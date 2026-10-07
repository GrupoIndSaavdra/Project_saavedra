// --- INITIALIZATION & MODAL LOGIC ---
window.pocState = {
    ot_raw: '',
    moldura: '',
    pages: {} // { 1: { proveedor: '', fecha: '', folio: '', observaciones: '', filas: [] }, ... }
};
window.materialesCastingPersonalizados = [];
window.MATERIALES_CASTING_FIJOS = ["Hierro Gris", "Hierro Nodular", "Acero al Carbón", "Acero Inoxidable", "Bronce", "Aluminio"];
window.pocAvailableClasses = [];

window.abrirModalPreOrdenCasting = function(ot) {
    const modal = document.getElementById("modalPreOrdenCasting");
    if (!modal) return;
    
    window.pocState = {
        ot_raw: ot,
        moldura: '',
        pages: {}
    };
    window.materialesCastingPersonalizados = [];
    
    const subtitle = document.getElementById("poc-modal-subtitle");
    if (subtitle) {
        const hasOtPrefix = /^OT\s*:/i.test(ot) || /^OT\s+/i.test(ot);
        subtitle.textContent = hasOtPrefix ? (ot.startsWith("OT:") ? ot : "OT: " + ot.replace(/^OT\s*/i, "")) : "OT: " + ot;
    }
    
    modal.classList.add("open");
    document.body.classList.add("modal-open");
    
    const tabsContainer = document.getElementById("poc-tabs-container");
    const pagesContainer = document.getElementById("poc-pages-container");
    if (tabsContainer) tabsContainer.innerHTML = '<div style="color:white; padding: 10px;">Cargando...</div>';
    if (pagesContainer) pagesContainer.innerHTML = '<div style="text-align:center; padding: 2em;">Cargando datos...</div>';
    
    fetch(`${window.almacenRoutes.getOtData}?ot=${ot}&type=casting`)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                let classesToUse = (data.clases && data.clases.length > 0) ? data.clases : [];
                if (classesToUse.length === 0 && data.clases_vinculadas) {
                    classesToUse = data.clases_vinculadas.map(c => typeof c === 'string' ? { nombre: c, proveedor: '', material: 'Hierro Gris' } : { nombre: c.nombre, proveedor: c.proveedor || '', material: c.material || 'Hierro Gris' });
                }
                
                window.pocAvailableClasses = classesToUse;
                window.pocState.moldura = data.moldura || "N/A";
                
                let pagesCount = 0;
                
                if (data.pre_ordenes && data.pre_ordenes.length > 0) {
                    data.pre_ordenes.forEach((po, i) => {
                        pagesCount++;
                        let filas = po.filas;
                        if (typeof filas === 'string') filas = JSON.parse(filas);
                        window.pocState.pages[pagesCount] = {
                            proveedor: po.proveedor || '',
                            fecha: po.fecha_creacion ? String(po.fecha_creacion).split(/[ T]/)[0] : new Date().toISOString().substring(0, 10),
                            folio: po.folio || data.folio || '',
                            observaciones: po.observaciones || '',
                            filas: Array.isArray(filas) ? filas : []
                        };
                    });
                } else {
                    let providersMap = {};
                    classesToUse.forEach(c => {
                        let prov = c.proveedor || "SIN_PROVEEDOR";
                        if (!providersMap[prov]) providersMap[prov] = [];
                        providersMap[prov].push(c);
                    });
                    
                    for (let prov in providersMap) {
                        pagesCount++;
                        let filas = [];
                        providersMap[prov].forEach(c => {
                            const className = c.nombre || c.clase || (typeof c === 'string' ? c : '');
                            const classId = c.id || className;
                            filas.push({
                                id_clase: classId,
                                tipo_modelo: '',
                                cant_fabricar: '',
                                cant_consignacion: 0,
                                descripcion: className,
                                clase_nombre: className,
                                clase: className,
                                material: c.material || 'Hierro Gris',
                                codigo: window.autoGenerarCodigo('', className, ot),
                                peso_juego: 0,
                                peso_total: 0,
                                fecha_entrega: data.fecha_entrega || ''
                            });
                        });
                        
                        window.pocState.pages[pagesCount] = {
                            proveedor: prov === "SIN_PROVEEDOR" ? "" : prov,
                            fecha: new Date().toISOString().substring(0, 10),
                            folio: data.folio || '',
                            observaciones: '',
                            filas: filas
                        };
                    }
                    
                    if (pagesCount === 0) {
                        pagesCount = 1;
                        window.pocState.pages[1] = {
                            proveedor: '',
                            fecha: new Date().toISOString().substring(0, 10),
                            folio: data.folio || '',
                            observaciones: '',
                            filas: []
                        };
                        window.agregarFilaPoc(1);
                    }
                }
                
                window.renderPocTabsAndPages();
                window.switchPocPage(1);
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

window.renderPocTabsAndPages = function() {
    const tabsContainer = document.getElementById("poc-tabs-container");
    const pagesContainer = document.getElementById("poc-pages-container");
    if (!tabsContainer || !pagesContainer) return;
    
    let tabsHtml = '';
    let pagesHtml = '';
    
    const pagesKeys = Object.keys(window.pocState.pages);
    
    pagesKeys.forEach((pageNumKey, index) => {
        const pageNum = parseInt(pageNumKey);
        const isActive = index === 0;
        
        // Tab button
        let tabContent = `<div style="display: inline-flex; align-items: stretch; margin-right: 8px; border-radius: 30px; transition: all 0.3s ease; ${isActive ? 'box-shadow: 0 4px 15px rgba(0,0,0,0.1), 0 0 0 4px rgba(255,255,255,0.2); transform: translateY(-2px);' : 'backdrop-filter: blur(4px);'}">`;
        
        tabContent += `<button type="button" id="tab-poc-page-${pageNum}" onclick="window.switchPocPage(${pageNum})" class="btn-po-tab ${isActive ? 'active' : ''}" style="
            background: ${isActive ? '#ffffff' : 'rgba(255,255,255,0.15)'}; 
            color: ${isActive ? '#0284c7' : '#ffffff'}; 
            border: ${isActive ? 'none' : '1px solid rgba(255,255,255,0.3)'}; 
            ${pagesKeys.length > 1 ? 'border-right: none;' : ''}
            padding: 10px 24px; 
            border-top-left-radius: 30px; 
            border-bottom-left-radius: 30px; 
            ${pagesKeys.length > 1 ? 'border-top-right-radius: 0; border-bottom-right-radius: 0;' : 'border-radius: 30px;'}
            font-family: 'Poppins', sans-serif; 
            font-weight: ${isActive ? '700' : '500'}; 
            font-size: 0.95em; 
            cursor: pointer; 
            display: inline-flex; 
            align-items: center; 
            justify-content: center;
            outline: none;
            "
            onmouseover="if(!this.classList.contains('active')){this.style.background='rgba(255,255,255,0.25)';}"
            onmouseout="if(!this.classList.contains('active')){this.style.background='rgba(255,255,255,0.15)';}"
            >
            <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 180px;">
                ${window.pocState.pages[pageNumKey].proveedor ? window.pocState.pages[pageNumKey].proveedor : `Proveedor ${pageNum}`}
            </span>
        </button>`;

        if (pagesKeys.length > 1) {
            tabContent += `<button type="button" id="tab-poc-close-${pageNum}" onclick="event.stopPropagation(); window.removerPocPagina(${pageNum});" style="
                background: ${isActive ? '#ffffff' : 'rgba(255,255,255,0.15)'}; 
                color: ${isActive ? '#ef4444' : '#fca5a5'};
                border: ${isActive ? 'none' : '1px solid rgba(255,255,255,0.3)'};
                border-left: ${isActive ? '1px solid #e2e8f0' : '1px solid rgba(255,255,255,0.2)'};
                padding: 0 16px;
                border-top-right-radius: 30px;
                border-bottom-right-radius: 30px;
                font-family: 'Poppins', sans-serif; 
                font-size: 1.4em;
                font-weight: 700;
                cursor: pointer;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                transition: all 0.2s ease;
                outline: none;
                "
                title="Remover proveedor"
                onmouseover="this.style.backgroundColor='${this.previousElementSibling?.classList.contains('active') ? '#fee2e2' : 'rgba(255,255,255,0.3)'}'; this.style.color='${this.previousElementSibling?.classList.contains('active') ? '#b91c1c' : '#ffffff'}';"
                onmouseout="this.style.backgroundColor='${this.previousElementSibling?.classList.contains('active') ? '#ffffff' : 'rgba(255,255,255,0.15)'}'; this.style.color='${this.previousElementSibling?.classList.contains('active') ? '#ef4444' : '#fca5a5'}';"
                >
                &times;
            </button>`;
        }
        tabContent += `</div>`;
        tabsHtml += tabContent;
        
        // Page content
        pagesHtml += `
        <div id="poc-page-${pageNum}" class="poc-page ${isActive ? '' : 'alm-display-none cal-display-none'}">
            <div class="form-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 18px; margin-bottom: 25px; background: #ffffff; padding: 20px 24px; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
                <div class="form-group">
                    <label for="poc-p${pageNum}-proveedor" style="font-weight: 700; color: #0f172a; font-size: 0.95em; margin-bottom: 8px; display: block;">Proveedor <span style="color: #dc2626;">*</span>:</label>
                    <select id="poc-p${pageNum}-proveedor" name="page${pageNum}_proveedor" onchange="window.handlePocProveedorChange(${pageNum})" class="form-control" style="width: 100%; height: 44px; padding: 8px 14px; border-radius: 10px; border: 1.5px solid #0284c7; font-family: 'Poppins', sans-serif; font-size: 0.95em; color: #0f172a; background: #ffffff; box-shadow: 0 2px 4px rgba(2,132,199,0.05);" required>
                        <option value="" disabled selected>-- Selecciona un proveedor --</option>
                        <option value="SS Metal Foundry, S. de R. L. de C. V.">SS Metal Foundry, S. de R. L. de C. V.</option>
                        <option value="SOCIEDAD COOPERATIVA DE PRODUCCIÓN JACARANDAS">SOCIEDAD COOPERATIVA DE PRODUCCIÓN JACARANDAS</option>
                        <option value="EXTERNO">EXTERNO</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="poc-p${pageNum}-fecha" style="font-weight: 700; color: #475569; font-size: 0.95em; margin-bottom: 8px; display: block;">Fecha Creación:</label>
                    <input type="date" id="poc-p${pageNum}-fecha" name="page${pageNum}_fecha" class="form-control" style="width: 100%; height: 44px; padding: 8px 14px; border-radius: 10px; border: 1.5px solid #cbd5e1; font-family: 'Poppins', sans-serif; font-size: 0.95em; background: #f1f5f9; color: #64748b; font-weight: 600;" readonly disabled>
                </div>
                <div class="form-group">
                    <label for="poc-p${pageNum}-folio" style="font-weight: 700; color: #475569; font-size: 0.95em; margin-bottom: 8px; display: block;">Folio:</label>
                    <input type="text" id="poc-p${pageNum}-folio" name="page${pageNum}_folio" class="form-control" style="width: 100%; height: 44px; padding: 8px 14px; border-radius: 10px; border: 1.5px solid #cbd5e1; font-family: 'Poppins', sans-serif; font-size: 0.95em; background: #f1f5f9; color: #0369a1; font-weight: 800;" readonly>
                </div>
                <div class="form-group">
                    <label for="poc-p${pageNum}-moldura" style="font-weight: 700; color: #475569; font-size: 0.95em; margin-bottom: 8px; display: block;">Moldura:</label>
                    <input type="text" id="poc-p${pageNum}-moldura" name="page${pageNum}_moldura" class="form-control" style="width: 100%; height: 44px; padding: 8px 14px; border-radius: 10px; border: 1.5px solid #cbd5e1; font-family: 'Poppins', sans-serif; font-size: 0.95em; background: #f1f5f9; color: #334155;" readonly>
                </div>
                <div class="form-group">
                    <label for="poc-p${pageNum}-ot" style="font-weight: 700; color: #475569; font-size: 0.95em; margin-bottom: 8px; display: block;">Orden de Trabajo:</label>
                    <input type="text" id="poc-p${pageNum}-ot" name="page${pageNum}_ot" class="form-control" style="width: 100%; height: 44px; padding: 8px 14px; border-radius: 10px; border: 1.5px solid #cbd5e1; font-family: 'Poppins', sans-serif; font-size: 0.95em; background: #f1f5f9; color: #334155;" readonly>
                </div>
            </div>

            <div class="modal-table-container" style="overflow-x: auto; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 16px; padding: 0; box-shadow: 0 4px 16px rgba(0,0,0,0.04);">
                <table class="modal-table" style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff; font-weight: 700; font-size: 0.88em; text-transform: uppercase; letter-spacing: 0.5px;">
                            <th style="padding: 14px 12px; min-width: 120px;">Tipo de Modelo <span style="color: #f87171;">*</span></th>
                            <th style="padding: 14px 12px; min-width: 95px;">Cant. Fabricar <span style="color: #f87171;">*</span></th>
                            <th style="padding: 14px 12px; min-width: 95px;">Cant. Consign. <span style="color: #f87171;">*</span></th>
                            <th style="padding: 14px 12px; min-width: 140px;">Descripción / Clase <span style="color: #f87171;">*</span></th>
                            <th style="padding: 14px 12px; min-width: 160px;">Material <span style="color: #f87171;">*</span></th>
                            <th style="padding: 14px 12px; min-width: 130px;">Código Modelo <span style="color: #f87171;">*</span></th>
                            <th style="padding: 14px 12px; min-width: 90px;">Peso Juego</th>
                            <th style="padding: 14px 12px; min-width: 90px;">Peso Total</th>
                            <th style="padding: 14px 12px; min-width: 130px;">Fecha Entrega <span style="color: #f87171;">*</span></th>
                            <th style="padding: 14px 12px; text-align: center; min-width: 70px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="alm-tbody-poc-p${pageNum}">
                    </tbody>
                </table>
                <div style="margin: 16px 0; text-align: center;">
                    <button type="button" id="btn-add-row-poc-p${pageNum}" onclick="window.agregarFilaPoc(${pageNum})" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 24px; background: #f0f9ff; border: 2px dashed #0284c7; border-radius: 30px; color: #0284c7; font-weight: 700; font-family: 'Poppins', sans-serif; font-size: 0.92em; cursor: pointer; transition: all 0.2s ease;">
                        <span>+ Añadir otra clase / modelo</span>
                    </button>
                </div>
            </div>

            <div class="form-group" style="margin-top: 25px;">
                <label for="poc-p${pageNum}-observaciones" style="font-weight: 700; color: #0f172a; font-size: 0.95em; margin-bottom: 8px; display: block;">Observaciones (Proveedor ${pageNum}):</label>
                <textarea id="poc-p${pageNum}-observaciones" name="page${pageNum}_observaciones" style="width: 100%; min-height: 80px; border-radius: 12px; padding: 14px; font-family: 'Poppins', sans-serif; font-size: 0.95em; border: 1.5px solid #cbd5e1; box-sizing: border-box;" placeholder="Escribe observaciones adicionales para el proveedor ${pageNum}..."></textarea>
            </div>
        </div>`;
    });
    
    // Add "Agregar Proveedor" button to tabs
    // Add "Agregar Proveedor" button to tabs
    let availableClasesCount = window.pocAvailableClasses ? window.pocAvailableClasses.length : 1;
    let limitReached = pagesKeys.length >= availableClasesCount;
    let addBtnStyle = limitReached 
        ? "align-items: center; gap: 8px; padding: 10px 24px; background: rgba(255, 255, 255, 0.05); border: 1.5px dashed rgba(255, 255, 255, 0.2); border-radius: 30px; color: rgba(255,255,255,0.4); font-family: 'Poppins', sans-serif; font-size: 0.95em; font-weight: 500; margin-left: 8px; cursor: not-allowed; transition: all 0.3s ease;"
        : "align-items: center; gap: 8px; padding: 10px 24px; background: rgba(255, 255, 255, 0.1); border: 1.5px dashed rgba(255, 255, 255, 0.6); border-radius: 30px; color: #ffffff; cursor: pointer; font-family: 'Poppins', sans-serif; font-size: 0.95em; font-weight: 600; transition: all 0.3s ease; margin-left: 8px;";
        
    tabsHtml += `
        <button type="button" id="btn-add-poc-page" onclick="${limitReached ? 'return false;' : 'window.agregarPocPagina()'}" class="btns btn-add-tab" style="${addBtnStyle}" ${limitReached ? 'title="Se ha alcanzado el límite de proveedores (uno por cada clase)" disabled' : ''}>
            + Agregar Proveedor
        </button>
    `;
    
    tabsContainer.innerHTML = tabsHtml;
    pagesContainer.innerHTML = pagesHtml;
    
    // Load data for all pages
    pagesKeys.forEach(pageNumKey => {
        window.loadPocPage(parseInt(pageNumKey));
    });
};

window.agregarPocPagina = function() {
    let newPageNum = 1;
    while(window.pocState.pages[newPageNum]) {
        newPageNum++;
    }
    
    window.pocState.pages[newPageNum] = {
        proveedor: '',
        fecha: new Date().toISOString().substring(0, 10),
        folio: window.pocState.pages[1] ? window.pocState.pages[1].folio : '',
        observaciones: '',
        filas: []
    };
    
    window.agregarFilaPoc(newPageNum);
    window.renderPocTabsAndPages();
    window.switchPocPage(newPageNum);
};

window.removerPocPagina = function(pageNum) {
    if (Object.keys(window.pocState.pages).length <= 1) {
        almacenToast("Debe haber al menos un proveedor.", "warning");
        return;
    }
    if (!confirm(`¿Estás seguro de eliminar el Proveedor ${pageNum}? Se perderán los datos ingresados en esta pestaña.`)) return;
    
    delete window.pocState.pages[pageNum];
    window.renderPocTabsAndPages();
    
    const remainingKeys = Object.keys(window.pocState.pages);
    window.switchPocPage(parseInt(remainingKeys[0]));
};

window.switchPocPage = function(pageNum) {
    document.querySelectorAll(".poc-page").forEach(p => p.classList.add("alm-display-none", "cal-display-none"));
    
    const pagesKeys = Object.keys(window.pocState.pages);
    
    // Reset all tabs to inactive styles
    document.querySelectorAll(".btn-po-tab").forEach(b => {
        if (!b.id.includes("btn-add-poc-page")) {
            b.classList.remove("active");
            b.style.background = "rgba(255,255,255,0.15)";
            b.style.color = "#ffffff";
            b.style.border = "1px solid rgba(255,255,255,0.3)";
            if (pagesKeys.length > 1) {
                b.style.borderRight = "none";
            }
            if (b.parentElement && b.parentElement.tagName === 'DIV') {
                b.parentElement.style.boxShadow = "none";
                b.parentElement.style.transform = "none";
                b.parentElement.style.backdropFilter = "blur(4px)";
            }
        }
    });

    // Reset all close buttons
    pagesKeys.forEach(p => {
        let c = document.getElementById("tab-poc-close-" + p);
        if (c) {
            c.style.background = "rgba(255,255,255,0.15)";
            c.style.color = "#fca5a5";
            c.style.border = "1px solid rgba(255,255,255,0.3)";
            c.style.borderLeft = "1px solid rgba(255,255,255,0.2)";
        }
    });
    
    const page = document.getElementById("poc-page-" + pageNum);
    const tab = document.getElementById("tab-poc-page-" + pageNum);
    const closeBtn = document.getElementById("tab-poc-close-" + pageNum);
    
    if (page) {
        page.classList.remove("alm-display-none", "cal-display-none");
    }
    if (tab) {
        tab.classList.add("active");
        tab.style.background = "#ffffff";
        tab.style.color = "#0284c7";
        tab.style.border = "none";
        if (tab.parentElement && tab.parentElement.tagName === 'DIV') {
            tab.parentElement.style.boxShadow = "0 4px 15px rgba(0,0,0,0.1), 0 0 0 4px rgba(255,255,255,0.2)";
            tab.parentElement.style.transform = "translateY(-2px)";
            tab.parentElement.style.backdropFilter = "none";
        }
    }
    if (closeBtn) {
        closeBtn.style.background = "#ffffff";
        closeBtn.style.color = "#ef4444";
        closeBtn.style.border = "none";
        closeBtn.style.borderLeft = "1px solid #e2e8f0";
    }
};

window.updatePocAddRowButtonState = function () {
    // Left empty on purpose because renderPocTabsAndPages now handles dynamic elements
};

window.handlePocProveedorChange = function(pageNum) {
    window.savePocPageData(pageNum);

    const pSel = document.getElementById(`poc-p${pageNum}-proveedor`);
    if (!pSel || !pSel.value) return;
    
    const currentProv = pSel.value;
    const pagesKeys = Object.keys(window.pocState.pages);
    
    for (let key of pagesKeys) {
        if (key != pageNum && window.pocState.pages[key].proveedor === currentProv) {
            if (window.pocState.pages[pageNum].filas && window.pocState.pages[pageNum].filas.length > 0) {
                window.pocState.pages[key].filas = window.pocState.pages[key].filas.concat(window.pocState.pages[pageNum].filas);
            }
            
            if (window.pocState.pages[pageNum].observaciones) {
                window.pocState.pages[key].observaciones = window.pocState.pages[key].observaciones 
                    ? window.pocState.pages[key].observaciones + "\n" + window.pocState.pages[pageNum].observaciones 
                    : window.pocState.pages[pageNum].observaciones;
            }
            
            delete window.pocState.pages[pageNum];
            
            window.renderPocTabsAndPages();
            window.switchPocPage(parseInt(key));
            window.loadPocPage(parseInt(key));
            
            if (typeof almacenToast !== 'undefined') {
                almacenToast(`Modelos agrupados con el proveedor existente.`, "info");
            }
            return;
        }
    }
};

window.loadPocPage = function(pageNum) {
    if (!pageNum) return;
    const pData = window.pocState.pages[pageNum];
    if (!pData) return;
    
    const provEl = document.getElementById(`poc-p${pageNum}-proveedor`);
    const folioEl = document.getElementById(`poc-p${pageNum}-folio`);
    const obsEl = document.getElementById(`poc-p${pageNum}-observaciones`);
    const otEl = document.getElementById(`poc-p${pageNum}-ot`);
    const molduraEl = document.getElementById(`poc-p${pageNum}-moldura`);
    const fechaEl = document.getElementById(`poc-p${pageNum}-fecha`);

    if (provEl) provEl.value = pData.proveedor || "";
    if (folioEl) folioEl.value = pData.folio || window.pocState.pages[1].folio || "";
    if (obsEl) obsEl.value = pData.observaciones || "";
    if (otEl) {
        const rawOt = window.pocState.ot_raw || "";
        const cleanOt = rawOt.split(" - ")[0].trim();
        otEl.value = cleanOt || rawOt;
    }
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
            
            if (f.material && !window.MATERIALES_CASTING_FIJOS.includes(f.material) && f.material !== 'Otro') {
                if (!window.materialesCastingPersonalizados.includes(f.material)) {
                    window.materialesCastingPersonalizados.push(f.material);
                }
            }
            
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
                    <input type="text" class="poc-input-material form-control" style="width:100%; opacity:0.8; cursor:not-allowed; background-color:#f1f5f9; color:#475569; font-weight:600;" value="${f.material || ''}" readonly disabled>
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
    const pData = pocState.pages[pageNum];
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
    const paginas = Object.values(window.pocState.pages);
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
    const pData = pocState.pages[pageNum];
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
    
    // Auto-populate material when class changes
    if (el === selectClase && selectClase) {
        const matInput = tr.querySelector(".poc-input-material");
        if (matInput) {
            const classObj = (window.pocAvailableClasses || []).find(c => c.id == selectClase.value || c.nombre == selectClase.value);
            if (classObj && classObj.material) {
                matInput.value = classObj.material;
            } else {
                matInput.value = "Hierro Gris";
            }
            // Trigger save and reload to update state
            const tbody = tr.closest("tbody");
            if (tbody) {
                const pageNumMatch = tbody.id.match(/poc-p(\d+)/);
                if (pageNumMatch) {
                    window.savePocPageData(pageNumMatch[1]);
                }
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
    pocState.pages[pageNum].filas.push({
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
    const pFilas = pocState.pages[pageNum].filas || [];
    if (pFilas.length <= 1) {
        if (typeof almacenToast === "function") {
            almacenToast("Cada proveedor debe conservar al menos una clase.", "warning");
        } else {
            alert("Cada proveedor debe conservar al menos una clase.");
        }
        return;
    }
    pocState.pages[pageNum].filas.splice(idx, 1);
    loadPocPage(pageNum);
};

window.savePocPageData = function (pageNum) {
    if (!pageNum) return;
    const pData = pocState.pages[pageNum];
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
            
            let allValid = true;
            let pagesPayload = [];
            
            Object.keys(window.pocState.pages).forEach(pageNumKey => {
                const pageNum = parseInt(pageNumKey);
                window.savePocPageData(pageNum);
                
                const p = window.pocState.pages[pageNum];
                p.ot_raw = window.pocState.ot_raw;
                p.ot = window.pocState.ot_raw;
                p.moldura = window.pocState.moldura;
                
                if (!p.proveedor) {
                    almacenToast(`Debe seleccionar un proveedor para la pestaña Proveedor ${pageNum}.`, "error");
                    allValid = false;
                }
                
                if (!p.filas || p.filas.length === 0) {
                    almacenToast(`El Proveedor ${pageNum} debe tener al menos una clase asignada.`, "error");
                    allValid = false;
                }
                
                let invalidRow = p.filas.find((f) => !f.tipo_modelo || (!f.id_clase && !f.descripcion));
                if (invalidRow) {
                    almacenToast(`Debe seleccionar el Tipo de Modelo y la Clase para todas las filas del Proveedor ${pageNum}.`, "error");
                    allValid = false;
                }
                
                pagesPayload.push(p);
            });
            
            if (!allValid) return;
            
            const payload = {
                ot: window.pocState.ot_raw,
                type: 'casting',
                pages: pagesPayload
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
