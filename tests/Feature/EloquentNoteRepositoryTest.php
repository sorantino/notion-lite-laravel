<?php

namespace Tests\Feature;

use App\Domain\Note\Note;
use App\Domain\Note\NoteTitle;
use App\Domain\Note\Tags;
use App\Infrastructure\Persistence\EloquentNoteRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EloquentNoteRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EloquentNoteRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new EloquentNoteRepository;
    }

    public function test_it_saves_and_reloads_a_note(): void
    {
        $saved = $this->repository->save($this->note('Notes', 'Contenu', ['php', 'laravel']));

        $reloaded = $this->repository->find($saved->id());

        $this->assertSame('Notes', $reloaded->title()->value);
        $this->assertSame('Contenu', $reloaded->content());
        $this->assertSame(['php', 'laravel'], $reloaded->tags()->values);
    }

    public function test_it_finds_all_descendants(): void
    {
        $root = $this->repository->save($this->note('Racine'));
        $child = $this->repository->save($this->note('Enfant', parent: $root));
        $this->repository->save($this->note('Petit-enfant', parent: $child));
        $this->repository->save($this->note('Autre racine'));

        $titles = array_map(fn (Note $note) => $note->title()->value, $this->repository->descendantsOf($root));

        $this->assertSame(['Enfant', 'Petit-enfant'], $titles);
    }

    public function test_search_ignores_archived_notes(): void
    {
        $this->repository->save($this->note('Réunion lundi'));
        $archived = $this->repository->save($this->note('Réunion mardi'));
        $archived->archive();
        $this->repository->save($archived);

        $this->assertCount(1, $this->repository->search('Réunion'));
        $this->assertCount(1, $this->repository->archived());
    }

    public function test_deleting_a_note_deletes_its_sub_notes(): void
    {
        $root = $this->repository->save($this->note('Racine'));
        $this->repository->save($this->note('Enfant', parent: $root));

        $this->repository->delete($root);

        $this->assertSame([], $this->repository->active());
    }

    /** @param list<string> $tags */
    private function note(string $title, string $content = '', array $tags = [], ?Note $parent = null): Note
    {
        return Note::create(NoteTitle::fromString($title), $content, Tags::fromArray($tags), $parent);
    }
}
