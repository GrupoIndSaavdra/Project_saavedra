<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="icon" href="{{ url('images/lg_saavedra.png') }}?v=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informacion de la orden de trabajo</title>
    <link rel="icon" type="image/png" href="{{ asset('images/lg_saavedra.png') }}">
</head>
<style>
    .contenedor{
        margin: 20px;
    }
    table{
        width: 100%;
        border-collapse: collapse;
        text-align: center;
        padding: 10px;
    }
</style>
<body>
    <h1>Informacion de la orden de trabajo: {{$workOrder->id}}</h1>
    <div class="contenedor">
        <label class="title-wo">Orden de trabajo: {{ $workOrder->id }}</label><br>
        <label class="molding title-wo">Moldura: {{ $molding->nombre }}</label><br>
        <label class="title-wo">Fecha y hora de creacion: {{ $workOrder->created_at }}</label><br>
    </div>
    <table border="1">
        <thead>
            <tr>
                <th>Clase</th>
                <th>Tamaño o seccion</th>
                <th>Piezas con consignación</th>
                <th>Pedido</th>
                <th style="width: 90px;">Fecha de inicio</th>
                <th style="width: 90px;">Fecha de termino</th>
                <th>Procesos</th>
            </tr>
        </thead>
        @if ($classes != null)    
            <tbody>
                @foreach($classes as $class)
                <tr>
                    <td>{{ $class->nombre }}</td>
                    @if($class->nombre == 'Obturador')
                        <td>{{ $class->seccion }}</td>
                    @else
                        <td>{{ $class->tamanio }}</td>
                    @endif
                    <td>{{ $class->piezas }}</td>
                    <td>{{ $class->pedido }}</td>
                    <td>{{ $class->fecha_inicio }}</td>
                    <td>{{ $class->fecha_termino }}</td>
                    @if ($processes != null)
                        @if ($processes[$class->id] != null)
                            <td>{{ $processes[$class->id ]}}</td>
                        @else
                            <td>Sin procesos establecidos</td>
                        @endif
                    @else
                        <td>Sin procesos establecidos</td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        @else
            <tbody>
                <tr>
                    <td colspan="5">No hay clases registradas</td>
                </tr>
            </tbody>
        @endif
    </table>
</body>

</html>