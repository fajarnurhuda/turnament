@extends('layouts.app')

@section('title', 'Operator Desk Authentication Portal - LDII CUP TANJUNG PINANG')

@section('content')
    <div class="min-h-[80vh] flex items-center justify-center px-4 py-12">
        <div class="w-full max-w-md space-y-8">
            <!-- Brand Header in Card -->
            <div class="text-center space-y-2">
                <div
                    class="inline-flex p-2.5 rounded-2xl bg-court-surface border border-court-border mb-2 shadow-[0_0_25px_rgba(0,255,135,0.2)]">
                    <img src="{{ asset('images/logo.png') }}" alt="LDII CUP TANJUNG PINANG" class="w-16 h-16 object-contain">
                </div>
                <h1 class="text-2xl sm:text-3xl font-headline font-black tracking-wider uppercase text-text-primary">
                    LDII CUP <span class="text-stadium-emerald">TANJUNG PINANG</span>
                </h1>
                <p class="text-xs font-mono text-text-muted">PORTAL OPERATOR DESK &bull; WASIT MEJA & ADMIN</p>
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
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required
                                autofocus placeholder="nama@futsal.test"
                                class="w-full pl-10 pr-4 py-2.5 rounded bg-court-navy border border-court-border focus:border-stadium-emerald focus:ring-1 focus:ring-stadium-emerald text-sm text-text-primary placeholder-text-muted/60 transition-all">
                        </div>
                    </div>

                    <!-- Password Input -->
                    <div class="space-y-1.5" x-data="{ showPassword: false }">
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
                            <input id="password" :type="showPassword ? 'text' : 'password'" type="password" name="password"
                                value="" required placeholder="masukan password"
                                class="w-full pl-10 pr-10 py-2.5 rounded bg-court-navy border border-court-border focus:border-stadium-emerald focus:ring-1 focus:ring-stadium-emerald text-sm text-text-primary placeholder-text-muted/60 transition-all">
                            <button type="button" @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-text-muted hover:text-text-primary transition-colors focus:outline-none"
                                :title="showPassword ? 'Sembunyikan Sandi' : 'Lihat Sandi'">
                                <span class="material-symbols-outlined text-lg"
                                    x-text="showPassword ? 'visibility_off' : 'visibility'">visibility</span>
                            </button>
                        </div>
                    </div>

                    <!-- Captcha Verification -->
                    <div class="space-y-1.5" x-data="{ captchaUrl: '{{ route('captcha') }}' }">
                        <div class="flex items-center justify-between">
                            <label for="captcha"
                                class="block text-xs font-mono font-medium text-text-primary uppercase tracking-wider">
                                KODE KEAMANAN (CAPTCHA)
                            </label>
                            <span class="text-[10px] font-mono text-text-muted">KLIK GAMBAR UNTUK REFRESH</span>
                        </div>
                        <div class="flex items-center gap-2.5">
                            <div class="relative group cursor-pointer border border-court-border rounded overflow-hidden flex-shrink-0 bg-court-navy hover:border-stadium-emerald transition-all shadow-inner"
                                @click="captchaUrl = '{{ route('captcha') }}?v=' + Date.now()"
                                title="Klik untuk ganti kode CAPTCHA">
                                <img :src="captchaUrl" alt="Kode Keamanan"
                                    class="h-11 w-36 object-contain block select-none">
                                <div
                                    class="absolute inset-0 bg-stadium-emerald/10 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center pointer-events-none">
                                    <span
                                        class="material-symbols-outlined text-xs text-stadium-emerald bg-court-navy/80 rounded-full p-0.5">sync</span>
                                </div>
                            </div>
                            <button type="button" @click="captchaUrl = '{{ route('captcha') }}?v=' + Date.now()"
                                class="h-11 px-3 rounded bg-court-navy hover:bg-court-surface border border-court-border hover:border-stadium-emerald text-text-muted hover:text-stadium-emerald transition-colors flex items-center justify-center"
                                title="Perbarui Kode CAPTCHA">
                                <span class="material-symbols-outlined text-lg">refresh</span>
                            </button>
                            <div class="relative flex-grow">
                                <input id="captcha" type="text" name="captcha" required maxlength="6"
                                    autocomplete="off" placeholder="Ketik kode"
                                    class="w-full h-11 px-3.5 rounded bg-court-navy border border-court-border focus:border-stadium-emerald focus:ring-1 focus:ring-stadium-emerald text-sm font-mono tracking-widest uppercase text-text-primary placeholder-text-muted/60 transition-all">
                            </div>
                        </div>
                        @error('captcha')
                            <p class="text-[11px] font-mono text-card-red mt-1 flex items-center gap-1">
                                <span class="material-symbols-outlined text-xs">error</span>
                                {{ $message }}
                            </p>
                        @enderror
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
