.PHONY: install dev web test

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
