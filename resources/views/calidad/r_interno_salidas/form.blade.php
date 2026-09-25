@extends('layouts.appMenu')

@section('head')
    <title>Reporte {{ $reporte->ot_id }} — Reporte Interno de Salidas | GIS</title>
    <meta name="description" content="Registro de números de trazabilidad por moldura OT {{ $reporte->ot_id }}.">
    @vite([
        'resources/css/calidad_views/r_interno_salidas.css',
        'resources/js/calidad_views/r_interno_salidas.js'
    ])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@section('background-body', 'background-image:url("' . asset('images/fondoLogin.jpg') . '")')

@section('content')

<div class="alm-wrapper rd-page-wrapper">

    {{-- ═══ BARRA SUPERIOR DE ACCIONES ═══ --}}
    <div class="rd-top-bar">
        <div class="rd-top-left">
            <a href="{{ route('calidad.r_interno_salidas.index') }}" class="btn-regresar">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                Regresar al Selector
            </a>
            <span class="rd-title-tag">
                <img src="{{ asset('images/RInternoSalida.png') }}" width="20" height="20" alt="Reporte Interno de Salidas">
                <span>Reporte Interno de Salidas</span>
            </span>
            <span class="rd-ot-pill">OT #{{ $reporte->ot_id }} — {{ $reporte->nombre_moldura }} ({{ $reporte->clase }})</span>

            @if(isset($ordenesTrabajo) && count($ordenesTrabajo) > 0)
                @php
                    $otsGrouped = $ordenesTrabajo->groupBy('ot_id');
                    $currentOtClases = $otsGrouped->get($reporte->ot_id, collect());
                @endphp

                {{-- Filtro de O. T. --}}
                <div class="ri-header-filter-group">
                    <span class="ri-header-badge-label">OT:</span>
                    <select id="header-select-ot" class="ri-header-select-compact" title="Seleccionar Orden de Trabajo">
                        @foreach($otsGrouped as $otId => $clasesCol)
                            @php
                                $firstItem = $clasesCol->first();
                                $isOtSelected = ($otId == $reporte->ot_id);
                            @endphp
                            <option value="{{ $otId }}" {{ $isOtSelected ? 'selected' : '' }}>
                                OT {{ $otId }} &mdash; {{ $firstItem->nombre_moldura }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filtro de Clase --}}
                <div class="ri-header-filter-group" id="header-clase-container"
                    style="{{ $currentOtClases->count() > 1 ? '' : 'display: none;' }}">
                    <span class="ri-header-badge-label">Clase:</span>
                    <select id="header-select-clase" class="ri-header-select-compact" title="Seleccionar Clase del reporte">
                        @foreach($currentOtClases as $claseItem)
                            <option
                                value="{{ route('calidad.r_interno_salidas.show', ['ot' => $reporte->ot_id, 'clase' => $claseItem->clase_nombre]) }}"
                                {{ $claseItem->clase_nombre == $reporte->clase ? 'selected' : '' }}>
                                {{ $claseItem->clase_nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>

        <div class="rd-top-right">
            {{-- Indicador dinámico de autoguardado --}}
            <div class="rd-autosave-indicator ri-save-indicator ri-save-indicator--idle" id="save-indicator">
                <span class="rd-status-dot ri-save-indicator__dot"></span>
                <span class="rd-status-text" id="save-status-text">Listo</span>
            </div>
        </div>
    </div>

    {{-- ═══ FORMATO INSTITUCIONAL DE CONTROL (HOJA OFICIAL) ═══ --}}
    <div class="rd-sheet-container">

        {{-- ENCABEZADO DEL REPORTE (Formato institucional) --}}
        @include('calidad.r_interno_salidas.partials._header', [
            'reporte' => $reporte,
            'ordenTrabajo' => $ordenTrabajo,
            'inspector' => $inspector,
        ])

        {{-- ── CUERPO PRINCIPAL: TABLA DE PIEZAS ── --}}
        <div class="rd-table-wrapper">
            <div class="rd-table-bar ri-header-with-tools">
                <div class="ri-header-title-box">
                    <h3 class="rd-table-title">
                        @if($reporte->formato === 'bombillos')
                            REGISTRO DE PIEZAS — FORMATO BOMBILLOS (90° / LP)
                        @else
                            REGISTRO DE PIEZAS — FORMATO MOLDES
                        @endif
                    </h3>
                </div>

                {{-- ═══ BARRA DE ACCIONES MASIVAS / RANGOS EN EL MISMO HEADER ═══ --}}
                <div class="ri-header-bulk-tools">
                    <div class="ri-bulk-filter-group">
                        <label for="ri-range-desde">Desde:</label>
                        <input type="number" id="ri-range-desde" class="ri-bulk-input" placeholder="1" min="1"
                            max="{{ $totalPiezas }}" autocomplete="off">
                    </div>

                    <div class="ri-bulk-filter-group">
                        <label for="ri-range-hasta">Hasta:</label>
                        <input type="number" id="ri-range-hasta" class="ri-bulk-input" placeholder="20" min="1"
                            max="{{ $totalPiezas }}" autocomplete="off">
                    </div>

                    <div class="ri-bulk-filter-group">
                        <label>Estado:</label>
                        <div class="ri-bulk-btn-row">
                            <button type="button" id="btn-apply-range-mark" class="ri-btn-bulk ri-btn-bulk--check"
                                title="Marcar rango como liberadas (✔)">
                                ✔
                            </button>
                            <button type="button" id="btn-apply-range-unmark" class="ri-btn-bulk ri-btn-bulk--uncheck"
                                title="Desmarcar rango (✘)">
                                ✘
                            </button>
                            <button type="button" id="btn-apply-range-none" class="ri-btn-bulk ri-btn-bulk--none"
                                title="Estado Ninguno (—)">
                                —
                            </button>
                        </div>
                    </div>

                    <div class="ri-bulk-filter-group">
                        <label>Selección Global:</label>
                        <div class="ri-bulk-btn-row">
                            <button type="button" id="btn-select-all" class="ri-btn-bulk ri-btn-bulk--all"
                                title="Seleccionar todas las piezas">
                                Liberar Todo
                            </button>
                            <button type="button" id="btn-deselect-all" class="ri-btn-bulk ri-btn-bulk--uncheck"
                                title="Desmarcar todas las piezas">
                                Rechazar Todo
                            </button>
                            <button type="button" id="btn-none-all" class="ri-btn-bulk ri-btn-bulk--none"
                                title="Poner todas en Ninguno">
                                Ninguno Todo
                            </button>
                        </div>
                    </div>

                    <span class="alm-results-count">{{ $totalPiezas }} pieza{{ $totalPiezas !== 1 ? 's' : '' }}</span>
                </div>
            </div>

            @if($reporte->formato === 'bombillos')
                @include('calidad.r_interno_salidas.partials._form2', [
                    'celdas' => $celdas,
                    'totalPiezas' => $totalPiezas,
                    'reporte' => $reporte,
                ])
            @else
                @include('calidad.r_interno_salidas.partials._form1', [
                    'celdas' => $celdas,
                    'totalPiezas' => $totalPiezas,
                    'reporte' => $reporte,
                ])
            @endif
        </div>

        {{-- OBSERVACIONES GENERALES --}}
        @include('calidad.r_interno_salidas.partials._observaciones', [
            'reporte' => $reporte,
        ])

        {{-- ── Botón de Acción Generar PDF ── --}}
        <div class="ri-actions-container">
            <button type="button" class="ri-btn-action-custom ri-btn-action--blue" id="btn-generate-pdf"
                @if(!$hasChanges)
                    onclick="Swal.fire({icon: 'info', title: 'Sin cambios', text: 'No hay cambios nuevos desde el último PDF generado.', confirmButtonColor: '#033966'})"
                    style="opacity: 0.6; cursor: not-allowed;" @else
                    onclick="generateReportPdf()" @endif>
                @if($pdfs->count() > 0)
                    <span class="ri-btn-badge" id="pdf-badge-counter">{{ $pdfs->count() }}</span>
                @else
                    <span class="ri-btn-badge" id="pdf-badge-counter" style="display: none;">0</span>
                @endif
                <img src="{{ asset('images/pdf-view.png') }}" alt="Generar PDF">
                <span>Generar Formato en PDF</span>
            </button>
        </div>

    </div> {{-- FIN DEL RD-SHEET-CONTAINER --}}

    {{-- TABLA DE VERSIONES DE PDF --}}
    <div class="alm-table-card" id="pdf-versions-container"
        style="margin-top: 1.5rem; margin-bottom: 2rem; {{ $pdfs->count() > 0 ? '' : 'display: none;' }}">
        <div class="alm-table-header">
            <h2>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 8px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                Historial de Versiones Generadas
            </h2>
        </div>
        <div style="padding: 15px;">
            <div class="alm-table-scroll">
                <table class="alm-table" style="width: 100%; text-align: center;">
                    <thead>
                        <tr>
                            <th>Versión</th>
                            <th>Nombre de Archivo</th>
                            <th>Generado Por</th>
                            <th>Fecha y Hora</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody id="pdf-versions-tbody">
                        @foreach($pdfs as $pdf)
                            <tr>
                                <td style="font-weight: bold; color: #d32f2f;">V{{ $pdf->version }}</td>
                                <td>{{ $pdf->nombre_archivo }}</td>
                                <td>{{ $pdf->creador ? $pdf->creador->nombre . ' ' . $pdf->creador->a_paterno : 'Sistema' }}</td>
                                <td>{{ $pdf->created_at->format('d/m/Y H:i A') }}</td>
                                <td>
                                    <a href="{{ route('calidad.r_interno_salidas.pdf.download', [$reporte->id, $pdf->id]) }}"
                                        class="btn-file-download"
                                        style="padding: 6px 14px; font-size: 0.82rem; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                        Descargar
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ═══ LOG DE AUDITORÍA ═══ --}}
    @include('calidad.r_interno_salidas.partials._log_panel', [
        'reporte' => $reporte,
    ])

</div>{{-- /.alm-wrapper --}}

{{-- ═══ DATOS PARA JS ═══ --}}
<script>
    window.routes = {
        ...(window.routes || {}),
        'calidad.r_interno_salidas.show': @json(route('calidad.r_interno_salidas.show', ['ot' => ':ot', 'clase' => ':clase'])),
        'calidad.r_interno_salidas.autosave': @json(route('calidad.r_interno_salidas.autosave')),
        'calidad.r_interno_salidas.observaciones': @json(route('calidad.r_interno_salidas.observaciones')),
        'calidad.r_interno_salidas.updateHeader': @json(route('calidad.r_interno_salidas.updateHeader')),
        'calidad.r_interno_salidas.log': @json(route('calidad.r_interno_salidas.log', ['id' => ':id'])),
        'calidad.r_interno_salidas.index': @json(route('calidad.r_interno_salidas.index')),
        'calidad.r_interno_salidas.bulk': @json(route('calidad.r_interno_salidas.bulk')),
        'calidad.r_interno_salidas.pdf': @json(route('calidad.r_interno_salidas.pdf', ['id' => ':id'])),
    };
    @if(isset($ordenesTrabajo))
        window.riOtClases = @json($ordenesTrabajo->groupBy('ot_id')->map(fn($group) => $group->pluck('clase_nombre')->unique()->values()));
    @endif
    window.riReporte = {
        id:          {{ $reporte->id }},
        formato:     @json($reporte->formato),
        totalPiezas: {{ $totalPiezas }},
    };
</script>

@endsection
