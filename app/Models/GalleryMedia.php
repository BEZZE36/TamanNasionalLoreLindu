<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GalleryMedia extends Model
{
    protected $table = 'gallery_media';

    /**
     * The accessors to append to the model's array form.
     */
    protected $appends = [
        'url',
    ];

    protected $fillable = [
        'gallery_id',
        'type',
        'image_data',
        'image_mime',
        'video_url',
        'alt_text',
        'sort_order',
    ];

    public function gallery(): BelongsTo
    {
        return $this->belongsTo(Gallery::class);
    }

    // Helper to get URL
    public function getUrlAttribute()
    {
        if ($this->type === 'video') {
            return $this->video_url;
        }

        if ($this->image_data) {
            return route('images.gallery_media', $this->id);
        }

        return null; // or default image
    }
}
