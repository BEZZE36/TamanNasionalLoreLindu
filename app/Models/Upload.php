<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Upload extends Model
{
    protected $fillable = [
        'hash',
        'filename',
        'image_data',
        'image_mime',
        'size',
    ];
}
