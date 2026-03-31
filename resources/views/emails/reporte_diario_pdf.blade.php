<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte de Producción — {{ $fecha->format('d/m/Y') }}</title>
    <style>
        {!! file_get_contents(resource_path('css/reportes/pdf.css')) !!}
    </style>
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

    @foreach ($reporte as $nombreOperador => $filas)
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
                style="background: #f8f9fa; padding: 10px 15px; border-left: 5px solid #00b913; margin-bottom: 10px; font-weight: bold; font-size: 1.1em; color: #333;">
                Operador: {{ $nombreOperador }} ({{ count($filas) }} registros)
            </div>

            <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                <thead>
                    <tr>
                        <th style="width: 18%; text-align: center;">Orden de Trabajo</th>
                        <th style="width: 11%; text-align: center;">Clase</th>
                        <th style="width: 13%; text-align: center;">Proceso</th>
                        <th style="width: 10%; text-align: center;">Núm. de Juego</th>
                        <th style="width: 7%; text-align: center; color: #2ecc71;">Meta</th>
                        <th style="width: 9%; text-align: center; color: {{ $colorHeaderRealizados }};">Juegos Realizados</th>
                        <th style="width: 16%; text-align: center;">Obs. Operador</th>
                        <th style="width: 16%; text-align: center;">Obs. Calidad</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($filas as $fila)
                        @php
                            if ($fila['meta'] == 0) {
                                // Sin meta asignada: fondo blanco, número negro, borde visible
                                $barColor    = "#ffffff";
                                $barTextColor = "#000000";
                                $barBorder   = "border: 1px solid #bbb;";
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
                            <td style="font-size: 0.85em;">{{ $fila['ot_label'] }}</td>
                            <td style="font-size: 0.85em;">{{ $fila['clase_label'] ?? '—' }}</td>
                            <td style="font-size: 0.85em;">{{ $fila['proceso'] }}</td>
                            <td style="text-align: center;"><strong>{{ $fila['n_piezas'] }}</strong></td>
                            <td style="text-align: center;">{{ $fila['meta'] == 0 ? '—' : $fila['meta'] }}</td>
                            <td style="background-color: {{ $barColor }}; color: {{ $barTextColor }}; font-weight: bold; text-align: center; {{ $barBorder }}">
                                {{ $fila['juegos_realizados'] }}
                            </td>
                            <td style="font-size: 0.8em;">{{ $fila['obs_operador'] }}</td>
                            <td style="font-size: 0.8em;">{{ $fila['obs_calidad'] }}</td>
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
