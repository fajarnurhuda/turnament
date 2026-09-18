<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\GameMatch;
use App\Models\Stage;
use App\Services\TournamentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicTournamentController extends Controller
{
    public function __construct(
        protected TournamentService $tournamentService
    ) {}

    /**
     * Fan Center / Tournament Home Page.
     */
    public function index(Request $request): View
    {
        $categories = Category::with('stages.groups')->get();

        $selectedCategorySlug = $request->query('category', $categories->first()?->slug);
        $activeCategory = $categories->firstWhere('slug', $selectedCategorySlug) ?: $categories->first();

        $categoryId = $activeCategory?->id;

        // Matches
        $allMatchesQuery = GameMatch::query()
            ->with(['homeTeam', 'awayTeam', 'group', 'stage', 'category'])
            ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId))
            ->orderBy('match_date', 'asc');

        $allMatches = $allMatchesQuery->get();

        // Featured Live Match (first match that is live or first upcoming)
        $liveMatches = $allMatches->filter(fn ($m) => $m->isLive());
        $featuredMatch = $liveMatches->first() ?: $allMatches->firstWhere('status', 'scheduled') ?: $allMatches->first();

        // Separate matches by tabs
        $upcomingMatches = $allMatches->where('status', 'scheduled');
        $finishedMatches = $allMatches->where('status', 'finished');

        // Standings: Group stages or Single Group
        $groupStages = $activeCategory?->stages->where('type', 'group') ?: collect();
        $standingsByGroup = [];

        foreach ($groupStages as $stage) {
            if ($stage->groups->count() > 0) {
                foreach ($stage->groups as $grp) {
                    $standingsByGroup[$grp->name] = [
                        'group' => $grp,
                        'stage' => $stage,
                        'rows' => $this->tournamentService->calculateStandings($categoryId, $stage->id, $grp->id),
                    ];
                }
            } else {
                // If stage has no explicit groups (single league table)
                $standingsByGroup['Klasemen Utama'] = [
                    'group' => null,
                    'stage' => $stage,
                    'rows' => $this->tournamentService->calculateStandings($categoryId, $stage->id, null),
                ];
            }
        }

        // Leaderboards
        $topScorers = $this->tournamentService->getTopScorers($categoryId, 5);
        $topAssists = $this->tournamentService->getTopAssists($categoryId, 5);
        $disciplinary = $this->tournamentService->getDisciplinaryLeaderboard($categoryId, 5);

        // Knockout Stages (Bracket)
        $knockoutStages = $activeCategory?->stages->where('type', 'knockout') ?: collect();
        $bracketStages = [];
        foreach ($knockoutStages as $kStage) {
            $bracketStages[] = [
                'stage' => $kStage,
                'matches' => GameMatch::where('stage_id', $kStage->id)
                    ->with(['homeTeam', 'awayTeam'])
                    ->get(),
            ];
        }

        return view('public.fan_center', [
            'categories' => $categories,
            'activeCategory' => $activeCategory,
            'featuredMatch' => $featuredMatch,
            'liveMatches' => $liveMatches,
            'allMatches' => $allMatches,
            'upcomingMatches' => $upcomingMatches,
            'finishedMatches' => $finishedMatches,
            'standingsByGroup' => $standingsByGroup,
            'topScorers' => $topScorers,
            'topAssists' => $topAssists,
            'disciplinary' => $disciplinary,
            'bracketStages' => $bracketStages,
        ]);
    }

    /**
     * Match Detail & Vertical Timeline Stream.
     */
    public function showMatch(int $id): View
    {
        $match = GameMatch::with([
            'homeTeam.players',
            'awayTeam.players',
            'category',
            'stage',
            'group',
            'venueModel',
            'referee1',
            'referee2',
            'referee3',
            'events.player',
            'events.assistPlayer',
            'events.team',
        ])->findOrFail($id);

        return view('public.match_detail', [
            'match' => $match,
        ]);
    }

    /**
     * JSON Telemetry Live Endpoint for dynamic browser polling.
     */
    public function liveFeed(int $id): JsonResponse
    {
        $match = GameMatch::with([
            'homeTeam',
            'awayTeam',
            'events.player',
            'events.assistPlayer',
            'events.team',
        ])->findOrFail($id);

        return response()->json([
            'id' => $match->id,
            'home_score' => $match->home_score,
            'away_score' => $match->away_score,
            'status' => $match->status,
            'current_minute' => $match->current_minute,
            'timer_seconds' => $match->elapsed_seconds,
            'timer_running' => $match->timer_running,
            'time_formatted' => $match->time_formatted,
            'status_badge' => $match->status_badge,
            'is_live' => $match->isLive(),
            'events' => $match->events->map(fn ($e) => [
                'id' => $e->id,
                'team_id' => $e->team_id,
                'event_type' => $e->event_type,
                'event_label' => $e->event_label,
                'minute' => $e->minute,
                'player_name' => $e->player?->name,
                'player_number' => $e->player?->jersey_number,
                'assist_name' => $e->assistPlayer?->name,
                'notes' => $e->notes,
                'is_home' => $e->team_id === $match->home_team_id,
            ]),
        ]);
    }

    /**
     * JSON Endpoint for all live matches ticker.
     */
    public function allLiveScores(): JsonResponse
    {
        $matches = GameMatch::live()
            ->with(['homeTeam', 'awayTeam'])
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'home_name' => $m->homeTeam->name,
                'home_code' => $m->homeTeam->code,
                'home_score' => $m->home_score,
                'away_name' => $m->awayTeam->name,
                'away_code' => $m->awayTeam->code,
                'away_score' => $m->away_score,
                'minute' => $m->current_minute,
                'status_badge' => $m->status_badge,
            ]);

        return response()->json($matches);
    }
}
