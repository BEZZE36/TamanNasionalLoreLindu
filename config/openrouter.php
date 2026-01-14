<?php

return [
    /*
    |--------------------------------------------------------------------------
    | OpenRouter API Configuration
    |--------------------------------------------------------------------------
    */

    'api_key' => env('OPENROUTER_API_KEY'),

    'endpoint' => 'https://openrouter.ai/api/v1/chat/completions',

    'model' => env('OPENROUTER_MODEL', 'meta-llama/llama-3.3-70b-instruct:free'),

    'site_url' => env('APP_URL', 'http://localhost'),

    'site_name' => env('APP_NAME', 'TNLL'),

    // Request configuration
    'timeout' => 120,

    'max_tokens' => 4096,

    'temperature' => 0.7,
];
