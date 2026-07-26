#!/usr/bin/env bash
# Launcher do Orbit — usado pelo atalho .desktop gerado por scripts/install.sh
set -euo pipefail

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$PROJECT_DIR"

# Ubuntu 24.04+ restringe user namespaces: sem SUID no chrome-sandbox o
# Electron aborta antes de abrir a janela. O install.sh configura o SUID
# (via sudo); se ainda não estiver configurado, cai para o modo sem sandbox
# para o atalho nunca falhar em silêncio.
SANDBOX="$PROJECT_DIR/vendor/nativephp/desktop/resources/electron/node_modules/electron/dist/chrome-sandbox"
if [ "$(stat -c '%u %a' "$SANDBOX" 2>/dev/null)" != "0 4755" ] \
   && [ "$(sysctl -n kernel.apparmor_restrict_unprivileged_userns 2>/dev/null)" = "1" ]; then
    export ELECTRON_DISABLE_SANDBOX=1
    echo "aviso: chrome-sandbox sem SUID — rodando com ELECTRON_DISABLE_SANDBOX=1 (rode make install para corrigir)"
fi

# --no-dependencies: as deps do Electron já foram instaladas pelo install.sh
CMD="php artisan native:run --no-queue --no-dependencies"

# Sem terminal (clique no atalho): loga em arquivo e avisa que está subindo
# (o primeiro boot leva ~30-60s). Redireciona antes de preparar o banco para
# que erros de migração também caiam no log.
if [ ! -t 1 ]; then
    LOG_DIR="$HOME/.config/orbit-dev"
    mkdir -p "$LOG_DIR"
    exec >> "$LOG_DIR/launcher.log" 2>&1
    echo "── $(date '+%F %T') · iniciando Orbit pelo atalho ──"
    command -v notify-send >/dev/null && notify-send -i "$PROJECT_DIR/resources/icons/orbit.png" "Orbit" "Iniciando… a janela abre em instantes." || true
fi

# ── Banco de uso real ───────────────────────────────────────────
# Fica FORA do repositório: sobrevive a git clean, re-clone ou apagar a pasta
# do projeto. O database/database.sqlite do repo é descartável e serve ao
# make dev / make web / make test, que não exportam DB_DATABASE.
# O Dotenv do Laravel não sobrescreve variável já exportada, e o NativePHP
# repassa o process.env ao PHP — então este export vale para o app inteiro.
ORBIT_DATA_DIR="${ORBIT_DATA_DIR:-$HOME/.local/share/orbit}"
export DB_DATABASE="$ORBIT_DATA_DIR/orbit.sqlite"

if [ ! -f "$DB_DATABASE" ]; then
    echo "→ criando banco de uso real em $DB_DATABASE"
    mkdir -p "$ORBIT_DATA_DIR"
    touch "$DB_DATABASE"
    php artisan migrate --seed --force --no-interaction
else
    php artisan migrate --force --no-interaction
fi

if [ -t 1 ]; then
    exec $CMD
fi

# script aloca o pseudo-TTY que o native:run exige.
exec script -qefc "$CMD" /dev/null
