import "../layouts/partials/messages.js";

//Dar funcionalidad al bonton del menu
let btn_open = document.querySelector(".open-menu"); //Obtenemos el elemento por su id.
let nav = document.querySelector(".filter-opacity"); //Obtenemos el elemento por su id.

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

// Crear la lista de rutas
let profile = document.getElementById("profile").value;
createMenu(profile);

//Funcion para crear La lista de rutas para el menu
function createMenu(profile) {
    let routes = getRoutes(profile);
    let ul = document.querySelector(".nav-list");
    ul.appendChild(createList(routes));
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

                const linkPath = new URL(a.href, window.location.origin)
                    .pathname;
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
                        ["createUser", "Registrar usuario"],
                        ["recoverPassword", "Recuperar contraseña"],
                    ],
                },
                {
                    title: "Producción",
                    routes: [
                        ["productionData", "Datos de produccion"],
                        ["cNominals", "Editar C.Nominales y Tolerancias"],
                        ["showTimes", "Modificar tiempos de producción"],
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
                    routes: [
                        ["processProduction", "Proceso de Producción"],

                        ["cepillado", "Cepillado"],
                        ["desbasteExterior", "Desbaste exterior"],
                        ["revisionLaterales", "Revisión de laterales"],
                        ["primeraOpeSoldadura", "1ra Operación Soldadura"],
                        ["barrenoManiobra", "Barreno de maniobra"],
                        ["segundaOpeSoldadura", "2da Operación Soldadura"],
                        ["soldadura", "Soldadura"],
                        ["soldaduraPTA", "Soldadura PTA"],
                        ["rectificado", "Reporte diario de rectificado"],
                        ["asentado", "Asentado"],
                        ["calificado", "Calificado"],
                        ["acabadoBombillo", "Revisión acabados bombillo"],
                        ["acabadoMolde", "Revisión acabados molde"],
                        ["barrenoProfundidad", "Barreno de profundidad"],
                        ["cavidades", "Reporte diario de Cavidades"],
                        ["copiado", "Reporte de Copiado"],
                        ["offSet", "Ranura Off-Set"],
                        ["palomas", "Reporte de Palomas"],
                        ["rebajes", "Rebajes"],
                        ["operacionEquipo", "1ra y 2da Operación Equipo"],
                        ["embudoCM", "Embudo C.M"],
                    ],
                },
            ];
            break;
        case "3":
            sections = [
                {
                    title: null,
                    routes: [routeHome],
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
                    routes: [
                        ["showReleasePieces_view", "Liberacion de piezas"],
                    ],
                },
                {
                    title: "Producción",
                    routes: [
                        ["cNominals", "Editar C.Nominales y Tolerancias"],
                        ["showTimes", "Modificar tiempos de producción"],
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
            if(this.parentElement.classList.contains("active")) {
                this.parentElement.classList.remove("active");
                this.nextElementSibling.style.display = "none";
            } else {
                this.parentElement.classList.add("active");
                this.nextElementSibling.style.display = "block";
            }
        });
    });
});
