@extends('layouts.appMenu')

@section('head')
<title>Datos de productividad</title>
<link rel="icon" href="{{ url('images/lg_saavedra.png') }}?v=1">
@vite(['resources/css/users_views/productionData.css', 'resources/js/users_views/productionData.js'])
@endsection

@section('background-body', 'background-image:url("' . asset("images/fondoLogin.jpg") . '")')

@section('content')
<div class="container">
    <h1>Datos de productividad</h1>
    <!--Sección del dashboard de producción-->
    <form method="post" action="{{ route('showProduccion') }}">
        @csrf
        <div class="dashboard">
            <!-- Cuadro de OT -->
            <div class="box ot pro-background-color-fff">
                <label for="ot-select">Orden de Trabajo:</label>
            </div>

            <!--Cuadro de Operadores-->
            <div class="box operadores">
                <label for="operadores-label">Operadores:</label>
                <input id="operadores-input" class="filtros" type="text" disabled>
            </div>

            <!--Cuadro de clase-->
            <div class="box clases">
                <label for="clases-select">Clase:</label>
                <input id="clases-input" class="filtros" type="text" disabled>
            </div>


            <!-- Cuadro de Pedido -->
            <div class="box pedido">
                <label for="pedido-select">Pedido:</label>
                <input id="pedido-input" class="filtros" type="text" disabled>
            </div>

            <!-- Cuadro de proceso -->
            <div class="box procesos">
                <label for="procesos-select">Proceso:</label>
                <input id="procesos-input" class="filtros" type="text" disabled>
            </div>
        </div>
        <!-- Boton de buscar -->
        <div class="button-container">
            <input type="submit" id="button" class="button pro-display-none">
        </div>
    </form>

    <!-- Tabla de resultados -->
    {{-- Agregar funcion en js para crear la tabla con los respectivos datos --}}
    @isset($filtros)
    <div class="dashboard2">
        <div class="datos">
            <h3>Orden de trabajo</h3>
            <input class="filtros2" type="text" value="{{ $filtros['ot'] }} {{ $filtros['moldura'] }}" disabled>
            <h3>Clase</h3>
            <input class="filtros2" type="text" value="{{ $filtros['clase'] }} {{ $filtros['pedido'] }} piezas"
                disabled>
            <h3>Proceso</h3>
            <input class="filtros2" type="text" value="{{ $filtros['proceso'] }}" disabled>
            <h3>Operador</h3>
            <input class="filtros2 pro-width-300px">
        </div>
        <div class="div-table">
            <script>
                window.datosOperadores = @json($operadores);
                window.filtros = @json($filtros);
            </script>
        </div>
    </div>
    @endisset
</div>
<script>
    window.datos = @json($datos);
</script>
@endsection