// ── TOAST NOTIFICACIONES ──────────────────────────────────────────────────────
function mostrarToast(mensaje, esError = false) {
    const prev = document.querySelector(".alm-toast");
    if (prev) prev.remove();
    const toast = document.createElement("div");
    toast.className = "alm-toast " + (esError ? "error" : "success");
    let baseUrl = window.baseUrl || window.location.origin + "/";
    if (!baseUrl.endsWith("/")) baseUrl += "/";
    const iconPath = esError
        ? baseUrl + "images/delete.png"
        : baseUrl + "images/ready.png";
    const iconAlt = esError ? "error" : "success";
    toast.innerHTML = `
<img src="${iconPath}" class="alert-icon-small" alt="${iconAlt}">
<div class="alert-content">
${mensaje}
</div>
<button class="close-alert-x" onclick="this.parentElement.remove()">×</button>
`;
    document.body.appendChild(toast);
    setTimeout(() => {
        if (toast.parentNode) {
            toast.classList.add("fade-out");
            setTimeout(() => {
                if (toast.parentNode) toast.remove();
            }, 450);
        }
    }, 4000);
}


// Expose to window for global access
window.mostrarToast = mostrarToast;
