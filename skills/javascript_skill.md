# Guía de JavaScript (JS Skill) — Project Saavedra

> ** Directorio de Referencia:** `resources/js/`
> *Todo script en `Project_saavedra` debe estar diseñado para manejar fallos de red sin romper la UI.*

---

## 1. Patrón Asíncrono Definitivo (Async / Await)

Olvida las cadenas `.then().catch()`. Todo debe usar `async / await` rodeado de un bloque `try / catch`.

```javascript
async function procesarPieza(idPieza) {
 // 1. Prepara UI (Mostrar Loading, deshabilitar botón)
 const btn = document.getElementById('btn-procesar');
 setVisualLoading(btn, true);

 try {
 // 2. Extraer CSRF Token (OBLIGATORIO)
 const token = document.querySelector('meta[name="csrf-token"]').content;

 // 3. Reemplazar ID en la URL inyectada por window.routes
 const url = window.routes.apiProcesarPieza.replace(':id', idPieza);

 // 4. Hacer petición
 const response = await fetch(url, {
 method: 'POST',
 headers: {
 'Content-Type': 'application/json',
 'X-CSRF-TOKEN': token,
 'Accept': 'application/json' // Obliga a Laravel a regresar JSON en error 422
 },
 body: JSON.stringify({ estado: 'ok' })
 });

 // 5. Verificar si el servidor dio error HTTP (4xx o 5xx)
 if (!response.ok) {
 const errorData = await response.json().catch(() => ({}));
 throw new Error(errorData.message || `Error ${response.status} del servidor`);
 }

 const data = await response.json();

 // 6. Éxito
 mostrarAlerta('Éxito', data.message || data.success, 'success');

 } catch (error) {
 // 7. Falla (Red caída, error 500, etc.)
 console.error('[procesarPieza]', error);
 mostrarAlerta('Error', error.message, 'error');
 } finally {
 // 8. Restaurar UI sin importar si fue éxito o error
 setVisualLoading(btn, false);
 }
}
```

---

## 2. Alertas Elegantes (Uso de SweetAlert2)

```javascript
// Alerta estándar del proyecto
function mostrarAlerta(titulo, texto, icono) {
 Swal.fire({
 title: titulo,
 text: texto,
 icon: icono, // 'success', 'error', 'warning', 'info'
 timer: icono === 'success' ? 2500 : undefined, // Auto-cerrar en éxito
 timerProgressBar: icono === 'success',
 confirmButtonColor: icono === 'error' ? '#9c0303' : '#0a8504',
 });
}

// Confirmación de acción destructiva
function confirmarEliminacion(id, callback) {
 Swal.fire({
 title: '¿Estás seguro?',
 text: "Esta acción no se puede deshacer.",
 icon: 'warning',
 showCancelButton: true,
 confirmButtonColor: '#b30404', // Rojo Peligro Saavedra
 cancelButtonColor: '#030041', // Azul Marino Saavedra
 confirmButtonText: 'Sí, eliminar',
 cancelButtonText: 'Cancelar'
 }).then((result) => {
 if (result.isConfirmed) {
 callback(id);
 }
 });
}
```

---

## 3. Subida de Archivos o Formularios (FormData)

```javascript
async function subirDocumento(formularioHTML) {
 const formData = new FormData(formularioHTML);
 const token = document.querySelector('meta[name="csrf-token"]').content;

 // NO pongas 'Content-Type' al usar FormData — el browser lo pone con boundary automático
 try {
 const response = await fetch(window.routes.apiSubir, {
 method: 'POST',
 headers: { 'X-CSRF-TOKEN': token },
 body: formData
 });

 if (!response.ok) throw new Error(`HTTP ${response.status}`);

 const data = await response.json();
 mostrarAlerta('Éxito', data.message, 'success');
 return data;

 } catch (e) {
 mostrarAlerta('Error', e.message, 'error');
 return null;
 }
}
```

---

## 4. Manipulación del DOM y Template Literals

```javascript
function agregarFila(datos) {
 const tbody = document.querySelector('#tabla-datos tbody');
 if (!tbody) return;

 const fila = `
 <tr id="fila-${datos.id}" class="fade-in">
 <td>${datos.nombre}</td>
 <td>
 <span class="badge ${datos.estado === 'Activo' ? 'bg-green' : 'bg-red'}">
 ${datos.estado}
 </span>
 </td>
 <td>
 <button class="btn-ver" onclick="verDetalle('${datos.id}')">Ver</button>
 <button class="btn-eliminar" data-id="${datos.id}"></button>
 </td>
 </tr>
 `;

 tbody.insertAdjacentHTML('beforeend', fila);
}

// Eliminar fila dinámicamente
function eliminarFilaDOM(id) {
 const fila = document.getElementById(`fila-${id}`);
 if (fila) {
 fila.style.opacity = '0';
 fila.style.transition = 'opacity 0.3s ease';
 setTimeout(() => fila.remove(), 300);
 }
}
```

---

## 5. Aislamiento de Variables y Event Listener del DOM

Envuelve TODO el código JavaScript de un módulo en un IIFE con `DOMContentLoaded`:

```javascript
(function () {
 'use strict';

 document.addEventListener('DOMContentLoaded', () => {
 // Inicialización de componentes, listeners, etc.
 inicializarModulo();
 });

 function inicializarModulo() {
 const selectLote = document.getElementById('lote_id');
 if (selectLote) {
 selectLote.addEventListener('change', (e) => {
 actualizarDetallesLote(e.target.value);
 });
 }
 }

 // Funciones internas del módulo (no expuestas globalmente)
 async function actualizarDetallesLote(id) {
 // ... Lógica fetch ...
 }

})();
```

---

## 6. Manejo de Estados de Carga Visual (Visual Loading State)

```javascript
/**
 * Activa o desactiva el estado de carga en un botón.
 * @param {HTMLElement} elemento - El botón a modificar
 * @param {boolean} isLoading - true para activar carga, false para restaurar
 */
function setVisualLoading(elemento, isLoading) {
 if (isLoading) {
 elemento.disabled = true;
 elemento.dataset.originalText = elemento.innerHTML;
 elemento.innerHTML = `<span class="spinner"></span> Procesando...`;
 } else {
 elemento.disabled = false;
 elemento.innerHTML = elemento.dataset.originalText || 'Enviar';
 }
}

// Overlay de carga para toda la pantalla
function setPageLoading(show) {
 let overlay = document.getElementById('page-loading-overlay');
 if (show) {
 if (!overlay) {
 overlay = document.createElement('div');
 overlay.id = 'page-loading-overlay';
 overlay.style.cssText = `
 position: fixed; inset: 0; background: rgba(0,0,0,0.4);
 display: flex; align-items: center; justify-content: center;
 z-index: 9999;
 `;
 overlay.innerHTML = `<div class="spinner" style="width:50px;height:50px;border-width:5px;"></div>`;
 document.body.appendChild(overlay);
 }
 } else {
 overlay?.remove();
 }
}
```

---

## 7. Validación Temprana en el Frontend

```javascript
/**
 * Valida campos de un formulario antes de enviar al servidor.
 * @returns {boolean} true si el formulario es válido
 */
function validarFormulario(formulario) {
 const campos = formulario.querySelectorAll('[required]');
 let valido = true;

 campos.forEach(campo => {
 campo.classList.remove('campo-invalido');
 if (!campo.value.trim()) {
 campo.classList.add('campo-invalido');
 valido = false;
 }
 });

 if (!valido) {
 mostrarAlerta('Advertencia', 'Por favor completa todos los campos requeridos.', 'warning');
 }

 return valido;
}
```

---

## 8. Delegación de Eventos para Elementos Dinámicos

Cuando tablas o listas tengan elementos dinámicos, no agregues event listeners individuales:

```javascript
// Delegación sobre el contenedor padre
document.getElementById('tabla-piezas').addEventListener('click', (e) => {
 // Eliminar pieza
 const deleteBtn = e.target.closest('.btn-eliminar-pieza');
 if (deleteBtn) {
 const idPieza = deleteBtn.dataset.id;
 confirmarEliminacion(idPieza, ejecutarEliminacion);
 return;
 }

 // Ver detalle de pieza
 const verBtn = e.target.closest('.btn-ver-pieza');
 if (verBtn) {
 abrirModalDetalle(verBtn.dataset.id);
 return;
 }
});
```

---

## 9. Funciones JS Globales del Proyecto (Referencia Real)

| Función | Archivo | Descripción |
|---|---|---|
| `almacenVerPdf(ot, archivo, tipo)` | `almacen_fundicion.js` | Abre un PDF de almacén en el visor embebido |
| `confirmDeletePdf(btn, ot, archivo, origin)` | `almacen_fundicion.js` | Confirma y ejecuta eliminación de PDF con Swal |
| `calidadVerPdf(ot, archivo, tipo)` | `calidad_fundicion.js` | Abre un PDF de calidad en el visor embebido |
| `mostrarAlerta(titulo, texto, icono)` | Global (ambos) | Muestra alerta SweetAlert2 estándar |
| `openReproceso(otName)` | `almacen_fundicion.js` | Abre el modal de generación de reproceso |
| `generarPreOrden(otName)` | `almacen_fundicion.js` | Inicia el flujo de pre-orden de fundición |
| `setVisualLoading(elemento, isLoading)` | Global | Activa/desactiva estado de carga en botón |

### Patrón de Rutas Inyectadas por Blade (`window.routes`)

```blade
<script>
 window.routes = {
 ...(window.routes || {}),
 almacenServeFile: @json(route('almacen.fundicion.serve', ['ot' => ':ot', 'archivo' => ':archivo', 'tipo' => ':tipo'])),
 almacenDeleteFile: @json(route('almacen.fundicion.delete.pdf')),
 calidadServeFile: @json(route('calidad.fundicion.serve', ['ot' => ':ot', 'archivo' => ':archivo', 'tipo' => ':tipo'])),
 };
</script>
```

En JS: `window.routes.almacenServeFile.replace(':ot', ot).replace(':archivo', archivo).replace(':tipo', tipo)`

---

## 10. Actualización Dinámica de Tablas (Evitar Carga de Página)

```javascript
function renderEstructuraTable() {
 const tbody = document.querySelector('#tabla-estructura tbody');
 if (!tbody) return;

 // 1. Guardar conteos existentes antes de limpiar
 const existingCounts = {};
 tbody.querySelectorAll('.badge-count').forEach(span => {
 existingCounts[span.id] = span.textContent;
 });

 tbody.innerHTML = ''; // 2. Limpiar

 // 3. Re-pintar con conteos cacheados
 window.estructura.forEach(item => {
 const badgeId = 'badge-' + slugify(item);
 const savedCount = existingCounts[badgeId] !== undefined ? existingCounts[badgeId] : '0';

 const tr = document.createElement('tr');
 tr.innerHTML = `
 <td>${item}</td>
 <td><span class="badge-count" id="${badgeId}">${savedCount}</span></td>
 `;
 tbody.appendChild(tr);
 });
}

// Helper: convertir texto a slug para IDs
function slugify(text) {
 return text.toLowerCase().replace(/[^a-z0-9]/g, '-');
}
```

> **PELIGRO en ediciones con Python/scripts:** Si usas `re.sub` para inyectar cadenas en archivos JS, evita generar escapes innecesarios como `\'` dentro de literales ordinarios. Esto causa `SyntaxError` en esbuild/Vite.

---

## 11. Manejo de Errores de Red vs Errores de Servidor

```javascript
// Distinguir entre error de red (sin respuesta) y error HTTP (respuesta con código de error)
async function fetchSeguro(url, options = {}) {
 try {
 const response = await fetch(url, options);

 // El servidor respondió pero con error
 if (!response.ok) {
 let mensaje = `Error ${response.status}`;
 try {
 const err = await response.json();
 mensaje = err.message || err.error || mensaje;
 } catch (_) { /* El cuerpo no es JSON */ }
 throw new Error(mensaje);
 }

 return await response.json();

 } catch (error) {
 if (error.name === 'TypeError') {
 // Error de red (sin conexión, timeout, CORS)
 throw new Error('Error de red: verifica tu conexión o contacta al administrador.');
 }
 throw error; // Re-lanzar errores HTTP
 }
}
```

---

## 12. Inicialización Segura de Variables de Window

Cuando leas datos de `window.*`, siempre verifica que existan antes de usarlos:

```javascript
// Acceso defensivo
const estructura = window.estructura ?? {};
const todasOTs = Array.isArray(window.todasLasOTs) ? window.todasLasOTs : [];
const config = window.usuarioConfig ?? { perfil: 0 };

// Actualizar estado de window sin sobreescribir
window.estructura = window.estructura || {};
window.estructura[ot] = window.estructura[ot] || [];
if (!window.estructura[ot].includes(clase)) {
 window.estructura[ot].push(clase);
}
```

---

## 13. Validación Defensiva de Elementos del DOM en Event Listeners

Cuando captures eventos globales (como un `submit` en un formulario específico que puede no existir o botones que pueden no estar renderizados en el DOM en ese momento), **SIEMPRE valida la existencia de los elementos antes de acceder a sus propiedades** (como `innerText` o `disabled`). Esto evita que un TypeError rompa el script en páginas donde ese componente no se carga.

```javascript
document
    .getElementById("formPreOrden")
    ?.addEventListener("submit", function (e) {
        e.preventDefault();

        // VALIDACIÓN OBLIGATORIA
        const btn = document.getElementById("btn-submit-preorden");
        if (!btn) return; // <-- Evita TypeError: Cannot read properties of null (reading 'innerText')

        const originalText = btn.innerText;
        btn.disabled = true;
        btn.innerText = "Procesando...";
        
        // ... Lógica de subida ...
    });
```

---

## 14. Persistencia de Filtros con `sessionStorage`

Cuando el usuario navega a una vista de detalle (por ejemplo, ver una pieza o historial de OT) y luego regresa con un botón "Regresar" o la navegación del navegador, los filtros que tenía aplicados en la tabla de origen se pierden, arruinando la experiencia de usuario.

Para solucionar esto, usa el **Patrón de Persistencia con sessionStorage**:
1. Antes de navegar fuera de la página, guarda los filtros actuales en `sessionStorage` en formato JSON.
2. Al cargar la página, comprueba si existe una bandera que indique que venimos regresando de la subvista. Si es así, carga y aplica los filtros.
3. Si el usuario entra por primera vez de forma directa, limpia la sesión para evitar filtros antiguos inesperados.

```javascript
// ✅ PATRÓN CORRECTO: Persistencia de Filtros
const comingBack = sessionStorage.getItem('comingFromPieceView') === 'true';
sessionStorage.removeItem('comingFromPieceView'); // Consumir bandera inmediatamente

if (comingBack) {
    const savedFilters = sessionStorage.getItem('adminPiecesFilters');
    if (savedFilters) {
        try {
            const parsed = JSON.parse(savedFilters);
            if (window.selectedItems) {
                Object.keys(parsed).forEach(key => {
                    window.selectedItems[key] = parsed[key];
                });
            }
        } catch (e) {
            console.error("[restoreFilters] Error parsing saved filters:", e);
        }
    }
} else {
    // Si no es un regreso, limpiar filtros previos para iniciar de cero
    sessionStorage.removeItem('adminPiecesFilters');
}

// Escuchar clics en los enlaces de detalles/ver para guardar el estado antes de irse
document.addEventListener("click", (e) => {
    const btn = e.target.closest(".btn-ver-detalle");
    if (btn) {
        sessionStorage.setItem("comingFromPieceView", "true");
        const filters = obtenerFiltrosActivos(); // Retorna objeto con filtros
        sessionStorage.setItem("adminPiecesFilters", JSON.stringify(filters));
    }
});

// Al presionar el botón "Limpiar Filtros", también limpiar la persistencia
document.getElementById("btnClearFilters")?.addEventListener("click", () => {
    sessionStorage.removeItem('adminPiecesFilters');
});
```

---

## 15. Activación Condicional de Controles/Botones Según Filtros Activos

Si tienes un botón global cuya funcionalidad solo aplica a ciertos datos filtrados (por ejemplo, "Exportar Reporte de Soldadura" que solo tiene sentido si estamos visualizando procesos de soldadura), debes ocultar o deshabilitar dinámicamente el botón dependiendo del filtro seleccionado.

La función debe ser **idempotente** y ejecutarse cada vez que la tabla se re-filtre:

```javascript
// ✅ PATRÓN CORRECTO: Mostrar/ocultar condicional de botones globales
function checkProcessAndShowButton(procesoFiltrado) {
    const btnSoldadura = document.getElementById('btn-soldadura-report');
    if (!btnSoldadura) return;

    const procesosPermitidos = ['Soldadura', 'SoldaduraPTA'];

    // Usar clases CSS de visibilidad (del styles_skill.md)
    if (procesosPermitidos.includes(procesoFiltrado)) {
        btnSoldadura.classList.remove('hidden');
    } else {
        btnSoldadura.classList.add('hidden');
    }
}

// En tu función principal de filtrado de filas:
function applyAllFilters() {
    const proceso = document.getElementById("select-proceso").value;
    
    // Ejecutar verificación del botón
    checkProcessAndShowButton(proceso);

    // ... lógica para mostrar/ocultar filas en la tabla ...
}
```

---

## 16. Normalización de Rutas y Filtros de Archivos en Windows/Linux

Cuando proceses nombres o rutas de archivos obtenidos dinámicamente en JavaScript (especialmente en entornos de desarrollo basados en Windows donde las barras inclinadas invertidas `\` se usan comúnmente en la base de datos o en el sistema de archivos), siempre normaliza las rutas reemplazando los backslashes por barras inclinadas hacia adelante `/` antes de cualquier validación:

```javascript
// ✅ PATRÓN CORRECTO: Normalización de backslashes
const pathNorm = (f.nombre || "").toLowerCase().replace(/\\/g, "/");
const parts = pathNorm.split("/");
const isUserUploadedScar = parts[parts.length - 2] === "scar";
```
```

