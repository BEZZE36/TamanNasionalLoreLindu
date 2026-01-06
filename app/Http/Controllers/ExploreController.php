<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use Illuminate\Http\Request;

class ExploreController extends Controller
{
    /**
     * Display the interactive map.
     */
    public function index()
    {
        $destinations = Destination::active()
            ->select(['id', 'name', 'slug', 'city', 'latitude', 'longitude', 'rating', 'category_id'])
            ->with(['images', 'category'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->map(function ($destination) {
                return [
                    'id' => $destination->id,
                    'name' => $destination->name,
                    'slug' => $destination->slug,
                    'city' => $destination->city,
                    'lat' => (float) $destination->latitude,
                    'lng' => (float) $destination->longitude,
                    'image' => $destination->primary_image_url,
                    'rating' => $destination->rating ?? 0,
                    'category' => $destination->category ? $destination->category->name : 'Umum',
                    'icon' => $destination->category ? $destination->category->icon : '📍',
                    'price' => $destination->formatted_adult_price,
                ];
            });

        return \Inertia\Inertia::render('Public/Explore/Map', compact('destinations'));
    }
}

