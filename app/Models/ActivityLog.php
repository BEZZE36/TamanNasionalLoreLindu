<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'admin_id',
        'action',
        'model_type',
        'model_id',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    const ACTIONS = [
        'create' => 'Membuat',
        'update' => 'Memperbarui',
        'delete' => 'Menghapus',
        'login' => 'Login',
        'logout' => 'Logout',
        'view' => 'Melihat',
        'export' => 'Mengekspor',
        'import' => 'Mengimpor',
        'publish' => 'Mempublikasikan',
        'unpublish' => 'Batal Publikasi',
        'archive' => 'Mengarsipkan',
        'restore' => 'Memulihkan',
        'send' => 'Mengirim',
        'scan' => 'Memindai',
        'checkin' => 'Check-in',
        'toggle' => 'Mengubah Status',
        'backup' => 'Backup',
        'settings' => 'Pengaturan',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    // Accessors
    public function getActionLabelAttribute()
    {
        return self::ACTIONS[$this->action] ?? ucfirst($this->action);
    }

    public function getIconAttribute()
    {
        $icons = [
            'create' => '➕',
            'update' => '✏️',
            'delete' => '🗑️',
            'login' => '🔑',
            'logout' => '🚪',
            'view' => '👁️',
            'export' => '📤',
            'import' => '📥',
        ];
        return $icons[$this->action] ?? '📌';
    }

    public function getColorClassAttribute()
    {
        $colors = [
            'create' => 'bg-green-100 text-green-700',
            'update' => 'bg-blue-100 text-blue-700',
            'delete' => 'bg-red-100 text-red-700',
            'login' => 'bg-purple-100 text-purple-700',
            'logout' => 'bg-gray-100 text-gray-700',
            'view' => 'bg-cyan-100 text-cyan-700',
            'export' => 'bg-orange-100 text-orange-700',
            'import' => 'bg-yellow-100 text-yellow-700',
        ];
        return $colors[$this->action] ?? 'bg-gray-100 text-gray-700';
    }

    // Scopes
    public function scopeRecent($query, $limit = 10)
    {
        return $query->latest()->take($limit);
    }

    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    public function scopeByModel($query, $modelType)
    {
        return $query->where('model_type', $modelType);
    }

    // Static helper to log activity
    public static function log($action, $description, $model = null, $oldValues = null, $newValues = null)
    {
        $admin = auth('admin')->user();
        $user = auth()->user();

        return self::create([
            'user_id' => $user?->id,
            'admin_id' => $admin?->id,
            'action' => $action,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model?->id,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
