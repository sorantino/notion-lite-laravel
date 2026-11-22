<?php

namespace Tests\Unit\Domain;

use App\Domain\Page\PageRuleViolation;
use App\Domain\Page\Tags;
use PHPUnit\Framework\TestCase;

class TagsTest extends TestCase
{
    public function test_it_normalizes_and_deduplicates_tags(): void
    {
        $tags = Tags::fromCommaSeparated(' PHP, Laravel ,php, , Clean Code ');

        $this->assertSame(['php', 'laravel', 'clean-code'], $tags->values);
    }

    public function test_it_refuses_more_than_five_tags(): void
    {
        $this->expectException(PageRuleViolation::class);

        Tags::fromArray(['a', 'b', 'c', 'd', 'e', 'f']);
    }

    public function test_duplicates_do_not_count_towards_the_limit(): void
    {
        $tags = Tags::fromArray(['a', 'b', 'c', 'd', 'e', 'A', ' e ']);

        $this->assertCount(5, $tags->values);
    }

    public function test_it_refuses_a_tag_longer_than_30_characters(): void
    {
        $this->expectException(PageRuleViolation::class);

        Tags::fromArray([str_repeat('x', 31)]);
    }

    public function test_it_renders_back_to_a_comma_separated_string(): void
    {
        $this->assertSame('php, laravel', Tags::fromArray(['php', 'laravel'])->toCommaSeparated());
        $this->assertSame('', Tags::none()->toCommaSeparated());
    }
}
