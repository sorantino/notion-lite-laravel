<?php

namespace App\Domain\Page;

final class PageNotFound extends \RuntimeException
{
    public static function withId(int $id): self
    {
        return new self("La page #{$id} n'existe pas.");
    }
}
