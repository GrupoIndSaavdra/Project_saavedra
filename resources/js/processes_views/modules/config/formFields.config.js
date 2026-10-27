/**
 * Configuración única y declarativa para los campos del formulario de producción.
 * Permite agregar, remover o cambiar iconos y etiquetas desde un solo punto.
 */

export const formFieldIcons = {
    "Orden de trabajo": "Light_Icons/OT-Light.png",
    "workOrder": "Light_Icons/OT-Light.png",
    "Clase": "Light_Icons/Herramientas-Light.png",
    "class": "Light_Icons/Herramientas-Light.png",
    "Proceso": "Light_Icons/Proceso-Light.png",
    "process": "Light_Icons/Proceso-Light.png",
    "Subproceso": "Light_Icons/Revision-Light.png",
    "subprocess": "Light_Icons/Revision-Light.png",
    "Turno": "Light_Icons/Turno-Light.png",
    "startTime": "Light_Icons/Turno-Light.png",
    "Fecha": "Light_Icons/Fecha-Light.png",
    "date": "Light_Icons/Fecha-Light.png",
    "Máquina": "Light_Icons/Maquina-Light.png",
    "machine": "Light_Icons/Maquina-Light.png",
    "consignmentPieces": "Light_Icons/Revision-Light.png",
    "remainingPieces": "Light_Icons/Revision-Light.png"
};

/**
 * Genera el HTML simétrico con iconos para las etiquetas de los campos
 */
export function getFieldLabelHtml(iconFileName, text) {
    if (!iconFileName) return `<span>${text}</span>`;
    const baseUrl = (window.baseUrl || "").replace(/\/$/, "");
    const iconSrc = baseUrl ? `${baseUrl}/images/${iconFileName}` : `/images/${iconFileName}`;
    return `<img src="${iconSrc}" class="form-label-icon" alt="${text}"><span>${text}</span>`;
}
