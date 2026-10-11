---
name: classes_skill
description: Guía y patrones para el catálogo global de clases (config/global_classes.php), normalización de nombres, categorización de soldadura/procesos y matching en Project_saavedra.
version: 1.0.0
---

# Habilidad de Catálogo y Normalización de Clases (Classes Skill) — Project Saavedra

> **Directorio de Referencia:** `config/global_classes.php`, `app/Services/FundicionPaths.php` y `app/Models/Clase.php`

---

## ⚠️ DIRECTIVAS DE EJECUCIÓN OBLIGATORIA PARA EL AGENTE DE IA

Cuando trabajes con clases de productos/molduras en PHP, Blade o JS, DEBES cumplir estrictamente estas 5 reglas:

1. **NUNCA hardcodear arreglos de clases en código:** Si necesitas obtener el catálogo oficial de clases o las clases de soldadura/procesos, consulta SIEMPRE la configuración `config('global_classes.*')` en PHP o las variables globales `window.GlobalClasses`, `window.FundicionWelding`, etc., en JavaScript.
2. **Normalización obligatoria de nombres de clase:** Antes de comparar nombres de clases o buscar archivos vinculados, usa `FundicionPaths::normalizeClass($claseNombre)` o `Clase::normalizeClassName($clase)`. Esto remueve prefijos numéricos (ej. `"1 - MOLDE"` $\rightarrow$ `"MOLDE"`), acentos y espacios irregulares.
3. **Categorización oficial:**
   - **Clases de Soldadura:** `config('global_classes.welding_classes')` (`molde`, `fondo`, `bombillo`, `obturador`, `corona`).
   - **Clases de Procesos:** `config('global_classes.process_classes')`.
   - **Clases Excluidas de Procesos:** `config('global_classes.process_excluded_classes')`.
4. **Matching defensivo (Singular/Plural):** Al buscar archivos en disco pertenecientes a una clase, compara tanto la versión normalizada completa como la versión limpia en singular (`rtrim($claseLimpia, 's')`).
5. **No mutar llaves primarias numéricas:** El catálogo asigna IDs fijos a clases clave (ej. `1 => MOLDE`, `2 => FONDO`, `3 => BOMBILLO`, `4 => OBTURADOR`). Respeta estos IDs en migraciones y seeds.

---

## Instrucciones y Patrones Estándar

## 1. El Archivo de Configuración Global (`config/global_classes.php`)

Este archivo es la **única fuente de verdad** para la clasificación de piezas e instrumental de producción en GIS Saavedra.

### Estructura en PHP:
```php
// Obtener el listado oficial de todas las clases (ID => Nombre)
$clasesGlobales = config('global_classes.clases', []);

// Obtener solo clases que requieren procesos de soldadura (PTA / Manual)
$weldingClasses = config('global_classes.welding_classes', []);

// Obtener clases que requieren programación en máquinas
$processClasses = config('global_classes.process_classes', []);

// Obtener clases explícitamente excluidas de maquinado
$excludedClasses = config('global_classes.process_excluded_classes', []);
```

---

## 2. Inyección Automática a JavaScript (`layouts/appMenu.blade.php`)

El layout principal del sistema inyecta estas variables a `window.*` para que los scripts frontend no tengan que hacer peticiones AJAX adicionales:

```html
<!-- Inyectado automáticamente en layouts/appMenu.blade.php -->
<script>
    window.GlobalClasses = @json(config('global_classes.clases', []));
    window.FundicionWelding = @json(config('global_classes.welding_classes', []));
    window.FundicionProcess = @json(config('global_classes.process_classes', []));
    window.FundicionProcessExcluded = @json(config('global_classes.process_excluded_classes', []));
</script>
```

### Uso en JavaScript (`almacen_fundicion.js`, `calidad_fundicion.js`, etc.):
```javascript
// ✅ PATRÓN CORRECTO: Verificar si una clase lleva soldadura en JS
function esClaseSoldadura(nombreClase) {
    if (!nombreClase) return false;
    const clean = nombreClase.toLowerCase().trim();
    const weldingList = window.FundicionWelding || ['molde', 'fondo', 'bombillo', 'obturador', 'corona'];
    return weldingList.some(w => clean.includes(w));
}
```

---

## 3. Normalización de Nombres de Clase en PHP (`FundicionPaths::normalizeClass`)

Las cadenas enviadas por los usuarios o cargadas desde la base de datos pueden venir como `"1 - MOLDES"`, `"FONDO (REPROCESO)"` o `"obturadores"`. Para compararlas limpiamente:

```php
use App\Services\FundicionPaths;

// Convierte "1 - MOLDES" -> "MOLDE" o "FONDO" -> "FONDO"
$claseNormalizada = FundicionPaths::normalizeClass($claseNombre);

// En modelos Eloquent:
use App\Models\Clase;
$claseLimpia = Clase::normalizeClassName($claseNombre);
```

---

## 4. Matching Defensivo para Filtros de Archivos de Clase en Disco

```php
// ✅ PATRÓN CORRECTO: Buscar archivos pertenecientes a una clase de modelo
$classCleanStr = strtolower(trim(preg_replace('/^\d+\s*-\s*/', '', $claseNorm)));
$classCleanSingular = rtrim($classCleanStr, 's');

foreach (scandir($directorio) ?: [] as $fileName) {
    $fileNameLower = strtolower($fileName);
    
    $matchesClase = str_contains($fileNameLower, strtolower($claseNorm)) ||
                    str_contains($fileNameLower, $classCleanStr) ||
                    str_contains($fileNameLower, $classCleanSingular);
                    
    if ($matchesClase) {
        // Archivo encontrado para esta clase
    }
}
```

---

## 5. Renderizado de Selects de Clases en Blade

```blade
{{-- ✅ PATRÓN CORRECTO: Renderizado de dropdown de clases desde config --}}
<select name="clase_nombre" id="select_clase_nombre" class="form-control">
    <option value="">Seleccione una clase...</option>
    @foreach (config('global_classes.clases', []) as $index => $className)
        <option value="{{ $className }}">{{ $index }} - {{ $className }}</option>
    @endforeach
</select>
```
