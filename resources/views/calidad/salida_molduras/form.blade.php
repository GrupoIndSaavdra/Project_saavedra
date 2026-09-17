@extends('layouts.appMenu')

@section('head')
    <title>Reporte {{ $reporte->ot_id }} — Salida de Molduras | GIS</title>
    <meta name="description" content="Registro de números de trazabilidad por moldura OT {{ $reporte->ot_id }}.">
    @vite([
        'resources/css/calidad_views/salida_molduras.css',
        'resources/js/calidad_views/salida_molduras.js'
    ])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@section('background-body', 'background-image:url("' . asset('images/fondoLogin.jpg') . '")')

@section('content')

    <div class="alm-wrapper">

        {{-- ═══ HEADER ═══ --}}
        <div class="alm-header">
            <div class="sm-header-center">
                <div class="alm-header-icon">
                    <img src="{{ asset('images/salidaMoldura.png') }}" alt="Salida de Molduras" class="sm-header-icon-img">
                </div>
                <div class="alm-header-text">
                    <h1>Salida de Molduras &mdash; OT {{ $reporte->ot_id }} {{ $reporte->nombre_moldura }}</h1>
                    <p>{{ $reporte->clase }} &mdash; Formato: {{ $reporte->formato === 'moldes' ? 'Moldes' : 'Bombillos' }}
                    </p>
                </div>
            </div>

            <div class="sm-action-right">
                @if(isset($ordenesTrabajo) && count($ordenesTrabajo) > 0)
                    @php
                        $otsGrouped = $ordenesTrabajo->groupBy('ot_id');
                        $currentOtClases = $otsGrouped->get($reporte->ot_id, collect());
                    @endphp

                    {{-- Filtro de O. T. --}}
                    <div class="sm-header-filter-group">
                        <span class="sm-header-badge-label">O. T.</span>
                        <select id="header-select-ot" class="sm-header-select-compact" title="Seleccionar Orden de Trabajo">
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
                    <div class="sm-header-filter-group" id="header-clase-container"
                        style="{{ $currentOtClases->count() > 1 ? '' : 'display: none;' }}">
                        <span class="sm-header-badge-label">Clase</span>
                        <select id="header-select-clase" class="sm-header-select-compact" title="Seleccionar Clase del reporte">
                            @foreach($currentOtClases as $claseItem)
                                <option
                                    value="{{ route('calidad.salida_molduras.show', ['ot' => $reporte->ot_id, 'clase' => $claseItem->clase_nombre]) }}"
                                    {{ $claseItem->clase_nombre == $reporte->clase ? 'selected' : '' }}>
                                    {{ $claseItem->clase_nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                {{-- Indicador dinámico de autoguardado --}}
                <div class="sm-save-indicator sm-save-indicator--idle" id="save-indicator">
                    <span class="sm-save-indicator__dot"></span>
                    <span id="save-status-text">Listo</span>
                </div>
            </div>
        </div>

        {{-- ═══ ENCABEZADO DEL REPORTE (Formato físico) ═══ --}}
        <div class="alm-table-card">
            @include('calidad.salida_molduras.partials._header', [
                'reporte' => $reporte,
                'ordenTrabajo' => $ordenTrabajo,
                'inspector' => $inspector,
            ])
        </div>

        {{-- ═══ GRID DE PIEZAS (dinámico según formato) ═══ --}}
        <div class="alm-table-card">
            <div class="alm-table-header sm-header-with-tools">
                <div class="sm-header-title-box">
                    <h2>
                        @if($reporte->formato === 'bombillos')
                            Registro de Piezas — Formato Bombillos (90° / LP)
                        @else
                            Registro de Piezas — Formato Moldes
                        @endif
                    </h2>
                </div>

                {{-- ═══ BARRA DE ACCIONES MASIVAS / RANGOS EN EL MISMO HEADER ═══ --}}
                <div class="sm-header-bulk-tools">
                    <div class="sm-bulk-filter-group">
                        <label for="sm-range-desde">Rango Desde:</label>
                        <input type="number" id="sm-range-desde" class="sm-bulk-input" placeholder="Ej. 1" min="1"
                            max="{{ $totalPiezas }}" autocomplete="off" {{ $reporte->enviado ? 'disabled' : '' }}>
                    </div>

                    <div class="sm-bulk-filter-group">
                        <label for="sm-range-hasta">Rango Hasta:</label>
                        <input type="number" id="sm-range-hasta" class="sm-bulk-input" placeholder="Ej. 20" min="1"
                            max="{{ $totalPiezas }}" autocomplete="off" {{ $reporte->enviado ? 'disabled' : '' }}>
                    </div>

                    <div class="sm-bulk-filter-group">
                        <label>Rango:</label>
                        <div class="sm-bulk-btn-row">
                            <button type="button" id="btn-apply-range-mark" class="sm-btn-bulk sm-btn-bulk--check"
                                title="Marcar rango como liberadas (✔)" {{ $reporte->enviado ? 'disabled' : '' }}>
                                ✔
                            </button>
                            <button type="button" id="btn-apply-range-unmark" class="sm-btn-bulk sm-btn-bulk--uncheck"
                                title="Desmarcar rango (✘)" {{ $reporte->enviado ? 'disabled' : '' }}>
                                ✘
                            </button>
                        </div>
                    </div>

                    <div class="sm-bulk-filter-group">
                        <label>Selección Global:</label>
                        <div class="sm-bulk-btn-row">
                            <button type="button" id="btn-select-all" class="sm-btn-bulk sm-btn-bulk--all"
                                title="Seleccionar todas las piezas" {{ $reporte->enviado ? 'disabled' : '' }}>
                                Seleccionar Todo
                            </button>
                            <button type="button" id="btn-deselect-all" class="sm-btn-bulk sm-btn-bulk--none"
                                title="Desmarcar todas las piezas" {{ $reporte->enviado ? 'disabled' : '' }}>
                                Desmarcar Todo
                            </button>
                        </div>
                    </div>
                </div>

                <span class="alm-results-count">{{ $totalPiezas }} pieza{{ $totalPiezas !== 1 ? 's' : '' }}</span>
            </div>

            @if($reporte->formato === 'bombillos')
                @include('calidad.salida_molduras.partials._form2', [
                    'celdas' => $celdas,
                    'totalPiezas' => $totalPiezas,
                    'reporte' => $reporte,
                ])
            @else
                @include('calidad.salida_molduras.partials._form1', [
                    'celdas' => $celdas,
                    'totalPiezas' => $totalPiezas,
                    'reporte' => $reporte,
                ])
            @endif
        </div>

        {{-- ═══ OBSERVACIONES ═══ --}}
        <div class="alm-table-card">
            <div class="alm-table-header">
                <h2>Observaciones</h2>
            </div>
            @include('calidad.salida_molduras.partials._observaciones', [
                'reporte' => $reporte,
            ])
        </div>

        {{-- ═══ ACCIONES: ENVÍO Y PDF ═══ --}}
        <div class="sm-actions-container" style="display: flex; gap: 30px; margin-bottom: 2.5em; align-items: center; justify-content: center; padding: 20px; background: transparent;">
            @if($reporte->enviado)
                <button type="button" class="sm-btn-unlock" id="btn-unlock-report" onclick="openMasterUnlockModal()"
                        style="background: #e2e8f0; color: #475569; border: 1px solid #cbd5e1; padding: 12px 24px; border-radius: 10px; font-weight: bold; cursor: pointer; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; transition: all 0.2s ease; box-shadow: 0 4px 6px rgba(0,0,0,0.05);"
                        onmouseover="this.style.background='#cbd5e1'; this.style.transform='translateY(-2px)';"
                        onmouseout="this.style.background='#e2e8f0'; this.style.transform='translateY(0)';">
                    <img src="{{ asset('images/error.png') }}" alt="Unlock" style="width: 32px; height: 32px; filter: grayscale(100%);">
                    <span>Desbloquear (Master)</span>
                </button>
            @endif

            <button type="button" class="sm-btn-action-custom sm-btn-action--green {{ $reporte->enviado ? 'disabled' : '' }}" 
                    id="btn-send-report" onclick="openSendReportModal()" {{ $reporte->enviado ? 'disabled' : '' }}>
                <img src="{{ asset('images/EnvioMoldes.png') }}" alt="Enviar Correo">
                <span>Enviar Moldura a la Raza</span>
            </button>
            
            <button type="button" class="sm-btn-action-custom sm-btn-action--blue" 
                    id="btn-generate-pdf" onclick="generateReportPdf()">
                @if($reporte->pdf_generado_count > 0)
                    <span class="sm-btn-badge">{{ $reporte->pdf_generado_count }}</span>
                @endif
                <img src="{{ asset('images/pdf-view.png') }}" alt="Generar PDF">
                <span>Generar Formato en PDF</span> 
            </button>
        </div>

        {{-- ═══ LOG DE AUDITORÍA ═══ --}}
        @include('calidad.salida_molduras.partials._log_panel', [
            'reporte' => $reporte,
        ])

    </div>{{-- /.alm-wrapper --}}

    {{-- MODAL DE DESBLOQUEO MASTER --}}
    <template id="template-master-unlock">
        <div style="text-align: left; margin-bottom: 15px;">
            <p style="font-size: 0.9em; color: #555;">Ingrese la contraseña de un usuario con privilegios Master o Administrador para habilitar nuevamente la edición de este reporte.</p>
            <input type="password" id="swal-master-password" class="swal2-input" placeholder="Contraseña Master">
        </div>
    </template>

    {{-- ═══ DATOS PARA JS ═══ --}}
    <script>
        window.routes = {
            ...(window.routes || {}),
            'calidad.salida_molduras.show': @json(route('calidad.salida_molduras.show', ['ot' => ':ot', 'clase' => ':clase'])),
            'calidad.salida_molduras.autosave': @json(route('calidad.salida_molduras.autosave')),
            'calidad.salida_molduras.observaciones': @json(route('calidad.salida_molduras.observaciones')),
            'calidad.salida_molduras.updateHeader': @json(route('calidad.salida_molduras.updateHeader')),
            'calidad.salida_molduras.log': @json(route('calidad.salida_molduras.log', ['id' => ':id'])),
            'calidad.salida_molduras.index': @json(route('calidad.salida_molduras.index')),
            'calidad.salida_molduras.bulk': @json(route('calidad.salida_molduras.bulk')),
            'calidad.salida_molduras.pdf': @json(route('calidad.salida_molduras.pdf', ['id' => ':id'])),
            'calidad.salida_molduras.send': @json(route('calidad.salida_molduras.send', ['id' => ':id'])),
            'calidad.salida_molduras.unlock': @json(route('calidad.salida_molduras.unlock', ['id' => ':id'])),
        };
        @if(isset($ordenesTrabajo))
            window.smOtClases = @json($ordenesTrabajo->groupBy('ot_id')->map(fn($group) => $group->pluck('clase_nombre')->unique()->values()));
        @endif
        window.smReporte = {
            id:          {{ $reporte->id }},
                formato: @json($reporte->formato),
                    totalPiezas: {{ $totalPiezas }},
        };
    </script>

@endsection