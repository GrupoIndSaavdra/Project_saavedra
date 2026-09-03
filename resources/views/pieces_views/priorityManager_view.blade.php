@extends('layouts.appMenu')

@section('head')
    <title>Prioridad de Órdenes de Trabajo</title>
    <link rel="icon" href="{{ url('images/lg_saavedra.png') }}?v=1">
    <script>
        window.baseUrl        = "{{ url('/') }}";
        window.savePrioritiesUrl = "{{ route('savePriorities') }}";
        window.csrfToken      = "{{ csrf_token() }}";
        window.otPriorities   = @json($otPriorities);
    </script>
    @vite([
        'resources/css/pieces_views/priorityManager_view.css',
        'resources/js/pieces_views/priorityManager_view.js'
    ])
@endsection

@section('background-body', 'background-image:url("' . asset("images/fondoLogin.jpg") . '")')

@section('content')
<div class="pm-page">

    {{-- ── Encabezado ─────────────────────────────────────────── --}}
    <div class="pm-header">
        <div class="pm-header-left">
            <div class="pm-header-icon-wrapper">
                <img src="{{ asset('images/priorizar.png') }}" class="pm-title-icon" alt="">
            </div>
            <div class="pm-header-titles">
                <h1 class="pm-title">Prioridad de Órdenes de Trabajo</h1>
                <p class="pm-subtitle">
                    Consulta del orden de prioridad en que aparecen las OTs en la vista de producción.
                </p>
            </div>
        </div>
    </div>

    {{-- ── Banner informativo ────────────────────────────────── --}}
    <div class="pm-info-banner">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/>
            <line x1="12" y1="8" x2="12" y2="12"/>
            <line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        <div class="pm-flex-col-100">
            <span class="pm-info-text-main">
                El orden mostrado aquí se administra desde el dashboard de <strong>Prioridades GIS</strong>. Las OTs se muestran de mayor a menor urgencia (de arriba hacia abajo).
            </span>
            <div class="pm-stats-row">
                <div class="pm-stat-item">
                    <img src="{{ asset('images/uno.png') }}" alt="Oro" class="pm-info-icon-small">
                    <span class="pm-info-text-small"><strong>1 al 5:</strong> Prioridad Máxima (Oro)</span>
                </div>
                <div class="pm-stat-item">
                    <img src="{{ asset('images/plata.png') }}" alt="Plata" class="pm-info-icon-small">
                    <span class="pm-info-text-small"><strong>6 en adelante:</strong> Prioridad Normal (Plata)</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Lista draggable (renderizada por JS) ─────────────── --}}
    <p class="pm-count-badge" id="pm-count-label">
        Cargando órdenes de trabajo&hellip;
    </p>
    <ul class="pm-list" id="pm-list" aria-label="Lista de Órdenes de Trabajo ordenables">
        {{-- Las tarjetas se generan en priorityManager_view.js --}}
    </ul>

</div>

{{-- Toast de resultado --}}
<div class="pm-toast" id="pm-toast" role="alert" aria-live="polite"></div>
@endsection
