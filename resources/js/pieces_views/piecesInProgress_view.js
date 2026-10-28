// ══════════════════════════════════════════════════════════
// VISTA: ÓRDENES DE TRABAJO EN PROGRESO (PIECES IN PROGRESS)
// Arquitectura Modular:
//   - ./modules/dashboard.js: Renderizado de OTs y procesos
//   - ./modules/checklistCards.js: Cards reactivas (PTA, Fundición, Planeación, Térmico)
//   - ./modules/navDock.js: Controles inferiores, dropdown buscador y scroll-snap
//   - ./modules/loadMore.js: Paginación continua con backend y overlay .div-opacity
// ══════════════════════════════════════════════════════════

import { Dashboard } from "./modules/dashboard.js";
import {
    initNavDock,
    updateCachedSections,
    getCachedSections
} from "./modules/navDock.js";
import { performLoadMoreOTs } from "./modules/loadMore.js";

let wOrderArray = window.wOInProgress;

// Limpieza defensiva de overlays residuales
let div_opacity = document.querySelector(".div-opacity");
if (div_opacity && div_opacity.id !== "pip-loading-blue-filter") {
    let btn_cerrar = document.querySelector(".btn-cerrar");
    if (btn_cerrar) {
        btn_cerrar.addEventListener("click", (e) => {
            e.stopPropagation();
            let div_padre = document.querySelector(".div-opacity");
            if (div_padre && div_padre.id !== "pip-loading-blue-filter") div_padre.remove();
        });
    }
    div_opacity.addEventListener("click", (e) => {
        if (e.target === div_opacity && div_opacity.id !== "pip-loading-blue-filter") {
            let div_padre = document.querySelector(".div-opacity");
            if (div_padre && div_padre.id !== "pip-loading-blue-filter") div_padre.remove();
        }
    });
}

if (wOrderArray && Object.keys(wOrderArray).length > 0) {
    let dashboard = new Dashboard(wOrderArray);
    window.activeDashboard = dashboard;
    dashboard.createSections();
    updateCachedSections();

    window.getClosestSection = function () {
        let closest = null;
        let minDist = Infinity;
        const scrollY = window.scrollY || document.documentElement.scrollTop;
        const sections = getCachedSections();

        for (let i = 0; i < sections.length; i++) {
            const sec = sections[i];
            const dist = Math.abs(sec.offsetTop - scrollY);
            if (dist < minDist) {
                minDist = dist;
                closest = sec.element;
            }
        }

        return closest;
    };
} else {
    let body = document.querySelector("body");
    let noDataMessage = document.createElement("h2");
    noDataMessage.className = "no-data-message";
    noDataMessage.innerHTML = "No hay órdenes de trabajo en progreso.";
    body.appendChild(noDataMessage);
}

// Inicializar el Dock de Navegación Inteligente y Paginación
initNavDock(performLoadMoreOTs);

// Modal de Finalizar Pedido
window.mostrarModalFinalizarPedido = async function (tipo, mensaje, recargar = false) {
    const isError = (tipo === "error");
    await window.gisAlert({
        title: isError ? "Completar piezas requeridas" : "Pedido finalizado",
        text: mensaje || (isError ? "No se pudo finalizar el pedido." : "Se ha finalizado el pedido correctamente."),
        confirmButtonText: "Entendido",
        confirmButtonColor: "#033966"
    });
    if (recargar) {
        location.reload();
    }
};
