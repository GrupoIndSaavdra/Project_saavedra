@extends('layouts.appMenu')

<!--Estilos y codigo JS-->
@section('head')
<title>Formato de Reporte de Producción</title>
@vite(['resources/css/processes_views/processProductionReport_view.css', 'resources/js/processes_views/processProductionReport_view.js'])
@endsection

@section('background-body', 'background-image:url("' . asset("images/Fondoprocesos.png") . '")')
@section('content')
@include('layouts.partials.messages')
<div class="container-meta">
    <div class="principal-data">
        <div class="form-group">
            <input type="text" name="operator" value="{{ $arrayData['operator'] }}" readonly>
            <label for="operator" class="form-label">Operador</label>
        </div>
        <div class="form-group">
            <input type="text" name="workOrder">
            <label for="workOrder" class="form-label">Orden de trabajo</label>
        </div>
        <div class="form-group row-direction">
            <div class="form-subgroup">
                <input type="text" name="class" value="{{$arrayData['class']}}" readonly>
                <label for="class" class="form-label">Clase</label>
            </div>
            <div class="form-subgroup">
                <input type="text" name="machine" value="{{ $arrayData['meta']->maquina }}" readonly>
                <label for="machine" class="form-label">Máquina</label>
            </div>
        </div>
        <div class="form-group row-direction">
            <div class="form-subgroup">
                <input style="font-size:1.1em;" type="time" name="startTime" value="{{ $arrayData['meta']->h_inicio }}" readonly>
                <label for="startTime" class="form-label">Hora de inicio</label>
            </div>
            <div class="form-subgroup">
                <input style="font-size:1.1em;" type="time" name="endTime" value="{{ $arrayData['meta']->h_termino }}" readonly>
                <label for="endTime" class="form-label">Hora de termino</label>
            </div>
            <div class="form-subgroup">
                <input style="font-size:1.1em;" type="date" name="date" value="{{ $arrayData['meta']->fecha }}" readonly>
                <label for="date" class="form-label">Fecha</label>
            </div>
        </div>
    </div>
    <div class="div-table-meta">
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
    </div>
    <div class="div-table-code">
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
@endsection