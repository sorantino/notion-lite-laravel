<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Note\Note;
use App\Domain\Note\NoteRepository;
use App\Domain\Note\NoteTitle;
use App\Domain\Note\Tags;
use Illuminate\Database\Eloquent\Builder;

final class EloquentNoteRepository implements NoteRepository
{
    public function find(int $id): ?Note
    {
        $record = NoteRecord::find($id);

        return $record ? $this->toDomain($record) : null;
    }

    public function save(Note $note): Note
    {
        $record = $note->id() ? NoteRecord::findOrFail($note->id()) : new NoteRecord;

        $record->fill([
            'parent_id' => $note->parentId(),
            'title' => $note->title()->value,
            'content' => $note->content(),
            'tags' => $note->tags()->values,
            'depth' => $note->depth(),
            'archived' => $note->isArchived(),
        ])->save();

        return $note->id() ? $note : $note->withId($record->id);
    }

    public function saveSummary(Note $note, string $summary): void
    {
        NoteRecord::whereKey($note->id())->update(['summary' => $summary]);
    }

    public function delete(Note $note): void
    {
        NoteRecord::whereKey($note->id())->delete();
    }

    public function active(): array
    {
        return $this->map(NoteRecord::where('archived', false)->orderBy('title'));
    }

    public function archived(): array
    {
        return $this->map(NoteRecord::where('archived', true)->orderByDesc('updated_at'));
    }

    public function descendantsOf(Note $note): array
    {
        $descendants = [];
        $parentIds = [$note->id()];

        while ($parentIds !== []) {
            $children = $this->map(NoteRecord::whereIn('parent_id', $parentIds));
            $descendants = [...$descendants, ...$children];
            $parentIds = array_map(fn (Note $child): int => $child->id(), $children);
        }

        return $descendants;
    }

    public function search(string $term): array
    {
        $like = '%'.$term.'%';

        return $this->map(
            NoteRecord::where('archived', false)
                ->where(fn (Builder $query) => $query->where('title', 'like', $like)->orWhere('content', 'like', $like))
                ->orderBy('title')
        );
    }

    /** @return list<Note> */
    private function map(Builder $query): array
    {
        return $query->get()->map(fn (NoteRecord $record): Note => $this->toDomain($record))->all();
    }

    private function toDomain(NoteRecord $record): Note
    {
        return Note::reconstitute(
            id: $record->id,
            title: NoteTitle::fromString($record->title),
            content: $record->content,
            tags: Tags::fromArray($record->tags ?? []),
            parentId: $record->parent_id,
            depth: $record->depth,
            archived: $record->archived,
            updatedAt: $record->updated_at->toDateTimeImmutable(),
            summary: $record->summary,
        );
    }
}
