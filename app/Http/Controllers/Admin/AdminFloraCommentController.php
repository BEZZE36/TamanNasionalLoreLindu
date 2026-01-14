<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FloraComment;
use App\Models\Flora;
use App\Services\NotificationService;
use App\Services\PushNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AdminFloraCommentController extends Controller
{
    protected NotificationService $notificationService;
    protected PushNotificationService $pushNotificationService;

    public function __construct(
        NotificationService $notificationService,
        PushNotificationService $pushNotificationService
    ) {
        $this->notificationService = $notificationService;
        $this->pushNotificationService = $pushNotificationService;
    }
    public function index(Request $request)
    {
        $query = FloraComment::with(['user', 'admin', 'flora', 'replies.user', 'replies.admin'])
            ->topLevel();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('content', 'like', "%{$search}%")
                    ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('flora_id')) {
            $query->where('flora_id', $request->flora_id);
        }

        $comments = $query->orderBy('is_pinned', 'desc')->latest()->paginate(15)->withQueryString();

        $comments->getCollection()->transform(fn($c) => [
            'id' => $c->id,
            'content' => $c->content,
            'is_approved' => $c->is_approved,
            'is_pinned' => $c->is_pinned,
            'flora_id' => $c->flora_id,
            'created_at' => $c->created_at,
            'user' => $c->user ? ['name' => $c->user->name] : null,
            'admin' => $c->admin ? ['name' => $c->admin->name] : null,
            'flora' => $c->flora ? ['name' => $c->flora->name] : null,
            'replies' => $c->replies->map(fn($r) => ['id' => $r->id, 'content' => $r->content, 'user' => $r->user ? ['name' => $r->user->name] : null, 'admin' => $r->admin ? ['name' => $r->admin->name] : null]),
        ]);

        $floraList = Flora::select('id', 'name')->orderBy('name')->get();
        $filters = $request->only(['search', 'flora_id']);

        return Inertia::render('Admin/Flora/Comments', compact('comments', 'floraList', 'filters'));
    }

    /**
     * Store a new comment (admin can add comments)
     */
    public function store(Request $request)
    {
        $request->validate([
            'flora_id' => 'required|exists:flora,id',
            'content' => 'required|string|max:2000',
            'parent_id' => 'nullable|exists:flora_comments,id',
        ]);

        FloraComment::create([
            'admin_id' => Auth::guard('admin')->id(),
            'flora_id' => $request->input('flora_id'),
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
    public function reply(Request $request, FloraComment $comment)
    {
        $request->validate([
            'content' => 'required|string|max:2000',
        ]);

        FloraComment::create([
            'admin_id' => Auth::guard('admin')->id(),
            'flora_id' => $comment->flora_id,
            'content' => $request->input('content'),
            'parent_id' => $comment->id,
            'is_approved' => true,
        ]);

        // Send notification to original commenter and thread participants
        $flora = $comment->flora;
        if ($flora && $comment->user_id) {
            // Get all participants in this thread
            $participantUserIds = FloraComment::where('flora_id', $comment->flora_id)
                ->where(function ($q) use ($comment) {
                    $q->where('id', $comment->id)
                        ->orWhere('parent_id', $comment->id);
                })
                ->whereNotNull('user_id')
                ->pluck('user_id')
                ->unique()
                ->toArray();

            $url = route('flora.show', $flora->slug);

            // Send in-app notifications
            $this->notificationService->notifyCommentReply(
                $comment->user_id,
                $participantUserIds,
                'Flora',
                $flora->name,
                $url
            );

            // Send push notifications
            foreach (array_unique(array_filter(array_merge([$comment->user_id], $participantUserIds))) as $userId) {
                $this->pushNotificationService->notifyCommentReply(
                    $userId,
                    'Flora',
                    $flora->name,
                    $url
                );
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Balasan berhasil ditambahkan'
        ]);
    }

    public function destroy(FloraComment $comment)
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
            'ids.*' => 'exists:flora_comments,id'
        ]);

        FloraComment::whereIn('id', $request->ids)->delete();

        return response()->json([
            'success' => true,
            'message' => count($request->ids) . ' komentar berhasil dihapus'
        ]);
    }

    public function toggleStatus(FloraComment $comment)
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

    public function togglePin(FloraComment $comment)
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
