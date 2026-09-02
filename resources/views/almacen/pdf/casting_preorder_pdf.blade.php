<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        @page {
            size: A4 landscape;
            margin: 1cm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9px;
            color: #1a1a1a;
            line-height: 1.1;
        }

        * {
            box-sizing: border-box;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        /* ── ENCABEZADO: LOGOS Y TÍTULO ─── */
        .tbl-header {
            margin-bottom: 5px;
        }

        .tbl-header td {
            border: none;
            padding: 5px;
            vertical-align: middle;
        }

        .logo-cell {
            width: 150px;
            text-align: center;
        }

        .logo-cell img {
            max-height: 45px;
            max-width: 130px;
        }

        .title-cell {
            text-align: center;
            font-size: 15px;
            font-weight: bold;
            color: #15803d;
            text-transform: uppercase;
        }

        /* ── BLOQUE CONTROL DE CALIDAD ─── */
        .tbl-quality {
            margin-bottom: 8px;
        }

        .tbl-quality td {
            border: 1px solid #333;
            padding: 3px 4px;
            text-align: center;
            font-size: 8px;
        }

        .quality-label {
            background-color: #e8e8e8;
            font-weight: bold;
            text-transform: uppercase;
            width: 13%;
        }

        .quality-value {
            font-weight: bold;
            width: 7%;
        }

        /* ── DATOS GENERALES ─── */
        .tbl-general {
            margin-bottom: 8px;
        }

        .tbl-general td {
            border: 1px solid #aaa;
            padding: 4px 8px;
        }

        .gen-label {
            background-color: #f0f0f0;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8px;
            width: 12%;
        }

        .gen-value {
            font-weight: bold;
            font-size: 10px;
        }

        .gen-value-folio {
            font-weight: bold;
            color: #0369a1;
            font-size: 11px;
        }

        /* ── TABLA PRINCIPAL ─── */
        .tbl-main th {
            background-color: #15803d;
            color: #ffffff;
            padding: 5px 3px;
            font-size: 8px;
            font-weight: bold;
            text-align: center;
            border: 1px solid #15803d;
            text-transform: uppercase;
        }

        .tbl-main td {
            border: 1px solid #cccccc;
            padding: 5px 4px;
            font-size: 9px;
            text-align: center;
            vertical-align: middle;
        }

        .desc-cell {
            text-align: left;
            padding-left: 6px;
            font-weight: bold;
        }

        .code-cell {
            font-family: 'Courier New', Courier, monospace;
            font-weight: bold;
            color: #0369a1;
            font-size: 8.5px;
        }

        .row-alt {
            background-color: #f0f9ff;
        }

        /* ── OBSERVACIONES ─── */
        .tbl-obs {
            margin-top: 8px;
        }

        .tbl-obs td {
            border: 1px solid #aaa;
            padding: 4px 6px;
            font-size: 8.5px;
        }

        .obs-label {
            background-color: #f0f0f0;
            font-weight: bold;
            font-size: 8px;
            text-transform: uppercase;
            width: 12%;
            color: #333;
        }

        /* ── FIRMAS ─── */
        .tbl-firmas {
            margin-top: 20px;
        }

        .tbl-firmas td {
            width: 33.33%;
            text-align: center;
            padding: 0 15px;
            border: none;
            vertical-align: bottom;
        }

        .firma-espacio {
            height: 30px;
        }

        .firma-linea {
            border-top: 1px solid #222;
            width: 85%;
            margin: 0 auto 3px auto;
        }

        .firma-rol {
            font-weight: bold;
            font-size: 8.5px;
            text-transform: uppercase;
            color: #0369a1;
        }

        .firma-sub {
            font-size: 8px;
            color: #555;
            margin-top: 2px;
        }

        /* ── FOOTER ─── */
        .footer {
            position: fixed;
            bottom: -0.8cm;
            right: 0;
            width: 100%;
            text-align: right;
            font-size: 7px;
            color: #999;
            border-top: 0.5px solid #ddd;
            padding-top: 2px;
        }
    </style>
</head>

<body>

    @php
        $pdfPages = isset($pages) ? $pages : [$data];
    @endphp

    @foreach($pdfPages as $pageIdx => $data)
        @if($pageIdx > 0)
            <div style="page-break-before: always;"></div>
        @endif

        {{-- BLOQUE 1: LOGOS Y TÍTULO --}}
        <table class="tbl-header">
            <tr>
                <td class="logo-cell">
                    <img src="{{ public_path('images/lg_saavedra2.png') }}" alt="Logo GIS">
                </td>
                <td class="title-cell">
                    PREORDEN DE FABRICACIÓN DE CASTING
                </td>
                <td class="logo-cell">
                    <img src="{{ public_path('images/lg_saavedra.png') }}" alt="GIS">
                </td>
            </tr>
        </table>

        {{-- BLOQUE 2: CONTROL DE CALIDAD --}}
        <table class="tbl-quality">
            <tr>
                <td class="quality-label">Fecha de Elaboración</td>
                <td class="quality-value">{{ date('d-M-y') }}</td>
                <td class="quality-label">Fecha de Revisión</td>
                <td class="quality-value">{{ date('d-M-y') }}</td>
                <td class="quality-label">Fecha de Aprobación</td>
                <td class="quality-value">{{ date('d-M-y') }}</td>
                <td class="quality-label">Código</td>
                <td class="quality-value">4ALM-17</td>
                <td class="quality-label">Nivel de Revisión</td>
                <td class="quality-value">0</td>
            </tr>
        </table>

        {{-- BLOQUE 3: DATOS GENERALES --}}
        <table class="tbl-general">
            <tr>
                <td class="gen-label">Proveedor</td>
                <td class="gen-value" style="width: 30%;">{{ $data['proveedor'] }}</td>
                <td class="gen-label">Fecha Emisión</td>
                <td class="gen-value" style="width: 14%;">
                    {{ \Carbon\Carbon::parse($data['fecha_creacion'])->format('d/m/Y') }}
                </td>
                <td class="gen-label">Folio</td>
                <td class="gen-value-folio" style="width: 14%;">{{ $data['folio'] }}</td>
            </tr>
            <tr>
                <td class="gen-label">Moldura</td>
                <td class="gen-value" colspan="3">{{ $data['moldura'] }}</td>
                <td class="gen-label">Orden de Trabajo</td>
                @php
                    $rawOtVal = $data['ot'] ?? '';
                    $otPartClean = trim(explode(' - ', $rawOtVal)[0]);
                    if (preg_match('/(?:OT\s*)?(\d+(?:_[rR]\d+)?)/i', $otPartClean, $mOtMatch)) {
                        $otDisplayVal = $mOtMatch[1];
                    } else {
                        $otDisplayVal = preg_replace('/^OT\s*/i', '', $otPartClean);
                    }
                @endphp
                <td class="gen-value">{{ $otDisplayVal ?: $rawOtVal }}</td>
            </tr>
        </table>

        {{-- BLOQUE 4: TABLA PRINCIPAL --}}
        <table class="tbl-main">
            <thead>
                <tr>
                    <th style="width: 10%;">Tipo de Modelo</th>
                    <th style="width: 9%;">Cant. Fabricar</th>
                    <th style="width: 9%;">Cant. Consign.</th>
                    <th style="width: 18%;">Descripción</th>
                    <th style="width: 10%;">Material</th>
                    <th style="width: 13%;">Código de Modelo</th>
                    <th style="width: 10%;">Peso Juego (KG)</th>
                    <th style="width: 10%;">Peso Total (KG)</th>
                    <th style="width: 11%;">Fecha de Entrega</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['filas'] as $index => $fila)
                    <tr class="{{ $index % 2 == 1 ? 'row-alt' : '' }}">
                        <td>{{ $fila['tipo_modelo'] ?? '' }}</td>
                        <td>{{ $fila['cantidad_fabricar'] ?? $fila['cant_fabricar'] ?? 0 }}</td>
                        <td>{{ $fila['cantidad_consignacion'] ?? $fila['cant_consignacion'] ?? 0 }}</td>
                        @php
                            $claseOriginal = trim($fila['clase_nombre'] ?? $fila['descripcion'] ?? $fila['clase'] ?? '');
                            $tipo = trim($fila['tipo_modelo'] ?? '');
                            $prefijo = (stripos($tipo, 'templadera') !== false) ? 'Templadera' : 'Modelo';
                            
                            // Capitalizar el nombre de la clase de forma elegante
                            $claseFormateada = mb_convert_case($claseOriginal, MB_CASE_TITLE, "UTF-8");
                            
                            $claseNombreFinal = $claseFormateada;
                            if (!empty($claseOriginal) && stripos($claseOriginal, $prefijo) === false) {
                                $claseNombreFinal = $prefijo . ' ' . $claseFormateada;
                            }
                        @endphp
                        <td class="desc-cell">{{ $claseNombreFinal }}</td>
                        <td>{{ $fila['material'] ?? '' }}</td>
                        <td class="code-cell">{{ $fila['codigo_modelo'] ?? $fila['codigo'] ?? '' }}</td>
                        <td>{{ number_format((float) ($fila['peso_juego'] ?? 0), 3) }}</td>
                        <td>{{ number_format((float) ($fila['peso_total'] ?? 0), 3) }}</td>
                        <td>
                            @php
                                $fechaEntregaFila = null;
                                if (!empty($fila['fecha_entrega_row'])) {
                                    $fechaEntregaFila = $fila['fecha_entrega_row'];
                                } elseif (!empty($fila['fecha_entrega'])) {
                                    $fechaEntregaFila = $fila['fecha_entrega'];
                                } elseif (!empty($data['fecha_entrega'])) {
                                    $fechaEntregaFila = $data['fecha_entrega'];
                                }
                            @endphp
                            {{ $fechaEntregaFila ? \Carbon\Carbon::parse($fechaEntregaFila)->format('d/m/Y') : '' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- BLOQUE 5: OBSERVACIONES --}}
        <table class="tbl-obs">
            <tr>
                <td class="obs-label">Observaciones</td>
                <td>{{ $data['observaciones'] ?: 'Sin observaciones adicionales.' }}</td>
            </tr>
        </table>

        {{-- BLOQUE 6: FIRMAS --}}
        <table class="tbl-firmas">
            <tr>
                <td>
                    <div class="firma-espacio"></div>
                    <div class="firma-linea"></div>
                    <div class="firma-rol">Departamento de Almacén</div>
                    <div class="firma-sub">(Nombre y Firma)</div>
                </td>
                <td>
                    <div class="firma-espacio"></div>
                    <div class="firma-linea"></div>
                    <div class="firma-rol">Departamento de Calidad</div>
                    <div class="firma-sub">(Nombre y Firma)</div>
                </td>
                <td>
                    <div class="firma-espacio"></div>
                    <div class="firma-linea"></div>
                    <div class="firma-rol">Departamento de Ingeniería (Proveedor)</div>
                    <div class="firma-sub">(Nombre, Firma y Sello)</div>
                </td>
            </tr>
        </table>
    @endforeach

    <div class="footer">
        Grupo Industrial Saavedra &nbsp;|&nbsp; Formato 4ALM-17 &nbsp;|&nbsp; Generado el {{ date('d/m/Y H:i') }}
        &nbsp;|&nbsp; Copia Controlada
    </div>

</body>
</html>
