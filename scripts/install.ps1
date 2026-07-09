# Setup do Orbit no Windows após clonar do GitHub:
#   instala dependências, prepara o banco, builda os assets e
#   cria os atalhos "Orbit" (Área de Trabalho + Menu Iniciar) com o ícone oficial.
#
# Uso (PowerShell, na pasta do projeto):
#   powershell -ExecutionPolicy Bypass -File scripts\install.ps1

$ErrorActionPreference = 'Stop'
$ProjectDir = Split-Path -Parent $PSScriptRoot

function Fail($msg) { Write-Host "ERRO: $msg" -ForegroundColor Red; exit 1 }
function Step($msg) { Write-Host "-> $msg" -ForegroundColor Cyan }

Set-Location $ProjectDir

# ── Pré-requisitos ──────────────────────────────────────────────
if (-not (Get-Command php      -ErrorAction SilentlyContinue)) { Fail "PHP nao encontrado. Instale PHP 8.3+ (https://windows.php.net) e adicione ao PATH." }
if (-not (Get-Command composer -ErrorAction SilentlyContinue)) { Fail "Composer nao encontrado. Veja https://getcomposer.org/download/" }
if (-not (Get-Command npm      -ErrorAction SilentlyContinue)) { Fail "Node/npm nao encontrados. Instale Node.js 20+ (https://nodejs.org)." }

php -r "exit(version_compare(PHP_VERSION, '8.3.0', '>=') ? 0 : 1);"
if ($LASTEXITCODE -ne 0) { Fail "PHP 8.3+ e necessario." }

# ── Dependências e app ──────────────────────────────────────────
Step "Instalando dependencias PHP (composer)..."
composer install --no-interaction --prefer-dist
if ($LASTEXITCODE -ne 0) { Fail "composer install falhou." }

Step "Instalando dependencias JS (npm)..."
npm install --no-fund --no-audit
if ($LASTEXITCODE -ne 0) { Fail "npm install falhou." }

if (-not (Test-Path .env)) {
    Step "Criando .env e chave da aplicacao..."
    Copy-Item .env.example .env
    php artisan key:generate --ansi
}

Step "Preparando banco de dados local..."
if (-not (Test-Path database\database.sqlite)) { New-Item -ItemType File database\database.sqlite | Out-Null }
php artisan migrate --seed --no-interaction
if ($LASTEXITCODE -ne 0) { Fail "migrations falharam." }

Step "Compilando assets (Vite)..."
npm run build
if ($LASTEXITCODE -ne 0) { Fail "npm run build falhou." }

Step "Instalando runtime desktop (Electron)..."
Push-Location vendor\nativephp\desktop\resources\electron
npm install --no-fund --no-audit
if ($LASTEXITCODE -ne 0) { Pop-Location; Fail "npm install do Electron falhou." }
Pop-Location

# ── Atalhos com ícone ───────────────────────────────────────────
Step "Criando atalhos do Orbit..."
$Icon     = Join-Path $ProjectDir 'resources\icons\orbit.ico'
$Launcher = Join-Path $ProjectDir 'scripts\orbit.bat'
$Shell    = New-Object -ComObject WScript.Shell

$Targets = @(
    (Join-Path ([Environment]::GetFolderPath('Desktop'))  'Orbit.lnk'),
    (Join-Path ([Environment]::GetFolderPath('Programs')) 'Orbit.lnk')
)
foreach ($Path in $Targets) {
    $Lnk = $Shell.CreateShortcut($Path)
    $Lnk.TargetPath       = $Launcher
    $Lnk.WorkingDirectory = $ProjectDir
    $Lnk.IconLocation     = $Icon
    $Lnk.Description      = 'Sistema operacional pessoal - financas, agenda, notas e habitos'
    $Lnk.WindowStyle      = 7   # janela do console minimizada
    $Lnk.Save()
}

Write-Host ""
Write-Host "Orbit instalado!" -ForegroundColor Green
Write-Host "  Abra pelo atalho 'Orbit' na Area de Trabalho ou no Menu Iniciar."
Write-Host "  (O primeiro boot pode levar de 30 a 60 segundos.)"
