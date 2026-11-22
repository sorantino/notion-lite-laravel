<?php

namespace App\Application;

use App\Domain\Note\Note;
use App\Domain\Note\NoteNotFound;
use App\Domain\Note\NoteRepository;
use App\Domain\Note\NoteTitle;
use App\Domain\Note\Tags;
use Illuminate\Support\Facades\DB;

final class NoteService
{
    public function __construct(private readonly NoteRepository $notes) {}

    public function get(int $id): Note
    {
        return $this->notes->find($id) ?? throw NoteNotFound::withId($id);
    }

    public function create(string $title, ?string $content = '', ?string $tags = '', ?int $parentId = null): Note
    {
        $parent = $parentId === null ? null : $this->get($parentId);

        $note = Note::create(
            NoteTitle::fromString($title),
            (string) $content,
            Tags::fromCommaSeparated($tags),
            $parent,
        );

        return $this->notes->save($note);
    }

    public function update(int $id, string $title, ?string $content, ?string $tags): Note
    {
        $note = $this->get($id);

        $note->edit(NoteTitle::fromString($title), (string) $content, Tags::fromCommaSeparated($tags));

        return $this->notes->save($note);
    }

    public function archive(int $id): void
    {
        $note = $this->get($id);

        DB::transaction(function () use ($note): void {
            foreach ([$note, ...$this->notes->descendantsOf($note)] as $toArchive) {
                $toArchive->archive();
                $this->notes->save($toArchive);
            }
        });
    }

    public function restore(int $id): void
    {
        $note = $this->get($id);
        $parent = $note->parentId() === null ? null : $this->get($note->parentId());

        DB::transaction(function () use ($note, $parent): void {
            $note->restore($parent);
            $this->notes->save($note);

            foreach ($this->notes->descendantsOf($note) as $descendant) {
                $descendant->restore(null);
                $this->notes->save($descendant);
            }
        });
    }

    public function delete(int $id): void
    {
        $note = $this->get($id);

        $note->ensureCanBeDeleted();

        $this->notes->delete($note);
    }

    /** @return list<NoteNode> */
    public function tree(): array
    {
        $byParent = [];
        foreach ($this->notes->active() as $note) {
            $byParent[$note->parentId() ?? 0][] = $note;
        }

        $build = function (int $parentId) use (&$build, $byParent): array {
            return array_map(
                fn (Note $note): NoteNode => new NoteNode($note, $build($note->id())),
                $byParent[$parentId] ?? [],
            );
        };

        return $build(0);
    }

    /** @return list<Note> */
    public function childrenOf(Note $note): array
    {
        return array_values(array_filter(
            $this->notes->active(),
            fn (Note $candidate): bool => $candidate->parentId() === $note->id(),
        ));
    }

    /** @return list<Note> */
    public function trash(): array
    {
        return $this->notes->archived();
    }

    /** @return list<Note> */
    public function search(string $term): array
    {
        $term = trim($term);

        return $term === '' ? [] : $this->notes->search($term);
    }
}
