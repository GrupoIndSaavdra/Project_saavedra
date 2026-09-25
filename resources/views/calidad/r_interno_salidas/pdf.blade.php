<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Interno de Salidas OT {{ $reporte->ot_id }} — {{ $reporte->clase }}</title>
    <style>
        @page {
            margin: 6mm 8mm 6mm 8mm;
            size: letter portrait;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
            font-size: 8.5px;
            color: #0f172a;
            margin: 0;
            padding: 0;
            background: #ffffff;
            line-height: 1.2;
        }

        .main-frame {
            width: 100%;
            border: 1.5px solid #033966;
            background: #ffffff;
            margin: 0;
            padding: 0;
        }

        /* ── Encabezado Principal ── */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 1.5px solid #033966;
        }

        .header-table td {
            border: none;
            padding: 4px 6px;
            vertical-align: middle;
        }

        .logo-cell {
            width: 22%;
            text-align: left;
            padding: 4px 8px !important;
        }

        .logo-img {
            max-height: 38px;
            max-width: 140px;
        }

        .title-cell {
            width: 54%;
            text-align: center;
        }

        .title-cell h1 {
            margin: 0;
            font-size: 11.5px;
            font-weight: bold;
            color: #033966;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            line-height: 1.2;
        }

        .title-cell .folio {
            margin-top: 3px;
            font-size: 9.5px;
            font-weight: bold;
        }

        .title-cell .folio-lbl {
            color: #64748b;
        }

        .title-cell .folio-val {
            color: #b91c1c;
        }

        .code-cell {
            width: 24%;
            text-align: right;
            padding: 4px 8px !important;
        }

        .codigo-table {
            border-collapse: collapse;
            width: 100%;
            font-size: 7.5px;
            border: 1px solid #94a3b8;
        }

        .codigo-table td {
            border: 1px solid #94a3b8;
            padding: 2.5px 4px;
        }

        .codigo-table td.label {
            background-color: #f1f5f9;
            font-weight: bold;
            color: #475569;
            text-align: left;
            width: 52%;
        }

        .codigo-table td.val {
            font-weight: bold;
            color: #033966;
            text-align: center;
        }

        /* ── Banners de Sección ── */
        .section-banner {
            background-color: #033966;
            color: #ffffff;
            font-size: 8px;
            font-weight: bold;
            text-align: center;
            padding: 3px 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-top: 1px solid #033966;
            border-bottom: 1px solid #033966;
        }

        /* ── Tablas de Información ── */
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            border: 1px solid #cbd5e1;
            padding: 4px 6px;
            font-size: 8px;
            vertical-align: middle;
        }

        .info-table .lbl {
            font-weight: bold;
            color: #475569;
            background-color: #f8fafc;
            width: 14%;
            text-align: left;
            font-size: 7.5px;
            text-transform: uppercase;
        }

        .info-table .val {
            font-weight: bold;
            color: #033966;
            background-color: #ffffff;
            width: 36%;
        }

        .info-table .val-highlight {
            color: #0284c7;
        }

        .info-table .val-amber {
            color: #b45309;
        }

        /* Barra de Resumen de Total */
        .total-summary-row {
            background-color: #f0f6fc;
            padding: 4px 8px;
            font-size: 8px;
            border-bottom: 1.5px solid #033966;
            border-top: 1px solid #cbd5e1;
        }

        .total-summary-row table {
            width: 100%;
            border-collapse: collapse;
        }

        .total-summary-row td {
            border: none;
            padding: 0;
        }

        /* ── Tabla Principal de Piezas ── */
        .pieces-table {
            width: 100%;
            border-collapse: collapse;
        }

        .pieces-table th {
            background-color: #033966;
            color: #ffffff;
            font-weight: bold;
            font-size: 7.5px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            padding: 4px 2px;
            border: 1px solid #002244;
            text-align: center;
        }

        .pieces-table th.col-divider {
            border-right: 2px solid #00172e !important;
        }

        .pieces-table td {
            border: 1px solid #cbd5e1;
            padding: 3px 2px;
            font-size: 7.8px;
            text-align: center;
            vertical-align: middle;
            background-color: #ffffff;
        }

        .pieces-table td.col-divider {
            border-right: 2px solid #94a3b8 !important;
        }

        .pieces-table tr.even td {
            background-color: #f8fafc;
        }

        .pieces-table td.td-desc {
            text-align: left;
            padding-left: 4px;
            padding-right: 4px;
            font-size: 7.5px;
            color: #1e293b;
        }

        .num-badge {
            font-weight: bold;
            display: inline-block;
            padding: 1px 3px;
            border-radius: 3px;
        }

        .status-ok {
            color: #15803d;
            font-weight: bold;
            font-size: 9.5px;
        }

        .status-nok {
            color: #dc2626;
            font-weight: bold;
            font-size: 9.5px;
        }

        .status-none {
            color: #94a3b8;
            font-weight: bold;
            font-size: 8px;
        }

        .td-blank {
            background-color: #f1f5f9 !important;
            border: 1px solid #e2e8f0;
        }

        /* ── Observaciones ── */
        .obs-container {
            width: 100%;
            border-top: 1.5px solid #033966;
        }

        .obs-content {
            padding: 6px 8px;
            min-height: 48px;
            font-size: 8px;
            color: #334155;
            line-height: 1.35;
            background-color: #ffffff;
        }

        /* ── Firmas y Validación ── */
        .signatures-table {
            width: 100%;
            border-collapse: collapse;
            border-top: 1.5px solid #033966;
            margin-top: 0;
            background-color: #fafbfc;
        }

        .signatures-table td {
            border: none;
            padding: 12px 16px 8px;
            vertical-align: top;
            text-align: center;
        }

        .sig-line {
            width: 80%;
            margin: 0 auto 3px auto;
            border-top: 1px solid #0f172a;
        }

        .sig-title {
            font-weight: bold;
            font-size: 8px;
            color: #033966;
            text-transform: uppercase;
        }

        .sig-name {
            font-size: 7.5px;
            color: #475569;
            margin-top: 2px;
        }
    </style>
</head>
<body>

<div class="main-frame">

    <!-- ═══ ENCABEZADO INSTITUCIONAL OFICIAL ═══ -->
    <table class="header-table">
        <tr>
            <td class="logo-cell">
                <img src="{{ public_path('images/lg_saavedra.png') }}" class="logo-img" alt="Grupo Industrial Saavedra">
            </td>
            <td class="title-cell">
                <h1>Registro de Números de Trazabilidad por Moldura</h1>
                <div class="folio">
                    <span class="folio-lbl">Folio:</span>
                    <span class="folio-val">F_CCL_RIS_{{ str_pad($reporte->id, 4, '0', STR_PAD_LEFT) }}-V{{ $siguienteVersion }}</span>
                </div>
            </td>
            <td class="code-cell">
                <table class="codigo-table">
                    <tr>
                        <td class="label">Código:</td>
                        <td class="val">F PRO CPT</td>
                    </tr>
                    <tr>
                        <td class="label">Versión:</td>
                        <td class="val">6</td>
                    </tr>
                    <tr>
                        <td class="label">Fecha Rev:</td>
                        <td class="val">22/04/2026</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- ═══ INFORMACIÓN GENERAL Y DE PEDIDO ═══ -->
    <div class="section-banner">INFORMACIÓN GENERAL Y DE TRAZABILIDAD</div>
    <table class="info-table">
        <tr>
            <td class="lbl">FECHA:</td>
            <td class="val">{{ $reporte->fecha_inicio ? \Carbon\Carbon::parse($reporte->fecha_inicio)->format('d/m/Y') : now()->format('d/m/Y') }}</td>
            <td class="lbl">OT:</td>
            <td class="val val-highlight">{{ $reporte->ot_id }}</td>
        </tr>
        <tr>
            <td class="lbl">MOLDURA:</td>
            <td class="val">{{ $reporte->nombre_moldura }}</td>
            <td class="lbl">CLASE:</td>
            <td class="val">{{ $reporte->clase }}</td>
        </tr>
        <tr>
            <td class="lbl">PEDIDO:</td>
            <td class="val val-highlight">{{ $reporte->cantidad_pedido }} pzas</td>
            <td class="lbl">CONSIGNACIÓN:</td>
            <td class="val val-amber">
                @if($reporte->cantidad_consignacion > 0)
                    +{{ $reporte->cantidad_consignacion }} pzas
                @else
                    0 pzas
                @endif
            </td>
        </tr>
        <tr>
            <td class="lbl">CLIENTE:</td>
            <td class="val">{{ $reporte->cliente ?? '—' }}</td>
            <td class="lbl">INSPECTOR:</td>
            <td class="val">{{ $inspector->nombre ?? 'N/A' }}</td>
        </tr>
    </table>

    <!-- Resumen de Piezas -->
    <div class="total-summary-row">
        <table>
            <tr>
                <td align="left">
                    <strong style="color: #033966; text-transform: uppercase;">Total de Piezas en Reporte:</strong>
                    <strong style="color: #0284c7; font-size: 9px;">{{ $reporte->total_piezas }}</strong> piezas
                </td>
                <td align="right" style="color: #475569;">
                    @if($reporte->cantidad_consignacion > 0)
                        <span>(Consignación: <strong>{{ $reporte->cantidad_consignacion }}</strong> | Pedido original: <strong>{{ $reporte->cantidad_pedido }}</strong>)</span>
                    @else
                        <span>(Pedido original: <strong>{{ $reporte->cantidad_pedido }}</strong>)</span>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <!-- ═══ CUERPO: REGISTRO DE PIEZAS ═══ -->
    <div class="section-banner">
        REGISTRO DE PIEZAS &mdash; FORMATO {{ strtoupper($reporte->formato === 'bombillos' ? 'BOMBILLOS (90° / LP)' : 'MOLDES') }}
    </div>

    @php
        $maxItemsPerRow = ($reporte->formato === 'bombillos') ? 2 : 4;
        $itemsPerRow = min($maxItemsPerRow, max(1, (int) $totalPiezas));
        $filas = (int) ceil($totalPiezas / $itemsPerRow);

        // Proporciones dinámicas por columna
        if ($reporte->formato === 'bombillos') {
            $wNum = round(8 / $itemsPerRow, 2);
            $wCheck = round(8 / $itemsPerRow, 2);
            $w90 = round(14 / $itemsPerRow, 2);
            $wLp = round(14 / $itemsPerRow, 2);
            $wDesc = round((100 / $itemsPerRow) - $wNum - $wCheck - $w90 - $wLp, 2);
        } else {
            $wNum = round(14 / $itemsPerRow, 2);
            $wCheck = round(12 / $itemsPerRow, 2);
            $wDesc = round((100 / $itemsPerRow) - $wNum - $wCheck, 2);
        }
    @endphp

    <table class="pieces-table">
        <thead>
            <tr>
                @for($i = 0; $i < $itemsPerRow; $i++)
                    @php $isLastSet = ($i === $itemsPerRow - 1); @endphp
                    @if($reporte->formato === 'bombillos')
                        <th width="{{ $wNum }}%">N°</th>
                        <th width="{{ $wCheck }}%">✔/✘</th>
                        <th width="{{ $w90 }}%">90°</th>
                        <th width="{{ $wLp }}%">LP</th>
                        <th width="{{ $wDesc }}%" class="{{ !$isLastSet ? 'col-divider' : '' }}">DESCRIPCIÓN</th>
                    @else
                        <th width="{{ $wNum }}%">N°</th>
                        <th width="{{ $wCheck }}%">✔/✘</th>
                        <th width="{{ $wDesc }}%" class="{{ !$isLastSet ? 'col-divider' : '' }}">DESCRIPCIÓN</th>
                    @endif
                @endfor
            </tr>
        </thead>
        <tbody>
            @for($fila = 0; $fila < $filas; $fila++)
                <tr class="{{ $fila % 2 === 1 ? 'even' : '' }}">
                    @for($col = 0; $col < $itemsPerRow; $col++)
                        @php
                            $isLastSet = ($col === $itemsPerRow - 1);
                            $dividerClass = !$isLastSet ? 'col-divider' : '';
                            $numPieza = ($fila * $itemsPerRow) + $col + 1;

                            if ($numPieza > $totalPiezas) {
                                if ($reporte->formato === 'bombillos') {
                                    echo "<td class='td-blank'></td><td class='td-blank'></td><td class='td-blank'></td><td class='td-blank'></td><td class='td-blank {$dividerClass}'></td>";
                                } else {
                                    echo "<td class='td-blank'></td><td class='td-blank'></td><td class='td-blank {$dividerClass}'></td>";
                                }
                                continue;
                            }

                            $celda = $celdas[$numPieza] ?? null;
                            $valorSimple = $celda?->valor_simple;
                            $desc = $celda?->descripcion ?? '';
                            $val90 = $celda?->valor_90 ?? '';
                            $valLp = $celda?->valor_lp ?? '';

                            if ($valorSimple === '1') {
                                $simbolo = '✔';
                                $estadoClase = 'status-ok';
                            } elseif ($valorSimple === '0') {
                                $simbolo = '✘';
                                $estadoClase = 'status-nok';
                            } else {
                                $simbolo = '—';
                                $estadoClase = 'status-none';
                            }
                        @endphp

                        <td><span class="num-badge">{{ $numPieza }}</span></td>
                        <td><span class="{{ $estadoClase }}">{{ $simbolo }}</span></td>

                        @if($reporte->formato === 'bombillos')
                            <td>{{ $val90 ?: '—' }}</td>
                            <td>{{ $valLp ?: '—' }}</td>
                            <td class="td-desc {{ $dividerClass }}">{{ $desc }}</td>
                        @else
                            <td class="td-desc {{ $dividerClass }}">{{ $desc }}</td>
                        @endif
                    @endfor
                </tr>
            @endfor
        </tbody>
    </table>

    <!-- ═══ OBSERVACIONES GENERALES ═══ -->
    <div class="obs-container">
        <div class="section-banner" style="text-align: left; padding-left: 8px;">OBSERVACIONES GENERALES</div>
        <div class="obs-content">
            {{ $reporte->observaciones ?: 'Sin observaciones generales registradas para este reporte.' }}
        </div>
    </div>

    <!-- ═══ FIRMAS DE VALIDACIÓN ═══ -->
    <table class="signatures-table">
        <tr>
            <td width="50%">
                <div class="sig-line"></div>
                <div class="sig-title">Inspector de Calidad</div>
                <div class="sig-name">{{ $inspector->nombre ?? 'N/A' }}</div>
            </td>
            <td width="50%">
                <div class="sig-line"></div>
                <div class="sig-title">Aseguramiento de Calidad</div>
                <div class="sig-name">Grupo Industrial Saavedra</div>
            </td>
        </tr>
    </table>

</div>

<!-- Paginación Automática -->
<script type="text/php">
    if (isset($pdf)) {
        $pdf->page_text(260, 765, "Página {PAGE_NUM} de {PAGE_COUNT} — GIS Calidad Formato 4CAL-01", null, 7, array(100, 116, 139));
    }
</script>

</body>
</html>
