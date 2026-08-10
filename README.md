# ProjectFlow — Client Project Tracker

A simple Client Project Tracker for a digital agency. Project managers can track
client projects, monitor progress, and manage priorities through a full CRUD
interface backed by a RESTful controller.

## Technology Choices

| Layer     | Choice                              | Why |
|-----------|-------------------------------------|-----|
| Backend   | **Laravel 12 (PHP 8.4)**            | Batteries-included framework: routing, validation, Eloquent, migrations, and a resourceful controller give REST CRUD with very little boilerplate. |
| Frontend  | **Vue 3 + Inertia.js v3**           | A single-page experience without building/consuming a separate API by hand. Inertia lets the Laravel controllers drive fully client-rendered Vue pages while keeping server-side routing and validation. |
| UI        | **Tailwind CSS + shadcn-vue (reka-ui)** | Accessible, consistent primitives (Select, Dialog, Badge, Button) already shipped with the starter kit. |
| Database  | **PostgreSQL**                      | Robust relational store; matches the project's existing configuration. |
| Auth      | **Laravel Fortify** (starter kit)   | Login / registration / 2FA / passkeys were already wired in; the app now uses **Login as the landing page**. |
| Tooling   | Laravel Wayfinder, Pint, PHPUnit    | Type-safe route helpers in TS, PSR-12 formatting, and feature tests. |

## Data Model

`projects` table:

| Column         | Type    | Notes |
|----------------|---------|-------|
| id             | bigint  | PK |
| client_name    | string  | required |
| project_name   | string  | required |
| description    | text    | nullable |
| status         | string  | enum: `Planning`, `In Progress`, `On Hold`, `Completed` |
| priority       | string  | enum: `Low`, `Medium`, `High` |
| start_date     | date    | nullable |
| due_date       | date    | nullable, must be `>=` start_date |
| timestamps     |         | |

`status` and `priority` are backed by PHP enums (`App\Enums\ProjectStatus`,
`App\Enums\ProjectPriority`) and cast on the model.

## API / Routes

Registered via `Route::resource('projects', ProjectController::class)` (behind
`auth` + `verified` middleware):

| Method | URI              | Action  | Purpose |
|--------|------------------|---------|---------|
| GET    | /projects        | index   | List (supports search / filter / sort) |
| GET    | /projects/create | create  | Create form |
| POST   | /projects        | store   | Create |
| GET    | /projects/{id}   | show    | Details |
| GET    | /projects/{id}/edit | edit | Edit form |
| PUT    | /projects/{id}   | update  | Update |
| DELETE | /projects/{id}   | destroy | Delete |

Because this is an Inertia application, these RESTful routes power the UI
directly — `index`/`show`/`create`/`edit` return Inertia pages while
`store`/`update`/`destroy` persist and redirect with a flash toast.

## Validation

Enforced in `StoreProjectRequest` (and inherited by `UpdateProjectRequest`):

- `client_name` — required
- `project_name` — required
- `status` — required, must be a valid `ProjectStatus`
- `priority` — required, must be a valid `ProjectPriority`
- `due_date` — when a start date is provided, must be `after_or_equal:start_date`
- Invalid requests return `422` with field-level error messages (rendered inline
  in the forms).

## Bonus Features Implemented

- **Search** by client or project name
- **Filter** by Status and by Priority
- **Sorting** on every column (toggle asc/desc)
- **Authentication** (login required for all project routes)
- **Unit/feature tests** (`tests/Feature/ProjectControllerTest.php`)

## Setup Instructions

### Prerequisites

- PHP 8.4, Composer
- Node.js 20+ and npm
- PostgreSQL

### 1. Install dependencies

```bash
composer install
npm install
```

### 2. Environment

```bash
cp .env.example .env   # if you don't already have a .env
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

The seeder loads the 12 sample projects from
`database/data/projects.json`.

```bash
php artisan migrate --seed
```

The bundled `DatabaseSeeder` also creates a test login:
`test@example.com`.

### 4. Run the application

```bash
composer run dev   # runs the PHP server, queue and Vite together
```

or run them separately:

```bash
php artisan serve
npm run dev
```

Open the app — you'll land on the **Login** page. Register or log in, then use
**Projects** in the sidebar.

## Running Tests

```bash
php artisan test
```

Tests use `RefreshDatabase`. The project's `phpunit.xml` targets an in-memory
SQLite database by default. If your PHP build does not include the
`pdo_sqlite` driver, point the suite at a PostgreSQL test database instead:

```bash
createdb project_flow_test   # once
DB_CONNECTION=pgsql DB_DATABASE=project_flow_test php artisan test
```

## Assumptions Made

- **Inertia counts as the REST layer.** The resourceful routes are genuinely
  RESTful (correct verbs/URIs); rather than duplicate a separate JSON API, the
  same controller serves the SPA. A standalone `/api` layer could be added if a
  non-browser consumer were required.
- **Authentication is desired.** The starter kit already provided auth, so all
  project routes require a logged-in, verified user, and the removed Welcome
  page is replaced by the Login screen as the landing page.
- **Dates are optional.** Only client name, project name, status, and priority
  are strictly required; start/due dates may be left blank. The due-date rule
  only applies when a start date is present.
- **Single shared workspace.** Projects are not scoped per-user (any
  authenticated user manages the same list), matching the "internal agency
  tool" framing.

## Technical Reflection

**Why this implementation approach?**
The repository was already a Laravel + Inertia + Vue starter kit with auth,
Tailwind, and a shadcn-vue component library in place. Building the tracker as a
resourceful Inertia controller let me reuse that foundation and deliver a
type-safe, server-validated CRUD app quickly, keeping one source of truth for
validation (Form Requests) instead of splitting logic across a separate API and
client.

**What tradeoffs did I make?**
I chose Inertia over a decoupled JSON API + client fetch layer. This is faster to
build and avoids state-sync boilerplate, at the cost of the endpoints not
returning JSON to external consumers. I also modeled status/priority as string
enums stored as strings (readable, matches the sample data) rather than
normalized lookup tables, which would be overkill for four/three fixed values.

**What would I improve with more time?**
Server-side pagination for the list, per-user ownership/authorization policies,
an activity log, optimistic UI updates, and a small component test suite for the
Vue pages. I'd also add a dedicated `pgsql_testing` connection in `phpunit.xml`
so tests run cleanly regardless of the available PDO drivers.

**Most challenging part?**
Integrating cleanly with the existing starter kit's conventions — the
attribute-based `#[Fillable]` models, the Inertia flash-toast helper, and
Wayfinder's generated route helpers — so the new feature felt native rather than
bolted on.

**Did I use AI tools during development?**
Yes — Claude (Claude Code). It was used to explore the existing starter-kit
conventions, scaffold the model/controller/requests/enums/seeder, write the Vue
pages and feature tests, and produce this documentation. All output was reviewed,
run, and verified (migrations, seeding, `npm run build`, and the passing test
suite) before finalizing.
