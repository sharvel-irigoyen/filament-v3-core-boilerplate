 #!/bin/bash

# Detener si hay error
set -e

echo "🚨 INICIANDO PROTOCOLO DE ROLLBACK 🚨"
echo "Este script revertirá el código al estado previo al último 'pull' o al commit que especifiques."

# 1. Determinar el Commit destino
TARGET_COMMIT="ORIG_HEAD"

if [ -n "$1" ]; then
    TARGET_COMMIT="$1"
fi

echo "🎯 Objetivo de Rollback: $TARGET_COMMIT"
echo "   (Información del commit:)"
git show -s --format="%h %s (%an)" "$TARGET_COMMIT"

read -p "⚠️  ¿Estás SEGURO de aplicar este rollback? (s/n): " confirm
if [[ "$confirm" != "s" ]]; then
    echo "❌ Cancelado."
    exit 1
fi

# 2. Ejecutar Git Reset
echo "🔙 Revertidos cambios de Git..."
git reset --hard "$TARGET_COMMIT"

# 3. Reconstruir Container (Igual que deploy paso 2)
echo "🏗️  Reconstruyendo imagen (versión anterior)..."
docker compose build on-negocios-php

# 4. Ajustar Permisos (Igual que deploy paso 3)
echo "🛡️  Restaurando permisos..."
if [ -f .env ]; then
    sudo chown :33 .env
    sudo chmod 640 .env
fi
rm -rf node_modules
sudo chown -R 33:33 storage bootstrap/cache vendor public app/Policies

# 5. Reiniciar Servicios
echo "🚀 Reiniciando contenedores con código restaurado..."
docker compose up -d --remove-orphans --force-recreate

echo "⏳ Esperando arranque..."
docker exec on-negocios-php sh -c 'while [ ! -f vendor/autoload.php ]; do sleep 2; done'

# 6. Limpieza de Caché
echo "🧹 Limpiando caché..."
docker exec on-negocios-php php artisan optimize:clear
docker exec on-negocios-php php artisan config:cache
docker exec on-negocios-php php artisan route:cache
docker exec on-negocios-php php artisan view:cache

echo "⚠️  IMPORTANTE: Este script NO revierte migraciones de base de datos automaticaente."
echo "   Si el deploy fallido corrió migraciones, revísalo manualmente con:"
echo "   docker exec -it on-negocios-php php artisan migrate:rollback"

echo "✅ ROLLBACK FINALIZADO. El sistema está en la versión anterior."
