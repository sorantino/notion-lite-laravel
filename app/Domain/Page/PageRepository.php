<?php

namespace App\Domain\Page;

interface PageRepository
{
    public function find(int $id): ?Page;

    public function save(Page $page): Page;

    public function delete(Page $page): void;

    /** @return list<Page> */
    public function active(): array;

    /** @return list<Page> */
    public function archived(): array;

    /** @return list<Page> */
    public function descendantsOf(Page $page): array;

    /** @return list<Page> */
    public function search(string $term): array;
}
