const module = 'fundicion';
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

window.changeDocSelector = function (paramName, value, toClear = []) {
    const url = new URL(window.location.href);
    if (value) url.searchParams.set(paramName, value);
    else url.searchParams.delete(paramName);
    toClear.forEach(p => url.searchParams.delete(p));
    window.location.href = url.toString();
};

window.irACarpeta = function (p1, p2, isId = false) {
    const url = new URL(window.location.href);

    if (module === 'dibujos' || module === 'fundicion') {
        url.searchParams.set('ot_id', p1);
        if (p2 && p2 !== 'null') url.searchParams.set('clase_id', p2);
        else url.searchParams.delete('clase_id');
    } else if (module === 'manuales') {
        url.searchParams.set('proceso_id', p1);
    } else if (module === 'ayudas') {
        if (p2 && p2 !== 'null') url.searchParams.set('clase_id', p2);
        url.searchParams.set('proceso_id', p1);
    } else if (module === 'ayudas_fundicion') {
        if (p2 && p2 !== 'null') url.searchParams.set('clase_id', p2);
    }

    window.location.href = url.toString();
};

function updateDependentSelectors() {
    const urlParams = new URLSearchParams(window.location.search);
    const otSel = document.getElementById('ot-select');
    const clSel = document.getElementById('clase-select');
    const prSel = document.getElementById('proceso-select');

    function forceOption(sel, paramName) {
        if (!sel) return;
        const val = urlParams.get(paramName);
        if (!val) return;
        if (sel.tagName === 'INPUT') {
            sel.value = val;
            return;
        }
        let found = Array.from(sel.options).some(o => o.value === val || o.text === val);
        if (!found) {
            const opt = document.createElement('option');
            opt.value = val;
            opt.text = val;
            sel.appendChild(opt);
        }
        let matchingOpt = Array.from(sel.options).find(o => o.value === val || o.text === val);
        if (matchingOpt) sel.value = matchingOpt.value;
    }

    if (module === 'dibujos' || module === 'fundicion') {
        forceOption(otSel, 'ot_id');
        forceOption(clSel, 'clase_id');
    } else if (module === 'manuales' || module === 'ayudas') {
        forceOption(prSel, 'proceso_id');
        forceOption(clSel, 'clase_id');
    } else if (module === 'ayudas_fundicion') {
        forceOption(clSel, 'clase_id');
        forceOption(prSel, 'proceso_id');
    }

    if (module === 'ayudas_fundicion') {
        if (prSel && clSel) prSel.disabled = !clSel.value;
    } else if (module === 'dibujos' || module === 'fundicion') {
        if (clSel && otSel) clSel.disabled = !otSel.value;
    }
}

function updateAdminUI() {
    let p1 = null, p2 = null, label = '';
    let ready = false;

    const otSel = document.getElementById('ot-select');
    const clSel = document.getElementById('clase-select');
    const prSel = document.getElementById('proceso-select');

    if (module === 'fundicion' && otSel && clSel && otSel.value && clSel.value) {
        if (otSel.selectedIndex !== -1 && clSel.selectedIndex !== -1) {
            ready = true;
            const otText = otSel.options[otSel.selectedIndex].text.trim();
            p1 = normalizeOTName(otText);
            let rawP2 = clSel.options[clSel.selectedIndex].text.trim();
            // Limpiar texto de (Opcional) si existe
            p2 = rawP2.replace(' (Opcional)', '');
            if (clSel.value === '--') p2 = '--';
            label = `${p1} / ${p2}`;
        }
    } else if (module === 'dibujos' && otSel && clSel && otSel.value && clSel.value) {
        if (otSel.selectedIndex !== -1 && clSel.selectedIndex !== -1) {
            ready = true;
            const otText = otSel.options[otSel.selectedIndex].text.trim();
            p1 = normalizeOTName(otText);
            p2 = clSel.options[clSel.selectedIndex].text.trim();
            if (clSel.value === '--') p2 = '--';
            label = `${p1} / ${p2}`;
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
        let label = '';
        if (module === 'dibujos' || module === 'fundicion') {
            label = `<span class="lvl-1">${p1}</span> <span class="lvl-sep">/</span> <span class="lvl-2">${p2}</span>`;
        } else if (module === 'ayudas') {
            label = `<span class="lvl-1">${p2}</span> <span class="lvl-sep">/</span> <span class="lvl-2">${p1}</span>`;
        } else if (module === 'ayudas_fundicion') {
            label = `<span class="lvl-1">${p2}</span>`;
        } else if (module === 'manuales') {
            label = `<span class="lvl-1">${p1}</span>`;
        }

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
            if (module === 'dibujos' || module === 'fundicion') {
                const k1 = Object.keys(window.estructura).find(k => eq(k, p1));
                if (k1 && window.estructura[k1]) existe = window.estructura[k1].some(val => eq(val, p2));
            } else if (module === 'manuales' || module === 'ayudas') {
                if (Array.isArray(window.estructura)) existe = window.estructura.some(val => eq(val, p1));
                else existe = !!Object.keys(window.estructura).find(k => eq(k, p1));
            } else if (module === 'ayudas_fundicion') {
                if (Array.isArray(window.estructura)) existe = window.estructura.some(val => eq(val, p2));
                else existe = !!Object.keys(window.estructura).find(k => eq(k, p2));
            }
        }

        // Visibilidad Alertas Izquierda
        if (alertNotReady) alertNotReady.style.display = 'none';
        if (alertReadyExists) alertReadyExists.style.display = existe ? 'block' : 'none';
        if (alertReadyNotExists) alertReadyNotExists.style.display = existe ? 'none' : 'block';
        if (btnCrear) {
            btnCrear.style.display = existe ? 'none' : 'block';
            if (module === 'dibujos' || module === 'fundicion') { btnCrear.dataset.otId = otSel.value; btnCrear.dataset.clase = p2; btnCrear.dataset.folderParam1 = p1; btnCrear.dataset.folderParam2 = p2; }
            else if (module === 'manuales') { btnCrear.dataset.proceso = p1; btnCrear.dataset.folderParam1 = p1; }
            else if (module === 'ayudas') { btnCrear.dataset.proceso = p1; btnCrear.dataset.clase = p2; btnCrear.dataset.folderParam1 = p1; btnCrear.dataset.folderParam2 = p2; }
            else if (module === 'ayudas_fundicion') { btnCrear.dataset.clase = p2; btnCrear.dataset.folderParam1 = p1; btnCrear.dataset.folderParam2 = p2; }
        }

        // Visibilidad Panel Derecha (Subir)
        if (uploadNotReadyContent) uploadNotReadyContent.style.display = 'none';
        if (uploadReadyContent) uploadReadyContent.style.display = 'block';
        if (alertUploadNoFolder) alertUploadNoFolder.style.display = existe ? 'none' : 'block';

        const fileFormGroup = uploadReadyContent ? uploadReadyContent.querySelector('.dibujos-form-group') : null;
        if (fileFormGroup) fileFormGroup.style.display = existe ? 'block' : 'none';

        const fileInput = document.getElementById('d-upload-file');
        if (fileInput) fileInput.disabled = !existe;

        if (btnSubir) {
            btnSubir.disabled = !existe;
            btnSubir.style.display = existe ? 'inline-block' : 'none';
            if (module === 'dibujos' || module === 'fundicion') { btnSubir.dataset.otId = otSel.value; btnSubir.dataset.clase = p2; btnSubir.dataset.folderParam1 = p1; btnSubir.dataset.folderParam2 = p2; }
            else if (module === 'manuales') { btnSubir.dataset.proceso = p1; btnSubir.dataset.folderParam1 = p1; }
            else if (module === 'ayudas') { btnSubir.dataset.proceso = p1; btnSubir.dataset.clase = p2; btnSubir.dataset.folderParam1 = p1; btnSubir.dataset.folderParam2 = p2; }
            else if (module === 'ayudas_fundicion') { btnSubir.dataset.clase = p2; btnSubir.dataset.folderParam1 = p1; btnSubir.dataset.folderParam2 = p2; }
        }

        cargarArchivosEnPanel(p1, p2);

        if (window.initFundicionChecklists) {
            setTimeout(window.initFundicionChecklists, 100);
        }
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

                    // Actualizar historiales locales en caliente para la vinculación automática
                    if (data.ot && data.clase) {
                        if (!window.historiales) window.historiales = {};
                        if (!window.historiales[data.ot]) window.historiales[data.ot] = [];
                        if (!window.historiales[data.ot].includes(data.clase)) {
                            window.historiales[data.ot].push(data.clase);
                        }
                    }

                    fetch(window.routes['doc.estructura']).then(r => r.json()).then(str => {
                        window.estructura = str;
                        renderEstructuraTable();
                        updateAdminUI();
                        actualizarBadge(payload.param1, payload.param2);
                    });
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
            btnSubir.innerHTML = `<span class="dibujos-spinner"></span> (${i + 1}/${files.length}) Subiendo...`;

            try {
                const data = await subirArchivoIndividual(payload, file);
                if (data.success) {
                    successCount++;
                    // Sincronizar window.historiales si la respuesta trae ot/clase
                    if (data.ot && data.clase) {
                        if (!window.historiales) window.historiales = {};
                        if (!window.historiales[data.ot]) window.historiales[data.ot] = [];
                        if (!window.historiales[data.ot].includes(data.clase)) {
                            window.historiales[data.ot].push(data.clase);
                        }
                    }
                } else {
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
            renderAlertasTable();
            loadAuditLog();
        }
    });
}

function getPayloadFromBtn(btn) {
    if (module === 'dibujos' || module === 'fundicion') return {
        ot_id: btn.dataset.otId,
        clase: btn.dataset.clase,
        param1: btn.dataset.folderParam1 || btn.dataset.otId,
        param2: btn.dataset.folderParam2 || btn.dataset.clase
    };
    if (module === 'manuales') return {
        proceso: btn.dataset.proceso,
        param1: btn.dataset.folderParam1 || btn.dataset.proceso
    };
    if (module === 'ayudas') return {
        proceso: btn.dataset.proceso,
        clase: btn.dataset.clase,
        param1: btn.dataset.folderParam1 || btn.dataset.proceso,
        param2: btn.dataset.folderParam2 || btn.dataset.clase
    };
    if (module === 'ayudas_fundicion') return {
        clase: btn.dataset.clase,
        param1: btn.dataset.folderParam1,
        param2: btn.dataset.folderParam2 || btn.dataset.clase
    };
    return {};
}

function cargarArchivosEnPanel(param1, param2 = null, payloadObj = null) {
    const grid = document.getElementById('archivos-grid');
    if (!grid) return;

    grid.innerHTML = '<p class="d-text-subtle d-text-center d-w-100">Cargando archivos...</p>';

    let url = window.routes['doc.archivos'] + '?';
    const c1 = param1 ? encodeURIComponent(param1) : '';
    const c2 = (param2 && param2 !== 'null') ? encodeURIComponent(param2) : '';

    if (module === 'dibujos' || module === 'fundicion') {
        url += `ot=${c1}&clase=${c2}`;
    } else if (module === 'manuales') {
        url += `proceso=${c1}`;
    } else if (module === 'ayudas') {
        url += `proceso=${c1}&clase=${c2}`;
    } else if (module === 'ayudas_fundicion') {
        url += `clase=${c2}`;
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
    if (module === 'fundicion' && ayudasSection) {
        ayudasSection.style.display = (data.existe && data.archivos.length > 0) ? 'block' : 'none';
    }

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

window.abrirPdf = function (url) {
    window.open(url, '_blank');
};

window.prepararReemplazo = function (nombreArchivo, param1, param2, btnElement) {
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
        if (module === 'dibujos' || module === 'fundicion') { payload.ot = param1; payload.clase = param2; }
        else if (module === 'manuales') { payload.proceso = param1; }
        else if (module === 'ayudas') { payload.proceso = param1; payload.clase = param2; }
        else if (module === 'ayudas_fundicion') { payload.clase = param2; }

        reemplazarPdf(payload, file, btnElement, () => {
            cargarArchivosEnPanel(param1, param2);
            actualizarBadge(param1, param2);
        });
    });

    document.body.appendChild(hiddenInput);
    hiddenInput.click();
    setTimeout(() => hiddenInput.remove(), 5000);
};

window.eliminarPdf = function (nombreArchivo, param1, param2) {
    if (!confirm(`¿Deseas eliminar el archivo "${nombreArchivo}"?\nEsta acción no se puede deshacer.`)) return;

    let payload = { archivo: nombreArchivo };
    if (module === 'dibujos' || module === 'fundicion') { payload.ot = param1; payload.clase = param2; }
    else if (module === 'manuales') { payload.proceso = param1; }
    else if (module === 'ayudas') { payload.proceso = param1; payload.clase = param2; }
    else if (module === 'ayudas_fundicion') { payload.clase = param2; }

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
        if (k !== 'param1' && k !== 'param2') formData.append(k, payload[k]);
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
    if (module === 'dibujos' || module === 'fundicion') rows = document.querySelectorAll('[data-ot][data-clase]');
    else if (module === 'manuales') rows = document.querySelectorAll('[data-proceso]');
    else if (module === 'ayudas') rows = document.querySelectorAll('[data-proceso][data-clase]');
    else if (module === 'ayudas_fundicion') rows = document.querySelectorAll('[data-clase]');

    if (!rows) return;

    rows.forEach(row => {
        if (module === 'dibujos' || module === 'fundicion') actualizarBadge(row.dataset.ot, row.dataset.clase);
        else if (module === 'manuales') actualizarBadge(row.dataset.proceso);
        else if (module === 'ayudas') actualizarBadge(row.dataset.proceso, row.dataset.clase);
        else if (module === 'ayudas_fundicion') actualizarBadge(null, row.dataset.clase);
    });

    // Totales globales por OT (Solo Fundicion)
    if (module === 'fundicion') {
        const totalBadges = document.querySelectorAll('[data-ot-total]');
        totalBadges.forEach(badge => {
            actualizarTotalBadge(badge.dataset.otTotal, badge);
        });
    }
}

function actualizarTotalBadge(ot, badgeElement) {
    if (!badgeElement || !window.routes['doc.total_archivos']) return;

    const url = window.routes['doc.total_archivos'] + '?ot=' + encodeURIComponent(ot);

    fetch(url, { headers: { 'Accept': 'application/json' } })
        .then(async r => {
            const text = await r.text();
            try {
                return JSON.parse(text);
            } catch (err) {
                console.error('Error parseando JSON de total_archivos. Respuesta raw:', text);
                throw err;
            }
        })
        .then(data => {
            if (data.error) {
                console.error("Error en servidor getTotalFiles:", data.error, data.file, data.line);
            }
            const count = data.total || 0;
            badgeElement.textContent = count;
            badgeElement.classList.toggle('badge-count-empty', count === 0);
        })
        .catch(err => console.error('Error cargando total:', err));
}

function getBadgeElement(param1, param2 = null) {
    let rowSelector = '';
    const safeParam1 = param1 ? param1.replace(/"/g, '\\"') : '';
    const safeParam2 = param2 ? param2.replace(/"/g, '\\"') : '';

    if (module === 'dibujos' || module === 'fundicion') {
        rowSelector = `tr[data-ot="${safeParam1}"][data-clase="${safeParam2}"]`;
    } else if (module === 'manuales') {
        rowSelector = `tr[data-proceso="${safeParam1}"]`;
    } else if (module === 'ayudas') {
        rowSelector = `tr[data-proceso="${safeParam1}"][data-clase="${safeParam2}"]`;
    } else if (module === 'ayudas_fundicion') {
        rowSelector = `tr[data-clase="${safeParam2}"]`;
    }

    if (rowSelector) {
        const row = document.querySelector(rowSelector);
        if (row) {
            const badge = row.querySelector('.badge-count');
            if (badge) return badge;
        }
    }

    // Fallback original con IDs
    let badgeId = '';
    if (module === 'dibujos') badgeId = `badge-${slugify(param1)}-${param2 ? slugify(param2) : 'raiz'}`;
    else if (module === 'fundicion') badgeId = `badge-${slugify(param1)}-${slugify(param2 || 'Raíz OT')}`;
    else if (module === 'manuales') badgeId = `badge-${slugify(param1)}`;
    else if (module === 'ayudas') badgeId = `badge-${slugify(param2)}-${slugify(param1)}`;
    else if (module === 'ayudas_fundicion') badgeId = `badge-${slugify(param2)}`;

    return document.getElementById(badgeId);
}

function actualizarBadge(param1, param2 = null) {
    const badge = getBadgeElement(param1, param2);
    if (!badge) return;

    let url = window.routes['doc.archivos'] + '?';
    if (module === 'dibujos' || module === 'fundicion') url += `ot=${encodeURIComponent(param1)}&clase=${encodeURIComponent(param2)}`;
    else if (module === 'manuales') url += `proceso=${encodeURIComponent(param1)}`;
    else if (module === 'ayudas') url += `proceso=${encodeURIComponent(param1)}&clase=${encodeURIComponent(param2)}`;
    else if (module === 'ayudas_fundicion') url += `clase=${encodeURIComponent(param2)}`;

    fetch(url, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            let count = 0;
            if (data.existe && data.archivos) {
                count = data.archivos.length;
            }
            badge.textContent = count;
            badge.classList.toggle('badge-count-empty', count === 0);

            // Sincronizar el badge total de la tabla de alertas para esta OT
            if (param1) {
                const totalBadge = document.querySelector(`[data-ot-total="${param1.replace(/"/g, '\\"')}"]`);
                if (totalBadge) {
                    actualizarTotalBadge(param1, totalBadge);
                }
            }

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
                        (module === 'dibujos' && !row.dataset.clase) ||
                        (module === 'fundicion' && !row.dataset.clase) ||
                        module === 'manuales');

                    if (count > 0) {
                        if (btnSpan) btnSpan.textContent = 'Vaciar Carpeta';
                        if (btnImg) btnImg.src = window.baseUrl + '/images/Eliminar-Archivos.png';
                    } else if (isRoot) {
                        if (btnSpan) btnSpan.textContent = 'Eliminar Directorio Raíz';
                        if (btnImg) btnImg.src = window.baseUrl + '/images/Eliminar-Carpeta.png';
                    } else {
                        // Etiquetas específicas para subcarpetas vacías
                        const labelMap = { 'dibujos': 'Clase', 'ayudas': 'Proceso', 'ayudas_fundicion': 'Carpeta' };
                        if (btnSpan) btnSpan.textContent = 'Eliminar ' + (labelMap['fundicion'] || 'Carpeta');
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

window.confirmarEliminarCarpeta = function (p1, p2, label) {
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
            if (!p2) { // Eliminando OT (Raíz)
                finalHtml = `Se va a ${actionWord} del Directorio Raíz:<br>
                             <strong style="color: #033966; font-size: 1.2em;">${p1}</strong>`;
            } else { // Eliminando Clase (Subcarpeta)
                finalHtml = `Se va a ${actionWord} de la clase:<br>
                             <span class="confirm-label-highlight" style="display: inline-block; margin-top: 0.3em;">${p2}</span><br>
                             <small style="color: #555;">(Orden de Trabajo: ${label.split(' / ')[0]})</small>`;
            }
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

window.cerrarConfirmarEliminar = function () {
    const modal = document.getElementById('dibujos-confirm-modal');
    if (modal) modal.style.display = 'none';
    folderToDelete = null;
};

function eliminarCarpetaAJAX(folder) {
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
        if (!folder.p2) {
            payload = { ot: folder.p1 };
            route = window.routes['doc.deleteParent'];
        } else {
            payload = { ot: folder.p1, clase: folder.p2 };
            route = window.routes['doc.deleteFolder'];
        }
    } else if (module === 'manuales') {
        payload = { proceso: folder.p1 };
    } else if (module === 'ayudas') {
        if (!folder.p1 || folder.p1 === '--' || folder.p1 === '-- SIN CLASE --') {
            payload = { proceso: folder.p2 }; // Enviamos la Clase al deleteParent
            route = window.routes['doc.deleteParent'];
        } else {
            payload = { proceso: folder.p1, clase: folder.p2 };
        }
    } else if (module === 'ayudas_fundicion') {
        // Estructura de 1 nivel: solo clase
        payload = { clase: folder.p1 };
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
                    if (window.estructura[folder.p1]) {
                        if (folder.p2) {
                            window.estructura[folder.p1] = window.estructura[folder.p1].filter(c => c !== folder.p2);
                            if (window.estructura[folder.p1].length === 0) delete window.estructura[folder.p1];
                        } else {
                            delete window.estructura[folder.p1];
                        }
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

window.enviarAlertaFundicion = function (archivo, ot, btnEl) {
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
                    if (data.ayudasLinked !== undefined && data.ot) {
                        if (!window.historiales) window.historiales = {};
                        window.historiales[data.ot] = data.ayudasLinked;
                        if (typeof renderAlertasTable === 'function') renderAlertasTable();
                    }
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
                        loadAuditLog();
                        if (data.ayudasLinked !== undefined && data.ot) {
                            if (!window.historiales) window.historiales = {};
                            window.historiales[data.ot] = data.ayudasLinked;
                            if (typeof renderAlertasTable === 'function') renderAlertasTable();
                        }
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

// ══════════════════════════════════════════════════════════
// FundicionChecklistCard — Checklist reactivo del flujo de fundición
// (Añadido para dar soporte interactivo a la vista actual)
// ══════════════════════════════════════════════════════════
class FundicionChecklistCard {
    constructor(otId, container) {
        this.otId = otId;
        this._data = { pasos: {} };
        this.container = container;
        this._pollTimer = null;
        this._mounted = false;
        this.root = null;

        if (container.classList.contains('fundicion-checklist-card')) {
            this.root = container;
            this.root.innerHTML = '';
            this._mounted = true;
            this._renderInto(this.root);
        } else {
            this._mount();
        }

        this._poll();
        this._startPolling();
    }

    _mount() {
        if (this._mounted) return;
        this.root = document.createElement('div');
        this.root.className = 'fundicion-checklist-card';
        this.root.id = `fundicion-checklist-${this.otId}`;
        this._renderInto(this.root);
        this.container.appendChild(this.root);
        this._mounted = true;
    }

    _renderInto(card) {
        const header = document.createElement('div');
        header.className = 'checklist-header';

        const title = document.createElement('span');
        title.className = 'checklist-title';
        title.textContent = 'Levantamiento de OT';
        header.appendChild(title);

        const badge = document.createElement('span');
        badge.className = 'checklist-reproceso-badge';
        badge.id = `checklist-badge-${this.otId}`;
        badge.textContent = 'Reproceso';
        badge.style.display = 'none';
        header.appendChild(badge);

        card.appendChild(header);

        const itemsContainer = document.createElement('div');
        itemsContainer.className = 'checklist-items';
        itemsContainer.id = `checklist-items-${this.otId}`;
        itemsContainer.style.display = 'none';
        card.appendChild(itemsContainer);

        card.classList.add('is-closed');

        // Toggle logic: make whole card clickable
        card.style.cursor = 'pointer';
        card.addEventListener('click', () => {
            if (itemsContainer.style.display === 'none') {
                itemsContainer.style.display = '';
                card.classList.remove('is-closed');
            } else {
                itemsContainer.style.display = 'none';
                card.classList.add('is-closed');
            }
        });

        this._updateCard(card, this._data);
    }

    _getIconFor(estado) {
        const baseUrl = window.baseUrl || (window.location.origin + '/');
        const slash = baseUrl.endsWith('/') ? '' : '/';
        let imgName = '';
        switch (estado) {
            case 'completado': imgName = 'Aprobado.png'; break;
            case 'pendiente': imgName = 'Espera.png'; break;
            case 'rechazado': imgName = 'Rechazado.png'; break;
            case 'inactivo': default: imgName = 'Recibido.png'; break;
        }
        return `${baseUrl}${slash}images/${imgName}`;
    }

    _getBorderColor(data) {
        const pasos = Object.values(data.pasos || {});
        if (pasos.some(p => p.estado === 'rechazado')) return '#9D0402';
        if (pasos.length > 0 && pasos.every(p => p.estado === 'completado')) return '#0C8201';
        return '#424141';
    }

    _updateCard(card, data) {
        if (!card) return;

        let colorHex = this._getBorderColor(data);
        if (colorHex === '#9D0402') { card.style.borderColor = '#9D0402'; card.style.boxShadow = 'none'; }
        else if (colorHex === '#0C8201') { card.style.borderColor = '#0C8201'; card.style.boxShadow = 'none'; }
        else { card.style.borderColor = ''; card.style.boxShadow = ''; }

        const badge = card.querySelector(`#checklist-badge-${this.otId}`);
        if (badge) {
            badge.style.display = data.isBadgeVisible ? 'inline-flex' : 'none';
            if (data.badgeText) badge.textContent = data.badgeText;
        }

        const container = card.querySelector(`#checklist-items-${this.otId}`);
        if (!container) return;
        container.innerHTML = '';

        const pasosEntries = Object.entries(data.pasos || {});
        if (pasosEntries.length === 0) {
            container.innerHTML = `<div style="padding: 10px; color: #64748b; font-size: 0.85rem; text-align: center;">Cargando estado...</div>`;
            return;
        }

        pasosEntries.forEach(([key, paso]) => {
            if (!paso) return;

            const item = document.createElement('div');
            item.className = `checklist-item checklist-item--${paso.estado}`;
            if (paso.tooltip) { item.title = paso.tooltip; item.style.cursor = 'help'; }

            const iconSpan = document.createElement('span');
            iconSpan.className = 'checklist-icon';

            const img = document.createElement('img');
            img.src = this._getIconFor(paso.estado);
            img.alt = paso.estado;
            img.className = 'checklist-state-icon';
            iconSpan.appendChild(img);

            const label = document.createElement('span');
            label.className = 'checklist-label';
            label.textContent = paso.label;

            item.appendChild(iconSpan);
            item.appendChild(label);
            container.appendChild(item);
        });
    }

    _startPolling() {
        this._pollTimer = setInterval(() => this._poll(), 30_000);
    }

    async _poll() {
        if (!this.root || !this.root.isConnected) {
            this._destroy();
            return;
        }
        try {
            const endpointUrl = window.fundicionChecklistUrl || `${window.location.origin}/admin/fundicion/checklist`;
            const endpoint = `${endpointUrl}/${this.otId}`;
            const res = await fetch(endpoint);
            if (!res.ok) return;

            const data = await res.json();
            if (data && !data.error) {
                this._data = data;
                this._updateCard(this.root, data);
            }
        } catch (_) { }
    }

    _destroy() {
        clearInterval(this._pollTimer);
        this._pollTimer = null;
        if (this.root && this.root.isConnected && !this.root.classList.contains('fundicion-checklist-card')) {
            this.root.remove();
        }
    }
}

function initFundicionChecklists() {
    document.querySelectorAll('.fundicion-checklist-card, .fundicion-checklist-container').forEach(el => {
        if (el.hasAttribute('data-checklist-init')) return;

        let otId = el.getAttribute('data-ot');
        if (!otId && el.id && el.id.startsWith('fundicion-checklist-')) {
            otId = el.id.replace('fundicion-checklist-', '');
        }

        if (otId) {
            el.setAttribute('data-checklist-init', 'true');
            new FundicionChecklistCard(otId, el);
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    setTimeout(initFundicionChecklists, 500);
});

window.initFundicionChecklists = initFundicionChecklists;


function renderEstructuraTable() {
    const tbody = document.querySelector('#tabla-estructura tbody');
    if (!tbody) return;

    // Guardar conteos existentes para no perderlos y evitar peticiones extras
    const existingCounts = {};
    tbody.querySelectorAll('.badge-count').forEach(span => {
        existingCounts[span.id] = span.textContent;
    });

    tbody.innerHTML = '';

    const ots = Object.keys(window.estructura);
    if (ots.length > 0) {
        ots.forEach(otName => {
            const clases = window.estructura[otName];
            const displayClases = clases.length > 0 ? clases : ['--'];

            displayClases.forEach(claseLabel => {
                const esHuerfano = (claseLabel === '--');
                const badgeId = "badge-" + slugify(otName) + "-" + slugify(claseLabel);
                const savedCount = existingCounts[badgeId] !== undefined ? existingCounts[badgeId] : '0';
                const countClass = savedCount === '0' ? 'badge-count badge-count-empty' : 'badge-count';

                const tr = document.createElement('tr');
                tr.setAttribute('data-ot', otName);
                tr.setAttribute('data-clase', claseLabel);
                tr.innerHTML = `
                    <td class="d-text-center d-text-primary"><strong>${otName}</strong></td>
                    <td class="d-text-center">
                        ${esHuerfano ?
                        `<em class="d-text-danger d-text-bold">Sin clases</em>` :
                        `<span class="d-text-success d-text-bold">${claseLabel}</span>`
                    }
                    </td>
                    <td class="d-text-center">
                        ${esHuerfano ?
                        `<span class="d-text-subtle">—</span>` :
                        `<span class="${countClass}" id="${badgeId}">${savedCount}</span>`
                    }
                    </td>
                    <td class="d-text-center">
                        <div class="td-actions">
                            ${!esHuerfano ?
                        `<button class="btn-action-icon btn-ver-archivos" title="Ver archivos"
                                    onclick="irACarpeta('${otName}', '${claseLabel}')">
                                    <img src="${window.baseUrl}/images/documento.png" alt="Ver">
                                    <span>Ver PDF's</span>
                                </button>` : ''
                    }
                            <button class="btn-action-icon btn-eliminar-carpeta" title="Eliminar carpeta"
                                onclick="confirmarEliminarCarpeta('${otName}', '${claseLabel}', '${otName}${esHuerfano ? "" : " / " + claseLabel}')">
                                <img src="${window.baseUrl}/images/Eliminar-Carpeta.png" alt="Eliminar">
                                <span>Eliminar ${esHuerfano ? 'Directorio Raíz' : 'Clase'}</span>
                            </button>
                        </div>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        });
    } else {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td colspan="4" class="d-text-center d-text-subtle">
                No hay carpetas de OTs registradas en el servidor.
            </td>
        `;
        tbody.appendChild(tr);
    }
    if (typeof renderAlertasTable === 'function') renderAlertasTable();
}

function renderAlertasTable() {
    const tbody = document.getElementById('tabla-alertas-body');
    if (!tbody) return;
    tbody.innerHTML = '';

    const ots = Object.keys(window.estructura);
    if (ots.length > 0) {
        ots.forEach(otName => {
            const clasesFisicas = window.estructura[otName];

            // Parsear ID de OT
            const match = otName.match(/OT\s*(\d+)/i);
            const otIdNumber = match ? parseInt(match[1]) : 0;

            // Buscar datos de la OT
            const otReal = window.todasLasOTs ? window.todasLasOTs.find(o => o.id === otIdNumber) : null;
            const otLabel = otReal ? `OT ${otReal.id}${otReal.moldura_nombre ? ' — ' + otReal.moldura_nombre : ''}` : otName;
            const otIdBD = otReal ? otReal.id : null;

            // Filtrar ayudas que sí existen físicamente
            const ayudasLinked = window.historiales ? (window.historiales[otName] || []) : [];
            const ayudasFiltradas = ayudasLinked.filter(a => {
                const val = (a || '').toString().trim().toLowerCase();
                if (!val || val === 'null' || val === 'undefined') return false;
                return clasesFisicas.includes(a);
            });

            let htmlAyudas = '';
            if (ayudasFiltradas.length > 0) {
                htmlAyudas = `<div class="d-flex d-flex-wrap d-justify-center d-gap-1">`;
                ayudasFiltradas.forEach(al => {
                    let clTagId = 'null';
                    const clTagReal = window.todasLasClases ? window.todasLasClases.find(c => c.nombre === al) : null;
                    if (clTagReal) {
                        clTagId = clTagReal.id;
                    } else if (['Pistones', 'Guías', 'Guias'].includes(al)) {
                        clTagId = al;
                    }

                    htmlAyudas += `
                        <span class="badge-ayuda-tag clickable-tag" title="Ir a esta carpeta"
                            onclick="irACarpeta('${otIdBD || otName}', '${clTagId}', ${otIdBD ? 'true' : 'false'})">
                            ${al}
                        </span>
                    `;
                });
                htmlAyudas += `</div>`;
            } else {
                htmlAyudas = `
                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 4px;">
                        <img src="${window.baseUrl}/images/sin_AV.png" alt="Sin clases" style="width: 30px; height: 30px; opacity: 0.85;">
                        <span style="color: #d32f2f; font-size: 0.85em; font-weight: 800; text-transform: uppercase;">Sin clases y ayudas visuales vinculadas</span>
                    </div>
                `;
            }

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="d-text-center d-text-primary"><strong>${otLabel}</strong></td>
                <td class="d-text-center">${htmlAyudas}</td>
                <td class="d-text-center">
                    <span class="badge-count" data-ot-total="${otName}">
                        ...
                    </span>
                </td>
                <td class="d-text-center">
                    <div class="td-actions">
                        <button class="btn-action-icon btn-alerta-fund" title="Enviar correo de alerta global"
                            onclick="enviarAlertaFundicion(null, '${otName}', this)">
                            <img src="${window.baseUrl}/images/enviando.png" alt="Alerta">
                            <span>Enviar Correo</span>
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });

        // Cargar conteos de totales para esta tabla
        loadTotalBadgeCounts();
    }
}

function loadTotalBadgeCounts() {
    const totalBadges = document.querySelectorAll('[data-ot-total]');
    totalBadges.forEach(badge => {
        actualizarTotalBadge(badge.dataset.otTotal, badge);
    });
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
                row.style.display = '';
            } else {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(val) ? '' : 'none';
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
    poblarYFiltrarSelect('filtro-tabla-alertas', '#tabla-alertas-body', '— Mostrar Todas las OTs —');
}
