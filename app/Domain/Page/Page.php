<?php

namespace App\Domain\Page;

use DateTimeImmutable;

// Entité Page : porte les règles métier, sans dépendance au framework.
final class Page
{
    /** Profondeur maximale (0, 1, 2 → 3 niveaux). */
    public const MAX_DEPTH = 2;

    private function __construct(
        private ?int $id,
        private PageTitle $title,
        private string $content,
        private Tags $tags,
        private ?int $parentId,
        private int $depth,
        private bool $archived,
        private DateTimeImmutable $updatedAt,
    ) {}

    /** Création d'une nouvelle page, éventuellement sous une page parente. */
    public static function create(PageTitle $title, string $content, Tags $tags, ?Page $parent = null): self
    {
        if ($parent !== null) {
            if ($parent->archived) {
                throw PageRuleViolation::parentIsArchived();
            }
            if ($parent->depth >= self::MAX_DEPTH) {
                throw PageRuleViolation::tooDeep(self::MAX_DEPTH + 1);
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

    // Recharge une page existante, sans rejouer les règles de création.
    public static function reconstitute(
        int $id,
        PageTitle $title,
        string $content,
        Tags $tags,
        ?int $parentId,
        int $depth,
        bool $archived,
        DateTimeImmutable $updatedAt,
    ): self {
        return new self($id, $title, $content, $tags, $parentId, $depth, $archived, $updatedAt);
    }

    public function edit(PageTitle $title, string $content, Tags $tags): void
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

    /** @param  Page|null  $parent  la page parente, si elle existe */
    public function restore(?Page $parent): void
    {
        if ($parent?->isArchived()) {
            throw PageRuleViolation::parentIsArchived();
        }

        $this->archived = false;
        $this->touch();
    }

    public function ensureCanBeDeleted(): void
    {
        if (! $this->archived) {
            throw PageRuleViolation::mustBeArchivedBeforeDeletion();
        }
    }

    public function canHaveChildren(): bool
    {
        return ! $this->archived && $this->depth < self::MAX_DEPTH;
    }

    /** Un aperçu en texte brut, utile pour les listes et la recherche. */
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
            throw PageRuleViolation::archivedPageIsReadOnly();
        }
    }

    private function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable;
    }

    // --- Accesseurs (lecture seule depuis l'extérieur) ---

    public function id(): ?int
    {
        return $this->id;
    }

    public function title(): PageTitle
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

    public function updatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
