@extends('layouts.appMenu')

@section('head')
    <title>Dashboard de Prioridades</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('images/logos/favicon.ico') }}?v=3">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @vite(['resources/css/wo_views/priorities.css', 'resources/js/wo_views/priorities_pdf.js'])
@endsection

@php
    $userPerfil = (string) (auth()->user()->perfil ?? '');
    $canEditPriorities = $userPerfil === '3';
    $isReadOnly = !$canEditPriorities;

    if (!function_exists('safeDateParse')) {
        function safeDateParse($value)
        {
            if (empty($value)) {
                return '';
            }
            try {
                // Handle common formats before parsing
                $clean = str_replace('/', '-', $value);
                return \Carbon\Carbon::parse($clean)->format('Y-m-d');
            } catch (\Exception $e) {
                return ''; // If it's text like "FGF", return empty for the datepicker
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
            return $value; // If it's text, just display the text
            }
        }
    }

    if (!function_exists('renderDateDisplayOrIcon')) {
        function renderDateDisplayOrIcon($value, $isReadOnly = false)
        {
            $parsed = safeDateParseDisplay($value);
            if (!empty($parsed)) {
                return e($parsed);
            }
            if ($isReadOnly) {
                return '-';
            }
            return '<span class="empty-date-icon-wrap" title="Hacer clic para asignar fecha"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="empty-date-svg" style="vertical-align: middle; opacity: 0.65;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg></span>';
        }
    }

    if (!function_exists('renderEditableTextOrIcon')) {
        function renderEditableTextOrIcon($value, $isReadOnly = false)
        {
            $clean = trim($value ?? '');
            if (!empty($clean) && $clean !== '-') {
                return e($clean);
            }
            if ($isReadOnly) {
                return '-';
            }
            return '<span class="empty-editable-ph" style="color: #94a3b8; font-size: 0.85em; display: inline-flex; align-items: center; justify-content: center; gap: 3px;" title="Hacer clic para editar"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>-</span>';
        }
    }

    if (!function_exists('getPastelColorForWeek')) {
        function getPastelColorForWeek($weekString)
        {
            // Una paleta curada de 20 colores pasteles profesionales y armoniosos
            $premiumPastels = [
                '#d4f0f0', // Cyan claro
                '#ffdfd3', // Peach
                '#e2f0cb', // Menta suave
                '#f3e5f5', // Lila
                '#ffebd2', // Naranja suave
                '#d6e4ff', // Azul bebe
                '#ffe5e5', // Rosa claro
                '#e8f4d9', // Verde te
                '#f0e6ff', // Lavanda
                '#fff2cc', // Amarillo pastel
                '#d9f0f9', // Celeste
                '#fbe4e4', // Rosa palido
                '#e0f7fa', // Cyan
                '#f5f5dc', // Beige
                '#e6e6fa', // Lavanda gris
                '#ffefd5', // Papaya
                '#e0e8f5', // Azul lavanda
                '#f0fff0', // Honeydew
                '#fff0f5', // Lavender blush
                '#fdf5e6', // Old lace
            ];

            $weekNum = (int) preg_replace('/[^0-9]/', '', $weekString);
            if ($weekNum === 0) {
                $weekNum = crc32($weekString) % count($premiumPastels);
            }

            // Usar el número de semana como índice (haciendo un wrap-around si es mayor a 20)
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

<div class="priorities-wrapper">
    @include('wo_views.partials.priorities.toolbar')

    <!-- Tabla y Encabezado -->
    <div class="excel-table-container">
        @include('wo_views.partials.priorities.header')
        <table class="excel-table" id="prioritiesTable">
            <thead>
                <tr>
                    @if ($canEditPriorities)
                        <th class="col-select no-print">
                            <input type="checkbox" id="selectAllCheckbox" title="Seleccionar todas las OTs">
                        </th>
                    @endif
                    @if (!$isReadOnly)
                        <th class="col-drag no-print">MOVER</th>
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
                    <th class="col-fecha-prom" style="background-color: yellow;">FECHA PROMETIDA
                        ENTREGA
                    </th>
                    <th class="col-obs text-center">OBSERVACIONES</th>
                    <th class="col-documents text-center no-print">ARCHIVOS</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $rowCount = 1;
                    $wosJsData = [];
                    $totalWosCount = 0;
                    foreach ($groupedWOs as $semana => $wos) {
                        $totalWosCount += count($wos);
                    }
                @endphp
                @if ($totalWosCount === 0)
                    <tr>
                        <td colspan="{{ 17 + (int) $canEditPriorities + (int) (!$isReadOnly) }}" class="text-center p-4"
                            style="background-color: #ffffff; padding: 48px 20px !important;">
                            <div
                                style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px;">
                                <img src="{{ asset('images/Color_Icons/Generales/calendario.png') }}" alt="Calendario"
                                    style="width: 58px; height: 58px; object-fit: contain; filter: drop-shadow(0 4px 8px rgba(220, 38, 38, 0.25));">
                                <span
                                    style="color: #dc2626; font-size: 1.15em; font-weight: 700; letter-spacing: 0.3px;">
                                    Esta semana o consulta no tiene ninguna Orden de Trabajo asignada actualmente.
                                </span>
                                <span style="color: #64748b; font-size: 0.88em; font-weight: 500;">
                                    Intenta seleccionando otro número de semana o limpiando los filtros de búsqueda.
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
                                $isInactive = $wo->clases->where('finalizada', 0)->count() === 0;
                                $rowColor = $isInactive ? '#d6d6d6' : getPastelColorForWeek($semana);

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

                                $clasesJsArr = [];
                                foreach ($sortedClases as $cl) {
                                    $clasesJsArr[] = [
                                        'id' => $cl->id,
                                        'nombre' => $cl->nombre,
                                        'pedido' => $cl->pedido,
                                        'proveedor' => !empty($cl->proveedor)
                                            ? trim($cl->proveedor)
                                            : (!empty($wo->proveedor_material)
                                                ? trim($wo->proveedor_material)
                                                : ''),
                                        'fecha_entrega_fundicion' => safeDateParse(
                                            !empty($cl->fecha_entrega_fundicion)
                                                ? $cl->fecha_entrega_fundicion
                                                : $wo->fecha_entrega_fundicion,
                                        ),
                                    ];
                                }

                                $hasCastingPfc = \App\Models\PreOrdenFundicion::where(
                                    'ot',
                                    'LIKE',
                                    '%' . $wo->id . '%',
                                )->exists();

                                $wosJsData[$wo->id] = [
                                    'id' => $wo->id,
                                    'cliente' => $wo->cliente ?? 'Sin Cliente',
                                    'moldura' => $wo->moldura
                                        ? $wo->moldura->nombre
                                        : $wo->nombre_producto ?? 'Sin Moldura',
                                    'proveedor_material' => $wo->proveedor_material ?? '',
                                    'fecha_entrega_fundicion' => safeDateParse($wo->fecha_entrega_fundicion),
                                    'clases' => $clasesJsArr,
                                    'hasCastingPfc' => $hasCastingPfc,
                                ];
                            @endphp
                            <tr class="pm-table-row {{ $isInactive ? 'is-inactive-ot' : '' }}"
                                data-ot-id="{{ $wo->id }}" data-is-inactive="{{ $isInactive ? '1' : '0' }}"
                                style="background-color: {{ $rowColor }};">
                                @if ($canEditPriorities)
                                    <td class="col-select no-print text-center">
                                        <input type="checkbox" class="ot-select-checkbox" data-ot-id="{{ $wo->id }}" data-ot-label="OT {{ $wo->id }} - {{ $wo->cliente ?? 'Sin Cliente' }} ({{ $wo->moldura->nombre ?? $wo->nombre_producto ?? 'Sin Moldura' }})">
                                    </td>
                                @endif
                                @if (!$isReadOnly)
                                    <td class="text-center no-print cell-drag">
                                        <div class="table-drag-handle"
                                            title="Sujeta y arrastra para mover de posición la OT">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="9" cy="5" r="1.5" fill="currentColor" />
                                                <circle cx="9" cy="12" r="1.5" fill="currentColor" />
                                                <circle cx="9" cy="19" r="1.5" fill="currentColor" />
                                                <circle cx="15" cy="5" r="1.5" fill="currentColor" />
                                                <circle cx="15" cy="12" r="1.5" fill="currentColor" />
                                                <circle cx="15" cy="19" r="1.5" fill="currentColor" />
                                            </svg>
                                        </div>
                                    </td>
                                @endif
                                <td class="text-center font-bold font-large cell-row-num">{{ $rowCount++ }}</td>
                                <td class="text-center font-bold cell-ot-val">{{ $wo->id }}</td>
                                <td class="text-center font-bold">
                                    @if ($wo->fecha_compra)
                                        {{ \Carbon\Carbon::parse($wo->fecha_compra)->format('d/m/Y') }}<br>
                                    @endif
                                    {{ $wo->orden_compra }}
                                </td>
                                <td class="text-center font-bold cell-cliente-val">{{ $wo->cliente }}</td>
                                <td class="text-center font-bold cell-producto-val">
                                    {{ $wo->moldura ? $wo->moldura->nombre : $wo->nombre_producto }}
                                </td>
                                <td class="text-center p-0 font-bold cell-subcells"
                                    style="vertical-align: stretch; height: 1px;">
                                    <div class="subcell-container">
                                        @if ($sortedClases->count() > 0)
                                            @foreach ($sortedClases as $cl)
                                                <div class="subcell-row font-bold">{{ $cl->pedido }}</div>
                                            @endforeach
                                        @else
                                            <div class="subcell-row font-bold">{{ $wo->cantidad }}</div>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center p-0 text-uppercase font-bold cell-subcells"
                                    style="vertical-align: stretch; height: 1px;">
                                    <div class="subcell-container">
                                        @if ($sortedClases->count() > 0)
                                            @foreach ($sortedClases as $cl)
                                                <div class="subcell-row font-bold">{{ $cl->nombre }}</div>
                                            @endforeach
                                        @else
                                            <div class="subcell-row font-bold">-</div>
                                        @endif
                                    </div>
                                </td>
                                @if (!$isReadOnly)
                                    <td class="text-center" contenteditable="true"
                                        onblur="autosaveField({{ $wo->id }}, 'forma_grabados', this)">
                                        {!! renderEditableTextOrIcon($wo->forma_grabados, false) !!}</td>
                                @else
                                    <td class="text-center">{!! renderEditableTextOrIcon($wo->forma_grabados, true) !!}</td>
                                @endif
                                @php
                                    $supplierBlocks = [];
                                    if ($sortedClases->count() > 0) {
                                        $currentBlock = null;
                                        foreach ($sortedClases as $cl) {
                                            $clProv = !empty($cl->proveedor)
                                                ? trim($cl->proveedor)
                                                : (!empty($wo->proveedor_material)
                                                    ? trim($wo->proveedor_material)
                                                    : '-');
                                            $rawName = $cl->nombre ?? 'Clase';
                                            $cleanName = trim(preg_replace('/^[0-9]+\s*-\s*/', '', $rawName));
                                            if (empty($cleanName)) {
                                                $cleanName = $rawName;
                                            }

                                            if ($currentBlock === null) {
                                                $currentBlock = [
                                                    'supplier' => $clProv,
                                                    'count' => 1,
                                                    'clases' => [$cleanName],
                                                ];
                                            } elseif ($currentBlock['supplier'] === $clProv) {
                                                $currentBlock['count']++;
                                                $currentBlock['clases'][] = $cleanName;
                                            } else {
                                                $supplierBlocks[] = $currentBlock;
                                                $currentBlock = [
                                                    'supplier' => $clProv,
                                                    'count' => 1,
                                                    'clases' => [$cleanName],
                                                ];
                                            }
                                        }
                                        if ($currentBlock !== null) {
                                            $supplierBlocks[] = $currentBlock;
                                        }
                                    } else {
                                        $supplierBlocks[] = [
                                            'supplier' => $wo->proveedor_material ?? '-',
                                            'count' => 1,
                                            'clases' => [],
                                        ];
                                    }
                                @endphp
                                @if ($isReadOnly)
                                    @if ($sortedClases->count() <= 1)
                                        <td class="text-center cell-suppliers font-bold">
                                            {{ $supplierBlocks[0]['supplier'] ?? ($wo->proveedor_material ?? '-') }}
                                        </td>
                                    @elseif(count($supplierBlocks) === 1)
                                        <td class="text-center cell-suppliers font-bold">
                                            {{ $supplierBlocks[0]['supplier'] }}
                                        </td>
                                    @else
                                        <td class="text-center p-0 cell-suppliers cell-subcells"
                                            style="vertical-align: stretch; height: 1px;">
                                            <div class="subcell-container">
                                                @foreach ($supplierBlocks as $bIdx => $block)
                                                    <div class="subcell-row supplier-cell"
                                                        style="flex: {{ $block['count'] }} {{ $block['count'] }} auto; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 4px 2px; box-sizing: border-box; min-height: 28px;">
                                                        @if (!empty($block['clases']) && count($supplierBlocks) > 1 && count($supplierBlocks) < $sortedClases->count())
                                                            <span class="subcell-class-label font-bold"
                                                                style="font-size: 10px; color: #033966; text-transform: uppercase; font-weight: 800; line-height: 1.1; margin-bottom: 1px; display: block; text-align: center; word-break: break-word;">
                                                                {{ implode(', ', $block['clases']) }}
                                                            </span>
                                                        @endif
                                                        <span
                                                            style="font-weight: 600;">{{ $block['supplier'] }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </td>
                                    @endif
                                @else
                                    @if ($sortedClases->count() <= 1)
                                        <td class="text-center cell-suppliers"
                                            style="{{ !$hasCastingPfc ? 'cursor: pointer;' : '' }} font-weight: {{ $hasCastingPfc ? '900' : '500' }};"
                                            onclick="openSupplierModal({{ $wo->id }})"
                                            title="Hacer clic para seleccionar proveedor de fundición">
                                            {{ $supplierBlocks[0]['supplier'] ?? ($wo->proveedor_material ?? '-') }}
                                        </td>
                                    @elseif(count($supplierBlocks) === 1)
                                        <td class="text-center cell-suppliers"
                                            style="{{ !$hasCastingPfc ? 'cursor: pointer;' : '' }} font-weight: {{ $hasCastingPfc ? '900' : '500' }};"
                                            onclick="openSupplierModal({{ $wo->id }})"
                                            title="Hacer clic para abrir gestor de proveedor de fundición">
                                            {{ $supplierBlocks[0]['supplier'] }}
                                        </td>
                                    @else
                                        <td class="text-center p-0 cell-suppliers cell-subcells"
                                            style="{{ !$hasCastingPfc ? 'cursor: pointer;' : '' }} vertical-align: stretch; height: 1px; font-weight: {{ $hasCastingPfc ? '900' : '500' }};"
                                            onclick="openSupplierModal({{ $wo->id }})"
                                            title="Hacer clic para abrir gestor de proveedor de fundición">
                                            <div class="subcell-container">
                                                @foreach ($supplierBlocks as $bIdx => $block)
                                                    <div class="subcell-row supplier-cell"
                                                        style="flex: {{ $block['count'] }} {{ $block['count'] }} auto; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 4px 2px; box-sizing: border-box; min-height: 28px; font-weight: {{ $hasCastingPfc ? '900' : '500' }};">
                                                        @if (!empty($block['clases']) && count($supplierBlocks) > 1 && count($supplierBlocks) < $sortedClases->count())
                                                            <span class="subcell-class-label font-bold"
                                                                style="font-size: 10px; color: #033966; text-transform: uppercase; font-weight: 800; line-height: 1.1; margin-bottom: 1px; display: block; text-align: center; word-break: break-word;">
                                                                {{ implode(', ', $block['clases']) }}
                                                            </span>
                                                        @endif
                                                        <span
                                                            style="font-weight: {{ $hasCastingPfc ? '900' : '500' }};">{{ $block['supplier'] }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </td>
                                    @endif
                                @endif
                                <td class="text-center p-0 cell-materials font-bold cell-subcells"
                                    style="vertical-align: stretch; height: 1px;">
                                    <div class="subcell-container">
                                        @if ($sortedClases->count() > 0)
                                            @foreach ($sortedClases as $cl)
                                                <div class="subcell-row font-bold">{{ $cl->material ?? '-' }}</div>
                                            @endforeach
                                        @else
                                            <div class="subcell-row font-bold">{{ $wo->material }}</div>
                                        @endif
                                    </div>
                                </td>
                                @php
                                    $fundDateBlocks = [];
                                    if ($sortedClases->count() > 0) {
                                        $currentBlock = null;
                                        foreach ($sortedClases as $cl) {
                                            $rawDate = !empty($cl->fecha_entrega_fundicion)
                                                ? $cl->fecha_entrega_fundicion
                                                : $wo->fecha_entrega_fundicion;
                                            $clFundDate = safeDateParse($rawDate);
                                            $rawName = $cl->nombre ?? 'Clase';
                                            $cleanName = trim(preg_replace('/^[0-9]+\s*-\s*/', '', $rawName));
                                            if (empty($cleanName)) {
                                                $cleanName = $rawName;
                                            }

                                            if ($currentBlock === null) {
                                                $currentBlock = [
                                                    'date' => $clFundDate,
                                                    'count' => 1,
                                                    'clases' => [$cleanName],
                                                ];
                                            } elseif ($currentBlock['date'] === $clFundDate) {
                                                $currentBlock['count']++;
                                                $currentBlock['clases'][] = $cleanName;
                                            } else {
                                                $fundDateBlocks[] = $currentBlock;
                                                $currentBlock = [
                                                    'date' => $clFundDate,
                                                    'count' => 1,
                                                    'clases' => [$cleanName],
                                                ];
                                            }
                                        }
                                        if ($currentBlock !== null) {
                                            $fundDateBlocks[] = $currentBlock;
                                        }
                                    } else {
                                        $fundDateBlocks[] = [
                                            'date' => safeDateParse($wo->fecha_entrega_fundicion ?? ''),
                                            'count' => 1,
                                            'clases' => [],
                                        ];
                                    }
                                    $singleClass = $sortedClases->count() === 1 ? $sortedClases->first() : null;
                                    $singleFundDate = $singleClass
                                        ? ($singleClass->fecha_entrega_fundicion ?:
                                        $wo->fecha_entrega_fundicion)
                                        : $wo->fecha_entrega_fundicion;
                                @endphp
                                @if ($isReadOnly)
                                    @if ($sortedClases->count() <= 1)
                                        <td class="text-center p-0 date-cell cell-subcells"
                                            style="vertical-align: stretch; height: 1px;">
                                            <div class="subcell-container">
                                                <div class="date-single-box subcell-row"
                                                    style="padding: 6px; width: 100%; box-sizing: border-box;">
                                                    <span class="date-display"
                                                        style="font-size: 11px; color: {{ $hasCastingPfc ? '#033966' : '#000000' }}; font-weight: {{ $hasCastingPfc ? '900' : '600' }};">{!! renderDateDisplayOrIcon($singleFundDate, true) !!}</span>
                                                </div>
                                            </div>
                                        </td>
                                    @else
                                        <td class="text-center p-0 date-cell-group cell-subcells"
                                            style="vertical-align: stretch; height: 1px;">
                                            <div class="subcell-container">
                                                @foreach ($fundDateBlocks as $bIdx => $block)
                                                    <div class="subcell-row date-cell"
                                                        style="flex: {{ $block['count'] }} {{ $block['count'] }} auto;">
                                                        @if (!empty($block['clases']) && count($fundDateBlocks) > 1 && count($fundDateBlocks) < $sortedClases->count())
                                                            <span class="subcell-class-label font-bold"
                                                                style="font-size: 10px; color: #033966; text-transform: uppercase; font-weight: 800; line-height: 1.1; margin-bottom: 1px; display: block; text-align: center; word-break: break-word;">
                                                                {{ implode(', ', $block['clases']) }}
                                                            </span>
                                                        @endif
                                                        <span class="date-display"
                                                            style="font-size: 11px; color: {{ $hasCastingPfc ? '#033966' : '#000000' }}; font-weight: {{ $hasCastingPfc ? '900' : '600' }}; line-height: 1.1;">{!! renderDateDisplayOrIcon($block['date'], true) !!}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </td>
                                    @endif
                                @else
                                    @if ($sortedClases->count() <= 1)
                                        <td class="text-center p-0 date-cell cell-subcells"
                                            style="{{ !$hasCastingPfc ? 'cursor: pointer;' : '' }} vertical-align: stretch; height: 1px;"
                                            onclick="openDatePicker(this)"
                                            title="Hacer clic para seleccionar fecha de fundición">
                                            <div class="subcell-container">
                                                <div class="date-single-box subcell-row"
                                                    style="padding: 6px; width: 100%; box-sizing: border-box;">
                                                    <span class="date-display"
                                                        style="font-size: 11px; color: {{ $hasCastingPfc ? '#033966' : '#000000' }}; font-weight: {{ $hasCastingPfc ? '900' : '500' }};">{!! renderDateDisplayOrIcon($singleFundDate) !!}</span>
                                                    <input type="date"
                                                        style="opacity:0; position:absolute; z-index:-1; width:1px; height:1px;"
                                                        value="{{ safeDateParse($singleFundDate) }}"
                                                        onchange="handleDateChange(this, {{ $wo->id }}, 'fecha_entrega_fundicion'{{ $singleClass ? ', ' . $singleClass->id : '' }})"
                                                        {{ $hasCastingPfc ? 'disabled' : '' }}>
                                                </div>
                                            </div>
                                        </td>
                                    @else
                                        <td class="text-center p-0 date-cell-group cell-subcells"
                                            style="{{ !$hasCastingPfc ? 'cursor: pointer;' : '' }} vertical-align: stretch; height: 1px;"
                                            onclick="openFundicionModal({{ $wo->id }})"
                                            title="Hacer clic para abrir gestor de fechas de fundición">
                                            <div class="subcell-container">
                                                @foreach ($fundDateBlocks as $bIdx => $block)
                                                    <div class="subcell-row date-cell"
                                                        style="flex: {{ $block['count'] }} {{ $block['count'] }} auto;">
                                                        @if (!empty($block['clases']) && count($fundDateBlocks) > 1 && count($fundDateBlocks) < $sortedClases->count())
                                                            <span class="subcell-class-label font-bold"
                                                                style="font-size: 10px; color: #033966; text-transform: uppercase; font-weight: 800; line-height: 1.1; margin-bottom: 1px; display: block; text-align: center; word-break: break-word;">
                                                                {{ implode(', ', $block['clases']) }}
                                                            </span>
                                                        @endif
                                                        <span class="date-display"
                                                            style="font-size: 11px; color: {{ $hasCastingPfc ? '#033966' : '#000000' }}; font-weight: {{ $hasCastingPfc ? '900' : '500' }}; line-height: 1.1;">{!! renderDateDisplayOrIcon($block['date']) !!}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </td>
                                    @endif
                                @endif
                                @php
                                    $classTecDates = $sortedClases
                                        ->map(function ($cl) use ($wo) {
                                            return !empty($cl->entrega_tecamac)
                                                ? trim($cl->entrega_tecamac)
                                                : (!empty($wo->entrega_tecamac)
                                                    ? trim($wo->entrega_tecamac)
                                                    : '');
                                        })
                                        ->filter();
                                    $uniqueTecDates = $classTecDates->unique();
                                @endphp
                                @if ($isReadOnly)
                                    @if ($sortedClases->count() > 0 && $uniqueTecDates->count() > 1)
                                        <td class="text-center p-0 cell-subcells"
                                            style="vertical-align: stretch; height: 1px;">
                                            <div class="subcell-container">
                                                @foreach ($sortedClases as $cl)
                                                    @php
                                                        $clTecDate = !empty($cl->entrega_tecamac)
                                                            ? $cl->entrega_tecamac
                                                            : $wo->entrega_tecamac;
                                                    @endphp
                                                    <div class="subcell-row date-cell">
                                                        <span class="date-display"
                                                            style="font-weight: 600;">{!! renderDateDisplayOrIcon($clTecDate, true) !!}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </td>
                                    @else
                                        @php
                                            $singleTecDate = $uniqueTecDates->first() ?? $wo->entrega_tecamac;
                                        @endphp
                                        <td class="text-center p-0 date-cell">
                                            <span class="date-display"
                                                style="font-weight: 600;">{!! renderDateDisplayOrIcon($singleTecDate, true) !!}</span>
                                        </td>
                                    @endif
                                @else
                                    @if ($sortedClases->count() > 0 && $uniqueTecDates->count() > 1)
                                        <td class="text-center p-0 cell-subcells"
                                            style="vertical-align: stretch; height: 1px;">
                                            <div class="subcell-container">
                                                @foreach ($sortedClases as $cl)
                                                    @php
                                                        $clTecDate = !empty($cl->entrega_tecamac)
                                                            ? $cl->entrega_tecamac
                                                            : $wo->entrega_tecamac;
                                                    @endphp
                                                    <div class="subcell-row date-cell" style="cursor: pointer;"
                                                        onclick="openDatePicker(this)">
                                                        <span class="date-display"
                                                            style="font-weight: 500;">{!! renderDateDisplayOrIcon($clTecDate) !!}</span>
                                                        <input type="date"
                                                            style="opacity:0; position:absolute; z-index:-1; width:1px; height:1px;"
                                                            value="{{ safeDateParse($clTecDate) }}"
                                                            onchange="handleDateChange(this, {{ $wo->id }}, 'entrega_tecamac', {{ $cl->id }})">
                                                    </div>
                                                @endforeach
                                            </div>
                                        </td>
                                    @else
                                        @php
                                            $singleTecDate = $uniqueTecDates->first() ?? $wo->entrega_tecamac;
                                        @endphp
                                        <td class="text-center p-0 date-cell" style="cursor: pointer;"
                                            onclick="openDatePicker(this)">
                                            <span class="date-display"
                                                style="font-weight: 500;">{!! renderDateDisplayOrIcon($singleTecDate) !!}</span>
                                            <input type="date"
                                                style="opacity:0; position:absolute; z-index:-1; width:1px; height:1px;"
                                                value="{{ safeDateParse($singleTecDate) }}"
                                                onchange="handleDateChange(this, {{ $wo->id }}, 'entrega_tecamac')">
                                        </td>
                                    @endif
                                @endif
                                <td class="text-center font-large font-bold cell-semana">
                                    {{ $wo->semana_entrega_cliente }}</td>
                                @php
                                    $classMexDates = $sortedClases
                                        ->map(function ($cl) use ($wo) {
                                            return !empty($cl->fecha_real)
                                                ? trim($cl->fecha_real)
                                                : (!empty($wo->fecha_real)
                                                    ? trim($wo->fecha_real)
                                                    : '');
                                        })
                                        ->filter();
                                    $uniqueMexDates = $classMexDates->unique();
                                @endphp
                                @if ($isReadOnly)
                                    @if ($sortedClases->count() > 0 && $uniqueMexDates->count() > 1)
                                        <td class="text-center p-0 cell-subcells"
                                            style="vertical-align: stretch; height: 1px;">
                                            <div class="subcell-container">
                                                @foreach ($sortedClases as $cl)
                                                    @php
                                                        $clMexDate = !empty($cl->fecha_real)
                                                            ? $cl->fecha_real
                                                            : $wo->fecha_real;
                                                    @endphp
                                                    <div class="subcell-row date-cell">
                                                        <span class="date-display"
                                                            style="font-weight: 600;">{!! renderDateDisplayOrIcon($clMexDate, true) !!}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </td>
                                    @else
                                        @php
                                            $singleMexDate = $uniqueMexDates->first() ?? $wo->fecha_real;
                                        @endphp
                                        <td class="text-center p-0 date-cell">
                                            <span class="date-display"
                                                style="font-weight: 600;">{!! renderDateDisplayOrIcon($singleMexDate, true) !!}</span>
                                        </td>
                                    @endif
                                @else
                                    @if ($sortedClases->count() > 0 && $uniqueMexDates->count() > 1)
                                        <td class="text-center p-0 cell-subcells"
                                            style="vertical-align: stretch; height: 1px;">
                                            <div class="subcell-container">
                                                @foreach ($sortedClases as $cl)
                                                    @php
                                                        $clMexDate = !empty($cl->fecha_real)
                                                            ? $cl->fecha_real
                                                            : $wo->fecha_real;
                                                    @endphp
                                                    <div class="subcell-row date-cell" style="cursor: pointer;"
                                                        onclick="openDatePicker(this)">
                                                        <span class="date-display"
                                                            style="font-weight: 500;">{!! renderDateDisplayOrIcon($clMexDate) !!}</span>
                                                        <input type="date"
                                                            style="opacity:0; position:absolute; z-index:-1; width:1px; height:1px;"
                                                            value="{{ safeDateParse($clMexDate) }}"
                                                            onchange="handleDateChange(this, {{ $wo->id }}, 'fecha_real', {{ $cl->id }})">
                                                    </div>
                                                @endforeach
                                            </div>
                                        </td>
                                    @else
                                        @php
                                            $singleMexDate = $uniqueMexDates->first() ?? $wo->fecha_real;
                                        @endphp
                                        <td class="text-center p-0 date-cell" style="cursor: pointer;"
                                            onclick="openDatePicker(this)">
                                            <span class="date-display"
                                                style="font-weight: 500;">{!! renderDateDisplayOrIcon($singleMexDate) !!}</span>
                                            <input type="date"
                                                style="opacity:0; position:absolute; z-index:-1; width:1px; height:1px;"
                                                value="{{ safeDateParse($singleMexDate) }}"
                                                onchange="handleDateChange(this, {{ $wo->id }}, 'fecha_real')">
                                        </td>
                                    @endif
                                @endif
                                <td class="text-center font-bold">
                                    {{ safeDateParseDisplay($wo->fecha_entrega_cliente) }}
                                </td>
                                @if (!$isReadOnly)
                                    <td class="text-center" contenteditable="true"
                                        onblur="autosaveField({{ $wo->id }}, 'observaciones_prioridad', this)">
                                        {!! renderEditableTextOrIcon($wo->observaciones_prioridad, false) !!}</td>
                                @else
                                    <td class="text-center">{!! renderEditableTextOrIcon($wo->observaciones_prioridad, true) !!}</td>
                                @endif
                                <td class="text-center no-print">
                                    <button type="button" class="btns blue priority-documents-button"
                                        data-priority-documents="{{ $wo->id }}"
                                        data-label="OT {{ $wo->id }} — {{ $wo->moldura->nombre ?? $wo->nombre_producto }}"
                                        @disabled(!in_array($userPerfil, ['1', '3', '4', '6'], true))>
                                        Ver archivos
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
</div>


@include('wo_views.partials.priorities.documents')
@include('wo_views.partials.priorities.scripts')
@endsection
