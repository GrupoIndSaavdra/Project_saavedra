@extends('layouts.appMenu')

@section('head')
<title>Datos de productividad</title>
<link rel="icon" href="{{ url('images/lg_saavedra.png') }}?v=1">
@vite(['resources/css/users_views/productionData.css', 'resources/js/users_views/productionData.js'])
@endsection

@section('background-body', 'background-image:url("' . asset("images/fondoLogin.jpg") . '")')

@section('content')
<div class="container">
    <div class="header-section">
        <h1>Datos de Productividad</h1>
        <p class="subtitle">Consulte el rendimiento y la eficiencia de los procesos en tiempo real</p>
    </div>

    @if(session('error'))
    <div class="alert-error-container">
        {{ session('error') }}
    </div>
    @endif

    <!--Sección del dashboard de producción-->
    <form method="post" action="{{ route('showProduccion') }}" class="search-form">
        @csrf
        <div class="dashboard">
            <!-- Cuadro de OT -->
            <div class="box ot">
                <label for="ot-select">Orden de Trabajo</label>
            </div>

            <!--Cuadro de Operadores-->
            <div class="box operadores">
                <label for="operadores-label">Operador</label>
                <input id="operadores-input" class="filtros" type="text" disabled placeholder="Esperando OT...">
            </div>

            <!--Cuadro de clase-->
            <div class="box clases">
                <label for="clases-select">Clase</label>
                <input id="clases-input" class="filtros" type="text" disabled placeholder="Esperando OT...">
            </div>

            <!-- Cuadro de Pedido -->
            <div class="box pedido">
                <label for="pedido-select">Pedido</label>
                <input id="pedido-input" class="filtros" type="text" disabled placeholder="—">
            </div>

            <!-- Cuadro de proceso -->
            <div class="box procesos">
                <label for="procesos-select">Proceso</label>
                <input id="procesos-input" class="filtros" type="text" disabled placeholder="Esperando Operador/Clase...">
            </div>
        </div>
        
        <!-- Boton de buscar (oculto por consulta automatica) -->
        <div class="button-container" style="display: none;">
            <button type="submit" id="button" class="button" disabled>
                Consultar Rendimiento
            </button>
        </div>
    </form>

    <!-- Indicador de carga de consulta AJAX -->
    <div id="loading-status" class="loading-status-banner">
        <div class="status-spinner" style="display: none;"></div>
        <span id="status-text">
            @if(isset($filtros))
                Consulta completada. Mostrando rendimiento para la OT {{ $filtros['ot'] }}, operador {{ $filtros['operador'] }}.
            @else
                Falta seleccionar 5 parámetros para realizar la consulta automática.
            @endif
        </span>
    </div>

    <!-- Tabla de resultados -->
    <div class="dashboard2" @if(!isset($filtros)) style="display: none;" @endif>
        <div class="datos">
            <div class="detail-group">
                <div class="detail-label">Orden de Trabajo</div>
                <div class="detail-value" id="detail-ot">@isset($filtros) {{ $filtros['ot'] }} - {{ $filtros['moldura'] }} @endisset</div>
            </div>
            
            <div class="detail-group">
                <div class="detail-label">Clase de Moldura</div>
                <div class="detail-value" id="detail-clase">@isset($filtros) {{ $filtros['clase'] }} ({{ $filtros['pedido'] }} pzas) @endisset</div>
            </div>
            
            <div class="detail-group">
                <div class="detail-label">Proceso</div>
                <div class="detail-value" id="detail-proceso">@isset($filtros) {{ $filtros['proceso'] }} @endisset</div>
            </div>
            
            <div class="detail-group">
                <div class="detail-label">Operador Asignado</div>
                <div class="detail-value" id="detail-operador">@isset($filtros) {{ $filtros['operador'] }} @endisset</div>
            </div>
        </div>
        
        <div class="div-table">
            @isset($filtros)
            <script>
                window.datosOperadores = @json($operadores);
                window.filtros = @json($filtros);
            </script>
            @endisset
        </div>
    </div>
</div>

<script>
    window.datos = @json($datos);
</script>
@endsection