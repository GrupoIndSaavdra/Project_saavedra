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

# Limpiar caché de rutas (si usas route:cache)
php artisan route:clear
```

---

## 9. Rutas Principales del Proyecto — Referencia Completa

> **Fuente:** `php artisan route:list` ejecutado el 2026-07-30. Total: 217 rutas registradas.

### Módulo: Auth
| Nombre | Método | URI | Controlador |
|---|---|---|---|
| `login` | GET | `/login` | `LoginController@show` |
| `loginUser` | POST | `/login` | `LoginController@login` |
| `logout` | GET | `/logout` | `LogoutController@logout` |
| `recoverPassword` | GET | `/users/recoverPassword` | `UserController@showRecoverPassword` |
| `recover` | POST | `/users/recoverPassword` | `UserController@recoverPassword` |

### Módulo: Home
| Nombre | Método | URI | Controlador |
|---|---|---|---|
| `home` | GET | `/home` | `HomeController@index` |

### Módulo: Usuarios
| Nombre | Método | URI | Controlador |
|---|---|---|---|
| `users` | GET | `/users` | `UserController@show` |
| `createUser` | GET | `/users/create` | `UserController@create` |
| `storeUser` | POST | `/users/create/store` | `UserController@store` |
| `update_usuario` | POST | `/users/{id}` | `UserController@updateUsuario` |
| `eliminar_usuario` | DELETE | `/eliminar-usuario/{id}` | `UserController@eliminarUsuario` |
| `baja_usuario` | POST | `/baja-usuario/{id}` | `UserController@bajaUsuario` |
| `productionData` | GET | `/productionData` | `DatosProduccionController@index` |
| `showProduccion` | POST | `/productionData` | `DatosProduccionController@show` |

### Módulo: Almacén Fundición
| Nombre | Método | URI | Controlador |
|---|---|---|---|
| `almacen.fundicion.index` | GET | `/almacen/fundicion` | `AlmacenFundicionController@index` |
| `almacen.fundicion.show` | GET | `/almacen/fundicion/detalle/{ot}` | `AlmacenFundicionController@show` |
| `almacen.fundicion.getFiles` | GET | `/almacen/fundicion/archivos` | `AlmacenFundicionController@getFiles` |
| `almacen.fundicion.serve` | GET | `/almacen/fundicion/servir/{ot}/{archivo}/{tipo}` | `AlmacenFundicionController@servirArchivo` |
| `almacen.fundicion.uploadPdf` | POST | `/almacen/fundicion/subir-pdf` | `AlmacenFundicionController@subirPdf` |
| `almacen.fundicion.delete.pdf` | POST | `/almacen/fundicion/eliminar-pdf` | `AlmacenFundicionController@eliminarPdf` |
| `almacen.fundicion.send.alert` | POST | `/almacen/fundicion/enviar-alerta` | `AlmacenFundicionController@enviarAlerta` |

### Módulo: Calidad Fundición
| Nombre | Método | URI | Controlador |
|---|---|---|---|
| `calidad.fundicion.index` | GET | `/calidad/fundicion` | `CalidadFundicionController@index` |
| `calidad.fundicion.serve` | GET | `/calidad/fundicion/serve` | `CalidadFundicionController@serveFile` |
| `calidad.fundicion.archivos` | GET | `/calidad/fundicion/archivos` | `CalidadFundicionController@getFiles` |
| `calidad.fundicion.deleteFile` | POST | `/calidad/fundicion/delete-file` | `CalidadFundicionController@deleteFile` |
| `calidad.fundicion.getLiberacion` | GET | `/calidad/fundicion/liberacion` | `CalidadFundicionController@getLiberacion` |
| `calidad.fundicion.submitLiberacion` | POST | `/calidad/fundicion/submit-liberacion` | `CalidadFundicionController@submitLiberacion` |
| `calidad.fundicion.generateScar` | POST | `/calidad/fundicion/generate-scar` | `CalidadFundicionController@generateScar` |
| `calidad.fundicion.getScar` | GET | `/calidad/fundicion/get-scar` | `CalidadFundicionController@getScar` |
| `calidad.fundicion.sendScarAlert` | POST | `/calidad/fundicion/send-scar-alert` | `CalidadFundicionController@sendScarAlert` |
| `calidad.fundicion.enviarAlertaLiberacion` | POST | `/calidad/fundicion/enviar-alerta-liberacion` | `CalidadFundicionController@enviarAlertaLiberacion` |

### Módulo: Calidad Maquinados
| Nombre | Método | URI | Controlador |
|---|---|---|---|
| `calidad.maquinados.index` | GET | `/calidad/maquinados` | `CalidadMaquinadosController@index` |
| `calidad.maquinados.docs` | GET | `/calidad/maquinados/docs` | `CalidadMaquinadosController@getDocs` |
| `calidad.maquinados.serve` | GET | `/calidad/maquinados/serve` | `CalidadMaquinadosController@serveFile` |

### Módulo: Órdenes de Trabajo (WO)
| Nombre | Método | URI | Controlador |
|---|---|---|---|
| `manageWO` | GET | `/manageWO` | `WOController@manage` |
| `storeWO` | POST | `/storeWO` | `WOController@store` |
| `showWO` | GET | `/showWO/{workOrder}` | `WOController@show` |
| `show_panelWO` | GET | `/show_panelWO` | `WOController@show_panelWO` |
| `destroyWO` | GET | `/destroyWO/{wo}` | `WOController@destroy` |
| `fundicionUpdateFlag` | POST | `/fundicion/updateFlag` | `WOController@markFundicionFlag` |
| `panelProgreso` | GET | `/panel-progreso` | *(generada)* |
| `showPiecesInProgress` | GET | `/piecesInProgress` | `WOController@showViewPiecesInProgress` |
| `savePriorities` | POST | `/piecesInProgress/priorities` | `WOController@savePriorities` |
| `showPriorityManager` | GET | `/piecesInProgress/priorityManager` | `WOController@showPriorityManager` |
| `generatePDFWO` | GET | `/generatePDFWO/{wo}` | `WOController@generatePDF` |
| `finishOrder` | GET | `/finishOrder/{wOrderName}/{className}` | `WOController@finishOrder` |

### Módulo: Piezas
| Nombre | Método | URI | Controlador |
|---|---|---|---|
| `showPiecesReport_view` | GET | `/pieces` | `PzasGeneralesController@showPiecesReport_view` |
| `chosenPiece` | GET | `/pieces/{pieces}/{process}/{profile}` | `PzasGeneralesController@showPiece` |
| `searchPieces` | GET/POST | `/pieces/search` | `PzasGeneralesController@getPiecesRequest` |
| `showReleasePieces_view` | GET | `/releasePieces` | `PzasLiberadasController@show` |
| `liberar_rechazar` | POST | `/piezasLiberar` | `PzasLiberadasController@liberar_rechazar` |
| `vistaPzasMaquina` | GET | `/piezasMaquina` | `PzasGeneralesController@showVistaMaquina` |
| `showMachinesProcess` | POST | `/piezasMaquina` | `PzasGeneralesController@showMachinesProcess` |

### Módulo: Gestión de Archivos (Dibujos, Ayudas, Manuales, Fundición)
| Nombre | Método | URI |
|---|---|---|
| `dibujos.manage` | GET | `/dibujos/manage` |
| `dibujos.upload` | POST | `/dibujos/upload` |
| `dibujos.delete` | POST | `/dibujos/delete` |
| `dibujos.replace` | POST | `/dibujos/replace` |
| `dibujos.serve` | GET | `/dibujos/serve` |
| `dibujos.archivos` | GET | `/dibujos/archivos` |
| `dibujos.estructura` | GET | `/dibujos/estructura` |
| `dibujos.createFolder` | POST | `/dibujos/createFolder` |
| `dibujos.deleteFolder` | POST | `/dibujos/deleteFolder` |
| `dibujos.deleteParent` | POST | `/dibujos/deleteParent` |
| `dibujos.log` | GET | `/dibujos/log` |
| `ayudas.*` | varios | `/ayudas/*` | *(mismo patrón que dibujos)* |
| `ayudas_fundicion.*` | varios | `/ayudas_fundicion/*` | *(mismo patrón)* |
| `manuales.*` | varios | `/manuales/*` | *(mismo patrón)* |
| `fundicion.*` | varios | `/fundicion/*` | *(mismo patrón, controlador DibujosFundicionPdfController)* |

### Módulo: Procesos y Producción
| Nombre | Método | URI | Controlador |
|---|---|---|---|
| `headerdata` | POST | `/processProduction/selected` | `ProcessProductionController@storeHeaderdata` |
| `storePiece` | POST | `/processProduction/storePiece` | `ProcessProductionController@storePiece` |
| `editPieces` | POST | `/processProduction/editPieces` | `ProcessProductionController@editPieces` |
| `finishReport` | GET | `/processProduction/finishReport/{meta}` | `ProcessProductionController@finishReport` |
| `showReportFormat` | GET | `/processProduction/format/{meta}/{process}/{edit}` | `ProcessProductionController@showReportFormat` |
| `cNominals` | GET | `/cNominals` | `ProcessesController@show_cNominalsView` |
| `storeCNominals` | POST | `/cNominals/store` | `ProcessesController@storeCNominalsData` |
| `showTimes` | GET | `/tiemposProduccion/{clase?}` | `TiemposProduccionController@show` |
| `storeTimes` | POST | `/tiemposProduccion` | `TiemposProduccionController@store` |
| `verProcesos` | GET | `/progresoOT` | `ProgresoProcesosController@show` |
| `machinesOccupied` | GET | `/machinesOccupied` | `MachinesController@show` |
| `freeUp` | POST | `/machinesOccupied/freeUp` | `MachinesController@freeUp` |

### Módulo: Herramientas Tecamac
| Nombre | Método | URI | Controlador |
|---|---|---|---|
| `herramientas.tecamac.index` | GET | `/herramientas/tecamac` | `HerramientasTecamacController@index` |
| `herramientas.tecamac.store` | POST | `/herramientas/tecamac` | `HerramientasTecamacController@store` |
| `herramientas.tecamac.update` | POST | `/herramientas/tecamac/{id}` | `HerramientasTecamacController@update` |
| `herramientas.tecamac.destroy` | DELETE | `/herramientas/tecamac/{id}` | `HerramientasTecamacController@destroy` |
| `herramientas.tecamac.reactivar` | POST | `/herramientas/tecamac/{id}/reactivar` | `HerramientasTecamacController@reactivar` |
| `herramientas.tecamac.updateStock` | PATCH | `/herramientas/tecamac/{id}/stock` | `HerramientasTecamacController@updateStock` |
| `herramientas.tecamac.imagen.replace` | POST | `/herramientas/tecamac/imagen/{imgId}/replace` | `HerramientasTecamacController@replaceImage` |
| `herramientas.tecamac.imagen.rename` | PATCH | `/herramientas/tecamac/imagen/{imgId}/rename` | `HerramientasTecamacController@renameImage` |

### Módulo: Rastreo de Soldadura
| Nombre | Método | URI | Controlador |
|---|---|---|---|
| `trackingSoldadura.index` | GET | `/trackingSoldadura` | `TrackingSoldaduraController@index` |
| `trackingSoldadura.store` | POST | `/trackingSoldadura` | `TrackingSoldaduraController@store` |
| `soldadura.generarQRIndividual` | GET | `/soldadura/generar-qr-individual` | `GenerarQRIndividualController@index` |
| `soldadura.generarQRIndividual.store` | POST | `/soldadura/generar-qr-individual` | `GenerarQRIndividualController@store` |
| `soldadura.generarQRLote` | GET | `/soldadura/generar-qr-lote` | `GenerarQRLoteController@index` |
| `soldadura.generarQRLote.store` | POST | `/soldadura/generar-qr-lote` | `GenerarQRLoteController@store` |
| `soldadura.liberarQRPlanta` | GET | `/soldadura/liberar-qr-planta` | `LiberarQRPlantaController@index` |
| `soldadura.liberarQRPlanta.escanear` | POST | `/soldadura/liberar-qr-planta/escanear` | `LiberarQRPlantaController@escanear` |
| `soldadura.liberarQRPlanta.liberar` | POST | `/soldadura/liberar-qr-planta/liberar` | `LiberarQRPlantaController@liberar` |
| `soldadura.recepcionPlanta` | GET | `/soldadura/recepcion-planta` | `LiberarQRPlantaController@indexRecepcion` |
| `soldadura.recepcionPlanta.escanear` | POST | `/soldadura/recepcion-planta/escanear` | `LiberarQRPlantaController@escanear` |
| `soldadura.recepcionPlanta.confirmar` | POST | `/soldadura/recepcion-planta/confirmar` | `LiberarQRPlantaController@confirmar` |
| `soldadura.regenerarQR` | GET | `/soldadura/regenerar-qr` | `RegenerarQRController@index` |
| `soldadura.regenerarQR.lista` | GET | `/soldadura/regenerar-qr/lista` | `RegenerarQRController@listaLotes` |
| `soldadura.regenerarQR.verificar` | POST | `/soldadura/regenerar-qr/verificar` | `RegenerarQRController@verificarAcceso` |
| `soldadura.regenerarQR.cerrar` | GET | `/soldadura/regenerar-qr/cerrar` | `RegenerarQRController@cerrarSesion` |
| `soldadura.regenerarQRLote.descargar` | GET | `/soldadura/regenerar-qr/lote/{loteId}` | `RegenerarQRController@regenerarLote` |
| `soldadura.regenerarQRIndividuales.descargar` | GET | `/soldadura/regenerar-qr/individuales/{loteId}` | `RegenerarQRController@descargarIndividuales` |

### Módulo: Reportes
| Nombre | Método | URI | Controlador |
|---|---|---|---|
| `reportes.pta` | GET | `/reportes/pta` | `EnvioPtaController@index` |
| `reportes.pta.enviar` | POST | `/reportes/pta/enviar` | `EnvioPtaController@enviar` |
| `reportes.reenvio` | GET | `/reportes/reenvio` | `ReporteProduccionController@showReenvio` |
| `reportes.produccion.reenviar` | POST | `/reportes/reenviar` | `ReporteProduccionController@reenviarCorreo` |
| `reportes.descargar_pdf` | GET | `/reportes/descargar-pdf/{fecha}` | `ReporteProduccionController@descargarPDF` |
| `systemLogsReport` | GET | `/system-logs-report` | `SystemLogController@index` |
| `system.logs.store` | POST | `/system-logs` | `SystemLogController@store` |
| `system.logs.purge` | POST | `/system-logs/purge` | `SystemLogController@purge` |

### Módulo: PTA
| Nombre | Método | URI | Controlador |
|---|---|---|---|
| *(pta routes)* | varios | `/pta/*` | *(middleware: CheckPtaAccess)* |

### Módulo: Almacén WO (Parcialidades, Remisiones, Tratamiento Térmico)
| Nombre | Método | URI |
|---|---|---|
| `wo.parcialidad.store` | POST | `/wo/parcialidad` |
| `wo.parcialidad.update` | PUT | `/wo/parcialidad/{id}` |
| `wo.parcialidad.destroy` | DELETE | `/wo/parcialidad/{id}` |
| `wo.remision.store` | POST | `/wo/remision` |
| `wo.remision.destroy` | DELETE | `/wo/remision/{id}` |
| `wo.remision.serve` | GET | `/wo/remision/{id}/serve` |
| `wo.tratamiento.store` | POST | `/wo/tratamiento` |
| `wo.tratamiento.update` | PUT | `/wo/tratamiento/{id}` |
| `wo.tratamiento.destroy` | DELETE | `/wo/tratamiento/{id}` |
| `wo.tratamiento.download` | GET | `/wo/tratamiento/{id}/download` |

### Módulo: Productividad
| Nombre | Método | URI |
|---|---|---|
| `productivity.ping` | POST | `/productivity/ping` |
| `productivity.unlock` | POST | `/productivity/unlock` |

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

---

## 11. Buenas Prácticas y Bugs Conocidos

- **Nunca codifiques URLs en JS directamente.** Siempre usa `window.routes` inyectado desde Blade.
- **`route:cache`** no debe usarse en desarrollo. Solo en producción (`php artisan route:cache`). Para limpiar: `php artisan route:clear`.
- **Rutas legacy sin nombre** (ej. `generated::XYZ`): No usarlas en Blade ni en JS. Agregar nombre si se necesita referenciar.
- **Rutas de `showMachinesProcess`** (`POST /piezasMaquina`): Esta ruta usa controlador `PzasGeneralesController`, no `MachinesController` — no confundir con `machinesOccupied`.
- **El módulo `fundicion/*`** (`DibujosFundicionPdfController`) es distinto de `almacen/fundicion/*` (`AlmacenFundicionController`). El primero gestiona el filesystem de archivos; el segundo gestiona la vista de historial.
