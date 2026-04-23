<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <link rel="icon" href="{{ url('images/lg_saavedra.png') }}?v=1">
    <title>QR Lote - {{ $lote->matricula }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&display=swap');
        body { 
            font-family: 'Orbitron', monospace; 
            margin: 0; 
            padding: 10px;
            font-size: 10px;
        }
        .container {
            width: 100mm;
            height: 135mm;
            border: 2px solid #000;
            padding: 12px;
            box-sizing: border-box;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
        }
        .company-name {
            font-size: 16px;
            font-weight: 900;
            color: #033966;
            margin-bottom: 5px;
        }
        .qr-title {
            font-size: 14px;
            font-weight: 700;
            color: #000;
            margin-bottom: 15px;
        }
        .qr-container {
            text-align: center;
            margin: 15px 0;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 10px;
        }
        .info-table th, .info-table td {
            border: 1px solid #333;
            padding: 4px;
            text-align: left;
        }
        .info-table th {
            background-color: #f0f0f0;
            font-weight: 700;
        }
        .footer {
            margin-top: 10px;
            font-size: 8px;
            color: #666;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="company-name">GRUPO INDUSTRIAL SAAVEDRA</div>
            <div class="qr-title">QR - SOLDADURA POR LOTE</div>
        </div>

        <div class="qr-container">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&format=svg&data={{ urlencode($qrContent) }}" 
                 alt="QR Code" style="width: 160px; height: 160px;">
            <p style="font-size: 10px; margin-top: 5px; font-weight: 700;">Matrícula: {{ $lote->matricula }}</p>
        </div>

        <table class="info-table">
            <tr>
                <th>Matrícula</th>
                <td>{{ $lote->matricula }}</td>
            </tr>
            <tr>
                <th>Soldadura</th>
                <td>{{ $lote->nombre }}</td>
            </tr>
            <tr>
                <th>Lote</th>
                <td>{{ $lote->lote }}</td>
            </tr>
            <tr>
                <th>Peso Total</th>
                <td>{{ $lote->peso_total_kg }} kg</td>
            </tr>
            <tr>
                <th>Factura</th>
                <td>{{ $lote->numero_factura }}</td>
            </tr>
            <tr>
                <th>Fecha Ingreso</th>
                <td>{{ $lote->fecha_ingreso->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <th>Botes (5kg c/u)</th>
                <td>{{ $lote->cantidadBotesEsperados() }} botes</td>
            </tr>
        </table>

        <div class="footer">
            <p>{{ now()->format('d/m/Y H:i:s') }} | Sistema Tracking Soldadura</p>
        </div>
    </div>
</body>
</html>