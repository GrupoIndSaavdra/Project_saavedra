<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de piezas</title>
</head>

<body>
    <style>
        * {
            font-family: Arial, Helvetica, sans-serif;
        }

        .title {
            font-size: .7em;
            text-align: center;
            font-weight: bold;
        }

        h2 {
            font-size: 1rem;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #032c00cc;
            background: #fff;
            width: max-content;
            box-shadow: 0 0 10px rgba(0, 0, 0);
            font-size: 12px;
        }

        label {
            margin-right: 10px;
            /* Agrega un margen derecho para separar las etiquetas */
            font-size: 16px;
            /* Tamaño de fuente */
            color: #333;
            /* Color del texto */
            display: inline-block;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
        }

        th {
            background-color: #033966;
            color: #fff;
        }

        .principalData {
            width: 100%;
        }

        .colors,
        .filters {
            display: inline-block;
            width: 49%;
        }
        .filters {
            margin-top: 4em;
        }
        .table-filters, .table-colors {
            width: 100%;
        }

        .table-pieces {
            margin: 1em 0;
        }
    </style>

    <div class="title">
        <h1>Reporte de piezas</h1>
    </div>
    <div class="principalData">
        <div class="colors">
            <h2>Tabla de colores</h2>
            <table class="table-colors">
                <thead>
                    <tr>
                        <th>Color</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $colorsArray = ["Azul" => "Liberado", "Rojo" => "Rechazado", "Verde" => "Buena sin liberacion/rechazo", "Morado" => "Mala sin liberacion/rechazo", "Amarillo" => "Incompleto"]; ?>
                    @foreach ($colorsArray as $key => $colorArray)
                    <tr>
                        <td>{{$key}}</td>
                        <td>{{$colorArray}}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="filters">
            <h2>Filtros aplicados</h2>
            <?php $titles = [
                "workOrder" => "Orden de trabajo",
                "class" => "Clase",
                "operator" => "Operador",
                "machine" => "Maquina",
                "process" => "Proceso",
                "error" => "Error",
                "dateFrom" => "Desde",
                "dateTo" => "Hasta",
            ];
            ?>
            <table class="table-filters">
                <thead>
                    <!--Titulos de la tabla filtros-->
                    <tr>
                        <th>Filtro</th>
                        <th>Valor elegido</th>
                    </tr>
                </thead>

                <tbody>
                    <!--Valores de los filtros-->
                    @foreach ($selectedItems as $key => $filter)
                    <tr>
                        <td>{{ $titles[$key] }}</td>
                        @if ($key == "operator" && $filter != "Todos")
                        <td>{{$filter->nombre}} {{$filter->a_paterno}} {{$filter->a_materno}}</td>
                        @else
                        <td>{{ $filter }}</td>
                        @endif
                    </tr>
                    @endforeach

                </tbody>
            </table>
        </div>
    </div>
    <table class="table-pieces">
        <thead>
            <tr>
                <th>Juego</th>
                <th>Nombre del operador</th>
                <th>Máquina</th>
                <th>Proceso</th>
                @foreach ($pieces as $piece)
                @if ($piece[4] == 'Operacion Equipo')
                <th>Operación</th>
                @php
                $band = true;
                @endphp
                @break
                @endif
                @endforeach
                <th>Errores</th>
                <th>Fecha de máquinado</th>
                <th>Fecha de liberación</th>
                <th>Liberado por</th>
            </tr>
        </thead>

        <tbody>
            @for ($i = 0; $i < count($pieces); $i++)
                <!--Definicion del color de la columna con respecto a sus liberaciones y errores-->
                <?php
                $colorColumn;
                $indexLiberation = $pieces[$i][4] == "Operacion Equipo" ? 10 : 9;
                $indexError = $pieces[$i][4] == "Operacion Equipo" ? 6 : 5;
                switch (true) {
                    case $pieces[$i][9] == 1:
                        $colorColumn = "#79BFED"; // Azul
                        break;
                    case $pieces[$i][9] == 2:
                        $colorColumn = "#EC7063"; // Rojo
                        break;
                    case $pieces[$i][9] == 0:
                        $colorColumn = match ($pieces[$i][5]) {
                            "Incompleto" => "#FFFF99", // Amarillo
                            "Ninguno" => "#ACF980A8", // Verde
                            default => "#E59CFF" // Morado
                        };
                        break;
                }
                ?>

                <tr style="background-color: {{$colorColumn}}">
                    @if(isset($band))
                        @for ($j = 1; $j < count($pieces[$i]) - 4; $j++)
                            @if ($j==5)
                                <td></td>   
                                <td>{{ $pieces[$i][$j] }}</td>
                            @else
                                <td>{{ $pieces[$i][$j] }}</td>
                            @endif
                        @endfor
                    @else
                        @for ($j = 1; $j < count($pieces[$i]) - 5; $j++)
                            <td>{{ $pieces[$i][$j] }}</td>
                        @endfor
                    @endif
                </tr>
                @endfor
        </tbody>
    </table>
    </div>
</body>

</html>