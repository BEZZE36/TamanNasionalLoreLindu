<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'admin_name',
        'action',
        'model_type',
        'model_id',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    // Action constants
    public const ACTION_CREATE = 'create';
    public const ACTION_UPDATE = 'update';
    public const ACTION_DELETE = 'delete';
    public const ACTION_LOGIN = 'login';
    public const ACTION_LOGOUT = 'logout';
    public const ACTION_VALIDATE_TICKET = 'validate_ticket';
    public const ACTION_BLOCK_USER = 'block_user';
    public const ACTION_UNBLOCK_USER = 'unblock_user';

    public const ACTIONS = [
        self::ACTION_CREATE => 'Membuat',
        self::ACTION_UPDATE => 'Mengubah',
        self::ACTION_DELETE => 'Menghapus',
        self::ACTION_LOGIN => 'Login',
        self::ACTION_LOGOUT => 'Logout',
        self::ACTION_VALIDATE_TICKET => 'Validasi Tiket',
        self::ACTION_BLOCK_USER => 'Blokir Pengguna',
        self::ACTION_UNBLOCK_USER => 'Buka Blokir Pengguna',
    ];

    // Relationships
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    // Accessors
    public function getActionLabelAttribute(): string
    {
        return self::ACTIONS[$this->action] ?? $this->action;
    }

    public function getModelNameAttribute(): string
    {
        if (!$this->model_type) {
            return '';
        }

        $map = [
            'App\\Models\\User' => 'Pengguna',
            'App\\Models\\Destination' => 'Destinasi',
            'App\\Models\\Booking' => 'Booking',
            'App\\Models\\Ticket' => 'Tiket',
            'App\\Models\\Announcement' => 'Pengumuman',
            'App\\Models\\Feedback' => 'Feedback',
            'App\\Models\\Banner' => 'Banner',
            'App\\Models\\Admin' => 'Admin',
        ];

        return $map[$this->model_type] ?? class_basename($this->model_type);
    }

    // Static helper to create log
    public static function log(
        string $action,
        ?string $description = null,
        ?Model $model = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): self {
        $admin = auth('admin')->user();

        return self::create([
            'admin_id' => $admin?->id,
            'admin_name' => $admin?->name ?? 'System',
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

    // Scopes
    public function scopeByAdmin($query, int $adminId)
    {
        return $query->where('admin_id', $adminId);
    }

    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
