@extends('layouts.appMenu')

<!--Estilos y codigo JS-->
@section('head')
    <title>Producción</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('images/logos/favicon.ico') }}?v=3">
    @vite(['resources/css/processes_views/processProduction.css', 'resources/js/processes_views/processProduction.js'])
@endsection

@section('background-body', 'background-color: #dbe4ee; background-image: repeating-linear-gradient(45deg, transparent, transparent 78px, rgba(3, 57, 102, 0.07) 78px, rgba(3, 57, 102, 0.07) 80px, rgba(255, 255, 255, 0.75) 80px, rgba(255, 255, 255, 0.75) 82px), repeating-linear-gradient(-45deg, transparent, transparent 78px, rgba(3, 57, 102, 0.07) 78px, rgba(3, 57, 102, 0.07) 80px, rgba(255, 255, 255, 0.75) 80px, rgba(255, 255, 255, 0.75) 82px), radial-gradient(circle at 50% 20%, #edf3f9 0%, #cbd7e4 100%); background-attachment: fixed; background-size: cover;')

@section('header-left')
    @include('processes_views.partials.header_actions')
@endsection

@section('content')
    <div class="container-meta">
        {{-- Barra de Resumen en Modo Foco (Visible cuando se pliegan los datos para ampliar la tabla) --}}
        @if (!empty($arrayData['meta']))
            @include('processes_views.partials.meta_summary_strip')
        @endif

        {{-- 1. Panel de selección y formulario del operador --}}
        <div class="principal-data">
            <div class="panel-section-header">
                <img src="{{ asset('images/iconos/Tornilleria-Icon.png') }}" class="panel-header-icon" alt="Tornillería">
                <span>Reporte de Operador</span>
                <img src="{{ asset('images/iconos/Tornilleria-Icon.png') }}" class="panel-header-icon" alt="Tornillería">
            </div>
            <form action="{{ route('headerdata') }}" method="post" class="form-principal-data">
                @csrf
                @include('layouts.partials.messages')
                <div class="form-grid"></div>
            </form>
        </div>

        {{-- 2. Panel Central: Metas, telemetría y aviso importante --}}
        <div class="div-table-meta">
            @include('processes_views.partials.meta_table_panel')
            @include('processes_views.partials.warning_banner')
        </div>

        {{-- 3. Panel Lateral: Perfil del Operador y Código de Control --}}
        @include('processes_views.partials.operator_profile_card')
    </div>

    {{-- Variables y Rutas inyectadas para JS --}}
    @include('processes_views.partials.window_env_data')

    {{-- Modales interactivos para Fecha, Turno y Máquina --}}
    @include('components.modal_cm_calendar')
    @include('components.modal_cm_time')
    @include('components.modal_cm_machine')
@endsection
