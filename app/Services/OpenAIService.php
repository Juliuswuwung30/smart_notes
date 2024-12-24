<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class OpenAIService
{
    protected string $apiKey;
    protected string $baseUrl = 'https://api.openai.com/v1';

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key');
    }

    public function generateTodos(string $note): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl . '/chat/completions', [
            'model' => 'gpt-4o-mini',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'You are a helpful assistant that generates todo-list items from notes. Extract actionable tasks from the given note. If there are no clear actionable items, return an empty array.'
                        ]
                    ]
                ],
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => "Here is my note: {$note}"
                        ]
                    ]
                ]
            ],
            'response_format' => [
                'type' => 'json_schema',
                'json_schema' => [
                    'name' => 'todos_schema',
                    'strict' => true,
                    'schema' => [
                        'type' => 'object',
                        'properties' => [
                            'todos' => [
                                'type' => 'array',
                                'description' => 'A list of todo items.',
                                'items' => [
                                    'type' => 'string',
                                    'description' => 'A single todo item.'
                                ]
                            ]
                        ],
                        'required' => ['todos'],
                        'additionalProperties' => false
                    ]
                ]
            ],
            'temperature' => 1,
            'max_completion_tokens' => 2048,
            'top_p' => 1,
            'frequency_penalty' => 0,
            'presence_penalty' => 0
        ]);

        if (!$response->successful()) {
            throw new \Exception('Failed to generate todos from OpenAI');
        }

        $content = json_decode($response->json('choices.0.message.content'), true);
        return $content['todos'] ?? [];
    }
}