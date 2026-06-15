#!/bin/sh
set -e

echo "🚀 Entrypoint: Preparando aplicación..."

# -----------------------------------------------
# 1. Storage link (idempotente)
#    El volumen storage/ está montado desde el host,
#    el symlink public/storage -> ../storage/app/public
#    resuelve correctamente
# -----------------------------------------------
php artisan storage:link --force --quiet || true

# -----------------------------------------------
# 2. Optimización de caché (solo producción)
#    Filament genera archivos en bootstrap/cache/
#    que ya tiene permisos 775 desde el Dockerfile
# -----------------------------------------------
if [ "$APP_ENV" = "production" ] && [ "$SKIP_CACHE_OPTIMIZE" != "true" ]; then
    echo "⚡ Optimizando caché de producción..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    # Solo ejecutar optimize si Filament está instalado
    if php artisan list | grep -q filament:optimize; then
        php artisan filament:optimize
    fi
fi

echo "✅ Entrypoint: Aplicación lista."

# -----------------------------------------------
# 3. Ejecutar el proceso principal
# -----------------------------------------------
exec "$@"
