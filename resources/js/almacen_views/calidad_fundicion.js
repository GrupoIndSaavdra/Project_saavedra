/**
 * almacen_fundicion.js
 * Lógica de la vista de Almacén/Calidad para Dibujos de Fundición.
 */
console.log("ALMACEN_FUNDICION_JS_V2_LOADED");
function getBaseUrl() {
    let base = window.baseUrl || window.location.origin + "/";
    if (!base.endsWith("/")) base += "/";
    return base;
}
// Helper para notificaciones usando el sistema del layout
function almacenToast(message, type = "success") {
    if (typeof window.mostrarNotificacion === "function") {
        window.mostrarNotificacion(message, type);
    } else if (typeof window.toastpremium === "function") {
        window.toastpremium(message, type);
    } else if (typeof window.showToastAlert === "function") {
        window.showToastAlert(message, type);
    } else {
        alert(message);
    }
}
// Helper para truncar a 3 decimales sin redondear
function truncateToThreeDecimalsJS(val) {
    if (val === null || val === undefined || val === "") return "";
    let valStr = String(val);
    if (valStr.includes(".")) {
        let parts = valStr.split(".");
        let integerPart = parts[0];
        let decimalPart = parts[1].substring(0, 3);
        return integerPart + "." + decimalPart;
    }
    return valStr;
}
function formatInputTruncated(input) {
    let val = input.value;
    if (!val) return;
    let truncated = truncateToThreeDecimalsJS(val);
    if (truncated !== "") {
        let parts = truncated.split(".");
        let integerPart = parts[0];
        let decimalPart = parts[1] || "";
        while (decimalPart.length < 3) {
            decimalPart += "0";
        }
        input.value = integerPart + "." + decimalPart;
    }
}
function initTruncateInputs() {
    document.addEventListener(
        "blur",
        (e) => {
            if (e.target.matches(".lib-num-input, .lib-num-input-sm")) {
                formatInputTruncated(e.target);
            }
        },
        true,
    );
}
document.addEventListener("DOMContentLoaded", () => {
    initToggleFiles();
    initCustomFileInputs();
    initTruncateInputs();
    // Check if we need to open the model pre-order modal after a reload (rejections)
    const otToOpen = sessionStorage.getItem("openPreordenOt");
    if (otToOpen) {
        sessionStorage.removeItem("openPreordenOt");
        setTimeout(() => {
            if (typeof window.abrirModalPreOrden === "function") {
                window.abrirModalPreOrden(otToOpen);
            }
        }, 100);
    }
    // Check if we need to open the casting pre-order modal after a reload
    const otCastingToOpen = sessionStorage.getItem("openCastingOt");
    if (otCastingToOpen) {
        sessionStorage.removeItem("openCastingOt");
        setTimeout(() => {
            if (typeof window.abrirModalPreOrdenCasting === "function") {
                window.abrirModalPreOrdenCasting(otCastingToOpen);
            }
        }, 100);
    }
});
// ── TOGGLE FILAS DE ARCHIVOS ──────────────────────────────────────────────────
function initToggleFiles() {
    document.addEventListener("click", (e) => {
        const btn = e.target.closest(".btn-toggle-files");
        if (!btn) return;
        const targetId = btn.dataset.target;
        const filesRow = document.getElementById(targetId);
        if (!filesRow) return;
        const isOpen = filesRow.classList.contains("open");
        // Cerrar todos los demás antes de abrir el nuevo (Comportamiento de Acordeón)
        if (!isOpen) {
            document.querySelectorAll(".alm-files-row.open").forEach((row) => {
                row.classList.remove("open");
            });
            document
                .querySelectorAll(".btn-toggle-files.active")
                .forEach((b) => {
                    b.classList.remove("active");
                    b.setAttribute("aria-expanded", "false");
                    b.innerHTML = "Ver PDFs";
                });
        }
        if (isOpen) {
            filesRow.classList.remove("open");
            btn.classList.remove("active");
            btn.setAttribute("aria-expanded", "false");
            btn.innerHTML = "Ver PDFs";
        } else {
            filesRow.classList.add("open");
            btn.classList.add("active");
            btn.setAttribute("aria-expanded", "true");
            btn.innerHTML = "Ocultar";
        }
    });
}
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
// ── TOAST NOTIFICACIONES ──────────────────────────────────────────────────────
function mostrarToast(mensaje, esError = false) {
    const prev = document.querySelector(".alm-toast");
    if (prev) prev.remove();
    const toast = document.createElement("div");
    toast.className = "alm-toast " + (esError ? "error" : "success");
    let baseUrl = window.baseUrl || window.location.origin + "/";
    if (!baseUrl.endsWith("/")) baseUrl += "/";
    const iconPath = esError
        ? baseUrl + "images/delete.png"
        : baseUrl + "images/ready.png";
    const iconAlt = esError ? "error" : "success";
    toast.innerHTML = `
<img src="${iconPath}" class="alert-icon-small" alt="${iconAlt}">
<div class="alert-content">
${mensaje}
</div>
<button class="close-alert-x" onclick="this.parentElement.remove()">×</button>
`;
    document.body.appendChild(toast);
    setTimeout(() => {
        if (toast.parentNode) {
            toast.classList.add("fade-out");
            setTimeout(() => {
                if (toast.parentNode) toast.remove();
            }, 450);
        }
    }, 4000);
}
// ── CONTROL DE MODELOS ────────────────────────────────────────────────────────
/**
 * Marca una OT como que ya tiene el modelo físico.
 * @param {string} ot
 */
window.confirmarModelo = function (ot) {
    if (
        !confirm(
            `¿Confirmas que actualmente cuentas con el modelo físico para la OT ${ot}?`,
        )
    )
        return;
    fetch(window.almacenRoutes.confirmarModelo, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content"),
        },
        body: JSON.stringify({ ot }),
    })
        .then((res) => res.json())
        .then((data) => {
            if (data.success) {
                mostrarToast(data.message);
                const container =
                    document.getElementById(`status-modelo-${ot}`) ||
                    document.getElementById(
                        `status-modelo-${ot.replace(/_R\d+$/i, "")}`,
                    );
                const baseUrl = window.baseUrl || window.location.origin + "/";
                if (container) {
                    container.innerHTML = `
<div class="status-modelo-container" style="display: inline-flex; flex-direction: column; align-items: center; gap: 2px; padding: 6px; border-radius: 8px;">
<span class="badge-modelo-icon" title="Modelo físico disponible en Almacén, en espera de revisión por Calidad" style="display: flex; align-items: center; justify-content: center; width: 52px; height: 52px; border-radius: 50%; background: #f0f9ff; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); border: 2px solid #0ea5e9; transition: all 0.2s ease;">
<img src="${baseUrl}${baseUrl.endsWith("/") ? "" : "/"}images/Espera.png" alt="Tengo Modelo" style="width: 34px; height: 34px; object-fit: contain;">
</span>
<span class="status-modelo-label" style="font-size: 11px; font-weight: 700; color: #0369a1; margin-top: 4px; text-transform: uppercase; white-space: nowrap;">
Tengo Modelo
</span>
</div>
`;
                }
            } else {
                mostrarToast(
                    data.message || "Error al actualizar estado del modelo",
                    true,
                );
            }
        })
        .catch((err) => {
            console.error(err);
            mostrarToast("Error de conexión", true);
        });
};
// ── MODAL PRE-ORDEN ───────────────────────────────────────────────────────────
let availableClasses = []; // Caché de clases para las filas nuevas
let optionsHtmlCache = ""; // Caché del HTML de las opciones para evitar reconstruir en cada fila
const normalizeStr = (str) => {
    if (!str) return "";
    return str
        .toLowerCase()
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .trim();
};
// ── Apertura / Cierre del modal ──
window.abrirModalPreOrden = function (ot) {
    const modal = document.getElementById("modalPreOrden");
    const inputOt = document.getElementById("po-ot");
    const inputMoldura = document.getElementById("po-moldura");
    const tbody = document.getElementById("alm-tbody-preorden");
    window.currentFechaEntrega = "";
    window.predefinedCycleObs = "";
    const prefixDiv = document.getElementById("po-observaciones-cycle-prefix");
    if (prefixDiv) {
        prefixDiv.classList.add("cal-display-none");
        prefixDiv.textContent = "";
    }
    // Mostrar/ocultar badge de ciclo de re-fabricación
    const cycleMatch = ot.match(/_R(\d+)$/i);
    const cycleBadge = document.getElementById("po-modal-cycle-badge");
    if (cycleBadge) {
        if (cycleMatch) {
            cycleBadge.textContent = `Ciclo: R${cycleMatch[1]}`;
            cycleBadge.classList.remove("cal-display-none");
        } else {
            cycleBadge.classList.add("cal-display-none");
        }
    }
    // Resetear estado multi-orden (botón añadir siempre visible)
    resetMultiOrderState();
    // Separar OT y Moldura si vienen juntas (ej. "6473 - VINERA...")
    let otNum = ot;
    let molduraName = "";
    if (ot.includes(" - ")) {
        const parts = ot.split(" - ");
        otNum = parts[0].trim();
        molduraName = parts
            .slice(1)
            .join(" - ")
            .trim()
            .replace(/_\d{8}_\d{6}_.*/, "");
    }
    // Dejar solo los números de la OT (por ejemplo, "OT 6748" pasa a "6748")
    otNum = otNum.split("_")[0].replace(/[^0-9]/g, "");
    inputOt.value = otNum;
    document.getElementById("po-ot-raw").value = ot;
    if (molduraName) inputMoldura.value = molduraName;
    tbody.innerHTML =
        '<tr><td colspan="6" style="text-align:center; padding:20px;"><div class="alm-spinner"></div> Cargando datos de la OT...</td></tr>';
    modal.classList.add("open");
    document.body.classList.add("modal-open");
    // Cargar datos de la OT (Moldura y Clases activas)
    fetch(`${window.almacenRoutes.getOtData}?ot=${ot}`)
        .then((res) => res.json())
        .then((data) => {
            if (data.success) {
                inputMoldura.value = data.moldura || "N/A";
                availableClasses = data.clases || [];
                availableClasses.forEach((c) => {
                    c._norm = normalizeStr(c.nombre);
                });
                if (data.clases_vinculadas) {
                    data.clases_vinculadas.forEach((cv) => {
                        const cvNorm = normalizeStr(cv);
                        const found = availableClasses.find(
                            (ac) =>
                                ac._norm === cvNorm ||
                                ac._norm.includes(cvNorm) ||
                                cvNorm.includes(ac._norm),
                        );
                        if (!found) {
                            availableClasses.push({
                                id: `manual_${cv}`,
                                nombre: cv,
                                _norm: cvNorm,
                            });
                        }
                    });
                }
                optionsHtmlCache =
                    '<option value="">Selecciona clase</option>' +
                    availableClasses
                        .map(
                            (c) =>
                                `<option value="${c.id}" data-nombre="${c.nombre}">${c.nombre}</option>`,
                        )
                        .join("");
                if (data.folio)
                    document.getElementById("po-folio").value = data.folio;
                tbody.innerHTML = "";
                if (data.pre_orden_data) {
                    const pod = data.pre_orden_data;
                    window.currentFechaEntrega = pod.fecha_entrega
                        ? pod.fecha_entrega.split(" ")[0]
                        : "";
                    if (pod.fecha_creacion) {
                        const dateOnly = pod.fecha_creacion.split(" ")[0];
                        document.getElementById("po-fecha").value = dateOnly;
                    }
                    // Para OTs de re-proceso (_R1, _R2...), generar una observación descriptiva
                    const obsField =
                        document.getElementById("po-observaciones");
                    if (obsField) {
                        if (cycleMatch) {
                            const cycle = parseInt(cycleMatch[1], 10);
                            const ordinal =
                                cycle === 1
                                    ? "2.ª"
                                    : cycle === 2
                                      ? "3.ª"
                                      : `${cycle + 1}.ª`;
                            window.predefinedCycleObs = `[${ordinal} vuelta — Ciclo R${cycle} de liberación de modelo y fabricación de casting]`;
                            const prefixDiv = document.getElementById(
                                "po-observaciones-cycle-prefix",
                            );
                            if (prefixDiv) {
                                prefixDiv.textContent =
                                    window.predefinedCycleObs;
                                prefixDiv.classList.remove("cal-display-none");
                            }
                            // Si la observación existente tiene el prefijo de ciclo o de RECHAZO, limpiarlo
                            let rawObs = (pod.observaciones || "")
                                .replace(/^RECHAZO:\s*/i, "")
                                .trim();
                            rawObs = rawObs
                                .replace(
                                    /^\[.*? vuelta — Ciclo R\d+ de liberación de modelo y fabricación de casting\]\s*/i,
                                    "",
                                )
                                .trim();
                            obsField.value = rawObs;
                        } else if (pod.observaciones) {
                            obsField.value = pod.observaciones;
                        }
                    }
                    if (pod.proveedor)
                        document.getElementById("po-proveedor").value =
                            pod.proveedor;
                    if (pod.filas && pod.filas.length > 0) {
                        const fragment = document.createDocumentFragment();
                        pod.filas.forEach((rowObj) => {
                            const claseId = rowObj.id_clase || rowObj.clase_id;
                            fragment.appendChild(
                                createRowElement("", false, {
                                    tipo_modelo: rowObj.tipo_modelo,
                                    impresiones: rowObj.impresiones,
                                    cantidad: rowObj.cantidad,
                                    clase_id: claseId,
                                    codigo_modelo: rowObj.codigo_modelo,
                                }),
                            );
                        });
                        tbody.appendChild(fragment);
                    } else {
                        tbody.appendChild(createRowElement());
                    }
                } else {
                    // Sin pre-orden existente — si es re-proceso, poner observación predeterminada
                    if (cycleMatch) {
                        const cycle = parseInt(cycleMatch[1], 10);
                        const ordinal =
                            cycle === 1
                                ? "2.ª"
                                : cycle === 2
                                  ? "3.ª"
                                  : `${cycle + 1}.ª`;
                        window.predefinedCycleObs = `[${ordinal} vuelta — Ciclo R${cycle} de liberación de modelo y fabricación de casting]`;
                        const prefixDiv = document.getElementById(
                            "po-observaciones-cycle-prefix",
                        );
                        if (prefixDiv) {
                            prefixDiv.textContent = window.predefinedCycleObs;
                            prefixDiv.classList.remove("cal-display-none");
                        }
                        const obsField =
                            document.getElementById("po-observaciones");
                        if (obsField) {
                            obsField.value = "";
                        }
                    }
                    if (
                        data.clases_vinculadas &&
                        data.clases_vinculadas.length > 0
                    ) {
                        const fragment = document.createDocumentFragment();
                        data.clases_vinculadas.forEach((claseNombre) => {
                            fragment.appendChild(createRowElement(claseNombre));
                        });
                        tbody.appendChild(fragment);
                        syncClassOptions("alm-tbody-preorden");
                    } else {
                        tbody.appendChild(createRowElement());
                    }
                }
                // Bloque 3a: Aplicar bloqueo de Impresiones a todas las filas ya cargadas
                // (Molde y Bombillo siempre N/A sin importar el origen de los datos)
                setTimeout(
                    () =>
                        aplicarBloqueoImpresionesEnTodas("alm-tbody-preorden"),
                    0,
                );
                // Datos cargados con éxito
            } else {
                mostrarToast(
                    data.message || "Error al cargar datos de la OT",
                    true,
                );
                cerrarModalPreOrden();
            }
        })
        .catch((err) => {
            console.error(err);
            mostrarToast("Error al obtener datos", true);
        });
};
window.cerrarModalPreOrden = function () {
    const modal = document.getElementById("modalPreOrden");
    modal.classList.remove("open");
    document.body.classList.remove("modal-open");
    document.getElementById("formPreOrden").reset();
    document.getElementById("alm-tbody-preorden").innerHTML = "";
    optionsHtmlCache = "";
    window.currentFechaEntrega = "";
    resetMultiOrderState();
};
function resetMultiOrderState() {
    // No-op para mantener compatibilidad
}
// ── Creación de filas de la tabla ──
/**
 * Crea un elemento TR para la tabla de pre-orden.
 * @param {string} claseNombrePredefinida - Nombre de clase a preseleccionar.
 * @param {boolean} isSecondOrder - Si es para la tabla 2, usa el tbody2.
 */
function createRowElement(
    claseNombrePredefinida = "",
    isSecondOrder = false,
    rowObj = null,
) {
    const tr = document.createElement("tr");
    const fechaEntregaVal =
        rowObj && rowObj.fecha_entrega
            ? rowObj.fecha_entrega
            : window.currentFechaEntrega || "";
    let selectedId = "";
    if (rowObj && rowObj.clase_id) {
        selectedId = rowObj.clase_id;
    } else if (claseNombrePredefinida) {
        const search = normalizeStr(claseNombrePredefinida);
        const found = availableClasses.find(
            (c) =>
                c._norm === search ||
                c._norm.includes(search) ||
                search.includes(c._norm),
        );
        if (found) selectedId = found.id;
    }
    let options = optionsHtmlCache;
    if (selectedId) {
        options =
            '<option value="">Selecciona clase</option>' +
            availableClasses
                .map((c) => {
                    const selectedAttr = selectedId == c.id ? "selected" : "";
                    return `<option value="${c.id}" data-nombre="${c.nombre}" ${selectedAttr}>${c.nombre}</option>`;
                })
                .join("");
    }
    const deleteHandler = isSecondOrder
        ? "eliminarFilaPreOrden2(this)"
        : "eliminarFilaPreOrden(this)";
    const tipoVal = rowObj && rowObj.tipo_modelo ? rowObj.tipo_modelo : "";
    const impresionesVal =
        rowObj && rowObj.impresiones ? rowObj.impresiones : "";
    const cantidadVal = rowObj && rowObj.cantidad ? rowObj.cantidad : "";
    const codigoVal =
        rowObj && rowObj.codigo_modelo ? rowObj.codigo_modelo : "";
    tr.innerHTML = `
<td>
<select name="tipo_modelo[]" class="form-control po-tipo-select" required onchange="generarCodigoFila(this.closest('tr').querySelector('.po-clase-select'))">
<option value="" disabled ${!tipoVal ? "selected" : ""}>Selecciona uno</option>
<option value="Suelto" ${tipoVal === "Suelto" ? "selected" : ""}>Suelto</option>
<option value="Placa" ${tipoVal === "Placa" ? "selected" : ""}>Placa</option>
<option value="Templadera" ${tipoVal === "Templadera" ? "selected" : ""}>Templadera</option>
</select>
</td>
<td>
<input type="text" name="impresiones[]" class="form-control po-impresiones" style="text-align:center;" placeholder="Ej. 1" required value="${impresionesVal}">
</td>
<td>
<input type="number" name="cantidad[]" class="form-control" style="text-align:center;" min="1" placeholder="0" required value="${cantidadVal}">
</td>
<td>
<select name="id_clase[]" class="form-control po-clase-select" required onchange="generarCodigoFila(this); actualizarInputImpresiones(this);">
${options}
</select>
</td>
<td>
<input type="text" name="codigo_modelo[]" class="form-control po-codigo-input" value="${codigoVal}">
</td>
<td>
<input type="date" name="fecha_entrega_rows[]" class="form-control po-fecha-entrega-row" readonly value="${fechaEntregaVal}" style="text-align:center; background-color: #f1f5f9; color: #64748b;">
</td>
<td style="text-align:center;">
<button type="button" class="btn-img-action" onclick="${deleteHandler}" title="Quitar esta clase de la lista">
<img src="${window.baseUrl || ""}${(window.baseUrl || "").endsWith("/") ? "" : "/"}images/quitar.png" alt="Quitar" style="width: 30px;">
</button>
</td>
`;
    const select = tr.querySelector(".po-clase-select");
    if (!rowObj && select.value) {
        const ot = document.getElementById("po-ot").value;
        const nombreClase =
            select.options[select.selectedIndex].dataset.nombre ||
            select.options[select.selectedIndex].text;
        tr.querySelector(".po-codigo-input").value = calculateModelCode(
            ot,
            nombreClase,
        );
    }
    // Bloque 3a: Si la fila ya tiene clase seleccionada (carga de datos existentes),
    // aplicar el bloqueo de impresiones si aplica
    if (select.value) {
        setTimeout(() => window.actualizarInputImpresiones(select), 0);
    }
    return tr;
}
window.agregarFilaPreOrden = function () {
    document
        .getElementById("alm-tbody-preorden")
        .appendChild(createRowElement());
    syncClassOptions("alm-tbody-preorden");
};
window.eliminarFilaPreOrden = function (btn) {
    const row = btn.closest("tr");
    const tbody = document.getElementById("alm-tbody-preorden");
    if (tbody.rows.length > 1) {
        row.remove();
        // No llamamos a syncClassOptions: las opciones ya no se filtran
    } else {
        mostrarToast("Debe haber al menos una clase en la pre-orden", true);
    }
};
// ── Cálculo de códigos ──
/**
 * Función centralizada para calcular el código de modelo
 */
function calculateModelCode(ot, nombreClase, tipoModelo = "") {
    const siglas = {
        Corona: "C",
        "Cabeza de Soplo": "CS",
        "Candado Obturador": "CO",
        Obturador: "O",
        Bombillo: "B",
        Molde: "M",
        Fondo: "F",
        Guía: "G",
        Guias: "G",
        Guías: "G",
        Pistón: "P",
        Pistones: "P",
        Plato: "PL",
    };
    const matches = ot.match(/\d+/);
    const otNum = matches ? matches[0] : ot;
    const sigla = siglas[nombreClase] || "X";
    // Regla especial: Templadera → prefijo T en cualquier clase
    if (tipoModelo === "Templadera") {
        return `T${sigla}${otNum}`;
    }
    return `${sigla}${otNum}`;
}
window.generarCodigoFila = function (select) {
    const row = select.closest("tr");
    const inputCodigo = row.querySelector(".po-codigo-input");
    const tipoSelect = row.querySelector(".po-tipo-select");
    const ot = document.getElementById("po-ot").value;
    const tipoModelo = tipoSelect ? tipoSelect.value : "";
    if (!select.value) {
        inputCodigo.value = "";
    } else {
        const nombreClase =
            select.options[select.selectedIndex].dataset.nombre ||
            select.options[select.selectedIndex].text;
        inputCodigo.value = calculateModelCode(ot, nombreClase, tipoModelo);
    }
    // No se llama a syncClassOptions: las opciones no se filtran
};
/**
 * Sincroniza las opciones de todos los selects de un tbody.
 * En esta versión NO se filtran/ocultan clases: todas las opciones
 * permanecen disponibles para que el usuario pueda elegirlas libremente.
 * @param {string} tbodyId - ID del tbody (mantenido para compatibilidad)
 */
function syncClassOptions(tbodyId) {
    // Lógica de exclusión desactivada por requerimiento.
    // Todas las opciones quedan visibles en cada select.
}
// ── Modales de confirmación (ELIMINADOS) ──
/**
 * Construye el payload de una forma a partir del tbody y los campos del form.
 */
function buildPayload(tbodyId, formIds) {
    const rows = [];
    const tbody = document.getElementById(tbodyId);
    for (let i = 0; i < tbody.rows.length; i++) {
        const row = tbody.rows[i];
        const classSelect = row.querySelector('[name="id_clase[]"]');
        const tipoSelect = row.querySelector('[name="tipo_modelo[]"]');
        const rawClassName =
            classSelect.options[classSelect.selectedIndex].text;
        const tipoModelo = tipoSelect ? tipoSelect.value : "";
        // Si el tipo es Templadera, usar ese prefijo en la descripción; si no, usar "Modelo"
        const prefijo = tipoModelo === "Templadera" ? "Templadera" : "Modelo";
        const claseNombreFinal = rawClassName.startsWith(prefijo + " ")
            ? rawClassName
            : `${prefijo} ${rawClassName}`;
        rows.push({
            tipo_modelo: tipoModelo,
            impresiones: row.querySelector('[name="impresiones[]"]').value,
            cantidad: row.querySelector('[name="cantidad[]"]').value,
            id_clase: classSelect.value,
            clase_nombre: claseNombreFinal,
            codigo_modelo: row.querySelector('[name="codigo_modelo[]"]').value,
        });
    }
    // Limpiar OT: solo el número (quitar prefijos "OT ", espacios, etc.)
    const otRaw = document.getElementById(formIds.ot).value;
    const otClean = otRaw.replace(/[^0-9]/g, "") || otRaw;
    return {
        proveedor: document.getElementById(formIds.proveedor).value,
        fecha_creacion: document.getElementById(formIds.fecha_creacion).value,
        folio: document.getElementById(formIds.folio).value,
        ot: otClean,
        ot_raw: document.getElementById("po-ot-raw").value,
        moldura: document.getElementById(formIds.moldura).value,
        fecha_entrega: "", // Se deja vacío para llenado manual del proveedor
        observaciones: (() => {
            const userObs = document
                .getElementById(formIds.observaciones)
                .value.trim();
            if (window.predefinedCycleObs) {
                return (
                    window.predefinedCycleObs + (userObs ? "\n" : "") + userObs
                );
            }
            return userObs;
        })(),
        filas: rows,
    };
}
/**
 * Dispara el fetch de guardado de una pre-orden y maneja la respuesta.
 */
function submitPreOrden(payload, btn, originalText, onSuccess) {
    fetch(window.almacenRoutes.storePreOrden, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content"),
        },
        body: JSON.stringify(payload),
    })
        .then(async (res) => {
            const contentType = res.headers.get("content-type");
            if (contentType && contentType.includes("application/pdf")) {
                const blob = await res.blob();
                // Intentar extraer el nombre del header Content-Disposition
                const disposition = res.headers.get("Content-Disposition");
                let filename = "";
                if (disposition && disposition.indexOf("attachment") !== -1) {
                    const filenameRegex =
                        /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                    const matches = filenameRegex.exec(disposition);
                    if (matches != null && matches[1]) {
                        filename = matches[1].replace(/['"]/g, "");
                    }
                }
                return { blob, filename };
            }
            return res.json();
        })
        .then((data) => {
            if (data.blob) {
                const url = window.URL.createObjectURL(data.blob);
                const a = document.createElement("a");
                a.href = url;
                a.download =
                    data.filename ||
                    `PreOrden_${payload.ot.replace(/[^a-z0-9]/gi, "_")}.pdf`;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                mostrarToast("Pre-orden generada y descargada correctamente.");
                cerrarModalPreOrden();
                // Recargar para actualizar los estados y la lista de archivos
                setTimeout(() => {
                    window.location.reload();
                }, 200);
                if (onSuccess) onSuccess(data);
            } else if (data.success) {
                mostrarToast(data.message + ". Actualizando...");
                setTimeout(() => {
                    window.location.reload();
                }, 200);
                if (onSuccess) onSuccess();
            } else {
                mostrarToast(
                    data.message || "Error al procesar Pre-Orden",
                    true,
                );
            }
        })
        .catch((err) => {
            console.error(err);
            mostrarToast("Error al procesar la solicitud", true);
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerText = originalText;
        });
}
// ── Envío Pre-Orden 1 ──
document
    .getElementById("formPreOrden")
    ?.addEventListener("submit", function (e) {
        e.preventDefault();
        const btn = document.getElementById("btn-submit-preorden");
        if (!btn) return;
        const originalText = btn.innerText;
        btn.disabled = true;
        btn.innerText = "Procesando...";
        const payload = buildPayload("alm-tbody-preorden", {
            proveedor: "po-proveedor",
            fecha_creacion: "po-fecha",
            folio: "po-folio",
            ot: "po-ot",
            moldura: "po-moldura",
            fecha_entrega: "po-fecha-entrega",
            observaciones: "po-observaciones",
        });
        submitPreOrden(payload, btn, originalText, () => {
            cerrarModalPreOrden();
        });
    });
// ── Envío Pre-Orden 2 (ELIMINADO) ──
function updateModelStatusUI(ot, status) {
    const container =
        document.getElementById(`status-modelo-${ot}`) ||
        document.getElementById(`status-modelo-${ot.replace(/_R\d+$/i, "")}`);
    if (!container) return;
    let icon = "Recibido.png";
    let label = "Recibido";
    let tooltip =
        "Alerta inicial recibida, pendiente de procesar modelo por Almacén";
    let borderColor = "#cbd5e1";
    let bgColor = "#f1f5f9";
    let textColor = "#64748b";
    if (status === "pendiente") {
        icon = "Espera.png";
        label = "Tengo Modelo";
        tooltip =
            "Modelo físico disponible en Almacén, en espera de revisión por Calidad";
        borderColor = "#0ea5e9";
        bgColor = "#f0f9ff";
        textColor = "#0369a1";
    } else if (status === "ok") {
        icon = "Quality.png";
        label = "Aprobado";
        tooltip = "Modelo aprobado y liberado por Calidad";
        borderColor = "#10b981";
        bgColor = "#ecfdf5";
        textColor = "#047857";
    }
    const baseUrl = window.baseUrl || window.location.origin + "/";
    const imgUrl =
        baseUrl + (baseUrl.endsWith("/") ? "" : "/") + "images/" + icon;
    container.innerHTML = `
<div class="status-modelo-container" style="display: inline-flex; flex-direction: column; align-items: center; gap: 2px; padding: 6px; border-radius: 8px;">
<span class="badge-modelo-icon" title="${tooltip}" style="display: flex; align-items: center; justify-content: center; width: 52px; height: 52px; border-radius: 50%; background: ${bgColor}; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); border: 2px solid ${borderColor}; transition: all 0.2s ease;">
<img src="${imgUrl}" alt="${label}" style="width: 34px; height: 34px; object-fit: contain;">
</span>
<span class="status-modelo-label" style="font-size: 11px; font-weight: 700; color: ${textColor}; margin-top: 4px; text-transform: uppercase; white-space: nowrap;">
${label}
</span>
</div>
`;
}
// ── FASE 2: ENVÍO DE CORREO ──
let adicionalesSelectedFiles = [];
let alFotosSelectedFiles = [];
let envScarSelectedFiles = [];
let alAdicionalesSelectedFiles = [];
let cmConfirmarSelectedFiles = [];
let scarFotosSelectedFiles = [];
let scarOtrosSelectedFiles = [];
let micAdicionalesSelectedFiles = [];
window.micRequiredClasses = [];
function generarHtmlCategorizadoArchivos(archivos, ot, baseUrl, inputNameMode) {
    let dibujosPdfs = [];
    let ayudasPdfs = [];
    let aprobadosPdfs = [];
    let rechazadosPdfs = [];
    let otrosPdfs = [];
    if (Array.isArray(archivos)) {
        archivos.forEach((f) => {
            const ext = f.nombre.split(".").pop().toLowerCase();
            if (
                ["pdf", "png", "jpg", "jpeg", "gif", "webp", "bmp"].includes(
                    ext,
                )
            ) {
                if (f.tipo === "dibujo") {
                    dibujosPdfs.push(f);
                } else if (f.tipo === "ayuda") {
                    ayudasPdfs.push(f);
                } else {
                    const lower = f.nombre.toLowerCase();
                    if (lower.includes("escaneado_fundicion")) {
                        aprobadosPdfs.push(f);
                    } else if (
                        lower.includes("documentos_rechazados") ||
                        lower.includes("rechazado") ||
                        lower.includes("scar")
                    ) {
                        rechazadosPdfs.push(f);
                    } else {
                        aprobadosPdfs.push(f);
                    }
                }
            }
        });
    }
    const makeCategorySection = (title, files, inputName, colorClass) => {
        if (files.length === 0) return "";
        let borderLeftColor = "#033966";
        if (
            title.toLowerCase().includes("rechazados") ||
            title.toLowerCase().includes("scar")
        ) {
            borderLeftColor = "#9c0300";
        } else if (
            title.toLowerCase().includes("aprobados") ||
            title.toLowerCase().includes("liberación") ||
            title.toLowerCase().includes("liberados")
        ) {
            borderLeftColor = "#059669";
        } else if (
            title.toLowerCase().includes("dibujos") ||
            title.toLowerCase().includes("planos")
        ) {
            borderLeftColor = "#0284c7";
        } else if (title.toLowerCase().includes("ayudas")) {
            borderLeftColor = "#d97706";
        }
        return `
<div style="width: 100%;">
<h4 style="font-family:'Poppins',sans-serif;font-weight:700;color:#1e293b;font-size:1.05em;margin-top:10px;margin-bottom:12px;border-left:4px solid ${borderLeftColor};padding-left:8px;text-align:left;">${title}</h4>
<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(210px, 1fr)); gap: 12px; justify-items: center; width: 100%;">
${files
    .map((f, idx) => {
        const cleanName = f.nombre.split("/").pop();
        const ext = f.nombre.split(".").pop().toLowerCase();
        const esImg = ["png", "jpg", "jpeg", "gif", "webp", "bmp"].includes(
            ext,
        );
        const defaultIcon = esImg
            ? "galeria-shadow.png"
            : "pdf-view-shadow.png";
        const hoverIcon = esImg ? "galeria.png" : "pdf-view.png";
        const isConfirmacion = cleanName
            .toLowerCase()
            .includes("confirmacionmodelo");
        const shouldCheck = !(inputNameMode === "preorden" && isConfirmacion);
        const checkedAttr = shouldCheck ? "checked" : "";
        const checkedClass = shouldCheck ? "checked-card" : "";
        return `
<div class="dibujos-file-card ${colorClass} select-file-card ${checkedClass}" style="position: relative; width: 100%; max-width: 220px; display: inline-flex; flex-direction: column; align-items: center; text-align: center; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); box-sizing: border-box; background: #fff; padding: 10px; border: 1.5px solid #e2e8f0;">
<div style="position: absolute; top: 10px; left: 10px; z-index: 10;">
<input type="checkbox" name="${inputName}" value="${f.nombre}" ${checkedAttr} style="width: 20px; height: 20px; cursor: pointer;" onchange="this.closest('.select-file-card').classList.toggle('checked-card', this.checked);">
</div>
<div class="file-icon-wrapper" onclick="calidadVerPdf('${ot}', '${f.nombre}', '${f.tipo}')" style="cursor: pointer; margin-top: 10px;" title="Abrir Archivo">
<img src="${baseUrl}images/${defaultIcon}" class="file-icon icon-default" style="width: 48px; height: auto;">
<img src="${baseUrl}images/${hoverIcon}" class="file-icon icon-hover" style="width: 48px; height: auto;">
</div>
<div class="file-name" style="cursor: pointer; font-size: 0.82em; margin: 8px 0; max-height: 40px; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; font-weight: 600; color: #334155; line-height: 1.3;" title="Abrir Archivo" onclick="calidadVerPdf('${ot}', '${f.nombre}', '${f.tipo}')">
${cleanName}
</div>
<div class="file-actions" style="width: 100%; margin-top: auto;">
<button type="button" class="btn-dibujos btn-dibujos-sm btn-ver btn-ayuda-color" style="font-size:0.8em;padding:5px 12px;border-radius:6px;font-family:'Poppins',sans-serif;font-weight:600;flex-shrink:0;width:100%;" onclick="calidadVerPdf('${ot}', '${f.nombre}', '${f.tipo}')">Ver</button>
</div>
</div>
`;
    })
    .join("")}
</div>
</div>
`;
    };
    let nameDibujos = "dibujos[]";
    let nameAyudas = "ayudas[]";
    let nameAprobados = "dibujos_aprobados[]";
    let nameRechazados = "dibujos_rechazados[]";
    let nameOtros = "otros_documentos[]";
    if (inputNameMode === "preorden") {
        nameDibujos = "archivos_seleccionados[]";
        nameAyudas = "archivos_seleccionados[]";
        nameAprobados = "archivos_seleccionados[]";
        nameRechazados = "archivos_seleccionados[]";
        nameOtros = "archivos_seleccionados[]";
    } else if (inputNameMode === "scar") {
        nameDibujos = "dibujos[]";
        nameAyudas = "ayudas[]";
        nameAprobados = "otros_documentos[]";
        nameRechazados = "otros_documentos[]";
        nameOtros = "otros_documentos[]";
    }
    let sectionsHtml = "";
    sectionsHtml += makeCategorySection(
        "Ayudas Visuales",
        ayudasPdfs,
        nameAyudas,
        "card-ayuda",
    );
    sectionsHtml += makeCategorySection(
        "Dibujos de Fundición",
        dibujosPdfs,
        nameDibujos,
        "card-plano",
    );
    sectionsHtml += makeCategorySection(
        "Documentos Aprobados",
        aprobadosPdfs,
        nameAprobados,
        "card-ayuda",
    );
    const isReprocesoRechazos = /_[rR]\d+/.test(ot);
    const hideRechazados = inputNameMode === "preorden" && !isReprocesoRechazos;
    if (!hideRechazados) {
        sectionsHtml += makeCategorySection(
            inputNameMode === "calidad"
                ? "Documentos Rechazados"
                : "Documentos Rechazados (SCAR)",
            rechazadosPdfs,
            nameRechazados,
            "card-ayuda",
        );
    }
    if (inputNameMode !== "calidad") {
        sectionsHtml += makeCategorySection(
            "Otros Documentos",
            otrosPdfs,
            nameOtros,
            "card-ayuda",
        );
    }
    return sectionsHtml;
}
window.abrirModalEnviarPreOrden = function (ot, tipo, clasesFaltantes = null) {
    const modal = document.getElementById("modalEnviarPreOrden");
    const inputOt = document.getElementById("env-ot");
    const filesContainer = document.getElementById(
        "env-server-files-container",
    );
    inputOt.value = ot;
    // Set tipo (casting / modelo) on the hidden field
    const inputTipo = document.getElementById("env-tipo");
    if (inputTipo) {
        inputTipo.value = tipo || "modelo";
    }
    const subtitle = document.getElementById("env-po-modal-subtitle");
    if (subtitle) {
        subtitle.textContent = `OT: ${ot.replace(/_\d{8}_\d{6}_.*/, "")}`;
    }
    // Reset file inputs and badges
    adicionalesSelectedFiles = [];
    renderSelectedFilesBadges();
    // Limpiar contenedor de archivos y mostrar cargando
    filesContainer.innerHTML = `
<div style="text-align: center; padding: 10px;">
<div class="alm-spinner" style="border-top-color: #033966; display: inline-block;"></div>
<span style="color: #64748b; margin-left: 10px;">Obteniendo archivos del servidor...</span>
</div>
`;
    modal.classList.add("open");
    document.body.classList.add("modal-open");
    // Obtener los archivos de la OT desde el backend (pre-órdenes, dibujos y ayudas visuales)
    fetch(`${window.almacenRoutes.archivos}?ot=${encodeURIComponent(ot)}`)
        .then((res) => res.json())
        .then((data) => {
            // Prellenar la fecha de entrega si ya existe en la pre-orden
            const inputFecha = document.getElementById("env-fecha-entrega");
            if (inputFecha) {
                inputFecha.value = data.fecha_entrega || "";
            }
            if (data.existe && data.archivos && data.archivos.length > 0) {
                let baseUrl = window.baseUrl || window.location.origin + "/";
                if (!baseUrl.endsWith("/")) baseUrl += "/";
                let archivosAMostrar = data.archivos;
                if (tipo === "casting") {
                    archivosAMostrar = data.archivos.filter((f) => {
                        const n = (f.nombre || "").toLowerCase();
                        return (
                            n.includes("pre-orden_casting") ||
                            (n.includes("pre-orden") && n.includes("casting"))
                        );
                    });
                } else {
                    if (clasesFaltantes && Array.isArray(clasesFaltantes)) {
                        archivosAMostrar = archivosAMostrar.filter((f) => {
                            const n = (f.nombre || "").toLowerCase();
                            // Siempre mantener archivos que no estén divididos por carpetas de clase
                            if (
                                n.includes("documentos_aprobados") ||
                                n.includes("documentos_rechazados") ||
                                n.includes("pre-orden")
                            )
                                return true;
                            // Para Ayudas Visuales y Dibujos (que están dentro de carpetas de clase), validar si la clase es faltante
                            const knownClasses = [
                                "candado obturador",
                                "cabeza de soplo",
                                "obturador",
                                "bombillo",
                                "embudo",
                                "corona",
                                "plato",
                                "molde",
                                "fondo",
                            ];
                            let foundClass = null;
                            for (let kc of knownClasses) {
                                if (n.includes(kc)) {
                                    foundClass = kc;
                                    break;
                                }
                            }
                            if (foundClass) {
                                return clasesFaltantes.some((clase) => {
                                    let c = clase
                                        .toLowerCase()
                                        .trim()
                                        .replace(/^modelo\s+/i, "")
                                        .replace(/^casting\s+/i, "")
                                        .trim();
                                    return foundClass === c;
                                });
                            }
                            return false;
                        });
                    }
                }
                const sectionsHtml = generarHtmlCategorizadoArchivos(
                    archivosAMostrar,
                    ot,
                    baseUrl,
                    "preorden",
                );
                filesContainer.innerHTML =
                    sectionsHtml ||
                    `
<div style="text-align: center; color: #64748b; padding: 15px; font-style: italic;">
No se encontraron archivos en el servidor para esta OT.
</div>
`;
            } else {
                filesContainer.innerHTML = `
<div style="text-align: center; color: #64748b; padding: 15px; font-style: italic;">
No se encontraron archivos en el servidor para esta OT.
</div>
`;
            }
        })
        .catch((err) => {
            console.error(err);
            filesContainer.innerHTML = `
<div style="text-align: center; color: #ef4444; padding: 15px; font-weight: 600;">
Error al cargar la lista de archivos.
</div>
`;
        });
};
window.cerrarModalEnviarPreOrden = function () {
    const modal = document.getElementById("modalEnviarPreOrden");
    modal.classList.remove("open");
    document.body.classList.remove("modal-open");
    document.getElementById("formEnviarPreOrden").reset();
    adicionalesSelectedFiles = [];
    renderSelectedFilesBadges();
};
document
    .getElementById("formEnviarPreOrden")
    ?.addEventListener("submit", function (e) {
        e.preventDefault();
        const fecha = document.getElementById("env-fecha-entrega").value;
        if (!fecha) {
            mostrarToast(
                "Por favor, indica la fecha de entrega acordada.",
                true,
            );
            return;
        }
        const btn = document.getElementById("btn-submit-envio");
        if (!btn) return;
        const originalText = btn.innerText;
        btn.disabled = true;
        btn.innerText = "Enviando correo...";
        const formData = new FormData(this);
        // Replace native files with custom selection array
        formData.delete("archivos_adicionales[]");
        adicionalesSelectedFiles.forEach((file) => {
            formData.append("archivos_adicionales[]", file);
        });
        fetch(window.almacenRoutes.sendEmailPreOrden, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content"),
            },
            body: formData,
        })
            .then((res) => res.json())
            .then((data) => {
                if (data.success) {
                    mostrarToast(data.message);
                    cerrarModalEnviarPreOrden();
                    const ot = document.getElementById("env-ot").value;
                    if (window.ModeloStateMachine) {
                        if (formData.get("tipo") === "casting") {
                            window.ModeloStateMachine._forzarTerminal(
                                ot,
                                "casting_aprobado",
                            );
                        } else {
                            window.ModeloStateMachine.onCorreoEnviado(ot);
                        }
                    }
                    // Bloque 3b: bloquear controles del modal de pre-orden
                    bloquearModalPreOrden();
                    setTimeout(() => location.reload(), 1500);
                } else {
                    mostrarToast(
                        data.message || "Error al enviar el correo.",
                        true,
                    );
                }
            })
            .catch((err) => {
                console.error(err);
                mostrarToast("Error de conexión al enviar el correo.", true);
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerText = originalText;
            });
    });
function initCustomFileInputs() {
    const input = document.getElementById("env-archivos-adicionales");
    if (input) {
        input.addEventListener("change", function () {
            if (this.files && this.files.length > 0) {
                Array.from(this.files).forEach((file) => {
                    const alreadyExists = adicionalesSelectedFiles.some(
                        (f) => f.name === file.name && f.size === file.size,
                    );
                    if (!alreadyExists) {
                        adicionalesSelectedFiles.push(file);
                    }
                });
            }
            renderSelectedFilesBadges();
            this.value = ""; // Reset input to allow re-selection
        });
    }
    const inputFotos = document.getElementById("al-fotos");
    if (inputFotos) {
        inputFotos.addEventListener("change", function () {
            if (this.files && this.files.length > 0) {
                Array.from(this.files).forEach((file) => {
                    const alreadyExists = alFotosSelectedFiles.some(
                        (f) => f.name === file.name && f.size === file.size,
                    );
                    if (!alreadyExists) {
                        alFotosSelectedFiles.push(file);
                    }
                });
            }
            renderAlFotosBadges();
            this.value = ""; // Reset input to allow re-selection
        });
    }
    const inputScar = document.getElementById("env-scar-archivos-adicionales");
    if (inputScar) {
        inputScar.addEventListener("change", function () {
            if (this.files && this.files.length > 0) {
                Array.from(this.files).forEach((file) => {
                    const alreadyExists = envScarSelectedFiles.some(
                        (f) => f.name === file.name && f.size === file.size,
                    );
                    if (!alreadyExists) {
                        envScarSelectedFiles.push(file);
                    }
                });
            }
            renderEnvScarBadges();
            this.value = ""; // Reset input to allow re-selection
        });
    }
    const inputAlAdicionales = document.getElementById(
        "al-archivos-adicionales",
    );
    if (inputAlAdicionales) {
        inputAlAdicionales.addEventListener("change", function () {
            if (this.files && this.files.length > 0) {
                Array.from(this.files).forEach((file) => {
                    const alreadyExists = alAdicionalesSelectedFiles.some(
                        (f) => f.name === file.name && f.size === file.size,
                    );
                    if (!alreadyExists) {
                        alAdicionalesSelectedFiles.push(file);
                    }
                });
            }
            renderAlAdicionalesBadges();
            this.value = ""; // Reset input to allow re-selection
        });
    }
    const inputCmArchivos = document.getElementById("cm-archivos");
    if (inputCmArchivos) {
        inputCmArchivos.addEventListener("change", function () {
            if (this.files && this.files.length > 0) {
                Array.from(this.files).forEach((file) => {
                    const alreadyExists = cmConfirmarSelectedFiles.some(
                        (f) => f.name === file.name && f.size === file.size,
                    );
                    if (!alreadyExists) {
                        cmConfirmarSelectedFiles.push(file);
                    }
                });
            }
            renderCmConfirmarBadges();
            this.value = ""; // Reset input to allow re-selection
        });
    }
    const inputScarFotos = document.getElementById("scar-fotos");
    if (inputScarFotos) {
        inputScarFotos.addEventListener("change", function () {
            if (this.files && this.files.length > 0) {
                Array.from(this.files).forEach((file) => {
                    const alreadyExists = scarFotosSelectedFiles.some(
                        (f) => f.name === file.name && f.size === file.size,
                    );
                    if (!alreadyExists) {
                        scarFotosSelectedFiles.push(file);
                    }
                });
            }
            renderScarFotosBadges();
            this.value = ""; // Reset input to allow re-selection
        });
    }
    const inputScarOtros = document.getElementById("scar-otro-archivos");
    if (inputScarOtros) {
        inputScarOtros.addEventListener("change", function () {
            if (this.files && this.files.length > 0) {
                Array.from(this.files).forEach((file) => {
                    const alreadyExists = scarOtrosSelectedFiles.some(
                        (f) => f.name === file.name && f.size === file.size,
                    );
                    if (!alreadyExists) {
                        scarOtrosSelectedFiles.push(file);
                    }
                });
            }
            renderScarOtrosBadges();
            this.value = ""; // Reset input to allow re-selection
        });
    }
    const inputMicAdicionales = document.getElementById(
        "mic-archivos-adicionales",
    );
    if (inputMicAdicionales) {
        inputMicAdicionales.addEventListener("change", function () {
            if (this.files && this.files.length > 0) {
                Array.from(this.files).forEach((file) => {
                    const alreadyExists = micAdicionalesSelectedFiles.some(
                        (item) =>
                            item.file.name === file.name &&
                            item.file.size === file.size,
                    );
                    if (!alreadyExists) {
                        let initialType = "";
                        const fnameLower = file.name.toLowerCase();
                        if (fnameLower.includes("ldm")) {
                            if (
                                fnameLower.includes("fondo") &&
                                window.micRequiredClasses.includes("fondo")
                            )
                                initialType = "ldm_fondo";
                            else if (
                                fnameLower.includes("bombillo") &&
                                window.micRequiredClasses.includes("bombillo")
                            )
                                initialType = "ldm_bombillo";
                            else if (
                                fnameLower.includes("molde") &&
                                window.micRequiredClasses.includes("molde")
                            )
                                initialType = "ldm_molde";
                            else if (
                                fnameLower.includes("obturador") &&
                                window.micRequiredClasses.includes("obturador")
                            )
                                initialType = "ldm_obturador";
                        }
                        micAdicionalesSelectedFiles.push({
                            file: file,
                            type: initialType,
                        });
                    }
                });
            }
            renderMicAdicionalesBadges();
            this.value = ""; // Reset input to allow re-selection
        });
    }
}
function renderMicAdicionalesBadges() {
    const listContainer = document.getElementById(
        "mic-archivos-adicionales-list",
    );
    if (!listContainer) return;
    listContainer.innerHTML = "";
    const currentTypes = micAdicionalesSelectedFiles.map((item) => item.type);
    if (window.micRequiredClasses) {
        window.micRequiredClasses.forEach((c) => {
            const li = document.getElementById(`mic-req-item-${c}`);
            if (li) {
                if (currentTypes.includes(`ldm_${c}`)) {
                    li.innerHTML = `✅ Formato F-CCL-LDM para <span style="text-transform: capitalize; color: #10b981;">${c}</span> cargado`;
                    li.style.color = "#10b981";
                } else {
                    li.innerHTML = `⚠️ Falta cargar F-CCL-LDM para <span style="text-transform: capitalize;">${c}</span>`;
                    li.style.color = "#b45309";
                }
            }
        });
    }
    micAdicionalesSelectedFiles.forEach((item, index) => {
        const row = document.createElement("div");
        row.className = "select-file-card";
        row.classList.remove("cal-display-none");
        row.style.alignItems = "center";
        row.style.justifyContent = "space-between";
        row.style.background = "#fff";
        row.style.border = "1px solid #e2e8f0";
        row.style.borderRadius = "8px";
        row.style.padding = "10px 15px";
        row.style.gap = "15px";
        row.style.fontFamily = "'Poppins', sans-serif";
        let selectOptionsHtml = `<option value="">-- Selecciona el tipo de documento --</option>`;
        if (window.micRequiredClasses) {
            window.micRequiredClasses.forEach((c) => {
                const isSelected = item.type === `ldm_${c}` ? "selected" : "";
                selectOptionsHtml += `<option value="ldm_${c}" ${isSelected}>Formato F-CCL-LDM - ${c.toUpperCase()}</option>`;
            });
        }
        const isAdicionalSelected = item.type === "adicional" ? "selected" : "";
        selectOptionsHtml += `<option value="adicional" ${isAdicionalSelected}>Documento Adicional</option>`;
        row.innerHTML = `
<div style="display: flex; align-items: center; gap: 10px; flex: 1; min-width: 0;">
<span style="font-size: 1.5em;">📄</span>
<div style="min-width: 0;">
<div style="font-weight: 600; color: #334155; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="${item.file.name}">${item.file.name}</div>
<div style="font-size: 0.8em; color: #64748b;">(${(item.file.size / 1024).toFixed(1)} KB)</div>
</div>
</div>
<div style="display: flex; align-items: center; gap: 10px;">
<select class="form-control" style="font-family:'Poppins',sans-serif; font-size: 0.9em; padding: 6px 12px; height: auto; border-radius: 6px; width: 240px;" onchange="updateMicFileAssociation(${index}, this.value)">
${selectOptionsHtml}
</select>
<button type="button" style="background: #fca5a5; border: none; border-radius: 50%; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #9c0300; font-weight: bold; font-size: 1.1em; transition: all 0.2s;" onclick="removeMicAdicionalesAttachment(${index})" title="Eliminar">&times;</button>
</div>
`;
        listContainer.appendChild(row);
    });
}
window.updateMicFileAssociation = function (index, value) {
    if (micAdicionalesSelectedFiles[index]) {
        micAdicionalesSelectedFiles[index].type = value;
        renderMicAdicionalesBadges();
    }
};
window.removeMicAdicionalesAttachment = function (index) {
    micAdicionalesSelectedFiles.splice(index, 1);
    renderMicAdicionalesBadges();
};
function renderScarFotosBadges() {
    const listContainer = document.getElementById("scar-fotos-list");
    if (!listContainer) return;
    listContainer.innerHTML = "";
    if (scarFotosSelectedFiles.length === 0) {
        listContainer.classList.add("cal-display-none");
        return;
    }
    listContainer.classList.remove("cal-display-none");
    scarFotosSelectedFiles.forEach((file, index) => {
        const card = document.createElement("div");
        card.className =
            "dibujos-file-card card-ayuda select-file-card checked-card";
        card.style.position = "relative";
        card.style.width = "100%";
        card.style.maxWidth = "220px";
        card.classList.remove("cal-display-none");
        card.style.flexDirection = "column";
        card.style.alignItems = "center";
        card.style.textAlign = "center";
        card.style.borderRadius = "12px";
        card.style.boxShadow = "0 4px 6px rgba(0,0,0,0.05)";
        card.style.boxSizing = "border-box";
        card.style.padding = "12px";
        card.style.border = "2px solid #d97706";
        card.style.background = "#fff";
        const fileUrl = URL.createObjectURL(file);
        const iconHtml = `
<div style="width: 80px; height: 80px; margin-top: 10px; display: flex; align-items: center; justify-content: center; overflow: hidden; border-radius: 8px; border: 1px solid #e2e8f0;">
<img src="${fileUrl}" style="width: 100%; height: 100%; object-fit: cover;">
</div>
`;
        card.innerHTML = `
<div style="position: absolute; top: 10px; right: 10px; z-index: 10;">
<button type="button" style="background: #fca5a5; border: none; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #9c0300; font-weight: bold; font-size: 0.9em; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onclick="removeScarFotoAttachment(${index})" title="Eliminar">&times;</button>
</div>
${iconHtml}
<div class="file-name" style="cursor: pointer; font-size: 0.85em; margin: 8px 0; max-height: 40px; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; font-weight: 600; color: #334155; line-height: 1.3;" title="${file.name}" onclick="window.open('${fileUrl}', '_blank')">
${file.name}
</div>
<div class="file-actions" style="width: 100%; margin-top: auto;">
<button type="button" class="btn-dibujos btn-dibujos-sm btn-ver btn-ayuda-color" style="width: 100%; background: #d97706; border: none; color: white; padding: 6px 12px; border-radius: 6px; font-weight: 600; cursor: pointer;" onclick="window.open('${fileUrl}', '_blank')">Ver</button>
</div>
`;
        listContainer.appendChild(card);
    });
}
window.removeScarFotoAttachment = function (index) {
    scarFotosSelectedFiles.splice(index, 1);
    renderScarFotosBadges();
};
function renderScarOtrosBadges() {
    const listContainer = document.getElementById("scar-otro-archivos-list");
    if (!listContainer) return;
    listContainer.innerHTML = "";
    if (scarOtrosSelectedFiles.length === 0) {
        listContainer.classList.add("cal-display-none");
        return;
    }
    listContainer.classList.remove("cal-display-none");
    scarOtrosSelectedFiles.forEach((file, index) => {
        const card = document.createElement("div");
        card.className =
            "dibujos-file-card card-ayuda select-file-card checked-card";
        card.style.position = "relative";
        card.style.width = "100%";
        card.style.maxWidth = "220px";
        card.classList.remove("cal-display-none");
        card.style.flexDirection = "column";
        card.style.alignItems = "center";
        card.style.textAlign = "center";
        card.style.borderRadius = "12px";
        card.style.boxShadow = "0 4px 6px rgba(0,0,0,0.05)";
        card.style.boxSizing = "border-box";
        card.style.padding = "12px";
        card.style.border = "2px solid #0369a1";
        card.style.background = "#fff";
        let baseUrl = window.baseUrl || window.location.origin + "/";
        if (!baseUrl.endsWith("/")) baseUrl += "/";
        let iconHtml = "";
        const fileUrl = URL.createObjectURL(file);
        if (file.type.startsWith("image/")) {
            iconHtml = `
<div style="width: 80px; height: 80px; margin-top: 10px; display: flex; align-items: center; justify-content: center; overflow: hidden; border-radius: 8px; border: 1px solid #e2e8f0;">
<img src="${fileUrl}" style="width: 100%; height: 100%; object-fit: cover;">
</div>
`;
        } else {
            iconHtml = `
<div class="file-icon-wrapper" style="cursor: pointer; margin-top: 10px;" title="Abrir PDF" onclick="window.open('${fileUrl}', '_blank')">
<img src="${baseUrl}images/pdf-view-shadow.png" class="file-icon icon-default">
<img src="${baseUrl}images/pdf-view.png" class="file-icon icon-hover">
</div>
`;
        }
        card.innerHTML = `
<div style="position: absolute; top: 10px; right: 10px; z-index: 10;">
<button type="button" style="background: #fca5a5; border: none; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #9c0300; font-weight: bold; font-size: 0.9em; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onclick="removeScarOtrosAttachment(${index})" title="Eliminar">&times;</button>
</div>
${iconHtml}
<div class="file-name" style="cursor: pointer; font-size: 0.85em; margin: 8px 0; max-height: 40px; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; font-weight: 600; color: #334155; line-height: 1.3;" title="${file.name}" onclick="window.open('${fileUrl}', '_blank')">
${file.name}
</div>
<div class="file-actions" style="width: 100%; margin-top: auto;">
<button type="button" class="btn-dibujos btn-dibujos-sm btn-ver btn-ayuda-color" style="width: 100%; background: #0369a1; border: none; color: white; padding: 6px 12px; border-radius: 6px; font-weight: 600; cursor: pointer;" onclick="window.open('${fileUrl}', '_blank')">Ver</button>
</div>
`;
        listContainer.appendChild(card);
    });
}
window.removeScarOtrosAttachment = function (index) {
    scarOtrosSelectedFiles.splice(index, 1);
    renderScarOtrosBadges();
};
function renderCmConfirmarBadges() {
    const listContainer = document.getElementById("cm-archivos-list");
    if (!listContainer) return;
    listContainer.innerHTML = "";
    if (cmConfirmarSelectedFiles.length === 0) {
        listContainer.classList.add("cal-display-none");
        return;
    }
    listContainer.classList.remove("cal-display-none");
    cmConfirmarSelectedFiles.forEach((file, index) => {
        const card = document.createElement("div");
        card.className =
            "dibujos-file-card card-ayuda select-file-card checked-card";
        card.style.position = "relative";
        card.style.width = "100%";
        card.style.maxWidth = "220px";
        card.classList.remove("cal-display-none");
        card.style.flexDirection = "column";
        card.style.alignItems = "center";
        card.style.textAlign = "center";
        card.style.borderRadius = "12px";
        card.style.boxShadow = "0 4px 6px rgba(0,0,0,0.05)";
        card.style.boxSizing = "border-box";
        card.style.padding = "12px";
        card.style.border = "2px solid #10b981";
        card.style.background = "#fff";
        let baseUrl = window.baseUrl || window.location.origin + "/";
        if (!baseUrl.endsWith("/")) baseUrl += "/";
        // Determinar icono o thumbnail
        let iconHtml = "";
        const fileUrl = URL.createObjectURL(file);
        if (file.type.startsWith("image/")) {
            iconHtml = `
<div style="width: 80px; height: 80px; margin-top: 10px; display: flex; align-items: center; justify-content: center; overflow: hidden; border-radius: 8px; border: 1px solid #e2e8f0;">
<img src="${fileUrl}" style="width: 100%; height: 100%; object-fit: cover;">
</div>
`;
        } else {
            iconHtml = `
<div class="file-icon-wrapper" style="cursor: pointer; margin-top: 10px;" title="Abrir PDF" onclick="window.open('${fileUrl}', '_blank')">
<img src="${baseUrl}images/pdf-view-shadow.png" class="file-icon icon-default">
<img src="${baseUrl}images/pdf-view.png" class="file-icon icon-hover">
</div>
`;
        }
        card.innerHTML = `
<!-- Botón Eliminar overlay en esquina superior derecha -->
<div style="position: absolute; top: 10px; right: 10px; z-index: 10;">
<button type="button" style="background: #fca5a5; border: none; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #9c0300; font-weight: bold; font-size: 0.9em; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onclick="removeCmConfirmarAttachment(${index})" title="Eliminar">&times;</button>
</div>
${iconHtml}
<div class="file-name" style="cursor: pointer; font-size: 0.85em; margin: 8px 0; max-height: 40px; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; font-weight: 600; color: #334155; line-height: 1.3;" title="${file.name}" onclick="window.open('${fileUrl}', '_blank')">
${file.name}
</div>
<div class="file-actions" style="width: 100%; margin-top: auto;">
<button type="button" class="btn-dibujos btn-dibujos-sm btn-ver btn-ayuda-color" style="width: 100%; background: #10b981; border: none; color: white; padding: 6px 12px; border-radius: 6px; font-weight: 600; cursor: pointer;" onclick="window.open('${fileUrl}', '_blank')">Ver</button>
</div>
`;
        listContainer.appendChild(card);
    });
}
window.removeCmConfirmarAttachment = function (index) {
    cmConfirmarSelectedFiles.splice(index, 1);
    renderCmConfirmarBadges();
};
function renderSelectedFilesBadges() {
    const listContainer = document.getElementById(
        "env-archivos-adicionales-list",
    );
    if (!listContainer) return;
    listContainer.innerHTML = "";
    adicionalesSelectedFiles.forEach((file, index) => {
        const badge = document.createElement("span");
        badge.className = "file-badge";
        badge.classList.remove("cal-display-none");
        badge.style.alignItems = "center";
        badge.style.gap = "6px";
        badge.innerHTML = `
📄 ${file.name} (${(file.size / 1024).toFixed(1)} KB)
<button type="button" class="remove-file-badge-btn" style="background: none; border: none; color: #9c0300; font-weight: bold; cursor: pointer; padding: 0 4px; font-size: 1.2em; line-height: 1; display: flex; align-items: center;" onclick="removeSelectedAttachment(${index})">&times;</button>
`;
        listContainer.appendChild(badge);
    });
}
window.removeSelectedAttachment = function (index) {
    adicionalesSelectedFiles.splice(index, 1);
    renderSelectedFilesBadges();
};
function renderAlFotosBadges() {
    const listContainer = document.getElementById("al-fotos-list");
    const textEl = document.getElementById("al-fotos-text");
    if (!listContainer) return;
    listContainer.innerHTML = "";
    if (alFotosSelectedFiles.length > 0) {
        if (textEl) {
            textEl.textContent = `${alFotosSelectedFiles.length} archivo(s) seleccionado(s)`;
            textEl.style.color = "#10b981"; // Green color when selected
        }
        alFotosSelectedFiles.forEach((file, index) => {
            const badge = document.createElement("span");
            badge.className = "file-badge";
            badge.classList.remove("cal-display-none");
            badge.style.alignItems = "center";
            badge.style.gap = "6px";
            badge.style.padding = "6px 12px";
            badge.style.background = "#fffbeb";
            badge.style.border = "1.5px solid #fde047";
            badge.style.borderRadius = "8px";
            badge.style.color = "#854d0e";
            badge.style.fontSize = "0.85em";
            badge.style.fontFamily = "'Poppins', sans-serif";
            badge.innerHTML = `
📄 ${file.name} (${(file.size / 1024).toFixed(1)} KB)
<button type="button" class="remove-file-badge-btn" style="background: none; border: none; color: #9c0300; font-weight: bold; cursor: pointer; padding: 0 4px; font-size: 1.2em; line-height: 1; display: flex; align-items: center;" onclick="removeAlFotoAttachment(${index})">&times;</button>
`;
            listContainer.appendChild(badge);
        });
    } else {
        if (textEl) {
            textEl.textContent = "Adjuntar fotos u otros archivos *";
            textEl.style.color = "#d97706";
        }
    }
}
window.removeAlFotoAttachment = function (index) {
    alFotosSelectedFiles.splice(index, 1);
    renderAlFotosBadges();
};
function renderEnvScarBadges() {
    const listContainer = document.getElementById(
        "env-scar-archivos-adicionales-list",
    );
    if (!listContainer) return;
    listContainer.innerHTML = "";
    envScarSelectedFiles.forEach((file, index) => {
        const badge = document.createElement("span");
        badge.className = "file-badge";
        badge.classList.remove("cal-display-none");
        badge.style.alignItems = "center";
        badge.style.gap = "6px";
        badge.style.padding = "6px 12px";
        badge.style.background = "#fff8f8";
        badge.style.border = "1.5px solid #fca5a5";
        badge.style.borderRadius = "8px";
        badge.style.color = "#9c0300";
        badge.style.fontSize = "0.85em";
        badge.style.fontFamily = "'Poppins', sans-serif";
        badge.innerHTML = `
📄 ${file.name} (${(file.size / 1024).toFixed(1)} KB)
<button type="button" class="remove-file-badge-btn" style="background: none; border: none; color: #9c0300; font-weight: bold; cursor: pointer; padding: 0 4px; font-size: 1.2em; line-height: 1; display: flex; align-items: center;" onclick="removeEnvScarAttachment(${index})">&times;</button>
`;
        listContainer.appendChild(badge);
    });
}
window.removeEnvScarAttachment = function (index) {
    envScarSelectedFiles.splice(index, 1);
    renderEnvScarBadges();
};
function renderAlAdicionalesBadges() {
    const listContainer = document.getElementById(
        "al-archivos-adicionales-list",
    );
    if (!listContainer) return;
    listContainer.innerHTML = "";
    alAdicionalesSelectedFiles.forEach((file, index) => {
        const badge = document.createElement("span");
        badge.className = "file-badge";
        badge.classList.remove("cal-display-none");
        badge.style.alignItems = "center";
        badge.style.gap = "6px";
        badge.innerHTML = `
📄 ${file.name} (${(file.size / 1024).toFixed(1)} KB)
<button type="button" class="remove-file-badge-btn" style="background: none; border: none; color: #9c0300; font-weight: bold; cursor: pointer; padding: 0 4px; font-size: 1.2em; line-height: 1; display: flex; align-items: center;" onclick="removeAlAdicionalesAttachment(${index})">&times;</button>
`;
        listContainer.appendChild(badge);
    });
}
window.removeAlAdicionalesAttachment = function (index) {
    alAdicionalesSelectedFiles.splice(index, 1);
    renderAlAdicionalesBadges();
};
// ── MODAL LIBERACION DE MODELOS (Calidad) — F-CCL-LDM ────────────────────────
/**
 * Mapa de visibilidad de tablas por tipo de modelo.
 * lib-tabla-1    => Macho/Hembra A-G2 (Molde, Bombillo)
 * lib-tabla-2    => Matriz V,W,X,Y,Z  (Molde, Bombillo, Obturador)
 * lib-tabla-fondo=> Fondo
 */
const LIB_TABLA_MAP = {
    Fondo: ["lib-tabla-fondo"],
    Obturador: ["lib-tabla-obturador"],
    Molde: ["lib-tabla-1", "lib-tabla-2"],
    Bombillo: ["lib-tabla-1", "lib-tabla-2"],
    Corona: ["lib-tabla-fondo"],
    Plato: ["lib-tabla-fondo"],
    Embudo: ["lib-tabla-fondo"],
    "Cabeza de Soplo": ["lib-tabla-fondo"],
    "Candado Obturador": ["lib-tabla-fondo"],
};
const LIB_TODAS_TABLAS = [
    "lib-tabla-1",
    "lib-tabla-2",
    "lib-tabla-fondo",
    "lib-tabla-obturador",
];
let _libTipo = "aprobar";
let _libOt = "";
// ── Apertura del modal ────────────────────────────────────────────────────────
/**
 * Abre el modal de Liberacion de Modelos configurado para aprobar o rechazar.
 *
 * @param {string} ot    - Nombre completo de la OT
 * @param {string} tipo  - 'aprobar' | 'rechazar'
 */
window.abrirModalLiberacion = function (ot, tipo) {
    _libTipo = tipo;
    _libOt = ot;
    const modal = document.getElementById("modalLiberacionModelo");
    const header = document.getElementById("lib-modal-header");
    const title =
        document.getElementById("lib-modal-title-text") ||
        document.getElementById("lib-modal-title");
    const subtitle = document.getElementById("lib-modal-subtitle");
    const rechazoBlock = document.getElementById("lib-rechazo-block");
    const actionsEl = document.getElementById("lib-actions");
    const hiddenOt = document.getElementById("lib-ot");
    const hiddenAccion = document.getElementById("lib-accion");
    const otDisplay = document.getElementById("lib-ot-display");
    if (!modal) return;
    // Resetear formulario para no conservar datos previos
    const formEl = document.getElementById("formLiberacion");
    if (formEl) formEl.reset();
    // Mostrar OT en la cabecera del formato
    if (otDisplay) otDisplay.textContent = ot.replace(/_\d{8}_\d{6}_.*/, "");
    // Configurar apariencia segun tipo de accion
    const esRechazo = tipo === "rechazar";
    if (esRechazo) {
        header.classList.add("lib-modal-header-rechazo");
        if (title)
            title.textContent = "Formato de Rechazo de Modelo — F-CCL-LDM";
        if (subtitle)
            subtitle.textContent = `OT: ${ot.replace(/_\d{8}_\d{6}_.*/, "")}  |  Modo: Rechazo`;
        if (rechazoBlock) rechazoBlock.classList.remove("cal-display-none");
    } else {
        header.classList.remove("lib-modal-header-rechazo");
        if (title)
            title.textContent = "Formato de Liberacion de Modelos — F-CCL-LDM";
        if (subtitle)
            subtitle.textContent = `OT: ${ot.replace(/_\d{8}_\d{6}_.*/, "")}  |  Modo: Aprobacion`;
        if (rechazoBlock) rechazoBlock.classList.add("cal-display-none");
    }
    if (actionsEl) {
        const imgDescarga =
            window.almacenAppAssets?.descarga ?? "/images/Descarga.png";
        const imgAprobado =
            window.almacenAppAssets?.aprobado ?? "/images/aprobado.png";
        const imgRechazado =
            window.almacenAppAssets?.rechazado ?? "/images/Rechazado.png";
        actionsEl.innerHTML = `
<div style="display:flex; gap:12px; justify-content:center; align-items:center; flex-wrap:wrap; width:100%;">
<button type="button" class="btn-lib-aprobar-send" id="lib-btn-accion"
style="flex:1; min-width:200px; max-width:380px; justify-content:center; display:flex; gap:8px; align-items:center; font-size:1.15em; padding:14px 28px; border-radius:10px; font-family:'Poppins',sans-serif; font-weight:700; height:auto;">
<img src="${imgDescarga}" alt="" style="width:20px;height:20px;">
Aprobar y Descargar PDF
</button>
</div>
`;
        // Asignar eventos a los botones recien creados
        document
            .getElementById("lib-btn-accion")
            ?.addEventListener("click", () => _libSubmit("accion"));
    }
    if (hiddenOt) hiddenOt.value = ot;
    if (hiddenAccion) hiddenAccion.value = esRechazo ? "rechazar" : "aprobar";
    // Abrir modal
    modal.classList.add("open");
    document.body.classList.add("modal-open");
    // Inicializar la lupa para las imagenes
    _libInicializarZoom();
    // Pre-cargar datos existentes del backend
    _libCargarDatos(ot);
};
// ── Cierre del modal ──────────────────────────────────────────────────────────
window.cerrarModalLiberacion = function () {
    const modal = document.getElementById("modalLiberacionModelo");
    if (modal) modal.classList.remove("open");
    document.body.classList.remove("modal-open");
    // Ocultar zoom si quedara visible
    const zoomEl = document.getElementById("lib-zoom-result");
    if (zoomEl) zoomEl.classList.add("cal-display-none");
};
/**
 * Actualiza dinamicamente el badge de estado en la columna "Modelo" de la tabla
 * sin necesidad de recargar la pagina. Se ejecuta tras un guardado parcial (borrador).
 *
 * @param {string} ot          - Identificador exacto de la OT
 * @param {string} nuevoEstado - 'pendiente' | 'aprobado' | 'rechazado'
 */
function _libActualizarBadgeEstado(ot, nuevoEstado) {
    const container =
        document.getElementById(`status-modelo-${ot}`) ||
        document.getElementById(`status-modelo-${ot.replace(/_R\d+$/i, "")}`);
    if (!container) return;
    const assets = window.almacenAppAssets ?? {};
    const imgMap = {
        pendiente: {
            src: assets.guardado ?? "/images/Guardado.png",
            alt: "Guardado (Borrador)",
            cls: "badge-modelo-guardado",
            title: "Datos capturados por Calidad (borrador)",
        },
        aprobado: {
            src: assets.aprobado ?? "/images/aprobado.png",
            alt: "Aprobado",
            cls: "badge-modelo-ok",
            title: "Modelo liberado y aprobado por Calidad",
        },
        rechazado: {
            src: assets.rechazado ?? "/images/Rechazado.png",
            alt: "Rechazado",
            cls: "badge-modelo-rechazado",
            title: "Modelo rechazado por Calidad",
        },
    };
    const cfg = imgMap[nuevoEstado];
    if (!cfg) return;
    container.innerHTML = `
<span class="${cfg.cls}" title="${cfg.title}">
<img src="${cfg.src}" alt="${cfg.alt}" style="width:38px;height:38px;">
</span>
`;
}
// Cerrar al hacer clic en el backdrop
document.addEventListener("click", (e) => {
    if (e.target.id === "modalLiberacionModelo") cerrarModalLiberacion();
    if (e.target.id === "modalScar") cerrarModalScar();
    if (e.target.id === "modalEnviarScar") cerrarModalEnviarScar();
});
// Cerrar lightbox con Escape, cerrar modal con Escape si lightbox cerrado
document.addEventListener("keydown", (e) => {
    if (e.key !== "Escape") return;
    const lb = document.getElementById("lib-lightbox");
    if (lb && lb.classList.contains("open")) {
        libCerrarLightbox();
    } else {
        cerrarModalLiberacion();
        cerrarModalScar();
        cerrarModalEnviarScar();
    }
});
// ── Cambio dinamico de tabla segun tipo seleccionado ─────────────────────────
/**
 * Muestra u oculta las tablas segun el tipo de modelo elegido en el select.
 * Los campos de las tablas ocultas se marcan con data-lib-hidden="1" para
 * ser llenados con 0.000 antes del submit.
 *
 * @param {string} tipo - Valor seleccionado en #lib-tipo
 */
window.libCambiarTipo = function (tipo) {
    const aviso = document.getElementById("lib-tabla-aviso");
    const visibles = LIB_TABLA_MAP[tipo] ?? [];
    // Resetear formulario para evitar cruce de datos entre "Molde" y "Bombillo"
    const form = document.getElementById("formLiberacion");
    const currOt = document.getElementById("lib-ot")?.value;
    const currAcc = document.getElementById("lib-accion")?.value;
    if (form) form.reset();
    // Restaurar meta datos despues de limpiar
    if (document.getElementById("lib-ot"))
        document.getElementById("lib-ot").value = currOt;
    if (document.getElementById("lib-accion"))
        document.getElementById("lib-accion").value = currAcc;
    if (document.getElementById("lib-tipo"))
        document.getElementById("lib-tipo").value = tipo;
    LIB_TODAS_TABLAS.forEach((id) => {
        const el = document.getElementById(id);
        if (!el) return;
        const activo = visibles.includes(id);
        el.classList.toggle("cal-display-none", !activo);
        if (activo) {
            el.removeAttribute("hidden");
        } else {
            el.setAttribute("hidden", "");
        }
        // Marcar inputs ocultos para el zero-fill en submit
        el.querySelectorAll('input[type="number"]').forEach((inp) => {
            inp.dataset.libHidden = activo ? "0" : "1";
        });
    });
    if (aviso) aviso.classList.toggle("cal-display-none", visibles.length > 0);
    const tituloFondo = document.getElementById("lib-tabla-fondo-title");
    if (tituloFondo) {
        if (tipo === "Fondo") tituloFondo.textContent = "Dimensiones de Fondo";
        else tituloFondo.textContent = "Dimensiones de " + tipo;
    }
    const decisionSelector = document.getElementById("lib-decision-selector");
    if (decisionSelector) {
        decisionSelector.classList.toggle("cal-display-none", !tipo);
        decisionSelector.style.display = tipo ? "flex" : "none";
    }
    if (typeof _libActualizarColorSelectPropio === "function") {
        _libActualizarColorSelectPropio();
    }
    // Si tenemos registros cacheados especificos para este tipo, poblamos la UI
    if (
        tipo &&
        window.cacheLiberacionGlobal &&
        window.cacheLiberacionGlobal[tipo]
    ) {
        const cached = window.cacheLiberacionGlobal[tipo];
        _libRellenarInputs(cached);
        if (cached.decision) {
            _libSetDecisionUI(cached.decision);
        } else {
            _libSetDecisionUI("aprobar");
        }
    } else {
        _libSetDecisionUI("aprobar");
    }
    // CARGAR BORRADOR AUTOMÁTICAMENTE ANTES DE CAPTURAR EL ESTADO INICIAL
    window.loadLiberacionDraft();
    // Capturar el estado despues de llenar la UI
    setTimeout(() => {
        window._libLastSavedState = _libGetSerializedForm();
    }, 150);
};
function _libGetSerializedForm() {
    const form = document.getElementById("formLiberacion");
    if (!form) return "";
    _libZeroFillOcultos();
    document
        .querySelectorAll(".lib-num-input, .lib-num-input-sm")
        .forEach((inp) => formatInputTruncated(inp));
    return new URLSearchParams(new FormData(form)).toString();
}
// ── Lightbox de imagenes ──────────────────────────────────────────────────────
/**
 * Abre el lightbox con la imagen del wrapper clicado.
 * @param {HTMLElement} wrapper - div.lib-img-zoom-wrapper
 */
window.libAbrirLightbox = function (wrapper) {
    const lb = document.getElementById("lib-lightbox");
    const lbImg = document.getElementById("lib-lightbox-img");
    const lbCap = document.getElementById("lib-lightbox-caption");
    const img = wrapper.querySelector(".lib-ref-img");
    if (!lb || !lbImg || !img) return;
    lbImg.src = wrapper.dataset.src || img.src;
    lbImg.alt = wrapper.dataset.label || img.alt;
    if (lbCap) lbCap.textContent = wrapper.dataset.label || "";
    lb.classList.add("open");
    document.body.classList.add("modal-open");
};
/**
 * Cierra el lightbox de imagen ampliada.
 */
window.libCerrarLightbox = function () {
    const lb = document.getElementById("lib-lightbox");
    if (lb) lb.classList.remove("open");
};
// ── Zoom tipo lupa (magnifying glass) ────────────────────────────────────────
/**
 * Inicializa el efecto de lupa para todas las imagenes de referencia del modal.
 * Se llama una sola vez al abrir el modal; usa delegacion para evitar
 * registros duplicados si el modal se abre varias veces.
 */
let _libZoomInit = false;
function _libInicializarZoom() {
    if (_libZoomInit) return;
    _libZoomInit = true;
    const zoomResult = document.getElementById("lib-zoom-result");
    if (!zoomResult) return;
    // Tamano del recuadro de zoom y factor de ampliacion
    const ZOOM_SIZE = 450;
    const ZOOM_RATIO = 3.2;
    document.addEventListener("mousemove", (e) => {
        const wrapper = e.target.closest(".lib-img-zoom-wrapper");
        if (!wrapper) {
            zoomResult.classList.add("cal-display-none");
            return;
        }
        // Solo activar si el modal de liberacion esta abierto
        const modal = document.getElementById("modalLiberacionModelo");
        if (!modal || !modal.classList.contains("open")) {
            zoomResult.classList.add("cal-display-none");
            return;
        }
        const img = wrapper.querySelector(".lib-ref-img");
        if (!img || !img.complete) return;
        const rect = img.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        // Ignorar si el cursor esta fuera de los limites de la imagen
        if (x < 0 || y < 0 || x > rect.width || y > rect.height) {
            zoomResult.classList.add("cal-display-none");
            return;
        }
        // Calcular posicion del background para el recuadro de zoom
        const bgX = -(x * ZOOM_RATIO - ZOOM_SIZE / 2);
        const bgY = -(y * ZOOM_RATIO - ZOOM_SIZE / 2);
        zoomResult.classList.remove("cal-display-none");
        zoomResult.style.backgroundImage = `url(${img.src})`;
        zoomResult.style.backgroundSize = `${rect.width * ZOOM_RATIO}px ${rect.height * ZOOM_RATIO}px`;
        zoomResult.style.backgroundPosition = `${bgX}px ${bgY}px`;
        zoomResult.style.width = `${ZOOM_SIZE}px`;
        zoomResult.style.height = `${ZOOM_SIZE}px`;
        // Posicionar el recuadro cerca del cursor, evitando que salga de pantalla
        const offsetX = 24;
        const offsetY = -ZOOM_SIZE / 2;
        let posX = e.clientX + offsetX;
        let posY = e.clientY + offsetY;
        if (posX + ZOOM_SIZE > window.innerWidth - 10)
            posX = e.clientX - ZOOM_SIZE - offsetX;
        if (posY < 10) posY = 10;
        if (posY + ZOOM_SIZE > window.innerHeight - 10)
            posY = window.innerHeight - ZOOM_SIZE - 10;
        zoomResult.style.left = `${posX}px`;
        zoomResult.style.top = `${posY}px`;
    });
    document.addEventListener(
        "mouseleave",
        () => {
            zoomResult.classList.add("cal-display-none");
        },
        true,
    );
}
// ── Carga de datos existentes desde el backend ────────────────────────────────
window.cacheLiberacionGlobal = {};
/**
 * Consulta la API y pre-llena el formulario con datos guardados previamente.
 * Estructura medidas_plantilla: claves en formato "{row}_{col}" (ej: "plantilla_V", "templadera_x1").
 */
async function _libCargarDatos(ot) {
    if (!window.almacenRoutes?.getLiberacion) return;
    try {
        const url = `${window.almacenRoutes.getLiberacion}?ot=${encodeURIComponent(ot)}`;
        const resp = await fetch(url, {
            headers: {
                Accept: "application/json",
                "X-Requested-With": "XMLHttpRequest",
            },
        });
        const data = await resp.json();
        // Normalizar claves de cache desde la DB para evitar problemas case-sensitive
        const rawCache = data.registros_por_tipo || {};
        window.cacheLiberacionGlobal = {};
        const MAPA_TIPO = {
            "candado obturador": "Candado Obturador",
            "cabeza de soplo": "Cabeza de Soplo",
            embudo: "Embudo",
            corona: "Corona",
            plato: "Plato",
            fondo: "Fondo",
            obturador: "Obturador",
            molde: "Molde",
            bombillo: "Bombillo",
        };
        const knownKeys = Object.keys(MAPA_TIPO);
        for (let key in rawCache) {
            let normalizedKey = key;
            const keyLow = key.toLowerCase();
            for (let k of knownKeys) {
                if (keyLow.includes(k)) {
                    normalizedKey = MAPA_TIPO[k];
                    break;
                }
            }
            window.cacheLiberacionGlobal[normalizedKey] = rawCache[key];
        }
        // Colorear las opciones del select según su estado
        _libActualizarColoresSelect();
        if (!data.success) return;
        const lastLib = data.liberacion;
        // Pre-seleccionar tipo y actualizar visibilidad de tablas
        const selectTipo = document.getElementById("lib-tipo");
        if (selectTipo) {
            if (window._currentClasesActivas) {
                // Si venimos del flujo unificado, reevaluar auto-selección (ignorar los ya verdes)
                const autoSelectValue = _libFiltrarTiposModelo(
                    window._currentClasesActivas,
                    window._currentTodasClases,
                );
                if (autoSelectValue) {
                    selectTipo.value = autoSelectValue;
                    libCambiarTipo(autoSelectValue);
                }
            } else if (lastLib && lastLib.tipo_modelo) {
                // Flujo normal: cargar el último modelo que guardamos
                let tipo = lastLib.tipo_modelo;
                const tipoLow = tipo.toLowerCase();
                for (let k of knownKeys) {
                    if (tipoLow.includes(k)) {
                        tipo = MAPA_TIPO[k];
                        break;
                    }
                }
                selectTipo.value = tipo;
                libCambiarTipo(tipo);
            } else {
                // Capturar el estado si no habia lastLib
                setTimeout(() => {
                    window._libLastSavedState = _libGetSerializedForm();
                }, 150);
            }
        }
    } catch (err) {
        console.error("Error al cargar datos de liberacion:", err);
    }
}
/**
 * Colorea las opciones del select #lib-tipo según la decisión guardada o seleccionada.
 */
function _libActualizarColoresSelect() {
    const select = document.getElementById("lib-tipo");
    if (!select) return;
    select.querySelectorAll("option").forEach((opt) => {
        const val = opt.value;
        if (!val) {
            opt.style.backgroundColor = "";
            opt.style.color = "";
            return;
        }
        const record =
            window.cacheLiberacionGlobal && window.cacheLiberacionGlobal[val];
        if (record) {
            if (record.decision === "aprobar") {
                opt.style.backgroundColor = "#d1fae5"; // Verde suave
                opt.style.color = "#065f46";
            } else if (record.decision === "rechazar") {
                opt.style.backgroundColor = "#fee2e2"; // Rojo suave
                opt.style.color = "#991b1b";
            } else {
                opt.style.backgroundColor = "";
                opt.style.color = "";
            }
        } else {
            opt.style.backgroundColor = "";
            opt.style.color = "";
        }
    });
    _libActualizarColorSelectPropio();
}
window._libActualizarColoresSelect = _libActualizarColoresSelect;
/**
 * Colorea el select en sí de acuerdo al valor y estado actual.
 */
function _libActualizarColorSelectPropio() {
    const select = document.getElementById("lib-tipo");
    if (!select) return;
    select.style.backgroundColor = "";
    select.style.color = "";
    select.style.borderColor = "#cbd5e1"; // neutral border
}
window._libActualizarColorSelectPropio = _libActualizarColorSelectPropio;
/**
 * Rellena los inputs con un objeto "lib" especifico de un tipo de modelo.
 */
function _libRellenarInputs(lib) {
    if (!lib) return;
    try {
        // Modelo (Macho/Hembra): id = lib-modelo-{ITEM}-{col}
        if (lib.medidas_modelo && typeof lib.medidas_modelo === "object") {
            Object.entries(lib.medidas_modelo).forEach(([item, cols]) => {
                if (!cols) return;
                ["dibujo", "macho", "hembra"].forEach((col) => {
                    const inp = document.getElementById(
                        `lib-modelo-${item}-${col}`,
                    );
                    if (inp && cols[col] != null) inp.value = cols[col];
                });
            });
        }
        // Plantilla/Templadera (Matriz): clave = "{row}_{col}", id = lib-plt-{row}-{col}-{dim}
        if (
            lib.medidas_plantilla &&
            typeof lib.medidas_plantilla === "object"
        ) {
            Object.entries(lib.medidas_plantilla).forEach(([key, cols]) => {
                if (!cols) return;
                // key formato: "plantilla_V", "templadera_x1", etc.
                const m = key.match(/^(plantilla|templadera)_(.+)$/);
                if (!m) return;
                const [, row, col] = m;
                ["dibujo", "fisico"].forEach((dim) => {
                    const inp = document.getElementById(
                        `lib-plt-${row}-${col}-${dim}`,
                    );
                    if (inp && cols[dim] != null) inp.value = cols[dim];
                });
            });
        }
        // Fondo: id = lib-fondo-{key}-{dim}
        if (lib.medidas_fondo && typeof lib.medidas_fondo === "object") {
            Object.entries(lib.medidas_fondo).forEach(([item, cols]) => {
                if (!cols) return;
                ["dibujo", "fisico"].forEach((dim) => {
                    const inp = document.getElementById(
                        `lib-fondo-${item}-${dim}`,
                    );
                    if (inp && cols[dim] != null) inp.value = cols[dim];
                });
            });
        }
        // Obturador: id = lib-obturador-{key}-{dim}
        if (
            lib.medidas_obturador &&
            typeof lib.medidas_obturador === "object"
        ) {
            Object.entries(lib.medidas_obturador).forEach(([item, cols]) => {
                if (!cols) return;
                ["dibujo", "fisico"].forEach((dim) => {
                    const inp = document.getElementById(
                        `lib-obturador-${item}-${dim}`,
                    );
                    if (inp && cols[dim] != null) inp.value = cols[dim];
                });
            });
        }
        const obsModelo = document.getElementById("lib-obs-modelo");
        const obsPlantilla = document.getElementById("lib-obs-plantilla");
        const obsFondo = document.getElementById("lib-obs-fondo");
        const obsObturador = document.getElementById("lib-obs-obturador");
        const rechEl = document.getElementById("lib-motivo-rechazo");
        if (obsModelo) obsModelo.value = lib.observaciones_modelo || "";
        if (obsPlantilla)
            obsPlantilla.value = lib.observaciones_plantilla || "";
        if (obsFondo) obsFondo.value = lib.observaciones_fondo || "";
        if (obsObturador)
            obsObturador.value = lib.observaciones_obturador || "";
        if (rechEl) rechEl.value = lib.motivo_rechazo || "";
        // Truncar y formatear todos los campos numericos despues de cargar
        document
            .querySelectorAll(".lib-num-input, .lib-num-input-sm")
            .forEach((inp) => {
                formatInputTruncated(inp);
            });
    } catch (err) {
        console.error("Error al rellenar inputs de liberacion:", err);
    }
}
// ── Envio del formulario ──────────────────────────────────────────────────────
/**
 * Antes del submit, rellena con 0.000 todos los inputs de tablas ocultas
 * para garantizar consistencia en la base de datos.
 */
function _libZeroFillOcultos() {
    document.querySelectorAll('input[data-lib-hidden="1"]').forEach((inp) => {
        inp.value = "0.000";
    });
    // Limpiar observaciones de las tablas ocultas
    const t1 = document.getElementById("lib-tabla-1");
    if (t1 && t1.classList.contains("cal-display-none")) {
        const obs = document.getElementById("lib-obs-modelo");
        if (obs) obs.value = "";
    }
    const t2 = document.getElementById("lib-tabla-2");
    if (t2 && t2.classList.contains("cal-display-none")) {
        const obs = document.getElementById("lib-obs-plantilla");
        if (obs) obs.value = "";
    }
    const tf = document.getElementById("lib-tabla-fondo");
    if (tf && tf.classList.contains("cal-display-none")) {
        const obs = document.getElementById("lib-obs-fondo");
        if (obs) obs.value = "";
    }
    const to = document.getElementById("lib-tabla-obturador");
    if (to && to.classList.contains("cal-display-none")) {
        const obs = document.getElementById("lib-obs-obturador");
        if (obs) obs.value = "";
    }
}
/**
 * Envia el formulario de liberacion al backend.
 *
 * @param {'guardar'|'accion'} accion
 */
async function _libSubmit(accion) {
    const ot = document.getElementById("lib-ot")?.value;
    if (!ot) return;
    // Validacion del select tipo
    const tipoVal = document.getElementById("lib-tipo")?.value;
    if (!tipoVal) {
        almacenToast(
            "Selecciona el Tipo de Modelo antes de continuar.",
            "error",
        );
        return;
    }
    const activeDecisionEl = document.querySelector(
        ".lib-decision-card.active",
    );
    const decisionVal =
        activeDecisionEl && activeDecisionEl.id === "lib-dec-rechazar"
            ? "rechazar"
            : "aprobar";
    // Validacion obligatoria de motivo de rechazo
    if (decisionVal === "rechazar") {
        const motivo = document
            .getElementById("lib-motivo-rechazo")
            ?.value?.trim();
        if (!motivo) {
            almacenToast(
                'El campo "Motivo de Rechazo" es obligatorio para rechazar la liberacion.',
                "error",
            );
            document.getElementById("lib-motivo-rechazo")?.focus();
            return;
        }
    }
    // Rellenar con 0.000 los campos de tablas que no aplican al tipo seleccionado
    _libZeroFillOcultos();
    // Truncar y formatear todos los inputs a 3 decimales
    document
        .querySelectorAll(".lib-num-input, .lib-num-input-sm")
        .forEach((inp) => {
            formatInputTruncated(inp);
        });
    const form = document.getElementById("formLiberacion");
    const currentFormState = new URLSearchParams(new FormData(form)).toString();
    // Verificar si no hay cambios y es un rechazo ya guardado
    if (accion === "accion" && decisionVal === "rechazar") {
        const cached =
            window.cacheLiberacionGlobal &&
            window.cacheLiberacionGlobal[tipoVal];
        const isAlreadyRejected = cached && cached.decision === "rechazar";
        if (
            isAlreadyRejected &&
            window._libLastSavedState === currentFormState
        ) {
            // Abrir SCAR directamente sin descargar de nuevo el PDF
            const motivoRechazo =
                document.getElementById("lib-motivo-rechazo")?.value || "";
            cerrarModalLiberacion();
            if (typeof window.abrirModalScar === "function") {
                window.abrirModalScar(ot, tipoVal, motivoRechazo);
            }
            return;
        }
    }
    const fd = new FormData(form);
    fd.set("accion", accion === "accion" ? decisionVal : accion);
    fd.set("decision", decisionVal);
    fd.set("ot", ot);
    // Bloquear botones durante la peticion
    const btns = document.querySelectorAll("#lib-actions button");
    btns.forEach((b) => {
        b.disabled = true;
    });
    try {
        const resp = await fetch(window.almacenRoutes.submitLiberacion, {
            method: "POST",
            headers: {
                Accept: "application/json",
                "X-CSRF-TOKEN":
                    document.querySelector('meta[name="csrf-token"]')
                        ?.content ?? "",
                "X-Requested-With": "XMLHttpRequest",
            },
            body: fd,
        });
        const data = await resp.json();
        if (data.success) {
            almacenToast(data.message, "success");
            // LIMPIAR BORRADOR TRAS ÉXITO
            window.clearLiberacionDraft();
            // Descargar PDF automaticamente con nombre estetico
            if (data.pdf_url) {
                const enlace = document.createElement("a");
                enlace.href = data.pdf_url;
                enlace.download = data.pdf_filename ?? "Liberacion_Modelos.pdf";
                enlace.classList.add("cal-display-none");
                document.body.appendChild(enlace);
                enlace.click();
                document.body.removeChild(enlace);
            }
            if (accion === "guardar") {
                // Actualizar badge de estado en tabla sin recargar pagina
                if (data.ot && data.nuevo_estado) {
                    _libActualizarBadgeEstado(data.ot, data.nuevo_estado);
                }
                setTimeout(() => {
                    cerrarModalLiberacion();
                    window.location.reload();
                }, 1800);
            } else {
                // ── Máquina de estados: disparar evento de liberación final ──
                const otFinal = data.ot || ot;
                document.dispatchEvent(
                    new CustomEvent("modeloLiberado", {
                        detail: { ot: otFinal, accion },
                    }),
                );
                // ── Si fue un RECHAZO: abrir modal SCAR prellenado ──────────
                const activeDecisionEl = document.querySelector(
                    ".lib-decision-card.active",
                );
                const esRechazoPorDecision =
                    document.getElementById("lib-accion")?.value ===
                        "rechazar" ||
                    (activeDecisionEl &&
                        activeDecisionEl.id === "lib-dec-rechazar");
                // También detectar por la decisión enviada al servidor
                const decisionFD = fd.get("decision");
                const esRechazoFinal =
                    esRechazoPorDecision || decisionFD === "rechazar";
                if (
                    esRechazoFinal &&
                    typeof window.abrirModalScar === "function"
                ) {
                    const tipoModelo =
                        document.getElementById("lib-tipo")?.value || "";
                    const motivoRechazo =
                        document.getElementById("lib-motivo-rechazo")?.value ||
                        "";
                    // Pequeno delay para que el PDF se descargue primero
                    setTimeout(() => {
                        cerrarModalLiberacion();
                        window.abrirModalScar(
                            otFinal,
                            tipoModelo,
                            motivoRechazo,
                        );
                    }, 600);
                } else {
                    setTimeout(() => {
                        cerrarModalLiberacion();
                        window.location.reload();
                    }, 1800);
                }
            }
            // Actualizar ultimo estado guardado
            window._libLastSavedState = currentFormState;
        } else {
            almacenToast(
                data.message || "Ocurrio un error inesperado.",
                "error",
            );
        }
    } catch (err) {
        console.error("Error al enviar liberacion:", err);
        almacenToast("Error de red al enviar el formulario.", "error");
    } finally {
        btns.forEach((b) => {
            b.disabled = false;
        });
    }
}
// ═══════════════════════════════════════════════════════════════════════════════
// ── MÁQUINA DE ESTADOS VISUAL — Estado del Modelo  (v4 — FSM Completa) ─────────
// ═══════════════════════════════════════════════════════════════════════════════
/**
 * ModeloStateMachine (v4)
 * ─────────────────────────────────────────────────────────────────────────────
 * FSM de 8 estados exactos en 3 niveles jerárquicos.
 *
 * REGLA DE ORO: Una vez alcanzado un nivel, los estados de nivel inferior
 * son ignorados. La transición solo avanza, nunca retrocede.
 *
 * ┌───────┬────────────┬──────────────┬──────────────────────────────────────┐
 * │ NIVEL │ Estado     │ Imagen       │ Disparador                           │
 * ├───────┼────────────┼──────────────┼──────────────────────────────────────┤
 * │   1   │ recibido   │ Recibido.png │ Alerta inicial del servidor          │
 * │   1   │ revisando  │ Revisando.png│ Clic en "Ver Archivos"               │
 * │   1   │ editando   │ Editando.png │ Clic en "Aprobar/Rechazar Lib."      │
 * ├───────┼────────────┼──────────────┼──────────────────────────────────────┤
 * │   2   │ guardado   │ Guardado.png │ Clic en "Guardar"                    │
 * │   2   │ descargado │ Descarga.png │ PDF generado y descargado            │
 * │   2   │ espera     │ documento.png│ Correo enviado / Dpto. confirmó      │
 * ├───────┼────────────┼──────────────┼──────────────────────────────────────┤
 * │   3   │ aprobado   │ Aprobado.png │ Liberación aprobada (servidor)       │
 * │   3   │ rechazado  │ Rechazado.png│ Liberación rechazada (servidor)      │
 * └───────┴────────────┴──────────────┴──────────────────────────────────────┘
 */
const ModeloStateMachine = (() => {
    function _baseUrl() {
        let b = window.baseUrl || window.location.origin + "/";
        return b.endsWith("/") ? b : b + "/";
    }
    // ── Registro de estados ───────────────────────────────────────────────────
    const ESTADOS = {
        recibido: {
            img: "Recibido.png",
            label: "Nuevo",
            title: "Alerta inicial recibida, pendiente de procesar modelo por Almacén",
            borderColor: "#cbd5e1",
            bgColor: "#f1f5f9",
            textColor: "#64748b",
            nivel: 1,
            prio: 1,
        },
        pre_orden: {
            img: "pdf-view.png",
            label: "Pre-Orden",
            title: "Pre-orden de modelo generada y guardada, pendiente de enviar",
            borderColor: "#60a5fa",
            bgColor: "#eff6ff",
            textColor: "#2563eb",
            nivel: 3,
            prio: 2,
        },
        correo_enviado: {
            img: "enviando.png",
            label: "Correo Enviado",
            title: "Pre-orden enviada por correo electrónico, esperando revisión de Calidad",
            borderColor: "#818cf8",
            bgColor: "#e0e7ff",
            textColor: "#4f46e5",
            nivel: 2,
            prio: 3,
        },
        tiene_modelo: {
            img: "Espera.png",
            label: "Tengo Modelo",
            title: "Modelo físico disponible en Almacén, en espera de revisión por Calidad",
            borderColor: "#0ea5e9",
            bgColor: "#f0f9ff",
            textColor: "#0369a1",
            nivel: 3,
            prio: 4,
        },
        revisando: {
            img: "Revisando.png",
            label: "En Revisión",
            title: "Calidad está realizando la revisión del modelo",
            borderColor: "#f59e0b",
            bgColor: "#fffbeb",
            textColor: "#b45309",
            nivel: 2,
            prio: 5,
        },
        aprobado: {
            img: "Quality.png",
            label: "Aprobado",
            title: "Modelo aprobado y liberado por Calidad",
            borderColor: "#10b981",
            bgColor: "#ecfdf5",
            textColor: "#047857",
            nivel: 3,
            prio: 99,
        },
        aprobado_final: {
            img: "Aprobado.png",
            label: "Aprobado",
            title: "Proceso de modelo y casting finalizado y aprobado",
            borderColor: "#15803d",
            bgColor: "#f0fdf4",
            textColor: "#15803d",
            nivel: 3,
            prio: 100,
        },
        casting_aprobado: {
            img: "Proveedor.png",
            label: "Enviado a Proveedor",
            title: "Pre-orden de casting enviada al proveedor, proceso finalizado",
            borderColor: "#9333ea",
            bgColor: "#f3e8ff",
            textColor: "#9333ea",
            nivel: 3,
            prio: 100,
        },
        rechazado: {
            img: "Quality.png",
            label: "Rechazado",
            title: "Modelo rechazado por Calidad debido a desviaciones",
            borderColor: "#ef4444",
            bgColor: "#fef2f2",
            textColor: "#b91c1c",
            nivel: 3,
            prio: 99,
        },
        rechazado_final: {
            img: "Rechazado.png",
            label: "Rechazado",
            title: "Modelo rechazado y reproceso iniciado por Almacén",
            borderColor: "#dc2626",
            bgColor: "#fef2f2",
            textColor: "#b91c1c",
            nivel: 3,
            prio: 100,
        },
        mixto: {
            img: "Quality.png",
            label: "Mixto",
            title: "Liberación mixta por Calidad (clases aprobadas y rechazadas)",
            borderColor: "#eab308",
            bgColor: "#fef9c3",
            textColor: "#854d0e",
            nivel: 3,
            prio: 99,
        },
        casting: {
            img: "pdf-view.png",
            label: "Casting",
            title: "Pre-orden de casting generada y aprobada",
            borderColor: "#059669",
            bgColor: "#f0fdf4",
            textColor: "#15803d",
            nivel: 3,
            prio: 99,
        },
        reproceso: {
            img: "Reproceso.png",
            label: "Reproceso",
            title: "Retornado hacia un nuevo ciclo de modelo (Reproceso)",
            borderColor: "#ec4899",
            bgColor: "#fdf2f8",
            textColor: "#be185d",
            nivel: 1,
            prio: 1,
        },
    };
    /** Mapa alias → estado canónico para la caché interna */
    const _CANONICAL = {
        editando: "revisando",
        guardado: "revisando",
        descargado: "revisando",
        pendiente: "revisando",
        en_proceso: "revisando",
        espera: "tiene_modelo",
        enviando: "correo_enviado",
        documento: "tiene_modelo",
    };
    /** Caché: ot → estado canónico actual */
    const _cache = {};
    // ── Aplicar estado al DOM ─────────────────────────────────────────────────
    function _render(ot, estado, cfg) {
        const el =
            document.getElementById(`status-modelo-${ot}`) ||
            document.getElementById(
                `status-modelo-${ot.replace(/_R\d+$/i, "")}`,
            );
        if (!el) {
            console.warn(
                `[FSM] Contenedor no encontrado: #status-modelo-${ot}`,
            );
            return;
        }
        const src = _baseUrl() + "images/" + cfg.img;
        el.innerHTML = `
<div class="status-modelo-container" style="display: inline-flex; flex-direction: column; align-items: center; gap: 2px; padding: 6px; border-radius: 8px;">
<span class="badge-modelo-icon" title="${cfg.title}" style="display: flex; align-items: center; justify-content: center; width: 52px; height: 52px; border-radius: 50%; background: ${cfg.bgColor}; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); border: 2px solid ${cfg.borderColor}; transition: all 0.2s ease;">
<img src="${src}" alt="${cfg.label}" style="width: 34px; height: 34px; object-fit: contain;">
</span>
<span class="status-modelo-label" style="font-size: 11px; font-weight: 700; color: ${cfg.textColor}; margin-top: 4px; text-transform: uppercase; white-space: nowrap;">
${cfg.label}
</span>
</div>
`;
        console.info(`[FSM] "${ot}": → ${estado} (nivel ${cfg.nivel})`);
    }
    // ── Transición normal (respeta jerarquía) ─────────────────────────────────
    function transicion(ot, estado) {
        const canonical = _CANONICAL[estado] ?? estado;
        const cfg = ESTADOS[canonical];
        if (!cfg) {
            console.warn(
                `[FSM] Estado desconocido: "${estado}" (canonical: "${canonical}")`,
            );
            return false;
        }
        const actual = _cache[ot];
        const cfgActual = actual ? ESTADOS[actual] : null;
        // Regla 1 — Terminales son permanentes
        if (cfgActual?.nivel === 3) {
            console.info(
                `[FSM] "${ot}": BLOQUEADO — terminal "${actual}" (→ ${estado})`,
            );
            return false;
        }
        // Regla 2 — No retroceder prioridad
        if (cfg.prio <= (cfgActual?.prio ?? 0)) {
            console.info(
                `[FSM] "${ot}": BLOQUEADO — retroceso (${actual}[${cfgActual?.prio}] → ${estado}[${cfg.prio}])`,
            );
            return false;
        }
        _cache[ot] = canonical;
        _render(ot, canonical, cfg);
        return true;
    }
    // ── Forzar terminal (solo desde servidor) ─────────────────────────────────
    function _forzarTerminal(ot, estado) {
        const canonical = _CANONICAL[estado] ?? estado;
        const cfg = ESTADOS[canonical];
        if (!cfg || cfg.nivel !== 3) {
            console.warn(`[FSM] _forzarTerminal: "${estado}" no es terminal`);
            return false;
        }
        _cache[ot] = canonical;
        _render(ot, canonical, cfg);
        console.info(`[FSM] "${ot}": TERMINAL FORZADO → ${estado} ★`);
        return true;
    }
    // ── Sincronización desde DOM ──────────────────────────────────────────────
    function init() {
        document.querySelectorAll('[id^="status-modelo-"]').forEach((el) => {
            const ot = el.id.replace("status-modelo-", "");
            const labelEl = el.querySelector(".status-modelo-label");
            if (labelEl && !_cache[ot]) {
                const txt = labelEl.textContent.trim().toUpperCase();
                const imgEl = el.querySelector("img");
                const imgSrc = imgEl ? imgEl.src.toUpperCase() : "";
                let estado = "recibido";
                if (txt === "RECIBIDO" || txt === "NUEVO") estado = "recibido";
                else if (txt === "PRE-ORDEN") estado = "pre_orden";
                else if (txt === "CORREO ENVIADO") estado = "correo_enviado";
                else if (txt === "TENGO MODELO") estado = "tiene_modelo";
                else if (txt === "EN REVISIÓN") estado = "revisando";
                else if (txt === "APROBADO") {
                    if (imgSrc.includes("APROBADO.PNG")) {
                        estado = "aprobado_final";
                    } else {
                        estado = "aprobado";
                    }
                } else if (txt === "ENVIADO A PROVEEDOR") {
                    estado = "casting_aprobado";
                } else if (txt === "RECHAZADO") {
                    if (imgSrc.includes("RECHAZADO.PNG")) {
                        estado = "rechazado_final";
                    } else {
                        estado = "rechazado";
                    }
                } else if (txt === "MIXTO") estado = "mixto";
                else if (txt === "CASTING") estado = "casting";
                else if (txt === "REPROCESO") estado = "reproceso";
                _cache[ot] = estado;
                console.info(`[FSM] init: "${ot}" → ${estado}`);
            }
        });
    }
    function getEstado(ot) {
        return _cache[ot] ?? null;
    }
    function getNivel(ot) {
        return ESTADOS[_cache[ot]]?.nivel ?? 0;
    }
    function onAlertaEnviada(ot) {
        transicion(ot, "recibido");
    }
    function onVerArchivos(ot) {
        transicion(ot, "revisando");
    }
    function onAbrirDecision(ot) {
        transicion(ot, "editando");
    }
    function onGuardar(ot) {
        transicion(ot, "guardado");
    }
    function onDescargado(ot) {
        transicion(ot, "descargado");
    }
    function onCorreoEnviado(ot) {
        transicion(ot, "correo_enviado");
    }
    function onConfirmarModelo(ot) {
        transicion(ot, "tiene_modelo");
    }
    function onEnEspera(ot) {
        transicion(ot, "tiene_modelo");
    }
    function onAprobado(ot) {
        _forzarTerminal(ot, "aprobado");
    }
    function onRechazado(ot) {
        _forzarTerminal(ot, "rechazado");
    }
    return {
        transicion,
        _forzarTerminal,
        init,
        getEstado,
        getNivel,
        onAlertaEnviada,
        onVerArchivos,
        onAbrirDecision,
        onGuardar,
        onDescargado,
        onCorreoEnviado,
        onConfirmarModelo,
        onEnEspera,
        onAprobado,
        onRechazado,
        ESTADOS,
    };
})();
window.ModeloStateMachine = ModeloStateMachine;
// ═══════════════════════════════════════════════════════════════════════════════
// ── HOOKS — Integración con el DOM existente ───────────────────────────────────
// ═══════════════════════════════════════════════════════════════════════════════
/**
 * INIT — Sincroniza el caché con el estado inicial servido por Blade.
 * Se ejecuta en DOMContentLoaded para leer las imágenes ya renderizadas.
 */
document.addEventListener("DOMContentLoaded", () => ModeloStateMachine.init());
/**
 * HOOK 1 — _libActualizarBadgeEstado
 * Puente de compatibilidad con el callback del backend (accion='guardar').
 * pendiente → guardado | aprobado → aprobado | rechazado → rechazado
 */
(function _hookLibBadge() {
    window._libActualizarBadgeEstado = function (ot, nuevoEstado) {
        const mapa = {
            pendiente: "guardado",
            guardado: "guardado",
            en_proceso: "guardado",
        };
        const estado = mapa[nuevoEstado] ?? nuevoEstado;
        const esTerminal =
            nuevoEstado === "aprobado" || nuevoEstado === "rechazado";
        if (esTerminal) ModeloStateMachine._forzarTerminal(ot, estado);
        else ModeloStateMachine.transicion(ot, estado);
    };
})();
/**
 * HOOK 2 — btn-toggle-files → revisando (Nivel 1)
 * Solo si el panel se está ABRIENDO y el nivel actual < 2.
 */
(function _hookToggleFiles() {
    document.addEventListener(
        "click",
        (e) => {
            const btn = e.target.closest(".btn-toggle-files");
            if (!btn) return;
            const ot = btn.dataset.ot;
            if (!ot) return;
            // Solo disparar si el nivel actual es < 2 (no sobreescribir permanentes/terminales)
            if (ModeloStateMachine.getNivel(ot) >= 2) return;
            const panel = document.getElementById(btn.dataset.target);
            const estaAbierto = panel?.classList.contains("open");
            if (!estaAbierto) ModeloStateMachine.onVerArchivos(ot);
        },
        true,
    );
})();
/**
 * HOOK 3 — abrirModalLiberacion → editando (Nivel 1)
 * Solo si el nivel actual es < 2.
 */
(function _hookAbrirModal() {
    const _orig = window.abrirModalLiberacion;
    window.abrirModalLiberacion = function (ot, tipo) {
        if (ModeloStateMachine.getNivel(ot) < 2) {
            ModeloStateMachine.onAbrirDecision(ot);
        }
        return _orig.call(this, ot, tipo);
    };
})();
/**
 * HOOK 4 — Botones de #lib-actions (MutationObserver)
 *   lib-btn-guardar  → guardado   (Nivel 2, click inmediato)
 *   lib-btn-accion   → espera     (Nivel 2, correo enviado de forma optimista)
 *
 * El estado definitivo aprobado/rechazado llega por el evento 'modeloLiberado'.
 */
(function _hookLibActions() {
    const obs = new MutationObserver(() => {
        const btnGuardar = document.getElementById("lib-btn-guardar");
        if (btnGuardar && !btnGuardar.dataset.fsmHooked) {
            btnGuardar.dataset.fsmHooked = "1";
            btnGuardar.addEventListener(
                "click",
                () => {
                    const ot = document.getElementById("lib-ot")?.value;
                    if (ot) ModeloStateMachine.onGuardar(ot);
                },
                true,
            );
        }
        const btnAccion = document.getElementById("lib-btn-accion");
        if (btnAccion && !btnAccion.dataset.fsmHooked) {
            btnAccion.dataset.fsmHooked = "1";
        }
    });
    document.addEventListener("DOMContentLoaded", () => {
        const actionsEl = document.getElementById("lib-actions");
        if (actionsEl) obs.observe(actionsEl, { childList: true });
    });
})();
/**
 * HOOK 5 — URL.createObjectURL (detección de descarga de PDF)
 * Cuando la liberación genera un PDF blob, la prioridad avanza a "descargado".
 * Solo se activa si el modal de liberación (#lib-ot) está activo.
 */
(function _hookPdfDescarga() {
    const _origCreate = URL.createObjectURL;
    URL.createObjectURL = function (blob) {
        const url = _origCreate.call(URL, blob);
        try {
            const ot = document.getElementById("lib-ot")?.value;
            if (ot && blob?.type === "application/pdf") {
                // Pequeño delay para que el anchor de descarga se procese primero
                setTimeout(() => ModeloStateMachine.onDescargado(ot), 200);
            }
        } catch (_) {
            /* silenciar errores no relacionados */
        }
        return url;
    };
})();
/**
 * HOOK 6 — confirmarModelo (Vista Almacén)
 * Cuando Almacén confirma el modelo físico → espera (Nivel 2).
 */
(function _hookConfirmarModelo() {
    window.confirmarModelo = function (ot, id_hash) {
        if (
            !confirm(
                `¿Confirmas que actualmente cuentas con el modelo físico para la OT ${ot}?`,
            )
        )
            return;
        fetch(window.almacenRoutes.confirmarModelo, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content"),
            },
            body: JSON.stringify({ ot }),
        })
            .then((r) => r.json())
            .then((data) => {
                if (data.success) {
                    mostrarToast(data.message);
                    ModeloStateMachine.onConfirmarModelo(ot);
                    if (id_hash) {
                        const container = document.getElementById(
                            "control-modelo-" + id_hash,
                        );
                        if (container) {
                            container.style.opacity = "0.5";
                            container.style.pointerEvents = "none";
                        }
                    }
                } else {
                    mostrarToast(
                        data.message || "Error al actualizar estado",
                        true,
                    );
                }
            })
            .catch((err) => {
                console.error(err);
                mostrarToast("Error de conexión", true);
            });
    };
})();
/**
 * HOOK 7 — evento 'modeloLiberado' (disparado por _libSubmit tras éxito del servidor)
 * Actualiza al estado terminal definitivo (aprobado/rechazado),
 * sobreescribiendo el "espera" provisional del HOOK 4.
 */
(function _hookModeloLiberado() {
    document.addEventListener("modeloLiberado", (e) => {
        const { ot, accion } = e.detail ?? {};
        if (!ot || !accion) return;
        if (accion === "aprobar") ModeloStateMachine.onAprobado(ot);
        if (accion === "rechazar") ModeloStateMachine.onRechazado(ot);
    });
})();
// =========================================================================
// MODAL SCAR (Solicitud de Acción Correctiva de Rechazo)
// =========================================================================
/**
 * Abre el modal del formato SCAR pre-llenando los datos del rechazo.
 */
window.abrirModalScar = function (ot, tipoModelo, motivoRechazo) {
    const modal = document.getElementById("modalScar");
    if (!modal) return;
    // Resetear formulario para no conservar datos previos
    const formEl = document.getElementById("formScar");
    if (formEl) formEl.reset();
    // Reset local files arrays
    scarFotosSelectedFiles = [];
    scarOtrosSelectedFiles = [];
    renderScarFotosBadges();
    renderScarOtrosBadges();
    // Extraer numero de OT y nombre de la moldura de forma automatica
    // Formato esperado: "OT 6748 - TEREMANA 1000 ML" o similar, ignorando sufijos de timestamp
    let otNumber = "";
    let molduraName = "";
    const cleanOt = ot
        .replace(/_[rR]?\d{8}_\d{6}_.*/, "")
        .replace(/_[rR]?\d+$/, "");
    // Extract only digits for otNumber (e.g. 2101)
    const numMatch = cleanOt.match(/\d+/);
    if (numMatch) {
        otNumber = numMatch[0];
    } else {
        otNumber = cleanOt;
    }
    const match = cleanOt.match(/^(?:OT\s*)?\d+\s*-\s*(.*)$/i);
    if (match) {
        molduraName = match[1].trim();
    }
    // Mostrar datos en el modal
    const otInput = document.getElementById("scar-ot");
    if (otInput) otInput.value = ot;
    const otDisplay = document.getElementById("scar-ot-display");
    if (otDisplay) otDisplay.textContent = cleanOt;
    const molduraInput = document.getElementById("scar-nombre-moldura");
    if (molduraInput) molduraInput.value = molduraName;
    const codigoInput = document.getElementById("scar-codigo-modelo");
    if (codigoInput) {
        let prefix = "F"; // Default fallback
        if (tipoModelo) {
            const tLow = tipoModelo.toLowerCase();
            if (tLow.includes("templadera")) {
                if (tLow.includes("obturador")) prefix = "TO";
                else if (tLow.includes("molde")) prefix = "TM";
                else if (tLow.includes("fondo")) prefix = "TF";
                else if (tLow.includes("bombillo")) prefix = "TB";
                else prefix = "T";
            } else {
                if (tLow === "bombillo" || tLow.includes("bombillo"))
                    prefix = "B";
                else if (tLow === "obturador" || tLow.includes("obturador"))
                    prefix = "O";
                else if (tLow === "molde" || tLow.includes("molde"))
                    prefix = "M";
                else if (tLow === "fondo" || tLow.includes("fondo"))
                    prefix = "F";
                else if (tLow.includes("cabeza") && tLow.includes("soplo"))
                    prefix = "CS";
                else {
                    prefix = tipoModelo.charAt(0).toUpperCase();
                }
            }
        }
        codigoInput.value = otNumber ? prefix + otNumber : "";
    }
    const tipoInput = document.getElementById("scar-tipo");
    if (tipoInput) tipoInput.value = tipoModelo || "";
    const tipoDisplay = document.getElementById("scar-tipo-display");
    if (tipoDisplay) tipoDisplay.textContent = tipoModelo || "General";
    const motivoInput = document.getElementById("scar-motivo");
    if (motivoInput) motivoInput.value = motivoRechazo || "";
    const descTextarea = document.getElementById("scar-descripcion");
    if (descTextarea) descTextarea.value = motivoRechazo || "";
    // Checkboxes seleccionados de cajón/por defecto
    const defaultChkDibujos = document.getElementById("scar-evidencia-dibujos");
    if (defaultChkDibujos) defaultChkDibujos.checked = true;
    const defaultChkAyudas = document.getElementById("scar-evidencia-ayudas");
    if (defaultChkAyudas) defaultChkAyudas.checked = true;
    // Fetch existing SCAR data if any
    fetch(
        `${window.almacenRoutes.getScar}?ot=${encodeURIComponent(ot)}&tipo_modelo=${encodeURIComponent(tipoModelo || "")}`,
    )
        .then((res) => res.json())
        .then((data) => {
            if (data.success) {
                if (data.preorden_codigo_modelo) {
                    if (codigoInput)
                        codigoInput.value = data.preorden_codigo_modelo;
                } else {
                    // Recalcular dinámicamente con los datos de clase reales del servidor si no hay preorden
                    let prefix = "F";
                    const tLow = (tipoModelo || "").toLowerCase();
                    const cLow = (data.clase_nombre || "").toLowerCase();
                    const esTempladera =
                        data.es_templadera ||
                        tLow.includes("templadera") ||
                        cLow.includes("templadera");
                    if (esTempladera) {
                        if (
                            tLow.includes("obturador") ||
                            cLow.includes("obturador")
                        )
                            prefix = "TO";
                        else if (
                            tLow.includes("molde") ||
                            cLow.includes("molde")
                        )
                            prefix = "TM";
                        else if (
                            tLow.includes("fondo") ||
                            cLow.includes("fondo")
                        )
                            prefix = "TF";
                        else if (
                            tLow.includes("bombillo") ||
                            cLow.includes("bombillo")
                        )
                            prefix = "TB";
                        else prefix = "T";
                    } else {
                        if (tLow === "bombillo" || cLow.includes("bombillo"))
                            prefix = "B";
                        else if (
                            tLow === "obturador" ||
                            cLow.includes("obturador")
                        )
                            prefix = "O";
                        else if (tLow === "molde" || cLow.includes("molde"))
                            prefix = "M";
                        else if (tLow === "fondo" || cLow.includes("fondo"))
                            prefix = "F";
                        else if (
                            cLow.includes("cabeza") &&
                            cLow.includes("soplo")
                        )
                            prefix = "CS";
                        else {
                            prefix = (data.clase_nombre || tipoModelo || "F")
                                .charAt(0)
                                .toUpperCase();
                        }
                    }
                    if (codigoInput) {
                        codigoInput.value = otNumber ? prefix + otNumber : "";
                    }
                }
                if (data.scar) {
                    const s = data.scar;
                    if (s.cliente_empresa)
                        document.getElementById("scar-cliente-empresa").value =
                            s.cliente_empresa;
                    if (s.area_solicitante)
                        document.getElementById("scar-area-solicitante").value =
                            s.area_solicitante;
                    if (s.nombre_solicitante)
                        document.getElementById(
                            "scar-nombre-solicitante",
                        ).value = s.nombre_solicitante;
                    if (s.nombre_moldura)
                        document.getElementById("scar-nombre-moldura").value =
                            s.nombre_moldura;
                    if (s.proveedor)
                        document.getElementById("scar-proveedor").value =
                            s.proveedor;
                    if (s.descripcion_no_conformidad)
                        document.getElementById("scar-descripcion").value =
                            s.descripcion_no_conformidad;
                    if (s.causa_raiz)
                        document.getElementById("scar-causa-raiz").value =
                            s.causa_raiz;
                    if (s.acciones_correctivas)
                        document.getElementById("scar-acciones").value =
                            s.acciones_correctivas;
                    if (s.codigo_modelo)
                        document.getElementById("scar-codigo-modelo").value =
                            s.codigo_modelo;
                    // Checkboxes y sus contenedores correspondientes
                    const chkDibujos = document.getElementById(
                        "scar-evidencia-dibujos",
                    );
                    if (chkDibujos)
                        chkDibujos.checked =
                            s.evidencia_dibujos === undefined
                                ? true
                                : !!s.evidencia_dibujos;
                    const chkAyudas = document.getElementById(
                        "scar-evidencia-ayudas",
                    );
                    if (chkAyudas)
                        chkAyudas.checked =
                            s.evidencia_ayudas === undefined
                                ? true
                                : !!s.evidencia_ayudas;
                    const chkFotos = document.getElementById(
                        "scar-evidencia-fotos",
                    );
                    if (chkFotos) {
                        chkFotos.checked = !!s.evidencia_fotos;
                        const group = document.getElementById(
                            "scar-fotos-upload-group",
                        );
                        if (group)
                            group.classList.toggle(
                                "cal-display-none",
                                !chkFotos.checked,
                            );
                    }
                    const chkOtro = document.getElementById(
                        "scar-evidencia-otro",
                    );
                    if (chkOtro) {
                        chkOtro.checked = !!s.evidencia_otro;
                        const group = document.getElementById(
                            "scar-otro-upload-group",
                        );
                        if (group)
                            group.classList.toggle(
                                "cal-display-none",
                                !chkOtro.checked,
                            );
                    }
                    const chkRegreso = document.getElementById(
                        "scar-accion-regreso",
                    );
                    if (chkRegreso) chkRegreso.checked = !!s.accion_regreso;
                    const chkFabricacion = document.getElementById(
                        "scar-accion-fabricacion",
                    );
                    if (chkFabricacion)
                        chkFabricacion.checked = !!s.accion_fabricacion;
                    const chkAccionOtro =
                        document.getElementById("scar-accion-otro");
                    if (chkAccionOtro) {
                        chkAccionOtro.checked = !!s.accion_otro;
                        const group = document.getElementById(
                            "scar-accion-otro-text-group",
                        );
                        if (group)
                            group.classList.toggle(
                                "cal-display-none",
                                !chkAccionOtro.checked,
                            );
                    }
                    if (s.accion_otro_texto)
                        document.getElementById(
                            "scar-accion-otro-texto",
                        ).value = s.accion_otro_texto;
                }
            }
        })
        .catch((err) => console.error("Error loading SCAR:", err));
    // Cargar evidencias ya subidas al SCAR (fotos y otros)
    const scarServerFilesContainer = document.getElementById(
        "scar-server-files-container",
    );
    if (scarServerFilesContainer) {
        scarServerFilesContainer.innerHTML = `
<div style="text-align: center; padding: 10px; grid-column: 1 / -1;">
<div class="alm-spinner" style="border-top-color: #033966; display: inline-block;"></div>
<span style="color: #64748b; margin-left: 10px;">Obteniendo evidencias guardadas...</span>
</div>
`;
        fetch(`${window.almacenRoutes.archivos}?ot=${encodeURIComponent(ot)}`)
            .then((res) => res.json())
            .then((data) => {
                if (data.existe && data.archivos && data.archivos.length > 0) {
                    let baseUrl =
                        window.baseUrl || window.location.origin + "/";
                    if (!baseUrl.endsWith("/")) baseUrl += "/";
                    const activeClasses = (tipoModelo || "")
                        .toLowerCase()
                        .split(",")
                        .map((s) => s.trim().replace(/[^a-z0-9_\-]/g, "_"))
                        .filter(Boolean);
                    const scarFiles = data.archivos.filter((f) => {
                        const pathLower = f.nombre.toLowerCase();
                        if (!pathLower.includes("documentos_rechazados/"))
                            return false;
                        if (
                            activeClasses.length === 0 ||
                            activeClasses.includes("general")
                        )
                            return true;
                        // Check if the path contains any of the active class folders, e.g. /documentos_rechazados/bombillo/
                        return activeClasses.some((cls) =>
                            pathLower.includes(
                                "/documentos_rechazados/" + cls + "/",
                            ),
                        );
                    });
                    if (scarFiles.length > 0) {
                        scarServerFilesContainer.innerHTML = scarFiles
                            .map((file, index) => {
                                const dispName = file.nombre.split("/").pop();
                                const isImg = file.nombre
                                    .toLowerCase()
                                    .match(/\.(jpg|jpeg|png|gif)$/);
                                const isPdf = file.nombre
                                    .toLowerCase()
                                    .endsWith(".pdf");
                                let iconDefault =
                                    baseUrl + "images/pdf-view-shadow.png";
                                let iconHover = baseUrl + "images/pdf-view.png";
                                if (isImg) {
                                    iconDefault =
                                        baseUrl + "images/galeria-shadow.png";
                                    iconHover = baseUrl + "images/galeria.png";
                                }
                                return `
<div class="dibujos-file-card" style="animation-delay: ${index * 0.05}s;">
<div class="file-icon-wrapper" onclick="calidadVerPdf('${ot}', '${file.nombre}', 'otro')" style="cursor: pointer;" title="Abrir Archivo">
<img src="${iconDefault}" class="file-icon icon-default">
<img src="${iconHover}" class="file-icon icon-hover">
</div>
<div class="file-name" style="cursor: pointer;" title="Abrir Archivo" onclick="calidadVerPdf('${ot}', '${file.nombre}', 'otro')">${dispName}</div>
<div class="file-actions">
<button type="button" class="btn-dibujos btn-dibujos-sm btn-ver" onclick="calidadVerPdf('${ot}', '${file.nombre}', 'otro')">Ver</button>
</div>
</div>
`;
                            })
                            .join("");
                    } else {
                        scarServerFilesContainer.innerHTML = `
<div style="text-align: center; color: #64748b; padding: 15px; font-style: italic; grid-column: 1 / -1;">
No hay evidencias subidas aún para este SCAR.
</div>
`;
                    }
                } else {
                    scarServerFilesContainer.innerHTML = `
<div style="text-align: center; color: #64748b; padding: 15px; font-style: italic; grid-column: 1 / -1;">
No hay evidencias subidas aún para este SCAR.
</div>
`;
                }
            })
            .catch((err) => {
                console.error(err);
                scarServerFilesContainer.innerHTML = `
<div style="text-align: center; color: #ef4444; padding: 15px; font-weight: 600; grid-column: 1 / -1;">
Error al cargar evidencias.
</div>
`;
            });
    }
    modal.classList.add("open");
    document.body.classList.add("modal-open");
};
/**
 * Cierra el modal de SCAR.
 */
window.cerrarModalScar = function () {
    const modal = document.getElementById("modalScar");
    if (modal) modal.classList.remove("open");
    document.body.classList.remove("modal-open");
};
/**
 * Envía el formulario del SCAR para generar el PDF y guardarlo en la BD.
 */
window.scarSubmit = function (accion) {
    const form = document.getElementById("formScar");
    if (!form) return;
    const ot = document.getElementById("scar-ot")?.value;
    if (!ot) {
        mostrarToast("OT es requerida.", true);
        return;
    }
    const btn = document.getElementById("scar-btn-guardar");
    const originalText = btn ? btn.innerHTML : "";
    if (btn) {
        btn.disabled = true;
        btn.innerHTML =
            '<span class="alm-spinner" style="display:inline-block; border-top-color:#ffffff; width:15px; height:15px; margin-right:8px; vertical-align:middle;"></span> Procesando...';
    }
    const formData = new FormData(form);
    formData.delete("fotos[]");
    formData.delete("otros_archivos[]");
    scarFotosSelectedFiles.forEach((file) => {
        formData.append("fotos[]", file);
    });
    scarOtrosSelectedFiles.forEach((file) => {
        formData.append("otros_archivos[]", file);
    });
    formData.append("accion", accion);
    fetch(window.almacenRoutes.generateScar, {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content"),
        },
        body: formData,
    })
        .then((res) => res.json())
        .then((data) => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
            if (data.success) {
                mostrarToast(data.message || "SCAR procesado correctamente.");
                cerrarModalScar();
                if (data.pdf_url) {
                    const link = document.createElement("a");
                    link.href = data.pdf_url;
                    link.download = data.pdf_filename || `SCAR_${ot}.pdf`;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                }
                setTimeout(() => location.reload(), 1500);
            } else {
                mostrarToast(data.message || "Error al procesar SCAR.", true);
            }
        })
        .catch((err) => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
            console.error("Error submitting SCAR:", err);
            mostrarToast("Error de conexión con el servidor.", true);
        });
};
// =========================================================================
// MODAL: ENVIAR ALERTA SCAR (Paso 2)
// =========================================================================
/**
 * Abre el modal para enviar el SCAR firmado al proveedor.
 */
window.abrirModalEnviarScar = function (ot) {
    const modal = document.getElementById("modalEnviarScar");
    if (!modal) return;
    const inputOt = document.getElementById("env-scar-ot");
    if (inputOt) inputOt.value = ot;
    const subtitle = document.getElementById("env-scar-modal-subtitle");
    if (subtitle) {
        subtitle.textContent = `OT: ${ot.replace(/_\d{8}_\d{6}_.*/, "")}`;
    }
    // Resetear formulario
    const form = document.getElementById("formEnviarScar");
    if (form) form.reset();
    const filesContainer = document.getElementById(
        "env-scar-server-files-container",
    );
    if (filesContainer) {
        filesContainer.innerHTML = `
<div style="text-align: center; padding: 10px;">
<div class="alm-spinner" style="border-top-color: #9c0300; display: inline-block;"></div>
<span style="color: #64748b; margin-left: 10px;">Obteniendo archivos del servidor...</span>
</div>
`;
    }
    modal.classList.add("open");
    document.body.classList.add("modal-open");
    // Fetch existing SCAR details to prefill fecha_compromiso
    fetch(`${window.almacenRoutes.getScar}?ot=${encodeURIComponent(ot)}`)
        .then((res) => res.json())
        .then((data) => {
            if (data.success && data.scar) {
                const s = data.scar;
                const fcInput = document.getElementById(
                    "env-scar-fecha-compromiso",
                );
                if (fcInput && s.fecha_compromiso) {
                    fcInput.value = s.fecha_compromiso
                        .split(" ")[0]
                        .split("T")[0];
                }
            }
        })
        .catch((err) => console.error("Error loading SCAR details:", err));
    // Fetch files from server
    fetch(`${window.almacenRoutes.archivos}?ot=${encodeURIComponent(ot)}`)
        .then((res) => res.json())
        .then((data) => {
            if (data.existe && data.archivos && data.archivos.length > 0) {
                let baseUrl = window.baseUrl || window.location.origin + "/";
                if (!baseUrl.endsWith("/")) baseUrl += "/";
                const sectionsHtml = generarHtmlCategorizadoArchivos(
                    data.archivos,
                    ot,
                    baseUrl,
                    "scar",
                );
                if (filesContainer) {
                    filesContainer.innerHTML =
                        sectionsHtml ||
                        `
<div style="text-align: center; color: #64748b; padding: 15px; font-style: italic;">
No se encontraron archivos en el servidor para esta OT.
</div>
`;
                }
            } else {
                if (filesContainer) {
                    filesContainer.innerHTML = `
<div style="text-align: center; color: #64748b; padding: 15px; font-style: italic;">
No se encontraron archivos en el servidor para esta OT.
</div>
`;
                }
            }
        })
        .catch((err) => {
            console.error("Error loading files for SCAR:", err);
            if (filesContainer) {
                filesContainer.innerHTML = `
<div style="text-align: center; color: #ef4444; padding: 15px; font-weight: 600;">
Error al cargar la lista de archivos.
</div>
`;
            }
        });
};
/**
 * Cierra el modal de Enviar SCAR.
 */
window.cerrarModalEnviarScar = function () {
    const modal = document.getElementById("modalEnviarScar");
    if (modal) modal.classList.remove("open");
    document.body.classList.remove("modal-open");
};
(function _initScarEvents() {
    document.addEventListener("DOMContentLoaded", () => {
        const formEnvScar = document.getElementById("formEnviarScar");
        if (formEnvScar) {
            formEnvScar.addEventListener("submit", function (e) {
                e.preventDefault();
                const ot = document.getElementById("env-scar-ot").value;
                const fechaCompromiso = document.getElementById(
                    "env-scar-fecha-compromiso",
                ).value;
                const pdfFirmado = document.getElementById(
                    "env-scar-pdf-firmado",
                ).files[0];
                if (!fechaCompromiso) {
                    mostrarToast(
                        "Por favor, indica la fecha compromiso.",
                        true,
                    );
                    return;
                }
                if (!pdfFirmado) {
                    mostrarToast(
                        "Por favor, sube el SCAR firmado físicamente.",
                        true,
                    );
                    return;
                }
                const btn = this.querySelector('button[type="submit"]');
                const originalText = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML =
                    '<span class="alm-spinner" style="display:inline-block; border-top-color:#ffffff; width:15px; height:15px; margin-right:8px; vertical-align:middle;"></span> Enviando alerta...';
                const formData = new FormData(this);
                formData.delete("archivos_adicionales[]");
                envScarSelectedFiles.forEach((file) => {
                    formData.append("archivos_adicionales[]", file);
                });
                fetch(window.almacenRoutes.sendScarAlert, {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": document
                            .querySelector('meta[name="csrf-token"]')
                            .getAttribute("content"),
                    },
                    body: formData,
                })
                    .then((res) => res.json())
                    .then((data) => {
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                        if (data.success) {
                            mostrarToast(
                                data.message ||
                                    "Alerta SCAR firmada enviada con éxito.",
                            );
                            cerrarModalEnviarScar();
                            if (window.ModeloStateMachine) {
                                window.ModeloStateMachine.onCorreoEnviado(ot);
                            }
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            mostrarToast(
                                data.message || "Error al enviar alerta SCAR.",
                                true,
                            );
                        }
                    })
                    .catch((err) => {
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                        console.error("Error sending SCAR alert:", err);
                        mostrarToast("Error al enviar la solicitud.", true);
                    });
            });
        }
    });
})();
// =============================================================================
// BLOQUE 2 — MINI-MODAL: CONFIRMAR MODELO CON DOCUMENTOS OBLIGATORIOS
// =============================================================================
window.abrirModalConfirmarModelo = function (
    ot,
    idHash,
    clasesFaltantes = null,
    todasClases = null,
) {
    const modal = document.getElementById("modalConfirmarModelo");
    if (!modal) return;
    // Resetear lista de archivos seleccionados y badges
    cmConfirmarSelectedFiles = [];
    renderCmConfirmarBadges();
    // Resetear formulario
    const form = document.getElementById("formConfirmarModelo");
    if (form) form.reset();
    // Asignar valores a campos ocultos
    const otInput = document.getElementById("cm-ot");
    if (otInput) otInput.value = ot;
    const hashInput = document.getElementById("cm-id-hash");
    if (hashInput) hashInput.value = idHash || "";
    // Asignar subtítulo con la OT
    const subtitle = document.getElementById("confirmar-modelo-subtitle");
    if (subtitle) subtitle.textContent = `OT: ${ot}`;
    // Asignar fecha actual por defecto
    const fechaInput = document.getElementById("cm-fecha");
    if (fechaInput) {
        const h = new Date();
        fechaInput.value = `${h.getFullYear()}-${String(h.getMonth() + 1).padStart(2, "0")}-${String(h.getDate()).padStart(2, "0")}`;
    }
    // Abrir modal y bloquear scroll de la página
    modal.classList.add("open");
    document.body.classList.add("modal-open");
    // Obtener las clases de la OT para que el usuario seleccione cuáles tiene físicamente
    const clasesContainer = document.getElementById("cm-clases-container");
    if (clasesContainer) {
        clasesContainer.innerHTML =
            '<div class="alm-spinner" id="cm-clases-spinner" style="border-top-color: #0284c7; display: block; margin: 5px auto;"></div>';
        if (
            todasClases &&
            Array.isArray(todasClases) &&
            todasClases.length > 0
        ) {
            let html = "";
            todasClases.forEach((nombreClase, index) => {
                const nombreNorm = nombreClase.toLowerCase();
                let yaProcesada = false;
                if (
                    clasesFaltantes !== null &&
                    Array.isArray(clasesFaltantes)
                ) {
                    // Validar si la clase actual NO está en clasesFaltantes
                    const esFaltante = clasesFaltantes.some(
                        (f) => f.toLowerCase() === nombreNorm,
                    );
                    yaProcesada = !esFaltante;
                }
                if (yaProcesada) {
                    return; // Omitir completamente de la interfaz
                } else {
                    const nombreDisplay =
                        nombreClase.charAt(0).toUpperCase() +
                        nombreClase.slice(1);
                    html += `
<label style="display: flex; align-items: center; gap: 8px; background: #fff; border: 1.5px solid #cbd5e1; padding: 10px 15px; border-radius: 8px; cursor: pointer; transition: all 0.2s ease;"
onmouseover="this.style.borderColor='#0a8504'; this.style.backgroundColor='#f0fdf4';"
onmouseout="if(!this.querySelector('input').checked){ this.style.borderColor='#cbd5e1'; this.style.backgroundColor='#fff'; }">
<input type="checkbox" name="clases_seleccionadas[]" value="${nombreDisplay}" class="cm-clase-checkbox"
style="width: 18px; height: 18px; cursor: pointer;"
onchange="window.onCmClaseToggle(this);">
<span style="font-family:'Poppins', sans-serif; font-weight: 500; color: #334155;">${index + 1}. ${nombreDisplay}</span>
</label>
`;
                }
            });
            if (html === "") {
                html =
                    '<div style="text-align: center; color: #64748b; padding: 10px; font-style: italic;">Todas las clases ya fueron procesadas.</div>';
            }
            clasesContainer.innerHTML = html;
        } else {
            clasesContainer.innerHTML =
                '<span style="color:#ef4444; font-size:0.9em; font-weight:500;">No hay clases configuradas para confirmar.</span>';
        }
    }
    // Obtener y renderizar los archivos de la OT desde el backend
    const filesContainer = document.getElementById("cm-server-files-container");
    if (filesContainer) {
        filesContainer.innerHTML = `
<div style="text-align: center; padding: 10px;">
<div class="alm-spinner" style="border-top-color: #033966; display: inline-block;"></div>
<span style="color: #64748b; margin-left: 10px;">Obteniendo archivos del servidor...</span>
</div>
`;
        fetch(`${window.almacenRoutes.archivos}?ot=${encodeURIComponent(ot)}`)
            .then((res) => res.json())
            .then((data) => {
                if (data.existe && data.archivos && data.archivos.length > 0) {
                    let baseUrl =
                        window.baseUrl || window.location.origin + "/";
                    if (!baseUrl.endsWith("/")) baseUrl += "/";
                    let archivosAMostrar = data.archivos;
                    if (clasesFaltantes && Array.isArray(clasesFaltantes)) {
                        archivosAMostrar = archivosAMostrar.filter((f) => {
                            const n = (f.nombre || "").toLowerCase();
                            if (
                                n.includes("documentos_aprobados") ||
                                n.includes("documentos_rechazados") ||
                                n.includes("pre-orden")
                            )
                                return true;
                            const knownClasses = [
                                "candado obturador",
                                "cabeza de soplo",
                                "obturador",
                                "bombillo",
                                "embudo",
                                "corona",
                                "plato",
                                "molde",
                                "fondo",
                            ];
                            let foundClass = null;
                            for (let kc of knownClasses) {
                                if (n.includes(kc)) {
                                    foundClass = kc;
                                    break;
                                }
                            }
                            if (foundClass) {
                                return clasesFaltantes.some((clase) => {
                                    let c = clase
                                        .toLowerCase()
                                        .trim()
                                        .replace(/^modelo\s+/i, "")
                                        .replace(/^casting\s+/i, "")
                                        .trim();
                                    return foundClass === c;
                                });
                            }
                            return false;
                        });
                    }
                    const sectionsHtml = generarHtmlCategorizadoArchivos(
                        archivosAMostrar,
                        ot,
                        baseUrl,
                        "preorden",
                    ); // Use preorden to show Dibujos and Ayudas
                    filesContainer.innerHTML =
                        sectionsHtml ||
                        `
<div style="text-align: center; color: #64748b; padding: 15px; font-style: italic;">
No se encontraron archivos pendientes para esta OT.
</div>
`;
                    // Para el modal Confirmar Modelo, iniciar con todos los dibujos desmarcados
                    const fileCards =
                        filesContainer.querySelectorAll(".select-file-card");
                    fileCards.forEach((card) => {
                        const fileInput = card.querySelector(
                            'input[type="checkbox"]',
                        );
                        if (fileInput) fileInput.checked = false;
                        card.classList.remove("checked-card");
                    });
                } else {
                    filesContainer.innerHTML = `
<div style="text-align: center; color: #64748b; padding: 15px; font-style: italic;">
No se encontraron archivos en el servidor para esta OT.
</div>
`;
                }
            })
            .catch((err) => {
                console.error(err);
                filesContainer.innerHTML = `
<div style="text-align: center; color: #ef4444; padding: 15px; font-weight: 500;">
Error al cargar los archivos del servidor.
</div>
`;
            });
    }
};
window.cerrarModalConfirmarModelo = function () {
    const modal = document.getElementById("modalConfirmarModelo");
    if (modal) modal.classList.remove("open");
    document.body.classList.remove("modal-open");
};
(function _initConfirmarModelo() {
    document.addEventListener("DOMContentLoaded", () => {
        const form = document.getElementById("formConfirmarModelo");
        if (!form) return;
        form.addEventListener("submit", async function (e) {
            e.preventDefault();
            const ot = document.getElementById("cm-ot")?.value;
            const idHash = document.getElementById("cm-id-hash")?.value;
            if (!ot) return;
            if (cmConfirmarSelectedFiles.length === 0) {
                almacenToast(
                    "Debes adjuntar al menos un documento de recepción.",
                    "error",
                );
                return;
            }
            const btn = document.getElementById("btn-submit-confirmar-modelo");
            const origText = btn ? btn.innerHTML : "";
            if (btn) {
                btn.disabled = true;
                btn.innerHTML =
                    '<span class="alm-spinner" style="display:inline-block;border-top-color:#fff;width:14px;height:14px;margin-right:8px;vertical-align:middle;"></span> Guardando...';
            }
            const fd = new FormData(this);
            fd.delete("archivos[]");
            cmConfirmarSelectedFiles.forEach((file) => {
                fd.append("archivos[]", file);
            });
            try {
                const resp = await fetch(window.almacenRoutes.confirmarModelo, {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN":
                            document.querySelector('meta[name="csrf-token"]')
                                ?.content ?? "",
                        "X-Requested-With": "XMLHttpRequest",
                    },
                    body: fd,
                });
                const data = await resp.json();
                if (data.success) {
                    almacenToast(data.message, "success");
                    cerrarModalConfirmarModelo();
                    // Actualizar FSM y bloquear card visualmente
                    if (window.ModeloStateMachine)
                        window.ModeloStateMachine.onConfirmarModelo(ot);
                    if (idHash) {
                        const container = document.getElementById(
                            "control-modelo-" + idHash,
                        );
                        if (container) {
                            container.style.opacity = "0.5";
                            container.style.pointerEvents = "none";
                        }
                    }
                    setTimeout(() => location.reload(), 1600);
                } else {
                    almacenToast(
                        data.message || "Error al registrar el modelo.",
                        "error",
                    );
                }
            } catch (err) {
                console.error("Error confirmando modelo:", err);
                almacenToast("Error de red al registrar el modelo.", "error");
            } finally {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = origText;
                }
            }
        });
    });
})();
// =============================================================================
// BLOQUE 3a — PRE-ORDEN: BLOQUEAR IMPRESIONES PARA BOMBILLO Y MOLDE (N/A)
// =============================================================================
/**
 * Clases que usan N/A en impresiones (sin distinción de mayúsculas).
 * Molde y Bombillo nunca llevan impresiones.
 */
const CLASES_SIN_IMPRESIONES = ["bombillo", "molde"];
/**
 * Bloquea / desbloquea el input de Impresiones de la fila según la clase seleccionada.
 * Expuesto en window para ser llamable desde onchange inline en el HTML generado.
 */
window.actualizarInputImpresiones = function (selectEl) {
    const row = selectEl.closest("tr");
    if (!row) return;
    const impInput = row.querySelector("input.po-impresiones");
    if (!impInput) return;
    const nombreClase =
        selectEl.options[selectEl.selectedIndex]?.text?.toLowerCase() ?? "";
    const esNA = CLASES_SIN_IMPRESIONES.some((c) => nombreClase.includes(c));
    impInput.disabled = esNA;
    impInput.value = esNA
        ? "N/A"
        : impInput.value === "N/A"
          ? ""
          : impInput.value;
    impInput.placeholder = esNA ? "N/A" : "Ej. 1";
    impInput.style.background = esNA ? "#f1f5f9" : "";
    impInput.style.color = esNA ? "#94a3b8" : "";
    impInput.style.cursor = esNA ? "not-allowed" : "";
    impInput.title = esNA ? "Esta clase no lleva impresiones (N/A)" : "";
};
/**
 * Recorre TODAS las filas del tbody de pre-orden y aplica el bloqueo.
 * Se debe llamar al abrir el modal y al cargar filas existentes.
 */
function aplicarBloqueoImpresionesEnTodas(tbodyId = "alm-tbody-preorden") {
    const tbody = document.getElementById(tbodyId);
    if (!tbody) return;
    tbody.querySelectorAll(".po-clase-select").forEach((sel) => {
        window.actualizarInputImpresiones(sel);
    });
}
// Listener delegado: reaplica el bloqueo al cambiar cualquier select de clase
// (cubre filas creadas dinámicamente sin necesidad del onchange inline)
(function _initImpresionesDelegate() {
    document.addEventListener("change", function (e) {
        if (e.target.classList.contains("po-clase-select")) {
            window.actualizarInputImpresiones(e.target);
        }
    });
})();
// =============================================================================
// BLOQUE 3b — PRE-ORDEN: BLOQUEAR CONTROLES TRAS ENVIAR NOTIFICACIÓN
// =============================================================================
/**
 * Bloquea todos los campos del modal de pre-orden para solo lectura.
 * Se llama después de un envío exitoso de la notificación por correo.
 */
function bloquearModalPreOrden() {
    const form = document.getElementById("formPreOrden");
    if (!form) return;
    form.querySelectorAll(
        'input:not([type="hidden"]), select, textarea',
    ).forEach((el) => {
        el.disabled = true;
        el.style.background = "#f1f5f9";
        el.style.cursor = "not-allowed";
    });
    const btnSubmit = document.getElementById("btn-submit-preorden");
    if (btnSubmit) {
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = "✔ Notificación enviada — Solo lectura";
        btnSubmit.style.background = "#94a3b8";
    }
    const btnAdd = document.getElementById("btn-add-clase-po");
    if (btnAdd) {
        btnAdd.disabled = true;
        btnAdd.style.opacity = "0.4";
    }
}
// =============================================================================
// BLOQUE 5a/5b — MODAL UNIFICADO DE CALIDAD CON SELECTOR Y FILTRO DE TIPOS
// =============================================================================
/**
 * Abre el modal de liberación con el selector Aprobar/Rechazar y filtra los
 * tipos de modelo disponibles según las clases activas de la OT.
 *
 * @param {string}   ot           - Nombre completo de la OT
 * @param {string[]} clasesActivas - Array de nombres de clases activas
 * @param {string[]} todasClases  - Array de todas las clases vinculadas (incluye opcionales)
 */
window.abrirModalLiberacionUnificado = function (
    ot,
    clasesActivas,
    todasClases,
) {
    if (!clasesActivas || !Array.isArray(clasesActivas) || clasesActivas.length === 0) {
        if (typeof almacenToast === "function") {
            almacenToast("No hay clases enviadas por Almacén para revisar", "error");
        }
        return;
    }
    window._currentClasesActivas = clasesActivas;
    window._currentTodasClases = todasClases;
    // Llamar al opener original para mantener lógica de FSM
    if (typeof abrirModalLiberacion === "function") {
        abrirModalLiberacion(ot, "aprobar");
    }
    // Resetear selector visual a "Aprobar"
    libSeleccionarDecision("aprobar");
    // Filtrar el <select id="lib-tipo"> según las clases registradas y obtener el mejor a seleccionar
    const autoSelectValue = _libFiltrarTiposModelo(clasesActivas, todasClases);
    if (autoSelectValue) {
        const select = document.getElementById("lib-tipo");
        if (select) {
            select.value = autoSelectValue;
            libCambiarTipo(autoSelectValue);
        }
    }
};
/**
 * Filtra las opciones del select #lib-tipo según todas las clases registradas en la OT.
 * Mapa: nombre de clase contiene → valor de option
 * Retorna el valor del primer modelo no procesado (activo), o en su defecto el primer disponible.
 */
function _libFiltrarTiposModelo(clasesActivas, todasClases) {
    const select =
        document.getElementById("lib-tipo") ||
        document.getElementById("lib-tipo");
    if (!select) return null;
    let firstAvailable = null;
    let firstUnprocessed = null;
    const MAPA_TIPO = {
        "candado obturador": "Candado Obturador",
        "cabeza de soplo": "Cabeza de Soplo",
        embudo: "Embudo",
        corona: "Corona",
        plato: "Plato",
        fondo: "Fondo",
        obturador: "Obturador",
        molde: "Molde",
        bombillo: "Bombillo",
    };
    const knownKeys = [
        "candado obturador",
        "cabeza de soplo",
        "embudo",
        "corona",
        "plato",
        "fondo",
        "obturador",
        "molde",
        "bombillo",
    ];
    // Calcular qué tipos están configurados en la OT
    const tiposConfigurados = new Set();
    const clasesAUsar =
        todasClases && todasClases.length > 0 ? todasClases : clasesActivas;
    if (clasesAUsar && clasesAUsar.length > 0) {
        clasesAUsar.forEach((clase) => {
            const clLow = clase.toLowerCase();
            for (let key of knownKeys) {
                if (clLow.includes(key)) {
                    tiposConfigurados.add(MAPA_TIPO[key]);
                    break;
                }
            }
        });
    }
    // Calcular cuáles aún están activos (no aprobados) para auto-selección
    const tiposActivos = new Set();
    if (clasesActivas && clasesActivas.length > 0) {
        clasesActivas.forEach((clase) => {
            const clLow = clase.toLowerCase();
            for (let key of knownKeys) {
                if (clLow.includes(key)) {
                    tiposActivos.add(MAPA_TIPO[key]);
                    break;
                }
            }
        });
    }
    // Mostrar/ocultar opciones según tiposActivos
    select.querySelectorAll("option").forEach((opt) => {
        if (!opt.value) {
            opt.hidden = false;
            opt.disabled = false;
            opt.classList.remove("cal-display-none");
            return;
        }
        // Comparamos case-insensitive para ser más robustos
        let optValLow = opt.value.toLowerCase();
        let isActive = Array.from(tiposActivos).some(
            (t) => t.toLowerCase() === optValLow,
        );
        let shouldHide = false;
        if (tiposActivos.size === 0) {
            shouldHide = false;
        } else {
            shouldHide = !isActive;
        }
        opt.hidden = shouldHide;
        opt.disabled = shouldHide;
        opt.classList.toggle("cal-display-none", shouldHide);
        if (!shouldHide) {
            if (!firstAvailable) firstAvailable = opt.value;
            if (isActive || tiposActivos.size === 0) {
                const cached =
                    window.cacheLiberacionGlobal &&
                    window.cacheLiberacionGlobal[opt.value];
                if (!cached && !firstUnprocessed) {
                    firstUnprocessed = opt.value;
                }
            }
        }
    });
    return firstUnprocessed || firstAvailable;
}
/**
 * Cambia visualmente el selector Aprobar/Rechazar y actualiza el hidden `lib-accion`.
 * Si elige "rechazar" muestra el bloque de motivo de rechazo.
 */
function _libSetDecisionUI(decision) {
    const accionInput = document.getElementById("lib-accion");
    if (accionInput) accionInput.value = decision;
    const cardAprobar = document.getElementById("lib-dec-aprobar");
    const cardRechazar = document.getElementById("lib-dec-rechazar");
    const bloqueRechazo = document.getElementById("lib-rechazo-block");
    // Quitar clase "active" de ambos y asignar al elegido
    if (cardAprobar) cardAprobar.classList.remove("active");
    if (cardRechazar) cardRechazar.classList.remove("active");
    if (decision === "aprobar") {
        if (cardAprobar) {
            cardAprobar.classList.add("active");
            cardAprobar.style.border = "2px solid #0a8504";
            cardAprobar.style.background = "rgba(10,133,4,0.08)";
        }
        if (cardRechazar) {
            cardRechazar.style.border = "2px solid #e2e8f0";
            cardRechazar.style.background = "#fff";
        }
        if (bloqueRechazo) {
            bloqueRechazo.classList.add("cal-display-none");
            bloqueRechazo.style.display = "none";
        }
    } else {
        if (cardRechazar) {
            cardRechazar.classList.add("active");
            cardRechazar.style.border = "2px solid #9c0300";
            cardRechazar.style.background = "rgba(156,3,0,0.07)";
        }
        if (cardAprobar) {
            cardAprobar.style.border = "2px solid #e2e8f0";
            cardAprobar.style.background = "#fff";
        }
        if (bloqueRechazo) {
            bloqueRechazo.classList.remove("cal-display-none");
            bloqueRechazo.removeAttribute("hidden");
            bloqueRechazo.style.display = "block";
        }
    }
    // Actualizar los botones de acción del modal
    _libActualizarBotonesAccion(decision);
}
window._libSetDecisionUI = _libSetDecisionUI;
window.libSeleccionarDecision = function (decision) {
    _libSetDecisionUI(decision);
    // Actualizar la decisión en caché de forma reactiva y actualizar el color del select
    const select = document.getElementById("lib-tipo");
    if (select && select.value) {
        const val = select.value;
        if (!window.cacheLiberacionGlobal) window.cacheLiberacionGlobal = {};
        if (!window.cacheLiberacionGlobal[val])
            window.cacheLiberacionGlobal[val] = {};
        window.cacheLiberacionGlobal[val].decision = decision;
        if (typeof _libActualizarColoresSelect === "function") {
            _libActualizarColoresSelect();
        }
    }
};
/**
 * Actualiza el contenido del div #lib-actions con el botón correcto
 * según la decisión seleccionada.
 */
function _libActualizarBotonesAccion(decision) {
    const actionsEl = document.getElementById("lib-actions");
    if (!actionsEl) return;
    const btnAccion = actionsEl.querySelector("#lib-btn-accion");
    if (!btnAccion) return;
    // Remover listener anterior clonando el nodo
    const nuevoBtn = btnAccion.cloneNode(false);
    if (decision === "aprobar") {
        nuevoBtn.className = "btn-lib-aprobar-send";
        nuevoBtn.classList.add("btn-lib-send-custom");
        nuevoBtn.innerHTML =
            '<img src="' +
            (window.almacenAppAssets?.descarga ?? "/images/Descarga.png") +
            '" alt="" style="width:20px;height:20px;"> Aprobar y Descargar PDF';
        nuevoBtn.addEventListener("click", () => _libSubmit("accion"));
    } else {
        nuevoBtn.className = "btn-lib-rechazar-send";
        nuevoBtn.classList.add("btn-lib-send-custom");
        nuevoBtn.innerHTML =
            '<img src="' +
            (window.almacenAppAssets?.descarga ?? "/images/Descarga.png") +
            '" alt="" style="width:20px;height:20px;"> Descargar Documento y Generar SCAR';
        nuevoBtn.addEventListener("click", () => _libSubmit("accion"));
    }
    btnAccion.replaceWith(nuevoBtn);
}
/**
 * Elimina un documento adicional (tipo imagen u otro) del servidor.
 */
window.almacenEliminarOtroArchivo = function (
    ot,
    archivo,
    tipo,
    buttonEl,
    origin,
) {
    if (
        !confirm(
            "¿Estás seguro de que deseas eliminar permanentemente este archivo? Esta acción no se puede deshacer.",
        )
    ) {
        return;
    }
    const card = buttonEl.closest(".dibujos-file-card");
    if (buttonEl) buttonEl.disabled = true;
    fetch(window.almacenRoutes.deleteFile, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content"),
            Accept: "application/json",
        },
        body: JSON.stringify({
            ot: ot,
            archivo: archivo,
            tipo: tipo,
            origin: origin || "",
        }),
    })
        .then((res) => res.json())
        .then((data) => {
            if (data.success) {
                mostrarToast(
                    data.message || "Archivo eliminado correctamente.",
                );
                if (card) {
                    card.style.transition = "all 0.4s ease";
                    card.style.opacity = "0";
                    card.style.transform = "scale(0.8)";
                    setTimeout(() => {
                        card.remove();
                        // Si ya no quedan archivos, recargar la página para limpiar la vista
                        const grid = card.closest(".alm-pdf-grid");
                        if (
                            grid &&
                            grid.querySelectorAll(".dibujos-file-card")
                                .length === 0
                        ) {
                            location.reload();
                        }
                    }, 400);
                } else {
                    setTimeout(() => location.reload(), 1000);
                }
            } else {
                if (buttonEl) buttonEl.disabled = false;
                mostrarToast(
                    data.error || "No se pudo eliminar el archivo.",
                    true,
                );
            }
        })
        .catch((err) => {
            if (buttonEl) buttonEl.disabled = false;
            console.error("Error al eliminar archivo:", err);
            mostrarToast("Error de conexión al eliminar el archivo.", true);
        });
};
// =========================================================================
// MODAL: ENVIAR ALERTA DE LIBERACIÓN v2 (dual: aprobados / rechazados)
// =========================================================================
/** Genera una fila de upload por modelo */
function _crearFilaUpload(tipo, color, accentBg, esRechazo, baseUrl) {
    const idBase = `al-upload-${tipo.toLowerCase().replace(/\s/g, "-")}-${esRechazo ? "rech" : "aprob"}`;
    const tipoLabel =
        tipo.charAt(0).toUpperCase() + tipo.slice(1).toLowerCase();
    const nombre = esRechazo
        ? `archivos_rechazados_extra[${tipo}]`
        : `archivos_aprobados_extra[${tipo}]`;
    const nombreScar = `archivos_scar_extra[${tipo}]`;
    const scarBlock = esRechazo
        ? `
<div style="margin-top:14px; display:flex; flex-direction:column; gap:6px; width:100%;" id="${idBase}-scar-wrap">
<label style="font-weight:600; font-size:0.9em; color:#475569; font-family:'Poppins',sans-serif;" for="${idBase}-scar">
Subir SCAR Firmado (${tipoLabel}) <span style="color:#ef4444;">*</span>
</label>
<div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;width:100%;">
<label style="display:flex;align-items:center;gap:10px;background:#fff;border:1.8px dashed #fca5a5;border-radius:10px;padding:12px 16px;cursor:pointer;font-size:0.95em;color:#64748b;flex:1;font-family:'Poppins',sans-serif;" id="${idBase}-scar-label">
<img src="${baseUrl}images/anadir.png" style="width:20px;height:20px;">
<span id="${idBase}-scar-text">Seleccionar archivo...</span>
<input type="file" name="${nombreScar}" accept=".pdf,image/*" style="display:none;" id="${idBase}-scar" required
onchange="_alFileChanged('${idBase}-scar','${idBase}-scar-text','${idBase}-scar-label')">
</label>
<div id="${idBase}-scar-preview" style="font-size:0.9em;font-weight:600;color:#059669;display:none;font-family:'Poppins',sans-serif;width:100%;justify-content:center;"></div>
</div>
</div>`
        : "";
    return `
<div class="al-modelo-upload-row" id="${idBase}-row"
style="background:${accentBg};border:1.8px solid ${color}40;border-radius:12px;padding:16px 20px;margin-bottom:12px;box-shadow: 0 2px 8px rgba(0,0,0,0.02); display:flex; flex-direction:column; gap:12px;">
<div style="font-weight:700;font-size:1.1em;color:${color};font-family:'Poppins',sans-serif;">Modelo: ${tipoLabel}</div>
<div style="display:flex; flex-direction:column; gap:6px; width:100%;">
<label style="font-weight:600; font-size:0.9em; color:#475569; font-family:'Poppins',sans-serif;" for="${idBase}">
Subir Formato ${esRechazo ? "F-CCL-LDM Rechazado" : "F-CCL-LDM Aprobado"} (${tipoLabel}) <span style="color:#ef4444;">*</span>
</label>
<div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;width:100%;">
<label style="display:flex;align-items:center;gap:10px;background:#fff;border:1.8px dashed ${color};border-radius:10px;padding:12px 16px;cursor:pointer;font-size:0.95em;color:#64748b;flex:1;font-family:'Poppins',sans-serif;" id="${idBase}-label">
<img src="${baseUrl}images/anadir.png" style="width:20px;height:20px;">
<span id="${idBase}-text">Seleccionar archivo...</span>
<input type="file" name="${nombre}" accept=".pdf,image/*" style="display:none;" id="${idBase}" required
onchange="_alFileChanged('${idBase}','${idBase}-text','${idBase}-label')">
</label>
<div id="${idBase}-preview" style="font-size:0.9em;font-weight:600;color:#059669;display:none;font-family:'Poppins',sans-serif;width:100%;justify-content:center;"></div>
</div>
</div>
${scarBlock}
</div>`;
}
window._alFileChanged = function (inputId, textId, labelId) {
    const inp = document.getElementById(inputId);
    if (!inp || !inp.files.length) return;
    const nm = inp.files[0].name;
    const txt = document.getElementById(textId);
    if (txt) txt.textContent = nm;
    const lbl = document.getElementById(labelId);
    if (lbl) lbl.style.borderStyle = "solid";
    if (inp._objectUrl) {
        URL.revokeObjectURL(inp._objectUrl);
    }
    const file = inp.files[0];
    const url = URL.createObjectURL(file);
    inp._objectUrl = url;
    let baseUrl = window.baseUrl || window.location.origin + "/";
    if (!baseUrl.endsWith("/")) baseUrl += "/";
    const isScar = inputId.endsWith("-scar");
    const borderCol = isScar
        ? "#ef4444"
        : inputId.includes("-rech")
          ? "#dc2626"
          : "#059669";
    let iconHtml = "";
    if (file.type.startsWith("image/")) {
        iconHtml = `
<div style="width: 80px; height: 80px; margin-top: 10px; display: flex; align-items: center; justify-content: center; overflow: hidden; border-radius: 8px; border: 1px solid #e2e8f0;">
<img src="${url}" style="width: 100%; height: 100%; object-fit: cover;">
</div>
`;
    } else {
        iconHtml = `
<div class="file-icon-wrapper" onclick="window.open('${url}', '_blank')" style="cursor:pointer; margin-top: 10px;" title="Ver">
<img src="${baseUrl}images/pdf-view-shadow.png" class="file-icon icon-default" style="width:48px;height:48px;object-fit:contain;">
<img src="${baseUrl}images/pdf-view.png" class="file-icon icon-hover" style="width:48px;height:48px;object-fit:contain;">
</div>
`;
    }
    const prv = document.getElementById(inputId + "-preview");
    if (prv) {
        prv.innerHTML = `
<div class="dibujos-file-card select-file-card checked-card" style="position:relative; width:100%; max-width:180px; display:inline-flex; flex-direction:column; align-items:center; text-align:center; border-radius:12px; box-shadow:0 4px 10px rgba(0,0,0,0.08); box-sizing:border-box; font-size:0.95em; padding:12px; background:#fff; border:2px solid ${borderCol}; margin-top:12px;">
<div style="position: absolute; top: 10px; right: 10px; z-index: 10;">
<button type="button" style="background: #fca5a5; border: none; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #9c0300; font-weight: bold; font-size: 0.95em; box-shadow: 0 2px 4px rgba(0,0,0,0.1); line-height: 1; padding: 0;" onclick="_alClearFile('${inputId}')" title="Quitar">&times;</button>
</div>
${iconHtml}
<div class="file-name" style="cursor:pointer; font-size:0.88em; margin:8px 0; max-height:42px; overflow:hidden; font-weight:600; color:#334155; line-height:1.3; font-family:'Poppins',sans-serif;" onclick="window.open('${url}', '_blank')">${nm}</div>
<div class="file-actions" style="width:100%; margin-top:auto;">
<button type="button" class="btn-dibujos btn-dibujos-sm btn-ver btn-ayuda-color" style="font-size:0.85em; padding:6px 14px; border-radius:6px; font-family:'Poppins',sans-serif; font-weight:600; width:100%; cursor:pointer;" onclick="window.open('${url}', '_blank')">Ver</button>
</div>
</div>
`;
        prv.classList.remove("cal-display-none");
    }
};
window._alClearFile = function (inputId) {
    const inp = document.getElementById(inputId);
    if (inp) {
        inp.value = "";
        if (inp._objectUrl) {
            URL.revokeObjectURL(inp._objectUrl);
            inp._objectUrl = null;
        }
    }
    const prv = document.getElementById(inputId + "-preview");
    if (prv) {
        prv.innerHTML = "";
        prv.classList.add("cal-display-none");
    }
    const lbl = document.getElementById(inputId + "-label");
    if (lbl) lbl.style.borderStyle = "dashed";
    // Restaurar el texto original
    const txt = document.getElementById(inputId + "-text");
    if (txt) {
        txt.textContent = "Seleccionar archivo...";
    }
};
function _renderServerFileCard(file, ot, baseUrl, tipo) {
    const dispName = file.nombre.split("/").pop();
    const inputName =
        tipo === "rechazados" ? "dibujos_rechazados[]" : "dibujos_aprobados[]";
    // Detectar si es una imagen por su extensión
    const ext = file.nombre.split(".").pop().toLowerCase();
    const esImg = ["png", "jpg", "jpeg", "gif", "webp", "bmp"].includes(ext);
    const defaultIcon = esImg ? "galeria-shadow.png" : "pdf-view-shadow.png";
    const hoverIcon = esImg ? "galeria.png" : "pdf-view.png";
    return `<div class="dibujos-file-card card-ayuda select-file-card checked-card" style="position:relative;width:100%;max-width:230px;display:inline-flex;flex-direction:column;align-items:center;text-align:center;border-radius:12px;box-shadow:0 4px 10px rgba(0,0,0,0.08);box-sizing:border-box;font-size:0.95em;padding:12px;background:#fff;border:1.5px solid #e2e8f0;margin:4px;">
<div style="position:absolute;top:10px;left:10px;z-index:10;"><input type="checkbox" name="${inputName}" value="${file.nombre}" checked style="width:18px;height:18px;cursor:pointer;" onchange="this.closest('.select-file-card').classList.toggle('checked-card',this.checked);"></div>
<div class="file-icon-wrapper" onclick="calidadVerPdf('${ot}','${file.nombre}','${file.tipo}')" style="cursor:pointer;margin-top:12px;" title="Ver">
<img src="${baseUrl}images/${defaultIcon}" class="file-icon icon-default" style="width:48px;height:48px;object-fit:contain;"><img src="${baseUrl}images/${hoverIcon}" class="file-icon icon-hover" style="width:48px;height:48px;object-fit:contain;">
</div>
<div class="file-name" style="cursor:pointer;font-size:0.88em;margin:8px 0;max-height:42px;overflow:hidden;font-weight:600;color:#334155;line-height:1.3;font-family:'Poppins',sans-serif;" onclick="calidadVerPdf('${ot}','${file.nombre}','${file.tipo}')">${dispName}</div>
<div class="file-actions" style="width:100%;margin-top:auto;"><button type="button" class="btn-dibujos btn-dibujos-sm btn-ver btn-ayuda-color" style="font-size:0.85em;padding:6px 14px;border-radius:6px;font-family:'Poppins',sans-serif;font-weight:600;width:100%;" onclick="calidadVerPdf('${ot}','${file.nombre}','${file.tipo}')">Ver</button></div>
</div>`;
}
// Nueva firma: tiposAprobados y tiposRechazados son arrays JSON pasados desde Blade
// Nueva firma: tiposAprobados y tiposRechazados son arrays JSON pasados desde Blade, isAlmacen determina si lo abre Almacén
window.abrirModalEnviarAlertaLiberacion = function (
    ot,
    decision,
    tiposAprobados,
    tiposRechazados,
    isAlmacen = false,
) {
    const modal = document.getElementById("modalEnviarAlertaLiberacion");
    if (!modal) return;
    const form = document.getElementById("formEnviarAlertaLiberacion");
    if (form) form.reset();
    // Los arrays vienen directamente desde Blade: tiposAprobados, tiposRechazados
    // Aseguramos que sean arrays
    const arrAprobados = Array.isArray(tiposAprobados) ? tiposAprobados : [];
    const arrRechazados = Array.isArray(tiposRechazados) ? tiposRechazados : [];
    const hasAprobado = arrAprobados.length > 0;
    const hasRechazado = isAlmacen ? false : arrRechazados.length > 0;
    const esMixto = isAlmacen ? false : hasAprobado && hasRechazado;
    // Hiddens
    document.getElementById("al-ot").value = ot;
    document.getElementById("al-decision").value = isAlmacen
        ? "aprobar"
        : esMixto
          ? "mixto"
          : decision;
    document.getElementById("al-tipo-modelo").value = [
        ...arrAprobados,
        ...(isAlmacen ? [] : arrRechazados),
    ].join(", ");
    // Fecha hoy: se deja vacía para obligar al usuario a seleccionar una fecha
    const fi = document.getElementById("al-fecha");
    if (fi) {
        fi.value = "";
    }
    const otClean = ot.replace(/_\d{8}_\d{6}_.*/, "");
    // Colores adaptativos
    let bg, border, btnBg, ttl, pmt;
    if (isAlmacen) {
        bg = "linear-gradient(135deg,#0284c7,#0369a1)";
        border = "#0284c7";
        btnBg = "#0284c7";
        ttl = `Cargar LDM Firmado — ${otClean}`;
        pmt = `Por favor, sube el formato F-CCL-LDM firmado de los modelos aprobados (${arrAprobados.join(", ")}) para avanzar al proceso de Casting.`;
        const destGroup = document
            .getElementById("al-destinatario")
            ?.closest(".form-group");
        if (destGroup) destGroup.classList.add("cal-display-none");
        // default value so validation passes
        const d = document.getElementById("al-destinatario");
        if (d) d.value = "jaxer020406@gmail.com";
    } else {
        const destGroup = document
            .getElementById("al-destinatario")
            ?.closest(".form-group");
        if (destGroup) destGroup.classList.remove("cal-display-none");
        if (esMixto) {
            bg = "linear-gradient(135deg,#d97706,#b45309)";
            border = "#d97706";
            btnBg = "#b45309";
            ttl = `Enviar Alertas (Mixto) — ${otClean}`;
            pmt = `Esta OT tiene modelos aprobados (${arrAprobados.join(", ")}) y rechazados (${arrRechazados.join(", ")}). Se enviarán 2 correos separados.`;
        } else if (hasRechazado) {
            bg = "linear-gradient(135deg,#dc2626,#b91c1c)";
            border = "#dc2626";
            btnBg = "#9c0300";
            ttl = `Enviar Alerta de Rechazo — ${otClean}`;
            pmt = `Notifica el rechazo de: ${arrRechazados.join(", ")} para OT ${otClean}.`;
        } else {
            bg = "linear-gradient(135deg,#059669,#047857)";
            border = "#059669";
            btnBg = "#047857";
            ttl = `Enviar Alerta de Aprobación — ${otClean}`;
            pmt = `Notifica la aprobación de: ${arrAprobados.join(", ")} para OT ${otClean}.`;
        }
    }
    const header = document.getElementById("alerta-lib-header");
    const mc = document.getElementById("alerta-lib-modal-content");
    const btn = document.getElementById("btn-submit-alerta-liberacion");
    if (header) {
        header.style.background = bg;
        header.style.borderBottom = `2px solid ${border}80`;
    }
    if (mc) mc.style.borderColor = border;
    if (btn) {
        btn.style.background = btnBg;
        btn.style.boxShadow = `0 4px 15px ${border}40`;
        const btnSpan = btn.querySelector("span");
        if (btnSpan)
            btnSpan.textContent = isAlmacen
                ? "Guardar Documentación Aprobada"
                : "Enviar Alerta";
        else
            btn.textContent = isAlmacen
                ? "Guardar Documentación Aprobada"
                : "Enviar Alerta";
    }
    const t = document.getElementById("alerta-lib-title");
    if (t) t.textContent = ttl;
    const p = document.getElementById("al-prompt-text");
    if (p) p.textContent = pmt;
    const s = document.getElementById("alerta-lib-subtitle");
    if (s) s.textContent = `OT: ${otClean}`;
    // Actualizar label de fecha dinámicamente
    const dateLabel = document.getElementById("al-fecha-label");
    if (dateLabel) {
        if (esMixto) {
            dateLabel.innerHTML = `Fecha Compromiso de Devolución / Fecha de Liberación <span style="color:#ef4444;">*</span>`;
        } else if (hasRechazado) {
            dateLabel.innerHTML = `Fecha Compromiso de Devolución <span style="color:#ef4444;">*</span>`;
        } else {
            dateLabel.innerHTML = `Fecha de Liberación <span style="color:#ef4444;">*</span>`;
        }
    }
    // Columnas visibilidad
    const colA = document.getElementById("al-col-aprobados");
    if (colA) colA.classList.toggle("cal-display-none", !hasAprobado);
    const colR = document.getElementById("al-col-rechazados");
    if (colR) colR.classList.toggle("cal-display-none", !hasRechazado);
    const dl = document.getElementById("al-dual-layout");
    if (dl) {
        dl.style.flexDirection = esMixto ? "row" : "column";
        dl.style.alignItems = "stretch";
    }
    // Labels tipos
    const aLbl = document.getElementById("al-aprobados-tipos-label");
    if (aLbl) aLbl.textContent = arrAprobados.join(", ") || "—";
    const rLbl = document.getElementById("al-rechazados-tipos-label");
    if (rLbl) rLbl.textContent = arrRechazados.join(", ") || "—";
    let baseUrl = window.baseUrl || window.location.origin + "/";
    if (!baseUrl.endsWith("/")) baseUrl += "/";
    // Filas upload por modelo
    const rowsA = document.getElementById("al-upload-aprobados-rows");
    const rowsR = document.getElementById("al-upload-rechazados-rows");
    if (rowsA)
        rowsA.innerHTML = arrAprobados.length
            ? arrAprobados
                  .map((t) =>
                      _crearFilaUpload(t, "#059669", "#f0fdf4", false, baseUrl),
                  )
                  .join("")
            : '<p style="font-size:0.8em;color:#64748b;font-style:italic;">Sin modelos aprobados.</p>';
    if (rowsR)
        rowsR.innerHTML = arrRechazados.length
            ? arrRechazados
                  .map((t) =>
                      _crearFilaUpload(t, "#dc2626", "#fef2f2", true, baseUrl),
                  )
                  .join("")
            : '<p style="font-size:0.8em;color:#64748b;font-style:italic;">Sin modelos rechazados.</p>';
    // Activar/desactivar inputs requeridos según la visibilidad de las columnas
    if (rowsA) {
        rowsA.querySelectorAll('input[type="file"]').forEach((inp) => {
            if (hasAprobado) {
                inp.setAttribute("required", "required");
            } else {
                inp.removeAttribute("required");
            }
        });
    }
    if (rowsR) {
        rowsR.querySelectorAll('input[type="file"]').forEach((inp) => {
            if (hasRechazado) {
                inp.setAttribute("required", "required");
            } else {
                inp.removeAttribute("required");
            }
        });
    }
    // Archivos del servidor separados
    const sA = document.getElementById("al-server-files-aprobados");
    const sR = document.getElementById("al-server-files-rechazados");
    const loadHtml = `<div style="text-align:center;color:#64748b;grid-column:1/-1;padding:8px;font-style:italic;font-size:0.8em;">Cargando...</div>`;
    const emptyHtml = `<div style="text-align:center;color:#94a3b8;grid-column:1/-1;padding:8px;font-style:italic;font-size:0.8em;">Sin archivos en servidor.</div>`;
    if (sA) sA.innerHTML = loadHtml;
    if (sR) sR.innerHTML = loadHtml;
    fetch(`${window.almacenRoutes.archivos}?ot=${encodeURIComponent(ot)}`)
        .then((r) => r.json())
        .then((data) => {
            let cardsA = "",
                cardsR = "";
            if (data.existe && data.archivos?.length > 0) {
                // Función para comprobar si el archivo pertenece a un listado de modelos activos
                const archivoPerteneceAModelos = (nombre, modelosActivos) => {
                    const pl = nombre.toLowerCase();
                    const todosModelosPosibles = [
                        "candado obturador",
                        "cabeza de soplo",
                        "obturador",
                        "bombillo",
                        "embudo",
                        "corona",
                        "plato",
                        "molde",
                        "fondo",
                    ];
                    const modelosEncontrados = todosModelosPosibles.filter(
                        (m) => pl.includes(m),
                    );
                    if (modelosEncontrados.length === 0) {
                        return false;
                    }
                    const modelosActivosLower = modelosActivos.map((m) =>
                        m.toLowerCase(),
                    );
                    return modelosEncontrados.some((m) =>
                        modelosActivosLower.includes(m),
                    );
                };
                data.archivos.forEach((f) => {
                    const pl = f.nombre.toLowerCase();
                    const isRechazadoFile =
                        pl.includes("documentos_rechazados") ||
                        pl.includes("rechazado") ||
                        pl.includes("scar");
                    if (isRechazadoFile) {
                        if (
                            hasRechazado &&
                            archivoPerteneceAModelos(f.nombre, arrRechazados)
                        ) {
                            cardsR += _renderServerFileCard(
                                f,
                                ot,
                                baseUrl,
                                "rechazados",
                            );
                        }
                    } else {
                        // Es un dibujo, ayuda visual o documento de aprobación
                        if (
                            hasAprobado &&
                            archivoPerteneceAModelos(f.nombre, arrAprobados)
                        ) {
                            cardsA += _renderServerFileCard(
                                f,
                                ot,
                                baseUrl,
                                "aprobados",
                            );
                        }
                        if (
                            hasRechazado &&
                            archivoPerteneceAModelos(f.nombre, arrRechazados)
                        ) {
                            cardsR += _renderServerFileCard(
                                f,
                                ot,
                                baseUrl,
                                "rechazados",
                            );
                        }
                    }
                });
            }
            if (sA) sA.innerHTML = cardsA || emptyHtml;
            if (sR) sR.innerHTML = cardsR || emptyHtml;
        })
        .catch(() => {
            if (sA)
                sA.innerHTML = `<div style="color:#ef4444;font-size:0.8em;grid-column:1/-1;">Error al cargar.</div>`;
            if (sR)
                sR.innerHTML = `<div style="color:#ef4444;font-size:0.8em;grid-column:1/-1;">Error al cargar.</div>`;
        });
    // Destinatario — toma el primero de los tipos que haya
    const primerTipo = arrAprobados[0] || arrRechazados[0] || "";
    fetch(`${window.almacenRoutes.getLiberacion}?ot=${encodeURIComponent(ot)}`)
        .then((r) => r.json())
        .then((data) => {
            let dest =
                data.registros_por_tipo?.[primerTipo]?.destinatario ||
                data.liberacion?.destinatario ||
                "";
            if (dest) {
                const d = document.getElementById("al-destinatario");
                if (d) d.value = dest;
            }
        })
        .catch(() => {});
    modal.classList.add("open");
    document.body.classList.add("modal-open");
};
window.cerrarModalEnviarAlertaLiberacion = function () {
    const modal = document.getElementById("modalEnviarAlertaLiberacion");
    if (modal) modal.classList.remove("open");
    document.body.classList.remove("modal-open");
};
window.handleAlertaFileChange = function (input, textId, type) {
    const el = document.getElementById(textId);
    if (!el) return;
    if (input.files?.length > 0) {
        el.textContent =
            input.files.length > 1
                ? `${input.files.length} archivo(s)`
                : input.files[0].name;
        el.style.color = "#10b981";
    }
};
document.addEventListener("click", (e) => {
    if (e.target.id === "modalEnviarAlertaLiberacion")
        cerrarModalEnviarAlertaLiberacion();
});
document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") cerrarModalEnviarAlertaLiberacion();
});
document
    .getElementById("formEnviarAlertaLiberacion")
    ?.addEventListener("submit", async function (e) {
        e.preventDefault();
        // 1. Validar campos obligatorios de texto y fecha
        const destinatario = document
            .getElementById("al-destinatario")
            .value.trim();
        if (!destinatario) {
            almacenToast("El campo Destinatario(s) es obligatorio.", "error");
            return;
        }
        const fecha = document.getElementById("al-fecha").value;
        if (!fecha) {
            almacenToast("La fecha es obligatoria.", "error");
            return;
        }
        // 2. Validar archivos de subida obligatorios (los que tienen el atributo "required")
        const form = this;
        const requiredFiles = form.querySelectorAll(
            'input[type="file"][required]',
        );
        let missingFiles = [];
        requiredFiles.forEach((inp) => {
            if (!inp.files || inp.files.length === 0) {
                // Buscar la etiqueta label correspondiente para obtener un nombre descriptivo
                const parentBlock = inp.closest(
                    'div[style*="flex-direction:column"]',
                );
                const label = parentBlock
                    ? parentBlock.querySelector("label")
                    : null;
                let labelText = label
                    ? label.textContent.trim().replace(/\s*\*\s*$/, "")
                    : "";
                if (!labelText) {
                    labelText = inp.name || inp.id;
                }
                missingFiles.push(labelText);
            }
        });
        if (missingFiles.length > 0) {
            almacenToast(
                "Por favor, suba los archivos obligatorios: " +
                    missingFiles.join(", "),
                "error",
            );
            return;
        }
        const ot = document.getElementById("al-ot").value;
        const decision = document.getElementById("al-decision").value;
        const btn = document.getElementById("btn-submit-alerta-liberacion");
        if (!ot || !decision) return;
        const fd = new FormData(this);
        const orig = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = `Enviando...`;
        try {
            const resp = await fetch(
                window.almacenRoutes.enviarAlertaLiberacion,
                {
                    method: "POST",
                    headers: {
                        Accept: "application/json",
                        "X-CSRF-TOKEN":
                            document.querySelector('meta[name="csrf-token"]')
                                ?.content ?? "",
                    },
                    body: fd,
                },
            );
            const data = await resp.json();
            if (data.success) {
                almacenToast(data.message, "success");
                if (window.ModeloStateMachine) {
                    if (decision === "aprobar")
                        window.ModeloStateMachine.onAprobado(ot);
                    else if (decision === "rechazar")
                        window.ModeloStateMachine.onRechazado(ot);
                }
                setTimeout(() => {
                    cerrarModalEnviarAlertaLiberacion();
                    window.location.reload();
                }, 1800);
            } else {
                almacenToast(
                    data.message || "Error al enviar la alerta.",
                    "error",
                );
                btn.disabled = false;
                btn.innerHTML = orig;
            }
        } catch (err) {
            console.error("Error al enviar alerta liberación:", err);
            almacenToast("Error de conexión al enviar la alerta.", "error");
            btn.disabled = false;
            btn.innerHTML = orig;
        }
    });
window.abrirModalFinalizarCalidad = function (
    ot,
    decision,
    tiposAprobados,
    tiposRechazados,
) {
    const modal = document.getElementById("modalFinalizarCalidad");
    if (!modal) return;
    const form = document.getElementById("formFinalizarCalidad");
    if (form) form.reset();
    const arrAprobados = Array.isArray(tiposAprobados) ? tiposAprobados : [];
    const arrRechazados = Array.isArray(tiposRechazados) ? tiposRechazados : [];
    // Recalcular la decisión real desde los arrays, ignorando el parámetro recibido
    // para evitar falsos-positivos de "mixto" cuando solo hay un tipo pendiente.
    const hasAprobados = arrAprobados.length > 0;
    const hasRechazados = arrRechazados.length > 0;
    let effectiveDecision;
    if (hasAprobados && hasRechazados) {
        effectiveDecision = "mixto";
    } else if (hasRechazados) {
        effectiveDecision = "rechazar";
    } else {
        effectiveDecision = "aprobar";
    }
    // Set hidden inputs
    document.getElementById("fc-ot").value = ot;
    document.getElementById("fc-decision").value = effectiveDecision;
    document.getElementById("fc-tipo-modelo").value = [
        ...arrAprobados,
        ...arrRechazados,
    ].join(", ");
    document.getElementById("fc-tipos-aprobados").value =
        JSON.stringify(arrAprobados);
    document.getElementById("fc-tipos-rechazados").value =
        JSON.stringify(arrRechazados);
    // Initialize email destination from dataset
    const inputDestinatario = document.getElementById("fc-destinatario");
    if (inputDestinatario && form) {
        inputDestinatario.value = form.getAttribute("data-email-almacen");
    }
    // Initialize date empty to force selection
    const fDate = document.getElementById("fc-fecha");
    if (fDate) fDate.value = "";
    const otClean = ot.replace(/_\d{8}_\d{6}_.*/, "");
    let baseUrl = window.baseUrl || window.location.origin + "/";
    if (!baseUrl.endsWith("/")) baseUrl += "/";
    // Adapt colors and text dynamically based on the EFFECTIVE decision
    let bg, border, btnBg, titleText, promptHtml, btnText;
    if (effectiveDecision === "aprobar") {
        bg = "linear-gradient(135deg, #10b981, #059669)";
        border = "#10b981";
        btnBg = "#10b981";
        titleText = `Finalizar Proceso de Calidad (Aprobado) — ${otClean}`;
        btnText = "Finalizar y Enviar Alerta de Aprobación";
        promptHtml = `
<div style="background: #ecfdf5; border-left: 5px solid #059669; border-radius: 8px; padding: 15px 20px; display: flex; align-items: center; gap: 15px; box-shadow: inset 0 0 8px rgba(5, 150, 105, 0.03);">
<img src="${baseUrl}images/Aprobado.png" style="width: 32px; height: 32px; object-fit: contain; flex-shrink: 0;" alt="Aprobado">
<div style="font-family:'Poppins', sans-serif; font-weight: 500; color: #065f46; font-size: 1.1em; line-height: 1.5;">
Se enviará la alerta de liberación aprobada para los modelos: <strong>${arrAprobados.join(", ")}</strong>.
</div>
</div>
`;
    } else if (effectiveDecision === "rechazar") {
        bg = "linear-gradient(135deg, #ef4444, #dc2626)";
        border = "#ef4444";
        btnBg = "#ef4444";
        titleText = `Finalizar Proceso de Calidad (Rechazado) — ${otClean}`;
        btnText = "Finalizar y Enviar Alerta de Rechazo";
        promptHtml = `
<div style="background: #fef2f2; border-left: 5px solid #dc2626; border-radius: 8px; padding: 15px 20px; display: flex; align-items: center; gap: 15px; box-shadow: inset 0 0 8px rgba(220, 38, 38, 0.03);">
<img src="${baseUrl}images/Rechazado.png" style="width: 32px; height: 32px; object-fit: contain; flex-shrink: 0;" alt="Rechazado">
<div style="font-family:'Poppins', sans-serif; font-weight: 500; color: #991b1b; font-size: 1.1em; line-height: 1.5;">
Se enviará la alerta de rechazo para los modelos: <strong>${arrRechazados.join(", ")}</strong>.
</div>
</div>
`;
    } else {
        // Mixto
        bg = "linear-gradient(135deg, #0ea5e9, #0284c7)";
        border = "#0ea5e9";
        btnBg = "#0ea5e9";
        titleText = `Finalizar Proceso de Calidad (Mixto) — ${otClean}`;
        btnText = "Finalizar y Enviar Alertas (Mixto)";
        promptHtml = `
<div style="background: #f0f9ff; border-left: 5px solid #0284c7; border-radius: 8px; padding: 15px 20px; display: flex; align-items: center; gap: 15px; box-shadow: inset 0 0 8px rgba(2, 132, 199, 0.03);">
<img src="${baseUrl}images/almacen.png" style="width: 28px; height: 28px; object-fit: contain; flex-shrink: 0;" alt="Mixto">
<div style="font-family:'Poppins', sans-serif; font-weight: 500; color: #075985; font-size: 1.1em; line-height: 1.5;">
Esta OT tiene modelos aprobados (<strong>${arrAprobados.join(", ")}</strong>) y rechazados (<strong>${arrRechazados.join(", ")}</strong>). Se enviarán correos separados de liberación y rechazo.
</div>
</div>
`;
    }
    const header = document.getElementById("finalizar-calidad-header");
    const mc = document.getElementById("finalizar-calidad-modal-content");
    const btnSubmit = document.getElementById("btn-submit-finalizar-calidad");
    const title = document.getElementById("finalizar-calidad-title");
    const prompt = document.getElementById("fc-prompt-text");
    const subtitle = document.getElementById("finalizar-calidad-subtitle");
    if (header) {
        header.style.background = bg;
        header.style.borderBottom = `2px solid ${border}80`;
    }
    if (mc) mc.style.borderColor = border;
    if (title) {
        title.textContent = titleText;
    }
    if (prompt) prompt.innerHTML = promptHtml;
    if (subtitle) subtitle.textContent = `OT: ${otClean}`;
    if (btnSubmit) {
        btnSubmit.innerHTML = btnText;
        btnSubmit.style.background = btnBg;
        btnSubmit.style.boxShadow = `0 4px 15px ${border}40`;
    }
    // Load server files for Calidad finalization
    const filesContainer = document.getElementById("fc-server-files-container");
    const loadHtml = `<div style="text-align:center;color:#64748b;grid-column:1/-1;padding:8px;font-style:italic;font-size:0.8em;">Cargando...</div>`;
    const emptyHtml = `<div style="text-align:center;color:#94a3b8;grid-column:1/-1;padding:8px;font-style:italic;font-size:0.8em;">Sin archivos en servidor.</div>`;
    if (filesContainer) filesContainer.innerHTML = loadHtml;
    fetch(`${window.almacenRoutes.archivos}?ot=${encodeURIComponent(ot)}`)
        .then((r) => r.json())
        .then((data) => {
            if (data.existe && data.archivos?.length > 0) {
                const archivoPerteneceAModelos = (nombre, modelosActivos) => {
                    const pl = nombre.toLowerCase();
                    const todosModelosPosibles = [
                        "candado obturador",
                        "cabeza de soplo",
                        "obturador",
                        "bombillo",
                        "embudo",
                        "corona",
                        "plato",
                        "molde",
                        "fondo",
                    ];
                    const modelosEncontrados = todosModelosPosibles.filter(
                        (m) => pl.includes(m),
                    );
                    if (modelosEncontrados.length === 0) {
                        return false;
                    }
                    const modelosActivosLower = modelosActivos.map((m) =>
                        m.toLowerCase(),
                    );
                    return modelosEncontrados.some((m) =>
                        modelosActivosLower.includes(m),
                    );
                };
                const allRelevantModels = [...arrAprobados, ...arrRechazados];
                const filteredFiles = data.archivos.filter((f) => {
                    const pl = f.nombre.toLowerCase();
                    const isRechazadoFile =
                        pl.includes("documentos_rechazados") ||
                        pl.includes("rechazado") ||
                        pl.includes("scar");
                    const isDibujoOrAyuda =
                        f.tipo === "dibujo" || f.tipo === "ayuda";
                    const isPreordenFile =
                        pl.includes("pre-orden") ||
                        pl.includes("preorden") ||
                        (pl.includes("confirmacionmodelo") &&
                            !pl.includes("casting"));
                    const allRelevantModels = [
                        ...arrAprobados,
                        ...arrRechazados,
                    ];
                    if (isPreordenFile) return true;
                    if (effectiveDecision === "aprobar") {
                        if (isRechazadoFile) return false;
                        // Dibujos/ayudas y preordenes solo de las clases aprobadas
                        return archivoPerteneceAModelos(f.nombre, arrAprobados);
                    }
                    if (effectiveDecision === "rechazar") {
                        if (isRechazadoFile)
                            return archivoPerteneceAModelos(
                                f.nombre,
                                arrRechazados,
                            );
                        // Incluir dibujos/ayudas de las clases rechazadas (contexto útil para el correo)
                        if (isDibujoOrAyuda)
                            return archivoPerteneceAModelos(
                                f.nombre,
                                arrRechazados,
                            );
                        return false;
                    }
                    // mixto
                    if (isRechazadoFile)
                        return archivoPerteneceAModelos(
                            f.nombre,
                            arrRechazados,
                        );
                    if (isDibujoOrAyuda)
                        return archivoPerteneceAModelos(
                            f.nombre,
                            allRelevantModels,
                        );
                    return archivoPerteneceAModelos(
                        f.nombre,
                        allRelevantModels,
                    );
                });
                const sectionsHtml = generarHtmlCategorizadoArchivos(
                    filteredFiles,
                    ot,
                    baseUrl,
                    "calidad",
                );
                if (filesContainer) {
                    filesContainer.innerHTML = sectionsHtml || emptyHtml;
                }
            } else {
                if (filesContainer) filesContainer.innerHTML = emptyHtml;
            }
        })
        .catch((err) => {
            console.error(err);
            if (filesContainer) {
                filesContainer.innerHTML = `<div style="text-align:center;color:#ef4444;grid-column:1/-1;padding:8px;font-weight:600;">Error al cargar archivos.</div>`;
            }
        });
    // Prefill recipient email
    const primerTipo = arrAprobados[0] || arrRechazados[0] || "";
    const formElement = document.getElementById("formFinalizarCalidad");
    const defaultEmail = formElement
        ? formElement.getAttribute("data-email-almacen")
        : "";
    const defaultCalidadEmail = formElement
        ? formElement.getAttribute("data-email-calidad")
        : "";
    fetch(`${window.almacenRoutes.getLiberacion}?ot=${encodeURIComponent(ot)}`)
        .then((r) => r.json())
        .then((data) => {
            let dest =
                data.registros_por_tipo?.[primerTipo]?.destinatario ||
                data.liberacion?.destinatario ||
                defaultEmail;
            const d = document.getElementById("fc-destinatario");
            if (d) d.value = dest;
            const dc = document.getElementById("fc-destinatario-calidad");
            if (dc) dc.value = defaultCalidadEmail;
        })
        .catch(() => {
            const d = document.getElementById("fc-destinatario");
            if (d) d.value = defaultEmail;
            const dc = document.getElementById("fc-destinatario-calidad");
            if (dc) dc.value = defaultCalidadEmail;
        });
    modal.classList.add("open");
    document.body.classList.add("modal-open");
};
window.cerrarModalFinalizarCalidad = function () {
    const modal = document.getElementById("modalFinalizarCalidad");
    if (modal) modal.classList.remove("open");
    document.body.classList.remove("modal-open");
};
document.addEventListener("DOMContentLoaded", () => {
    // Submit handler for formFinalizarCalidad
    const fcForm = document.getElementById("formFinalizarCalidad");
    if (fcForm) {
        fcForm.addEventListener("submit", async function (e) {
            e.preventDefault();
            const destinatario = document
                .getElementById("fc-destinatario")
                .value.trim();
            if (!destinatario) {
                almacenToast(
                    "El campo Destinatario(s) es obligatorio.",
                    "error",
                );
                return;
            }
            const fecha = document.getElementById("fc-fecha").value;
            if (!fecha) {
                almacenToast("La fecha es obligatoria.", "error");
                return;
            }
            const ot = document.getElementById("fc-ot").value;
            const decision = document.getElementById("fc-decision").value;
            const btn = document.getElementById("btn-submit-finalizar-calidad");
            if (!ot || !decision) return;
            const fd = new FormData(this);
            const orig = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = `Enviando...`;
            try {
                const resp = await fetch(
                    window.almacenRoutes.enviarAlertaLiberacion,
                    {
                        method: "POST",
                        headers: {
                            Accept: "application/json",
                            "X-CSRF-TOKEN":
                                document.querySelector(
                                    'meta[name="csrf-token"]',
                                )?.content ?? "",
                        },
                        body: fd,
                    },
                );
                const data = await resp.json();
                if (data.success) {
                    almacenToast(data.message, "success");
                    if (window.ModeloStateMachine) {
                        if (decision === "aprobar")
                            window.ModeloStateMachine.onAprobado(ot);
                        else if (decision === "rechazar")
                            window.ModeloStateMachine.onRechazado(ot);
                    }
                    cerrarModalFinalizarCalidad();
                    let baseUrlLocal =
                        window.baseUrl || window.location.origin + "/";
                    if (!baseUrlLocal.endsWith("/")) baseUrlLocal += "/";
                    const otSafe = ot.replace(/'/g, "\\\\'");
                    const buttons = document.querySelectorAll(
                        `button[onclick*="abrirModalFinalizarCalidad('${otSafe}'"]`,
                    );
                    buttons.forEach((b) => {
                        b.disabled = true;
                        b.style.pointerEvents = "none";
                        b.style.opacity = "0.85";
                        b.style.backgroundColor = "#059669";
                        b.style.borderColor = "#047857";
                        b.style.color = "#ffffff";
                        b.style.cursor = "not-allowed";
                        b.title = "El correo de alerta ha sido enviado exitosamente";
                        b.innerHTML = `<img src="${baseUrlLocal}images/enviando.png" style="filter: none !important;" alt=""> <span>Correo Enviado</span>`;
                    });
                    setTimeout(() => {
                        window.location.reload();
                    }, 1800);
                } else {
                    almacenToast(
                        data.message || "Error al enviar la alerta.",
                        "error",
                    );
                    btn.disabled = false;
                    btn.innerHTML = orig;
                }
            } catch (err) {
                console.error("Error al enviar alerta liberación:", err);
                almacenToast("Error de conexión al enviar la alerta.", "error");
                btn.disabled = false;
                btn.innerHTML = orig;
            }
        });
    }
    // General Fecha Entrega change handler to populate row-level dates if empty
    document.addEventListener("change", (e) => {
        if (
            e.target &&
            (e.target.id === "poc-p1-fecha-entrega" ||
                e.target.id === "poc-p2-fecha-entrega")
        ) {
            const pageNum = e.target.id === "poc-p1-fecha-entrega" ? 1 : 2;
            savePocPageData(pageNum);
            loadPocPage(pageNum);
        }
    });
});
document.addEventListener("click", (e) => {
    if (e.target.id === "modalFinalizarCalidad") cerrarModalFinalizarCalidad();
});
document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") cerrarModalFinalizarCalidad();
});
/* =========================================================================
NUEVAS FUNCIONES: PRE-ORDEN DE FABRICACIÓN DE CASTING (DOUBLE MODAL TABS)
========================================================================= */
let pocActivePage = 1;
let pocState = {
    ot_raw: "",
    moldura: "",
    allClases: [],
    page1: {
        proveedor: "",
        fecha: new Date().toISOString().substring(0, 10),
        folio: "",
        observaciones: "",
        fecha_entrega: "",
        filas: [],
    },
    page2: {
        proveedor: "",
        fecha: new Date().toISOString().substring(0, 10),
        folio: "",
        observaciones: "",
        fecha_entrega: "",
        filas: [],
    },
};
window.materialesCastingPersonalizados = [];
function recopilarMaterialesPersonalizados() {
    window.materialesCastingPersonalizados = [];
    const paginas = [pocState.page1, pocState.page2];
    paginas.forEach((p) => {
        if (p && p.filas) {
            p.filas.forEach((f) => {
                const mat = f.material;
                if (
                    mat &&
                    !MATERIALES_CASTING_FIJOS.includes(mat) &&
                    mat !== "Otro"
                ) {
                    // Si el material tiene comas del estado anterior, las separamos, si no se procesa directo
                    const partes = mat
                        .split(",")
                        .map((s) => s.trim())
                        .filter(Boolean);
                    partes.forEach((pmat) => {
                        if (
                            pmat &&
                            !MATERIALES_CASTING_FIJOS.includes(pmat) &&
                            !window.materialesCastingPersonalizados.includes(
                                pmat,
                            )
                        ) {
                            window.materialesCastingPersonalizados.push(pmat);
                        }
                    });
                }
            });
        }
    });
}
// Helper: habilita o deshabilita el 'required' de los inputs de la página 2
// para evitar el error "invalid form control is not focusable" cuando está oculta.
function setPocPage2Required(enable) {
    const page2 = document.getElementById("poc-page-2");
    if (!page2) return;
    page2.querySelectorAll("input, select, textarea").forEach((el) => {
        if (enable) {
            if (el.dataset.wasRequired === "1") el.setAttribute("required", "");
        } else {
            if (el.hasAttribute("required")) {
                el.dataset.wasRequired = "1";
                el.removeAttribute("required");
            } else {
                el.dataset.wasRequired = "0";
            }
        }
    });
}
window.abrirModalPreOrdenCasting = async function (ot) {
    pocState.ot_raw = ot;
    pocActivePage = 1;
    document.getElementById("poc-has-page2").value = "0";
    // Ocultar tab/sección de página 2, mostrar página 1
    document.getElementById("tab-poc-page-2").classList.add("cal-display-none");
    document
        .getElementById("btn-remove-poc-page-2")
        .classList.add("cal-display-none");
    document
        .getElementById("btn-add-poc-page-2")
        .classList.remove("cal-display-none");
    document.getElementById("poc-page-2").classList.add("cal-display-none");
    document.getElementById("poc-page-1").classList.remove("cal-display-none");
    setPocPage2Required(false);
    const tab1 = document.getElementById("tab-poc-page-1");
    tab1.className = "btn-po-tab active";
    tab1.style.background = "#0369a1";
    tab1.style.color = "white";
    tab1.style.borderColor = "#0369a1";
    document.getElementById("poc-modal-subtitle").textContent = `OT: ${ot}`;
    try {
        const resp = await fetch(
            `${window.almacenRoutes.getOtData}?ot=${encodeURIComponent(ot)}&type=casting`,
        );
        const res = await resp.json();
        if (res.success) {
            pocState.allClases = res.clases || [];
            pocState.moldura = res.moldura;
            // Folios iniciales
            pocState.page1.folio = res.folio;
            pocState.page2.folio = res.folio;
            const todayStr = new Date().toISOString().substring(0, 10);
            pocState.page1.fecha = todayStr;
            pocState.page2.fecha = todayStr;
            // Cargar datos preexistentes si existen
            const castingOrders = (res.pre_ordenes || []).filter((po) => {
                if (po.filas && po.filas.length > 0) {
                    const firstFila = po.filas[0];
                    return firstFila.cant_fabricar !== undefined;
                }
                return false;
            });
            if (castingOrders.length > 0) {
                // Sí hay órdenes de casting guardadas, usarlas
                const po1 = castingOrders[0];
                pocState.page1.proveedor = po1.proveedor;
                pocState.page1.fecha = po1.fecha_creacion
                    ? po1.fecha_creacion.substring(0, 10)
                    : todayStr;
                pocState.page1.folio = po1.folio;
                pocState.page1.observaciones = po1.observaciones || "";
                pocState.page1.fecha_entrega = po1.fecha_entrega
                    ? po1.fecha_entrega.substring(0, 10)
                    : po1.filas[0]?.fecha_entrega || res.fecha_entrega || "";
                pocState.page1.filas = po1.filas.map((f) => ({
                    id_clase: f.id_clase || f.clase_id,
                    tipo_modelo:
                        f.tipo_modelo ||
                        getTipoModeloFromClase(f.clase || f.descripcion),
                    impresiones: f.impresiones || 1,
                    cant_fabricar:
                        f.cant_fabricar !== undefined ? f.cant_fabricar : 0,
                    cant_consignacion: f.cant_consignacion || 0,
                    descripcion: f.clase || f.descripcion || "",
                    material: f.material || "Hierro Gris",
                    codigo: f.codigo || f.codigo_modelo || "",
                    peso_juego: f.peso_juego || 0,
                    peso_total: f.peso_total || 0,
                    fecha_entrega:
                        f.fecha_entrega ||
                        po1.fecha_entrega ||
                        res.fecha_entrega ||
                        "",
                }));
                if (castingOrders.length > 1) {
                    const po2 = castingOrders[1];
                    pocState.page2.proveedor = po2.proveedor;
                    pocState.page2.fecha = po2.fecha_creacion
                        ? po2.fecha_creacion.substring(0, 10)
                        : todayStr;
                    pocState.page2.folio = po2.folio;
                    pocState.page2.observaciones = po2.observaciones || "";
                    pocState.page2.fecha_entrega = po2.fecha_entrega
                        ? po2.fecha_entrega.substring(0, 10)
                        : po2.filas[0]?.fecha_entrega ||
                          res.fecha_entrega ||
                          "";
                    pocState.page2.filas = po2.filas.map((f) => ({
                        id_clase: f.id_clase || f.clase_id,
                        tipo_modelo:
                            f.tipo_modelo ||
                            getTipoModeloFromClase(f.clase || f.descripcion),
                        impresiones: f.impresiones || 1,
                        cant_fabricar:
                            f.cant_fabricar !== undefined ? f.cant_fabricar : 0,
                        cant_consignacion: f.cant_consignacion || 0,
                        descripcion: f.clase || f.descripcion || "",
                        material: f.material || "Hierro Gris",
                        codigo: f.codigo || f.codigo_modelo || "",
                        peso_juego: f.peso_juego || 0,
                        peso_total: f.peso_total || 0,
                        fecha_entrega:
                            f.fecha_entrega ||
                            po2.fecha_entrega ||
                            res.fecha_entrega ||
                            "",
                    }));
                    fillPageData(pocState.page2, castingPos[1]);
                } else {
                    pocState.page2.filas = [];
                    pocState.page2.fecha_entrega = res.fecha_entrega || "";
                }
                // Siempre iniciar con el Proveedor 2 deshabilitado y oculto hasta que se haga clic en Agregar Proveedor 2
                document.getElementById("poc-has-page2").value = "0";
                document
                    .getElementById("tab-poc-page-2")
                    .classList.add("alm-display-none", "cal-display-none");
                document
                    .getElementById("btn-remove-poc-page-2")
                    .classList.add("alm-display-none", "cal-display-none");
                document
                    .getElementById("btn-add-poc-page-2")
                    .classList.remove("alm-display-none", "cal-display-none");
                setPocPage2Required(false);
            } else {
                // No hay órdenes de casting guardadas. Revisar si hay una orden standard para prellenar los datos
                document.getElementById("poc-has-page2").value = "0";
                document
                    .getElementById("tab-poc-page-2")
                    .classList.add("alm-display-none", "cal-display-none");
                document
                    .getElementById("btn-remove-poc-page-2")
                    .classList.add("alm-display-none", "cal-display-none");
                document
                    .getElementById("btn-add-poc-page-2")
                    .classList.remove("alm-display-none", "cal-display-none");
                setPocPage2Required(false);

                const standardPo = (res.pre_ordenes || []).find((po) => {
                    if (po.filas && po.filas.length > 0) {
                        const firstFila = po.filas[0];
                        return firstFila.cant_fabricar === undefined;
                    }
                    return false;
                });
                if (standardPo) {
                    pocState.page1.proveedor = standardPo.proveedor || "";
                    pocState.page1.observaciones =
                        standardPo.observaciones || "";
                    pocState.page1.fecha_entrega =
                        standardPo.filas[0]?.fecha_entrega ||
                        res.fecha_entrega ||
                        "";
                    // Mapear las filas del standard a formato casting
                    pocState.page1.filas = pocState.allClases.map((c) => {
                        const matchingFila = (standardPo.filas || []).find(
                            (f) => (f.id_clase || f.clase_id) == c.id,
                        );
                        return {
                            id_clase: c.id,
                            tipo_modelo: getTipoModeloFromClase(c.nombre),
                            impresiones: matchingFila
                                ? matchingFila.impresiones || 1
                                : 1,
                            cant_fabricar: "",
                            cant_consignacion: 0,
                            descripcion: c.nombre,
                            material: "",
                            codigo: "",
                            peso_juego: 0,
                            peso_total: 0,
                            fecha_entrega: res.fecha_entrega || "",
                        };
                    });
                } else {
                    // No hay pre-órdenes previas en absoluto. Usar valores por defecto y clases de la OT
                    pocState.page1.proveedor = "";
                    pocState.page1.observaciones = "";
                    pocState.page1.fecha_entrega = res.fecha_entrega || "";
                    pocState.page1.filas = pocState.allClases.map((c) => ({
                        id_clase: c.id,
                        tipo_modelo: getTipoModeloFromClase(c.nombre),
                        impresiones: 1,
                        cant_fabricar: "",
                        cant_consignacion: 0,
                        descripcion: c.nombre,
                        material: "",
                        codigo: "",
                        peso_juego: 0,
                        peso_total: 0,
                        fecha_entrega: res.fecha_entrega || "",
                    }));
                }
                pocState.page2.filas = [];
                pocState.page2.fecha_entrega = res.fecha_entrega || "";
            }
            recopilarMaterialesPersonalizados();
            pocActivePage = 0; // Forzar cambio a pagina 1
            switchPocPage(1);
            const modal = document.getElementById("modalPreOrdenCasting");
            modal.classList.add("open");
            document.body.classList.add("modal-open");
        } else {
            almacenToast(
                res.message || "Error al obtener datos de la OT.",
                "error",
            );
        }
    } catch (err) {
        console.error("Error fetching casting OT data:", err);
        almacenToast("Error de red al obtener datos de la OT.", "error");
    }
};
window.cerrarModalPreOrdenCasting = function () {
    const modal = document.getElementById("modalPreOrdenCasting");
    if (modal) modal.classList.remove("open");
    document.body.classList.remove("modal-open");
};
window.switchPocPage = function (pageNum) {
    if (pageNum === pocActivePage) return;
    const hasPage2 = document.getElementById("poc-has-page2")?.value === "1";
    if (pageNum === 2 && !hasPage2) return;

    savePocPageData(pocActivePage);
    const tab1 = document.getElementById("tab-poc-page-1");
    const tab2 = document.getElementById("tab-poc-page-2");
    if (pageNum === 1) {
        if (tab1) {
            tab1.classList.add("active");
            tab1.style.background = "#ffffff";
            tab1.style.color = "#0369a1";
            tab1.style.borderColor = "#ffffff";
            tab1.style.boxShadow = "0 -4px 12px rgba(0,0,0,0.15)";
        }
        if (tab2) {
            tab2.classList.remove("active");
            tab2.style.background = "rgba(255, 255, 255, 0.2)";
            tab2.style.color = "#ffffff";
            tab2.style.borderColor = "transparent";
            tab2.style.boxShadow = "none";
            if (!hasPage2) {
                tab2.classList.add("alm-display-none", "cal-display-none");
            }
        }
        document.getElementById("poc-page-2")?.classList.add("alm-display-none", "cal-display-none");
        document
            .getElementById("poc-page-1")
            ?.classList.remove("alm-display-none", "cal-display-none");
        setPocPage2Required(false);
    } else {
        if (tab2) {
            tab2.classList.add("active");
            tab2.style.background = "#ffffff";
            tab2.style.color = "#0369a1";
            tab2.style.borderColor = "#ffffff";
            tab2.style.boxShadow = "0 -4px 12px rgba(0,0,0,0.15)";
            tab2.classList.remove("alm-display-none", "cal-display-none");
        }
        if (tab1) {
            tab1.classList.remove("active");
            tab1.style.background = "rgba(255, 255, 255, 0.2)";
            tab1.style.color = "#ffffff";
            tab1.style.borderColor = "transparent";
            tab1.style.boxShadow = "none";
        }
        document.getElementById("poc-page-1")?.classList.add("alm-display-none", "cal-display-none");
        document
            .getElementById("poc-page-2")
            ?.classList.remove("alm-display-none", "cal-display-none");
        setPocPage2Required(true);
    }
    pocActivePage = pageNum;
    loadPocPage(pageNum);
};
window.agregarPocPagina2 = function () {
    savePocPageData(1);
    document.getElementById("poc-has-page2").value = "1";
    document
        .getElementById("tab-poc-page-2")
        .classList.remove("alm-display-none", "cal-display-none");
    document
        .getElementById("btn-add-poc-page-2")
        .classList.add("alm-display-none", "cal-display-none");
    document
        .getElementById("btn-remove-poc-page-2")
        .classList.remove("alm-display-none", "cal-display-none");
    if (pocState.page2.filas.length === 0) {
        pocState.page2.fecha_entrega =
            pocState.page2.fecha_entrega || pocState.page1.fecha_entrega || "";
        pocState.page2.filas = pocState.allClases.map((c) => ({
            id_clase: c.id,
            tipo_modelo: getTipoModeloFromClase(c.nombre),
            impresiones: 1,
            cant_fabricar: "",
            cant_consignacion: 0,
            descripcion: c.nombre,
            material: "",
            codigo: "",
            peso_juego: 0,
            peso_total: 0,
            fecha_entrega: "",
        }));
    }
    switchPocPage(2);
};
window.removerPocPagina2 = function () {
    if (
        confirm(
            "¿Está seguro de remover el Proveedor 2? Se perderán sus datos cargados.",
        )
    ) {
        document.getElementById("poc-has-page2").value = "0";
        document
            .getElementById("tab-poc-page-2")
            .classList.add("alm-display-none", "cal-display-none");
        document
            .getElementById("btn-remove-poc-page-2")
            .classList.add("alm-display-none", "cal-display-none");
        document
            .getElementById("btn-add-poc-page-2")
            .classList.remove("alm-display-none", "cal-display-none");
        setPocPage2Required(false);
        pocState.page2.filas = [];
        switchPocPage(1);
    }
};
window.handlePocProveedorChange = function (pageNum) {
    const provSelect1 = document.getElementById("poc-p1-proveedor");
    const provSelect2 = document.getElementById("poc-p2-proveedor");
    if (pageNum === 1 && provSelect2) {
        if (provSelect1.value === provSelect2.value) {
            almacenToast(
                "No puedes seleccionar el mismo proveedor para ambas páginas.",
                "warning",
            );
            for (let opt of provSelect2.options) {
                if (opt.value !== provSelect1.value) {
                    provSelect2.value = opt.value;
                    break;
                }
            }
        }
    } else if (pageNum === 2 && provSelect1) {
        if (provSelect1.value === provSelect2.value) {
            almacenToast(
                "No puedes seleccionar el mismo proveedor para ambas páginas.",
                "warning",
            );
            for (let opt of provSelect1.options) {
                if (opt.value !== provSelect2.value) {
                    provSelect1.value = opt.value;
                    break;
                }
            }
        }
    }
};
function getTipoModeloFromClase(claseNombre) {
    const clLow = (claseNombre || "").toLowerCase();
    if (clLow.includes("fondo")) return "Fondo";
    if (clLow.includes("obturador")) return "Obturador";
    if (clLow.includes("molde")) return "Molde";
    if (clLow.includes("bombillo")) return "Bombillo";
    return "Otro";
}
// Tabla de consignaciones por rango de fabricar
function calcularConsignacion(cantFabricar, tipoModelo) {
    const esMolde = (tipoModelo || "").toLowerCase().includes("molde");
    const rangos = [
        { max: 3, molde: 0.5, otro: 0.5 }, // PRUEBAS [2-3]
        { max: 6, molde: 0.5, otro: 0.35 }, // [1-6]
        { max: 12, molde: 0.25, otro: 0.25 }, // [7-12]
        { max: 24, molde: 0.17, otro: 0.13 }, // [13-24]
        { max: 50, molde: 0.1, otro: 0.1 }, // [25-50]
        { max: 80, molde: 0.08, otro: 0.08 }, // [51-80]
        { max: Infinity, molde: 0.05, otro: 0.065 }, // [81-120+]
    ];
    const rango =
        rangos.find((r) => cantFabricar <= r.max) || rangos[rangos.length - 1];
    const pct = esMolde ? rango.molde : rango.otro;
    return cantFabricar + Math.ceil(cantFabricar * pct);
}
function autoGenerarCodigo(tipoModelo, claseNombre, ot) {
    if (!claseNombre) return "";
    const letter = claseNombre.trim().substring(0, 1).toUpperCase();
    const otClean = (ot || "")
        .split("-")[0]
        .replace(/_[rR]\d+.*/, "")
        .trim();
    const otNum = otClean.replace(/[^0-9]/g, "");
    const prefix = tipoModelo === "Templadera" ? "T" : "";
    return `${prefix}${letter}${otNum}`;
}
const MATERIALES_CASTING = [
    "Hierro Gris",
    "Hierro Híbrido",
    "Hierro Nodular",
    "Minox",
    "Otro",
];
const MATERIALES_CASTING_FIJOS = [
    "Hierro Gris",
    "Hierro Híbrido",
    "Hierro Nodular",
    "Minox",
];
const POC_MATERIAL_MAX = 7;
function loadPocPage(pageNum) {
    const pData = pocState["page" + pageNum];
    const todayStr = new Date().toISOString().substring(0, 10);
    const provEl = document.getElementById(`poc-p${pageNum}-proveedor`);
    if (provEl) {
        provEl.value = pData.proveedor || "";
        // If empty, ensure placeholder is shown (select stays on disabled blank option)
        if (!pData.proveedor) provEl.selectedIndex = 0;
    }
    const fechaEl = document.getElementById(`poc-p${pageNum}-fecha`);
    if (fechaEl) {
        fechaEl.value = pData.fecha || todayStr;
        fechaEl.readOnly = true;
        fechaEl.disabled = true;
    }
    document.getElementById(`poc-p${pageNum}-folio`).value = pData.folio;
    document.getElementById(`poc-p${pageNum}-moldura`).value = pocState.moldura;
    let pocOtNumber = pocState.ot_raw.replace(/_\d{8}_\d{6}_.*/, "");
    if (pocOtNumber.includes(" - ")) {
        pocOtNumber = pocOtNumber.split(" - ")[0].trim();
    }
    pocOtNumber = pocOtNumber.split("_")[0].replace(/[^0-9]/g, "");
    document.getElementById(`poc-p${pageNum}-ot`).value = pocOtNumber;
    document.getElementById(`poc-p${pageNum}-observaciones`).value =
        pData.observaciones || "";

    const tbody = document.getElementById(`alm-tbody-poc-p${pageNum}`);
    tbody.innerHTML = "";
    const tiposModelo = ["Suelto", "Placa", "Templadera"];
    pData.filas.forEach((fila, idx) => {
        const tr = document.createElement("tr");
        tr.style.borderBottom = "1px solid #e2e8f0";
        tr.style.transition = "background 0.2s ease";
        tr.onmouseover = function() { tr.style.background = "#f8fafc"; };
        tr.onmouseout = function() { tr.style.background = "#ffffff"; };
        let tipoOpts = `<option value="">-- Tipo --</option>`;
        tiposModelo.forEach((t) => {
            const sel = fila.tipo_modelo === t ? "selected" : "";
            tipoOpts += `<option value="${t}" ${sel}>${t}</option>`;
        });
        let claseOptions = '<option value="">-- Clase --</option>';
        pocState.allClases.forEach((c) => {
            const selected = c.id == fila.id_clase ? "selected" : "";
            claseOptions += `<option value="${c.id}" ${selected}>${c.nombre}</option>`;
        });
        const codigoVal =
            fila.codigo ||
            autoGenerarCodigo(
                fila.tipo_modelo,
                fila.descripcion,
                pocState.ot_raw,
            );
        const materialesDisponibles = [
            ...MATERIALES_CASTING_FIJOS,
            ...window.materialesCastingPersonalizados,
        ];
        const materialFila = fila.material
            ? fila.material.split(",")[0].trim()
            : "";
        if (
            materialFila &&
            !materialesDisponibles.includes(materialFila) &&
            materialFila !== "Otro"
        ) {
            window.materialesCastingPersonalizados.push(materialFila);
            materialesDisponibles.push(materialFila);
        }
        let matOpts = `<option value="">-- Material --</option>`;
        materialesDisponibles.forEach((m) => {
            const sel = m === materialFila ? "selected" : "";
            matOpts += `<option value="${m}" ${sel}>${m}</option>`;
        });
        const limiteAlcanzado = materialesDisponibles.length >= 7;
        if (!limiteAlcanzado) {
            matOpts += `<option value="Otro">Otro</option>`;
        }
        tr.innerHTML = `
            <td style="padding:10px 8px;min-width:110px;">
                <select name="tipo_modelo" class="form-control poc-input-tipo" required style="font-size:0.9em; padding:6px 10px; border-radius:8px;" onchange="handlePocTipoChange(${pageNum},${idx},this)">
                    ${tipoOpts}
                </select>
            </td>
            <td style="padding:10px 8px;min-width:90px;">
                <input type="number" name="cant_fabricar" class="form-control poc-input-cant-fabricar" min="1" value="${fila.cant_fabricar !== undefined && fila.cant_fabricar !== null && fila.cant_fabricar !== "" ? fila.cant_fabricar : ""}" placeholder="Ej: 100" required style="font-size:0.9em; padding:6px 10px; border-radius:8px;" oninput="recalcPocRowWeight(${pageNum},${idx})">
            </td>
            <td style="padding:10px 8px;min-width:90px;">
                <input type="number" name="cant_consignacion" class="form-control poc-input-cant-consignacion" min="0" value="${fila.cant_consignacion || ""}" placeholder="Auto" required style="background:#f0fdf4; border-color:#bbf7d0; color:#15803d; font-weight:700; font-size:0.9em; padding:6px 10px; border-radius:8px;" title="Se calcula automáticamente al llenar Cant. Fabricar. Puedes modificarlo si es necesario.">
            </td>
            <td style="padding:10px 8px;">
                <select name="id_clase" class="form-control poc-select-clase" required style="font-size:0.9em; padding:6px 10px; border-radius:8px;" onchange="handlePocClaseChange(${pageNum},${idx},this)">
${claseOptions}
</select>
</td>
<td style="padding:8px;min-width:160px;vertical-align:middle;">
<div class="poc-material-wrapper" data-page="${pageNum}" data-idx="${idx}">
<div style="display:flex;gap:6px;align-items:center;">
<select class="form-control poc-input-material" style="flex:1;font-size:0.88em;" required onchange="handlePocMaterialChange(${pageNum},${idx},this)">
${matOpts}
</select>
${
    materialFila &&
    !MATERIALES_CASTING_FIJOS.includes(materialFila) &&
    materialFila !== "Otro"
        ? `<button type="button" class="btn-eliminar-material-opcion" onclick="eliminarMaterialGlobal(${pageNum}, '${materialFila.replace(/'/g, "\\'")}')" 
style="background:#fff;border:none;border-radius:6px;width:28px;height:28px;display:flex;align-items:center;justify-content:center;cursor:pointer;padding:0;flex-shrink:0;" title="Quitar de la lista de materiales">
<img src="${getBaseUrl()}images/quitar.png" style="width:16px;height:16px;">
</button>`
        : ""
}
</div>
<input type="text" class="poc-input-material-custom" placeholder="Especificar material..." style="display:none;margin-top:4px;padding:5px 8px;border:1.5px solid #0284c7;border-radius:8px;width:100%;font-family:'Poppins',sans-serif;font-size:0.88em;" onkeydown="handlePocMaterialCustomKey(${pageNum},${idx},event,this)" oninput="handlePocMaterialCustomInput(${pageNum},${idx},this)" onblur="handlePocMaterialCustomBlur(${pageNum},${idx},this)">
${
    limiteAlcanzado
        ? `<span class="poc-material-hint" style="font-size:0.72em;color:#f97316;margin-top:2px;display:block;">Límite de 7 materiales alcanzado.</span>`
        : ""
}
</div>
</td>
<td style="padding:8px;min-width:120px;">
<input type="text" name="codigo" class="form-control poc-input-codigo" value="${codigoVal}" placeholder="Ej: F2102" required>
</td>
<td style="padding:8px;min-width:90px;">
<input type="number" step="0.01" name="peso_juego" class="form-control poc-input-peso-juego" min="0" value="${fila.peso_juego > 0 ? fila.peso_juego : ""}" placeholder="KG p/juego" oninput="recalcPocRowWeight(${pageNum},${idx})">
</td>
<td style="padding:8px;min-width:90px;">
<input type="number" step="0.01" name="peso_total" class="form-control poc-input-peso-total" min="0" value="${fila.peso_total > 0 ? fila.peso_total : ""}" placeholder="KG total">
</td>
<td style="padding:8px;min-width:120px;">
<input type="date" name="fecha_entrega" class="form-control poc-input-fecha-entrega" value="${fila.fecha_entrega || ""}" style="font-size:0.9em; padding: 6px 10px;">
</td>
<td style="padding:8px;text-align:center;">
<button type="button" class="btn-eliminar-fila" onclick="eliminarFilaPoc(${pageNum},${idx})" style="background:none;border:none;cursor:pointer;">
<img src="${getBaseUrl()}images/quitar.png" style="width:24px;height:24px;">
</button>
</td>
`;
        tbody.appendChild(tr);
    });
}
window.handlePocTipoChange = function (pageNum, idx, selectEl) {
    const pData = pocState["page" + pageNum];
    const row = pData.filas[idx];
    row.tipo_modelo = selectEl.value;
    // Auto-update codigo when tipo changes
    const tr = selectEl.closest("tr");
    const codEl = tr.querySelector(".poc-input-codigo");
    const claseSel = tr.querySelector(".poc-select-clase");
    if (codEl && !codEl.dataset.userEdited) {
        const claseNombre =
            claseSel && claseSel.selectedIndex >= 0
                ? claseSel.options[claseSel.selectedIndex].text
                : row.descripcion || "";
        codEl.value = autoGenerarCodigo(
            selectEl.value,
            claseNombre,
            pocState.ot_raw,
        );
    }
    // Recalcular consignacion al cambiar tipo (afecta tabla molde vs otros)
    recalcPocRowWeight(pageNum, idx);
};
// ── DYNAMIC SINGLE MATERIAL LOGIC ──
window.handlePocMaterialChange = function (pageNum, idx, selectEl) {
    const pData = pocState["page" + pageNum];
    const row = pData.filas[idx];
    if (selectEl.value === "Otro") {
        // Mostrar input personalizado
        const wrapper = selectEl.closest(".poc-material-wrapper");
        const customInput =
            wrapper && wrapper.querySelector(".poc-input-material-custom");
        if (customInput) {
            customInput.classList.remove("cal-display-none");
            customInput.value = "";
            customInput.focus();
        }
        selectEl.classList.add("cal-display-none");
        return;
    }
    row.material = selectEl.value;
    loadPocPage(pageNum);
};
window.handlePocMaterialCustomInput = function (pageNum, idx, inputEl) {
    // Solo preview
};
window.handlePocMaterialCustomBlur = function (pageNum, idx, inputEl) {
    confirmPocMaterialCustom(pageNum, idx, inputEl);
};
window.handlePocMaterialCustomKey = function (pageNum, idx, event, inputEl) {
    if (event.key === "Enter") {
        event.preventDefault();
        confirmPocMaterialCustom(pageNum, idx, inputEl);
    }
};
function confirmPocMaterialCustom(pageNum, idx, inputEl) {
    const val = inputEl.value.trim().replace(/\b\w/g, (c) => c.toUpperCase());
    const wrapper = inputEl.closest(".poc-material-wrapper");
    const selectEl = wrapper && wrapper.querySelector(".poc-input-material");
    if (!val) {
        // Cancelar y restaurar select
        inputEl.classList.add("cal-display-none");
        if (selectEl) selectEl.classList.remove("cal-display-none");
        return;
    }
    const pData = pocState["page" + pageNum];
    const row = pData.filas[idx];
    const materialesDisponibles = [
        ...MATERIALES_CASTING_FIJOS,
        ...window.materialesCastingPersonalizados,
    ];
    if (!materialesDisponibles.includes(val)) {
        if (materialesDisponibles.length >= 7) {
            almacenToast(
                "Límite de 7 materiales en el selector alcanzado.",
                "error",
            );
            inputEl.classList.add("cal-display-none");
            if (selectEl) selectEl.classList.remove("cal-display-none");
            loadPocPage(pageNum);
            return;
        }
        window.materialesCastingPersonalizados.push(val);
    }
    row.material = val;
    inputEl.classList.add("cal-display-none");
    if (selectEl) selectEl.classList.remove("cal-display-none");
    // Recargar vista para actualizar todos los dropdowns
    loadPocPage(pageNum);
}
window.eliminarMaterialGlobal = function (pageNum, mat) {
    // 1. Quitar de la lista global de personalizados
    window.materialesCastingPersonalizados =
        window.materialesCastingPersonalizados.filter((m) => m !== mat);
    // 2. Limpiar la selección de cualquier fila que usara este material
    const paginas = [pocState.page1, pocState.page2];
    paginas.forEach((p) => {
        if (p && p.filas) {
            p.filas.forEach((f) => {
                if (f.material === mat) {
                    f.material = ""; // Resetea a vacío
                }
            });
        }
    });
    almacenToast(`Material "${mat}" eliminado de las opciones.`, "success");
    loadPocPage(pageNum);
};
window.handlePocClaseChange = function (pageNum, idx, selectEl) {
    const pData = pocState["page" + pageNum];
    const row = pData.filas[idx];
    const selectedOption = selectEl.options[selectEl.selectedIndex];
    if (selectedOption && selectedOption.value) {
        row.id_clase = selectedOption.value;
        row.descripcion = selectedOption.text;
        // Auto-update codigo when clase changes
        const tr = selectEl.closest("tr");
        const codEl = tr.querySelector(".poc-input-codigo");
        const tipoEl = tr.querySelector(".poc-input-tipo");
        if (codEl && !codEl.dataset.userEdited) {
            codEl.value = autoGenerarCodigo(
                tipoEl ? tipoEl.value : "",
                selectedOption.text,
                pocState.ot_raw,
            );
        }
    }
};
// Mark codigo as user-edited when touched
document.addEventListener("input", function (e) {
    if (e.target.classList.contains("poc-input-codigo")) {
        e.target.dataset.userEdited = "1";
    }
});
window.recalcPocRowWeight = function (pageNum, idx) {
    const pData = pocState["page" + pageNum];
    const row = pData.filas[idx];
    const tbody = document.getElementById(`alm-tbody-poc-p${pageNum}`);
    const tr = tbody.children[idx];
    if (!tr) return;
    const fabInput = tr.querySelector(".poc-input-cant-fabricar");
    const consInput = tr.querySelector(".poc-input-cant-consignacion");
    const juegoInput = tr.querySelector(".poc-input-peso-juego");
    const totalInput = tr.querySelector(".poc-input-peso-total");
    const tipoInput = tr.querySelector(".poc-input-tipo");
    const fab = parseInt(fabInput ? fabInput.value : 0) || 0;
    const juego = parseFloat(juegoInput ? juegoInput.value : 0) || 0;
    const tipoModelo = tipoInput ? tipoInput.value : row.tipo_modelo || "";
    // Auto-calcular consignación basada en tabla (solo si el usuario acaba de cambiar "fabricar")
    if (fabInput && document.activeElement === fabInput) {
        const autocons = calcularConsignacion(fab, tipoModelo);
        if (consInput) {
            consInput.value = autocons;
            consInput.style.background = "#f0fdf4";
        }
        row.cant_consignacion = autocons;
    } else {
        row.cant_consignacion = parseInt(consInput ? consInput.value : 0) || 0;
    }
    row.cant_fabricar = fab;
    row.peso_juego = juego;
    // peso_total = peso_juego × cant_consignacion
    const totalWeight = parseFloat((juego * row.cant_consignacion).toFixed(3));
    row.peso_total = totalWeight;
    if (totalInput) {
        totalInput.value = totalWeight;
    }
};
window.agregarFilaPoc = function (pageNum) {
    savePocPageData(pageNum);
    pocState["page" + pageNum].filas.push({
        id_clase: "",
        tipo_modelo: "",
        cant_fabricar: "",
        cant_consignacion: 0,
        descripcion: "",
        material: "",
        codigo: "",
        peso_juego: 0,
        peso_total: 0,
        fecha_entrega: "",
    });
    loadPocPage(pageNum);
};
window.eliminarFilaPoc = function (pageNum, idx) {
    savePocPageData(pageNum);
    pocState["page" + pageNum].filas.splice(idx, 1);
    loadPocPage(pageNum);
};
function savePocPageData(pageNum) {
    if (!pageNum || (pageNum !== 1 && pageNum !== 2)) return;
    const pData = pocState["page" + pageNum];
    if (!pData) return;
    const provEl = document.getElementById(`poc-p${pageNum}-proveedor`);
    const folioEl = document.getElementById(`poc-p${pageNum}-folio`);
    const obsEl = document.getElementById(`poc-p${pageNum}-observaciones`);
    const fechaEntregaEl = document.getElementById(
        `poc-p${pageNum}-fecha-entrega`,
    );
    if (provEl) pData.proveedor = provEl.value;
    // Preservar la fecha guardada; solo asignar hoy si aún no tiene fecha
    if (!pData.fecha) pData.fecha = new Date().toISOString().substring(0, 10);
    if (folioEl) pData.folio = folioEl.value;
    if (obsEl) pData.observaciones = obsEl.value;
    if (fechaEntregaEl) pData.fecha_entrega = fechaEntregaEl.value;
    const tbody = document.getElementById(`alm-tbody-poc-p${pageNum}`);
    if (tbody) {
        const rows = tbody.querySelectorAll("tr");
        rows.forEach((tr, idx) => {
            const rowState = pData.filas[idx];
            if (!rowState) return;
            const tipoSel = tr.querySelector(".poc-input-tipo");
            rowState.tipo_modelo = tipoSel
                ? tipoSel.value
                : rowState.tipo_modelo || "";
            const rawCant = tr.querySelector(".poc-input-cant-fabricar")?.value;
            rowState.cant_fabricar =
                rawCant !== undefined && rawCant !== ""
                    ? parseInt(rawCant)
                    : "";
            rowState.cant_consignacion =
                parseInt(
                    tr.querySelector(".poc-input-cant-consignacion")?.value,
                ) || 0;
            const selectClase = tr.querySelector(".poc-select-clase");
            if (selectClase) {
                rowState.id_clase = selectClase.value;
                rowState.descripcion =
                    selectClase.options[selectClase.selectedIndex]?.text || "";
            }
            // Material: leer el select actual
            const matSel = tr.querySelector(".poc-input-material");
            if (matSel && matSel.value && matSel.value !== "Otro") {
                rowState.material = matSel.value;
            } else if (!rowState.material) {
                rowState.material = "Hierro Gris";
            }
            rowState.codigo =
                tr.querySelector(".poc-input-codigo")?.value || "";
            rowState.peso_juego =
                parseFloat(tr.querySelector(".poc-input-peso-juego")?.value) ||
                0;
            rowState.peso_total =
                parseFloat(tr.querySelector(".poc-input-peso-total")?.value) ||
                0;
            rowState.fecha_entrega =
                tr.querySelector(".poc-input-fecha-entrega")?.value ||
                pData.fecha_entrega ||
                "";
        });
    }
}
document
    .getElementById("formPreOrdenCasting")
    ?.addEventListener("submit", async function (e) {
        e.preventDefault();
        const hasPage2 = document.getElementById("poc-has-page2").value === "1";
        savePocPageData(1);
        if (hasPage2) {
            savePocPageData(2);
        }
        const p1 = pocState.page1;
        if (!p1.proveedor || !p1.fecha) {
            almacenToast(
                "Debe completar el Proveedor y Fecha de la Página 1.",
                "error",
            );
            return;
        }

        if (p1.filas.length === 0) {
            almacenToast(
                "Página 1 debe tener al menos una fila de clase.",
                "error",
            );
            return;
        }
        let p1Valid = true;
        p1.filas.forEach((f) => {
            if (
                !f.id_clase ||
                !f.tipo_modelo ||
                !f.material ||
                !f.codigo ||
                f.cant_fabricar === "" ||
                f.cant_fabricar === null ||
                f.cant_fabricar === undefined ||
                (!f.cant_consignacion && f.cant_consignacion !== 0) ||
                (!f.peso_juego && f.peso_juego !== 0) ||
                (!f.peso_total && f.peso_total !== 0)
            ) {
                p1Valid = false;
            }
        });
        if (!p1Valid) {
            almacenToast(
                "Por favor complete todos los campos obligatorios de las clases en Página 1.",
                "error",
            );
            return;
        }
        const p2 = pocState.page2;
        if (hasPage2) {
            if (!p2.proveedor || !p2.fecha) {
                almacenToast(
                    "Debe completar el Proveedor y Fecha de la Página 2.",
                    "error",
                );
                return;
            }

            if (p2.filas.length === 0) {
                almacenToast(
                    "Página 2 debe tener al menos una fila de clase.",
                    "error",
                );
                return;
            }
            let p2Valid = true;
            p2.filas.forEach((f) => {
                if (
                    !f.id_clase ||
                    !f.tipo_modelo ||
                    !f.material ||
                    !f.codigo ||
                    f.cant_fabricar === "" ||
                    f.cant_fabricar === null ||
                    f.cant_fabricar === undefined ||
                    (!f.cant_consignacion && f.cant_consignacion !== 0) ||
                    (!f.peso_juego && f.peso_juego !== 0) ||
                    (!f.peso_total && f.peso_total !== 0)
                ) {
                    p2Valid = false;
                }
            });
            if (!p2Valid) {
                almacenToast(
                    "Por favor complete todos los campos obligatorios de las clases en Página 2.",
                    "error",
                );
                return;
            }
        }
        let otNumClean = pocState.ot_raw;
        if (otNumClean.includes(" - ")) {
            otNumClean = otNumClean.split(" - ")[0].trim();
        }
        otNumClean = otNumClean.split("_")[0].replace(/[^0-9]/g, "");
        const payload = {
            type: "casting",
            has_page2: hasPage2,
            page1: {
                ot: otNumClean,
                ot_raw: pocState.ot_raw,
                proveedor: p1.proveedor,
                fecha_creacion: p1.fecha,
                fecha_entrega: p1.fecha_entrega,
                folio: p1.folio,
                moldura: pocState.moldura,
                observaciones: p1.observaciones,
                filas: p1.filas.map((f) => ({
                    id_clase: f.id_clase,
                    clase: f.descripcion,
                    tipo_modelo: f.tipo_modelo,
                    impresiones: f.impresiones,
                    cant_fabricar: f.cant_fabricar,
                    cant_consignacion: f.cant_consignacion,
                    material: f.material,
                    codigo: f.codigo,
                    peso_juego: f.peso_juego,
                    peso_total: f.peso_total,
                    fecha_entrega: f.fecha_entrega,
                })),
            },
        };
        if (hasPage2) {
            payload.page2 = {
                ot: otNumClean,
                ot_raw: pocState.ot_raw,
                proveedor: p2.proveedor,
                fecha_creacion: p2.fecha,
                fecha_entrega: p2.fecha_entrega,
                folio: p2.folio,
                moldura: pocState.moldura,
                observaciones: p2.observaciones,
                filas: p2.filas.map((f) => ({
                    id_clase: f.id_clase,
                    clase: f.descripcion,
                    tipo_modelo: f.tipo_modelo,
                    impresiones: f.impresiones,
                    cant_fabricar: f.cant_fabricar,
                    cant_consignacion: f.cant_consignacion,
                    material: f.material,
                    codigo: f.codigo,
                    peso_juego: f.peso_juego,
                    peso_total: f.peso_total,
                    fecha_entrega: f.fecha_entrega,
                })),
            };
        }
        const btn = document.getElementById("btn-submit-poc") || document.querySelector(".btn-save-preorden");
        const origText = btn ? btn.innerHTML : "";
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin" style="margin-right: 8px;"></i> Guardando y Generando PDF...';
        }
        try {
            let fetchUrl = "/almacen/fundicion/store-preorden";
            if (window.baseUrl) {
                fetchUrl =
                    window.baseUrl.replace(/\/+$/, "") +
                    "/almacen/fundicion/store-preorden";
            }
            const resp = await fetch(fetchUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN":
                        document.querySelector('meta[name="csrf-token"]')
                            ?.content ?? "",
                },
                body: JSON.stringify(payload),
            });
            const res = await resp.json();
            if (res.success) {
                almacenToast(res.message, "success");
                cerrarModalPreOrdenCasting();
                if (res.pdfs && res.pdfs.length > 0) {
                    res.pdfs.forEach((pdf) => {
                        const link = document.createElement("a");
                        link.href = pdf.url;
                        link.download = pdf.filename;
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    });
                }
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                almacenToast(
                    res.message || "Error al guardar la pre-orden.",
                    "error",
                );
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = origText;
                }
            }
        } catch (err) {
            console.error("Error storing casting pre-order:", err);
            almacenToast("Error de red al guardar la pre-orden.", "error");
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = origText;
            }
        }
    });
let madcSelectedFiles = [];
window.removeMadcSelectedAttachment = function (index) {
    madcSelectedFiles.splice(index, 1);
    renderMadcSelectedFilesBadges();
};
function renderMadcSelectedFilesBadges() {
    const listContainer = document.getElementById(
        "madc-archivos-adicionales-list",
    );
    if (!listContainer) return;
    listContainer.innerHTML = "";
    madcSelectedFiles.forEach((file, index) => {
        const badge = document.createElement("span");
        badge.className = "file-badge";
        badge.classList.remove("cal-display-none");
        badge.style.alignItems = "center";
        badge.style.gap = "6px";
        badge.style.background = "#ffedd5";
        badge.style.color = "#ea580c";
        badge.style.border = "1px solid #fed7aa";
        badge.style.borderRadius = "20px";
        badge.style.padding = "6px 12px";
        badge.style.fontSize = "0.85em";
        badge.style.fontWeight = "600";
        badge.innerHTML = `
📄 ${file.name} (${(file.size / 1024).toFixed(1)} KB)
<button type="button" class="remove-file-badge-btn" style="background: none; border: none; color: #ea580c; font-weight: bold; cursor: pointer; padding: 0 4px; font-size: 1.2em; line-height: 1; display: flex; align-items: center;" onclick="window.removeMadcSelectedAttachment(${index})">&times;</button>
`;
        listContainer.appendChild(badge);
    });
}
const handleMadcFileChange = function (e) {
    if (this.files) {
        for (let i = 0; i < this.files.length; i++) {
            madcSelectedFiles.push(this.files[i]);
        }
        renderMadcSelectedFilesBadges();
    }
};
const bindMadcDropzone = () => {
    const inp = document.getElementById("madc-archivos-adicionales");
    if (inp) {
        inp.removeEventListener("change", handleMadcFileChange);
        inp.addEventListener("change", handleMadcFileChange);
    }
};
// Bind on initial load
setTimeout(bindMadcDropzone, 500);
/**
 * Calidad: Enviar alerta directo a Almacén sin abrir modal de subida de archivos (Ahora abre un modal confirmando fecha y mostrando PDFs)
 */
window.enviarAlertaDirectoCalidad = async function (
    ot,
    decision,
    tiposAprobados,
    tiposRechazados,
) {
    const modal = document.getElementById("modalEnviarAlertaDirectoCalidad");
    if (!modal) return;
    bindMadcDropzone();
    madcSelectedFiles = [];
    const dropzoneInput = document.getElementById("madc-archivos-adicionales");
    if (dropzoneInput) dropzoneInput.value = "";
    const badgeContainer = document.getElementById(
        "madc-archivos-adicionales-list",
    );
    if (badgeContainer) badgeContainer.innerHTML = "";
    document.getElementById("madc-ot").value = ot;
    document.getElementById("madc-decision").value = decision;
    document.getElementById("madc-tipos-aprobados").value = JSON.stringify(
        tiposAprobados || [],
    );
    document.getElementById("madc-tipos-rechazados").value = JSON.stringify(
        tiposRechazados || [],
    );
    const otClean = ot.replace(/_\d{8}_\d{6}_.*/, "");
    document.getElementById("madc-subtitle").textContent =
        `OT: ${otClean} (${decision.toUpperCase()})`;
    // Ajustar colores y estilos (Igual al modal de Pre-Orden)
    const header = document.getElementById("madc-header");
    const submitBtn = document.getElementById("btn-submit-direct-calidad");
    modal.querySelector(".alm-modal-content").style.borderColor = "";
    if (header) {
        header.style.background = "#033966";
    }
    if (submitBtn) {
        submitBtn.style.background = "#005194";
        submitBtn.style.boxShadow = "0 4px 15px rgba(0, 81, 148, 0.3)";
    }
    // Colocar fecha de hoy
    const today = new Date();
    const formattedToday = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, "0")}-${String(today.getDate()).padStart(2, "0")}`;
    document.getElementById("madc-fecha").value = formattedToday;
    const listContainer = document.getElementById(
        "madc-server-files-container",
    );
    listContainer.innerHTML =
        "<div style=\"text-align:center;color:#64748b;font-family:'Poppins',sans-serif;padding:10px;\">Cargando archivos...</div>";
    modal.classList.add("open");
    document.body.classList.add("modal-open");
    try {
        const response = await fetch(
            `${window.almacenRoutes.archivos}?ot=${encodeURIComponent(ot)}`,
        );
        const data = await response.json();
        let baseUrl = window.baseUrl || window.location.origin + "/";
        if (!baseUrl.endsWith("/")) baseUrl += "/";
        if (data.existe && data.archivos && data.archivos.length > 0) {
            const sectionsHtml = generarHtmlCategorizadoArchivos(
                data.archivos,
                ot,
                baseUrl,
                "calidad",
            );
            listContainer.innerHTML =
                sectionsHtml ||
                "<div style=\"text-align:center;color:#ef4444;font-family:'Poppins',sans-serif;padding:10px;font-weight:600;\">No se encontraron PDFs en el servidor para esta OT.</div>";
        } else {
            listContainer.innerHTML =
                "<div style=\"text-align:center;color:#ef4444;font-family:'Poppins',sans-serif;padding:10px;font-weight:600;\">No se encontraron PDFs en el servidor para esta OT.</div>";
        }
    } catch (err) {
        console.error(err);
        listContainer.innerHTML =
            "<div style=\"text-align:center;color:#ef4444;font-family:'Poppins',sans-serif;padding:10px;\">Error al consultar archivos.</div>";
    }
};
window.cerrarModalEnviarAlertaDirectoCalidad = function () {
    const modal = document.getElementById("modalEnviarAlertaDirectoCalidad");
    if (modal) modal.classList.remove("open");
    document.body.classList.remove("modal-open");
};
document
    .getElementById("formEnviarAlertaDirectoCalidad")
    ?.addEventListener("submit", async function (e) {
        e.preventDefault();
        const ot = document.getElementById("madc-ot").value;
        const decision = document.getElementById("madc-decision").value;
        const tiposAprobados = JSON.parse(
            document.getElementById("madc-tipos-aprobados").value,
        );
        const tiposRechazados = JSON.parse(
            document.getElementById("madc-tipos-rechazados").value,
        );
        const fecha = document.getElementById("madc-fecha").value;
        const destinatario = document
            .getElementById("madc-destinatario")
            .value.trim();
        if (!destinatario) {
            almacenToast("El destinatario es obligatorio.", "error");
            return;
        }
        if (!fecha) {
            almacenToast("La fecha de envío es obligatoria.", "error");
            return;
        }
        const submitBtn = document.getElementById("btn-submit-direct-calidad");
        if (!submitBtn) return;
        const origText = submitBtn.innerText;
        submitBtn.disabled = true;
        submitBtn.innerText = "Enviando...";
        const allTypes = [...tiposAprobados, ...tiposRechazados];
        const formData = new FormData(this);
        formData.set("tipo_modelo", allTypes.join(", "));
        // Adjuntar archivos de dropzone
        madcSelectedFiles.forEach((file) => {
            if (decision === "rechazar") {
                formData.append("archivos_rechazados_extra[]", file);
            } else if (decision === "aprobar") {
                formData.append("archivos_aprobados_extra[]", file);
            } else {
                formData.append("archivos_aprobados_extra[]", file);
                formData.append("archivos_rechazados_extra[]", file);
            }
        });
        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content");
        try {
            const response = await fetch(
                window.almacenRoutes.enviarAlertaLiberacion,
                {
                    method: "POST",
                    body: formData,
                    headers: {
                        "X-CSRF-TOKEN": csrfToken || "",
                    },
                },
            );
            const res = await response.json();
            if (res.success) {
                almacenToast(
                    res.message || "Alerta enviada correctamente.",
                    "success",
                );
                setTimeout(() => {
                    cerrarModalEnviarAlertaDirectoCalidad();
                    location.reload();
                }, 1500);
            } else {
                almacenToast(
                    res.message || "Error al enviar la alerta.",
                    "error",
                );
                submitBtn.disabled = false;
                submitBtn.innerText = origText;
            }
        } catch (error) {
            console.error("Error al enviar alerta directo:", error);
            almacenToast("Error de conexión al enviar la alerta.", "error");
            submitBtn.disabled = false;
            submitBtn.innerText = origText;
        }
    });
/**
 * Almacén: Confirmación de recepción de rechazo
 */
window.abrirModalConfirmarRechazoAlmacen = function (ot) {
    const modal = document.getElementById("modalConfirmarRechazoAlmacen");
    if (!modal) return;
    document.getElementById("cr-ot").value = ot;
    const today = new Date();
    const formattedToday = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, "0")}-${String(today.getDate()).padStart(2, "0")}`;
    document.getElementById("cr-fecha").value = formattedToday;
    modal.classList.add("open");
    document.body.classList.add("modal-open");
};
window.cerrarModalConfirmarRechazoAlmacen = function () {
    const modal = document.getElementById("modalConfirmarRechazoAlmacen");
    if (modal) modal.classList.remove("open");
    document.body.classList.remove("modal-open");
};
document
    .getElementById("formConfirmarRechazoAlmacen")
    ?.addEventListener("submit", async function (e) {
        e.preventDefault();
        const ot = document.getElementById("cr-ot").value;
        const fecha = document.getElementById("cr-fecha").value;
        if (!fecha) {
            almacenToast("La fecha de recepción es obligatoria.", "error");
            return;
        }
        const submitBtn = this.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerText = "Procesando...";
        }
        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content");
        try {
            const response = await fetch(
                window.almacenRoutes.confirmarRecepcionRechazo,
                {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrfToken || "",
                    },
                    body: JSON.stringify({ ot, fecha }),
                },
            );
            const data = await response.json();
            if (data.success) {
                almacenToast(data.message, "success");
                setTimeout(() => {
                    cerrarModalConfirmarRechazoAlmacen();
                    location.reload();
                }, 1500);
            } else {
                almacenToast(
                    data.message || "Error al procesar el rechazo.",
                    "error",
                );
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerText = "Confirmar y Reiniciar";
                }
            }
        } catch (err) {
            console.error(err);
            almacenToast("Error de conexión al procesar el rechazo.", "error");
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerText = "Confirmar y Reiniciar";
            }
        }
    });
// ==========================================
// FLUJO: INICIAR CASTING
// ==========================================
window.updateCustomFileLabel = function (input) {
    const container = input.closest(".custom-file-dropzone");
    if (!container) return;
    const textLabel = container.querySelector(".dropzone-text-label");
    const subtextLabel = container.querySelector(".dropzone-subtext-label");
    const isRechazo = input.dataset.type === "rechazo";
    const isScar = input.dataset.type === "scar";
    if (input.files && input.files[0]) {
        if (textLabel) {
            textLabel.textContent = `${input.files[0].name}`;
            textLabel.style.color = "#10b981";
        }
        if (subtextLabel) {
            subtextLabel.textContent = `Tamaño: ${(input.files[0].size / 1024).toFixed(1)} KB`;
        }
        container.style.borderColor = "#10b981";
        container.style.background = "#f0fdf4";
    } else {
        if (textLabel) {
            if (isRechazo) {
                textLabel.textContent = `Arrastra formato de rechazo o haz clic para buscar`;
                textLabel.style.color = "#dc2626";
            } else if (isScar) {
                textLabel.textContent = `Arrastra SCAR o haz clic para buscar`;
                textLabel.style.color = "#ca8a04";
            } else {
                textLabel.textContent = `Arrastra formato LDM o haz clic para buscar`;
                textLabel.style.color = "#0ea5e9";
            }
        }
        if (subtextLabel) {
            subtextLabel.textContent = `Ningún archivo seleccionado`;
        }
        if (isRechazo) {
            container.style.borderColor = "#fca5a5";
            container.style.background = "#fef2f2";
        } else if (isScar) {
            container.style.borderColor = "#fef08a";
            container.style.background = "#fefce8";
        } else {
            container.style.borderColor = "#0ea5e9";
            container.style.background = "#f0f9ff";
        }
    }
};
// Genera HTML de archivos para el modal de casting — dinámico para aprobados y rechazados
function generarHtmlCategorizadoCastingAprobados(
    archivos,
    otClean,
    isRechazados = false,
) {
    const baseUrl = window.baseUrl || window.location.origin + "/";
    const secciones = [
        {
            label: "Ayudas Visuales",
            color: "#d97706",
            tipos: ["ayuda"],
            claseCard: "card-ayuda",
        },
        {
            label: "Dibujos de Fundición",
            color: "#0284c7",
            tipos: ["dibujo", "plano"],
            claseCard: "card-plano",
        },
        {
            label: isRechazados
                ? "Documentos Rechazados"
                : "Documentos Aprobados",
            color: isRechazados ? "#ef4444" : "#059669",
            tipos: ["aprobado", "preorden", "otro"],
            claseCard: "card-ayuda",
        },
    ];
    let html = "";
    secciones.forEach((sec) => {
        const archivosSeccion = archivos.filter((f) => {
            const tipo = (f.tipo || "").toLowerCase();
            const nombre = (f.nombre || "").toLowerCase();
            if (sec.tipos.includes("aprobado")) {
                // Sección de aprobados/rechazados: incluye preordenes y documentos del veredicto correspondiente
                const targetFolder = isRechazados
                    ? "documentos_rechazados"
                    : "documentos_aprobados";
                return (
                    tipo === "aprobado" ||
                    tipo === "preorden" ||
                    tipo === "otro" ||
                    nombre.includes(targetFolder) ||
                    nombre.includes("preordenes/")
                );
            }
            return sec.tipos.includes(tipo);
        });
        if (archivosSeccion.length === 0) return;
        html += `<div style="width:100%;">
<h4 style="font-family:'Poppins',sans-serif;font-weight:700;color:#1e293b;font-size:1.05em;margin-top:10px;margin-bottom:12px;border-left:4px solid ${sec.color};padding-left:8px;">${sec.label}</h4>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px;">`;
        archivosSeccion.forEach((f) => {
            const nombre = f.nombre || "";
            const baseName = nombre.split("/").pop();
            const ext = baseName.split(".").pop().toLowerCase();
            const isImg = ["png", "jpg", "jpeg", "gif", "webp"].includes(ext);
            const iconDefault = isImg
                ? `${getBaseUrl()}images/galeria-shadow.png`
                : `${getBaseUrl()}images/pdf-view-shadow.png`;
            const iconHover = isImg
                ? `${getBaseUrl()}images/galeria.png`
                : `${getBaseUrl()}images/pdf-view.png`;
            const tipoParam = f.tipo || "otro";
            html += `<div class="dibujos-file-card ${sec.claseCard} select-file-card" style="position:relative;width:100%;max-width:220px;display:inline-flex;flex-direction:column;align-items:center;text-align:center;border-radius:12px;box-shadow:0 4px 6px rgba(0,0,0,0.05);background:#fff;padding:10px;border:1.5px solid #e2e8f0;">
<div class="file-icon-wrapper" onclick="calidadVerPdf('${otClean}','${nombre}','${tipoParam}')" style="cursor:pointer;margin-top:10px;">
<img src="${iconDefault}" class="file-icon icon-default" style="width:48px;height:auto;">
<img src="${iconHover}" class="file-icon icon-hover" style="width:48px;height:auto;">
</div>
<div class="file-name" onclick="calidadVerPdf('${otClean}','${nombre}','${tipoParam}')" style="cursor:pointer;font-size:0.82em;margin:8px 0;max-height:40px;overflow:hidden;text-overflow:ellipsis;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;font-weight:600;color:#334155;line-height:1.3;">${baseName}</div>
<div class="file-actions" style="width:100%;margin-top:auto;">
<button type="button" class="btn-dibujos btn-dibujos-sm btn-ver" style="font-size:0.8em;padding:5px 12px;border-radius:6px;font-family:'Poppins',sans-serif;font-weight:600;width:100%;background:#15803d;color:white;border-color:#15803d;" onclick="calidadVerPdf('${otClean}','${nombre}','${tipoParam}')">Ver</button>
</div>
</div>`;
        });
        html += `</div></div>`;
    });
    return html;
}
window.cargarInputsCasting = function (ot, files) {
    const dynamicInputs = document.getElementById("mgv-aprobados-inputs");
    if (!dynamicInputs) return;
    dynamicInputs.innerHTML = "";
    const otClean = ot.replace(/_\d{8}_\d{6}_.*/, "");
    let allLoaded = true;
    if (window.micRequiredClasses && window.micRequiredClasses.length > 0) {
        window.micRequiredClasses.forEach((c) => {
            const group = document.createElement("div");
            group.className = "custom-class-upload";
            group.style.marginBottom = "12px";
            let existingFile = null;
            if (files) {
                let sanitizedOt = ot.replace(/[^\w\s\-]/g, "");
                sanitizedOt = sanitizedOt
                    .trim()
                    .replace(/[\s]+/g, "_")
                    .toUpperCase();
                existingFile = files.find((f) => {
                    const nameUpper = (f.nombre || "").toUpperCase();
                    return (
                        f.origin === "aprobado" &&
                        nameUpper.includes("DOCUMENTOS_APROBADOS/FDLDM/") &&
                        nameUpper.includes(
                            "F-CCL-LDM_" +
                                c.toUpperCase() +
                                "_" +
                                sanitizedOt +
                                "_APROBADO",
                        )
                    );
                });
            }
            const label = c.charAt(0).toUpperCase() + c.slice(1);
            if (existingFile) {
                const cleanName = existingFile.nombre.split("/").pop();
                group.innerHTML = `
<label style="font-weight:700;color:#15803d;margin-bottom:6px;display:block;font-family:'Poppins',sans-serif;font-size:0.95em;">Formato LDM — ${label} <span style="background:#dcfce7;color:#15803d;border-radius:20px;padding:2px 8px;font-size:0.82em;margin-left:4px;">Cargado</span></label>
<div style="background:#f0fdf4;border:2px solid #86efac;border-radius:10px;padding:10px 15px;display:flex;align-items:center;justify-content:space-between;gap:15px;">
<div style="display:flex;align-items:center;gap:10px;overflow:hidden;width:100%;">
<img src="${getBaseUrl()}images/pdf.png" style="width:24px;height:24px;object-fit:contain;flex-shrink:0;">
<span style="font-weight:600;color:#15803d;font-size:0.9em;font-family:'Poppins',sans-serif;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="${cleanName}">${cleanName}</span>
</div>
<div style="display:flex;gap:8px;flex-shrink:0;">
<button type="button" class="btn-dibujos btn-dibujos-sm btn-ver" style="font-size:0.8em;padding:6px 12px;border-radius:6px;font-family:'Poppins',sans-serif;font-weight:600;background:#15803d;border-color:#15803d;color:white;" onclick="calidadVerPdf('${ot}','${existingFile.nombre}','aprobado')">Ver</button>
<button type="button" class="btn-dibujos btn-dibujos-sm" style="font-size:0.8em;padding:6px 12px;border-radius:6px;font-family:'Poppins',sans-serif;font-weight:600;background:#ef4444;border-color:#ef4444;color:white;" onclick="quitarArchivoAprobado('${ot}','${existingFile.nombre}',this)">Quitar</button>
</div>
</div>`;
            } else {
                allLoaded = false;
                group.innerHTML = `
<label style="font-weight:700;color:#334155;margin-bottom:6px;display:block;font-family:'Poppins',sans-serif;font-size:0.95em;">Formato F-CCL-LDM — ${label} <span style="color:#ef4444;">*</span></label>
<div class="custom-file-dropzone" style="border:2px dashed #16a34a;background:#f0fdf4;min-height:64px;position:relative;border-radius:10px;display:flex;align-items:center;padding:10px 15px;cursor:pointer;transition:all 0.2s;">
<input type="file" name="ldm_${c}" accept=".pdf" required style="position:absolute;top:0;left:0;width:100%;height:100%;opacity:0;cursor:pointer;" onchange="updateCustomFileLabel(this)">
<div style="display:flex;align-items:center;gap:10px;width:100%;">
<div class="file-icon-wrapper" style="position:relative;width:38px;height:38px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
<img src="${getBaseUrl()}images/pdf-view-shadow.png" class="file-icon icon-default" style="width:38px;height:38px;object-fit:contain;">
<img src="${getBaseUrl()}images/pdf-view.png" class="file-icon icon-hover" style="width:38px;height:38px;object-fit:contain;position:absolute;top:0;left:0;opacity:0;">
</div>
<div style="overflow:hidden;">
<span class="dropzone-text-label" style="font-weight:700;color:#16a34a;font-size:0.9em;font-family:'Poppins',sans-serif;display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">Arrastra formato F-CCL-LDM firmado o haz clic</span>
<span class="dropzone-subtext-label" style="font-size:0.75em;color:#64748b;display:block;font-family:'Poppins',sans-serif;">Ningún archivo seleccionado — solo PDF</span>
</div>
</div>
</div>`;
            }
            dynamicInputs.appendChild(group);
        });
    } else {
        dynamicInputs.innerHTML =
            "<p style=\"color:#10b981;font-weight:600;font-family:'Poppins',sans-serif;padding:12px;background:#f0fdf4;border-radius:8px;\">No se requieren formatos LDM adicionales.</p>";
    }
    const btnSubmit = document.getElementById("btn-submit-aprobados");
    const btnIr = document.getElementById("btn-ir-preorden-casting");
    if (btnSubmit) {
        if (allLoaded) {
            btnSubmit.classList.add("cal-display-none");
        } else {
            btnSubmit.classList.remove("cal-display-none");
            btnSubmit.disabled = false;
            const textSpan = btnSubmit.querySelector("span");
            if (textSpan) {
                textSpan.innerText = "Procesar Aceptados";
            }
        }
    }
    if (btnIr) {
        btnIr.classList.toggle("cal-display-none", !allLoaded);
    }
};
window.quitarArchivoAprobado = function (ot, archivo, buttonEl) {
    if (
        !confirm(
            "¿Estás seguro de que deseas eliminar permanentemente este formato LDM? Esta acción no se puede deshacer.",
        )
    ) {
        return;
    }
    if (buttonEl) buttonEl.disabled = true;
    fetch(window.almacenRoutes.deleteFile, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content"),
            Accept: "application/json",
        },
        body: JSON.stringify({
            ot: ot,
            archivo: archivo,
            tipo: "aprobado",
        }),
    })
        .then((res) => res.json())
        .then((data) => {
            if (data.success) {
                almacenToast(
                    data.message || "Formato LDM eliminado correctamente.",
                    "success",
                );
                // Refrescar los archivos del modal
                fetch(
                    `${window.almacenRoutes.archivos}?ot=${encodeURIComponent(ot)}`,
                )
                    .then((res) => res.json())
                    .then((data) => {
                        cargarInputsCasting(ot, data.archivos);
                        const filesContainer = document.getElementById(
                            "mgv-aprobados-files",
                        );
                        if (filesContainer) {
                            const otClean = ot.replace(/_\d{8}_\d{6}_.*/, "");
                            let baseUrl =
                                window.baseUrl || window.location.origin + "/";
                            if (!baseUrl.endsWith("/")) baseUrl += "/";
                            const sectionsHtml =
                                generarHtmlCategorizadoArchivos(
                                    data.archivos,
                                    otClean,
                                    baseUrl,
                                    "preorden",
                                );
                            filesContainer.innerHTML =
                                sectionsHtml ||
                                `
<div style="text-align: center; color: #64748b; padding: 15px; font-style: italic;">
No se encontraron archivos en el servidor para esta OT.
</div>
`;
                        }
                        // Toggle download button visibility
                        const downloadBtn = document.getElementById(
                            "btn-download-casting-po",
                        );
                        if (downloadBtn) {
                            if (data.casting_pdf_generated) {
                                downloadBtn.classList.remove(
                                    "cal-display-none",
                                );
                            } else {
                                downloadBtn.classList.add("cal-display-none");
                            }
                        }
                    });
            } else {
                almacenToast(
                    data.error || "Error al eliminar archivo.",
                    "error",
                );
                if (buttonEl) buttonEl.disabled = false;
            }
        })
        .catch((err) => {
            console.error(err);
            almacenToast("Error de red al eliminar archivo.", "error");
            if (buttonEl) buttonEl.disabled = false;
        });
};
window.cargarInputsRechazados = function (ot, files, clasesRechazadas) {
    const dynamicRechInputs = document.getElementById("mgv-rechazados-inputs");
    if (!dynamicRechInputs) return;
    dynamicRechInputs.innerHTML = "";
    const otClean = ot.replace(/_\d{8}_\d{6}_.*/, "");
    let allLoaded = true;
    if (clasesRechazadas && clasesRechazadas.length > 0) {
        clasesRechazadas.forEach((c) => {
            const group = document.createElement("div");
            group.style.marginBottom = "25px";
            group.style.padding = "15px";
            group.style.background = "#fef2f2";
            group.style.border = "1px solid #fca5a5";
            group.style.borderRadius = "8px";
            // Buscar archivos cargados previamente que correspondan exactamente a esta clase y tipo (Rechazo o SCAR)
            let existingRechazo = null;
            let existingScar = null;
            if (files && files.length > 0) {
                let sanitizedOt = ot.replace(/[^\w\s\-]/g, "");
                sanitizedOt = sanitizedOt
                    .trim()
                    .replace(/[\s]+/g, "_")
                    .toLowerCase();
                existingRechazo = files.find((f) => {
                    const nameLower = (f.nombre || "").toLowerCase();
                    const filename = nameLower.split("/").pop();
                    return (
                        nameLower.includes("documentos_rechazados/fdrdm/") &&
                        filename.startsWith(
                            "rechazo_" +
                                c.toLowerCase() +
                                "_" +
                                sanitizedOt +
                                ".",
                        )
                    );
                });
                existingScar = files.find((f) => {
                    const nameLower = (f.nombre || "").toLowerCase();
                    const filename = nameLower.split("/").pop();
                    return (
                        nameLower.includes("documentos_rechazados/scar/") &&
                        filename.startsWith(
                            "scar_" + c.toLowerCase() + "_" + sanitizedOt + ".",
                        )
                    );
                });
            }
            const label = c.charAt(0).toUpperCase() + c.slice(1);
            group.innerHTML = `<h4 style="margin-top:0; margin-bottom: 15px; color: #dc2626; font-weight: 700; font-family:'Poppins', sans-serif;">Clase: ${label}</h4>`;
            // Rechazo
            if (existingRechazo) {
                const cleanName = existingRechazo.nombre.split("/").pop();
                group.innerHTML += `
<div class="form-group" style="margin-bottom: 15px;">
<label style="font-weight: 600; color: #334155; margin-bottom: 6px; display: block; font-family: 'Poppins', sans-serif; font-size: 0.95em;">Formato de Rechazo <span style="background:#dcfce7;color:#15803d;border-radius:20px;padding:2px 8px;font-size:0.82em;margin-left:4px;">Cargado</span></label>
<div style="background:#f0fdf4;border:2px solid #86efac;border-radius:10px;padding:10px 15px;display:flex;align-items:center;justify-content:space-between;gap:15px;">
<div style="display:flex;align-items:center;gap:10px;overflow:hidden;width:100%;">
<img src="${getBaseUrl()}images/pdf.png" style="width:24px;height:24px;object-fit:contain;flex-shrink:0;">
<span style="font-weight:600;color:#15803d;font-size:0.9em;font-family:'Poppins',sans-serif;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="${cleanName}">${cleanName}</span>
</div>
<div style="display:flex;gap:8px;flex-shrink:0;">
<button type="button" class="btn-dibujos btn-dibujos-sm btn-ver" style="font-size:0.8em;padding:6px 12px;border-radius:6px;font-family:'Poppins',sans-serif;font-weight:600;background:#15803d;border-color:#15803d;color:white;" onclick="calidadVerPdf('${ot}','${existingRechazo.nombre}','otro')">Ver</button>
<button type="button" class="btn-dibujos btn-dibujos-sm" style="font-size:0.8em;padding:6px 12px;border-radius:6px;font-family:'Poppins',sans-serif;font-weight:600;background:#ef4444;border-color:#ef4444;color:white;" onclick="quitarArchivoRechazo('${ot}','${existingRechazo.nombre}',this)">Quitar</button>
</div>
</div>
</div>`;
            } else {
                allLoaded = false;
                group.innerHTML += `
<div class="form-group" style="margin-bottom: 15px;">
<label style="font-weight: 600; color: #334155; margin-bottom: 6px; display: block; font-family: 'Poppins', sans-serif; font-size: 0.95em;">Formato de Rechazo <span style="color:#ef4444;">*</span></label>
<div class="custom-file-dropzone" style="border: 2px dashed #fca5a5; background: #fef2f2; min-height: 64px; position: relative; border-radius: 10px; display: flex; align-items: center; padding: 10px 15px; cursor: pointer; transition: all 0.2s;">
<input type="file" name="rechazo_${c.toLowerCase()}" data-type="rechazo" accept=".pdf" required style="position: absolute; top:0; left:0; width:100%; height:100%; opacity:0; cursor:pointer;" onchange="updateCustomFileLabel(this)">
<div style="display: flex; align-items: center; gap: 10px; width: 100%;">
<div class="file-icon-wrapper" style="position:relative;width:38px;height:38px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
<img src="${getBaseUrl()}images/pdf-view-shadow.png" class="file-icon icon-default" style="width:38px;height:38px;object-fit:contain;">
<img src="${getBaseUrl()}images/pdf-view.png" class="file-icon icon-hover" style="width:38px;height:38px;object-fit:contain;position:absolute;top:0;left:0;opacity:0;">
</div>
<div style="overflow:hidden;">
<span class="dropzone-text-label" style="font-weight: 700; color: #dc2626; font-size: 0.9em; font-family:'Poppins', sans-serif; display: block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">Arrastra formato de rechazo o haz clic para buscar</span>
<span class="dropzone-subtext-label" style="font-size: 0.75em; color: #64748b; display: block; font-family:'Poppins', sans-serif;">Ningún archivo seleccionado</span>
</div>
</div>
</div>
</div>`;
            }
            // SCAR
            if (existingScar) {
                const cleanName = existingScar.nombre.split("/").pop();
                group.innerHTML += `
<div class="form-group" style="margin-bottom: 0;">
<label style="font-weight: 600; color: #334155; margin-bottom: 6px; display: block; font-family: 'Poppins', sans-serif; font-size: 0.95em;">SCAR <span style="background:#dcfce7;color:#15803d;border-radius:20px;padding:2px 8px;font-size:0.82em;margin-left:4px;">Cargado</span></label>
<div style="background:#f0fdf4;border:2px solid #86efac;border-radius:10px;padding:10px 15px;display:flex;align-items:center;justify-content:space-between;gap:15px;">
<div style="display:flex;align-items:center;gap:10px;overflow:hidden;width:100%;">
<img src="${getBaseUrl()}images/pdf.png" style="width:24px;height:24px;object-fit:contain;flex-shrink:0;">
<span style="font-weight:600;color:#15803d;font-size:0.9em;font-family:'Poppins',sans-serif;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="${cleanName}">${cleanName}</span>
</div>
<div style="display:flex;gap:8px;flex-shrink:0;">
<button type="button" class="btn-dibujos btn-dibujos-sm btn-ver" style="font-size:0.8em;padding:6px 12px;border-radius:6px;font-family:'Poppins',sans-serif;font-weight:600;background:#15803d;border-color:#15803d;color:white;" onclick="calidadVerPdf('${ot}','${existingScar.nombre}','otro')">Ver</button>
<button type="button" class="btn-dibujos btn-dibujos-sm" style="font-size:0.8em;padding:6px 12px;border-radius:6px;font-family:'Poppins',sans-serif;font-weight:600;background:#ef4444;border-color:#ef4444;color:white;" onclick="quitarArchivoRechazo('${ot}','${existingScar.nombre}',this)">Quitar</button>
</div>
</div>
</div>`;
            } else {
                allLoaded = false;
                group.innerHTML += `
<div class="form-group" style="margin-bottom: 0;">
<label style="font-weight: 600; color: #334155; margin-bottom: 6px; display: block; font-family: 'Poppins', sans-serif; font-size: 0.95em;">SCAR <span style="color:#ef4444;">*</span></label>
<div class="custom-file-dropzone" style="border: 2px dashed #fef08a; background: #fefce8; min-height: 64px; position: relative; border-radius: 10px; display: flex; align-items: center; padding: 10px 15px; cursor: pointer; transition: all 0.2s;">
<input type="file" name="scar_${c.toLowerCase()}" data-type="scar" accept=".pdf" required style="position: absolute; top:0; left:0; width:100%; height:100%; opacity:0; cursor:pointer;" onchange="updateCustomFileLabel(this)">
<div style="display: flex; align-items: center; gap: 10px; width: 100%;">
<div class="file-icon-wrapper" style="position:relative;width:38px;height:38px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
<img src="${getBaseUrl()}images/pdf-view-shadow.png" class="file-icon icon-default" style="width:38px;height:38px;object-fit:contain;">
<img src="${getBaseUrl()}images/pdf-view.png" class="file-icon icon-hover" style="width:38px;height:38px;object-fit:contain;position:absolute;top:0;left:0;opacity:0;">
</div>
<div style="overflow:hidden;">
<span class="dropzone-text-label" style="font-weight: 700; color: #ca8a04; font-size: 0.9em; font-family:'Poppins', sans-serif; display: block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">Arrastra SCAR o haz clic para buscar</span>
<span class="dropzone-subtext-label" style="font-size: 0.75em; color: #64748b; display: block; font-family:'Poppins', sans-serif;">Ningún archivo seleccionado</span>
</div>
</div>
</div>
</div>`;
            }
            dynamicRechInputs.appendChild(group);
        });
    }
    const btnSubmit = document.getElementById("btn-submit-rechazados");
    if (btnSubmit) {
        const textSpan = btnSubmit.querySelector("span");
        const imgIcon = btnSubmit.querySelector("img");
        if (imgIcon) {
            imgIcon.remove();
        }
        if (allLoaded) {
            btnSubmit.style.background =
                "linear-gradient(135deg, #dc2626, #b91c1c)";
            btnSubmit.style.boxShadow = "0 4px 15px rgba(3,105,161,0.3)";
            if (textSpan)
                textSpan.innerText =
                    "Generar Pre-Orden de Fabricación de Modelo";
        } else {
            btnSubmit.style.background =
                "linear-gradient(135deg, #dc2626, #b91c1c)";
            btnSubmit.style.boxShadow = "0 4px 15px rgba(220,38,38,0.35)";
            if (textSpan) textSpan.innerText = "Subir Formatos y Continuar";
        }
    }
};
window.quitarArchivoRechazo = function (ot, archivo, buttonEl) {
    if (
        !confirm(
            "¿Estás seguro de que deseas eliminar permanentemente este formato? Esta acción no se puede deshacer.",
        )
    ) {
        return;
    }
    if (buttonEl) buttonEl.disabled = true;
    fetch(window.almacenRoutes.deleteFile, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content"),
            Accept: "application/json",
        },
        body: JSON.stringify({
            ot: ot,
            archivo: archivo,
            tipo: "otro",
        }),
    })
        .then((res) => res.json())
        .then((data) => {
            if (data.success) {
                almacenToast(
                    data.message || "Archivo eliminado correctamente.",
                    "success",
                );
                // Refrescar
                fetch(
                    `${window.almacenRoutes.archivos}?ot=${encodeURIComponent(ot)}`,
                )
                    .then((res) => res.json())
                    .then((data) => {
                        const hiddenRech = document.getElementById(
                            "mgv-clases-rechazadas",
                        );
                        const clasesRechazadas = hiddenRech
                            ? JSON.parse(hiddenRech.value)
                            : [];
                        window.cargarInputsRechazados(
                            ot,
                            data.archivos,
                            clasesRechazadas,
                        );
                        const filesContainer = document.getElementById(
                            "mgv-rechazados-files",
                        );
                        if (filesContainer) {
                            const otClean = ot.replace(/_\\d{8}_\\d{6}_.*/, "");
                            // Filtrar base para rechazados
                            const baseRech = (data.archivos || []).filter(
                                (f) => {
                                    const nombre = (
                                        f.nombre || ""
                                    ).toLowerCase();
                                    if (
                                        nombre.includes("aprobado") ||
                                        (nombre.includes("pre-orden") &&
                                            nombre.includes("fundicion") &&
                                            !nombre.includes("modelo"))
                                    )
                                        return false;
                                    return true;
                                },
                            );
                            const clasesMonitoreadas = [
                                "candado obturador",
                                "cabeza de soplo",
                                "obturador",
                                "bombillo",
                                "embudo",
                                "corona",
                                "plato",
                                "molde",
                                "fondo",
                            ];
                            const filtrados = baseRech.filter((f) => {
                                const nombre = (f.nombre || "").toLowerCase();
                                const perteneceAClase = clasesMonitoreadas.some(
                                    (c) => nombre.includes(c),
                                );
                                if (perteneceAClase) {
                                    return clasesRechazadas.some((c) =>
                                        nombre.includes(c.toLowerCase()),
                                    );
                                }
                                return false;
                            });
                            const sectionsHtml =
                                window.generarHtmlCategorizadoCastingAprobados
                                    ? window.generarHtmlCategorizadoCastingAprobados(
                                          filtrados,
                                          otClean,
                                          true,
                                      )
                                    : "";
                            filesContainer.innerHTML =
                                sectionsHtml ||
                                `
<div style="text-align: center; color: #64748b; padding: 15px; font-style: italic;">
No se encontraron archivos en el servidor para esta OT.
</div>
`;
                        }
                    });
            } else {
                almacenToast(
                    data.error || "Error al eliminar archivo.",
                    "error",
                );
                if (buttonEl) buttonEl.disabled = false;
            }
        })
        .catch((err) => {
            console.error(err);
            almacenToast("Error de red al eliminar archivo.", "error");
            if (buttonEl) buttonEl.disabled = false;
        });
};
window.switchMgvTab = function (tabName) {
    document
        .querySelectorAll(".mgv-view")
        .forEach((v) => v.classList.add("cal-display-none"));
    document
        .querySelectorAll(".mgv-tab")
        .forEach((t) => t.classList.remove("active"));
    document
        .getElementById("mgv-view-" + tabName)
        .classList.remove("cal-display-none");
    const activeTab = document.getElementById("tab-" + tabName);
    if (activeTab) activeTab.classList.add("active");
    const header = document.getElementById("mgv-header");
    if (header) {
        if (tabName === "aprobados") {
            header.style.background =
                "linear-gradient(135deg, #16a34a, #15803d)";
        } else {
            header.style.background =
                "linear-gradient(135deg, #dc2626, #b91c1c)";
        }
    }
};
window.abrirModalGestionVeredicto = function (ot, aprobados, rechazados) {
    const modal = document.getElementById("modalGestionVeredicto");
    if (!modal) return;
    document.getElementById("mgv-ot").value = ot;
    document.querySelectorAll(".mgv-form-ot").forEach((i) => (i.value = ot));
    const otClean = ot.replace(/_\d{8}_\d{6}_.*/, "");
    document.getElementById("mgv-subtitle").textContent = `OT: ${otClean}`;
    const today = new Date();
    const formattedToday = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, "0")}-${String(today.getDate()).padStart(2, "0")}`;
    document.getElementById("mgv-fecha").value = formattedToday;
    document
        .querySelectorAll(".mgv-form-fecha")
        .forEach((i) => (i.value = formattedToday));
    const requiredClasses = [
        "candado obturador",
        "cabeza de soplo",
        "obturador",
        "bombillo",
        "embudo",
        "corona",
        "plato",
        "molde",
        "fondo",
    ];
    const filteredAprobados = (aprobados || []).filter((c) =>
        requiredClasses.includes(c.toLowerCase()),
    );
    const filteredRechazados = (rechazados || []).filter((c) =>
        requiredClasses.includes(c.toLowerCase()),
    );
    const hiddenClasesRech = document.getElementById("mgv-clases-rechazadas");
    if (hiddenClasesRech) {
        hiddenClasesRech.value = JSON.stringify(filteredRechazados);
    }
    window.micRequiredClasses = filteredAprobados.map((c) => c.toLowerCase());
    cargarInputsCasting(ot, []); // Llenará mgv-aprobados-inputs
    const dynamicRechInputs = document.getElementById("mgv-rechazados-inputs");
    if (dynamicRechInputs) {
        dynamicRechInputs.innerHTML =
            '<div style="text-align:center;padding:15px;color:#64748b;">Cargando formatos requeridos...</div>';
    }
    const hasAprobados = filteredAprobados.length > 0;
    const hasRechazados = filteredRechazados.length > 0;
    const tabAprobados = document.getElementById("tab-aprobados");
    const tabRechazados = document.getElementById("tab-rechazados");
    if (tabAprobados)
        tabAprobados.classList.toggle("cal-display-none", !hasAprobados);
    if (tabRechazados)
        tabRechazados.classList.toggle("cal-display-none", !hasRechazados);
    const filesContainerA = document.getElementById("mgv-aprobados-files");
    const filesContainerR = document.getElementById("mgv-rechazados-files");
    if (filesContainerA)
        filesContainerA.innerHTML = `<div style="text-align:center;padding:14px;"><div class="alm-spinner" style="border-top-color:#16a34a;display:inline-block;"></div><span style="color:#64748b;margin-left:10px;font-family:'Poppins',sans-serif;">Cargando archivos...</span></div>`;
    if (filesContainerR)
        filesContainerR.innerHTML = `<div style="text-align:center;padding:14px;"><div class="alm-spinner" style="border-top-color:#dc2626;display:inline-block;"></div><span style="color:#64748b;margin-left:10px;font-family:'Poppins',sans-serif;">Cargando archivos...</span></div>`;
    modal.classList.add("open");
    document.body.classList.add("modal-open");
    // Inicialmente saltar a la pestaña adecuada
    if (hasAprobados) switchMgvTab("aprobados");
    else if (hasRechazados) switchMgvTab("rechazados");
    fetch(`${window.almacenRoutes.archivos}?ot=${encodeURIComponent(ot)}`)
        .then((res) => res.json())
        .then((data) => {
            cargarInputsCasting(ot, data.archivos);
            window.cargarInputsRechazados(
                ot,
                data.archivos,
                filteredRechazados,
            );
            const btnIr = document.getElementById("btn-ir-preorden-casting");
            if (btnIr) {
                const hasPreordenCasting = (data.archivos || []).some((f) => {
                    const n = (f.nombre || "").toLowerCase();
                    return (
                        n.includes("pre-orden") &&
                        n.includes("fundicion") &&
                        !n.includes("modelo")
                    );
                });
                // Si la preorden ya se generó y hay rechazados pendientes, bloquear casting
                if (hasPreordenCasting && hasRechazados) {
                    if (tabAprobados)
                        tabAprobados.classList.add("cal-display-none");
                    switchMgvTab("rechazados");
                }
            }
            if (data.existe && data.archivos && data.archivos.length > 0) {
                // Función helper para filtrar archivos por clases activas en la pestaña
                const filtrarPorClasesActivas = (
                    archivosList,
                    clasesActivas,
                    esAprobados,
                ) => {
                    const clasesMonitoreadas = [
                        "candado obturador",
                        "cabeza de soplo",
                        "obturador",
                        "bombillo",
                        "embudo",
                        "corona",
                        "plato",
                        "molde",
                        "fondo",
                    ];
                    return archivosList.filter((f) => {
                        const nombre = (f.nombre || "").toLowerCase();
                        const perteneceAClase = clasesMonitoreadas.some((c) =>
                            nombre.includes(c),
                        );
                        if (perteneceAClase) {
                            return clasesActivas.some((c) =>
                                nombre.includes(c.toLowerCase()),
                            );
                        }
                        return true; // Los archivos genéricos (sin clase) se muestran en ambas pestañas
                    });
                };
                // Filtro base para aprobados (excluyendo rechazados de calidad y SCAR)
                const baseAprob = (data.archivos || []).filter((f) => {
                    const nombre = (f.nombre || "").toLowerCase();
                    const tipo = (f.tipo || "").toLowerCase();
                    if (
                        nombre.includes("rechazado") ||
                        nombre.includes("scar") ||
                        nombre.includes("documentos_rechazados")
                    )
                        return false;
                    return (
                        tipo === "ayuda" ||
                        tipo === "dibujo" ||
                        tipo === "aprobado" ||
                        tipo === "preorden" ||
                        nombre.includes("ayudas_visuales") ||
                        nombre.includes("dibujo") ||
                        nombre.includes("documentos_aprobados") ||
                        nombre.includes("preordenes/")
                    );
                });
                const archivosAprob = filtrarPorClasesActivas(
                    baseAprob,
                    filteredAprobados,
                    true,
                );
                if (filesContainerA) {
                    filesContainerA.innerHTML =
                        generarHtmlCategorizadoCastingAprobados(
                            archivosAprob,
                            otClean,
                            false,
                        ) ||
                        `<div style="text-align:center;color:#64748b;padding:15px;font-style:italic;">No hay archivos disponibles para esta clase.</div>`;
                }
                // Filtro base para rechazados (excluyendo aprobados y preordenes de casting)
                const baseRech = (data.archivos || []).filter((f) => {
                    const nombre = (f.nombre || "").toLowerCase();
                    if (
                        nombre.includes("aprobado") ||
                        (nombre.includes("pre-orden") &&
                            nombre.includes("fundicion") &&
                            !nombre.includes("modelo"))
                    )
                        return false;
                    return true;
                });
                const archivosRech = filtrarPorClasesActivas(
                    baseRech,
                    filteredRechazados,
                    false,
                );
                if (filesContainerR) {
                    filesContainerR.innerHTML =
                        generarHtmlCategorizadoCastingAprobados(
                            archivosRech,
                            otClean,
                            true,
                        ) ||
                        `<div style="text-align:center;color:#64748b;padding:15px;font-style:italic;">No hay archivos disponibles para esta clase.</div>`;
                }
            } else {
                if (filesContainerA)
                    filesContainerA.innerHTML = `<div style="text-align:center;color:#64748b;padding:15px;font-style:italic;font-family:'Poppins',sans-serif;">No hay archivos en el servidor.</div>`;
                if (filesContainerR)
                    filesContainerR.innerHTML = `<div style="text-align:center;color:#64748b;padding:15px;font-style:italic;font-family:'Poppins',sans-serif;">No hay archivos en el servidor.</div>`;
            }
        })
        .catch((err) => {
            console.error(err);
            if (filesContainerA)
                filesContainerA.innerHTML = `<div style="text-align:center;color:#ef4444;padding:15px;">Error al cargar.</div>`;
            if (filesContainerR)
                filesContainerR.innerHTML = `<div style="text-align:center;color:#ef4444;padding:15px;">Error al cargar.</div>`;
        });
};
window.cerrarModalGestionVeredicto = function () {
    const modal = document.getElementById("modalGestionVeredicto");
    if (modal) modal.classList.remove("open");
    document.body.classList.remove("modal-open");
};
document
    .getElementById("formMgvAprobados")
    ?.addEventListener("submit", async function (e) {
        e.preventDefault();
        let allValid = true;
        const fileInputs = this.querySelectorAll('input[type="file"]');
        fileInputs.forEach((input) => {
            if (
                input.hasAttribute("required") &&
                (!input.files || input.files.length === 0)
            ) {
                allValid = false;
            }
        });
        if (!allValid) {
            almacenToast(
                "Por favor, selecciona los formatos LDM requeridos.",
                "error",
            );
            return;
        }
        const formData = new FormData(this);
        const submitBtn = document.getElementById("btn-submit-aprobados");
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.querySelector("span").innerText = "Procesando...";
        }
        try {
            const response = await fetch(window.almacenRoutes.iniciarCasting, {
                method: "POST",
                body: formData,
                headers: {
                    "X-CSRF-TOKEN":
                        document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute("content") || "",
                },
            });
            const data = await response.json();
            if (data.success) {
                almacenToast(data.message, "success");
                const otRaw = document.getElementById("mgv-ot").value;
                sessionStorage.setItem("openCastingOt", otRaw);
                setTimeout(() => {
                    cerrarModalGestionVeredicto();
                    location.reload();
                }, 1500);
            } else {
                almacenToast(
                    data.message || "Error al procesar casting.",
                    "error",
                );
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.querySelector("span").innerText =
                        "Procesar Aceptados";
                }
            }
        } catch (err) {
            console.error(err);
            almacenToast("Error de red.", "error");
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.querySelector("span").innerText =
                    "Procesar Aceptados";
            }
        }
    });
document
    .getElementById("formMgvRechazados")
    ?.addEventListener("submit", async function (e) {
        e.preventDefault();
        let allValid = true;
        const fileInputs = this.querySelectorAll('input[type="file"]');
        fileInputs.forEach((input) => {
            if (
                input.hasAttribute("required") &&
                (!input.files || input.files.length === 0)
            ) {
                allValid = false;
            }
        });
        if (!allValid) {
            almacenToast(
                "Por favor, selecciona el Formato de Rechazo y el SCAR requeridos para todas las clases.",
                "error",
            );
            return;
        }
        const formData = new FormData(this);
        const submitBtn = document.getElementById("btn-submit-rechazados");
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.querySelector("span").innerText = "Procesando...";
        }
        try {
            const response = await fetch(
                window.almacenRoutes.procesarRechazos,
                {
                    method: "POST",
                    body: formData,
                    headers: {
                        "X-CSRF-TOKEN":
                            document
                                .querySelector('meta[name="csrf-token"]')
                                ?.getAttribute("content") || "",
                    },
                },
            );
            const data = await response.json();
            if (data.success) {
                almacenToast(data.message, "success");
                if (data.new_ot) {
                    sessionStorage.setItem("openPreordenOt", data.new_ot);
                }
                setTimeout(() => {
                    cerrarModalGestionVeredicto();
                    if (data.pdf_url) {
                        window.open(data.pdf_url, "_blank");
                    }
                    location.reload();
                }, 1500);
            } else {
                almacenToast(data.message || "Error al procesar.", "error");
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.querySelector("span").innerText =
                        "Subir Formatos y Continuar";
                }
            }
        } catch (err) {
            console.error(err);
            almacenToast("Error de red.", "error");
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.querySelector("span").innerText =
                    "Subir Formatos y Continuar";
            }
        }
    });
/* =========================================================================
AUTOGUARDADO DE FORMATO DE LIBERACION (LOCALSTORAGE)
========================================================================= */
window._libAutoSaveTimeout = null;
window.saveLiberacionDraft = function () {
    const form = document.getElementById("formLiberacion");
    if (!form) return;
    const ot = document.getElementById("lib-ot")?.value;
    const tipo = document.getElementById("lib-tipo")?.value;
    if (!ot || !tipo) return;
    // Solo guardar inputs numéricos y textareas para evitar pisar campos ocultos
    const inputs = form.querySelectorAll(
        ".lib-num-input, .lib-num-input-sm, .lib-textarea, #lib-motivo-rechazo",
    );
    const draftData = {};
    inputs.forEach((inp) => {
        if (inp.name) {
            draftData[inp.name] = inp.value;
        }
    });
    const key = `liberacion_draft_${ot}_${tipo}`;
    localStorage.setItem(key, JSON.stringify(draftData));
};
window.loadLiberacionDraft = function () {
    const form = document.getElementById("formLiberacion");
    if (!form) return;
    const ot = document.getElementById("lib-ot")?.value;
    const tipo = document.getElementById("lib-tipo")?.value;
    if (!ot || !tipo) return;
    const key = `liberacion_draft_${ot}_${tipo}`;
    const draftDataStr = localStorage.getItem(key);
    if (draftDataStr) {
        try {
            const draftData = JSON.parse(draftDataStr);
            const inputs = form.querySelectorAll(
                ".lib-num-input, .lib-num-input-sm, .lib-textarea, #lib-motivo-rechazo",
            );
            inputs.forEach((inp) => {
                if (inp.name && draftData[inp.name] !== undefined) {
                    inp.value = draftData[inp.name];
                }
            });
            console.log(
                `[Autosave] Borrador cargado para OT: ${ot}, Tipo: ${tipo}`,
            );
        } catch (e) {
            console.error("Error al parsear el borrador:", e);
        }
    }
};
window.clearLiberacionDraft = function () {
    const ot = document.getElementById("lib-ot")?.value;
    const tipo = document.getElementById("lib-tipo")?.value;
    if (ot && tipo) {
        const key = `liberacion_draft_${ot}_${tipo}`;
        localStorage.removeItem(key);
        console.log(
            `[Autosave] Borrador limpiado para OT: ${ot}, Tipo: ${tipo}`,
        );
    }
};
// Configurar el listener en el formulario para detectar cambios y disparar el autosave con debounce
document.addEventListener("DOMContentLoaded", () => {
    const formLib = document.getElementById("formLiberacion");
    if (formLib) {
        formLib.addEventListener("input", (e) => {
            if (
                e.target.matches(
                    ".lib-num-input, .lib-num-input-sm, .lib-textarea, #lib-motivo-rechazo",
                )
            ) {
                clearTimeout(window._libAutoSaveTimeout);
                window._libAutoSaveTimeout = setTimeout(() => {
                    window.saveLiberacionDraft();
                }, 800); // 800ms debounce
            }
        });
    }
});
// ══════════════════════════════════════════════════════════
// FundicionChecklistCard — Checklist reactivo del flujo de fundición
// (Añadido para dar soporte interactivo a la vista actual)
// ══════════════════════════════════════════════════════════
class FundicionChecklistCard {
    constructor(otId, container) {
        this.otId = otId;
        this._data = { pasos: {} };
        this.container = container;
        this._pollTimer = null;
        this._mounted = false;
        this.root = null;
        if (container.classList.contains("fundicion-checklist-card")) {
            this.root = container;
            this.root.innerHTML = "";
            this._mounted = true;
            this._renderInto(this.root);
        } else {
            this._mount();
        }
        this._poll();
        this._startPolling();
    }
    _mount() {
        if (this._mounted) return;
        this.root = document.createElement("div");
        this.root.className = "fundicion-checklist-card";
        this.root.id = `fundicion-checklist-${this.otId}`;
        this._renderInto(this.root);
        this.container.appendChild(this.root);
        this._mounted = true;
    }
    _renderInto(card) {
        const header = document.createElement("div");
        header.className = "checklist-header";
        const title = document.createElement("span");
        title.className = "checklist-title";
        title.textContent = "Levantamiento de OT";
        header.appendChild(title);
        const badge = document.createElement("span");
        badge.className = "checklist-reproceso-badge";
        badge.id = `checklist-badge-${this.otId}`;
        badge.textContent = "Reproceso";
        badge.classList.add("cal-display-none");
        header.appendChild(badge);
        card.appendChild(header);
        const itemsContainer = document.createElement("div");
        itemsContainer.className = "checklist-items";
        itemsContainer.id = `checklist-items-${this.otId}`;
        itemsContainer.classList.add("cal-display-none");
        card.appendChild(itemsContainer);
        card.classList.add("is-closed");
        // Toggle logic: make whole card clickable
        card.style.cursor = "pointer";
        card.addEventListener("click", () => {
            if (itemsContainer.classList.contains("cal-display-none")) {
                itemsContainer.classList.remove("cal-display-none");
                card.classList.remove("is-closed");
            } else {
                itemsContainer.classList.add("cal-display-none");
                card.classList.add("is-closed");
            }
        });
        this._updateCard(card, this._data);
    }
    _getIconFor(estado) {
        const baseUrl = window.baseUrl || window.location.origin + "/";
        const slash = baseUrl.endsWith("/") ? "" : "/";
        let imgName = "";
        switch (estado) {
            case "completado":
                imgName = "Aprobado.png";
                break;
            case "pendiente":
                imgName = "Espera.png";
                break;
            case "rechazado":
                imgName = "Rechazado.png";
                break;
            case "inactivo":
            default:
                imgName = "Recibido.png";
                break;
        }
        return `${baseUrl}${slash}images/${imgName}`;
    }
    _getBorderColor(data) {
        const pasos = Object.values(data.pasos || {});
        if (pasos.some((p) => p.estado === "rechazado")) return "#9D0402";
        if (pasos.length > 0 && pasos.every((p) => p.estado === "completado"))
            return "#0C8201";
        return "#424141";
    }
    _updateCard(card, data) {
        if (!card) return;
        let colorHex = this._getBorderColor(data);
        if (colorHex === "#9D0402") {
            card.style.borderColor = "#9D0402";
            card.style.boxShadow = "none";
        } else if (colorHex === "#0C8201") {
            card.style.borderColor = "#0C8201";
            card.style.boxShadow = "none";
        } else {
            card.style.borderColor = "";
            card.style.boxShadow = "";
        }
        const badge = card.querySelector(`#checklist-badge-${this.otId}`);
        if (badge) {
            badge.classList.toggle("cal-display-none", !data.isBadgeVisible);
            if (data.badgeText) badge.textContent = data.badgeText;
        }
        const container = card.querySelector(`#checklist-items-${this.otId}`);
        if (!container) return;
        container.innerHTML = "";
        const pasosEntries = Object.entries(data.pasos || {});
        if (pasosEntries.length === 0) {
            container.innerHTML = `<div style="padding: 10px; color: #64748b; font-size: 0.85rem; text-align: center;">Cargando estado...</div>`;
            return;
        }
        pasosEntries.forEach(([key, paso]) => {
            if (!paso) return;
            const item = document.createElement("div");
            item.className = `checklist-item checklist-item--${paso.estado}`;
            if (paso.tooltip) {
                item.title = paso.tooltip;
                item.style.cursor = "help";
            }
            const iconSpan = document.createElement("span");
            iconSpan.className = "checklist-icon";
            const img = document.createElement("img");
            img.src = this._getIconFor(paso.estado);
            img.alt = paso.estado;
            img.className = "checklist-state-icon";
            iconSpan.appendChild(img);
            const label = document.createElement("span");
            label.className = "checklist-label";
            label.textContent = paso.label;
            item.appendChild(iconSpan);
            item.appendChild(label);
            container.appendChild(item);
        });
    }
    _startPolling() {
        this._pollTimer = setInterval(() => this._poll(), 30_000);
    }
    async _poll() {
        if (!this.root || !this.root.isConnected) {
            this._destroy();
            return;
        }
        try {
            const endpointUrl =
                window.fundicionChecklistUrl ||
                `${window.location.origin}/admin/fundicion/checklist`;
            const endpoint = `${endpointUrl}/${this.otId}`;
            const res = await fetch(endpoint);
            if (!res.ok) return;
            const data = await res.json();
            if (data && !data.error) {
                this._data = data;
                this._updateCard(this.root, data);
            }
        } catch (_) {}
    }
    _destroy() {
        clearInterval(this._pollTimer);
        this._pollTimer = null;
        if (
            this.root &&
            this.root.isConnected &&
            !this.root.classList.contains("fundicion-checklist-card")
        ) {
            this.root.remove();
        }
    }
}
function initFundicionChecklists() {
    document
        .querySelectorAll(
            ".fundicion-checklist-card, .fundicion-checklist-container",
        )
        .forEach((el) => {
            if (el.hasAttribute("data-checklist-init")) return;
            let otId = el.getAttribute("data-ot");
            if (!otId && el.id && el.id.startsWith("fundicion-checklist-")) {
                otId = el.id.replace("fundicion-checklist-", "");
            }
            if (otId) {
                el.setAttribute("data-checklist-init", "true");
                new FundicionChecklistCard(otId, el);
            }
        });
}
document.addEventListener("DOMContentLoaded", () => {
    setTimeout(initFundicionChecklists, 500);
});
window.initFundicionChecklists = initFundicionChecklists;
// ── SINCRONIZACIÓN AUTOMÁTICA Y MANUAL DE DIBUJOS ──────────────────────────────
window._syncSnapshot = {};
window._syncIntervalId = null;
window._lastSyncTime = null;
window.sincronizarDibujos = function (manual = false) {
    if (!manual) return; // Solo ejecutar cuando el usuario presiona "Sincronizar ahora"
    let btnId = "btn-sync-manual-calidad";
    let btn = document.getElementById(btnId);
    if (!btn) {
        btnId = "btn-sync-manual-almacen";
        btn = document.getElementById(btnId);
    }
    if (manual && btn) {
        btn.innerHTML = `<span class="alm-spinner" style="width:14px;height:14px;border-width:2px;border-top-color:#fff;display:inline-block;vertical-align:middle;margin-right:5px;"></span> Sincronizando...`;
        btn.disabled = true;
    }
    const tbody = document.getElementById("alm-tbody-activa");
    if (!tbody) {
        if (manual && btn) {
            btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-right:5px;vertical-align:middle;"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg> Sincronizar ahora`;
            btn.disabled = false;
        }
        return;
    }
    const routesObj = window.almacenRoutes || window.calidadRoutes;
    if (!routesObj || !routesObj.archivos) return;
    const rows = tbody.querySelectorAll("tr[data-ot]");
    let promises = [];
    rows.forEach((row) => {
        const ot = row.getAttribute("data-ot");
        if (!ot) return;
        const baseUrlArchivos = (routesObj.archivos || "").trim();
        const p = fetch(`${baseUrlArchivos}?ot=${encodeURIComponent(ot)}`)
            .then((res) => {
                if (!res.ok) throw new Error(`HTTP ${res.status}`);
                return res.json();
            })
            .then((data) => {
                if (data.existe) {
                    const count = Array.isArray(data.archivos)
                        ? data.archivos.length
                        : 0;
                    window._syncSnapshot[ot] = count;
                    const badge = row.querySelector(".badge-pdf-count");
                    if (badge) {
                        badge.textContent = count;
                    }
                }
            })
            .catch((err) => console.error(`Error sync OT ${ot}:`, err));
        promises.push(p);
    });
    Promise.all(promises).then(() => {
        if (manual && btn) {
            btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-right:5px;vertical-align:middle;"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg> Sincronizar ahora`;
            btn.disabled = false;
            if (typeof almacenToast === 'function') almacenToast("Sincronización manual completada.", "success");
        }
        let timeId = "sync-last-time-calidad";
        let statusTime = document.getElementById(timeId);
        if (!statusTime)
            statusTime = document.getElementById("sync-last-time-almacen");
        if (statusTime) {
            window._lastSyncTime = new Date();
            actualizarRelojSync();
        }
    });
};
function actualizarRelojSync() {
    let statusTime = document.getElementById("sync-last-time-calidad");
    if (!statusTime)
        statusTime = document.getElementById("sync-last-time-almacen");
    if (!statusTime || !window._lastSyncTime) return;
    const now = new Date();
    const diffSecs = Math.floor((now - window._lastSyncTime) / 1000);
    if (diffSecs < 5) {
        statusTime.textContent = "Actualizado: justo ahora";
    } else if (diffSecs < 60) {
        statusTime.textContent = `Actualizado: hace ${diffSecs} seg`;
    } else {
        const mins = Math.floor(diffSecs / 60);
        statusTime.textContent = `Actualizado: hace ${mins} min`;
    }
}
document.addEventListener("DOMContentLoaded", () => {
    if (document.getElementById("alm-tbody-activa")) {
        window._lastSyncTime = new Date();
        actualizarRelojSync();
        setInterval(actualizarRelojSync, 5000);
    }
});
