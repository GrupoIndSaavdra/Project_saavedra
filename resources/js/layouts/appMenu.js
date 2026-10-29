import "../layouts/partials/messages.js";
import "../components/cursorPagination.js";

function initAppMenu() {
    let btn_open = document.querySelector(".open-menu");
    let nav = document.querySelector(".filter-opacity");
    let profileEl = document.getElementById("profile");

    if (!profileEl) return;

    let profile = profileEl.value;
    createMenu(profile);

    let closeTimer = null;

    if (btn_open && nav) {
        let headerEl = btn_open.closest("header");
        btn_open.addEventListener("click", function () {
            if (nav.classList.contains("is-open")) {
                nav.classList.remove("is-open");
                btn_open.classList.remove("is-open");
                if (headerEl) headerEl.classList.remove("menu-is-open");
                btn_open.setAttribute("aria-expanded", "false");
                nav.style.opacity = "0";
                if (closeTimer) clearTimeout(closeTimer);
                closeTimer = setTimeout(() => {
                    if (!nav.classList.contains("is-open")) {
                        nav.style.visibility = "hidden";
                    }
                }, 220);
            } else {
                if (closeTimer) clearTimeout(closeTimer);
                nav.classList.add("is-open");
                btn_open.classList.add("is-open");
                if (headerEl) headerEl.classList.add("menu-is-open");
                btn_open.setAttribute("aria-expanded", "true");
                nav.style.visibility = "visible";
                nav.style.opacity = "1";

                const activeLink = nav.querySelector("a.nav-link.active");
                if (activeLink) {
                    setTimeout(() => {
                        activeLink.scrollIntoView({ block: "center", behavior: "smooth" });
                    }, 50);
                }
            }
        });

        nav.addEventListener("click", function (e) {
            if (e.target === nav) {
                nav.classList.remove("is-open");
                btn_open.classList.remove("is-open");
                if (headerEl) headerEl.classList.remove("menu-is-open");
                btn_open.setAttribute("aria-expanded", "false");
                nav.style.opacity = "0";
                if (closeTimer) clearTimeout(closeTimer);
                closeTimer = setTimeout(() => {
                    if (!nav.classList.contains("is-open")) {
                        nav.style.visibility = "hidden";
                    }
                }, 220);
            }
        });
    }
}

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initAppMenu);
} else {
    initAppMenu();
}

function createMenu(profile) {
    let btn_open = document.querySelector(".open-menu");
    if (profile == 2) {
        if (btn_open) {
            btn_open.style.opacity = "0";
            btn_open.style.pointerEvents = "none";
        }
    } else {
        if (btn_open) {
            btn_open.style.opacity = "1";
            btn_open.style.pointerEvents = "auto";
        }
        let routes = getRoutes(profile);
        let ul = document.querySelector(".nav-list");
        if (!ul) return;
        ul.innerHTML = "";
        ul.appendChild(createList(routes, false, []));

        const currentPath = window.location.pathname;
        const currentSearch = window.location.search;
        const allLinks = Array.from(ul.querySelectorAll("a.nav-link"));
        let targetLink = null;

        // Detección de sección preferida explícita por parámetros en URL
        let urlPreferredSection = null;
        if (currentSearch.includes('almacen_only=1') || currentSearch.includes('sec=almacen')) {
            urlPreferredSection = "Almacén";
        } else if (currentSearch.includes('sec=calidad')) {
            urlPreferredSection = "Calidad";
        } else if (currentSearch.includes('admin_only=1') || currentSearch.includes('sec=admin')) {
            urlPreferredSection = "Administración";
        }

        const savedPath = sessionStorage.getItem("active_menu_path");
        const savedHref = sessionStorage.getItem("active_menu_href");
        const savedTopSection = sessionStorage.getItem("active_top_section");
        const preferredTopSection = urlPreferredSection || savedTopSection;

        // 1. Prioridad Máxima: Si el usuario hizo clic en una opción específica del menú y coincide con la vista actual
        if (savedPath && savedHref) {
            const isMatch = isSameOrSubRoute(currentPath, savedHref);
            if (isMatch) {
                const candidate = allLinks.find(a => a.dataset.menuPath === savedPath);
                if (candidate) {
                    if (!urlPreferredSection || candidate.dataset.topSection === urlPreferredSection) {
                        targetLink = candidate;
                    }
                }
            }
        }

        // 2. Coincidencia dentro de la sección preferida (por query param o guardada)
        if (!targetLink && preferredTopSection) {
            const prefLinks = allLinks.filter(a => a.dataset.topSection === preferredTopSection);

            // 2.a. Coincidencia exacta de pathname y search
            targetLink = prefLinks.find(a => {
                const linkUrl = new URL(a.href, window.location.origin);
                return currentPath === linkUrl.pathname && isLinkMatching(a, currentPath);
            });

            // 2.b. Coincidencia flexible de pathname
            if (!targetLink) {
                targetLink = prefLinks.find(a => isLinkMatching(a, currentPath));
            }

            // 2.c. Coincidencia por sub-rutas dentro de la sección
            if (!targetLink) {
                targetLink = prefLinks.find(a => isPathMatching(currentPath, a.href));
            }
        }

        // 3. Coincidencia exacta de pathname y search en todo el menú
        if (!targetLink) {
            targetLink = allLinks.find(a => {
                const linkUrl = new URL(a.href, window.location.origin);
                return currentPath === linkUrl.pathname && isLinkMatching(a, currentPath);
            });
        }

        // 4. Coincidencia flexible en todo el menú
        if (!targetLink) {
            targetLink = allLinks.find(a => isLinkMatching(a, currentPath));
        }

        // 5. Coincidencia por sub-rutas en todo el menú
        if (!targetLink) {
            targetLink = allLinks.find(a => isPathMatching(currentPath, a.href));
        }

        // Expandir solo la rama del enlace activo seleccionado
        if (targetLink) {
            // Limpiar cualquier active previo para evitar duplicados
            allLinks.forEach(a => a.classList.remove("active"));
            ul.querySelectorAll(".menu-section.active").forEach(sec => sec.classList.remove("active"));

            targetLink.classList.add("active");
            let parentEl = targetLink.parentElement;
            while (parentEl && parentEl.tagName !== "NAV" && parentEl !== document.body) {
                if (parentEl.classList.contains("menu-section")) {
                    parentEl.classList.add("active");
                    const directSubmenu = Array.from(parentEl.children).find(c => c.classList && c.classList.contains("submenu"));
                    if (directSubmenu) {
                        directSubmenu.hidden = false;
                    }
                }
                parentEl = parentEl.parentElement;
            }

            // Sincronizar sessionStorage con la opción activa confirmada
            if (targetLink.dataset.menuPath) {
                sessionStorage.setItem("active_menu_path", targetLink.dataset.menuPath);
            }
            if (targetLink.dataset.topSection) {
                sessionStorage.setItem("active_top_section", targetLink.dataset.topSection);
            } else {
                sessionStorage.removeItem("active_top_section");
            }
            sessionStorage.setItem("active_menu_href", targetLink.href);
        }
    }
}

function isSameOrSubRoute(currentPath, savedHref) {
    if (!savedHref) return false;
    try {
        const savedUrl = new URL(savedHref, window.location.origin);
        if (savedUrl.pathname === currentPath) return true;
        return isPathMatching(currentPath, savedHref);
    } catch (e) {
        return false;
    }
}

function isLinkMatching(a, currentPath) {
    const linkUrl = new URL(a.href, window.location.origin);
    const linkSearch = linkUrl.search;

    const pathMatch = isPathMatching(currentPath, a.href);
    let searchMatch = false;
    if (linkSearch) {
        searchMatch = window.location.search.includes(linkSearch.substring(1));
    } else {
        searchMatch = window.location.search === '' || (
            !window.location.search.includes('admin_only=1') &&
            !window.location.search.includes('almacen_only=1') &&
            !window.location.search.includes('sec=almacen') &&
            !window.location.search.includes('sec=calidad')
        );
    }

    return pathMatch && searchMatch;
}

function isPathMatching(currentPath, aHref) {
    if (!aHref) return false;
    try {
        const linkUrl = new URL(aHref, window.location.origin);
        const linkPath = linkUrl.pathname;

        if (currentPath === linkPath) return true;

        // Sub-rutas derivadas o parametrizadas de Orden de Trabajo (ej: /showWO/5468 -> /master/createWO o /manageWO)
        if (currentPath.startsWith('/showWO') || currentPath.startsWith('/show_wo_almacen')) {
            if (window.location.search.includes('from_master=1')) {
                if (window.routes && window.routes.createMasterWO) {
                    const masterPath = new URL(window.routes.createMasterWO, window.location.origin).pathname;
                    if (linkPath === masterPath) return true;
                }
            } else if (window.routes && window.routes.manageWO) {
                const managePath = new URL(window.routes.manageWO, window.location.origin).pathname;
                if (linkPath === managePath) return true;
            }
        }

        // Sub-rutas de usuarios (ej: /users/1/edit -> /users)
        if (currentPath.startsWith('/users/') && !currentPath.includes('/create') && !currentPath.includes('/recoverPassword') && !currentPath.includes('/organigrama')) {
            if (window.routes && window.routes.users) {
                const usersPath = new URL(window.routes.users, window.location.origin).pathname;
                if (linkPath === usersPath) return true;
            }
        }

        // Sub-rutas de Salida de Molduras
        if (currentPath.startsWith('/calidad/r-interno-salidas/')) {
            if (window.routes && window.routes['calidad.r_interno_salidas.index']) {
                const salidaMoldurasPath = new URL(window.routes['calidad.r_interno_salidas.index'], window.location.origin).pathname;
                if (linkPath === salidaMoldurasPath) return true;
            }
        }

        // Sub-rutas de Inspección Visual
        if (currentPath.startsWith('/calidad/inspeccion-visual/')) {
            if (window.routes && window.routes['calidad.inspeccion_visual.index']) {
                const visualPath = new URL(window.routes['calidad.inspeccion_visual.index'], window.location.origin).pathname;
                if (linkPath === visualPath) return true;
            }
        }

        // Sub-rutas de Inspección Dimensional
        if (currentPath.startsWith('/calidad/inspeccion-dimensional/')) {
            if (window.routes && window.routes['calidad.inspeccion_dimensional.index']) {
                const dimensionalPath = new URL(window.routes['calidad.inspeccion_dimensional.index'], window.location.origin).pathname;
                if (linkPath === dimensionalPath) return true;
            }
        }

        // Sub-rutas de Soldadura Regenerar QR
        if (currentPath.startsWith('/soldadura/regenerar-qr')) {
            if (window.routes && window.routes['soldadura.regenerarQR']) {
                const regenPath = new URL(window.routes['soldadura.regenerarQR'], window.location.origin).pathname;
                if (linkPath === regenPath) return true;
            }
        }

        return false;
    } catch (e) {
        return false;
    }
}

function handleNavClick(a) {
    if (a.dataset.menuPath) {
        sessionStorage.setItem("active_menu_path", a.dataset.menuPath);
    }
    if (a.dataset.topSection) {
        sessionStorage.setItem("active_top_section", a.dataset.topSection);
    } else {
        sessionStorage.removeItem("active_top_section");
    }
    sessionStorage.setItem("active_menu_href", a.href);

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
}

function createList(sections, isNested = false, trail = []) {
    const fragment = document.createDocumentFragment();

    sections.forEach((section) => {
        // Sección con título (submenú)
        if (section.title) {
            const currentTrail = [...trail, section.title];
            const liSection = document.createElement("li");
            liSection.classList.add("menu-section");
            if (isNested) {
                liSection.classList.add("nested-menu-section");
            }

            const toggle = document.createElement("a");
            toggle.href = "#";
            toggle.classList.add("submenu-toggle");
            toggle.textContent = section.title;

            const ulSubmenu = document.createElement("ul");
            ulSubmenu.classList.add("submenu");

            // Si routes tiene sub-secciones (objetos) en lugar de arrays
            const firstRoute = section.routes[0];
            if (firstRoute && typeof firstRoute === "object" && !Array.isArray(firstRoute)) {
                ulSubmenu.appendChild(createList(section.routes, true, currentTrail));
            } else {
                section.routes.forEach((route) => {
                    const li = document.createElement("li");
                    const a = document.createElement("a");
                    a.classList.add("nav-link");
                    const targetRoute = (window.routes && window.routes[route[0]]) ? window.routes[route[0]] : ((window.baseUrl || '') + '/' + route[0]);
                    a.href = targetRoute;
                    if (route[2]) {
                        if (a.href.includes('?')) {
                            a.href += '&' + route[2];
                        } else {
                            a.href += '?' + route[2];
                        }
                    }
                    a.textContent = route[1];

                    const itemTrail = [...currentTrail, route[1]];
                    a.dataset.menuPath = itemTrail.join(" > ");
                    a.dataset.topSection = currentTrail[0] || "";

                    a.addEventListener("click", (e) => {
                        e.preventDefault();
                        handleNavClick(a);
                    });

                    li.appendChild(a);
                    ulSubmenu.appendChild(li);
                });
            }

            // Manejador del click para abrir/cerrar este submenú
            toggle.addEventListener("click", function (e) {
                e.preventDefault();
                e.stopPropagation();
                const parent = liSection;
                const isAlreadyActive = parent.classList.contains("active");

                // Cerrar hermanos del mismo nivel (para evitar desorden)
                const siblings = parent.parentElement.children;
                for (let sibling of siblings) {
                    if (sibling !== parent && sibling.classList.contains("menu-section")) {
                        sibling.classList.remove("active");
                        const submenu = sibling.querySelector(".submenu");
                        if (submenu) submenu.hidden = true;
                    }
                }

                // Alternar el actual
                if (isAlreadyActive) {
                    parent.classList.remove("active");
                    ulSubmenu.hidden = true;
                } else {
                    parent.classList.add("active");
                    ulSubmenu.hidden = false;
                }
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
                if (route[2]) {
                    if (a.href.includes('?')) {
                        a.href += '&' + route[2];
                    } else {
                        a.href += '?' + route[2];
                    }
                }
                a.textContent = route[1];

                a.dataset.menuPath = route[1];
                a.dataset.topSection = "";

                a.addEventListener("click", (e) => {
                    e.preventDefault();
                    handleNavClick(a);
                });

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

    const adminSubsections = [
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
                ["createMasterWO", "Crear o Modificar Orden de Trabajo (Master)"],
                ["manageWO", "Programación de O.T"],
                ["piecesInProgress", "Orden de Trabajo en Progreso"],
                ["priorityManager", "Prioridad de Órdenes de Trabajo"],
                ["showPiecesReport_view", "Reporte de piezas"],
                ["showReleasePieces_view", "Liberación de piezas"],
            ],
        },
        {
            title: "Documentación Técnica",
            routes: [
                ["ayudas_fundicion.manage", "Ayudas Visuales de Fundición"],
                ["ayudas.manage", "Ayudas Visuales de Maquinados"],
                ["fundicion.manage", "Dibujos de Fundición"],
                ["dibujos.manage", "Dibujos de Maquinados"],
                ["programas.manage", "Programas CNC"],
                ["manuales.manage", "Manuales de Procesos"],
            ],
        },
        {
            title: "Usuarios",
            routes: [
                ['users', 'Ver usuarios'],
                ['usersOrganigrama', 'Organigrama'],
                ["createUser", "Registrar usuario"],
                ["recoverPassword", "Recuperar contraseña"],
            ],
        },
        {
            title: "Producción",
            routes: [
                ["productionData", "Datos de productividad"],
                ["cNominals", "Editar C.Nominales y Tolerancias"],
                ["machinesOccupied", "Máquinas ocupadas"],
                ["piecesOccupied", "Piezas ocupadas"],
                ["show_panelWO", "Panel de progreso de O.T"],
                ["systemLogsReport", "Auditoría de Producción"],
                ["adminLogsReport", "Logs de Administradores"],
            ],
        },
        {
            title: "Soldadura PTA",
            routes: [
                ["pta.analysis", "Análisis de Resultados PTA"],
                ["pta.segunda_pasada", "Segunda Pasada PTA"],
            ],
        },
        {
            title: "Reportes",
            routes: [
                ["reportes.reenvio", "Reenviar Reporte Diario"],
                ["reportes.pta", "Envío de Reportes PTA"],
            ],
        },
        {
            title: "Herramientas",
            routes: [
                ["herramientas.tecamac.index", "Herramientas Planta Tecámac"],
            ],
        },
    ];

    switch (profile) {
        case "1":
            // Administración original (perfil 1 en BD): Administración completa + Almacén (Documentación Técnica y Herramientas)
            sections = [
                {
                    title: null,
                    routes: [routeHome],
                },
                {
                    title: "Prioridades",
                    routes: [
                        ["masterPriorities", "Prioridades GIS"],
                    ],
                },
                {
                    title: "Administración",
                    routes: adminSubsections,
                },
                {
                    title: "Almacén",
                    routes: [
                        {
                            title: "Documentación Técnica",
                            routes: [
                                ["almacen.fundicion.index", "Dibujos y Ayudas de Fundición"],
                            ],
                        },
                        {
                            title: "Herramientas",
                            routes: [
                                ["herramientas.tecamac.index", "Herramientas Planta Tecámac", "sec=almacen"],
                            ],
                        },
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
                {
                    title: "Herramientas",
                    routes: [
                        ["herramientas.tecamac.index", "Herramientas Planta Tecámac"],
                    ],
                },
            ];
            break;
        case "3":
            // Master (perfil 3 en BD): Administración + Calidad + Almacén
            sections = [
                {
                    title: null,
                    routes: [routeHome],
                },
                {
                    title: "Prioridades",
                    routes: [
                        ["masterPriorities", "Prioridades GIS"],
                        ["masterPrioritiesDisabled", "Prioridades Desactivadas"],
                    ],
                },
                {
                    title: "Administración",
                    routes: adminSubsections,
                },
                {
                    title: "Calidad",
                    routes: [
                        {
                            title: "Liberación de Piezas",
                            routes: [["showReleasePieces_view", "Liberación de piezas", "sec=calidad"]],
                        },
                        {
                            title: "Producción",
                            routes: [
                                ["piecesInProgress", "Orden de Trabajo en Progreso", "sec=calidad"],
                                ["cNominals", "Editar C.Nominales y Tolerancias", "sec=calidad"],
                            ],
                        },
                        {
                            title: "Documentación Técnica",
                            routes: [
                                ["calidad.fundicion.index", "Dibujos y Ayudas de Fundición"],
                                ["calidad.maquinados.index", "Dibujos y Ayudas de Maquinados"],
                            ],
                        },
                        {
                            title: "Reportes",
                            routes: [
                                ["calidad.r_interno_salidas.index", "Reporte de Inspección Visual"],
                                ["calidad.inspeccion_visual.index", "Reporte Volumétrico y Dimensional"],
                            ],
                        },
                    ],
                },
                {
                    title: "Almacén",
                    routes: [
                        {
                            title: "Orden de Trabajo",
                            routes: [
                                ["manageWO", "Modificar O.T", "almacen_only=1"],
                                ["piecesInProgress", "Orden de Trabajo en Progreso", "sec=almacen"],
                            ],
                        },
                        {
                            title: "Soldadura",
                            routes: [
                                ["soldadura.generarQRLote", "Generar QR por Lote"],
                                ["soldadura.generarQRIndividual", "Generar QRs Botes"],
                                ["soldadura.recepcionPlanta", "Registrar entrada de Soldadura"],
                                ["soldadura.liberarQRPlanta", "Entrega de Soldadura a Planta"],
                                ["soldadura.regenerarQR", "Regenerar QRs"],
                            ],
                        },
                        {
                            title: "Documentación Técnica",
                            routes: [
                                ["almacen.fundicion.index", "Dibujos y Ayudas de Fundición"],
                            ],
                        },
                        {
                            title: "Herramientas",
                            routes: [
                                ["herramientas.tecamac.index", "Herramientas Planta Tecámac", "sec=almacen"],
                            ],
                        },
                    ],
                },
            ];
            break;
        case "6":
            // Supervisor (perfil 6 en BD): Administración completa + Almacén (Orden de Trabajo y Soldadura)
            sections = [
                {
                    title: null,
                    routes: [routeHome],
                },
                {
                    title: "Prioridades",
                    routes: [
                        ["masterPriorities", "Prioridades GIS"],
                    ],
                },
                {
                    title: "Administración",
                    routes: adminSubsections,
                },
                {
                    title: "Almacén",
                    routes: [
                        {
                            title: "Orden de Trabajo",
                            routes: [
                                ["manageWO", "Modificar O.T", "almacen_only=1"],
                                ["piecesInProgress", "Orden de Trabajo en Progreso", "sec=almacen"],
                            ],
                        },
                        {
                            title: "Soldadura",
                            routes: [
                                ["soldadura.generarQRLote", "Generar QR por Lote"],
                                ["soldadura.generarQRIndividual", "Generar QRs Botes"],
                                ["soldadura.recepcionPlanta", "Registrar entrada de Soldadura"],
                                ["soldadura.liberarQRPlanta", "Entrega de Soldadura a Planta"],
                                ["soldadura.regenerarQR", "Regenerar QRs"],
                            ],
                        },
                    ],
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
                    routes: [["showReleasePieces_view", "Liberación de piezas"]],
                },
                {
                    title: "Producción",
                    routes: [
                        ["piecesInProgress", "Orden de Trabajo en Progreso"],
                        ["cNominals", "Editar C.Nominales y Tolerancias"],
                        // ["showTimes", "Modificar tiempos de producción"],
                    ],
                },
                {
                    title: "Documentación Técnica",
                    routes: [
                        ["calidad.fundicion.index", "Dibujos y Ayudas de Fundición"],
                        ["calidad.maquinados.index", "Dibujos y Ayudas de Maquinados"],
                    ],
                },
                {
                    title: "Reportes",
                    routes: [
                        ["calidad.r_interno_salidas.index", "Reporte de Inspección Visual"],
                        ["calidad.inspeccion_visual.index", "Reporte Volumétrico y Dimensional"],
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
                        ["piecesInProgress", "Orden de Trabajo en Progreso"],
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
                {
                    title: "Documentación Técnica",
                    routes: [
                        ["almacen.fundicion.index", "Dibujos y Ayudas de Fundición"],
                    ],
                },
                {
                    title: "Herramientas",
                    routes: [
                        ["herramientas.tecamac.index", "Herramientas Planta Tecámac"],
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
                        ["pta.segunda_pasada", "Segunda Pasada PTA"],
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


// Si window.pieces_Released es de tipo Object convertirlo a Array
if (window.pieces_Released) {
    window.pieces_Released = !Array.isArray(window.pieces_Released)
        ? Object.values(window.pieces_Released)
        : window.pieces_Released;

    if (window.pieces_Released && window.pieces_Released.length > 0) {
        const divOpacity = document.createElement("div");
        divOpacity.classList.add("filter-opacity-home");

        // Crear contenedor principal del modal
        const divTable = document.createElement("div");
        divTable.className = "div-table-pieces-released";

        const iconLiberacion = window.iconoLiberacion || (window.getIcon ? window.getIcon('vista_liberacion') : '') || `${window.baseUrl}/images/Color_Icons/Vistas/Liberacion_Pzas.png`;

        // Encabezado corporativo del modal con contador dinámico
        const headerModal = document.createElement("div");
        headerModal.className = "header-pieces-released-modal";
        headerModal.innerHTML = `
            <div class="header-brand-wrap">
                <img src="${iconLiberacion}" alt="Liberación de piezas" class="modal-header-icon" />
                <div class="header-title-box">
                    <span class="header-badge-warning">Atención Obligatoria</span>
                    <h2 class="header-modal-title">Liberación de Piezas — Control de Calidad</h2>
                    <p class="header-modal-desc">
                        Existen piezas maquinadas pendientes de inspección o con reporte. Debe liberar o rechazar cada pieza para continuar.
                    </p>
                </div>
            </div>
            <div class="header-counter-box">
                <span class="counter-number" id="pending-pieces-count">${window.pieces_Released.length}</span>
                <span class="counter-label">Piezas pendientes</span>
            </div>
        `;
        divTable.appendChild(headerModal);

        // Contenedor scrollable para la tabla
        const tableWrapper = document.createElement("div");
        tableWrapper.className = "table-wrapper-released";

        const table = document.createElement("table");
        table.id = "table-pieces-released-home";
        table.classList.add("table-pieces-released");

        const thead = document.createElement("thead");
        const tr = document.createElement("tr");

        // Agregar encabezados base
        const headers = ["Clase", "Orden de trabajo", "Juego", "Nombre del operador", "Máquina", "Proceso"];

        headers.forEach((header) => {
            const th = document.createElement("th");
            th.textContent = header;
            if (header === "Nombre del operador") th.classList.add("cell-long-text");
            tr.appendChild(th);
        });

        // Agregar columnas finales
        const moreHeaders = [
            "Errores",
            "Observaciones",
            "Fecha de Maquinado",
            "Liberar",
            "Rechazar",
            "Ver",
        ];

        moreHeaders.forEach((header) => {
            const th = document.createElement("th");
            const headerText = typeof header === "object" ? header.name : header;
            th.textContent = headerText;
            if (headerText === "Errores" || headerText === "Observaciones") th.classList.add("cell-long-text");
            tr.appendChild(th);
        });

        thead.appendChild(tr);
        table.appendChild(thead);
        tableWrapper.appendChild(table);
        divTable.appendChild(tableWrapper);

        divOpacity.appendChild(divTable);
        document.body.appendChild(divOpacity);
        crearTabla(window.pieces_Released, window.info_Pieces, divOpacity);
    }
}

function crearTabla(piezas, infoPiezas, divOpacity) {
    const table = document.getElementById("table-pieces-released-home");
    if (!table) return;

    const tbody = document.createElement("tbody");

    piezas.forEach((piezaRaw, counter) => {
        const tr = document.createElement("tr");
        const pieza = orderedArray(piezaRaw);

        Object.keys(pieza).forEach((key) => {
            if (key !== "colorPiece") {
                const td = document.createElement("td");
                switch (key) {
                    case "btn_release":
                        if (!pieza[key][1].includes("Incompleto") && pieza[key][0] != 1) {
                            td.appendChild(crearBotonLiberar(infoPiezas, counter, piezas, tr, divOpacity));
                        }
                        break;
                    case "btn_decline":
                        if (pieza[key] != 2) {
                            td.appendChild(crearBotonRechazar(infoPiezas, counter, tr, divOpacity));
                        }
                        break;
                    case "btn_seePiece":
                        td.appendChild(crearBotonVer(infoPiezas, counter, pieza[key]));
                        break;
                    default:
                        td.textContent = pieza[key] ?? "";
                        if (key === "operator" || key === "errors" || key === "observations" || key === "observacion_liberacion") {
                            td.classList.add("cell-long-text");
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

function asignColorTr(status, error, process) {
    switch (status) {
        case 1:
            return "#79BFED"; // Liberado - Azul
        case 2:
            return "#FF6B6B"; // Rechazado - Rojo
        case 3:
            return "#90EE90"; // Buena sin liberación - Verde
        case 4:
            if (process === "Soldadura PTA" && !error.toLowerCase().includes("fundicion") && !error.toLowerCase().includes("fundición")) {
                return "#90EE90";
            }
            return "#DDA0DD"; // Mala sin liberación - Morado
        case 5:
            return "#FFD700"; // Incompleto - Amarillo
        case 0:
        default:
            if (error.includes("Incompleto")) {
                return "#FFD700";
            } else if (error === "Ninguno") {
                return "#90EE90";
            } else {
                if (process === "Soldadura PTA" && !error.toLowerCase().includes("fundicion") && !error.toLowerCase().includes("fundición")) {
                    return "#90EE90";
                }
                return "#DDA0DD";
            }
    }
}

function orderedArray(array) {
    return {
        class: array["className"] ?? "",
        workOrder: array[0] ?? "",
        noAssembly: array[1] ?? "",
        operator: array[2] ?? "",
        machine: array[3] ?? "",
        process: array[4] ?? "",
        errors: array[5] ?? "",
        observations: array.observations ?? "",
        machinedDate: array[6] ?? "",
        btn_release: [array[9], array[5] ?? ""],
        btn_decline: array[9],
        btn_seePiece: array[2],
        colorPiece: asignColorTr(array[9], array[5] ?? "", array[4] ?? ""),
    };
}

function crearBotonLiberar(infoPiezas, i, piezas, tr, divOpacity) {
    const a = document.createElement("a");
    a.className = "btn-liberar";
    a.title = "Liberar pieza";
    a.style.cursor = "pointer";

    let bool = (infoPiezas[i][2] === "Ninguno" && piezas[i][9] != 2);

    const image = document.createElement("img");
    image.src = window.liberar;
    image.alt = "Liberar";
    image.className = "ver";
    a.appendChild(image);

    const keys = {
        pieza: infoPiezas[i][0],
        proceso: infoPiezas[i][1],
        liberar: true,
        buena: bool,
    };

    a.addEventListener("click", (e) => {
        e.preventDefault();
        create_ObservationsField(keys, tr, divOpacity);
    });
    return a;
}

function crearBotonRechazar(infoPiezas, i, tr, divOpacity) {
    const a = document.createElement("a");
    a.className = "btn-liberar";
    a.title = "Rechazar pieza";
    a.style.cursor = "pointer";

    const image = document.createElement("img");
    image.src = window.rechazar;
    image.alt = "Rechazar";
    image.className = "ver";
    a.appendChild(image);

    const keys = {
        pieza: infoPiezas[i][0],
        proceso: infoPiezas[i][1],
        liberar: false,
        buena: false,
    };

    a.addEventListener("click", (e) => {
        e.preventDefault();
        create_ObservationsField(keys, tr, divOpacity);
    });
    return a;
}

function create_ObservationsField(keys, tr, divOpacity) {
    const div_opacity = document.createElement("div");
    div_opacity.className = "div-opacity div-opacity-liberation";

    function closeObservationModal() {
        document.removeEventListener("keydown", handleKeyDown);
        div_opacity.style.willChange = "opacity";
        div_opacity.style.transition = "opacity 0.16s ease";
        div_opacity.style.opacity = "0";
        setTimeout(() => {
            if (div_opacity.parentNode) {
                div_opacity.remove();
            }
        }, 170);
    }

    const handleKeyDown = (e) => {
        if (e.key === "Escape") {
            closeObservationModal();
        }
    };
    document.addEventListener("keydown", handleKeyDown);

    div_opacity.addEventListener("click", (e) => {
        if (e.target === div_opacity) {
            closeObservationModal();
        }
    });

    const isLiberate = keys.liberar;
    const actionTitle = isLiberate ? "Liberar Pieza / Juego" : "Rechazar Pieza / Juego";
    const badgeClass = isLiberate ? "badge-liberate" : "badge-reject";
    const badgeText = isLiberate ? "Aprobación Calidad" : "Rechazo Calidad";
    const btnClass = isLiberate ? "btn-liberate" : "btn-reject";
    const btnText = isLiberate ? "Confirmar Liberación" : "Confirmar Rechazo";

    let juegoLabel = "Pieza";
    if (Array.isArray(keys.pieza) && keys.pieza.length > 0) {
        juegoLabel = keys.pieza[0].toString().split(/[HMJ]/i)[0] + "J";
    } else if (typeof keys.pieza === "string") {
        juegoLabel = keys.pieza.split(/[HMJ]/i)[0] + "J";
    }

    const formBox = document.createElement("div");
    formBox.className = "form-liberation";
    formBox.addEventListener("click", (e) => {
        e.stopPropagation();
    });
    formBox.innerHTML = `
        <div class="form-liberation-header">
            <h3 class="form-liberation-title">${actionTitle}</h3>
            <span class="liberation-badge ${badgeClass}">${badgeText}</span>
        </div>
        <div class="form-liberation-meta">
            <div>Proceso: <span class="meta-chip">${keys.proceso}</span></div>
            <div>Juego / Pieza: <span class="meta-chip">${juegoLabel}</span></div>
        </div>
        <textarea class="textArea-liberation" placeholder="${isLiberate ? 'Agregue una observación para la bitácora de calidad (Opcional)...' : 'Especifique el motivo de rechazo de la pieza (Requerido)...'}"></textarea>
        <div class="form-liberation-actions">
            <button type="button" class="btn-liberation-cancel">Cancelar</button>
            <button type="button" class="btn-liberation ${btnClass}">${btnText}</button>
        </div>
    `;

    const cancelBtn = formBox.querySelector(".btn-liberation-cancel");
    const confirmBtn = formBox.querySelector(".btn-liberation:not(.btn-liberation-cancel)");
    const textArea = formBox.querySelector(".textArea-liberation");

    cancelBtn.addEventListener("click", closeObservationModal);

    confirmBtn.addEventListener("click", async () => {
        const obsValue = textArea.value.trim();

        if (!isLiberate && !obsValue) {
            if (window.toastpremium) {
                window.toastpremium("Debe ingresar un motivo para rechazar la pieza.", "warning");
            }
            textArea.focus();
            return;
        }

        confirmBtn.disabled = true;
        cancelBtn.disabled = true;
        confirmBtn.textContent = "Procesando...";

        try {
            const tokenMeta = document.querySelector('meta[name="csrf-token"]');
            const token = tokenMeta ? tokenMeta.getAttribute("content") : "";

            const payload = {
                pieza: Array.isArray(keys.pieza) ? keys.pieza.join(",") : String(keys.pieza),
                proceso: keys.proceso,
                liberar: keys.liberar ? "true" : "false",
                buena: keys.buena ? "true" : "false",
                observationPiece: obsValue,
                requestLiberation: "yes"
            };

            const response = await fetch(`${window.baseUrl}/piezasLiberar`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": token,
                    "Accept": "application/json",
                    "X-Requested-With": "XMLHttpRequest"
                },
                body: JSON.stringify(payload)
            });

            if (!response.ok) {
                throw new Error(`Error en el servidor (${response.status})`);
            }

            const data = await response.json();
            closeObservationModal();

            if (window.toastpremium) {
                window.toastpremium(data.message || (isLiberate ? "Pieza liberada con éxito." : "Pieza rechazada."), "success");
            }

            // Animar y remover la fila correspondiente con fluidez GPU
            if (tr) {
                tr.style.willChange = "transform, opacity";
                tr.style.transition = "transform 0.22s cubic-bezier(0.2, 0.8, 0.2, 1), opacity 0.2s ease";
                tr.style.transform = "translate3d(35px, 0, 0)";
                tr.style.opacity = "0";

                setTimeout(() => {
                    tr.remove();

                    // Recalcular piezas restantes
                    const tableEl = document.getElementById("table-pieces-released-home");
                    const remaining = tableEl ? tableEl.querySelectorAll("tbody tr").length : 0;
                    const counterEl = document.getElementById("pending-pieces-count");
                    if (counterEl) {
                        counterEl.textContent = remaining;
                    }

                    // Si ya no quedan piezas pendientes, desbloquear y cerrar overlay
                    if (remaining === 0) {
                        if (window.toastpremium) {
                            window.toastpremium("¡Todas las piezas pendientes han sido procesadas con éxito!", "success");
                        }
                        if (divOpacity) {
                            divOpacity.style.willChange = "transform, opacity";
                            divOpacity.style.transition = "opacity 0.26s cubic-bezier(0.16, 1, 0.3, 1), transform 0.26s cubic-bezier(0.16, 1, 0.3, 1)";
                            divOpacity.style.opacity = "0";
                            divOpacity.style.transform = "scale(0.985)";
                            setTimeout(() => {
                                if (divOpacity.parentNode) {
                                    divOpacity.remove();
                                }
                            }, 270);
                        }
                    }
                }, 230);
            }
        } catch (err) {
            confirmBtn.disabled = false;
            cancelBtn.disabled = false;
            confirmBtn.textContent = btnText;
            if (window.toastpremium) {
                window.toastpremium("Error al procesar: " + err.message, "error");
            }
        }
    });

    div_opacity.appendChild(formBox);
    document.body.appendChild(div_opacity);
    textArea.focus();
}

function crearBotonVer(infoPiezas, i, usuarios) {
    const a = document.createElement("a");
    a.className = "btn-pza";
    a.title = "Inspeccionar medidas de la pieza";

    let nPiezas = [];
    for (let j = 0; j < infoPiezas[i][0].length; j++) {
        nPiezas.push(infoPiezas[i][0][j]);
    }

    const url = `${window.baseUrl}/pieces/${nPiezas.join(",")}/${infoPiezas[i][1]}/quality?from=home`;
    a.href = url;

    const image = document.createElement("img");
    image.src = window.ojito;
    image.alt = "Ver";
    image.className = "ver";
    a.appendChild(image);
    return a;
}

// Marcar en sessionStorage cuando el usuario navega dando clic al menú de navegación
document.addEventListener("click", function (e) {
    const link = e.target.closest("a");
    if (link && link.closest(".nav-list, .filter-opacity, nav, header")) {
        sessionStorage.setItem("gis_nav_entry", "true");
    }
});
