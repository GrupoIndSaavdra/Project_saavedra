@extends('layouts.appMenu')

<!--Estilos y codigo JS-->
@section('head')
<title>C.Nominales y tolerancias</title>
<link rel="icon" href="{{ url('images/lg_saavedra.png') }}?v=1">
@vite(['resources/css/processes_views/c_nominals_view.css', 'resources/js/processes_views/c_nominals_view.js'])
@endsection

@section('background-body', 'background-image:url("' . asset("images/fondoLogin.jpg") . '")')
@section('content')
<form action="{{ route('storeCNominals') }}" method="post" class="form-search">
    <div class="wrapper">
        <img src="{{ asset('images/lg_saavedra.png') }}" class="lg-saavedra rounded-4" alt="" />
        <h1>Cotas Nominales y Tolerancias</h1>
        <div class="row-principal"></div>
        <div class="row"></div>
        @include('layouts.partials.messages')
    </div>
    @csrf
    <div class="scrollable-table">
        <!-- Se insertan las tablas de cada proceso con JavaScript -->
    </div>
    <input type="submit" class="btn-submit" value="Guardar" />
</form>
<script>
    window.workOrders = @json($workOrders);
    @if (session('success'))
        window.successSave = true;
    @endif
</script>
@endsection