// ── SCAR MODAL LOGIC & ACTIONS ──

let scarFotosSelectedFiles = [];
let scarOtrosSelectedFiles = [];
let envScarSelectedFiles = [];

window.scarFotosSelectedFiles = scarFotosSelectedFiles;
window.scarOtrosSelectedFiles = scarOtrosSelectedFiles;
window.envScarSelectedFiles = envScarSelectedFiles;

window.removeScarFotoAttachment = function (index) {
    scarFotosSelectedFiles.splice(index, 1);
    renderScarFotosBadges();
};

window.removeScarOtroAttachment = function (index) {
    scarOtrosSelectedFiles.splice(index, 1);
    renderScarOtrosBadges();
};

window.removeEnvScarAttachment = function (index) {
    envScarSelectedFiles.splice(index, 1);
    renderEnvScarBadges();
};

function renderScarFotosBadges() {
    window.renderFileCards(
        "scar-fotos-list",
        scarFotosSelectedFiles,
        "window.removeScarFotoAttachment",
        "#dc2626"
    );
}

function renderScarOtrosBadges() {
    window.renderFileCards(
        "scar-otro-archivos-list",
        scarOtrosSelectedFiles,
        "window.removeScarOtroAttachment",
        "#ea580c"
    );
}

function renderEnvScarBadges() {
    if (typeof window.renderFileCards === "function") {
        window.renderFileCards(
            "env-scar-archivos-adicionales-list",
            envScarSelectedFiles,
            "window.removeEnvScarAttachment",
            "#0369a1"
        );
    }
}

window.renderScarFotosBadges = renderScarFotosBadges;
window.renderScarOtrosBadges = renderScarOtrosBadges;
window.renderEnvScarBadges = renderEnvScarBadges;

window.abrirModalScar = function (ot, tipoModelo, motivoRechazo) {
    const modal = document.getElementById("modalScar");
    if (!modal) return;
    const formEl = document.getElementById("formScar");
    if (formEl) formEl.reset();
    
    scarFotosSelectedFiles = [];
    scarOtrosSelectedFiles = [];
    window.scarFotosSelectedFiles = scarFotosSelectedFiles;
    window.scarOtrosSelectedFiles = scarOtrosSelectedFiles;
    renderScarFotosBadges();
    renderScarOtrosBadges();
    
    let otNumber = "";
    let molduraName = "";
    const cleanOt = ot
        .replace(/_[rR]?\d{8}_\d{6}_.*/, "")
        .replace(/_[rR]?\d+$/, "");
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
    const otInput = document.getElementById("scar-ot");
    if (otInput) otInput.value = ot;
    const otDisplay = document.getElementById("scar-ot-display");
    if (otDisplay) otDisplay.textContent = cleanOt;
    const molduraInput = document.getElementById("scar-nombre-moldura");
    if (molduraInput) molduraInput.value = molduraName;
    const codigoInput = document.getElementById("scar-codigo-modelo");
    if (codigoInput) {
        let prefix = "F";
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
                else if (tLow.includes("pistones"))
                    prefix = "P";
                else if (tLow.includes("guías") || tLow.includes("guias"))
                    prefix = "G";
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
    const clienteEl = document.getElementById("scar-cliente-empresa");
    if (clienteEl) clienteEl.value = "Industrial Saavedra";
    const areaEl = document.getElementById("scar-area-solicitante");
    if (areaEl) areaEl.value = "Calidad";
    const provEl = document.getElementById("scar-proveedor");
    if (provEl) provEl.value = "SS Metal Foundry, S. de R.L. de C.V.";
    const defaultChkDibujos = document.getElementById("scar-evidencia-dibujos");
    if (defaultChkDibujos) defaultChkDibujos.checked = true;
    const defaultChkAyudas = document.getElementById("scar-evidencia-ayudas");
    if (defaultChkAyudas) defaultChkAyudas.checked = true;
    
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
                                "alm-display-none",
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
                                "alm-display-none",
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
                                "alm-display-none",
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
        
    if (typeof window.cargarEvidenciasScarServer === 'function') {
        window.cargarEvidenciasScarServer(ot, tipoModelo);
    }
    modal.classList.add("open");
    document.body.classList.add("modal-open");
};

window.cerrarModalScar = function () {
    const modal = document.getElementById("modalScar");
    if (modal) modal.classList.remove("open");
    document.body.classList.remove("modal-open");
};

window.eliminarArchivoServidorCategorizado = function (ot, fileNombre, tipo) {
    const filenameOnly = fileNombre.split("/").pop();
    if (
        !confirm(
            `¿Estás seguro de eliminar el archivo "${filenameOnly}" del servidor?`,
        )
    ) {
        return;
    }
    const formData = new FormData();
    formData.append("ot", ot);
    formData.append("archivo", fileNombre);
    formData.append("tipo", tipo || "otro");
    
    const lower = fileNombre.toLowerCase();
    if (
        lower.includes("documentos_rechazados") ||
        lower.includes("rechazado") ||
        lower.includes("scar")
    ) {
        formData.append("origin", "rechazado");
    } else if (
        lower.includes("documentos_aprobados") ||
        lower.includes("aprobado") ||
        lower.includes("confirmacion")
    ) {
        formData.append("origin", "aprobado");
    }
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");
    if (token) formData.append("_token", token);
    
    fetch(window.almacenRoutes.deleteFile, {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": token || "",
        },
        body: formData,
    })
        .then((res) => res.json())
        .then((data) => {
            if (data.success || data.message) {
                almacenToast("Archivo eliminado del servidor.", "success");
                const openModals = document.querySelectorAll(".alm-modal.open");
                if (openModals.length > 0) {
                    openModals.forEach((m) => {
                        if (
                            m.id === "modalScar" &&
                            typeof window.cargarEvidenciasScarServer === "function"
                        ) {
                            const otVal = document.getElementById("scar-ot")?.value;
                            const tipoVal = document.getElementById("scar-tipo")?.value;
                            if (otVal) window.cargarEvidenciasScarServer(otVal, tipoVal);
                        } else if (
                            m.id === "modalEnviarScar" &&
                            typeof window.abrirModalEnviarScar === "function"
                        ) {
                            const otVal = document.getElementById("env-scar-ot")?.value;
                            if (otVal) window.abrirModalEnviarScar(otVal);
                        }
                    });
                } else {
                    setTimeout(() => window.location.reload(), 800);
                }
            } else {
                alert(data.error || data.message || "Error al eliminar el archivo.");
            }
        })
        .catch((err) => {
            console.error(err);
            alert("Error de red al intentar eliminar el archivo.");
        });
};

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
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "",
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

window.abrirModalEnviarScar = function (ot) {
    const modal = document.getElementById("modalEnviarScar");
    if (!modal) return;
    const inputOt = document.getElementById("env-scar-ot");
    if (inputOt) inputOt.value = ot;
    const subtitle = document.getElementById("env-scar-modal-subtitle");
    if (subtitle) {
        subtitle.textContent = `OT: ${ot.replace(/_[rR]?\d{8}_\d{6}_.*/, "")}`;
    }
    const form = document.getElementById("formEnviarScar");
    if (form) form.reset();
    
    const filesContainer = document.getElementById("env-scar-server-files-container");
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
    
    fetch(`${window.almacenRoutes.getScar}?ot=${encodeURIComponent(ot)}`)
        .then((res) => res.json())
        .then((data) => {
            if (data.success && data.scar) {
                const s = data.scar;
                const fcInput = document.getElementById("env-scar-fecha-compromiso");
                if (fcInput && s.fecha_compromiso) {
                    fcInput.value = s.fecha_compromiso.split(" ")[0].split("T")[0];
                }
            }
        })
        .catch((err) => console.error(err));
        
    fetch(`${window.almacenRoutes.archivos}?ot=${encodeURIComponent(ot)}`)
        .then((res) => res.json())
        .then((data) => {
            if (data.existe && data.archivos && data.archivos.length > 0) {
                let baseUrl = window.baseUrl || window.location.origin + "/";
                if (!baseUrl.endsWith("/")) baseUrl += "/";
                const sectionsHtml = window.generarHtmlCategorizadoArchivos(
                    data.archivos,
                    ot,
                    baseUrl,
                    "scar"
                );
                if (filesContainer) {
                    filesContainer.innerHTML = sectionsHtml || `<div style="text-align: center; color: #64748b; padding: 15px; font-style: italic;">No se encontraron archivos en el servidor para esta OT.</div>`;
                }
            } else {
                if (filesContainer) {
                    filesContainer.innerHTML = `<div style="text-align: center; color: #64748b; padding: 15px; font-style: italic;">No se encontraron archivos en el servidor para esta OT.</div>`;
                }
            }
        })
        .catch((err) => {
            console.error(err);
            if (filesContainer) {
                filesContainer.innerHTML = `<div style="text-align: center; color: #ef4444; padding: 15px; font-weight: 600;">Error al cargar la lista de archivos.</div>`;
            }
        });
};

window.cerrarModalEnviarScar = function () {
    const modal = document.getElementById("modalEnviarScar");
    if (modal) modal.classList.remove("open");
    document.body.classList.remove("modal-open");
};

document.addEventListener("DOMContentLoaded", () => {
    // Setup file input listeners for SCAR modal
    const scarFotosInput = document.getElementById("scar-fotos");
    if (scarFotosInput) {
        scarFotosInput.addEventListener("change", function (e) {
            Array.from(this.files).forEach((file) => {
                scarFotosSelectedFiles.push(file);
            });
            window.renderScarFotosBadges();
            this.value = "";
        });
    }

    const scarOtroArchivosInput = document.getElementById("scar-otro-archivos");
    if (scarOtroArchivosInput) {
        scarOtroArchivosInput.addEventListener("change", function (e) {
            Array.from(this.files).forEach((file) => {
                scarOtrosSelectedFiles.push(file);
            });
            window.renderScarOtrosBadges();
            this.value = "";
        });
    }

    const envScarAdicionalesInput = document.getElementById("env-scar-archivos-adicionales");
    if (envScarAdicionalesInput) {
        envScarAdicionalesInput.addEventListener("change", function (e) {
            Array.from(this.files).forEach((file) => {
                envScarSelectedFiles.push(file);
            });
            window.renderEnvScarBadges();
            this.value = "";
        });
    }

    const formEnvScar = document.getElementById("formEnviarScar");
    if (formEnvScar) {
        formEnvScar.addEventListener("submit", function (e) {
            e.preventDefault();
            const ot = document.getElementById("env-scar-ot").value;
            const fechaCompromiso = document.getElementById("env-scar-fecha-compromiso").value;
            const pdfFirmado = document.getElementById("env-scar-pdf-firmado").files[0];
            if (!fechaCompromiso) {
                mostrarToast("Por favor, indica la fecha compromiso.", true);
                return;
            }
            if (!pdfFirmado) {
                mostrarToast("Por favor, sube el SCAR firmado físicamente.", true);
                return;
            }
            const btn = this.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="alm-spinner" style="display:inline-block; border-top-color:#ffffff; width:15px; height:15px; margin-right:8px; vertical-align:middle;"></span> Enviando alerta...';
            
            const formData = new FormData(this);
            formData.delete("archivos_adicionales[]");
            envScarSelectedFiles.forEach((file) => {
                formData.append("archivos_adicionales[]", file);
            });
            
            fetch(window.almacenRoutes.sendScarAlert, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "",
                },
                body: formData,
            })
                .then((res) => res.json())
                .then((data) => {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                    if (data.success) {
                        mostrarToast(data.message || "Alerta SCAR firmada enviada con éxito.");
                        window.cerrarModalEnviarScar();
                        if (window.ModeloStateMachine) {
                            window.ModeloStateMachine.onCorreoEnviado(ot);
                        }
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        mostrarToast(data.message || "Error al enviar alerta SCAR.", true);
                    }
                })
                .catch((err) => {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                    console.error(err);
                    mostrarToast("Error al enviar la solicitud.", true);
                });
        });
    }
});
