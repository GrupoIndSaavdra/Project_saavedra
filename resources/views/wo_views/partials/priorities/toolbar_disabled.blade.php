<!-- Barra de Filtros y Herramientas Prioridades Desactivadas (Modo Una Sola Línea — Oculta al imprimir) -->
<div class="toolbar no-print">
    <form method="GET" action="{{ route('master.priorities.disabled') }}" class="filters-form">
        <div class="filter-group">
            <label for="start_week">De Semana:</label>
            <select id="start_week" name="start_week" onchange="this.form.submit()">
                <option value="">Cualquiera</option>
                @for ($i = 1; $i <= 52; $i++)
                    <option value="{{ $i }}" {{ $startWeek == $i ? 'selected' : '' }}>Semana {{ $i }}</option>
                @endfor
            </select>
        </div>

        <div class="filter-group">
            <label for="end_week">A Semana:</label>
            <select id="end_week" name="end_week" onchange="this.form.submit()">
                <option value="">Cualquiera</option>
                @for ($i = 1; $i <= 52; $i++)
                    <option value="{{ $i }}" {{ $endWeek == $i ? 'selected' : '' }}>Semana {{ $i }}</option>
                @endfor
            </select>
        </div>

        <div class="filter-group">
            <label for="ot_id">Por OT:</label>
            <select id="ot_id" name="ot_id" onchange="this.form.submit()">
                <option value="">Todas las OTs Desactivadas</option>
                @foreach ($allOts as $ot)
                    <option value="{{ $ot->id }}" {{ $otId == $ot->id ? 'selected' : '' }}>
                        OT {{ $ot->id }} - {{ $ot->cliente ?? 'Sin Cliente' }} ({{ $ot->moldura->nombre ?? 'Sin Moldura' }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="filter-actions">
            <a href="{{ route('master.priorities.disabled') }}" class="btn-clear">General / Limpiar</a>
        </div>
    </form>

    <div class="export-actions">
        @if ($isMaster)
            <div class="pm-debug-status-badge no-print" title="Modo depuración: reactivar o eliminar OTs inactivas">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18.36 6.64a9 9 0 1 1-12.73 0"></path>
                    <line x1="12" y1="2" x2="12" y2="12"></line>
                </svg>
                <span>Modo Depuración y Autoguardado Activo</span>
            </div>
        @endif

        <a href="{{ route('master.priorities') }}" class="btn-switch-view" title="Regresar a Prioridades Activas">
            <img src="{{ asset('images/Color_Icons/Generales/activar.png') }}" class="btn-switch-icon" alt="">
            <span>Prioridades Activas</span>
        </a>

        {{-- Menú de Acciones (Solo Reactivar o Eliminar por completo) --}}
        <div class="actions-dropdown-container" id="actionsDropdownContainer">
            <button type="button" class="btn-actions-trigger" id="btnActionsTrigger" aria-expanded="false" onclick="toggleActionsDropdown(event)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="3"></circle>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                </svg>
                <span>Acciones</span>
                <svg class="dropdown-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </button>

            <div class="actions-dropdown-menu" id="actionsDropdownMenu">
                <div class="actions-menu-header">
                    <span>Gestión de Inactivas</span>
                </div>
                @if ($isMaster)
                    <button type="button" class="actions-menu-item item-success" id="btnStartReactivateMode" onclick="startReactivateMode()">
                        <div class="action-icon-wrapper">
                            <img src="{{ asset('images/Color_Icons/Generales/activar.png') }}" class="action-icon-main" alt="Reactivar OTs">
                        </div>
                        <div class="action-text">
                            <strong>Reactivar OTs</strong>
                            <small>Volver a activar en todas las vistas</small>
                        </div>
                    </button>
                    <button type="button" class="actions-menu-item item-delete-permanent" id="btnStartDeleteMode" onclick="startDeleteMode()">
                        <div class="action-icon-wrapper">
                            <img src="{{ asset('images/Color_Icons/Generales/delete.png') }}" class="action-icon-main" alt="Eliminar y respaldar OTs">
                        </div>
                        <div class="action-text">
                            <strong style="color: #fca5a5;">Eliminar y Respaldar OTs</strong>
                            <small style="color: #f87171;">Ocultar y generar 4 reportes en storage</small>
                        </div>
                    </button>
                @endif
            </div>
        </div>

        {{-- Controles durante el Modo Selección Múltiple (Reactivación / Eliminación) --}}
        <div class="selection-mode-controls" id="selectionModeControls" style="display: none;">
            <div class="selection-counter-badge" id="selectionBadge">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2.5">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                <span id="selectedCountText">0 seleccionadas</span>
            </div>
            <button type="button" class="btn-cancel-selection" id="btnCancelSelection" onclick="exitSelectionMode()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2.5">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
                Cancelar
            </button>
            <button type="button" class="btn-confirm-selection btn-confirm-reactivate" id="btnConfirmReactivate" style="display: none;" disabled>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2.5">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                Confirmar
            </button>
            <button type="button" class="btn-confirm-selection btn-confirm-delete-permanent" id="btnConfirmDelete" style="display: none;" disabled>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2.5">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                Confirmar
            </button>
        </div>
    </div>
</div>

