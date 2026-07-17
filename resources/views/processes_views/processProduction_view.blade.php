@extends('layouts.appMenu')

<!--Estilos y codigo JS-->
@section('head')
    <title>Producción</title>
    <link rel="icon" href="{{ url('images/lg_saavedra.png') }}?v=1">
    @vite(['resources/css/processes_views/processProduction.css', 'resources/js/processes_views/processProduction.js'])
@endsection

@section('background-body', 'background-color: #f2f2f2; background-image: repeating-linear-gradient(45deg, transparent, transparent 78px, rgba(64,64,64,0.35) 78px, rgba(64,64,64,0.35) 80px, rgba(255,255,255,1) 80px, rgba(255,255,255,1) 82px), repeating-linear-gradient(-45deg, transparent, transparent 78px, rgba(64,64,64,0.35) 78px, rgba(64,64,64,0.35) 80px, rgba(255,255,255,1) 80px, rgba(255,255,255,1) 82px), radial-gradient(circle at 50% -20%, #ffffff 0%, transparent 80%); background-attachment: fixed; background-size: cover;')
@section('content')
    <div class="container-meta">
        <div class="principal-data">
            <form action="{{ route('headerdata') }}" method="post" class="form-principal-data">
                @csrf
                @include('layouts.partials.messages')
                <div class="form-grid">

                </div>
            </form>
        </div>
        <div class="div-table-meta">
            <form action="{{ route('verifiedPassword') }}" method="post" class="form-verified-password">
                @csrf
                <table class="table-meta">
                    <thead>
                        <tr>
                            <th>Tiempo estandar</th>
                            <th>Meta piezas/juegos</th>
                            <th>Resultado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <input type="text" value="{{ $arrayData['meta']->t_estandar ?? 0}} minutos" readonly>
                                {{-- Campo oculto para el monitoreo de productividad --}}
                                <input type="hidden" id="standard_time_hidden" value="{{ $arrayData['meta']->t_estandar ?? 0 }}">
                            </td>
                            <td>
                                <input type="text" value="{{ $arrayData['meta']->meta ?? 0 }} piezas" readonly>
                            </td>
                            <td>
                                <input type="text" value="{{ $arrayData['meta']->resultado ?? 0 }} piezas" readonly>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </form>
            <button class="btn-finishReport">Terminar reporte</button>
            <div class="warning-pieces">
                <svg class="warning-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/>
                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
                <div class="warning-text">
                    LAS COTAS SON <span class="highlight-orange">ÚNICAMENTE DE REFERENCIA</span>; VERIFIQUE SIEMPRE LAS MEDIDAS CON EL DIBUJO VIGENTE.
                    SI DETECTA CUALQUIER ANOMALÍA O DIFERENCIA EN LAS MEDIDAS, NOTIFÍQUELO DE INMEDIATO AL ENCARGADO DE PRODUCCIÓN O AL DEPARTAMENTO DE CALIDAD.
                    <span class="highlight-red">ES OBLIGATORIO LIBERAR LA PRIMERA PIEZA Y, POSTERIORMENTE, CADA 5 PIEZAS</span> ANTES DE CONTINUAR CON EL PROCESO,
                    LOS PLANOS SE ENCUENTRAN EXCLUSIVAMENTE EN EL ÍCONO DE DIBUJOS DEL SOFTWARE</span>.
                </div>
            </div>
        </div>
        <div class="div-table-code">
            <div class="operator-name form-group">
                <input class="normal-input" type="text"
                    value="{{ auth()->user()->matricula . ' - ' . auth()->user()->a_paterno . ' ' . auth()->user()->a_materno . ' ' . auth()->user()->nombre }}"
                    disabled>
                <label>Operador</label>
            </div>

            {{-- El botón de documentación técnica se inyecta vía JS en insertProductionActions --}}

            <table class="table-code">
                <tr>
                    <th>Código</th>
                    <th> F- PRO - CPT</th>
                </tr>
                <tr>
                    <th>Versión</th>
                    <th> 05 </th>
                </tr>
                <tr>
                    <th>Fecha de revisión: </th>
                    <th> 23 - Agosto - 23</th>
                </tr>
            </table>
        </div>
    </div>
    <script>
        window.workOrders = @json($workOrders);
        window.baseUrl = "{{ url('/') }}";
        window.edit = "{{ asset('images/img-edit.png') }}";
        window.back = "{{ asset('images/img-back.png') }}";
        window.imgError = "{{ asset('images/personError.png') }}";
        window.imgNoPieces = "{{ asset('images/ready.png') }}";
        window.imgNoPiecesPrevious = "{{ asset('images/noPieces.png') }}";
        window.cerrarImgUrl = "{{ asset('images/cerrar.png') }}";
        window.imgCerrar = "{{ asset('images/cerrar.png') }}";
        window.imgEditPieces = "{{ asset('images/editPieces.png') }}";
        window.imgQualityCheck = "{{ asset('images/Quality.png') }}";
        window.imgDraws = "{{ asset('images/DrawsProduction.png') }}";
        window.imgTechDocs = "{{ asset('images/manual.png') }}";
    </script>
    @isset($arrayData)
        <script>
            window.arrayData = @json($arrayData);
            window.pieceToBeUsed = @json($pieceToBeUsed);
            @if (!empty($arrayData['ptaTableHtml']))
                window.ptaTableHtml = @json($arrayData['ptaTableHtml']);
            @else
                window.ptaTableHtml = null;
            @endif
        </script>
    @endisset
@endsection
