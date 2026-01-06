<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FloraImage extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    /**
     * The accessors to append to the model's array form.
     */
    protected $appends = ['image_url'];

    protected $fillable = [
        'flora_id',
        'image_data',
        'image_mime',
        'sort_order',
    ];

    public function flora()
    {
        return $this->belongsTo(Flora::class);
    }

    public function getImageUrlAttribute()
    {
        return route('images.flora_gallery', $this->id);
    }
}
