# Guía de Pruebas y Validación Estructural (Testing Skill)

> ** Directorio de Referencia:** `(Raíz del Proyecto)`
> *Usa esta skill para verificar la integridad del proyecto después de refactorizaciones masivas o antes de despliegues.*

Esta skill define un protocolo estricto y minucioso para garantizar que no existan referencias rotas, errores de sintaxis, vistas faltantes o fallos en el bundler de Vite.

---

## 1. Validación de Sintaxis PHP (Fail-Fast)
Antes de probar la aplicación en el navegador, debes asegurarte de que ningún controlador o modelo tenga errores de sintaxis (llaves faltantes, puntos y comas omitidos).

**Comando PowerShell para revisar toda la carpeta `app/`:**
```powershell
Get-ChildItem -Path app -Recurse -Filter *.php | ForEach-Object { $result = php -l $_.FullName; if ($result -notmatch "No syntax errors detected") { Write-Host $result -ForegroundColor Red } }
```
*Si este comando arroja texto en rojo, hay un error de sintaxis que crasheará la aplicación en producción.*

## 2. Validación de Rutas y Vistas de Laravel
Laravel Blade compila las vistas a PHP puro. Si una vista incluye (`@include`) un componente que no existe, o un Controlador llama a un `view('ruta.falsa')`, la aplicación lanzará una Excepción.

**Prueba de Caches Obligatoria:**
```powershell
php artisan view:clear
php artisan route:clear
php artisan config:clear
php artisan view:cache
php artisan route:cache
php artisan config:cache
```
*Criterio de Aceptación: Todos los comandos deben retornar "successfully". Si `view:cache` falla, significa que hay un error de sintaxis en un archivo `.blade.php` o una ruta a una vista que no existe.*

## 3. Validación de Assets Front-End (Vite)
Cualquier archivo CSS, JS o imagen que haya sido renombrado o eliminado causará que Vite aborte la compilación.

**Prueba de Build Completa:**
```powershell
Remove-Item -Recurse -Force public/build -ErrorAction SilentlyContinue
npm run build
```
*Criterio de Aceptación: El comando debe finalizar mostrando el tamaño (en kB) de los assets (e.g. `✓ built in X.XXs`). Si falla por `Unable to resolve @import` o `ENOENT`, debes corregir la ruta del asset en el archivo Blade o CSS.*

## 4. Auditoría de Código Muerto o Referencias Fantasma
Cuando se renombran carpetas o convenciones (ej. de Spanglish a `snake_case`), debes realizar búsquedas profundas (`grep_search`) para asegurar que no quede texto residual.

**Patrones de búsqueda a validar (adaptar según el caso):**
- Búsqueda en Controladores: `grep_search` de cadenas viejas (`almacen.`, `calidad_views`).
- Búsqueda en Vistas: `@vite`, `@include`, `<x-`, `asset(`.

## 5. Script de Validación de Vistas vs Archivos Físicos
Esta es la prueba más estricta. Consiste en leer todos los `view('nombre.vista')` de los controladores y verificar que el archivo `nombre/vista.blade.php` exista físicamente en `resources/views/`.

**Script Rápido (PowerShell):**
```powershell
# Extrae todas las llamadas a view() en los controladores y verifica si el archivo existe
$controllers = Get-ChildItem -Path "app/Http/Controllers" -Recurse -Filter *.php
$missingViews = 0

foreach ($file in $controllers) {
    $content = Get-Content $file.FullName
    $matches = [regex]::Matches($content, "view\(['""]([^'""]+)['""]")
    foreach ($match in $matches) {
        $viewPath = $match.Groups[1].Value.Replace('.', '/') + ".blade.php"
        $fullPath = "resources/views/$viewPath"
        if (-Not (Test-Path $fullPath)) {
            Write-Host "[ERROR] Vista no encontrada: $viewPath referenciada en $($file.Name)" -ForegroundColor Red
            $missingViews++
        }
    }
}

if ($missingViews -eq 0) { Write-Host "[OK] Todas las vistas referenciadas en Controladores existen." -ForegroundColor Green }
```

---

## Flujo de Trabajo para Testing Estructural
Cada vez que el usuario solicite "realizar pruebas", debes:
1. Ejecutar el comprobador de sintaxis PHP (`php -l`).
2. Limpiar y reconstruir las cachés de Artisan.
3. Compilar el entorno Vite desde cero.
4. Ejecutar el script validador de vistas físicas.
5. Reportar el resultado exacto (logs) al usuario.
