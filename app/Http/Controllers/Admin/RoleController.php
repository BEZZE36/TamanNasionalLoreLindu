<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RoleController extends Controller
{
    public function __construct()
    {
        // Check for permission to access role management
        $this->middleware(function ($request, $next) {
            $admin = \Illuminate\Support\Facades\Auth::guard('admin')->user();

            // Super admin has all permissions
            if ($admin->isSuperAdmin()) {
                return $next($request);
            }

            // Check if admin has 'access-roles' permission
            if (!$admin->hasPermission('access-roles')) {
                abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk mengelola role.');
            }

            return $next($request);
        });
    }

    public function index()
    {
        // Get actual counts from database
        $superAdminCount = Admin::where('role', 'super_admin')->count();
        $adminCount = Admin::where('role', 'admin')->count();
        $userCount = \App\Models\User::count();
        $guestCount = \App\Models\VisitorLog::count(); // Total visitor logs, not unique

        $roles = Role::withCount(['admins', 'permissions'])->get()
            ->map(function ($r) use ($superAdminCount, $adminCount, $userCount, $guestCount) {
                // Determine member count based on role type
                $memberCount = 0;
                $memberLabel = 'Admin';

                switch ($r->slug) {
                    case 'super-admin':
                        $memberCount = $superAdminCount;
                        $memberLabel = 'Super Admin';
                        break;
                    case 'admin':
                        $memberCount = $adminCount;
                        $memberLabel = 'Admin';
                        break;
                    case 'user':
                        $memberCount = $userCount;
                        $memberLabel = 'User';
                        break;
                    case 'guest':
                        $memberCount = $guestCount;
                        $memberLabel = 'Pengunjung';
                        break;
                    default:
                        $memberCount = $r->admins_count;
                        $memberLabel = 'Admin';
                }

                return [
                    'id' => $r->id,
                    'name' => $r->name,
                    'slug' => $r->slug,
                    'description' => $r->description,
                    'admins_count' => $r->admins_count,
                    'permissions_count' => $r->permissions_count,
                    'member_count' => $memberCount,
                    'member_label' => $memberLabel,
                ];
            });
        return Inertia::render('Admin/Roles/Index', ['roles' => $roles]);
    }

    public function create()
    {
        $permissions = Permission::all()->groupBy('group');
        return Inertia::render('Admin/Roles/Create', ['permissions' => $permissions]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:roles,slug',
            'description' => 'nullable|string',
            'permissions' => 'array',
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'description' => $validated['description'] ?? null,
        ]);

        if (!empty($validated['permissions'])) {
            $role->permissions()->sync($validated['permissions']);
        }

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role berhasil dibuat.');
    }

    public function edit(Role $role)
    {
        $permissions = Permission::all()->groupBy('group');
        $role->load('permissions');
        return Inertia::render('Admin/Roles/Edit', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'slug' => $role->slug,
                'description' => $role->description,
                'permissions' => $role->permissions->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'slug' => $p->slug])
            ],
            'permissions' => $permissions,
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'array',
        ]);

        // Debug log
        \Log::info('Role update', [
            'role_id' => $role->id,
            'role_slug' => $role->slug,
            'permissions_received' => $validated['permissions'] ?? [],
            'permissions_count' => count($validated['permissions'] ?? []),
        ]);

        $role->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        $role->permissions()->sync($validated['permissions'] ?? []);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role berhasil diperbarui.');
    }

    public function destroy(Role $role)
    {
        if ($role->slug === 'super-admin') {
            return redirect()->route('admin.roles.index')
                ->with('error', 'Role Super Admin tidak dapat dihapus.');
        }

        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role berhasil dihapus.');
    }

    // Admin role assignment
    public function admins()
    {
        $admins = Admin::with('roles')->get()->map(fn($a) => [
            'id' => $a->id,
            'name' => $a->name,
            'email' => $a->email,
            'role' => $a->role,
            'roles' => $a->roles->map(fn($r) => ['id' => $r->id, 'name' => $r->name, 'slug' => $r->slug]),
        ]);
        $roles = Role::active()->get()->map(fn($r) => ['id' => $r->id, 'name' => $r->name, 'slug' => $r->slug]);
        return Inertia::render('Admin/Roles/Admins', ['admins' => $admins, 'roles' => $roles]);
    }

    public function assignRole(Request $request, Admin $admin)
    {
        $request->validate([
            'roles' => 'array',
        ]);

        $admin->roles()->sync($request->roles ?? []);

        return redirect()->route('admin.roles.admins')
            ->with('success', 'Role admin berhasil diperbarui.');
    }
}
