<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AiController extends Controller
{
    protected GeminiService $gemini;

    public function __construct(GeminiService $gemini)
    {
        $this->gemini = $gemini;
    }

    /**
     * Generate content based on prompt
     */
    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'prompt' => 'required|string|max:2000',
            'context' => 'nullable|string|max:5000',
            'type' => 'nullable|string|in:paragraph,article,list,description,news'
        ]);

        $type = $request->input('type', 'paragraph');

        if ($request->filled('prompt') && !$request->filled('context')) {
            $result = $this->gemini->generateContent($request->input('prompt'), $type);
        } else {
            $result = $this->gemini->generate($request->input('prompt'), $request->input('context'));
        }

        return response()->json($result);
    }

    /**
     * Rewrite content with style
     */
    public function rewrite(Request $request): JsonResponse
    {
        $request->validate([
            'content' => 'required|string|max:10000',
            'style' => 'nullable|string|in:formal,casual,seo'
        ]);

        $result = $this->gemini->rewrite(
            $request->input('content'),
            $request->input('style', 'formal')
        );

        return response()->json($result);
    }

    /**
     * Summarize content
     */
    public function summarize(Request $request): JsonResponse
    {
        $request->validate([
            'content' => 'required|string|max:20000',
            'sentences' => 'nullable|integer|min:1|max:10'
        ]);

        $result = $this->gemini->summarize(
            $request->input('content'),
            $request->input('sentences', 3)
        );

        return response()->json($result);
    }

    /**
     * Get SEO suggestions
     */
    public function seoSuggest(Request $request): JsonResponse
    {
        $request->validate([
            'content' => 'required|string|max:20000',
            'keyword' => 'nullable|string|max:100'
        ]);

        $result = $this->gemini->seoSuggestions(
            $request->input('content'),
            $request->input('keyword')
        );

        return response()->json($result);
    }

    /**
     * Generate headline alternatives
     */
    public function headlines(Request $request): JsonResponse
    {
        $request->validate([
            'content' => 'required|string|max:5000',
            'count' => 'nullable|integer|min:1|max:10'
        ]);

        $result = $this->gemini->generateHeadlines(
            $request->input('content'),
            $request->input('count', 5)
        );

        return response()->json($result);
    }

    /**
     * Expand content
     */
    public function expand(Request $request): JsonResponse
    {
        $request->validate([
            'content' => 'required|string|max:5000'
        ]);

        $result = $this->gemini->expand($request->input('content'));

        return response()->json($result);
    }

    /**
     * Shorten content
     */
    public function shorten(Request $request): JsonResponse
    {
        $request->validate([
            'content' => 'required|string|max:10000'
        ]);

        $result = $this->gemini->shorten($request->input('content'));

        return response()->json($result);
    }

    /**
     * Improve grammar
     */
    public function improveGrammar(Request $request): JsonResponse
    {
        $request->validate([
            'content' => 'required|string|max:10000'
        ]);

        $result = $this->gemini->improveGrammar($request->input('content'));

        return response()->json($result);
    }



    /**
     * Generate SEO Tags (JSON)
     */
    public function generateSeoTags(Request $request): JsonResponse
    {
        $request->validate([
            'content' => 'required|string|max:10000'
        ]);

        $result = $this->gemini->generateSeoTags($request->input('content'));

        // Clean markdown code blocks if present
        if (!empty($result['content'])) {
            $jsonString = $result['content'];
            $jsonString = preg_replace('/^```json\s*|\s*```$/', '', $jsonString);
            $result['content'] = json_decode($jsonString, true);
        }

        return response()->json($result);
    }
}
