var operacion = false;

function crearTabla(piezas, infoPiezas) {
    const table = document.querySelector(".table");
    const tbody = document.createElement("tbody");
    table.appendChild(tbody);
    
    // Delegación de eventos para los botones de liberar/rechazar
    tbody.addEventListener("click", (e) => {
        const btn = e.target.closest(".btn-action-liberar, .btn-action-rechazar");
        if (!btn) return;
        e.preventDefault();

        const dataPieza = JSON.parse(btn.getAttribute("data-pieza"));
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

    const CHUNK_SIZE = 500;
    let index = 0;
    const esc = (v) => v ? String(v).replace(/"/g, '&quot;').replace(/>/g, '&gt;').replace(/</g, '&lt;') : '';

    // releasePieces usa 'quality' como perfil por defecto
    const profileValue = 'quality';

    function renderNextChunk() {
        let chunkHtml = "";
        const limit = Math.min(index + CHUNK_SIZE, piezas.length);

        for (; index < limit; index++) {
            let piezaOG = piezas[index];
            let infoP = infoPiezas[index];
            let piezasArray = infoP[0]; // Las piezas originales para el JSON
            let pieza = orderedArray(piezaOG);
            
            chunkHtml += `<tr style="background-color: ${pieza.colorPiece};" 
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
                        // Logic de negocio original
                        if (!pieza[key][1].includes("Incompleto") && pieza[key][0] != 1) {
                            let bool = (infoP[2] == "Ninguno" && piezaOG[9] != 2);
                            btnHtml = `<a class="btn-liberar btn-action-liberar" style="cursor:pointer;" data-pieza='${JSON.stringify(piezasArray)}' data-proceso="${infoP[1]}" data-buena="${bool}"><img src="${window.liberar}" alt="Liberar" class="ver"></a>`;
                        }
                        chunkHtml += `<td>${btnHtml}</td>`;
                    } else if (key === "btn_decline") {
                        let btnHtml = "";
                        if (pieza[key] != 2) {
                            btnHtml = `<a class="btn-liberar btn-action-rechazar" style="cursor:pointer;" data-pieza='${JSON.stringify(piezasArray)}' data-proceso="${infoP[1]}"><img src="${window.rechazar}" alt="Rechazar" class="ver"></a>`;
                        }
                        chunkHtml += `<td>${btnHtml}</td>`;
                    } else if (key === "btn_seePiece") {
                        let nPiezas = piezasArray.join(",");
                        let url = `${window.baseUrl}/pieces/${nPiezas}/${infoP[1]}/${profileValue}`;
                        chunkHtml += `<td><a class="btn-pza" href="${url}"><img src="${window.ojito}" alt="Ver" class="ver"></a></td>`;
                    } else {
                        let cellClass = (key === "operator" || key === "observations" || key === "observacion_liberacion") ? ' class="wide-cell"' : '';
                        chunkHtml += `<td${cellClass}>${cellValue}</td>`;
                    }
                }
            });
            chunkHtml += `</tr>`;
        }

        tbody.innerHTML += chunkHtml;

        if (index < piezas.length) {
            requestAnimationFrame(renderNextChunk);
        } else {
            applyAllFilters();
            const loading = document.querySelector('.loading');
            if(loading) loading.style.display = 'none';
        }
    }

    renderNextChunk();
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
    div_opacity.className = "div-opacity";
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
        `Agrega una observación para el juego ${keys.pieza[0].slice(0, -2)}J de ${keys.proceso} (Opcional)`
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
        let value = document.getElementsByName(names[i])[0].value.replaceAll("/", "_");
        request.push(value);
    }
    return request;
}
function createFilters() {
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
        let div = document.createElement("div");
        div.className = "filter";

        switch (item) {
            case "dateFrom":
            case "dateTo":
                let input = document.createElement("input");
                input.type = "date";
                input.name = item;
                input.className = "input-filter";
                input.value = window.selectedItems[item];
                div.appendChild(input);
                break;
            default:
                if (item != "action" && item != "n_juego") {
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
                        // Generar 45 máquinas como en processProduction.js
                        for (let i = 1; i <= 45; i++) {
                            if (i == window.selectedItems[item] || (window.selectedItems[item].includes("_") && window.selectedItems[item] == `${i}_${i + 1}`)) {
                                if (i == 1 || i == 25 || i == 27) {
                                    i++; // Saltar la siguiente iteración para máquinas agrupadas
                                }
                                continue;
                            }
                            const option = document.createElement("option");
                            if (i == 1 || i == 25 || i == 27) {
                                option.value = `${i}_${i + 1}`;
                                option.textContent = `Maquina ${i} y ${i + 1}`;
                                i++;
                            } else {
                                option.value = `${i}`;
                                option.textContent = `Maquina ${i}`;
                            }
                            select.appendChild(option);
                        }
                    } else {
                        let dataToIterate = window.filtersData[item];
                        if (item == "class" && !dataToIterate.includes("Cabeza de Soplo")) {
                            dataToIterate.push("Cabeza de Soplo");
                        }
                        if (item == "class" && !dataToIterate.includes("Candado Obturador")) {
                            dataToIterate.push("Candado Obturador");
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
        if (item != "action" && item != "n_juego") {
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

    if (Object.keys(window.selectedItems).length > 0) {
        document.querySelectorAll(".input-filter, .select-filter").forEach(el => {
            el.addEventListener("change", applyAllFilters);
        });

        // ============================================
        // NUEVO BOTÓN: Limpiar Filtros
        // ============================================
        let btnClear = document.createElement("button");
        btnClear.id = "btnClearFilters";
        btnClear.textContent = "Limpiar Filtros";
        btnClear.className = "btns btn-clear-filters";
        btnClear.type = "button";
        btnClear.disabled = true;

        btnClear.addEventListener("click", () => {
            if (btnClear.disabled) return;
            document.querySelectorAll(".select-filter").forEach(select => {
                select.value = "Todos";
                select.dispatchEvent(new Event('change'));
            });
            document.querySelectorAll(".input-filter").forEach(input => {
                input.value = "";
            });
            
            let statusSel = document.getElementById("statusPieceFilter");
            if (statusSel) {
                statusSel.value = "Todos";
                statusSel.dispatchEvent(new Event('change'));
                sessionStorage.setItem("currentStatusFilter", "Todos");
            }

            applyAllFilters();
        });

        document.querySelector(".filters").appendChild(btnClear);

        // Función para actualizar el estado del botón
        window.updateClearButtonState = () => {
            let hasFilters = false;
            document.querySelectorAll(".select-filter").forEach(s => {
                if (s.value !== "Todos") hasFilters = true;
            });
            document.querySelectorAll(".input-filter").forEach(i => {
                if (i.value !== "") hasFilters = true;
            });

            btnClear.disabled = !hasFilters;
        };
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

        // 1. OT activas globales
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
    });

    divStatus.appendChild(selectStatus);

    let labelStatus = document.createElement("label");
    labelStatus.textContent = "Estado: ";
    divStatus.appendChild(labelStatus);

    let filtersContainer = document.querySelector(".filters");
    if (filtersContainer) filtersContainer.appendChild(divStatus);
}

function applyAllFilters() {
    let visibleCount = 0;
    const getVal = (name) => {
        let el = document.querySelector(`[name="${name}"]`);
        if (!el) return "Todos";
        if (name === "operator") {
            return el.options[el.selectedIndex].textContent.trim();
        }
        return el.value.trim();
    };

    let statusFilterEl = document.getElementById("statusPieceFilter");
    let statusFilter = statusFilterEl ? statusFilterEl.value : "Todos";

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

    const rows = document.querySelectorAll(".table tbody tr");
    rows.forEach(row => {
        let show = true;
        let ds = row.dataset;

        if (f.workOrder && f.workOrder !== "Todos") {
            let dsId = String(ds.workorder).split(' ')[0].split('-')[0].trim();
            let fId = String(f.workOrder).split(' ')[0].split('-')[0].trim();
            if (dsId !== fId) show = false;
        }
        if (f.class && f.class !== "Todos" && String(ds.class).trim() !== f.class) show = false;
        if (f.operator && f.operator !== "Todos" && String(ds.operator).trim() !== f.operator) show = false;

        if (f.machine && f.machine !== "Todos") {
            let mach = f.machine.replace(" y ", "_");
            let strMach = String(ds.machine).trim();
            if (strMach !== f.machine && strMach !== mach) show = false;
        }

        if (f.process && f.process !== "Todos" && String(ds.process).trim() !== f.process) show = false;

        if (f.error && f.error !== "Todos") {
            let err = typeof ds.error === "string" && ds.error.trim() !== "" ? ds.error : "Ninguno";
            if (f.error === "Ninguno") {
                if (err.toLowerCase() !== "ninguno" && err !== "") show = false;
            } else {
                if (!err.toLowerCase().includes(f.error.toLowerCase())) show = false;
            }
        }

        if (f.n_juego && f.n_juego !== "Todos" && ds.njuego) {
            let numPiece = String(ds.njuego).replace(/[^0-9]/g, "");
            let numFilter = String(f.n_juego).replace(/[^0-9]/g, "");
            if (numPiece !== numFilter && String(ds.njuego).trim() !== f.n_juego) show = false;
        }

        if (statusFilter !== "Todos" && ds.color) {
            if (ds.color !== statusFilter.toUpperCase()) show = false;
        }

        // Filtro de fecha: se aplica sobre la fecha de maquinado (machinedDate)
        // El estado de liberación es irrelevante para este filtro.
        // Si la pieza tiene fecha de maquinado válida, se compara contra el rango.
        // Si no tiene fecha de maquinado (caso borde), se muestra igualmente.
        if (ds.date && ds.date.trim() !== "" && ds.date !== "No liberado") {
            let dsDate = ds.date.replace(/\n/g, "").trim().split(" ")[0];
            if (f.dateFrom && f.dateFrom !== "") {
                if (dsDate < f.dateFrom) show = false;
            }
            if (f.dateTo && f.dateTo !== "") {
                if (dsDate > f.dateTo) show = false;
            }
        }

        row.style.display = show ? "" : "none";
        if (show) visibleCount++;
    });

    let totalLabel = document.querySelector(".total-records-found");
    if (totalLabel) {
        totalLabel.textContent = `Registros encontrados: ${visibleCount}`;

        // Nota explicativa
        let explanation = document.querySelector(".filter-explanation");
        if (!explanation) {
            explanation = document.createElement("div");
            explanation.className = "filter-explanation";
            explanation.style.cssText = "font-size: 0.85rem; color: #555; margin-top: 5px; font-style: italic;";
            explanation.textContent = "Nota: Los filtros se aplican en tiempo real de forma acumulativa (puedes combinar múltiples criterios).";
            totalLabel.insertAdjacentElement("afterend", explanation);
        }

        // Mensaje de 'No hay datos'
        let noDataMsg = document.getElementById("no-data-alert");
        if (visibleCount === 0) {
            if (!noDataMsg) {
                noDataMsg = document.createElement("div");
                noDataMsg.id = "no-data-alert";
                noDataMsg.style.cssText = "background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-top: 20px; text-align: center; font-weight: bold; border: 1px solid #f5c6cb;";
                noDataMsg.textContent = "No hay datos para ese filtro aplicado.";
                explanation.insertAdjacentElement("afterend", noDataMsg);
            }
        } else {
            if (noDataMsg) noDataMsg.remove();
        }
    }

    if (window.updateClearButtonState) window.updateClearButtonState();
}

function sortPiezasDatabaseOrder(piezas, infoPiezas) {
    // 1. Pre-mapear todas las piezas primero para evitar miles de llamadas redundantes a orderedArray
    let mapped = piezas.map((p, i) => ({
        original: p,
        info: infoPiezas[i],
        ordered: orderedArray(p)
    }));

    const classOrder = ["Bombillo", "Molde", "Obturador", "Fondo", "Corona", "Plato", "Embudo", "Cabeza de Soplo", "Candado Obturador"];
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
        piezas: mapped.map(m => m.original),
        infoPiezas: mapped.map(m => m.info)
    };
}

if (window.pieces.length > 0) {
    let sortedData = sortPiezasDatabaseOrder(window.pieces, window.infoPieces);
    crearTabla(sortedData.piezas, sortedData.infoPiezas);
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

        if (otVal && otVal !== "Todos" && classVal && classVal !== "Todos") {
            gameSelect.disabled = false;
            // Cargar juegos
            loadAvailableGames(otVal, classVal, gameSelect);
        } else {
            gameSelect.disabled = true;
            gameSelect.value = "Todos";
            while (gameSelect.options.length > 1) {
                gameSelect.remove(1);
            }
        }
    }

    if (otSelect) otSelect.addEventListener("change", checkEnableGameFilter);
    if (classSelect) classSelect.addEventListener("change", checkEnableGameFilter);

    // Chequeo inicial
    checkEnableGameFilter();
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



