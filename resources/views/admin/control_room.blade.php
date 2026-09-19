@extends('layouts.app')

@section('title', 'Control Room Wasit Meja - ' . $match->homeTeam->name . ' vs ' . $match->awayTeam->name)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6" 
     x-data="{
        isFinished: {{ $match->status === 'finished' ? 'true' : 'false' }},
        goalModal: false,
        cardModal: false,
        ownGoalModal: false,
        scoreModal: false,
        timerModal: false,
        homePenaltyScore: {{ $match->home_penalty_score ?? 0 }},
        awayPenaltyScore: {{ $match->away_penalty_score ?? 0 }},
        goalTeamId: '{{ $match->home_team_id }}',
        cardTeamId: '{{ $match->home_team_id }}',
        timerSeconds: {{ $match->elapsed_seconds }},
        timerRunning: {{ $match->timer_running ? 'true' : 'false' }},
        timeFormatted: '{{ $match->time_formatted }}',
        currentMinute: {{ $match->current_minute }},
        isUpdating: false,

        updatePenalty(deltaHome, deltaAway) {
            this.homePenaltyScore = Math.max(0, this.homePenaltyScore + deltaHome);
            this.awayPenaltyScore = Math.max(0, this.awayPenaltyScore + deltaAway);
            fetch('{{ route('admin.matches.penalty_score', $match->id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    home_penalty_score: this.homePenaltyScore,
                    away_penalty_score: this.awayPenaltyScore
                })
            }).then(r => r.json()).catch(() => {});
        },

        init() {
            if (!this.isFinished) {
                setInterval(() => {
                    if (this.timerRunning) {
                        this.timerSeconds++;
                        this.updateFormatted();
                    }
                }, 1000);
            }
        },

        updateFormatted() {
            const mins = Math.floor(this.timerSeconds / 60);
            const secs = this.timerSeconds % 60;
            this.timeFormatted = String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
            this.currentMinute = Math.max(1, Math.ceil(this.timerSeconds / 60));
        },

        toggleTimer() {
            if (this.isFinished) return;
            this.isUpdating = true;
            fetch('{{ route('admin.matches.timer.toggle', $match->id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                this.timerRunning = data.timer_running;
                this.timerSeconds = data.elapsed_seconds;
                this.timeFormatted = data.time_formatted;
                this.currentMinute = data.current_minute;
                this.isUpdating = false;
            })
            .catch(() => { this.isUpdating = false; });
        },

        setTimerSeconds(sec) {
            if (this.isFinished) return;
            this.isUpdating = true;
            fetch('{{ route('admin.matches.timer.set', $match->id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ seconds: Math.max(0, sec) })
            })
            .then(res => res.json())
            .then(data => {
                this.timerSeconds = data.elapsed_seconds;
                this.timeFormatted = data.time_formatted;
                this.currentMinute = data.current_minute;
                this.isUpdating = false;
            })
            .catch(() => { this.isUpdating = false; });
        },

        adjustSeconds(delta) {
            if (this.isFinished) return;
            this.setTimerSeconds(this.timerSeconds + delta);
        }
     }">

    <!-- Top Navigation & Return Link -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-3 border-b border-court-border">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.fixtures') }}" class="p-2 rounded bg-court-surface hover:bg-court-surface-elevated text-text-muted hover:text-text-primary border border-court-border transition-colors">
                <span class="material-symbols-outlined text-sm">arrow_back</span>
            </a>
            <div>
                <span class="text-[10px] font-mono tracking-widest text-live-pulse uppercase font-bold flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-live-pulse animate-ping"></span>
                    WASIT MEJA & OPERATOR CONTROL ROOM
                </span>
                <h1 class="text-xl font-headline font-black text-text-primary uppercase">
                    {{ $match->homeTeam->name }} <span class="text-text-muted">VS</span> {{ $match->awayTeam->name }}
                </h1>
            </div>
        </div>

        <div class="flex items-center gap-2 font-mono text-xs">
            <a href="{{ route('matches.show', $match->id) }}" target="_blank" class="px-3 py-1.5 rounded bg-court-surface hover:bg-court-surface-elevated text-text-muted hover:text-stadium-emerald border border-court-border flex items-center gap-1.5 transition-colors">
                <span class="material-symbols-outlined text-sm">open_in_new</span>
                Buka Layar Publik
            </a>
        </div>
    </div>

    @if($match->status === 'finished')
        <!-- MATCH FULL TIME / FINISHED BANNER (STOPWATCH HILANG & WAKTU DIKUNCI) -->
        <div class="p-6 rounded-xl bg-court-surface border border-stadium-emerald/40 shadow-xl space-y-4">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-stadium-emerald/10 border border-stadium-emerald/30 flex items-center justify-center text-stadium-emerald flex-shrink-0 shadow-[0_0_20px_rgba(0,255,135,0.25)]">
                        <span class="material-symbols-outlined text-3xl">sports_score</span>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 font-mono text-xs">
                            <span class="px-2.5 py-0.5 rounded-full font-bold uppercase bg-stadium-emerald text-court-navy tracking-wider shadow-sm">
                                FULL TIME &bull; PERTANDINGAN SELESAI
                            </span>
                            <span class="text-text-muted">Skor Terkunci: <strong class="text-stadium-emerald font-bold">{{ $match->home_score }} - {{ $match->away_score }}</strong></span>
                        </div>
                        <h2 class="font-headline font-bold text-lg text-text-primary mt-1 uppercase tracking-wide">
                            Laga Telah Selesai
                        </h2>
                        <p class="text-xs font-mono text-text-muted">
                            Stopwatch dinonaktifkan &bull; Pengaturan waktu pertandingan telah dikunci secara resmi.
                        </p>
                    </div>
                </div>

                <!-- Tombol jika panitia/wasit perlu membuka kembali babak -->
                <div class="flex items-center gap-2">
                    <form action="{{ route('admin.matches.status', $match->id) }}" method="POST" class="inline" onsubmit="return confirm('Buka kembali pertandingan ke Babak 2?')">
                        @csrf
                        <input type="hidden" name="status" value="second_half">
                        <input type="hidden" name="force" value="1">
                        <button type="submit" class="px-4 py-2 rounded-lg bg-court-navy hover:bg-court-surface-elevated border border-court-border text-xs font-mono text-text-muted hover:text-telemetry-cyan transition-colors flex items-center gap-1.5 shadow-sm">
                            <span class="material-symbols-outlined text-sm">replay</span>
                            Buka Kembali Babak 2
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @else
        <!-- OFFICIAL STOPWATCH TELEMETRY COMMAND DECK -->
        <div class="p-6 rounded-xl bg-court-surface border border-court-border shadow-xl space-y-4">
            <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                <!-- Big Digital Stopwatch Readout -->
                <div class="flex items-center gap-6">
                    <div class="p-4 px-6 rounded-2xl bg-court-navy/90 border-2 border-court-border shadow-inner text-center">
                        <span class="text-[10px] font-mono text-text-muted tracking-widest block uppercase">STOPWATCH WAKTU BERSIH</span>
                        <div class="font-headline font-black text-5xl sm:text-6xl tracking-wider text-stadium-emerald mt-1 font-mono" x-text="timeFormatted">
                            {{ $match->time_formatted }}
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <div class="flex items-center gap-2 font-mono text-xs">
                            <span class="w-3 h-3 rounded-full" 
                                  :class="timerRunning ? 'bg-stadium-emerald animate-ping' : 'bg-card-yellow'"></span>
                            <span class="font-bold text-sm"
                                  :class="timerRunning ? 'text-stadium-emerald' : 'text-card-yellow'"
                                  x-text="timerRunning ? 'STOPWATCH AKTIF (RUNNING)' : 'STOPWATCH DIJEDA (PAUSED)'">
                                {{ $match->timer_running ? 'STOPWATCH AKTIF (RUNNING)' : 'STOPWATCH DIJEDA (PAUSED)' }}
                            </span>
                        </div>
                        <p class="text-xs font-mono text-text-muted">
                            Menit Berjalan: <strong class="text-text-primary" x-text="'Menit ke-' + currentMinute + '\''">Menit ke-{{ $match->current_minute }}'</strong>
                        </p>
                        <p class="text-[11px] font-mono text-text-muted">
                            Status Laga: <span class="text-telemetry-cyan font-bold uppercase">{{ $match->status_badge['text'] }}</span>
                        </p>
                    </div>
                </div>

                <!-- Main Big Stopwatch Control Buttons -->
                <div class="flex flex-wrap items-center gap-3">
                    <!-- Master Toggle Button: Start/Pause -->
                    <button type="button" @click="toggleTimer()" 
                            :disabled="isUpdating"
                            class="px-6 py-3.5 rounded-xl font-headline font-bold text-sm tracking-wider uppercase transition-all shadow-lg flex items-center gap-2.5"
                            :class="timerRunning ? 'bg-card-red hover:bg-card-red/90 text-white shadow-[0_0_20px_rgba(255,42,77,0.4)]' : 'bg-stadium-emerald hover:bg-stadium-emerald/90 text-court-navy shadow-[0_0_20px_rgba(0,255,135,0.4)]'">
                        <span class="material-symbols-outlined text-xl" x-text="timerRunning ? 'pause_circle' : 'play_circle'">
                            {{ $match->timer_running ? 'pause_circle' : 'play_circle' }}
                        </span>
                        <span x-text="timerRunning ? 'JEDA WAKTU (PAUSE)' : 'MULAI WAKTU (START)'">
                            {{ $match->timer_running ? 'JEDA WAKTU (PAUSE)' : 'MULAI WAKTU (START)' }}
                        </span>
                    </button>

                    <!-- Quick Adjustments -->
                    <div class="flex items-center gap-2">
                        <button type="button" @click="setTimerSeconds(0)" title="Set ke 00:00 (Awal Babak 1)" class="px-3 py-2.5 rounded-lg bg-court-surface-elevated hover:bg-court-border border border-court-border font-mono text-xs text-text-primary transition-colors">
                            ⏮ 00:00
                        </button>
                        <button type="button" @click="setTimerSeconds({{ $match->half_duration_seconds }})" title="Set ke {{ sprintf('%02d:00', $match->half_duration_minutes) }} (Awal Babak 2)" class="px-3 py-2.5 rounded-lg bg-court-surface-elevated hover:bg-court-border border border-court-border font-mono text-xs text-text-primary transition-colors">
                            ⏭ {{ sprintf('%02d:00', $match->half_duration_minutes) }}
                        </button>
                        <button type="button" @click="adjustSeconds(30)" title="Tambah 30 Detik" class="px-3 py-2.5 rounded-lg bg-court-surface-elevated hover:bg-court-border border border-court-border font-mono text-xs text-stadium-emerald font-bold transition-colors">
                            +30s
                        </button>
                        <button type="button" @click="adjustSeconds(-30)" title="Kurang 30 Detik" class="px-3 py-2.5 rounded-lg bg-court-surface-elevated hover:bg-court-border border border-court-border font-mono text-xs text-card-yellow font-bold transition-colors">
                            -30s
                        </button>
                    </div>
                </div>
            </div>

            <!-- STATE MACHINE STEPPER & PERIOD PROGRESSION COMMAND DECK -->
            <div class="pt-5 border-t border-court-border/60 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div class="text-xs font-mono text-text-muted flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-base text-stadium-emerald">linear_scale</span>
                        <span class="font-bold uppercase text-text-primary tracking-wider">Alur Transisi Babak (State Machine Stepper):</span>
                    </div>
                    <div class="text-[11px] font-mono text-text-muted">
                        Format Waktu: <strong class="text-stadium-emerald">2 x {{ $match->half_duration_minutes }} Menit</strong> &bull; Extra Time: <strong class="text-card-yellow">{{ $match->extra_time_duration_minutes }} Menit</strong>
                    </div>
                </div>

                <!-- Step Indicators -->
                <div class="grid grid-cols-2 sm:grid-cols-6 gap-2 font-mono text-xs">
                    <!-- Step 1: Babak 1 -->
                    <div class="p-2.5 rounded-lg border flex flex-col gap-1 transition-all {{ $match->status === 'first_half' ? 'bg-stadium-emerald/10 border-stadium-emerald text-stadium-emerald ring-1 ring-stadium-emerald/50' : (in_array($match->status, ['half_time', 'second_half', 'extra_time', 'penalty_shootout', 'finished']) ? 'bg-court-navy/60 border-court-border text-stadium-emerald/80' : 'bg-court-navy border-court-border text-text-muted') }}">
                        <div class="flex items-center justify-between">
                            <span class="font-bold">1. BABAK 1</span>
                            @if(in_array($match->status, ['half_time', 'second_half', 'extra_time', 'penalty_shootout', 'finished']))
                                <span class="material-symbols-outlined text-sm text-stadium-emerald">check_circle</span>
                            @elseif($match->status === 'first_half')
                                <span class="w-2 h-2 rounded-full bg-stadium-emerald animate-ping"></span>
                            @endif
                        </div>
                        <span class="text-[10px] text-text-muted">00:00 - {{ sprintf('%02d:00', $match->half_duration_minutes) }}</span>
                    </div>

                    <!-- Step 2: Half Time -->
                    <div class="p-2.5 rounded-lg border flex flex-col gap-1 transition-all {{ $match->status === 'half_time' ? 'bg-card-yellow/10 border-card-yellow text-card-yellow ring-1 ring-card-yellow/50' : (in_array($match->status, ['second_half', 'extra_time', 'penalty_shootout', 'finished']) ? 'bg-court-navy/60 border-court-border text-card-yellow/80' : 'bg-court-navy border-court-border text-text-muted') }}">
                        <div class="flex items-center justify-between">
                            <span class="font-bold">2. HALF TIME</span>
                            @if(in_array($match->status, ['second_half', 'extra_time', 'penalty_shootout', 'finished']))
                                <span class="material-symbols-outlined text-sm text-card-yellow">check_circle</span>
                            @elseif($match->status === 'half_time')
                                <span class="w-2 h-2 rounded-full bg-card-yellow animate-ping"></span>
                            @endif
                        </div>
                        <span class="text-[10px] text-text-muted">Jeda Istirahat</span>
                    </div>

                    <!-- Step 3: Babak 2 -->
                    <div class="p-2.5 rounded-lg border flex flex-col gap-1 transition-all {{ $match->status === 'second_half' ? 'bg-stadium-emerald/10 border-stadium-emerald text-stadium-emerald ring-1 ring-stadium-emerald/50' : (in_array($match->status, ['extra_time', 'penalty_shootout', 'finished']) ? 'bg-court-navy/60 border-court-border text-stadium-emerald/80' : 'bg-court-navy border-court-border text-text-muted') }}">
                        <div class="flex items-center justify-between">
                            <span class="font-bold">3. BABAK 2</span>
                            @if(in_array($match->status, ['extra_time', 'penalty_shootout', 'finished']))
                                <span class="material-symbols-outlined text-sm text-stadium-emerald">check_circle</span>
                            @elseif($match->status === 'second_half')
                                <span class="w-2 h-2 rounded-full bg-stadium-emerald animate-ping"></span>
                            @endif
                        </div>
                        <span class="text-[10px] text-text-muted">{{ sprintf('%02d:00', $match->half_duration_minutes) }} - {{ sprintf('%02d:00', $match->half_duration_minutes * 2) }}</span>
                    </div>

                    <!-- Step 4: Babak Tambahan -->
                    <div class="p-2.5 rounded-lg border flex flex-col gap-1 transition-all {{ $match->status === 'extra_time' ? 'bg-telemetry-cyan/10 border-telemetry-cyan text-telemetry-cyan ring-1 ring-telemetry-cyan/50' : (in_array($match->status, ['penalty_shootout', 'finished']) ? 'bg-court-navy/60 border-court-border text-text-muted' : 'bg-court-navy border-court-border text-text-muted/60') }}">
                        <div class="flex items-center justify-between">
                            <span class="font-bold">4. EXTRA TIME</span>
                            @if($match->status === 'extra_time')
                                <span class="w-2 h-2 rounded-full bg-telemetry-cyan animate-ping"></span>
                            @endif
                        </div>
                        <span class="text-[10px] text-text-muted">+{{ $match->extra_time_duration_minutes }}' Menit</span>
                    </div>

                    <!-- Step 5: Adu Penalti -->
                    <div class="p-2.5 rounded-lg border flex flex-col gap-1 transition-all {{ $match->status === 'penalty_shootout' ? 'bg-card-yellow/20 border-card-yellow text-card-yellow ring-1 ring-card-yellow/50' : ($match->status === 'finished' && $match->has_penalty ? 'bg-court-navy/60 border-court-border text-card-yellow/80' : 'bg-court-navy border-court-border text-text-muted/60') }}">
                        <div class="flex items-center justify-between">
                            <span class="font-bold">5. PENALTI</span>
                            @if($match->status === 'penalty_shootout')
                                <span class="w-2 h-2 rounded-full bg-card-yellow animate-ping"></span>
                            @elseif($match->has_penalty)
                                <span class="material-symbols-outlined text-sm text-card-yellow">check_circle</span>
                            @endif
                        </div>
                        <span class="text-[10px] text-text-muted">Adu Penalti</span>
                    </div>

                    <!-- Step 6: Selesai (Full Time) -->
                    <div class="p-2.5 rounded-lg border flex flex-col gap-1 transition-all {{ $match->status === 'finished' ? 'bg-stadium-emerald text-court-navy font-bold' : 'bg-court-navy border-court-border text-text-muted' }}">
                        <div class="flex items-center justify-between">
                            <span class="font-bold">6. FULL TIME</span>
                            @if($match->status === 'finished')
                                <span class="material-symbols-outlined text-sm">flag</span>
                            @endif
                        </div>
                        <span class="text-[10px] {{ $match->status === 'finished' ? 'text-court-navy/80' : 'text-text-muted' }}">Laga Selesai</span>
                    </div>
                </div>

                <!-- NEXT ACTION CONTROLLER (CLEAR, UNAMBIGUOUS BUTTONS) -->
                <div class="p-4 rounded-xl bg-court-surface-elevated/70 border border-court-border flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <span class="text-[10px] font-mono uppercase tracking-widest text-text-muted block">TINDAKAN WASIT BERIKUTNYA (NEXT ACTION):</span>
                        @if($match->status === 'scheduled')
                            <p class="text-xs font-mono text-text-primary mt-0.5">Pertandingan belum dimulai. Klik tombol untuk memulai Kick-Off Babak 1.</p>
                        @elseif($match->status === 'first_half')
                            <p class="text-xs font-mono text-text-primary mt-0.5">Babak 1 sedang berlangsung. Jika waktu telah mencapai {{ $match->half_duration_minutes }}', selesaikan babak untuk istirahat (Half Time).</p>
                        @elseif($match->status === 'half_time')
                            <p class="text-xs font-mono text-text-primary mt-0.5">Jeda istirahat babak. Klik tombol di kanan saat kedua tim siap memulai Babak 2.</p>
                        @elseif($match->status === 'second_half')
                            <p class="text-xs font-mono text-text-primary mt-0.5">Babak 2 sedang berlangsung. Anda dapat mengakhiri pertandingan (Full Time), lanjut Extra Time, atau langsung ke sesi Adu Penalti jika skor imbang.</p>
                        @elseif($match->status === 'extra_time')
                            <p class="text-xs font-mono text-text-primary mt-0.5">Babak tambahan sedang berlangsung (+{{ $match->extra_time_duration_minutes }}'). Anda dapat mengakhiri laga atau lanjut ke sesi Adu Penalti jika skor masih imbang.</p>
                        @elseif($match->status === 'penalty_shootout')
                            <p class="text-xs font-mono text-text-primary mt-0.5">Sesi adu penalti sedang berlangsung. Atur skor adu penalti di panel kontrol, lalu klik 'SELESAIKAN PENALTI & KUNCI LAGA' untuk meresmikan hasil akhir.</p>
                        @endif
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        @if($match->status === 'scheduled')
                            <form action="{{ route('admin.matches.status', $match->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="status" value="first_half">
                                <button type="submit" class="px-5 py-2.5 rounded-lg bg-stadium-emerald hover:bg-stadium-emerald/90 text-court-navy font-headline font-bold text-xs uppercase tracking-wider transition-all shadow-[0_0_15px_rgba(0,255,135,0.3)] flex items-center gap-2">
                                    <span class="material-symbols-outlined text-sm">play_arrow</span>
                                    MULAI BABAK 1 (KICK-OFF)
                                </button>
                            </form>
                        @elseif($match->status === 'first_half')
                            <form action="{{ route('admin.matches.status', $match->id) }}" method="POST" onsubmit="return confirm('Selesaikan Babak 1 dan jeda istirahat (Half Time)? Stopwatch akan berhenti di {{ sprintf('%02d:00', $match->half_duration_minutes) }}.')">
                                @csrf
                                <input type="hidden" name="status" value="half_time">
                                <button type="submit" class="px-5 py-2.5 rounded-lg bg-card-yellow hover:bg-card-yellow/90 text-court-navy font-headline font-bold text-xs uppercase tracking-wider transition-all shadow-[0_0_15px_rgba(255,214,0,0.3)] flex items-center gap-2">
                                    <span class="material-symbols-outlined text-sm">pause</span>
                                    SELESAIKAN BABAK 1 & JEDA (HALF TIME)
                                </button>
                            </form>
                        @elseif($match->status === 'half_time')
                            <form action="{{ route('admin.matches.status', $match->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="status" value="second_half">
                                <button type="submit" class="px-5 py-2.5 rounded-lg bg-stadium-emerald hover:bg-stadium-emerald/90 text-court-navy font-headline font-bold text-xs uppercase tracking-wider transition-all shadow-[0_0_15px_rgba(0,255,135,0.3)] flex items-center gap-2">
                                    <span class="material-symbols-outlined text-sm">play_arrow</span>
                                    MULAI BABAK 2 (SECOND HALF)
                                </button>
                            </form>
                        @elseif($match->status === 'second_half')
                            <!-- Option 1: Extra Time -->
                            <form action="{{ route('admin.matches.status', $match->id) }}" method="POST" onsubmit="return confirm('Lanjutkan pertandingan ke Babak Tambahan (Extra Time +{{ $match->extra_time_duration_minutes }} menit)?')">
                                @csrf
                                <input type="hidden" name="status" value="extra_time">
                                <button type="submit" class="px-4 py-2.5 rounded-lg bg-court-navy hover:bg-court-surface-elevated text-telemetry-cyan border border-telemetry-cyan/40 font-headline font-bold text-xs uppercase tracking-wider transition-all flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-sm">more_time</span>
                                    BABAK TAMBAHAN (+{{ $match->extra_time_duration_minutes }}')
                                </button>
                            </form>

                            <!-- Option 2: Adu Penalti -->
                            <form action="{{ route('admin.matches.status', $match->id) }}" method="POST" onsubmit="return confirm('Lanjutkan laga ke Sesi Adu Penalti (Shootout)?')">
                                @csrf
                                <input type="hidden" name="status" value="penalty_shootout">
                                <button type="submit" class="px-4 py-2.5 rounded-lg bg-court-navy hover:bg-court-surface-elevated text-card-yellow border border-card-yellow/40 font-headline font-bold text-xs uppercase tracking-wider transition-all flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-sm">sports_soccer</span>
                                    SESI ADU PENALTI
                                </button>
                            </form>

                            <!-- Option 3: Full Time -->
                            <form action="{{ route('admin.matches.status', $match->id) }}" method="POST" onsubmit="return confirm('Akhiri pertandingan ini secara resmi (Full Time)? Skor akhir dan waktu akan dikunci.')">
                                @csrf
                                <input type="hidden" name="status" value="finished">
                                <button type="submit" class="px-5 py-2.5 rounded-lg bg-stadium-emerald hover:bg-stadium-emerald/90 text-court-navy font-headline font-bold text-xs uppercase tracking-wider transition-all shadow-[0_0_15px_rgba(0,255,135,0.3)] flex items-center gap-2">
                                    <span class="material-symbols-outlined text-sm">flag</span>
                                    SELESAI LAGA (FULL TIME)
                                </button>
                            </form>
                        @elseif($match->status === 'extra_time')
                            <!-- Option 1: Adu Penalti -->
                            <form action="{{ route('admin.matches.status', $match->id) }}" method="POST" onsubmit="return confirm('Lanjutkan pertandingan ke Sesi Adu Penalti (Shootout)?')">
                                @csrf
                                <input type="hidden" name="status" value="penalty_shootout">
                                <button type="submit" class="px-4 py-2.5 rounded-lg bg-court-navy hover:bg-court-surface-elevated text-card-yellow border border-card-yellow/40 font-headline font-bold text-xs uppercase tracking-wider transition-all flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-sm">sports_soccer</span>
                                    SESI ADU PENALTI
                                </button>
                            </form>

                            <!-- Option 2: Full Time after Extra Time -->
                            <form action="{{ route('admin.matches.status', $match->id) }}" method="POST" onsubmit="return confirm('Akhiri babak tambahan dan resmikan hasil pertandingan (Full Time)?')">
                                @csrf
                                <input type="hidden" name="status" value="finished">
                                <button type="submit" class="px-5 py-2.5 rounded-lg bg-stadium-emerald hover:bg-stadium-emerald/90 text-court-navy font-headline font-bold text-xs uppercase tracking-wider transition-all shadow-[0_0_15px_rgba(0,255,135,0.3)] flex items-center gap-2">
                                    <span class="material-symbols-outlined text-sm">flag</span>
                                    AKHIRI LAGA (FULL TIME)
                                </button>
                            </form>
                        @elseif($match->status === 'penalty_shootout')
                            <form action="{{ route('admin.matches.status', $match->id) }}" method="POST" onsubmit="return confirm('Akhiri adu penalti dan resmikan hasil akhir pertandingan (Full Time)?')">
                                @csrf
                                <input type="hidden" name="status" value="finished">
                                <button type="submit" class="px-5 py-2.5 rounded-lg bg-stadium-emerald hover:bg-stadium-emerald/90 text-court-navy font-headline font-bold text-xs uppercase tracking-wider transition-all shadow-[0_0_15px_rgba(0,255,135,0.3)] flex items-center gap-2">
                                    <span class="material-symbols-outlined text-sm">flag</span>
                                    SELESAIKAN PENALTI & KUNCI LAGA (FULL TIME)
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <!-- EMERGENCY ROLLBACK ACCORDION (FOR HUMAN ERROR CORRECTION) -->
                <div x-data="{ openRollback: false }" class="pt-1">
                    <div class="flex items-center justify-between">
                        <button type="button" @click="openRollback = !openRollback" class="text-xs font-mono text-card-yellow hover:text-card-yellow/80 flex items-center gap-1.5 transition-colors">
                            <span class="material-symbols-outlined text-sm" x-text="openRollback ? 'expand_less' : 'tune'">tune</span>
                            <span x-text="openRollback ? 'Tutup Menu Koreksi Alur' : '⚠️ Koreksi / Rollback Status Babak (Khusus Jika Terjadi Salah Klik)'"></span>
                        </button>
                        <span class="text-[10px] font-mono text-text-muted hidden sm:inline">Hanya gunakan jika operator tidak sengaja menekan tombol transisi</span>
                    </div>

                    <div x-show="openRollback" x-cloak class="mt-3 p-3 bg-court-navy/90 rounded-lg border border-court-border space-y-2 text-xs font-mono">
                        <p class="text-text-muted text-[11px]">Pilih babak yang ingin dipulihkan secara paksa (Emergency Override):</p>
                        <div class="flex flex-wrap items-center gap-2">
                            @if($match->status !== 'scheduled')
                                <form action="{{ route('admin.matches.status', $match->id) }}" method="POST" onsubmit="return confirm('PERINGATAN: Kembalikan pertandingan ke status Belum Dimulai (Scheduled)?')">
                                    @csrf
                                    <input type="hidden" name="status" value="scheduled">
                                    <input type="hidden" name="force" value="1">
                                    <button type="submit" class="px-2.5 py-1.5 rounded bg-court-surface hover:bg-card-red/20 text-text-muted hover:text-card-red border border-court-border transition-colors">
                                        ↩ Kembalikan ke Terjadwal
                                    </button>
                                </form>
                            @endif

                            @if($match->status !== 'first_half')
                                <form action="{{ route('admin.matches.status', $match->id) }}" method="POST" onsubmit="return confirm('PERINGATAN: Kembalikan pertandingan ke Babak 1?')">
                                    @csrf
                                    <input type="hidden" name="status" value="first_half">
                                    <input type="hidden" name="force" value="1">
                                    <button type="submit" class="px-2.5 py-1.5 rounded bg-court-surface hover:bg-card-yellow/20 text-text-muted hover:text-card-yellow border border-court-border transition-colors">
                                        ↩ Kembalikan ke Babak 1
                                    </button>
                                </form>
                            @endif

                            @if($match->status !== 'half_time')
                                <form action="{{ route('admin.matches.status', $match->id) }}" method="POST" onsubmit="return confirm('PERINGATAN: Kembalikan pertandingan ke Istirahat (Half Time)?')">
                                    @csrf
                                    <input type="hidden" name="status" value="half_time">
                                    <input type="hidden" name="force" value="1">
                                    <button type="submit" class="px-2.5 py-1.5 rounded bg-court-surface hover:bg-card-yellow/20 text-text-muted hover:text-card-yellow border border-court-border transition-colors">
                                        ↩ Kembalikan ke Half Time
                                    </button>
                                </form>
                            @endif

                            @if($match->status !== 'second_half')
                                <form action="{{ route('admin.matches.status', $match->id) }}" method="POST" onsubmit="return confirm('PERINGATAN: Kembalikan pertandingan ke Babak 2?')">
                                    @csrf
                                    <input type="hidden" name="status" value="second_half">
                                    <input type="hidden" name="force" value="1">
                                    <button type="submit" class="px-2.5 py-1.5 rounded bg-court-surface hover:bg-stadium-emerald/20 text-text-muted hover:text-stadium-emerald border border-court-border transition-colors">
                                        ↩ Kembalikan ke Babak 2
                                    </button>
                                </form>
                            @endif

                            @if($match->status !== 'penalty_shootout')
                                <form action="{{ route('admin.matches.status', $match->id) }}" method="POST" onsubmit="return confirm('PERINGATAN: Kembalikan pertandingan ke status Adu Penalti?')">
                                    @csrf
                                    <input type="hidden" name="status" value="penalty_shootout">
                                    <input type="hidden" name="force" value="1">
                                    <button type="submit" class="px-2.5 py-1.5 rounded bg-court-surface hover:bg-card-yellow/20 text-text-muted hover:text-card-yellow border border-court-border transition-colors">
                                        ↩ Kembalikan ke Adu Penalti
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- MAIN SCOREBOARD DIGITAL READOUT -->
    <div class="rounded-xl overflow-hidden bg-court-surface border border-court-border p-6 shadow-2xl">
        <div class="grid grid-cols-1 md:grid-cols-11 items-center gap-6 text-center">
            <!-- Home Team -->
            <div class="md:col-span-4 flex flex-col md:flex-row items-center justify-end gap-4">
                <div class="text-center md:text-right order-2 md:order-1">
                    <h2 class="text-2xl sm:text-3xl font-headline font-black text-text-primary">{{ $match->homeTeam->name }}</h2>
                    <span class="text-xs font-mono text-stadium-emerald">KANDANG (HOME)</span>
                </div>
                <div class="w-20 h-20 rounded-xl bg-court-surface-elevated border border-court-border flex items-center justify-center font-headline font-black text-2xl text-stadium-emerald shadow-inner order-1 md:order-2 flex-shrink-0 overflow-hidden p-2">
                    @if($match->homeTeam->logo)
                        <img src="{{ $match->homeTeam->logo_url }}" alt="{{ $match->homeTeam->name }}" class="w-full h-full object-contain">
                    @else
                        {{ $match->homeTeam->initials }}
                    @endif
                </div>
            </div>

            <!-- Big Digital Score Center -->
            <div class="md:col-span-3 flex flex-col items-center justify-center py-4 bg-court-navy/80 rounded-xl border border-court-border/60">
                <div class="flex items-center gap-4 font-headline text-6xl sm:text-7xl font-black tracking-tight">
                    <span class="text-stadium-emerald">{{ $match->home_score }}</span>
                    <span class="text-court-border">:</span>
                    <span class="text-text-primary">{{ $match->away_score }}</span>
                </div>

                <div class="mt-2 flex flex-col items-center gap-1 font-mono text-xs">
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded font-bold border {{ $match->status_badge['color'] }}">
                            {{ $match->status_badge['text'] }}
                        </span>
                        @if($match->status !== 'finished' && $match->status !== 'penalty_shootout')
                            <span class="text-text-muted">MENIT {{ $match->current_minute }}'</span>
                        @endif
                    </div>

                    <template x-if="homePenaltyScore > 0 || awayPenaltyScore > 0 || '{{ $match->status }}' === 'penalty_shootout' || {{ $match->has_penalty ? 'true' : 'false' }}">
                        <div class="mt-1 px-3 py-1 rounded-full bg-card-yellow/20 text-card-yellow border border-card-yellow/40 font-mono font-bold text-xs tracking-wider">
                            ADU PENALTI: <span x-text="homePenaltyScore">{{ $match->home_penalty_score ?? 0 }}</span> - <span x-text="awayPenaltyScore">{{ $match->away_penalty_score ?? 0 }}</span>
                        </div>
                    </template>
                </div>

                <!-- Manual Score Correction Trigger -->
                <button @click="scoreModal = true" class="mt-3 text-[11px] font-mono text-text-muted hover:text-telemetry-cyan underline">
                    Koreksi Manual Papan Skor
                </button>
            </div>

            <!-- Away Team -->
            <div class="md:col-span-4 flex flex-col md:flex-row items-center justify-start gap-4">
                <div class="w-20 h-20 rounded-xl bg-court-surface-elevated border border-court-border flex items-center justify-center font-headline font-black text-2xl text-telemetry-cyan shadow-inner flex-shrink-0 overflow-hidden p-2">
                    @if($match->awayTeam->logo)
                        <img src="{{ $match->awayTeam->logo_url }}" alt="{{ $match->awayTeam->name }}" class="w-full h-full object-contain">
                    @else
                        {{ $match->awayTeam->initials }}
                    @endif
                </div>
                <div class="text-center md:text-left">
                    <h2 class="text-2xl sm:text-3xl font-headline font-black text-text-primary">{{ $match->awayTeam->name }}</h2>
                    <span class="text-xs font-mono text-telemetry-cyan">TANDANG (AWAY)</span>
                </div>
            </div>
        </div>
    </div>

    <!-- DEDICATED PENALTY SHOOTOUT CONTROL DECK -->
    <div x-show="'{{ $match->status }}' === 'penalty_shootout' || {{ $match->has_penalty ? 'true' : 'false' }} || homePenaltyScore > 0 || awayPenaltyScore > 0" 
         class="rounded-xl overflow-hidden bg-court-surface border-2 {{ $match->status === 'penalty_shootout' ? 'border-card-yellow shadow-[0_0_30px_rgba(255,214,0,0.15)]' : 'border-court-border' }} p-6 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-court-border">
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-card-yellow/20 text-card-yellow border border-card-yellow/40 flex items-center justify-center font-bold text-xl">
                    ⚽
                </span>
                <div>
                    <h3 class="font-headline font-bold text-base uppercase text-card-yellow tracking-wider flex items-center gap-2">
                        Papan Skor Adu Penalti (Shootout Deck)
                        @if($match->status === 'penalty_shootout')
                            <span class="px-2 py-0.5 rounded text-[10px] bg-card-yellow text-court-navy font-bold animate-pulse">SESI AKTIF</span>
                        @endif
                    </h3>
                    <p class="text-xs font-mono text-text-muted">Tekan tombol +1 atau -1 untuk mencatat eksekusi tendangan penalti yang berhasil masuk</p>
                </div>
            </div>

            <div class="text-right font-mono text-xs text-text-muted">
                Status: <span class="font-bold text-text-primary uppercase">{{ $match->status_badge['text'] }}</span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
            <!-- Home Penalty Controls -->
            <div class="p-4 rounded-xl bg-court-navy border border-court-border space-y-3">
                <div class="flex items-center justify-between">
                    <span class="font-headline font-bold text-sm text-stadium-emerald uppercase truncate">{{ $match->homeTeam->name }} (HOME)</span>
                    <span class="text-[10px] font-mono text-text-muted uppercase">Penalti Masuk</span>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <div class="font-headline font-black text-5xl text-stadium-emerald font-mono" x-text="homePenaltyScore">
                        {{ $match->home_penalty_score ?? 0 }}
                    </div>
                    @if($match->status !== 'finished')
                        <div class="flex items-center gap-2">
                            <button type="button" @click="updatePenalty(-1, 0)" class="w-12 h-12 rounded-xl bg-court-surface hover:bg-card-red/20 text-card-red border border-court-border font-headline font-bold text-xl flex items-center justify-center transition-colors shadow-sm">
                                -1
                            </button>
                            <button type="button" @click="updatePenalty(1, 0)" class="w-14 h-12 rounded-xl bg-stadium-emerald hover:bg-stadium-emerald/90 text-court-navy font-headline font-black text-xl flex items-center justify-center transition-all shadow-[0_0_15px_rgba(0,255,135,0.3)]">
                                +1
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Away Penalty Controls -->
            <div class="p-4 rounded-xl bg-court-navy border border-court-border space-y-3">
                <div class="flex items-center justify-between">
                    <span class="font-headline font-bold text-sm text-telemetry-cyan uppercase truncate">{{ $match->awayTeam->name }} (AWAY)</span>
                    <span class="text-[10px] font-mono text-text-muted uppercase">Penalti Masuk</span>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <div class="font-headline font-black text-5xl text-telemetry-cyan font-mono" x-text="awayPenaltyScore">
                        {{ $match->away_penalty_score ?? 0 }}
                    </div>
                    @if($match->status !== 'finished')
                        <div class="flex items-center gap-2">
                            <button type="button" @click="updatePenalty(0, -1)" class="w-12 h-12 rounded-xl bg-court-surface hover:bg-card-red/20 text-card-red border border-court-border font-headline font-bold text-xl flex items-center justify-center transition-colors shadow-sm">
                                -1
                            </button>
                            <button type="button" @click="updatePenalty(0, 1)" class="w-14 h-12 rounded-xl bg-telemetry-cyan hover:bg-telemetry-cyan/90 text-court-navy font-headline font-black text-xl flex items-center justify-center transition-all shadow-[0_0_15px_rgba(0,229,255,0.3)]">
                                +1
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- QUICK WASIT ACTION BUTTONS -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <!-- CATAT GOL -->
        <button @click="goalModal = true" class="p-4 rounded-xl bg-stadium-emerald/10 hover:bg-stadium-emerald/20 border border-stadium-emerald/40 text-stadium-emerald flex items-center gap-3 transition-all hover:scale-[1.02] shadow-[0_0_15px_rgba(0,255,135,0.15)] text-left">
            <span class="w-10 h-10 rounded-lg bg-stadium-emerald/20 flex items-center justify-center text-xl font-bold flex-shrink-0">⚽</span>
            <div>
                <span class="font-headline font-bold text-sm block uppercase text-text-primary">Catat Gol</span>
                <span class="text-[10px] font-mono text-text-muted">Pencetak gol & assist</span>
            </div>
        </button>

        <!-- KARTU KUNING -->
        <button @click="cardModal = true; cardType = 'yellow_card'" class="p-4 rounded-xl bg-card-yellow/10 hover:bg-card-yellow/20 border border-card-yellow/40 text-card-yellow flex items-center gap-3 transition-all hover:scale-[1.02] text-left">
            <span class="w-10 h-10 rounded-lg bg-card-yellow/20 flex items-center justify-center text-xl font-bold flex-shrink-0">🟨</span>
            <div>
                <span class="font-headline font-bold text-sm block uppercase text-text-primary">Kartu Kuning</span>
                <span class="text-[10px] font-mono text-text-muted">Peringatan disiplin pemain</span>
            </div>
        </button>

        <!-- KARTU MERAH -->
        <button @click="cardModal = true; cardType = 'red_card'" class="p-4 rounded-xl bg-card-red/10 hover:bg-card-red/20 border border-card-red/40 text-card-red flex items-center gap-3 transition-all hover:scale-[1.02] text-left">
            <span class="w-10 h-10 rounded-lg bg-card-red/20 flex items-center justify-center text-xl font-bold flex-shrink-0">🟥</span>
            <div>
                <span class="font-headline font-bold text-sm block uppercase text-text-primary">Kartu Merah</span>
                <span class="text-[10px] font-mono text-text-muted">Pengeluaran pemain / 2x kuning</span>
            </div>
        </button>

        <!-- GOL BUNUH DIRI -->
        <button @click="ownGoalModal = true" class="p-4 rounded-xl bg-court-surface hover:bg-court-surface-elevated border border-court-border text-text-muted hover:text-text-primary flex items-center gap-3 transition-all hover:scale-[1.02] text-left">
            <span class="w-10 h-10 rounded-lg bg-court-navy flex items-center justify-center text-xl font-bold flex-shrink-0">🔄</span>
            <div>
                <span class="font-headline font-bold text-sm block uppercase text-text-primary">Gol Bunuh Diri</span>
                <span class="text-[10px] font-mono text-text-muted">Poin untuk tim lawan</span>
            </div>
        </button>
    </div>

    <!-- SPLIT VIEW: LIVE EVENT LOG & TIMELINE (Left 7 cols) & SQUAD QUICK SELECTOR (Right 5 cols) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

        <!-- LEFT COLUMN: LIVE EVENT LOG STREAM WITH UNDO/DELETE -->
        <div class="lg:col-span-7 bg-court-surface rounded-xl border border-court-border p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-court-border">
                <div>
                    <h3 class="font-headline font-bold uppercase tracking-wider text-sm flex items-center gap-2 text-text-primary">
                        <span class="material-symbols-outlined text-stadium-emerald text-base">format_list_bulleted</span>
                        Log Kejadian Pertandingan (Live Event Stream)
                    </h3>
                    <p class="text-[10px] font-mono text-text-muted">SECARA OTOMATIS MEMPERBARUI PAPAN SKOR & KLASEMEN</p>
                </div>

                <span class="text-xs font-mono text-stadium-emerald font-bold">
                    {{ $match->events->count() }} Peristiwa
                </span>
            </div>

            <!-- List of events -->
            <div class="space-y-3">
                @forelse($match->events as $ev)
                    <div class="p-3.5 rounded-lg bg-court-surface-elevated border border-court-border flex items-center justify-between gap-4 group hover:border-court-border/90 transition-all">
                        <div class="flex items-center gap-3">
                            <span class="px-2 py-1 rounded bg-court-navy font-mono font-bold text-xs text-stadium-emerald border border-court-border">
                                {{ $ev->minute }}'
                            </span>

                            <div class="text-xl">
                                @if($ev->event_type === 'goal') ⚽
                                @elseif($ev->event_type === 'own_goal') 🔄
                                @elseif($ev->event_type === 'yellow_card') 🟨
                                @elseif($ev->event_type === 'red_card' || $ev->event_type === 'second_yellow') 🟥
                                @else 👟
                                @endif
                            </div>

                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-headline font-bold text-sm text-text-primary">
                                        {{ $ev->player?->name ?? 'Insiden Tim' }}
                                    </span>
                                    @if($ev->player)
                                        <span class="text-xs font-mono text-text-muted">#{{ $ev->player->jersey_number }}</span>
                                    @endif
                                    <span class="text-xs font-mono text-telemetry-cyan font-semibold">
                                        &bull; {{ $ev->team->name }}
                                    </span>
                                </div>

                                <div class="text-[11px] font-mono text-text-muted flex items-center gap-2">
                                    <span class="font-bold uppercase 
                                        {{ in_array($ev->event_type, ['goal', 'own_goal']) ? 'text-stadium-emerald' : '' }}
                                        {{ $ev->event_type === 'yellow_card' ? 'text-card-yellow' : '' }}
                                        {{ in_array($ev->event_type, ['red_card', 'second_yellow']) ? 'text-card-red' : '' }}">
                                        {{ $ev->event_label }}
                                    </span>

                                    @if($ev->assistPlayer)
                                        <span>&bull; Assist: <strong>{{ $ev->assistPlayer->name }}</strong> (#{{ $ev->assistPlayer->jersey_number }})</span>
                                    @endif

                                    @if($ev->notes)
                                        <span class="italic">"{{ $ev->notes }}"</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Delete / Undo Event Button -->
                        <form action="{{ route('admin.matches.events.destroy', ['id' => $match->id, 'eventId' => $ev->id]) }}" method="POST" onsubmit="return confirm('Batalkan/Hapus peristiwa ini? Skor akan disinkronkan kembali.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" title="Batalkan Peristiwa" class="p-1.5 rounded bg-court-navy hover:bg-card-red/20 text-text-muted hover:text-card-red border border-court-border transition-colors">
                                <span class="material-symbols-outlined text-sm">undo</span>
                            </button>
                        </form>
                    </div>
                @empty
                    <div class="py-12 text-center text-text-muted font-mono text-xs">
                        Belum ada kejadian tercatat. Gunakan tombol aksi di atas untuk mencatat gol atau kartu.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- RIGHT COLUMN: TEAMS ROSTER OVERVIEW -->
        <div class="lg:col-span-5 space-y-4">
            <div class="bg-court-surface rounded-xl border border-court-border p-5 space-y-4">
                <div class="pb-2 border-b border-court-border">
                    <h3 class="font-headline font-bold uppercase tracking-wider text-sm text-text-primary">
                        Skuad Pemain Lapangan
                    </h3>
                    <p class="text-[10px] font-mono text-text-muted">REFERENSI NOMOR PUNGGUNG & POSISI</p>
                </div>

                <!-- Home Team Roster -->
                <div class="space-y-2">
                    <span class="text-xs font-headline font-bold text-stadium-emerald uppercase block">
                        {{ $match->homeTeam->name }} (Home)
                    </span>
                    <div class="grid grid-cols-2 gap-2 text-xs font-mono">
                        @foreach($match->homeTeam->players as $hp)
                            <div class="p-2 rounded bg-court-surface-elevated border border-court-border flex items-center justify-between">
                                <span class="text-stadium-emerald font-bold">#{{ $hp->jersey_number }}</span>
                                <span class="font-sans truncate text-text-primary text-[11px] px-1">{{ $hp->name }}</span>
                                <span class="text-[9px] text-text-muted bg-court-navy px-1 rounded">{{ $hp->position }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Away Team Roster -->
                <div class="space-y-2 pt-3 border-t border-court-border/40">
                    <span class="text-xs font-headline font-bold text-telemetry-cyan uppercase block">
                        {{ $match->awayTeam->name }} (Away)
                    </span>
                    <div class="grid grid-cols-2 gap-2 text-xs font-mono">
                        @foreach($match->awayTeam->players as $ap)
                            <div class="p-2 rounded bg-court-surface-elevated border border-court-border flex items-center justify-between">
                                <span class="text-telemetry-cyan font-bold">#{{ $ap->jersey_number }}</span>
                                <span class="font-sans truncate text-text-primary text-[11px] px-1">{{ $ap->name }}</span>
                                <span class="text-[9px] text-text-muted bg-court-navy px-1 rounded">{{ $ap->position }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL 1: CATAT GOL -->
    <template x-teleport="body">
    <div x-show="goalModal" x-cloak class="fixed inset-0 z-[100] bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-court-surface rounded-xl border border-court-border max-w-md w-full p-6 space-y-4 shadow-2xl">
            <div class="flex items-center justify-between pb-3 border-b border-court-border">
                <h3 class="font-headline font-bold text-base text-stadium-emerald uppercase flex items-center gap-2">
                    <span>⚽</span> Catat Gol Pertandingan
                </h3>
                <button @click="goalModal = false" class="text-text-muted hover:text-text-primary">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form action="{{ route('admin.matches.events.store', $match->id) }}" method="POST" class="space-y-4 text-xs font-mono">
                @csrf
                <input type="hidden" name="event_type" value="goal">

                <!-- Team Selector -->
                <div class="space-y-1">
                    <label class="block text-text-muted uppercase">Pilih Tim Pencetak Gol</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="p-3 rounded-lg border cursor-pointer text-center"
                               :class="goalTeamId == '{{ $match->home_team_id }}' ? 'bg-stadium-emerald/10 border-stadium-emerald text-stadium-emerald font-bold' : 'bg-court-navy border-court-border text-text-muted'">
                            <input type="radio" name="team_id" value="{{ $match->home_team_id }}" x-model="goalTeamId" class="hidden">
                            <span>{{ $match->homeTeam->name }}</span>
                        </label>
                        <label class="p-3 rounded-lg border cursor-pointer text-center"
                               :class="goalTeamId == '{{ $match->away_team_id }}' ? 'bg-telemetry-cyan/10 border-telemetry-cyan text-telemetry-cyan font-bold' : 'bg-court-navy border-court-border text-text-muted'">
                            <input type="radio" name="team_id" value="{{ $match->away_team_id }}" x-model="goalTeamId" class="hidden">
                            <span>{{ $match->awayTeam->name }}</span>
                        </label>
                    </div>
                </div>

                <!-- Scorer Select -->
                <div class="space-y-1">
                    <label class="block text-text-muted uppercase">Pencetak Gol (Scorer)</label>
                    <!-- Home Players -->
                    <select name="player_id" :disabled="goalTeamId != '{{ $match->home_team_id }}'" x-show="goalTeamId == '{{ $match->home_team_id }}'" class="w-full py-2 px-3 rounded bg-court-navy border border-court-border text-text-primary focus:border-stadium-emerald font-sans">
                        <option value="">-- Pilih Pemain {{ $match->homeTeam->name }} --</option>
                        @foreach($match->homeTeam->players as $p)
                            <option value="{{ $p->id }}">#{{ $p->jersey_number }} - {{ $p->name }} ({{ $p->position }})</option>
                        @endforeach
                    </select>
                    <!-- Away Players -->
                    <select name="player_id" :disabled="goalTeamId != '{{ $match->away_team_id }}'" x-show="goalTeamId == '{{ $match->away_team_id }}'" class="w-full py-2 px-3 rounded bg-court-navy border border-court-border text-text-primary focus:border-stadium-emerald font-sans">
                        <option value="">-- Pilih Pemain {{ $match->awayTeam->name }} --</option>
                        @foreach($match->awayTeam->players as $p)
                            <option value="{{ $p->id }}">#{{ $p->jersey_number }} - {{ $p->name }} ({{ $p->position }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Assist Select (Optional) -->
                <div class="space-y-1">
                    <label class="block text-text-muted uppercase">Penyumbang Assist (Opsional)</label>
                    <!-- Home Players -->
                    <select name="assist_player_id" :disabled="goalTeamId != '{{ $match->home_team_id }}'" x-show="goalTeamId == '{{ $match->home_team_id }}'" class="w-full py-2 px-3 rounded bg-court-navy border border-court-border text-text-primary focus:border-stadium-emerald font-sans">
                        <option value="">-- Tanpa Assist / Gol Solo --</option>
                        @foreach($match->homeTeam->players as $p)
                            <option value="{{ $p->id }}">#{{ $p->jersey_number }} - {{ $p->name }}</option>
                        @endforeach
                    </select>
                    <!-- Away Players -->
                    <select name="assist_player_id" :disabled="goalTeamId != '{{ $match->away_team_id }}'" x-show="goalTeamId == '{{ $match->away_team_id }}'" class="w-full py-2 px-3 rounded bg-court-navy border border-court-border text-text-primary focus:border-stadium-emerald font-sans">
                        <option value="">-- Tanpa Assist / Gol Solo --</option>
                        @foreach($match->awayTeam->players as $p)
                            <option value="{{ $p->id }}">#{{ $p->jersey_number }} - {{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Minute -->
                <div class="space-y-1">
                    <div class="flex items-center justify-between">
                        <label class="block text-text-muted uppercase">Menit Kejadian</label>
                        <span class="text-[10px] text-stadium-emerald font-bold font-mono">Maks: Menit ke-<span x-text="currentMinute"></span></span>
                    </div>
                    <input type="number" name="minute" min="1" :max="currentMinute" :value="currentMinute"
                           @input="if (parseInt($el.value) > currentMinute) $el.value = currentMinute; if (parseInt($el.value) < 1 && $el.value !== '') $el.value = 1;"
                           required
                           class="w-full py-2 px-3 rounded bg-court-navy border border-court-border text-text-primary focus:border-stadium-emerald font-mono font-bold">
                    <p class="text-[10px] text-text-muted">Terkunci maks. menit ke-<span class="text-stadium-emerald font-bold" x-text="currentMinute"></span> (sesuai waktu berjalan)</p>
                </div>

                <!-- Notes -->
                <div class="space-y-1">
                    <label class="block text-text-muted uppercase">Keterangan Singkat</label>
                    <input type="text" name="notes" placeholder="Tembakan mendatar / voli" class="w-full py-2 px-3 rounded bg-court-navy border border-court-border text-text-primary focus:border-stadium-emerald font-sans">
                </div>

                <div class="pt-4 flex items-center justify-end gap-3 border-t border-court-border/60">
                    <button type="button" @click="goalModal = false" class="px-4 py-2 rounded bg-court-navy hover:bg-court-border text-text-muted font-bold transition-colors">
                        BATAL
                    </button>
                    <button type="submit" class="px-5 py-2 rounded bg-stadium-emerald hover:bg-stadium-emerald/90 text-court-navy font-headline font-bold uppercase transition-all shadow-[0_0_15px_rgba(0,255,135,0.3)]">
                        SIMPAN GOL (+1 SKOR)
                    </button>
                </div>
            </form>
        </div>
    </div>
    </template>

    <!-- MODAL 2: CATAT KARTU KUNING / MERAH -->
    <template x-teleport="body">
    <div x-show="cardModal" x-cloak class="fixed inset-0 z-[100] bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-court-surface rounded-xl border border-court-border max-w-md w-full p-6 space-y-4 shadow-2xl">
            <div class="flex items-center justify-between pb-3 border-b border-court-border">
                <h3 class="font-headline font-bold text-base text-card-yellow uppercase flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">warning</span> Catat Pelanggaran & Kartu
                </h3>
                <button @click="cardModal = false" class="text-text-muted hover:text-text-primary">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form action="{{ route('admin.matches.events.store', $match->id) }}" method="POST" class="space-y-4 text-xs font-mono">
                @csrf

                <!-- Card Type Selector -->
                <div class="space-y-1">
                    <label class="block text-text-muted uppercase">Jenis Kartu</label>
                    <select name="event_type" required class="w-full py-2 px-3 rounded bg-court-navy border border-court-border text-text-primary focus:border-stadium-emerald">
                        <option value="yellow_card">🟨 Kartu Kuning (Peringatan)</option>
                        <option value="red_card">🟥 Kartu Merah Langsung (Ejection)</option>
                        <option value="second_yellow">🟥 Kartu Kuning Kedua (Kartu Merah)</option>
                    </select>
                </div>

                <!-- Team Selector -->
                <div class="space-y-1">
                    <label class="block text-text-muted uppercase">Pilih Tim Pemain</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="p-3 rounded-lg border cursor-pointer text-center"
                               :class="cardTeamId == '{{ $match->home_team_id }}' ? 'bg-stadium-emerald/10 border-stadium-emerald text-stadium-emerald font-bold' : 'bg-court-navy border-court-border text-text-muted'">
                            <input type="radio" name="team_id" value="{{ $match->home_team_id }}" x-model="cardTeamId" class="hidden">
                            <span>{{ $match->homeTeam->name }}</span>
                        </label>
                        <label class="p-3 rounded-lg border cursor-pointer text-center"
                               :class="cardTeamId == '{{ $match->away_team_id }}' ? 'bg-telemetry-cyan/10 border-telemetry-cyan text-telemetry-cyan font-bold' : 'bg-court-navy border-court-border text-text-muted'">
                            <input type="radio" name="team_id" value="{{ $match->away_team_id }}" x-model="cardTeamId" class="hidden">
                            <span>{{ $match->awayTeam->name }}</span>
                        </label>
                    </div>
                </div>

                <!-- Player Select -->
                <div class="space-y-1">
                    <label class="block text-text-muted uppercase">Pemain yang Diberi Kartu</label>
                    <select name="player_id" :disabled="cardTeamId != '{{ $match->home_team_id }}'" :required="cardTeamId == '{{ $match->home_team_id }}'" x-show="cardTeamId == '{{ $match->home_team_id }}'" class="w-full py-2 px-3 rounded bg-court-navy border border-court-border text-text-primary focus:border-stadium-emerald font-sans">
                        <option value="">-- Pilih Pemain {{ $match->homeTeam->name }} --</option>
                        @foreach($match->homeTeam->players as $p)
                            <option value="{{ $p->id }}">#{{ $p->jersey_number }} - {{ $p->name }} ({{ $p->position }})</option>
                        @endforeach
                    </select>
                    <select name="player_id" :disabled="cardTeamId != '{{ $match->away_team_id }}'" :required="cardTeamId == '{{ $match->away_team_id }}'" x-show="cardTeamId == '{{ $match->away_team_id }}'" class="w-full py-2 px-3 rounded bg-court-navy border border-court-border text-text-primary focus:border-stadium-emerald font-sans">
                        <option value="">-- Pilih Pemain {{ $match->awayTeam->name }} --</option>
                        @foreach($match->awayTeam->players as $p)
                            <option value="{{ $p->id }}">#{{ $p->jersey_number }} - {{ $p->name }} ({{ $p->position }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Minute -->
                <div class="space-y-1">
                    <div class="flex items-center justify-between">
                        <label class="block text-text-muted uppercase">Menit Kejadian</label>
                        <span class="text-[10px] text-card-yellow font-bold font-mono">Maks: Menit ke-<span x-text="currentMinute"></span></span>
                    </div>
                    <input type="number" name="minute" min="1" :max="currentMinute" :value="currentMinute"
                           @input="if (parseInt($el.value) > currentMinute) $el.value = currentMinute; if (parseInt($el.value) < 1 && $el.value !== '') $el.value = 1;"
                           required
                           class="w-full py-2 px-3 rounded bg-court-navy border border-court-border text-text-primary focus:border-stadium-emerald font-mono font-bold">
                    <p class="text-[10px] text-text-muted">Terkunci maks. menit ke-<span class="text-card-yellow font-bold" x-text="currentMinute"></span> (sesuai waktu berjalan)</p>
                </div>

                <!-- Notes -->
                <div class="space-y-1">
                    <label class="block text-text-muted uppercase">Alasan Pelanggaran</label>
                    <input type="text" name="notes" placeholder="Tarikan baju / tekel keras" class="w-full py-2 px-3 rounded bg-court-navy border border-court-border text-text-primary focus:border-stadium-emerald font-sans">
                </div>

                <div class="pt-4 flex items-center justify-end gap-3 border-t border-court-border/60">
                    <button type="button" @click="cardModal = false" class="px-4 py-2 rounded bg-court-navy hover:bg-court-border text-text-muted font-bold transition-colors">
                        BATAL
                    </button>
                    <button type="submit" class="px-5 py-2 rounded bg-card-yellow hover:bg-card-yellow/90 text-court-navy font-headline font-bold uppercase transition-all shadow-[0_0_15px_rgba(255,214,0,0.3)]">
                        CATAT KARTU
                    </button>
                </div>
            </form>
        </div>
    </div>
    </template>

    <!-- MODAL 3: GOL BUNUH DIRI -->
    <template x-teleport="body">
    <div x-show="ownGoalModal" x-cloak class="fixed inset-0 z-[100] bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-court-surface rounded-xl border border-court-border max-w-md w-full p-6 space-y-4 shadow-2xl">
            <div class="flex items-center justify-between pb-3 border-b border-court-border">
                <h3 class="font-headline font-bold text-base text-card-red uppercase flex items-center gap-2">
                    <span>🔄</span> Catat Gol Bunuh Diri (Own Goal)
                </h3>
                <button @click="ownGoalModal = false" class="text-text-muted hover:text-text-primary">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form action="{{ route('admin.matches.events.store', $match->id) }}" method="POST" class="space-y-4 text-xs font-mono">
                @csrf
                <input type="hidden" name="event_type" value="own_goal">

                <div class="space-y-1">
                    <label class="block text-text-muted uppercase">Tim yang Melakukan Gol Bunuh Diri</label>
                    <p class="text-[11px] text-text-muted pb-1">Skor akan otomatis ditambahkan kepada tim lawan.</p>
                    <select name="team_id" required class="w-full py-2 px-3 rounded bg-court-navy border border-court-border text-text-primary focus:border-stadium-emerald">
                        <option value="{{ $match->home_team_id }}">{{ $match->homeTeam->name }} (Poin untuk {{ $match->awayTeam->name }})</option>
                        <option value="{{ $match->away_team_id }}">{{ $match->awayTeam->name }} (Poin untuk {{ $match->homeTeam->name }})</option>
                    </select>
                </div>

                <!-- Minute -->
                <div class="space-y-1">
                    <div class="flex items-center justify-between">
                        <label class="block text-text-muted uppercase">Menit Kejadian</label>
                        <span class="text-[10px] text-card-red font-bold font-mono">Maks: Menit ke-<span x-text="currentMinute"></span></span>
                    </div>
                    <input type="number" name="minute" min="1" :max="currentMinute" :value="currentMinute"
                           @input="if (parseInt($el.value) > currentMinute) $el.value = currentMinute; if (parseInt($el.value) < 1 && $el.value !== '') $el.value = 1;"
                           required
                           class="w-full py-2 px-3 rounded bg-court-navy border border-court-border text-text-primary focus:border-stadium-emerald font-mono font-bold">
                    <p class="text-[10px] text-text-muted">Terkunci maks. menit ke-<span class="text-card-red font-bold" x-text="currentMinute"></span> (sesuai waktu berjalan)</p>
                </div>

                <!-- Notes -->
                <div class="space-y-1">
                    <label class="block text-text-muted uppercase">Catatan</label>
                    <input type="text" name="notes" placeholder="Defleksi bola tendangan lawan" class="w-full py-2 px-3 rounded bg-court-navy border border-court-border text-text-primary focus:border-stadium-emerald font-sans">
                </div>

                <div class="pt-4 flex items-center justify-end gap-3 border-t border-court-border/60">
                    <button type="button" @click="ownGoalModal = false" class="px-4 py-2 rounded bg-court-navy hover:bg-court-border text-text-muted font-bold transition-colors">
                        BATAL
                    </button>
                    <button type="submit" class="px-5 py-2 rounded bg-card-red hover:bg-card-red/90 text-white font-headline font-bold uppercase transition-all shadow-[0_0_15px_rgba(255,42,77,0.3)]">
                        CATAT OWN GOAL
                    </button>
                </div>
            </form>
        </div>
    </div>
    </template>

    <!-- MODAL 4: MANUAL SCORE OVERRIDE -->
    <template x-teleport="body">
    <div x-show="scoreModal" x-cloak class="fixed inset-0 z-[100] bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-court-surface rounded-xl border border-court-border max-w-sm w-full p-6 space-y-4 shadow-2xl">
            <div class="flex items-center justify-between pb-3 border-b border-court-border">
                <h3 class="font-headline font-bold text-base text-text-primary uppercase">Koreksi Skor Manual</h3>
                <button @click="scoreModal = false" class="text-text-muted hover:text-text-primary">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form action="{{ route('admin.matches.score', $match->id) }}" method="POST" class="space-y-4 text-xs font-mono">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1 text-center">
                        <label class="block text-text-muted truncate uppercase">{{ $match->homeTeam->name }}</label>
                        <input type="number" name="home_score" value="{{ $match->home_score }}" min="0" required class="w-full py-2.5 text-center font-headline font-black text-2xl bg-court-navy rounded border border-court-border text-stadium-emerald focus:border-stadium-emerald">
                    </div>
                    <div class="space-y-1 text-center">
                        <label class="block text-text-muted truncate uppercase">{{ $match->awayTeam->name }}</label>
                        <input type="number" name="away_score" value="{{ $match->away_score }}" min="0" required class="w-full py-2.5 text-center font-headline font-black text-2xl bg-court-navy rounded border border-court-border text-telemetry-cyan focus:border-telemetry-cyan">
                    </div>
                </div>

                <div class="pt-4 flex items-center justify-end gap-3 border-t border-court-border/60">
                    <button type="button" @click="scoreModal = false" class="px-4 py-2 rounded bg-court-navy hover:bg-court-border text-text-muted font-bold transition-colors">
                        BATAL
                    </button>
                    <button type="submit" class="px-5 py-2 rounded bg-stadium-emerald hover:bg-stadium-emerald/90 text-court-navy font-headline font-bold uppercase transition-all">
                        SIMPAN SKOR
                    </button>
                </div>
            </form>
        </div>
    </div>
    </template>

</div>
@endsection
