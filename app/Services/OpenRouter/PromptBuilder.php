<?php

namespace App\Services\OpenRouter;

class PromptBuilder
{
    /**
     * Build rewrite prompt with specified style
     */
    public function buildRewritePrompt(string $content, string $style): string
    {
        $styleInstructions = $this->getStyleInstructions($style);

        return "You are an expert content editor. Rewrite the following text according to these instructions:

STYLE: {$styleInstructions}

ORIGINAL TEXT:
{$content}

REWRITTEN TEXT:";
    }

    /**
     * Get style instructions for rewriting
     */
    protected function getStyleInstructions(string $style): string
    {
        return match ($style) {
            'formal' => 'Transform into formal, professional language suitable for official documents. Use sophisticated vocabulary, proper grammar, and authoritative tone. Remove colloquialisms and casual expressions.',

            'casual' => 'Transform into friendly, conversational language. Use warm, approachable tone with simple words. Add appropriate emojis sparingly. Make it feel like chatting with a friend.',

            'seo' => 'Optimize for search engines while maintaining readability. Use active voice, include relevant keywords naturally, break into scannable paragraphs, and make it engaging for readers.',

            default => 'Improve clarity, fix grammar, and enhance readability while preserving the original meaning.',
        };
    }

    /**
     * Build summarize prompt
     */
    public function buildSummarizePrompt(string $content, int $maxSentences = 3): string
    {
        return "Summarize the following text in exactly {$maxSentences} sentences. Capture the main points concisely. Output ONLY the summary, nothing else.

TEXT:
{$content}

SUMMARY:";
    }

    /**
     * Build headlines prompt
     */
    public function buildHeadlinesPrompt(string $content, int $count = 5): string
    {
        return "Generate {$count} compelling headlines for the following content. Make them attention-grabbing, clear, and 6-12 words each. Output ONLY numbered headlines, nothing else.

CONTENT:
{$content}

HEADLINES:";
    }

    /**
     * Build SEO suggestions prompt
     */
    public function buildSeoPrompt(string $content, ?string $targetKeyword = null): string
    {
        $keywordInstruction = $targetKeyword
            ? "Target keyword: {$targetKeyword}"
            : "Identify the best target keyword";

        return "Analyze this content for SEO optimization. {$keywordInstruction}

CONTENT:
{$content}

Provide specific, actionable SEO recommendations:";
    }

    /**
     * Build expand prompt
     */
    public function buildExpandPrompt(string $content): string
    {
        return "Expand the following text to 2-3x its length. Add relevant details, examples, and explanations while maintaining the original tone and message. Output ONLY the expanded text.

ORIGINAL:
{$content}

EXPANDED:";
    }

    /**
     * Build shorten prompt
     */
    public function buildShortenPrompt(string $content): string
    {
        return "Condense this text to 40-60% of its original length. Keep essential information and key points. Remove redundancy. Output ONLY the shortened text.

ORIGINAL:
{$content}

SHORTENED:";
    }

    /**
     * Build generate content prompt
     */
    public function buildGeneratePrompt(string $topic, string $type = 'paragraph'): string
    {
        $typeInstructions = $this->getTypeInstructions($type);

        return "You are a professional content writer for Taman Nasional Lore Lindu (TNLL), a UNESCO Biosphere Reserve in Central Sulawesi, Indonesia.

Write about: {$topic}

FORMAT: {$typeInstructions}

GUIDELINES:
- Professional yet engaging tone
- Accurate and informative
- Optimized for web reading
- Indonesian language unless specified otherwise

OUTPUT:";
    }

    /**
     * Get type instructions for content generation
     */
    protected function getTypeInstructions(string $type): string
    {
        return match ($type) {
            'paragraph' => 'Write 2-3 well-structured paragraphs',
            'list' => 'Create a bulleted list with 5-8 points',
            'headline' => 'Write a single compelling headline',
            'intro' => 'Write an engaging introduction paragraph',
            'conclusion' => 'Write a strong conclusion paragraph',
            default => 'Write in the most appropriate format',
        };
    }

    /**
     * Build grammar improvement prompt
     */
    public function buildGrammarPrompt(string $content): string
    {
        return "Fix all grammar, spelling, and punctuation errors in the following text. Improve clarity and flow. Preserve the original meaning and style. Output ONLY the corrected text.

TEXT:
{$content}

CORRECTED:";
    }

    /**
     * Build SEO tags generation prompt
     */
    public function buildSeoTagsPrompt(string $content): string
    {
        return "Generate SEO metadata for this content. Output ONLY valid JSON without code blocks.

CONTENT:
{$content}

Return this exact JSON format:
{\"meta_title\": \"title under 60 chars\", \"meta_description\": \"description under 155 chars\", \"keywords\": \"keyword1, keyword2, keyword3\"}

JSON:";
    }

    /**
     * Build translate prompt
     */
    public function buildTranslatePrompt(string $content, string $direction = 'id_to_en'): string
    {
        if ($direction === 'en_to_id') {
            return "Translate English to Indonesian accurately.

RULES:
- Translate ALL words - do not skip any
- Use proper Indonesian vocabulary
- Do not add HTML or links
- Output ONLY the translation

VOCABULARY GUIDE:
- Megalithic = Megalitik
- Traces/Footprints = Jejak-Jejak
- Rare Wildlife = Satwa Liar Langka
- Hidden Paradise = Surga Tersembunyi
- Secrets = Rahasia

ENGLISH:
{$content}

INDONESIAN:";
        }

        return "Translate Indonesian to English accurately.

RULES:
- Translate ALL words - do not skip any
- Use proper English vocabulary and grammar
- Do not add HTML or links
- Output ONLY the translation

VOCABULARY GUIDE:
- Jejak-Jejak = Traces (not 'Tracks Tracks')
- Megalitik = Megalithic (with 'h')
- Satwa Liar Langka = Rare Wildlife
- Surga Tersembunyi = Hidden Paradise
- Rahasia = Secrets/Secret

INDONESIAN:
{$content}

ENGLISH:";
    }
}
