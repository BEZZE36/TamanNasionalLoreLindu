<?php

namespace App\Models\Traits;

trait HasGalleryScopes
{
    /**
     * Scope: Active galleries only.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Featured galleries only.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope: Order by sort_order and created_at.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('created_at', 'desc');
    }

    /**
     * Scope: Images only.
     */
    public function scopeImages($query)
    {
        return $query->where('type', self::TYPE_IMAGE);
    }

    /**
     * Scope: Videos only.
     */
    public function scopeVideos($query)
    {
        return $query->where('type', self::TYPE_VIDEO);
    }

    /**
     * Scope: Search by keyword.
     */
    public function scopeSearch($query, ?string $search)
    {
        if (!$search) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('location', 'like', "%{$search}%")
                ->orWhereHas('destination', function ($dq) use ($search) {
                    $dq->where('name', 'like', "%{$search}%");
                });
        });
    }

    /**
     * Scope: Popular (by view count).
     */
    public function scopePopular($query)
    {
        return $query->orderBy('view_count', 'desc');
    }

    /**
     * Scope: Alphabetical by title.
     */
    public function scopeAlphabetical($query)
    {
        return $query->orderBy('title', 'asc');
    }
}
