# Filament Core Boilerplate

> A starter boilerplate built on **Laravel 12** + **Filament 3**, featuring robust authentication, role and permission management, comprehensive auditing, and user profiles. Ready to be cloned and used as the foundation for future projects.

---

## 📋 Table of Contents

- [Tech Stack](#-tech-stack)
- [Included Features](#-included-features)
- [Quick Start (Local)](#-quick-start-local)
- [Deployment with Docker](#-deployment-with-docker)

---

## 🛠 Tech Stack

### Backend
| Technology | Role |
|---|---|
| PHP 8.4 | Primary language |
| Laravel ^12.0 | HTTP Framework + ORM + Queue |
| Filament ^3.3 | Administration Panel |
| Spatie Permission | Granular roles and permissions |
| Spatie Activity Log | Model auditing |
| Spatie Media Library | Media/file management |
| Spatie CSP | Content Security Policy |
| Spatie Health | System monitoring |
| Filament Shield | Visual RBAC in the panel |
| Filament Breezy | Profiles, 2FA, sessions |
| filament-impersonate | User impersonation |
| filament-excel | Excel exports |
| filament-log-viewer | Real-time log viewer |

### Infrastructure (Docker)
- `app`: PHP-FPM Server + Automated migrations
- `worker`: Asynchronous queue processor
- `web`: Reverse proxy / Web server
- `db`: MySQL 8 Database
- `redis`: Cache + Queue broker

---

## ✨ Included Features

- **Admin Panel (Filament)**: Rich and reactive user interface.
- **Roles and Permissions (Shield)**: Automated role configuration, including a default `super_admin`.
- **Authentication (Breezy)**: 2FA support and active session management.
- **Impersonation**: Allows super admins to take control of other accounts for support purposes.
- **Logs and Auditing**: Activity logging across the system and an in-browser log viewer.
- **Monitoring (Health)**: Health check panel for the server and database.
- **Docker Compose**: Complete production-ready environment.

---

## 🚀 Quick Start (Local)

```bash
# 1. Clone the repository
git clone <your-repository>
cd boilerplate

# 2. Automated setup (installs dependencies, generates key, runs migrations, builds assets)
composer setup

# 3. Start the development server with all services running in parallel
composer dev
```

The `composer dev` command spins up the following in parallel:
- `php artisan serve` — HTTP Server
- `php artisan queue:listen` — Queue worker
- `php artisan pail` — Terminal log viewer
- `npm run dev` — Vite HMR

> **Access:** `http://localhost:8000/admin`

---

## 🐳 IT Deployment Guide (Production)

### Step 1: Configure .env
```bash
cp .env.example .env
nano .env
```
Configure your database, Redis, SMTP email, and base URL values.

### Step 2: Run the Deployment Script
This script will build images, start services, and install dependencies automatically.
```bash
chmod +x deploy.sh
./deploy.sh
```

### Step 3: Permissions and Administrator Initialization
This interactive wizard will automatically configure panel policies and roles, and create the root Super Administrator account.
```bash
./setup-admin.sh
```
