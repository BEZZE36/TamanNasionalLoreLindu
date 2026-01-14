<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class AdminController extends Controller
{
    use LogsActivity;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $admin = Auth::guard('admin')->user();

            if ($admin->isSuperAdmin()) {
                return $next($request);
            }

            if (!$admin->hasPermission('access-admins')) {
                abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk mengelola akun admin.');
            }

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $currentAdminId = Auth::guard('admin')->id();

        $query = Admin::with('creator', 'roles');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        $query->orderByRaw("role = 'super_admin' DESC")
            ->orderBy('name');

        $admins = $query->paginate(10)->withQueryString();

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
            // Semua aksi bisa dilakukan KECUALI untuk akun sendiri yang sedang login
            'can_edit' => $admin->id !== $currentAdminId,
            'can_delete' => $admin->id !== $currentAdminId,
            'can_toggle' => $admin->id !== $currentAdminId,
        ]);

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

        $admin = Admin::create($validated);

        $this->logCreate($admin, 'Admin', $admin->name);

        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin berhasil ditambahkan.');
    }

    public function edit(Admin $admin)
    {
        // Admin tidak bisa mengedit akun sendiri dari halaman ini
        if ($admin->id === Auth::guard('admin')->id()) {
            return redirect()->route('admin.admins.index')
                ->with('error', 'Tidak dapat mengedit akun sendiri dari halaman ini. Gunakan halaman profil.');
        }

        // Load admin's current roles
        $admin->load('roles');

        // Get all available roles
        $availableRoles = \App\Models\Role::select('id', 'name', 'slug')->orderBy('name')->get();

        return Inertia::render('Admin/Admins/Edit', [
            'editAdmin' => [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'username' => $admin->username,
                'role' => $admin->role,
                'is_active' => $admin->is_active,
                'role_ids' => $admin->roles->pluck('id')->toArray(),
            ],
            'availableRoles' => $availableRoles,
        ]);
    }

    public function update(Request $request, Admin $admin)
    {
        // Admin tidak bisa mengedit akun sendiri dari halaman ini
        if ($admin->id === Auth::guard('admin')->id()) {
            return back()->with('error', 'Tidak dapat mengedit akun sendiri dari halaman ini. Gunakan halaman profil.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email,' . $admin->id,
            'username' => 'required|string|max:50|unique:admins,username,' . $admin->id . '|alpha_dash',
            'password' => ['nullable', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()],
            'role' => 'required|in:admin,super_admin',
            'is_active' => 'boolean',
            'role_ids' => 'array',
            'role_ids.*' => 'exists:roles,id',
        ]);

        $oldValues = $admin->only(['name', 'email', 'role', 'is_active']);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['is_active'] = $request->boolean('is_active');

        // Remove role_ids from validated before update (it's handled separately)
        $roleIds = $validated['role_ids'] ?? [];
        unset($validated['role_ids']);

        $admin->update($validated);

        // Sync roles
        $admin->roles()->sync($roleIds);

        $newValues = $admin->only(['name', 'email', 'role', 'is_active']);
        $this->logUpdate($admin, 'Admin', $oldValues, $newValues, $admin->name);

        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin berhasil diperbarui.');
    }

    public function destroy(Admin $admin)
    {
        // Admin tidak bisa menghapus akun sendiri
        if ($admin->id === Auth::guard('admin')->id()) {
            return back()->with('error', 'Tidak dapat menghapus akun sendiri.');
        }

        $name = $admin->name;
        $this->logDelete($admin, 'Admin', $name);

        $admin->delete();

        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin berhasil dihapus.');
    }

    public function toggleStatus(Admin $admin)
    {
        // Admin tidak bisa mengubah status akun sendiri
        if ($admin->id === Auth::guard('admin')->id()) {
            return back()->with('error', 'Tidak dapat mengubah status akun sendiri.');
        }

        $admin->update(['is_active' => !$admin->is_active]);

        $this->logToggle($admin, 'Admin', 'is_active', $admin->is_active, $admin->name);

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

        $oldValues = $admin->only(['name', 'email']);
        $admin->update($validated);
        $newValues = $admin->only(['name', 'email']);

        $this->logUpdate($admin, 'Profil Admin', $oldValues, $newValues, $admin->name);

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

        $this->logActivity('update', "Mengubah password profil", $admin);

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
