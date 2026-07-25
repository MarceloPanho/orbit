#!/usr/bin/env bash
# Setup do Orbit após clonar do GitHub:
#   instala dependências, prepara o banco, builda os assets e
#   cria o atalho "Orbit" (menu de aplicativos + área de trabalho) com o ícone oficial.
set -euo pipefail

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$PROJECT_DIR"

bold()  { printf '\033[1m%s\033[0m\n' "$*"; }
fail()  { printf '\033[31mERRO:\033[0m %s\n' "$*" >&2; exit 1; }

# ── Pré-requisitos ──────────────────────────────────────────────
command -v php      >/dev/null || fail "PHP não encontrado. Instale PHP 8.3+ (sudo apt install php php-sqlite3 php-xml php-curl php-zip)."
command -v composer >/dev/null || fail "Composer não encontrado. Veja https://getcomposer.org/download/"
command -v npm      >/dev/null || fail "Node/npm não encontrados. Instale Node.js 20+ (https://nodejs.org)."

php -r 'exit(version_compare(PHP_VERSION, "8.3.0", ">=") ? 0 : 1);' \
    || fail "PHP 8.3+ é necessário (encontrado: $(php -r 'echo PHP_VERSION;'))."

# ── Dependências e app ──────────────────────────────────────────
bold "→ Instalando dependências PHP (composer)…"
composer install --no-interaction --prefer-dist

bold "→ Instalando dependências JS (npm)…"
npm install --no-fund --no-audit

if [ ! -f .env ]; then
    bold "→ Criando .env e chave da aplicação…"
    cp .env.example .env
    php artisan key:generate --ansi
fi

bold "→ Preparando banco de dados local…"
[ -f database/database.sqlite ] || touch database/database.sqlite
php artisan migrate --seed --no-interaction

bold "→ Compilando assets (Vite)…"
npm run build

bold "→ Instalando runtime desktop (Electron)…"
(cd vendor/nativephp/desktop/resources/electron && npm install --no-fund --no-audit)

# Ubuntu 24.04+ restringe user namespaces (AppArmor): o helper de sandbox do
# Electron precisa de SUID root, senão o app aborta ao abrir pelo atalho.
SANDBOX="$PROJECT_DIR/vendor/nativephp/desktop/resources/electron/node_modules/electron/dist/chrome-sandbox"
if [ -f "$SANDBOX" ] && [ "$(stat -c '%u %a' "$SANDBOX" 2>/dev/null)" != "0 4755" ] \
   && [ "$(sysctl -n kernel.apparmor_restrict_unprivileged_userns 2>/dev/null)" = "1" ]; then
    bold "→ Configurando sandbox do Electron (pede senha do sudo)…"
    if sudo chown root:root "$SANDBOX" && sudo chmod 4755 "$SANDBOX"; then
        echo "  sandbox configurado."
    else
        echo "  aviso: não foi possível configurar o SUID — o atalho vai rodar sem sandbox do Chrome."
    fi
fi

# ── Atalho com ícone ────────────────────────────────────────────
bold "→ Criando atalho do Orbit…"
ICON_SRC="$PROJECT_DIR/resources/icons/orbit.png"
APPS_DIR="$HOME/.local/share/applications"
mkdir -p "$APPS_DIR"

# caminho absoluto funciona em qualquer ambiente; instalar no tema de ícones
# é opcional (em algumas máquinas ~/.local/share/icons pertence ao root)
ICON_REF="$ICON_SRC"
ICON_DIR="$HOME/.local/share/icons/hicolor/512x512/apps"
if mkdir -p "$ICON_DIR" 2>/dev/null && cp "$ICON_SRC" "$ICON_DIR/orbit.png" 2>/dev/null; then
    ICON_REF="orbit"
    command -v gtk-update-icon-cache >/dev/null && gtk-update-icon-cache -q "$HOME/.local/share/icons/hicolor" 2>/dev/null || true
fi

DESKTOP_FILE="$APPS_DIR/orbit.desktop"
cat > "$DESKTOP_FILE" <<EOF
[Desktop Entry]
Type=Application
Name=Orbit
Comment=Sistema operacional pessoal — finanças, agenda, notas e hábitos
Exec=$PROJECT_DIR/scripts/orbit.sh
Icon=$ICON_REF
Path=$PROJECT_DIR
Terminal=false
Categories=Utility;
StartupWMClass=Orbit
StartupNotify=true
EOF
chmod +x "$DESKTOP_FILE" "$PROJECT_DIR/scripts/orbit.sh"

# cópia na área de trabalho, se existir
DESKTOP_DIR="$(command -v xdg-user-dir >/dev/null && xdg-user-dir DESKTOP || echo "$HOME/Desktop")"
if [ -d "$DESKTOP_DIR" ]; then
    cp "$DESKTOP_FILE" "$DESKTOP_DIR/orbit.desktop"
    chmod +x "$DESKTOP_DIR/orbit.desktop"
    command -v gio >/dev/null && gio set "$DESKTOP_DIR/orbit.desktop" metadata::trusted true 2>/dev/null || true
fi

command -v update-desktop-database >/dev/null && update-desktop-database "$APPS_DIR" 2>/dev/null || true

bold "✓ Orbit instalado!"
echo "  Abra pelo menu de aplicativos (procure \"Orbit\") ou pelo atalho na área de trabalho."
echo "  Alternativas no terminal:  make dev  (app desktop)  |  make web  (navegador)"
echo
echo "  Bancos separados: o atalho usa ~/.local/share/orbit/orbit.sqlite (dados reais,"
echo "  criado e migrado no primeiro clique); make dev/web/test usam o database/database.sqlite"
echo "  do repositório. Para rodar artisan no banco real: make app-artisan c=\"migrate:status\""
