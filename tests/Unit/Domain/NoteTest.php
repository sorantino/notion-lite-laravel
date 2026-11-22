<?php

namespace Tests\Unit\Domain;

use App\Domain\Note\Note;
use App\Domain\Note\NoteRuleViolation;
use App\Domain\Note\NoteTitle;
use App\Domain\Note\Tags;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class NoteTest extends TestCase
{
    public function test_a_new_note_is_a_root_note_and_not_archived(): void
    {
        $note = $this->newNote('Accueil');

        $this->assertNull($note->id());
        $this->assertNull($note->parentId());
        $this->assertSame(0, $note->depth());
        $this->assertFalse($note->isArchived());
    }

    public function test_a_sub_note_is_one_level_below_its_parent(): void
    {
        $parent = $this->existingNote(id: 1, depth: 0);

        $child = Note::create(NoteTitle::fromString('Enfant'), '', Tags::none(), $parent);

        $this->assertSame(1, $child->parentId());
        $this->assertSame(1, $child->depth());
    }

    public function test_nesting_is_limited_to_three_levels(): void
    {
        $deepest = $this->existingNote(id: 3, depth: Note::MAX_DEPTH);

        $this->expectException(NoteRuleViolation::class);

        Note::create(NoteTitle::fromString('Trop profond'), '', Tags::none(), $deepest);
    }

    public function test_a_sub_note_cannot_be_created_in_an_archived_note(): void
    {
        $archivedParent = $this->existingNote(id: 1, archived: true);

        $this->expectException(NoteRuleViolation::class);

        Note::create(NoteTitle::fromString('Enfant'), '', Tags::none(), $archivedParent);
    }

    public function test_a_note_can_be_edited(): void
    {
        $note = $this->newNote('Brouillon');

        $note->edit(NoteTitle::fromString('Version finale'), '# Contenu', Tags::fromArray(['doc']));

        $this->assertSame('Version finale', $note->title()->value);
        $this->assertSame('# Contenu', $note->content());
        $this->assertSame(['doc'], $note->tags()->values);
    }

    public function test_an_archived_note_is_read_only(): void
    {
        $note = $this->newNote('Ancienne note');
        $note->archive();

        $this->expectException(NoteRuleViolation::class);

        $note->edit(NoteTitle::fromString('Nouveau titre'), '', Tags::none());
    }

    public function test_a_note_cannot_be_restored_while_its_parent_is_archived(): void
    {
        $parent = $this->existingNote(id: 1, archived: true);
        $child = $this->existingNote(id: 2, depth: 1, parentId: 1, archived: true);

        $this->expectException(NoteRuleViolation::class);

        $child->restore($parent);
    }

    public function test_a_note_can_be_restored_when_its_parent_is_active(): void
    {
        $parent = $this->existingNote(id: 1);
        $child = $this->existingNote(id: 2, depth: 1, parentId: 1, archived: true);

        $child->restore($parent);

        $this->assertFalse($child->isArchived());
    }

    public function test_only_an_archived_note_can_be_deleted(): void
    {
        $note = $this->newNote('Active');

        $this->expectException(NoteRuleViolation::class);

        $note->ensureCanBeDeleted();
    }

    public function test_an_archived_note_can_be_deleted(): void
    {
        $note = $this->newNote('À supprimer');
        $note->archive();

        $note->ensureCanBeDeleted();

        $this->assertTrue($note->isArchived());
    }

    public function test_excerpt_is_plain_text_and_truncated(): void
    {
        $note = Note::create(NoteTitle::fromString('Long'), "<b>Bonjour</b>\n\n".str_repeat('x', 200), Tags::none());

        $excerpt = $note->excerpt(20);

        $this->assertStringStartsWith('Bonjour x', $excerpt);
        $this->assertSame(21, mb_strlen($excerpt));
    }

    private function newNote(string $title): Note
    {
        return Note::create(NoteTitle::fromString($title), '', Tags::none());
    }

    private function existingNote(int $id, int $depth = 0, ?int $parentId = null, bool $archived = false): Note
    {
        return Note::reconstitute(
            id: $id,
            title: NoteTitle::fromString("Note {$id}"),
            content: '',
            tags: Tags::none(),
            parentId: $parentId,
            depth: $depth,
            archived: $archived,
            updatedAt: new DateTimeImmutable,
        );
    }
}
