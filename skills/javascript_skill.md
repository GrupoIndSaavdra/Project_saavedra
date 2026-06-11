# ⚡ Guía de JavaScript (JS Skill) - Máximo Nivel

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
