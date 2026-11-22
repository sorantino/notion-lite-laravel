<?php

namespace App\Infrastructure\Llm;

use App\Domain\Ports\LlmPort;

final class FakeLlmAdapter implements LlmPort
{
    public const RESPONSE = 'Résumé factice : choisissez un fournisseur avec LLM_PROVIDER pour obtenir un vrai résumé.';

    public function complete(array $messages): string
    {
        return self::RESPONSE;
    }
}
