/**
 * Módulo de Tabla Interactiva de Piezas
 * Creación de tabla por proceso, inserción de cotas nominales, tolerancias, inputs por pieza y botones
 */

import { Process } from '../Process.js';
import {
    insertProductionActions,
    handleEditMetaClick,
    createHistoricalTable,
    createBtnCancelEdit,
    showInlinePasswordForm,
    removePasswordForms
} from './actionsHub.js';
import { openTechDocsModal } from './viewersManager.js';
import { createDivOpacity } from './uiUtils.js';

function createTable() {
    let body = document.querySelector("body");

    //Creacion del formulario
    let form = createForm("/processProduction/storePiece");
    insertPrincipalVariables(form); // Insertar las variables principales

    // Verificar si existen cotas nominales en el proceso
    let cNominalsBool = false;
    if (window.arrayData["process"] == "Copiado") {
        cNominalsBool = !!(
            window.arrayData?.cNominals?.Cilindrado?.[0]?.diametro1_cilindrado &&
            window.arrayData?.cNominals?.Cavidades?.[0]?.diametro1_cavidades
        );
    } else {
        cNominalsBool = !!window.arrayData?.cNominals;
    }

    //Crear el contenedor de la tabla
    let scrollableTable = document.createElement("div");
    scrollableTable.className = "scrollable-table";
    if (window.arrayData && window.arrayData["edit"] == 2) {
        scrollableTable.classList.add("is-edit-pieces-mode");
        form.classList.add("is-edit-pieces-mode");
    }
    //Insertar la tabla de las medidas si existen cotas nominales
    if (cNominalsBool) {
        let table;
        if (window.arrayData["process"] == "Copiado") {
            scrollableTable.classList.add("max-height-table");
            const subprocesses = ["Cilindrado", "Cavidades"];
            subprocesses.forEach((subProcess) => {
                // Crear tabla de cada subproceso
                // Crear etiqueta para el subproceso
                let label = document.createElement("label");
                label.className = "label-table";
                label.innerHTML = subProcess;
                scrollableTable.appendChild(label);

                // Insertar tabla en el formulario
                table = insertTableOnForm(
                    window.arrayData["cNominals"][subProcess][0],
                    window.arrayData["cNominals"][subProcess][1],
                    scrollableTable,
                    form,
                    subProcess
                );
            });
        } else {
            table = insertTableOnForm(
                window.arrayData["cNominals"][0],
                window.arrayData["cNominals"][1],
                scrollableTable,
                form
            );
        }
        //Insertar boton de ELEGIR o GUARDAR
        insertButton_saveOrChoose(form, table);
    } else {
        const processesNoCotas = ["Soldadura", "Soldadura PTA", "Asentado", "Rectificado"];
        if (processesNoCotas.includes(window.arrayData["process"])) {
            // Soldadura PTA tiene su propia tabla pre-renderizada por el servidor (rowspans)
            if (window.arrayData["process"] === "Soldadura PTA" && window.ptaTableHtml) {
                // Inyectar la tabla pre-renderizada con rowspans
                let ptaContainer = document.createElement("div");
                ptaContainer.className = "scrollable-table pta-scrollable-container";
                if (window.arrayData && window.arrayData["edit"] == 2) {
                    ptaContainer.classList.add("is-edit-pieces-mode");
                    form.classList.add("is-edit-pieces-mode");
                }
                ptaContainer.innerHTML = window.ptaTableHtml;
                form.appendChild(ptaContainer);

                // El formulario de captura de PTA ya está incluido en el partial Blade
                // Solo agregar el botón de guardar/elegir si hay una pieza activa
                insertButton_saveOrChoose(form, ptaContainer);
            } else {
                let table = insertTableOnForm(null, null, scrollableTable, form);
                //Insertar boton de ELEGIR o GUARDAR
                insertButton_saveOrChoose(form, table);
            }
        } else {
            //Mostrar DIV de alerta para Cotas No disponibles
            let div = document.createElement("div");
            div.className = "label-noCotas";
            const noCotasIconSrc = window.imgNoCotas || (window.baseUrl ? `${window.baseUrl.replace(/\/$/, '')}/images/Color_Icons/Generales/icono_sin_cotas.png` : "/images/Color_Icons/Generales/icono_sin_cotas.png");
            div.innerHTML = `
                <img src="${noCotasIconSrc}" class="no-cotas-icon" alt="Sin Cotas Nominales">
                <span>No hay Cotas Nominales disponibles. Notificar inmediatamente al área de software o calidad.</span>
            `;
            form.appendChild(div);

            const noCotasMsg = "Las Cotas Nominales y tolerancias aún no se han cargado. Por favor notifica al área de Calidad o Software.";
            setTimeout(() => {
                if (typeof window.gisAlert === "function") {
                    window.gisAlert({
                        title: "¡Sin Cotas Nominales!",
                        text: noCotasMsg,
                        icon: "warning"
                    });
                } else if (typeof window.toastpremium === "function") {
                    window.toastpremium(noCotasMsg, "warning");
                } else if (typeof showDivAlert === "function") {
                    let bodyNode = document.querySelector("body");
                    bodyNode.appendChild(showDivAlert(noCotasMsg, true));
                }
            }, 300);
        }
    }
    body.appendChild(form);

    // Interceptar el submit para evitar el límite de max_input_vars en PHP
    form.addEventListener("submit", function () {
        // Enviar al logger que se guardó una pieza
        let now = new Date();
        let timeFormatted = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');

        let operatorNameInput = document.querySelector(".operator-name input");
        let opName = operatorNameInput ? operatorNameInput.value.split(" - ").slice(1).join(" ") : "Operador";

        if (window.pieceToBeUsed && window.pieceToBeUsed.id) {
            let pieceIdentifier = window.pieceToBeUsed.n_pieza || window.pieceToBeUsed.n_juego;
            let description = "";
            let action = "Captura Medida";

            const raw = String(pieceIdentifier).toUpperCase();
            const num = raw.slice(0, -1);
            const letra = raw.slice(-1);

            if (letra === 'J') {
                description = `El operador completó el maquinado del juego ${num}`;
            } else if (letra === 'M' || letra === 'H') {
                // Buscar si la otra mitad ya existe en las piezas maquinadas de la meta actual
                const otraLetra = (letra === 'M') ? 'H' : 'M';
                const pareja = num + otraLetra;

                const piezasYaHechas = window.arrayData.machinedPiecesInMeta || [];
                const yaExistePareja = piezasYaHechas.some(p => {
                    let pId = p.piece.n_pieza || p.piece.n_juego;
                    return String(pId).toUpperCase() === pareja;
                });

                if (yaExistePareja) {
                    description = `El operador completó el maquinado del juego ${num}`;
                } else {
                    description = `El operador registró la pieza ${raw} (${letra === 'M' ? 'Macho' : 'Hembra'})`;
                }
            } else {
                description = `El operador registró la pieza ${raw}`;
            }

            // --- LÓGICA DE ALERTAS ESTÁNDAR (TODOS LOS PROCESOS QUE USAN JUEGOS H/M) ---
            if (letra === 'M' || letra === 'H') {
                const otraLetra = (letra === 'M') ? 'H' : 'M';
                const pareja = num + otraLetra;
                const piezasYaHechas = window.arrayData.machinedPiecesInMeta || [];
                const yaExistePareja = piezasYaHechas.some(p => {
                    let pId = p.piece.n_pieza || p.piece.n_juego;
                    return String(pId).toUpperCase() === pareja;
                });

                if (yaExistePareja) {
                    // Si completa el juego, mandamos dos alertas secuenciales
                    toastpremium(`El operador registró la pieza ${raw} (${letra === 'M' ? 'Macho' : 'Hembra'}) a las ${timeFormatted}`, "success");
                    setTimeout(() => {
                        toastpremium(`El operador completó el maquinado del juego ${num} a las ${timeFormatted}`, "success");
                    }, 800);
                } else {
                    // Si es solo la primera pieza del juego
                    toastpremium(description + ` a las ${timeFormatted}`, "success");
                }
            } else {
                // Registro de pieza normal (no H/M) o Reporte General
                toastpremium(description + ` a las ${timeFormatted}`, "success");
            }

            // --- LÓGICA DE LOGGING (En el servidor para PTA, en el JS para los demás) ---
            if (window.arrayData.process !== 'Soldadura PTA') {
                // Registro de captura de pieza (SÍ se mantiene)
                window.logUserAction(action, description + ` a las ${timeFormatted}`);
            }
        } else {
            // "Proceso Correcto" eliminado: sincronización de reporte general es telemetría
            toastpremium(`El operador registró el reporte general a las ${timeFormatted}`, "success");
        }

        const pieceInputs = form.querySelectorAll('input[name="piece[]"]');
        if (pieceInputs.length > 40) { // Si hay más de 40 piezas (aprox 500-600 variables), usamos JSON
            const formData = new FormData(form);
            const jsonData = {};
            const keysToGroup = [];

            for (const [key, value] of formData.entries()) {
                if (key.endsWith("[]")) {
                    const cleanKey = key.slice(0, -2);
                    if (!jsonData[cleanKey]) {
                        jsonData[cleanKey] = [];
                        keysToGroup.push(key);
                    }
                    jsonData[cleanKey].push(value);
                }
            }

            if (Object.keys(jsonData).length > 0) {
                // Agregar el JSON como un solo campo
                let inputJson = document.createElement("input");
                inputJson.type = "hidden";
                inputJson.name = "piece_data_json";
                inputJson.value = JSON.stringify(jsonData);
                form.appendChild(inputJson);

                // Remover los nombres de los inputs originales para que PHP no los procese individualmente
                keysToGroup.forEach(name => {
                    form.querySelectorAll(`[name="${name}"]`).forEach(el => el.removeAttribute("name"));
                });
            }
        }
    });

    // Insertar tabla historica si hay datos
    if (window.arrayData && window.arrayData["history"]) {
        createHistoricalTable(window.arrayData["history"]);
    }
}
function createForm(route) {
    //Creacion del formulario
    let form = document.createElement("form");
    form.className = "form-tablePieces";
    form.method = "POST";
    form.action = window.baseUrl + route;

    //Insertar CSRF
    // Obtener el token desde el <meta>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute("content");
    let inputCSRF = document.createElement("input");
    inputCSRF.type = "hidden";
    inputCSRF.name = "_token";
    inputCSRF.value = csrfToken;
    form.appendChild(inputCSRF);

    // Agregar h_inicio_solicitud para reportar el inicio real de la pieza al backend
    let inputInicio = document.createElement("input");
    inputInicio.type = "hidden";
    inputInicio.name = "h_inicio_solicitud";
    inputInicio.value = window.currentPieceStartTime || new Date().toLocaleTimeString('it-IT');
    form.appendChild(inputInicio);

    return form;
}
function insertPrincipalVariables(form) {
    // Insertar el input que contiene el id de la meta
    let inputMeta = document.createElement("input");
    inputMeta.type = "hidden";
    inputMeta.name = "meta";
    inputMeta.value = window.arrayData["meta"].id;
    form.appendChild(inputMeta);

    // Insertar el input que contiene el nombre del proceso
    let inputProcess = document.createElement("input");
    inputProcess.type = "hidden";
    inputProcess.name = "process";
    inputProcess.value = window.arrayData["subprocess"]
        ? window.arrayData["process"] + "_" + window.arrayData["subprocess"]
        : window.arrayData["process"];
    form.appendChild(inputProcess);

    // Insertar el input que contiene el id de la pieza a utilizar si existe
    if (window.pieceToBeUsed && window.pieceToBeUsed != "NoPreviousPieces") {
        let inputPiece = document.createElement("input");
        inputPiece.type = "hidden";
        inputPiece.name = "piece";
        inputPiece.value = window.pieceToBeUsed.id;
        form.appendChild(inputPiece);
    }
}
function insertTableOnForm(cNominals, tolerances, scrollableTable, form, subprocess = null) {
    let processName = window.arrayData["process"];
    if (processName.includes("Operacion Equipo")) {
        processName = "Operacion Equipo";
    }

    let process = new Process(
        processName,
        subprocess,
        cNominals,
        tolerances,
        window.arrayData["machinedPiecesInMeta"] ?? [],
        window.pieceToBeUsed ?? null,
        true,
        window.arrayData["edit"] == 2 ? true : false
    );
    //Insertar tabla
    let table = process.createProcess();
    scrollableTable.appendChild(table);
    form.appendChild(scrollableTable);

    return table;
}

function insertButton_saveOrChoose(form, table) {
    let btnsContainer = document.createElement("div");
    btnsContainer.className = "table-actions-container";

    const isEditMode = window.arrayData && window.arrayData["edit"] == 2;

    const saveIconSrc = window.imgResultado || (window.baseUrl ? `${window.baseUrl.replace(/\/$/, '')}/images/Light_Icons/Resultado-Light.png` : "/images/Light_Icons/Resultado-Light.png");

    if (window.pieceToBeUsed) {
        if (window.pieceToBeUsed != "NoPreviousPieces") {
            let btn = document.createElement("button");
            btn.type = "submit";
            btn.className = "btn-savePiece";
            btn.innerHTML = `<img src="${saveIconSrc}" class="save-piece-icon" alt="Guardar"><span>Guardar</span>`;

            if (isEditMode) {
                let cancelBtn = createBtnCancelEdit("btn-cancel-pieces");
                btnsContainer.appendChild(cancelBtn);
            }
            btnsContainer.appendChild(btn);
            form.appendChild(btnsContainer);
        } else {
            //Mostrar DIV de alerta para "No hay piezas registradas anteriormente"
            document
                .querySelector("body")
                .appendChild(
                showDivAlert(
                    "No hay piezas registradas anteriormente. Por favor notifica a un Supervisor o al área de Software.",
                    true
                )
            );
        }
    } else {
        if (window.arrayData["availableAssemblies"] && window.arrayData["availableAssemblies"].length > 0) {
            if (!isEditMode) {
                // Cambiar la ruta del formulario para seleccionar un juego y registralo
                form.action = window.baseUrl + "/processProduction/selectAssembly";

                insertAvaliablePiecesSelect(form); // Insertar select de piezas disponibles

                // Crear botón de "Elegir pieza"
                let btn = document.createElement("button");
                btn.type = "submit";
                btn.className = "btn-savePiece";
                btn.innerHTML = `<img src="${saveIconSrc}" class="save-piece-icon" alt="Elegir pieza"><span>Elegir pieza</span>`;
                btnsContainer.appendChild(btn);
                form.appendChild(btnsContainer);
            }
        } else {
            //Mostrar alerta para "No hay piezas disponibles"
            if (!isEditMode) {
                document
                    .querySelector("body")
                    .appendChild(showDivAlert("No hay piezas por registrar", true, window.imgNoPieces));
            }
        }
        if (isEditMode) {
            let btn = document.createElement("button");
            btn.type = "submit";
            btn.className = "btn-savePiece";
            btn.innerHTML = `<img src="${saveIconSrc}" class="save-piece-icon" alt="Guardar"><span>Guardar</span>`;

            let cancelBtn = createBtnCancelEdit("btn-cancel-pieces");
            btnsContainer.appendChild(cancelBtn);
            btnsContainer.appendChild(btn);
            form.appendChild(btnsContainer);
        }
    }
}
function insertAvaliablePiecesSelect(form) {
    let select = document.createElement("select");
    select.className = "select-pieces";
    select.name = "selectedAssembly";
    select.required = true;

    select.addEventListener("change", function () {
        if (this.value) {
            // Actualizar la hora de inicio al momento exacto de selección de la pieza
            if (typeof window.refreshPieceStartTime === "function") {
                window.refreshPieceStartTime();
            }
            if (typeof window.toastpremium === "function") {
                window.toastpremium(`${this.value} seleccionado correctamente`, "success");
            }
        }
    });

    //Agregar opciones al select
    window.arrayData["availableAssemblies"].forEach((assembly, index) => {
        //Crear opción vacia
        if (index === 0) {
            let option = document.createElement("option");
            option.value = "";
            option.textContent = "Seleccionar pieza";
            select.appendChild(option);
        }
        //Crear las opciones con las piezas disponibles
        let option = document.createElement("option");
        option.value = assembly;
        option.textContent = assembly;
        select.appendChild(option);
    });

    // Insertar el select directamente en el formulario (fuera de la tabla)
    form.appendChild(select);
}



function showDivAlert(text, close, img) {
    const cleanText = text ? text.replace(/<[^>]*>/g, '').trim() : '';

    // REGISTRO AUTOMÁTICO EN BITÁCORA:
    if (typeof window.logUserAction === 'function') {
        window.logUserAction("Avisos de Sistema", `Se mostró mensaje al operador: ${cleanText}`);
    }

    // Estándar SweetAlert2 GIS
    if (typeof Swal !== 'undefined') {
        let icon = 'info';
        const lower = cleanText.toLowerCase();
        if (lower.includes('error') || lower.includes('incorrecto') || lower.includes('falla') || lower.includes('rechazad')) {
            icon = 'error';
        } else if (lower.includes('advertencia') || lower.includes('aviso') || lower.includes('atención')) {
            icon = 'warning';
        } else if (lower.includes('correcto') || lower.includes('éxito') || lower.includes('completad') || lower.includes('registrad')) {
            icon = 'success';
        }

        Swal.fire({
            title: cleanText,
            icon: icon,
            confirmButtonColor: '#033966',
            confirmButtonText: 'Entendido',
            customClass: { popup: 'swal2-gis-popup' }
        });

        // Retorna nodo invisible para llamadas tipo parent.appendChild(showDivAlert(...))
        let dummy = document.createElement("div");
        dummy.style.display = "none";
        return dummy;
    }

    // Fallback nativo
    let div_padre = createDivOpacity();

    let div = document.createElement("div");
    div.className = "alert-NoCotas";

    let label = document.createElement("label");
    label.className = "label-alert";
    label.innerHTML = text;

    let image = document.createElement("img");
    image.className = "img-error";
    image.src = img || window.imgError;

    if (close) {
        let div_cerrar = document.createElement("div");
        div_cerrar.className = "div-cerrar";
        let btn_cerrar = document.createElement("button");
        btn_cerrar.className = "btn-cerrar";
        btn_cerrar.addEventListener("click", function () {
            cerrarDiv();
        });
        let imageCerrar = document.createElement("img");
        imageCerrar.className = "img-cerrar";
        imageCerrar.src = window.cerrarImgUrl;
        btn_cerrar.appendChild(imageCerrar);
        div_cerrar.appendChild(btn_cerrar);
        div.appendChild(div_cerrar);
    }
    div.appendChild(image);
    div.appendChild(label);
    div_padre.appendChild(div);
    return div_padre;
}

function cerrarDiv() {
    let div_padre = document.getElementById("div-opacity");
    if (div_padre) div_padre.remove();
}

function enableTable() {
    createTable();
    if (window.arrayData["process"] === "Copiado") {
        let arraySubprocess = [".Cilindrado", ".Cavidades"];
        arraySubprocess.forEach((subprocess) => {
            let table = document.querySelector(subprocess);
            disabledInputsTable(table);
        });
    } else {
        let selector = window.arrayData["process"] === "Soldadura PTA" ? ".pta-table" : ".table";
        let table = document.querySelector(selector);
        disabledInputsTable(table);
    }
    redirectToEndTable();
    if (typeof window.applySavedFocusState === "function") {
        window.applySavedFocusState();
    } else if (typeof window.checkTableActive === "function") {
        window.checkTableActive();
    }
}
function disabledInputsTable(table) {
    if (table) {
        let inputs = table.querySelectorAll("input:not([type='hidden']), select, textarea");
        if (inputs.length > 0) {
            inputs.forEach((input) => {
                const isFreeInput = input.classList.contains("input-pieceUsed")
                    || input.classList.contains("mat-sold-input")   // ← input de material libre
                    || input.classList.contains("mat-sold-select")   // select de material
                    || input.classList.contains("mat-sold-btn-back");
                if (!isFreeInput) {
                    if (window.arrayData["edit"] == 2) {
                        input.disabled =
                            input.parentElement.parentElement.className === "table-row-cNominals" ? true : false;
                    } else {
                        input.disabled = true;
                    }
                } else {
                    input.disabled = false;
                    if (input.tagName === "INPUT") {
                        input.classList.add("cnom-select-white");
                    }
                }
            });
        }
        // También asegurar que el botón "← volver" del widget no quede deshabilitado
        table.querySelectorAll(".mat-sold-btn-back").forEach(btn => {
            btn.disabled = false;
        });
    }
}

function redirectToEndTable() {
    const isEditPiecesMode = window.arrayData && window.arrayData["edit"] == 2;
    const scrollFn = () => {
        const scrollable = document.querySelector(".scrollable-table");
        if (scrollable) {
            if (!isEditPiecesMode) {
                const pieceUsedRow = scrollable.querySelector(".table-row-pieceUsed");
                if (pieceUsedRow) {
                    const prevPieceRow = pieceUsedRow.previousElementSibling;
                    const hasCotas = !!scrollable.querySelector(".table-row-cNominals");
                    const stickyHeadersOffset = hasCotas ? 156 : 56;

                    // Si hay pieza anterior maquinada, encuadramos desde ella para que queden visibles tanto la anterior como la activa
                    if (prevPieceRow && prevPieceRow.classList.contains("table-row-piece")) {
                        const targetScroll = Math.max(0, prevPieceRow.offsetTop - stickyHeadersOffset);
                        scrollable.scrollTop = targetScroll;
                    } else {
                        const targetScroll = Math.max(0, pieceUsedRow.offsetTop - stickyHeadersOffset);
                        scrollable.scrollTop = targetScroll;
                    }
                } else {
                    scrollable.scrollTop = scrollable.scrollHeight;
                }
            } else {
                scrollable.scrollTop = 0;
            }
        }
        if (!isEditPiecesMode) {
            window.scrollTo(0, 0);
        } else {
            const formPieces = document.querySelector(".form-tablePieces");
            if (formPieces) {
                formPieces.scrollIntoView({ behavior: "smooth", block: "start" });
            }
        }
    };
    if (document.readyState === "complete") {
        setTimeout(scrollFn, 60);
    } else {
        window.addEventListener("load", scrollFn);
    }
}
function createBtnMetaEdit() {
    let wrapper = document.createElement("div");
    wrapper.className = "edit-meta-wrapper";

    let btn_edit = document.createElement("img");
    btn_edit.className = "img-edit";
    btn_edit.src = window.edit;
    btn_edit.alt = "Editar";
    btn_edit.title = "Editar Datos Generales del Reporte";
    btn_edit.classList.remove("z-11000"); // Asegurar que inicie limpio (herede del contenedor)

    let label = document.createElement("div");
    label.className = "action-label";
    label.textContent = "Reporte";

    btn_edit.addEventListener("click", function () {
        if (btn_edit.src == window.edit) {
            // Mostrar formulario de contraseña inline localmente
            showInlinePasswordForm("EditMeta", btn_edit);
        } else {
            removePasswordForms();
        }
    });

    wrapper.appendChild(btn_edit);
    wrapper.appendChild(label);
    return wrapper;
}

function createBtnTechDocs() {
    let wrapper = document.createElement("div");
    wrapper.className = "tech-docs-wrapper";

    let btn_docs = document.createElement("img");
    btn_docs.className = "img-edit";
    btn_docs.src = window.imgTechDocs;
    btn_docs.alt = "Documentos";
    btn_docs.title = "Ver Manuales y Ayudas Visuales";

    let label = document.createElement("div");
    label.className = "action-label";
    label.textContent = "Documentos";

    wrapper.addEventListener("click", function () {
        openTechDocsModal();
    });

    wrapper.appendChild(btn_docs);
    wrapper.appendChild(label);
    return wrapper;
}




// Exponer globalmente para compatibilidad
window.createTable = createTable;
window.createForm = createForm;
window.enableTable = enableTable;
window.disabledInputsTable = disabledInputsTable;
window.redirectToEndTable = redirectToEndTable;
window.createBtnMetaEdit = createBtnMetaEdit;
window.createBtnTechDocs = createBtnTechDocs;

export {
    createTable,
    createForm,
    insertPrincipalVariables,
    insertTableOnForm,
    insertButton_saveOrChoose,
    insertAvaliablePiecesSelect,
    createDivOpacity,
    showDivAlert,
    cerrarDiv,
    enableTable,
    disabledInputsTable,
    redirectToEndTable,
    createBtnMetaEdit,
    createBtnTechDocs
};
