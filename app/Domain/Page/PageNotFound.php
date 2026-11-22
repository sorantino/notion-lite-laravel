<?php

namespace App\Domain\Page;

// La page demandée n'existe pas.
final class PageNotFound extends \RuntimeException
{
    public static function withId(int $id): self
    {
        return new self("La page #{$id} n'existe pas.");
    }
}
