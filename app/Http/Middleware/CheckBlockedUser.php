<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckBlockedUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip admin routes - they use different auth guard
        if ($request->is('admin/*') || $request->is('admin')) {
            return $next($request);
        }

        // Skip API routes
        if ($request->is('api/*')) {
            return $next($request);
        }

        // Skip all AJAX/JSON requests - never block these
        if ($request->ajax() || $request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return $next($request);
        }

        // Only check regular web users (not admin guard)
        $user = $request->user();

        // If user is logged in and blocked
        if ($user && $user->status === 'blocked') {
            // Allow logout route
            if ($request->is('logout') || $request->is('*/logout')) {
                return $next($request);
            }

            // Allow blocked account page
            if ($request->is('account-blocked')) {
                return $next($request);
            }

            // Redirect to blocked page for page navigation only
            return redirect('/account-blocked');
        }

        return $next($request);
    }
}
