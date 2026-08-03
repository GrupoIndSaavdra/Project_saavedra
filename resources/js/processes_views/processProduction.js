import { Process } from "./Process.js";

// Variable global para rastrear el inicio real de cada pieza/juego independiente de la meta
window.currentPieceStartTime = null;

// Función global para actualizar el inicio de la pieza y los inputs del formulario
window.refreshPieceStartTime = function() {
    window.currentPieceStartTime = new Date().toLocaleTimeString('it-IT');
    // Actualizar todos los inputs h_inicio_solicitud que existan en el DOM
    document.querySelectorAll('input[name="h_inicio_solicitud"]').forEach(input => {
        input.value = window.currentPieceStartTime;
    });
};

// Función global para validaciones case-insensitive
window.eq = (a, b) => a && b && String(a).toLowerCase() === String(b).toLowerCase();

document.addEventListener("DOMContentLoaded", () => {
    // Inicializar datos si estamos en proceso
    if (window.arrayData && window.arrayData.meta) {
        if (!window.currentPieceStartTime) {
            window.refreshPieceStartTime();
        }
        // "Carga de Formulario de Producción" eliminado: es telemetría de bajo valor
    }
});


function createSelects(labelText, className) {
    let form_grid = document.querySelector(".form-grid");

    let form_group = document.createElement("div");
    form_group.className = "form-group";

    let select = document.createElement("select");
    select.className = `form-control ${className}`;
    select.name = className;
    select.id = className;
    if (className === "workOrder") {
        modifySelects(window.workOrders, select, labelText);
    } else {
        select.disabled = true;
        let optionEmpty = document.createElement("option");
        optionEmpty.value = "";
        optionEmpty.textContent = "No disponible";
        select.appendChild(optionEmpty);
    }

    if (labelText === "Subproceso" || labelText === "Orden de trabajo" || labelText === "Proceso") {
        if (labelText !== "Proceso") {
            form_group.classList.add("full-width");
        }
        form_group.id = "processGroup";
    } else if (labelText === "Subproceso") {
        let form_groupFounded = document.querySelector(".full-width");
        if (form_groupFounded) {
            form_groupFounded.classList.remove("full-width");
            form_groupFounded.classList.add("normal-width");
        }
    }
    let label = document.createElement("label");
    label.textContent = labelText;
    label.className = "form-label";
    label.setAttribute("for", className);

    select.addEventListener("change", function () {
        disabledSelects(className);
        if (select.value) {
            if (className === "subprocess") {
                let submit = document.querySelector(".btn-submit");
                submit.classList.remove("prod-submit-hidden"); submit.classList.add("prod-submit-visible");
            }
            select.classList.remove("cnom-select-blue"); select.classList.add("cnom-select-white");
        } else {
            if (className === "subprocess") {
                let submit = document.querySelector(".btn-submit");
                if (submit) {
                    submit.classList.remove("prod-submit-visible"); submit.classList.add("prod-submit-hidden");
                }
            }
            select.classList.remove("cnom-select-white"); select.classList.add("cnom-select-blue");
        }
    });

    form_group.appendChild(select);
    form_group.appendChild(label);
    if (labelText === "Subproceso") {
        let form_groupProcess = document.querySelectorAll("#processGroup");
        form_groupProcess[1].after(form_group);
    } else {
        form_grid.appendChild(form_group);
    }

    return select;
}

function validateProductionForm() {
    let submit = document.querySelector(".btn-submit");
    if (!submit) return;

    let formGrid = document.querySelector(".form-grid");
    if (!formGrid) return;

    // Obtener todos los campos requeridos (selects e inputs)
    let inputs = formGrid.querySelectorAll("input[required], select[required], .workOrder, .class, .process");
    let allFilled = true;

    inputs.forEach(input => {
        // Ignorar campos deshabilitados
        if (input.disabled) return;

        if (!input.value || input.value.trim() === "") {
            allFilled = false;
        }
    });

    // Cambiar estado del botón
    if (allFilled) {
        submit.classList.remove("prod-submit-hidden"); submit.classList.add("prod-submit-visible");
    } else {
        submit.classList.remove("prod-submit-visible"); submit.classList.add("prod-submit-hidden");
    }
}

function modifySelects(array, select, labelText) {
    select.disabled = false; // Habilitar el select si ya existe
    select.innerHTML = ""; // Limpiar las opciones existentes

    // Crear opciones para cada orden de trabajo

    let arrayElements = labelText == "Proceso" || labelText == "Subproceso" ? Object.values(array) : Object.keys(array);

    arrayElements.forEach((element, key) => {
        // Crear un elemento de opción vacio
        if (key == 0) {
            let optionEmpty = document.createElement("option");
            optionEmpty.value = "";
            optionEmpty.textContent = "Selecciona una opción";
            select.appendChild(optionEmpty);
        }
        // Evitar crear opción si element es "moldura" y labelText es "Clase"
        if (labelText === "Clase" && element === "moldura") {
            return; // o `continue;` si estás en un for tradicional
        }

        // Crear un elemento de opción con el valor de la orden de trabajo
        let option = document.createElement("option");
        option.value = element;
        option.textContent = labelText == "Orden de trabajo" ? `${element} - ${array[element]["moldura"]}` : element;
        select.appendChild(option);
    });
}

function disabledSelects(className) {
    //Deshabilitar selects
    let array = ["subprocess", "process", "class"];
    for (let i = 0; i < array.length; i++) {
        if (array[i] === className) {
            break; // No deshabilitar el select actual
        }
        let select = document.querySelector(`.${array[i]}`);
        if (select) {
            if (array[i] !== "subprocess") {
                select.classList.remove("cnom-select-white"); select.classList.add("cnom-select-blue");
                select.disabled = true;
                select.innerHTML = ""; // Limpiar las opciones existentes
                let optionEmpty = document.createElement("option");
                optionEmpty.value = "";
                optionEmpty.textContent = "No disponible";
                select.appendChild(optionEmpty);

                let submit = document.querySelector(".btn-submit");
                if (submit) {
                    submit.classList.remove("prod-submit-visible"); submit.classList.add("prod-submit-hidden");
                }
            } else {
                let parent = select.parentElement;
                if (parent) {
                    let formGroupProcess = document.querySelector("#processGroup");
                    if (formGroupProcess) {
                        formGroupProcess.classList.remove("normal-width");
                        formGroupProcess.classList.add("full-width");
                    }
                    parent.innerHTML = ""; // Limpiar el contenido del div padre
                    parent.remove(); // Eliminar el div padre
                }
            }
        }
    }
}

function createInputs() {
    let form_grid = document.querySelector(".form-grid");

    let arrayTimes = ["Hora de inicio", "Hora de termino", "Fecha", "Maquina"];
    let arrayNames = ["startTime", "endTime", "date", "machine"];
    arrayTimes.forEach((element) => {
        let form_group = document.createElement("div");
        form_group.className = "form-group";

        let input;
        if (element === "Maquina") {
            input = document.createElement("select");
            input.className = "form-control normal-input";
            input.id = arrayNames[arrayTimes.indexOf(element)];

            let optionEmpty = document.createElement("option");
            optionEmpty.value = "";
            optionEmpty.textContent = "Selecciona una opción";
            input.appendChild(optionEmpty);
            // Generar 45 máquinas
            // Máquinas agrupadas: 1_2, 5_6, 25_26, 27_28 (primero la versión agrupada, luego las individuales)
            const pairedMachines = [1, 5, 25, 27];
            for (let i = 1; i <= 45; i++) {
                let option = document.createElement("option");
                if (pairedMachines.includes(i)) {
                    // Opción agrupada (1_2, 5_6, 25_26, 27_28)
                    option.value = `${i}_${i + 1}`;
                    option.textContent = `Maquina ${i} y ${i + 1}`;
                    input.appendChild(option);
                    // Opción individual A
                    let optA = document.createElement("option");
                    optA.value = `${i}`;
                    optA.textContent = `Maquina ${i}`;
                    input.appendChild(optA);
                    // Opción individual B
                    let optB = document.createElement("option");
                    optB.value = `${i + 1}`;
                    optB.textContent = `Maquina ${i + 1}`;
                    input.appendChild(optB);
                    i++; // Saltar el siguiente ya que ya fue agregado
                } else {
                    option.value = `${i}`;
                    option.textContent = `Maquina ${i}`;
                    input.appendChild(option);
                }
            }
        } else {
            input = document.createElement("input");
            input.type = {
                "Hora de inicio": "time",
                "Hora de termino": "time",
                Fecha: "date",
            }[element];
            input.className = "form-control normal-input";
            input.id = arrayNames[arrayTimes.indexOf(element)];
        }
        input.required = true;
        input.name = arrayNames[arrayTimes.indexOf(element)];

        let label = document.createElement("label");
        label.textContent = element;
        label.className = "form-label";
        label.setAttribute("for", arrayNames[arrayTimes.indexOf(element)]);

        form_group.appendChild(input);
        form_group.appendChild(label);
        form_grid.appendChild(form_group);
    });

    let form_principal_data = document.querySelector(".form-principal-data");

    let container = document.createElement("div");
    container.className = "form-btns-container";

    let submit = document.createElement("button");
    submit.type = "submit";
    submit.className = "btn-submit";
    submit.textContent = "Registrar";

    container.appendChild(submit);
    form_principal_data.appendChild(container);
}

function createInputsWithValue(values, valuesEnabled = []) {
    let arrayKeyNames = {
        workOrder: "Orden de trabajo",
        class: "Clase",
        process: "Proceso",
        subprocess: "Subproceso",
        machine: "Máquina",
        startTime: "Hora de inicio",
        endTime: "Hora de término",
        date: "Fecha",
        consignmentPieces: "Pedido con consignación",
        remainingPieces: "Piezas restantes",
    };
    let form_grid = document.querySelector(".form-grid");
    for (const [key, value] of Object.entries(values)) {
        let form_group = document.createElement("div");
        form_group.className = key == "workOrder" || key == "subprocess" ? "form-group full-width" : "form-group";

        if (
            key != "operator" &&
            key != "meta" &&
            key != "edit" &&
            key != "cNominals" &&
            key != "machinedPiecesInMeta" &&
            key != "numberPieces" &&
            key != "availableAssemblies" &&
            key != "history" &&
            key != "ptaTableHtml" &&
            key != "tipo_soldadura" &&
            value != null &&
            value !== ""
        ) {
            let input = document.createElement("input");
            input.name = key;
            input.id = key;
            if (valuesEnabled.includes(key)) {
                input.type = {
                    startTime: "time",
                    endTime: "time",
                    date: "date",
                }[key];
                input.className = "form-control normal-input";
                input.required = true;
                input.name = key;
                input.classList.add("cnom-select-blue");
            } else {
                if (key == "machine") {
                    let input_hidden = document.createElement("input");
                    input_hidden.type = "hidden";
                    input_hidden.name = key;
                    input_hidden.value = value;
                    form_group.appendChild(input_hidden);
                    input.value = value.replace("_", " y ");
                } else {
                    input.name = key;
                    input.value = key == "remainingPieces" ? value : value;
                }
                input.type = "text";
                input.className = "form-control normal-input";
                input.readOnly = true;
            }

            let label = document.createElement("label");
            label.textContent = arrayKeyNames[key];
            label.className = "form-label";
            label.setAttribute("for", key);

            form_group.appendChild(input);
            form_group.appendChild(label);
            form_grid.appendChild(form_group);
        }
    }
    // Crear el boton de submit si hay campos habilitados
    if (valuesEnabled.length > 0) {
        let container = document.createElement("div");
        container.className = "form-btns-container";

        // Agregar submit
        container.appendChild(createBtnSubmit_editMeta());

        // Mover cancelar si ya existe (inyectado por changeFormRoute)
        let existingCancel = document.querySelector(".btn-cancel");
        if (existingCancel) {
            container.appendChild(existingCancel);
        }

        document.querySelector(".form-principal-data").appendChild(container);
    }
}
function createBtnSubmit_editMeta() {
    let submit = document.createElement("button");
    submit.type = "submit";
    submit.className = "btn-submit";
    submit.classList.remove("prod-submit-hidden"); submit.classList.add("prod-submit-visible");
    submit.textContent = "Editar";
    return submit;
}

/**
 * Muestra un cuadro informativo de tipo de soldadura en el form-grid.
 * Solo se muestra en los procesos que aplican y solo si hay arrayData.
 */
function insertWeldingTypeBox() {
    const procesosAplicables = [
        "Primera Operacion", "Segunda Operacion", "Operacion Equipo",
        "Cepillado", "Soldadura", "Soldadura PTA"
    ];

    if (!window.arrayData) return;

    const proceso = window.arrayData["process"];
    if (!procesosAplicables.includes(proceso)) return;

    const tipoSoldaduraRaw = window.arrayData["tipo_soldadura"];
    const tipoSoldaduraLabels = {
        "1": "P1 - 3",
        "2": "P2 - 2.5",
        "3": "P3 - 2",
        "4": "P4 - 1.5",
    };
    const tipoSoldadura = tipoSoldaduraRaw
        ? (tipoSoldaduraLabels[String(tipoSoldaduraRaw)] ?? ("Tipo " + tipoSoldaduraRaw))
        : null;

    const formGrid = document.querySelector(".form-grid");
    if (!formGrid) return;

    // Evitar duplicados
    if (document.getElementById("weld-type-box")) return;

    let box = document.createElement("div");
    box.id = "weld-type-box";
    box.className = "form-group";
    box.classList.add("prod-weld-box");

    let labelTitle = document.createElement("span");
    labelTitle.textContent = "tipo de soldadura";
    labelTitle.classList.add("prod-stat-title");

    let valueSpan = document.createElement("span");
    if (tipoSoldadura) {
        valueSpan.textContent = tipoSoldadura;
        valueSpan.classList.add("prod-stat-val-white");
    } else {
        valueSpan.textContent = "-";
        valueSpan.classList.add("prod-stat-val-muted");
    }

    box.appendChild(labelTitle);
    box.appendChild(valueSpan);
    formGrid.appendChild(box);
}
function insertSelects() {
    let form_grid = document.querySelector(".form-grid");
    if (window.workOrders != null) {
        //prettier-ignore
        let selectWO = createSelects("Orden de trabajo", "workOrder");
        let selectClasses = createSelects("Clase", "class");
        let selectProcesses = createSelects("Proceso", "process");

        selectWO.addEventListener("change", function () {
            let selectedValue = selectWO.value;
            if (selectedValue) {
                // "Selección de OT" eliminado: es telemetría de bajo valor
                let classes = window.workOrders[selectedValue];
                modifySelects(classes, document.querySelector(".class"), "Clase");
            }
        });
        //prettier-ignore
        selectClasses.addEventListener("change", function () {
            let selectedClass = selectClasses.value;
            if (selectedClass) {
                // "Selección de Clase" eliminado: es telemetría de bajo valor
                let processes = window.workOrders[selectWO.value][selectedClass];
                if (processes.length > 0) {
                    modifySelects(processes, document.querySelector(".process"), "Proceso");
                }
            }
        });
        //prettier-ignore
        selectProcesses.addEventListener("change", function () {
            let selectedProcess = selectProcesses.value;
            if (selectedProcess) {
                // "Selección de Proceso" eliminado: es telemetría de bajo valor
            }
            let submit = document.querySelector(".btn-submit");
            if (selectedProcess && (selectedProcess === "Operacion Equipo" || selectedProcess === "Candado Obturador")) {
                let selectSubprocesses = createSelects("Subproceso", "subprocess");
                modifySelects(["1 operacion", "2 operacion"], selectSubprocesses, "Subproceso");
            } else if (selectedProcess) {
                validateProductionForm();
            } else {
                validateProductionForm();
            }
        });
        createInputs();

        // Agregar listeners para validación en tiempo real
        let formContainer = document.querySelector(".form-principal-data");
        formContainer.addEventListener("change", validateProductionForm);
        formContainer.addEventListener("input", validateProductionForm);
    } else {
        let p = document.createElement("p");
        p.textContent = "No hay órdenes de trabajo disponibles.";
        form_grid.classList.add("form-grid-one");
        form_grid.appendChild(p);
    }
}

function createInputPassword(name = "passwordAdmin", placeholderText = "Password of the Admin") {
    // Creación de contenedor
    let form_group = document.createElement("div");
    form_group.className = "form-group-password";
    form_group.classList.add("z-11000");

    // Input de contraseña
    let inputPassword = document.createElement("input");
    inputPassword.type = "password";
    inputPassword.name = name;
    inputPassword.placeholder = placeholderText;
    inputPassword.className = "normal-input input-password";
    inputPassword.required = true;

    // Botón de submit
    let submit = document.createElement("button");
    submit.type = "submit";
    submit.className = "btn-submit-password" + (name === "passwordQuality" ? " btn-quality" : "");
    submit.textContent = "Verificar";

    // Input oculto para meta (dentro del grupo como pide el snippet)
    let inputMeta = document.createElement("input");
    inputMeta.type = "hidden";
    inputMeta.name = "meta";
    inputMeta.value = window.arrayData["meta"].id;

    // Contenedor del input para el ojo
    let inputWrapper = document.createElement("div");
    inputWrapper.className = "password-input-wrapper";
    inputWrapper.classList.add("pos-relative");
    inputWrapper.classList.remove("hidden");
    inputWrapper.classList.add("align-center");

    // Iconos SVG Premium
    const eyeOpen = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>`;
    const eyeClosed = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>`;

    // Icono del ojo
    let eyeIcon = document.createElement("span");
    eyeIcon.innerHTML = eyeOpen;
    eyeIcon.className = "eye-toggle";

    eyeIcon.onclick = () => {
        if (inputPassword.type === "password") {
            inputPassword.type = "text";
            eyeIcon.innerHTML = eyeClosed;
        } else {
            inputPassword.type = "password";
            eyeIcon.innerHTML = eyeOpen;
        }
    };

    inputWrapper.appendChild(inputPassword);
    inputWrapper.appendChild(eyeIcon);

    form_group.appendChild(inputWrapper);
    form_group.appendChild(submit);
    form_group.appendChild(inputMeta);

    return form_group;
}

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
                ptaContainer.className = "scrollable-table";
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
            div.innerHTML =
                "No hay Cotas Nominales disponibles. Notificar inmediatamente al area de software o calidad.";
            form.appendChild(div);

            let bodyNode = document.querySelector("body");
            bodyNode.appendChild(
                showDivAlert(
                    "Las Cotas Nominales y tolerancias aun no se han cargado. Por favor notifica al area de Calidad o Software",
                    true
                )
            );
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
    if (window.pieceToBeUsed) {
        if (window.pieceToBeUsed != "NoPreviousPieces") {
            let btn = document.createElement("button");
            btn.type = "submit";
            btn.className = "btn-savePiece";
            btn.textContent = "Guardar";
            form.appendChild(btn);
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
        if (window.arrayData["availableAssemblies"].length > 0) {
            if (window.arrayData["edit"] != 2) {
                // Cambiar la ruta del formulario para seleccionar un juego y registralo
                form.action = window.baseUrl + "/processProduction/selectAssembly";

                insertAvaliablePiecesSelect(form); // Insertar select de piezas disponibles

                // Crear botón de "Elegir pieza"
                let btn = document.createElement("button");
                btn.type = "submit";
                btn.className = "btn-savePiece";
                btn.textContent = "Elegir pieza";
                form.appendChild(btn);
            }
        } else {
            //Mostrar alerta para "No hay piezas disponibles"
            if (window.arrayData["edit"] != 2) {
                document
                    .querySelector("body")
                    .appendChild(showDivAlert("No hay piezas por registrar", true, window.imgNoPieces));
            }
        }
        if (window.arrayData["edit"] == 2) {
            let btn = document.createElement("button");
            btn.type = "submit";
            btn.className = "btn-savePiece";
            btn.textContent = "Guardar";
            form.appendChild(btn);
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
            window.refreshPieceStartTime();
            // "Selección de Pieza" eliminado: es telemetría de bajo valor
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

    select.addEventListener("change", function (e) {
        if (e.target.value) {
            toastpremium(`${e.target.value} seleccionado correctamente`, "success");
        }
    });

    // Insertar el select directamente en el formulario (fuera de la tabla)
    form.appendChild(select);
}

function createDivOpacity() {
    let div_padre = document.createElement("div");
    div_padre.className = "div-opacity";
    div_padre.id = "div-opacity";

    return div_padre;
}


function showDivAlert(text, close, img) {
    let div_padre = createDivOpacity();

    let div = document.createElement("div");
    div.className = "alert-NoCotas";

    let label = document.createElement("label");
    label.className = "label-alert";
    label.innerHTML = text;

    let image = document.createElement("img");
    image.className = "img-error";
    image.src = img || window.imgError;

    // REGISTRO AUTOMÁTICO EN BITÁCORA:
    if (typeof window.logUserAction === 'function') {
        window.logUserAction("Avisos de Sistema", `Se mostró mensaje al operador: ${text.replace(/<[^>]*>/g, '')}`);
    }

    if (close) {
        // Permitir cerrar la alerta
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
    const scrollFn = () => {
        const selector = window.arrayData && window.arrayData["process"] === "Soldadura PTA" ? ".pta-table tr:last-child" : ".table tr:last-child";
        const lastLine = document.querySelector(selector);
        if (lastLine) {
            lastLine.scrollIntoView({ behavior: "smooth", block: "end" });
        }
    };
    if (document.readyState === "complete") {
        scrollFn();
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

/**
 * Crea el botón de acceso rápido a Documentación (Manuales/Ayudas)
 * Espejo estetico de createBtnMetaEdit pero para el lado derecho
 */
function createBtnTechDocs() {
    let wrapper = document.createElement("div");
    wrapper.className = "tech-docs-wrapper";
    wrapper.classList.remove("hidden");
    wrapper.classList.add("prod-col-center");

    // Contenedor relativo para posicionar el pez sin afectar el centrado del logo
    let imgContainer = document.createElement("div");
    imgContainer.classList.add("pos-relative");
    imgContainer.classList.remove("hidden");
    imgContainer.classList.add("justify-center");

    let btn_docs = document.createElement("img");
    btn_docs.className = "img-edit";
    btn_docs.src = window.imgTechDocs;
    btn_docs.alt = "Documentación";
    btn_docs.title = "Ver Manuales y Ayudas Visuales";

    // El pececito adjuntado (Oculto a petición)
    let fish_gif = document.createElement("img");
    fish_gif.src = window.baseUrl + "/images/fish.gif";
    fish_gif.alt = "Pez";
    fish_gif.classList.add("prod-fish-gif");
    fish_gif.classList.add("hidden"); // Ocultado

    let label = document.createElement("div");
    label.className = "action-label";
    label.textContent = "Documentos";
    label.classList.add("prod-label-center");

    // Asignar el clic a todo el contenedor para mayor facilidad de uso
    wrapper.addEventListener("click", function () {
        openTechDocsModal();
    });

    imgContainer.appendChild(btn_docs);
    imgContainer.appendChild(fish_gif);

    wrapper.appendChild(imgContainer);
    wrapper.appendChild(label);
    return wrapper;
}


function showInlinePasswordForm(type, imgElement = null) {
    // 1. Limpiar cualquier formulario previo y ocultar botón de terminar
    removePasswordForms();
    toggleFinishReportButton(false);

    // 2. Insertar el div-opacity (fondo borroso)
    document.querySelector("body").appendChild(createDivOpacity());

    // 3. Determinar contenedor y comportamiento
    let form;
    let targetContainer = imgElement ? imgElement.parentElement : null;

    if (!targetContainer) return;

    // Crear formulario dinámico (Nuevo enfoque unificado)
    form = document.createElement("form");
    form.method = "POST";
    form.className = "form-inline-verification";
    form.action = window.baseUrl + "/processProduction/verified";

    // Agregar Token CSRF
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute("content");
    let inputCSRF = document.createElement("input");
    inputCSRF.type = "hidden";
    inputCSRF.name = "_token";
    inputCSRF.value = csrfToken;
    form.appendChild(inputCSRF);

    if (type === "EditPieces") {
        let editFlag = document.createElement("input");
        editFlag.type = "hidden";
        editFlag.name = "editPieces";
        editFlag.value = "true";
        form.appendChild(editFlag);
    }

    // REGLA DE ORO: INICIO DE RANGO (OPCIÓN 2 - ESPERA SUPERVISOR)
    const h_inicio_solicitud = new Date().toLocaleTimeString('it-IT');
    window.qualityStartTime = h_inicio_solicitud; // Guardar globalmente

    let inputInicio = document.createElement("input");
    inputInicio.type = "hidden";
    inputInicio.name = "h_inicio_solicitud";
    inputInicio.value = h_inicio_solicitud;
    form.appendChild(inputInicio);

    // 4. Crear el grupo de contraseña según el tipo
    let form_group_password;
    if (type === "Calidad") {
        form_group_password = createInputPassword("passwordQuality", "Password of the Quality");
        form.onsubmit = function (e) {
            e.preventDefault();
            window.logUserAction("Intento de Liberación", "Se abrió el formulario de liberación de calidad", { h_inicio: window.qualityStartTime });
            verifyQualityPasswordAjax(form, imgElement);
        };
    } else if (type === "EditMeta") {
        window.logUserAction("Solicitud Edición de Reporte", "Se requiere contraseña de administrador para editar metadatos");
        form_group_password = createInputPassword("passwordAdmin", "Password of the Admin");
    } else {
        window.logUserAction("Solicitud Edición de Piezas", "Se requiere contraseña de administrador para editar piezas");
        form_group_password = createInputPassword("passwordAdmin", "Password of the Admin");
    }

    // 5. Ubicar el formulario al lado del botón
    targetContainer.classList.add("is-verifying");
    form.appendChild(form_group_password);
    targetContainer.appendChild(form);

    // 6. Cambiar icono a "Back" (La capa superior la hereda del contenedor wrapper)
    if (imgElement) {
        imgElement.src = window.back;
        imgElement.classList.remove("z-11000");
    }
}

function toggleFinishReportButton(show) {
    let btn_finishReport = document.querySelector(".btn-finishReport");
    if (btn_finishReport) {
        btn_finishReport.classList.toggle("hidden", !(show));
    }
}

function addEventToFinishReport() {
    let btn_finishReport = document.querySelector(".btn-finishReport");
    let reporteTerminado = false; // control de estado

    // Mostrar botón y manejar clic
    btn_finishReport.classList.remove("opacity-0");
    btn_finishReport.addEventListener("click", function () {
        if (confirm("¿Estás seguro de que deseas terminar el reporte?")) {
            window.logUserAction("Terminar Reporte", "El usuario finalizó su turno");
            reporteTerminado = true; // ✅ permitir salir sin advertencia
            if (window.arrayData) {
                window.location.href =
                    window.baseUrl + "/processProduction/finishReport/" + window.arrayData["meta"].id;
            } else {
                window.location.href = window.baseUrl + "/processProduction/finishReport/0";
            }
        }
    });
}
function changeFormRoute(div, form, route) {
    // Cambiar la ruta del formulario a la de editar
    form.action = window.baseUrl + route;

    // Insertar el input que contiene el id de la meta
    let input_hidden = document.createElement("input");
    input_hidden.type = "hidden";
    input_hidden.name = "meta";
    input_hidden.value = window.arrayData["meta"].id;
    form.appendChild(input_hidden);

    // Crear el boton de cancelar edición solo si no es edición de piezas (el cual va abajo)
    if (window.arrayData["edit"] == 1) {
        div.appendChild(createBtnCancelEdit("btn-cancel-report"));
    }
}

function createBtnCancelEdit(customClass = "btn-cancel") {
    let btn_cancel = document.createElement("a");
    btn_cancel.href = "#";
    btn_cancel.className = customClass;
    btn_cancel.textContent = "Cancelar";
    btn_cancel.onclick = function (e) {
        e.preventDefault();
        if (confirm("¿Estás seguro de que deseas cancelar?")) {
            window.location.href =
                window.baseUrl +
                "/processProduction/format/" +
                window.arrayData["meta"].id +
                "/" +
                window.arrayData["process"] +
                "/0";
        }
    };
    return btn_cancel;
}

// --- Centralización de Controles de Producción ---

function insertProductionActions() {
    let warningLabel = document.querySelector(".warning-pieces");
    if (!warningLabel) return;

    // Crear contenedor principal
    let actionsContainer = document.createElement("div");
    actionsContainer.className = "production-actions";

    // MODO EDICIÓN DE PIEZAS (Estandarizado con la estética del reporte pero con clase propia)
    if (window.arrayData["edit"] == 2) {
        actionsContainer.appendChild(createBtnCancelEdit("btn-cancel-pieces"));
        warningLabel.insertAdjacentElement('afterend', actionsContainer);
        return;
    }

    // Detectar si hay piezas liberadas (liberacion == 1)
    let hasPieces = window.arrayData["machinedPiecesInMeta"] &&
        window.arrayData["machinedPiecesInMeta"].length > 0;

    let hasReleasedPieces = window.arrayData["machinedPiecesInMeta"] &&
        window.arrayData["machinedPiecesInMeta"].some(p => p.piece.liberacion === 1);

    // 1. Botón de Dibujos (Siempre habilitado)
    let btnDrawings = createActionButton(
        window.imgDraws,
        "Dibujos",
        "Ver Dibujos/Planos",
        false,
        () => {
            const activeOT = window.arrayData && window.arrayData.workOrder ? window.arrayData.workOrder : 'Sin OT';
            const activeClase = window.arrayData ? (window.arrayData.class || 'Sin Clase') : 'Sin Clase';
            window.logUserAction("Consulta Dibujos Técnicos", `El operador revisó los dibujos técnicos de ${activeOT} - ${activeClase}`);
            window.openDibujosViewer();
        }
    );
    btnDrawings.classList.add("btn-drawings");
    actionsContainer.appendChild(btnDrawings);


    // 2. Botón de Calidad (Bloqueado si no hay piezas registradas)
    let qualityDisabled = !hasPieces;
    actionsContainer.appendChild(createActionButton(
        window.imgQualityCheck,
        "Calidad",
        "Liberación de Calidad",
        qualityDisabled,
        (e) => handleQualityClick(e, actionsContainer)
    ));

    // 3. Botón de Editar Piezas (Bloqueado similar a calidad)
    let editDisabled = !hasPieces;
    actionsContainer.appendChild(createActionButton(
        window.imgEditPieces,
        "Editar",
        "Editar piezas registradas",
        editDisabled,
        (e) => handleEditPiecesClick(e, actionsContainer)
    ));

    // Insertar después de la advertencia
    warningLabel.insertAdjacentElement('afterend', actionsContainer);
}

function createActionButton(src, label, title, disabled, callback) {
    let wrapper = document.createElement("div");
    wrapper.className = "action-btn-wrapper";

    let img = document.createElement("img");
    img.src = src;
    img.className = "img-action-btn" + (disabled ? " btn-disabled" : "");
    img.title = title;
    img.alt = label;

    if (!disabled) {
        img.onclick = (e) => callback(e);
    }

    let lbl = document.createElement("div");
    lbl.className = "action-label";
    lbl.textContent = label;

    wrapper.appendChild(img);
    wrapper.appendChild(lbl);
    return wrapper;
}

function handleQualityClick(event) {
    let img = event.currentTarget;
    if (!img || img.classList.contains("btn-disabled")) return;

    if (img.src.includes(window.imgQualityCheck.split('/').pop())) {
        showInlinePasswordForm("Calidad", img);
    } else {
        window.logUserAction("Abandono de Liberación", "El usuario canceló la autenticación de calidad.");
        removePasswordForms();
        img.src = window.imgQualityCheck;
    }
}

function handleEditPiecesClick(event) {
    let img = event.currentTarget;
    if (!img || img.classList.contains("btn-disabled")) return;

    if (img.src.includes(window.imgEditPieces.split('/').pop())) {
        showInlinePasswordForm("EditPieces", img);
    } else {
        removePasswordForms();
        img.src = window.imgEditPieces;
    }
}

function removePasswordForms() {
    // 1. Eliminar el div de opacidad
    let div_opacity = document.getElementById("div-opacity");
    if (div_opacity) div_opacity.remove();

    // 2. Eliminar el grupo de contraseña si existe (global o local)
    let form_group_password = document.querySelector(".form-group-password");
    if (form_group_password) form_group_password.remove();

    // 3. Restaurar botón de terminar reporte
    toggleFinishReportButton(true);

    // Eliminar formularios en línea inyectados
    let form_inline = document.querySelector(".form-inline-verification");
    if (form_inline) form_inline.remove();

    // 3. Restaurar action y onsubmit del formulario principal
    let form = document.querySelector(".form-verified-password");
    if (form) {
        form.action = window.baseUrl + "/processProduction/verified";
        form.onsubmit = null;
        let editFlag = form.querySelector('input[name="editPieces"]');
        if (editFlag) editFlag.remove();
    }

    // 4. Restaurar contenedores y estados
    document.querySelectorAll(".is-verifying").forEach(el => el.classList.remove("is-verifying"));

    // 5. Restaurar iconos de acciones de producción e iconos de edición
    let imgQuality = document.querySelector('img[alt="Calidad"]');
    let imgEditPieces = document.querySelector('img[alt="Editar"]');
    let imgEditMeta = document.querySelector('.img-edit');

    if (imgQuality) {
        imgQuality.src = window.imgQualityCheck;
        imgQuality.classList.remove("z-11000");
    }
    if (imgEditPieces) {
        imgEditPieces.src = window.imgEditPieces;
        imgEditPieces.classList.remove("z-11000");
    }
    if (imgEditMeta) {
        imgEditMeta.src = window.edit;
        imgEditMeta.classList.remove("z-11000");
    }
}


// Verificar contraseña de calidad mediante AJAX
function verifyQualityPasswordAjax(form, imgElement) {
    const formData = new FormData(form);
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute("content");

    fetch(window.baseUrl + "/processProduction/verifyQualityPassword", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": csrfToken,
            "Accept": "application/json"
        },
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.logUserAction("Login Inspector Calidad", "Autenticación correcta: " + data.qualityUser, { h_inicio: window.qualityStartTime });
                // Limpiar UI
                removePasswordForms();
                if (imgElement) imgElement.src = window.imgQualityCheck;

                // Mostrar el modal de liberación de piezas
                showQualityReleaseModal(data.pieces, data.qualityUser);
            } else {
                toastpremium("Contraseña incorrecta", "error");
                window.logUserAction("Error Inspector Calidad", "Autenticación fallida");
            }
        })
        .catch(error => {
            console.error("Error:", error);
            alert("Error al verificar la contraseña. Intente de nuevo.");
        });
}


// Función para obtener el color de un juego completo
function getColorForSet(pieces) {
    // Si todas las piezas están liberadas
    if (pieces.every(p => p.liberacion === 1)) return "#79BFED"; // Azul

    // Si alguna está rechazada
    if (pieces.some(p => p.liberacion === 2)) return "#FF6B6B"; // Rojo

    // Si todas están sin error y sin liberar
    if (pieces.every(p => (p.error === "Ninguno" || !p.error) && p.liberacion === 0)) return "#90EE90"; // Verde

    // Si alguna tiene error y no está liberada
    if (pieces.some(p => p.error !== "Ninguno" && p.error && p.liberacion === 0)) return "#DDA0DD"; // Morado

    return "#FFD700"; // Amarillo (incompleto)
}

// Función para obtener el estado más restrictivo de un juego
function getMostRestrictiveStatus(pieces) {
    // Si alguna está rechazada, el juego está rechazado
    if (pieces.some(p => p.liberacion === 2)) return "Rechazado";

    // Si todas están liberadas, el juego está liberado
    if (pieces.every(p => p.liberacion === 1)) return "Liberado";

    // Si hay una mezcla, está sin liberar
    return "Sin liberar";
}

// Función para mostrar el modal de liberación de piezas
function showQualityReleaseModal(piecesData, qualityUserName = "") {
    let body = document.querySelector("body");
    body.appendChild(createDivOpacity());

    let modalContainer = document.createElement("div");
    modalContainer.className = "quality-release-modal";

    // Botón de cerrar (X) arriba a la derecha
    let divCerrar = document.createElement("div");
    divCerrar.className = "div-cerrar";
    let btnCerrar = document.createElement("button");
    btnCerrar.className = "btn-cerrar btn-cancel-release";
    btnCerrar.onclick = function () {
        closeQualityModal(true);
    };
    let imgCerrar = document.createElement("img");
    imgCerrar.className = "img-cerrar";
    imgCerrar.src = window.cerrarImgUrl;
    btnCerrar.appendChild(imgCerrar);
    divCerrar.appendChild(btnCerrar);
    modalContainer.appendChild(divCerrar);

    // Título del modal
    let title = document.createElement("h2");
    title.className = "modal-title";
    title.textContent = "Liberación de piezas";
    modalContainer.appendChild(title);

    // Mostrar nombre del usuario de calidad
    if (qualityUserName) {
        let qualityInfo = document.createElement("h2");
        qualityInfo.className = "quality-user-info";
        qualityInfo.innerHTML = `Inspector de Calidad: ${qualityUserName}`;
        modalContainer.appendChild(qualityInfo);
    }

    // Leyenda de colores
    let legendContainer = document.createElement("div");
    legendContainer.className = "color-legend";
    let colorsArray = {
        "#79BFED": "Liberado",
        "#FF6B6B": "Rechazado",
        "#FFD700": "Incompleto",
        "#DDA0DD": "Liberación pendiente",


    };
    for (let color in colorsArray) {
        let legendItem = document.createElement("div");
        legendItem.className = "legend-item";
        let colorBox = document.createElement("span");
        colorBox.className = "color-box";
        colorBox.style.backgroundColor = color;
        let colorLabel = document.createElement("span");
        colorLabel.textContent = colorsArray[color];
        legendItem.appendChild(colorBox);
        legendItem.appendChild(colorLabel);
        legendContainer.appendChild(legendItem);
    }
    modalContainer.appendChild(legendContainer);

    // Crear formulario
    let form = createForm("/processProduction/releasePieces");
    form.className = "form-release-pieces";

    // Input oculto para meta
    let inputMeta = document.createElement("input");
    inputMeta.type = "hidden";
    inputMeta.name = "meta";
    inputMeta.value = window.arrayData["meta"].id;
    form.appendChild(inputMeta);

    // Input oculto para h_inicio_solicitud (Persistencia de tiempo)
    if (window.qualityStartTime) {
        let inputInicio = document.createElement("input");
        inputInicio.type = "hidden";
        inputInicio.name = "h_inicio_solicitud";
        inputInicio.value = window.qualityStartTime;
        form.appendChild(inputInicio);
    }

    // Contenedor de tabla scrolleable
    let tableContainer = document.createElement("div");
    tableContainer.className = "release-table-container";

    // Crear tabla
    let table = document.createElement("table");
    table.className = "release-table";

    // Encabezado de tabla
    let thead = document.createElement("thead");
    let headerRow = document.createElement("tr");
    let headers = ["No. Pieza/Juego", "Proceso", "Error", "Estado Actual", "Acción", "Comentarios"];
    headers.forEach(headerText => {
        let th = document.createElement("th");
        th.textContent = headerText;
        headerRow.appendChild(th);
    });
    thead.appendChild(headerRow);
    table.appendChild(thead);

    // Cuerpo de tabla
    let tbody = document.createElement("tbody");

    // El backend ya filtra por id_meta y agrupa en juegos completos
    // piecesData ya viene filtrado y agrupado desde el backend
    if (piecesData && piecesData.length > 0) {
        piecesData.forEach((pieceGroup, index) => {
            let row = document.createElement("tr");
            row.className = "piece-row";

            // Determinar el color basado en el estado de las piezas del grupo
            let groupColor = pieceGroup.isSet
                ? getColorForSet(pieceGroup.pieces)
                : getColorByStatus(pieceGroup.pieces[0].liberacion, pieceGroup.pieces[0].error);

            row.style.backgroundColor = groupColor;

            // No. Pieza (mostrar "J" si es un juego completo)
            let tdPiece = document.createElement("td");
            tdPiece.textContent = pieceGroup.displayName;
            row.appendChild(tdPiece);

            // Inputs ocultos para id(s) de pieza(s)
            pieceGroup.pieces.forEach((piece, pieceIdx) => {
                let inputPieceId = document.createElement("input");
                inputPieceId.type = "hidden";
                inputPieceId.name = `pieces[${index}][ids][${pieceIdx}]`;
                inputPieceId.value = piece.id;
                row.appendChild(inputPieceId);
            });

            // Input para indicar si es un juego completo
            let inputIsSet = document.createElement("input");
            inputIsSet.type = "hidden";
            inputIsSet.name = `pieces[${index}][isSet]`;
            inputIsSet.value = pieceGroup.isSet ? "1" : "0";
            row.appendChild(inputIsSet);

            // Proceso
            let tdProcess = document.createElement("td");
            tdProcess.textContent = pieceGroup.pieces[0].proceso;
            row.appendChild(tdProcess);

            // Error (mostrar errores de todas las piezas del grupo)
            let tdError = document.createElement("td");
            let errors = pieceGroup.pieces.map(p => p.error || "Ninguno").filter((v, i, a) => a.indexOf(v) === i);
            tdError.textContent = errors.join(", ");
            row.appendChild(tdError);

            // Estado actual (mostrar el estado más restrictivo)
            let tdStatus = document.createElement("td");
            let statusText = pieceGroup.isSet
                ? getMostRestrictiveStatus(pieceGroup.pieces)
                : getStatusText(pieceGroup.pieces[0].liberacion);
            tdStatus.textContent = statusText;
            row.appendChild(tdStatus);

            // Acción (select)
            let tdAction = document.createElement("td");
            let selectAction = document.createElement("select");
            selectAction.name = `pieces[${index}][action]`;
            selectAction.className = "action-select";

            // Opciones de acción con sus valores y colores correspondientes
            let options = [
                { value: "", text: "Selecciona una accion", color: "" },
                { value: "1", text: "Liberar", color: "#79BFED" },
                { value: "2", text: "Rechazar", color: "#FF6B6B" },
                { value: "5", text: "Incompleto", color: "#FFD700" }
            ];

            options.forEach(opt => {
                let option = document.createElement("option");
                option.value = opt.value;
                option.textContent = opt.text;
                option.setAttribute("data-color", opt.color);
                selectAction.appendChild(option);
            });

            // Evento para cambiar el color del select según la opción seleccionada
            selectAction.addEventListener("change", function () {
                let selectedOption = this.options[this.selectedIndex];
                let color = selectedOption.getAttribute("data-color");
                if (color) {
                    this.classList.add("row-hovered"); this.style.backgroundColor = color;
                } else {
                    this.classList.remove("row-hovered"); this.style.backgroundColor = "";
                }
            });

            tdAction.appendChild(selectAction);
            row.appendChild(tdAction);

            // Comentarios
            let tdComments = document.createElement("td");
            let inputComments = document.createElement("textarea");
            inputComments.name = `pieces[${index}][comments]`;
            inputComments.className = "comments-input";
            inputComments.placeholder = "Observaciones...";
            inputComments.rows = 2;
            tdComments.appendChild(inputComments);
            row.appendChild(tdComments);

            tbody.appendChild(row);
        });
    } else {
        let row = document.createElement("tr");
        let td = document.createElement("td");
        td.colSpan = 6;
        td.textContent = "No hay juegos completos disponibles para liberar en este reporte.";
        td.className = "prod-empty-td";
        row.appendChild(td);
        tbody.appendChild(row);
    }
    table.appendChild(tbody);
    tableContainer.appendChild(table);
    form.appendChild(tableContainer);

    // Botones de acción
    let buttonContainer = document.createElement("div");
    buttonContainer.className = "button-container";

    let btnAccept = document.createElement("button");
    btnAccept.type = "submit";
    btnAccept.className = "btn-accept-release";
    btnAccept.textContent = "Guardar Liberaciones";
    buttonContainer.appendChild(btnAccept);

    form.appendChild(buttonContainer);
    modalContainer.appendChild(form);

    let divOpacity = document.getElementById("div-opacity");
    divOpacity.appendChild(modalContainer);
}

function getColorByStatus(liberacion, error) {
    if (liberacion === 1) return "#79BFED"; // Liberado - Azul
    if (liberacion === 2) return "#FF6B6B"; // Rechazado - Rojo
    if (error === "Ninguno" && liberacion === 0) return "#90EE90"; // Buena sin liberación - Verde
    if (error !== "Ninguno" && liberacion === 0) return "#DDA0DD"; // Mala sin liberación - Morado
    return "#FFD700"; // Incompleto - Amarillo
}

function getStatusText(liberacion) {
    switch (liberacion) {
        case 1:
            return "Liberado";
        case 2:
            return "Rechazado";
        default:
            return "Sin liberar";
    }
}

function closeQualityModal(isManual = false) {
    if (isManual) {
        window.logUserAction("Abandono de Liberación", "El usuario canceló y cerró la interfaz de liberación de piezas.", { h_inicio: window.qualityStartTime });
    }

    let divOpacity = document.getElementById("div-opacity");
    if (divOpacity) {
        divOpacity.remove();
    }
    // Restaurar el botón de calidad en el nuevo contenedor
    let imgQuality = document.querySelector('img[alt="Calidad"]');
    if (imgQuality) {
        imgQuality.src = window.imgQualityCheck;
    }
}

//Ejecucion del script
//Evitar doble click en el submit
document.addEventListener("submit", (e) => {
    const btn = e.target.querySelector("button[type='submit']");
    if (btn) {
        if (btn.textContent.trim() === "Registrar") {
            window.logUserAction("Inicio de Reporte", "El operador inició un nuevo reporte de producción");
        }
        btn.disabled = true;
    }
});

if (window.arrayData) {
    if (window.arrayData["edit"]) {
        toggleFinishReportButton(false); // Ocultar siempre en modo edición

        if (window.arrayData["edit"] == 1) {
            //Cambiar la ruta del formulario a la de editar y crear el botón de cancelar edición
            changeFormRoute(
                document.querySelector(".div-table-meta"),
                document.querySelector(".form-principal-data"),
                "/processProduction/editMeta"
            );

            if (window.arrayData["numberPieces"] > 0) {
                // Si ya se han registrado piezas, solo habilitar los inputs de tiempo y fecha
                createInputsWithValue(window.arrayData, ["startTime", "endTime", "date"]);
            } else {
                // Si no se han registrado piezas, habilitar todos los inputs
                insertSelects();
            }
        } else {
            createInputsWithValue(window.arrayData); // Crear inputs con los valores de la meta
            insertWeldingTypeBox(); // Cuadro informativo de tipo de soldadura
            enableTable(); // Habilitar la tabla de piezas
            // Cambiar la ruta del formulario a la de editar piezas
            changeFormRoute(
                document.querySelector(".container-meta"),
                document.querySelector(".form-tablePieces"),
                "/processProduction/editPieces"
            );

            // Insertar controles abajo (que detectará el modo edición para el botón gigante)
            insertProductionActions();
        }
    } else {
        createInputsWithValue(window.arrayData); // Crear inputs con los valores de la meta
        insertWeldingTypeBox(); // Cuadro informativo de tipo de soldadura
        document.querySelector(".div-table-meta").prepend(createBtnMetaEdit()); // Insertar botón de editar meta arriba

        let containerCode = document.querySelector(".div-table-code");
        if (containerCode) containerCode.prepend(createBtnTechDocs()); // Insertar botón de documentos arriba a la derecha

        addEventToFinishReport(); // Agregar evento al botón de terminar reporte
        enableTable(); // Habilitar la tabla de piezas
        insertProductionActions(); // Insertar controles unificados de producción
    }
} else {
    if (window.workOrders != null) {
        insertSelects();
        addEventToFinishReport();
    } else {
        //Si aun no existen Ordenes de Trabajo disponibles mostrar Div de alerta
        let body = document.querySelector("body");
        body.appendChild(showDivAlert("Aun no hay ordenes de trabajo disponibles", false));
    }
}

function createHistoricalTable(history) {
    // Evitar duplicados si ya existe
    let existingIndicator = document.querySelector(".historical-indicator-container");
    if (existingIndicator) {
        existingIndicator.remove();
    }

    if (!history || Object.keys(history).length === 0) return;

    let container = document.createElement("div");
    container.className = "historical-indicator-container";

    let title = document.createElement("div");
    title.className = "historical-title";
    title.innerText = "Progreso de Piezas";
    container.appendChild(title);

    let consignmentPieces = window.arrayData.consignmentPieces || 0;

    // Determine current process name to filter history
    let currentProcessName = window.arrayData.process;
    if (window.arrayData.subprocess) {
        currentProcessName += "_" + window.arrayData.subprocess;
    }

    // Normalize process name to match history keys (e.g., Soldadura y Soldadura PTA)
    if (currentProcessName === "Soldadura" || currentProcessName === "Soldadura PTA") {
        currentProcessName = "Soldadura y Soldadura PTA";
    }

    let lookupName = currentProcessName;
    let processData = history[lookupName];

    // Obtener el número de proceso buscando en las llaves del historial
    let processIndex = Object.keys(history).indexOf(lookupName) + 1;
    let processNumber = processIndex > 0 ? String(processIndex).padStart(2, '0') : '--';

    // ── Construir la card con el diseño estándar del Dashboard ────────────────
    let processSection = document.createElement("div");
    processSection.className = "process-section";
    processSection.style.position = "relative";
    processSection.style.cursor = "default";

    // Click para Soldadura PTA (manteniendo la lógica de acceso original)
    if (currentProcessName === "Soldadura y Soldadura PTA" && window.arrayData.process === "Soldadura PTA") {
        processSection.style.cursor = "pointer";
        processSection.addEventListener("click", function () {
            let otId = window.arrayData.meta ? window.arrayData.meta.id_ot : null;
            if (otId) {
                createPtaPasswordModal(otId);
            } else {
                console.error("No se pudo obtener el OT ID de window.arrayData.meta");
            }
        });
    }

    // 1. Badge numérico del proceso
    let numberBadge = document.createElement("span");
    numberBadge.className = "process-number-badge";
    numberBadge.textContent = processNumber;
    processSection.appendChild(numberBadge);

    // 2. Título del proceso
    let processTitle = document.createElement("h3");
    processTitle.className = "process-title";
    processTitle.innerHTML = currentProcessName;
    processSection.appendChild(processTitle);

    // 3. Label de límite (Total disponible)
    let limitLabel = document.createElement("label");
    limitLabel.className = "limit-label";
    limitLabel.style.fontSize = "12px";
    limitLabel.style.color = "#fff";
    limitLabel.innerHTML = `Total de OT: ${consignmentPieces}`;
    processSection.appendChild(limitLabel);

    const goodCount = processData ? (processData.pieces.good || 0) : 0;
    const badCount  = processData ? (processData.pieces.bad  || 0) : 0;

    // Si no hay piezas procesadas, oscurecer
    if (goodCount === 0 && badCount === 0) {
        processSection.classList.add("inactive-process");
    }

    // Calcular porcentaje de piezas buenas
    let goodPercentage = consignmentPieces == 0 ? (goodCount > 0 ? 100 : 0) : (goodCount * 100) / consignmentPieces;
    goodPercentage = Math.min(100, goodPercentage);

    // Aplicar Glow Hue dinámico
    let hue = 30 + (goodPercentage * 0.9);
    processSection.style.setProperty('--glow-hue', hue);

    // Tooltip dinámico
    if (goodPercentage === 0) {
        processSection.title = "Proceso aún no iniciado. Esperando primeras piezas.";
    } else if (goodPercentage < 50) {
        processSection.title = `Progreso bajo (${goodPercentage.toFixed(1)}%). Se requiere atención.`;
    } else if (goodPercentage < 100) {
        processSection.title = `Progreso estable (${goodPercentage.toFixed(1)}%). Buen ritmo de trabajo.`;
    } else {
        processSection.title = "¡Proceso completado exitosamente al 100%!";
    }

    // Estilos dinámicos para el contorno de la sección y el badge numérico
    if (goodPercentage >= 100) {
        processSection.style.borderColor = '#4ade80';
        processSection.style.boxShadow = 'none';
        numberBadge.style.color = '#4ade80';
        numberBadge.style.borderColor = 'rgba(74, 222, 128, 0.3)';
        numberBadge.style.background = 'rgba(74, 222, 128, 0.1)';
    } else if (goodPercentage > 0) {
        processSection.style.borderColor = `hsl(${hue}, 100%, 50%)`;
        processSection.style.boxShadow = 'none';
        numberBadge.style.color = `hsl(${hue}, 100%, 50%)`;
        numberBadge.style.borderColor = `hsla(${hue}, 100%, 50%, 0.3)`;
        numberBadge.style.background = `hsla(${hue}, 100%, 50%, 0.1)`;
    } else {
        processSection.style.borderColor = '';
        processSection.style.boxShadow = '';
        numberBadge.style.color = '';
        numberBadge.style.borderColor = '';
        numberBadge.style.background = '';
    }

    // 4. Barras de progreso
    let pieces = [goodCount, badCount];
    for (let i = 0; i < pieces.length; i++) {
        let progressBar = document.createElement("div");
        progressBar.className = "progress-bar";
        // Colores base estilo dashboard: Verde translúcido para buenas, Rojo translúcido para malas
        progressBar.style.backgroundColor = i == 0 ? "rgba(12, 130, 1, 0.15)" : "rgba(157, 4, 2, 0.15)";

        let progress = document.createElement("div");
        progress.className = i == 0 ? "good-progress progress" : "bad-progress progress";
        
        let percentage = consignmentPieces > 0 ? (pieces[i] * 100) / consignmentPieces : 0;
        progress.style.width = `${Math.min(percentage, 100)}%`;
        
        // Color de fill específico pedido por el usuario (solo para buenas, malas usa CSS global)
        if (i === 0) {
            progress.style.backgroundColor = "rgb(52, 163, 0)";
        }

        let formattedPercentage = percentage != 0 ? percentage.toFixed(1) : 0;
        let percentageLabel = document.createElement("div");
        percentageLabel.className = "progress-percentage";
        percentageLabel.innerText = pieces[i] == 1 ? `${formattedPercentage}% ${pieces[i]} pieza` : `${formattedPercentage}% ${pieces[i]} piezas`;

        progressBar.appendChild(progress);
        progressBar.appendChild(percentageLabel);
        processSection.appendChild(progressBar);
    }

    container.appendChild(processSection);

    // Insertar después de la tabla de metadatos
    let targetContainer = document.querySelector(".div-table-code");
    if (targetContainer) {
        targetContainer.appendChild(container);
    }
}


//--------------------------------------------------------------------------------
// LÓGICA TARJETA SOLDADURA PTA EN REPORTE DE PRODUCCIÓN
//--------------------------------------------------------------------------------

document.addEventListener("DOMContentLoaded", () => {
    // Listener is injected dynamically in createHistoricalTable for "Soldadura PTA" Process
});

function createPtaPasswordModal(otId) {
    // 1. Crear el div de opacidad (fondo oscuro)
    let divOpacity = document.createElement("div");
    divOpacity.className = "div-opacity";
    divOpacity.id = "pta-modal-opacity";

    // 2. Crear contenedor principal del modal
    let modalContainer = document.createElement("div");
    modalContainer.className = "pta-modal-container";

    // Evitar que al hacer clic en el modal se cierre
    modalContainer.addEventListener("click", function (e) {
        e.stopPropagation();
    });

    // Icono (opcional)
    let icon = document.createElement("div");
    icon.className = "pta-modal-icon";
    // Icono removido por petición del usuario
    modalContainer.appendChild(icon);

    // 3. Crear título
    let title = document.createElement("h3");
    title.textContent = "Acceso Restringido";
    title.className = "pta-modal-title";
    modalContainer.appendChild(title);

    // 4. Crear texto descriptivo
    let desc = document.createElement("p");
    desc.textContent = "Ingrese la contraseña de supervisor para registrar o visualizar los resultados de Soldadura PTA.";
    desc.className = "pta-modal-desc";
    modalContainer.appendChild(desc);

    // 5. Crear el formulario
    let form = document.createElement("form");
    form.id = "pta-password-form";

    // 5.1 Input Token CSRF
    let tokenMeta = document.querySelector('meta[name="csrf-token"]');
    let token = tokenMeta ? tokenMeta.getAttribute("content") : "";
    let inputToken = document.createElement("input");
    inputToken.type = "hidden";
    inputToken.name = "_token";
    inputToken.value = token;
    form.appendChild(inputToken);

    // 5.2 Input Password
    let inputPassword = document.createElement("input");
    inputPassword.type = "password";
    inputPassword.name = "password";
    inputPassword.placeholder = "Ingresa la contraseña";
    inputPassword.className = "pta-modal-input";
    inputPassword.required = true;

    form.appendChild(inputPassword);

    // 5.3 Contenedor de Error
    let errorMsg = document.createElement("div");
    errorMsg.className = "pta-modal-error";
    form.appendChild(errorMsg);

    // 5.4 Contenedor de Botones
    let btnContainer = document.createElement("div");
    btnContainer.className = "pta-modal-btn-container";

    // Botón Cancelar
    let btnCancel = document.createElement("button");
    btnCancel.type = "button";
    btnCancel.textContent = "Cancelar";
    btnCancel.className = "pta-modal-btn pta-modal-btn-cancel";
    btnCancel.addEventListener("click", () => {
        divOpacity.remove();
    });

    // Botón Aceptar
    let btnSubmit = document.createElement("button");
    btnSubmit.type = "submit";
    btnSubmit.textContent = "Acceder";
    btnSubmit.className = "pta-modal-btn pta-modal-btn-submit";

    btnContainer.appendChild(btnCancel);
    btnContainer.appendChild(btnSubmit);
    form.appendChild(btnContainer);

    // 6. Enviar formulario por Fetch (AJAX)
    form.addEventListener("submit", function (e) {
        e.preventDefault();

        btnSubmit.disabled = true;
        btnSubmit.textContent = "Verificando...";
        errorMsg.classList.add("hidden");

        let formData = new FormData(this);
        formData.append("ot_id", otId);

        let baseUrl = window.baseUrl || "";

        fetch(baseUrl + "/admin/pta/verify-temp-password", {
            method: "POST",
            body: formData,
            headers: {
                "X-Requested-With": "XMLHttpRequest"
            }
        })
            .then(response => response.json().then(data => ({ status: response.status, body: data })))
            .then(result => {
                if (result.status === 200 && result.body.success) {
                    // Contraseña correcta: redigir a la URL que manda el server
                    window.location.href = result.body.redirect_url;
                } else {
                    // Contraseña incorrecta u otro error
                    errorMsg.textContent = result.body.message || "Error de verificación.";
                    errorMsg.classList.remove("hidden");
                    btnSubmit.disabled = false;
                    btnSubmit.textContent = "Ingresar";
                    inputPassword.value = "";
                    inputPassword.focus();
                }
            })
            .catch(error => {
                console.error("Error en petición PTA:", error);
                errorMsg.textContent = "Ocurrió un error. Intente nuevamente.";
                errorMsg.classList.remove("hidden");
                btnSubmit.disabled = false;
                btnSubmit.textContent = "Ingresar";
            });
    });

    // Armar el DOM
    modalContainer.appendChild(title);
    modalContainer.appendChild(desc);
    modalContainer.appendChild(form);
    divOpacity.appendChild(modalContainer);

    // Enfocar el input al abrir y cerrar al hacer clic afuera
    divOpacity.addEventListener("click", function (e) {
        if (e.target === divOpacity) {
            divOpacity.remove();
        }
    });

    document.body.appendChild(divOpacity);
    inputPassword.focus();
}

// ────────────────────────────────────────────────────────────────────────────
// 2DA PASADA — toggle checkbox (PTA table partial, cargado via AJAX)
// La función debe estar en window porque el partial se inyecta via innerHTML
// y sus <script> no se ejecutan.
// ────────────────────────────────────────────────────────────────────────────
window.handleP2Checkbox = function (p2Id) {
    const chk = document.getElementById("chk-p2-" + p2Id);
    const hdnAct = document.getElementById("inp-p2-activa-" + p2Id);

    if (!chk) return;

    const activate = chk.checked;

    window._setP2Rows(p2Id, activate);
    if (hdnAct) hdnAct.value = activate ? "1" : "0";
};

window._setP2Rows = function (p2Id, show) {
    // La fila de 2da pasada tiene style="display:none" inline; se manipula con style.display.
    // También se busca el índice -0 que es el único que existe en modo captura.
    [0, 1, 2].forEach(function (i) {
        const row = document.getElementById("row-p2-" + p2Id + "-" + i);
        if (row) row.style.display = show ? "" : "none";
    });
};

// ────────────────────────────────────────────────────────────────────────────
// VISOR DE DIBUJOS / PLANOS PDF — Acceso del Operador
// ────────────────────────────────────────────────────────────────────────────

/** Abre el visor de dibujos/planos para una OT */
window.openDibujosViewer = function (otId = null, claseNombre = null) {
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

    // Boton cerrar
    const divCerrar = document.createElement('div');
    divCerrar.className = 'div-cerrar';
    const btnCerrar = document.createElement('button');
    btnCerrar.className = 'btn-cerrar';
    btnCerrar.onclick = () => closeAndViewerLog("Consulta Dibujos Técnicos", `El operador finalizó la revisión de planos.`);
    const imgCerrar = document.createElement('img');
    imgCerrar.className = 'img-cerrar';
    imgCerrar.src = window.cerrarImgUrl;
    btnCerrar.appendChild(imgCerrar);
    divCerrar.appendChild(btnCerrar);

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

            selOT.addEventListener('change', () => {
                const selOTVal = selOT.value;
                selClase.innerHTML = '<option value="">— Seleccionar Clase —</option>';
                if (selOTVal && estructura[selOTVal]) {
                    estructura[selOTVal].forEach(clase => {
                        const opt = document.createElement('option');
                        opt.value = clase;
                        opt.textContent = clase;
                        selClase.appendChild(opt);
                    });
                    selClase.disabled = false;
                } else {
                    selClase.disabled = true;
                }
                // Búsqueda automática al cambiar OT (si hay clase)
                if (selOTVal && selClase.value) {
                    const otText = selOT.options[selOT.selectedIndex].text;
                    _dibujosCargarArchivos(selOTVal, selClase.value, contentDiv, otText);
                }
            });

            selClase.addEventListener('change', () => {
                const ot = selOT.value;
                const clase = selClase.value;
                if (ot && clase) {
                    const otText = selOT.options[selOT.selectedIndex].text;
                    _dibujosCargarArchivos(ot, clase, contentDiv, otText);
                }
            });

            if (exactActiveOT && Object.keys(estructura).some(k => window.eq(k, exactActiveOT))) {
                selOT.value = exactActiveOT;
                selOT.dispatchEvent(new Event('change'));
                setTimeout(() => {
                    const opt = Array.from(selClase.options).find(o => window.eq(o.value, activeClase));
                    if (opt) {
                        opt.selected = true;
                        const otText = selOT.options[selOT.selectedIndex].text;
                        _dibujosCargarArchivos(exactActiveOT, opt.value, contentDiv, otText);
                    }
                }, 50);
            }
        })
        .catch((err) => {
            console.error(err);
            contentDiv.innerHTML = '<p style="color:#d9534f;text-align:center;padding:2em;font-weight:bold;font-style:italic;">ERROR AL CARGAR LA ESTRUCTURA DE CARPETAS. ' + err.message + '</p>';
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
    if (selectId === 'd-viewer-clase') select.disabled = true;

    wrap.appendChild(label);
    wrap.appendChild(select);
    return wrap;
}

/** Carga y renderiza los archivos PDF de la carpeta OT/Clase indicada */
function _dibujosCargarArchivos(ot, clase, contentDiv, otText = '') {
    contentDiv.innerHTML = '<p style="color:#666;text-align:center;">Cargando archivos...</p>';
    // Obtener el nombre completo de la OT de la lista de opciones si está disponible
    const selOTViewer = document.getElementById('d-viewer-ot');
    const otDisplay = selOTViewer ? selOTViewer.options[selOTViewer.selectedIndex].text : ot;
    window.logUserAction("Consulta Dibujos Técnicos", `El operador revisó los dibujos técnicos de ${otDisplay} - ${clase}`);

    const url = `${window.baseUrl}/dibujos/archivos?ot=${encodeURIComponent(ot)}&clase=${encodeURIComponent(clase)}`;
    fetch(url, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            if (!data.existe || !data.archivos || data.archivos.length === 0) {
                _dibujosShowEmpty(contentDiv);
                return;
            }
            _dibujosRenderArchivos(data.archivos, otText || ot, clase, contentDiv);
        })
        .catch(() => _dibujosShowEmpty(contentDiv));
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
                    <img src="${window.baseUrl}/images/pdf-view-shadow.png" class="prod-viewer-icon icon-default">
                    <img src="${window.baseUrl}/images/pdf-view.png" class="prod-viewer-icon icon-hover">
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
function _dibujosShowEmpty(contentDiv) {
    contentDiv.innerHTML = '<p style="text-align:center; padding: 2em; color: #666; font-style: italic;">No se encontraron archivos en este directorio.</p>';
    toastpremium('No hay archivos disponibles en esta categoría. Favor de reportar con el departamento de Programacion CNC o de Software.', 'error');
}

// ────────────────────────────────────────────────────────────────────────────
// VISOR DE MANUALES DE PROCESOS — Acceso del Operador
// ────────────────────────────────────────────────────────────────────────────

window.openManualesViewer = function () {
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

    const divCerrar = document.createElement('div');
    divCerrar.className = 'div-cerrar';
    const btnCerrar = document.createElement('button');
    btnCerrar.className = 'btn-cerrar';
    btnCerrar.onclick = () => closeManualLog();
    const imgCerrar = document.createElement('img');
    imgCerrar.className = 'img-cerrar';
    imgCerrar.src = window.cerrarImgUrl;
    btnCerrar.appendChild(imgCerrar);
    divCerrar.appendChild(btnCerrar);

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
                    _dibujosShowEmpty(contentDiv);
                }
            });
        })
        .catch(() => {
            contentDiv.innerHTML = '<p style="color:#9c0300;text-align:center;">Error al cargar la estructura.</p>';
        });
};

function _manualesCargarArchivos(proceso, contentDiv) {
    contentDiv.innerHTML = '<p style="color:#666;text-align:center;">Cargando archivos...</p>';
    const url = `${window.baseUrl}/manuales/archivos?proceso=${encodeURIComponent(proceso)}`;
    fetch(url, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            if (!data.existe || !data.archivos || data.archivos.length === 0) {
                _dibujosShowEmpty(contentDiv);
                return;
            }
            _dibujosRenderArchivos(data.archivos, proceso, 'General', contentDiv);
        })
        .catch(() => _dibujosShowEmpty(contentDiv));
}

// ────────────────────────────────────────────────────────────────────────────
// VISOR DE AYUDAS VISUALES — Acceso del Operador
// ────────────────────────────────────────────────────────────────────────────

window.openAyudasViewer = function () {
    const activeProceso = window.arrayData ? window.arrayData.process : '';
    const activeClase = window.arrayData ? (window.arrayData.class || '') : '';

    const divOpacity = document.createElement('div');
    divOpacity.className = 'prod-viewer-portal';
    divOpacity.id = 'div-opacity-ayudas';

    const modal = document.createElement('div');
    modal.className = 'prod-viewer-modal';

    const headerDiv = document.createElement('div');
    headerDiv.className = 'prod-viewer-header';

    const divCerrar = document.createElement('div');
    divCerrar.className = 'div-cerrar';
    const btnCerrar = document.createElement('button');
    btnCerrar.className = 'btn-cerrar';
    btnCerrar.onclick = () => divOpacity.remove();
    const imgCerrar = document.createElement('img');
    imgCerrar.className = 'img-cerrar';
    imgCerrar.src = window.cerrarImgUrl;
    btnCerrar.appendChild(imgCerrar);
    divCerrar.appendChild(btnCerrar);

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
                }
            });
        })
        .catch(() => {
            contentDiv.innerHTML = '<p style="color:#9c0300;text-align:center;">Error al cargar la estructura.</p>';
        });
};

/**
 * Abre el modal técnico con pestañas para Manuales y Ayudas Visuales
 */
window.openTechDocsModal = function () {
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

    const divCerrar = document.createElement('div');
    divCerrar.className = 'div-cerrar';
    const btnCerrar = document.createElement('button');
    btnCerrar.className = 'btn-cerrar';
    btnCerrar.onclick = () => divOpacity.remove();
    const imgCerrar = document.createElement('img');
    imgCerrar.className = 'img-cerrar';
    imgCerrar.src = window.cerrarImgUrl;
    btnCerrar.appendChild(imgCerrar);
    divCerrar.appendChild(btnCerrar);

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
        contentDiv.innerHTML = '<p style="color:#9c0300;text-align:center;">Error al cargar estructuras.</p>';
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
                selProcesoWrap.classList.add("hidden");

                if (activeTab === 'manuales') {
                    selProcesoWrap.classList.remove("hidden");
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
                    selProcesoWrap.classList.remove("hidden");
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
                alert("Error al cambiar pestaña: " + err.message);
                console.error(err);
            }
        };
    });

    function _techDocsShowEmpty(containerDiv, msg) {
        containerDiv.innerHTML = '';
        const alertDiv = document.createElement('div');
        alertDiv.classList.add("prod-alert-div");
        const msgLbl = document.createElement('label');
        msgLbl.className = 'label-alert';
        msgLbl.classList.add("label-alert");
        msgLbl.textContent = msg || 'No hay documentos disponibles en esta ubicación.';
        alertDiv.appendChild(msgLbl);
        containerDiv.appendChild(alertDiv);
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
                        <img src="${window.baseUrl}/images/pdf-view-shadow.png" class="prod-viewer-icon icon-default">
                        <img src="${window.baseUrl}/images/pdf-view.png" class="prod-viewer-icon icon-hover">
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

/**
 * toastpremium
 * Sistema de alertas premium restaurado para la vista de producción.
 * Utiliza clases CSS .toastpremium definidas en processProduction.css
 */

// Alias para compatibilidad con código que use mostrarNotificacion
window.mostrarNotificacion = toastpremium;

// Manejadores para el selector de material de soldadura con opción "Otro"
window.handlePTAMaterialSelectChange = function(idWidget) {
    const selectEl = document.getElementById('select_' + idWidget);
    const otroWrap = document.getElementById('otro_wrap_' + idWidget);
    const inputEl = document.getElementById('input_' + idWidget);

    if (selectEl.value === '__otro__') {
        const name = selectEl.name;
        selectEl.name = '';
        selectEl.classList.add("hidden");

        otroWrap.classList.remove("hidden");
        otroWrap.classList.add('visible');
        inputEl.name = name;
        inputEl.disabled = false; // ← Asegurar habilitado en JS al alternar
        inputEl.focus();
    }
};

window.handlePTAMaterialBackClick = function(idWidget, originalName) {
    const selectEl = document.getElementById('select_' + idWidget);
    const otroWrap = document.getElementById('otro_wrap_' + idWidget);
    const inputEl = document.getElementById('input_' + idWidget);

    inputEl.name = '';
    inputEl.value = '';
    inputEl.disabled = true; // ← Deshabilitar para que no interfiera ni se envíe vacío
    otroWrap.classList.add("hidden");
    otroWrap.classList.remove('visible');

    selectEl.name = originalName;
    selectEl.value = '';
    selectEl.classList.remove("hidden");
};

