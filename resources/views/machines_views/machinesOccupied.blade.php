@extends('layouts.appMenu')

@section('head')
    <title>Maquinas Ocupadas</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('images/logos/favicon.ico') }}?v=3">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @vite(['resources/css/machines_views/machinesOccupied.css', 'resources/js/machines_views/machinesOccupied.js'])
@endsection

@section('background-body', 'background-image:url("' . asset('images/fondos/fondoLogin.jpg') . '")')
<!--Body background Image-->
@section('content')
    <div class="wrapper">
        <div class="report-header">
            <div class="title-row">
                <img src="{{ asset('images/Color_Icons/Vistas/Maquinas_Ocupadas.png') }}" class="header-view-icon" alt="Máquinas Ocupadas" />
                <h2>Máquinas Ocupadas</h2>
                <img src="{{ asset('images/Color_Icons/Vistas/Maquinas_Ocupadas.png') }}" class="header-view-icon" alt="Máquinas Ocupadas" />
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
        window.imgProgramsCNC = "{{ asset('images/Color_Icons/Generales/ProgramsCNC.png') }}";
        window.imgProgramsProduction = "{{ asset('images/Color_Icons/Generales/ProgramsProduction.png') }}";
        window.imgOperator = "{{ asset('images/Light_Icons/Operador-Light.png') }}";
        window.imgLocked = "{{ asset('images/Light_Icons/Bloqueado-Light.png') }}";
        window.imgUnlocked = "{{ asset('images/Color_Icons/Generales/desbloqueado.png') }}";
        window.imgSinOrden = "{{ asset('images/Light_Icons/Sin_Orden-Light.png') }}";
        window.imgOrdenAsc = "{{ asset('images/Light_Icons/Orden_Asc-Light.png') }}";
        window.imgOrdenDesc = "{{ asset('images/Light_Icons/Orden_Desc-Light.png') }}";
    </script>
@endsection

