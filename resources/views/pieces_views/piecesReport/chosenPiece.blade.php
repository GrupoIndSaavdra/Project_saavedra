@extends('layouts.appMenu')

@section('head')
    <title>Visualizacion de pieza</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('images/logos/favicon.ico') }}?v=3">
    @vite('resources/css/pieces_views/piecesReport/chosenPiece.css')
@endsection

@section('background-body', 'background-image:url("' . asset('images/fondos/fondoLogin.jpg') . '")')
<!--Body background Image-->
@section('content')
    @php
        // Determinar si el proceso actual cuenta con Cotas Nominales registradas
        $hasCNominal = false;
        if (!empty($cNominal) && $cNominal !== 0 && $cNominal !== '0') {
            if (is_array($cNominal)) {
                $dimensionalKeys = array_filter(array_keys($cNominal), function($k) {
                    return !in_array($k, ['id', 'id_proceso', 'created_at', 'updated_at', 'n_pieza', 'id_meta']);
                });
                foreach ($dimensionalKeys as $k) {
                    $val = $cNominal[$k];
                    if ($val !== null && $val !== '' && $val !== '----' && $val !== 0 && $val !== '0') {
                        $hasCNominal = true;
                        break;
                    }
                }
            } elseif (is_object($cNominal)) {
                $hasCNominal = true;
            } else {
                $hasCNominal = true;
            }
        }

        // Procesos específicos que por especificación no llevan Cotas Nominales
        $procesosSinCotas = ['Asentado', 'Soldadura', 'Soldadura PTA', 'Rectificado', 'Pintado', 'Rebabeo', 'Pulido'];
        if (isset($process) && in_array($process, $procesosSinCotas)) {
            $hasCNominal = false;
        }
    @endphp

    {{-- 1. Definición de la clase Proceso, generador de tablas e instancia global --}}
    @include('pieces_views.piecesReport.components.proceso_scripts')

    {{-- Contenedor principal autoajustable al viewport --}}
    <div class="container cp-view-wrapper" id="container">
        {{-- 2. Barra Superior (Botón Regresar oficial GIS, Título de OT, Botón Imprimir, Operadores) --}}
        @include('pieces_views.piecesReport.components.top_bar')

        {{-- 3. Barra de Contexto HUD (Chips de Información y Leyenda Dimensional) --}}
        @include('pieces_views.piecesReport.components.hud_strip')

        {{-- 4. Panel de Calidad y Diagnóstico de Desviaciones (Solo para procesos con Cotas Nominales) --}}
        @if ($hasCNominal)
            @include('pieces_views.piecesReport.components.analytics_card')
        @endif

        {{-- 5. Tablas Dimensionales por Proceso de Maquinado --}}
        @include('pieces_views.piecesReport.components.process_tables')
    </div>

    {{-- 6. Motor de Auditoría Dimensional en Vivo y Gestión de Retorno --}}
    @include('pieces_views.piecesReport.components.audit_scripts')
@endsection
