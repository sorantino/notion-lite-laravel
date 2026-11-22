<?php

namespace Tests\Unit\Domain;

use App\Domain\Note\NoteRuleViolation;
use App\Domain\Note\NoteTitle;
use PHPUnit\Framework\TestCase;

class NoteTitleTest extends TestCase
{
    public function test_it_trims_and_collapses_spaces(): void
    {
        $title = NoteTitle::fromString('   Notes   de  réunion  ');

        $this->assertSame('Notes de réunion', $title->value);
    }

    public function test_it_refuses_an_empty_title(): void
    {
        $this->expectException(NoteRuleViolation::class);

        NoteTitle::fromString('    ');
    }

    public function test_it_refuses_a_title_longer_than_120_characters(): void
    {
        $this->expectException(NoteRuleViolation::class);

        NoteTitle::fromString(str_repeat('a', 121));
    }

    public function test_it_accepts_a_title_of_exactly_120_characters(): void
    {
        $title = NoteTitle::fromString(str_repeat('é', 120));

        $this->assertSame(120, mb_strlen($title->value));
    }
}
