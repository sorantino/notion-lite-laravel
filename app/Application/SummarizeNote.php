<?php

namespace App\Application;

use App\Domain\Note\NoteNotFound;
use App\Domain\Note\NoteRepository;
use App\Domain\Ports\LlmPort;

final class SummarizeNote
{
    public function __construct(
        private readonly NoteRepository $notes,
        private readonly LlmPort $llm,
    ) {}

    public function handle(int $noteId): string
    {
        $note = $this->notes->find($noteId) ?? throw NoteNotFound::withId($noteId);

        $summary = trim($this->llm->complete([
            ['role' => 'system', 'content' => 'Tu résumes des notes en français, en trois phrases maximum.'],
            ['role' => 'user', 'content' => "# {$note->title()}\n\n{$note->content()}"],
        ]));

        $this->notes->saveSummary($note, $summary);

        return $summary;
    }
}
