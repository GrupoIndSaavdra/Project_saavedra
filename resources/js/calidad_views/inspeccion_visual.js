/**
 * inspeccion_visual.js
 * Módulo de Inspección Visual — Desplazamiento de Molduras (Formato 4CAL-01)
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
    const btnAbrirVisual = document.getElementById('btn-abrir-visual');
    const btnAbrirDimensional = document.getElementById('btn-abrir-reporte');
    const otInfoBox = document.getElementById('ot-info-box');
    const tabDimensional = document.getElementById('tab-dimensional');

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

        if (tabDimensional) {
            tabDimensional.addEventListener('click', () => {
                const currentOt = selectOt.value;
                const currentClase = selectClase ? selectClase.value : '';
                if (currentOt) {
                    sessionStorage.setItem('gis_calidad_selected_ot', currentOt);
                    if (currentClase) sessionStorage.setItem('gis_calidad_selected_clase', currentClase);
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

        if (btnAbrirDimensional) {
            btnAbrirDimensional.addEventListener('click', () => {
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
    }


    /* ═══════════════════════════════════════════════════════════════
       2. VISTA DE CAPTURA DE DESPLAZAMIENTO DE MOLDURAS (SHOW)
       ═══════════════════════════════════════════════════════════════ */
    const config = window.rvConfig;
    if (!config) return;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const autosaveStatus = document.getElementById('autosave-status');
    const statusDot = autosaveStatus?.querySelector('.rv-status-dot');
    const statusText = autosaveStatus?.querySelector('.rv-status-text');

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

    // ── Autoguardado de Encabezado / Especificaciones de Volumen / Footer ──
    let headerDebounceTimer = null;
    document.querySelectorAll('.header-editable').forEach(input => {
        input.addEventListener('input', () => {
            clearTimeout(headerDebounceTimer);
            headerDebounceTimer = setTimeout(() => {
                saveHeaderField(input.dataset.campo, input.value);
                if (input.id === 'input-vol-ideal') {
                    recalculateAllDifferences();
                }
            }, 400);
        });

        input.addEventListener('change', () => {
            clearTimeout(headerDebounceTimer);
            saveHeaderField(input.dataset.campo, input.value);
            if (input.id === 'input-vol-ideal') {
                recalculateAllDifferences();
            }
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

    // ── Autoguardado de Celdas de la Tabla ──
    const tbodyMedidas = document.getElementById('medidas-tbody');
    let cellDebounceTimer = null;

    if (tbodyMedidas) {
        tbodyMedidas.addEventListener('input', (e) => {
            if (e.target.classList.contains('cell-editable')) {
                clearTimeout(cellDebounceTimer);
                cellDebounceTimer = setTimeout(() => {
                    handleCellInput(e.target);
                }, 400);
            }
        });

        tbodyMedidas.addEventListener('change', (e) => {
            if (e.target.classList.contains('cell-editable')) {
                clearTimeout(cellDebounceTimer);
                handleCellInput(e.target);
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

    function handleCellInput(target) {
        // Si cambió el volumen real, recalcular diferencia vs volumen ideal
        if (target.classList.contains('input-vol-real')) {
            const tr = target.closest('tr');
            calculateRowDifference(tr);
        }
        saveCell(target);
    }

    function calculateRowDifference(tr) {
        const inputVolIdeal = document.getElementById('input-vol-ideal');
        const inputVolReal = tr.querySelector('.input-vol-real');
        const inputDif = tr.querySelector('.input-dif');

        if (!inputVolIdeal || !inputVolReal || !inputDif) return;

        const volIdealVal = parseFloat(inputVolIdeal.value);
        const volRealVal = parseFloat(inputVolReal.value);

        if (!isNaN(volIdealVal) && !isNaN(volRealVal)) {
            const dif = (volRealVal - volIdealVal).toFixed(2);
            inputDif.value = dif;
            saveCell(inputDif);
        }
    }

    function recalculateAllDifferences() {
        const rows = document.querySelectorAll('#medidas-tbody tr[data-medida-id]');
        rows.forEach(tr => {
            calculateRowDifference(tr);
        });
    }

    async function saveCell(input) {
        const tr = input.closest('tr');
        const medidaId = tr?.dataset.medidaId;
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
            if (!res.ok || !data.success) {
                console.error('Error al guardar celda:', data.error);
            }
        } catch (err) {
            console.error('Error de red al guardar celda:', err);
        } finally {
            setTimeout(() => setSavingState(false), 300);
        }
    }

    // ── Restricción: Permitir únicamente números decimales ──
    document.addEventListener('input', (e) => {
        if (e.target.classList.contains('input-decimal-only')) {
            let val = e.target.value.replace(/[^0-9.-]/g, '');
            // Permitir un solo punto decimal
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
                const filledRows = Array.from(document.querySelectorAll('#medidas-tbody tr[data-medida-id]')).filter(tr => {
                    const vol = tr.querySelector('input[data-campo="vol_real"]')?.value.trim();
                    return vol !== '' && !isNaN(parseFloat(vol));
                }).length;
                const porc = totalPzas > 0 ? Math.round((filledRows / totalPzas) * 100) : 0;

                let htmlContent = '';
                let iconType = 'info';
                let titleText = 'Límite de Filas Alcanzado';

                if (porc >= 100 || filledRows >= totalPzas) {
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
                            Avance actual: <b>${filledRows} de ${totalPzas} piezas completadas (${porc}%)</b>
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
                        <td>
                            <input type="text" class="rd-cell-input cell-editable text-center font-bold" data-campo="temp_agua" value="${m.temp_agua || '20°'}" placeholder="20°">
                        </td>
                        <td>
                            <input type="text" class="rd-cell-input cell-editable input-decimal-only input-vol-real text-center font-bold" inputmode="decimal" data-campo="vol_real" value="" placeholder="0.0">
                        </td>
                        <td>
                            <input type="text" class="rd-cell-input cell-editable input-decimal-only input-dif text-center" inputmode="decimal" data-campo="dif_vs_vol_ideal" value="" placeholder="0.0">
                        </td>
                        <td>
                            <input type="text" class="rd-cell-input cell-editable text-left" data-campo="observaciones" value="" placeholder="Observaciones de la pieza...">
                        </td>
                        <td class="td-actions">
                            <button type="button" class="btn-del-row" title="Eliminar fila" data-medida-id="${m.id}">
                                &times;
                            </button>
                        </td>
                    `;
                    tbodyMedidas.appendChild(tr);

                    // Sincronizar selects para ocultar el número recién ocupado
                    syncPieceNumberSelects();

                    // Focalizar el campo de volumen real
                    tr.querySelector('.input-vol-real')?.focus();
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
                text: 'Se removerá esta medición de desplazamiento.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
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
            }
        } catch (err) {
            console.error('Error al eliminar fila:', err);
        } finally {
            setSavingState(false);
        }
    }

    // ── Carga y Gestión de Archivos Adjuntos (Hasta 4 Archivos) ──
    const btnTriggerUploadVisual = document.getElementById('btn-trigger-upload-visual');
    const inputArchivoFile = document.getElementById('input-archivo-file');
    const visualFilesContainer = document.getElementById('visual-files-container');
    const visualCountBadge = document.getElementById('visual-count-badge');

    function renderVisualFiles(archivos) {
        if (!visualFilesContainer) return;

        const count = Array.isArray(archivos) ? archivos.length : 0;

        if (visualCountBadge) {
            visualCountBadge.textContent = `(${count}/4)`;
        }
        if (btnTriggerUploadVisual) {
            btnTriggerUploadVisual.disabled = (count >= 4);
        }

        if (!archivos || archivos.length === 0) {
            visualFilesContainer.innerHTML = '';
            return;
        }

        const baseDownloadUrl = (config.routes && config.routes.downloadArchivo) || (`/calidad/inspeccion_visual/download_archivo/${config.reporteId}`);
        const baseViewUrl = (config.routes && config.routes.viewArchivo) || (`/calidad/inspeccion_visual/view_archivo/${config.reporteId}`);

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
                        <button type="button" class="btn-file-delete btn-delete-visual-file" data-file-id="${archivo.id}" data-file-name="${archivo.nombre}" title="Eliminar archivo">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                            Eliminar
                        </button>
                    </div>
                </div>
            `;
        });

        visualFilesContainer.innerHTML = html;
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
    if (visualFilesContainer) {
        visualFilesContainer.addEventListener('click', (e) => {
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

    if (btnTriggerUploadVisual && inputArchivoFile) {
        btnTriggerUploadVisual.addEventListener('click', () => {
            const currentFiles = visualFilesContainer ? visualFilesContainer.querySelectorAll('.rd-excel-file-card').length : 0;
            if (currentFiles >= 4) {
                showNotice('warning', 'Límite alcanzado', 'Has alcanzado el límite máximo de 4 archivos adjuntos para este reporte.');
                return;
            }
            inputArchivoFile.click();
        });

        inputArchivoFile.addEventListener('change', async () => {
            const file = inputArchivoFile.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('reporte_id', config.reporteId);
            formData.append('archivo', file);

            setSavingState(true);
            btnTriggerUploadVisual.disabled = true;
            btnTriggerUploadVisual.innerHTML = 'Subiendo...';

            try {
                const res = await fetch(config.routes.uploadArchivo, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await res.json();
                if (res.ok && data.success) {
                    renderVisualFiles(data.archivos);
                    showNotice('success', 'Archivo Guardado', data.message);
                } else {
                    showNotice('error', 'Error al subir', data.error || 'No se pudo cargar el archivo.');
                }
            } catch (err) {
                console.error('Error al subir archivo:', err);
                showNotice('error', 'Error', 'Ocurrió un fallo de conexión al subir el archivo.');
            } finally {
                inputArchivoFile.value = '';
                const currentFiles = visualFilesContainer ? visualFilesContainer.querySelectorAll('.rd-excel-file-card').length : 0;
                btnTriggerUploadVisual.disabled = (currentFiles >= 4);
                btnTriggerUploadVisual.innerHTML = `
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                    <span>Subir Archivo (PDF / Excel)</span>
                    <span id="visual-count-badge" class="rd-badge-counter">(${currentFiles}/4)</span>
                `;
                setSavingState(false);
            }
        });
    }

    // ── Eliminar Archivo Adjunto Individual con Clave Maestra CALIDAD2026 ──
    if (visualFilesContainer) {
        visualFilesContainer.addEventListener('click', async (e) => {
            const btnDelete = e.target.closest('.btn-delete-visual-file');
            if (!btnDelete) return;

            const fileId = btnDelete.getAttribute('data-file-id');
            const fileName = btnDelete.getAttribute('data-file-name') || 'este archivo';

            let clave = '';
            if (window.Swal) {
                const result = await Swal.fire({
                    title: 'Eliminar Archivo Adjunto',
                    html: `
                        <p style="margin-bottom: 8px; font-size: 0.95rem; color: #475569;">
                            ¿Deseas eliminar <b>"${fileName}"</b>?
                        </p>
                        <p style="margin-bottom: 14px; font-size: 0.88rem; color: #64748b;">
                            Ingresa la clave maestra para confirmar:
                        </p>
                        <div style="position: relative; max-width: 320px; margin: 0 auto;">
                            <input type="password" id="swal-clave-input-visual" placeholder="Clave maestra..." 
                                style="width: 100%; box-sizing: border-box; padding: 10px 42px 10px 14px; margin: 0; background: #ffffff; color: #0f172a; border: 1.5px solid #cbd5e1; border-radius: 6px; font-size: 0.95rem; outline: none; font-family: inherit;"
                                autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
                            <button type="button" id="swal-toggle-password-visual" title="Mostrar / Ocultar contraseña" 
                                style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; padding: 4px; display: flex; align-items: center; justify-content: center; color: #64748b; outline: none; transition: color 0.2s;">
                                <svg id="swal-eye-icon-visual" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                        </div>
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#9c0303',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Eliminar Archivo',
                    cancelButtonText: 'Cancelar',
                    didOpen: () => {
                        const input = document.getElementById('swal-clave-input-visual');
                        const toggleBtn = document.getElementById('swal-toggle-password-visual');
                        const eyeIcon = document.getElementById('swal-eye-icon-visual');

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
                                    toggleBtn.style.color = '#033966';
                                    eyeIcon.innerHTML = `
                                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                        <line x1="1" y1="1" x2="23" y2="23"></line>
                                    `;
                                } else {
                                    input.type = 'password';
                                    toggleBtn.style.color = '#64748b';
                                    eyeIcon.innerHTML = `
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    `;
                                }
                            });
                        }
                    },
                    preConfirm: () => {
                        const input = document.getElementById('swal-clave-input-visual');
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
                const res = await fetch(config.routes.deleteArchivo, {
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
                    renderVisualFiles(data.archivos);
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

    // ── Exportar Reporte a PDF Oficial ──
    function exportVisualPdf() {
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

    if (btnTopPdf) btnTopPdf.addEventListener('click', exportVisualPdf);
    if (btnBottomPdf) btnBottomPdf.addEventListener('click', exportVisualPdf);

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
                confirmButtonColor: '#033966',
                confirmButtonText: 'Aceptar',
                timer: icon === 'success' ? 2500 : undefined
            });
        } else {
            alert(text || title);
        }
    }

});
