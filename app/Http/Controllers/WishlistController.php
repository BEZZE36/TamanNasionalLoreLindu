<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\ArticleFavorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Destinations wishlist
        $destinations = Auth::user()->wishlist()->with(['images', 'prices'])->latest('wishlists.created_at')->paginate(12);

        // Article favorites (blog and news)
        $articles = ArticleFavorite::with([
            'article' => function ($q) {
                $q->select('id', 'title', 'slug', 'excerpt', 'featured_image', 'image_data', 'image_mime', 'category', 'published_at', 'views_count', 'likes_count');
            }
        ])
            ->where('user_id', Auth::id())
            ->latest()
            ->get()
            ->map(fn($fav) => $fav->article)
            ->filter(); // Remove null articles

        // Separate blog articles and news
        $blogArticles = $articles->filter(fn($a) => $a->category !== 'berita')->values();
        $newsArticles = $articles->filter(fn($a) => $a->category === 'berita')->values();

        return \Inertia\Inertia::render('User/Wishlist/Index', compact('destinations', 'blogArticles', 'newsArticles'));
    }

    /**
     * Toggle wishlist status for a destination.
     */
    /**
     * Toggle wishlist status for a destination.
     */
    public function toggle(Request $request, $slug)
    {
        $destination = Destination::where('slug', $slug)->firstOrFail();
        $user = Auth::user();

        if ($user->hasWishlist($destination->id)) {
            $user->wishlist()->detach($destination->id);
            $message = 'Destinasi dihapus dari favorit.';
            $status = 'removed';
        } else {
            $user->wishlist()->attach($destination->id);
            $message = 'Destinasi ditambahkan ke favorit.';
            $status = 'added';
        }

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'action' => $status,
                'message' => $message
            ]);
        }

        return back()->with('success', $message);
    }
}
