@extends('layouts.appMenu')

<!--Estilos y codigo JS-->
@section('head')
<title>Tiempos de producción</title>
<link rel="icon" href="{{ url('images/lg_saavedra.png') }}?v=1">
@vite(['resources/css/processes_views/productionTimes.css', 'resources/js/processes_views/productionTimes.js'])
@endsection

@section('background-body', 'background-image:url("' . asset("images/fondoLogin.jpg") . '")')
@section('content')

<!--Sección de busqueda-->
<form action="{{ route('storeTimes') }}" method="post" class="form">
    @csrf
    <div class="search left">
        <img src="{{ asset('/images/lg_saavedra.png') }}" alt="lg-saavedra" class="lg-saavedra">
        <h2>Modificar tiempos de producción</h2>
        @csrf
        @include('layouts.partials.messages')
    </div>

</form>
<script>
    const workOrders = @json($workOrders);
</script>
@endsection