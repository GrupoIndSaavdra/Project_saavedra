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

@section('background-body', 'background-image:url("' . asset("images/fondos/fondoLogin.jpg") . '")')

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

    {{-- Botones flotantes de navegación rápida (Inicio / Fin) --}}
    <div class="scroll-navigation-container">
        <button id="btn-scroll-top" class="scroll-nav-btn" title="Ir al inicio">▲</button>
        <button id="btn-scroll-bottom" class="scroll-nav-btn" title="Ir al final">▼</button>
    </div>

    <script>
        {{-- Inyectar OTs en progreso (JSON) y IDs en orden de prioridad --}}
        window.wOInProgress = @json($wOInProgress);
        window.orderedOtIds = @json($orderedOtIds ?? []);

        {{-- Checklist de fundición: solo OTs con flujo activo (perfiles 1 y 2 lo usan en JS) --}}
        window.fundicionChecklist    = @json($fundicionChecklist ?? []);
        window.fundicionChecklistUrl = "{{ url('/piecesInProgress/fundicionChecklist') }}";
        window.planeacionChecklistUrl = "{{ url('/piecesInProgress/planeacionChecklist') }}";
        window.termicoChecklistUrl = "{{ url('/piecesInProgress/termicoChecklist') }}";

        {{-- Timeout elevado a 120s: el polling de las cards cubre actualizaciones frecuentes --}}
        setTimeout(() => {
            location.reload();
        }, 120000);
    </script>
@endsection
