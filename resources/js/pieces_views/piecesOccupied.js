document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    // Elementos del DOM
    const selectOt = document.getElementById('select-ot');
    const selectClase = document.getElementById('select-clase');
    const selectProceso = document.getElementById('select-proceso');
    const btnRefresh = document.getElementById('btn-refresh');
    const tableBody = document.getElementById('pieces-table-body');

    // KPI Counters
    const statTotalOccupied = document.getElementById('stat-total-occupied');
    const statTotalOrphans = document.getElementById('stat-total-orphans');
    const statAffectedOts = document.getElementById('stat-affected-ots');

    // Modal elements
    const editModal = document.getElementById('edit-piece-modal');
    const btnCloseModal = document.getElementById('btn-close-modal');
    const btnCancelEdit = document.getElementById('btn-cancel-edit');
    const formEditPiece = document.getElementById('form-edit-piece');
    const btnDeletePiece = document.getElementById('btn-delete-piece');

    // Modal de confirmación nativo GIS
    const confirmModal = document.getElementById('confirm-action-modal');
    const btnCloseConfirm = document.getElementById('btn-close-confirm');
    const btnCancelConfirm = document.getElementById('btn-cancel-confirm');
    const btnDoConfirm = document.getElementById('btn-do-confirm');
    const confirmTitle = document.getElementById('confirm-modal-title');
    const confirmText = document.getElementById('confirm-modal-text');
    const confirmIcon = document.getElementById('confirm-modal-icon');
    const confirmPillOt = document.getElementById('confirm-pill-ot');
    const confirmPillClase = document.getElementById('confirm-pill-clase');
    const confirmPillProceso = document.getElementById('confirm-pill-proceso');

    // Pestañas y Auditoría Administrativa
    const tabBtnPieces = document.getElementById('tab-btn-pieces');
    const tabBtnLogs = document.getElementById('tab-btn-logs');
    const tabBadgePieces = document.getElementById('tab-badge-pieces');
    const tabBadgeLogs = document.getElementById('tab-badge-logs');
    const containerPiecesTable = document.getElementById('container-pieces-table');
    const containerLogsTable = document.getElementById('container-logs-table');
    const logsTableBody = document.getElementById('logs-table-body');
    const footerCount = document.getElementById('footer-count-text');
    const footerLegendNote = document.getElementById('footer-legend-note');

    let activeTab = 'pieces';
    let currentLogsData = [];
    let pendingConfirmAction = null;
    let currentPiecesData = [];

    // Helper: Verificación segura de SweetAlert2 con fallback nativo
    const hasSwal = () => typeof Swal !== 'undefined';

    const showToast = (icon, title) => {
        if (typeof window.gisToast === 'function') {
            window.gisToast(title, icon);
            return;
        }
        if (hasSwal()) {
            Swal.mixin({
                toast: true,
                position: 'bottom-end',
                showConfirmButton: false,
                timer: 3200,
                timerProgressBar: true,
                backdrop: false,
                showClass: {
                    popup: 'swal2-toast-in',
                },
                hideClass: {
                    popup: 'swal2-toast-out',
                },
            }).fire({ icon, title });
        } else {
            console.log(`[Toast ${icon}] ${title}`);
        }
    };

    const showError = (title, text) => {
        if (typeof window.gisAlert === 'function') {
            window.gisAlert({
                title,
                text,
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'Entendido'
            });
            return;
        }
        if (hasSwal()) {
            Swal.fire({
                title,
                text,
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'Entendido',
                customClass: { popup: 'swal2-gis-popup' }
            });
        } else if (window.toastpremium) {
            window.toastpremium(`${title}: ${text}`, 'error');
        }
    };

    /**
     * Abre el modal de confirmación nativo GIS con animación ultra-fluida.
     */
    function openConfirmDialog({
        title,
        text,
        icon,
        confirmBtnText = 'Continuar',
        isDanger = false,
        ot = '-',
        clase = '-',
        proceso = '-',
        onConfirm
    }) {
        if (confirmTitle) confirmTitle.textContent = title;
        if (confirmText) confirmText.textContent = text;
        if (confirmIcon && icon) confirmIcon.src = icon;
        if (btnDoConfirm) {
            btnDoConfirm.textContent = confirmBtnText;
            if (isDanger) {
                btnDoConfirm.classList.add('btn-danger-confirm');
            } else {
                btnDoConfirm.classList.remove('btn-danger-confirm');
            }
        }

        if (confirmPillOt) confirmPillOt.textContent = ot;
        if (confirmPillClase) confirmPillClase.textContent = clase;
        if (confirmPillProceso) confirmPillProceso.textContent = proceso;

        pendingConfirmAction = onConfirm;
        if (confirmModal) confirmModal.style.display = 'flex';
    }

    function closeConfirmDialog() {
        if (confirmModal) confirmModal.style.display = 'none';
        pendingConfirmAction = null;
    }

    if (btnCloseConfirm) btnCloseConfirm.addEventListener('click', closeConfirmDialog);
    if (btnCancelConfirm) btnCancelConfirm.addEventListener('click', closeConfirmDialog);
    if (confirmModal) {
        confirmModal.addEventListener('click', (e) => {
            if (e.target === confirmModal) closeConfirmDialog();
        });
    }

    if (btnDoConfirm) {
        btnDoConfirm.addEventListener('click', async () => {
            if (typeof pendingConfirmAction === 'function') {
                const origText = btnDoConfirm.textContent;
                btnDoConfirm.disabled = true;
                btnDoConfirm.textContent = 'Procesando...';
                try {
                    await pendingConfirmAction();
                    closeConfirmDialog();
                } catch (err) {
                    console.error('Error en confirmación:', err);
                    showError('Error', err.message || 'Error en la operación');
                } finally {
                    btnDoConfirm.disabled = false;
                    btnDoConfirm.textContent = origText;
                }
            }
        });
    }

    /**
     * Carga y renderiza las piezas ocupadas desde el servidor.
     * @param {boolean} isSilent - Si es true, refresca datos sin colapsar la tabla con spinner.
     */
    async function loadOccupiedPieces(isSilent = false) {
        if (!isSilent) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="9" class="empty-state">
                        <img src="${window.images.loading}" class="loading-gif" alt="Cargando..." />
                        <p>Consultando piezas ocupadas en los procesos...</p>
                    </td>
                </tr>
            `;
        } else {
            tableBody.style.transition = 'opacity 0.2s ease';
            tableBody.style.opacity = '0.5';
        }

        try {
            const response = await fetch(window.routes.getOccupiedPieces, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    ot: selectOt.value,
                    clase_id: selectClase.value,
                    proceso: selectProceso.value,
                }),
            });

            const data = await response.json();
            if (!data.success) {
                throw new Error(data.message || 'Error al cargar piezas');
            }

            currentPiecesData = data.pieces || [];

            // Actualizar KPIs
            statTotalOccupied.textContent = data.summary.total_occupied;
            statTotalOrphans.textContent = data.summary.total_orphans;
            statAffectedOts.textContent = data.summary.affected_ots;

            // Renderizar tabla
            renderTable(currentPiecesData);

        } catch (error) {
            console.error('Error cargando piezas ocupadas:', error);
            tableBody.innerHTML = `
                <tr>
                    <td colspan="9" class="empty-state text-danger">
                        <img src="${window.images.aviso}" class="empty-state-icon" alt="" />
                        <p>Ocurrió un error al cargar las piezas: ${error.message}</p>
                    </td>
                </tr>
            `;
        } finally {
            tableBody.style.opacity = '1';
        }
    }

    /**
     * Renderiza las filas de la tabla con base en los datos recibidos.
     */
    function renderTable(pieces) {
        const footerCount = document.getElementById('footer-count-text');

        if (!pieces || pieces.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="9" class="empty-state">
                        <img src="${window.images.ready}" class="empty-state-icon" alt="" />
                        <p>No se encontraron piezas ocupadas con los filtros seleccionados.</p>
                    </td>
                </tr>
            `;
            if (footerCount) footerCount.innerHTML = '<strong>Registros encontrados:</strong> 0';
            return;
        }

        if (footerCount && activeTab === 'pieces') {
            footerCount.innerHTML = `<strong>Registros encontrados:</strong> ${pieces.length}`;
        }
        if (tabBadgePieces) {
            tabBadgePieces.textContent = pieces.length;
        }

        let html = '';
        pieces.forEach((piece) => {
            const orphanClass = piece.is_orphan ? 'row-orphan' : '';
            const conditionBadge = piece.is_orphan
                ? `<span class="badge badge-orphan" title="La máquina o meta ya no está activa en el sistema. Es un registro retenido.">
                     <img src="${window.images.aviso}" class="icon-badge" alt="" /> Huérfana (Máquina libre)
                   </span>`
                : `<span class="badge badge-active" title="Máquina actualmente activa en producción">
                     <img src="${window.images.ready}" class="icon-badge" alt="" /> Máquina activa
                   </span>`;

            const metaInfo = piece.id_meta
                ? `<strong>Meta #${piece.id_meta}</strong><br><small class="text-muted">Máq: ${piece.maquina}</small>`
                : `<span class="text-muted">Sin Meta</span>`;

            const opInfo = piece.operador_matricula
                ? `<span>${piece.operador_nombre}</span><br><small class="text-muted">Mat: ${piece.operador_matricula}</small>`
                : `<span class="text-muted">—</span>`;

            html += `
                <tr class="${orphanClass}">
                    <td><span class="badge badge-ot">OT ${piece.id_ot}</span></td>
                    <td><strong>${piece.clase_nombre}</strong></td>
                    <td><span class="badge badge-process">${piece.proceso_label}</span></td>
                    <td>
                        <span class="badge-piece">${piece.n_pieza}</span>
                        ${piece.n_juego && piece.n_juego !== piece.n_pieza ? `<br><small class="text-muted">Juego: ${piece.n_juego}</small>` : ''}
                    </td>
                    <td>${metaInfo}</td>
                    <td>${opInfo}</td>
                    <td><small>${piece.created_at}</small></td>
                    <td>${conditionBadge}</td>
                    <td class="text-center">
                        <div class="table-actions">
                            <button type="button" class="btn-icon-action btn-action-free" 
                                    title="Liberar / Desocupar pieza"
                                    data-proceso="${piece.proceso_key}"
                                    data-id="${piece.id}"
                                    data-piezaname="${piece.n_pieza}"
                                    data-initial="${piece.is_initial}">
                                <img src="${window.images.liberar}" class="icon-action" alt="Liberar" />
                            </button>
                            <button type="button" class="btn-icon-action btn-action-edit" 
                                    title="Modificar datos o estado"
                                    data-proceso="${piece.proceso_key}"
                                    data-id="${piece.id}">
                                <img src="${window.images.editar}" class="icon-action" alt="Modificar" />
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });

        tableBody.innerHTML = html;

        // Registrar eventos en botones de acción y checkboxes de fila
        attachTableEvents();
    }

    /**
     * Vincula los listeners de las filas generadas.
     */
    function attachTableEvents() {
        // Botones de Liberar individual
        document.querySelectorAll('.btn-action-free').forEach((btn) => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const proceso = btn.dataset.proceso;
                const pieceId = btn.dataset.id;
                const piezaName = btn.dataset.piezaname;
                const isInitial = btn.dataset.initial === 'true';

                confirmFreePiece(proceso, pieceId, piezaName, isInitial);
            });
        });

        // Botones de Modificar modal
        document.querySelectorAll('.btn-action-edit').forEach((btn) => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const proceso = btn.dataset.proceso;
                const pieceId = btn.dataset.id;
                openEditModal(proceso, pieceId);
            });
        });
    }

    /**
     * Confirma y ejecuta la liberación de una pieza usando el modal nativo GIS.
     */
    function confirmFreePiece(proceso, pieceId, piezaName, isInitial) {
        const piece = currentPiecesData.find(p => p.proceso_key === proceso && String(p.id) === String(pieceId));
        const initialNote = isInitial 
            ? 'Al ser del proceso inicial (ej. Cepillado), se eliminará el registro huérfano para que el juego vuelva a estar 100% disponible para maquinar.'
            : 'El estado de la pieza volverá a 0 (desocupada) para que pueda ser seleccionada y maquinada.';
        const cleanOt = piece && piece.id_ot ? String(piece.id_ot).replace(/^OT\s*/i, '') : '-';

        openConfirmDialog({
            title: `¿Liberar pieza ${piezaName}?`,
            text: initialNote,
            icon: window.images.liberar,
            confirmBtnText: 'Liberar',
            isDanger: false,
            ot: cleanOt,
            clase: piece ? piece.clase_nombre : '-',
            proceso: piece ? piece.proceso_label : proceso,
            onConfirm: async () => {
                const response = await fetch(window.routes.freeUpPiece, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ proceso, piece_id: pieceId }),
                });

                const data = await response.json();
                if (!data.success) {
                    throw new Error(data.message || 'Error al liberar la pieza');
                }

                // Animación inmediata y sutil en la fila afectada para retroalimentación instantánea
                const targetBtn = document.querySelector(`button.btn-action-free[data-id="${pieceId}"][data-proceso="${proceso}"]`);
                const targetRow = targetBtn ? targetBtn.closest('tr') : null;
                if (targetRow) {
                    targetRow.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
                    targetRow.style.opacity = '0.3';
                    targetRow.style.transform = 'scale(0.99)';
                }

                showToast('success', data.message);
                loadOccupiedPieces(true);
                loadAdminLogs(true);
            }
        });
    }



    /**
     * Abre y prepara el modal de edición de pieza.
     */
    function openEditModal(proceso, pieceId) {
        const piece = currentPiecesData.find(p => p.proceso_key === proceso && String(p.id) === String(pieceId));
        if (!piece) return;

        document.getElementById('edit-proceso').value = piece.proceso_key;
        document.getElementById('edit-piece-id').value = piece.id;
        document.getElementById('edit-n-pieza').value = piece.n_pieza;
        document.getElementById('edit-n-juego').value = piece.n_juego || piece.n_pieza;
        document.getElementById('edit-estado').value = piece.estado;
        document.getElementById('edit-id-meta').value = piece.id_meta || '';

        const badgeOt = document.getElementById('badge-modal-ot');
        const badgeClase = document.getElementById('badge-modal-clase');
        const badgeProceso = document.getElementById('badge-modal-proceso');
        if (badgeOt) badgeOt.textContent = piece.id_ot;
        if (badgeClase) badgeClase.textContent = piece.clase_nombre;
        if (badgeProceso) badgeProceso.textContent = piece.proceso_label;

        editModal.style.display = 'flex';
    }

    function closeEditModal() {
        editModal.style.display = 'none';
        formEditPiece.reset();
    }

    if (btnCloseModal) btnCloseModal.addEventListener('click', closeEditModal);
    if (btnCancelEdit) btnCancelEdit.addEventListener('click', closeEditModal);
    editModal.addEventListener('click', (e) => {
        if (e.target === editModal) closeEditModal();
    });

    // Guardar cambios del modal
    formEditPiece.addEventListener('submit', async (e) => {
        e.preventDefault();

        const proceso = document.getElementById('edit-proceso').value;
        const pieceId = document.getElementById('edit-piece-id').value;
        const nPieza = document.getElementById('edit-n-pieza').value.trim();
        const nJuego = document.getElementById('edit-n-juego').value.trim();
        const estado = document.getElementById('edit-estado').value;
        const idMeta = document.getElementById('edit-id-meta').value;

        const btnSave = document.getElementById('btn-save-edit');
        const origText = btnSave ? btnSave.innerHTML : 'Guardar';
        if (btnSave) {
            btnSave.disabled = true;
            btnSave.textContent = 'Guardando...';
        }

        try {
            const response = await fetch(window.routes.updatePiece, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    proceso,
                    piece_id: pieceId,
                    n_pieza: nPieza,
                    n_juego: nJuego,
                    estado,
                    id_meta: idMeta || null,
                }),
            });

            const data = await response.json();
            if (data.success) {
                closeEditModal();
                setTimeout(() => {
                    showToast('success', data.message);
                    loadOccupiedPieces(true);
                    loadAdminLogs(true);
                }, 100);
            } else {
                throw new Error(data.message || 'Error al guardar cambios');
            }
        } catch (error) {
            showError('Error al actualizar', error.message);
        } finally {
            if (btnSave) {
                btnSave.disabled = false;
                btnSave.innerHTML = origText;
            }
        }
    });

    // Eliminar registro huérfano desde el modal
    btnDeletePiece.addEventListener('click', () => {
        const proceso = document.getElementById('edit-proceso').value;
        const pieceId = document.getElementById('edit-piece-id').value;
        const nPieza = document.getElementById('edit-n-pieza').value;
        const piece = currentPiecesData.find(p => p.proceso_key === proceso && String(p.id) === String(pieceId));

        openConfirmDialog({
            title: `¿Eliminar registro de ${nPieza}?`,
            text: 'Esta acción eliminará físicamente este registro huérfano. Útil si se creó una pieza duplicada o fantasma.',
            icon: window.images.aviso || window.images.delete,
            confirmBtnText: 'Eliminar',
            isDanger: true,
            ot: piece && piece.id_ot ? String(piece.id_ot).replace(/^OT\s*/i, '') : '-',
            clase: piece ? piece.clase_nombre : '-',
            proceso: piece ? piece.proceso_label : proceso,
            onConfirm: async () => {
                const response = await fetch(window.routes.destroyPiece, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ proceso, piece_id: pieceId }),
                });

                const data = await response.json();
                if (!data.success) {
                    throw new Error(data.message || 'Error al eliminar');
                }

                closeEditModal();
                showToast('success', data.message || 'Registro eliminado');
                loadOccupiedPieces(true);
                loadAdminLogs(true);
            }
        });
    });

    // Cerrar modales con tecla Escape para máxima agilidad
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            if (confirmModal && confirmModal.style.display === 'flex') {
                closeConfirmDialog();
            } else if (editModal && editModal.style.display === 'flex') {
                closeEditModal();
            }
        }
    });

    // Pestañas: Alternar entre Piezas Ocupadas y Auditoría
    if (tabBtnPieces && tabBtnLogs) {
        tabBtnPieces.addEventListener('click', () => {
            activeTab = 'pieces';
            tabBtnPieces.classList.add('active');
            tabBtnLogs.classList.remove('active');
            if (containerPiecesTable) containerPiecesTable.style.display = 'block';
            if (containerLogsTable) containerLogsTable.style.display = 'none';
            if (footerCount) {
                footerCount.innerHTML = `<strong>Registros encontrados:</strong> ${currentPiecesData.length}`;
            }
            if (footerLegendNote) {
                footerLegendNote.innerHTML = 'Nota: Las filas sombreadas en tono rojizo corresponden a piezas <strong>huérfanas</strong> (su máquina o meta ya no está activa).';
            }
        });

        tabBtnLogs.addEventListener('click', () => {
            activeTab = 'logs';
            tabBtnLogs.classList.add('active');
            tabBtnPieces.classList.remove('active');
            if (containerPiecesTable) containerPiecesTable.style.display = 'none';
            if (containerLogsTable) containerLogsTable.style.display = 'block';
            if (footerLegendNote) {
                footerLegendNote.innerHTML = 'Historial de auditoría: Muestra todas las liberaciones, modificaciones y eliminaciones ejecutadas en este módulo.';
            }
            loadAdminLogs();
        });
    }

    /**
     * Carga y renderiza los logs de auditoría administrativa.
     */
    async function loadAdminLogs(isSilent = false) {
        if (!logsTableBody) return;

        if (!isSilent) {
            logsTableBody.innerHTML = `
                <tr>
                    <td colspan="8" class="empty-state">
                        <img src="${window.images.loading}" class="loading-gif" alt="Cargando..." />
                        <p>Cargando historial de auditoría administrativa...</p>
                    </td>
                </tr>
            `;
        }

        try {
            const response = await fetch(window.routes.getLogs, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    ot: selectOt ? selectOt.value : '',
                    proceso: selectProceso ? selectProceso.value : '',
                }),
            });

            const data = await response.json();
            if (!data.success) {
                throw new Error(data.message || 'Error al obtener auditoría');
            }

            currentLogsData = data.logs || [];
            if (tabBadgeLogs) tabBadgeLogs.textContent = currentLogsData.length;

            if (activeTab === 'logs' && footerCount) {
                footerCount.innerHTML = `<strong>Registros de auditoría:</strong> ${currentLogsData.length}`;
            }

            renderLogsTable(currentLogsData);
        } catch (error) {
            console.error('Error cargando logs de auditoría:', error);
            if (logsTableBody) {
                logsTableBody.innerHTML = `
                    <tr>
                        <td colspan="8" class="empty-state text-danger">
                            <img src="${window.images.aviso}" class="empty-state-icon" alt="" />
                            <p>Error al cargar historial: ${error.message}</p>
                        </td>
                    </tr>
                `;
            }
        }
    }

    /**
     * Renderiza las filas de la tabla de auditoría.
     */
    function renderLogsTable(logs) {
        if (!logsTableBody) return;

        if (!logs || logs.length === 0) {
            logsTableBody.innerHTML = `
                <tr>
                    <td colspan="8" class="empty-state">
                        <img src="${window.images.ready}" class="empty-state-icon" alt="" />
                        <p>No hay acciones administrativas registradas con los filtros seleccionados.</p>
                    </td>
                </tr>
            `;
            return;
        }

        let html = '';
        logs.forEach((log) => {
            let badgeClass = 'badge-action-free';
            let actionIcon = window.images.liberar;

            if (log.action.includes('Modificación')) {
                badgeClass = 'badge-action-edit';
                actionIcon = window.images.editar;
            } else if (log.action.includes('Eliminación')) {
                badgeClass = 'badge-action-delete';
                actionIcon = window.images.delete || window.images.aviso;
            } else if (log.action.includes('Masiva')) {
                badgeClass = 'badge-action-batch';
                actionIcon = window.images.liberar;
            }

            const otBadge = log.ot && log.ot !== '—'
                ? `<span class="badge badge-ot">OT ${log.ot}</span>`
                : `<span class="text-muted">—</span>`;

            const claseVal = log.clase && log.clase !== '—'
                ? `<strong>${log.clase}</strong>`
                : `<span class="text-muted">—</span>`;

            const procesoBadge = log.proceso && log.proceso !== '—'
                ? `<span class="badge badge-process">${log.proceso}</span>`
                : `<span class="text-muted">—</span>`;

            const piezaBadge = log.n_pieza && log.n_pieza !== '—'
                ? `<span class="badge-piece">${log.n_pieza}</span>`
                : `<span class="text-muted">—</span>`;

            html += `
                <tr>
                    <td>
                        <strong>${log.created_at}</strong>
                        ${log.time_ago ? `<br><small class="text-muted">${log.time_ago}</small>` : ''}
                    </td>
                    <td>
                        <span class="badge-admin-user">${log.user_nombre}</span>
                        ${log.user_matricula && log.user_matricula !== '—' ? `<br><small class="text-muted">Mat: ${log.user_matricula}</small>` : ''}
                    </td>
                    <td>
                        <span class="badge ${badgeClass}">
                            <img src="${actionIcon}" class="icon-badge" alt="" /> ${log.action}
                        </span>
                    </td>
                    <td>${otBadge}</td>
                    <td>${claseVal}</td>
                    <td>${procesoBadge}</td>
                    <td>${piezaBadge}</td>
                    <td><div class="log-details-text">${log.details}</div></td>
                </tr>
            `;
        });

        logsTableBody.innerHTML = html;
    }

    // Cambio de OT -> Actualizar dropdown de Clases y recargar según pestaña activa
    selectOt.addEventListener('change', async () => {
        const otId = selectOt.value;
        selectClase.innerHTML = '<option value="">Todas las Clases</option>';

        if (otId) {
            try {
                const res = await fetch(`${window.routes.getClasses}?ot=${otId}`);
                const data = await res.json();
                if (data.classes && data.classes.length > 0) {
                    data.classes.forEach(c => {
                        const opt = document.createElement('option');
                        opt.value = c.id;
                        opt.textContent = `${c.nombre} (${c.piezas || 0} pzas)`;
                        selectClase.appendChild(opt);
                    });
                }
            } catch (err) {
                console.error('Error al cargar clases:', err);
            }
        }

        if (activeTab === 'pieces') {
            loadOccupiedPieces();
            loadAdminLogs(true);
        } else {
            loadAdminLogs();
        }
    });

    // Filtros de Clase y Proceso
    selectClase.addEventListener('change', () => {
        if (activeTab === 'pieces') loadOccupiedPieces();
    });

    selectProceso.addEventListener('change', () => {
        if (activeTab === 'pieces') {
            loadOccupiedPieces();
            loadAdminLogs(true);
        } else {
            loadAdminLogs();
        }
    });

    btnRefresh.addEventListener('click', () => {
        if (activeTab === 'pieces') {
            loadOccupiedPieces();
            loadAdminLogs(true);
        } else {
            loadAdminLogs();
            loadOccupiedPieces(true);
        }
    });

    // Carga inicial
    loadOccupiedPieces();
    loadAdminLogs(true);
});
