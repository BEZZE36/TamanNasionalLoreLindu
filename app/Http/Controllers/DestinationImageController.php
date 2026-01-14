<?php

namespace App\Http\Controllers;

use App\Models\DestinationImage;
use App\Services\ImageService;
use Illuminate\Http\Request;

class DestinationImageController extends Controller
{
    public function show(DestinationImage $image)
    {
        if (!$image->image_data) {
            abort(404);
        }

        return ImageService::serve(
            $image->image_data,
            $image->image_mime ?? 'image/jpeg',
            md5($image->updated_at . $image->id)
        );
    }
}
