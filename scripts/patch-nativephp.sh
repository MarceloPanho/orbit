#!/usr/bin/env bash
# Aplica correções no nativephp/desktop que não têm ponto de override pelo
# projeto. Roda no CI antes do `native:build` — o composer sobrescreve vendor/,
# então patch versionado aqui é a única forma de manter as correções.
#
# Cada patch FALHA ALTO se o trecho esperado sumir. É de propósito: um bump do
# nativephp que reescreva esse código deve quebrar o build ruidosamente, não
# passar batido e voltar a publicar pacotes defeituosos.
#
# Uso: ./scripts/patch-nativephp.sh
set -euo pipefail

ELECTRON_DIR="vendor/nativephp/desktop/resources/electron"
PLUGIN_DIST="$ELECTRON_DIR/electron-plugin/dist/index.js"
PHP_DIST="$ELECTRON_DIR/electron-plugin/dist/server/php.js"
UPDATER_DIST="$ELECTRON_DIR/electron-plugin/dist/server/api/autoUpdater.js"
BUILDER_CFG="$ELECTRON_DIR/electron-builder.mjs"
COPY_TRAIT="vendor/nativephp/desktop/src/Builder/Concerns/CopiesToBuildDirectory.php"

bold() { printf '\033[1m%s\033[0m\n' "$*"; }
skip() { printf '  \033[2m%s\033[0m\n' "$*"; }
fail() { printf '\033[31mERRO:\033[0m %s\n' "$*" >&2; exit 1; }

[ -f "$PLUGIN_DIST" ] || fail "não encontrei $PLUGIN_DIST — rode a partir da raiz do projeto, com o composer install feito."
[ -f "$PHP_DIST" ] || fail "não encontrei $PHP_DIST."
[ -f "$UPDATER_DIST" ] || fail "não encontrei $UPDATER_DIST."
[ -f "$BUILDER_CFG" ] || fail "não encontrei $BUILDER_CFG."
[ -f "$COPY_TRAIT" ] || fail "não encontrei $COPY_TRAIT."

# ── Patch 1: janela oculta não volta ao clicar no ícone ─────────
# O handler de 'second-instance' faz restore() só se estiver minimizada e
# depois focus(). Numa janela oculta (_NET_WM_STATE_HIDDEN) isMinimized() é
# false e focus() é no-op, então clicar no atalho com o app rodando não faz
# absolutamente nada. Falta show().
#
# Patch no dist/ e não no src/: o package.json expõe
# "exports": "./electron-plugin/dist/index.js", e o `plugin:build` que compila
# TypeScript não faz parte do `npm run build`. Mexer no .ts não teria efeito.
bold "→ Patch 1: show() no handler de second-instance"
if grep -q "this.mainWindow.show();" "$PLUGIN_DIST"; then
    skip "já aplicado, pulando"
else
    grep -q "this.mainWindow.focus();" "$PLUGIN_DIST" \
        || fail "âncora 'this.mainWindow.focus();' não existe mais em $PLUGIN_DIST — o upstream mudou, revise o patch."

    n="$(grep -c "this.mainWindow.focus();" "$PLUGIN_DIST")"
    [ "$n" -eq 1 ] || fail "esperava 1 ocorrência de 'this.mainWindow.focus();', achei $n — patch ambíguo, revise."

    node --input-type=module -e '
import { readFileSync, writeFileSync } from "fs";
const p = process.argv[1];
const s = readFileSync(p, "utf8");
writeFileSync(p, s.replace(
    /(\s*)this\.mainWindow\.focus\(\);/,
    "$1this.mainWindow.show();$1this.mainWindow.focus();",
));
' "$PLUGIN_DIST"
    grep -q "this.mainWindow.show();" "$PLUGIN_DIST" || fail "patch 1 não aplicou."
    skip "aplicado"
fi

# ── Patch 2: StartupWMClass com a caixa errada ──────────────────
# O electron-builder gera StartupWMClass a partir do productName ("Orbit"),
# mas a janela Electron reporta WM_CLASS igual ao nome do executável, que é
# Str::slug(app.name) → "orbit". Divergindo, a dock mostra um ícone genérico
# separado em vez de agrupar na entrada do app.
#
# `fileName` já existe no escopo do arquivo (process.env.NATIVEPHP_APP_FILENAME),
# que é exatamente o slug usado no executável — por isso não hardcodamos "orbit".
bold "→ Patch 2: StartupWMClass = nome do executável"
if grep -q "StartupWMClass" "$BUILDER_CFG"; then
    skip "já aplicado, pulando"
else
    grep -q "^        category: 'Utility'," "$BUILDER_CFG" \
        || fail "âncora do bloco linux não existe mais em $BUILDER_CFG — o upstream mudou, revise o patch."

    # A âncora tem aspas simples, que colidiriam com o shell — por isso a regex
    # casa a linha inteira do category em vez do literal.
    node --input-type=module -e '
import { readFileSync, writeFileSync } from "fs";
const p = process.argv[1];
const s = readFileSync(p, "utf8");
writeFileSync(p, s.replace(
    /(^        category: [^\n]*\n)/m,
    "$1        desktop: {\n            entry: {\n                StartupWMClass: fileName,\n            },\n        },\n",
));
' "$BUILDER_CFG"
    grep -q "StartupWMClass: fileName," "$BUILDER_CFG" || fail "patch 2 não aplicou."
    skip "aplicado"
fi

# ── Patch 3: PHP órfão quando o Electron morre de forma anormal ─
# A limpeza dos filhos (servidor PHP, worker de fila) está pendurada só no
# 'before-quit'. Esse evento não dispara quando o processo principal leva um
# SIGTERM/SIGHUP — crash, logout, `kill`, fim de sessão. O Electron sai e os
# `php` ficam vivos, segurando a porta 8100 e rodando a fila indefinidamente,
# invisíveis para o usuário, até um kill manual.
#
# SIGKILL continua fora de alcance (não é interceptável); esses três cobrem o
# que acontece na prática num desligamento ou num logout.
bold "→ Patch 3: matar filhos também em SIGTERM/SIGINT/SIGHUP"
if grep -q "PATCH-ORBIT: sinais" "$PLUGIN_DIST"; then
    skip "já aplicado, pulando"
else
    n="$(grep -c "this.killChildProcesses();" "$PLUGIN_DIST")"
    [ "$n" -eq 1 ] || fail "esperava 1 chamada de 'this.killChildProcesses();' em $PLUGIN_DIST, achei $n — o upstream mudou, revise o patch."

    node --input-type=module -e '
import { readFileSync, writeFileSync } from "fs";
const p = process.argv[1];
const s = readFileSync(p, "utf8");
const insert = [
    "",
    "        // PATCH-ORBIT: sinais — before-quit não dispara em SIGTERM/SIGHUP.",
    "        [\"SIGINT\", \"SIGTERM\", \"SIGHUP\"].forEach((signal) => {",
    "            process.on(signal, () => {",
    "                stopAllProcesses();",
    "                this.killChildProcesses();",
    "                app.quit();",
    "            });",
    "        });",
].join("\n");
const out = s.replace(
    /(\n            this\.killChildProcesses\(\);\n        \}\);\n)/,
    "$1" + insert + "\n",
);
if (out === s) { console.error("regex do patch 3 não casou"); process.exit(1); }
writeFileSync(p, out);
' "$PLUGIN_DIST" || fail "patch 3 não aplicou."
    grep -q "PATCH-ORBIT: sinais" "$PLUGIN_DIST" || fail "patch 3 não aplicou."
    skip "aplicado"
fi

# ── Patch 4: `artisan optimize` que falha em todo boot ──────────
# O plugin redireciona APP_CONFIG_CACHE & cia. para o userData (gravável), mas
# só dentro de `if (runningSecureBuild())`. Como o Orbit não é secure build
# (o log diz "Running from source"), o Laravel cai no bootstrap/cache DENTRO do
# app — read-only no AppImage (squashfs) e do root no .deb em /opt. Resultado:
# `optimize` falha em 100% das inicializações e grava um ERROR no laravel.log.
#
# Aqui o optimize é PULADO quando não há destino gravável, em vez de tentado e
# falhado. Não passa a cachear: apontar o cache para o userData seria o conserto
# "de verdade", mas no AppImage o app é montado num /tmp/.mount_Orbit.XXXX
# diferente a cada execução, e um config.php persistido guardaria caminhos
# absolutos de uma montagem que já não existe.
bold "→ Patch 4: não tentar optimize sem bootstrap/cache gravável"
if grep -q "PATCH-ORBIT: optimize" "$PHP_DIST"; then
    skip "já aplicado, pulando"
else
    grep -q "if (shouldOptimize()) {" "$PHP_DIST" \
        || fail "âncora 'if (shouldOptimize()) {' não existe mais em $PHP_DIST — o upstream mudou, revise o patch."

    node --input-type=module -e '
import { readFileSync, writeFileSync } from "fs";
const p = process.argv[1];
let s = readFileSync(p, "utf8");
const before = s;

s = s.replace(
    /^import \{ existsSync, /m,
    "import { accessSync, constants, existsSync, ",
);

s = s.replace(
    /\nfunction shouldOptimize\(\) \{/,
    [
        "",
        "// PATCH-ORBIT: optimize — sem destino gravável, nem tenta.",
        "function canWriteBootstrapCache(appPath) {",
        "    try {",
        "        accessSync(join(appPath, \"bootstrap\", \"cache\"), constants.W_OK);",
        "        return true;",
        "    }",
        "    catch (err) {",
        "        return false;",
        "    }",
        "}",
        "function shouldOptimize() {",
    ].join("\n"),
);

s = s.replace(
    /(\n        )if \(shouldOptimize\(\)\) \{/,
    [
        "$1if (shouldOptimize() && !runningSecureBuild() && !canWriteBootstrapCache(appPath)) {",
        "$1    console.log(\"Skipping optimize: bootstrap/cache is not writable in this build.\");",
        "$1}",
        "$1else if (shouldOptimize()) {",
    ].join(""),
);

if (s === before) { console.error("nenhuma das regex do patch 4 casou"); process.exit(1); }
writeFileSync(p, s);
' "$PHP_DIST" || fail "patch 4 não aplicou."
    grep -q "PATCH-ORBIT: optimize" "$PHP_DIST" || fail "patch 4 não aplicou (marcador)."
    grep -q "accessSync, constants, existsSync," "$PHP_DIST" || fail "patch 4: import do accessSync não aplicou."
    grep -q "else if (shouldOptimize())" "$PHP_DIST" || fail "patch 4: guarda no call site não aplicou."
    skip "aplicado"
fi

# ── Patch 5: glob() comendo o separador no build do Windows ────
# `cleanup_exclude_files` apaga vendor/nativephp/desktop/resources inteiro do
# bundle, e o único arquivo que volta de lá é o livewire-dispatcher.js, via
# `cleanup_include_files`. Essa reinclusão usa glob(), e o pattern é normalizado
# para DIRECTORY_SEPARATOR antes — que no Windows é a barra invertida, o
# caractere de ESCAPE do glob. Cada "\" some levando junto o caractere seguinte,
# o pattern perde os separadores e não casa nada.
#
# Resultado: no pacote Windows o livewire-dispatcher.js simplesmente não existe.
# O LivewireDispatcher.php lê esse arquivo com file_get_contents() sem checar
# retorno, injeta um <script> vazio, e a ponte Electron→Livewire morre calada:
# nenhum evento de auto-update chega na tela, e Configurações → Atualizações
# fica girando em "Verificando…" para sempre. No Linux o separador é "/", não
# há escape, e por isso o mesmo build funciona.
#
# GLOB_NOESCAPE desliga o escape; glob() aceita a barra invertida como separador
# normal no Windows.
bold "→ Patch 5: GLOB_NOESCAPE na reinclusão de arquivos do bundle"
if grep -q "GLOB_NOESCAPE" "$COPY_TRAIT"; then
    skip "já aplicado, pulando"
else
    grep -q 'glob($sourcePath.DIRECTORY_SEPARATOR.$pattern, GLOB_BRACE)' "$COPY_TRAIT" \
        || fail "âncora do glob() não existe mais em $COPY_TRAIT — o upstream mudou, revise o patch."

    node --input-type=module -e '
import { readFileSync, writeFileSync } from "fs";
const p = process.argv[1];
const s = readFileSync(p, "utf8");
const anchor = "glob($sourcePath.DIRECTORY_SEPARATOR.$pattern, GLOB_BRACE)";
const out = s.replace(anchor, () => "glob($sourcePath.DIRECTORY_SEPARATOR.$pattern, GLOB_BRACE | GLOB_NOESCAPE)");
if (out === s) { console.error("patch 5 não casou"); process.exit(1); }
writeFileSync(p, out);
' "$COPY_TRAIT" || fail "patch 5 não aplicou."
    grep -q "GLOB_BRACE | GLOB_NOESCAPE" "$COPY_TRAIT" || fail "patch 5 não aplicou (marcador)."
    skip "aplicado"
fi

# ── Patch 6: falha de auto-update invisível ────────────────────
# O electron-updater só loga se alguém definir autoUpdater.logger; sem isso ele
# cai no console, que num app GUI empacotado (Windows principalmente) não existe.
# Some tudo: erro de rede, sha512 divergente, instalador barrado pelo antivírus.
# Foi exatamente esse silêncio que fez o Patch 5 levar tanto tempo para aparecer.
#
# Escreve junto do laravel.log, em storage/logs do userData — diretório que o
# php.js já cria no boot.
bold "→ Patch 6: logger do updater em arquivo"
if grep -q "PATCH-ORBIT: logger" "$UPDATER_DIST"; then
    skip "já aplicado, pulando"
else
    grep -q "const { autoUpdater } = electronUpdater;" "$UPDATER_DIST" \
        || fail "âncora 'const { autoUpdater } = electronUpdater;' não existe mais em $UPDATER_DIST — o upstream mudou, revise o patch."

    node --input-type=module -e '
import { readFileSync, writeFileSync } from "fs";
const p = process.argv[1];
const s = readFileSync(p, "utf8");
const anchor = "const { autoUpdater } = electronUpdater;";
// As declarações import são hoisted, então podem entrar aqui no meio do topo
// do módulo sem quebrar a ordem de avaliação.
const insert = [
    "",
    "// PATCH-ORBIT: logger — sem autoUpdater.logger o electron-updater escreve no",
    "// console, que não existe num app empacotado. Toda falha de update sumia.",
    "import { app } from \"electron\";",
    "import { appendFileSync } from \"fs\";",
    "import { join } from \"path\";",
    "const orbitUpdaterLog = (level, ...args) => {",
    "    const line = args",
    "        .map((a) => (a instanceof Error ? a.stack || a.message : typeof a === \"string\" ? a : JSON.stringify(a)))",
    "        .join(\" \");",
    "    try {",
    "        appendFileSync(",
    "            join(app.getPath(\"userData\"), \"storage\", \"logs\", \"updater.log\"),",
    "            `[${new Date().toISOString()}] ${level}: ${line}\\n`,",
    "        );",
    "    }",
    "    catch (err) {",
    "        // Log é diagnóstico: se não dá para escrever, não derruba o update.",
    "    }",
    "};",
    "autoUpdater.logger = {",
    "    info: (...a) => orbitUpdaterLog(\"info\", ...a),",
    "    warn: (...a) => orbitUpdaterLog(\"warn\", ...a),",
    "    error: (...a) => orbitUpdaterLog(\"error\", ...a),",
    "    debug: (...a) => orbitUpdaterLog(\"debug\", ...a),",
    "};",
].join("\n");
const out = s.replace(anchor, () => anchor + insert);
if (out === s) { console.error("patch 6 não casou"); process.exit(1); }
writeFileSync(p, out);
' "$UPDATER_DIST" || fail "patch 6 não aplicou."
    grep -q "PATCH-ORBIT: logger" "$UPDATER_DIST" || fail "patch 6 não aplicou (marcador)."
    skip "aplicado"
fi

bold "✓ Patches aplicados."
