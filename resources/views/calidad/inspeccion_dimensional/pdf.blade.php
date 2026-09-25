<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Inspección Dimensional OT {{ $reporte->ot_id }} — {{ $reporte->clase }}</title>
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
            font-size: 13px;
            font-weight: bold;
            letter-spacing: 0.5px;
            padding: 6px 10px !important;
            background-color: #ffffff;
        }

        .logo-cell {
            width: 170px;
            text-align: center;
            padding: 4px !important;
            background-color: #ffffff;
        }

        .logo-img {
            max-height: 38px;
            max-width: 160px;
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
            font-size: 7.5px;
            font-weight: bold;
            padding: 3px 2px;
            border: 1px solid #000000;
            text-transform: uppercase;
        }

        .meta-table td {
            border: 1px solid #000000;
            padding: 3px 2px;
            font-size: 8px;
            font-weight: bold;
            background-color: #ffffff;
        }

        /* ── Secciones de Información ── */
        .section-header {
            background-color: #033966;
            color: #ffffff;
            font-size: 8px;
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
            padding: 3px 6px;
            font-size: 8px;
        }

        .lbl {
            font-weight: bold;
            color: #000000;
        }

        .val {
            font-weight: bold;
            color: #033966;
        }

        /* ── Cuerpo Principal: Tabla + Diagrama ── */
        .body-layout-table {
            width: 100%;
            border-collapse: collapse;
        }

        .body-layout-table > tbody > tr > td {
            vertical-align: top;
            padding: 0;
            border: none;
        }

        .table-side {
            width: 58%;
            border-right: 1.5px solid #000000;
        }

        .diagram-side {
            width: 42%;
            padding: 4px 6px !important;
            text-align: center;
        }

        /* ── Tabla de Mediciones ── */
        .banner-mediciones {
            background-color: #033966;
            color: #ffffff;
            font-size: 8px;
            font-weight: bold;
            text-align: center;
            padding: 2.5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-top: 1px solid #000000;
            border-bottom: 1px solid #000000;
        }

        .medidas-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7px;
        }

        .medidas-table th,
        .medidas-table td {
            border: 1px solid #000000;
            padding: 2px 1px;
            text-align: center;
        }

        .medidas-table th {
            background-color: #033966;
            color: #ffffff;
            font-weight: bold;
            font-size: 6.5px;
            text-transform: uppercase;
        }

        .medidas-table tr.row-nom td {
            background-color: #f1f5f9;
            font-weight: bold;
            color: #033966;
        }

        .medidas-table tr.row-tol td {
            background-color: #f8fafc;
            font-weight: bold;
            color: #334155;
        }

        .medidas-table tr.row-data:nth-child(even) td {
            background-color: #fbfcfe;
        }

        .td-num-val {
            font-weight: bold;
            background-color: #e2e8f0 !important;
            color: #033966;
            width: 18px;
        }

        .td-obs-val {
            text-align: left !important;
            padding-left: 3px !important;
            font-size: 6.5px;
        }

        /* ── Panel del Diagrama ── */
        .diagram-title {
            font-size: 9.5px;
            font-weight: bold;
            color: #033966;
            margin-bottom: 3px;
            text-transform: uppercase;
            border-bottom: 1px solid #033966;
            padding-bottom: 2px;
        }

        .diagram-img {
            width: 100%;
            height: 310px;
            border: 1px solid #cbd5e1;
            margin-bottom: 2px;
            display: block;
        }

        .legend-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 6.5px;
            text-align: left;
        }

        .legend-table td {
            padding: 1px 2px;
            border: none;
            color: #334155;
        }

        .legend-key {
            font-weight: bold;
            color: #033966;
            width: 32px;
        }

        /* ── Observaciones ── */
        .obs-container {
            border-top: 1.5px solid #000000;
            padding: 4px 6px;
            background-color: #ffffff;
        }

        .obs-label {
            font-size: 7.5px;
            font-weight: bold;
            color: #000000;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .obs-text {
            font-size: 8px;
            color: #1e293b;
            min-height: 20px;
        }
    </style>
</head>
<body>

<div class="main-container">

    {{-- ═══ ENCABEZADO OFICIAL ═══ --}}
    <table class="header-table">
        <tr>
            <td class="title-cell">
                REPORTE DE INSPECCIÓN PARA {{ strtoupper($reporte->clase) }} (Reporte Dimensional)
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
                <td style="color: #033966; font-size: 8.5px;">{{ $esMolde ? '4CAL-04' : '4CAL-02' }}</td>
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
                <span class="val" style="font-size: 9px; color: #033966; font-weight: bold;">{{ $reporte->ot_id }}</span>
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
                    <span style="font-size: 7.5px; color: #475569; margin-left: 6px;">(Total Lote: <strong>{{ $totalPiezas }}</strong> pzas)</span>
                @endif
            </td>
            <td style="width: 37%;">
                <span class="lbl">INSPECCIONADA:</span>
                <span class="val" style="font-weight: bold; color: #033966;">{{ $piezasCompletas }} pzas completas</span>
                @if($totalPiezas > 0)
                    <span style="color: #0A8504; font-weight: bold; font-size: 8px;">({{ $porcentajeActual }}%)</span>
                @endif
            </td>
        </tr>
    </table>

    {{-- ═══ CUERPO: REPORTE DE INSPECCIÓN FINAL + DIAGRAMA ═══ --}}
    <table class="body-layout-table">
        <tbody>
            <tr>
                {{-- Columna Izquierda: Tabla de Cotas y Mediciones --}}
                <td class="table-side">
                    <div class="banner-mediciones">REPORTE DE INSPECCIÓN FINAL DE MOLDURA</div>
                    <table class="medidas-table">
                        <thead>
                            <tr>
                                <th style="width: 22px;">NUM</th>
                                <th>C1</th>
                                <th>C2</th>
                                <th>CUELLO</th>
                                <th>E</th>
                                <th>D</th>
                                <th>F1</th>
                                <th>A. TOTAL</th>
                                <th>F</th>
                                <th>ALTURA</th>
                                <th>ALTURA 2</th>
                                <th>AL CUELLO</th>
                                <th style="width: 85px;">OBSERVACIONES</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $nom = is_array($reporte->valores_nominales) ? $reporte->valores_nominales : [];
                                $tol = is_array($reporte->tolerancias) ? $reporte->tolerancias : [];
                            @endphp

                            {{-- Fila Nominal --}}
                            <tr class="row-nom">
                                <td class="td-num-val">Nom</td>
                                <td>{{ !empty($nom['c1']) ? $nom['c1'] : '—' }}</td>
                                <td>{{ !empty($nom['c2']) ? $nom['c2'] : '—' }}</td>
                                <td>{{ !empty($nom['cuello']) ? $nom['cuello'] : '—' }}</td>
                                <td>{{ !empty($nom['e']) ? $nom['e'] : '—' }}</td>
                                <td>{{ !empty($nom['d']) ? $nom['d'] : '—' }}</td>
                                <td>{{ !empty($nom['f1']) ? $nom['f1'] : '—' }}</td>
                                <td>{{ !empty($nom['a_total']) ? $nom['a_total'] : '—' }}</td>
                                <td>{{ !empty($nom['f']) ? $nom['f'] : '—' }}</td>
                                <td>{{ !empty($nom['altura']) ? $nom['altura'] : '—' }}</td>
                                <td>{{ !empty($nom['altura_2']) ? $nom['altura_2'] : '—' }}</td>
                                <td>{{ !empty($nom['al_cuello']) ? $nom['al_cuello'] : '—' }}</td>
                                <td></td>
                            </tr>

                            {{-- Fila Tolerancia --}}
                            <tr class="row-tol">
                                <td class="td-num-val">±</td>
                                <td>{{ !empty($tol['c1']) ? '± ' . $tol['c1'] : '—' }}</td>
                                <td>{{ !empty($tol['c2']) ? '± ' . $tol['c2'] : '—' }}</td>
                                <td>{{ !empty($tol['cuello']) ? '± ' . $tol['cuello'] : '—' }}</td>
                                <td>{{ !empty($tol['e']) ? '± ' . $tol['e'] : '—' }}</td>
                                <td>{{ !empty($tol['d']) ? '± ' . $tol['d'] : '—' }}</td>
                                <td>{{ !empty($tol['f1']) ? '± ' . $tol['f1'] : '—' }}</td>
                                <td>{{ !empty($tol['a_total']) ? '± ' . $tol['a_total'] : '—' }}</td>
                                <td>{{ !empty($tol['f']) ? '± ' . $tol['f'] : '—' }}</td>
                                <td>{{ !empty($tol['altura']) ? '± ' . $tol['altura'] : '—' }}</td>
                                <td>{{ !empty($tol['altura_2']) ? '± ' . $tol['altura_2'] : '—' }}</td>
                                <td>{{ !empty($tol['al_cuello']) ? '± ' . $tol['al_cuello'] : '—' }}</td>
                                <td></td>
                            </tr>

                            {{-- Filas de Piezas Medidas (Únicamente las registradas) --}}
                            @forelse($medidas as $m)
                                <tr class="row-data">
                                    <td class="td-num-val">{{ $m->numero_pieza }}</td>
                                    <td>{{ $m->c1 !== null && $m->c1 !== '' ? $m->c1 : '—' }}</td>
                                    <td>{{ $m->c2 !== null && $m->c2 !== '' ? $m->c2 : '—' }}</td>
                                    <td>{{ $m->cuello !== null && $m->cuello !== '' ? $m->cuello : '—' }}</td>
                                    <td>{{ $m->e !== null && $m->e !== '' ? $m->e : '—' }}</td>
                                    <td>{{ $m->d !== null && $m->d !== '' ? $m->d : '—' }}</td>
                                    <td>{{ $m->f1 !== null && $m->f1 !== '' ? $m->f1 : '—' }}</td>
                                    <td>{{ $m->a_total !== null && $m->a_total !== '' ? $m->a_total : '—' }}</td>
                                    <td>{{ $m->f !== null && $m->f !== '' ? $m->f : '—' }}</td>
                                    <td>{{ $m->altura !== null && $m->altura !== '' ? $m->altura : '—' }}</td>
                                    <td>{{ $m->altura_2 !== null && $m->altura_2 !== '' ? $m->altura_2 : '—' }}</td>
                                    <td>{{ $m->al_cuello !== null && $m->al_cuello !== '' ? $m->al_cuello : '—' }}</td>
                                    <td class="td-obs-val">{{ $m->observaciones !== null && $m->observaciones !== '' ? $m->observaciones : '' }}</td>
                                </tr>
                            @empty
                                <tr class="row-data">
                                    <td colspan="13" style="text-align: center; color: #64748b; padding: 6px;">Sin piezas registradas</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </td>

                {{-- Columna Derecha: Figura / Diagrama Técnico --}}
                <td class="diagram-side">
                    <div class="diagram-title">Figura 1 {{ $esMolde ? 'MOLDE' : 'BOMBILLO' }}</div>
                    @if(!empty($diagramaBase64))
                        <img src="{{ $diagramaBase64 }}" alt="Diagrama Técnico" class="diagram-img">
                    @endif
                    <table class="legend-table">
                        <tr>
                            <td class="legend-key">A:</td>
                            <td>Ajuste de Cono</td>
                            <td class="legend-key">L:</td>
                            <td>Viaje</td>
                        </tr>
                        <tr>
                            <td class="legend-key">B1/B2:</td>
                            <td>Conexión L.P./90°</td>
                            <td class="legend-key">F1/F2:</td>
                            <td>Grueso/Alt. Ceja</td>
                        </tr>
                        <tr>
                            <td class="legend-key">C1/C2:</td>
                            <td>Boca L.P./90°</td>
                            <td class="legend-key">E1/E2:</td>
                            <td>Mordaza/Inf.</td>
                        </tr>
                        <tr>
                            <td class="legend-key">K:</td>
                            <td>Alt. Cavidad</td>
                            <td class="legend-key">G1/G2:</td>
                            <td>Grueso/Alt. Tacón</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>

    {{-- ═══ OBSERVACIONES Y FIRMA ═══ --}}
    <table style="width: 100%; border-collapse: collapse; margin-top: 4px; border: 1px solid #000000;">
        <tr>
            <td style="width: 64%; padding: 6px 8px; vertical-align: top; border-right: 1px solid #000000; background-color: #fbfcfe;">
                <div class="obs-label" style="font-size: 7.5px; font-weight: bold; color: #033966; text-transform: uppercase; margin-bottom: 3px;">OBSERVACIONES / NOTAS:</div>
                <div class="obs-text" style="font-size: 8px; color: #334155; min-height: 46px; line-height: 1.3;">
                    {{ $reporte->observaciones !== null && $reporte->observaciones !== '' ? $reporte->observaciones : 'Sin observaciones registradas.' }}
                </div>
            </td>
            <td style="width: 36%; padding: 6px 10px; text-align: center; vertical-align: top; background-color: #ffffff;">
                <div style="font-size: 7.5px; font-weight: bold; color: #033966; text-transform: uppercase;">INSPECCIONADO POR:</div>
                <div style="font-size: 8.5px; font-weight: bold; color: #000000; margin-top: 2px;">{{ $reporte->inspector_nombre_completo }}</div>
                <div style="height: 38px;"></div>
                <div style="border-bottom: 1.2px solid #475569; width: 85%; margin: 0 auto 3px auto;"></div>
                <div style="font-size: 7px; color: #475569; font-weight: bold; text-transform: uppercase;">Firma del Inspector de Calidad</div>
            </td>
        </tr>
    </table>

</div>

</body>
</html>
