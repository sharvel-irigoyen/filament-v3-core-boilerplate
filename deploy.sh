#!/bin/bash
set -e

echo "🚀 Iniciando despliegue para Boilerplate..."

# 1. Actualizar código fuente
echo "⬇️  Bajando últimos cambios de Git..."
git fetch origin
git reset --hard origin/master

# 2. Validar APP_KEY
echo "🔑 Verificando APP_KEY..."
if [ ! -f .env ]; then
    echo "⚠️  No se encontró .env, creando desde .env.example..."
    cp .env.example .env
fi

if ! grep -q "^APP_KEY=base64:" .env; then
    echo "⚙️  Generando APP_KEY..."
    NEW_KEY="base64:$(openssl rand -base64 32)"
    sed -i "s|^APP_KEY=.*|APP_KEY=${NEW_KEY}|" .env
    echo "✅ APP_KEY guardada."
else
    echo "✅ APP_KEY ya existe."
fi

# 2.5 Configurar el mapeo de usuario para evitar problemas de permisos de Docker en cualquier servidor
echo "👤 Configurando mapeo de usuario para Docker..."
CURRENT_USER=$(whoami)
CURRENT_UID=$(id -u)
CURRENT_GID=$(id -g)

# Si se ejecuta como root (UID=0), usar los defaults de www-data del Dockerfile
# ya que root ya existe dentro del contenedor y no se puede volver a crear.
if [ "$CURRENT_UID" = "0" ]; then
    echo "⚠️  Ejecutando como root — usando usuario www-data dentro del contenedor."
    CURRENT_USER="www-data"
    CURRENT_UID="33"
    CURRENT_GID="33"
fi

if ! grep -q "^DOCKER_UID=" .env; then
    echo -e "\n# Docker Host User Mapping" >> .env
    echo "DOCKER_USER=${CURRENT_USER}" >> .env
    echo "DOCKER_UID=${CURRENT_UID}" >> .env
    echo "DOCKER_GID=${CURRENT_GID}" >> .env
else
    sed -i "s/^DOCKER_USER=.*/DOCKER_USER=${CURRENT_USER}/" .env
    sed -i "s/^DOCKER_UID=.*/DOCKER_UID=${CURRENT_UID}/" .env
    sed -i "s/^DOCKER_GID=.*/DOCKER_GID=${CURRENT_GID}/" .env
fi

# 3. Permisos del proyecto en el host
# Cuando se ejecuta como root, git pull deja los archivos con dueño root:root.
# Necesitamos que los directorios de escritura sean accesibles para www-data (UID 33)
# dentro del contenedor.
echo "🔒 Ajustando permisos del proyecto..."
chown -R 33:33 storage bootstrap/cache 2>/dev/null || true
chmod -R 775 storage bootstrap/cache

# 4. Construir imágenes inmutables (PHP + Nginx) — ignora override de dev
echo "🏗️  Construyendo imágenes y desplegando..."
docker compose -f docker-compose.yml up -d --build --remove-orphans

# Esperamos un poco para asegurarnos de que el contenedor esté corriendo (no necesariamente healthy)
sleep 5

# 5. Instalando dependencias PHP
# Usamos --user root porque los archivos del volumen montado pertenecen a root en el host.
# Sin esto, www-data no puede escribir en vendor/ ni node_modules/.
echo "📦 Instalando dependencias PHP de Composer..."
docker compose -f docker-compose.yml exec -T --user root app composer install --no-interaction --prefer-dist --optimize-autoloader

# 5.5 Construir assets (Vite / Filament)
echo "📦 Instalando dependencias NPM y construyendo assets..."
docker compose -f docker-compose.yml exec -T --user root app npm install
docker compose -f docker-compose.yml exec -T --user root app npm run build

# 6. Migraciones (una sola vez — evita race conditions con réplicas)
echo "📦 Ejecutando migraciones..."
docker compose -f docker-compose.yml exec -T --user root app php artisan migrate --force --no-interaction

# 6.5 Optimización de caché de Laravel para producción
echo "⚡ Optimizando cachés de producción..."
docker compose -f docker-compose.yml exec -T --user root app php artisan optimize
docker compose -f docker-compose.yml exec -T --user root app php artisan view:cache
docker compose -f docker-compose.yml exec -T --user root app php artisan filament:cache-components
docker compose -f docker-compose.yml exec -T --user root app php artisan icons:cache

# 6.6 Restaurar permisos de escritura para www-data en runtime
# PHP-FPM corre como www-data y necesita escribir en storage/ y bootstrap/cache/
echo "🔒 Restaurando permisos de runtime para www-data..."
docker compose -f docker-compose.yml exec -T --user root app chown -R www-data:www-data storage bootstrap/cache
docker compose -f docker-compose.yml exec -T --user root app chmod -R 775 storage bootstrap/cache

# 7. Esperar a que PHP-FPM esté healthy
echo "⏳ Esperando a que el backend esté listo y healthy..."
timeout 180 sh -c 'until docker inspect --format="{{.State.Health.Status}}" app 2>/dev/null | grep -q healthy; do sleep 3; echo "  ...esperando"; done'

# 8. Recargar Nginx (zero-downtime — las conexiones activas no se cortan)
echo "🔄 Recargando Nginx..."
docker compose -f docker-compose.yml exec -T web nginx -s reload || true

# 8. Limpieza de imágenes huérfanas
echo "🧹 Limpiando imágenes viejas..."
docker image prune -f

echo "✅ ¡DESPLIEGUE FINALIZADO CON ÉXITO! 🎉"
