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
                    Arrastra las OTs para definir el orden en que aparecen en la vista de producción.
                </p>
            </div>
        </div>
        <button id="pm-save-btn" class="pm-save-btn" disabled>
            <span class="pm-spinner"></span>
            <span class="pm-btn-text">Autoguardado Activo</span>
        </button>
    </div>

    {{-- ── Banner informativo ────────────────────────────────── --}}
    <div class="pm-info-banner">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/>
            <line x1="12" y1="8" x2="12" y2="12"/>
            <line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        <div style="display: flex; flex-direction: column; gap: 14px; width: 100%;">
            <span class="pm-info-text-main">
                El orden definido aquí se reflejará en la vista <strong>Orden de Trabajo en Progreso</strong>
                en el próximo refresco. Las OTs se ordenan de mayor a menor urgencia (de arriba hacia abajo).
            </span>
            <div style="display: flex; flex-wrap: wrap; gap: 32px; align-items: center; justify-content: center; margin-top: 4px; padding-top: 14px; border-top: 1px solid rgba(255,255,255,0.1);">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <img src="{{ asset('images/uno.png') }}" alt="Oro" class="pm-info-icon-small">
                    <span class="pm-info-text-small"><strong>1 al 5:</strong> Prioridad Máxima (Oro)</span>
                </div>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <img src="{{ asset('images/plata.png') }}" alt="Plata" class="pm-info-icon-small">
                    <span class="pm-info-text-small"><strong>6 en adelante:</strong> Prioridad Normal (Plata)</span>
                </div>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <svg xmlns="http://www.w3.org/2000/svg" class="pm-info-icon-small pm-svg-drag" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="9" cy="5" r="1" fill="currentColor" stroke="none"></circle>
                        <circle cx="9" cy="12" r="1" fill="currentColor" stroke="none"></circle>
                        <circle cx="9" cy="19" r="1" fill="currentColor" stroke="none"></circle>
                        <circle cx="15" cy="5" r="1" fill="currentColor" stroke="none"></circle>
                        <circle cx="15" cy="12" r="1" fill="currentColor" stroke="none"></circle>
                        <circle cx="15" cy="19" r="1" fill="currentColor" stroke="none"></circle>
                    </svg>
                    <span class="pm-info-text-small"><strong>Mango lateral:</strong> Arrastra para reordenar</span>
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
