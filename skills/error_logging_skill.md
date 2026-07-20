# Guía de Registro de Errores y Depuración (Error Logging Skill)

> ** Directorio de Referencia:** `storage/logs/ y app/Exceptions/`
> *Usa los archivos en este directorio como base o inspiración al crear/modificar funcionalidades relacionadas con esta skill.*


El registro adecuado de errores es vital para mantener la estabilidad de `Project_saavedra` y diagnosticar fallos silenciosos en la planta sin interrumpir la operación de los operarios.

---

## 1. La Diferencia entre Logs de Sistema y Logs de Auditoría de Negocio
En este sistema existen dos tipos de registros muy diferenciados:

1. **SystemLog (Logs de Auditoría en Base de Datos):**
 - Registra eventos de negocio: inicio de sesión, cambio de estado de OT, piezas maquinadas, etc.
 - Son visibles para los administradores del sistema y se guardan en la base de datos a través del modelo `SystemLog`.
2. **Laravel Logs (Logs de Sistema en Archivo):**
 - Registra errores técnicos, excepciones de base de datos (`PDOException`), fallos de red, etc.
 - Se guardan físicamente en el servidor en `storage/logs/laravel.log` y son administrados por el Facade `Illuminate\Support\Facades\Log`.

---

## 2. Niveles de Logs del Sistema (Facade `Log`)
Utiliza el nivel de severidad correcto según la naturaleza de la información:

- **`Log::error()`**: Errores críticos que detienen un proceso (ej. Excepción SQL, fallo al escribir en disco, error de pasarela). Requiere intervención técnica.
- **`Log::warning()`**: Comportamiento inesperado pero no crítico (ej. Intento de acceso sin permisos suficientes, parámetros inusuales).
- **`Log::info()`**: Flujos normales importantes (ej. Inicio de depuración de logs manual por Artisan).
- **`Log::debug()`**: Telemetría detallada para desarrollo local (ej. Payload de un webhook o API de QR).

---

## 3. Patrón de Logging Blindado con Contexto (Buenas Prácticas)
Nunca concatenes strings dentro del log. Pasa el mensaje base y adjunta un **array asociativo con el contexto**. Esto facilita enormemente la lectura y análisis con herramientas externas.

- ** PÉSIMO:**
```php
Log::error("Error al procesar pieza " . $pieza->id . " por el usuario " . Auth::user()->id . " - Error: " . $e->getMessage());
```

- ** EXCELENTE (Datos estructurados):**
```php
Log::error('Fallo al procesar liberación de pieza.', [
 'pieza_id' => $pieza->id,
 'matricula_user' => auth()->user()->matricula ?? 'sistema',
 'proceso' => $request->proceso,
 'error' => $e->getMessage(),
 'trace' => $e->getTraceAsString() // Adjuntar traza completa solo para errores críticos
]);
```

---

## 4. Control de Excepciones Silencioso vs Ruidoso
Dependiendo del tipo de fallo, debemos decidir si mostramos el error al usuario final o lo registramos de manera silenciosa devolviendo un mensaje amigable.

### A. Fallos del Sistema Críticos (Base de datos, Red, Archivos)
**Acción:** Capturar de forma silenciosa, guardar log detallado, revertir transacción (rollback) y devolver un mensaje genérico al usuario. **No expongas trazas SQL al usuario final.**

```php
try {
 DB::beginTransaction();
 // Operación SQL crítica...
 DB::commit();
} catch (\Throwable $e) {
 DB::rollBack();

 Log::error('Error crítico en base de datos al guardar OT', [
 'user' => auth()->user()->matricula ?? 'N/A',
 'input' => $request->only(['ot_id', 'piezas']),
 'error' => $e->getMessage()
 ]);

 return response()->json([
 'success' => false,
 'message' => 'Ocurrió un error interno del sistema. Por favor intente más tarde.'
 ], 500);
}
```

### B. Fallos de Validación de Negocio (Campos inválidos, estados incorrectos)
**Acción:** Lanzar excepción directamente o devolver errores de validación de Laravel (error 422 / 400). **No es necesario llenar el log de errores con fallos cometidos por el usuario.**

```php
if ($bote->estado === 'liberado') {
 // Es un flujo esperado de negocio, no requiere Log::error
 return back()->withErrors(['qr_content' => 'Este bote ya fue liberado']);
}
```
---

## 5. Ubicación e Inspección de Logs en Servidor
Los logs se registran en:
`c:\Users\Jaxer020406\Documents\GitHub\Project_saavedra\storage\logs\laravel.log` (Entorno local)
Puedes consultar los últimos registros en tiempo real en la terminal ejecutando:
```powershell
Get-Content -Path "storage/logs/laravel.log" -Tail 50 -Wait
```
o en entornos Linux:
```bash
tail -f storage/logs/laravel.log
```

---

## 6. Modelo `SystemLog` — Auditoría de Negocio en BD
El proyecto usa un modelo específico para registrar eventos de negocio en la base de datos (distintos al `laravel.log`):

```php
// Cómo registrar un evento de auditoría de negocio
use App\Models\SystemLog;

SystemLog::create([
 'user_id' => auth()->id(),
 'user_name' => auth()->user()->nombre ?? 'Sistema',
 'accion' => 'elimino_archivo_fundicion',
 'descripcion'=> "Eliminó el archivo '{$archivo}' de la OT '{$ot}'",
 'modulo' => 'Almacén Fundición',
 'ip' => $request->ip(),
]);
```

### Archivos de Log del Sistema (Rutas Reales)
- **Laravel Log (Técnico):** `storage/logs/laravel.log`
- **Revisar en tiempo real (PowerShell):**
```powershell
Get-Content -Path "storage/logs/laravel.log" -Tail 100 -Wait
```
- **Los logs de BD** se consultan en la vista `SystemLogController@index` con filtros por módulo, usuario y fecha.
