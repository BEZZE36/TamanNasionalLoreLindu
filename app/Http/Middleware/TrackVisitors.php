<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackVisitors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // Skip if admin or API or resource
        if ($request->is('admin*') || $request->is('api*') || $request->is('storage*')) {
            return $next($request);
        }

        // Simple bot filter (very basic)
        $userAgent = $request->header('User-Agent');
        if (str_contains(strtolower($userAgent), 'bot') || str_contains(strtolower($userAgent), 'spider')) {
            return $next($request);
        }

        try {
            \App\Models\VisitorLog::create([
                'ip_address' => $request->ip(),
                'url' => $request->fullUrl(),
                'user_agent' => $userAgent,
                'user_id' => auth()->id(),
            ]);
        } catch (\Exception $e) {
            // Do nothing if logging fails
        }

        return $next($request);
    }
}
