<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\GameMatch;
use App\Models\Player;
use App\Models\Referee;
use App\Models\Team;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminMasterController extends Controller
{
    /**
     * Display Master Data Management (Categories, Teams, Players, Venues, Operators, Referees).
     */
    public function index(Request $request): View
    {
        $categories = Category::withCount(['teams', 'matches'])->get();
        $selectedCategoryId = $request->query('category_id', $categories->first()?->id);

        $teams = Team::query()
            ->when($selectedCategoryId, fn ($q) => $q->where('category_id', $selectedCategoryId))
            ->withCount('players')
            ->with('category')
            ->orderBy('name')
            ->get();

        $selectedTeamId = $request->query('team_id');

        $players = Player::query()
            ->when($selectedTeamId, fn ($q) => $q->where('team_id', $selectedTeamId))
            ->when(! $selectedTeamId && $selectedCategoryId, function ($q) use ($selectedCategoryId) {
                $q->whereHas('team', fn ($t) => $t->where('category_id', $selectedCategoryId));
            })
            ->with('team')
            ->orderBy('team_id')
            ->orderBy('jersey_number')
            ->get();

        $venues = Venue::withCount('matches')->orderBy('name')->get();

        $operators = User::where('role', 'operator')
            ->withCount('assignedMatches')
            ->orderBy('name')
            ->get();

        $referees = Referee::withCount(['matchesAsReferee1', 'matchesAsReferee2'])
            ->orderBy('name')
            ->get();

        $stats = [
            'total_categories' => $categories->count(),
            'total_teams' => Team::count(),
            'total_players' => Player::count(),
            'total_venues' => $venues->count(),
            'total_operators' => $operators->count(),
            'total_referees' => $referees->count(),
            'total_matches' => GameMatch::count(),
            'live_matches' => GameMatch::live()->count(),
        ];

        return view('admin.dashboard', [
            'categories' => $categories,
            'selectedCategoryId' => $selectedCategoryId,
            'teams' => $teams,
            'selectedTeamId' => $selectedTeamId,
            'players' => $players,
            'venues' => $venues,
            'operators' => $operators,
            'referees' => $referees,
            'stats' => $stats,
        ]);
    }

    // --- CATEGORY CRUD ---

    public function storeCategory(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        Category::create($validated);

        return back()->with('success', 'Kategori turnamen baru berhasil ditambahkan.');
    }

    public function updateCategory(Request $request, int $id): RedirectResponse
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $category->update($validated);

        return back()->with('success', 'Kategori turnamen berhasil diperbarui.');
    }

    public function destroyCategory(int $id): RedirectResponse
    {
        if (Category::count() <= 1) {
            return back()->with('error', 'Tidak dapat menghapus kategori terakhir. Minimal harus ada 1 kategori di dalam sistem.');
        }

        $category = Category::findOrFail($id);
        $category->delete();

        return back()->with('success', 'Kategori turnamen berhasil dihapus.');
    }

    // --- TEAM CRUD ---

    public function storeTeam(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:10'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048'],
            'manager_name' => ['nullable', 'string', 'max:255'],
            'manager_contact' => ['nullable', 'string', 'max:50'],
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('teams', 'public');
        }

        Team::create($validated);

        return back()->with('success', 'Tim futsal baru berhasil didaftarkan.');
    }

    public function updateTeam(Request $request, int $id): RedirectResponse
    {
        $team = Team::findOrFail($id);

        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:10'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048'],
            'manager_name' => ['nullable', 'string', 'max:255'],
            'manager_contact' => ['nullable', 'string', 'max:50'],
        ]);

        if ($request->hasFile('logo')) {
            if ($team->logo && Storage::disk('public')->exists($team->logo)) {
                Storage::disk('public')->delete($team->logo);
            }
            $validated['logo'] = $request->file('logo')->store('teams', 'public');
        }

        $team->update($validated);

        return back()->with('success', 'Data tim futsal berhasil diperbarui.');
    }

    public function destroyTeam(int $id): RedirectResponse
    {
        $team = Team::findOrFail($id);

        if ($team->logo && Storage::disk('public')->exists($team->logo)) {
            Storage::disk('public')->delete($team->logo);
        }

        $team->delete();

        return back()->with('success', 'Tim futsal berhasil dihapus.');
    }

    // --- PLAYER CRUD ---

    public function storePlayer(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'team_id' => ['required', 'exists:teams,id'],
            'name' => ['required', 'string', 'max:255'],
            'jersey_number' => [
                'required',
                'integer',
                'min:1',
                'max:99',
                Rule::unique('players')->where(fn ($query) => $query->where('team_id', $request->input('team_id'))),
            ],
            'position' => ['required', Rule::in(['GK', 'DEF', 'FLA', 'PIV'])],
            'is_captain' => ['nullable', 'boolean'],
        ]);

        $validated['is_captain'] = $request->boolean('is_captain');

        Player::create($validated);

        return back()->with('success', 'Pemain berhasil didaftarkan ke skuad.');
    }

    public function updatePlayer(Request $request, int $id): RedirectResponse
    {
        $player = Player::findOrFail($id);

        $validated = $request->validate([
            'team_id' => ['required', 'exists:teams,id'],
            'name' => ['required', 'string', 'max:255'],
            'jersey_number' => [
                'required',
                'integer',
                'min:1',
                'max:99',
                Rule::unique('players')->ignore($player->id)->where(fn ($query) => $query->where('team_id', $request->input('team_id'))),
            ],
            'position' => ['required', Rule::in(['GK', 'DEF', 'FLA', 'PIV'])],
            'is_captain' => ['nullable', 'boolean'],
        ]);

        $validated['is_captain'] = $request->boolean('is_captain');

        $player->update($validated);

        return back()->with('success', 'Data pemain berhasil diperbarui.');
    }

    public function destroyPlayer(int $id): RedirectResponse
    {
        $player = Player::findOrFail($id);
        $player->delete();

        return back()->with('success', 'Pemain berhasil dihapus dari tim.');
    }

    // --- VENUE CRUD ---

    public function storeVenue(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'court_name' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $validated['is_active'] = true;

        Venue::create($validated);

        return back()->with('success', 'Venue / Lapangan baru berhasil ditambahkan.');
    }

    public function updateVenue(Request $request, int $id): RedirectResponse
    {
        $venue = Venue::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'court_name' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $venue->update($validated);

        return back()->with('success', 'Data venue / lapangan berhasil diperbarui.');
    }

    public function destroyVenue(int $id): RedirectResponse
    {
        $venue = Venue::findOrFail($id);
        $venue->delete();

        return back()->with('success', 'Venue / Lapangan berhasil dihapus.');
    }

    // --- OPERATOR (WASIT MEJA) CRUD ---

    public function storeOperator(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['role'] = 'operator';
        $validated['email_verified_at'] = now();

        User::create($validated);

        return back()->with('success', 'Operator wasit meja baru berhasil ditambahkan.');
    }

    public function updateOperator(Request $request, int $id): RedirectResponse
    {
        $operator = User::where('role', 'operator')->findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($operator->id)],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $operator->update($validated);

        return back()->with('success', 'Data operator wasit meja berhasil diperbarui.');
    }

    public function destroyOperator(int $id): RedirectResponse
    {
        $operator = User::where('role', 'operator')->findOrFail($id);

        if ($operator->id === auth()->id()) {
            return back()->withErrors(['error' => 'Tidak dapat menghapus akun Anda sendiri.']);
        }

        $operator->delete();

        return back()->with('success', 'Akun operator wasit meja berhasil dihapus.');
    }

    // --- REFEREE (WASIT LAPANGAN) CRUD ---

    public function storeReferee(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'license' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'city' => ['nullable', 'string', 'max:100'],
        ]);

        $validated['is_active'] = true;

        Referee::create($validated);

        return back()->with('success', 'Wasit lapangan baru berhasil ditambahkan ke Master Wasit.');
    }

    public function updateReferee(Request $request, int $id): RedirectResponse
    {
        $referee = Referee::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'license' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'city' => ['nullable', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : $referee->is_active;

        $referee->update($validated);

        return back()->with('success', 'Data wasit lapangan berhasil diperbarui.');
    }

    public function destroyReferee(int $id): RedirectResponse
    {
        $referee = Referee::findOrFail($id);
        $referee->delete();

        return back()->with('success', 'Wasit lapangan berhasil dihapus dari Master Wasit.');
    }
}
