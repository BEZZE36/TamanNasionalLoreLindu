<?php

namespace App\Models;

use App\Models\Traits\HasGalleryMedia;
use App\Models\Traits\HasGalleryScopes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Gallery extends Model
{
    use HasFactory, HasGalleryMedia, HasGalleryScopes;

    /**
     * The accessors to append to the model's array form.
     */
    protected $appends = [
        'image_url',
        'cover_url',
        'thumbnail_url',
        'formatted_view_count',
        'type_label',
    ];

    protected $fillable = [
        'title',
        'slug',
        'description',
        'category',
        'location',
        'image_path',
        'image_data',
        'image_mime',
        'video_url',
        'type',
        'destination_id',
        'gallery_category_id',
        'tags',
        'capture_date',
        'is_featured',
        'is_active',
        'sort_order',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'view_count',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'tags' => 'array',
            'capture_date' => 'date',
        ];
    }

    // Type constants
    public const TYPE_IMAGE = 'image';
    public const TYPE_VIDEO = 'video';
    public const TYPES = [
        self::TYPE_IMAGE => 'Gambar',
        self::TYPE_VIDEO => 'Video',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($gallery) {
            if (empty($gallery->slug)) {
                $gallery->slug = self::generateUniqueSlug($gallery->title);
            }
        });

        static::updating(function ($gallery) {
            if ($gallery->isDirty('title') && !$gallery->isDirty('slug')) {
                $gallery->slug = self::generateUniqueSlug($gallery->title, $gallery->id);
            }
        });
    }

    public static function generateUniqueSlug(string $title, ?int $excludeId = null): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        $query = self::where('slug', $slug);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        while ($query->exists()) {
            $slug = $originalSlug . '-' . $counter++;
            $query = self::where('slug', $slug);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
        }

        return $slug;
    }

    // Relationships
    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(GalleryCategory::class, 'gallery_category_id');
    }

    public function media(): HasMany
    {
        return $this->hasMany(GalleryMedia::class)->orderBy('sort_order');
    }

    public function likes(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'gallery_likes')->withTimestamps();
    }

    public function wishlists(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'gallery_wishlists')->withTimestamps();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(GalleryComment::class)->orderBy('created_at', 'desc');
    }

    // Accessors
    public function getLikesCountAttribute(): int
    {
        return $this->likes()->count();
    }

    public function getFormattedLikesCountAttribute(): string
    {
        return $this->formatCount($this->likes_count);
    }

    public function getTypeLabelAttribute(): string
    {
        if ($this->type === null) {
            return '';
        }
        return self::TYPES[$this->type] ?? $this->type ?? '';
    }

    public function getFormattedViewCountAttribute(): string
    {
        return $this->formatCount($this->view_count ?? 0);
    }

    /**
     * Alias for image_url - used as cover image.
     */
    public function getCoverUrlAttribute(): ?string
    {
        return $this->image_url;
    }

    // Methods
    public function isLikedBy(?User $user): bool
    {
        return $user ? $this->likes()->where('user_id', $user->id)->exists() : false;
    }

    public function isWishlistedBy(?User $user): bool
    {
        return $user ? $this->wishlists()->where('user_id', $user->id)->exists() : false;
    }

    public function isImage(): bool
    {
        return $this->type === self::TYPE_IMAGE;
    }

    public function isVideo(): bool
    {
        return $this->type === self::TYPE_VIDEO;
    }

    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }

    protected function formatCount(int $count): string
    {
        if ($count >= 1000000) {
            return number_format($count / 1000000, 1) . 'M';
        }
        if ($count >= 1000) {
            return number_format($count / 1000, 1) . 'K';
        }
        return (string) $count;
    }
}
