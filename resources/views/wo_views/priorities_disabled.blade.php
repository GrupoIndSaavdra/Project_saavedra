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
    @include('wo_views.partials.priorities.toolbar_disabled')

    <!-- Tabla y Encabezado -->
    <div class="excel-table-container">
        @include('wo_views.partials.priorities.header_disabled')
        <table class="excel-table" id="prioritiesTable">
            <thead>
                <tr>
                    @if ($isMaster)
                        <th class="col-select no-print">
                            <input type="checkbox" id="selectAllCheckboxDisabled" title="Seleccionar todas las OTs">
                        </th>
                    @endif
                    <th class="col-num">Nº</th>
                    <th class="col-ot">ORDEN DE TRABAJO</th>
                    <th class="col-compra">FECHA Y ORDEN DE COMPRA CLIENTE</th>
                    <th class="col-cliente">CLIENTE</th>
                    <th class="col-producto">NOMBRE DEL PRODUCTO</th>
                    <th class="col-cantidades">CANTIDADES</th>
                    <th class="col-piezas">PIEZAS</th>
                    <th class="col-forma">FORMA<br>GRABADOS<br>1,2,3,4</th>
                    <th class="col-prov">PROVEEDOR DE MATERIAL</th>
                    <th class="col-material">MATERIAL</th>
                    <th class="col-fecha-fund">FECHA DE ENTREGA PROVEEDOR FUNDICION</th>
                    <th class="col-fecha-tec">FECHA ENTREGA TECAMAC</th>
                    <th class="col-semana">N° DE SEMANA</th>
                    <th class="col-fecha-mex">FECHA ENTREGA MEXICO</th>
                    <th class="col-fecha-prom" style="background-color: yellow;">FECHA PROMETIDA ENTREGA</th>
                    <th class="col-obs text-center">OBSERVACIONES</th>
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
                        <td colspan="16" class="text-center p-4 empty-state-cell"
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
                                        <input type="checkbox" class="ot-select-checkbox-disabled"
                                            data-ot-id="{{ $wo->id }}"
                                            data-ot-label="OT {{ $wo->id }} - {{ $wo->cliente ?? 'Sin Cliente' }} ({{ $wo->moldura->nombre ?? $wo->nombre_producto ?? 'Sin Moldura' }})"
                                            data-product="{{ $wo->moldura->nombre ?? $wo->nombre_producto ?? 'Sin Moldura' }}"
                                            data-client="{{ $wo->cliente ?? 'Sin Cliente' }}"
                                            data-classes="{{ $wo->clases->pluck('nombre')->implode(', ') }}">
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

@include('wo_views.partials.priorities.scripts_disabled')
@endsection
