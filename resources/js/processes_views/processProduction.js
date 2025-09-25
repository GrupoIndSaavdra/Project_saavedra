import { Process } from "./Process.js";

function createSelects(labelText, className) {
    let form_grid = document.querySelector(".form-grid");

    let form_group = document.createElement("div");
    form_group.className = "form-group";

    let select = document.createElement("select");
    select.className = `form-control ${className}`;
    select.name = className;
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

            let optionEmpty = document.createElement("option");
            optionEmpty.value = "";
            optionEmpty.textContent = "Selecciona una opción";
            input.appendChild(optionEmpty);
            for (let i = 1; i <= 7; i++) {
                let option = document.createElement("option");
                option.value = `${i}`;
                option.textContent = `Maquina ${i}`;
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
        }
        input.required = true;
        input.name = arrayNames[arrayTimes.indexOf(element)];

        let label = document.createElement("label");
        label.textContent = element;
        label.className = "form-label";
        label.setAttribute("for", "processName");

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
            value != null &&
            value !== ""
        ) {
            let input = document.createElement("input");
            input.name = key;
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
                input.type = "text";
                input.className = "form-control normal-input";
                input.value = key == "remainingPieces" ? value : value;
                input.name = key;
                input.readOnly = true;
            }

            let label = document.createElement("label");
            label.textContent = arrayKeyNames[key];
            label.className = "form-label";
            label.setAttribute("for", "processName");

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
                modifySelects(processes, document.querySelector(".process"), "Proceso");
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

    let inputPassword = document.createElement("input");
    inputPassword.type = "password";
    inputPassword.name = "passwordAdmin";
    inputPassword.placeholder = "Contraseña de administrador";
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
            let table = insertTableOnForm(null, null, scrollableTable, form);
            //Insertar boton de ELEGIR o GUARDAR
            insertButton_saveOrChoose(form, table);
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
    inputProcess.value = window.arrayData["process"];
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
    let process = new Process(
        window.arrayData["process"],
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

                insertAvaliablePiecesSelect(table); // Insertar select de piezas disponibles

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
function insertAvaliablePiecesSelect(table) {
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

    // Insertar el select en la tabla
    let tr = document.createElement("tr");
    let td = document.createElement("td");
    td.appendChild(select);
    tr.appendChild(td);
    table.appendChild(tr);
}

function showDivAlert(text, close, img) {
    let div_padre = document.createElement("div");
    div_padre.className = "div-opacity";
    div_padre.id = "div-opacity";

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
            let form_group_password = createInputPassword();
            let table_meta = document.querySelector(".table-meta");

            let input_hidden = document.createElement("input");
            input_hidden.type = "hidden";
            input_hidden.name = "meta";
            input_hidden.value = window.arrayData["meta"].id;

            form_group_password.appendChild(input_hidden);
            table_meta.before(form_group_password);

            btn_edit.src = window.back;
        } else {
            let form_group_password = document.querySelector(".form-group-password");
            if (form_group_password) {
                form_group_password.remove();
            }
            btn_edit.src = window.edit;
        }
    });

    return btn_edit;
}

function addEventToFinishReport() {
    let btn_finishReport = document.querySelector(".btn-finishReport");
    let reporteTerminado = false; // 🔒 control de estado

    // Mostrar botón y manejar clic
    btn_finishReport.style.opacity = "1";
    btn_finishReport.addEventListener("click", function () {
        if (confirm("¿Estás seguro de que deseas terminar el reporte?")) {
            reporteTerminado = true; // ✅ permitir salir sin advertencia
            window.location.href = window.baseUrl + "/processProduction/finishReport/" + window.arrayData["meta"].id;
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

            form.appendChild(input_meta);
            form.appendChild(input_hidden);
            form.appendChild(createInputPassword());

            img.before(form);
        } else {
            div.querySelector(".form-verifyPassword").remove();
            div.style.width = "auto";
        }
        img.src = img.src == window.imgEditPieces ? window.back : window.imgEditPieces;
    };
}

//Ejecucion del script
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
        }
    } else {
        createInputsWithValue(window.arrayData); // Crear inputs con los valores de la meta
        document.querySelector(".div-table-meta").appendChild(createBtnMetaEdit()); // Insertar botón de editar meta
        addEventToFinishReport(); // Agregar evento al botón de terminar reporte
        enableTable(); // Habilitar la tabla de piezas
        if (window.arrayData["machinedPiecesInMeta"] && window.arrayData["machinedPiecesInMeta"].length > 0) {
            insertButtonEditPieces(); // Insertar botón para editar piezas
        }
    }
} else {
    if (window.workOrders != null) {
        insertSelects();
    } else {
        //Si aun no existen Ordenes de Trabajo disponibles mostrar Div de alerta
        let body = document.querySelector("body");
        body.appendChild(showDivAlert("Aun no hay ordenes de trabajo disponibles", false));
    }
}
