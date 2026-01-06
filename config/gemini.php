<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Gemini API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Google Gemini AI integration.
    |
    */

    'api_key' => env('GEMINI_API_KEY'),
    
    'model' => env('GEMINI_MODEL', 'gemini-3-flash-preview'),
    
    'endpoint' => 'https://generativelanguage.googleapis.com/v1beta/models/',
    
    /*
    |--------------------------------------------------------------------------
    | Generation Settings
    |--------------------------------------------------------------------------
    */
    
    'temperature' => env('GEMINI_TEMPERATURE', 0.7),
    
    'max_output_tokens' => env('GEMINI_MAX_TOKENS', 8192),
    
    'top_p' => 0.95,
    
    'top_k' => 40,
];
