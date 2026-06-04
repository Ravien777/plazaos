# AgencyOS - Product Requirements Document (PRD)

## Overview

AgencyOS is a desktop-focused web application designed for solo entrepreneurs and small agencies.

The purpose of the platform is to:

1. Collect and manage cold leads
2. Convert leads into clients
3. Manage client communications
4. Manage projects
5. Collect client information and documents
6. Schedule meetings
7. Handle simple support tickets
8. Automate repetitive workflows

The platform is NOT:

* An accounting solution
* An invoicing solution
* An HR system
* An employee management platform
* A full enterprise CRM

The platform should remain extremely simple and intuitive.

Primary design principle:

> A 15-year-old should be able to understand and use every screen without training.

---

# Technical Requirements

## Backend

Laravel (latest stable version)

## Frontend

Vue 3
Inertia.js
TailwindCSS

## Database

PostgreSQL

## File Storage

Cloudflare R2

## Email

Resend API

## Authentication

Laravel Breeze

## Queue Processing

Laravel Queues

---

# Core Entities

## Lead

Represents a potential client.

Fields:

* id
* company_name
* contact_name
* email
* phone
* website
* country
* city
* industry
* source
* notes
* status
* last_contacted_at
* created_at
* updated_at

Statuses:

* New
* Qualified
* Contacted
* Interested
* Meeting Scheduled
* Proposal Sent
* Won
* Lost

---

## Client

Represents a converted lead.

Fields:

* id
* company_name
* contact_name
* email
* phone
* website
* country
* city
* industry
* notes
* created_at
* updated_at

---

## Project

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

Statuses:

* Discovery
* Design
* Development
* Testing
* Launch
* Completed

---

## Ticket

Fields:

* id
* client_id
* project_id
* subject
* description
* status
* priority
* created_at
* updated_at

Statuses:

* Open
* In Progress
* Waiting
* Closed

Priorities:

* Low
* Medium
* High

---

## Meeting

Fields:

* id
* client_id
* title
* description
* start_time
* end_time
* meeting_url
* provider
* created_at
* updated_at

Providers:

* Google Meet
* Microsoft Teams
* Zoom

---

## Document

Fields:

* id
* client_id
* project_id
* file_name
* file_path
* mime_type
* uploaded_by
* created_at

---

# Module 1 - Lead Management

Purpose:

Manage all cold leads.

Features:

* Create lead manually
* Edit lead
* Delete lead
* View lead details
* Add notes
* Change status
* Search leads
* Filter leads
* Sort leads
* Bulk actions
* CSV import
* CSV export

Bulk Actions:

* Delete selected
* Change status
* Export selected
* Send email

Lead table columns:

* Company
* Contact
* Email
* Website
* Industry
* Country
* Status
* Last Contacted
* Source

---

# Module 2 - Lead Sources

Purpose:

Import and scrape leads from external sources.

Features:

* Create source
* Edit source
* Delete source
* Run scraper manually
* Schedule scraper execution

Source Fields:

* Name
* URL
* Type
* Filters
* Run Frequency

Source Types:

* Scraper
* CSV
* Manual

All imported records become Leads.

Duplicate detection should be performed based on:

* Website
* Email
* Company Name

---

# Module 3 - Email Outreach

Purpose:

Communicate with leads and clients.

Do NOT build an email server.

Use Resend API.

Features:

* Send email
* Email templates
* Personalization variables
* Email history
* Open tracking (if supported)
* Reply tracking (future)

Template Variables:

* {company_name}
* {contact_name}
* {industry}
* {city}
* {website}

AI Features:

* Generate personalized outreach email
* Generate follow-up email
* Summarize company website

---

# Module 4 - Lead Conversion

Purpose:

Convert leads into clients.

Action:

Convert Lead → Client

Process:

1. Create Client
2. Copy lead information
3. Copy notes
4. Copy communication history
5. Copy attachments
6. Archive lead

The conversion should require a single button click.

---

# Module 5 - Client Management

Purpose:

Maintain client records.

Features:

* Client list
* Client detail page
* Notes
* Documents
* Meetings
* Projects
* Tickets
* Communication history

Actions:

* Edit client
* Delete client
* Schedule meeting
* Create project
* Upload document
* Send email

---

# Module 6 - Meeting Scheduling

Purpose:

Schedule meetings with clients.

Integrations:

* Google Calendar
* Google Meet

Future:

* Zoom
* Microsoft Teams

Features:

* Create meeting
* Send invitation
* Generate meeting link
* Meeting reminders

---

# Module 7 - Client Intake Forms

Purpose:

Collect project information from clients.

Features:

* Form builder
* Custom forms
* File uploads
* Client submissions

Example Form:

Website Project Intake

Fields:

* Business Name
* Services
* Target Audience
* Competitors
* Brand Colors
* Logo Upload
* Content Upload

Submission data should be attached to the client profile.

---

# Module 8 - Document Management

Purpose:

Store client files.

Features:

* Upload document
* Download document
* Delete document
* Search documents

Supported files:

* PDF
* DOCX
* XLSX
* PNG
* JPG
* ZIP

Storage:

Cloudflare R2

Documents belong to:

* Client
* Project

---

# Module 9 - Project Management

Purpose:

Track client work.

Keep this module simple.

Features:

* Create project
* Update project status
* Progress tracking
* Notes

Do NOT build:

* Kanban boards
* Complex task systems
* Time tracking

Project View:

* Project Details
* Documents
* Notes
* Meetings
* Tickets

---

# Module 10 - Client Portal

Purpose:

Allow clients to access their information.

Authentication:

Separate client login.

Clients can:

* View projects
* View documents
* Submit tickets
* View meetings
* Complete intake forms

Clients cannot:

* Access other clients
* Access administrative settings

---

# Module 11 - Ticket System

Purpose:

Provide lightweight support.

Features:

* Create ticket
* Reply to ticket
* Close ticket
* Reopen ticket

Ticket Categories:

* Website Changes
* Bug Report
* Question
* Other

Notifications:

* New ticket
* Ticket replied
* Ticket closed

---

# Module 12 - Notifications

Purpose:

Alert the owner of important activity.

Notification Types:

* New lead imported
* New client created
* Meeting reminder
* New ticket
* New document uploaded
* Form submission received

Delivery:

* In-app notifications

Future:

* Email notifications

---

# Module 13 - Automation Engine

Purpose:

Reduce repetitive work.

Automation 1:

When lead is imported:

* Generate company summary

Automation 2:

When lead inactive for 7 days:

* Create reminder

Automation 3:

When meeting scheduled:

* Create follow-up task

Automation 4:

When lead converted:

* Create onboarding checklist

Automation 5:

When project completed:

* Request testimonial

Automation 6:

When intake form submitted:

* Notify owner

---

# Dashboard

Widgets:

* New Leads
* Active Leads
* Active Clients
* Open Projects
* Upcoming Meetings
* Open Tickets
* Recent Activity

Dashboard should provide a quick overview of business activity.

---

# Permissions

Version 1:

Single-user system only.

No employee roles.

No team management.

Future versions may support:

* Admin
* Staff
* Client

---

# User Experience Rules

Every screen must:

* Be simple
* Avoid clutter
* Require minimal clicks
* Use plain language
* Prioritize speed

Rule:

If a feature adds complexity without saving significant time, do not build it.

---

# MVP Scope

Must Build:

* Authentication
* Leads
* Lead Sources
* CSV Import/Export
* Email Sending
* Lead Conversion
* Clients
* Documents
* Meetings
* Projects
* Dashboard
* Notifications

Build Later:

* Ticket System
* Client Portal
* AI Features
* Automation Engine
* Advanced Integrations

Goal:

Create a usable internal operating system for acquiring, converting, and managing clients from a single dashboard.

