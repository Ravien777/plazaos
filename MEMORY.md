
### Feature: MVP Execution Plan
Date: 2026-06-02

What was built:
- Broke down PRD MVP modules into phased, actionable steps (Phases 1–6)

Technical decisions:
- Follow ARCHITECTURE.md 8-phase ordering but limited to MVP scope
- Medium granularity: grouped by backend/frontend layer per feature
- Added Phase 2.5 for Email Outreach (in PRD MVP, not in original ARCHITECTURE phases)
- Phases 7–8 (AI Features, Client Portal, Ticket System) deferred per PRD §MVP Scope

Rejected alternatives:
- Linear module-by-module ordering (too disconnected from dependencies)
- Fine-grained steps (too noisy for high-level planning)
- All 13 modules at once (would include non-MVP work)

Follow-up work:
- Begin Phase 1 implementation: scaffold + auth

### Feature: Phase 1 Implementation
Date: 2026-06-02

What was built:
- Laravel 12 + Breeze (Inertia/Vue/TypeScript) project scaffolded
- Auth (login, register, password reset) via Breeze
- Dashboard with stat cards (New Leads, Active Leads, Active Clients, Upcoming Meetings) + ActivityFeed
- Leads module: migration (UUIDs, all fields), LeadStatus enum, Lead model, LeadController, form requests, LeadService, LeadPolicy
- Leads frontend: Index.vue (table + filters + search), Create.vue, Edit.vue, Show.vue
- Notes module: polymorphic migration, Note model, NoteController, NoteService, NotePolicy
- Notes frontend: reusable NotesSection.vue component embedded in Lead Show
- Activities module: migration (immutable log), Activity model, ActivityService (with `activity()` helper)
- Activities frontend: ActivityFeed.vue component on Dashboard + Lead Show

Technical decisions:
- Used SQLite for local dev (PostgreSQL auth issue, PDO extension extracted from rpm)
- Created artisan.sh wrapper for PHP_INI_SCAN_DIR to load PDO extensions
- `activity()` helper registered via app/helpers.php + composer.json autoload.files
- All IDs use UUIDs via HasUuids trait
- Notes use polymorphic relationship (morphs) for Lead/Client/Project attachability
- Activities use polymorphic relationship for any model

Rejected alternatives:
- Docker/Podman for dev environment (daemon not running, no sudo)
- PostgreSQL in dev (requires root for pg_hba.conf changes)

Environment quirks:
- PHP 8.4.21 without PDO extension - installed by downloading RPM and extracting pdo.so/pdo_sqlite.so
- PDO loaded via PHP_INI_SCAN_DIR custom scan path

Follow-up work:
- Begin Phase 2 implementation: CSV Import/Export, Lead Sources

### Feature: Phases 2–6 Implementation
Date: 2026-06-03

What was built:
- CSV Import/Export (Leads + Clients): upload UI, queue job, duplicate detection, filtered export
- Lead Sources: CRUD, SourceType enum, scraper job skeleton, manual run button
- Email Outreach: Resend SDK integration, EmailService, template system, email history on detail pages
- Clients module: CRUD, Client model, ClientService, ClientPolicy, tabs for Projects/Documents/Meetings
- Lead Conversion: ConvertLeadToClientAction — creates Client, copies notes/files, archives Lead
- Projects module: CRUD, ProjectStatus enum, progress tracking, status filters
- Documents module: Cloudflare R2 integration, UploadDocumentAction, signed URLs, file uploader component
- Meetings module: CRUD, Google Calendar + Meet integration, upcoming meetings widget
- Notifications: Laravel database notifications, NotificationBell component, dropdown, in-app index, mark-as-read
- Client Portal: separate `client` auth guard, ClientUser model, portal dashboard/projects/documents/meetings/tickets
- Portal auth (login/logout) + middleware
- All corresponding tests for each module

Technical decisions:
- `ValidationException::withMessages()` used instead of raw constructor
- Portal routes wrapped in `web` middleware group (fixes session + route model binding)
- `documentable_type` / `ticketable_type` use FQCN (no morph map, `getMorphClass()` returns FQCN)
- User auto-increment ID in tickets `foreignUuid('user_id')` → works in SQLite, may need fix for PostgreSQL
- Policy methods return `true` (single-user desktop system)
- `RateLimiter::for()` not static in Laravel 12 — use `tooManyAttempts()` / `hit()` / `clear()` instead

Rejected alternatives:
- Separate field CRUD for form builder (single Create/Edit page with dynamic field rows instead)
- Hardcoded `public` disk for file storage (uses `config('filesystems.default')` instead)

Environment quirks:
- Resend webhook endpoint CSRF-exempted in bootstrap/app.php
- Google Calendar integration requires `storage/app/google-calendar-credentials.json`

Follow-up work:
- Create DemoSeeder with interconnected demo data
- Fix vue-tsc type errors (18 errors across 6 files)

### Feature: Intake Forms Module
Date: 2026-06-03

What was built:
- IntakeForm, IntakeFormField, IntakeFormSubmission, IntakeFormSubmissionData models with migrations
- Admin CRUD (index, create, store, show, edit, update, destroy)
- Dynamic form builder on single Create/Edit page (add/remove/reorder fields inline)
- Portal submission flow (form list → show fields → submit with validation)
- File upload support in submissions (stored on configurable disk)
- Notification sent to admin on new submission
- Submissions viewable from IntakeForm detail page and Client Show tab
- Portal sidebar link for "Intake Forms"
- Admin nav link for "Forms"
- 14 tests (112 assertions): 3 test classes (admin CRUD, submission download, portal flow)
- 4 factories: IntakeFormFactory, IntakeFormFieldFactory, IntakeFormSubmissionFactory, IntakeFormSubmissionDataFactory

Technical decisions:
- `useForm` typed as `Record<string, string>` — multi-select values JSON-encoded to avoid v-model type conflicts with `string[] | File`
- File uploads via separate `Map<string, File>` + `transform()` callback merging files before submit
- Multi-select `@change` handlers extracted to methods (no inline `const` in Vue templates)
- `IntakeFieldType` enum with 8 field types: Text, Textarea, Email, Select, MultiSelect, File, Checkbox, Date

Rejected alternatives:
- Separate field CRUD (single page with dynamic rows is simpler for this use case)
- Multiple `useForm` instances per field type (single `Record<string, string>` is simpler despite JSON encoding)

### Feature: TypeScript & Build Fixes
Date: 2026-06-03

What was fixed:
- All 18 `vue-tsc --noEmit` errors across 6 files

Key fixes:
- `types/index.d.ts` — added `client_user` to auth `PageProps` type
- `LeadSources/Create.vue` & `Edit.vue` — replaced `transform` option in `form.post()` with `form.transform().post()` chain
- `Notifications/Index.vue` — replaced `preserveScroll` with `preserveState` in `router.reload()`
- `Portal/IntakeForms/Show.vue` — refactored to `Record<string, string>` form type, extracted inline `const` to methods
- `Portal/Meetings/Index.vue` — added `join_url` to `Meeting` TS interface
- `Portal/Tickets/Show.vue` — captured `defineProps` return value as `const props`
- Vite build passes (`npx vite build`)
- Total: 260 tests (914 assertions)

### Feature: DemoSeeder & Database Consolidation
Date: 2026-06-03

What was built:
- `DemoSeeder` with ~140 interconnected records:
  - 20 leads (realistic companies: Acme Corp, Stark Industries, etc.)
  - 8 clients (3 converted from won leads, 5 direct)
  - 4 portal users (Sarah Connor, Milton Waddams, Samir Nagheenanajar, Pepper Potts)
  - 12 projects distributed across clients
  - 2 intake forms with 16 total fields (Onboarding + Kickoff Questionnaire)
  - 3 intake form submissions with submission data
  - 10 meetings (mixed Lead/Client/Project meetables)
  - 12 notes spread across leads, clients, projects, and tickets
  - 6 tickets with 11 replies
  - 10 emails
  - 15 documents
  - 15 activities

Technical decisions:
- Created in correct FK order: User → LeadSource → Lead → Client → ClientUser → Project → IntakeForm → IntakeFormField → Meeting → Note → Ticket → TicketReply → Email → Document → Activity
- All data uses realistic names/companies (movie/pop-culture references)
- `getMorphClass()` for polymorphic type columns (FQCN convention, no morph map)
- `DemoSeeder` called from `DatabaseSeeder`, replacing 5 individual seeders (LeadSeeder, ClientSeeder, ProjectSeeder, MeetingSeeder, LeadSourceSeeder)
- Removed individual seeder files, autoload regenerated

### Feature: Deployment Configuration
Date: 2026-06-03

What was built:
- `.github/workflows/deploy.yml` — GitHub Actions CI/CD pipeline
  - Trigger: push to `production` branch
  - Test job: PHP 8.4 + Node 22, Pint lint, vue-tsc, Vite build, PHPUnit (SQLite)
  - Deploy job: appleboy/ssh-action, runs `bin/deploy.sh` on Hetzner VPS
  - Concurrency serializes deploys (no cancel-in-progress)
- Updated `bin/deploy.sh` (pre-existing script):
  - Default branch: `main` → `production`
  - Keep releases: 3 → 5
  - Added `php artisan event:cache` to optimization step

Technical decisions:
- Server clones repo itself (no artifact upload needed)
- Zero-downtime via symlink swap: `current → releases/YYYYMMDD_HHMMSS/`
- Fresh shallow clone per release (clean, no leftover state)
- `shared/.env` and `shared/storage/` persisted across releases
- Supervisor restarts queue worker on each deploy
- PHP-FPM reloaded for opcache invalidation

Rejected alternatives:
- Manual deploy script only (GitHub Actions provides test gate + automation)
- Docker-based deployment (bare metal simpler for single-server setup)
- Artifact upload approach (server-side git clone simpler)

Follow-up work:
- Configure GitHub secrets: DEPLOY_HOST, DEPLOY_USER, DEPLOY_KEY
- Set up GitHub deploy key on the server for read-only repo access

### Feature: Slack Notifications
Date: 2026-06-04

What was built:
- Installed `laravel/slack-notification-channel` v3.8
- `routeNotificationForSlack()` on User model (reads `services.slack.notifications.bot_user_oauth_token`)
- Slack channel + Block Kit formatted `toSlack()` on 10 notification classes (LeadCreated, LeadImported, LeadInactiveReminder, ImportCompleted, MeetingScheduled, ProjectStatusChanged, DocumentUploaded, TicketCreated, TicketReplied, FormSubmissionReceived)
- 13 tests in `tests/Unit/Notifications/SlackNotificationsTest.php`
- Graceful skip when token is empty (no Slack configured)
- Full suite: 329 tests (1028 assertions; later 420 tests / 1387 assertions)

Technical decisions:
- Bot Token style (not Incoming Webhook) — single `SLACK_BOT_USER_OAUTH_TOKEN` env var
- Block Kit API (sectionBlock, actionsBlock) for richer actionable messages
- Variables extracted outside Heredoc to avoid PHP parser edge cases
- Team-wide notifications routed through the single user's `routeNotificationForSlack()`

Rejected alternatives:
- Incoming Webhook per channel (Bot Token more flexible, one env var)
- Plain text messages (Block Kit richer for actionable notifications)

Follow-up work:
- Set `SLACK_BOT_USER_OAUTH_TOKEN` in production .env for Slack integration

### Feature: Warm/Stone Color Theme
Date: 2026-06-04

What was changed:
- `tailwind.config.js` — remapped entire `gray` palette to Tailwind's `stone` (warm neutrals)
- `resources/css/app.css` — added 10 CSS custom properties (`--color-bg`, `--color-primary`, etc.)
- `resources/js/app.ts` — Inertia progress bar `#4B5563` → `#44403c`
- 93 `.vue` files bulk-updated via 17 sed patterns (~1800 occurrences)

Color shifts:
- Page backgrounds: `gray-100` → `stone-100` (#f5f5f4), `gray-50` → `stone-50` (#fafaf9)
- Primary buttons: `bg-gray-800` → `bg-gray-700` (#44403c), hover `700` → `600`
- Links: `text-indigo-600` → `text-indigo-500` (#6366f1), hover `900` → `600`
- Headings: `text-gray-900` → `text-gray-800` (#292524), subheadings `800` → `700`
- Metadata: `text-gray-500` → `text-gray-600` (#57534e) — WCAG AA contrast fix
- Decorative: `text-gray-400` → `text-gray-500` (#a8a29e)
- Focus rings: `ring-indigo-500` → `ring-indigo-400` (#818cf8)
- Borders: `border-gray-300` → `border-gray-200` (#e7e5e4)
- Dividers: `divide-gray-200` → `divide-gray-100`

Technical decisions:
- Single config line (`gray: colors.stone`) warms all `gray-*` classes instantly
- Contrast bumps applied via sed alongside palette remap (WCAG AA compliance)
- No dark mode — none requested, stay focused on warm-light aesthetic

### Feature: Webhook Integration
Date: 2026-06-04

What was built:
- Webhook subscription system — users create webhooks with URL + event subscriptions
- Migration + Webhook model (uuid PK, user FK, url, events jsonb, secret, active, error tracking, soft deletes)
- WebhookPolicy — scoped to owner (view/update own, delete only if owner canDelete)
- WebhookService — CRUD + dispatch() that finds active webhooks subscribed to an event
- DispatchWebhookJob — queued POST with HMAC-SHA256 signature in X-Webhook-Signature header
- WebhookController — CRUD + test action
- 10 exposed events: lead.created, lead.converted, client.created, project.created, project.completed, meeting.scheduled, ticket.created, ticket.closed, document.uploaded, email.opened
- ActivityService::log() automatically dispatches webhooks after creating activity
- Frontend: Settings/Webhooks/Index.vue, Create.vue, Edit.vue
- Nav link in AuthenticatedLayout (settings dropdown, desktop + mobile)
- 13 new files (migration, model, policy, 2 form requests, service, job, controller, factory, 3 Vue pages, 3 test files)
- 18 new tests (78 assertions)

Technical decisions:
- Payload includes event, data (id, type, attributes), and ISO8601 timestamp
- Signature = HMAC-SHA256 of raw JSON body using webhook's secret
- Webhook errors tracked per-webhook (last_error_at, last_error_message)
- Events dispatched via queue (ShouldQueue) — webhook calls don't block HTTP responses
- WebhookService injected into ActivityService singleton for auto-dispatch on any logged activity

Rejected alternatives:
- Standalone webhook observer (ActivityService injection is simpler, one dispatch point)
- Event sourcing pattern (unnecessary complexity for outgoing webhooks only)

Follow-up work:
- Configure webhooks in production for Zapier/Make/n8n integration

### Feature: Multi-User Data Scoping
Date: 2026-06-05

What was built:
- Migration added `team_id` (UUID FK → teams.id) to 20 resource tables
- `BelongsToTeam` trait with global scope + auto-assign on `creating` event
- Applied trait to 19 models (Lead, Client, Project, Meeting, etc.)
- Backfills existing records to first team if exactly one exists
- Fixed `SyncTrelloAction` which referenced non-existent `project->team_id` column

Technical decisions:
- Global scope silently skips filtering when `auth()->user()` is null (console/queue/admin)
- `$query->getModel()->qualifyColumn('team_id')` used instead of `static::qualifyColumn()` (Laravel 12 made qualifyColumn non-static)
- No policy changes needed — global scope handles data isolation transparently
- Sub-entity models (TicketReply, IntakeFormSubmission) get the trait for consistency
- Defensive-in-depth policy checks not added — scope is sufficient, route model binding naturally 404s foreign-team records

Rejected alternatives:
- Manual `->where('team_id', ...)` everywhere (scope is automatic and implicitly understood)

Follow-up work:
- None — the scoping is fully transparent to all existing code paths

### Feature: Mobile-First Responsive Design
Date: 2026-06-05

What was built:
- `DataTable.vue` — shared component rendering as table on desktop (`sm:+`), card layout on mobile (`<sm`)
- `FilterBar.vue` — collapsible filter panel (toggle button on mobile, always visible on desktop)
- `BottomNav.vue` — sticky mobile bottom nav with 6 icon-only links (Dashboard, Leads, Clients, Projects, Meetings, Tickets)
- 12 index/trash pages converted to use DataTable (Leads, Clients, Projects, Tickets, LeadSources, IntakeForms, Templates, Testimonials + 4 trash pages)
- Touch target audit: `py-2` → `py-3` + `min-h-[44px]` on PrimaryButton, SecondaryButton, DangerButton
- AuthenticatedLayout updated with BottomNav + `pb-20` on mobile content area

Technical decisions:
- Shared component over per-page cards — DRY, consistent, but each page provides its own `#card` scoped slot for custom layout
- Scoped slots for cell rendering (`#cell-{key}`) — flexible without tight coupling
- Meetings and Tasks pages skipped (already mobile-friendly: card-based/kanban)

Rejected alternatives:
- Dedicated mobile menu component (inline in layout is sufficient and simpler)
- Table-to-card CSS-only approach (scoped slots give each page full control over mobile layout)

### Feature: Marketing Pages, UI-Configurable Integrations, Test Coverage Sprint
Date: 2026-06-08

What was built:
- Marketing pages: MarketingLayout.vue (shared glass-header + footer), About.vue, Features.vue, ScrollReveal.vue, Welcome.vue rewritten with 7 sections
- CSS keyframes: `float`, `marquee`, `fade-in`, `fade-in-up`, `glow-pulse`, `grid-scroll`
- Integrations made UI-configurable via Settings: Trello (setting() method, Integrations.vue fields), Resend (AppServiceProvider override), OpenAI (AiService setting() method)
- Full-app scan: 5 parallel agents scanned backend, frontend, database, routes/config, tests — found 10 critical, 6 high, 6 medium issues
- All 10 critical bugs fixed: route param name mismatches (5 controllers), trash routes unreachable, SyncTrelloAction ProjectStatus enum, ResendWebhookController HMAC, user_settings PK migration, LeadSourceController show route, 3 factories, NoteFactory created_by, duplicate LeadCreated notification
- Batch A (config): PG default port 5432→5433, R2 disk uses R2_* env vars, CommentPolicy registered, 4 Vue components use route() not hardcoded URLs
- Batch B (code): tearDown() in 3 config-mutating tests, status indexes migration, IntakeFormField/Submission BelongsToTeam trait
- Batch C: Resend Guzzle mock via Mockery::mock(ResendClient::class) in EmailServiceTest, EmailControllerTest, LeadBulkControllerTest
- Batch D: 13 new test files, 71 new tests — CommentController, EmailTemplateController, MaroniController, MaroniWebhookController, NotificationController, ProjectBulkController, SecuritySettingsController, SettingsController, TicketBulkController, TrelloController, Portal/TicketReplyController, SendEmailJob, GoogleCalendarService
- Batch E: Removed hardcoded `['id' => 1]` from User::factory()->create() across ~63 test files; fixed `$user` scope in ImportLeadsJobTest and MeetingObserverTest
- Fixed flaky CalendarControllerTest (Meeting factory date range)

Technical decisions:
- Resend SDK uses raw Guzzle, not Laravel Http facade — used Mockery::mock(ResendClient::class) in container, not Http::fake()
- Mocked MaroniService must allow isConfigured() as a by-default expectation (ClientMaroniObserver fires on Client creation)
- Webhook HMAC signature tests must use payloads that round-trip identically through json_decode/json_encode (empty objects `{}` vs arrays `[]` differ)
- Guest redirects may go to `/` instead of `route('login')` — check actual redirect target

Rejected alternatives:
- Http::fake() for Resend (SDK uses raw Guzzle, not Laravel's HTTP client)

Follow-up work:
- Review responsive layout on mobile for new marketing pages
- Look into config caching with dynamic integration settings (caveat: config() cache won't reflect DB overrides after php artisan config:cache)
