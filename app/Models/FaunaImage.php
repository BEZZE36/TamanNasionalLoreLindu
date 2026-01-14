<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaunaImage extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    /**
     * The accessors to append to the model's array form.
     */
    protected $appends = ['image_url'];

    protected $fillable = [
        'fauna_id',
        'image_data',
        'image_mime',
        'sort_order',
    ];

    public function fauna()
    {
        return $this->belongsTo(Fauna::class);
    }

    public function getImageUrlAttribute()
    {
        return route('images.fauna_gallery', $this->id);
    }
}
