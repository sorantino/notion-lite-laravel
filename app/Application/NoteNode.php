<?php

namespace App\Application;

use App\Domain\Note\Note;

final class NoteNode
{
    /** @param list<NoteNode> $children */
    public function __construct(public readonly Note $note, public readonly array $children = []) {}
}
