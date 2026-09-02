// ── REVISAR CAMBIOS PENDIENTES (Calidad) ────────────────────────────────────
let currentPendingOt = null;

window.almacenRevisarCambios = function (ot) {
    currentPendingOt = ot;
    const url = window.almacenRoutes.pendingComparison + "?ot=" + encodeURIComponent(ot);

    fetch(url)
        .then(res => res.json())
        .then(data => {
            if (data.success && data.has_pending) {
                renderizarModalRevisarCambios(data.comparison, data.tipo_cambio, data.es_total, data.affected_clases_count);
                const modal = document.getElementById("modalRevisarCambios");
                modal.classList.add("open");
                document.body.classList.add("modal-open");
            } else {
                if (typeof almacenToast === "function") {
                    almacenToast(data.message || "No hay cambios pendientes.", data.success ? "success" : "error");
                } else {
                    alert(data.message || "No hay cambios pendientes.");
                }
            }
        })
        .catch(err => {
            console.error(err);
            if (typeof almacenToast === "function") {
                almacenToast("Error al obtener los cambios pendientes.", "error");
            } else {
                alert("Error al obtener los cambios pendientes.");
            }
        });
};

window.cerrarModalRevisarCambios = function () {
    const modal = document.getElementById("modalRevisarCambios");
    modal.classList.remove("open");
    document.body.classList.remove("modal-open");
    currentPendingOt = null;
};

window.almacenResolverCambios = function (action) {
    if (!currentPendingOt) {
        const revModal = document.getElementById("modalRevisarCambios");
        if (revModal && revModal.dataset.ot) {
            currentPendingOt = revModal.dataset.ot;
        }
    }
    if (!currentPendingOt) return;

    let msg = "";
    if (action === "reiniciar_completo") {
        msg =
            "¿Estás seguro de reiniciar el proceso completo de toda la OT? Se borrarán los avances y documentos de todas las clases.";
    } else if (action === "reiniciar_parcial" || action === "reiniciar") {
        msg =
            "¿Estás seguro de reiniciar el proceso para la(s) clase(s) afectada(s)? Se borrarán únicamente los avances y documentos de esta(s) clase(s).";
    } else {
        msg = "¿Estás seguro de mantener el proceso? Se actualizarán los dibujos conservando el avance actual.";
    }

    if (!confirm(msg)) return;

    fetch(window.almacenRoutes.resolveChanges, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector("meta[name=\"csrf-token\"]").getAttribute("content"),
        },
        body: JSON.stringify({ ot: currentPendingOt, action: action }),
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (typeof almacenToast === "function") {
                    almacenToast(data.message, "success");
                } else {
                    alert(data.message);
                }
                cerrarModalRevisarCambios();
                setTimeout(() => window.location.reload(), 1500);
            } else {
                if (typeof almacenToast === "function") {
                    almacenToast(data.message || "Error al resolver los cambios.", "error");
                } else {
                    alert(data.message || "Error al resolver los cambios.");
                }
            }
        })
        .catch(err => {
            console.error(err);
            if (typeof almacenToast === "function") {
                almacenToast("Error de conexión.", "error");
            } else {
                alert("Error de conexión.");
            }
        });
};

function renderizarModalRevisarCambios(comparisonData, tipoCambio, esTotal, affectedCount) {
    const container = document.getElementById("revisar-cambios-container");
    const btnReiniciar = document.getElementById("btn-resolver-reiniciar");
    const btnMantener = document.getElementById("btn-resolver-mantener");

    const affectedClasses = comparisonData.map(item => item.clase).join(", ");
    const baseUrl = window.baseUrl || window.location.origin + "/";
    const pdfViewShadow = baseUrl.endsWith("/")
        ? baseUrl + "images/pdf-view-shadow.png"
        : baseUrl + "/images/pdf-view-shadow.png";
    const pdfView = baseUrl.endsWith("/") ? baseUrl + "images/pdf-view.png" : baseUrl + "/images/pdf-view.png";

    const isAdicion = tipoCambio === "adicion";

    if (btnReiniciar) {
        if (esTotal) {
            btnReiniciar.textContent = "Reiniciar Proceso Completo de la OT";
            btnReiniciar.setAttribute("onclick", "almacenResolverCambios('reiniciar_completo')");
        } else {
            const numClases = affectedCount || comparisonData.length;
            btnReiniciar.textContent = numClases === 1
                ? "Reiniciar Proceso Completo para esta Clase"
                : "Reiniciar Proceso Completo para Clases Afectadas";
            btnReiniciar.setAttribute("onclick", "almacenResolverCambios('reiniciar_parcial')");
        }
    }

    if (btnMantener) {
        btnMantener.textContent = isAdicion ? "Solo Agregar Dibujos" : "Solo Reemplazar Archivos";
    }

    let alertHtml = isAdicion
        ? `<strong>¡Atención!</strong> Se agregaron nuevos Dibujos de Fundición para <strong>${affectedClasses}</strong>. <br><br>¿Deseas regresar el proceso desde el inicio o solo agregamos los dibujos nuevos al proceso?`
        : `<strong>¡Atención!</strong> Se registraron cambios en Dibujos de Fundición y estos afectan al proceso de <strong>${affectedClasses}</strong>. <br><br>¿Deseas regresar el proceso desde el inicio o solo cambiamos los dibujos viejos por los nuevos?`;

    let html = `
                        <div class="alm-alert alm-alert-warning alm-margin-bottom-20px alm-padding-15px alm-border-radius-8px" style="background-color: #fffbeb; border-left: 4px solid #f59e0b; color: #b45309;">
                            ${alertHtml}
                        </div>
                    `;

    comparisonData.forEach(item => {
        const itemIsAdicion = item.es_adicion || isAdicion;
        const viejos = item.viejos || [];
        const nuevos = item.nuevos || [];

        if (itemIsAdicion) {
            const agregadosList = (item.agregados && item.agregados.length > 0) ? item.agregados : nuevos;
            html += `
                            <div class="alm-background-ffffff alm-border-radius-14px alm-padding-20px alm-margin-bottom-20px" style="border: 2px solid #cbd5e1; box-shadow: 0 4px 15px rgba(0,0,0,0.06);">
                                <h4 class="alm-margin-top-0 alm-margin-bottom-15px alm-color-0f172a alm-font-size-1-15rem alm-border-bottom-2px-solid-0369a1 alm-padding-bottom-8px">
                                    Clase: <strong style="text-transform: capitalize; color: #0369a1;">${item.clase}</strong> <span style="font-size: 0.8em; color: #059669; font-weight: 700;">(Nuevo Dibujo Agregado)</span>
                                </h4>
                                <div class="alm-display-flex alm-flex-direction-column alm-gap-10px">
                                    <h5 class="alm-color-059669 alm-margin-0-0-10px-0" style="font-weight: 700;">Nuevos Dibujos Agregados</h5>
                                    ${agregadosList.map((n, index) => `
                                        <div class="dibujos-file-card card-dibujo" style="animation-delay: ${index * 0.05
                }s; border: 2px solid #10b981; background-color: #ecfdf5; border-left: 5px solid #10b981;">
                                            <div class="file-icon-wrapper alm-cursor-pointer" title="Abrir PDF">
                                                <img src="${pdfViewShadow}" class="file-icon icon-default">
                                                <img src="${pdfView}" class="file-icon icon-hover">
                                            </div>
                                            <div class="file-name alm-cursor-pointer" title="Abrir PDF" onclick="window.open('${n.url}', '_blank')">
                                                <div style="margin-bottom: 3px;">
                                                    <span style="font-size: 0.72em; font-weight: 700; background: #d1fae5; color: #047857; padding: 2px 8px; border-radius: 4px; border: 1px solid #a7f3d0;">NUEVO DIBUJO AGREGADO</span>
                                                </div>
                                                <strong>${n.nombre}</strong>
                                            </div>
                                            <div class="file-actions alm-flex-gap-5">
                                                <button class="btn-dibujos btn-dibujos-sm btn-ver" style="background-color: #059669; color: white;" onclick="window.open('${n.url}', '_blank')">Ver</button>
                                            </div>
                                        </div>
                                    `).join("")
                }
                                </div>
                            </div>
                            `;
        } else {
            const nuevosProcesados = nuevos.map((n, index) => {
                const exactMatch = viejos.some(v => v.nombre.toLowerCase() === n.nombre.toLowerCase());
                const posMatch = index < viejos.length;
                const isReemplazo = exactMatch || posMatch;
                return {
                    ...n,
                    isReemplazo: isReemplazo,
                    theme: "green",
                };
            });

            html += `
                            <div class="alm-background-ffffff alm-border-radius-14px alm-padding-20px alm-margin-bottom-20px" style="border: 2px solid #cbd5e1; box-shadow: 0 4px 15px rgba(0,0,0,0.06);">
                                <h4 class="alm-margin-top-0 alm-margin-bottom-15px alm-color-0f172a alm-font-size-1-15rem alm-border-bottom-2px-solid-0369a1 alm-padding-bottom-8px">
                                    Clase: <strong style="text-transform: capitalize; color: #0369a1;">${item.clase}</strong>
                                </h4>
                                <div class="alm-display-flex alm-gap-20px">
                                    <!-- Viejos (En Almacén) -->
                                    <div class="alm-flex-1">
                                        <h5 class="alm-color-64748b alm-margin-0-0-10px-0" style="font-weight: 700;">Actuales (En Almacén)</h5>
                                        <div class="alm-display-flex alm-flex-direction-column alm-gap-10px">
                                            ${viejos.length > 0
                    ? viejos.map((v, index) => `
                                                <div class="dibujos-file-card card-dibujo" style="animation-delay: ${index * 0.05
                        }s; border: 2px solid #0284c7; background-color: #f0f9ff; border-left: 5px solid #0284c7;">
                                                    <div class="file-icon-wrapper alm-cursor-pointer" title="Abrir PDF">
                                                        <img src="${pdfViewShadow}" class="file-icon icon-default">
                                                        <img src="${pdfView}" class="file-icon icon-hover">
                                                    </div>
                                                    <div class="file-name alm-cursor-pointer" title="Abrir PDF" onclick="window.open('${v.url}', '_blank')">
                                                        <div style="margin-bottom: 3px;">
                                                            <span style="font-size: 0.72em; font-weight: 700; background: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 4px; border: 1px solid #bae6fd;">DIBUJO EN ALMACÉN</span>
                                                        </div>
                                                        <strong>${v.nombre}</strong>
                                                    </div>
                                                    <div class="file-actions alm-flex-gap-5">
                                                        <button class="btn-dibujos btn-dibujos-sm btn-ver" style="background-color: #0284c7; color: white;" onclick="window.open('${v.url}', '_blank')">Ver</button>
                                                    </div>
                                                </div>
                                            `).join("")
                    : "<span class=\"alm-text-sm-gray\">Sin archivos</span>"
                }
                                        </div>
                                    </div>
                                    <!-- Nuevos (De Dibujos de Fundición) -->
                                    <div class="alm-flex-1">
                                        <h5 class="alm-color-059669 alm-margin-0-0-10px-0" style="font-weight: 700;">Nuevos (De Programación)</h5>
                                        <div class="alm-display-flex alm-flex-direction-column alm-gap-10px">
                                            ${nuevosProcesados.length > 0
                    ? nuevosProcesados.map((n, index) => {
                        const isReemplazo = n.isReemplazo;
                        const borderColor = "#10b981";
                        const bgColor = "#ecfdf5";
                        const badgeBg = "#d1fae5";
                        const badgeColor = "#047857";
                        const badgeBorder = "#a7f3d0";
                        const badgeText = isReemplazo ? "DIBUJO REEMPLAZADO" : "NUEVO DIBUJO AGREGADO";
                        const btnColor = "#059669";
                        const textColor = "#047857";

                        return `
                                                <div class="dibujos-file-card card-dibujo" style="animation-delay: ${index * 0.05
                            }s; border: 2px solid ${borderColor}; background-color: ${bgColor}; border-left: 5px solid ${borderColor};">
                                                    <div class="file-icon-wrapper alm-cursor-pointer" title="Abrir PDF">
                                                        <img src="${pdfViewShadow}" class="file-icon icon-default">
                                                        <img src="${pdfView}" class="file-icon icon-hover">
                                                    </div>
                                                    <div class="file-name alm-cursor-pointer" title="Abrir PDF" onclick="window.open('${n.url}', '_blank')">
                                                        <div style="margin-bottom: 3px;">
                                                            <span style="font-size: 0.72em; font-weight: 700; background: ${badgeBg}; color: ${badgeColor}; padding: 2px 8px; border-radius: 4px; border: 1px solid ${badgeBorder};">${badgeText}</span>
                                                        </div>
                                                        <strong style="color: ${textColor};">${n.nombre}</strong>
                                                    </div>
                                                    <div class="file-actions alm-flex-gap-5">
                                                        <button class="btn-dibujos btn-dibujos-sm btn-ver" style="background-color: ${btnColor}; color: white;" onclick="window.open('${n.url}', '_blank')">Ver</button>
                                                    </div>
                                                </div>
                                                `;
                    }).join("")
                    : "<span class=\"alm-text-sm-gray\">Sin archivos</span>"
                }
                                        </div>
                                    </div>
                                </div>
                            </div>
                            `;
        }
    });

    container.innerHTML = html;
}

// renderizarModalRevisarCambios se llama internamente, no necesita exposición
