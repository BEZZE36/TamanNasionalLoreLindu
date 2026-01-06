<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // ========================================
        // ADMIN PANEL PERMISSIONS - 3 Level System
        // Sesuai urutan sidebar (19 menu)
        // access-* = Menu terlihat di sidebar (Tampilkan)
        // read-*   = Bisa melihat data saja (Read Only)
        // write-*  = Full akses CRUD (Full Akses)
        // ========================================
        $adminPermissions = [
            // ============ KONTEN ============
            // 1. Dashboard
            ['name' => 'Akses Dashboard', 'slug' => 'access-dashboard', 'group' => 'dashboard', 'description' => 'Menu Dashboard terlihat di sidebar'],
            ['name' => 'Lihat Dashboard', 'slug' => 'read-dashboard', 'group' => 'dashboard', 'description' => 'Dapat melihat data dashboard'],
            ['name' => 'Kelola Dashboard', 'slug' => 'write-dashboard', 'group' => 'dashboard', 'description' => 'Dapat mengubah widget dashboard'],

            // 2. Destinasi
            ['name' => 'Akses Destinasi', 'slug' => 'access-destinations', 'group' => 'destinations', 'description' => 'Menu Destinasi terlihat di sidebar'],
            ['name' => 'Lihat Destinasi', 'slug' => 'read-destinations', 'group' => 'destinations', 'description' => 'Hanya bisa melihat data destinasi'],
            ['name' => 'Kelola Destinasi', 'slug' => 'write-destinations', 'group' => 'destinations', 'description' => 'Dapat tambah, edit, hapus destinasi'],

            // 3. Flora
            ['name' => 'Akses Flora', 'slug' => 'access-flora', 'group' => 'flora', 'description' => 'Menu Flora terlihat di sidebar'],
            ['name' => 'Lihat Flora', 'slug' => 'read-flora', 'group' => 'flora', 'description' => 'Hanya bisa melihat data flora'],
            ['name' => 'Kelola Flora', 'slug' => 'write-flora', 'group' => 'flora', 'description' => 'Dapat tambah, edit, hapus flora'],

            // 4. Fauna
            ['name' => 'Akses Fauna', 'slug' => 'access-fauna', 'group' => 'fauna', 'description' => 'Menu Fauna terlihat di sidebar'],
            ['name' => 'Lihat Fauna', 'slug' => 'read-fauna', 'group' => 'fauna', 'description' => 'Hanya bisa melihat data fauna'],
            ['name' => 'Kelola Fauna', 'slug' => 'write-fauna', 'group' => 'fauna', 'description' => 'Dapat tambah, edit, hapus fauna'],

            // 5. Gallery
            ['name' => 'Akses Galeri', 'slug' => 'access-gallery', 'group' => 'gallery', 'description' => 'Menu Gallery terlihat di sidebar'],
            ['name' => 'Lihat Galeri', 'slug' => 'read-gallery', 'group' => 'gallery', 'description' => 'Hanya bisa melihat data galeri'],
            ['name' => 'Kelola Galeri', 'slug' => 'write-gallery', 'group' => 'gallery', 'description' => 'Dapat tambah, edit, hapus galeri'],

            // 6. Pengumuman
            ['name' => 'Akses Pengumuman', 'slug' => 'access-announcements', 'group' => 'announcements', 'description' => 'Menu Pengumuman terlihat di sidebar'],
            ['name' => 'Lihat Pengumuman', 'slug' => 'read-announcements', 'group' => 'announcements', 'description' => 'Hanya bisa melihat data pengumuman'],
            ['name' => 'Kelola Pengumuman', 'slug' => 'write-announcements', 'group' => 'announcements', 'description' => 'Dapat tambah, edit, hapus pengumuman'],

            // 7. Artikel
            ['name' => 'Akses Artikel', 'slug' => 'access-articles', 'group' => 'articles', 'description' => 'Menu Artikel terlihat di sidebar'],
            ['name' => 'Lihat Artikel', 'slug' => 'read-articles', 'group' => 'articles', 'description' => 'Hanya bisa melihat data artikel'],
            ['name' => 'Kelola Artikel', 'slug' => 'write-articles', 'group' => 'articles', 'description' => 'Dapat tambah, edit, hapus artikel'],

            // 8. Berita
            ['name' => 'Akses Berita', 'slug' => 'access-news', 'group' => 'news', 'description' => 'Menu Berita terlihat di sidebar'],
            ['name' => 'Lihat Berita', 'slug' => 'read-news', 'group' => 'news', 'description' => 'Hanya bisa melihat data berita'],
            ['name' => 'Kelola Berita', 'slug' => 'write-news', 'group' => 'news', 'description' => 'Dapat tambah, edit, hapus berita'],

            // 9. Newsletter
            ['name' => 'Akses Newsletter', 'slug' => 'access-newsletter', 'group' => 'newsletter', 'description' => 'Menu Newsletter terlihat di sidebar'],
            ['name' => 'Lihat Newsletter', 'slug' => 'read-newsletter', 'group' => 'newsletter', 'description' => 'Hanya bisa melihat data newsletter'],
            ['name' => 'Kelola Newsletter', 'slug' => 'write-newsletter', 'group' => 'newsletter', 'description' => 'Dapat mengelola newsletter dan kampanye'],

            // 10. Edit Info
            ['name' => 'Akses Edit Info', 'slug' => 'access-site-info', 'group' => 'site-info', 'description' => 'Menu Edit Info terlihat di sidebar'],
            ['name' => 'Lihat Site Info', 'slug' => 'read-site-info', 'group' => 'site-info', 'description' => 'Hanya bisa melihat info website'],
            ['name' => 'Kelola Site Info', 'slug' => 'write-site-info', 'group' => 'site-info', 'description' => 'Dapat mengubah info website, FAQ, dll'],

            // ============ MANAJEMEN ============
            // 11. Pemesanan
            ['name' => 'Akses Pemesanan', 'slug' => 'access-bookings', 'group' => 'bookings', 'description' => 'Menu Pemesanan terlihat di sidebar'],
            ['name' => 'Lihat Pemesanan', 'slug' => 'read-bookings', 'group' => 'bookings', 'description' => 'Hanya bisa melihat data pemesanan'],
            ['name' => 'Kelola Pemesanan', 'slug' => 'write-bookings', 'group' => 'bookings', 'description' => 'Dapat mengelola pemesanan'],

            // 12. Kupon
            ['name' => 'Akses Kupon', 'slug' => 'access-coupons', 'group' => 'coupons', 'description' => 'Menu Kupon terlihat di sidebar'],
            ['name' => 'Lihat Kupon', 'slug' => 'read-coupons', 'group' => 'coupons', 'description' => 'Hanya bisa melihat data kupon'],
            ['name' => 'Kelola Kupon', 'slug' => 'write-coupons', 'group' => 'coupons', 'description' => 'Dapat tambah, edit, hapus kupon'],

            // 13. Pengguna
            ['name' => 'Akses Pengguna', 'slug' => 'access-users', 'group' => 'users', 'description' => 'Menu Pengguna terlihat di sidebar'],
            ['name' => 'Lihat Pengguna', 'slug' => 'read-users', 'group' => 'users', 'description' => 'Hanya bisa melihat data pengguna'],
            ['name' => 'Kelola Pengguna', 'slug' => 'write-users', 'group' => 'users', 'description' => 'Dapat mengelola pengguna'],

            // 14. Scan Tiket
            ['name' => 'Akses Scan Tiket', 'slug' => 'access-tickets', 'group' => 'tickets', 'description' => 'Menu Scan Tiket terlihat di sidebar'],
            ['name' => 'Lihat Tiket', 'slug' => 'read-tickets', 'group' => 'tickets', 'description' => 'Hanya bisa melihat data tiket'],
            ['name' => 'Scan Tiket', 'slug' => 'scan-tickets', 'group' => 'tickets', 'description' => 'Dapat melakukan scan tiket'],

            // ============ LAPORAN ============
            // 15. Activity Log
            ['name' => 'Akses Activity Log', 'slug' => 'access-activity-logs', 'group' => 'activity-logs', 'description' => 'Menu Activity Log terlihat di sidebar'],
            ['name' => 'Lihat Activity Log', 'slug' => 'read-activity-logs', 'group' => 'activity-logs', 'description' => 'Dapat melihat log aktivitas'],

            // 16. Analytics
            ['name' => 'Akses Analytics', 'slug' => 'access-analytics', 'group' => 'analytics', 'description' => 'Menu Analytics terlihat di sidebar'],
            ['name' => 'Lihat Analytics', 'slug' => 'read-analytics', 'group' => 'analytics', 'description' => 'Dapat melihat data analytics'],

            // ============ PENGATURAN ============
            // 17. Kelola Admin
            ['name' => 'Akses Kelola Admin', 'slug' => 'access-admins', 'group' => 'admins', 'description' => 'Menu Kelola Admin terlihat di sidebar'],
            ['name' => 'Lihat Admin', 'slug' => 'read-admins', 'group' => 'admins', 'description' => 'Hanya bisa melihat data admin'],
            ['name' => 'Kelola Admin', 'slug' => 'write-admins', 'group' => 'admins', 'description' => 'Dapat tambah, edit, hapus admin'],

            // 18. Role & Akses
            ['name' => 'Akses Role & Akses', 'slug' => 'access-roles', 'group' => 'roles', 'description' => 'Menu Role & Akses terlihat di sidebar'],
            ['name' => 'Lihat Role', 'slug' => 'read-roles', 'group' => 'roles', 'description' => 'Hanya bisa melihat data role'],
            ['name' => 'Kelola Role', 'slug' => 'write-roles', 'group' => 'roles', 'description' => 'Dapat mengelola role dan permission'],

            // 19. Database
            ['name' => 'Akses Database', 'slug' => 'access-database', 'group' => 'database', 'description' => 'Menu Database terlihat di sidebar'],
            ['name' => 'Lihat Database', 'slug' => 'read-database', 'group' => 'database', 'description' => 'Dapat melihat info database'],
            ['name' => 'Kelola Database', 'slug' => 'write-database', 'group' => 'database', 'description' => 'Dapat backup, restore, import database'],
        ];

        // ========================================
        // PUBLIC WEBSITE PERMISSIONS (untuk User & Guest)
        // ========================================
        $publicPermissions = [
            // Viewing Public Content (Guest & User)
            ['name' => 'Lihat Halaman Publik', 'slug' => 'view-public-pages', 'group' => 'public', 'description' => 'Dapat melihat halaman publik website'],
            ['name' => 'Lihat Destinasi Publik', 'slug' => 'view-public-destinations', 'group' => 'public', 'description' => 'Dapat melihat detail destinasi'],
            ['name' => 'Lihat Galeri Publik', 'slug' => 'view-public-gallery', 'group' => 'public', 'description' => 'Dapat melihat galeri foto'],
            ['name' => 'Lihat Artikel Publik', 'slug' => 'view-public-articles', 'group' => 'public', 'description' => 'Dapat membaca artikel/blog'],
            ['name' => 'Lihat Flora Fauna Publik', 'slug' => 'view-public-flora-fauna', 'group' => 'public', 'description' => 'Dapat melihat informasi flora fauna'],

            // User Actions (hanya User yang login)
            ['name' => 'Buat Pemesanan', 'slug' => 'create-booking', 'group' => 'user-actions', 'description' => 'Dapat membuat pemesanan tiket'],
            ['name' => 'Lihat Pemesanan Saya', 'slug' => 'view-my-bookings', 'group' => 'user-actions', 'description' => 'Dapat melihat riwayat pemesanan sendiri'],
            ['name' => 'Batalkan Pemesanan', 'slug' => 'cancel-my-booking', 'group' => 'user-actions', 'description' => 'Dapat membatalkan pemesanan sendiri'],
            ['name' => 'Lihat Tiket Saya', 'slug' => 'view-my-tickets', 'group' => 'user-actions', 'description' => 'Dapat melihat tiket yang dimiliki'],
            ['name' => 'Unduh Tiket', 'slug' => 'download-ticket', 'group' => 'user-actions', 'description' => 'Dapat mengunduh tiket PDF'],
            ['name' => 'Beri Review', 'slug' => 'create-review', 'group' => 'user-actions', 'description' => 'Dapat memberikan review destinasi'],
            ['name' => 'Kirim Feedback', 'slug' => 'send-feedback', 'group' => 'user-actions', 'description' => 'Dapat mengirim feedback ke admin'],
            ['name' => 'Kelola Profil', 'slug' => 'manage-profile', 'group' => 'user-actions', 'description' => 'Dapat mengubah profil dan password'],
            ['name' => 'Subscribe Newsletter', 'slug' => 'subscribe-newsletter', 'group' => 'user-actions', 'description' => 'Dapat berlangganan newsletter'],
            ['name' => 'Simpan Favorit', 'slug' => 'save-favorites', 'group' => 'user-actions', 'description' => 'Dapat menyimpan destinasi favorit'],
        ];

        // Merge all permissions
        $allPermissions = array_merge($adminPermissions, $publicPermissions);

        // PENTING: Clear pivot table dulu sebelum update permissions
        // untuk menghindari foreign key constraint error
        \DB::table('role_permission')->truncate();

        // Delete old permissions yang tidak valid
        $validSlugs = collect($allPermissions)->pluck('slug')->toArray();
        Permission::whereNotIn('slug', $validSlugs)->delete();

        // Create/update permissions
        foreach ($allPermissions as $permission) {
            Permission::updateOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }

        // Create 4 Default Roles
        $roles = [
            [
                'name' => 'Super Admin',
                'slug' => 'super-admin',
                'description' => 'Akses penuh ke semua fitur admin panel tanpa batasan.',
            ],
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Akses terbatas ke admin panel sesuai izin yang diberikan Super Admin.',
            ],
            [
                'name' => 'User',
                'slug' => 'user',
                'description' => 'Pengguna website yang sudah login. Full akses ke fitur publik (booking, review, dll).',
            ],
            [
                'name' => 'Guest',
                'slug' => 'guest',
                'description' => 'Pengunjung website yang belum login. Hanya bisa melihat konten publik (read-only).',
            ],
        ];

        foreach ($roles as $roleData) {
            Role::updateOrCreate(
                ['slug' => $roleData['slug']],
                $roleData
            );
        }

        // Assign permissions to roles
        $permissionModels = Permission::all();

        // Super Admin gets ALL admin panel permissions
        $superAdmin = Role::where('slug', 'super-admin')->first();
        if ($superAdmin) {
            $superAdminPerms = $permissionModels->whereNotIn('group', ['public', 'user-actions']);
            $superAdmin->permissions()->sync($superAdminPerms->pluck('id'));
        }

        // Admin gets limited admin permissions (no admins, roles, database management)
        $admin = Role::where('slug', 'admin')->first();
        if ($admin) {
            $adminPerms = $permissionModels->whereNotIn('group', ['public', 'user-actions', 'admins', 'roles', 'database']);
            $admin->permissions()->sync($adminPerms->pluck('id'));
        }

        // User - Full akses ke fitur publik website (view + actions)
        $user = Role::where('slug', 'user')->first();
        if ($user) {
            $userPerms = $permissionModels->whereIn('group', ['public', 'user-actions']);
            $user->permissions()->sync($userPerms->pluck('id'));
        }

        // Guest - Hanya bisa VIEW konten publik (read-only)
        $guest = Role::where('slug', 'guest')->first();
        if ($guest) {
            $guestPerms = $permissionModels->where('group', 'public');
            $guest->permissions()->sync($guestPerms->pluck('id'));
        }

        $this->command->info('✓ Roles and Permissions seeded successfully!');
        $this->command->info('  - 19 Menu dengan 3 level permission (access/read/write)');
        $this->command->info('  - Super Admin: Full admin panel access');
        $this->command->info('  - Admin: Limited admin panel access');
        $this->command->info('  - User: Full public website access');
        $this->command->info('  - Guest: Read-only public access');
    }
}
