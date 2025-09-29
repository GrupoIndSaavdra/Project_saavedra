@extends('layouts.appAdmin')
@section('content')

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maquinas</title>
    @vite('resources/css/maquinas2.css')
</head>

<body>
    <div class="divP">
        <div class="container">
            <!-- SELECT -->
            <div class="label-container">
                <label for="title" class="label-ot">{{$ot->id}}</label>
                <label for="title" class="label-margin">{{$clase->nombre}} {{$clase->tamanio}}</label>
            </div>
            <!-- DIVS -->
            <div class="flex-container">
                    @foreach ($procesos as $proceso)
                        <div class="box">
                            <table>
                                <label for="title" class="titulo-proceso">{{$proceso[0]}}</label>
                                <thead>
                                    <tr>
                                        <th>N_pieza</th>
                                        <th>Operador</th>
                                        <th>Estatus</th>
                                        <th>Máquina</th>
                                        @if ($proceso[0] == "Operacion Equipo")
                                            <th>Operación</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach ($proceso[1] as $piezas)
                                    <tr>
                                        <td>{{$piezas[0]}}</td>
                                        <td>{{$piezas[1]}}</td>
                                        <td>{{$piezas[2]}}</td>
                                        <td>{{$piezas[3]}}</td>
                                        @if ($proceso[0] == "Operacion Equipo")
                                            <td>{{$piezas[4]}}</td>
                                        @endif
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endforeach
                <a href="{{route('vistaPzasMaquina')}}" class="btn-back">Regresar</a>
            </div>
        </div>
    </div>
</body>
@endsection