// ── TOAST NOTIFICACIONES (SISTEMA CENTRALIZADO GIS) ──────────────────────────
function mostrarToast(mensaje, esError = false) {
    const tipo = (esError === true || esError === "error" || esError === "danger") ? "error" : "success";
    if (typeof window.gisToast === "function") {
        window.gisToast(mensaje, tipo);
    } else if (typeof window.toastpremium === "function") {
        window.toastpremium(mensaje, tipo);
    }
}

// Expose to window for global access
window.mostrarToast = mostrarToast;
