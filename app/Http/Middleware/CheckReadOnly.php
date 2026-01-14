<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckReadOnly
{
    /**
     * Permission groups mapped to their menu routes
     */
    private $menuGroups = [
        'dashboard' => ['/admin/dashboard'],
        'destinations' => ['/admin/destinations'],
        'flora' => ['/admin/flora'],
        'fauna' => ['/admin/fauna'],
        'gallery' => ['/admin/gallery'],
        'announcements' => ['/admin/announcements'],
        'articles' => ['/admin/articles'],
        'news' => ['/admin/news'],
        'newsletter' => ['/admin/newsletter'],
        'site-info' => ['/admin/site-info'],
        'bookings' => ['/admin/bookings'],
        'coupons' => ['/admin/coupons'],
        'users' => ['/admin/users'],
        'tickets' => ['/admin/tickets'],
        'activity-logs' => ['/admin/activity-logs'],
        'analytics' => ['/admin/analytics'],
        'admins' => ['/admin/admins'],
        'roles' => ['/admin/roles'],
        'database' => ['/admin/database'],
        'feedback' => ['/admin/feedback'],
    ];

    /**
     * Handle an incoming request.
     * Check if admin has write permission for the current menu group.
     * If only read permission, block POST/PUT/PATCH/DELETE requests.
     */
    public function handle(Request $request, Closure $next, ?string $group = null): Response
    {
        $admin = auth('admin')->user();

        if (!$admin) {
            return redirect()->route('admin.login');
        }

        // Super admin has all permissions
        if ($admin->isSuperAdmin()) {
            return $next($request);
        }

        // Auto-detect group from route if not specified
        if (!$group) {
            $group = $this->detectGroupFromRoute($request);
        }

        if (!$group) {
            return $next($request);
        }

        // Check read-only mode for write operations
        $isWriteOperation = in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE']);

        if ($isWriteOperation) {
            $hasWritePermission = $admin->hasPermission("write-{$group}") ||
                $admin->hasPermission("scan-{$group}") ||
                $admin->hasPermission("export-{$group}");

            if (!$hasWritePermission) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Anda hanya memiliki akses Read Only untuk menu ini.',
                        'read_only' => true
                    ], 403);
                }

                return back()->with('error', 'Anda hanya memiliki akses Read Only untuk menu ini.');
            }
        }

        return $next($request);
    }

    /**
     * Detect permission group from current route
     */
    private function detectGroupFromRoute(Request $request): ?string
    {
        $path = $request->path();

        foreach ($this->menuGroups as $group => $routes) {
            foreach ($routes as $route) {
                $routePath = ltrim($route, '/');
                if (str_starts_with($path, $routePath)) {
                    return $group;
                }
            }
        }

        return null;
    }
}
