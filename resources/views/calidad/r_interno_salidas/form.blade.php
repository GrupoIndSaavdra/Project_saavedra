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

    <div class="alm-wrapper">

        {{-- ═══ HEADER ═══ --}}
        <div class="alm-header">
            <div class="ri-header-center">
                <div class="alm-header-icon">
                    <img src="{{ asset('images/RInternoSalida.png') }}" alt="Salida de Molduras" class="ri-header-icon-img">
                </div>
                <div class="alm-header-text">
                    <h1 style="margin-bottom: 3px;">Reporte Interno de Salidas</h1>
                    <h3 style="color: #3e9c49; font-size: 1.2rem; margin-bottom: 5px; font-weight: 500;">OT
                        {{ $reporte->ot_id }} &mdash; {{ $reporte->nombre_moldura }}
                    </h3>
                    <p style="margin-bottom: 0; font-size: 1rem;">
                        <strong>Clase:</strong> {{ $reporte->clase }}
                        <span style="color: #999; margin: 0 5px;">|</span>
                        <strong>Formato Layout:</strong> {{ $reporte->formato === 'moldes' ? 'Moldes/Otros' : 'Bombillos' }}
                    </p>
                </div>
            </div>

            <div class="ri-action-right">
                @if(isset($ordenesTrabajo) && count($ordenesTrabajo) > 0)
                    @php
                        $otsGrouped = $ordenesTrabajo->groupBy('ot_id');
                        $currentOtClases = $otsGrouped->get($reporte->ot_id, collect());
                    @endphp

                    {{-- Filtro de O. T. --}}
                    <div class="ri-header-filter-group">
                        <span class="ri-header-badge-label">O. T.</span>
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

                    {{-- Chiquifiltro de Clase (visible si la OT actual o seleccionada tiene más de 1 clase) --}}
                    <div class="ri-header-filter-group" id="header-clase-container"
                        style="{{ $currentOtClases->count() > 1 ? '' : 'display: none;' }}">
                        <span class="ri-header-badge-label">Clase</span>
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

                {{-- Indicador dinámico de autoguardado --}}
                <div class="ri-save-indicator ri-save-indicator--idle" id="save-indicator">
                    <span class="ri-save-indicator__dot"></span>
                    <span id="save-status-text">Listo</span>
                </div>
            </div>
        </div>

        {{-- CONTENEDOR PRINCIPAL DEL REPORTE --}}
        <div class="alm-table-card"
            style="margin-bottom: 2.5em; border: 4px solid #033966; box-shadow: 0 4px 15px rgba(0, 80, 158, 0.15); background-color: rgba(255, 255, 255, 0.9);">

            {{-- ENCABEZADO DEL REPORTE (Formato físico) --}}
            @include('calidad.r_interno_salidas.partials._header', [
                'reporte' => $reporte,
                'ordenTrabajo' => $ordenTrabajo,
                'inspector' => $inspector,
            ])

            {{-- GRID DE PIEZAS (dinámico según formato) --}}
            <div class="alm-table-header ri-header-with-tools">
                <div class="ri-header-title-box">
                    <h2>
                        @if($reporte->formato === 'bombillos')
                            Registro de Piezas — Formato Bombillos (90° / LP)
                        @else
                            Registro de Piezas — Formato Moldes
                        @endif
                    </h2>
                </div>

                {{-- ═══ BARRA DE ACCIONES MASIVAS / RANGOS EN EL MISMO HEADER ═══ --}}
                <div class="ri-header-bulk-tools">
                    <div style="display: flex; flex-direction: column; justify-content: flex-end; padding-bottom: 2px;">
                        <span
                            style="font-size: 0.85rem; font-weight: 800; color: #fff; text-transform: uppercase; margin-right: 5px;">Rango:</span>
                    </div>

                    <div class="ri-bulk-filter-group">
                        <label for="ri-range-desde">Desde:</label>
                        <input type="number" id="ri-range-desde" class="ri-bulk-input" placeholder="Ej. 1" min="1"
                            max="{{ $totalPiezas }}" autocomplete="off">
                    </div>

                    <div class="ri-bulk-filter-group">
                        <label for="ri-range-hasta">Hasta:</label>
                        <input type="number" id="ri-range-hasta" class="ri-bulk-input" placeholder="Ej. 20" min="1"
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
                </div>

                <span class="alm-results-count">{{ $totalPiezas }} pieza{{ $totalPiezas !== 1 ? 's' : '' }}</span>
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

            {{-- OBSERVACIONES --}}
            <div class="alm-table-header">
                <h2>Observaciones</h2>
            </div>
            @include('calidad.r_interno_salidas.partials._observaciones', [
                'reporte' => $reporte,
            ])

            {{-- ACCIONES: ENVÍO Y PDF --}}
            <div class="ri-actions-container"
                style="display: flex; gap: 30px; margin-bottom: 10px; margin-top: 10px; align-items: center; justify-content: center; padding: 10px 20px 20px; background: transparent;">

                <button type="button" class="ri-btn-action-custom ri-btn-action--blue" id="btn-generate-pdf"
                    @if(!$hasChanges)
                        onclick="Swal.fire({icon: 'info', title: 'Sin cambios', text: 'No hay cambios nuevos desde el último PDF generado.', confirmButtonColor: '#033966'})"
                    style="min-width: 400px; justify-content: center; opacity: 0.6; cursor: not-allowed;" @else
                    onclick="generateReportPdf()" style="min-width: 400px; justify-content: center;" @endif>
                    @if($pdfs->count() > 0)
                        <span class="ri-btn-badge" id="pdf-badge-counter">{{ $pdfs->count() }}</span>
                    @else
                        <span class="ri-btn-badge" id="pdf-badge-counter" style="display: none;">0</span>
                    @endif
                    <img src="{{ asset('images/pdf-view.png') }}" alt="Generar PDF">
                    <span style="font-size: 1.1em; letter-spacing: 0.5px;">Generar Formato en PDF</span>
                </button>
            </div>

        </div> {{-- FIN DEL CONTENEDOR PRINCIPAL DEL REPORTE --}}

        {{-- TABLA DE VERSIONES DE PDF --}}
        <div class="alm-table-card" id="pdf-versions-container"
            style="margin-bottom: 2.5em; {{ $pdfs->count() > 0 ? '' : 'display: none;' }}">
            <div class="alm-table-header">
                <h2>
                    <img src="{{ asset('images/pdf-view.png') }}"
                        style="width:24px; vertical-align: middle; margin-right: 10px;" alt="PDFs">
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
                                    <td>{{ $pdf->creador ? $pdf->creador->nombre . ' ' . $pdf->creador->a_paterno : 'Sistema' }}
                                    </td>
                                    <td>{{ $pdf->created_at->format('d/m/Y H:i A') }}</td>
                                    <td>
                                        <a href="{{ route('calidad.r_interno_salidas.pdf.download', [$reporte->id, $pdf->id]) }}"
                                            class="ri-btn-action-custom ri-btn-action--blue"
                                            style="padding: 6px 12px; font-size: 0.85rem; text-decoration: none; display: inline-flex; justify-content: center; align-items: center; gap: 6px; width: auto; height: auto;">
                                            <i class="fa fa-download"></i> Descargar
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
                formato: @json($reporte->formato),
                    totalPiezas: {{ $totalPiezas }},
        };
    </script>

@endsection
