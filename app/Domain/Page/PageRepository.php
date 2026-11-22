<?php

namespace App\Domain\Page;

// Contrat de stockage défini par le domaine, implémenté par l'infrastructure.
interface PageRepository
{
    public function find(int $id): ?Page;

    /** Insère ou met à jour, et renvoie la page avec son identifiant. */
    public function save(Page $page): Page;

    public function delete(Page $page): void;

    /** @return list<Page> pages actives (hors corbeille), triées par titre */
    public function active(): array;

    /** @return list<Page> pages à la corbeille */
    public function archived(): array;

    /** @return list<Page> enfants, petits-enfants… de la page */
    public function descendantsOf(Page $page): array;

    /** @return list<Page> pages actives dont le titre ou le contenu contient le terme */
    public function search(string $term): array;
}
