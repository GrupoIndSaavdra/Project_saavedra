// ── CONTROL DE MODELOS Y VERIFICACION FISICA (Calidad) ──

let cmConfirmarSelectedFiles = [];
window.cmConfirmarSelectedFiles = cmConfirmarSelectedFiles;

window.removeCmConfirmarAttachment = function (index) {
    cmConfirmarSelectedFiles.splice(index, 1);
    renderCmConfirmarBadges();
};

function renderCmConfirmarBadges() {
    window.renderFileCards(
        "cm-archivos-list",
        cmConfirmarSelectedFiles,
        "window.removeCmConfirmarAttachment",
        "#16a34a",
        "Ningún archivo adjuntado aún."
    );
}

window.renderCmConfirmarBadges = renderCmConfirmarBadges;


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
                if (window.ModeloStateMachine) {
                    window.ModeloStateMachine.onConfirmarModelo(ot);
                }
                if (id_hash) {
                    const container = document.getElementById("control-modelo-" + id_hash);
                    if (container) {
                        container.style.opacity = "0.5";
                        container.style.pointerEvents = "none";
                    }
                }
            } else {
                mostrarToast(data.message || "Error al actualizar estado", true);
            }
        })
        .catch((err) => {
            console.error(err);
            mostrarToast("Error de conexión", true);
        });
};

window.onCmClaseToggle = function (checkbox) {
    checkbox.parentElement.style.borderColor = checkbox.checked ? "#0a8504" : "#cbd5e1";
    checkbox.parentElement.style.backgroundColor = checkbox.checked ? "#f0fdf4" : "#fff";
    
    const claseNombre = checkbox.value.toLowerCase();
    const fileCards = document.querySelectorAll("#cm-server-files-container .select-file-card");
    fileCards.forEach((card) => {
        const fileInput = card.querySelector('input[type="checkbox"]');
        if (!fileInput) return;
        const fileName = fileInput.value.toLowerCase();
        if (fileName.includes(claseNombre)) {
            fileInput.checked = checkbox.checked;
            card.classList.toggle("checked-card", checkbox.checked);
        }
    });
};

window.abrirModalConfirmarModelo = function (ot, idHash, clasesFaltantes = null, todasClases = null) {
    const modal = document.getElementById("modalConfirmarModelo");
    if (!modal) return;
    
    cmConfirmarSelectedFiles = [];
    window.cmConfirmarSelectedFiles = cmConfirmarSelectedFiles;
    renderCmConfirmarBadges();
    
    const form = document.getElementById("formConfirmarModelo");
    if (form) form.reset();
    
    const otInput = document.getElementById("cm-ot");
    if (otInput) otInput.value = ot;
    const hashInput = document.getElementById("cm-id-hash");
    if (hashInput) hashInput.value = idHash || "";
    
    const inputDestinatario = document.getElementById("cm-destinatario");
    const inputDestinatarioCalidad = document.getElementById("cm-destinatario-calidad");
    if (inputDestinatario && form) {
        inputDestinatario.value = form.getAttribute("data-email-modelo");
    }
    if (inputDestinatarioCalidad && form) {
        inputDestinatarioCalidad.value = form.getAttribute("data-email-calidad");
    }
    
    const subtitle = document.getElementById("confirmar-modelo-subtitle");
    if (subtitle) subtitle.textContent = `OT: ${ot}`;
    
    const fechaInput = document.getElementById("cm-fecha");
    if (fechaInput) {
        const h = new Date();
        fechaInput.value = `${h.getFullYear()}-${String(h.getMonth() + 1).padStart(2, "0")}-${String(h.getDate()).padStart(2, "0")}`;
    }
    
    modal.classList.add("open");
    document.body.classList.add("modal-open");
    
    const clasesContainer = document.getElementById("cm-clases-container");
    if (clasesContainer) {
        clasesContainer.innerHTML = '<div class="alm-spinner" id="cm-clases-spinner" style="border-top-color:#0284c7; display:block; margin:5px auto;"></div>';
        if (todasClases && Array.isArray(todasClases) && todasClases.length > 0) {
            let html = "";
            todasClases.forEach((nombreClase, index) => {
                const nombreNorm = nombreClase.toLowerCase();
                let yaProcesada = false;
                if (clasesFaltantes !== null && Array.isArray(clasesFaltantes)) {
                    const esFaltante = clasesFaltantes.some(
                        (f) => f.toLowerCase().includes(nombreNorm) || nombreNorm.includes(f.toLowerCase())
                    );
                    yaProcesada = !esFaltante;
                }
                if (yaProcesada) return;
                
                const nombreDisplay = nombreClase.charAt(0).toUpperCase() + nombreClase.slice(1);
                html += `
                    <label style="display:flex; align-items:center; gap:8px; background:#fff; border:1.5px solid #cbd5e1; padding:10px 15px; border-radius:8px; cursor:pointer; transition:all 0.2s ease;"
                           onmouseover="this.style.borderColor='#0a8504'; this.style.backgroundColor='#f0fdf4';"
                           onmouseout="if(!this.querySelector('input').checked){ this.style.borderColor='#cbd5e1'; this.style.backgroundColor='#fff'; }">
                        <input type="checkbox" name="clases_seleccionadas[]" value="${nombreDisplay}" class="cm-clase-checkbox"
                               style="width:18px; height:18px; cursor:pointer;"
                               onchange="window.onCmClaseToggle(this);">
                        <span style="font-family:'Poppins', sans-serif; font-weight:500; color:#334155;">${index + 1}. ${nombreDisplay}</span>
                    </label>
                `;
            });
            if (html === "") {
                html = '<div style="text-align:center; color:#64748b; padding:10px; font-style:italic;">Todas las clases ya fueron procesadas.</div>';
            }
            clasesContainer.innerHTML = html;
        } else {
            clasesContainer.innerHTML = '<span style="color:#ef4444; font-size:0.9em; font-weight:500;">No hay clases configuradas para confirmar.</span>';
        }
    }
    
    const filesContainer = document.getElementById("cm-server-files-container");
    if (filesContainer) {
        filesContainer.innerHTML = `
            <div style="text-align: center; padding: 10px;">
                <div class="alm-spinner" style="border-top-color: #033966; display: inline-block;"></div>
                <span style="color: #64748b; margin-left: 10px;">Obteniendo archivos del servidor...</span>
            </div>
        `;
        fetch(`${window.almacenRoutes.archivos}?ot=${encodeURIComponent(ot)}&tipo=modelo`)
            .then((res) => res.json())
            .then((data) => {
                if (data.existe && data.archivos && data.archivos.length > 0) {
                    let baseUrl = window.baseUrl || window.location.origin + "/";
                    if (!baseUrl.endsWith("/")) baseUrl += "/";
                    let archivosAMostrar = data.archivos;
                    if (clasesFaltantes && Array.isArray(clasesFaltantes)) {
                        archivosAMostrar = archivosAMostrar.filter((f) => {
                            const n = (f.nombre || "").toLowerCase();
                            if (n.includes("documentos_aprobados") || n.includes("documentos_rechazados") || n.includes("pre-orden"))
                                return true;
                            const knownClasses = ["candado obturador", "cabeza de soplo", "obturador", "bombillo", "embudo", "corona", "plato", "molde", "fondo", "pistones", "guías", "guias"];
                            let foundClass = null;
                            for (let kc of knownClasses) {
                                if (window.compararClasesSurgico(n, kc)) {
                                    foundClass = kc;
                                    break;
                                }
                            }
                            if (foundClass) {
                                return clasesFaltantes.some((clase) => {
                                    return window.compararClasesSurgico(foundClass, clase);
                                });
                            }
                            return true;
                        });
                    }
                    const sectionsHtml = window.generarHtmlCategorizadoArchivos(
                        archivosAMostrar,
                        ot,
                        baseUrl,
                        "preorden"
                    );
                    filesContainer.innerHTML = sectionsHtml || `<div style="text-align: center; color: #64748b; padding: 15px; font-style: italic;">No se encontraron archivos pendientes para esta OT.</div>`;
                    
                    const fileCards = filesContainer.querySelectorAll(".select-file-card");
                    fileCards.forEach((card) => {
                        const fileInput = card.querySelector('input[type="checkbox"]');
                        if (fileInput) fileInput.checked = false;
                        card.classList.remove("checked-card");
                    });
                } else {
                    filesContainer.innerHTML = `<div style="text-align: center; color: #64748b; padding: 15px; font-style: italic;">No se encontraron archivos en el servidor.</div>`;
                }
            })
            .catch((err) => {
                console.error(err);
                filesContainer.innerHTML = `<div style="text-align: center; color: #ef4444; padding: 15px; font-weight: 600;">Ocurrió un error al cargar los archivos.</div>`;
            });
    }
};

window.cerrarModalConfirmarModelo = function () {
    const modal = document.getElementById("modalConfirmarModelo");
    if (modal) modal.classList.remove("open");
    document.body.classList.remove("modal-open");
};

document.addEventListener("DOMContentLoaded", () => {
    // ── Listener del input de archivos ────────────────────────────────────────
    const fileInput = document.getElementById("cm-archivos");
    if (fileInput) {
        fileInput.addEventListener("change", function () {
            Array.from(this.files).forEach((file) => {
                // Evitar duplicados por nombre y tamaño
                const exists = cmConfirmarSelectedFiles.some((f) => f.name === file.name && f.size === file.size);
                if (!exists) cmConfirmarSelectedFiles.push(file);
            });
            // Limpiar el input para permitir re-seleccionar el mismo archivo
            this.value = "";
            renderCmConfirmarBadges();
        });
    }

    const form = document.getElementById("formConfirmarModelo");
    if (!form) return;
    form.addEventListener("submit", async function (e) {
        e.preventDefault();
        const ot = document.getElementById("cm-ot")?.value;
        const idHash = document.getElementById("cm-id-hash")?.value;
        if (!ot) return;
        
        const selectedClasses = document.querySelectorAll(".cm-clase-checkbox:checked");
        if (selectedClasses.length === 0) {
            almacenToast("Debes seleccionar al menos una clase para confirmar su modelo físico.", "error");
            return;
        }
        if (cmConfirmarSelectedFiles.length === 0) {
            almacenToast("Debes adjuntar al menos un documento de recepción.", "error");
            return;
        }
        const totalPending = document.querySelectorAll(".cm-clase-checkbox").length;
        const selectedPending = document.querySelectorAll(".cm-clase-checkbox:checked").length;
        const allConfirmed = totalPending === selectedPending;
        const btn = document.getElementById("btn-submit-confirmar-modelo");
        const origText = btn ? btn.innerHTML : "";
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="alm-spinner" style="display:inline-block;border-top-color:#fff;width:14px;height:14px;margin-right:8px;vertical-align:middle;"></span> Guardando...';
        }
        const fd = new FormData(this);
        fd.delete("archivos[]");
        
        if (window.otCurrentClasses) {
            const yaUsadas = Array.from(document.querySelectorAll(".po-clase-select"))
                .map((s) => s.options[s.selectedIndex]?.text?.toLowerCase() || "")
                .filter((t) => t !== "");
            const availableClasses = window.otCurrentClasses.filter((c) => {
                const nombreNorm = c.nombre.toLowerCase();
                return !yaUsadas.some((yp) => nombreNorm.includes(yp) || yp.includes(nombreNorm));
            });
            const newHtml = '<option value="">Selecciona clase</option>' +
                availableClasses.map((c) => `<option value="${c.id}" data-nombre="${c.nombre}">${c.nombre}</option>`).join("");
            const tbody = document.getElementById("alm-tbody-preorden");
            if (tbody) {
                const selects = tbody.querySelectorAll(".po-clase-select");
                selects.forEach((select) => {
                    const currentVal = select.value;
                    select.innerHTML = newHtml;
                    if (currentVal && Array.from(select.options).some((o) => o.value === currentVal)) {
                        select.value = currentVal;
                    }
                });
            }
        }
        cmConfirmarSelectedFiles.forEach((file) => {
            fd.append("archivos[]", file);
        });
        fd.append("all_confirmed", allConfirmed ? "1" : "0");
        try {
            const resp = await fetch(window.almacenRoutes.confirmarModelo, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.content ?? "",
                    "X-Requested-With": "XMLHttpRequest",
                },
                body: fd,
            });
            const data = await resp.json();
            if (data.success) {
                almacenToast(data.message, "success");
                window.cerrarModalConfirmarModelo();
                if (allConfirmed) {
                    if (window.ModeloStateMachine)
                        window.ModeloStateMachine.onConfirmarModelo(ot);
                    if (idHash) {
                        const container = document.getElementById("control-modelo-" + idHash);
                        if (container) {
                            container.style.opacity = "0.5";
                            container.style.pointerEvents = "none";
                        }
                    }
                }
                setTimeout(() => location.reload(), 1600);
            } else {
                almacenToast(data.message || "Error al registrar el modelo.", "error");
            }
        } catch (err) {
            console.error("Error", err);
            almacenToast("Error de red al registrar el modelo.", "error");
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = origText;
            }
        }
    });
});
