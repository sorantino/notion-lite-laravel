<?php

namespace Tests\Feature;

use App\Application\NoteService;
use App\Domain\Note\NoteNotFound;
use App\Domain\Note\NoteRuleViolation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoteServiceTest extends TestCase
{
    use RefreshDatabase;

    private NoteService $notes;

    protected function setUp(): void
    {
        parent::setUp();

        $this->notes = $this->app->make(NoteService::class);
    }

    public function test_it_creates_a_note_with_normalized_tags(): void
    {
        $note = $this->notes->create('  Ma note ', 'Texte', 'PHP, php, Laravel');

        $this->assertSame('Ma note', $note->title()->value);
        $this->assertSame(['php', 'laravel'], $this->notes->get($note->id())->tags()->values);
    }

    public function test_it_builds_the_note_tree(): void
    {
        $root = $this->notes->create('Racine');
        $this->notes->create('Enfant', parentId: $root->id());

        $tree = $this->notes->tree();

        $this->assertCount(1, $tree);
        $this->assertSame('Enfant', $tree[0]->children[0]->note->title()->value);
    }

    public function test_it_refuses_a_fourth_level(): void
    {
        $level1 = $this->notes->create('Niveau 1');
        $level2 = $this->notes->create('Niveau 2', parentId: $level1->id());
        $level3 = $this->notes->create('Niveau 3', parentId: $level2->id());

        $this->expectException(NoteRuleViolation::class);

        $this->notes->create('Niveau 4', parentId: $level3->id());
    }

    public function test_archiving_a_note_archives_its_sub_notes(): void
    {
        $root = $this->notes->create('Racine');
        $child = $this->notes->create('Enfant', parentId: $root->id());

        $this->notes->archive($root->id());

        $this->assertTrue($this->notes->get($child->id())->isArchived());
        $this->assertSame([], $this->notes->tree());
        $this->assertCount(2, $this->notes->trash());
    }

    public function test_a_sub_note_cannot_be_restored_before_its_parent(): void
    {
        $root = $this->notes->create('Racine');
        $child = $this->notes->create('Enfant', parentId: $root->id());
        $this->notes->archive($root->id());

        $this->expectException(NoteRuleViolation::class);

        $this->notes->restore($child->id());
    }

    public function test_restoring_a_note_restores_its_sub_notes(): void
    {
        $root = $this->notes->create('Racine');
        $child = $this->notes->create('Enfant', parentId: $root->id());
        $this->notes->archive($root->id());

        $this->notes->restore($root->id());

        $this->assertFalse($this->notes->get($child->id())->isArchived());
    }

    public function test_an_active_note_cannot_be_deleted(): void
    {
        $note = $this->notes->create('Active');

        $this->expectException(NoteRuleViolation::class);

        $this->notes->delete($note->id());
    }

    public function test_an_archived_note_can_be_deleted(): void
    {
        $note = $this->notes->create('À supprimer');
        $this->notes->archive($note->id());

        $this->notes->delete($note->id());

        $this->expectException(NoteNotFound::class);
        $this->notes->get($note->id());
    }

    public function test_search_looks_in_title_and_content(): void
    {
        $this->notes->create('Réunion', 'Budget validé');
        $this->notes->create('Idées', 'Parler du budget');
        $this->notes->create('Autre', 'Rien à voir');

        $this->assertCount(2, $this->notes->search('budget'));
        $this->assertSame([], $this->notes->search('   '));
    }
}
