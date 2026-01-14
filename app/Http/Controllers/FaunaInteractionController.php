<?php

namespace App\Http\Controllers;

use App\Models\Fauna;
use App\Models\FaunaComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FaunaInteractionController extends Controller
{
    /**
     * Store a new comment on a fauna item.
     */
    public function storeComment(Request $request, Fauna $fauna)
    {
        $request->validate([
            'content' => 'required|string|min:3|max:1000',
            'parent_id' => 'nullable|exists:fauna_comments,id',
        ], [
            'content.required' => 'Komentar tidak boleh kosong.',
            'content.min' => 'Komentar minimal 3 karakter.',
            'content.max' => 'Komentar maksimal 1000 karakter.',
        ]);

        $comment = $fauna->comments()->create([
            'user_id' => Auth::id(),
            'content' => $request->input('content'),
            'parent_id' => $request->input('parent_id'),
            'is_approved' => true,
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
                    'avatar_url' => $comment->user->avatar_url,
                ],
            ],
        ]);
    }

    /**
     * Delete a comment.
     */
    public function deleteComment(FaunaComment $comment)
    {
        // Users cannot delete comments as per new policy
        return response()->json([
            'success' => false,
            'message' => 'Fitur hapus komentar telah dinonaktifkan.',
        ], 403);
    }
}
