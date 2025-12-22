var operacion = false;

function crearTabla(piezas, infoPiezas) {
    //Crea la tabla de piezas trabajadas en la O.T
    console.log(piezas);
    const table = document.querySelector(".table");
    const tbody = document.createElement("tbody");
    //Convertir el objeto a un array
    piezas.forEach((pieza, counter) => {
        const tr = document.createElement("tr");
        pieza = orderedArray(pieza);
        //Insertar valores
        Object.keys(pieza).forEach((key) => {
            if (key != "colorPiece") {
                const td = document.createElement("td");
                switch (key) {
                    case "btn_release":
                        if (!pieza[key][1].includes("Incompleto") && pieza[key][0] != 1) {
                            td.appendChild(crearBotonLiberar(infoPiezas, counter, piezas));
                        }
                        break;
                    case "btn_decline":
                        if (pieza[key] != 2) {
                            td.appendChild(crearBotonRechazar(infoPiezas, counter));
                        }
                        break;
                    case "btn_seePiece":
                        td.appendChild(crearBotonVer(infoPiezas, counter, pieza[key]));
                        break;
                    default:
                        td.textContent = pieza[key];
                        if(key == "operator" || key == "observations" || key == "observacion_liberacion"){
                            td.style.width = "600px";
                        }
                        break;
                }
                tr.appendChild(td);
            } else {
                tr.style.backgroundColor = pieza[key];
            }
        });
        tbody.appendChild(tr);
    });
    table.appendChild(tbody);
}
function asignColorTr(status, error) {
    switch (status) {
        case 0:
            if (error.includes("Incompleto")) {
                return "#FFFF99";
            } else if (error == "Ninguno") {
                return "#ACF980A8";
            } else {
                return "#E59CFF";
            }
        case 1:
            return "#79BFED";
        case 2:
            return "#EC7063";
    }
}
function orderedArray(array) {
    return {
        class: array["className"],
        workOrder: array[0],
        noAssembly: array[1],
        operator: array[2],
        machine: array[3],
        process: array[4],
        errors: array[5],
        observations: array.observations,
        machinedDate: array[6],
        liberationDate: array[7],
        user_liberation: array[8],
        observacion_liberacion: array.observacion_liberacion,
        btn_release: [array[9], array[5]],
        btn_decline: array[9],
        btn_seePiece: array[2],
        colorPiece: asignColorTr(array[9], array[5]),
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

function crearBotonLiberar(infoPiezas, i, piezas) {
    const a = document.createElement("a");
    a.className = "btn-liberar";

    let bool;
    if (infoPiezas[i][2] == "Ninguno" && piezas[i][9] != 2) {
        bool = true;
    } else {
        bool = false;
    }

    //Agregar imagen al boton de liberar
    const image = document.createElement("img");
    image.src = window.liberar;
    image.alt = "Liberar";
    image.className = "ver";
    a.appendChild(image);

    let keys = {
        pieza: infoPiezas[i][0],
        proceso: infoPiezas[i][1],
        liberar: true,
        buena: bool,
    };
    // Agregar evento al boton
    a.addEventListener("click", (e) => {
        e.preventDefault;
        create_ObservationsField(keys);
    });
    return a;
}
function crearBotonRechazar(infoPiezas, i) {
    const a = document.createElement("a");
    a.className = "btn-liberar";

    //Agregar imagen al boton de rechazar
    const image = document.createElement("img");
    image.src = window.rechazar;
    image.alt = "Rechazar";
    image.className = "ver";
    a.appendChild(image);

    let keys = {
        pieza: infoPiezas[i][0],
        proceso: infoPiezas[i][1],
        liberar: false,
        buena: false,
    };

    // Agregar evento al boton
    a.addEventListener("click", (e) => {
        e.preventDefault;
        create_ObservationsField(keys);
    });
    return a;
}
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
function crearBotonVer(infoPiezas, i, usuarios) {
    const a = document.createElement("a");
    a.className = "btn-pza";

    let nPiezas = [];
    for (let j = 0; j < infoPiezas[i][0].length; j++) {
        nPiezas.push(infoPiezas[i][0][j]);
    }
    //INFORMACIÓN DE LAS PIEZAS O PIEZA
    let url = `${window.baseUrl}/pieces/${nPiezas}/${infoPiezas[i][1]}/quality`;
    a.href = url;

    const image = document.createElement("img");
    image.src = window.ojito;
    image.alt = "Ver";
    image.className = "ver";
    a.appendChild(image);
    return a;
}
function obtenerRequest() {
    let names = ["workOrder", "class", "operator", "machine", "process", "error", "dateFrom", "dateTo"];
    let request = [];
    for (let i = 0; i < names.length; i++) {
        let value = document.getElementsByName(names[i])[0].value.replaceAll("/", "_");
        request.push(value);
    }
    return request;
}
function createFilters() {
    let titles = ["Orden de trabajo", "Clase", "Operador", "Maquina", "Proceso", "Error", "Desde", "Hasta"];
    Object.keys(window.selectedItems).forEach((item, index) => {
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
                if (item != "action") {
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

                    window.filtersData[item].forEach((key) => {
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
                    div.appendChild(select);
                }
                break;
        }
        //Agregar label
        if (item != "action") {
            let label = document.createElement("label");
            label.textContent = titles[index] + ": ";
            div.appendChild(label);
            document.querySelector(".filters").appendChild(div);
        }
    });
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
if (window.pieces.length > 0) {
    crearTabla(window.pieces, window.infoPieces);
}
const pdf = document.getElementById("pdf");
