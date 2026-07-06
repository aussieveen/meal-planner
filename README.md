# Meal Planner

A Symfony API for planning weekly meals. Fetches recipe data from a separate [cookbook API](https://github.com/aussieveen/cookbook) and stores weekly meal plans in MongoDB.

## Requirements

- Docker & Docker Compose
- A running [cookbook API](https://github.com/aussieveen/cookbook) (default: `localhost:10000`)

## Setup

```bash
cp .env.example .env
# Set APP_PORT and any other values in .env
docker compose up -d
```

## Configuration

| Variable | Description | Default |
|---|---|---|
| `APP_PORT` | Port to expose the app on | `10001` |
| `COOKBOOK_API_URL` | URL of the cookbook API | `http://localhost:10000` |

> **Docker users:** if the cookbook runs in a separate compose project, set `COOKBOOK_API_URL=http://host.docker.internal:10000`.

## API

Swagger UI available at `http://localhost:{APP_PORT}/api/doc`.

| Method | Path | Description |
|---|---|---|
| `GET` | `/api/v1/plan/current` | Get or create the current week plan |
| `GET` | `/api/v1/plan/{weekStartDate}` | Get a plan by date (YYYY-MM-DD) |
| `PUT` | `/api/v1/plan/{weekStartDate}/{day}` | Assign a meal to a day |
| `DELETE` | `/api/v1/plan/{weekStartDate}/{day}` | Clear a day |

## Admin

Sonata admin UI at `http://localhost:{APP_PORT}/admin`.
