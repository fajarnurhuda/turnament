<!DOCTYPE html>
<html class="dark" lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'FUTSAL PRO CONTROL - Sistem Manajemen Turnamen & Fan Center')</title>

    <!-- Google Fonts: Space Grotesk, Inter, JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;600;700;800&display=swap" rel="stylesheet">

    <!-- Material Symbols Outlined -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">

    <!-- Tailwind CSS with custom theme matching stitch DESIGN.md -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        'court-navy': '#0B0E17',
                        'court-surface': '#121826',
                        'court-surface-elevated': '#1A2338',
                        'court-border': '#22304C',
                        'stadium-emerald': '#00FF87',
                        'telemetry-cyan': '#00E5FF',
                        'live-pulse': '#FF3366',
                        'card-yellow': '#FFD600',
                        'card-red': '#FF2A4D',
                        'text-primary': '#F0F6FC',
                        'text-muted': '#798FA8',
                        'surface-container': '#1c1f29',
                        'surface-bright': '#363943',
                        'surface-dim': '#10131c'
                    },
                    fontFamily: {
                        'sans': ['Inter', 'sans-serif'],
                        'headline': ['Space Grotesk', 'sans-serif'],
                        'mono': ['JetBrains Mono', 'monospace'],
                    },
                    borderRadius: {
                        'DEFAULT': '0.25rem',
                        'md': '0.375rem',
                        'lg': '0.5rem',
                        'xl': '0.75rem',
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #0B0E17; }
        ::-webkit-scrollbar-thumb { background: #22304C; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #00FF87; }
    </style>
    @stack('styles')
</head>
<body class="bg-court-navy text-text-primary font-sans antialiased min-h-screen flex flex-col selection:bg-stadium-emerald selection:text-court-navy">

    <!-- Top Navigation Bar -->
    <header class="sticky top-0 z-40 bg-court-navy/90 backdrop-blur-md border-b border-court-border">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Brand / Logo -->
                <div class="flex items-center gap-4">
                    <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                        <div class="w-10 h-10 rounded bg-stadium-emerald/10 border border-stadium-emerald/30 flex items-center justify-center text-stadium-emerald group-hover:shadow-[0_0_15px_rgba(0,255,135,0.4)] transition-all">
                            <span class="material-symbols-outlined text-2xl">sports_soccer</span>
                        </div>
                        <div>
                            <span class="font-headline font-bold text-lg tracking-wider text-text-primary uppercase flex items-center gap-2">
                                FUTSAL <span class="text-stadium-emerald">PRO</span> CONTROL
                            </span>
                            <span class="text-[10px] font-mono tracking-widest text-text-muted block -mt-1">PITCHPULSE TELEMETRY</span>
                        </div>
                    </a>
                </div>

                <!-- Navigation Links -->
                <nav class="hidden md:flex items-center gap-2">
                    <a href="{{ route('home') }}" class="px-3 py-2 rounded text-sm font-medium transition-colors {{ request()->routeIs('home') ? 'bg-stadium-emerald/10 text-stadium-emerald border border-stadium-emerald/30' : 'text-text-muted hover:text-text-primary hover:bg-court-surface' }}">
                        <span class="flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-sm">stadium</span>
                            Fan Center
                        </span>
                    </a>

                    @auth
                        <a href="{{ route('admin.fixtures') }}" class="px-3 py-2 rounded text-sm font-medium transition-colors {{ request()->routeIs('admin.fixtures*') ? 'bg-stadium-emerald/10 text-stadium-emerald border border-stadium-emerald/30' : 'text-text-muted hover:text-text-primary hover:bg-court-surface' }}">
                            <span class="flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-sm">calendar_month</span>
                                Jadwal & Fixture
                            </span>
                        </a>

                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="px-3 py-2 rounded text-sm font-medium transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-stadium-emerald/10 text-stadium-emerald border border-stadium-emerald/30' : 'text-text-muted hover:text-text-primary hover:bg-court-surface' }}">
                                <span class="flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-sm">database</span>
                                    Master Data
                                </span>
                            </a>
                        @endif
                    @endauth
                </nav>

                <!-- Right Action Bar: Status & Auth -->
                <div class="flex items-center gap-3">
                    <!-- Live Ticker Indicator -->
                    <div class="hidden sm:flex items-center gap-2 px-3 py-1 rounded bg-court-surface border border-court-border text-xs font-mono">
                        <span class="w-2 h-2 rounded-full bg-stadium-emerald animate-pulse"></span>
                        <span class="text-text-muted">STATUS:</span>
                        <span class="text-stadium-emerald font-bold">ONLINE</span>
                    </div>

                    @auth
                        <div class="flex items-center gap-3">
                            <div class="text-right hidden sm:block">
                                <span class="text-xs font-medium text-text-primary block">{{ auth()->user()->name }}</span>
                                @if(auth()->user()->isAdmin())
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-mono uppercase bg-stadium-emerald/10 text-stadium-emerald border border-stadium-emerald/30">ADMINISTRATOR</span>
                                @else
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-mono uppercase bg-card-yellow/10 text-card-yellow border border-card-yellow/30">WASIT MEJA</span>
                                @endif
                            </div>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" title="Keluar" class="p-2 rounded bg-court-surface hover:bg-card-red/20 text-text-muted hover:text-card-red border border-court-border transition-colors">
                                    <span class="material-symbols-outlined text-base">logout</span>
                                </button>
                            </form>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="px-4 py-2 rounded text-xs font-headline font-bold bg-stadium-emerald hover:bg-stadium-emerald/90 text-court-navy transition-all shadow-[0_0_12px_rgba(0,255,135,0.2)] flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-base">login</span>
                            LOGIN OPERATOR
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <!-- Flash Messages -->
    @if(session('success') || session('info') || $errors->any())
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
            @if(session('success'))
                <div class="p-4 rounded bg-stadium-emerald/10 border border-stadium-emerald/30 text-stadium-emerald flex items-center gap-3 text-sm">
                    <span class="material-symbols-outlined">check_circle</span>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('info'))
                <div class="p-4 rounded bg-telemetry-cyan/10 border border-telemetry-cyan/30 text-telemetry-cyan flex items-center gap-3 text-sm">
                    <span class="material-symbols-outlined">info</span>
                    <span>{{ session('info') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="p-4 rounded bg-card-red/10 border border-card-red/30 text-card-red flex items-start gap-3 text-sm">
                    <span class="material-symbols-outlined mt-0.5">error</span>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    @endif

    <!-- Main Content -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-court-navy border-t border-court-border py-6 mt-12 text-xs font-mono text-text-muted">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-stadium-emerald"></span>
                <span>FUTSAL PRO CONTROL &copy; {{ date('Y') }} — PITCHPULSE TELEMETRY ENGINE</span>
            </div>
            <div class="flex items-center gap-6">
                <span>MYSQL 8.0</span>
                <span>LARAVEL 13</span>
                <span class="text-stadium-emerald">REAL-TIME TELEMETRY READY</span>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
