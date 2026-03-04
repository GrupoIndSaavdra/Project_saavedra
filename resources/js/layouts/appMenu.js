import "../layouts/partials/messages.js";

//Dar funcionalidad al bonton del menu
let btn_open = document.querySelector(".open-menu"); //Obtenemos el elemento por su id.
let nav = document.querySelector(".filter-opacity"); //Obtenemos el elemento por su id.

// Crear la lista de rutas
let profile = document.getElementById("profile").value;
createMenu(profile);
//Agregamos un evento al botón de abrir
btn_open.addEventListener("click", function () {
    //Agregamos un evento al botón de abrir
    if (nav.style.visibility == "hidden" || nav.style.visibility == "") {
        nav.style.visibility = "visible"; //Cambiamos la visibilidad del nav.
        nav.style.opacity = "1"; //Cambiamos la opacidad del nav.
    } else {
        nav.style.visibility = "hidden"; //Cambiamos la visibilidad del nav.
        nav.style.opacity = "0"; //Cambiamos la opacidad del nav.
    }
    nav.style.transition = "0.5s ease"; //Agregamos una transición al nav.
    nav.style.translationX = "0%"; //Agregamos una transición al nav.
});

//Funcion para crear La lista de rutas para el menu
function createMenu(profile) {
    if (profile == 2) {
        let btn_open = document.querySelector(".open-menu"); //Obtenemos el elemento por su id.
        btn_open.style.opacity = "0"; //Cambiamos la opacidad del nav.
        btn_open.style.pointerEvents = "none"; //Cambiamos la opacidad del nav.
    } else {
        let routes = getRoutes(profile);
        let ul = document.querySelector(".nav-list");
        ul.appendChild(createList(routes));
    }
}

function createList(sections) {
    const fragment = document.createDocumentFragment();
    const currentPath = window.location.pathname;

    sections.forEach((section) => {
        // Sección con título (submenú)
        if (section.title) {
            const liSection = document.createElement("li");
            liSection.classList.add("menu-section");

            const toggle = document.createElement("a");
            toggle.href = "#";
            toggle.classList.add("submenu-toggle");
            toggle.textContent = section.title;

            const ulSubmenu = document.createElement("ul");
            ulSubmenu.classList.add("submenu");

            section.routes.forEach((route) => {
                const li = document.createElement("li");
                const a = document.createElement("a");
                a.classList.add("nav-link");
                a.href = window.routes[route[0]];
                a.textContent = route[1];

                a.addEventListener("click", (e) => {
                    e.preventDefault();
                    //Aparecer div opacity
                    let div_opacity = document.createElement("div");
                    div_opacity.classList.add("div-opacity");

                    let div_loading = document.createElement("div");
                    div_loading.classList.add("loading");

                    let img_loading = document.createElement("img");
                    img_loading.classList.add("img-loading");
                    img_loading.src = window.loading;
                    img_loading.alt = "Cargando...";
                    div_loading.appendChild(img_loading);

                    div_opacity.appendChild(div_loading);
                    document.body.appendChild(div_opacity);

                    window.location.href = a.href;
                });

                const linkPath = new URL(a.href, window.location.origin).pathname;
                if (currentPath === linkPath) {
                    a.classList.add("active");
                    liSection.classList.add("active"); // para mostrar sección activa
                    ulSubmenu.style.display = "block";
                }

                li.appendChild(a);
                ulSubmenu.appendChild(li);
            });

            liSection.appendChild(toggle);
            liSection.appendChild(ulSubmenu);
            fragment.appendChild(liSection);
        }
        // Sección sin título (rutas individuales)
        else {
            section.routes.forEach((route) => {
                const li = document.createElement("li");
                const a = document.createElement("a");
                a.classList.add("nav-link");
                a.href = window.routes[route[0]];
                a.textContent = route[1];

                a.addEventListener("click", (e) => {
                    e.preventDefault();
                    //Aparecer div opacity
                    let div_opacity = document.createElement("div");
                    div_opacity.classList.add("div-opacity");

                    let div_loading = document.createElement("div");
                    div_loading.classList.add("loading");

                    let img_loading = document.createElement("img");
                    img_loading.classList.add("img-loading");
                    img_loading.src = window.loading;
                    img_loading.alt = "Cargando...";
                    div_loading.appendChild(img_loading);

                    div_opacity.appendChild(div_loading);
                    document.body.appendChild(div_opacity);

                    window.location.href = a.href;
                });

                const linkPath = new URL(a.href, window.location.origin).pathname;
                if (currentPath === linkPath) {
                    a.classList.add("active");
                }

                li.appendChild(a);
                fragment.appendChild(li);
            });
        }
    });

    return fragment;
}

function getRoutes(profile) {
    const routeHome = ["home", "Inicio"];
    let sections = [];

    switch (profile) {
        case "1":
            sections = [
                {
                    title: null,
                    routes: [routeHome],
                },
                {
                    title: "Molduras",
                    routes: [
                        ["createMolding", "Crear nueva moldura"],
                        ["editMolding", "Editar moldura"],
                    ],
                },
                {
                    title: "Orden de Trabajo",
                    routes: [
                        ["manageWO", "Crear o Modificar O.T"],
                        ["piecesInProgress", "Piezas en progreso"],
                        ["showPiecesReport_view", "Reporte de piezas"],
                        ["showReleasePieces_view", "Liberacion de piezas"],
                    ],
                },
                {
                    title: "Usuarios",
                    routes: [
                        // ['users', 'Ver usuarios'],
                        // ["createUser", "Registrar usuario"],
                        ["recoverPassword", "Recuperar contraseña"],
                    ],
                },
                {
                    title: "Producción",
                    routes: [
                        ["productionData", "Datos de productividad"],
                        ["cNominals", "Editar C.Nominales y Tolerancias"],
                        ["machinesOccupied", "Maquinas ocupadas"],
                        // ["showTimes", "Modificar tiempos de producción"],
                        ["show_panelWO", "Panel de progreso de O.T"],
                    ],
                },
                {
                    title: "Soldadura PTA",
                    routes: [
                        ["pta.analysis", "Análisis de Resultados Sold. PTA"],
                    ],
                },
            ];
            break;
        case "2":
            sections = [
                {
                    title: null,
                    routes: [routeHome],
                },
                {
                    title: null,
                    routes: [["processProduction", "Proceso de Producción"]],
                },
            ];
            break;
        case "3":
            sections = [
                {
                    title: null,
                    routes: [routeHome, ["createUser", "Registrar usuario"]],
                },
                {
                    title: "Usuarios",
                    routes: [["createUser", "Registrar usuario"]],
                }
            ];
            break;
        case "4":
            sections = [
                {
                    title: null,
                    routes: [routeHome],
                },
                {
                    title: "Liberación de Piezas",
                    routes: [["showReleasePieces_view", "Liberacion de piezas"]],
                },
                {
                    title: "Producción",
                    routes: [
                        ["piecesInProgress", "Piezas en progreso"],
                        ["cNominals", "Editar C.Nominales y Tolerancias"],
                        // ["showTimes", "Modificar tiempos de producción"],
                    ],
                },
            ];
            break;
        case "5":
            sections = [
                {
                    title: null,
                    routes: [routeHome],
                },
                {
                    title: "Orden de Trabajo",
                    routes: [
                        ["manageWO", "Modificar O.T"],
                        ["piecesInProgress", "Piezas en progreso"],
                    ],
                },
                {
                    title: "Soldadura",
                    routes: [
                        ["soldadura.generarQRLote", "Generar QR por Lote"],
                        ["soldadura.generarQRIndividual", "Generar QRs Botes"],
                        ["soldadura.recepcionPlanta", "Registrar entrada de Soldadura"],
                        ["soldadura.liberarQRPlanta", "Entrega de Soldadura a Planta"],
                        ["soldadura.regenerarQR", "Regenerar QRs"]
                    ],
                },
            ];
            break;
        case "pta_temp":
            sections = [
                {
                    title: "Opciones PTA",
                    routes: [
                        ["pta.results.current", "Resultados de Sold. PTA"],
                        ["pta.analysis", "Análisis de Resultados Sold. PTA"],
                    ],
                },
            ];
            break;
        default:
            sections = [
                {
                    title: null,
                    routes: [routeHome],
                },
            ];
            break;
    }
    return sections;
}

document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll(".submenu-toggle").forEach((toggle) => {
        toggle.addEventListener("click", function (e) {
            e.preventDefault();
            if (this.parentElement.classList.contains("active")) {
                this.parentElement.classList.remove("active");
                this.nextElementSibling.style.display = "none";
            } else {
                this.parentElement.classList.add("active");
                this.nextElementSibling.style.display = "block";
            }
        });
    });
});

//Si window.pieces_released es de tipo Object convertirlo a Array
if (window.pieces_Released) {
    window.pieces_Released = !Array.isArray(window.pieces_Released)
        ? Object.values(window.pieces_Released)
        : window.pieces_Released;

    if (window.pieces_Released && window.pieces_Released.length > 0) {
        const divOpacity = document.createElement("div");
        divOpacity.classList.add("filter-opacity-home");

        // Crear elementos
        const divTable = document.createElement("div");
        divTable.className = "div-table-pieces-released";
        const table = document.createElement("table");
        table.id = "table";
        table.classList.add("table-pieces-released");

        const thead = document.createElement("thead");
        const tr = document.createElement("tr");

        // Agregar encabezados base
        const headers = ["Clase", "Orden de trabajo", "Juego", "Nombre del operador", "Máquina", "Proceso"];

        // Agregar columnas base
        headers.forEach((header, i) => {
            const th = document.createElement("th");
            th.textContent = header;
            // Aplicar estilos a columnas específicas
            if (header === "Orden de trabajo") {
                th.style.width = "200px";
            }
            tr.appendChild(th);
        });

        // Agregar columnas finales
        const moreHeaders = [
            { name: "Errores", width: "300px" },
            "Observaciones",
            "Fecha de Maquinado",
            "Liberar",
            "Rechazar",
            "Ver",
        ];

        moreHeaders.forEach((header) => {
            const th = document.createElement("th");
            if (typeof header === "object") {
                th.textContent = header.name;
                th.style.width = header.width;
            } else {
                th.textContent = header;
            }
            tr.appendChild(th);
        });

        // Estructurar tabla
        thead.appendChild(tr);
        table.appendChild(thead);
        divTable.appendChild(table);

        // Finalmente, agregar a tu documento
        divOpacity.appendChild(divTable); // O donde tú necesites insertarlo
        document.body.appendChild(divOpacity);
        crearTabla(window.pieces_Released, window.info_Pieces);
    }
}
function crearTabla(piezas, infoPiezas) {
    //Crea la tabla de piezas trabajadas en la O.T
    console.log(piezas);
    const table = document.getElementById("table");
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
                        if (key == "operator" || key == "observations" || key == "observacion_liberacion") {
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
        btn_release: [array[9], array[5]],
        btn_decline: array[9],
        btn_seePiece: array[2],
        colorPiece: asignColorTr(array[9], array[5]),
    };
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
    submit.classList.add("btn-submit", "btn-liberation");
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

    let input = document.createElement("input");
    input.type = "hidden";
    input.name = "requestLiberation";
    input.value = "yes";
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
