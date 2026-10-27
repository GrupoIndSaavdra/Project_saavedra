@extends('layouts.appMenu')

@section('head')
<title>Registrar Orden de trabajo</title>
<link rel="icon" type="image/x-icon" href="{{ asset('images/logos/favicon.ico') }}?v=3">
@vite(['resources/css/wo_views/manage_wo.css', 'resources/js/wo_views/manage_wo.js'])
@endsection

@section('background-body', 'background-image:url("' . asset("images/fondos/fondoLogin.jpg") . '")')

@section('content')
<!--Formulario para seleccionar o crear una orden de trabajo-->
<div class="wrapper">
    <div class="report-header">
        <div class="title-row">
            <img src="{{ asset('images/Color_Icons/Vistas/Agregar_Seleccionar_OT.png') }}" class="header-view-icon" alt="Orden de Trabajo" />
            <h2>Agregar o seleccionar orden de trabajo</h2>
            <img src="{{ asset('images/Color_Icons/Vistas/Agregar_Seleccionar_OT.png') }}" class="header-view-icon" alt="Orden de Trabajo" />
        </div>
        <p class="subtitle">Seleccione una OT existente para captura de procesos o registre una nueva orden</p>
    </div>
    <form action="{{ route('storeWO', request('almacen_only') == 1 ? ['almacen_only' => 1] : []) }}" method="POST" class="form pt-3">
        @csrf
        @include('layouts.partials.messages') <!--Mensajes de error o exito en la creacion o seleccion de una orden de trabajo-->
        <input type="hidden" value="{{ request('almacen_only') == 1 ? 5 : auth()->user()->perfil }}" id="wo-profile" name="profile" />
        <div class="div-bttns"></div>
    </form>
</div>
<script>
    window.workOrders = @json($workOrders);
    window.moldings = @json($moldings);
</script>
@endsection
