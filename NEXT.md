# Implementation Plan

## Task 1 — Ticket Creation Client/Project Picker

**Problem**: Creating a ticket requires manually entering a UUID for the related client/project.

### Steps

| # | Step | Files |
|---|---|---|
| 1.1 | Add `searchEntities` method to `TicketController` — accepts `?type=client\|project&q=searchterm`, returns JSON `[{id, label, type}]` | `app/Http/Controllers/TicketController.php`, `routes/web.php` |
| 1.2 | Create `EntityPicker.vue` — async search with debounce, fetches search API, shows type-ahead dropdown | `resources/js/Components/EntityPicker.vue` |
| 1.3 | Update `Tickets/Create.vue` — replace UUID text input + type dropdown with `EntityPicker` | `resources/js/Pages/Tickets/Create.vue` |
| 1.4 | Update `Tickets/Edit.vue` — show current association and allow changing it (optionally) | `resources/js/Pages/Tickets/Edit.vue` |
| 1.5 | Update `StoreTicketRequest` — add `exists:clients,id` / `exists:projects,id` validation | `app/Http/Requests/Ticket/StoreTicketRequest.php` |
| 1.6 | Update `UpdateTicketRequest` — same validation for edit | `app/Http/Requests/Ticket/UpdateTicketRequest.php` |

---

## Task 2 — Provider Connection via Settings Page

**Problem**: Zoom/Teams/Google Calendar credentials can only be set via `.env` — no UI.

### Steps

| # | Step | Files |
|---|---|---|
| 2.1 | Create `user_settings` migration — `user_id` (FK), `key` (string), `value` (text, nullable), unique on `[user_id, key]` | `database/migrations/..._create_user_settings_table.php` |
| 2.2 | Create `UserSetting` model — `belongsTo(User)`, no UUID trait, fillable `key`/`value` | `app/Models/UserSetting.php` |
| 2.3 | Add `settings` relationship on `User` model | `app/Models/User.php` |
| 2.4 | Create `SettingsController` — `edit()` loads all provider keys from DB (fallback to config), `update()` saves to DB | `app/Http/Controllers/SettingsController.php` |
| 2.5 | Create route — `GET /settings/integrations` + `POST /settings/integrations` | `routes/web.php` |
| 2.6 | Create `Settings/Integrations.vue` — 3 sections (Google Calendar, Zoom, Teams) with toggle + credential fields | `resources/js/Pages/Settings/Integrations.vue` |
| 2.7 | Add "Integrations" link to user dropdown in nav | `resources/js/Layouts/AuthenticatedLayout.vue` |
| 2.8 | Refactor providers — `ZoomMeetingProvider`, `TeamsMeetingProvider`, `GoogleCalendarService` read from `User::first()->settings()` falling back to `config()` | `app/Services/ZoomMeetingProvider.php`, `app/Services/TeamsMeetingProvider.php`, `app/Services/GoogleCalendarService.php` |

---

## Task 3 — AgencyOS Landing Page

**Problem**: Root `/` shows generic Laravel Welcome page.

### Steps

| # | Step | Files |
|---|---|---|
| 3.1 | Replace `Welcome.vue` — header with logo + login/register, hero section, feature cards, footer | `resources/js/Pages/Welcome.vue` |

---

## Implementation Order

1. **Task 3** — Landing page (pure frontend, zero dependencies)
2. **Task 1** — Ticket picker (new component + endpoint + form update)
3. **Task 2** — Settings page (migration → model → controller → UI → provider refactor)
