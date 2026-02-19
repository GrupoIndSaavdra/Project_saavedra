//Ejecución de la función para la creación del formulario de la clase
createForm(); //Creación del formulario de la clase

function createForm() {
    let div_rows = document.querySelector(".div-rows"); //Obtención del div en donde se insertará el formulario
    div_rows.appendChild(createRowsForm(get_inputAttributes(window.workOrder.id, window.molding.nombre)[0])); //Creación del formulario de la clase
    let div_rowsHidden = document.createElement("div");
    div_rowsHidden.className = "div-rows-hidden";
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
            options: ["Bombillo", "Molde", "Obturador", "Fondo", "Corona", "Plato", "Embudo", "Cabeza de Soplo"],
        },
        size: {
            label: "Seleccione el tamaño",
            select: {
                name: "size",
                class: "selects",
            },
            options: tamanios,
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
    inputClassId.removeAttribute("value");
    if (value != null) {
        //Modificacion de valor del id de la clase en el input de tipo hidden
        inputClassId.setAttribute("value", value.id);

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

    //Obtención del número de filas que se deben de crear
    const numberRows = Number.isInteger(Object.keys(formInputs).length / 2)
        ? Object.keys(formInputs).length / 2
        : Math.ceil(Object.keys(formInputs).length / 2);

    let inputsCounter = 0; //Contador para los inputs que se van insertando en el formulario
    for (let i = 0; i < numberRows; i++) {
        //For para la creacion del div "row" para cada par de inputs
        let nameInput = Object.keys(formInputs)[inputsCounter]; //Obtención del nombre del input
        if (nameInput != "table") {
            let row = document.createElement("div");
            row.className = "row";
            for (let j = 0; j < 2; j++) {
                //For para la creacion de los div "col" para cada input
                nameInput = Object.keys(formInputs)[inputsCounter]; //Obtención del nombre del input
                let col = document.createElement("div");
                col.className = "column";

                //Creación del label correspondiente
                if (formInputs[nameInput].label != undefined) {
                    let label = document.createElement("label");
                    label.textContent = formInputs[nameInput].label;
                    label.className = "label-form";
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
        } else {
            fragment.appendChild(createScrollableTable(window.classes));
            //Inserción de los elementos correspondientes al checkbox de agregar más clases
            insertWOButtons(fragment);
        }
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
        for (let i = 0; i < options.length; i++) {
            let option = document.createElement("option");
            option.value = options[i];
            option.text = options[i];
            htmlTag.add(option);
        }
        if (nameInput == "classType") {
            //Si el select es el de tipo de clase, se añade un evento para modificar el select
            htmlTag.addEventListener("change", () => {
                modifySelect(htmlTag.value);
                createOperationsCheckBox(htmlTag.value, null, true);
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
                        div_td.className = "div-td";
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
                buttonOne.style.backgroundColor = "white";
                buttonOne.style.color = "#000000";
            });
            button.style.backgroundColor = "#033966";
            button.style.color = "#ffffff";

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
        if (idClass !== null) {
            let div_btns = document.querySelector(".div-btns"); //Obtener el div en donde se insertaran los botones de accion de la clase
            //Ocultar el boton de agregar clase
            let btn_addClass = document.querySelector(".btn-addClass");
            btn_addClass.style.display = "none";

            //Creacion del boton de eliminar clase
            createButtons(idClass).forEach((button) => {
                if (window.profile == 5 && button.innerHTML == "Eliminar Clase") {
                    button.style.display = "none";
                }
                div_btns.appendChild(button);
            });
        } else {
            //Ocultar el boton de agregar clase
            btn_addClass.style.display = "none";
        }
    } else if (action == "edit") {
        //Ocultar el boton de agregar clase
        btn_addClass.style.display = "none";

        //Creacion del boton de editar clase
        let btn_saveClassEdition = document.createElement("button");
        btn_saveClassEdition.className = "btn-editClass action-btns";
        btn_saveClassEdition.id = "btn-saveClass";
        btn_saveClassEdition.innerHTML = "Guardar";
        btn_saveClassEdition.setAttribute("form", "form");
        let div_btns = document.querySelector(".div-btns"); //Obtener el div en donde se insertaran los botones de accion de la clase
        div_btns.appendChild(btn_saveClassEdition);
    } else {
        //Mostrar el boton de agregar clase
        btn_addClass.style.display = "block";
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
}

function setClassInfo(classesObject = null, classSelected) {
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
        //Estilos de los botones de la tabla
        let buttons = document.querySelectorAll(".btnClass");
        buttons.forEach((button) => {
            button.style.backgroundColor = "white";
        });

        if (checkbox.checked) {
            setOrDelete_ClassButtons(null, true);

            let div_rowsHidden = document.querySelector(".div-rows-hidden");
            div_rowsHidden.innerHTML = "";
            div_rowsHidden.appendChild(
                createRowsForm(get_inputAttributes(window.workOrder.id, window.molding.nombre)[1])
            );

            let className = document.querySelector(".classes").value;
            createOperationsCheckBox(className, null, true); //Creación de las casillas de los procesos
        } else {
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
    if (value) {
        div_boxes.style.display = "flex";
        div_rowsHidden.style.display = "block";
    } else {
        div_boxes.style.display = "none";
        div_rowsHidden.style.display = "none";
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
            operations = ["Cepillado", "Desbaste exterior", "1ra Operacion", "2da Operacion", "Soldadura", "Soldadura PTA", "Rectificado", "Asentado", "Calificado", "Acabado Bombillo"];
            operationsArray = ["Cepillado", "Desbaste exterior", "Primera Operacion", "Segunda Operacion", "Soldadura", "Soldadura PTA", "Rectificado", "Asentado", "Calificado", "Acabado Bombillo"];
            break;
        case "Plato":
            operations = ["1ra y 2da Operación Equipo", "Embudo C.M."];
            operationsArray = ["operacionEquipo", "embudoCM"];
            break;
        case "Embudo":
            operations = ["1ra y 2da Operación Equipo", "Embudo C.M."];
            operationsArray = ["operacionEquipo", "embudoCM"];
            break;
        case "Cabeza de Soplo":
            operations = ["Primera Operacion", "Segunda Operacion"];
            operationsArray = ["primeraOperacionCabezaSoplo", "segundaOperacionCabezaSoplo"];
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
                    input.style.backgroundColor = "white";
                    input.style.border = "1px solid #000000";
                });
            } else {
                machineInputs.forEach((input) => {
                    input.disabled = true;
                    input.style.backgroundColor = "#ced4da";
                    input.style.border = "none";
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
        machineInput.style.backgroundColor = "#ced4da";
        machineInput.style.border = "none";
    }
    return [checkbox, machineInput];
}

function changeStatusCheckbox(checkbox, machineInput) {
    if (checkbox.checked) {
        //Si el checkbox se marca
        machineInput.disabled = false;
        machineInput.style.backgroundColor = "white";
        machineInput.style.border = "1px solid #000000";
    } else {
        //Si el checkbox se desmarca
        machineInput.disabled = true;
        machineInput.style.backgroundColor = "#ced4da";
        machineInput.style.border = "none";
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
                    input.style.backgroundColor = "#ced4da";
                    input.style.border = "none";
                    input.value = "";
                } else {
                    input.style.backgroundColor = "white";
                    input.style.border = "1px solid #000000";
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
