<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Salida de Molduras OT {{ $reporte->ot_id }}</title>
    <style>
        @page {
            margin: 1.2cm 1cm;
        }

        body {
            font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 0;
            color: #2c3e50;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        th,
        td {
            border: 1px solid #94a3b8;
            padding: 7px 5px;
            text-align: center;
        }

        th {
            background-color: #0c4a8e;
            color: #ffffff;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.5px;
        }

        .header-table {
            margin-bottom: 12px;
            border-bottom: 2px solid #0c4a8e;
            padding-bottom: 8px;
        }

        .header-table td {
            border: none;
            padding: 0;
        }

        .header-table .codigo-table {
            border-collapse: collapse;
            width: 100%;
            font-size: 9px;
            border: 1px solid #94a3b8;
        }

        .header-table .codigo-table td {
            border: 1px solid #94a3b8;
            padding: 4px;
        }

        .header-table .codigo-table td.label {
            background-color: #f1f5f9;
            font-weight: bold;
            color: #475569;
            text-align: left;
        }

        .info-table {
            border: 2px solid #0c4a8e;
        }

        .info-table td {
            background-color: #ffffff;
            color: #1e293b;
            font-size: 11px;
            text-align: left;
            padding: 8px;
            border: 1px solid #cbd5e1;
        }

        .info-table th {
            background-color: #f8fafc;
            color: #0f172a;
            text-align: left;
            padding: 8px;
            font-size: 10px;
            border: 1px solid #cbd5e1;
            text-transform: uppercase;
        }

        .page-break {
            page-break-after: always;
        }

        .checked {
            font-family: 'DejaVu Sans', sans-serif;
            color: #15803d;
            font-weight: bold;
            font-size: 12px;
        }

        .unchecked {
            font-family: 'DejaVu Sans', sans-serif;
            color: #ef4444;
            font-weight: bold;
            font-size: 12px;
        }

        .section-title {
            background-color: #f8fafc;
            color: #0c4a8e;
            padding: 6px 12px;
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 6px;
            text-align: left;
            border-left: 5px solid #0c4a8e;
            border-bottom: 1px solid #e2e8f0;
        }

        .grid-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .obs-box {
            border: 1px solid #cbd5e1;
            padding: 12px;
            min-height: 60px;
            background-color: #ffffff;
            border-radius: 4px;
            color: #334155;
            font-size: 11px;
            line-height: 1.5;
        }

        .th-simbolo {
            font-family: 'DejaVu Sans', sans-serif !important;
            text-transform: none !important;
            font-size: 12px !important;
            font-weight: bold !important;
        }
    </style>
</head>

<body>

    <!-- Encabezado -->
    <table class="header-table">
        <tr>
            <td width="25%">
                <img src="{{ public_path('images/lg_saavedra.png') }}" width="140" height="auto" alt="Logo GIS">
            </td>
            <td width="50%" align="center" style="vertical-align: middle;">
                <h2 style="margin:0; font-size:15px; text-transform: uppercase; letter-spacing: 0.5px; color: #0f172a;">
                    Registro de Números de Trazabilidad por Moldura</h2>
                <h3 style="margin: 6px 0 0 0; font-size:13px; font-weight: bold; letter-spacing: 1px;">
                    <span style="color: #64748b;">Folio:</span>
                    <span
                        style="color: #b91c1c;">F_CCL_RIS_{{ str_pad($reporte->id, 4, '0', STR_PAD_LEFT) }}-V{{ $siguienteVersion }}</span>
                </h3>
            </td>
            <td width="25%" align="right" style="vertical-align: middle;">
                <table class="codigo-table">
                    <tr>
                        <td class="label">Código:</td>
                        <td style="text-align: center;">F PRO CPT</td>
                    </tr>
                    <tr>
                        <td class="label">Versión:</td>
                        <td style="text-align: center;">6</td>
                    </tr>
                    <tr>
                        <td class="label">Fecha Rev:</td>
                        <td style="text-align: center;">22/04/2026</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Info General -->
    <table class="info-table">
        <tr>
            <th width="9%">OT:</th>
            <td width="9%">{{ $reporte->ot_id }}</td>
            <th width="14%">MOLDURA:</th>
            <td width="36%">{{ $reporte->nombre_moldura }}</td>
            <th width="12%">CLASE:</th>
            <td width="20%">{{ $reporte->clase }}</td>
        </tr>
        <tr>
            <th>PEDIDO:</th>
            <td>{{ $reporte->cantidad_pedido }}</td>
            <th>CONSIG.:</th>
            <td>{{ $reporte->cantidad_consignacion }}</td>
            <th>FECHA:</th>
            <td>{{ $reporte->fecha_inicio ? \Carbon\Carbon::parse($reporte->fecha_inicio)->format('d/m/Y') : now()->format('d/m/Y') }}
            </td>
        </tr>
        <tr>
            <th>CLIENTE:</th>
            <td colspan="3">{{ $reporte->cliente ?? '—' }}</td>
            <th>INSPECTOR:</th>
            <td>{{ $inspector->nombre ?? 'N/A' }}</td>
        </tr>
    </table>

    <div class="section-title">Registro de Piezas &mdash; Clase: {{ $reporte->clase }}</div>

    @php
        $itemsPerRow = ($reporte->formato === 'bombillos') ? 2 : 3;
    @endphp
    <!-- Grid de Piezas -->
    <table class="grid-table">
        <thead>
            <tr>
                @for($i = 0; $i < $itemsPerRow; $i++)
                    <th width="5%">N°</th>
                    <th width="5%" class="th-simbolo">&#10004;/&#10008;</th>
                    @if($reporte->formato === 'bombillos')
                        <th width="10%">90°</th>
                        <th width="10%">LP</th>
                        <th width="20%">Desc.</th>
                    @else
                        <th width="23%">Descripción</th>
                    @endif
                @endfor
            </tr>
        </thead>
        <tbody>
            @php $filas = (int) ceil($totalPiezas / $itemsPerRow); @endphp
            @for($fila = 0; $fila < $filas; $fila++)
                <tr>
                    @for($col = 0; $col < $itemsPerRow; $col++)
                        @php
                            $numPieza = ($fila * $itemsPerRow) + $col + 1;
                            if ($numPieza > $totalPiezas) {
                                $cols = $reporte->formato === 'bombillos' ? 5 : 3;
                                for ($blank = 0; $blank < $cols; $blank++) {
                                    echo "<td></td>";
                                }
                                continue;
                            }

                            $celda = $celdas[$numPieza] ?? null;
                            $valorSimple = $celda?->valor_simple;

                            $estadoClase = '';
                            $estadoSimbolo = '';

                            if ($valorSimple === '1') {
                                $estadoClase = 'checked';
                                $estadoSimbolo = '✔';
                            } elseif ($valorSimple === '0') {
                                $estadoClase = 'unchecked';
                                $estadoSimbolo = '✘';
                            }

                            $desc = $celda?->descripcion ?? '';
                            $val90 = $celda?->valor_90 ?? '';
                            $valLp = $celda?->valor_lp ?? '';
                        @endphp

                        <td class="{{ $estadoClase }}">{{ $numPieza }}</td>
                        <td class="{{ $estadoClase }}">{{ $estadoSimbolo }}</td>

                        @if($reporte->formato === 'bombillos')
                            <td>{{ $val90 }}</td>
                            <td>{{ $valLp }}</td>
                            <td style="font-size: 10px;">{{ $desc }}</td>
                        @else
                            <td style="font-size: 10px;">{{ $desc }}</td>
                        @endif
                    @endfor
                </tr>
            @endfor
        </tbody>
    </table>

    <div style="page-break-inside: avoid; margin-top: 25px;">
        <div class="section-title">Observaciones</div>
        <div class="obs-box">
            {{ $reporte->observaciones ?? 'Sin observaciones.' }}
        </div>

        <div style="margin-top: 50px; text-align: center;">
            <div
                style="width: 250px; border-top: 1px solid #1e293b; margin: 0 auto; padding-top: 8px; font-size: 11px;">
                <strong>Firma del Inspector de Calidad</strong><br>
            </div>
        </div>
    </div>

    <!-- Paginación Automática -->
    <script type="text/php">
        if (isset($pdf)) {
            $pdf->page_text(750, 580, "Página {PAGE_NUM} de {PAGE_COUNT}", null, 9, array(0,0,0));
        }
    </script>
</body>

</html>
