<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\DestinationComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DestinationInteractionController extends Controller
{
    /**
     * Store a new comment on a destination.
     */
    public function storeComment(Request $request, Destination $destination)
    {
        $request->validate([
            'content' => 'required|string|min:3|max:1000',
            'parent_id' => 'nullable|exists:destination_comments,id',
        ], [
            'content.required' => 'Komentar tidak boleh kosong.',
            'content.min' => 'Komentar minimal 3 karakter.',
            'content.max' => 'Komentar maksimal 1000 karakter.',
        ]);

        $comment = $destination->comments()->create([
            'user_id' => Auth::id(),
            'content' => $request->input('content'),
            'parent_id' => $request->input('parent_id'),
            'is_visible' => true,
        ]);

        $comment->load('user');

        return response()->json([
            'success' => true,
            'message' => $request->parent_id ? 'Balasan berhasil ditambahkan.' : 'Komentar berhasil ditambahkan.',
            'comment' => [
                'id' => $comment->id,
                'content' => $comment->content,
                'parent_id' => $comment->parent_id,
                'created_at' => $comment->created_at->diffForHumans(),
                'user' => [
                    'name' => $comment->user->name,
                    'avatar_url' => $comment->user->avatar_url ?? null,
                ],
            ],
        ]);
    }

    /**
     * Get comments for a destination (paginated).
     */
    public function getComments(Destination $destination, Request $request)
    {
        $comments = $destination->comments()
            ->visible()
            ->root()
            ->with([
                'user',
                'replies' => function ($q) {
                    $q->visible()->with('user');
                }
            ])
            ->orderByDesc('is_pinned')
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'comments' => $comments->map(function ($comment) {
                return $this->formatComment($comment);
            }),
            'has_more' => $comments->hasMorePages(),
            'total' => $comments->total(),
        ]);
    }

    /**
     * Format comment for JSON response
     */
    private function formatComment($comment)
    {
        $data = [
            'id' => $comment->id,
            'content' => $comment->content,
            'parent_id' => $comment->parent_id,
            'is_pinned' => $comment->is_pinned,
            'created_at' => $comment->created_at->diffForHumans(),
            'is_owner' => Auth::check() && Auth::id() === $comment->user_id,
            'user' => [
                'name' => $comment->user->name ?? 'Anonymous',
                'avatar_url' => $comment->user->avatar_url ?? null,
            ],
            'replies' => [],
        ];

        if ($comment->relationLoaded('replies') && $comment->replies->count() > 0) {
            $data['replies'] = $comment->replies->map(function ($reply) {
                return $this->formatComment($reply);
            });
        }

        return $data;
    }
}
