<!DOCTYPE html>
<html>

<head>
    <title>Reporte Soldadura PTA - Información Detallada</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 8px;
            margin: 0;
            padding: 10px;
        }

        .header {
            text-align: center;
            margin-bottom: 12px;
            border-bottom: 2px solid #033966;
            padding-bottom: 8px;
        }

        .header h2 {
            margin: 0 0 4px 0;
            color: #033966;
            font-size: 14px;
        }

        .header p {
            margin: 2px 0;
            color: #555;
            font-size: 9px;
        }

        .info-row {
            margin-bottom: 8px;
            font-size: 9px;
        }

        .info-row span {
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        th {
            background-color: #033966;
            color: #ffffff;
            font-weight: bold;
            padding: 5px 4px;
            text-align: center;
            border: 1px solid #01264d;
            font-size: 7px;
        }

        th.sub-header {
            background-color: #055a9e;
        }

        td {
            padding: 4px 3px;
            border: 1px solid #bbb;
            text-align: center;
            vertical-align: middle;
            font-size: 7.5px;
        }

        /* Primera fila de cada grupo de pieza */
        tr.grupo-inicio td {
            border-top: 2px solid #033966;
        }

        /* Columna número de pieza */
        td.td-pieza {
            background-color: #033966;
            color: #ffffff;
            font-weight: bold;
            font-size: 10px;
        }

        /* Columna tipo medida */
        td.td-tipo {
            background-color: #e0ecf8;
            font-weight: bold;
            text-align: left;
            padding-left: 5px;
        }

        /* Precalentamiento (rowspan=3) */
        td.td-precal {
            background-color: #fff7e0;
            font-weight: bold;
            border-left: 2px solid #e6a800;
            border-right: 2px solid #e6a800;
        }

        /* Filas pares */
        tr.par td {
            background-color: #f4f8fd;
        }

        tr.par td.td-tipo {
            background-color: #d0e3f5;
        }

        .defecto-fund {
            color: #c0392b;
            font-weight: bold;
        }

        .footer {
            margin-top: 14px;
            text-align: right;
            font-size: 8px;
            color: #888;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>Reporte de Soldadura PTA — Información Detallada</h2>
        <p>Fecha de generación: {{ date('d-m-Y H:i') }}</p>
        <p>OT: <strong>{{ $ordenTrabajo ?? '—' }}</strong> &nbsp;|&nbsp;
            Clase: <strong>{{ $clase ?? '—' }}</strong> &nbsp;|&nbsp;
            Operador: <strong>{{ $operador ?? '—' }}</strong></p>
        <p>Total de piezas registradas: {{ $piezasGroup->count() }}</p>
    </div>

    @php
        $tiposOrden = ['D_Conexion_pico', 'D_Conexion_obt', 'Perfilado'];
        $labelMedida = [
            'D_Conexion_pico' => 'D. Conn. pico',
            'D_Conexion_obt' => 'D. Conn. obt',
            'Perfilado' => 'Perfilado',
        ];
        $grupoIdx = 0;
    @endphp

    <table>
        <thead>
            <tr>
                <th rowspan="2">N°<br>Pieza</th>
                <th rowspan="2">Medida</th>
                <th rowspan="2">Valor</th>
                <th rowspan="2">VL</th>
                <th rowspan="2">T.P.</th>
                <th rowspan="2">Precal.<br>(°C)</th>
                <th colspan="3" class="sub-header">Soldadura</th>
                <th colspan="3" class="sub-header">Corriente</th>
                <th rowspan="2">Gas<br>Ar.</th>
                <th rowspan="2">Vel.<br>Cal.</th>
                <th rowspan="2">Resultado</th>
                <th rowspan="2">Defecto</th>
                <th rowspan="2">Observaciones</th>
            </tr>
            <tr>
                <th class="sub-header">Inic.</th>
                <th class="sub-header">Aplic.</th>
                <th class="sub-header">Final</th>
                <th class="sub-header">Inic.</th>
                <th class="sub-header">Aplic.</th>
                <th class="sub-header">Final</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($piezasGroup as $nPieza => $subFilas)
                @php
                    $esPar = ($grupoIdx % 2 === 0);
                    $grupoIdx++;
                    $filasPorTipo = [];
                    foreach ($subFilas as $sf) {
                        $filasPorTipo[$sf->tipo_medida] = $sf;
                    }
                    $filaPrecal = $filasPorTipo['D_Conexion_pico'] ?? null;
                @endphp

                @foreach ($tiposOrden as $loopIndex => $tipo)
                    @php
                        $fila = $filasPorTipo[$tipo] ?? null;
                        $esPrimera = ($loopIndex === 0);
                        $trClass = ($esPar ? 'par' : '') . ($esPrimera ? ' grupo-inicio' : '');

                        // Campo de valor según tipo de medida
                        $campoPrincipal = $tipo === 'D_Conexion_pico' ? 'd_conexion_pico'
                            : ($tipo === 'D_Conexion_obt' ? 'd_conexion_obt'
                                : 'perfilado');
                    @endphp
                    <tr class="{{ $trClass }}">

                        {{-- Número de pieza (rowspan=3) --}}
                        @if ($esPrimera)
                            <td class="td-pieza" rowspan="3">{{ $nPieza }}</td>
                        @endif

                        {{-- Tipo de medida --}}
                        <td class="td-tipo">{{ $labelMedida[$tipo] ?? $tipo }}</td>

                        {{-- Valor principal --}}
                        <td>{{ $fila?->$campoPrincipal ?? '—' }}</td>

                        {{-- VL --}}
                        <td>{{ $fila?->vl ?? '—' }}</td>

                        {{-- Tipo Preparación --}}
                        <td>{{ $fila?->tipo_preparacion ?? '—' }}</td>

                        {{-- Precalentamiento (rowspan=3) --}}
                        @if ($esPrimera)
                            <td class="td-precal" rowspan="3">
                                {{ $filaPrecal?->precalentamiento ?? '—' }}
                            </td>
                        @endif

                        {{-- Soldadura --}}
                        <td>{{ $fila?->sold_inicial ?? '—' }}</td>
                        <td>{{ $fila?->sold_aplicada ?? '—' }}</td>
                        <td>{{ $fila?->sold_final ?? '—' }}</td>

                        {{-- Corriente --}}
                        <td>{{ $fila?->corr_inicial ?? '—' }}</td>
                        <td>{{ $fila?->corr_aplicada ?? '—' }}</td>
                        <td>{{ $fila?->corr_final ?? '—' }}</td>

                        {{-- Gas Argón --}}
                        <td>{{ $fila?->gas_argon ?? '—' }}</td>

                        {{-- Velocidad calc. --}}
                        <td>{{ $fila?->velocidad_calculada ?? '—' }}</td>

                        {{-- Resultado --}}
                        <td>{{ $fila?->resultado ?? '—' }}</td>

                        {{-- Defecto --}}
                        <td>
                            @php $def = $fila?->defecto_pta ?? 'Ninguno'; @endphp
                            @if ($def === 'Fundición')
                                <span class="defecto-fund">{{ $def }}</span>
                            @else
                                {{ $def }}
                            @endif
                        </td>

                        {{-- Observaciones (rowspan=3) --}}
                        @if ($esPrimera)
                            <td rowspan="3" style="text-align:left; padding:3px 4px;">
                                {{ $filaPrecal?->observaciones ?? '—' }}
                            </td>
                        @endif
                    </tr>
                @endforeach

            @empty
                <tr>
                    <td colspan="17" style="text-align:center; padding:12px; color:#888; font-style:italic;">
                        No hay registros de Soldadura PTA para esta pieza.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Generado por Sistema de Producción — Grupo Industrial Saavedra
    </div>
</body>

</html>