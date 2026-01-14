<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserReviewController extends Controller
{
    /**
     * Display a listing of the authenticated user's reviews.
     */
    public function index()
    {
        $reviews = Auth::user()->reviews()
            ->with(['destination.images', 'publicReplies'])
            ->latest()
            ->paginate(10);

        return \Inertia\Inertia::render('User/Reviews/Index', compact('reviews'));
    }
}
