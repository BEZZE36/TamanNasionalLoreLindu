<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Flora extends Model
{
    use HasFactory;

    protected $table = 'flora';

    /**
     * The accessors to append to the model's array form.
     */
    protected $appends = [
        'image_url',
        'category_label',
    ];

    protected $fillable = [
        'name',
        'slug',
        'local_name',
        'scientific_name',
        'description',
        'image_path',
        'image_data',
        'image_mime',
        'category',
        'conservation_status',

        'meta_title',
        'meta_description',
        'is_featured',
        'is_active',
        'sort_order',
        'view_count',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    // Category constants
    public const CATEGORY_UMUM = 'umum';
    public const CATEGORY_LANGKA = 'langka';
    public const CATEGORY_ENDEMIK = 'endemik';

    public const CATEGORIES = [
        self::CATEGORY_UMUM => 'Umum',
        self::CATEGORY_LANGKA => 'Langka',
        self::CATEGORY_ENDEMIK => 'Endemik',
    ];

    // Accessors
    public function getImageUrlAttribute(): string
    {
        // Prioritize database storage
        if ($this->image_data) {
            return route('images.flora', $this->id);
        }

        if (!$this->image_path) {
            return asset('images/placeholder-no-image.svg');
        }

        if (str_starts_with($this->image_path, 'http')) {
            return $this->image_path;
        }

        // Check if file exists in storage first (for uploaded images)
        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($this->image_path)) {
            return asset('storage/' . $this->image_path);
        }

        // Fallback to public/assets folder (for seeded images)
        return asset('assets/' . $this->image_path);
    }

    public function getCategoryLabelAttribute(): string
    {
        if (empty($this->category)) {
            return 'Umum';
        }
        return self::CATEGORIES[$this->category] ?? ucfirst($this->category) ?? 'Umum';
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function images()
    {
        return $this->hasMany(FloraImage::class)->orderBy('sort_order');
    }

    public function comments()
    {
        return $this->hasMany(FloraComment::class)->orderBy('created_at', 'desc');
    }



    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where('id', $value)
            ->orWhere('slug', $value)
            ->firstOrFail();
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = \Illuminate\Support\Str::slug($model->name);
            }
        });

        static::updating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = \Illuminate\Support\Str::slug($model->name);
            }
        });
    }

    // Conservation Status (IUCN)
    public const STATUS_LC = 'LC'; // Least Concern
    public const STATUS_NT = 'NT'; // Near Threatened
    public const STATUS_VU = 'VU'; // Vulnerable
    public const STATUS_EN = 'EN'; // Endangered
    public const STATUS_CR = 'CR'; // Critically Endangered

    public const CONSERVATION_STATUSES = [
        self::STATUS_LC => 'Risiko Rendah (LC)',
        self::STATUS_NT => 'Hampir Terancam (NT)',
        self::STATUS_VU => 'Rentan (VU)',
        self::STATUS_EN => 'Terancam Punah (EN)',
        self::STATUS_CR => 'Kritis (CR)',
    ];

    public const CONSERVATION_COLORS = [
        self::STATUS_LC => 'green',
        self::STATUS_NT => 'blue',
        self::STATUS_VU => 'yellow',
        self::STATUS_EN => 'orange',
        self::STATUS_CR => 'red',
    ];

    public function getConservationStatusLabelAttribute(): string
    {
        if (empty($this->conservation_status)) {
            return '-';
        }
        return self::CONSERVATION_STATUSES[$this->conservation_status] ?? $this->conservation_status;
    }

    public function getConservationStatusColorAttribute(): string
    {
        return self::CONSERVATION_COLORS[$this->conservation_status] ?? 'gray';
    }
}
