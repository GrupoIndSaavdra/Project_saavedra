<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>QR Soldadura - Grupo Industrial Saavedra</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 15px;
            font-size: 12px;
        }

        .container {
            width: 300px;
            float: left;
            background: rgba(255, 255, 255, 0.96);
            border-radius: 1.2rem;
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.18);
            padding: 20px;
        }

        .header {
            margin-bottom: 15px;
            border-bottom: 2px solid #1f3a5f;
            padding-bottom: 10px;
            text-align: center;
        }

        .title {
            font-size: 16px;
            font-weight: 600;
            color: #1f3a5f;
            margin-bottom: 3px;
        }

        .subtitle {
            font-size: 12px;
            color: #1f3a5f;
            margin-bottom: 5px;
            font-weight: 500;
        }

        .company {
            font-size: 10px;
            color: #1f3a5f;
        }

        .qr-container {
            margin: 15px 0;
            padding: 15px;
            border: 2px solid #1f3a5f;
            border-radius: 1rem;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.12);
            background-color: #ffffff;
            text-align: center;
        }

        .info-section {
            margin-top: 15px;
        }

        .info-row {
            margin-bottom: 8px;
            padding: 8px;
            background-color: #f9f9f9;
            border-radius: 0.6rem;
            font-size: 10px;
            border: 1px solid #cfcfcf;
        }

        .info-label {
            font-weight: 500;
            color: #1f3a5f;
        }

        .info-value {
            color: #1f3a5f;
            font-weight: normal;
        }

        .footer {
            margin-top: 20px;
            font-size: 8px;
            color: #1f3a5f;
            border-top: 1px solid #1f3a5f;
            padding-top: 10px;
            text-align: center;
        }

        .footer p {
            margin: 2px 0;
        }

        .footer strong {
            font-weight: 600;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <div class="title">GRUPO INDUSTRIAL SAAVEDRA</div>
            <div class="subtitle">QR - Registro de Soldadura</div>
        </div>

        <div class="qr-container">
            <img src="{{ $qrImage }}" alt="Código QR" style="width: 120px; height: 120px;">
        </div>

        <div class="info-section">
            <div class="info-row">
                <span class="info-label">Soldadura:</span>
                <span class="info-value">{{ $soldadura['nombre'] }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Lote:</span>
                <span class="info-value">{{ $soldadura['lote'] }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Fecha Ingreso:</span>
                <span class="info-value">{{ $soldadura['fecha_ingreso'] }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Kilos:</span>
                <span class="info-value">{{ $soldadura['kilos'] }} kg</span>
            </div>
        </div>

        <div class="footer">
            <p><strong>Generado:</strong> {{ date('d/m/Y H:i:s') }}</p>
            <p><strong>Grupo Industrial Saavedra</strong></p>
        </div>
    </div>
</body>

</html>