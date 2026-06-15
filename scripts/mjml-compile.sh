#!/bin/bash
# ============================================================
# Compila todos los templates MJML a Blade (*.blade.php)
#
# Uso:
#   ./scripts/mjml-compile.sh          # Compila una vez
#   ./scripts/mjml-compile.sh --watch  # Vigila cambios y recompila
# ============================================================

set -euo pipefail

MJML_DIR="resources/views/emails/mjml"
OUTPUT_DIR="resources/views/emails"

compile() {
    local changed=0

    for src in "$MJML_DIR"/*.mjml; do
        [ -f "$src" ] || continue

        local basename
        basename=$(basename "$src" .mjml)
        local dest="$OUTPUT_DIR/${basename}.blade.php"

        # Compilar MJML → HTML temporal
        local tmp
        tmp=$(mktemp)
        npx mjml "$src" -o "$tmp" 2>/dev/null

        # Solo sobrescribir si el contenido cambió (evita rebuilds innecesarios)
        if [ ! -f "$dest" ] || ! cmp -s "$tmp" "$dest"; then
            mv "$tmp" "$dest"
            echo "  ✓ $basename.mjml → $basename.blade.php"
            changed=$((changed + 1))
        else
            rm "$tmp"
        fi
    done

    if [ "$changed" -eq 0 ]; then
        echo "  ✓ Sin cambios"
    fi
}

echo "━━━ MJML Build ━━━"
compile

if [ "${1:-}" = "--watch" ]; then
    echo ""
    echo "👁  Vigilando cambios en $MJML_DIR/ ..."
    echo "   (Ctrl+C para detener)"
    echo ""

    # Usar inotifywait si está disponible, sino polling
    if command -v inotifywait &>/dev/null; then
        while inotifywait -q -e modify,create "$MJML_DIR"/*.mjml 2>/dev/null; do
            echo ""
            echo "━━━ Recompilando... ━━━"
            compile
        done
    else
        echo "   (inotifywait no disponible, usando polling cada 2s)"
        while true; do
            sleep 2
            compile 2>/dev/null
        done
    fi
fi
