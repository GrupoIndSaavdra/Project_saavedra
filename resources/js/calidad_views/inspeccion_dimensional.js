/**
 * inspeccion_dimensional.js
 * Módulo de Reporte Dimensional (Calidad)
 * Grupo Industrial Saavedra
 */
import * as XLSX from 'xlsx';

document.addEventListener('DOMContentLoaded', () => {

    /* ═══════════════════════════════════════════════════════════════
       1. VISTA DE SELECCIÓN (INDEX)
       ═══════════════════════════════════════════════════════════════ */
    const selectOt = document.getElementById('select-ot');
    const selectClase = document.getElementById('select-clase');
    const grupoClase = document.getElementById('grupo-clase');
    const grupoBtnAbrir = document.getElementById('grupo-btn-abrir');
    const btnAbrir = document.getElementById('btn-abrir-reporte');
    const btnAbrirVisual = document.getElementById('btn-abrir-visual');
    const otInfoBox = document.getElementById('ot-info-box');
    const tabVisual = document.getElementById('tab-visual');

    const infoOtMoldura = document.getElementById('info-ot-moldura');
    const infoMetricsGroup = document.getElementById('info-metrics-group');
    const cardPedido = document.getElementById('card-pedido');
    const infoOtPedido = document.getElementById('info-ot-pedido');
    const operatorPlus = document.getElementById('operator-plus');
    const cardConsignacion = document.getElementById('card-consignacion');
    const infoOtConsignacion = document.getElementById('info-ot-consignacion');
    const operatorEquals = document.getElementById('operator-equals');
    const cardTotal = document.getElementById('card-total');
    const infoOtTotal = document.getElementById('info-ot-total');

    if (selectOt) {
        const data = window.otClasesData || [];

        function updateOtInfo(otId) {
            if (!otId || !otInfoBox) {
                if (otInfoBox) otInfoBox.style.display = 'none';
                return;
            }

            const otItems = data.filter(item => String(item.ot_id) === String(otId));
            if (otItems.length > 0) {
                const firstItem = otItems[0];
                const moldura = firstItem.nombre_moldura || '—';

                if (infoOtMoldura) infoOtMoldura.textContent = moldura;

                const selectedClaseVal = selectClase ? selectClase.value : '';
                if (selectedClaseVal) {
                    const classItem = otItems.find(i => i.clase_nombre === selectedClaseVal);
                    if (classItem) {
                        const piezas = Math.round(Number(classItem.clase_piezas || 0));
                        const pedido = Math.round(Number(classItem.clase_pedido || 0));
                        const consignacion = (piezas > pedido) ? (piezas - pedido) : 0;
                        const total = piezas > 0 ? piezas : pedido;

                        if (infoOtPedido) infoOtPedido.textContent = pedido > 0 ? pedido : total;

                        if (consignacion > 0) {
                            if (infoOtConsignacion) infoOtConsignacion.textContent = consignacion;
                            if (infoOtTotal) infoOtTotal.textContent = total;
                            if (cardConsignacion) cardConsignacion.style.display = 'flex';
                            if (operatorPlus) operatorPlus.style.display = 'block';
                            if (operatorEquals) operatorEquals.style.display = 'block';
                            if (cardTotal) cardTotal.style.display = 'flex';
                        } else {
                            if (cardConsignacion) cardConsignacion.style.display = 'none';
                            if (operatorPlus) operatorPlus.style.display = 'none';
                            if (operatorEquals) operatorEquals.style.display = 'none';
                            if (cardTotal) cardTotal.style.display = 'none';
                        }

                        if (infoMetricsGroup) infoMetricsGroup.style.display = 'flex';
                    } else {
                        if (infoMetricsGroup) infoMetricsGroup.style.display = 'none';
                    }
                } else {
                    if (infoMetricsGroup) infoMetricsGroup.style.display = 'none';
                }

                otInfoBox.style.display = 'flex';
            }
        }

        function populateClases(otId, selectedClaseVal = null) {
            selectClase.innerHTML = '<option value="">— Selecciona una clase —</option>';

            if (!otId) {
                if (grupoClase) grupoClase.style.display = 'none';
                if (grupoBtnAbrir) grupoBtnAbrir.style.display = 'none';
                if (otInfoBox) otInfoBox.style.display = 'none';
                return;
            }

            const clases = data.filter(item => String(item.ot_id) === String(otId));

            if (clases.length > 0) {
                clases.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.clase_nombre;
                    opt.textContent = item.clase_nombre;
                    if (selectedClaseVal && item.clase_nombre === selectedClaseVal) {
                        opt.selected = true;
                    }
                    selectClase.appendChild(opt);
                });

                if (grupoClase) grupoClase.style.display = 'flex';

                if (clases.length === 1 && !selectedClaseVal) {
                    selectClase.selectedIndex = 1;
                    sessionStorage.setItem('gis_calidad_selected_clase', selectClase.value);
                }

                if (selectClase.value) {
                    if (grupoBtnAbrir) grupoBtnAbrir.style.display = 'flex';
                } else {
                    if (grupoBtnAbrir) grupoBtnAbrir.style.display = 'none';
                }
            } else {
                if (grupoClase) grupoClase.style.display = 'none';
                if (grupoBtnAbrir) grupoBtnAbrir.style.display = 'none';
            }

            updateOtInfo(otId);
        }

        selectOt.addEventListener('change', () => {
            const otId = selectOt.value;
            if (otId) {
                sessionStorage.setItem('gis_calidad_selected_ot', otId);
                sessionStorage.removeItem('gis_calidad_selected_clase');
            } else {
                sessionStorage.removeItem('gis_calidad_selected_ot');
                sessionStorage.removeItem('gis_calidad_selected_clase');
            }
            populateClases(otId);
        });

        if (selectClase) {
            selectClase.addEventListener('change', () => {
                const claseVal = selectClase.value;
                if (claseVal) {
                    sessionStorage.setItem('gis_calidad_selected_clase', claseVal);
                    if (grupoBtnAbrir) grupoBtnAbrir.style.display = 'flex';
                } else {
                    sessionStorage.removeItem('gis_calidad_selected_clase');
                    if (grupoBtnAbrir) grupoBtnAbrir.style.display = 'none';
                }
                updateOtInfo(selectOt.value);
            });
        }

        const urlParams = new URLSearchParams(window.location.search);
        const initialOt = urlParams.get('ot') || sessionStorage.getItem('gis_calidad_selected_ot');
        const initialClase = urlParams.get('clase') || sessionStorage.getItem('gis_calidad_selected_clase');

        if (initialOt) {
            selectOt.value = initialOt;
            if (selectOt.value) {
                populateClases(initialOt, initialClase);
            }
        }

        if (tabVisual) {
            tabVisual.addEventListener('click', () => {
                const currentOt = selectOt.value;
                const currentClase = selectClase ? selectClase.value : '';
                if (currentOt) {
                    sessionStorage.setItem('gis_calidad_selected_ot', currentOt);
                    if (currentClase) sessionStorage.setItem('gis_calidad_selected_clase', currentClase);
                }
            });
        }

        if (btnAbrir) {
            btnAbrir.addEventListener('click', () => {
                const ot = selectOt.value;
                const clase = selectClase.value;

                if (!ot || !clase) {
                    showNotice('warning', 'Campos requeridos', 'Por favor selecciona la Orden de Trabajo y la Clase.');
                    return;
                }

                const routePattern = window.routes?.['calidad.inspeccion_dimensional.show'];
                if (routePattern) {
                    window.location.href = routePattern
                        .replace(':ot', encodeURIComponent(ot))
                        .replace(':clase', encodeURIComponent(clase));
                }
            });
        }

        if (btnAbrirVisual) {
            btnAbrirVisual.addEventListener('click', () => {
                const ot = selectOt.value;
                const clase = selectClase.value;

                if (!ot || !clase) {
                    showNotice('warning', 'Campos requeridos', 'Por favor selecciona la Orden de Trabajo y la Clase.');
                    return;
                }

                const routePattern = window.routes?.['calidad.inspeccion_visual.show'];
                if (routePattern) {
                    window.location.href = routePattern
                        .replace(':ot', encodeURIComponent(ot))
                        .replace(':clase', encodeURIComponent(clase));
                }
            });
        }
    }


    /* ═══════════════════════════════════════════════════════════════
       2. VISTA DE CAPTURA DEL REPORTE DIMENSIONAL (SHOW)
       ═══════════════════════════════════════════════════════════════ */
    const config = window.rdConfig;
    if (!config) return;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const autosaveStatus = document.getElementById('autosave-status');
    const statusDot = autosaveStatus?.querySelector('.rd-status-dot');
    const statusText = autosaveStatus?.querySelector('.rd-status-text');

    function setSavingState(isSaving) {
        if (!autosaveStatus) return;
        if (isSaving) {
            statusDot?.classList.add('saving');
            if (statusText) statusText.textContent = 'Guardando...';
        } else {
            statusDot?.classList.remove('saving');
            if (statusText) statusText.textContent = 'Cambios guardados';
        }
    }

    // ── Autoguardado de Encabezado / Información General ──
    document.querySelectorAll('.header-editable').forEach(input => {
        input.addEventListener('change', () => {
            const campo = input.dataset.campo;
            const valor = input.value;
            saveHeaderField(campo, valor);
        });
    });

    async function saveHeaderField(campo, valor) {
        setSavingState(true);
        try {
            const res = await fetch(config.routes.updateHeader, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    reporte_id: config.reporteId,
                    campo: campo,
                    valor: valor
                })
            });
            const data = await res.json();
            if (!res.ok || !data.success) {
                console.error('Error al guardar campo del encabezado:', data.error);
            }
        } catch (err) {
            console.error('Error de red al guardar encabezado:', err);
        } finally {
            setTimeout(() => setSavingState(false), 300);
        }
    }

    // ── Autoguardado de Celdas de Medidas y Evaluación Visual Instantánea ──
    const tbodyMedidas = document.getElementById('medidas-tbody');
    let debounceTimer = null;

    const COTAS_OBLIGATORIAS = ['c1', 'c2', 'cuello', 'e', 'd', 'f1', 'a_total', 'f', 'altura', 'altura_2', 'al_cuello'];

    function evaluateRowCompletenessLocally(tr) {
        if (!tr) return;
        let allFilled = true;
        for (const cota of COTAS_OBLIGATORIAS) {
            const input = tr.querySelector(`[data-campo="${cota}"]`);
            if (!input || input.value.trim() === '') {
                allFilled = false;
                break;
            }
        }
        tr.classList.toggle('tr-completa', allFilled);
        updateCountersLocally();
    }

    function updateCountersLocally() {
        const completedRows = document.querySelectorAll('#medidas-tbody tr[data-medida-id].tr-completa').length;
        const totalPzas = (config && config.totalPiezas) ? config.totalPiezas : 0;
        const pct = totalPzas > 0 ? (Math.round((completedRows / totalPzas) * 1000) / 10) : 0;
        updateInspectionCounters(completedRows, pct);
    }

    if (tbodyMedidas) {
        tbodyMedidas.addEventListener('input', (e) => {
            if (e.target.classList.contains('cell-editable')) {
                const tr = e.target.closest('tr');
                if (tr) {
                    evaluateRowCompletenessLocally(tr);
                }
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    saveCell(e.target);
                }, 300);
            }
        });

        tbodyMedidas.addEventListener('change', (e) => {
            if (e.target.classList.contains('cell-editable')) {
                const tr = e.target.closest('tr');
                if (tr) {
                    evaluateRowCompletenessLocally(tr);
                }
                clearTimeout(debounceTimer);
                saveCell(e.target);
                if (e.target.matches('select[data-campo="numero_pieza"]')) {
                    syncPieceNumberSelects();
                }
            }
        });

        // Eliminar fila
        tbodyMedidas.addEventListener('click', (e) => {
            const btnDel = e.target.closest('.btn-del-row');
            if (btnDel) {
                const medidaId = btnDel.dataset.medidaId;
                const tr = btnDel.closest('tr');
                confirmDeleteRow(medidaId, tr);
            }
        });
    }

    // ── Sincronizar Selects de Número de Pieza (Evitar Números Duplicados) ──
    function syncPieceNumberSelects() {
        const selects = Array.from(document.querySelectorAll('select[data-campo="numero_pieza"]'));
        if (!selects.length) return;

        const usedValues = new Set();
        selects.forEach(select => {
            if (select.value) {
                usedValues.add(String(select.value));
            }
        });

        selects.forEach(select => {
            const currentVal = String(select.value);
            Array.from(select.options).forEach(opt => {
                const optVal = String(opt.value);
                if (!optVal) return;
                if (usedValues.has(optVal) && optVal !== currentVal) {
                    opt.hidden = true;
                    opt.disabled = true;
                } else {
                    opt.hidden = false;
                    opt.disabled = false;
                }
            });
        });
    }

    // Ejecutar sincronización inicial
    syncPieceNumberSelects();

    async function saveCell(input) {
        const tr = input.closest('tr');
        const medidaId = tr.dataset.medidaId;
        const campo = input.dataset.campo;
        const valor = input.value;

        if (!medidaId || !campo) return;

        setSavingState(true);
        try {
            const res = await fetch(config.routes.autosaveMedida, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    reporte_id: config.reporteId,
                    medida_id: medidaId,
                    campo: campo,
                    valor: valor
                })
            });
            const data = await res.json();
            if (res.ok && data.success) {
                // Alternar clase de fila completa visualmente si todos los 12 campos están llenos
                tr.classList.toggle('tr-completa', !!data.fila_completa);
                // Actualizar contadores en vivo
                updateInspectionCounters(data.piezas_completas, data.porcentaje_inspeccion);
            } else {
                console.error('Error al guardar celda:', data.error);
            }
        } catch (err) {
            console.error('Error de red al guardar celda:', err);
        } finally {
            setTimeout(() => setSavingState(false), 300);
        }
    }

    // ── Autoguardado de Especificaciones (Nominales / Tolerancias) ──
    let specDebounceTimer = null;
    document.addEventListener('input', (e) => {
        if (e.target.classList.contains('spec-editable')) {
            clearTimeout(specDebounceTimer);
            specDebounceTimer = setTimeout(() => {
                saveSpec(e.target);
            }, 400);
        }
    });

    document.addEventListener('change', (e) => {
        if (e.target.classList.contains('spec-editable')) {
            clearTimeout(specDebounceTimer);
            saveSpec(e.target);
        }
    });

    async function saveSpec(input) {
        const tipo = input.dataset.tipo;
        const cota = input.dataset.cota;
        const valor = input.value;
        if (!tipo || !cota) return;

        setSavingState(true);
        try {
            const res = await fetch(config.routes.updateNominalTolerancia, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    reporte_id: config.reporteId,
                    tipo: tipo,
                    cota: cota,
                    valor: valor
                })
            });
            const data = await res.json();
            if (!res.ok || !data.success) {
                console.error('Error al guardar especificación:', data.error);
            }
        } catch (err) {
            console.error('Error de red al guardar especificación:', err);
        } finally {
            setTimeout(() => setSavingState(false), 300);
        }
    }

    // ── Restricción: Permitir únicamente números decimales (pulgadas) en cotas ──
    document.addEventListener('input', (e) => {
        if (e.target.classList.contains('input-decimal-only')) {
            let val = e.target.value.replace(/[^0-9.]/g, '');
            const parts = val.split('.');
            if (parts.length > 2) {
                val = parts[0] + '.' + parts.slice(1).join('');
            }
            if (e.target.value !== val) {
                e.target.value = val;
            }
        }
    });

    // ── Agregar Fila de Medición ──
    const btnAddRow = document.getElementById('btn-add-row');
    if (btnAddRow) {
        btnAddRow.addEventListener('click', async () => {
            const currentRows = document.querySelectorAll('#medidas-tbody tr[data-medida-id]').length;
            const totalPzas = config.totalPiezas || 0;

            if (totalPzas > 0 && currentRows >= totalPzas) {
                const elCant = document.getElementById('display-cant-inspeccionada');
                const elPorc = document.getElementById('display-porcentaje-inspeccion');
                const completas = elCant ? parseInt(elCant.textContent.trim(), 10) || 0 : 0;
                const porc = elPorc ? parseFloat(elPorc.textContent.trim()) || 0 : 0;

                let htmlContent = '';
                let iconType = 'info';
                let titleText = 'Límite de Filas Alcanzado';

                if (porc >= 100 || completas >= totalPzas) {
                    iconType = 'success';
                    titleText = '¡Inspección Completa al 100%!';
                    htmlContent = `
                        <div style="font-size: 15px; margin: 8px 0; color: #0A8504; font-weight: bold;">
                            ✓ Has alcanzado el límite de filas y completado la inspección del 100% de tus piezas
                        </div>
                        <div style="font-size: 13px; color: #334155; margin-top: 6px;">
                            Ya has llenado todas las mediciones de las <b>${totalPzas} de ${totalPzas} piezas</b> del lote (incluyendo consignación).
                        </div>
                    `;
                } else {
                    iconType = 'warning';
                    titleText = 'Límite de Filas Alcanzado';
                    htmlContent = `
                        <div style="font-size: 15px; margin: 8px 0; color: #ea580c; font-weight: bold;">
                            Ya agregaste el límite de filas del lote
                        </div>
                        <div style="font-size: 13.5px; color: #1e293b; margin-top: 6px; line-height: 1.4;">
                            Ya no puedes agregar más filas. <b>Ahora completa tus registros de medición</b>.
                        </div>
                        <div style="margin-top: 10px; font-size: 12.5px; color: #475569; background: #f1f5f9; padding: 7px 10px; border-radius: 6px;">
                            Avance actual: <b>${completas} de ${totalPzas} piezas completadas (${porc}%)</b>
                        </div>
                    `;
                }

                Swal.fire({
                    icon: iconType,
                    title: titleText,
                    html: htmlContent,
                    confirmButtonColor: '#033966',
                    confirmButtonText: 'Entendido'
                });
                return;
            }

            btnAddRow.disabled = true;
            const currentNumbers = Array.from(document.querySelectorAll('select[data-campo="numero_pieza"]'))
                .map(s => parseInt(s.value, 10))
                .filter(n => !isNaN(n));
            let nextNum = 1;
            while (currentNumbers.includes(nextNum)) {
                nextNum++;
            }

            setSavingState(true);
            try {
                const res = await fetch(config.routes.addRow, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        reporte_id: config.reporteId,
                        numero_pieza: String(nextNum)
                    })
                });

                const data = await res.json();
                if (!res.ok || !data.success) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Límite alcanzado',
                        text: data.error || 'No se puede agregar más filas a este lote.',
                        confirmButtonColor: '#033966'
                    });
                    return;
                }
                if (res.ok && data.success) {
                    const rowEmpty = document.getElementById('row-empty');
                    if (rowEmpty) rowEmpty.remove();

                    const m = data.medida;
                    let optionsHtml = '';
                    for (let i = 1; i <= totalPzas; i++) {
                        const isSelected = String(m.numero_pieza) === String(i) ? 'selected' : '';
                        optionsHtml += `<option value="${i}" ${isSelected}>${i}</option>`;
                    }

                    const tr = document.createElement('tr');
                    tr.dataset.medidaId = m.id;
                    tr.innerHTML = `
                        <td class="td-num">
                            <select class="rd-cell-select cell-editable text-center font-bold" data-campo="numero_pieza">
                                ${optionsHtml}
                            </select>
                        </td>
                        <td><input type="text" class="rd-cell-input cell-editable input-decimal-only text-center" inputmode="decimal" data-campo="c1" value=""></td>
                        <td><input type="text" class="rd-cell-input cell-editable input-decimal-only text-center" inputmode="decimal" data-campo="c2" value=""></td>
                        <td><input type="text" class="rd-cell-input cell-editable input-decimal-only text-center" inputmode="decimal" data-campo="cuello" value=""></td>
                        <td><input type="text" class="rd-cell-input cell-editable input-decimal-only text-center" inputmode="decimal" data-campo="e" value=""></td>
                        <td><input type="text" class="rd-cell-input cell-editable input-decimal-only text-center" inputmode="decimal" data-campo="d" value=""></td>
                        <td><input type="text" class="rd-cell-input cell-editable input-decimal-only text-center" inputmode="decimal" data-campo="f1" value=""></td>
                        <td><input type="text" class="rd-cell-input cell-editable input-decimal-only text-center" inputmode="decimal" data-campo="a_total" value=""></td>
                        <td><input type="text" class="rd-cell-input cell-editable input-decimal-only text-center" inputmode="decimal" data-campo="f" value=""></td>
                        <td><input type="text" class="rd-cell-input cell-editable input-decimal-only text-center" inputmode="decimal" data-campo="altura" value=""></td>
                        <td><input type="text" class="rd-cell-input cell-editable input-decimal-only text-center" inputmode="decimal" data-campo="altura_2" value=""></td>
                        <td><input type="text" class="rd-cell-input cell-editable input-decimal-only text-center" inputmode="decimal" data-campo="al_cuello" value=""></td>
                        <td><input type="text" class="rd-cell-input cell-editable text-left" data-campo="observaciones" value="" placeholder="Observaciones..."></td>
                        <td class="td-actions">
                            <button type="button" class="btn-del-row" title="Eliminar fila" data-medida-id="${m.id}">
                                &times;
                            </button>
                        </td>
                    `;
                    tbodyMedidas.appendChild(tr);

                    // Sincronizar selects para ocultar el número recién ocupado
                    syncPieceNumberSelects();

                    // Actualizar indicadores de inspección
                    updateInspectionCounters(data.cantidad_inspeccionada, data.porcentaje_inspeccion);

                    // Focalizar la nueva celda de número de pieza
                    tr.querySelector('select[data-campo="numero_pieza"]')?.focus();
                }
            } catch (err) {
                console.error('Error al agregar fila:', err);
            } finally {
                btnAddRow.disabled = false;
                setSavingState(false);
            }
        });
    }

    // ── Eliminar Fila de Medición ──
    async function confirmDeleteRow(medidaId, tr) {
        if (window.Swal) {
            const result = await Swal.fire({
                title: '¿Eliminar fila?',
                text: 'Se removerá esta pieza del reporte.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                background: '#1e293b',
                color: '#fff'
            });
            if (!result.isConfirmed) return;
        }

        setSavingState(true);
        try {
            const res = await fetch(config.routes.deleteRow, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    reporte_id: config.reporteId,
                    medida_id: medidaId
                })
            });
            const data = await res.json();
            if (res.ok && data.success) {
                tr.remove();
                syncPieceNumberSelects();
                updateInspectionCounters(data.cantidad_inspeccionada, data.porcentaje_inspeccion);
            }
        } catch (err) {
            console.error('Error al eliminar fila:', err);
        } finally {
            setSavingState(false);
        }
    }

    function updateInspectionCounters(cant, porc) {
        const elCant = document.getElementById('display-cant-inspeccionada');
        const elPorc = document.getElementById('display-porcentaje-inspeccion');
        if (elCant) elCant.textContent = cant;
        if (elPorc) elPorc.textContent = porc;

        const elCriterios = document.querySelectorAll('.display-criterio-pct');
        elCriterios.forEach(el => {
            el.textContent = porc;
        });
    }

    // ── Carga y Gestión de Archivos Adjuntos (Hasta 4 Archivos) ──
    const btnTriggerUpload = document.getElementById('btn-trigger-upload');
    const inputExcelFile = document.getElementById('input-excel-file');
    const excelFilesContainer = document.getElementById('excel-files-container');
    const excelCountBadge = document.getElementById('excel-count-badge');

    function renderDimensionalFiles(archivos) {
        if (!excelFilesContainer) return;
        
        const count = Array.isArray(archivos) ? archivos.length : 0;
        
        if (excelCountBadge) {
            excelCountBadge.textContent = `(${count}/4)`;
        }
        if (btnTriggerUpload) {
            btnTriggerUpload.disabled = (count >= 4);
        }

        if (!archivos || archivos.length === 0) {
            excelFilesContainer.innerHTML = '';
            return;
        }

        const baseDownloadUrl = (config.routes && config.routes.downloadExcel) || (`/calidad/inspeccion_dimensional/download_excel/${config.reporteId}`);
        const baseViewUrl = (config.routes && config.routes.viewExcel) || (`/calidad/inspeccion_dimensional/view_excel/${config.reporteId}`);
        
        let html = '';
        archivos.forEach(archivo => {
            const downloadUrl = archivo.download_url || (baseDownloadUrl.includes('?') 
                ? `${baseDownloadUrl}&file_id=${archivo.id}`
                : `${baseDownloadUrl}?file_id=${archivo.id}`);
            const viewUrl = archivo.view_url || (baseViewUrl.includes('?')
                ? `${baseViewUrl}&file_id=${archivo.id}`
                : `${baseViewUrl}?file_id=${archivo.id}`);
            const fechaHtml = archivo.fecha ? `<span class="rd-file-date">${archivo.fecha}</span>` : '';
            const usuarioHtml = archivo.usuario ? `
                <span class="rd-file-user" title="Subido por: ${archivo.usuario}">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    ${archivo.usuario}
                </span>
            ` : '';
            const ext = (archivo.nombre || '').split('.').pop().toLowerCase();
            const isExcel = archivo.is_excel !== undefined ? archivo.is_excel : ['xlsx', 'xls', 'csv'].includes(ext);

            html += `
                <div class="rd-excel-file-card" data-file-id="${archivo.id}">
                    <div class="rd-file-info">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                        <span class="rd-file-name" title="${archivo.nombre}">${archivo.nombre}</span>
                        ${fechaHtml}
                        ${usuarioHtml}
                        <span class="rd-file-badge">Archivo guardado</span>
                    </div>
                    <div class="rd-file-btn-group">
                        <button type="button" class="btn-file-view btn-preview-file ${isExcel ? 'btn-open-excel' : ''}" 
                            data-file-id="${archivo.id}" 
                            data-file-name="${archivo.nombre}" 
                            data-is-excel="${isExcel ? 'true' : 'false'}" 
                            data-view-url="${viewUrl}" 
                            data-download-url="${downloadUrl}" 
                            title="${isExcel ? 'Ver hoja de cálculo interactiva' : 'Ver archivo PDF'}">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            Ver
                        </button>
                        <a href="${downloadUrl}" class="btn-file-download" target="_blank" title="Descargar archivo">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                            Descargar
                        </a>
                        <button type="button" class="btn-file-delete btn-delete-excel-file" data-file-id="${archivo.id}" data-file-name="${archivo.nombre}" title="Eliminar archivo">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                            Eliminar
                        </button>
                    </div>
                </div>
            `;
        });

        excelFilesContainer.innerHTML = html;
    }

    /* ═══════════════════════════════════════════════════════════════
       VISOR INTERACTIVO DE ARCHIVOS EXCEL (SHEETJS)
       ═══════════════════════════════════════════════════════════════ */
    const excelModal = document.getElementById('excel-preview-modal');
    const excelModalBackdrop = document.getElementById('excel-modal-backdrop');
    const btnCloseExcelModal = document.getElementById('btn-close-excel-modal');
    const excelModalFilename = document.getElementById('excel-modal-filename');
    const excelModalDownloadBtn = document.getElementById('excel-modal-download-btn');
    const excelModalTabs = document.getElementById('excel-modal-tabs');
    const excelModalTableContainer = document.getElementById('excel-modal-table-container');

    let currentWorkbook = null;

    function closeExcelModal() {
        if (excelModal) {
            excelModal.style.display = 'none';
            if (excelModalTableContainer) excelModalTableContainer.innerHTML = '';
            if (excelModalTabs) excelModalTabs.innerHTML = '';
            currentWorkbook = null;
        }
    }

    if (btnCloseExcelModal) {
        btnCloseExcelModal.addEventListener('click', closeExcelModal);
    }
    if (excelModalBackdrop) {
        excelModalBackdrop.addEventListener('click', closeExcelModal);
    }
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && excelModal && excelModal.style.display !== 'none') {
            closeExcelModal();
        }
    });

    function renderExcelSheet(sheetName) {
        if (!currentWorkbook || !excelModalTableContainer) return;

        const worksheet = currentWorkbook.Sheets[sheetName];
        if (!worksheet) {
            excelModalTableContainer.innerHTML = '<div class="excel-empty-notice">No se pudo leer la hoja seleccionada.</div>';
            return;
        }

        const rawData = XLSX.utils.sheet_to_json(worksheet, { header: 1, defval: '' });
        if (!rawData || rawData.length === 0) {
            excelModalTableContainer.innerHTML = `<div class="excel-empty-notice">La hoja "${sheetName}" no contiene datos.</div>`;
            return;
        }

        let maxCols = 0;
        rawData.forEach(row => {
            if (row && row.length > maxCols) maxCols = row.length;
        });

        if (maxCols === 0) {
            excelModalTableContainer.innerHTML = '<div class="excel-empty-notice">La hoja está vacía.</div>';
            return;
        }

        function getColLetter(colIdx) {
            let temp = colIdx + 1;
            let letter = '';
            while (temp > 0) {
                let mod = (temp - 1) % 26;
                letter = String.fromCharCode(65 + mod) + letter;
                temp = Math.floor((temp - mod) / 26);
            }
            return letter;
        }

        let tableHtml = '<table class="excel-sheet-table"><thead><tr><th class="col-index">#</th>';
        for (let c = 0; c < maxCols; c++) {
            tableHtml += `<th>${getColLetter(c)}</th>`;
        }
        tableHtml += '</tr></thead><tbody>';

        rawData.forEach((row, rowIdx) => {
            tableHtml += `<tr><td class="row-index">${rowIdx + 1}</td>`;
            for (let c = 0; c < maxCols; c++) {
                const cellVal = (row && row[c] !== undefined && row[c] !== null) ? String(row[c]) : '';
                tableHtml += `<td>${cellVal || '&nbsp;'}</td>`;
            }
            tableHtml += '</tr>';
        });

        tableHtml += '</tbody></table>';
        excelModalTableContainer.innerHTML = tableHtml;
    }

    async function openExcelModal(fileName, viewUrl, downloadUrl) {
        if (!excelModal) return;

        excelModal.style.display = 'flex';
        if (excelModalFilename) excelModalFilename.textContent = fileName;
        if (excelModalDownloadBtn) {
            excelModalDownloadBtn.href = downloadUrl;
            excelModalDownloadBtn.setAttribute('download', fileName);
        }
        if (excelModalTabs) excelModalTabs.innerHTML = '';
        if (excelModalTableContainer) {
            excelModalTableContainer.innerHTML = `
                <div class="excel-loading-spinner">
                    <div class="rd-spinner"></div>
                    <span>Cargando "${fileName}"...</span>
                </div>
            `;
        }

        try {
            const res = await fetch(viewUrl);
            if (!res.ok) throw new Error('No se pudo descargar el archivo para visualización.');

            const arrayBuffer = await res.arrayBuffer();
            currentWorkbook = XLSX.read(new Uint8Array(arrayBuffer), { type: 'array' });

            const sheetNames = currentWorkbook.SheetNames || [];
            if (sheetNames.length === 0) {
                excelModalTableContainer.innerHTML = '<div class="excel-empty-notice">El libro de Excel no contiene hojas visibles.</div>';
                return;
            }

            if (excelModalTabs) {
                let tabsHtml = '';
                sheetNames.forEach((sheetName, idx) => {
                    tabsHtml += `<button type="button" class="excel-sheet-tab ${idx === 0 ? 'active' : ''}" data-sheet-name="${sheetName}">${sheetName}</button>`;
                });
                excelModalTabs.innerHTML = tabsHtml;

                excelModalTabs.querySelectorAll('.excel-sheet-tab').forEach(tabBtn => {
                    tabBtn.addEventListener('click', () => {
                        excelModalTabs.querySelectorAll('.excel-sheet-tab').forEach(b => b.classList.remove('active'));
                        tabBtn.classList.add('active');
                        renderExcelSheet(tabBtn.getAttribute('data-sheet-name'));
                    });
                });
            }

            renderExcelSheet(sheetNames[0]);

        } catch (err) {
            console.error('Error al abrir Excel en modal:', err);
            if (excelModalTableContainer) {
                excelModalTableContainer.innerHTML = `
                    <div class="excel-empty-notice" style="color: #f87171;">
                        <p style="font-weight: 700; margin-bottom: 8px;">No se pudo previsualizar la hoja de cálculo.</p>
                        <p style="font-size: 0.85rem; color: #94a3b8; margin-bottom: 16px;">Puedes descargar el archivo directamente para abrirlo en tu aplicación de Microsoft Excel.</p>
                        <a href="${downloadUrl}" class="btn-modal-excel-download" style="display: inline-flex;" download>
                            Descargar Archivo Original
                        </a>
                    </div>
                `;
            }
        }
    }

    // ── Click en Botón Ver (Preview de Archivos PDF o Excel) ──
    if (excelFilesContainer) {
        excelFilesContainer.addEventListener('click', (e) => {
            const btnPreview = e.target.closest('.btn-preview-file');
            if (btnPreview) {
                e.preventDefault();
                const fileName = btnPreview.getAttribute('data-file-name') || 'Archivo';
                const isExcel = btnPreview.getAttribute('data-is-excel') === 'true';
                const viewUrl = btnPreview.getAttribute('data-view-url');
                const downloadUrl = btnPreview.getAttribute('data-download-url') || viewUrl;

                if (isExcel) {
                    openExcelModal(fileName, viewUrl, downloadUrl);
                } else {
                    window.open(viewUrl, '_blank');
                }
            }
        });
    }

    if (btnTriggerUpload && inputExcelFile) {
        btnTriggerUpload.addEventListener('click', () => {
            const currentFiles = excelFilesContainer ? excelFilesContainer.querySelectorAll('.rd-excel-file-card').length : 0;
            if (currentFiles >= 4) {
                showNotice('warning', 'Límite alcanzado', 'Has alcanzado el límite máximo de 4 archivos adjuntos para este reporte.');
                return;
            }
            inputExcelFile.click();
        });

        inputExcelFile.addEventListener('change', async () => {
            const file = inputExcelFile.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('reporte_id', config.reporteId);
            formData.append('archivo_excel', file);

            setSavingState(true);
            btnTriggerUpload.disabled = true;
            btnTriggerUpload.innerHTML = 'Subiendo...';

            try {
                const res = await fetch(config.routes.uploadExcel, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await res.json();
                if (res.ok && data.success) {
                    renderDimensionalFiles(data.archivos);
                    showNotice('success', 'Archivo Guardado', data.message);
                } else {
                    showNotice('error', 'Error al subir', data.error || 'No se pudo cargar el archivo.');
                }
            } catch (err) {
                console.error('Error al subir Excel:', err);
                showNotice('error', 'Error', 'Ocurrió un fallo de conexión al subir el archivo.');
            } finally {
                inputExcelFile.value = '';
                const currentFiles = excelFilesContainer ? excelFilesContainer.querySelectorAll('.rd-excel-file-card').length : 0;
                btnTriggerUpload.disabled = (currentFiles >= 4);
                btnTriggerUpload.innerHTML = `
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                    <span>Subir Archivo (PDF / Excel)</span>
                    <span id="excel-count-badge" class="rd-badge-counter">(${currentFiles}/4)</span>
                `;
                setSavingState(false);
            }
        });
    }

    // ── Eliminar Archivo Adjunto Individual con Clave Maestra CALIDAD2026 ──
    if (excelFilesContainer) {
        excelFilesContainer.addEventListener('click', async (e) => {
            const btnDelete = e.target.closest('.btn-delete-excel-file');
            if (!btnDelete) return;

            const fileId = btnDelete.getAttribute('data-file-id');
            const fileName = btnDelete.getAttribute('data-file-name') || 'este archivo';

            let clave = '';
            if (window.Swal) {
                const result = await Swal.fire({
                    title: 'Eliminar Archivo Adjunto',
                    html: `
                        <p style="margin-bottom: 8px; font-size: 0.95rem; color: #e2e8f0;">
                            ¿Deseas eliminar <b>"${fileName}"</b>?
                        </p>
                        <p style="margin-bottom: 14px; font-size: 0.88rem; color: #94a3b8;">
                            Ingresa la clave maestra para confirmar:
                        </p>
                        <div style="position: relative; max-width: 320px; margin: 0 auto;">
                            <input type="password" id="swal-clave-input" placeholder="Clave maestra..." 
                                style="width: 100%; box-sizing: border-box; padding: 10px 42px 10px 14px; margin: 0; background: #02203e; color: #ffffff; border: 1.5px solid #38bdf8; border-radius: 6px; font-size: 0.95rem; outline: none; font-family: inherit;"
                                autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
                            <button type="button" id="swal-toggle-password" title="Mostrar / Ocultar contraseña" 
                                style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; padding: 4px; display: flex; align-items: center; justify-content: center; color: #94a3b8; outline: none; transition: color 0.2s;">
                                <svg id="swal-eye-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                        </div>
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#9c0303',
                    cancelButtonColor: '#404040',
                    confirmButtonText: 'Eliminar Archivo',
                    cancelButtonText: 'Cancelar',
                    background: '#033966',
                    color: '#ffffff',
                    didOpen: () => {
                        const input = document.getElementById('swal-clave-input');
                        const toggleBtn = document.getElementById('swal-toggle-password');
                        const eyeIcon = document.getElementById('swal-eye-icon');

                        if (input) {
                            input.focus();
                            input.addEventListener('keydown', (e) => {
                                if (e.key === 'Enter') {
                                    Swal.clickConfirm();
                                }
                            });
                        }

                        if (toggleBtn && input && eyeIcon) {
                            toggleBtn.addEventListener('click', () => {
                                if (input.type === 'password') {
                                    input.type = 'text';
                                    toggleBtn.style.color = '#38bdf8';
                                    eyeIcon.innerHTML = `
                                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                        <line x1="1" y1="1" x2="23" y2="23"></line>
                                    `;
                                } else {
                                    input.type = 'password';
                                    toggleBtn.style.color = '#94a3b8';
                                    eyeIcon.innerHTML = `
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    `;
                                }
                            });
                        }
                    },
                    preConfirm: () => {
                        const input = document.getElementById('swal-clave-input');
                        const val = input ? input.value.trim() : '';
                        if (!val) {
                            Swal.showValidationMessage('¡Debes ingresar la clave maestra!');
                            return false;
                        }
                        return val;
                    }
                });
                if (!result.isConfirmed) return;
                clave = result.value;
            } else {
                clave = prompt('Ingresa la clave maestra para eliminar el archivo:');
                if (!clave) return;
            }

            setSavingState(true);
            try {
                const res = await fetch(config.routes.deleteExcel, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        reporte_id: config.reporteId,
                        clave_maestra: clave,
                        file_id: fileId
                    })
                });

                const data = await res.json();
                if (res.ok && data.success) {
                    renderDimensionalFiles(data.archivos);
                    showNotice('success', 'Archivo Eliminado', data.message || 'El archivo adjunto ha sido removido.');
                } else {
                    showNotice('error', 'Error', data.error || 'No se pudo eliminar el archivo.');
                }
            } catch (err) {
                console.error('Error al eliminar archivo:', err);
                showNotice('error', 'Error', 'Ocurrió un fallo de conexión al eliminar.');
            } finally {
                setSavingState(false);
            }
        });
    }

    // ── Zoom Interactivo en el Diagrama Técnico y Modal Pantalla Completa ──
    const zoomWrapper = document.getElementById('diagram-zoom-wrapper');
    const zoomImg = document.getElementById('diagram-img');
    const btnExpandDiagram = document.getElementById('btn-expand-diagram');
    const diagramModal = document.getElementById('diagram-fullscreen-modal');
    const btnCloseDiagramModal = document.getElementById('btn-close-diagram-modal');
    const diagramModalBackdrop = document.getElementById('diagram-modal-backdrop');

    const modalZoomBox = document.getElementById('modal-zoom-box');
    const modalZoomImg = document.getElementById('modal-zoom-img');
    const btnModalZoomIn = document.getElementById('btn-modal-zoom-in');
    const btnModalZoomOut = document.getElementById('btn-modal-zoom-out');
    const btnModalZoomReset = document.getElementById('btn-modal-zoom-reset');
    const modalZoomLevel = document.getElementById('modal-zoom-level');

    let modalZoomScale = 1;
    const MODAL_MIN_ZOOM = 1;
    const MODAL_MAX_ZOOM = 3.5;
    let modalOriginX = 50;
    let modalOriginY = 50;

    function applyModalZoom(newScale, originX = null, originY = null) {
        modalZoomScale = Math.min(MODAL_MAX_ZOOM, Math.max(MODAL_MIN_ZOOM, Math.round(newScale * 100) / 100));

        if (originX !== null && originY !== null) {
            modalOriginX = originX;
            modalOriginY = originY;
        }

        if (modalZoomImg) {
            if (modalZoomScale === 1) {
                modalZoomImg.style.transformOrigin = 'center center';
                modalZoomImg.style.transform = 'scale(1)';
                if (modalZoomBox) modalZoomBox.classList.remove('is-zoomed');
            } else {
                modalZoomImg.style.transformOrigin = `${modalOriginX}% ${modalOriginY}%`;
                modalZoomImg.style.transform = `scale(${modalZoomScale})`;
                if (modalZoomBox) modalZoomBox.classList.add('is-zoomed');
            }
        }

        if (modalZoomLevel) {
            modalZoomLevel.textContent = `${Math.round(modalZoomScale * 100)}%`;
        }
    }

    if (modalZoomBox && modalZoomImg) {
        // Zoom con rueda de ratón
        modalZoomBox.addEventListener('wheel', (e) => {
            e.preventDefault();
            const rect = modalZoomBox.getBoundingClientRect();
            const x = Math.max(0, Math.min(100, ((e.clientX - rect.left) / rect.width) * 100));
            const y = Math.max(0, Math.min(100, ((e.clientY - rect.top) / rect.height) * 100));

            const delta = e.deltaY < 0 ? 0.3 : -0.3;
            applyModalZoom(modalZoomScale + delta, x, y);
        }, { passive: false });

        // Movimiento panorámico cuando hay zoom activo
        modalZoomBox.addEventListener('mousemove', (e) => {
            if (modalZoomScale > 1) {
                const rect = modalZoomBox.getBoundingClientRect();
                const x = Math.max(0, Math.min(100, ((e.clientX - rect.left) / rect.width) * 100));
                const y = Math.max(0, Math.min(100, ((e.clientY - rect.top) / rect.height) * 100));
                modalOriginX = x;
                modalOriginY = y;
                modalZoomImg.style.transformOrigin = `${x}% ${y}%`;
            }
        });

        // Alternar zoom al hacer clic en la imagen
        modalZoomImg.addEventListener('click', (e) => {
            e.stopPropagation();
            if (modalZoomScale === 1) {
                const rect = modalZoomBox.getBoundingClientRect();
                const x = Math.max(0, Math.min(100, ((e.clientX - rect.left) / rect.width) * 100));
                const y = Math.max(0, Math.min(100, ((e.clientY - rect.top) / rect.height) * 100));
                applyModalZoom(2.2, x, y);
            } else {
                applyModalZoom(1);
            }
        });
    }

    if (btnModalZoomIn) {
        btnModalZoomIn.addEventListener('click', (e) => {
            e.stopPropagation();
            applyModalZoom(modalZoomScale + 0.35);
        });
    }

    if (btnModalZoomOut) {
        btnModalZoomOut.addEventListener('click', (e) => {
            e.stopPropagation();
            applyModalZoom(modalZoomScale - 0.35);
        });
    }

    if (btnModalZoomReset) {
        btnModalZoomReset.addEventListener('click', (e) => {
            e.stopPropagation();
            applyModalZoom(1);
        });
    }

    if (zoomWrapper && zoomImg) {
        zoomWrapper.addEventListener('mousemove', (e) => {
            const rect = zoomWrapper.getBoundingClientRect();
            const x = Math.max(0, Math.min(100, ((e.clientX - rect.left) / rect.width) * 100));
            const y = Math.max(0, Math.min(100, ((e.clientY - rect.top) / rect.height) * 100));

            zoomImg.style.transformOrigin = `${x}% ${y}%`;
            zoomImg.style.transform = 'scale(2.6)';
        });

        zoomWrapper.addEventListener('mouseleave', () => {
            zoomImg.style.transform = 'scale(1)';
            zoomImg.style.transformOrigin = 'center center';
        });

        // Abrir modal al hacer clic en el contenedor de la imagen
        zoomWrapper.addEventListener('click', (e) => {
            openDiagramModal();
        });
    }

    if (btnExpandDiagram) {
        btnExpandDiagram.addEventListener('click', (e) => {
            e.stopPropagation();
            openDiagramModal();
        });
    }

    function openDiagramModal() {
        if (!diagramModal) return;
        applyModalZoom(1);
        diagramModal.style.display = 'flex';
        // Forzar reflow para animación
        void diagramModal.offsetWidth;
        diagramModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeDiagramModal() {
        if (!diagramModal) return;
        diagramModal.classList.remove('active');
        applyModalZoom(1);
        setTimeout(() => {
            diagramModal.style.display = 'none';
            document.body.style.overflow = '';
        }, 200);
    }

    if (btnCloseDiagramModal) {
        btnCloseDiagramModal.addEventListener('click', closeDiagramModal);
    }

    if (diagramModalBackdrop) {
        diagramModalBackdrop.addEventListener('click', closeDiagramModal);
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && diagramModal && diagramModal.classList.contains('active')) {
            closeDiagramModal();
        }
    });

    // ── Exportar Reporte a PDF Oficial ──
    function exportDimensionalPdf() {
        if (config.routes && config.routes.pdf) {
            window.location.href = config.routes.pdf;
        } else {
            window.print();
        }
    }

    // ── Asignar eventos a botones de descarga y envío ──
    const btnTopPdf = document.getElementById('btn-top-descargar-pdf');
    const btnBottomPdf = document.getElementById('btn-bottom-descargar-pdf');
    const btnTopSend = document.getElementById('btn-top-enviar-reporte');
    const btnBottomSend = document.getElementById('btn-bottom-enviar-reporte');

    if (btnTopPdf) btnTopPdf.addEventListener('click', exportDimensionalPdf);
    if (btnBottomPdf) btnBottomPdf.addEventListener('click', exportDimensionalPdf);

    function handleSendReport() {
        if (window.Swal) {
            Swal.fire({
                icon: 'info',
                title: 'Enviar Reporte',
                text: 'El módulo de envío y notificación por correo está en proceso de integración.',
                confirmButtonColor: '#033966'
            });
        } else {
            alert('El módulo de envío y notificación por correo está en proceso de integración.');
        }
    }

    if (btnTopSend) btnTopSend.addEventListener('click', handleSendReport);
    if (btnBottomSend) btnBottomSend.addEventListener('click', handleSendReport);

    function showNotice(icon, title, text) {
        if (window.Swal) {
            Swal.fire({
                icon: icon,
                title: title,
                text: text,
                background: '#1e293b',
                color: '#fff',
                confirmButtonColor: '#059669'
            });
        } else {
            alert(text);
        }
    }
});
