<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte Soldadura PTA</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.15); }
        .header { background: #033966; color: #fff; padding: 28px 32px; text-align: center; }
        .header h1 { margin: 0; font-size: 22px; font-weight: 800; }
        .header p  { margin: 6px 0 0; font-size: 13px; opacity: .85; }
        .body { padding: 32px; }
        .body p  { font-size: 14px; color: #333; line-height: 1.6; }
        .info-box { background: #e8f0fe; border-left: 4px solid #033966; border-radius: 4px; padding: 16px 20px; margin: 20px 0; }
        .info-box strong { color: #033966; font-size: 13px; text-transform: uppercase; display: block; margin-bottom: 4px; }
        .info-box span { font-size: 15px; color: #111; font-weight: 700; }
        .footer { background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 16px 32px; text-align: center; font-size: 11px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Reporte de Inspección Soldadura PTA</h1>
            <p>Grupo Industrial Saavedra — Sistema de Control de Calidad</p>
        </div>
        <div class="body">
            <p>Estimado equipo,</p>
            <p>
                Se adjunta el <strong>Reporte de Inspección de Soldadura PTA</strong> generado desde el
                Sistema de Control de Producción de Grupo Industrial Saavedra.
            </p>

            <div class="info-box">
                <strong>Orden de Trabajo</strong>
                <span>{{ $otNombre }}</span>
            </div>
            <div class="info-box">
                <strong>Clase</strong>
                <span>{{ $claseNombre }}</span>
            </div>
            <div class="info-box">
                <strong>Fecha de generación</strong>
                <span>{{ now()->translatedFormat('d \d\e F \d\e Y, H:i \h\r\s') }}</span>
            </div>

            <p>
                Por favor revise el PDF adjunto para consultar los resultados completos de inspección,
                medidas técnicas y evidencia fotográfica.
            </p>
            <p style="font-size:12px; color:#666;">
                Este correo fue generado automáticamente por el sistema. No responder a este mensaje.
            </p>
        </div>
        <div class="footer">
            Grupo Industrial Saavedra &copy; {{ date('Y') }} — Sistema de Control de Producción
        </div>
    </div>
</body>
</html>
