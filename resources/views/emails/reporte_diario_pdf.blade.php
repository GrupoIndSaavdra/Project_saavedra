<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte de Producción — {{ $fecha->format('d/m/Y') }}</title>
    <style>
        {!! file_get_contents(resource_path('css/reportes/pdf.css')) !!}
    </style>
    <link rel="icon" href="{{ url('images/lg_saavedra.png') }}?v=1">
</head>

<body>

    <div class="header">
        <p>Grupo Industrial Saavedra</p>
        <h1>Reporte General de Producción</h1>
        <div class="badge">{{ $fecha->translatedFormat('l, d \d\e F \d\e Y') }}</div>
    </div>

    {{-- LEYENDA HORIZONTAL REQUISITADA --}}
    <div
        style="margin-bottom: 15px; padding: 10px; border: 1px solid #ddd; border-radius: 5px; background-color: #ffffff;">
        <div
            style="font-weight: bold; margin-bottom: 8px; color: #333; text-align: center; border-bottom: 1px solid #00b913; font-size: 10pt;">
            GUÍA DE COLORES Y PRODUCTIVIDAD
        </div>

        <table style="width: 100%; border-collapse: collapse; margin-bottom: 5px;">
            <tr>
                <td colspan="5" style="font-size: 8pt; font-weight: bold; color: #666; padding-bottom: 3px;">
                    Productividad (<span style="color: #00b003;">Meta</span> y <span style="color: #0054ad;">Juegos
                        Realizados</span>):
                </td>
            </tr>
            <tr>
                <td style="padding: 1px;">
                    <div style="background-color: #9b59b6; height: 10px;"></div>
                </td>
                <td style="padding: 1px;">
                    <div style="background-color: #f1c40f; height: 10px;"></div>
                </td>
                <td style="padding: 1px;">
                    <div style="background-color: #27ae60; height: 10px;"></div>
                </td>
                <td style="padding: 1px;">
                    <div style="background-color: #e67e22; height: 10px;"></div>
                </td>
                <td style="padding: 1px;">
                    <div style="background-color: #e74c3c; height: 10px;"></div>
                </td>
                <td style="padding: 1px;">
                    <div style="background-color: #ffffff; height: 10px; border: 1px solid #bbb;"></div>
                </td>
            </tr>
            <tr style="font-size: 7pt; text-align: center; color: #333;">
                <td><b>+150%</b><br>Excelencia</td>
                <td><b>100-149%</b><br>Destacado</td>
                <td><b>75-99%</b><br>Aceptable</td>
                <td><b>40-74%</b><br>Medio</td>
                <td><b>0-39%</b><br>Bajo</td>
                <td><b>—</b><br>Sin meta</td>
            </tr>
        </table>
        <p
            style="font-size: 6.5pt; color: #777; font-style: italic; margin-bottom: 10px; margin-top: 0; text-align: center;">
            * Nota: El color del texto <strong>'Juegos Realizados'</strong> en las tablas varía según el promedio de
            productividad
            alcanzado.
        </p>

        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td colspan="5" style="font-size: 8pt; font-weight: bold; color: #666; padding-bottom: 3px;">Estado de
                    Piezas (Color de Fila):</td>
            </tr>
            <tr>
                <td style="padding: 1px;">
                    <div style="background-color: #79BFED; height: 10px;"></div>
                </td>
                <td style="padding: 1px;">
                    <div style="background-color: #90EE90; height: 10px;"></div>
                </td>
                <td style="padding: 1px;">
                    <div style="background-color: #FFD700; height: 10px;"></div>
                </td>
                <td style="padding: 1px;">
                    <div style="background-color: #DDA0DD; height: 10px;"></div>
                </td>
                <td style="padding: 1px;">
                    <div style="background-color: #FF6B6B; height: 10px;"></div>
                </td>
            </tr>
            <tr style="font-size: 7pt; text-align: center; color: #333;">
                <td>Liberado</td>
                <td>Buena s/lib.</td>
                <td>Incompleto</td>
                <td>Mala s/lib.</td>
                <td>Rechazado</td>
            </tr>
        </table>
    </div>

    @foreach ($reporte as $nombreProceso => $operadores)
        <div class="proceso-section">
            <h2
                style="color: #00b913; border-bottom: 2px solid #00b913; margin-top: 15px; margin-bottom: 5px; font-size: 1.1em;">
                Proceso: {{ $nombreProceso }}
            </h2>
            @foreach ($operadores as $nombreOperador => $filas)
                @php
                    // Cálculo de promedio de productividad para el operador en PDF
                    $totalMeta = 0;
                    $totalRealizados = 0;
                    foreach ($filas as $f) {
                        $totalMeta += (float) ($f['meta'] ?? 0);
                        $totalRealizados += (float) ($f['juegos_realizados'] ?? 0);
                    }

                    if ($totalMeta == 0) {
                        // Sin meta definida: usar gris neutro para el header
                        $colorHeaderRealizados = "#888888";
                    } else {
                        $promedioOp = ($totalRealizados / $totalMeta) * 100;
                        $colorHeaderRealizados = "#e74c3c"; // Rojo (Bajo) por defecto
                        if ($promedioOp >= 150)
                            $colorHeaderRealizados = "#9b59b6";
                        elseif ($promedioOp >= 100)
                            $colorHeaderRealizados = "#f1c40f";
                        elseif ($promedioOp >= 75)
                            $colorHeaderRealizados = "#27ae60";
                        elseif ($promedioOp >= 40)
                            $colorHeaderRealizados = "#e67e22";
                    }
                @endphp
                <div class="operador-section">
                    <div class="operador-header"
                        style="background: #f8f9fa; padding: 5px 10px; border-left: 4px solid #00b913; margin-bottom: 5px; font-weight: bold; font-size: 0.95em; color: #333;">
                        Operador: {{ $nombreOperador }} ({{ count($filas) }} registros)
                    </div>

                    <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">
                        <thead>
                            <tr style="font-size: 0.75em; background-color: #f2f2f2;">
                                <th style="width: 15%; text-align: center; padding: 3px;">Orden de Trabajo</th>
                                <th style="width: 10%; text-align: center; padding: 3px;">Clase</th>
                                <th style="width: 10%; text-align: center; padding: 3px;">Proceso</th>
                                <th style="width: 8%; text-align: center; padding: 3px;">Núm. de Juego</th>
                                <th style="width: 6%; text-align: center; color: #2ecc71; padding: 3px;">Meta</th>
                                <th style="width: 8%; text-align: center; color: {{ $colorHeaderRealizados }}; padding: 3px;">
                                    Juegos
                                    Realizados</th>
                                <th style="width: 8%; text-align: center; padding: 3px;">Hr Inicio</th>
                                <th style="width: 8%; text-align: center; padding: 3px;">Hr Fin</th>
                                <th style="width: 13%; text-align: center; padding: 3px;">Obs. Operador</th>
                                <th style="width: 14%; text-align: center; padding: 3px;">Obs. Calidad</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($filas as $fila)
                                @php
                                    if ($fila['meta'] == 0) {
                                        // Sin meta asignada: fondo blanco, número negro, borde visible
                                        $barColor = "#ffffff";
                                        $barTextColor = "#000000";
                                        $barBorder = "border: 1px solid #bbb;";
                                    } else {
                                        $meta = $fila['meta'];
                                        $realPct = ($fila['juegos_realizados'] / $meta) * 100;
                                        $barBorder = "";
                                        $barTextColor = "white";
                                        if ($realPct >= 150) {
                                            $barColor = "#9b59b6";
                                        } elseif ($realPct >= 100) {
                                            $barColor = "#f1c40f";
                                        } elseif ($realPct >= 75) {
                                            $barColor = "#27ae60";
                                        } elseif ($realPct >= 40) {
                                            $barColor = "#e67e22";
                                        } else {
                                            $barColor = "#e74c3c";
                                        }
                                    }
                                @endphp
                                <tr style="background-color: {{ $fila['bg_color'] ?? 'white' }};">
                                    <td style="font-size: 0.75em; padding: 2px;">{{ $fila['ot_label'] }}</td>
                                    <td style="font-size: 0.75em; padding: 2px;">{{ $fila['clase_label'] ?? '—' }}</td>
                                    <td style="font-size: 0.75em; padding: 2px;">{{ $fila['proceso'] }}</td>
                                    <td style="text-align: center; font-size: 0.75em; padding: 2px;">
                                        <strong>{{ $fila['n_piezas'] }}</strong></td>
                                    <td style="text-align: center; font-size: 0.75em; padding: 2px;">
                                        {{ $fila['meta'] == 0 ? '—' : $fila['meta'] }}</td>
                                    <td
                                        style="background-color: {{ $barColor }}; color: {{ $barTextColor }}; font-weight: bold; text-align: center; font-size: 0.75em; padding: 2px; {{ $barBorder }}">
                                        {{ $fila['juegos_realizados'] }}
                                    </td>
                                    <td style="font-size: 7pt; text-align: center; padding: 2px;">{{ $fila['hora_inicio'] ?? '—' }}
                                    </td>
                                    <td style="font-size: 7pt; text-align: center; padding: 2px;">{{ $fila['hora_fin'] ?? '—' }}
                                    </td>
                                    <td style="font-size: 7pt; padding: 2px;">{{ $fila['obs_operador'] }}</td>
                                    <td style="font-size: 7pt; padding: 2px;">{{ $fila['obs_calidad'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
        </div>
    @endforeach

    <div style="page-break-before: always;"></div>

    <div style="margin-bottom: 25px; text-align: center; border-bottom: 3px solid #00b913; padding-bottom: 15px;">
        <h1 style="margin: 0; font-size: 1.8em; color: #333; text-transform: uppercase; letter-spacing: 1px;">RESUMEN PRODUCCIÓN</h1>
        <div style="margin-top: 8px;">
            <span style="display: inline-block; font-size: 1.1em; background-color: #00b913; color: white; padding: 4px 15px; border-radius: 20px; font-weight: bold;">
                {{ $fecha->format('d/m/Y') }}
            </span>
        </div>
    </div>

    @foreach ($reporte as $nombreProceso => $operadores)
        <div
            style="page-break-inside: avoid; margin-bottom: 20px; border: 1px solid #ddd; border-radius: 6px; overflow: hidden;">
            <div
                style="background-color: #f8f9fa; padding: 8px 12px; border-bottom: 2px solid #00b913; font-weight: bold; font-size: 1.05em; color: #333; text-transform: uppercase;">
                {{ $nombreProceso }}
            </div>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background-color: #f2f2f2; font-size: 0.85em;">
                        <th
                            style="width: 50%; text-align: left; padding: 4px 10px; border-bottom: 1px solid #ccc; color: #fff;">
                            Nombre (Operador)</th>
                        <th
                            style="width: 15%; text-align: center; padding: 4px 10px; border-bottom: 1px solid #ccc; color: #fff;">
                            Registros</th>
                        <th
                            style="width: 35%; text-align: center; padding: 4px 10px; border-bottom: 1px solid #ccc; color: #fff;">
                            Firma</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($operadores as $nombreOperador => $filas)
                        <tr>
                            <td style="font-size: 0.85em; text-align: left; padding: 2px 8px; border-bottom: 1px solid #eee;">
                                <strong>{{ $nombreOperador }}</strong>
                            </td>
                            <td style="font-size: 0.85em; text-align: center; padding: 2px 8px; border-bottom: 1px solid #eee;">
                                <strong>{{ count($filas) }}</strong>
                            </td>
                            <td style="font-size: 0.85em; text-align: center; padding: 2px 8px; border-bottom: 1px solid #eee;">
                                <div
                                    style="display: inline-block; width: 150px; height: 25px; border: 1px solid #ffffff; border-radius: 3px; background-color: #fff;">
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach

    <div class="footer">
        Reporte generado automáticamente el {{ now()->format('d/m/Y H:i') }}
    </div>


</body>

</html>
