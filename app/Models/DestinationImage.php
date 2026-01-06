<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DestinationImage extends Model
{
    use HasFactory;

    /**
     * The accessors to append to the model's array form.
     */
    protected $appends = [
        'url',
        'image_url',
    ];

    protected $fillable = [
        'destination_id',
        'image_path',
        'image_data',
        'image_mime',
        'alt_text',
        'is_primary',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }

    // Relationships
    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    // Accessors
    public function getUrlAttribute(): string
    {
        // Prioritize database storage
        if ($this->image_data) {
            return route('images.destination', $this->id);
        }

        if (!$this->image_path) {
            return asset('images/placeholder-no-image.svg');
        }

        if (str_starts_with($this->image_path, 'http')) {
            return $this->image_path;
        }

        // Check if file exists in storage first (for uploaded images)
        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($this->image_path)) {
            return '/storage/' . $this->image_path;
        }

        // Fallback to public/assets folder (for seeded images)
        return '/assets/' . $this->image_path;
    }

    public function getImageUrlAttribute(): string
    {
        return $this->url;
    }
}
