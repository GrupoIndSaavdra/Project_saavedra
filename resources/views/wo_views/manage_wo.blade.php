@extends('layouts.appMenu')

@section('head')
<title>Registrar Orden de trabajo</title>
<link rel="icon" href="{{ url('images/lg_saavedra.png') }}?v=1">
@vite(['resources/css/wo_views/manage_wo.css', 'resources/js/wo_views/manage_wo.js'])
@endsection

@section('background-body', 'background-image:url("' . asset("images/fondoLogin.jpg") . '")')

@section('content')
<!--Formulario para seleccionar o crear una orden de trabajo-->
<div class="wrapper">
    <img src="{{ asset('images/lg_saavedra.png') }}" class="lg-saavedra rounded-4" alt="" />
    <h2>Agregar o seleccionar orden de trabajo</h2>
    <form action="{{ route('storeWO') }}" method="POST" class="form pt-3">
        @csrf
        @include('layouts.partials.messages') <!--Mensajes de error o exito en la creacion o seleccion de una orden de trabajo-->
        <input type="hidden" value="{{ auth()->user()->perfil }}" name="profile" />
        <div class="div-bttns"></div>
    </form>
</div>
<script>
    window.workOrders = @json($workOrders);
    window.moldings = @json($moldings);
</script>
@endsection