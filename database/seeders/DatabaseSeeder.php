<?php

namespace Database\Seeders;

use App\Services\PageService;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(PageService $pages): void
    {
        $welcome = $pages->create(
            'Bienvenue dans Notion Lite',
            "Un mini Notion pour apprendre à **séparer le métier du framework**.\n\n- Créez des pages et des sous-pages (3 niveaux max)\n- Ajoutez des étiquettes (5 max)\n- Mettez à la corbeille, restaurez, recherchez",
            'guide',
        );
        $pages->create('Raccourcis Markdown', "# Titre\n\n**gras**, *italique*, `code`\n\n> citation", 'guide', $welcome->id());

        $project = $pages->create(
            'Projet : refonte du site',
            "## Objectif\n\nRefondre le site vitrine avant la fin du trimestre.\n\n## Décisions\n\n- Stack : Laravel + Blade\n- Hébergement : à définir\n- Budget validé le 12/11",
            'projet, web',
        );
        $meetings = $pages->create('Comptes rendus', 'Les réunions du projet.', 'réunion', $project->id());
        $pages->create(
            'Réunion du 18/11',
            "Présents : Alice, Karim, Léa.\n\n- Maquettes validées, sauf la page contact\n- Karim prépare l'estimation de l'hébergement\n- Prochaine réunion le 25/11\n\nPoint bloquant : le formulaire de contact doit être conforme RGPD.",
            'réunion, rgpd',
            $meetings->id(),
        );
        $pages->create('Backlog', "- [ ] Page contact\n- [ ] Blog\n- [x] Maquettes", 'projet', $project->id());

        $pages->create(
            'Idées d\'articles',
            "- Pourquoi séparer le domaine du framework\n- Laravel vs Spring Boot : les mêmes couches\n- Brancher une IA sans casser son métier",
            'blog',
        );
    }
}
