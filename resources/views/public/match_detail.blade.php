@extends('layouts.app')

@section('title', $match->homeTeam->name . ' vs ' . $match->awayTeam->name . ' - Live Timeline & Roster')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8" x-data="matchDetail({{ $match->id }}, {{ $match->isLive() ? 'true' : 'false' }})">

    <!-- Top Navigation Breadcrumbs -->
    <div class="flex items-center justify-between">
        <a href="{{ route('home') }}" class="text-xs font-mono text-text-muted hover:text-stadium-emerald flex items-center gap-1.5 transition-colors">
            <span class="material-symbols-outlined text-sm">arrow_back</span>
            KEMBALI KE FAN CENTER
        </a>

        @auth
            <a href="{{ route('admin.matches.control', $match->id) }}" class="px-3 py-1.5 rounded text-xs font-headline font-bold bg-court-surface hover:bg-court-surface-elevated text-telemetry-cyan border border-court-border flex items-center gap-1.5 transition-colors">
                <span class="material-symbols-outlined text-sm">tune</span>
                KONTROL WASIT MEJA
            </a>
        @endauth
    </div>

    <!-- MAIN SCOREBOARD HERO -->
    <div class="rounded-xl overflow-hidden bg-court-surface border border-court-border shadow-2xl p-6 sm:p-8">
        <!-- Stage info & status badge -->
        <div class="flex items-center justify-between pb-6 mb-6 border-b border-court-border">
            <div class="flex items-center gap-3">
                <span class="px-3 py-1 rounded text-xs font-mono font-bold tracking-wider border {{ $match->status_badge['color'] }}" x-text="matchData.status_badge.text">
                    {{ $match->status_badge['text'] }}
                </span>
                <span class="text-xs font-mono text-text-muted">
                    {{ $match->category->name }} &bull; {{ $match->stage?->name }} {{ $match->group ? '('.$match->group->name.')' : '' }}
                </span>
            </div>

            <div class="text-right text-xs font-mono text-text-muted">
                {{ $match->venue }} &bull; {{ $match->match_date->format('d M Y, H:i') }} WIB
            </div>
        </div>

        <!-- Symmetrical Score Readout -->
        <div class="grid grid-cols-1 md:grid-cols-11 items-center gap-6 text-center">
            <!-- Home Team -->
            <div class="md:col-span-4 flex flex-col md:flex-row items-center justify-end gap-4">
                <div class="text-center md:text-right order-2 md:order-1">
                    <h1 class="text-2xl sm:text-3xl font-headline font-black text-text-primary">{{ $match->homeTeam->name }}</h1>
                    <p class="text-xs font-mono text-text-muted">MANAJER: {{ $match->homeTeam->manager_name ?? '-' }}</p>
                </div>
                <div class="w-20 h-20 rounded-xl bg-court-surface-elevated border border-court-border flex items-center justify-center font-headline font-black text-2xl text-stadium-emerald shadow-inner order-1 md:order-2 flex-shrink-0">
                    {{ $match->homeTeam->initials }}
                </div>
            </div>

            <!-- Score Center with Live Status & Minute (Sesuai Referensi) -->
            <div class="md:col-span-3 flex flex-col items-center justify-center py-4 bg-court-navy/80 rounded-xl border border-court-border/60">
                <div class="flex items-center gap-4 font-headline text-6xl sm:text-7xl font-black tracking-tight">
                    <span class="text-stadium-emerald" x-text="matchData.home_score">{{ $match->home_score }}</span>
                    <span class="text-court-border">:</span>
                    <span class="text-text-primary" x-text="matchData.away_score">{{ $match->away_score }}</span>
                </div>
                
                <!-- Status Babak & Menit Pertandingan -->
                <div class="mt-3 flex items-center gap-2 font-mono text-xs">
                    <span class="px-2.5 py-0.5 rounded font-bold border"
                          :class="matchData.status_badge.color"
                          x-text="matchData.status_badge.text">
                        {{ $match->status_badge['text'] }}
                    </span>
                    <span x-show="matchData.is_live" class="text-telemetry-cyan font-bold tracking-wider">
                        MENIT <span x-text="matchData.current_minute">{{ $match->current_minute }}</span>'
                    </span>
                </div>
            </div>

            <!-- Away Team -->
            <div class="md:col-span-4 flex flex-col md:flex-row items-center justify-start gap-4">
                <div class="w-20 h-20 rounded-xl bg-court-surface-elevated border border-court-border flex items-center justify-center font-headline font-black text-2xl text-telemetry-cyan shadow-inner flex-shrink-0">
                    {{ $match->awayTeam->initials }}
                </div>
                <div class="text-center md:text-left">
                    <h1 class="text-2xl sm:text-3xl font-headline font-black text-text-primary">{{ $match->awayTeam->name }}</h1>
                    <p class="text-xs font-mono text-text-muted">MANAJER: {{ $match->awayTeam->manager_name ?? '-' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- TWO COLUMNS: LIVE TIMELINE (Left 7 cols) & TEAM ROSTERS (Right 5 cols) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

        <!-- LEFT COLUMN: VERTICAL MATCH TIMELINE STREAM -->
        <div class="lg:col-span-7 bg-court-surface rounded-xl border border-court-border p-6 space-y-6">
            <div class="flex items-center justify-between pb-4 border-b border-court-border">
                <div>
                    <h3 class="font-headline font-bold uppercase tracking-wider text-sm flex items-center gap-2 text-text-primary">
                        <span class="material-symbols-outlined text-stadium-emerald text-base">stream</span>
                        Lini Masa Pertandingan (Match Timeline)
                    </h3>
                    <p class="text-[10px] font-mono text-text-muted">KRONOLOGI GOL, KARTU & INSIDEN LAPANGAN</p>
                </div>

                <span class="text-xs font-mono text-text-muted flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-stadium-emerald animate-pulse" x-show="matchData.is_live"></span>
                    <span x-show="matchData.is_live">REAL-TIME ACTIVE</span>
                </span>
            </div>

            <!-- Vertical Timeline Axis -->
            <div class="relative pl-6 sm:pl-8 border-l-2 border-court-border space-y-6 my-4">
                @forelse($match->events as $event)
                    <div class="relative group">
                        <!-- Node Pin on Axis -->
                        <div class="absolute -left-[31px] sm:-left-[39px] top-0 w-7 h-7 sm:w-8 sm:h-8 rounded-full flex items-center justify-center text-xs font-black shadow-lg
                            {{ $event->event_type === 'goal' ? 'bg-stadium-emerald/20 text-stadium-emerald border-2 border-stadium-emerald' : '' }}
                            {{ $event->event_type === 'own_goal' ? 'bg-card-red/20 text-card-red border-2 border-card-red' : '' }}
                            {{ $event->event_type === 'yellow_card' ? 'bg-card-yellow/20 text-card-yellow border-2 border-card-yellow' : '' }}
                            {{ $event->event_type === 'red_card' || $event->event_type === 'second_yellow' ? 'bg-card-red/20 text-card-red border-2 border-card-red' : '' }}
                            {{ $event->event_type === 'assist' ? 'bg-telemetry-cyan/20 text-telemetry-cyan border-2 border-telemetry-cyan' : '' }}">
                            
                            @if($event->event_type === 'goal')
                                ⚽
                            @elseif($event->event_type === 'own_goal')
                                🔄
                            @elseif($event->event_type === 'yellow_card')
                                🟨
                            @elseif($event->event_type === 'red_card' || $event->event_type === 'second_yellow')
                                🟥
                            @else
                                👟
                            @endif
                        </div>

                        <!-- Event Content Box -->
                        <div class="p-4 rounded-lg bg-court-surface-elevated border border-court-border group-hover:border-court-border/80 transition-all">
                            <div class="flex items-center justify-between pb-2 mb-2 border-b border-court-border/40">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded font-mono font-bold text-xs bg-court-navy text-stadium-emerald border border-court-border">
                                        {{ $event->minute }}'
                                    </span>
                                    <span class="font-headline font-bold text-xs uppercase tracking-wider
                                        {{ in_array($event->event_type, ['goal', 'own_goal']) ? 'text-stadium-emerald' : '' }}
                                        {{ $event->event_type === 'yellow_card' ? 'text-card-yellow' : '' }}
                                        {{ in_array($event->event_type, ['red_card', 'second_yellow']) ? 'text-card-red' : '' }}">
                                        {{ $event->event_label }}
                                    </span>
                                </div>

                                <span class="font-mono text-xs text-text-muted">
                                    {{ $event->team->name }}
                                </span>
                            </div>

                            <!-- Player details -->
                            <div class="space-y-1">
                                <div class="font-headline font-bold text-sm text-text-primary flex items-center gap-2">
                                    <span>{{ $event->player?->name ?? 'Tim' }}</span>
                                    @if($event->player)
                                        <span class="text-xs font-mono text-text-muted">#{{ $event->player->jersey_number }} ({{ $event->player->position }})</span>
                                    @endif
                                </div>

                                @if($event->assistPlayer)
                                    <p class="text-xs font-mono text-telemetry-cyan flex items-center gap-1">
                                        <span>👟 Assist oleh:</span>
                                        <strong>{{ $event->assistPlayer->name }}</strong> (#{{ $event->assistPlayer->jersey_number }})
                                    </p>
                                @endif

                                @if($event->notes)
                                    <p class="text-xs text-text-muted italic pt-1">
                                        "{{ $event->notes }}"
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center text-text-muted font-mono text-xs">
                        Belum ada peristiwa atau gol yang dicatat dalam pertandingan ini.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- RIGHT COLUMN: TEAM SQUAD ROSTERS & OFFICIALS -->
        <div class="lg:col-span-5 space-y-6">
            @if($match->referee1 || $match->referee2 || $match->referee3)
                <div class="bg-court-surface rounded-xl border border-court-border p-5 space-y-3">
                    <div class="flex items-center justify-between pb-3 border-b border-court-border">
                        <h4 class="font-headline font-bold text-xs uppercase tracking-wider text-telemetry-cyan flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-base">sports</span>
                            Perangkat Pertandingan (Wasit Lapangan)
                        </h4>
                        <span class="text-[10px] font-mono text-text-muted">OFFICIALS</span>
                    </div>
                    <div class="space-y-2 text-xs font-mono">
                        @if($match->referee1)
                            <div class="flex items-center justify-between p-2 rounded-lg bg-court-navy/80 border border-court-border/40">
                                <span class="text-text-muted">Wasit 1 (Utama):</span>
                                <span class="text-text-primary font-bold">{{ $match->referee1->name }} <span class="text-[10px] text-stadium-emerald font-normal">({{ $match->referee1->license ?? 'Wasit' }})</span></span>
                            </div>
                        @endif
                        @if($match->referee2)
                            <div class="flex items-center justify-between p-2 rounded-lg bg-court-navy/80 border border-court-border/40">
                                <span class="text-text-muted">Wasit 2:</span>
                                <span class="text-text-primary font-bold">{{ $match->referee2->name }} <span class="text-[10px] text-stadium-emerald font-normal">({{ $match->referee2->license ?? 'Wasit' }})</span></span>
                            </div>
                        @endif
                        @if($match->referee3)
                            <div class="flex items-center justify-between p-2 rounded-lg bg-court-navy/80 border border-court-border/40">
                                <span class="text-text-muted">Wasit Cadangan:</span>
                                <span class="text-text-primary font-bold">{{ $match->referee3->name }} <span class="text-[10px] text-stadium-emerald font-normal">({{ $match->referee3->license ?? 'Wasit' }})</span></span>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Home Team Line-up -->
            <div class="bg-court-surface rounded-xl border border-court-border p-5 space-y-3">
                <div class="flex items-center justify-between pb-3 border-b border-court-border">
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded bg-court-navy border border-court-border flex items-center justify-center font-headline font-bold text-xs text-stadium-emerald">
                            {{ $match->homeTeam->initials }}
                        </div>
                        <h4 class="font-headline font-bold text-sm text-text-primary">{{ $match->homeTeam->name }}</h4>
                    </div>
                    <span class="text-[10px] font-mono text-text-muted">SKUAD</span>
                </div>

                <div class="divide-y divide-court-border/40 text-xs">
                    @forelse($match->homeTeam->players as $p)
                        <div class="py-2 flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <span class="w-6 font-mono font-bold text-stadium-emerald">#{{ $p->jersey_number }}</span>
                                <span class="font-sans text-text-primary">{{ $p->name }}</span>
                                @if($p->is_captain)
                                    <span class="px-1 py-0.2 rounded bg-card-yellow/20 text-card-yellow text-[9px] font-mono font-bold">(C)</span>
                                @endif
                            </div>
                            <span class="font-mono text-[10px] text-text-muted px-1.5 py-0.5 rounded bg-court-navy border border-court-border">
                                {{ $p->position }}
                            </span>
                        </div>
                    @empty
                        <p class="py-2 text-xs font-mono text-text-muted">Belum ada daftar pemain.</p>
                    @endforelse
                </div>
            </div>

            <!-- Away Team Line-up -->
            <div class="bg-court-surface rounded-xl border border-court-border p-5 space-y-3">
                <div class="flex items-center justify-between pb-3 border-b border-court-border">
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded bg-court-navy border border-court-border flex items-center justify-center font-headline font-bold text-xs text-telemetry-cyan">
                            {{ $match->awayTeam->initials }}
                        </div>
                        <h4 class="font-headline font-bold text-sm text-text-primary">{{ $match->awayTeam->name }}</h4>
                    </div>
                    <span class="text-[10px] font-mono text-text-muted">SKUAD</span>
                </div>

                <div class="divide-y divide-court-border/40 text-xs">
                    @forelse($match->awayTeam->players as $p)
                        <div class="py-2 flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <span class="w-6 font-mono font-bold text-telemetry-cyan">#{{ $p->jersey_number }}</span>
                                <span class="font-sans text-text-primary">{{ $p->name }}</span>
                                @if($p->is_captain)
                                    <span class="px-1 py-0.2 rounded bg-card-yellow/20 text-card-yellow text-[9px] font-mono font-bold">(C)</span>
                                @endif
                            </div>
                            <span class="font-mono text-[10px] text-text-muted px-1.5 py-0.5 rounded bg-court-navy border border-court-border">
                                {{ $p->position }}
                            </span>
                        </div>
                    @empty
                        <p class="py-2 text-xs font-mono text-text-muted">Belum ada daftar pemain.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function matchDetail(matchId, isInitiallyLive) {
        return {
            matchId: matchId,
            matchData: {
                home_score: {{ $match->home_score }},
                away_score: {{ $match->away_score }},
                current_minute: {{ $match->current_minute }},
                timer_seconds: {{ $match->elapsed_seconds }},
                timer_running: {{ $match->timer_running ? 'true' : 'false' }},
                time_formatted: "{{ $match->time_formatted }}",
                is_live: {{ $match->isLive() ? 'true' : 'false' }},
                status_badge: {
                    text: "{{ $match->status_badge['text'] }}",
                    color: "{{ $match->status_badge['color'] }}"
                }
            },
            init() {
                if (this.matchData.is_live) {
                    this.pollLive();
                }
            },
            pollLive() {
                setInterval(() => {
                    fetch(`/api/matches/${this.matchId}/live`)
                        .then(res => res.json())
                        .then(data => {
                            this.matchData.home_score = data.home_score;
                            this.matchData.away_score = data.away_score;
                            this.matchData.current_minute = data.current_minute;
                            this.matchData.is_live = data.is_live;
                            this.matchData.status_badge = data.status_badge;
                        })
                        .catch(() => {});
                }, 3000);
            }
        }
    }
</script>
@endpush
