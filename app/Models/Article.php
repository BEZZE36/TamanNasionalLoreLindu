<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Article extends Model
{
    use HasFactory;

    /**
     * The accessors to append to the model's array form.
     * This covers both Blog and News pages since they share the Article model.
     */
    protected $appends = [
        'image_url',
        'category_label',
        'category_color',
        'reading_time',
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
        'published_at',
        'views_count',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_featured' => 'boolean',
        'published_at' => 'datetime',
        'views_count' => 'integer',
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

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->where('published_at', '<=', now());
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    // Accessors
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
        // Prioritize database image
        if ($this->image_data) {
            return route('images.article', $this->id);
        }

        // Fallback to legacy file storage
        if ($this->featured_image && file_exists(storage_path('app/public/' . $this->featured_image))) {
            return asset('storage/' . $this->featured_image);
        }

        return asset('images/placeholder-no-image.svg');
    }

    public function getReadingTimeAttribute()
    {
        $wordCount = str_word_count(strip_tags($this->content));
        $minutes = ceil($wordCount / 200);
        return $minutes . ' menit baca';
    }

    public function getFormattedDateAttribute()
    {
        if ($this->published_at) {
            return $this->published_at->format('d M Y');
        }
        if ($this->created_at) {
            return $this->created_at->format('d M Y');
        }
        return '-';
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Boot
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
        });
    }

    // Helpers
    public function incrementViews()
    {
        $this->increment('views_count');
    }
}
