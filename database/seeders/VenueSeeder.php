<?php

namespace Database\Seeders;

use App\Models\GameMatch;
use App\Models\Venue;
use Illuminate\Database\Seeder;

class VenueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $venue1 = Venue::firstOrCreate(
            ['name' => 'Court A - GOR Futsal Arena Utama'],
            [
                'court_name' => 'Court A (Utama)',
                'address' => 'Jl. Stadion Pemuda No. 12',
                'city' => 'Jakarta Selatan',
                'description' => 'Lapangan vinyl standar AFC dengan tribun kapasitas 1.500 penonton.',
                'is_active' => true,
            ]
        );

        $venue2 = Venue::firstOrCreate(
            ['name' => 'Court B - GOR Futsal Arena Utama'],
            [
                'court_name' => 'Court B (Sayap Barat)',
                'address' => 'Jl. Stadion Pemuda No. 12',
                'city' => 'Jakarta Selatan',
                'description' => 'Lapangan taraflex dengan pencahayaan LED 800 lux.',
                'is_active' => true,
            ]
        );

        $venue3 = Venue::firstOrCreate(
            ['name' => 'Court Utama GOR'],
            [
                'court_name' => 'Center Court GOR',
                'address' => 'Kompleks Olahraga Mahasiswa',
                'city' => 'Jakarta Pusat',
                'description' => 'Lapangan utama perebutan medali dan final.',
                'is_active' => true,
            ]
        );

        $venue4 = Venue::firstOrCreate(
            ['name' => 'Lapangan Futsal Siliwangi'],
            [
                'court_name' => 'Lapangan 1',
                'address' => 'Jl. Lombok No. 8',
                'city' => 'Bandung',
                'description' => 'Venue cadangan inter-provinsi.',
                'is_active' => true,
            ]
        );

        // Link existing matches
        GameMatch::where('venue', 'like', '%Court A%')->update(['venue_id' => $venue1->id]);
        GameMatch::where('venue', 'like', '%Court B%')->update(['venue_id' => $venue2->id]);
        GameMatch::where('venue', 'like', '%Court Utama%')->update(['venue_id' => $venue3->id]);
    }
}
