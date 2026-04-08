<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte Soldadura PTA - OT {{ $ot->id }}</title>
    <style>
        {!! file_get_contents(resource_path('css/pta_views/analysis_pdf.css')) !!}
    </style>
</head>

<body>

    <div class="header">
        <h1>Reporte de Inspección Soldadura PTA</h1>
        <p>Industrial Saavedra — Sistema de Control de Calidad</p>
    </div>

    <div class="info-section">
        <div class="info-box">
            <p><strong>Orden de Trabajo:</strong> OT {{ $ot->id }}</p>
            <p><strong>Moldura:</strong> {{ $ot->moldura ? $ot->moldura->nombre : 'N/A' }}</p>
        </div>
        <div class="info-box" style="text-align: right;">
            <p><strong>Clase:</strong> {{ $claseSeleccionada->nombre }}</p>
            <p><strong>Fecha de Reporte:</strong> {{ $fecha }}</p>
        </div>
    </div>

    @php
        $juegosPTA = [];
        foreach ($piezasPTA as $pieza) {
            // Omitir piezas que Calidad ya liberó como RECHAZADAS (liberacion == 2)
            // if ($pieza->liberacion == 2) continue; // El usuario quiere ver todo o solo lo bueno?
            // En el análisis admin ve todo. Lo dejaré que vea todo.

            preg_match('/^(\d+)/', $pieza->n_pieza, $m);
            $jNum = $m[1] ?? $pieza->n_pieza;
            $juegosPTA[$jNum][$pieza->n_pieza] = $pieza;
        }
        ksort($juegosPTA, SORT_NUMERIC);
    @endphp

    @php
        $piezasMap = $piezasPTA->keyBy('n_pieza');
    @endphp

    @foreach ($juegosPTA as $jNum => $piezasDelJuegoObj)
        <div class="juego-block">
            <div class="juego-header">
                @if(isset($esJuegoCompleto) && $esJuegoCompleto)
                    Juego {{ $jNum }} - J{{ $jNum }}
                @else
                    Juego {{ $jNum }} — Piezas: {{ implode(' / ', array_keys($piezasDelJuegoObj)) }}
                @endif
            </div>

            {{-- 1. Tabla de Resultados (Pico Llenado, etc.) --}}
            <table class="pta-table pta-table-resultados">
                <thead>
                    <tr>
                        <th style="width: 35px;">Pieza</th>
                        <th>Pico Llenado</th>
                        <th>Pico Soldadura</th>
                        <th>Conexión Llenado</th>
                        <th>Conexión Soldadura</th>
                        <th>Perfilado Llenado</th>
                        <th>Perfilado Soldadura</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($piezasDelJuegoObj as $pieza)
                        @php $res = $resultados->get($pieza->id); @endphp
                        <tr>
                            <td class="td-pieza" style="font-size: 10px;">{{ $pieza->n_pieza }}</td>
                            @php
                                $campos = [
                                    $res->resultado_pico_llenado ?? null,
                                    $res->resultado_pico_soldadura ?? null,
                                    $res->resultado_conexion_llenado ?? null,
                                    $res->resultado_conexion_soldadura ?? null,
                                    $res->resultado_perfilado_llenado ?? null,
                                    $res->resultado_perfilado_soldadura ?? null,
                                ];
                            @endphp
                            @foreach ($campos as $campo)
                                @php
                                    $bgClass = 'td-res-none';
                                    if ($campo === 'Si') {
                                        $bgClass = 'td-res-si';
                                    } elseif ($campo === 'No') {
                                        $bgClass = 'td-res-no';
                                    } elseif ($campo === 'No Aplica') {
                                        $bgClass = 'td-res-na';
                                    }
                                @endphp
                                <td class="{{ $bgClass }}">
                                    {{ $campo === 'No Aplica' ? 'N/A' : ($campo ?: '—') }}
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- 2. Tabla de Datos Técnicos (Resumen) --}}
            @php
                $piezasKeys = array_keys($piezasDelJuegoObj);
                $piezasDelJuegoTecnicos = [];
                foreach ($piezasKeys as $_k) {
                    if ($piezasGroup->has($_k)) {
                        $piezasDelJuegoTecnicos[$_k] = $piezasGroup->get($_k);
                    }
                }
            @endphp

            @if (!empty($piezasDelJuegoTecnicos))
                @php
                    $tiposOrden = ['D_Conexion_pico', 'D_Conexion_obt', 'Perfilado'];
                    $labelMedida = [
                        'D_Conexion_pico' => 'D. Conexión pico',
                        'D_Conexion_obt' => 'D. Conexión obt',
                        'Perfilado' => 'Perfilado',
                    ];
                @endphp
                @foreach ($piezasDelJuegoTecnicos as $nPieza => $subFilas)
                    <table class="pta-table" style="page-break-inside: avoid; margin-bottom: 5px;">
                        <thead>
                            <tr>
                                <th rowspan="2">
                                    Número<br>({{ isset($esJuegoCompleto) && $esJuegoCompleto ? 'Juego' : 'M/H' }})</th>
                                <th colspan="2" class="th-section">Concepto</th>
                                <th rowspan="2">VL</th>
                                <th rowspan="2">T. de P.</th>
                                <th rowspan="2">Precal.<br>(°C)</th>
                                <th colspan="3" class="th-section">Soldadura</th>
                                <th colspan="3" class="th-section">Corriente</th>
                                <th rowspan="2">Gas<br>Argón</th>
                                <th rowspan="2">Vel.<br>Calc.</th>
                                <th rowspan="2">Resultado</th>
                                <th rowspan="2">Defecto</th>
                                <th rowspan="2">Observ.</th>
                            </tr>
                            <tr>
                                <th>Medida</th>
                                <th>Valor</th>
                                <th>Inicial</th>
                                <th>Aplicada</th>
                                <th>Final</th>
                                <th>Inicial</th>
                                <th>Aplicada</th>
                                <th>Final</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $filasPorTipo = [];
                                foreach ($subFilas as $sf) {
                                    $filasPorTipo[$sf->tipo_medida] = $sf;
                                }
                                $filaPrecal = $filasPorTipo['D_Conexion_pico'] ?? null;

                                // Lógica de colores idéntica al partial de operadores
                                $piezaObj = $piezasMap->get($nPieza);
                                $libVal = $piezaObj->liberacion ?? null;

                                if ($libVal === 1) {
                                    $claseColor = 'pta-row-liberada';
                                } elseif ($libVal === 2) {
                                    $claseColor = 'pta-row-rechazada';
                                } elseif ($libVal === 3) {
                                    $claseColor = 'pta-row-buena';
                                } elseif ($libVal === 4) {
                                    $claseColor = 'pta-row-mala';
                                } elseif ($libVal === 5) {
                                    $claseColor = 'pta-row-incompleta';
                                } elseif ($libVal === 0 || $libVal === null) {
                                    $claseColor = 'pta-row-ok';
                                } else {
                                    $claseColor = 'pta-row-sin-lib';
                                }
                            @endphp
                            @foreach ($tiposOrden as $subIdx => $tipo)
                                @php
                                    $fila = $filasPorTipo[$tipo] ?? null;
                                    $campo = $tipo === 'D_Conexion_pico' ? 'd_conexion_pico' : ($tipo === 'D_Conexion_obt' ? 'd_conexion_obt' : 'perfilado');
                                @endphp
                                <tr class="{{ $claseColor }}">
                                    @if ($subIdx === 0)
                                        <td rowspan="3" class="td-pieza">
                                            {{ $nPieza }}
                                        </td>
                                    @endif
                                    <td class="td-tipo-medida">{{ $labelMedida[$tipo] }}</td>
                                    <td>{{ ($fila?->$campo !== null) ? $fila->$campo . '"' : '—' }}</td>
                                    <td>{{ $fila?->vl ?? '—' }}</td>
                                    <td>{{ $fila?->tipo_preparacion ?? '—' }}</td>
                                    @if ($subIdx === 0)
                                        <td rowspan="3" class="td-precal">{{ $filaPrecal?->precalentamiento ?? '—' }}</td>
                                    @endif
                                    <td>{{ $fila?->sold_inicial ?? '—' }}</td>
                                    <td>{{ $fila?->sold_aplicada ?? '—' }}</td>
                                    <td>{{ $fila?->sold_final ?? '—' }}</td>
                                    <td>{{ $fila?->corr_inicial ?? '—' }}</td>
                                    <td>{{ $fila?->corr_aplicada ?? '—' }}</td>
                                    <td>{{ $fila?->corr_final ?? '—' }}</td>
                                    <td>{{ $fila?->gas_argon ?? '—' }}</td>
                                    <td>{{ $fila?->velocidad_calculada ?? '—' }}</td>
                                    <td>
                                        <span class="{{ ($fila?->resultado ?? '') === 'Bien' ? 'resultado-OK' : 'resultado-NOK' }}">
                                            {{ $fila?->resultado ?? '—' }}
                                        </span>
                                    </td>
                                    <td>{{ $fila?->defecto_pta ?? 'Ninguno' }}</td>
                                    @if ($subIdx === 0)
                                        <td rowspan="3" style="text-align: left; font-size: 7px;">{{ $filaPrecal?->observaciones ?? '—' }}
                                        </td>
                                    @endif
                                </tr>
                            @endforeach

                            {{-- 2DA PASADA --}}
                            @php
                                $filaP2H = $filasPorTipo['Segunda_Pasada'] ?? null;
                                if (!$filaP2H && isset($filasPorTipo['D_Conexion_pico']) && $filasPorTipo['D_Conexion_pico']->p2_activa) {
                                    $filaP2H = $filasPorTipo['D_Conexion_pico'];
                                }
                                $p2YaActivaH = $filaP2H?->p2_activa ?? false;
                            @endphp

                            @if ($p2YaActivaH)
                                @php
                                    $tipoP2GuardadoH = null;
                                    if ($filaP2H?->p2_d_conexion_pico !== null)
                                        $tipoP2GuardadoH = 'D_Conexion_pico';
                                    elseif ($filaP2H?->p2_d_conexion_obt !== null)
                                        $tipoP2GuardadoH = 'D_Conexion_obt';
                                    elseif ($filaP2H?->p2_perfilado !== null)
                                        $tipoP2GuardadoH = 'Perfilado';

                                    $valorP2GuardadoH = match ($tipoP2GuardadoH) {
                                        'D_Conexion_pico' => $filaP2H?->p2_d_conexion_pico,
                                        'D_Conexion_obt' => $filaP2H?->p2_d_conexion_obt,
                                        'Perfilado' => $filaP2H?->p2_perfilado,
                                        default => null,
                                    };
                                @endphp
                                <tr>
                                    <td class="td-pieza" style="font-size: 6px; line-height: 1.1;">
                                        {{ $nPieza }}<br><span style="color: #ffeb3b;">(2da Pasada)</span>
                                    </td>
                                    <td class="td-tipo-medida">
                                        {{ $tipoP2GuardadoH ? ($tipoP2GuardadoH === 'D_Conexion_pico' ? 'D. Conexión Pico' : ($tipoP2GuardadoH === 'D_Conexion_obt' ? 'D. Conexión Obt.' : 'Perfilado')) : '—' }}
                                    </td>
                                    <td>{{ ($valorP2GuardadoH !== null) ? $valorP2GuardadoH . '"' : '—' }}</td>
                                    <td>{{ $filaP2H?->p2_vl ?? '—' }}</td>
                                    <td>{{ $filaP2H?->p2_tipo_preparacion ?? '—' }}</td>
                                    <td class="td-precal">{{ $filaP2H?->p2_precalentamiento ?? '—' }}</td>
                                    <td>{{ $filaP2H?->p2_sold_inicial ?? '—' }}</td>
                                    <td>{{ $filaP2H?->p2_sold_aplicada ?? '—' }}</td>
                                    <td>{{ $filaP2H?->p2_sold_final ?? '—' }}</td>
                                    <td>{{ $filaP2H?->p2_corr_inicial ?? '—' }}</td>
                                    <td>{{ $filaP2H?->p2_corr_aplicada ?? '—' }}</td>
                                    <td>{{ $filaP2H?->p2_corr_final ?? '—' }}</td>
                                    <td>{{ $filaP2H?->p2_gas_argon ?? '—' }}</td>
                                    <td>{{ $filaP2H?->p2_velocidad_calculada ?? '—' }}</td>
                                    <td>
                                        @php $resP2 = $filaP2H?->p2_resultado ?? '—'; @endphp
                                        <span
                                            class="{{ $resP2 === 'Bien' ? 'resultado-OK' : ($resP2 !== '—' ? 'resultado-NOK' : '') }}">
                                            {{ $resP2 }}
                                        </span>
                                    </td>
                                    <td>{{ $filaP2H?->p2_defecto_pta ?? 'Ninguno' }}</td>
                                    <td style="text-align: left; font-size: 7px;">{{ $filaP2H?->p2_observaciones ?? '—' }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                @endforeach
            @else
                <p style="text-align: center; color: #888; font-style: italic;">Sin datos técnicos registrados para este juego.
                </p>
            @endif

        </div>
    @endforeach

    <div class="page-break"></div>
    <div class="header">
        <h1>Anexos - Evidencia Fotográfica</h1>
        <p>Imágenes por juego/pieza</p>
    </div>

    @foreach ($juegosPTA as $jNum => $piezasDelJuegoObj)
        <div class="juego-block">
            <div class="juego-header">
                @if(isset($esJuegoCompleto) && $esJuegoCompleto)
                    Anexos: Juego {{ $jNum }} - J{{ $jNum }}
                @else
                    Anexos: Juego {{ $jNum }} — Piezas: {{ implode(' / ', array_keys($piezasDelJuegoObj)) }}
                @endif
            </div>

            @php
                $anyImagesInJuego = false;
                foreach ($piezasDelJuegoObj as $pieza) {
                    $res = $resultados->get($pieza->id);
                    if ($res && ($res->imagen_pico_soldadura || $res->imagen_conexion_soldadura || $res->imagen_perfilado_soldadura)) {
                        $anyImagesInJuego = true;
                        break;
                    }
                }
            @endphp
            
            @if(!$anyImagesInJuego)
                <p style="text-align: center; color: #888; font-style: italic; font-size: 10px;">Sin imágenes subidas para este juego.</p>
            @else
                @foreach ($piezasDelJuegoObj as $pieza)
                    @php 
                        $res = $resultados->get($pieza->id); 
                        $hasImages = $res && ($res->imagen_pico_soldadura || $res->imagen_conexion_soldadura || $res->imagen_perfilado_soldadura);
                    @endphp
                    @if($hasImages)
                        <div class="anexos-juego-banner">
                            <strong>Pieza: {{ $pieza->n_pieza }}</strong>
                        </div>
                        <table class="anexos-img-table">
                            <tr>
                                <td class="anexos-img-td">
                                    <strong>Pico Soldadura</strong>
                                    @if($res->imagen_pico_soldadura && file_exists(public_path($res->imagen_pico_soldadura)))
                                        <img src="{{ public_path($res->imagen_pico_soldadura) }}" class="anexos-img">
                                    @else
                                        <div class="anexos-img-none">No disponible</div>
                                    @endif
                                </td>
                                <td class="anexos-img-td">
                                    <strong>Conexión Soldadura</strong>
                                    @if($res->imagen_conexion_soldadura && file_exists(public_path($res->imagen_conexion_soldadura)))
                                        <img src="{{ public_path($res->imagen_conexion_soldadura) }}" class="anexos-img">
                                    @else
                                        <div class="anexos-img-none">No disponible</div>
                                    @endif
                                </td>
                                <td class="anexos-img-td">
                                    <strong>Perfilado Soldadura</strong>
                                    @if($res->imagen_perfilado_soldadura && file_exists(public_path($res->imagen_perfilado_soldadura)))
                                        <img src="{{ public_path($res->imagen_perfilado_soldadura) }}" class="anexos-img">
                                    @else
                                        <div class="anexos-img-none">No disponible</div>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    @endif
                @endforeach
            @endif
        </div>
    @endforeach

    <div class="footer">
        Generado automáticamente por el Sistema de Control de Producción — {{ date('Y-m-d H:i:s') }}
    </div>

</body>

</html>