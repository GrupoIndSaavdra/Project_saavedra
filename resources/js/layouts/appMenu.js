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
                        ["productionData", "Datos de produccion"],
                        ["cNominals", "Editar C.Nominales y Tolerancias"],
                        ["machinesOccupied", "Maquinas ocupadas"],
                        // ["showTimes", "Modificar tiempos de producción"],
                        ["show_panelWO", "Panel de progreso de O.T"],
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
                    routes: [["manageWO", "Registrar o Modificar O.T"]],
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
window.pieces_Released = !Array.isArray(window.pieces_Released) ? Object.values(window.pieces_Released) : window.pieces_Released;

if (window.pieces_Released && window.pieces_Released.length > 0) {
    const divOpacity = document.createElement("div");
    divOpacity.classList.add("filter-opacity-home");

    let operacion = false;
    // Crear elementos
    const divTable = document.createElement("div");
    divTable.className = "div-table-pieces-released";
    const table = document.createElement("table");
    table.id = "table";
    table.classList.add("table-pieces-released");

    const thead = document.createElement("thead");
    const tr = document.createElement("tr");

    // Agregar encabezados base
    const headers = ["Clase", "Orden de trabajo", "N_juego", "Nombre del operador", "Máquina", "Proceso"];

    // Agregar columnas base
    headers.forEach((header, i) => {
        const th = document.createElement("th");
        th.textContent = header;
        // Aplicar estilos a columnas específicas
        if (header === "Nombre del operador" || header === "Proceso") {
            th.style.width = "500px";
        }
        tr.appendChild(th);
    });

    // Agregar columnas finales
    const moreHeaders = [
        { name: "Errores", width: "300px" },
        "Fecha de Maquinado",
        "Fecha de Liberación",
        "Liberado/Rechazado por",
        "Observaciones",
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

var operacion = false;

function crearTabla(piezas, infoPiezas) {
    //Crea la tabla de piezas trabajadas en la O.T
    // console.log(piezas);
    const table = document.getElementById("table");;
    const tbody = document.createElement("tbody");
    //Convertir el objeto a un array
    piezas = convertirObjectToArray(piezas);
    for (let i = 0; i < piezas.length; i++) {
        const tr = document.createElement("tr");
        for (let j = 0; j < piezas[i].length + 1; j++) {
            if (j >= 13) {
                break;
            }
            let td = document.createElement("td");
            switch (j) {
                case 6:
                    // td.textContent = crearFecha(piezas[i][j]);
                    td.textContent = piezas[i][j];
                    break;
                case 9:
                    td.textContent = piezas[i][13];
                    td.style.width = "600px";
                    break;
                case 10:
                    if (!piezas[i][5].includes("Incompleto") && piezas[i][9] != 1) {
                        td.appendChild(crearBotonLiberar(infoPiezas, i, piezas));
                        tr.appendChild(td);
                    }
                    break;
                case 11:
                    if (piezas[i][9] != 2) {
                        td.appendChild(crearBotonRechazar(infoPiezas, i));
                    }
                    break;
                case 12:
                    td.appendChild(crearBotonVer(infoPiezas, i, piezas[i][2]));
                    break;
                default:
                    if (piezas[i][j] != undefined) {
                        if (j == 0) {
                            td.textContent = piezas[i][j];
                            let tdClass = document.createElement("td");
                            tdClass.textContent = piezas[i][12];
                            tr.appendChild(tdClass);
                        } else {
                            td.textContent = piezas[i][j];
                        }
                    } else {
                        td.textContent = "";
                    }
                    break;
            }
            tr.appendChild(td);
            switch (piezas[i][9]) {
                case 0:
                    if (piezas[i][5].includes("Incompleto")) {
                        tr.style.backgroundColor = "#FFFF99";
                    } else if (piezas[i][5] == "Ninguno") {
                        tr.style.backgroundColor = "#ACF980A8";
                    } else {
                        tr.style.backgroundColor = "#E59CFF";
                    }
                    break;
                case 1:
                    tr.style.backgroundColor = "#79BFED";
                    break;
                case 2:
                    tr.style.backgroundColor = "#EC7063";
                    break;
            }
        }
        tbody.appendChild(tr);
    }
    table.appendChild(tbody);
}

function convertirObjectToArray(obj) {
    // console.log(obj);
    let array = [];
    for (let i = 0; i < obj.length; i++) {
        array.push(Object.values(obj[i]));
    }
    return array;
}

function crearFecha(fecha) {
    console.log(fecha);
    let cadena = "";
    if (fecha != "No liberado") {
        let array = fecha.split("T");
        console.log(array);
        cadena = array[0] + "\n " + array[1].slice(0, 8);
        console.log(cadena);
    } else {
        cadena = fecha;
    }
    return cadena;
}

function crearBotonLiberar(infoPiezas, i, piezas) {
    const a = document.createElement("a");
    a.className = "btn-liberar";

    let bool;
    if (infoPiezas[i][2] == "Ninguno" && piezas[i][piezas[i].length - 2] != 2) {
        bool = true;
    } else {
        bool = false;
    }
    let url = `${window.baseUrl}/piezasLiberar/${infoPiezas[i][0]}/${infoPiezas[i][1]}/${true}/${bool}/${null}`;
    a.href = url;

    const image = document.createElement("img");
    image.src = window.liberar;
    image.alt = "Liberar";
    image.className = "ver";
    a.appendChild(image);
    return a;
}
function crearBotonRechazar(infoPiezas, i) {
    const a = document.createElement("a");
    a.className = "btn-liberar";
    let url = `${window.baseUrl}/piezasLiberar/${infoPiezas[i][0]}/${infoPiezas[i][1]}/${false}/${false}/${null}`;
    a.href = url;

    const image = document.createElement("img");
    image.src = window.rechazar;
    image.alt = "Rechazar";
    image.className = "ver";
    a.appendChild(image);
    return a;
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