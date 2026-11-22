<?php

namespace App\Domain\Note;

final class NoteRuleViolation extends \DomainException
{
    public static function emptyTitle(): self
    {
        return new self('Une note doit avoir un titre.');
    }

    public static function titleTooLong(int $max): self
    {
        return new self("Le titre ne peut pas dépasser {$max} caractères.");
    }

    public static function tooManyTags(int $max): self
    {
        return new self("Une note ne peut pas avoir plus de {$max} étiquettes.");
    }

    public static function tagTooLong(string $tag, int $max): self
    {
        return new self("L'étiquette « {$tag} » dépasse {$max} caractères.");
    }

    public static function tooDeep(int $maxLevels): self
    {
        return new self("Les notes ne peuvent pas être imbriquées sur plus de {$maxLevels} niveaux.");
    }

    public static function parentIsArchived(): self
    {
        return new self('Impossible de créer ou restaurer une sous-note dans une note qui est à la corbeille.');
    }

    public static function archivedNoteIsReadOnly(): self
    {
        return new self('Une note à la corbeille ne peut pas être modifiée. Restaurez-la d\'abord.');
    }

    public static function mustBeArchivedBeforeDeletion(): self
    {
        return new self('Une note doit être mise à la corbeille avant d\'être supprimée définitivement.');
    }
}
