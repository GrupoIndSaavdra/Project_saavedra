// ── PRE-ORDEN DE CASTING HOOKS ──

window.handlePocMaterialChange = function (pageNum, idx, selectEl) {
    const pData = window.pocState["page" + pageNum];
    const row = pData.filas[idx];
    if (selectEl.value === "Otro") {
        const wrapper = selectEl.closest(".poc-material-wrapper");
        const customInput = wrapper && wrapper.querySelector(".poc-input-material-custom");
        if (customInput) {
            customInput.classList.remove("cal-display-none");
            customInput.value = "";
            customInput.focus();
        }
        selectEl.classList.add("cal-display-none");
        return;
    }
    row.material = selectEl.value;
    window.loadPocPage(pageNum);
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
        inputEl.classList.add("cal-display-none");
        if (selectEl) selectEl.classList.remove("cal-display-none");
        return;
    }
    const pData = window.pocState["page" + pageNum];
    const row = pData.filas[idx];
    const materialesDisponibles = [
        ...window.MATERIALES_CASTING_FIJOS,
        ...window.materialesCastingPersonalizados,
    ];
    if (!materialesDisponibles.includes(val)) {
        if (materialesDisponibles.length >= 7) {
            almacenToast("Límite de 7 materiales en el selector alcanzado.", "error");
            inputEl.classList.add("cal-display-none");
            if (selectEl) selectEl.classList.remove("cal-display-none");
            window.loadPocPage(pageNum);
            return;
        }
        window.materialesCastingPersonalizados.push(val);
    }
    row.material = val;
    inputEl.classList.add("cal-display-none");
    if (selectEl) selectEl.classList.remove("cal-display-none");
    window.loadPocPage(pageNum);
}

window.eliminarMaterialGlobal = function (pageNum, mat) {
    window.materialesCastingPersonalizados = window.materialesCastingPersonalizados.filter((m) => m !== mat);
    const paginas = [window.pocState.page1, window.pocState.page2];
    paginas.forEach((p) => {
        if (p && p.filas) {
            p.filas.forEach((f) => {
                if (f.material === mat) {
                    f.material = "";
                }
            });
        }
    });
    almacenToast(`Material "${mat}" eliminado de las opciones.`, "success");
    window.loadPocPage(pageNum);
};

window.handlePocClaseChange = function (pageNum, idx, selectEl) {
    const pData = window.pocState["page" + pageNum];
    const row = pData.filas[idx];
    const selectedOption = selectEl.options[selectEl.selectedIndex];
    if (selectedOption && selectedOption.value) {
        row.id_clase = selectedOption.value;
        row.descripcion = selectedOption.text;
        const tr = selectEl.closest("tr");
        const codEl = tr.querySelector(".poc-input-codigo");
        const tipoEl = tr.querySelector(".poc-input-tipo");
        if (codEl && !codEl.dataset.userEdited) {
            codEl.value = window.autoGenerarCodigo(
                tipoEl ? tipoEl.value : "",
                selectedOption.text,
                window.pocState.ot_raw,
            );
        }
    }
};

window.recalcPocRowWeight = function (pageNum, idx) {
    if (typeof window.savePocPageData === "function") {
        window.savePocPageData(pageNum);
    }
    if (typeof window.loadPocPage === "function") {
        window.loadPocPage(pageNum);
    }
};

window.agregarFilaPoc = function (pageNum) {
    window.savePocPageData(pageNum);
    window.pocState["page" + pageNum].filas.push({
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
    window.loadPocPage(pageNum);
};

window.eliminarFilaPoc = function (pageNum, idx) {
    window.savePocPageData(pageNum);
    window.pocState["page" + pageNum].filas.splice(idx, 1);
    window.loadPocPage(pageNum);
};
