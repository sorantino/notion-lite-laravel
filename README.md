# Notion Lite — Laravel

Un mini Notion (pages Markdown en arborescence, étiquettes, corbeille, recherche)
codé **en couches** pour montrer ce qui vient du framework et ce qui vient du métier.

> Dépôt jumeau, même métier en Spring Boot : [notion-lite-spring](https://github.com/sorantino/notion-lite-spring)

## Lancer le projet

```sh
composer install
cp .env.example .env && php artisan key:generate
php artisan migrate:fresh --seed   # base SQLite + pages d'exemple
php artisan serve                  # http://127.0.0.1:8000
```

Tests : `php artisan test`

## Les couches

```
Requête HTTP
   │
   ▼
PageController ── SavePageRequest        FRAMEWORK : routes, validation de forme, vues
   │
   ▼
PageService                              SERVICE : orchestre le cas d'usage
   │
   ├──► Page, PageTitle, Tags            MÉTIER : les règles, en PHP pur
   │
   └──► PageRepository (interface)       MÉTIER : ce dont le domaine a besoin
             ▲
             └── EloquentPageRepository  FRAMEWORK : comment on stocke (Eloquent)
```

## Ce qui vient du framework, ce qui vient du métier

| Fichier | Couche | Origine | Rôle |
|---|---|---|---|
| `app/Domain/Page/Page.php` | Domaine | **Métier** | L'entité et ses règles (3 niveaux max, corbeille en lecture seule…) |
| `app/Domain/Page/PageTitle.php` | Domaine | **Métier** | Titre obligatoire, 120 caractères max |
| `app/Domain/Page/Tags.php` | Domaine | **Métier** | 5 étiquettes max, normalisées, sans doublon |
| `app/Domain/Page/PageRuleViolation.php` | Domaine | **Métier** | Les erreurs métier, dans la langue du métier |
| `app/Domain/Page/PageRepository.php` | Domaine | **Métier** | Le contrat de stockage (une interface) |
| `app/Services/PageService.php` | Service | **Métier** + un peu de framework | Les cas d'usage ; `DB::transaction` est le seul appel à Laravel |
| `app/Http/Controllers/PageController.php` | Contrôleur | Framework | Traduit HTTP ↔ service |
| `app/Http/Requests/SavePageRequest.php` | Contrôleur | Framework | Valide la *forme* (champ présent, type) |
| `app/Infrastructure/Persistence/*` | Infrastructure | Framework | Modèle Eloquent + implémentation du contrat |
| `app/Providers/AppServiceProvider.php` | Configuration | Framework | Relie l'interface `PageRepository` à Eloquent |
| `bootstrap/app.php` | Configuration | Framework | Transforme une erreur métier en message, `PageNotFound` en 404 |
| `routes/web.php`, `resources/views/*` | Web | Framework | Routes et vues Blade |
| `database/migrations/*` | Infrastructure | Framework | Schéma de la table `pages` |

**Le test simple :** `grep -r Illuminate app/Domain` ne renvoie rien.
Le domaine se teste avec PHPUnit pur (`tests/Unit/Domain`), sans base ni Laravel.

## Exemple : une règle, deux validations

Créer une page avec un titre de 200 caractères :

1. `SavePageRequest` vérifie que le champ `title` **existe** et est une chaîne. ✅ (framework)
2. `PageTitle::fromString()` refuse plus de 120 caractères. ❌ (métier)
3. `bootstrap/app.php` attrape `PageRuleViolation` et renvoie au formulaire avec le message.

La règle des 120 caractères vit **une seule fois**, dans le domaine. Elle s'applique
quelle que soit l'entrée : formulaire, seeder, commande artisan… ou IA.

## Brancher l'IA (séquences suivantes)

Chaque fonctionnalité IA s'ajoute comme une **nouvelle brique**, sans toucher au domaine :

| Fonctionnalité | Où la brancher |
|---|---|
| Résumer une page | `PageAiService::summarize()` + un bouton dans `pages/show` |
| Suggérer des étiquettes | Réponse structurée de l'IA → `Tags::fromArray()` (la limite de 5 s'applique) |
| Rédiger un brouillon | L'IA produit titre + contenu → `PageService::create()` |
| Reformuler / traduire | Réponse en streaming dans l'éditeur |
| Questions sur son espace | Embeddings sur `PageRepository::active()` |
| Agent qui range les pages | Outils qui appellent `PageService` (les règles de profondeur s'appliquent) |

Les emplacements des futurs boutons sont marqués dans `resources/views/pages/show.blade.php`.
