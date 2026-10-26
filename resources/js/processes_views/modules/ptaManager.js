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
        const blockedIconUrl = window.imgBloqueado || (window.baseUrl ? `${window.baseUrl.replace(/\/$/, '')}/images/iconos/bloqueado.png` : "/images/iconos/bloqueado.png");
        badgeHtml = `<span class="locked-field-badge" title="Campo bloqueado - No modificable"><img src="${blockedIconUrl}" class="locked-field-icon" alt="Bloqueado"> Bloqueado</span>`;
    }

    let label = document.createElement("label");
    label.className = "form-label";
    label.setAttribute("for", "tipo_soldadura");
    label.innerHTML = getFieldLabelHtml("Proceso-Icon.png", "Tipo de Soldadura") + badgeHtml;

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

export {
    insertWeldingTypeBox,
    createPtaPasswordModal
};


