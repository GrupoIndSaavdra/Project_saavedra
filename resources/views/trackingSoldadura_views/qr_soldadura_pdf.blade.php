<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>QR Soldadura - Grupo Industrial Saavedra</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 5px;
            font-size: 9px;
        }

        .page-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-template-rows: 1fr 1fr;
            gap: 5px;
            width: 45%;
            height: 250mm;
            page-break-inside: avoid;
        }

        .container {
            background: rgba(255, 255, 255, 0.96);
            border-radius: 0.5rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            padding: 10px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
        }

        .header {
            margin-bottom: 8px;
            border-bottom: 1px solid #1f3a5f;
            padding-bottom: 5px;
            text-align: center;
        }

        .title {
            font-size: 10px;
            font-weight: 600;
            color: #1f3a5f;
            margin-bottom: 2px;
        }

        .subtitle {
            font-size: 8px;
            color: #1f3a5f;
            font-weight: 500;
        }

        .qr-container {
            margin: 8px 0;
            padding: 2px;
            border: 1px solid #1f3a5f;
            border-radius: 0.5rem;
            background-color: #ffffff;
            text-align: center;
        }

        .info-section {
            flex-grow: 1;
        }

        .info-row {
            margin-bottom: 3px;
            padding: 3px 5px;
            background-color: #f9f9f9;
            border-radius: 0.3rem;
            font-size: 7px;
            border: 1px solid #e0e0e0;
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
            margin-top: 5px;
            font-size: 5px;
            color: #1f3a5f;
            border-top: 1px solid #1f3a5f;
            padding-top: 3px;
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
    <div class="page-container">
        @for ($i = 0; $i < 4; $i++)
            <div class="container">
                <div class="header">
                    <div class="title">GRUPO INDUSTRIAL SAAVEDRA</div>
                    <div class="subtitle">QR - Registro de Soldadura</div>
                </div>

                <div class="qr-container">
                    <img src="{{ $qrImage }}" alt="Código QR" style="width: 80px; height: 80px;">
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
        @endfor
    </div>
</body>

</html>