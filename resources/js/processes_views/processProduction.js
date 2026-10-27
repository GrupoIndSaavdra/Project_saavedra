/**
 * resources/js/processes_views/processProduction.js
 * ==============================================================================
 * Master Orchestrator for Process Production
 * Arquitectura Modular ES6:
 *  - modules/config/formFields.config.js : Configuración declarativa de campos e iconos
 *  - modules/config/actionsHub.config.js : Configuración declarativa del Hub de Acciones
 *  - modules/formManager.js              : Gestión del formulario del operador y pickers
 *  - modules/piecesTableManager.js       : Tabla interactiva de piezas y cotas
 *  - modules/actionsHub.js               : Hub de acciones (Admin, Progreso, Docs)
 *  - modules/qualityReleaseModal.js      : Modal de liberación de calidad
 *  - modules/viewersManager.js           : Visores de Planos, CNC y Manuales
 *  - modules/ptaManager.js               : Lógica especial de soldadura PTA
 * ==============================================================================
 */

// 1. Importar Módulos y Componentes
import { Process } from "./Process.js";
import { openModalCalendar, formatDisplayDate } from "../components/modalCmCalendar.js";
import { openModalShiftTime, openModalTime, getAutoDetectedShift, formatDisplayTime, getShiftSummaryText, getShiftTriggerHtml } from "../components/modalCmTime.js";
import { openModalMachine, formatDisplayMachine, getMachinePlaceholderHtml, getMachineTriggerHtml } from "../components/modalCmMachine.js";

import { formFieldIcons, getFieldLabelHtml } from "./modules/config/formFields.config.js";
import { ACTIONS_HUB_CONFIG } from "./modules/config/actionsHub.config.js";

import {
    createSelects,
    validateProductionForm,
    modifySelects,
    disabledSelects,
    createInteractivePickerField,
    getShiftPlaceholderHtml,
    createInteractiveShiftTimeField,
    createInputs,
    createInputsWithValue,
    createBtnSubmit_editMeta,
    insertSelects,
    createInputPassword
} from "./modules/formManager.js";

import {
    createTable,
    createForm,
    insertPrincipalVariables,
    insertTableOnForm,
    insertButton_saveOrChoose,
    insertAvaliablePiecesSelect,
    createDivOpacity,
    showDivAlert,
    cerrarDiv,
    enableTable,
    disabledInputsTable,
    redirectToEndTable,
    createBtnMetaEdit,
    createBtnTechDocs
} from "./modules/piecesTableManager.js";

import {
    insertProductionActions,
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
} from "./modules/actionsHub.js";

import {
    showQualityReleaseModal,
    closeQualityModal,
    verifyQualityPasswordAjax,
    getColorForSet,
    getMostRestrictiveStatus,
    getColorByStatus,
    getStatusText
} from "./modules/qualityReleaseModal.js";

import {
    openDibujosViewer,
    openProgramasViewer,
    openManualesViewer,
    openAyudasViewer,
    openTechDocsModal
} from "./modules/viewersManager.js";

import {
    insertWeldingTypeBox,
    createPtaPasswordModal
} from "./modules/ptaManager.js";

// 2. Variables y utilidades globales
window.currentPieceStartTime = null;

window.refreshPieceStartTime = function () {
    window.currentPieceStartTime = new Date().toLocaleTimeString('it-IT');
    document.querySelectorAll('input[name="h_inicio_solicitud"]').forEach(input => {
        input.value = window.currentPieceStartTime;
    });
};

window.eq = (a, b) => a && b && String(a).toLowerCase() === String(b).toLowerCase();

if (typeof window.toastpremium === 'function') {
    window.mostrarNotificacion = window.toastpremium;
} else if (typeof toastpremium !== 'undefined') {
    window.mostrarNotificacion = toastpremium;
}

// 2.1 Gestión del Modo Zoom y Foco en Tabla (Resolución y prioridad de captura)
function initTableFocusMode() {
    const btnToggle = document.getElementById("btnToggleFocus");

    const getMasZoomUrl = () => window.imgMasZoom || (window.baseUrl ? `${window.baseUrl.replace(/\/$/, '')}/images/Light_Icons/Mas_Zoom-Light.png` : "/images/Light_Icons/Mas_Zoom-Light.png");
    const getMenosZoomUrl = () => window.imgMenosZoom || (window.baseUrl ? `${window.baseUrl.replace(/\/$/, '')}/images/Light_Icons/Menos_Zoom-Light.png` : "/images/Light_Icons/Menos_Zoom-Light.png");

    const checkTableActive = () => {
        const isEditMeta = window.arrayData && window.arrayData["edit"] == 1;
        const hasMeta = window.arrayData && !!window.arrayData.meta;
        if (!hasMeta || isEditMeta) {
            if (btnToggle) btnToggle.style.display = "none";
            return false;
        }

        const tableContainer = document.querySelector(".scrollable-table");
        const table = document.querySelector(".table, .pta-table, .scrollable-table table");
        const formTable = document.querySelector(".form-tablePieces");
        const isActive = !!(tableContainer && (table || formTable));

        if (btnToggle) {
            btnToggle.style.display = isActive ? "inline-flex" : "none";
        }
        return isActive;
    };
    window.checkTableActive = checkTableActive;

    const updateUI = (isFocus) => {
        const isActive = checkTableActive();
        if (!isActive) {
            document.body.classList.remove("table-focus-mode");
            document.documentElement.classList.remove("table-focus-mode");
            return;
        }

        if (isFocus) {
            document.body.classList.add("table-focus-mode");
            document.documentElement.classList.add("table-focus-mode");
            window.scrollTo(0, 0);
            if (btnToggle) {
                const label = btnToggle.querySelector(".btn-focus-label");
                const icon = btnToggle.querySelector("img");
                if (label) label.textContent = "Menos Zoom";
                if (icon) icon.src = getMenosZoomUrl();
                btnToggle.setAttribute("title", "Menos Zoom (Restaurar vista completa)");
            }
        } else {
            document.body.classList.remove("table-focus-mode");
            document.documentElement.classList.remove("table-focus-mode");
            if (btnToggle) {
                const label = btnToggle.querySelector(".btn-focus-label");
                const icon = btnToggle.querySelector("img");
                if (label) label.textContent = "Más Zoom";
                if (icon) icon.src = getMasZoomUrl();
                btnToggle.setAttribute("title", "Más Zoom (Ampliar tabla de captura)");
            }
        }

        // Re-encuadrar la tabla con retardo suave
        setTimeout(() => {
            if (typeof window.redirectToEndTable === "function") {
                window.redirectToEndTable();
            }
        }, 150);
    };

    const toggleMode = (forceState = null) => {
        const currentlyFocus = document.body.classList.contains("table-focus-mode");
        const nextState = forceState !== null ? forceState : !currentlyFocus;
        localStorage.setItem("gis_production_table_focus", nextState ? "1" : "0");
        updateUI(nextState);
    };

    if (btnToggle) {
        btnToggle.addEventListener("click", () => toggleMode());
    }

    const applySavedFocusState = () => {
        if (!checkTableActive()) return;
        const savedPref = localStorage.getItem("gis_production_table_focus");
        let shouldBeFocus = false;
        if (savedPref !== null) {
            shouldBeFocus = savedPref === "1";
        } else if (window.innerHeight <= 768) {
            shouldBeFocus = true;
        }
        updateUI(shouldBeFocus);
    };
    window.applySavedFocusState = applySavedFocusState;

    // Observar inserción de tabla en el DOM para activar botones y restaurar el modo zoom guardado
    let initialFocusRestored = false;
    const observer = new MutationObserver(() => {
        if (checkTableActive()) {
            if (!initialFocusRestored) {
                initialFocusRestored = true;
                applySavedFocusState();
            }
        }
    });
    observer.observe(document.body, { childList: true, subtree: true });

    if (checkTableActive()) {
        initialFocusRestored = true;
        applySavedFocusState();
    } else {
        if (btnToggle) btnToggle.style.display = "none";
    }
}

// 3. Inicialización al cargar el DOM o inmediata si ya está cargado
function bootProductionFocus() {
    if (window.arrayData && window.arrayData.meta) {
        if (!window.currentPieceStartTime) {
            window.refreshPieceStartTime();
        }
        initTableFocusMode();
    }
}

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", bootProductionFocus);
} else {
    bootProductionFocus();
}

// 4. Bloqueo para evitar doble submit
document.addEventListener("submit", (e) => {
    const btn = e.target.querySelector("button[type='submit']");
    if (btn) {
        if (btn.textContent.trim() === "Registrar") {
            if (typeof window.logUserAction === 'function') {
                window.logUserAction("Inicio de Reporte", "El operador inició un nuevo reporte de producción");
            }
        }
        btn.disabled = true;
    }
});

// 5. Flujo Principal de Inicialización del Reporte
if (window.arrayData) {
    if (window.arrayData["edit"]) {
        toggleFinishReportButton(false);

        if (window.arrayData["edit"] == 1) {
            changeFormRoute(
                document.querySelector(".div-table-meta"),
                document.querySelector(".form-principal-data"),
                "/processProduction/editMeta"
            );

            const hasPieces = (window.arrayData["numberPieces"] && window.arrayData["numberPieces"] > 0) ||
                (window.arrayData["machinedPiecesInMeta"] && window.arrayData["machinedPiecesInMeta"].length > 0);

            if (hasPieces) {
                createInputsWithValue(window.arrayData, ["startTime", "endTime", "date"]);
                insertWeldingTypeBox();
            } else {
                insertSelects();
                const submitBtn = document.querySelector(".btn-submit span");
                if (submitBtn) {
                    submitBtn.textContent = "Confirmar";
                }
            }
        } else {
            document.documentElement.classList.add("is-edit-pieces-mode");
            document.body.classList.add("is-edit-pieces-mode");
            createInputsWithValue(window.arrayData);
            insertWeldingTypeBox();
            enableTable();
            if (typeof window.applySavedFocusState === "function") {
                window.applySavedFocusState();
            }
            changeFormRoute(
                document.querySelector(".container-meta"),
                document.querySelector(".form-tablePieces"),
                "/processProduction/editPieces"
            );
            insertProductionActions();
        }
    } else {
        createInputsWithValue(window.arrayData);
        insertWeldingTypeBox();
        addEventToFinishReport();
        insertProductionActions();
        enableTable();
        if (typeof window.applySavedFocusState === "function") {
            window.applySavedFocusState();
        }
    }
} else {
    if (window.workOrders != null) {
        insertSelects();
        addEventToFinishReport();
    } else {
        let body = document.querySelector("body");
        body.appendChild(showDivAlert("Aun no hay ordenes de trabajo disponibles", false));
    }
}
