<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Código QR - Grupo Industrial Saavedra</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            text-align: center;
        }
        .header {
            margin-bottom: 30px;
            border-bottom: 3px solid #2c3e50;
            padding-bottom: 20px;
        }
        .title {
            font-size: 28px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        .subtitle {
            font-size: 18px;
            color: #7f8c8d;
            margin-bottom: 10px;
        }
        .company {
            font-size: 14px;
            color: #95a5a6;
        }
        .qr-container {
            margin: 30px auto;
            padding: 20px;
            border: 2px solid #3498db;
            border-radius: 10px;
            display: inline-block;
            background-color: #f8f9fa;
        }
        .info-section {
            margin-top: 30px;
            text-align: left;
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
        }
        .info-row {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #ecf0f1;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
        }
        .info-label {
            font-weight: bold;
            color: #2c3e50;
        }
        .info-value {
            color: #34495e;
        }
        .footer {
            margin-top: 40px;
            font-size: 12px;
            color: #95a5a6;
            border-top: 1px solid #bdc3c7;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">GRUPO INDUSTRIAL SAAVEDRA</div>
        <div class="subtitle">Código QR - Registro de Soldadura</div>
        <div class="company">Sistema de Gestión de Calidad</div>
    </div>

    <div class="qr-container">
        <img src="{{ $qrImage }}" alt="Código QR" style="width: 250px; height: 250px; margin: 0 auto; display: block;">
    </div>

    <div class="info-section">
        <div class="info-row">
            <span class="info-label">ID Operador:</span>
            <span class="info-value">{{ $qrData['id_operador'] }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">ID Soldadura:</span>
            <span class="info-value">{{ $qrData['id_soldadura'] }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Fecha Entrega:</span>
            <span class="info-value">{{ $qrData['fecha_entrega'] }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Cantidad:</span>
            <span class="info-value">{{ $qrData['cantidad_entregada'] }} kg</span>
        </div>
    </div>

    <div class="footer">
        <p><strong>Generado:</strong> {{ date('d/m/Y H:i:s') }}</p>
        <p><strong>Contenido QR:</strong> {{ $qrData['contenido_qr'] }}</p>
        <p><strong>Grupo Industrial Saavedra</strong> - Sistema de Trazabilidad</p>
    </div>
</body>
</html>