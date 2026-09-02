// ── SINCRONIZACIÓN AUTOMÁTICA Y MANUAL DE DIBUJOS ──────────────────────────────
window._syncSnapshot = {};
window._syncIntervalId = null;
window._lastSyncTime = null;
window.sincronizarDibujos = function (manual = false) {
    if (!manual) return; // Solo ejecutar cuando el usuario presiona "Sincronizar ahora"
    const isAlmacen = !!window.almacenRoutes;
    const btnId = isAlmacen
        ? "btn-sync-manual-almacen"
        : "btn-sync-manual-calidad";
    const btn = document.getElementById(btnId);
    if (manual && btn) {
        btn.innerHTML = `<span class="alm-spinner" style="width:14px;height:14px;border-width:2px;border-top-color:#fff;display:inline-block;vertical-align:middle;margin-right:5px;"></span> Sincronizando...`;
        btn.disabled = true;
    }
    const tbody = document.getElementById("alm-tbody-activa");
    if (!tbody) {
        if (manual && btn) {
            btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-right:5px;vertical-align:middle;"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg> Sincronizar ahora`;
            btn.disabled = false;
        }
        return;
    }
    const routesObj = window.almacenRoutes || window.calidadRoutes;
    if (!routesObj || !routesObj.archivos) return;
    const rows = tbody.querySelectorAll("tr[data-ot]");
    let promises = [];
    rows.forEach((row) => {
        const ot = row.getAttribute("data-ot");
        if (!ot) return;
        const p = fetch(`${routesObj.archivos}?ot=${encodeURIComponent(ot)}`)
            .then((res) => {
                if (!res.ok) throw new Error(`HTTP ${res.status}`);
                return res.json();
            })
            .then((data) => {
                if (data.existe) {
                    const count = Array.isArray(data.archivos)
                        ? data.archivos.length
                        : 0;
                    window._syncSnapshot[ot] = count;
                    const badge = row.querySelector(".badge-pdf-count");
                    if (badge) {
                        badge.textContent = count;
                    }
                }
            })
            .catch((err) => console.error(`Error sync OT ${ot}:`, err));
        promises.push(p);
    });
    Promise.all(promises).then(() => {
        if (manual && btn) {
            btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-right:5px;vertical-align:middle;"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg> Sincronizar ahora`;
            btn.disabled = false;
            if (typeof almacenToast === 'function') almacenToast("Sincronización manual completada.", "success");
        }
        const timeId = isAlmacen
            ? "sync-last-time-almacen"
            : "sync-last-time-calidad";
        const statusTime = document.getElementById(timeId);
        if (statusTime) {
            window._lastSyncTime = new Date();
            actualizarRelojSync();
        }
    });
};
function actualizarRelojSync() {
    const isAlmacen = !!window.almacenRoutes;
    const timeId = isAlmacen
        ? "sync-last-time-almacen"
        : "sync-last-time-calidad";
    const statusTime = document.getElementById(timeId);
    if (!statusTime || !window._lastSyncTime) return;
    const now = new Date();
    const diffSecs = Math.floor((now - window._lastSyncTime) / 1000);
    if (diffSecs < 5) {
        statusTime.textContent = "Actualizado: justo ahora";
    } else if (diffSecs < 60) {
        statusTime.textContent = `Actualizado: hace ${diffSecs} seg`;
    } else {
        const mins = Math.floor(diffSecs / 60);
        statusTime.textContent = `Actualizado: hace ${mins} min`;
    }
}
document.addEventListener("DOMContentLoaded", () => {
    if (document.getElementById("alm-tbody-activa")) {
        window._lastSyncTime = new Date();
        actualizarRelojSync();
        setInterval(actualizarRelojSync, 5000);
    }
});



// Expose to window for global access
window.actualizarRelojSync = actualizarRelojSync;
