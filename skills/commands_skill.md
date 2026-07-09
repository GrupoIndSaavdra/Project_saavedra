# ⚡ Guía de Comandos de Entorno (Commands Skill) — Project Saavedra

> **📁 Directorio de Referencia:** `(Raíz del Proyecto — PowerShell en Windows)`
> *Referencia rápida de los comandos más frecuentes usados durante el desarrollo. Úsalos para buscar, editar, depurar y verificar sin perder tiempo.*

---

## 🔑 REGLA CRÍTICA: Siempre usar Python para búsquedas complejas en archivos Blade/JS grandes

PowerShell falla con caracteres especiales (`&&`, `=>`, `@`, `$`) dentro de strings. Para búsquedas en archivos grandes como `almacen_fundicion.blade.php` o `calidad_fundicion.blade.php`, **usa SIEMPRE Python** con un script en lugar de comandos shell directos.

---

## 1. 🔍 Búsqueda de Texto en Archivos (Python — MÁS CONFIABLE)

### Buscar texto en un archivo y mostrar contexto
```python
# Ejecutar con: python -c "..."  O  escribir a archivo y ejecutar python script.py

with open('resources/views/almacen/almacen_fundicion.blade.php', 'r', encoding='utf-8') as f:
    lines = f.readlines()

query = 'archivosRechazados'  # <-- Cambiar el texto a buscar
for i, line in enumerate(lines):
    if query in line:
        print(f'{i+1}: {line.rstrip()}')
```

### Buscar y mostrar N líneas de contexto alrededor del match
```python
with open('resources/views/almacen/almacen_fundicion.blade.php', 'r', encoding='utf-8') as f:
    text = f.read()

target = '$rechazadosDibujos'  # <-- Cambiar
start = text.find(target)
if start != -1:
    # Buscar inicio de la línea
    line_start = text.rfind('\n', 0, start) + 1
    # Mostrar 20 líneas desde ese punto
    chunk = text[line_start:line_start+1500]
    print(chunk)
else:
    print('No encontrado')
```

### Buscar desde una posición específica del texto (para múltiples ocurrencias)
```python
with open('ruta/al/archivo.php', 'r', encoding='utf-8') as f:
    text = f.read()

query = 'Documentos Rechazados'
pos = 0
while True:
    idx = text.find(query, pos)
    if idx == -1: break
    # Mostrar línea número aproximado
    line_num = text[:idx].count('\n') + 1
    print(f'Línea ~{line_num}: {text[idx:idx+80]}')
    pos = idx + 1
```

---

## 2. ✏️ Modificación de Archivos con Python (SIN REGEX — búsqueda literal)

### Reemplazar un bloque exacto de texto
```python
with open('ruta/archivo.php', 'r', encoding='utf-8') as f:
    text = f.read()

old = '''$rechazadosDibujos = [];
                                                $rechazadosAyudas = [];'''

new = '''$rechazadosDibujos = $dibujosRechazados ?? [];
                                                $rechazadosAyudas  = $ayudasRechazados ?? [];'''

if old in text:
    text = text.replace(old, new, 1)  # El 1 limita a primera ocurrencia
    with open('ruta/archivo.php', 'w', encoding='utf-8') as f:
        f.write(text)
    print('Reemplazado OK')
else:
    print('Texto NO encontrado — revisar espacios/tabs')
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

---

## 3. 🐘 PHP — Verificación y Artisan

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
# Limpiar caché de vistas, rutas y config
php artisan view:clear
php artisan route:clear
php artisan config:clear
php artisan cache:clear

# Listar todas las rutas del proyecto
php artisan route:list

# Ver rutas filtradas por nombre
php artisan route:list --name=almacen

# Ejecutar migraciones
php artisan migrate

# Rollback de la última migración
php artisan migrate:rollback

# Crear migración nueva
php artisan make:migration add_campo_to_tabla_table --table=tabla

# Crear modelo
php artisan make:model NombreModelo

# Crear controlador
php artisan make:controller NombreController
```

---

## 4. 📦 NPM / Vite — Assets Frontend

```powershell
# Compilar assets en modo desarrollo (hot reload)
npm run dev

# Compilar assets para producción (minificado)
npm run build

# Verificar que Vite compila sin errores (sin servidor)
npm run build 2>&1
```

---

## 5. 📋 Leer Logs del Sistema

```powershell
# Ver últimas 100 líneas del log de Laravel en tiempo real
Get-Content -Path "storage/logs/laravel.log" -Tail 100 -Wait

# Ver últimas 50 líneas sin tiempo real (más rápido)
Get-Content -Path "storage/logs/laravel.log" -Tail 50

# Buscar errores específicos en el log
Select-String -Path "storage/logs/laravel.log" -Pattern "ErrorException" | Select-Object -Last 20
```

---

## 6. 🗂️ Explorar Estructura de Directorios

```powershell
# Listar archivos en una carpeta (con tamaño)
Get-ChildItem -Path resources/views/almacen

# Listar archivos PHP de un directorio (recursivo)
Get-ChildItem -Path app/Models -Filter *.php | Select-Object Name, Length

# Listar carpetas de migraciones
Get-ChildItem -Path database/migrations -Directory
```

---

## 7. 🧹 Limpieza de Archivos Temporales

```powershell
# Eliminar un archivo temporal
Remove-Item nombre_script.py -ErrorAction SilentlyContinue

# Eliminar múltiples archivos temporales
Remove-Item scratch_*.py -ErrorAction SilentlyContinue
Remove-Item test_*.php  -ErrorAction SilentlyContinue
```

---

## 8. 🐍 Patrón Recomendado: Script Python Temporal

Cuando la operación es compleja (regex en blade, append a múltiples archivos, etc.),
**escribe el script a un archivo `.py` y ejecútalo**, en vez de intentar un one-liner en PowerShell:

```python
# Escribir con write_to_file a: nombre_script.py
# Luego ejecutar:
python nombre_script.py
# Al terminar, limpiar:
Remove-Item nombre_script.py
```

**¿Por qué?**
- Los one-liners con `-c` en Python dentro de PowerShell rompen con `&&`, `>`, comillas dobles y `\n`.
- Los scripts en archivo son más legibles, debuggables y evitan errores de escape.

---

## 9. ⚠️ Caracteres que ROMPEN en PowerShell (Evitar en one-liners)

| Carácter | Problema en PowerShell | Solución |
|---|---|---|
| `&&` | Se interpreta como operador lógico PS | Usar script Python en archivo |
| `>` | Se redirige la salida | Usar `$q->where(...)` dentro de Python string |
| `@php` / `@` | Dirección de arreglo en PS | Escapar o usar Python |
| `$var` | Variable de PS, no de PHP | Escapar con `` ` `` o usar Python |
| Comillas dobles en `-c` | Rompen el string del comando | Usar comillas simples o script en archivo |

---

## 10. 🔧 Comandos de Git Frecuentes

```powershell
# Ver estado actual
git status

# Ver diferencia de un archivo
git diff resources/views/almacen/almacen_fundicion.blade.php

# Agregar y commitear cambios
git add .
git commit -m "fix: descripción del cambio"

# Ver últimos commits
git log --oneline -10

# Descartar cambios de un archivo (¡CUIDADO! Irreversible)
git checkout -- resources/views/almacen/almacen_fundicion.blade.php
```
