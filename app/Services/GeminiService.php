<?php

namespace App\Services;

use App\Services\Gemini\GeminiPromptBuilder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected string $apiKey;
    protected string $model;
    protected string $endpoint;
    protected float $temperature;
    protected int $maxTokens;
    protected GeminiPromptBuilder $promptBuilder;

    public function __construct()
    {
        $this->apiKey = config('gemini.api_key');
        $this->model = config('gemini.model');
        $this->endpoint = config('gemini.endpoint');
        $this->temperature = config('gemini.temperature');
        $this->maxTokens = config('gemini.max_output_tokens');
        $this->promptBuilder = new GeminiPromptBuilder();
    }

    /**
     * Generate content using Gemini AI
     */
    public function generate(string $prompt, ?string $context = null): array
    {
        $fullPrompt = $context
            ? "Context:\n{$context}\n\nTask:\n{$prompt}"
            : $prompt;

        return $this->callApi($fullPrompt);
    }

    /**
     * Rewrite content with specified style
     */
    public function rewrite(string $content, string $style = 'formal'): array
    {
        return $this->callApi($this->promptBuilder->buildRewritePrompt($content, $style));
    }

    /**
     * Summarize content
     */
    public function summarize(string $content, int $maxSentences = 3): array
    {
        return $this->callApi($this->promptBuilder->buildSummarizePrompt($content, $maxSentences));
    }

    /**
     * Generate headline alternatives
     */
    public function generateHeadlines(string $content, int $count = 5): array
    {
        return $this->callApi($this->promptBuilder->buildHeadlinesPrompt($content, $count));
    }

    /**
     * Get SEO suggestions
     */
    public function seoSuggestions(string $content, ?string $targetKeyword = null): array
    {
        return $this->callApi($this->promptBuilder->buildSeoPrompt($content, $targetKeyword));
    }

    /**
     * Expand/elaborate content
     */
    public function expand(string $content): array
    {
        return $this->callApi($this->promptBuilder->buildExpandPrompt($content));
    }

    /**
     * Shorten content while keeping key points
     */
    public function shorten(string $content): array
    {
        return $this->callApi($this->promptBuilder->buildShortenPrompt($content));
    }

    /**
     * Generate content based on topic
     */
    public function generateContent(string $topic, string $type = 'paragraph'): array
    {
        return $this->callApi($this->promptBuilder->buildGeneratePrompt($topic, $type));
    }

    /**
     * Grammar and clarity improvement
     */
    public function improveGrammar(string $content): array
    {
        return $this->callApi($this->promptBuilder->buildGrammarPrompt($content));
    }

    /**
     * Generate SEO tags (Meta Title, Description, Keywords)
     */
    public function generateSeoTags(string $content): array
    {
        return $this->callApi($this->promptBuilder->buildSeoTagsPrompt($content));
    }

    /**
     * Call Gemini API
     */
    protected function callApi(string $prompt): array
    {
        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'error' => 'Gemini API key not configured',
                'content' => null
            ];
        }

        try {
            $url = "{$this->endpoint}{$this->model}:generateContent?key={$this->apiKey}";

            $response = Http::withOptions([
                'verify' => false,
            ])->timeout(120)->post($url, [
                        'contents' => [
                            [
                                'parts' => [
                                    ['text' => $prompt]
                                ]
                            ]
                        ],
                        'generationConfig' => [
                            'temperature' => $this->temperature,
                            'maxOutputTokens' => $this->maxTokens,
                            'topP' => config('gemini.top_p'),
                            'topK' => config('gemini.top_k'),
                        ]
                    ]);

            if ($response->successful()) {
                $data = $response->json();
                $content = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

                return [
                    'success' => true,
                    'content' => $content,
                    'error' => null
                ];
            }

            Log::error('Gemini API Error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return [
                'success' => false,
                'error' => 'API request failed: ' . $response->status(),
                'content' => null
            ];

        } catch (\Exception $e) {
            Log::error('Gemini Service Exception', [
                'message' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'content' => null
            ];
        }
    }
}
