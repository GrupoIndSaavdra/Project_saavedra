<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Alerta de Entrega de Preorden</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;">
    <div style="background-color: #ffffff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); max-width: 600px; margin: auto;">
        
        <h2 style="color: #d9534f; text-align: center;">¡Atención! Recordatorio de Entrega</h2>
        
        <p style="font-size: 16px; color: #333333;">
            Estimado proveedor y equipo de almacén,
        </p>

        <p style="font-size: 16px; color: #333333;">
            Este es un recordatorio automático para notificar que <strong>falta un día</strong> para la fecha de entrega acordada de la pre-orden de fabricación de <strong>{{ strtolower($tipo) }}</strong>.
        </p>

        <div style="background-color: #f9f9f9; padding: 15px; border-left: 4px solid #0056b3; margin-top: 20px;">
            <p style="margin: 5px 0;"><strong>Orden de Trabajo (OT):</strong> {{ $po->ot }}</p>
            <p style="margin: 5px 0;"><strong>Folio de Pre-orden:</strong> {{ $po->folio }}</p>
            <p style="margin: 5px 0;"><strong>Proveedor:</strong> {{ $po->proveedor }}</p>
            <p style="margin: 5px 0; color: #d9534f;"><strong>Fecha Compromiso (Entrega):</strong> {{ \Carbon\Carbon::parse($po->fecha_entrega)->format('d/m/Y') }}</p>
        </div>

        <p style="font-size: 14px; color: #555555; margin-top: 20px;">
            @if(empty($escaneadoPath))
                <em>Nota: El documento escaneado no se encontró adjunto en el sistema en el momento de generar esta alerta.</em>
            @else
                Se adjunta el documento escaneado de la pre-orden firmada para su referencia.
            @endif
        </p>

        <p style="font-size: 12px; color: #999999; text-align: center; margin-top: 30px; border-top: 1px solid #eeeeee; padding-top: 10px;">
            Este correo es generado automáticamente por el Sistema GIS Saavedra. Por favor, no responda a este mensaje directamente.
        </p>
    </div>
</body>
</html>
