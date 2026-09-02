// ── REJECTION CASTING FILES RENDERING & ACTIONS ──

window.pintarCardsArchivosRechazados = function (archivos, ot, contenedorId, tipoModelo) {
    const contenedor = document.getElementById(contenedorId);
    if (!contenedor) return;
    contenedor.innerHTML = "";
    
    const tipoModeloNorm = (tipoModelo || "").trim().toLowerCase();
    const otClean = ot.replace(/_[rR]?\d{8}_\d{6}_.*/, "").replace(/_[rR]?\d+$/, "");
    let count = 0;
    
    if (archivos && archivos.length > 0) {
        archivos.forEach((file) => {
            const fileNameLower = file.nombre.toLowerCase();
            const esImg = ["png", "jpg", "jpeg", "gif", "webp", "bmp"].includes(file.nombre.split(".").pop().toLowerCase());
            const defaultIcon = esImg ? "galeria-shadow.png" : "pdf-view-shadow.png";
            
            let matchModelo = false;
            const knownClasses = ["candado obturador", "cabeza de soplo", "obturador", "bombillo", "embudo", "corona", "plato", "molde", "fondo", "pistones", "guías", "guias"];
            let foundClass = null;
            for (let kc of knownClasses) {
                if (window.compararClasesSurgico(fileNameLower, kc)) {
                    foundClass = kc;
                    break;
                }
            }
            if (foundClass) {
                matchModelo = (foundClass === tipoModeloNorm);
            } else {
                matchModelo = window.compararClasesSurgico(fileNameLower, tipoModeloNorm);
            }
            
            const isRechazadoFolder = fileNameLower.includes("documentos_rechazados") || fileNameLower.includes("rechazado") || fileNameLower.includes("scar");
            
            if (matchModelo && isRechazadoFolder) {
                count++;
                const card = document.createElement("div");
                card.className = "dibujos-file-card card-otro";
                card.style.animationDelay = "0s";
                card.style.borderLeftColor = "#dc2626";
                card.style.margin = "8px";
                
                const dispName = file.nombre.split("/").pop();
                
                card.innerHTML = `
                    <div class="file-icon-wrapper alm-cursor-pointer" title="Abrir PDF" onclick="window.almacenVerPdf('${ot}', '${file.nombre}', '${file.tipo}')">
                        <img src="${window.baseUrl || "/"}images/${defaultIcon}" class="file-icon icon-default">
                    </div>
                    <div class="file-name alm-cursor-pointer" title="Abrir PDF" onclick="window.almacenVerPdf('${ot}', '${file.nombre}', '${file.tipo}')">
                        ${dispName}
                    </div>
                    <div class="file-actions" style="margin-top:auto; display:flex; gap:6px; width:100%;">
                        <button type="button" class="btn-dibujos btn-dibujos-sm btn-ver" style="flex:1;" onclick="window.almacenVerPdf('${ot}', '${file.nombre}', '${file.tipo}')">Ver</button>
                        <button type="button" class="btn-dibujos btn-dibujos-sm" style="flex:1; background:#dc2626; color:white; border:none; cursor:pointer;" onclick="window.eliminarArchivoRechazadoServer('${ot}', '${file.nombre}', '${tipoModelo}')">Eliminar</button>
                    </div>
                `;
                contenedor.appendChild(card);
            }
        });
    }
    
    if (count > 0) {
        window.bloquearBotonEstatico(otClean, tipoModeloNorm, "rechazados");
    } else {
        window.desbloquearBotonEstatico(otClean, tipoModeloNorm, "rechazados");
        contenedor.innerHTML = `
            <div style="text-align: center; color: #64748b; padding: 15px; font-style: italic; width: 100%; font-family: 'Poppins', sans-serif;">
                No se ha subido ningún formato escaneado RDM o SCAR para este modelo.
            </div>
        `;
    }
};

window.subirArchivoRechazadoEstatico = function (ot, tipoModelo, inputId) {
    const fileInput = document.getElementById(inputId);
    if (!fileInput || fileInput.files.length === 0) return;
    const file = fileInput.files[0];
    
    const formData = new FormData();
    formData.append("ot", ot);
    formData.append("tipo_modelo", tipoModelo);
    formData.append("archivo", file);
    formData.append("origin", "rechazado");
    
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");
    if (token) formData.append("_token", token);
    
    const tipoModeloNorm = (tipoModelo || "").trim().toLowerCase();
    const otClean = ot.replace(/_[rR]?\d{8}_\d{6}_.*/, "").replace(/_[rR]?\d+$/, "");
    window.bloquearBotonEstatico(otClean, tipoModeloNorm, "rechazados");
    
    fetch(window.almacenRoutes.uploadFileCategorized, {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": token || "",
        },
        body: formData,
    })
        .then((res) => res.json())
        .then((data) => {
            if (data.success) {
                almacenToast("Archivo subido y guardado exitosamente.", "success");
                fileInput.value = "";
                if (typeof window.actualizarContenedoresMateriales === "function") {
                    window.actualizarContenedoresMateriales(ot, tipoModelo);
                }
            } else {
                almacenToast(data.error || data.message || "Error al subir archivo.", "error");
                window.desbloquearBotonEstatico(otClean, tipoModeloNorm, "rechazados");
            }
        })
        .catch((err) => {
            console.error(err);
            almacenToast("Error de conexión al subir el archivo.", "error");
            window.desbloquearBotonEstatico(otClean, tipoModeloNorm, "rechazados");
        });
};

window.eliminarArchivoRechazadoServer = function (ot, fileNombre, tipoModelo) {
    const filenameOnly = fileNombre.split("/").pop();
    if (!confirm(`¿Estás seguro de eliminar el archivo "${filenameOnly}"?`)) {
        return;
    }
    
    const formData = new FormData();
    formData.append("ot", ot);
    formData.append("archivo", fileNombre);
    formData.append("tipo", "otro");
    formData.append("origin", "rechazado");
    
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
                almacenToast("Archivo eliminado exitosamente.", "success");
                if (typeof window.actualizarContenedoresMateriales === "function") {
                    window.actualizarContenedoresMateriales(ot, tipoModelo);
                }
            } else {
                almacenToast(data.error || data.message || "Error al eliminar archivo.", "error");
            }
        })
        .catch((err) => {
            console.error(err);
            almacenToast("Error de red al eliminar el archivo.", "error");
        });
};
