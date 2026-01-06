<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function __construct()
    {
        // Check for permission to access admin management
        $this->middleware(function ($request, $next) {
            $admin = Auth::guard('admin')->user();

            // Super admin has all permissions
            if ($admin->isSuperAdmin()) {
                return $next($request);
            }

            // Check if admin has 'access-admins' permission
            if (!$admin->hasPermission('access-admins')) {
                abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk mengelola akun admin.');
            }

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $currentAdminId = Auth::guard('admin')->id();

        // Build query with filters
        $query = Admin::with('creator', 'roles');

        // Search filter
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($status = $request->input('status')) {
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Role filter
        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        // Order by role priority and name
        $query->orderByRaw("role = 'super_admin' DESC")
            ->orderBy('name');

        // Paginate
        $admins = $query->paginate(10)->withQueryString();

        // Transform data
        $admins->getCollection()->transform(fn($admin) => [
            'id' => $admin->id,
            'name' => $admin->name,
            'email' => $admin->email,
            'username' => $admin->username,
            'role' => $admin->role,
            'is_active' => $admin->is_active,
            'created_at' => $admin->created_at,
            'creator' => $admin->creator ? ['name' => $admin->creator->name] : null,
            'roles' => $admin->roles->map(fn($r) => ['id' => $r->id, 'name' => $r->name, 'slug' => $r->slug]),
            'can_edit' => !$admin->isSuperAdmin() || $admin->id === $currentAdminId,
            'can_delete' => !$admin->isSuperAdmin() && $admin->id !== $currentAdminId,
        ]);

        // Calculate stats
        $stats = [
            'total' => Admin::count(),
            'active' => Admin::where('is_active', true)->count(),
            'inactive' => Admin::where('is_active', false)->count(),
            'super_admin' => Admin::where('role', 'super_admin')->count(),
        ];

        return Inertia::render('Admin/Admins/Index', [
            'admins' => $admins,
            'stats' => $stats,
            'filters' => $request->only(['search', 'status', 'role']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Admins/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email',
            'username' => 'required|string|max:50|unique:admins,username|alpha_dash',
            'password' => ['required', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()],
            'role' => 'required|in:admin,super_admin',
            'is_active' => 'boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['created_by'] = Auth::guard('admin')->id();

        Admin::create($validated);

        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin berhasil ditambahkan.');
    }

    public function edit(Admin $admin)
    {
        // Cannot edit super_admin if not self
        if ($admin->isSuperAdmin() && $admin->id !== Auth::guard('admin')->id()) {
            return back()->with('error', 'Tidak dapat mengedit Super Admin lain.');
        }

        return Inertia::render('Admin/Admins/Edit', [
            'admin' => [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'username' => $admin->username,
                'role' => $admin->role,
                'is_active' => $admin->is_active,
            ]
        ]);
    }

    public function update(Request $request, Admin $admin)
    {
        // Cannot edit super_admin if not self
        if ($admin->isSuperAdmin() && $admin->id !== Auth::guard('admin')->id()) {
            return back()->with('error', 'Tidak dapat mengedit Super Admin lain.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email,' . $admin->id,
            'username' => 'required|string|max:50|unique:admins,username,' . $admin->id . '|alpha_dash',
            'password' => ['nullable', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()],
            'role' => 'required|in:admin,super_admin',
            'is_active' => 'boolean',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['is_active'] = $request->boolean('is_active');

        $admin->update($validated);

        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin berhasil diperbarui.');
    }

    public function destroy(Admin $admin)
    {
        // Cannot delete super_admin
        if ($admin->isSuperAdmin()) {
            return back()->with('error', 'Tidak dapat menghapus Super Admin.');
        }

        // Cannot delete self
        if ($admin->id === Auth::guard('admin')->id()) {
            return back()->with('error', 'Tidak dapat menghapus akun sendiri.');
        }

        $admin->delete();

        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin berhasil dihapus.');
    }

    public function toggleStatus(Admin $admin)
    {
        // Cannot toggle super_admin
        if ($admin->isSuperAdmin()) {
            return back()->with('error', 'Tidak dapat mengubah status Super Admin.');
        }

        // Cannot toggle self
        if ($admin->id === Auth::guard('admin')->id()) {
            return back()->with('error', 'Tidak dapat mengubah status akun sendiri.');
        }

        $admin->update(['is_active' => !$admin->is_active]);

        return back()->with('success', 'Status admin berhasil diperbarui.');
    }

    public function profile()
    {
        $admin = Auth::guard('admin')->user();
        return Inertia::render('Admin/Admins/Profile', [
            'admin' => [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'username' => $admin->username,
                'role' => $admin->role,
            ]
        ]);
    }

    public function updateProfile(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email,' . $admin->id,
            'username' => 'required|string|max:50|unique:admins,username,' . $admin->id . '|alpha_dash',
        ]);

        $admin->update($validated);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()],
        ]);

        $admin = Auth::guard('admin')->user();

        if (!Hash::check($request->current_password, $admin->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini salah.']);
        }

        $admin->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password berhasil diperbarui.');
    }

    // API for real-time validation
    public function checkEmail(Request $request)
    {
        $email = $request->input('email');
        $excludeId = $request->input('exclude_id');

        $query = Admin::where('email', $email);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return response()->json([
            'available' => !$query->exists()
        ]);
    }

    public function checkUsername(Request $request)
    {
        $username = $request->input('username');
        $excludeId = $request->input('exclude_id');

        $query = Admin::where('username', $username);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return response()->json([
            'available' => !$query->exists()
        ]);
    }

    public function generateUsername(Request $request)
    {
        $name = $request->input('name', '');
        $baseUsername = Str::slug($name, '_');

        if (empty($baseUsername)) {
            return response()->json(['username' => '']);
        }

        $username = $baseUsername;
        $counter = 1;

        while (Admin::where('username', $username)->exists()) {
            $username = $baseUsername . '_' . $counter;
            $counter++;
        }

        return response()->json(['username' => $username]);
    }
}
