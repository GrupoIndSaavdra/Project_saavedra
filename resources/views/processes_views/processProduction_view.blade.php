@extends('layouts.appMenu')

<!--Estilos y codigo JS-->
@section('head')
    <title>Producción</title>
    <link rel="icon" href="{{ url('images/lg_saavedra.png') }}?v=1">
    @vite(['resources/css/processes_views/processProduction.css', 'resources/js/processes_views/processProduction.js'])
@endsection

@section('background-body', 'background-color: #dbe4ee; background-image: repeating-linear-gradient(45deg, transparent, transparent 78px, rgba(3, 57, 102, 0.07) 78px, rgba(3, 57, 102, 0.07) 80px, rgba(255, 255, 255, 0.75) 80px, rgba(255, 255, 255, 0.75) 82px), repeating-linear-gradient(-45deg, transparent, transparent 78px, rgba(3, 57, 102, 0.07) 78px, rgba(3, 57, 102, 0.07) 80px, rgba(255, 255, 255, 0.75) 80px, rgba(255, 255, 255, 0.75) 82px), radial-gradient(circle at 50% 20%, #edf3f9 0%, #cbd7e4 100%); background-attachment: fixed; background-size: cover;')
@section('header-left')
    @if (!empty($arrayData['edit']) && $arrayData['edit'] == 1)
        <a href="#" class="btn-cancel-report">
            <img src="{{ asset('images/Cancelar-Icon.png') }}" class="cancel-report-icon" alt="Cancelar">
            <span>Cancelar</span>
            <img src="{{ asset('images/Cancelar-Icon.png') }}" class="cancel-report-icon" alt="Cancelar">
        </a>
    @else
        <button class="btn-finishReport">
            <img src="{{ asset('images/Terminar-Repo-Icon.png') }}" class="finish-report-icon" alt="Terminar reporte">
            <span>Terminar reporte</span>
            <img src="{{ asset('images/Terminar-Repo-Icon.png') }}" class="finish-report-icon" alt="Terminar reporte">
        </button>
    @endif
@endsection

@section('content')
    <div class="container-meta">
        <div class="principal-data">
            <div class="panel-section-header">
                <img src="{{ asset('images/Tornilleria-Icon.png') }}" class="panel-header-icon" alt="Tornillería">
                <span>Reporte de Operador</span>
                <img src="{{ asset('images/Tornilleria-Icon.png') }}" class="panel-header-icon" alt="Tornillería">
            </div>
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
                            <th>
                                <div class="table-meta-th-content">
                                    <img src="{{ asset('images/Turno-Icon.png') }}" class="table-meta-icon" alt="Tiempo">
                                    <span>Tiempo estándar</span>
                                    <img src="{{ asset('images/Turno-Icon.png') }}" class="table-meta-icon" alt="Tiempo">
                                </div>
                            </th>
                            <th>
                                <div class="table-meta-th-content">
                                    <img src="{{ asset('images/Meta-Icon.png') }}" class="table-meta-icon" alt="Meta">
                                    <span>Meta piezas/juegos</span>
                                    <img src="{{ asset('images/Meta-Icon.png') }}" class="table-meta-icon" alt="Meta">
                                </div>
                            </th>
                            <th>
                                <div class="table-meta-th-content">
                                    <img src="{{ asset('images/Resultado-Icon.png') }}" class="table-meta-icon" alt="Resultado">
                                    <span>Resultado</span>
                                    <img src="{{ asset('images/Resultado-Icon.png') }}" class="table-meta-icon" alt="Resultado">
                                </div>
                            </th>
                            <th>
                                <div class="table-meta-th-content">
                                    <img src="{{ asset('images/OT-Icon.png') }}" class="table-meta-icon" alt="Pedido">
                                    <span>Pedido Consignación</span>
                                    <img src="{{ asset('images/OT-Icon.png') }}" class="table-meta-icon" alt="Pedido">
                                </div>
                            </th>
                            <th>
                                <div class="table-meta-th-content">
                                    <img src="{{ asset('images/Tornilleria-Icon.png') }}" class="table-meta-icon" alt="Restantes">
                                    <span>Piezas Restantes</span>
                                    <img src="{{ asset('images/Tornilleria-Icon.png') }}" class="table-meta-icon" alt="Restantes">
                                </div>
                            </th>
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
                            <td>
                                <input type="text" id="meta_consignment_pieces" value="{{ $arrayData['consignmentPieces'] ?? 0 }} piezas" readonly>
                            </td>
                            <td>
                                <input type="text" id="meta_remaining_pieces" value="{{ $arrayData['remainingPieces'] ?? 0 }} piezas" readonly>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </form>
            <div class="warning-pieces yellow-glass-container">
                <div class="warning-icon-wrapper">
                    <img src="{{ asset('images/Notificacion-Icon.png') }}" class="warning-icon-img" alt="Notificación">
                </div>
                <div class="warning-text">
                    <div class="warning-title-pill">
                        <img src="{{ asset('images/Alerta-Icon.png') }}" class="warning-pill-icon" alt="Alerta">
                        <span>Aviso Importante de Producción</span>
                        <img src="{{ asset('images/Alerta-Icon.png') }}" class="warning-pill-icon" alt="Alerta">
                    </div>
                    <div class="warning-body">
                        LAS COTAS SON <span class="highlight-important-orange">ÚNICAMENTE DE REFERENCIA</span>; VERIFIQUE SIEMPRE LAS MEDIDAS CON EL DIBUJO VIGENTE.
                        SI DETECTA CUALQUIER ANOMALÍA O DIFERENCIA EN LAS MEDIDAS, <span class="highlight-important-orange">NOTIFÍQUELO DE INMEDIATO</span> AL ENCARGADO DE PRODUCCIÓN O AL DEPARTAMENTO DE CALIDAD.
                        <span class="highlight-important-orange">ES OBLIGATORIO LIBERAR LA PRIMERA PIEZA Y, POSTERIORMENTE, CADA 5 PIEZAS</span> ANTES DE CONTINUAR CON EL PROCESO,
                        LOS PLANOS SE ENCUENTRAN EXCLUSIVAMENTE EN EL ÍCONO DE DIBUJOS DEL SOFTWARE.
                    </div>
                </div>
                <div class="warning-icon-wrapper">
                    <img src="{{ asset('images/Notificacion-Icon.png') }}" class="warning-icon-img" alt="Notificación">
                </div>
            </div>
        </div>
        <div class="div-table-code">
            <div class="panel-section-header">
                <img src="{{ asset('images/Operador-Icon.png') }}" class="panel-header-icon" alt="Operador">
                <span>Datos del Operador</span>
                <img src="{{ asset('images/Operador-Icon.png') }}" class="panel-header-icon" alt="Operador">
            </div>

            <div class="operator-profile-card">
                <div class="operator-avatar-wrapper">
                    <img src="{{ asset('images/Operador-Profile-Icon.png') }}" alt="Operador" class="operator-avatar-img">
                </div>
                <div class="operator-info-content">
                    <div class="operator-meta-badges">
                        <span class="operator-id-badge">
                            <img src="{{ asset('images/Operador-Icon.png') }}" alt="Matrícula">
                            <span>Matrícula: {{ auth()->user()->matricula }}</span>
                        </span>
                        <span class="operator-status-badge">
                            <span class="status-pulse-dot"></span>
                            <span>Activo</span>
                        </span>
                    </div>
                    <div class="operator-fullname-box">
                        <span class="operator-name-text">{{ auth()->user()->a_paterno . ' ' . auth()->user()->a_materno . ' ' . auth()->user()->nombre }}</span>
                    </div>
                </div>
                {{-- Input oculto para compatibilidad con telemetría de processProduction.js --}}
                <div class="operator-name" style="display: none;">
                    <input type="hidden" value="{{ auth()->user()->matricula . ' - ' . auth()->user()->a_paterno . ' ' . auth()->user()->a_materno . ' ' . auth()->user()->nombre }}">
                </div>
            </div>

            {{-- El botón de documentación técnica se inyecta vía JS en insertProductionActions --}}

            <table class="table-code">
                <tr>
                    <th>
                        <div class="table-code-th-content">
                            <img src="{{ asset('images/Codigo-Icon.png') }}" class="table-code-icon" alt="Código">
                            <span>Código</span>
                            <img src="{{ asset('images/Codigo-Icon.png') }}" class="table-code-icon" alt="Código">
                        </div>
                    </th>
                    <th>F- PRO - CPT</th>
                </tr>
                <tr>
                    <th>
                        <div class="table-code-th-content">
                            <img src="{{ asset('images/Version-Icon.png') }}" class="table-code-icon" alt="Versión">
                            <span>Versión</span>
                            <img src="{{ asset('images/Version-Icon.png') }}" class="table-code-icon" alt="Versión">
                        </div>
                    </th>
                    <th>05</th>
                </tr>
                <tr>
                    <th>
                        <div class="table-code-th-content">
                            <img src="{{ asset('images/Revision-Icon.png') }}" class="table-code-icon" alt="Revisión">
                            <span>Revisión</span>
                            <img src="{{ asset('images/Revision-Icon.png') }}" class="table-code-icon" alt="Revisión">
                        </div>
                    </th>
                    <th>23 - Agosto - 23</th>
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
        window.imgProgramsCNC = "{{ asset('images/ProgramsCNC.png') }}";
        window.imgProgramsShadow = "{{ asset('images/ProgramsCNC-Shadow.png') }}";
        window.imgTechDocs = "{{ asset('images/manual.png') }}";
        window.imgNoCotas = "{{ asset('images/icono_sin_cotas.png') }}";
        window.imgCancelar = "{{ asset('images/Cancelar-Icon.png') }}";
        window.imgResultado = "{{ asset('images/Resultado-Icon.png') }}";
        window.occupiedMachines = @json((new \App\Http\Controllers\MachinesController)->machinesOccupied());
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

    {{-- Modales interactivos para Fecha, Turno y Máquina --}}
    @include('components.modal_cm_calendar')
    @include('components.modal_cm_time')
    @include('components.modal_cm_machine')
@endsection

