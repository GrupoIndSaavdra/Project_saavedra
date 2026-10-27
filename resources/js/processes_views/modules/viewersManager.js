/**
 * Módulo de Visores Documentales de Producción
 * Planos/Dibujos Técnicos, Programas CNC, Manuales y Ayudas Visuales
 */

// ────────────────────────────────────────────────────────────────────────────
// UTILIDADES COMPARTIDAS PARA VISORES (Botón Cerrar & Estados Vacíos)
// ────────────────────────────────────────────────────────────────────────────

/**
 * Crea un botón de cerrar estilizado y accesible con imagen o fallback vectorial
 * @param {Function} onClose Callback al hacer clic
 * @returns {HTMLElement} Contenedor .div-cerrar
 */
function _createCloseButton(onClose) {
    const divCerrar = document.createElement('div');
    divCerrar.className = 'div-cerrar';

    const btnCerrar = document.createElement('button');
    btnCerrar.className = 'btn-cerrar';
    btnCerrar.type = 'button';
    btnCerrar.title = 'Cerrar visor';
    btnCerrar.setAttribute('aria-label', 'Cerrar');
    btnCerrar.onclick = (e) => {
        e.stopPropagation();
        if (typeof onClose === 'function') onClose();
    };

    if (window.cerrarImgUrl) {
        const imgCerrar = document.createElement('img');
        imgCerrar.className = 'img-cerrar';
        imgCerrar.src = window.cerrarImgUrl;
        imgCerrar.alt = 'Cerrar';
        imgCerrar.onerror = () => {
            imgCerrar.remove();
            btnCerrar.innerHTML = `
                <div class="svg-cerrar-fallback">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </div>
            `;
        };
        btnCerrar.appendChild(imgCerrar);
    } else {
        btnCerrar.innerHTML = `
            <div class="svg-cerrar-fallback">
                <svg viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </div>
        `;
    }

    divCerrar.appendChild(btnCerrar);
    return divCerrar;
}

/**
 * Renderiza una tarjeta de estado vacío moderna, clara e informativa
 * @param {HTMLElement} containerDiv Contenedor donde se insertará
 * @param {Object} options Opciones de contenido
 * @param {string} options.title Título principal del estado
 * @param {string} options.message Descripción o recomendación
 * @param {'info'|'warning'} [options.type='warning'] Variante visual
 * @param {string} [options.badgeText] Mensaje secundario tipo badge
 */
function _renderEmptyState(containerDiv, { title, message, type = 'warning', badgeText = null }) {
    if (!containerDiv) return;
    containerDiv.innerHTML = '';

    const emptyBox = document.createElement('div');
    emptyBox.className = `prod-empty-state is-${type}`;

    const iconSvg = type === 'info'
        ? `<svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="16" x2="12" y2="12"></line>
            <line x1="12" y1="8" x2="12.01" y2="8"></line>
           </svg>`
        : `<svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
            <line x1="12" y1="9" x2="12" y2="13"></line>
            <line x1="12" y1="17" x2="12.01" y2="17"></line>
           </svg>`;

    emptyBox.innerHTML = `
        <div class="empty-icon-wrap">${iconSvg}</div>
        <h4 class="empty-title">${title}</h4>
        <p class="empty-desc">${message}</p>
        ${badgeText ? `<span class="empty-badge">${badgeText}</span>` : ''}
    `;

    containerDiv.appendChild(emptyBox);
}

// ────────────────────────────────────────────────────────────────────────────
// VISOR DE DIBUJOS / PLANOS PDF — Acceso del Operador
// ────────────────────────────────────────────────────────────────────────────

/** Abre el visor de dibujos/planos para una OT */
export function openDibujosViewer(otId = null, claseNombre = null) {
    const activeOT = otId || (window.arrayData && window.arrayData.workOrder ? window.arrayData.workOrder.split(' - ')[0] : '');
    const activeClase = claseNombre || (window.arrayData ? (window.arrayData.class || '') : '');

    const divOpacity = document.createElement('div');
    divOpacity.className = 'prod-viewer-portal';
    divOpacity.id = 'div-opacity-dibujos';

    // REGLA DE ORO: INICIO DE RANGO (OPCIÓN 1)
    const startTimeDoc = new Date().toLocaleTimeString('it-IT');

    const modal = document.createElement('div');
    modal.className = 'prod-viewer-modal';

    // Función para cerrar y loguear automáticamente
    const closeAndViewerLog = (action, details) => {
        const endTimeDoc = new Date().toLocaleTimeString('it-IT');
        window.logUserAction(action, details, {
            h_inicio: startTimeDoc,
            h_termino: endTimeDoc
        });
        divOpacity.remove();
    };

    // Seccion de Header (Titulo + Filtros)
    const headerDiv = document.createElement('div');
    headerDiv.className = 'prod-viewer-header';

    // Boton cerrar accesible y visible
    const divCerrar = _createCloseButton(() => closeAndViewerLog("Consulta Dibujos Técnicos", `El operador finalizó la revisión de planos.`));

    const titulo = document.createElement('h3');
    titulo.textContent = 'Visor de Planos / Dibujos';

    const navDiv = document.createElement('div');
    navDiv.classList.add("prod-nav-div");

    const selOTWrap = _dibujosSelectGroup('Orden de Trabajo', 'd-viewer-ot');
    const selClaseWrap = _dibujosSelectGroup('Clase', 'd-viewer-clase');

    navDiv.appendChild(selOTWrap);
    navDiv.appendChild(selClaseWrap);
    headerDiv.appendChild(divCerrar);
    headerDiv.appendChild(titulo);
    headerDiv.appendChild(navDiv);

    // Area de contenido (Scrollable)
    const contentDiv = document.createElement('div');
    contentDiv.id = 'viewer-content';
    contentDiv.className = 'prod-viewer-body';

    // Estado inicial amigable antes de seleccionar
    _renderEmptyState(contentDiv, {
        title: 'Selecciona una Orden de Trabajo y Clase',
        message: 'Usa los selectores superiores para explorar los planos técnicos y dibujos de ingeniería disponibles.',
        type: 'info'
    });

    modal.appendChild(headerDiv);
    modal.appendChild(contentDiv);
    divOpacity.appendChild(modal);
    document.body.appendChild(divOpacity);

    // Cerrar al hacer clic en fondo con logging
    divOpacity.addEventListener('click', (e) => {
        if (e.target === divOpacity) closeAndViewerLog("Consulta Dibujos Técnicos", `El operador finalizó la revisión de planos.`);
    });

    const selOT = document.getElementById('d-viewer-ot');
    const selClase = document.getElementById('d-viewer-clase');

    fetch(window.baseUrl + '/dibujos/estructura', {
        headers: { 'Accept': 'application/json' },
        cache: 'no-store'
    })
        .then(r => r.json())
        .then(estructura => {
            let exactActiveOT = activeOT;
            let otNumMatch = activeOT ? activeOT.match(/\d+/) : null;
            let otNum = otNumMatch ? otNumMatch[0] : activeOT;

            if (activeOT && !Object.keys(estructura).some(k => window.eq(k, activeOT))) {
                const foundKey = Object.keys(estructura).find(key => {
                    if (window.eq(key, activeOT)) return true;
                    if (otNum) {
                        const keyOtMatch = key.match(/\d+/);
                        if (keyOtMatch && keyOtMatch[0] === otNum) return true;
                        if (new RegExp(`\\b${otNum}\\b`).test(key)) return true;
                    }
                    return false;
                });
                if (foundKey) exactActiveOT = foundKey;
            } else {
                exactActiveOT = Object.keys(estructura).find(k => window.eq(k, activeOT)) || activeOT;
            }

            selOT.innerHTML = '<option value="">— Seleccionar OT —</option>';
            Object.keys(estructura).sort().forEach(ot => {
                let label = ot;
                if (window.workOrders && window.workOrders[ot] && window.workOrders[ot].moldura) {
                    label = `${ot} — ${window.workOrders[ot].moldura}`;
                }
                const opt = document.createElement('option');
                opt.value = ot;
                opt.textContent = label;
                if (window.eq(ot, exactActiveOT)) opt.selected = true;
                selOT.appendChild(opt);
            });
            selOT.disabled = false;

            const isMatchClase = (c1, c2) => {
                if (!c1 || !c2) return false;
                if (window.eq(c1, c2)) return true;
                const c1Clean = String(c1).replace(/^\d+\s*-\s*/, '').toLowerCase().trim();
                const c2Clean = String(c2).replace(/^\d+\s*-\s*/, '').toLowerCase().trim();
                if (c1Clean === c2Clean) return true;
                if (c1Clean.replace(/s$/, '') === c2Clean.replace(/s$/, '')) return true;
                return c1Clean.includes(c2Clean) || c2Clean.includes(c1Clean);
            };

            const updateClases = () => {
                const selOTVal = selOT.value;
                selClase.innerHTML = '<option value="">— Seleccionar Clase —</option>';
                if (selOTVal && estructura[selOTVal]) {
                    estructura[selOTVal].forEach(clase => {
                        const opt = document.createElement('option');
                        opt.value = clase;
                        opt.textContent = (clase === '--') ? 'Planos Generales (Raíz)' : clase;
                        selClase.appendChild(opt);
                    });
                }
                selClase.disabled = false;
            };

            selOT.addEventListener('change', () => {
                updateClases();
                if (selOT.value && selClase.value) {
                    const otText = selOT.options[selOT.selectedIndex].text;
                    _dibujosCargarArchivos(selOT.value, selClase.value, contentDiv, otText);
                } else if (selOT.value) {
                    _renderEmptyState(contentDiv, {
                        title: 'Selecciona una Clase',
                        message: `Selecciona la clase correspondiente para cargar los planos de ${selOT.options[selOT.selectedIndex].text}.`,
                        type: 'info'
                    });
                } else {
                    _renderEmptyState(contentDiv, {
                        title: 'Selecciona una Orden de Trabajo y Clase',
                        message: 'Usa los selectores superiores para explorar los planos técnicos y dibujos de ingeniería disponibles.',
                        type: 'info'
                    });
                }
            });

            selClase.addEventListener('change', () => {
                const ot = selOT.value;
                const clase = selClase.value;
                if (ot && clase) {
                    const otText = selOT.options[selOT.selectedIndex].text;
                    _dibujosCargarArchivos(ot, clase, contentDiv, otText);
                } else if (ot) {
                    _renderEmptyState(contentDiv, {
                        title: 'Selecciona una Clase',
                        message: `Selecciona la clase correspondiente para cargar los planos técnicos.`,
                        type: 'info'
                    });
                }
            });

            // Si hay OT activa identificable (por ejemplo, al abrir desde el proceso de producción),
            // preseleccionarla y cargar sus clases y planos automáticamente, pero manteniendo
            // ambos selects completamente desbloqueados para navegación libre:
            if (exactActiveOT && Object.keys(estructura).some(k => window.eq(k, exactActiveOT))) {
                selOT.value = exactActiveOT;
                updateClases();

                // Buscar clase correspondiente al proceso actual
                let opt = Array.from(selClase.options).find(o => isMatchClase(o.value, activeClase));

                // Si no coincide por nombre pero hay clases disponibles, preseleccionar la primera opción válida
                if (!opt && selClase.options.length === 2) {
                    opt = selClase.options[1];
                } else if (!opt && Array.from(selClase.options).some(o => o.value === '--')) {
                    opt = Array.from(selClase.options).find(o => o.value === '--');
                } else if (!opt && selClase.options.length > 1) {
                    opt = selClase.options[1];
                }

                if (opt && opt.value) {
                    opt.selected = true;
                    const otText = selOT.options[selOT.selectedIndex].text;
                    _dibujosCargarArchivos(exactActiveOT, opt.value, contentDiv, otText);
                } else {
                    _renderEmptyState(contentDiv, {
                        title: 'Selecciona una Clase',
                        message: `Selecciona la clase de ${selOT.options[selOT.selectedIndex]?.text || exactActiveOT} para visualizar los planos técnicos.`,
                        type: 'info'
                    });
                }
            } else {
                updateClases();
            }
        })
        .catch((err) => {
            console.error(err);
            _renderEmptyState(contentDiv, {
                title: 'Error al Cargar Estructura de Planos',
                message: 'No fue posible consultar el directorio de dibujos técnicos: ' + err.message,
                type: 'warning'
            });
        });
};

/** Crea un grupo de select con label para el visor */
function _dibujosSelectGroup(labelText, selectId) {
    const wrap = document.createElement('div');
    wrap.classList.add("prod-filter-wrap");

    const label = document.createElement('label');
    label.htmlFor = selectId;
    label.textContent = labelText;
    label.className = 'form-label';
    label.classList.add("prod-filter-label");

    const select = document.createElement('select');
    select.id = selectId;
    select.className = 'form-control';
    select.classList.add("prod-filter-select");
    select.innerHTML = `<option value="">— ${labelText} —</option>`;

    wrap.appendChild(label);
    wrap.appendChild(select);
    return wrap;
}

/** Carga y renderiza los archivos PDF de la carpeta OT/Clase indicada */
function _dibujosCargarArchivos(ot, clase, contentDiv, otText = '') {
    contentDiv.innerHTML = '<p style="color:#666;text-align:center;">Cargando archivos...</p>';
    // Obtener el nombre completo de la OT de la lista de opciones si está disponible
    const selOTViewer = document.getElementById('d-viewer-ot');
    const otDisplay = selOTViewer ? selOTViewer.options[selOTViewer.selectedIndex].text : (otText || ot);
    window.logUserAction("Consulta Dibujos Técnicos", `El operador revisó los dibujos técnicos de ${otDisplay} - ${clase}`);

    const url = `${window.baseUrl}/dibujos/archivos?ot=${encodeURIComponent(ot)}&clase=${encodeURIComponent(clase)}`;
    fetch(url, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            if (!data.existe || !data.archivos || data.archivos.length === 0) {
                _dibujosShowEmpty(contentDiv, otDisplay, clase);
                return;
            }
            _dibujosRenderArchivos(data.archivos, otDisplay, clase, contentDiv);
        })
        .catch(() => _dibujosShowEmpty(contentDiv, otDisplay, clase));
}

/** Renderiza la cuadricula de tarjetas de archivos PDF con optimización de rendimiento */
function _dibujosRenderArchivos(archivos, otDisplay, clase, contentDiv) {
    contentDiv.innerHTML = '';

    const breadcrumb = document.createElement('div');
    breadcrumb.className = 'prod-viewer-breadcrumb';
    breadcrumb.innerHTML = `
        <span class="path-label">DOCUMENTACION_GIS</span>
        <span class="path-ot">${otDisplay}</span>
        <span style="opacity: 0.5;">/</span>
        <span class="path-clase">${clase}</span>
        <span class="path-count">${archivos.length} PDFs</span>
    `;
    contentDiv.appendChild(breadcrumb);

    const grid = document.createElement('div');
    grid.className = 'prod-viewer-grid';
    contentDiv.appendChild(grid);

    // Optimizacion: Renderizado por lotes (Chunks) para evitar lag
    const CHUNK_SIZE = 8;
    let currentIndex = 0;

    function renderNextBatch() {
        const end = Math.min(currentIndex + CHUNK_SIZE, archivos.length);

        for (let i = currentIndex; i < end; i++) {
            const archivo = archivos[i];
            const card = document.createElement('div');
            card.className = 'prod-viewer-card';
            // Delay escalonado relativo al inicio del lote para maxima fluidez
            card.style.animationDelay = `${(i % CHUNK_SIZE) * 0.05}s`;

            card.innerHTML = `
                <div class="file-icon-wrapper">
                    <img src="${window.baseUrl}/images/Shadow_Icons/PDF_View-Shadow.png" class="prod-viewer-icon icon-default">
                    <img src="${window.baseUrl}/images/Color_Icons/Generales/pdf-view.png" class="prod-viewer-icon icon-hover">
                </div>
                <div class="prod-viewer-filename">${archivo.nombre}</div>
                <div class="prod-viewer-action">Clic para abrir</div>`;

            card.onclick = () => window.open(archivo.url, '_blank');
            grid.appendChild(card);
        }

        currentIndex = end;
        if (currentIndex < archivos.length) {
            requestAnimationFrame(renderNextBatch);
        }
    }

    requestAnimationFrame(renderNextBatch);
}

/** Muestra el estado vacio cuando no hay archivos PDF disponibles. */
function _dibujosShowEmpty(contentDiv, otDisplay = '', clase = '') {
    const contextLabel = otDisplay && clase ? `${otDisplay} - ${clase}` : (otDisplay || clase || 'esta categoría');
    _renderEmptyState(contentDiv, {
        title: 'Sin Planos Técnicos Disponibles',
        message: `No se encontraron planos técnicos o dibujos PDF registrados para ${contextLabel}.`,
        type: 'warning',
        badgeText: 'Favor de reportar con el departamento de Programación CNC o Ingeniería'
    });
    if (typeof window.toastpremium === 'function') {
        window.toastpremium('No hay planos disponibles en esta categoría.', 'warning');
    }
}

// ────────────────────────────────────────────────────────────────────────────
// VISOR DE PROGRAMAS CNC — Acceso del Operador
// ────────────────────────────────────────────────────────────────────────────

export function openProgramasViewer(otId = null, claseNombre = null, procesoNombre = null) {
    const activeOT = otId || (window.arrayData && window.arrayData.workOrder ? window.arrayData.workOrder.split(' - ')[0] : '');
    const activeClase = claseNombre || (window.arrayData ? (window.arrayData.class || '') : '');
    const activeProceso = procesoNombre || (window.arrayData ? (window.arrayData.process || '') : '');

    const divOpacity = document.createElement('div');
    divOpacity.className = 'prod-viewer-portal';
    divOpacity.id = 'div-opacity-programas';

    // REGLA DE ORO: INICIO DE RANGO (OPCIÓN 1)
    const startTimeDoc = new Date().toLocaleTimeString('it-IT');

    const modal = document.createElement('div');
    modal.className = 'prod-viewer-modal';

    const closeAndViewerLog = (action, details) => {
        const endTimeDoc = new Date().toLocaleTimeString('it-IT');
        window.logUserAction(action, details, { h_inicio: startTimeDoc, h_termino: endTimeDoc });
        divOpacity.remove();
    };

    const headerDiv = document.createElement('div');
    headerDiv.className = 'prod-viewer-header';

    const divCerrar = _createCloseButton(() => closeAndViewerLog("Consulta Programas CNC", `El operador finalizó la revisión de programas.`));

    const titulo = document.createElement('h3');
    titulo.textContent = 'Visor de Programas CNC';

    const navDiv = document.createElement('div');
    navDiv.classList.add("prod-nav-div");

    const selOTWrap = _dibujosSelectGroup('Orden de Trabajo', 'p-viewer-ot');
    const selClaseWrap = _dibujosSelectGroup('Clase', 'p-viewer-clase');
    const selProcesoWrap = _dibujosSelectGroup('Proceso', 'p-viewer-proceso');

    navDiv.appendChild(selOTWrap);
    navDiv.appendChild(selClaseWrap);
    navDiv.appendChild(selProcesoWrap);

    headerDiv.appendChild(divCerrar);
    headerDiv.appendChild(titulo);
    headerDiv.appendChild(navDiv);

    const contentDiv = document.createElement('div');
    contentDiv.id = 'viewer-content-programas';
    contentDiv.className = 'prod-viewer-body';

    // Estado inicial amigable antes de seleccionar
    _renderEmptyState(contentDiv, {
        title: 'Selecciona los Parámetros del Programa',
        message: 'Selecciona la Orden de Trabajo, Clase y Proceso para visualizar los programas CNC de maquinado.',
        type: 'info'
    });

    modal.appendChild(headerDiv);
    modal.appendChild(contentDiv);
    divOpacity.appendChild(modal);
    document.body.appendChild(divOpacity);

    divOpacity.addEventListener('click', (e) => {
        if (e.target === divOpacity) closeAndViewerLog("Consulta Programas CNC", `El operador finalizó la revisión de programas.`);
    });

    const selOT = document.getElementById('p-viewer-ot');
    const selClase = document.getElementById('p-viewer-clase');
    const selProceso = document.getElementById('p-viewer-proceso');

    fetch(window.baseUrl + '/programas/estructura', { headers: { 'Accept': 'application/json' }, cache: 'no-store' })
        .then(r => r.json())
        .then(estructura => {
            let exactActiveOT = activeOT;
            let otNumMatch = activeOT ? activeOT.match(/\d+/) : null;
            let otNum = otNumMatch ? otNumMatch[0] : activeOT;

            if (activeOT && !Object.keys(estructura).some(k => window.eq(k, activeOT))) {
                const foundKey = Object.keys(estructura).find(key => key.includes("OT " + otNum) || window.eq(key, activeOT));
                if (foundKey) exactActiveOT = foundKey;
            } else {
                exactActiveOT = Object.keys(estructura).find(k => window.eq(k, activeOT)) || activeOT;
            }

            selOT.innerHTML = '<option value="">— Seleccionar OT —</option>';
            Object.keys(estructura).sort().forEach(ot => {
                let label = ot;
                if (window.workOrders && window.workOrders[ot] && window.workOrders[ot].moldura) {
                    label = `${ot} — ${window.workOrders[ot].moldura}`;
                }
                const opt = document.createElement('option');
                opt.value = ot;
                opt.textContent = label;
                if (window.eq(ot, exactActiveOT)) opt.selected = true;
                selOT.appendChild(opt);
            });

            const isMatchClase = (c1, c2) => {
                if (!c1 || !c2) return false;
                if (window.eq(c1, c2)) return true;
                const c1Clean = String(c1).replace(/^\d+\s*-\s*/, '').toLowerCase().trim();
                const c2Clean = String(c2).replace(/^\d+\s*-\s*/, '').toLowerCase().trim();
                if (c1Clean === c2Clean) return true;
                if (c1Clean.replace(/s$/, '') === c2Clean.replace(/s$/, '')) return true;
                return c1Clean.includes(c2Clean) || c2Clean.includes(c1Clean);
            };

            const updateClases = () => {
                const ot = selOT.value;
                selClase.innerHTML = '<option value="">— Seleccionar Clase —</option>';
                selProceso.innerHTML = '<option value="">— Seleccionar Proceso —</option>';
                selProceso.disabled = true;

                if (ot && estructura[ot]) {
                    Object.keys(estructura[ot]).forEach(clase => {
                        const opt = document.createElement('option');
                        opt.value = clase;
                        opt.textContent = clase;
                        if (isMatchClase(clase, activeClase)) opt.selected = true;
                        selClase.appendChild(opt);
                    });
                    selClase.disabled = false;
                } else {
                    selClase.disabled = true;
                }
            };

            const updateProcesos = () => {
                const ot = selOT.value;
                const clase = selClase.value;
                selProceso.innerHTML = '<option value="">— Seleccionar Proceso —</option>';

                if (ot && clase && estructura[ot] && estructura[ot][clase]) {
                    estructura[ot][clase].forEach(proc => {
                        const opt = document.createElement('option');
                        opt.value = proc;
                        opt.textContent = proc;
                        if (window.eq(proc, activeProceso)) opt.selected = true;
                        selProceso.appendChild(opt);
                    });
                    selProceso.disabled = false;
                } else {
                    selProceso.disabled = true;
                }
            };

            selOT.addEventListener('change', () => {
                updateClases();
                updateProcesos();
                if (selOT.value && selClase.value && selProceso.value) {
                    const otText = selOT.options[selOT.selectedIndex]?.text || selOT.value;
                    _programasCargarArchivos(selOT.value, selClase.value, selProceso.value, contentDiv, otText);
                } else {
                    _renderEmptyState(contentDiv, {
                        title: 'Seleccione los Parámetros del Programa',
                        message: 'Complete la selección de Orden de Trabajo, Clase y Proceso para listar los programas CNC.',
                        type: 'info'
                    });
                }
            });

            selClase.addEventListener('change', () => {
                updateProcesos();
                if (selOT.value && selClase.value && selProceso.value) {
                    const otText = selOT.options[selOT.selectedIndex]?.text || selOT.value;
                    _programasCargarArchivos(selOT.value, selClase.value, selProceso.value, contentDiv, otText);
                } else {
                    _renderEmptyState(contentDiv, {
                        title: 'Selecciona un Proceso',
                        message: 'Seleccione el proceso para listar los programas CNC correspondientes.',
                        type: 'info'
                    });
                }
            });

            selProceso.addEventListener('change', () => {
                if (selOT.value && selClase.value && selProceso.value) {
                    const otText = selOT.options[selOT.selectedIndex]?.text || selOT.value;
                    _programasCargarArchivos(selOT.value, selClase.value, selProceso.value, contentDiv, otText);
                }
            });

            // Carga inicial automática
            if (exactActiveOT && Object.keys(estructura).some(k => window.eq(k, exactActiveOT))) {
                selOT.value = exactActiveOT;
                updateClases();
                const optClase = Array.from(selClase.options).find(o => isMatchClase(o.value, activeClase));
                if (optClase) {
                    optClase.selected = true;
                    updateProcesos();
                    const optProc = Array.from(selProceso.options).find(o => window.eq(o.value, activeProceso));
                    if (optProc) {
                        optProc.selected = true;
                        const otText = selOT.options[selOT.selectedIndex]?.text || exactActiveOT;
                        _programasCargarArchivos(exactActiveOT, optClase.value, optProc.value, contentDiv, otText);
                    }
                }
            }

            // Bloquear los filtros para que los operadores no puedan cambiarlos
            selOT.disabled = true;
            selClase.disabled = true;
            selProceso.disabled = true;
        })
        .catch((err) => {
            _renderEmptyState(contentDiv, {
                title: 'Error al Cargar Programas CNC',
                message: 'No fue posible consultar la estructura de programas CNC: ' + err.message,
                type: 'warning'
            });
        });
};

function _programasCargarArchivos(ot, clase, proceso, contentDiv, otText = '') {
    contentDiv.innerHTML = '<p style="color:#666;text-align:center;">Cargando programas...</p>';
    const url = `${window.baseUrl}/programas/archivos?ot=${encodeURIComponent(ot)}&clase=${encodeURIComponent(clase)}&proceso=${encodeURIComponent(proceso)}`;

    fetch(url, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            if (!data.existe || !data.archivos || data.archivos.length === 0) {
                _renderEmptyState(contentDiv, {
                    title: 'Sin Programas CNC Registrados',
                    message: `No se encontraron programas de maquinado (.NC / CNC) para el proceso "${proceso}" en la OT ${otText || ot} (${clase}).`,
                    type: 'warning',
                    badgeText: 'Favor de solicitar su carga al departamento de Programación CNC'
                });
                return;
            }

            contentDiv.innerHTML = '';

            const breadcrumb = document.createElement('div');
            breadcrumb.className = 'prod-viewer-breadcrumb';
            breadcrumb.innerHTML = `
                <span class="path-label">PROGRAMAS_MAQUINADOS</span>
                <span class="path-ot">${otText || ot}</span> <span style="opacity:0.5">/</span>
                <span class="path-clase">${clase}</span> <span style="opacity:0.5">/</span>
                <span class="path-proceso">${proceso}</span>
                <span class="path-count">${data.archivos.length} PROG</span>
            `;
            contentDiv.appendChild(breadcrumb);

            const grid = document.createElement('div');
            grid.className = 'prod-viewer-grid';
            contentDiv.appendChild(grid);

            data.archivos.forEach((archivo, i) => {
                const extUpper = String(archivo.extension || '').toUpperCase();
                const isNC     = extUpper === 'NC';

                const iconDefault = isNC
                    ? (window.imgProgramsNCShadow || (window.baseUrl + '/images/Shadow_Icons/ProgramsNC-Shadow.png'))
                    : (window.imgProgramsCNCShadow || (window.baseUrl + '/images/Shadow_Icons/ProgramsCNC-Shadow.png'));

                const iconHover = isNC
                    ? (window.imgProgramsNC || (window.baseUrl + '/images/Color_Icons/Generales/ProgramsNC.png'))
                    : (window.imgProgramsCNC || (window.baseUrl + '/images/Color_Icons/Generales/ProgramsCNC.png'));

                const card = document.createElement('div');
                card.className = 'prod-viewer-card card-programas';
                card.style.animationDelay = `${(i % 8) * 0.05}s`;
                card.innerHTML = `
                    <div class="file-icon-wrapper">
                        <img src="${iconDefault}" class="prod-viewer-icon icon-default">
                        <img src="${iconHover}" class="prod-viewer-icon icon-hover">
                    </div>
                    <div class="prod-viewer-filename" style="color:#0a7c42;">${archivo.nombre}</div>
                    <div class="prod-viewer-action">Clic para Descargar</div>`;
                card.onclick = () => window.open(archivo.url, '_blank');
                grid.appendChild(card);
            });
        })
        .catch(() => {
            _renderEmptyState(contentDiv, {
                title: 'Error de Comunicación',
                message: 'No fue posible conectar con el servidor para obtener los programas CNC.',
                type: 'warning'
            });
        });
}

// ────────────────────────────────────────────────────────────────────────────
// VISOR DE MANUALES DE PROCESOS — Acceso del Operador
// ────────────────────────────────────────────────────────────────────────────

export function openManualesViewer() {
    const activeProceso = window.arrayData ? window.arrayData.process : '';

    const divOpacity = document.createElement('div');
    divOpacity.className = 'prod-viewer-portal';
    divOpacity.id = 'div-opacity-manuales';

    // REGLA DE ORO: INICIO DE RANGO (OPCIÓN 1)
    const startTimeManual = new Date().toLocaleTimeString('it-IT');

    const modal = document.createElement('div');
    modal.className = 'prod-viewer-modal';

    // Función para cerrar y loguear automáticamente
    const closeManualLog = () => {
        const endTimeManual = new Date().toLocaleTimeString('it-IT');
        window.logUserAction("Consulta Documentación Técnica", "El operador finalizó la consulta de manuales de procesos.", {
            h_inicio: startTimeManual,
            h_termino: endTimeManual
        });
        divOpacity.remove();
    };

    const headerDiv = document.createElement('div');
    headerDiv.className = 'prod-viewer-header';

    const divCerrar = _createCloseButton(() => closeManualLog());

    const titulo = document.createElement('h3');
    titulo.textContent = 'Visor de Manuales de Procesos';

    const navDiv = document.createElement('div');
    navDiv.classList.add("prod-nav-div");

    const selProcesoWrap = _dibujosSelectGroup('Proceso', 'd-viewer-manuales-proceso');

    navDiv.appendChild(selProcesoWrap);

    headerDiv.appendChild(divCerrar);
    headerDiv.appendChild(titulo);
    headerDiv.appendChild(navDiv);

    const contentDiv = document.createElement('div');
    contentDiv.id = 'viewer-content-manuales';
    contentDiv.className = 'prod-viewer-body';

    // Estado inicial amigable antes de seleccionar proceso
    _renderEmptyState(contentDiv, {
        title: 'Selecciona un Proceso',
        message: 'Elija un proceso en el selector superior para consultar su manual técnico de operación y especificaciones.',
        type: 'info'
    });

    modal.appendChild(headerDiv);
    modal.appendChild(contentDiv);
    divOpacity.appendChild(modal);
    document.body.appendChild(divOpacity);

    divOpacity.addEventListener('click', (e) => {
        if (e.target === divOpacity) closeManualLog();
    });

    const selProceso = document.getElementById('d-viewer-manuales-proceso');

    fetch(window.baseUrl + '/manuales/estructura', {
        headers: { 'Accept': 'application/json' },
        cache: 'no-store'
    })
        .then(r => r.json())
        .then(estructura => {
            selProceso.innerHTML = '<option value="">— Seleccionar Proceso —</option>';
            estructura.sort().forEach(proc => {
                const opt = document.createElement('option');
                opt.value = proc;
                opt.textContent = proc;
                selProceso.appendChild(opt);
            });

            // Intentar seleccionar el proceso actual
            const optDefault = Array.from(selProceso.options).find(o => window.eq(o.value, activeProceso));
            if (optDefault) optDefault.selected = true;

            if (selProceso.value) {
                _manualesCargarArchivos(selProceso.value, contentDiv);
            }

            selProceso.addEventListener('change', () => {
                if (selProceso.value) {
                    _manualesCargarArchivos(selProceso.value, contentDiv);
                } else {
                    _renderEmptyState(contentDiv, {
                        title: 'Selecciona un Proceso',
                        message: 'Elija un proceso en el selector superior para consultar su manual técnico de operación.',
                        type: 'info'
                    });
                }
            });
        })
        .catch(() => {
            _renderEmptyState(contentDiv, {
                title: 'Error de Comunicación',
                message: 'No fue posible consultar la estructura de manuales técnicos.',
                type: 'warning'
            });
        });
};

function _manualesCargarArchivos(proceso, contentDiv) {
    contentDiv.innerHTML = '<p style="color:#666;text-align:center;">Cargando archivos...</p>';
    const url = `${window.baseUrl}/manuales/archivos?proceso=${encodeURIComponent(proceso)}`;
    fetch(url, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            if (!data.existe || !data.archivos || data.archivos.length === 0) {
                _renderEmptyState(contentDiv, {
                    title: 'Sin Manual Técnico Registrado',
                    message: `Actualmente no existe un manual técnico registrado para el proceso "${proceso}".`,
                    type: 'warning',
                    badgeText: 'Consulte con el área de Calidad o Ingeniería de Procesos'
                });
                return;
            }
            _dibujosRenderArchivos(data.archivos, proceso, 'General', contentDiv);
        })
        .catch(() => {
            _renderEmptyState(contentDiv, {
                title: 'Error de Comunicación',
                message: 'No fue posible consultar los archivos del manual técnico.',
                type: 'warning'
            });
        });
}

function _ayudasCargarArchivos(proceso, clase, contentDiv) {
    contentDiv.innerHTML = '<p style="color:#666;text-align:center;">Cargando archivos...</p>';
    const url = `${window.baseUrl}/ayudas/archivos?proceso=${encodeURIComponent(proceso)}`;
    fetch(url, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            if (!data.existe || !data.archivos || data.archivos.length === 0) {
                _renderEmptyState(contentDiv, {
                    title: 'Sin Ayudas Visuales Registradas',
                    message: `No se encontraron ayudas visuales registradas para el proceso "${proceso}".`,
                    type: 'warning',
                    badgeText: 'Consulte con el Supervisor de Turno o Calidad'
                });
                return;
            }
            _dibujosRenderArchivos(data.archivos, proceso, 'General', contentDiv);
        })
        .catch(() => {
            _renderEmptyState(contentDiv, {
                title: 'Error de Comunicación',
                message: 'No fue posible consultar los archivos de ayudas visuales.',
                type: 'warning'
            });
        });
}

// ────────────────────────────────────────────────────────────────────────────
// VISOR DE AYUDAS VISUALES — Acceso del Operador
// ────────────────────────────────────────────────────────────────────────────

export function openAyudasViewer() {
    const activeProceso = window.arrayData ? window.arrayData.process : '';
    const activeClase = window.arrayData ? (window.arrayData.class || '') : '';

    const divOpacity = document.createElement('div');
    divOpacity.className = 'prod-viewer-portal';
    divOpacity.id = 'div-opacity-ayudas';

    const modal = document.createElement('div');
    modal.className = 'prod-viewer-modal';

    const headerDiv = document.createElement('div');
    headerDiv.className = 'prod-viewer-header';

    const divCerrar = _createCloseButton(() => divOpacity.remove());

    const titulo = document.createElement('h3');
    titulo.textContent = 'Visor de Ayudas Visuales';

    const navDiv = document.createElement('div');
    navDiv.classList.add("prod-nav-div");

    const selProcesoWrap = _dibujosSelectGroup('Proceso', 'd-viewer-ayudas-proceso');

    navDiv.appendChild(selProcesoWrap);

    headerDiv.appendChild(divCerrar);
    headerDiv.appendChild(titulo);
    headerDiv.appendChild(navDiv);

    const contentDiv = document.createElement('div');
    contentDiv.id = 'viewer-content-ayudas';
    contentDiv.className = 'prod-viewer-body';

    // Estado inicial amigable antes de seleccionar proceso
    _renderEmptyState(contentDiv, {
        title: 'Selecciona un Proceso',
        message: 'Elija un proceso en el selector superior para consultar las ayudas visuales y criterios de calidad.',
        type: 'info'
    });

    modal.appendChild(headerDiv);
    modal.appendChild(contentDiv);
    divOpacity.appendChild(modal);
    document.body.appendChild(divOpacity);

    divOpacity.addEventListener('click', (e) => {
        if (e.target === divOpacity) divOpacity.remove();
    });

    const selProceso = document.getElementById('d-viewer-ayudas-proceso');

    fetch(window.baseUrl + '/ayudas/estructura', {
        headers: { 'Accept': 'application/json' },
        cache: 'no-store'
    })
        .then(r => r.json())
        .then(estructura => {
            let ayuProcs = [...new Set(Object.values(estructura).flat())];

            selProceso.innerHTML = '<option value="">— Seleccionar Proceso —</option>';
            ayuProcs.sort().forEach(proc => {
                const opt = document.createElement('option');
                opt.value = proc;
                opt.textContent = proc;
                selProceso.appendChild(opt);
            });
            selProceso.disabled = false;

            let matchingProcesoKey = ayuProcs.find(k => window.eq(k, activeProceso));
            if (activeProceso && matchingProcesoKey) {
                selProceso.value = matchingProcesoKey;
                selProceso.dispatchEvent(new Event('change'));
                setTimeout(() => {
                    if (selProceso.value) {
                        _ayudasCargarArchivos(selProceso.value, null, contentDiv);
                    }
                }, 50);
            }

            selProceso.addEventListener('change', () => {
                if (selProceso.value) {
                    _ayudasCargarArchivos(selProceso.value, null, contentDiv);
                } else {
                    _renderEmptyState(contentDiv, {
                        title: 'Selecciona un Proceso',
                        message: 'Elija un proceso en el selector superior para consultar sus ayudas visuales.',
                        type: 'info'
                    });
                }
            });
        })
        .catch(() => {
            _renderEmptyState(contentDiv, {
                title: 'Error de Comunicación',
                message: 'No fue posible consultar la estructura de ayudas visuales.',
                type: 'warning'
            });
        });
};

/**
 * Abre el modal técnico con pestañas para Manuales y Ayudas Visuales
 */
export function openTechDocsModal() {
    // Evitar abrir múltiples modales si se dan clics rápidos
    if (document.getElementById('tech-docs-overlay')) return;

    let activeProcess = window.arrayData ? window.arrayData["process"] || (window.arrayData["meta"] ? window.arrayData["meta"].proceso : null) : null;
    let activeClass = window.arrayData ? window.arrayData["class"] || (window.arrayData["meta"] ? window.arrayData["meta"].clase : null) : null;
    let activeOT = window.arrayData ? window.arrayData["ot_folio"] || window.arrayData["ot"] || (window.arrayData["meta"] ? window.arrayData["meta"].ot : null) : null;

    if (!activeProcess) {
        const procInput = document.getElementById("process");
        if (procInput && procInput.value) activeProcess = procInput.value;
    }
    if (!activeClass) {
        const classInput = document.getElementById("class");
        if (classInput && classInput.value) activeClass = classInput.value;
    }

    if (!activeProcess && !activeOT) {
        mostrarNotificacion("No se pudo identificar el contexto activo.", true);
        return;
    }

    // Loguear acceso a documentación enriquecido
    window.logUserAction("Consulta Documentación Técnica", `El operador consultó los manuales de proceso o ayudas visuales para el proceso ${activeProcess} de la clase ${activeClass || 'N/A'}`);

    const divOpacity = document.createElement('div');
    divOpacity.className = 'prod-viewer-portal';
    divOpacity.id = 'tech-docs-overlay';

    const modal = document.createElement('div');
    modal.className = 'prod-viewer-modal';

    const headerDiv = document.createElement('div');
    headerDiv.className = 'prod-viewer-header';

    const divCerrar = _createCloseButton(() => divOpacity.remove());

    const titulo = document.createElement('h3');
    titulo.textContent = 'Documentación Técnica';

    const topBar = document.createElement('div');
    topBar.classList.add("prod-top-bar");

    const tabsContainer = document.createElement('div');
    tabsContainer.className = 'tech-docs-tabs';
    tabsContainer.classList.add("prod-tabs-container");
    tabsContainer.innerHTML = `
        <button class="tech-tab-btn active" data-tab="manuales" style="flex:1; padding: 0.8em; font-size: 0.9em;">Manuales</button>
        <button class="tech-tab-btn" data-tab="ayudas" style="flex:1; padding: 0.8em; font-size: 0.9em;">Ayudas Visuales</button>
    `;

    const navDiv = document.createElement('div');
    navDiv.classList.add("prod-nav-div-flex2");

    // Solo necesitamos Proceso
    const selProcesoWrap = _dibujosSelectGroup('Proceso', 'tech-doc-proceso');

    navDiv.appendChild(selProcesoWrap);

    topBar.appendChild(tabsContainer);
    topBar.appendChild(navDiv);

    headerDiv.appendChild(divCerrar);
    headerDiv.appendChild(titulo);
    headerDiv.appendChild(topBar);

    const contentDiv = document.createElement('div');
    contentDiv.id = 'viewer-content-tech-docs';
    contentDiv.className = 'prod-viewer-body';

    modal.appendChild(headerDiv);
    modal.appendChild(contentDiv);
    divOpacity.appendChild(modal);
    document.body.appendChild(divOpacity);

    let activeTab = 'manuales';
    let manualEstructura = [];
    let ayudasEstructura = {};

    const selProceso = document.getElementById('tech-doc-proceso');

    divOpacity.addEventListener('click', (e) => {
        if (e.target === divOpacity) divOpacity.remove();
    });

    contentDiv.innerHTML = '<p style="color:#666;text-align:center;">Cargando estructuras...</p>';
    Promise.all([
        fetch(window.baseUrl + '/manuales/estructura').then(r => r.json()),
        fetch(window.baseUrl + '/ayudas/estructura').then(r => r.json())
    ]).then(([manData, ayuData]) => {
        manualEstructura = Array.isArray(manData) ? manData : Object.keys(manData);
        ayudasEstructura = ayuData;

        let matchedProc = activeProcess;
        let found = manualEstructura.find(p => window.eq(p, matchedProc));

        if (!found && activeProcess && activeProcess.includes('_')) {
            matchedProc = activeProcess.split('_')[0];
            found = manualEstructura.find(p => window.eq(p, matchedProc));
            if (!found) {
                matchedProc = activeProcess.split('_')[1];
                found = manualEstructura.find(p => window.eq(p, matchedProc));
            }
        }

        if (found) {
            matchedProc = found;
        }

        updateManualesSelect(matchedProc);
        _triggerSearch();

    }).catch(err => {
        console.error(err);
        _renderEmptyState(contentDiv, {
            title: 'Error de Comunicación',
            message: 'No fue posible consultar la estructura de documentos técnicos: ' + err.message,
            type: 'warning'
        });
    });

    function _triggerSearch() {
        const proc = selProceso.value;

        if (activeTab === 'manuales') {
            if (proc) _techDocsCargarArchivos('manuales', proc, null, contentDiv);
            else _techDocsShowEmpty(contentDiv, "Seleccione un Proceso.");
        } else if (activeTab === 'ayudas') {
            if (proc) _techDocsCargarArchivos('ayudas', proc, null, contentDiv);
            else _techDocsShowEmpty(contentDiv, "Seleccione un Proceso.");
        }
    }

    function updateManualesSelect(selectedProc = null) {
        selProceso.innerHTML = '<option value="">— Seleccionar Proceso —</option>';
        manualEstructura.sort().forEach(proc => {
            const opt = document.createElement('option');
            opt.value = proc;
            opt.textContent = proc;
            if (proc === selectedProc) opt.selected = true;
            selProceso.appendChild(opt);
        });
        selProceso.disabled = false;
    }
    function updateAyudasFilters(procVal = null) {
        let ayuProcs = [...new Set(Object.values(ayudasEstructura).flat())];

        selProceso.innerHTML = '<option value="">— Seleccionar Proceso —</option>';
        ayuProcs.sort().forEach(p => {
            const opt = document.createElement('option');
            opt.value = p;
            opt.textContent = p;
            if (p === procVal) opt.selected = true;
            selProceso.appendChild(opt);
        });
        selProceso.disabled = false;
    }

    selProceso.addEventListener('change', () => {
        _triggerSearch();
    });

    const tabs = tabsContainer.querySelectorAll('.tech-tab-btn');
    tabs.forEach(tab => {
        tab.onclick = () => {
            try {
                tabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                activeTab = tab.dataset.tab;

                // Ocultar todos (ya solo queda proceso)
                selProcesoWrap.hidden = true;

                if (activeTab === 'manuales') {
                    selProcesoWrap.hidden = false;
                    let matchedProc = activeProcess;
                    let found = manualEstructura.find(p => window.eq(p, matchedProc));
                    if (!found && activeProcess && activeProcess.includes('_')) {
                        matchedProc = activeProcess.split('_')[0];
                        found = manualEstructura.find(p => window.eq(p, matchedProc));
                        if (!found) {
                            matchedProc = activeProcess.split('_')[1];
                            found = manualEstructura.find(p => window.eq(p, matchedProc));
                        }
                    }
                    if (found) matchedProc = found;
                    updateManualesSelect(matchedProc);
                } else if (activeTab === 'ayudas') {
                    selProcesoWrap.hidden = false;
                    let matchedProc = activeProcess;
                    let ayuProcs = [...new Set(Object.values(ayudasEstructura).flat())];

                    let found = ayuProcs.find(p => window.eq(p, matchedProc));
                    if (!found && activeProcess && activeProcess.includes('_')) {
                        matchedProc = activeProcess.split('_')[0];
                        found = ayuProcs.find(p => window.eq(p, matchedProc));
                        if (!found) {
                            matchedProc = activeProcess.split('_')[1];
                            found = ayuProcs.find(p => window.eq(p, matchedProc));
                        }
                    }
                    if (found) matchedProc = found;
                    updateAyudasFilters(matchedProc);
                }
                _triggerSearch();
            } catch (err) {
                toastpremium("Error al cambiar pestaña: " + err.message, "error");
                console.error(err);
            }
        };
    });

    function _techDocsShowEmpty(containerDiv, msg = null) {
        const proc = selProceso ? selProceso.value : null;
        if (!proc || (msg && msg.includes('Seleccione'))) {
            _renderEmptyState(containerDiv, {
                title: 'Seleccione un Proceso',
                message: msg || 'Selecciona un proceso en el menú superior para visualizar la documentación técnica disponible.',
                type: 'info'
            });
        } else {
            const isManual = activeTab === 'manuales';
            _renderEmptyState(containerDiv, {
                title: isManual ? 'Sin Manual Técnico Registrado' : 'Sin Ayudas Visuales Registradas',
                message: msg || (isManual
                    ? `No se encontró ningún manual técnico registrado para el proceso "${proc}".`
                    : `No se encontraron ayudas visuales registradas para el proceso "${proc}".`),
                type: 'warning',
                badgeText: isManual ? 'Consulte con el área de Calidad o Ingeniería de Procesos' : 'Consulte con el Supervisor de Turno o Calidad'
            });
        }
    }

    function _techDocsCargarArchivos(type, proc, clase, contentDiv) {
        contentDiv.innerHTML = '<p style="color:#666;text-align:center;">Cargando archivos...</p>';

        let url = '';
        if (type === 'manuales') {
            url = `${window.baseUrl}/manuales/archivos?proceso=${encodeURIComponent(proc)}`;
        } else if (type === 'ayudas') {
            url = `${window.baseUrl}/ayudas/archivos?proceso=${encodeURIComponent(proc)}`;
        } else if (type === 'dibujos') {
            url = `${window.baseUrl}/dibujos/archivos?ot=${encodeURIComponent(proc)}&clase=${encodeURIComponent(clase)}`;
        }

        fetch(url, { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => {
                if (!data.archivos || data.archivos.length === 0) {
                    _techDocsShowEmpty(contentDiv);
                    return;
                }
                _techDocsRenderArchivos(data.archivos, type, proc, clase, contentDiv);
            })
            .catch(() => _techDocsShowEmpty(contentDiv));
    }

    function _techDocsRenderArchivos(archivos, type, proc, clase, contentDiv) {
        contentDiv.innerHTML = '';
        const breadcrumb = document.createElement('div');
        breadcrumb.className = 'prod-viewer-breadcrumb';
        breadcrumb.innerHTML = `
            <span class="path-label">DOCUMENTACION_GIS</span>
            <span class="path-ot">${proc}</span>
            ${clase ? `<span style="opacity: 0.5;">/</span><span class="path-clase">${clase}</span>` : ''}
            <span class="path-count">${archivos.length} PDFs</span>
        `;
        contentDiv.appendChild(breadcrumb);

        const grid = document.createElement('div');
        grid.className = 'prod-viewer-grid';
        contentDiv.appendChild(grid);

        let currentIndex = 0;
        const CHUNK_SIZE = 8;

        function renderNextBatch() {
            const end = Math.min(currentIndex + CHUNK_SIZE, archivos.length);
            for (let i = currentIndex; i < end; i++) {
                const archivo = archivos[i];
                const card = document.createElement('div');
                card.className = 'prod-viewer-card';
                card.style.animationDelay = `${(i % CHUNK_SIZE) * 0.05}s`;

                const fileName = typeof archivo === 'string' ? archivo : archivo.nombre;
                const urlFinal = typeof archivo === 'object' ? archivo.url : '#';

                card.innerHTML = `
                    <div class="file-icon-wrapper">
                        <img src="${window.baseUrl}/images/Shadow_Icons/PDF_View-Shadow.png" class="prod-viewer-icon icon-default">
                        <img src="${window.baseUrl}/images/Color_Icons/Generales/pdf-view.png" class="prod-viewer-icon icon-hover">
                    </div>
                    <div class="prod-viewer-filename">${fileName}</div>
                    <div class="prod-viewer-action">Clic para abrir</div>`;

                card.onclick = () => window.open(urlFinal, '_blank');
                grid.appendChild(card);
            }

            currentIndex = end;
            if (currentIndex < archivos.length) {
                requestAnimationFrame(renderNextBatch);
            }
        }
        requestAnimationFrame(renderNextBatch);
    }
}

// Exponer globalmente en window para compatibilidad total
window.openDibujosViewer = openDibujosViewer;
window.openProgramasViewer = openProgramasViewer;
window.openManualesViewer = openManualesViewer;
window.openAyudasViewer = openAyudasViewer;
window.openTechDocsModal = openTechDocsModal;

