@extends('layouts.appMenu')

<!--Estilos y codigo JS-->
@section('head')
<title>C.Nominales y tolerancias</title>
<link rel="icon" type="image/x-icon" href="{{ asset('images/logos/favicon.ico') }}?v=3">
@vite(['resources/css/processes_views/cNominals_view.css', 'resources/js/processes_views/cNominals_view.js'])
@endsection

@section('background-body', 'background-image:url("' . asset("images/fondos/fondoLogin.jpg") . '")')
@section('content')
<form action="{{ route('storeCNominals') }}" method="post" class="form-search">
    <div class="wrapper">
        <div class="report-header">
            <div class="title-row">
                <img src="{{ asset('images/Color_Icons/Vistas/Cotas_Nominales_Tolerancias.png') }}" class="header-view-icon" alt="Cotas Nominales" />
                <h1>Cotas Nominales y Tolerancias</h1>
                <img src="{{ asset('images/Color_Icons/Vistas/Cotas_Nominales_Tolerancias.png') }}" class="header-view-icon" alt="Cotas Nominales" />
            </div>
            <p class="subtitle">Definición de cotas críticas y márgenes de tolerancia por proceso</p>
        </div>
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
