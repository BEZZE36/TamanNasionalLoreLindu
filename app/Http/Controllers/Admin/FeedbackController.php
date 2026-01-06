<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\FeedbackReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class FeedbackController extends Controller
{
    public function index(Request $request)
    {
        $query = Feedback::with(['user', 'destination']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->notArchived();
        }

        // Filter by rating
        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('subject', 'like', '%' . $request->search . '%')
                    ->orWhere('message', 'like', '%' . $request->search . '%')
                    ->orWhere('display_name', 'like', '%' . $request->search . '%');
            });
        }

        $feedbacks = $query->latest()->paginate(20)->withQueryString()
            ->through(fn($f) => [
                'id' => $f->id,
                'display_name' => $f->display_name,
                'message' => $f->message,
                'rating' => $f->rating,
                'status' => $f->status,
                'is_published' => $f->is_published,
                'created_at' => $f->created_at,
                'destination' => $f->destination ? ['id' => $f->destination->id, 'name' => $f->destination->name] : null,
            ]);

        // Stats
        $stats = [
            'unread' => Feedback::unread()->count(),
            'total' => Feedback::notArchived()->count(),
            'published' => Feedback::published()->count(),
        ];

        return Inertia::render('Admin/Feedback/Index', [
            'feedbacks' => $feedbacks,
            'stats' => $stats,
            'filters' => $request->only(['search', 'status', 'rating']),
        ]);
    }

    public function show(Feedback $feedback)
    {
        $feedback->markAsRead();
        $feedback->load(['user', 'destination', 'replies.admin']);

        return Inertia::render('Admin/Feedback/Show', [
            'feedback' => [
                'id' => $feedback->id,
                'display_name' => $feedback->display_name,
                'message' => $feedback->message,
                'rating' => $feedback->rating,
                'status' => $feedback->status,
                'is_published' => $feedback->is_published,
                'created_at' => $feedback->created_at,
                'destination' => $feedback->destination ? ['name' => $feedback->destination->name] : null,
                'replies' => $feedback->replies->map(fn($r) => [
                    'id' => $r->id,
                    'reply_message' => $r->reply_message,
                    'is_public' => $r->is_public,
                    'created_at' => $r->created_at,
                    'admin' => $r->admin ? ['name' => $r->admin->name] : null,
                ]),
            ]
        ]);
    }

    public function reply(Request $request, Feedback $feedback)
    {
        $validated = $request->validate([
            'message' => 'required|string',
            'is_public' => 'boolean',
        ]);

        FeedbackReply::create([
            'feedback_id' => $feedback->id,
            'admin_id' => Auth::guard('admin')->id(),
            'reply_message' => $validated['message'],
            'is_public' => $request->boolean('is_public'),
        ]);

        $feedback->update(['status' => Feedback::STATUS_REPLIED]);

        return back()->with('success', 'Balasan berhasil dikirim.');
    }

    public function create()
    {
        $destinations = \App\Models\Destination::select('id', 'name')->get();
        return Inertia::render('Admin/Feedback/Create', ['destinations' => $destinations]);
    }

    public function deleteReply(FeedbackReply $reply)
    {
        $reply->delete();
        return back()->with('success', 'Balasan berhasil dihapus.');
    }

    public function updateReply(Request $request, FeedbackReply $reply)
    {
        $validated = $request->validate([
            'reply_message' => 'required|string',
            'is_public' => 'boolean',
        ]);

        $reply->update([
            'reply_message' => $validated['reply_message'],
            'is_public' => $request->boolean('is_public'),
        ]);

        return back()->with('success', 'Balasan berhasil diperbarui.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'display_name' => 'required|string|max:255',
            'destination_id' => 'nullable|exists:destinations,id',
            'message' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
            'status' => 'required|in:unread,read,replied,archived',
            'is_published' => 'boolean',
        ]);

        Feedback::create([
            'display_name' => $validated['display_name'],
            'destination_id' => $validated['destination_id'],
            'message' => $validated['message'],
            'rating' => $validated['rating'],
            'status' => $validated['status'],
            'is_published' => $request->boolean('is_published'),
            'ip_address' => $request->ip(),
            'is_anonymous' => true,
        ]);

        return redirect()->route('admin.feedback.index')
            ->with('success', 'Feedback berhasil ditambahkan manual.');
    }

    public function edit(Feedback $feedback)
    {
        $destinations = \App\Models\Destination::select('id', 'name')->get();
        return Inertia::render('Admin/Feedback/Edit', [
            'feedback' => [
                'id' => $feedback->id,
                'display_name' => $feedback->display_name,
                'destination_id' => $feedback->destination_id,
                'message' => $feedback->message,
                'rating' => $feedback->rating,
                'status' => $feedback->status,
                'is_published' => $feedback->is_published,
            ],
            'destinations' => $destinations,
        ]);
    }

    public function update(Request $request, Feedback $feedback)
    {
        $validated = $request->validate([
            'display_name' => 'required|string|max:255',
            'destination_id' => 'nullable|exists:destinations,id',
            'message' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
            'status' => 'required|in:unread,read,replied,archived',
            'is_published' => 'boolean',
        ]);

        $feedback->update([
            'display_name' => $validated['display_name'],
            'destination_id' => $validated['destination_id'],
            'message' => $validated['message'],
            'rating' => $validated['rating'],
            'status' => $validated['status'],
            'is_published' => $request->boolean('is_published'),
        ]);

        return redirect()->route('admin.feedback.index')
            ->with('success', 'Feedback berhasil diperbarui.');
    }

    public function updateStatus(Request $request, Feedback $feedback)
    {
        $validated = $request->validate([
            'status' => 'nullable|in:unread,read,replied,archived',
            'is_published' => 'boolean',
        ]);

        $updateData = [];

        if ($request->has('status')) {
            $updateData['status'] = $validated['status'];
        }

        if ($request->has('is_published')) {
            $updateData['is_published'] = $request->boolean('is_published');
        }

        $feedback->update($updateData);

        return back()->with('success', 'Status feedback berhasil diperbarui.');
    }

    public function destroy(Feedback $feedback)
    {
        $feedback->update(['status' => Feedback::STATUS_ARCHIVED]);

        return redirect()->route('admin.feedback.index')
            ->with('success', 'Feedback berhasil diarsipkan.');
    }
}
