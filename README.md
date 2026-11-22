# Notion Lite (Laravel)

Un mini Notion (notes Markdown en arborescence, étiquettes, corbeille, recherche)
codé **en couches** pour montrer ce qui vient du framework et ce qui vient du métier.

> Dépôt jumeau, même métier en Spring Boot : [notion-lite-spring](https://github.com/sorantino/notion-lite-spring)

## Lancer le projet

```sh
composer install
cp .env.example .env && php artisan key:generate
php artisan migrate:fresh --seed   # base SQLite + notes d'exemple
php artisan serve                  # http://127.0.0.1:8000
```

Tests : `php artisan test`

Le dépôt tourne sans clé d'API : par défaut, `LLM_PROVIDER=fake` renvoie un résumé fixe.

## Les couches

```
Requête HTTP
   │
   ▼
NoteController ── SaveNoteRequest        FRAMEWORK : routes, validation de forme, vues
   │
   ▼
NoteService, SummarizeNote               APPLICATION : les cas d'usage
   │
   ├──► Note, NoteTitle, Tags            MÉTIER : les règles, en PHP pur
   │
   ├──► NoteRepository (interface)       MÉTIER : ce dont le domaine a besoin pour stocker
   │         ▲
   │         └── EloquentNoteRepository  FRAMEWORK : comment on stocke (Eloquent)
   │
   └──► LlmPort (interface)              MÉTIER : ce dont le domaine a besoin pour l'IA
             ▲
             └── Fake / Http / Ollama    INFRASTRUCTURE : quel modèle on appelle
```

## Ce qui vient du framework, ce qui vient du métier

| Fichier | Couche | Origine | Rôle |
|---|---|---|---|
| `app/Domain/Note/Note.php` | Domaine | **Métier** | L'entité et ses règles (3 niveaux max, corbeille en lecture seule…) |
| `app/Domain/Note/NoteTitle.php` | Domaine | **Métier** | Titre obligatoire, 120 caractères max |
| `app/Domain/Note/Tags.php` | Domaine | **Métier** | 5 étiquettes max, normalisées, sans doublon |
| `app/Domain/Note/NoteRuleViolation.php` | Domaine | **Métier** | Les erreurs métier, dans la langue du métier |
| `app/Domain/Note/NoteRepository.php` | Domaine | **Métier** | Le contrat de stockage (une interface) |
| `app/Domain/Ports/LlmPort.php` | Domaine | **Métier** | Le contrat d'appel à un modèle de langage |
| `app/Application/NoteService.php` | Application | **Métier** + un peu de framework | Les cas d'usage ; `DB::transaction` est le seul appel à Laravel |
| `app/Application/SummarizeNote.php` | Application | **Métier** | Résumer une note via `LlmPort`, puis enregistrer le résumé |
| `app/Http/Controllers/NoteController.php` | Contrôleur | Framework | Traduit HTTP ↔ cas d'usage |
| `app/Http/Requests/SaveNoteRequest.php` | Contrôleur | Framework | Valide la *forme* (champ présent, type) |
| `app/Infrastructure/Persistence/*` | Infrastructure | Framework | Modèle Eloquent + implémentation de `NoteRepository` |
| `app/Infrastructure/Llm/*` | Infrastructure | Framework | Les adaptateurs `LlmPort` (factice, API HTTP, Ollama) |
| `app/Providers/AppServiceProvider.php` | Configuration | Framework | Relie chaque interface à son implémentation |
| `config/llm.php` | Configuration | Framework | Choix du fournisseur (`LLM_PROVIDER`) |
| `bootstrap/app.php` | Configuration | Framework | Transforme une erreur métier en message, `NoteNotFound` en 404 |
| `routes/web.php`, `resources/views/*` | Web | Framework | Routes et vues Blade |
| `database/migrations/*` | Infrastructure | Framework | Schéma de la table `notes` |

**Le test simple :** `grep -r Illuminate app/Domain` ne renvoie rien.
Le domaine se teste avec PHPUnit pur (`tests/Unit/Domain`), sans base ni Laravel.

## Exemple : une règle, deux validations

Créer une note avec un titre de 200 caractères :

1. `SaveNoteRequest` vérifie que le champ `title` **existe** et est une chaîne. ✅ (framework)
2. `NoteTitle::fromString()` refuse plus de 120 caractères. ❌ (métier)
3. `bootstrap/app.php` attrape `NoteRuleViolation` et renvoie au formulaire avec le message.

La règle des 120 caractères vit **une seule fois**, dans le domaine. Elle s'applique
quelle que soit l'entrée : formulaire, seeder, commande artisan… ou IA.

## Choisir le modèle de langage

| `LLM_PROVIDER` | Adaptateur | Variables utiles |
|---|---|---|
| `fake` (défaut) | `FakeLlmAdapter` : renvoie une chaîne fixe | aucune |
| `http` | `HttpLlmAdapter` : API compatible `/chat/completions` | `LLM_BASE_URL`, `LLM_API_KEY`, `LLM_MODEL` |
| `ollama` | `OllamaAdapter` : modèle local | `OLLAMA_HOST`, `OLLAMA_MODEL` |

Changer de fournisseur ne touche ni au domaine, ni à `SummarizeNote` : seul le binding change.

## Brancher l'IA

Chaque fonctionnalité IA s'ajoute comme une **nouvelle brique**, sans toucher au domaine :

| Fonctionnalité | Où la brancher |
|---|---|
| Résumer une note | `SummarizeNote::handle()` + le bouton ✨ Résumer (déjà en place) |
| Suggérer des étiquettes | Réponse structurée de l'IA → `Tags::fromArray()` (la limite de 5 s'applique) |
| Rédiger un brouillon | L'IA produit titre + contenu → `NoteService::create()` |
| Reformuler / traduire | Réponse en streaming dans l'éditeur |
| Questions sur son espace | Embeddings sur `NoteRepository::active()` |
| Agent qui range les notes | Outils qui appellent `NoteService` (les règles de profondeur s'appliquent) |
