<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckBlockedAdmin
{
    /**
     * Handle an incoming request.
     * Check if the logged in admin is blocked/inactive.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only check admin guard
        if (!Auth::guard('admin')->check()) {
            return $next($request);
        }

        $admin = Auth::guard('admin')->user();

        // Check if admin is inactive
        if (!$admin->is_active) {
            // Allow logout route
            if ($request->is('admin/logout') || $request->is('*/logout')) {
                return $next($request);
            }

            // Allow blocked account page
            if ($request->is('admin/account-blocked')) {
                return $next($request);
            }

            // Skip AJAX/JSON requests - return error instead of redirect
            if ($request->ajax() || $request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'error' => 'Akun admin Anda telah dinonaktifkan.',
                    'blocked' => true
                ], 403);
            }

            // Redirect to blocked page
            return redirect('/admin/account-blocked');
        }

        return $next($request);
    }
}
