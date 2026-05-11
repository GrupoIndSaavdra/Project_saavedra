@extends('layouts.appMenu')

<!--Estilos y codigo JS-->
@section('head')
    <title>Producción</title>
    <link rel="icon" href="{{ url('images/lg_saavedra.png') }}?v=1">
    @vite(['resources/css/processes_views/processProduction.css', 'resources/js/processes_views/processProduction.js'])
@endsection

@section('background-body', 'background-image:url("' . asset("images/Fondoprocesos.png") . '")')
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
            <label class="warning-pieces">LAS COTAS SON DE REFERENCIA; ANTE DUDAS, CONSULTA CON CALIDAD O PROGRAMACIÓN. ES OBLIGATORIO LIBERAR LA 1RA PIEZA Y CADA 5 PIEZAS CON CALIDAD ANTES DE CONTINUAR. LOS PLANOS SE ENCUENTRAN EXCLUSIVAMENTE EN EL ICONO DE DIBUJOS AQUÍ DEBAJO, YA QUE LA CARPETA DE DOCUMENTOS NO SE ACTUALIZARÁ PARA FOMENTAR EL USO DEL SOFTWARE AL 100%. CUALQUIER ERROR DEL SISTEMA O DIFERENCIA EN MEDIDAS DEBE REPORTARSE DE INMEDIATO AL DEPARTAMENTO CORRESPONDIENTE..</label>
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
