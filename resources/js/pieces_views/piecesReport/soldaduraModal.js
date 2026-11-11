let extraInfoButton = null;
let soldaduraModalOpen = false;
let currentSelectedProcess = null;

/**
 * Detectar cambios en el filtro de proceso e inicializar el botón en cabecera
 */
export function initializeSoldaduraFeature() {
    // Buscar el botón ya presente en la cabecera (adminPieces.blade.php)
    extraInfoButton = document.getElementById("btnExtraInfoSoldadura");

    if (extraInfoButton) {
        extraInfoButton.removeEventListener("click", getSoldaduraExtraInfo);
        extraInfoButton.addEventListener("click", getSoldaduraExtraInfo);
    }

    const processSelect = document.querySelector('select[name="process"]');
    if (processSelect) {
        // Verificar el valor inicial
        checkProcessAndShowButton(processSelect.value);

        // Agregar listener para cambios
        processSelect.addEventListener("change", function () {
            currentSelectedProcess = this.value;
            checkProcessAndShowButton(this.value);
        });
    }
}

/**
 * Verificar si el proceso es Soldadura y mostrar/ocultar botón
 */
export function checkProcessAndShowButton(processValue) {
    const procLower = String(processValue || "").toLowerCase().trim();
    const isSoldadura = procLower.includes("soldadura") || procLower === "pta";

    if (isSoldadura) {
        showExtraInfoButton();
    } else {
        hideExtraInfoButton();
        closeSoldaduraModal(); // Cerrar modal si está abierto
    }
}

/**
 * Mostrar el botón "Información extra" al lado del PDF
 */
function showExtraInfoButton() {
    if (!extraInfoButton || !document.body.contains(extraInfoButton)) {
        extraInfoButton = document.getElementById("btnExtraInfoSoldadura");
    }

    // Fallback: si por alguna razón no existía en el Blade, crearlo al lado de .btn-PDF
    if (!extraInfoButton) {
        const btnPdf = document.querySelector(".btn-PDF");
        if (btnPdf && btnPdf.parentNode) {
            extraInfoButton = document.createElement("button");
            extraInfoButton.type = "button";
            extraInfoButton.id = "btnExtraInfoSoldadura";
            extraInfoButton.className = "btn-extra-info-soldadura";
            extraInfoButton.title = "Ver información extra de soldadura";

            const shadowSrc = window.iconSoldadorShadow || (window.baseUrl + "/images/Shadow_Icons/soldador-Shadow.png");
            const viewSrc = window.iconSoldadorView || (window.baseUrl + "/images/Color_Icons/Generales/soldador-view.png");

            extraInfoButton.innerHTML = `
                <div class="soldadura-btn-wrapper">
                    <img src="${shadowSrc}" class="soldadura-icon icon-shadow" alt="Soldadura">
                    <img src="${viewSrc}" class="soldadura-icon icon-view" alt="Soldadura">
                </div>
                <span class="soldadura-btn-text">Información extra</span>
            `;

            btnPdf.parentNode.insertBefore(extraInfoButton, btnPdf);
            extraInfoButton.addEventListener("click", getSoldaduraExtraInfo);
        }
    }

    if (extraInfoButton) {
        extraInfoButton.style.display = "inline-flex";
    }
}

/**
 * Ocultar el botón
 */
function hideExtraInfoButton() {
    if (!extraInfoButton) {
        extraInfoButton = document.getElementById("btnExtraInfoSoldadura");
    }
    if (extraInfoButton) {
        extraInfoButton.style.display = "none";
    }
}

/**
 * Obtener los filtros activos actualmente de forma ultra-rápida (desde memoria si existe)
 */
function getCurrentFiltersFromDOM() {
    const getVal = (name) => {
        let el = document.querySelector(`[name="${name}"]`);
        if (!el) return "Todos";
        return el.value.trim();
    };

    let statusFilterEl = document.getElementById("statusPieceFilter");
    const selectedProcess = getVal("process");

    const activePiecesList = [];
    const activeOts = new Set();

    // 1. INTENTO EN MEMORIA (Instantáneo: 0ms sin forzar recalcs del DOM)
    if (typeof window.getCurrentFilteredPieces === "function") {
        const memPieces = window.getCurrentFilteredPieces();
        if (Array.isArray(memPieces) && memPieces.length > 0) {
            memPieces.forEach((item) => {
                const ord = item.ordered;
                if (ord) {
                    const proc = String(ord.process || "").trim().toLowerCase();
                    if (proc === selectedProcess.toLowerCase() || (selectedProcess.toLowerCase().includes("soldadura") && proc.includes("soldadura"))) {
                        const ot = ord.workOrder ? String(ord.workOrder).trim() : "";
                        if (ot) {
                            activeOts.add(ot);
                            activePiecesList.push({
                                class: String(ord.class || "").trim(),
                                workOrder: ot,
                                noAssembly: String(ord.noAssembly || "").trim(),
                            });
                        }
                    }
                }
            });
        }
    }

    // 2. FALLBACK A DOM sólo si la memoria estaba vacía
    if (activePiecesList.length === 0) {
        const rows = document.querySelectorAll(".table tbody tr");
        rows.forEach((row) => {
            if (row.style.display !== "none") {
                const ds = row.dataset;
                const proc = ds.process || "";
                if (String(proc).trim().toLowerCase() === selectedProcess.toLowerCase()) {
                    const clase = ds.class || "";
                    const ot = ds.workorder || "";
                    const n_juego = ds.njuego || "";
                    if (ot) {
                        activeOts.add(String(ot).trim());
                        activePiecesList.push({
                            class: String(clase).trim(),
                            workOrder: String(ot).trim(),
                            noAssembly: String(n_juego).trim(),
                        });
                    }
                }
            }
        });
    }

    return {
        workOrder: getVal("workOrder"),
        class: getVal("class"),
        operator: getVal("operator"),
        machine: getVal("machine"),
        process: selectedProcess,
        error: getVal("error"),
        dateFrom: getVal("dateFrom"),
        dateTo: getVal("dateTo"),
        n_juego: getVal("n_juego"),
        status: statusFilterEl ? statusFilterEl.value : "Todos",
        activeOts: Array.from(activeOts),
        activePieces: activePiecesList,
    };
}

export function getSoldaduraExtraInfo(e) {
    if (e) e.preventDefault();
    if (extraInfoButton && extraInfoButton.disabled) return;

    // Leer los filtros activos de forma optimizada
    const liveFilters = getCurrentFiltersFromDOM();

    // Abrir modal de inmediato con el loader corporativo animado
    openSoldaduraModalWithLoader(liveFilters);
}

/**
 * Abre el modal de Soldadura con diseño corporativo GIS Saavedra y loader fluido
 */
function openSoldaduraModalWithLoader(liveFilters) {
    closeSoldaduraModal(); // Asegurar que no haya múltiples modales abiertos
    soldaduraModalOpen = true;

    // Crear overlay con desenfoque suave y fondo oscuro corporativo
    const divOverlay = document.createElement("div");
    divOverlay.className = "modal-soldadura-overlay open";
    divOverlay.id = "modalSoldadura";

    // Contenedor principal del modal
    const modalContainer = document.createElement("div");
    modalContainer.className = "soldadura-info-modal";

    // Detectar si es PTA
    const isPTA = liveFilters.process && liveFilters.process.toLowerCase().includes("pta");

    // ── CABECERA CORPORATIVA GIS ──
    const headerGroup = document.createElement("div");
    headerGroup.className = "soldadura-modal-header";

    const titleArea = document.createElement("div");
    titleArea.className = "soldadura-modal-title-area";

    // Icono principal más grande con marco redondeado corporativo
    const iconBadge = document.createElement("div");
    iconBadge.className = "soldadura-modal-header-icon-wrap";
    const headerIconImg = document.createElement("img");
    headerIconImg.src = window.iconSoldadorView || (window.baseUrl + "/images/Color_Icons/Generales/soldador-view.png");
    headerIconImg.alt = "Soldadura";
    headerIconImg.className = "soldadura-modal-header-icon";
    iconBadge.appendChild(headerIconImg);

    const titleTextGroup = document.createElement("div");
    titleTextGroup.className = "soldadura-modal-title-group";

    const title = document.createElement("h2");
    title.textContent = isPTA ? "Información Extra — Soldadura PTA" : "Información Extra — Soldadura";
    title.className = "soldadura-modal-title";

    // Contenedor compacto redondeado para las píldoras de registros y proceso
    const metaRow = document.createElement("div");
    metaRow.className = "soldadura-modal-meta-row";

    const metaCapsule = document.createElement("div");
    metaCapsule.className = "soldadura-meta-capsule";

    const badgeCount = document.createElement("span");
    badgeCount.className = "soldadura-meta-pill soldadura-meta-count";
    badgeCount.id = "soldaduraBadgeCount";
    badgeCount.innerHTML = `<span class="badge-dot-pulse"></span> Consultando registros...`;

    const metaDivider = document.createElement("span");
    metaDivider.className = "soldadura-meta-divider";

    const badgeProcess = document.createElement("span");
    badgeProcess.className = "soldadura-meta-pill soldadura-meta-process";
    badgeProcess.textContent = isPTA ? "Procedimiento PTA" : "Soldadura Convencional";

    metaCapsule.appendChild(badgeCount);
    metaCapsule.appendChild(metaDivider);
    metaCapsule.appendChild(badgeProcess);
    metaRow.appendChild(metaCapsule);

    titleTextGroup.appendChild(title);
    titleTextGroup.appendChild(metaRow);

    titleArea.appendChild(iconBadge);
    titleArea.appendChild(titleTextGroup);
    headerGroup.appendChild(titleArea);

    // Acciones de cabecera: Búsqueda rápida + Botón cerrar (X)
    const headerActions = document.createElement("div");
    headerActions.className = "soldadura-modal-header-actions";

    const searchWrap = document.createElement("div");
    searchWrap.className = "soldadura-search-wrapper";
    searchWrap.id = "soldaduraSearchWrap";
    searchWrap.style.display = "none";
    searchWrap.innerHTML = `
        <svg class="search-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
        <input type="text" id="soldaduraQuickSearch" class="soldadura-quick-search-input" placeholder="Filtrar por OT, operador, juego..." autocomplete="off" />
    `;
    headerActions.appendChild(searchWrap);

    const closeBtnX = document.createElement("button");
    closeBtnX.type = "button";
    closeBtnX.className = "btn-modal-close-x";
    closeBtnX.title = "Cerrar modal (Esc)";
    closeBtnX.innerHTML = `
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
    `;
    closeBtnX.addEventListener("click", closeSoldaduraModal);
    headerActions.appendChild(closeBtnX);

    headerGroup.appendChild(headerActions);
    modalContainer.appendChild(headerGroup);

    // ── CONTENEDOR DEL CARGADOR ANIMADO (GIS SPINNER) ──
    const loaderContainer = document.createElement("div");
    loaderContainer.id = "soldadura-modal-loader-container";
    loaderContainer.className = "soldadura-loader-container";

    const spinnerImg = document.createElement("img");
    spinnerImg.src = window.loading || (window.baseUrl + "/images/Color_Icons/Generales/loading.gif");
    spinnerImg.alt = "Cargando...";
    spinnerImg.className = "soldadura-loader-img";

    const loaderText = document.createElement("p");
    loaderText.textContent = "Buscando y organizando piezas de soldadura...";
    loaderText.className = "soldadura-loader-text";

    loaderContainer.appendChild(spinnerImg);
    loaderContainer.appendChild(loaderText);
    modalContainer.appendChild(loaderContainer);

    divOverlay.appendChild(modalContainer);
    document.body.appendChild(divOverlay);

    // Cerrar al hacer clic fuera del modal
    divOverlay.addEventListener("click", (e) => {
        if (e.target === divOverlay) {
            closeSoldaduraModal();
        }
    });

    // Cerrar con la tecla Escape
    const onKeydownEscape = (e) => {
        if (e.key === "Escape" && soldaduraModalOpen) {
            closeSoldaduraModal();
            document.removeEventListener("keydown", onKeydownEscape);
        }
    };
    document.addEventListener("keydown", onKeydownEscape);

    // ── CONSULTA AL SERVIDOR ──
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "";

    fetch(window.baseUrl + "/pieces/getSoldaduraExtraInfo", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": csrfToken,
            "Content-Type": "application/json",
            Accept: "application/json",
        },
        body: JSON.stringify(liveFilters),
    })
        .then((response) => response.json())
        .then((data) => {
            if (!soldaduraModalOpen) return;

            if (data.success) {
                loaderContainer.remove();
                searchWrap.style.display = "flex";
                initializeSoldaduraModalContent(modalContainer, data.pieces || [], liveFilters, badgeCount);
            } else {
                loaderText.textContent = "Error: " + (data.message || "No se pudo obtener la información.");
                loaderText.style.color = "var(--gis-red)";
                spinnerImg.style.display = "none";
            }
        })
        .catch((error) => {
            if (!soldaduraModalOpen) return;
            console.error("Error en getSoldaduraExtraInfo:", error);
            loaderText.textContent = "Error de conexión con el servidor.";
            loaderText.style.color = "var(--gis-red)";
            spinnerImg.style.display = "none";
        });
}

/**
 * Inicializar la tabla y componentes interactivos del modal (con paginación ultra rápida)
 */
function initializeSoldaduraModalContent(modalContainer, rawPieces, liveFilters, badgeCount) {
    const isPTA = liveFilters.process && liveFilters.process.toLowerCase().includes("pta");

    // ── CONTENEDOR DE LA TABLA ──
    const tableContainer = document.createElement("div");
    tableContainer.className = "soldadura-table-container";

    const table = document.createElement("table");
    table.className = "soldadura-modal-table";

    const thead = document.createElement("thead");
    const headerRow = document.createElement("tr");
    headerRow.className = "soldadura-modal-header-row";

    const headers = isPTA
        ? [
              { text: "N° Juego", align: "center" },
              { text: "Operador", align: "left" },
              { text: "Clase", align: "center" },
              { text: "OT", align: "center" },
              { text: "Fecha", align: "center" },
              { text: "Hora", align: "center" },
              { text: "Precal. (°C)", align: "center" },
              { text: "Soldadura", align: "center" },
              { text: "Resultado", align: "center" },
              { text: "Defecto", align: "center" },
              { text: "Observaciones", align: "left" },
          ]
        : [
              { text: "N° Juego", align: "center" },
              { text: "Operador", align: "left" },
              { text: "Clase", align: "center" },
              { text: "OT", align: "center" },
              { text: "Peso por Pieza", align: "center" },
              { text: "Tipo Soldadura", align: "center" },
              { text: "Soldadura", align: "center" },
              { text: "Lote", align: "center" },
              { text: "Fecha", align: "center" },
              { text: "Hora", align: "center" },
              { text: "Observaciones", align: "left" },
          ];

    headers.forEach((h) => {
        const th = document.createElement("th");
        th.textContent = h.text;
        th.className = `soldadura-modal-th text-${h.align}`;
        headerRow.appendChild(th);
    });

    thead.appendChild(headerRow);
    table.appendChild(thead);

    const tbody = document.createElement("tbody");
    table.appendChild(tbody);
    tableContainer.appendChild(table);
    modalContainer.appendChild(tableContainer);

    // ── FOOTER ACTIONS (CON PAGINACIÓN INTEGRADA) ──
    const buttonsContainer = document.createElement("div");
    buttonsContainer.className = "soldadura-modal-buttons";

    // 1. Izquierda: Contenedor con contorno redondo de estadísticas
    const footerStatBadge = document.createElement("div");
    footerStatBadge.className = "soldadura-footer-stat-badge";
    footerStatBadge.id = "soldaduraFooterStats";
    footerStatBadge.innerHTML = `
        <span class="stat-dot"></span>
        <span id="soldaduraFooterStatsText">Mostrando 0 de 0 piezas</span>
    `;
    buttonsContainer.appendChild(footerStatBadge);

    // 2. Centro: Controles de Paginación fluida
    const paginationControls = document.createElement("div");
    paginationControls.className = "soldadura-pagination-controls";
    paginationControls.innerHTML = `
        <div class="soldadura-page-size-wrap">
            <label for="soldaduraPageSize">Filas:</label>
            <select id="soldaduraPageSize" class="soldadura-page-size-select" title="Cantidad de filas por página">
                <option value="25" selected>25</option>
                <option value="50">50</option>
                <option value="100">100</option>
                <option value="all">Todas</option>
            </select>
        </div>

        <div class="soldadura-pagination-nav">
            <button type="button" class="soldadura-page-btn" id="soldaduraBtnFirst" title="Primera página">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="11 17 6 12 11 7"></polyline>
                    <polyline points="18 17 13 12 18 7"></polyline>
                </svg>
            </button>
            <button type="button" class="soldadura-page-btn" id="soldaduraBtnPrev" title="Página anterior">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
            </button>

            <span class="soldadura-page-indicator" id="soldaduraPageIndicator">Pág. 1 de 1</span>

            <button type="button" class="soldadura-page-btn" id="soldaduraBtnNext" title="Página siguiente">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </button>
            <button type="button" class="soldadura-page-btn" id="soldaduraBtnLast" title="Última página">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="13 17 18 12 13 7"></polyline>
                    <polyline points="6 17 11 12 6 7"></polyline>
                </svg>
            </button>
        </div>
    `;
    buttonsContainer.appendChild(paginationControls);

    // 3. Derecha: Botones de Acción (PDF Azul y Cerrar Rojo)
    const rightButtons = document.createElement("div");
    rightButtons.className = "soldadura-modal-right-buttons";

    // Botón descargar PDF / Imprimir (Azul GIS)
    const btnDownload = document.createElement("button");
    btnDownload.type = "button";
    btnDownload.className = "btns blue soldadura-btn-download";
    btnDownload.title = "Descargar reporte PDF de soldadura";
    btnDownload.innerHTML = `
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
            <polyline points="7 10 12 15 17 10"></polyline>
            <line x1="12" y1="15" x2="12" y2="3"></line>
        </svg>
        <span>Descargar PDF</span>
    `;
    btnDownload.addEventListener("click", () => {
        const filters = { ...liveFilters };
        if (filters.activePieces) {
            filters.activePieces = JSON.stringify(filters.activePieces);
        }
        delete filters.action;
        delete filters.status;

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "";
        const form = document.createElement("form");
        form.method = "POST";
        form.action = window.baseUrl + "/pieces/downloadSoldaduraExtraInfoPDF";
        form.target = "_blank";

        const csrfInput = document.createElement("input");
        csrfInput.type = "hidden";
        csrfInput.name = "_token";
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);

        Object.entries(filters).forEach(([key, val]) => {
            const input = document.createElement("input");
            input.type = "hidden";
            input.name = key;
            input.value = val;
            form.appendChild(input);
        });

        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    });

    // Botón cerrar (Rojo GIS)
    const btnClose = document.createElement("button");
    btnClose.type = "button";
    btnClose.textContent = "Cerrar";
    btnClose.className = "btns danger soldadura-btn-close";
    btnClose.title = "Cerrar ventana";
    btnClose.addEventListener("click", closeSoldaduraModal);

    rightButtons.appendChild(btnDownload);
    rightButtons.appendChild(btnClose);
    buttonsContainer.appendChild(rightButtons);
    modalContainer.appendChild(buttonsContainer);

    // ── ESTADO DE PAGINACIÓN Y RENDERIZADO ──
    let currentPage = 1;
    let pageSize = 25; // 25 filas por defecto
    let currentFilteredPieces = [...rawPieces];

    const footerStatsText = document.getElementById("soldaduraFooterStatsText");
    const pageIndicator = document.getElementById("soldaduraPageIndicator");
    const btnFirst = document.getElementById("soldaduraBtnFirst");
    const btnPrev = document.getElementById("soldaduraBtnPrev");
    const btnNext = document.getElementById("soldaduraBtnNext");
    const btnLast = document.getElementById("soldaduraBtnLast");
    const selectPageSize = document.getElementById("soldaduraPageSize");

    const esc = (v) =>
        v !== null && v !== undefined
            ? String(v).replace(/"/g, "&quot;").replace(/>/g, "&gt;").replace(/</g, "&lt;")
            : "N/A";

    const renderCurrentPage = () => {
        const total = currentFilteredPieces.length;
        const totalPages = pageSize === "all" ? 1 : Math.ceil(total / Number(pageSize)) || 1;

        if (currentPage > totalPages) currentPage = totalPages;
        if (currentPage < 1) currentPage = 1;

        const startIdx = pageSize === "all" ? 0 : (currentPage - 1) * Number(pageSize);
        const endIdx = pageSize === "all" ? total : Math.min(startIdx + Number(pageSize), total);
        const pageItems = currentFilteredPieces.slice(startIdx, endIdx);

        tbody.innerHTML = "";

        if (total === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="${headers.length}" class="soldadura-modal-td soldadura-no-data">
                        <div class="soldadura-empty-state">
                            <span class="empty-icon">&#128269;</span>
                            <p>No se encontraron piezas que coincidan con la búsqueda.</p>
                        </div>
                    </td>
                </tr>
            `;
            if (footerStatsText) footerStatsText.innerHTML = `Sin registros`;
            if (pageIndicator) pageIndicator.textContent = `Pág. 0 de 0`;
            btnFirst.disabled = true;
            btnPrev.disabled = true;
            btnNext.disabled = true;
            btnLast.disabled = true;
            return;
        }

        let fullHtml = "";
        for (let i = 0; i < pageItems.length; i++) {
            fullHtml += buildRowHtml(pageItems[i], startIdx + i, isPTA, esc);
        }
        tbody.innerHTML = fullHtml;

        // Actualizar estadísticas del footer
        if (footerStatsText) {
            footerStatsText.innerHTML = `Mostrando <strong>${startIdx + 1} - ${endIdx}</strong> de <strong>${total}</strong> piezas`;
        }
        if (pageIndicator) {
            pageIndicator.textContent = `Pág. ${currentPage} de ${totalPages}`;
        }

        // Habilitar / deshabilitar botones de navegación
        btnFirst.disabled = currentPage <= 1;
        btnPrev.disabled = currentPage <= 1;
        btnNext.disabled = currentPage >= totalPages;
        btnLast.disabled = currentPage >= totalPages;

        // Subir scroll de la tabla al cambiar de página
        tableContainer.scrollTop = 0;
    };

    // Helper para generar el HTML de cada fila con badges corporativos GIS
    function buildRowHtml(piece, index, isPtaMode, escFn) {
        const rowClass = index % 2 === 0 ? "soldadura-row-even" : "soldadura-row-odd";

        const juegoBadge = `<span class="badge-juego">${escFn(piece.n_juego)}</span>`;
        const otBadge = `<span class="badge-ot">${escFn(piece.orden_trabajo)}</span>`;
        const claseBadge = `<span class="badge-clase">${escFn(piece.clase)}</span>`;

        if (isPtaMode) {
            let badgeHtml = "";
            const res = String(piece.resultado || "").trim().toLowerCase();
            if (res === "si") {
                badgeHtml = `<span class="badge-si"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><polyline points="20 6 9 17 4 12"></polyline></svg> Aprobado</span>`;
            } else if (res === "no") {
                badgeHtml = `<span class="badge-no"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg> Rechazado</span>`;
            } else if (res === "no aplica") {
                badgeHtml = `<span class="badge-na">N/A</span>`;
            } else {
                badgeHtml = `<span class="badge-empty">—</span>`;
            }

            const def = piece.defecto && piece.defecto !== "Ninguno" && piece.defecto !== "N/A"
                ? `<span class="badge-defecto">${escFn(piece.defecto)}</span>`
                : `<span class="text-muted">Ninguno</span>`;

            return `
                <tr class="${rowClass}">
                    <td class="soldadura-modal-td text-center">${juegoBadge}</td>
                    <td class="soldadura-modal-td text-left text-bold">${escFn(piece.operador)}</td>
                    <td class="soldadura-modal-td text-center">${claseBadge}</td>
                    <td class="soldadura-modal-td text-center">${otBadge}</td>
                    <td class="soldadura-modal-td text-center">${escFn(piece.fecha)}</td>
                    <td class="soldadura-modal-td text-center font-mono">${escFn(piece.hora)}</td>
                    <td class="soldadura-modal-td text-center">${escFn(piece.precalentamiento)}</td>
                    <td class="soldadura-modal-td text-center">${escFn(piece.material_soldadura)}</td>
                    <td class="soldadura-modal-td text-center">${badgeHtml}</td>
                    <td class="soldadura-modal-td text-center">${def}</td>
                    <td class="soldadura-modal-td text-left cell-obs">${escFn(piece.observaciones) || "—"}</td>
                </tr>
            `;
        }

        const pesoVal = piece.peso_pieza && piece.peso_pieza !== "N/A"
            ? `<span class="text-peso font-mono">${escFn(piece.peso_pieza)}</span>`
            : `<span class="text-muted">N/A</span>`;

        return `
            <tr class="${rowClass}">
                <td class="soldadura-modal-td text-center">${juegoBadge}</td>
                <td class="soldadura-modal-td text-left text-bold">${escFn(piece.operador)}</td>
                <td class="soldadura-modal-td text-center">${claseBadge}</td>
                <td class="soldadura-modal-td text-center">${otBadge}</td>
                <td class="soldadura-modal-td text-center">${pesoVal}</td>
                <td class="soldadura-modal-td text-center">${escFn(piece.tipo_soldadura)}</td>
                <td class="soldadura-modal-td text-center">${escFn(piece.material_soldadura)}</td>
                <td class="soldadura-modal-td text-center font-mono">${escFn(piece.lote)}</td>
                <td class="soldadura-modal-td text-center">${escFn(piece.fecha)}</td>
                <td class="soldadura-modal-td text-center font-mono">${escFn(piece.hora)}</td>
                <td class="soldadura-modal-td text-left cell-obs">${escFn(piece.observaciones) || "—"}</td>
            </tr>
        `;
    }

    // Eventos de Paginación
    btnFirst.addEventListener("click", () => {
        if (currentPage > 1) {
            currentPage = 1;
            renderCurrentPage();
        }
    });

    btnPrev.addEventListener("click", () => {
        if (currentPage > 1) {
            currentPage--;
            renderCurrentPage();
        }
    });

    btnNext.addEventListener("click", () => {
        const totalPages = pageSize === "all" ? 1 : Math.ceil(currentFilteredPieces.length / Number(pageSize)) || 1;
        if (currentPage < totalPages) {
            currentPage++;
            renderCurrentPage();
        }
    });

    btnLast.addEventListener("click", () => {
        const totalPages = pageSize === "all" ? 1 : Math.ceil(currentFilteredPieces.length / Number(pageSize)) || 1;
        if (currentPage < totalPages) {
            currentPage = totalPages;
            renderCurrentPage();
        }
    });

    selectPageSize.addEventListener("change", function () {
        pageSize = this.value === "all" ? "all" : parseInt(this.value, 10);
        currentPage = 1;
        renderCurrentPage();
    });

    // ── FILTRO RÁPIDO EN VIVO (BÚSQUEDA INSTANTÁNEA EN MODAL) ──
    const searchInput = document.getElementById("soldaduraQuickSearch");
    if (searchInput) {
        searchInput.value = "";
        searchInput.addEventListener("input", function () {
            const query = this.value.toLowerCase().trim();
            if (!query) {
                currentFilteredPieces = [...rawPieces];
            } else {
                currentFilteredPieces = rawPieces.filter((p) => {
                    const searchStr = [
                        p.n_juego,
                        p.operador,
                        p.clase,
                        p.orden_trabajo,
                        p.material_soldadura,
                        p.lote,
                        p.fecha,
                        p.hora,
                        p.observaciones,
                        p.defecto,
                        p.resultado,
                    ]
                        .filter(Boolean)
                        .join(" ")
                        .toLowerCase();

                    return searchStr.includes(query);
                });
            }

            badgeCount.innerHTML = `<span class="badge-dot-success"></span> ${currentFilteredPieces.length} ${currentFilteredPieces.length === 1 ? "registro" : "registros"}`;
            currentPage = 1;
            renderCurrentPage();
        });
    }

    // Renderizar página inicial
    badgeCount.innerHTML = `<span class="badge-dot-success"></span> ${rawPieces.length} ${rawPieces.length === 1 ? "registro" : "registros"}`;
    renderCurrentPage();
}

/**
 * Cerrar modal de Soldadura con transición limpia
 */
export function closeSoldaduraModal() {
    const overlay = document.getElementById("modalSoldadura");
    if (overlay) {
        overlay.classList.remove("open");
        overlay.remove();
    }
    soldaduraModalOpen = false;
}
