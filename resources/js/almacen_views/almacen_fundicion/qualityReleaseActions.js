// ── QUALITY RELEASE ACTIONS & MODALS EXTRACTION ──

window._libFiltrarTiposModelo = function (clasesActivas, todasClases) {
    const select = document.getElementById("lib-tipo");
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
        pistones: "Pistones",
        guías: "Guías",
        guias: "Guías",
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
        "pistones",
        "guías",
        "guias",
    ];
    const tiposConfigurados = new Set();
    const clasesAUsar = todasClases && todasClases.length > 0 ? todasClases : clasesActivas;
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
    select.querySelectorAll("option").forEach((opt) => {
        if (!opt.value) {
            opt.hidden = false;
            opt.disabled = false;
            opt.classList.remove("alm-display-none");
            return;
        }
        let optValLow = opt.value.toLowerCase();
        let isActive = Array.from(tiposActivos).some((t) => t.toLowerCase() === optValLow);
        let shouldHide = tiposActivos.size === 0 ? false : !isActive;

        // --- FILTRO DE ENVIADOS DESDE ALMACÉN (cacheLiberacionGlobal) ---
        // Si hay clases en cacheLiberacionGlobal (lo que almacen nos ha mandado/confirmado),
        // ocultamos del select cualquier opción que NO haya sido enviada.
        if (window.cacheLiberacionGlobal && Object.keys(window.cacheLiberacionGlobal).length > 0) {
            const keysEnviadas = Object.keys(window.cacheLiberacionGlobal).map(k => 
                k.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").trim()
            );
            const optValNorm = opt.value.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").trim();
            const haSidoEnviado = keysEnviadas.some(k => k === optValNorm || k.includes(optValNorm) || optValNorm.includes(k));
            if (!haSidoEnviado) {
                shouldHide = true;
            }
        }

        opt.hidden = shouldHide;
        opt.disabled = shouldHide;
        opt.classList.toggle("alm-display-none", shouldHide);
        if (!shouldHide) {
            if (!firstAvailable) firstAvailable = opt.value;
            if (isActive || tiposActivos.size === 0) {
                const cached = window.cacheLiberacionGlobal && window.cacheLiberacionGlobal[opt.value];
                const isProcessed = cached && (cached.decision === "aprobar" || cached.decision === "rechazar" || cached.estado === "aprobado" || cached.estado === "rechazado");
                if (!isProcessed && !firstUnprocessed) {
                    firstUnprocessed = opt.value;
                }
            }
        }
    });
    return firstUnprocessed || firstAvailable;
};

window.abrirModalLiberacionUnificado = function (ot, clasesActivas, todasClases) {
    if (!clasesActivas || !Array.isArray(clasesActivas) || clasesActivas.length === 0) {
        if (typeof almacenToast === "function") {
            almacenToast("No hay clases enviadas por Almacén para revisar", "error");
        }
        return;
    }
    window._currentClasesActivas = clasesActivas;
    window._currentTodasClases = todasClases;
    if (typeof abrirModalLiberacion === "function") {
        abrirModalLiberacion(ot, "aprobar");
    }
    window.libSeleccionarDecision("aprobar");
    const autoSelectValue = window._libFiltrarTiposModelo(clasesActivas, todasClases);
    if (autoSelectValue) {
        const select = document.getElementById("lib-tipo");
        if (select) {
            select.value = autoSelectValue;
            libCambiarTipo(autoSelectValue);
        }
    }
};

window._libSetDecisionUI = function (decision) {
    const accionInput = document.getElementById("lib-accion");
    if (accionInput) accionInput.value = decision;
    const cardAprobar = document.getElementById("lib-dec-aprobar");
    const cardRechazar = document.getElementById("lib-dec-rechazar");
    const bloqueRechazo = document.getElementById("lib-rechazo-block");
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
            bloqueRechazo.classList.add("alm-display-none");
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
            bloqueRechazo.classList.remove("alm-display-none");
            bloqueRechazo.removeAttribute("hidden");
            bloqueRechazo.style.display = "block";
        }
    }
    window._libActualizarBotonesAccion(decision);
};

window.libSeleccionarDecision = function (decision) {
    window._libSetDecisionUI(decision);
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

window._libActualizarBotonesAccion = function (decision) {
    const actionsEl = document.getElementById("lib-actions");
    if (!actionsEl) return;
    const btnAccion = actionsEl.querySelector("#lib-btn-accion");
    if (!btnAccion) return;
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
};

window.almacenEliminarOtroArchivo = function (ot, archivo, tipo, buttonEl, origin) {
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
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
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
                mostrarToast(data.message || "Archivo eliminado correctamente.");
                if (card) {
                    card.style.transition = "all 0.4s ease";
                    card.style.opacity = "0";
                    card.style.transform = "scale(0.8)";
                    setTimeout(() => {
                        card.remove();
                        const grid = card.closest(".alm-pdf-grid");
                        if (grid && grid.querySelectorAll(".dibujos-file-card").length === 0) {
                            location.reload();
                        }
                    }, 400);
                } else {
                    setTimeout(() => location.reload(), 1000);
                }
            } else {
                if (buttonEl) buttonEl.disabled = false;
                mostrarToast(data.error || "No se pudo eliminar el archivo.", true);
            }
        })
        .catch((err) => {
            if (buttonEl) buttonEl.disabled = false;
            console.error(err);
            mostrarToast("Error de conexión al eliminar el archivo.", true);
        });
};

window._crearFilaUpload = function (tipo, color, accentBg, esRechazo, baseUrl) {
    const idBase = `al-upload-${tipo.toLowerCase().replace(/\s/g, "-")}-${esRechazo ? "rech" : "aprob"}`;
    const tipoLabel = tipo.charAt(0).toUpperCase() + tipo.slice(1).toLowerCase();
    const nombre = esRechazo ? `archivos_rechazados_extra[${tipo}]` : `archivos_aprobados_extra[${tipo}]`;
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
                        onchange="window._alFileChanged('${idBase}-scar','${idBase}-scar-text','${idBase}-scar-label')">
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
                            onchange="window._alFileChanged('${idBase}','${idBase}-text','${idBase}-label')">
                    </label>
                    <div id="${idBase}-preview" style="font-size:0.9em;font-weight:600;color:#059669;display:none;font-family:'Poppins',sans-serif;width:100%;justify-content:center;"></div>
                </div>
            </div>
            ${scarBlock}
        </div>`;
};

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
    const borderCol = isScar ? "#ef4444" : inputId.includes("-rech") ? "#dc2626" : "#059669";
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
            </div>
        `;
    }
    const prv = document.getElementById(inputId + "-preview");
    if (prv) {
        prv.innerHTML = `
            <div class="dibujos-file-card select-file-card checked-card" style="position:relative; width:100%; max-width:180px; display:inline-flex; flex-direction:column; align-items:center; text-align:center; border-radius:12px; box-shadow:0 4px 10px rgba(0,0,0,0.08); box-sizing:border-box; font-size:0.95em; padding:12px; background:#fff; border:2px solid ${borderCol}; margin-top:12px;">
                <div style="position: absolute; top: 10px; right: 10px; z-index: 10;">
                    <button type="button" style="background: #fca5a5; border: none; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #9c0300; font-weight: bold; font-size: 0.95em; box-shadow: 0 2px 4px rgba(0,0,0,0.1); line-height: 1; padding: 0;" onclick="window._alClearFile('${inputId}')" title="Quitar">&times;</button>
                </div>
                ${iconHtml}
                <div class="file-name" style="cursor:pointer; font-size:0.88em; margin:8px 0; max-height:42px; overflow:hidden; font-weight:600; color:#334155; line-height:1.3; font-family:'Poppins',sans-serif;" onclick="window.open('${url}', '_blank')">${nm}</div>
                <div class="file-actions" style="width:100%; margin-top:auto;">
                    <button type="button" class="btn-dibujos btn-dibujos-sm btn-ver btn-ayuda-color" style="font-size:0.85em; padding:6px 14px; border-radius:6px; font-family:'Poppins',sans-serif; font-weight:600; width:100%; cursor:pointer;" onclick="window.open('${url}', '_blank')">Ver</button>
                </div>
            </div>
        `;
        prv.classList.remove("alm-display-none");
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
        prv.classList.add("alm-display-none");
    }
    const lbl = document.getElementById(inputId + "-label");
    if (lbl) lbl.style.borderStyle = "dashed";
    const txt = document.getElementById(inputId + "-text");
    if (txt) {
        txt.textContent = "Seleccionar archivo...";
    }
};

window._renderServerFileCard = function (file, ot, baseUrl, tipo) {
    const dispName = file.nombre.split("/").pop();
    const inputName = tipo === "rechazados" ? "dibujos_rechazados[]" : "dibujos_aprobados[]";
    const ext = file.nombre.split(".").pop().toLowerCase();
    const esImg = ["png", "jpg", "jpeg", "gif", "webp", "bmp"].includes(ext);
    const defaultIcon = esImg ? "galeria-shadow.png" : "pdf-view-shadow.png";
    const hoverIcon = esImg ? "galeria.png" : "pdf-view.png";
    return `<div class="dibujos-file-card card-ayuda select-file-card checked-card" style="position:relative;width:100%;max-width:230px;display:inline-flex;flex-direction:column;align-items:center;text-align:center;border-radius:12px;box-shadow:0 4px 10px rgba(0,0,0,0.08);box-sizing:border-box;font-size:0.95em;padding:12px;background:#fff;border:1.5px solid #e2e8f0;margin:4px;">
        <div style="position:absolute;top:10px;left:10px;z-index:10;"><input type="checkbox" name="${inputName}" value="${file.nombre}" checked style="width:18px;height:18px;cursor:pointer;" onchange="this.closest('.select-file-card').classList.toggle('checked-card',this.checked);"></div>
        <div class="file-icon-wrapper" onclick="almacenVerPdf('${ot}','${file.nombre}','${file.tipo}')" style="cursor:pointer;margin-top:12px;" title="Ver">
            <img src="${baseUrl}images/${defaultIcon}" class="file-icon icon-default" style="width:48px;height:48px;object-fit:contain;">
        </div>
        <div class="file-name" style="cursor:pointer;font-size:0.88em;margin:8px 0;max-height:42px;overflow:hidden;font-weight:600;color:#334155;line-height:1.3;font-family:'Poppins',sans-serif;" onclick="almacenVerPdf('${ot}','${file.nombre}','${file.tipo}')">${dispName}</div>
        <div class="file-actions" style="width:100%;margin-top:auto;"><button type="button" class="btn-dibujos btn-dibujos-sm btn-ver btn-ayuda-color" style="font-size:0.85em;padding:6px 14px;border-radius:6px;font-family:'Poppins',sans-serif;font-weight:600;width:100%;" onclick="almacenVerPdf('${ot}','${file.nombre}','${file.tipo}')">Ver</button></div>
    </div>`;
};

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

window.libCerrarLightbox = function () {
    const lb = document.getElementById("lib-lightbox");
    if (lb) lb.classList.remove("open");
};

// ── Lightbox & Zoom Magnifier ──
window._libZoomInit = false;
window._libInicializarZoom = function () {
    if (window._libZoomInit) return;
    window._libZoomInit = true;
    const zoomResult = document.getElementById("lib-zoom-result");
    if (!zoomResult) return;
    const ZOOM_SIZE = 450;
    const ZOOM_RATIO = 3.2;
    document.addEventListener("mousemove", (e) => {
        const wrapper = e.target.closest(".lib-img-zoom-wrapper");
        if (!wrapper) {
            zoomResult.classList.add("alm-display-none");
            return;
        }
        const modal = document.getElementById("modalLiberacionModelo");
        if (!modal || !modal.classList.contains("open")) {
            zoomResult.classList.add("alm-display-none");
            return;
        }
        const img = wrapper.querySelector(".lib-ref-img");
        if (!img || !img.complete) return;
        const rect = img.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        if (x < 0 || y < 0 || x > rect.width || y > rect.height) {
            zoomResult.classList.add("alm-display-none");
            return;
        }
        const bgX = -(x * ZOOM_RATIO - ZOOM_SIZE / 2);
        const bgY = -(y * ZOOM_RATIO - ZOOM_SIZE / 2);
        zoomResult.classList.remove("alm-display-none");
        zoomResult.style.backgroundImage = `url(${img.src})`;
        zoomResult.style.backgroundSize = `${rect.width * ZOOM_RATIO}px ${rect.height * ZOOM_RATIO}px`;
        zoomResult.style.backgroundPosition = `${bgX}px ${bgY}px`;
        zoomResult.style.width = `${ZOOM_SIZE}px`;
        zoomResult.style.height = `${ZOOM_SIZE}px`;
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
    document.addEventListener("mouseleave", () => {
        zoomResult.classList.add("alm-display-none");
    }, true);
};

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
    
    const arrAprobados = Array.isArray(tiposAprobados) ? tiposAprobados : [];
    const arrRechazados = Array.isArray(tiposRechazados) ? tiposRechazados : [];
    const hasAprobado = arrAprobados.length > 0;
    const hasRechazado = isAlmacen ? false : arrRechazados.length > 0;
    const esMixto = isAlmacen ? false : hasAprobado && hasRechazado;
    
    document.getElementById("al-ot").value = ot;
    document.getElementById("al-decision").value = isAlmacen ? "aprobar" : esMixto ? "mixto" : decision;
    document.getElementById("al-tipo-modelo").value = [...arrAprobados, ...(isAlmacen ? [] : arrRechazados)].join(", ");
    
    const fi = document.getElementById("al-fecha");
    if (fi) {
        fi.value = "";
    }
    const otClean = ot.replace(/_\d{8}_\d{6}_.*/, "");
    let bg, border, btnBg, ttl, pmt;
    
    if (isAlmacen) {
        bg = "linear-gradient(135deg,#0284c7,#0369a1)";
        border = "#0284c7";
        btnBg = "#0284c7";
        ttl = `Cargar LDM Firmado — ${otClean}`;
        pmt = `Por favor, sube el formato F-CCL-LDM firmado de los modelos aprobados (${arrAprobados.join(", ")}) para avanzar al proceso de Casting.`;
        const destGroup = document.getElementById("al-destinatario")?.closest(".form-group");
        if (destGroup) destGroup.classList.add("alm-display-none");
        const d = document.getElementById("al-destinatario");
        if (d) d.value = "jaxer020406@gmail.com";
    } else {
        const destGroup = document.getElementById("al-destinatario")?.closest(".form-group");
        if (destGroup) destGroup.classList.remove("alm-display-none");
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
            btnSpan.textContent = isAlmacen ? "Guardar Documentación Aprobada" : "Enviar Alerta";
        else
            btn.textContent = isAlmacen ? "Guardar Documentación Aprobada" : "Enviar Alerta";
    }
    const t = document.getElementById("alerta-lib-title");
    if (t) t.textContent = ttl;
    const p = document.getElementById("al-prompt-text");
    if (p) p.textContent = pmt;
    const s = document.getElementById("alerta-lib-subtitle");
    if (s) s.textContent = `OT: ${otClean}`;
    
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
    
    const colA = document.getElementById("al-col-aprobados");
    if (colA) colA.classList.toggle("alm-display-none", !hasAprobado);
    const colR = document.getElementById("al-col-rechazados");
    if (colR) colR.classList.toggle("alm-display-none", !hasRechazado);
    const dl = document.getElementById("al-dual-layout");
    if (dl) {
        dl.style.flexDirection = esMixto ? "row" : "column";
        dl.style.alignItems = "stretch";
    }
    const aLbl = document.getElementById("al-aprobados-tipos-label");
    if (aLbl) aLbl.textContent = arrAprobados.join(", ") || "—";
    const rLbl = document.getElementById("al-rechazados-tipos-label");
    if (rLbl) rLbl.textContent = arrRechazados.join(", ") || "—";
    
    let baseUrl = window.baseUrl || window.location.origin + "/";
    if (!baseUrl.endsWith("/")) baseUrl += "/";
    
    const rowsA = document.getElementById("al-upload-aprobados-rows");
    const rowsR = document.getElementById("al-upload-rechazados-rows");
    if (rowsA) {
        rowsA.innerHTML = arrAprobados.length
            ? arrAprobados.map((t) => window._crearFilaUpload(t, "#059669", "#f0fdf4", false, baseUrl)).join("")
            : '<p style="font-size:0.8em;color:#64748b;font-style:italic;">Sin modelos aprobados.</p>';
    }
    if (rowsR) {
        rowsR.innerHTML = arrRechazados.length
            ? arrRechazados.map((t) => window._crearFilaUpload(t, "#dc2626", "#fef2f2", true, baseUrl)).join("")
            : '<p style="font-size:0.8em;color:#64748b;font-style:italic;">Sin modelos rechazados.</p>';
    }
    
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
    
    const sA = document.getElementById("al-server-files-aprobados");
    const sR = document.getElementById("al-server-files-rechazados");
    const loadHtml = `<div style="text-align:center;color:#64748b;grid-column:1/-1;padding:8px;font-style:italic;font-size:0.8em;">Cargando...</div>`;
    const emptyHtml = `<div style="text-align:center;color:#94a3b8;grid-column:1/-1;padding:8px;font-style:italic;font-size:0.8em;">Sin archivos en servidor.</div>`;
    if (sA) sA.innerHTML = loadHtml;
    if (sR) sR.innerHTML = loadHtml;
    
    fetch(`${window.almacenRoutes.archivos}?ot=${encodeURIComponent(ot)}`)
        .then((r) => r.json())
        .then((data) => {
            let cardsA = "", cardsR = "";
            if (data.existe && data.archivos?.length > 0) {
                const archivoPerteneceAModelos = (nombre, modelosActivos) => {
                    const pl = nombre.toLowerCase();
                    const todosModelosPosibles = [
                        "candado obturador", "cabeza de soplo", "obturador", "bombillo",
                        "embudo", "corona", "plato", "molde", "fondo", "pistones", "guías", "guias"
                    ];
                    const modelosEncontrados = todosModelosPosibles.filter((m) => {
                        return (
                            pl.includes("/" + m + "/") ||
                            pl.startsWith(m + "/") ||
                            pl.includes("_" + m + "_") ||
                            pl.includes("-" + m + " -") ||
                            pl.includes(" " + m + " ") ||
                            pl.split("/").pop().startsWith(m)
                        );
                    });
                    if (modelosEncontrados.length === 0) return true;
                    const modelosActivosLower = modelosActivos.map((m) => m.toLowerCase());
                    return modelosEncontrados.some((m) => modelosActivosLower.includes(m));
                };
                
                data.archivos.forEach((f) => {
                    const pl = f.nombre.toLowerCase();
                    const isRechazadoFile = pl.includes("documentos_rechazados") || pl.includes("rechazado") || pl.includes("scar");
                    if (isRechazadoFile) {
                        if (hasRechazado && archivoPerteneceAModelos(f.nombre, arrRechazados)) {
                            cardsR += window._renderServerFileCard(f, ot, baseUrl, "rechazados");
                        }
                    } else {
                        if (hasAprobado && archivoPerteneceAModelos(f.nombre, arrAprobados)) {
                            cardsA += window._renderServerFileCard(f, ot, baseUrl, "aprobados");
                        }
                        if (hasRechazado && archivoPerteneceAModelos(f.nombre, arrRechazados)) {
                            cardsR += window._renderServerFileCard(f, ot, baseUrl, "rechazados");
                        }
                    }
                });
            }
            if (sA) sA.innerHTML = cardsA || emptyHtml;
            if (sR) sR.innerHTML = cardsR || emptyHtml;
        })
        .catch(() => {
            if (sA) sA.innerHTML = `<div style="color:#ef4444;font-size:0.8em;grid-column:1/-1;">Error al cargar.</div>`;
            if (sR) sR.innerHTML = `<div style="color:#ef4444;font-size:0.8em;grid-column:1/-1;">Error al cargar.</div>`;
        });
        
    const primerTipo = arrAprobados[0] || arrRechazados[0] || "";
    fetch(`${window.almacenRoutes.getLiberacion}?ot=${encodeURIComponent(ot)}`)
        .then((r) => r.json())
        .then((data) => {
            let dest = data.registros_por_tipo?.[primerTipo]?.destinatario || data.liberacion?.destinatario || "";
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
        el.textContent = input.files.length > 1 ? `${input.files.length} archivo(s)` : input.files[0].name;
        el.style.color = "#10b981";
    }
};

document.addEventListener("DOMContentLoaded", () => {
    document.addEventListener("click", (e) => {
        if (e.target.id === "modalEnviarAlertaLiberacion") window.cerrarModalEnviarAlertaLiberacion();
    });
    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") window.cerrarModalEnviarAlertaLiberacion();
    });
    
    const form = document.getElementById("formEnviarAlertaLiberacion");
    if (form) {
        form.addEventListener("submit", async function (e) {
            e.preventDefault();
            const destinatario = document.getElementById("al-destinatario").value.trim();
            if (!destinatario) {
                almacenToast("El campo Destinatario(s) es obligatorio.", "error");
                return;
            }
            const fecha = document.getElementById("al-fecha").value;
            if (!fecha) {
                almacenToast("La fecha es obligatoria.", "error");
                return;
            }
            
            const requiredFiles = form.querySelectorAll('input[type="file"][required]');
            let missingFiles = [];
            requiredFiles.forEach((inp) => {
                if (!inp.files || inp.files.length === 0) {
                    const parentBlock = inp.closest('div[style*="flex-direction:column"]');
                    const label = parentBlock ? parentBlock.querySelector("label") : null;
                    let labelText = label ? label.textContent.trim().replace(/\s*\*\s*$/, "") : "";
                    if (!labelText) {
                        labelText = inp.name || inp.id;
                    }
                    missingFiles.push(labelText);
                }
            });
            if (missingFiles.length > 0) {
                almacenToast("Por favor, suba los archivos obligatorios: " + missingFiles.join(", "), "error");
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
                const resp = await fetch(window.almacenRoutes.enviarAlertaLiberacion, {
                    method: "POST",
                    headers: {
                        Accept: "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: fd,
                });
                const data = await resp.json();
                if (data.success) {
                    almacenToast(data.message, "success");
                    if (window.ModeloStateMachine) {
                        if (decision === "aprobar") window.ModeloStateMachine.onAprobado(ot);
                        else if (decision === "rechazar") window.ModeloStateMachine.onRechazado(ot);
                    }
                    setTimeout(() => {
                        window.cerrarModalEnviarAlertaLiberacion();
                        window.location.reload();
                    }, 1800);
                } else {
                    almacenToast(data.message || "Error al enviar la alerta.", "error");
                    btn.disabled = false;
                    btn.innerHTML = orig;
                }
            } catch (err) {
                console.error("Error", err);
                almacenToast("Error de conexión al enviar la alerta.", "error");
                btn.disabled = false;
                btn.innerHTML = orig;
            }
        });
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
    
    document.getElementById("fc-ot").value = ot;
    document.getElementById("fc-decision").value = decision;
    document.getElementById("fc-tipo-modelo").value = [...arrAprobados, ...arrRechazados].join(", ");
    document.getElementById("fc-tipos-aprobados").value = JSON.stringify(arrAprobados);
    document.getElementById("fc-tipos-rechazados").value = JSON.stringify(arrRechazados);
    
    const fDate = document.getElementById("fc-fecha");
    if (fDate) fDate.value = "";
    
    const otClean = ot.replace(/_\d{8}_\d{6}_.*/, "");
    let baseUrl = window.baseUrl || window.location.origin + "/";
    if (!baseUrl.endsWith("/")) baseUrl += "/";
    
    let bg, border, btnBg, titleText, promptHtml, btnText;
    if (decision === "aprobar") {
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
    } else if (decision === "rechazar") {
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
    
    const filesContainer = document.getElementById("fc-server-files-container");
    const loadHtml2 = `<div style="text-align:center;color:#64748b;grid-column:1/-1;padding:8px;font-style:italic;font-size:0.8em;">Cargando...</div>`;
    const emptyHtml2 = `<div style="text-align:center;color:#94a3b8;grid-column:1/-1;padding:8px;font-style:italic;font-size:0.8em;">Sin archivos en servidor.</div>`;
    if (filesContainer) filesContainer.innerHTML = loadHtml2;
    
    fetch(`${window.almacenRoutes.archivos}?ot=${encodeURIComponent(ot)}`)
        .then((r) => r.json())
        .then((data) => {
            if (data.existe && data.archivos?.length > 0) {
                const archivoPerteneceAModelos = (nombre, modelosActivos) => {
                    const pl = nombre.toLowerCase();
                    if (pl.includes("_anterior_n")) return false;
                    const todosModelosPosibles = [
                        "candado obturador", "cabeza de soplo", "obturador", "bombillo",
                        "embudo", "corona", "plato", "molde", "fondo", "pistones", "guías", "guias"
                    ];
                    const modelosEncontrados = todosModelosPosibles.filter((m) => pl.includes(m));
                    if (modelosEncontrados.length === 0) return false;
                    const modelosActivosLower = modelosActivos.map((m) => m.toLowerCase().trim().replace(/^modelo\s+/i, ""));
                    return modelosEncontrados.some((m) => modelosActivosLower.includes(m));
                };
                const allRelevantModels = [...arrAprobados, ...arrRechazados];
                const filteredFiles = data.archivos.filter((f) => {
                    const pl = f.nombre.toLowerCase();
                    if (pl.includes("_anterior_n")) return false;
                    const isRechazadoFile = pl.includes("documentos_rechazados") || pl.includes("rechazado") || pl.includes("scar");
                    
                    if (decision === "aprobar") {
                        if (isRechazadoFile) return false;
                        return archivoPerteneceAModelos(f.nombre, arrAprobados);
                    }
                    if (decision === "rechazar") {
                        if (isRechazadoFile) return archivoPerteneceAModelos(f.nombre, arrRechazados);
                        return archivoPerteneceAModelos(f.nombre, arrRechazados);
                    }
                    return archivoPerteneceAModelos(f.nombre, allRelevantModels);
                });
                
                const seenBases = new Set();
                const uniqueFilteredFiles = filteredFiles.filter((f) => {
                    const baseName = (f.nombre.split("/").pop() || f.nombre).toLowerCase();
                    if (seenBases.has(baseName)) return false;
                    seenBases.add(baseName);
                    return true;
                });
                const sectionsHtml = window.generarHtmlCategorizadoArchivos(uniqueFilteredFiles, ot, baseUrl, "calidad");
                if (filesContainer) {
                    filesContainer.innerHTML = sectionsHtml || emptyHtml2;
                }
            } else {
                if (filesContainer) filesContainer.innerHTML = emptyHtml2;
            }
        })
        .catch((err) => {
            console.error(err);
            if (filesContainer) {
                filesContainer.innerHTML = `<div style="text-align:center;color:#ef4444;grid-column:1/-1;padding:8px;font-weight:600;">Error al cargar archivos.</div>`;
            }
        });
        
    const defaultAlmacen = form ? (form.getAttribute("data-email-almacen") || "almacentec@grupoindsaavedra.com") : "almacentec@grupoindsaavedra.com";
    const defaultCalidad = form ? (form.getAttribute("data-email-calidad") || "inspecciontec@grupoindsaavedra.com") : "inspecciontec@grupoindsaavedra.com";

    const dAlmacen = document.getElementById("fc-destinatario");
    if (dAlmacen) dAlmacen.value = defaultAlmacen;

    const dCalidad = document.getElementById("fc-destinatario-calidad");
    if (dCalidad) dCalidad.value = defaultCalidad;

    const dCalidadGroup = dCalidad ? dCalidad.closest(".form-group") : null;
    if (dCalidadGroup) {
        if (decision === "rechazar") {
            dCalidadGroup.style.display = "none";
        } else {
            dCalidadGroup.style.display = "block";
        }
    }
        

        
    modal.classList.add("open");
    document.body.classList.add("modal-open");
};

window.cerrarModalFinalizarCalidad = function () {
    const modal = document.getElementById("modalFinalizarCalidad");
    if (modal) modal.classList.remove("open");
    document.body.classList.remove("modal-open");
};

document.addEventListener("DOMContentLoaded", () => {
    document.addEventListener("click", (e) => {
        if (e.target.id === "modalFinalizarCalidad") window.cerrarModalFinalizarCalidad();
    });
    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") window.cerrarModalFinalizarCalidad();
    });
    
    const fcForm = document.getElementById("formFinalizarCalidad");
    if (fcForm) {
        fcForm.addEventListener("submit", async function (e) {
            e.preventDefault();
            const destinatario = document.getElementById("fc-destinatario").value.trim();
            if (!destinatario) {
                almacenToast("El campo Destinatario(s) es obligatorio.", "error");
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
                const resp = await fetch(window.almacenRoutes.enviarAlertaLiberacion, {
                    method: "POST",
                    headers: {
                        Accept: "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: fd,
                });
                const data = await resp.json();
                if (data.success) {
                    almacenToast(data.message, "success");
                    if (window.ModeloStateMachine) {
                        if (decision === "aprobar") window.ModeloStateMachine.onAprobado(ot);
                        else if (decision === "rechazar") window.ModeloStateMachine.onRechazado(ot);
                    }
                    window.cerrarModalFinalizarCalidad();
                    let baseUrlLocal = window.baseUrl || window.location.origin + "/";
                    if (!baseUrlLocal.endsWith("/")) baseUrlLocal += "/";
                    const otSafe = ot.replace(/'/g, "\\\\'");
                    const buttons = document.querySelectorAll(`button[onclick*="abrirModalFinalizarCalidad('${otSafe}'"]`);
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
                    almacenToast(data.message || "Error al enviar la alerta.", "error");
                    btn.disabled = false;
                    btn.innerHTML = orig;
                }
            } catch (err) {
                console.error("Error", err);
                almacenToast("Error de conexión al enviar la alerta.", "error");
                btn.disabled = false;
                btn.innerHTML = orig;
            }
        });
    }
});


