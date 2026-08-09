# Architecture Overview

## Summary

meal-planner is a Symfony 8 application running in Docker Compose.
It stores weekly meal plan documents in MongoDB and delegates recipe data to the sibling cookbook API.

## Runtime Structure

```
Client / UI
    │
    ▼
Nginx (port APP_PORT)
    │
    ▼
PHP-FPM (Symfony 8 app)
    ├── /api/v1/plan/*  → MealPlanController
    │       └── CookbookService → CookbookClient → Cookbook API
    └── /admin/*        → Sonata Admin (WeekPlanAdmin)
    │
    ▼
MongoDB 7 (meal-planner collection)
```

## Data Model

All data lives in a single MongoDB collection managed by Doctrine MongoDB ODM.

| Document | Type | Role |
|---|---|---|
| `WeekPlan` | Root document | One per ISO week; keyed by `weekStartDate` (Monday, `YYYY-MM-DD`); unique index enforced. |
| `DayPlan` | Embedded in `WeekPlan` | One per day; holds a main recipe ref, zero or more side recipe refs, and a `shopped` flag. |
| `RecipeRef` | Embedded in `DayPlan` | Snapshot of a cookbook recipe: `recipeId`, `name`, `image`. Stored at assignment time; not updated if the cookbook changes. |

## External Dependencies

| Dependency | Role | Config |
|---|---|---|
| [Cookbook API](https://github.com/aussieveen/cookbook) | Provides recipe data at meal assignment time | `COOKBOOK_API_URL` in `.env` |
| MongoDB 7 | Persists meal plans | Credentials in `docker-compose.yaml` (dev only) |

## CI / CD

On push to `main`, GitHub Actions runs the full test suite (phpcs, phpunit, phpmd, phplint) against PHP 8.5 and builds and pushes a production Docker image to GHCR (`ghcr.io/{owner}/meal-planner:latest` and a date-stamped tag).
The production deployment configuration lives outside this repository.

## Key Decisions

- **Recipe snapshot**: recipe data is copied into `RecipeRef` at assignment time rather than looked up live, so meal plans remain readable even if the cookbook API is unavailable or a recipe is deleted.
- **Automatic week creation**: `GET /api/v1/plan/current` creates the current week's plan on first access; no separate creation step is needed.
- **`weekStartDate` uniqueness**: enforced at the MongoDB index level, not just application code.

---

<details>
<summary>Source Map</summary>

- [app/src/Controller/Api/MealPlanController.php](../../app/src/Controller/Api/MealPlanController.php) — all API routes
- [app/src/Document/WeekPlan.php](../../app/src/Document/WeekPlan.php) — root document and ODM mapping
- [app/src/Document/DayPlan.php](../../app/src/Document/DayPlan.php) — embedded day document
- [app/src/Document/RecipeRef.php](../../app/src/Document/RecipeRef.php) — embedded recipe snapshot
- [app/src/Repository/WeekPlanRepository.php](../../app/src/Repository/WeekPlanRepository.php) — MongoDB queries
- [app/src/Client/CookbookClient.php](../../app/src/Client/CookbookClient.php) — HTTP client for cookbook API
- [app/src/Service/CookbookService.php](../../app/src/Service/CookbookService.php) — recipe fetch and mapping
- [app/src/Admin/WeekPlanAdmin.php](../../app/src/Admin/WeekPlanAdmin.php) — Sonata admin configuration
- [docker-compose.yaml](../../docker-compose.yaml) — local service topology
- [docker/php/Dockerfile.dev](../../docker/php/Dockerfile.dev) — dev container image
- [docker/php/Dockerfile.prod](../../docker/php/Dockerfile.prod) — production container image

</details>

---

[Back to Architecture](README.md) | [Documentation Home](../README.md)
