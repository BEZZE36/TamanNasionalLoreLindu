<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $destinations = Auth::user()->wishlist()->with(['images', 'prices'])->latest('wishlists.created_at')->paginate(12);
        return \Inertia\Inertia::render('User/Wishlist/Index', compact('destinations'));
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
