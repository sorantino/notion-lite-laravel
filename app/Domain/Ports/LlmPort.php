<?php

namespace App\Domain\Ports;

interface LlmPort
{
    /** @param list<array{role: string, content: string}> $messages */
    public function complete(array $messages): string;
}
