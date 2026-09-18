<?php

namespace App\Services;

use App\Models\Category;
use App\Models\GameMatch;
use App\Models\Group;
use App\Models\MatchEvent;
use App\Models\Team;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TournamentService
{
    /**
     * Calculate standings for a specific group, stage, or category.
     * Supports single-group, multi-group, or stage-wide standings.
     */
    public function calculateStandings(?int $categoryId = null, ?int $stageId = null, ?int $groupId = null): array
    {
        // Determine teams in scope
        $teamsQuery = Team::query();
        if ($categoryId) {
            $teamsQuery->where('category_id', $categoryId);
        }

        // Fetch matches that have been finished or are live
        $matchesQuery = GameMatch::query()
            ->whereIn('status', ['finished', 'second_half', 'extra_time']);

        if ($groupId) {
            $matchesQuery->where('group_id', $groupId);
        } elseif ($stageId) {
            $matchesQuery->where('stage_id', $stageId);
        } elseif ($categoryId) {
            $matchesQuery->where('category_id', $categoryId);
        }

        $matches = $matchesQuery->get();

        // If specific group is given, limit teams to those who have matches in that group
        // or all teams in category if not enough match history
        $teamIds = collect();
        foreach ($matches as $m) {
            $teamIds->push($m->home_team_id);
            $teamIds->push($m->away_team_id);
        }
        $teamIds = $teamIds->unique();

        if ($groupId && $teamIds->isNotEmpty()) {
            $teams = Team::whereIn('id', $teamIds)->get();
        } else {
            $teams = $teamsQuery->get();
        }

        $table = [];

        foreach ($teams as $team) {
            $teamRow = [
                'team' => $team,
                'played' => 0,
                'won' => 0,
                'draw' => 0,
                'lost' => 0,
                'goals_for' => 0,
                'goals_against' => 0,
                'goal_diff' => 0,
                'points' => 0,
                'form' => [],
            ];

            foreach ($matches as $match) {
                $isHome = $match->home_team_id === $team->id;
                $isAway = $match->away_team_id === $team->id;

                if (! $isHome && ! $isAway) {
                    continue;
                }

                $teamRow['played']++;
                $scored = $isHome ? $match->home_score : $match->away_score;
                $conceded = $isHome ? $match->away_score : $match->home_score;

                $teamRow['goals_for'] += $scored;
                $teamRow['goals_against'] += $conceded;

                if ($scored > $conceded) {
                    $teamRow['won']++;
                    $teamRow['points'] += 3;
                    $teamRow['form'][] = 'W';
                } elseif ($scored === $conceded) {
                    $teamRow['draw']++;
                    $teamRow['points'] += 1;
                    $teamRow['form'][] = 'D';
                } else {
                    $teamRow['lost']++;
                    $teamRow['form'][] = 'L';
                }
            }

            $teamRow['goal_diff'] = $teamRow['goals_for'] - $teamRow['goals_against'];
            $teamRow['form'] = array_slice(array_reverse($teamRow['form']), 0, 5);

            $table[] = $teamRow;
        }

        // Sort table: points DESC, goal_diff DESC, goals_for DESC, name ASC
        usort($table, function ($a, $b) {
            if ($b['points'] !== $a['points']) {
                return $b['points'] <=> $a['points'];
            }
            if ($b['goal_diff'] !== $a['goal_diff']) {
                return $b['goal_diff'] <=> $a['goal_diff'];
            }
            if ($b['goals_for'] !== $a['goals_for']) {
                return $b['goals_for'] <=> $a['goals_for'];
            }

            return strcmp($a['team']->name, $b['team']->name);
        });

        // Add position rankings
        foreach ($table as $idx => &$row) {
            $row['position'] = $idx + 1;
        }

        return $table;
    }

    /**
     * Get Top Scorers leaderboard (excluding own goals).
     */
    public function getTopScorers(?int $categoryId = null, int $limit = 10): Collection
    {
        $query = MatchEvent::query()
            ->where('event_type', 'goal')
            ->whereNotNull('player_id');

        if ($categoryId) {
            $query->whereHas('match', function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            });
        }

        return $query->select('player_id', 'team_id', DB::raw('count(*) as total_goals'))
            ->groupBy('player_id', 'team_id')
            ->orderByDesc('total_goals')
            ->with(['player', 'team'])
            ->limit($limit)
            ->get();
    }

    /**
     * Get Top Assists leaderboard.
     */
    public function getTopAssists(?int $categoryId = null, int $limit = 10): Collection
    {
        $query = MatchEvent::query()
            ->whereNotNull('assist_player_id');

        if ($categoryId) {
            $query->whereHas('match', function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            });
        }

        return $query->select('assist_player_id as player_id', 'team_id', DB::raw('count(*) as total_assists'))
            ->groupBy('assist_player_id', 'team_id')
            ->orderByDesc('total_assists')
            ->with(['assistPlayer', 'team'])
            ->limit($limit)
            ->get();
    }

    /**
     * Get Disciplinary cards leaderboard (Yellow & Red cards).
     */
    public function getDisciplinaryLeaderboard(?int $categoryId = null, int $limit = 10): Collection
    {
        $query = MatchEvent::query()
            ->whereIn('event_type', ['yellow_card', 'red_card', 'second_yellow'])
            ->whereNotNull('player_id');

        if ($categoryId) {
            $query->whereHas('match', function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            });
        }

        return $query->select(
            'player_id',
            'team_id',
            DB::raw("SUM(CASE WHEN event_type = 'yellow_card' THEN 1 ELSE 0 END) as yellow_cards"),
            DB::raw("SUM(CASE WHEN event_type IN ('red_card', 'second_yellow') THEN 1 ELSE 0 END) as red_cards"),
            DB::raw('COUNT(*) as total_cards')
        )
            ->groupBy('player_id', 'team_id')
            ->orderByDesc('red_cards')
            ->orderByDesc('yellow_cards')
            ->with(['player', 'team'])
            ->limit($limit)
            ->get();
    }

    /**
     * Recalculate and update the scores of a match from its recorded events.
     */
    public function recalculateMatchScore(GameMatch $match): void
    {
        $homeGoals = MatchEvent::where('match_id', $match->id)
            ->where(function ($q) use ($match) {
                // Regular goal by home team OR own goal by away team
                $q->where(function ($sub) use ($match) {
                    $sub->where('team_id', $match->home_team_id)->where('event_type', 'goal');
                })->orWhere(function ($sub) use ($match) {
                    $sub->where('team_id', $match->away_team_id)->where('event_type', 'own_goal');
                });
            })->count();

        $awayGoals = MatchEvent::where('match_id', $match->id)
            ->where(function ($q) use ($match) {
                // Regular goal by away team OR own goal by home team
                $q->where(function ($sub) use ($match) {
                    $sub->where('team_id', $match->away_team_id)->where('event_type', 'goal');
                })->orWhere(function ($sub) use ($match) {
                    $sub->where('team_id', $match->home_team_id)->where('event_type', 'own_goal');
                });
            })->count();

        $match->update([
            'home_score' => $homeGoals,
            'away_score' => $awayGoals,
        ]);
    }
}
