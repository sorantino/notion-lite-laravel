<?php

namespace App\Domain\Note;

final class NoteNotFound extends \RuntimeException
{
    public static function withId(int $id): self
    {
        return new self("La note #{$id} n'existe pas.");
    }
}
