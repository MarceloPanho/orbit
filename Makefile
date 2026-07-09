.PHONY: install dev web test

# setup completo após clonar (deps, banco, assets e atalho com ícone)
install:
	bash scripts/install.sh

# app desktop (NativePHP)
dev:
	php artisan native:run

# app no navegador (serve + queue + vite)
web:
	composer run dev

test:
	php artisan test
