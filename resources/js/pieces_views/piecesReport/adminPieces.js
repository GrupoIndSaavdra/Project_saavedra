var operacion = false;

function crearTabla(piezas, infoPiezas) {
    const table = document.querySelector(".table");
    const tbody = document.createElement("tbody");
    table.appendChild(tbody);

    const CHUNK_SIZE = 500;
    let index = 0;
    const esc = (v) => v ? String(v).replace(/"/g, '&quot;').replace(/>/g, '&gt;').replace(/</g, '&lt;') : '';

    // Intentar obtener el perfil para los enlaces, fallback a 'quality' si no existe
    const profileInput = document.getElementsByName("profile")[0];
    const profileValue = profileInput ? profileInput.value : 'quality';

    function renderNextChunk() {
        let chunkHtml = "";
        const limit = Math.min(index + CHUNK_SIZE, piezas.length);

        for (; index < limit; index++) {
            let piezaOG = piezas[index];
            let infoP = infoPiezas[index];
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

                    if (key === "btn_seePiece") {
                        let nPiezas = infoP[0].join(",");
                        let url = `${window.baseUrl}/pieces/${nPiezas}/${infoP[1]}/${profileValue}`;
                        chunkHtml += `<td><a class="btn-pza" href="${url}"><img src="${window.ojito}" alt="Ver pieza" class="ver"></a></td>`;
                    } else {
                        let widthAttr = (key === "operator") ? ' style="max-width: 300px; white-space: normal;"' : 
                                        (key === "observations" || key === "observacion_liberacion") ? ' style="max-width: 350px; white-space: normal;"' : '';
                        chunkHtml += `<td${widthAttr}>${cellValue}</td>`;
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
            if (loading) loading.style.display = 'none';
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
    //
    // NOTA: Para procesos 'Operacion Equipo', en el PHP el error se guarda en [6]
    // y luego [6] se sobreescribe con la fecha. El error correcto siempre está en [5]
    // para piezas normales. El backend ya unifica esto correctamente en [5].
    return {
        class: array["className"],
        workOrder: array[0],
        noAssembly: array[1],
        operator: array[2],
        machine: array[3],
        process: array[4],
        errors: array[5],
        observations: array.observations ?? "",
        startTime: array.hora_inicio ?? "N/A",
        endTime: array.hora_termino ?? "N/A",
        totalTime: array.tiempo_total ?? "N/A",
        machinedDate: array[6],
        liberationDate: array[7],
        user_liberation: array[8],
        observacion_liberacion: array.observacion_liberacion ?? "",
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
// La función crearBotonVer ha sido absorbida por el String Builder en crearTabla para máximo rendimiento
function obtenerRequest() {
    let names = ["workOrder", "class", "operator", "machine", "process", "error", "dateFrom", "dateTo", "n_juego"];
    let request = [];
    for (let i = 0; i < names.length; i++) {
        let value = document.getElementsByName(names[i])[0].value;
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
                        if (item == "machine") {
                            optionDefault.value = window.selectedItems[item];
                            optionDefault.textContent = window.selectedItems[item].replace("_", " y ");
                        } else {
                            optionDefault.value = window.selectedItems[item];
                            optionDefault.textContent = window.selectedItems[item];
                        }
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
                            const pairedVal = pairedMachines.includes(i) ? `${i}_${i + 1}` : null;
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
                        // Ordenar operadores alfabéticamente si es el filtro de operador
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
        // Si ya hay un valor seleccionado, habilitarlo y añadir la opción
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
    });

    divStatus.appendChild(selectStatus);

    let labelStatus = document.createElement("label");
    labelStatus.textContent = "Estado: ";
    divStatus.appendChild(labelStatus);

    let filtersContainer = document.querySelector(".filters");
    if (filtersContainer) filtersContainer.appendChild(divStatus);
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
            let dsWo = String(ds.workorder).trim().toLowerCase();
            let fWo = String(f.workOrder).trim().toLowerCase();
            let dsId = dsWo.split(' ')[0].split('-')[0].trim();
            let fId = fWo.split(' ')[0].split('-')[0].trim();
            if (dsWo !== fWo && dsId !== fId) show = false;
        }
        if (f.class && f.class !== "Todos" && String(ds.class).trim() !== f.class) show = false;
        if (f.operator && f.operator !== "Todos") {
            let rowOperators = String(ds.operator).split('/').map(op => op.trim());
            if (!rowOperators.includes(f.operator)) show = false;
        }


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

        // Agregar o actualizar nota explicativa debajo del contador
        let explanation = document.querySelector(".filter-explanation");
        if (!explanation) {
            explanation = document.createElement("div");
            explanation.className = "filter-explanation";
            explanation.style.cssText = "font-size: 0.85rem; color: #555; margin-top: 5px; font-style: italic;";
            explanation.textContent = "Nota: Los filtros se aplican en tiempo real de forma acumulativa (puedes combinar múltiples criterios).";
            totalLabel.insertAdjacentElement("afterend", explanation);
        }

        // Agregar o actualizar mensaje de 'No hay datos'
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

        // 2. Orden por Clase (según el arreglo pre-definido)
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

        // 4. Orden por número de pieza/juego (Numérico)
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
    let sortedData = sortPiezasDatabaseOrder(window.pieces, window.infoPiezas);
    crearTabla(sortedData.piezas, sortedData.infoPiezas);
    applyAllFilters();
}
const pdf = document.getElementById("pdf");

// ============================================
// FUNCIONALIDAD EXTRA PARA SOLDADURA
// ============================================

let currentSelectedProcess = window.selectedItems?.process || "Todos";
let extraInfoButton = null;
let soldaduraModalOpen = false;

/**
 * Detectar cambios en el filtro de proceso
 */
function initializeSoldaduraFeature() {
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
    if (processValue === "Soldadura") {
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

    return {
        workOrder: getVal("workOrder"),
        class: getVal("class"),
        operator: getVal("operator"),
        machine: getVal("machine"),
        process: getVal("process"),
        error: getVal("error"),
        dateFrom: getVal("dateFrom"),
        dateTo: getVal("dateTo"),
        n_juego: getVal("n_juego"),
        status: statusFilterEl ? statusFilterEl.value : "Todos",
    };
}

function getSoldaduraExtraInfo() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute("content");

    // Leer los filtros activos del DOM en lugar de los selectedItems iniciales
    const liveFilters = getCurrentFiltersFromDOM();

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
            if (data.success) {
                showSoldaduraExtraInfoTable(data.pieces, liveFilters);
            } else {
                alert(data.message || "Error al obtener información");
            }
        })
        .catch((error) => {
            console.error("Error:", error);
            alert("Error al obtener información de Soldadura.");
        });
}

/**
 * Mostrar tabla con información extra de Soldadura
 */
function showSoldaduraExtraInfoTable(pieces, liveFilters) {
    soldaduraModalOpen = true;

    // Crear div de opacidad
    const divOpacity = document.createElement("div");
    divOpacity.className = "div-opacity";
    divOpacity.id = "div-opacity-soldadura-table";

    // Crear contenedor del modal
    const modalContainer = document.createElement("div");
    modalContainer.className = "soldadura-info-modal";

    // Título
    const title = document.createElement("h2");
    title.textContent = "Información Extra - Soldadura";
    title.className = "soldadura-modal-title";
    modalContainer.appendChild(title);

    // Subtítulo con total
    const subtitle = document.createElement("p");
    subtitle.textContent = `Total de piezas en Soldadura: ${pieces.length}`;
    subtitle.className = "soldadura-modal-subtitle";
    modalContainer.appendChild(subtitle);

    if (pieces.length === 0) {
        const noDataMsg = document.createElement("p");
        noDataMsg.textContent = "No hay piezas en proceso de Soldadura actualmente.";
        noDataMsg.className = "soldadura-no-data";
        modalContainer.appendChild(noDataMsg);
    } else {
        // Crear tabla
        const tableContainer = document.createElement("div");
        tableContainer.className = "soldadura-table-container";

        const table = document.createElement("table");
        table.className = "soldadura-modal-table";

        // Encabezados
        const thead = document.createElement("thead");
        const headerRow = document.createElement("tr");
        headerRow.className = "soldadura-modal-header-row";

        const headers = [
            "N° Juego",
            "Operador",
            "Clase",
            "OT",
            "Peso por Pieza",
            "Tipo Soldadura",
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

        // Cuerpo de la tabla
        const tbody = document.createElement("tbody");
        pieces.forEach((piece, index) => {
            const tr = document.createElement("tr");
            tr.className = index % 2 === 0 ? "soldadura-row-even" : "soldadura-row-odd";

            const fields = [
                piece.n_juego,
                piece.operador,
                piece.clase,
                piece.orden_trabajo,
                piece.peso_pieza,
                piece.tipo_soldadura,
                piece.lote,
                piece.fecha,
                piece.hora,
                piece.observaciones,
            ];

            fields.forEach((fieldValue) => {
                const td = document.createElement("td");
                td.textContent = fieldValue;
                td.className = "soldadura-modal-td";
                tr.appendChild(td);
            });

            tbody.appendChild(tr);
        });

        table.appendChild(tbody);
        tableContainer.appendChild(table);
        modalContainer.appendChild(tableContainer);
    }

    // Botones de acción
    const buttonsContainer = document.createElement("div");
    buttonsContainer.className = "soldadura-modal-buttons";

    // Botón descargar PDF
    const btnDownload = document.createElement("button");
    btnDownload.type = "button";
    btnDownload.textContent = "Descargar PDF";
    btnDownload.className = "soldadura-btn-download";
    btnDownload.addEventListener("click", () => {
        // Usar los filtros vivos del DOM (los mismos que se enviaron al backend)
        const filters = liveFilters ? { ...liveFilters } : getCurrentFiltersFromDOM();

        // Eliminar parámetros que no son filtros de datos
        delete filters.action;
        delete filters.status; // Se maneja por separado si el backend lo necesita

        const params = new URLSearchParams(filters).toString();
        const downloadUrl = window.baseUrl + "/pieces/downloadSoldaduraExtraInfoPDF?" + params;

        window.open(downloadUrl, '_blank');
    });

    // Botón cerrar
    const btnClose = document.createElement("button");
    btnClose.textContent = "Cerrar";
    btnClose.className = "soldadura-btn-close";
    btnClose.addEventListener("click", closeSoldaduraModal);

    buttonsContainer.appendChild(btnDownload);
    buttonsContainer.appendChild(btnClose);
    modalContainer.appendChild(buttonsContainer);

    divOpacity.appendChild(modalContainer);
    document.body.appendChild(divOpacity);
}

/**
 * Cerrar modal de Soldadura
 */
function closeSoldaduraModal() {
    const divOpacity = document.getElementById("div-opacity-soldadura-table");
    if (divOpacity) {
        divOpacity.remove();
    }
    soldaduraModalOpen = false;
}

// Inicializar la funcionalidad cuando se carga la página
initializeSoldaduraFeature();

/**
 * Lógica para el filtro de N# Pieza/Juego
 */
function setupGameFilterLogic() {
    const otSelect = document.querySelector('select[name="workOrder"]');
    const classSelect = document.querySelector('select[name="class"]');
    const gameSelect = document.getElementById("n_juego_filter");

    // Flag para evitar que múltiples cambios simultáneos lancen varias peticiones AJAX
    let gameFilterDebounceTimer = null;

    function checkEnableGameFilter() {
        if (!otSelect || !classSelect || !gameSelect) return;

        const otVal = otSelect.value;
        const classVal = classSelect.value;
        const gameContainer = gameSelect.closest('.filter');

        if (otVal && otVal !== "Todos" && classVal && classVal !== "Todos") {
            // Mostrar y habilitar el filtro
            if (gameContainer) gameContainer.style.display = "";
            gameSelect.disabled = false;
            // Debounce: esperar 150ms para evitar múltiples requests al cambiar rápido
            clearTimeout(gameFilterDebounceTimer);
            gameFilterDebounceTimer = setTimeout(() => {
                loadAvailableGames(otVal, classVal, gameSelect);
            }, 150);
        } else {
            clearTimeout(gameFilterDebounceTimer);
            // Ocultar y deshabilitar el filtro
            if (gameContainer) gameContainer.style.display = "none";
            gameSelect.disabled = true;
            gameSelect.value = "Todos";
            // Limpiar opciones extra (mantener solo "Todos")
            while (gameSelect.options.length > 1) {
                gameSelect.remove(1);
            }
            applyAllFilters();
        }
    }

    // Usar listeners con {passive: true} para que no bloqueen el submit del formulario
    if (otSelect) otSelect.addEventListener("change", checkEnableGameFilter);
    if (classSelect) classSelect.addEventListener("change", checkEnableGameFilter);

    // Ocultar por defecto al inicializar
    const gameContainer = gameSelect?.closest('.filter');
    if (gameContainer) gameContainer.style.display = "none";

    // Chequeo inicial: solo cargar juegos si OT y clase ya tienen valor seleccionado
    // (es decir, si el usuario ya había filtrado antes)
    checkEnableGameFilter();
}

/**
 * Cargar juegos disponibles desde el servidor (AJAX)
 * Siempre usa AJAX para garantizar que el dropdown muestre TODOS los juegos
 * disponibles para la OT+Clase, sin importar el juego actualmente filtrado.
 */
function loadAvailableGames(ot, clase, selectElement) {
    // Guardar el valor actualmente seleccionado antes de limpiar
    const currentSelected = window.selectedItems?.n_juego || selectElement.value || "Todos";

    // Limpiar opciones existentes (excepto "Todos")
    while (selectElement.options.length > 1) {
        selectElement.remove(1);
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");

    if (!csrfToken) {
        console.warn("[loadAvailableGames] CSRF token not found.");
        return;
    }

    const payload = { ot: ot, class: clase };
    console.log("[loadAvailableGames] Sending AJAX:", payload, "| currentSelected:", currentSelected);

    fetch(window.baseUrl + "/getGamesFromOT", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": csrfToken,
            Accept: "application/json",
        },
        body: JSON.stringify(payload),
    })
        .then((response) => {
            console.log("[loadAvailableGames] HTTP status:", response.status);
            if (response.ok) return response.json();
            throw new Error("HTTP " + response.status);
        })
        .then((data) => {
            console.log("[loadAvailableGames] Games received:", data);
            if (data && Array.isArray(data)) {
                data.forEach((game) => {
                    let opt = document.createElement("option");
                    opt.value = game;
                    opt.textContent = game;
                    // Mantener la selección actual si coincide
                    if (currentSelected && currentSelected !== "Todos" && currentSelected == game) {
                        opt.selected = true;
                    }
                    selectElement.appendChild(opt);
                });
            }
        })
        .catch((err) => {
            console.error("[loadAvailableGames] AJAX error:", err);
        });
}

