import { Process } from "./Process.js";

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
                submit.style.opacity = "1";
            }
            select.style.backgroundColor = "#ffffff";
            select.style.color = "#000000";
        } else {
            if (className === "subprocess") {
                let submit = document.querySelector(".btn-submit");
                if (submit) {
                    submit.style.opacity = "0";
                }
            }
            select.style.backgroundColor = "#033966";
            select.style.color = "#ffffff";
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
                select.style.backgroundColor = "#033966";
                select.style.color = "#ffffff";
                select.disabled = true;
                select.innerHTML = ""; // Limpiar las opciones existentes
                let optionEmpty = document.createElement("option");
                optionEmpty.value = "";
                optionEmpty.textContent = "No disponible";
                select.appendChild(optionEmpty);
                let submit = document.querySelector(".btn-submit");
                if (submit) {
                    submit.style.opacity = "0";
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
            for (let i = 1; i <= 45; i++) {
                let option = document.createElement("option");
                if (i == 1 || i == 25 || i == 27) {
                    option.value = `${i}_${i + 1}`;
                    option.textContent = `Maquina ${i} y ${i + 1}`;
                    i++;
                } else {
                    option.value = `${i}`;
                    option.textContent = `Maquina ${i}`;
                }
                input.appendChild(option);
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
    let submit = document.createElement("button");
    submit.type = "submit";
    submit.className = "btn-submit";
    submit.textContent = "Registrar";
    form_principal_data.appendChild(submit);
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
                input.style.backgroundColor = "#033966dd";
                input.style.color = "#ffffff";
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
        document.querySelector(".form-principal-data").appendChild(createBtnSubmit_editMeta());
    }
}
function createBtnSubmit_editMeta() {
    let submit = document.createElement("button");
    submit.type = "submit";
    submit.className = "btn-submit";
    submit.style.opacity = "1"; // Mostrar el botón de submit
    submit.textContent = "Editar";
    return submit;
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
                let classes = window.workOrders[selectedValue];
                modifySelects(classes, document.querySelector(".class"), "Clase");
            }
        });
        //prettier-ignore
        selectClasses.addEventListener("change", function () {
            let selectedClass = selectClasses.value;
            if (selectedClass) {
                let processes = window.workOrders[selectWO.value][selectedClass];
                if (processes.length > 0) {
                    modifySelects(processes, document.querySelector(".process"), "Proceso");
                }
            }
        });
        //prettier-ignore
        selectProcesses.addEventListener("change", function () {
            let selectedProcess = selectProcesses.value;
            let submit = document.querySelector(".btn-submit");
            if (selectedProcess && selectedProcess === "Operacion Equipo") {
                let selectSubprocesses = createSelects("Subproceso", "subprocess");
                modifySelects(["1 operacion", "2 operacion"], selectSubprocesses, "Subproceso");
            } else if (selectedProcess) {
                submit.style.opacity = "1";
            } else {
                submit.style.opacity = "0";
            }
        });
        createInputs();
    } else {
        let p = document.createElement("p");
        p.textContent = "No hay órdenes de trabajo disponibles.";
        form_grid.classList.add("form-grid-one");
        form_grid.appendChild(p);
    }
}

function createInputPassword() {
    //Creacion de input
    let form_group = document.createElement("div");
    form_group.className = "form-group-password";
    form_group.style.width = "100%";
    form_group.style.zIndex = "1000";

    let inputPassword = document.createElement("input");
    inputPassword.type = "password";
    inputPassword.name = "passwordAdmin";
    inputPassword.placeholder = "Password Admin";
    inputPassword.className = "normal-input input-password";
    inputPassword.required = true;

    //Creacion de boton de submit
    let submit = document.createElement("button");
    submit.type = "submit";
    submit.className = "btn-submit-password";
    submit.textContent = "Verificar";
    form_group.appendChild(inputPassword);
    form_group.appendChild(submit);

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
            body.appendChild(
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
            //Mostrar DIV de alerta para "No hay piezas disponibles"
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
    div_padre.remove();
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
        let table = document.querySelector(".table");
        disabledInputsTable(table);
    }
    redirectToEndTable();


}
function disabledInputsTable(table) {
    if (table) {
        let inputs = table.querySelectorAll("input");
        if (inputs.length > 0) {
            inputs.forEach((input) => {
                if (input.className != "input input-pieceUsed" && input.className != "input-medio input-pieceUsed") {
                    if (window.arrayData["edit"] == 2) {
                        input.disabled =
                            input.parentElement.parentElement.className === "table-row-cNominals" ? true : false;
                    } else {
                        input.disabled = true;
                    }
                } else {
                    input.style.backgroundColor = "#fff";
                }
            });
        }
    }
}
function redirectToEndTable() {
    window.onload = function () {
        const lastLine = document.querySelector(".table tr:last-child");
        if (lastLine) {
            lastLine.scrollIntoView({ behavior: "smooth", block: "end" });
        }
    };
}
function createBtnMetaEdit() {
    let btn_edit = document.createElement("img");
    btn_edit.className = "img-edit";
    btn_edit.src = window.edit;
    btn_edit.alt = "Editar";

    btn_edit.addEventListener("click", function () {
        if (btn_edit.src == window.edit) {
            //Insertar el div-opacity
            document.querySelector("body").appendChild(createDivOpacity());

            let form_group_password = createInputPassword();
            let table_meta = document.querySelector(".table-meta");

            let input_hidden = document.createElement("input");
            input_hidden.type = "hidden";
            input_hidden.name = "meta";
            input_hidden.value = window.arrayData["meta"].id;

            form_group_password.appendChild(input_hidden);
            table_meta.before(form_group_password);

            btn_edit.src = window.back;
            btn_edit.style.zIndex = "1000";
        } else {
            let form_group_password = document.querySelector(".form-group-password");
            if (form_group_password) {
                form_group_password.remove();
                let div_opacity = document.getElementById("div-opacity");
                if (div_opacity) {
                    div_opacity.remove();
                }
            }
            btn_edit.src = window.edit;
            btn_edit.style.zIndex = "1";
        }
    });

    return btn_edit;
}

function addEventToFinishReport() {
    let btn_finishReport = document.querySelector(".btn-finishReport");
    let reporteTerminado = false; // control de estado

    // Mostrar botón y manejar clic
    btn_finishReport.style.opacity = "1";
    btn_finishReport.addEventListener("click", function () {
        if (confirm("¿Estás seguro de que deseas terminar el reporte?")) {
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

    //Crear el boton de cancelar edición
    div.appendChild(createBtnCancelEdit());
}
function createBtnCancelEdit() {
    let btn_cancel = document.createElement("a");
    btn_cancel.href = "#";
    btn_cancel.className = "btn-cancel";
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

// Editar las piezas ya registradas en la meta
function insertButtonEditPieces() {
    let div = document.createElement("div");
    div.className = "div-editPieces";

    let img = document.createElement("img");
    img.src = window.imgEditPieces;
    img.className = "img-editPieces";
    img.alt = "Editar piezas";
    div.appendChild(img);
    document.querySelector(".container-meta").appendChild(div);

    //Agregar evento onclick a la imagen
    img.onclick = function () {
        //Insertar campo de contraseña para editar piezas
        if (img.src == window.imgEditPieces) {
            document.querySelector("body").appendChild(createDivOpacity());

            div.style.width = "40%";
            let form = createForm("/processProduction/verified");
            form.className = "form-verifyPassword";

            let input_hidden = document.createElement("input");
            input_hidden.type = "hidden";
            input_hidden.name = "editPieces";
            input_hidden.value = true;

            let input_meta = document.createElement("input");
            input_meta.type = "hidden";
            input_meta.name = "meta";
            input_meta.value = window.arrayData["meta"].id;

            let form_group_password = createInputPassword();
            form.appendChild(form_group_password);
            form.appendChild(input_meta);
            form.appendChild(input_hidden);

            img.before(form);
            div.style.zIndex = "1000";
        } else {
            div.querySelector(".form-verifyPassword").remove();
            div.style.width = "auto";
            div.style.zIndex = "1";
            let div_opacity = document.getElementById("div-opacity");
            if (div_opacity) {
                div_opacity.remove();
            }
        }
        img.src = img.src == window.imgEditPieces ? window.back : window.imgEditPieces;
    };
}

// Botón de verificación de calidad para liberación de piezas
function insertButtonQualityCheck() {
    let div = document.createElement("div");
    div.className = "div-qualityCheck";

    let img = document.createElement("img");
    img.src = window.imgQualityCheck;
    img.className = "img-qualityCheck";
    img.alt = "Liberación de calidad";
    img.title = "Liberación de piezas por calidad";
    div.appendChild(img);
    document.querySelector(".container-meta").appendChild(div);

    // Agregar evento onclick a la imagen
    img.onclick = function () {
        if (!document.querySelector(".form-verifyQualityPassword")) {
            document.querySelector("body").appendChild(createDivOpacity());

            div.style.width = "40%";

            // Crear formulario que manejará la verificación via AJAX
            let form = document.createElement("form");
            form.className = "form-verifyQualityPassword";
            form.onsubmit = function (e) {
                e.preventDefault();
                verifyQualityPasswordAjax(form);
            };

            let input_meta = document.createElement("input");
            input_meta.type = "hidden";
            input_meta.name = "meta";
            input_meta.value = window.arrayData["meta"].id;

            let form_group_password = createInputPasswordQuality();
            form.appendChild(form_group_password);
            form.appendChild(input_meta);

            img.before(form);
            div.style.zIndex = "1000";
            img.src = window.back;
        } else {
            div.querySelector(".form-verifyQualityPassword").remove();
            div.style.width = "auto";
            div.style.zIndex = "1";
            let div_opacity = document.getElementById("div-opacity");
            if (div_opacity) {
                div_opacity.remove();
            }
            img.src = window.imgQualityCheck;
        }
    };
}

// Verificar contraseña de calidad mediante AJAX
function verifyQualityPasswordAjax(form) {
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
                // Cerrar el formulario de contraseña
                let divQuality = document.querySelector(".div-qualityCheck");
                if (divQuality) {
                    let formPassword = divQuality.querySelector(".form-verifyQualityPassword");
                    if (formPassword) {
                        formPassword.remove();
                    }
                    divQuality.style.width = "auto";
                    divQuality.style.zIndex = "1";
                    let imgQuality = divQuality.querySelector(".img-qualityCheck");
                    if (imgQuality) {
                        imgQuality.src = window.imgQualityCheck;
                    }
                }

                // Cerrar el div de opacidad actual
                let div_opacity = document.getElementById("div-opacity");
                if (div_opacity) {
                    div_opacity.remove();
                }

                // Mostrar el modal de liberación de piezas
                showQualityReleaseModal(data.pieces, data.qualityUser);
            } else {
                alert(data.message || "Contraseña incorrecta");
            }
        })
        .catch(error => {
            console.error("Error:", error);
            alert("Error al verificar la contraseña. Intente de nuevo.");
        });
}

function createInputPasswordQuality() {
    // Creación de input para contraseña de calidad
    let form_group = document.createElement("div");
    form_group.className = "form-group-password";
    form_group.style.width = "100%";
    form_group.style.zIndex = "1000";

    let inputPassword = document.createElement("input");
    inputPassword.type = "password";
    inputPassword.name = "passwordQuality";
    inputPassword.placeholder = "Password Quality";
    inputPassword.className = "normal-input input-password";
    inputPassword.required = true;

    // Creación de botón de submit
    let submit = document.createElement("button");
    submit.type = "submit";
    submit.className = "btn-submit-password btn-quality";
    submit.textContent = "Verificar";
    form_group.appendChild(inputPassword);
    form_group.appendChild(submit);

    return form_group;
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
                    this.style.backgroundColor = color;
                    this.style.color = "#000";
                    this.style.fontWeight = "bold";
                } else {
                    this.style.backgroundColor = "";
                    this.style.color = "";
                    this.style.fontWeight = "";
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
        td.style.textAlign = "center";
        td.style.padding = "2em";
        row.appendChild(td);
        tbody.appendChild(row);
    }
    table.appendChild(tbody);
    tableContainer.appendChild(table);
    form.appendChild(tableContainer);

    // Botones de acción
    let buttonContainer = document.createElement("div");
    buttonContainer.className = "button-container";

    let btnCancel = document.createElement("button");
    btnCancel.type = "button";
    btnCancel.className = "btn-cancel-release";
    btnCancel.textContent = "Cancelar";
    btnCancel.onclick = function () {
        closeQualityModal();
    };
    buttonContainer.appendChild(btnCancel);

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

function closeQualityModal() {
    let divOpacity = document.getElementById("div-opacity");
    if (divOpacity) {
        divOpacity.remove();
    }
    // Restaurar el botón de calidad
    let divQuality = document.querySelector(".div-qualityCheck");
    if (divQuality) {
        divQuality.style.width = "auto";
        divQuality.style.zIndex = "1";
        let imgQuality = divQuality.querySelector(".img-qualityCheck");
        if (imgQuality) {
            imgQuality.src = window.imgQualityCheck;
        }
    }
}

//Ejecucion del script
//Evitar doble click en el submit
document.addEventListener("submit", (e) => {
    const btn = e.target.querySelector("button[type='submit']");
    console.log(btn);
    if (btn) btn.disabled = true;
});

if (window.arrayData) {
    console.log(window.arrayData);
    if (window.arrayData["edit"]) {
        if (window.arrayData["edit"] == 1) {
            //Cambiar la ruta del formulario a la de editar y crear el botón de cancelar edición
            changeFormRoute(
                document.querySelector(".div-table-meta"),
                document.querySelector(".form-principal-data"),
                "/processProduction/editMeta"
            );
            let btnCancel = document.querySelector(".btn-cancel");
            btnCancel.style.top = "0";
            btnCancel.style.left = "0";
            btnCancel.style.margin = "1.5em";
            if (window.arrayData["numberPieces"] > 0) {
                // Si ya se han registrado piezas, solo habilitar los inputs de tiempo y fecha
                createInputsWithValue(window.arrayData, ["startTime", "endTime", "date"]);
            } else {
                // Si no se han registrado piezas, habilitar todos los inputs
                insertSelects();
            }
        } else {
            createInputsWithValue(window.arrayData); // Crear inputs con los valores de la meta
            enableTable(); // Habilitar la tabla de piezas
            //Cambiar la ruta del formulario a la de editar y crear el botón de cancelar edición
            changeFormRoute(
                document.querySelector(".container-meta"),
                document.querySelector(".form-tablePieces"),
                "/processProduction/editPieces"
            );
            let btnCancel = document.querySelector(".btn-cancel");
            btnCancel.style.bottom = "0";
            btnCancel.style.right = "6em";
            btnCancel.style.margin = "-2em";
        }
    } else {
        createInputsWithValue(window.arrayData); // Crear inputs con los valores de la meta
        document.querySelector(".div-table-meta").appendChild(createBtnMetaEdit()); // Insertar botón de editar meta
        addEventToFinishReport(); // Agregar evento al botón de terminar reporte
        enableTable(); // Habilitar la tabla de piezas
        if (window.arrayData["machinedPiecesInMeta"] && window.arrayData["machinedPiecesInMeta"].length > 0) {
            insertButtonEditPieces(); // Insertar botón para editar piezas
            insertButtonQualityCheck(); // Insertar botón para liberación de calidad
        }
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

    // Fix for "Operacion Equipo" which might have suffixes like "_1 operacion" in the frontend
    // but is keyed as "Operacion Equipo" in the backend history
    let lookupName = currentProcessName;

    // Find the matching process in history
    let processData = history[lookupName];

    if (processData) {
        let processSection = document.createElement("div");
        processSection.className = "process-section";

        let processTitle = document.createElement("h3");
        processTitle.className = "process-title";
        processTitle.innerText = currentProcessName; // Keep the specific name for the title
        processSection.appendChild(processTitle);

        // Inject click access for Soldadura PTA temporal session
        if (currentProcessName === "Soldadura y Soldadura PTA" && window.arrayData.process === "Soldadura PTA") {
            processSection.style.cursor = "pointer";
            processSection.title = "Añadir / Ver Resultados de Soldadura PTA";
            processSection.addEventListener("click", function () {
                let otId = window.arrayData.meta ? window.arrayData.meta.id_ot : null; // Fetch current OT ID
                if (otId) {
                    createPtaPasswordModal(otId);
                } else {
                    console.error("No se pudo obtener el OT ID de window.arrayData.meta");
                }
            });
        }

        let pieces = [processData.pieces.good, processData.pieces.bad];
        for (let i = 0; i < pieces.length; i++) {
            // Crear barra de progreso
            let progressBar = document.createElement("div");
            progressBar.className = "progress-bar";

            let progress = document.createElement("div");
            progress.className = i == 0 ? "good-progress progress" : "bad-progress progress";

            let percentage = consignmentPieces > 0 ? (pieces[i] * 100) / consignmentPieces : 0;
            progress.style.width = `${Math.min(percentage, 100)}%`;

            let formattedPercentage = percentage != 0 ? percentage.toFixed(1) : 0;
            let percentageLabel = document.createElement("div");
            percentageLabel.className = "progress-percentage";
            percentageLabel.innerText = pieces[i] == 1 ? `${formattedPercentage}% ${pieces[i]} pieza` : `${formattedPercentage}% ${pieces[i]} piezas`;

            progressBar.appendChild(progress);
            progressBar.appendChild(percentageLabel);
            processSection.appendChild(progressBar);
        }

        container.appendChild(processSection);
    } else {
        let processSection = document.createElement("div");
        processSection.className = "process-section";

        let processTitle = document.createElement("h3");
        processTitle.className = "process-title";
        processTitle.innerText = currentProcessName;
        processSection.appendChild(processTitle);

        // Render empty bars
        let pieces = [0, 0];
        for (let i = 0; i < pieces.length; i++) {
            // Crear barra de progreso
            let progressBar = document.createElement("div");
            progressBar.className = "progress-bar";

            let progress = document.createElement("div");
            progress.className = i == 0 ? "good-progress progress" : "bad-progress progress";
            progress.style.width = `0%`;

            let percentageLabel = document.createElement("div");
            percentageLabel.className = "progress-percentage";
            percentageLabel.innerText = `0% 0 piezas`;

            progressBar.appendChild(progress);
            progressBar.appendChild(percentageLabel);
            processSection.appendChild(progressBar);
        }
        container.appendChild(processSection);
    }

    // Insertar después de la tabla de metadatos (Código/Versión)
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
    icon.innerHTML = "🔒";
    icon.className = "pta-modal-icon";
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
        errorMsg.style.display = "none";

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
                    errorMsg.style.display = "block";
                    btnSubmit.disabled = false;
                    btnSubmit.textContent = "Ingresar";
                    inputPassword.value = "";
                    inputPassword.focus();
                }
            })
            .catch(error => {
                console.error("Error en petición PTA:", error);
                errorMsg.textContent = "Ocurrió un error. Intente nuevamente.";
                errorMsg.style.display = "block";
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
    const row = document.getElementById("row-p2-" + p2Id + "-0");
    if (row) row.style.display = show ? "" : "none";
};
