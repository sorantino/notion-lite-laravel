<?php

namespace Tests\Unit\Domain;

use App\Domain\Page\Page;
use App\Domain\Page\PageRuleViolation;
use App\Domain\Page\PageTitle;
use App\Domain\Page\Tags;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class PageTest extends TestCase
{
    public function test_a_new_page_is_a_root_page_and_not_archived(): void
    {
        $page = $this->newPage('Accueil');

        $this->assertNull($page->id());
        $this->assertNull($page->parentId());
        $this->assertSame(0, $page->depth());
        $this->assertFalse($page->isArchived());
    }

    public function test_a_sub_page_is_one_level_below_its_parent(): void
    {
        $parent = $this->existingPage(id: 1, depth: 0);

        $child = Page::create(PageTitle::fromString('Enfant'), '', Tags::none(), $parent);

        $this->assertSame(1, $child->parentId());
        $this->assertSame(1, $child->depth());
    }

    public function test_nesting_is_limited_to_three_levels(): void
    {
        $deepest = $this->existingPage(id: 3, depth: Page::MAX_DEPTH);

        $this->expectException(PageRuleViolation::class);

        Page::create(PageTitle::fromString('Trop profond'), '', Tags::none(), $deepest);
    }

    public function test_a_sub_page_cannot_be_created_in_an_archived_page(): void
    {
        $archivedParent = $this->existingPage(id: 1, archived: true);

        $this->expectException(PageRuleViolation::class);

        Page::create(PageTitle::fromString('Enfant'), '', Tags::none(), $archivedParent);
    }

    public function test_a_page_can_be_edited(): void
    {
        $page = $this->newPage('Brouillon');

        $page->edit(PageTitle::fromString('Version finale'), '# Contenu', Tags::fromArray(['doc']));

        $this->assertSame('Version finale', $page->title()->value);
        $this->assertSame('# Contenu', $page->content());
        $this->assertSame(['doc'], $page->tags()->values);
    }

    public function test_an_archived_page_is_read_only(): void
    {
        $page = $this->newPage('Ancienne page');
        $page->archive();

        $this->expectException(PageRuleViolation::class);

        $page->edit(PageTitle::fromString('Nouveau titre'), '', Tags::none());
    }

    public function test_a_page_cannot_be_restored_while_its_parent_is_archived(): void
    {
        $parent = $this->existingPage(id: 1, archived: true);
        $child = $this->existingPage(id: 2, depth: 1, parentId: 1, archived: true);

        $this->expectException(PageRuleViolation::class);

        $child->restore($parent);
    }

    public function test_a_page_can_be_restored_when_its_parent_is_active(): void
    {
        $parent = $this->existingPage(id: 1);
        $child = $this->existingPage(id: 2, depth: 1, parentId: 1, archived: true);

        $child->restore($parent);

        $this->assertFalse($child->isArchived());
    }

    public function test_only_an_archived_page_can_be_deleted(): void
    {
        $page = $this->newPage('Active');

        $this->expectException(PageRuleViolation::class);

        $page->ensureCanBeDeleted();
    }

    public function test_an_archived_page_can_be_deleted(): void
    {
        $page = $this->newPage('À supprimer');
        $page->archive();

        $page->ensureCanBeDeleted();

        $this->assertTrue($page->isArchived());
    }

    public function test_excerpt_is_plain_text_and_truncated(): void
    {
        $page = Page::create(PageTitle::fromString('Long'), "<b>Bonjour</b>\n\n".str_repeat('x', 200), Tags::none());

        $excerpt = $page->excerpt(20);

        $this->assertStringStartsWith('Bonjour x', $excerpt);
        $this->assertSame(21, mb_strlen($excerpt));
    }

    private function newPage(string $title): Page
    {
        return Page::create(PageTitle::fromString($title), '', Tags::none());
    }

    private function existingPage(int $id, int $depth = 0, ?int $parentId = null, bool $archived = false): Page
    {
        return Page::reconstitute(
            id: $id,
            title: PageTitle::fromString("Page {$id}"),
            content: '',
            tags: Tags::none(),
            parentId: $parentId,
            depth: $depth,
            archived: $archived,
            updatedAt: new DateTimeImmutable,
        );
    }
}
