<script>
    // ═════════════════════════════════════════════════════════════════════
    // CONTROL DEL MENÚ DE ACCIONES (PRIORIDADES DESACTIVADAS)
    // ═════════════════════════════════════════════════════════════════════
    window.toggleActionsDropdown = function(e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        var actionsContainer = document.getElementById('actionsDropdownContainer');
        var btnActionsTrigger = document.getElementById('btnActionsTrigger');
        if (!actionsContainer) return;

        var isOpen = actionsContainer.classList.contains('is-open');
        if (isOpen) {
            actionsContainer.classList.remove('is-open');
            if (btnActionsTrigger) btnActionsTrigger.setAttribute('aria-expanded', 'false');
        } else {
            actionsContainer.classList.add('is-open');
            if (btnActionsTrigger) btnActionsTrigger.setAttribute('aria-expanded', 'true');
        }
    };

    // Cerrar menú al hacer clic fuera del dropdown
    document.addEventListener('click', function(e) {
        var actionsContainer = document.getElementById('actionsDropdownContainer');
        var btnActionsTrigger = document.getElementById('btnActionsTrigger');
        if (actionsContainer && !actionsContainer.contains(e.target)) {
            actionsContainer.classList.remove('is-open');
            if (btnActionsTrigger) btnActionsTrigger.setAttribute('aria-expanded', 'false');
        }
    });

    // Helper para inicialización segura sin importar el ciclo de vida del DOM
    function initWhenReady(fn) {
        if (document.readyState === 'complete' || document.readyState === 'interactive') {
            setTimeout(fn, 1);
        } else {
            document.addEventListener('DOMContentLoaded', fn);
        }
    }

    initWhenReady(function() {
        var actionsContainer = document.getElementById('actionsDropdownContainer');
        var btnActionsTrigger = document.getElementById('btnActionsTrigger');
        var btnStartReactivateMode = document.getElementById('btnStartReactivateMode');
        var btnStartDeleteMode = document.getElementById('btnStartDeleteMode');
        var selectionControls = document.getElementById('selectionModeControls');
        var btnCancelSelection = document.getElementById('btnCancelSelection');
        var btnConfirmReactivate = document.getElementById('btnConfirmReactivate');
        var btnConfirmDelete = document.getElementById('btnConfirmDelete');
        var selectionBadge = document.getElementById('selectionBadge');
        var selectedCountText = document.getElementById('selectedCountText');
        var selectAllCheckboxDisabled = document.getElementById('selectAllCheckboxDisabled');
        var prioritiesTable = document.getElementById('prioritiesTable');

        var currentSelectionMode = null; // 'reactivate' | 'delete'

        // Exponer funciones para llamadas inline seguras
        window.startReactivateMode = function() {
            var totalBoxes = document.querySelectorAll('.ot-select-checkbox-disabled');
            if (totalBoxes.length === 0) {
                if (window.gisToast) {
                    window.gisToast('No hay órdenes de trabajo desactivadas para reactivar.', 'warning');
                }
                return;
            }
            enterSelectionMode('reactivate');
        };
        window.startDeleteMode = function() {
            var totalBoxes = document.querySelectorAll('.ot-select-checkbox-disabled');
            if (totalBoxes.length === 0) {
                if (window.gisToast) {
                    window.gisToast('No hay órdenes de trabajo desactivadas para eliminar.', 'warning');
                }
                return;
            }
            enterSelectionMode('delete');
        };
        window.exitSelectionMode = function() {
            exitSelectionMode();
        };

        function updateSelectionState() {
            var checkedBoxes = document.querySelectorAll('.ot-select-checkbox-disabled:checked');
            var totalBoxes = document.querySelectorAll('.ot-select-checkbox-disabled');
            var count = checkedBoxes.length;

            if (selectedCountText) {
                selectedCountText.textContent = count === 1 ? '1 OT seleccionada' : count + ' OTs seleccionadas';
            }

            if (btnConfirmReactivate) {
                btnConfirmReactivate.disabled = (count === 0);
            }
            if (btnConfirmDelete) {
                btnConfirmDelete.disabled = (count === 0);
            }

            if (selectAllCheckboxDisabled && totalBoxes.length > 0) {
                selectAllCheckboxDisabled.checked = (count === totalBoxes.length);
                selectAllCheckboxDisabled.indeterminate = (count > 0 && count < totalBoxes.length);
            }
        }

        function enterSelectionMode(mode) {
            currentSelectionMode = mode;
            if (actionsContainer) {
                actionsContainer.classList.remove('is-open');
                actionsContainer.style.display = 'none';
            }
            if (selectionControls) selectionControls.style.display = 'flex';
            if (prioritiesTable) {
                prioritiesTable.classList.add('is-selection-mode');
                prioritiesTable.classList.toggle('mode-delete-active', mode === 'delete');
            }

            var emptyCell = document.querySelector('.empty-state-cell');
            if (emptyCell) {
                emptyCell.colSpan = 17;
            }

            if (selectionControls) {
                selectionControls.classList.toggle('mode-reactivate', mode === 'reactivate');
                selectionControls.classList.toggle('mode-delete', mode === 'delete');
            }

            if (mode === 'reactivate') {
                if (selectionBadge) {
                    selectionBadge.className = 'selection-counter-badge badge-success';
                }
                if (btnConfirmReactivate) btnConfirmReactivate.style.display = 'inline-flex';
                if (btnConfirmDelete) btnConfirmDelete.style.display = 'none';
            } else if (mode === 'delete') {
                if (selectionBadge) {
                    selectionBadge.className = 'selection-counter-badge';
                }
                if (btnConfirmReactivate) btnConfirmReactivate.style.display = 'none';
                if (btnConfirmDelete) btnConfirmDelete.style.display = 'inline-flex';
            }
            updateSelectionState();
        }

        function exitSelectionMode() {
            currentSelectionMode = null;
            if (selectionControls) selectionControls.style.display = 'none';
            if (actionsContainer) actionsContainer.style.display = 'inline-block';
            if (prioritiesTable) {
                prioritiesTable.classList.remove('is-selection-mode');
                prioritiesTable.classList.remove('mode-delete-active');
            }

            var emptyCell = document.querySelector('.empty-state-cell');
            if (emptyCell) {
                emptyCell.colSpan = 16;
            }

            document.querySelectorAll('.ot-select-checkbox-disabled').forEach(function(cb) {
                cb.checked = false;
                var tr = cb.closest('tr');
                if (tr) {
                    tr.classList.remove('row-selected-reactivate', 'row-selected-delete');
                }
            });

            if (selectAllCheckboxDisabled) {
                selectAllCheckboxDisabled.checked = false;
                selectAllCheckboxDisabled.indeterminate = false;
            }
            updateSelectionState();
        }

        // Iniciar modo reactivar
        if (btnStartReactivateMode) {
            btnStartReactivateMode.addEventListener('click', function() {
                enterSelectionMode('reactivate');
            });
        }

        // Iniciar modo eliminar permanentemente
        if (btnStartDeleteMode) {
            btnStartDeleteMode.addEventListener('click', function() {
                enterSelectionMode('delete');
            });
        }

        // Cancelar modo selección
        if (btnCancelSelection) {
            btnCancelSelection.addEventListener('click', function() {
                exitSelectionMode();
            });
        }

        // Delegación de eventos en tabla (Checkboxes)
        if (prioritiesTable) {
            prioritiesTable.addEventListener('change', function(e) {
                if (e.target && e.target.classList.contains('ot-select-checkbox-disabled')) {
                    var tr = e.target.closest('tr');
                    if (tr) {
                        var targetClass = (currentSelectionMode === 'delete') ? 'row-selected-delete' :
                            'row-selected-reactivate';
                        if (e.target.checked) {
                            tr.classList.add(targetClass);
                        } else {
                            tr.classList.remove('row-selected-delete', 'row-selected-reactivate');
                        }
                    }
                    updateSelectionState();
                }
            });

            prioritiesTable.addEventListener('click', function(e) {
                if (!prioritiesTable.classList.contains('is-selection-mode')) return;
                var td = e.target.closest('td.col-select');
                if (td && e.target.tagName !== 'INPUT') {
                    var cb = td.querySelector('.ot-select-checkbox-disabled');
                    if (cb) {
                        cb.checked = !cb.checked;
                        cb.dispatchEvent(new Event('change', {
                            bubbles: true
                        }));
                    }
                }
            });
        }

        // Seleccionar / Deseleccionar todas
        if (selectAllCheckboxDisabled) {
            selectAllCheckboxDisabled.addEventListener('change', function() {
                var isChecked = this.checked;
                var targetClass = (currentSelectionMode === 'delete') ? 'row-selected-delete' :
                    'row-selected-reactivate';
                document.querySelectorAll('.ot-select-checkbox-disabled').forEach(function(cb) {
                    cb.checked = isChecked;
                    var tr = cb.closest('tr');
                    if (tr) {
                        if (isChecked) {
                            tr.classList.add(targetClass);
                        } else {
                            tr.classList.remove('row-selected-delete',
                                'row-selected-reactivate');
                        }
                    }
                });
                updateSelectionState();
            });
        }

        // ── 1. Confirmar Reactivación ──
        if (btnConfirmReactivate) {
            btnConfirmReactivate.addEventListener('click', function() {
                var checkedBoxes = Array.from(document.querySelectorAll(
                    '.ot-select-checkbox-disabled:checked'));
                if (checkedBoxes.length === 0) return;

                var selectedOts = checkedBoxes.map(function(cb) {
                    return {
                        id: cb.dataset.otId,
                        label: cb.dataset.otLabel || ('OT ' + cb.dataset.otId)
                    };
                });

                var listHtml =
                    '<ul style="text-align: left; max-height: 150px; overflow-y: auto; padding-left: 20px; margin: 10px 0; font-size: 0.9rem; color: #1e293b;">';
                selectedOts.forEach(function(ot) {
                    listHtml += '<li style="margin-bottom: 4px;"><strong>' + ot.label +
                        '</strong></li>';
                });
                listHtml += '</ul>';

                Swal.fire({
                    title: (selectedOts.length === 1 ? '¿Reactivar orden de trabajo?' :
                        '¿Reactivar órdenes de trabajo?'),
                    html: `
                        <div style="text-align: left; font-size: 0.92rem; line-height: 1.5; color: #334155;">
                            <div style="background-color: #f0fdf4; border: 1.5px solid #22c55e; border-radius: 8px; padding: 12px; margin-bottom: 12px;">
                                <p style="margin: 0; color: #15803d; font-weight: 700; font-size: 0.95rem;">
                                    Restablecimiento en el flujo de producción
                                </p>
                                <p style="margin: 6px 0 0 0; color: #166534; font-size: 0.85rem;">
                                    Las órdenes volverán a integrarse inmediatamente a las vistas de Prioridades, OTs activas, Maquinado, Almacén y Control de Calidad.
                                </p>
                            </div>
                            <p style="font-weight: 600; color: #475569; margin-bottom: 4px;">Órdenes de Trabajo a reactivar:</p>
                            ${listHtml}
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Reactivar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#16a34a',
                    cancelButtonColor: '#64748b',
                    reverseButtons: true,
                    focusCancel: true,
                    customClass: {
                        popup: 'swal2-gis-popup'
                    }
                }).then(function(result) {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Reactivando OTs...',
                            html: 'Restableciendo el estado activo en la base de datos...',
                            allowOutsideClick: false,
                            customClass: {
                                popup: 'swal2-gis-popup'
                            },
                            didOpen: function() {
                                Swal.showLoading();
                            }
                        });

                        var csrfToken = document.querySelector('meta[name="csrf-token"]')
                            ?.getAttribute('content');
                        var otIds = selectedOts.map(function(item) {
                            return item.id;
                        });

                        fetch("{{ route('master.priorities.reactivate') }}", {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken,
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                body: JSON.stringify({
                                    ot_ids: otIds
                                })
                            })
                            .then(function(res) {
                                return res.json();
                            })
                            .then(function(data) {
                                if (data && data.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: '¡OTs Reactivadas!',
                                        text: data.message,
                                        timer: 1800,
                                        showConfirmButton: false,
                                        customClass: {
                                            popup: 'swal2-gis-popup'
                                        }
                                    }).then(function() {
                                        window.location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: data.message ||
                                            'No fue posible reactivar las OTs seleccionadas.',
                                        customClass: {
                                            popup: 'swal2-gis-popup'
                                        }
                                    });
                                }
                            })
                            .catch(function(err) {
                                console.error("Error al reactivar:", err);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error de Red',
                                    text: 'Ocurrió un error al procesar la solicitud con el servidor.',
                                    customClass: {
                                        popup: 'swal2-gis-popup'
                                    }
                                });
                            });
                    }
                });
            });
        }

        // ── 2. Función Unificada: Eliminar con Respaldo de Reportes ──
        function triggerEliminarConRespaldo(selectedOts) {
            if (!selectedOts || selectedOts.length === 0) return;

            var listHtml =
                '<div style="max-height: 160px; overflow-y: auto; padding-right: 6px; margin: 8px 0 12px 0;">';
            selectedOts.forEach(function(ot) {
                listHtml +=
                    '<div style="background: #ffffff; border: 1px solid #fecaca; border-left: 4px solid #dc2626; border-radius: 6px; padding: 8px 12px; margin-bottom: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">';
                listHtml +=
                    '  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2px;">';
                listHtml += '    <strong style="color: #991b1b; font-size: 0.95rem;">OT ' + ot.id +
                    '</strong>';
                listHtml +=
                    '    <span style="font-size: 0.78rem; background: #fee2e2; color: #991b1b; padding: 2px 6px; border-radius: 4px; font-weight: 700;">' +
                    (ot.client || 'Sin Cliente') + '</span>';
                listHtml += '  </div>';
                listHtml += '  <div style="font-size: 0.83rem; color: #334155; font-weight: 600;">' + (
                    ot.product || 'Sin Producto') + '</div>';
                if (ot.classes) {
                    listHtml +=
                        '  <div style="font-size: 0.76rem; color: #64748b; margin-top: 2px;">Piezas: ' +
                        ot.classes + '</div>';
                }
                listHtml += '</div>';
            });
            listHtml += '</div>';

            var isSingle = selectedOts.length === 1;

            Swal.fire({
                title: isSingle ? '¿Eliminar y respaldar orden de trabajo?' :
                    '¿Eliminar y respaldar órdenes de trabajo?',
                html: `
                    <div style="text-align: left; font-size: 0.92rem; line-height: 1.5; color: #334155;">
                        <div style="background-color: #fef2f2; border: 1.5px solid #ef4444; border-radius: 10px; padding: 12px 14px; margin-bottom: 12px;">
                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                                </svg>
                                <strong style="color: #991b1b; font-size: 0.95rem;">Ocultación Segura y Respaldo Automático</strong>
                            </div>
                            <p style="margin: 0; color: #7f1d1d; font-size: 0.84rem; line-height: 1.45;">
                                Las órdenes se ocultarán de la lista <strong>sin eliminar ningún registro de la base de datos</strong>. Se generará un respaldo comprimido en formato ZIP con los reportes oficiales y la documentación respectiva en <code style="background: rgba(0,0,0,0.06); padding: 2px 5px; border-radius: 4px; font-weight: 700; color: #991b1b;">storage/app/RESPALDO_PRIORIDADES/</code> y se descargará automáticamente:
                            </p>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 4px 10px; margin-top: 8px; padding-top: 8px; border-top: 1px dashed #fca5a5; font-size: 0.8rem; color: #991b1b; font-weight: 600;">
                                <div>• Reportes de Piezas (por Clase y Proceso)</div>
                                <div>• Reporte de Soldadura (si aplica)</div>
                                <div>• Reporte Soldadura PTA (si aplica)</div>
                                <div>• Documentación GIS (si existe)</div>
                            </div>
                        </div>
                        <p style="font-weight: 700; color: #475569; margin: 0 0 6px 0; font-size: 0.88rem;">
                            ${isSingle ? 'Detalle de la Orden de Trabajo a ocultar:' : 'Órdenes de Trabajo seleccionadas (' + selectedOts.length + '):'}
                        </p>
                        ${listHtml}
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Eliminar y Respaldar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#64748b',
                reverseButtons: true,
                focusCancel: true,
                customClass: {
                    popup: 'swal2-gis-popup'
                }
            }).then(function(result) {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Respaldando y ocultando...',
                        html: `
                            <div style="text-align: center; font-size: 0.9rem; color: #475569; padding: 6px 0;">
                                <p style="margin-bottom: 6px; font-weight: 600; color: #1e293b;">Generando reportes, clonando documentación y empaquetando ZIP...</p>
                                <span style="font-size: 0.82rem; color: #64748b;">Guardando en <code>storage/app/RESPALDO_PRIORIDADES/</code> y preparando descarga</span>
                            </div>
                        `,
                        allowOutsideClick: false,
                        didOpen: function() {
                            Swal.showLoading();
                        },
                        customClass: {
                            popup: 'swal2-gis-popup'
                        }
                    });

                    var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                        'content');
                    var otIds = selectedOts.map(function(item) {
                        return item.id;
                    });

                    fetch("{{ route('master.priorities.delete_permanent') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({
                                ot_ids: otIds
                            })
                        })
                        .then(function(res) {
                            return res.json();
                        })
                        .then(function(data) {
                            if (data && data.success) {
                                if (data.download_url) {
                                    var dlLink = document.createElement('a');
                                    dlLink.href = data.download_url;
                                    dlLink.setAttribute('download', data.zip_file || '');
                                    document.body.appendChild(dlLink);
                                    dlLink.click();
                                    dlLink.remove();
                                }

                                Swal.fire({
                                    icon: 'success',
                                    title: '¡OTs Respaldadas y Ocultadas!',
                                    html: `
                                    <div style="font-size: 0.92rem; color: #334155; line-height: 1.5;">
                                        <p style="margin-bottom: 8px; font-weight: 600; color: #15803d;">
                                            ${data.message}
                                        </p>
                                        ${data.zip_file ? `
                                        <div style="background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 8px; padding: 10px 14px; margin-bottom: 8px; text-align: left;">
                                            <div style="display: flex; align-items: center; gap: 6px; font-weight: 700; color: #166534; font-size: 0.88rem; margin-bottom: 4px;">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                                Descarga de Respaldo ZIP Iniciada:
                                            </div>
                                            <code style="font-weight: 700; color: #14532d; font-size: 0.84rem; word-break: break-all;">${data.zip_file}</code>
                                        </div>` : ''}
                                        <div style="background: #f1f5f9; border-radius: 6px; padding: 8px 12px; font-size: 0.82rem; color: #475569; text-align: left;">
                                            Carpeta de respaldos en servidor:<br>
                                            <code style="font-weight: 700; color: #0f172a;">${data.backup_dir}</code>
                                        </div>
                                    </div>
                                `,
                                    timer: 3500,
                                    showConfirmButton: false,
                                    customClass: {
                                        popup: 'swal2-gis-popup'
                                    }
                                }).then(function() {
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error al procesar',
                                    text: data.message ||
                                        'No fue posible completar el respaldo y eliminación de las OTs.',
                                    customClass: {
                                        popup: 'swal2-gis-popup'
                                    }
                                });
                            }
                        })
                        .catch(function(err) {
                            console.error("Error al respaldar y eliminar:", err);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error de Red',
                                text: 'Ocurrió un error al procesar la solicitud con el servidor.',
                                customClass: {
                                    popup: 'swal2-gis-popup'
                                }
                            });
                        });
                }
            });
        }

        // ── 3. Confirmar Eliminación Permanente por Lote ──
        if (btnConfirmDelete) {
            btnConfirmDelete.addEventListener('click', function() {
                var checkedBoxes = Array.from(document.querySelectorAll(
                    '.ot-select-checkbox-disabled:checked'));
                if (checkedBoxes.length === 0) return;

                var selectedOts = checkedBoxes.map(function(cb) {
                    return {
                        id: cb.dataset.otId,
                        label: cb.dataset.otLabel || ('OT ' + cb.dataset.otId),
                        product: cb.dataset.product || 'Sin Producto',
                        client: cb.dataset.client || 'Sin Cliente',
                        classes: cb.dataset.classes || ''
                    };
                });

                triggerEliminarConRespaldo(selectedOts);
            });
        }
    });
</script>
