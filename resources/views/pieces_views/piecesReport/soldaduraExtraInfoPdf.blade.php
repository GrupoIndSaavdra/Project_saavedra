<!DOCTYPE html>
<html>

<head>
    <title>Reporte de Soldadura - Información Extra</title>
    <link rel="icon" href="{{ url('images/lg_saavedra.png') }}?v=1">
    <link rel="stylesheet" href="{{ public_path('css/pieces_views/piecesReport/soldaduraExtraInfoPdf.css') }}">
    <link rel="icon" type="image/png" href="{{ asset('images/lg_saavedra.png') }}">
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
                <th>Soldadura</th>
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
                    <td>{{ $piece['material_soldadura'] ?? 'N/A' }}</td>
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
