# Real-time Table Updates & Dynamic UI Rendering (Avoid location.reload)

## The Problem
When performing CRUD operations (like creating folders, uploading PDFs, or deleting files), it is common practice to simply do `setTimeout(() => window.location.reload(), 1500);` after a successful API response to refresh the view. However, this causes a full page reload, leading to a slow and clunky user experience.

## The Solution
Instead of reloading the page, we use a **Local State Management Pattern** using `window` objects (like `window.estructura`, `window.historiales`, `window.todasLasOTs`, etc.) that act as our "Source of Truth".

### 1. Update the Backend (Controller)
Ensure your controller returns the necessary identifiers in the JSON response, so the frontend knows exactly what was created, deleted, or modified.

**Example for creating a folder:**
```php
if ($request->expectsJson()) {
 return response()->json([
 'success' => true,
 'message' => 'Carpeta creada correctamente.',
 'ot' => $otName,
 'clase' => $claseName
 ]);
}
```

**Example for linking alerts/records:**
```php
if ($request->expectsJson()) {
 return response()->json([
 'success' => true,
 'message' => 'Ayudas vinculadas correctamente.',
 'ayudasLinked' => $ayudasFinales,
 'ot' => $ot
 ]);
}
```

### 2. Update the Frontend (JavaScript)
In the `.then(data => { ... })` block of your `fetch` request, parse the identifiers sent by the controller and update the global `window.*` objects accordingly. Then, call the rendering functions to surgically update the DOM.

**Example for Folder Creation:**
```javascript
if (data.success) {
 mostrarNotificacion(data.message);

 // 1. Mutate local state
 if (data.ot && data.clase) {
 if (!window.estructura[data.ot]) window.estructura[data.ot] = [];
 if (!window.estructura[data.ot].includes(data.clase)) {
 window.estructura[data.ot].push(data.clase);
 }
 }

 // 2. Call surgical render functions (DO NOT RELOAD PAGE)
 if (typeof renderEstructuraTable === 'function') renderEstructuraTable();
 updateAdminUI();
 actualizarBadge(payload.param1, payload.param2);
 if (typeof loadAuditLog === 'function') loadAuditLog();
}
```

**Example for Deleting:**
```javascript
if (data.success) {
 mostrarNotificacion(data.message);

 // 1. Mutate local state
 if (window.estructura[folder.p1]) {
 window.estructura[folder.p1] = window.estructura[folder.p1].filter(c => c !== folder.p2);
 if (window.estructura[folder.p1].length === 0) delete window.estructura[folder.p1];
 }

 // 2. Refresh UI Components
 updateAdminUI();
 loadBadgeCounts();
 loadAuditLog();
 if (typeof renderEstructuraTable === 'function') renderEstructuraTable();
}
```

### Key Principles
1. **Never use `location.reload()`** if you can easily update the DOM dynamically.
2. **Never refetch the entire structure** from the server (`fetch(routes['doc.estructura'])`) unless absolutely necessary, as it can be very slow if there are many directories.
3. Always make sure the backend returns the specific identifiers (`ot`, `clase`, `proceso`, `ayudasLinked`) needed to update the `window.*` cache.
4. **Abstract rendering logic** into reusable functions like `renderEstructuraTable()` and `renderAlertasTable()` so that you can call them from anywhere in the script once the state is updated.

---

## Patrón OBLIGATORIO: Modales y Overlays de Pantalla Completa

> ⚠️ **REGLA**: Siempre que necesites crear un modal con fondo oscuro, pantalla de carga, alerta bloqueante o cualquier overlay de pantalla completa, **DEBES usar las clases globales del proyecto** definidas en `resources/css/layouts/partials/messages.css`. **NUNCA crear CSS nuevo** para el overlay/modal desde cero.

### Clases globales recomendadas (listas para usar)

| Clase | Rol |
|---|---|
| `.gis-lock-overlay` | Contenedor fijo que cubre toda la pantalla con fondo oscuro + blur |
| `.gis-premium-modal` | Caja blanca centrada con borde de color, sombra y bordes redondeados |
| `.gis-premium-modal.success` | Variante verde (éxito) |
| `.gis-premium-modal.warning` | Variante naranja (advertencia) |
| `.gis-premium-modal.error` | Variante roja (error crítico) |
| `.gis-premium-modal.notice` | Variante amarilla (aviso) |
| `.lock-icon-container` | Contenedor para el ícono/imagen central |
| `.lock-icon` | Imagen de 100×100px con drop-shadow |
| `.lock-title` | Título del modal (1.8em, bold 900) |
| `.lock-message` | Mensaje descriptivo del modal |
| `.btn-lock-understood` | Botón de acción del modal |

> 📌 **Nota sobre compatibilidad**: Las clases `.productivity-lock-overlay` y `.productivity-premium-modal` siguen existiendo como alias en el archivo de estilos global para compatibilidad con la advertencia de inactividad de `processProduction`. Para cualquier otro caso de uso general, prefiere utilizar `.gis-lock-overlay` y `.gis-premium-modal`.

### Estructura HTML de referencia

```html
<div class="gis-lock-overlay" id="mi-overlay" style="display:none;">
    <div class="gis-premium-modal warning">

        <!-- Opción A: imagen estática -->
        <div class="lock-icon-container">
            <img src="{{ asset('images/Sospechosa.png') }}" class="lock-icon" alt="Aviso">
        </div>

        <!-- Opción B: spinner animado (para pantallas de carga) -->
        <div class="lock-icon-container">
            <div class="mi-spinner-css"></div>
        </div>

        <h2 class="lock-title">Título del Modal</h2>
        <p class="lock-message">Descripción clara de lo que está pasando.</p>

        <!-- Barra de progreso (opcional, para procesos largos) -->
        <div style="padding: 0 2rem 0.6rem;">
            <div class="purge-progress-bar-track">
                <div id="mi-progress-bar" class="purge-progress-bar-fill"></div>
            </div>
            <p id="mi-status-text" class="purge-progress-status">Procesando...</p>
        </div>

        <div style="padding-bottom: 3em;">
            <button class="btn-lock-understood" onclick="cerrarModal()">Aceptar</button>
        </div>
    </div>
</div>
```

### Personalizar el color del borde (sin crear clase nueva)
Si ninguna variante predefinida aplica, usar inline style para el color de borde:
```html
<div class="gis-premium-modal" style="border-color: #033966;">
```

### Mostrar/ocultar desde JavaScript
```javascript
// Mostrar
document.getElementById('mi-overlay').style.display = 'flex';

// Ocultar
document.getElementById('mi-overlay').style.display = 'none';
```

### Lo que SÍ puedes agregar en el CSS del módulo (componentes únicos)
Solo los elementos que no existen en el sistema global:
- Spinner animado (`@keyframes` + `.mi-spinner`)
- Barra de progreso (`.purge-progress-bar-track`, `.purge-progress-bar-fill`, `.purge-progress-status` — ya definidos en `systemLogs.css` y reusables)
