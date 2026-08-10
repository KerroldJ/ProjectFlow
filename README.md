# ProjectFlow — Client Project Tracker

A Client Project Tracker for a digital agency. Project managers can track client
projects, monitor their progress, and manage priorities through a full CRUD
interface backed by a **JSON REST API** and a layered, DDD-style backend.

- **Backend:** Laravel 12 (PHP 8.4) — REST API + Repository Pattern / Service Layer
- **Frontend:** Vue 3 + Inertia.js v3 (SPA) + Tailwind CSS + shadcn-vue
- **Auth:** Laravel Sanctum (SPA / cookie) + Fortify
- **Database:** PostgreSQL

---

## Requirements Coverage

| Requirement | Status | Where |
|-------------|:------:|-------|
| Project model (id, client, project, description, status, priority, start/due date) | ✅ | `database/migrations/*_create_projects_table.php`, `App\Repository\Models\Project` |
| Status enum (Planning / In Progress / On Hold / Completed) | ✅ | `App\Domain\Project\Enums\ProjectStatus` |
| Priority enum (Low / Medium / High) | ✅ | `App\Domain\Project\Enums\ProjectPriority` |
| GET all projects | ✅ | `GET /api/projects` |
| GET single project | ✅ | `GET /api/projects/{id}` |
| Create project | ✅ | `POST /api/projects` |
| Update project | ✅ | `PUT /api/projects/{id}` |
| Delete project | ✅ | `DELETE /api/projects/{id}` |
| Frontend list / create / edit / delete | ✅ | `resources/js/pages/projects/Index.vue` + dialog |
| Validation + meaningful errors | ✅ | `StoreProjectRequest` (422 JSON) |
| **Bonus:** search, status filter, priority filter, sorting, pagination, authentication, tests | ✅ | see below |

> **Note on endpoint paths:** the spec lists `GET /projects` etc. Because the UI
> is a same-origin SPA, the JSON API is namespaced under `/api` (`/api/projects`)
> and the plain `/projects` route serves the SPA page. The verbs, resource, and
> semantics are exactly as required.

---

## Architecture (Repository Pattern + Service Layer / DDD)

Controllers never touch Eloquent directly. The dependency flow is:

```
Request (validation)
      │
Controller ─▶ Action (Service) ─▶ Domain contract ─▶ Eloquent Repository ─▶ Model
                     │
                     └── input & output shaped by a Concerns DTO
```

| Folder | Responsibility |
|--------|----------------|
| `app/Domain/{User,Project}/*Domain.php` | Domain repository **contracts** (interfaces), each `extends IRepository`. |
| `app/Domain/Project/Enums/` | Domain **enums** (`ProjectStatus`, `ProjectPriority`). |
| `app/Repository/IRepository.php` | Base repository interface (`all` / `find` / `store` / `update` / `delete`). |
| `app/Repository/Models/` | Eloquent **models** (`User`, `Project`). |
| `app/Repository/Eloquent/` | Repository **implementations** (`ProjectEloquentRepository`, `UserEloquentRepository`). |
| `app/Actions/ProjectService.php` | **Service layer** — business logic; depends only on the domain abstraction. |
| `app/Concerns/ProjectData.php` | **DTO** — carries validated input into the domain and shapes the JSON response. |
| `app/Http/Requests/StoreProjectRequest.php` | Validation. Enums are referenced **here only**, never in controllers. |
| `app/Providers/RepositoryServiceProvider.php` | Binds each `*Domain` interface to its Eloquent implementation. |

Swapping the data source (e.g. to an external API) means writing one class that
implements the domain contract and changing a single binding — controllers and
services are untouched. This keeps the app testable and scalable.

---

## Data Model

`projects` table:

| Column        | Type   | Notes |
|---------------|--------|-------|
| id            | bigint | PK |
| client_name   | string | required |
| project_name  | string | required |
| description   | text   | nullable |
| status        | string | enum: `Planning`, `In Progress`, `On Hold`, `Completed` |
| priority      | string | enum: `Low`, `Medium`, `High` |
| start_date    | date   | nullable |
| due_date      | date   | nullable, must be `>=` start_date |
| timestamps    |        | |

`status`/`priority` are backed by PHP enums (`App\Domain\Project\Enums\*`) and
cast on the model.

---

## REST API

Defined in `routes/api.php` via `Route::apiResource('projects', ...)` behind
`auth:sanctum` (Sanctum SPA / cookie auth):

| Method | URI                | Success | Body |
|--------|--------------------|:-------:|------|
| GET    | /api/projects      | `200`   | `{ data: [...], meta: { current_page, last_page, per_page, total, from, to } }` |
| GET    | /api/projects/{id} | `200`   | `{ data: {...} }` |
| POST   | /api/projects      | `201`   | `{ data: {...} }` |
| PUT    | /api/projects/{id} | `200`   | `{ data: {...} }` |
| DELETE | /api/projects/{id} | `204`   | — |

**Query params on index:** `search`, `status`, `priority`, `sort`, `direction`,
`page`, `per_page` (all handled server-side).

**Error handling:**

- `422 Unprocessable Entity` → `{ message, errors: { field: [...] } }` on validation failure
- `401 Unauthorized` → unauthenticated API request
- `404 Not Found` → unknown project id

The web route `GET /projects` renders the Inertia SPA shell; the Vue app fetches
its data from the API client-side. **Login is the landing page**; after login the
user lands directly on the Projects list.

---

## Validation

`StoreProjectRequest` (inherited by `UpdateProjectRequest`):

- `client_name` — required
- `project_name` — required
- `status` — required, must be a valid `ProjectStatus`
- `priority` — required, must be a valid `ProjectPriority`
- `due_date` — when a start date is provided, must be `after_or_equal:start_date`
  (custom message: *"The due date cannot be earlier than the start date."*)

Invalid requests return `422` with field-level errors; the SPA maps them inline
on the form fields.

---

## Bonus Features Implemented

- **Search** by client or project name (server-side, runs on **Enter**)
- **Filter** by Status and by Priority (server-side)
- **Sorting** on every column (toggle asc/desc, server-side)
- **Pagination** (server-side, with Prev/Next controls)
- **Authentication** — every project route requires a logged-in, verified user
- **Unit / feature tests** — 52 PHPUnit feature tests
- **UX** — success toasts on create/update/delete, loading skeletons, modal create/edit

*Not included:* Docker setup and deployment (out of scope for this submission).

---

## Setup Instructions

### Prerequisites

- PHP 8.4 + Composer
- Node.js 20+ and npm
- PostgreSQL

### 1. Install dependencies

```bash
composer install
npm install
```

### 2. Environment

```bash
cp .env.example .env
php artisan key:generate
```

Configure the database in `.env`:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=project_flow_db
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

### 3. Migrate & seed

`DatabaseSeeder` runs `UserSeeder` (a known login account) and `ProjectSeeder`
(the 12 sample projects from `database/data/projects.json`):

```bash
php artisan migrate --seed
```

Seeded login credentials:

- **Email:** `admin@projectflow.test`
- **Password:** `password`

### 4. Run the application

```bash
composer run dev      # PHP server + queue + Vite together
```

or run them separately:

```bash
php artisan serve
npm run dev
```

Open the app on the served URL (e.g. `http://127.0.0.1:8000`). You'll land on the
**Login** page; after logging in you go straight to the **Projects** list.

> Sanctum SPA auth uses same-origin cookies. Access the app on a stateful domain
> (the defaults include `localhost:8000` / `127.0.0.1:8000`).

---

## Running Tests

```bash
php artisan test
```

Tests use `RefreshDatabase`. `phpunit.xml` targets an in-memory SQLite database by
default. If your PHP build lacks the `pdo_sqlite` driver, run against a PostgreSQL
test database instead:

```bash
createdb project_flow_test   # once
DB_CONNECTION=pgsql DB_DATABASE=project_flow_test php artisan test
```

The suite covers the API CRUD, all validation rules, search/filter, pagination,
auth boundaries (401/redirect), and the landing-page behaviour.

---

## Assumptions Made

- **Dedicated JSON REST API.** A real `/api` layer (not just Inertia responses)
  is provided so the endpoints are consumable by any client; the Vue SPA is one
  consumer of it.
- **Cookie-based (SPA) auth.** Sanctum stateful auth reuses the Fortify session,
  so the same login protects both the page and the API without token handling in
  the browser.
- **Dates are optional.** Only client name, project name, status and priority are
  required; the due-date rule only applies when a start date is present.
- **Single shared workspace.** Projects are not scoped per-user — any
  authenticated user manages the same list (internal agency tool).

---

## Technical Reflection

**Why did I choose this implementation approach?**
The task explicitly asks for a REST API, so I built a real JSON API and layered
the backend with the **Repository Pattern + Service Layer (DDD/Clean
Architecture)**: controllers stay thin, business logic lives in an Action/Service,
data access sits behind a domain contract, and a DTO shapes I/O. This maximises
separation of concerns, testability, and scalability — the qualities the rubric
weights most (Architecture + Code Quality). The frontend is a Vue 3 + Inertia SPA
that consumes the API, authenticated via Sanctum's cookie-based SPA mode so the
browser flow stays simple.

**What tradeoffs did I make?**
The layered architecture adds indirection/boilerplate that a small CRUD doesn't
strictly need — a deliberate trade to demonstrate a structure that scales.
Sanctum SPA (cookie) auth keeps the client simple but ties the API to same-origin
usage; a token guard could be added for third-party consumers. Fetching list data
client-side (rather than via Inertia props) introduces a brief loading state,
mitigated with skeletons.

**What would I improve with more time?**
Per-user ownership + authorization policies, cursor pagination with proper
indexes, optimistic UI updates, component-level tests (Vitest) for the Vue pages,
OpenAPI documentation and API rate limiting, and a Docker + CI setup for one-command
onboarding and automated test runs.

**What was the most challenging part?**
Refactoring the Laravel starter kit into clean DDD layers — especially moving the
`User` model out of `app/Models` (which touches Fortify, the auth config, factory
resolution, and every test) and wiring Sanctum SPA CSRF for the `fetch` / `useHttp`
client — all while keeping the full 52-test suite green.

**Did I use AI tools during development?**
Yes — **Claude (Claude Code)**. It was used to explore the starter-kit
conventions, scaffold the model/migration/requests/enums/seeders, refactor the
backend into the Repository/Service/DDD architecture, build the Vue pages and the
feature tests, and write this documentation. Every change was reviewed and
verified by running Pint, `npm run build`, and the passing test suite before
finalizing.
