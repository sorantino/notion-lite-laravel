# INIT — notion-lite (Laravel)

> Dépôt d'exemple pour la **séquence S1.1** (support écrit).
> Dépôt jumeau, lu par l'apprenant (pas de TP) : `notion-lite-springboot`.

## 1. Objectif

Montrer **un besoin métier simple codé en couches** :

```
HTTP ──► Contrôleur ──► Service ──► Domaine
          (framework)   (orchestration)  (métier pur)
                            │
                            └──► Repository (interface du domaine)
                                     ▲
                                     └── implémentation Eloquent (framework)
```

et rendre visible, dans le README, **ce qui vient du framework** et **ce qui vient du métier**.

Le dépôt sert ensuite de base pour les séquences suivantes, où l'on ajoute des
fonctionnalités avec le **SDK IA de Laravel**, sans toucher au domaine.

## 2. Le besoin métier : un mini Notion

Un espace de **pages** rédigées en Markdown, organisées en arborescence.

| # | Fonctionnalité | Règle métier portée par le domaine |
|---|----------------|------------------------------------|
| 1 | Créer / modifier une page | Titre obligatoire, 120 caractères max |
| 2 | Étiqueter une page | Étiquettes normalisées (minuscules, sans doublon), 5 max |
| 3 | Créer une sous-page | Imbrication limitée à 3 niveaux |
| 4 | Mettre à la corbeille | La page et ses sous-pages passent en lecture seule |
| 5 | Restaurer | Impossible si la page parente est encore à la corbeille |
| 6 | Supprimer définitivement | Seulement depuis la corbeille |
| 7 | Rechercher | Dans le titre et le contenu des pages actives |

## 3. Architecture cible

| Couche | Dossier | Dépend de Laravel ? |
|--------|---------|---------------------|
| Domaine | `app/Domain/Page/` | **Non** : PHP pur, testé sans base |
| Service | `app/Services/` | Un peu (transactions) |
| Contrôleur | `app/Http/Controllers/`, `app/Http/Requests/` | Oui |
| Infrastructure | `app/Infrastructure/Persistence/` | Oui (Eloquent) |
| Vues | `resources/views/` | Oui (Blade) |

Choix techniques : Laravel 13, PHP 8.3+, SQLite, Blade sans build front, PHPUnit.

## 4. Plan de livraison : un commit par brique

| Étape | Commit | Contenu |
|-------|--------|---------|
| 0 | `chore: init` | Squelette Laravel vierge + ce fichier |
| 1 | `feat(domain): page, titre, étiquettes` | Entité `Page`, value objects, exceptions, interface `PageRepository`, tests unitaires |
| 2 | `feat(persistence): stockage Eloquent` | Migration `pages`, modèle `PageRecord`, `EloquentPageRepository` |
| 3 | `feat(service): cas d'usage des pages` | `PageService` : créer, modifier, sous-pages, corbeille, recherche, arbre |
| 4 | `feat(web): interface des pages` | Contrôleur, FormRequest, routes, vues Blade, gestion des erreurs métier |
| 5 | `chore: données d'exemple` | Seeder avec quelques pages |
| 6 | `docs: README framework vs métier` | Tableau fichier par fichier, schéma des couches |

Le dépôt Spring Boot suit le même plan.

## 5. Suite : fonctionnalités avec le SDK IA Laravel (hors S1.1)

Chaque fonctionnalité IA arrive dans un **nouveau service** ; les règles du domaine
s'appliquent aussi à ce que produit l'IA.

| Fonctionnalité | Ce qu'elle montre |
|----------------|-------------------|
| Résumer une page | Génération de texte simple |
| Suggérer des étiquettes | Sortie structurée, revalidée par `Tags` (5 max, normalisées) |
| Rédiger un brouillon à partir d'une consigne | Génération + création via `PageService` |
| Reformuler / traduire un passage | Streaming de la réponse |
| Poser une question à son espace | Embeddings + recherche sémantique (RAG) |
| Assistant qui crée et range des pages | Agent avec outils qui appellent `PageService` |

## 6. Critère de fin (S1.1)

- [ ] Les deux dépôts sont publics.
- [ ] Ils sont liés depuis le chapitre 01.
