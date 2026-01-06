<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FaunaComment;
use App\Models\Fauna;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AdminFaunaCommentController extends Controller
{
    public function index(Request $request)
    {
        $query = FaunaComment::with(['user', 'admin', 'fauna', 'replies.user', 'replies.admin'])
            ->topLevel();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('content', 'like', "%{$search}%")
                    ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('fauna_id')) {
            $query->where('fauna_id', $request->fauna_id);
        }

        $comments = $query->orderBy('is_pinned', 'desc')->latest()->paginate(15)->withQueryString();

        $comments->getCollection()->transform(fn($c) => [
            'id' => $c->id,
            'content' => $c->content,
            'is_approved' => $c->is_approved,
            'is_pinned' => $c->is_pinned,
            'fauna_id' => $c->fauna_id,
            'created_at' => $c->created_at,
            'user' => $c->user ? ['name' => $c->user->name] : null,
            'admin' => $c->admin ? ['name' => $c->admin->name] : null,
            'fauna' => $c->fauna ? ['name' => $c->fauna->name] : null,
            'replies' => $c->replies->map(fn($r) => ['id' => $r->id, 'content' => $r->content, 'user' => $r->user ? ['name' => $r->user->name] : null, 'admin' => $r->admin ? ['name' => $r->admin->name] : null]),
        ]);

        $faunaList = Fauna::select('id', 'name')->orderBy('name')->get();
        $filters = $request->only(['search', 'fauna_id']);

        return Inertia::render('Admin/Fauna/Comments', compact('comments', 'faunaList', 'filters'));
    }

    /**
     * Store a new comment (admin can add comments)
     */
    public function store(Request $request)
    {
        $request->validate([
            'fauna_id' => 'required|exists:fauna,id',
            'content' => 'required|string|max:2000',
            'parent_id' => 'nullable|exists:fauna_comments,id',
        ]);

        FaunaComment::create([
            'admin_id' => Auth::guard('admin')->id(),
            'fauna_id' => $request->input('fauna_id'),
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
    public function reply(Request $request, FaunaComment $comment)
    {
        $request->validate([
            'content' => 'required|string|max:2000',
        ]);

        FaunaComment::create([
            'admin_id' => Auth::guard('admin')->id(),
            'fauna_id' => $comment->fauna_id,
            'content' => $request->input('content'),
            'parent_id' => $comment->id,
            'is_approved' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Balasan berhasil ditambahkan'
        ]);
    }

    public function destroy(FaunaComment $comment)
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
            'ids.*' => 'exists:fauna_comments,id'
        ]);

        FaunaComment::whereIn('id', $request->ids)->delete();

        return response()->json([
            'success' => true,
            'message' => count($request->ids) . ' komentar berhasil dihapus'
        ]);
    }

    public function toggleStatus(FaunaComment $comment)
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

    public function togglePin(FaunaComment $comment)
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
