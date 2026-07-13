# ⚡ Guía de JavaScript (JS Skill) - Máximo Nivel

> **📁 Directorio de Referencia:** `public/js/ y resources/js/`
> *Usa los archivos en este directorio como base o inspiración al crear/modificar funcionalidades relacionadas con esta skill.*


Todo script en `Project_saavedra` debe estar diseñado para manejar fallos de red sin romper la UI. JS se utiliza para mejorar la UX y conectar endpoints silenciosos.

## 1. Patrón Asíncrono Definitivo (Async / Await)
Olvida las cadenas `.then().catch()`. Todo debe usar `async / await` rodeado de un bloque `try / catch`.

```javascript
async function procesarPieza(idPieza) {
    // 1. Prepara UI (Mostrar Loading, deshabilitar botón)
    const btn = document.getElementById('btn-procesar');
    btn.disabled = true;
    btn.innerHTML = 'Procesando...';

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
                'Accept': 'application/json' // Obliga a Laravel a regresar JSON si hay error 422
            },
            body: JSON.stringify({ estado: 'ok' })
        });

        // 5. Verificar si el servidor dio error HTTP (4xx o 5xx)
        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || 'Error desconocido en servidor');
        }

        const data = await response.json();
        
        // 6. Éxito
        mostrarAlerta('Éxito', data.success, 'success');
        
    } catch (error) {
        // 7. Falla (Red caída, error 500, etc)
        console.error(error);
        mostrarAlerta('Error', error.message, 'error');
    } finally {
        // 8. Restaurar UI sin importar si fue éxito o error
        btn.disabled = false;
        btn.innerHTML = 'Procesar';
    }
}
```

## 2. Alertas Elegantes (Uso de SweetAlert2)
Si se requiere confirmación para borrar o procesar, usa la librería `Swal`.

```javascript
function confirmarEliminacion(id) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "Esta acción no se puede deshacer.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#b30404', // Rojo Peligro Saavedra
        cancelButtonColor: '#030041',  // Azul Marino Saavedra
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            ejecutarEliminacion(id); // Llamar a tu función async fetch
        }
    });
}
```

## 3. Subida de Archivos o Formularios (FormData)
Si necesitas enviar imágenes o formularios completos por Fetch, no uses `JSON.stringify`. Usa `FormData`.

```javascript
async function subirDocumento(formularioHTML) {
    const formData = new FormData(formularioHTML);
    const token = document.querySelector('meta[name="csrf-token"]').content;

    try {
        const response = await fetch(window.routes.apiSubir, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token
            },
            body: formData
        });
        
        const res = await response.json();
        // Lógica posterior...
    } catch (e) {
        // Error handling...
    }
}
```

## 4. Manipulación del DOM y Template Literals
Para inyectar HTML, usa siempre literales para que el código sea limpio y legible.

```javascript
function agregarFila(datos) {
    const tbody = document.getElementById('tabla-datos').querySelector('tbody');
    
    const fila = `
        <tr id="fila-${datos.id}" class="fade-in">
            <td>${datos.nombre}</td>
            <td>
                <span class="badge ${datos.estado === 'Activo' ? 'bg-green' : 'bg-red'}">
                    ${datos.estado}
                </span>
            </td>
        </tr>
    `;
    
    tbody.insertAdjacentHTML('beforeend', fila);
}
```

## 5. Aislamiento de Variables y Event Listener del DOM
Para evitar la colisión de variables globales y errores al cargar el script antes del HTML, envuelve todo tu código JavaScript en el evento `DOMContentLoaded`.

```javascript
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', () => {
        // Inicialización de componentes, listeners, etc.
        const selectLote = document.getElementById('lote_id');
        if (selectLote) {
            selectLote.addEventListener('change', (e) => {
                actualizarDetallesLote(e.target.value);
            });
        }
    });

    // Funciones internas del módulo (no expuestas globalmente)
    async function actualizarDetallesLote(id) {
        // ... Lógica fetch ...
    }
})();
```

## 6. Manejo de Estados de Carga Visual (Visual Loading State)
Nunca dejes al usuario esperando sin retroalimentación visual al enviar datos.
- Deshabilita los botones.
- Agrega una clase CSS de spinner o un overlay de loading.

```javascript
function setVisualLoading(elemento, isLoading) {
    if (isLoading) {
        elemento.disabled = true;
        elemento.dataset.originalText = elemento.innerHTML;
        elemento.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Cargando...`;
    } else {
        elemento.disabled = false;
        elemento.innerHTML = elemento.dataset.originalText || 'Enviar';
    }
}
```

## 7. Validación Temprana en el Frontend
Valida la entrada del usuario antes de realizar una petición fetch para evitar consumos de red innecesarios.

```javascript
function validarEntradaPieza(formulario) {
    const nPieza = formulario.querySelector('#n_pieza').value.trim();
    
    if (nPieza === '') {
        mostrarAlerta('Advertencia', 'El número de pieza es obligatorio', 'warning');
        return false;
    }
    
    if (nPieza.length < 3) {
        mostrarAlerta('Advertencia', 'El número de pieza debe tener al menos 3 caracteres', 'warning');
        return false;
    }
    
    return true;
}
```

## 8. Delegación de Eventos para Elementos Dinámicos
Cuando tengas tablas o listas cuyos elementos se agregan de forma dinámica mediante JavaScript, no agregues event listeners individuales. Utiliza delegación de eventos en el contenedor padre.

```javascript
document.getElementById('tabla-piezas').addEventListener('click', (e) => {
    // Buscar si el clic se hizo en el botón de eliminar pieza
    const deleteBtn = e.target.closest('.btn-eliminar-pieza');
    if (deleteBtn) {
        const idPieza = deleteBtn.dataset.id;
        confirmarEliminacion(idPieza);
    }
});
```

---

## 9. Funciones JS Globales del Proyecto (Referencia Real)
Los archivos `almacen_fundicion.js` y `calidad_fundicion.js` son los más grandes (~328KB). Estas funciones ya existen y deben usarse en lugar de reimplementar la funcionalidad:

| Función | Archivo | Descripción |
|---|---|---|
| `almacenVerPdf(ot, archivo, tipo)` | `almacen_fundicion.js` | Abre un PDF de almacén en el visor embebido |
| `confirmDeletePdf(btn, ot, archivo, origin)` | `almacen_fundicion.js` | Confirma y ejecuta eliminación de PDF con Swal |
| `calidadVerPdf(ot, archivo, tipo)` | `calidad_fundicion.js` | Abre un PDF de calidad en el visor embebido |
| `mostrarAlerta(titulo, texto, icono)` | Global (ambos) | Muestra alerta SweetAlert2 estándar |
| `openReproceso(otName)` | `almacen_fundicion.js` | Abre el modal de generación de reproceso |
| `generarPreOrden(otName)` | `almacen_fundicion.js` | Inicia el flujo de pre-orden de fundición |

### Patrón de Rutas Inyectadas por Blade (`window.routes`)
Las URLs de las peticiones AJAX se inyectan desde Blade para evitar hardcodear rutas en JS:
```blade
<script>
    window.routes = {
        ...(window.routes || {}),
        almacenServeFile:  @json(route('almacen.fundicion.serve', ['ot' => ':ot', 'archivo' => ':archivo', 'tipo' => ':tipo'])),
        almacenDeleteFile: @json(route('almacen.fundicion.delete.pdf')),
        calidadServeFile:  @json(route('calidad.fundicion.serve', ['ot' => ':ot', 'archivo' => ':archivo', 'tipo' => ':tipo'])),
    };
</script>
```
Luego en JS: `window.routes.almacenServeFile.replace(':ot', ot).replace(':archivo', archivo).replace(':tipo', tipo)`

## 10. Actualización Dinámica de Tablas en Tiempo Real (Evitar Carga de Página)

Al implementar operaciones CRUD vía fetch (creación de carpetas, subida o eliminación de archivos) que deban actualizar el contenido de una tabla estructural sin recargar la página completa, utiliza el siguiente patrón de diseño:

1. **Función de Re-renderizado Local (`renderEstructuraTable`)**: Crea una función que limpie el `<tbody>` de la tabla en el DOM y reconstruya las filas dinámicamente iterando sobre la estructura actualizada en memoria (`window.estructura`).
2. **Preservación de Estado Visual (Caché Local de Conteos)**: Para evitar cuellos de botella y cascadas de peticiones HTTP innecesarias al redibujar una tabla que contiene contadores o badges de archivos, lee y guarda temporalmente los conteos actuales del DOM antes de limpiar el contenedor. Reutiliza estos conteos al pintar las nuevas filas:
   ```javascript
   function renderEstructuraTable() {
       const tbody = document.querySelector('#tabla-estructura tbody');
       if (!tbody) return;

       // 1. Guardar conteos existentes
       const existingCounts = {};
       tbody.querySelectorAll('.badge-count').forEach(span => {
           existingCounts[span.id] = span.textContent;
       });

       tbody.innerHTML = ''; // 2. Limpiar

       // 3. Pintar de nuevo con los conteos cacheados
       window.estructura.forEach(item => {
           const badgeId = "badge-" + slugify(item);
           const savedCount = existingCounts[badgeId] !== undefined ? existingCounts[badgeId] : '0';
           
           const tr = document.createElement('tr');
           tr.innerHTML = `
               <td>${item}</td>
               <td><span class="badge-count" id="${badgeId}">${savedCount}</span></td>
           `;
           tbody.appendChild(tr);
       });
   }
   ```
3. **Sincronización en Caliente del Estado Local**: Si la creación o subida de archivos vincula entidades de forma automática en la base de datos (por ejemplo, relacionando una ayuda visual con su orden de trabajo), asegúrate de que el controlador devuelva la `ot` y la `clase` correspondientes en el JSON de respuesta. El frontend debe interceptar esta respuesta e inyectarla en caliente en los objetos de memoria (`window.historiales` / `window.estructura`) antes de llamar al re-renderizado para mantener la consistencia sin forzar un F5.
4. **Peligro en Reemplazo con Expresiones Regulares (Syntax Error in build/Vite)**: Si utilizas scripts (ej. Python `re.sub`) para inyectar cadenas de texto o alertar en los archivos JS del proyecto, evita generar escapes innecesarios como `\'` dentro de literales de cadena ordinarios. Un escape de comillas simple literal (`\'`) dentro de un JS no-procesado causará fallos en los compiladores de esbuild/Vite lanzando errores de sintaxis críticos e impidiendo la carga de dependencias.

