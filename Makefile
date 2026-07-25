.PHONY: install dev web test app-artisan update check-update

# Banco de uso real (o que o atalho abre), fora do repositório. Os alvos abaixo
# não exportam DB_DATABASE de propósito: dev, web e test usam o banco
# descartável do repo (database/database.sqlite).
ORBIT_DB ?= $(HOME)/.local/share/orbit/orbit.sqlite

# setup completo após clonar (deps, banco, assets e atalho com ícone)
install:
	bash scripts/install.sh

# app desktop (NativePHP) + vite com hot reload
# script dá um pseudo-TTY ao native:run (ele exige TTY); --kill-others derruba
# o vite junto, que remove o public/hot ao sair
dev:
	npx concurrently -c "#c4b5fd,#fdba74" "script -qefc \"php artisan native:run\" /dev/null" "npm run dev" --names=native,vite --kill-others

# app no navegador (serve + queue + vite)
web:
	composer run dev

test:
	php artisan test

# artisan contra o banco de uso real, ex.: make app-artisan c="migrate:status"
app-artisan:
	DB_DATABASE="$(ORBIT_DB)" php artisan $(c)

# mesma atualização do botão em Configurações (pull + deps + assets + migrations)
update:
	bash scripts/update.sh

# só checa e grava o status lido pela tela de Configurações
check-update:
	bash scripts/check-update.sh && cat storage/app/orbit-update.json
