#!/usr/bin/env bash
# Integra o AppImage do Orbit ao desktop: move para um caminho estável,
# extrai o ícone oficial de dentro do próprio bundle e cria o atalho
# no menu de aplicativos.
#
# Uso:
#   ./scripts/install-appimage.sh                    # procura em ~/Downloads
#   ./scripts/install-appimage.sh /caminho/Orbit.AppImage
set -euo pipefail

APP_DIR="$HOME/Applications"
APP_PATH="$APP_DIR/Orbit.AppImage"
ICON_DIR="$HOME/.local/share/orbit"
DESKTOP_DIR="$HOME/.local/share/applications"
DESKTOP_FILE="$DESKTOP_DIR/orbit.desktop"

bold() { printf '\033[1m%s\033[0m\n' "$*"; }
warn() { printf '\033[33mAVISO:\033[0m %s\n' "$*" >&2; }
fail() { printf '\033[31mERRO:\033[0m %s\n' "$*" >&2; exit 1; }

# ── Localiza o AppImage ─────────────────────────────────────────
if [ $# -ge 1 ]; then
    SRC="$1"
    [ -f "$SRC" ] || fail "Arquivo não encontrado: $SRC"
else
    # shellcheck disable=SC2012 # ls -t é suficiente aqui e mais legível que find -printf
    SRC="$(ls -t "$HOME"/Downloads/Orbit-*.AppImage 2>/dev/null | head -1 || true)"
    [ -n "$SRC" ] || fail "Nenhum Orbit-*.AppImage em ~/Downloads. Passe o caminho: $0 /caminho/Orbit.AppImage"
fi
SRC="$(readlink -f "$SRC")"
bold "→ AppImage: $SRC"

# ── FUSE 2: sem isso o AppImage não monta ───────────────────────
# O erro nativo ("dlopen(): error loading libfuse.so.2") parece app quebrado,
# mas é só dependência ausente — o Ubuntu 24.04 não traz por padrão.
if ! ldconfig -p 2>/dev/null | grep -q 'libfuse\.so\.2'; then
    warn "libfuse2 não encontrado — o AppImage não vai montar."
    if grep -qi 'ubuntu\|debian' /etc/os-release 2>/dev/null; then
        PKG=libfuse2
        grep -q 'VERSION_ID="2[4-9]' /etc/os-release 2>/dev/null && PKG=libfuse2t64
        warn "Instale com: sudo apt install $PKG"
    fi
    warn "Alternativa sem instalar nada: rode com --appimage-extract-and-run"
fi

# ── Encerra instância em execução ───────────────────────────────
# O AppImage fica montado em /tmp/.mount_* enquanto roda; mover o arquivo
# embaixo de um processo vivo deixa o mount órfão.
if pgrep -f '/tmp/\.mount_Orb[i]t' >/dev/null 2>&1; then
    bold "→ Fechando instância em execução…"
    pkill -f '/tmp/\.mount_Orb[i]t' || true
    sleep 3
fi

# ── Caminho estável ─────────────────────────────────────────────
# O nome não leva versão de propósito: o electron-updater sobrescreve
# este mesmo arquivo ao atualizar, e o atalho precisa continuar válido.
mkdir -p "$APP_DIR"
if [ "$SRC" != "$APP_PATH" ]; then
    bold "→ Movendo para $APP_PATH…"
    mv -f "$SRC" "$APP_PATH"
fi
chmod +x "$APP_PATH"

# ── Ícone ───────────────────────────────────────────────────────
bold "→ Extraindo o ícone do bundle…"
TMP_DIR="$(mktemp -d)"
trap 'rm -rf "$TMP_DIR"' EXIT
(cd "$TMP_DIR" && "$APP_PATH" --appimage-extract 'usr/share/icons/hicolor/512x512/apps/*.png' >/dev/null 2>&1) || true

ICON_SRC="$(find "$TMP_DIR/squashfs-root" -name '*.png' -path '*512x512*' 2>/dev/null | head -1 || true)"
mkdir -p "$ICON_DIR"
if [ -n "$ICON_SRC" ]; then
    cp -f "$ICON_SRC" "$ICON_DIR/orbit.png"
    bold "  ícone: $ICON_DIR/orbit.png"
else
    warn "Não consegui extrair o ícone; o atalho vai usar o genérico."
fi

# ── Atalho ──────────────────────────────────────────────────────
# Icon= com caminho absoluto em vez do nome do tema: dispensa
# ~/.local/share/icons, que em algumas máquinas ficou root-owned por
# instaladores rodados com sudo e aí a cópia falha silenciosamente.
#
# StartupWMClass=orbit em MINÚSCULO: é o WM_CLASS que a janela Electron
# realmente reporta. O .desktop embutido no AppImage declara "Orbit" com
# maiúscula, e por isso duplica o ícone na dock quando o app abre.
mkdir -p "$DESKTOP_DIR"
cat > "$DESKTOP_FILE" <<EOF
[Desktop Entry]
Type=Application
Name=Orbit
Comment=Sistema operacional pessoal — finanças, agenda, notas e hábitos
Exec=$APP_PATH %U
Icon=$ICON_DIR/orbit.png
StartupWMClass=orbit
StartupNotify=true
Terminal=false
Categories=Office;
EOF

update-desktop-database "$DESKTOP_DIR" 2>/dev/null || true

if command -v desktop-file-validate >/dev/null 2>&1; then
    desktop-file-validate "$DESKTOP_FILE" || warn "desktop-file-validate reclamou do atalho (acima)."
fi

bold "✓ Orbit instalado."
printf '  app:    %s\n  atalho: %s\n  dados:  %s\n' \
    "$APP_PATH" "$DESKTOP_FILE" "$HOME/.config/orbit"
printf '\nProcure por "Orbit" no menu de aplicativos.\n'
