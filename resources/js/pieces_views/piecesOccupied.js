document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    // Elementos del DOM
    const selectOt = document.getElementById('select-ot');
    const selectClase = document.getElementById('select-clase');
    const selectProceso = document.getElementById('select-proceso');
    const selectPieza = document.getElementById('select-pieza');
    const selectOperador = document.getElementById('select-operador');
    const btnRefresh = document.getElementById('btn-refresh');
    const btnClearFilters = document.getElementById('btn-clear-filters');
    const tableBody = document.getElementById('pieces-table-body');

    // KPI Counters
    const statTotalOccupied = document.getElementById('stat-total-occupied');
    const statTotalOrphans = document.getElementById('stat-total-orphans');
    const statTotalIncompletes = document.getElementById('stat-total-incompletes');
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
    const paginationContainer = document.getElementById('pieces-pagination-container');
    const colorsLegendContainer = document.getElementById('colors-legend-container');
    const colorsToggleBtn = document.getElementById('colors-toggle-btn');

    let activeTab = 'pieces';
    let currentLogsData = [];
    let pendingConfirmAction = null;
    let currentPiecesData = [];

    // Estado de Paginación Unificada GIS
    let currentPage = 1;
    let pageSize = 50;

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
     * Actualiza el estado visual del botón Limpiar Filtros:
     * Gris desactivado si no hay filtros activos; Azul GIS si hay al menos un filtro.
     */
    function updateClearButtonState() {
        if (!btnClearFilters) return;
        const hasFilters = Boolean(
            (selectOt && selectOt.value) ||
            (selectClase && selectClase.value) ||
            (selectProceso && selectProceso.value) ||
            (selectPieza && selectPieza.value) ||
            (selectOperador && selectOperador.value)
        );
        btnClearFilters.disabled = !hasFilters;
    }

    /**
     * Sincroniza el estado del filtro de Número de Pieza:
     * Solo se activa si los 3 primeros filtros (OT, Clase, Proceso) están seleccionados.
     * Carga las piezas específicas de ese contexto englobando macho y hembra en un solo número.
     */
    async function syncPieceFilterState() {
        if (!selectPieza) return;

        const ot = selectOt ? selectOt.value : '';
        const clase = selectClase ? selectClase.value : '';
        const proceso = selectProceso ? selectProceso.value : '';

        const allThreeSelected = Boolean(ot && clase && proceso);

        if (!allThreeSelected) {
            selectPieza.disabled = true;
            selectPieza.innerHTML = '<option value="">Todas las Piezas</option>';
            selectPieza.value = '';
            selectPieza.title = 'Selecciona primero OT, Clase y Proceso para activar este filtro';
            return;
        }

        // Si los 3 están seleccionados, habilitar y cargar opciones
        selectPieza.disabled = true;
        selectPieza.innerHTML = '<option value="">Cargando piezas...</option>';
        selectPieza.title = 'Selecciona un número de pieza';

        try {
            const res = await fetch(window.routes.getPiecesByContext, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    ot,
                    clase_id: clase,
                    proceso,
                }),
            });

            const data = await res.json();
            const pieces = data.pieces || [];

            selectPieza.innerHTML = '<option value="">Todas las Piezas</option>';

            if (pieces.length > 0) {
                pieces.forEach(p => {
                    const opt = document.createElement('option');
                    opt.value = p.id;
                    opt.textContent = p.label;
                    selectPieza.appendChild(opt);
                });
            } else {
                const optNone = document.createElement('option');
                optNone.value = '';
                optNone.textContent = 'Sin piezas registradas';
                selectPieza.appendChild(optNone);
            }
            selectPieza.disabled = false;
        } catch (err) {
            console.error('Error al cargar piezas por contexto:', err);
            selectPieza.innerHTML = '<option value="">Todas las Piezas</option>';
            selectPieza.disabled = false;
        }
    }

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
        if (btnRefresh) btnRefresh.disabled = true;
        updateClearButtonState();

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
                    ot: selectOt ? selectOt.value : '',
                    clase_id: selectClase ? selectClase.value : '',
                    proceso: selectProceso ? selectProceso.value : '',
                    pieza: selectPieza ? selectPieza.value : '',
                    operador: selectOperador ? selectOperador.value : '',
                }),
            });

            const data = await response.json();
            if (!data.success) {
                throw new Error(data.message || 'Error al cargar piezas');
            }

            currentPiecesData = data.pieces || [];

            // Actualizar KPIs
            if (statTotalOccupied) statTotalOccupied.textContent = data.summary?.total_occupied ?? 0;
            if (statTotalOrphans) statTotalOrphans.textContent = data.summary?.total_orphans ?? 0;
            if (statTotalIncompletes) statTotalIncompletes.textContent = data.summary?.total_incompletes ?? 0;
            if (statAffectedOts) statAffectedOts.textContent = data.summary?.affected_ots ?? 0;

            // Reiniciar a la primera página ante nueva consulta
            currentPage = 1;

            // Renderizar tabla con paginación
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
            if (paginationContainer) paginationContainer.innerHTML = '';
        } finally {
            tableBody.style.opacity = '1';
            if (btnRefresh) btnRefresh.disabled = false;
        }
    }

    /**
     * Renderiza las filas de la tabla con paginación integrada.
     */
    function renderTable(pieces) {
        if (!pieces || pieces.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="9" class="empty-state">
                        <img src="${window.images.ready}" class="empty-state-icon" alt="" />
                        <p>No se encontraron piezas ocupadas, desvinculadas ni incompletas con los filtros seleccionados.</p>
                    </td>
                </tr>
            `;
            if (footerCount) footerCount.innerHTML = '<strong>Registros encontrados:</strong> 0';
            if (tabBadgePieces) tabBadgePieces.textContent = '0';
            if (paginationContainer) {
                paginationContainer.style.display = 'none';
                paginationContainer.innerHTML = '';
            }
            return;
        }

        const totalRecords = pieces.length;
        const totalPages = Math.max(1, Math.ceil(totalRecords / pageSize));
        if (currentPage > totalPages) currentPage = totalPages;
        if (currentPage < 1) currentPage = 1;

        const startIdx = (currentPage - 1) * pageSize;
        const endIdx = Math.min(startIdx + pageSize, totalRecords);
        const pageItems = pieces.slice(startIdx, endIdx);

        if (footerCount && activeTab === 'pieces') {
            footerCount.innerHTML = `<strong>Registros encontrados:</strong> ${totalRecords}`;
        }
        if (tabBadgePieces) {
            tabBadgePieces.textContent = totalRecords;
        }

        let html = '';
        pageItems.forEach((piece) => {
            const rowClass = piece.is_incomplete ? 'row-incomplete' : (piece.is_orphan ? 'row-orphan' : 'row-active');
            
            let conditionBadge = '';
            if (piece.is_incomplete) {
                conditionBadge = `
                    <span class="badge badge-incomplete" title="Juego incompleto: solo una mitad (H o M) fue registrada. Pasa el cursor para ver el icono a color.">
                        <span class="badge-icon-swap">
                            <img src="${window.images.incompleto_shadow}" class="badge-icon-img icon-shadow" alt="" />
                            <img src="${window.images.incompleto_color}" class="badge-icon-img icon-color" alt="" />
                        </span>
                        <span class="badge-text">Juego Incompleto (Mitad faltante)</span>
                    </span>`;
            } else if (piece.is_orphan) {
                conditionBadge = `
                    <span class="badge badge-orphan" title="La máquina o meta ya no está activa en el sistema. Pasa el cursor para ver el icono a color.">
                        <span class="badge-icon-swap">
                            <img src="${window.images.desvincular_shadow}" class="badge-icon-img icon-shadow" alt="" />
                            <img src="${window.images.desvincular_color}" class="badge-icon-img icon-color" alt="" />
                        </span>
                        <span class="badge-text">Desvinculada (Máquina libre)</span>
                    </span>`;
            } else {
                conditionBadge = `
                    <span class="badge badge-active" title="Máquina actualmente activa en producción">
                        <span class="badge-icon-swap">
                            <img src="${window.images.ready_shadow || window.images.ready}" class="badge-icon-img icon-shadow" alt="" />
                            <img src="${window.images.ready}" class="badge-icon-img icon-color" alt="" />
                        </span>
                        <span class="badge-text">Máquina activa</span>
                    </span>`;
            }

            const metaInfo = piece.id_meta
                ? `<strong>Meta #${piece.id_meta}</strong><br><small class="text-muted">Máq: ${piece.maquina}</small>`
                : `<span class="text-muted">Sin Meta</span>`;

            const opInfo = piece.operador_matricula
                ? `<span>${piece.operador_nombre}</span><br><small class="text-muted">Mat: ${piece.operador_matricula}</small>`
                : `<span class="text-muted">—</span>`;

            const isBlocked = !piece.is_incomplete && !piece.is_orphan;
            let actionsHtml = '';

            if (isBlocked) {
                actionsHtml = `
                    <div class="table-actions">
                        <button type="button" class="btn-action-col btn-action-free btn-action-disabled" 
                                disabled
                                title="Acción bloqueada: Esta pieza está actualmente en proceso de maquinado activo"
                                tabindex="-1">
                            <div class="action-icon-swap">
                                <img src="${window.images.bloqueado_light}" class="action-icon-img icon-static" alt="" />
                            </div>
                            <span class="btn-action-txt">Liberar</span>
                        </button>
                        <button type="button" class="btn-action-col btn-action-edit btn-action-disabled" 
                                disabled
                                title="Acción bloqueada: Esta pieza está actualmente en proceso de maquinado activo"
                                tabindex="-1">
                            <div class="action-icon-swap">
                                <img src="${window.images.editar_light}" class="action-icon-img icon-static" alt="" />
                            </div>
                            <span class="btn-action-txt">Editar</span>
                        </button>
                    </div>
                `;
            } else {
                actionsHtml = `
                    <div class="table-actions">
                        <button type="button" class="btn-action-col btn-action-free" 
                                title="Liberar pieza: Desocupar registro retenido para permitir su maquinado"
                                data-proceso="${piece.proceso_key}"
                                data-id="${piece.id}"
                                data-piezaname="${piece.n_pieza}"
                                data-initial="${piece.is_initial}"
                                data-incomplete="${piece.is_incomplete ? 'true' : 'false'}">
                            <div class="action-icon-swap">
                                <img src="${window.images.bloqueado_light}" class="action-icon-img icon-static" alt="" />
                                <img src="${window.images.desbloqueado_color}" class="action-icon-img icon-hover" alt="" />
                            </div>
                            <span class="btn-action-txt">Liberar</span>
                        </button>
                        <button type="button" class="btn-action-col btn-action-edit" 
                                title="Editar pieza: Modificar datos o máquina de la pieza"
                                data-proceso="${piece.proceso_key}"
                                data-id="${piece.id}">
                            <div class="action-icon-swap">
                                <img src="${window.images.editar_light}" class="action-icon-img icon-static" alt="" />
                                <img src="${window.images.editar_color}" class="action-icon-img icon-hover" alt="" />
                            </div>
                            <span class="btn-action-txt">Editar</span>
                        </button>
                    </div>
                `;
            }

            html += `
                <tr class="${rowClass}">
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
                        ${actionsHtml}
                    </td>
                </tr>
            `;
        });

        tableBody.innerHTML = html;

        // Renderizar barra de paginación idéntica a Reporte de Piezas
        updatePaginationUI(totalRecords, totalPages, startIdx, endIdx);

        // Registrar eventos en botones de acción
        attachTableEvents();
    }

    /**
     * Renderiza la barra de paginación unificada GIS.
     */
    function updatePaginationUI(totalCount, totalPages, startIdx, endIdx) {
        if (!paginationContainer) return;

        if (activeTab !== 'pieces' || totalCount === 0) {
            paginationContainer.style.display = 'none';
            paginationContainer.innerHTML = '';
            return;
        }

        paginationContainer.style.display = 'flex';
        paginationContainer.innerHTML = `
            <nav class="gis-unified-pagination" aria-label="Navegación de piezas ocupadas">
                <div class="gis-pagination-left">
                    <span class="gis-pagination-stat-badge">
                        <span class="gis-pagination-stat-dot"></span>
                        <span>Total: <strong>${totalCount.toLocaleString()}</strong></span>
                    </span>

                    <div class="gis-pagination-select-wrap">
                        <label for="pieces-page-size-select">Filas:</label>
                        <select id="pieces-page-size-select" class="gis-pagination-select" aria-label="Cantidad de filas por página">
                            <option value="50" ${pageSize === 50 ? 'selected' : ''}>50</option>
                            <option value="100" ${pageSize === 100 ? 'selected' : ''}>100</option>
                            <option value="250" ${pageSize === 250 ? 'selected' : ''}>250</option>
                            <option value="500" ${pageSize === 500 ? 'selected' : ''}>500</option>
                        </select>
                    </div>
                </div>

                <div class="gis-pagination-center">
                    <span class="gis-pagination-range-pill">
                        ${totalCount === 0
                            ? 'Sin piezas registradas con estos filtros'
                            : `Mostrando <strong>${(startIdx + 1).toLocaleString()} - ${endIdx.toLocaleString()}</strong> de <strong>${totalCount.toLocaleString()}</strong> piezas &nbsp;•&nbsp; Pág. <strong>${currentPage}</strong> de <strong>${totalPages}</strong>`}
                    </span>
                </div>

                <div class="gis-pagination-right">
                    <button type="button"
                            class="gis-pagination-btn-nav gis-pagination-btn-icon-only ${currentPage <= 1 ? 'disabled-btn' : 'active-btn'}"
                            id="pieces-btn-first"
                            title="Primera página"
                            ${currentPage <= 1 ? 'disabled' : ''}>
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="11 17 6 12 11 7"></polyline>
                            <polyline points="18 17 13 12 18 7"></polyline>
                        </svg>
                    </button>

                    <button type="button"
                            class="gis-pagination-btn-nav ${currentPage <= 1 ? 'disabled-btn' : 'active-btn'}"
                            id="pieces-btn-prev"
                            title="Página anterior"
                            ${currentPage <= 1 ? 'disabled' : ''}>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                        <span>Anterior</span>
                    </button>

                    <button type="button"
                            class="gis-pagination-btn-nav ${currentPage >= totalPages ? 'disabled-btn' : 'active-btn'}"
                            id="pieces-btn-next"
                            title="Página siguiente"
                            ${currentPage >= totalPages ? 'disabled' : ''}>
                        <span>Siguiente</span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </button>

                    <button type="button"
                            class="gis-pagination-btn-nav gis-pagination-btn-icon-only ${currentPage >= totalPages ? 'disabled-btn' : 'active-btn'}"
                            id="pieces-btn-last"
                            title="Última página"
                            ${currentPage >= totalPages ? 'disabled' : ''}>
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="13 17 18 12 13 7"></polyline>
                            <polyline points="6 17 11 12 6 7"></polyline>
                        </svg>
                    </button>
                </div>
            </nav>
        `;

        const scrollTable = () => {
            if (containerPiecesTable) {
                containerPiecesTable.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        };

        const pageSizeSelect = document.getElementById('pieces-page-size-select');
        if (pageSizeSelect) {
            pageSizeSelect.addEventListener('change', (e) => {
                pageSize = parseInt(e.target.value, 10) || 50;
                currentPage = 1;
                renderTable(currentPiecesData);
                scrollTable();
            });
        }

        document.getElementById('pieces-btn-first')?.addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage = 1;
                renderTable(currentPiecesData);
                scrollTable();
            }
        });

        document.getElementById('pieces-btn-prev')?.addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                renderTable(currentPiecesData);
                scrollTable();
            }
        });

        document.getElementById('pieces-btn-next')?.addEventListener('click', () => {
            if (currentPage < totalPages) {
                currentPage++;
                renderTable(currentPiecesData);
                scrollTable();
            }
        });

        document.getElementById('pieces-btn-last')?.addEventListener('click', () => {
            if (currentPage < totalPages) {
                currentPage = totalPages;
                renderTable(currentPiecesData);
                scrollTable();
            }
        });
    }

    /**
     * Vincula los listeners de las filas generadas.
     */
    function attachTableEvents() {
        // Botones de Liberar individual
        document.querySelectorAll('.btn-action-free').forEach((btn) => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                if (btn.disabled) return;
                const proceso = btn.dataset.proceso;
                const pieceId = btn.dataset.id;
                const piezaName = btn.dataset.piezaname;
                const isInitial = btn.dataset.initial === 'true';
                const isIncomplete = btn.dataset.incomplete === 'true';

                confirmFreePiece(proceso, pieceId, piezaName, isInitial, isIncomplete);
            });
        });

        // Botones de Modificar modal
        document.querySelectorAll('.btn-action-edit').forEach((btn) => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                if (btn.disabled) return;
                const proceso = btn.dataset.proceso;
                const pieceId = btn.dataset.id;
                openEditModal(proceso, pieceId);
            });
        });
    }

    /**
     * Confirma y ejecuta la liberación de una pieza usando el modal nativo GIS.
     */
    function confirmFreePiece(proceso, pieceId, piezaName, isInitial, isIncomplete) {
        const piece = currentPiecesData.find(p => p.proceso_key === proceso && String(p.id) === String(pieceId));
        
        let note = '';
        if (isIncomplete) {
            note = 'Esta pieza corresponde a un juego incompleto (mitad registrada sin par). Al liberarla, se eliminará el registro residual para desbloquear el juego y permitir su maquinado completo.';
        } else if (isInitial) {
            note = 'Al ser del proceso inicial (ej. Cepillado), se eliminará el registro retenido o desvinculado para que el juego vuelva a estar 100% disponible para maquinar.';
        } else {
            note = 'El estado de la pieza volverá a 0 (desocupada) para que pueda ser seleccionada y maquinada nuevamente.';
        }
        
        const cleanOt = piece && piece.id_ot ? String(piece.id_ot).replace(/^OT\s*/i, '') : '-';

        openConfirmDialog({
            title: `¿Liberar pieza ${piezaName}?`,
            text: note,
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
    if (editModal) {
        editModal.addEventListener('click', (e) => {
            if (e.target === editModal) closeEditModal();
        });
    }

    // Guardar cambios del modal
    if (formEditPiece) {
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
    }

    // Eliminar registro huérfano o residual desde el modal
    if (btnDeletePiece) {
        btnDeletePiece.addEventListener('click', () => {
            const proceso = document.getElementById('edit-proceso').value;
            const pieceId = document.getElementById('edit-piece-id').value;
            const nPieza = document.getElementById('edit-n-pieza').value;
            const piece = currentPiecesData.find(p => p.proceso_key === proceso && String(p.id) === String(pieceId));

            openConfirmDialog({
                title: `¿Eliminar registro de ${nPieza}?`,
                text: 'Esta acción eliminará físicamente este registro desvinculado o incompleto. Útil si se creó una pieza duplicada o un registro residual.',
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
    }

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

    // Drawer de Código de Colores
    if (colorsToggleBtn && colorsLegendContainer) {
        colorsToggleBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            colorsLegendContainer.classList.toggle('active');
        });

        document.addEventListener('click', (e) => {
            if (!colorsLegendContainer.contains(e.target)) {
                colorsLegendContainer.classList.remove('active');
            }
        });
    }

    // Pestañas: Alternar entre Piezas Ocupadas y Auditoría
    if (tabBtnPieces && tabBtnLogs) {
        tabBtnPieces.addEventListener('click', () => {
            activeTab = 'pieces';
            tabBtnPieces.classList.add('active');
            tabBtnLogs.classList.remove('active');
            if (containerPiecesTable) containerPiecesTable.style.display = 'block';
            if (containerLogsTable) containerLogsTable.style.display = 'none';
            if (paginationContainer) paginationContainer.style.display = 'flex';
            if (colorsLegendContainer) colorsLegendContainer.style.display = 'flex';
            if (footerCount) {
                footerCount.innerHTML = `<strong>Registros encontrados:</strong> ${currentPiecesData.length}`;
            }
        });

        tabBtnLogs.addEventListener('click', () => {
            activeTab = 'logs';
            tabBtnLogs.classList.add('active');
            tabBtnPieces.classList.remove('active');
            if (containerPiecesTable) containerPiecesTable.style.display = 'none';
            if (containerLogsTable) containerLogsTable.style.display = 'block';
            if (paginationContainer) paginationContainer.style.display = 'none';
            if (colorsLegendContainer) {
                colorsLegendContainer.classList.remove('active');
                colorsLegendContainer.style.display = 'none';
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
                    pieza: selectPieza ? selectPieza.value : '',
                    operador: selectOperador ? selectOperador.value : '',
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

    // Cambio de OT -> Actualizar dropdown de Clases, sincronizar piezas y recargar
    if (selectOt) {
        selectOt.addEventListener('change', async () => {
            const otId = selectOt.value;
            if (selectClase) selectClase.innerHTML = '<option value="">Todas las Clases</option>';

            try {
                const url = otId ? `${window.routes.getClasses}?ot=${encodeURIComponent(otId)}` : window.routes.getClasses;
                const res = await fetch(url);
                const data = await res.json();
                if (data.classes && data.classes.length > 0 && selectClase) {
                    data.classes.forEach(c => {
                        const opt = document.createElement('option');
                        opt.value = c.id;
                        opt.textContent = c.piezas !== undefined ? `${c.nombre} (${c.piezas} pzas)` : c.nombre;
                        selectClase.appendChild(opt);
                    });
                }
            } catch (err) {
                console.error('Error al cargar clases:', err);
            }

            await syncPieceFilterState();
            updateClearButtonState();
            currentPage = 1;

            if (activeTab === 'pieces') {
                loadOccupiedPieces();
                loadAdminLogs(true);
            } else {
                loadAdminLogs();
            }
        });
    }

    // Filtros de Clase y Proceso -> Sincronizan el filtro dependiente de Número de Pieza
    if (selectClase) {
        selectClase.addEventListener('change', async () => {
            await syncPieceFilterState();
            updateClearButtonState();
            currentPage = 1;
            if (activeTab === 'pieces') loadOccupiedPieces();
        });
    }

    if (selectProceso) {
        selectProceso.addEventListener('change', async () => {
            await syncPieceFilterState();
            updateClearButtonState();
            currentPage = 1;
            if (activeTab === 'pieces') {
                loadOccupiedPieces();
                loadAdminLogs(true);
            } else {
                loadAdminLogs();
            }
        });
    }

    // Filtro de Número de Pieza (activo solo con los 3 primeros filtros seleccionados)
    if (selectPieza) {
        selectPieza.addEventListener('change', () => {
            updateClearButtonState();
            currentPage = 1;
            if (activeTab === 'pieces') {
                loadOccupiedPieces();
                loadAdminLogs(true);
            } else {
                loadAdminLogs();
            }
        });
    }

    // Filtro de Operador
    if (selectOperador) {
        selectOperador.addEventListener('change', () => {
            updateClearButtonState();
            currentPage = 1;
            if (activeTab === 'pieces') {
                loadOccupiedPieces();
                loadAdminLogs(true);
            } else {
                loadAdminLogs();
            }
        });
    }

    // Botón Limpiar Filtros
    if (btnClearFilters) {
        btnClearFilters.addEventListener('click', async () => {
            if (selectOt) selectOt.value = '';
            if (selectClase) selectClase.value = '';
            if (selectProceso) selectProceso.value = '';
            if (selectPieza) selectPieza.value = '';
            if (selectOperador) selectOperador.value = '';

            await syncPieceFilterState();

            // Restaurar catálogo general de clases en el dropdown
            if (selectClase && window.routes?.getClasses) {
                selectClase.innerHTML = '<option value="">Todas las Clases</option>';
                try {
                    const res = await fetch(window.routes.getClasses);
                    const data = await res.json();
                    if (data.classes && data.classes.length > 0) {
                        data.classes.forEach(c => {
                            const opt = document.createElement('option');
                            opt.value = c.id;
                            opt.textContent = c.piezas !== undefined ? `${c.nombre} (${c.piezas} pzas)` : c.nombre;
                            selectClase.appendChild(opt);
                        });
                    }
                } catch (err) {
                    console.error('Error al restaurar clases:', err);
                }
            }

            updateClearButtonState();
            currentPage = 1;

            if (activeTab === 'pieces') {
                loadOccupiedPieces();
                loadAdminLogs(true);
            } else {
                loadAdminLogs();
            }
        });
    }

    if (btnRefresh) {
        btnRefresh.addEventListener('click', () => {
            if (activeTab === 'pieces') {
                loadOccupiedPieces();
                loadAdminLogs(true);
            } else {
                loadAdminLogs();
                loadOccupiedPieces(true);
            }
        });
    }

    // Inicialización de estado y primera carga
    syncPieceFilterState();
    updateClearButtonState();
    loadOccupiedPieces();
    loadAdminLogs(true);
});
