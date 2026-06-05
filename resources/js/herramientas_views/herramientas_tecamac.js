/**
 * herramientas_tecamac.js
 * Lógica de la vista Herramientas Tecamac.
 *
 * Perfiles:
 *   esCrud (1/5) → modal completo: nueva herramienta, editar todo, inactivar, reactivar
 *   esMM   (2)   → modal simplificado: solo mínimo y máximo
 */

document.addEventListener('DOMContentLoaded', () => {
    initBusqueda();
    initFiltroNombre();
    if (window.htEsCrud) {
        initModal();
        initConfirm();
    }
    initLightbox();
});

// ── CSRF ──────────────────────────────────────────────────────────────────────

function getCsrf() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

// ── BÚSQUEDA REACTIVA (descripción general) ───────────────────────────────────

function initBusqueda() {
    const input = document.getElementById('ht-search');
    if (!input) return;
    input.addEventListener('input', aplicarFiltros);
}

// ── FILTRO POR NOMBRE DE HERRAMIENTA ──────────────────────────────────────────

function initFiltroNombre() {
    const input = document.getElementById('ht-filter-nombre');
    if (!input) return;
    input.addEventListener('input', aplicarFiltros);
}

/** Aplica búsqueda + filtro de nombre simultáneamente sobre las filas. */
function aplicarFiltros() {
    const termGeneral = (document.getElementById('ht-search')?.value ?? '').trim().toLowerCase();
    const termNombre  = (document.getElementById('ht-filter-nombre')?.value ?? '').trim().toLowerCase();
    const tbody = document.getElementById('ht-tbody');
    const count = document.getElementById('ht-count');
    const noRes = document.getElementById('ht-no-results');
    if (!tbody) return;

    const rows = tbody.querySelectorAll('tr.ht-row');
    let visible = 0;
    rows.forEach(row => {
        const searchData  = row.dataset.search ?? '';
        const nombreData  = (row.dataset.nombreHerramienta ?? '').toLowerCase();
        const matchGeneral = !termGeneral || searchData.includes(termGeneral);
        const matchNombre  = !termNombre  || nombreData.includes(termNombre);
        const show = matchGeneral && matchNombre;
        row.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    if (count) count.textContent = visible + ' resultado' + (visible !== 1 ? 's' : '');
    if (noRes) noRes.style.display = visible === 0 ? 'block' : 'none';
}

// ── PREVIEW DE PROCESO EN MODAL ───────────────────────────────────────────────


// ── MODAL CRUD ────────────────────────────────────────────────────────────────

let editingId        = null;
let imagenesEliminar = new Set(); // IDs de imágenes a eliminar

const TIPOS_IMAGEN = ['herramienta', 'accesorio', 'tornilleria', 'tornilleria_accesorio', 'imagen_fisica'];

function initModal() {
    const overlay   = document.getElementById('ht-modal-overlay');
    const btnNuevo  = document.getElementById('ht-btn-nuevo');
    const btnClose  = document.getElementById('ht-modal-close');
    const btnCancel = document.getElementById('ht-btn-cancel');
    const btnSave   = document.getElementById('ht-btn-save');

    if (!overlay) return;

    if (btnNuevo)  btnNuevo.addEventListener('click', () => {
        if (!window.htEsAlta) return; // Solo Almacén puede crear
        abrirModal(null);
    });
    if (btnClose)  btnClose.addEventListener('click', cerrarModal);
    if (btnCancel) btnCancel.addEventListener('click', cerrarModal);
    if (btnSave)   btnSave.addEventListener('click', () => guardarHerramienta());

    overlay.addEventListener('click', (e) => { if (e.target === overlay) cerrarModal(); });
}

window.htAbrirEditar = function(id) { abrirModal(id); };

function abrirModal(id) {
    editingId        = id;
    imagenesEliminar = new Set();

    const overlay = document.getElementById('ht-modal-overlay');
    const title   = document.getElementById('ht-modal-title');
    const form    = document.getElementById('ht-form');
    if (!overlay || !form) return;

    form.reset();
    // Limpiar listas de imágenes
    TIPOS_IMAGEN.forEach(tipo => {
        const list = document.getElementById(`ht-imgs-${tipo}`);
        if (list) list.innerHTML = '';
    });

    // Desmarcar todos los checkboxes de proceso
    document.querySelectorAll('input[name="proceso[]"]').forEach(cb => cb.checked = false);

    if (id) {
        if (title) title.textContent = 'Editar Herramienta';
        cargarDatosEnModal(id);
    } else {
        if (title) title.textContent = 'Nueva Herramienta';
    }

    overlay.classList.add('open');
    document.body.style.overflow = 'hidden';
}

function cerrarModal() {
    const overlay = document.getElementById('ht-modal-overlay');
    if (!overlay) return;
    overlay.classList.remove('open');
    document.body.style.overflow = '';
    editingId = null;
}

function cargarDatosEnModal(id) {
    const fila = document.querySelector(`tr.ht-row[data-id="${id}"]`);
    if (!fila) return;

    const campos = {
        'ht-f-nombre'     : fila.dataset.nombreHerramienta,
        'ht-f-desc'       : fila.dataset.desc,
        'ht-f-inserto'    : fila.dataset.inserto,
        'ht-f-cantidad'   : fila.dataset.cantidad,
        'ht-f-profundidad': fila.dataset.profundidad,
        'ht-f-rpm'        : fila.dataset.rpm,
        'ht-f-avances'    : fila.dataset.avances,
        'ht-f-minimo'     : fila.dataset.minimo,
        'ht-f-maximo'     : fila.dataset.maximo,
    };
    Object.entries(campos).forEach(([fId, val]) => {
        const el = document.getElementById(fId);
        if (el && val !== undefined && val !== 'null') el.value = val;
    });

    // Cargar checkboxes de proceso (multi-selección)
    const allCbs = document.querySelectorAll('input[name="proceso[]"]');
    allCbs.forEach(cb => cb.checked = false);
    try {
        const procesos = JSON.parse(fila.dataset.proceso || '[]');
        procesos.forEach(proc => {
            const cb = document.querySelector(`input[name="proceso[]"][value="${CSS.escape(proc)}"]`);
            if (cb) cb.checked = true;
        });
    } catch (e) { /* no procesos */ }

    // Cargar imágenes existentes
    try {
        const imgs = JSON.parse(fila.dataset.imgs || '[]');
        imgs.forEach(img => agregarFilaImagenExistente(img));
    } catch (e) { console.error('Error parsing imgs', e); }
}

// ── GESTIÓN DE IMÁGENES EN MODAL ──────────────────────────────────────────────

window.htAgregarFoto = function(tipo) {
    const list = document.getElementById(`ht-imgs-${tipo}`);
    if (!list) return;

    const row = document.createElement('div');
    row.className = 'ht-img-row ht-img-row-new';
    row.innerHTML = `
        <div class="ht-img-row-preview-wrap">
            <img class="ht-img-mini-preview" src="" alt="" style="display:none">
        </div>
        <input type="file" name="img_${tipo}[]" accept="image/jpeg,image/png,image/webp"
               class="ht-img-file-input" onchange="htPreviewNuevaImg(this)">
        <input type="text" name="nom_${tipo}[]" placeholder="Nombre de la foto" class="ht-img-nombre-input">
        <button type="button" class="ht-img-remove-btn" onclick="this.closest('.ht-img-row').remove()" title="Quitar">✕</button>
    `;
    list.appendChild(row);
};

function agregarFilaImagenExistente(img) {
    const list = document.getElementById(`ht-imgs-${img.tipo}`);
    if (!list) return;

    const row = document.createElement('div');
    row.className = 'ht-img-row ht-img-row-existing';
    row.dataset.imgId = img.id;
    row.innerHTML = `
        <div class="ht-img-row-preview-wrap">
            <img class="ht-img-mini-preview" src="${img.url}" alt="${img.nombre ?? ''}" style="display:block">
        </div>
        <span class="ht-img-nombre-label">${img.nombre ?? '(sin nombre)'}</span>
        <button type="button" class="ht-img-remove-btn" onclick="htMarcarEliminar(this, ${img.id})" title="Eliminar imagen">🗑</button>
    `;
    list.appendChild(row);
}

window.htPreviewNuevaImg = function(input) {
    const preview = input.closest('.ht-img-row')?.querySelector('.ht-img-mini-preview');
    if (!preview) return;
    if (input.files?.[0]) {
        const reader = new FileReader();
        reader.onload = e => { preview.src = e.target.result; preview.style.display = 'block'; };
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.src = ''; preview.style.display = 'none';
    }
};

window.htMarcarEliminar = function(btn, imgId) {
    const row = btn.closest('.ht-img-row');
    if (imagenesEliminar.has(imgId)) {
        imagenesEliminar.delete(imgId);
        row.classList.remove('marcada-eliminar');
        btn.textContent = '🗑';
        btn.title = 'Eliminar imagen';
    } else {
        imagenesEliminar.add(imgId);
        row.classList.add('marcada-eliminar');
        btn.textContent = '↩';
        btn.title = 'Deshacer eliminación';
    }
};

// ── GUARDAR ───────────────────────────────────────────────────────────────────

async function guardarHerramienta() {
    const form    = document.getElementById('ht-form');
    const btnSave = document.getElementById('ht-btn-save');
    if (!form) return;

    if (btnSave) { btnSave.disabled = true; btnSave.innerHTML = '<span class="ht-spinner"></span> Guardando…'; }

    const formData = new FormData(form);
    imagenesEliminar.forEach(id => formData.append('delete_img_ids[]', id));

    const url = editingId
        ? (window.htRoutes?.update ?? '/herramientas/tecamac/{id}').replace('{id}', editingId)
        : (window.htRoutes?.store  ?? '/herramientas/tecamac');

    try {
        const res = await fetch(url, {
            method : 'POST',
            headers: {
                'X-CSRF-TOKEN': getCsrf(),
                'Accept'      : 'application/json',
            },
            body: formData,
        });

        let json;
        const contentType = res.headers.get('content-type') ?? '';
        if (contentType.includes('application/json')) {
            json = await res.json();
        } else {
            // El servidor devolvió HTML (error 500, 419 CSRF, etc.)
            const text = await res.text();
            const match = text.match(/<title>(.*?)<\/title>/i);
            mostrarToast('Error del servidor: ' + (match?.[1] ?? `HTTP ${res.status}`), true);
            console.error('Respuesta no-JSON del servidor:', text.substring(0, 500));
            return;
        }

        if (json.ok) {
            mostrarToast(json.message ?? 'Guardado correctamente.');
            cerrarModal();
            setTimeout(() => window.location.reload(), 900);
        } else {
            const err = json.errors
                ? Object.values(json.errors).flat().join(' | ')
                : (json.message ?? 'Error al guardar.');
            mostrarToast(err, true);
        }
    } catch (e) {
        mostrarToast('Error de conexión: ' + e.message, true);
        console.error(e);
    } finally {
        if (btnSave) { btnSave.disabled = false; btnSave.innerHTML = '💾 Guardar'; }
    }
}

// ── MODAL MIN/MAX (Producción) ─────────────────────────────────────────────────

let minmaxId = null;

function initMinMaxModal() {
    const overlay = document.getElementById('ht-minmax-overlay');
    const close   = document.getElementById('ht-minmax-close');
    const cancel  = document.getElementById('ht-minmax-cancel');
    const save    = document.getElementById('ht-minmax-save');
    if (!overlay) return;

    if (close)  close.addEventListener('click',  cerrarMinMax);
    if (cancel) cancel.addEventListener('click', cerrarMinMax);
    if (save)   save.addEventListener('click',   guardarMinMax);
    overlay.addEventListener('click', (e) => { if (e.target === overlay) cerrarMinMax(); });
}

window.htAbrirMinMax = function(id, minimo, maximo) {
    minmaxId = id;
    const overlay = document.getElementById('ht-minmax-overlay');
    const fila    = document.querySelector(`tr.ht-row[data-id="${id}"]`);
    if (!overlay) return;

    const nombre = fila?.dataset.desc ?? `Herramienta #${id}`;
    const label  = document.getElementById('ht-minmax-nombre');
    if (label) label.textContent = nombre;

    const mmMin = document.getElementById('ht-mm-minimo');
    const mmMax = document.getElementById('ht-mm-maximo');
    if (mmMin) mmMin.value = (minimo !== null && minimo !== undefined) ? minimo : '';
    if (mmMax) mmMax.value = (maximo !== null && maximo !== undefined) ? maximo : '';

    overlay.classList.add('open');
    document.body.style.overflow = 'hidden';
};

function cerrarMinMax() {
    const overlay = document.getElementById('ht-minmax-overlay');
    if (!overlay) return;
    overlay.classList.remove('open');
    document.body.style.overflow = '';
    minmaxId = null;
}

async function guardarMinMax() {
    const btnSave = document.getElementById('ht-minmax-save');
    const minimo  = document.getElementById('ht-mm-minimo')?.value;
    const maximo  = document.getElementById('ht-mm-maximo')?.value;

    if (!minmaxId) return;
    if (btnSave) { btnSave.disabled = true; btnSave.innerHTML = '<span class="ht-spinner"></span>'; }

    const formData = new FormData();
    formData.append('_method', 'POST');
    if (minimo !== '') formData.append('minimo', minimo);
    if (maximo !== '') formData.append('maximo', maximo);

    const url = (window.htRoutes?.update ?? '/herramientas/tecamac/{id}').replace('{id}', minmaxId);

    try {
        const res  = await fetch(url, {
            method : 'POST',
            headers: { 'X-CSRF-TOKEN': getCsrf() },
            body   : formData,
        });
        const json = await res.json();
        if (json.ok) {
            mostrarToast(json.message ?? 'Stock actualizado.');
            // Actualizar celda en tabla sin recargar
            const fila   = document.querySelector(`tr.ht-row[data-id="${minmaxId}"]`);
            const celdas = fila?.querySelectorAll('td');
            // Recarga para reflejar cambios visuales (stock bajo, etc.)
            cerrarMinMax();
            setTimeout(() => window.location.reload(), 700);
        } else {
            mostrarToast(json.message ?? 'Error.', true);
        }
    } catch (e) {
        mostrarToast('Error de conexión.', true);
    } finally {
        if (btnSave) { btnSave.disabled = false; btnSave.innerHTML = 'Guardar'; }
    }
}

// ── ELIMINAR / REACTIVAR ─────────────────────────────────────────────────────

let confirmCallback = null;

function initConfirm() {
    const overlay = document.getElementById('ht-confirm-overlay');
    const btnSi   = document.getElementById('ht-confirm-si');
    const btnNo   = document.getElementById('ht-confirm-no');
    if (!overlay) return;

    if (btnSi) btnSi.addEventListener('click', () => {
        overlay.classList.remove('open');
        document.body.style.overflow = '';
        if (confirmCallback) confirmCallback();
        confirmCallback = null;
    });
    if (btnNo) btnNo.addEventListener('click', () => {
        overlay.classList.remove('open');
        document.body.style.overflow = '';
        confirmCallback = null;
    });
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) {
            overlay.classList.remove('open');
            document.body.style.overflow = '';
            confirmCallback = null;
        }
    });
}

window.htEliminar = function(id, nombre) {
    const overlay = document.getElementById('ht-confirm-overlay');
    const titulo  = document.getElementById('ht-confirm-titulo');
    const desc    = document.getElementById('ht-confirm-desc');
    const btnSi   = document.getElementById('ht-confirm-si');
    if (!overlay) return;

    if (titulo) titulo.textContent = '¿Desactivar herramienta?';
    if (desc)   desc.textContent   = `"${nombre}" pasará a Inactivas. Puedes reactivarla después.`;
    if (btnSi)  { btnSi.style.background = '#9c0300'; btnSi.textContent = 'Sí, desactivar'; }

    overlay.classList.add('open');
    document.body.style.overflow = 'hidden';

    confirmCallback = async () => {
        const url = (window.htRoutes?.destroy ?? '/herramientas/tecamac/{id}').replace('{id}', id);
        try {
            const res  = await fetch(url, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': getCsrf(), 'Accept': 'application/json' } });
            const json = await res.json();
            if (json.ok) {
                mostrarToast(json.message ?? 'Herramienta desactivada.');
                const fila = document.querySelector(`tr.ht-row[data-id="${id}"]`);
                if (fila) { fila.style.transition = 'opacity 0.4s'; fila.style.opacity = '0'; setTimeout(() => fila.remove(), 450); }
            } else { mostrarToast(json.message ?? 'Error.', true); }
        } catch (e) { mostrarToast('Error de conexión.', true); }
    };
};

window.htReactivar = function(id, nombre) {
    const overlay = document.getElementById('ht-confirm-overlay');
    const titulo  = document.getElementById('ht-confirm-titulo');
    const desc    = document.getElementById('ht-confirm-desc');
    const btnSi   = document.getElementById('ht-confirm-si');
    if (!overlay) return;

    if (titulo) titulo.textContent = '¿Reactivar herramienta?';
    if (desc)   desc.textContent   = `"${nombre}" volverá al catálogo activo.`;
    if (btnSi)  { btnSi.style.background = '#027a3a'; btnSi.textContent = 'Sí, reactivar'; }

    overlay.classList.add('open');
    document.body.style.overflow = 'hidden';

    confirmCallback = async () => {
        const url = (window.htRoutes?.reactivar ?? '/herramientas/tecamac/{id}/reactivar').replace('{id}', id);
        try {
            const res  = await fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': getCsrf(), 'Accept': 'application/json' } });
            const json = await res.json();
            if (json.ok) {
                mostrarToast(json.message ?? 'Reactivada.');
                const fila = document.querySelector(`tr.ht-row[data-id="${id}"]`);
                if (fila) { fila.style.transition = 'opacity 0.4s'; fila.style.opacity = '0'; setTimeout(() => fila.remove(), 450); }
            } else { mostrarToast(json.message ?? 'Error.', true); }
        } catch (e) { mostrarToast('Error de conexión.', true); }
    };
};

// ── LIGHTBOX ─────────────────────────────────────────────────────────────────

function initLightbox() {
    const overlay  = document.getElementById('ht-lightbox-overlay');
    const btnClose = document.getElementById('ht-lightbox-close');
    const img      = document.getElementById('ht-lightbox-img');
    if (!overlay) return;

    [btnClose, overlay].forEach(el => {
        if (!el) return;
        el.addEventListener('click', (e) => {
            if (e.target === overlay || e.currentTarget === btnClose) {
                overlay.classList.remove('open');
                document.body.style.overflow = '';
                setTimeout(() => { if (img) img.src = ''; }, 300);
            }
        });
    });
}

window.htVerImagen = function(url, titulo) {
    const overlay = document.getElementById('ht-lightbox-overlay');
    const img     = document.getElementById('ht-lightbox-img');
    const cap     = document.getElementById('ht-lightbox-caption');
    if (!overlay || !img) return;
    img.src = url; img.alt = titulo ?? 'Imagen';
    if (cap) cap.textContent = titulo ?? '';
    overlay.classList.add('open');
    document.body.style.overflow = 'hidden';
};

// ── TOAST ─────────────────────────────────────────────────────────────────────

function mostrarToast(mensaje, esError = false) {
    document.querySelector('.ht-toast')?.remove();
    const toast = document.createElement('div');
    toast.className = 'ht-toast' + (esError ? ' error' : '');
    toast.textContent = mensaje;
    document.body.appendChild(toast);
    setTimeout(() => { toast.classList.add('fade-out'); setTimeout(() => toast.remove(), 450); }, 4000);
}
