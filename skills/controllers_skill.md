---
name: controllers_skill
description: Guía y patrones para controladores Laravel, Form Requests, eager loading, transacciones DB y arquitectura en Project_saavedra.
version: 1.0.0
---

# Guía de Controladores (Controllers Skill) — Project Saavedra

> **Directorio de Referencia:** `app/Http/Controllers/`

## Cuándo usar esta habilidad
- Al crear, modificar o refactorizar controladores en `app/Http/Controllers/`.
- Al definir endpoints AJAX/JSON, Form Requests o controladores delgados (Thin Controllers).
- Al implementar transacciones DB, eager loading o manejo de errores `try/catch`.

---

## ⚠️ DIRECTIVAS DE EJECUCIÓN OBLIGATORIA PARA EL AGENTE DE IA

Cuando crees o modifiques controladores, DEBES cumplir estrictamente estas 5 reglas:

1. **DB::transaction() en escrituras multi-tabla:** Todo método que cree/actualice/elimine en más de un modelo DEBE usar `DB::beginTransaction()` y `DB::commit()` dentro de `try/catch (\Throwable $e)`. Jamás hagas escrituras encadenadas sin transacción.
2. **Respuesta Mixta o JSON estructurada:** Si es un endpoint invocado vía Fetch/AJAX, responde `response()->json(['success' => bool, 'message' => string, ...], $statusCode)`. Devuelve código HTTP adecuado (200 para éxito, 400/422 para error de entrada, 500 para excepciones internas).
3. **Controladores Delgados (Thin Controllers):** No pongas formateos pesados de HTML o strings gigantes en el controlador; pásalos al ViewModel, Helper o vista Blade.
4. **where() de 4 argumentos en este proyecto:** Para compatibilidad con el helper del proyecto, escribe la forma canónica `->where('columna', '=', $valor, 'and')`.
5. **No tragar excepciones silenciosamente:** En bloques `catch (\Throwable $e)`, DEBES registrar el error en `Log::error(...)` con contexto (`user`, `ot`, `error`) antes de responder.

---

## Instrucciones y Patrones Estándar

## 1. Nomenclatura, Rutas y Middlewares

- **Convención:** `[Entidad]Controller.php` (Ej. `PtaResultsController.php`).
- **Middlewares en el Constructor:** Todo controlador que maneje datos confidenciales debe bloquear el acceso a invitados.

```php
public function __construct() {
 $this->middleware('auth');
 // Instanciar servicios o helpers compartidos si aplica
 $this->classController = new ClassController();
}
```

---

## 2. Protección de Integridad (Transacciones y Excepciones)

Cualquier método que afecte múltiples modelos (`insert`, `update`, `delete`) **DEBE** usar `DB::transaction()` y un bloque `try/catch` para evitar bases de datos corruptas si el script falla a la mitad.

```php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

public function store(Request $request) {
 $request->validate(['n_pieza' => 'required', 'estado' => 'required']);

 try {
 DB::beginTransaction();

 $orden = Orden_trabajo::findOrFail($request->ot_id);
 $orden->estado = 'Completado';
 $orden->save();

 Pieza::where('id_ot', $orden->id)->update(['liberacion' => 1]);

 DB::commit();
 return response()->json(['success' => 'Operación completada']);

 } catch (\Throwable $e) {
 DB::rollBack();
 Log::error('Error al guardar OT.', [
 'user' => auth()->user()->matricula ?? 'N/A',
 'ot_id' => $request->ot_id,
 'error' => $e->getMessage(),
 ]);
 return response()->json(['error' => 'Ocurrió un error interno'], 500);
 }
}
```

> Usa `\Throwable` en lugar de `\Exception` para capturar también errores de PHP 7+/8+.

---

## 3. Resolución de Rendimiento (Eager Loading Avanzado)

El problema N+1 es el enemigo número uno. No basta con usar `with()`, a veces necesitas filtrar relaciones precargadas.

```php
// EXCELENTE: Precarga la moldura y SOLO las clases que NO están finalizadas
$workOrders = Orden_trabajo::query()
 ->with(['moldura', 'clases' => function($query) {
 $query->where('finalizada', 0);
 }])
 ->orderBy('created_at', 'desc')
 ->get();

// También: withCount para obtener totales sin cargar la relación completa
$ordenes = Orden_trabajo::withCount('piezas')->get();
// Acceso: $orden->piezas_count
```

---

## 4. Tipos de Respuestas (Blade vs JSON vs Redirect)

El desarrollador debe saber exactamente cómo debe responder el controlador basándose en quién hizo la solicitud.

- **Renderizado Completo (Blade):** Para navegación de menús.
 ```php
 return view('wo_views.show', compact('orden', 'piezas'));
 ```
- **Petición Fetch/AJAX:** Responder JSON siempre, controlando los códigos HTTP (200, 400, 404, 500).
 ```php
 return response()->json([
 'success' => true,
 'message' => 'Pieza procesada con éxito',
 'data' => $resultadosHtml
 ], 200);
 ```
- **Formularios Síncronos (`<form action="...">`):** Redirige a la vista anterior con mensajes de estado.
 ```php
 return redirect()->back()->with('success', 'La clase ha sido cerrada.');
 // O con errores:
 return redirect()->route('home')->withErrors(['error' => 'No tienes permiso']);
 ```
- **Detección de tipo de petición (para endpoints mixtos):**
 ```php
 if ($request->expectsJson()) {
 return response()->json(['success' => true]);
 }
 return redirect()->back()->with('success', 'Guardado');
 ```

---

## 5. Validación Avanzada con Form Requests

Para evitar saturar los controladores con reglas de validación complejas, utiliza Form Requests dedicados.

```bash
php artisan make:request GuardarPiezaRequest
```

```php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class GuardarPiezaRequest extends FormRequest
{
 public function authorize() {
 return in_array(auth()->user()->perfil, [1, 8]);
 }

 public function rules() {
 return [
 'ot_id' => 'required|exists:ordenes_trabajo,id',
 'n_pieza' => 'required|string|max:50',
 'estado' => 'required|in:Aprobado,Rechazado,Scrap',
 ];
 }

 public function messages() {
 return [
 'ot_id.exists' => 'La OT especificada no existe en el sistema.',
 ];
 }
}
```

*En el controlador, inyectas la clase directamente:*
```php
public function store(GuardarPiezaRequest $request) {
 $validated = $request->validated();
 // Si llega aquí, ya pasó validación y autorización
}
```

---

## 6. Controladores Delgados / Modelos Gordos (Thin Controllers)

Mantén los controladores enfocados únicamente en recibir la solicitud (`Request`) y devolver la respuesta (`Response`). Toda la lógica compleja debe estar en:
- El **Modelo** (scopes locales, mutadores, métodos de modelo).
- Un **Servicio** dedicado si involucra flujos de múltiples pasos.
- **Helpers** si son formateos o cálculos compartidos.

---

## 7. Tipado Estricto de Parámetros (Type Hinting)

```php
// MAL: Sin tipo, genera advertencias en IDEs estrictos.
public function destroy($idWOrder) { ... }

// BIEN: Tipado estricto. (Laravel inyecta los parámetros de ruta como strings)
public function destroy(string $idWOrder) { ... }
```

---

## 8. Controladores Clave del Proyecto (Referencia Rápida)

| Controlador | Responsabilidad |
|---|---|
| `AlmacenFundicionController.php` | Gestión de documentos, archivos, reprocesos y alertas de OTs para Almacén |
| `CalidadFundicionController.php` | Revisión, liberación y seguimiento de OTs para Calidad |
| `ProcessProductionController.php` | Control del flujo de producción en cada proceso de maquinado |
| `WOController.php` | CRUD principal de Órdenes de Trabajo (OTs) |
| `DibujosFundicionPdfController.php` | Generación de PDFs de dibujos técnicos de fundición |
| `SystemLogController.php` | Consulta y gestión del log de auditoría de eventos del sistema |
| `LiberarQRPlantaController.php` | Flujo de liberación de piezas vía escaneo de QR en planta |

### Patrón Real: Servir Archivos con Tipo (Almacén/Calidad)

```php
// Patrón en AlmacenFundicionController y CalidadFundicionController
$tipoDocumento = $request->tipo; // 'dibujo', 'ayuda', 'otro', 'calidad'
$basePath = match($tipoDocumento) {
 'dibujo' => 'DOCUMENTACION_GIS/ALMACEN_FUNDICION/' . $otSanitized . '/',
 'ayuda' => 'DOCUMENTACION_GIS/ALMACEN_FUNDICION/' . $otSanitized . '/ayudas_visuales/',
 'calidad' => 'DOCUMENTACION_GIS/CALIDAD_FUNDICION/' . $otSanitized . '/',
 default => 'DOCUMENTACION_GIS/ALMACEN_FUNDICION/' . $otSanitized . '/',
};
$fullPath = storage_path('app/' . $basePath . $archivo);
return response()->file($fullPath);
```

---

## 9. Prevención de Errores de Restricción de BD en Refactorizaciones

Cuando refactorices y quites columnas del flujo pero la tabla las siga requiriendo (`NOT NULL`), pasa un valor por defecto seguro:

```php
// MAL: Lanza SQLSTATE[HY000] si 'clase' es NOT NULL
AyudaVisualHistory::firstOrCreate(['proceso' => $proceso]);

// BIEN: 'clase' se llena con 'N/A' como fallback si el registro no existe
AyudaVisualHistory::firstOrCreate(['proceso' => $proceso], ['clase' => 'N/A']);
```

---

## 10. Patrón de Abort y Respuestas de Error Tempranas

Usa `abort()` para cortar el flujo inmediatamente ante condiciones inválidas, en lugar de anidar bloques `if`.

```php
public function show(string $otId) {
 $registro = FundicionHistory::where('ot', $otId)->firstOrFail(); // Lanza 404 si no existe

 // Verificar perfil
 if (!in_array(auth()->user()->perfil, [1, 2, 4, 5])) {
 abort(403, 'No tienes permiso para ver este recurso.');
 }

 return view('calidad.detalle', compact('registro'));
}
```

---

## 11. Patrón de Respuesta para AJAX con Estado de Operación

Cuando el endpoint puede ser llamado tanto por formularios síncronos como por Fetch:

```php
// Patrón estándar de respuesta mixta (web + AJAX)
private function responder(Request $request, bool $success, string $message, array $data = []) {
 if ($request->expectsJson()) {
 return response()->json(array_merge(['success' => $success, 'message' => $message], $data),
 $success ? 200 : 400
 );
 }
 $flashKey = $success ? 'success' : 'error';
 return redirect()->back()->with($flashKey, $message);
}
```
---

## 12. Regla del IDE — `where()` con 4 argumentos siempre

> ⚠️ **Específico de este proyecto.** El `_ide_helper.php` generado fuerza que el análisis estático espere los 4 argumentos de `where()` en cualquier encadenamiento.

**SIEMPRE usa la forma de 4 argumentos** para evitar warnings "Not enough arguments. Expected 4":

```php
// ❌ MAL: El IDE se queja aunque Laravel funcione bien en runtime
Model::where('columna', $valor)->get();
Model::where('columna', '=', $valor)->get();

// ✅ BIEN: Forma canónica de este proyecto (4 args explícitos)
Model::where('columna', '=', $valor, 'and')->get();

// ✅ BIEN: En cadenas, TODOS los where() deben llevar 4 args
Model::where('col1', '=', $val1, 'and')
    ->where('col2', '!=', $val2, 'and')
    ->get();

// ✅ BIEN: whereIn() también necesita los 4 argumentos
Model::whereIn('columna', [1, 2, 3], 'and', false)->get();
```

El 4° argumento `'and'` es el valor por defecto de Laravel — no cambia el comportamiento en runtime.
El `whereIn()` también necesita `$boolean = 'and'` y `$not = false` explícitos.

---

## 13. Resolución Fuzzy de Directorios de Servidor y Auditoría de Archivos

Cuando se manejen carpetas en disco organizadas jerárquicamente (`OT` -> `Clase` -> `Proceso`), el nombre de las carpetas físicamente en el servidor puede diferir sutilmente de las cadenas enviadas por el frontend o de los nombres cortos de los modelos (ej: la carpeta en disco es `"1 - MOLDES"` o `"OT 8890 — JOSE CUERVO"`, pero el cliente consulta `"Molde"` o `"8890 - JOSE CUERVO"`).

#### Patrón Recomendado:
1. **Búsqueda Fuzzy e Insensible a Mayúsculas (`resolveCaseInsensitivePath`)**:
   Empareja partes de ruta normalizando espacios, guiones largos (`—` vs `-`), y usando `Clase::normalizeClassName()` o regex de ID numérico para OTs (`preg_match('/(?:OT\s*)?(\d+)/i')`).
2. **Auditoría Obligatoria de Eventos de Archivo**:
   Toda creación de directorio, subida de archivo o eliminación debe registrarse en la tabla de auditoría dedicada (ej. `programas_maquinados_file_log`) registrando matrícula del usuario, IP, OT, Clase, Proceso y tipo de acción (`CREAR_CARPETA`, `SUBIR_ARCHIVO`, `ELIMINAR_ARCHIVO`).

```php
// Ejemplo de resolución case-insensitive y normalizada de carpetas:
private function resolveCaseInsensitivePath(string $basePath, array $pathSegments): ?string {
    $current = rtrim($basePath, '/\\');
    foreach ($pathSegments as $segment) {
        if (!is_dir($current)) return null;
        $entries = scandir($current);
        $found = null;
        $targetNorm = mb_strtolower(trim(preg_replace('/\s+/', ' ', $segment)));
        
        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') continue;
            $entryNorm = mb_strtolower(trim(preg_replace('/\s+/', ' ', $entry)));
            if ($entryNorm === $targetNorm || Clase::normalizeClassName($entry) === Clase::normalizeClassName($segment)) {
                $found = $entry;
                break;
            }
        }
        if (!$found) return null;
        $current .= DIRECTORY_SEPARATOR . $found;
    }
    return $current;
}
```

---

## 13. Sincronización Segura de Archivos sin Afectar Estados (Mantener / Reemplazar)

Cuando un usuario desea "Reemplazar" archivos de ingeniería en el sistema, pero el proceso ya avanzó (ej. Calidad ya liberó el modelo), NO DEBES ejecutar consultas de `delete()` sobre el historial ni sobre tablas transaccionales (`LiberacionModeloFundicion`, `PreOrdenFundicion`).

El código debe separar la lógica de **reinicio** (que borra estados y base de datos) de la lógica de **reemplazo** (que solo sincroniza archivos en disco usando `copyToAlmacen` con `$resetFlags = false` y actualiza los Hashes).

---

## 14. Protección de Configuraciones Hijas en Ediciones Parciales

Al editar modelos padres que tienen configuraciones hijas críticas (ej: `Clase` y su registro hijo en `Procesos` con 24 columnas de máquinas y tiempos de maquinado):

1. **Discriminar el contexto de la edición:**
   - Formularios parciales (ej. Almacén cambiando cantidad de piezas, fechas o material) deben enviar un flag explicito como `partial_edit = 1` o `from_almacen = 1`.
   - Vistas de programación completa deben enviar `from_programming = 1` y el array de operaciones seleccionadas.
   - Si la edición es parcial y no trae `operations`, el controlador principal **NO debe invocar el método de guardado de procesos**.

2. **Salvaguarda defensiva en métodos de asignación (`storeProcess`):**
   - Si el método recibe `$dataProcess === null` y el registro hijo ya existe en la base de datos (`$child->exists`), **DEBE retornar de inmediato**:
     ```php
     // Proteger configuración existente si la petición no incluye operaciones
     if ($dataProcess === null && $process->exists) {
         return;
     }
     ```
   - Nunca iterar columnas para ponerlas a `0` a menos que sea un reinicio explícito o una deselección intencional desde la pantalla de configuración.

3. **Mapeo robusto de máquinas asociativas:**
   - El frontend puede enviar `machines[cepillado] => 2` o un array numérico `machines[0] => 2`.
   - Leer siempre con prioridad asociativa por nombre de proceso y fallback defensivo al índice:
     ```php
     $machineVal = 1;
     if (is_array($machines)) {
         if (isset($machines[$procName]) && (int)$machines[$procName] > 0) {
             $machineVal = (int)$machines[$procName];
         } elseif (isset($machines[$counter]) && (int)$machines[$counter] > 0) {
             $machineVal = (int)$machines[$counter];
         }
     }
     ```


---

## 15. Estándares Obligatorios de Limpieza, Tipado y Cero Sobrecarga

1. **Cero Imports Muertos y Estandarización de FQCNs a Cabecera:**
   - Prohibido dejar declaraciones `use` no utilizadas tras refactorizaciones de modelos (ej. antiguos `*_pza` tras migrar a `Pieza::class`).
   - Prohibido escribir FQCNs dispersos inline (`\App\Models\Clase::`, `\DB::`, `\Log::`, `\Cache::`, `\Carbon\Carbon::`, `\Illuminate\Support\Facades\Storage::`). Todos deben importarse formalmente al inicio del archivo.
   - Si un modelo de BD utiliza nomenclatura no estándar en camelCase o snake_case (ej. `revCalificado_tolerancia`), definir un alias en PascalCase en el import para evitar advertencias de servidores de lenguaje e IDEs:
     ```php
     use App\Models\revCalificado_tolerancia as RevCalificadoTolerancia;
     ```

2. **Prohibición Estricta de Propiedades Muertas e Instanciaciones en `__construct()`:**
   - Prohibido declarar propiedades de clase (`protected $classController;`, `protected $releasedPiecesController;`, `protected $cepilladoController;`) e instanciar controladores secundarios en el constructor si no son invocados directamente en ningún método del controlador.
   - Instanciar controladores que no se usan consume memoria RAM y ciclos de procesamiento innecesarios en cada solicitud HTTP. Si un método requiere asistencia de otro controlador, instáncialo localmente dentro de ese método específico o reutiliza un Service / Helper.

3. **Paginación por Cursor y Resolución Estática de Tipos (`hasMorePages`):**
   - El método `->cursorPaginate()` devuelve formalmente la interfaz `Illuminate\Contracts\Pagination\CursorPaginator`, la cual omite la firma del método `hasMorePages()`. Sin embargo, la clase concreta en tiempo de ejecución sí lo implementa.
   - Para evitar falsos positivos del analizador estático (PHP Intelephense / Language Server), documentar la variable con un bloque PHPDoc:
     ```php
     /** @var \Illuminate\Pagination\CursorPaginator $paginator */
     $paginator = $query->orderByDesc('system_logs.id')->cursorPaginate($limit)->withQueryString();
     ```

4. **Formateo Estricto PSR-12 y Verificación Silenciosa:**
   - Todo controlador creado o editado debe formatearse bajo el estándar oficial de Laravel ejecutando `.\vendor\bin\pint.bat app/Http/Controllers/[NombreController].php`.
   - Validar siempre la sintaxis post-edición con `php -l [ruta/al/controlador.php]`.
