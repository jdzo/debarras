# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Symfony 7.3 PHP application — site vitrine + estimation en ligne pour une société de débarras. Architecture DDD, FrankenPHP, PostgreSQL.

Plan d'implémentation : `doc/plan_implementation.md`
Plan lead generation : `doc/plan_lead_generation.md`
Audit sécurité : `doc/audit_securite.md`
Stratégie business : `doc/strategie_business.md`
Analyse concurrentielle : `doc/analyse_concurrentielle.md`

## Development Commands

All commands use Docker via Makefile:

```bash
make up              # Start containers (app at https://debarras.localhost)
make down            # Stop containers
make restart         # Restart containers
make logs            # Stream container logs
make bash            # Shell into PHP container
make composer-install # Install PHP dependencies
make cache-clear     # Clear Symfony cache
make buildq          # Rebuild Docker image (no cache)
make clean           # Remove all containers, volumes, images
```

Inside container (`make bash`):
```bash
php bin/console <command>       # Symfony console
./vendor/bin/phpunit            # Run all tests
./vendor/bin/phpunit tests/Path/To/Test.php  # Run single test
```

## Architecture

The project follows DDD with clear layer separation:

```
src/
├── Domain/
│   ├── Estimation/    # Agrégat estimation (calcul prix, fourchette)
│   ├── Lead/          # Agrégat lead (scoring, relance, conversion)
│   ├── Chantier/      # Agrégat chantier (non actif)
│   └── Shared/        # Interfaces partagées (MessageBus)
├── Application/
│   ├── Estimation/    # Commands/Queries estimation
│   └── Lead/          # Commands/Queries lead
├── Infrastructure/
│   ├── Persistence/   # Doctrine entities + repositories
│   ├── Notification/  # EstimationNotifier, LeadNotifier, ContactNotifier
│   ├── Messaging/     # Symfony Messenger adapter
│   ├── Upload/        # PhotoUploader
│   └── Console/       # Commandes CLI (relance leads)
└── UI/
    └── Http/
        ├── Controller/    # AccueilController, EstimationController, LeadController, AdminController, etc.
        └── EventListener/ # UtmCaptureListener
```

**Key patterns:**
- Deux bounded contexts : Estimation (calcul) et Lead (conversion)
- Value objects avec validation dans les constructeurs
- Domain events pour les side effects (async via Messenger)
- Lead scoring : HOT/WARM/COLD via ScoringLead service
- UTM tracking : capturé en session, propagé aux commandes

## Technical Stack

- PHP 8.2+ / Symfony 7.3
- PostgreSQL 16
- FrankenPHP (Docker)
- Symfony Messenger for async processing (Doctrine transport)
- PHPUnit 12 with strict mode (fails on warnings, notices, deprecations)
- Twig templates with Stimulus/Turbo
- Symfony AssetMapper + importmap (pas de Webpack/Vite)

## Security Headers

CSP gérée par `SecurityHeadersListener`. La directive `script-src` doit inclure `data:` pour que les modules importmap (Stimulus, Turbo) se chargent correctement. Sans `data:`, les scripts sont bloqués et le JS ne s'exécute pas.

## Testing

PHPUnit 12 strict mode. Tests in `tests/` with `APP_ENV=test`.

Tester les classes importantes : agrégats, value objects, handlers, services domaine.

## Coding Standards

- **SOLID, DRY, DDD** — respecter la séparation des couches
- **Design patterns** : les utiliser quand pertinent (Strategy, Factory, Repository, etc.)
- **Pas d'over-engineering** : rester simple, pas d'abstraction prématurée
- **Nommage** : variables et méthodes explicites, pas d'ambiguïté
- **Pas de commentaires inutiles** : le code doit être auto-documenté. Supprimer les commentaires évidents
- **Code maintenable** : petit fichiers, responsabilité unique
- **Réponses concises** : ne montrer que les diffs, pas de reformulation inutile
- **Mettre à jour CLAUDE.md** au fil de l'avancement du projet
