// ── VER PDF ───────────────────────────────────────────────────────────────────
/**
 * Abre el PDF desde el directorio aislado FUNDICION_ALMACEN en una nueva pestaña.
 *
 * @param {string} ot      - Nombre de la carpeta OT
 * @param {string} archivo - Nombre del archivo PDF
 */
window.calidadVerPdf = function (ot, archivo, tipo = "dibujo") {
    const routesObj = window.calidadRoutes || window.almacenRoutes;
    const url =
        routesObj.serve +
        "?ot=" +
        encodeURIComponent(ot) +
        "&archivo=" +
        encodeURIComponent(archivo) +
        "&tipo=" +
        encodeURIComponent(tipo);
    window.open(url, "_blank", "noopener,noreferrer");
    // Registrar "Visto/Revisado"
    let flagToUpdate = null;
    if (tipo === "dibujo" || tipo === "adicionales" || tipo === "preorden") {
        flagToUpdate = "documentos_revisados_calidad";
    }
    if (flagToUpdate) {
        let otClean = ot.replace(/[^0-9]/g, "");
        fetch((window.baseUrl || "") + "/fundicion/updateFlag", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    ?.getAttribute("content"),
            },
            body: JSON.stringify({ ot: otClean, flag: flagToUpdate }),
        }).catch((err) => console.error("Error actualizando flag visto", err));
    }
};

/**
 * Abre un archivo usando una URL directa (para archivos en endpoints distintos,
 * como ayudas globales en ayudas_fundicion.serve). Si no se pasa urlDirecta,
 * delega a calidadVerPdf.
 *
 * @param {string} urlDirecta - URL directa al endpoint de descarga (puede ser '')
 * @param {string} ot         - OT (usado si urlDirecta está vacía)
 * @param {string} archivo    - Ruta relativa del archivo (usado si urlDirecta está vacía)
 * @param {string} tipo       - Tipo de archivo (usado si urlDirecta está vacía)
 */
window.calidadAbrirArchivo = function (urlDirecta, ot, archivo, tipo = "ayuda") {
    if (urlDirecta && urlDirecta !== "") {
        window.open(urlDirecta, "_blank", "noopener,noreferrer");
    } else {
        window.calidadVerPdf(ot, archivo, tipo);
    }
};

