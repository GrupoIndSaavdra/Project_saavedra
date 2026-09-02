// --- INITIALIZATION & MODAL LOGIC ---
window.pocState = {
    ot_raw: '',
    has_page2: false,
    page1: { proveedor: '', fecha: '', folio: '', observaciones: '', filas: [] },
    page2: { proveedor: '', fecha: '', folio: '', observaciones: '', filas: [] },
};
window.materialesCastingPersonalizados = [];
window.MATERIALES_CASTING_FIJOS = ["Hierro Gris", "Hierro Nodular", "Acero al Carbón", "Acero Inoxidable", "Bronce", "Aluminio"];
window.pocAvailableClasses = [];

window.abrirModalPreOrdenCasting = function(ot) {
    const modal = document.getElementById("modalPreOrdenCasting");
    if (!modal) return;
    
    window.pocState = {
        ot_raw: ot,
        has_page2: false,
        page1: { proveedor: '', fecha: new Date().toISOString().substring(0, 10), folio: '', observaciones: '', filas: [] },
        page2: { proveedor: '', fecha: new Date().toISOString().substring(0, 10), folio: '', observaciones: '', filas: [] },
    };
    window.materialesCastingPersonalizados = [];
    
    const subtitle = document.getElementById("poc-modal-subtitle");
    if (subtitle) subtitle.textContent = "OT: " + ot;
    
    document.getElementById("poc-has-page2").value = "0";
    
    modal.classList.add("open");
    document.body.classList.add("modal-open");
    
    const tbody = document.getElementById("alm-tbody-poc-p1");
    if (tbody) tbody.innerHTML = '<tr><td colspan="10" style="text-align:center; padding:20px;"><div class="alm-spinner"></div> Cargando datos...</td></tr>';
    
    fetch(`${window.almacenRoutes.getOtData}?ot=${ot}&type=casting`)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                window.pocAvailableClasses = data.clases || [];
                window.pocState.moldura = data.moldura || "N/A";
                
                if (data.folio) {
                    window.pocState.page1.folio = data.folio;
                    window.pocState.page2.folio = data.folio;
                }
                
                if (data.pre_ordenes && data.pre_ordenes.length > 0) {
                    const po1 = data.pre_ordenes[0];
                    window.pocState.page1.proveedor = po1.proveedor || '';
                    window.pocState.page1.fecha = po1.fecha_creacion ? String(po1.fecha_creacion).split(/[ T]/)[0] : window.pocState.page1.fecha;
                    window.pocState.page1.folio = po1.folio || window.pocState.page1.folio;
                    window.pocState.page1.observaciones = po1.observaciones || '';
                    let filas1 = po1.filas;
                    if (typeof filas1 === 'string') filas1 = JSON.parse(filas1);
                    if (Array.isArray(filas1)) {
                        window.pocState.page1.filas = filas1;
                    }
                    
                    if (data.pre_ordenes.length > 1) {
                        window.pocState.has_page2 = true;
                        document.getElementById("poc-has-page2").value = "1";
                        const po2 = data.pre_ordenes[1];
                        window.pocState.page2.proveedor = po2.proveedor || '';
                        window.pocState.page2.fecha = po2.fecha_creacion ? String(po2.fecha_creacion).split(/[ T]/)[0] : window.pocState.page2.fecha;
                        window.pocState.page2.folio = po2.folio || window.pocState.page2.folio;
                        window.pocState.page2.observaciones = po2.observaciones || '';
                        let filas2 = po2.filas;
                        if (typeof filas2 === 'string') filas2 = JSON.parse(filas2);
                        if (Array.isArray(filas2)) {
                            window.pocState.page2.filas = filas2;
                        }
                    }
                } else {
                    let classesToUse = (data.clases && data.clases.length > 0) ? data.clases : [];
                    if (classesToUse.length === 0 && data.clases_vinculadas) {
                        classesToUse = data.clases_vinculadas.map(c => ({ nombre: c }));
                    }
                    
                    classesToUse.forEach(c => {
                        const className = c.nombre || c.clase || (typeof c === 'string' ? c : '');
                        const classId = c.id || className;
                        window.pocState.page1.filas.push({
                            id_clase: classId,
                            tipo_modelo: '',
                            cant_fabricar: '',
                            cant_consignacion: 0,
                            descripcion: className,
                            clase_nombre: className,
                            clase: className,
                            material: 'Hierro Gris',
                            codigo: window.autoGenerarCodigo('', className, ot),
                            peso_juego: 0,
                            peso_total: 0,
                            fecha_entrega: data.fecha_entrega || ''
                        });
                    });
                    
                    if (window.pocState.page1.filas.length === 0) {
                        window.agregarFilaPoc(1);
                    }
                }
                window.loadPocPage(1);
                window.switchPocPage(1);
                const btnAdd = document.getElementById("btn-add-poc-page-2");
                const btnRemove = document.getElementById("btn-remove-poc-page-2");
                const tab2 = document.getElementById("tab-poc-page-2");
                const totalClassesAvailable = (window.pocAvailableClasses || []).length;

                if (window.pocState.has_page2) {
                    if (btnAdd) {
                        btnAdd.classList.add("alm-display-none", "cal-display-none");
                        btnAdd.style.display = "none";
                    }
                    if (btnRemove) btnRemove.classList.remove("alm-display-none", "cal-display-none");
                    if (tab2) tab2.classList.remove("alm-display-none", "cal-display-none");
                    window.loadPocPage(2);
                } else {
                    if (btnRemove) btnRemove.classList.add("alm-display-none", "cal-display-none");
                    if (tab2) tab2.classList.add("alm-display-none", "cal-display-none");
                    if (totalClassesAvailable <= 1) {
                        if (btnAdd) {
                            btnAdd.classList.add("alm-display-none", "cal-display-none");
                            btnAdd.style.display = "none";
                        }
                    } else {
                        if (btnAdd) {
                            btnAdd.classList.remove("alm-display-none", "cal-display-none");
                            btnAdd.style.display = "inline-flex";
                        }
                    }
                }
                window.updatePocAddRowButtonState();
            } else {
                almacenToast(data.message || "Error al cargar datos", "error");
                window.cerrarModalPreOrdenCasting();
            }
        })
        .catch(e => {
            console.error(e);
            almacenToast("Error de conexión", "error");
            window.cerrarModalPreOrdenCasting();
        });
};

window.cerrarModalPreOrdenCasting = function() {
    const modal = document.getElementById("modalPreOrdenCasting");
    if (modal) {
        modal.classList.remove("open");
        document.body.classList.remove("modal-open");
    }
};

window.switchPocPage = function(pageNum) {
    document.querySelectorAll(".poc-page").forEach(p => p.classList.add("alm-display-none", "cal-display-none"));
    document.querySelectorAll(".btn-po-tab").forEach(b => {
        b.classList.remove("active");
        b.style.background = "rgba(255,255,255,0.2)";
        b.style.color = "#ffffff";
        b.style.boxShadow = "none";
    });
    
    const page = document.getElementById("poc-page-" + pageNum);
    const tab = document.getElementById("tab-poc-page-" + pageNum);
    
    if (page) {
        page.classList.remove("alm-display-none", "cal-display-none");
    }
    if (tab) {
        tab.classList.add("active");
        tab.style.background = "#ffffff";
        tab.style.color = "#0369a1";
        tab.style.boxShadow = "0 -4px 12px rgba(0,0,0,0.15)";
    }
};

window.autoSelectOtherProveedor = function (targetPageNum) {
    const otherPageNum = targetPageNum === 1 ? 2 : 1;
    const currentSelect = document.getElementById(`poc-p${targetPageNum}-proveedor`);
    const otherSelect = document.getElementById(`poc-p${otherPageNum}-proveedor`);
    if (!currentSelect) return;

    const otherVal = otherSelect ? (otherSelect.value || window.pocState[`page${otherPageNum}`].proveedor || "") : "";
    const currentVal = currentSelect.value || window.pocState[`page${targetPageNum}`].proveedor || "";

    if (!currentVal || (otherVal && currentVal === otherVal)) {
        for (let i = 0; i < currentSelect.options.length; i++) {
            const optVal = currentSelect.options[i].value;
            if (optVal && optVal !== otherVal && !currentSelect.options[i].disabled) {
                currentSelect.value = optVal;
                window.pocState[`page${targetPageNum}`].proveedor = optVal;
                break;
            }
        }
    }
};

window.updatePocAddRowButtonState = function () {
    const totalAvailable = (window.pocAvailableClasses || []).length;
    const btn1 = document.getElementById("btn-add-row-poc-p1");
    const btn2 = document.getElementById("btn-add-row-poc-p2");
    const btnAddP2 = document.getElementById("btn-add-poc-page-2");

    if (btnAddP2) {
        if (totalAvailable <= 1 || window.pocState.has_page2) {
            btnAddP2.classList.add("alm-display-none", "cal-display-none");
            btnAddP2.style.display = "none";
        } else {
            btnAddP2.classList.remove("alm-display-none", "cal-display-none");
            btnAddP2.style.display = "inline-flex";
        }
    }

    if (totalAvailable <= 0) {
        if (btn1) btn1.style.display = "inline-flex";
        if (btn2) btn2.style.display = "inline-flex";
        return;
    }

    const filasP1 = window.pocState.page1.filas || [];
    const filasP2 = window.pocState.has_page2 ? (window.pocState.page2.filas || []) : [];

    const totalUsed = filasP1.length + filasP2.length;
    const allAssigned = totalUsed >= totalAvailable;

    if (btn1) {
        if (allAssigned || filasP1.length >= totalAvailable) {
            btn1.style.display = "none";
        } else {
            btn1.style.display = "inline-flex";
        }
    }

    if (btn2) {
        if (allAssigned || filasP2.length >= totalAvailable) {
            btn2.style.display = "none";
        } else {
            btn2.style.display = "inline-flex";
        }
    }
};

window.agregarPocPagina2 = function() {
    document.getElementById("poc-has-page2").value = "1";
    window.pocState.has_page2 = true;
    
    // Heredar folio de la página 1 si el de la página 2 está vacío
    if (!window.pocState.page2.folio && window.pocState.page1.folio) {
        window.pocState.page2.folio = window.pocState.page1.folio;
    }
    
    window.autoSelectOtherProveedor(2);
    
    const btnAdd = document.getElementById("btn-add-poc-page-2");
    const btnRemove = document.getElementById("btn-remove-poc-page-2");
    const tab2 = document.getElementById("tab-poc-page-2");
    
    if (btnAdd) {
        btnAdd.classList.add("alm-display-none", "cal-display-none");
        btnAdd.style.display = "none";
    }
    if (btnRemove) btnRemove.classList.remove("alm-display-none", "cal-display-none");
    if (tab2) tab2.classList.remove("alm-display-none", "cal-display-none");
    
    if (window.pocState.page2.filas.length === 0) {
        window.agregarFilaPoc(2);
    } else {
        window.loadPocPage(2);
    }
    window.switchPocPage(2);
    window.updatePocAddRowButtonState();
};

window.removerPocPagina2 = function() {
    if (!confirm("¿Estás seguro de eliminar el Proveedor 2? Se perderán los datos ingresados en esa pestaña.")) return;
    document.getElementById("poc-has-page2").value = "0";
    window.pocState.has_page2 = false;
    window.pocState.page2.filas = [];
    
    const btnAdd = document.getElementById("btn-add-poc-page-2");
    const btnRemove = document.getElementById("btn-remove-poc-page-2");
    const tab2 = document.getElementById("tab-poc-page-2");
    const totalClassesAvailable = (window.pocAvailableClasses || []).length;
    
    if (btnAdd) {
        if (totalClassesAvailable <= 1) {
            btnAdd.classList.add("alm-display-none", "cal-display-none");
            btnAdd.style.display = "none";
        } else {
            btnAdd.classList.remove("alm-display-none", "cal-display-none");
            btnAdd.style.display = "inline-flex";
        }
    }
    if (btnRemove) btnRemove.classList.add("alm-display-none", "cal-display-none");
    if (tab2) tab2.classList.add("alm-display-none", "cal-display-none");
    
    window.switchPocPage(1);
    window.updatePocAddRowButtonState();
};

window.handlePocProveedorChange = function(pageNum) {
    window.savePocPageData(pageNum);
    if (window.pocState.has_page2) {
        const otherPageNum = pageNum === 1 ? 2 : 1;
        window.autoSelectOtherProveedor(otherPageNum);
    }
};

window.loadPocPage = function(pageNum) {
    if (!pageNum || (pageNum !== 1 && pageNum !== 2)) return;
    const pData = window.pocState["page" + pageNum];
    if (!pData) return;
    
    const provEl = document.getElementById(`poc-p${pageNum}-proveedor`);
    const folioEl = document.getElementById(`poc-p${pageNum}-folio`);
    const obsEl = document.getElementById(`poc-p${pageNum}-observaciones`);
    const otEl = document.getElementById(`poc-p${pageNum}-ot`);
    const molduraEl = document.getElementById(`poc-p${pageNum}-moldura`);
    const fechaEl = document.getElementById(`poc-p${pageNum}-fecha`);

    if (provEl) provEl.value = pData.proveedor || "";
    if (folioEl) folioEl.value = pData.folio || window.pocState.page1.folio || "";
    if (obsEl) obsEl.value = pData.observaciones || "";
    if (otEl) otEl.value = window.pocState.ot_raw || "";
    if (molduraEl) molduraEl.value = window.pocState.moldura || "";
    if (fechaEl) fechaEl.value = (pData.fecha && pData.fecha !== 'undefined') ? String(pData.fecha).split(/[ T]/)[0] : "";

    const tbody = document.getElementById(`alm-tbody-poc-p${pageNum}`);
    if (tbody) {
        tbody.innerHTML = "";
        if (!pData.filas || pData.filas.length === 0) {
            tbody.innerHTML = `<tr><td colspan="10" style="text-align:center; padding:20px; color:#64748b; font-style:italic;">No hay modelos agregados.</td></tr>`;
            window.updatePocAddRowButtonState();
            return;
        }

        pData.filas.forEach((f, idx) => {
            const tr = document.createElement("tr");
            
            const cantFab = (f.cant_fabricar !== undefined && f.cant_fabricar !== null && f.cant_fabricar !== '' && f.cant_fabricar !== 'undefined' && f.cant_fabricar !== 0) ? f.cant_fabricar : '';
            const cantCons = (f.cant_consignacion !== undefined && f.cant_consignacion !== null && f.cant_consignacion !== '' && f.cant_consignacion !== 'undefined' && f.cant_consignacion !== 0) ? f.cant_consignacion : '';
            const codigoVal = (f.codigo !== undefined && f.codigo !== null && f.codigo !== 'undefined') ? f.codigo : (f.codigo_modelo ?? '');
            const pesoJuegoVal = (f.peso_juego !== undefined && f.peso_juego !== null && f.peso_juego !== '' && f.peso_juego !== 'undefined' && f.peso_juego !== 0) ? f.peso_juego : '';
            const pesoTotalVal = (f.peso_total !== undefined && f.peso_total !== null && f.peso_total !== '' && f.peso_total !== 'undefined' && f.peso_total !== 0) ? f.peso_total : '';
            const fechaEntregaVal = (f.fecha_entrega && f.fecha_entrega !== 'undefined') ? String(f.fecha_entrega).split(/[ T]/)[0] : '';
            
            const selectedClass = f.id_clase || f.descripcion || f.clase_nombre || f.clase || '';
            const optionsClases = (window.pocAvailableClasses || []).map(c => {
                const classId = c.id || c.nombre;
                const isSel = selectedClass && (selectedClass == classId || selectedClass == c.nombre || selectedClass == c.id);
                return `<option value="${classId}" ${isSel ? 'selected' : ''}>${c.nombre}</option>`;
            }).join('');
            
            const customMatOptions = (window.materialesCastingPersonalizados || []).map(m => 
                `<option value="${m}" ${f.material === m ? 'selected' : ''}>${m}</option>`
            ).join('');
            
            const fixedMatOptions = window.MATERIALES_CASTING_FIJOS.map(m => 
                `<option value="${m}" ${f.material === m ? 'selected' : ''}>${m}</option>`
            ).join('');
            
            const isSingleRow = pData.filas.length <= 1;
            const btnDelAttrs = isSingleRow
                ? 'disabled title="Mínimo debe conservar una clase por proveedor" style="padding:4px 8px; background:#cbd5e1; color:#94a3b8; border:none; border-radius:4px; cursor:not-allowed;"'
                : 'style="padding:4px 8px; background:#ef4444; color:white; border:none; border-radius:4px; cursor:pointer;"';

            tr.innerHTML = `
                <td>
                    <select class="poc-input-tipo form-control" style="width:100%" required onchange="window.handlePocRowInputChange(this)">
                        <option value="">Seleccione...</option>
                        <option value="Placa" ${f.tipo_modelo === 'Placa' ? 'selected' : ''}>Placa</option>
                        <option value="Suelto" ${f.tipo_modelo === 'Suelto' ? 'selected' : ''}>Suelto</option>
                        <option value="Templadera" ${f.tipo_modelo === 'Templadera' || f.tipo_modelo === 'Múltiple' ? 'selected' : ''}>Templadera</option>
                    </select>
                </td>
                <td><input type="number" min="0" step="1" class="poc-input-cant-fabricar form-control" style="width:100%" value="${cantFab}" placeholder="0" required oninput="window.handlePocRowInputChange(this)"></td>
                <td><input type="number" min="0" step="1" class="poc-input-cant-consignacion form-control" style="width:100%; background-color:#dcfce7; border:1.5px solid #16a34a; color:#15803d; font-weight:700;" value="${cantCons}" placeholder="0" required oninput="window.handlePocRowInputChange(this)"></td>
                <td>
                    <select class="poc-select-clase form-control" style="width:100%" required onchange="window.handlePocRowInputChange(this)">
                        <option value="">-- Clase --</option>
                        ${optionsClases}
                    </select>
                </td>
                <td class="poc-material-wrapper">
                    <select class="poc-input-material form-control" style="width:100%" required onchange="window.handlePocMaterialChange(${pageNum}, ${idx}, this)">
                        <option value="">-- Material --</option>
                        ${fixedMatOptions}
                        ${customMatOptions}
                        <option value="Otro">Otro (Especificar)</option>
                    </select>
                    <input type="text" class="poc-input-material-custom form-control alm-display-none" style="width:100%; margin-top:5px;" placeholder="Nuevo material..." onblur="window.handlePocMaterialCustomBlur(${pageNum}, ${idx}, this)" onkeypress="window.handlePocMaterialCustomKey(${pageNum}, ${idx}, event, this)">
                </td>
                <td><input type="text" class="poc-input-codigo form-control" style="width:100%" value="${codigoVal}" required ${f.user_edited_code ? 'data-user-edited="1"' : ''}></td>
                <td><input type="number" min="0" step="0.001" class="poc-input-peso-juego form-control" style="width:100%" value="${pesoJuegoVal}" placeholder="0" required oninput="window.handlePocRowInputChange(this)"></td>
                <td><input type="number" min="0" step="0.001" class="poc-input-peso-total form-control" style="width:100%; background-color:#dcfce7; border:1.5px solid #16a34a; color:#15803d; font-weight:700;" value="${pesoTotalVal}" placeholder="0" readonly></td>
                <td><input type="date" class="poc-input-fecha-entrega form-control" style="width:100%" value="${fechaEntregaVal}" required></td>
                <td style="text-align:center;">
                    <button type="button" class="btn-eliminar" onclick="window.eliminarFilaPoc(${pageNum}, ${idx})" ${btnDelAttrs}><i class="fas fa-trash"></i></button>
                </td>
            `;
            tbody.appendChild(tr);
        });
        window.updatePocAddRowButtonState();
    }
};

window.calcularConsignacion = function (fabricar, tipo, claseNombre = "") {
    const fab = parseInt(fabricar, 10) || 0;
    if (fab <= 0) return 0;

    const tLow = (tipo || "").toLowerCase();
    const cLow = (claseNombre || "").toLowerCase();
    const esTempladera =
        tLow.includes("templadera") ||
        cLow.includes("templadera") ||
        tLow.includes("múltiple") ||
        tLow.includes("multiple");

    if (esTempladera) {
        // CALCULADORA DE TEMPLADERAS (Tabla 2)
        // [1-6]: 100%, [7-12]: 50%, [13-80]: 33%, [>=81]: 20%. Max 30 juegos por clase.
        let pct = 1.0;
        if (fab <= 6) {
            pct = 1.0;
        } else if (fab <= 12) {
            pct = 0.5;
        } else if (fab <= 80) {
            pct = 0.33;
        } else {
            pct = 0.2;
        }
        const calc = Math.ceil(fab * pct);
        return Math.min(30, calc);
    } else {
        // CALCULADORA DE CONSIGNACIONES (Tabla 1: Moldes, Bombillos, Fondos, Obturadores)
        // [1-6]: 50%, [7-12]: 30%, [13-24]: 20%, [25-50]: 10%, [51-80]: 8%, [>=81]: 5%.
        let pct = 0.5;
        if (fab <= 6) {
            pct = 0.5;
        } else if (fab <= 12) {
            pct = 0.3;
        } else if (fab <= 24) {
            pct = 0.2;
        } else if (fab <= 50) {
            pct = 0.1;
        } else if (fab <= 80) {
            pct = 0.08;
        } else {
            pct = 0.05;
        }
        return Math.ceil(fab * pct);
    }
};

window.autoGenerarCodigo = window.autoGenerarCodigo || function(tipo, claseNombre, ot) {
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
// ── DYNAMIC SINGLE MATERIAL LOGIC & POC HOOKS ──

window.handlePocMaterialChange = function (pageNum, idx, selectEl) {
    if (selectEl.value === "Otro") {
        // Mostrar input personalizado
        const wrapper = selectEl.closest(".poc-material-wrapper");
        const customInput =
            wrapper && wrapper.querySelector(".poc-input-material-custom");
        if (customInput) {
            customInput.classList.remove("alm-display-none");
            customInput.value = "";
            customInput.focus();
        }
        selectEl.classList.add("alm-display-none");
        return;
    }
    savePocPageData(pageNum);
    loadPocPage(pageNum);
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
        // Cancelar y restaurar select
        inputEl.classList.add("alm-display-none");
        if (selectEl) selectEl.classList.remove("alm-display-none");
        return;
    }
    savePocPageData(pageNum);
    const pData = pocState["page" + pageNum];
    const row = pData.filas[idx];
    const materialesDisponibles = [
        ...MATERIALES_CASTING_FIJOS,
        ...window.materialesCastingPersonalizados,
    ];
    if (!materialesDisponibles.includes(val)) {
        if (materialesDisponibles.length >= 7) {
            almacenToast(
                "Límite de 7 materiales en el selector alcanzado.",
                "error",
            );
            inputEl.classList.add("alm-display-none");
            if (selectEl) selectEl.classList.remove("alm-display-none");
            loadPocPage(pageNum);
            return;
        }
        window.materialesCastingPersonalizados.push(val);
    }
    row.material = val;
    inputEl.classList.add("alm-display-none");
    if (selectEl) selectEl.classList.remove("alm-display-none");
    // Recargar vista para actualizar todos los dropdowns
    loadPocPage(pageNum);
}

window.eliminarMaterialGlobal = function (pageNum, mat) {
    savePocPageData(pageNum);
    // 1. Quitar de la lista global de personalizados
    window.materialesCastingPersonalizados =
        window.materialesCastingPersonalizados.filter((m) => m !== mat);
    // 2. Limpiar la selección de cualquier fila que usara este material
    const paginas = [pocState.page1, pocState.page2];
    paginas.forEach((p) => {
        if (p && p.filas) {
            p.filas.forEach((f) => {
                if (f.material === mat) {
                    f.material = ""; // Resetea a vacío
                }
            });
        }
    });

    almacenToast(`Material "${mat}" eliminado de las opciones.`, "success");
    loadPocPage(pageNum);
};

window.handlePocClaseChange = function (pageNum, idx, selectEl) {
    savePocPageData(pageNum);
    const pData = pocState["page" + pageNum];
    const row = pData.filas[idx];
    const selectedOption = selectEl.options[selectEl.selectedIndex];
    if (selectedOption && selectedOption.value) {
        row.id_clase = selectedOption.value;
        row.descripcion = selectedOption.text;
        if (!row.user_edited_code) {
            row.codigo = window.autoGenerarCodigo(
                row.tipo_modelo || "",
                selectedOption.text,
                pocState.ot_raw,
            );
        }
    }
    loadPocPage(pageNum);
};

// Mark codigo & consignacion as user-edited when touched
document.addEventListener("input", function (e) {
    if (e.target.classList.contains("poc-input-codigo")) {
        const tr = e.target.closest("tr");
        if (tr) {
            tr.dataset.userEditedCode = "1";
        }
    }
    if (e.target.classList.contains("poc-input-cant-consignacion")) {
        const tr = e.target.closest("tr");
        if (tr) {
            tr.dataset.userEditedConsignacion = "1";
        }
    }
});

window.handlePocRowInputChange = function (el) {
    const tr = el.closest("tr");
    if (!tr) return;

    const fabInput = tr.querySelector(".poc-input-cant-fabricar");
    const consInput = tr.querySelector(".poc-input-cant-consignacion");
    const tipoSel = tr.querySelector(".poc-input-tipo");
    const selectClase = tr.querySelector(".poc-select-clase");
    const codInput = tr.querySelector(".poc-input-codigo");
    const juegoInput = tr.querySelector(".poc-input-peso-juego");
    const totalInput = tr.querySelector(".poc-input-peso-total");

    const fabVal = parseInt(fabInput ? fabInput.value : 0) || 0;
    const tipoVal = tipoSel ? tipoSel.value : "";
    const selOpt = selectClase ? selectClase.options[selectClase.selectedIndex] : null;
    const claseDesc = (selOpt && selOpt.value) ? selOpt.text : "";

    // Track user manual edits on consignacion input
    if (el === consInput) {
        tr.dataset.userEditedConsignacion = "1";
    }

    // Auto-calculate consignación if user didn't edit it manually
    if (el === fabInput || el === tipoSel || el === selectClase) {
        if (tr.dataset.userEditedConsignacion !== "1" && fabVal > 0) {
            const autoCons = window.calcularConsignacion(fabVal, tipoVal, claseDesc);
            if (consInput) {
                consInput.value = autoCons;
            }
        }
    }

    // Auto-generate código if user didn't edit it manually
    if (el === tipoSel || el === selectClase) {
        if (tr.dataset.userEditedCode !== "1" && (claseDesc || tipoVal)) {
            const autoCod = window.autoGenerarCodigo(tipoVal, claseDesc, window.pocState.ot_raw);
            if (codInput) {
                codInput.value = autoCod;
            }
        }
    }

    // Auto-calculate peso_total = (cant_fabricar + cant_consignacion) * peso_juego
    const consVal = parseInt(consInput ? consInput.value : 0) || 0;
    const juegoVal = parseFloat(juegoInput ? juegoInput.value : 0) || 0;
    const totalPiezas = fabVal + consVal;
    const pesoTotal = parseFloat((totalPiezas * juegoVal).toFixed(3));
    if (totalInput) {
        totalInput.value = pesoTotal > 0 ? pesoTotal : 0;
    }
};

window.recalcPocRowWeight = function (pageNum, idx) {
    const tbody = document.getElementById(`alm-tbody-poc-p${pageNum}`);
    if (tbody && tbody.children[idx]) {
        const fabInput = tbody.children[idx].querySelector(".poc-input-cant-fabricar");
        if (fabInput) window.handlePocRowInputChange(fabInput);
    }
};

window.agregarFilaPoc = function (pageNum) {
    savePocPageData(pageNum);
    pocState["page" + pageNum].filas.push({
        id_clase: "",
        tipo_modelo: "",
        cant_fabricar: "",
        cant_consignacion: "",
        descripcion: "",
        material: "Hierro Gris",
        codigo: "",
        peso_juego: "",
        peso_total: "",
        fecha_entrega: "",
    });
    loadPocPage(pageNum);
};

window.eliminarFilaPoc = function (pageNum, idx) {
    savePocPageData(pageNum);
    const pFilas = pocState["page" + pageNum].filas || [];
    if (pFilas.length <= 1) {
        if (typeof almacenToast === "function") {
            almacenToast("Cada proveedor debe conservar al menos una clase.", "warning");
        } else {
            alert("Cada proveedor debe conservar al menos una clase.");
        }
        return;
    }
    pocState["page" + pageNum].filas.splice(idx, 1);
    loadPocPage(pageNum);
};

window.savePocPageData = function (pageNum) {
    if (!pageNum || (pageNum !== 1 && pageNum !== 2)) return;
    const pData = pocState["page" + pageNum];
    if (!pData) return;
    const provEl = document.getElementById(`poc-p${pageNum}-proveedor`);
    const folioEl = document.getElementById(`poc-p${pageNum}-folio`);
    const obsEl = document.getElementById(`poc-p${pageNum}-observaciones`);
    if (provEl) pData.proveedor = provEl.value;
    if (!pData.fecha) pData.fecha = new Date().toISOString().substring(0, 10);
    if (folioEl && folioEl.value) pData.folio = folioEl.value;
    if (obsEl) pData.observaciones = obsEl.value;
    const tbody = document.getElementById(`alm-tbody-poc-p${pageNum}`);
    if (tbody) {
        const rows = tbody.querySelectorAll("tr");
        rows.forEach((tr, idx) => {
            const rowState = pData.filas[idx];
            if (!rowState) return;
            const tipoSel = tr.querySelector(".poc-input-tipo");
            rowState.tipo_modelo = tipoSel ? tipoSel.value : rowState.tipo_modelo || "";
            const rawCant = tr.querySelector(".poc-input-cant-fabricar")?.value;
            rowState.cant_fabricar = (rawCant !== undefined && rawCant !== "") ? parseInt(rawCant) : 0;
            
            const selectClase = tr.querySelector(".poc-select-clase");
            if (selectClase) {
                rowState.id_clase = selectClase.value;
                const selOpt = selectClase.options[selectClase.selectedIndex];
                if (selOpt && selOpt.value) {
                    rowState.descripcion = selOpt.text;
                    rowState.clase_nombre = selOpt.text;
                    rowState.clase = selOpt.text;
                }
            }

            // User edit tracking
            const codInput = tr.querySelector(".poc-input-codigo");
            if (tr.dataset.userEditedCode === "1") {
                rowState.user_edited_code = true;
            }
            const consInput = tr.querySelector(".poc-input-cant-consignacion");
            if (tr.dataset.userEditedConsignacion === "1") {
                rowState.user_edited_consignacion = true;
            }

            // Consignación auto-calcular
            let cantCons = parseInt(consInput ? consInput.value : 0) || 0;
            if (!rowState.user_edited_consignacion && rowState.cant_fabricar > 0) {
                cantCons = window.calcularConsignacion(rowState.cant_fabricar, rowState.tipo_modelo, rowState.descripcion);
            }
            rowState.cant_consignacion = cantCons;

            // Código auto-generar
            let codigoVal = codInput ? codInput.value : (rowState.codigo || "");
            if (!rowState.user_edited_code && (rowState.descripcion || rowState.tipo_modelo)) {
                codigoVal = window.autoGenerarCodigo(
                    rowState.tipo_modelo || "",
                    rowState.descripcion || "",
                    pocState.ot_raw || ""
                );
            }
            rowState.codigo = codigoVal;

            // Material: leer el select actual
            const matSel = tr.querySelector(".poc-input-material");
            if (matSel && matSel.value && matSel.value !== "Otro") {
                rowState.material = matSel.value;
            } else if (!rowState.material) {
                rowState.material = "Hierro Gris";
            }
            
            rowState.peso_juego = parseFloat(tr.querySelector(".poc-input-peso-juego")?.value) || 0;
            const fabNum = parseInt(rowState.cant_fabricar) || 0;
            const consNum = parseInt(rowState.cant_consignacion) || 0;
            rowState.peso_total = parseFloat(((fabNum + consNum) * rowState.peso_juego).toFixed(3)) || 0;
            rowState.fecha_entrega = tr.querySelector(".poc-input-fecha-entrega")?.value || "";
        });
    }
};

// Exponer savePocPageData a global
window.savePocPageData = window.savePocPageData;

// ── Envío Pre-Orden Casting ──
document.addEventListener("DOMContentLoaded", () => {
    const formPoc = document.getElementById("formPreOrdenCasting");
    if (formPoc) {
        formPoc.addEventListener("submit", function(e) {
            e.preventDefault();
            window.savePocPageData(1);
            if (window.pocState.has_page2) window.savePocPageData(2);
            
            const p1 = window.pocState.page1;
            p1.ot_raw = window.pocState.ot_raw;
            p1.ot = window.pocState.ot_raw;
            p1.moldura = window.pocState.moldura;
            
            let p2 = null;
            if (window.pocState.has_page2) {
                p2 = window.pocState.page2;
                p2.ot_raw = window.pocState.ot_raw;
                p2.ot = window.pocState.ot_raw;
                p2.moldura = window.pocState.moldura;
            }

            if (!p1.proveedor) {
                almacenToast("Debe seleccionar un proveedor para la página 1.", "error");
                return;
            }
            
            if (!p1.filas || p1.filas.length === 0) {
                almacenToast("El Proveedor 1 debe tener al menos una clase asignada.", "error");
                return;
            }
            let invalidRow1 = p1.filas.find((f) => !f.tipo_modelo || (!f.id_clase && !f.descripcion));
            if (invalidRow1) {
                almacenToast("Debe seleccionar el Tipo de Modelo y la Clase para todas las filas del Proveedor 1.", "error");
                return;
            }

            if (window.pocState.has_page2) {
                if (!p2 || !p2.proveedor) {
                    almacenToast("Debe seleccionar un proveedor para la página 2.", "error");
                    return;
                }
                if (!p2.filas || p2.filas.length === 0) {
                    almacenToast("El Proveedor 2 debe tener al menos una clase asignada.", "error");
                    return;
                }
            }
            if (window.pocState.has_page2 && p2) {
                let invalidRow2 = p2.filas.find((f) => !f.tipo_modelo || (!f.id_clase && !f.descripcion));
                if (invalidRow2) {
                    almacenToast("Debe seleccionar el Tipo de Modelo y la Clase para todas las filas del Proveedor 2.", "error");
                    return;
                }
            }
            
            const payload = {
                ot: window.pocState.ot_raw,
                type: 'casting',
                has_page2: window.pocState.has_page2 ? 1 : 0,
                page1: p1,
                page2: p2
            };
            
            const btnSubmit = document.getElementById("btn-submit-poc");
            let originalText = '';
            if (btnSubmit) {
                originalText = btnSubmit.innerHTML;
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = `<i class="fas fa-spinner fa-spin" style="margin-right: 8px;"></i> Procesando...`;
            }
            
            const targetUrl = (window.almacenRoutes && (window.almacenRoutes.generarPreOrden || window.almacenRoutes.storePreOrden))
                ? (window.almacenRoutes.generarPreOrden || window.almacenRoutes.storePreOrden)
                : '/almacen/fundicion/store-preorden';

            fetch(targetUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify(payload)
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    almacenToast(data.message || "Pre-Orden generada.", "success");
                    
                    if (data.pdfs && Array.isArray(data.pdfs) && data.pdfs.length > 0) {
                        data.pdfs.forEach(p => {
                            if (p.url) {
                                const a = document.createElement("a");
                                a.href = p.url;
                                a.download = p.filename || "PreOrden_Casting.pdf";
                                a.target = "_blank";
                                document.body.appendChild(a);
                                a.click();
                                setTimeout(() => {
                                    if (document.body.contains(a)) document.body.removeChild(a);
                                }, 500);
                            }
                        });
                    }
                    
                    window.cerrarModalPreOrdenCasting();
                    setTimeout(() => location.reload(), 1500);
                } else {
                    almacenToast(data.message || "Error al guardar.", "error");
                    if (btnSubmit) {
                        btnSubmit.disabled = false;
                        btnSubmit.innerHTML = originalText;
                    }
                }
            })
            .catch(e => {
                console.error(e);
                almacenToast("Error de conexión", "error");
                if (btnSubmit) {
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = originalText;
                }
            });
        });
    }
});
