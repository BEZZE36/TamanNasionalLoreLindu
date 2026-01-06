<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'group',
        'description',
    ];

    // Permission groups
    const GROUPS = [
        'dashboard' => 'Dashboard',
        'destinations' => 'Destinasi',
        'bookings' => 'Pemesanan',
        'tickets' => 'Tiket',
        'users' => 'Pengguna',
        'flora' => 'Flora',
        'fauna' => 'Fauna',
        'gallery' => 'Galeri',
        'articles' => 'Artikel',
        'settings' => 'Pengaturan',
        'reports' => 'Laporan',
        'admins' => 'Admin',
    ];

    // All permissions
    const PERMISSIONS = [
        // Dashboard
        'view-dashboard' => ['name' => 'Lihat Dashboard', 'group' => 'dashboard'],
        'view-analytics' => ['name' => 'Lihat Analytics', 'group' => 'dashboard'],
        
        // Destinations
        'view-destinations' => ['name' => 'Lihat Destinasi', 'group' => 'destinations'],
        'create-destinations' => ['name' => 'Tambah Destinasi', 'group' => 'destinations'],
        'edit-destinations' => ['name' => 'Edit Destinasi', 'group' => 'destinations'],
        'delete-destinations' => ['name' => 'Hapus Destinasi', 'group' => 'destinations'],
        
        // Bookings
        'view-bookings' => ['name' => 'Lihat Pemesanan', 'group' => 'bookings'],
        'manage-bookings' => ['name' => 'Kelola Pemesanan', 'group' => 'bookings'],
        'export-bookings' => ['name' => 'Export Pemesanan', 'group' => 'bookings'],
        
        // Tickets
        'view-tickets' => ['name' => 'Lihat Tiket', 'group' => 'tickets'],
        'scan-tickets' => ['name' => 'Scan Tiket', 'group' => 'tickets'],
        
        // Users
        'view-users' => ['name' => 'Lihat Pengguna', 'group' => 'users'],
        'manage-users' => ['name' => 'Kelola Pengguna', 'group' => 'users'],
        
        // Flora/Fauna
        'manage-flora' => ['name' => 'Kelola Flora', 'group' => 'flora'],
        'manage-fauna' => ['name' => 'Kelola Fauna', 'group' => 'fauna'],
        
        // Gallery
        'manage-gallery' => ['name' => 'Kelola Galeri', 'group' => 'gallery'],
        
        // Articles
        'view-articles' => ['name' => 'Lihat Artikel', 'group' => 'articles'],
        'manage-articles' => ['name' => 'Kelola Artikel', 'group' => 'articles'],
        
        // Settings
        'view-settings' => ['name' => 'Lihat Pengaturan', 'group' => 'settings'],
        'manage-settings' => ['name' => 'Kelola Pengaturan', 'group' => 'settings'],
        'manage-backups' => ['name' => 'Kelola Backup', 'group' => 'settings'],
        
        // Reports
        'view-reports' => ['name' => 'Lihat Laporan', 'group' => 'reports'],
        'export-reports' => ['name' => 'Export Laporan', 'group' => 'reports'],
        
        // Admins
        'view-admins' => ['name' => 'Lihat Admin', 'group' => 'admins'],
        'manage-admins' => ['name' => 'Kelola Admin', 'group' => 'admins'],
        'manage-roles' => ['name' => 'Kelola Role', 'group' => 'admins'],
    ];

    // Relationships
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permission');
    }

    // Get permissions grouped
    public static function allGrouped(): array
    {
        $permissions = self::all();
        return $permissions->groupBy('group')->toArray();
    }
}
