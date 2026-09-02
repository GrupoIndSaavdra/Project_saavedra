// ── DYNAMIC SINGLE MATERIAL ORCHESTRATOR (Calidad) ──

window.actualizarContenedoresMateriales = function (ot, tipoModelo) {
    if (!window.almacenRoutes?.archivos) return;
    
    fetch(`${window.almacenRoutes.archivos}?ot=${encodeURIComponent(ot)}`)
        .then((res) => res.json())
        .then((data) => {
            if (data.success || data.existe) {
                const archivos = data.archivos || [];
                const tipoModeloNorm = (tipoModelo || "").trim().toLowerCase();
                const containerIdAprob = `alm-aprobados-container-${tipoModeloNorm.replace(/\s/g, "-")}`;
                const containerIdRech = `alm-rechazados-container-${tipoModeloNorm.replace(/\s/g, "-")}`;
                
                if (typeof window.pintarCardsArchivosAprobados === "function") {
                    window.pintarCardsArchivosAprobados(archivos, ot, containerIdAprob, tipoModelo);
                }
                if (typeof window.pintarCardsArchivosRechazados === "function") {
                    window.pintarCardsArchivosRechazados(archivos, ot, containerIdRech, tipoModelo);
                }
            }
        })
        .catch((err) => console.error("Error al actualizar contenedores de materiales:", err));
};

window.subirFormatoEscaneadoEstatico = function (ot, tipoModelo, decisionVal, inputId) {
    if (decisionVal === "aprobados" || decisionVal === "aprobado") {
        if (typeof window.subirArchivoAprobadoEstatico === "function") {
            window.subirArchivoAprobadoEstatico(ot, tipoModelo, inputId);
        }
    } else {
        if (typeof window.subirArchivoRechazadoEstatico === "function") {
            window.subirArchivoRechazadoEstatico(ot, tipoModelo, inputId);
        }
    }
};

// ══════════════════════════════════════════════════════════
// FundicionChecklistCard — Checklist reactivo del flujo de fundición
// ══════════════════════════════════════════════════════════
class FundicionChecklistCard {
    constructor(otId, container) {
        this.otId = otId;
        this._data = { pasos: {} };
        this.container = container;
        this._pollTimer = null;
        this._mounted = false;
        this.root = null;
        if (container.classList.contains("fundicion-checklist-card")) {
            this.root = container;
            this.root.innerHTML = "";
            this._mounted = true;
            this._renderInto(this.root);
        } else {
            this._mount();
        }
        this._poll();
        this._startPolling();
    }
    _mount() {
        if (this._mounted) return;
        this.root = document.createElement("div");
        this.root.className = "fundicion-checklist-card";
        this.root.id = `fundicion-checklist-${this.otId}`;
        this._renderInto(this.root);
        this.container.appendChild(this.root);
        this._mounted = true;
    }
    _renderInto(card) {
        const header = document.createElement("div");
        header.className = "checklist-header";
        const title = document.createElement("span");
        title.className = "checklist-title";
        title.textContent = "Levantamiento de OT";
        header.appendChild(title);
        const badge = document.createElement("span");
        badge.className = "checklist-reproceso-badge";
        badge.id = `checklist-badge-${this.otId}`;
        badge.textContent = "Reproceso";
        badge.classList.add("cal-display-none");
        header.appendChild(badge);
        card.appendChild(header);
        const itemsContainer = document.createElement("div");
        itemsContainer.className = "checklist-items";
        itemsContainer.id = `checklist-items-${this.otId}`;
        itemsContainer.classList.add("cal-display-none");
        card.appendChild(itemsContainer);
        card.classList.add("is-closed");
        
        card.style.cursor = "pointer";
        card.addEventListener("click", () => {
            if (itemsContainer.classList.contains("cal-display-none")) {
                itemsContainer.classList.remove("cal-display-none");
                card.classList.remove("is-closed");
            } else {
                itemsContainer.classList.add("cal-display-none");
                card.classList.add("is-closed");
            }
        });
        this._updateCard(card, this._data);
    }
    _getIconFor(pasoEstado) {
        const baseUrl = window.baseUrl || window.location.origin + "/";
        const slash = baseUrl.endsWith("/") ? "" : "/";
        let imgName = "";
        switch (pasoEstado) {
            case "completado":
                imgName = "Aprobado.png";
                break;
            case "pendiente":
                imgName = "Espera.png";
                break;
            case "rechazado":
                imgName = "Rechazado.png";
                break;
            default:
                imgName = "Recibido.png";
                break;
        }
        return `${baseUrl}${slash}images/${imgName}`;
    }
    _getBorderColor(data) {
        const pasos = Object.values(data.pasos || {});
        if (pasos.some((p) => p.estado === "rechazado")) return "#9D0402";
        if (pasos.length > 0 && pasos.every((p) => p.estado === "completado"))
            return "#0C8201";
        return "#424141";
    }
    _updateCard(card, data) {
        if (!card) return;
        let colorHex = this._getBorderColor(data);
        if (colorHex === "#9D0402") {
            card.style.borderColor = "#9D0402";
            card.style.boxShadow = "none";
        } else if (colorHex === "#0C8201") {
            card.style.borderColor = "#0C8201";
            card.style.boxShadow = "none";
        } else {
            card.style.borderColor = "";
            card.style.boxShadow = "";
        }
        const badge = card.querySelector(`#checklist-badge-${this.otId}`);
        if (badge) {
            badge.classList.toggle("cal-display-none", !data.isBadgeVisible);
            if (data.badgeText) badge.textContent = data.badgeText;
        }
        const container = card.querySelector(`#checklist-items-${this.otId}`);
        if (!container) return;
        container.innerHTML = "";
        const pasosEntries = Object.entries(data.pasos || {});
        if (pasosEntries.length === 0) {
            container.innerHTML = `<div style="padding: 10px; color: #64748b; font-size: 0.85rem; text-align: center;">Cargando estado...</div>`;
            return;
        }
        pasosEntries.forEach(([key, paso]) => {
            if (!paso) return;
            const item = document.createElement("div");
            item.className = `checklist-item checklist-item--${paso.estado}`;
            if (paso.tooltip) {
                item.title = paso.tooltip;
                item.style.cursor = "help";
            }
            const iconSpan = document.createElement("span");
            iconSpan.className = "checklist-icon";
            const img = document.createElement("img");
            img.src = this._getIconFor(paso.estado);
            img.alt = paso.estado;
            img.className = "checklist-state-icon";
            iconSpan.appendChild(img);
            const label = document.createElement("span");
            label.className = "checklist-label";
            label.textContent = paso.label;
            item.appendChild(iconSpan);
            item.appendChild(label);
            container.appendChild(item);
        });
    }
    _startPolling() {
        this._pollTimer = setInterval(() => this._poll(), 30_000);
    }
    async _poll() {
        if (!this.root || !this.root.isConnected) {
            this._destroy();
            return;
        }
        try {
            const endpointUrl = window.fundicionChecklistUrl || `${window.location.origin}/admin/fundicion/checklist`;
            const endpoint = `${endpointUrl}/${this.otId}`;
            const res = await fetch(endpoint);
            if (!res.ok) return;
            const data = await res.json();
            if (data && !data.error) {
                this._data = data;
                this._updateCard(this.root, data);
            }
        } catch (_) {}
    }
    _destroy() {
        clearInterval(this._pollTimer);
        this._pollTimer = null;
        if (this.root && this.root.isConnected && !this.root.classList.contains("fundicion-checklist-card")) {
            this.root.remove();
        }
    }
}

function initFundicionChecklists() {
    document.querySelectorAll(".fundicion-checklist-card, .fundicion-checklist-container").forEach((el) => {
        if (el.hasAttribute("data-checklist-init")) return;
        let otId = el.getAttribute("data-ot");
        if (!otId && el.id && el.id.startsWith("fundicion-checklist-")) {
            otId = el.id.replace("fundicion-checklist-", "");
        }
        if (otId) {
            el.setAttribute("data-checklist-init", "true");
            new FundicionChecklistCard(otId, el);
        }
    });
}

document.addEventListener("DOMContentLoaded", () => {
    setTimeout(initFundicionChecklists, 500);
});

window.initFundicionChecklists = initFundicionChecklists;
window.FundicionChecklistCard = FundicionChecklistCard;
