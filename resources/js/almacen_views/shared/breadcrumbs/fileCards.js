// ── FILE CARDS — Renderizado de tarjetas de archivos categorizados ─────────────
// Uso: window.generarHtmlCategorizadoArchivos(archivos, ot, baseUrl, inputNameMode)
//
// inputNameMode: "preorden" | "scar" | "calidad" | "general"
//
// Separa los archivos en categorías (Dibujos, Ayudas, Aprobados, Rechazados, Otros)
// y devuelve HTML listo para insertar en cualquier contenedor.

// ── Helper: Comparación quirúrgica de clases y nombres de archivos ────────────────
// Retorna true si hay coincidencia de palabra clave (normalizada, sin acentos ni plurales)
window.compararClasesSurgico = function (claseA, claseB) {
    if (!claseA || !claseB) return false;
    
    const normA = claseA.toLowerCase()
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .replace(/^modelo\s+/i, "")
        .replace(/^casting\s+/i, "")
        .replace(/[^a-z0-9]/g, " ")
        .trim();
        
    const normB = claseB.toLowerCase()
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .replace(/^modelo\s+/i, "")
        .replace(/^casting\s+/i, "")
        .replace(/[^a-z0-9]/g, " ")
        .trim();

    const wordsA = normA.split(/\s+/).map(w => w.replace(/s$/, ""));
    const wordsB = normB.split(/\s+/).map(w => w.replace(/s$/, ""));
    
    return wordsA.some(wA => wA.length > 2 && wordsB.some(wB => wB === wA || (wB.length > 2 && wA.includes(wB)) || (wA.length > 2 && wB.includes(wA))));
};

// ── Helper: crea una card visual para un File object local ─────────────────────
// file         : File object
// index        : índice en el array (para el botón Quitar)
// removeFnName : nombre de función global (string), ej. "window.removeCmConfirmarAttachment"
// accentColor  : color de borde (default "#16a34a")
window.crearFileCard = function (file, index, removeFnName, accentColor) {
    const color = accentColor || "#16a34a";
    const baseUrl = typeof window.getBaseUrl === "function"
        ? window.getBaseUrl()
        : (window.baseUrl || "/");
    const ext = file.name.split(".").pop().toLowerCase();
    const esImg = ["png", "jpg", "jpeg", "gif", "webp", "bmp"].includes(ext);
    const isDwg = ext === "dwg";
    const iconDefault = isDwg ? "dwg-shadow.png" : (esImg ? "galeria-shadow.png" : "pdf-view-shadow.png");
    const iconHover   = isDwg ? "dwg.png" : (esImg ? "galeria.png"        : "pdf-view.png");
    const titleAttr   = isDwg ? "Descargar DWG" : "Abrir archivo";
    const btnText     = isDwg ? "Descargar" : "Ver";
    const sizeKb = (file.size / 1024).toFixed(1);
    const shortName = file.name.length > 28 ? file.name.substring(0, 26) + "…" : file.name;

    // Crear URL temporal para preview del archivo local
    const objectUrl = URL.createObjectURL(file);

    const card = document.createElement("div");
    card.className = "dibujos-file-card card-otro";
    card.style.cssText = `
        position: relative;
        width: 100%;
        max-width: 220px;
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        box-sizing: border-box;
        background: #fff;
        padding: 10px;
        border: 1.5px solid #e2e8f0;
        animation-delay: ${index * 0.05}s;
        border-left-color: ${color};
    `;
    card.innerHTML = `
        <div class="file-icon-wrapper cal-cursor-pointer" style="cursor:pointer; margin-top:10px;" title="${titleAttr}">
            <img src="${baseUrl}images/${iconDefault}" class="file-icon icon-default" style="width:48px;height:auto;">
            <img src="${baseUrl}images/${iconHover}"   class="file-icon icon-hover"   style="width:48px;height:auto;">
        </div>
        <div class="file-name cal-cursor-pointer" style="cursor:pointer;font-size:0.82em;margin:8px 0;max-height:40px;overflow:hidden;text-overflow:ellipsis;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;font-weight:600;color:#334155;line-height:1.3;" title="${titleAttr}">
            ${shortName}
        </div>
        <div style="font-size:0.7em; color:#64748b; font-family:'Poppins',sans-serif; margin-bottom: 4px;">${sizeKb} KB</div>
        <div class="file-actions" style="width:100%; margin-top:auto; display:flex; gap:5px;">
            <button type="button" class="btn-dibujos btn-dibujos-sm btn-ver" style="font-size:0.8em;padding:5px 8px;border-radius:6px;font-family:'Poppins',sans-serif;font-weight:600;flex:1;" title="${titleAttr}">${btnText}</button>
            <button type="button" class="btn-dibujos btn-dibujos-sm btn-eliminar" style="font-size:0.8em;padding:5px 8px;border-radius:6px;font-family:'Poppins',sans-serif;font-weight:600;flex:1;" onclick="${removeFnName}(${index})" title="Quitar archivo">Quitar</button>
        </div>
    `;
    // Click en icono/nombre/botón Ver → abrir preview
    card.querySelector(".file-icon-wrapper").addEventListener("click", () => {
        window.open(objectUrl, "_blank");
    });
    card.querySelector(".file-name").addEventListener("click", () => {
        window.open(objectUrl, "_blank");
    });
    card.querySelector(".btn-ver").addEventListener("click", () => {
        window.open(objectUrl, "_blank");
    });
    return card;
};

// ── Helper: renderiza lista de File objects en un contenedor DOM ─────────────────
// containerId  : id del elemento contenedor
// filesArray   : array de File objects
// removeFnName : función de quitar (string global), ej. "window.removeScarFotoAttachment"
// accentColor  : color de borde (opcional)
// emptyMsg     : mensaje cuando lista está vacía (opcional)
window.renderFileCards = function (containerId, filesArray, removeFnName, accentColor, emptyMsg) {
    const list = document.getElementById(containerId);
    if (!list) return;
    list.innerHTML = "";

    if (!filesArray || filesArray.length === 0) {
        list.innerHTML = `<div style="width:100%;text-align:center;color:#94a3b8;font-size:0.85em;
            padding:16px 0;font-family:'Poppins',sans-serif;">
            ${emptyMsg || "Ningún archivo adjuntado aún."}
        </div>`;
        return;
    }
    filesArray.forEach((file, idx) => {
        list.appendChild(window.crearFileCard(file, idx, removeFnName, accentColor));
    });
};



window.generarHtmlCategorizadoArchivos = function (archivos, ot, baseUrl, inputNameMode) {
    let dibujosPdfs = [];
    let ayudasPdfs  = [];
    let aprobadosPdfs  = [];
    let rechazadosPdfs = [];
    let otrosPdfs = [];

    if (Array.isArray(archivos)) {
        archivos.forEach((f) => {
            const ext = f.nombre.split(".").pop().toLowerCase();
            if (!["pdf","png","jpg","jpeg","gif","webp","bmp","dwg"].includes(ext)) return;

            const lower = f.nombre.toLowerCase().replace(/\\/g, "/");
            const origin = (f.origin || "").toLowerCase();

            // ── Clasificación por nombre de archivo — nomenclaturas viejas y nuevas ─────
            // Rechazado: RDM / SCAR / documentos_rechazados / fdrdm / F-CCL-RDM
            const esRechazado =
                origin === "rechazado" ||
                /documentos.rechazados/.test(lower) ||
                /[_\-.\s]scar[_\-.\s]/.test(lower) ||
                lower.startsWith("scar") ||
                lower.includes("/scar/") ||
                lower.includes("f_ccl_scar") ||
                lower.includes("f-ccl-scar") ||
                lower.includes("f_ccl_rdm") ||
                lower.includes("f-ccl-rdm") ||
                lower.includes("fdrdm") ||
                (/[_\-.\/]rdm[_\-.\/]/.test(lower) && !lower.includes("standard")) ||
                lower.includes("/fdrdm/");

            // Aprobado: LDM firmado / documentos_aprobados / ConfirmacionModelo
            const esAprobado =
                origin === "aprobado" ||
                /documentos.aprobados/.test(lower) ||
                lower.includes("f_ccl_ldm") ||
                lower.includes("f-ccl-ldm") ||
                lower.includes("fdldm") ||
                lower.includes("/fdldm/") ||
                lower.includes("confirmacionmodelo") ||
                lower.includes("confirmacion_modelo") ||
                lower.includes("confirmacion-modelo");

            // Pre-órdenes: PFC, PFM, CFM, EFM, EFC — cualquier variante con - o _
            const esPreOrden =
                lower.includes("pre-orden") ||
                lower.includes("preorden") ||
                lower.includes("preorden_casting") ||
                lower.includes("preorden_modelo") ||
                lower.includes("preorden-casting") ||
                lower.includes("preorden-modelo") ||
                /[_\-.]pfc[_\-.]/.test(lower) || lower.endsWith("_pfc.pdf") || lower.endsWith("-pfc.pdf") ||
                /[_\-.]pfm[_\-.]/.test(lower) || lower.endsWith("_pfm.pdf") || lower.endsWith("-pfm.pdf") ||
                /[_\-.]cfm[_\-.]/.test(lower) || lower.endsWith("_cfm.pdf") || lower.endsWith("-cfm.pdf") ||
                /[_\-.]efm[_\-.]/.test(lower) || lower.endsWith("_efm.pdf") || lower.endsWith("-efm.pdf") ||
                /[_\-.]efc[_\-.]/.test(lower) || lower.endsWith("_efc.pdf") || lower.endsWith("-efc.pdf") ||
                lower.includes("f_alm_pfc") || lower.includes("f-alm-pfc") ||
                lower.includes("f_alm_pfm") || lower.includes("f-alm-pfm") ||
                lower.includes("f_alm_cfm") || lower.includes("f-alm-cfm") ||
                lower.includes("f_alm_efm") || lower.includes("f-alm-efm") ||
                lower.includes("f_alm_efc") || lower.includes("f-alm-efc");

            if (esRechazado) {
                rechazadosPdfs.push(f);
            } else if (esAprobado || esPreOrden) {
                aprobadosPdfs.push(f);
            } else if (f.tipo === "ayuda" || lower.includes("ayudas_visuales") || lower.includes("ayuda_visual")) {
                ayudasPdfs.push(f);
            } else if (f.tipo === "dibujo" || lower.includes("dibujos_fundicion") || lower.includes("dibujo") || ext === "dwg") {
                dibujosPdfs.push(f);
            } else {
                otrosPdfs.push(f);
            }
        });
    }

    // ── Colores de borde por categoría ──────────────────────────────────────────
    const CATEGORY_COLORS = {
        rechazados: "#9c0300",
        scar:       "#9c0300",
        aprobados:  "#059669",
        liberación: "#059669",
        liberados:  "#059669",
        dibujos:    "#0284c7",
        planos:     "#0284c7",
        ayudas:     "#d97706",
    };

    const makeCategorySection = (title, files, inputName, colorClass) => {
        if (!files.length) return "";
        const tLow = title.toLowerCase();
        let borderColor = "#033966";
        for (const [key, color] of Object.entries(CATEGORY_COLORS)) {
            if (tLow.includes(key)) { borderColor = color; break; }
        }

        const cards = files.map((f) => {
            const cleanName = f.nombre.split("/").pop();
            const ext = f.nombre.split(".").pop().toLowerCase();
            const esImg = ["png","jpg","jpeg","gif","webp","bmp"].includes(ext);
            const isDwg = ext === "dwg";
            const defaultIcon = isDwg ? "dwg-shadow.png" : (esImg ? "galeria-shadow.png" : "pdf-view-shadow.png");
            const hoverIcon   = isDwg ? "dwg.png" : (esImg ? "galeria.png"        : "pdf-view.png");
            const titleAttr   = isDwg ? "Descargar DWG" : "Abrir Archivo";
            const btnText     = isDwg ? "Descargar" : "Ver";

            const isConfirmacion = cleanName.toLowerCase().includes("confirmacionmodelo");
            const shouldCheck = !(inputNameMode === "preorden" && isConfirmacion);
            const checkedAttr  = shouldCheck ? 'checked="checked"' : "";
            const checkedClass = shouldCheck ? "checked-card" : "";

            const safeName = f.nombre.replace(/'/g, "\\'");
            const safeOt   = ot.replace(/'/g, "\\'");
            const safeTipo = (f.tipo || "otro").replace(/'/g, "\\'");
            const fnViewer = typeof window.almacenVerPdf === "function"
                ? "window.almacenVerPdf"
                : "window.calidadVerPdf";

            const esDibujoOAyuda = f.tipo === "dibujo" || f.tipo === "ayuda";
            const lowerName = f.nombre.toLowerCase();
            let fileOwner = "almacen";
            if (
                lowerName.includes("calidad_fundicion") ||
                lowerName.includes("f-ccl-ldm") ||
                lowerName.includes("f-ccl-scar") ||
                lowerName.includes("scar")
            ) {
                fileOwner = "calidad";
            }

            let yaEnviado = false;
            if (fileOwner === "almacen" && f.almacen_alert_sent) {
                yaEnviado = true;
            } else if (fileOwner === "calidad" && f.calidad_alert_sent) {
                yaEnviado = true;
            }

            const mostrarEliminar = !esDibujoOAyuda && !yaEnviado;

            return `
                <div class="dibujos-file-card ${colorClass} select-file-card ${checkedClass}"
                     style="position:relative;width:100%;max-width:220px;display:inline-flex;flex-direction:column;align-items:center;text-align:center;border-radius:12px;box-shadow:0 4px 6px rgba(0,0,0,0.05);box-sizing:border-box;background:#fff;padding:10px;border:1.5px solid #e2e8f0;">
                    <div style="position:absolute;top:10px;left:10px;z-index:10;">
                        <input type="checkbox" name="${inputName}" value="${f.nombre}" ${checkedAttr}
                               style="width:20px;height:20px;cursor:pointer;"
                               onchange="this.closest('.select-file-card').classList.toggle('checked-card',this.checked);">
                    </div>
                    <div class="file-icon-wrapper"
                         onclick="${fnViewer}('${safeOt}','${safeName}','${safeTipo}')"
                         style="cursor:pointer;margin-top:10px;" title="${titleAttr}">
                        <img src="${baseUrl}images/${defaultIcon}" class="file-icon icon-default" style="width:48px;height:auto;">
                        <img src="${baseUrl}images/${hoverIcon}"   class="file-icon icon-hover"   style="width:48px;height:auto;">
                    </div>
                    <div class="file-name"
                         style="cursor:pointer;font-size:0.82em;margin:8px 0;max-height:40px;overflow:hidden;text-overflow:ellipsis;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;font-weight:600;color:#334155;line-height:1.3;"
                         title="${titleAttr}"
                         onclick="${fnViewer}('${safeOt}','${safeName}','${safeTipo}')">
                        ${cleanName}
                    </div>
                    <div class="file-actions" style="width:100%;margin-top:auto;display:flex;gap:5px;">
                        <button type="button" class="btn-dibujos btn-dibujos-sm btn-ver btn-ayuda-color"
                                style="font-size:0.8em;padding:5px 8px;border-radius:6px;font-family:'Poppins',sans-serif;font-weight:600;flex:1;"
                                onclick="${fnViewer}('${safeOt}','${safeName}','${safeTipo}')">${btnText}</button>
                        ${mostrarEliminar ? `
                        <button type="button" class="btn-dibujos btn-dibujos-sm"
                                style="font-size:0.8em;padding:5px 8px;border-radius:6px;font-family:'Poppins',sans-serif;font-weight:600;flex:1;background:#dc2626;color:white;border:none;cursor:pointer;"
                                onclick="window.eliminarArchivoServidorCategorizado('${safeOt}','${safeName}','${safeTipo}')">Eliminar</button>
                        ` : ""}
                    </div>
                </div>`;
        }).join("");

        return `
            <div style="margin-bottom: 15px;">
                <h4 style="margin: 0 0 10px 0; color: #033966; font-size: 0.95em; border-bottom: 2px solid ${borderColor}; padding-bottom: 4px; font-weight: 700; font-family: 'Poppins', sans-serif; display: flex; align-items: center; gap: 6px;">
                    ${title}
                </h4>
                <div class="alm-pdf-grid" style="background-color: #f8fafc; border: 1px solid #e2e8f0; padding: 15px; border-radius: 8px;">
                    ${cards}
                </div>
            </div>
        `;
    };

    // ── Nombres de campos según modo ─────────────────────────────────────────────
    const NAMES = {
        preorden: { dibujos: "archivos_seleccionados[]", ayudas: "archivos_seleccionados[]", aprobados: "archivos_seleccionados[]", rechazados: "archivos_seleccionados[]", otros: "archivos_seleccionados[]" },
        scar:     { dibujos: "dibujos[]", ayudas: "ayudas[]", aprobados: "otros_documentos[]", rechazados: "otros_documentos[]", otros: "otros_documentos[]" },
        default:  { dibujos: "dibujos[]", ayudas: "ayudas[]", aprobados: "dibujos_aprobados[]", rechazados: "dibujos_rechazados[]", otros: "otros_documentos[]" },
    };
    const n = NAMES[inputNameMode] || NAMES.default;

    // ── Orden estándar de secciones (igual en TODOS los modales) ───────────────
    // 1. Dibujos de Fundición
    // 2. Ayudas Visuales
    // 3. Pre-órdenes / Documentos Aprobados
    // 4. Documentos Rechazados (SCAR / RDM)
    // 5. Otros (solo en modos que lo necesiten)
    let html = "";
    html += makeCategorySection("Dibujos de Fundición",  dibujosPdfs,   n.dibujos,    "card-plano");
    html += makeCategorySection("Ayudas Visuales",       ayudasPdfs,    n.ayudas,     "card-ayuda");
    html += makeCategorySection("Pre-órdenes / Documentos Aprobados", aprobadosPdfs, n.aprobados, "card-ayuda");

    const tituloRech = inputNameMode === "calidad"
        ? "Documentos Rechazados"
        : "Documentos Rechazados (SCAR / RDM)";
    html += makeCategorySection(tituloRech, rechazadosPdfs, n.rechazados, "card-ayuda");

    // Otros: sólo en modo general (no preorden, no scar, no calidad)
    if (inputNameMode !== "preorden" && inputNameMode !== "scar" && inputNameMode !== "calidad") {
        html += makeCategorySection("Otros Documentos", otrosPdfs, n.otros, "card-ayuda");
    }

    return html;
};
