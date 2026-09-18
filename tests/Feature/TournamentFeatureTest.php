<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\GameMatch;
use App\Models\Referee;
use App\Models\Team;
use App\Models\User;
use App\Models\Venue;
use App\Services\TournamentService;
use Database\Seeders\TournamentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TournamentFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TournamentSeeder::class);
    }

    /**
     * Test public fan center renders successfully.
     */
    public function test_public_fan_center_loads_successfully(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('LDII CUP');
        $response->assertSee('TANJUNG PINANG');
        $response->assertSee('Fan Center');
    }

    /**
     * Test match detail and vertical timeline view.
     */
    public function test_match_detail_loads_successfully(): void
    {
        $match = GameMatch::first();
        $this->assertNotNull($match);

        $response = $this->get(route('matches.show', $match->id));

        $response->assertStatus(200);
        $response->assertSee($match->homeTeam->name);
        $response->assertSee($match->awayTeam->name);
        $response->assertSee('Lini Masa Pertandingan');
    }

    /**
     * Test JSON live telemetry endpoint returns proper structure for Alpine polling.
     */
    public function test_live_telemetry_json_endpoint_works(): void
    {
        $match = GameMatch::first();
        $this->assertNotNull($match);

        $response = $this->getJson(route('api.matches.live', $match->id));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'id',
            'home_score',
            'away_score',
            'status',
            'current_minute',
            'status_badge',
            'is_live',
            'events',
        ]);
    }

    /**
     * Test quick demo authentication for operators redirects to fixtures.
     */
    public function test_operator_quick_login_works(): void
    {
        $response = $this->get(route('login.quick', 'operator'));

        $response->assertRedirect(route('admin.fixtures'));
        $this->assertAuthenticated();
    }

    /**
     * Test quick demo authentication for admin redirects to dashboard.
     */
    public function test_admin_quick_login_works(): void
    {
        $response = $this->get(route('login.quick', 'admin'));

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated();
    }

    /**
     * Test table referee can record a goal and scoreboard updates automatically.
     */
    public function test_table_referee_can_record_goal_and_score_updates(): void
    {
        $operator = User::where('role', 'operator')->first() ?? User::first();
        $match = GameMatch::first();
        $homePlayer = $match->homeTeam->players->first();

        $initialHomeScore = $match->home_score;

        $response = $this->actingAs($operator)->post(route('admin.matches.events.store', $match->id), [
            'team_id' => $match->home_team_id,
            'player_id' => $homePlayer?->id,
            'event_type' => 'goal',
            'minute' => 19,
            'notes' => 'Gol tendangan penalti',
        ]);

        $response->assertSessionHas('success');

        $match->refresh();
        $this->assertEquals($initialHomeScore + 1, $match->home_score);
    }

    /**
     * Test standings calculation service computes correctly.
     */
    public function test_standings_service_computes_points_accurately(): void
    {
        $service = app(TournamentService::class);
        $category = Category::first();

        $standings = $service->calculateStandings($category->id);

        $this->assertIsArray($standings);
        $this->assertNotEmpty($standings);

        foreach ($standings as $row) {
            $this->assertArrayHasKey('team', $row);
            $this->assertArrayHasKey('played', $row);
            $this->assertArrayHasKey('won', $row);
            $this->assertArrayHasKey('draw', $row);
            $this->assertArrayHasKey('lost', $row);
            $this->assertArrayHasKey('points', $row);
            $this->assertEquals($row['won'] * 3 + $row['draw'] * 1, $row['points']);
        }
    }

    /**
     * Test stopwatch toggle (pause & resume) and set timer.
     */
    public function test_stopwatch_toggle_and_set_timer(): void
    {
        $operator = User::where('role', 'operator')->first() ?? User::first();
        $match = GameMatch::where('status', 'first_half')->first() ?? GameMatch::first();

        // 1. Toggle timer
        $response = $this->actingAs($operator)->postJson(route('admin.matches.timer.toggle', $match->id));
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'timer_running', 'time_formatted']);

        // 2. Set timer to 1200s (20:00)
        $setResponse = $this->actingAs($operator)->postJson(route('admin.matches.timer.set', $match->id), [
            'seconds' => 1200,
        ]);
        $setResponse->assertStatus(200);
        $setResponse->assertJson([
            'success' => true,
            'time_formatted' => '20:00',
            'current_minute' => 20,
        ]);

        $match->refresh();
        $this->assertEquals(1200, $match->timer_seconds);
        $this->assertEquals(20, $match->current_minute);
    }

    /**
     * Test admin can create, update, and delete Master Venue.
     */
    public function test_admin_can_manage_venues(): void
    {
        $admin = User::where('role', 'admin')->first() ?? User::first();

        // 1. Create Venue
        $createResponse = $this->actingAs($admin)->post(route('admin.venues.store'), [
            'name' => 'GOR Bulungan Arena',
            'court_name' => 'Court 1 (Utama)',
            'address' => 'Jl. Bulungan No. 1',
            'city' => 'Jakarta Selatan',
            'description' => 'Lantai vinyl interlock dengan lampu sorot standar nasional.',
        ]);
        $createResponse->assertRedirect();
        $createResponse->assertSessionHas('success');

        $this->assertDatabaseHas('venues', [
            'name' => 'GOR Bulungan Arena',
            'court_name' => 'Court 1 (Utama)',
            'city' => 'Jakarta Selatan',
        ]);

        $venue = Venue::where('name', 'GOR Bulungan Arena')->first();
        $this->assertNotNull($venue);

        // 2. Update Venue
        $updateResponse = $this->actingAs($admin)->put(route('admin.venues.update', $venue->id), [
            'name' => 'GOR Bulungan International Arena',
            'court_name' => 'Court 1 VIP',
            'city' => 'Jakarta Selatan',
            'is_active' => true,
        ]);
        $updateResponse->assertRedirect();
        $this->assertDatabaseHas('venues', [
            'id' => $venue->id,
            'name' => 'GOR Bulungan International Arena',
        ]);

        // 3. Delete Venue
        $deleteResponse = $this->actingAs($admin)->delete(route('admin.venues.destroy', $venue->id));
        $deleteResponse->assertRedirect();
        $this->assertDatabaseMissing('venues', [
            'id' => $venue->id,
        ]);
    }

    /**
     * Test scheduling fixture using master venue.
     */
    public function test_admin_can_schedule_fixture_with_master_venue(): void
    {
        $admin = User::where('role', 'admin')->first() ?? User::first();
        $category = Category::first();
        $teams = Team::where('category_id', $category->id)->take(2)->get();
        $venue = Venue::first();

        $response = $this->actingAs($admin)->post(route('admin.fixtures.store'), [
            'category_id' => $category->id,
            'home_team_id' => $teams[0]->id,
            'away_team_id' => $teams[1]->id,
            'match_date' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'venue_id' => $venue->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('matches', [
            'category_id' => $category->id,
            'home_team_id' => $teams[0]->id,
            'away_team_id' => $teams[1]->id,
            'venue_id' => $venue->id,
            'venue' => $venue->name,
        ]);
    }

    /**
     * Test operator is forbidden (403) from accessing admin dashboard and mutating master data/fixtures.
     */
    public function test_operator_is_forbidden_from_admin_dashboard_and_mutations(): void
    {
        $operator = User::where('role', 'operator')->first();
        $this->assertNotNull($operator);

        // 1. Dashboard is forbidden for operator
        $this->actingAs($operator)->get(route('admin.dashboard'))->assertStatus(403);

        // 2. Team creation is forbidden for operator
        $category = Category::first();
        $this->actingAs($operator)->post(route('admin.teams.store'), [
            'name' => 'Unauthorized FC',
            'initials' => 'UFC',
            'category_id' => $category->id,
        ])->assertStatus(403);

        // 3. Venue creation is forbidden for operator
        $this->actingAs($operator)->post(route('admin.venues.store'), [
            'name' => 'Unauthorized Venue',
        ])->assertStatus(403);

        // 4. Fixture creation is forbidden for operator
        $teams = Team::where('category_id', $category->id)->take(2)->get();
        $this->actingAs($operator)->post(route('admin.fixtures.store'), [
            'category_id' => $category->id,
            'home_team_id' => $teams[0]->id,
            'away_team_id' => $teams[1]->id,
            'match_date' => now()->addDays(3)->format('Y-m-d H:i:s'),
        ])->assertStatus(403);
    }

    /**
     * Test operator can access fixtures schedule and control room.
     */
    public function test_operator_can_access_fixtures_and_control_room(): void
    {
        $operator = User::where('role', 'operator')->first();
        $this->assertNotNull($operator);

        // 1. Operator can view fixtures
        $fixtureResponse = $this->actingAs($operator)->get(route('admin.fixtures'));
        $fixtureResponse->assertStatus(200);
        $fixtureResponse->assertSee('MODE WASIT MEJA');

        // 2. Operator can view match control room
        $match = GameMatch::first();
        $controlResponse = $this->actingAs($operator)->get(route('admin.matches.control', $match->id));
        $controlResponse->assertStatus(200);
        $controlResponse->assertSee('OPERATOR CONTROL ROOM');
    }

    /**
     * Test full time match hides stopwatch deck and prevents any time adjustments.
     */
    public function test_full_time_disables_stopwatch_and_locks_time_adjustments(): void
    {
        $operator = User::where('role', 'operator')->first();
        $match = GameMatch::first();
        $match->update([
            'status' => 'finished',
            'timer_running' => false,
            'timer_started_at' => null,
            'timer_seconds' => 2400,
            'current_minute' => 40,
        ]);

        // 1. Control room view hides stopwatch and displays Full Time banner
        $response = $this->actingAs($operator)->get(route('admin.matches.control', $match->id));
        $response->assertStatus(200);
        $response->assertSee('FULL TIME');
        $response->assertSee('Laga Telah Selesai');
        $response->assertDontSee('STOPWATCH WAKTU BERSIH');
        $response->assertDontSee('MULAI WAKTU (START)');
        $response->assertDontSee('JEDA WAKTU (PAUSE)');

        // 2. toggleTimer is rejected with 422
        $toggle = $this->actingAs($operator)->postJson(route('admin.matches.timer.toggle', $match->id));
        $toggle->assertStatus(422);
        $toggle->assertJsonFragment(['success' => false]);

        // 3. setTimer is rejected with 422
        $set = $this->actingAs($operator)->postJson(route('admin.matches.timer.set', $match->id), [
            'seconds' => 1200,
        ]);
        $set->assertStatus(422);
        $set->assertJsonFragment(['success' => false]);

        // 4. updateMinute is rejected with 422
        $minute = $this->actingAs($operator)->postJson(route('admin.matches.minute', $match->id), [
            'current_minute' => 30,
        ]);
        $minute->assertStatus(422);
        $minute->assertJsonFragment(['success' => false]);
    }

    /**
     * Test superadmin can create, update and delete operator accounts.
     */
    public function test_superadmin_can_manage_operators(): void
    {
        $admin = User::where('role', 'admin')->first();

        // 1. Create operator
        $createRes = $this->actingAs($admin)->post(route('admin.operators.store'), [
            'name' => 'Budi Operator Futsal',
            'email' => 'budi.operator@futsal.test',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);
        $createRes->assertSessionHas('success');

        $operator = User::where('email', 'budi.operator@futsal.test')->first();
        $this->assertNotNull($operator);
        $this->assertEquals('operator', $operator->role);
        $this->assertEquals('Budi Operator Futsal', $operator->name);

        // 2. Update operator
        $updateRes = $this->actingAs($admin)->put(route('admin.operators.update', $operator->id), [
            'name' => 'Budi Santoso (Senior)',
            'email' => 'budi.operator@futsal.test',
        ]);
        $updateRes->assertSessionHas('success');
        $operator->refresh();
        $this->assertEquals('Budi Santoso (Senior)', $operator->name);

        // 3. Delete operator
        $deleteRes = $this->actingAs($admin)->delete(route('admin.operators.destroy', $operator->id));
        $deleteRes->assertSessionHas('success');
        $this->assertNull(User::find($operator->id));
    }

    /**
     * Test fixture creation with flexible half and extra time durations and operator assignments.
     */
    public function test_fixture_flexible_durations_and_operator_assignment(): void
    {
        $admin = User::where('role', 'admin')->first();
        $operatorA = User::where('role', 'operator')->first();
        $category = Category::first();
        $teams = Team::where('category_id', $category->id)->take(2)->get();

        $response = $this->actingAs($admin)->post(route('admin.fixtures.store'), [
            'category_id' => $category->id,
            'home_team_id' => $teams[0]->id,
            'away_team_id' => $teams[1]->id,
            'match_date' => now()->addDay()->format('Y-m-d H:i:s'),
            'venue' => 'Arena GOR A',
            'half_duration_minutes' => 12,
            'extra_time_duration_minutes' => 3,
            'operator_ids' => [$operatorA->id],
        ]);

        $response->assertSessionHas('success');

        $match = GameMatch::where('half_duration_minutes', 12)->first();
        $this->assertNotNull($match);
        $this->assertEquals(12, $match->half_duration_minutes);
        $this->assertEquals(3, $match->extra_time_duration_minutes);
        $this->assertEquals(720, $match->half_duration_seconds);
        $this->assertEquals(1440, $match->full_time_seconds);
        $this->assertEquals(180, $match->extra_time_seconds);
        $this->assertTrue($match->operators->contains('id', $operatorA->id));
    }

    /**
     * Test operator access restriction: unassigned operator gets 403 Forbidden.
     */
    public function test_unassigned_operator_cannot_access_or_control_match(): void
    {
        $operatorAssigned = User::where('role', 'operator')->first();
        $operatorUnassigned = User::create([
            'name' => 'Operator Baru Lain',
            'email' => 'unassigned.op@futsal.test',
            'password' => 'secret123',
            'role' => 'operator',
        ]);

        $match = GameMatch::first();
        // Ensure match only has operatorAssigned
        $match->operators()->sync([$operatorAssigned->id]);

        // 1. Assigned operator can access
        $this->actingAs($operatorAssigned)->get(route('admin.matches.control', $match->id))->assertStatus(200);

        // 2. Unassigned operator gets 403 Forbidden
        $this->actingAs($operatorUnassigned)->get(route('admin.matches.control', $match->id))->assertStatus(403);

        // 3. Unassigned operator cannot send control updates
        $this->actingAs($operatorUnassigned)->postJson(route('admin.matches.timer.toggle', $match->id))->assertStatus(403);
    }

    /**
     * Test period state transitions: sequential progression vs invalid jumping.
     */
    public function test_match_period_state_machine_and_dynamic_timer(): void
    {
        $admin = User::where('role', 'admin')->first();
        $category = Category::first();
        $teams = Team::where('category_id', $category->id)->take(2)->get();

        // Create a 12-minute match
        $match = GameMatch::create([
            'category_id' => $category->id,
            'home_team_id' => $teams[0]->id,
            'away_team_id' => $teams[1]->id,
            'match_date' => now()->addHour(),
            'venue' => 'Court Mini',
            'status' => 'scheduled',
            'half_duration_minutes' => 12,
            'extra_time_duration_minutes' => 3,
            'current_minute' => 0,
        ]);

        // 1. Trying to jump directly from scheduled to second_half without force is rejected (422)
        $invalidRes = $this->actingAs($admin)->postJson(route('admin.matches.status', $match->id), [
            'status' => 'second_half',
        ]);
        $invalidRes->assertStatus(422);

        // 2. Valid transition: scheduled -> first_half
        $step1 = $this->actingAs($admin)->postJson(route('admin.matches.status', $match->id), [
            'status' => 'first_half',
        ]);
        $step1->assertStatus(200);
        $match->refresh();
        $this->assertEquals('first_half', $match->status);
        $this->assertTrue($match->timer_running);

        // 3. Valid transition: first_half -> half_time (timer should auto-snap to half_duration_seconds = 720)
        $step2 = $this->actingAs($admin)->postJson(route('admin.matches.status', $match->id), [
            'status' => 'half_time',
        ]);
        $step2->assertStatus(200);
        $match->refresh();
        $this->assertEquals('half_time', $match->status);
        $this->assertFalse($match->timer_running);
        $this->assertEquals(720, $match->timer_seconds);
        $this->assertEquals(12, $match->current_minute);

        // 4. Valid transition: half_time -> second_half (timer starts from 720)
        $step3 = $this->actingAs($admin)->postJson(route('admin.matches.status', $match->id), [
            'status' => 'second_half',
        ]);
        $step3->assertStatus(200);
        $match->refresh();
        $this->assertEquals('second_half', $match->status);
        $this->assertTrue($match->timer_running);
        $this->assertEquals(720, $match->timer_seconds);

        // 5. Valid transition: second_half -> extra_time (starts at full_time_seconds = 1440)
        $step4 = $this->actingAs($admin)->postJson(route('admin.matches.status', $match->id), [
            'status' => 'extra_time',
        ]);
        $step4->assertStatus(200);
        $match->refresh();
        $this->assertEquals('extra_time', $match->status);
        $this->assertTrue($match->timer_running);
        $this->assertEquals(1440, $match->timer_seconds);

        // 6. Emergency rollback with force=true works
        $rollback = $this->actingAs($admin)->postJson(route('admin.matches.status', $match->id), [
            'status' => 'second_half',
            'force' => true,
        ]);
        $rollback->assertStatus(200);
        $match->refresh();
        $this->assertEquals('second_half', $match->status);

        // 7. Finish match (second_half -> finished)
        $finish = $this->actingAs($admin)->postJson(route('admin.matches.status', $match->id), [
            'status' => 'finished',
        ]);
        $finish->assertStatus(200);
        $match->refresh();
        $this->assertEquals('finished', $match->status);
        $this->assertFalse($match->timer_running);
    }

    /**
     * Test admin can perform CRUD on on-field Referees.
     */
    public function test_admin_can_manage_referees_crud(): void
    {
        $admin = User::where('role', 'admin')->first();

        // 1. Create Referee
        $createResponse = $this->actingAs($admin)->post(route('admin.referees.store'), [
            'name' => 'Fajar Irawan, S.Pd',
            'license' => 'FIFA Futsal Referee',
            'phone' => '081299887766',
            'city' => 'Surabaya',
            'is_active' => true,
        ]);
        $createResponse->assertRedirect();
        $createResponse->assertSessionHas('success');

        $this->assertDatabaseHas('referees', [
            'name' => 'Fajar Irawan, S.Pd',
            'license' => 'FIFA Futsal Referee',
            'city' => 'Surabaya',
        ]);

        $referee = Referee::where('name', 'Fajar Irawan, S.Pd')->first();
        $this->assertNotNull($referee);

        // 2. Update Referee
        $updateResponse = $this->actingAs($admin)->put(route('admin.referees.update', $referee->id), [
            'name' => 'Fajar Irawan, M.Pd',
            'license' => 'FIFA Elite Referee',
            'phone' => '081299887766',
            'city' => 'Malang',
            'is_active' => true,
        ]);
        $updateResponse->assertRedirect();
        $this->assertDatabaseHas('referees', [
            'id' => $referee->id,
            'name' => 'Fajar Irawan, M.Pd',
            'city' => 'Malang',
        ]);

        // 3. Delete Referee
        $deleteResponse = $this->actingAs($admin)->delete(route('admin.referees.destroy', $referee->id));
        $deleteResponse->assertRedirect();
        $this->assertDatabaseMissing('referees', [
            'id' => $referee->id,
        ]);
    }

    /**
     * Test scheduling fixture with on-field referees and preventing duplicate Wasit 1 & 2.
     */
    public function test_admin_can_schedule_fixture_with_on_field_referees(): void
    {
        $admin = User::where('role', 'admin')->first();
        $category = Category::first();
        $teams = Team::where('category_id', $category->id)->take(2)->get();
        $referees = Referee::take(3)->get();
        $this->assertGreaterThanOrEqual(2, $referees->count());

        // 1. Successful creation with 2 distinct referees
        $response = $this->actingAs($admin)->post(route('admin.fixtures.store'), [
            'category_id' => $category->id,
            'home_team_id' => $teams[0]->id,
            'away_team_id' => $teams[1]->id,
            'match_date' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'referee_1_id' => $referees[0]->id,
            'referee_2_id' => $referees[1]->id,
            'referee_3_id' => $referees[2]->id ?? null,
            'half_duration_minutes' => 15,
            'extra_time_duration_minutes' => 5,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('matches', [
            'category_id' => $category->id,
            'home_team_id' => $teams[0]->id,
            'away_team_id' => $teams[1]->id,
            'referee_1_id' => $referees[0]->id,
            'referee_2_id' => $referees[1]->id,
            'half_duration_minutes' => 15,
        ]);

        // 2. Validation error when referee 1 and referee 2 are the same person
        $duplicateResponse = $this->actingAs($admin)->post(route('admin.fixtures.store'), [
            'category_id' => $category->id,
            'home_team_id' => $teams[0]->id,
            'away_team_id' => $teams[1]->id,
            'match_date' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'referee_1_id' => $referees[0]->id,
            'referee_2_id' => $referees[0]->id, // Duplicate!
        ]);

        $duplicateResponse->assertSessionHasErrors('referee_2_id');
    }

    /**
     * Test public match detail displays on-field referee information.
     */
    public function test_public_can_view_match_detail_with_referees(): void
    {
        $match = GameMatch::whereNotNull('referee_1_id')->first();
        $this->assertNotNull($match);

        $response = $this->get(route('matches.show', $match->id));
        $response->assertStatus(200);
        $response->assertSee($match->referee1->name);
        $response->assertSee('Perangkat Pertandingan (Wasit Lapangan)');
    }

    /**
     * Test admin can create, update, and delete categories, with last category guard.
     */
    public function test_admin_category_management_and_deletion(): void
    {
        $admin = User::where('role', 'admin')->first();

        // 1. Create a new category
        $createResponse = $this->actingAs($admin)->post(route('admin.categories.store'), [
            'name' => 'Kategori U-13 Junior',
            'description' => 'Kategori untuk usia di bawah 13 tahun',
        ]);
        $createResponse->assertRedirect();
        $this->assertDatabaseHas('categories', ['name' => 'Kategori U-13 Junior', 'slug' => 'kategori-u-13-junior']);

        $category = Category::where('slug', 'kategori-u-13-junior')->first();

        // 2. Update the category
        $updateResponse = $this->actingAs($admin)->put(route('admin.categories.update', $category->id), [
            'name' => 'Kategori U-13 Junior Revisi',
            'description' => 'Deskripsi diperbarui',
        ]);
        $updateResponse->assertRedirect();
        $this->assertDatabaseHas('categories', ['name' => 'Kategori U-13 Junior Revisi']);

        // 3. Delete the newly created category (allowed since total categories > 1)
        $this->assertGreaterThan(1, Category::count());
        $deleteResponse = $this->actingAs($admin)->delete(route('admin.categories.destroy', $category->id));
        $deleteResponse->assertRedirect();
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);

        // 4. Test protection when only 1 category remains
        Category::where('id', '!=', Category::first()->id)->delete();
        $this->assertEquals(1, Category::count());
        $lastCategory = Category::first();

        $lastDeleteResponse = $this->actingAs($admin)->delete(route('admin.categories.destroy', $lastCategory->id));
        $lastDeleteResponse->assertSessionHas('error');
        $this->assertDatabaseHas('categories', ['id' => $lastCategory->id]);
    }

    /**
     * Test team logo upload, update, deletion, and public rendering.
     */
    public function test_team_logo_upload_management_and_public_rendering(): void
    {
        Storage::fake('public');
        $admin = User::where('role', 'admin')->first();
        $category = Category::first();

        // 1. Create team with logo upload
        $logoFile = UploadedFile::fake()->image('my_team_logo.png', 200, 200);
        $response = $this->actingAs($admin)->post(route('admin.teams.store'), [
            'category_id' => $category->id,
            'name' => 'Bintang Timur FC',
            'code' => 'BTF',
            'manager_name' => 'Coach Budi',
            'manager_contact' => '08123456789',
            'logo' => $logoFile,
        ]);

        $response->assertRedirect();
        $team = Team::where('name', 'Bintang Timur FC')->first();
        $this->assertNotNull($team);
        $this->assertNotNull($team->logo);
        Storage::disk('public')->assertExists($team->logo);
        $this->assertStringContainsString('storage/teams/', $team->logo_url);

        // 2. Public fan center and match display
        $match = GameMatch::where('category_id', $category->id)->first();
        $match->update(['home_team_id' => $team->id]);

        $publicResponse = $this->get('/');
        $publicResponse->assertStatus(200);
        $publicResponse->assertSee($team->logo_url);

        $detailResponse = $this->get(route('matches.show', $match->id));
        $detailResponse->assertStatus(200);
        $detailResponse->assertSee($team->logo_url);

        // 3. Update team with a new logo
        $oldLogoPath = $team->logo;
        $newLogoFile = UploadedFile::fake()->image('updated_logo.png', 300, 300);
        $updateResponse = $this->actingAs($admin)->put(route('admin.teams.update', $team->id), [
            'category_id' => $category->id,
            'name' => 'Bintang Timur FC Reborn',
            'code' => 'BTR',
            'logo' => $newLogoFile,
        ]);

        $updateResponse->assertRedirect();
        $team->refresh();
        $this->assertNotEquals($oldLogoPath, $team->logo);
        Storage::disk('public')->assertMissing($oldLogoPath);
        Storage::disk('public')->assertExists($team->logo);

        // 4. Delete team cleans up logo from storage
        $currentLogoPath = $team->logo;
        $deleteResponse = $this->actingAs($admin)->delete(route('admin.teams.destroy', $team->id));
        $deleteResponse->assertRedirect();
        $this->assertDatabaseMissing('teams', ['id' => $team->id]);
        Storage::disk('public')->assertMissing($currentLogoPath);
    }

    /**
     * Test captcha endpoint returns valid SVG and sets session.
     */
    public function test_captcha_endpoint_returns_svg_and_sets_session(): void
    {
        $response = $this->get(route('captcha'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'image/svg+xml');
        $this->assertNotEmpty(session('login_captcha'));
        $this->assertStringContainsString('<svg', $response->getContent());
    }

    /**
     * Test login validation requires captcha and fails with invalid captcha.
     */
    public function test_login_requires_and_validates_captcha(): void
    {
        // 1. Missing captcha fails validation
        $response = $this->post(route('login'), [
            'email' => 'admin@futsal.test',
            'password' => 'password',
        ]);
        $response->assertSessionHasErrors('captcha');
        $this->assertGuest();

        // 2. Invalid captcha fails validation
        $this->withSession(['login_captcha' => 'abcde']);
        $invalidResponse = $this->post(route('login'), [
            'email' => 'admin@futsal.test',
            'password' => 'password',
            'captcha' => 'wrongcode',
        ]);
        $invalidResponse->assertSessionHasErrors('captcha');
        $this->assertGuest();

        // 3. Valid captcha succeeds (case-insensitive)
        $this->withSession(['login_captcha' => 'x7k9p']);
        $validResponse = $this->post(route('login'), [
            'email' => 'admin@futsal.test',
            'password' => 'password',
            'captcha' => 'X7K9P',
        ]);
        $validResponse->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated();
    }
}
