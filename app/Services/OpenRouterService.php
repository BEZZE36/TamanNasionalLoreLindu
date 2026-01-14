<?php

namespace App\Services;

use App\Services\OpenRouter\PromptBuilder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenRouterService
{
    protected string $apiKey;
    protected string $model;
    protected string $endpoint;
    protected int $timeout;
    protected int $maxTokens;
    protected float $temperature;
    protected PromptBuilder $promptBuilder;

    public function __construct()
    {
        $this->apiKey = config('openrouter.api_key');
        $this->model = config('openrouter.model');
        $this->endpoint = config('openrouter.endpoint');
        $this->timeout = config('openrouter.timeout', 120);
        $this->maxTokens = config('openrouter.max_tokens', 4096);
        $this->temperature = config('openrouter.temperature', 0.7);
        $this->promptBuilder = new PromptBuilder();
    }

    /**
     * Generate content using AI
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
     * Translate content (preserves HTML formatting)
     */
    public function translate(string $content, string $direction = 'id_to_en'): array
    {
        return $this->callApi($this->promptBuilder->buildTranslatePrompt($content, $direction));
    }

    /**
     * Call OpenRouter API with chat completions format
     */
    protected function callApi(string $prompt): array
    {
        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'error' => 'OpenRouter API key not configured',
                'content' => null
            ];
        }

        try {
            Log::debug('OpenRouter API Request', [
                'model' => $this->model,
                'prompt_length' => strlen($prompt)
            ]);

            $response = Http::withOptions([
                'verify' => false,
            ])
                ->timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'HTTP-Referer' => config('openrouter.site_url'),
                    'X-Title' => config('openrouter.site_name'),
                    'Content-Type' => 'application/json',
                ])
                ->post($this->endpoint, [
                    'model' => $this->model,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ],
                    'max_tokens' => $this->maxTokens,
                    'temperature' => $this->temperature,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $content = $data['choices'][0]['message']['content'] ?? null;

                Log::debug('OpenRouter API Success', [
                    'model' => $this->model,
                    'response_length' => strlen($content ?? '')
                ]);

                return [
                    'success' => true,
                    'content' => $content,
                    'model_used' => $this->model,
                    'error' => null
                ];
            }

            Log::error('OpenRouter API Error', [
                'status' => $response->status(),
                'body' => substr($response->body(), 0, 500)
            ]);

            // Handle rate limit (429)
            if ($response->status() === 429) {
                $retryAfter = $response->header('Retry-After') ?? 60;
                $resetTime = now()->addSeconds((int) $retryAfter)->format('H:i:s');

                return [
                    'success' => false,
                    'error' => 'Rate limit exceeded',
                    'error_type' => 'rate_limit',
                    'retry_after' => (int) $retryAfter,
                    'reset_time' => $resetTime,
                    'content' => null
                ];
            }

            return [
                'success' => false,
                'error' => 'API request failed: ' . $response->status(),
                'content' => null
            ];

        } catch (\Exception $e) {
            Log::error('OpenRouter Service Exception', [
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
