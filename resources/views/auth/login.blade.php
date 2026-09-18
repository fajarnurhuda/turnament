@extends('layouts.app')

@section('title', 'Operator Desk Authentication Portal - FUTSAL PRO CONTROL')

@section('content')
    <div class="min-h-[80vh] flex items-center justify-center px-4 py-12">
        <div class="w-full max-w-md space-y-8">
            <!-- Brand Header in Card -->
            <div class="text-center space-y-2">
                <div
                    class="inline-flex p-3 rounded-2xl bg-stadium-emerald/10 border border-stadium-emerald/30 text-stadium-emerald mb-2 shadow-[0_0_20px_rgba(0,255,135,0.2)]">
                    <span class="material-symbols-outlined text-4xl">terminal</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-headline font-black tracking-wider uppercase text-text-primary">
                    OPERATOR DESK <span class="text-stadium-emerald">PORTAL</span>
                </h1>
                <p class="text-xs font-mono text-text-muted">AUTHENTICATION GATEWAY &bull; WASIT MEJA & ADMIN</p>
            </div>

            <!-- Login Card Form -->
            <div class="p-8 rounded-xl bg-court-surface border border-court-border shadow-2xl space-y-6">
                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <!-- Email Input -->
                    <div class="space-y-1.5">
                        <label for="email"
                            class="block text-xs font-mono font-medium text-text-primary uppercase tracking-wider">
                            EMAIL OPERATOR / ADMIN
                        </label>
                        <div class="relative">
                            <span
                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-text-muted">
                                <span class="material-symbols-outlined text-sm">badge</span>
                            </span>
                            <input id="email" type="email" name="email" value="" required autofocus
                                placeholder="nama@futsal.test"
                                class="w-full pl-10 pr-4 py-2.5 rounded bg-court-navy border border-court-border focus:border-stadium-emerald focus:ring-1 focus:ring-stadium-emerald text-sm text-text-primary placeholder-text-muted/60 transition-all">
                        </div>
                    </div>

                    <!-- Password Input -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <label for="password"
                                class="block text-xs font-mono font-medium text-text-primary uppercase tracking-wider">
                                KATA SANDI / PIN
                            </label>
                        </div>
                        <div class="relative">
                            <span
                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-text-muted">
                                <span class="material-symbols-outlined text-sm">lock</span>
                            </span>
                            <input id="password" type="password" name="password" value="" required
                                placeholder="masukan password"
                                class="w-full pl-10 pr-4 py-2.5 rounded bg-court-navy border border-court-border focus:border-stadium-emerald focus:ring-1 focus:ring-stadium-emerald text-sm text-text-primary placeholder-text-muted/60 transition-all">
                        </div>
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between text-xs font-mono">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="remember"
                                class="rounded bg-court-navy border-court-border text-stadium-emerald focus:ring-stadium-emerald">
                            <span class="text-text-muted">Ingat sesi ini</span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit"
                        class="w-full py-3 px-4 rounded text-xs font-headline font-bold uppercase tracking-widest bg-stadium-emerald hover:bg-stadium-emerald/90 text-court-navy transition-all shadow-[0_0_15px_rgba(0,255,135,0.3)] flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-sm">lock_open</span>
                        OTORISASI & MASUK
                    </button>
                </form>
            </div>

            <!-- Back to Public Fan Center -->
            <div class="text-center">
                <a href="{{ route('home') }}"
                    class="text-xs font-mono text-text-muted hover:text-stadium-emerald transition-colors">
                    &larr; Kembali ke Tampilan Publik Fan Center
                </a>
            </div>
        </div>
    </div>
@endsection
