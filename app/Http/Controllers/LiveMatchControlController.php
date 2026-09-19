<?php

namespace App\Http\Controllers;

use App\Models\GameMatch;
use App\Models\MatchEvent;
use App\Services\TournamentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LiveMatchControlController extends Controller
{
    public function __construct(
        protected TournamentService $tournamentService
    ) {}

    /**
     * Ensure user has permission to control this match.
     */
    protected function authorizeMatchControl(GameMatch $match): void
    {
        $user = auth()->user();
        if (! $user || ! $user->canControlMatch($match)) {
            abort(403, 'Akses Ditolak. Anda tidak memiliki wewenang untuk mengontrol pertandingan ini.');
        }
    }

    /**
     * Show Live Match Control Room.
     */
    public function show(int $id): View
    {
        $match = GameMatch::with([
            'homeTeam.players',
            'awayTeam.players',
            'category',
            'stage',
            'group',
            'events.player',
            'events.assistPlayer',
            'events.team',
            'operators',
        ])->findOrFail($id);

        $this->authorizeMatchControl($match);

        return view('admin.control_room', [
            'match' => $match,
        ]);
    }

    /**
     * Update match status and active period.
     */
    public function updateStatus(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $match = GameMatch::findOrFail($id);
        $this->authorizeMatchControl($match);

        $validated = $request->validate([
            'status' => ['required', Rule::in([
                'scheduled',
                'first_half',
                'half_time',
                'second_half',
                'extra_time',
                'penalty_shootout',
                'finished',
                'postponed',
            ])],
            'current_minute' => ['nullable', 'integer', 'min:0', 'max:120'],
            'force' => ['nullable', 'boolean'],
        ]);

        $newStatus = $validated['status'];
        $isForce = (bool) ($validated['force'] ?? false);

        // State machine validation if not forced
        if (! $isForce && $newStatus !== $match->status) {
            $allowedTransitions = match ($match->status) {
                'scheduled' => ['first_half', 'postponed'],
                'first_half' => ['half_time', 'postponed'],
                'half_time' => ['second_half', 'postponed'],
                'second_half' => ['finished', 'extra_time', 'penalty_shootout', 'postponed'],
                'extra_time' => ['finished', 'penalty_shootout', 'postponed'],
                'penalty_shootout' => ['finished', 'postponed'],
                'finished' => [], // Reopening finished match requires force=true
                'postponed' => ['scheduled', 'first_half'],
                default => [],
            };

            if (! in_array($newStatus, $allowedTransitions, true)) {
                $msg = "Transisi babak dari {$match->status_badge['text']} tidak diperbolehkan langsung melompat ke pilihan tersebut. Ikuti alur babak atau gunakan opsi Rollback jika salah klik.";
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $msg,
                    ], 422);
                }

                return back()->withErrors(['error' => $msg]);
            }
        }

        $halfDurationMinutes = $match->half_duration_minutes ?: 20;
        $halfDurationSeconds = $match->half_duration_seconds;
        $fullTimeSeconds = $match->full_time_seconds;

        if (! isset($validated['current_minute'])) {
            // Provide intelligent defaults for minute based on flexible match half duration
            $validated['current_minute'] = match ($newStatus) {
                'first_half' => max($match->current_minute, 1),
                'half_time' => $halfDurationMinutes,
                'second_half' => max($match->current_minute, $halfDurationMinutes + 1),
                'extra_time' => max($match->current_minute, ($halfDurationMinutes * 2) + 1),
                'penalty_shootout' => max($match->current_minute, $halfDurationMinutes * 2),
                'finished' => max($match->current_minute, $halfDurationMinutes * 2),
                default => $match->current_minute,
            };
        }

        // Synchronize stopwatch with period transitions
        if ($newStatus === 'first_half') {
            $validated['timer_seconds'] = $match->timer_seconds > 0 ? $match->timer_seconds : 0;
            $validated['timer_running'] = true;
            $validated['timer_started_at'] = now();
        } elseif ($newStatus === 'half_time') {
            $validated['timer_seconds'] = $halfDurationSeconds;
            $validated['timer_running'] = false;
            $validated['timer_started_at'] = null;
        } elseif ($newStatus === 'second_half') {
            $validated['timer_seconds'] = max($halfDurationSeconds, $match->timer_seconds);
            $validated['timer_running'] = true;
            $validated['timer_started_at'] = now();
        } elseif ($newStatus === 'extra_time') {
            $validated['timer_seconds'] = max($fullTimeSeconds, $match->timer_seconds);
            $validated['timer_running'] = true;
            $validated['timer_started_at'] = now();
        } elseif ($newStatus === 'penalty_shootout') {
            $validated['timer_seconds'] = $match->elapsed_seconds;
            $validated['timer_running'] = false;
            $validated['timer_started_at'] = null;
            if (is_null($match->home_penalty_score)) {
                $validated['home_penalty_score'] = 0;
            }
            if (is_null($match->away_penalty_score)) {
                $validated['away_penalty_score'] = 0;
            }
        } elseif (in_array($newStatus, ['finished', 'postponed', 'scheduled'])) {
            $validated['timer_seconds'] = $match->elapsed_seconds;
            $validated['timer_running'] = false;
            $validated['timer_started_at'] = null;
        }

        $match->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Status pertandingan berhasil diperbarui.',
                'match' => $match,
                'time_formatted' => $match->time_formatted,
                'timer_running' => $match->timer_running,
            ]);
        }

        return back()->with('success', 'Status pertandingan diubah menjadi '.$match->status_badge['text']);
    }

    /**
     * Start or Pause the live match stopwatch.
     */
    public function toggleTimer(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $match = GameMatch::findOrFail($id);
        $this->authorizeMatchControl($match);

        if (in_array($match->status, ['finished', 'penalty_shootout'])) {
            $msg = $match->status === 'penalty_shootout'
                ? 'Pertandingan dalam sesi Adu Penalti. Stopwatch waktu bersih tidak digunakan.'
                : 'Pertandingan telah selesai (Full Time). Stopwatch tidak dapat diaktifkan.';

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $msg,
                ], 422);
            }

            return back()->withErrors(['error' => $msg]);
        }

        if ($match->timer_running) {
            // Pause the stopwatch and accumulate elapsed seconds
            $elapsed = $match->elapsed_seconds;
            $minute = max(1, (int) ceil($elapsed / 60));

            $match->update([
                'timer_seconds' => $elapsed,
                'timer_running' => false,
                'timer_started_at' => null,
                'current_minute' => $minute,
            ]);
            $msg = 'Stopwatch dijeda (Paused).';
        } else {
            // Start or resume the stopwatch
            $updateData = [
                'timer_running' => true,
                'timer_started_at' => now(),
            ];

            // If match is still scheduled, starting the timer officially kicks off Babak 1
            if ($match->status === 'scheduled') {
                $updateData['status'] = 'first_half';
                $updateData['current_minute'] = max(1, (int) $match->current_minute);
            }

            $match->update($updateData);
            $msg = 'Stopwatch berjalan (Running).';
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'timer_running' => $match->timer_running,
                'elapsed_seconds' => $match->elapsed_seconds,
                'time_formatted' => $match->time_formatted,
                'current_minute' => $match->current_minute,
                'status' => $match->status,
                'status_badge' => $match->status_badge,
                'is_live' => $match->isLive(),
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Set stopwatch to a specific time or reset (e.g. 00:00 or 20:00).
     */
    public function setTimer(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $match = GameMatch::findOrFail($id);
        $this->authorizeMatchControl($match);

        if ($match->status === 'finished') {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pertandingan telah selesai (Full Time). Pengaturan waktu dinonaktifkan.',
                ], 422);
            }

            return back()->withErrors(['error' => 'Pertandingan telah selesai (Full Time). Pengaturan waktu dinonaktifkan.']);
        }

        $validated = $request->validate([
            'seconds' => ['required', 'integer', 'min:0', 'max:7200'],
        ]);

        $seconds = $validated['seconds'];
        $minute = max(1, (int) ceil($seconds / 60));

        $updateData = [
            'timer_seconds' => $seconds,
            'current_minute' => $minute,
        ];

        if ($match->timer_running) {
            $updateData['timer_started_at'] = now();
        }

        if ($match->status === 'scheduled') {
            $updateData['status'] = 'first_half';
        }

        $match->update($updateData);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'elapsed_seconds' => $match->elapsed_seconds,
                'time_formatted' => $match->time_formatted,
                'current_minute' => $match->current_minute,
            ]);
        }

        return back()->with('success', 'Waktu stopwatch diatur ke '.$match->time_formatted);
    }

    /**
     * Update active minute.
     */
    public function updateMinute(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $match = GameMatch::findOrFail($id);
        $this->authorizeMatchControl($match);

        if ($match->status === 'finished') {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pertandingan telah selesai (Full Time). Menit pertandingan tidak dapat diatur.',
                ], 422);
            }

            return back()->withErrors(['error' => 'Pertandingan telah selesai (Full Time). Menit pertandingan tidak dapat diatur.']);
        }

        $validated = $request->validate([
            'current_minute' => ['required', 'integer', 'min:0', 'max:120'],
        ]);

        $match->update([
            'current_minute' => $validated['current_minute'],
            'timer_seconds' => $validated['current_minute'] * 60,
            'timer_started_at' => $match->timer_running ? now() : null,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'minute' => $match->current_minute,
                'time_formatted' => $match->time_formatted,
            ]);
        }

        return back()->with('success', 'Menit pertandingan diperbarui.');
    }

    /**
     * Manually adjust score if needed by table referee.
     */
    public function updateScore(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $match = GameMatch::findOrFail($id);
        $this->authorizeMatchControl($match);

        $validated = $request->validate([
            'home_score' => ['required', 'integer', 'min:0'],
            'away_score' => ['required', 'integer', 'min:0'],
        ]);

        $match->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'home_score' => $match->home_score,
                'away_score' => $match->away_score,
            ]);
        }

        return back()->with('success', 'Papan skor manual berhasil disesuaikan.');
    }

    /**
     * Update penalty shootout scores.
     */
    public function updatePenaltyScore(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $match = GameMatch::findOrFail($id);
        $this->authorizeMatchControl($match);

        $validated = $request->validate([
            'home_penalty_score' => ['required', 'integer', 'min:0', 'max:50'],
            'away_penalty_score' => ['required', 'integer', 'min:0', 'max:50'],
        ]);

        $match->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'home_penalty_score' => $match->home_penalty_score,
                'away_penalty_score' => $match->away_penalty_score,
                'penalty_formatted' => $match->penalty_score_formatted,
            ]);
        }

        return back()->with('success', 'Skor adu penalti berhasil diperbarui: '.$match->penalty_score_formatted);
    }

    /**
     * Record match event (Goal, Yellow Card, Red Card, Own Goal, Assist).
     */
    public function addEvent(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $match = GameMatch::findOrFail($id);
        $this->authorizeMatchControl($match);

        $currentLiveMinute = max(1, (int) ceil($match->elapsed_seconds / 60));
        $maxMinute = $match->status === 'finished'
            ? 120
            : $currentLiveMinute;

        $validated = $request->validate([
            'team_id' => ['required', 'exists:teams,id'],
            'player_id' => ['nullable', 'exists:players,id'],
            'assist_player_id' => ['nullable', 'exists:players,id', 'different:player_id'],
            'event_type' => ['required', Rule::in(['goal', 'own_goal', 'yellow_card', 'red_card', 'second_yellow', 'assist'])],
            'minute' => ['required', 'integer', 'min:1', "max:$maxMinute"],
            'notes' => ['nullable', 'string', 'max:255'],
        ], [
            'minute.max' => "Menit kejadian tidak boleh melebihi waktu pertandingan yang sedang berjalan (Maksimal menit ke-{$maxMinute}).",
            'assist_player_id.different' => 'Pencetak gol dan pemberi assist tidak boleh pemain yang sama.',
        ]);

        $validated['match_id'] = $match->id;

        $event = MatchEvent::create($validated);

        // Recalculate match score automatically if goal or own goal
        if (in_array($validated['event_type'], ['goal', 'own_goal'])) {
            $this->tournamentService->recalculateMatchScore($match);
        }

        if ($request->wantsJson()) {
            $match->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Event pertandingan berhasil dicatat.',
                'event' => $event->load(['player', 'assistPlayer', 'team']),
                'home_score' => $match->home_score,
                'away_score' => $match->away_score,
            ]);
        }

        return back()->with('success', 'Kejadian '.$event->event_label.' berhasil dicatat!');
    }

    /**
     * Undo/Delete match event.
     */
    public function deleteEvent(Request $request, int $id, int $eventId): RedirectResponse|JsonResponse
    {
        $match = GameMatch::findOrFail($id);
        $this->authorizeMatchControl($match);

        $event = MatchEvent::where('match_id', $match->id)->findOrFail($eventId);

        $eventType = $event->event_type;
        $event->delete();

        // Recalculate match score if goal or own goal was deleted
        if (in_array($eventType, ['goal', 'own_goal'])) {
            $this->tournamentService->recalculateMatchScore($match);
        }

        if ($request->wantsJson()) {
            $match->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Event berhasil dibatalkan/dihapus.',
                'home_score' => $match->home_score,
                'away_score' => $match->away_score,
            ]);
        }

        return back()->with('success', 'Event berhasil dihapus dan skor disinkronkan kembali.');
    }
}
