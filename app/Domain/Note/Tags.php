<?php

namespace App\Domain\Note;

final class Tags
{
    public const MAX_TAGS = 5;

    public const MAX_TAG_LENGTH = 30;

    /** @param list<string> $values */
    private function __construct(public readonly array $values) {}

    /** @param iterable<string> $raw */
    public static function fromArray(iterable $raw): self
    {
        $normalized = [];

        foreach ($raw as $tag) {
            $tag = mb_strtolower(trim((string) $tag));
            $tag = preg_replace('/\s+/u', '-', $tag);

            if ($tag === '') {
                continue;
            }

            if (mb_strlen($tag) > self::MAX_TAG_LENGTH) {
                throw NoteRuleViolation::tagTooLong($tag, self::MAX_TAG_LENGTH);
            }

            $normalized[$tag] = $tag;
        }

        if (count($normalized) > self::MAX_TAGS) {
            throw NoteRuleViolation::tooManyTags(self::MAX_TAGS);
        }

        return new self(array_values($normalized));
    }

    public static function fromCommaSeparated(?string $raw): self
    {
        return self::fromArray(explode(',', (string) $raw));
    }

    public static function none(): self
    {
        return new self([]);
    }

    public function toCommaSeparated(): string
    {
        return implode(', ', $this->values);
    }
}
