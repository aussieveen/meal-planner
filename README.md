# Meal Planner

A Symfony API for planning weekly meals.
It fetches recipe data from a separate [cookbook API](https://github.com/aussieveen/cookbook) and stores weekly meal plans in MongoDB.

## Purpose

Provides a REST API and admin UI for managing weekly meal plans.
Each plan covers one ISO week (Monday–Sunday) and can assign a main recipe plus optional side recipes to each day.
A shopping list endpoint collects all recipe IDs from upcoming planned (and un-shopped) days.

## Local Development

```bash
cp .env.example .env
# Set APP_PORT and any other values in .env
docker compose up -d
```

Run checks inside the app container or with PHP 8.5+ available locally:

```bash
cd app
composer run-phpcs      # code style (PHP_CodeSniffer)
composer run-phpmd      # mess detection
composer run-phplint    # syntax lint
composer run-phpunit    # unit and integration tests
composer run-tests      # all of the above in sequence
```

## Documentation

- [Documentation home](docs/README.md)
- [Features](docs/features/README.md)
- [Architecture](docs/architecture/README.md)

## Useful Links

- [Cookbook API](https://github.com/aussieveen/cookbook) — sibling service that provides recipe data
- Swagger UI: `http://localhost:{APP_PORT}/api/doc`
- Sonata admin UI: `http://localhost:{APP_PORT}/admin`

## Configuration

| Variable | Description | Default |
|---|---|---|
| `APP_PORT` | Port to expose the app on | `10001` |
| `COOKBOOK_API_URL` | URL of the cookbook API | `http://localhost:10000` |

> **Docker users:** if the cookbook runs in a separate Compose project, set `COOKBOOK_API_URL=http://host.docker.internal:10000`.
