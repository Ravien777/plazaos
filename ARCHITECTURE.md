# ARCHITECTURE.md

# PlazaOS Architecture

## Purpose

This document defines the technical architecture of PlazaOS.

All development decisions should prioritize:

1. Simplicity
2. Maintainability
3. Performance
4. Scalability
5. Developer experience

This application is designed primarily for a solo entrepreneur.

Avoid enterprise complexity.

---

# Technology Stack

## Backend

Laravel 12+

PHP 8.4+

## Frontend

Vue 3
Inertia.js
TypeScript

## Styling

TailwindCSS

## Database

PostgreSQL

## File Storage

Cloudflare R2

## Authentication

Laravel Breeze

## Queue System

Laravel Queues

Driver:

* Database (development)
* Redis (production)

## Email

Resend API

## Calendar Integration

Google Calendar API

---

# Architecture Style

Use a modular monolith.

Do NOT use microservices.

All functionality lives in a single Laravel application.

Modules should be isolated using:

* Controllers
* Services
* Actions
* Policies
* Requests
* Models

Business logic must NEVER live inside controllers.

Controllers should remain thin.

---

# Folder Structure

app/

├── Actions/
│
├── Http/
│   ├── Controllers/
│   ├── Requests/
│   └── Middleware/
│
├── Models/
│
├── Policies/
│
├── Services/
│
├── Jobs/
│
├── Notifications/
│
├── Events/
│
├── Listeners/
│
├── DTOs/
│
├── Enums/
│
└── Support/

resources/

├── js/
│   ├── Pages/
│   ├── Components/
│   ├── Layouts/
│   ├── Composables/
│   └── Types/

---

# Modules

PlazaOS consists of the following modules:

1. Dashboard
2. Leads
3. Lead Sources
4. Clients
5. Projects
6. Documents
7. Meetings
8. Notifications
9. Email
10. Intake Forms
11. Tickets (future)
12. Client Portal (future)

Each module should be independently maintainable.

---

# Database Design

Use UUIDs for all primary keys.

Example:

id UUID PRIMARY KEY

Never expose auto-increment IDs.

---

# Users Table

users

Fields:

* id
* name
* email
* password
* created_at
* updated_at

Purpose:

Internal application user.

Version 1 supports only one user.

---

# Leads Table

leads

Fields:

* id
* company_name
* contact_name
* email
* phone
* website
* industry
* city
* country
* source
* status
* notes
* last_contacted_at
* converted_at
* created_at
* updated_at

Indexes:

* email
* website
* company_name
* status

---

# Lead Sources Table

lead_sources

Fields:

* id
* name
* url
* source_type
* configuration
* active
* last_run_at
* created_at
* updated_at

Configuration:

JSON column

Used to store:

* Filters
* Scraper settings
* Import settings

---

# Clients Table

clients

Fields:

* id
* company_name
* contact_name
* email
* phone
* website
* city
* country
* industry
* notes
* created_at
* updated_at

Indexes:

* email
* company_name

---

# Projects Table

projects

Fields:

* id
* client_id
* name
* description
* status
* progress_percentage
* start_date
* due_date
* created_at
* updated_at

Relationships:

Project belongs to Client

---

# Meetings Table

meetings

Fields:

* id
* client_id
* title
* description
* provider
* meeting_url
* start_time
* end_time
* created_at
* updated_at

Relationships:

Meeting belongs to Client

---

# Documents Table

documents

Fields:

* id
* client_id
* project_id
* original_name
* stored_name
* file_path
* file_size
* mime_type
* uploaded_by
* created_at

Relationships:

Document belongs to Client

Document optionally belongs to Project

---

# Notes Table

notes

Purpose:

Reusable note system.

Fields:

* id
* noteable_type
* noteable_id
* content
* created_by
* created_at
* updated_at

Polymorphic:

* Lead
* Client
* Project

---

# Activities Table

activities

Purpose:

Audit trail.

Fields:

* id
* subject_type
* subject_id
* event
* description
* metadata
* created_at

Examples:

Lead Created
Lead Converted
Project Created
Document Uploaded

---

# Notifications Table

Use Laravel Notifications.

Store:

* id
* type
* data
* read_at
* created_at

---

# Eloquent Relationships

Lead

hasMany Notes

Client

hasMany Projects
hasMany Meetings
hasMany Documents
hasMany Notes

Project

belongsTo Client
hasMany Documents
hasMany Notes

Meeting

belongsTo Client

Document

belongsTo Client
belongsTo Project

---

# Service Layer

All business logic belongs inside services.

Examples:

LeadService
ClientService
ProjectService
DocumentService
MeetingService
NotificationService

Controllers must never directly contain business logic.

---

# Action Classes

Use Actions for single responsibilities.

Examples:

ConvertLeadToClientAction

Responsibilities:

* Create client
* Copy notes
* Copy attachments
* Archive lead
* Create activity log

---

CreateMeetingAction

Responsibilities:

* Create Google Meet
* Store meeting URL
* Send invitations

---

UploadDocumentAction

Responsibilities:

* Validate file
* Upload to R2
* Save record

---

# DTOs

Use Data Transfer Objects.

Example:

CreateLeadDTO

Purpose:

Strongly typed input structure.

Avoid passing raw request arrays into services.

---

# Enums

Use PHP Enums.

LeadStatus

* New
* Qualified
* Contacted
* Interested
* MeetingScheduled
* ProposalSent
* Won
* Lost

ProjectStatus

* Discovery
* Design
* Development
* Testing
* Launch
* Completed

TicketStatus

* Open
* InProgress
* Waiting
* Closed

---

# API Design

Use resource routes.

Examples:

/leads
/clients
/projects
/documents
/meetings

Naming conventions:

GET /leads

GET /leads/{id}

POST /leads

PUT /leads/{id}

DELETE /leads/{id}

---

# Frontend Architecture

Pages should map directly to modules.

Example:

Pages/

Dashboard/

Leads/
├── Index.vue
├── Create.vue
├── Edit.vue
├── Show.vue

Clients/
├── Index.vue
├── Create.vue
├── Edit.vue
├── Show.vue

Projects/
├── Index.vue
├── Show.vue

---

# Shared Components

Create reusable components.

Examples:

DataTable

FiltersBar

SearchInput

StatusBadge

PageHeader

EmptyState

Modal

ConfirmDialog

NotificationBell

FileUploader

ProgressBar

Avoid duplicated UI code.

---

# State Management

Prefer:

* Inertia Props
* Vue Composables

Avoid Vuex or Pinia until necessary.

Keep state simple.

---

# File Upload Strategy

Files should NEVER be stored locally.

Process:

1. Upload file
2. Store in Cloudflare R2
3. Save metadata in database
4. Generate temporary signed URL when viewing

---

# Background Jobs

Use queues for:

* CSV imports
* Scraping
* Email sending
* AI processing
* Document processing

Never perform long-running tasks inside requests.

---

# Activity Logging

Every important action should generate an activity record.

Examples:

Lead Created

Lead Updated

Lead Converted

Client Created

Project Created

Document Uploaded

Meeting Scheduled

---

# Search

Version 1:

Database search

Searchable fields:

Lead:

* Company Name
* Website
* Email

Client:

* Company Name
* Website
* Email

Future:

Laravel Scout

---

# Security

Always validate requests.

Always authorize actions.

Never trust frontend data.

Sanitize:

* HTML input
* File uploads
* CSV imports

Implement:

* CSRF protection
* Rate limiting
* Signed URLs

---

# Performance Rules

Always paginate large datasets.

Default:

25 records per page.

Never load large relationships automatically.

Use eager loading where appropriate.

Avoid N+1 queries.

---

# MVP Development Order

Phase 1

* Authentication
* Dashboard
* Leads
* Notes
* Activities

Phase 2

* CSV Import
* CSV Export
* Lead Sources

Phase 3

* Clients
* Lead Conversion

Phase 4

* Projects
* Documents

Phase 5

* Meetings
* Google Calendar Integration

Phase 6

* Notifications
* Automations

Phase 7

* AI Features

Phase 8

* Client Portal
* Ticket System

---

# Development Rule

When choosing between:

A complex feature

or

A simple feature that solves 80% of the problem

Always choose the simpler solution.

PlazaOS is intended to be an operational tool, not an enterprise platform.

