@extends('layouts.appMenu')

@section('head')
    <title>Piezas en Progreso</title>
    <script>
        window.cerrarImgUrl = "{{ asset('images/cerrar.png') }}";
        window.baseUrl = "{{ url('/') }}";
    </script>
    @vite(['resources/css/pieces_views/piecesInProgress_view.css', 'resources/js/pieces_views/piecesInProgress_view.js'])
@endsection

@section('background-body', 'background-image:url("' . asset('images/fondoLogin.jpg') . '")')

@section('content')

    {{-- Alerta de sesión --}}
    @if (session('finishOrder'))
        <div class="div-opacity">
            <div class="alert-finishOrder">
                <div class="div-cerrar">
                    <button class="btn-cerrar"><img class="img-cerrar" src="{{ asset('images/cerrar.png') }}"></button>
                </div>
                <?php $imgRoute = session('finishOrder')[0] == 'error' ? 'images/error.png' : 'images/ready.png'; ?>
                <img class="img-error" src="{{ asset($imgRoute) }}" alt="alert image">
                <label for="">{{ session('finishOrder')[1] }}</label>
            </div>
        </div>
    @endif

    {{-- Nota informativa --}}
    <div
        style="position: fixed; bottom: 10px; right: 10px; background: rgba(3, 57, 102, 0.9); color: #fff; padding: 10px 15px; border-radius: 8px; font-size: 0.85em; max-width: 300px; box-shadow: 0 4px 6px rgba(0,0,0,0.3); z-index: 1000;">
        ℹ️ Los datos de tiempo son estimaciones iniciales, no seguimiento en tiempo real.
    </div>

    {{-- El JavaScript renderizará las secciones de órdenes de trabajo y procesos --}}
    <script>
        window.wOInProgress = @json($wOInProgress);
        window.pieces_Released = @json($pieces_Released);
        window.info_Pieces = @json($info_Pieces);
    </script>

@endsection
