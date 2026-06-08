# PlazaOS SME Expansion: AI Coding Agent Implementation Guide

## 🧠 Core Directives & Constraints
**Project:** PlazaOS (Transitioning to a Team Platform for SMEs)
**Target Audience:** Small teams (1 to 20 members maximum). 
**The Golden Rule (The "15-Year-Old Test"):** The UI/UX must be so intuitive that a 15-year-old could use it without any training. 
**Anti-Patterns:** DO NOT build enterprise features. No complex RBAC, no Gantt charts, no deep hierarchical permissions, no steep learning curves.

**Tech Stack:** Laravel 12, Vue 3, Inertia.js, TypeScript, Tailwind CSS, PostgreSQL 16.

---

## 🚀 Phase 1: Multi-User Foundation

### Feature 1: Multi-User Workspaces & Simple Permissions
**Goal:** Allow the owner to invite up to 19 team members. Keep permissions dead simple.
**15-Year-Old Test:** "I click 'Invite', type an email, and my friend gets in. They see our stuff, I see my stuff."

#### Actionable Steps:
1.  **Database:** 
    *   Run `php artisan make:model Workspace -m`. Add `name` (string) and `owner_id` (UUID foreign key to `users`).
    *   Add `workspace_id` (UUID, nullable) to `users`, `leads`, `clients`, `projects`, `documents`, `meetings`, and `notes` tables.
    *   Add `role` enum (`owner`, `member`) to the `users` table, defaulting to `member`.
2.  **Backend (Laravel):**
    *   Create a `BelongsToWorkspace` trait. Apply it to all models that need scoping.
    *   In the trait, add a `bootBelongsToWorkspace` method that automatically applies a global scope: `static::addGlobalScope('workspace', fn ($query) => $query->where('workspace_id', auth()->user()->workspace_id));`.
    *   Create an `InviteUser` Action. It generates a secure, single-use magic link and emails it.
3.  **Frontend (Vue):**
    *   Add a "Team" page in settings. Show a list of members with a big "Invite Member" button.
    *   **DO NOT** build a permissions matrix. Members can edit anything in the workspace; Owners can delete the workspace and manage billing/settings.

### Feature 2: Real-Time Notifications
**Goal:** Let the team know when something important happens without refreshing the page.
**15-Year-Old Test:** "A little red dot appears on the bell, I click it, and I see 'John assigned you a task'."

#### Actionable Steps:
1.  **Database:** Use Laravel's built-in `notifications` table (`php artisan notifications:table`).
2.  **Backend (Laravel):**
    *   Install Laravel Reverb (or configure Pusher) for WebSockets.
    *   Create simple Notification classes: `TaskAssignedNotification`, `ProjectCompletedNotification`.
    *   Trigger these notifications inside your existing Action classes (e.g., inside `AssignTaskAction`).
3.  **Frontend (Vue):**
    *   Create a `<NotificationBell>` component in the main layout header.
    *   Use Laravel Echo to listen to the private user channel: `Echo.private('App.Models.User.' + userId).notification(...)`.
    *   When a notification arrives, increment a red badge counter on the bell.
    *   Clicking the bell opens a simple dropdown list of recent notifications. Clicking a notification marks it as read and redirects to the resource.

---

## 🤝 Phase 2: Team Collaboration

### Feature 3: In-App Comments & Mentions
**Goal:** Stop using email threads for quick questions about a specific lead or project.
**15-Year-Old Test:** "I type '@Sarah can you check this?' at the bottom of the project page, and she gets a notification."

#### Actionable Steps:
1.  **Database:** 
    *   Run `php artisan make:model Comment -m`. 
    *   Add `body` (text), `user_id` (UUID), `commentable_type` (string), `commentable_id` (UUID).
2.  **Backend (Laravel):**
    *   Make `Lead`, `Client`, and `Project` models `MorphMany` comments.
    *   Create a `StoreComment` Action. Parse the `body` for `@mentions` (using a simple regex) and trigger a notification for the mentioned user.
3.  **Frontend (Vue):**
    *   Create a `<CommentSection>` component. 
    *   Place it at the bottom of the `Show` pages for Leads, Clients, and Projects.
    *   UI: A simple textarea with a "Post" button. Below it, render comments as a chat feed (Avatar on the left, Name + Time + Text on the right).
    *   **DO NOT** build nested/threaded replies. Keep it flat and simple.

### Feature 4: Global Search (Cmd+K)
**Goal:** Find anything instantly without navigating through menus.
**15-Year-Old Test:** "I press Cmd+K, type 'Acme', and click the Acme project."

#### Actionable Steps:
1.  **Backend (Laravel):**
    *   Create a `SearchController` with an `index` method.
    *   Accept a `query` parameter. 
    *   Query `Lead`, `Client`, and `Project` models using `where('name', 'ilike', "%{$query}%")` (Keep it simple, do not implement complex Postgres full-text tsvector unless performance demands it later).
    *   Return a JSON response grouping results by type.
2.  **Frontend (Vue):**
    *   Create a `<GlobalSearchModal>` component.
    *   Add a global event listener for `keydown` to detect `Cmd+K` (Mac) or `Ctrl+K` (Windows).
    *   When opened, show an input field. Debounce the API call by 300ms as the user types.
    *   Display results in a clean list with icons for each type (e.g., 📁 for Project, 🤵 for Client). Allow keyboard navigation (Up/Down arrows + Enter).

### Feature 5: Activity Feed
**Goal:** See what the team has been doing today.
**15-Year-Old Test:** "I look at the dashboard and see 'Sarah finished the Logo Design'."

#### Actionable Steps:
1.  **Database:** 
    *   Run `php artisan make:model Activity -m`. 
    *   Add `user_id`, `subject_type`, `subject_id`, `description` (string), `properties` (JSON).
2.  **Backend (Laravel):**
    *   Create a `LogsActivity` trait. 
    *   In the trait, hook into the model's `created`, `updated`, and `deleted` events to automatically insert a row into the `activities` table.
    *   Format the description in plain English (e.g., "updated the status of", "created a new").
3.  **Frontend (Vue):**
    *   Create an `<ActivityFeed>` widget for the main dashboard.
    *   Display a chronological list: `[User Avatar] [User Name] [Action] [Resource Name] • [Time ago]`.
    *   Limit to the last 20 activities. Add a "Load More" button if needed.

---

## ⚡ Phase 3: Quality of Life & Productivity

### Feature 6: Bulk Actions
**Goal:** Manage multiple items at once without clicking one by one.
**15-Year-Old Test:** "I check three boxes, click 'Delete', and they are gone."

#### Actionable Steps:
1.  **Frontend (Vue):**
    *   Add a checkbox to the first column of all index tables (Leads, Clients, Projects).
    *   Use Vue `ref` to track an array of `selectedIds`.
    *   Create a `<BulkActionBar>` component. It should be hidden by default, but slide up from the bottom of the screen when `selectedIds.length > 0`.
    *   Include simple buttons: "Archive", "Delete", "Change Status".
    *   **DO NOT** build complex bulk-edit forms. Just simple, destructive, or status-changing actions.

### Feature 7: Mobile-First Responsive Design
**Goal:** Make the app usable on a phone for quick checks.
**15-Year-Old Test:** "I open the app on my phone, and I don't have to pinch and zoom to read the text."

#### Actionable Steps:
1.  **Frontend (Vue/Tailwind):**
    *   Audit all `<table>` elements. On mobile (`md:hidden`), hide the table and render the data as a stack of cards.
    *   Add a mobile bottom navigation bar (`fixed bottom-0`) with icons for: Dashboard, Projects, Leads, and Profile. Hide the main sidebar on mobile.
    *   Ensure all buttons and touch targets are at least `h-10 w-10` (44px).
    *   Convert wide modal forms into full-screen slides on mobile.

### Feature 8: Dashboard Customization
**Goal:** Let users hide widgets they don't care about.
**15-Year-Old Test:** "I don't care about the 'Recent Leads' box, so I click the 'X' and it disappears."

#### Actionable Steps:
1.  **Database:** Add a `dashboard_layout` JSON column to the `users` table. Default it to an array of widget IDs.
2.  **Frontend (Vue):**
    *   Add a small "Customize" button to the dashboard header.
    *   When clicked, show an "X" on each widget and allow drag-and-drop reordering (use a lightweight library like `vuedraggable`).
    *   Clicking "X" removes the widget from the local state and the `dashboard_layout` JSON.
    *   **DO NOT** allow users to add new custom widgets or change widget internal settings. Just show/hide and reorder.

---

## 🛡️ Phase 4: Security & Polish

### Feature 9: Two-Factor Authentication (2FA)
**Goal:** Secure the accounts of the team.
**15-Year-Old Test:** "I log in, and it asks me for a code from my phone app."

#### Actionable Steps:
1.  **Backend (Laravel):**
    *   Install and configure Laravel Fortify.
    *   Enable `Features::twoFactorAuthentication()` in the Fortify config.
2.  **Frontend (Vue):**
    *   Add a "Security" tab in the user settings.
    *   Show a "Enable 2FA" button. When clicked, show the QR code and recovery codes.
    *   **DO NOT** force 2FA on day one. Make it optional but highly recommended in the UI.

### Feature 10: Quick Wins (UI/UX Polish)
**Goal:** Make the app feel premium and forgiving.

#### Actionable Steps:
1.  **Empty States:** Create a `<EmptyState>` component. Use it on all index pages when data is empty. Include a friendly illustration, a plain-English message ("No leads yet!"), and a primary button to create one.
2.  **Loading Skeletons:** Replace standard spinners with Tailwind `animate-pulse` skeleton screens for tables and cards while data is fetching.
3.  **Toast Notifications:** Use a library like `vue-sonner` or `v-toaster`. Show a green toast for success ("Lead created!") and a red toast for errors. Auto-dismiss after 3 seconds.
4.  **Confirm Destructive Actions:** Create a `<ConfirmModal>` component. Use it for *any* delete or archive action. Use plain English: "Are you sure you want to delete this lead? This cannot be undone."

---

## 🛑 Strict "DO NOT" List for AI Agents

To ensure the app passes the "15-Year-Old Test" and fits the SME constraint, **DO NOT** generate code for the following:

1.  **No Granular Permissions:** Do not create `permissions` or `roles` tables beyond the simple `owner`/`member` enum. Do not build "Can edit, but cannot delete" logic.
2.  **No Complex Search:** Do not implement Elasticsearch, Meilisearch, or complex Postgres `tsvector` weighting. Standard `ILIKE` queries are perfectly fine for 20 users.
3.  **No Deeply Nested Comments:** Do not build Reddit-style threaded replies for comments. Keep it flat.
4.  **No Complex Automation:** Do not build a visual workflow builder (like Zapier). If you build email templates, keep it to simple text replacement.
5.  **No API Key Management:** Do not build a UI for generating personal access tokens. This is an SME app, not a developer tool.
6.  **No "Settings Overload":** Do not create 50 toggle switches in the settings page. Group settings logically and hide advanced options behind an "Advanced" accordion.

---

## 🏗️ Architectural Reminders
*   **Actions over Controllers:** Continue using the `App\Actions` pattern. All new business logic (inviting users, posting comments) must go into dedicated Action classes.
*   **Form Requests:** Validate all incoming data using dedicated Form Request classes.
*   **Polymorphism:** Ensure the new `Comments` and `Activities` models use polymorphic relationships (`commentable`, `subject`) so they can be attached to any current or future model.
*   **Frontend State:** Keep Vue components dumb. Pass data via Inertia `props`. Use `ref` only for local UI state (like dropdown toggles or loading spinners).


-----------------

> **Completed:** Trello integration (one-way sync), double header fix.

---

## 🏦 Maroni Integration (Financial App)

### Overview
Maroni is a financial web app (Next.js, TypeScript) handling accounting, invoices, payroll, and expenses. PlazaOS is the CRM. Both apps share clients as a common entity.

**Architecture:**
- **Client sync:** PlazaOS → Maroni (CRM is source of truth). Uses existing PlazaOS webhook system for `client.created` / `client.updated` events.
- **Financial data:** PlazaOS pulls live from Maroni API (no local storage of invoices/expenses).
- **Events:** Maroni pushes events (invoice.paid, invoice.created) to PlazaOS via signed webhook.

```
PlazaOS (Laravel)                          Maroni (Next.js)
┌─────────────────────┐                   ┌──────────────────────┐
│  Webhook dispatch   │  client.created   │  POST /api/plazaos-  │
│  (existing system)  │ ─────────────────→│  webhook             │
│                     │  client.updated   │                      │
│                     │                   │  Creates/updates     │
│  MaroniService      │ ←─────────────────│  client in Maroni    │
│  (live fetch)       │   GET /api/       │                      │
│                     │   clients/:id/    │  GET /api/clients/   │
│  Show invoices,     │   invoices        │  :id/invoices        │
│  expenses, balance  │   GET /api/       │  GET /api/clients/   │
│  per client         │   clients/:id/    │  :id/expenses        │
│                     │   expenses        │  GET /api/clients/   │
│                     │   GET /api/       │  :id/summary         │
│                     │   clients/:id/    │  GET /api/dashboard/ │
│                     │   summary         │  summary             │
│                     │   GET /api/       │                      │
│                     │   dashboard/      │                      │
│                     │   summary         │                      │
│                     │                   │                      │
│  POST /api/maroni/  │ ←─────────────────│  Webhook:            │
│  webhook            │                   │  invoice.paid        │
│  (signature-verif.) │                   │  invoice.created     │
└─────────────────────┘                   └──────────────────────┘
```

### PlazaOS — Implementation Order

#### Step 1: Config + Database
- **`config/maroni.php`** — API base URL, API key, webhook secret (from `.env`)
- **Migration:** Add to `clients` table:
  - `maroni_client_id` (string, nullable, unique)
  - `maroni_sync_status` (string, nullable: `synced` / `failed`)
  - `last_maroni_synced_at` (datetime, nullable)

#### Step 2: MaroniService (`app/Services/MaroniService.php`)
HTTP client wrapping Maroni's REST API:
- `syncClient(Client $client)` — create/update client in Maroni, returns `maroni_client_id`
- `getClientInvoices(Client $client)` — list invoices for client
- `getClientExpenses(Client $client)` — list expenses for client
- `getClientSummary(Client $client)` — total billed, outstanding, paid
- `getDashboardSummary()` — global KPIs (monthly revenue, outstanding total)

#### Step 3: MaroniWebhookController (`app/Http/Controllers/MaroniWebhookController.php`)
Receives inbound webhooks from Maroni:
- `POST /api/maroni/webhook` — signature-verified with shared secret
- Events: `invoice.created`, `invoice.paid`, `client.updated`
- Logs activities on the relevant client

#### Step 4: ClientMaroniObserver (`app/Observers/ClientMaroniObserver.php`)
Hooks into Client `created` / `updated` events:
- **Outbound via Webhook (recommended first):** Maroni registers a webhook in PlazaOS Settings → Webhooks for `client.created` + `client.updated`. No new code needed — the existing webhook infrastructure handles it.
- **Direct API (fallback):** Observer calls `MaroniService::syncClient()` directly, stores `maroni_client_id`.

#### Step 5: Bulk Sync Command (`app/Console/Commands/SyncClientsToMaroni.php`)
- `php artisan maroni:sync-clients` — sends all clients to Maroni, sets `maroni_client_id`
- Useful for initial one-time sync after setup
- Route: `POST /maroni/sync-clients` for on-demand trigger from UI

#### Step 6: Settings Page
- Add `maroni_base_url`, `maroni_api_key`, `maroni_webhook_secret` to `SettingsController::SETTING_KEYS` + validation
- Add "Maroni" section to `Settings/Integrations.vue` with:
  - Base URL, API Key, Webhook Secret inputs
  - "Sync Clients" button
  - Connection status indicator

#### Step 7: Client Show — Financial Section
- Add "Financial" panel to `Clients/Show.vue`:
  - Total billed, total paid, outstanding balance (from `getClientSummary`)
  - Recent invoices list with external link to Maroni
  - Recent expenses list

#### Step 8: Dashboard — Financial KPIs
- Add KPI cards to `Dashboard.vue` (when Maroni is configured):
  - Monthly revenue
  - Outstanding invoices total
  - Recent payments

#### Step 9: Schedule (`routes/console.php`)
- `php artisan maroni:sync-clients` — nightly (catch any missed webhooks)
- `php artisan maroni:sync-clients --new` — every 5 min (optional, for near-real-time sync)

### Maroni (Next.js) — What Needs to Be Built

#### PlazaOS Webhook Receiver
```
POST /api/plazaos-webhook
```
- Validate HMAC signature with shared secret
- Handle `client.created`, `client.updated` events
- Create/update client in Maroni
- Store `plazaos_client_id` on Maroni's client record

#### Read API Endpoints (shared API key auth)
```
GET  /api/clients/:id/invoices      → Invoice[]
GET  /api/clients/:id/expenses      → Expense[]
GET  /api/clients/:id/summary       → { totalBilled, totalPaid, outstanding }
GET  /api/dashboard/summary         → { monthlyRevenue, outstandingTotal, recentPayments }
```

#### Outbound Webhook Sender
When financial events happen in Maroni, send signed POST to `PlazaOS_URL/api/maroni/webhook`:
- `invoice.created` → { event, client_id, invoice_id, amount, url }
- `invoice.paid`    → { event, client_id, invoice_id, amount, paid_at }

### Key Design Decisions
| Decision | Choice |
|---|---|
| Source of truth for clients | PlazaOS (CRM wins) |
| Financial data storage | None — fetched live from Maroni API |
| Client sync direction | PlazaOS → Maroni (via webhooks) |
| Financial events direction | Maroni → PlazaOS (via signed webhook) |
| Auth | Shared API key in `.env` + HMAC signatures |
| Initial sync | Bulk command `maroni:sync-clients` | 
