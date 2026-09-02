/**
 * almacen_fundicion.js
 * Lógica de la vista de Almacén/Calidad para Dibujos de Fundición.
 */
console.log("ALMACEN_FUNDICION_JS_V2_LOADED");
function getBaseUrl() {
    let base = window.baseUrl || window.location.origin + "/";
    if (!base.endsWith("/")) base += "/";
    return base;
}
// Helper para notificaciones usando el sistema del layout
function almacenToast(message, type = "success") {
    if (typeof window.mostrarNotificacion === "function") {
        window.mostrarNotificacion(message, type);
    } else if (typeof window.toastpremium === "function") {
        window.toastpremium(message, type);
    } else if (typeof window.showToastAlert === "function") {
        window.showToastAlert(message, type);
    } else {
        alert(message);
    }
}
// Helper para truncar a 3 decimales sin redondear
function truncateToThreeDecimalsJS(val) {
    if (val === null || val === undefined || val === "") return "";
    let valStr = String(val);
    if (valStr.includes(".")) {
        let parts = valStr.split(".");
        let integerPart = parts[0];
        let decimalPart = parts[1].substring(0, 3);
        return integerPart + "." + decimalPart;
    }
    return valStr;
}
function formatInputTruncated(input) {
    let val = input.value;
    if (!val) return;
    let truncated = truncateToThreeDecimalsJS(val);
    if (truncated !== "") {
        let parts = truncated.split(".");
        let integerPart = parts[0];
        let decimalPart = parts[1] || "";
        while (decimalPart.length < 3) {
            decimalPart += "0";
        }
        input.value = integerPart + "." + decimalPart;
    }
}
function initTruncateInputs() {
    document.addEventListener(
        "blur",
        (e) => {
            if (e.target.matches(".lib-num-input, .lib-num-input-sm")) {
                formatInputTruncated(e.target);
            }
        },
        true,
    );
}
document.addEventListener("DOMContentLoaded", () => {
    if (typeof initToggleFiles === "function") initToggleFiles();
    initTruncateInputs();
    // Check if we need to open the model pre-order modal after a reload (rejections)
    const otToOpen = sessionStorage.getItem("openPreordenOt");
    if (otToOpen) {
        sessionStorage.removeItem("openPreordenOt");
        setTimeout(() => {
            if (typeof window.abrirModalPreOrden === "function") {
                window.abrirModalPreOrden(otToOpen);
            }
        }, 100);
    }
    // Check if we need to open the casting pre-order modal after a reload
    const otCastingToOpen = sessionStorage.getItem("openCastingOt");
    if (otCastingToOpen) {
        sessionStorage.removeItem("openCastingOt");
        setTimeout(() => {
            if (typeof window.abrirModalPreOrdenCasting === "function") {
                window.abrirModalPreOrdenCasting(otCastingToOpen);
            }
        }, 100);
    }
});


// Expose to window for global access
window.initTruncateInputs = initTruncateInputs;
window.truncateToThreeDecimalsJS = truncateToThreeDecimalsJS;
window.formatInputTruncated = formatInputTruncated;
window.getBaseUrl = getBaseUrl;
window.almacenToast = almacenToast;
