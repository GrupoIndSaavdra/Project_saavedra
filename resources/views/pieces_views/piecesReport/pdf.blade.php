<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Piezas</title>
    <style>
        @page {
            size: letter portrait;
            margin: 0.8cm;
        }

        body {
            font-family: "Helvetica", "Arial", sans-serif;
            font-size: 10px;
            color: #333;
            margin: 0;
            padding: 0;
            line-height: 1.3;
        }

        /* --- Header Table --- */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border: none;
        }

        .header-table td {
            border: none;
            padding: 0;
            vertical-align: middle;
        }

        .logo-container {
            width: 20%;
        }

        .logo-img {
            height: 40px;
        }

        .title-container {
            width: 60%;
            text-align: center;
        }

        .title-container h1 {
            color: #033966;
            font-size: 16px;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: bold;
        }

        .title-container p {
            margin: 3px 0 0;
            font-size: 9px;
            color: #666;
            font-weight: bold;
            text-transform: uppercase;
        }

        .meta-container {
            width: 20%;
            text-align: right;
            font-size: 9px;
            color: #555;
        }

        /* --- Column Layout Table --- */
        .columns-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            border: none;
        }

        .columns-table td {
            border: none;
            padding: 0;
            vertical-align: top;
        }

        .section-title {
            font-size: 11px;
            color: #033966;
            margin-top: 0;
            margin-bottom: 6px;
            text-transform: uppercase;
            font-weight: bold;
            border-bottom: 2px solid #033966;
            padding-bottom: 3px;
        }

        /* --- General Tables --- */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            background-color: #fff;
        }

        th, td {
            border: 1px solid #b0c4de;
            padding: 4px 6px;
            text-align: center;
            vertical-align: middle;
        }

        th {
            background-color: #033966;
            color: #ffffff;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        /* --- Custom Row Colors (Pastel for readability) --- */
        .color-badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 9px;
            color: #000;
        }
    </style>
</head>

<body>

    <!-- Header Section -->
    <table class="header-table">
        <tr>
            <td class="logo-container">
                <div style="background-color: #033966; color: #ffffff; padding: 6px 12px; font-weight: bold; border-radius: 4px; display: inline-block; font-size: 14px; letter-spacing: 1px; text-align: center;">
                    GIS
                </div>
            </td>
            <td class="title-container">
                <h1>Reporte de Piezas</h1>
                <p>Grupo Industrial Saavedra</p>
            </td>
            <td class="meta-container">
                <strong>Fecha impresión:</strong><br>
                {{ date('d/m/Y H:i') }}
            </td>
        </tr>
    </table>

    <!-- Two-column metadata (Colors and Filters) -->
    <table class="columns-table">
        <tr>
            <td style="width: 48%;">
                <div class="section-title">Tabla de colores</div>
                <table class="table-colors">
                    <thead>
                        <tr>
                            <th style="width: 30%;">Color</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $colorsArray = [
                            'Azul' => 'Liberado',
                            'Rojo' => 'Rechazado',
                            'Verde' => 'Buena sin liberación/rechazo',
                            'Morado' => 'Mala sin liberación/rechazo',
                            'Amarillo' => 'Incompleto'
                        ];
                        $colorStyles = [
                            'Azul' => 'background-color: #79BFED; color: #083c5d;',
                            'Rojo' => 'background-color: #FF6B6B; color: #5a0c0c;',
                            'Verde' => 'background-color: #90EE90; color: #0f4d0f;',
                            'Morado' => 'background-color: #DDA0DD; color: #4b104b;',
                            'Amarillo' => 'background-color: #FFD700; color: #574600;',
                        ];
                        ?>
                        @foreach ($colorsArray as $key => $colorArray)
                            <tr>
                                <td>
                                    <span class="color-badge" style="{{ $colorStyles[$key] }}">{{ $key }}</span>
                                </td>
                                <td>{{ $colorArray }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </td>
            
            <td style="width: 4%;"></td> <!-- Separator -->
            
            <td style="width: 48%;">
                <div class="section-title">Filtros aplicados</div>
                <?php $titles = [
                    'workOrder' => 'Orden de trabajo',
                    'class' => 'Clase',
                    'operator' => 'Operador',
                    'machine' => 'Máquina',
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
                        <tr>
                            <th>Filtro</th>
                            <th>Valor elegido</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($selectedItems as $key => $filter)
                            <tr>
                                <td style="font-weight: bold; color: #033966;">{{ $titles[$key] ?? $key }}</td>
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
            </td>
        </tr>
    </table>

    <!-- Main Pieces Table -->
    <div class="section-title">Detalle de Piezas</div>
    <table class="table-pieces">
        <thead>
            <tr>
                <th style="width: 8%;">Juego</th>
                <th>Nombre del Operador</th>
                <th style="width: 12%;">Máquina</th>
                <th style="width: 15%;">Proceso</th>
                <th>Errores</th>
                <th style="width: 12%;">Fecha Máquinado</th>
                <th style="width: 12%;">Fecha Liberación</th>
                <th style="width: 12%;">Liberado Por</th>
            </tr>
        </thead>
        <tbody>
            @for ($i = 0; $i < count($pieces); $i++)
                <?php
                $colorColumn = '#FFFFFF';
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
                <tr style="background-color: {{ $colorColumn }};">
                    @for ($j = 1; $j <= 8; $j++)
                        <td>{{ $pieces[$i][$j] ?? '' }}</td>
                    @endfor
                </tr>
            @endfor
        </tbody>
    </table>

</body>

</html>
