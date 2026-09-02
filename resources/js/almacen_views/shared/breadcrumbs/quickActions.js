// ── QUICK ACTIONS — Botones rápidos estándar para modales ─────────────────────
// Expone window.QuickActions con helpers para cerrar, recargar y navegar.
// Se pueden usar directamente en inline handlers de Blade o JS.

window.QuickActions = {

    /**
     * Cierra cualquier modal que tenga la clase .alm-modal.open
     * o un modal específico por ID.
     * @param {string} [modalId] — Si se omite, cierra el modal abierto más cercano al foco.
     */
    cerrarModal: function (modalId) {
        if (modalId) {
            const m = document.getElementById(modalId);
            if (m) {
                m.classList.remove("open");
                document.body.classList.remove("modal-open");
            }
        } else {
            const abiertos = document.querySelectorAll(".alm-modal.open, .alm-modal-overlay.open");
            abiertos.forEach((m) => m.classList.remove("open"));
            document.body.classList.remove("modal-open");
        }
    },

    /**
     * Recarga la página actual.
     * @param {number} [delay=0] — ms de espera antes de recargar (útil tras operaciones async)
     */
    recargarPagina: function (delay = 0) {
        if (delay > 0) {
            setTimeout(() => window.location.reload(), delay);
        } else {
            window.location.reload();
        }
    },

    /**
     * Abre un PDF en el visor de archivos del sistema.
     * @param {string} ot
     * @param {string} archivo
     * @param {string} tipo
     */
    verArchivo: function (ot, archivo, tipo) {
        if (typeof window.almacenVerPdf === "function") {
            window.almacenVerPdf(ot, archivo, tipo);
        } else if (typeof window.calidadVerPdf === "function") {
            window.calidadVerPdf(ot, archivo, tipo);
        }
    },

    /**
     * Genera HTML de un botón rápido independiente (sin breadcrumbs wrapper).
     * Útil para insertar inline en cualquier header de modal.
     *
     * @param {Object} options
     * @param {string} options.icon     — Emoji o HTML del icono
     * @param {string} options.onclick  — Código JS del onclick
     * @param {string} [options.title]  — Tooltip del botón
     * @param {string} [options.bg]     — Color de fondo (default: rgba(255,255,255,0.15))
     * @param {string} [options.hoverBg]— Color hover
     * @returns {string} HTML del botón
     */
    btnHtml: function ({ icon, onclick, title = "", bg = "rgba(255,255,255,0.15)", hoverBg = "rgba(255,255,255,0.3)" }) {
        return `
            <button type="button" class="btn-breadcrumb-action"
                onclick="${onclick}" title="${title}"
                style="background:${bg};border:none;color:white;border-radius:6px;width:28px;height:28px;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;transition:all 0.2s;font-size:0.9em;font-family:'Poppins',sans-serif;"
                onmouseover="this.style.background='${hoverBg}'"
                onmouseout="this.style.background='${bg}'">
                ${icon}
            </button>`;
    },

    /** Botón de cerrar (rojo ✕) */
    btnCerrar: function (onclick) {
        return window.QuickActions.btnHtml({
            icon: "✕",
            onclick,
            title: "Cerrar",
            bg: "#ef4444",
            hoverBg: "#dc2626",
        });
    },

    /** Botón de recargar (🔄) */
    btnRecargar: function (onclick) {
        return window.QuickActions.btnHtml({
            icon: "🔄",
            onclick,
            title: "Actualizar datos",
        });
    },

    /** Botón de volver (←) */
    btnVolver: function (onclick) {
        return window.QuickActions.btnHtml({
            icon: "←",
            onclick,
            title: "Volver",
            bg: "rgba(255,255,255,0.2)",
        });
    },

    /**
     * Renderiza una barra de acciones mínima (solo botones, sin pasos).
     * Útil para headers simples que solo necesitan Cerrar y/o Recargar.
     *
     * @param {string} containerId  — ID del header
     * @param {Object} options      — { onClose?, onBack?, onRefresh? }
     */
    renderBarraAcciones: function (containerId, options = {}) {
        const header = document.getElementById(containerId);
        if (!header) return;
        const oldBtn = header.querySelector(".div-cerrar");
        if (oldBtn) oldBtn.style.display = "none";

        let existing = header.querySelector(".quick-actions-bar");
        if (!existing) {
            existing = document.createElement("div");
            existing.className = "quick-actions-bar";
            Object.assign(existing.style, {
                display: "flex",
                alignItems: "center",
                justifyContent: "flex-end",
                gap: "8px",
                width: "100%",
                marginBottom: "6px",
            });
            header.insertBefore(existing, header.firstChild);
        }

        let html = "";
        if (options.onBack)    html += window.QuickActions.btnVolver(options.onBack);
        if (options.onRefresh) html += window.QuickActions.btnRecargar(options.onRefresh);
        if (options.onClose)   html += window.QuickActions.btnCerrar(options.onClose);
        existing.innerHTML = html;
    },
};
