<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="icon" href="{{ url('images/lg_saavedra.png') }}?v=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de piezas</title>
    <link rel="icon" type="image/png" href="{{ asset('images/lg_saavedra.png') }}">
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

        .table-filters,
        .table-colors {
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
                    <?php
                    $colorsArray = ['Azul' => 'Liberado', 'Rojo' => 'Rechazado', 'Verde' => 'Buena sin liberacion/rechazo', 'Morado' => 'Mala sin liberacion/rechazo', 'Amarillo' => 'Incompleto'];
                    $colorStyles = [
                        'Azul' => 'background-color: #79BFED; color: black; font-weight: bold;',
                        'Rojo' => 'background-color: #FF6B6B; color: black; font-weight: bold;',
                        'Verde' => 'background-color: #90EE90; color: black; font-weight: bold;',
                        'Morado' => 'background-color: #DDA0DD; color: black; font-weight: bold;',
                        'Amarillo' => 'background-color: #FFD700; color: black; font-weight: bold;',
                    ];
                    ?>
                    @foreach ($colorsArray as $key => $colorArray)
                        <tr>
                            <td style="{{ $colorStyles[$key] }}">{{ $key }}</td>
                            <td>{{ $colorArray }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="filters">
            <h2>Filtros aplicados</h2>
            <?php $titles = [
                'workOrder' => 'Orden de trabajo',
                'class' => 'Clase',
                'operator' => 'Operador',
                'machine' => 'Maquina',
                'process' => 'Proceso',
                'error' => 'Error',
                'dateFrom' => 'Desde',
                'dateTo' => 'Hasta',
                'n_juego' => 'N# Pieza',
                'status' => 'Estado',
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
                            @if ($key == 'operator' && $filter != 'Todos')
                                <td>{{ $filter->nombre }} {{ $filter->a_paterno }} {{ $filter->a_materno }}</td>
                            @elseif ($key == 'status' && $filter != 'Todos')
                                <?php
                                $statusNames = [
                                    '#79BFED' => 'Liberadas',
                                    '#FF6B6B' => 'Rechazadas',
                                    '#90EE90' => 'Buenas sin liberación',
                                    '#DDA0DD' => 'Malas sin liberación',
                                    '#FFD700' => 'Incompletas'
                                ];
                                ?>
                                <td>{{ $statusNames[strtoupper($filter)] ?? $filter }}</td>
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
                $colorColumn = '#FFFFFF'; // default blanco
                $indexLiberation = 9;
                $indexError      = 5;
                $libVal = $pieces[$i][$indexLiberation] ?? null;
                $errVal = $pieces[$i][$indexError] ?? 'Ninguno';
                switch (true) {
                    case $libVal == 1:
                        $colorColumn = '#79BFED'; // Azul - Liberado
                        break;
                    case $libVal == 2:
                        $colorColumn = '#FF6B6B'; // Rojo - Rechazado
                        break;
                    default:
                        if (str_contains($errVal, 'Incompleto')) {
                            $colorColumn = '#FFD700'; // Amarillo - Incompleto
                        } elseif ($errVal === 'Ninguno') {
                            $colorColumn = '#90EE90'; // Verde - Buena sin liberación
                        } else {
                            $procName = $pieces[$i][4] ?? '';
                            if ($procName === 'Soldadura PTA' && !str_contains(strtolower($errVal), 'fundicion') && !str_contains(strtolower($errVal), 'fundición')) {
                                $colorColumn = '#90EE90';
                            } else {
                                $colorColumn = '#DDA0DD'; // Morado - Mala sin liberación
                            }
                        }
                        break;
                }
                ?>

                <tr style="background-color: {{ $colorColumn }}">
                    {{-- Columnas fijas visibles: [1]=Juego [2]=Operador [3]=Maquina [4]=Proceso [5]=Error [6]=Fecha maquinado [7]=Fecha liberacion [8]=Liberado por --}}
                    @for ($j = 1; $j <= 8; $j++)
                        <td>{{ $pieces[$i][$j] ?? '' }}</td>
                    @endfor
                </tr>
            @endfor
        </tbody>
    </table>
    </div>
</body>

</html>
