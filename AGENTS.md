# AGENTS.md — Execution Plan

## MVP Build Order (per PRD + ARCHITECTURE.md)

### Phase 1 — Foundation (Auth, Dashboard, Leads, Notes, Activities)

| # | Step | Artifacts |
|---|---|---|
| 1.1 | Scaffold project | Laravel 12 + Breeze (Inertia/Vue stack), TypeScript, TailwindCSS, PostgreSQL config |
| 1.2 | Auth | Breeze default — login, register, password reset |
| 1.3 | Dashboard | Backend: controller fetching stats. Frontend: widget layout with stat cards |
| 1.4 | Leads — database + backend | Migration, `Lead` model, `LeadStatus` enum, resource controller, form requests, `LeadService`, `LeadPolicy` |
| 1.5 | Leads — frontend | `Pages/Leads/Index.vue` (table + filters + search), `Create.vue`, `Edit.vue`, `Show.vue` |
| 1.6 | Notes — backend | Polymorphic migration, `Note` model, controller, service, policy |
| 1.7 | Notes — frontend | Reusable notes component embedded in Lead/Client/Project Show pages |
| 1.8 | Activities — backend | Migration, `Activity` model, `ActivityService`, auto-log on model events |
| 1.9 | Activities — frontend | Activity feed component on Dashboard + detail pages |

### Phase 2 — Data Operations (CSV, Lead Sources)

| # | Step | Artifacts |
|---|---|---|
| 2.1 | CSV Import — backend | Import action, queue job, validation, duplicate detection |
| 2.2 | CSV Import — frontend | Upload UI with progress indicator |
| 2.3 | CSV Export — backend | Export action + job, filtered export |
| 2.4 | CSV Export — frontend | Export button on Leads Index |
| 2.5 | Lead Sources — backend | Migration, `LeadSource` model, `SourceType` enum, CRUD service, scraper job skeleton |
| 2.6 | Lead Sources — frontend | `Index.vue`, `Create.vue`, `Edit.vue`, manual run button |

### Phase 2.5 — Email Outreach

| # | Step | Artifacts |
|---|---|---|
| 2.5.1 | Email — backend | Resend SDK integration, `EmailService`, `SendEmailAction`, template variables, email history model |
| 2.5.2 | Email — frontend | Compose modal, template picker, email history on Lead/Client Show |

### Phase 3 — Client Management (Clients, Lead Conversion)

| # | Step | Artifacts |
|---|---|---|
| 3.1 | Clients — backend | Migration, `Client` model, resource controller, form requests, service, policy |
| 3.2 | Clients — frontend | `Index.vue`, `Create.vue`, `Edit.vue`, `Show.vue` (tabs for Projects/Meetings/Documents) |
| 3.3 | Lead Conversion — backend | `ConvertLeadToClientAction` — creates Client, copies notes/attachments, archives Lead, logs activity |
| 3.4 | Lead Conversion — frontend | "Convert to Client" button on Lead Show, confirmation dialog, redirect |

### Phase 4 — Project Management (Projects, Documents)

| # | Step | Artifacts |
|---|---|---|
| 4.1 | Projects — backend | Migration, `Project` model, `ProjectStatus` enum, resource controller, service, policy |
| 4.2 | Projects — frontend | `Index.vue` (status filter), `Show.vue` (details, progress, docs/meetings) |
| 4.3 | Documents — backend | Migration, `Document` model, R2 integration, `UploadDocumentAction`, signed URL generation |
| 4.4 | Documents — frontend | File uploader component, document list with download/delete on Client and Project Show |

### Phase 5 — Scheduling (Meetings)

| # | Step | Artifacts |
|---|---|---|
| 5.1 | Meetings — backend | Migration, `Meeting` model, controller, service, `CreateMeetingAction` (Google Calendar + Meet) |
| 5.2 | Meetings — frontend | Create/edit form, calendar list on Client Show, upcoming meetings Dashboard widget |

### Phase 6 — Notifications

| # | Step | Artifacts |
|---|---|---|
| 6.1 | Notifications — backend | Laravel Notification setup (database channel), notification classes for key events |
| 6.2 | Notifications — frontend | NotificationBell component, dropdown, in-app index page, mark-as-read |

## PostgreSQL Setup (local dev)

- User-owned cluster at `docker/pgdata/`, started via `bash bin/pg-start.sh`, stopped via `bash bin/pg-stop.sh`.
- Runs on port **5433** with Unix socket at `/run/user/1000`, `trust` auth.
- Database: `agencyos`, username: `raviensewpal`.
- Must use `bash artisan.sh` (or `bash bin/php.sh`) for any PHP command — these set `LD_LIBRARY_PATH` for `libpq.so.5` and `PHP_INI_SCAN_DIR` for `pdo_pgsql.so`.
- `composer.json` scripts `setup`, `dev`, `test` all use `bash artisan.sh` internally.
- `pdo_pgsql.so` and `libpq.so.5` live in `docker/8.4/ext-override/`.
- Old SQLite database archived to `database/database.sqlite.bak`.
