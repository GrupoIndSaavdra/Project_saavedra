/**
 * herramientas_tecamac.js
 * Lógica de la vista Herramientas Tecamac.
 *
 * Perfiles:
 *   htEsAlmacen → modal completo CRUD: nueva herramienta, editar todo, fotos, inactivar, reactivar
 *   htEsAdmin   → modal simplificado: solo mínimo y máximo (updateStock)
 */

document.addEventListener('DOMContentLoaded', () => {
    initBusqueda();

    if (window.htEsAlmacen) {
        initModal();
        initConfirm();
    }
    if (window.htEsAdmin) {
        initStockModal();
    }
    initLightbox();
});

// ── CSRF ──────────────────────────────────────────────────────────────────────

function getCsrf() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

// ── BÚSQUEDA REACTIVA ─────────────────────────────────────────────────────────

function initBusqueda() {
    const input = document.getElementById('ht-search');
    if (!input) return;
    input.addEventListener('input', aplicarFiltros);
}

/** Filtra las filas de la tabla por nombre, descripción, inserto y proceso. */
function aplicarFiltros() {
    const term  = (document.getElementById('ht-search')?.value ?? '').trim().toLowerCase();
    const tbody = document.getElementById('ht-tbody');
    const count = document.getElementById('ht-count');
    const noRes = document.getElementById('ht-no-results');
    if (!tbody) return;

    const rows = tbody.querySelectorAll('tr.ht-row');
    let visible = 0;
    rows.forEach(row => {
        const searchData = row.dataset.search ?? '';
        const show = !term || searchData.includes(term);
        row.classList.toggle("herr-display-none", !(show ));
        if (show) visible++;
    });
    if (count) count.textContent = visible + ' resultado' + (visible !== 1 ? 's' : '');
    if (noRes) noRes.classList.toggle("herr-display-none", !(visible === 0 ));
}

// ── MODAL CRUD (solo Almacén) ─────────────────────────────────────────────────

let editingId        = null;
let imagenesEliminar = new Set();

const TIPOS_IMAGEN = ['herramienta', 'accesorio', 'tornilleria', 'tornilleria_accesorio', 'imagen_fisica'];

function initModal() {
    const overlay   = document.getElementById('ht-modal-overlay');
    const btnNuevo  = document.getElementById('ht-btn-nuevo');
    const btnClose  = document.getElementById('ht-modal-close');
    const btnCancel = document.getElementById('ht-btn-cancel');
    const btnSave   = document.getElementById('ht-btn-save');

    if (!overlay) return;

    if (btnNuevo)  btnNuevo.addEventListener('click', () => {
        if (!window.htEsAlmacen) return;
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
    TIPOS_IMAGEN.forEach(tipo => {
        const list = document.getElementById(`ht-imgs-${tipo}`);
        if (list) list.innerHTML = '';
    });

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

    // Checkboxes de proceso
    const allCbs = document.querySelectorAll('input[name="proceso[]"]');
    allCbs.forEach(cb => cb.checked = false);
    try {
        const procesos = JSON.parse(fila.dataset.proceso || '[]');
        procesos.forEach(proc => {
            const cb = document.querySelector(`input[name="proceso[]"][value="${CSS.escape(proc)}"]`);
            if (cb) cb.checked = true;
        });
    } catch (e) { /* no procesos */ }

    // Imágenes existentes
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
        reader.onload = e => { preview.src = e.target.result; preview.classList.remove("herr-display-none"); };
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.src = ''; preview.classList.add("herr-display-none");
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

// ── GUARDAR CRUD (Almacén) ────────────────────────────────────────────────────

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

// ── MODAL STOCK (Admin — solo mínimo y máximo) ────────────────────────────────

let stockEditingId = null;

function initStockModal() {
    const overlay = document.getElementById('ht-modal-stock-overlay');
    const close   = document.getElementById('ht-stock-close');
    const cancel  = document.getElementById('ht-stock-cancel');
    const save    = document.getElementById('ht-stock-save');
    if (!overlay) return;

    if (close)  close.addEventListener('click',  cerrarStockModal);
    if (cancel) cancel.addEventListener('click', cerrarStockModal);
    if (save)   save.addEventListener('click',   guardarStock);

    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) cerrarStockModal();
    });
}

window.htAbrirStock = function(id) {
    stockEditingId = id;
    const overlay  = document.getElementById('ht-modal-stock-overlay');
    const fila     = document.querySelector(`tr.ht-row[data-id="${id}"]`);
    if (!overlay) return;

    const elNombre   = document.getElementById('ht-stock-nombre');
    const elDesc     = document.getElementById('ht-stock-desc');
    const elCantidad = document.getElementById('ht-stock-cantidad');
    const elMinimo   = document.getElementById('ht-stock-minimo');
    const elMaximo   = document.getElementById('ht-stock-maximo');

    const nombre   = fila?.dataset.nombreHerramienta;
    const desc     = fila?.dataset.inserto || fila?.dataset.desc;
    const cantidad = fila?.dataset.cantidad;
    const minimo   = fila?.dataset.minimo;
    const maximo   = fila?.dataset.maximo;

    if (elNombre)   elNombre.textContent   = nombre   || '—';
    if (elDesc)     elDesc.textContent     = desc     || '—';
    if (elCantidad) elCantidad.textContent = cantidad !== undefined ? cantidad : '—';

    if (elMinimo) elMinimo.value = (minimo !== 'null' && minimo !== undefined && minimo !== '') ? minimo : '';
    if (elMaximo) elMaximo.value = (maximo !== 'null' && maximo !== undefined && maximo !== '') ? maximo : '';

    overlay.classList.add('open');
    document.body.style.overflow = 'hidden';
    if (elMinimo) setTimeout(() => elMinimo.focus(), 150);
};

function cerrarStockModal() {
    const overlay = document.getElementById('ht-modal-stock-overlay');
    if (!overlay) return;
    overlay.classList.remove('open');
    document.body.style.overflow = '';
    stockEditingId = null;
}

async function guardarStock() {
    const btnSave = document.getElementById('ht-stock-save');
    const minimo  = document.getElementById('ht-stock-minimo')?.value;
    const maximo  = document.getElementById('ht-stock-maximo')?.value;
    if (!stockEditingId) return;

    if (btnSave) {
        btnSave.disabled = true;
        btnSave.innerHTML = '<span class="ht-spinner"></span> Guardando…';
    }

    const url = (window.htRoutes?.updateStock ?? '/herramientas/tecamac/{id}/stock')
        .replace('{id}', stockEditingId);

    try {
        const body = {};
        if (minimo !== '') body.minimo = parseInt(minimo, 10);
        if (maximo !== '') body.maximo = parseInt(maximo, 10);

        const res = await fetch(url, {
            method : 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrf(),
                'Accept'      : 'application/json',
            },
            body: JSON.stringify(body),
        });

        let json;
        const ct = res.headers.get('content-type') ?? '';
        if (ct.includes('application/json')) {
            json = await res.json();
        } else {
            const text = await res.text();
            mostrarToast('Error del servidor: HTTP ' + res.status, true);
            console.error(text.substring(0, 500));
            return;
        }

        if (json.ok) {
            mostrarToast(json.message ?? 'Stock actualizado correctamente.');
            cerrarStockModal();
            setTimeout(() => window.location.reload(), 800);
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
        if (btnSave) {
            btnSave.disabled = false;
            btnSave.innerHTML = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg> Guardar Stock`;
        }
    }
}

// ── ELIMINAR / REACTIVAR (solo Almacén) ───────────────────────────────────────

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
