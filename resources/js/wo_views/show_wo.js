//Ejecución de la función para la creación del formulario de la clase
createForm(); //Creación del formulario de la clase

// Validación al enviar el formulario (Composición Química es obligatoria)
document.getElementById("form").addEventListener("submit", function (event) {
    let checkboxAddClass = document.querySelector(".checkbox-add-class");
    let isAdding = checkboxAddClass && checkboxAddClass.checked;
    let isEditing = document.getElementById("btn-saveClass") !== null;

    if (isAdding || isEditing) {
        let checkedChips = document.querySelectorAll(".chemical-composition-input:checked");
        let otroInput = document.querySelector('input[name="composicion_quimica_otro"]');
        let hasOtro = otroInput && otroInput.value.trim() !== "";

        // Normalizar el campo "otro" al enviar (solo admin=1 y master=3)
        if (otroInput && (window.profile == 1 || window.profile == 3)) {
            otroInput.value = normalizeChemicalInput(otroInput.value);
        }

        if (checkedChips.length === 0 && !hasOtro) {
            event.preventDefault();
            alert("Por favor, seleccione al menos una Composición Química o especifique otra.");
            return false;
        }
    }
});

function createForm() {
    let div_rows = document.querySelector(".div-rows"); //Obtención del div en donde se insertará el formulario
    div_rows.appendChild(createRowsForm(get_inputAttributes(window.workOrder.id, window.molding.nombre)[0])); //Creación del formulario de la clase
    let div_rowsHidden = document.createElement("div");
    div_rowsHidden.className = "div-rows-hidden hidden";
    div_rows.appendChild(div_rowsHidden);
}

// Funcion para obtener los atributos que se deben de implementar en los inputs del formulario
function get_inputAttributes(workOrder, molding, value = null) {
    let formInputs, formInputsHidden;

    formInputs = {
        workOrder: {
            label: "Orden de trabajo",
            input: {
                type: "text",
                value: workOrder,
                disabled: true,
            },
        },
        molding: {
            label: "Moldura",
            input: {
                type: "text",
                value: molding,
                disabled: true,
            },
        },
        table: {},
    };

    //Insertar los demas tamanios al arreglo
    let tamanios = [];
    if (value != null) {
        tamanios.push(value.tamanio);
        ["Chico", "Mediano", "Grande"].forEach((size) => {
            if (!tamanios.includes(size)) {
                tamanios.push(size);
            }
        });
    } else {
        tamanios = ["Chico", "Mediano", "Grande"];
    }

    formInputsHidden = {
        classType: {
            label: "Seleccione el tipo ",
            select: {
                name: "class",
                class: "classes",
            },
            options: ["Bombillo", "Molde", "Obturador", "Fondo", "Corona", "Plato", "Embudo", "Cabeza de Soplo", "Candado Obturador"],
        },
        size: {
            label: "Seleccione el tamaño",
            select: {
                name: "size",
                class: "selects",
            },
            options: tamanios,
        },
        composicionQuimica: {
            label: "Composición Química",
            fullWidth: true,
            required: true,
            options: ["HGSS10", "HGSS50V", "HG", "MINOX", "HG/MINOX", "DAMERON", "HG CR - NI", "VERMICULAR", "ACERO", "HG/METZ"],
            currentValue: value == null ? null : value.composicion_quimica,
            tipoSoldadura: value == null ? null : value.tipo_soldadura,
        },
        order: {
            label: "Pedido Total",
            input: {
                type: "number",
                name: "order",
                required: true,
                value: value == null ? null : value.pedido,
            },
        },
        pieces: {
            label: "Piezas con consignación",
            input: {
                type: "number",
                name: "pieces",
                required: true,
                value: value == null ? null : value.piezas,
            },
        },
        startDate: {
            label: "Fecha de inicio",
            input: {
                type: "date",
                name: "start_date",
                required: true,
                value: value == null ? null : value.fecha_inicio,
            },
        },
        startTime: {
            label: "Hora de inicio",
            input: {
                type: "time",
                name: "start_time",
                required: true,
                value: value == null ? null : value.hora_inicio,
            },
        },
        finishDate: {
            label: "Fecha de termino",
            input: {
                type: "date",
                name: "finish_date",
                disabled: true,
                value: value == null ? null : value.fecha_termino,
            },
        },
        finishTime: {
            label: "Hora de termino",
            input: {
                type: "time",
                name: "finish_time",
                disabled: true,
                value: value == null ? null : value.hora_termino,
            },
        },
    };
    //Eliminacion de valor del id de la clase en el input de tipo hidden
    let inputClassId = document.getElementById("idClass");
    if (inputClassId) {
        inputClassId.value = "";
        inputClassId.removeAttribute("value");
    }
    if (value != null) {
        //Modificacion de valor del id de la clase en el input de tipo hidden
        if (inputClassId) {
            inputClassId.setAttribute("value", value.id);
            inputClassId.value = value.id;
        }

        // //Modificación de las opciones del select de tamaño si la clase es obturador
        // if (value.nombre == "Obturador") {
        //     let sectionOptions = [1, 2];
        //     sectionOptions.splice(sectionOptions.indexOf(value.seccion), 1);
        //     sectionOptions.unshift(value.seccion);

        //     formInputsHidden.size = {
        //         label: "Selecciona la sección",
        //         select: {
        //             name: "section",
        //             class: "selects",
        //         },
        //         options: sectionOptions,
        //     };
        // }

        formInputsHidden.classType = {
            label: "Clase",
            input: {
                type: "text",
                value: value.nombre,
                name: "class",
                class: "classes",
                disabled: true,
            },
        };
    }
    return [formInputs, formInputsHidden];
}

function createRowsForm(formInputs) {
    let fragment = document.createDocumentFragment(); //Creación de un fragmento para insertar los elementos del formulario

    const keys = Object.keys(formInputs);
    let inputsCounter = 0; //Contador para los inputs que se van insertando en el formulario

    while (inputsCounter < keys.length) {
        let nameInput = keys[inputsCounter]; //Obtención del nombre del input

        if (nameInput === "table") {
            fragment.appendChild(createScrollableTable(window.classes));
            //Inserción de los elementos correspondientes al checkbox de agregar más clases
            insertWOButtons(fragment);
            inputsCounter++;
            continue;
        }

        let inputConfig = formInputs[nameInput];

        // Si este campo es fullWidth, crear una fila dedicada de ancho completo
        if (inputConfig.fullWidth) {
            let row = document.createElement("div");
            row.className = "row";

            let col = document.createElement("div");
            col.className = "column full-width-column";
            

            if (inputConfig.label != undefined) {
                let label = document.createElement("label");
                label.className = "label-form";
                let isRequired = inputConfig.required || (inputConfig.input && inputConfig.input.required);
                if (isRequired) {
                    label.innerHTML = inputConfig.label + ' <span style="color: #dc3545;">*</span>';
                } else {
                    label.textContent = inputConfig.label;
                }
                col.appendChild(label);
            }

            let htmlTag;
            if (nameInput === "composicionQuimica" && inputConfig.hasOwnProperty("options")) {
                htmlTag = createChemicalCompositionChips(inputConfig);
            } else if (nameInput === "composicionQuimica" && inputConfig.isTags) {
                htmlTag = createChemicalCompositionTags(inputConfig.value, inputConfig.tipoSoldadura ?? null);
            } else {
                let element = inputConfig.hasOwnProperty("select") ? "select" : "input";
                htmlTag = createSelectOrInput(element, inputConfig, nameInput);
            }

            col.appendChild(htmlTag);
            row.appendChild(col);
            fragment.appendChild(row);

            inputsCounter++;
            continue;
        }

        let row = document.createElement("div");
        row.className = "row";

        // Crear hasta 2 columnas por fila
        for (let j = 0; j < 2; j++) {
            nameInput = keys[inputsCounter];
            // Guard: salir si no hay más campos o si es tabla
            if (nameInput === undefined || formInputs[nameInput] === undefined) break;
            if (nameInput === "table") break;
            if (formInputs[nameInput].fullWidth) break; // Terminar fila actual si el siguiente es fullWidth

            let col = document.createElement("div");
            col.className = "column";

            //Creación del label correspondiente
            if (formInputs[nameInput].label != undefined) {
                let label = document.createElement("label");
                label.className = "label-form";
                let isRequired = formInputs[nameInput].required || (formInputs[nameInput].input && formInputs[nameInput].input.required);
                if (isRequired) {
                    label.innerHTML = formInputs[nameInput].label + ' <span style="color: #dc3545;">*</span>';
                } else {
                    label.textContent = formInputs[nameInput].label;
                }
                col.appendChild(label);
            }

            //Creación del input correspondiente
            let element = formInputs[nameInput].hasOwnProperty("select") ? "select" : "input";
            let attributesArray = formInputs[nameInput]; //Obtención de los atributos del input correspondiente
            let htmlTag = createSelectOrInput(element, attributesArray, nameInput);

            //Inserción de elementos
            col.appendChild(htmlTag);
            row.appendChild(col);

            inputsCounter++; //Incremento del contador de inputs
        }
        fragment.appendChild(row); //Inserción del div "row" en el fragmento
    }
    return fragment;
}

function insertWOButtons(fragment) {
    let div = document.createElement("div");
    div.className = "container-WOButtons";
    //Creación del botón de eliminar orden de trabajo
    let buttonDelete = document.createElement("a");
    buttonDelete.className = "btn-deleteWO action-btns";
    buttonDelete.textContent = "Eliminar orden de trabajo";
    buttonDelete.addEventListener("click", function () {
        event.preventDefault();
        let container_form = document.querySelector(".container-form");
        container_form.appendChild(mostrarDiv(`../destroyWO/${window.workOrder.id}`));
    });

    //Creación del botón de generar PDF de la orden de trabajo
    let buttonPDF = document.createElement("a");
    buttonPDF.className = "btn-pdfWO action-btns";
    buttonPDF.textContent = "Generar PDF";
    buttonPDF.href = `../generatePDFWO/${window.workOrder.id}`;

    let elements = [];
    if (window.profile != 5) {
        elements[1] = createCheckboxAddClass(); //Creación del checkbox de agregar clase
        div.appendChild(buttonDelete);
    }
    div.appendChild(buttonPDF);
    elements[0] = div;

    //Inserción de los botones en el div contenedor
    elements.forEach((element) => {
        fragment.appendChild(element);
    });
}

function createSelectOrInput(element, attributesArray, nameInput) {
    let htmlTag = document.createElement(element);
    for (let attribute in attributesArray[element]) {
        htmlTag.setAttribute(attribute, attributesArray[element][attribute]); //Insertar los atributos correspondientes al input
        if (window.profile == 5 && nameInput != "order" && nameInput != "pieces") {
            htmlTag.disabled = true;
        }
    }
    htmlTag.classList.add("form-control"); //Se añade la clase "form-control al input correspondiente"

    //Si el elemento es un select, se añaden las opciones correspondientes
    if (element == "select") {
        let options = attributesArray["options"];
        let currentValue = attributesArray["currentValue"] ?? null;
        for (let i = 0; i < options.length; i++) {
            let option = document.createElement("option");
            option.value = options[i];
            option.text = options[i];
            if (currentValue && options[i] === currentValue) {
                option.selected = true;
            }
            htmlTag.add(option);
        }
        if (nameInput == "classType") {
            //Si el select es el de tipo de clase, se añade un evento para modificar el select
            htmlTag.addEventListener("change", () => {
                modifySelect(htmlTag.value);
                createOperationsCheckBox(htmlTag.value, null, true);
                toggleWeldingTypeVisibility(htmlTag.value);
            });
        }
    }
    return htmlTag;
}

function modifySelect(className) {
    //Obtención del los elementos del select correspondiente
    let sizeSelect = document.querySelector(".selects");
    let label = sizeSelect.previousElementSibling.textContent;

    //If para verificart si es necesario modificar el select de tamaño dependendiendo del tipo de clase
    let divSelect = removeSelect(sizeSelect); //Recibe como parametro el select que se eliminara
    divSelect.appendChild(createSelect(className)); //Agrega el nuevo select al div padre
}

function removeSelect(select) {
    let parentDiv = select.parentElement; //Obtiene el div padre del select
    select.previousElementSibling.remove(); //Elimina el label del select
    select.remove(); //Elimina el select
    return parentDiv; //Retorna el div padre del select
}

function createSelect(className) {
    let fragment = document.createDocumentFragment(); //Creación de un fragmento para insertar los elementos del formulario

    //Creacion de los elementos
    let label = document.createElement("label");
    let select = document.createElement("select");
    select.className = "selects form-control";

    //Se crea un select con las opciones de tamaño
    label.textContent = "Seleccione el tamaño";
    select.name = "size";

    let options = ["Chico", "Mediano", "Grande"];
    for (let i = 0; i < 3; i++) {
        let option = document.createElement("option");
        option.value = options[i];
        option.text = options[i];
        select.add(option);
    }
    fragment.appendChild(label);
    fragment.appendChild(select);
    return fragment;
}

function createScrollableTable(classes = null) {
    let scrollableTable = document.createElement("div"); //Obtención de la tabla
    scrollableTable.className = "scrollabe-table"; //Clase de la tabla
    if (classes != null) {
        //Si no se reciben las clases, se muestra un mensaje de alerta
        scrollableTable.appendChild(createTableClasses(classes));
    } else {
        let div_alert = document.createElement("div");
        div_alert.className = "alert alert-danger text-center";
        div_alert.textContent = "Aún no se han registrado clases";
        scrollableTable.appendChild(div_alert);
    }
    return scrollableTable;
}

function createTableClasses(classes) {
    let fragment = document.createDocumentFragment(); //Creación de un fragmento para insertar los elementos del formulario

    let table = document.createElement("table"); //Creación de la tabla
    table.className = "table"; //Clase de la tabla

    //Creación de la fila de títulos
    let titles = ["Clase", "Tamaño", "Piezas con consignación", "Pedido"];
    let tr = document.createElement("tr");
    titles.forEach((title) => {
        let th = document.createElement("th");
        th.textContent = title;
        th.className = "t-title";
        tr.appendChild(th);
    });
    table.appendChild(tr);
    fragment.appendChild(table);

    //Creación de la fila de títulos
    classes.forEach((classArray) => {
        //Se recorren las clases
        let button = document.createElement("button");
        button.value = classArray["id"];
        button.className = "btnClass";

        for (let field in classArray) {
            //Se recorren los campos de cada clase
            switch (
            field //Switch para insertar los campos correspondientes en la tabla
            ) {
                case "nombre":
                case "tamanio":
                case "seccion":
                case "piezas":
                case "pedido":
                    if (classArray[field] != null) {
                        let div_td = document.createElement("div");
                        div_td.className = "div-td td-" + field;
                        div_td.textContent = classArray[field];
                        button.appendChild(div_td);
                    }
                    break;
            }
        }

        //Agregar evento al boton
        button.addEventListener("click", function () {
            event.preventDefault();

            //Estilos de los botones de accion de la clase
            setOrDelete_ClassButtons(button.value, false);

            //Estilos de los botones de la tabla
            let buttons = document.querySelectorAll(".btnClass");
            buttons.forEach((buttonOne) => {
                buttonOne.classList.remove("swo-btn-selected"); buttonOne.classList.add("swo-btn-unselected");
            });
            button.classList.remove("swo-btn-unselected"); button.classList.add("swo-btn-selected");

            //Obtener el valor del boton y mostrar la información de la clase seleccionada
            setClassInfo(classes, button.value);
            let checkbox = document.querySelector(".checkbox-add-class");
            if (checkbox) {
                if (checkbox.checked == true) {
                    checkbox.checked = false;
                }
            }

            createOperationsCheckBox(classArray["nombre"], window.processes[button.value], false); //Crear las casillas de los procesos
            //Mostrar el formulario de la clase junto con sus procesos
            showformHidden(true);
        });

        fragment.appendChild(button);
    });
    return fragment;
}

function setOrDelete_ClassButtons(idClass, action) {
    let btn_addClass = document.querySelector(".btn-addClass"); //Obtener el boton de agregar clase
    let containerCheckbox = document.querySelector(".container-checkbox");

    //Eliminar el boton de eliminar clase si ya existe uno
    if (document.querySelector(".btn-deleteClass") != null) {
        document.querySelector(".btn-deleteClass").remove();
        document.querySelector(".btn-editClass").remove();
    }
    if (document.getElementById("btn-saveClass") != null) {
        document.getElementById("btn-saveClass").remove();
    }

    //Crear el boton de eliminar clase dirigiendolo a la ruta correspondiente con el id de la clase que se desea eliminar
    if (!action) {
        if (containerCheckbox) {
            containerCheckbox.hidden = false;
            containerCheckbox.classList.remove("hidden");
        }
        if (idClass !== null) {
            let div_btns = document.querySelector(".div-btns"); //Obtener el div en donde se insertaran los botones de accion de la clase
            //Ocultar el boton de agregar clase
            let btn_addClass = document.querySelector(".btn-addClass");
            if (btn_addClass) {
                btn_addClass.hidden = true;
                btn_addClass.classList.add("hidden");
            }

            //Creacion del boton de eliminar clase
            createButtons(idClass).forEach((button) => {
                if (window.profile == 5 && button.innerHTML == "Eliminar Clase") {
                    button.hidden = true;
                    button.classList.add("hidden");
                }
                div_btns.appendChild(button);
            });
        } else {
            //Ocultar el boton de agregar clase
            let btn_addClass = document.querySelector(".btn-addClass");
            if (btn_addClass) {
                btn_addClass.hidden = true;
                btn_addClass.classList.add("hidden");
            }
        }
    } else if (action == "edit") {
        if (containerCheckbox) {
            containerCheckbox.hidden = true;
            containerCheckbox.classList.add("hidden");
        }
        //Ocultar el boton de agregar clase
        let btn_addClass = document.querySelector(".btn-addClass");
        if (btn_addClass) {
            btn_addClass.hidden = true;
            btn_addClass.classList.add("hidden");
        }

        //Creacion del boton de editar clase
        let btn_saveClassEdition = document.createElement("button");
        btn_saveClassEdition.className = "btn-editClass action-btns";
        btn_saveClassEdition.id = "btn-saveClass";
        btn_saveClassEdition.innerHTML = "Guardar";
        btn_saveClassEdition.setAttribute("form", "form");
        let div_btns = document.querySelector(".div-btns"); //Obtener el div en donde se insertaran los botones de accion de la clase
        div_btns.appendChild(btn_saveClassEdition);
    } else {
        if (containerCheckbox) {
            containerCheckbox.hidden = false;
            containerCheckbox.classList.remove("hidden");
        }
        //Mostrar el boton de agregar clase
        let btn_addClass = document.querySelector(".btn-addClass");
        if (btn_addClass) {
            btn_addClass.hidden = false;
            btn_addClass.classList.remove("hidden");
        }
    }
}

function createButtons(idClass) {
    //Creacion del boton eliminar
    let btn_deleteClass = document.createElement("button");
    btn_deleteClass.innerHTML = "Eliminar Clase";
    btn_deleteClass.className = "btn-deleteClass action-btns";
    btn_deleteClass.addEventListener("click", function () {
        event.preventDefault();
        let container_form = document.querySelector(".container-form");
        container_form.appendChild(mostrarDiv(`../destroyClass/${idClass}`));
    });

    //Creacion del boton editar
    let btn_editClass = document.createElement("button");
    btn_editClass.className = "btn-editClass action-btns";
    btn_editClass.innerHTML = "Editar Clase";
    btn_editClass.addEventListener("click", function () {
        event.preventDefault();
        enableEditClass(idClass);
    });

    return [btn_deleteClass, btn_editClass];
}

function enableEditClass(idClass) {
    setOrDelete_ClassButtons(idClass, "edit");

    let div_rowsHidden = document.querySelector(".div-rows-hidden");
    div_rowsHidden.innerHTML = "";
    for (let classObject in window.classes) {
        if (window.classes[classObject].id == idClass) {
            div_rowsHidden.appendChild(
                createRowsForm(
                    get_inputAttributes(window.workOrder.id, window.molding.nombre, window.classes[classObject])[1]
                )
            );
            break;
        }
    }

    let className = document.querySelector(".classes").value;
    createOperationsCheckBox(className, window.processes[idClass], true); //Creación de las casillas de los procesos
    toggleWeldingTypeVisibility(className);
}

function setClassInfo(classesObject = null, classSelected) {
    // Asegurar que el id de clase del formulario oculto está limpio en vista de solo lectura
    let inputClassId = document.getElementById("idClass");
    if (inputClassId) {
        inputClassId.value = "";
        inputClassId.removeAttribute("value");
    }

    //Obtener el div en donde se insertaran los inputs con el valor de la clase seleccionada y eliminar los inputs anteriores
    let parentDiv = document.querySelector(".div-rows-hidden");
    parentDiv.innerHTML = "";

    for (let classObject in classesObject) {
        if (classesObject[classObject].id == classSelected) {
            let formInputs = {
                classType: {
                    label: "Clase",
                    input: {
                        type: "text",
                        value: classesObject[classObject].nombre,
                        disabled: true,
                    },
                },
                size: {
                    label: "Tamaño",
                    input: {
                        type: "text",
                        value: classesObject[classObject].tamanio,
                        disabled: true,
                    },
                },
                composicionQuimica: {
                    label: "Composición Química",
                    fullWidth: true,
                    isTags: true,
                    value: classesObject[classObject].composicion_quimica ?? "-",
                    tipoSoldadura: classesObject[classObject].tipo_soldadura ?? null,
                },
                order: {
                    label: "Pedido Total",
                    input: {
                        type: "number",
                        value: classesObject[classObject].pedido,
                        disabled: true,
                    },
                },
                pieces: {
                    label: "Piezas con consignación",
                    input: {
                        type: "number",
                        value: classesObject[classObject].piezas,
                        disabled: true,
                    },
                },
                startDate: {
                    label: "Fecha de inicio",
                    input: {
                        type: "date",
                        value: classesObject[classObject].fecha_inicio,
                        disabled: true,
                    },
                },
                startTime: {
                    label: "Hora de inicio",
                    input: {
                        type: "time",
                        value: classesObject[classObject].hora_inicio,
                        disabled: true,
                    },
                },
                finishDate: {
                    label: "Fecha de termino",
                    input: {
                        type: "date",
                        value:
                            classesObject[classObject].fecha_termino == null
                                ? ""
                                : classesObject[classObject].fecha_termino,
                        disabled: true,
                    },
                },
                finishTime: {
                    label: "Hora de termino",
                    input: {
                        type: "time",
                        value:
                            classesObject[classObject].hora_termino == null
                                ? ""
                                : classesObject[classObject].hora_termino,
                        disabled: true,
                    },
                },
            };
            parentDiv.appendChild(createRowsForm(formInputs));
            break;
        }
    }
}

function createCheckboxAddClass() {
    let div = document.createElement("div");
    div.className = "container-checkbox";

    let label = document.createElement("label");
    label.textContent = "¿Deseas agregar una clase?";
    label.id = "label-add-class";
    label.className = "label-add-class";

    let checkbox = document.createElement("input");
    checkbox.type = "checkbox";
    checkbox.className = "checkbox-add-class";

    //Añadir evento al checkbox
    checkbox.addEventListener("change", function () {
        if (checkbox.checked) {
            // Guardar la clase que se estaba visualizando para restaurarla si se desmarca el checkbox
            let activeBtn = null;
            let buttons = document.querySelectorAll(".btnClass");
            buttons.forEach((btn) => {
                if (btn.classList.contains("swo-btn-selected") || btn.style.backgroundColor === "rgb(3, 57, 102)" || btn.style.backgroundColor === "#033966") {
                    activeBtn = btn;
                }
            });
            if (activeBtn) {
                window.selectedClassId = activeBtn.value;
            } else {
                window.selectedClassId = null;
            }

            //Estilos de los botones de la tabla
            buttons.forEach((button) => {
                button.classList.remove("swo-btn-selected"); button.classList.add("swo-btn-unselected");
            });

            setOrDelete_ClassButtons(null, true);

            let div_rowsHidden = document.querySelector(".div-rows-hidden");
            div_rowsHidden.innerHTML = "";
            div_rowsHidden.appendChild(
                createRowsForm(get_inputAttributes(window.workOrder.id, window.molding.nombre)[1])
            );

            let className = document.querySelector(".classes").value;
            createOperationsCheckBox(className, null, true); //Creación de las casillas de los procesos
            toggleWeldingTypeVisibility(className);
        } else {
            // Si desmarca, restaurar la clase seleccionada si existía
            if (window.selectedClassId) {
                let targetButton = document.querySelector(`.btnClass[value="${window.selectedClassId}"]`);
                if (targetButton) {
                    targetButton.click(); // Vuelve a cargar y mostrar la clase seleccionada
                    return;
                }
            }
            setOrDelete_ClassButtons(null, false);
        }
        showformHidden(checkbox.checked);
    });

    div.appendChild(label);
    div.appendChild(checkbox);
    return div;
}

function showformHidden(value) {
    let div_rowsHidden = document.querySelector(".div-rows-hidden");
    let div_boxes = document.querySelector(".div-boxes");
    if (div_boxes) {
        div_boxes.hidden = !value;
        div_boxes.classList.toggle("hidden", !value);
    }
    if (div_rowsHidden) {
        div_rowsHidden.hidden = !value;
        div_rowsHidden.classList.toggle("hidden", !value);
    }
}

function createOperationsCheckBox(className, markedProcesses, edit) {
    //Obtener el div en donde se insertaran las casillas de los procesos
    let sections = document.querySelector(".sections");
    sections.innerHTML = "";
    //Obtener los titulos de los checkbox y su name atraves de arrays
    let operations = get_operationsArray(className);
    let operationsArray = operations[1];
    operations = operations[0];

    crearCasillas(operations, operationsArray, markedProcesses, edit);
}

function get_operationsArray(className) {
    let operations = [];
    let operationsArray = [];
    switch (className) {
        case "Bombillo":
        case "Molde":
            operations = [
                "Cepillado",
                "Desbaste exterior",
                "Revision Laterales",
                "1ra Operación",
                "Barreno maniobra",
                "2da Operación",
                "Soldadura",
                "Soldadura PTA",
                "Rectificado",
                "Asentado",
                "Calificado",
                "Acabado " + className,
                "Barreno profundidad",
                "Cavidades",
                "Copiado",
                "Offset",
                "Palomas",
                "Rebajes",
                "Grabado",
            ];
            operationsArray = [
                "cepillado",
                "desbaste_exterior",
                "revision_laterales",
                "pOperacion",
                "barreno_maniobra",
                "sOperacion",
                "soldadura",
                "soldaduraPTA",
                "rectificado",
                "asentado",
                "calificado",
                "acabado" + className,
                "barreno_profundidad",
                "cavidades",
                "copiado",
                "offSet",
                "palomas",
                "rebajes",
                "grabado",
            ];
            break;
        case "Obturador":
        case "Fondo":
            operations = ["1ra y 2da Operación Equipo", "Soldadura", "Soldadura PTA"];
            operationsArray = ["operacionEquipo", "soldadura", "soldaduraPTA"];
            break;
        case "Corona":
            operations = ["Cepillado", "Desbaste exterior", "1ra Operacion", "2da Operacion", "Soldadura", "Soldadura PTA", "Rectificado", "Asentado", "Calificado"];
            operationsArray = ["cepillado", "desbaste_exterior", "pOperacion", "sOperacion", "soldadura", "soldaduraPTA", "rectificado", "asentado", "calificado"];
            break;
        case "Plato":
            operations = ["Barreno Maniobra", "1ra y 2da Operación Equipo"];
            operationsArray = ["barreno_maniobra", "operacionEquipo"];
            break;
        case "Embudo":
            operations = ["1ra y 2da Operación Equipo", "Embudo C.M."];
            operationsArray = ["operacionEquipo", "embudoCM"];
            break;
        case "Cabeza de Soplo":
            operations = ["Primera Operacion", "Segunda Operacion"];
            operationsArray = ["primeraOperacionCabezaSoplo", "segundaOperacionCabezaSoplo"];
            break;
        case "Candado Obturador":
            operations = ["1ra y 2da Operación Equipo"];
            operationsArray = ["operacionEquipo"];
            break;
    }
    return [operations, operationsArray];
}

function crearCasillas(operations, operationsArray, markedProcesses, edit) {
    let sections = document.querySelector(".sections"); //Obtener el div de las secciones

    //Secciones de las casillas
    let section1 = document.createElement("div");
    section1.className = "section1";
    let section2 = document.createElement("div");
    section2.className = "section2";

    for (let i = 0; i < operations.length; i++) {
        //For para la creación de cada una de las casillas
        let div = createProcessBox(operations[i], i + 1, operationsArray[i], markedProcesses, edit);
        //Agregar a las secciones correspondientes
        if (i < parseInt(operations.length / 2)) {
            section1.appendChild(div);
        } else {
            section2.appendChild(div);
        }
    }
    //Inserción de las secciones en el div de las casillas
    sections.appendChild(section1);
    sections.appendChild(section2);
    //Si no se esta editando la clase, se deshabilita el checkbox de seleccionar todo
    if (window.profile != 5) {
        createCheckboxAll(edit);
    }

    // changeStatusSoldaduras(); //Agregar eventos a los checkbox de soldaduras
}

function createProcessBox(operation, processIndex, operationName, markedProcesses, edit) {
    //Creación de un div que sera el contenedor de los elementos del proceso correspondiente
    let div = document.createElement("div");
    div.className = "checkbox-container";

    //Creación de un label para cada checkbox
    let label = document.createElement("label");
    label.className = "checkbox-label";
    label.innerHTML = operation;

    //Creación de un input en donde se insertara el numero de maquinas a utilizar en el proceso correspondiente
    let labelMachine = document.createElement("label");
    labelMachine.textContent = "Máquinas: ";
    labelMachine.classList.add("class", "label-machine");

    let machineInput = document.createElement("input");
    machineInput.type = "number";
    machineInput.name = "machines[]";
    machineInput.className = "input-machine";
    machineInput.id = `process-${processIndex}`;

    //Creación de un checkbox del proceso correspondiente
    let checkbox = document.createElement("input");
    checkbox.type = "checkbox";
    checkbox.name = "operations[]";
    checkbox.value = operationName;
    checkbox.className = "checkbox";

    //Algoritmo para el desmarcado y deshabilitado de los checkbox y los inputs de las maquinas
    let elements = automateCheckbox(checkbox, machineInput, operationName, markedProcesses, edit);
    checkbox = elements[0];
    machineInput = elements[1];

    //Inserción de los elementos en el div contenedor
    div.appendChild(labelMachine);
    div.appendChild(machineInput);
    div.appendChild(checkbox);
    div.appendChild(label);

    return div;
}

function createCheckboxAll(value) {
    //Eliminar el checkbox de seleccionar todo si ya existe uno
    let div = document.querySelector(".div-checkboxAll");
    if (div != null) {
        div.remove();
    }

    //Crear el checkbox de seleccionar todo
    if (value) {
        let div_boxes = document.querySelector(".div-boxes");

        let div = document.createElement("div");
        div.className = "div-checkboxAll";

        let label = document.createElement("label");
        label.className = "checkbox-label";
        label.id = "all-label";
        label.innerHTML = "Seleccionar todo";

        let checkbox = document.createElement("input");
        checkbox.type = "checkbox";
        checkbox.className = "checkboxAll";
        if (document.getElementById("btn-saveClass") == null) {
            checkbox.checked = true;
        }

        checkbox.addEventListener("change", function () {
            let checkboxes = document.querySelectorAll(".checkbox");
            let machineInputs = document.querySelectorAll(".input-machine");
            if (this.checked) {
                machineInputs.forEach((input) => {
                    input.disabled = false;
                    input.classList.remove("swo-input-disabled"); input.classList.add("swo-input-enabled");
                });
            } else {
                machineInputs.forEach((input) => {
                    input.disabled = true;
                    input.classList.remove("swo-input-enabled"); input.classList.add("swo-input-disabled");
                    input.value = "";
                });
            }
            checkboxes.forEach((checkbox) => {
                if (checkbox.checked != this.checked) {
                    checkbox.checked = this.checked;
                }
            });
        });
        div.appendChild(checkbox);
        div.appendChild(label);
        div_boxes.appendChild(div);
    }
}

function automateCheckbox(checkbox, machineInput, operationName, markedProcesses, edit) {
    checkbox.checked = true;
    machineInput.required = true;
    // //Si el proceso es de soldadura, se muestra desmarcado el checkbox y el input se deshabilita
    // if (operationName == "soldadura" || operationName == "soldaduraPTA") {
    //     checkbox.className = "checkbox-soldaduras";
    //     machineInput.className = "input-machine-soldaduras";
    //     checkbox.checked = false;
    //     machineInput.disabled = true;
    // }

    if (markedProcesses !== null) {
        //Si el proceso ya ha sido seleccionado anteriormente en la clase, se muestra marcado el checkbox y se muestran las maquinas en el input
        checkbox.checked = false;
        machineInput.disabled = true;
        if (markedProcesses !== undefined) {
            if (markedProcesses[operationName] != undefined) {
                checkbox.checked = true;
                machineInput.value = markedProcesses[operationName];
                if (edit) {
                    machineInput.disabled = false;
                }
            }
        }
    }
    if (!edit) {
        //Si no se esta editando la clase, se deshabilita todo (Unicamente se muestran)
        checkbox.disabled = true;
        machineInput.disabled = true;
    }

    //Si se ingresa a la interfaz con el perfil de almacen deshabilitar las casillas de los procesos
    if (window.profile == 5) {
        machineInput.disabled = true;
        checkbox.disabled = true;
        if (markedProcesses == null) {
            // Si el proceso no ha sido seleccionado anteriormente en la clase
            checkbox.checked = false;
        }
    }
    //Agregar eventos a los checkbox
    checkbox.addEventListener("change", function () {
        changeStatusCheckbox(checkbox, machineInput);
    });

    //Agregar los estilos correspondientes a los inputs de las maquinas
    if (machineInput.disabled) {
        machineInput.classList.remove("swo-input-enabled"); machineInput.classList.add("swo-input-disabled");
    }
    return [checkbox, machineInput];
}

function changeStatusCheckbox(checkbox, machineInput) {
    if (checkbox.checked) {
        //Si el checkbox se marca
        machineInput.disabled = false;
        machineInput.classList.remove("swo-input-disabled"); machineInput.classList.add("swo-input-enabled");
    } else {
        //Si el checkbox se desmarca
        machineInput.disabled = true;
        machineInput.classList.remove("swo-input-enabled"); machineInput.classList.add("swo-input-disabled");
        machineInput.value = "";
    }
}

function changeStatusSoldaduras() {
    let checkboxes = document.querySelectorAll(".checkbox-soldaduras");
    let machineInput = document.querySelectorAll(".input-machine-soldaduras");
    // Agregar un evento de cambio a cada checkbox
    checkboxes.forEach((checkbox, index) => {
        checkbox.addEventListener("change", function () {
            // Deshabilitar el input-maq correspondiente según el estado de la checkbox
            machineInput[index].disabled = !checkbox.checked;
            // Desmarcar el otro checkbox cuando uno se selecciona
            checkboxes.forEach((otherCheckbox, otherIndex) => {
                if (otherCheckbox !== checkbox) {
                    otherCheckbox.checked = false;
                    // Deshabilitar el input-maq correspondiente si la checkbox no está marcada
                    machineInput[otherIndex].disabled = !otherCheckbox.checked;
                }
            });
            machineInput.forEach((input) => {
                if (input.disabled) {
                    input.classList.remove("swo-input-enabled"); input.classList.add("swo-input-disabled");
                    input.value = "";
                } else {
                    input.classList.remove("swo-input-disabled"); input.classList.add("swo-input-enabled");
                }
            });
        });
    });
}

function mostrarDiv(route) {
    let div_padre = document.createElement("div");
    div_padre.className = "div-opacity";
    div_padre.id = "div-opacity";

    let div = document.createElement("div");
    div.className = "div-delete";

    let label = document.createElement("label");
    label.className = "label-delete";
    label.innerHTML = route.includes("Class")
        ? "¿Estás seguro de eliminar la clase?"
        : "¿Estás seguro de eliminar la orden de trabajo?";

    let image = document.createElement("img");
    image.className = "img-delete";
    image.src = window.deleteImgUrl;

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

    let a = document.createElement("a");
    a.className = "btn-deleteClass action-btns";
    a.href = route;
    a.innerHTML = "Eliminar";

    div.appendChild(div_cerrar);
    div.appendChild(image);
    div.appendChild(label);
    div.appendChild(a);
    div_padre.appendChild(div);
    return div_padre;
}

function cerrarDiv() {
    let div_padre = document.getElementById("div-opacity");
    div_padre.remove();
}

function modificarSelect() {
    let secciones = document.getElementById("secciones"); //Obtener el div de las casillas
    secciones.innerHTML = ""; //Eliminar las casillas
    crearCheckbox(clase.value, 0, 0, false); //Crear los checkbox de acuerdo a la clase
}

function createChemicalCompositionChips(attributesArray) {
    let wrapper = document.createElement("div");
    wrapper.className = "chemical-composition-wrapper";

    let grid = document.createElement("div");
    grid.className = "chemical-composition-grid";

    let options = attributesArray.options;
    let currentValue = attributesArray.currentValue ?? null;

    // Parsear currentValue respetando grupos con '/':
    // Si un grupo A/B tiene algún elemento que NO es opción predefinida,
    // el grupo completo se trata como composición personalizada (ej: BRONCE/ZINC).
    // Si todos los elementos del grupo son opciones predefinidas, se marcan como chips individuales.
    let activeCompositions = [];  // elementos predefinidos (chips a marcar)
    let customGroups = [];        // grupos personalizados (campo "otro")

    if (currentValue) {
        if (Array.isArray(currentValue)) {
            activeCompositions = currentValue;
        } else {
            // Separar primero por coma (distintas composiciones)
            let commaGroups = currentValue.split(/\s*,\s*/).map(s => s.trim()).filter(Boolean);
            commaGroups.forEach((group) => {
                // Cada grupo puede tener '/' (mezcla)
                let parts = group.split(/\s*\/\s*/).map(s => s.trim()).filter(Boolean);
                let allPredefined = parts.every(p => options.includes(p));
                if (allPredefined) {
                    // Todos son chips predefinidos → marcarlos individualmente
                    parts.forEach(p => activeCompositions.push(p));
                } else {
                    // Hay al menos un elemento no predefinido → mantener el grupo unido
                    customGroups.push(group);
                }
            });
        }
    }

    options.forEach((optionValue) => {
        let label = document.createElement("label");
        label.className = "composition-option";

        let checkbox = document.createElement("input");
        checkbox.type = "checkbox";
        checkbox.name = "composicion_quimica[]";
        checkbox.value = optionValue;
        checkbox.className = "chemical-composition-input";

        if (window.profile == 5) {
            checkbox.disabled = true;
        }

        let isChecked = activeCompositions.includes(optionValue);
        if (isChecked) {
            checkbox.checked = true;
        }

        let chip = document.createElement("span");
        chip.className = "chemical-composition-chip";
        chip.textContent = optionValue;

        label.appendChild(checkbox);
        label.appendChild(chip);
        grid.appendChild(label);
    });

    wrapper.appendChild(grid);

    // Construir el valor del campo "otro" con los grupos personalizados
    let customValue = customGroups.join(", ");

    // Crear el campo "otro" + selector de Tipo de Soldadura en la misma fila
    let otroContainer = document.createElement("div");
    otroContainer.className = "otro-composition-container";
    otroContainer.classList.add("swo-otro-container");

    let otroLabel = document.createElement("label");
    otroLabel.textContent = "Otro (Especificar composición):";
    otroLabel.classList.add("swo-otro-label");
    otroLabel.hidden = false;

    // Fila que contiene el input "otro" y el selector de tipo de soldadura lado a lado
    let otroInputRow = document.createElement("div");
    otroInputRow.hidden = false;
    otroInputRow.classList.add("swo-otro-row");

    let otroInput = document.createElement("input");
    otroInput.type = "text";
    otroInput.name = "composicion_quimica_otro";
    otroInput.className = "form-control";
    otroInput.classList.add("swo-otro-input");
    otroInput.placeholder = "Separar por comas (ej: COBRE, BRONCE) o con / para mezclas (ej: HG/MINOX)";
    otroInput.value = customValue ? normalizeChemicalInput(customValue) : customValue;

    if (window.profile == 5) {
        otroInput.disabled = true;
    }

    // Normalización en tiempo real: mayúsculas + reglas de / y , (solo admin=1 y master=3)
    if (window.profile == 1 || window.profile == 3) {
        otroInput.addEventListener("input", function () {
            let cursorPos = this.selectionStart;
            let original = this.value;
            let normalized = normalizeChemicalInput(original);
            if (normalized !== original) {
                this.value = normalized;
                let diff = normalized.length - original.length;
                this.setSelectionRange(cursorPos + diff, cursorPos + diff);
            }
        });
    }

    // Selector de Tipo de Soldadura (al lado del input "otro")
    let soldaduraWrapper = document.createElement("div");
    soldaduraWrapper.id = "welding-type-wrapper";
    soldaduraWrapper.hidden = false;
    soldaduraWrapper.classList.add("swo-sol-wrapper");

    let soldaduraLabel = document.createElement("label");
    soldaduraLabel.textContent = "Tipo de Soldadura:";
    soldaduraLabel.classList.add("swo-sol-label");

    let soldaduraSelect = document.createElement("select");
    soldaduraSelect.name = "tipo_soldadura";
    soldaduraSelect.className = "form-control";
    soldaduraSelect.classList.add("swo-sol-select");

    let defaultOption = document.createElement("option");
    defaultOption.value = "";
    defaultOption.text = "-- Seleccionar --";
    soldaduraSelect.add(defaultOption);

    const tiposSoldadura = [
        { value: "1", label: "P1 - 3" },
        { value: "2", label: "P2 - 2.5" },
        { value: "3", label: "P3 - 2" },
        { value: "4", label: "P4 - 1.5" },
    ];
    tiposSoldadura.forEach(({ value, label }) => {
        let opt = document.createElement("option");
        opt.value = value;
        opt.text = label;
        if (attributesArray.tipoSoldadura && String(attributesArray.tipoSoldadura) === value) {
            opt.selected = true;
        }
        soldaduraSelect.add(opt);
    });

    if (window.profile == 5) {
        soldaduraSelect.disabled = true;
    }

    soldaduraWrapper.appendChild(soldaduraLabel);
    soldaduraWrapper.appendChild(soldaduraSelect);

    otroInputRow.appendChild(otroInput);
    otroInputRow.appendChild(soldaduraWrapper);

    otroContainer.appendChild(otroLabel);
    otroContainer.appendChild(otroInputRow);
    wrapper.appendChild(otroContainer);

    return wrapper;
}

function createChemicalCompositionTags(valueString, tipoSoldadura) {
    let container = document.createElement("div");
    container.className = "chemical-composition-tags-container";

    if (!valueString || valueString === "-") {
        let noData = document.createElement("span");
        noData.textContent = "-";
        noData.classList.add("swo-no-data");
        container.appendChild(noData);
    } else {
        let activeCompositions = valueString.split(/\s*\/\s*/);
        activeCompositions.forEach((tagText) => {
            if (!tagText.trim()) return;
            let tag = document.createElement("span");
            tag.className = "chemical-composition-tag";
            tag.textContent = tagText.trim();
            container.appendChild(tag);
        });
    }

    // Separador siempre visible
    let sep = document.createElement("span");
    sep.classList.add("swo-sep");
    sep.textContent = "│";
    container.appendChild(sep);

    let soldaduraWrapper = document.createElement("span");
    soldaduraWrapper.hidden = false;
    soldaduraWrapper.classList.add("swo-sol-wrapper-sm");

    let soldaduraLabelText = document.createElement("span");
    soldaduraLabelText.classList.add("swo-sol-label-sm");
    soldaduraLabelText.textContent = "Tipo de Soldadura:";

    let soldaduraBadge = document.createElement("span");
    soldaduraBadge.className = "chemical-composition-tag";

    const tiposSoldaduraMap = { "1": "P1 - 3", "2": "P2 - 2.5", "3": "P3 - 2", "4": "P4 - 1.5" };
    if (tipoSoldadura) {
        // Badge azul oscuro con el tipo registrado
        soldaduraBadge.classList.add("swo-badge-active");
        soldaduraBadge.textContent = tiposSoldaduraMap[String(tipoSoldadura)] ?? ("Tipo " + tipoSoldadura);
    } else {
        // Badge gris indicando que no hay información
        soldaduraBadge.classList.add("swo-badge-inactive");
        soldaduraBadge.textContent = "Sin información";
    }

    soldaduraWrapper.appendChild(soldaduraLabelText);
    soldaduraWrapper.appendChild(soldaduraBadge);
    container.appendChild(soldaduraWrapper);

    return container;
}

/**
 * Normaliza el texto de composición química:
 * - Convierte todo a mayúsculas
 * - Si hay '/', elimina espacios alrededor (juntar): "HG / MINOX" → "HG/MINOX"
 * - Si hay ',', separa con ", " (separar): "hg,minox" → "HG, MINOX"
 */
function normalizeChemicalInput(value) {
    if (!value) return value;
    // Convertir a mayúsculas
    let result = value.toUpperCase();
    // Normalizar '/': quitar espacios alrededor (juntar elementos)
    result = result.replace(/\s*\/\s*/g, "/");
    // Normalizar ',': asegurar un espacio después de la coma (separar)
    result = result.replace(/\s*,\s*/g, ", ");
    return result;
}

/**
 * Muestra u oculta el selector de tipo de soldadura según la clase seleccionada.
 * Las clases que aplican son: Molde, Fondo, Bombillo, Obturador.
 */
function toggleWeldingTypeVisibility(className) {
    const weldingClasses = ["Molde", "Fondo", "Bombillo", "Obturador"];
    let wrapper = document.getElementById("welding-type-wrapper");
    if (!wrapper) return;

    let shouldShow = weldingClasses.includes(className);
    if (shouldShow) {
        wrapper.hidden = false;
    } else {
        wrapper.hidden = true;
        // Limpiar el selector para no enviar datos residuales
        let select = wrapper.querySelector('select[name="tipo_soldadura"]');
        if (select) {
            select.value = "";
        }
    }
}

// ── Lógica de Polling (Sincronización en tiempo real) ──
if (window.classesDataUrl && window.workOrder && window.workOrder.id) {
    setInterval(async () => {
        try {
            const res = await fetch(`${window.classesDataUrl}/${window.workOrder.id}`);
            if (!res.ok) return;
            const data = await res.json();
            
            if (data && Array.isArray(data) && window.classes) {
                data.forEach(updatedClass => {
                    // Actualizar el array en memoria
                    const classInMem = window.classes.find(c => c.id == updatedClass.id);
                    if (classInMem) {
                        const currentPedido = parseInt(classInMem.pedido);
                        const currentPiezas = parseInt(classInMem.piezas);
                        
                        if (currentPedido !== updatedClass.pedido || currentPiezas !== updatedClass.piezas) {
                            classInMem.pedido = updatedClass.pedido;
                            classInMem.piezas = updatedClass.piezas;
                            
                            // Actualizar visualmente la tabla
                            const btn = document.querySelector(`.btnClass[value="${updatedClass.id}"]`);
                            if (btn) {
                                const tdPedido = btn.querySelector('.td-pedido');
                                const tdPiezas = btn.querySelector('.td-piezas');
                                if (tdPedido) tdPedido.textContent = updatedClass.pedido;
                                if (tdPiezas) tdPiezas.textContent = updatedClass.piezas;
                            }
                            
                            // Actualizar los inputs si esta clase es la que está abierta actualmente y NO estamos editando
                            const idClassInput = document.getElementById('idClass');
                            if (idClassInput && idClassInput.value == updatedClass.id) {
                                const isEditing = document.getElementById('btn-saveClass') != null;
                                if (!isEditing) {
                                    const inputOrder = document.querySelector('input[name="order"]');
                                    const inputPieces = document.querySelector('input[name="pieces"]');
                                    if (inputOrder) inputOrder.value = updatedClass.pedido;
                                    if (inputPieces) inputPieces.value = updatedClass.piezas;
                                }
                            }
                        }
                    }
                });
            }
        } catch (e) {
            // Error silencioso de red
        }
    }, 15000);
}
