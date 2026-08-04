/**
 * show_wo_almacen.js
 * Lógica frontend para la vista de Almacén en showWO.
 */

document.addEventListener('DOMContentLoaded', () => {

    const rows = document.querySelectorAll('.fila-clase');

    // Refs a los elementos que se muestran / actualizan al seleccionar clase
    const claseDetail            = document.getElementById('clase-detail');
    const formRemision           = document.getElementById('form-remision');
    const formParcialidad        = document.getElementById('form-parcialidad');
    const placeholderRemision    = document.getElementById('placeholder-remision');
    const placeholderParcialidad = document.getElementById('placeholder-parcialidad');
    const avisoSinRemision       = document.getElementById('aviso-sin-remision');
    const resumenEl              = document.getElementById('resumen-parcialidades');
    const resumenTratamientoEl   = document.getElementById('resumen-tratamientos');

    rows.forEach(row => {
        row.addEventListener('click', () => {
            // ── Resaltar fila ──
            rows.forEach(r => r.classList.remove('selected'));
            row.classList.add('selected');

            const idClase   = row.dataset.idClase;
            const nombre    = row.dataset.nombre;
            const tamanio   = row.dataset.tamanio;
            const pedido    = row.dataset.pedido;
            const piezas    = row.dataset.piezas;
            const idOt      = row.dataset.idOt;
            const composicion = row.dataset.composicion;
            const soldadura   = row.dataset.soldadura;

            // ── Panel izquierdo: campos editables ──
            document.getElementById('clase-nombre').value          = nombre + ' – ' + tamanio;
            
            const inputComposicion = document.getElementById('input-composicion');
            const inputSoldadura   = document.getElementById('input-soldadura');
            inputComposicion.value = (composicion && composicion !== 'null' && composicion.trim() !== '') ? composicion : 'N/A';
            inputSoldadura.value   = (soldadura && soldadura !== 'null' && soldadura.trim() !== '') ? soldadura : 'N/A';
            
            const inputPedido = document.getElementById('input-pedido');
            const inputPiezas = document.getElementById('input-piezas');
            inputPedido.value          = pedido;
            inputPiezas.value          = piezas;
            inputPedido.disabled       = true;
            inputPiezas.disabled       = true;

            const btnHabilitar = document.getElementById('btn-habilitar-edicion');
            const btnGuardar = document.getElementById('btn-guardar-clase');
            if (btnHabilitar) {
                btnHabilitar.hidden = false;
                btnHabilitar.classList.remove('hidden');
            }
            if (btnGuardar) {
                btnGuardar.hidden = true;
                btnGuardar.classList.add('hidden');
            }

            document.getElementById('hidden-idClase').value        = idClase;
            document.getElementById('hidden-clase-nombre').value   = nombre;
            document.getElementById('hidden-clase-tamanio').value  = tamanio;
            claseDetail.classList.add('visible');

            // ── Formulario parcialidad y tratamiento: mostrar directamente ──
            document.getElementById('hidden-idOtParcialidad').value    = idOt;
            document.getElementById('hidden-idClaseParcialidad').value = idClase;
            if (placeholderParcialidad) {
                placeholderParcialidad.hidden = true;
                placeholderParcialidad.classList.add('hidden');
            }
            if (formParcialidad) {
                formParcialidad.hidden = false;
                formParcialidad.classList.remove('hidden');
            }
            if (avisoSinRemision) {
                avisoSinRemision.hidden = true;
                avisoSinRemision.classList.add('hidden');
            }

            const placeholderTratamiento = document.getElementById('placeholder-tratamiento');
            const formTratamiento        = document.getElementById('form-tratamiento');
            if (placeholderTratamiento) {
                placeholderTratamiento.hidden = true;
                placeholderTratamiento.classList.add('hidden');
            }
            if (formTratamiento) {
                document.getElementById('hidden-idOtTratamiento').value    = idOt;
                document.getElementById('hidden-idClaseTratamiento').value = idClase;
                formTratamiento.hidden = false;
                formTratamiento.classList.remove('hidden');
            }

            // ── Filtrar listas de remisiones, parcialidades y tratamientos por clase ──
            filterGroups('.grupo-remision', idClase);
            filterGroups('.grupo-parcialidad', idClase);
            filterGroups('.grupo-tratamiento', idClase);

            // ── Actualizar resumen de parcialidades ──
            updateResumen(idClase, pedido, piezas);
            updateResumenTratamiento(idClase, pedido, piezas);

            // Guardar clase activa en localStorage
            localStorage.setItem('selected_class_id_' + idOt, idClase);
        });
    });

    /**
     * Muestra solo el grupo de la clase seleccionada, oculta los demás.
     */
    function filterGroups(selector, idClase) {
        document.querySelectorAll(selector).forEach(g => {
            g.classList.toggle("hidden", !(g.dataset.idClase === idClase ));
        });
    }

    /**
     * Devuelve { idClase, pedido, piezas } de la clase activa (fila seleccionada).
     */
    function getActiveClaseInfo() {
        const activeRow = document.querySelector('.fila-clase.selected');
        if (!activeRow) return null;
        return {
            idClase : activeRow.dataset.idClase,
            pedido  : activeRow.dataset.pedido,
            piezas  : activeRow.dataset.piezas,
        };
    }

    /**
     * Suma las cantidades de las parcialidades de la clase y actualiza el resumen.
     * Si una fila está en modo edición (.editando), usa el input en lugar del badge.
     */
    function updateResumen(idClase, pedido, piezas) {
        if (!resumenEl) return;
        resumenEl.hidden = false;

        let total = 0;
        document.querySelectorAll('.grupo-parcialidad').forEach(g => {
            if (g.dataset.idClase === idClase) {
                g.querySelectorAll('.fila-parcialidad-item').forEach(item => {
                    if (item.classList.contains('editando')) {
                        // En modo edición: leer el input directamente
                        const editInput = item.querySelector('.edit-cantidad');
                        total += parseInt(editInput ? editInput.value : 0) || 0;
                    } else {
                        // Modo vista: leer el badge
                        const badge = item.querySelector('.badge-cantidad');
                        total += parseInt(badge ? badge.textContent.trim() : 0) || 0;
                    }
                });
            }
        });

        const pedidoNum     = parseInt(pedido) || 0;
        const piezasNum     = parseInt(piezas)  || 0;
        const pct = piezasNum > 0 ? Math.min(100, Math.round((total / piezasNum) * 100)) : 0;

        resumenEl.querySelector('.val-recibido').textContent = total;
        resumenEl.querySelector('.val-pedido').textContent   = pedidoNum;

        const valConsignacion = resumenEl.querySelector('.val-consignacion');
        if (valConsignacion) {
            valConsignacion.textContent = piezasNum || '0';
        }
        resumenEl.querySelector('.val-pct').textContent = pct + '%';

        const bar = resumenEl.querySelector('.progress-bar-fill');
        if (bar) {
            bar.style.width = pct + '%';
            bar.style.background = pct >= 100
                ? '#0a8504'
                : pct >= 50 ? '#f39c12' : '#033966';
        }
    }

    /**
     * Suma las cantidades de tratamientos de la clase y actualiza el resumen.
     */
    function updateResumenTratamiento(idClase, pedido, piezas) {
        if (!resumenTratamientoEl) return;
        resumenTratamientoEl.hidden = false;

        let total = 0;
        document.querySelectorAll('.grupo-tratamiento').forEach(g => {
            if (g.dataset.idClase === idClase) {
                g.querySelectorAll('.fila-tratamiento-item').forEach(item => {
                    if (item.classList.contains('editando')) {
                        const editInput = item.querySelector('.edit-cantidad');
                        total += parseInt(editInput ? editInput.value : 0) || 0;
                    } else {
                        const badge = item.querySelector('.badge-cantidad');
                        total += parseInt(badge ? badge.textContent.trim() : 0) || 0;
                    }
                });
            }
        });

        let totalParcialidades = 0;
        document.querySelectorAll(`.grupo-parcialidad[data-id-clase="${idClase}"] .fila-parcialidad-item`).forEach(fila => {
            const num = parseInt(fila.getAttribute('data-cantidad')) || 0;
            totalParcialidades += num;
        });

        const pedidoNum = parseInt(pedido) || 0;
        const piezasNum = parseInt(piezas) || 0;
        const pct = piezasNum > 0 ? Math.min(100, Math.round((total / piezasNum) * 100)) : 0;

        resumenTratamientoEl.querySelector('.val-tratadas').textContent = total;
        
        const valPedido = resumenTratamientoEl.querySelector('.val-pedido-tratamiento');
        if (valPedido) {
            valPedido.textContent = totalParcialidades;
        }

        const formTratamiento = document.getElementById('form-tratamiento');
        if (formTratamiento) {
            if (totalParcialidades === 0) {
                formTratamiento.classList.add("alm-form-disabled");
                formTratamiento.setAttribute('title', 'Se requiere recibir piezas en parcialidades primero');
            } else {
                formTratamiento.classList.remove("alm-form-disabled");
                formTratamiento.removeAttribute('title');
            }
        }

        const valConsignacion = resumenTratamientoEl.querySelector('.val-consignacion-tratamiento');
        if (valConsignacion) {
            valConsignacion.textContent = piezasNum || '0';
        }
        resumenTratamientoEl.querySelector('.val-pct-tratamiento').textContent = pct + '%';

        const bar = resumenTratamientoEl.querySelector('.progress-bar-fill-tratamiento');
        if (bar) {
            bar.style.width = pct + '%';
            bar.style.background = pct >= 100
                ? '#0a8504'
                : pct >= 50 ? '#f39c12' : '#033966';
        }
    }

    // ── Confirmaciones de eliminación ──
    document.querySelectorAll('.form-eliminar-remision').forEach(form => {
        form.addEventListener('submit', e => {
            if (!confirm('¿Eliminar esta remisión?')) e.preventDefault();
        });
    });

    let pendingDeleteForm = null;

    document.querySelectorAll('.form-eliminar-parcialidad, .form-eliminar-tratamiento').forEach(form => {
        form.addEventListener('submit', e => {
            e.preventDefault();
            
            const layoutMain = document.getElementById('almacen-layout-main');
            const userPerfil = layoutMain ? layoutMain.dataset.userPerfil : '';
            
            const isTratamiento = form.classList.contains('form-eliminar-tratamiento');
            const msgConfirm = isTratamiento ? '¿Eliminar este tratamiento?' : '¿Eliminar esta parcialidad?';
            const msgModal = isTratamiento 
                ? 'Ingresa la contraseña de un Administrador o Master para eliminar este tratamiento:' 
                : 'Ingresa la contraseña de un Administrador o Master para eliminar esta parcialidad:';
            
            if (userPerfil != '1' && userPerfil != '3') {
                pendingDeleteForm = form;
                const modal = document.getElementById('modal-confirm-delete');
                const passwordInput = document.getElementById('modal-delete-password');
                const modalMsg = modal ? modal.querySelector('p') : null;
                if (modalMsg) {
                    modalMsg.textContent = msgModal;
                }
                if (modal && passwordInput) {
                    passwordInput.value = '';
                    modal.hidden = false;
                    passwordInput.focus();
                }
            } else {
                if (confirm(msgConfirm)) {
                    form.submit();
                }
            }
        });
    });

    const btnModalConfirm = document.getElementById('btn-modal-delete-confirm');
    const btnModalCancel = document.getElementById('btn-modal-delete-cancel');
    const modalDelete = document.getElementById('modal-confirm-delete');

    if (btnModalConfirm) {
        btnModalConfirm.addEventListener('click', () => {
            const passwordInput = document.getElementById('modal-delete-password');
            const password = passwordInput ? passwordInput.value : '';
            if (!password) {
                alert('Debes ingresar una contraseña.');
                return;
            }
            if (pendingDeleteForm) {
                pendingDeleteForm.querySelector('.input-confirm-password').value = password;
                if (modalDelete) modalDelete.hidden = true;
                pendingDeleteForm.submit();
                pendingDeleteForm = null;
            }
        });
    }

    if (btnModalCancel) {
        btnModalCancel.addEventListener('click', () => {
            if (modalDelete) modalDelete.hidden = true;
            pendingDeleteForm = null;
        });
    }

    // ── Lógica de edición de parcialidades en línea ──
    document.querySelectorAll('.btn-editar-parcialidad').forEach(btn => {
        btn.addEventListener('click', () => {
            const row = btn.closest('.fila-parcialidad-item');
            row.classList.add('editando');

            // Al entrar en modo edición, el input ya tiene el valor actual;
            // forzar un recálculo del resumen para que refleje el estado correcto.
            const info = getActiveClaseInfo();
            if (info) updateResumen(info.idClase, info.pedido, info.piezas);
        });
    });

    // Actualizar resumen en tiempo real mientras el usuario escribe en edit-cantidad
    document.querySelectorAll('.edit-cantidad').forEach(input => {
        input.addEventListener('input', () => {
            const info = getActiveClaseInfo();
            if (info) updateResumen(info.idClase, info.pedido, info.piezas);
        });
    });

    document.querySelectorAll('.btn-cancelar-parcialidad').forEach(btn => {
        btn.addEventListener('click', () => {
            const row = btn.closest('.fila-parcialidad-item');
            
            // Restaurar valores iniciales
            row.querySelector('.edit-fecha').value = row.dataset.fecha;
            row.querySelector('.edit-cantidad').value = row.dataset.cantidad;
            row.querySelector('.edit-descripcion').value = row.dataset.descripcion;
            
            const fileInput = row.querySelector('.edit-archivo');
            if (fileInput) {
                fileInput.value = '';
            }
            row.classList.remove('editando');

            // Recalcular resumen al cancelar (volver a valores originales)
            const info = getActiveClaseInfo();
            if (info) updateResumen(info.idClase, info.pedido, info.piezas);
        });
    });

    document.querySelectorAll('.btn-guardar-parcialidad').forEach(btn => {
        btn.addEventListener('click', () => {
            const row = btn.closest('.fila-parcialidad-item');
            const id = row.dataset.id;
            const cantidad = parseInt(row.querySelector('.edit-cantidad').value) || 0;
            const descripcion = row.querySelector('.edit-descripcion').value;
            const fecha = row.querySelector('.edit-fecha').value;
            
            if (!cantidad || cantidad < 1) {
                alert('La cantidad debe ser al menos 1.');
                return;
            }
            if (!fecha) {
                alert('Debes seleccionar una fecha.');
                return;
            }
            
            // Validar límite
            const activeRow = document.querySelector('.fila-clase.selected');
            if (activeRow) {
                const limit = parseInt(activeRow.dataset.piezas) || 0;
                let currentTotal = 0;
                const idClase = activeRow.dataset.idClase;
                document.querySelectorAll('.grupo-parcialidad').forEach(g => {
                    if (g.dataset.idClase === idClase) {
                        g.querySelectorAll('.fila-parcialidad-item').forEach(item => {
                            if (item.dataset.id !== id) {
                                currentTotal += parseInt(item.dataset.cantidad) || 0;
                            }
                        });
                    }
                });

                if (currentTotal + cantidad > limit) {
                    alert(`No puedes recibir más piezas de las que tiene en Consignación (${limit}). Las otras entregas suman ${currentTotal}, intentando cambiar esta a: ${cantidad}.`);
                    return;
                }
            }
            
            const formData = new FormData();
            formData.append('_token', document.querySelector('input[name="_token"]').value);
            formData.append('_method', 'PUT');
            formData.append('cantidad', cantidad);
            formData.append('descripcion', descripcion);
            formData.append('fecha_recepcion', fecha);
            
            const fileInput = row.querySelector('.edit-archivo');
            if (fileInput && fileInput.files.length > 0) {
                formData.append('archivo', fileInput.files[0]);
            }
            
            // Submit via Fetch API
            fetch(row.dataset.updateUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => {
                if (res.ok) {
                    window.location.reload();
                } else {
                    res.json().then(data => {
                        alert(data.message || 'Error al guardar los cambios.');
                    }).catch(() => {
                        alert('Error al guardar los cambios.');
                    });
                }
            })
            .catch(err => {
                console.error(err);
                alert('Error al procesar la solicitud.');
            });
        });
    });

    // Habilitar edición de pedido y piezas consignación al dar clic en el icono de edición
    const btnHabilitar = document.getElementById('btn-habilitar-edicion');
    if (btnHabilitar) {
        btnHabilitar.addEventListener('click', () => {
            document.getElementById('input-pedido').disabled = false;
            document.getElementById('input-piezas').disabled = false;
            btnHabilitar.hidden = true;
            btnHabilitar.classList.add('hidden');
            const btnGuardar = document.getElementById('btn-guardar-clase');
            if (btnGuardar) {
                btnGuardar.hidden = false;
                btnGuardar.classList.remove('hidden');
            }
        });
    }

    // Habilitar/deshabilitar botón registrar según cantidad, archivo y fecha
    const inputCantidad = document.getElementById('parcialidad-cantidad');
    const inputArchivo = document.getElementById('parcialidad-archivo');
    const inputFecha = document.getElementById('parcialidad-fecha');
    const btnRegistrar = document.getElementById('btn-registrar-parcialidad');

    function checkRegistrarButton() {
        if (btnRegistrar && inputCantidad && inputArchivo && inputFecha) {
            const hasQty = inputCantidad.value.trim() !== '' && parseInt(inputCantidad.value) >= 1;
            const hasFile = inputArchivo.files && inputArchivo.files.length > 0;
            const hasDate = inputFecha.value.trim() !== '';
            btnRegistrar.disabled = !(hasQty && hasFile && hasDate);
        }
    }

    if (inputCantidad) {
        inputCantidad.addEventListener('input', checkRegistrarButton);
    }
    if (inputArchivo) {
        inputArchivo.addEventListener('change', checkRegistrarButton);
    }
    if (inputFecha) {
        inputFecha.addEventListener('input', checkRegistrarButton);
        inputFecha.addEventListener('change', checkRegistrarButton);
    }

    // Validar límite al enviar el formulario de nueva parcialidad
    const formParcialidadEl = document.getElementById('form-parcialidad');
    if (formParcialidadEl) {
        formParcialidadEl.addEventListener('submit', (e) => {
            const activeRow = document.querySelector('.fila-clase.selected');
            if (!activeRow) return;

            const limit = parseInt(activeRow.dataset.piezas) || 0;
            const newQty = parseInt(document.getElementById('parcialidad-cantidad').value) || 0;
            
            let currentTotal = 0;
            const idClase = activeRow.dataset.idClase;
            document.querySelectorAll('.grupo-parcialidad').forEach(g => {
                if (g.dataset.idClase === idClase) {
                    g.querySelectorAll('.badge-cantidad').forEach(badge => {
                        currentTotal += parseInt(badge.textContent.trim()) || 0;
                    });
                }
            });

            if (currentTotal + newQty > limit) {
                e.preventDefault();
                alert(`No puedes recibir más piezas de las que tiene en Consignación (${limit}). Actualmente recibidas: ${currentTotal}, ingresando: ${newQty}.`);
            }
        });
    }

    // Validar límite al enviar el formulario de nuevo tratamiento
    const formTratamientoEl = document.getElementById('form-tratamiento');
    if (formTratamientoEl) {
        formTratamientoEl.addEventListener('submit', (e) => {
            const activeRow = document.querySelector('.fila-clase.selected');
            if (!activeRow) return;

            const idClase = activeRow.dataset.idClase;
            let limit = 0;
            document.querySelectorAll(`.grupo-parcialidad[data-id-clase="${idClase}"] .fila-parcialidad-item`).forEach(fila => {
                limit += parseInt(fila.getAttribute('data-cantidad')) || 0;
            });

            const newQty = parseInt(document.getElementById('tratamiento-cantidad').value) || 0;
            
            let currentTotal = 0;
            document.querySelectorAll('.grupo-tratamiento').forEach(g => {
                if (g.dataset.idClase === idClase) {
                    g.querySelectorAll('.badge-cantidad').forEach(badge => {
                        currentTotal += parseInt(badge.textContent.trim()) || 0;
                    });
                }
            });

            if (currentTotal + newQty > limit) {
                e.preventDefault();
                alert(`No puedes tratar más piezas de las que se han recibido en almacén (${limit}). Actualmente tratadas: ${currentTotal}, ingresando: ${newQty}.`);
            }
        });
    }

    // ── Lógica para deshabilitar botón de Tratamiento Térmico hasta que cantidad y archivo estén llenos ──
    const btnRegistrarTratamiento = document.getElementById('btn-registrar-tratamiento');
    const inputCantidadTratamiento = document.getElementById('tratamiento-cantidad');
    const inputArchivoTratamiento = document.getElementById('tratamiento-archivo');

    function checkTratamientoForm() {
        if (!btnRegistrarTratamiento || !inputCantidadTratamiento || !inputArchivoTratamiento) return;
        const cantidadValue = inputCantidadTratamiento.value.trim();
        const fileHasValue = inputArchivoTratamiento.files.length > 0;

        if (cantidadValue !== '' && parseInt(cantidadValue) > 0 && fileHasValue) {
            btnRegistrarTratamiento.disabled = false;
            btnRegistrarTratamiento.classList.remove("alm-btn-disabled");
        } else {
            btnRegistrarTratamiento.disabled = true;
            btnRegistrarTratamiento.classList.add("alm-btn-disabled");
        }
    }

    if (inputCantidadTratamiento && inputArchivoTratamiento) {
        // Inicialmente deshabilitado si está vacío
        checkTratamientoForm();
        inputCantidadTratamiento.addEventListener('input', checkTratamientoForm);
        inputArchivoTratamiento.addEventListener('change', checkTratamientoForm);
    }

    // ── Lógica de edición de tratamientos en línea ──
    document.querySelectorAll('.btn-editar-tratamiento').forEach(btn => {
        btn.addEventListener('click', () => {
            const row = btn.closest('.fila-tratamiento-item');
            row.classList.add('editando');
            const info = getActiveClaseInfo();
            if (info) updateResumenTratamiento(info.idClase, info.pedido, info.piezas);
        });
    });

    document.querySelectorAll('.fila-tratamiento-item .edit-cantidad').forEach(input => {
        input.addEventListener('input', () => {
            const info = getActiveClaseInfo();
            if (info) updateResumenTratamiento(info.idClase, info.pedido, info.piezas);
        });
    });

    document.querySelectorAll('.btn-cancelar-tratamiento').forEach(btn => {
        btn.addEventListener('click', () => {
            const row = btn.closest('.fila-tratamiento-item');
            
            // Restaurar valores iniciales
            row.querySelector('.edit-cantidad').value = row.dataset.cantidad;
            row.querySelector('.edit-descripcion').value = row.dataset.descripcion;
            
            const fileInput = row.querySelector('.edit-archivo');
            if (fileInput) {
                fileInput.value = '';
            }
            row.classList.remove('editando');

            const info = getActiveClaseInfo();
            if (info) updateResumenTratamiento(info.idClase, info.pedido, info.piezas);
        });
    });

    document.querySelectorAll('.btn-guardar-tratamiento').forEach(btn => {
        btn.addEventListener('click', () => {
            const row = btn.closest('.fila-tratamiento-item');
            const id = row.dataset.id;
            const cantidad = parseInt(row.querySelector('.edit-cantidad').value) || 0;
            const descripcion = row.querySelector('.edit-descripcion').value;
            
            if (!cantidad || cantidad < 1) {
                alert('La cantidad debe ser al menos 1.');
                return;
            }
            
            // Validar límite
            const activeRow = document.querySelector('.fila-clase.selected');
            if (activeRow) {
                const idClase = activeRow.dataset.idClase;
                let limit = 0;
                document.querySelectorAll(`.grupo-parcialidad[data-id-clase="${idClase}"] .fila-parcialidad-item`).forEach(fila => {
                    limit += parseInt(fila.getAttribute('data-cantidad')) || 0;
                });
                
                let currentTotal = 0;
                document.querySelectorAll('.grupo-tratamiento').forEach(g => {
                    if (g.dataset.idClase === idClase) {
                        g.querySelectorAll('.fila-tratamiento-item').forEach(item => {
                            if (item.dataset.id !== id) {
                                currentTotal += parseInt(item.dataset.cantidad) || 0;
                            }
                        });
                    }
                });

                if (currentTotal + cantidad > limit) {
                    alert(`No puedes tratar más piezas de las que se han recibido en almacén (${limit}). Los otros tratamientos suman ${currentTotal}, intentando cambiar esta a: ${cantidad}.`);
                    return;
                }
            }
            
            const formData = new FormData();
            formData.append('_token', document.querySelector('input[name="_token"]').value);
            formData.append('_method', 'PUT');
            formData.append('cantidad', cantidad);
            formData.append('descripcion', descripcion);
            
            const fileInput = row.querySelector('.edit-archivo');
            if (fileInput && fileInput.files.length > 0) {
                formData.append('archivo', fileInput.files[0]);
            }
            
            // Submit via Fetch API
            fetch(row.dataset.updateUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => {
                if (res.ok) {
                    window.location.reload();
                } else {
                    res.json().then(data => {
                        alert(data.message || 'Error al guardar los cambios.');
                    }).catch(() => {
                        alert('Error al guardar los cambios.');
                    });
                }
            })
            .catch(err => {
                console.error(err);
                alert('Error al procesar la solicitud.');
            });
        });
    });

    // Restaurar la última clase seleccionada tras recargas de página
    if (rows.length > 0) {
        const firstRowIdOt = rows[0].dataset.idOt;
        const savedId = localStorage.getItem('selected_class_id_' + firstRowIdOt);
        if (savedId) {
            const savedRow = document.querySelector(`.fila-clase[data-id-clase="${savedId}"]`);
            if (savedRow) {
                savedRow.click();
            } else {
                rows[0].click();
            }
        } else {
            rows[0].click();
        }
    }

    // ── Lógica de Polling (Sincronización en tiempo real) ──
    if (window.classesDataUrl && typeof otIdParaPolleo !== 'undefined') {
        setInterval(async () => {
            try {
                const res = await fetch(`${window.classesDataUrl}/${otIdParaPolleo}`);
                if (!res.ok) return;
                const data = await res.json();
                
                if (data && Array.isArray(data)) {
                    data.forEach(updatedClass => {
                        // Buscar la fila correspondiente
                        const row = document.querySelector(`.fila-clase[data-id-clase="${updatedClass.id}"]`);
                        if (row) {
                            const currentPedido = parseInt(row.dataset.pedido);
                            const currentPiezas = parseInt(row.dataset.piezas);
                            
                            // Si cambiaron los datos
                            if (currentPedido !== updatedClass.pedido || currentPiezas !== updatedClass.piezas) {
                                // Actualizar dataset
                                row.dataset.pedido = updatedClass.pedido;
                                row.dataset.piezas = updatedClass.piezas;
                                
                                // Actualizar texto visual (las celdas asumiendo el orden: nombre, tamaño, piezas, pedido)
                                const cells = row.querySelectorAll('td');
                                if (cells.length >= 4) {
                                    cells[2].textContent = updatedClass.piezas;
                                    cells[3].textContent = updatedClass.pedido;
                                }

                                // Si la clase está seleccionada actualmente, recalcular y actualizar panel izquierdo
                                if (row.classList.contains('selected')) {
                                    // Actualizar inputs si NO estamos en modo edición
                                    const btnGuardar = document.getElementById('btn-guardar-clase');
                                    const isEditing = btnGuardar && !btnGuardar.classList.contains("hidden");
                                    
                                    if (!isEditing) {
                                        const inputPedido = document.getElementById('input-pedido');
                                        const inputPiezas = document.getElementById('input-piezas');
                                        if (inputPedido) inputPedido.value = updatedClass.pedido;
                                        if (inputPiezas) inputPiezas.value = updatedClass.piezas;
                                    }
                                    
                                    // Actualizar resúmenes (esto recalcula porcentajes y barra de progreso)
                                    updateResumen(updatedClass.id, updatedClass.pedido, updatedClass.piezas);
                                    updateResumenTratamiento(updatedClass.id, updatedClass.pedido, updatedClass.piezas);
                                }
                            }
                        }
                    });
                }
            } catch (e) {
                // Silencioso en caso de error de red temporal
            }
        }, 15000); // Polling cada 15 segundos
    }

});
