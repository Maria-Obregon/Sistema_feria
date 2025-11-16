# Makefile para Sistema_feria
# Comandos de desarrollo y testing

.PHONY: help dev serve seed-demo seed-all migrate-fresh install test

# Comando por defecto: mostrar ayuda
help:
	@echo "🎯 Sistema de Gestión de Ferias Científicas"
	@echo ""
	@echo "Comandos disponibles:"
	@echo "  make install        - Instalar dependencias (composer + npm)"
	@echo "  make migrate-fresh  - Resetear BD y ejecutar migraciones"
	@echo "  make seed-all       - Ejecutar todos los seeders"
	@echo "  make seed-demo      - Ejecutar solo JuezDemoSeeder"
	@echo "  make serve          - Iniciar servidor Laravel (puerto 8000)"
	@echo "  make dev            - Iniciar Vite dev server (puerto 5173)"
	@echo "  make test           - Ejecutar tests PHPUnit"
	@echo "  make fresh-start    - Setup completo (migrate + seed)"
	@echo ""

# Instalar dependencias
install:
	@echo "📦 Instalando dependencias de Composer..."
	composer install
	@echo "📦 Instalando dependencias de NPM..."
	npm install
	@echo "✅ Dependencias instaladas"

# Resetear base de datos
migrate-fresh:
	@echo "🗄️  Reseteando base de datos..."
	php artisan migrate:fresh
	@echo "✅ Migraciones completadas"

# Ejecutar todos los seeders
seed-all:
	@echo "🌱 Ejecutando seeders..."
	php artisan db:seed
	@echo "✅ Seeders completados"

# Ejecutar solo el seeder de demo para jueces
seed-demo:
	@echo "🌱 Ejecutando JuezDemoSeeder..."
	php artisan db:seed --class=JuezDemoSeeder
	@echo "✅ Datos de demo creados"
	@echo ""
	@echo "📋 Credenciales:"
	@echo "   Juez: juez@feria.test / Juez123!"

# Iniciar servidor Laravel
serve:
	@echo "🚀 Iniciando servidor Laravel en http://127.0.0.1:8000"
	php artisan serve

# Iniciar Vite dev server
dev:
	@echo "⚡ Iniciando Vite dev server en http://localhost:5173"
	npm run dev

# Ejecutar tests
test:
	@echo "🧪 Ejecutando tests..."
	php artisan test

# Setup completo desde cero
fresh-start: migrate-fresh seed-all
	@echo ""
	@echo "✅ Setup completo finalizado"
	@echo ""
	@echo "📋 Credenciales disponibles:"
	@echo "   Admin: admin@feria.test / password123"
	@echo "   Juez: juez@feria.test / Juez123!"
	@echo ""
	@echo "🚀 Iniciar servidores:"
	@echo "   Terminal 1: make serve"
	@echo "   Terminal 2: make dev"
	@echo ""
