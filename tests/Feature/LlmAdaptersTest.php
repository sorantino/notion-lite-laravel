<?php

namespace Tests\Feature;

use App\Domain\Ports\LlmPort;
use App\Infrastructure\Llm\FakeLlmAdapter;
use App\Infrastructure\Llm\HttpLlmAdapter;
use App\Infrastructure\Llm\OllamaAdapter;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LlmAdaptersTest extends TestCase
{
    private const MESSAGES = [['role' => 'user', 'content' => 'Bonjour']];

    public function test_the_provider_is_chosen_from_the_config(): void
    {
        $this->assertInstanceOf(FakeLlmAdapter::class, $this->app->make(LlmPort::class));

        config(['llm.provider' => 'http']);
        $this->assertInstanceOf(HttpLlmAdapter::class, $this->app->make(LlmPort::class));

        config(['llm.provider' => 'ollama']);
        $this->assertInstanceOf(OllamaAdapter::class, $this->app->make(LlmPort::class));
    }

    public function test_the_http_adapter_calls_a_chat_completions_api(): void
    {
        Http::fake(['api.example.com/*' => Http::response(['choices' => [['message' => ['content' => 'Salut']]]])]);

        $answer = (new HttpLlmAdapter('https://api.example.com/v1', 'secret', 'mon-modele'))->complete(self::MESSAGES);

        $this->assertSame('Salut', $answer);
        Http::assertSent(fn (Request $request) => $request->url() === 'https://api.example.com/v1/chat/completions'
            && $request->hasHeader('Authorization', 'Bearer secret')
            && $request['model'] === 'mon-modele'
            && $request['messages'] === self::MESSAGES);
    }

    public function test_the_ollama_adapter_calls_the_chat_endpoint(): void
    {
        Http::fake(['localhost:11434/*' => Http::response(['message' => ['content' => 'Salut']])]);

        $answer = (new OllamaAdapter('http://localhost:11434', 'llama3.2'))->complete(self::MESSAGES);

        $this->assertSame('Salut', $answer);
        Http::assertSent(fn (Request $request) => $request->url() === 'http://localhost:11434/api/chat'
            && $request['stream'] === false);
    }
}
