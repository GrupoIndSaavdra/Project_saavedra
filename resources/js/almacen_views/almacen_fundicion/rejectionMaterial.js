// ── REJECTION MATERIAL & DISMISSED STAGE LOGIC ──

window.cargarInputsRechazados = function (ot, files, clasesRechazadas) {
    const dynamicRechInputs = document.getElementById("mgv-rechazados-inputs");
    if (!dynamicRechInputs) return;
    dynamicRechInputs.innerHTML = "";

    // ℹ️ Banner de instrucciones (siempre visible, fijo en la parte superior)
    if (!document.getElementById("mgv-rech-banner-element")) {
        dynamicRechInputs.insertAdjacentHTML('beforebegin', `
            <div id="mgv-rech-banner-element" style="border-radius: 10px; overflow: hidden; border: 2px solid #dc2626; box-shadow: 0 4px 10px rgba(220, 38, 38, 0.08); flex-shrink: 0; margin-bottom: 15px;">
                <div style="background-color: #fee2e2; padding: 6px 14px; border-bottom: 1px solid #fecaca;">
                    <strong style="color: #991b1b; font-size: 0.88em; display: flex; align-items: center; gap: 6px;">
                        <img src="${getBaseUrl()}images/info-icon.png" style="width: 22px; height: 22px; object-fit: contain;">
                        Verifica estos puntos antes de subir
                    </strong>
                </div>
                <div style="padding: 10px 12px; display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; background: #fff;">
                    <div style="display: flex; flex-direction: column; align-items: center; gap: 6px; padding: 10px 8px; background: #fef2f2; border-radius: 8px; border: 1px solid #fee2e2; text-align: center;">
                        <img src="${getBaseUrl()}images/firma-icon.png" style="width: 36px; height: 36px; object-fit: contain;">
                        <span style="color: #b91c1c; font-size: 0.82em; line-height: 1.3; font-weight: 500;"><strong>PDF Firmado</strong><br>Documento autorizado</span>
                    </div>
                    <div style="display: flex; flex-direction: column; align-items: center; gap: 6px; padding: 10px 8px; background: #fef2f2; border-radius: 8px; border: 1px solid #fee2e2; text-align: center;">
                        <img src="${getBaseUrl()}images/Nombre_Correcto.png" style="width: 36px; height: 36px; object-fit: contain;">
                        <span style="color: #b91c1c; font-size: 0.82em; line-height: 1.3; font-weight: 500;"><strong>Nombre Correcto</strong><br>F_CCL_RDM/SCAR_[clase].pdf</span>
                    </div>
                    <div style="display: flex; flex-direction: column; align-items: center; gap: 6px; padding: 10px 8px; background: #fef2f2; border-radius: 8px; border: 1px solid #fee2e2; text-align: center;">
                        <img src="${getBaseUrl()}images/claridad-icon.png" style="width: 36px; height: 36px; object-fit: contain;">
                        <span style="color: #b91c1c; font-size: 0.82em; line-height: 1.3; font-weight: 500;"><strong>100% Legible</strong><br>Sin tachaduras ni borrones</span>
                    </div>
                    <div style="display: flex; flex-direction: column; align-items: center; gap: 6px; padding: 10px 8px; background: #fef2f2; border-radius: 8px; border: 1px solid #fee2e2; text-align: center;">
                        <img src="${getBaseUrl()}images/SCAR_RDM_Correctos.png" style="width: 36px; height: 36px; object-fit: contain;">
                        <span style="color: #b91c1c; font-size: 0.82em; line-height: 1.3; font-weight: 500;"><strong>Un par (RDM + SCAR)</strong><br>por cada clase rechazada</span>
                    </div>
                </div>
            </div>
        `);
    }

    const otClean = ot.replace(/_\d{8}_\d{6}_.*/, "");
    let allLoaded = true;
    if (clasesRechazadas && clasesRechazadas.length > 0) {
        clasesRechazadas.forEach((c) => {
            const group = document.createElement("div");
            group.style.styleFloat = "none";
            group.style.clear = "both";
            group.style.marginBottom = "25px";
            group.style.padding = "0";
            group.style.background = "#ffffff";
            group.style.border = "1px solid #fecaca";
            group.style.borderRadius = "12px";
            group.style.width = "100%";
            group.style.boxSizing = "border-box";
            group.style.display = "flex";
            group.style.flexDirection = "column";
            group.style.flexShrink = "0";
            group.style.boxShadow = "0 4px 10px rgba(220, 38, 38, 0.05)";
            group.style.overflow = "hidden";
            
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
                        (filename.startsWith("f_ccl_rdm_") || filename.startsWith("f-ccl-rdm_") || filename.startsWith("rechazo_")) &&
                        filename.includes(c.toLowerCase()) && !filename.includes("_rechazado.pdf")
                    );
                });
                existingScar = files.find((f) => {
                    const nameLower = (f.nombre || "").toLowerCase();
                    const filename = nameLower.split("/").pop();
                    const pathNorm = nameLower.replace(/\\/g, "/");
                    const parts = pathNorm.split("/");
                    const isUserUploadedScar = parts[parts.length - 2] === "scar";
                    return (
                        isUserUploadedScar &&
                        nameLower.includes("documentos_rechazados/scar/") &&
                        (filename.startsWith("f_ccl_scar_") || filename.startsWith("f-ccl-scar_") || filename.startsWith("scar_")) &&
                        filename.includes(c.toLowerCase()) &&
                        !filename.includes("_rechazado.pdf")
                    );
                });
            }
            const label = c.charAt(0).toUpperCase() + c.slice(1);
            let groupHtml = `
                <div style="background: #fee2e2; padding: 12px 18px; border-bottom: 1px solid #fecaca; display: flex; align-items: center; gap: 8px;">
                    <img src="${getBaseUrl()}images/perspectiva-icon.png" style="width: 36px; height: 36px; object-fit: contain;">
                    <h4 style="margin: 0; color: #b91c1c; font-weight: 700; font-family:'Poppins', sans-serif; font-size: 1.05em; text-transform: uppercase;">
                        CLASE: ${label}
                    </h4>
                </div>
                <div style="padding: 18px; display: flex; flex-direction: column; gap: 20px;">
            `;
            
            // Rechazo
            const cleanRechazoName = existingRechazo ? existingRechazo.nombre.split("/").pop() : "";
            if (!existingRechazo) {
                allLoaded = false;
            }
            groupHtml += `
                <div class="form-group" style="margin-bottom: 0; width: 100%;">
                    <label style="font-weight: 600; color: #334155; margin-bottom: 6px; display: block; font-family: 'Poppins', sans-serif; font-size: 0.95em;">
                        Formato de Rechazo <span style="color:#ef4444;">*</span>
                        ${existingRechazo ? `<span style="background:#dcfce7;color:#15803d;border-radius:20px;padding:2px 8px;font-size:0.82em;margin-left:4px;font-weight:600;">Cargado</span>` : ''}
                    </label>
                    <div class="custom-file-upload-btn-wrapper" style="margin-bottom: 8px; display:flex; justify-content:center;">
                        <div class="custom-file-dropzone btn-modelo btn-modelo-no" style="position:relative; ${existingRechazo ? 'pointer-events:none; opacity:0.6; filter:grayscale(0.3);' : ''}">
                            <input type="file" id="rechazo-input-${c.toLowerCase().replace(/\\s+/g, '-')}" name="rechazo_${c.toLowerCase()}" data-type="rechazo" accept=".pdf" ${existingRechazo ? 'disabled' : 'required'} style="position:absolute;top:0;left:0;width:100%;height:100%;opacity:0;cursor:${existingRechazo ? 'not-allowed' : 'pointer'};" onchange="window._onRechFileSelected(this, '${ot}', 'rechazo')">
                            <img src="${getBaseUrl()}images/upload_icon.png" alt="Subir RDM">
                            <span>${existingRechazo ? 'Formato Cargado' : 'Subir Formato Rechazo'}</span>
                        </div>
                    </div>
                    <div class="file-card-preview-container" style="width: 100%;">
                        ${existingRechazo ? `
                            <div class="file-card-preview-container" style="width: 100%; display: flex; justify-content: center;">
                                <div class="dibujos-file-card card-otro" style="border-left-color: #dc2626; width: 100%; max-width: 280px; margin-top: 8px;">
                                    <div class="file-icon-wrapper alm-cursor-pointer" onclick="almacenVerPdf('${ot}','${existingRechazo.nombre}','otro')" title="Abrir PDF">
                                        <img src="${getBaseUrl()}images/pdf-view-shadow.png" class="file-icon icon-default">
                                        <img src="${getBaseUrl()}images/pdf-view.png" class="file-icon icon-hover">
                                    </div>
                                    <div class="file-name alm-cursor-pointer" onclick="almacenVerPdf('${ot}','${existingRechazo.nombre}','otro')" title="Abrir PDF">
                                        ${cleanRechazoName}
                                    </div>
                                    <div class="file-actions alm-flex-gap-5">
                                        <button type="button" class="btn-dibujos btn-dibujos-sm btn-ver" style="background-color: #dc2626; color: white; border-color: #dc2626;" onclick="almacenVerPdf('${ot}','${existingRechazo.nombre}','otro')">Ver</button>
                                        <button type="button" class="btn-dibujos btn-dibujos-sm btn-eliminar" style="background-color: #ef4444; color: white; border-color: #ef4444;" onclick="quitarArchivoRechazo('${ot}','${existingRechazo.nombre}',this)">Eliminar</button>
                                    </div>
                                </div>
                            </div>
                        ` : ''}
                    </div>
                </div>`;

            // SCAR
            const cleanScarName = existingScar ? existingScar.nombre.split("/").pop() : "";
            if (!existingScar) {
                allLoaded = false;
            }

            groupHtml += `
                <div class="form-group" style="margin-bottom: 0; width: 100%;">
                    <label style="font-weight: 600; color: #334155; margin-bottom: 6px; display: block; font-family: 'Poppins', sans-serif; font-size: 0.95em;">
                        SCAR Firmado por Proveedor <span style="color:#ef4444;">*</span>
                        ${existingScar ? `<span style="background:#dcfce7;color:#15803d;border-radius:20px;padding:2px 8px;font-size:0.82em;margin-left:4px;font-weight:600;">Cargado</span>` : ''}
                    </label>
                    <div class="custom-file-upload-btn-wrapper" style="margin-bottom: 8px; display:flex; justify-content:center;">
                        <div class="custom-file-dropzone btn-modelo btn-modelo-no" style="position:relative; ${existingScar ? 'pointer-events:none; opacity:0.6; filter:grayscale(0.3);' : ''}">
                            <input type="file" id="scar-input-${c.toLowerCase().replace(/\\s+/g, '-')}" name="scar_${c.toLowerCase()}" data-type="scar" accept=".pdf" ${existingScar ? 'disabled' : 'required'} style="position:absolute;top:0;left:0;width:100%;height:100%;opacity:0;cursor:${existingScar ? 'not-allowed' : 'pointer'};" onchange="window._onRechFileSelected(this, '${ot}', 'scar')">
                            <img src="${getBaseUrl()}images/upload_icon.png" alt="Subir SCAR">
                            <span>${existingScar ? 'SCAR Cargado' : 'Subir SCAR Firmado'}</span>
                        </div>
                    </div>
                    <div class="file-card-preview-container" style="width: 100%;">
                        ${existingScar ? `
                            <div class="file-card-preview-container" style="width: 100%; display: flex; justify-content: center;">
                                <div class="dibujos-file-card card-otro" style="border-left-color: #ca8a04; width: 100%; max-width: 280px; margin-top: 8px;">
                                    <div class="file-icon-wrapper alm-cursor-pointer" onclick="almacenVerPdf('${ot}','${existingScar.nombre}','otro')" title="Abrir PDF">
                                        <img src="${getBaseUrl()}images/pdf-view-shadow.png" class="file-icon icon-default">
                                        <img src="${getBaseUrl()}images/pdf-view.png" class="file-icon icon-hover">
                                    </div>
                                    <div class="file-name alm-cursor-pointer" onclick="almacenVerPdf('${ot}','${existingScar.nombre}','otro')" title="Abrir PDF">
                                        ${cleanScarName}
                                    </div>
                                    <div class="file-actions alm-flex-gap-5">
                                        <button type="button" class="btn-dibujos btn-dibujos-sm btn-ver" style="background-color: #ca8a04; color: white; border-color: #ca8a04;" onclick="almacenVerPdf('${ot}','${existingScar.nombre}','otro')">Ver</button>
                                        <button type="button" class="btn-dibujos btn-dibujos-sm btn-eliminar" style="background-color: #ef4444; color: white; border-color: #ef4444;" onclick="quitarArchivoRechazo('${ot}','${existingScar.nombre}',this)">Eliminar</button>
                                    </div>
                                </div>
                            </div>
                        ` : ''}
                    </div>
                </div>
            </div>`;
            group.innerHTML = groupHtml;
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
                            const otClean = ot.replace(/_\d{8}_\d{6}_.*/, "");
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
                                "guias",
                                "pistones",
                            ];
                            const filtrados = baseRech.filter((f) => {
                                const nombre = (f.nombre || "").toLowerCase();
                                  const perteneceAClase = clasesMonitoreadas.some(
                                      (c) => window.compararClasesSurgico ? window.compararClasesSurgico(nombre, c) : nombre.includes(c),
                                  );
                                  if (perteneceAClase) {
                                      return clasesRechazadas.some((c) =>
                                          window.compararClasesSurgico ? window.compararClasesSurgico(nombre, c.toLowerCase()) : nombre.includes(c.toLowerCase()),
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

document.addEventListener("DOMContentLoaded", () => {
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
                        window.cerrarModalGestionVeredicto();
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
});

// ── Handler: bloquear btn Subir Formato al seleccionar archivo (rechazados) ──
window._onRechFileSelected = function (input, ot, tipo) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];
    const dropzone = input.closest(".custom-file-dropzone");
    const previewContainer = dropzone?.closest(".form-group")?.querySelector(".file-card-preview-container");

    const objectUrl = URL.createObjectURL(file);
    input._objectUrl = objectUrl;

    if (dropzone) {
        dropzone.style.display = 'none';
    }

    if (previewContainer) {
        let base = window.baseUrl || window.location.origin + "/";
        if (!base.endsWith("/")) base += "/";
        const btnColor = tipo === "scar" ? "#ca8a04" : "#dc2626";
        
        previewContainer.style.display = 'flex';
        previewContainer.style.justifyContent = 'center';
        previewContainer.style.width = '100%';

        previewContainer.innerHTML = `
            <div class="dibujos-file-card card-otro" style="border-left-color: ${btnColor}; width: 100%; max-width: 280px; margin-top: 6px;">
                <div class="file-icon-wrapper alm-cursor-pointer" onclick="window.open('${objectUrl}', '_blank')" title="Ver archivo">
                    <img src="${base}images/pdf-view-shadow.png" class="file-icon icon-default">
                    <img src="${base}images/pdf-view.png" class="file-icon icon-hover">
                </div>
                <div class="file-name alm-cursor-pointer" onclick="window.open('${objectUrl}', '_blank')" title="Ver archivo">
                    ${file.name}
                    <div style="font-size:0.82em;background:#fee2e2;color:#dc2626;border-radius:20px;padding:2px 8px;font-weight:700;display:inline-block;margin-top:4px;">Por subir</div>
                </div>
                <div class="file-actions alm-flex-gap-5">
                    <button type="button" class="btn-dibujos btn-dibujos-sm btn-ver" style="background-color: ${btnColor}; color: white; border-color: ${btnColor};" onclick="window.open('${objectUrl}', '_blank')">Ver</button>
                    <button type="button" class="btn-dibujos btn-dibujos-sm btn-eliminar" style="background-color: #dc2626; color: white; border-color: #dc2626;" onclick="window._quitarLdmTemporal('${input.id}')">Quitar</button>
                </div>
            </div>`;
    }
};
