@extends('layouts.appMenu')

@section('head')
    <title>Reporte Interno de Salidas — Calidad Fundición | GIS</title>
    <meta name="description" content="Registro interno de trazabilidad de piezas. Grupo Industrial Saavedra.">
    @vite([
        'resources/css/calidad_views/r_interno_salidas.css',
        'resources/js/calidad_views/r_interno_salidas.js'
    ])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@section('background-body', 'background-image:url("' . asset('images/fondoLogin.jpg') . '")')

@section('content')

<div class="alm-wrapper">

    {{-- ═══ HEADER ═══ --}}
    <div class="alm-header">
        <div class="alm-header-icon">
            <img src="{{ asset('images/RInternoSalida.png') }}" alt="Salida de Molduras" class="ri-header-icon-img">
        </div>
        <div class="alm-header-text">
            <h1>Reporte Interno de Salidas — Calidad Fundición</h1>
            <p>Registro interno de trazabilidad de piezas. Selecciona una OT y clase para abrir o crear un reporte.</p>
        </div>
        <div class="ri-codigo-box">
            <span>Código: <strong>F PRO CPT</strong></span>
            <span>Versión: <strong>6</strong></span>
            <span>Fecha Rev: <strong>22/04/2026</strong></span>
        </div>
    </div>

    {{-- ═══ MENSAJES DE SESIÓN ═══ --}}
    @if(session('success'))
        <div class="toast toast-success fade-in">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="toast toast-danger fade-in">{{ session('error') }}</div>
    @endif

    {{-- ═══ CARD: BUSCAR / ABRIR REPORTE ═══ --}}
    <div class="alm-table-card">
        <div class="alm-table-header">
            <h2>Abrir Reporte</h2>
            <span class="alm-results-count">Selecciona la OT y la clase para abrir o crear un reporte</span>
        </div>

        <div class="ri-search-form-wrapper">
            <form id="form-abrir-reporte" class="ri-search-form" onsubmit="return false;">

                <div class="ri-form-group">
                    <label for="select-ot" class="ri-label">Orden de Trabajo (OT)</label>
                    <select id="select-ot" class="select-filter ri-select-ot" required>
                        <option value="">— Selecciona una OT —</option>
                        @foreach($ordenesTrabajo->unique('ot_id') as $ot)
                            <option value="{{ $ot->ot_id }}">
                                {{ $ot->ot_id }}
                                @if($ot->nombre_moldura) — {{ $ot->nombre_moldura }}@endif
                                @if($ot->cliente) ({{ $ot->cliente }})@endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="ri-form-group" id="grupo-clase" style="display:none;">
                    <label for="select-clase" class="ri-label">Clase</label>
                    <select id="select-clase" class="select-filter" required>
                        <option value="">— Selecciona una clase —</option>
                    </select>
                </div>

            </form>
        </div>
    </div>

    {{-- ═══ CARD: REPORTES RECIENTES ═══ --}}
    @if($reportesExistentes->count() > 0)
    <div class="alm-table-card">
        <div class="alm-table-header">
            <h2>Reportes Existentes</h2>
            <span class="alm-results-count">
                {{ $reportesExistentes->count() }} reporte{{ $reportesExistentes->count() !== 1 ? 's' : '' }}
            </span>
        </div>

        <div class="alm-table-scroll">
            <table class="alm-table" id="tabla-reportes">
                <thead>
                    <tr>
                        <th class="sortable" data-type="number" style="cursor: pointer; user-select: none;" title="Ordenar por OT">
                            <div style="display: flex; align-items: center; justify-content: center; gap: 5px;">
                                OT <img src="{{ asset('images/Sin_Orden.png') }}" class="sort-icon" width="14" height="14">
                            </div>
                        </th>
                        <th class="sortable" data-type="string" style="cursor: pointer; user-select: none;" title="Ordenar por Moldura">
                            <div style="display: flex; align-items: center; justify-content: center; gap: 5px;">
                                Moldura <img src="{{ asset('images/Sin_Orden.png') }}" class="sort-icon" width="14" height="14">
                            </div>
                        </th>
                        <th class="sortable" data-type="string" style="cursor: pointer; user-select: none;" title="Ordenar por Clase">
                            <div style="display: flex; align-items: center; justify-content: center; gap: 5px;">
                                Clase <img src="{{ asset('images/Sin_Orden.png') }}" class="sort-icon" width="14" height="14">
                            </div>
                        </th>
                        <th class="sortable" data-type="string" style="cursor: pointer; user-select: none;" title="Ordenar por Formato">
                            <div style="display: flex; align-items: center; justify-content: center; gap: 5px;">
                                Formato <img src="{{ asset('images/Sin_Orden.png') }}" class="sort-icon" width="14" height="14">
                            </div>
                        </th>
                        <th class="sortable" data-type="string" style="cursor: pointer; user-select: none;" title="Ordenar por Inspector">
                            <div style="display: flex; align-items: center; justify-content: center; gap: 5px;">
                                Inspector <img src="{{ asset('images/Sin_Orden.png') }}" class="sort-icon" width="14" height="14">
                            </div>
                        </th>
                        <th class="sortable" data-type="string" style="cursor: pointer; user-select: none;" title="Ordenar por Fecha Inicio">
                            <div style="display: flex; align-items: center; justify-content: center; gap: 5px;">
                                Fecha Inicio <img src="{{ asset('images/Sin_Orden.png') }}" class="sort-icon" width="14" height="14">
                            </div>
                        </th>
                        <th class="sortable d-text-center" data-type="number" style="cursor: pointer; user-select: none;" title="Ordenar por Piezas">
                            <div style="display: flex; align-items: center; justify-content: center; gap: 5px;">
                                Total Piezas <img src="{{ asset('images/Sin_Orden.png') }}" class="sort-icon" width="14" height="14">
                            </div>
                        </th>
                        <th class="d-text-center">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reportesExistentes as $rep)
                    <tr>
                        <td data-sort-value="{{ $rep->ot_id }}"><div class="alm-ot-label">{{ $rep->ot_id }}</div></td>
                        <td data-sort-value="{{ $rep->nombre_moldura }}">{{ $rep->nombre_moldura }}</td>
                        <td data-sort-value="{{ implode(', ', $rep->clases) }}">
                            <div style="display: flex; flex-direction: column; gap: 4px; align-items: center;">
                                @foreach($rep->clases as $clase)
                                    <div class="alm-clase-label">{{ $clase }}</div>
                                @endforeach
                            </div>
                        </td>
                        <td data-sort-value="{{ implode(', ', $rep->formatos) }}">
                            <div style="display: flex; flex-direction: column; gap: 4px; align-items: center;">
                                @foreach($rep->formatos as $formato)
                                    @if($formato === 'moldes')
                                        <span class="badge-tipo-dibujo">Moldes</span>
                                    @else
                                        <span class="badge-tipo-ayuda">Bombillos</span>
                                    @endif
                                @endforeach
                            </div>
                        </td>
                        <td data-sort-value="{{ $rep->inspector?->nombre ?? '' }}">{{ $rep->inspector?->nombre ?? '—' }}</td>
                        <td data-sort-value="{{ $rep->fecha_inicio ?? '9999-99-99' }}">
                            @if($rep->fecha_inicio)
                                {{ \Carbon\Carbon::parse($rep->fecha_inicio)->format('d/m/Y') }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="d-text-center" data-sort-value="{{ $rep->total_piezas }}">
                            <span class="badge-pdf-count">{{ $rep->total_piezas }}</span>
                        </td>
                        <td class="d-text-center" data-sort-value="0">
                            <a href="{{ route('calidad.r_interno_salidas.show', [
                                'ot'    => $rep->ot_id,
                                'clase' => $rep->primer_clase
                            ]) }}" class="btn-toggle-files">
                                Abrir
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>{{-- /.alm-wrapper --}}

{{-- Variables para JS --}}
<script>
    window.routes = {
        ...(window.routes || {}),
        'calidad.r_interno_salidas.index': @json(route('calidad.r_interno_salidas.index')),
        'calidad.r_interno_salidas.show':  @json(route('calidad.r_interno_salidas.show', ['ot' => ':ot', 'clase' => ':clase'])),
    };
    window.riOtClases = @json(
        $ordenesTrabajo->groupBy('ot_id')->map(fn($items) =>
            $items->pluck('clase_nombre')->unique()->values()
        )
    );

    document.addEventListener('DOMContentLoaded', () => {
        const table = document.getElementById('tabla-reportes');
        if (!table) return;

        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        
        // Guardar orden original para el estado "sin orden"
        rows.forEach((row, index) => row.setAttribute('data-original-index', index));

        const headers = table.querySelectorAll('th.sortable');
        
        const iconSin = '{{ asset("images/Sin_Orden.png") }}';
        const iconAsc = '{{ asset("images/Orden_Asc.png") }}';
        const iconDesc = '{{ asset("images/Orden_Desc.png") }}';

        headers.forEach((th, colIndex) => {
            // Estado inicial: 0 = sin orden, 1 = asc, 2 = desc
            th.dataset.order = 0; 

            th.addEventListener('click', () => {
                // Resetear el ícono y estado de todos los demás encabezados
                headers.forEach((otherTh) => {
                    if (otherTh !== th) {
                        otherTh.dataset.order = 0;
                        const img = otherTh.querySelector('.sort-icon');
                        if(img) img.src = iconSin;
                    }
                });

                // Cambiar estado actual: 0 -> 1 -> 2 -> 0
                let order = (parseInt(th.dataset.order) + 1) % 3;
                th.dataset.order = order;

                const img = th.querySelector('.sort-icon');
                const type = th.dataset.type || 'string';

                if (order === 0) {
                    if(img) img.src = iconSin;
                    // Restaurar orden original
                    rows.sort((a, b) => parseInt(a.dataset.originalIndex) - parseInt(b.dataset.originalIndex));
                } else {
                    if(img) img.src = order === 1 ? iconAsc : iconDesc;
                    
                    rows.sort((a, b) => {
                        const aCell = a.cells[colIndex];
                        const bCell = b.cells[colIndex];
                        
                        let aVal = aCell.dataset.sortValue || aCell.textContent.trim();
                        let bVal = bCell.dataset.sortValue || bCell.textContent.trim();

                        if (type === 'number') {
                            aVal = parseFloat(aVal) || 0;
                            bVal = parseFloat(bVal) || 0;
                            return order === 1 ? aVal - bVal : bVal - aVal;
                        } else {
                            aVal = aVal.toLowerCase();
                            bVal = bVal.toLowerCase();
                            if (aVal < bVal) return order === 1 ? -1 : 1;
                            if (aVal > bVal) return order === 1 ? 1 : -1;
                            return 0;
                        }
                    });
                }

                // Volver a insertar las filas ordenadas al tbody
                rows.forEach(row => tbody.appendChild(row));
            });
        });
    });
</script>

@endsection
