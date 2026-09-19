@extends('layouts.app')

@section('title', 'Master Jadwal & Fixture Pertandingan - LDII CUP TANJUNG PINANG')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8" 
         x-data="{ 
             createModal: false, 
             editModal: false, 
             editData: {}, 
             editUrl: '', 
             selectedVenueId: '{{ $venues->first()?->id ?? '' }}',
             venueInput: '{{ $venues->first() ? ($venues->first()->court_name ? $venues->first()->name . ' (' . $venues->first()->court_name . ')' : $venues->first()->name) : '' }}',
             venueMap: {{ $venues->mapWithKeys(fn($v) => [(string)$v->id => $v->court_name ? $v->name . ' (' . $v->court_name . ')' : $v->name])->toJson() }},
             onVenueChange(val) {
                 if (val && this.venueMap[val]) {
                     this.venueInput = this.venueMap[val];
                 } else if (val === 'custom') {
                     this.venueInput = '';
                 }
             },
             onEditVenueChange(val) {
                 if (val && this.venueMap[val]) {
                     this.editData.venue = this.venueMap[val];
                 } else if (val === 'custom') {
                     this.editData.venue = '';
                 }
             }
         }">

        <!-- Header Section -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-court-border">
            <div>
                <h1
                    class="text-2xl font-headline font-bold uppercase tracking-wider text-text-primary flex items-center gap-2">
                    <span class="material-symbols-outlined text-stadium-emerald text-2xl">event_available</span>
                    Master Jadwal & Fixture Pertandingan
                </h1>
                <p class="text-xs font-mono text-text-muted">PENJADWALAN LAGA, PENGATURAN VENUE & KONTROL WASIT LAPANGAN</p>
            </div>

            <div>
                @if (auth()->user()->isAdmin())
                    <button @click="createModal = true"
                        class="px-4 py-2.5 rounded text-xs font-headline font-bold bg-stadium-emerald hover:bg-stadium-emerald/90 text-court-navy uppercase tracking-wider transition-all shadow-[0_0_15px_rgba(0,255,135,0.25)] flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm">add_circle</span>
                        BUAT JADWAL LAGA BARU
                    </button>
                @else
                    <div
                        class="px-3.5 py-2 rounded-lg bg-card-yellow/10 border border-card-yellow/30 text-card-yellow text-xs font-mono flex items-center gap-2">
                        <span class="material-symbols-outlined text-base">sports</span>
                        <span>MODE WASIT MEJA (KONTROL PERTANDINGAN)</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Filter Bar -->
        <div
            class="p-4 rounded-xl bg-court-surface border border-court-border flex flex-wrap items-center justify-between gap-4">
            <form method="GET" action="{{ route('admin.fixtures') }}"
                class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
                <!-- Category Filter -->
                <select name="category_id" onchange="this.form.submit()"
                    class="py-1.5 px-3 rounded text-xs font-mono bg-court-navy border border-court-border text-text-primary focus:border-stadium-emerald">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $c)
                        <option value="{{ $c->id }}" {{ $selectedCategoryId == $c->id ? 'selected' : '' }}>
                            {{ $c->name }}</option>
                    @endforeach
                </select>

                <!-- Status Filter -->
                <select name="status" onchange="this.form.submit()"
                    class="py-1.5 px-3 rounded text-xs font-mono bg-court-navy border border-court-border text-text-primary focus:border-stadium-emerald">
                    <option value="">Semua Status</option>
                    <option value="scheduled" {{ $selectedStatus === 'scheduled' ? 'selected' : '' }}>Terjadwal</option>
                    <option value="first_half" {{ $selectedStatus === 'first_half' ? 'selected' : '' }}>Babak 1 (Live)
                    </option>
                    <option value="second_half" {{ $selectedStatus === 'second_half' ? 'selected' : '' }}>Babak 2 (Live)
                    </option>
                    <option value="finished" {{ $selectedStatus === 'finished' ? 'selected' : '' }}>Selesai</option>
                </select>

                @if ($selectedCategoryId || $selectedStatus)
                    <a href="{{ route('admin.fixtures') }}" class="text-xs font-mono text-text-muted hover:text-card-red">
                        Reset Filter
                    </a>
                @endif
            </form>

            <div class="text-xs font-mono text-text-muted">
                Total Laga: <strong class="text-stadium-emerald">{{ $matches->count() }} Pertandingan</strong>
            </div>
        </div>

        <!-- Fixtures Cards Container -->
        <div class="space-y-4">
            @forelse($matches as $m)
                <div
                    class="p-5 rounded-xl bg-court-surface border border-court-border hover:border-court-border/80 transition-all">
                    <div
                        class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 pb-3 mb-3 border-b border-court-border/50 text-xs font-mono">
                        <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                            <span class="px-2.5 py-0.5 rounded font-bold border {{ $m->status_badge['color'] }}">
                                {{ $m->status_badge['text'] }}
                            </span>
                            <span
                                class="px-2 py-0.5 rounded bg-court-navy text-text-muted border border-court-border text-[11px] flex items-center gap-1">
                                <span class="material-symbols-outlined text-[13px] text-stadium-emerald">timer</span>
                                2 x {{ $m->half_duration_minutes }}' (ET: {{ $m->extra_time_duration_minutes }}')
                            </span>
                            <span class="text-text-muted">
                                {{ $m->match_date->format('d M Y - H:i') }} WIB &bull; {{ $m->venue }}
                            </span>
                        </div>

                        <!-- Info Wasit & Kategori (Rata Kanan) -->
                        <div class="flex flex-col sm:items-end items-start gap-1.5 text-left sm:text-right sm:ml-auto">
                            <div class="flex flex-wrap items-center justify-start sm:justify-end gap-2">
                                @if ($m->operators->isNotEmpty())
                                    <span
                                        class="px-2 py-0.5 rounded bg-court-navy text-stadium-emerald border border-stadium-emerald/30 text-[11px] inline-flex items-center gap-1"
                                        title="Operator Wasit Meja yang Ditugaskan">
                                        <span class="material-symbols-outlined text-[13px]">person_check</span>
                                        Wasit Meja: {{ $m->operators->pluck('name')->join(', ') }}
                                    </span>
                                @else
                                    <span
                                        class="px-2 py-0.5 rounded bg-court-navy text-text-muted/60 border border-court-border text-[11px] inline-flex items-center gap-1"
                                        title="Belum ditugaskan ke operator khusus">
                                        <span class="material-symbols-outlined text-[13px]">person_off</span>
                                        Wasit Meja: Terbuka
                                    </span>
                                @endif

                                @if ($m->referee1 || $m->referee2 || $m->referee3)
                                    <span
                                        class="px-2 py-0.5 rounded bg-court-navy text-telemetry-cyan border border-telemetry-cyan/30 text-[11px] inline-flex items-center gap-1"
                                        title="Wasit di Lapangan (Pitch Officials)">
                                        <span class="material-symbols-outlined text-[13px]">sports</span>
                                        Wasit Lapangan: {{ $m->referee1?->name ?? '-' }}{{ $m->referee2 ? ', ' . $m->referee2->name : '' }}{{ $m->referee3 ? ' (Cadangan: ' . $m->referee3->name . ')' : '' }}
                                    </span>
                                @endif
                            </div>

                            <div class="text-telemetry-cyan uppercase text-[11px] text-left sm:text-right">
                                {{ $m->category->name }} &bull; {{ $m->stage?->name }}
                                {{ $m->group ? '(' . $m->group->name . ')' : '' }}
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-12 items-center gap-4 py-2">
                        <!-- Home Team -->
                        <div class="md:col-span-4 flex items-center gap-3">
                            <div
                                class="w-10 h-10 rounded-lg bg-court-navy border border-court-border flex items-center justify-center font-headline font-bold text-sm text-stadium-emerald flex-shrink-0 overflow-hidden p-1">
                                @if ($m->homeTeam->logo)
                                    <img src="{{ $m->homeTeam->logo_url }}" alt="{{ $m->homeTeam->name }}" class="w-full h-full object-contain">
                                @else
                                    {{ $m->homeTeam->initials }}
                                @endif
                            </div>
                            <div>
                                <h3 class="font-headline font-bold text-base text-text-primary">{{ $m->homeTeam->name }}
                                </h3>
                                <span class="text-[10px] font-mono text-text-muted">KANDANG (HOME)</span>
                            </div>
                        </div>

                        <!-- Score / Time center -->
                        <div
                            class="md:col-span-4 text-center py-2 bg-court-navy/60 rounded-lg border border-court-border/40">
                            @if ($m->status === 'scheduled')
                                <div class="font-mono text-sm font-bold text-text-muted">BELUM DIMULAI</div>
                                <span
                                    class="text-[11px] font-mono text-stadium-emerald">{{ $m->match_date->diffForHumans() }}</span>
                            @else
                                <div class="font-headline font-black text-2xl tracking-wider">
                                    <span
                                        class="{{ $m->isLive() ? 'text-stadium-emerald' : 'text-text-primary' }}">{{ $m->home_score }}</span>
                                    <span class="text-court-border">-</span>
                                    <span
                                        class="{{ $m->isLive() ? 'text-stadium-emerald' : 'text-text-primary' }}">{{ $m->away_score }}</span>
                                </div>
                                <span class="text-[10px] font-mono text-text-muted">
                                    {{ $m->isLive() ? ($m->status === 'penalty_shootout' ? 'Adu Penalti' : "Menit ke-{$m->current_minute}'") : 'Hasil Akhir' }}
                                </span>
                                @if ($m->has_penalty)
                                    <div class="mt-1">
                                        <span class="px-2 py-0.5 rounded bg-card-yellow/20 text-card-yellow border border-card-yellow/30 font-bold text-[10px]">
                                            Adu Penalti: {{ $m->home_penalty_score }} - {{ $m->away_penalty_score }}
                                        </span>
                                    </div>
                                @endif
                            @endif
                        </div>

                        <!-- Away Team -->
                        <div
                            class="md:col-span-4 flex items-center justify-start md:justify-end gap-3 text-left md:text-right">
                            <div>
                                <h3 class="font-headline font-bold text-base text-text-primary">{{ $m->awayTeam->name }}
                                </h3>
                                <span class="text-[10px] font-mono text-text-muted">TANDANG (AWAY)</span>
                            </div>
                            <div
                                class="w-10 h-10 rounded-lg bg-court-navy border border-court-border flex items-center justify-center font-headline font-bold text-sm text-telemetry-cyan flex-shrink-0 overflow-hidden p-1">
                                @if ($m->awayTeam->logo)
                                    <img src="{{ $m->awayTeam->logo_url }}" alt="{{ $m->awayTeam->name }}" class="w-full h-full object-contain">
                                @else
                                    {{ $m->awayTeam->initials }}
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Action Toolbar -->
                    <div
                        class="mt-4 pt-3 border-t border-court-border/40 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs font-mono">
                        <div class="flex items-center gap-3">
                            <a href="{{ route('matches.show', $m->id) }}"
                                class="text-text-muted hover:text-text-primary flex items-center gap-1">
                                <span class="material-symbols-outlined text-sm">visibility</span>
                                Pratinjau Fan Center
                            </a>
                        </div>

                        <div class="flex items-center gap-2">
                            @if (auth()->user()->isAdmin())
                                <!-- EDIT JADWAL / VENUE BUTTON (ADMIN ONLY) -->
                                <button type="button"
                                    @click="editData = {
                                id: {{ $m->id }},
                                venue_id: '{{ $m->venue_id ?? '' }}',
                                venue: '{{ addslashes($m->venue) }}',
                                match_date: '{{ $m->match_date->format('Y-m-d\TH:i') }}',
                                stage_id: '{{ $m->stage_id }}',
                                group_id: '{{ $m->group_id }}',
                                home_team_id: '{{ $m->home_team_id }}',
                                away_team_id: '{{ $m->away_team_id }}',
                                status: '{{ $m->status }}',
                                half_duration_minutes: {{ $m->half_duration_minutes ?? 20 }},
                                extra_time_duration_minutes: {{ $m->extra_time_duration_minutes ?? 5 }},
                                referee_1_id: '{{ $m->referee_1_id ?? '' }}',
                                referee_2_id: '{{ $m->referee_2_id ?? '' }}',
                                referee_3_id: '{{ $m->referee_3_id ?? '' }}',
                                operator_ids: {{ json_encode($m->operators->pluck('id')->toArray()) }}
                            }; if (editData.venue_id && venueMap[editData.venue_id]) { editData.venue = venueMap[editData.venue_id]; } editUrl = '{{ route('admin.fixtures.update', $m->id) }}'; editModal = true"
                                    class="px-3 py-1.5 rounded font-headline font-bold text-xs uppercase tracking-wider bg-court-navy hover:bg-court-surface-elevated text-text-muted hover:text-telemetry-cyan border border-court-border transition-colors flex items-center gap-1">
                                    <span class="material-symbols-outlined text-sm">edit_calendar</span>
                                    Edit / Venue
                                </button>
                            @endif

                            <!-- LAUNCH CONTROL ROOM BUTTON (ADMIN & ASSIGNED OPERATOR) -->
                            <a href="{{ route('admin.matches.control', $m->id) }}"
                                class="px-3.5 py-1.5 rounded font-headline font-bold uppercase tracking-wider bg-stadium-emerald/10 hover:bg-stadium-emerald text-stadium-emerald hover:text-court-navy border border-stadium-emerald/40 transition-all flex items-center gap-1.5 shadow-sm">
                                <span class="material-symbols-outlined text-sm">tune</span>
                                Buka Control Room Wasit Meja
                            </a>

                            @if (auth()->user()->isAdmin())
                                <!-- DELETE FIXTURE (ADMIN ONLY) -->
                                <form action="{{ route('admin.fixtures.destroy', $m->id) }}" method="POST"
                                    onsubmit="return confirm('Hapus jadwal pertandingan ini?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Hapus Laga"
                                        class="p-1.5 rounded bg-court-navy hover:bg-card-red/20 text-text-muted hover:text-card-red border border-court-border transition-colors">
                                        <span class="material-symbols-outlined text-sm">delete</span>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div
                    class="p-12 text-center text-text-muted font-mono text-xs bg-court-surface rounded-xl border border-court-border">
                    Tidak ada data pertandingan sesuai filter. Silakan klik "BUAT JADWAL LAGA BARU".
                </div>
            @endforelse
        </div>

        @if (auth()->user()->isAdmin())
            <!-- MODAL CREATE FIXTURE (WIDE LAYOUT MAX-W-4XL) -->
            <template x-teleport="body">
            <div x-show="createModal" x-cloak
                class="fixed inset-0 z-[100] bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
                <div
                    class="bg-court-surface rounded-2xl border border-court-border max-w-4xl w-full p-6 sm:p-8 space-y-6 shadow-2xl max-h-[90vh] overflow-y-auto">
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between pb-4 border-b border-court-border">
                        <div>
                            <h3
                                class="font-headline font-bold text-xl text-stadium-emerald uppercase flex items-center gap-2">
                                <span class="material-symbols-outlined text-2xl">calendar_add_on</span>
                                Buat Jadwal Pertandingan Baru
                            </h3>
                            <p class="text-xs font-mono text-text-muted mt-0.5">PENJADWALAN LAGA LENGKAP &bull; PILIH TIM,
                                WAKTU & MASTER VENUE LAPANGAN</p>
                        </div>
                        <button @click="createModal = false"
                            class="text-text-muted hover:text-text-primary p-1.5 rounded-lg hover:bg-court-surface-elevated transition-colors">
                            <span class="material-symbols-outlined text-xl">close</span>
                        </button>
                    </div>

                    <form action="{{ route('admin.fixtures.store') }}" method="POST" class="space-y-6 text-xs font-mono">
                        @csrf

                        <!-- Row-by-Row Form Sections -->
                        <div class="space-y-5">

                            <!-- ROW 1: INFORMASI TURNAMEN & WAKTU -->
                            <div class="p-5 rounded-xl bg-court-surface-elevated/50 border border-court-border space-y-4">
                                <div
                                    class="text-xs font-headline font-bold uppercase tracking-wider text-stadium-emerald flex items-center gap-1.5 pb-2 border-b border-court-border/40">
                                    <span class="material-symbols-outlined text-base">emoji_events</span>
                                    1. Informasi Turnamen & Waktu Kick-Off
                                </div>

                                <!-- Sub-row 1: Kategori, Babak, Grup -->
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <!-- Category -->
                                    <div class="space-y-1.5">
                                        <label class="block text-text-muted uppercase font-bold">Kategori Turnamen</label>
                                        <select name="category_id" required
                                            class="w-full py-2.5 px-3 rounded-lg bg-court-navy border border-court-border text-text-primary focus:border-stadium-emerald font-sans">
                                            @foreach ($categories as $cat)
                                                <option value="{{ $cat->id }}"
                                                    {{ $selectedCategoryId == $cat->id ? 'selected' : '' }}>
                                                    {{ $cat->name }} ({{ $cat->teams->count() }} Tim)
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Stage -->
                                    <div class="space-y-1.5">
                                        <label class="block text-text-muted uppercase font-bold">Babak / Tahapan</label>
                                        <select name="stage_id"
                                            class="w-full py-2.5 px-3 rounded-lg bg-court-navy border border-court-border text-text-primary focus:border-stadium-emerald font-sans">
                                            <option value="">Babak Umum</option>
                                            @foreach ($stages as $stg)
                                                <option value="{{ $stg->id }}">{{ $stg->name }} [{{ $stg->type === 'knockout' ? 'Knockout' : 'Grup' }}]</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Group -->
                                    <div class="space-y-1.5">
                                        <label class="block text-text-muted uppercase font-bold">Grup (Opsional)</label>
                                        <select name="group_id"
                                            class="w-full py-2.5 px-3 rounded-lg bg-court-navy border border-court-border text-text-primary focus:border-stadium-emerald font-sans">
                                            <option value="">Tanpa Grup / 1 Grup</option>
                                            @foreach ($groups as $grp)
                                                <option value="{{ $grp->id }}">{{ $grp->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <!-- Sub-row 2: Waktu Kick-off, Durasi Babak & Extra Time -->
                                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 pt-3 border-t border-court-border/40">
                                    <div class="md:col-span-6 space-y-1.5">
                                        <label class="block text-text-muted uppercase font-bold">Tanggal & Jam Kick-off (WIB)</label>
                                        <input type="datetime-local" name="match_date" required
                                            value="{{ now()->addHours(2)->format('Y-m-d\TH:i') }}"
                                            class="w-full py-2.5 px-3 rounded-lg bg-court-navy border border-court-border text-text-primary focus:border-stadium-emerald font-sans">
                                        <span class="text-[10px] text-text-muted">Waktu kick-off otomatis disinkronkan ke zona WIB</span>
                                    </div>

                                    <div class="md:col-span-3 space-y-1.5">
                                        <label class="block text-text-muted uppercase font-bold flex items-center gap-1">
                                            <span class="material-symbols-outlined text-sm text-stadium-emerald">timer</span>
                                            Babak (Menit)
                                        </label>
                                        <input type="number" name="half_duration_minutes" value="20" min="1" max="60" required
                                            class="w-full py-2 px-3 rounded-lg bg-court-navy border border-court-border text-text-primary focus:border-stadium-emerald font-sans">
                                        <span class="text-[10px] text-text-muted">Durasi 1 babak</span>
                                    </div>

                                    <div class="md:col-span-3 space-y-1.5">
                                        <label class="block text-text-muted uppercase font-bold flex items-center gap-1">
                                            <span class="material-symbols-outlined text-sm text-card-yellow">more_time</span>
                                            Extra Time (Menit)
                                        </label>
                                        <input type="number" name="extra_time_duration_minutes" value="5" min="1" max="30" required
                                            class="w-full py-2 px-3 rounded-lg bg-court-navy border border-court-border text-text-primary focus:border-stadium-emerald font-sans">
                                        <span class="text-[10px] text-text-muted">Babak tambahan</span>
                                    </div>
                                </div>
                            </div>

                            <!-- ROW 2: TIM BERTANDING & LOKASI VENUE -->
                            <div class="p-5 rounded-xl bg-court-surface-elevated/50 border border-court-border space-y-4">
                                <div
                                    class="text-xs font-headline font-bold uppercase tracking-wider text-telemetry-cyan flex items-center justify-between pb-2 border-b border-court-border/40">
                                    <div class="flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-base">stadium</span>
                                        2. Tim Bertanding & Lokasi Venue Lapangan
                                    </div>
                                    <a href="{{ route('admin.dashboard', ['tab' => 'venues']) }}" target="_blank"
                                        class="text-[11px] font-mono text-stadium-emerald hover:underline flex items-center gap-1">
                                        <span class="material-symbols-outlined text-xs">add_location_alt</span>
                                        Master Venue
                                    </a>
                                </div>

                                <!-- Sub-row 1: Tim Kandang & Tim Tandang -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="space-y-1.5">
                                        <label class="block text-stadium-emerald uppercase font-bold flex items-center gap-1">
                                            <span class="w-2 h-2 rounded-full bg-stadium-emerald"></span> Tim Kandang (Home)
                                        </label>
                                        <select name="home_team_id" required
                                            class="w-full py-2.5 px-3 rounded-lg bg-court-navy border border-court-border text-text-primary focus:border-stadium-emerald font-sans">
                                            <option value="">-- Pilih Tim Kandang --</option>
                                            @foreach ($teams as $t)
                                                <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->code ?? '-' }})</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="space-y-1.5">
                                        <label class="block text-telemetry-cyan uppercase font-bold flex items-center gap-1">
                                            <span class="w-2 h-2 rounded-full bg-telemetry-cyan"></span> Tim Tandang (Away)
                                        </label>
                                        <select name="away_team_id" required
                                            class="w-full py-2.5 px-3 rounded-lg bg-court-navy border border-court-border text-text-primary focus:border-stadium-emerald font-sans">
                                            <option value="">-- Pilih Tim Tandang --</option>
                                            @foreach ($teams as $idx => $t)
                                                <option value="{{ $t->id }}" {{ $idx === 1 ? 'selected' : '' }}>
                                                    {{ $t->name }} ({{ $t->code ?? '-' }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <!-- Sub-row 2: Venue Lapangan & Kustom -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-3 border-t border-court-border/40">
                                    <div class="space-y-1.5">
                                        <label class="block text-text-muted uppercase font-bold">Pilih dari Master Venue Lapangan</label>
                                        <select name="venue_id" x-model="selectedVenueId" @change="onVenueChange($event.target.value)"
                                            class="w-full py-2.5 px-3 rounded-lg bg-court-navy border border-court-border text-text-primary focus:border-stadium-emerald font-sans">
                                            <option value="">-- Pilih dari Master Venue Lapangan --</option>
                                            @foreach ($venues as $v)
                                                <option value="{{ $v->id }}">
                                                    {{ $v->name }} {{ $v->court_name ? '(' . $v->court_name . ')' : '' }} &bull; {{ $v->city ?? 'Arena' }}
                                                </option>
                                            @endforeach
                                            <option value="custom">✏️ Tulis Nama Venue Kustom / Manual...</option>
                                        </select>
                                        <span class="text-[10px] text-text-muted">Pilih lapangan resmi yang sudah terdaftar</span>
                                    </div>

                                    <div class="space-y-1.5">
                                        <label class="block text-text-muted uppercase font-bold">Nama Venue / Lapangan</label>
                                        <input type="text" name="venue" x-model="venueInput"
                                            :readonly="!!(selectedVenueId && venueMap[selectedVenueId])"
                                            :class="selectedVenueId && venueMap[selectedVenueId] ? 'bg-court-surface-elevated/70 text-text-primary border-court-border cursor-not-allowed' : 'bg-court-navy border-court-border text-text-primary focus:border-stadium-emerald'"
                                            placeholder="Ketik nama venue/lapangan..."
                                            required
                                            class="w-full py-2.5 px-3 rounded-lg border text-sm font-sans transition-all">
                                        <span class="text-[10px] text-text-muted" x-text="selectedVenueId && venueMap[selectedVenueId] ? 'Terkunci otomatis (readonly) sesuai Master Venue yang dipilih' : 'Ketik nama venue kustom jika memilih manual'">
                                            Terkunci otomatis (readonly) sesuai Master Venue yang dipilih
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- ROW 3: PENUGASAN WASIT LAPANGAN (OFFICIALS) -->
                            <div
                                class="p-5 rounded-xl bg-court-surface-elevated/50 border border-court-border space-y-3">
                                <div class="flex items-center justify-between pb-2 border-b border-court-border/40">
                                    <div
                                        class="text-xs font-headline font-bold uppercase tracking-wider text-stadium-emerald flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-base">sports</span>
                                        3. Penugasan Wasit di Lapangan (Pitch Officials)
                                    </div>
                                    <a href="{{ route('admin.dashboard', ['tab' => 'referees']) }}" target="_blank"
                                        class="text-[11px] font-mono text-stadium-emerald hover:underline flex items-center gap-1">
                                        <span class="material-symbols-outlined text-xs">person_add</span>
                                        Master Wasit Lapangan
                                    </a>
                                </div>

                                <p class="text-[11px] text-text-muted">Pilih wasit yang memimpin jalannya laga langsung di lapangan (Wasit 1, Wasit 2, dan Cadangan).</p>

                                @if ($referees->isNotEmpty())
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                        <div class="space-y-1.5">
                                            <label class="block text-text-muted uppercase font-bold text-[11px]">Wasit 1 (Utama)</label>
                                            <select name="referee_1_id"
                                                class="w-full py-2.5 px-3 rounded-lg bg-court-navy border border-court-border text-text-primary focus:border-stadium-emerald font-sans">
                                                <option value="">-- Belum Ditugaskan --</option>
                                                @foreach ($referees as $rf)
                                                    <option value="{{ $rf->id }}">{{ $rf->name }} ({{ $rf->license ?? 'Wasit' }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="block text-text-muted uppercase font-bold text-[11px]">Wasit 2 (Pendamping)</label>
                                            <select name="referee_2_id"
                                                class="w-full py-2.5 px-3 rounded-lg bg-court-navy border border-court-border text-text-primary focus:border-stadium-emerald font-sans">
                                                <option value="">-- Belum Ditugaskan --</option>
                                                @foreach ($referees as $rf)
                                                    <option value="{{ $rf->id }}">{{ $rf->name }} ({{ $rf->license ?? 'Wasit' }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="block text-text-muted uppercase font-bold text-[11px]">Wasit 3 / Cadangan (Opsional)</label>
                                            <select name="referee_3_id"
                                                class="w-full py-2.5 px-3 rounded-lg bg-court-navy border border-court-border text-text-primary focus:border-stadium-emerald font-sans">
                                                <option value="">-- Belum Ditugaskan --</option>
                                                @foreach ($referees as $rf)
                                                    <option value="{{ $rf->id }}">{{ $rf->name }} ({{ $rf->license ?? 'Wasit' }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                @else
                                    <div
                                        class="p-3 bg-card-yellow/10 border border-card-yellow/20 rounded-lg text-card-yellow text-xs flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="material-symbols-outlined text-sm">info</span>
                                            <span>Belum ada wasit lapangan terdaftar. Tambahkan di master data wasit lapangan.</span>
                                        </div>
                                        <a href="{{ route('admin.dashboard', ['tab' => 'referees']) }}" target="_blank"
                                            class="font-bold underline text-stadium-emerald">+ Tambah Wasit</a>
                                    </div>
                                @endif
                            </div>

                            <!-- ROW 4: PENUGASAN WASIT MEJA / OPERATOR -->
                            <div
                                class="p-5 rounded-xl bg-court-surface-elevated/50 border border-court-border space-y-3">
                                <div class="flex items-center justify-between pb-2 border-b border-court-border/40">
                                    <div
                                        class="text-xs font-headline font-bold uppercase tracking-wider text-card-yellow flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-base">badge</span>
                                        4. Penugasan Wasit Meja / Operator (Hak Kontrol Pertandingan)
                                    </div>
                                    <a href="{{ route('admin.dashboard', ['tab' => 'operators']) }}" target="_blank"
                                        class="text-[11px] font-mono text-stadium-emerald hover:underline flex items-center gap-1">
                                        <span class="material-symbols-outlined text-xs">group_add</span>
                                        Master Operator
                                    </a>
                                </div>

                                <p class="text-[11px] text-text-muted">Pilih wasit meja yang bertugas memimpin laga ini.
                                    Hanya operator terpilih (dan superadmin) yang berhak mengontrol skor & stopwatch.</p>

                                @if ($operators->isNotEmpty())
                                    <div
                                        class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2.5 max-h-36 overflow-y-auto p-2.5 bg-court-navy/80 rounded-lg border border-court-border">
                                        @foreach ($operators as $op)
                                            <label
                                                class="flex items-center gap-2.5 p-2 rounded-lg bg-court-surface hover:bg-court-surface-elevated border border-court-border/50 cursor-pointer text-xs transition-colors">
                                                <input type="checkbox" name="operator_ids[]" value="{{ $op->id }}"
                                                    class="rounded text-stadium-emerald bg-court-navy border-court-border focus:ring-stadium-emerald">
                                                <div class="truncate">
                                                    <div class="font-bold text-text-primary truncate">{{ $op->name }}
                                                    </div>
                                                    <div class="text-[10px] font-mono text-text-muted truncate">
                                                        {{ $op->email }}</div>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                @else
                                    <div
                                        class="p-3 bg-card-yellow/10 border border-card-yellow/20 rounded-lg text-card-yellow text-xs flex items-center gap-2">
                                        <span class="material-symbols-outlined text-sm">info</span>
                                        <span>Belum ada user ber-role Operator. Tambahkan wasit meja baru di tab Master
                                            Operator.</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Footer Buttons -->
                        <div class="pt-5 border-t border-court-border/80 flex items-center justify-end gap-3">
                            <button type="button" @click="createModal = false"
                                class="px-5 py-2.5 rounded-lg bg-court-navy hover:bg-court-border text-text-muted hover:text-text-primary font-bold transition-colors">
                                BATAL
                            </button>
                            <button type="submit"
                                class="px-6 py-2.5 rounded-lg bg-stadium-emerald hover:bg-stadium-emerald/90 text-court-navy font-headline font-bold uppercase tracking-wider transition-all shadow-[0_0_15px_rgba(0,255,135,0.35)] flex items-center gap-2">
                                <span class="material-symbols-outlined text-sm">event_available</span>
                                SIMPAN JADWAL
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            </template>

            <!-- MODAL EDIT JADWAL & VENUE (WIDE LAYOUT MAX-W-4XL) -->
            <template x-teleport="body">
            <div x-show="editModal" x-cloak
                class="fixed inset-0 z-[100] bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
                <div
                    class="bg-court-surface rounded-2xl border border-court-border max-w-4xl w-full p-6 sm:p-8 space-y-6 shadow-2xl max-h-[90vh] overflow-y-auto">
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between pb-4 border-b border-court-border">
                        <div>
                            <h3
                                class="font-headline font-bold text-xl text-telemetry-cyan uppercase flex items-center gap-2">
                                <span class="material-symbols-outlined text-2xl">edit_calendar</span>
                                Edit Jadwal & Venue Pertandingan
                            </h3>
                            <p class="text-xs font-mono text-text-muted mt-0.5">PERBARUI WAKTU, DURASI BABAK, PENUGASAN
                                WASIT MEJA ATAU PINDAH LAPANGAN</p>
                        </div>
                        <button @click="editModal = false"
                            class="text-text-muted hover:text-text-primary p-1.5 rounded-lg hover:bg-court-surface-elevated transition-colors">
                            <span class="material-symbols-outlined text-xl">close</span>
                        </button>
                    </div>

                    <form :action="editUrl" method="POST" class="space-y-6 text-xs font-mono">
                        @csrf
                        @method('PUT')

                        <!-- Row-by-Row Form Sections -->
                        <div class="space-y-5">

                            <!-- ROW 1: WAKTU & STATUS & DURASI -->
                            <div class="p-5 rounded-xl bg-court-surface-elevated/50 border border-court-border space-y-4">
                                <div
                                    class="text-xs font-headline font-bold uppercase tracking-wider text-telemetry-cyan flex items-center gap-1.5 pb-2 border-b border-court-border/40">
                                    <span class="material-symbols-outlined text-base">schedule</span>
                                    1. Waktu Kick-Off, Durasi & Status Pertandingan
                                </div>

                                <!-- Sub-row 1: Tanggal/Jam & Status -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="space-y-1.5">
                                        <label class="block text-text-muted uppercase font-bold">Tanggal & Jam Tanding (WIB)</label>
                                        <input type="datetime-local" name="match_date" required :value="editData.match_date"
                                            class="w-full py-2.5 px-3 rounded-lg bg-court-navy border border-court-border text-text-primary focus:border-stadium-emerald font-sans">
                                    </div>

                                    <div class="space-y-1.5">
                                        <label class="block text-text-muted uppercase font-bold">Status Pertandingan</label>
                                        <select name="status" x-model="editData.status"
                                            class="w-full py-2.5 px-3 rounded-lg bg-court-navy border border-court-border text-text-primary focus:border-stadium-emerald font-sans">
                                            <option value="scheduled">Terjadwal (Scheduled)</option>
                                            <option value="first_half">Babak 1 (First Half - Live)</option>
                                            <option value="half_time">Istirahat Babak (Half Time)</option>
                                            <option value="second_half">Babak 2 (Second Half - Live)</option>
                                            <option value="extra_time">Babak Tambahan (Extra Time)</option>
                                            <option value="penalty_shootout">Adu Penalti (Shootout - Live)</option>
                                            <option value="finished">Selesai (Finished)</option>
                                            <option value="postponed">Ditunda (Postponed)</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Sub-row 2: Flexible Durations -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-3 border-t border-court-border/40">
                                    <div class="space-y-1.5">
                                        <label class="block text-text-muted uppercase font-bold flex items-center gap-1">
                                            <span class="material-symbols-outlined text-sm text-stadium-emerald">timer</span>
                                            Durasi 1 Babak (Menit)
                                        </label>
                                        <input type="number" name="half_duration_minutes"
                                            x-model="editData.half_duration_minutes" min="1" max="60" required
                                            class="w-full py-2.5 px-3 rounded-lg bg-court-navy border border-court-border text-text-primary focus:border-stadium-emerald font-sans">
                                        <span class="text-[10px] text-text-muted">Misal: 12' futsal / 20' normal</span>
                                    </div>

                                    <div class="space-y-1.5">
                                        <label class="block text-text-muted uppercase font-bold flex items-center gap-1">
                                            <span class="material-symbols-outlined text-sm text-card-yellow">more_time</span>
                                            Extra Time (Menit)
                                        </label>
                                        <input type="number" name="extra_time_duration_minutes"
                                            x-model="editData.extra_time_duration_minutes" min="1" max="30" required
                                            class="w-full py-2.5 px-3 rounded-lg bg-court-navy border border-court-border text-text-primary focus:border-stadium-emerald font-sans">
                                        <span class="text-[10px] text-text-muted">Babak tambahan (misal: 3' atau 5')</span>
                                    </div>
                                </div>

                                <!-- Hidden Stage / Group pass through -->
                                <input type="hidden" name="stage_id" :value="editData.stage_id">
                                <input type="hidden" name="group_id" :value="editData.group_id">
                            </div>

                            <!-- ROW 2: TIM & MASTER VENUE -->
                            <div class="p-5 rounded-xl bg-court-surface-elevated/50 border border-court-border space-y-4">
                                <div
                                    class="text-xs font-headline font-bold uppercase tracking-wider text-stadium-emerald flex items-center justify-between pb-2 border-b border-court-border/40">
                                    <div class="flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-base">stadium</span>
                                        2. Tim Bertanding & Lokasi Venue Lapangan
                                    </div>
                                    <a href="{{ route('admin.dashboard', ['tab' => 'venues']) }}" target="_blank"
                                        class="text-[11px] font-mono text-stadium-emerald hover:underline flex items-center gap-1">
                                        <span class="material-symbols-outlined text-xs">add_location_alt</span>
                                        Master Venue
                                    </a>
                                </div>

                                <!-- Sub-row 1: Tim Kandang vs Tim Tandang -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="space-y-1.5">
                                        <label class="block text-stadium-emerald uppercase font-bold flex items-center gap-1">
                                            <span class="w-2 h-2 rounded-full bg-stadium-emerald"></span> Tim Kandang (Home)
                                        </label>
                                        <select name="home_team_id" x-model="editData.home_team_id" required
                                            class="w-full py-2.5 px-3 rounded-lg bg-court-navy border border-court-border text-text-primary focus:border-stadium-emerald font-sans">
                                            @foreach ($teams as $t)
                                                <option value="{{ $t->id }}">{{ $t->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="space-y-1.5">
                                        <label class="block text-telemetry-cyan uppercase font-bold flex items-center gap-1">
                                            <span class="w-2 h-2 rounded-full bg-telemetry-cyan"></span> Tim Tandang (Away)
                                        </label>
                                        <select name="away_team_id" x-model="editData.away_team_id" required
                                            class="w-full py-2.5 px-3 rounded-lg bg-court-navy border border-court-border text-text-primary focus:border-stadium-emerald font-sans">
                                            @foreach ($teams as $t)
                                                <option value="{{ $t->id }}">{{ $t->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <!-- Sub-row 2: Venue Lapangan & Kustom -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-3 border-t border-court-border/40">
                                    <div class="space-y-1.5">
                                        <label class="block text-text-muted uppercase font-bold">Pilih dari Master Venue</label>
                                        <select name="venue_id" x-model="editData.venue_id" @change="onEditVenueChange($event.target.value)"
                                            class="w-full py-2.5 px-3 rounded-lg bg-court-navy border border-court-border text-text-primary focus:border-stadium-emerald font-sans">
                                            <option value="">-- Pilih dari Master Venue --</option>
                                            @foreach ($venues as $v)
                                                <option value="{{ $v->id }}">
                                                    {{ $v->name }} {{ $v->court_name ? '(' . $v->court_name . ')' : '' }} &bull; {{ $v->city ?? 'Arena' }}
                                                </option>
                                            @endforeach
                                            <option value="custom">✏️ Tulis Nama Venue Kustom / Manual...</option>
                                        </select>
                                        <span class="text-[10px] text-text-muted">Pilih lapangan yang terdaftar</span>
                                    </div>

                                    <div class="space-y-1.5">
                                        <label class="block text-text-muted uppercase font-bold">Nama Venue</label>
                                        <input type="text" name="venue" x-model="editData.venue"
                                            :readonly="!!(editData.venue_id && venueMap[editData.venue_id])"
                                            :class="editData.venue_id && venueMap[editData.venue_id] ? 'bg-court-surface-elevated/70 text-text-primary border-court-border cursor-not-allowed' : 'bg-court-navy border-court-border text-text-primary focus:border-stadium-emerald'"
                                            placeholder="Nama venue..."
                                            required
                                            class="w-full py-2.5 px-3 rounded-lg border text-sm font-sans transition-all">
                                        <span class="text-[10px] text-text-muted" x-text="editData.venue_id && venueMap[editData.venue_id] ? 'Terkunci otomatis (readonly) sesuai Master Venue yang dipilih' : 'Ketik nama venue kustom jika diperlukan'">
                                            Terkunci otomatis (readonly) sesuai Master Venue yang dipilih
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- ROW 3: PENUGASAN WASIT LAPANGAN (OFFICIALS) EDIT -->
                            <div
                                class="p-5 rounded-xl bg-court-surface-elevated/50 border border-court-border space-y-3">
                                <div class="flex items-center justify-between pb-2 border-b border-court-border/40">
                                    <div
                                        class="text-xs font-headline font-bold uppercase tracking-wider text-stadium-emerald flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-base">sports</span>
                                        3. Penugasan Wasit di Lapangan (Pitch Officials)
                                    </div>
                                    <a href="{{ route('admin.dashboard', ['tab' => 'referees']) }}" target="_blank"
                                        class="text-[11px] font-mono text-stadium-emerald hover:underline flex items-center gap-1">
                                        <span class="material-symbols-outlined text-xs">person_add</span>
                                        Master Wasit Lapangan
                                    </a>
                                </div>

                                <p class="text-[11px] text-text-muted">Pilih wasit yang memimpin jalannya laga langsung di lapangan (Wasit 1, Wasit 2, dan Cadangan).</p>

                                @if ($referees->isNotEmpty())
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                        <div class="space-y-1.5">
                                            <label class="block text-text-muted uppercase font-bold text-[11px]">Wasit 1 (Utama)</label>
                                            <select name="referee_1_id" x-model="editData.referee_1_id"
                                                class="w-full py-2.5 px-3 rounded-lg bg-court-navy border border-court-border text-text-primary focus:border-stadium-emerald font-sans">
                                                <option value="">-- Belum Ditugaskan --</option>
                                                @foreach ($referees as $rf)
                                                    <option value="{{ $rf->id }}">{{ $rf->name }} ({{ $rf->license ?? 'Wasit' }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="block text-text-muted uppercase font-bold text-[11px]">Wasit 2 (Pendamping)</label>
                                            <select name="referee_2_id" x-model="editData.referee_2_id"
                                                class="w-full py-2.5 px-3 rounded-lg bg-court-navy border border-court-border text-text-primary focus:border-stadium-emerald font-sans">
                                                <option value="">-- Belum Ditugaskan --</option>
                                                @foreach ($referees as $rf)
                                                    <option value="{{ $rf->id }}">{{ $rf->name }} ({{ $rf->license ?? 'Wasit' }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="block text-text-muted uppercase font-bold text-[11px]">Wasit 3 / Cadangan (Opsional)</label>
                                            <select name="referee_3_id" x-model="editData.referee_3_id"
                                                class="w-full py-2.5 px-3 rounded-lg bg-court-navy border border-court-border text-text-primary focus:border-stadium-emerald font-sans">
                                                <option value="">-- Belum Ditugaskan --</option>
                                                @foreach ($referees as $rf)
                                                    <option value="{{ $rf->id }}">{{ $rf->name }} ({{ $rf->license ?? 'Wasit' }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                @else
                                    <div
                                        class="p-3 bg-card-yellow/10 border border-card-yellow/20 rounded-lg text-card-yellow text-xs flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="material-symbols-outlined text-sm">info</span>
                                            <span>Belum ada wasit lapangan terdaftar. Tambahkan di master data wasit lapangan.</span>
                                        </div>
                                        <a href="{{ route('admin.dashboard', ['tab' => 'referees']) }}" target="_blank"
                                            class="font-bold underline text-stadium-emerald">+ Tambah Wasit</a>
                                    </div>
                                @endif
                            </div>

                            <!-- ROW 4: PENUGASAN WASIT MEJA / OPERATOR EDIT -->
                            <div
                                class="p-5 rounded-xl bg-court-surface-elevated/50 border border-court-border space-y-3">
                                <div class="flex items-center justify-between pb-2 border-b border-court-border/40">
                                    <div
                                        class="text-xs font-headline font-bold uppercase tracking-wider text-card-yellow flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-base">badge</span>
                                        4. Penugasan Wasit Meja / Operator (Hak Kontrol Pertandingan)
                                    </div>
                                    <a href="{{ route('admin.dashboard', ['tab' => 'operators']) }}" target="_blank"
                                        class="text-[11px] font-mono text-stadium-emerald hover:underline flex items-center gap-1">
                                        <span class="material-symbols-outlined text-xs">group_add</span>
                                        Master Operator
                                    </a>
                                </div>

                                <p class="text-[11px] text-text-muted">Centang operator wasit meja yang memiliki wewenang
                                    mengontrol pertandingan ini.</p>

                                @if ($operators->isNotEmpty())
                                    <div
                                        class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2.5 max-h-36 overflow-y-auto p-2.5 bg-court-navy/80 rounded-lg border border-court-border">
                                        @foreach ($operators as $op)
                                            <label
                                                class="flex items-center gap-2.5 p-2 rounded-lg bg-court-surface hover:bg-court-surface-elevated border border-court-border/50 cursor-pointer text-xs transition-colors">
                                                <input type="checkbox" name="operator_ids[]" value="{{ $op->id }}"
                                                    :checked="editData.operator_ids && editData.operator_ids.map(Number).includes(
                                                        {{ $op->id }})"
                                                    class="rounded text-stadium-emerald bg-court-navy border-court-border focus:ring-stadium-emerald">
                                                <div class="truncate">
                                                    <div class="font-bold text-text-primary truncate">{{ $op->name }}
                                                    </div>
                                                    <div class="text-[10px] font-mono text-text-muted truncate">
                                                        {{ $op->email }}</div>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                @else
                                    <div
                                        class="p-3 bg-card-yellow/10 border border-card-yellow/20 rounded-lg text-card-yellow text-xs flex items-center gap-2">
                                        <span class="material-symbols-outlined text-sm">info</span>
                                        <span>Belum ada user ber-role Operator. Tambahkan wasit meja baru di tab Master
                                            Operator.</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Footer Buttons -->
                        <div class="pt-5 border-t border-court-border/80 flex items-center justify-end gap-3">
                            <button type="button" @click="editModal = false"
                                class="px-5 py-2.5 rounded-lg bg-court-navy hover:bg-court-border text-text-muted hover:text-text-primary font-bold transition-colors">
                                BATAL
                            </button>
                            <button type="submit"
                                class="px-6 py-2.5 rounded-lg bg-telemetry-cyan hover:bg-telemetry-cyan/90 text-court-navy font-headline font-bold uppercase tracking-wider transition-all shadow-[0_0_15px_rgba(0,240,255,0.35)] flex items-center gap-2">
                                <span class="material-symbols-outlined text-sm">save</span>
                                PERBARUI JADWAL
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            </template>
        @endif

    </div>
@endsection
