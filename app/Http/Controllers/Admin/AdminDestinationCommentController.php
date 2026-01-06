<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DestinationComment;
use App\Models\Destination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AdminDestinationCommentController extends Controller
{
    public function index(Request $request)
    {
        $query = DestinationComment::with(['user', 'admin', 'destination', 'replies.user', 'replies.admin'])
            ->root(); // Only get top-level comments

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('content', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($u) use ($search) {
                        $u->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by Destination
        if ($request->filled('destination_id')) {
            $query->where('destination_id', $request->destination_id);
        }

        $comments = $query->orderBy('is_pinned', 'desc')->latest()->paginate(15)->withQueryString();

        // Transform for Vue
        $comments->getCollection()->transform(fn($c) => [
            'id' => $c->id,
            'content' => $c->content,
            'is_visible' => $c->is_visible,
            'is_pinned' => $c->is_pinned,
            'destination_id' => $c->destination_id,
            'created_at' => $c->created_at,
            'user' => $c->user ? ['name' => $c->user->name] : null,
            'admin' => $c->admin ? ['name' => $c->admin->name] : null,
            'destination' => $c->destination ? ['name' => $c->destination->name] : null,
            'replies' => $c->replies->map(fn($r) => [
                'id' => $r->id,
                'content' => $r->content,
                'user' => $r->user ? ['name' => $r->user->name] : null,
                'admin' => $r->admin ? ['name' => $r->admin->name] : null,
            ]),
        ]);

        $destinationList = Destination::select('id', 'name')->orderBy('name')->get();
        $filters = $request->only(['search', 'destination_id']);

        return Inertia::render('Admin/Destinations/Comments', compact('comments', 'destinationList', 'filters'));
    }

    /**
     * Store a new comment (admin can add comments)
     */
    public function store(Request $request)
    {
        $request->validate([
            'destination_id' => 'required|exists:destinations,id',
            'content' => 'required|string|max:2000',
            'parent_id' => 'nullable|exists:destination_comments,id',
        ]);

        DestinationComment::create([
            'admin_id' => Auth::guard('admin')->id(),
            'destination_id' => $request->input('destination_id'),
            'content' => $request->input('content'),
            'parent_id' => $request->input('parent_id'),
            'is_visible' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => $request->parent_id ? 'Balasan berhasil ditambahkan' : 'Komentar berhasil ditambahkan'
        ]);
    }

    /**
     * Reply to a comment
     */
    public function reply(Request $request, DestinationComment $comment)
    {
        $request->validate([
            'content' => 'required|string|max:2000',
        ]);

        DestinationComment::create([
            'admin_id' => Auth::guard('admin')->id(),
            'destination_id' => $comment->destination_id,
            'content' => $request->input('content'),
            'parent_id' => $comment->id,
            'is_visible' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Balasan berhasil ditambahkan'
        ]);
    }

    public function destroy(DestinationComment $comment)
    {
        $comment->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Komentar berhasil dihapus'
            ]);
        }

        return back()->with('success', 'Komentar berhasil dihapus');
    }

    public function batchDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:destination_comments,id'
        ]);

        DestinationComment::whereIn('id', $request->ids)->delete();

        return response()->json([
            'success' => true,
            'message' => count($request->ids) . ' komentar berhasil dihapus'
        ]);
    }

    public function toggleStatus(DestinationComment $comment)
    {
        $comment->update([
            'is_visible' => !$comment->is_visible
        ]);

        return response()->json([
            'success' => true,
            'is_visible' => $comment->is_visible,
            'message' => $comment->is_visible ? 'Komentar ditampilkan' : 'Komentar disembunyikan'
        ]);
    }

    public function togglePin(DestinationComment $comment)
    {
        $comment->update([
            'is_pinned' => !$comment->is_pinned
        ]);

        return response()->json([
            'success' => true,
            'is_pinned' => $comment->is_pinned,
            'message' => $comment->is_pinned ? 'Komentar disematkan' : 'Komentar tidak lagi disematkan'
        ]);
    }
}
