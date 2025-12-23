@extends('layouts.appMenu')

@section('head')
<title>Registro de soldadura</title>
@vite(['resources/css/TrackingSoladura/trackingSoldadura.css', 'resources/js/TrackingSoladura/soldadura.js'])
@endsection

@section('background-body', 'background-image:url("' . asset("images/fondoLogin.jpg") . '")')

@section('content')
<!--Formulario para entregar o registrar soldadura-->
<div class="wrapper">
    <img src="{{ asset('images/lg_saavedra.png') }}" class="lg-saavedra rounded-4" alt="" />
    <h2>Selecciona tu proceso a seguir</h2>
    <form action="{{ route('storeWO') }}" method="POST" class="form pt-3">
        @csrf
        @include('layouts.partials.messages')
        <input type="hidden" value="{{ auth()->user()->perfil }}" name="profile" />
        <div class="div-bttns"></div>
    </form>
</div>
<script>
    window.workOrders = @json($workOrders);
    window.moldings = @json($moldings);
</script>
@endsection