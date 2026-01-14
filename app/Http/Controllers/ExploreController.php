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
            ->select(['id', 'name', 'slug', 'city', 'short_description', 'latitude', 'longitude', 'rating', 'category_id', 'google_maps_embed'])
            ->with(['images', 'category', 'prices'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->map(function ($destination) {
                return [
                    'id' => $destination->id,
                    'name' => $destination->name,
                    'slug' => $destination->slug,
                    'city' => $destination->city,
                    'short_description' => $destination->short_description,
                    'lat' => (float) $destination->latitude,
                    'lng' => (float) $destination->longitude,
                    'image' => $destination->primary_image_url,
                    'rating' => $destination->rating ?? 0,
                    'category' => $destination->category ? $destination->category->name : 'Umum',
                    'category_id' => $destination->category_id,
                    'icon' => $destination->category ? $destination->category->icon : '📍',
                    'price' => $destination->formatted_adult_price,
                    'google_maps_embed' => $destination->google_maps_embed,
                ];
            });

        // Get all categories that have destinations with coordinates
        $categories = \App\Models\DestinationCategory::whereHas('destinations', function ($q) {
            $q->active()->whereNotNull('latitude')->whereNotNull('longitude');
        })->get(['id', 'name', 'icon']);

        return \Inertia\Inertia::render('Public/Explore/Map', compact('destinations', 'categories'));
    }
}

