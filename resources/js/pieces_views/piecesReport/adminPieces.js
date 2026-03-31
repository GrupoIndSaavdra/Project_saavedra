var operacion = false;

function crearTabla(piezas, infoPiezas) {
    const table = document.querySelector(".table");
    const tbody = document.createElement("tbody");
    let htmlContent = "";

    // Construir tabla con String Builder (100x más rápido que createElement en loops largos)
    piezas.forEach((pieza, counter) => {
        pieza = orderedArray(pieza);
        htmlContent += `<tr style="background-color: ${pieza.colorPiece};">`;

        Object.keys(pieza).forEach((key) => {
            if (key !== "colorPiece") {
                let cellValue = pieza[key] !== null && pieza[key] !== undefined ? pieza[key] : "";
                
                if (key === "btn_seePiece") {
                    let profileValue = document.getElementsByName("profile")[0].value;
                    let nPiezas = infoPiezas[counter][0].join(",");
                    let url = `${window.baseUrl}/pieces/${nPiezas}/${infoPiezas[counter][1]}/${profileValue}`;
                    htmlContent += `<td><a class="btn-pza" href="${url}"><img src="${window.ojito}" alt="Ver pieza" class="ver"></a></td>`;
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
        machinedDate: array[6],
        liberationDate: array[7],
        user_liberation: array[8],
        observacion_liberacion: array.observacion_liberacion ?? "",
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
                        // Generar 45 máquinas como en processProduction.js
                        for (let i = 1; i <= 45; i++) {
                            if (
                                i == window.selectedItems[item] ||
                                (window.selectedItems[item].includes("_") &&
                                    window.selectedItems[item] == `${i}_${i + 1}`)
                            ) {
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
                        // Ordenar operadores alfabéticamente si es el filtro de operador
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
            imgLoading.className = "img-loading";
            imgLoading.src = window.loading;
            imgLoading.alt = "Cargando...";
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

if (pieces.length > 0) {
    let sortedData = sortPiezasDatabaseOrder(pieces, infoPiezas);
    crearTabla(sortedData.piezas, sortedData.infoPiezas);
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
    extraInfoButton.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        padding: 12px 24px;
        background-color: #033966;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 14px;
        font-weight: bold;
        box-shadow: 0 4px 6px rgba(0,0,0,0.3);
        z-index: 999;
        transition: background-color 0.3s;
    `;

    extraInfoButton.addEventListener("mouseenter", function () {
        this.style.backgroundColor = "#055a9e";
    });

    extraInfoButton.addEventListener("mouseleave", function () {
        this.style.backgroundColor = "#033966";
    });

    extraInfoButton.addEventListener("click", showAdminVerification);

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
 * Mostrar modal de verificación de administrador
 */
function showAdminVerification() {
    if (soldaduraModalOpen) return;

    soldaduraModalOpen = true;

    // Crear div de opacidad
    const divOpacity = document.createElement("div");
    divOpacity.className = "div-opacity";
    divOpacity.id = "div-opacity-soldadura";

    // Crear contenedor del modal
    const modalContainer = document.createElement("div");
    modalContainer.className = "soldadura-verification-modal";
    modalContainer.style.cssText = `
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        z-index: 1001;
        min-width: 400px;
    `;

    // Título
    const title = document.createElement("h2");
    title.textContent = "Verificación de Administrador";
    title.style.cssText = "margin: 0 0 20px 0; color: #033966; font-size: 20px;";
    modalContainer.appendChild(title);

    // Formulario
    const form = document.createElement("form");
    form.className = "form-verify-admin-soldadura";
    form.style.cssText = "display: flex; flex-direction: column; gap: 15px;";

    // Input de contraseña
    const inputGroup = document.createElement("div");
    inputGroup.style.cssText = "display: flex; flex-direction: column; gap: 8px;";

    const label = document.createElement("label");
    label.textContent = "Contraseña de administrador:";
    label.style.cssText = "font-weight: bold; color: #333;";

    const inputPassword = document.createElement("input");
    inputPassword.type = "password";
    inputPassword.name = "passwordAdmin";
    inputPassword.placeholder = "Ingrese contraseña";
    inputPassword.className = "normal-input input-password";
    inputPassword.required = true;
    inputPassword.style.cssText = "padding: 10px; border: 1px solid #ccc; border-radius: 4px;";

    inputGroup.appendChild(label);
    inputGroup.appendChild(inputPassword);
    form.appendChild(inputGroup);

    // Botones
    const buttonGroup = document.createElement("div");
    buttonGroup.style.cssText = "display: flex; gap: 10px; justify-content: flex-end; margin-top: 10px;";

    const btnCancel = document.createElement("button");
    btnCancel.type = "button";
    btnCancel.textContent = "Cancelar";
    btnCancel.className = "btn-cancel-soldadura";
    btnCancel.style.cssText =
        "padding: 10px 20px; background: #ccc; border: none; border-radius: 4px; cursor: pointer;";
    btnCancel.addEventListener("click", closeSoldaduraModal);

    const btnVerify = document.createElement("button");
    btnVerify.type = "submit";
    btnVerify.textContent = "Verificar";
    btnVerify.className = "btn-submit-password";
    btnVerify.style.cssText =
        "padding: 10px 20px; background: #033966; color: white; border: none; border-radius: 4px; cursor: pointer;";

    buttonGroup.appendChild(btnCancel);
    buttonGroup.appendChild(btnVerify);
    form.appendChild(buttonGroup);

    // Event listener del formulario
    form.addEventListener("submit", function (e) {
        e.preventDefault();
        verifyAdminPasswordAjax(inputPassword.value);
    });

    modalContainer.appendChild(form);
    divOpacity.appendChild(modalContainer);
    document.body.appendChild(divOpacity);

    // Focus en el input
    inputPassword.focus();
}

/**
 * Verificar contraseña de administrador mediante AJAX
 */
function verifyAdminPasswordAjax(password) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute("content");

    const formData = new FormData();
    formData.append("passwordAdmin", password);

    fetch(window.baseUrl + "/pieces/verifyAdminPassword", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": csrfToken,
            Accept: "application/json",
        },
        body: formData,
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                closeSoldaduraModal();
                getSoldaduraExtraInfo();
            } else {
                alert(data.message || "Contraseña incorrecta");
            }
        })
        .catch((error) => {
            console.error("Error:", error);
            alert("Error al verificar la contraseña. Intente de nuevo.");
        });
}

/**
 * Obtener información extra de Soldadura
 */
function getSoldaduraExtraInfo() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute("content");

    fetch(window.baseUrl + "/pieces/getSoldaduraExtraInfo", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": csrfToken,
            "Content-Type": "application/json",
            Accept: "application/json",
        },
        body: JSON.stringify(window.selectedItems || {})
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                showSoldaduraExtraInfoTable(data.pieces);
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
function showSoldaduraExtraInfoTable(pieces) {
    soldaduraModalOpen = true;

    // Crear div de opacidad
    const divOpacity = document.createElement("div");
    divOpacity.className = "div-opacity";
    divOpacity.id = "div-opacity-soldadura-table";

    // Crear contenedor del modal
    const modalContainer = document.createElement("div");
    modalContainer.className = "soldadura-info-modal";
    modalContainer.style.cssText = `
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        z-index: 1001;
        max-width: 90%;
        max-height: 90%;
        overflow: auto;
    `;

    // Título
    const title = document.createElement("h2");
    title.textContent = "Información Extra - Soldadura";
    title.style.cssText = "margin: 0 0 20px 0; color: #033966; font-size: 20px;";
    modalContainer.appendChild(title);

    // Subtítulo con total
    const subtitle = document.createElement("p");
    subtitle.textContent = `Total de piezas en Soldadura: ${pieces.length}`;
    subtitle.style.cssText = "margin: 0 0 15px 0; color: #666; font-size: 14px;";
    modalContainer.appendChild(subtitle);

    if (pieces.length === 0) {
        const noDataMsg = document.createElement("p");
        noDataMsg.textContent = "No hay piezas en proceso de Soldadura actualmente.";
        noDataMsg.style.cssText = "color: #999; font-style: italic;";
        modalContainer.appendChild(noDataMsg);
    } else {
        // Crear tabla
        const tableContainer = document.createElement("div");
        tableContainer.style.cssText = "overflow-x: auto; margin-bottom: 20px;";

        const table = document.createElement("table");
        table.style.cssText = `
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        `;

        // Encabezados
        const thead = document.createElement("thead");
        const headerRow = document.createElement("tr");
        headerRow.style.cssText = "background-color: #033966; color: white;";

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
            th.style.cssText = "padding: 12px 8px; text-align: left; border: 1px solid #ddd;";
            headerRow.appendChild(th);
        });

        thead.appendChild(headerRow);
        table.appendChild(thead);

        // Cuerpo de la tabla
        const tbody = document.createElement("tbody");
        pieces.forEach((piece, index) => {
            const tr = document.createElement("tr");
            tr.style.cssText = index % 2 === 0 ? "background-color: #f9f9f9;" : "background-color: white;";

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
                td.style.cssText = "padding: 10px 8px; border: 1px solid #ddd;";
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
    buttonsContainer.style.cssText = "display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;";

    // Botón descargar PDF
    const btnDownload = document.createElement("button");
    btnDownload.type = "button"; // Prevenir submit del formulario
    btnDownload.textContent = "Descargar PDF";
    btnDownload.style.cssText =
        "padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer;";
    btnDownload.addEventListener("click", () => {
        console.log("=== PDF Download Debug ===");
        console.log("window.selectedItems:", window.selectedItems);

        // Construir query string con los filtros actuales, excluyendo 'action'
        const filters = { ...(window.selectedItems || {}) };
        delete filters.action; // Eliminar el parámetro 'action' que causa conflictos de ruta

        // Si el operador es un objeto, extraer solo la matrícula
        if (filters.operator && typeof filters.operator === 'object') {
            console.log("Operator is object:", filters.operator);
            filters.operator = filters.operator.matricula;
        }

        console.log("Filters after processing:", filters);

        const params = new URLSearchParams(filters).toString();
        const downloadUrl = window.baseUrl + "/pieces/downloadSoldaduraExtraInfoPDF?" + params;

        console.log("Download URL:", downloadUrl);
        console.log("Params:", params);

        // Usar window.open en lugar de window.location.href para mejor manejo de errores
        window.open(downloadUrl, '_blank');
    });

    // Botón cerrar
    const btnClose = document.createElement("button");
    btnClose.textContent = "Cerrar";
    btnClose.style.cssText =
        "padding: 10px 20px; background: #033966; color: white; border: none; border-radius: 4px; cursor: pointer;";
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
    const divOpacity =
        document.getElementById("div-opacity-soldadura") || document.getElementById("div-opacity-soldadura-table");
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

        if (otVal && otVal !== "Todos" && classVal && classVal !== "Todos") {
            gameSelect.disabled = false;
            // Debounce: esperar 150ms para evitar múltiples requests al cambiar rápido
            clearTimeout(gameFilterDebounceTimer);
            gameFilterDebounceTimer = setTimeout(() => {
                loadAvailableGames(otVal, classVal, gameSelect);
            }, 150);
        } else {
            clearTimeout(gameFilterDebounceTimer);
            gameSelect.disabled = true;
            gameSelect.value = "Todos";
            // Limpiar opciones extra (mantener solo "Todos")
            while (gameSelect.options.length > 1) {
                gameSelect.remove(1);
            }
        }
    }

    // Usar listeners con {passive: true} para que no bloqueen el submit del formulario
    if (otSelect) otSelect.addEventListener("change", checkEnableGameFilter);
    if (classSelect) classSelect.addEventListener("change", checkEnableGameFilter);

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

