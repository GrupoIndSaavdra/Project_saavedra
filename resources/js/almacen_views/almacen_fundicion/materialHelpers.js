// ── MATERIAL HELPERS & CHECKLIST INTERACTS ──

window.updateCustomFileLabel = function (input) {
    const container = input.closest(".custom-file-dropzone");
    if (!container) return;
    
    const group = input.closest(".form-group") || input.closest(".ldm-upload-group");
    if (!group) return;
    
    const previewContainer = group.querySelector(".file-card-preview-container");
    const isRechazo = input.dataset.type === "rechazo";
    const isScar = input.dataset.type === "scar";
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const cleanName = file.name;
        
        // Bloquear boton de subir
        container.style.pointerEvents = "none";
        container.style.opacity = "0.5";
        container.style.cursor = "not-allowed";
        
        let borderColor = "#155724"; // Verde LDM
        if (isRechazo) borderColor = "#dc2626"; // Rojo Rechazo
        else if (isScar) borderColor = "#ca8a04"; // Dorado SCAR
        
        if (previewContainer) {
            previewContainer.innerHTML = `
                <div class="dibujos-file-card card-otro" style="animation-delay: 0s; border-left-color: ${borderColor}; display: flex; align-items: center; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); background: #fff; padding: 10px; border: 1.5px solid #e2e8f0; border-left: 5px solid ${borderColor}; width: 100%; box-sizing: border-box; margin-top: 8px;">
                    <div class="file-icon-wrapper" style="position:relative;width:38px;height:38px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <img src="${getBaseUrl()}images/pdf-view-shadow.png" class="file-icon icon-default" style="width:38px;height:38px;object-fit:contain;">
                    </div>
                    <div class="file-name" style="font-size: 0.85em; margin: 0 10px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-weight: 600; color: #334155; flex: 1; text-align: left;">
                        ${cleanName} <span style="font-size:0.8em;color:#64748b;font-weight:400;margin-left:5px;">(${(file.size / 1024).toFixed(1)} KB)</span>
                    </div>
                    <div class="file-actions alm-flex-gap-5" style="display:flex;gap:5px;flex-shrink:0;">
                        <button type="button" class="btn-dibujos btn-dibujos-sm btn-eliminar" style="font-size:0.8em;padding:5px 10px;border-radius:6px;font-family:'Poppins',sans-serif;font-weight:600;background:#ef4444;border-color:#ef4444;color:white;cursor:pointer;" onclick="eliminarArchivoLocalSeleccionado(this)">Eliminar</button>
                    </div>
                </div>
            `;
        }
    }
};

window.eliminarArchivoLocalSeleccionado = function (btn) {
    const group = btn.closest(".form-group") || btn.closest(".ldm-upload-group");
    if (!group) return;
    
    const fileInput = group.querySelector("input[type='file']");
    if (fileInput) {
        fileInput.value = "";
    }
    
    const container = group.querySelector(".custom-file-dropzone");
    if (container) {
        container.style.pointerEvents = "auto";
        container.style.opacity = "1";
        container.style.cursor = "pointer";
    }
    
    const previewContainer = group.querySelector(".file-card-preview-container");
    if (previewContainer) {
        previewContainer.innerHTML = "";
    }
};

/* =========================================================================
   AUTOGUARDADO DE FORMATO DE LIBERACION (LOCALSTORAGE)
   ========================================================================= */
window._libAutoSaveTimeout = null;
window.saveLiberacionDraft = function () {
    const form = document.getElementById("formLiberacion");
    if (!form) return;
    const ot = document.getElementById("lib-ot")?.value;
    const tipo = document.getElementById("lib-tipo")?.value;
    if (!ot || !tipo) return;
    // Solo guardar inputs numéricos y textareas para evitar pisar campos ocultos
    const inputs = form.querySelectorAll(
        ".lib-num-input, .lib-num-input-sm, .lib-textarea, #lib-motivo-rechazo",
    );
    const draftData = {};
    inputs.forEach((inp) => {
        if (inp.name) {
            draftData[inp.name] = inp.value;
        }
    });
    const key = `liberacion_draft_${ot}_${tipo}`;
    localStorage.setItem(key, JSON.stringify(draftData));
};

window.loadLiberacionDraft = function () {
    const form = document.getElementById("formLiberacion");
    if (!form) return;
    const ot = document.getElementById("lib-ot")?.value;
    const tipo = document.getElementById("lib-tipo")?.value;
    if (!ot || !tipo) return;
    const key = `liberacion_draft_${ot}_${tipo}`;
    const draftDataStr = localStorage.getItem(key);
    if (draftDataStr) {
        try {
            const draftData = JSON.parse(draftDataStr);
            const inputs = form.querySelectorAll(
                ".lib-num-input, .lib-num-input-sm, .lib-textarea, #lib-motivo-rechazo",
            );
            inputs.forEach((inp) => {
                if (inp.name && draftData[inp.name] !== undefined) {
                    inp.value = draftData[inp.name];
                }
            });
            console.log(
                `[Autosave] Borrador cargado para OT: ${ot}, Tipo: ${tipo}`,
            );
        } catch (e) {
            console.error("Error al parsear el borrador:", e);
        }
    }
};

window.clearLiberacionDraft = function () {
    const ot = document.getElementById("lib-ot")?.value;
    const tipo = document.getElementById("lib-tipo")?.value;
    if (ot && tipo) {
        const key = `liberacion_draft_${ot}_${tipo}`;
        localStorage.removeItem(key);
        console.log(
            `[Autosave] Borrador limpiado para OT: ${ot}, Tipo: ${tipo}`,
        );
    }
};

// Configurar el listener en el formulario para detectar cambios y disparar el autosave con debounce
document.addEventListener("DOMContentLoaded", () => {
    const formLib = document.getElementById("formLiberacion");
    if (formLib) {
        formLib.addEventListener("input", (e) => {
            if (
                e.target.matches(
                    ".lib-num-input, .lib-num-input-sm, .lib-textarea, #lib-motivo-rechazo",
                )
            ) {
                clearTimeout(window._libAutoSaveTimeout);
                window._libAutoSaveTimeout = setTimeout(() => {
                    window.saveLiberacionDraft();
                }, 800); // 800ms debounce
            }
        });
    }
});

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
    _poll() {
        if (!this.root || !this.root.isConnected) {
            this._destroy();
            return;
        }
        this._fetchStatus();
    }
    async _fetchStatus() {
        try {
            const endpointUrl =
                window.fundicionChecklistUrl ||
                `${window.location.origin}/admin/fundicion/checklist`;
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
        badge.classList.add("alm-display-none");
        header.appendChild(badge);
        card.appendChild(header);
        const itemsContainer = document.createElement("div");
        itemsContainer.className = "checklist-items";
        itemsContainer.id = `checklist-items-${this.otId}`;
        itemsContainer.classList.add("alm-display-none");
        card.appendChild(itemsContainer);
        card.classList.add("is-closed");
        // Toggle logic: make whole card clickable
        card.style.cursor = "pointer";
        card.addEventListener("click", () => {
            if (itemsContainer.classList.contains("alm-display-none")) {
                itemsContainer.classList.remove("alm-display-none");
                card.classList.remove("is-closed");
            } else {
                itemsContainer.classList.add("alm-display-none");
                card.classList.add("is-closed");
            }
        });
        this._updateCard(card, this._data);
    }
    _getIconFor(estado) {
        const baseUrl = window.baseUrl || window.location.origin + "/";
        const slash = baseUrl.endsWith("/") ? "" : "/";
        let imgName = "";
        switch (estado) {
            case "completado":
                imgName = "Aprobado.png";
                break;
            case "pendiente":
                imgName = "Espera.png";
                break;
            case "rechazado":
                imgName = "Rechazado.png";
                break;
            case "inactivo":
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
            badge.classList.toggle("alm-display-none", !data.isBadgeVisible);
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
    _destroy() {
        clearInterval(this._pollTimer);
        this._pollTimer = null;
        if (
            this.root &&
            this.root.isConnected &&
            !this.root.classList.contains("fundicion-checklist-card")
        ) {
            this.root.remove();
        }
    }
}

window.FundicionChecklistCard = FundicionChecklistCard;

window.initFundicionChecklists = function () {
    document
        .querySelectorAll(
            ".fundicion-checklist-card, .fundicion-checklist-container",
        )
        .forEach((el) => {
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
};

document.addEventListener("DOMContentLoaded", () => {
    setTimeout(window.initFundicionChecklists, 500);
});
