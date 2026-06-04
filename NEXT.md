# PlazaOS SME Expansion: Feature Specifications for AI Coding Agents

## 📋 Context & Core Directives
**Project:** PlazaOS (Transitioning from Single-User to SME Team Platform)
**Target Audience:** Small to Medium Enterprises (SMEs), specifically teams of **1 to 20 members maximum**. 
**Target User Persona:** Non-technical users. The UI/UX must be so intuitive that a **15-year-old could use it without any training or manual**.
**Anti-Pattern:** DO NOT build enterprise-grade features. No complex Role-Based Access Control (RBAC) matrices, no Gantt charts, no resource leveling, no complex approval workflows. 

**Tech Stack Context:** Laravel 12 (PHP 8.4), Vue 3, Inertia.js, TypeScript, Tailwind CSS, PostgreSQL 16.

---

## 🎨 Global UI/UX Golden Rules (The "15-Year-Old" Test)
When generating code for these features, you MUST adhere to these rules:
1. **Zero Clutter:** Hide advanced options behind "Settings" or "More" menus. The main view should only show the absolute essentials.
2. **Plain English:** No industry jargon. Use "Team" instead of "Organization", "To-Do" instead of "Task Dependencies", "Share" instead of "Provision Access".
3. **Visual over Textual:** Use progress bars, color-coded tags, and emojis instead of dense text tables.
4. **Forgiving Actions:** Always provide an "Undo" toast notification for destructive actions (delete, archive) instead of annoying confirmation modals.

---

## 🚀 Feature Specifications

### 1. Lightweight Team & Workspace Management
**Goal:** Allow the owner to invite up to 19 team members to collaborate.
**Agency-os Complexity to Avoid:** Complex hierarchical roles, custom permission toggles per resource, department structures.

#### Implementation Guidelines:
*   **Roles:** Strictly limit to two roles: `Owner` (the creator) and `Member` (everyone else). 
*   **Invitations:** 
    *   **DO:** Implement a simple "Invite via Email" flow. Generate a secure, single-use magic link.
    *   **DO NOT:** Build a complex invitation approval workflow or Active Directory/LDAP sync.
*   **Team Directory:** A simple grid showing team members' avatars, names, and a "Message" or "Assign" quick action.
*   **Database Hint:** Add a `team_id` (UUID) to the `users` table. Add a `role` enum column (`owner`, `member`) defaulting to `member`.

### 2. Simplified Client Portal (Read-Only)
**Goal:** Give clients a place to see project progress and files without logging into the main app.
**Agency-os Complexity to Avoid:** Client-side task creation, client-side commenting, complex client user management, granular client permissions.

#### Implementation Guidelines:
*   **Access Method:** 
    *   **DO:** Generate a unique, unguessable URL (e.g., `/portal/{uuid}`) for each client. No passwords required for the client (security via obscurity + HTTPS).
    *   **DO NOT:** Build a client login/authentication system.
*   **Portal UI:** 
    *   A single, beautiful, read-only dashboard.
    *   Shows: Project Name, Overall Progress Bar (0-100%), Recent Activity Feed (last 5 updates), and a "Files" list.
    *   **DO NOT:** Allow clients to edit anything. Include a simple "Contact Us" button that opens their default email client with a pre-filled subject line.

### 3. Intuitive Task & Project Boards (Kanban)
**Goal:** Manage tasks visually without the learning curve of complex project management software.
**Agency-os Complexity to Avoid:** Gantt charts, critical path method, task dependencies (Task B blocks Task A), sub-tasks, time-tracking per task.

#### Implementation Guidelines:
*   **View:** A Trello-style Kanban board. Default columns: `To Do`, `In Progress`, `Done`.
*   **Interactions:** 
    *   **DO:** Implement smooth drag-and-drop for moving cards between columns.
    *   **DO:** Allow creating a task instantly by typing in an "Add task..." input at the bottom of a column.
*   **Task Card UI:** 
    *   Show: Task Title, Assignee Avatar, and a colored Priority Dot (Red=High, Yellow=Med, Gray=Low).
    *   **DO NOT:** Show due dates, estimated hours, or dependency links on the main card. Keep it visually clean.
*   **Database Hint:** Add a `status` enum (`todo`, `in_progress`, `done`) and a `priority` enum to the `tasks` table. Add an `order` integer column for drag-and-drop sorting.

### 4. Visual Team Calendar
**Goal:** See who is doing what and when, at a glance.
**Agency-os Complexity to Avoid:** Resource leveling, conflict detection, complex recurring event rules, multi-timezone team scheduling.

#### Implementation Guidelines:
*   **Views:** Only provide **Week** and **Month** views. Day view is unnecessary for this scale.
*   **Visuals:** 
    *   Events/Tasks should be color-coded based on the **Assignee** (each team member gets a unique pastel color).
    *   **DO:** Show a simple list of "Today's Focus" on the side of the calendar.
    *   **DO NOT:** Build a drag-and-drop calendar rescheduler. If a date changes, they should open the task and change the date picker. (Keeps UI simple and prevents accidental drag-and-drop errors).
*   **Sync:** Simple one-way export (`.ics` file generation) so users can add their PlazaOS tasks to their personal Google/Apple calendars.

### 5. "One-Click" Integrations & Notifications
**Goal:** Keep the team in the loop without forcing them to constantly check the app.
**Agency-os Complexity to Avoid:** Deep two-way API syncs, bi-directional data mapping, complex webhook builders.

#### Implementation Guidelines:
*   **Slack Integration:**
    *   **DO:** Simple OAuth2 login. Allow users to toggle "Send notifications to Slack" in their profile.
    *   **DO:** Post simple, formatted messages to a designated Slack channel when a project is marked "Done" or a high-priority task is created.
    *   **DO NOT:** Allow creating PlazaOS tasks from within Slack.
*   **Email Digests:**
    *   **DO:** A simple toggle in settings: "Send me a daily summary email at 8:00 AM".
    *   The email should just be a plain-text or simple HTML list of "Tasks Due Today" and "Overdue Tasks".

### 6. Automated Client Feedback (Testimonials)
**Goal:** Easily collect reviews from happy clients to use for marketing.
**Agency-os Complexity to Avoid:** Complex NPS surveys, multi-question forms, automated social media posting.

#### Implementation Guidelines:
*   **The Flow:** 
    1. On a Project or Client detail page, add a big, friendly button: **"Request a Review"**.
    2. Clicking it sends a pre-written, friendly email to the client with a link to a public form.
*   **The Public Form:**
    *   **DO:** Ask exactly TWO questions: 
       1. "How was your experience working with us?" (1 to 5 Stars)
       2. "Would you like to leave a short written review?" (Optional text area)
    *   **DO NOT:** Ask for their company name, role, or email on the form (we already have it).
*   **Internal Dashboard:**
    *   Create a "Wall of Love" widget on the main dashboard showing the latest 5-star reviews.
    *   Include a simple "Copy to Clipboard" button next to each review so the owner can easily paste it into their website.

---

## 🛑 Strict "DO NOT" List for AI Agents
To ensure the app remains simple enough for a 15-year-old and fits the SME constraint, **DO NOT** generate code for the following:
1. **No Granular Permissions:** Do not create `permissions` or `roles` tables. Just use the `is_admin` or `role` enum on the user.
2. **No Audit Logs UI:** Do not build a UI for viewing system audit logs. (You can log to the database in the background, but don't show it to the user).
3. **No Complex Search:** Do not build Elasticsearch/Meilisearch integrations. Standard PostgreSQL `ILIKE` queries are more than fast enough for 20 users and a few thousand records.
4. **No File Versioning:** When a user uploads a document, it overwrites or creates a new simple file. Do not build a "Version History" tree for documents.
5. **No API Key Management:** Do not build a UI for users to generate personal API tokens. This is an SME tool, not a developer tool.

---

## 🏗️ Architectural Notes for the AI
*   **Polymorphism:** Continue using the existing polymorphic relationships for `Notes`, `Documents`, and `Activities`. When adding `Tasks` and `Calendar Events`, ensure they can also be polymorphically linked to `Clients` or `Projects`.
*   **Soft Deletes:** Use Laravel's `SoftDeletes` on all major models (Clients, Projects, Tasks). Since the UI is simple, users will accidentally delete things. A simple "Trash" view (accessible only to Owners) is required to restore them.
*   **Frontend State:** Use Vue 3 `ref` and `reactive` for local UI state (like drag-and-drop sorting). Use Inertia `router.visit` or `form.post` for actual data persistence. Keep the Vue components small and highly focused.
