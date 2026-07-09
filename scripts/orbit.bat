@echo off
rem Launcher do Orbit no Windows — usado pelo atalho criado por scripts\install.ps1
cd /d "%~dp0.."
php artisan native:run --no-queue --no-dependencies
