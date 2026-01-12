<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>QR Soldadura Individual - Grupo Industrial Saavedra</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 5px;
            font-size: 9px;
        }

        .container {
            background: rgba(255, 255, 255, 0.96);
            border-radius: 0.5rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            width: 40%;
            margin: 20px;
        }

        .header {
            margin-bottom: 8px;
            border-bottom: 1px solid #1f3a5f;
            padding-bottom: 5px;
            text-align: center;
        }

        .title {
            font-size: 14px;
            font-weight: 600;
            color: #1f3a5f;
            margin-bottom: 4px;
        }

        .subtitle {
            font-size: 12px;
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
            margin-bottom: 6px;
            padding: 6px 10px;
            background-color: #f9f9f9;
            border-radius: 0.3rem;
            font-size: 11px;
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
    <div class="container">
        <div class="header">
            <div class="title">GRUPO INDUSTRIAL SAAVEDRA</div>
            <div class="subtitle">QR - Soldadura Individual</div>
        </div>

        <div class="qr-container">
            <img src="{{ $qrs[0]['qr_image'] }}" alt="Código QR" style="width: 180px; height: 180px;">
        </div>

        <div class="info-section">
            <div class="info-row">
                <span class="info-label">Operador:</span>
                <span class="info-value">
                    @if($operador)
                        {{ $operador->matricula }} - {{ $operador->nombre }} {{ $operador->a_paterno }}
                    @else
                        Sin operador asignado
                    @endif
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Soldadura:</span>
                <span class="info-value">{{ $nombre }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Lote:</span>
                <span class="info-value">{{ $lote }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Fecha Generación:</span>
                <span class="info-value">{{ $fecha }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Kilos:</span>
                <span class="info-value">{{ $qrs[0]['kilos'] }} kg</span>
            </div>
        </div>

        <div class="footer">
            <p><strong>Generado:</strong> {{ date('d/m/Y H:i:s') }}</p>
            <p><strong>Grupo Industrial Saavedra</strong></p>
        </div>
    </div>
</body>

</html>