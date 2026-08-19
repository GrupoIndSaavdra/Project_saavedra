let extraInfoButton = null;
let soldaduraModalOpen = false;
let soldaduraActiveFrameId = null;

let currentSelectedProcess = null;

/**
 * Detectar cambios en el filtro de proceso
 */
export function initializeSoldaduraFeature() {
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
    const procLower = String(processValue).toLowerCase().trim();
    if (procLower.includes("soldadura") || procLower === "pta") {
        showExtraInfoButton();
    } else {
        hideExtraInfoButton();
        closeSoldaduraModal(); // Cerrar modal si está abierto
    }
}

/**
 * Crear y mostrar el botón "Ver información extra"
 */
function showExtraInfoButton() {
    if (extraInfoButton && document.body.contains(extraInfoButton)) return; // Ya existe y está en el DOM

    // Limpiar botones huérfanos que pudieran haber quedado en el DOM
    document.querySelectorAll(".btn-extra-info-soldadura").forEach(btn => btn.remove());

    extraInfoButton = document.createElement("button");
    extraInfoButton.className = "btn-extra-info-soldadura";
    extraInfoButton.textContent = "Ver información extra";

    extraInfoButton.addEventListener("click", getSoldaduraExtraInfo);

    document.body.appendChild(extraInfoButton);
}

/**
 * Ocultar y remover el botón
 */
function hideExtraInfoButton() {
    if (extraInfoButton) {
        extraInfoButton.remove();
        extraInfoButton = null;
    }
    
    // Asegurar que se eliminen del DOM todos los botones con esa clase
    document.querySelectorAll(".btn-extra-info-soldadura").forEach(btn => btn.remove());
}


/**
 * Obtener los filtros activos actualmente desde el DOM
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
    
    // Extraer únicamente las piezas y OTs visibles en el DOM principal (adminPieces)
    // que coincidan con los filtros aplicados en el frontend.
    const rows = document.querySelectorAll(".table tbody tr");
    rows.forEach(row => {
        if (row.style.display !== "none") {
            const ds = row.dataset;
            const proc = ds.process || '';
            if (String(proc).trim().toLowerCase() === selectedProcess.toLowerCase()) {
                const clase = ds.class || '';
                const ot = ds.workorder || '';
                const n_juego = ds.njuego || '';
                if (ot) {
                    activeOts.add(String(ot).trim());
                    activePiecesList.push({
                        class: String(clase).trim(),
                        workOrder: String(ot).trim(),
                        noAssembly: String(n_juego).trim()
                    });
                }
            }
        }
    });

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

    // Leer los filtros activos del DOM principal
    const liveFilters = getCurrentFiltersFromDOM();

    // Abrir modal de inmediato con el loader corporativo animado
    openSoldaduraModalWithLoader(liveFilters);
}

/**
 * Abre el modal de Soldadura con un loader corporativo inicial
 */
function openSoldaduraModalWithLoader(liveFilters) {
    closeSoldaduraModal(); // Asegurar que no haya múltiples modales abiertos
    soldaduraModalOpen = true;

    // Crear div de opacidad (overlay) usando el fondo liviano de calidad (sin blur pesado)
    const divOpacity = document.createElement("div");
    divOpacity.className = "modal-soldadura-overlay open";
    divOpacity.id = "modalSoldadura";

    // Crear contenedor del modal (content) usando el estilo original de soldadura-info-modal
    const modalContainer = document.createElement("div");
    modalContainer.className = "soldadura-info-modal";

    // Detectar si es PTA
    const isPTA = liveFilters.process && liveFilters.process.toLowerCase().includes('pta');

    // ── ESTRUCTURA CABECERA ORIGINAL ──
    const headerGroup = document.createElement("div");
    headerGroup.className = "soldadura-modal-header";

    const titleGroup = document.createElement("div");
    titleGroup.className = "soldadura-modal-title-group";

    const title = document.createElement("h2");
    title.textContent = isPTA ? "Información Extra — Soldadura PTA" : "Información Extra — Soldadura";
    title.className = "soldadura-modal-title";
    titleGroup.appendChild(title);

    const subtitle = document.createElement("p");
    subtitle.className = "soldadura-modal-subtitle";
    subtitle.textContent = "Conectando con el servidor...";
    titleGroup.appendChild(subtitle);

    headerGroup.appendChild(titleGroup);
    modalContainer.appendChild(headerGroup);

    // Contenedor del cargador/spinner corporativo
    const loaderContainer = document.createElement("div");
    loaderContainer.id = "soldadura-modal-loader-container";
    loaderContainer.style.display = "flex";
    loaderContainer.style.flexDirection = "column";
    loaderContainer.style.alignItems = "center";
    loaderContainer.style.justifyContent = "center";
    loaderContainer.style.flexGrow = "1";
    loaderContainer.style.padding = "60px 40px";

    const spinnerImg = document.createElement("img");
    spinnerImg.src = window.loading; // Usar el loader.gif cargado de la paleta corporativa
    spinnerImg.alt = "Cargando...";
    spinnerImg.style.width = "80px";
    spinnerImg.style.height = "80px";
    spinnerImg.style.marginBottom = "20px";

    const loaderText = document.createElement("p");
    loaderText.textContent = "Buscando registros y optimizando resultados...";
    loaderText.style.color = "var(--gis-blue)";
    loaderText.style.fontWeight = "600";
    loaderText.style.fontFamily = "'Poppins', sans-serif";
    loaderText.style.fontSize = "15px";

    loaderContainer.appendChild(spinnerImg);
    loaderContainer.appendChild(loaderText);
    modalContainer.appendChild(loaderContainer);

    divOpacity.appendChild(modalContainer);
    document.body.appendChild(divOpacity);

    // Cierre al hacer click en el fondo oscuro
    divOpacity.addEventListener("click", (e) => {
        if (e.target === divOpacity) {
            closeSoldaduraModal();
        }
    });

    // Iniciar la consulta AJAX
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute("content");

    fetch(window.baseUrl + "/pieces/getSoldaduraExtraInfo", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": csrfToken,
            "Content-Type": "application/json",
            Accept: "application/json",
        },
        body: JSON.stringify(liveFilters)
    })
    .then((response) => response.json())
    .then((data) => {
        if (!soldaduraModalOpen) return; // Por si cerró el modal mientras cargaba
        
        if (data.success) {
            // Remover el cargador e inicializar la interfaz
            loaderContainer.remove();
            initializeSoldaduraModalContent(modalContainer, data.pieces, liveFilters, subtitle);
        } else {
            loaderText.textContent = "Error: " + (data.message || "No se pudo obtener información.");
            loaderText.style.color = "var(--gis-red)";
            spinnerImg.style.display = "none";
        }
    })
    .catch((error) => {
        if (!soldaduraModalOpen) return;
        console.error("Error:", error);
        loaderText.textContent = "Error de conexión con el servidor.";
        loaderText.style.color = "var(--gis-red)";
        spinnerImg.style.display = "none";
    });
}

/**
 * Inicializar los componentes interactivos y la tabla del modal
 */
function initializeSoldaduraModalContent(modalContainer, pieces, liveFilters, subtitle) {
    const isPTA = liveFilters.process && liveFilters.process.toLowerCase().includes('pta');

    // ── CREACIÓN DE TABLA ──
    const tableContainer = document.createElement("div");
    tableContainer.className = "soldadura-table-container";

    const table = document.createElement("table");
    table.className = "soldadura-modal-table";

    const thead = document.createElement("thead");
    const headerRow = document.createElement("tr");
    headerRow.className = "soldadura-modal-header-row";

    const headers = isPTA ? [
        "N° Juego",
        "Operador",
        "Clase",
        "OT",
        "Fecha",
        "Hora",
        "PRECAL. (°C)",
        "Soldadura",
        "Resultado",
        "Defecto",
        "Observaciones"
    ] : [
        "N° Juego",
        "Operador",
        "Clase",
        "OT",
        "Peso por Pieza",
        "Tipo Soldadura",
        "Soldadura",
        "Lote",
        "Fecha",
        "Hora",
        "Observaciones",
    ];

    headers.forEach((headerText) => {
        const th = document.createElement("th");
        th.textContent = headerText;
        th.className = "soldadura-modal-th";
        headerRow.appendChild(th);
    });

    thead.appendChild(headerRow);
    table.appendChild(thead);

    const tbody = document.createElement("tbody");
    table.appendChild(tbody);
    tableContainer.appendChild(table);
    modalContainer.appendChild(tableContainer);

    // ── LÓGICA DE APLICACIÓN DE FILTROS POR CHUNKS (requestAnimationFrame) ──
    const renderTableBody = (filteredPieces) => {
        if (soldaduraActiveFrameId) {
            cancelAnimationFrame(soldaduraActiveFrameId);
            soldaduraActiveFrameId = null;
        }
        tbody.innerHTML = "";
        if (filteredPieces.length === 0) {
            const tr = document.createElement("tr");
            const td = document.createElement("td");
            td.colSpan = headers.length;
            td.textContent = "Ningún registro coincide con los filtros seleccionados.";
            td.className = "soldadura-modal-td soldadura-no-data";
            td.style.textAlign = "center";
            td.style.padding = "20px";
            tr.appendChild(td);
            tbody.appendChild(tr);
            subtitle.textContent = "Total de piezas: 0";
            return;
        }

        const CHUNK_SIZE = 100; // Renderizar en partes de 100 para máximo rendimiento
        let index = 0;
        const esc = (v) => v !== null && v !== undefined ? String(v).replace(/"/g, '&quot;').replace(/>/g, '&gt;').replace(/</g, '&lt;') : 'N/A';

        function renderNextChunk() {
            let chunkHtml = "";
            const limit = Math.min(index + CHUNK_SIZE, filteredPieces.length);

            for (; index < limit; index++) {
                const piece = filteredPieces[index];
                const rowClass = index % 2 === 0 ? "soldadura-row-even" : "soldadura-row-odd";

                if (isPTA) {
                    let badgeHtml = "";
                    if (piece.resultado === 'Si') {
                        badgeHtml = `<span class="badge-si">&#10003; Aprobado</span>`;
                    } else if (piece.resultado === 'No') {
                        badgeHtml = `<span class="badge-no">&#10007; Rechazado</span>`;
                    } else if (piece.resultado === 'No Aplica') {
                        badgeHtml = `<span class="badge-na">N/A</span>`;
                    } else {
                        badgeHtml = `<span class="badge-empty">—</span>`;
                    }

                    chunkHtml += `
                        <tr class="${rowClass}">
                            <td class="soldadura-modal-td"><strong>${esc(piece.n_juego)}</strong></td>
                            <td class="soldadura-modal-td">${esc(piece.operador)}</td>
                            <td class="soldadura-modal-td">${esc(piece.clase)}</td>
                            <td class="soldadura-modal-td">${esc(piece.orden_trabajo)}</td>
                            <td class="soldadura-modal-td">${esc(piece.fecha)}</td>
                            <td class="soldadura-modal-td">${esc(piece.hora)}</td>
                            <td class="soldadura-modal-td">${esc(piece.precalentamiento)}</td>
                            <td class="soldadura-modal-td">${esc(piece.material_soldadura)}</td>
                            <td class="soldadura-modal-td">${badgeHtml}</td>
                            <td class="soldadura-modal-td">${esc(piece.defecto)}</td>
                            <td class="soldadura-modal-td">${esc(piece.observaciones)}</td>
                        </tr>
                    `;
                } else {
                    chunkHtml += `
                        <tr class="${rowClass}">
                            <td class="soldadura-modal-td"><strong>${esc(piece.n_juego)}</strong></td>
                            <td class="soldadura-modal-td">${esc(piece.operador)}</td>
                            <td class="soldadura-modal-td">${esc(piece.clase)}</td>
                            <td class="soldadura-modal-td">${esc(piece.orden_trabajo)}</td>
                            <td class="soldadura-modal-td">${esc(piece.peso_pieza)}</td>
                            <td class="soldadura-modal-td">${esc(piece.tipo_soldadura)}</td>
                            <td class="soldadura-modal-td">${esc(piece.material_soldadura)}</td>
                            <td class="soldadura-modal-td">${esc(piece.lote)}</td>
                            <td class="soldadura-modal-td">${esc(piece.fecha)}</td>
                            <td class="soldadura-modal-td">${esc(piece.hora)}</td>
                            <td class="soldadura-modal-td">${esc(piece.observaciones)}</td>
                        </tr>
                    `;
                }
            }

            tbody.insertAdjacentHTML("beforeend", chunkHtml);

            if (index < filteredPieces.length) {
                subtitle.textContent = `Cargando piezas por partes: ${index} de ${filteredPieces.length}...`;
                soldaduraActiveFrameId = requestAnimationFrame(renderNextChunk);
            } else {
                subtitle.textContent = `Total de piezas: ${filteredPieces.length} ${filteredPieces.length === 1 ? 'pieza' : 'piezas'} (de ${pieces.length} en total)`;
                soldaduraActiveFrameId = null;
            }
        }

        renderNextChunk();
    };

    // Inicializar contenido de la tabla
    renderTableBody(pieces);

    // ── BOTONES DE ACCIÓN ──
    const buttonsContainer = document.createElement("div");
    buttonsContainer.className = "soldadura-modal-buttons";

    // Botón descargar PDF
    const btnDownload = document.createElement("button");
    btnDownload.type = "button";
    btnDownload.textContent = "Descargar PDF";
    btnDownload.className = "soldadura-btn-download";
    btnDownload.addEventListener("click", () => {
        const filters = {
            ...liveFilters
        };
 
        // Serializar activePieces a JSON string para enviarlo de forma segura
        if (filters.activePieces) {
            filters.activePieces = JSON.stringify(filters.activePieces);
        }
 
        delete filters.action;
        delete filters.status;
 
        // Submit using dynamic POST form to avoid Apache "Request-URI Too Long" 414 error
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute("content");
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

    // Botón cerrar
    const btnClose = document.createElement("button");
    btnClose.textContent = "Cerrar";
    btnClose.className = "soldadura-btn-close";
    btnClose.addEventListener("click", closeSoldaduraModal);

    buttonsContainer.appendChild(btnDownload);
    buttonsContainer.appendChild(btnClose);
    modalContainer.appendChild(buttonsContainer);
}

/**
 * Cerrar modal de Soldadura con transición
 */
export function closeSoldaduraModal() {
    if (soldaduraActiveFrameId) {
        cancelAnimationFrame(soldaduraActiveFrameId);
        soldaduraActiveFrameId = null;
    }
    let divOpacity = document.getElementById("modalSoldadura");
    if (divOpacity) {
        divOpacity.remove();
    }
    soldaduraModalOpen = false;
}
