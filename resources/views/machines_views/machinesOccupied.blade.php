@extends('layouts.appMenu')

@section('head')
    <title>Maquinas Ocupadas</title>
    <link rel="icon" href="{{ url('images/lg_saavedra.png') }}?v=1">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @vite(['resources/css/machines_views/machinesOccupied.css', 'resources/js/machines_views/machinesOccupied.js'])
@endsection

@section('background-body', 'background-image:url("' . asset('images/fondoLogin.jpg') . '")')
<!--Body background Image-->
@section('content')
    <div class="wrapper">
        <div class="report-header">
            <div class="title-row">
                <img src="{{ asset('images/Iconos_Vistas/Maquinas_Ocupadas.png') }}" class="header-view-icon" alt="Máquinas Ocupadas" />
                <h2>Máquinas Ocupadas</h2>
                <img src="{{ asset('images/Iconos_Vistas/Maquinas_Ocupadas.png') }}" class="header-view-icon" alt="Máquinas Ocupadas" />
            </div>
            <p class="subtitle">Estado de asignación operativa y liberación de centros de maquinado</p>
        </div>
        <form action="{{ route('freeUp') }}" method="post">
            @csrf
            @include('layouts.partials.messages')

            <!--La tabla y el boton se crea en JavaScript-->
        </form>
    </div>
    <script>
        const machines = @json($machines);
        window.baseUrl = "{{ url('/') }}";
        window.imgProgramsCNC = "{{ asset('images/ProgramsCNC.png') }}";
        window.imgProgramsProduction = "{{ asset('images/ProgramsProduction.png') }}";
        window.imgOperator = "{{ asset('images/Operador-Icon.png') }}";
        window.imgLocked = "{{ asset('images/bloqueado.png') }}";
        window.imgUnlocked = "{{ asset('images/desbloqueado.png') }}";
        window.imgSinOrden = "{{ asset('images/Sin_Orden.png') }}";
        window.imgOrdenAsc = "{{ asset('images/Orden_Asc.png') }}";
        window.imgOrdenDesc = "{{ asset('images/Orden_Desc.png') }}";
    </script>
@endsection

