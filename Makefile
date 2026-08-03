.PHONY: install dev dev-clean web test app-artisan update build-linux build-win build-clean

# Banco de uso real (o que o atalho abre), fora do repositório. Os alvos abaixo
# não exportam DB_DATABASE de propósito: dev, web e test usam o banco
# descartável do repo (database/database.sqlite).
ORBIT_DB ?= $(HOME)/.local/share/orbit/orbit.sqlite

# Storage do app desktop em desenvolvimento: o NativePHP exporta
# LARAVEL_STORAGE_PATH apontando para o userData do Electron, e o Laravel honra
# essa variável no Application::storagePath(). Ou seja, as views compiladas do
# make dev NÃO ficam no storage/ do repositório — um `php artisan view:clear`
# solto no terminal limpa a pasta errada e parece não ter feito nada.
ORBIT_DEV_STORAGE ?= $(HOME)/.config/orbit-dev/storage

# setup completo após clonar (deps, banco, assets e atalho com ícone)
install:
	bash scripts/install.sh

# app desktop (NativePHP) + vite com hot reload
# script dá um pseudo-TTY ao native:run (ele exige TTY); --kill-others derruba
# o vite junto, que remove o public/hot ao sair
dev:
	npx concurrently -c "#c4b5fd,#fdba74" "script -qefc \"php artisan native:run\" /dev/null" "npm run dev" --names=native,vite --kill-others

# descarta as views compiladas quando o Blade insiste em servir uma versão velha.
# O Blade decide recompilar comparando mtime, então editar normalmente nunca dá
# problema: isto é para quando o mtime do .blade.php anda PARA TRÁS (checkout de
# outro branch, stash pop, arquivo restaurado) e o compilado fica mais novo que o
# fonte. Limpa as duas pastas porque são caches distintos: o do app desktop e o do
# repo, que web/test usam. Pode rodar com o make dev de pé.
dev-clean:
	@if [ -d "$(ORBIT_DEV_STORAGE)/framework/views" ]; then \
		LARAVEL_STORAGE_PATH="$(ORBIT_DEV_STORAGE)" php artisan view:clear; \
	else \
		echo "sem cache do app desktop em $(ORBIT_DEV_STORAGE) (rode o make dev ao menos uma vez)"; \
	fi
	php artisan view:clear

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

# build local para TESTE — nunca distribua o artefato gerado aqui: ele leva o
# .env de desenvolvimento junto, incluindo a APP_KEY. Releases saem do CI.
build-linux:
	php artisan native:build linux x64

build-win:
	php artisan native:build win x64

# native:reset limpa build/ e dist/ da raiz, mas NÃO a saída real do
# electron-builder, que fica em nativephp/electron/dist (~670 MB), nem a cópia
# do app que o build local deixa em vendor/nativephp/desktop/resources/build/app —
# essa cópia leva um .env com a APP_KEY real do desenvolvedor dentro.
build-clean:
	rm -rf nativephp build dist vendor/nativephp/desktop/resources/build/app
