<?php

namespace App\Http\Controllers;

use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return Auth::user()->isAdmin()
                ? redirect()->route('admin.dashboard')
                : redirect()->route('client.dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            if (! $user->isActive()) {
                Auth::logout();
                AuditLogger::log('login_failed_suspended', "Attempted login to suspended account: {$credentials['email']}");
                return back()->withErrors(['email' => 'Your account has been suspended.']);
            }

            $request->session()->regenerate();
            AuditLogger::log('user_login', "User {$user->name} logged in successfully.", $user->id);

            return $user->isAdmin()
                ? redirect()->intended(route('admin.dashboard'))
                : redirect()->intended(route('client.dashboard'));
        }

        AuditLogger::log('login_failed', "Failed login attempt for email: {$credentials['email']}");

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if ($user) {
            AuditLogger::log('user_logout', "User {$user->name} logged out.", $user->id);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'You have been logged out successfully.');
    }
}
