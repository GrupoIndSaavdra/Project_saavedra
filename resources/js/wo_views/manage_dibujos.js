/**
 * manage_dibujos.js
 * Logica JavaScript para la vista de Gestion de Planos/Dibujos PDF.
 */

// ========================================================================
// Init
// ========================================================================
document.addEventListener('DOMContentLoaded', () => {
    initCreateFolderBtn();
    initUploadBtn();
    loadBadgeCounts();
    loadAuditLog();

    if (window.otNombreActivo && window.claseNombreActivo) {
        cargarArchivosEnPanel(
            String(window.otNombreActivo),
            window.claseNombreActivo
        );
    }
});

// ========================================================================
// 1. Boton Crear Carpeta
// ========================================================================
function initCreateFolderBtn() {
    const btnCrear = document.getElementById('btn-crear-carpeta');
    if (!btnCrear) return;

    btnCrear.addEventListener('click', () => {
        const ot    = btnCrear.dataset.ot;
        const clase = btnCrear.dataset.clase;

        btnCrear.disabled     = true;
        btnCrear.innerHTML    = '<span class="dibujos-spinner"></span> Creando...';

        fetch(window.routes['dibujos.createFolder'], {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': window.csrfToken,
                'Content-Type': 'application/json',
                'Accept'      : 'application/json',
            },
            body: JSON.stringify({ ot, clase }),
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                mostrarNotificacion(data.message || 'Carpeta creada correctamente.');
                setTimeout(() => window.location.reload(), 1000);
            } else {
                mostrarNotificacion(data.message || 'No se pudo crear la carpeta.', true);
                btnCrear.disabled  = false;
                btnCrear.innerHTML = 'Crear Carpeta';
            }
        })
        .catch(() => {
            mostrarNotificacion('Error de conexion. Intente de nuevo.', true);
            btnCrear.disabled  = false;
            btnCrear.innerHTML = 'Crear Carpeta';
        });
    });
}

// ========================================================================
// 2. Boton Subir PDF (tarjeta superior)
// ========================================================================
function initUploadBtn() {
    const btnSubir      = document.getElementById('btn-subir-pdf');
    const fileInput     = document.getElementById('d-upload-file');
    const fileNameLabel = document.getElementById('d-upload-file-name');
    const fileLabelText = document.getElementById('d-upload-file-label-text');

    if (!btnSubir || !fileInput) return;

    const ot    = btnSubir.dataset.ot;
    const clase = btnSubir.dataset.clase;

    fileInput.addEventListener('change', () => {
        if (fileInput.files[0]) {
            fileNameLabel.textContent = fileInput.files[0].name;
            fileLabelText.textContent = 'Archivo seleccionado: ' + fileInput.files[0].name;
            btnSubir.disabled = false;
        } else {
            fileNameLabel.textContent = '';
            fileLabelText.textContent = 'Seleccionar archivo PDF';
            btnSubir.disabled = true;
        }
    });

    btnSubir.addEventListener('click', () => {
        const file = fileInput.files[0];
        if (!file) return;

        subirPdf(ot, clase, file, btnSubir, () => {
            fileInput.value           = '';
            fileNameLabel.textContent = '';
            fileLabelText.textContent = 'Seleccionar archivo PDF';
            btnSubir.disabled         = true;
            cargarArchivosEnPanel(ot, clase);
            actualizarBadge(ot, clase);
        });
    });
}

// ========================================================================
// 4. Cargar archivos en el panel activo
// ========================================================================
function cargarArchivosEnPanel(ot, clase) {
    const grid = document.getElementById('archivos-grid');
    if (!grid) return;

    grid.innerHTML = '<p class="d-text-subtle d-text-center d-w-100">Cargando archivos...</p>';

    const url = `${window.routes['dibujos.archivos']}?ot=${encodeURIComponent(ot)}&clase=${encodeURIComponent(clase)}`;
    fetch(url, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => renderArchivosGrid(data, ot, clase))
        .catch(() => {
            grid.innerHTML = '<p class="d-text-danger d-text-center d-w-100">Error al cargar los archivos.</p>';
        });
}

function renderArchivosGrid(data, ot, clase) {
    const grid = document.getElementById('archivos-grid');

    if (!data.existe || data.archivos.length === 0) {
        grid.innerHTML = `
            <div class="dibujos-empty-state" style="grid-column:1/-1;">
                <p>No hay archivos PDF en esta carpeta. Sube el primer plano usando el panel de arriba.</p>
            </div>`;
        return;
    }

    grid.innerHTML = '';
    data.archivos.forEach((archivo, index) => {
        const card = document.createElement('div');
        card.className = 'dibujos-file-card';
        card.style.animationDelay = `${index * 0.05}s`;
        card.innerHTML = `
            <div class="file-icon-wrapper">
                <img src="${window.baseUrl}/images/pdf-view-shadow.png" class="file-icon icon-default">
                <img src="${window.baseUrl}/images/pdf-view.png" class="file-icon icon-hover">
            </div>
            <div class="file-name">${archivo.nombre}</div>
            <div class="file-actions">
                <button class="btn-dibujos btn-dibujos-sm btn-ver"
                    onclick="abrirPdf('${archivo.url}')">
                    Ver
                </button>
                <button class="btn-dibujos btn-dibujos-sm btn-reemplazar"
                    onclick="prepararReemplazo('${archivo.nombre}', '${ot}', '${clase}', this)">
                    Reemplazar
                </button>
                <button class="btn-dibujos btn-dibujos-sm btn-dibujos-danger btn-eliminar"
                    onclick="eliminarPdf('${archivo.nombre}','${ot}','${clase}')">
                    Eliminar
                </button>
            </div>`;

        card.querySelector('.file-name').onclick = () => abrirPdf(archivo.url);

        grid.appendChild(card);
    });
}

// ========================================================================
// 5. Abrir PDF en nueva pestana
// ========================================================================
window.abrirPdf = function(url) {
    window.open(url, '_blank');
};

// ========================================================================
// 6. Preparar reemplazo de archivo automático
// ========================================================================
window.prepararReemplazo = function(nombreArchivo, ot, clase, btnElement) {
    const hiddenInput = document.createElement('input');
    hiddenInput.type = 'file';
    hiddenInput.accept = '.pdf';
    hiddenInput.style.display = 'none';
    
    hiddenInput.addEventListener('change', () => {
        const file = hiddenInput.files[0];
        if (!file) return;
        
        reemplazarPdf(ot, clase, nombreArchivo, file, btnElement, () => {
            cargarArchivosEnPanel(ot, clase);
            actualizarBadge(ot, clase);
        });
    });
    
    document.body.appendChild(hiddenInput);
    hiddenInput.click();
    
    // Limpiar input despues de usarse
    setTimeout(() => hiddenInput.remove(), 5000);
};

// ========================================================================
// 7. Eliminar PDF
// ========================================================================
window.eliminarPdf = function(nombreArchivo, ot, clase) {
    if (!confirm('¿Deseas eliminar el archivo "' + nombreArchivo + '"?\nEsta accion no se puede deshacer.')) return;

    fetch(window.routes['dibujos.delete'], {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': window.csrfToken,
            'Content-Type': 'application/json',
            'Accept'      : 'application/json',
        },
        body: JSON.stringify({ ot, clase, archivo: nombreArchivo }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            mostrarNotificacion(data.message || 'Archivo eliminado correctamente.');
            cargarArchivosEnPanel(ot, clase);
            actualizarBadge(ot, clase);
            loadAuditLog();
        } else {
            mostrarNotificacion(data.message || 'No se pudo eliminar el archivo.', true);
        }
    })
    .catch(() => mostrarNotificacion('Error de conexion. Intente de nuevo.', true));
};

// ========================================================================
// 8. Navegar a carpeta desde la tabla de estructura
// ========================================================================
window.irACarpeta = function(otId, claseId) {
    const url = new URL(window.location.href);
    url.searchParams.set('ot_id', otId);
    url.searchParams.set('clase_id', claseId);
    window.location.href = url.toString();
};

/** Respaldo para cuando no se encuentra el ID en la BD (vía nombres) */
window.irACarpetaNombre = function(otName, claseName) {
    mostrarNotificacion('Buscando OT ' + otName + '...', false);
    // Intentar buscar en el select de arriba
    const otSelect = document.getElementById('ot-select');
    if (otSelect) {
        // Buscamos una opción que empiece con "OT [id]"
        const option = Array.from(otSelect.options).find(o => o.text.includes('OT ' + otName));
        if (option) {
            window.irACarpeta(option.value, ""); // Redirigir a OT primero
        } else {
            mostrarNotificacion('No se encontró el ID de la OT en el sistema.', true);
        }
    }
};

// ========================================================================
// 9. AJAX: Subir PDF
// ========================================================================
function subirPdf(ot, clase, file, btn, onSuccess) {
    const originalText = btn.innerHTML;
    btn.disabled  = true;
    btn.innerHTML = '<span class="dibujos-spinner"></span> Subiendo...';

    const formData = new FormData();
    formData.append('ot',    ot);
    formData.append('clase', clase);
    formData.append('pdf',   file);

    fetch(window.routes['dibujos.upload'], {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': window.csrfToken,
            'Accept'      : 'application/json',
        },
        body: formData,
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            mostrarNotificacion(data.message || 'Archivo subido correctamente.');
            loadAuditLog();
            if (onSuccess) onSuccess();
        } else {
            mostrarNotificacion(data.message || 'No se pudo subir el archivo.', true);
        }
    })
    .catch(() => mostrarNotificacion('Error de conexion. Intente de nuevo.', true))
    .finally(() => {
        btn.disabled  = false;
        btn.innerHTML = originalText;
    });
}

// ========================================================================
// 10. AJAX: Reemplazar PDF
// ========================================================================
function reemplazarPdf(ot, clase, archivoAnterior, file, btn, onSuccess) {
    const originalText = btn.innerHTML;
    btn.disabled  = true;
    btn.innerHTML = '<span class="dibujos-spinner"></span> Reemplazando...';

    const formData = new FormData();
    formData.append('ot',               ot);
    formData.append('clase',            clase);
    formData.append('archivo_anterior', archivoAnterior);
    formData.append('pdf',              file);

    fetch(window.routes['dibujos.replace'], {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': window.csrfToken,
            'Accept'      : 'application/json',
        },
        body: formData,
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            mostrarNotificacion(data.message || 'Archivo reemplazado correctamente.');
            loadAuditLog();
            if (onSuccess) onSuccess();
        } else {
            mostrarNotificacion(data.message || 'No se pudo reemplazar el archivo.', true);
        }
    })
    .catch(() => mostrarNotificacion('Error de conexion. Intente de nuevo.', true))
    .finally(() => {
        btn.disabled  = false;
        btn.innerHTML = originalText;
    });
}

// ========================================================================
// 11. Badges de conteo
// ========================================================================
function loadBadgeCounts() {
    const rows = document.querySelectorAll('[data-ot][data-clase]');
    rows.forEach(row => {
        actualizarBadge(row.dataset.ot, row.dataset.clase);
    });
}

function actualizarBadge(ot, clase) {
    const badgeId = `badge-${slugify(ot)}-${slugify(clase)}`;
    const badge   = document.getElementById(badgeId);
    if (!badge) return;

    const url = `${window.routes['dibujos.archivos']}?ot=${encodeURIComponent(ot)}&clase=${encodeURIComponent(clase)}`;
    fetch(url, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            const count = data.archivos ? data.archivos.length : 0;
            badge.textContent = count;
            badge.classList.toggle('badge-count-empty', count === 0);
        })
        .catch(() => { badge.textContent = '?'; });
}

// ========================================================================
// 12. Registro de auditoria
// ========================================================================
function loadAuditLog() {
    const tbody = document.getElementById('tbody-log');
    if (!tbody) return;

    fetch(window.baseUrl + '/dibujos/log', {
        headers: { 'Accept': 'application/json' },
    })
    .then(r => {
        if (!r.ok) throw new Error();
        return r.json();
    })
    .then(data => {
        tbody.innerHTML = '';
        if (!data.logs || data.logs.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="d-text-center d-text-subtle" style="padding:1em;">Sin acciones registradas aun.</td></tr>';
            return;
        }
        data.logs.forEach(log => {
            const accionEs = {
                'crear_carpeta' : 'Crear carpeta',
                'subir_pdf'     : 'Subir PDF',
                'eliminar_pdf'  : 'Eliminar PDF',
                'reemplazar_pdf': 'Reemplazar PDF',
            };
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${log.created_at}</td>
                <td>${log.user_name || '—'}</td>
                <td><span class="action-badge ${log.action}">${accionEs[log.action] || log.action}</span></td>
                <td>${log.ruta}</td>
                <td>${log.archivo || '—'}</td>`;
            tbody.appendChild(tr);
        });
    })
    .catch(() => {
        tbody.innerHTML = '<tr><td colspan="5" class="d-text-center d-text-subtle" style="padding:1em;">Registro no disponible.</td></tr>';
    });
}

// ========================================================================
// Utilidades
// ========================================================================
function slugify(text) {
    return text.toString().toLowerCase()
        .replace(/\s+/g, '-')
        .replace(/[^a-z0-9\-_]/g, '-')
        .replace(/-+/g, '-').trim();
}

/** Notificacion tipo toast en la esquina inferior derecha */
function mostrarNotificacion(mensaje, esError = false) {
    const existente = document.querySelector('.dibujos-toast');
    if (existente) existente.remove();

    const toast = document.createElement('div');
    toast.className   = 'dibujos-toast' + (esError ? ' error' : '');
    toast.textContent = mensaje;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.classList.add('fade-out');
        setTimeout(() => toast.remove(), 500);
    }, 3500);
}
