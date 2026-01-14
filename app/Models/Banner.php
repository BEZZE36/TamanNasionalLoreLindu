<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    /**
     * The accessors to append to the model's array form.
     */
    protected $appends = [
        'image_url',
        'mobile_image_url',
    ];

    protected $fillable = [
        'title',
        'subtitle',
        'image_path',
        'mobile_image_path',
        'link_url',
        'link_target',
        'sort_order',
        'is_active',
        'start_at',
        'end_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'start_at' => 'datetime',
            'end_at' => 'datetime',
        ];
    }

    // Accessors
    public function getImageUrlAttribute(): string
    {
        // Prioritize database storage
        if ($this->image_data) {
            return route('images.banner', $this->id);
        }

        if (!$this->image_path) {
            return asset('images/placeholder-no-image.svg');
        }

        // Check if file exists in storage first (for uploaded images)
        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($this->image_path)) {
            return asset('storage/' . $this->image_path);
        }

        // Fallback to public/assets folder (for seeded images)
        if (str_starts_with($this->image_path, 'assets/')) {
            return asset($this->image_path);
        }

        return asset('assets/' . $this->image_path);
    }

    public function getMobileImageUrlAttribute(): ?string
    {
        if (!$this->mobile_image_path) {
            return null;
        }

        // Check if file exists in storage first (for uploaded images)
        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($this->mobile_image_path)) {
            return asset('storage/' . $this->mobile_image_path);
        }

        // Fallback to public/assets folder (for seeded images)
        if (str_starts_with($this->mobile_image_path, 'assets/')) {
            return asset($this->mobile_image_path);
        }

        return asset('assets/' . $this->mobile_image_path);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('start_at')
                    ->orWhere('start_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_at')
                    ->orWhere('end_at', '>=', now());
            })
            ->orderBy('sort_order');
    }

    // Helpers
    public function isCurrentlyActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();

        if ($this->start_at && $this->start_at->isFuture()) {
            return false;
        }

        if ($this->end_at && $this->end_at->isPast()) {
            return false;
        }

        return true;
    }
}
