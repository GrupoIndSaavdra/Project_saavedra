/**
 * Módulo del Hub de Acciones de Producción
 * Tarjeta de 3 columnas (Admin, Progreso, Documentación), barra de progreso y contraseñas supervisor
 */

import { verifyQualityPasswordAjax } from './qualityReleaseModal.js';
import { ACTIONS_HUB_CONFIG } from './config/actionsHub.config.js';
import { createDivOpacity, createInputPassword } from './uiUtils.js';
import { createPtaPasswordModal } from './ptaManager.js';
import { openDibujosViewer, openProgramasViewer, openTechDocsModal } from './viewersManager.js';

function showInlinePasswordForm(type, imgElement = null) {
    // 1. Limpiar cualquier formulario previo y ocultar botón de terminar
    removePasswordForms();
    toggleFinishReportButton(false);

    // 2. Insertar el div-opacity estándar
    let divOpacity = createDivOpacity();
    divOpacity.addEventListener("click", () => {
        removePasswordForms();
    });
    document.querySelector("body").appendChild(divOpacity);

    if (!imgElement) return;

    // 3. Crear formulario dinámico (Nuevo enfoque unificado)
    let form = document.createElement("form");
    form.method = "POST";
    form.className = "form-inline-verification";
    form.action = window.baseUrl + "/processProduction/verified";

    // Campo de accesibilidad para evitar advertencia de Chromium DOM
    let inputUser = document.createElement("input");
    inputUser.type = "text";
    inputUser.name = "username";
    inputUser.value = "admin";
    inputUser.autocomplete = "username";
    inputUser.style.display = "none";
    inputUser.setAttribute("aria-hidden", "true");
    inputUser.tabIndex = -1;
    form.appendChild(inputUser);

    // Agregar Token CSRF
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute("content");
    let inputCSRF = document.createElement("input");
    inputCSRF.type = "hidden";
    inputCSRF.name = "_token";
    inputCSRF.value = csrfToken;
    form.appendChild(inputCSRF);

    if (type === "EditPieces") {
        let editFlag = document.createElement("input");
        editFlag.type = "hidden";
        editFlag.name = "editPieces";
        editFlag.value = "true";
        form.appendChild(editFlag);
    }

    // REGLA DE ORO: INICIO DE RANGO (OPCIÓN 2 - ESPERA SUPERVISOR)
    const h_inicio_solicitud = new Date().toLocaleTimeString('it-IT');
    window.qualityStartTime = h_inicio_solicitud; // Guardar globalmente

    let inputInicio = document.createElement("input");
    inputInicio.type = "hidden";
    inputInicio.name = "h_inicio_solicitud";
    inputInicio.value = h_inicio_solicitud;
    form.appendChild(inputInicio);

    // Badge contextual indicador de la acción
    let badgePill = document.createElement("div");
    badgePill.className = "verify-context-badge";

    let badgeIcon = document.createElement("img");
    badgeIcon.className = "verify-badge-icon";

    let badgeText = document.createElement("span");
    badgeText.className = "verify-badge-text";

    // 4. Crear el grupo de contraseña según el tipo
    let form_group_password;
    if (type === "Calidad") {
        form.classList.add("is-quality-verify");
        badgePill.classList.add("badge-quality");
        badgeIcon.src = window.imgQualityCheck || (window.baseUrl + "/images/iconos/Quality.png");
        badgeIcon.alt = "Calidad";
        badgeIcon.onerror = function () {
            this.src = window.baseUrl + "/images/iconos/Quality.png";
        };
        badgeText.textContent = "Calidad";

        form_group_password = createInputPassword("passwordQuality", "Contraseña de Calidad");
        form.onsubmit = function (e) {
            e.preventDefault();
            window.logUserAction("Intento de Liberación", "Se abrió el formulario de liberación de calidad", { h_inicio: window.qualityStartTime });
            verifyQualityPasswordAjax(form, imgElement);
        };
    } else if (type === "EditMeta") {
        form.classList.add("is-admin-verify");
        badgePill.classList.add("badge-admin");
        badgeIcon.src = window.edit || (window.baseUrl + "/images/iconos/img-edit.png");
        badgeIcon.alt = "Reporte";
        badgeText.textContent = "Reporte";

        window.logUserAction("Solicitud Edición de Reporte", "Se requiere contraseña de administrador para editar metadatos");
        form_group_password = createInputPassword("passwordAdmin", "Contraseña de Administrador");
    } else {
        form.classList.add("is-pieces-verify");
        badgePill.classList.add("badge-pieces");
        badgeIcon.src = window.imgEditPieces || (window.baseUrl + "/images/iconos/editPieces-Icon.png");
        badgeIcon.alt = "Piezas";
        badgeIcon.onerror = function () {
            this.src = window.baseUrl + "/images/iconos/editPieces-Icon.png";
        };
        badgeText.textContent = "Piezas";

        window.logUserAction("Solicitud Edición de Piezas", "Se requiere contraseña de administrador para editar piezas");
        form_group_password = createInputPassword("passwordAdmin", "Contraseña de Administrador");
    }

    badgePill.appendChild(badgeIcon);
    badgePill.appendChild(badgeText);
    form.appendChild(badgePill);
    form.appendChild(form_group_password);

    // 5. Crear barra flotante situada específicamente arriba del botón presionado
    document.body.classList.add("is-verifying-active");

    let floatingBar = document.createElement("div");
    floatingBar.className = "gis-verification-floating-bar";

    // Botón de regresar ligeramente más grande
    let btnBack = document.createElement("img");
    btnBack.src = window.back;
    btnBack.className = "img-action-btn img-back-floating";
    btnBack.title = "Regresar / Cancelar verificación";
    btnBack.alt = "Regresar";
    btnBack.onclick = (e) => {
        e.stopPropagation();
        removePasswordForms();
    };

    floatingBar.appendChild(btnBack);
    floatingBar.appendChild(form);

    // Coordenadas exactas del botón de acción presionado (ligeramente más grande: 68px)
    const rect = imgElement.getBoundingClientRect();
    const btnSize = 68;
    const centerY = rect.top + (rect.height / 2);
    const centerX = rect.left + (rect.width / 2);

    floatingBar.style.position = "fixed";
    floatingBar.style.top = `${centerY - (btnSize / 2)}px`;
    floatingBar.style.left = `${centerX - (btnSize / 2)}px`;
    floatingBar.style.zIndex = "10000030";

    document.body.appendChild(floatingBar);

    // Auto-focus en el input de contraseña para agilidad del usuario
    setTimeout(() => {
        let passInput = form.querySelector(".input-password");
        if (passInput) passInput.focus();
    }, 60);
}

function toggleFinishReportButton(show) {
    let btn_finishReport = document.querySelector(".btn-finishReport");
    if (btn_finishReport) {
        btn_finishReport.classList.toggle("hidden", !(show));
    }
}

function addEventToFinishReport() {
    let btn_finishReport = document.querySelector(".btn-finishReport");
    let reporteTerminado = false; // control de estado

    // Mostrar botón y manejar clic
    btn_finishReport.classList.remove("opacity-0");
    btn_finishReport.addEventListener("click", async function () {
        const confirmResult = await (window.gisConfirm ? window.gisConfirm({
            title: "¿Terminar reporte?",
            text: "¿Estás seguro de que deseas terminar el reporte?",
            icon: "warning",
            confirmButtonText: "Sí, terminar",
            cancelButtonText: "Cancelar",
            confirmButtonColor: "#b30404",
            cancelButtonColor: "#030041",
            reverseButtons: true
        }) : Promise.resolve({ isConfirmed: true }));

        if (confirmResult.isConfirmed) {
            window.logUserAction("Terminar Reporte", "El usuario finalizó su turno");
            reporteTerminado = true; // ✅ permitir salir sin advertencia
            if (window.arrayData) {
                window.location.href =
                    window.baseUrl + "/processProduction/finishReport/" + window.arrayData["meta"].id;
            } else {
                window.location.href = window.baseUrl + "/processProduction/finishReport/0";
            }
        }
    });
}
function changeFormRoute(div, form, route) {
    // Cambiar la ruta del formulario a la de editar
    form.action = window.baseUrl + route;

    // Insertar el input que contiene el id de la meta
    let input_hidden = document.createElement("input");
    input_hidden.type = "hidden";
    input_hidden.name = "meta";
    input_hidden.value = window.arrayData["meta"].id;
    form.appendChild(input_hidden);

    // Crear o vincular el botón de cancelar edición en la barra superior (Header Izquierdo)
    if (window.arrayData["edit"] == 1) {
        let existingHeaderCancel = document.querySelector("header .btn-cancel-report");
        if (existingHeaderCancel) {
            attachCancelEditEvent(existingHeaderCancel);
        } else {
            let btnFinish = document.querySelector(".btn-finishReport");
            let btnCancel = createBtnCancelEdit("btn-cancel-report");
            if (btnFinish && btnFinish.parentNode) {
                btnFinish.parentNode.insertBefore(btnCancel, btnFinish);
            } else {
                let header = document.querySelector("header");
                if (header) {
                    header.prepend(btnCancel);
                } else if (div) {
                    div.appendChild(btnCancel);
                }
            }
        }
    }
}

function attachCancelEditEvent(btn_cancel) {
    btn_cancel.onclick = async function (e) {
        e.preventDefault();
        const confirmResult = await (window.gisConfirm ? window.gisConfirm({
            title: "¿Cancelar?",
            text: "¿Estás seguro de que deseas cancelar?",
            icon: "warning",
            confirmButtonText: "Sí, cancelar",
            cancelButtonText: "Regresar",
            confirmButtonColor: "#b30404",
            cancelButtonColor: "#030041",
            reverseButtons: true
        }) : Promise.resolve({ isConfirmed: true }));

        if (confirmResult.isConfirmed) {
            window.location.href =
                window.baseUrl +
                "/processProduction/format/" +
                window.arrayData["meta"].id +
                "/" +
                window.arrayData["process"] +
                "/0";
        }
    };
}

function createBtnCancelEdit(customClass = "btn-cancel") {
    let btn_cancel = document.createElement("a");
    btn_cancel.href = "#";
    btn_cancel.className = customClass;

    const cancelIconSrc = window.imgCancelar || (window.baseUrl ? `${window.baseUrl.replace(/\/$/, '')}/images/iconos/Cancelar-Icon.png` : "/images/iconos/Cancelar-Icon.png");

    let iconLeft = document.createElement("img");
    iconLeft.src = cancelIconSrc;
    iconLeft.className = "cancel-report-icon";
    iconLeft.alt = "Cancelar";

    let span = document.createElement("span");
    span.textContent = "Cancelar";

    btn_cancel.appendChild(iconLeft);
    btn_cancel.appendChild(span);
    attachCancelEditEvent(btn_cancel);
    return btn_cancel;
}

// --- Centralización de Controles de Producción ---

function renderEditPiecesNotice(warningLabel) {
    let existingNotice = document.querySelector(".edit-pieces-notice-card");
    if (existingNotice) {
        existingNotice.remove();
    }

    const editPiecesIcon = window.imgEditPieces || (window.baseUrl ? `${window.baseUrl.replace(/\/$/, '')}/images/iconos/editPieces-Icon.png` : "/images/iconos/editPieces-Icon.png");

    let card = document.createElement("div");
    card.className = "edit-pieces-notice-card";

    card.innerHTML = `
        <div class="edit-pieces-notice-glow"></div>
        <div class="edit-pieces-notice-header">
            <div class="edit-pieces-header-left">
                <div class="edit-pieces-icon-bubble">
                    <img src="${editPiecesIcon}" class="edit-pieces-card-icon" alt="Edición de Piezas">
                </div>
                <div class="edit-pieces-title-group">
                    <div class="edit-pieces-pill-tag">
                        <span class="edit-pieces-pulse-dot"></span>
                        MODO EDICIÓN ACTIVO
                    </div>
                    <h3 class="edit-pieces-main-title">Edición de Piezas y Cotas de Producción</h3>
                </div>
            </div>
            <div class="edit-pieces-status-badge">
                <span>Modo Especial</span>
            </div>
        </div>
        <div class="edit-pieces-notice-body">
            <p class="edit-pieces-notice-desc">
                Se ha desbloqueado la tabla inferior para que puedas corregir cualquier dimensión o valor capturado. 
                Los cambios no afectarán el histórico hasta que presiones <strong>Guardar</strong>.
            </p>
            <div class="edit-pieces-action-hints">
                <div class="hint-item hint-save">
                    <span class="hint-bullet">✔</span>
                    <span><strong>Guardar:</strong> Confirma las correcciones en el reporte.</span>
                </div>
                <div class="hint-item hint-cancel">
                    <span class="hint-bullet">✖</span>
                    <span><strong>Cancelar:</strong> Descarta las modificaciones y vuelve al modo normal.</span>
                </div>
                <button type="button" class="btn-scroll-to-pieces" onclick="document.querySelector('.scrollable-table')?.scrollIntoView({behavior: 'smooth', block: 'start'})">
                    <span>Ir a la Tabla</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
                </button>
            </div>
        </div>
    `;

    warningLabel.insertAdjacentElement("afterend", card);
}

function insertProductionActions() {
    let warningLabel = document.querySelector(".warning-pieces");
    if (!warningLabel) return;

    // Remover tarjeta previa si existe
    let existingCard = document.querySelector(".production-actions-card");
    if (existingCard) {
        existingCard.remove();
    }
    let existingNotice = document.querySelector(".edit-pieces-notice-card");
    if (existingNotice) {
        existingNotice.remove();
    }

    // MODO EDICIÓN DE PIEZAS: Se renderiza el panel informativo de aviso en el panel central
    if (window.arrayData && window.arrayData["edit"] == 2) {
        renderEditPiecesNotice(warningLabel);
        return;
    }

    // Crear tarjeta/contenedor envolvente para las acciones
    let actionsCard = document.createElement("div");
    actionsCard.className = "production-actions-card";

    // Grid central con 3 secciones simétricas
    let hubGrid = document.createElement("div");
    hubGrid.className = "production-hub-grid";

    // Detectar si hay piezas liberadas (liberacion == 1)
    let hasPieces = window.arrayData && window.arrayData["machinedPiecesInMeta"] &&
        window.arrayData["machinedPiecesInMeta"].length > 0;

    // ==========================================
    // 1. COLUMNA IZQUIERDA: ACCIONES ADMIN
    // ==========================================
    let adminCol = document.createElement("div");
    adminCol.className = "hub-column hub-admin";

    let adminHeader = document.createElement("div");
    adminHeader.className = "hub-section-header";
    adminHeader.textContent = "Admin";
    adminCol.appendChild(adminHeader);

    let adminBtnsRow = document.createElement("div");
    adminBtnsRow.className = "hub-buttons-row";

    // 1.1 Botón Editar Reporte (Meta)
    let btnEditMeta = createActionButton(
        window.edit,
        "Reporte",
        "Editar Datos Generales del Reporte",
        false,
        (e) => handleEditMetaClick(e)
    );
    btnEditMeta.setAttribute("data-action", "edit-meta");
    let imgEditMeta = btnEditMeta.querySelector("img");
    if (imgEditMeta) {
        imgEditMeta.setAttribute("data-action", "edit-meta");
        imgEditMeta.classList.add("img-edit-meta");
    }
    adminBtnsRow.appendChild(btnEditMeta);

    // 1.2 Botón Editar Piezas (Bloqueado si no hay piezas registradas)
    let editDisabled = !hasPieces;
    let btnEditPieces = createActionButton(
        window.imgEditPieces,
        "Editar",
        "Editar piezas registradas",
        editDisabled,
        (e) => handleEditPiecesClick(e)
    );
    btnEditPieces.setAttribute("data-action", "edit-pieces");
    let imgEditPieces = btnEditPieces.querySelector("img");
    if (imgEditPieces) {
        imgEditPieces.setAttribute("data-action", "edit-pieces");
    }
    adminBtnsRow.appendChild(btnEditPieces);

    // 1.3 Botón Calidad (Bloqueado si no hay piezas registradas)
    let qualityDisabled = !hasPieces;
    let btnQuality = createActionButton(
        window.imgQualityCheck,
        "Calidad",
        "Liberación de Calidad",
        qualityDisabled,
        (e) => handleQualityClick(e)
    );
    btnQuality.setAttribute("data-action", "calidad");
    let imgQuality = btnQuality.querySelector("img");
    if (imgQuality) {
        imgQuality.setAttribute("data-action", "calidad");
    }
    adminBtnsRow.appendChild(btnQuality);

    adminCol.appendChild(adminBtnsRow);
    hubGrid.appendChild(adminCol);

    // ==========================================
    // 2. COLUMNA CENTRAL: PROGRESO DE PIEZAS (Card idéntica a OT en Progreso)
    // ==========================================
    let progressCol = document.createElement("div");
    progressCol.className = "hub-column hub-progress";
    progressCol.id = "hub-progress-column";

    let progressContent = document.createElement("div");
    progressContent.className = "hub-progress-content";
    progressCol.appendChild(progressContent);

    hubGrid.appendChild(progressCol);

    // ==========================================
    // 3. COLUMNA DERECHA: DOCUMENTACIÓN / OPERADOR
    // ==========================================
    let operatorCol = document.createElement("div");
    operatorCol.className = "hub-column hub-operator hub-docs";

    let operatorHeader = document.createElement("div");
    operatorHeader.className = "hub-section-header";
    operatorHeader.textContent = "Documentación";
    operatorCol.appendChild(operatorHeader);

    let operatorBtnsRow = document.createElement("div");
    operatorBtnsRow.className = "hub-buttons-row";

    // 3.1 Botón Programas CNC (Se habilita dinámicamente si hay archivos)
    let btnProgramas = createActionButton(
        window.imgProgramsCNC || window.baseUrl + '/images/iconos/ProgramsCNC.png',
        "Programas",
        "Ver Programas CNC",
        true, // Bloqueado por defecto
        () => {
            const activeOT = window.arrayData && window.arrayData.workOrder ? window.arrayData.workOrder : 'Sin OT';
            const activeClase = window.arrayData ? (window.arrayData.class || 'Sin Clase') : 'Sin Clase';
            const activeProceso = window.arrayData ? (window.arrayData.process || 'Sin Proceso') : 'Sin Proceso';
            window.logUserAction("Consulta Programas CNC", `El operador revisó los programas CNC de ${activeOT} - ${activeClase} - ${activeProceso}`);
            if (typeof openProgramasViewer === 'function') openProgramasViewer();
            else if (typeof window.openProgramasViewer === 'function') window.openProgramasViewer();
        }
    );
    btnProgramas.classList.add("btn-programas");
    let imgBtnProgramas = btnProgramas.querySelector('img');
    operatorBtnsRow.appendChild(btnProgramas);

    // Fetch silencioso para verificar si existen archivos .NC/.CNC en la carpeta específica
    const actOT = window.arrayData && window.arrayData.workOrder ? window.arrayData.workOrder : '';
    const actClase = window.arrayData ? window.arrayData.class : '';
    const actProc = window.arrayData ? window.arrayData.process : '';

    if (actOT && actClase && actProc) {
        const checkFilesUrl = `${window.baseUrl}/programas/archivos?ot=${encodeURIComponent(actOT)}&clase=${encodeURIComponent(actClase)}&proceso=${encodeURIComponent(actProc)}`;
        fetch(checkFilesUrl, { headers: { 'Accept': 'application/json' }, cache: 'no-store' })
            .then(r => r.json())
            .then(data => {
                if (data && data.archivos && Array.isArray(data.archivos) && data.archivos.length > 0) {
                    if (imgBtnProgramas) imgBtnProgramas.classList.remove('btn-disabled');
                } else {
                    if (imgBtnProgramas) imgBtnProgramas.classList.add('btn-disabled');
                }
            })
            .catch(() => {
                if (imgBtnProgramas) imgBtnProgramas.classList.add('btn-disabled');
            });
    }

    // 3.2 Botón Dibujos (Siempre habilitado)
    let btnDrawings = createActionButton(
        window.imgDraws,
        "Dibujos",
        "Ver Dibujos/Planos",
        false,
        () => {
            const activeOT = window.arrayData && window.arrayData.workOrder ? window.arrayData.workOrder : 'Sin OT';
            const activeClase = window.arrayData ? (window.arrayData.class || 'Sin Clase') : 'Sin Clase';
            window.logUserAction("Consulta Dibujos Técnicos", `El operador revisó los dibujos técnicos de ${activeOT} - ${activeClase}`);
            if (typeof openDibujosViewer === 'function') openDibujosViewer();
            else if (typeof window.openDibujosViewer === 'function') window.openDibujosViewer();
        }
    );
    btnDrawings.classList.add("btn-drawings");
    operatorBtnsRow.appendChild(btnDrawings);

    // 3.3 Botón Documentos (Manuales y Ayudas Visuales)
    let btnTechDocs = createActionButton(
        window.imgTechDocs,
        "Documentos",
        "Ver Manuales y Ayudas Visuales",
        false,
        () => {
            window.logUserAction("Consulta Documentación", "El operador consultó los manuales y ayudas visuales");
            openTechDocsModal();
        }
    );
    btnTechDocs.classList.add("btn-tech-docs");
    operatorBtnsRow.appendChild(btnTechDocs);

    operatorCol.appendChild(operatorBtnsRow);
    hubGrid.appendChild(operatorCol);

    // Ensamblar tarjeta e insertar después de la advertencia
    actionsCard.appendChild(hubGrid);
    warningLabel.insertAdjacentElement('afterend', actionsCard);

    // Renderizar progreso de piezas en la columna central del hub
    if (window.arrayData && window.arrayData["history"]) {
        createHistoricalTable(window.arrayData["history"]);
    } else {
        renderPlaceholderProgress(progressCol);
    }
}

function handleEditMetaClick(event) {
    let img = event.currentTarget;
    if (!img || img.classList.contains("btn-disabled")) return;

    if (img.src.includes(window.edit.split('/').pop())) {
        showInlinePasswordForm("EditMeta", img);
    } else {
        window.logUserAction("Abandono Edición Reporte", "El usuario canceló la autenticación para editar reporte.");
        removePasswordForms();
        img.src = window.edit;
    }
}

function createActionButton(src, label, title, disabled, callback) {
    let wrapper = document.createElement("div");
    wrapper.className = "action-btn-wrapper";

    let img = document.createElement("img");
    img.src = src;
    img.className = "img-action-btn" + (disabled ? " btn-disabled" : "");
    img.title = title;
    img.alt = label;

    img.onclick = (e) => {
        if (!img.classList.contains('btn-disabled')) {
            callback(e);
        }
    };

    let lbl = document.createElement("div");
    lbl.className = "action-label";
    lbl.textContent = label;

    wrapper.appendChild(img);
    wrapper.appendChild(lbl);
    return wrapper;
}

function handleQualityClick(event) {
    let img = event.currentTarget;
    if (!img || img.classList.contains("btn-disabled")) return;

    if (img.src.includes(window.imgQualityCheck.split('/').pop())) {
        showInlinePasswordForm("Calidad", img);
    } else {
        window.logUserAction("Abandono de Liberación", "El usuario canceló la autenticación de calidad.");
        removePasswordForms();
        img.src = window.imgQualityCheck;
    }
}

function handleEditPiecesClick(event) {
    let img = event.currentTarget;
    if (!img || img.classList.contains("btn-disabled")) return;

    if (img.src.includes(window.imgEditPieces.split('/').pop())) {
        showInlinePasswordForm("EditPieces", img);
    } else {
        removePasswordForms();
        img.src = window.imgEditPieces;
    }
}

function removePasswordForms() {
    // 1. Eliminar barra flotante si existe
    let floatingBar = document.querySelector(".gis-verification-floating-bar");
    if (floatingBar) floatingBar.remove();

    // 2. Eliminar el div de opacidad
    let div_opacity = document.getElementById("div-opacity") || document.querySelector(".div-opacity");
    if (div_opacity) div_opacity.remove();

    // 3. Eliminar el grupo de contraseña si existe (global o local)
    let form_group_password = document.querySelector(".form-group-password");
    if (form_group_password) form_group_password.remove();

    // 3. Restaurar botón de terminar reporte
    toggleFinishReportButton(true);

    // Eliminar formularios en línea inyectados
    let form_inline = document.querySelector(".form-inline-verification");
    if (form_inline) form_inline.remove();

    // 3. Restaurar action y onsubmit del formulario principal
    let form = document.querySelector(".form-verified-password");
    if (form) {
        form.action = window.baseUrl + "/processProduction/verified";
        form.onsubmit = null;
        let editFlag = form.querySelector('input[name="editPieces"]');
        if (editFlag) editFlag.remove();
    }

    // 4. Restaurar contenedores y estados
    document.body.classList.remove("is-verifying-active");
    document.querySelectorAll(".is-verifying").forEach(el => el.classList.remove("is-verifying"));

    // 5. Restaurar iconos de acciones de producción e iconos de edición
    let imgQuality = document.querySelector('img[alt="Calidad"]');
    let imgEditPieces = document.querySelector('img[alt="Editar"]') || document.querySelector('img[data-action="edit-pieces"]');
    let imgEditMeta = document.querySelector('img[alt="Reporte"]') || document.querySelector('img[data-action="edit-meta"]') || document.querySelector('.img-edit-meta') || document.querySelector('.img-edit');

    if (imgQuality) {
        imgQuality.src = window.imgQualityCheck;
        imgQuality.classList.remove("z-11000");
    }
    if (imgEditPieces) {
        imgEditPieces.src = window.imgEditPieces;
        imgEditPieces.classList.remove("z-11000");
    }
    if (imgEditMeta) {
        imgEditMeta.src = window.edit;
        imgEditMeta.classList.remove("z-11000");
    }
}


// Verificar contraseña de calidad mediante AJAX


function createHistoricalTable(history) {
    let targetContainer = document.querySelector(".hub-progress-content") || document.getElementById("hub-progress-column") || document.querySelector(".hub-progress");
    if (!targetContainer) return;

    if (!history || Object.keys(history).length === 0) {
        renderPlaceholderProgress(targetContainer);
        return;
    }

    let consignmentPieces = window.arrayData ? (window.arrayData.consignmentPieces || 0) : 0;

    let currentProcessName = window.arrayData ? window.arrayData.process : 'Sin Proceso';
    if (window.arrayData && window.arrayData.subprocess) {
        currentProcessName += "_" + window.arrayData.subprocess;
    }

    if (currentProcessName === "Soldadura" || currentProcessName === "Soldadura PTA") {
        currentProcessName = "Soldadura y Soldadura PTA";
    }

    let lookupName = currentProcessName;
    let processData = history[lookupName];

    let processIndex = Object.keys(history).indexOf(lookupName) + 1;
    let processNumber = processIndex > 0 ? String(processIndex).padStart(2, '0') : '--';

    let processSection = document.createElement("div");
    processSection.className = "process-section";
    processSection.style.position = "relative";

    // Click para Soldadura PTA
    if (currentProcessName === "Soldadura y Soldadura PTA" && window.arrayData.process === "Soldadura PTA") {
        processSection.style.cursor = "pointer";
        processSection.addEventListener("click", function () {
            let otId = window.arrayData.meta ? window.arrayData.meta.id_ot : null;
            if (otId) {
                createPtaPasswordModal(otId);
            }
        });
    }

    // 1. Badge con número de proceso en la esquina superior izquierda
    let numberBadge = document.createElement("span");
    numberBadge.className = "process-number-badge";
    numberBadge.textContent = String(processNumber).padStart(2, '0');
    processSection.appendChild(numberBadge);

    // 2. Icono de Cotas / Aprobado en la esquina superior derecha
    let cotasBadge = document.createElement("img");
    cotasBadge.className = "cotas-badge";
    const baseUrl = window.baseUrl || (window.location.origin + '/');
    const slash = baseUrl.endsWith('/') ? '' : '/';

    const processesNoCotas = ["Soldadura", "Soldadura PTA", "Asentado", "Rectificado"];
    const isNoCotasProcess = processesNoCotas.includes(window.arrayData ? window.arrayData.process : null);

    let hasCotas = false;
    if (window.arrayData && window.arrayData.process === "Copiado") {
        hasCotas = !!(
            window.arrayData?.cNominals?.Cilindrado?.[0]?.diametro1_cilindrado &&
            window.arrayData?.cNominals?.Cavidades?.[0]?.diametro1_cavidades
        );
    } else {
        hasCotas = !!(window.arrayData && window.arrayData.cNominals);
    }

    if (isNoCotasProcess) {
        cotasBadge.src = `${baseUrl}${slash}images/iconos/Aprobado.png`;
        cotasBadge.title = "No requiere cotas";
        cotasBadge.classList.add("cotas-badge-glow");
    } else if (hasCotas) {
        cotasBadge.src = `${baseUrl}${slash}images/iconos/icono_cotas.png`;
        cotasBadge.title = "Cotas registradas correctamente";
        cotasBadge.classList.add("cotas-badge-glow");
    } else {
        cotasBadge.src = `${baseUrl}${slash}images/iconos/icono_sin_cotas.png`;
        cotasBadge.title = "Falta registrar cotas (Clic para detalles)";
        cotasBadge.style.opacity = "0.7";
        cotasBadge.style.cursor = "pointer";
        cotasBadge.addEventListener("click", (e) => {
            e.stopPropagation();
            if (typeof window.gisAlert === "function") {
                window.gisAlert({
                    title: "¡Sin Cotas Nominales!",
                    text: "Este proceso requiere cotas nominales pero aún no han sido registradas. Notifica a Calidad o Software.",
                    icon: "warning"
                });
            } else if (typeof window.toastpremium === "function") {
                window.toastpremium("Este proceso no cuenta con cotas nominales cargadas.", "warning");
            }
        });
    }
    processSection.appendChild(cotasBadge);

    // 3. Título del proceso centrado
    let processTitle = document.createElement("h3");
    processTitle.className = "process-title";
    processTitle.innerHTML = currentProcessName;
    processSection.appendChild(processTitle);

    // 4. Total disponible
    let limitLabel = document.createElement("label");
    limitLabel.className = "limit-label";
    limitLabel.innerHTML = `Total disponible: ${consignmentPieces}`;
    processSection.appendChild(limitLabel);

    const goodCount = processData ? (processData.pieces.good || 0) : 0;
    const badCount = processData ? (processData.pieces.bad || 0) : 0;

    if (goodCount === 0 && badCount === 0) {
        processSection.classList.add("inactive-process");
    }

    let goodPercentage = consignmentPieces == 0 ? (goodCount > 0 ? 100 : 0) : (goodCount * 100) / consignmentPieces;
    goodPercentage = Math.min(100, goodPercentage);

    let hue = 30 + (goodPercentage * 0.9);
    processSection.style.setProperty('--glow-hue', hue);

    // Borde dinámico según progreso y estado de cotas en el contenedor .hub-progress
    let parentCol = targetContainer.closest(".hub-progress") || targetContainer;
    if (!hasCotas && !isNoCotasProcess) {
        if (parentCol) {
            parentCol.style.borderColor = '#f59e0b';
            parentCol.style.boxShadow = '0 0 12px rgba(245, 158, 11, 0.35), 0 8px 24px rgba(0, 0, 0, 0.4)';
        }
        numberBadge.style.color = '#f59e0b';
        numberBadge.style.borderColor = 'rgba(245, 158, 11, 0.5)';
        numberBadge.style.background = 'rgba(245, 158, 11, 0.12)';
    } else if (goodPercentage >= 100) {
        if (parentCol) {
            parentCol.style.borderColor = '#4ade80';
            parentCol.style.boxShadow = '0 0 14px rgba(74, 222, 128, 0.45), 0 8px 24px rgba(0, 0, 0, 0.4)';
        }
        numberBadge.style.color = '#4ade80';
        numberBadge.style.borderColor = 'rgba(74, 222, 128, 0.5)';
        numberBadge.style.background = 'rgba(74, 222, 128, 0.12)';
    } else if (goodPercentage > 0) {
        if (parentCol) {
            parentCol.style.borderColor = `hsl(${hue}, 100%, 50%)`;
            parentCol.style.boxShadow = `0 0 14px hsla(${hue}, 100%, 50%, 0.35), 0 8px 24px rgba(0, 0, 0, 0.4)`;
        }
        numberBadge.style.color = `hsl(${hue}, 100%, 50%)`;
        numberBadge.style.borderColor = `hsla(${hue}, 100%, 50%, 0.5)`;
        numberBadge.style.background = `hsla(${hue}, 100%, 50%, 0.12)`;
    } else {
        if (parentCol) {
            parentCol.style.borderColor = '#22c55e';
            parentCol.style.boxShadow = '0 0 10px rgba(34, 197, 94, 0.25), 0 8px 24px rgba(0, 0, 0, 0.4)';
        }
        numberBadge.style.color = '#22c55e';
        numberBadge.style.borderColor = 'rgba(34, 197, 94, 0.5)';
        numberBadge.style.background = 'rgba(34, 197, 94, 0.12)';
    }

    // Barras de progreso
    let pieces = [goodCount, badCount];
    for (let i = 0; i < pieces.length; i++) {
        let progressBar = document.createElement("div");
        progressBar.className = "progress-bar";
        progressBar.style.backgroundColor = i == 0 ? "rgba(12, 130, 1, 0.18)" : "rgba(157, 4, 2, 0.18)";

        let progress = document.createElement("div");
        progress.className = i == 0 ? "good-progress progress" : "bad-progress progress";

        let percentage = consignmentPieces > 0 ? (pieces[i] * 100) / consignmentPieces : 0;
        let displayPercentage = percentage;
        percentage = Math.min(100, percentage);

        progress.style.width = `${percentage}%`;

        if (i === 0 && percentage > 0) {
            let barHue = 30 + (percentage * 0.9);
            progress.style.backgroundColor = `hsl(${barHue}, 100%, 32%)`;
        }

        displayPercentage = displayPercentage != 0 ? displayPercentage.toFixed(1) : 0;
        let percentageLabel = document.createElement("div");
        percentageLabel.className = "progress-percentage";
        percentageLabel.innerText = pieces[i] == 1 ? `${displayPercentage}% ${pieces[i]} pieza` : `${displayPercentage}% ${pieces[i]} piezas`;

        progressBar.appendChild(progress);
        progressBar.appendChild(percentageLabel);
        processSection.appendChild(progressBar);
    }

    targetContainer.innerHTML = "";
    targetContainer.appendChild(processSection);
}

function renderPlaceholderProgress(container) {
    let target = container && container.classList.contains("hub-progress-content") 
        ? container 
        : (document.querySelector(".hub-progress-content") || container);
    if (!target) return;
    target.innerHTML = "";

    let currentProcessName = window.arrayData ? (window.arrayData.process || 'Sin Proceso') : 'Sin Proceso';
    let consignmentPieces = window.arrayData ? (window.arrayData.consignmentPieces || 0) : 0;

    let processSection = document.createElement("div");
    processSection.className = "process-section inactive-process";
    processSection.style.position = "relative";

    let numberBadge = document.createElement("span");
    numberBadge.className = "process-number-badge";
    numberBadge.textContent = "--";
    processSection.appendChild(numberBadge);

    let cotasBadge = document.createElement("img");
    cotasBadge.className = "cotas-badge";
    const baseUrl = window.baseUrl || (window.location.origin + '/');
    const slash = baseUrl.endsWith('/') ? '' : '/';
    cotasBadge.src = `${baseUrl}${slash}images/iconos/icono_sin_cotas.png`;
    cotasBadge.title = "Sin cotas registradas";
    cotasBadge.style.opacity = "0.4";
    processSection.appendChild(cotasBadge);

    let processTitle = document.createElement("h3");
    processTitle.className = "process-title";
    processTitle.innerHTML = currentProcessName;
    processSection.appendChild(processTitle);

    let limitLabel = document.createElement("label");
    limitLabel.className = "limit-label";
    limitLabel.innerHTML = `Total disponible: ${consignmentPieces}`;
    processSection.appendChild(limitLabel);

    for (let i = 0; i < 2; i++) {
        let progressBar = document.createElement("div");
        progressBar.className = "progress-bar";
        progressBar.style.backgroundColor = i == 0 ? "rgba(12, 130, 1, 0.15)" : "rgba(157, 4, 2, 0.15)";
        let progress = document.createElement("div");
        progress.className = i == 0 ? "good-progress progress" : "bad-progress progress";
        progress.style.width = "0%";
        let percentageLabel = document.createElement("div");
        percentageLabel.className = "progress-percentage";
        percentageLabel.innerText = "0% 0 piezas";
        progressBar.appendChild(progress);
        progressBar.appendChild(percentageLabel);
        processSection.appendChild(progressBar);
    }

    target.appendChild(processSection);
}



// Exponer globalmente para compatibilidad
window.insertProductionActions = insertProductionActions;
window.createActionButton = createActionButton;
window.showInlinePasswordForm = showInlinePasswordForm;
window.removePasswordForms = removePasswordForms;
window.handleEditMetaClick = handleEditMetaClick;
window.handleQualityClick = handleQualityClick;
window.handleEditPiecesClick = handleEditPiecesClick;
window.toggleFinishReportButton = toggleFinishReportButton;
window.addEventToFinishReport = addEventToFinishReport;
window.createBtnCancelEdit = createBtnCancelEdit;
window.createHistoricalTable = createHistoricalTable;
window.renderPlaceholderProgress = renderPlaceholderProgress;
window.changeFormRoute = changeFormRoute;
window.renderEditPiecesNotice = renderEditPiecesNotice;

export {
    insertProductionActions,
    renderEditPiecesNotice,
    createActionButton,
    showInlinePasswordForm,
    removePasswordForms,
    handleEditMetaClick,
    handleQualityClick,
    handleEditPiecesClick,
    toggleFinishReportButton,
    addEventToFinishReport,
    createBtnCancelEdit,
    attachCancelEditEvent,
    changeFormRoute,
    createHistoricalTable,
    renderPlaceholderProgress
};
