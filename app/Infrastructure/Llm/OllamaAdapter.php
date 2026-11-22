<?php

namespace App\Infrastructure\Llm;

use App\Domain\Ports\LlmPort;
use Illuminate\Support\Facades\Http;

final class OllamaAdapter implements LlmPort
{
    public function __construct(
        private readonly string $host,
        private readonly string $model,
    ) {}

    public function complete(array $messages): string
    {
        return Http::timeout(120)
            ->post(rtrim($this->host, '/').'/api/chat', [
                'model' => $this->model,
                'messages' => $messages,
                'stream' => false,
            ])
            ->throw()
            ->json('message.content', '');
    }
}
