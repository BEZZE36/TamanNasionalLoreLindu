<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use App\Models\NewsletterSubscriber;

class ProfileController extends Controller
{
    /**
     * Show the user's profile.
     */
    public function show()
    {
        $user = Auth::user();

        $stats = [
            'totalBookings' => $user->bookings()->count(),
            'completedBookings' => $user->bookings()->where('status', 'paid')->count(),
            'totalSpent' => 'Rp ' . number_format($user->bookings()->where('status', 'paid')->sum('total_amount'), 0, ',', '.'),
            'memberSince' => $user->created_at->format('M Y'),
        ];

        // Get newsletter subscription for this user
        $newsletterSubscription = NewsletterSubscriber::where('user_id', $user->id)->first();

        // Pass user data explicitly for Vue reactivity
        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'username' => $user->username,
            'address' => $user->address,
            'avatar' => $user->avatar,
            'avatar_url' => $user->avatar_url,
            'google_id' => $user->google_id,
            'created_at' => $user->created_at,
        ];

        return \Inertia\Inertia::render('User/Profile', [
            'stats' => $stats,
            'newsletterSubscription' => $newsletterSubscription,
            'userData' => $userData,
        ]);
    }


    /**
     * Update the user's profile.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20', Rule::unique('users')->ignore($user->id)],
            'username' => ['nullable', 'string', 'max:50', Rule::unique('users')->ignore($user->id), 'regex:/^[a-zA-Z0-9_]+$/'],
        ], [
            'email.unique' => 'Email ini sudah digunakan oleh pengguna lain.',
            'phone.unique' => 'Nomor telepon ini sudah digunakan oleh pengguna lain.',
            'username.unique' => 'Username ini sudah digunakan oleh pengguna lain.',
            'username.regex' => 'Username hanya boleh mengandung huruf, angka, dan underscore.',
        ]);

        $user->update($validated);

        return back()->with('success', 'Profil berhasil diperbarui!');
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        // Custom validation: new password must be different from current
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => [
                'required',
                'confirmed',
                'min:8',
                'different:current_password', // Must be different from current
                'regex:/[A-Z]/',      // Must contain uppercase
                'regex:/[a-z]/',      // Must contain lowercase
                'regex:/[0-9]/',      // Must contain number
                'regex:/[@#?!$%^&*_\-+=]/', // Must contain special character
            ],
            'password_confirmation' => ['required'],
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'current_password.current_password' => 'Password saat ini tidak sesuai.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.different' => 'Password baru tidak boleh sama dengan password saat ini.',
            'password.regex' => 'Password harus mengandung huruf besar, huruf kecil, angka, dan karakter unik (@#?!$%^&*_-+=).',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password_confirmation.required' => 'Konfirmasi password wajib diisi.',
        ]);

        $user->password = Hash::make($validated['password']);
        $user->save();

        return back()->with('success', 'Password berhasil diperbarui!');
    }

    /**
     * Update the user's avatar.
     */
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ], [
            'avatar.required' => 'Silakan pilih foto profil.',
            'avatar.image' => 'File harus berupa gambar.',
            'avatar.mimes' => 'Format gambar harus JPEG, PNG, JPG, atau WEBP.',
            'avatar.max' => 'Ukuran gambar maksimal 2MB.',
        ]);

        $user = Auth::user();

        // Delete old avatar if exists
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        // Store new avatar
        $path = $request->file('avatar')->store('avatars', 'public');

        $user->update(['avatar' => $path]);

        return back()->with('success', 'Foto profil berhasil diperbarui!');
    }

    /**
     * Delete the user's avatar.
     */
    public function deleteAvatar()
    {
        $user = Auth::user();

        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->update(['avatar' => null]);

        return back()->with('success', 'Foto profil berhasil dihapus!');
    }
}

