# PlazaOS

**Your team's command center for leads, clients, and projects.**

PlazaOS is an all-in-one CRM and project management platform built for small teams (1–20 people) who've outgrown spreadsheets but don't need the complexity — or the price tag — of enterprise tools like Salesforce or HubSpot.

---

## The Problem

You're juggling leads in a spreadsheet, tracking tasks in one tool, emailing from another, and scheduling meetings in a third. Nothing talks to each other. Nothing gives you a clear picture of where things stand.

## The Solution

PlazaOS brings leads, clients, projects, meetings, documents, and emails into one unified workspace. Every piece of data is connected. Every activity is tracked. Your team stays aligned without switching tabs all day.

---

## Who It's For

- **Freelancers & agencies** managing multiple clients and projects
- **B2B sales teams** tracking leads through a pipeline
- **Small businesses** that need a single source of truth for customer relationships
- **Anyone tired of spreadsheets** for CRM and project management

---

## Features

| Capability | What you can do |
|---|---|
| **Lead Management** | Track leads through a customizable pipeline, import from CSV, enrich from online sources, and convert to clients in one click |
| **Client Management** | Centralized client profiles with linked projects, meetings, documents, and communication history |
| **Project Management** | Create projects from templates, assign tasks, track progress, and send automated status updates |
| **Email Outreach** | Compose and send emails directly from lead or client profiles using templates. Powered by Resend for reliable delivery |
| **Meeting Scheduling** | Create meetings with Google Meet links and calendar integration, all linked to the relevant client or project |
| **Document Storage** | Upload, organize, and share files with clients. Powered by Cloudflare R2 for fast, reliable storage |
| **Notes & Activity Feed** | Polymorphic notes attach to any record. Every action is logged to a timeline so you always know what happened and when |
| **CSV Import / Export** | Bulk import leads from any CSV file with column mapping and duplicate detection. Export filtered data any time |
| **Client Portal** | Give clients a limited view of their projects, documents, and meeting notes without exposing your full system |
| **Webhook Integration** | Connect PlazaOS to Zapier, n8n, Make, or any custom endpoint. Events fire in real time with HMAC-SHA256 signed payloads |
| **Onboarding Wizard** | Guided setup walks new users through creating their first team, importing contacts, and launching their first project |
| **Role-based Access** | Owner, Admin, Member, and Client Portal roles with granular permissions per resource |

---

## How We Compare

| Feature | PlazaOS | HubSpot (Free) | Salesforce | Monday.com | Spreadsheets |
|---|---|---|---|---|---|
| Lead pipeline | ✅ Yes | ✅ Basic | ✅ Yes | ❌ No | ❌ Manual |
| Client profiles | ✅ Yes | ❌ No | ✅ Yes | ❌ No | ❌ Manual |
| Project management | ✅ Yes | ❌ No | ✅ Add-on | ✅ Yes | ❌ Manual |
| Email outreach | ✅ Built-in | ✅ Free tier | ✅ Add-on | ❌ No | ❌ No |
| Meeting scheduling | ✅ Built-in | ❌ No | ❌ No | ❌ No | ❌ No |
| Document storage | ✅ Built-in | ❌ No | ✅ Add-on | ✅ Add-on | ❌ No |
| Client portal | ✅ Yes | ❌ No | ✅ Add-on | ✅ Add-on | ❌ No |
| Webhook integrations | ✅ Yes | ✅ Yes | ✅ Yes | ✅ Yes | ❌ No |
| Mobile responsive | ✅ Yes | ✅ Yes | ✅ Yes | ✅ Yes | ✅ Yes |
| Self-hostable | ✅ Yes (open source) | ❌ No | ❌ No | ❌ No | N/A |
| Price | **Free** self-hosted / **$49/mo** Pro cloud | Free (limited) | $25/user/mo | $12/user/mo | Free |

---

## Pricing

| Plan | Price | Best for |
|---|---|---|
| **Free** (self-hosted) | **$0** | Teams who want full control of their data and infrastructure |
| **Pro** (cloud) | **$49/mo** up to 5 users | Small teams who want a managed experience without the overhead |
| **Team** (cloud) | **$149/mo** up to 20 users | Growing teams needing more seats |

All cloud plans include hosting, backups, SSL, and updates. Self-hosted users get the same software with no license fees — just bring your own server.

---

## Architecture Overview

```
┌──────────────┐     ┌──────────────┐     ┌──────────────┐
│   Browser    │────▶│  Caddy (TLS) │────▶│  PHP-FPM     │
│ (Vue + Inertia)│    │  (reverse    │     │  (Laravel 12) │
└──────────────┘     │   proxy)     │     └──────┬───────┘
                     └──────────────┘            │
                                          ┌──────┴───────┐
                                          │  PostgreSQL  │
                                          │  (database)  │
                                          └──────┬───────┘
                                          ┌──────┴───────┐
                                          │   Redis      │
                                          │ (queue/cache)│
                                          └──────────────┘
```

- **Backend**: Laravel 12 with strict types, service-oriented architecture, and policy-based authorization
- **Frontend**: Vue 3 + Inertia.js — server-side routing with client-side reactivity. No API boilerplate
- **Database**: PostgreSQL 16 with UUID primary keys and team-based row-level isolation
- **File storage**: Cloudflare R2 (S3-compatible, zero egress fees)
- **Queue**: Redis-powered async job processing for imports, emails, and webhooks
- **Email**: Resend SDK for transactional and campaign email delivery
- **Security**: CSRF protection, CSP headers, signed webhook payloads, team-scoped data isolation

---

## Screenshots

> *Coming soon — mockups and screenshots of the dashboard, lead pipeline, and project view.*

---

## Quick Start

### Try the cloud version

Visit [plazaos.com](https://plazaos.com) and create a free account. No credit card required.

### Self-host

```bash
git clone <repo-url> plazaos
cd plazaos
cp .env.example .env
composer install
npm install && npm run build
php artisan key:generate
php artisan migrate
php artisan serve
```

For detailed setup instructions, see [SETUP.md](SETUP.md).

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 12, PHP 8.4 |
| Frontend | Vue 3, Inertia.js, TypeScript, Tailwind CSS |
| Database | PostgreSQL 16 |
| Queue / Cache | Redis |
| File Storage | Cloudflare R2 |
| Email | Resend |
| Reverse Proxy | Caddy (auto HTTPS) |
| CI/CD | GitHub Actions |
| Testing | PHPUnit 11 (520+ tests) |

---

## License

PlazaOS is open source software. See the [LICENSE](LICENSE) file for details.

---

*Built with Laravel, Vue, and PostgreSQL.*
