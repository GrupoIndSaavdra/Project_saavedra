/**
 * calidad_maquinados.js
 * Lógica de la vista de Calidad — Dibujos y Ayudas Visuales de Maquinados.
 * SOLO LECTURA.
 *
 * Lógica de filtrado por tabla:
 *   Dibujos   → filtran por OT  + Clase           (sin Proceso)
 *   Ayudas    → filtran por Clase + Proceso        (sin OT)
 *   Inactivos → filtran por OT  + Clase + Proceso  (todos)
 */

document.addEventListener('DOMContentLoaded', () => {
    initFiltrosReactivos();
    initToggleFiles();
});

// ── CONFIGURACIÓN DE TABLAS ───────────────────────────────────────────────────
// Cada entrada declara qué data-atributos deben coincidir para que la fila sea visible.
// null = ese filtro no aplica a esta tabla (la fila siempre pasa ese criterio).

const TABLAS = [
    {
        tbody   : 'calmaq-tbody-dibujos',
        count   : 'calmaq-count-dibujos',
        noMatch : 'calmaq-no-match-dibujos',
        usaOt   : true,
        usaClase: true,
        usaProc : false,   // ← Dibujos NO filtra por proceso
    },
    {
        tbody   : 'calmaq-tbody-ayudas',
        count   : 'calmaq-count-ayudas',
        noMatch : 'calmaq-no-match-ayudas',
        usaOt   : false,   // ← Ayudas NO filtra por OT
        usaClase: true,
        usaProc : true,
    },
    {
        tbody   : 'calmaq-tbody-inactivos',
        count   : 'calmaq-count-inactivos',
        noMatch : 'calmaq-no-match-inactivos',
        usaOt   : true,
        usaClase: true,
        usaProc : true,
    },
];

// ── TOGGLE FILAS DE ARCHIVOS ──────────────────────────────────────────────────

function initToggleFiles() {
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.btn-toggle-files');
        if (!btn) return;

        const targetId = btn.dataset.target;
        const filesRow = document.getElementById(targetId);

        if (!filesRow) return;

        const isOpen = filesRow.classList.contains('open');

        // Cerrar todos los demás del mismo tbody antes de abrir el nuevo (Comportamiento de Acordeón)
        if (!isOpen) {
            const tbody = filesRow.closest('tbody');
            if (tbody) {
                tbody.querySelectorAll('.alm-files-row.open').forEach(row => {
                    row.classList.remove('open');
                    row.classList.add("hidden");
                });
                tbody.querySelectorAll('.btn-toggle-files.active').forEach(b => {
                    b.classList.remove('active');
                    b.setAttribute('aria-expanded', 'false');
                    b.innerHTML = 'Ver Archivos';
                });
            }
        }

        if (isOpen) {
            filesRow.classList.remove('open');
            filesRow.classList.add("hidden");
            btn.classList.remove('active');
            btn.setAttribute('aria-expanded', 'false');
            btn.innerHTML = 'Ver Archivos';
        } else {
            filesRow.classList.add('open');
            filesRow.classList.remove("hidden");
            btn.classList.add('active');
            btn.setAttribute('aria-expanded', 'true');
            btn.innerHTML = 'Ocultar';
        }
    });
}

// ── INIT ──────────────────────────────────────────────────────────────────────

function initFiltrosReactivos() {
    const selOt      = document.getElementById('calmaq-ot');
    const selClase   = document.getElementById('calmaq-clase');
    const selProceso = document.getElementById('calmaq-proceso');
    const btnLimpiar = document.getElementById('calmaq-btn-limpiar');

    // Escuchar cambios
    [selOt, selClase, selProceso].forEach(sel => {
        if (sel) sel.addEventListener('change', aplicarFiltros);
    });

    // Botón limpiar
    if (btnLimpiar) {
        btnLimpiar.addEventListener('click', () => {
            if (selOt)      selOt.value      = '';
            if (selClase)   selClase.value   = '';
            if (selProceso) selProceso.value = '';

            // Si hay fechas activas, también limpiar (server redirect)
            const desde = document.getElementById('calmaq-desde');
            const hasta = document.getElementById('calmaq-hasta');
            if ((desde?.value) || (hasta?.value)) {
                window.location.href = window.calmaqRoutes?.indexBase ?? '/calidad/maquinados';
                return;
            }

            aplicarFiltros();
        });
    }

    // Aplicar al cargar (por si había filtros pre-seleccionados desde server)
    aplicarFiltros();
}

// ── FILTRADO ──────────────────────────────────────────────────────────────────

function aplicarFiltros() {
    const ot      = (document.getElementById('calmaq-ot')?.value      ?? '').trim();
    const clase   = (document.getElementById('calmaq-clase')?.value   ?? '').trim();
    const proceso = (document.getElementById('calmaq-proceso')?.value ?? '').trim();

    const hayFiltros = ot !== '' || clase !== '' || proceso !== '';
    const desde      = document.getElementById('calmaq-desde');
    const hasta      = document.getElementById('calmaq-hasta');
    const hayFecha   = (desde?.value ?? '') !== '' || (hasta?.value ?? '') !== '';

    // Mostrar / ocultar botón limpiar
    const btnLimpiar = document.getElementById('calmaq-btn-limpiar');
    if (btnLimpiar) {
        btnLimpiar.classList.toggle("hidden", !(hayFiltros || hayFecha));
    }

    // Aplicar a cada tabla según su configuración
    TABLAS.forEach(tabla => {
        const otEfectivo      = tabla.usaOt    ? ot      : '';
        const claseEfectiva   = tabla.usaClase ? clase   : '';
        const procesoEfectivo = tabla.usaProc  ? proceso : '';

        filtrarTabla(tabla, otEfectivo, claseEfectiva, procesoEfectivo);
    });
}

/**
 * Filtra visualmente las filas de una tabla.
 * Solo evalúa los atributos que la tabla usa según su config.
 */
function filtrarTabla(tabla, ot, clase, proceso) {
    const tbody   = document.getElementById(tabla.tbody);
    const noMatch = document.getElementById(tabla.noMatch);

    if (!tbody) return;

    const rows   = tbody.querySelectorAll('tr.calmaq-fila-doc');
    let visibles = 0;

    rows.forEach(row => {
        const ds = row.dataset;

        const matchOt      = !ot      || (ds.ot      ?? '') === ot;
        const matchClase   = !clase   || (ds.clase   ?? '') === clase;
        const matchProceso = !proceso || (ds.proceso ?? '') === proceso;

        const visible = matchOt && matchClase && matchProceso;
        row.classList.toggle("hidden", !(visible ));

        // Si la fila se oculta, también ocultamos su detalle expandido si lo tuviera
        const nextRow = row.nextElementSibling;
        if (nextRow && nextRow.classList.contains('alm-files-row')) {
            if (!visible) {
                nextRow.classList.add("hidden");
                nextRow.classList.remove('open');
                const btn = row.querySelector('.btn-toggle-files');
                if (btn) {
                    btn.classList.remove('active');
                    btn.innerHTML = 'Ver Archivos';
                    btn.setAttribute('aria-expanded', 'false');
                }
            }
        }

        if (visible) visibles++;
    });

    // Actualizar contador del header
    const badge = document.getElementById(tabla.count);
    if (badge) {
        badge.textContent = visibles + ' resultado' + (visibles !== 1 ? 's' : '');
    }

    // Mostrar panel vacío si todas las filas quedaron ocultas
    if (noMatch) {
        noMatch.classList.toggle("hidden", !(rows.length > 0 && visibles === 0));
    }
}

// ── VER ARCHIVO ───────────────────────────────────────────────────────────────

/**
 * Abre el archivo desde la ruta protegida del servidor usando el ID de BD.
 * @param {number} id
 */
window.calmaqVerArchivo = function (id) {
    if (!window.calmaqRoutes?.serve) {
        mostrarToast('Error: rutas del servidor no disponibles.', true);
        return;
    }
    window.open(
        window.calmaqRoutes.serve + '?id=' + encodeURIComponent(id),
        '_blank',
        'noopener,noreferrer'
    );
};

// ── TOAST ─────────────────────────────────────────────────────────────────────

function mostrarToast(mensaje, esError = false) {
    document.querySelector('.alm-toast')?.remove();

    const toast = document.createElement('div');
    toast.className = 'alm-toast' + (esError ? ' error' : '');
    toast.textContent = mensaje;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.classList.add('fade-out');
        setTimeout(() => toast.remove(), 450);
    }, 4000);
}
