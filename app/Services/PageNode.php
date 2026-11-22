<?php

namespace App\Services;

use App\Domain\Page\Page;

final class PageNode
{
    /** @param list<PageNode> $children */
    public function __construct(public readonly Page $page, public readonly array $children = []) {}
}
