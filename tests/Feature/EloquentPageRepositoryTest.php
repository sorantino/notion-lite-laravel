<?php

namespace Tests\Feature;

use App\Domain\Page\Page;
use App\Domain\Page\PageTitle;
use App\Domain\Page\Tags;
use App\Infrastructure\Persistence\EloquentPageRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EloquentPageRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EloquentPageRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new EloquentPageRepository;
    }

    public function test_it_saves_and_reloads_a_page(): void
    {
        $saved = $this->repository->save($this->page('Notes', 'Contenu', ['php', 'laravel']));

        $reloaded = $this->repository->find($saved->id());

        $this->assertSame('Notes', $reloaded->title()->value);
        $this->assertSame('Contenu', $reloaded->content());
        $this->assertSame(['php', 'laravel'], $reloaded->tags()->values);
    }

    public function test_it_finds_all_descendants(): void
    {
        $root = $this->repository->save($this->page('Racine'));
        $child = $this->repository->save($this->page('Enfant', parent: $root));
        $this->repository->save($this->page('Petit-enfant', parent: $child));
        $this->repository->save($this->page('Autre racine'));

        $titles = array_map(fn (Page $page) => $page->title()->value, $this->repository->descendantsOf($root));

        $this->assertSame(['Enfant', 'Petit-enfant'], $titles);
    }

    public function test_search_ignores_archived_pages(): void
    {
        $this->repository->save($this->page('Réunion lundi'));
        $archived = $this->repository->save($this->page('Réunion mardi'));
        $archived->archive();
        $this->repository->save($archived);

        $this->assertCount(1, $this->repository->search('Réunion'));
        $this->assertCount(1, $this->repository->archived());
    }

    public function test_deleting_a_page_deletes_its_sub_pages(): void
    {
        $root = $this->repository->save($this->page('Racine'));
        $this->repository->save($this->page('Enfant', parent: $root));

        $this->repository->delete($root);

        $this->assertSame([], $this->repository->active());
    }

    /** @param list<string> $tags */
    private function page(string $title, string $content = '', array $tags = [], ?Page $parent = null): Page
    {
        return Page::create(PageTitle::fromString($title), $content, Tags::fromArray($tags), $parent);
    }
}
