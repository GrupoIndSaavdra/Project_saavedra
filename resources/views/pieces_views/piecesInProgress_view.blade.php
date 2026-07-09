@extends('layouts.appMenu')

@section('head')
    <title>Progreso de OT</title>
    <link rel="icon" href="{{ url('images/lg_saavedra.png') }}?v=1">
    <script>
        window.cerrarImgUrl = "{{ asset('images/cerrar.png') }}";
        window.baseUrl = "{{ url('/') }}";
        window.ptaCardsData = @json($ptaCardsData ?? []);
        window.ptaResultsBaseUrl = "{{ url('admin/pta/results') }}";
    </script>
    @vite(['resources/css/pieces_views/piecesInProgress_view.css', 'resources/js/pieces_views/piecesInProgress_view.js'])
@endsection

@section('background-body', 'background-image:url("' . asset("images/fondoLogin.jpg") . '")')

@section('content')
    @if (session('finishOrder'))
        <div class="div-opacity">
            <div class="alert-finishOrder">
                <div class="div-cerrar">
                    <button class="btn-cerrar"><img class="img-cerrar" src="{{ asset('images/cerrar.png') }}"></button>
                </div>
                <?php    $imgRoute = session('finishOrder')[0] == "error" ? "images/error.png" : "images/ready.png";?>
                <img class="img-error" src="{{ asset($imgRoute) }}" alt="alert image">
                <label for="">{{ session('finishOrder')[1] }}</label>
            </div>
        </div>
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
