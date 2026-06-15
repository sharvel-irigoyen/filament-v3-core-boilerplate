# Filament Core Boilerplate

> Plataforma base (boilerplate) construida sobre **Laravel 12** + **Filament 3**, con autenticación robusta, gestión de roles y permisos, auditoría completa, y perfiles de usuario. Lista para ser clonada y utilizada como base para futuros proyectos.

---

## 📋 Tabla de Contenidos

- [Stack Tecnológico](#-stack-tecnológico)
- [Funcionalidades Incluidas](#-funcionalidades-incluidas)
- [Instalación Rápida (Local)](#-instalación-rápida-local)
- [Despliegue con Docker](#-despliegue-con-docker)

---

## 🛠 Stack Tecnológico

### Backend
| Tecnología | Rol |
|---|---|
| PHP 8.4 | Lenguaje principal |
| Laravel ^12.0 | Framework HTTP + ORM + Queue |
| Filament ^3.3 | Panel de administración |
| Spatie Permission | Roles y permisos granulares |
| Spatie Activity Log | Auditoría de modelos |
| Spatie Media Library | Gestión de archivos/media |
| Spatie CSP | Content Security Policy |
| Spatie Health | Monitoreo del sistema |
| Filament Shield | RBAC visual en el panel |
| Filament Breezy | Perfil, 2FA, sesiones |
| filament-impersonate | Impersonación de usuarios |
| filament-excel | Exportación Excel |
| filament-log-viewer | Visor de logs en tiempo real |

### Infraestructura (Docker)
- `boilerplate-php`: Servidor PHP-FPM + Migraciones automáticas
- `boilerplate-worker`: Procesador de colas asíncronas
- `boilerplate-nginx`: Reverse proxy / servidor web
- `boilerplate-db`: Base de datos MySQL 8
- `boilerplate-redis`: Cache + Queue broker

---

## ✨ Funcionalidades Incluidas

- **Panel de Administración (Filament)**: Interfaz de usuario rica y reactiva.
- **Roles y Permisos (Shield)**: Configuración automática de roles, incluyendo un `super_admin` por defecto.
- **Autenticación (Breezy)**: Soporte de 2FA y gestión de sesiones activas.
- **Impersonación**: Permite a los super administradores tomar el control de otras cuentas para soporte.
- **Logs y Auditoría**: Registro de actividad en el sistema y un visor de logs en el navegador.
- **Monitoreo (Health)**: Panel de verificación de salud del servidor y base de datos.
- **Docker Compose**: Entorno completo de producción.

---

## 🚀 Instalación Rápida (Local)

```bash
# 1. Clonar el repositorio
git clone <tu-repositorio>
cd boilerplate

# 2. Setup automático (instala deps, genera clave, migra, build assets)
composer setup

# 3. Iniciar servidor de desarrollo con todos los servicios en paralelo
composer dev
```

El comando `composer dev` levanta en paralelo:
- `php artisan serve` — Servidor HTTP
- `php artisan queue:listen` — Worker de colas
- `php artisan pail` — Visor de logs en terminal
- `npm run dev` — Vite HMR

> **Acceso:** `http://localhost:8000/admin`

---

## 🐳 Guía de Despliegue para TI (Producción)

### Paso 1: Configurar .env
```bash
cp .env.example .env
nano .env
```
Configura los valores de base de datos, Redis, correo SMTP y URL base.

### Paso 2: Ejecutar el Script de Despliegue
Este script construirá imágenes, levantará servicios, e instalará dependencias automáticamente.
```bash
chmod +x deploy.sh
./deploy.sh
```

### Paso 3: Crear el Usuario Administrador Inicial
```bash
docker exec -it --user root boilerplate-php php artisan shield:super-admin
```

### Paso 4: Generar y Asignar Permisos del Panel
```bash
docker exec -it --user root boilerplate-php php artisan shield:generate --all
```
