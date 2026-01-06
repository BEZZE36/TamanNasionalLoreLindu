<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GalleryComment;
use App\Models\Gallery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AdminGalleryCommentController extends Controller
{
    public function index(Request $request)
    {
        $query = GalleryComment::with(['user', 'gallery', 'admin', 'replies.user', 'replies.admin'])
            ->topLevel(); // Only get top-level comments

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

        // Filter by Gallery
        if ($request->filled('gallery_id')) {
            $query->where('gallery_id', $request->gallery_id);
        }

        $comments = $query->orderBy('is_pinned', 'desc')->latest()->paginate(15)->withQueryString();

        // Transform for Vue
        $comments->getCollection()->transform(fn($c) => [
            'id' => $c->id,
            'content' => $c->content,
            'is_approved' => $c->is_approved,
            'is_pinned' => $c->is_pinned,
            'gallery_id' => $c->gallery_id,
            'created_at' => $c->created_at,
            'user' => $c->user ? ['name' => $c->user->name] : null,
            'admin' => $c->admin ? ['name' => $c->admin->name] : null,
            'gallery' => $c->gallery ? ['title' => $c->gallery->title] : null,
            'replies' => $c->replies->map(fn($r) => [
                'id' => $r->id,
                'content' => $r->content,
                'user' => $r->user ? ['name' => $r->user->name] : null,
                'admin' => $r->admin ? ['name' => $r->admin->name] : null,
            ]),
        ]);

        $galleryList = Gallery::select('id', 'title')->orderBy('title')->get();
        $filters = $request->only(['search', 'gallery_id']);

        return Inertia::render('Admin/Gallery/Comments', compact('comments', 'galleryList', 'filters'));
    }

    /**
     * Store a new comment (admin can add comments)
     */
    public function store(Request $request)
    {
        $request->validate([
            'gallery_id' => 'required|exists:galleries,id',
            'content' => 'required|string|max:2000',
            'parent_id' => 'nullable|exists:gallery_comments,id',
        ]);

        GalleryComment::create([
            'admin_id' => Auth::guard('admin')->id(),
            'gallery_id' => $request->input('gallery_id'),
            'content' => $request->input('content'),
            'parent_id' => $request->input('parent_id'),
            'is_approved' => true, // Admin comments are auto-approved
        ]);

        return response()->json([
            'success' => true,
            'message' => $request->parent_id ? 'Balasan berhasil ditambahkan' : 'Komentar berhasil ditambahkan'
        ]);
    }

    /**
     * Reply to a comment
     */
    public function reply(Request $request, GalleryComment $comment)
    {
        $request->validate([
            'content' => 'required|string|max:2000',
        ]);

        GalleryComment::create([
            'admin_id' => Auth::guard('admin')->id(),
            'gallery_id' => $comment->gallery_id,
            'content' => $request->input('content'),
            'parent_id' => $comment->id,
            'is_approved' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Balasan berhasil ditambahkan'
        ]);
    }

    public function destroy(GalleryComment $comment)
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
            'ids.*' => 'exists:gallery_comments,id'
        ]);

        GalleryComment::whereIn('id', $request->ids)->delete();

        return response()->json([
            'success' => true,
            'message' => count($request->ids) . ' komentar berhasil dihapus'
        ]);
    }

    public function toggleStatus(GalleryComment $comment)
    {
        $comment->update([
            'is_approved' => !$comment->is_approved
        ]);

        return response()->json([
            'success' => true,
            'is_approved' => $comment->is_approved,
            'message' => $comment->is_approved ? 'Komentar disetujui' : 'Komentar disembunyikan'
        ]);
    }

    public function togglePin(GalleryComment $comment)
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
