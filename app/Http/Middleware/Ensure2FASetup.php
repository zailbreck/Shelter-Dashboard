<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensure 2FA Setup Middleware
 * 
 * Forces users to complete 2FA setup before accessing any other routes
 */
class Ensure2FASetup
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // If user is authenticated and 2FA is not enabled
        if ($user && !$user->google2fa_enabled) {
            // Allow access only to 2FA setup and logout routes
            if (
                !$request->routeIs('2fa.setup') &&
                !$request->routeIs('2fa.verify-setup') &&
                !$request->routeIs('logout')
            ) {
                return redirect()->route('2fa.setup');
            }
        }

        return $next($request);
    }
}
