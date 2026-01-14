<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMenuAccess
{
    /**
     * Permission groups mapped to routes
     * Format: 'group' => ['route_prefix']
     */
    private $menuGroups = [
        'dashboard' => '/admin/dashboard',
        'destinations' => '/admin/destinations',
        'flora' => '/admin/flora',
        'fauna' => '/admin/fauna',
        'gallery' => '/admin/gallery',
        'announcements' => '/admin/announcements',
        'articles' => '/admin/articles',
        'news' => '/admin/news',
        'newsletter' => '/admin/newsletter',
        'testimonial' => '/admin/testimonial',
        'site-info' => '/admin/site-info',
        'bookings' => '/admin/bookings',
        'coupons' => '/admin/coupons',
        'users' => '/admin/users',
        'tickets' => '/admin/tickets',
        'activity-logs' => '/admin/activity-logs',
        'analytics' => '/admin/analytics',
        'admins' => '/admin/admins',
        'roles' => '/admin/roles',
        'database' => '/admin/database',
    ];

    /**
     * Handle permission check for admin routes
     * 
     * 3 Level Permission:
     * - access-* = Menu visible but page LOCKED (403)
     * - read-*   = Can view page, but CANNOT write (forms locked, POST/PUT/DELETE blocked)
     * - write-*  = Full access (can do everything)
     */
    public function handle(Request $request, Closure $next, ?string $group = null): Response
    {
        $admin = auth('admin')->user();

        if (!$admin) {
            return redirect()->route('admin.login');
        }

        // Super admin bypasses all checks
        if ($admin->isSuperAdmin()) {
            // Share permission level for frontend
            $request->attributes->set('permissionLevel', 'write');
            return $next($request);
        }

        // Auto-detect group from route if not specified
        if (!$group) {
            $group = $this->detectGroupFromRoute($request);
        }

        if (!$group) {
            return $next($request);
        }

        // Get permission level for this menu
        $hasAccess = $admin->hasPermission("access-{$group}");
        $hasRead = $admin->hasPermission("read-{$group}");
        $hasWrite = $admin->hasPermission("write-{$group}") ||
            $admin->hasPermission("scan-{$group}");

        // Determine permission level
        $permissionLevel = 'none';
        if ($hasWrite) {
            $permissionLevel = 'write';
        } elseif ($hasRead) {
            $permissionLevel = 'read';
        } elseif ($hasAccess) {
            $permissionLevel = 'access';
        }

        // Store permission level for frontend
        $request->attributes->set('permissionLevel', $permissionLevel);
        $request->attributes->set('menuGroup', $group);

        // LEVEL: NONE - No access at all
        if ($permissionLevel === 'none') {
            return $this->denyAccess($request, 'Anda tidak memiliki akses ke menu ini.');
        }

        // LEVEL: ACCESS ONLY - Can see menu but cannot open page
        if ($permissionLevel === 'access') {
            return $this->denyAccess($request, 'Menu ini hanya untuk ditampilkan. Anda tidak memiliki izin untuk membuka halaman ini.');
        }

        // LEVEL: READ ONLY - Can view but cannot write
        if ($permissionLevel === 'read') {
            $isWriteOperation = in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE']);

            if ($isWriteOperation) {
                return $this->denyWrite($request, 'Anda hanya memiliki akses Read Only. Tidak dapat melakukan perubahan.');
            }
        }

        // LEVEL: WRITE - Full access, allow everything
        return $next($request);
    }

    /**
     * Detect permission group from current route
     */
    private function detectGroupFromRoute(Request $request): ?string
    {
        $path = '/' . ltrim($request->path(), '/');

        foreach ($this->menuGroups as $group => $routePrefix) {
            if (str_starts_with($path, $routePrefix)) {
                return $group;
            }
        }

        return null;
    }

    /**
     * Deny access to page
     */
    private function denyAccess(Request $request, string $message): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'error' => 'access_denied'
            ], 403);
        }

        abort(403, $message);
    }

    /**
     * Deny write operation
     */
    private function denyWrite(Request $request, string $message): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'error' => 'read_only',
                'read_only' => true
            ], 403);
        }

        return back()->with('error', $message);
    }
}
