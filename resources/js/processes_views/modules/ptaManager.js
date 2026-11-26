/**
 * Módulo de Procesos Especiales PTA / Soldadura
 * Modal de verificación de supervisor, toggles de 2da pasada y selector de materiales
 */

import { getFieldLabelHtml } from './config/formFields.config.js';

function insertWeldingTypeBox() {
    const procesosAplicables = [
        "Primera Operacion", "Segunda Operacion", "Operacion Equipo",
        "Cepillado", "Soldadura", "Soldadura PTA"
    ];

    if (!window.arrayData) return;

    const proceso = window.arrayData["process"];
    if (!procesosAplicables.includes(proceso)) return;

    const tipoSoldaduraRaw = window.arrayData["tipo_soldadura"];
    const tipoSoldaduraLabels = {
        "1": "P1 - 3",
        "2": "P2 - 2.5",
        "3": "P3 - 2",
        "4": "P4 - 1.5",
    };
    const tipoSoldadura = tipoSoldaduraRaw
        ? (tipoSoldaduraLabels[String(tipoSoldaduraRaw)] ?? ("Tipo " + tipoSoldaduraRaw))
        : "-";

    const formGrid = document.querySelector(".form-grid");
    if (!formGrid) return;

    // Evitar duplicados
    if (document.getElementById("weld-type-group")) return;

    let formGroup = document.createElement("div");
    formGroup.id = "weld-type-group";
    formGroup.className = "form-group full-width";

    let input = document.createElement("input");
    input.name = "tipo_soldadura";
    input.id = "tipo_soldadura";
    input.type = "text";
    input.className = "form-control normal-input";
    input.readOnly = true;
    input.value = tipoSoldadura;

    const isEditMode = window.arrayData && (window.arrayData["edit"] == 1 || window.arrayData["edit"] == 2);
    let badgeHtml = "";
    if (isEditMode) {
        formGroup.classList.add("form-group-locked");
        input.classList.add("input-locked");
        input.setAttribute("tabindex", "-1");
        input.setAttribute("aria-readonly", "true");
        input.title = "Este campo está bloqueado y no se puede editar";
        const blockedIconUrl = window.imgBloqueado || (window.baseUrl ? `${window.baseUrl.replace(/\/$/, '')}/images/Light_Icons/Bloqueado-Light.png` : "/images/Light_Icons/Bloqueado-Light.png");
        badgeHtml = `<span class="locked-field-badge" title="Campo bloqueado - No modificable"><img src="${blockedIconUrl}" class="locked-field-icon" alt="Bloqueado"> Bloqueado</span>`;
    }

    let label = document.createElement("label");
    label.className = "form-label";
    label.setAttribute("for", "tipo_soldadura");
    label.innerHTML = getFieldLabelHtml("Light_Icons/Proceso-Light.png", "Tipo de Soldadura") + badgeHtml;

    formGroup.appendChild(input);
    formGroup.appendChild(label);
    formGrid.appendChild(formGroup);
}


//--------------------------------------------------------------------------------
// LÓGICA TARJETA SOLDADURA PTA EN REPORTE DE PRODUCCIÓN
//--------------------------------------------------------------------------------

document.addEventListener("DOMContentLoaded", () => {
    // Listener is injected dynamically in createHistoricalTable for "Soldadura PTA" Process
});

function createPtaPasswordModal(otId) {
    // 1. Crear el div de opacidad (fondo oscuro)
    let divOpacity = document.createElement("div");
    divOpacity.className = "div-opacity";
    divOpacity.id = "pta-modal-opacity";

    // 2. Crear contenedor principal del modal
    let modalContainer = document.createElement("div");
    modalContainer.className = "pta-modal-container";

    // Evitar que al hacer clic en el modal se cierre
    modalContainer.addEventListener("click", function (e) {
        e.stopPropagation();
    });

    // Icono (opcional)
    let icon = document.createElement("div");
    icon.className = "pta-modal-icon";
    // Icono removido por petición del usuario
    modalContainer.appendChild(icon);

    // 3. Crear título
    let title = document.createElement("h3");
    title.textContent = "Acceso Restringido";
    title.className = "pta-modal-title";
    modalContainer.appendChild(title);

    // 4. Crear texto descriptivo
    let desc = document.createElement("p");
    desc.textContent = "Ingrese la contraseña de supervisor para registrar o visualizar los resultados de Soldadura PTA.";
    desc.className = "pta-modal-desc";
    modalContainer.appendChild(desc);

    // 5. Crear el formulario
    let form = document.createElement("form");
    form.id = "pta-password-form";

    // 5.1 Input Token CSRF
    let tokenMeta = document.querySelector('meta[name="csrf-token"]');
    let token = tokenMeta ? tokenMeta.getAttribute("content") : "";
    let inputToken = document.createElement("input");
    inputToken.type = "hidden";
    inputToken.name = "_token";
    inputToken.value = token;
    form.appendChild(inputToken);

    // 5.2 Input Password
    let inputPassword = document.createElement("input");
    inputPassword.type = "password";
    inputPassword.name = "password";
    inputPassword.placeholder = "Ingresa la contraseña";
    inputPassword.className = "pta-modal-input";
    inputPassword.required = true;

    form.appendChild(inputPassword);

    // 5.3 Contenedor de Error
    let errorMsg = document.createElement("div");
    errorMsg.className = "pta-modal-error";
    form.appendChild(errorMsg);

    // 5.4 Contenedor de Botones
    let btnContainer = document.createElement("div");
    btnContainer.className = "pta-modal-btn-container";

    // Botón Cancelar
    let btnCancel = document.createElement("button");
    btnCancel.type = "button";
    btnCancel.textContent = "Cancelar";
    btnCancel.className = "pta-modal-btn pta-modal-btn-cancel";
    btnCancel.addEventListener("click", () => {
        divOpacity.remove();
    });

    // Botón Aceptar
    let btnSubmit = document.createElement("button");
    btnSubmit.type = "submit";
    btnSubmit.textContent = "Acceder";
    btnSubmit.className = "pta-modal-btn pta-modal-btn-submit";

    btnContainer.appendChild(btnCancel);
    btnContainer.appendChild(btnSubmit);
    form.appendChild(btnContainer);

    // 6. Enviar formulario por Fetch (AJAX)
    form.addEventListener("submit", function (e) {
        e.preventDefault();

        btnSubmit.disabled = true;
        btnSubmit.textContent = "Verificando...";
        errorMsg.hidden = true;

        let formData = new FormData(this);
        formData.append("ot_id", otId);

        let baseUrl = window.baseUrl || "";

        fetch(baseUrl + "/admin/pta/verify-temp-password", {
            method: "POST",
            body: formData,
            headers: {
                "X-Requested-With": "XMLHttpRequest"
            }
        })
            .then(response => response.json().then(data => ({ status: response.status, body: data })))
            .then(result => {
                if (result.status === 200 && result.body.success) {
                    // Contraseña correcta: redigir a la URL que manda el server
                    window.location.href = result.body.redirect_url;
                } else {
                    // Contraseña incorrecta u otro error
                    errorMsg.textContent = result.body.message || "Error de verificación.";
                    errorMsg.hidden = false;
                    btnSubmit.disabled = false;
                    btnSubmit.textContent = "Ingresar";
                    inputPassword.value = "";
                    inputPassword.focus();
                }
            })
            .catch(error => {
                console.error("Error en petición PTA:", error);
                errorMsg.textContent = "Ocurrió un error. Intente nuevamente.";
                errorMsg.hidden = false;
                btnSubmit.disabled = false;
                btnSubmit.textContent = "Ingresar";
            });
    });

    // Armar el DOM
    modalContainer.appendChild(title);
    modalContainer.appendChild(desc);
    modalContainer.appendChild(form);
    divOpacity.appendChild(modalContainer);

    // Enfocar el input al abrir y cerrar al hacer clic afuera
    divOpacity.addEventListener("click", function (e) {
        if (e.target === divOpacity) {
            divOpacity.remove();
        }
    });

    document.body.appendChild(divOpacity);
    inputPassword.focus();
}

// ────────────────────────────────────────────────────────────────────────────
// 2DA PASADA — toggle checkbox (PTA table partial, cargado via AJAX)
// La función debe estar en window porque el partial se inyecta via innerHTML
// y sus <script> no se ejecutan.
// ────────────────────────────────────────────────────────────────────────────
window.handleP2Checkbox = function (p2Id) {
    const chk = document.getElementById("chk-p2-" + p2Id);
    const hdnAct = document.getElementById("inp-p2-activa-" + p2Id);

    if (!chk) return;

    const activate = chk.checked;

    window._setP2Rows(p2Id, activate);
    if (hdnAct) hdnAct.value = activate ? "1" : "0";
};

window._setP2Rows = function (p2Id, show) {
    // La fila de 2da pasada tiene style="display:none" inline; se manipula con style.display.
    // También se busca el índice -0 que es el único que existe en modo captura.
    [0, 1, 2].forEach(function (i) {
        const row = document.getElementById("row-p2-" + p2Id + "-" + i);
        if (row) row.style.display = show ? "" : "none";
    });
};



// Manejadores para el selector de material de soldadura con opción "Otro"
window.handlePTAMaterialSelectChange = function (idWidget) {
    const selectEl = document.getElementById('select_' + idWidget);
    const otroWrap = document.getElementById('otro_wrap_' + idWidget);
    const inputEl = document.getElementById('input_' + idWidget);

    if (selectEl.value === '__otro__') {
        const name = selectEl.name;
        selectEl.name = '';
        selectEl.hidden = true;

        otroWrap.hidden = false;
        otroWrap.classList.add('visible');
        inputEl.name = name;
        inputEl.disabled = false; // ← Asegurar habilitado en JS al alternar
        inputEl.focus();
    }
};

window.handlePTAMaterialBackClick = function (idWidget, originalName) {
    const selectEl = document.getElementById('select_' + idWidget);
    const otroWrap = document.getElementById('otro_wrap_' + idWidget);
    const inputEl = document.getElementById('input_' + idWidget);

    inputEl.name = '';
    inputEl.value = '';
    inputEl.disabled = true; // ← Deshabilitar para que no interfiera ni se envíe vacío
    otroWrap.hidden = true;
    otroWrap.classList.remove('visible');

    selectEl.name = originalName;
    selectEl.value = '';
    selectEl.hidden = false;
};

window.insertWeldingTypeBox = insertWeldingTypeBox;
window.createPtaPasswordModal = createPtaPasswordModal;

// ────────────────────────────────────────────────────────────────────────────
// COLOREADO EN VIVO Y SELECTOR MODAL DE PROGRAMAS SOLDADURA PTA
// ────────────────────────────────────────────────────────────────────────────
document.addEventListener("change", (e) => {
    const target = e.target;
    if (!target || !target.name) return;
    if (!target.name.startsWith("resultado[") && !target.name.startsWith("p2_resultado[")) return;

    const tr = target.closest("tr");
    if (!tr) return;

    tr.classList.remove(
        "pta-row-ok", "pta-row-error", "pta-row-liberada",
        "pta-row-rechazada", "pta-row-buena", "pta-row-mala", "pta-row-incompleta"
    );

    const val = target.value;
    if (val === "En mal estado" || val === "Mal") {
        tr.classList.add("pta-row-error");
    } else if (val === "En correcto estado" || val === "Bien") {
        tr.classList.add("pta-row-ok");
    }
});

function syncPtaButtonWithPath(hiddenInput, path) {
    if (!hiddenInput) return;
    hiddenInput.value = path || "";
    const td = hiddenInput.closest("td");
    const btn = td ? td.querySelector(".pta-prog-btn") : null;
    if (!btn) return;

    const label = btn.querySelector(".pta-prog-btn-label");
    const fileName = path ? path.split("/").pop() : "Seleccionar programa";
    if (label) label.textContent = fileName;
    btn.title = path || "Seleccionar programa de Soldadura PTA";
    btn.classList.toggle("has-program", Boolean(path));
}

function escapeHtmlPta(str) {
    return String(str ?? "")
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#39;");
}

window.openPtaProgramModal = function (triggerBtn) {
    if (!triggerBtn) return;
    const wrapper = triggerBtn.closest(".pta-table-wrapper");
    const td = triggerBtn.closest("td");
    const targetInput = td ? td.querySelector(".pta-programa-input") : null;
    if (!wrapper || !targetInput) return;

    const isP2Override = triggerBtn.dataset.isP2 === "1";
    const otId = wrapper.dataset.ptaOtId || "";
    const claseId = wrapper.dataset.ptaClaseId || "";
    const processId = wrapper.dataset.ptaProcessId || "";
    const otLabel = wrapper.dataset.ptaOtLabel || `OT ${otId}`;
    const claseLabel = wrapper.dataset.ptaClaseLabel || "";
    const listUrl = window.routes?.ptaProgramasList;
    const uploadUrl = window.routes?.ptaProgramasUpload;
    const assignUrl = window.routes?.ptaProgramasAssign;

    if (!listUrl) {
        alert("No está configurada la ruta de programas de Soldadura PTA.");
        return;
    }

    document.getElementById("pta-prog-modal-overlay")?.remove();

    const overlay = document.createElement("div");
    overlay.id = "pta-prog-modal-overlay";
    overlay.className = "pta-prog-modal-overlay";

    const modal = document.createElement("div");
    modal.className = "pta-prog-modal";
    modal.innerHTML = `
        <div class="pta-prog-modal-header">
            <div>
                <h3>Seleccionar Programa — Soldadura PTA</h3>
                <p class="pta-prog-modal-sub">DOCUMENTACION_GIS / PROGRAMAS_SOLDADURA_PTA</p>
            </div>
            <button type="button" class="pta-prog-modal-close" title="Cerrar">&times;</button>
        </div>
        <div class="pta-prog-tabs">
            <button type="button" class="pta-prog-tab active" data-tab="current">Esta OT y Clase</button>
            <button type="button" class="pta-prog-tab" data-tab="all">Todas las OTs</button>
        </div>
        <div class="pta-prog-toolbar">
            <input type="search" class="pta-prog-search" placeholder="Buscar programa, OT o clase...">
            <input type="file" id="pta-prog-file-upload" style="display:none;">
            <button type="button" class="pta-prog-upload-btn">+ Subir programa</button>
        </div>
        <div class="pta-prog-modal-body">
            <div class="pta-prog-empty">Cargando programas...</div>
        </div>
    `;

    overlay.appendChild(modal);
    document.body.appendChild(overlay);

    const bodyEl = modal.querySelector(".pta-prog-modal-body");
    const searchEl = modal.querySelector(".pta-prog-search");
    const fileInputEl = modal.querySelector("#pta-prog-file-upload");
    const uploadBtnEl = modal.querySelector(".pta-prog-upload-btn");
    const closeBtnEl = modal.querySelector(".pta-prog-modal-close");
    let activeTab = "current";
    let cachedData = null;

    const closeModal = () => {
        overlay.remove();
        document.removeEventListener("keydown", onEsc);
    };
    const onEsc = (ev) => {
        if (ev.key === "Escape") closeModal();
    };
    document.addEventListener("keydown", onEsc);
    closeBtnEl.addEventListener("click", closeModal);
    overlay.addEventListener("click", (ev) => {
        if (ev.target === overlay) closeModal();
    });

    async function selectProgram(file) {
        syncPtaButtonWithPath(targetInput, file.path);

        if (!isP2Override) {
            // Aplicar a todas las piezas activas en la tabla de captura actual
            wrapper.querySelectorAll(".pta-programa-input").forEach((inp) => {
                if (!inp.classList.contains("pta-programa-p2-input") || !inp.value) {
                    syncPtaButtonWithPath(inp, file.path);
                }
            });
            wrapper.dataset.ptaActiveProgram = file.path;

            if (assignUrl) {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "";
                try {
                    await fetch(assignUrl, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "Accept": "application/json",
                            "X-CSRF-TOKEN": csrf,
                        },
                        body: JSON.stringify({
                            programa_pta: file.path,
                            id_proceso: processId || null,
                            ot_id: otId,
                            clase_id: claseId,
                        }),
                    });
                } catch (err) {
                    console.error("Error al persistir programa PTA:", err);
                }
            }

            if (typeof window.gisToast === "function") {
                window.gisToast(`Programa "${file.name}" asignado para Soldadura PTA.`, "success");
            }
        }
        closeModal();
    }

    function renderFileButton(file, selectedPath) {
        const btn = document.createElement("button");
        btn.type = "button";
        btn.className = "pta-prog-file-item" + (selectedPath === file.path ? " is-selected" : "");
        btn.innerHTML = `
            <span class="pta-prog-file-meta">
                <span class="pta-prog-file-name">${escapeHtmlPta(file.name)}</span>
                <span class="pta-prog-file-sub">${escapeHtmlPta(file.ot)} / ${escapeHtmlPta(file.clase)}</span>
            </span>
            <span class="pta-prog-file-ext">${escapeHtmlPta(file.extension)}</span>
        `;
        btn.addEventListener("click", () => selectProgram(file));
        return btn;
    }

    function renderContent() {
        if (!cachedData) return;
        const term = (searchEl.value || "").trim().toLowerCase();
        const selectedPath = targetInput.value || cachedData.active_program || "";
        bodyEl.replaceChildren();

        if (activeTab === "current") {
            const cur = cachedData.current || {};
            const banner = document.createElement("div");
            banner.className = "pta-prog-folder-banner";
            banner.textContent = `Carpeta actual: ${cur.ot_folder || otLabel} / ${cur.clase_folder || claseLabel}`;
            bodyEl.appendChild(banner);

            const files = (cur.files || []).filter((f) =>
                !term || f.name.toLowerCase().includes(term)
            );

            if (!files.length) {
                const empty = document.createElement("div");
                empty.className = "pta-prog-empty";
                empty.textContent = term
                    ? "No hay programas en esta OT/Clase que coincidan con tu búsqueda."
                    : "Aún no hay programas en esta OT y Clase. Puedes subir uno arriba o elegir de 'Todas las OTs'.";
                bodyEl.appendChild(empty);
                return;
            }

            const list = document.createElement("div");
            list.className = "pta-prog-list";
            files.forEach((f) => list.appendChild(renderFileButton(f, selectedPath)));
            bodyEl.appendChild(list);
            return;
        }

        // Tab "Todas las OTs" (strictly inside PROGRAMAS_SOLDADURA_PTA)
        const ots = cachedData.all_ots || [];
        let renderedCount = 0;

        ots.forEach((otGroup) => {
            const matchingClasses = (otGroup.classes || []).map((clsGroup) => {
                const otMatch = otGroup.ot.toLowerCase().includes(term);
                const clsMatch = clsGroup.clase.toLowerCase().includes(term);
                const files = (clsGroup.files || []).filter((f) =>
                    !term || otMatch || clsMatch || f.name.toLowerCase().includes(term)
                );
                return { ...clsGroup, files };
            }).filter((clsGroup) => clsGroup.files.length > 0);

            if (!matchingClasses.length) return;
            renderedCount++;

            const details = document.createElement("details");
            details.className = "pta-prog-ot-details";
            if (otGroup.is_current || term !== "") details.open = true;

            const summary = document.createElement("summary");
            const totalFiles = matchingClasses.reduce((sum, c) => sum + c.files.length, 0);
            summary.textContent = `${otGroup.ot}${otGroup.is_current ? " (OT actual)" : ""} · ${totalFiles} programa(s)`;
            details.appendChild(summary);

            matchingClasses.forEach((clsGroup) => {
                const clsWrap = document.createElement("div");
                clsWrap.className = "pta-prog-cls-group";
                const clsTitle = document.createElement("div");
                clsTitle.className = "pta-prog-cls-title";
                clsTitle.textContent = clsGroup.clase;
                clsWrap.appendChild(clsTitle);

                const list = document.createElement("div");
                list.className = "pta-prog-list";
                clsGroup.files.forEach((f) => list.appendChild(renderFileButton(f, selectedPath)));
                clsWrap.appendChild(list);
                details.appendChild(clsWrap);
            });

            bodyEl.appendChild(details);
        });

        if (renderedCount === 0) {
            const empty = document.createElement("div");
            empty.className = "pta-prog-empty";
            empty.textContent = "No se encontraron programas en PROGRAMAS_SOLDADURA_PTA.";
            bodyEl.appendChild(empty);
        }
    }

    async function loadPrograms() {
        try {
            const url = new URL(listUrl, window.location.origin);
            url.searchParams.set("ot_id", otId);
            if (claseId) url.searchParams.set("clase_id", claseId);
            if (processId) url.searchParams.set("id_proceso", processId);

            const res = await fetch(url, { headers: { Accept: "application/json" } });
            const data = await res.json();
            if (!res.ok || !data.success) throw new Error(data.message || "Error al cargar");
            cachedData = data;
            renderContent();
        } catch (err) {
            bodyEl.innerHTML = `<div class="pta-prog-empty">No se pudieron cargar los programas: ${escapeHtmlPta(err.message)}</div>`;
        }
    }

    modal.querySelectorAll(".pta-prog-tab").forEach((tab) => {
        tab.addEventListener("click", () => {
            modal.querySelectorAll(".pta-prog-tab").forEach((t) => t.classList.remove("active"));
            tab.classList.add("active");
            activeTab = tab.dataset.tab;
            renderContent();
        });
    });

    searchEl.addEventListener("input", renderContent);

    uploadBtnEl.addEventListener("click", () => {
        if (!uploadUrl) return;
        fileInputEl.value = "";
        fileInputEl.click();
    });

    fileInputEl.addEventListener("change", async () => {
        const file = fileInputEl.files?.[0];
        if (!file || !uploadUrl) return;

        uploadBtnEl.disabled = true;
        uploadBtnEl.textContent = "Subiendo...";
        try {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "";
            const fd = new FormData();
            fd.append("ot_id", otId);
            fd.append("clase_id", claseId);
            fd.append("programa", file);

            const res = await fetch(uploadUrl, {
                method: "POST",
                headers: {
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": csrf,
                },
                body: fd,
            });
            const data = await res.json();
            if (!res.ok || !data.success) throw new Error(data.message || "Error al subir el programa.");

            if (typeof window.gisToast === "function") {
                window.gisToast("Programa guardado correctamente.", "success");
            }
            activeTab = "current";
            modal.querySelectorAll(".pta-prog-tab").forEach((t) => t.classList.toggle("active", t.dataset.tab === "current"));
            await loadPrograms();
        } catch (err) {
            if (typeof window.gisToast === "function") {
                window.gisToast(err.message || "Error al subir programa.", "error");
            } else {
                alert(err.message || "Error al subir programa.");
            }
        } finally {
            uploadBtnEl.disabled = false;
            uploadBtnEl.textContent = "+ Subir programa";
        }
    });

    loadPrograms();
};

export {
    insertWeldingTypeBox,
    createPtaPasswordModal
};


