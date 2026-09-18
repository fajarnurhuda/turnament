<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\GameMatch;
use App\Models\Group;
use App\Models\MatchEvent;
use App\Models\Player;
use App\Models\Referee;
use App\Models\Stage;
use App\Models\Team;
use App\Models\User;
use App\Models\Venue;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TournamentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Users
        User::updateOrCreate(
            ['email' => 'admin@futsal.test'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'operator@futsal.test'],
            [
                'name' => 'Wasit Meja Arena 01',
                'password' => Hash::make('password'),
                'role' => 'operator',
                'email_verified_at' => now(),
            ]
        );

        // 2. Categories
        $catU17 = Category::create([
            'name' => 'U-17 Putra Nasional',
            'slug' => 'u-17-putra',
            'description' => 'Kejuaraan Futsal Kelompok Umur 17 Tahun Putra Tingkat Nasional 2025',
        ]);

        $catUmum = Category::create([
            'name' => 'Kategori Umum (Single Group)',
            'slug' => 'kategori-umum',
            'description' => 'Turnamen Terbuka Sistem Liga 1 Grup Tunggal',
        ]);

        // 3. Stages & Groups for U-17
        $stageGroupU17 = Stage::create([
            'category_id' => $catU17->id,
            'name' => 'Babak Grup',
            'type' => 'group',
            'order_num' => 1,
        ]);

        $stageSemiU17 = Stage::create([
            'category_id' => $catU17->id,
            'name' => 'Semifinal',
            'type' => 'knockout',
            'order_num' => 2,
        ]);

        $stageFinalU17 = Stage::create([
            'category_id' => $catU17->id,
            'name' => 'Grand Final',
            'type' => 'knockout',
            'order_num' => 3,
        ]);

        $groupA = Group::create([
            'stage_id' => $stageGroupU17->id,
            'name' => 'Grup A',
        ]);

        $groupB = Group::create([
            'stage_id' => $stageGroupU17->id,
            'name' => 'Grup B',
        ]);

        // Stages & Single Group for Umum
        $stageLigaUmum = Stage::create([
            'category_id' => $catUmum->id,
            'name' => 'Babak Liga',
            'type' => 'group',
            'order_num' => 1,
        ]);

        $singleGroupUmum = Group::create([
            'stage_id' => $stageLigaUmum->id,
            'name' => 'Grup Tunggal',
        ]);

        // 4. Teams & Players
        $teamsDataU17 = [
            [
                'name' => 'Garuda Muda FC',
                'code' => 'GFA',
                'manager' => 'Coach Hendra Wijaya',
                'group' => $groupA,
                'players' => [
                    ['name' => 'Rian Albagir', 'no' => 1, 'pos' => 'GK'],
                    ['name' => 'Rio Rizki Pratama', 'no' => 4, 'pos' => 'DEF', 'captain' => true],
                    ['name' => 'Dimas Syauqi', 'no' => 7, 'pos' => 'FLA'],
                    ['name' => 'Bintang Subhan', 'no' => 10, 'pos' => 'FLA'],
                    ['name' => 'Evan Runtuboy', 'no' => 9, 'pos' => 'PIV'],
                    ['name' => 'Arif Fajar', 'no' => 11, 'pos' => 'FLA'],
                ],
            ],
            [
                'name' => 'Bintang Timur Surabaya Jr',
                'code' => 'BTS',
                'manager' => 'Coach Rakha Pratama',
                'group' => $groupA,
                'players' => [
                    ['name' => 'Ahmad Habibie', 'no' => 2, 'pos' => 'GK'],
                    ['name' => 'Sunny Rizky', 'no' => 5, 'pos' => 'DEF', 'captain' => true],
                    ['name' => 'Singgih Romana', 'no' => 8, 'pos' => 'FLA'],
                    ['name' => 'Samuel Eko', 'no' => 14, 'pos' => 'PIV'],
                    ['name' => 'Aditya Muhammad', 'no' => 12, 'pos' => 'FLA'],
                    ['name' => 'Fadilah Nur', 'no' => 6, 'pos' => 'DEF'],
                ],
            ],
            [
                'name' => 'Cosmo JNE Academy',
                'code' => 'CJN',
                'manager' => 'Coach Deni Handoko',
                'group' => $groupA,
                'players' => [
                    ['name' => 'Kennet Erick', 'no' => 1, 'pos' => 'GK'],
                    ['name' => 'Dewa Rizki', 'no' => 3, 'pos' => 'DEF'],
                    ['name' => 'Reza Yamani', 'no' => 9, 'pos' => 'FLA', 'captain' => true],
                    ['name' => 'Alfajri Zikri', 'no' => 10, 'pos' => 'PIV'],
                    ['name' => 'Farhan Mujahiddin', 'no' => 13, 'pos' => 'FLA'],
                ],
            ],
            [
                'name' => 'Blacksteel Papua Muda',
                'code' => 'BSA',
                'manager' => 'Coach Checha Samuel',
                'group' => $groupB,
                'players' => [
                    ['name' => 'M. Alfaridzi', 'no' => 1, 'pos' => 'GK'],
                    ['name' => 'Ardiansyah Nur', 'no' => 4, 'pos' => 'DEF', 'captain' => true],
                    ['name' => 'Holypaul Septinus', 'no' => 7, 'pos' => 'FLA'],
                    ['name' => 'Wendy Brian', 'no' => 10, 'pos' => 'FLA'],
                    ['name' => 'Diego Souza Jr', 'no' => 11, 'pos' => 'PIV'],
                ],
            ],
            [
                'name' => 'Kancil WHW Pontianak Jr',
                'code' => 'KCL',
                'manager' => 'Coach Wahyudin',
                'group' => $groupB,
                'players' => [
                    ['name' => 'Dian Rohmansyah', 'no' => 20, 'pos' => 'GK'],
                    ['name' => 'Marvin Alexa', 'no' => 8, 'pos' => 'DEF', 'captain' => true],
                    ['name' => 'Rama Adithia', 'no' => 17, 'pos' => 'FLA'],
                    ['name' => 'Daniel Alves', 'no' => 99, 'pos' => 'PIV'],
                    ['name' => 'Filipe Santos', 'no' => 19, 'pos' => 'FLA'],
                ],
            ],
            [
                'name' => 'Halus FC Jakarta Jr',
                'code' => 'HLS',
                'manager' => 'Coach Nur Ali',
                'group' => $groupB,
                'players' => [
                    ['name' => 'Gerry Anesta', 'no' => 12, 'pos' => 'GK'],
                    ['name' => 'Bambang Bayu', 'no' => 5, 'pos' => 'DEF', 'captain' => true],
                    ['name' => 'Hamzah', 'no' => 11, 'pos' => 'FLA'],
                    ['name' => 'Ikrima Nofiansyah', 'no' => 18, 'pos' => 'PIV'],
                ],
            ],
        ];

        $teamModels = [];
        $playerModels = [];

        foreach ($teamsDataU17 as $t) {
            $team = Team::create([
                'category_id' => $catU17->id,
                'name' => $t['name'],
                'code' => $t['code'],
                'manager_name' => $t['manager'],
                'manager_contact' => '0812'.rand(10000000, 99999999),
            ]);
            $teamModels[$t['code']] = $team;

            foreach ($t['players'] as $p) {
                $player = Player::create([
                    'team_id' => $team->id,
                    'name' => $p['name'],
                    'jersey_number' => $p['no'],
                    'position' => $p['pos'],
                    'is_captain' => $p['captain'] ?? false,
                ]);
                $playerModels[$team->code.'_'.$p['no']] = $player;
            }
        }

        // Teams for Umum (Single Group)
        $teamsDataUmum = [
            ['name' => 'Fafage Banua FC', 'code' => 'FFG', 'manager' => 'Coach Sayan Karmadi'],
            ['name' => 'Unggul FC Malang', 'code' => 'UGL', 'manager' => 'Coach Joao Almeida'],
            ['name' => 'Moncongbulo FC Makassar', 'code' => 'MCB', 'manager' => 'Coach Mario'],
            ['name' => 'Radit FC Kalbar', 'code' => 'RDT', 'manager' => 'Coach Naim'],
        ];

        foreach ($teamsDataUmum as $tu) {
            $team = Team::create([
                'category_id' => $catUmum->id,
                'name' => $tu['name'],
                'code' => $tu['code'],
                'manager_name' => $tu['manager'],
                'manager_contact' => '0813'.rand(10000000, 99999999),
            ]);
            $teamModels[$tu['code']] = $team;

            // Generate 5 starter players
            $positions = ['GK', 'DEF', 'FLA', 'FLA', 'PIV'];
            $sampleNames = ['Bagus', 'Candra', 'Deden', 'Eko', 'Fajar'];
            foreach ($positions as $idx => $pos) {
                $p = Player::create([
                    'team_id' => $team->id,
                    'name' => $sampleNames[$idx].' '.$tu['code'],
                    'jersey_number' => $idx === 0 ? 1 : ($idx * 4),
                    'position' => $pos,
                    'is_captain' => $idx === 1,
                ]);
                $playerModels[$team->code.'_'.$p->jersey_number] = $p;
            }
        }

        // 5. Create Venues
        $venue1 = Venue::firstOrCreate(
            ['name' => 'Court A - GOR Futsal Arena Utama'],
            ['court_name' => 'Court A', 'city' => 'Jakarta Selatan', 'address' => 'GOR Arena Utama Lantai 1', 'is_active' => true]
        );
        $venue2 = Venue::firstOrCreate(
            ['name' => 'Court B - GOR Futsal Arena Utama'],
            ['court_name' => 'Court B', 'city' => 'Jakarta Selatan', 'address' => 'GOR Arena Utama Lantai 1', 'is_active' => true]
        );
        $venue3 = Venue::firstOrCreate(
            ['name' => 'Court Utama GOR'],
            ['court_name' => 'Center Court', 'city' => 'Jakarta Selatan', 'address' => 'GOR Arena Utama Center', 'is_active' => true]
        );

        // 5b. Create Master Wasit di Lapangan (Pitch Referees)
        $ref1 = Referee::firstOrCreate(
            ['name' => 'Agus Suryanto, S.Pd'],
            [
                'license' => 'FIFA Futsal Referee',
                'phone' => '081234567890',
                'city' => 'Jakarta Selatan',
                'is_active' => true,
            ]
        );

        $ref2 = Referee::firstOrCreate(
            ['name' => 'Hendro Kartiko, M.Or'],
            [
                'license' => 'Lisensi 1 Nasional',
                'phone' => '081398765432',
                'city' => 'Bandung',
                'is_active' => true,
            ]
        );

        $ref3 = Referee::firstOrCreate(
            ['name' => 'Bambang Sudrajat'],
            [
                'license' => 'Lisensi 2 Provinsi',
                'phone' => '085712345678',
                'city' => 'Surabaya',
                'is_active' => true,
            ]
        );

        $ref4 = Referee::firstOrCreate(
            ['name' => 'Dwi Prasetyo'],
            [
                'license' => 'Lisensi 1 Nasional',
                'phone' => '082155667788',
                'city' => 'Semarang',
                'is_active' => true,
            ]
        );

        // 6. Create Matches & Events
        // Match 1: LIVE MATCH (Garuda vs Bintang Timur)
        $liveMatch = GameMatch::create([
            'category_id' => $catU17->id,
            'stage_id' => $stageGroupU17->id,
            'group_id' => $groupA->id,
            'home_team_id' => $teamModels['GFA']->id,
            'away_team_id' => $teamModels['BTS']->id,
            'home_score' => 2,
            'away_score' => 1,
            'match_date' => Carbon::today()->setTime(14, 0),
            'venue_id' => $venue1->id,
            'venue' => 'Court A - GOR Futsal Arena Utama',
            'referee_1_id' => $ref1->id,
            'referee_2_id' => $ref2->id,
            'referee_3_id' => $ref3->id,
            'status' => 'first_half',
            'current_minute' => 14,
            'timer_seconds' => 840,
            'timer_running' => true,
            'timer_started_at' => now(),
        ]);

        // Live Match Events
        MatchEvent::create([
            'match_id' => $liveMatch->id,
            'team_id' => $teamModels['GFA']->id,
            'player_id' => $playerModels['GFA_9']->id, // Evan Runtuboy
            'assist_player_id' => $playerModels['GFA_7']->id, // Dimas Syauqi
            'event_type' => 'goal',
            'minute' => 4,
            'notes' => 'Finishing placing kaki kiri ke sudut kanan bawah gawang',
        ]);

        MatchEvent::create([
            'match_id' => $liveMatch->id,
            'team_id' => $teamModels['BTS']->id,
            'player_id' => $playerModels['BTS_5']->id, // Sunny Rizky
            'event_type' => 'yellow_card',
            'minute' => 9,
            'notes' => 'Tarikan baju saat transisi serangan balik cepat',
        ]);

        MatchEvent::create([
            'match_id' => $liveMatch->id,
            'team_id' => $teamModels['BTS']->id,
            'player_id' => $playerModels['BTS_14']->id, // Samuel Eko
            'assist_player_id' => $playerModels['BTS_8']->id,
            'event_type' => 'goal',
            'minute' => 11,
            'notes' => 'Pivot turn mendatar menembus sela kaki kiper',
        ]);

        MatchEvent::create([
            'match_id' => $liveMatch->id,
            'team_id' => $teamModels['GFA']->id,
            'player_id' => $playerModels['GFA_10']->id, // Bintang Subhan
            'event_type' => 'goal',
            'minute' => 14,
            'notes' => 'Tendangan voli keras memanfaatkan bola muntah sepak pojok',
        ]);

        // Match 2: FINISHED MATCH (Cosmo JNE 4 - 2 Blacksteel)
        $finishedMatch1 = GameMatch::create([
            'category_id' => $catU17->id,
            'stage_id' => $stageGroupU17->id,
            'group_id' => $groupA->id,
            'home_team_id' => $teamModels['CJN']->id,
            'away_team_id' => $teamModels['BSA']->id,
            'home_score' => 4,
            'away_score' => 2,
            'match_date' => Carbon::today()->setTime(10, 0),
            'venue' => 'Court A - GOR Futsal Arena Utama',
            'status' => 'finished',
            'current_minute' => 40,
        ]);

        // Events for Finished Match 1
        MatchEvent::create([
            'match_id' => $finishedMatch1->id,
            'team_id' => $teamModels['CJN']->id,
            'player_id' => $playerModels['CJN_9']->id, // Reza Yamani
            'event_type' => 'goal',
            'minute' => 6,
        ]);
        MatchEvent::create([
            'match_id' => $finishedMatch1->id,
            'team_id' => $teamModels['CJN']->id,
            'player_id' => $playerModels['CJN_10']->id, // Alfajri
            'assist_player_id' => $playerModels['CJN_9']->id,
            'event_type' => 'goal',
            'minute' => 12,
        ]);
        MatchEvent::create([
            'match_id' => $finishedMatch1->id,
            'team_id' => $teamModels['BSA']->id,
            'player_id' => $playerModels['BSA_10']->id, // Wendy Brian
            'event_type' => 'goal',
            'minute' => 18,
        ]);
        MatchEvent::create([
            'match_id' => $finishedMatch1->id,
            'team_id' => $teamModels['CJN']->id,
            'player_id' => $playerModels['CJN_9']->id, // Reza Yamani
            'event_type' => 'goal',
            'minute' => 25,
        ]);
        MatchEvent::create([
            'match_id' => $finishedMatch1->id,
            'team_id' => $teamModels['CJN']->id,
            'player_id' => $playerModels['CJN_13']->id,
            'event_type' => 'goal',
            'minute' => 31,
        ]);
        MatchEvent::create([
            'match_id' => $finishedMatch1->id,
            'team_id' => $teamModels['BSA']->id,
            'player_id' => $playerModels['BSA_11']->id,
            'event_type' => 'goal',
            'minute' => 38,
        ]);
        MatchEvent::create([
            'match_id' => $finishedMatch1->id,
            'team_id' => $teamModels['BSA']->id,
            'player_id' => $playerModels['BSA_4']->id,
            'event_type' => 'red_card',
            'minute' => 39,
            'notes' => 'Pelanggaran keras profesional foul',
        ]);

        // Match 3: FINISHED MATCH (Kancil WHW 3 - 3 Halus FC)
        $finishedMatch2 = GameMatch::create([
            'category_id' => $catU17->id,
            'stage_id' => $stageGroupU17->id,
            'group_id' => $groupB->id,
            'home_team_id' => $teamModels['KCL']->id,
            'away_team_id' => $teamModels['HLS']->id,
            'home_score' => 3,
            'away_score' => 3,
            'match_date' => Carbon::yesterday()->setTime(16, 0),
            'venue' => 'Court B - GOR Futsal Arena Utama',
            'status' => 'finished',
            'current_minute' => 40,
        ]);

        MatchEvent::create([
            'match_id' => $finishedMatch2->id,
            'team_id' => $teamModels['KCL']->id,
            'player_id' => $playerModels['KCL_99']->id,
            'event_type' => 'goal',
            'minute' => 5,
        ]);
        MatchEvent::create([
            'match_id' => $finishedMatch2->id,
            'team_id' => $teamModels['HLS']->id,
            'player_id' => $playerModels['HLS_11']->id,
            'event_type' => 'goal',
            'minute' => 14,
        ]);
        MatchEvent::create([
            'match_id' => $finishedMatch2->id,
            'team_id' => $teamModels['KCL']->id,
            'player_id' => $playerModels['KCL_17']->id,
            'event_type' => 'goal',
            'minute' => 22,
        ]);
        MatchEvent::create([
            'match_id' => $finishedMatch2->id,
            'team_id' => $teamModels['HLS']->id,
            'player_id' => $playerModels['HLS_18']->id,
            'event_type' => 'goal',
            'minute' => 29,
        ]);
        MatchEvent::create([
            'match_id' => $finishedMatch2->id,
            'team_id' => $teamModels['KCL']->id,
            'player_id' => $playerModels['KCL_99']->id,
            'event_type' => 'goal',
            'minute' => 36,
        ]);
        MatchEvent::create([
            'match_id' => $finishedMatch2->id,
            'team_id' => $teamModels['HLS']->id,
            'player_id' => $playerModels['HLS_18']->id,
            'event_type' => 'goal',
            'minute' => 39,
        ]);

        // Match 4: UPCOMING MATCH (Garuda vs Cosmo JNE)
        GameMatch::create([
            'category_id' => $catU17->id,
            'stage_id' => $stageGroupU17->id,
            'group_id' => $groupA->id,
            'home_team_id' => $teamModels['GFA']->id,
            'away_team_id' => $teamModels['CJN']->id,
            'home_score' => 0,
            'away_score' => 0,
            'match_date' => Carbon::today()->setTime(18, 30),
            'venue' => 'Court A - GOR Futsal Arena Utama',
            'status' => 'scheduled',
            'current_minute' => 0,
        ]);

        // Match 5: UPCOMING MATCH (Bintang Timur vs Blacksteel)
        GameMatch::create([
            'category_id' => $catU17->id,
            'stage_id' => $stageGroupU17->id,
            'group_id' => $groupB->id,
            'home_team_id' => $teamModels['BTS']->id,
            'away_team_id' => $teamModels['BSA']->id,
            'home_score' => 0,
            'away_score' => 0,
            'match_date' => Carbon::tomorrow()->setTime(15, 0),
            'venue' => 'Court A - GOR Futsal Arena Utama',
            'status' => 'scheduled',
            'current_minute' => 0,
        ]);

        // Matches in Kategori Umum (Single Group)
        $umumFinished1 = GameMatch::create([
            'category_id' => $catUmum->id,
            'stage_id' => $stageLigaUmum->id,
            'group_id' => $singleGroupUmum->id,
            'home_team_id' => $teamModels['FFG']->id,
            'away_team_id' => $teamModels['UGL']->id,
            'home_score' => 3,
            'away_score' => 1,
            'match_date' => Carbon::yesterday()->setTime(19, 0),
            'venue' => 'Court Utama GOR',
            'status' => 'finished',
            'current_minute' => 40,
        ]);

        $umumFinished2 = GameMatch::create([
            'category_id' => $catUmum->id,
            'stage_id' => $stageLigaUmum->id,
            'group_id' => $singleGroupUmum->id,
            'home_team_id' => $teamModels['MCB']->id,
            'away_team_id' => $teamModels['RDT']->id,
            'home_score' => 2,
            'away_score' => 2,
            'match_date' => Carbon::yesterday()->setTime(20, 30),
            'venue' => 'Court Utama GOR',
            'status' => 'finished',
            'current_minute' => 40,
        ]);

        GameMatch::create([
            'category_id' => $catUmum->id,
            'stage_id' => $stageLigaUmum->id,
            'group_id' => $singleGroupUmum->id,
            'home_team_id' => $teamModels['FFG']->id,
            'away_team_id' => $teamModels['MCB']->id,
            'home_score' => 0,
            'away_score' => 0,
            'match_date' => Carbon::tomorrow()->setTime(19, 0),
            'venue' => 'Court Utama GOR',
            'status' => 'scheduled',
            'current_minute' => 0,
        ]);

        $operatorUser = User::where('role', 'operator')->first();
        if ($operatorUser) {
            GameMatch::all()->each(function ($m) use ($operatorUser) {
                $m->operators()->syncWithoutDetaching([$operatorUser->id]);
            });
        }
    }
}
