# filament-v3 — Panel de Administración

> Plataforma de administración empresarial construida sobre **Laravel 12** + **Filament 3**, con autenticación robusta, gestión de roles y permisos, auditoría completa, importación/exportación masiva de usuarios e infraestructura lista para producción con Docker.

---

## 📋 Tabla de Contenidos

- [Descripción General](#-descripción-general)
- [Stack Tecnológico](#-stack-tecnológico)
- [Funcionalidades](#-funcionalidades)
- [Arquitectura del Sistema](#-arquitectura-del-sistema)
- [Requisitos](#-requisitos)
- [Instalación Rápida (Local)](#-instalación-rápida-local)
- [Despliegue con Docker](#-despliegue-con-docker)
- [Variables de Entorno](#-variables-de-entorno)
- [Estructura del Proyecto](#-estructura-del-proyecto)
- [Gestión de Roles y Permisos](#-gestión-de-roles-y-permisos)
- [Importación y Exportación de Usuarios](#-importación-y-exportación-de-usuarios)
- [Seguridad](#-seguridad)
- [Monitoreo y Salud del Sistema](#-monitoreo-y-salud-del-sistema)
- [Auditoria (Activity Log)](#-auditoría-activity-log)
- [Pruebas](#-pruebas)
- [Rollback en Producción](#-rollback-en-producción)
- [Comandos Útiles](#-comandos-útiles)

---

## 📌 Descripción General

**filament-v3** es un panel de administración de usuarios y recursos que provee:

- Autenticación segura con soporte para **2FA (Two-Factor Authentication)** y gestión de sesiones por navegador.
- Sistema de **roles y permisos granulares** basado en Spatie Permission + Filament Shield.
- **Impersonación de usuarios** para soporte y depuración (solo `super_admin`).
- **Importación masiva** de usuarios desde Excel/CSV con procesamiento asíncrono en cola.
- **Exportación** de datos con estilos corporativos.
- **Auditoría completa** de acciones sobre modelos.
- Visor de **logs de aplicación** en tiempo real.
- Monitor de **salud del sistema** con checks automatizados.
- **CSP (Content Security Policy)** configurada para Filament/Livewire/Alpine.js.
- Infraestructura **Dockerizada** lista para producción con Nginx, MySQL 8 y Redis 7.

---

## 🛠 Stack Tecnológico

### Backend
| Tecnología | Versión | Rol |
|---|---|---|
| PHP | 8.4 (Docker) / ^8.2 (Composer) | Lenguaje principal |
| Laravel | ^12.0 | Framework HTTP + ORM + Queue |
| Filament | ^3.3 | Panel de administración |
| Spatie Permission | ^6.24 | Roles y permisos |
| Spatie Activity Log | (via rmsramos) | Auditoría de modelos |
| Spatie Media Library | ^3.2 | Gestión de archivos/media |
| Spatie CSP | ^2 | Content Security Policy |
| Spatie Health | ^2.3 | Monitoreo del sistema |
| Filament Shield | ^3.9 | RBAC en el panel |
| Filament Breezy | ^2.6 | Perfil, 2FA, sesiones |
| filament-impersonate | ^3.16 | Impersonación de usuarios |
| filament-excel | ^2.4 | Exportación Excel |
| filament-log-viewer | ^1.2 | Visor de logs en el panel |
| image-optimizer | ^1.6 | Optimización de imágenes |
| Laravel Reverb | ^1.6 | WebSockets (Broadcasting) |
| Flysystem AWS S3 | ^3.0 | Almacenamiento en S3 |
| Filament Daterangepicker | ^3.4 | Filtros por rango de fecha |

### Frontend / Build
| Tecnología | Rol |
|---|---|
| Vite | Bundler de assets |
| Alpine.js | Reactvidad en componentes Livewire/Filament |
| Livewire | Componentización reactiva |

### Infraestructura (Docker)
| Servicio | Imagen | Rol |
|---|---|---|
| `filament-v3-php` | PHP 8.4-FPM (custom) | Servidor PHP-FPM |
| `filament-v3-worker` | PHP 8.4-FPM (custom) | Procesador de colas |
| `filament-v3-nginx` | nginx:alpine | Reverse proxy / web server |
| `filament-v3-db` | mysql:8.0 | Base de datos principal |
| `filament-v3-redis` | redis:7-alpine | Cache + Queue broker |

---

## ✨ Funcionalidades

### 🔐 Autenticación y Perfiles
- **Login estándar** con email y contraseña vía Filament.
- **Two-Factor Authentication (2FA)** opcional/configurable por usuario (TOTP via Filament Breezy).
- **Gestión de sesiones activas por navegador** — el usuario puede ver y revocar sesiones desde su perfil.
- **Página de perfil personalizada** (`MyProfileCustomPage`) con:
  - Actualización de datos personales (nombre, email, teléfono).
  - Cambio de contraseña.
  - Upload de avatar (almacenado en Storage, con URL dinámica).
  - Configuración de 2FA.
  - Gestión de sesiones del navegador.
- **Avatar dinámico** por usuario, recuperado desde `Storage`.

### 👥 Gestión de Usuarios
- **CRUD completo** de usuarios desde el panel (`UserResource`):
  - Campos: Nombre, Email, Teléfono, Contraseña, Roles.
  - Filtros por estado de verificación de email (verificado / no verificado).
  - Búsqueda por nombre, email, teléfono y rol.
  - Ordenamiento por todos los campos principales.
- **Contraseña segura**: Hashing automático con `Hash::make()`. En edición solo se actualiza si se introduce una nueva contraseña.
- **Impersonación de usuarios** (`super_admin` únicamente):
  - Botón "Impersonar" en la tabla de usuarios.
  - Solo visible para `super_admin`, no aparece sobre el propio usuario ni sobre usuarios sin acceso al panel.
  - Registra de vuelta la sesión de impersonación para retornar al admin original.
- **Eliminación masiva (bulk delete)** de usuarios.
- **Ocultamiento de `super_admin`**: Los usuarios no-super_admin no ven en la lista a usuarios con ese rol.

### 🔑 Roles y Permisos (RBAC)
- Basado en **Spatie Laravel Permission** + **Filament Shield**.
- Rol especial `super_admin` con acceso total sin restricciones.
- **Acceso al panel** controlado:
  - `super_admin` → acceso siempre.
  - Otros usuarios → deben tener el permiso `page_Dashboard`.
- **Asignación de roles en formulario de usuario**:
  - Los usuarios no-super_admin no pueden asignar el rol `super_admin`.
  - Lista de roles filtrada según privilegios del autenticado.
- Políticas de acceso implementadas para: `User`, `Activity`, `QueueMonitor`.
- Permisos granulares: `view`, `view_any`, `create`, `update`, `delete` sobre cada recurso.

### 📥 Importación Masiva de Usuarios
- **Importación desde Excel/CSV** (`UsersImport`) con:
  - Fila de cabecera requerida con columnas: `nombre`, `correo_electronico`, `contrasena` (opcional), `telefono` (opcional), `rol` (requerido).
  - Validación de datos por fila (nombre requerido, email válido, rol requerido).
  - **Crear o actualizar** (`updateOrCreate`) por email — idempotente.
  - **Asignación de roles** automática al importar (búsqueda case-insensitive del nombre de rol).
  - **Procesamiento en cola** (`ShouldQueue`) con chunks de 100 filas.
  - **Manejo de errores** por fila (`SkipsOnFailure`) — filas inválidas se registran y se omiten sin detener la importación.
  - Logging detallado de cada fila procesada y errores.
  - **Tabla de filas fallidas** (`failed_import_rows`) para auditoría post-importación.

### 📤 Exportación de Datos
- **Exportación estilizada** (`BaseExport`):
  - Encabezado con fondo azul corporativo (`#0066CC`), texto blanco y negrita.
  - Bordes finos en todo el rango de datos.
- **Plantilla de importación descargable** (`UsersTemplateExport`):
  - Genera un Excel vacío con las cabeceras correctas: `Nombre`, `Correo Electrónico`, `Teléfono`, `Contraseña`, `Rol`.

### 📊 Auditoría (Activity Log)
- Auditoría completa sobre el modelo `User` con **Spatie Activity Log**:
  - Log name: `users`.
  - Registra **todos los campos** del modelo (`logAll()`).
  - Solo registra **diffs** (cambios reales) con `logOnlyDirty()`.
  - No registra logs vacíos (`dontSubmitEmptyLogs()`).
  - Descripción automática: `"User created"`, `"User updated"`, `"User deleted"`.
- **Visor de logs** en el panel de administración (`ActivityLogResource`):
  - Grupo de navegación: **Auditoría**.
  - Badge con conteo de registros.
  - Permisos granulares via Shield: `view`, `view_any`, `create`, `update`, `delete`.
- **Timeline de actividad** en la tabla de usuarios (botón "Activities" por usuario).
- **Pipeline personalizado** (`RenameKeysForTimelinePipe`) para formatear datos del timeline.

### 🩺 Monitoreo del Sistema (Health Checks)
- Integrado con **Spatie Laravel Health** + widget en Filament.
- Checks automáticos configurados:
  | Check | Descripción |
  |---|---|
  | `OptimizedAppCheck` | Verifica que el caché de configuración/rutas esté activo |
  | `DebugModeCheck` | Alerta si `APP_DEBUG=true` en producción |
  | `EnvironmentCheck` | Verifica el entorno (`production` vs `local`) |
  | `DatabaseCheck` | Verifica conectividad con la base de datos |
  | `UsedDiskSpaceCheck` | Monitorea el uso de disco |
  | `ScheduleCheck` | Verifica que el scheduler esté corriendo |
- Acceso restringido a `super_admin` únicamente.

### 📋 Visor de Logs de Aplicación
- **Log Viewer en tiempo real** (`gboquizosanchez/filament-log-viewer`) integrado en el panel.
- Grupo de navegación: **Configuración**.
- Ícono: `heroicon-o-bug-ant`.
- Acceso restringido a `super_admin`.

### 🔔 Notificaciones y Broadcasting
- Sistema de **notificaciones de base de datos** (tabla `notifications`).
- **Laravel Reverb** instalado para broadcasting via WebSockets.
- Soporte para colas de trabajos (`jobs`, `failed_jobs`, `job_batches`).

### 🖼️ Gestión de Media
- Integración con **Spatie Media Library** (tabla `media`).
- Plugin de Filament para gestión visual de archivos adjuntos.
- Soporte para **AWS S3** como disco de almacenamiento alternativo.
- **Optimización automática de imágenes** (`joshembling/image-optimizer`).

---

## 🏗 Arquitectura del Sistema

```
                          ┌─────────────────────┐
                          │    proxy_net (ext)   │
                          │  (Nginx Proxy Mgr)   │
                          └──────────┬──────────┘
                                     │ HTTPS/80
                          ┌──────────▼──────────┐
                          │   filament-v3-nginx   │
                          │   (nginx:alpine)     │
                          └──────────┬──────────┘
                                     │ FastCGI :9000
                    ┌────────────────▼────────────────┐
                    │       filament-v3_net             │
                    │                                  │
          ┌─────────▼────────┐           ┌────────────▼────────┐
          │  filament-v3-php  │           │  filament-v3-worker  │
          │  (PHP 8.4-FPM)   │           │  (Queue Worker)     │
          │  php-fpm :9000   │           │  --tries=3 --t=120  │
          └──────┬───────────┘           └────────────┬────────┘
                 │                                    │
         ┌───────▼──────────────────────────────────▼──────┐
         │               filament-v3_net                     │
         │                                                  │
  ┌──────▼──────┐                              ┌───────────▼──────┐
  │  MySQL 8.0  │                              │  Redis 7-alpine  │
  │  (DB/ORM)   │                              │  (Cache + Queue) │
  └─────────────┘                              └──────────────────┘
```

### Flujo de Petición
1. El tráfico HTTPS llega al **Nginx** externo (proxy_net).
2. **Nginx sidecar** sirve assets estáticos y redirige PHP a `PHP-FPM`.
3. **PHP-FPM** procesa la petición Laravel/Filament/Livewire.
4. Los jobs pesados (importaciones) se despachan a **Redis Queue**.
5. El **Worker** los consume de forma asíncrona con reintentos.

---

## ✅ Requisitos

### Para desarrollo local
- PHP >= 8.2 con extensiones: `pdo`, `pdo_mysql`, `mbstring`, `zip`, `gd`, `xml`, `intl`, `redis`, `exif`
- Composer 2.x
- Node.js 20.x + npm
- SQLite (para tests) o MySQL 8.0

### Para Docker (producción/staging)
- Docker >= 24
- Docker Compose v2
- Red externa `proxy_net` creada (para el reverse proxy)

---

## 🚀 Instalación Rápida (Local)

```bash
# 1. Clonar el repositorio
git clone https://github.com/Electo-Desarrollo/filament-v3.git
cd filament-v3

# 2. Setup automático (instala deps, genera clave, migra, build assets)
composer setup

# 3. Iniciar servidor de desarrollo con todos los servicios
composer dev
```

El comando `composer dev` levanta en paralelo:
- `php artisan serve` — Servidor HTTP
- `php artisan queue:listen` — Worker de colas
- `php artisan pail` — Visor de logs en terminal
- `npm run dev` — Vite HMR

> **Acceso:** `http://localhost:8000/admin`

---

## 🐳 Despliegue con Docker

### Primera vez

```bash
# 1. Copiar configuración
cp .env.example .env

# 2. Configurar variables (DB, Redis, etc.) — ver sección Variables de Entorno

# 3. Crear la red externa del proxy (si no existe)
docker network create proxy_net

# 4. Construir y levantar
docker compose up -d --build

# 5. Esperar que PHP-FPM esté listo y ejecutar migraciones
docker exec filament-v3-php php artisan migrate --force

# 6. Crear el primer super_admin
docker exec -it filament-v3-php php artisan make:filament-user
```

### Actualización / Deploy

```bash
git pull
docker compose build filament-v3-php
docker compose up -d --remove-orphans
docker exec filament-v3-php php artisan migrate --force
docker exec filament-v3-php php artisan optimize
```

### Permisos de Directorios

```bash
# Ajustar ownership para el usuario www-data del contenedor (uid=33)
sudo chown -R 33:33 storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

---

## ⚙️ Variables de Entorno

| Variable | Descripción | Ejemplo |
|---|---|---|
| `APP_NAME` | Nombre de la aplicación | `filament-v3` |
| `APP_ENV` | Entorno de ejecución | `production` |
| `APP_KEY` | Clave de cifrado Laravel | (generada con `artisan key:generate`) |
| `APP_DEBUG` | Modo debug (desactivar en prod) | `false` |
| `APP_URL` | URL base de la aplicación | `https://app.ejemplo.com` |
| `APP_PORT` | Puerto Docker de Nginx | `80` |
| `redirect_https` | Forzar HTTPS | `true` |
| `DB_CONNECTION` | Driver de BD | `mysql` |
| `DB_HOST` | Host de MySQL (Docker: `filament-v3-db`) | `filament-v3-db` |
| `DB_DATABASE` | Nombre de la base de datos | `on_equifax` |
| `DB_USERNAME` | Usuario MySQL | `equifax_user` |
| `DB_PASSWORD` | Contraseña MySQL | `secret` |
| `DB_ROOT_PASSWORD` | Contraseña root MySQL (Docker) | `rootsecret` |
| `REDIS_HOST` | Host Redis (Docker: `filament-v3-redis`) | `filament-v3-redis` |
| `REDIS_PASSWORD` | Contraseña Redis | `redissecret` |
| `QUEUE_CONNECTION` | Driver de colas | `redis` |
| `CACHE_STORE` | Driver de caché | `redis` |
| `FILESYSTEM_DISK` | Disco de almacenamiento | `local` o `s3` |
| `AWS_ACCESS_KEY_ID` | Credencial AWS S3 | — |
| `AWS_SECRET_ACCESS_KEY` | Credencial AWS S3 | — |
| `AWS_BUCKET` | Bucket S3 | — |
| `DOCKER_USER` | Usuario para el contenedor | `www-data` |
| `DOCKER_UID` | UID del usuario Docker | `33` |
| `DOCKER_GID` | GID del usuario Docker | `33` |
| `PROXY_NETWORK` | Nombre de la red proxy externa | `proxy_net` |

---

## 📁 Estructura del Proyecto

```
filament-v3/
├── app/
│   ├── Activitylog/
│   │   └── Pipes/
│   │       └── RenameKeysForTimelinePipe.php   # Pipeline para timeline de auditoría
│   ├── Exports/
│   │   ├── BaseExport.php                       # Exportación con estilos corporativos
│   │   └── UsersTemplateExport.php              # Plantilla de importación descargable
│   ├── Filament/
│   │   ├── Pages/
│   │   │   └── MyProfileCustomPage.php          # Página de perfil personalizada
│   │   ├── Resources/
│   │   │   ├── ActivityLogResource.php          # Recurso de auditoría (CRUD + Shield)
│   │   │   ├── ActivityLogResource/Pages/       # Páginas del log
│   │   │   ├── UserResource.php                 # Gestión completa de usuarios
│   │   │   └── UserResource/Pages/              # Páginas: List, Create, Edit
│   │   └── Widgets/                             # (disponible para widgets futuros)
│   ├── Imports/
│   │   └── UsersImport.php                      # Importación masiva de usuarios (Queue)
│   ├── Models/
│   │   └── User.php                             # Modelo con roles, 2FA, avatar, activitylog
│   ├── Policies/
│   │   ├── ActivityPolicy.php                   # Permisos sobre logs de auditoría
│   │   ├── RolePolicy.php                       # Permisos sobre roles
│   │   └── UserPolicy.php                       # Permisos sobre usuarios
│   ├── Providers/
│   │   ├── AppServiceProvider.php               # Bootstrap: Health, CSP nonce, colores Filament
│   │   └── Filament/
│   │       └── AdminPanelProvider.php           # Configuración del panel admin
│   └── Support/
│       ├── Csp/
│       │   └── Policies/
│       │       └── FilamentPolicy.php           # Política CSP personalizada
│       └── helpers.php
├── database/
│   ├── factories/                               # Factories para testing
│   ├── migrations/                              # 15 migraciones
│   └── seeders/
├── resources/
│   └── css/
│       ├── custom-pages.css                     # Estilos personalizados de páginas
│       └── filament/admin/theme.css             # Tema custom del panel
├── routes/
│   ├── web.php                                  # Rutas web (login, panel)
│   └── console.php                              # Comandos Artisan programados
├── tests/                                       # Tests con Pest
├── Dockerfile                                   # PHP 8.4-FPM con todas las extensiones
├── docker-compose.yml                           # Stack completo (PHP, Worker, Nginx, MySQL, Redis)
├── nginx.conf                                   # Configuración de Nginx
├── php.ini                                      # Configuración PHP personalizada
├── rollback.sh                                  # Script de rollback en producción
└── vite.config.js                               # Configuración de Vite
```

---

## 🔑 Gestión de Roles y Permisos

El sistema implementa RBAC (Role-Based Access Control) mediante **Spatie Laravel Permission** con la integración de **Filament Shield** para la gestión visual desde el panel.

### Roles Predefinidos

| Rol | Descripción |
|---|---|
| `super_admin` | Acceso total. Sin restricciones. Invisible para otros roles. |
| Otros roles | Configurables vía Filament Shield UI |

### Acceso al Panel

Un usuario puede acceder al panel si:
1. Tiene el rol `super_admin`, **O**
2. Tiene el permiso explícito `page_Dashboard`.

### Restricciones de Seguridad

- Un usuario **no puede asignarse ni asignar a otros el rol `super_admin`** a menos que ya lo tenga.
- Los usuarios con rol `super_admin` **no son visibles** para usuarios sin ese rol en la tabla de usuarios.
- Los permisos sobre recursos se definen granularmente via Shield: `view`, `view_any`, `create`, `update`, `delete`, `force_delete`, `restore`, `replicate`, `reorder`.

### Inicializar Permisos de Shield

```bash
php artisan shield:generate --all
php artisan shield:super-admin --user=1
```

---

## 📥 Importación y Exportación de Usuarios

### Importación desde Excel

**Formato del archivo requerido** (columnas en cabecera):

| Columna | Requerido | Descripción |
|---|---|---|
| `nombre` | ✅ | Nombre completo del usuario |
| `correo_electronico` | ✅ | Email único (clave de upsert) |
| `contrasena` | ❌ | Contraseña en texto plano (se hashea automáticamente) |
| `telefono` | ❌ | Número de teléfono |
| `rol` | ✅ | Nombre del rol a asignar (debe existir en el sistema) |

**Características:**
- Procesamiento **asíncrono en cola** (no bloquea la UI).
- **100 filas por chunk** para eficiencia de memoria.
- Filas inválidas se **saltan y registran** sin detener el proceso.
- Si el rol no existe, se registra un `warning` en el log.
- Las filas fallidas quedan en la tabla `failed_import_rows`.

### Descarga de Plantilla

Desde el panel se puede descargar una **plantilla Excel vacía** con las cabeceras correctas para facilitar la preparación del archivo de importación.

### Exportación

- Los exports heredan de `BaseExport` que aplica estilos corporativos automáticamente.
- Encabezados: fondo **azul corporativo (#0066CC)**, texto blanco, negrita.
- Bordes finos en todo el rango de datos.

---

## 🔒 Seguridad

### Content Security Policy (CSP)

Política personalizada (`FilamentPolicy`) configurada para ser compatible con Filament, Livewire y Alpine.js:

| Directiva | Valor | Motivo |
|---|---|---|
| `default-src` | `'self'` | Solo recursos propios |
| `script-src` | `'self'`, `'unsafe-eval'`, `'unsafe-inline'`, nonce | Alpine.js + Livewire |
| `style-src` | `'self'`, `'unsafe-inline'`, `fonts.bunny.net` | Filament inline styles |
| `img-src` | `'self'`, `data:`, `blob:`, `ui-avatars.com` | Avatares y procesamiento |
| `font-src` | `'self'`, `fonts.bunny.net`, `data:` | Tipografías del panel |
| `frame-ancestors` | `'self'` | Prevención de Clickjacking |
| `object-src` | `'none'` | Bloquear plugins |
| `worker-src` | `'self'`, `blob:` | FilePond / Web Workers |

- **CSP Nonce** integrado con Vite (`Vite::useCspNonce()`).
- **HTTPS forzado** en producción con `URL::forceScheme('https')` (configurable via `APP_REDIRECT_HTTPS`).

### Autenticación
- Contraseñas hasheadas con **bcrypt** (12 rounds).
- Sesiones de base de datos encriptadas.
- **CSRF** habilitado en todas las rutas de Filament.
- **2FA via TOTP** opcional por usuario.
- Gestión y revocación de **sesiones por dispositivo/navegador**.

---

## 🩺 Monitoreo y Salud del Sistema

Accesible desde el panel admin en **Configuración → Health** (solo `super_admin`):

| Check | ¿Qué verifica? |
|---|---|
| Optimized App | Caché de config/rutas activo |
| Debug Mode | `APP_DEBUG=false` en producción |
| Environment | Entorno correcto (`production`) |
| Database | Conectividad activa con MySQL |
| Disk Space | Uso de disco < umbral configurado |
| Schedule | Scheduler de Laravel ejecutándose |

---

## 📜 Auditoría (Activity Log)

Todas las acciones sobre el modelo `User` son registradas automáticamente:

- **Creación**: `"User created"` con todos los campos (excepto password).
- **Actualización**: `"User updated"` solo con los campos modificados (diff).
- **Eliminación**: `"User deleted"`.

Los logs son visibles en el panel en **Auditoría → Logs** con soporte para:
- Filtrado por fecha, usuario, evento.
- Vista de **timeline** por usuario individual (botón "Activities").

---

## 🧪 Pruebas

El proyecto utiliza **Pest 3** con los plugins de Laravel y Livewire.

```bash
# Ejecutar todos los tests
composer test

# Solo un archivo
php artisan test tests/Feature/MiTest.php

# Con cobertura (requiere Xdebug/PCOV)
php artisan test --coverage
```

La base de datos de tests usa **SQLite en memoria** (configurado en `phpunit.xml`).

---

## 🔄 Rollback en Producción

En caso de un deploy fallido, ejecutar el script de rollback:

```bash
# Rollback al commit anterior (ORIG_HEAD)
bash rollback.sh

# Rollback a un commit específico
bash rollback.sh abc1234
```

El script:
1. Hace `git reset --hard` al commit destino.
2. Reconstruye la imagen Docker.
3. Restaura permisos de directorios críticos.
4. Reinicia todos los contenedores.
5. Limpia y regenera las cachés (config, routes, views).

> ⚠️ **Importante:** El rollback **NO revierte migraciones de base de datos** automáticamente. Si el deploy ejecutó migraciones, deben revertirse manualmente:
> ```bash
> docker exec -it filament-v3-php php artisan migrate:rollback
> ```

---

## 🛠 Comandos Útiles

```bash
# Generar permisos de Shield para todos los recursos
php artisan shield:generate --all

# Crear usuario super_admin
php artisan shield:super-admin --user={id}

# Limpiar caché completo
php artisan optimize:clear

# Regenerar caché de producción
php artisan optimize

# Ejecutar worker manualmente
php artisan queue:work --tries=3 --timeout=120

# Ver logs en tiempo real (terminal)
php artisan pail

# Acceder al contenedor PHP
docker exec -it filament-v3-php bash

# Ver logs del worker
docker logs filament-v3-worker -f

# Ver logs de Nginx
docker logs filament-v3-nginx -f
```

---

## 📄 Licencia

Este proyecto es de uso privado y propiedad de **Electo Desarrollo**.

---

*Documentación generada automáticamente a partir del análisis del código fuente. Última actualización: Abril 2026.*
