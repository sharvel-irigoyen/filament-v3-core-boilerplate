# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

### Local Development
```bash
composer setup   # First-time setup: install deps, generate key, migrate, build assets
composer dev     # Start all services: PHP server, queue worker, log viewer, Vite HMR
```

### Testing
```bash
composer test              # Run all tests (Pest 3) — clears config cache first
php artisan test tests/Feature/SomeTest.php  # Run a single test file
php artisan test --coverage
```
Tests use an in-memory SQLite database (configured in `phpunit.xml`).

### Linting
```bash
./vendor/bin/pint          # Format code (Laravel Pint — PSR-12 + Laravel preset)
./vendor/bin/pint --test   # Check formatting without modifying files
```

### Assets
```bash
npm run dev        # Vite dev server with HMR
npm run build      # Production build
npm run mjml:build # Compile MJML email templates
```

### Docker (staging/production)
```bash
docker compose up -d --build
docker compose exec app php artisan migrate --force
docker compose exec app php artisan optimize

# Full deploy manually
git pull && docker compose build app && docker compose up -d --remove-orphans
docker compose exec app php artisan migrate --force && docker compose exec app php artisan optimize

# Rollback
./rollback.sh
```

### Permissions
```bash
php artisan shield:generate --all        # Generate Shield permissions for all resources
php artisan shield:super-admin --user=1  # Grant super_admin to a user by ID
```

## Architecture

### Overview
This is a **Filament 3 admin panel** (Laravel 12, PHP 8.4) with no public-facing routes — all traffic redirects to `/admin`. The Filament panel (`app/Providers/Filament/AdminPanelProvider.php`) is the entry point for all features. There are no API routes.

**The app UI and domain data (navigation groups, import column headers, descriptions) are in Spanish.**

### Key Packages
- **Filament 3** — all admin UI, forms, tables, pages
- **Filament Shield** — UI for managing Spatie Permissions
- **Filament Breezy** — profile page, 2FA (TOTP), browser sessions
- **Filament Impersonate** — admin can impersonate any user
- **Spatie Laravel Permission** — RBAC with roles and granular permissions
- **Spatie Activity Log** — automatic audit trail for User model changes
- **Spatie Media Library** — file/avatar handling (supports AWS S3)
- **Spatie Health** — system health checks (DB, disk, env, optimizer, scheduler)
- **Spatie CSP** — Content Security Policy headers
- **Laravel Reverb** — WebSocket broadcasting

### Equifax Integration
The central feature. `app/Services/EquifaxService.php` queries Equifax's credit report API.

- **Entry point:** `EquifaxService::getReport(string $document, DocumentType $documentType, PersonType $personType)`
- **Enums:** `app/Enums/Equifax/DocumentType` (RUC=6, DNI=1) and `PersonType` (JURIDICA=1, NATURAL=2) — both implement `HasOptions` for Filament selects.
- **Caching:** 24-hour Redis cache per document query (disabled in mock mode).
- **Mock mode:** When `EQUIFAX_API_MOCK=true`, loads JSON from `storage/app/mock-equifax-response-{$document}.json` (fallback: `mock-equifax-response.json`). Use this for local development without hitting the real API.
- **Error handling:** Specific exceptions for 404, 401/403, timeouts, and API logical errors. 3 retries with 100ms backoff.
- **Response structure:** Data organized by block `Codigo` (100=query metadata, 822=credit score, 861=payment behavior, 863=credit register, 857=unpaid debts, 865=executive summary, etc.). Full mapping in `docs/equifax-api.md`.
- **Config:** `config/services.php` → `equifax.url`, `equifax.key`, `equifax.mock`.

### EquifaxPage (Livewire Component)
`app/Filament/Pages/EquifaxPage.php` — uses a **custom Blade layout** (`resources/views/layouts/custom.blade.php`) instead of Filament's default, rendering a full-screen report viewer.

- **Views:** Two states toggled by `$currentView`: `modal` (search form) and `dashboard` (results).
- **Sub-views** in `resources/views/filament/pages/inc/`: `dashboard`, `identificacion`, `endeudamiento-historico`, `rcc-por-cuentas`, `otras-deudas-impagas`, `consultas`, and more.
- **Frontend:** Alpine.js for interactivity, ApexCharts for credit score donut, Tailwind CSS responsive design.
- **Assets:** Custom JS in `resources/js/custom-pages.js`, bundled via Vite into the custom layout.

### RBAC Model
- The `super_admin` role bypasses all permission checks.
- All other roles need explicit Shield-generated permissions (e.g., `page_Dashboard`, `view_user`, etc.).
- Non-`super_admin` users cannot see or assign the `super_admin` role — enforced in `UserResource` queries and form validation.
- `canAccessPanel()` on User requires either `super_admin` role or `page_Dashboard` permission.
- Policies in `app/Policies/`: `UserPolicy`, `ActivityPolicy`, `RolePolicy`.

### Domain Model
- **User** is the only model (`app/Models/User.php`). Has `business_unit` field (`On Empresas` | `On Negocios`), soft deletes, avatar support, activity logging, and 2FA.
- **Project**, **BillingLog**, **Payment** models exist but are skeletal (in progress).

### Services
- `EquifaxService` — Equifax API client (see above).
- `PasswordService` — generates secure passwords (excludes ambiguous chars), queues `WelcomeMail` / `PasswordChangedMail`.

### User Import/Export
- **Import (Maatwebsite):** `app/Imports/UsersImport.php` — queue-based, chunked (100 rows), expects Spanish headers (`nombre`, `correo_electronico`, `contrasena`, `rol`, `unidad_de_negocio`). Uses `updateOrCreate` by email, sends welcome email only to new users.
- **Import (Filament native):** `app/Filament/Imports/UserImporter.php` — column mapping UI.
- **Export:** `app/Exports/BaseExport.php` applies corporate styles via `HasCorporateStyles` (blue #0066CC header).

### Async Processing
- Queue driver: **Redis** (also used for cache).
- Worker started automatically by `composer dev` and runs as a separate container in Docker.
- Emails (welcome, password changed) and bulk imports are queued.

### Email
- `app/Mail/WelcomeMail.php` and `PasswordChangedMail.php`.
- Templates use MJML (`resources/mjml/`) compiled to Blade via `npm run mjml:build`.

### Content Security Policy
`app/Support/Csp/Policies/FilamentPolicy.php` extends Spatie CSP to allow Filament, Livewire, Alpine.js, and Reverb. Update this file when adding new CDN resources or inline scripts.

### Infrastructure (Docker)
| Container | Role |
|---|---|
| `web` | Nginx reverse proxy → PHP-FPM |
| `app` | PHP 8.4-FPM (app) |
| `worker` | Queue worker |
| `db` | MySQL 8.0 |
| `redis` | Redis 7 (cache + queue) |

All containers share the `proxy_net` external Docker network (create before first deploy: `docker network create proxy_net`).

### Environment
Key `.env` variables beyond Laravel defaults:
- `APP_REDIRECT_HTTPS` — enforces HTTPS redirect
- `FILAMENT_FILESYSTEM_DISK` — disk for Filament media (defaults to `public`, set to `s3` for production)
- `EQUIFAX_API_URL`, `EQUIFAX_API_KEY` — Equifax API credentials
- `EQUIFAX_API_MOCK` — set `true` to use mock JSON responses from `storage/app/`
- `REVERB_*` — WebSocket server config
- Standard `DB_*`, `REDIS_*`, `MAIL_*`, `AWS_*` variables
