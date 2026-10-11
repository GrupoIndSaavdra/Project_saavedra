---
name: file_storage_skill
description: Guía y patrones para la gestión de archivos en disco, almacenamiento en Storage, normalización de rutas Windows/Linux, sufijos de reproceso (_R1) y resolución fuzzy en Project_saavedra.
version: 1.0.0
---

# Habilidad de Almacenamiento y Archivos (File Storage Skill) — Project Saavedra

> **Directorio de Referencia:** `storage/app/DOCUMENTACION_GIS/ y app/Services/FundicionPaths.php`

---

## ⚠️ DIRECTIVAS DE EJECUCIÓN OBLIGATORIA PARA EL AGENTE DE IA

Cuando crees o modifiques código que manipule archivos o carpetas en disco, DEBES cumplir estrictamente estas 5 reglas:

1. **Normalización obligatoria de slashes:** Usa `str_replace('\\', '/', $path)` tanto en PHP como en JS. Windows usa `\` que rompe URLs, expresiones regulares y comparaciones de texto.
2. **Uso de `Storage::disk('local')`:** NUNCA accedas con `file_exists()` sobre rutas relativas sin sanitizar. Usa `Storage::disk('local')->exists(...)` o `storage_path('app/...')` con sanitización previa (`preg_replace('/[^a-zA-Z0-9_\-\. ]/', '_', $ot)`).
3. **Resolución Fuzzy de Carpetas (`resolveCaseInsensitivePath`):** Para emparejar OTs en disco (ej. `"OT 8890 — JOSE CUERVO"` vs `"8890"`), usa la normalización de la clase `FundicionPaths::normalizeClass()` o regex numérico de OT.
4. **Sufijos de Reproceso (`_R1`, `_R2`):** Al generar o buscar archivos de pre-órdenes (PFM, PFC) o escaneos de reproceso, extrae y concatena la firma de reproceso mediante `preg_match('/_R\d+$/i', $ot)`.
5. **Auditoría de Archivos:** Toda subida, reemplazo o borrado de archivos en disco DEBE registrar la acción en `FundicionFileLog` o `SystemLog` con la matrícula del usuario, IP y OT.

---

## Instrucciones y Patrones Estándar

## 1. Estructura de Rutas de Almacenamiento en Disco

```
storage/app/DOCUMENTACION_GIS/
├── ALMACEN_FUNDICION/
│   └── {OT_SANITIZADA}/
│       ├── F_ALM_PFM_{CLASES}_{SUFIJO}.pdf   ← Pre-Órdenes Metal
│       ├── F_ALM_PFC_{CLASES}_{SUFIJO}.pdf   ← Pre-Órdenes Casting
│       ├── ayudas_visuales/                 ← Ayudas visuales por OT
│       └── preordenes/                      ← Documentos aprobados
└── CALIDAD_FUNDICION/
    └── {OT_SANITIZADA}/
        ├── SCAR_{CLASES}.pdf                ← Reportes SCAR
        ├── RDM_{CLASES}.pdf                 ← Reportes RDM
        └── liberaciones/                    ← Liberaciones de modelo
```

---

## 2. Patrón de Normalización de Rutas (Cross-Platform)

```php
// ✅ PATRÓN CORRECTO: Normalización universal de rutas
public static function sanitizePath(string $path): string {
    $normalized = str_replace('\\', '/', $path);
    return preg_replace('#/+#', '/', $normalized);
}

// Sanitizar nombres de OT para uso seguro en sistema de archivos
public static function sanitizeOtName(string $ot): string {
    $clean = trim($ot);
    return preg_replace('/[^a-zA-Z0-9_\-\. ]/', '_', $clean);
}
```

---

## 3. Resolución Fuzzy Case-Insensitive de Carpetas

```php
// ✅ PATRÓN CORRECTO: Resolver carpetas con nombres inconsistentes en disco
public function resolveCaseInsensitivePath(string $basePath, array $segments): ?string {
    $current = rtrim(str_replace('\\', '/', $basePath), '/');
    
    foreach ($segments as $segment) {
        if (!is_dir($current)) return null;
        
        $entries = scandir($current);
        $found = null;
        $targetNorm = mb_strtolower(trim(preg_replace('/\s+/', ' ', $segment)));
        
        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') continue;
            $entryNorm = mb_strtolower(trim(preg_replace('/\s+/', ' ', $entry)));
            
            if ($entryNorm === $targetNorm || \App\Services\FundicionPaths::normalizeClass($entry) === \App\Services\FundicionPaths::normalizeClass($segment)) {
                $found = $entry;
                break;
            }
        }
        
        if (!$found) return null;
        $current .= '/' . $found;
    }
    
    return $current;
}
```

---

## 4. Preservación del Sufijo de Reproceso (`_R1`, `_R2`)

```php
// ✅ PATRÓN CORRECTO: Conservar el sufijo de reproceso al renombrar/guardar PDFs
$reprocesoMatch = [];
preg_match('/_R\d+$/i', $otRaw, $reprocesoMatch);
$suffixReproceso = !empty($reprocesoMatch) ? strtoupper($reprocesoMatch[0]) : '';

$fileName = "F_ALM_PFM_{$clasesStr}{$suffixReproceso}.pdf";
```

---

## 5. Auditoría Obligatoria de Archivos (`FundicionFileLog`)

```php
// ✅ PATRÓN CORRECTO: Registrar evento de archivo en auditoría
\App\Models\FundicionFileLog::create([
    'ot' => $ot,
    'clase' => $clase,
    'archivo' => $nombreArchivo,
    'accion' => 'SUBIR_DIBUJO', // SUBIR_DIBUJO, ELIMINAR_PDF, REEMPLAZAR_DIBUJO
    'user_id' => auth()->id(),
    'user_matricula' => auth()->user()->matricula ?? 'N/A',
    'ip' => request()->ip(),
]);
```
