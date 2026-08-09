# Meal Planning

## Summary

Manages weekly meal plans, one per ISO week (Monday–Sunday).
Each plan can assign a main recipe and optional side recipes to any day.
Plans are created automatically on first access and persisted in MongoDB.

## Why It Matters

Provides the primary API for a client to set up and browse what is being cooked each day of the week.

## How It Works

A `WeekPlan` document is keyed by its Monday date (`weekStartDate`, format `YYYY-MM-DD`).
Each of the seven days holds an optional embedded `DayPlan` containing a main `RecipeRef` and a list of side `RecipeRef` objects.
When assigning a meal, the API fetches live recipe data from the cookbook API and stores a snapshot (`recipeId`, `name`, `image`) so the plan is independent of future cookbook changes.

`GET /api/v1/plan/current` finds or creates the plan for the current ISO week automatically.

## Where to Start

| Method | Path | Description |
|---|---|---|
| `GET` | `/api/v1/plan/current` | Get or create the current week plan |
| `GET` | `/api/v1/plan/{weekStartDate}` | Get a plan by Monday date (`YYYY-MM-DD`) |
| `PUT` | `/api/v1/plan/{weekStartDate}/{day}` | Assign a meal to a day |
| `DELETE` | `/api/v1/plan/{weekStartDate}/{day}` | Clear a day |

`{day}` must be one of: `monday`, `tuesday`, `wednesday`, `thursday`, `friday`, `saturday`, `sunday`.

**PUT body:**

```json
{
  "mainRecipeId": 42,
  "sideRecipeIds": [15, 23]
}
```

Returns the full updated week plan on success.
Returns `422` if a recipe ID is not found in the cookbook.

## Dependencies

- [Cookbook API](https://github.com/aussieveen/cookbook) — provides recipe data at assignment time.
- MongoDB — stores `WeekPlan` documents (collection managed by Doctrine MongoDB ODM).

---

<details>
<summary>Source Map</summary>

- [app/src/Controller/Api/MealPlanController.php](../../app/src/Controller/Api/MealPlanController.php) — API endpoints for plan CRUD
- [app/src/Document/WeekPlan.php](../../app/src/Document/WeekPlan.php) — root MongoDB document; owns seven embedded DayPlan fields
- [app/src/Document/DayPlan.php](../../app/src/Document/DayPlan.php) — embedded document for one day
- [app/src/Document/RecipeRef.php](../../app/src/Document/RecipeRef.php) — embedded recipe snapshot
- [app/src/Repository/WeekPlanRepository.php](../../app/src/Repository/WeekPlanRepository.php) — MongoDB queries and week-start-date helpers
- [app/src/Service/CookbookService.php](../../app/src/Service/CookbookService.php) — fetches and maps recipe data from the cookbook API
- [app/src/Client/CookbookClient.php](../../app/src/Client/CookbookClient.php) — HTTP client wrapper for the cookbook API

</details>

---

[Back to Features](README.md) | [Documentation Home](../README.md)
