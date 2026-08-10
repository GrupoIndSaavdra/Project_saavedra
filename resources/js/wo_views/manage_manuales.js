const module = 'manuales';
/**
 * manage_documentation.js
 * Logica JavaScript unificada para la vista de Gestion de Documentacion (Dibujos, Manuales, Ayudas).
 */

document.addEventListener('DOMContentLoaded', () => {
    initCreateFolderBtn();
    initUploadBtn();
    loadBadgeCounts();
    loadAuditLog();
    initTableFilters();

    // Sincronizar UI inicial si hay parámetros cargados (mediante selectores)
    updateDependentSelectors();
    updateAdminUI();
});

window.changeDocSelector = function(paramName, value, toClear = []) {
    const url = new URL(window.location.href);
    if (value) url.searchParams.set(paramName, value);
    else url.searchParams.delete(paramName);
    toClear.forEach(p => url.searchParams.delete(p));
    window.location.href = url.toString();
};

window.irACarpeta = function(p1, p2, isId = false) {
    const url = new URL(window.location.href);

    url.searchParams.set('proceso_id', p1);
    
    window.location.href = url.toString();
};

function updateDependentSelectors() {
    const urlParams = new URLSearchParams(window.location.search);
    const prSel = document.getElementById('proceso-select');

    function forceOption(sel, paramName) {
        if (!sel) return;
        const val = urlParams.get(paramName);
        if (!val) return;
        if (sel.tagName === 'INPUT') {
            sel.value = val;
            return;
        }
        let found = Array.from(sel.options).some(o => String(o.value) === String(val) || o.text.trim() === String(val).trim());
        if (!found) {
            const opt = document.createElement('option');
            opt.value = val;
            opt.text = val;
            sel.appendChild(opt);
        }
        let matchingOpt = Array.from(sel.options).find(o => String(o.value) === String(val) || o.text.trim() === String(val).trim());
        if (matchingOpt) sel.value = matchingOpt.value;
    }

    forceOption(prSel, 'proceso_id');
}

function updateAdminUI() {
    let p1 = null, label = '';
    let ready = false;

    const prSel = document.getElementById('proceso-select');

    if (prSel && prSel.value) {
        if (prSel.selectedIndex !== -1) {
            ready = true;
            p1 = prSel.options[prSel.selectedIndex].text.trim();
            label = p1;
        }
    }

    const panelFiles = document.getElementById('panel-archivos');
    
    // Contenedores de Alertas y Botones
    const alertReadyExists = document.getElementById('alert-ready-exists');
    const alertReadyNotExists = document.getElementById('alert-ready-not-exists');
    const alertNotReady = document.getElementById('alert-not-ready');
    const btnCrear = document.getElementById('btn-crear-carpeta');
    
    const uploadReadyContent = document.getElementById('upload-ready-content');
    const uploadNotReadyContent = document.getElementById('upload-not-ready-content');
    const alertUploadNoFolder = document.getElementById('alert-upload-no-folder');
    const btnSubir = document.getElementById('btn-subir-pdf');

    if (ready) {
        let label = `<span class="lvl-1">${p1}</span>`;

        // Carpeta destino labels
        document.querySelectorAll('.folder-label').forEach(el => el.innerHTML = label);

        // Files panel
        if (panelFiles) {
            panelFiles.classList.add('active');
            const h2Span = panelFiles.querySelector('h2 span');
            if (h2Span) h2Span.innerHTML = label;
            const bcrumb = panelFiles.querySelector('.dibujos-files-breadcrumb strong');
            if (bcrumb) bcrumb.innerHTML = label;
        }

        // Función case-insensitive para verificar existencia en el servidor Linux
        const sanitizeJS = (str) => {
            if (!str) return '';
            str = String(str).replace(/\.\.+/g, '');
            str = str.replace(/[\\\/]/g, '');
            return str.trim();
        };
        const eq = (a, b) => a && b && sanitizeJS(String(a)).toLowerCase() === sanitizeJS(String(b)).toLowerCase();
        let existe = false;
        if (window.estructura) {
            if (Array.isArray(window.estructura)) existe = window.estructura.some(val => eq(val, p1));
            else existe = !!Object.keys(window.estructura).find(k => eq(k, p1));
        }

        // Visibilidad Alertas Izquierda
        if (alertNotReady) alertNotReady.hidden = true;
        if (alertReadyExists) alertReadyExists.classList.toggle("hidden", !existe);
        if (alertReadyNotExists) alertReadyNotExists.classList.toggle("hidden", existe);
        if (btnCrear) {
            btnCrear.classList.toggle("hidden", existe);
            btnCrear.dataset.proceso = p1; btnCrear.dataset.folderParam1 = p1;
        }

        // Visibilidad Panel Derecha (Subir)
        if (uploadNotReadyContent) uploadNotReadyContent.hidden = true;
        if (uploadReadyContent) uploadReadyContent.hidden = false;
        if (alertUploadNoFolder) alertUploadNoFolder.classList.toggle("hidden", existe);
        
        const fileFormGroup = uploadReadyContent ? uploadReadyContent.querySelector('.dibujos-form-group') : null;
        if (fileFormGroup) fileFormGroup.classList.toggle("hidden", !existe);
        
        const fileInput = document.getElementById('d-upload-file');
        if (fileInput) fileInput.disabled = !existe;

        if (btnSubir) {
            btnSubir.disabled = !existe;
            btnSubir.classList.toggle("hidden", !existe);
            btnSubir.dataset.proceso = p1; btnSubir.dataset.folderParam1 = p1;
        }

        cargarArchivosEnPanel(p1);
    } else {
        if (panelFiles) panelFiles.classList.remove('active');
        if (alertNotReady) alertNotReady.hidden = false;
        if (alertReadyExists) alertReadyExists.hidden = true;
        if (alertReadyNotExists) alertReadyNotExists.hidden = true;
        if (btnCrear) btnCrear.hidden = true;

        if (uploadNotReadyContent) uploadNotReadyContent.hidden = false;
        if (uploadReadyContent) uploadReadyContent.hidden = true;
    }
}

/**
 * Inicializa el botón de creación de carpeta con delegación de eventos
 */
/**
 * Inicializa el botón de creación de carpeta con delegación de eventos
 */
function initCreateFolderBtn() {
    document.addEventListener('click', (e) => {
        const btnCrear = e.target.closest('#btn-crear-carpeta');
        if (!btnCrear || btnCrear.disabled) return;

        const payload = getPayloadFromBtn(btnCrear);
        if (!payload.param1) {
            mostrarNotificacion('Selección incompleta para crear carpeta.', true);
            return;
        }

        btnCrear.disabled = true;
        const originalHTML = btnCrear.innerHTML;
        btnCrear.innerHTML = '<span class="dibujos-spinner"></span> Creando...';

        fetch(window.routes['doc.createFolder'], {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': window.csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify(payload),
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                mostrarNotificacion(data.message || 'Carpeta creada correctamente.');
                setTimeout(() => window.location.reload(), 1000);
            } else {
                mostrarNotificacion(data.message || 'No se pudo crear la carpeta.', true);
            }
        })
        .catch(() => mostrarNotificacion('Error de conexión al crear carpeta.', true))
        .finally(() => {
            btnCrear.disabled = false;
            btnCrear.innerHTML = originalHTML;
        });
    });
}

/**
 * Inicializa los eventos de subida con delegación
 */
function initUploadBtn() {
    // Evento Change para el input file (es dinámico)
    document.addEventListener('change', (e) => {
        const fileInput = e.target.closest('#d-upload-file');
        if (!fileInput) return;
        
        const fileNameLabel = document.getElementById('d-upload-file-name');
        const fileLabelText = document.getElementById('d-upload-file-label-text');
        const btnSubir = document.getElementById('btn-subir-pdf');

        const files = fileInput.files;
        if (files.length > 0) {
            const count = files.length;
            const text = count === 1 ? files[0].name : `${count} archivos seleccionados`;
            if (fileNameLabel) fileNameLabel.textContent = text;
            if (fileLabelText) fileLabelText.textContent = 'Seleccionado: ' + text;
            if (btnSubir) btnSubir.disabled = false;
        } else {
            if (fileNameLabel) fileNameLabel.textContent = '';
            if (fileLabelText) fileLabelText.textContent = 'Seleccionar archivo PDF';
            if (btnSubir) btnSubir.disabled = true;
        }
    });

    // Evento Click para el botón de subir
    document.addEventListener('click', async (e) => {
        const btnSubir = e.target.closest('#btn-subir-pdf');
        if (!btnSubir || btnSubir.disabled) return;

        const fileInput = document.getElementById('d-upload-file');
        const files = fileInput ? fileInput.files : [];
        if (files.length === 0) {
            mostrarNotificacion('Por favor selecciona al menos un archivo.', true);
            return;
        }

        const payload = getPayloadFromBtn(btnSubir);
        const originalText = btnSubir.innerHTML;
        btnSubir.disabled = true;

        let successCount = 0;
        let errorCount = 0;

        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            btnSubir.innerHTML = `<span class="dibujos-spinner"></span> (${i+1}/${files.length}) Subiendo...`;
            
            try {
                const data = await subirArchivoIndividual(payload, file);
                if (data.success) successCount++;
                else {
                    errorCount++;
                    mostrarNotificacion(`Error en "${file.name}": ${data.message}`, true);
                }
            } catch (err) {
                errorCount++;
                mostrarNotificacion(`Error de conexión en "${file.name}"`, true);
            }
        }

        // Reset UI
        btnSubir.disabled = true;
        btnSubir.innerHTML = originalText;
        if (fileInput) fileInput.value = '';
        const fileNameLabel = document.getElementById('d-upload-file-name');
        const fileLabelText = document.getElementById('d-upload-file-label-text');
        if (fileNameLabel) fileNameLabel.textContent = '';
        if (fileLabelText) fileLabelText.textContent = 'Seleccionar archivo PDF';

        if (successCount > 0) {
            mostrarNotificacion(successCount === 1 ? 'Archivo subido correctamente.' : `${successCount} archivos subidos correctamente.`);
            
            // Recargar vista de archivos y badges
            const p1 = (module === 'manuales') ? payload.proceso : payload.param1;
            const p2 = (module === 'manuales') ? null : payload.param2;

            cargarArchivosEnPanel(p1, p2);
            actualizarBadge(p1, p2);
            loadAuditLog();
        }
    });
}

function getPayloadFromBtn(btn) {
    return { 
        proceso: btn.dataset.proceso, 
        param1: btn.dataset.folderParam1 || btn.dataset.proceso 
    };
}

function cargarArchivosEnPanel(param1, param2 = null, payloadObj = null) {
    const grid = document.getElementById('archivos-grid');
    if (!grid) return;

    grid.innerHTML = '<p class="d-text-subtle d-text-center d-w-100">Cargando archivos...</p>';

    let url = window.routes['doc.archivos'] + '?';
    const c1 = param1 ? encodeURIComponent(param1) : '';

    url += `proceso=${c1}`;

    fetch(url, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => renderArchivosGrid(data, param1, param2))
        .catch(() => {
            grid.innerHTML = '<p class="d-text-danger d-text-center d-w-100">Error al cargar los archivos.</p>';
        });
}

function renderArchivosGrid(data, param1, param2) {
    const grid = document.getElementById('archivos-grid');

    if (!data.existe || data.archivos.length === 0) {
        grid.innerHTML = `
            <div class="dibujos-empty-state" style="grid-column:1/-1; text-align:center;">
                <img src="${window.baseUrl}/images/sin_archivos.png" alt="Sin archivos" style="width: 120px; opacity: 0.6; margin-bottom: 1em;">
                <p>No hay archivos PDF. Sube el primero usando el panel de arriba.</p>
            </div>`;
        return;
    }

    grid.innerHTML = '';
    data.archivos.forEach((archivo, index) => {
        const card = document.createElement('div');
        card.className = 'dibujos-file-card';
        card.style.animationDelay = `${index * 0.05}s`;

        let deleteParams = `'${archivo.nombre}', '${param1}'`;
        if (param2) deleteParams += `, '${param2}'`;

        card.innerHTML = `
            <div class="file-icon-wrapper" onclick="abrirPdf('${archivo.url}')" style="cursor: pointer;" title="Abrir PDF">
                <img src="${window.baseUrl}/images/pdf-view-shadow.png" class="file-icon icon-default">
                <img src="${window.baseUrl}/images/pdf-view.png" class="file-icon icon-hover">
            </div>
            <div class="file-name" style="cursor: pointer;" title="Abrir PDF">${escapeHTML(archivo.nombre)}</div>
            <div class="file-actions">
                <button class="btn-dibujos btn-dibujos-sm btn-ver" onclick="abrirPdf('${archivo.url}')">Ver</button>
                <button class="btn-dibujos btn-dibujos-sm btn-reemplazar" onclick="prepararReemplazo(${deleteParams}, this)">Reemplazar</button>
                <button class="btn-dibujos btn-dibujos-sm btn-dibujos-danger btn-eliminar" onclick="eliminarPdf(${deleteParams})">Eliminar</button>
            </div>`;

        grid.appendChild(card);
    });
}

window.abrirPdf = function(url) {
    window.open(url, '_blank');
};

window.prepararReemplazo = function(nombreArchivo, param1, param2, btnElement) {
    if (typeof btnElement === 'undefined') {
        btnElement = param2;
        param2 = null;
    }

    const hiddenInput = document.createElement('input');
    hiddenInput.type = 'file';
    hiddenInput.accept = '.pdf';
    hiddenInput.hidden = true;
    
    hiddenInput.addEventListener('change', () => {
        const file = hiddenInput.files[0];
        if (!file) return;
        
        let payload = { archivo_anterior: nombreArchivo };
        payload.proceso = param1;
        
        reemplazarPdf(payload, file, btnElement, () => {
            cargarArchivosEnPanel(param1, param2);
            actualizarBadge(param1, param2);
        });
    });
    
    document.body.appendChild(hiddenInput);
    hiddenInput.click();
    setTimeout(() => hiddenInput.remove(), 5000);
};

window.eliminarPdf = function(nombreArchivo, param1, param2) {
    if (!confirm(`¿Deseas eliminar el archivo "${nombreArchivo}"?\nEsta acción no se puede deshacer.`)) return;

    let payload = { archivo: nombreArchivo };
    payload.proceso = param1;

    fetch(window.routes['doc.delete'], {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': window.csrfToken,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify(payload),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            mostrarNotificacion(data.message || 'Archivo eliminado correctamente.');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            mostrarNotificacion(data.message || 'No se pudo eliminar el archivo.', true);
        }
    })
    .catch(() => mostrarNotificacion('Error de conexion. Intente de nuevo.', true));
};


function subirArchivoIndividual(payload, file) {
    const formData = new FormData();
    Object.keys(payload).forEach(k => {
        if(k !== 'param1' && k !== 'param2') formData.append(k, payload[k]);
    });
    formData.append('pdf', file);

    return fetch(window.routes['doc.upload'], {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': window.csrfToken,
            'Accept': 'application/json',
        },
        body: formData,
    }).then(r => r.json());
}

function subirPdf(payload, file, btn, onSuccess) {
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="dibujos-spinner"></span> Subiendo...';

    subirArchivoIndividual(payload, file)
    .then(data => {
        if (data.success) {
            mostrarNotificacion(data.message || 'Archivo subido correctamente.');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            mostrarNotificacion(data.message || 'No se pudo subir el archivo.', true);
        }
    })
    .catch(() => mostrarNotificacion('Error de conexion.', true))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}

function reemplazarPdf(payload, file, btn, onSuccess) {
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="dibujos-spinner"></span> Reemplazando...';

    const formData = new FormData();
    Object.keys(payload).forEach(k => formData.append(k, payload[k]));
    formData.append('pdf', file);

    fetch(window.routes['doc.replace'], {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': window.csrfToken,
            'Accept': 'application/json',
        },
        body: formData,
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            mostrarNotificacion(data.message || 'Archivo reemplazado correctamente.');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            mostrarNotificacion(data.message || 'No se pudo reemplazar.', true);
        }
    })
    .catch(() => mostrarNotificacion('Error de conexion.', true))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}

function loadBadgeCounts() {
    let rows;
    rows = document.querySelectorAll('[data-proceso]');
    
    if(!rows) return;

    const observer = new IntersectionObserver((entries, obs) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const row = entry.target;
                actualizarBadge(row.dataset.proceso);
                obs.unobserve(row);
            }
        });
    }, { rootMargin: '50px' });

    rows.forEach(row => observer.observe(row));
}

function actualizarTotalBadge(ot, badgeElement) {
    if (!badgeElement || !window.routes['doc.total_archivos']) return;

    const url = window.routes['doc.total_archivos'] + '?ot=' + encodeURIComponent(ot);
    
    fetch(url, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            const count = data.total || 0;
            badgeElement.textContent = count;
            badgeElement.classList.toggle('badge-count-empty', count === 0);
        })
        .catch(err => console.error('Error cargando total:', err));
}

function getBadgeElement(param1, param2 = null) {
    let rowSelector = '';
    const safeParam1 = param1 ? param1.replace(/"/g, '\\"') : '';

    rowSelector = `tr[data-proceso="${safeParam1}"]`;

    if (rowSelector) {
        const row = document.querySelector(rowSelector);
        if (row) {
            const badge = row.querySelector('.badge-count');
            if (badge) return badge;
        }
    }

    // Fallback original con IDs
    let badgeId = `badge-${slugify(param1)}`;
    
    return document.getElementById(badgeId);
}

function actualizarBadge(param1, param2 = null) {
    const badge = getBadgeElement(param1, param2);
    if (!badge) return;

    let url = window.routes['doc.archivos'] + '?';
    url += `proceso=${encodeURIComponent(param1)}`;

    fetch(url, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            let count = 0;
            if (data.existe && data.archivos) {
                count = data.archivos.length;
            }
            badge.textContent = count;
            badge.classList.toggle('badge-count-empty', count === 0);

            // Actualizar texto del botón de eliminar dinámicamente
            const row = badge.closest('tr');
            if (row) {
                const btnEliminar = row.querySelector('.btn-eliminar-carpeta');
                if (btnEliminar) {
                    const btnSpan = btnEliminar.querySelector('span');
                    const btnImg = btnEliminar.querySelector('img');
                    // Identificar si es Directorio Raíz
                    const isRoot = true; // en manuales siempre es raíz
                    
                    if (count > 0) {
                        if (btnSpan) btnSpan.textContent = 'Vaciar Carpeta';
                        if (btnImg) btnImg.src = window.baseUrl + '/images/Eliminar-Archivos.png';
                    } else if (isRoot) {
                        if (btnSpan) btnSpan.textContent = 'Eliminar Directorio Raíz';
                        if (btnImg) btnImg.src = window.baseUrl + '/images/Eliminar-Carpeta.png';
                    }
                }
            }
        })
        .catch(() => { badge.textContent = '?'; });
}

function loadAuditLog() {
    const tbody = document.getElementById('tbody-log');
    if (!tbody) return;

    fetch(window.routes['doc.log'], {
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
                'crear_carpeta': 'Se ha creado una nueva carpeta en el sistema',
                'subir_pdf': 'Se subió un nuevo archivo PDF al servidor',
                'eliminar_pdf': 'Se eliminó definitivamente un archivo PDF',
                'reemplazar_pdf': 'Se reemplazó un archivo existente por una nueva versión',
                'enviar_alerta': 'Se envió un correo de alerta a Almacén/Calidad',
                'eliminar_carpeta': 'Se eliminó la carpeta permanentemente',
                'vaciar_carpeta': 'Se eliminaron todos los archivos de la carpeta',
                'guardar_ayudas': 'Se vincularon ayudas visuales a la Orden de Trabajo',
                'desvincular_ayudas': 'Se desvincularon todas las ayudas de la Orden de Trabajo'
            };
            
            let actionLabel = accionEs[log.action] || log.action;

            // Refinar descripción para carpetas según profundidad (Raíz vs Subcarpeta)
            if (log.action === 'eliminar_carpeta') {
                if (log.ruta && log.ruta.includes('/')) {
                    actionLabel = 'Se eliminó la subcarpeta permanentemente';
                } else {
                    actionLabel = 'Se eliminó el directorio raíz permanentemente';
                }
            } else if (log.action === 'vaciar_carpeta') {
                actionLabel = 'Se eliminaron todos los archivos de la carpeta';
            } else if (log.action === 'crear_carpeta') {
                if (log.ruta && log.ruta.includes('/')) {
                    actionLabel = 'Se creó una subcarpeta para organizar archivos';
                } else {
                    actionLabel = 'Se creó el directorio raíz';
                }
            }

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${escapeHTML(log.created_at)}</td>
                <td>${escapeHTML(log.user_name || '—')}</td>
                <td><span class="action-badge ${log.action}">${escapeHTML(actionLabel)}</span></td>
                <td>${escapeHTML(log.ruta)}</td>
                <td>${escapeHTML(log.archivo || '—')}</td>`;
            tbody.appendChild(tr);
        });
    })
    .catch(() => {
        tbody.innerHTML = '<tr><td colspan="5" class="d-text-center d-text-subtle" style="padding:1em;">Registro no disponible.</td></tr>';
    });
}

function slugify(text) {
    if (!text) return '';
    return text.toString().toLowerCase()
        .normalize('NFD').replace(/[\u0300-\u036f]/g, "") // Quitar acentos
        .replace(/[^a-z0-9\s-]/g, "") // Eliminar caracteres no alfanuméricos excepto espacios y guiones
        .replace(/[\s-]+/g, '-') // Reemplazar espacios y guiones consecutivos por un solo guion
        .replace(/^-+|-+$/g, ''); // Quitar guiones de los extremos
}

function escapeHTML(str) {
    if (!str) return "";
    return str.toString()
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

/**
 * Normaliza el nombre de la OT para que coincida con la lógica del servidor (PHP)
 */
function normalizeOTName(name) {
    if (!name) return '';
    // Reemplazar guiones especiales y espacios de no ruptura (\xA0)
    let clean = name.replace(/[—–\xA0]/g, '-');
    // Mayúsculas
    clean = clean.toUpperCase();
    // Eliminar espacios múltiples
    clean = clean.replace(/\s+/g, ' ');
    return clean.trim();
}

function mostrarNotificacion(mensaje, esError = false) {
    const existente = document.querySelector('.dibujos-toast');
    if (existente) existente.remove();

    const toast = document.createElement('div');
    toast.className = 'dibujos-toast' + (esError ? ' error' : '');
    
    const icono = esError ? '❌ ' : '✅ ';
    toast.innerHTML = `<span style="margin-right:8px;">${icono}</span> ${escapeHTML(mensaje)}`;
    
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.classList.add('fade-out');
        setTimeout(() => toast.remove(), 500);
    }, 4000);
}

/* ── MÓDULO DE ELIMINACIÓN DE CARPETAS ── */

let folderToDelete = null;

window.confirmarEliminarCarpeta = function(p1, p2, label) {
    folderToDelete = { p1, p2 };
    const modal = document.getElementById('dibujos-confirm-modal');
    const msgContainer = document.getElementById('confirm-message-container');
    const btnConfirm = document.getElementById('btn-confirmar-borrar');
    const modalIcon = document.getElementById('confirm-modal-icon');

    if (modal && msgContainer) {
                
        const badge = getBadgeElement(p1, p2);
        
        let count = 0;
        if (badge) {
            count = parseInt(badge.textContent) || 0;
        }
        
        let isVaciar = (count > 0);
        let actionWord = isVaciar ? 'vaciar todos los archivos' : 'eliminar completamente';
        
        let finalHtml = `Se va a ${actionWord} del Directorio Raíz:<br>
                            <strong style="color: #033966; font-size: 1.2em;">${p1}</strong>`;

        msgContainer.innerHTML = finalHtml;
        
        if (modalIcon) {
            modalIcon.src = window.baseUrl + (isVaciar ? '/images/Eliminar-Archivos.png' : '/images/Eliminar-Carpeta.png');
        }
        
        btnConfirm.textContent = isVaciar ? 'Vaciar Carpeta' : 'Eliminar Permanentemente';
        
        modal.hidden = false;
        
        btnConfirm.onclick = () => {
            eliminarCarpetaAJAX(folderToDelete);
            cerrarConfirmarEliminar();
        };
    }
};

window.cerrarConfirmarEliminar = function() {
    const modal = document.getElementById('dibujos-confirm-modal');
    if (modal) modal.hidden = true;
    folderToDelete = null;
};

function eliminarCarpetaAJAX(folder) {
        let payload = {};
    let route = window.routes['doc.deleteFolder'];

    payload = { proceso: folder.p1 };

    fetch(route, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': window.csrfToken,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify(payload),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            mostrarNotificacion(data.message || 'Carpeta eliminada.');
            setTimeout(() => window.location.reload(), 1000);

        } else {
            mostrarNotificacion(data.message || 'Error al eliminar carpeta.', true);
        }
    })
    .catch(() => mostrarNotificacion('Error de conexión.', true));
}




function renderEstructuraTable() {
    const tbody = document.querySelector('#tabla-estructura tbody');
    if (!tbody) return;
    
    // Guardar conteos existentes para no perderlos y evitar peticiones extras
    const existingCounts = {};
    tbody.querySelectorAll('.badge-count').forEach(span => {
        existingCounts[span.id] = span.textContent;
    });

    tbody.innerHTML = '';

    if (Array.isArray(window.estructura) && window.estructura.length > 0) {
        const prSel = document.getElementById('proceso-select');
        
        window.estructura.forEach(procesoName => {
            let procesoIdBD = null;
            if (prSel) {
                const opt = Array.from(prSel.options).find(o => o.text === procesoName);
                if (opt) procesoIdBD = opt.value;
            }
            const badgeId = "badge-" + slugify(procesoName);
            const savedCount = existingCounts[badgeId] !== undefined ? existingCounts[badgeId] : '0';
            const countClass = savedCount === '0' ? 'badge-count badge-count-empty' : 'badge-count';
            
            const tr = document.createElement('tr');
            tr.setAttribute('data-proceso', procesoName);
            tr.innerHTML = `
                <td class="d-text-center d-text-primary"><strong>${procesoName}</strong></td>
                <td class="d-text-center"><span class="${countClass}" id="${badgeId}">${savedCount}</span></td>
                <td class="d-text-center">
                    <div class="td-actions">
                        <button class="btn-action-icon btn-ver-archivos" title="Ver archivos"
                            onclick="irACarpeta('${procesoIdBD || procesoName}', null, ${procesoIdBD ? 'true' : 'false'})">
                            <img src="${window.baseUrl}/images/documento.png" alt="Ver">
                            <span>Ver PDF's</span>
                        </button>
                        <button class="btn-action-icon btn-eliminar-carpeta" title="Eliminar carpeta"
                            onclick="confirmarEliminarCarpeta('${procesoName}', null, '${procesoName}')">
                            <img src="${window.baseUrl}/images/Eliminar-Carpeta.png" alt="Eliminar">
                            <span>Eliminar Directorio Raíz</span>
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });
    } else {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td colspan="3" class="d-text-center d-text-subtle">
                No hay carpetas de procesos registradas en el servidor.
            </td>
        `;
        tbody.appendChild(tr);
    }
    poblarYFiltrarSelect('filtro-tabla-estructura', '#tabla-estructura', '— Mostrar Todos —');
}

function poblarYFiltrarSelect(selectId, tableOrTbodySelector, defaultLabel) {
    const select = document.getElementById(selectId);
    if (!select) return;

    const target = document.querySelector(tableOrTbodySelector);
    if (!target) return;

    const rows = target.tagName === 'TBODY' ? target.querySelectorAll('tr') : target.querySelectorAll('tbody tr');
    const selectedVal = select.value;

    const opcionesSet = new Set();
    rows.forEach(row => {
        if (row.children.length === 1 && row.querySelector('td[colspan]')) return;
        const firstTd = row.querySelector('td');
        const mainCellText = firstTd ? firstTd.textContent.trim() : row.textContent.trim();
        if (mainCellText) opcionesSet.add(mainCellText);
    });

    const arrayOpciones = Array.from(opcionesSet).sort((a, b) => a.localeCompare(b));
    select.innerHTML = `<option value="">${defaultLabel}</option>`;
    arrayOpciones.forEach(optText => {
        const opt = document.createElement('option');
        opt.value = optText;
        opt.textContent = optText;
        if (optText === selectedVal) opt.selected = true;
        select.appendChild(opt);
    });

    const aplicarFiltrado = () => {
        const val = select.value.toLowerCase().trim();
        rows.forEach(row => {
            if (row.children.length === 1 && row.querySelector('td[colspan]')) return;
            if (!val) {
                row.hidden = false;
            } else {
                const text = row.textContent.toLowerCase();
                row.classList.toggle("hidden", !text.includes(val));
            }
        });
    };

    aplicarFiltrado();

    if (!select.dataset.listenerAdded) {
        select.addEventListener('change', () => aplicarFiltrado());
        select.dataset.listenerAdded = 'true';
    }
}

function initTableFilters() {
    poblarYFiltrarSelect('filtro-tabla-estructura', '#tabla-estructura', '— Mostrar Todos —');
}