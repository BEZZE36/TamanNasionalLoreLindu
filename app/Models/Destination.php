<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Destination extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The accessors to append to the model's array form.
     */
    protected $appends = [
        'primary_image_url',
        'formatted_adult_price',
        'short_description',
    ];

    protected $fillable = [
        'name',
        'slug',
        'description',
        'name',
        'slug',
        'category_id',
        'description',
        'short_description',
        'address',
        'city',
        'province',
        'latitude',
        'longitude',
        'google_maps_embed',
        'operating_hours',
        'contact_phone',
        'contact_email',
        'facilities',
        'rules',
        'parking_fees',
        'is_featured',
        'is_active',
        'rating',
        'review_count',
        'visitor_count',
        'views_count',
        'meta_title',
        'meta_description',
        'keywords',
    ];

    protected function casts(): array
    {
        return [
            'facilities' => 'array',
            'rules' => 'array',
            'parking_fees' => 'array',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'rating' => 'decimal:1',
        ];
    }

    // Auto-generate slug
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($destination) {
            if (empty($destination->slug)) {
                $destination->slug = Str::slug($destination->name);
            }
        });
    }

    // Relationships
    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(DestinationCategory::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(DestinationImage::class)->orderBy('sort_order');
    }

    public function prices(): HasMany
    {
        return $this->hasMany(DestinationPrice::class)->where('is_active', true);
    }

    public function allPrices(): HasMany
    {
        return $this->hasMany(DestinationPrice::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Feedback::class)->where('is_published', true)->latest();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(DestinationComment::class)->orderBy('is_pinned', 'desc')->orderBy('created_at', 'desc');
    }

    public function favoritedBy(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'wishlists', 'destination_id', 'user_id')
            ->withTimestamps();
    }

    // Accessors
    public function getPrimaryImageAttribute(): ?DestinationImage
    {
        $primary = $this->images->where('is_primary', true)->first();
        return $primary ?? $this->images->first();
    }

    public function getPrimaryImageUrlAttribute(): string
    {
        $primaryImage = $this->primary_image;

        if (!$primaryImage) {
            return asset('assets/logo.png');
        }

        // Use the DestinationImage accessor which handles database images
        return $primaryImage->url;
    }

    // Price helpers
    public function getPrice(string $category): ?DestinationPrice
    {
        return $this->prices->where('category', $category)->first();
    }

    public function getAdultPriceAttribute(): ?float
    {
        // Get the minimum price from all active prices
        // Since categories are now dynamic slugs, we get the minimum price
        $minPrice = $this->prices->min('price');
        return $minPrice ?: null;
    }

    public function getFormattedAdultPriceAttribute(): string
    {
        $price = $this->adult_price;
        return $price ? 'Rp ' . number_format($price, 0, ',', '.') : 'Gratis';
    }

    public function getShortDescriptionAttribute($value): string
    {
        // Get raw value from attributes (not the $value parameter which may be null for appended attributes)
        $rawValue = $this->attributes['short_description'] ?? null;

        // If short_description is set in database, use it
        if (!empty($rawValue)) {
            return $rawValue;
        }

        // Otherwise, generate from description by stripping HTML and truncating
        $description = $this->attributes['description'] ?? '';
        $plainText = strip_tags($description);
        $plainText = html_entity_decode($plainText, ENT_QUOTES, 'UTF-8');
        $plainText = preg_replace('/\s+/', ' ', $plainText);
        $plainText = trim($plainText);

        return Str::limit($plainText, 120);
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

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('city', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        });
    }
}
