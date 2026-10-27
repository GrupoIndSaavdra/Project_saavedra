@extends('layouts.appMenu')

@section('head')
    <title>Dashboard de Prioridades Desactivadas</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('images/logos/favicon.ico') }}?v=3">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @vite(['resources/css/wo_views/priorities.css', 'resources/js/wo_views/priorities_pdf.js'])
@endsection

@php
    $userPerfil = (string) (auth()->user()->perfil ?? '');
    $isMaster = $userPerfil === '3';

    if (!function_exists('safeDateParse')) {
        function safeDateParse($value)
        {
            if (empty($value)) {
                return '';
            }
            try {
                $clean = str_replace('/', '-', $value);
                return \Carbon\Carbon::parse($clean)->format('Y-m-d');
            } catch (\Exception $e) {
                return '';
            }
        }
    }

    if (!function_exists('safeDateParseDisplay')) {
        function safeDateParseDisplay($value)
        {
            if (empty($value)) {
                return '';
            }
            try {
                $clean = str_replace('/', '-', $value);
                return \Carbon\Carbon::parse($clean)->format('d/m/Y');
            } catch (\Exception $e) {
                return $value;
            }
        }
    }

    if (!function_exists('renderDateDisplayOrIcon')) {
        function renderDateDisplayOrIcon($value)
        {
            $parsed = safeDateParseDisplay($value);
            return !empty($parsed) ? e($parsed) : '-';
        }
    }

    if (!function_exists('renderEditableTextOrIcon')) {
        function renderEditableTextOrIcon($value)
        {
            $clean = trim($value ?? '');
            return (!empty($clean) && $clean !== '-') ? e($clean) : '-';
        }
    }

    if (!function_exists('getPastelColorForWeek')) {
        function getPastelColorForWeek($weekString)
        {
            $premiumPastels = [
                '#d4f0f0', '#ffdfd3', '#e2f0cb', '#f3e5f5', '#ffebd2',
                '#d6e4ff', '#ffe5e5', '#e8f4d9', '#f0e6ff', '#fff2cc',
                '#d9f0f9', '#fbe4e4', '#e0f7fa', '#f5f5dc', '#e6e6fa',
                '#ffefd5', '#e0e8f5', '#f0fff0', '#fff0f5', '#fdf5e6',
            ];

            $weekNum = (int) preg_replace('/[^0-9]/', '', $weekString);
            if ($weekNum === 0) {
                $weekNum = crc32($weekString) % count($premiumPastels);
            }
            $index = $weekNum % count($premiumPastels);
            return $premiumPastels[$index];
        }
    }
@endphp

@section('content')
@section('background-body')
    background-image: url('{{ asset('images/fondos/fondoLogin.jpg') }}');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    background-attachment: fixed;
@endsection

<div class="priorities-wrapper priorities-disabled-theme">
    <!-- Barra de Filtros y Herramientas (Oculta al imprimir) -->
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
                            OT {{ $ot->id }} - {{ $ot->cliente ?? 'Sin Cliente' }}
                            ({{ $ot->moldura->nombre ?? 'Sin Moldura' }})
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
                <button type="button" class="btn-actions-trigger" id="btnActionsTrigger" aria-expanded="false">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                    </svg>
                    <span>Acciones</span>
                    <svg class="dropdown-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </button>

                <div class="actions-dropdown-menu" id="actionsDropdownMenu">
                    <div class="actions-menu-header">
                        <span>Gestión de Inactivas</span>
                    </div>
                    @if ($isMaster)
                        <button type="button" class="actions-menu-item item-success" id="btnStartReactivateMode">
                            <div class="action-icon-wrapper">
                                <img src="{{ asset('images/Color_Icons/Generales/activar.png') }}" class="action-icon-main" alt="Reactivar OTs">
                            </div>
                            <div class="action-text">
                                <strong>Reactivar OTs</strong>
                                <small>Volver a activar en todas las vistas</small>
                            </div>
                        </button>
                        <button type="button" class="actions-menu-item item-delete-permanent" id="btnStartDeleteMode">
                            <div class="action-icon-wrapper">
                                <img src="{{ asset('images/Color_Icons/Generales/delete.png') }}" class="action-icon-main" alt="Eliminar por completo">
                            </div>
                            <div class="action-text">
                                <strong style="color: #fca5a5;">Eliminar por Completo</strong>
                                <small style="color: #f87171;">Eliminación definitiva del sistema</small>
                            </div>
                        </button>
                    @endif
                </div>
            </div>

            {{-- Controles durante el Modo Selección Múltiple (Reactivación / Eliminación) --}}
            <div class="selection-mode-controls selection-panel-disabled" id="selectionModeControls" style="display: none;">
                <span class="selection-mode-title" id="selectionModeTitle">Reactivando OTs</span>
                <div class="selection-counter-badge" id="selectionBadge">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    <span id="selectedCountText">0 seleccionadas</span>
                </div>
                <button type="button" class="btn-cancel-selection" id="btnCancelSelection">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    Cancelar
                </button>
                <button type="button" class="btn-confirm-selection btn-confirm-reactivate" id="btnConfirmReactivate" style="display: none;" disabled>
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    Confirmar Reactivación
                </button>
                <button type="button" class="btn-confirm-selection btn-confirm-delete-permanent" id="btnConfirmDelete" style="display: none;" disabled>
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                    Confirmar Eliminación Definitiva
                </button>
            </div>
        </div>
    </div>

    <!-- Tabla y Encabezado -->
    <div class="excel-table-container">
        <!-- Encabezado Estilo Excel -->
        <div class="excel-header">
            <div class="header-logo">
                <img src="{{ asset('images/logos/lg_saavedra.png') }}" alt="Grupo Industrial Saavedra" />
            </div>
            <div class="header-title" style="display: flex; align-items: center; justify-content: center; gap: 12px;">
                @if (file_exists(public_path('images/Color_Icons/Vistas/Prioridades.png')))
                    <img src="{{ asset('images/Color_Icons/Vistas/Prioridades.png') }}" alt="Prioridades"
                        style="height: 38px; width: auto; object-fit: contain; filter: grayscale(100%);">
                @endif
                <h1 style="color: #dc2626;">PRIORIDADES DESACTIVADAS</h1>
            </div>
            <div class="header-week">
                @if (!empty($startWeek) && !empty($endWeek) && $startWeek == $endWeek)
                    <h2>SEMANA {{ $startWeek }}</h2>
                @elseif(!empty($startWeek) || !empty($endWeek))
                    <h2>SEMANAS {{ $startWeek ?? 1 }}-{{ $endWeek ?? 52 }}</h2>
                @else
                    <h2>SEMANA {{ \Carbon\Carbon::now()->isoWeek() }}</h2>
                @endif
                <p>HISTORIAL DE OTs INACTIVAS</p>
            </div>
            <div class="header-date">
                <h3>{{ \Carbon\Carbon::now()->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}</h3>
            </div>
        </div>

        <table class="excel-table" id="prioritiesTable">
            <thead>
                <tr>
                    @if ($isMaster)
                        <th rowspan="2" class="col-select no-print">
                            <input type="checkbox" id="selectAllCheckboxDisabled" title="Seleccionar todas las OTs">
                        </th>
                    @endif
                    <th rowspan="2" class="col-num">Nº</th>
                    <th rowspan="2" class="col-ot">ORDEN DE TRABAJO</th>
                    <th rowspan="2" class="col-compra">FECHA Y ORDEN DE COMPRA CLIENTE</th>
                    <th rowspan="2" class="col-cliente">CLIENTE</th>
                    <th rowspan="2" class="col-producto">NOMBRE DEL PRODUCTO</th>
                    <th rowspan="2" class="col-cantidades">CANTIDADES</th>
                    <th rowspan="2" class="col-piezas">PIEZAS</th>
                    <th rowspan="2" class="col-forma">FORMA<br>GRABADOS<br>1,2,3,4</th>
                    <th rowspan="2" class="col-prov">PROVEEDOR DE MATERIAL</th>
                    <th rowspan="2" class="col-material">MATERIAL</th>
                    <th rowspan="2" class="col-fecha-fund">FECHA DE ENTREGA PROVEEDOR FUNDICION</th>
                    <th rowspan="2" class="col-fecha-tec">FECHA ENTREGA TECAMAC</th>
                    <th rowspan="2" class="col-semana">N° DE SEMANA</th>
                    <th rowspan="2" class="col-fecha-mex">FECHA ENTREGA MEXICO</th>
                    <th rowspan="2" class="col-fecha-prom" style="background-color: yellow;">FECHA PROMETIDA ENTREGA</th>
                    <th rowspan="2" class="col-obs text-center">OBSERVACIONES</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $rowCount = 1;
                    $totalWosCount = 0;
                    foreach ($groupedWOs as $semana => $wos) {
                        $totalWosCount += count($wos);
                    }
                @endphp
                @if ($totalWosCount === 0)
                    <tr>
                        <td colspan="{{ 16 + ($isMaster ? 1 : 0) }}" class="text-center p-4"
                            style="background-color: #ffffff; padding: 48px 20px !important;">
                            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px;">
                                <img src="{{ asset('images/Color_Icons/Generales/calendario.png') }}" alt="Sin OTs"
                                    style="width: 58px; height: 58px; object-fit: contain; filter: drop-shadow(0 4px 8px rgba(220, 38, 38, 0.35));">
                                <span style="color: #991b1b; font-size: 1.15em; font-weight: 700; letter-spacing: 0.3px;">
                                    No hay ninguna Orden de Trabajo desactivada en este momento.
                                </span>
                                <span style="color: #64748b; font-size: 0.88em; font-weight: 500;">
                                    Todas las OTs se encuentran activas en el Dashboard de Prioridades.
                                </span>
                            </div>
                        </td>
                    </tr>
                @else
                    @foreach ($groupedWOs as $semana => $wos)
                        @php
                            $rowColor = getPastelColorForWeek($semana);
                        @endphp
                        @foreach ($wos as $wo)
                            @php
                                $rawClases = $wo->clases ?? collect();
                                if ($rawClases->count() > 0) {
                                    $sortedClases = $rawClases
                                        ->sortBy(function ($cl) use ($wo) {
                                            return !empty($cl->fecha_entrega_fundicion)
                                                ? trim($cl->fecha_entrega_fundicion)
                                                : (!empty($wo->fecha_entrega_fundicion)
                                                    ? trim($wo->fecha_entrega_fundicion)
                                                    : '');
                                        })
                                        ->values();
                                } else {
                                    $sortedClases = collect();
                                }
                            @endphp
                            <tr class="pm-table-row is-disabled-priority-row"
                                data-ot-id="{{ $wo->id }}"
                                style="background-color: {{ $rowColor }};">
                                @if ($isMaster)
                                    <td class="col-select no-print text-center">
                                        <input type="checkbox" class="ot-select-checkbox-disabled" data-ot-id="{{ $wo->id }}" data-ot-label="OT {{ $wo->id }} - {{ $wo->cliente ?? 'Sin Cliente' }} ({{ $wo->moldura->nombre ?? $wo->nombre_producto ?? 'Sin Moldura' }})">
                                    </td>
                                @endif
                                <td class="text-center col-num">{{ $rowCount++ }}</td>
                                <td class="text-center font-bold col-ot" style="font-weight: 800; color: #1e293b;">
                                    {{ $wo->id }}
                                </td>
                                <td class="col-compra">
                                    <div class="compra-cell-grid">
                                        <div class="compra-field-row">
                                            <span class="compra-field-label">FECHA:</span>
                                            <span class="compra-field-val">{{ renderDateDisplayOrIcon($wo->fecha_compra) }}</span>
                                        </div>
                                        <div class="compra-divider-line"></div>
                                        <div class="compra-field-row">
                                            <span class="compra-field-label">OC:</span>
                                            <span class="compra-field-val">{{ renderEditableTextOrIcon($wo->orden_compra) }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="col-cliente font-bold">
                                    {{ $wo->cliente ?? 'Sin Cliente' }}
                                </td>
                                <td class="col-producto font-bold">
                                    {{ $wo->moldura ? $wo->moldura->nombre : $wo->nombre_producto ?? 'Sin Moldura' }}
                                </td>
                                <td class="col-cantidades">
                                    <div class="sub-row-cell">
                                        @if ($sortedClases->count() > 0)
                                            @foreach ($sortedClases as $clase)
                                                <div class="sub-row-item text-center">{{ $clase->pedido ?? 0 }}</div>
                                            @endforeach
                                        @else
                                            <div class="sub-row-item text-center">{{ $wo->cantidad ?? 0 }}</div>
                                        @endif
                                    </div>
                                </td>
                                <td class="col-piezas">
                                    <div class="sub-row-cell">
                                        @if ($sortedClases->count() > 0)
                                            @foreach ($sortedClases as $clase)
                                                <div class="sub-row-item font-semibold text-center">{{ $clase->nombre }}</div>
                                            @endforeach
                                        @else
                                            <div class="sub-row-item font-semibold text-center">-</div>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center col-forma">
                                    {{ renderEditableTextOrIcon($wo->forma_grabados) }}
                                </td>
                                <td class="col-prov">
                                    <div class="sub-row-cell">
                                        @if ($sortedClases->count() > 0)
                                            @foreach ($sortedClases as $clase)
                                                <div class="sub-row-item text-center">
                                                    {{ !empty($clase->proveedor) ? $clase->proveedor : (!empty($wo->proveedor_material) ? $wo->proveedor_material : '-') }}
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="sub-row-item text-center">{{ $wo->proveedor_material ?? '-' }}</div>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center col-material font-semibold">
                                    {{ renderEditableTextOrIcon($wo->material) }}
                                </td>
                                <td class="col-fecha-fund">
                                    <div class="sub-row-cell">
                                        @if ($sortedClases->count() > 0)
                                            @foreach ($sortedClases as $clase)
                                                <div class="sub-row-item text-center">
                                                    {{ renderDateDisplayOrIcon(!empty($clase->fecha_entrega_fundicion) ? $clase->fecha_entrega_fundicion : $wo->fecha_entrega_fundicion) }}
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="sub-row-item text-center">{{ renderDateDisplayOrIcon($wo->fecha_entrega_fundicion) }}</div>
                                        @endif
                                    </div>
                                </td>
                                <td class="col-fecha-tec text-center">
                                    <div class="sub-row-cell">
                                        @if ($sortedClases->count() > 0)
                                            @foreach ($sortedClases as $clase)
                                                <div class="sub-row-item text-center">
                                                    {{ renderDateDisplayOrIcon(!empty($clase->entrega_tecamac) ? $clase->entrega_tecamac : $wo->entrega_tecamac) }}
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="sub-row-item text-center">{{ renderDateDisplayOrIcon($wo->entrega_tecamac) }}</div>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center col-semana">
                                    <span class="week-pill-badge" style="background-color: rgba(0,0,0,0.06); padding: 2px 8px; border-radius: 4px; font-weight: 700;">
                                        {{ $semana }}
                                    </span>
                                </td>
                                <td class="col-fecha-mex text-center">
                                    {{ renderDateDisplayOrIcon($wo->fecha_entrega_cliente) }}
                                </td>
                                <td class="col-fecha-prom text-center font-bold" style="background-color: yellow; color: #000000;">
                                    {{ renderDateDisplayOrIcon($wo->fecha_real) }}
                                </td>
                                <td class="col-obs">
                                    {{ renderEditableTextOrIcon($wo->observaciones_prioridad) }}
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
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

        // Toggle dropdown
        if (btnActionsTrigger && actionsContainer) {
            btnActionsTrigger.addEventListener('click', function(e) {
                e.stopPropagation();
                var isOpen = actionsContainer.classList.contains('is-open');
                if (isOpen) {
                    actionsContainer.classList.remove('is-open');
                    btnActionsTrigger.setAttribute('aria-expanded', 'false');
                } else {
                    actionsContainer.classList.add('is-open');
                    btnActionsTrigger.setAttribute('aria-expanded', 'true');
                }
            });

            document.addEventListener('click', function(e) {
                if (!actionsContainer.contains(e.target)) {
                    actionsContainer.classList.remove('is-open');
                    btnActionsTrigger.setAttribute('aria-expanded', 'false');
                }
            });
        }

        function updateSelectionState() {
            var checkedBoxes = document.querySelectorAll('.ot-select-checkbox-disabled:checked');
            var totalBoxes = document.querySelectorAll('.ot-select-checkbox-disabled');
            var count = checkedBoxes.length;

            if (selectedCountText) {
                if (currentSelectionMode === 'delete') {
                    selectedCountText.textContent = count === 1 ? '1 OT a eliminar' : count + ' OTs a eliminar';
                } else {
                    selectedCountText.textContent = count === 1 ? '1 OT a reactivar' : count + ' OTs a reactivar';
                }
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
            if (prioritiesTable) prioritiesTable.classList.add('is-selection-mode');

            var modeTitle = document.getElementById('selectionModeTitle');
            if (selectionControls) {
                selectionControls.classList.toggle('mode-reactivate', mode === 'reactivate');
                selectionControls.classList.toggle('mode-delete', mode === 'delete');
            }
            if (modeTitle) modeTitle.textContent = (mode === 'delete') ? 'Eliminando OTs' : 'Reactivando OTs';

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
            if (prioritiesTable) prioritiesTable.classList.remove('is-selection-mode');

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

        // Toggle individual checkbox y clic en celda
        if (prioritiesTable) {
            prioritiesTable.addEventListener('change', function(e) {
                if (e.target && e.target.classList.contains('ot-select-checkbox-disabled')) {
                    var tr = e.target.closest('tr');
                    if (tr) {
                        var targetClass = (currentSelectionMode === 'delete') ? 'row-selected-delete' : 'row-selected-reactivate';
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
                        cb.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                }
            });
        }

        // Seleccionar / Deseleccionar todas
        if (selectAllCheckboxDisabled) {
            selectAllCheckboxDisabled.addEventListener('change', function() {
                var isChecked = this.checked;
                var targetClass = (currentSelectionMode === 'delete') ? 'row-selected-delete' : 'row-selected-reactivate';
                document.querySelectorAll('.ot-select-checkbox-disabled').forEach(function(cb) {
                    cb.checked = isChecked;
                    var tr = cb.closest('tr');
                    if (tr) {
                        if (isChecked) {
                            tr.classList.add(targetClass);
                        } else {
                            tr.classList.remove('row-selected-delete', 'row-selected-reactivate');
                        }
                    }
                });
                updateSelectionState();
            });
        }

        // ── 1. Confirmar Reactivación ──
        if (btnConfirmReactivate) {
            btnConfirmReactivate.addEventListener('click', function() {
                var checkedBoxes = Array.from(document.querySelectorAll('.ot-select-checkbox-disabled:checked'));
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
                    title: (selectedOts.length === 1 ? '¿Reactivar orden de trabajo?' : '¿Reactivar órdenes de trabajo?'),
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
                    customClass: { popup: 'swal2-gis-popup' }
                }).then(function(result) {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Reactivando OTs...',
                            html: 'Restableciendo el estado activo en la base de datos...',
                            allowOutsideClick: false,
                            customClass: { popup: 'swal2-gis-popup' },
                            didOpen: function() {
                                Swal.showLoading();
                            }
                        });

                        var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                        var otIds = selectedOts.map(function(item) { return item.id; });

                        fetch("{{ route('master.priorities.reactivate') }}", {
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
                                    title: '¡OTs Reactivadas!',
                                    text: data.message,
                                    timer: 1800,
                                    showConfirmButton: false,
                                    customClass: { popup: 'swal2-gis-popup' }
                                }).then(function() {
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: data.message || 'No fue posible reactivar las OTs seleccionadas.',
                                    customClass: { popup: 'swal2-gis-popup' }
                                });
                            }
                        })
                        .catch(function(err) {
                            console.error("Error al reactivar:", err);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error de Red',
                                text: 'Ocurrió un error al procesar la solicitud con el servidor.',
                                customClass: { popup: 'swal2-gis-popup' }
                            });
                        });
                    }
                });
            });
        }

        // ── 2. Confirmar Eliminación Permanente ──
        if (btnConfirmDelete) {
            btnConfirmDelete.addEventListener('click', function() {
                var checkedBoxes = Array.from(document.querySelectorAll('.ot-select-checkbox-disabled:checked'));
                if (checkedBoxes.length === 0) return;

                var selectedOts = checkedBoxes.map(function(cb) {
                    return {
                        id: cb.dataset.otId,
                        label: cb.dataset.otLabel || ('OT ' + cb.dataset.otId)
                    };
                });

                var listHtml = '<ul style="text-align: left; max-height: 150px; overflow-y: auto; padding-left: 20px; margin: 10px 0; font-size: 0.9rem; color: #991b1b;">';
                selectedOts.forEach(function(ot) {
                    listHtml += '<li style="margin-bottom: 4px;"><strong>' + ot.label + '</strong></li>';
                });
                listHtml += '</ul>';

                Swal.fire({
                    title: (selectedOts.length === 1 ? '¿Eliminar orden de trabajo?' : '¿Eliminar órdenes de trabajo?'),
                    html: `
                        <div style="text-align: left; font-size: 0.92rem; line-height: 1.5; color: #334155;">
                            <div style="background-color: #fef2f2; border: 1.5px solid #ef4444; border-radius: 8px; padding: 12px; margin-bottom: 12px;">
                                <p style="margin: 0; color: #991b1b; font-weight: 700; font-size: 0.95rem;">
                                    Acción destructiva e irreversible
                                </p>
                                <p style="margin: 6px 0 0 0; color: #b91c1c; font-size: 0.85rem;">
                                    Se borrarán permanentemente de la base de datos las clases, piezas, mediciones dimensionales, trazabilidad histórica y registros de avance vinculados.
                                </p>
                            </div>
                            <p style="font-weight: 600; color: #475569; margin-bottom: 4px;">Órdenes de Trabajo seleccionadas para destrucción:</p>
                            ${listHtml}
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Eliminar definitivamente',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#b91c1c',
                    cancelButtonColor: '#64748b',
                    reverseButtons: true,
                    focusCancel: true,
                    customClass: { popup: 'swal2-gis-popup' }
                }).then(function(result) {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Eliminando OTs permanentemente...',
                            html: 'Borrando registros, clases y procesos asociados...',
                            allowOutsideClick: false,
                            didOpen: function() {
                                Swal.showLoading();
                            }
                        });

                        var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                        var otIds = selectedOts.map(function(item) { return item.id; });

                        fetch("{{ route('master.priorities.delete_permanent') }}", {
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
                                    title: '¡OTs Eliminadas!',
                                    text: data.message,
                                    timer: 2000,
                                    showConfirmButton: false
                                }).then(function() {
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error al Eliminar',
                                    text: data.message || 'No fue posible eliminar permanentemente las OTs seleccionadas.'
                                });
                            }
                        })
                        .catch(function(err) {
                            console.error("Error al eliminar permanentemente:", err);
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
</script>
@endsection
