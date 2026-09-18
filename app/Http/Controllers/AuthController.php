<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\CaptchaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        protected CaptchaService $captchaService
    ) {}

    /**
     * Show Operator & Admin Login Portal.
     */
    public function showLogin(): View
    {
        return view('auth.login');
    }

    /**
     * Serve Dynamic SVG CAPTCHA Image.
     */
    public function captcha(): Response
    {
        $svg = $this->captchaService->generate();

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * Handle Authentication Request.
     */
    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'captcha' => ['required', 'string'],
        ], [
            'captcha.required' => 'Kode CAPTCHA wajib diisi.',
        ]);

        if (! $this->captchaService->verify($request->input('captcha'))) {
            return back()->withErrors([
                'captcha' => 'Kode CAPTCHA tidak cocok atau telah kedaluwarsa. Silakan coba lagi.',
            ])->onlyInput('email');
        }

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            /** @var User $user */
            $user = Auth::user();
            $defaultRoute = $user->isAdmin() ? route('admin.dashboard') : route('admin.fixtures');

            return redirect()->intended($defaultRoute)
                ->with('success', 'Berhasil masuk ke portal turnamen sebagai '.($user->isAdmin() ? 'Administrator' : 'Wasit Meja / Operator').'.');
        }

        return back()->withErrors([
            'email' => 'Kredensial atau kode operator yang dimasukkan tidak cocok.',
        ])->onlyInput('email');
    }

    /**
     * Quick Demo Login Helper for Testing.
     */
    public function quickLogin(string $role): RedirectResponse
    {
        $email = $role === 'admin' ? 'admin@futsal.test' : 'operator@futsal.test';

        if (Auth::attempt(['email' => $email, 'password' => 'password'])) {
            request()->session()->regenerate();

            /** @var User $user */
            $user = Auth::user();
            $targetRoute = $user->isAdmin() ? 'admin.dashboard' : 'admin.fixtures';

            return redirect()->route($targetRoute)
                ->with('success', 'Masuk cepat sebagai '.($user->isAdmin() ? 'Administrator Turnamen' : 'Wasit Meja (Operator)'));
        }

        return redirect()->route('login');
    }

    /**
     * Logout.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')
            ->with('info', 'Sesi operator telah ditutup.');
    }
}
