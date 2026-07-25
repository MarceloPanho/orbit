#!/usr/bin/env bash
# Checa se há commits novos no origin e grava o resultado em
# storage/app/orbit-update.json, que a tela de Configurações lê.
#
# Roda no boot (chamado por scripts/orbit.sh) e sob demanda (botão "verificar
# agora"). Nunca falha de forma barulhenta: sem rede, registra ok=false, mantém
# a última comparação conhecida e sai com 0 — o app precisa abrir de qualquer jeito.
set -uo pipefail

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$PROJECT_DIR" || exit 0

STATUS_FILE="$PROJECT_DIR/storage/app/orbit-update.json"
BRANCH="${ORBIT_BRANCH:-main}"

git rev-parse --is-inside-work-tree >/dev/null 2>&1 || exit 0

CURRENT="$(git rev-parse --short HEAD 2>/dev/null || echo '')"
CURRENT_DATE="$(git log -1 --format=%cs 2>/dev/null || echo '')"

DIRTY=false
[ -n "$(git status --porcelain 2>/dev/null)" ] && DIRTY=true

# ok diz apenas se o fetch de AGORA funcionou; behind é sempre calculado contra
# a ref local de origin, então offline ainda mostramos a última comparação.
OK=false
timeout 15 git fetch --quiet origin "$BRANCH" 2>/dev/null && OK=true

REMOTE="$(git rev-parse --short "origin/$BRANCH" 2>/dev/null || echo '')"
BEHIND="$(git rev-list --count "HEAD..origin/$BRANCH" 2>/dev/null || echo 0)"
[[ "$BEHIND" =~ ^[0-9]+$ ]] || BEHIND=0

mkdir -p "$(dirname "$STATUS_FILE")"
printf '{"checked_at":"%s","ok":%s,"behind":%s,"dirty":%s,"branch":"%s","current":"%s","current_date":"%s","remote":"%s"}\n' \
    "$(date -Iseconds)" "$OK" "$BEHIND" "$DIRTY" "$BRANCH" "$CURRENT" "$CURRENT_DATE" "$REMOTE" \
    > "$STATUS_FILE"
