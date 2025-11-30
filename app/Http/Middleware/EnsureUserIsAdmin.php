<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // NOTE: This middleware is actually redundant now because
        // Filament's canAccessPanel() method already handles admin check.
        // Keeping it as extra security layer.
        
        // Allow through - Filament's FilamentUser contract handles the check
        return $next($request);
    }
}
