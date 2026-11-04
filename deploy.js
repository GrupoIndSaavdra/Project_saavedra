/**
 * ==============================================================================
 * Project Saavedra - Script de Despliegue Automatizado a Producción
 * Ejecución: node deploy.js  (o también: npm run deploy)
 * ==============================================================================
 */

import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { execSync } from 'node:child_process';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const rootDir = __dirname;

const colors = {
    reset: '\x1b[0m',
    cyan: '\x1b[36m',
    green: '\x1b[32m',
    yellow: '\x1b[33m',
    red: '\x1b[31m',
    gray: '\x1b[90m',
    bold: '\x1b[1m',
};

function log(msg, color = colors.reset) {
    console.log(`${color}${msg}${colors.reset}`);
}

function runCommand(command, desc) {
    log(`\n${colors.cyan}[${desc}]${colors.reset} Ejecutando: ${colors.gray}${command}${colors.reset}`);
    try {
        execSync(command, { cwd: rootDir, stdio: 'inherit' });
    } catch (error) {
        log(`\n❌ Error al ejecutar: "${command}"`, colors.red);
        process.exit(1);
    }
}

const startTime = Date.now();

log('\n==========================================================', colors.cyan);
log('   GRUPO INDUSTRIAL SAAVEDRA - DESPLIEGUE A PRODUCCION    ', colors.bold + colors.yellow);
log('==========================================================', colors.cyan);

// 1. Eliminar public/hot si existe (evita congelamiento por búsqueda de dev server)
const hotFile = path.join(rootDir, 'public', 'hot');
if (fs.existsSync(hotFile)) {
    try {
        fs.unlinkSync(hotFile);
        log('\n✔ Archivo public/hot eliminado (modo desarrollo desactivado).', colors.green);
    } catch (err) {
        log(`\n⚠ Aviso: No se pudo eliminar public/hot: ${err.message}`, colors.yellow);
    }
} else {
    log('\n✔ public/hot no presente (entorno limpio de desarrollo).', colors.gray);
}

// 2. Compilar assets de producción con Vite
runCommand('npm run build', '1/4 Compilando assets con Vite');

// 3. Ejecutar migraciones pendientes e índices de rendimiento
runCommand('php artisan migrate --force', '2/4 Ejecutando migraciones de base de datos');

// 4. Limpiar cachés de Laravel (CRÍTICO: nunca usar route:cache en XAMPP/Apache bajo subcarpeta para evitar error 405)
runCommand('php artisan optimize:clear', '3/4 Limpiando cachés de Laravel (evita conflictos de rutas)');

// 5. Precompilar vistas de Blade para rendimiento de renderizado
runCommand('php artisan view:cache', '4/4 Precompilando vistas Blade');

const elapsedSeconds = ((Date.now() - startTime) / 1000).toFixed(1);

log('\n==========================================================', colors.cyan);
log(`¡DESPLIEGUE COMPLETADO EXITOSAMENTE en ${elapsedSeconds}s!`, colors.bold + colors.green);
log('Apache/XAMPP ahora sirve directamente los bundles compilados.', colors.gray);
log('==========================================================\n', colors.cyan);
