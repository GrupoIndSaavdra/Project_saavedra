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

            // ── Panel izquierdo: campos editables ──
            document.getElementById('clase-nombre').value          = nombre + ' – ' + tamanio;
            
            const inputPedido = document.getElementById('input-pedido');
            const inputPiezas = document.getElementById('input-piezas');
            inputPedido.value          = pedido;
            inputPiezas.value          = piezas;
            inputPedido.disabled       = true;
            inputPiezas.disabled       = true;

            const btnHabilitar = document.getElementById('btn-habilitar-edicion');
            const btnGuardar = document.getElementById('btn-guardar-clase');
            if (btnHabilitar) btnHabilitar.style.display = '';
            if (btnGuardar) btnGuardar.style.display = 'none';

            document.getElementById('hidden-idClase').value        = idClase;
            document.getElementById('hidden-clase-nombre').value   = nombre;
            document.getElementById('hidden-clase-tamanio').value  = tamanio;
            claseDetail.classList.add('visible');

            // ── Formulario parcialidad: mostrar directamente ──
            document.getElementById('hidden-idOtParcialidad').value    = idOt;
            document.getElementById('hidden-idClaseParcialidad').value = idClase;
            placeholderParcialidad.style.display = 'none';
            formParcialidad.style.display = '';
            if (avisoSinRemision) avisoSinRemision.style.display = 'none';

            // ── Filtrar listas de remisiones y parcialidades por clase ──
            filterGroups('.grupo-remision', idClase);
            filterGroups('.grupo-parcialidad', idClase);

            // ── Actualizar resumen de parcialidades ──
            updateResumen(idClase, pedido, piezas);

            // Guardar clase activa en localStorage
            localStorage.setItem('selected_class_id_' + idOt, idClase);
        });
    });

    /**
     * Muestra solo el grupo de la clase seleccionada, oculta los demás.
     */
    function filterGroups(selector, idClase) {
        document.querySelectorAll(selector).forEach(g => {
            g.style.display = g.dataset.idClase === idClase ? '' : 'none';
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
        resumenEl.style.display = 'flex';

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

    // ── Confirmaciones de eliminación ──
    document.querySelectorAll('.form-eliminar-remision').forEach(form => {
        form.addEventListener('submit', e => {
            if (!confirm('¿Eliminar esta remisión?')) e.preventDefault();
        });
    });

    let pendingDeleteForm = null;

    document.querySelectorAll('.form-eliminar-parcialidad').forEach(form => {
        form.addEventListener('submit', e => {
            e.preventDefault();
            
            const layoutMain = document.getElementById('almacen-layout-main');
            const userPerfil = layoutMain ? layoutMain.dataset.userPerfil : '';
            
            if (userPerfil != '1' && userPerfil != '3') {
                pendingDeleteForm = form;
                const modal = document.getElementById('modal-confirm-delete');
                const passwordInput = document.getElementById('modal-delete-password');
                if (modal && passwordInput) {
                    passwordInput.value = '';
                    modal.style.display = 'flex';
                    passwordInput.focus();
                }
            } else {
                if (confirm('¿Eliminar esta parcialidad?')) {
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
                if (modalDelete) modalDelete.style.display = 'none';
                pendingDeleteForm.submit();
                pendingDeleteForm = null;
            }
        });
    }

    if (btnModalCancel) {
        btnModalCancel.addEventListener('click', () => {
            if (modalDelete) modalDelete.style.display = 'none';
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
            btnHabilitar.style.display = 'none';
            const btnGuardar = document.getElementById('btn-guardar-clase');
            if (btnGuardar) btnGuardar.style.display = '';
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
});
