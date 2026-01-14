<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleComment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Public controller for article comment submission.
 */
class ArticleCommentController extends Controller
{
    /**
     * Store a new public comment (root or reply).
     */
    public function store(Request $request, Article $article): JsonResponse
    {
        $validated = $request->validate([
            'content' => 'required|string|max:2000',
            'parent_id' => 'nullable|exists:article_comments,id',
        ]);

        // Check if user is blocked
        $isBlocked = \App\Models\BlockedCommenter::where('user_id', Auth::id())->exists();
        if ($isBlocked) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak diizinkan untuk berkomentar.',
            ], 403);
        }

        $comment = ArticleComment::create([
            'article_id' => $article->id,
            'user_id' => Auth::id(),
            'parent_id' => $validated['parent_id'] ?? null,
            'content' => $validated['content'],
            'is_visible' => true,
            'status' => 'approved',
        ]);

        return response()->json([
            'success' => true,
            'message' => $validated['parent_id'] ? 'Balasan berhasil ditambahkan.' : 'Komentar berhasil ditambahkan.',
            'comment' => [
                'id' => $comment->id,
                'content' => $comment->content,
                'created_at' => $comment->created_at,
                'parent_id' => $comment->parent_id,
                'user' => [
                    'id' => Auth::id(),
                    'name' => Auth::user()->name,
                ],
                'admin' => null,
                'replies' => [],
            ],
        ]);
    }
}
