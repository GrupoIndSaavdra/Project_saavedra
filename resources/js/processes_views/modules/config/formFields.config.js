/**
 * Configuración única y declarativa para los campos del formulario de producción.
 * Permite agregar, remover o cambiar iconos y etiquetas desde un solo punto.
 */

export const formFieldIcons = {
    "Orden de trabajo": "OT-Icon.png",
    "workOrder": "OT-Icon.png",
    "Clase": "Herramientas-Icon.png",
    "class": "Herramientas-Icon.png",
    "Proceso": "Proceso-Icon.png",
    "process": "Proceso-Icon.png",
    "Subproceso": "Revision-Icon.png",
    "subprocess": "Revision-Icon.png",
    "Turno": "Turno-Icon.png",
    "startTime": "Turno-Icon.png",
    "Fecha": "Fecha-Icon.png",
    "date": "Fecha-Icon.png",
    "Máquina": "Maquina-Icon.png",
    "machine": "Maquina-Icon.png",
    "consignmentPieces": "Revision-Icon.png",
    "remainingPieces": "Revision-Icon.png"
};

/**
 * Genera el HTML simétrico con iconos para las etiquetas de los campos
 */
export function getFieldLabelHtml(iconFileName, text) {
    if (!iconFileName) return `<span>${text}</span>`;
    return `<img src="/images/${iconFileName}" class="form-label-icon" alt="${text}"><span>${text}</span>`;
}
