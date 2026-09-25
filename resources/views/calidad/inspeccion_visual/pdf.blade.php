<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Desplazamiento de Molduras OT {{ $reporte->ot_id }} — {{ $reporte->clase }}</title>
    <style>
        @page {
            margin: 5mm 5mm 5mm 5mm;
            size: letter landscape;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif;
            font-size: 8px;
            color: #000000;
            margin: 0;
            padding: 0;
            background: #ffffff;
        }

        .main-container {
            width: 100%;
            border: 1.5px solid #000000;
            background: #ffffff;
            margin: 0;
            padding: 0;
        }

        /* ── Encabezado Principal ── */
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            border: 1px solid #000000;
            padding: 4px;
            vertical-align: middle;
        }

        .title-cell {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            letter-spacing: 0.5px;
            padding: 6px 10px !important;
            background-color: #ffffff;
        }

        .logo-cell {
            width: 180px;
            text-align: center;
            padding: 4px !important;
            background-color: #ffffff;
        }

        .logo-img {
            max-height: 40px;
            max-width: 170px;
        }

        /* ── Barra de Metadatos ── */
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
        }

        .meta-table th {
            background-color: #033966;
            color: #ffffff;
            font-size: 8px;
            font-weight: bold;
            padding: 3px 2px;
            border: 1px solid #000000;
            text-transform: uppercase;
        }

        .meta-table td {
            border: 1px solid #000000;
            padding: 3px 2px;
            font-size: 8.5px;
            font-weight: bold;
            background-color: #ffffff;
        }

        /* ── Secciones de Información ── */
        .section-header {
            background-color: #033966;
            color: #ffffff;
            font-size: 8.5px;
            font-weight: bold;
            text-align: center;
            padding: 2.5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-top: 1px solid #000000;
            border-bottom: 1px solid #000000;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            border: 1px solid #000000;
            padding: 3.5px 6px;
            font-size: 8.5px;
        }

        .lbl {
            font-weight: bold;
            color: #000000;
        }

        .val {
            font-weight: bold;
            color: #033966;
        }

        /* ── Tabla de Mediciones ── */
        .banner-mediciones {
            background-color: #033966;
            color: #ffffff;
            font-size: 9px;
            font-weight: bold;
            text-align: center;
            padding: 3.5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-top: 1px solid #000000;
            border-bottom: 1px solid #000000;
        }

        .medidas-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
        }

        .medidas-table th,
        .medidas-table td {
            border: 1px solid #000000;
            padding: 3px 4px;
            text-align: center;
        }

        .medidas-table th {
            background-color: #033966;
            color: #ffffff;
            font-weight: bold;
            font-size: 8px;
            text-transform: uppercase;
        }

        .medidas-table tr:nth-child(even) td {
            background-color: #fbfcfe;
        }

        .td-num-val {
            font-weight: bold;
            background-color: #e2e8f0 !important;
            color: #033966;
            width: 40px;
        }

        .td-obs-val {
            text-align: left !important;
            padding-left: 6px !important;
        }

        /* ── Resumen de Volúmenes y Observaciones ── */
        .bottom-summary-table {
            width: 100%;
            border-collapse: collapse;
            border-top: 1.5px solid #000000;
        }

        .bottom-summary-table td {
            vertical-align: top;
            padding: 6px;
            border: 1px solid #000000;
        }

        .vol-box {
            background-color: #f8fafc;
            padding: 4px;
        }

        .vol-row {
            margin-bottom: 4px;
            font-size: 8.5px;
        }
    </style>
</head>
<body>

<div class="main-container">

    {{-- ═══ ENCABEZADO OFICIAL ═══ --}}
    <table class="header-table">
        <tr>
            <td class="title-cell">
                DESPLAZAMIENTO DE MOLDURAS (Reporte Volumétrico)
            </td>
            <td class="logo-cell">
                @if(!empty($logoBase64))
                    <img src="{{ $logoBase64 }}" alt="Grupo Industrial Saavedra" class="logo-img">
                @else
                    <strong>GRUPO INDUSTRIAL SAAVEDRA</strong>
                @endif
            </td>
        </tr>
    </table>

    {{-- ═══ BARRA DE METADATOS ═══ --}}
    <table class="meta-table">
        <thead>
            <tr>
                <th style="width: 22%;">Fecha de Elaboración</th>
                <th style="width: 22%;">Fecha de Revisión</th>
                <th style="width: 22%;">Fecha de Aprobación</th>
                <th style="width: 17%;">Nivel de Revisión</th>
                <th style="width: 17%;">Código</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>19-nov-25</td>
                <td>19-nov-25</td>
                <td>20-nov-25</td>
                <td>0</td>
                <td style="color: #033966; font-size: 9px;">4CAL-01</td>
            </tr>
        </tbody>
    </table>

    {{-- ═══ INFORMACIÓN GENERAL ═══ --}}
    <div class="section-header">INFORMACIÓN GENERAL</div>
    <table class="info-table">
        <tr>
            <td style="width: 25%;">
                <span class="lbl">FECHA:</span>
                <span class="val">{{ $reporte->fecha_elaboracion ? $reporte->fecha_elaboracion->format('d-M-y') : now()->format('d-M-y') }}</span>
            </td>
            <td style="width: 38%;">
                <span class="lbl">CLIENTE:</span>
                <span class="val">{{ $reporte->cliente ?: ($otData->cliente ?: '—') }}</span>
            </td>
            <td style="width: 37%;">
                <span class="lbl">INSPECCIONÓ:</span>
                <span class="val" style="color: #033966;">{{ $reporte->inspector_nombre_completo }}</span>
            </td>
        </tr>
        <tr>
            <td style="width: 25%;">
                <span class="lbl">OT:</span>
                <span class="val" style="font-size: 9.5px; color: #033966; font-weight: bold;">{{ $reporte->ot_id }}</span>
            </td>
            <td style="width: 38%;">
                <span class="lbl">CLASE:</span>
                <span class="val" style="font-weight: bold; color: #033966;">{{ $reporte->clase ?: '—' }}</span>
            </td>
            <td style="width: 37%;">
                <span class="lbl">MOLDURA:</span>
                <span class="val">{{ $reporte->nombre_moldura ?: ($otData->nombre_moldura ?: '—') }}</span>
            </td>
        </tr>
    </table>

    {{-- ═══ INFORMACIÓN DE ENTREGA ═══ --}}
    <div class="section-header">INFORMACIÓN DE ENTREGA</div>
    <table class="info-table">
        <tr>
            <td style="width: 25%;">
                <span class="lbl">PEDIDO:</span>
                <span class="val" style="font-weight: bold; color: #033966;">{{ $pedido ?: ($totalPiezas ?: '0') }} pzas</span>
            </td>
            <td style="width: 38%;">
                <span class="lbl">CONSIGNACIÓN:</span>
                <span class="val" style="font-weight: bold; {{ $consignacion > 0 ? 'color: #ea580c;' : '' }}">
                    {{ $consignacion > 0 ? $consignacion . ' pzas' : '0 pzas' }}
                </span>
                @if($totalPiezas > 0)
                    <span style="font-size: 8px; color: #475569; margin-left: 6px;">(Total Lote: <strong>{{ $totalPiezas }}</strong> pzas)</span>
                @endif
            </td>
            <td style="width: 37%;">
                <span class="lbl">INSPECCIONADA:</span>
                <span class="val" style="font-weight: bold; color: #033966;">{{ $piezasCompletas }} pzas completas</span>
                @if($totalPiezas > 0)
                    <span style="color: #0A8504; font-weight: bold; font-size: 8.5px;">({{ $porcentajeActual }}%)</span>
                @endif
            </td>
        </tr>
    </table>

    {{-- ═══ TABLA DE MEDICIONES DE DESPLAZAMIENTO ═══ --}}
    <div class="banner-mediciones">REGISTRO DE MEDICIÓN DE DESPLAZAMIENTO</div>
    <table class="medidas-table">
        <thead>
            <tr>
                <th style="width: 50px;">PIEZA #</th>
                <th style="width: 100px;">TEMP. AGUA</th>
                <th style="width: 140px;">VOLUMEN REAL (ml)</th>
                <th style="width: 140px;">DIF. VS VOL. IDEAL (ml)</th>
                <th>OBSERVACIONES DE LA PIEZA</th>
            </tr>
        </thead>
        <tbody>
            @forelse($medidas as $m)
                <tr>
                    <td class="td-num-val">{{ $m->numero_pieza }}</td>
                    <td>{{ $m->temp_agua !== null && $m->temp_agua !== '' ? $m->temp_agua : '20°' }}</td>
                    <td style="font-weight: bold; color: #033966;">{{ $m->vol_real !== null && $m->vol_real !== '' ? $m->vol_real : '—' }}</td>
                    <td style="font-weight: bold;">{{ $m->dif_vs_vol_ideal !== null && $m->dif_vs_vol_ideal !== '' ? $m->dif_vs_vol_ideal : '—' }}</td>
                    <td class="td-obs-val">{{ $m->observaciones !== null && $m->observaciones !== '' ? $m->observaciones : '' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: #64748b; padding: 6px;">Sin piezas registradas</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- ═══ RESUMEN DE VOLUMEN, OBSERVACIONES Y FIRMA ═══ --}}
    <table class="bottom-summary-table">
        <tr>
            <td style="width: 30%;">
                <div class="lbl" style="margin-bottom: 4px; text-transform: uppercase;">PARÁMETROS DE VOLUMEN:</div>
                <div class="vol-box">
                    <div class="vol-row">
                        <span class="lbl">VOL. IDEAL:</span>
                        <span class="val">{{ $reporte->vol_desplazado_ideal !== null && $reporte->vol_desplazado_ideal !== '' ? $reporte->vol_desplazado_ideal : '—' }} ml</span>
                    </div>
                    <div class="vol-row">
                        <span class="lbl">MÁXIMO:</span>
                        <span class="val">{{ $reporte->vol_desplazado_max !== null && $reporte->vol_desplazado_max !== '' ? $reporte->vol_desplazado_max : '—' }} ml</span>
                    </div>
                    <div class="vol-row">
                        <span class="lbl">MÍNIMO:</span>
                        <span class="val">{{ $reporte->vol_desplazado_min !== null && $reporte->vol_desplazado_min !== '' ? $reporte->vol_desplazado_min : '—' }} ml</span>
                    </div>
                </div>
            </td>
            <td style="width: 45%; vertical-align: top; padding: 6px 8px;">
                <div class="lbl" style="font-size: 7.5px; font-weight: bold; color: #033966; text-transform: uppercase; margin-bottom: 3px;">OBSERVACIONES GENERALES:</div>
                <div style="font-size: 8px; color: #1e293b; min-height: 46px; line-height: 1.3;">
                    {{ $reporte->observaciones_generales !== null && $reporte->observaciones_generales !== '' ? $reporte->observaciones_generales : 'Sin observaciones registradas.' }}
                </div>
            </td>
            <td style="width: 25%; text-align: center; vertical-align: top; background-color: #ffffff; padding: 6px 8px;">
                <div style="font-size: 7.5px; font-weight: bold; color: #033966; text-transform: uppercase;">INSPECCIONADO POR:</div>
                <div style="font-size: 8.5px; font-weight: bold; color: #000000; margin-top: 2px;">{{ $reporte->inspector_nombre_completo }}</div>
                <div style="height: 38px;"></div>
                <div style="border-bottom: 1.2px solid #475569; width: 85%; margin: 0 auto 3px auto;"></div>
                <div style="font-size: 7px; color: #475569; font-weight: bold; text-transform: uppercase;">Firma Inspector de Calidad</div>
            </td>
        </tr>
    </table>

</div>

</body>
</html>
