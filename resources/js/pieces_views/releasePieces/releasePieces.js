import { createDateFilterTrigger } from '../../components/modalCmCalendar.js';
var operacion = false;

// Restaurar filtros si venimos de la vista de detalle de una pieza
const comingBack = sessionStorage.getItem('comingFromPieceView') === 'true';
sessionStorage.removeItem('comingFromPieceView');

// Notificación breve si el usuario ingresa dando clic al menú de navegación
if (sessionStorage.getItem('gis_nav_entry') === 'true') {
    sessionStorage.removeItem('gis_nav_entry');
    setTimeout(() => {
        if (typeof window.gisToast === 'function') {
            window.gisToast('Puedes combinar múltiples filtros en tiempo real para refinar tu búsqueda.', 'info');
        }
    }, 900);
}

if (comingBack) {
    const savedFilters = sessionStorage.getItem('adminPiecesFilters');
    if (savedFilters) {
        try {
            const parsed = JSON.parse(savedFilters);
            if (window.selectedItems) {
                Object.keys(parsed).forEach(key => {
                    if (key !== 'status' && key !== 'statusPerson') {
                        window.selectedItems[key] = parsed[key];
                    }
                });
            }
            if (parsed.status) {
                sessionStorage.setItem("currentStatusFilter", parsed.status);
            }
            if (parsed.statusPerson) {
                sessionStorage.setItem("currentStatusPersonFilter", parsed.statusPerson);
            }
        } catch (e) {
            console.error("[restoreFilters] Error parsing saved filters:", e);
        }
    }
} else {
    // Si no venimos de la pieza, limpiar filtros para iniciar limpio
    sessionStorage.removeItem('adminPiecesFilters');
    sessionStorage.removeItem('currentStatusFilter');
    sessionStorage.removeItem('currentStatusPersonFilter');
}

// Escuchar clicks en el botón "Ver pieza" para marcar el retorno
document.addEventListener("click", (e) => {
    const btn = e.target.closest(".btn-pza");
    if (btn) {
        sessionStorage.setItem("comingFromPieceView", "true");
        // Guardar estado actual de filtros antes de navegar
        const filters = obtenerRequest();
        const parts = filters.split("|");
        const filterNames = ["workOrder", "class", "operator", "machine", "process", "error", "dateFrom", "dateTo", "n_juego"];
        let filterObj = {};
        filterNames.forEach((name, i) => {
            let val = parts[i] || "Todos";
            if (name === "workOrder" && val) {
                val = val.replaceAll("!", "/");
            }
            filterObj[name] = val;
        });
        const statusSel = document.getElementById("statusPieceFilter");
        if (statusSel) filterObj.status = statusSel.value;
        const personSel = document.getElementById("statusPersonFilter");
        if (personSel) filterObj.statusPerson = personSel.value;

        sessionStorage.setItem("adminPiecesFilters", JSON.stringify(filterObj));
    }
});

let allMappedPieces = [];
let currentFilteredPieces = [];
let currentPage = 1;
let pageSize = 50;
let currentSortColumn = -1;
let currentSortDirection = "none";
const profileValue = 'quality';
const esc = (v) => v ? String(v).replace(/"/g, '&quot;').replace(/>/g, '&gt;').replace(/</g, '&lt;') : '';

function renderCurrentPage() {
    const table = document.querySelector(".table");
    if (!table) return;

    let tbody = table.querySelector("tbody");
    if (!tbody) {
        tbody = document.createElement("tbody");
        table.appendChild(tbody);

        tbody.addEventListener("click", (e) => {
            const btn = e.target.closest(".btn-action-liberar, .btn-action-rechazar");
            if (!btn) return;
            e.preventDefault();

            const dataPieza = btn.getAttribute("data-pieza");
            const proceso = btn.getAttribute("data-proceso");

            if (btn.classList.contains("btn-action-liberar")) {
                create_ObservationsField({
                    pieza: dataPieza,
                    proceso: proceso,
                    liberar: true,
                    buena: btn.getAttribute("data-buena") === "true",
                });
            } else {
                create_ObservationsField({
                    pieza: dataPieza,
                    proceso: proceso,
                    liberar: false,
                    buena: false,
                });
            }
        });
    }

    const totalCount = currentFilteredPieces.length;
    const totalPages = Math.max(1, Math.ceil(totalCount / pageSize));
    if (currentPage > totalPages) currentPage = totalPages;
    if (currentPage < 1) currentPage = 1;

    const startIdx = (currentPage - 1) * pageSize;
    const endIdx = Math.min(startIdx + pageSize, totalCount);
    const pageItems = currentFilteredPieces.slice(startIdx, endIdx);

    let html = "";
    for (let i = 0; i < pageItems.length; i++) {
        let data = pageItems[i];
        let piezaOG = data.original;
        let infoP = data.info;
        let pieza = data.ordered;
        let piezasIds = infoP[0].join(",");
        let origIdx = data.originalIndex !== undefined ? data.originalIndex : (startIdx + i);

        html += `<tr style="background-color: ${pieza.colorPiece};"
            data-original-index="${origIdx}"
            data-color="${esc(pieza.colorPiece).toUpperCase()}"
            data-workorder="${esc(pieza.workOrder)}"
            data-class="${esc(pieza.class)}"
            data-operator="${esc(pieza.operator)}"
            data-machine="${esc(pieza.machine)}"
            data-process="${esc(pieza.process)}"
            data-error="${esc(pieza.errors)}"
            data-date="${esc(pieza.machinedDate)}"
            data-njuego="${esc(pieza.noAssembly)}"
        >`;

        Object.keys(pieza).forEach((key) => {
            if (key !== "colorPiece") {
                let cellValue = pieza[key] !== null && pieza[key] !== undefined ? pieza[key] : "";

                if (key === "btn_release") {
                    let btnHtml = "";
                    if (!pieza[key][1].includes("Incompleto") && pieza[key][0] != 1) {
                        let bool = (infoP[2] == "Ninguno" && piezaOG[9] != 2);
                        btnHtml = `<a class="btn-liberar btn-action-liberar" style="cursor:pointer;" data-pieza="${piezasIds}" data-proceso="${infoP[1]}" data-buena="${bool}"><img src="${window.liberar}" alt="Liberar" class="ver"></a>`;
                    }
                    html += `<td>${btnHtml}</td>`;
                } else if (key === "btn_decline") {
                    let btnHtml = "";
                    if (pieza[key] != 2) {
                        btnHtml = `<a class="btn-liberar btn-action-rechazar" style="cursor:pointer;" data-pieza="${piezasIds}" data-proceso="${infoP[1]}"><img src="${window.rechazar}" alt="Rechazar" class="ver"></a>`;
                    }
                    html += `<td>${btnHtml}</td>`;
                } else if (key === "btn_seePiece") {
                    let url = `${window.baseUrl}/pieces/${piezasIds}/${infoP[1]}/${profileValue}`;
                    html += `<td><a class="btn-pza" href="${url}"><img src="${window.ojito}" alt="Ver" class="ver"></a></td>`;
                } else {
                    let cellClass = (key === "operator" || key === "observations" || key === "observacion_liberacion") ? ' class="wide-cell"' : '';
                    html += `<td${cellClass}>${cellValue}</td>`;
                }
            }
        });
        html += `</tr>`;
    }

    if (totalCount === 0) {
        html = `<tr class="empty-row"><td colspan="15" style="text-align: center; padding: 25px; color: #64748b; font-weight: 600;">No se encontraron piezas con los filtros seleccionados.</td></tr>`;
    }

    tbody.innerHTML = html;

    updatePaginationUI(totalCount, totalPages, startIdx, endIdx);

    const loading = document.querySelector('.loading');
    if (loading) loading.hidden = true;
}

function updatePaginationUI(totalCount, totalPages, startIdx, endIdx) {
    let totalLabel = document.querySelector(".total-records-found");
    if (!totalLabel) return;

    const grandTotal = (allMappedPieces && allMappedPieces.length) ? allMappedPieces.length : totalCount;

    totalLabel.innerHTML = `
        <nav class="gis-unified-pagination" aria-label="Navegación de piezas">
            <div class="gis-pagination-left">
                <span class="gis-pagination-stat-badge">
                    <span class="gis-pagination-stat-dot"></span>
                    <span>Total: <strong>${grandTotal.toLocaleString()}</strong></span>
                    ${totalCount !== grandTotal ? `<span style="color:rgba(255,255,255,0.4); margin:0 4px;">|</span><span style="color:#7dd3fc;">Filtradas: <strong>${totalCount.toLocaleString()}</strong></span>` : ''}
                </span>

                <div class="gis-pagination-select-wrap">
                    <label for="pieces-page-size-select">Filas:</label>
                    <select id="pieces-page-size-select" class="gis-pagination-select" aria-label="Cantidad de filas por página">
                        <option value="50" ${pageSize === 50 ? 'selected' : ''}>50</option>
                        <option value="100" ${pageSize === 100 ? 'selected' : ''}>100</option>
                        <option value="250" ${pageSize === 250 ? 'selected' : ''}>250</option>
                        <option value="500" ${pageSize === 500 ? 'selected' : ''}>500</option>
                    </select>
                </div>
            </div>

            <div class="gis-pagination-center">
                <span class="gis-pagination-range-pill">
                    ${totalCount === 0 
                        ? 'Sin piezas registradas con estos filtros' 
                        : `Mostrando <strong>${(startIdx + 1).toLocaleString()} - ${endIdx.toLocaleString()}</strong> de <strong>${totalCount.toLocaleString()}</strong> piezas &nbsp;•&nbsp; Pág. <strong>${currentPage}</strong> de <strong>${totalPages}</strong>`}
                </span>
            </div>

            <div class="gis-pagination-right">
                <button type="button" 
                        class="gis-pagination-btn-nav gis-pagination-btn-icon-only ${currentPage <= 1 ? 'disabled-btn' : 'active-btn'}" 
                        id="pieces-btn-first" 
                        title="Primera página" 
                        ${currentPage <= 1 ? 'disabled' : ''}>
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="11 17 6 12 11 7"></polyline>
                        <polyline points="18 17 13 12 18 7"></polyline>
                    </svg>
                </button>

                <button type="button" 
                        class="gis-pagination-btn-nav ${currentPage <= 1 ? 'disabled-btn' : 'active-btn'}" 
                        id="pieces-btn-prev" 
                        title="Página anterior" 
                        ${currentPage <= 1 ? 'disabled' : ''}>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="15 18 9 12 15 6"></polyline>
                    </svg>
                    <span>Anterior</span>
                </button>

                <button type="button" 
                        class="gis-pagination-btn-nav ${currentPage >= totalPages ? 'disabled-btn' : 'active-btn'}" 
                        id="pieces-btn-next" 
                        title="Página siguiente" 
                        ${currentPage >= totalPages ? 'disabled' : ''}>
                    <span>Siguiente</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </button>

                <button type="button" 
                        class="gis-pagination-btn-nav gis-pagination-btn-icon-only ${currentPage >= totalPages ? 'disabled-btn' : 'active-btn'}" 
                        id="pieces-btn-last" 
                        title="Última página" 
                        ${currentPage >= totalPages ? 'disabled' : ''}>
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="13 17 18 12 13 7"></polyline>
                        <polyline points="6 17 11 12 6 7"></polyline>
                    </svg>
                </button>
            </div>
        </nav>
    `;

    document.getElementById("pieces-btn-first")?.addEventListener("click", () => {
        if (currentPage > 1) {
            currentPage = 1;
            renderCurrentPage();
            document.querySelector(".div-table")?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });

    document.getElementById("pieces-btn-prev")?.addEventListener("click", () => {
        if (currentPage > 1) {
            currentPage--;
            renderCurrentPage();
            document.querySelector(".div-table")?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });

    document.getElementById("pieces-btn-next")?.addEventListener("click", () => {
        if (currentPage < totalPages) {
            currentPage++;
            renderCurrentPage();
            document.querySelector(".div-table")?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });

    document.getElementById("pieces-btn-last")?.addEventListener("click", () => {
        if (currentPage < totalPages) {
            currentPage = totalPages;
            renderCurrentPage();
            document.querySelector(".div-table")?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });

    document.getElementById("pieces-page-size-select")?.addEventListener("change", function() {
        pageSize = parseInt(this.value, 10);
        currentPage = 1;
        renderCurrentPage();
    });
}

function crearTabla(piezas, infoPiezas) {
    allMappedPieces = piezas || [];
    currentFilteredPieces = [...allMappedPieces];
    currentPage = 1;
    renderCurrentPage();
    initTableSorting();
}
function asignColorTr(status, error, process) {
    switch (status) {
        case 1:
            return "#79BFED"; // Liberado - Azul
        case 2:
            return "#FF6B6B"; // Rechazado - Rojo
        case 3:
            return "#90EE90"; // Buena sin liberación - Verde
        case 4:
            // Para Soldadura PTA: solo Fundicion bloquea
            if (process === "Soldadura PTA" && !error.toLowerCase().includes("fundicion") && !error.toLowerCase().includes("fundición")) {
                return "#90EE90"; // Verde — defecto no bloqueante para PTA
            }
            return "#DDA0DD"; // Mala sin liberación - Morado
        case 5:
            return "#FFD700"; // Incompleto - Amarillo
        case 0:
        default:
            // Lógica legacy para compatibilidad con piezas antiguas
            if (error.includes("Incompleto")) {
                return "#FFD700"; // Incompleto - Amarillo
            } else if (error == "Ninguno") {
                return "#90EE90"; // Buena sin liberación - Verde
            } else {
                // Para Soldadura PTA: solo Fundicion bloquea, los demás defectos pasan verde
                if (process === "Soldadura PTA" && !error.toLowerCase().includes("fundicion") && !error.toLowerCase().includes("fundición")) {
                    return "#90EE90"; // Verde — defecto no bloqueante para PTA
                }
                return "#DDA0DD"; // Mala sin liberación - Morado
            }
    }
}
function orderedArray(array) {
    // Estructura del backend PHP (saveInArray):
    //  [0] id_ot  [1] n_pieza  [2] operador  [3] maquina  [4] proceso
    //  [5] error  [6] created_at  [7] fecha_liberacion  [8] user_liberacion
    //  [9] liberacion (push)  [10] 'mitad'|'juego' (push)
    //  .observations  .observacion_liberacion  .className  .id_clase
    return {
        class: array["className"],
        workOrder: array[0],
        noAssembly: array[1],
        operator: array[2],
        machine: array[3],
        process: array[4],
        errors: array[5],
        observations: array.observations ?? "",
        machinedDate: array[6],
        liberationDate: array[7],
        user_liberation: array[8],
        observacion_liberacion: array.observacion_liberacion ?? "",
        btn_release: [array[9], array[5] ?? ""],
        btn_decline: array[9],
        btn_seePiece: array[2],
        colorPiece: asignColorTr(array[9], array[5] ?? "", array[4] ?? ""),
    };
}
function crearFecha(fecha) {
    let cadena = "";
    if (fecha != "No liberado") {
        let array = fecha.split(" ");
        cadena = array[0] + "\n " + array[1].slice(0, 8);
    } else {
        cadena = fecha;
    }
    return cadena;
}

// Funciones redundantes (crearBotonLiberar, crearBotonRechazar) eliminadas por refactorización HTML Builder
function create_ObservationsField(keys) {
    //Creacion del div con efcto blur
    let div_opacity = document.createElement("div");
    div_opacity.className = "div-opacity div-opacity-liberation";
    div_opacity.addEventListener("click", () => {
        div_opacity.remove();
    });

    //Creacion del formulario
    let form = document.createElement("form");
    form.action = window.baseUrl + "/piezasLiberar";
    form.method = "POST";
    form.classList.add("form-liberation");
    form.addEventListener("click", (e) => {
        e.stopPropagation();
    });
    form.appendChild(generateToken());
    createInputsHidden(keys, form);

    //Creacion del textarea
    let textArea = document.createElement("textarea");
    textArea.setAttribute("cols", "50");
    textArea.setAttribute("row", "5");
    textArea.setAttribute(
        "placeholder",
        `Agrega una observación para el juego ${keys.pieza[0].toString().split(/[HMJ]/i)[0]}J de ${keys.proceso} (Opcional)`
    );
    textArea.classList.add("textArea-liberation");
    textArea.setAttribute("name", "observationPiece");

    //Creacion del submit
    let submit = document.createElement("input");
    submit.type = "submit";
    submit.value = keys.liberar ? "Liberar" : "Rechazar";
    submit.classList.add("btn-liberation");
    submit.classList.add(keys.liberar ? "btn-liberate" : "btn-reject");

    form.appendChild(textArea);
    form.appendChild(submit);
    div_opacity.appendChild(form);
    document.body.appendChild(div_opacity);
}
function generateToken() {
    let token = document.querySelector('meta[name="csrf-token"]').getAttribute("content");
    let input_token = document.createElement("input");
    input_token.type = "hidden";
    input_token.name = "_token";
    input_token.value = token;
    return input_token;
}
function createInputsHidden(array, form) {
    // Guardar claves del array
    let keysArray = [];
    Object.keys(array).forEach((item) => {
        keysArray.push(item);
    });

    // Crear inputs hidden e insertarlos en el form
    keysArray.forEach((key) => {
        let input = document.createElement("input");
        input.type = "hidden";
        input.name = key;
        input.value = array[key];
        form.appendChild(input);
    });

    //Insertar valores generales
    let input = document.createElement("input");
    input.type = "hidden";
    input.name = "extraRequest";
    input.value = obtenerRequest();
    form.appendChild(input);
}
// crearBotonVer eliminado por refactorización HTML Builder
function obtenerRequest() {
    let names = ["workOrder", "class", "operator", "machine", "process", "error", "dateFrom", "dateTo", "n_juego"];
    let request = [];
    for (let i = 0; i < names.length; i++) {
        let el = document.getElementsByName(names[i])[0];
        let value = el ? el.value : "Todos";

        // Solo reemplazar "/" por "|" en workOrder (no en machine que ya usa "_" para agrupadas)
        if (names[i] === "workOrder" && value) {
            value = value.replaceAll("/", "!"); // Usamos ! para no confundir con el separador de campos |
        }
        request.push(value);
    }
    return request.join("|");
}
function createFilters() {
    // Si no hay piezas o selectedItems es inválido, mostramos advertencia pero permitimos crear estructura si es posible
    if (!window.selectedItems || typeof window.selectedItems !== 'object') {
        console.warn("Filtros con datos limitados: window.selectedItems no es un objeto válido.");
    }
    let titles = {
        workOrder: "Orden de trabajo",
        class: "Clase",
        operator: "Operador",
        machine: "Maquina",
        process: "Proceso",
        error: "Error",
        dateFrom: "Desde",
        dateTo: "Hasta",
        n_juego: "N# Pieza/Juego"
    };
    Object.keys(window.selectedItems).forEach((item) => {
        if (item === "order" || item === "action" || item === "n_juego" || item === "status") return;
        let div = document.createElement("div");
        div.className = "filter filter-" + item;

        switch (item) {
            case "dateFrom":
            case "dateTo":
                createDateFilterTrigger(
                    div,
                    item,
                    window.selectedItems[item] || "",
                    titles[item] || "Fecha"
                );
                break;
            default:
                if (item != "action" && item != "n_juego" && item != "status") {
                    const select = document.createElement("select");
                    select.className = "select-filter";
                    select.name = item;

                    const optionDefault = document.createElement("option");
                    if (item == "operator" && window.selectedItems[item] != "Todos") {
                        optionDefault.value = window.selectedItems[item].matricula;
                        optionDefault.textContent =
                            window.selectedItems[item].nombre +
                            " " +
                            window.selectedItems[item].a_paterno +
                            " " +
                            window.selectedItems[item].a_materno;
                    } else {
                        optionDefault.value = window.selectedItems[item];
                        optionDefault.textContent = window.selectedItems[item];
                    }
                    select.appendChild(optionDefault);
                    if (window.selectedItems[item] != "Todos") {
                        //Crear la opción de Todos
                        const optionAll = document.createElement("option");
                        optionAll.value = "Todos";
                        optionAll.textContent = "Todos";
                        select.appendChild(optionAll);
                    }

                    if (item == "machine") {
                        // Máquinas agrupadas: 1_2, 5_6, 25_26, 27_28 + individuales
                        const pairedMachines = [1, 5, 25, 27];
                        for (let i = 1; i <= 45; i++) {
                            // Si ya es el valor seleccionado por defecto, se saltea
                            const pairedVal = (pairedMachines.includes(i)) ? `${i}_${i + 1}` : null;
                            if (pairedMachines.includes(i)) {
                                // Opción agrupada
                                if (window.selectedItems[item] !== pairedVal) {
                                    const opt = document.createElement("option");
                                    opt.value = pairedVal;
                                    opt.textContent = `Maquina ${i} y ${i + 1}`;
                                    select.appendChild(opt);
                                }
                                // Opción individual A
                                if (window.selectedItems[item] !== String(i)) {
                                    const optA = document.createElement("option");
                                    optA.value = `${i}`;
                                    optA.textContent = `Maquina ${i}`;
                                    select.appendChild(optA);
                                }
                                // Opción individual B
                                if (window.selectedItems[item] !== String(i + 1)) {
                                    const optB = document.createElement("option");
                                    optB.value = `${i + 1}`;
                                    optB.textContent = `Maquina ${i + 1}`;
                                    select.appendChild(optB);
                                }
                                i++;
                            } else {
                                if (window.selectedItems[item] !== String(i)) {
                                    const option = document.createElement("option");
                                    option.value = `${i}`;
                                    option.textContent = `Maquina ${i}`;
                                    select.appendChild(option);
                                }
                            }
                        }
                    } else {
                        let dataToIterate = window.filtersData[item];
                        if (item == "class" && typeof window.getSearchableClassesJS === "function") {
                            window.getSearchableClassesJS().forEach(c => {
                                const formatted = c.split(' ').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
                                if (!dataToIterate.includes(formatted)) {
                                    dataToIterate.push(formatted);
                                }
                            });
                        }

                        if (item == "operator") {
                            dataToIterate = [...window.filtersData[item]].sort((a, b) => {
                                const nameA = `${a.nombre} ${a.a_paterno} ${a.a_materno}`.toLowerCase();
                                const nameB = `${b.nombre} ${b.a_paterno} ${b.a_materno}`.toLowerCase();
                                return nameA.localeCompare(nameB);
                            });
                        }

                        dataToIterate.forEach((key) => {
                            if (item == "operator") {
                                if (key.matricula == window.selectedItems[item].matricula) {
                                    return;
                                }
                            } else {
                                if (key == window.selectedItems[item]) {
                                    return;
                                }
                            }
                            const option = document.createElement("option");
                            option.value = item == "operator" ? key.matricula : key;
                            option.textContent =
                                item == "operator" ? key.nombre + " " + key.a_paterno + " " + key.a_materno : key;
                            select.appendChild(option);
                        });
                    }
                    div.appendChild(select);
                }
                break;
        }
        //Agregar label
        if (item != "action" && item != "n_juego" && item != "status") {
            let label = document.createElement("label");
            label.textContent = titles[item] + ": ";
            div.appendChild(label);
            document.querySelector(".filters").appendChild(div);

            if (item === "error") {
                createStatusFilterUI();
            }
        }
    });

    // ============================================
    // NUEVO FILTRO: N# Pieza/Juego
    // ============================================
    let divGame = document.createElement("div");
    divGame.className = "filter";

    // Crear select
    let selectGame = document.createElement("select");
    selectGame.className = "select-filter";
    selectGame.name = "n_juego";
    selectGame.id = "n_juego_filter";
    selectGame.disabled = true; // Deshabilitado por defecto

    // Opción por defecto
    let defaultOption = document.createElement("option");
    defaultOption.value = "Todos";
    defaultOption.textContent = "Todos";
    selectGame.appendChild(defaultOption);

    // Si viene en selectedItems, ponerle el valor
    if (window.selectedItems && window.selectedItems.n_juego) {
        selectGame.disabled = false;
        if (window.selectedItems.n_juego !== "Todos") {
            let selectedOpt = document.createElement("option");
            selectedOpt.value = window.selectedItems.n_juego;
            selectedOpt.textContent = window.selectedItems.n_juego;
            selectedOpt.selected = true;
            selectGame.appendChild(selectedOpt);
        }
    }

    divGame.appendChild(selectGame);

    let labelGame = document.createElement("label");
    labelGame.textContent = "N# Pieza: ";
    divGame.appendChild(labelGame);

    document.querySelector(".filters").appendChild(divGame);

    // Lógica de activación
    setupGameFilterLogic();

    // Asegurar que los listeners se agreguen SIEMPRE que existan los inputs
    document.querySelectorAll(".input-filter, .select-filter").forEach(el => {
        el.removeEventListener("change", applyAllFilters); // Evitar duplicados
        el.addEventListener("change", applyAllFilters);
    });

    if (window.selectedItems && Object.keys(window.selectedItems).length > 0) {

        // ============================================
        // NUEVO BOTÓN: Limpiar Filtros
        // ============================================
        let divClear = document.createElement("div");
        divClear.className = "filter filter-btn-wrapper";

        let btnClear = document.createElement("button");
        btnClear.id = "btnClearFilters";
        btnClear.textContent = "Limpiar Filtros";
        btnClear.className = "btns btn-clear-filters";
        btnClear.type = "button";
        btnClear.disabled = true;

        btnClear.addEventListener("click", () => {
            if (btnClear.disabled) return;
            sessionStorage.removeItem('adminPiecesFilters');
            sessionStorage.removeItem('currentStatusPersonFilter');
            sessionStorage.removeItem('currentStatusFilter');
            document.querySelectorAll(".select-filter").forEach(select => {
                select.value = "Todos";
                select.dispatchEvent(new Event('change'));
            });
            document.querySelectorAll(".input-filter").forEach(input => {
                input.value = "";
            });
            document.querySelectorAll(".date-trigger-label").forEach(label => {
                label.textContent = "dd/mm/aaaa";
                label.style.color = "#64748b";
                label.style.fontWeight = "500";
                label.style.flex = "1 1 auto";
                label.style.width = "100%";
                label.style.maxWidth = "100%";
                label.classList.add("is-empty");
            });
            document.querySelectorAll(".btn-clear-single-date").forEach(btn => {
                btn.style.display = "none";
            });

            let statusSel = document.getElementById("statusPieceFilter");
            if (statusSel) {
                statusSel.value = "Todos";
                statusSel.dispatchEvent(new Event('change'));
            }

            applyAllFilters();
            if (window.updateClearButtonState) window.updateClearButtonState();
        });

        divClear.appendChild(btnClear);
        document.querySelector(".filters").appendChild(divClear);

        // Función para actualizar el estado del botón
        window.updateClearButtonState = () => {
            let hasFilters = false;
            document.querySelectorAll(".select-filter").forEach(s => {
                if (s.value && s.value !== "Todos" && s.value !== "") hasFilters = true;
            });
            document.querySelectorAll(".input-filter").forEach(i => {
                if (i.value && i.value !== "" && i.value !== "Todos") hasFilters = true;
            });

            btnClear.disabled = !hasFilters;
        };
        window.updateClearButtonState();
    }
}
createFilters();

// ============================================
// FUNCIONALIDAD: Filtros en Cascada (OT, Clase, Procesos)
// ============================================
function setupCascadingFilters() {
    const otSelect = document.querySelector('select[name="workOrder"]');
    const classSelect = document.querySelector('select[name="class"]');
    const processSelect = document.querySelector('select[name="process"]');

    if (!otSelect || !classSelect || !processSelect) return;

    function extractOptions(select) {
        let opts = [];
        let seen = new Set();
        Array.from(select.options).forEach(o => {
            if (!seen.has(o.value)) {
                seen.add(o.value);
                opts.push({ value: o.value, text: o.textContent });
            }
        });
        return opts;
    }

    const originalOTOptions = extractOptions(otSelect);
    const originalClassOptions = extractOptions(classSelect);
    const originalProcessOptions = extractOptions(processSelect);

    function updateCascadingFilters() {
        if (!window.pieces || window.pieces.length === 0) return;

        let anySelectionChanged = false;

        function refreshSelect(selectEl, originalOptions, activeSet, isOT) {
            const currentVal = selectEl.value;
            while (selectEl.options.length > 0) selectEl.remove(0);

            let hasCurrentVal = false;

            originalOptions.forEach(opt => {
                if (opt.value === "Todos") {
                    let o = document.createElement("option");
                    o.value = opt.value;
                    o.textContent = opt.text;
                    selectEl.appendChild(o);
                    if (currentVal === "Todos") hasCurrentVal = true;
                    return;
                }

                let isActive = false;
                if (isOT) {
                    let optId = String(opt.value).split(" - ")[0].trim();
                    isActive = activeSet.has(optId);
                } else {
                    isActive = activeSet.has(opt.value);
                }

                if (isActive) {
                    let o = document.createElement("option");
                    o.value = opt.value;
                    o.textContent = opt.text;
                    selectEl.appendChild(o);
                    if (currentVal === opt.value) hasCurrentVal = true;
                }
            });

            if (hasCurrentVal) {
                selectEl.value = currentVal;
            } else {
                selectEl.value = "Todos";
                anySelectionChanged = true;
            }
        }

        // 1. OT activas globales - Filtrar solo las OTs que tienen registros en la tabla actual
        let activeOTIds = new Set();
        window.pieces.forEach(p => activeOTIds.add(String(p[0]).trim()));
        refreshSelect(otSelect, originalOTOptions, activeOTIds, true);
        // 2. Clases activas para OT
        const finalOT = otSelect.value;
        let activeClasses = new Set();
        window.pieces.forEach(p => {
            let pieceOT = String(p[0]).trim();
            let matchOT = finalOT === "Todos" ? true : (pieceOT === String(finalOT).split(" - ")[0].trim());
            if (matchOT) {
                activeClasses.add(String(p.className).trim());
            }
        });
        refreshSelect(classSelect, originalClassOptions, activeClasses, false);

        // 3. Procesos activos para OT y Clase
        const finalClass = classSelect.value;
        let activeProcesses = new Set();
        window.pieces.forEach(p => {
            let pieceOT = String(p[0]).trim();
            let pieceClass = String(p.className).trim();

            let matchOT = finalOT === "Todos" ? true : (pieceOT === String(finalOT).split(" - ")[0].trim());
            let matchClass = finalClass === "Todos" ? true : (pieceClass === finalClass);

            if (matchOT && matchClass) {
                activeProcesses.add(String(p[4]).trim());
            }
        });
        refreshSelect(processSelect, originalProcessOptions, activeProcesses, false);

        if (anySelectionChanged && typeof applyAllFilters === 'function') {
            applyAllFilters();
        }
    }

    // Inicializar cascada con valores actuales
    updateCascadingFilters();

    // Event listeners
    otSelect.addEventListener('change', updateCascadingFilters);
    classSelect.addEventListener('change', updateCascadingFilters);
}

setupCascadingFilters();

function createStatusFilterUI() {
    let divStatus = document.createElement("div");
    divStatus.className = "filter";

    let selectStatus = document.createElement("select");
    selectStatus.className = "select-filter";
    selectStatus.id = "statusPieceFilter";
    selectStatus.name = "status";

    const statuses = [
        { value: "Todos", text: "Todos" },
        { value: "#79BFED", text: "Liberadas" },
        { value: "#FF6B6B", text: "Rechazadas" },
        { value: "#90EE90", text: "Buenas sin liberación" },
        { value: "#DDA0DD", text: "Malas sin liberación" },
        { value: "#FFD700", text: "Incompletas" }
    ];

    let savedStatus = sessionStorage.getItem("currentStatusFilter") || "Todos";

    statuses.forEach(s => {
        let opt = document.createElement("option");
        opt.value = s.value;
        opt.textContent = s.text;
        if (s.value !== "Todos") {
            opt.style.backgroundColor = s.value;
            opt.style.color = (s.value === "#FFD700" || s.value === "#90EE90") ? "#000" : "#FFF";
        }
        if (s.value === savedStatus) opt.selected = true;
        selectStatus.appendChild(opt);
    });

    selectStatus.addEventListener("change", function () {
        sessionStorage.setItem("currentStatusFilter", this.value);
        applyAllFilters();
        updateStatusPersonFilter(this.value);
    });

    divStatus.appendChild(selectStatus);

    let labelStatus = document.createElement("label");
    labelStatus.textContent = "Estado: ";
    divStatus.appendChild(labelStatus);

    let filtersContainer = document.querySelector(".filters");
    if (filtersContainer) filtersContainer.appendChild(divStatus);

    // ============================================
    // FILTRO CONDICIONAL: Persona (activo solo cuando hay estado seleccionado)
    // ============================================
    createStatusPersonFilterUI();
    setupHeaderOrderBadge();

    // Si ya hay un estado guardado en sesión, activar el filtro de persona
    if (savedStatus && savedStatus !== "Todos") {
        setTimeout(() => updateStatusPersonFilter(savedStatus), 0);
    }
}

/**
 * Obtiene la URL del icono Light para encabezados de tabla (TH).
 */
function getThSortIcon(name) {
    if (name === "sin_orden") return window.iconSinOrdenLight || (window.getIcon ? window.getIcon("sin_orden_light") : "");
    if (name === "orden_asc") return window.iconOrdenAscLight || (window.getIcon ? window.getIcon("orden_asc_light") : "");
    if (name === "orden_desc") return window.iconOrdenDescLight || (window.getIcon ? window.getIcon("orden_desc_light") : "");
    return "";
}

/**
 * Obtiene la URL del icono Shadow para el indicador/badge de orden superior izquierdo.
 */
function getShadowSortIcon(name) {
    if (name === "sin_orden") return window.iconSinOrdenShadow || (window.getIcon ? window.getIcon("sin_orden_shadow") : "");
    if (name === "orden_asc") return window.iconOrdenAscShadow || (window.getIcon ? window.getIcon("orden_asc_shadow") : "");
    if (name === "orden_desc") return window.iconOrdenDescShadow || (window.getIcon ? window.getIcon("orden_desc_shadow") : "");
    return "";
}

/**
 * Configura el botón/indicador de orden ubicado en la parte superior izquierda.
 */
function setupHeaderOrderBadge() {
    const badge = document.getElementById("headerOrderBadge");
    if (badge) {
        badge.addEventListener("click", () => {
            resetAllSorting();
        });
    }
}

let tableSortingInitialized = false;

/**
 * Inicializa el ordenamiento por clic en los encabezados (TH) de la tabla.
 */
function initTableSorting() {
    const table = document.querySelector(".table");
    if (!table) return;
    const thead = table.querySelector("thead");
    if (!thead) return;
    const ths = thead.querySelectorAll("th");
    if (ths.length === 0) return;

    if (tableSortingInitialized) return;
    tableSortingInitialized = true;

    const nonSortable = ["ver", "liberar", "rechazar"];

    ths.forEach((th, index) => {
        const rawText = th.textContent.trim();
        const lowerText = rawText.toLowerCase();
        if (nonSortable.includes(lowerText)) return;

        th.classList.add("sortable-th");
        th.dataset.colIndex = index;
        th.dataset.sortDir = "none";
        th.title = `Clic para ordenar por ${rawText} (Asc / Desc / Sin orden)`;

        th.innerHTML = `
            <div class="th-content">
                <span class="th-title">${rawText}</span>
                <img class="th-sort-icon" src="${getThSortIcon('sin_orden')}" alt="orden">
            </div>
        `;

        th.addEventListener("click", () => {
            const currentDir = th.dataset.sortDir || "none";
            let nextDir = "asc";
            if (currentDir === "asc") nextDir = "desc";
            else if (currentDir === "desc") nextDir = "none";

            // Resetear todos los demás encabezados
            ths.forEach(otherTh => {
                if (otherTh.classList.contains("sortable-th")) {
                    otherTh.dataset.sortDir = "none";
                    const otherImg = otherTh.querySelector(".th-sort-icon");
                    if (otherImg) otherImg.src = getThSortIcon("sin_orden");
                }
            });

            // Asignar estado a este th
            th.dataset.sortDir = nextDir;
            const myImg = th.querySelector(".th-sort-icon");
            if (myImg) {
                myImg.src = getThSortIcon(nextDir === "none" ? "sin_orden" : (nextDir === "asc" ? "orden_asc" : "orden_desc"));
            }

            updateOrderFilterBadge(rawText, nextDir);
            sortTable(index, nextDir);
        });
    });
}

/**
 * Ordena las filas del tbody según la columna y dirección especificada.
 */
function sortTable(colIndex, direction) {
    currentSortColumn = colIndex;
    currentSortDirection = direction;

    if (direction === "none") {
        currentFilteredPieces.sort((a, b) => {
            const idxA = a.originalIndex !== undefined ? a.originalIndex : 0;
            const idxB = b.originalIndex !== undefined ? b.originalIndex : 0;
            return idxA - idxB;
        });
    } else {
        const colKeys = [
            "class", "workOrder", "noAssembly", "operator", "machine",
            "process", "errors", "observations", "machinedDate",
            "liberationDate", "user_liberation", "observacion_liberacion"
        ];
        const key = colKeys[colIndex];

        currentFilteredPieces.sort((a, b) => {
            let valA = (key && a.ordered[key] !== undefined && a.ordered[key] !== null) ? String(a.ordered[key]).trim() : "";
            let valB = (key && b.ordered[key] !== undefined && b.ordered[key] !== null) ? String(b.ordered[key]).trim() : "";

            if (valA === valB) return 0;
            if (valA === "" || valA === "No liberado" || valA === "N/A") return 1;
            if (valB === "" || valB === "No liberado" || valB === "N/A") return -1;

            if (key === "noAssembly") {
                const matchA = valA.match(/^(\d+)(.*)$/);
                const matchB = valB.match(/^(\d+)(.*)$/);
                if (matchA && matchB) {
                    const numDiff = parseInt(matchA[1], 10) - parseInt(matchB[1], 10);
                    if (numDiff !== 0) return direction === "asc" ? numDiff : -numDiff;
                    const suffixDiff = matchA[2].localeCompare(matchB[2]);
                    return direction === "asc" ? suffixDiff : -suffixDiff;
                }
            }

            const numA = Number(valA);
            const numB = Number(valB);
            if (!isNaN(numA) && !isNaN(numB) && valA !== "" && valB !== "") {
                return direction === "asc" ? numA - numB : numB - numA;
            }

            const comp = valA.localeCompare(valB, 'es', { numeric: true, sensitivity: 'base' });
            return direction === "asc" ? comp : -comp;
        });
    }

    currentPage = 1;
    renderCurrentPage();
}

/**
 * Restablece el orden de todas las columnas al original.
 */
function resetAllSorting() {
    const ths = document.querySelectorAll(".table thead th.sortable-th");
    ths.forEach(th => {
        th.dataset.sortDir = "none";
        const img = th.querySelector(".th-sort-icon");
        if (img) img.src = getThSortIcon("sin_orden");
    });
    updateOrderFilterBadge(null, "none");
    currentSortColumn = -1;
    currentSortDirection = "none";
    sortTable(0, "none");
}

/**
 * Actualiza el texto y el icono del indicador superior de orden.
 */
function updateOrderFilterBadge(columnName, direction) {
    const icon = document.getElementById("headerOrderIcon") || document.getElementById("orderFilterIcon");
    const text = document.getElementById("headerOrderText") || document.getElementById("orderFilterText");
    if (!icon || !text) return;

    if (direction === "asc") {
        icon.src = getShadowSortIcon("orden_asc");
        text.textContent = columnName ? `${columnName} (Asc)` : "Ascendente";
    } else if (direction === "desc") {
        icon.src = getShadowSortIcon("orden_desc");
        text.textContent = columnName ? `${columnName} (Desc)` : "Descendente";
    } else {
        icon.src = getShadowSortIcon("sin_orden");
        text.textContent = "Sin orden";
    }
}

/**
 * Crea el contenedor del filtro "Persona" (oculto por defecto).
 * Se activa dinámicamente cuando el usuario selecciona un Estado.
 */
function createStatusPersonFilterUI() {
    let divPerson = document.createElement("div");
    divPerson.className = "filter";
    divPerson.id = "statusPersonFilterDiv";
    divPerson.hidden = true; // Oculto por defecto

    let selectPerson = document.createElement("select");
    selectPerson.className = "select-filter";
    selectPerson.id = "statusPersonFilter";
    selectPerson.name = "statusPerson";

    let defaultOpt = document.createElement("option");
    defaultOpt.value = "Todos";
    defaultOpt.textContent = "Todos";
    selectPerson.appendChild(defaultOpt);

    selectPerson.addEventListener("change", function () {
        sessionStorage.setItem("currentStatusPersonFilter", this.value);
        applyAllFilters();
        if (window.updateClearButtonState) window.updateClearButtonState();
    });

    divPerson.appendChild(selectPerson);

    let labelPerson = document.createElement("label");
    labelPerson.textContent = "Liberó: ";
    divPerson.appendChild(labelPerson);

    let filtersContainer = document.querySelector(".filters");
    if (filtersContainer) filtersContainer.appendChild(divPerson);
}

/**
 * Actualiza las opciones del filtro "Persona" según el estado seleccionado.
 * Extrae de las filas de la tabla solo las personas activas para ese estado.
 * @param {string} statusValue - El valor hex del estado seleccionado (o "Todos")
 */
function updateStatusPersonFilter(statusValue) {
    const divPerson = document.getElementById("statusPersonFilterDiv");
    const selectPerson = document.getElementById("statusPersonFilter");
    if (!divPerson || !selectPerson) return;

    if (!statusValue || statusValue === "Todos") {
        divPerson.hidden = true;
        selectPerson.value = "Todos";
        while (selectPerson.options.length > 1) selectPerson.remove(1);
        return;
    }

    // Liberadas/Rechazadas → liberador (celda índice 10 en releasePieces)
    // Resto → operador (data-operator)
    const useOperator = (statusValue === "#90EE90" || statusValue === "#DDA0DD" || statusValue === "#FFD700");

    let personSet = new Set();
    const sourcePieces = (allMappedPieces && allMappedPieces.length > 0) ? allMappedPieces : [];

    sourcePieces.forEach(item => {
        let ord = item.ordered;
        let rowColor = (ord.colorPiece || "").toUpperCase();
        if (rowColor !== statusValue.toUpperCase()) return;

        if (useOperator) {
            let ops = String(ord.operator || "").split("/").map(o => o.trim()).filter(Boolean);
            ops.forEach(op => { if (op) personSet.add(op); });
        } else {
            let liberador = String(ord.user_liberation || "").trim();
            if (liberador && liberador !== "No liberado" && liberador !== "—") personSet.add(liberador);
        }
    });

    let prevValue = selectPerson.value;
    while (selectPerson.options.length > 1) selectPerson.remove(1);

    let sortedPersons = Array.from(personSet).filter(Boolean).sort((a, b) => a.localeCompare(b));
    sortedPersons.forEach(person => {
        let opt = document.createElement("option");
        opt.value = person;
        opt.textContent = person;
        selectPerson.appendChild(opt);
    });

    const savedPerson = sessionStorage.getItem("currentStatusPersonFilter");
    if (savedPerson && sortedPersons.includes(savedPerson)) {
        selectPerson.value = savedPerson;
    } else if (prevValue !== "Todos" && sortedPersons.includes(prevValue)) {
        selectPerson.value = prevValue;
    } else {
        selectPerson.value = "Todos";
    }

    divPerson.hidden = false;
}

function applyAllFilters() {
    const getVal = (name) => {
        let el = document.querySelector(`[name="${name}"]`);
        if (!el) return "Todos";
        if (name === "operator") {
            return (el.options && el.selectedIndex >= 0 && el.options[el.selectedIndex])
                ? el.options[el.selectedIndex].textContent.trim()
                : "Todos";
        }
        if (name === "dateFrom" || name === "dateTo") {
            let v = el.value ? el.value.trim() : "";
            return (v === "Todos" || !v || !/^\d{4}-\d{2}-\d{2}$/.test(v)) ? "" : v;
        }
        return el.value !== undefined ? el.value.trim() : "Todos";
    };

    let f = {
        workOrder: getVal("workOrder"),
        class: getVal("class"),
        operator: getVal("operator"),
        machine: getVal("machine"),
        process: getVal("process"),
        error: getVal("error"),
        dateFrom: getVal("dateFrom"),
        dateTo: getVal("dateTo"),
        n_juego: getVal("n_juego")
    };

    let statusFilterEl = document.getElementById("statusPieceFilter");
    let statusFilter = statusFilterEl ? statusFilterEl.value : "Todos";

    let statusPersonFilterEl = document.getElementById("statusPersonFilter");
    let statusPersonFilter = statusPersonFilterEl ? statusPersonFilterEl.value : "Todos";

    const personUsesOperator = (statusFilter === "#90EE90" || statusFilter === "#DDA0DD" || statusFilter === "#FFD700");

    currentFilteredPieces = allMappedPieces.filter(data => {
        let ord = data.ordered;

        if (f.workOrder && f.workOrder !== "Todos") {
            let dsId = String(ord.workOrder).split(' ')[0].split('-')[0].trim();
            let fId = String(f.workOrder).split(' ')[0].split('-')[0].trim();
            if (dsId !== fId) return false;
        }

        if (f.class && f.class !== "Todos" && String(ord.class).trim() !== f.class) return false;

        if (f.operator && f.operator !== "Todos") {
            let rowOperators = String(ord.operator).split('/').map(op => op.trim());
            if (!rowOperators.includes(f.operator)) return false;
        }

        if (f.machine && f.machine !== "Todos") {
            let mach = f.machine.replace(" y ", "_");
            let strMach = String(ord.machine).trim();
            if (f.machine.includes("_")) {
                let parts = f.machine.split("_");
                if (!parts.includes(strMach) && strMach !== f.machine && strMach !== mach) return false;
            } else {
                if (strMach !== f.machine && strMach !== mach) return false;
            }
        }

        if (f.process && f.process !== "Todos" && String(ord.process).trim() !== f.process) return false;

        if (f.error && f.error !== "Todos") {
            let err = typeof ord.errors === "string" && ord.errors.trim() !== "" ? ord.errors : "Ninguno";
            if (f.error === "Ninguno") {
                if (err.toLowerCase() !== "ninguno" && err !== "") return false;
            } else {
                if (!err.toLowerCase().includes(f.error.toLowerCase())) return false;
            }
        }

        if (f.n_juego && f.n_juego !== "Todos" && ord.noAssembly) {
            let numPiece = String(ord.noAssembly).replace(/[^0-9]/g, "");
            let numFilter = String(f.n_juego).replace(/[^0-9]/g, "");
            if (numPiece !== numFilter && String(ord.noAssembly).trim() !== f.n_juego) return false;
        }

        if (statusFilter !== "Todos" && ord.colorPiece) {
            if (ord.colorPiece.toUpperCase() !== statusFilter.toUpperCase()) return false;
        }

        if (statusFilter !== "Todos" && statusPersonFilter && statusPersonFilter !== "Todos") {
            if (personUsesOperator) {
                let rowOps = String(ord.operator || "").split("/").map(o => o.trim());
                if (!rowOps.includes(statusPersonFilter)) return false;
            } else {
                let liberador = String(ord.user_liberation || "").trim();
                if (liberador !== statusPersonFilter) return false;
            }
        }

        if (ord.machinedDate && ord.machinedDate.trim() !== "" && ord.machinedDate !== "No liberado") {
            let dsDate = ord.machinedDate.replace(/\n/g, "").trim().split(" ")[0];
            if (f.dateFrom && f.dateFrom !== "" && f.dateFrom !== "Todos") {
                if (dsDate < f.dateFrom) return false;
            }
            if (f.dateTo && f.dateTo !== "" && f.dateTo !== "Todos") {
                if (dsDate > f.dateTo) return false;
            }
        }

        return true;
    });

    if (currentSortColumn >= 0 && currentSortDirection !== "none") {
        sortTable(currentSortColumn, currentSortDirection);
    } else {
        currentPage = 1;
        renderCurrentPage();
    }

    if (currentFilteredPieces.length === 0) {
        if (typeof window.gisToast === "function") {
            window.gisToast("No se encontraron registros para los filtros aplicados", "warning");
        }
    }

    if (window.updateClearButtonState) window.updateClearButtonState();

    let curStatus = statusFilterEl ? statusFilterEl.value : "Todos";
    if (curStatus !== "Todos" && typeof updateStatusPersonFilter === "function") {
        updateStatusPersonFilter(curStatus);
    }

    sessionStorage.setItem('adminPiecesFilters', JSON.stringify(f));
}

function sortPiezasDatabaseOrder(piezas, infoPiezas) {
    // 1. Pre-mapear todas las piezas primero para evitar miles de llamadas redundantes a orderedArray
    let mapped = piezas.map((p, i) => {
        if (!p || i >= infoPiezas.length) return null;
        return {
            original: p,
            info: infoPiezas[i],
            ordered: orderedArray(p),
            originalIndex: i
        };
    }).filter(m => m !== null);

    const classOrder = typeof window.getSearchableClassesJS === "function"
        ? window.getSearchableClassesJS().map(c => c.split(' ').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' '))
        : [];
    const processOrder = [
        "Cepillado", "Desbaste Exterior", "Revision Laterales", "Primera Operacion",
        "Barreno Maniobra", "Segunda Operacion", "Soldadura", "Soldadura PTA",
        "Rectificado", "Asentado", "Calificado", "Acabado Bombillo", "Acabado Molde",
        "Barreno Profundidad", "Cavidades", "Copiado", "Off Set", "Palomas",
        "Rebajes", "Operacion Equipo_1 operacion", "Operacion Equipo_2 operacion",
        "Candado Obturador_1 operacion", "Candado Obturador_2 operacion",
        "Embudo CM", "Primera Operacion Cabeza Soplo", "Segunda Operacion Cabeza Soplo"
    ];

    mapped.sort((a, b) => {
        let pA = a.ordered;
        let pB = b.ordered;

        if (!pA || !pB) return 0; // Guard contra elementos corruptos

        // 1. Orden por OT (Numérico)
        let otA = parseInt(pA.workOrder) || 0;
        let otB = parseInt(pB.workOrder) || 0;
        if (otA !== otB) return otA - otB;

        // 2. Orden por Clase
        let cIdxA = classOrder.indexOf(pA.class);
        let cIdxB = classOrder.indexOf(pB.class);
        if (cIdxA === -1) cIdxA = 999;
        if (cIdxB === -1) cIdxB = 999;
        if (cIdxA !== cIdxB) return cIdxA - cIdxB;

        // 3. Orden por Proceso
        let pIdxA = processOrder.indexOf(pA.process);
        let pIdxB = processOrder.indexOf(pB.process);
        if (pIdxA === -1) pIdxA = 999;
        if (pIdxB === -1) pIdxB = 999;
        if (pIdxA !== pIdxB) return pIdxA - pIdxB;

        // 4. Orden por número de pieza/juego
        let numA = parseInt(String(pA.noAssembly).replace(/[^0-9]/g, "")) || 0;
        let numB = parseInt(String(pB.noAssembly).replace(/[^0-9]/g, "")) || 0;
        if (numA !== numB) return numA - numB;

        return 0;
    });

    // Reconstruir los arreglos originales en el nuevo orden optimizado
    return {
        mapped: mapped,
        piezas: mapped.map(m => m.original),
        infoPiezas: mapped.map(m => m.info)
    };
}

if (window.pieces.length > 0) {
    let sortedData = sortPiezasDatabaseOrder(window.pieces, window.infoPiezas);
    // Pasamos el array de objetos ya mapeados {original, info, ordered} para evitar re-calculo
    crearTabla(sortedData.mapped, null);
}
const pdf = document.getElementById("pdf");

/**
 * Lógica para el filtro de N# Pieza/Juego
 */
function setupGameFilterLogic() {
    // releasePieces usa "workOrder" y "class"
    const otSelect = document.querySelector('select[name="workOrder"]');
    const classSelect = document.querySelector('select[name="class"]');
    const gameSelect = document.getElementById("n_juego_filter");

    function checkEnableGameFilter() {
        if (!otSelect || !classSelect || !gameSelect) return;

        const otVal = otSelect.value;
        const classVal = classSelect.value;
        const gameContainer = gameSelect.closest('.filter');

        if (otVal && otVal !== "Todos" && classVal && classVal !== "Todos") {
            // Mostrar y habilitar el filtro
            if (gameContainer) gameContainer.hidden = false;
            gameSelect.disabled = false;
            // Cargar juegos
            loadAvailableGames(otVal, classVal, gameSelect);
        } else {
            // Ocultar y deshabilitar el filtro
            if (gameContainer) gameContainer.hidden = true;
            gameSelect.disabled = true;
            gameSelect.value = "Todos";
            while (gameSelect.options.length > 1) {
                gameSelect.remove(1);
            }
            applyAllFilters();
        }
    }

    if (otSelect) otSelect.addEventListener("change", checkEnableGameFilter);
    if (classSelect) classSelect.addEventListener("change", checkEnableGameFilter);

    // Ocultar por defecto al inicializar
    const gameContainer = gameSelect?.closest('.filter');
    if (gameContainer) gameContainer.hidden = true;
}

/**
 * Cargar juegos disponibles
 */
function loadAvailableGames(ot, clase, selectElement) {
    // Limpiar opciones existentes (excepto "Todos")
    while (selectElement.options.length > 1) {
        selectElement.remove(1);
    }

    console.log("=== loadAvailableGames DEBUG ===");
    console.log("OT:", ot, "Clase:", clase);
    console.log("window.pieces exists:", !!window.pieces);
    console.log("window.pieces length:", window.pieces?.length);

    // Estrategia 1: Intentar usar datos locales primero
    let gamesLoaded = false;

    if (window.pieces && window.pieces.length > 0) {
        const games = new Set();

        window.pieces.forEach((p, index) => {
            if (index < 3) { // Log solo las primeras 3 piezas
                console.log(`Piece ${index}:`, {
                    ot: p[0],
                    className: p.className,
                    noAssembly: p[1]
                });
            }

            // p[0] es OT, p.className es Clase, p[1] es JUEGO (noAssembly)
            if (p[0] && p.className && p[1]) {
                const pieceOT = String(p[0]).trim();
                const pieceClass = String(p.className).trim();

                // Extraer solo el número del OT (antes del " - ")
                const selectedOT = String(ot).includes(" - ")
                    ? String(ot).split(" - ")[0].trim()
                    : String(ot).trim();
                const selectedClass = String(clase).trim();

                if (pieceOT === selectedOT && pieceClass === selectedClass) {
                    games.add(p[1]);
                }
            }
        });

        console.log("Games found:", Array.from(games));

        if (games.size > 0) {
            // Ordenar y agregar opciones
            const sortedGames = Array.from(games).sort();
            sortedGames.forEach(game => {
                let opt = document.createElement("option");
                opt.value = game;
                opt.textContent = game;
                if (window.selectedItems && window.selectedItems.n_juego == game) {
                    opt.selected = true;
                }
                selectElement.appendChild(opt);
            });
            gamesLoaded = true;
            console.log("Games loaded from local data successfully");
        } else {
            console.log("No games matched for OT:", ot, "Class:", clase);
        }
    } else {
        console.log("window.pieces is empty or undefined");
    }

    // Estrategia 2: Si no hay datos locales, intentar AJAX
    if (!gamesLoaded) {
        console.log("Attempting AJAX load...");
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");

        if (csrfToken) {
            fetch(window.baseUrl + "/getGamesFromOT", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                    "Accept": "application/json"
                },
                body: JSON.stringify({ ot: ot, class: clase })
            })
                .then(response => {
                    if (response.ok) return response.json();
                    throw new Error("Network response was not ok");
                })
                .then(data => {
                    console.log("AJAX response:", data);
                    if (data && Array.isArray(data)) {
                        data.forEach(game => {
                            let opt = document.createElement("option");
                            opt.value = game;
                            opt.textContent = game;
                            if (window.selectedItems && window.selectedItems.n_juego == game) {
                                opt.selected = true;
                            }
                            selectElement.appendChild(opt);
                        });
                    }
                })
                .catch(err => {
                    console.log("AJAX failed:", err);
                });
        }
    }
}



