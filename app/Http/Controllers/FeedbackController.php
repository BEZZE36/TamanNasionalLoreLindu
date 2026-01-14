<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Models\Destination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    /**
     * Display public feedback/testimonials
     */
    public function index()
    {
        $feedbacks = Feedback::published()
            ->withRating()
            ->with(['user', 'destination'])
            ->latest()
            ->paginate(12);

        $avgRating = Feedback::published()->withRating()->avg('rating') ?? 0;
        $totalReviews = Feedback::published()->withRating()->count();

        return \Inertia\Inertia::render('Public/Testimonials/Index', compact('feedbacks', 'avgRating', 'totalReviews'));
    }

    /**
     * Show feedback submission form (for logged in users)
     */
    public function create()
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu untuk memberikan ulasan.');
        }

        // Check if user has any confirmed or used booking (valid statuses from Booking model)
        $hasBooking = Auth::user()->bookings()->whereIn('status', ['confirmed', 'used'])->exists();
        $destinations = Destination::active()->orderBy('name')->get();

        return \Inertia\Inertia::render('Public/Testimonials/Create', compact('hasBooking', 'destinations'));
    }

    /**
     * Store feedback submission
     * Testimonials are published immediately without admin verification
     */
    public function store(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'destination_id' => 'nullable|exists:destinations,id',
            'rating' => 'required|integer|min:1|max:5',
            'message' => 'required|string|min:20|max:1000',
            'is_anonymous' => 'boolean',
        ]);

        // Get destination name for subject if selected
        $destination = $validated['destination_id']
            ? \App\Models\Destination::find($validated['destination_id'])
            : null;

        $subject = $destination
            ? 'Ulasan untuk ' . $destination->name
            : 'Ulasan Umum Taman Nasional Lore Lindu';

        Feedback::create([
            'user_id' => Auth::id(),
            'destination_id' => $validated['destination_id'],
            'display_name' => Auth::user()->name,
            'is_anonymous' => $validated['is_anonymous'] ?? false,
            'subject' => $subject,
            'message' => $validated['message'],
            'rating' => $validated['rating'],
            'status' => 'unread', // Waiting for admin review
            'is_published' => false, // Requires admin verification
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('testimonials')->with('success', 'Terima kasih! Ulasan Anda akan ditampilkan setelah diverifikasi oleh admin.');
    }
}
