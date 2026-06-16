<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte Soldadura PTA - OT {{ $ot->id }}</title>
    <style>
        {!! file_get_contents(resource_path('css/pta_views/analysis_pdf.css')) !!}
    </style>
    <link rel="icon" type="image/png" href="{{ asset('images/lg_saavedra.png') }}">
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
            preg_match('/^(\d+)/', $pieza->n_pieza, $m);
            $jNum = $m[1] ?? $pieza->n_pieza;
            $juegosPTA[$jNum][$pieza->n_pieza] = $pieza;
        }
        ksort($juegosPTA, SORT_NUMERIC);
        $piezasMap = $piezasPTA->keyBy('n_pieza');
    @endphp

    @foreach ($juegosPTA as $jNum => $piezasDelJuegoObj)
        <div class="juego-block">
            <div class="juego-header">
                @if(isset($esJuegoCompleto) && $esJuegoCompleto)
                    Juego {{ $jNum }} - {{ $jNum }}J
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
                                <th rowspan="2" style="width: 35px;">Número<br>({{ isset($esJuegoCompleto) && $esJuegoCompleto ? 'Juego' : 'M/H' }})</th>
                                <th colspan="2" class="th-section">Concepto</th>
                                <th rowspan="2" style="width: 25px;">VL</th>
                                <th rowspan="2" style="width: 20px;">T. de P.</th>
                                <th rowspan="2" style="width: 25px;">Precal.<br>(°C)</th>
                                <th rowspan="2" style="width: 55px;">Soldadura</th>
                                <th colspan="3" class="th-section">Soldadura</th>
                                <th colspan="3" class="th-section">Corriente</th>
                                <th rowspan="2" style="width: 22px;">Gas<br>Argón</th>
                                <th rowspan="2" style="width: 22px;">Vel.<br>Calc.</th>
                                <th rowspan="2" style="width: 35px;">Resultado</th>
                                <th rowspan="2" style="width: 35px;">Defecto</th>
                                <th rowspan="2">Observ.</th>
                            </tr>
                            <tr>
                                <th style="width: 60px;">Medida</th>
                                <th style="width: 25px;">Valor</th>
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
                                $piezaObj = $piezasMap->get($nPieza);
                                $libVal = $piezaObj->liberacion ?? null;

                                $claseColor = match($libVal) {
                                    1 => 'pta-row-liberada',
                                    2 => 'pta-row-rechazada',
                                    3 => 'pta-row-buena',
                                    4 => 'pta-row-mala',
                                    5 => 'pta-row-incompleta',
                                    0, null => 'pta-row-ok',
                                    default => 'pta-row-sin-lib'
                                };
                            @endphp
                            @foreach ($tiposOrden as $subIdx => $tipo)
                                @php
                                    $fila = $filasPorTipo[$tipo] ?? null;
                                    $campo = $tipo === 'D_Conexion_pico' ? 'd_conexion_pico' : ($tipo === 'D_Conexion_obt' ? 'd_conexion_obt' : 'perfilado');
                                @endphp
                                <tr class="{{ $claseColor }}">
                                    @if ($subIdx === 0)
                                        <td rowspan="3" class="td-pieza">{{ $nPieza }}</td>
                                    @endif
                                    <td class="td-tipo-medida">{{ $labelMedida[$tipo] }}</td>
                                    <td>{{ ($fila?->$campo !== null) ? $fila->$campo . '"' : '—' }}</td>
                                    <td>{{ $fila?->vl ?? '—' }}</td>
                                    <td>{{ $fila?->tipo_preparacion ?? '—' }}</td>
                                    @if ($subIdx === 0)
                                        <td rowspan="3" class="td-precal">{{ $filaPrecal?->precalentamiento ?? '—' }}</td>
                                        <td rowspan="3" class="td-precal" style="font-size:7px;">{{ $filaPrecal?->material_soldadura ?? '—' }}</td>
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
                                        <td rowspan="3" style="text-align: left; font-size: 7px;">{{ $filaPrecal?->observaciones ?? '—' }}</td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endforeach
            @else
                <p style="text-align: center; color: #888; font-style: italic;">Sin datos técnicos registrados para este juego.</p>
            @endif
        </div>
    @endforeach

    <div style="page-break-before: always;"></div>
    <div class="header">
        <h1>Anexos - Evidencia Fotográfica</h1>
        <p>Imágenes por juego/pieza</p>
    </div>

    @foreach ($juegosPTA as $jNum => $piezasDelJuegoObj)
        <div class="juego-block">
            <div class="juego-header">
                @if(isset($esJuegoCompleto) && $esJuegoCompleto)
                    Anexos: Juego {{ $jNum }} - {{ $jNum }}J
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
                            @php
                                $nPieza = $pieza->n_pieza;
                                $esSufijoJ = str_ends_with(strtoupper($nPieza), 'J');
                                $labelPza = $esSufijoJ ? 'Juego: ' . $nPieza : 'Pieza: ' . $nPieza;
                            @endphp
                            <strong>{{ $labelPza }}</strong>
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
