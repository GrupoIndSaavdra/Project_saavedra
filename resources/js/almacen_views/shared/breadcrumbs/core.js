// ── BREADCRUMBS ENGINE — Motor principal reutilizable ──────────────────────────
// Uso: window.SaavedraBreadcrumbs.render(containerId, steps, activeIndex, options)
//
// steps: Array de objetos { label: string, onClick?: string }
// options: { onClose?, onBack?, onRefresh?, extraButtons?: [{ icon, title, onclick, bg? }] }

window.SaavedraBreadcrumbs = {
    /**
     * Renderiza la barra de breadcrumbs dentro de un contenedor header.
     * Oculta automáticamente el botón de cierre tradicional (.div-cerrar).
     *
     * @param {string} containerId  — ID del elemento header que alojará los breadcrumbs
     * @param {Array}  steps        — Pasos del flujo [ { label, onClick? } ]
     * @param {number} activeIndex  — Índice del paso actual (resaltado en blanco)
     * @param {Object} options      — Callbacks y botones extra
     */
    render: function (containerId, steps, activeIndex, options = {}) {
        const header = document.getElementById(containerId);
        if (!header) return;

        // Ocultar botón cerrar tradicional si existe para evitar duplicados
        const oldCloseBtn = header.querySelector(".div-cerrar");
        if (oldCloseBtn) oldCloseBtn.style.display = "none";

        // Reutilizar wrapper existente o crear uno nuevo
        let wrapper = header.querySelector(".breadcrumbs-wrapper");
        if (!wrapper) {
            wrapper = document.createElement("div");
            wrapper.className = "breadcrumbs-wrapper";
            Object.assign(wrapper.style, {
                display: "flex",
                alignItems: "center",
                justifyContent: "space-between",
                width: "100%",
                marginBottom: "10px",
                borderBottom: "1px solid rgba(255,255,255,0.15)",
                paddingBottom: "8px",
                boxSizing: "border-box",
            });
            header.insertBefore(wrapper, header.firstChild);
        }

        // ── 1. Pasos del breadcrumb ──────────────────────────────────────────
        let stepsHtml = `<div class="breadcrumbs-steps" style="display:flex;align-items:center;gap:8px;font-family:'Poppins',sans-serif;font-size:0.85em;font-weight:600;color:rgba(255,255,255,0.6);">`;
        steps.forEach((step, idx) => {
            const isActive = idx === activeIndex;
            const style = isActive
                ? "color:#ffffff;text-shadow:0 0 10px rgba(255,255,255,0.4);font-weight:700;"
                : "color:rgba(255,255,255,0.55);cursor:pointer;";
            const clickAttr = !isActive && step.onClick ? `onclick="${step.onClick}"` : "";
            stepsHtml += `<span style="${style}" ${clickAttr}>${step.label}</span>`;
            if (idx < steps.length - 1) {
                stepsHtml += `<span style="color:rgba(255,255,255,0.3);margin:0 2px;">➔</span>`;
            }
        });
        stepsHtml += `</div>`;

        // ── 2. Botones de acción rápida ──────────────────────────────────────
        let actionsHtml = `<div class="breadcrumbs-actions" style="display:flex;align-items:center;gap:8px;">`;

        // Botones extra personalizados (si los hay)
        if (Array.isArray(options.extraButtons)) {
            options.extraButtons.forEach((btn) => {
                const bg = btn.bg || "rgba(255,255,255,0.15)";
                const safeOnclick = btn.onclick.replace(/"/g, '&quot;');
                actionsHtml += `
                    <button type="button" class="btn-breadcrumb-action"
                        onclick="${safeOnclick}" title="${btn.title || ""}"
                        style="background:${bg};border:none;color:white;border-radius:6px;width:28px;height:28px;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;transition:all 0.2s;font-size:0.9em;font-family:'Poppins',sans-serif;"
                        onmouseover="this.style.background='rgba(255,255,255,0.3)'"
                        onmouseout="this.style.background='${bg}'">
                        ${btn.icon}
                    </button>`;
            });
        }

        if (options.onBack) {
            actionsHtml += window.SaavedraBreadcrumbs._btnHtml("←", options.onBack, "Volver", "rgba(255,255,255,0.15)", "font-weight:700;");
        }
        if (options.onRefresh) {
            actionsHtml += window.SaavedraBreadcrumbs._btnHtml("🔄", options.onRefresh, "Actualizar datos", "rgba(255,255,255,0.15)");
        }
        if (options.onClose) {
            const assetUrl = window.baseUrl || (window.location.origin + "/");
            const cleanUrl = assetUrl.endsWith('/') ? assetUrl : (assetUrl + '/');
            const imgPath = cleanUrl + 'images/cerrar.png';
            actionsHtml += `
                <button type="button" class="btn-cerrar btn-breadcrumb-action" onclick="${options.onClose.replace(/"/g, '&quot;')}" title="Cerrar"
                    style="background:none;border:none;padding:0;cursor:pointer;transition:transform 0.3s ease;display:inline-flex;align-items:center;justify-content:center;margin-left:6px;"
                    onmouseover="this.style.transform='scale(1.1)'"
                    onmouseout="this.style.transform='scale(1)'">
                    <img src="${imgPath}" alt="Cerrar" style="width:36px;height:36px;object-fit:contain;" />
                </button>
            `;
        }

        actionsHtml += `</div>`;
        wrapper.innerHTML = stepsHtml + actionsHtml;
    },

    /** Helper interno para generar un botón de acción */
    _btnHtml: function (icon, onclick, title, bg, extraStyle = "", hoverBg = null, outBg = null) {
        const hBg = hoverBg || "rgba(255,255,255,0.3)";
        const oBg = outBg || bg;
        const safeOnclick = onclick.replace(/"/g, '&quot;');
        return `
            <button type="button" class="btn-breadcrumb-action"
                onclick="${safeOnclick}" title="${title}"
                style="background:${bg};border:none;color:white;border-radius:6px;width:28px;height:28px;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;transition:all 0.2s;font-size:0.9em;font-family:'Poppins',sans-serif;${extraStyle}"
                onmouseover="this.style.background='${hBg}'"
                onmouseout="this.style.background='${oBg}'">
                ${icon}
            </button>`;
    },

    /**
     * Destruye la barra de breadcrumbs de un header, restaurando el botón original.
     * @param {string} containerId
     */
    destroy: function (containerId) {
        const header = document.getElementById(containerId);
        if (!header) return;
        const wrapper = header.querySelector(".breadcrumbs-wrapper");
        if (wrapper) wrapper.remove();
        const oldCloseBtn = header.querySelector(".div-cerrar");
        if (oldCloseBtn) oldCloseBtn.style.display = "";
    },
};
