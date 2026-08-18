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

### Patrón OBLIGATORIO: Modales y Overlays de Pantalla Completa

> ⚠️ **REGLA CRÍTICA DE RENDIMIENTO (Evitar Lag/Trabamiento)**: Nunca uses `backdrop-filter` o `-webkit-backdrop-filter` con valores altos de desenfoque (`blur(15px)`) en overlays de pantalla completa. Esto genera un cuello de botella de renderizado en GPU y hace que la interfaz se trabe o se sienta pesada.
>
> En su lugar, usa el **filtro azulito corporativo** sin blur (`background: rgba(0, 10, 25, 0.85)`).

### Clases globales recomendadas (listas para usar)

| Clase | Rol |
|---|---|
| `.gis-lock-overlay` | Contenedor fijo que cubre toda la pantalla con fondo azul corporativo translúcido (`rgba(0, 10, 25, 0.85)`) sin blur. |
| `.alm-modal-content` | Caja blanca contenedora del modal con borde celeste, sombra suave y esquinas redondeadas. |

### Estética Premium del Proyecto (Estilo Pre-Orden Casting)

Para que cualquier modal u overlay se integre perfectamente con la identidad del software, debe apegarse a estas especificaciones de diseño:

1. **Contenedor Principal (`.alm-modal-content` o equivalente)**:
   - Borde: `4px solid #0284c7` (Borde celeste premium).
   - Sombras: `box-shadow: 0 25px 60px rgba(2, 132, 199, 0.25)` (Sombra brillante celeste).
   - Esquinas: `border-radius: 20px` (Esquinas muy suaves y redondeadas).
2. **Cabecera (`.alm-modal-header`)**:
   - Fondo: `background: linear-gradient(135deg, #0369a1 0%, #0284c7 100%)` (Degradado azul brillante).
   - Texto del título: Fuente `Poppins, sans-serif`, peso `700`, sombra `text-shadow: 0 2px 4px rgba(0,0,0,0.15)`.
3. **Cuerpo del Modal (`.alm-modal-body`)**:
   - Fondo: `#f8fafc` (Gris claro premium).
   - Tipografía: `Poppins, sans-serif`.
4. **Botón Principal (`.btn-lock-understood` o `.btn-save-preorden`)**:
   - Fondo: `linear-gradient(135deg, #0369a1 0%, #0284c7 100%)`.
   - Borde: Redondeado de tipo píldora (`border-radius: 50px`).
   - Sombras: `box-shadow: 0 8px 24px rgba(3, 105, 161, 0.35)`.
   - Transiciones: Suaves al hacer hover y active:
     ```html
     onmouseover="this.style.filter='brightness(1.1)'; this.style.transform='translateY(-2px)';" 
     onmouseout="this.style.filter='none'; this.style.transform='none';"
     ```

### Estructura HTML de referencia (Totalmente Autocontenida)

Si creas el modal de manera dinámica mediante JavaScript, inyecta este marcado con los estilos integrados para asegurar que se renderice perfecto en cualquier sección del sistema:

```javascript
const overlay = document.createElement('div');
overlay.className = 'gis-lock-overlay';
overlay.id = 'mi-overlay';

overlay.innerHTML = `
    <div class="alm-modal-content" style="background: #ffffff; width: 95vw; max-width: 600px; border-radius: 20px; border: 4px solid #0284c7; box-shadow: 0 25px 60px rgba(2, 132, 199, 0.25); display: flex; flex-direction: column; overflow: hidden; position: relative;">
        <div class="alm-modal-header" style="background: linear-gradient(135deg, #0369a1 0%, #0284c7 100%); padding: 2em 2.5em 1.5em; position: relative; text-align: center;">
            <h3 style="margin: 0; color: #fff; font-size: 1.8em; font-weight: 700; letter-spacing: 0.5px; text-shadow: 0 2px 4px rgba(0,0,0,0.15); font-family: 'Poppins', sans-serif;">
                Título del Modal
            </h3>
        </div>
        <div class="alm-modal-body" style="background: #f8fafc; padding: 2.5em; font-family: 'Poppins', sans-serif; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 20px;">
            <div class="lock-icon-container" style="margin: 0;">
                <img src="${baseUrl}images/Aviso.png" class="lock-icon" alt="Aviso" style="width: 100px; height: 100px; filter: drop-shadow(0 4px 6px rgba(3, 57, 102, 0.2));">
            </div>
            <p class="lock-message" style="color: #475569; font-size: 1.25em; line-height: 1.6; margin: 0; font-weight: 500; padding: 0 1rem; text-align: center;">
                Descripción clara de lo que está pasando en la aplicación.
            </p>
            <div style="margin-top: 15px; width: 100%;">
                <button class="btn-lock-understood" style="background: linear-gradient(135deg, #0369a1 0%, #0284c7 100%); border-radius: 50px; font-weight: 800; font-size: 1.1em; text-transform: uppercase; letter-spacing: 1.5px; padding: 16px 45px; border: none; color: #fff; cursor: pointer; transition: all 0.2s ease; box-shadow: 0 8px 24px rgba(3, 105, 161, 0.35); width: 100%; max-width: 320px;" onmouseover="this.style.filter='brightness(1.1)'; this.style.transform='translateY(-2px)';" onmouseout="this.style.filter='none'; this.style.transform='none';">
                    Aceptar y Continuar
                </button>
            </div>
        </div>
    </div>
`;
document.body.appendChild(overlay);
```

### Mostrar/ocultar desde JavaScript
```javascript
// Mostrar
document.getElementById('mi-overlay').style.display = 'flex';

// Ocultar
document.getElementById('mi-overlay').style.display = 'none';
```
