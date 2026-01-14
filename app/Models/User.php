<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'phone',
        'address',
        'id_type',
        'id_number',
        'avatar',
        'google_id',
        'status',
        'password',
        'last_login_at',
        'last_login_ip',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate username from name on creation
        static::creating(function ($user) {
            if (empty($user->username)) {
                $user->username = self::generateUniqueUsername($user->name);
            }
        });
    }

    /**
     * Generate a unique username from user's name.
     */
    public static function generateUniqueUsername(string $name): string
    {
        // Convert name to username base (lowercase, replace spaces with underscore, only alphanumeric and underscore)
        $base = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', str_replace(' ', '_', $name)));

        // Limit base length
        $base = substr($base, 0, 15);

        // If base is empty, use 'user'
        if (empty($base)) {
            $base = 'user';
        }

        // Try with random suffix until unique
        $attempts = 0;
        $maxAttempts = 10;

        do {
            // Generate random 4-digit suffix
            $suffix = rand(1000, 9999);
            $username = $base . '_' . $suffix;
            $attempts++;
        } while (self::where('username', $username)->exists() && $attempts < $maxAttempts);

        // If still not unique after max attempts, add timestamp
        if (self::where('username', $username)->exists()) {
            $username = $base . '_' . time();
        }

        return $username;
    }

    // Status constants
    public const STATUS_ACTIVE = 'active';
    public const STATUS_BLOCKED = 'blocked';
    public const STATUS_PENDING = 'pending';

    public const STATUSES = [
        self::STATUS_ACTIVE => 'Aktif',
        self::STATUS_BLOCKED => 'Diblokir',
        self::STATUS_PENDING => 'Menunggu Verifikasi',
    ];

    // Relationships
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function feedbacks(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }

    public function wishlist(): BelongsToMany
    {
        return $this->belongsToMany(Destination::class, 'wishlists', 'user_id', 'destination_id')
            ->withTimestamps();
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }

    public function hasWishlist($destinationId): bool
    {
        return $this->wishlist()->where('destination_id', $destinationId)->exists();
    }

    /**
     * Galleries liked by this user.
     */
    public function galleryLikes(): BelongsToMany
    {
        return $this->belongsToMany(Gallery::class, 'gallery_likes')
            ->withTimestamps();
    }

    /**
     * Comments made by this user on galleries.
     */
    public function galleryComments(): HasMany
    {
        return $this->hasMany(GalleryComment::class);
    }

    /**
     * Check if user has liked a gallery.
     */
    public function hasLikedGallery($galleryId): bool
    {
        return $this->galleryLikes()->where('gallery_id', $galleryId)->exists();
    }

    // Accessors
    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'green',
            self::STATUS_BLOCKED => 'red',
            self::STATUS_PENDING => 'yellow',
            default => 'gray',
        };
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            // Check if avatar is already a full URL (e.g., Google OAuth avatar)
            if (str_starts_with($this->avatar, 'http://') || str_starts_with($this->avatar, 'https://')) {
                return $this->avatar;
            }
            return asset('storage/' . $this->avatar);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=059669&color=fff';
    }

    // Status checks
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isBlocked(): bool
    {
        return $this->status === self::STATUS_BLOCKED;
    }

    // Actions
    public function block(): void
    {
        $this->update(['status' => self::STATUS_BLOCKED]);
    }

    public function unblock(): void
    {
        $this->update(['status' => self::STATUS_ACTIVE]);
    }

    public function updateLoginInfo(): void
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => request()->ip(),
        ]);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeBlocked($query)
    {
        return $query->where('status', self::STATUS_BLOCKED);
    }

    public function scopeVerified($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('username', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%");
        });
    }
}

