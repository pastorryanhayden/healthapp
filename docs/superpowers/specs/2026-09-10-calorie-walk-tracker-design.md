# Calorie, walk, and weigh-in tracker

Personal Laravel API for logging food, walks, and Friday weigh-ins. Flutter is the client. A thin Blade page exists only to smoke-test the backend.

## Goals

- Log what was eaten. Known foods skip AI; unknown foods are estimated by a Laravel AI SDK agent (xAI/Grok) and saved in the catalog.
- Show calories eaten, remaining, and eating pass/fail against a **2000** calorie cap. Coffee is not logged (those ~200 calories are outside the cap).
- Log walks as miles that add up. Goal is **4.0 miles** per day.
- Log a weigh-in in pounds (Fridays). Keep history and a this-week vs last-week delta.
- Calendar: eating and walking each get pass or fail per day. No food log is an eating fail — the point of the app is to log.
- Every write has a JSON API route for Flutter. No authentication.

## Non-goals

- Accounts, multi-user, or API tokens.
- Special-casing coffee in code.
- Charts beyond a simple weigh-in delta.
- GPS, step counters, or barcode scanning.
- Editing a food log in place (delete and re-add).

## Time and numbers

- Timezone: `America/Chicago`.
- Dates on the API: `YYYY-MM-DD` in that timezone.
- “Today” is `now` in that timezone.
- Calories: integers.
- Miles and pounds: decimals, stored with two places.
- Eating remaining: `2000 - eaten` (may be negative).

## Domain

See `CONTEXT.md`. Day status is computed:

| Signal | Pass | Fail |
| --- | --- | --- |
| Eating | ≥1 food log **and** eaten ≤ 2000 | no food log **or** eaten > 2000 |
| Walking | miles ≥ 4.0 | miles < 4.0 (including 0) |

Weigh-ins do not pass/fail. Calendar marks `weigh_in: true|false` for that date.

## Data

**foods**

- `id`
- `name` — display name (from AI, or the typed text)
- `normalized_name` — unique catalog key
- `calories` — integer > 0
- timestamps

**food_logs**

- `id`
- `food_id` (restrict on delete)
- `date` (date)
- `calories` — snapshot, integer
- `input` — original typed text
- timestamps

Deleting a food log does not delete the Food.

**walks**

- `id`
- `date`
- `miles` — decimal(6,2) > 0
- timestamps

**weigh_ins**

- `id`
- `date` — unique
- `pounds` — decimal(6,2) > 0
- timestamps

Posting a weigh-in for an existing date updates that row.

## Food / AI flow

1. `POST /api/food-logs` with `{ "input": "...", "date"?: "YYYY-MM-DD" }`.
2. Normalize input: trim, collapse whitespace, lowercase. Empty after normalize → 422.
3. If a Food exists for that `normalized_name`, use it. Do not call AI.
4. Else prompt `App\Ai\Agents\CalorieEstimator` (Laravel AI SDK, xAI, model `grok-4-5` / current Grok 4.5 alias) with structured output `{ name: string, calories: integer }`.
5. Calories from AI must be an integer ≥ 1. Invalid or provider failure → 502, nothing stored.
6. Create Food (`name` from agent, `normalized_name` from input, `calories` from agent).
7. Create Food log for the date with calorie snapshot and original input.
8. Return `{ food_log, day }` where `day` is the day summary.

A catalog Food with calories < 1 is treated as a miss and re-estimated.

## API

No auth. JSON. Validation failures are 422. Missing models are 404.

Day summary (used by today, days, and food-log responses):

```json
{
  "date": "2026-09-10",
  "calories_eaten": 650,
  "calories_goal": 2000,
  "calories_remaining": 1350,
  "eating": "fail",
  "miles_walked": 1.2,
  "miles_goal": 4,
  "walking": "fail",
  "food_logs": [
    {
      "id": 1,
      "input": "oatmeal with banana",
      "name": "Oatmeal with banana",
      "calories": 350,
      "created_at": "2026-09-10T08:15:00-05:00"
    }
  ],
  "walks": [
    {
      "id": 1,
      "miles": 1.2,
      "created_at": "2026-09-10T07:40:00-05:00"
    }
  ]
}
```

| Method | Path | Body | Result |
| --- | --- | --- | --- |
| GET | `/api/today` | | Day summary for today |
| GET | `/api/days/{date}` | | Day summary. Invalid date → 404 |
| POST | `/api/food-logs` | `input` required, `date` optional | `{ food_log, day }` |
| DELETE | `/api/food-logs/{id}` | | `{ day }` after delete |
| GET | `/api/foods` | | `{ data: [{ id, name, normalized_name, calories }] }` |
| POST | `/api/walks` | `miles` required, `date` optional | `{ walk, day }` |
| DELETE | `/api/walks/{id}` | | `{ day }` |
| POST | `/api/weigh-ins` | `pounds` required, `date` optional | `{ weigh_in, history }` (upsert by date) |
| GET | `/api/weigh-ins` | | `{ data: [... newest first], this_week: n\|null, last_week: n\|null, delta: n\|null }` |
| GET | `/api/calendar?month=2026-09` | | `{ month, days: [{ date, eating, walking, weigh_in }] }` for every date in that month. Missing `month` uses current month. Invalid month → 422 |

`this_week` / `last_week` are the weigh-in pounds on the most recent Friday on or before today, and the Friday 7 days before that. `delta` is this_week − last_week when both exist.

Future calendar days use the same pass/fail rules (so they show fail until logged).

## Errors

- Empty `input`, `miles` ≤ 0, `pounds` ≤ 0, malformed date → 422.
- AI failure or unusable structured output → 502 `{ "message": "Could not estimate calories." }`. Catalog and logs unchanged.
- Unknown id → 404.

## Web smoke page

`GET /` renders today’s summary, forms to add food / walk / weigh-in, and the current month calendar (green check = pass, red x = fail, weigh-in marked). Forms POST to web routes that call the same application services as the API, then redirect home. Not a second domain.

## Testing

Pest/PHPUnit feature tests, `RefreshDatabase`, AI faked via `CalorieEstimator::fake()`.

Must cover:

- Empty day: eating fail, walking fail, remaining 2000.
- Catalog hit: agent never prompted; log uses stored calories.
- Catalog miss: agent prompted; Food created; log appended.
- After a log of 650: remaining 1350; eating pass (logged and under cap).
- Log that pushes eaten over 2000: eating fail.
- Walks 1.2 + 2.8 = 4.0: walking pass.
- Weigh-in twice on one date: one row, latest pounds.
- Calendar includes every day of the month with eating/walking/weigh_in.
- 422 on empty food input; 404 on delete missing walk.
- Agent exception → 502, no food log.

## Stack

- Laravel 13, SQLite, no auth.
- `laravel/ai` with xAI (`XAI_API_KEY`), Grok 4.5.
- Hotwire/Stimulus + daisyUI only on the smoke page.
- Config: `config/health.php` (`timezone`, `calories_goal`, `miles_goal`).
