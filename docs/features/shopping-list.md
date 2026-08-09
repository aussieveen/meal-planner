# Shopping List

## Summary

Collects all planned recipe IDs from a given date onwards, excluding days already marked as shopped.
Supports marking a set of days as shopped so they are excluded from future shopping list requests.

## Why It Matters

Allows a client to build a shopping list by fetching all recipe IDs from upcoming planned meals in one request, avoiding manual iteration over individual week plans.

## How It Works

`GET /api/v1/plan/recipe-ids` queries all `WeekPlan` documents from the Monday of the supplied date onwards.
For each plan it iterates days in order (Monday–Sunday), skips days before the `from` date, and skips days already marked as shopped.
It returns a flat, ordered list of recipe IDs — main recipe first, then sides, for each qualifying day.

`PATCH /api/v1/plan/shopped` marks all days on or after the `from` date as shopped.
Days marked as shopped are excluded from future `recipe-ids` responses.

## Where to Start

| Method | Path | Description |
|---|---|---|
| `GET` | `/api/v1/plan/recipe-ids?from=YYYY-MM-DD` | Flat ordered list of recipe IDs from `from` (defaults to today) |
| `PATCH` | `/api/v1/plan/shopped?from=YYYY-MM-DD` | Mark all days from `from` onwards as shopped; returns `204 No Content` |

The `from` parameter is a date string in `YYYY-MM-DD` format.
The response from `recipe-ids` is:

```json
{ "recipeIds": [42, 15, 23, 7] }
```

## Dependencies

- MongoDB — persists the `shopped` flag per `DayPlan`.

---

<details>
<summary>Source Map</summary>

- [app/src/Controller/Api/MealPlanController.php](../../app/src/Controller/Api/MealPlanController.php) — `recipeIds` and `markShopped` actions
- [app/src/Repository/WeekPlanRepository.php](../../app/src/Repository/WeekPlanRepository.php) — `findFromDate` query
- [app/src/Document/DayPlan.php](../../app/src/Document/DayPlan.php) — `shopped` flag

</details>

---

[Back to Features](README.md) | [Documentation Home](../README.md)
