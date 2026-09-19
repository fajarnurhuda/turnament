<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\GameMatch;
use App\Models\Group;
use App\Models\Referee;
use App\Models\Stage;
use App\Models\Team;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminFixtureController extends Controller
{
    /**
     * Display match scheduler and fixtures management.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $categories = Category::with(['stages.groups', 'teams'])->get();
        $selectedCategoryId = $request->query('category_id', $categories->first()?->id);
        $selectedStatus = $request->query('status');
        $selectedDate = $request->query('date');

        $matchesQuery = GameMatch::query()
            ->with(['homeTeam', 'awayTeam', 'category', 'stage', 'group', 'venueModel', 'operators', 'referee1', 'referee2', 'referee3'])
            ->when($selectedCategoryId, fn ($q) => $q->where('category_id', $selectedCategoryId))
            ->when($selectedStatus, fn ($q) => $q->where('status', $selectedStatus))
            ->when($selectedDate, fn ($q) => $q->whereDate('match_date', $selectedDate))
            ->when($user && ! $user->isAdmin(), fn ($q) => $q->whereHas('operators', fn ($sub) => $sub->where('users.id', $user->id)))
            ->orderBy('match_date', 'asc');

        $matches = $matchesQuery->get();

        $teams = Team::when($selectedCategoryId, fn ($q) => $q->where('category_id', $selectedCategoryId))->get();
        $stages = Stage::with('groups')->when($selectedCategoryId, fn ($q) => $q->where('category_id', $selectedCategoryId))->get();
        $groups = Group::whereHas('stage', function ($q) use ($selectedCategoryId) {
            if ($selectedCategoryId) {
                $q->where('category_id', $selectedCategoryId);
            }
        })->get();
        $venues = Venue::where('is_active', true)->orderBy('name')->get();
        $operators = User::where('role', 'operator')->orderBy('name')->get();
        $referees = Referee::where('is_active', true)->orderBy('name')->get();

        return view('admin.fixtures', [
            'matches' => $matches,
            'categories' => $categories,
            'selectedCategoryId' => $selectedCategoryId,
            'selectedStatus' => $selectedStatus,
            'selectedDate' => $selectedDate,
            'teams' => $teams,
            'stages' => $stages,
            'groups' => $groups,
            'venues' => $venues,
            'operators' => $operators,
            'referees' => $referees,
        ]);
    }

    /**
     * Store new match fixture.
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->input('venue_id') === 'custom') {
            $request->merge(['venue_id' => null]);
        }

        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'stage_id' => ['nullable', 'exists:stages,id'],
            'group_id' => ['nullable', 'exists:groups,id'],
            'home_team_id' => ['required', 'exists:teams,id', 'different:away_team_id'],
            'away_team_id' => ['required', 'exists:teams,id'],
            'match_date' => ['required', 'date'],
            'venue_id' => ['nullable', 'exists:venues,id'],
            'venue' => ['nullable', 'string', 'max:255'],
            'half_duration_minutes' => ['nullable', 'integer', 'min:1', 'max:60'],
            'extra_time_duration_minutes' => ['nullable', 'integer', 'min:1', 'max:30'],
            'operator_ids' => ['nullable', 'array'],
            'operator_ids.*' => ['exists:users,id'],
            'referee_1_id' => ['nullable', 'exists:referees,id'],
            'referee_2_id' => ['nullable', 'exists:referees,id', 'different:referee_1_id'],
            'referee_3_id' => ['nullable', 'exists:referees,id'],
        ], [
            'home_team_id.different' => 'Tim kandang dan tim tandang tidak boleh sama.',
            'referee_2_id.different' => 'Wasit 1 dan Wasit 2 tidak boleh orang yang sama.',
        ]);

        if (! empty($validated['venue_id'])) {
            $v = Venue::find($validated['venue_id']);
            $fullName = $v ? ($v->court_name ? "{$v->name} ({$v->court_name})" : $v->name) : ($validated['venue'] ?? 'Arena Futsal');
            $validated['venue'] = $request->input('venue') ?: $fullName;
        } elseif (! empty($validated['venue'])) {
            $v = Venue::firstOrCreate(['name' => $validated['venue']], ['is_active' => true]);
            $validated['venue_id'] = $v->id;
        } else {
            $validated['venue'] = 'Arena Futsal';
        }

        $validated['half_duration_minutes'] = $request->input('half_duration_minutes', 20) ?: 20;
        $validated['extra_time_duration_minutes'] = $request->input('extra_time_duration_minutes', 5) ?: 5;
        $validated['status'] = 'scheduled';
        $validated['home_score'] = 0;
        $validated['away_score'] = 0;
        $validated['current_minute'] = 0;

        $match = GameMatch::create($validated);

        if ($request->has('operator_ids')) {
            $match->operators()->sync($request->input('operator_ids', []));
        }

        return back()->with('success', 'Jadwal pertandingan baru berhasil dibuat.');
    }

    /**
     * Update match fixture.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        if ($request->input('venue_id') === 'custom') {
            $request->merge(['venue_id' => null]);
        }

        $match = GameMatch::findOrFail($id);

        $validated = $request->validate([
            'stage_id' => ['nullable', 'exists:stages,id'],
            'group_id' => ['nullable', 'exists:groups,id'],
            'home_team_id' => ['required', 'exists:teams,id', 'different:away_team_id'],
            'away_team_id' => ['required', 'exists:teams,id'],
            'match_date' => ['required', 'date'],
            'venue_id' => ['nullable', 'exists:venues,id'],
            'venue' => ['nullable', 'string', 'max:255'],
            'half_duration_minutes' => ['nullable', 'integer', 'min:1', 'max:60'],
            'extra_time_duration_minutes' => ['nullable', 'integer', 'min:1', 'max:30'],
            'operator_ids' => ['nullable', 'array'],
            'operator_ids.*' => ['exists:users,id'],
            'referee_1_id' => ['nullable', 'exists:referees,id'],
            'referee_2_id' => ['nullable', 'exists:referees,id', 'different:referee_1_id'],
            'referee_3_id' => ['nullable', 'exists:referees,id'],
            'status' => ['required', Rule::in(['scheduled', 'first_half', 'half_time', 'second_half', 'extra_time', 'finished', 'postponed'])],
        ], [
            'home_team_id.different' => 'Tim kandang dan tim tandang tidak boleh sama.',
            'referee_2_id.different' => 'Wasit 1 dan Wasit 2 tidak boleh orang yang sama.',
        ]);

        if (! empty($validated['venue_id'])) {
            $v = Venue::find($validated['venue_id']);
            $fullName = $v ? ($v->court_name ? "{$v->name} ({$v->court_name})" : $v->name) : ($validated['venue'] ?? $match->venue);
            $validated['venue'] = $request->input('venue') ?: $fullName;
        } elseif (! empty($validated['venue'])) {
            $v = Venue::firstOrCreate(['name' => $validated['venue']], ['is_active' => true]);
            $validated['venue_id'] = $v->id;
        }

        if ($request->filled('half_duration_minutes')) {
            $validated['half_duration_minutes'] = (int) $request->input('half_duration_minutes');
        }
        if ($request->filled('extra_time_duration_minutes')) {
            $validated['extra_time_duration_minutes'] = (int) $request->input('extra_time_duration_minutes');
        }

        $match->update($validated);

        if ($request->has('operator_ids')) {
            $match->operators()->sync($request->input('operator_ids', []));
        }

        return back()->with('success', 'Jadwal pertandingan berhasil diperbarui.');
    }

    /**
     * Delete match fixture.
     */
    public function destroy(int $id): RedirectResponse
    {
        $match = GameMatch::findOrFail($id);
        $match->delete();

        return back()->with('success', 'Pertandingan berhasil dihapus dari jadwal.');
    }
}
