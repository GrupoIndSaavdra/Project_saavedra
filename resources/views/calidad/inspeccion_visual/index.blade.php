@extends('layouts.appMenu')

@section('head')
    <title>Reportes de Calidad — Inspección Volumétrica y Dimensional | GIS</title>
    <meta name="description" content="Reporte de inspección volumétrica y control dimensional de piezas. Grupo Industrial Saavedra.">
    @vite([
        'resources/css/calidad_views/inspeccion_visual.css',
        'resources/js/calidad_views/inspeccion_visual.js'
    ])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@section('background-body', 'background-image:url("' . asset('images/fondoLogin.jpg') . '")')

@section('content')

<div class="alm-wrapper">

    {{-- ═══ HEADER ═══ --}}
    <div class="alm-header">
        <div class="alm-header-left">
            <div class="alm-header-icon">
                <img src="{{ asset('images/reporte Volumetrico.png') }}" alt="Reporte Volumétrico" class="ri-header-icon-img">
            </div>
            <div class="alm-header-text">
                <h1>Reporte de Calidad — Reporte Volumétrico</h1>
                <p>Inspección y verificación volumétrica de piezas por orden de trabajo. Selecciona una OT para comenzar.</p>
            </div>
        </div>
        <div class="reportes-nav-tabs">
            <a href="{{ route('calidad.inspeccion_visual.index') }}" class="reporte-tab active" id="tab-visual">
                <img src="{{ asset('images/reporte Volumetrico.png') }}" alt="" class="tab-icon"> Reporte Volumétrico
            </a>
            <a href="{{ route('calidad.inspeccion_dimensional.index') }}" class="reporte-tab" id="tab-dimensional">
                <img src="{{ asset('images/reporte dimensional.png') }}" alt="" class="tab-icon"> Reporte Dimensional
            </a>
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
            <h2>Seleccionar Orden de Trabajo</h2>
            <span class="alm-results-count">Selecciona la OT y la clase para abrir el reporte volumétrico o dimensional</span>
        </div>

        <div class="ri-search-form-wrapper">
            <form id="form-abrir-reporte" class="ri-search-form" onsubmit="return false;">

                <div class="ri-form-group">
                    <label for="select-ot" class="ri-label">Orden de Trabajo (OT)</label>
                    <select id="select-ot" class="select-filter ri-select-ot" required>
                        <option value="">— Selecciona una OT —</option>
                        @foreach($ordenesTrabajo->unique('ot_id') as $ot)
                            <option value="{{ $ot->ot_id }}"
                                data-moldura="{{ $ot->nombre_moldura }}">
                                {{ $ot->ot_id }}
                                @if($ot->nombre_moldura) — {{ $ot->nombre_moldura }}@endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="ri-form-group" id="grupo-clase" style="display:none;">
                    <label for="select-clase" class="ri-label">Clase de la Moldura</label>
                    <select id="select-clase" class="select-filter" required>
                        <option value="">— Selecciona una clase —</option>
                    </select>
                </div>

                <div class="ri-form-group-actions" id="grupo-btn-abrir" style="display:none;">
                    <button type="button" id="btn-abrir-reporte" class="btn-abrir">
                        <img src="{{ asset('images/reporte Volumetrico.png') }}" alt="" class="btn-icon"> Abrir Reporte Volumétrico
                    </button>
                    <button type="button" id="btn-abrir-dimensional" class="btn-abrir btn-abrir-secondary">
                        <img src="{{ asset('images/reporte dimensional.png') }}" alt="" class="btn-icon"> Abrir Reporte Dimensional
                    </button>
                </div>

            </form>

            {{-- ═══ RESUMEN DE LA INFORMACIÓN ═══ --}}
            <div id="ot-info-box" class="ot-info-card" style="display:none;">
                <div class="ot-info-item ot-info-moldura">
                    <span class="ot-info-item-label">
                        <svg class="info-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                        Moldura
                    </span>
                    <span class="ot-info-item-val val-moldura" id="info-ot-moldura">—</span>
                </div>

                <div class="ot-info-metrics-group" id="info-metrics-group" style="display:none;">
                    <div class="ot-info-metric-card" id="card-pedido">
                        <span class="ot-metric-label">
                            <svg class="info-icon text-blue" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                            Pedido
                        </span>
                        <div class="ot-metric-value-wrap">
                            <span class="ot-metric-val val-pedido" id="info-ot-pedido">0</span>
                            <span class="ot-metric-unit">pzas</span>
                        </div>
                    </div>

                    <div class="ot-metric-operator" id="operator-plus" style="display:none;">+</div>

                    <div class="ot-info-metric-card" id="card-consignacion" style="display:none;">
                        <span class="ot-metric-label">
                            <svg class="info-icon text-amber" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                            Consignación
                        </span>
                        <div class="ot-metric-value-wrap">
                            <span class="ot-metric-val val-consignacion" id="info-ot-consignacion">0</span>
                            <span class="ot-metric-unit">pzas</span>
                        </div>
                    </div>

                    <div class="ot-metric-operator" id="operator-equals" style="display:none;">=</div>

                    <div class="ot-info-metric-card ot-metric-card--total" id="card-total">
                        <span class="ot-metric-label">
                            <svg class="info-icon text-emerald" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                            Total a Inspeccionar
                        </span>
                        <div class="ot-metric-value-wrap">
                            <span class="ot-metric-val val-total" id="info-ot-total">0</span>
                            <span class="ot-metric-unit">pzas</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══ CARD: REPORTES RECIENTES ═══ --}}
    @if($reportesExistentes->count() > 0)
    <div class="alm-table-card">
        <div class="alm-table-header">
            <h2>Reportes Registrados</h2>
            <span class="alm-results-count">
                {{ $reportesExistentes->count() }} reporte{{ $reportesExistentes->count() !== 1 ? 's' : '' }}
            </span>
        </div>

        <div class="alm-table-scroll">
            <table class="alm-table" id="tabla-reportes">
                <thead>
                    <tr>
                        <th>OT</th>
                        <th>Moldura</th>
                        <th>Clase</th>
                        <th>Inspector</th>
                        <th>Fecha</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reportesExistentes as $rep)
                    <tr>
                        <td><strong>{{ $rep->ot_id }}</strong></td>
                        <td>{{ $rep->nombre_moldura }}</td>
                        <td><span class="badge-clase">{{ $rep->clase }}</span></td>
                        <td>{{ $rep->inspector_nombre_completo }}</td>
                        <td>{{ $rep->fecha_elaboracion ? $rep->fecha_elaboracion->format('d/m/Y') : ($rep->created_at ? $rep->created_at->format('d/m/Y') : '—') }}</td>
                        <td>
                            <a href="{{ route('calidad.inspeccion_visual.show', ['ot' => $rep->ot_id, 'clase' => $rep->clase]) }}" class="btn-ver-reporte">
                                <img src="{{ asset('images/reporte Volumetrico.png') }}" width="16" height="16" style="object-fit: contain;"> Ver Reporte Volumétrico
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>

{{-- Pasamos los datos de las OTs y sus clases a JavaScript --}}
<script>
    window.otClasesData = @json($ordenesTrabajo);
    window.routes = {
        ...(window.routes || {}),
        'calidad.inspeccion_visual.index': @json(route('calidad.inspeccion_visual.index')),
        'calidad.inspeccion_visual.show':  @json(route('calidad.inspeccion_visual.show', ['ot' => ':ot', 'clase' => ':clase'])),
        'calidad.inspeccion_dimensional.index': @json(route('calidad.inspeccion_dimensional.index')),
        'calidad.inspeccion_dimensional.show':  @json(route('calidad.inspeccion_dimensional.show', ['ot' => ':ot', 'clase' => ':clase']))
    };
</script>

@endsection
