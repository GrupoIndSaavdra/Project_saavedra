@extends('layouts.appMenu')

@section('head')
    <title>Salida de Molduras — Calidad Fundición | GIS</title>
    <meta name="description" content="Registro de trazabilidad de piezas enviadas al área RAZA. Grupo Industrial Saavedra.">
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
        <div class="alm-header-icon">
            <img src="{{ asset('images/salidaMoldura.png') }}" alt="Salida de Molduras" class="sm-header-icon-img">
        </div>
        <div class="alm-header-text">
            <h1>Salida de Molduras — Calidad Fundición</h1>
            <p>Registro de trazabilidad de piezas enviadas al área RAZA. Selecciona una OT y clase para abrir o crear un reporte.</p>
        </div>
        <div class="sm-codigo-box">
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

        <div class="sm-search-form-wrapper">
            <form id="form-abrir-reporte" class="sm-search-form" onsubmit="return false;">

                <div class="sm-form-group">
                    <label for="select-ot" class="sm-label">Orden de Trabajo (OT)</label>
                    <select id="select-ot" class="select-filter sm-select-ot" required>
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

                <div class="sm-form-group" id="grupo-clase" style="display:none;">
                    <label for="select-clase" class="sm-label">Clase</label>
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
                        <th>OT</th>
                        <th>Moldura</th>
                        <th>Clase</th>
                        <th>Formato</th>
                        <th>Inspector</th>
                        <th>Fecha Inicio</th>
                        <th class="d-text-center">Total Piezas</th>
                        <th class="d-text-center">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reportesExistentes as $rep)
                    <tr>
                        <td><div class="alm-ot-label">{{ $rep->ot_id }}</div></td>
                        <td>{{ $rep->nombre_moldura }}</td>
                        <td><div class="alm-clase-label">{{ $rep->clase }}</div></td>
                        <td>
                            @if($rep->formato === 'moldes')
                                <span class="badge-tipo-dibujo">Moldes</span>
                            @else
                                <span class="badge-tipo-ayuda">Bombillos</span>
                            @endif
                        </td>
                        <td>{{ $rep->inspector?->nombre ?? '—' }}</td>
                        <td>
                            @if($rep->fecha_inicio)
                                {{ \Carbon\Carbon::parse($rep->fecha_inicio)->format('d/m/Y') }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="d-text-center">
                            <span class="badge-pdf-count">{{ $rep->total_piezas }}</span>
                        </td>
                        <td class="d-text-center">
                            <a href="{{ route('calidad.salida_molduras.show', [
                                'ot'    => $rep->ot_id,
                                'clase' => urlencode($rep->clase)
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
        'calidad.salida_molduras.index': @json(route('calidad.salida_molduras.index')),
        'calidad.salida_molduras.show':  @json(route('calidad.salida_molduras.show', ['ot' => ':ot', 'clase' => ':clase'])),
    };
    window.smOtClases = @json(
        $ordenesTrabajo->groupBy('ot_id')->map(fn($items) =>
            $items->pluck('clase_nombre')->unique()->values()
        )
    );
</script>

@endsection
