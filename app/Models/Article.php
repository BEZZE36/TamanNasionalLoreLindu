<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Article extends Model
{
    use HasFactory;

    protected $appends = [
        'image_url',
        'category_label',
        'category_color',
        'reading_time_text',
        'formatted_date',
    ];

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'image_data',
        'image_mime',
        'category',
        'author_name',
        'user_id',
        'is_published',
        'is_featured',
        'is_pinned',
        'published_at',
        'scheduled_at',
        'archived_at',
        'views_count',
        'likes_count',
        'reading_time',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'revision_count',
        'last_auto_save',
        'locale',
        'translation_of',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_featured' => 'boolean',
        'is_pinned' => 'boolean',
        'published_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'archived_at' => 'datetime',
        'last_auto_save' => 'datetime',
        'views_count' => 'integer',
        'likes_count' => 'integer',
        'reading_time' => 'integer',
        'revision_count' => 'integer',
    ];

    const CATEGORIES = [
        'berita' => 'Berita',
        'wisata' => 'Wisata',
        'budaya' => 'Budaya',
        'konservasi' => 'Konservasi',
        'edukasi' => 'Edukasi',
        'event' => 'Event',
        'pengumuman' => 'Pengumuman',
    ];

    const LOCALES = [
        'id' => 'Indonesia',
        'en' => 'English',
    ];

    // ========== SCOPES ==========

    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->whereNull('archived_at')
            ->where(function ($q) {
                // Show if: no scheduled_at (publish immediately) OR scheduled_at has passed
                $q->whereNull('scheduled_at')
                    ->orWhere('scheduled_at', '<=', now());
            });
    }

    public function scopeScheduled($query)
    {
        return $query->where('is_published', true)
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '>', now());
    }

    public function scopeArchived($query)
    {
        return $query->whereNotNull('archived_at');
    }

    public function scopeDraft($query)
    {
        return $query->where('is_published', false)
            ->whereNull('archived_at');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByLocale($query, $locale)
    {
        return $query->where('locale', $locale);
    }

    // ========== ACCESSORS ==========

    public function getCategoryLabelAttribute()
    {
        return self::CATEGORIES[$this->category] ?? ucfirst($this->category);
    }

    public function getCategoryColorAttribute()
    {
        $colors = [
            'berita' => 'red',
            'wisata' => 'primary',
            'budaya' => 'amber',
            'konservasi' => 'green',
            'edukasi' => 'blue',
            'event' => 'purple',
            'pengumuman' => 'red',
        ];
        return $colors[$this->category] ?? 'gray';
    }

    public function getImageUrlAttribute()
    {
        if ($this->image_data) {
            // Add cache-busting parameter to force browser refresh when image changes
            return route('images.article', $this->id) . '?v=' . ($this->updated_at?->timestamp ?? time());
        }
        if ($this->featured_image && file_exists(storage_path('app/public/' . $this->featured_image))) {
            return asset('storage/' . $this->featured_image) . '?v=' . ($this->updated_at?->timestamp ?? time());
        }
        return asset('images/placeholder-no-image.svg');
    }

    public function getReadingTimeTextAttribute()
    {
        $minutes = $this->reading_time ?: $this->calculateReadingTime();
        return $minutes . ' menit baca';
    }

    public function getFormattedDateAttribute()
    {
        if ($this->published_at) {
            return $this->published_at->format('d M Y');
        }
        return $this->created_at?->format('d M Y') ?? '-';
    }

    public function getStatusAttribute()
    {
        if ($this->archived_at)
            return 'archived';
        if (!$this->is_published)
            return 'draft';
        if ($this->scheduled_at && $this->scheduled_at->isFuture())
            return 'scheduled';
        return 'published';
    }

    public function getStatusLabelAttribute()
    {
        return match ($this->status) {
            'archived' => 'Diarsipkan',
            'draft' => 'Draft',
            'scheduled' => 'Terjadwal',
            'published' => 'Dipublikasikan',
            default => 'Unknown',
        };
    }

    // ========== RELATIONSHIPS ==========

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tags()
    {
        return $this->belongsToMany(ArticleTag::class, 'article_tag', 'article_id', 'tag_id');
    }

    public function likes()
    {
        return $this->hasMany(ArticleLike::class);
    }

    public function favorites()
    {
        return $this->hasMany(ArticleFavorite::class);
    }

    public function views()
    {
        return $this->hasMany(ArticleView::class);
    }

    public function comments()
    {
        return $this->hasMany(ArticleComment::class);
    }

    public function revisions()
    {
        return $this->hasMany(ArticleRevision::class)->latest();
    }

    public function translations()
    {
        return $this->hasMany(Article::class, 'translation_of');
    }

    public function originalArticle()
    {
        return $this->belongsTo(Article::class, 'translation_of');
    }

    // ========== HELPERS ==========

    public function calculateReadingTime(): int
    {
        $wordCount = str_word_count(strip_tags($this->content ?? ''));
        return max(1, (int) ceil($wordCount / 200));
    }

    public function updateReadingTime(): void
    {
        $this->update(['reading_time' => $this->calculateReadingTime()]);
    }

    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    public function incrementLikes(): void
    {
        $this->increment('likes_count');
    }

    public function decrementLikes(): void
    {
        $this->decrement('likes_count');
    }

    public function isLikedBy(?User $user): bool
    {
        if (!$user)
            return false;
        return $this->likes()->where('user_id', $user->id)->exists();
    }

    public function isFavoritedBy(?User $user): bool
    {
        if (!$user)
            return false;
        return $this->favorites()->where('user_id', $user->id)->exists();
    }

    public function archive(): void
    {
        $this->update(['archived_at' => now()]);
    }

    public function restore(): void
    {
        $this->update(['archived_at' => null]);
    }

    public function publish(): void
    {
        $this->update([
            'is_published' => true,
            'published_at' => now(),
            'archived_at' => null,
        ]);
    }

    public function unpublish(): void
    {
        $this->update(['is_published' => false]);
    }

    public function saveRevision(int $adminId, ?string $changeSummary = null): ArticleRevision
    {
        $revision = $this->revisions()->create([
            'admin_id' => $adminId,
            'title' => $this->title,
            'excerpt' => $this->excerpt,
            'content' => $this->content,
            'meta' => [
                // SEO fields
                'meta_title' => $this->meta_title,
                'meta_description' => $this->meta_description,
                'meta_keywords' => $this->meta_keywords,
                // Additional fields
                'author_name' => $this->author_name,
                'category' => $this->category,
                'is_featured' => $this->is_featured,
                'is_published' => $this->is_published,
                'is_pinned' => $this->is_pinned,
                'scheduled_at' => $this->scheduled_at?->toIso8601String(),
                'locale' => $this->locale,
            ],
            'change_summary' => $changeSummary,
        ]);

        $this->increment('revision_count');

        return $revision;
    }

    // ========== BOOT ==========

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($article) {
            if (empty($article->slug)) {
                $article->slug = Str::slug($article->title);
            }
            if (empty($article->published_at) && $article->is_published) {
                $article->published_at = now();
            }
            $article->reading_time = $article->calculateReadingTime();
        });

        static::updating(function ($article) {
            if ($article->isDirty('content')) {
                $article->reading_time = $article->calculateReadingTime();
            }
        });
    }
}
