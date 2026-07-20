# Guía de Comandos de Entorno (Commands Skill) — Project Saavedra

> ** Directorio de Referencia:** `(Raíz del Proyecto — PowerShell en Windows)`
> *Referencia rápida de los comandos más frecuentes. Úsalos para buscar, editar, depurar y verificar sin perder tiempo.*

---

## REGLA CRÍTICA: Siempre usar Python para búsquedas/ediciones en archivos grandes Blade/JS

PowerShell falla con caracteres especiales (`&&`, `=>`, `@`, `$`) dentro de strings. Para búsquedas en archivos grandes como `almacen_fundicion.blade.php`, **usa SIEMPRE Python** con un script en lugar de comandos shell directos.

---

## 1. Búsqueda de Texto en Archivos (Python — MÁS CONFIABLE)

### Buscar texto en un archivo y mostrar contexto
```python
# Ejecutar con: python script.py (Preferir archivo sobre one-liner)
with open('resources/views/almacen/almacen_fundicion.blade.php', 'r', encoding='utf-8') as f:
 lines = f.readlines()

query = 'archivosRechazados' # <-- Cambiar el texto a buscar
for i, line in enumerate(lines):
 if query in line:
 print(f'{i+1}: {line.rstrip()}')
```

### Buscar y mostrar N líneas de contexto alrededor del match
```python
with open('resources/views/almacen/almacen_fundicion.blade.php', 'r', encoding='utf-8') as f:
 lines = f.readlines()

query = '$rechazadosDibujos'
for i, line in enumerate(lines):
 if query in line:
 start = max(0, i - 3)
 end = min(len(lines), i + 8)
 print(f'--- Match en línea {i+1} ---')
 for j in range(start, end):
 print(f'{j+1}: {lines[j].rstrip()}')
 print()
```

### Buscar todas las ocurrencias en múltiples archivos de un directorio
```python
import os

carpeta = 'resources/views'
query = 'window.routes'
for root, dirs, files in os.walk(carpeta):
 for fname in files:
 if fname.endswith('.blade.php') or fname.endswith('.js'):
 path = os.path.join(root, fname)
 with open(path, 'r', encoding='utf-8', errors='ignore') as f:
 for i, line in enumerate(f, 1):
 if query in line:
 print(f'{path}:{i}: {line.rstrip()}')
```

---

## 2. Modificación de Archivos con Python (SIN REGEX — búsqueda literal)

### Reemplazar un bloque exacto de texto (PATRÓN ESTÁNDAR)
```python
with open('ruta/archivo.php', 'r', encoding='utf-8') as f:
 text = f.read()

old = '''$rechazadosDibujos = [];
 $rechazadosAyudas = [];'''

new = '''$rechazadosDibujos = $dibujosRechazados ?? [];
 $rechazadosAyudas = $ayudasRechazados ?? [];'''

if old in text:
 text = text.replace(old, new, 1) # El 1 limita a primera ocurrencia
 with open('ruta/archivo.php', 'w', encoding='utf-8') as f:
 f.write(text)
 print('Reemplazado OK')
else:
 print('Texto NO encontrado — revisar espacios/tabs/saltos de línea')
```

### Agregar texto al final de un archivo (append)
```python
with open('skills/views_skill.md', 'a', encoding='utf-8') as f:
 f.write('''
## Nueva Sección
Contenido de la nueva sección...
''')
print('Texto agregado')
```

### Insertar texto en una línea específica
```python
with open('ruta/archivo.php', 'r', encoding='utf-8') as f:
 lines = f.readlines()

# Insertar después de la línea 45 (índice 45)
linea_nueva = " // Línea insertada aquí\n"
lines.insert(45, linea_nueva)

with open('ruta/archivo.php', 'w', encoding='utf-8') as f:
 f.writelines(lines)
print('Línea insertada en posición 46')
```

---

## 3. PHP — Verificación y Artisan

### Verificar sintaxis de un archivo PHP
```powershell
php -l app/Http/Controllers/AlmacenFundicionController.php
```

### Verificar sintaxis de todos los controladores a la vez
```powershell
Get-ChildItem -Path app/Http/Controllers -Filter *.php | ForEach-Object { php -l $_.FullName }
```

### Comandos Artisan frecuentes
```powershell
# Limpiar caché de vistas, rutas, config y aplicación
php artisan view:clear
php artisan route:clear
php artisan config:clear
php artisan cache:clear

# Combo de limpieza total (útil después de cambios grandes)
php artisan optimize:clear

# Listar todas las rutas del proyecto
php artisan route:list

# Ver rutas filtradas por nombre o URI
php artisan route:list --name=almacen
php artisan route:list --path=api

# Ejecutar migraciones
php artisan migrate

# Rollback de la última migración
php artisan migrate:rollback

# Crear migración nueva
php artisan make:migration add_campo_to_tabla_table --table=tabla

# Crear modelo con migración y controlador
php artisan make:model NombreModelo -mc

# Crear controlador
php artisan make:controller NombreController

# Crear Form Request
php artisan make:request GuardarDatoRequest

# Crear Mailable
php artisan make:mail NombreCorreo --markdown=emails.nombre-vista

# Ver estado de migraciones
php artisan migrate:status
```

---

## 4. NPM / Vite — Assets Frontend

```powershell
# Compilar assets en modo desarrollo (hot reload)
npm run dev

# Compilar assets para producción (minificado)
npm run build

# Verificar que Vite compila sin errores (capturando stdout y stderr)
npm run build 2>&1
```

> Si el build falla con errores de sintaxis JS, revisar primero si hay escapes inválidos (`\'` dentro de strings en archivos .js). Ver `javascript_skill.md` sección 10.

---

## 5. Leer Logs del Sistema

```powershell
# Ver últimas 100 líneas del log de Laravel en tiempo real
Get-Content -Path "storage/logs/laravel.log" -Tail 100 -Wait

# Ver últimas 50 líneas sin tiempo real (más rápido)
Get-Content -Path "storage/logs/laravel.log" -Tail 50

# Buscar errores específicos en el log
Select-String -Path "storage/logs/laravel.log" -Pattern "ErrorException" | Select-Object -Last 20

# Buscar errores SQL específicos
Select-String -Path "storage/logs/laravel.log" -Pattern "SQLSTATE" | Select-Object -Last 10

# Limpiar el log (en desarrollo solamente)
Clear-Content -Path "storage/logs/laravel.log"
```

---

## 6. Explorar Estructura de Directorios

```powershell
# Listar archivos en una carpeta (con tamaño)
Get-ChildItem -Path resources/views/almacen

# Listar archivos PHP de un directorio (recursivo)
Get-ChildItem -Path app/Models -Filter *.php | Select-Object Name, Length

# Listar carpetas de migraciones
Get-ChildItem -Path database/migrations -Directory

# Contar archivos en un directorio
(Get-ChildItem -Path app/Models -Filter *.php).Count
```

---

## 7. Limpieza de Archivos Temporales

```powershell
# Eliminar un archivo temporal
Remove-Item nombre_script.py -ErrorAction SilentlyContinue

# Eliminar múltiples archivos temporales
Remove-Item scratch_*.py -ErrorAction SilentlyContinue
Remove-Item test_*.php -ErrorAction SilentlyContinue
```

---

## 8. Patrón Recomendado: Script Python Temporal

Cuando la operación es compleja (regex en blade, append a múltiples archivos, etc.),
**escribe el script a un archivo `.py` y ejecútalo**, en vez de intentar un one-liner en PowerShell:

```python
# Patrón:
# 1. Escribir con write_to_file a: nombre_script.py
# 2. Ejecutar: python nombre_script.py
# 3. Al terminar, limpiar: Remove-Item nombre_script.py
```

**¿Por qué?**
- Los one-liners con `-c` en Python dentro de PowerShell rompen con `&&`, `>`, comillas dobles y `\n`.
- Los scripts en archivo son más legibles, debuggables y evitan errores de escape.

---

## 9. Caracteres que ROMPEN en PowerShell (Evitar en one-liners)

| Carácter | Problema en PowerShell | Solución |
|---|---|---|
| `&&` | Se interpreta como operador lógico PS | Usar script Python en archivo |
| `>` | Se redirige la salida | Usar `$q->where(...)` dentro de Python string |
| `@php` / `@` | Dirección de arreglo en PS | Escapar o usar Python |
| `$var` | Variable de PS, no de PHP | Escapar con `` ` `` o usar Python |
| Comillas dobles en `-c` | Rompen el string del comando | Usar comillas simples o script en archivo |
| `\n` en `-c` | Se interpreta literalmente | Usar archivo .py con saltos reales |

---

## 10. Comandos de Git Frecuentes

```powershell
# Ver estado actual
git status

# Ver diferencia de un archivo
git diff resources/views/almacen/almacen_fundicion.blade.php

# Agregar y commitear cambios
git add .
git commit -m "feat: descripción del cambio"

# Tipos de commit (convención del proyecto):
# feat: nueva funcionalidad
# fix: corrección de bug
# refactor: mejora de código sin cambio funcional
# style: cambios de CSS/UI
# chore: tareas de mantenimiento

# Ver últimos commits
git log --oneline -10

# Descartar cambios de un archivo (¡CUIDADO! Irreversible)
git checkout -- resources/views/almacen/almacen_fundicion.blade.php

# Crear rama nueva
git checkout -b feature/nombre-funcionalidad

# Ver ramas
git branch -a
```

---

## 11. Comandos de grep_search (Herramienta del Agente)

Cuando necesites buscar texto en el código, usa la herramienta `grep_search` del agente:

```
SearchPath: c:\Users\Jaxer020406\Documents\GitHub\Project_saavedra
Query: texto a buscar
Includes: ["*.php"], ["*.blade.php"], ["*.js"], ["*.css"]
MatchPerLine: true
CaseInsensitive: true
```

Esto es más rápido que ejecutar PowerShell para búsquedas simples. Usa Python para búsquedas complejas con contexto o ediciones.
