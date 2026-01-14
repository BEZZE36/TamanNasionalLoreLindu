<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    use LogsActivity;

    public function index(Request $request)
    {
        $query = User::withCount('bookings');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%')
                    ->orWhere('username', 'like', '%' . $request->search . '%')
                    ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $users = $query->latest()->paginate(15)->withQueryString();
        $users->getCollection()->transform(fn($u) => [
            'id' => $u->id,
            'name' => $u->name,
            'username' => $u->username,
            'email' => $u->email,
            'phone' => $u->phone,
            'status' => $u->status,
            'avatar' => $u->avatar,
            'avatar_url' => $u->avatar_url,
            'google_id' => $u->google_id,
            'bookings_count' => $u->bookings_count,
            'created_at' => $u->created_at,
        ]);

        $stats = [
            'total' => User::count(),
            'active' => User::where('status', 'active')->count(),
            'blocked' => User::where('status', 'blocked')->count(),
            'newThisMonth' => User::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
        ];

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => $request->only(['search', 'status']),
            'stats' => $stats,
        ]);
    }

    public function show(User $user)
    {
        $user->load([
            'bookings' => function ($q) {
                $q->with(['destination', 'payment'])->latest()->take(10);
            }
        ]);

        $stats = [
            'total_bookings' => $user->bookings()->count(),
            'total_spent' => $user->bookings()->where('status', 'paid')->sum('total_amount'),
            'completed_bookings' => $user->bookings()->whereIn('status', ['used', 'paid'])->count(),
            'this_month' => $user->bookings()->whereMonth('created_at', now()->month)->count(),
        ];

        return Inertia::render('Admin/Users/Show', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'phone' => $user->phone,
                'status' => $user->status,
                'avatar' => $user->avatar,
                'avatar_url' => $user->avatar_url,
                'google_id' => $user->google_id,
                'created_at' => $user->created_at,
                'address' => $user->address,
                'bookings' => $user->bookings->map(fn($b) => [
                    'id' => $b->id,
                    'order_number' => $b->order_number,
                    'status' => $b->status,
                    'total_amount' => $b->total_amount,
                    'destination' => $b->destination ? ['name' => $b->destination->name] : null,
                ])->toArray(),
            ],
            'stats' => $stats,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Users/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20|unique:users',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/[0-9]/',
                'regex:/[@#?!$%^&*_\-+=]/',
            ],
            'status' => 'required|in:active,blocked',
        ], [
            'email.unique' => 'Email ini sudah digunakan oleh pengguna lain.',
            'username.unique' => 'Username ini sudah digunakan oleh pengguna lain.',
            'phone.unique' => 'Nomor telepon ini sudah digunakan oleh pengguna lain.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.regex' => 'Password harus mengandung huruf besar, huruf kecil, angka, dan karakter unik (@#?!$%^&*_-+=).',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $validated['password'] = bcrypt($validated['password']);

        $user = User::create($validated);

        $this->logCreate($user, 'Pengguna', $user->name);

        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        return Inertia::render('Admin/Users/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'phone' => $user->phone,
                'address' => $user->address,
                'avatar' => $user->avatar,
                'avatar_url' => $user->avatar_url,
                'google_id' => $user->google_id,
                'status' => $user->status,
            ],
        ]);
    }

    public function update(Request $request, User $user)
    {
        $passwordRules = $request->filled('password') ? [
            'nullable',
            'string',
            'min:8',
            'confirmed',
            'regex:/[A-Z]/',
            'regex:/[a-z]/',
            'regex:/[0-9]/',
            'regex:/[@#?!$%^&*_\-+=]/',
        ] : 'nullable|string|min:8|confirmed';

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20|unique:users,phone,' . $user->id,
            'password' => $passwordRules,
            'status' => 'required|in:active,blocked',
        ], [
            'email.unique' => 'Email ini sudah digunakan oleh pengguna lain.',
            'username.unique' => 'Username ini sudah digunakan oleh pengguna lain.',
            'phone.unique' => 'Nomor telepon ini sudah digunakan oleh pengguna lain.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.regex' => 'Password harus mengandung huruf besar, huruf kecil, angka, dan karakter unik (@#?!$%^&*_-+=).',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $oldValues = $user->only(['name', 'email', 'status']);

        if ($request->filled('password')) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        $newValues = $user->only(['name', 'email', 'status']);
        $this->logUpdate($user, 'Pengguna', $oldValues, $newValues, $user->name);

        return redirect()->route('admin.users.index')
            ->with('success', 'Data user berhasil diperbarui.');
    }

    public function updateStatus(Request $request, User $user)
    {
        $validated = $request->validate([
            'status' => 'required|in:active,blocked',
        ]);

        $oldStatus = $user->status;
        $user->update(['status' => $validated['status']]);

        $action = $validated['status'] === 'active' ? 'Mengaktifkan' : 'Memblokir';
        $this->logActivity('toggle', "{$action} pengguna: {$user->name}", $user, ['status' => $oldStatus], ['status' => $validated['status']]);

        $message = $validated['status'] === 'active'
            ? 'User berhasil diaktifkan.'
            : 'User berhasil diblokir.';

        return back()->with('success', $message);
    }

    public function block(User $user)
    {
        $user->update(['status' => User::STATUS_BLOCKED]);

        $this->logActivity('toggle', "Memblokir pengguna: {$user->name}", $user, ['status' => 'active'], ['status' => 'blocked']);

        return back()->with('success', 'User berhasil diblokir.');
    }

    public function unblock(User $user)
    {
        $user->update(['status' => User::STATUS_ACTIVE]);

        $this->logActivity('toggle', "Mengaktifkan pengguna: {$user->name}", $user, ['status' => 'blocked'], ['status' => 'active']);

        return back()->with('success', 'User berhasil diaktifkan.');
    }

    public function destroy(User $user)
    {
        $name = $user->name;
        $this->logDelete($user, 'Pengguna', $name);

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil dihapus.');
    }
}
