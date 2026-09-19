<!DOCTYPE html>
<html class="dark" lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'LDII CUP TANJUNG PINANG - Sistem Manajemen Turnamen & Fan Center')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <!-- Early Theme Detection (Prevent FOUC) -->
    <script>
        (function() {
            try {
                const storedTheme = localStorage.getItem('theme');
                if (storedTheme === 'light') {
                    document.documentElement.classList.remove('dark');
                    document.documentElement.classList.add('light');
                } else {
                    document.documentElement.classList.add('dark');
                    document.documentElement.classList.remove('light');
                }
            } catch (e) {
                document.documentElement.classList.add('dark');
            }
        })();

        function toggleTheme() {
            const html = document.documentElement;
            const isDark = html.classList.contains('dark');
            if (isDark) {
                html.classList.remove('dark');
                html.classList.add('light');
                localStorage.setItem('theme', 'light');
            } else {
                html.classList.add('dark');
                html.classList.remove('light');
                localStorage.setItem('theme', 'dark');
            }
            window.dispatchEvent(new CustomEvent('theme-changed', {
                detail: { isDark: !isDark }
            }));
        }
    </script>

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
                        'court-navy': 'rgb(var(--court-navy) / <alpha-value>)',
                        'court-surface': 'rgb(var(--court-surface) / <alpha-value>)',
                        'court-surface-elevated': 'rgb(var(--court-surface-elevated) / <alpha-value>)',
                        'court-border': 'rgb(var(--court-border) / <alpha-value>)',
                        'stadium-emerald': 'rgb(var(--stadium-emerald) / <alpha-value>)',
                        'telemetry-cyan': 'rgb(var(--telemetry-cyan) / <alpha-value>)',
                        'live-pulse': 'rgb(var(--live-pulse) / <alpha-value>)',
                        'card-yellow': 'rgb(var(--card-yellow) / <alpha-value>)',
                        'card-red': 'rgb(var(--card-red) / <alpha-value>)',
                        'text-primary': 'rgb(var(--text-primary) / <alpha-value>)',
                        'text-muted': 'rgb(var(--text-muted) / <alpha-value>)',
                        'surface-container': 'rgb(var(--surface-container) / <alpha-value>)',
                        'surface-bright': 'rgb(var(--surface-bright) / <alpha-value>)',
                        'surface-dim': 'rgb(var(--surface-dim) / <alpha-value>)',
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
        :root {
            /* Light Mode Palette */
            --court-navy: 243 244 246;          /* #F3F4F6 Slate-100 */
            --court-surface: 255 255 255;       /* #FFFFFF Pure White */
            --court-surface-elevated: 248 250 252;/* #F8FAFC Slate-50 */
            --court-border: 226 232 240;        /* #E2E8F0 Slate-200 */
            --stadium-emerald: 5 150 105;       /* #059669 Emerald-600 */
            --telemetry-cyan: 2 132 199;        /* #0284C7 Sky-600 */
            --live-pulse: 225 29 72;            /* #E11D48 Rose-600 */
            --card-yellow: 217 119 6;           /* #D97706 Amber-600 */
            --card-red: 220 38 38;              /* #DC2626 Red-600 */
            --text-primary: 15 23 42;           /* #0F172A Slate-900 */
            --text-muted: 100 116 139;          /* #64748B Slate-500 */
            --surface-container: 241 245 249;   /* #F1F5F9 */
            --surface-bright: 226 232 240;      /* #E2E8F0 */
            --surface-dim: 255 255 255;
        }

        html.dark {
            /* Cyberpunk Stadium Dark Mode Palette */
            --court-navy: 11 14 23;             /* #0B0E17 Deep Navy */
            --court-surface: 18 24 38;          /* #121826 Surface Card */
            --court-surface-elevated: 26 35 56; /* #1A2338 Elevated */
            --court-border: 34 48 76;           /* #22304C Court Border */
            --stadium-emerald: 0 255 135;       /* #00FF87 Neon Emerald */
            --telemetry-cyan: 0 229 255;        /* #00E5FF Neon Cyan */
            --live-pulse: 255 51 102;           /* #FF3366 Neon Pink/Red */
            --card-yellow: 255 214 0;           /* #FFD600 Neon Yellow */
            --card-red: 255 42 77;              /* #FF2A4D Vibrant Red */
            --text-primary: 240 246 252;        /* #F0F6FC Bright White */
            --text-muted: 121 143 168;          /* #798FA8 Muted Blue */
            --surface-container: 28 31 41;      /* #1c1f29 */
            --surface-bright: 54 57 67;         /* #363943 */
            --surface-dim: 16 19 28;            /* #10131c */
        }

        [x-cloak] { display: none !important; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: rgb(var(--court-navy)); }
        ::-webkit-scrollbar-thumb { background: rgb(var(--court-border)); border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: rgb(var(--stadium-emerald)); }
    </style>
    @stack('styles')
</head>
<body class="bg-court-navy text-text-primary font-sans antialiased min-h-screen flex flex-col selection:bg-stadium-emerald selection:text-white dark:selection:text-court-navy transition-colors duration-200">

    <!-- Top Navigation Bar -->
    <header class="sticky top-0 z-30 bg-court-navy/90 backdrop-blur-md border-b border-court-border">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Brand / Logo -->
                <div class="flex items-center gap-4">
                    <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                        <div class="w-10 h-10 rounded-lg bg-court-surface border border-court-border p-1 flex items-center justify-center group-hover:border-stadium-emerald/50 group-hover:shadow-[0_0_15px_rgba(0,255,135,0.3)] transition-all overflow-hidden flex-shrink-0">
                            <img src="{{ asset('images/logo.png') }}" alt="LDII CUP TANJUNG PINANG" class="w-full h-full object-contain">
                        </div>
                        <div>
                            <span class="font-headline font-bold text-base sm:text-lg tracking-wider text-text-primary uppercase flex items-center gap-2 leading-none">
                                LDII CUP <span class="text-stadium-emerald">TANJUNG PINANG</span>
                            </span>
                            <span class="text-[9px] font-mono tracking-widest text-text-muted block mt-1">SISTEM MANAJEMEN TURNAMEN</span>
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
                <div class="flex items-center gap-2 sm:gap-3">
                    <!-- Visitor Telemetry: Live Online & Total Views -->
                    <div id="visitor-telemetry" class="hidden sm:flex items-center gap-2.5 px-3 py-1.5 rounded-lg bg-court-surface border border-court-border text-xs font-mono select-none shadow-sm" title="Statistik Pengunjung Turnamen">
                        <!-- Active Online Counter -->
                        <div class="flex items-center gap-1.5" title="Penonton Aktif (5 Menit Terakhir)">
                            <span class="w-2 h-2 rounded-full bg-stadium-emerald animate-pulse flex-shrink-0"></span>
                            <span id="visitor-online-count" class="text-stadium-emerald font-bold">{{ $visitorStats['online_formatted'] ?? '1' }}</span>
                            <span class="text-[10px] text-text-muted font-sans font-medium uppercase tracking-wider">Online</span>
                        </div>

                        <span class="text-court-border/80 font-light">|</span>

                        <!-- Total Views Counter -->
                        <div class="flex items-center gap-1.5" title="Total Akumulasi Kunjungan Turnamen">
                            <span class="material-symbols-outlined text-[13px] text-telemetry-cyan flex-shrink-0">visibility</span>
                            <span id="visitor-total-count" class="text-text-primary font-bold">{{ $visitorStats['total_formatted'] ?? '1' }}</span>
                            <span class="text-[10px] text-text-muted font-sans font-medium uppercase tracking-wider">Views</span>
                        </div>
                    </div>

                    <!-- Theme Mode Toggle Button -->
                    <button type="button" onclick="toggleTheme()"
                        class="p-2 sm:px-3 sm:py-1.5 rounded-lg bg-court-surface hover:bg-court-surface-elevated border border-court-border text-text-muted hover:text-text-primary text-xs font-mono flex items-center gap-1.5 transition-all shadow-sm focus:outline-none"
                        title="Ubah Mode Tampilan (Terang / Gelap)">
                        <span class="material-symbols-outlined text-sm text-card-yellow hidden dark:inline">light_mode</span>
                        <span class="material-symbols-outlined text-sm text-telemetry-cyan inline dark:hidden">dark_mode</span>
                        <span class="hidden sm:inline-block font-bold">
                            <span class="hidden dark:inline">TERANG</span>
                            <span class="inline dark:hidden">GELAP</span>
                        </span>
                    </button>

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
                        <a href="{{ route('login') }}" title="Login Operator & Admin" class="p-2 sm:px-4 sm:py-2 rounded text-xs font-headline font-bold bg-stadium-emerald hover:bg-stadium-emerald/90 text-court-navy transition-all shadow-[0_0_12px_rgba(0,255,135,0.2)] flex items-center gap-1.5 flex-shrink-0">
                            <span class="material-symbols-outlined text-base">login</span>
                            <span class="hidden sm:inline">LOGIN OPERATOR</span>
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
            <div class="flex flex-col sm:flex-row items-center gap-2 sm:gap-3 text-center sm:text-left">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-stadium-emerald"></span>
                    <span>LDII CUP TANJUNG PINANG &copy; {{ date('Y') }} — SISTEM MANAJEMEN TURNAMEN</span>
                </div>
                <!-- Mobile Visitor Stats Pill -->
                <div class="sm:hidden flex items-center gap-2 px-2.5 py-1 rounded bg-court-surface border border-court-border text-[11px]" title="Statistik Pengunjung Turnamen">
                    <span class="flex items-center gap-1 text-stadium-emerald font-bold">
                        <span class="w-1.5 h-1.5 rounded-full bg-stadium-emerald animate-pulse"></span>
                        <span class="visitor-online-text">{{ $visitorStats['online_formatted'] ?? '1' }}</span> Online
                    </span>
                    <span class="text-court-border">|</span>
                    <span class="flex items-center gap-1 text-text-primary">
                        <span class="material-symbols-outlined text-[12px] text-telemetry-cyan">visibility</span>
                        <span class="visitor-total-text">{{ $visitorStats['total_formatted'] ?? '1' }}</span> Views
                    </span>
                </div>
            </div>
            <div class="flex items-center gap-1.5">
                <span>Developer : <span class="text-text-primary font-medium">Fajar Nurhuda (Mekarsari)</span> - <a href="https://wa.me/6282284066470" target="_blank" rel="noopener noreferrer" class="text-stadium-emerald hover:underline">0822 8406 6470</a></span>
            </div>
        </div>
    </footer>

    <!-- Global Double-Submit Prevention Guard -->
    <script>
        document.addEventListener('submit', function (e) {
            const form = e.target;
            if (!form || form.tagName !== 'FORM') return;

            // If form is already submitting, block repeat submissions
            if (form.dataset.submitting === 'true') {
                e.preventDefault();
                e.stopImmediatePropagation();
                return false;
            }

            form.dataset.submitting = 'true';

            // Locate submit button
            const submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');
            if (submitBtn) {
                submitBtn.dataset.originalHtml = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-60', 'cursor-not-allowed', 'pointer-events-none');

                if (submitBtn.innerText && submitBtn.innerText.trim().length > 2) {
                    submitBtn.innerHTML = '<span class="inline-flex items-center justify-center gap-1.5"><span class="material-symbols-outlined text-sm animate-spin">progress_activity</span> Menyimpan...</span>';
                }
            }

            // Safety timeout (5s) to release lock in case of network interruption
            setTimeout(function () {
                form.dataset.submitting = 'false';
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('opacity-60', 'cursor-not-allowed', 'pointer-events-none');
                    if (submitBtn.dataset.originalHtml) {
                        submitBtn.innerHTML = submitBtn.dataset.originalHtml;
                    }
                }
            }, 5000);
        }, true);

        // Reset any locked forms on page navigation restore (back-forward cache)
        window.addEventListener('pageshow', function () {
            document.querySelectorAll('form[data-submitting="true"]').forEach(function (f) {
                f.dataset.submitting = 'false';
                const btn = f.querySelector('button[type="submit"], input[type="submit"]');
                if (btn && btn.dataset.originalHtml) {
                    btn.disabled = false;
                    btn.classList.remove('opacity-60', 'cursor-not-allowed', 'pointer-events-none');
                    btn.innerHTML = btn.dataset.originalHtml;
                }
            });
        });

        // Periodic background visitor stats sync (30s)
        setInterval(function() {
            fetch('{{ route('api.visitor-stats') }}')
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    if (!data) return;
                    const onlineEls = document.querySelectorAll('#visitor-online-count, .visitor-online-text');
                    const totalEls = document.querySelectorAll('#visitor-total-count, .visitor-total-text');
                    onlineEls.forEach(function(el) { if (data.online_formatted) el.innerText = data.online_formatted; });
                    totalEls.forEach(function(el) { if (data.total_formatted) el.innerText = data.total_formatted; });
                })
                .catch(function() {});
        }, 30000);
    </script>

    @stack('scripts')
</body>
</html>
