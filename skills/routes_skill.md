# Guía de Rutas (Routes Skill) — Project Saavedra

> ** Directorio de Referencia:** `routes/web.php y routes/api.php`
> *Usa los archivos en este directorio como referencia para convenciones de naming y agrupación de rutas.*

Las rutas de `Project_saavedra` definen el contrato entre el frontend y los controladores. Deben ser predecibles, con nombres claros y middlewares correctos.

---

## 1. Convenciones de Nomenclatura (Naming)

Todas las rutas **DEBEN** tener un nombre (`->name()`) para poder generarlas dinámicamente con `route()` en Blade y `window.routes` en JS.

| Acción | Patrón de Nombre | Ejemplo |
|---|---|---|
| Vista principal (index) | `modulo.index` | `almacen.fundicion.index` |
| Ver detalle de recurso | `modulo.show` | `almacen.fundicion.show` |
| Crear recurso (GET form) | `modulo.create` | `wo.create` |
| Guardar nuevo recurso | `modulo.store` | `wo.store` |
| Editar recurso (GET form) | `modulo.edit` | `wo.edit` |
| Actualizar recurso | `modulo.update` | `wo.update` |
| Eliminar recurso | `modulo.destroy` | `wo.destroy` |
| Endpoints AJAX/API | `modulo.accion` | `almacen.fundicion.delete.pdf` |

---

## 2. Estructura de Rutas por Módulo

```php
// routes/web.php

use App\Http\Controllers\AlmacenFundicionController;
use App\Http\Controllers\CalidadFundicionController;

// =====================================================
// MÓDULO: ALMACÉN FUNDICIÓN
// =====================================================
Route::middleware(['auth'])->prefix('almacen')->name('almacen.')->group(function () {

 Route::prefix('fundicion')->name('fundicion.')->group(function () {
 Route::get('/', [AlmacenFundicionController::class, 'index'])->name('index');
 Route::get('/detalle/{ot}', [AlmacenFundicionController::class, 'show'])->name('show');

 // Endpoints AJAX (retornan JSON)
 Route::post('/subir-pdf', [AlmacenFundicionController::class, 'subirPdf'])->name('upload.pdf');
 Route::post('/eliminar-pdf', [AlmacenFundicionController::class, 'eliminarPdf'])->name('delete.pdf');
 Route::get('/servir-archivo/{ot}/{archivo}/{tipo}', [AlmacenFundicionController::class, 'servirArchivo'])->name('serve');
 Route::post('/enviar-alerta', [AlmacenFundicionController::class, 'enviarAlerta'])->name('send.alert');
 });

});

// =====================================================
// MÓDULO: CALIDAD FUNDICIÓN
// =====================================================
Route::middleware(['auth'])->prefix('calidad')->name('calidad.')->group(function () {

 Route::prefix('fundicion')->name('fundicion.')->group(function () {
 Route::get('/', [CalidadFundicionController::class, 'index'])->name('index');
 Route::post('/liberar', [CalidadFundicionController::class, 'liberar'])->name('liberar');
 Route::get('/servir-archivo/{ot}/{archivo}/{tipo}', [CalidadFundicionController::class, 'servirArchivo'])->name('serve');
 });

});
```

---

## 3. Middlewares en Rutas

### Middleware `auth` (Obligatorio para todo)
```php
// Global para todo el módulo
Route::middleware(['auth'])->group(function () {
 // Todas las rutas aquí requieren login
});
```

### Middleware `CheckPtaAccess` (Flujo PTA)
```php
Route::middleware(['auth', 'CheckPtaAccess'])->prefix('pta')->name('pta.')->group(function () {
 Route::get('/inicio', [PtaController::class, 'inicio'])->name('inicio');
 Route::post('/confirmar', [PtaController::class, 'confirmar'])->name('confirmar');
});
```

### Verificación de perfil en la ruta (alternativa al middleware)
Para casos específicos donde el perfil se verifica en el controlador (no en ruta):
```php
// En el controlador, al inicio del método:
if (!in_array(auth()->user()->perfil, [1, 2, 4])) {
 abort(403, 'No tienes permisos para acceder a esta sección.');
}
```

---

## 4. Rutas de Recursos (Resource Controllers)

Para CRUDs completos, usa `Route::resource` que genera las 7 rutas estándar automáticamente:

```php
// Genera: index, create, store, show, edit, update, destroy
Route::resource('ordenes-trabajo', WOController::class)->middleware('auth');
```

Para CRUDs parciales (solo algunas acciones):
```php
// Solo index, show y store
Route::resource('qrs', QrGeneradoController::class)
 ->only(['index', 'show', 'store'])
 ->middleware('auth');
```

---

## 5. Parámetros de Ruta y Restricciones

```php
// Parámetro string (nombre de OT)
Route::get('/detalle/{ot}', [AlmacenFundicionController::class, 'show'])
 ->where('ot', '[A-Za-z0-9_\-]+') // Solo caracteres seguros para OT
 ->name('almacen.fundicion.show');

// Parámetro entero (ID numérico)
Route::delete('/piezas/{id}', [PiezasController::class, 'destroy'])
 ->where('id', '[0-9]+')
 ->name('piezas.destroy');

// Múltiples parámetros
Route::get('/servir/{ot}/{archivo}/{tipo}', [AlmacenFundicionController::class, 'servirArchivo'])
 ->name('almacen.fundicion.serve');
```

---

## 6. Generación de URLs en Blade y JS

### En Blade (PHP)
```blade
{{-- URL simple --}}
<a href="{{ route('almacen.fundicion.index') }}">Almacén</a>

{{-- URL con parámetros --}}
<a href="{{ route('almacen.fundicion.show', ['ot' => $otName]) }}">Ver OT</a>

{{-- URL con múltiples parámetros --}}
<a href="{{ route('almacen.fundicion.serve', ['ot' => $ot, 'archivo' => $archivo, 'tipo' => 'dibujo']) }}">
 Ver Archivo
</a>
```

### En JavaScript (a través de `window.routes`)
```blade
{{-- Inyectar rutas desde Blade al JS --}}
<script>
 window.routes = {
 ...(window.routes || {}),
 // Rutas simples
 almacenIndex: @json(route('almacen.fundicion.index')),
 almacenDeletePdf: @json(route('almacen.fundicion.delete.pdf')),

 // Rutas con placeholders para reemplazar en JS
 almacenShowOt: @json(route('almacen.fundicion.show', ['ot' => ':ot'])),
 almacenServeFile: @json(route('almacen.fundicion.serve', ['ot' => ':ot', 'archivo' => ':archivo', 'tipo' => ':tipo'])),
 };
</script>
```

En JavaScript:
```javascript
// Reemplazar placeholders
const url = window.routes.almacenShowOt.replace(':ot', otName);
const fileUrl = window.routes.almacenServeFile
 .replace(':ot', ot)
 .replace(':archivo', archivo)
 .replace(':tipo', tipo);
```

---

## 7. Rutas de API (`routes/api.php`)

Para endpoints puramente de datos sin sesión web (si aplica):

```php
// routes/api.php
Route::middleware(['auth:sanctum'])->group(function () {
 Route::get('/ots', [WOController::class, 'apiIndex'])->name('api.ots.index');
 Route::post('/piezas/procesar', [PiezasController::class, 'apiProcesar'])->name('api.piezas.procesar');
});
```

> La mayoría de endpoints del proyecto están en `web.php` con sesión Laravel, no en `api.php`. Solo usar `api.php` si es un endpoint consumido desde fuera del sistema (apps móviles, integraciones externas).

---

## 8. Verificar y Depurar Rutas

```bash
# Listar todas las rutas (con nombres, URIs y controladores)
php artisan route:list

# Filtrar rutas por nombre
php artisan route:list --name=almacen

# Filtrar rutas por URI
php artisan route:list --path=fundicion

# Filtrar rutas por método HTTP
php artisan route:list --method=POST

# Limpiar caché de rutas (si usas route:cache)
php artisan route:clear
```

---

## 9. Rutas Principales del Proyecto (Referencia Rápida)

| Nombre de Ruta | Método | URI | Controlador |
|---|---|---|---|
| `almacen.fundicion.index` | GET | `/almacen/fundicion` | `AlmacenFundicionController@index` |
| `almacen.fundicion.show` | GET | `/almacen/fundicion/detalle/{ot}` | `AlmacenFundicionController@show` |
| `almacen.fundicion.delete.pdf` | POST | `/almacen/fundicion/eliminar-pdf` | `AlmacenFundicionController@eliminarPdf` |
| `almacen.fundicion.serve` | GET | `/almacen/fundicion/servir/{ot}/{archivo}/{tipo}` | `AlmacenFundicionController@servirArchivo` |
| `calidad.fundicion.index` | GET | `/calidad/fundicion` | `CalidadFundicionController@index` |
| `calidad.fundicion.serve` | GET | `/calidad/fundicion/servir/{ot}/{archivo}/{tipo}` | `CalidadFundicionController@servirArchivo` |
| `home` | GET | `/home` | `HomeController@index` |

> Para ver la lista completa y actualizada: `php artisan route:list`

---

## 10. Orden de Declaración de Rutas (Evitar Conflictos)

Laravel evalúa las rutas en orden de declaración. Las rutas más específicas deben ir **antes** que las genéricas:

```php
// CORRECTO: Más específica primero
Route::get('/almacen/fundicion/crear', [AlmacenFundicionController::class, 'crear'])->name('almacen.fundicion.crear');
Route::get('/almacen/fundicion/{ot}', [AlmacenFundicionController::class, 'show'])->name('almacen.fundicion.show');

// MAL: La ruta con parámetro captura '/almacen/fundicion/crear' antes
Route::get('/almacen/fundicion/{ot}', [AlmacenFundicionController::class, 'show'])->name('almacen.fundicion.show');
Route::get('/almacen/fundicion/crear', [AlmacenFundicionController::class, 'crear'])->name('almacen.fundicion.crear');
```
