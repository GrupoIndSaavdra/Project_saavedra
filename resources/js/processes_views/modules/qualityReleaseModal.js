/**
 * Módulo de Liberación de Calidad
 * Modal interactivo de calidad, matriz de piezas, leyenda de estatus y verificación
 */

import { removePasswordForms, changeFormRoute } from './actionsHub.js';
import { createDivOpacity } from './uiUtils.js';

function verifyQualityPasswordAjax(form, imgElement) {
    const formData = new FormData(form);
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute("content");

    fetch(window.baseUrl + "/processProduction/verifyQualityPassword", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": csrfToken,
            "Accept": "application/json"
        },
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.logUserAction("Login Inspector Calidad", "Autenticación correcta: " + data.qualityUser, { h_inicio: window.qualityStartTime });
                // Limpiar UI
                removePasswordForms();
                if (imgElement) imgElement.src = window.imgQualityCheck;

                // Mostrar el modal de liberación de piezas
                showQualityReleaseModal(data.pieces, data.qualityUser);
            } else {
                toastpremium("Contraseña incorrecta", "error");
                window.logUserAction("Error Inspector Calidad", "Autenticación fallida");
            }
        })
        .catch(error => {
            console.error("Error:", error);
            toastpremium("Error al verificar la contraseña. Intente de nuevo.", "error");
        });
}


// Función para obtener la clave de estado de un juego completo
function getStatusKeyForSet(pieces) {
    // Si alguna está rechazada (2)
    if (pieces.some(p => Number(p.liberacion) === 2)) return "rechazado";

    // Si alguna o todas están marcadas como incompleto (5)
    if (pieces.some(p => Number(p.liberacion) === 5)) return "incompleto";

    // Si todas las piezas están liberadas (1)
    if (pieces.every(p => Number(p.liberacion) === 1)) return "liberado";

    // Si alguna está liberada pero no todas (incompleto)
    if (pieces.some(p => Number(p.liberacion) === 1)) return "incompleto";

    // Si todas están sin error y sin liberar
    if (pieces.every(p => (p.error === "Ninguno" || !p.error) && Number(p.liberacion) === 0)) return "buena";

    // Si alguna tiene error y no está liberada
    if (pieces.some(p => p.error !== "Ninguno" && p.error && Number(p.liberacion) === 0)) return "pendiente";

    return "incompleto"; // Incompleto
}

// Función compatible para color hex clásico
function getColorForSet(pieces) {
    const key = getStatusKeyForSet(pieces);
    const colorMap = {
        liberado: "#79BFED",
        rechazado: "#FF6B6B",
        buena: "#90EE90",
        pendiente: "#DDA0DD",
        incompleto: "#FFD700"
    };
    return colorMap[key] || "#FFD700";
}

// Función para obtener el estado más restrictivo de un juego
function getMostRestrictiveStatus(pieces) {
    if (pieces.some(p => Number(p.liberacion) === 2)) return "Rechazado";
    if (pieces.some(p => Number(p.liberacion) === 5)) return "Incompleto";
    if (pieces.every(p => Number(p.liberacion) === 1)) return "Liberado";
    if (pieces.some(p => Number(p.liberacion) === 1)) return "Incompleto";
    return "Sin liberar";
}

// Función para obtener la clave de estado por pieza individual
function getStatusKeyByPiece(liberacion, error) {
    const lib = Number(liberacion);
    if (lib === 1) return "liberado";
    if (lib === 2) return "rechazado";
    if (lib === 5) return "incompleto";
    if ((error === "Ninguno" || !error) && lib === 0) return "buena";
    if (error !== "Ninguno" && error && lib === 0) return "pendiente";
    return "incompleto";
}

function getColorByStatus(liberacion, error) {
    const key = getStatusKeyByPiece(liberacion, error);
    const colorMap = {
        liberado: "#79BFED",
        rechazado: "#FF6B6B",
        buena: "#90EE90",
        pendiente: "#DDA0DD",
        incompleto: "#FFD700"
    };
    return colorMap[key] || "#FFD700";
}

function getStatusText(liberacion) {
    switch (Number(liberacion)) {
        case 1:
            return "Liberado";
        case 2:
            return "Rechazado";
        case 5:
            return "Incompleto";
        default:
            return "Sin liberar";
    }
}

function getStatusSlug(statusText) {
    if (statusText === "Liberado") return "liberado";
    if (statusText === "Rechazado") return "rechazado";
    if (statusText === "Incompleto") return "incompleto";
    return "sin-liberar";
}

// Función para mostrar el modal de liberación de piezas
function showQualityReleaseModal(piecesData, qualityUserName = "") {
    let body = document.querySelector("body");
    
    // Contenedor Portal con optimización anti-lag
    let divOpacity = createDivOpacity();
    divOpacity.className = "div-opacity prod-viewer-portal";
    body.appendChild(divOpacity);

    // Contenedor principal con estilo glassmorphism corporativo
    let modalContainer = document.createElement("div");
    modalContainer.className = "quality-release-modal prod-viewer-modal";

    // ── HEADER FIJO CORPORATIVO ──────────────────────────────────────────
    let headerDiv = document.createElement("div");
    headerDiv.className = "quality-modal-header prod-viewer-header";

    // Botón de cerrar circular integrado
    let divCerrar = document.createElement("div");
    divCerrar.className = "div-cerrar";
    let btnCerrar = document.createElement("button");
    btnCerrar.className = "btn-cerrar btn-cancel-release";
    btnCerrar.type = "button";
    btnCerrar.title = "Cerrar modal";
    btnCerrar.setAttribute("aria-label", "Cerrar");
    btnCerrar.onclick = function (e) {
        e.stopPropagation();
        closeQualityModal(true);
    };

    if (window.cerrarImgUrl) {
        let imgCerrar = document.createElement("img");
        imgCerrar.className = "img-cerrar";
        imgCerrar.src = window.cerrarImgUrl;
        imgCerrar.alt = "Cerrar";
        imgCerrar.onerror = function () {
            imgCerrar.remove();
            btnCerrar.innerHTML = `
                <div class="svg-cerrar-fallback">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </div>
            `;
        };
        btnCerrar.appendChild(imgCerrar);
    } else {
        btnCerrar.innerHTML = `
            <div class="svg-cerrar-fallback">
                <svg viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </div>
        `;
    }
    divCerrar.appendChild(btnCerrar);
    headerDiv.appendChild(divCerrar);

    // Título con icono de calidad a ambos lados
    let titleRow = document.createElement("div");
    titleRow.className = "quality-title-row";

    let titleIconLeft = document.createElement("img");
    titleIconLeft.className = "quality-header-icon quality-header-icon-left";
    titleIconLeft.src = window.imgQualityCheck || (window.baseUrl + "/images/iconos/Quality.png");
    titleIconLeft.alt = "Calidad";
    titleIconLeft.onerror = function () {
        this.src = window.baseUrl + "/images/iconos/Quality.png";
    };

    let titleText = document.createElement("h3");
    titleText.className = "quality-header-title";
    titleText.textContent = "Liberación de Piezas";

    let titleIconRight = document.createElement("img");
    titleIconRight.className = "quality-header-icon quality-header-icon-right";
    titleIconRight.src = window.imgQualityCheck || (window.baseUrl + "/images/iconos/Quality.png");
    titleIconRight.alt = "Calidad";
    titleIconRight.onerror = function () {
        this.src = window.baseUrl + "/images/iconos/Quality.png";
    };

    titleRow.appendChild(titleIconLeft);
    titleRow.appendChild(titleText);
    titleRow.appendChild(titleIconRight);
    headerDiv.appendChild(titleRow);

    // Inspector de Calidad - Sub-barra integrada estilo capsule
    if (qualityUserName) {
        let inspectorBar = document.createElement("div");
        inspectorBar.className = "quality-inspector-bar";

        let badgeRole = document.createElement("span");
        badgeRole.className = "inspector-role-label";
        badgeRole.textContent = "Inspector de Calidad:";

        let badgeName = document.createElement("span");
        badgeName.className = "inspector-name-val";
        badgeName.textContent = qualityUserName;

        inspectorBar.appendChild(badgeRole);
        inspectorBar.appendChild(badgeName);
        headerDiv.appendChild(inspectorBar);
    }

    modalContainer.appendChild(headerDiv);

    // ── CUERPO DEL MODAL (SCROLLABLE) ──────────────────────────────────
    let modalBody = document.createElement("div");
    modalBody.className = "quality-modal-body";

    // Leyenda de colores moderna estilo pastilla / capsule
    let legendContainer = document.createElement("div");
    legendContainer.className = "quality-legend-bar";

    let legendStatuses = [
        { label: "Liberado", dot: "#0284c7" },
        { label: "Rechazado", dot: "#ef4444" },
        { label: "Incompleto", dot: "#f59e0b" },
        { label: "Liberación pendiente", dot: "#a855f7" },
        { label: "Pieza OK (Sin liberar)", dot: "#10b981" }
    ];

    legendStatuses.forEach(item => {
        let legendItem = document.createElement("div");
        legendItem.className = "legend-pill-item";

        let colorDot = document.createElement("span");
        colorDot.className = "color-dot-indicator";
        colorDot.style.backgroundColor = item.dot;

        let colorLabel = document.createElement("span");
        colorLabel.className = "legend-label-text";
        colorLabel.textContent = item.label;

        legendItem.appendChild(colorDot);
        legendItem.appendChild(colorLabel);
        legendContainer.appendChild(legendItem);
    });
    modalBody.appendChild(legendContainer);

    // Crear formulario
    let form = createForm("/processProduction/releasePieces");
    form.className = "form-release-pieces";

    // Input oculto para meta
    let inputMeta = document.createElement("input");
    inputMeta.type = "hidden";
    inputMeta.name = "meta";
    inputMeta.value = window.arrayData["meta"].id;
    form.appendChild(inputMeta);

    // Input oculto para h_inicio_solicitud (Persistencia de tiempo)
    if (window.qualityStartTime) {
        let inputInicio = document.createElement("input");
        inputInicio.type = "hidden";
        inputInicio.name = "h_inicio_solicitud";
        inputInicio.value = window.qualityStartTime;
        form.appendChild(inputInicio);
    }

    // Contenedor de tabla scrolleable
    let tableContainer = document.createElement("div");
    tableContainer.className = "release-table-container";

    // Crear tabla
    let table = document.createElement("table");
    table.className = "release-table";

    // Encabezado de tabla
    let thead = document.createElement("thead");
    let headerRow = document.createElement("tr");
    let headers = ["No. Pieza / Juego", "Proceso", "Error", "Estado Actual", "Acción", "Comentarios"];
    headers.forEach(headerText => {
        let th = document.createElement("th");
        th.textContent = headerText;
        headerRow.appendChild(th);
    });
    thead.appendChild(headerRow);
    table.appendChild(thead);

    // Cuerpo de tabla
    let tbody = document.createElement("tbody");

    if (piecesData && piecesData.length > 0) {
        piecesData.forEach((pieceGroup, index) => {
            let row = document.createElement("tr");
            row.className = "piece-row";

            // Determinar la clave de estado
            let statusKey = pieceGroup.isSet
                ? getStatusKeyForSet(pieceGroup.pieces)
                : getStatusKeyByPiece(pieceGroup.pieces[0].liberacion, pieceGroup.pieces[0].error);

            row.classList.add(`row-status-${statusKey}`);

            // No. Pieza (badge estilizado)
            let tdPiece = document.createElement("td");
            let pieceBadge = document.createElement("span");
            pieceBadge.className = "quality-piece-badge";
            pieceBadge.textContent = pieceGroup.displayName;
            tdPiece.appendChild(pieceBadge);
            row.appendChild(tdPiece);

            // Inputs ocultos para id(s) de pieza(s)
            pieceGroup.pieces.forEach((piece, pieceIdx) => {
                let inputPieceId = document.createElement("input");
                inputPieceId.type = "hidden";
                inputPieceId.name = `pieces[${index}][ids][${pieceIdx}]`;
                inputPieceId.value = piece.id;
                row.appendChild(inputPieceId);
            });

            // Input para indicar si es un juego completo
            let inputIsSet = document.createElement("input");
            inputIsSet.type = "hidden";
            inputIsSet.name = `pieces[${index}][isSet]`;
            inputIsSet.value = pieceGroup.isSet ? "1" : "0";
            row.appendChild(inputIsSet);

            // Proceso
            let tdProcess = document.createElement("td");
            let processSpan = document.createElement("span");
            processSpan.className = "quality-process-name";
            processSpan.textContent = pieceGroup.pieces[0].proceso;
            tdProcess.appendChild(processSpan);
            row.appendChild(tdProcess);

            // Error (con badge informativo de alerta si no es Ninguno)
            let tdError = document.createElement("td");
            let errors = pieceGroup.pieces.map(p => p.error || "Ninguno").filter((v, i, a) => a.indexOf(v) === i);
            let errorText = errors.join(", ");
            let errorBadge = document.createElement("span");
            errorBadge.className = "quality-error-badge " + (errorText === "Ninguno" ? "is-none" : "has-error");
            errorBadge.textContent = errorText;
            tdError.appendChild(errorBadge);
            row.appendChild(tdError);

            // Estado actual (badge redondeado tipo chip)
            let tdStatus = document.createElement("td");
            let statusText = pieceGroup.isSet
                ? getMostRestrictiveStatus(pieceGroup.pieces)
                : getStatusText(pieceGroup.pieces[0].liberacion);
            let statusSlug = getStatusSlug(statusText);

            let statusBadge = document.createElement("span");
            statusBadge.className = `quality-status-badge is-${statusSlug}`;
            statusBadge.textContent = statusText;
            tdStatus.appendChild(statusBadge);
            row.appendChild(tdStatus);

            // Acción (select moderno con chevron personalizado y opciones coloreadas)
            let tdAction = document.createElement("td");
            let selectAction = document.createElement("select");
            selectAction.name = `pieces[${index}][action]`;
            selectAction.className = "action-select";

            let options = [
                { value: "", text: "Selecciona una acción", className: "opt-action-default" },
                { value: "1", text: "Liberar", className: "opt-action-liberar" },
                { value: "2", text: "Rechazar", className: "opt-action-rechazar" },
                { value: "5", text: "Incompleto", className: "opt-action-incompleto" }
            ];

            options.forEach(opt => {
                let option = document.createElement("option");
                option.value = opt.value;
                option.textContent = opt.text;
                if (opt.className) {
                    option.className = opt.className;
                }
                selectAction.appendChild(option);
            });

            // Determinar la acción previa guardada para que se mantenga seleccionada
            let currentAction = "";
            if (pieceGroup.isSet) {
                if (pieceGroup.pieces.some(p => Number(p.liberacion) === 2)) {
                    currentAction = "2";
                } else if (pieceGroup.pieces.some(p => Number(p.liberacion) === 5)) {
                    currentAction = "5";
                } else if (pieceGroup.pieces.every(p => Number(p.liberacion) === 1)) {
                    currentAction = "1";
                } else if (pieceGroup.pieces.some(p => Number(p.liberacion) === 1)) {
                    currentAction = "5";
                }
            } else {
                let lib = Number(pieceGroup.pieces[0].liberacion);
                if (lib === 1 || lib === 2 || lib === 5) {
                    currentAction = String(lib);
                }
            }

            if (currentAction) {
                selectAction.value = currentAction;
                selectAction.classList.add(`action-selected-${currentAction}`);
            }

            selectAction.addEventListener("change", function () {
                this.classList.remove("action-selected-1", "action-selected-2", "action-selected-5");
                if (this.value) {
                    this.classList.add(`action-selected-${this.value}`);

                    // Actualización interactiva en tiempo real del badge y fila
                    let actionVal = Number(this.value);
                    let newText = getStatusText(actionVal);
                    let newSlug = getStatusSlug(newText);
                    statusBadge.className = `quality-status-badge is-${newSlug}`;
                    statusBadge.textContent = newText;

                    row.classList.remove("row-status-liberado", "row-status-rechazado", "row-status-incompleto", "row-status-buena", "row-status-pendiente");
                    let rowKey = actionVal === 1 ? "liberado" : (actionVal === 2 ? "rechazado" : (actionVal === 5 ? "incompleto" : "buena"));
                    row.classList.add(`row-status-${rowKey}`);
                }
            });

            tdAction.appendChild(selectAction);
            row.appendChild(tdAction);

            // Comentarios (textarea con foco azul GIS)
            let tdComments = document.createElement("td");
            let inputComments = document.createElement("textarea");
            inputComments.name = `pieces[${index}][comments]`;
            inputComments.className = "comments-input";
            inputComments.placeholder = "Observaciones...";
            inputComments.rows = 2;

            let existingComments = pieceGroup.pieces
                .map(p => p.comments || p.observacion_liberacion || "")
                .filter(Boolean)
                .join("; ");
            if (existingComments) {
                inputComments.value = existingComments;
            }

            tdComments.appendChild(inputComments);
            row.appendChild(tdComments);

            tbody.appendChild(row);
        });
    } else {
        let row = document.createElement("tr");
        let td = document.createElement("td");
        td.colSpan = 6;
        td.className = "prod-empty-td";
        td.innerHTML = `
            <div class="quality-empty-state">
                <svg class="empty-icon" width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="16" x2="12" y2="12"></line>
                    <line x1="12" y1="8" x2="12.01" y2="8"></line>
                </svg>
                <div class="empty-title">Sin Juegos Disponibles</div>
                <div class="empty-sub">No hay juegos completos disponibles para liberar en este reporte.</div>
            </div>
        `;
        row.appendChild(td);
        tbody.appendChild(row);
    }

    table.appendChild(tbody);
    tableContainer.appendChild(table);
    form.appendChild(tableContainer);

    // Botón de acción primario con icono GIS corporativo
    let buttonContainer = document.createElement("div");
    buttonContainer.className = "quality-button-container";

    let btnAccept = document.createElement("button");
    btnAccept.type = "submit";
    btnAccept.className = "btn-accept-release";

    let btnIcon = document.createElement("img");
    btnIcon.src = window.baseUrl + "/images/iconos/Resultado-Icon.png";
    btnIcon.className = "btn-release-icon";
    btnIcon.alt = "Guardar";

    let btnSpan = document.createElement("span");
    btnSpan.textContent = "Guardar Liberaciones";

    btnAccept.appendChild(btnIcon);
    btnAccept.appendChild(btnSpan);
    buttonContainer.appendChild(btnAccept);

    form.appendChild(buttonContainer);
    modalBody.appendChild(form);
    modalContainer.appendChild(modalBody);

    divOpacity.appendChild(modalContainer);
}

function closeQualityModal(isManual = false) {
    if (isManual) {
        window.logUserAction("Abandono de Liberación", "El usuario canceló y cerró la interfaz de liberación de piezas.", { h_inicio: window.qualityStartTime });
    }

    let divOpacity = document.getElementById("div-opacity");
    if (divOpacity) {
        divOpacity.remove();
    }
    // Restaurar el botón de calidad en el nuevo contenedor
    let imgQuality = document.querySelector('img[alt="Calidad"]');
    if (imgQuality) {
        imgQuality.src = window.imgQualityCheck;
    }
}

// Exponer globalmente para compatibilidad
window.showQualityReleaseModal = showQualityReleaseModal;
window.closeQualityModal = closeQualityModal;
window.verifyQualityPasswordAjax = verifyQualityPasswordAjax;

export {
    showQualityReleaseModal,
    closeQualityModal,
    verifyQualityPasswordAjax,
    getColorForSet,
    getMostRestrictiveStatus,
    getColorByStatus,
    getStatusText
};

