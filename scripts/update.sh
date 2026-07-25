#!/usr/bin/env bash
# Atualiza o Orbit a partir do origin. Chamado pelo botão em Configurações
# (desacoplado, com --relaunch) ou pelo terminal via `make update`.
#
# Roda FORA do app de propósito: composer install e npm run build reescrevem
# vendor/ e public/build embaixo do processo PHP que serve a janela.
set -uo pipefail

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$PROJECT_DIR"

BRANCH="${ORBIT_BRANCH:-main}"
ORBIT_DB="${ORBIT_DB:-$HOME/.local/share/orbit/orbit.sqlite}"
RELAUNCH=0
[ "${1:-}" = "--relaunch" ] && RELAUNCH=1

# Sem terminal (disparado pelo app): tudo vai para o log.
if [ ! -t 1 ]; then
    LOG_DIR="$HOME/.config/orbit-dev"
    mkdir -p "$LOG_DIR"
    exec >> "$LOG_DIR/update.log" 2>&1
fi

echo "── $(date '+%F %T') · atualizando Orbit ──"

notify() {
    command -v notify-send >/dev/null \
        && notify-send -i "$PROJECT_DIR/resources/icons/orbit.png" "Orbit" "$1" || true
}

fail() {
    echo "ERRO: $1"
    notify "$1"
    exit 1
}

# ── Guardas ─────────────────────────────────────────────────────
# Nunca sobrescrever trabalho local: sem árvore limpa, não há atualização.
[ -z "$(git status --porcelain)" ] \
    || fail "Há alterações locais não commitadas. Commite ou descarte antes de atualizar."

notify "Atualizando… a janela reabre em instantes."

BEFORE="$(git rev-parse HEAD)"

timeout 60 git fetch --quiet origin "$BRANCH" || fail "Não foi possível falar com o GitHub."
git merge --ff-only "origin/$BRANCH" || fail "Não foi possível atualizar sem merge (histórico divergiu)."

AFTER="$(git rev-parse HEAD)"

if [ "$BEFORE" = "$AFTER" ]; then
    echo "já estava atualizado."
else
    CHANGED="$(git diff --name-only "$BEFORE" "$AFTER")"

    # vendor/ e node_modules/ estão no .gitignore: o pull não os traz.
    if grep -qx 'composer.lock' <<< "$CHANGED"; then
        echo "→ composer install"
        composer install --no-interaction --prefer-dist || fail "composer install falhou."
    fi

    if grep -qx 'package-lock.json' <<< "$CHANGED"; then
        echo "→ npm install"
        npm install --no-fund --no-audit || fail "npm install falhou."
    fi
fi

# public/build também é ignorado pelo git — e assets velhos quebram em silêncio,
# sem erro nenhum na tela. Por isso o build roda sempre (leva ~200ms).
echo "→ npm run build"
npm run build || fail "npm run build falhou."

echo "→ migrando bancos"
php artisan migrate --force --no-interaction || fail "migração do banco de desenvolvimento falhou."
if [ -f "$ORBIT_DB" ]; then
    DB_DATABASE="$ORBIT_DB" php artisan migrate --force --no-interaction \
        || fail "migração do banco de uso real falhou."
fi

"$PROJECT_DIR/scripts/check-update.sh" || true

echo "✓ atualizado para $(git rev-parse --short HEAD)"

if [ "$RELAUNCH" = "1" ]; then
    echo "→ reabrindo"
    exec "$PROJECT_DIR/scripts/orbit.sh"
fi

notify "Atualizado para $(git rev-parse --short HEAD)."
