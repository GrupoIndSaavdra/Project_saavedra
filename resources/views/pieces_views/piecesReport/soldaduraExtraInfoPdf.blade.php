<!DOCTYPE html>
<html>

<head>
    <title>Reporte de Soldadura - Información Extra</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #033966;
            color: white;
            font-weight: bold;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h2 {
            margin: 0;
            color: #033966;
        }

        .header p {
            margin: 5px 0 0 0;
            color: #666;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>Reporte de Información Extra - Soldadura</h2>
        <p>Fecha de generación: {{ date('d-m-Y H:i') }}</p>
        <p>Total de piezas: {{ count($piecesData) }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>N° Juego</th>
                <th>Operador</th>
                <th>Clase</th>
                <th>OT</th>
                <th>Peso por Pieza</th>
                <th>Tipo Soldadura</th>
                <th>Lote</th>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Observaciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($piecesData as $piece)
                <tr>
                    <td>{{ $piece['n_juego'] }}</td>
                    <td>{{ $piece['operador'] }}</td>
                    <td>{{ $piece['clase'] }}</td>
                    <td>{{ $piece['orden_trabajo'] }}</td>
                    <td>{{ $piece['peso_pieza'] }}</td>
                    <td>{{ $piece['tipo_soldadura'] }}</td>
                    <td>{{ $piece['lote'] }}</td>
                    <td>{{ $piece['fecha'] }}</td>
                    <td>{{ $piece['hora'] }}</td>
                    <td>{{ $piece['observaciones'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>