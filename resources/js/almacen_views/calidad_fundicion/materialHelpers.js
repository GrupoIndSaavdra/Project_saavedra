// ── MATERIAL HELPERS AND DRAFT AUTOSAVE ──

window.savePocPageData = function (pageNum) {
    if (!pageNum || (pageNum !== 1 && pageNum !== 2)) return;
    const pData = window.pocState["page" + pageNum];
    if (!pData) return;
    const provEl = document.getElementById(`poc-p${pageNum}-proveedor`);
    const folioEl = document.getElementById(`poc-p${pageNum}-folio`);
    const obsEl = document.getElementById(`poc-p${pageNum}-observaciones`);
    const fechaEntregaEl = document.getElementById(`poc-p${pageNum}-fecha-entrega`);
    if (provEl) pData.proveedor = provEl.value;
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
            rowState.tipo_modelo = tipoSel ? tipoSel.value : rowState.tipo_modelo || "";
            const rawCant = tr.querySelector(".poc-input-cant-fabricar")?.value;
            rowState.cant_fabricar = rawCant !== undefined && rawCant !== "" ? parseInt(rawCant) : "";
            rowState.cant_consignacion = parseInt(tr.querySelector(".poc-input-cant-consignacion")?.value || 0) || 0;
            const selectedOption = tr.querySelector(".poc-clase-select")?.options[tr.querySelector(".poc-clase-select").selectedIndex];
            rowState.id_clase = selectedOption?.value || "";
            rowState.descripcion = selectedOption?.text || "";
            rowState.material = tr.querySelector(".poc-input-material")?.value || "";
            rowState.codigo = tr.querySelector(".poc-input-codigo")?.value || "";
            rowState.peso_juego = parseFloat(tr.querySelector(".poc-input-peso-juego")?.value || 0) || 0;
            rowState.peso_total = parseFloat(tr.querySelector(".poc-input-peso-total")?.value || 0) || 0;
            rowState.fecha_entrega = tr.querySelector(".poc-input-fecha-entrega")?.value || "";
        });
    }
};

window.bloquearBotonEstatico = function (otClean, tipoModeloNorm, cardType) {
    const selector = `.btn-anadir-formato[data-ot="${otClean}"][data-tipo="${tipoModeloNorm}"][data-card="${cardType}"]`;
    document.querySelectorAll(selector).forEach((btn) => {
        btn.disabled = true;
        btn.style.pointerEvents = "none";
        btn.style.opacity = "0.6";
        btn.style.cursor = "not-allowed";
        btn.title = "Formato ya subido. Elimina el archivo para subir otro.";
        btn.innerHTML = `<img src="${window.baseUrl || '/'}images/anadir.png" style="width:20px;height:20px;filter:grayscale(1);"> Subir Formato`;
    });
};

window.desbloquearBotonEstatico = function (otClean, tipoModeloNorm, cardType) {
    const selector = `.btn-anadir-formato[data-ot="${otClean}"][data-tipo="${tipoModeloNorm}"][data-card="${cardType}"]`;
    document.querySelectorAll(selector).forEach((btn) => {
        btn.disabled = false;
        btn.style.pointerEvents = "auto";
        btn.style.opacity = "1";
        btn.style.cursor = "pointer";
        btn.title = "Subir archivo";
        btn.innerHTML = `<img src="${window.baseUrl || '/'}images/anadir.png" style="width:20px;height:20px;"> Subir Formato`;
    });
};

window.saveLiberacionDraft = function () {
    const form = document.getElementById("formLiberacion");
    const ot = document.getElementById("lib-ot")?.value;
    const tipo = document.getElementById("lib-tipo")?.value;
    if (!form || !ot || !tipo) return;
    const fd = new FormData(form);
    const dataObj = {};
    fd.forEach((value, key) => {
        dataObj[key] = value;
    });
    localStorage.setItem(`lib_draft_${ot}_${tipo}`, JSON.stringify(dataObj));
};

window.loadLiberacionDraft = function () {
    const ot = document.getElementById("lib-ot")?.value;
    const tipo = document.getElementById("lib-tipo")?.value;
    if (!ot || !tipo) return;
    const raw = localStorage.getItem(`lib_draft_${ot}_${tipo}`);
    if (!raw) return;
    try {
        const dataObj = JSON.parse(raw);
        Object.entries(dataObj).forEach(([key, val]) => {
            const el = document.getElementsByName(key)[0];
            if (el && !el.value && val) {
                el.value = val;
                if (el.classList.contains("lib-num-input") || el.classList.contains("lib-num-input-sm")) {
                    if (typeof formatInputTruncated === "function") {
                        formatInputTruncated(el);
                    }
                }
            }
        });
    } catch (_) {}
};

window.clearLiberacionDraft = function () {
    const ot = document.getElementById("lib-ot")?.value;
    const tipo = document.getElementById("lib-tipo")?.value;
    if (!ot || !tipo) return;
    localStorage.removeItem(`lib_draft_${ot}_${tipo}`);
};

document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("formLiberacion");
    if (form) {
        form.addEventListener("input", () => {
            window.saveLiberacionDraft();
        });
        form.addEventListener("change", () => {
            window.saveLiberacionDraft();
        });
    }
});

window.calcularConsignacion = function (fabricar, tipo) {
    if (!fabricar || fabricar <= 0) return 0;
    const tLow = (tipo || "").toLowerCase();
    if (tLow.includes("matriz") || tLow.includes("plato") || tLow.includes("embudo") || tLow.includes("cabeza") || tLow.includes("candado")) {
        return fabricar + 1;
    }
    if (tLow.includes("obturador")) {
        return fabricar + 2;
    }
    if (tLow.includes("corona")) {
        return fabricar + 1;
    }
    return fabricar + 1;
};

window.autoGenerarCodigo = function (tipo, claseNombre, ot) {
    let otNumber = "";
    const cleanOt = (ot || "").replace(/_[rR]?\d{8}_\d{6}_.*/, "").replace(/_[rR]?\d+$/, "");
    const numMatch = cleanOt.match(/\d+/);
    if (numMatch) {
        otNumber = numMatch[0];
    } else {
        otNumber = cleanOt;
    }
    let prefix = "F";
    const tLow = (tipo || "").toLowerCase();
    const cLow = (claseNombre || "").toLowerCase();
    const esTempladera = tLow.includes("templadera") || cLow.includes("templadera");
    if (esTempladera) {
        if (tLow.includes("obturador") || cLow.includes("obturador")) prefix = "TO";
        else if (tLow.includes("molde") || cLow.includes("molde")) prefix = "TM";
        else if (tLow.includes("fondo") || cLow.includes("fondo")) prefix = "TF";
        else if (tLow.includes("bombillo") || cLow.includes("bombillo")) prefix = "TB";
        else prefix = "T";
    } else {
        if (tLow === "bombillo" || cLow.includes("bombillo")) prefix = "B";
        else if (tLow === "obturador" || cLow.includes("obturador")) prefix = "O";
        else if (tLow === "molde" || cLow.includes("molde")) prefix = "M";
        else if (tLow === "fondo" || cLow.includes("fondo")) prefix = "F";
        else if (cLow.includes("cabeza") && cLow.includes("soplo")) prefix = "CS";
        else {
            prefix = (claseNombre || tipo || "F").charAt(0).toUpperCase();
        }
    }
    return otNumber ? prefix + otNumber : "";
};
