@extends('layouts.appMenu')

@section('head')
    <title>Panel de OT's</title>
    <meta http-equiv="refresh" content="10" />
    @vite(['resources/css/wo_views/progressPanel_wo.css', 'resources/js/wo_views/progressPanel_wo.js'])
@endsection

@section('content')
    <div class="tablas">
        <!-- A Tiempo tabla-->
        <div class="tabla-box">
            <h2 style="color: #000000; font-weight: bold;">P O R - D E F I N I R </h2>
            <div class="fila header">
                <div class="tex-center" id="header">OT</div>
                <div class="tex-center" id="header">NOMBRE</div>
                <div class="tex-center" id="header">TIPO</div>
                <div class="tex-center" id="header">F. PRONÓSTICO</div>
                <div class="tex-center" id="header">F. COMPROMISO</div>
                <div class="tex-center" id="header">FECHA REAL</div>
            </div>
            <!-- Fila 1 -->
            <div class="fila">
                <div class="tex-center">6507</div>
                <div class="tex-center">SRA. LEONA EXTRA GRANDE 2 SET NUEVA EDICIÓN</div>
                <div class="tex-center">BOMBILLO</div>
                <div class="tex-center">MIERCOLES 09 JULIO</div>
                <div class="tex-center">VIERNES 11 JULIO</div>
                <div class="tex-center">LUNES 14 JULIO</div>
                <div class="barra">
                    <div class="progreso" style="width: 95%"></div>
                </div>
            </div>
            <!-- Fila 2 -->
            <div class="fila demora">
                <div class="tex-center">8754</div>
                <div class="tex-center">PANTALONES SET 4</div>
                <div class="tex-center">MOLDE</div>
                <div class="tex-center">MARTES 15 JULIO</div>
                <div class="tex-center">JUEVES 17 JULIO</div>
                <div class="tex-center">VIERNES 18 JULIO</div>
                <div class="barra">
                    <div class="progreso" style="width: 10%"></div>
                </div>
            </div>
            <!-- Fila 3 -->
            <div class="fila">
                <div class="tex-center">6541</div>
                <div class="tex-center">FRASCO DE AGUA MINERAL 355ML</div>
                <div class="tex-center">MOLDE</div>
                <div class="tex-center">MIERCOLES 23 JULIO</div>
                <div class="tex-center">JUEVES 24 JULIO</div>
                <div class="tex-center">VIERNES 25 JULIO</div>
                <div class="barra">
                    <div class="progreso" style="width: 88%"></div>
                </div>
            </div>
            <!-- Fila 4 -->
            <div class="fila">
                <div class="tex-center">2013</div>
                <div class="tex-center">MASON GARAT 16 OZ CUADRADO</div>
                <div class="tex-center">BOMBILLO</div>
                <div class="tex-center">MARTES 29 JULIO</div>
                <div class="tex-center">MIERCOLES 30 JULIO</div>
                <div class="tex-center">JUEVES 31 JULIO</div>
                <div class="barra">
                    <div class="progreso" style="width: 70%"></div>
                </div>
            </div>
            <!-- Fila 5 -->
            <div class="fila demora">
                <div class="tex-center">8754</div>
                <div class="tex-center">BIKENDI 60 ML</div>
                <div class="tex-center">MOLDE</div>
                <div class="tex-center">MIERCOLES 09 JULIO</div>
                <div class="tex-center">VIERNES 11 JULIO</div>
                <div class="tex-center">LUNES 14 JULIO</div>
                <div class="barra">
                    <div class="progreso" style="width: 20%"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('background-body')
    background-image: url('{{ asset('images/fondoLogin.jpg') }}');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    background-attachment: fixed;
@endsection
