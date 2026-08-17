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
function checkProcessAndShowButton(processValue) {
    if (processValue === "Soldadura" || processValue === "Soldadura PTA" || processValue === "soldaduraPTA" || processValue === "PTA") {
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
    if (extraInfoButton) return; // Ya existe

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
        if (name === "operator") {
            // Para operador devolvemos la matrícula (value del select), no el textContent
            return el.value.trim();
        }
        return el.value.trim();
    };

    let statusFilterEl = document.getElementById("statusPieceFilter");
    const selectedProcess = getVal("process");

    // Extraer únicamente las piezas y OTs activas cargadas en la vista principal (adminPieces)
    // que correspondan al proceso seleccionado actualmente.
    const activePiecesList = [];
    const activeOts = new Set();
    
    if (window.pieces && Array.isArray(window.pieces)) {
        window.pieces.forEach(p => {
            let proc = p[4] || p.proceso || '';
            if (String(proc).trim().toLowerCase() === selectedProcess.toLowerCase()) {
                let clase = p["className"] || p.clase || '';
                let ot = p[0] || p.orden_trabajo || p.id_ot || '';
                let n_juego = p[1] || p.n_juego || p.n_pieza || '';
                if (ot) {
                    activeOts.add(String(ot).trim());
                    activePiecesList.push({
                        class: String(clase).trim(),
                        workOrder: String(ot).trim(),
                        noAssembly: String(n_juego).trim()
                    });
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

    // Leer los filtros activos del DOM principal
    const liveFilters = getCurrentFiltersFromDOM();

    // AISLAMIENTO DE FILTROS: Para la carga inicial del modal, solo pasamos el proceso,
    // las OTs activas y las piezas activas. Mantenemos los demás en "Todos" para no restringir las opciones del modal.
    const initialModalFilters = {
        process: liveFilters.process,
        activeOts: liveFilters.activeOts,
        activePieces: liveFilters.activePieces,
        workOrder: "Todos",
        class: "Todos",
        operator: "Todos",
        machine: "Todos",
        error: "Todos",
        dateFrom: "",
        dateTo: "",
        n_juego: "Todos",
        status: "Todos"
    };

    // Abrir modal de inmediato con el loader corporativo animado
    openSoldaduraModalWithLoader(initialModalFilters);
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

    // Contenedor para el selector de proceso en el encabezado
    const processToggleContainer = document.createElement("div");
    processToggleContainer.style.cssText = "display: flex; align-items: center; gap: 8px; margin-left: auto;";

    const processToggleLabel = document.createElement("label");
    processToggleLabel.textContent = "Proceso:";
    processToggleLabel.style.cssText = "font-family: 'Poppins', sans-serif; font-weight: 700; color: var(--gis-blue); font-size: 13px; margin: 0;";

    const processToggleSelect = document.createElement("select");
    processToggleSelect.className = "soldadura-modal-process-toggle";
    processToggleSelect.style.cssText = "padding: 6px 12px; width: 160px; border-radius: 8px; border: 1px solid rgba(3, 57, 102, 0.2); font-weight: 700; color: var(--gis-blue); font-family: 'Poppins', sans-serif; cursor: pointer; outline: none; background-color: var(--gis-white); box-shadow: 0 2px 4px rgba(0,0,0,0.05); font-size: 13px; transition: all 0.2s;";

    const optSoldadura = document.createElement("option");
    optSoldadura.value = "Soldadura";
    optSoldadura.textContent = "Soldadura";
    if (!isPTA) optSoldadura.selected = true;

    const optSoldaduraPTA = document.createElement("option");
    optSoldaduraPTA.value = "Soldadura PTA";
    optSoldaduraPTA.textContent = "Soldadura PTA";
    if (isPTA) optSoldaduraPTA.selected = true;

    processToggleSelect.appendChild(optSoldadura);
    processToggleSelect.appendChild(optSoldaduraPTA);

    processToggleSelect.addEventListener("change", (e) => {
        const nextProcess = e.target.value;
        const parentProcessSelect = document.querySelector('select[name="process"]');
        if (parentProcessSelect) {
            parentProcessSelect.value = nextProcess;
            parentProcessSelect.dispatchEvent(new Event('change'));
        }

        const nextActiveOts = new Set();
        const nextActivePieces = [];
        if (window.pieces && Array.isArray(window.pieces)) {
            window.pieces.forEach(p => {
                let proc = p[4] || p.proceso || '';
                if (String(proc).trim().toLowerCase() === nextProcess.toLowerCase()) {
                    let clase = p["className"] || p.clase || '';
                    let ot = p[0] || p.orden_trabajo || p.id_ot || '';
                    let n_juego = p[1] || p.n_juego || p.n_pieza || '';
                    if (ot) {
                        nextActiveOts.add(String(ot).trim());
                        nextActivePieces.push({
                            class: String(clase).trim(),
                            workOrder: String(ot).trim(),
                            noAssembly: String(n_juego).trim()
                        });
                    }
                }
            });
        }

        const nextFilters = {
            process: nextProcess,
            activeOts: Array.from(nextActiveOts),
            activePieces: nextActivePieces,
            workOrder: "Todos",
            class: "Todos",
            operator: "Todos",
            machine: "Todos",
            error: "Todos",
            dateFrom: "",
            dateTo: "",
            n_juego: "Todos",
            status: "Todos"
        };
        openSoldaduraModalWithLoader(nextFilters);
    });

    processToggleContainer.appendChild(processToggleLabel);
    processToggleContainer.appendChild(processToggleSelect);
    headerGroup.appendChild(processToggleContainer);

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

    // ── OBTENER VALORES ÚNICOS PARA LOS FILTROS DEL MODAL ──
    const getUniqueValues = (arr, key) => {
        const values = arr
            .map(item => item[key])
            .filter(val => val !== null && val !== undefined && String(val).trim() !== '' && String(val) !== 'N/A')
            .map(val => String(val).trim());
        return [...new Set(values)].sort((a, b) => a.localeCompare(b, undefined, {numeric: true}));
    };

    // ── MAPEO DE OT AL FORMATO CON NOMBRE ──
    const otMap = {};

    if (window.filtersData && Array.isArray(window.filtersData.workOrder)) {
        window.filtersData.workOrder.forEach(item => {
            if (typeof item === 'string') {
                const parts = item.split(" - ");
                const id = parts[0].trim();
                const name = parts.slice(1).join(" - ").trim();
                if (id && name && name !== '?') {
                    otMap[id] = name;
                }
            }
        });
    }

    const otSelectParent = document.querySelector('select[name="workOrder"]');
    if (otSelectParent) {
        Array.from(otSelectParent.options).forEach(opt => {
            if (opt.value && opt.value !== 'Todos') {
                const text = opt.textContent.trim();
                const val = opt.value.trim();
                [val, text].forEach(str => {
                    const parts = str.split(" - ");
                    const id = parts[0].trim();
                    const name = parts.slice(1).join(" - ").trim();
                    if (id && name && name !== '?') {
                        otMap[id] = name;
                    }
                });
            }
        });
    }

    let uniqueOts = getUniqueValues(pieces, 'orden_trabajo');
    if (liveFilters.activeOts && Array.isArray(liveFilters.activeOts) && liveFilters.activeOts.length > 0) {
        const activeSet = new Set(liveFilters.activeOts.map(v => String(v).trim()));
        uniqueOts = uniqueOts.filter(ot => activeSet.has(String(ot).trim()));
    }
    const uniqueClasses = getUniqueValues(pieces, 'clase');
    const uniqueOperators = getUniqueValues(pieces, 'operador');
    const uniqueJuegos = getUniqueValues(pieces, 'n_juego');

    let uniqueResultados = [];
    let uniqueDefectos = [];
    let uniqueTiposSoldadura = [];
    let uniqueMaterialesSoldadura = [];

    if (isPTA) {
        uniqueResultados = getUniqueValues(pieces, 'resultado');
        uniqueDefectos = getUniqueValues(pieces, 'defecto');
    } else {
        uniqueTiposSoldadura = getUniqueValues(pieces, 'tipo_soldadura').filter(val => val !== 'N/A' && val !== '0' && val !== '.0' && val !== '00' && val !== '');
        uniqueMaterialesSoldadura = getUniqueValues(pieces, 'material_soldadura');
    }

    // ── CREACIÓN DE FILTROS EN EL MODAL ──
    const filtersBar = document.createElement("div");
    filtersBar.className = "soldadura-modal-filters-bar";

    const createFilterSelect = (labelName, placeholder, optionsList, labelFormatter) => {
        const filterItem = document.createElement("div");
        filterItem.className = "soldadura-modal-filter-item";

        const label = document.createElement("label");
        label.textContent = labelName;
        filterItem.appendChild(label);

        const select = document.createElement("select");
        select.className = "soldadura-modal-filter-select";

        const defaultOption = document.createElement("option");
        defaultOption.value = "Todos";
        defaultOption.textContent = `— ${placeholder} —`;
        select.appendChild(defaultOption);

        optionsList.forEach(optVal => {
            const opt = document.createElement("option");
            opt.value = optVal;
            opt.textContent = labelFormatter ? labelFormatter(optVal) : optVal;
            select.appendChild(opt);
        });

        filterItem.appendChild(select);
        return { container: filterItem, selectElement: select };
    };

    const createFilterDate = (labelName, nameAttr) => {
        const filterItem = document.createElement("div");
        filterItem.className = "soldadura-modal-filter-item";
 
        const label = document.createElement("label");
        label.textContent = labelName;
        filterItem.appendChild(label);
 
        const input = document.createElement("input");
        input.type = "date";
        input.name = nameAttr;
        input.className = "soldadura-modal-filter-input";
 
        filterItem.appendChild(input);
        return { container: filterItem, inputElement: input };
    };

    const filterOt = createFilterSelect("OT", "Todas", uniqueOts, (val) => {
        const idStr = String(val).trim();
        if (otMap[idStr]) {
            return `${idStr} ${otMap[idStr]}`;
        }
        return idStr;
    });
    
    const filterClass = createFilterSelect("Clase", "Todas", uniqueClasses);
    const filterOperator = createFilterSelect("Operador", "Todos", uniqueOperators);
    const filterJuego = createFilterSelect("N° Juego", "Todos", uniqueJuegos);
    const filterDateFrom = createFilterDate("Desde", "dateFrom");
    const filterDateTo = createFilterDate("Hasta", "dateTo");
 
    filtersBar.appendChild(filterOt.container);
    filtersBar.appendChild(filterClass.container);
    filtersBar.appendChild(filterOperator.container);
    filtersBar.appendChild(filterJuego.container);
    filtersBar.appendChild(filterDateFrom.container);
    filtersBar.appendChild(filterDateTo.container);

    let filterPtaResultado = null;
    let filterPtaDefecto = null;
    let filterGeneralTipo = null;
    let filterGeneralMaterial = null;

    if (isPTA) {
        filterPtaResultado = createFilterSelect("Resultado", "Todos", uniqueResultados);
        filterPtaDefecto = createFilterSelect("Defecto", "Todos", uniqueDefectos);
        filtersBar.appendChild(filterPtaResultado.container);
        filtersBar.appendChild(filterPtaDefecto.container);
    } else {
        filterGeneralTipo = createFilterSelect("Tipo Soldadura", "Todos", uniqueTiposSoldadura);
        filterGeneralMaterial = createFilterSelect("Material", "Todos", uniqueMaterialesSoldadura);
        filtersBar.appendChild(filterGeneralTipo.container);
        filtersBar.appendChild(filterGeneralMaterial.container);
    }

    modalContainer.appendChild(filtersBar);

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

    const parsePieceDate = (dateStr) => {
        if (!dateStr || dateStr === 'N/A') return null;
        const parts = dateStr.split('-');
        if (parts.length === 3) {
            return `${parts[2]}-${parts[1]}-${parts[0]}`; // yyyy-mm-dd
        }
        return null;
    };

    const applyModalFilters = () => {
        const selectedOt = filterOt.selectElement.value;
        const selectedClass = filterClass.selectElement.value;
        const selectedOperator = filterOperator.selectElement.value;
        const selectedJuego = filterJuego.selectElement.value;
        const selectedDateFrom = filterDateFrom.inputElement.value;
        const selectedDateTo = filterDateTo.inputElement.value;
 
        let selectedResultado = "Todos";
        let selectedDefecto = "Todos";
        let selectedTipo = "Todos";
        let selectedMaterial = "Todos";
 
        if (isPTA) {
            selectedResultado = filterPtaResultado.selectElement.value;
            selectedDefecto = filterPtaDefecto.selectElement.value;
        } else {
            selectedTipo = filterGeneralTipo.selectElement.value;
            selectedMaterial = filterGeneralMaterial.selectElement.value;
        }
 
        const filtered = pieces.filter(piece => {
            if (selectedOt !== "Todos" && String(piece.orden_trabajo) !== selectedOt) return false;
            if (selectedClass !== "Todos" && String(piece.clase) !== selectedClass) return false;
            if (selectedOperator !== "Todos" && String(piece.operador) !== selectedOperator) return false;
            if (selectedJuego !== "Todos" && String(piece.n_juego) !== selectedJuego) return false;
 
            const pieceDate = parsePieceDate(piece.fecha);
            if (pieceDate) {
                if (selectedDateFrom && pieceDate < selectedDateFrom) return false;
                if (selectedDateTo && pieceDate > selectedDateTo) return false;
            }
 
            if (isPTA) {
                if (selectedResultado !== "Todos" && String(piece.resultado) !== selectedResultado) return false;
                if (selectedDefecto !== "Todos" && String(piece.defecto) !== selectedDefecto) return false;
            } else {
                if (selectedTipo !== "Todos" && String(piece.tipo_soldadura) !== selectedTipo) return false;
                if (selectedMaterial !== "Todos" && String(piece.material_soldadura) !== selectedMaterial) return false;
            }
            return true;
        });
 
        renderTableBody(filtered);
    };
 
    // Agregar event listeners a todos los selectores de filtros
    [filterOt, filterClass, filterOperator, filterJuego].forEach(f => {
        f.selectElement.addEventListener("change", applyModalFilters);
    });
 
    [filterDateFrom.inputElement, filterDateTo.inputElement].forEach(elem => {
        elem.addEventListener("change", applyModalFilters);
        elem.addEventListener("input", applyModalFilters);
    });
 
    if (isPTA) {
        filterPtaResultado.selectElement.addEventListener("change", applyModalFilters);
        filterPtaDefecto.selectElement.addEventListener("change", applyModalFilters);
    } else {
        filterGeneralTipo.selectElement.addEventListener("change", applyModalFilters);
        filterGeneralMaterial.selectElement.addEventListener("change", applyModalFilters);
    }

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
            ...getCurrentFiltersFromDOM()
        };
 
        // Serializar activePieces a JSON string para enviarlo de forma segura
        if (filters.activePieces) {
            filters.activePieces = JSON.stringify(filters.activePieces);
        }
 
        const modalOt = filterOt.selectElement.value;
        const modalClass = filterClass.selectElement.value;
        const modalOperator = filterOperator.selectElement.value;
        const modalJuego = filterJuego.selectElement.value;
        const modalDateFrom = filterDateFrom.inputElement.value;
        const modalDateTo = filterDateTo.inputElement.value;
 
        if (modalOt !== "Todos") filters.workOrder = modalOt;
        if (modalClass !== "Todos") filters.class = modalClass;
        if (modalOperator !== "Todos") {
            const match = pieces.find(p => p.operador === modalOperator);
            if (match && match.operator_matricula) {
                filters.operator = match.operator_matricula;
            }
        }
        if (modalJuego !== "Todos") filters.n_juego = modalJuego;
        if (modalDateFrom) filters.dateFrom = modalDateFrom;
        if (modalDateTo) filters.dateTo = modalDateTo;
 
        if (isPTA) {
            const modalResultado = filterPtaResultado.selectElement.value;
            const modalDefecto = filterPtaDefecto.selectElement.value;
            if (modalResultado !== "Todos" && modalResultado !== "") filters.resultado = modalResultado;
            if (modalDefecto !== "Todos" && modalDefecto !== "") filters.defecto = modalDefecto;
        } else {
            const modalTipo = filterGeneralTipo.selectElement.value;
            const modalMaterial = filterGeneralMaterial.selectElement.value;
            if (modalTipo !== "Todos" && modalTipo !== "") filters.tipo_soldadura = modalTipo;
            if (modalMaterial !== "Todos" && modalMaterial !== "") filters.material_soldadura = modalMaterial;
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

// Inicializar la funcionalidad cuando se carga la página

