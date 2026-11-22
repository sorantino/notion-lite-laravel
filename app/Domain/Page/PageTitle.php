<?php

namespace App\Domain\Page;

final class PageTitle
{
    public const MAX_LENGTH = 120;

    private function __construct(public readonly string $value) {}

    public static function fromString(string $raw): self
    {
        $value = trim(preg_replace('/\s+/u', ' ', $raw));

        if ($value === '') {
            throw PageRuleViolation::emptyTitle();
        }

        if (mb_strlen($value) > self::MAX_LENGTH) {
            throw PageRuleViolation::titleTooLong(self::MAX_LENGTH);
        }

        return new self($value);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
