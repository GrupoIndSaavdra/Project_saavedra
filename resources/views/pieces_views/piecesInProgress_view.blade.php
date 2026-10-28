@extends('layouts.appMenu')

@section('head')
    <title>Progreso de OT</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('images/logos/favicon.ico') }}?v=3">
    <script>
        window.cerrarImgUrl = "{{ asset('images/Color_Icons/Generales/cerrar.png') }}";
        window.baseUrl = "{{ url('/') }}";
        window.ptaCardsData = @json($ptaCardsData ?? []);
        window.ptaResultsBaseUrl = "{{ url('admin/pta/results') }}";
    </script>
    @vite(['resources/css/pieces_views/piecesInProgress_view.css', 'resources/js/pieces_views/piecesInProgress_view.js'])
@endsection

@section('background-body', 'background-image:url("' . asset('images/fondos/fondoLogin.jpg') . '")')

@section('content')
    @if (session('finishOrder'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const finishOrderData = @json(session('finishOrder'));
                const tipo = Array.isArray(finishOrderData) ? finishOrderData[0] : 'info';
                const msg = Array.isArray(finishOrderData) ? finishOrderData[1] : String(finishOrderData);
                if (typeof window.gisAlert === 'function') {
                    window.gisAlert({
                        title: tipo === 'error' ? 'Completar piezas requeridas' : 'Pedido finalizado',
                        text: msg,
                        confirmButtonText: 'Entendido',
                        confirmButtonColor: '#033966'
                    });
                }
            });
        </script>
    @endif

    {{-- Barra de Navegación Inferior Acostada con Pestañita Desplegable y Carga de OTs --}}
    <div class="bottom-nav-dock-wrapper" id="bottom-nav-dock-wrapper">
        {{-- Pestañita desplegable (Tab Handle) para abrir / cerrar --}}
        <button type="button" id="btn-toggle-nav-dock" class="nav-dock-tab-handle"
            title="Mostrar / Ocultar controles de navegación">
            <span class="tab-handle-icon" id="tab-handle-icon">▼</span>
            <span class="tab-handle-text">Controles de OT</span>
            <span class="tab-handle-badge" id="tab-handle-counter">OT 1 / {{ count($orderedOtIds) }}</span>
        </button>

        {{-- Barra de controles horizontal acostada --}}
        <div class="bottom-nav-dock-bar" id="bottom-nav-dock-bar">
            {{-- Ir a la primera OT (AtRa) --}}
            <button type="button" id="btn-scroll-top" class="nav-bar-btn" title="Ir al inicio de la página (Primera OT)">
                <img src="{{ asset('images/Color_Icons/Generales/AtRa_Icon.png') }}" class="nav-btn-icon icon-normal"
                    alt="Inicio">
                <img src="{{ asset('images/Light_Icons/AtRa_Icon-Light.png') }}" class="nav-btn-icon icon-hover"
                    alt="Inicio">
            </button>

            {{-- OT Anterior (Atras) --}}
            <button type="button" id="btn-scroll-prev" class="nav-bar-btn" title="OT Anterior (subir 1 orden)">
                <img src="{{ asset('images/Color_Icons/Generales/Atras_Icon.png') }}" class="nav-btn-icon icon-normal"
                    alt="Anterior">
                <img src="{{ asset('images/Light_Icons/Atras_Icon-Light.png') }}" class="nav-btn-icon icon-hover"
                    alt="Anterior">
            </button>

            {{-- Buscador y Selector directo de OT con Dropdown Interactivo GIS --}}
            <div class="nav-ot-search-box" id="nav-ot-search-box">
                <span class="nav-ot-badge">OT</span>
                <input type="text" id="nav-ot-input" class="nav-ot-input-field" placeholder="Buscar OT o molde..."
                    autocomplete="off" title="Escribe o selecciona una Orden de Trabajo">
                <button type="button" id="nav-ot-clear-btn" class="nav-ot-clear-btn" title="Limpiar búsqueda"
                    style="display: none;">✕</button>
                <button type="button" id="nav-ot-dropdown-toggle" class="nav-ot-dropdown-toggle"
                    title="Ver todas las Órdenes de Trabajo">▼</button>
                <button type="button" id="nav-ot-go-btn" class="nav-ot-submit-btn" title="Buscar e ir a la OT">➔</button>

                {{-- Menú flotante hacia arriba con diseño GIS --}}
                <div id="nav-ot-dropdown-menu" class="nav-ot-dropdown-menu" style="display: none;">
                    <div class="nav-ot-dropdown-header">
                        <span class="dropdown-header-title">Órdenes de Trabajo Activas</span>
                        <span id="nav-ot-dropdown-total" class="nav-ot-dropdown-total">{{ count($orderedOtIds) }} OTs</span>
                    </div>
                    <div id="nav-ot-dropdown-list" class="nav-ot-dropdown-list">
                        {{-- Items poblados dinámicamente por JS --}}
                    </div>
                </div>
            </div>

            {{-- Contador de posición actual --}}
            <div class="nav-ot-position-chip" id="nav-ot-counter" title="Posición de OT visible / Total cargadas">
                OT 1 / {{ count($orderedOtIds) }}
            </div>

            {{-- OT Siguiente (Adelante) --}}
            <button type="button" id="btn-scroll-next" class="nav-bar-btn" title="Siguiente OT (bajar 1 orden)">
                <img src="{{ asset('images/Color_Icons/Generales/Adelante_Icon.png') }}" class="nav-btn-icon icon-normal"
                    alt="Siguiente">
                <img src="{{ asset('images/Light_Icons/Adelante_Icon-Light.png') }}" class="nav-btn-icon icon-hover"
                    alt="Siguiente">
            </button>

            {{-- Ir a la última OT cargada (AvRa) --}}
            <button type="button" id="btn-scroll-bottom" class="nav-bar-btn" title="Ir a la última OT cargada">
                <img src="{{ asset('images/Color_Icons/Generales/AvRa_Icon.png') }}" class="nav-btn-icon icon-normal"
                    alt="Última">
                <img src="{{ asset('images/Light_Icons/AvRa_Icon-Light.png') }}" class="nav-btn-icon icon-hover"
                    alt="Última">
            </button>

            @if ($hasMore)
                {{-- Separador vertical --}}
                <div id="dock-load-divider" class="nav-bar-divider"></div>

                {{-- Botón Integrado de Cargar Más OTs (Recarga) --}}
                <button type="button" id="btn-dock-load-more" class="btn-dock-load-more"
                    title="Cargar siguientes Órdenes de Trabajo por prioridad">
                    <span class="dock-load-icon">
                        <img src="{{ asset('images/Color_Icons/Generales/Recarga_Icon.png') }}"
                            class="dock-icon-img icon-normal" alt="Cargar más">
                        <img src="{{ asset('images/Light_Icons/Recarga_Icon-Light.png') }}" class="dock-icon-img icon-hover"
                            alt="Cargar más">
                    </span>
                    <span class="dock-load-text">Cargar más ({{ $remaining }})</span>
                </button>
            @endif

            {{-- Botón para plegar controles a la pestañita --}}
            <button type="button" id="btn-collapse-nav-dock" class="nav-bar-close-btn"
                title="Plegar controles a la pestañita">
                <span>✕</span>
            </button>
        </div>
    </div>

    <script>
        window.routes = window.routes || {};
        window.routes.showPiecesInProgress = "{{ route('showPiecesInProgress') }}";

        {{-- Inyectar OTs en progreso (JSON) y IDs en orden de prioridad --}}
        window.wOInProgress = @json($wOInProgress);
        window.orderedOtIds = @json($orderedOtIds ?? []);

        {{-- Checklist de fundición: solo OTs con flujo activo (perfiles 1 y 2 lo usan en JS) --}}
        window.fundicionChecklist = @json($fundicionChecklist ?? []);
        window.fundicionChecklistUrl = "{{ url('/piecesInProgress/fundicionChecklist') }}";
        window.planeacionChecklistUrl = "{{ url('/piecesInProgress/planeacionChecklist') }}";
        window.termicoChecklistUrl = "{{ url('/piecesInProgress/termicoChecklist') }}";

        {{-- Datos de paginación progresiva --}}
        window.paginationData = {
            hasMore: {{ $hasMore ? 'true' : 'false' }},
            nextPage: {{ $nextPage ?? 2 }},
            totalWOs: {{ $totalWOs ?? 0 }},
            loadedSoFar: {{ $loadedSoFar ?? count($orderedOtIds) }},
            remaining: {{ $remaining ?? 0 }}
        };

        {{-- Protección contra auto-reload intrusivo: sólo recarga tras 5 min de inactividad total y estando al tope --}}
        window.hasLoadedMorePages = false;
        window.userHasInteractedWithPage = false;
        const markUserInteraction = () => {
            window.userHasInteractedWithPage = true;
        };
        window.addEventListener('scroll', markUserInteraction, {
            passive: true,
            once: true
        });
        window.addEventListener('click', markUserInteraction, {
            passive: true,
            once: true
        });
        window.addEventListener('keydown', markUserInteraction, {
            passive: true,
            once: true
        });

        setTimeout(() => {
            const isAtTop = (window.scrollY || document.documentElement.scrollTop) <= 20;
            if (!window.hasLoadedMorePages && !window.userHasInteractedWithPage && isAtTop && !document
                .querySelector('.div-opacity')) {
                location.reload();
            }
        }, 300000);
    </script>

    {{-- Contenedor del botón "Cargar más Órdenes de Trabajo" --}}
    <div id="load-more-ot-wrapper" class="load-more-ot-wrapper">
        @if ($hasMore)
            <button type="button" id="btn-load-more-ot" class="btn-load-more-ot-premium"
                title="Cargar siguientes Órdenes de Trabajo por prioridad">
                <span class="btn-load-more-ot-icon">
                    <img src="{{ asset('images/Color_Icons/Generales/Recarga_Icon.png') }}"
                        class="dock-icon-img icon-normal" alt="Cargar más">
                    <img src="{{ asset('images/Light_Icons/Recarga_Icon-Light.png') }}" class="dock-icon-img icon-hover"
                        alt="Cargar más">
                </span>
                <span class="btn-load-more-ot-text">Cargar más Órdenes de Trabajo ({{ $remaining }} restantes)</span>
            </button>
        @else
            <div class="load-more-completed-badge">
                <span class="badge-status-dot"></span>
                <span>Todas las Órdenes de Trabajo activas están cargadas (<strong>{{ $totalWOs }}</strong>)</span>
            </div>
        @endif
    </div>
@endsection
