<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Reporte de Producción — {{ $fecha->format('d/m/Y') }}</title>
    {{--
    Los clientes de correo no cargan CSS externos.
    Los estilos se inyectan directamente desde el archivo CSS del proyecto.
    --}}
    <style>
        {!! file_get_contents(resource_path('css/reportes/email.css')) !!}
    </style>
</head>

<body>

    <div class="header">
        <h1>Reporte General de Producción</h1>
        <p>Grupo Industrial Saavedra</p>
        <span class="badge">{{ $fecha->translatedFormat('l, d \d\e F \d\e Y') }}</span>

        <div style="margin-top: 20px;">
            <a href="{{ route('reportes.descargar_pdf', ['fecha' => $fecha->toDateString()]) }}" class="btn-pdf"
                style="background-color: #00b913; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;">
                Descargar PDF
            </a>
        </div>

    </div>


    {{-- CONTENIDO PRINCIPAL --}}
    @if(empty($reporte))
        <div class="sin-datos">
            <p>No se registró producción en este turno.</p>
        </div>
    @else
        {{-- LEYENDA HORIZONTAL REQUISITADA --}}
        <div
            style="margin: 20px auto; width: 100%; max-width: 1200px; background: #fff; padding: 15px; border: 1px solid #eee; border-radius: 8px;">
            <div
                style="font-weight: bold; margin-bottom: 15px; color: #333; text-align: center; border-bottom: 2px solid #00b913; padding-bottom: 5px; font-size: 1.1em;">
                GUÍA DE COLORES Y PRODUCTIVIDAD
            </div>

            <p style="font-size: 0.85em; font-weight: bold; color: #666; margin-bottom: 8px; margin-top: 0;">
                Productividad (<span style="color: #00b003;">Meta</span> y <span style="color: #0054ad;">Juegos
                    Realizados</span>):
            </p>
            <table style="width: 100%; border-collapse: collapse; table-layout: fixed; margin-bottom: 5px;">
                <tr>
                    <td style="padding: 2px;">
                        <div style="background: #9b59b6; height: 12px; border-radius: 2px;"></div>
                    </td>
                    <td style="padding: 2px;">
                        <div style="background: #f1c40f; height: 12px; border-radius: 2px;"></div>
                    </td>
                    <td style="padding: 2px;">
                        <div style="background: #27ae60; height: 12px; border-radius: 2px;"></div>
                    </td>
                    <td style="padding: 2px;">
                        <div style="background: #e67e22; height: 12px; border-radius: 2px;"></div>
                    </td>
                    <td style="padding: 2px;">
                        <div style="background: #e74c3c; height: 12px; border-radius: 2px;"></div>
                    </td>
                </tr>
                <tr style="font-size: 0.75em; text-align: center; color: #333;">
                    <td style="padding-top: 3px;"><b>+150%</b><br>Excelencia</td>
                    <td style="padding-top: 3px;"><b>100-149%</b><br>Destacado</td>
                    <td style="padding-top: 3px;"><b>75-99%</b><br>Aceptable</td>
                    <td style="padding-top: 3px;"><b>40-74%</b><br>Medio</td>
                    <td style="padding-top: 3px;"><b>0-39%</b><br>Bajo</td>
                </tr>
            </table>
            <p style="font-size: 0.7em; color: #777; font-style: italic; margin-bottom: 15px; text-align: center;">
                * Nota: El color del texto <strong>'Juegos Realizados'</strong> en las tablas varía según el promedio de
                productividad
                alcanzado.
            </p>

            <table style="width: 100%; border-collapse: collapse; table-layout: fixed;">
                <tr>
                    <td colspan="5" style="font-size: 0.85em; font-weight: bold; color: #666; padding-bottom: 5px;">Estado
                        de Piezas (Fondo de Fila):</td>
                </tr>
                <tr>
                    <td style="padding: 2px;">
                        <div style="background: #79BFED; height: 12px; border-radius: 2px; border: 1px solid #ddd;"></div>
                    </td>
                    <td style="padding: 2px;">
                        <div style="background: #90EE90; height: 12px; border-radius: 2px; border: 1px solid #ddd;"></div>
                    </td>
                    <td style="padding: 2px;">
                        <div style="background: #FFD700; height: 12px; border-radius: 2px; border: 1px solid #ddd;"></div>
                    </td>
                    <td style="padding: 2px;">
                        <div style="background: #DDA0DD; height: 12px; border-radius: 2px; border: 1px solid #ddd;"></div>
                    </td>
                    <td style="padding: 2px;">
                        <div style="background: #FF6B6B; height: 12px; border-radius: 2px; border: 1px solid #ddd;"></div>
                    </td>
                </tr>
                <tr style="font-size: 0.75em; text-align: center; color: #333;">
                    <td style="padding-top: 3px;">Liberado</td>
                    <td style="padding-top: 3px;">Buena s/lib.</td>
                    <td style="padding-top: 3px;">Incompleto</td>
                    <td style="padding-top: 3px;">Mala s/lib.</td>
                    <td style="padding-top: 3px;">Rechazado</td>
                </tr>
            </table>
        </div>

        {{-- NIVEL 1: Operador --}}
        @foreach ($reporte as $nombreOperador => $filas)
            @php
                // Cálculo de promedio de productividad para el operador
                $totalMeta = 0;
                $totalRealizados = 0;
                foreach ($filas as $f) {
                    $totalMeta += (float) ($f['meta'] ?? 0);
                    $totalRealizados += (float) ($f['juegos_realizados'] ?? 0);
                }

                $promedioOp = $totalMeta > 0 ? ($totalRealizados / $totalMeta) * 100 : 0;
                $colorHeaderRealizados = "#e74c3c"; // Rojo (Bajo) por defecto

                if ($promedioOp >= 150)
                    $colorHeaderRealizados = "#9b59b6";
                elseif ($promedioOp >= 100)
                    $colorHeaderRealizados = "#f1c40f";
                elseif ($promedioOp >= 75)
                    $colorHeaderRealizados = "#27ae60";
                elseif ($promedioOp >= 40)
                    $colorHeaderRealizados = "#e67e22";
            @endphp
            <div class="operador-section" style="margin-bottom: 30px;">
                <div class="operador-header"
                    style="background: #f8f9fa; padding: 10px 15px; border-left: 5px solid #00b913; margin-bottom: 10px; font-weight: bold; font-size: 1.1em; color: #333;">
                    Operador: {{ $nombreOperador }}
                    <span style="float: right; font-weight: normal; font-size: 0.9em; color: #666;">
                        {{ count($filas) }} {{ count($filas) === 1 ? 'registro' : 'registros' }}
                    </span>
                </div>

                <table class="op-table" style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                    <thead>
                        <tr>
                            <th style="width: 20%; text-align: center;">Orden de Trabajo</th>
                            <th style="width: 15%; text-align: center;">Proceso</th>
                            <th style="width: 12%; text-align: center;">Número de Juego</th>
                            <th style="width: 8%; text-align: center; color: #2ecc71;">Meta</th>
                            <th style="width: 10%; text-align: center; color: {{ $colorHeaderRealizados }};">Juegos Realizados
                            </th>
                            <th style="width: 17.5%; text-align: center;">Observaciones Operador</th>
                            <th style="width: 17.5%; text-align: center;">Observaciones Calidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($filas as $fila)
                            @php
                                $meta = $fila['meta'] > 0 ? $fila['meta'] : 1;
                                $realPct = ($fila['juegos_realizados'] / $meta) * 100;
                                if ($realPct >= 150) {
                                    $barColor = "#9b59b6"; // Platino/Morado brillante (Excelencia)
                                } elseif ($realPct >= 100) {
                                    $barColor = "#f1c40f"; // Dorado (Esfuerzo destacado)
                                } elseif ($realPct >= 75) {
                                    $barColor = "#27ae60"; // Verde (Aceptable)
                                } elseif ($realPct >= 40) {
                                    $barColor = "#e67e22"; // Naranja (Medio)
                                } else {
                                    $barColor = "#e74c3c"; // Rojo (Bajo)
                                }
                            @endphp
                            <tr style="background-color: {{ $fila['bg_color'] ?? 'white' }};">
                                <td style="font-size: 0.9em;">{{ $fila['ot_label'] }}</td>
                                <td style="font-size: 0.9em;">{{ $fila['proceso'] }}</td>
                                <td style="text-align: center;"><strong>{{ $fila['n_piezas'] }}</strong></td>
                                <td style="text-align: center;">{{ $fila['meta'] }}</td>
                                <td style="background-color: {{ $barColor }}; color: white; font-weight: bold; text-align: center;">
                                    {{ $fila['juegos_realizados'] }}
                                </td>
                                <td style="font-size: 0.85em;">{{ $fila['obs_operador'] }}</td>
                                <td style="font-size: 0.85em;">{{ $fila['obs_calidad'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    @endif

    <div class="footer">
        Reporte automático · {{ $fecha->format('d/m/Y') }} ·
        Este correo fue generado automáticamente, no responder.
    </div>

</body>

</html>
