@extends('layouts.appMenu')

<!--Estilos y codigo JS-->
@section('head')
<title>Producción</title>
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
        <label class="warning-pieces">🔴POR FAVOR SELECCIONA EL NÚMERO DE MÁQUINA QUE SE ENCUENTRA COLOCADO EN TU TORNO O EN EL MUEBLE CORRESPONDIENTE; EN CASO DE NO ENCONTRAR LA PIEZA QUE NECESITAS, NOTIFICA DE INMEDIATO PARA PODER REALIZAR SU RASTREO; TÚ MUEBLE Y COMPUTADORA DEBEN PERMANECER LIMPIOS, Y POR ÚLTIMO NO DESCONECTES LAS COMPUTADORAS❗</label>
    </div>
    <div class="div-table-code">
        <div class="operator-name form-group">
            <input class="normal-input" type="text" value="{{ auth()->user()->matricula . ' - ' . auth()->user()->a_paterno . ' ' . auth()->user()->a_materno . ' ' . auth()->user()->nombre }}" disabled>
            <label>Operador</label>
        </div>
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
    window.imgEditPieces = "{{ asset('images/editPieces.png') }}";
</script>
@isset($arrayData)
<script>
    window.arrayData = @json($arrayData);
    window.pieceToBeUsed = @json($pieceToBeUsed);
</script>
@endisset
@endsection