/**
 * Configuración única y declarativa para los botones del Hub de Acciones de Producción.
 * Permite agregar, modificar o reorganizar las acciones (Admin y Documentación) desde un solo punto.
 */

export const ACTIONS_HUB_CONFIG = {
    admin: [
        {
            id: "btn-edit-meta",
            actionKey: "edit-meta",
            label: "Reporte",
            title: "Editar Datos Generales del Reporte",
            icon: () => window.edit,
            imgClass: "img-edit-meta",
            isDisabled: () => false,
            handler: "handleEditMetaClick"
        },
        {
            id: "btn-edit-pieces",
            actionKey: "edit-pieces",
            label: "Editar",
            title: "Editar piezas registradas",
            icon: () => window.imgEditPieces,
            isDisabled: (state) => !state.hasPieces,
            handler: "handleEditPiecesClick"
        },
        {
            id: "btn-quality",
            actionKey: "calidad",
            label: "Calidad",
            title: "Liberación de Calidad",
            icon: () => window.imgQualityCheck,
            isDisabled: (state) => !state.hasPieces,
            handler: "handleQualityClick"
        }
    ],
    docs: [
        {
            id: "btn-programas",
            actionKey: "programas",
            label: "Programas",
            title: "Ver Programas CNC",
            icon: () => window.imgProgramsCNC || (window.baseUrl + '/images/iconos/ProgramsCNC.png'),
            customClass: "btn-programas",
            checkAsyncFiles: true,
            action: () => {
                const activeOT = window.arrayData && window.arrayData.workOrder ? window.arrayData.workOrder : 'Sin OT';
                const activeClase = window.arrayData ? (window.arrayData.class || 'Sin Clase') : 'Sin Clase';
                const activeProceso = window.arrayData ? (window.arrayData.process || 'Sin Proceso') : 'Sin Proceso';
                if (typeof window.logUserAction === 'function') {
                    window.logUserAction("Consulta Programas CNC", `El operador revisó los programas CNC de ${activeOT} - ${activeClase} - ${activeProceso}`);
                }
                if (typeof window.openProgramasViewer === 'function') {
                    window.openProgramasViewer();
                }
            }
        },
        {
            id: "btn-draws",
            actionKey: "draws",
            label: "Dibujos",
            title: "Ver Planos Técnicos",
            icon: () => window.imgDraws || (window.baseUrl + '/images/iconos/DrawsProduction.png'),
            customClass: "btn-draws",
            checkAsyncFiles: true,
            action: () => {
                const activeOT = window.arrayData && window.arrayData.workOrder ? window.arrayData.workOrder : 'Sin OT';
                const activeClase = window.arrayData ? (window.arrayData.class || 'Sin Clase') : 'Sin Clase';
                if (typeof window.logUserAction === 'function') {
                    window.logUserAction("Consulta Dibujos Técnicos", `El operador revisó los planos de ${activeOT} - ${activeClase}`);
                }
                if (typeof window.openDibujosViewer === 'function') {
                    window.openDibujosViewer();
                }
            }
        },
        {
            id: "btn-tech-docs",
            actionKey: "tech-docs",
            label: "Manuales",
            title: "Documentación Técnica / Ayudas Visuales",
            icon: () => window.imgTechDocs || (window.baseUrl + '/images/iconos/manual.png'),
            customClass: "btn-tech-docs",
            action: () => {
                if (typeof window.openTechDocsModal === 'function') {
                    window.openTechDocsModal();
                }
            }
        }
    ]
};
