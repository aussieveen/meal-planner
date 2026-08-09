# AGENTS.md

## Repo Brief

Symfony 8 / PHP 8.5 REST API for weekly meal planning.
Stores `WeekPlan` documents in MongoDB (Doctrine ODM).
Fetches recipe data from the sibling [cookbook API](https://github.com/aussieveen/cookbook) via `CookbookClient`.
Exposes a JSON API under `/api/v1/plan/` and a Sonata admin UI under `/admin`.
Runs locally via Docker Compose; CI publishes a production image to GHCR on push to `main`.

## Read First

- [README.md](README.md)
- [Documentation home](docs/README.md)

## Working Rules

- Preserve existing behaviour unless the user explicitly asks for a functional change.
- Follow the repo's existing patterns before introducing new abstractions.
- The `WeekPlan` document owns days as embedded `DayPlan` objects; never add a separate collection for days.
- Recipe data is always fetched from the cookbook API at assignment time and stored as an embedded `RecipeRef` snapshot; do not add live recipe lookups at read time.
- Keep documentation updates in sync with meaningful feature, architecture, workflow, command, setup, troubleshooting, or limitation changes.
- Use British English spelling in documentation.

## Commands

### Safe Checks

Run these inside the app container or with PHP 8.5+ locally (`cd app` first):

```bash
composer run-phpcs      # PHP_CodeSniffer — code style
composer run-phpmd      # PHP Mess Detector — src and tests
composer run-phplint    # syntax lint
composer run-phpunit    # PHPUnit test suite
composer run-tests      # all checks in sequence (phpcs + phpunit + phpmd + phplint)
```

### Service Commands

```bash
docker compose up -d    # build dev image (if not present), start app, nginx, and MongoDB containers
```

The app volume mounts `./app` into the container, so source changes are reflected immediately without a rebuild.

### Destructive Or Reset Commands

```bash
docker compose down             # stops and removes containers; add -v to also remove the db_data volume (deletes local MongoDB data)
composer run-coverage           # runs PHPUnit with coverage; writes a coverage/ directory under app/
```

## Documentation Maintenance

When a significant feature, architecture, workflow, command, setup, troubleshooting, or limitation change is ready for PR, suggest re-running the docs-maintainer skill so `README.md`, `AGENTS.md`, and `docs/` stay in sync.
