/**
 * manage_programas.js
 * Lógica JavaScript para la vista de Gestión de Programas CNC.
 * Patrón idéntico a manage_dibujos.js, adaptado a 3 niveles: OT → Clase → Proceso.
 */

document.addEventListener('DOMContentLoaded', () => {
    initCreateFolderBtn();
    initUploadBtn();
    loadBadgeCounts();
    loadAuditLog();
    initTableFilters();

    updateDependentSelectors();
    updateAdminUI();
});

// =========================================================================
// SELECTORES EN CASCADA
// =========================================================================

/**
 * Recarga la página con el nuevo parámetro en la query string.
 * Cuando el selector de OT cambia, limpia clase_id y proceso.
 * Cuando el selector de Clase cambia, limpia proceso.
 */
window.changeProgSelector = function (paramName, value, toClear = []) {
    const url = new URL(window.location.href);
    if (value) url.searchParams.set(paramName, value);
    else url.searchParams.delete(paramName);
    toClear.forEach(p => url.searchParams.delete(p));

    // Si cambia clase_id → cargar procesos vía AJAX y redirigir después
    if (paramName === 'clase_id' && value) {
        fetch(`${window.routes['programas.procesos_clase']}?clase_id=${encodeURIComponent(value)}`, {
            headers: { 'Accept': 'application/json' }
        })
            .then(r => r.json())
            .then(() => { window.location.href = url.toString(); })
            .catch(() => { window.location.href = url.toString(); });
        return;
    }

    window.location.href = url.toString();
};

/**
 * Navega a la carpeta OT/Clase/Proceso seleccionada desde la tabla.
 */
window.irACarpeta = function (otId, claseId, proceso, isId = false) {
    const url = new URL(window.location.href);
    url.searchParams.set('ot_id', otId);
    if (claseId && claseId !== 'null') url.searchParams.set('clase_id', claseId);
    else url.searchParams.delete('clase_id');
    if (proceso && proceso !== 'null') url.searchParams.set('proceso', proceso);
    else url.searchParams.delete('proceso');
    window.location.href = url.toString();
};

// =========================================================================
// SINCRONIZACIÓN UI
// =========================================================================

function updateDependentSelectors() {
    const urlParams = new URLSearchParams(window.location.search);
    const otSel   = document.getElementById('ot-select');
    const clSel   = document.getElementById('clase-select');
    const prSel   = document.getElementById('proceso-select');

    function forceOption(sel, paramNames) {
        if (!sel) return;
        const names = Array.isArray(paramNames) ? paramNames : [paramNames];
        let val = null;
        for (const n of names) {
            val = urlParams.get(n);
            if (val) break;
        }
        if (!val) return;
        let found = Array.from(sel.options).some(o => String(o.value) === String(val));
        if (!found) {
            const opt = document.createElement('option');
            opt.value = val; opt.text = val;
            sel.appendChild(opt);
        }
        const matching = Array.from(sel.options).find(o => String(o.value) === String(val));
        if (matching) sel.value = matching.value;
    }

    forceOption(otSel, 'ot_id');

    if (otSel && otSel.value && clSel && window.todasLasOTs) {
        const otMatch = window.todasLasOTs.find(o => String(o.id) === String(otSel.value));
        if (otMatch && otMatch.clases) {
            const currentClaseVal = clSel.value || urlParams.get('clase_id');
            clSel.innerHTML = '<option value="">— Seleccionar Clase —</option>';
            otMatch.clases.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.id; opt.textContent = c.nombre;
                if (String(c.id) === String(currentClaseVal)) opt.selected = true;
                clSel.appendChild(opt);
            });
            clSel.disabled = false;
        }
    }

    forceOption(clSel, 'clase_id');
    if (clSel) clSel.disabled = !otSel?.value;

    // El selector de proceso ya está renderizado por Blade; solo sincronizamos (soporta 'proceso' o 'proceso_id')
    forceOption(prSel, ['proceso', 'proceso_id']);
    if (prSel) prSel.disabled = !clSel?.value;
}

function updateAdminUI() {
    const otSel = document.getElementById('ot-select');
    const clSel = document.getElementById('clase-select');
    const prSel = document.getElementById('proceso-select');

    const ready = Boolean(otSel?.value && clSel?.value && prSel?.value);

    const alertReadyExists    = document.getElementById('alert-ready-exists');
    const alertReadyNotExists = document.getElementById('alert-ready-not-exists');
    const alertNotReady       = document.getElementById('alert-not-ready');
    const btnCrear            = document.getElementById('btn-crear-carpeta');
    const uploadReady         = document.getElementById('upload-ready-content');
    const uploadNotReady      = document.getElementById('upload-not-ready-content');
    const alertNoFolder       = document.getElementById('alert-upload-no-folder');
    const btnSubir            = document.getElementById('btn-subir-programa');

    if (ready) {
        const otText = otSel.options[otSel.selectedIndex]?.text.trim() || '';
        const p1 = normalizeOTName(otText);
        const p2 = clSel.options[clSel.selectedIndex]?.text.trim() || '';
        const p3 = prSel.value;

        // Verificar existencia en estructura del filesystem
        const sanitizeJS = (str) => str ? String(str).replace(/\.\.+/g, '').replace(/[\\\/]/g, '').trim() : '';
        const eq = (a, b) => a && b && sanitizeJS(a).toLowerCase() === sanitizeJS(b).toLowerCase();
        let existe = false;
        if (window.estructura) {
            const k1 = Object.keys(window.estructura).find(k => eq(k, p1));
            if (k1 && window.estructura[k1]) {
                const k2 = Object.keys(window.estructura[k1]).find(k => eq(k, p2));
                if (k2 && Array.isArray(window.estructura[k1][k2])) {
                    existe = window.estructura[k1][k2].some(proc => eq(proc, p3));
                }
            }
        }

        const label = `<span class="lvl-1">${p1}</span> <span class="lvl-sep">/</span> <span class="lvl-2">${p2}</span> <span class="lvl-sep">/</span> <span class="lvl-3">${p3}</span>`;
        document.querySelectorAll('.folder-label').forEach(el => el.innerHTML = label);

        if (alertNotReady) {
            alertNotReady.hidden = true;
            alertNotReady.classList.add('hidden');
        }
        if (alertReadyExists) {
            alertReadyExists.hidden = !existe;
            alertReadyExists.classList.toggle('hidden', !existe);
        }
        if (alertReadyNotExists) {
            alertReadyNotExists.hidden = existe;
            alertReadyNotExists.classList.toggle('hidden', existe);
        }
        if (btnCrear) {
            btnCrear.hidden = existe;
            btnCrear.classList.toggle('hidden', existe);
            btnCrear.dataset.ot      = otSel.value;
            btnCrear.dataset.clase   = clSel.value;
            btnCrear.dataset.proceso = p3;
        }

        if (uploadNotReady) {
            uploadNotReady.hidden = true;
            uploadNotReady.classList.add('hidden');
        }
        if (uploadReady) {
            uploadReady.hidden = false;
            uploadReady.classList.remove('hidden');
        }
        if (alertNoFolder) {
            alertNoFolder.hidden = existe;
            alertNoFolder.classList.toggle('hidden', existe);
        }

        if (btnSubir) {
            btnSubir.dataset.ot      = otSel.value;
            btnSubir.dataset.clase   = clSel.value;
            btnSubir.dataset.proceso = p3;
            btnSubir.disabled        = !existe;
        }

        cargarArchivosEnPanel(p1, p2, p3);
    } else {
        if (alertNotReady) {
            alertNotReady.hidden = false;
            alertNotReady.classList.remove('hidden');
        }
        if (alertReadyExists) {
            alertReadyExists.hidden = true;
            alertReadyExists.classList.add('hidden');
        }
        if (alertReadyNotExists) {
            alertReadyNotExists.hidden = true;
            alertReadyNotExists.classList.add('hidden');
        }
        if (btnCrear) {
            btnCrear.hidden = true;
            btnCrear.classList.add('hidden');
        }
        if (uploadNotReady) {
            uploadNotReady.hidden = false;
            uploadNotReady.classList.remove('hidden');
        }
        if (uploadReady) {
            uploadReady.hidden = true;
            uploadReady.classList.add('hidden');
        }
    }
}

// =========================================================================
// BOTÓN CREAR CARPETA
// =========================================================================

function initCreateFolderBtn() {
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('#btn-crear-carpeta');
        if (!btn || btn.disabled) return;

        const otId    = btn.dataset.ot;
        const claseId = btn.dataset.clase;
        const proceso = btn.dataset.proceso;

        if (!otId || !claseId || !proceso) {
            mostrarNotificacion('Selección incompleta para crear carpeta.', true);
            return;
        }

        btn.disabled = true;
        const orig = btn.innerHTML;
        btn.innerHTML = '<span class="dibujos-spinner"></span> Creando...';

        fetch(window.routes['doc.createFolder'], {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': window.csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ ot_id: otId, clase_id: claseId, proceso }),
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
        .finally(() => { btn.disabled = false; btn.innerHTML = orig; });
    });
}

// =========================================================================
// BOTÓN SUBIR PROGRAMA
// =========================================================================

function initUploadBtn() {
    // Cambio de archivo
    document.addEventListener('change', (e) => {
        const fileInput = e.target.closest('#d-upload-file');
        if (!fileInput) return;

        const nameLabel   = document.getElementById('d-upload-file-name');
        const labelText   = document.getElementById('d-upload-file-label-text');
        const btnSubir    = document.getElementById('btn-subir-programa');
        const files       = fileInput.files;

        // Validar extensiones
        const invalid = Array.from(files).filter(f => {
            const ext = f.name.split('.').pop().toLowerCase();
            return !['nc', 'cnc'].includes(ext);
        });

        if (invalid.length > 0) {
            mostrarNotificacion(`Solo se permiten archivos .NC o .CNC. Archivos inválidos: ${invalid.map(f => f.name).join(', ')}`, true);
            fileInput.value = '';
            if (nameLabel) nameLabel.textContent = '';
            if (labelText) labelText.textContent = 'Seleccionar archivo .NC / .CNC';
            if (btnSubir)  btnSubir.disabled = true;
            return;
        }

        if (files.length > 0) {
            const text = files.length === 1 ? files[0].name : `${files.length} archivos seleccionados`;
            if (nameLabel) nameLabel.textContent = text;
            if (labelText) labelText.textContent = 'Seleccionado: ' + text;
            if (btnSubir)  btnSubir.disabled = false;
        } else {
            if (nameLabel) nameLabel.textContent = '';
            if (labelText) labelText.textContent = 'Seleccionar archivo .NC / .CNC';
            if (btnSubir)  btnSubir.disabled = true;
        }
    });

    // Click en botón subir
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('#btn-subir-programa');
        if (!btn || btn.disabled) return;

        const fileInput = document.getElementById('d-upload-file');
        const files     = fileInput ? fileInput.files : [];
        if (files.length === 0) { mostrarNotificacion('Por favor selecciona al menos un archivo.', true); return; }

        const otId    = btn.dataset.ot;
        const claseId = btn.dataset.clase;
        const proceso = btn.dataset.proceso;
        const origTxt = btn.innerHTML;
        btn.disabled  = true;

        let ok = 0, err = 0;

        for (let i = 0; i < files.length; i++) {
            btn.innerHTML = `<span class="dibujos-spinner"></span> (${i+1}/${files.length}) Subiendo...`;
            try {
                const data = await subirPrograma({ ot_id: otId, clase_id: claseId, proceso }, files[i]);
                if (data.success) ok++;
                else { err++; mostrarNotificacion(`Error en "${files[i].name}": ${data.message}`, true); }
            } catch { err++; mostrarNotificacion(`Error de conexión en "${files[i].name}"`, true); }
        }

        btn.disabled = true;
        btn.innerHTML = origTxt;
        if (fileInput) fileInput.value = '';

        const nameLabel = document.getElementById('d-upload-file-name');
        const labelText = document.getElementById('d-upload-file-label-text');
        if (nameLabel) nameLabel.textContent = '';
        if (labelText) labelText.textContent = 'Seleccionar archivo .NC / .CNC';

        if (ok > 0) {
            mostrarNotificacion(ok === 1 ? 'Programa subido correctamente.' : `${ok} programas subidos correctamente.`);
            const p1 = normalizeOTName(document.getElementById('ot-select')?.options[document.getElementById('ot-select')?.selectedIndex]?.text || '');
            const p2 = document.getElementById('clase-select')?.options[document.getElementById('clase-select')?.selectedIndex]?.text || '';
            const p3 = document.getElementById('proceso-select')?.value || '';
            cargarArchivosEnPanel(p1, p2, p3);
            actualizarBadge(p1, p2, p3);
            loadAuditLog();
        }
    });
}

function subirPrograma(payload, file) {
    const formData = new FormData();
    formData.append('ot_id',    payload.ot_id);
    formData.append('clase_id', payload.clase_id);
    formData.append('proceso',  payload.proceso);
    formData.append('programa', file);

    return fetch(window.routes['doc.upload'], {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': window.csrfToken, 'Accept': 'application/json' },
        body: formData,
    }).then(r => r.json());
}

// =========================================================================
// PANEL DE ARCHIVOS
// =========================================================================

function cargarArchivosEnPanel(p1, p2, p3) {
    const grid = document.getElementById('archivos-grid');
    if (!grid) return;
    grid.innerHTML = '<p class="d-text-subtle d-text-center d-w-100">Cargando archivos...</p>';

    const url = `${window.routes['doc.archivos']}?ot=${encodeURIComponent(p1)}&clase=${encodeURIComponent(p2)}&proceso=${encodeURIComponent(p3)}`;
    fetch(url, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => renderArchivosGrid(data, p1, p2, p3))
        .catch(() => { grid.innerHTML = '<p class="d-text-danger d-text-center d-w-100">Error al cargar los archivos.</p>'; });
}

function renderArchivosGrid(data, p1, p2, p3) {
    const grid = document.getElementById('archivos-grid');
    if (!data.existe || !data.archivos || data.archivos.length === 0) {
        grid.innerHTML = `
            <div class="dibujos-empty-state" style="grid-column:1/-1; text-align:center;">
                <p>No hay programas CNC. Sube el primero usando el panel de arriba.</p>
            </div>`;
        return;
    }

    grid.innerHTML = '';
    data.archivos.forEach((archivo, i) => {
        const card = document.createElement('div');
        card.className = 'dibujos-file-card';
        card.style.animationDelay = `${i * 0.05}s`;

        const extUpper = String(archivo.extension || '').toUpperCase();
        const isNC     = extUpper === 'NC';

        const iconDefault = isNC
            ? (window.imgProgramsNCShadow || (window.baseUrl + '/images/ProgramsNC-Shadow.png'))
            : (window.imgProgramsCNCShadow || (window.baseUrl + '/images/ProgramsCNC-Shadow.png'));

        const iconHover = isNC
            ? (window.imgProgramsNC || (window.baseUrl + '/images/ProgramsNC.png'))
            : (window.imgProgramsCNC || (window.baseUrl + '/images/ProgramsCNC.png'));

        card.innerHTML = `
            <div class="file-icon-wrapper" style="cursor:pointer;" title="Descargar programa">
                <img src="${iconDefault}" class="file-icon icon-default">
                <img src="${iconHover}" class="file-icon icon-hover">
            </div>
            <div class="file-name" style="cursor:pointer;" title="Descargar">${escapeHTML(archivo.nombre)}</div>
            <div class="file-actions">
                <button class="btn-dibujos btn-dibujos-sm btn-ver" onclick="descargarPrograma('${escapeHTML(archivo.url)}')">Descargar</button>
                <button class="btn-dibujos btn-dibujos-sm btn-dibujos-danger btn-eliminar"
                    onclick="eliminarPrograma('${escapeHTML(archivo.nombre)}', '${escapeHTML(p1)}', '${escapeHTML(p2)}', '${escapeHTML(p3)}')">Eliminar</button>
            </div>`;

        grid.appendChild(card);
    });
}

window.descargarPrograma = function (url) {
    window.open(url, '_blank');
};

window.eliminarPrograma = function (nombre, p1, p2, p3) {
    if (!confirm(`¿Deseas eliminar el archivo "${nombre}"?\nEsta acción no se puede deshacer.`)) return;

    fetch(window.routes['doc.delete'], {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': window.csrfToken,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ ot: p1, clase: p2, proceso: p3, archivo: nombre }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            mostrarNotificacion(data.message || 'Archivo eliminado correctamente.');
            setTimeout(() => window.location.reload(), 900);
        } else {
            mostrarNotificacion(data.message || 'No se pudo eliminar.', true);
        }
    })
    .catch(() => mostrarNotificacion('Error de conexión. Intente de nuevo.', true));
};

// =========================================================================
// BADGES DE CONTEO
// =========================================================================

function loadBadgeCounts() {
    const rows = document.querySelectorAll('tr[data-ot][data-clase][data-proceso]');
    if (!rows.length) return;

    const observer = new IntersectionObserver((entries, obs) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const row = entry.target;
                actualizarBadge(row.dataset.ot, row.dataset.clase, row.dataset.proceso);
                obs.unobserve(row);
            }
        });
    }, { rootMargin: '50px' });

    rows.forEach(row => observer.observe(row));
}

function actualizarBadge(p1, p2, p3) {
    const badgeId = `badge-${slugify(p1)}-${slugify(p2)}-${slugify(p3)}`;
    const badge   = document.getElementById(badgeId);
    if (!badge) return;

    const url = `${window.routes['doc.archivos']}?ot=${encodeURIComponent(p1)}&clase=${encodeURIComponent(p2)}&proceso=${encodeURIComponent(p3)}`;
    fetch(url, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            const count = data.existe && data.archivos ? data.archivos.length : 0;
            badge.textContent = count;
            badge.classList.toggle('badge-count-empty', count === 0);
        })
        .catch(() => { badge.textContent = '?'; });
}

// =========================================================================
// FILTRO DE TABLA
// =========================================================================

function initTableFilters() {
    const filtro = document.getElementById('filtro-tabla-estructura');
    if (!filtro) return;

    // Poblar opciones únicas de OT
    const rows = document.querySelectorAll('#tabla-estructura tbody tr[data-ot]');
    const ots  = [...new Set(Array.from(rows).map(r => r.dataset.ot))];
    ots.forEach(ot => {
        const opt = document.createElement('option');
        opt.value = ot; opt.textContent = ot;
        filtro.appendChild(opt);
    });

    filtro.addEventListener('change', () => {
        const val = filtro.value;
        rows.forEach(row => {
            row.style.display = (!val || row.dataset.ot === val) ? '' : 'none';
        });
    });
}

// =========================================================================
// ELIMINACIÓN DE CARPETAS
// =========================================================================

let folderToDelete = null;

window.confirmarEliminarCarpeta = function (p1, p2, p3, label) {
    folderToDelete = { p1, p2, p3 };
    const modal        = document.getElementById('dibujos-confirm-modal');
    const msgContainer = document.getElementById('confirm-message-container');
    const btnConfirm   = document.getElementById('btn-confirmar-borrar');

    if (modal && msgContainer) {
        let finalHtml = `Se va a eliminar permanentemente la carpeta:<br>
            <strong style="color: var(--cnc-green, #0a7c42); font-size: 1.1em;">${escapeHTML(label)}</strong>`;
        msgContainer.innerHTML = finalHtml;
        btnConfirm.textContent = 'Eliminar Permanentemente';
        modal.hidden = false;

        btnConfirm.onclick = () => {
            eliminarCarpetaAJAX(folderToDelete);
            cerrarConfirmarEliminar();
        };
    }
};

window.cerrarConfirmarEliminar = function () {
    const modal = document.getElementById('dibujos-confirm-modal');
    if (modal) modal.hidden = true;
    folderToDelete = null;
};

function eliminarCarpetaAJAX(folder) {
    let payload = {};
    let route   = window.routes['doc.deleteFolder'];

    if (folder.p3) {
        payload = { ot: folder.p1, clase: folder.p2, proceso: folder.p3 };
        route   = window.routes['doc.deleteFolder'];
    } else if (folder.p2) {
        payload = { ot: folder.p1, clase: folder.p2 };
        route   = window.routes['doc.deleteParent'];
    } else {
        mostrarNotificacion('No se puede determinar la carpeta a eliminar.', true);
        return;
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
            mostrarNotificacion(data.message || 'Carpeta eliminada correctamente.');
            setTimeout(() => window.location.reload(), 900);
        } else {
            mostrarNotificacion(data.message || 'No se pudo eliminar.', true);
        }
    })
    .catch(() => mostrarNotificacion('Error de conexión. Intente de nuevo.', true));
}

// =========================================================================
// LOG DE AUDITORÍA
// =========================================================================

function loadAuditLog() {
    const tbody = document.getElementById('tbody-log');
    if (!tbody) return;

    fetch(window.routes['doc.log'], { headers: { 'Accept': 'application/json' } })
    .then(r => { if (!r.ok) throw new Error(); return r.json(); })
    .then(data => {
        tbody.innerHTML = '';
        if (!data.logs || data.logs.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="d-text-center d-text-subtle" style="padding:1em;">Sin acciones registradas aun.</td></tr>';
            return;
        }
        const accionEs = {
            'crear_carpeta':       'Se ha creado una nueva carpeta en el sistema',
            'subir_pdf':           'Se subió un nuevo archivo PDF al servidor',
            'subir_programa':      'Se subió un nuevo programa CNC al servidor',
            'eliminar_pdf':        'Se eliminó definitivamente un archivo PDF',
            'eliminar_programa':   'Se eliminó definitivamente un programa CNC',
            'reemplazar_pdf':      'Se reemplazó un archivo existente por una nueva versión',
            'reemplazar_programa': 'Se reemplazó un programa CNC por una nueva versión',
            'eliminar_carpeta':    'Se eliminó la carpeta permanentemente',
            'vaciar_carpeta':      'Se eliminaron todos los archivos de la carpeta'
        };
        
        data.logs.forEach(log => {
            let actionLabel = accionEs[log.action] || log.action;

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
        if (tbody) tbody.innerHTML = '<tr><td colspan="5" class="d-text-center d-text-subtle" style="padding:1em;">Registro no disponible.</td></tr>';
    });
}

// =========================================================================
// UTILIDADES
// =========================================================================

function normalizeOTName(name) {
    if (!name) return '';
    let clean = name.replace(/[—–\xA0]/g, '-');
    clean = clean.toUpperCase();
    clean = clean.replace(/\s+/g, ' ');
    return clean.trim();
}

function slugify(text) {
    if (!text) return '';
    return text.toString().toLowerCase()
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/[\s-]+/g, '-')
        .replace(/^-+|-+$/g, '');
}

function escapeHTML(str) {
    if (!str) return '';
    return str.toString()
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function mostrarNotificacion(mensaje, esError = false) {
    const existente = document.querySelector('.dibujos-toast');
    if (existente) existente.remove();

    const toast = document.createElement('div');
    toast.className = 'dibujos-toast' + (esError ? ' error' : '');
    toast.innerHTML = `<span style="margin-right:8px;">${esError ? '❌' : '✅'}</span> ${escapeHTML(mensaje)}`;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.classList.add('fade-out');
        setTimeout(() => toast.remove(), 500);
    }, 4000);
}
