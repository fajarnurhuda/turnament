@extends('layouts.app')

@section('title', 'Public Fan Center & Live Match Telemetry - LDII CUP TANJUNG PINANG')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6 space-y-6 sm:space-y-8" x-data="fanCenterData({{ $featuredMatch ? $featuredMatch->id : 'null' }}, {{ $activeCategory ? $activeCategory->id : 'null' }})">

        <!-- Category Selector Bar -->
        <div
            class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 pb-4 border-b border-court-border">
            <div>
                <h1
                    class="text-xl sm:text-2xl font-headline font-bold uppercase tracking-wider text-text-primary flex items-center gap-2">
                    <span
                        class="w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full bg-stadium-emerald animate-pulse flex-shrink-0"></span>
                    <span>Pusat Pertandingan & Fan Center</span>
                </h1>
                <p class="text-[10px] sm:text-xs font-mono text-text-muted mt-0.5">TELEMETRY SCOREBOARD & LIVE MATCH TRACKER
                </p>
            </div>

            <!-- Categories List & Theme Switcher (Smooth Horizontal Scroll on Mobile) -->
            <div class="flex items-center gap-2 overflow-x-auto pb-1 max-w-full scrollbar-none">
                <!-- Tombol Mode Terang / Gelap Fan Center -->
                <button type="button" onclick="toggleTheme()"
                    class="px-3.5 py-1.5 sm:py-2 rounded-lg bg-court-surface hover:bg-court-surface-elevated text-text-muted hover:text-text-primary border border-court-border flex items-center gap-1.5 text-xs font-headline font-bold uppercase tracking-wider transition-all whitespace-nowrap shadow-sm group flex-shrink-0"
                    title="Ubah Mode Tampilan (Terang / Gelap)">
                    <span class="material-symbols-outlined text-sm text-card-yellow hidden dark:inline">light_mode</span>
                    <span class="material-symbols-outlined text-sm text-telemetry-cyan inline dark:hidden">dark_mode</span>
                    <span class="hidden dark:inline">Mode Terang</span>
                    <span class="inline dark:hidden">Mode Gelap</span>
                </button>

                @foreach ($categories as $cat)
                    <a href="{{ route('home', ['category' => $cat->slug]) }}"
                        class="px-3.5 sm:px-4 py-1.5 sm:py-2 rounded-lg text-xs font-headline font-bold uppercase tracking-wider transition-all whitespace-nowrap {{ $activeCategory && $activeCategory->id === $cat->id ? 'bg-stadium-emerald text-white dark:text-court-navy shadow-[0_0_15px_rgba(0,255,135,0.3)]' : 'bg-court-surface hover:bg-court-surface-elevated text-text-muted hover:text-text-primary border border-court-border' }}">
                        {{ $cat->name }}
                    </a>
                @endforeach
            </div>
        </div>

        <!-- FEATURED MATCH TELEMETRY BANNER -->
        @if ($featuredMatch)
            <div
                class="relative rounded-xl sm:rounded-2xl overflow-hidden bg-court-surface border border-court-border shadow-2xl p-4 sm:p-6 lg:p-8">
                <!-- Background Glow Effect -->
                <div
                    class="absolute -top-24 -left-24 w-72 sm:w-96 h-72 sm:h-96 bg-stadium-emerald/10 rounded-full blur-3xl pointer-events-none">
                </div>
                <div
                    class="absolute -bottom-24 -right-24 w-72 sm:w-96 h-72 sm:h-96 bg-telemetry-cyan/10 rounded-full blur-3xl pointer-events-none">
                </div>

                <div class="relative z-10">
                    <!-- Status Top Bar -->
                    <div
                        class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 sm:gap-4 pb-4 sm:pb-6 mb-4 sm:mb-6 border-b border-court-border/60">
                        <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                            <span
                                class="px-2.5 sm:px-3 py-0.5 sm:py-1 rounded text-[10px] sm:text-xs font-mono font-bold tracking-wider border {{ $featuredMatch->status_badge['color'] }}"
                                x-text="featured.status_badge.text">
                                {{ $featuredMatch->status_badge['text'] }}
                            </span>
                            <span class="text-[11px] sm:text-xs font-mono text-text-muted truncate">
                                {{ $featuredMatch->venue }} &bull; {{ $featuredMatch->match_date->format('d M Y, H:i') }}
                                WIB
                            </span>
                        </div>

                        <div class="flex items-center gap-1.5 sm:gap-2 font-mono text-[11px] sm:text-xs">
                            <span class="text-text-muted">STAGE:</span>
                            <span class="text-telemetry-cyan uppercase truncate">
                                {{ $featuredMatch->stage?->name ?? 'Turnamen' }}
                                {{ $featuredMatch->group ? '(' . $featuredMatch->group->name . ')' : '' }}
                            </span>
                        </div>
                    </div>

                    <!-- Main Scoreboard Section (Fully Responsive 3-Col Broadcast on Mobile & 11-Col on Desktop) -->
                    <div class="grid grid-cols-3 md:grid-cols-11 items-center gap-2 sm:gap-4 md:gap-6 text-center">
                        <!-- Home Team -->
                        <div
                            class="col-span-1 md:col-span-4 flex flex-col md:flex-row items-center justify-center md:justify-end gap-2 sm:gap-4 min-w-0">
                            <div class="text-center md:text-right order-2 md:order-1 min-w-0">
                                <h2 class="text-xs sm:text-base md:text-2xl font-headline font-bold text-text-primary leading-tight line-clamp-2"
                                    title="{{ $featuredMatch->homeTeam->name }}">
                                    {{ $featuredMatch->homeTeam->name }}
                                </h2>
                                <p class="text-[9px] sm:text-xs font-mono text-text-muted mt-0.5">KANDANG (HOME)</p>
                            </div>
                            <div
                                class="w-12 h-12 sm:w-16 sm:h-16 md:w-20 md:h-20 rounded-xl bg-court-surface-elevated border border-court-border flex items-center justify-center font-headline font-black text-lg sm:text-2xl text-stadium-emerald shadow-inner order-1 md:order-2 overflow-hidden p-1 sm:p-1.5 flex-shrink-0">
                                @if ($featuredMatch->homeTeam->logo)
                                    <img src="{{ $featuredMatch->homeTeam->logo_url }}"
                                        alt="{{ $featuredMatch->homeTeam->name }}" class="w-full h-full object-contain">
                                @else
                                    {{ $featuredMatch->homeTeam->initials }}
                                @endif
                            </div>
                        </div>

                        <!-- Score Center Readout -->
                        <div
                            class="col-span-1 md:col-span-3 flex flex-col items-center justify-center py-2 sm:py-4 bg-court-navy/80 rounded-xl border border-court-border/60 px-1 sm:px-3">
                            <div
                                class="flex items-center justify-center gap-1 sm:gap-3 md:gap-4 font-headline text-3xl sm:text-4xl md:text-6xl font-black tracking-tight">
                                <span class="text-stadium-emerald"
                                    x-text="featured.home_score">{{ $featuredMatch->home_score }}</span>
                                <span class="text-court-border">:</span>
                                <span class="text-text-primary"
                                    x-text="featured.away_score">{{ $featuredMatch->away_score }}</span>
                            </div>

                            <!-- Status Babak (Atas) & Stopwatch Digital / Menit Pertandingan (Bawah) -->
                            <div class="mt-2 flex flex-col items-center gap-1 font-mono text-center">
                                <span class="px-2.5 py-0.5 rounded text-[10px] sm:text-[11px] font-bold tracking-wider border uppercase whitespace-nowrap"
                                    :class="featured.status_badge.color" x-text="featured.status_badge.text">
                                    {{ $featuredMatch->status_badge['text'] }}
                                </span>

                                <div x-show="featured.is_live && featured.status !== 'penalty_shootout'"
                                    class="flex items-center justify-center gap-1.5 bg-court-surface/90 px-2.5 py-0.5 rounded border border-court-border/80 text-[10px] sm:text-[11px] shadow-sm">
                                    <span class="w-1.5 h-1.5 rounded-full flex-shrink-0"
                                        :class="featured.timer_running ? 'bg-stadium-emerald animate-ping' : 'bg-card-yellow'"></span>
                                    <span class="font-bold text-stadium-emerald tracking-wide"
                                        x-text="featured.time_formatted">
                                        {{ $featuredMatch->time_formatted }}
                                    </span>
                                    <span class="text-text-muted font-medium">
                                        (Menit ke-<span class="text-telemetry-cyan font-bold"
                                            x-text="featured.current_minute">{{ $featuredMatch->current_minute }}</span>')
                                    </span>
                                </div>
                            </div>

                            <template
                                x-if="featured.has_penalty || {{ $featuredMatch->has_penalty ? 'true' : 'false' }} || featured.status === 'penalty_shootout'">
                                <div
                                    class="mt-1.5 px-2.5 py-0.5 rounded-full bg-card-yellow/20 text-card-yellow border border-card-yellow/40 font-mono font-bold text-[10px] sm:text-xs tracking-wider">
                                    ADU PENALTI: <span
                                        x-text="featured.home_penalty_score ?? {{ $featuredMatch->home_penalty_score ?? 0 }}">{{ $featuredMatch->home_penalty_score ?? 0 }}</span>
                                    - <span
                                        x-text="featured.away_penalty_score ?? {{ $featuredMatch->away_penalty_score ?? 0 }}">{{ $featuredMatch->away_penalty_score ?? 0 }}</span>
                                </div>
                            </template>
                        </div>

                        <!-- Away Team -->
                        <div
                            class="col-span-1 md:col-span-4 flex flex-col md:flex-row items-center justify-center md:justify-start gap-2 sm:gap-4 min-w-0">
                            <div
                                class="w-12 h-12 sm:w-16 sm:h-16 md:w-20 md:h-20 rounded-xl bg-court-surface-elevated border border-court-border flex items-center justify-center font-headline font-black text-lg sm:text-2xl text-telemetry-cyan shadow-inner overflow-hidden p-1 sm:p-1.5 flex-shrink-0">
                                @if ($featuredMatch->awayTeam->logo)
                                    <img src="{{ $featuredMatch->awayTeam->logo_url }}"
                                        alt="{{ $featuredMatch->awayTeam->name }}" class="w-full h-full object-contain">
                                @else
                                    {{ $featuredMatch->awayTeam->initials }}
                                @endif
                            </div>
                            <div class="text-center md:text-left min-w-0">
                                <h2 class="text-xs sm:text-base md:text-2xl font-headline font-bold text-text-primary leading-tight line-clamp-2"
                                    title="{{ $featuredMatch->awayTeam->name }}">
                                    {{ $featuredMatch->awayTeam->name }}
                                </h2>
                                <p class="text-[9px] sm:text-xs font-mono text-text-muted mt-0.5">TANDANG (AWAY)</p>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Quick Actions & Timeline Link -->
                    <div
                        class="mt-6 sm:mt-8 pt-4 sm:pt-6 border-t border-court-border/60 flex flex-col sm:flex-row items-center justify-between gap-3 sm:gap-4">
                        <div
                            class="text-[10px] sm:text-xs font-mono text-text-muted flex items-center gap-1.5 text-center sm:text-left">
                            <span class="material-symbols-outlined text-xs sm:text-sm text-stadium-emerald">sync</span>
                            <span>PEMBARUAN TELEMETRI OTOMATIS AKTIF (TIAP 4 DETIK)</span>
                        </div>

                        <div class="flex flex-wrap items-center justify-center gap-2 sm:gap-3 w-full sm:w-auto">
                            <a href="{{ route('matches.show', $featuredMatch->id) }}"
                                class="w-full sm:w-auto text-center px-4 sm:px-5 py-2 sm:py-2.5 rounded text-xs font-headline font-bold bg-stadium-emerald hover:bg-stadium-emerald/90 text-white dark:text-court-navy uppercase tracking-wider transition-all flex items-center justify-center gap-2 shadow-[0_0_15px_rgba(0,255,135,0.25)]">
                                <span class="material-symbols-outlined text-sm">timeline</span>
                                Buka Lini Masa & Statistik
                            </a>

                            @auth
                                <a href="{{ route('admin.matches.control', $featuredMatch->id) }}"
                                    class="w-full sm:w-auto text-center px-3.5 sm:px-4 py-2 sm:py-2.5 rounded text-xs font-headline font-bold bg-court-surface-elevated hover:bg-court-border text-telemetry-cyan uppercase tracking-wider border border-court-border transition-all flex items-center justify-center gap-2">
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
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 sm:gap-8">

            <!-- LEFT COLUMN: FIXTURES & MATCH SCHEDULE (7 cols on lg) -->
            <div class="lg:col-span-7 space-y-6">
                <div class="bg-court-surface rounded-xl border border-court-border overflow-hidden" x-data="{ activeTab: 'all' }">
                    <!-- Tabs Header (Responsive Flex-Col on Mobile) -->
                    <div
                        class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-4 sm:px-6 py-4 border-b border-court-border bg-court-surface-elevated/40">
                        <h3 class="font-headline font-bold uppercase tracking-wider text-sm flex items-center gap-2">
                            <span class="material-symbols-outlined text-stadium-emerald text-base">calendar_month</span>
                            Jadwal & Hasil Laga
                        </h3>

                        <!-- Filter Tabs (Horizontal Scroll on Mobile) -->
                        <div
                            class="flex items-center gap-1 bg-court-navy p-1 rounded border border-court-border text-xs font-mono overflow-x-auto max-w-full scrollbar-none">
                            <button @click="activeTab = 'all'"
                                :class="activeTab === 'all' ? 'bg-court-surface-elevated text-stadium-emerald font-bold' :
                                    'text-text-muted hover:text-text-primary'"
                                class="px-2.5 sm:px-3 py-1 rounded transition-colors whitespace-nowrap">
                                SEMUA ({{ $allMatches->count() }})
                            </button>
                            <button @click="activeTab = 'live'"
                                :class="activeTab === 'live' ? 'bg-court-surface-elevated text-live-pulse font-bold' :
                                    'text-text-muted hover:text-text-primary'"
                                class="px-2.5 sm:px-3 py-1 rounded transition-colors whitespace-nowrap">
                                LIVE ({{ $liveMatches->count() }})
                            </button>
                            <button @click="activeTab = 'upcoming'"
                                :class="activeTab === 'upcoming' ? 'bg-court-surface-elevated text-telemetry-cyan font-bold' :
                                    'text-text-muted hover:text-text-primary'"
                                class="px-2.5 sm:px-3 py-1 rounded transition-colors whitespace-nowrap">
                                TERJADWAL ({{ $upcomingMatches->count() }})
                            </button>
                            <button @click="activeTab = 'finished'"
                                :class="activeTab === 'finished' ? 'bg-court-surface-elevated text-text-primary font-bold' :
                                    'text-text-muted hover:text-text-primary'"
                                class="px-2.5 sm:px-3 py-1 rounded transition-colors whitespace-nowrap">
                                SELESAI ({{ $finishedMatches->count() }})
                            </button>
                        </div>
                    </div>

                    <!-- Match List Container -->
                    <div class="p-3 sm:p-6 space-y-3 sm:space-y-4">
                        @forelse($allMatches as $match)
                            <div x-show="activeTab === 'all' || (activeTab === 'live' && {{ $match->isLive() ? 'true' : 'false' }}) || (activeTab === 'upcoming' && '{{ $match->status }}' === 'scheduled') || (activeTab === 'finished' && '{{ $match->status }}' === 'finished')"
                                class="p-3 sm:p-4 rounded-lg bg-court-surface-elevated/60 hover:bg-court-surface-elevated border border-court-border/80 hover:border-court-border transition-all">

                                <div
                                    class="flex items-center justify-between text-[10px] sm:text-[11px] font-mono text-text-muted pb-2.5 mb-2.5 border-b border-court-border/40 gap-2">
                                    <span class="truncate">{{ $match->match_date->format('d M Y - H:i') }} WIB &bull;
                                        {{ $match->venue }}</span>
                                    <span
                                        class="px-2 py-0.5 rounded text-[10px] font-bold border flex-shrink-0 {{ $match->status_badge['color'] }}">
                                        {{ $match->status_badge['text'] }}
                                    </span>
                                </div>

                                <div class="grid grid-cols-7 items-center gap-1.5 sm:gap-2">
                                    <!-- Home -->
                                    <div class="col-span-3 flex items-center gap-2 sm:gap-3 min-w-0">
                                        <div
                                            class="w-7 h-7 sm:w-8 sm:h-8 rounded bg-court-navy border border-court-border flex items-center justify-center font-headline font-bold text-[10px] sm:text-xs text-stadium-emerald flex-shrink-0 overflow-hidden p-0.5">
                                            @if ($match->homeTeam->logo)
                                                <img src="{{ $match->homeTeam->logo_url }}"
                                                    alt="{{ $match->homeTeam->name }}"
                                                    class="w-full h-full object-contain">
                                            @else
                                                {{ $match->homeTeam->initials }}
                                            @endif
                                        </div>
                                        <span class="font-headline font-bold text-xs sm:text-sm text-text-primary truncate"
                                            title="{{ $match->homeTeam->name }}">{{ $match->homeTeam->name }}</span>
                                    </div>

                                    <!-- Score -->
                                    <div class="col-span-1 text-center font-headline font-black text-sm sm:text-lg">
                                        @if ($match->status === 'scheduled')
                                            <span
                                                class="text-text-muted text-[10px] sm:text-xs font-mono font-normal">VS</span>
                                        @else
                                            <span
                                                class="{{ $match->isLive() ? 'text-stadium-emerald' : 'text-text-primary' }}">{{ $match->home_score }}</span>
                                            <span class="text-court-border text-xs">-</span>
                                            <span
                                                class="{{ $match->isLive() ? 'text-stadium-emerald' : 'text-text-primary' }}">{{ $match->away_score }}</span>
                                            @if ($match->has_penalty)
                                                <div class="mt-0.5">
                                                    <span
                                                        class="px-1.5 py-0.5 rounded bg-card-yellow/20 text-card-yellow font-mono text-[9px] font-bold border border-card-yellow/40">
                                                        {{ $match->penalty_score_formatted }}
                                                    </span>
                                                </div>
                                            @endif
                                        @endif
                                    </div>

                                    <!-- Away -->
                                    <div
                                        class="col-span-3 flex items-center justify-end gap-2 sm:gap-3 text-right min-w-0">
                                        <span class="font-headline font-bold text-xs sm:text-sm text-text-primary truncate"
                                            title="{{ $match->awayTeam->name }}">{{ $match->awayTeam->name }}</span>
                                        <div
                                            class="w-7 h-7 sm:w-8 sm:h-8 rounded bg-court-navy border border-court-border flex items-center justify-center font-headline font-bold text-[10px] sm:text-xs text-telemetry-cyan flex-shrink-0 overflow-hidden p-0.5">
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
                                    class="mt-2.5 sm:mt-3 pt-2.5 sm:pt-3 border-t border-court-border/40 flex flex-col sm:flex-row sm:items-center justify-between text-xs font-mono gap-1.5 sm:gap-2">
                                    <span class="text-text-muted text-[10px] sm:text-[11px] truncate">
                                        {{ $match->stage?->name }}
                                        {{ $match->group ? '&bull; ' . $match->group->name : '' }}
                                    </span>
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('matches.show', $match->id) }}"
                                            class="text-stadium-emerald hover:underline text-[11px] sm:text-xs flex items-center gap-1 font-headline font-medium">
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
                    <div class="bg-court-surface rounded-xl border border-court-border p-4 sm:p-6 space-y-4">
                        <div
                            class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-court-border gap-2">
                            <h3 class="font-headline font-bold uppercase tracking-wider text-sm flex items-center gap-2">
                                <span class="material-symbols-outlined text-telemetry-cyan text-base">account_tree</span>
                                Bagan Sistem Gugur (Knockout Tree)
                            </h3>
                            <span class="text-[10px] font-mono text-text-muted">ROAD TO CHAMPION</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                            @foreach ($bracketStages as $bStage)
                                <div
                                    class="p-3.5 sm:p-4 rounded-lg bg-court-surface-elevated border border-court-border space-y-3">
                                    <div
                                        class="text-xs font-headline font-bold text-stadium-emerald uppercase border-b border-court-border/40 pb-2">
                                        {{ $bStage['stage']->name }}
                                    </div>
                                    @forelse($bStage['matches'] as $bm)
                                        <div class="p-2.5 rounded bg-court-navy/70 border border-court-border/60 text-xs">
                                            <div class="flex justify-between items-center py-1">
                                                <div class="flex items-center gap-2 truncate min-w-0">
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
                                                <span class="font-mono font-black text-stadium-emerald ml-2 flex-shrink-0">
                                                    {{ $bm->home_score }}
                                                    @if ($bm->has_penalty)
                                                        <span
                                                            class="text-[10px] text-card-yellow font-bold">({{ $bm->home_penalty_score }})</span>
                                                    @endif
                                                </span>
                                            </div>
                                            <div
                                                class="flex justify-between items-center py-1 border-t border-court-border/30">
                                                <div class="flex items-center gap-2 truncate min-w-0">
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
                                                <span class="font-mono font-black text-telemetry-cyan ml-2 flex-shrink-0">
                                                    {{ $bm->away_score }}
                                                    @if ($bm->has_penalty)
                                                        <span
                                                            class="text-[10px] text-card-yellow font-bold">({{ $bm->away_penalty_score }})</span>
                                                    @endif
                                                </span>
                                            </div>
                                            @if ($bm->has_penalty)
                                                <div
                                                    class="pt-1 mt-1 border-t border-court-border/30 flex items-center justify-between text-[10px] font-mono text-card-yellow">
                                                    <span>Menang Adu Penalti</span>
                                                    <span class="font-bold">{{ $bm->penalty_score_formatted }}</span>
                                                </div>
                                            @endif
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

            <!-- RIGHT COLUMN: STANDINGS & LEADERBOARD STATS (5 cols on lg) -->
            <div class="lg:col-span-5 space-y-6">

                <!-- KLASEMEN (STANDINGS TABLE) -->
                <div class="bg-court-surface rounded-xl border border-court-border overflow-hidden"
                    x-data="{ activeGroup: '{{ array_key_first($standingsByGroup) }}' }">

                    <!-- Standings Header -->
                    <div
                        class="px-4 sm:px-6 py-4 border-b border-court-border bg-court-surface-elevated/40 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <h3 class="font-headline font-bold uppercase tracking-wider text-sm flex items-center gap-2">
                                <span
                                    class="material-symbols-outlined text-stadium-emerald text-base">format_list_numbered</span>
                                Klasemen Turnamen
                            </h3>
                            <p class="text-[10px] font-mono text-text-muted">OTOMATIS DIHITUNG BERDASARKAN HASIL LAGA</p>
                        </div>

                        <!-- Group Selector Buttons -->
                        @if (count($standingsByGroup) > 1)
                            <div
                                class="flex items-center gap-1 bg-court-navy p-1 rounded border border-court-border text-xs font-mono overflow-x-auto max-w-full scrollbar-none">
                                @foreach ($standingsByGroup as $gName => $gData)
                                    <button @click="activeGroup = '{{ $gName }}'"
                                        :class="activeGroup === '{{ $gName }}' ?
                                            'bg-stadium-emerald text-white dark:text-court-navy font-bold' :
                                            'text-text-muted hover:text-text-primary'"
                                        class="px-2.5 py-1 rounded transition-colors whitespace-nowrap">
                                        {{ $gName }}
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Mobile Horizontal Scroll Hint -->
                    <div
                        class="sm:hidden px-4 py-1.5 bg-court-navy/50 text-[10px] font-mono text-text-muted flex items-center justify-between border-b border-court-border/30">
                        <span>&larr; Geser tabel untuk statistik &rarr;</span>
                        <span class="material-symbols-outlined text-xs">swipe</span>
                    </div>

                    <!-- Tables by Group / Single Group (Scrollable Table Container) -->
                    @foreach ($standingsByGroup as $gName => $gData)
                        <div x-show="activeGroup === '{{ $gName }}'" class="overflow-x-auto scrollbar-none">
                            @if (count($standingsByGroup) > 1)
                                <div
                                    class="px-4 sm:px-6 py-2 bg-court-surface-elevated/20 border-b border-court-border text-xs font-headline font-bold text-telemetry-cyan uppercase">
                                    {{ $gName }}
                                </div>
                            @endif

                            <table class="w-full text-left text-xs min-w-[480px] sm:min-w-full">
                                <thead
                                    class="bg-court-navy/80 font-mono text-[11px] text-text-muted uppercase border-b border-court-border">
                                    <tr>
                                        <th class="py-2.5 px-3 text-center w-8">#</th>
                                        <th class="py-2.5 px-3 min-w-[130px]">TIM</th>
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
                                    <template x-for="row in getRows('{{ $gName }}')" :key="row.team_id || (row.team ? row.team.id : Math.random())">
                                        <tr class="hover:bg-court-surface-elevated/40 transition-colors"
                                            :class="row.position <= 2 ? 'border-l-2 border-l-stadium-emerald' : ''">
                                            <td class="py-3 px-3 text-center font-bold"
                                                :class="row.position <= 2 ? 'text-stadium-emerald' : 'text-text-muted'"
                                                x-text="row.position">
                                            </td>
                                            <td class="py-3 px-3 font-sans font-medium text-text-primary">
                                                <div class="flex items-center gap-2">
                                                    <div class="w-6 h-6 rounded bg-court-navy border border-court-border flex items-center justify-center font-headline font-bold text-[10px] text-stadium-emerald flex-shrink-0 overflow-hidden p-0.5">
                                                        <template x-if="row.team_logo || (row.team && row.team.logo_url)">
                                                            <img :src="row.team_logo || row.team.logo_url" :alt="row.team_name || (row.team ? row.team.name : '')" class="w-full h-full object-contain">
                                                        </template>
                                                        <template x-if="!(row.team_logo || (row.team && row.team.logo_url))">
                                                            <span x-text="row.team_initials || (row.team ? row.team.initials : '')"></span>
                                                        </template>
                                                    </div>
                                                    <span class="truncate max-w-[120px] sm:max-w-none" x-text="row.team_name || (row.team ? row.team.name : '')"></span>
                                                </div>
                                            </td>
                                            <td class="py-3 px-2 text-center" x-text="row.played"></td>
                                            <td class="py-3 px-2 text-center" x-text="row.won"></td>
                                            <td class="py-3 px-2 text-center" x-text="row.draw"></td>
                                            <td class="py-3 px-2 text-center" x-text="row.lost"></td>
                                            <td class="py-3 px-2 text-center text-text-muted" x-text="row.goals_for"></td>
                                            <td class="py-3 px-2 text-center text-text-muted" x-text="row.goals_against"></td>
                                            <td class="py-3 px-2 text-center font-bold"
                                                :class="row.goal_diff > 0 ? 'text-stadium-emerald' : (row.goal_diff < 0 ? 'text-card-red' : 'text-text-muted')"
                                                x-text="row.goal_diff > 0 ? '+' + row.goal_diff : row.goal_diff">
                                            </td>
                                            <td class="py-3 px-3 text-right font-headline font-black text-sm text-stadium-emerald" x-text="row.points"></td>
                                        </tr>
                                    </template>
                                    <template x-if="getRows('{{ $gName }}').length === 0">
                                        <tr>
                                            <td colspan="10" class="py-6 text-center text-text-muted font-mono">
                                                Belum ada data klasemen untuk grup ini.
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    @endforeach

                    <!-- Standings Legend / Info Footer -->
                    <div
                        class="px-4 py-3 bg-court-navy/60 border-t border-court-border space-y-2 text-[10px] font-mono text-text-muted">
                        <!-- Qualification & Point Rules -->
                        <div
                            class="flex flex-col sm:flex-row sm:items-center justify-between gap-1.5 pb-2 border-b border-court-border/40">
                            <span class="text-text-muted font-mono">
                                Sistem Poin: <strong class="text-text-primary">Menang (MG) = 3</strong> &bull; <strong
                                    class="text-text-primary">Seri (S) = 1</strong> &bull; <strong
                                    class="text-text-primary">Kalah (K) = 0</strong>
                            </span>
                        </div>

                        <!-- Column Abbreviations Legend -->
                        <div class="pt-0.5">
                            <div
                                class="flex items-center gap-1 text-[10px] text-telemetry-cyan font-bold uppercase tracking-wider mb-1.5">
                                <span class="material-symbols-outlined text-xs">info</span>
                                <span>Keterangan Singkatan Kolom Klasemen:</span>
                            </div>
                            <div class="flex flex-wrap items-center gap-x-2.5 gap-y-1 text-[10px] leading-relaxed">
                                <span class="px-1.5 py-0.5 rounded bg-court-surface border border-court-border"><strong
                                        class="text-text-primary">M</strong> = Main (Pertandingan)</span>
                                <span class="px-1.5 py-0.5 rounded bg-court-surface border border-court-border"><strong
                                        class="text-stadium-emerald">MG</strong> = Menang</span>
                                <span class="px-1.5 py-0.5 rounded bg-court-surface border border-court-border"><strong
                                        class="text-card-yellow">S</strong> = Seri / Imbang</span>
                                <span class="px-1.5 py-0.5 rounded bg-court-surface border border-court-border"><strong
                                        class="text-card-red">K</strong> = Kalah</span>
                                <span class="px-1.5 py-0.5 rounded bg-court-surface border border-court-border"><strong
                                        class="text-text-primary">GM</strong> = Gol Memasukkan (Cetak Gol)</span>
                                <span class="px-1.5 py-0.5 rounded bg-court-surface border border-court-border"><strong
                                        class="text-text-primary">GK</strong> = Gol Kemasukan (Kebobolan)</span>
                                <span class="px-1.5 py-0.5 rounded bg-court-surface border border-court-border"><strong
                                        class="text-text-primary">SG</strong> = Selisih Gol (GM - GK)</span>
                                <span class="px-1.5 py-0.5 rounded bg-court-surface border border-court-border"><strong
                                        class="text-stadium-emerald">PTS</strong> = Total Poin</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- INDIVIDUAL LEADERBOARDS (TOP SCORER, TOP ASSIST, CARDS) -->
                <div class="bg-court-surface rounded-xl border border-court-border overflow-hidden">
                    <div
                        class="px-4 sm:px-6 py-4 border-b border-court-border bg-court-surface-elevated/40 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <h3 class="font-headline font-bold uppercase tracking-wider text-sm flex items-center gap-2">
                            <span class="material-symbols-outlined text-telemetry-cyan text-base">military_tech</span>
                            Statistik Individu
                        </h3>
                        <div
                            class="flex items-center gap-1 bg-court-navy p-1 rounded border border-court-border text-xs font-mono w-full sm:w-auto justify-center">
                            <button @click="statTab = 'scorers'"
                                :class="statTab === 'scorers' ? 'bg-stadium-emerald text-white dark:text-court-navy font-bold' :
                                    'text-text-muted hover:text-text-primary'"
                                class="flex-1 sm:flex-initial px-2.5 py-1 rounded transition-colors text-center flex items-center justify-center gap-1">
                                <span>GOL</span>
                                <template x-if="leaderboards.top_scorers && leaderboards.top_scorers.length > 0">
                                    <span class="px-1.5 py-0.2 rounded-full text-[9px] bg-white/20 dark:bg-court-navy/20 font-bold" x-text="leaderboards.top_scorers.length"></span>
                                </template>
                            </button>
                            <button @click="statTab = 'assists'"
                                :class="statTab === 'assists' ? 'bg-telemetry-cyan text-white dark:text-court-navy font-bold' :
                                    'text-text-muted hover:text-text-primary'"
                                class="flex-1 sm:flex-initial px-2.5 py-1 rounded transition-colors text-center flex items-center justify-center gap-1">
                                <span>ASSIST</span>
                                <template x-if="leaderboards.top_assists && leaderboards.top_assists.length > 0">
                                    <span class="px-1.5 py-0.2 rounded-full text-[9px] bg-white/20 dark:bg-court-navy/20 font-bold" x-text="leaderboards.top_assists.length"></span>
                                </template>
                            </button>
                            <button @click="statTab = 'cards'"
                                :class="statTab === 'cards' ? 'bg-card-yellow text-white dark:text-court-navy font-bold' :
                                    'text-text-muted hover:text-text-primary'"
                                class="flex-1 sm:flex-initial px-2.5 py-1 rounded transition-colors text-center flex items-center justify-center gap-1">
                                <span>KARTU</span>
                                <template x-if="leaderboards.disciplinary && leaderboards.disciplinary.length > 0">
                                    <span class="px-1.5 py-0.2 rounded-full text-[9px] bg-white/20 dark:bg-court-navy/20 font-bold" x-text="leaderboards.disciplinary.length"></span>
                                </template>
                            </button>
                        </div>
                    </div>

                    <!-- Top Scorers Tab -->
                    <div x-show="statTab === 'scorers'" class="p-3 sm:p-4 divide-y divide-court-border/40 max-h-96 overflow-y-auto">
                        <template x-for="(sc, idx) in leaderboards.top_scorers" :key="idx">
                            <div class="py-2.5 flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                                    <span
                                        class="w-5 sm:w-6 font-mono font-bold text-xs flex-shrink-0"
                                        :class="idx === 0 ? 'text-card-yellow' : 'text-text-muted'"
                                        x-text="'#' + (idx + 1)">
                                    </span>
                                    <div class="min-w-0">
                                        <span
                                            class="font-headline font-bold text-xs sm:text-sm text-text-primary block truncate"
                                            x-text="sc.player_name"></span>
                                        <span
                                            class="font-mono text-[10px] sm:text-[11px] text-text-muted truncate block">
                                            <span x-text="sc.team_name"></span>
                                            <template x-if="sc.jersey_number">
                                                <span> &bull; No. <span x-text="sc.jersey_number"></span></span>
                                            </template>
                                        </span>
                                    </div>
                                </div>
                                <div class="text-right flex-shrink-0 pl-2">
                                    <span
                                        class="font-headline font-black text-base sm:text-lg text-stadium-emerald"
                                        x-text="sc.total_goals"></span>
                                    <span class="text-[9px] sm:text-[10px] font-mono text-text-muted block">GOL</span>
                                </div>
                            </div>
                        </template>
                        <div x-show="!leaderboards.top_scorers || leaderboards.top_scorers.length === 0"
                            class="py-4 text-center font-mono text-xs text-text-muted">
                            Belum ada catatan gol.
                        </div>
                    </div>

                    <!-- Top Assists Tab -->
                    <div x-show="statTab === 'assists'" class="p-3 sm:p-4 divide-y divide-court-border/40 max-h-96 overflow-y-auto">
                        <template x-for="(as, idx) in leaderboards.top_assists" :key="idx">
                            <div class="py-2.5 flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                                    <span
                                        class="w-5 sm:w-6 font-mono font-bold text-xs flex-shrink-0"
                                        :class="idx === 0 ? 'text-telemetry-cyan' : 'text-text-muted'"
                                        x-text="'#' + (idx + 1)">
                                    </span>
                                    <div class="min-w-0">
                                        <span
                                            class="font-headline font-bold text-xs sm:text-sm text-text-primary block truncate"
                                            x-text="as.player_name"></span>
                                        <span
                                            class="font-mono text-[10px] sm:text-[11px] text-text-muted truncate block"
                                            x-text="as.team_name"></span>
                                    </div>
                                </div>
                                <div class="text-right flex-shrink-0 pl-2">
                                    <span
                                        class="font-headline font-black text-base sm:text-lg text-telemetry-cyan"
                                        x-text="as.total_assists"></span>
                                    <span class="text-[9px] sm:text-[10px] font-mono text-text-muted block">ASSIST</span>
                                </div>
                            </div>
                        </template>
                        <div x-show="!leaderboards.top_assists || leaderboards.top_assists.length === 0"
                            class="py-4 text-center font-mono text-xs text-text-muted">
                            Belum ada catatan assist.
                        </div>
                    </div>

                    <!-- Cards Tab -->
                    <div x-show="statTab === 'cards'" class="p-3 sm:p-4 divide-y divide-court-border/40 max-h-96 overflow-y-auto">
                        <template x-for="(card, idx) in leaderboards.disciplinary" :key="idx">
                            <div class="py-2.5 flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                                    <span
                                        class="w-5 sm:w-6 font-mono font-bold text-xs flex-shrink-0 text-text-muted"
                                        x-text="'#' + (idx + 1)"></span>
                                    <div class="min-w-0">
                                        <span
                                            class="font-headline font-bold text-xs sm:text-sm text-text-primary block truncate"
                                            x-text="card.player_name"></span>
                                        <span
                                            class="font-mono text-[10px] sm:text-[11px] text-text-muted truncate block"
                                            x-text="card.team_name"></span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1.5 sm:gap-3 font-mono text-xs flex-shrink-0">
                                    <span
                                        class="px-1.5 sm:px-2 py-0.5 rounded bg-card-yellow/20 text-card-yellow border border-card-yellow/40 font-bold text-[10px] sm:text-xs">
                                        🟨 <span x-text="card.yellow_cards"></span>
                                    </span>
                                    <span
                                        class="px-1.5 sm:px-2 py-0.5 rounded bg-card-red/20 text-card-red border border-card-red/40 font-bold text-[10px] sm:text-xs">
                                        🟥 <span x-text="card.red_cards"></span>
                                    </span>
                                </div>
                            </div>
                        </template>
                        <div x-show="!leaderboards.disciplinary || leaderboards.disciplinary.length === 0"
                            class="py-4 text-center font-mono text-xs text-text-muted">
                            Catatan kartu bersih.
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@php
    $initialLeaderboards = [
        'top_scorers' => $topScorers->map(fn ($sc, $idx) => [
            'rank' => $idx + 1,
            'player_name' => $sc->player?->name ?? 'Pemain',
            'jersey_number' => $sc->player?->jersey_number,
            'team_name' => $sc->team?->name ?? 'Tim',
            'total_goals' => (int) $sc->total_goals,
        ])->values(),
        'top_assists' => $topAssists->map(fn ($as, $idx) => [
            'rank' => $idx + 1,
            'player_name' => $as->assistPlayer?->name ?? 'Pemain',
            'team_name' => $as->team?->name ?? 'Tim',
            'total_assists' => (int) $as->total_assists,
        ])->values(),
        'disciplinary' => $disciplinary->map(fn ($cd, $idx) => [
            'rank' => $idx + 1,
            'player_name' => $cd->player?->name ?? 'Pemain',
            'team_name' => $cd->team?->name ?? 'Tim',
            'yellow_cards' => (int) $cd->yellow_cards,
            'red_cards' => (int) $cd->red_cards,
        ])->values(),
    ];
@endphp

@push('scripts')
    <script>
        function fanCenterData(featuredId, categoryId) {
            return {
                featuredId: featuredId,
                categoryId: categoryId,
                standings: @json($standingsByGroup),
                leaderboards: @json($initialLeaderboards),
                statTab: 'scorers',
                getRows(gName) {
                    if (this.standings && this.standings[gName] && this.standings[gName].rows) {
                        return this.standings[gName].rows;
                    }
                    return [];
                },
                refreshStandings() {
                    if (!this.categoryId) return;
                    fetch(`/api/categories/${this.categoryId}/standings`)
                        .then(res => res.json())
                        .then(data => {
                            if (data && Object.keys(data).length > 0) {
                                this.standings = data;
                            }
                        })
                        .catch(() => {});
                },
                refreshLeaderboards() {
                    if (!this.categoryId) return;
                    fetch(`/api/categories/${this.categoryId}/leaderboards`)
                        .then(res => res.json())
                        .then(data => {
                            if (data) {
                                this.leaderboards = data;
                            }
                        })
                        .catch(() => {});
                },
                featured: {
                    home_score: {{ $featuredMatch ? $featuredMatch->home_score : 0 }},
                    away_score: {{ $featuredMatch ? $featuredMatch->away_score : 0 }},
                    home_penalty_score: {{ $featuredMatch && $featuredMatch->home_penalty_score !== null ? $featuredMatch->home_penalty_score : 'null' }},
                    away_penalty_score: {{ $featuredMatch && $featuredMatch->away_penalty_score !== null ? $featuredMatch->away_penalty_score : 'null' }},
                    has_penalty: {{ $featuredMatch && $featuredMatch->has_penalty ? 'true' : 'false' }},
                    current_minute: {{ $featuredMatch ? $featuredMatch->current_minute : 0 }},
                    timer_seconds: {{ $featuredMatch ? $featuredMatch->elapsed_seconds : 0 }},
                    timer_running: {{ $featuredMatch && $featuredMatch->timer_running ? 'true' : 'false' }},
                    time_formatted: "{{ $featuredMatch ? $featuredMatch->time_formatted : '00:00' }}",
                    is_live: {{ $featuredMatch && $featuredMatch->isLive() ? 'true' : 'false' }},
                    status: "{{ $featuredMatch ? $featuredMatch->status : '' }}",
                    status_badge: {
                        text: "{{ $featuredMatch ? $featuredMatch->status_badge['text'] : '' }}",
                        color: "{{ $featuredMatch ? $featuredMatch->status_badge['color'] : '' }}"
                    }
                },
                init() {
                    // 1-second local clock increment for smooth live stopwatch animation
                    setInterval(() => {
                        if (this.featured.is_live && this.featured.timer_running && this.featured.status !==
                            'penalty_shootout') {
                            this.featured.timer_seconds++;
                            const mins = Math.floor(this.featured.timer_seconds / 60);
                            const secs = this.featured.timer_seconds % 60;
                            this.featured.time_formatted = String(mins).padStart(2, '0') + ':' + String(secs)
                                .padStart(2, '0');
                            this.featured.current_minute = Math.max(1, Math.ceil(this.featured.timer_seconds / 60));
                        }
                    }, 1000);

                    this.startPolling();
                },
                startPolling() {
                    let pollCount = 0;
                    setInterval(() => {
                        if (this.featuredId) {
                            fetch(`/api/matches/${this.featuredId}/live`)
                                .then(res => res.json())
                                .then(data => {
                                    const statusChanged = this.featured.status !== data.status;
                                    const scoreChanged = this.featured.home_score !== data.home_score || this.featured.away_score !== data.away_score;

                                    this.featured.home_score = data.home_score;
                                    this.featured.away_score = data.away_score;
                                    this.featured.home_penalty_score = data.home_penalty_score;
                                    this.featured.away_penalty_score = data.away_penalty_score;
                                    this.featured.has_penalty = data.has_penalty;
                                    this.featured.status = data.status;
                                    this.featured.current_minute = data.current_minute;
                                    this.featured.is_live = data.is_live;
                                    this.featured.status_badge = data.status_badge;
                                    this.featured.timer_seconds = data.timer_seconds;
                                    this.featured.timer_running = data.timer_running;
                                    this.featured.time_formatted = data.time_formatted;

                                    // Instantly update standings & leaderboards whenever a goal is scored or status reaches fulltime
                                    if (statusChanged || scoreChanged) {
                                        this.refreshStandings();
                                        this.refreshLeaderboards();
                                    }
                                })
                                .catch(() => {});
                        }

                        // Periodic background standings & leaderboards sync
                        pollCount++;
                        if (pollCount % 2 === 0) {
                            this.refreshStandings();
                            this.refreshLeaderboards();
                        }
                    }, 3000);
                }
            }
        }
    </script>
@endpush
