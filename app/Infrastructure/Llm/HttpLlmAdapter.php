<?php

namespace App\Infrastructure\Llm;

use App\Domain\Ports\LlmPort;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

final class HttpLlmAdapter implements LlmPort
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly ?string $apiKey,
        private readonly string $model,
    ) {}

    public function complete(array $messages): string
    {
        return Http::timeout(60)
            ->when($this->apiKey, fn (PendingRequest $request) => $request->withToken($this->apiKey))
            ->post(rtrim($this->baseUrl, '/').'/chat/completions', [
                'model' => $this->model,
                'messages' => $messages,
            ])
            ->throw()
            ->json('choices.0.message.content', '');
    }
}
