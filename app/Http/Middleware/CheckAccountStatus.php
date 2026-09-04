<?php

namespace App\Http\Middleware;

use App\Enums\UserStatus;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAccountStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user) {
            $statusVal = $user->status instanceof UserStatus ? $user->status->value : $user->status;

            if ($statusVal !== 'ACTIVE') {
                Auth::logout();

                $request->session()->invalidate();
                $request->session()->regenerateToken();

                $statusMsg = match($statusVal) {
                    'INACTIVE' => 'Your account is inactive. Please contact system administration.',
                    'SUSPENDED' => 'Your account has been suspended. Please contact customer service.',
                    'LOCKED' => 'Your account is locked due to security policy.',
                    default => 'Your account status prevents access.',
                };

                return redirect()->route('login')->withErrors([
                    'email' => $statusMsg,
                ]);
            }
        }

        return $next($request);
    }
}
