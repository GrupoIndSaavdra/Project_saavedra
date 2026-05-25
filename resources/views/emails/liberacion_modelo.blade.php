<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liberacion de Modelo - {{ strtoupper($estado) }}</title>
</head>
<body style="margin:0; padding:0; background-color:#f1f5f9; font-family: Arial, Helvetica, sans-serif; font-size:15px; color:#334155;">

<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f1f5f9; padding: 30px 0;">
    <tr>
        <td align="center">
            <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-radius:10px; overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,0.08);">

                {{-- ── ENCABEZADO ── --}}
                <tr>
                    <td style="background-color:{{ $estado === 'aprobado' ? '#005194' : '#9c0300' }}; padding:28px 32px; text-align:center;">
                        <p style="margin:0; font-size:13px; color:rgba(255,255,255,0.75); letter-spacing:1px; text-transform:uppercase;">
                            Grupo Industrial Saavedra — Calidad
                        </p>
                        <h1 style="margin:10px 0 0 0; font-size:22px; font-weight:700; color:#ffffff; line-height:1.3;">
                            @if($estado === 'aprobado')
                                ✅ Liberación de Modelo APROBADA
                            @else
                                ❌ Liberación de Modelo RECHAZADA
                            @endif
                        </h1>
                    </td>
                </tr>

                {{-- ── CUERPO ── --}}
                <tr>
                    <td style="padding:32px;">

                        <p style="margin:0 0 20px 0; line-height:1.7;">
                            @if($estado === 'aprobado')
                                Se ha <strong>aprobado</strong> la liberación del modelo de Fundición
                                correspondiente a la Orden de Trabajo <strong>{{ $ot }}</strong>.
                                El modelo está autorizado para continuar con el proceso de producción.
                            @else
                                Se ha <strong>rechazado</strong> la liberación del modelo de Fundición
                                correspondiente a la Orden de Trabajo <strong>{{ $ot }}</strong>.
                                Se requiere revisión por parte del área responsable antes de continuar.
                            @endif
                        </p>

                        {{-- Tabla de detalles --}}
                        <table width="100%" cellpadding="0" cellspacing="0"
                               style="border-collapse:collapse; border:1px solid #e2e8f0; border-radius:8px; overflow:hidden; margin-bottom:24px;">
                            <tr style="background-color:#f8fafc;">
                                <td style="padding:10px 16px; font-weight:700; color:#64748b; width:40%; border-bottom:1px solid #e2e8f0; font-size:13px; text-transform:uppercase; letter-spacing:0.5px;">
                                    Orden de Trabajo
                                </td>
                                <td style="padding:10px 16px; color:#0f172a; font-weight:600; border-bottom:1px solid #e2e8f0;">
                                    {{ $ot }}
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:10px 16px; font-weight:700; color:#64748b; border-bottom:1px solid #e2e8f0; font-size:13px; text-transform:uppercase; letter-spacing:0.5px;">
                                    Estado
                                </td>
                                <td style="padding:10px 16px; border-bottom:1px solid #e2e8f0;">
                                    <span style="display:inline-block; padding:3px 12px; border-radius:20px; font-size:13px; font-weight:700;
                                        background-color:{{ $estado === 'aprobado' ? '#dcfce7' : '#fee2e2' }};
                                        color:{{ $estado === 'aprobado' ? '#166534' : '#991b1b' }};">
                                        {{ strtoupper($estado) }}
                                    </span>
                                </td>
                            </tr>
                            <tr style="background-color:#f8fafc;">
                                <td style="padding:10px 16px; font-weight:700; color:#64748b; border-bottom:1px solid #e2e8f0; font-size:13px; text-transform:uppercase; letter-spacing:0.5px;">
                                    Revisado por
                                </td>
                                <td style="padding:10px 16px; color:#0f172a; border-bottom:1px solid #e2e8f0;">
                                    {{ $userCalidad ?? 'Departamento de Calidad' }}
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:10px 16px; font-weight:700; color:#64748b; {{ ($observaciones ?? null) || ($motivoRechazo ?? null) ? 'border-bottom:1px solid #e2e8f0;' : '' }} font-size:13px; text-transform:uppercase; letter-spacing:0.5px;">
                                    Fecha de Revisión
                                </td>
                                <td style="padding:10px 16px; color:#0f172a; {{ ($observaciones ?? null) || ($motivoRechazo ?? null) ? 'border-bottom:1px solid #e2e8f0;' : '' }}">
                                    {{ now()->format('d/m/Y H:i') }}
                                </td>
                            </tr>
                            @if(!empty($observaciones))
                            <tr style="background-color:#f8fafc;">
                                <td style="padding:10px 16px; font-weight:700; color:#64748b; {{ $estado === 'rechazado' && !empty($motivoRechazo) ? 'border-bottom:1px solid #e2e8f0;' : '' }} font-size:13px; text-transform:uppercase; letter-spacing:0.5px;">
                                    Observaciones
                                </td>
                                <td style="padding:10px 16px; color:#475569; font-style:italic; {{ $estado === 'rechazado' && !empty($motivoRechazo) ? 'border-bottom:1px solid #e2e8f0;' : '' }}">
                                    {{ $observaciones }}
                                </td>
                            </tr>
                            @endif
                            @if($estado === 'rechazado' && !empty($motivoRechazo))
                            <tr>
                                <td style="padding:10px 16px; font-weight:700; color:#9c0300; font-size:13px; text-transform:uppercase; letter-spacing:0.5px;">
                                    Motivo de Rechazo
                                </td>
                                <td style="padding:10px 16px; color:#7f1d1d; font-weight:600;">
                                    {{ $motivoRechazo }}
                                </td>
                            </tr>
                            @endif
                        </table>

                        {{-- Botón CTA removido a petición del usuario --}}

                    </td>
                </tr>

                {{-- ── PIE ── --}}
                <tr>
                    <td style="background-color:#f8fafc; border-top:1px solid #e2e8f0; padding:18px 32px; text-align:center; font-size:12px; color:#94a3b8;">
                        Este es un correo automático del sistema de control de Liberación de Modelos de Fundición.<br>
                        <strong style="color:#64748b;">GRUPO INDUSTRIAL SAAVEDRA</strong>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>

</body>
</html>
