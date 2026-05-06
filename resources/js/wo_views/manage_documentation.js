/**
 * manage_documentation.js
 * Logica JavaScript unificada para la vista de Gestion de Documentacion (Dibujos, Manuales, Ayudas).
 */

document.addEventListener('DOMContentLoaded', () => {
    initCreateFolderBtn();
    initUploadBtn();
    loadBadgeCounts();
    loadAuditLog();

    // Sincronizar UI inicial si hay parámetros cargados (mediante selectores)
    updateDependentSelectors();
    updateAdminUI();
    initAyudasFundicionForm();
});

window.changeDocSelector = function(paramName, value, toClear = []) {
    const url = new URL(window.location.href);
    if (value) url.searchParams.set(paramName, value);
    else url.searchParams.delete(paramName);
    toClear.forEach(p => url.searchParams.delete(p));
    window.location.href = url.toString();
};

window.irACarpeta = function(p1, p2, isId = false) {
    const module = window.moduleType;
    const url = new URL(window.location.href);

    if (module === 'dibujos') {
        url.searchParams.set('ot_id', p1);
        if (p2) url.searchParams.set('clase_id', p2);
    } else if (module === 'fundicion') {
        url.searchParams.set('ot_id', p1);
    } else if (module === 'manuales') {
        url.searchParams.set('proceso_id', p1);
    } else if (module === 'ayudas' || module === 'ayudas_fundicion') {
        if (p2) url.searchParams.set('clase_id', p2);
        url.searchParams.set('proceso_id', p1);
    }
    
    window.location.href = url.toString();
};

function updateDependentSelectors() {
    const module = window.moduleType;
    const clSel = document.getElementById('clase-select');
    const prSel = document.getElementById('proceso-select');
    const otSel = document.getElementById('ot-select');

    if (module === 'ayudas' || module === 'ayudas_fundicion') {
        if (prSel) {
            prSel.disabled = !clSel.value;
        }
    } else if (module === 'dibujos') {
        if (clSel) clSel.disabled = !otSel.value;
    }
}

function updateAdminUI() {
    const module = window.moduleType;
    let p1 = null, p2 = null, label = '';
    let ready = false;

    const otSel = document.getElementById('ot-select');
    const clSel = document.getElementById('clase-select');
    const prSel = document.getElementById('proceso-select');

    if (module === 'dibujos' && otSel && clSel && otSel.value && clSel.value) {
        if (otSel.selectedIndex !== -1 && clSel.selectedIndex !== -1) {
            ready = true;
            const otText = otSel.options[otSel.selectedIndex].text.trim();
            p1 = otText.replace(' — ', ' - '); 
            p2 = clSel.options[clSel.selectedIndex].text.trim();
            label = `${p1} / ${p2}`;
        }
    } else if (module === 'fundicion' && otSel && otSel.value) {
        if (otSel.selectedIndex !== -1) {
            ready = true;
            const otText = otSel.options[otSel.selectedIndex].text.trim();
            p1 = otText.replace(' — ', ' - '); 
            label = p1;
        }
    } else if (module === 'manuales' && prSel && prSel.value) {
        if (prSel.selectedIndex !== -1) {
            ready = true;
            p1 = prSel.options[prSel.selectedIndex].text.trim();
            label = p1;
        }
    } else if ((module === 'ayudas' || module === 'ayudas_fundicion') && clSel && prSel && clSel.value && prSel.value) {
        // En ayudas_fundicion, prSel puede ser un input hidden
        const isClSelect = clSel.tagName === 'SELECT';
        const isPrSelect = prSel.tagName === 'SELECT';

        if ((!isClSelect || clSel.selectedIndex !== -1) && (!isPrSelect || prSel.selectedIndex !== -1)) {
            ready = true;
            p1 = isPrSelect ? prSel.options[prSel.selectedIndex].text.trim() : prSel.value;
            p2 = isClSelect ? clSel.options[clSel.selectedIndex].text.trim() : clSel.value;
            label = `${p1} / ${p2}`;
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
        // Carpeta destino labels
        document.querySelectorAll('.folder-label').forEach(el => el.textContent = label);

        // Files panel
        if (panelFiles) {
            panelFiles.classList.add('active');
            const h2Span = panelFiles.querySelector('h2 span');
            if (h2Span) h2Span.textContent = label;
            const bcrumb = panelFiles.querySelector('.dibujos-files-breadcrumb strong');
            if (bcrumb) bcrumb.textContent = label;
        }

        // Determinar existencia
        let existe = false;
        if (module === 'dibujos') existe = window.estructura[p1] && window.estructura[p1].includes(p2);
        else if (module === 'fundicion') existe = Array.isArray(window.estructura) ? window.estructura.includes(p1) : window.estructura[p1];
        else if (module === 'manuales') existe = Array.isArray(window.estructura) ? window.estructura.includes(p1) : window.estructura[p1];
        else if (module === 'ayudas' || module === 'ayudas_fundicion') existe = window.estructura[p2] && window.estructura[p2].includes(p1);

        // Visibilidad Alertas Izquierda
        if (alertNotReady) alertNotReady.style.display = 'none';
        if (alertReadyExists) alertReadyExists.style.display = existe ? 'block' : 'none';
        if (alertReadyNotExists) alertReadyNotExists.style.display = existe ? 'none' : 'block';
        if (btnCrear) {
            btnCrear.style.display = existe ? 'none' : 'block';
            if (module === 'dibujos') { btnCrear.dataset.otId = otSel.value; btnCrear.dataset.clase = p2; btnCrear.dataset.folderParam1 = p1; btnCrear.dataset.folderParam2 = p2; }
            else if (module === 'fundicion') { btnCrear.dataset.otId = otSel.value; btnCrear.dataset.folderParam1 = p1; }
            else if (module === 'manuales') { btnCrear.dataset.proceso = p1; btnCrear.dataset.folderParam1 = p1; }
            else if (module === 'ayudas' || module === 'ayudas_fundicion') { btnCrear.dataset.proceso = p1; btnCrear.dataset.clase = p2; btnCrear.dataset.folderParam1 = p1; btnCrear.dataset.folderParam2 = p2; }
        }

        // Visibilidad Panel Derecha (Subir)
        if (uploadNotReadyContent) uploadNotReadyContent.style.display = 'none';
        if (uploadReadyContent) uploadReadyContent.style.display = 'block';
        if (alertUploadNoFolder) alertUploadNoFolder.style.display = existe ? 'none' : 'block';
        if (btnSubir) {
            btnSubir.disabled = !existe;
            if (module === 'dibujos') { btnSubir.dataset.otId = otSel.value; btnSubir.dataset.clase = p2; btnSubir.dataset.folderParam1 = p1; btnSubir.dataset.folderParam2 = p2; }
            else if (module === 'fundicion') { btnSubir.dataset.otId = otSel.value; btnSubir.dataset.folderParam1 = p1; }
            else if (module === 'manuales') { btnSubir.dataset.proceso = p1; btnSubir.dataset.folderParam1 = p1; }
            else if (module === 'ayudas' || module === 'ayudas_fundicion') { btnSubir.dataset.proceso = p1; btnSubir.dataset.clase = p2; btnSubir.dataset.folderParam1 = p1; btnSubir.dataset.folderParam2 = p2; }
        }

        cargarArchivosEnPanel(p1, p2);
    } else {
        if (panelFiles) panelFiles.classList.remove('active');
        if (alertNotReady) alertNotReady.style.display = 'block';
        if (alertReadyExists) alertReadyExists.style.display = 'none';
        if (alertReadyNotExists) alertReadyNotExists.style.display = 'none';
        if (btnCrear) btnCrear.style.display = 'none';

        if (uploadNotReadyContent) uploadNotReadyContent.style.display = 'block';
        if (uploadReadyContent) uploadReadyContent.style.display = 'none';
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
                setTimeout(() => window.location.reload(), 1200);
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
            if (window.moduleType === 'manuales') {
                cargarArchivosEnPanel(payload.proceso);
                actualizarBadge(payload.proceso);
            } else if (window.moduleType === 'fundicion') {
                cargarArchivosEnPanel(payload.param1);
                actualizarBadge(payload.param1);
            } else {
                cargarArchivosEnPanel(payload.param1, payload.param2);
                actualizarBadge(payload.param1, payload.param2);
            }
            loadBadgeCounts();
            loadAuditLog();
        }
    });
}

function getPayloadFromBtn(btn) {
    if (window.moduleType === 'dibujos') return { 
        ot_id: btn.dataset.otId, 
        clase: btn.dataset.clase, 
        param1: btn.dataset.folderParam1 || btn.dataset.otId, 
        param2: btn.dataset.folderParam2 || btn.dataset.clase 
    };
    if (window.moduleType === 'fundicion') return { 
        ot_id: btn.dataset.otId, 
        param1: btn.dataset.folderParam1 || btn.dataset.otId 
    };
    if (window.moduleType === 'manuales') return { 
        proceso: btn.dataset.proceso, 
        param1: btn.dataset.folderParam1 || btn.dataset.proceso 
    };
    if (window.moduleType === 'ayudas' || window.moduleType === 'ayudas_fundicion') return { 
        proceso: btn.dataset.proceso, 
        clase: btn.dataset.clase, 
        param1: btn.dataset.folderParam1 || btn.dataset.proceso, 
        param2: btn.dataset.folderParam2 || btn.dataset.clase 
    };
    return {};
}

function cargarArchivosEnPanel(param1, param2 = null, payloadObj = null) {
    const grid = document.getElementById('archivos-grid');
    if (!grid) return;

    grid.innerHTML = '<p class="d-text-subtle d-text-center d-w-100">Cargando archivos...</p>';

    let url = window.routes['doc.archivos'] + '?';
    if (window.moduleType === 'dibujos') {
        url += `ot=${encodeURIComponent(param1)}&clase=${encodeURIComponent(param2)}`;
    } else if (window.moduleType === 'fundicion') {
        url += `ot=${encodeURIComponent(param1)}`;
    } else if (window.moduleType === 'manuales') {
        url += `proceso=${encodeURIComponent(param1)}`;
    } else if (window.moduleType === 'ayudas' || window.moduleType === 'ayudas_fundicion') {
        url += `proceso=${encodeURIComponent(param1)}&clase=${encodeURIComponent(param2)}`;
    }

    fetch(url, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => renderArchivosGrid(data, param1, param2))
        .catch(() => {
            grid.innerHTML = '<p class="d-text-danger d-text-center d-w-100">Error al cargar los archivos.</p>';
        });
}

function renderArchivosGrid(data, param1, param2) {
    const grid = document.getElementById('archivos-grid');
    const ayudasSection = document.getElementById('fundicion-ayudas-section');
    if (window.moduleType === 'fundicion' && ayudasSection) {
        ayudasSection.style.display = (data.existe && data.archivos.length > 0) ? 'block' : 'none';
    }

    if (!data.existe || data.archivos.length === 0) {
        grid.innerHTML = `
            <div class="dibujos-empty-state" style="grid-column:1/-1;">
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
            <div class="file-name" style="cursor: pointer;" title="Abrir PDF">${archivo.nombre}</div>
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
    hiddenInput.style.display = 'none';
    
    hiddenInput.addEventListener('change', () => {
        const file = hiddenInput.files[0];
        if (!file) return;
        
        let payload = { archivo_anterior: nombreArchivo };
        if (window.moduleType === 'dibujos') { payload.ot = param1; payload.clase = param2; }
        else if (window.moduleType === 'fundicion') { payload.ot = param1; }
        else if (window.moduleType === 'manuales') { payload.proceso = param1; }
        else if (window.moduleType === 'ayudas' || window.moduleType === 'ayudas_fundicion') { payload.proceso = param1; payload.clase = param2; }
        
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
    if (window.moduleType === 'dibujos') { payload.ot = param1; payload.clase = param2; }
    else if (window.moduleType === 'fundicion') { payload.ot = param1; }
    else if (window.moduleType === 'manuales') { payload.proceso = param1; }
    else if (window.moduleType === 'ayudas' || window.moduleType === 'ayudas_fundicion') { payload.proceso = param1; payload.clase = param2; }

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
            cargarArchivosEnPanel(param1, param2);
            actualizarBadge(param1, param2);
            loadAuditLog();
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
            loadBadgeCounts();
            loadAuditLog();
            if (onSuccess) onSuccess();
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
            loadAuditLog();
            if (onSuccess) onSuccess();
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
    if (window.moduleType === 'dibujos') rows = document.querySelectorAll('[data-ot][data-clase]');
    else if (window.moduleType === 'fundicion') rows = document.querySelectorAll('[data-ot]');
    else if (window.moduleType === 'manuales') rows = document.querySelectorAll('[data-proceso]');
    else if (window.moduleType === 'ayudas' || window.moduleType === 'ayudas_fundicion') rows = document.querySelectorAll('[data-proceso][data-clase]');
    
    if(!rows) return;

    rows.forEach(row => {
        if (window.moduleType === 'dibujos') actualizarBadge(row.dataset.ot, row.dataset.clase);
        else if (window.moduleType === 'fundicion') actualizarBadge(row.dataset.ot);
        else if (window.moduleType === 'manuales') actualizarBadge(row.dataset.proceso);
        else if (window.moduleType === 'ayudas' || window.moduleType === 'ayudas_fundicion') actualizarBadge(row.dataset.proceso, row.dataset.clase);
    });
}

function actualizarBadge(param1, param2 = null) {
    let badgeId = '';
    if (window.moduleType === 'dibujos') badgeId = `badge-${slugify(param1)}-${slugify(param2)}`;
    else if (window.moduleType === 'fundicion') badgeId = `badge-${slugify(param1)}`;
    else if (window.moduleType === 'manuales') badgeId = `badge-${slugify(param1)}`;
    else if (window.moduleType === 'ayudas' || window.moduleType === 'ayudas_fundicion') badgeId = `badge-${slugify(param2)}-${slugify(param1)}`;

    const badge = document.getElementById(badgeId);
    if (!badge) return;

    let url = window.routes['doc.archivos'] + '?';
    if (window.moduleType === 'dibujos') url += `ot=${encodeURIComponent(param1)}&clase=${encodeURIComponent(param2)}`;
    else if (window.moduleType === 'fundicion') url += `ot=${encodeURIComponent(param1)}`;
    else if (window.moduleType === 'manuales') url += `proceso=${encodeURIComponent(param1)}`;
    else if (window.moduleType === 'ayudas' || window.moduleType === 'ayudas_fundicion') url += `proceso=${encodeURIComponent(param1)}&clase=${encodeURIComponent(param2)}`;

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
                    const isRoot = (row.dataset.proceso === '--' || 
                                    (!row.dataset.clase && !row.dataset.proceso) || 
                                    (window.moduleType === 'dibujos' && !row.dataset.clase) ||
                                    window.moduleType === 'manuales' || 
                                    window.moduleType === 'fundicion');
                    
                    if (count > 0) {
                        if (btnSpan) btnSpan.textContent = 'Vaciar Carpeta';
                        if (btnImg) btnImg.src = window.baseUrl + '/images/Eliminar-Archivos.png';
                    } else if (isRoot) {
                        if (btnSpan) btnSpan.textContent = 'Eliminar Directorio Raíz';
                        if (btnImg) btnImg.src = window.baseUrl + '/images/Eliminar-Carpeta.png';
                    } else {
                        // Etiquetas específicas para subcarpetas vacías
                        const labelMap = { 'dibujos': 'Clase', 'ayudas': 'Proceso', 'ayudas_fundicion': 'Carpeta' };
                        if (btnSpan) btnSpan.textContent = 'Eliminar ' + (labelMap[window.moduleType] || 'Carpeta');
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
                <td>${log.created_at}</td>
                <td>${log.user_name || '—'}</td>
                <td><span class="action-badge ${log.action}">${actionLabel}</span></td>
                <td>${log.ruta}</td>
                <td>${log.archivo || '—'}</td>`;
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
        .normalize('NFD').replace(/[\u0300-\u036f]/g, "") // Quitar acentos (transliteración básica)
        .replace(/\s+/g, '-')
        .replace(/[^a-z0-9\-_]/g, '-')
        .replace(/-+/g, '-').trim()
        .replace(/^-+|-+$/g, '');
}

function mostrarNotificacion(mensaje, esError = false) {
    const existente = document.querySelector('.dibujos-toast');
    if (existente) existente.remove();

    const toast = document.createElement('div');
    toast.className = 'dibujos-toast' + (esError ? ' error' : '');
    
    const icono = esError ? '❌ ' : '✅ ';
    toast.innerHTML = `<span style="margin-right:8px;">${icono}</span> ${mensaje}`;
    
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
        const module = window.moduleType;
        
        let badgeId = '';
        if (module === 'dibujos') badgeId = `badge-${slugify(p1)}-${slugify(p2)}`;
        else if (module === 'fundicion') badgeId = `badge-${slugify(p1)}`;
        else if (module === 'manuales') badgeId = `badge-${slugify(p1)}`;
        else if (module === 'ayudas' || module === 'ayudas_fundicion') badgeId = `badge-${slugify(p2)}-${slugify(p1)}`;
        
        let count = 0;
        if (badgeId) {
            const badge = document.getElementById(badgeId);
            if (badge) count = parseInt(badge.textContent) || 0;
        }
        
        let isVaciar = (count > 0);
        let actionWord = isVaciar ? 'vaciar todos los archivos' : 'eliminar completamente';
        
        let finalHtml = '';

        if (module === 'dibujos') {
            if (!p2) { // Eliminando OT (Raíz)
                finalHtml = `Se va a ${actionWord} del Directorio Raíz:<br>
                             <strong style="color: #033966; font-size: 1.2em;">${p1}</strong>`;
            } else { // Eliminando Clase (Subcarpeta)
                finalHtml = `Se va a ${actionWord} de la clase:<br>
                             <span class="confirm-label-highlight" style="display: inline-block; margin-top: 0.3em;">${p2}</span><br>
                             <small style="color: #555;">(Orden de Trabajo: ${label.split(' / ')[0]})</small>`;
            }
        } else if (module === 'fundicion') {
            finalHtml = `Se va a ${actionWord} del Directorio Raíz:<br>
                         <strong style="color: #033966; font-size: 1.2em;">${p1}</strong>`;
        } else if (module === 'manuales') {
            finalHtml = `Se va a ${actionWord} del Directorio Raíz:<br>
                         <strong style="color: #033966; font-size: 1.2em;">${p1}</strong>`;
        } else if (module === 'ayudas') {
            if (p1 === '--') { // Eliminando Clase (Raíz)
                finalHtml = `Se va a ${actionWord} del Directorio Raíz:<br>
                             <strong style="color: #033966; font-size: 1.2em;">${p2}</strong>`;
            } else { // Eliminando Proceso (Subcarpeta)
                finalHtml = `Se va a ${actionWord} del proceso:<br>
                             <span class="confirm-label-highlight" style="display: inline-block; margin-top: 0.3em;">${p1}</span><br>
                             <small style="color: #555;">(Clase: ${p2})</small>`;
            }
        } else if (module === 'ayudas_fundicion') {
            if (p1 === '--') { // Eliminando Clase (Raíz)
                finalHtml = `Se va a ${actionWord} del Directorio Raíz:<br>
                             <strong style="color: #033966; font-size: 1.2em;">${p2}</strong>`;
            } else { // Eliminando 'Fundicion' (Subcarpeta)
                finalHtml = `Se va a ${actionWord} de la carpeta:<br>
                             <span class="confirm-label-highlight" style="display: inline-block; margin-top: 0.3em;">Fundición</span><br>
                             <small style="color: #555;">(Clase: ${p2})</small>`;
            }
        }

        msgContainer.innerHTML = finalHtml;
        
        if (modalIcon) {
            modalIcon.src = window.baseUrl + (isVaciar ? '/images/Eliminar-Archivos.png' : '/images/Eliminar-Carpeta.png');
        }
        
        btnConfirm.textContent = isVaciar ? 'Vaciar Carpeta' : 'Eliminar Permanentemente';
        
        modal.style.display = 'flex';
        
        btnConfirm.onclick = () => {
            eliminarCarpetaAJAX(folderToDelete);
            cerrarConfirmarEliminar();
        };
    }
};

window.cerrarConfirmarEliminar = function() {
    const modal = document.getElementById('dibujos-confirm-modal');
    if (modal) modal.style.display = 'none';
    folderToDelete = null;
};

function eliminarCarpetaAJAX(folder) {
    const module = window.moduleType;
    let payload = {};
    let route = window.routes['doc.deleteFolder'];

    if (module === 'dibujos') {
        if (!folder.p2) {
            payload = { ot: folder.p1 };
            route = window.routes['doc.deleteParent'];
        } else {
            payload = { ot: folder.p1, clase: folder.p2 };
        }
    } else if (module === 'fundicion') {
        payload = { ot: folder.p1 };
    } else if (module === 'manuales') {
        payload = { proceso: folder.p1 };
    } else if (module === 'ayudas' || module === 'ayudas_fundicion') {
        if (!folder.p1 || folder.p1 === '--' || folder.p1 === '-- SIN CLASE --') {
            payload = { proceso: folder.p2 }; // Enviamos la Clase al deleteParent
            route = window.routes['doc.deleteParent'];
        } else {
            payload = { proceso: folder.p1, clase: folder.p2 };
        }
    }

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
            
            // Actualizar estructura local
            if (module === 'dibujos') {
                if (window.estructura[folder.p1]) {
                    window.estructura[folder.p1] = window.estructura[folder.p1].filter(c => c !== folder.p2);
                    if (window.estructura[folder.p1].length === 0) delete window.estructura[folder.p1];
                }
            } else if (module === 'fundicion') {
                if (Array.isArray(window.estructura)) {
                    window.estructura = window.estructura.filter(p => p !== folder.p1);
                }
            } else if (module === 'manuales') {
                if (Array.isArray(window.estructura)) {
                    window.estructura = window.estructura.filter(p => p !== folder.p1);
                } else {
                    delete window.estructura[folder.p1];
                }
            } else if (module === 'ayudas') {
                if (window.estructura[folder.p2]) {
                    window.estructura[folder.p2] = window.estructura[folder.p2].filter(p => p !== folder.p1);
                    if (window.estructura[folder.p2].length === 0) delete window.estructura[folder.p2];
                }
            }

            // Si la carpeta eliminada era la que estabamos viendo, limpiar UI
            updateAdminUI();
            loadBadgeCounts();
            loadAuditLog();

            // Opcional: recargar tabla para reflejar cambios (o podrías eliminar la fila del DOM)
            // Por ahora updateAdminUI y badges son suficientes si el usuario vuelve a filtrar.
            // Pero para la tabla, lo mejor es un refresco ligero o recarga si es necesario.
            // Como usamos Blade simple para la tabla, un reload podria ser util aqui si no queremos
            // manipular el DOM de la tabla manualmente.
            setTimeout(() => window.location.reload(), 1500); 

        } else {
            mostrarNotificacion(data.message || 'Error al eliminar carpeta.', true);
        }
    })
    .catch(() => mostrarNotificacion('Error de conexión.', true));
}

window.enviarAlertaFundicion = function(archivo, ot, btnEl) {
    const originalContent = btnEl.innerHTML;
    
    // Check if we already have the spinner to prevent multiple clicks
    if (btnEl.disabled) return;
    
    btnEl.disabled = true;
    btnEl.innerHTML = '<span class="dibujos-spinner dibujos-spinner-sm"></span>...';
    
    fetch(window.routes['fundicion.send_alert'] || '/fundicion/send-alert', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': window.csrfToken,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ ot: ot, archivo: archivo || null })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            mostrarNotificacion(data.message || 'Alerta enviada correctamente.');
            loadAuditLog();
        } else {
            mostrarNotificacion(data.message || 'No se pudo enviar la alerta.', true);
        }
    })
    .catch(() => mostrarNotificacion('Error de conexión al enviar alerta.', true))
    .finally(() => {
        btnEl.disabled = false;
        btnEl.innerHTML = originalContent;
    });
};

function initAyudasFundicionForm() {
    const section = document.getElementById('fundicion-ayudas-section');
    if (!section) return;

    const form = section.querySelector('form');
    if (!form) return;

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        
        const btn = form.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;
        const formData = new FormData(form);
        
        btn.disabled = true;
        btn.innerHTML = '<span class="dibujos-spinner dibujos-spinner-sm"></span> Guardando...';

        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': window.csrfToken,
                'Accept': 'application/json',
            },
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                mostrarNotificacion(data.message || 'Ayudas visuales vinculadas correctamente.');
                loadAuditLog();
                // Recargar página para actualizar la tabla de estructura (columna Ayudas Vinculadas)
                setTimeout(() => window.location.reload(), 1500);
            } else {
                mostrarNotificacion(data.message || 'No se pudieron vincular las ayudas.', true);
            }
        })
        .catch(() => {
            mostrarNotificacion('Error de conexión al guardar ayudas.', true);
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    });

    // Botón de Desvincular Manual
    const btnUnlink = section.querySelector('#btn-desvincular-ayudas');
    if (btnUnlink) {
        btnUnlink.addEventListener('click', () => {
            if (!confirm('¿Seguro que deseas desvincular todas las ayudas visuales de esta OT? Esto también las eliminará de la vista de Almacén.')) return;

            const originalText = btnUnlink.innerHTML;
            btnUnlink.disabled = true;
            btnUnlink.innerHTML = '<span class="dibujos-spinner dibujos-spinner-sm"></span>...';

            fetch(window.routes['fundicion.unlink_ayudas'] || '/fundicion/unlink-ayudas', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': window.csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ ot: btnUnlink.dataset.ot })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    mostrarNotificacion(data.message || 'Ayudas desvinculadas.');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    mostrarNotificacion(data.message || 'No se pudo desvincular.', true);
                }
            })
            .catch(() => mostrarNotificacion('Error de conexión.', true))
            .finally(() => {
                btnUnlink.disabled = false;
                btnUnlink.innerHTML = originalText;
            });
        });
    }
}
