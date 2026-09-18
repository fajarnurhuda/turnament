@extends('layouts.app')

@section('title', 'Public Fan Center & Live Match Telemetry - LDII CUP TANJUNG PINANG')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-8" x-data="fanCenterData({{ $featuredMatch ? $featuredMatch->id : 'null' }})">

        <!-- Category Selector Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-court-border">
            <div>
                <h1
                    class="text-2xl font-headline font-bold uppercase tracking-wider text-text-primary flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-stadium-emerald animate-pulse"></span>
                    Pusat Pertandingan & Fan Center
                </h1>
                <p class="text-xs font-mono text-text-muted mt-0.5">TELEMETRY SCOREBOARD & LIVE MATCH TRACKER</p>
            </div>

            <!-- Categories List -->
            <div class="flex items-center gap-2 overflow-x-auto pb-1">
                @foreach ($categories as $cat)
                    <a href="{{ route('home', ['category' => $cat->slug]) }}"
                        class="px-4 py-2 rounded text-xs font-headline font-bold uppercase tracking-wider transition-all whitespace-nowrap {{ $activeCategory && $activeCategory->id === $cat->id ? 'bg-stadium-emerald text-court-navy shadow-[0_0_15px_rgba(0,255,135,0.3)]' : 'bg-court-surface hover:bg-court-surface-elevated text-text-muted hover:text-text-primary border border-court-border' }}">
                        {{ $cat->name }}
                    </a>
                @endforeach
            </div>
        </div>

        <!-- FEATURED MATCH TELEMETRY BANNER -->
        @if ($featuredMatch)
            <div
                class="relative rounded-xl overflow-hidden bg-court-surface border border-court-border shadow-2xl p-6 sm:p-8">
                <!-- Background Glow Effect -->
                <div
                    class="absolute -top-24 -left-24 w-96 h-96 bg-stadium-emerald/10 rounded-full blur-3xl pointer-events-none">
                </div>
                <div
                    class="absolute -bottom-24 -right-24 w-96 h-96 bg-telemetry-cyan/10 rounded-full blur-3xl pointer-events-none">
                </div>

                <div class="relative z-10">
                    <!-- Status Top Bar -->
                    <div class="flex items-center justify-between pb-6 mb-6 border-b border-court-border/60">
                        <div class="flex items-center gap-3">
                            <span
                                class="px-3 py-1 rounded text-xs font-mono font-bold tracking-wider border {{ $featuredMatch->status_badge['color'] }}"
                                x-text="featured.status_badge.text">
                                {{ $featuredMatch->status_badge['text'] }}
                            </span>
                            <span class="text-xs font-mono text-text-muted">
                                {{ $featuredMatch->venue }} &bull; {{ $featuredMatch->match_date->format('d M Y, H:i') }}
                                WIB
                            </span>
                        </div>

                        <div class="flex items-center gap-2 font-mono text-xs">
                            <span class="text-text-muted">STAGE:</span>
                            <span class="text-telemetry-cyan uppercase">{{ $featuredMatch->stage?->name ?? 'Turnamen' }}
                                {{ $featuredMatch->group ? '(' . $featuredMatch->group->name . ')' : '' }}</span>
                        </div>
                    </div>

                    <!-- Main Scoreboard Section -->
                    <div class="grid grid-cols-1 md:grid-cols-11 items-center gap-6 text-center">
                        <!-- Home Team -->
                        <div class="md:col-span-4 flex flex-col md:flex-row items-center justify-end gap-4">
                            <div class="text-center md:text-right order-2 md:order-1">
                                <h2 class="text-xl sm:text-2xl font-headline font-bold text-text-primary">
                                    {{ $featuredMatch->homeTeam->name }}</h2>
                                <p class="text-xs font-mono text-text-muted">KANDANG (HOME)</p>
                            </div>
                            <div
                                class="w-16 h-16 sm:w-20 sm:h-20 rounded-xl bg-court-surface-elevated border border-court-border flex items-center justify-center font-headline font-black text-2xl text-stadium-emerald shadow-inner order-1 md:order-2 overflow-hidden p-1.5">
                                @if ($featuredMatch->homeTeam->logo)
                                    <img src="{{ $featuredMatch->homeTeam->logo_url }}"
                                        alt="{{ $featuredMatch->homeTeam->name }}" class="w-full h-full object-contain">
                                @else
                                    {{ $featuredMatch->homeTeam->initials }}
                                @endif
                            </div>
                        </div>

                        <!-- Score Center Readout (Sesuai Referensi) -->
                        <div
                            class="md:col-span-3 flex flex-col items-center justify-center py-4 bg-court-navy/80 rounded-xl border border-court-border/60">
                            <div
                                class="flex items-center gap-4 font-headline text-5xl sm:text-6xl font-black tracking-tight">
                                <span class="text-stadium-emerald"
                                    x-text="featured.home_score">{{ $featuredMatch->home_score }}</span>
                                <span class="text-court-border">:</span>
                                <span class="text-text-primary"
                                    x-text="featured.away_score">{{ $featuredMatch->away_score }}</span>
                            </div>

                            <!-- Status Babak & Menit Pertandingan -->
                            <div class="mt-3 flex items-center gap-2 font-mono text-xs">
                                <span class="px-2.5 py-0.5 rounded font-bold border" :class="featured.status_badge.color"
                                    x-text="featured.status_badge.text">
                                    {{ $featuredMatch->status_badge['text'] }}
                                </span>
                                <span x-show="featured.is_live" class="text-telemetry-cyan font-bold tracking-wider">
                                    MENIT <span
                                        x-text="featured.current_minute">{{ $featuredMatch->current_minute }}</span>'
                                </span>
                            </div>
                        </div>

                        <!-- Away Team -->
                        <div class="md:col-span-4 flex flex-col md:flex-row items-center justify-start gap-4">
                            <div
                                class="w-16 h-16 sm:w-20 sm:h-20 rounded-xl bg-court-surface-elevated border border-court-border flex items-center justify-center font-headline font-black text-2xl text-telemetry-cyan shadow-inner overflow-hidden p-1.5">
                                @if ($featuredMatch->awayTeam->logo)
                                    <img src="{{ $featuredMatch->awayTeam->logo_url }}"
                                        alt="{{ $featuredMatch->awayTeam->name }}" class="w-full h-full object-contain">
                                @else
                                    {{ $featuredMatch->awayTeam->initials }}
                                @endif
                            </div>
                            <div class="text-center md:text-left">
                                <h2 class="text-xl sm:text-2xl font-headline font-bold text-text-primary">
                                    {{ $featuredMatch->awayTeam->name }}</h2>
                                <p class="text-xs font-mono text-text-muted">TANDANG (AWAY)</p>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Quick Actions & Timeline Link -->
                    <div
                        class="mt-8 pt-6 border-t border-court-border/60 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="text-xs font-mono text-text-muted flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm text-stadium-emerald">sync</span>
                            <span>PEMBARUAN TELEMETRI OTOMATIS AKTIF (TIAP 4 DETIK)</span>
                        </div>

                        <div class="flex items-center gap-3">
                            <a href="{{ route('matches.show', $featuredMatch->id) }}"
                                class="px-5 py-2.5 rounded text-xs font-headline font-bold bg-stadium-emerald hover:bg-stadium-emerald/90 text-court-navy uppercase tracking-wider transition-all flex items-center gap-2 shadow-[0_0_15px_rgba(0,255,135,0.25)]">
                                <span class="material-symbols-outlined text-sm">timeline</span>
                                Buka Lini Masa Lengkap & Statistik
                            </a>

                            @auth
                                <a href="{{ route('admin.matches.control', $featuredMatch->id) }}"
                                    class="px-4 py-2.5 rounded text-xs font-headline font-bold bg-court-surface-elevated hover:bg-court-border text-telemetry-cyan uppercase tracking-wider border border-court-border transition-all flex items-center gap-2">
                                    <span class="material-symbols-outlined text-sm">tune</span>
                                    Control Room Wasit
                                </a>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- MAIN TWO-COLUMN SECTION: FIXTURES + STANDINGS & STATS -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

            <!-- LEFT COLUMN: FIXTURES & MATCH SCHEDULE (7 cols) -->
            <div class="lg:col-span-7 space-y-6">
                <div class="bg-court-surface rounded-xl border border-court-border overflow-hidden" x-data="{ activeTab: 'all' }">
                    <!-- Tabs Header -->
                    <div
                        class="flex items-center justify-between px-6 py-4 border-b border-court-border bg-court-surface-elevated/40">
                        <h3 class="font-headline font-bold uppercase tracking-wider text-sm flex items-center gap-2">
                            <span class="material-symbols-outlined text-stadium-emerald text-base">calendar_month</span>
                            Jadwal & Hasil Laga
                        </h3>

                        <!-- Filter Tabs -->
                        <div
                            class="flex items-center gap-1 bg-court-navy p-1 rounded border border-court-border text-xs font-mono">
                            <button @click="activeTab = 'all'"
                                :class="activeTab === 'all' ? 'bg-court-surface-elevated text-stadium-emerald font-bold' :
                                    'text-text-muted hover:text-text-primary'"
                                class="px-3 py-1 rounded transition-colors">
                                SEMUA ({{ $allMatches->count() }})
                            </button>
                            <button @click="activeTab = 'live'"
                                :class="activeTab === 'live' ? 'bg-court-surface-elevated text-live-pulse font-bold' :
                                    'text-text-muted hover:text-text-primary'"
                                class="px-3 py-1 rounded transition-colors">
                                LIVE ({{ $liveMatches->count() }})
                            </button>
                            <button @click="activeTab = 'upcoming'"
                                :class="activeTab === 'upcoming' ? 'bg-court-surface-elevated text-telemetry-cyan font-bold' :
                                    'text-text-muted hover:text-text-primary'"
                                class="px-3 py-1 rounded transition-colors">
                                TERJADWAL ({{ $upcomingMatches->count() }})
                            </button>
                            <button @click="activeTab = 'finished'"
                                :class="activeTab === 'finished' ? 'bg-court-surface-elevated text-text-primary font-bold' :
                                    'text-text-muted hover:text-text-primary'"
                                class="px-3 py-1 rounded transition-colors">
                                SELESAI ({{ $finishedMatches->count() }})
                            </button>
                        </div>
                    </div>

                    <!-- Match List Container -->
                    <div class="p-6 space-y-4">
                        @forelse($allMatches as $match)
                            <div x-show="activeTab === 'all' || (activeTab === 'live' && {{ $match->isLive() ? 'true' : 'false' }}) || (activeTab === 'upcoming' && '{{ $match->status }}' === 'scheduled') || (activeTab === 'finished' && '{{ $match->status }}' === 'finished')"
                                class="p-4 rounded-lg bg-court-surface-elevated/60 hover:bg-court-surface-elevated border border-court-border/80 hover:border-court-border transition-all">

                                <div
                                    class="flex items-center justify-between text-[11px] font-mono text-text-muted pb-3 mb-3 border-b border-court-border/40">
                                    <span>{{ $match->match_date->format('d M Y - H:i') }} WIB &bull;
                                        {{ $match->venue }}</span>
                                    <span
                                        class="px-2 py-0.5 rounded text-[10px] font-bold border {{ $match->status_badge['color'] }}">
                                        {{ $match->status_badge['text'] }}
                                    </span>
                                </div>

                                <div class="grid grid-cols-7 items-center gap-2">
                                    <!-- Home -->
                                    <div class="col-span-3 flex items-center gap-3">
                                        <div
                                            class="w-8 h-8 rounded bg-court-navy border border-court-border flex items-center justify-center font-headline font-bold text-xs text-stadium-emerald flex-shrink-0 overflow-hidden p-0.5">
                                            @if ($match->homeTeam->logo)
                                                <img src="{{ $match->homeTeam->logo_url }}"
                                                    alt="{{ $match->homeTeam->name }}"
                                                    class="w-full h-full object-contain">
                                            @else
                                                {{ $match->homeTeam->initials }}
                                            @endif
                                        </div>
                                        <span
                                            class="font-headline font-bold text-sm text-text-primary truncate">{{ $match->homeTeam->name }}</span>
                                    </div>

                                    <!-- Score -->
                                    <div class="col-span-1 text-center font-headline font-black text-lg">
                                        @if ($match->status === 'scheduled')
                                            <span class="text-text-muted text-xs font-mono font-normal">VS</span>
                                        @else
                                            <span
                                                class="{{ $match->isLive() ? 'text-stadium-emerald' : 'text-text-primary' }}">{{ $match->home_score }}</span>
                                            <span class="text-court-border">-</span>
                                            <span
                                                class="{{ $match->isLive() ? 'text-stadium-emerald' : 'text-text-primary' }}">{{ $match->away_score }}</span>
                                        @endif
                                    </div>

                                    <!-- Away -->
                                    <div class="col-span-3 flex items-center justify-end gap-3 text-right">
                                        <span
                                            class="font-headline font-bold text-sm text-text-primary truncate">{{ $match->awayTeam->name }}</span>
                                        <div
                                            class="w-8 h-8 rounded bg-court-navy border border-court-border flex items-center justify-center font-headline font-bold text-xs text-telemetry-cyan flex-shrink-0 overflow-hidden p-0.5">
                                            @if ($match->awayTeam->logo)
                                                <img src="{{ $match->awayTeam->logo_url }}"
                                                    alt="{{ $match->awayTeam->name }}"
                                                    class="w-full h-full object-contain">
                                            @else
                                                {{ $match->awayTeam->initials }}
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Actions bar -->
                                <div
                                    class="mt-3 pt-3 border-t border-court-border/40 flex items-center justify-between text-xs font-mono">
                                    <span class="text-text-muted text-[11px]">
                                        {{ $match->stage?->name }}
                                        {{ $match->group ? '&bull; ' . $match->group->name : '' }}
                                    </span>
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('matches.show', $match->id) }}"
                                            class="text-stadium-emerald hover:underline text-xs flex items-center gap-1 font-headline font-medium">
                                            Detail Laga & Lini Masa &rarr;
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-8 text-center text-text-muted font-mono text-xs">
                                Belum ada data pertandingan untuk kategori ini.
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- KNOCKOUT BRACKET SECTION (if available) -->
                @if (count($bracketStages) > 0)
                    <div class="bg-court-surface rounded-xl border border-court-border p-6 space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-court-border">
                            <h3 class="font-headline font-bold uppercase tracking-wider text-sm flex items-center gap-2">
                                <span class="material-symbols-outlined text-telemetry-cyan text-base">account_tree</span>
                                Bagan Sistem Gugur (Knockout Tree)
                            </h3>
                            <span class="text-[10px] font-mono text-text-muted">ROAD TO CHAMPION</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @foreach ($bracketStages as $bStage)
                                <div class="p-4 rounded-lg bg-court-surface-elevated border border-court-border space-y-3">
                                    <div
                                        class="text-xs font-headline font-bold text-stadium-emerald uppercase border-b border-court-border/40 pb-2">
                                        {{ $bStage['stage']->name }}
                                    </div>
                                    @forelse($bStage['matches'] as $bm)
                                        <div class="p-2.5 rounded bg-court-navy/70 border border-court-border/60 text-xs">
                                            <div class="flex justify-between items-center py-1">
                                                <div class="flex items-center gap-2 truncate">
                                                    <div
                                                        class="w-5 h-5 rounded bg-court-surface border border-court-border flex items-center justify-center font-headline font-bold text-[9px] text-stadium-emerald flex-shrink-0 overflow-hidden">
                                                        @if ($bm->homeTeam->logo)
                                                            <img src="{{ $bm->homeTeam->logo_url }}"
                                                                alt="{{ $bm->homeTeam->name }}"
                                                                class="w-full h-full object-contain">
                                                        @else
                                                            {{ $bm->homeTeam->initials }}
                                                        @endif
                                                    </div>
                                                    <span
                                                        class="font-headline font-bold text-text-primary truncate">{{ $bm->homeTeam->name }}</span>
                                                </div>
                                                <span
                                                    class="font-mono font-black text-stadium-emerald ml-2">{{ $bm->home_score }}</span>
                                            </div>
                                            <div
                                                class="flex justify-between items-center py-1 border-t border-court-border/30">
                                                <div class="flex items-center gap-2 truncate">
                                                    <div
                                                        class="w-5 h-5 rounded bg-court-surface border border-court-border flex items-center justify-center font-headline font-bold text-[9px] text-telemetry-cyan flex-shrink-0 overflow-hidden">
                                                        @if ($bm->awayTeam->logo)
                                                            <img src="{{ $bm->awayTeam->logo_url }}"
                                                                alt="{{ $bm->awayTeam->name }}"
                                                                class="w-full h-full object-contain">
                                                        @else
                                                            {{ $bm->awayTeam->initials }}
                                                        @endif
                                                    </div>
                                                    <span
                                                        class="font-headline font-bold text-text-primary truncate">{{ $bm->awayTeam->name }}</span>
                                                </div>
                                                <span
                                                    class="font-mono font-black text-telemetry-cyan ml-2">{{ $bm->away_score }}</span>
                                            </div>
                                        </div>
                                    @empty
                                        <p class="text-xs font-mono text-text-muted">Menunggu hasil babak penyisihan grup.
                                        </p>
                                    @endforelse
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- RIGHT COLUMN: STANDINGS & LEADERBOARD STATS (5 cols) -->
            <div class="lg:col-span-5 space-y-6">

                <!-- KLASEMEN (STANDINGS TABLE) -->
                <div class="bg-court-surface rounded-xl border border-court-border overflow-hidden"
                    x-data="{ activeGroup: '{{ array_key_first($standingsByGroup) }}' }">

                    <!-- Standings Header -->
                    <div
                        class="px-6 py-4 border-b border-court-border bg-court-surface-elevated/40 flex items-center justify-between">
                        <div>
                            <h3 class="font-headline font-bold uppercase tracking-wider text-sm flex items-center gap-2">
                                <span
                                    class="material-symbols-outlined text-stadium-emerald text-base">format_list_numbered</span>
                                Klasemen Turnamen
                            </h3>
                            <p class="text-[10px] font-mono text-text-muted">OTOMATIS DIHITUNG BERDASARKAN HASIL LAGA</p>
                        </div>

                        <!-- Group Selector Buttons (Only if multiple groups exist) -->
                        @if (count($standingsByGroup) > 1)
                            <div
                                class="flex items-center gap-1 bg-court-navy p-1 rounded border border-court-border text-xs font-mono">
                                @foreach ($standingsByGroup as $gName => $gData)
                                    <button @click="activeGroup = '{{ $gName }}'"
                                        :class="activeGroup === '{{ $gName }}' ?
                                            'bg-stadium-emerald text-court-navy font-bold' :
                                            'text-text-muted hover:text-text-primary'"
                                        class="px-2.5 py-1 rounded transition-colors">
                                        {{ $gName }}
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Tables by Group / Single Group -->
                    @foreach ($standingsByGroup as $gName => $gData)
                        <div x-show="activeGroup === '{{ $gName }}'" class="overflow-x-auto">
                            @if (count($standingsByGroup) > 1)
                                <div
                                    class="px-6 py-2 bg-court-surface-elevated/20 border-b border-court-border text-xs font-headline font-bold text-telemetry-cyan uppercase">
                                    {{ $gName }}
                                </div>
                            @endif

                            <table class="w-full text-left text-xs">
                                <thead
                                    class="bg-court-navy/80 font-mono text-[11px] text-text-muted uppercase border-b border-court-border">
                                    <tr>
                                        <th class="py-2.5 px-3 text-center w-8">#</th>
                                        <th class="py-2.5 px-3">TIM</th>
                                        <th class="py-2.5 px-2 text-center" title="Main">M</th>
                                        <th class="py-2.5 px-2 text-center" title="Menang">MG</th>
                                        <th class="py-2.5 px-2 text-center" title="Seri">S</th>
                                        <th class="py-2.5 px-2 text-center" title="Kalah">K</th>
                                        <th class="py-2.5 px-2 text-center" title="Gol Memasukkan">GM</th>
                                        <th class="py-2.5 px-2 text-center" title="Gol Kemasukan">GK</th>
                                        <th class="py-2.5 px-2 text-center" title="Selisih Gol">SG</th>
                                        <th class="py-2.5 px-3 text-right font-bold text-stadium-emerald" title="Poin">
                                            PTS</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-court-border/40 font-mono">
                                    @forelse($gData['rows'] as $row)
                                        <tr
                                            class="hover:bg-court-surface-elevated/40 transition-colors {{ $row['position'] <= 2 ? 'border-l-2 border-l-stadium-emerald' : '' }}">
                                            <td
                                                class="py-3 px-3 text-center font-bold {{ $row['position'] <= 2 ? 'text-stadium-emerald' : 'text-text-muted' }}">
                                                {{ $row['position'] }}
                                            </td>
                                            <td class="py-3 px-3 font-sans font-medium text-text-primary">
                                                <div class="flex items-center gap-2">
                                                    <div
                                                        class="w-6 h-6 rounded bg-court-navy border border-court-border flex items-center justify-center font-headline font-bold text-[10px] text-stadium-emerald flex-shrink-0 overflow-hidden p-0.5">
                                                        @if ($row['team']->logo)
                                                            <img src="{{ $row['team']->logo_url }}"
                                                                alt="{{ $row['team']->name }}"
                                                                class="w-full h-full object-contain">
                                                        @else
                                                            {{ $row['team']->initials }}
                                                        @endif
                                                    </div>
                                                    <span
                                                        class="truncate max-w-[130px] sm:max-w-none">{{ $row['team']->name }}</span>
                                                </div>
                                            </td>
                                            <td class="py-3 px-2 text-center">{{ $row['played'] }}</td>
                                            <td class="py-3 px-2 text-center">{{ $row['won'] }}</td>
                                            <td class="py-3 px-2 text-center">{{ $row['draw'] }}</td>
                                            <td class="py-3 px-2 text-center">{{ $row['lost'] }}</td>
                                            <td class="py-3 px-2 text-center text-text-muted">{{ $row['goals_for'] }}</td>
                                            <td class="py-3 px-2 text-center text-text-muted">{{ $row['goals_against'] }}
                                            </td>
                                            <td
                                                class="py-3 px-2 text-center font-bold {{ $row['goal_diff'] > 0 ? 'text-stadium-emerald' : ($row['goal_diff'] < 0 ? 'text-card-red' : 'text-text-muted') }}">
                                                {{ $row['goal_diff'] > 0 ? '+' . $row['goal_diff'] : $row['goal_diff'] }}
                                            </td>
                                            <td
                                                class="py-3 px-3 text-right font-headline font-black text-sm text-stadium-emerald">
                                                {{ $row['points'] }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="py-6 text-center text-text-muted font-mono">
                                                Belum ada data klasemen untuk grup ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endforeach
                    <div
                        class="px-4 py-2.5 bg-court-navy/50 border-t border-court-border text-[10px] font-mono text-text-muted flex items-center justify-between">
                        <span class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded bg-stadium-emerald"></span>
                            Posisi 1 - 4 Lolos ke Babak Knockout
                        </span>
                        <span>Sistem: M=3, S=1, K=0</span>
                    </div>
                </div>

                <!-- INDIVIDUAL LEADERBOARDS (TOP SCORER, TOP ASSIST, CARDS) -->
                <div class="bg-court-surface rounded-xl border border-court-border overflow-hidden"
                    x-data="{ statTab: 'scorers' }">
                    <div
                        class="px-6 py-4 border-b border-court-border bg-court-surface-elevated/40 flex items-center justify-between">
                        <h3 class="font-headline font-bold uppercase tracking-wider text-sm flex items-center gap-2">
                            <span class="material-symbols-outlined text-telemetry-cyan text-base">military_tech</span>
                            Statistik Individu
                        </h3>
                        <div
                            class="flex items-center gap-1 bg-court-navy p-1 rounded border border-court-border text-xs font-mono">
                            <button @click="statTab = 'scorers'"
                                :class="statTab === 'scorers' ? 'bg-stadium-emerald text-court-navy font-bold' :
                                    'text-text-muted hover:text-text-primary'"
                                class="px-2.5 py-1 rounded transition-colors">
                                GOL
                            </button>
                            <button @click="statTab = 'assists'"
                                :class="statTab === 'assists' ? 'bg-telemetry-cyan text-court-navy font-bold' :
                                    'text-text-muted hover:text-text-primary'"
                                class="px-2.5 py-1 rounded transition-colors">
                                ASSIST
                            </button>
                            <button @click="statTab = 'cards'"
                                :class="statTab === 'cards' ? 'bg-card-yellow text-court-navy font-bold' :
                                    'text-text-muted hover:text-text-primary'"
                                class="px-2.5 py-1 rounded transition-colors">
                                KARTU
                            </button>
                        </div>
                    </div>

                    <!-- Top Scorers Tab -->
                    <div x-show="statTab === 'scorers'" class="p-4 divide-y divide-court-border/40">
                        @forelse($topScorers as $idx => $sc)
                            <div class="py-2.5 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span
                                        class="w-6 font-mono font-bold text-xs {{ $idx === 0 ? 'text-card-yellow' : 'text-text-muted' }}">
                                        #{{ $idx + 1 }}
                                    </span>
                                    <div>
                                        <span
                                            class="font-headline font-bold text-sm text-text-primary block">{{ $sc->player?->name }}</span>
                                        <span class="font-mono text-[11px] text-text-muted">{{ $sc->team?->name }} &bull;
                                            No. {{ $sc->player?->jersey_number }}</span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span
                                        class="font-headline font-black text-lg text-stadium-emerald">{{ $sc->total_goals }}</span>
                                    <span class="text-[10px] font-mono text-text-muted block">GOL</span>
                                </div>
                            </div>
                        @empty
                            <p class="py-4 text-center font-mono text-xs text-text-muted">Belum ada catatan gol.</p>
                        @endforelse
                    </div>

                    <!-- Top Assists Tab -->
                    <div x-show="statTab === 'assists'" class="p-4 divide-y divide-court-border/40">
                        @forelse($topAssists as $idx => $as)
                            <div class="py-2.5 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span
                                        class="w-6 font-mono font-bold text-xs {{ $idx === 0 ? 'text-telemetry-cyan' : 'text-text-muted' }}">
                                        #{{ $idx + 1 }}
                                    </span>
                                    <div>
                                        <span
                                            class="font-headline font-bold text-sm text-text-primary block">{{ $as->assistPlayer?->name }}</span>
                                        <span class="font-mono text-[11px] text-text-muted">{{ $as->team?->name }}</span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span
                                        class="font-headline font-black text-lg text-telemetry-cyan">{{ $as->total_assists }}</span>
                                    <span class="text-[10px] font-mono text-text-muted block">ASSIST</span>
                                </div>
                            </div>
                        @empty
                            <p class="py-4 text-center font-mono text-xs text-text-muted">Belum ada catatan assist.</p>
                        @endforelse
                    </div>

                    <!-- Cards Tab -->
                    <div x-show="statTab === 'cards'" class="p-4 divide-y divide-court-border/40">
                        @forelse($disciplinary as $idx => $card)
                            <div class="py-2.5 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span
                                        class="w-6 font-mono font-bold text-xs text-text-muted">#{{ $idx + 1 }}</span>
                                    <div>
                                        <span
                                            class="font-headline font-bold text-sm text-text-primary block">{{ $card->player?->name }}</span>
                                        <span
                                            class="font-mono text-[11px] text-text-muted">{{ $card->team?->name }}</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 font-mono text-xs">
                                    <span
                                        class="px-2 py-0.5 rounded bg-card-yellow/20 text-card-yellow border border-card-yellow/40 font-bold">
                                        🟨 {{ $card->yellow_cards }}
                                    </span>
                                    <span
                                        class="px-2 py-0.5 rounded bg-card-red/20 text-card-red border border-card-red/40 font-bold">
                                        🟥 {{ $card->red_cards }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <p class="py-4 text-center font-mono text-xs text-text-muted">Catatan kartu bersih.</p>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function fanCenterData(featuredId) {
            return {
                featuredId: featuredId,
                featured: {
                    home_score: {{ $featuredMatch ? $featuredMatch->home_score : 0 }},
                    away_score: {{ $featuredMatch ? $featuredMatch->away_score : 0 }},
                    current_minute: {{ $featuredMatch ? $featuredMatch->current_minute : 0 }},
                    timer_seconds: {{ $featuredMatch ? $featuredMatch->elapsed_seconds : 0 }},
                    timer_running: {{ $featuredMatch && $featuredMatch->timer_running ? 'true' : 'false' }},
                    time_formatted: "{{ $featuredMatch ? $featuredMatch->time_formatted : '00:00' }}",
                    is_live: {{ $featuredMatch && $featuredMatch->isLive() ? 'true' : 'false' }},
                    status_badge: {
                        text: "{{ $featuredMatch ? $featuredMatch->status_badge['text'] : '' }}",
                        color: "{{ $featuredMatch ? $featuredMatch->status_badge['color'] : '' }}"
                    }
                },
                init() {
                    if (this.featuredId) {
                        this.startPolling();
                    }
                },
                startPolling() {
                    // Periodic telemetry synchronization with server
                    setInterval(() => {
                        fetch(`/api/matches/${this.featuredId}/live`)
                            .then(res => res.json())
                            .then(data => {
                                this.featured.home_score = data.home_score;
                                this.featured.away_score = data.away_score;
                                this.featured.current_minute = data.current_minute;
                                this.featured.is_live = data.is_live;
                                this.featured.status_badge = data.status_badge;
                            })
                            .catch(() => {});
                    }, 3000);
                }
            }
        }
    </script>
@endpush
