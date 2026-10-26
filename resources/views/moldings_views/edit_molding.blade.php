@extends('layouts.appMenu')

@section('head')
<title>Editar molduras</title>
<link rel="icon" type="image/x-icon" href="{{ asset('images/logos/favicon.ico') }}?v=3">
@vite(['resources/css/moldings_views/edit_molding.css', 'resources/js/moldings_views/edit_molding.js'])
<script>
    window.baseUrl = "{{ url('/') }}";
    window.imgEdit = "{{ asset('images/iconos/DrawsProduction.png') }}";
    window.imgDelete = "{{ asset('images/iconos/delete.png') }}";
    window.imgCancel = "{{ asset('images/iconos/Cancelar-Icon.png') }}";
</script>
@endsection

@section('background-body', 'background-image:url("' . asset("images/fondos/fondoLogin.jpg") . '")') <!--Body background Image-->
@section('content')
<div class="wrapper">
    <div class="report-header">
        <div class="title-row">
            <img src="{{ asset('images/vistas/Registro_Edicion_Molduras.png') }}" class="header-view-icon" alt="Editar molduras" />
            <h2>Editar molduras</h2>
            <img src="{{ asset('images/vistas/Registro_Edicion_Molduras.png') }}" class="header-view-icon" alt="Editar molduras" />
        </div>
        <p class="subtitle">Actualización y mantenimiento del catálogo de molduras existentes</p>
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
