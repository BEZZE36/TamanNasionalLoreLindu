<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ArticleTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;

class ArticleTagController extends Controller
{
    public function index(Request $request)
    {
        $query = ArticleTag::withCount('articles');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $tags = $query->orderBy('articles_count', 'desc')->paginate(20)->withQueryString();

        return Inertia::render('Admin/Articles/Tags', [
            'tags' => $tags,
            'filters' => $request->only('search'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:article_tags,name',
        ]);

        $tag = ArticleTag::create($validated);

        return response()->json([
            'success' => true,
            'tag' => $tag,
            'message' => 'Tag berhasil dibuat',
        ]);
    }

    public function update(Request $request, ArticleTag $tag)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:article_tags,name,' . $tag->id,
        ]);

        $tag->update($validated);

        return response()->json([
            'success' => true,
            'tag' => $tag,
            'message' => 'Tag berhasil diperbarui',
        ]);
    }

    public function destroy(ArticleTag $tag)
    {
        $tag->articles()->detach();
        $tag->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tag berhasil dihapus',
        ]);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:article_tags,id',
        ]);

        ArticleTag::whereIn('id', $request->ids)->each(function ($tag) {
            $tag->articles()->detach();
            $tag->delete();
        });

        return response()->json([
            'success' => true,
            'message' => count($request->ids) . ' tag berhasil dihapus',
        ]);
    }

    /**
     * AI-powered tag suggestions using Gemini API
     */
    public function suggest(Request $request)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string',
        ]);

        $title = $request->title ?? '';
        $content = strip_tags($request->content ?? '');
        $text = $title . "\n" . substr($content, 0, 2000); // Limit content length

        if (strlen($text) < 50) {
            return response()->json([
                'success' => false,
                'message' => 'Konten terlalu pendek untuk saran tag',
                'suggestions' => [],
            ]);
        }

        try {
            $apiKey = config('services.gemini.api_key');

            if (!$apiKey) {
                // Fallback to basic extraction if no API key
                return $this->fallbackSuggestions($title, $content);
            }

            $response = Http::timeout(30)->post(
                "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$apiKey}",
                [
                    'contents' => [
                        [
                            'parts' => [
                                [
                                    'text' => "Berikan 5-8 tag yang relevan untuk artikel berikut dalam Bahasa Indonesia. Format output sebagai JSON array of strings. Hanya berikan JSON array, tanpa markdown atau teks lain.\n\nArtikel:\n{$text}"
                                ]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.3,
                        'maxOutputTokens' => 200,
                    ]
                ]
            );

            if ($response->successful()) {
                $data = $response->json();
                $generatedText = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

                // Parse JSON from response
                $generatedText = trim($generatedText);
                $generatedText = preg_replace('/^```json\s*/', '', $generatedText);
                $generatedText = preg_replace('/\s*```$/', '', $generatedText);

                $suggestions = json_decode($generatedText, true);

                if (is_array($suggestions)) {
                    // Get existing tags that match
                    $existingTags = ArticleTag::whereIn('name', $suggestions)->pluck('name')->toArray();

                    return response()->json([
                        'success' => true,
                        'suggestions' => array_values(array_unique($suggestions)),
                        'existing' => $existingTags,
                    ]);
                }
            }

            return $this->fallbackSuggestions($title, $content);

        } catch (\Exception $e) {
            return $this->fallbackSuggestions($title, $content);
        }
    }

    /**
     * Fallback tag extraction without AI
     */
    private function fallbackSuggestions(string $title, string $content): \Illuminate\Http\JsonResponse
    {
        $text = strtolower($title . ' ' . $content);

        // Common Indonesian nature/conservation keywords
        $keywords = [
            'taman nasional',
            'konservasi',
            'satwa',
            'flora',
            'fauna',
            'hutan',
            'spesies',
            'habitat',
            'ekosistem',
            'biodiversitas',
            'endemik',
            'wisata',
            'pendakian',
            'jalur',
            'kawasan',
            'lindung',
            'langka',
            'penelitian',
            'edukasi',
            'komunitas',
            'festival',
            'event',
            'anoa',
            'maleo',
            'tarsius',
            'burung',
            'primata',
            'mamalia',
        ];

        $suggestions = [];
        foreach ($keywords as $keyword) {
            if (str_contains($text, $keyword)) {
                $suggestions[] = ucwords($keyword);
            }
        }

        // Get existing tags for matching
        $existingTags = ArticleTag::whereIn('name', $suggestions)->pluck('name')->toArray();

        return response()->json([
            'success' => true,
            'suggestions' => array_slice($suggestions, 0, 8),
            'existing' => $existingTags,
            'source' => 'fallback',
        ]);
    }

    /**
     * Search tags for autocomplete
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');

        $tags = ArticleTag::where('name', 'like', "%{$query}%")
            ->orderBy('articles_count', 'desc')
            ->take(10)
            ->get(['id', 'name', 'articles_count']);

        return response()->json($tags);
    }
}
