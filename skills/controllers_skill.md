# Guía de Controladores (Controllers Skill) — Project Saavedra

> ** Directorio de Referencia:** `app/Http/Controllers/`
> *Usa los archivos en este directorio como base o inspiración al crear/modificar funcionalidades relacionadas con esta skill.*

Los controladores en `Project_saavedra` son el corazón del sistema. Deben ser robustos, a prueba de fallos y estrictamente optimizados. No se permite código espagueti.

---

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
