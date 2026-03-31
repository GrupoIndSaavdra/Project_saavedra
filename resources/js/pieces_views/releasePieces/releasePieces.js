var operacion = false;

function crearTabla(piezas, infoPiezas) {
    const table = document.querySelector(".table");
    const tbody = document.createElement("tbody");
    let htmlContent = "";

    // Construir tabla con String Builder (100x más rápido que createElement en loops largos)
    piezas.forEach((piezaOG, counter) => {
        let pieza = orderedArray(piezaOG);
        htmlContent += `<tr style="background-color: ${pieza.colorPiece};">`;

        Object.keys(pieza).forEach((key) => {
            if (key !== "colorPiece") {
                let cellValue = pieza[key] !== null && pieza[key] !== undefined ? pieza[key] : "";
                
                if (key === "btn_release") {
                    let btnHtml = "";
                    if (!pieza[key][1].includes("Incompleto") && pieza[key][0] != 1) {
                        let bool = (infoPiezas[counter][2] == "Ninguno" && piezas[counter][9] != 2);
                        btnHtml = `<a class="btn-liberar btn-action-liberar" style="cursor:pointer;" data-pieza='${JSON.stringify(infoPiezas[counter][0])}' data-proceso="${infoPiezas[counter][1]}" data-buena="${bool}"><img src="${window.liberar}" alt="Liberar" class="ver"></a>`;
                    }
                    htmlContent += `<td>${btnHtml}</td>`;
                } else if (key === "btn_decline") {
                    let btnHtml = "";
                    if (pieza[key] != 2) {
                        btnHtml = `<a class="btn-liberar btn-action-rechazar" style="cursor:pointer;" data-pieza='${JSON.stringify(infoPiezas[counter][0])}' data-proceso="${infoPiezas[counter][1]}"><img src="${window.rechazar}" alt="Rechazar" class="ver"></a>`;
                    }
                    htmlContent += `<td>${btnHtml}</td>`;
                } else if (key === "btn_seePiece") {
                    let nPiezas = infoPiezas[counter][0].join(",");
                    let url = `${window.baseUrl}/pieces/${nPiezas}/${infoPiezas[counter][1]}/quality`;
                    htmlContent += `<td><a class="btn-pza" href="${url}"><img src="${window.ojito}" alt="Ver" class="ver"></a></td>`;
                } else {
                    let widthAttr = (key === "operator" || key === "observations" || key === "observacion_liberacion") ? ' style="width: 600px;"' : '';
                    htmlContent += `<td${widthAttr}>${cellValue}</td>`;
                }
            }
        });
        htmlContent += `</tr>`;
    });

    tbody.innerHTML = htmlContent;
    table.appendChild(tbody);

    // Agregar listeners al terminar el ciclo (muy importante para evitar memory leaks)
    document.querySelectorAll('.btn-action-liberar').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            create_ObservationsField({
                pieza: JSON.parse(btn.getAttribute('data-pieza')),
                proceso: btn.getAttribute('data-proceso'),
                liberar: true,
                buena: btn.getAttribute('data-buena') === 'true'
            });
        });
    });

    document.querySelectorAll('.btn-action-rechazar').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            create_ObservationsField({
                pieza: JSON.parse(btn.getAttribute('data-pieza')),
                proceso: btn.getAttribute('data-proceso'),
                liberar: false,
                buena: false
            });
        });
    });
}
function asignColorTr(status, error) {
    switch (status) {
        case 1:
            return "#79BFED"; // Liberado - Azul
        case 2:
            return "#FF6B6B"; // Rechazado - Rojo
        case 3:
            return "#90EE90"; // Buena sin liberación - Verde
        case 4:
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
        colorPiece: asignColorTr(array[9], array[5] ?? ""),
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
    submit.style.backgroundColor = !keys.liberar ? "#f00000" : "#033966";

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
        let button = document.createElement("button");
        button.textContent = "Buscar";
        button.className = "btns btn-search";
        button.type = "submit";
        button.name = "action";
        button.value = "search";
        button.textContent = "Buscar";

        //Agregar div de cargando
        button.addEventListener("click", () => {
            let div_opacity = document.createElement("div");
            div_opacity.className = "div-opacity";
            document.body.appendChild(div_opacity);

            let divLoading = document.createElement("div");
            divLoading.className = "loading";
            //Insertar video de cargando
            let imgLoading = document.createElement("img");
            imgLoading.src = window.loading;
            imgLoading.alt = "Cargando...";
            imgLoading.className = "img-loading";
            divLoading.appendChild(imgLoading);
            document.body.appendChild(divLoading);
        });

        document.querySelector(".filters").appendChild(button);
    }
}
createFilters();

function sortPiezasDatabaseOrder(piezas, infoPiezas) {
    let combined = [];
    for (let i = 0; i < piezas.length; i++) {
        combined.push({
            pieza: piezas[i],
            info: infoPiezas[i]
        });
    }

    const classOrder = ["Bombillo", "Molde", "Obturador", "Fondo", "Corona", "Plato", "Embudo", "Cabeza de Soplo"];
    const processOrder = [
        "Cepillado", "Desbaste Exterior", "Revision Laterales", "Primera Operacion", 
        "Barreno Maniobra", "Segunda Operacion", "Soldadura", "Soldadura PTA", 
        "Rectificado", "Asentado", "Calificado", "Acabado Bombillo", "Acabado Molde", 
        "Barreno Profundidad", "Cavidades", "Copiado", "Off Set", "Palomas", 
        "Rebajes", "Operacion Equipo_1 operacion", "Operacion Equipo_2 operacion", 
        "Embudo CM", "Primera Operacion Cabeza Soplo", "Segunda Operacion Cabeza Soplo"
    ];

    combined.sort((a, b) => {
        let pA = orderedArray(a.pieza);
        let pB = orderedArray(b.pieza);

        // 1. Orden por OT
        let otA = parseInt(pA.workOrder.split(' ')[0]) || 0;
        let otB = parseInt(pB.workOrder.split(' ')[0]) || 0;
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
        let strA = pA.noAssembly ? String(pA.noAssembly).replace(/[^0-9]/g, "") : "0";
        let strB = pB.noAssembly ? String(pB.noAssembly).replace(/[^0-9]/g, "") : "0";
        let numA = parseInt(strA) || 0;
        let numB = parseInt(strB) || 0;
        if (numA !== numB) return numA - numB;

        return 0;
    });

    let result = { piezas: [], infoPiezas: [] };
    for (let i = 0; i < combined.length; i++) {
        result.piezas.push(combined[i].pieza);
        result.infoPiezas.push(combined[i].info);
    }
    return result;
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



