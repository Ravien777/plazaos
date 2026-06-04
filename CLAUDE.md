# CLAUDE.md — AgencyOS

## Identity

- **Ravien Sewpal** — solo founder, full-stack developer building AgencyOS
- **Strong in:** Full-stack SaaS, API design, database modelling, Stripe, cron automation
- **Learning:** Advanced AI/ML, React Server Components, WebSocket real-time patterns

---

## Communication Style

- No openers. Start with the answer.
- Simple question → short answer. Complex task → full detail.
- Present 2–3 approaches before acting on anything significant. Wait for a choice.
- Admit unknowns. Never fill gaps with plausible-sounding guesses.
- My voice: direct, short sentences, no buzzwords, no fluff.

---

## Core Rules (Karpathy's 4)

1. **Ask, don't assume.** Unclear requirement? Ask before writing a line.
2. **Simplest solution first.** No unrequested abstractions, flexibility, or configurability.
3. **Don't touch unrelated code.** Not part of the task? Don't touch it — not even to improve it.
4. **Flag uncertainty explicitly.** Not confident in an approach? Say so before proceeding.

---

## Behaviour Rules

### Stay in Scope
Only modify files, functions, and lines directly related to the current task. No refactoring, renaming, reformatting, or "improvements" to adjacent code. Notice something worth fixing? Mention it at the end. Don't touch it.

### Show What Changed
After every coding task:
- **Files changed** — every file touched
- **What changed** — one line per file
- **Not touched** — files intentionally skipped
- **Follow-up needed**

### Hard Stops
These require explicit in-session confirmation before proceeding:
- Deploying or pushing to any environment
- Running Prisma migrations (`prisma db push`, `prisma migrate`)
- Calling external APIs (Stripe, Resend, Twilio, Xero, QuickBooks)
- Any irreversible command
- Modifying cron config (`vercel.json`)

Never send, post, publish, or schedule anything without explicit confirmation in the current message.

### Extended Thinking
For architecture, performance tradeoffs, database design, or long-term technical decisions: work through the problem step by step. Surface tradeoffs I haven't considered. Flag assumptions that may not hold at scale. Then give a recommendation.

---

## Goal-Driven Execution

Transform tasks into verifiable goals before starting:
- "Add validation" → write tests for invalid inputs, then make them pass
- "Fix the bug" → write a failing test, fix it, make it pass
- "Refactor X" → ensure tests pass before and after

For multi-step tasks, state the plan first:
```
1. [Step] → verify: [check]
2. [Step] → verify: [check]
3. [Step] → verify: [check]
```

---

## Session Protocol

### Session Start
Read in order before touching anything:
1. `MEMORY.md`
2. `ERRORS.md`
3. `ARCHITECTURE.md`
4. `PRD.md`

If a proposed change conflicts with a logged decision: **stop. Explain the conflict. Ask for direction.** Never silently override.

### Session End
When I say "session end", "wrapping up", or "let's stop here" — append a summary to `MEMORY.md`:
- Worked on
- Completed
- In progress
- Decisions made
- Next session priorities

---

## Tech Stack (locked — no additions without approval)

| Layer      | Choice                                      |
|------------|---------------------------------------------|
| Backend    | Laravel 12+, PHP 8.4+                       |
| Frontend   | Vue 3, Inertia.js, TypeScript               |
| Styling    | TailwindCSS                                 |
| Database   | PostgreSQL                                  |
| Auth       | Laravel Breeze                              |
| Storage    | Cloudflare R2                               |
| Email      | Resend                                      |
| Queue      | Laravel Queues (DB in dev / Redis in prod)  |
| Hosting    | Hetzner VPS                                 |

---

## Architecture

**Pattern:** Modular Monolith. No microservices.

Business logic lives in **Services** and **Actions** only — never in controllers, Vue pages, routes, or middleware.

Controllers do exactly three things:
1. Validate the request
2. Call an action or service
3. Return a response

---

## Database Standards

- **IDs:** UUIDs everywhere. No auto-increment.
- **Tables:** `snake_case` plural — `leads`, `clients`, `projects`
- **Columns:** `snake_case`
- **Foreign keys:** always explicit
- **Indexes:** on `email`, `website`, `status`, and all foreign keys
- **Migrations:** generate before models. Never modify committed migrations — create new ones.

---

## Laravel Standards

Always use: Form Requests, Policies, Services, Actions, DTOs, Enums.

Never: fat controllers, raw `$request->all()` passed to services, magic strings.

```php
// ✅
LeadStatus::New

// ❌
"new"
```

---

## Frontend Standards

- TypeScript in every component. No exceptions.
- Pages stay thin. Logic goes in composables or services.
- Extract a component when logic or UI repeats 3+ times. Not before.

---

## Testing

**For bugs:**
1. Reproduce the bug
2. Write a failing test
3. Fix the bug
4. Make the test pass

Never claim a bug is fixed without a passing test.

**For new features:** write tests for validation, permissions, and core workflows. Testing is mandatory.

---

## Feature Completion

A feature is not done until all of the following are true:

- [ ] Code implemented
- [ ] Tests written
- [ ] Tests passing
- [ ] Documentation updated
- [ ] `MEMORY.md` updated
- [ ] Activity logged

---

## Memory System

### `MEMORY.md` — Decision Log

Read at session start. Update after every completed feature. Append only — never remove entries.

Each entry:
```
### Feature: [name]
Date: YYYY-MM-DD
What was built:
Technical decisions:
Rejected alternatives:
Follow-up work:
```

### `ERRORS.md` — Failure Log

Check before suggesting approaches to familiar problem types. Log whenever an approach takes more than 2 attempts.

Each entry:
```
What didn't work:
What worked instead:
Note for next time:
```

---

## Project Identity

**AgencyOS is:** desktop-first, single-user, solo entrepreneur-focused CRM and lead generation tool.

**AgencyOS is not:** ERP, HR, accounting, team collaboration, or enterprise software.

**Design rule:** A 15-year-old should understand every screen without training.

**When in doubt:** choose simplicity over flexibility.
