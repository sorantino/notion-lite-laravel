<?php

namespace Tests\Feature;

use App\Domain\Page\PageNotFound;
use App\Domain\Page\PageRuleViolation;
use App\Services\PageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageServiceTest extends TestCase
{
    use RefreshDatabase;

    private PageService $pages;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pages = $this->app->make(PageService::class);
    }

    public function test_it_creates_a_page_with_normalized_tags(): void
    {
        $page = $this->pages->create('  Ma page ', 'Texte', 'PHP, php, Laravel');

        $this->assertSame('Ma page', $page->title()->value);
        $this->assertSame(['php', 'laravel'], $this->pages->get($page->id())->tags()->values);
    }

    public function test_it_builds_the_page_tree(): void
    {
        $root = $this->pages->create('Racine');
        $this->pages->create('Enfant', parentId: $root->id());

        $tree = $this->pages->tree();

        $this->assertCount(1, $tree);
        $this->assertSame('Enfant', $tree[0]->children[0]->page->title()->value);
    }

    public function test_it_refuses_a_fourth_level(): void
    {
        $level1 = $this->pages->create('Niveau 1');
        $level2 = $this->pages->create('Niveau 2', parentId: $level1->id());
        $level3 = $this->pages->create('Niveau 3', parentId: $level2->id());

        $this->expectException(PageRuleViolation::class);

        $this->pages->create('Niveau 4', parentId: $level3->id());
    }

    public function test_archiving_a_page_archives_its_sub_pages(): void
    {
        $root = $this->pages->create('Racine');
        $child = $this->pages->create('Enfant', parentId: $root->id());

        $this->pages->archive($root->id());

        $this->assertTrue($this->pages->get($child->id())->isArchived());
        $this->assertSame([], $this->pages->tree());
        $this->assertCount(2, $this->pages->trash());
    }

    public function test_a_sub_page_cannot_be_restored_before_its_parent(): void
    {
        $root = $this->pages->create('Racine');
        $child = $this->pages->create('Enfant', parentId: $root->id());
        $this->pages->archive($root->id());

        $this->expectException(PageRuleViolation::class);

        $this->pages->restore($child->id());
    }

    public function test_restoring_a_page_restores_its_sub_pages(): void
    {
        $root = $this->pages->create('Racine');
        $child = $this->pages->create('Enfant', parentId: $root->id());
        $this->pages->archive($root->id());

        $this->pages->restore($root->id());

        $this->assertFalse($this->pages->get($child->id())->isArchived());
    }

    public function test_an_active_page_cannot_be_deleted(): void
    {
        $page = $this->pages->create('Active');

        $this->expectException(PageRuleViolation::class);

        $this->pages->delete($page->id());
    }

    public function test_an_archived_page_can_be_deleted(): void
    {
        $page = $this->pages->create('À supprimer');
        $this->pages->archive($page->id());

        $this->pages->delete($page->id());

        $this->expectException(PageNotFound::class);
        $this->pages->get($page->id());
    }

    public function test_search_looks_in_title_and_content(): void
    {
        $this->pages->create('Réunion', 'Budget validé');
        $this->pages->create('Idées', 'Parler du budget');
        $this->pages->create('Autre', 'Rien à voir');

        $this->assertCount(2, $this->pages->search('budget'));
        $this->assertSame([], $this->pages->search('   '));
    }
}
