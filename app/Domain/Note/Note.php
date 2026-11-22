<?php

namespace App\Domain\Note;

use DateTimeImmutable;

final class Note
{
    public const MAX_DEPTH = 2;

    private function __construct(
        private ?int $id,
        private NoteTitle $title,
        private string $content,
        private Tags $tags,
        private ?int $parentId,
        private int $depth,
        private bool $archived,
        private DateTimeImmutable $updatedAt,
        private ?string $summary = null,
    ) {}

    public static function create(NoteTitle $title, string $content, Tags $tags, ?Note $parent = null): self
    {
        if ($parent !== null) {
            if ($parent->archived) {
                throw NoteRuleViolation::parentIsArchived();
            }
            if ($parent->depth >= self::MAX_DEPTH) {
                throw NoteRuleViolation::tooDeep(self::MAX_DEPTH + 1);
            }
        }

        return new self(
            id: null,
            title: $title,
            content: $content,
            tags: $tags,
            parentId: $parent?->id,
            depth: $parent === null ? 0 : $parent->depth + 1,
            archived: false,
            updatedAt: new DateTimeImmutable,
        );
    }

    public static function reconstitute(
        int $id,
        NoteTitle $title,
        string $content,
        Tags $tags,
        ?int $parentId,
        int $depth,
        bool $archived,
        DateTimeImmutable $updatedAt,
        ?string $summary = null,
    ): self {
        return new self($id, $title, $content, $tags, $parentId, $depth, $archived, $updatedAt, $summary);
    }

    public function edit(NoteTitle $title, string $content, Tags $tags): void
    {
        $this->ensureNotArchived();

        $this->title = $title;
        $this->content = $content;
        $this->tags = $tags;
        $this->touch();
    }

    public function archive(): void
    {
        $this->archived = true;
        $this->touch();
    }

    public function restore(?Note $parent): void
    {
        if ($parent?->isArchived()) {
            throw NoteRuleViolation::parentIsArchived();
        }

        $this->archived = false;
        $this->touch();
    }

    public function ensureCanBeDeleted(): void
    {
        if (! $this->archived) {
            throw NoteRuleViolation::mustBeArchivedBeforeDeletion();
        }
    }

    public function canHaveChildren(): bool
    {
        return ! $this->archived && $this->depth < self::MAX_DEPTH;
    }

    public function excerpt(int $length = 140): string
    {
        $text = trim(preg_replace('/\s+/u', ' ', strip_tags($this->content)));

        return mb_strlen($text) > $length ? mb_substr($text, 0, $length).'…' : $text;
    }

    public function withId(int $id): self
    {
        $copy = clone $this;
        $copy->id = $id;

        return $copy;
    }

    private function ensureNotArchived(): void
    {
        if ($this->archived) {
            throw NoteRuleViolation::archivedNoteIsReadOnly();
        }
    }

    private function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable;
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function title(): NoteTitle
    {
        return $this->title;
    }

    public function content(): string
    {
        return $this->content;
    }

    public function tags(): Tags
    {
        return $this->tags;
    }

    public function parentId(): ?int
    {
        return $this->parentId;
    }

    public function depth(): int
    {
        return $this->depth;
    }

    public function isArchived(): bool
    {
        return $this->archived;
    }

    public function summary(): ?string
    {
        return $this->summary;
    }

    public function updatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
