<?php

namespace App\Services;

use App\Domain\Page\Page;
use App\Domain\Page\PageNotFound;
use App\Domain\Page\PageRepository;
use App\Domain\Page\PageTitle;
use App\Domain\Page\Tags;
use Illuminate\Support\Facades\DB;

final class PageService
{
    public function __construct(private readonly PageRepository $pages) {}

    public function get(int $id): Page
    {
        return $this->pages->find($id) ?? throw PageNotFound::withId($id);
    }

    public function create(string $title, ?string $content = '', ?string $tags = '', ?int $parentId = null): Page
    {
        $parent = $parentId === null ? null : $this->get($parentId);

        $page = Page::create(
            PageTitle::fromString($title),
            (string) $content,
            Tags::fromCommaSeparated($tags),
            $parent,
        );

        return $this->pages->save($page);
    }

    public function update(int $id, string $title, ?string $content, ?string $tags): Page
    {
        $page = $this->get($id);

        $page->edit(PageTitle::fromString($title), (string) $content, Tags::fromCommaSeparated($tags));

        return $this->pages->save($page);
    }

    public function archive(int $id): void
    {
        $page = $this->get($id);

        DB::transaction(function () use ($page): void {
            foreach ([$page, ...$this->pages->descendantsOf($page)] as $toArchive) {
                $toArchive->archive();
                $this->pages->save($toArchive);
            }
        });
    }

    public function restore(int $id): void
    {
        $page = $this->get($id);
        $parent = $page->parentId() === null ? null : $this->get($page->parentId());

        DB::transaction(function () use ($page, $parent): void {
            $page->restore($parent);
            $this->pages->save($page);

            foreach ($this->pages->descendantsOf($page) as $descendant) {
                $descendant->restore(null);
                $this->pages->save($descendant);
            }
        });
    }

    public function delete(int $id): void
    {
        $page = $this->get($id);

        $page->ensureCanBeDeleted();

        $this->pages->delete($page);
    }

    /** @return list<PageNode> */
    public function tree(): array
    {
        $byParent = [];
        foreach ($this->pages->active() as $page) {
            $byParent[$page->parentId() ?? 0][] = $page;
        }

        $build = function (int $parentId) use (&$build, $byParent): array {
            return array_map(
                fn (Page $page): PageNode => new PageNode($page, $build($page->id())),
                $byParent[$parentId] ?? [],
            );
        };

        return $build(0);
    }

    /** @return list<Page> */
    public function childrenOf(Page $page): array
    {
        return array_values(array_filter(
            $this->pages->active(),
            fn (Page $candidate): bool => $candidate->parentId() === $page->id(),
        ));
    }

    /** @return list<Page> */
    public function trash(): array
    {
        return $this->pages->archived();
    }

    /** @return list<Page> */
    public function search(string $term): array
    {
        $term = trim($term);

        return $term === '' ? [] : $this->pages->search($term);
    }
}
