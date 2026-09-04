<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = Str::transliterate(Str::lower($request->input('email')).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            AuditLogService::logAuth('login.throttled', null, ['email' => $credentials['email']]);

            return back()->withErrors([
                'email' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ])->onlyInput('email');
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            $statusVal = $user->status instanceof UserStatus ? $user->status->value : $user->status;

            if ($statusVal !== 'ACTIVE') {
                Auth::logout();
                RateLimiter::hit($throttleKey);
                AuditLogService::logAuth('login.rejected_status', $user->id, ['status' => $statusVal]);

                $statusMsg = match($statusVal) {
                    'INACTIVE' => 'Your account is inactive.',
                    'SUSPENDED' => 'Your account is suspended.',
                    'LOCKED' => 'Your account is locked.',
                    default => 'Your account is disabled.',
                };

                return back()->withErrors(['email' => $statusMsg])->onlyInput('email');
            }

            RateLimiter::clear($throttleKey);

            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);

            AuditLogService::logAuth('login.success', $user->id);

            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        RateLimiter::hit($throttleKey);
        AuditLogService::logAuth('login.failed', null, ['email' => $credentials['email']]);

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request): RedirectResponse
    {
        if (Auth::check()) {
            AuditLogService::logAuth('logout', Auth::id());
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
