<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Page\Page;
use App\Domain\Page\PageRepository;
use App\Domain\Page\PageTitle;
use App\Domain\Page\Tags;
use Illuminate\Database\Eloquent\Builder;

final class EloquentPageRepository implements PageRepository
{
    public function find(int $id): ?Page
    {
        $record = PageRecord::find($id);

        return $record ? $this->toDomain($record) : null;
    }

    public function save(Page $page): Page
    {
        $record = $page->id() ? PageRecord::findOrFail($page->id()) : new PageRecord;

        $record->fill([
            'parent_id' => $page->parentId(),
            'title' => $page->title()->value,
            'content' => $page->content(),
            'tags' => $page->tags()->values,
            'depth' => $page->depth(),
            'archived' => $page->isArchived(),
        ])->save();

        return $page->id() ? $page : $page->withId($record->id);
    }

    public function delete(Page $page): void
    {
        PageRecord::whereKey($page->id())->delete();
    }

    public function active(): array
    {
        return $this->map(PageRecord::where('archived', false)->orderBy('title'));
    }

    public function archived(): array
    {
        return $this->map(PageRecord::where('archived', true)->orderByDesc('updated_at'));
    }

    public function descendantsOf(Page $page): array
    {
        $descendants = [];
        $parentIds = [$page->id()];

        while ($parentIds !== []) {
            $children = $this->map(PageRecord::whereIn('parent_id', $parentIds));
            $descendants = [...$descendants, ...$children];
            $parentIds = array_map(fn (Page $child): int => $child->id(), $children);
        }

        return $descendants;
    }

    public function search(string $term): array
    {
        $like = '%'.$term.'%';

        return $this->map(
            PageRecord::where('archived', false)
                ->where(fn (Builder $query) => $query->where('title', 'like', $like)->orWhere('content', 'like', $like))
                ->orderBy('title')
        );
    }

    /** @return list<Page> */
    private function map(Builder $query): array
    {
        return $query->get()->map(fn (PageRecord $record): Page => $this->toDomain($record))->all();
    }

    private function toDomain(PageRecord $record): Page
    {
        return Page::reconstitute(
            id: $record->id,
            title: PageTitle::fromString($record->title),
            content: $record->content,
            tags: Tags::fromArray($record->tags ?? []),
            parentId: $record->parent_id,
            depth: $record->depth,
            archived: $record->archived,
            updatedAt: $record->updated_at->toDateTimeImmutable(),
        );
    }
}
