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

    if (
        labelText === "Subproceso" ||
        labelText === "Orden de trabajo" ||
        labelText === "Proceso"
    ) {
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

    let arrayElements =
        labelText == "Proceso" || labelText == "Subproceso"
            ? Object.values(array)
            : Object.keys(array);

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
        option.textContent =
            labelText == "Orden de trabajo"
                ? `${element} - ${array[element]["moldura"]}`
                : element;
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
                    let formGroupProcess =
                        document.querySelector("#processGroup");
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
                "Fecha": "date",
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
    };
    let form_grid = document.querySelector(".form-grid");
    for (const [key, value] of Object.entries(values)) {
        let form_group = document.createElement("div");
        form_group.className =
            key == "workOrder" || key == "subprocess"
                ? "form-group full-width"
                : "form-group";

        if (
            key != "operator" &&
            key != "meta" &&
            key != "edit" &&
            value != null &&
            value != ""
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
                input.value = value;
                input.name = key;
                input.readOnly = true;
            }
            console.log(input);

            let label = document.createElement("label");
            label.textContent = arrayKeyNames[key];
            label.className = "form-label";
            label.setAttribute("for", "processName");

            console.log(key, value);
            form_group.appendChild(input);
            form_group.appendChild(label);
            form_grid.appendChild(form_group);
        }
    }

    if(valuesEnabled.length > 0){
        let form_principalData = document.querySelector(".form-principal-data");
        let submit = document.createElement("button");
        submit.type = "submit";
        submit.className = "btn-submit";
        submit.style.opacity = "1"; // Mostrar el botón de submit
        submit.textContent = "Editar";
        form_principalData.appendChild(submit);
    }

    return document.querySelector(".div-table-meta");
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
                modifySelects(
                    classes,
                    document.querySelector(".class"),
                    "Clase"
                );
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
            modifySelects(["1ra Operacion", "2da Operacion"], selectSubprocesses, "Subproceso");
        } else if (selectedProcess){
            submit.style.opacity = "1";
        }else {
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

//Ejecucion del script
if (window.workOrders != null && window.arrayData == null) {
    insertSelects();
} else {
    if (window.arrayData["edit"]) {
        //VERIFICAR SI YA HAY PIEZAS REGISTRADAS Y HABILITAR SOLO LOS CAMPOS DISPONIBLES
        if (window.arrayData["numberPieces"] > 0) {
            createInputsWithValue(window.arrayData, [
                "startTime",
                "endTime",
                "date",
            ]);
        } else {
            insertSelects();
        }

        //Cambiar la ruta del formulario a la de editar
        let form_principal_data = document.querySelector(
            ".form-principal-data"
        );
        form_principal_data.action =
            window.baseUrl + "/processProduction/editMeta";
        let input_hidden = document.createElement("input");
        input_hidden.type = "hidden";
        input_hidden.name = "meta";
        input_hidden.value = window.arrayData["meta"].id;

        //Crear el boton de cancelar edición
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

        let div_table_meta = document.querySelector(".div-table-meta");
        div_table_meta.className = "div-table-meta";

        div_table_meta.appendChild(btn_cancel);
        form_principal_data.appendChild(input_hidden);
    } else {
        let div_table_meta = createInputsWithValue(window.arrayData);
        let btn_edit = document.createElement("img");
        btn_edit.className = "img-edit";
        btn_edit.src = window.edit;
        btn_edit.alt = "Editar";
        div_table_meta.appendChild(btn_edit);
    }
}

let btn_edit = document.querySelector(".img-edit");
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
        let form_group_password = document.querySelector(
            ".form-group-password"
        );
        if (form_group_password) {
            form_group_password.remove();
        }
        btn_edit.src = window.edit;
    }
});
