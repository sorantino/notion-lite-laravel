<?php

namespace App\Domain\Page;

// Une règle métier a été enfreinte (un constructeur nommé par règle).
final class PageRuleViolation extends \DomainException
{
    public static function emptyTitle(): self
    {
        return new self('Une page doit avoir un titre.');
    }

    public static function titleTooLong(int $max): self
    {
        return new self("Le titre ne peut pas dépasser {$max} caractères.");
    }

    public static function tooManyTags(int $max): self
    {
        return new self("Une page ne peut pas avoir plus de {$max} étiquettes.");
    }

    public static function tagTooLong(string $tag, int $max): self
    {
        return new self("L'étiquette « {$tag} » dépasse {$max} caractères.");
    }

    public static function tooDeep(int $maxLevels): self
    {
        return new self("Les pages ne peuvent pas être imbriquées sur plus de {$maxLevels} niveaux.");
    }

    public static function parentIsArchived(): self
    {
        return new self('Impossible de créer ou restaurer une sous-page dans une page qui est à la corbeille.');
    }

    public static function archivedPageIsReadOnly(): self
    {
        return new self('Une page à la corbeille ne peut pas être modifiée. Restaurez-la d\'abord.');
    }

    public static function mustBeArchivedBeforeDeletion(): self
    {
        return new self('Une page doit être mise à la corbeille avant d\'être supprimée définitivement.');
    }
}
