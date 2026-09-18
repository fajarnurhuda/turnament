<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Show Operator & Admin Login Portal.
     */
    public function showLogin(): View
    {
        return view('auth.login');
    }

    /**
     * Handle Authentication Request.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

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
