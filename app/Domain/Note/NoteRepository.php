<?php

namespace App\Domain\Note;

interface NoteRepository
{
    public function find(int $id): ?Note;

    public function save(Note $note): Note;

    public function saveSummary(Note $note, string $summary): void;

    public function delete(Note $note): void;

    /** @return list<Note> */
    public function active(): array;

    /** @return list<Note> */
    public function archived(): array;

    /** @return list<Note> */
    public function descendantsOf(Note $note): array;

    /** @return list<Note> */
    public function search(string $term): array;
}
