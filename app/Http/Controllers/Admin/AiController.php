<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\OpenRouterService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AiController extends Controller
{
    protected OpenRouterService $ai;

    public function __construct(OpenRouterService $ai)
    {
        $this->ai = $ai;
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
            $result = $this->ai->generateContent($request->input('prompt'), $type);
        } else {
            $result = $this->ai->generate($request->input('prompt'), $request->input('context'));
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

        $result = $this->ai->rewrite(
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

        $result = $this->ai->summarize(
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

        $result = $this->ai->seoSuggestions(
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

        $result = $this->ai->generateHeadlines(
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

        $result = $this->ai->expand($request->input('content'));

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

        $result = $this->ai->shorten($request->input('content'));

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

        $result = $this->ai->improveGrammar($request->input('content'));

        return response()->json($result);
    }

    /**
     * Translate content (ID↔EN) preserving HTML formatting
     */
    public function translate(Request $request): JsonResponse
    {
        $request->validate([
            'content' => 'required|string|max:20000',
            'direction' => 'nullable|in:id_to_en,en_to_id'
        ]);

        $result = $this->ai->translate(
            $request->input('content'),
            $request->input('direction', 'id_to_en')
        );

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

        $result = $this->ai->generateSeoTags($request->input('content'));

        // Clean markdown code blocks if present and parse JSON
        if (!empty($result['content'])) {
            $jsonString = $result['content'];

            // Remove markdown code blocks (```json, ```, etc)
            $jsonString = preg_replace('/^```(?:json)?\s*/i', '', $jsonString);
            $jsonString = preg_replace('/\s*```$/', '', $jsonString);
            $jsonString = trim($jsonString);

            // Try to parse JSON
            $decoded = json_decode($jsonString, true);

            if ($decoded !== null) {
                $result['content'] = $decoded;
            } else {
                // If JSON parsing failed, try to extract data manually
                $result['content'] = [
                    'meta_title' => '',
                    'meta_description' => '',
                    'keywords' => ''
                ];

                // Try regex extraction as fallback
                if (preg_match('/"meta_title"\s*:\s*"([^"]+)"/', $jsonString, $m)) {
                    $result['content']['meta_title'] = $m[1];
                }
                if (preg_match('/"meta_description"\s*:\s*"([^"]+)"/', $jsonString, $m)) {
                    $result['content']['meta_description'] = $m[1];
                }
                if (preg_match('/"keywords"\s*:\s*"([^"]+)"/', $jsonString, $m)) {
                    $result['content']['keywords'] = $m[1];
                }
            }
        }

        return response()->json($result);
    }
}
