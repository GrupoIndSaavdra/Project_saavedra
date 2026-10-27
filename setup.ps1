# ==============================================================================
# Script de Instalacion y Preparacion Todo en Uno - Project Saavedra
# Ejecucion: powershell -ExecutionPolicy Bypass -File .\setup.ps1
# ==============================================================================

Write-Host ""
Write-Host "==========================================================" -ForegroundColor Cyan
Write-Host "    GRUPO INDUSTRIAL SAAVEDRA - INSTALADOR AUTOMATICO     " -ForegroundColor Yellow
Write-Host "==========================================================" -ForegroundColor Cyan
Write-Host ""

$ErrorActionPreference = "Stop"

try {
    # 1. Verificar PHP
    Write-Host "[1/6] Verificando entorno PHP..." -ForegroundColor Green
    $phpVersion = php -v 2>$null
    if (-not $phpVersion) {
        throw "PHP no esta instalado o no se encuentra en el PATH del sistema."
    }
    Write-Host "  -> PHP detectado correctamente." -ForegroundColor Gray

    # 2. Composer Install (Dependencias Backend + Livewire 3)
    Write-Host "[2/6] Instalando dependencias de PHP con Composer (incluye Livewire 3)..." -ForegroundColor Green
    composer install --no-interaction --prefer-dist
    Write-Host "  -> Composer install completado." -ForegroundColor Gray

    # 3. Archivo de Entorno .env y Llave de Aplicacion
    Write-Host "[3/6] Configurando archivo de entorno (.env)..." -ForegroundColor Green
    if (-not (Test-Path ".env")) {
        if (Test-Path ".env.example") {
            Copy-Item ".env.example" ".env"
            Write-Host "  -> Archivo .env creado a partir de .env.example." -ForegroundColor Yellow
            php artisan key:generate --ansi
        } else {
            Write-Host "  -> Aviso: No se encontro .env.example." -ForegroundColor Yellow
        }
    } else {
        Write-Host "  -> Archivo .env ya existe." -ForegroundColor Gray
    }

    # 4. Publicar recursos y configuracion de Livewire
    Write-Host "[4/6] Verificando configuracion de Laravel Livewire..." -ForegroundColor Green
    if (-not (Test-Path "config/livewire.php")) {
        php artisan livewire:publish --config --ansi
        Write-Host "  -> Configuracion config/livewire.php publicada." -ForegroundColor Gray
    } else {
        Write-Host "  -> config/livewire.php ya se encuentra presente." -ForegroundColor Gray
    }

    # 5. NPM Install (Dependencias Frontend + Tailwind CSS v3 + PostCSS)
    Write-Host "[5/6] Instalando dependencias de Node.js con NPM (Tailwind CSS v3, Vite)..." -ForegroundColor Green
    npm install
    Write-Host "  -> Dependencias de NPM instaladas." -ForegroundColor Gray

    # 6. Compilacion de Assets con Vite y Limpieza de Cache
    Write-Host "[6/6] Compilando assets frontend (Vite + Tailwind CSS) y limpiando caches..." -ForegroundColor Green
    php artisan optimize:clear --ansi
    npm run build
    Write-Host "  -> Compilacion de assets finalizada exitosamente." -ForegroundColor Gray

    Write-Host ""
    Write-Host "==========================================================" -ForegroundColor Cyan
    Write-Host "  PROYECTO SAAVEDRA INSTALADO Y CONFIGURADO CON EXITO!   " -ForegroundColor Green
    Write-Host "==========================================================" -ForegroundColor Cyan
    Write-Host "Para arrancar el entorno de desarrollo:" -ForegroundColor White
    Write-Host "  Terminal 1: php artisan serve" -ForegroundColor Yellow
    Write-Host "  Terminal 2: npm run dev" -ForegroundColor Yellow
    Write-Host ""
}
catch {
    Write-Host ""
    Write-Host "ERROR DURANTE LA INSTALACION:" -ForegroundColor Red
    Write-Host $_.Exception.Message -ForegroundColor Red
    Write-Host ""
    exit 1
}
