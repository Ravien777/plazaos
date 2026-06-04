# PlazaOS

Single-user CRM & lead generation tool for solo entrepreneurs. Track leads, clients, projects, meetings, documents, and emails — all in one place.

Built with Laravel 12, Vue 3, Inertia.js, TypeScript, Tailwind CSS, and PostgreSQL.

---

## Quick Start (5 minutes)

```bash
# 1. Clone the repo
git clone <repo-url> plazaos
cd plazaos

# 2. Copy and edit the environment file
cp .env.example .env
# Open .env and fill in your database details (DB_USERNAME, DB_PASSWORD, DB_DATABASE)

# 3. Install dependencies
composer install
npm install && npm run build

# 4. Generate app key + run migrations + seed data
php artisan key:generate
php artisan migrate
php artisan db:seed

# 5. Start the dev server
php artisan serve
# → Open http://localhost:8000 in your browser
# → Login: test@example.com / (check your database or use `php artisan tinker` to set a password)
```

> **Note for local development on this machine:** Use `bash artisan.sh` instead of `php artisan` for any artisan commands (see [Local Dev Setup](#local-dev-setup) below).

---

## Prerequisites

Make sure these are installed on your machine:

| Tool | Version | Why |
|---|---|---|
| [PHP](https://php.net) | 8.4+ | Extensions required: `pdo_pgsql`, `mbstring`, `xml`, `curl`, `zip`, `bcmath`, `intl`, `gd` |
| [PostgreSQL](https://postgresql.org) | 16+ | Primary database |
| [Node.js](https://nodejs.org) | 24+ | Frontend build tools |
| [Composer](https://getcomposer.org) | latest | PHP package manager |
| [Redis](https://redis.io) | 7+ | Queue and cache (production only, optional for local dev) |

### Check your setup

```bash
php -v          # Should show PHP 8.4.x
composer -V     # Should show Composer 2.x
node -v         # Should show v24.x
npm -v          # Should show 10.x or 11.x
psql --version  # Should show psql 16.x
```

---

## Local Dev Setup (this machine)

This project has a **local quirk**: the system PHP (8.4.21) was installed without the standard PDO extensions. A wrapper script `bash artisan.sh` loads the missing extensions from `docker/8.4/ext-override/`.

**Always use `bash artisan.sh` instead of `php artisan`** when running artisan commands:

```bash
# ✅ Correct
bash artisan.sh migrate
bash artisan.sh test
bash artisan.sh make:model Lead

# ❌ Wrong — will give "Class 'PDO' not found"
php artisan migrate
```

### Start PostgreSQL

```bash
bash bin/pg-start.sh
# → PostgreSQL starts on port 5433 with Unix socket at /run/user/1000
```

### Run everything (dev mode)

```bash
composer run dev
```

This launches 4 parallel processes in a single terminal:
- **Server** — `php artisan serve` on port 8000
- **Queue** — `php artisan queue:listen` processes background jobs
- **Logs** — `php artisan pail` shows real-time log output
- **Vite** — Frontend hot-reload on port 5173

### Stop PostgreSQL

```bash
bash bin/pg-stop.sh
```

### Build frontend assets (for production)

```bash
npm run build
```

---

## Project Structure

| Directory | What's inside |
|---|---|
| `app/Http/Controllers` | Request handlers — receive HTTP requests, call services, return responses |
| `app/Http/Requests` | Form validation rules per action |
| `app/Models` | Eloquent database models (Lead, Client, Project, Meeting, Note, Document, etc.) |
| `app/Services` | Business logic — complex operations live here, not in controllers |
| `app/Actions` | Single-purpose operations (ConvertLeadToClient, UploadDocument, SendEmail) |
| `app/Policies` | Authorization rules (who can view/create/update/delete each resource) |
| `app/Enums` | PHP enums (LeadStatus, ProjectStatus, MeetingStatus, SourceType) |
| `app/Jobs` | Background queue jobs (ImportLeadsJob, ScrapeLeadSourceJob) |
| `app/Http/Middleware` | HTTP middleware (HandleInertiaRequests, SecurityHeaders) |
| `resources/js` | Vue 3 + TypeScript frontend |
| `resources/js/Pages` | Inertia.js page components (one per route) |
| `resources/js/Components` | Reusable Vue components |
| `resources/js/Types` | Shared TypeScript interfaces |
| `routes/web.php` | All web routes |
| `routes/console.php` | Artisan console commands and scheduled tasks |
| `database/migrations` | Database schema changes (one file per change) |
| `database/factories` | Test data factories (generate fake records) |
| `database/seeders` | Seed data for development |
| `tests` | PHPUnit tests |
| `config` | All Laravel configuration files |
| `bin` | Utility scripts (deploy, pg-start, pg-stop, php) |
| `supervisor` | Production process manager configs |
| `docker` | PostgreSQL data directory + PHP extension overrides |
| `.github/workflows` | CI/CD pipeline (GitHub Actions) |

---

## Common Commands (Cheat Sheet)

### Database

| Task | Command |
|---|---|
| Run migrations | `bash artisan.sh migrate` |
| Rollback last migration | `bash artisan.sh migrate:rollback` |
| Refresh (rollback + migrate) | `bash artisan.sh migrate:fresh` |
| Refresh + seed | `bash artisan.sh migrate:fresh --seed` |
| Seed the database | `bash artisan.sh db:seed --force` |
| Show migration status | `bash artisan.sh migrate:status` |

### Code Generation

| Task | Command |
|---|---|
| Model + migration + factory | `bash artisan.sh make:model -m -f Lead` |
| Resource controller | `bash artisan.sh make:controller LeadController --resource` |
| Migration | `bash artisan.sh make:migration add_status_to_leads_table` |
| Service | (create manually in `app/Services`) |
| Policy | `bash artisan.sh make:policy LeadPolicy --model=Lead` |
| Form request | `bash artisan.sh make:request StoreLeadRequest` |

### Testing

| Task | Command |
|---|---|
| Run all tests | `bash artisan.sh test` or `composer run test` |
| Run a specific test file | `bash artisan.sh test --filter=LeadControllerTest` |
| Run a single test method | `bash artisan.sh test --filter="test_store_creates_lead"` |
| Run without coverage | `bash artisan.sh test --no-coverage` |

### Debugging

| Task | Command |
|---|---|
| Interactive shell (Tinker) | `bash artisan.sh tinker` |
| List all routes | `bash artisan.sh route:list` |
| Clear config cache | `bash artisan.sh config:clear` |
| Show all registered commands | `bash artisan.sh list` |

### Queue

| Task | Command |
|---|---|
| Process jobs (once) | `bash artisan.sh queue:work --once` |
| Process jobs (continuously) | `bash artisan.sh queue:work` |
| Show failed jobs | `bash artisan.sh queue:failed` |

---

## Key Workflows

### Adding a new feature

1. **Migration** — Create the database table or column
2. **Model** — Create the Eloquent model with relationships
3. **Factory** — Create a factory for test data
4. **Seeder** — Create a seeder for development data
5. **Service/Action** — Write the business logic
6. **Policy** — Define authorization rules
7. **Form Request** — Define validation rules
8. **Controller** — Wire it all together
9. **Route** — Register the endpoint in `routes/web.php`
10. **Vue page/component** — Build the UI
11. **Tests** — Write tests for the controller + service
12. **Verify** — `bash artisan.sh test` — all must pass

### Converting a Lead to a Client

1. Go to the Lead detail page (`/leads/{id}`)
2. Click **"Convert to Client"**
3. A new Client record is created from the lead data
4. The Lead is archived (status changed to Converted)
5. Notes, documents, and activity history are copied to the new Client
6. You're redirected to the new Client's page

### Importing Leads from CSV

1. Go to **Leads → Import**
2. Upload a CSV file
3. Map the CSV columns to database fields
4. Choose a duplicate strategy (skip existing or update existing)
5. The import runs in the background (queue job)
6. You can check progress on the import page

### Sending an Email

1. Go to a Lead, Client, or Project detail page
2. Click the **Email** tab
3. Pick a template or write a custom message
4. Click **Send**
5. The email is sent via Resend and saved to history

---

## Testing

This project uses **PHPUnit 11** with 260 tests (914 assertions).

```bash
# Run everything
composer run test

# Run a specific feature test
bash artisan.sh test --filter=LeadControllerTest

# Run all unit tests only
bash artisan.sh test --testsuite=Unit

# Run all feature tests only
bash artisan.sh test --testsuite=Feature
```

Tests use an **SQLite in-memory database** — no PostgreSQL needed to run them. Migrations run fresh before each test.

| Test location | What it tests |
|---|---|
| `tests/Feature/Http/Controllers/` | HTTP endpoints: status codes, validation, redirects, auth |
| `tests/Feature/Auth/` | Authentication: login, register, password reset |
| `tests/Unit/Services/` | Business logic in service classes |
| `tests/Unit/Actions/` | Single-purpose action classes |
| `tests/Unit/Jobs/` | Queue job logic |

---

## Deployment

### CI/CD Pipeline

Pushing to the `production` branch triggers automatic deployment via GitHub Actions:

1. **Test** — PHP lint, TypeScript check, Vite build, PHPUnit (SQLite in-memory)
2. **Deploy** — SSHes into the VPS and runs `bin/deploy.sh`

Requires these **GitHub secrets** configured in the repo settings:

| Secret | Value |
|---|---|
| `DEPLOY_HOST` | VPS IP address |
| `DEPLOY_USER` | SSH username |
| `DEPLOY_KEY` | SSH private key (the CI uses this to log into the VPS) |

---

### First Time (one-time server setup)

You need a **VPS** (virtual private server). This guide assumes Hetzner, but any Ubuntu 24.04 server works.

**1. Provision the server**

- Create a Hetzner Cloud VPS (recommended: 2 vCPU, 4GB RAM, 40GB SSD)
- Enable SSH key authentication
- Open ports 22 (SSH), 80 (HTTP), 443 (HTTPS) in the firewall

**2. Install software**

```bash
# PHP 8.4 + extensions
sudo apt update && sudo apt install -y php8.4-fpm php8.4-cli php8.4-pgsql \
  php8.4-mbstring php8.4-xml php8.4-curl php8.4-zip php8.4-bcmath \
  php8.4-intl php8.4-redis php8.4-sqlite3 php8.4-gd

# PostgreSQL 16
sudo apt install -y postgresql-16

# Redis
sudo apt install -y redis-server

# Caddy (reverse proxy + auto HTTPS)
sudo apt install -y debian-keyring debian-archive-keyring apt-transport-https
curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/gpg.key' | sudo gpg --dearmor -o /usr/share/keyrings/caddy-stable-archive-keyring.gpg
curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/debian.deb.txt' | sudo tee /etc/apt/sources.list.d/caddy-stable.list
sudo apt update && sudo apt install -y caddy

# Supervisor (process manager for queue worker)
sudo apt install -y supervisor

# Node.js 24
curl -fsSL https://deb.nodesource.com/setup_24.x | sudo bash -
sudo apt install -y nodejs

# Composer
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php --install-dir=/usr/local/bin --filename=composer
rm composer-setup.php
```

**3. Create the database**

```bash
sudo -u postgres psql -c "CREATE DATABASE plazaos;"
sudo -u postgres psql -c "CREATE USER plazaos WITH PASSWORD 'your-strong-password';"
sudo -u postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE plazaos TO plazaos;"
```

**4. Set up the deploy user**

```bash
# Create a dedicated deploy user
sudo adduser deploy
sudo usermod -aG www-data deploy

# Add your SSH public key to ~deploy/.ssh/authorized_keys
# This key is also used as DEPLOY_KEY in GitHub secrets

# Grant supervisorctl access
echo "deploy ALL=(ALL) NOPASSWD: /usr/bin/supervisorctl" | \
  sudo tee /etc/sudoers.d/deploy-supervisor
```

**5. Initial application setup**

```bash
# Create directory structure
sudo mkdir -p /var/www/plazaos/{releases,shared}
sudo chown -R deploy:www-data /var/www/plazaos

# Create shared storage skeleton
mkdir -p /var/www/plazaos/shared/storage/app/{public,private}
mkdir -p /var/www/plazaos/shared/storage/framework/{cache/data,sessions,views}
mkdir -p /var/www/plazaos/shared/storage/logs

# Clone the repo (as the deploy user)
sudo -u deploy git clone \
  --branch production \
  git@github.com:raviensewpal/plazaos.git \
  /var/www/plazaos

# Create the production .env file
cp /var/www/plazaos/.env.example /var/www/plazaos/shared/.env
# Edit /var/www/plazaos/shared/.env with your production values
# (see Environment Variables below)

# Run the deploy script for the first time
sudo -u deploy bash /var/www/plazaos/bin/deploy.sh

# Set proper permissions on bootstrap/cache + storage
sudo chown -R www-data:www-data /var/www/plazaos/shared/storage
sudo chown -R www-data:www-data /var/www/plazaos/current/bootstrap/cache
```

**6. Configure Caddy**

Copy `supervisor/Caddyfile` to `/etc/caddy/Caddyfile` and edit the domain name:

```
yourdomain.com {
    root * /var/www/plazaos/current/public
    php_fastcgi unix//run/php/php8.4-fpm.sock {
        resolve_root_symlink
    }
    header /build/* Cache-Control "public, max-age=31536000, immutable"
    header {
        Strict-Transport-Security "max-age=31536000; includeSubDomains; preload"
        X-Frame-Options "DENY"
        X-Content-Type-Options "nosniff"
        Referrer-Policy "strict-origin-when-cross-origin"
        Permissions-Policy "geolocation=(), microphone=(), camera=()"
        -Server
    }
    encode gzip
    log {
        output file /var/log/caddy/plazaos.log
    }
}
```

Then: `sudo systemctl reload caddy`

**7. Configure Supervisor for the queue worker**

Copy `supervisor/queue-worker.conf` to `/etc/supervisor/conf.d/plazaos-queue.conf`:

```ini
[program:plazaos-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/plazaos/current/artisan queue:work redis --sleep=3 --max-jobs=100 --tries=3 --timeout=60
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/log/plazaos-queue.log
stdout_logfile_maxbytes=10MB
```

Then:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start plazaos-queue:*
```

**8. Set up cron for scheduled tasks**

```bash
sudo -u deploy crontab -e
# Add this line:
* * * * * cd /var/www/plazaos/current && php artisan schedule:run >> /dev/null 2>&1
```

**9. Set up a GitHub deploy key**

The server needs read-only access to the private repo for `git clone` during deploys:

```bash
# Generate a deploy key (as deploy user)
sudo -u deploy ssh-keygen -t ed25519 -f ~deploy/.ssh/github_deploy_key -N ""

# Add the public key to GitHub:
#   Settings → Deploy keys → Add deploy key (read-only)
cat ~deploy/.ssh/github_deploy_key.pub
```

Then configure SSH to use this key for GitHub:

```bash
echo "Host github.com
  IdentityFile ~/.ssh/github_deploy_key
  IdentitiesOnly yes" | sudo -u deploy tee ~deploy/.ssh/config
```

Verify the connection: `sudo -u deploy ssh -T git@github.com`

**10. Visit your domain**

Caddy automatically provisions a Let's Encrypt SSL certificate. Your site should be live at `https://yourdomain.com`.

---

### Environment Variables (Production)

| Variable | Production value | Purpose |
|---|---|---|
| `APP_ENV` | `production` | Environment mode |
| `APP_DEBUG` | `false` | Hide error details |
| `APP_URL` | `https://yourdomain.com` | Base URL for link generation |
| `LOG_LEVEL` | `warning` | Reduce log verbosity |
| `DB_HOST` | `127.0.0.1` | PostgreSQL host |
| `DB_PORT` | `5432` | PostgreSQL port |
| `DB_DATABASE` | `plazaos` | Database name |
| `DB_USERNAME` | (app database user) | Database user |
| `DB_PASSWORD` | (strong password) | Database password |
| `DB_SSLMODE` | `require` | Enforce TLS for database |
| `SESSION_DRIVER` | `redis` | Fast session storage |
| `SESSION_SECURE_COOKIE` | `true` | HTTPS-only cookies |
| `CACHE_STORE` | `redis` | Fast cache |
| `QUEUE_CONNECTION` | `redis` | Async job processing |
| `MAIL_MAILER` | `resend` | Email sending |
| `RESEND_API_KEY` | (your Resend key) | Resend API key |
| `FILESYSTEM_DISK` | `r2` | File storage (Cloudflare R2) |
| `AWS_ACCESS_KEY_ID` | (R2 access key) | R2 credentials |
| `AWS_SECRET_ACCESS_KEY` | (R2 secret key) | R2 credentials |
| `AWS_BUCKET` | (your bucket name) | R2 bucket |
| `AWS_URL` | (R2 public URL) | R2 endpoint |
| `AWS_ENDPOINT` | (R2 S3 API URL) | R2 API endpoint |

### Subsequent Deploys

Just push to the `production` branch:

```bash
git checkout main
git merge my-feature-branch
git push origin main

# Then merge into production
git checkout production
git merge main
git push origin production
```

The GitHub Actions pipeline automatically:

1. Runs tests (lint, type check, build, PHPUnit)
2. SSHes into the VPS
3. Runs `bin/deploy.sh` on the server

The deploy script handles the rest:

- Clones the latest `production` branch into a timestamped release directory
- Copies `.env` and `storage/` from the `shared/` directory
- Installs Composer dependencies (production only)
- Installs npm dependencies and builds frontend assets
- Runs database migrations
- Caches config, routes, views, and events
- Switches the `current` symlink to the new release (zero-downtime)
- Restarts the queue worker and reloads PHP-FPM
- Cleans up old releases (keeps last 5)

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 12, PHP 8.4 |
| Frontend | Vue 3, Inertia.js, TypeScript, Tailwind CSS |
| Database | PostgreSQL 16 |
| Queue | Redis (production) / Database (local dev) |
| Cache | Redis (production) / Database (local dev) |
| File Storage | Cloudflare R2 (S3-compatible) |
| Email | Resend |
| Reverse Proxy | Caddy (auto HTTPS via Let's Encrypt) |
| Process Manager | Supervisor (queue worker) |
| Hosting | Hetzner VPS |
| Testing | PHPUnit 11 |
| CI/CD | GitHub Actions |

---

## Troubleshooting

### "Class 'PDO' not found" / "Driver 'pgsql' not found"

This means you're using `php artisan` directly. Use the wrapper:

```bash
# ✅ Correct
bash artisan.sh migrate

# ❌ Wrong
php artisan migrate
```

### "PostgreSQL connection refused"

The local PostgreSQL might not be running:

```bash
bash bin/pg-start.sh
```

It runs on port **5433** with a Unix socket (not TCP). The `.env` file reflects this:

```
DB_HOST=/run/user/1000
DB_PORT=5433
```

For production, change these to `127.0.0.1` and `5432`.

### Tests fail with database errors

Tests use SQLite in-memory. If they fail, check `phpunit.xml` has:

```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

Then run `composer run test` again.

### "npm run build" fails

Make sure dependencies are installed:

```bash
rm -rf node_modules && npm install
npm run build
```

### 404 on all routes except the homepage

Your web server isn't pointing to the `public/` directory, or `.htaccess` (Apache) / URL rewriting (Caddy/Nginx) isn't configured. With Caddy, the `root` directive must end with `/public`. Check the Caddyfile.

### Queue jobs aren't running

Start the worker:

```bash
bash artisan.sh queue:work
```

In production, check Supervisor:

```bash
sudo supervisorctl status plazaos-queue:*
```

### Emails aren't sending in local dev

Set the mailer to "log" to see emails in the log file:

```
MAIL_MAILER=log
```

Then check `storage/logs/laravel.log` for the email contents.

### "419 Page Expired" on form submissions

This is a CSRF token mismatch. Make sure you:
1. Are accessing the site via `http://localhost:8000` (not `127.0.0.1`)
2. Haven't cleared cookies recently (the session token changes)
3. For Inertia forms, use `@inertia.form` or the `<form>` helper which includes the CSRF token automatically

---

## Architecture Notes

- **Modular monolith** — All code lives in one app, organized by function (Controllers, Services, Actions). No microservices.
- **UUIDs everywhere** — No auto-increment IDs. All models use UUID primary keys via the `HasUuids` trait.
- **Form Requests** — Validation lives in dedicated request classes, not in controllers.
- **Services** — Business logic lives in service classes. Controllers are thin.
- **Actions** — Single-responsibility classes for complex operations (convert lead, upload document, send email).
- **Policies** — Authorization is centralized in policy classes. All controllers call `$this->authorize()`.
- **Strict types** — All PHP files use `declare(strict_types=1)`.
- **Polymorphic relationships** — Notes, Documents, Meetings, and Activities can attach to multiple model types (Lead, Client, Project).
