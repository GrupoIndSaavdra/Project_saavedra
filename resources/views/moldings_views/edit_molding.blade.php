@extends('layouts.appMenu')

@section('head')
<title>Editar molduras</title>
<link rel="icon" type="image/x-icon" href="{{ asset('images/logos/favicon.ico') }}?v=3">
@vite(['resources/css/moldings_views/edit_molding.css', 'resources/js/moldings_views/edit_molding.js'])
<script>
    window.baseUrl = "{{ url('/') }}";
    window.imgEdit = "{{ asset('images/Color_Icons/Generales/DrawsProduction.png') }}";
    window.imgDelete = "{{ asset('images/Color_Icons/Generales/delete.png') }}";
    window.imgCancel = "{{ asset('images/Light_Icons/Cancelar-Light.png') }}";
</script>
@endsection

@section('background-body', 'background-image:url("' . asset("images/fondos/fondoLogin.jpg") . '")') <!--Body background Image-->
@section('content')
<div class="wrapper">
    <div class="report-header">
        <div class="title-row">
            <img src="{{ asset('images/Color_Icons/Vistas/Registro_Edicion_Molduras.png') }}" class="header-view-icon" alt="Editar molduras" />
            <h2>Editar molduras</h2>
            <img src="{{ asset('images/Color_Icons/Vistas/Registro_Edicion_Molduras.png') }}" class="header-view-icon" alt="Editar molduras" />
        </div>
        <p class="subtitle">Actualización y mantenimiento del catálogo de molduras existentes</p>
    </div>

    <!-- Barra de búsqueda y conteo de molduras -->
    <div class="molding-toolbar">
        <div class="molding-search-box">
            <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input type="text" id="molding-search-input" class="molding-search-input" placeholder="Buscar moldura por nombre..." autocomplete="off">
            <button type="button" id="molding-search-clear" class="molding-search-clear" title="Limpiar búsqueda" style="display: none;">&times;</button>
        </div>
        <div class="table-results-badge" id="molding-results-badge">
            Total: <strong id="molding-count-label">{{ count($moldings) }}</strong> molduras
        </div>
    </div>

    <form action="{{ route('updateMolding') }}" method="post">
        @csrf
        @include('layouts.partials.messages')

        <!--La tabla y el boton se crea en JavaScript-->
    </form>
</div>
<script>
    const moldings = @json($moldings);
</script>
@endsection
