<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $guard = 'admin';

    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'role',
        'created_by',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    // Relationships
    public function creator(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function createdAdmins(): HasMany
    {
        return $this->hasMany(Admin::class, 'created_by');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class, 'created_by');
    }

    public function feedbackReplies(): HasMany
    {
        return $this->hasMany(FeedbackReply::class);
    }

    public function validatedTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'validated_by');
    }

    // Roles relationship
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'admin_role');
    }

    // Check if admin has permission
    public function hasPermission(string $permission): bool
    {
        // Super admin has all permissions
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Check through roles
        foreach ($this->roles as $role) {
            if ($role->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    // Helpers
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin' || $this->roles()->where('slug', 'super-admin')->exists();
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function canManageAdmins(): bool
    {
        return $this->isSuperAdmin() || $this->hasPermission('manage-admins');
    }
}
