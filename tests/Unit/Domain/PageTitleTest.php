<?php

namespace Tests\Unit\Domain;

use App\Domain\Page\PageRuleViolation;
use App\Domain\Page\PageTitle;
use PHPUnit\Framework\TestCase;

// Tests du domaine : PHPUnit pur, sans Laravel ni base de données.
class PageTitleTest extends TestCase
{
    public function test_it_trims_and_collapses_spaces(): void
    {
        $title = PageTitle::fromString('   Notes   de  réunion  ');

        $this->assertSame('Notes de réunion', $title->value);
    }

    public function test_it_refuses_an_empty_title(): void
    {
        $this->expectException(PageRuleViolation::class);

        PageTitle::fromString('    ');
    }

    public function test_it_refuses_a_title_longer_than_120_characters(): void
    {
        $this->expectException(PageRuleViolation::class);

        PageTitle::fromString(str_repeat('a', 121));
    }

    public function test_it_accepts_a_title_of_exactly_120_characters(): void
    {
        $title = PageTitle::fromString(str_repeat('é', 120));

        $this->assertSame(120, mb_strlen($title->value));
    }
}
