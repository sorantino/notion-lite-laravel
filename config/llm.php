<?php

return [

    // fake, http ou ollama
    'provider' => env('LLM_PROVIDER', 'fake'),

    'http' => [
        'base_url' => env('LLM_BASE_URL', 'https://api.openai.com/v1'),
        'api_key' => env('LLM_API_KEY'),
        'model' => env('LLM_MODEL', 'gpt-4o-mini'),
    ],

    'ollama' => [
        'host' => env('OLLAMA_HOST', 'http://localhost:11434'),
        'model' => env('OLLAMA_MODEL', 'llama3.2'),
    ],

];
