// ── REVISAR CAMBIOS PENDIENTES ────────────────────────────────────────────────
let currentPendingOt = null;

window.almacenRevisarCambios = function (ot) {
    currentPendingOt = ot;
    const modal = document.getElementById("modalRevisarCambios");
    if (modal) modal.dataset.ot = ot;
    const url = window.almacenRoutes.pendingComparison + "?ot=" + encodeURIComponent(ot);

    fetch(url)
        .then(res => res.json())
        .then(data => {
            if (data.success && data.has_pending) {
                renderizarModalRevisarCambios(data.comparison, data.tipo_cambio, data.es_total, data.affected_clases_count);
                const modal = document.getElementById("modalRevisarCambios");
                if (modal) {
                    modal.dataset.ot = ot;
                    modal.classList.add("open");
                }
                document.body.classList.add("modal-open");
            } else {
                almacenToast(data.message || "No hay cambios pendientes.", data.success ? "success" : "error");
            }
        })
        .catch(err => {
            console.error(err);
            almacenToast("Error al obtener los cambios pendientes.", "error");
        });
};

window.cerrarModalRevisarCambios = function () {
    const modal = document.getElementById("modalRevisarCambios");
    if (modal) modal.classList.remove("open");
    document.body.classList.remove("modal-open");
};

let pendingResolveAction = null;

window.solicitarConfirmacionCambios = function (action, step = 1) {
    if (!currentPendingOt) {
        const revModal = document.getElementById("modalRevisarCambios");
        if (revModal && revModal.dataset.ot) {
            currentPendingOt = revModal.dataset.ot;
        }
    }
    if (!currentPendingOt) {
        console.error("No hay OT pendiente seleccionada.");
        almacenToast("No se ha identificado la Orden de Trabajo.", "error");
        return;
    }
    pendingResolveAction = action;

    const modal = document.getElementById("modalConfirmarAccionCambios");
    const titleEl = document.getElementById("confirm-cambios-title");
    const iconWrapper = document.getElementById("confirm-cambios-icon-wrapper");
    const messageEl = document.getElementById("confirm-cambios-message");
    const btnEjecutar = document.getElementById("btn-ejecutar-resolver-cambios");

    const isRestart = (action === 'reiniciar_completo' || action === 'reiniciar_parcial' || action === 'reiniciar');

    if (isRestart) {
        const esParcial = (action === 'reiniciar_parcial');

        if (step === 1) {
            // Advertencia Inicial (Alerta Leve)
            if (titleEl) titleEl.textContent = esParcial ? "Advertencia: Reinicio de Clase" : "Advertencia: Reinicio Proceso Completo";
            if (titleEl) titleEl.style.color = "#dc2626";
            if (iconWrapper) {
                iconWrapper.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>`;
                iconWrapper.style.background = "#fee2e2";
            }
            if (messageEl) {
                messageEl.textContent = esParcial
                    ? "¿Deseas reiniciar el proceso para la clase afectada? Se eliminarán los avances y documentos registrados para esta clase."
                    : "¿Deseas reiniciar el proceso completo de la OT? Se eliminarán los avances y documentos registrados de todas las clases.";
            }
            if (btnEjecutar) {
                btnEjecutar.textContent = "Sí, Continuar";
                btnEjecutar.style.background = "linear-gradient(135deg, #e11d48 0%, #be123c 100%)";
                btnEjecutar.style.boxShadow = "0 4px 15px rgba(225, 29, 72, 0.4)";
                btnEjecutar.onclick = function () {
                    solicitarConfirmacionCambios(action, 2);
                };
            }
        } else {
            // Confirmación Definitiva (Alerta Grave/Irreversible)
            if (titleEl) titleEl.textContent = esParcial ? "Confirmación Definitiva: Reinicio de Clase" : "Confirmación Definitiva: Reinicio OT";
            if (titleEl) titleEl.style.color = "#991b1b";
            if (iconWrapper) {
                iconWrapper.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#991b1b" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>`;
                iconWrapper.style.background = "#fecdd3";
            }
            if (messageEl) {
                messageEl.innerHTML = `<strong style="color: #991b1b; display: block; margin-bottom: 6px; font-size: 1.1em;">¡ATENCIÓN: ACCIÓN IRREVERSIBLE!</strong> Esta acción NO se puede deshacer. Se eliminarán permanentemente el progreso y registros en Almacén. ¿Confirmas reiniciar definitivamente?`;
            }
            if (btnEjecutar) {
                btnEjecutar.textContent = "Sí, Reiniciar Definitivamente";
                btnEjecutar.style.background = "linear-gradient(135deg, #991b1b 0%, #450a0a 100%)";
                btnEjecutar.style.boxShadow = "0 4px 15px rgba(153, 27, 27, 0.5)";
                btnEjecutar.onclick = function () {
                    const actionToExecute = pendingResolveAction;
                    cerrarModalConfirmarAccionCambios();
                    ejecutarAlmacenResolverCambios(actionToExecute);
                };
            }
        }
    } else {
        // Breve aviso para Reemplazar Dibujos
        if (titleEl) titleEl.textContent = "Confirmar Reemplazo de Dibujos";
        if (titleEl) titleEl.style.color = "#16a34a";
        if (iconWrapper) {
            iconWrapper.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>`;
            iconWrapper.style.background = "#dcfce7";
        }
        if (messageEl) {
            messageEl.textContent = "¿Estás seguro de reemplazar los dibujos en Almacén? Se actualizarán los dibujos conservando el progreso actual de las clases.";
        }
        if (btnEjecutar) {
            btnEjecutar.textContent = "Sí, Reemplazar Dibujos";
            btnEjecutar.style.background = "linear-gradient(135deg, #16a34a 0%, #15803d 100%)";
            btnEjecutar.style.boxShadow = "0 4px 15px rgba(22, 163, 74, 0.4)";
            btnEjecutar.onclick = function () {
                const actionToExecute = pendingResolveAction;
                cerrarModalConfirmarAccionCambios();
                ejecutarAlmacenResolverCambios(actionToExecute);
            };
        }
    }

    if (modal) {
        modal.hidden = false;
        modal.classList.add("open");
    }
};

window.cerrarModalConfirmarAccionCambios = function () {
    const modal = document.getElementById("modalConfirmarAccionCambios");
    if (modal) {
        modal.hidden = true;
        modal.classList.remove("open");
    }
    pendingResolveAction = null;
};

window.almacenResolverCambios = function (action) {
    solicitarConfirmacionCambios(action);
};

function ejecutarAlmacenResolverCambios(action) {
    if (!currentPendingOt) {
        const revModal = document.getElementById("modalRevisarCambios");
        if (revModal && revModal.dataset.ot) {
            currentPendingOt = revModal.dataset.ot;
        }
    }
    if (!currentPendingOt || !action) {
        console.error("Parámetros incompletos:", { ot: currentPendingOt, action: action });
        almacenToast("Parámetros incompletos para resolver la acción.", "error");
        return;
    }

    fetch(window.almacenRoutes.resolveChanges, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
        },
        body: JSON.stringify({ ot: currentPendingOt, action: action })
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                almacenToast(data.message, "success");
                cerrarModalRevisarCambios();
                setTimeout(() => window.location.reload(), 1500);
            } else {
                almacenToast(data.message || "Error al resolver los cambios.", "error");
            }
        })
        .catch(err => {
            console.error(err);
            almacenToast("Error de conexión.", "error");
        });
}

function renderizarModalRevisarCambios(comparisonData, tipoCambio, esTotal, affectedCount) {
    const container = document.getElementById('revisar-cambios-container');
    const btnReiniciar = document.getElementById('btn-resolver-reiniciar');
    const textReiniciar = document.getElementById('text-btn-reiniciar');
    const btnMantener = document.getElementById('btn-resolver-mantener');

    const affectedClasses = comparisonData.map(item => item.clase).join(', ');
    const baseUrl = window.baseUrl || window.location.origin + "/";
    const pdfViewShadow = baseUrl.endsWith('/') ? baseUrl + 'images/pdf-view-shadow.png' : baseUrl + '/images/pdf-view-shadow.png';
    const pdfView = baseUrl.endsWith('/') ? baseUrl + 'images/pdf-view.png' : baseUrl + '/images/pdf-view.png';

    const isAdicion = tipoCambio === 'adicion';

    if (btnReiniciar) {
        if (esTotal) {
            if (textReiniciar) textReiniciar.textContent = "Reiniciar Proceso Completo";
            btnReiniciar.setAttribute("onclick", "solicitarConfirmacionCambios('reiniciar_completo')");
        } else {
            const numClases = affectedCount || comparisonData.length;
            const text = numClases === 1
                ? "Reiniciar Clase"
                : "Reiniciar Clases Afectadas";
            if (textReiniciar) textReiniciar.textContent = text;
            btnReiniciar.setAttribute("onclick", "solicitarConfirmacionCambios('reiniciar_parcial')");
        }
    }

    if (btnMantener) {
        btnMantener.setAttribute("onclick", "solicitarConfirmacionCambios('mantener')");
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
        const afectados = item.afectados || [];

        let subHtml = '';

        if (itemIsAdicion) {
            // CASO ADICIÓN: Mostrar solo la columna de Dibujos Nuevos Agregados
            const agregadosList = (item.agregados && item.agregados.length > 0) ? item.agregados : nuevos;
            subHtml += `
                <div class="alm-display-flex alm-flex-direction-column alm-gap-10px">
                    <h5 class="alm-color-059669 alm-margin-0-0-10px-0" style="font-weight: 700;">Nuevos Dibujos Agregados</h5>
                    ${agregadosList.map((n, index) => `
                        <div class="dibujos-file-card card-dibujo" style="animation-delay: ${index * 0.05}s; border: 2px solid #10b981; background-color: #ecfdf5; border-left: 5px solid #10b981;">
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
                    `).join('')}
                </div>
            `;
        } else {
            // CASO REEMPLAZO: Mostrar 2 columnas (Actuales en Almacén vs Nuevos de Dibujos de Fundición)
            let viejosAMostrar = [];
            const nuevosProcesados = nuevos.map((n, index) => {
                const exactMatch = viejos.some(v => v.nombre.toLowerCase() === n.nombre.toLowerCase());
                const posMatch = index < viejos.length;
                const isReemplazo = exactMatch || posMatch;
                
                let matchedViejo = viejos.find(v => v.nombre.toLowerCase() === n.nombre.toLowerCase());
                if (!matchedViejo && index < viejos.length) {
                    matchedViejo = viejos[index];
                }
                if (matchedViejo && !viejosAMostrar.includes(matchedViejo)) {
                    viejosAMostrar.push(matchedViejo);
                }
                
                return {
                    ...n,
                    isReemplazo: isReemplazo,
                    theme: 'green'
                };
            });

            subHtml += `
                <div class="alm-display-flex alm-gap-20px">
                    <!-- Viejos (En Almacén) -->
                    <div class="alm-flex-1">
                        <h5 class="alm-color-64748b alm-margin-0-0-10px-0" style="font-weight: 700;">Actuales (En Almacén)</h5>
                        <div class="alm-display-flex alm-flex-direction-column alm-gap-10px">
                            ${viejosAMostrar.length > 0 ? viejosAMostrar.map((v, index) => `
                                <div class="dibujos-file-card card-dibujo" style="animation-delay: ${index * 0.05}s; border: 2px solid #0284c7; background-color: #f0f9ff; border-left: 5px solid #0284c7;">
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
                            `).join('') : '<span class="alm-text-sm-gray">Sin archivos</span>'}
                        </div>
                    </div>
                    <!-- Nuevos (De Dibujos de Fundición) -->
                    <div class="alm-flex-1">
                        <h5 class="alm-color-059669 alm-margin-0-0-10px-0" style="font-weight: 700;">Nuevos (De Programación)</h5>
                        <div class="alm-display-flex alm-flex-direction-column alm-gap-10px">
                            ${nuevosProcesados.length > 0 ? nuevosProcesados.map((n, index) => {
                                const isReemplazo = n.isReemplazo;
                                const borderColor = '#10b981';
                                const bgColor = '#ecfdf5';
                                const badgeBg = '#d1fae5';
                                const badgeColor = '#047857';
                                const badgeBorder = '#a7f3d0';
                                const badgeText = isReemplazo ? 'DIBUJO REEMPLAZADO' : 'NUEVO DIBUJO AGREGADO';
                                const btnColor = '#059669';
                                const textColor = '#047857';

                                return `
                                <div class="dibujos-file-card card-dibujo" style="animation-delay: ${index * 0.05}s; border: 2px solid ${borderColor}; background-color: ${bgColor}; border-left: 5px solid ${borderColor};">
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
                            }).join('') : '<span class="alm-text-sm-gray">Sin archivos</span>'}
                        </div>
                    </div>
                </div>
            `;
        }

        // Agregar sección de documentos afectados por reiniciar la etapa
        let afectadosHtml = '';
        if (afectados.length > 0) {
            afectadosHtml = `
                <div class="alm-margin-top-15px" style="border-top: 1px dashed #cbd5e1; padding-top: 15px;">
                    <h5 class="alm-color-b91c1c alm-margin-0-0-10px-0" style="font-weight: 700; font-size: 0.9em; display: flex; align-items: center; gap: 6px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color: #e11d48;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                        Otros archivos de proceso afectados (se eliminarán si seleccionas Reiniciar Proceso):
                    </h5>
                    <div class="alm-display-flex alm-flex-direction-column alm-gap-6px">
                        ${afectados.map((a, index) => `
                            <div style="font-size: 0.85rem; padding: 6px 12px; background: #fff1f2; border: 1px solid #fecdd3; border-radius: 6px; color: #9f1239; display: flex; align-items: center; justify-content: space-between;">
                                <span style="font-weight: 500;">${a.nombre}</span>
                                <button type="button" class="btn-dibujos btn-dibujos-sm" style="background-color: #e11d48; color: white; padding: 2px 8px; font-size: 0.75rem;" onclick="window.open('${a.url}', '_blank')">Ver</button>
                            </div>
                        `).join('')}
                    </div>
                </div>
            `;
        }

        html += `
        <div class="alm-background-ffffff alm-border-radius-14px alm-padding-20px alm-margin-bottom-20px" style="border: 2px solid #cbd5e1; box-shadow: 0 4px 15px rgba(0,0,0,0.06);">
            <h4 class="alm-margin-top-0 alm-margin-bottom-15px alm-color-0f172a alm-font-size-1-15rem alm-border-bottom-2px-solid-0369a1 alm-padding-bottom-8px">
                Clase: <strong style="text-transform: capitalize; color: #0369a1;">${item.clase}</strong>
            </h4>
            ${subHtml}
            ${afectadosHtml}
        </div>
        `;
    });

    container.innerHTML = html;
}


// Expose to window for global access
window.renderizarModalRevisarCambios = renderizarModalRevisarCambios;
window.currentPendingOt = currentPendingOt;
