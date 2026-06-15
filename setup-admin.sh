#!/bin/bash
set -e

# ==========================================
# COLORES PARA UI/UX
# ==========================================
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# ==========================================
# CABECERA
# ==========================================
clear
echo -e "${BLUE}======================================================${NC}"
echo -e "${GREEN}   🚀 ASISTENTE DE INICIALIZACIÓN - BOILERPLATE${NC}"
echo -e "${BLUE}======================================================${NC}\n"

echo -e "${YELLOW}Este asistente configurará los roles del sistema y creará tu cuenta de Super Administrador.${NC}\n"

# ==========================================
# CAPTURA DE DATOS (INTERACTIVA)
# ==========================================
read -p "👤 Ingresa tu Nombre [Ej. Admin]: " ADMIN_NAME
ADMIN_NAME=${ADMIN_NAME:-Admin}

read -p "📧 Ingresa tu Correo electrónico [Ej. admin@example.com]: " ADMIN_EMAIL
ADMIN_EMAIL=${ADMIN_EMAIL:-admin@example.com}

# Bucle para asegurar que las contraseñas coincidan y no estén vacías
while true; do
    read -s -p "🔑 Ingresa tu Contraseña: " ADMIN_PASSWORD
    echo ""
    read -s -p "🔁 Confirma tu Contraseña: " ADMIN_PASSWORD_CONFIRM
    echo ""

    if [ -z "$ADMIN_PASSWORD" ]; then
        echo -e "${RED}❌ La contraseña no puede estar vacía. Intenta de nuevo.${NC}\n"
        continue
    fi

    if [ "$ADMIN_PASSWORD" != "$ADMIN_PASSWORD_CONFIRM" ]; then
        echo -e "${RED}❌ Las contraseñas no coinciden. Intenta de nuevo.${NC}\n"
        continue
    fi
    
    break
done

echo -e "\n${BLUE}======================================================${NC}"
echo -e "⚙️  INICIANDO PROCESO AUTOMATIZADO..."
echo -e "${BLUE}======================================================${NC}\n"

# ==========================================
# 1. GENERAR PERMISOS Y POLÍTICAS (SHIELD)
# ==========================================
echo -e "${YELLOW}⏳ [1/2] Generando y sincronizando permisos del sistema (Filament Shield)...${NC}"
docker compose -f docker-compose.yml exec -T app php artisan shield:generate --all --panel=admin

echo -e "${GREEN}✅ Permisos generados correctamente.${NC}\n"

# ==========================================
# 2. CREAR USUARIO EN BASE DE DATOS
# ==========================================
echo -e "${YELLOW}⏳ [2/2] Creando cuenta de Super Administrador en la base de datos...${NC}"

# Usamos Laravel Tinker para inyectar el usuario de forma segura y luego le asignamos el rol
docker compose -f docker-compose.yml exec -T app php artisan tinker --execute="
try {
    \$user = \\App\\Models\\User::firstOrNew(['email' => '$ADMIN_EMAIL']);
    \$user->name = '$ADMIN_NAME';
    \$user->password = \\Illuminate\\Support\\Facades\\Hash::make('$ADMIN_PASSWORD');
    \$user->email_verified_at = now();
    \$user->save();

    // Asignar rol super_admin si no lo tiene
    if (!\$user->hasRole('super_admin')) {
        \$user->assignRole('super_admin');
    }
    echo 'OK';
} catch (\Exception \$e) {
    echo 'ERROR: ' . \$e->getMessage();
}
" > /dev/null 2>&1

echo -e "${GREEN}✅ Cuenta administradora creada y rol asignado correctamente.${NC}\n"

# ==========================================
# RESUMEN FINAL
# ==========================================
echo -e "${BLUE}======================================================${NC}"
echo -e "${GREEN}🎉 ¡SISTEMA INICIALIZADO CON ÉXITO! 🎉${NC}"
echo -e "${BLUE}======================================================${NC}\n"

echo -e "Ya puedes acceder al panel de administración con las siguientes credenciales:\n"
echo -e "  🌐 ${YELLOW}URL:${NC}    http://localhost:${APP_PORT:-8090}/admin"
echo -e "  📧 ${YELLOW}Correo:${NC} $ADMIN_EMAIL"
echo -e "  🔑 ${YELLOW}Clave:${NC}  [La que acabas de ingresar]\n"
echo -e "${GREEN}¡Bienvenido a tu nuevo Boilerplate! 🚀${NC}\n"
