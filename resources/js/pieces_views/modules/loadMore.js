// ══════════════════════════════════════════════════════════
// MÓDULO LOAD MORE — Paginación Continua e Incremental de OTs
// ══════════════════════════════════════════════════════════
import {
    updateDockLoadMoreButton,
    updateCachedSections
} from "./navDock.js";

let isGlobalLoadingMore = false;

/**
 * Función centralizada para paginación continua de Órdenes de Trabajo en Progreso.
 * Solicita lotes de OTs al servidor y las renderiza en el DOM sincronizando ambos botones.
 */
export async function performLoadMoreOTs() {
    if (isGlobalLoadingMore) return null;

    const pagData = window.paginationData;
    if (!pagData || !pagData.hasMore) {
        if (typeof window.gisToast === "function") {
            window.gisToast("Todas las órdenes de trabajo activas están cargadas", "info");
        }
        return null;
    }

    isGlobalLoadingMore = true;
    updateDockLoadMoreButton(true);

    const startTime = Date.now();

    // Únicamente la alerta del lado derecho explicando la carga
    if (typeof window.gisToast === "function") {
        window.gisToast("Cargando órdenes de trabajo siguientes...", "info");
    }

    try {
        const baseUrl = window.routes?.showPiecesInProgress || window.location.href;
        const url = new URL(baseUrl, window.location.origin);
        url.searchParams.set("page", pagData.nextPage);

        const response = await fetch(url.toString(), {
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                "Accept": "application/json"
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP error ${response.status}`);
        }

        const data = await response.json();

        if (!data.success) {
            throw new Error(data.message || "Error al obtener más órdenes de trabajo");
        }

        // Marcador de seguridad contra auto-recarga agresiva
        window.hasLoadedMorePages = true;

        // 1. Integrar nuevos datos en los objetos globales
        window.wOInProgress = Object.assign(window.wOInProgress || {}, data.wOInProgress);
        window.orderedOtIds = (window.orderedOtIds || []).concat(data.orderedOtIds);

        if (data.ptaCardsData) {
            window.ptaCardsData = Object.assign(window.ptaCardsData || {}, data.ptaCardsData);
        }
        if (data.fundicionChecklist) {
            window.fundicionChecklist = Object.assign(window.fundicionChecklist || {}, data.fundicionChecklist);
        }

        const currentScrollY = window.scrollY || document.documentElement.scrollTop;

        if (window.activeDashboard) {
            Object.assign(window.activeDashboard.wOrderArray, data.wOInProgress);
            // 2. Renderizar únicamente las nuevas OTs
            window.activeDashboard.createSections(data.orderedOtIds);
        }

        // 3. Actualizar la caché de secciones y dropdown para navegación rápida
        updateCachedSections();

        // 3.1. Mantener estable el scroll exactamente donde estaba el usuario
        window.scrollTo({ top: currentScrollY, behavior: 'instant' });

        // 4. Actualizar metadata de paginación
        window.paginationData.hasMore = data.hasMore;
        window.paginationData.nextPage = data.nextPage;
        window.paginationData.loadedSoFar = data.loadedSoFar;
        window.paginationData.remaining = data.remaining;
        window.paginationData.totalWOs = data.totalWOs;

        // 5. Actualizar UI del botón del dock
        updateDockLoadMoreButton(false);

        // Garantizar que la alerta de carga complete su suave animación de entrada y lectura
        const elapsed = Date.now() - startTime;
        if (elapsed < 950) {
            await new Promise((resolve) => setTimeout(resolve, 950 - elapsed));
        }

        // Notificación de éxito a la derecha (estilo messages)
        if (typeof window.gisToast === "function") {
            window.gisToast(`Se cargaron ${data.orderedOtIds.length} órdenes de trabajo adicionales`, "success");
        }

        return data;

    } catch (err) {
        console.error("Error al cargar más órdenes de trabajo:", err);
        updateDockLoadMoreButton(false);
        if (typeof window.gisToast === "function") {
            window.gisToast("No se pudieron cargar más órdenes. Intenta nuevamente.", "error");
        }
        return null;
    } finally {
        updateDockLoadMoreButton(false);
        isGlobalLoadingMore = false;
    }
}
