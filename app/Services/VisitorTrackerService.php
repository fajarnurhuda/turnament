<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class VisitorTrackerService
{
    /**
     * Record a new unique visitor session.
     * Called once per browser session by TrackVisitorMetrics middleware.
     */
    public static function recordVisit(): void
    {
        try {
            if (Schema::hasTable('site_statistics')) {
                DB::table('site_statistics')
                    ->where('key', 'total_visits')
                    ->increment('value');

                // If cached, increment in-memory cache as well
                if (Cache::has('site_stats_total_visits')) {
                    Cache::increment('site_stats_total_visits');
                } else {
                    Cache::forget('site_stats_total_visits');
                }
            }
        } catch (\Throwable $e) {
            // Silently recover if database temporarily unreachable
        }
    }

    /**
     * Retrieve visitor statistics (active online and cumulative visits).
     * High-performance cached reads (zero database strain).
     *
     * @return array{online: int, total: int, online_formatted: string, total_formatted: string}
     */
    public static function getStats(): array
    {
        $online = self::getOnlineCount();
        $total = self::getTotalVisits();

        return [
            'online' => $online,
            'total' => $total,
            'online_formatted' => number_format($online, 0, ',', '.'),
            'total_formatted' => number_format($total, 0, ',', '.'),
        ];
    }

    /**
     * Get count of active visitors in the last 5 minutes.
     * Cached for 30 seconds to prevent query floods under high traffic.
     */
    public static function getOnlineCount(): int
    {
        return Cache::remember('site_stats_online_count', 30, function (): int {
            try {
                if (Schema::hasTable('sessions')) {
                    $fiveMinutesAgo = now()->subMinutes(5)->timestamp;
                    $count = DB::table('sessions')
                        ->where('last_activity', '>=', $fiveMinutesAgo)
                        ->count();

                    return max(1, (int) $count);
                }
            } catch (\Throwable $e) {
            }

            return 1;
        });
    }

    /**
     * Get total cumulative visits.
     * Cached for 60 seconds.
     */
    public static function getTotalVisits(): int
    {
        return Cache::remember('site_stats_total_visits', 60, function (): int {
            try {
                if (Schema::hasTable('site_statistics')) {
                    $record = DB::table('site_statistics')
                        ->where('key', 'total_visits')
                        ->first();

                    if (! $record) {
                        DB::table('site_statistics')->insert([
                            'key' => 'total_visits',
                            'value' => 1,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        return 1;
                    }

                    return max(1, (int) $record->value);
                }
            } catch (\Throwable $e) {
            }

            return 1;
        });
    }
}
