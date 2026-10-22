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

// 3. Inicialización al cargar el DOM
document.addEventListener("DOMContentLoaded", () => {
    if (window.arrayData && window.arrayData.meta) {
        if (!window.currentPieceStartTime) {
            window.refreshPieceStartTime();
        }
    }
});

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

            createInputsWithValue(window.arrayData, ["startTime", "endTime", "date"]);
            insertWeldingTypeBox();
        } else {
            document.documentElement.classList.add("is-edit-pieces-mode");
            document.body.classList.add("is-edit-pieces-mode");
            createInputsWithValue(window.arrayData);
            insertWeldingTypeBox();
            enableTable();
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
