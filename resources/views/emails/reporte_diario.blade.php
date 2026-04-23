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
    <link rel="icon" href="{{ url('images/lg_saavedra.png') }}?v=1">
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
                    <td style="padding: 2px;">
                        <div style="background: #ffffff; height: 12px; border-radius: 2px; border: 1px solid #bbb;"></div>
                    </td>
                </tr>
                <tr style="font-size: 0.75em; text-align: center; color: #333;">
                    <td style="padding-top: 3px;"><b>+150%</b><br>Excelencia</td>
                    <td style="padding-top: 3px;"><b>100-149%</b><br>Destacado</td>
                    <td style="padding-top: 3px;"><b>75-99%</b><br>Aceptable</td>
                    <td style="padding-top: 3px;"><b>40-74%</b><br>Medio</td>
                    <td style="padding-top: 3px;"><b>0-39%</b><br>Bajo</td>
                    <td style="padding-top: 3px;"><b>—</b><br>Sin meta</td>
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

        {{-- NIVEL 1: Proceso --}}
        @foreach ($reporte as $nombreProceso => $operadores)
            <div class="proceso-section">
                <h2 style="color: #00b913; border-bottom: 2px solid #00b913; margin-top: 15px; margin-bottom: 5px; font-size: 1.1em;">
                    Proceso: {{ $nombreProceso }}
                </h2>
                @foreach ($operadores as $nombreOperador => $filas)
                    @php
                        // Cálculo de promedio de productividad para el operador
                        $totalMeta = 0;
                        $totalRealizados = 0;
                        foreach ($filas as $f) {
                            $totalMeta += (float) ($f['meta'] ?? 0);
                            $totalRealizados += (float) ($f['juegos_realizados'] ?? 0);
                        }

                        if ($totalMeta == 0) {
                            // Sin meta definida: gris neutro para el header
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
                    <div class="operador-section" style="margin-bottom: 15px;">
                        <div class="operador-header"
                            style="background: #f8f9fa; padding: 5px 10px; border-left: 4px solid #00b913; margin-bottom: 5px; font-weight: bold; font-size: 0.95em; color: #333;">
                            Operador: {{ $nombreOperador }} ({{ count($filas) }} {{ count($filas) === 1 ? 'registro' : 'registros' }})
                        </div>

                        <table class="op-table" style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">
                            <thead>
                                <tr style="font-size: 0.75em; background-color: #f2f2f2;">
                                    <th style="width: 15%; text-align: center; padding: 3px;">Orden de Trabajo</th>
                                    <th style="width: 10%; text-align: center; padding: 3px;">Clase</th>
                                    <th style="width: 10%; text-align: center; padding: 3px;">Proceso</th>
                                    <th style="width: 8%; text-align: center; padding: 3px;">Núm. de Juego</th>
                                    <th style="width: 6%; text-align: center; color: #2ecc71; padding: 3px;">Meta</th>
                                    <th style="width: 8%; text-align: center; color: {{ $colorHeaderRealizados }}; padding: 3px;">Juegos Realizados</th>
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
                                    $barColor     = "#ffffff";
                                    $barTextColor = "#000000";
                                    $barBorder    = "border: 1px solid #bbb;";
                                } else {
                                    $meta = $fila['meta'];
                                    $realPct = ($fila['juegos_realizados'] / $meta) * 100;
                                    $barBorder    = "";
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
                                <td style="text-align: center; font-size: 0.75em; padding: 2px;"><strong>{{ $fila['n_piezas'] }}</strong></td>
                                <td style="text-align: center; font-size: 0.75em; padding: 2px;">{{ $fila['meta'] == 0 ? '—' : $fila['meta'] }}</td>
                                <td style="background-color: {{ $barColor }}; color: {{ $barTextColor }}; font-weight: bold; text-align: center; font-size: 0.75em; padding: 2px; {{ $barBorder }}">
                                    {{ $fila['juegos_realizados'] }}
                                </td>
                                <td style="font-size: 7.5pt; text-align: center; padding: 2px;">{{ $fila['hora_inicio'] ?? '—' }}</td>
                                <td style="font-size: 7.5pt; text-align: center; padding: 2px;">{{ $fila['hora_fin'] ?? '—' }}</td>
                                <td style="font-size: 7.5pt; padding: 2px;">{{ $fila['obs_operador'] }}</td>
                                <td style="font-size: 7.5pt; padding: 2px;">{{ $fila['obs_calidad'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
                @endforeach
            </div>
        @endforeach

        <div style="page-break-before: always;"></div>
        
        <div style="margin-bottom: 25px; text-align: center; border-bottom: 3px solid #00b913; padding-bottom: 15px; margin-top: 50px;">
            <h1 style="margin: 0; font-size: 1.8em; color: #333; text-transform: uppercase; letter-spacing: 1px;">RESUMEN PRODUCCIÓN</h1>
            <div style="margin-top: 8px;">
                <span style="display: inline-block; font-size: 1.1em; background-color: #00b913; color: white; padding: 4px 15px; border-radius: 20px; font-weight: bold;">
                    {{ $fecha->format('d/m/Y') }}
                </span>
            </div>
        </div>

        @foreach ($reporte as $nombreProceso => $operadores)
            <div style="page-break-inside: avoid; margin-bottom: 20px; border: 1px solid #ddd; border-radius: 6px; overflow: hidden; background-color: #fff;">
                <div style="background-color: #f8f9fa; padding: 8px 12px; border-bottom: 2px solid #00b913; font-weight: bold; font-size: 1.05em; color: #333; text-transform: uppercase;">
                    {{ $nombreProceso }}
                </div>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background-color: #f2f2f2; font-size: 0.85em;">
                            <th style="width: 50%; text-align: left; padding: 4px 10px; border-bottom: 1px solid #ccc; color: #555;">Nombre (Operador)</th>
                            <th style="width: 15%; text-align: center; padding: 4px 10px; border-bottom: 1px solid #ccc; color: #555;">Registros</th>
                            <th style="width: 35%; text-align: center; padding: 4px 10px; border-bottom: 1px solid #ccc; color: #555;">Firma</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($operadores as $nombreOperador => $filas)
                            <tr>
                                <td style="font-size: 0.85em; text-align: left; padding: 4px 10px; border-bottom: 1px solid #eee;"><strong>{{ $nombreOperador }}</strong></td>
                                <td style="font-size: 0.85em; text-align: center; padding: 4px 10px; border-bottom: 1px solid #eee;"><strong>{{ count($filas) }}</strong></td>
                                <td style="font-size: 0.85em; text-align: center; padding: 4px 10px; border-bottom: 1px solid #eee;">
                                    <div style="display: inline-block; width: 150px; height: 25px; border: 1px solid #999; border-radius: 3px; background-color: #fff;"></div>
                                </td>
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
