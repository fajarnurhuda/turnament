@extends('layouts.app')

@section('title', 'Admin Dashboard & Master Data Management - FUTSAL PRO')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8" 
     x-data="{ 
        tab: '{{ request('tab', 'teams') }}',
        categoryModal: false,
        teamModal: false,
        playerModal: false,
        venueModal: false,
        operatorModal: false,
        refereeModal: false,
        editCategory: null,
        editTeam: null,
        editPlayer: null,
        editVenue: null,
        editOperator: null,
        editReferee: null,
        categoryFormUrl: '{{ route('admin.categories.store') }}',
        categoryMethod: 'POST',
        venueFormUrl: '{{ route('admin.venues.store') }}',
        venueMethod: 'POST',
        operatorFormUrl: '{{ route('admin.operators.store') }}',
        operatorMethod: 'POST',
        refereeFormUrl: '{{ route('admin.referees.store') }}',
        refereeMethod: 'POST'
     }">

    <!-- Top Admin Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-court-border">
        <div>
            <h1 class="text-2xl font-headline font-bold uppercase tracking-wider text-text-primary flex items-center gap-2">
                <span class="material-symbols-outlined text-stadium-emerald text-2xl">admin_panel_settings</span>
                Manajemen Master Data Turnamen
            </h1>
            <p class="text-xs font-mono text-text-muted">ADMINISTRATION DESK &bull; KELOMPOK, TIM, WASIT & VENUE</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.fixtures') }}" class="px-4 py-2 rounded text-xs font-headline font-bold bg-court-surface hover:bg-court-surface-elevated text-telemetry-cyan border border-court-border transition-colors flex items-center gap-1.5">
                <span class="material-symbols-outlined text-sm">calendar_month</span>
                JADWAL FIXTURE &rarr;
            </a>
        </div>
    </div>

    <!-- Telemetry Statistics Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-4">
        <div class="p-4 rounded-xl bg-court-surface border border-court-border space-y-1">
            <span class="text-[10px] font-mono text-text-muted uppercase">TOTAL KATEGORI</span>
            <p class="font-headline font-black text-2xl text-text-primary">{{ $stats['total_categories'] }}</p>
        </div>
        <div class="p-4 rounded-xl bg-court-surface border border-court-border space-y-1">
            <span class="text-[10px] font-mono text-text-muted uppercase">TOTAL TIM</span>
            <p class="font-headline font-black text-2xl text-stadium-emerald">{{ $stats['total_teams'] }}</p>
        </div>
        <div class="p-4 rounded-xl bg-court-surface border border-court-border space-y-1">
            <span class="text-[10px] font-mono text-text-muted uppercase">TOTAL PEMAIN</span>
            <p class="font-headline font-black text-2xl text-telemetry-cyan">{{ $stats['total_players'] }}</p>
        </div>
        <div class="p-4 rounded-xl bg-court-surface border border-court-border space-y-1">
            <span class="text-[10px] font-mono text-text-muted uppercase">TOTAL VENUE</span>
            <p class="font-headline font-black text-2xl text-stadium-emerald">{{ $stats['total_venues'] }}</p>
        </div>
        <div class="p-4 rounded-xl bg-court-surface border border-court-border space-y-1">
            <span class="text-[10px] font-mono text-text-muted uppercase">WASIT LAPANGAN</span>
            <p class="font-headline font-black text-2xl text-stadium-emerald">{{ $stats['total_referees'] }}</p>
        </div>
        <div class="p-4 rounded-xl bg-court-surface border border-court-border space-y-1">
            <span class="text-[10px] font-mono text-text-muted uppercase">WASIT MEJA / OP</span>
            <p class="font-headline font-black text-2xl text-card-yellow">{{ $stats['total_operators'] }}</p>
        </div>
        <div class="p-4 rounded-xl bg-court-surface border border-court-border space-y-1">
            <span class="text-[10px] font-mono text-text-muted uppercase">TOTAL LAGA</span>
            <p class="font-headline font-black text-2xl text-text-primary">{{ $stats['total_matches'] }}</p>
        </div>
        <div class="p-4 rounded-xl bg-court-surface border border-court-border space-y-1">
            <span class="text-[10px] font-mono text-text-muted uppercase">LAGA LIVE</span>
            <p class="font-headline font-black text-2xl text-live-pulse">{{ $stats['live_matches'] }}</p>
        </div>
    </div>

    <!-- Category Filter selector -->
    <div class="p-4 rounded-xl bg-court-surface-elevated/40 border border-court-border flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-2 overflow-x-auto">
            <span class="text-xs font-mono text-text-muted">FILTER KATEGORI:</span>
            @foreach($categories as $c)
                <a href="{{ route('admin.dashboard', ['category_id' => $c->id]) }}" 
                   class="px-3 py-1 rounded text-xs font-mono {{ $selectedCategoryId == $c->id ? 'bg-stadium-emerald text-court-navy font-bold' : 'bg-court-navy text-text-muted hover:text-text-primary border border-court-border' }}">
                    {{ $c->name }} ({{ $c->teams_count }} tim)
                </a>
            @endforeach
        </div>

        <div class="flex items-center gap-2">
            <button @click="tab = 'categories'" class="px-3 py-1.5 rounded text-xs font-mono font-bold bg-court-navy hover:bg-court-surface text-telemetry-cyan border border-court-border transition-colors flex items-center gap-1.5">
                <span class="material-symbols-outlined text-sm">settings</span>
                Kelola / Hapus Kategori
            </button>
            <button @click="editCategory = null; categoryFormUrl = '{{ route('admin.categories.store') }}'; categoryMethod = 'POST'; categoryModal = true" class="px-3 py-1.5 rounded text-xs font-mono font-bold bg-court-surface hover:bg-court-border text-stadium-emerald border border-court-border transition-colors flex items-center gap-1">
                <span class="material-symbols-outlined text-sm">add</span>
                Kategori Baru
            </button>
        </div>
    </div>

    <!-- Main Data Tabs Navigation -->
    <div class="bg-court-surface rounded-xl border border-court-border overflow-hidden">
        <div class="flex flex-wrap items-center justify-between px-6 py-4 border-b border-court-border bg-court-surface-elevated/40 gap-4">
            <div class="flex flex-wrap items-center gap-2">
                <button @click="tab = 'categories'" :class="tab === 'categories' ? 'bg-stadium-emerald text-court-navy font-bold shadow-[0_0_12px_rgba(0,255,135,0.3)]' : 'text-text-muted hover:text-text-primary bg-court-navy border border-court-border'" class="px-4 py-2 rounded text-xs font-headline tracking-wider uppercase transition-colors flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-sm">category</span>
                    Kategori ({{ $categories->count() }})
                </button>
                <button @click="tab = 'teams'" :class="tab === 'teams' ? 'bg-stadium-emerald text-court-navy font-bold' : 'text-text-muted hover:text-text-primary bg-court-navy border border-court-border'" class="px-4 py-2 rounded text-xs font-headline tracking-wider uppercase transition-colors">
                    Daftar Tim ({{ $teams->count() }})
                </button>
                <button @click="tab = 'players'" :class="tab === 'players' ? 'bg-telemetry-cyan text-court-navy font-bold' : 'text-text-muted hover:text-text-primary bg-court-navy border border-court-border'" class="px-4 py-2 rounded text-xs font-headline tracking-wider uppercase transition-colors">
                    Daftar Pemain ({{ $players->count() }})
                </button>
                <button @click="tab = 'venues'" :class="tab === 'venues' ? 'bg-stadium-emerald text-court-navy font-bold' : 'text-text-muted hover:text-text-primary bg-court-navy border border-court-border'" class="px-4 py-2 rounded text-xs font-headline tracking-wider uppercase transition-colors">
                    Daftar Venue ({{ $venues->count() }})
                </button>
                <button @click="tab = 'referees'" :class="tab === 'referees' ? 'bg-stadium-emerald text-court-navy font-bold shadow-[0_0_12px_rgba(0,255,135,0.3)]' : 'text-text-muted hover:text-text-primary bg-court-navy border border-court-border'" class="px-4 py-2 rounded text-xs font-headline tracking-wider uppercase transition-colors flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-sm">sports</span>
                    Wasit Lapangan ({{ $referees->count() }})
                </button>
                <button @click="tab = 'operators'" :class="tab === 'operators' ? 'bg-card-yellow text-court-navy font-bold' : 'text-text-muted hover:text-text-primary bg-court-navy border border-court-border'" class="px-4 py-2 rounded text-xs font-headline tracking-wider uppercase transition-colors">
                    Wasit Meja / Operator ({{ $operators->count() }})
                </button>
            </div>

            <div>
                <button x-show="tab === 'categories'" @click="editCategory = null; categoryFormUrl = '{{ route('admin.categories.store') }}'; categoryMethod = 'POST'; categoryModal = true" class="px-4 py-2 rounded text-xs font-headline font-bold bg-stadium-emerald hover:bg-stadium-emerald/90 text-court-navy transition-all shadow-[0_0_12px_rgba(0,255,135,0.2)] flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-sm">add_circle</span>
                    TAMBAH KATEGORI BARU
                </button>
                <button x-show="tab === 'teams'" @click="teamModal = true; editTeam = null" class="px-4 py-2 rounded text-xs font-headline font-bold bg-stadium-emerald hover:bg-stadium-emerald/90 text-court-navy transition-all shadow-[0_0_12px_rgba(0,255,135,0.2)] flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-sm">add_circle</span>
                    TAMBAH TIM BARU
                </button>
                <button x-show="tab === 'players'" @click="playerModal = true; editPlayer = null" class="px-4 py-2 rounded text-xs font-headline font-bold bg-telemetry-cyan hover:bg-telemetry-cyan/90 text-court-navy transition-all shadow-[0_0_12px_rgba(0,229,255,0.2)] flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-sm">person_add</span>
                    TAMBAH PEMAIN BARU
                </button>
                <button x-show="tab === 'venues'" @click="editVenue = null; venueFormUrl = '{{ route('admin.venues.store') }}'; venueMethod = 'POST'; venueModal = true" class="px-4 py-2 rounded text-xs font-headline font-bold bg-stadium-emerald hover:bg-stadium-emerald/90 text-court-navy transition-all shadow-[0_0_12px_rgba(0,255,135,0.2)] flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-sm">add_location_alt</span>
                    TAMBAH VENUE BARU
                </button>
                <button x-show="tab === 'referees'" @click="editReferee = null; refereeFormUrl = '{{ route('admin.referees.store') }}'; refereeMethod = 'POST'; refereeModal = true" class="px-4 py-2 rounded text-xs font-headline font-bold bg-stadium-emerald hover:bg-stadium-emerald/90 text-court-navy transition-all shadow-[0_0_12px_rgba(0,255,135,0.3)] flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-sm">sports</span>
                    TAMBAH WASIT LAPANGAN
                </button>
                <button x-show="tab === 'operators'" @click="editOperator = null; operatorFormUrl = '{{ route('admin.operators.store') }}'; operatorMethod = 'POST'; operatorModal = true" class="px-4 py-2 rounded text-xs font-headline font-bold bg-card-yellow hover:bg-card-yellow/90 text-court-navy transition-all shadow-[0_0_12px_rgba(255,214,0,0.2)] flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-sm">badge</span>
                    TAMBAH OPERATOR BARU
                </button>
            </div>
        </div>

        <!-- TAB 0: CATEGORIES LIST -->
        <div x-show="tab === 'categories'" class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse($categories as $cat)
                    <div class="p-5 rounded-xl bg-court-surface-elevated border border-court-border space-y-4 hover:border-court-border/80 transition-all flex flex-col justify-between">
                        <div class="space-y-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-lg bg-court-navy border border-court-border flex items-center justify-center font-headline font-black text-lg text-stadium-emerald flex-shrink-0">
                                        <span class="material-symbols-outlined text-2xl">category</span>
                                    </div>
                                    <div>
                                        <h3 class="font-headline font-bold text-base text-text-primary">{{ $cat->name }}</h3>
                                        <span class="text-xs font-mono text-telemetry-cyan font-bold">/{{ $cat->slug }}</span>
                                    </div>
                                </div>

                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase {{ $cat->teams_count > 0 ? 'bg-stadium-emerald/10 text-stadium-emerald border border-stadium-emerald/30' : 'bg-court-navy text-text-muted border border-court-border' }}">
                                    {{ $cat->teams_count }} Tim
                                </span>
                            </div>

                            <p class="text-xs text-text-muted font-sans line-clamp-2 min-h-[32px]">
                                {{ $cat->description ?: 'Tidak ada deskripsi atau aturan khusus pada kategori ini.' }}
                            </p>

                            <div class="grid grid-cols-2 gap-2 pt-2 border-t border-court-border/40 text-xs font-mono">
                                <div class="p-2 rounded bg-court-navy/60 border border-court-border/60">
                                    <span class="text-[10px] text-text-muted uppercase block">Total Tim</span>
                                    <span class="font-bold text-text-primary">{{ $cat->teams_count }} Tim Terdaftar</span>
                                </div>
                                <div class="p-2 rounded bg-court-navy/60 border border-court-border/60">
                                    <span class="text-[10px] text-text-muted uppercase block">Total Laga</span>
                                    <span class="font-bold text-stadium-emerald">{{ $cat->matches_count }} Pertandingan</span>
                                </div>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-court-border/60 flex items-center justify-between text-xs font-mono">
                            <a href="{{ route('admin.dashboard', ['category_id' => $cat->id]) }}" @click="tab = 'teams'" class="text-xs font-mono text-stadium-emerald hover:underline flex items-center gap-1 font-bold">
                                <span>Lihat Tim</span>
                                <span class="material-symbols-outlined text-sm">arrow_forward</span>
                            </a>

                            <div class="flex items-center gap-2">
                                <button type="button" @click="editCategory = {
                                    id: {{ $cat->id }},
                                    name: '{{ addslashes($cat->name) }}',
                                    description: '{{ addslashes($cat->description ?? '') }}'
                                }; categoryFormUrl = '/admin/categories/{{ $cat->id }}'; categoryMethod = 'PUT'; categoryModal = true"
                                class="px-2.5 py-1 rounded bg-court-navy hover:bg-court-border text-telemetry-cyan font-bold transition-colors flex items-center gap-1">
                                    <span class="material-symbols-outlined text-xs">edit</span>
                                    Edit
                                </button>

                                @if($categories->count() > 1)
                                    <form action="{{ route('admin.categories.destroy', $cat->id) }}" method="POST" onsubmit="return confirm('PERINGATAN HAPUS KATEGORI:\n\nApakah Anda yakin ingin menghapus kategori \'{{ addslashes($cat->name) }}\'?\n\nMenghapus kategori ini juga akan MENGHAPUS SEMUA {{ $cat->teams_count }} Tim dan {{ $cat->matches_count }} Jadwal Pertandingan di dalamnya secara otomatis!\n\nKlik OK jika yakin.')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1 rounded bg-court-navy hover:bg-card-red/20 text-text-muted hover:text-card-red transition-colors" title="Hapus Kategori {{ $cat->name }}">
                                            <span class="material-symbols-outlined text-sm">delete</span>
                                        </button>
                                    </form>
                                @else
                                    <span class="p-1 text-text-muted opacity-40 cursor-not-allowed" title="Kategori terakhir tidak dapat dihapus">
                                        <span class="material-symbols-outlined text-sm">lock</span>
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-3 p-12 text-center text-text-muted font-mono text-xs">
                        Belum ada kategori turnamen terdaftar. Klik "TAMBAH KATEGORI BARU".
                    </div>
                @endforelse
            </div>
        </div>

        <!-- TAB 1: TEAMS LIST -->
        <div x-show="tab === 'teams'" class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse($teams as $tm)
                    <div class="p-5 rounded-xl bg-court-surface-elevated border border-court-border space-y-4 hover:border-court-border/80 transition-all">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-lg bg-court-navy border border-court-border flex items-center justify-center font-headline font-black text-base text-stadium-emerald">
                                    {{ $tm->initials }}
                                </div>
                                <div>
                                    <h3 class="font-headline font-bold text-base text-text-primary">{{ $tm->name }}</h3>
                                    <span class="text-xs font-mono text-telemetry-cyan">{{ $tm->code ?? '-' }} &bull; {{ $tm->category->name }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="text-xs font-mono text-text-muted space-y-1 py-2 border-y border-court-border/40">
                            <p>Manajer: <strong class="text-text-primary">{{ $tm->manager_name ?? '-' }}</strong></p>
                            <p>Kontak: <strong class="text-text-primary">{{ $tm->manager_contact ?? '-' }}</strong></p>
                            <p>Jumlah Pemain Terdaftar: <strong class="text-stadium-emerald">{{ $tm->players_count }} Pemain</strong></p>
                        </div>

                        <div class="flex items-center justify-between pt-1">
                            <a href="{{ route('admin.dashboard', ['category_id' => $selectedCategoryId, 'team_id' => $tm->id]) }}" @click="tab = 'players'" class="text-xs font-mono text-stadium-emerald hover:underline">
                                Lihat Pemain &rarr;
                            </a>

                            <div class="flex items-center gap-2">
                                <form action="{{ route('admin.teams.destroy', $tm->id) }}" method="POST" onsubmit="return confirm('Hapus tim {{ $tm->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded bg-court-navy hover:bg-card-red/20 text-text-muted hover:text-card-red border border-court-border transition-colors">
                                        <span class="material-symbols-outlined text-sm">delete</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-3 py-12 text-center text-text-muted font-mono text-xs">
                        Belum ada tim terdaftar dalam kategori ini. Silakan klik tombol "TAMBAH TIM BARU".
                    </div>
                @endforelse
            </div>
        </div>

        <!-- TAB 2: PLAYERS LIST -->
        <div x-show="tab === 'players'" class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-court-navy font-mono text-text-muted uppercase border-b border-court-border">
                    <tr>
                        <th class="py-3 px-4">NO. PUNGGUNG</th>
                        <th class="py-3 px-4">NAMA PEMAIN</th>
                        <th class="py-3 px-4">TIM FUTSAL</th>
                        <th class="py-3 px-4">POSISI</th>
                        <th class="py-3 px-4">STATUS</th>
                        <th class="py-3 px-4 text-right">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-court-border/40 font-mono">
                    @forelse($players as $pl)
                        <tr class="hover:bg-court-surface-elevated/40 transition-colors">
                            <td class="py-3 px-4 font-bold text-stadium-emerald font-mono text-sm">
                                #{{ $pl->jersey_number }}
                            </td>
                            <td class="py-3 px-4 font-sans font-medium text-text-primary text-sm">
                                {{ $pl->name }}
                            </td>
                            <td class="py-3 px-4 text-text-muted">
                                {{ $pl->team->name }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] bg-court-navy border border-court-border text-telemetry-cyan font-bold">
                                    {{ $pl->position_label }} ({{ $pl->position }})
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                @if($pl->is_captain)
                                    <span class="px-2 py-0.5 rounded text-[10px] bg-card-yellow/20 border border-card-yellow/40 text-card-yellow font-bold">
                                        KAPTEN TIM
                                    </span>
                                @else
                                    <span class="text-text-muted text-[11px]">Pemain</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right">
                                <form action="{{ route('admin.players.destroy', $pl->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus pemain {{ $pl->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1 rounded bg-court-navy hover:bg-card-red/20 text-text-muted hover:text-card-red border border-court-border transition-colors">
                                        <span class="material-symbols-outlined text-sm">delete</span>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-text-muted font-mono">
                                Belum ada pemain terdaftar. Silakan pilih tim dan klik tombol "TAMBAH PEMAIN BARU".
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- TAB 3: VENUES LIST -->
        <div x-show="tab === 'venues'" class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @forelse($venues as $vn)
                    <div class="p-5 rounded-xl bg-court-surface-elevated border border-court-border space-y-4 hover:border-stadium-emerald/40 transition-all flex flex-col justify-between">
                        <div class="space-y-3">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-xl bg-court-navy border border-court-border flex items-center justify-center font-headline text-xl text-stadium-emerald flex-shrink-0">
                                        🏟️
                                    </div>
                                    <div>
                                        <h3 class="font-headline font-bold text-base text-text-primary leading-tight">{{ $vn->name }}</h3>
                                        @if($vn->court_name)
                                            <span class="text-xs font-mono text-telemetry-cyan font-bold">{{ $vn->court_name }}</span>
                                        @endif
                                    </div>
                                </div>
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase {{ $vn->is_active ? 'bg-stadium-emerald/10 text-stadium-emerald border border-stadium-emerald/30' : 'bg-court-navy text-text-muted border border-court-border' }}">
                                    {{ $vn->is_active ? 'AKTIF' : 'NONAKTIF' }}
                                </span>
                            </div>

                            <div class="space-y-1.5 text-xs font-mono text-text-muted pt-2 border-t border-court-border/40">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-sm text-telemetry-cyan">location_on</span>
                                    <span class="text-text-primary truncate">{{ $vn->city ?? '-' }} &bull; {{ $vn->address ?? 'Alamat belum diatur' }}</span>
                                </div>
                                @if($vn->description)
                                    <p class="text-[11px] font-sans text-text-muted line-clamp-2 italic pt-1">{{ $vn->description }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="pt-3 border-t border-court-border/60 flex items-center justify-between text-xs font-mono">
                            <span class="text-text-muted flex items-center gap-1">
                                <span class="material-symbols-outlined text-sm text-stadium-emerald">sports_soccer</span>
                                <strong>{{ $vn->matches_count }}</strong> Laga Dimainkan
                            </span>

                            <div class="flex items-center gap-2">
                                <button type="button" @click="editVenue = {
                                    id: {{ $vn->id }},
                                    name: '{{ addslashes($vn->name) }}',
                                    court_name: '{{ addslashes($vn->court_name ?? '') }}',
                                    address: '{{ addslashes($vn->address ?? '') }}',
                                    city: '{{ addslashes($vn->city ?? '') }}',
                                    description: '{{ addslashes($vn->description ?? '') }}',
                                    is_active: {{ $vn->is_active ? 'true' : 'false' }}
                                }; venueFormUrl = '/admin/venues/{{ $vn->id }}'; venueMethod = 'PUT'; venueModal = true"
                                class="px-2.5 py-1 rounded bg-court-navy hover:bg-court-border text-telemetry-cyan font-bold transition-colors">
                                    Edit
                                </button>

                                <form action="{{ route('admin.venues.destroy', $vn->id) }}" method="POST" onsubmit="return confirm('Hapus venue ini?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1 rounded bg-court-navy hover:bg-card-red/20 text-text-muted hover:text-card-red transition-colors" title="Hapus Venue">
                                        <span class="material-symbols-outlined text-sm">delete</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-3 p-12 text-center text-text-muted font-mono text-xs">
                        Belum ada master venue. Klik "Tambah Venue Baru" untuk mendaftarkan lapangan.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- TAB 4: OPERATORS LIST -->
        <div x-show="tab === 'operators'" class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse($operators as $op)
                    <div class="p-5 rounded-xl bg-court-surface-elevated border border-court-border space-y-4 hover:border-court-border/80 transition-all">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-lg bg-court-navy border border-card-yellow/40 flex items-center justify-center text-card-yellow">
                                    <span class="material-symbols-outlined text-2xl">badge</span>
                                </div>
                                <div>
                                    <h3 class="font-headline font-bold text-base text-text-primary">{{ $op->name }}</h3>
                                    <span class="text-xs font-mono text-card-yellow uppercase">{{ $op->role }} &bull; WASIT MEJA</span>
                                </div>
                            </div>

                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase bg-stadium-emerald/10 text-stadium-emerald border border-stadium-emerald/30">
                                AKTIF
                            </span>
                        </div>

                        <div class="space-y-1.5 text-xs font-mono text-text-muted pt-2 border-t border-court-border/40">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-sm text-telemetry-cyan">mail</span>
                                <span class="text-text-primary truncate">{{ $op->email }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-sm text-stadium-emerald">sports_soccer</span>
                                <span>Penugasan Laga: <strong class="text-stadium-emerald">{{ $op->assigned_matches_count }} Pertandingan</strong></span>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-court-border/60 flex items-center justify-between text-xs font-mono">
                            <span class="text-[11px] text-text-muted">
                                Terdaftar {{ $op->created_at?->format('d M Y') ?? '-' }}
                            </span>

                            <div class="flex items-center gap-2">
                                <button type="button" @click="editOperator = {
                                    id: {{ $op->id }},
                                    name: '{{ addslashes($op->name) }}',
                                    email: '{{ addslashes($op->email) }}'
                                }; operatorFormUrl = '/admin/operators/{{ $op->id }}'; operatorMethod = 'PUT'; operatorModal = true"
                                class="px-2.5 py-1 rounded bg-court-navy hover:bg-court-border text-telemetry-cyan font-bold transition-colors">
                                    Edit
                                </button>

                                <form action="{{ route('admin.operators.destroy', $op->id) }}" method="POST" onsubmit="return confirm('Hapus akun operator wasit meja ini?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1 rounded bg-court-navy hover:bg-card-red/20 text-text-muted hover:text-card-red transition-colors" title="Hapus Operator">
                                        <span class="material-symbols-outlined text-sm">delete</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-3 p-12 text-center text-text-muted font-mono text-xs">
                        Belum ada operator wasit meja terdaftar. Silakan klik "Tambah Operator Baru".
                    </div>
                @endforelse
            </div>
        </div>

        <!-- TAB 5: REFEREES (WASIT LAPANGAN) LIST -->
        <div x-show="tab === 'referees'" class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse($referees as $rf)
                    <div class="p-5 rounded-xl bg-court-surface-elevated border border-court-border space-y-4 hover:border-court-border/80 transition-all">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-lg bg-court-navy border border-court-border flex items-center justify-center font-headline font-black text-lg text-stadium-emerald flex-shrink-0">
                                    <span class="material-symbols-outlined text-2xl">sports</span>
                                </div>
                                <div>
                                    <h3 class="font-headline font-bold text-base text-text-primary">{{ $rf->name }}</h3>
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase bg-stadium-emerald/10 text-stadium-emerald border border-stadium-emerald/30">
                                            {{ $rf->license ?? 'Wasit Futsal' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            @if($rf->is_active)
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase bg-stadium-emerald/10 text-stadium-emerald border border-stadium-emerald/30">
                                    AKTIF
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase bg-card-red/10 text-card-red border border-card-red/30">
                                    NONAKTIF
                                </span>
                            @endif
                        </div>

                        <div class="space-y-1.5 text-xs font-mono text-text-muted pt-2 border-t border-court-border/40">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-sm text-telemetry-cyan">location_on</span>
                                <span>Asal / Kota: <strong class="text-text-primary">{{ $rf->city ?? 'Umum' }}</strong></span>
                            </div>
                            @if($rf->phone)
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-sm text-card-yellow">call</span>
                                    <span>Kontak: <strong class="text-text-primary">{{ $rf->phone }}</strong></span>
                                </div>
                            @endif
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-sm text-stadium-emerald">sports_soccer</span>
                                <span>Total Laga Dipimpin: <strong class="text-stadium-emerald">{{ $rf->total_matches_count }} Pertandingan</strong></span>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-court-border/60 flex items-center justify-between text-xs font-mono">
                            <span class="text-[11px] text-text-muted">
                                Terdaftar {{ $rf->created_at?->format('d M Y') ?? '-' }}
                            </span>

                            <div class="flex items-center gap-2">
                                <button type="button" @click="editReferee = {
                                    id: {{ $rf->id }},
                                    name: '{{ addslashes($rf->name) }}',
                                    license: '{{ addslashes($rf->license ?? '') }}',
                                    phone: '{{ addslashes($rf->phone ?? '') }}',
                                    city: '{{ addslashes($rf->city ?? '') }}',
                                    is_active: {{ $rf->is_active ? 'true' : 'false' }}
                                }; refereeFormUrl = '/admin/referees/{{ $rf->id }}'; refereeMethod = 'PUT'; refereeModal = true"
                                class="px-2.5 py-1 rounded bg-court-navy hover:bg-court-border text-telemetry-cyan font-bold transition-colors">
                                    Edit
                                </button>

                                <form action="{{ route('admin.referees.destroy', $rf->id) }}" method="POST" onsubmit="return confirm('Hapus data wasit lapangan ini?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1 rounded bg-court-navy hover:bg-card-red/20 text-text-muted hover:text-card-red transition-colors" title="Hapus Wasit">
                                        <span class="material-symbols-outlined text-sm">delete</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-3 p-12 text-center text-text-muted font-mono text-xs">
                        Belum ada wasit lapangan terdaftar. Silakan klik "TAMBAH WASIT LAPANGAN".
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- MODAL 1: CREATE TEAM -->
    <div x-show="teamModal" x-cloak class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-court-surface rounded-xl border border-court-border max-w-md w-full p-6 space-y-4 shadow-2xl">
            <div class="flex items-center justify-between pb-3 border-b border-court-border">
                <h3 class="font-headline font-bold text-base text-text-primary uppercase">Pendaftaran Tim Baru</h3>
                <button @click="teamModal = false" class="text-text-muted hover:text-text-primary">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form action="{{ route('admin.teams.store') }}" method="POST" class="space-y-4 text-xs font-mono">
                @csrf
                <div class="space-y-1">
                    <label class="block text-text-muted uppercase">Kategori Turnamen</label>
                    <select name="category_id" required class="w-full py-2 px-3 rounded bg-court-navy border border-court-border text-text-primary focus:border-stadium-emerald">
                        @foreach($categories as $c)
                            <option value="{{ $c->id }}" {{ $selectedCategoryId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="block text-text-muted uppercase">Nama Tim Futsal</label>
                    <input type="text" name="name" required placeholder="Contoh: Garuda Futsal Academy" class="w-full py-2 px-3 rounded bg-court-navy border border-court-border text-text-primary focus:border-stadium-emerald font-sans">
                </div>

                <div class="space-y-1">
                    <label class="block text-text-muted uppercase">Kode Singkatan (Maks 5 Karakter)</label>
                    <input type="text" name="code" maxlength="5" placeholder="Contoh: GFA" class="w-full py-2 px-3 rounded bg-court-navy border border-court-border text-text-primary focus:border-stadium-emerald uppercase">
                </div>

                <div class="space-y-1">
                    <label class="block text-text-muted uppercase">Nama Manajer / Pelatih</label>
                    <input type="text" name="manager_name" placeholder="Contoh: Coach Hendra" class="w-full py-2 px-3 rounded bg-court-navy border border-court-border text-text-primary focus:border-stadium-emerald font-sans">
                </div>

                <div class="space-y-1">
                    <label class="block text-text-muted uppercase">Nomor Kontak / WhatsApp</label>
                    <input type="text" name="manager_contact" placeholder="08123456789" class="w-full py-2 px-3 rounded bg-court-navy border border-court-border text-text-primary focus:border-stadium-emerald">
                </div>

                <div class="pt-4 flex items-center justify-end gap-3">
                    <button type="button" @click="teamModal = false" class="px-4 py-2 rounded bg-court-navy hover:bg-court-border text-text-muted font-bold transition-colors">
                        BATAL
                    </button>
                    <button type="submit" class="px-5 py-2 rounded bg-stadium-emerald hover:bg-stadium-emerald/90 text-court-navy font-headline font-bold uppercase transition-all shadow-[0_0_12px_rgba(0,255,135,0.3)]">
                        SIMPAN TIM
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: CREATE PLAYER -->
    <div x-show="playerModal" x-cloak class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-court-surface rounded-xl border border-court-border max-w-md w-full p-6 space-y-4 shadow-2xl">
            <div class="flex items-center justify-between pb-3 border-b border-court-border">
                <h3 class="font-headline font-bold text-base text-text-primary uppercase">Pendaftaran Pemain Skuad</h3>
                <button @click="playerModal = false" class="text-text-muted hover:text-text-primary">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form action="{{ route('admin.players.store') }}" method="POST" class="space-y-4 text-xs font-mono">
                @csrf
                <div class="space-y-1">
                    <label class="block text-text-muted uppercase">Pilih Tim</label>
                    <select name="team_id" required class="w-full py-2 px-3 rounded bg-court-navy border border-court-border text-text-primary focus:border-stadium-emerald font-sans">
                        @foreach($teams as $t)
                            <option value="{{ $t->id }}" {{ $selectedTeamId == $t->id ? 'selected' : '' }}>{{ $t->name }} ({{ $t->category->name }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="block text-text-muted uppercase">Nama Lengkap Pemain</label>
                    <input type="text" name="name" required placeholder="Contoh: Rian Pratama" class="w-full py-2 px-3 rounded bg-court-navy border border-court-border text-text-primary focus:border-stadium-emerald font-sans">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="block text-text-muted uppercase">No. Punggung (1-99)</label>
                        <input type="number" name="jersey_number" min="1" max="99" required placeholder="7" class="w-full py-2 px-3 rounded bg-court-navy border border-court-border text-text-primary focus:border-stadium-emerald">
                    </div>

                    <div class="space-y-1">
                        <label class="block text-text-muted uppercase">Posisi</label>
                        <select name="position" required class="w-full py-2 px-3 rounded bg-court-navy border border-court-border text-text-primary focus:border-stadium-emerald">
                            <option value="GK">GK (Kiper)</option>
                            <option value="DEF">DEF (Anchor / Defender)</option>
                            <option value="FLA" selected>FLA (Flank / Sayap)</option>
                            <option value="PIV">PIV (Pivot / Penyerang)</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <input type="checkbox" name="is_captain" id="is_captain" value="1" class="rounded bg-court-navy border-court-border text-stadium-emerald focus:ring-stadium-emerald">
                    <label for="is_captain" class="text-text-primary cursor-pointer">Pemain ini bertindak sebagai Kapten Tim</label>
                </div>

                <div class="pt-4 flex items-center justify-end gap-3">
                    <button type="button" @click="playerModal = false" class="px-4 py-2 rounded bg-court-navy hover:bg-court-border text-text-muted font-bold transition-colors">
                        BATAL
                    </button>
                    <button type="submit" class="px-5 py-2 rounded bg-telemetry-cyan hover:bg-telemetry-cyan/90 text-court-navy font-headline font-bold uppercase transition-all shadow-[0_0_12px_rgba(0,229,255,0.3)]">
                        DAFTARKAN PEMAIN
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 3: CREATE / EDIT CATEGORY -->
    <div x-show="categoryModal" x-cloak class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-court-surface rounded-xl border border-court-border max-w-md w-full p-6 space-y-4 shadow-2xl">
            <div class="flex items-center justify-between pb-3 border-b border-court-border">
                <h3 class="font-headline font-bold text-base text-stadium-emerald uppercase flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">category</span>
                    <span x-text="editCategory ? 'Edit Kategori Turnamen' : 'Kategori Turnamen Baru'"></span>
                </h3>
                <button @click="categoryModal = false" class="text-text-muted hover:text-text-primary">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form :action="categoryFormUrl" method="POST" class="space-y-4 text-xs font-mono">
                @csrf
                <template x-if="categoryMethod === 'PUT'">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="space-y-1">
                    <label class="block text-text-muted uppercase font-bold">Nama Kategori Turnamen</label>
                    <input type="text" name="name" :value="editCategory ? editCategory.name : ''" required placeholder="Contoh: U-17 Putra atau Instansi / Umum" class="w-full py-2 px-3 rounded bg-court-navy border border-court-border text-text-primary focus:border-stadium-emerald font-sans">
                </div>

                <div class="space-y-1">
                    <label class="block text-text-muted uppercase font-bold">Deskripsi / Keterangan Regulasi</label>
                    <textarea name="description" rows="3" :value="editCategory ? editCategory.description : ''" placeholder="Informasi batas usia, sistem gugur/grup, dll..." class="w-full py-2 px-3 rounded bg-court-navy border border-court-border text-text-primary focus:border-stadium-emerald font-sans"></textarea>
                </div>

                <div class="pt-4 flex items-center justify-end gap-3 border-t border-court-border/60">
                    <button type="button" @click="categoryModal = false" class="px-4 py-2 rounded bg-court-navy hover:bg-court-border text-text-muted font-bold transition-colors">
                        BATAL
                    </button>
                    <button type="submit" class="px-5 py-2 rounded bg-stadium-emerald hover:bg-stadium-emerald/90 text-court-navy font-headline font-bold uppercase transition-all shadow-[0_0_12px_rgba(0,255,135,0.3)]">
                        <span x-text="editCategory ? 'PERBARUI KATEGORI' : 'SIMPAN KATEGORI'">SIMPAN KATEGORI</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 4: CREATE / EDIT VENUE -->
    <div x-show="venueModal" x-cloak class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-court-surface rounded-xl border border-court-border max-w-lg w-full p-6 space-y-4 shadow-2xl">
            <div class="flex items-center justify-between pb-3 border-b border-court-border">
                <h3 class="font-headline font-bold text-base text-stadium-emerald uppercase flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">stadium</span>
                    <span x-text="editVenue ? 'Edit Data Venue / Lapangan' : 'Pendaftaran Venue / Lapangan Baru'"></span>
                </h3>
                <button @click="venueModal = false" class="text-text-muted hover:text-text-primary">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form :action="venueFormUrl" method="POST" class="space-y-4 text-xs font-mono">
                @csrf
                <template x-if="venueMethod === 'PUT'">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="space-y-1">
                    <label class="block text-text-muted uppercase">Nama Venue / Gedung Olahraga</label>
                    <input type="text" name="name" required placeholder="Contoh: GOR Futsal Arena Utama" :value="editVenue ? editVenue.name : ''" class="w-full py-2 px-3 rounded bg-court-navy border border-court-border text-text-primary focus:border-stadium-emerald font-sans">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="block text-text-muted uppercase">Nama Lapangan / Court</label>
                        <input type="text" name="court_name" placeholder="Contoh: Court A / Lapangan 1" :value="editVenue ? editVenue.court_name : ''" class="w-full py-2 px-3 rounded bg-court-navy border border-court-border text-text-primary focus:border-stadium-emerald font-sans">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-text-muted uppercase">Kota / Wilayah</label>
                        <input type="text" name="city" placeholder="Contoh: Jakarta Selatan" :value="editVenue ? editVenue.city : ''" class="w-full py-2 px-3 rounded bg-court-navy border border-court-border text-text-primary focus:border-stadium-emerald font-sans">
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="block text-text-muted uppercase">Alamat Lengkap</label>
                    <input type="text" name="address" placeholder="Contoh: Jl. Stadion Pemuda No. 12" :value="editVenue ? editVenue.address : ''" class="w-full py-2 px-3 rounded bg-court-navy border border-court-border text-text-primary focus:border-stadium-emerald font-sans">
                </div>

                <div class="space-y-1">
                    <label class="block text-text-muted uppercase">Fasilitas / Deskripsi Lapangan</label>
                    <textarea name="description" rows="3" placeholder="Jenis lantai vinyl/taraflex, kapasitas tribun, pencahayaan LED..." :value="editVenue ? editVenue.description : ''" class="w-full py-2 px-3 rounded bg-court-navy border border-court-border text-text-primary focus:border-stadium-emerald font-sans"></textarea>
                </div>

                <div class="flex items-center gap-2 pt-1" x-show="editVenue">
                    <input type="checkbox" name="is_active" id="is_active" value="1" :checked="editVenue && editVenue.is_active" class="rounded bg-court-navy border-court-border text-stadium-emerald focus:ring-stadium-emerald">
                    <label for="is_active" class="text-text-primary cursor-pointer">Venue ini aktif digunakan untuk pertandingan</label>
                </div>

                <div class="pt-4 flex items-center justify-end gap-3 border-t border-court-border/60">
                    <button type="button" @click="venueModal = false" class="px-4 py-2 rounded bg-court-navy hover:bg-court-border text-text-muted font-bold transition-colors">
                        BATAL
                    </button>
                    <button type="submit" class="px-5 py-2 rounded bg-stadium-emerald hover:bg-stadium-emerald/90 text-court-navy font-headline font-bold uppercase transition-all shadow-[0_0_12px_rgba(0,255,135,0.3)]" x-text="editVenue ? 'SIMPAN PERUBAHAN' : 'SIMPAN VENUE'">
                        SIMPAN VENUE
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 5: CREATE / EDIT OPERATOR WASIT MEJA -->
    <div x-show="operatorModal" x-cloak class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-court-surface rounded-xl border border-court-border max-w-md w-full p-6 space-y-4 shadow-2xl">
            <div class="flex items-center justify-between pb-3 border-b border-court-border">
                <h3 class="font-headline font-bold text-base text-card-yellow uppercase flex items-center gap-2" x-text="editOperator ? 'Edit Akun Wasit Meja' : 'Pendaftaran Operator Baru'">
                    Pendaftaran Operator Baru
                </h3>
                <button @click="operatorModal = false" class="text-text-muted hover:text-text-primary">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form :action="operatorFormUrl" method="POST" class="space-y-4 text-xs font-mono">
                @csrf
                <template x-if="operatorMethod === 'PUT'">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="space-y-1">
                    <label class="block text-text-muted uppercase font-bold">Nama Lengkap Operator / Wasit Meja</label>
                    <input type="text" name="name" :value="editOperator ? editOperator.name : ''" required placeholder="Contoh: Wasit Ahmad Fauzi" class="w-full py-2 px-3 rounded bg-court-navy border border-court-border text-text-primary focus:border-card-yellow font-sans">
                </div>

                <div class="space-y-1">
                    <label class="block text-text-muted uppercase font-bold">Email Login</label>
                    <input type="email" name="email" :value="editOperator ? editOperator.email : ''" required placeholder="operator@futsal.test" class="w-full py-2 px-3 rounded bg-court-navy border border-court-border text-text-primary focus:border-card-yellow font-sans">
                </div>

                <div class="space-y-1">
                    <label class="block text-text-muted uppercase font-bold">
                        <span x-text="editOperator ? 'Ganti Password (Kosongkan jika tidak diubah)' : 'Password Akun'">Password Akun</span>
                    </label>
                    <input type="password" name="password" :required="!editOperator" placeholder="Minimal 6 karakter" class="w-full py-2 px-3 rounded bg-court-navy border border-court-border text-text-primary focus:border-card-yellow font-sans">
                </div>

                <div class="p-3 rounded bg-court-navy/60 border border-court-border text-text-muted text-[11px]">
                    <p class="flex items-center gap-1.5 text-card-yellow font-bold pb-1">
                        <span class="material-symbols-outlined text-sm">security</span>
                        Hak Akses Terbatas:
                    </p>
                    <p>Operator hanya akan dapat mengakses jadwal dan kontrol pertandingan yang ditugaskan secara spesifik oleh Superadmin.</p>
                </div>

                <div class="pt-4 flex items-center justify-end gap-3 border-t border-court-border/60">
                    <button type="button" @click="operatorModal = false" class="px-4 py-2 rounded bg-court-navy hover:bg-court-border text-text-muted font-bold transition-colors">
                        BATAL
                    </button>
                    <button type="submit" class="px-5 py-2 rounded bg-card-yellow hover:bg-card-yellow/90 text-court-navy font-headline font-bold uppercase transition-all shadow-[0_0_12px_rgba(255,214,0,0.3)]">
                        <span x-text="editOperator ? 'PERBARUI OPERATOR' : 'SIMPAN OPERATOR'">SIMPAN OPERATOR</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 6: CREATE / EDIT WASIT LAPANGAN -->
    <div x-show="refereeModal" x-cloak class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-court-surface rounded-xl border border-court-border max-w-md w-full p-6 space-y-4 shadow-2xl">
            <div class="flex items-center justify-between pb-3 border-b border-court-border">
                <h3 class="font-headline font-bold text-base text-stadium-emerald uppercase flex items-center gap-2" x-text="editReferee ? 'Edit Data Wasit Lapangan' : 'Pendaftaran Wasit Lapangan Baru'">
                    Pendaftaran Wasit Lapangan Baru
                </h3>
                <button @click="refereeModal = false" class="text-text-muted hover:text-text-primary">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form :action="refereeFormUrl" method="POST" class="space-y-4 text-xs font-mono">
                @csrf
                <template x-if="refereeMethod === 'PUT'">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="space-y-1">
                    <label class="block text-text-muted uppercase font-bold">Nama Lengkap Wasit Lapangan</label>
                    <input type="text" name="name" :value="editReferee ? editReferee.name : ''" required placeholder="Contoh: Hendro Kartiko, S.Pd" class="w-full py-2 px-3 rounded bg-court-navy border border-court-border text-text-primary focus:border-stadium-emerald font-sans">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="block text-text-muted uppercase font-bold">Lisensi / Sertifikasi</label>
                        <input type="text" name="license" list="licenseOptions" :value="editReferee ? editReferee.license : ''" placeholder="Lisensi 1 Nasional" class="w-full py-2 px-3 rounded bg-court-navy border border-court-border text-text-primary focus:border-stadium-emerald font-sans">
                        <datalist id="licenseOptions">
                            <option value="FIFA Futsal Referee"></option>
                            <option value="Lisensi 1 Nasional (Level 1)"></option>
                            <option value="Lisensi 2 Daerah (Level 2)"></option>
                            <option value="Lisensi 3 Kabupaten/Kota"></option>
                        </datalist>
                    </div>
                    <div class="space-y-1">
                        <label class="block text-text-muted uppercase font-bold">Asal Kota / Asosiasi</label>
                        <input type="text" name="city" :value="editReferee ? editReferee.city : ''" placeholder="Bandung" class="w-full py-2 px-3 rounded bg-court-navy border border-court-border text-text-primary focus:border-stadium-emerald font-sans">
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="block text-text-muted uppercase font-bold">No. Kontak / HP / WhatsApp</label>
                    <input type="text" name="phone" :value="editReferee ? editReferee.phone : ''" placeholder="08123456789" class="w-full py-2 px-3 rounded bg-court-navy border border-court-border text-text-primary focus:border-stadium-emerald font-sans">
                </div>

                <div class="flex items-center gap-2 pt-1" x-show="editReferee">
                    <input type="checkbox" name="is_active" id="referee_active" value="1" :checked="editReferee && editReferee.is_active" class="rounded bg-court-navy border-court-border text-stadium-emerald focus:ring-stadium-emerald">
                    <label for="referee_active" class="text-text-primary cursor-pointer">Wasit ini aktif dan siap ditugaskan memimpin laga</label>
                </div>

                <div class="pt-4 flex items-center justify-end gap-3 border-t border-court-border/60">
                    <button type="button" @click="refereeModal = false" class="px-4 py-2 rounded bg-court-navy hover:bg-court-border text-text-muted font-bold transition-colors">
                        BATAL
                    </button>
                    <button type="submit" class="px-5 py-2 rounded bg-stadium-emerald hover:bg-stadium-emerald/90 text-court-navy font-headline font-bold uppercase transition-all shadow-[0_0_12px_rgba(0,255,135,0.3)]">
                        <span x-text="editReferee ? 'PERBARUI WASIT' : 'SIMPAN WASIT'">SIMPAN WASIT</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
