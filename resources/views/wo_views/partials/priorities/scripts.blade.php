<script>
    window.isReadOnlyPriorities = @json($isReadOnly);
    window.prioritiesWOsData = @json($wosJsData);

    window.openFundicionModal = function(otId) {
        if (typeof Swal === 'undefined') {
            alert("No se pudo cargar el componente visual. Por favor, revisa tu conexión a internet.");
            return;
        }

        var woData = window.prioritiesWOsData ? window.prioritiesWOsData[otId] : null;
        if (!woData) return;

        if (woData.hasCastingPfc) {
            Swal.fire({
                icon: 'warning',
                title: '<div style="text-align: center !important; width: 100%;">Acción no permitida</div>',
                html: '<style>.gis-center-alert { text-align: center !important; display: block; width: 100%; }</style><div class="gis-center-alert">Esta OT ya tiene una pre-orden de casting generada, por lo que la fecha de fundición ya no puede ser modificada.</div>',
                confirmButtonText: 'OK',
                confirmButtonColor: '#033966'
            });
            return;
        }

        var clases = woData.clases || [];
        if (clases.length === 0) {
            var currentVal = woData.fecha_entrega_fundicion || '';
            Swal.fire({
                title: 'Fecha Entrega Proveedor Fundición',
                html: `<strong>OT ${otId}</strong> - ${woData.moldura}<br><br>Ingresa la fecha de entrega:`,
                input: 'date',
                inputValue: currentVal,
                showCancelButton: true,
                confirmButtonText: 'Guardar Fecha',
                cancelButtonText: 'Cancelar',
                customClass: {
                    popup: 'gis-date-modal-popup',
                    title: 'gis-date-modal-title',
                    confirmButton: 'gis-swal-confirm',
                    cancelButton: 'gis-swal-cancel'
                }
            }).then(function(result) {
                if (result.isConfirmed && result.value !== undefined) {
                    _doAutosave(otId, 'fecha_entrega_fundicion', result.value, document.body);
                    setTimeout(function() {
                        window.location.reload();
                    }, 600);
                }
            });
            return;
        }

        var firstDate = clases[0].fecha_entrega_fundicion || '';
        var allSame = clases.every(function(c) {
            return (c.fecha_entrega_fundicion || '') === firstDate;
        });
        var masterDate = allSame ? firstDate : (firstDate || woData.fecha_entrega_fundicion || '');

        var rowsHtml = '';
        clases.forEach(function(c, index) {
            var cDate = c.fecha_entrega_fundicion || '';
            rowsHtml += `
                    <tr>
                        <td style="font-weight: 700;">${index + 1}</td>
                        <td style="font-weight: 700; text-align: left;">${c.nombre}</td>
                        <td style="font-weight: 700;">${c.pedido}</td>
                        <td>
                            <input type="date" class="swal-class-date-input gis-date-input-clean" data-clase-id="${c.id}" value="${cDate}" style="width: 100%; box-sizing: border-box; ${allSame ? 'opacity: 0.5; cursor: not-allowed;' : ''}" ${allSame ? 'disabled' : ''}>
                        </td>
                    </tr>
                `;
        });

        var modalHtml = `
                <div class="gis-date-modal-info-card">
                    <div class="gis-date-modal-info-item">
                        <span class="gis-date-modal-info-label">OT</span>
                        <span class="gis-date-modal-info-val" style="color: #033966;">#${woData.id}</span>
                    </div>
                    <div class="gis-date-modal-info-item">
                        <span class="gis-date-modal-info-label">Cliente</span>
                        <span class="gis-date-modal-info-val">${woData.cliente}</span>
                    </div>
                    <div class="gis-date-modal-info-item">
                        <span class="gis-date-modal-info-label">Producto / Moldura</span>
                        <span class="gis-date-modal-info-val">${woData.moldura}</span>
                    </div>
                </div>

                <div class="gis-date-modal-mode-box">
                    <div style="font-size: 0.72rem; text-transform: uppercase; color: #64748b; font-weight: 700; letter-spacing: 0.5px; margin-bottom: 8px;">Asignación de Fechas</div>
                    <div style="display: flex; gap: 24px; align-items: center;">
                        <label>
                            <input type="radio" name="swal_date_mode" value="same" ${allSame ? 'checked' : ''} onchange="window.toggleSwalDateMode('same')">
                            Misma fecha para todas las clases
                        </label>
                        <label>
                            <input type="radio" name="swal_date_mode" value="diff" ${!allSame ? 'checked' : ''} onchange="window.toggleSwalDateMode('diff')">
                            Fechas diferentes por clase
                        </label>
                    </div>
                </div>

                <div id="swal-master-date-container" style="margin-bottom: 16px; text-align: left; ${allSame ? '' : 'display: none;'}">
                    <label style="font-size: 0.72rem; text-transform: uppercase; color: #64748b; font-weight: 700; letter-spacing: 0.5px; display: block; margin-bottom: 6px;">Fecha General (Aplica a todas las clases)</label>
                    <input type="date" id="swal-master-date" value="${masterDate}" class="gis-date-input-clean" style="width: 100%; box-sizing: border-box;" onchange="window.applyMasterDateToAllClasses(this.value)">
                </div>

                <div class="gis-date-modal-table-wrap">
                    <table class="gis-date-modal-table">
                        <thead>
                            <tr>
                                <th style="width: 45px;">Nº</th>
                                <th style="text-align: left;">Clase</th>
                                <th style="width: 60px;">Cant.</th>
                                <th style="width: 170px;">Fecha Entrega Fundición</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${rowsHtml}
                        </tbody>
                    </table>
                </div>
            `;

        Swal.fire({
            title: 'Fecha Entrega Proveedor Fundición',
            html: modalHtml,
            width: '740px',
            showCancelButton: true,
            confirmButtonText: 'Guardar Fechas',
            cancelButtonText: 'Cancelar',
            customClass: {
                popup: 'gis-date-modal-popup',
                title: 'gis-date-modal-title',
                confirmButton: 'gis-swal-confirm',
                cancelButton: 'gis-swal-cancel'
            },
            allowOutsideClick: false
        }).then(function(result) {
            if (result.isConfirmed) {
                var mode = document.querySelector('input[name="swal_date_mode"]:checked')?.value || 'same';
                var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                if (mode === 'same') {
                    var val = document.getElementById('swal-master-date')?.value || '';
                    fetch("{{ route('master.priorities.autosave') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            ot_id: otId,
                            field: 'fecha_entrega_fundicion',
                            value: val,
                            apply_to_all: true
                        })
                    }).then(function(r) {
                        return r.json();
                    }).then(function(d) {
                        if (d.success) {
                            window.location.reload();
                        }
                    });
                } else {
                    var batch = [];
                    document.querySelectorAll('.swal-class-date-input').forEach(function(inp) {
                        var cId = inp.dataset.claseId;
                        batch.push({
                            clase_id: cId,
                            fecha: inp.value
                        });
                    });
                    fetch("{{ route('master.priorities.autosave') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            ot_id: otId,
                            field: 'fecha_entrega_fundicion',
                            batch_dates: batch
                        })
                    }).then(function(r) {
                        return r.json();
                    }).then(function(d) {
                        if (d.success) {
                            window.location.reload();
                        }
                    });
                }
            }
        });
    };

    window.toggleSwalDateMode = function(mode) {
        var masterContainer = document.getElementById('swal-master-date-container');
        if (masterContainer) {
            masterContainer.style.display = mode === 'same' ? 'block' : 'none';
        }
        var isSame = mode === 'same';
        document.querySelectorAll('.swal-class-date-input').forEach(function(inp) {
            inp.disabled = isSame;
            if (isSame) {
                inp.style.opacity = '0.5';
                inp.style.cursor = 'not-allowed';
            } else {
                inp.style.opacity = '1';
                inp.style.cursor = 'pointer';
            }
        });
        if (isSame) {
            var masterVal = document.getElementById('swal-master-date')?.value || '';
            window.applyMasterDateToAllClasses(masterVal);
        }
    };

    window.applyMasterDateToAllClasses = function(val) {
        document.querySelectorAll('.swal-class-date-input').forEach(function(inp) {
            inp.value = val;
        });
    };

    const FOUNDRY_PROVIDERS_LIST = [
        "SS Metal Foundry, S. de R. L. de C. V.",
        "SOCIEDAD COOPERATIVA DE PRODUCCIÓN JACARANDAS",
        "EXTERNO",
        "Sin proveedor actual"
    ];

    window.openSupplierModal = function(otId) {
        if (typeof Swal === 'undefined') {
            alert("No se pudo cargar el componente visual. Por favor, revisa tu conexión a internet.");
            return;
        }

        var woData = window.prioritiesWOsData ? window.prioritiesWOsData[otId] : null;
        if (!woData) return;

        if (woData.hasCastingPfc) {
            Swal.fire({
                icon: 'warning',
                title: '<div style="text-align: center !important; width: 100%;">Acción no permitida</div>',
                html: '<style>.gis-center-alert { text-align: center !important; display: block; width: 100%; }</style><div class="gis-center-alert">Esta OT ya tiene una pre-orden de casting generada, por lo que el proveedor de fundición ya no puede ser modificado.</div>',
                confirmButtonText: 'OK',
                confirmButtonColor: '#033966'
            });
            return;
        }

        var clases = woData.clases || [];
        var currentSupplier = woData.proveedor_material || '';

        function buildSupplierOptions(selectedVal) {
            var opts = `<option value="">-- Seleccione Proveedor --</option>`;
            FOUNDRY_PROVIDERS_LIST.forEach(function(p) {
                var sel = (selectedVal === p) ? 'selected' : '';
                opts += `<option value="${p}" ${sel}>${p}</option>`;
            });
            if (selectedVal && !FOUNDRY_PROVIDERS_LIST.includes(selectedVal)) {
                opts += `<option value="${selectedVal}" selected>${selectedVal}</option>`;
            }
            return opts;
        }

        if (clases.length <= 1) {
            var singleSupplier = (clases.length === 1 && clases[0].proveedor) ? clases[0].proveedor :
                currentSupplier;
            var selectHtml = `<select id="swal-single-supplier" class="gis-date-input-clean" style="width: 100%; box-sizing: border-box;">
                    ${buildSupplierOptions(singleSupplier)}
                </select>`;

            Swal.fire({
                title: 'Proveedor de Fundición',
                html: `<strong>OT ${otId}</strong> - ${woData.moldura || ''}<br><br>Selecciona el proveedor:<br><br>${selectHtml}`,
                width: '420px',
                showCancelButton: true,
                confirmButtonText: 'Guardar Proveedor',
                cancelButtonText: 'Cancelar',
                customClass: {
                    popup: 'gis-date-modal-popup',
                    title: 'gis-date-modal-title',
                    confirmButton: 'gis-swal-confirm',
                    cancelButton: 'gis-swal-cancel'
                }
            }).then(function(result) {
                if (result.isConfirmed) {
                    var val = document.getElementById('swal-single-supplier')?.value || '';
                    var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                        'content');

                    fetch("{{ route('master.priorities.autosave') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            ot_id: otId,
                            field: 'proveedor',
                            value: val,
                            apply_to_all: true
                        })
                    }).then(function(r) {
                        return r.json();
                    }).then(function(d) {
                        if (d.success) {
                            window.location.reload();
                        }
                    });
                }
            });
            return;
        }

        var firstProv = clases[0].proveedor || '';
        var allSame = clases.every(function(c) {
            return (c.proveedor || '') === firstProv;
        });
        var masterProv = allSame ? firstProv : (firstProv || woData.proveedor_material || '');

        var rowsHtml = '';
        clases.forEach(function(c, index) {
            var cProv = c.proveedor || '';
            rowsHtml += `
                    <tr>
                        <td style="font-weight: 700;">${index + 1}</td>
                        <td style="font-weight: 700; text-align: left;">${c.nombre}</td>
                        <td style="font-weight: 700;">${c.pedido}</td>
                        <td>
                            <select class="swal-class-supplier-select gis-date-input-clean" data-clase-id="${c.id}" style="width: 100%; box-sizing: border-box; ${allSame ? 'opacity: 0.5; cursor: not-allowed;' : ''}" ${allSame ? 'disabled' : ''}>
                                ${buildSupplierOptions(cProv)}
                            </select>
                        </td>
                    </tr>
                `;
        });

        var modalHtml = `
                <div class="gis-date-modal-info-card">
                    <div class="gis-date-modal-info-item">
                        <span class="gis-date-modal-info-label">OT</span>
                        <span class="gis-date-modal-info-val" style="color: #033966;">#${woData.id}</span>
                    </div>
                    <div class="gis-date-modal-info-item">
                        <span class="gis-date-modal-info-label">Cliente</span>
                        <span class="gis-date-modal-info-val">${woData.cliente}</span>
                    </div>
                    <div class="gis-date-modal-info-item">
                        <span class="gis-date-modal-info-label">Producto / Moldura</span>
                        <span class="gis-date-modal-info-val">${woData.moldura}</span>
                    </div>
                </div>

                <div class="gis-date-modal-mode-box">
                    <div style="font-size: 0.72rem; text-transform: uppercase; color: #64748b; font-weight: 700; letter-spacing: 0.5px; margin-bottom: 8px;">Asignación de Proveedor</div>
                    <div style="display: flex; gap: 24px; align-items: center;">
                        <label>
                            <input type="radio" name="swal_supplier_mode" value="same" ${allSame ? 'checked' : ''} onchange="window.toggleSwalSupplierMode('same')">
                            Mismo proveedor para todas las clases
                        </label>
                        <label>
                            <input type="radio" name="swal_supplier_mode" value="diff" ${!allSame ? 'checked' : ''} onchange="window.toggleSwalSupplierMode('diff')">
                            Proveedores diferentes por clase
                        </label>
                    </div>
                </div>

                <div id="swal-master-supplier-container" style="margin-bottom: 16px; text-align: left; ${allSame ? '' : 'display: none;'}">
                    <label style="font-size: 0.72rem; text-transform: uppercase; color: #64748b; font-weight: 700; letter-spacing: 0.5px; display: block; margin-bottom: 6px;">Proveedor General (Aplica a todas las clases)</label>
                    <select id="swal-master-supplier" class="gis-date-input-clean" style="width: 100%; box-sizing: border-box;" onchange="window.applyMasterSupplierToAllClasses(this.value)">
                        ${buildSupplierOptions(masterProv)}
                    </select>
                </div>

                <div class="gis-date-modal-table-wrap">
                    <table class="gis-date-modal-table">
                        <thead>
                            <tr>
                                <th style="width: 45px;">Nº</th>
                                <th style="text-align: left;">Clase</th>
                                <th style="width: 60px;">Cant.</th>
                                <th style="width: 250px;">Proveedor de Fundición</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${rowsHtml}
                        </tbody>
                    </table>
                </div>
            `;

        Swal.fire({
            title: 'Proveedor de Fundición',
            html: modalHtml,
            width: '740px',
            showCancelButton: true,
            confirmButtonText: 'Guardar Proveedor',
            cancelButtonText: 'Cancelar',
            customClass: {
                popup: 'gis-date-modal-popup',
                title: 'gis-date-modal-title',
                confirmButton: 'gis-swal-confirm',
                cancelButton: 'gis-swal-cancel'
            },
            allowOutsideClick: false
        }).then(function(result) {
            if (result.isConfirmed) {
                var mode = document.querySelector('input[name="swal_supplier_mode"]:checked')?.value ||
                    'same';
                var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                if (mode === 'same') {
                    var val = document.getElementById('swal-master-supplier')?.value || '';
                    fetch("{{ route('master.priorities.autosave') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            ot_id: otId,
                            field: 'proveedor',
                            value: val,
                            apply_to_all: true
                        })
                    }).then(function(r) {
                        return r.json();
                    }).then(function(d) {
                        if (d.success) {
                            window.location.reload();
                        }
                    });
                } else {
                    var batch = [];
                    document.querySelectorAll('.swal-class-supplier-select').forEach(function(inp) {
                        var cId = inp.dataset.claseId;
                        batch.push({
                            clase_id: cId,
                            proveedor: inp.value
                        });
                    });
                    fetch("{{ route('master.priorities.autosave') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            ot_id: otId,
                            field: 'proveedor',
                            batch_suppliers: batch
                        })
                    }).then(function(r) {
                        return r.json();
                    }).then(function(d) {
                        if (d.success) {
                            window.location.reload();
                        }
                    });
                }
            }
        });
    };

    window.toggleSwalSupplierMode = function(mode) {
        var masterContainer = document.getElementById('swal-master-supplier-container');
        if (masterContainer) {
            masterContainer.style.display = mode === 'same' ? 'block' : 'none';
        }
        var isSame = mode === 'same';
        document.querySelectorAll('.swal-class-supplier-select').forEach(function(inp) {
            inp.disabled = isSame;
            if (isSame) {
                inp.style.opacity = '0.5';
                inp.style.cursor = 'not-allowed';
            } else {
                inp.style.opacity = '1';
                inp.style.cursor = 'pointer';
            }
        });
        if (isSame) {
            var masterVal = document.getElementById('swal-master-supplier')?.value || '';
            window.applyMasterSupplierToAllClasses(masterVal);
        }
    };

    window.applyMasterSupplierToAllClasses = function(val) {
        document.querySelectorAll('.swal-class-supplier-select').forEach(function(inp) {
            inp.value = val;
        });
    };
</script>

<!-- Menú Contextual para Liberar Piezas -->

<script>
    document.addEventListener('focusin', function(e) {
        if (e.target && e.target.getAttribute('contenteditable') === 'true') {
            if (e.target.querySelector('.empty-editable-ph')) {
                e.target.innerText = '';
            }
        }
    });

    function autosaveField(otId, fieldName, element) {
        var value = element.innerText.trim();
        if (value === '' || value === '-') {
            element.innerHTML =
                '<span class="empty-editable-ph" style="color: #94a3b8; font-size: 0.85em; display: inline-flex; align-items: center; justify-content: center; gap: 3px;" title="Hacer clic para editar"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>-</span>';
            value = '';
        }
        _doAutosave(otId, fieldName, value, element);
    }

    function openDatePicker(td) {
        var input = td.querySelector('input[type="date"]');
        if (input) {
            var onchangeStr = input.getAttribute('onchange') || '';
            if (onchangeStr.includes('fecha_entrega_fundicion')) {
                var tr = td.closest('tr');
                var otId = tr ? tr.getAttribute('data-ot-id') : null;
                var woData = window.prioritiesWOsData && otId ? window.prioritiesWOsData[otId] : null;
                if (woData && woData.hasCastingPfc) {
                    Swal.fire('Acción no permitida',
                        'Esta OT ya tiene una pre-orden de casting generada, por lo que la fecha de fundición ya no puede ser modificada.',
                        'warning');
                    return;
                }
            }

            if (typeof window.openModalCalendar === 'function') {
                var title = onchangeStr.includes('fecha_entrega_fundicion') ? 'Seleccionar Fecha de Fundición' : 'Seleccionar Fecha de Entrega';
                window.openModalCalendar(input, td.querySelector('.date-display'), null, title);
            } else {
                try {
                    input.showPicker();
                } catch (e) {
                    input.focus();
                }
            }
        }
    }

    function handleDateChange(input, otId, fieldName, claseId) {
        var value = input.value;
        var td = input.parentElement;
        var span = td.querySelector('.date-display');

        // Update display text immediately
        if (value) {
            var parts = value.split('-');
            span.innerText = parts[2] + '/' + parts[1] + '/' + parts[0];
        } else {
            span.innerHTML =
                '<span class="empty-date-icon-wrap" title="Hacer clic para asignar fecha"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="empty-date-svg" style="vertical-align: middle; opacity: 0.65;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg></span>';
        }

        _doAutosave(otId, fieldName, value, td, claseId);
    }

    function _doAutosave(otId, fieldName, value, feedbackElement, claseId) {
        var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        if (!csrfToken) {
            console.error("CSRF token not found.");
            return;
        }

        // Add a visual indicator of saving (border outline)
        feedbackElement.style.outline = "2px solid #0d6efd";
        feedbackElement.style.outlineOffset = "-2px";

        var payload = {
            ot_id: otId,
            field: fieldName,
            value: value
        };
        if (claseId) {
            payload.clase_id = claseId;
        }

        fetch("{{ route('master.priorities.autosave') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(payload)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Success indicator (green)
                    feedbackElement.style.outline = "2px solid #198754";
                    setTimeout(() => {
                        feedbackElement.style.outline = "none";
                    }, 1000);
                } else {
                    // Error indicator (red)
                    feedbackElement.style.outline = "2px solid #dc3545";
                    console.error(data.message);
                }
            })
            .catch(error => {
                feedbackElement.style.outline = "2px solid #dc3545";
                console.error("Error al guardar:", error);
            });
    }

    // ══════════════════════════════════════════════════════════════
    // CONTROL DEL MENÚ DE ACCIONES Y MODO SELECCIÓN MÚLTIPLE (DESACTIVAR)
    // ══════════════════════════════════════════════════════════════
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

    // Cerrar menú al hacer clic fuera
    document.addEventListener('click', function(e) {
        var actionsContainer = document.getElementById('actionsDropdownContainer');
        var btnActionsTrigger = document.getElementById('btnActionsTrigger');
        if (actionsContainer && !actionsContainer.contains(e.target)) {
            actionsContainer.classList.remove('is-open');
            if (btnActionsTrigger) btnActionsTrigger.setAttribute('aria-expanded', 'false');
        }
    });

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
        var btnStartDeactivateMode = document.getElementById('btnStartDeactivateMode');
        var selectionControls = document.getElementById('selectionModeControls');
        var btnCancelSelection = document.getElementById('btnCancelSelection');
        var btnConfirmDeactivate = document.getElementById('btnConfirmDeactivate');
        var selectedCountText = document.getElementById('selectedCountText');
        var selectAllCheckbox = document.getElementById('selectAllCheckbox');
        var prioritiesTable = document.getElementById('prioritiesTable');

        window.startDeactivateMode = function() {
            if (actionsContainer) {
                actionsContainer.classList.remove('is-open');
                actionsContainer.style.display = 'none';
            }
            if (selectionControls) selectionControls.style.display = 'flex';
            if (prioritiesTable) prioritiesTable.classList.add('is-selection-mode');
            updateSelectionState();
        };

        window.cancelSelectionMode = function() {
            if (selectionControls) selectionControls.style.display = 'none';
            if (actionsContainer) actionsContainer.style.display = 'inline-block';
            if (prioritiesTable) prioritiesTable.classList.remove('is-selection-mode');

            // Desmarcar todos los checkboxes y clases
            document.querySelectorAll('.ot-select-checkbox').forEach(function(cb) {
                cb.checked = false;
                var tr = cb.closest('tr');
                if (tr) tr.classList.remove('row-selected-deactivate');
            });
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = false;
            }
            updateSelectionState();
        };

        // Modo Selección Múltiple
        function updateSelectionState() {
            var checkedBoxes = document.querySelectorAll('.ot-select-checkbox:checked');
            var totalBoxes = document.querySelectorAll('.ot-select-checkbox');
            var count = checkedBoxes.length;

            if (selectedCountText) {
                selectedCountText.textContent = count === 1 ? '1 OT seleccionada' : count + ' OTs seleccionadas';
            }

            if (btnConfirmDeactivate) {
                btnConfirmDeactivate.disabled = (count === 0);
            }

            if (selectAllCheckbox && totalBoxes.length > 0) {
                selectAllCheckbox.checked = (count === totalBoxes.length);
                selectAllCheckbox.indeterminate = (count > 0 && count < totalBoxes.length);
            }
        }

        // Iniciar modo desactivar
        if (btnStartDeactivateMode) {
            btnStartDeactivateMode.addEventListener('click', window.startDeactivateMode);
        }

        // Cancelar modo selección
        if (btnCancelSelection) {
            btnCancelSelection.addEventListener('click', window.cancelSelectionMode);
        }

        // Toggle individual checkbox y clic en la fila
        if (prioritiesTable) {
            prioritiesTable.addEventListener('change', function(e) {
                if (e.target && e.target.classList.contains('ot-select-checkbox')) {
                    var tr = e.target.closest('tr');
                    if (tr) {
                        if (e.target.checked) {
                            tr.classList.add('row-selected-deactivate');
                        } else {
                            tr.classList.remove('row-selected-deactivate');
                        }
                    }
                    updateSelectionState();
                }
            });

            // Permitir seleccionar haciendo clic en la celda del checkbox
            prioritiesTable.addEventListener('click', function(e) {
                if (!prioritiesTable.classList.contains('is-selection-mode')) return;
                var td = e.target.closest('td.col-select');
                if (td && e.target.tagName !== 'INPUT') {
                    var cb = td.querySelector('.ot-select-checkbox');
                    if (cb) {
                        cb.checked = !cb.checked;
                        cb.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                }
            });
        }

        // Seleccionar / Deseleccionar todas
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                var isChecked = this.checked;
                document.querySelectorAll('.ot-select-checkbox').forEach(function(cb) {
                    cb.checked = isChecked;
                    var tr = cb.closest('tr');
                    if (tr) {
                        if (isChecked) {
                            tr.classList.add('row-selected-deactivate');
                        } else {
                            tr.classList.remove('row-selected-deactivate');
                        }
                    }
                });
                updateSelectionState();
            });
        }

        // Confirmar desactivación
        if (btnConfirmDeactivate) {
            btnConfirmDeactivate.addEventListener('click', function() {
                var checkedBoxes = Array.from(document.querySelectorAll('.ot-select-checkbox:checked'));
                if (checkedBoxes.length === 0) return;

                var selectedOts = checkedBoxes.map(function(cb) {
                    return {
                        id: cb.dataset.otId,
                        label: cb.dataset.otLabel || ('OT ' + cb.dataset.otId)
                    };
                });

                var listHtml = '<ul style="text-align: left; max-height: 150px; overflow-y: auto; padding-left: 20px; margin: 10px 0; font-size: 0.9rem; color: #1e293b;">';
                selectedOts.forEach(function(ot) {
                    listHtml += '<li style="margin-bottom: 4px;"><strong>' + ot.label + '</strong></li>';
                });
                listHtml += '</ul>';

                Swal.fire({
                    title: (selectedOts.length === 1 ? '¿Desactivar orden de trabajo?' : '¿Desactivar órdenes de trabajo?'),
                    html: `
                        <div style="text-align: left; font-size: 0.92rem; line-height: 1.5; color: #334155;">
                            <div style="background-color: #fef2f2; border: 1.5px solid #ef4444; border-radius: 8px; padding: 12px; margin-bottom: 12px;">
                                <p style="margin: 0; color: #991b1b; font-weight: 700; font-size: 0.95rem;">
                                    Suspensión de operaciones activas
                                </p>
                                <p style="margin: 6px 0 0 0; color: #b91c1c; font-size: 0.85rem;">
                                    Las órdenes y sus procesos asociados se ocultarán de todas las áreas operativas (Prioridades, Progreso, Maquinado). Solo podrán consultarse o reactivarse desde la vista de OTs Desactivadas.
                                </p>
                            </div>
                            <p style="font-weight: 600; color: #475569; margin-bottom: 4px;">Órdenes de Trabajo a desactivar:</p>
                            ${listHtml}
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Desactivar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#64748b',
                    reverseButtons: true,
                    focusCancel: true,
                    customClass: { popup: 'swal2-gis-popup' }
                }).then(function(result) {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Desactivando OTs...',
                            html: 'Actualizando el estado en la base de datos...',
                            allowOutsideClick: false,
                            didOpen: function() {
                                Swal.showLoading();
                            }
                        });

                        var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                        var otIds = selectedOts.map(function(item) { return item.id; });

                        fetch("{{ route('master.priorities.deactivate') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({ ot_ids: otIds })
                        })
                        .then(function(res) { return res.json(); })
                        .then(function(data) {
                            if (data && data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: '¡OTs Desactivadas!',
                                    text: data.message,
                                    timer: 1800,
                                    showConfirmButton: false
                                }).then(function() {
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: data.message || 'No fue posible desactivar las OTs seleccionadas.'
                                });
                            }
                        })
                        .catch(function(err) {
                            console.error("Error al desactivar:", err);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error de Red',
                                text: 'Ocurrió un error al procesar la solicitud con el servidor.'
                            });
                        });
                    }
                });
            });
        }
    });

    // ══════════════════════════════════════════════════════════════
    // DRAG AND DROP EN LA TABLA DE PRIORIDADES CON SELECCIÓN DE SEMANA
    // ══════════════════════════════════════════════════════════════
    document.addEventListener('DOMContentLoaded', function() {
        if (window.isReadOnlyPriorities) {
            // Modo solo lectura: inhabilitar drag & drop, autosaves y selectores
            window.openDatePicker = function() {};
            window.openFundicionModal = function() {};
            window.openSupplierModal = function() {};
            window.autosaveField = function() {};
            window.handleDateChange = function() {};
            return;
        }

        var tableBody = document.querySelector('#prioritiesTable tbody');
        if (!tableBody) return;

        var draggedRow = null;
        var originalNextSibling = null;
        var originalParent = null;
        var isSavingPriorities = false;
        var savePrioritiesPending = false;

        // Auto-scroll durante el arrastre
        var autoScrollSpeed = 0;
        var autoScrollInterval = null;

        document.addEventListener('dragover', function(e) {
            if (!draggedRow) return;

            var container = document.querySelector('.excel-table-container');
            if (!container) return;

            var rect = container.getBoundingClientRect();
            var edgeSize = 100; // Píxeles desde el borde del contenedor

            if (e.clientY < rect.top + edgeSize) {
                autoScrollSpeed = -15; // Hacia arriba
            } else if (e.clientY > rect.bottom - edgeSize) {
                autoScrollSpeed = 15; // Hacia abajo
            } else {
                autoScrollSpeed = 0;
            }

            if (autoScrollSpeed !== 0 && !autoScrollInterval) {
                autoScrollInterval = setInterval(function() {
                    container.scrollTop += autoScrollSpeed;
                }, 20);
            } else if (autoScrollSpeed === 0 && autoScrollInterval) {
                clearInterval(autoScrollInterval);
                autoScrollInterval = null;
            }
        });

        document.addEventListener('dragend', function() {
            if (autoScrollInterval) {
                clearInterval(autoScrollInterval);
                autoScrollInterval = null;
            }
        });

        var premiumPastels = [
            '#d4f0f0', '#ffdfd3', '#e2f0cb', '#f3e5f5', '#ffebd2',
            '#d6e4ff', '#ffe5e5', '#e8f4d9', '#f0e6ff', '#fff2cc',
            '#d9f0f9', '#fbe4e4', '#e0f7fa', '#f5f5dc', '#e6e6fa',
            '#ffefd5', '#e0e8f5', '#f0fff0', '#fff0f5', '#fdf5e6'
        ];

        function getJsPastelColor(weekStr) {
            var weekNum = parseInt(String(weekStr).replace(/[^0-9]/g, ''), 10) || 0;
            var index = weekNum % premiumPastels.length;
            return premiumPastels[index];
        }

        function initRowDrag(row) {
            var handle = row.querySelector('.table-drag-handle');
            if (!handle) return;

            handle.addEventListener('mousedown', function() {
                row.draggable = true;
            });
            handle.addEventListener('touchstart', function() {
                row.draggable = true;
            }, {
                passive: true
            });

            row.addEventListener('dragstart', function(e) {
                draggedRow = row;
                originalNextSibling = row.nextSibling;
                originalParent = row.parentNode;
                row.classList.add('is-row-dragging');
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', row.dataset.otId || '');
            });

            row.addEventListener('dragover', function(e) {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';

                if (!draggedRow || draggedRow === row) return;

                var rect = row.getBoundingClientRect();
                var isBottom = e.clientY > (rect.top + rect.height / 2);

                if (isBottom) {
                    row.classList.remove('drag-over-top');
                    row.classList.add('drag-over-bottom');
                } else {
                    row.classList.remove('drag-over-bottom');
                    row.classList.add('drag-over-top');
                }
            });

            row.addEventListener('dragleave', function(e) {
                var rect = row.getBoundingClientRect();
                if (
                    e.clientX < rect.left ||
                    e.clientX >= rect.right ||
                    e.clientY < rect.top ||
                    e.clientY >= rect.bottom
                ) {
                    row.classList.remove('drag-over-top', 'drag-over-bottom');
                }
            });

            row.addEventListener('drop', function(e) {
                e.preventDefault();
                e.stopPropagation();

                row.classList.remove('drag-over-top', 'drag-over-bottom');
                if (!draggedRow || draggedRow === row) return;

                var activeDragRow = draggedRow;
                var otId = activeDragRow.dataset.otId;
                var semanaCell = activeDragRow.querySelector('.cell-semana');
                var currentWeekRaw = semanaCell ? semanaCell.textContent.trim() : '';

                var rect = row.getBoundingClientRect();
                var isBottom = e.clientY > (rect.top + rect.height / 2);

                if (isBottom) {
                    row.parentNode.insertBefore(activeDragRow, row.nextSibling);
                } else {
                    row.parentNode.insertBefore(activeDragRow, row);
                }

                renumberRows();

                var prevRow = activeDragRow.previousElementSibling;
                var nextRow = activeDragRow.nextElementSibling;

                function extractWeek(r) {
                    if (!r) return null;
                    var c = r.querySelector('.cell-semana');
                    if (!c) return null;
                    var txt = c.textContent.trim();
                    var w = parseInt(txt.replace(/[^0-9]/g, ''), 10);
                    return isNaN(w) || w <= 0 ? null : w;
                }

                var wPrev = extractWeek(prevRow);
                var wNext = extractWeek(nextRow);

                function applyRowBg(r, week) {
                    if (r.dataset.isInactive === '1') {
                        r.style.backgroundColor = '#d6d6d6';
                    } else {
                        r.style.backgroundColor = getJsPastelColor(week);
                    }
                }

                var currentW = parseInt(String(currentWeekRaw).replace(/[^0-9]/g, ''), 10);
                if (isNaN(currentW) || currentW <= 0) currentW = null;

                var sameTargetWeek = (wPrev !== null && wNext !== null && wPrev === wNext) ? wPrev :
                    null;

                // Si se movió estrictamente DENTRO de su misma semana (los vecinos pertenecen a su misma semana original)
                if (sameTargetWeek !== null && currentW !== null && sameTargetWeek === currentW) {
                    if (semanaCell) {
                        semanaCell.textContent = currentW;
                    }
                    applyRowBg(activeDragRow, currentW);
                    autoSaveTablePriorities(otId, currentW);
                    return;
                }

                var targetW = sameTargetWeek || wNext || wPrev || currentW || 1;

                // Calcular rango lógico permitido de semanas para la posición donde se soltó la OT
                var startW = 1;
                var endW = 52;

                if (wPrev !== null && wNext !== null) {
                    var minB = Math.min(wPrev, wNext);
                    var maxB = Math.max(wPrev, wNext);
                    if (currentW) {
                        startW = Math.min(minB, currentW);
                        endW = Math.max(maxB, currentW);
                    } else {
                        startW = minB;
                        endW = maxB;
                    }
                } else {
                    // Si se coloca como primera, antes de la semana 1, o junto a una fila con "-", permitir todas las semanas (1-52)
                    startW = 1;
                    endW = 52;
                }

                var optionsHtml = '';
                for (var w = startW; w <= endW; w++) {
                    var isOrigin = (currentW && w === currentW);
                    var isTarget = (w === targetW || w === wPrev || w === wNext);
                    var isSelected = (w === targetW);

                    var optionStyle = '';
                    if (isOrigin || isTarget) {
                        optionStyle =
                            'background-color: #e0f2fe; color: #033966; font-weight: 800; padding: 6px;';
                    } else {
                        optionStyle = 'color: #1e293b; font-weight: 500;';
                    }

                    var selAttr = isSelected ? 'selected' : '';
                    optionsHtml +=
                        `<option value="${w}" ${selAttr} style="${optionStyle}">Semana ${w}</option>`;
                }

                var origLabel = currentW ? `Semana ${currentW}` : 'Sin Semana';
                var targetLabel = targetW ? `Semana ${targetW}` : 'Sin Semana';

                var modalHtml = `
                        <div style="background: #f8fafc; border: 1.5px solid rgba(3, 57, 102, 0.2); border-radius: 12px; padding: 18px 20px; margin-bottom: 20px; text-align: left; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; padding-bottom: 12px; border-bottom: 1px dashed rgba(3, 57, 102, 0.25);">
                                <span style="font-size: 0.88em; text-transform: uppercase; color: #64748b; font-weight: 700; letter-spacing: 0.5px;">Orden de Trabajo</span>
                                <span style="font-size: 1.35em; font-weight: 800; color: #033966;">OT #${otId}</span>
                            </div>
                            <div style="display: flex; gap: 14px; align-items: center; justify-content: space-between;">
                                <div style="flex: 1; background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 12px; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                                    <span style="display: block; font-size: 0.78em; text-transform: uppercase; color: #64748b; font-weight: 700; margin-bottom: 4px;">Semana Anterior</span>
                                    <span style="font-size: 1.2em; font-weight: 800; color: #334155;">${origLabel}</span>
                                </div>
                                <div style="color: #033966; font-weight: 900; font-size: 1.5em; flex-shrink: 0;">➔</div>
                                <div style="flex: 1; background: #e0f2fe; border: 2px solid #033966; border-radius: 10px; padding: 12px; text-align: center; box-shadow: 0 2px 6px rgba(3, 57, 102, 0.12);">
                                    <span style="display: block; font-size: 0.78em; text-transform: uppercase; color: #033966; font-weight: 800; margin-bottom: 4px;">Nueva Semana</span>
                                    <span id="swal-target-week-badge" style="font-size: 1.2em; font-weight: 800; color: #033966;">${targetLabel}</span>
                                </div>
                            </div>
                        </div>

                        <div style="text-align: left; margin-bottom: 6px;">
                            <label for="swal-week-select" style="display: block; font-size: 0.95em; font-weight: 700; color: #033966; margin-bottom: 8px;">
                                Selecciona el número de semana definitivo para la O.T.:
                            </label>
                            <select id="swal-week-select" onchange="var b=document.getElementById('swal-target-week-badge'); if(b) b.innerText='Semana '+this.value;" style="width: 100%; height: 44px; padding: 6px 14px; font-size: 1.05em; font-weight: 700; color: #033966; background-color: #ffffff; border: 1.5px solid #033966; border-radius: 8px; cursor: pointer; outline: none; box-sizing: border-box; font-family: inherit;">
                                ${optionsHtml}
                            </select>
                        </div>
                    `;

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Asignación de Número de Semana',
                        html: modalHtml,
                        width: '540px',
                        showCancelButton: true,
                        confirmButtonText: 'Guardar Orden y Semana',
                        cancelButtonText: 'Cancelar Cambio',
                        customClass: {
                            popup: 'gis-date-modal-popup',
                            title: 'gis-date-modal-title',
                            confirmButton: 'gis-swal-confirm',
                            cancelButton: 'gis-swal-cancel'
                        },
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: function() {
                            var sel = document.getElementById('swal-week-select');
                            if (sel) {
                                sel.addEventListener('change', function() {
                                    var badge = document.getElementById(
                                        'swal-target-week-badge');
                                    if (badge) {
                                        badge.innerText = 'Semana ' + this.value;
                                    }
                                });
                            }
                        },
                        preConfirm: function() {
                            var sel = document.getElementById('swal-week-select');
                            var val = sel ? parseInt(sel.value, 10) : null;
                            if (!val || isNaN(val)) {
                                Swal.showValidationMessage(
                                    'Debes seleccionar un número de semana válido.');
                                return false;
                            }
                            return val;
                        }
                    }).then(function(result) {
                        if (result.isConfirmed && result.value) {
                            var newWeek = result.value;
                            if (semanaCell) {
                                semanaCell.textContent = newWeek;
                            }
                            applyRowBg(activeDragRow, newWeek);
                            autoSaveTablePriorities(otId, newWeek);
                        } else {
                            // Revertir cambio si se cancela
                            if (originalNextSibling) {
                                originalParent.insertBefore(activeDragRow, originalNextSibling);
                            } else {
                                originalParent.appendChild(activeDragRow);
                            }
                            renumberRows();
                        }
                    });
                } else {
                    var promptWeek = prompt("Has movido la OT " + otId +
                        ". Ingresa el número de semana (1-52):", targetW);
                    if (promptWeek && promptWeek.trim() !== '') {
                        var parsedW = parseInt(promptWeek.replace(/[^0-9]/g, ''), 10);
                        if (parsedW > 0 && parsedW <= 52) {
                            if (semanaCell) semanaCell.textContent = parsedW;
                            applyRowBg(activeDragRow, parsedW);
                            autoSaveTablePriorities(otId, parsedW);
                        } else {
                            alert("Semana inválida. Se cancela el movimiento.");
                            if (originalNextSibling) {
                                originalParent.insertBefore(activeDragRow, originalNextSibling);
                            } else {
                                originalParent.appendChild(activeDragRow);
                            }
                            renumberRows();
                        }
                    } else {
                        if (originalNextSibling) {
                            originalParent.insertBefore(activeDragRow, originalNextSibling);
                        } else {
                            originalParent.appendChild(activeDragRow);
                        }
                        renumberRows();
                    }
                }
            });

            row.addEventListener('dragend', function() {
                row.draggable = false;
                row.classList.remove('is-row-dragging');
                document.querySelectorAll('#prioritiesTable tbody tr').forEach(function(r) {
                    r.classList.remove('drag-over-top', 'drag-over-bottom', 'is-row-dragging');
                });
                draggedRow = null;
            });
        }

        document.querySelectorAll('#prioritiesTable tbody tr').forEach(initRowDrag);

        function renumberRows() {
            var rows = document.querySelectorAll('#prioritiesTable tbody tr');
            rows.forEach(function(r, index) {
                var numCell = r.querySelector('.cell-row-num');
                if (numCell) {
                    numCell.textContent = index + 1;
                }
            });
        }

        function autoSaveTablePriorities(updatedOtId, updatedWeek) {
            if (isSavingPriorities) {
                savePrioritiesPending = true;
                return;
            }
            isSavingPriorities = true;
            savePrioritiesPending = false;

            updateTableStatus('saving');

            var rows = document.querySelectorAll('#prioritiesTable tbody tr');
            var priorities = [];
            rows.forEach(function(r, index) {
                var otId = r.dataset.otId;
                if (otId) {
                    var item = {
                        ot_id: otId,
                        prioridad: index + 1
                    };
                    if (updatedOtId && String(otId) === String(updatedOtId) && updatedWeek) {
                        item.semana_entrega_cliente = updatedWeek;
                    }
                    priorities.push(item);
                }
            });

            var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            fetch("{{ route('savePriorities') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        priorities: priorities
                    })
                })
                .then(function(res) {
                    return res.json();
                })
                .then(function(data) {
                    if (data && data.success) {
                        updateTableStatus('saved');
                        setTimeout(function() {
                            window.location.reload();
                        }, 300);
                    } else {
                        updateTableStatus('error');
                    }
                })
                .catch(function(err) {
                    console.error("Error al autoguardar prioridades:", err);
                    updateTableStatus('error');
                })
                .finally(function() {
                    isSavingPriorities = false;
                    if (savePrioritiesPending) {
                        autoSaveTablePriorities();
                    }
                });
        }

        function updateTableStatus(state) {
            var badge = document.getElementById('pm-status-badge');
            if (!badge) return;
            var spinner = badge.querySelector('.pm-table-spinner');
            var powerIcon = badge.querySelector('.pm-table-power-icon');
            var text = badge.querySelector('.pm-table-status-text');

            if (state === 'saving') {
                badge.className = 'pm-table-status-badge pm-status-saving no-print';
                if (spinner) spinner.hidden = false;
                if (powerIcon) powerIcon.style.display = 'none';
                if (text) text.textContent = 'Guardando orden...';
            } else if (state === 'saved') {
                badge.className = 'pm-table-status-badge pm-status-saved no-print';
                if (spinner) spinner.hidden = true;
                if (powerIcon) powerIcon.style.display = 'none';
                if (text) text.textContent = '✓ Orden Guardado';
            } else if (state === 'error') {
                badge.className = 'pm-table-status-badge pm-status-error no-print';
                if (spinner) spinner.hidden = true;
                if (powerIcon) powerIcon.style.display = 'none';
                if (text) text.textContent = '⚠ Error al guardar';
            } else {
                badge.className = 'pm-table-status-badge no-print';
                if (spinner) spinner.hidden = true;
                if (powerIcon) powerIcon.style.display = '';
                if (text) text.textContent = 'Modo Edición y Autoguardado Activo';
            }
        }
    });
</script>
