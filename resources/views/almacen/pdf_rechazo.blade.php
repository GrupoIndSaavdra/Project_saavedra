<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Rechazo de Modelos - {{ $liberacion->ot }}</title>
    <style>
        /* === BASE === */
        @page {
            margin: 10px 15px;
            size: letter landscape;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 7.2px;
            line-height: 1.05;
            color: #222;
            margin: 0;
            padding: 0;
        }

        /* === ENCABEZADO === */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #000;
            margin-bottom: 4px;
        }

        .header-table td {
            padding: 1px 2.5px;
            border: 1px solid #000;
            vertical-align: middle;
        }

        .header-logo {
            width: 15%;
            text-align: center;
        }

        .header-logo img {
            max-height: 24px;
            max-width: 65px;
        }

        .header-title {
            width: 60%;
            text-align: center;
            font-size: 9.5px;
            font-weight: bold;
            text-transform: uppercase;
            color: #9c0300;
        }

        .header-meta {
            width: 25%;
            font-size: 6.8px;
            line-height: 1.2;
        }

        .header-checkboxes {
            font-size: 6.8px;
            text-align: center;
            font-weight: bold;
            padding: 2px !important;
        }

        /* === TITULOS DE SECCION === */
        .section-title {
            font-size: 7.5px;
            font-weight: bold;
            background-color: #9c0300;
            color: #fff;
            padding: 1.5px 3px;
            margin-top: 3px;
            margin-bottom: 2px;
            text-transform: uppercase;
        }

        .section-title-danger {
            background-color: #9c0300;
        }

        /* === TABLAS DE DATOS === */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 3px;
            table-layout: fixed;
        }

        .data-table th {
            background-color: #9c0300;
            color: #fff;
            border: 1px solid #000;
            padding: 1.5px 2px;
            text-align: center;
            font-size: 6.8px;
            font-weight: bold;
        }

        .data-table th.sub-header {
            background-color: #7c1a1a;
        }

        .data-table td {
            border: 1px solid #aaa;
            padding: 1.5px 2px;
            text-align: center;
            font-size: 6.5px;
        }

        .text-left {
            text-align: left !important;
        }

        .bg-row-label {
            background-color: #fef2f2;
            font-weight: bold;
            color: #9c0300;
            text-align: left !important;
        }

        .val-na {
            color: #9c0300;
            font-weight: bold;
            font-size: 6.8px;
        }

        /* === OBSERVACIONES === */
        .obs-box {
            border: 1px solid #aaa;
            padding: 2.5px 4px;
            min-height: 20px;
            font-size: 7px;
            line-height: 1.15;
            background: #fdf8f8;
            margin-bottom: 5px;
        }

        .obs-label {
            font-weight: bold;
            color: #9c0300;
        }

        /* === FIRMAS === */
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 75px;
        }

        .footer-table td {
            width: 50%;
            text-align: center;
            padding: 0 5px;
            vertical-align: bottom;
        }

        .signature-line {
            border-top: 1px solid #000;
            margin-top: 35px;
            margin-bottom: 4px;
        }

        .signature-name {
            font-weight: bold;
            font-size: 11px;
        }

        .signature-role {
            font-size: 9px;
            color: #444;
        }

        /* === IMAGENES DE REFERENCIA === */
        .ref-images-row {
            text-align: center;
            margin: 3px 0;
        }

        .ref-images-row img {
            max-height: 48px;
            max-width: 110px;
            margin: 0 2px;
            border: 1px solid #ccc;
            display: inline-block;
            vertical-align: middle;
        }
    </style>
</head>

<body>

    @php
        $tipo = $liberacion->tipo_modelo ?? '';
        $activas = \App\Models\LiberacionModeloFundicion::tablasActivas($tipo);

        $na = '<span class="val-na">N / A</span>';

        // Helper para obtener ruta física absoluta (optimizado)
        $toBase64 = function ($path) {
            $fullPath = public_path($path);
            return file_exists($fullPath) ? $fullPath : '';
        };

        // Helper: formatea valor de tabla activa (0.000 si vacio) o N/A si inactiva
        $fmt = function ($grupo, $item, $col, $activa) use ($na, $liberacion) {
            if (!$activa)
                return $na;
            $val = $liberacion->{$grupo}[$item][$col] ?? null;
            return $val !== null ? number_format((float) $val, 3) : '0.000';
        };

        $modeloActivo = in_array('modelo', $activas);
        $plantillaActiva = in_array('plantilla', $activas);
        $fondoActivo = in_array('fondo', $activas);
        $obturadorActivo = in_array('obturador', $activas);

        $itemsModelo = \App\Models\LiberacionModeloFundicion::itemsModelo();
        $matrixCols = \App\Models\LiberacionModeloFundicion::matrixCols();
        $itemsFondo = \App\Models\LiberacionModeloFundicion::itemsFondo();
        $itemsObturador = \App\Models\LiberacionModeloFundicion::itemsObturador();

        // Extraer número de OT y nombre de moldura de forma robusta
        $otStr = preg_replace('/_\d{8}_\d{6}.*$/', '', $liberacion->ot ?? '');
        $otNum = '';
        $molduraName = '';
        if (preg_match('/^OT\s*(\d+)\s*-\s*(.*)$/i', $otStr, $matches)) {
            $otNum = $matches[1];
            $molduraName = trim($matches[2]);
        } elseif (preg_match('/^OT\s*(\d+)$/i', $otStr, $matches)) {
            $otNum = $matches[1];
            $molduraName = '';
        } else {
            if (preg_match('/^(\d+)/', preg_replace('/^OT\s+/i', '', $otStr), $matches)) {
                $otNum = $matches[1];
                $molduraName = trim(str_replace($matches[0], '', preg_replace('/^OT\s+/i', '', $otStr)));
                $molduraName = trim($molduraName, ' -');
            } else {
                $otNum = $otStr;
                $molduraName = '';
            }
        }
        $esRechazo = true;
    @endphp

    <table class="header-table">
        <tr>
            <td class="header-logo" rowspan="3" style="width: 12%; text-align: center;">
                @php $logoBase64 = $toBase64('images/lg_saavedra.png'); @endphp
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" alt="Logo">
                @endif
            </td>
            <td class="header-title" colspan="4"
                style="width: 63%; text-align: center; font-size: 11px; font-weight: bold; color: #9c0300; text-transform: uppercase;">
                FORMATO DE RECHAZO DE MODELOS
            </td>
            <td class="header-meta" style="width: 25%; font-size: 7.5px; line-height: 1.3;">
                <strong>Codigo:</strong> F-CCL-LDM<br>
                <strong>Version:</strong> B
            </td>
        </tr>
        <tr>
            <td colspan="2" style="width: 28%; font-size: 7px; padding: 2px 4px; border: 1px solid #000;">
                <strong>MOLDURA:</strong> {{ $molduraName ?: 'N/A' }}
            </td>
            <td style="width: 15%; font-size: 7px; padding: 2px 4px; border: 1px solid #000;">
                <strong>O.T.:</strong> {{ $otNum }}
            </td>
            <td colspan="2"
                style="width: 45%; font-size: 7px; padding: 2px 4px; border: 1px solid #000; white-space: nowrap;">
                <strong>TIPO DE LIBERACIÓN:</strong>
                <span style="font-size: 6.5px; margin-left: 2px; font-weight: bold;">
                    MOLDE [ {{ $tipo == 'Molde' ? 'X' : ' ' }} ] &nbsp;
                    FONDO [ {{ $tipo == 'Fondo' ? 'X' : ' ' }} ] &nbsp;
                    CORONA [ {{ $tipo == 'Corona' ? 'X' : ' ' }} ] &nbsp;
                    PLATO [ {{ $tipo == 'Plato' ? 'X' : ' ' }} ] &nbsp;
                    EMBUDO [ {{ $tipo == 'Embudo' ? 'X' : ' ' }} ] &nbsp;
                    C. SOPLO [ {{ $tipo == 'Cabeza de Soplo' ? 'X' : ' ' }} ] &nbsp;
                    C. OBTURADOR [ {{ $tipo == 'Candado Obturador' ? 'X' : ' ' }} ] &nbsp;
                    OBTURADOR [ {{ $tipo == 'Obturador' ? 'X' : ' ' }} ] &nbsp;
                    BOMBILLO [ {{ $tipo == 'Bombillo' ? 'X' : ' ' }} ]
                </span>
            </td>
        </tr>
        <tr>
            <td style="width: 13%; font-size: 7px; padding: 2px 4px; vertical-align: middle; border: 1px solid #000;">
                <strong>FECHA INSPECCIÓN:</strong>
                {{ $liberacion->fecha_revision ? $liberacion->fecha_revision->format('d/m/Y') : now()->format('d/m/Y') }}
            </td>
            <td style="width: 15%; font-size: 7px; padding: 2px 4px; vertical-align: middle; border: 1px solid #000;">
                <strong>ESTADO:</strong> <span
                    style="font-weight: bold; color: #9c0300;">RECHAZADO</span>
            </td>
            <td colspan="2"
                style="width: 37.5%; font-size: 7px; padding: 2px 4px; vertical-align: middle; border: 1px solid #000;">
                <strong>INSPECCIONÓ (CALIDAD):</strong> {{ $liberacion->user_nombre_calidad ?: 'N/A' }}
            </td>
            <td
                style="width: 22.5%; font-size: 7px; padding: 2px 4px; vertical-align: middle; border: 1px solid #000; white-space: nowrap;">
                <strong>FIRMA:</strong>
            </td>
        </tr>
    </table>

    {{-- MASTER TABLE PARA LAYOUT DE 2 COLUMNAS (28% IZQUIERDA / 72% DERECHA) --}}
    <table style="width: 100%; border-collapse: collapse; table-layout: fixed;">
        <tr>
            {{-- COLUMNA IZQUIERDA: BARRA LATERAL DE IMAGENES --}}
            <td style="width: 28%; vertical-align: top; padding-right: 5px; text-align: center;">
                @if($toBase64('images/Liberación Calidad/Figura 1.jpg'))
                    <img src="{{ $toBase64('images/Liberación Calidad/Molde 1.jpg') }}"
                        style="width: 90%; margin-bottom: 12px;">
                @endif
                @if($toBase64('images/Liberación Calidad/Figura 2.jpg'))
                    <img src="{{ $toBase64('images/Liberación Calidad/Figura 2.jpg') }}"
                        style="width: 90%; margin-bottom: 12px;">
                @endif
                @if($toBase64('images/Liberación Calidad/Figura 3.jpg'))
                    <img src="{{ $toBase64('images/Liberación Calidad/Figura 3.jpg') }}"
                        style="width: 90%; margin-bottom: 12px;">
                @endif
            </td>

            {{-- COLUMNA DERECHA: DATOS E IMAGENES SECUNDARIAS --}}
            <td style="width: 72%; vertical-align: top; padding-left: 5px;">

                {{-- SUB-TABLA SUPERIOR: FONDO/OBTURADOR (Izquierda 35%) y DIMENSION DEL MODELO (Derecha 65%) --}}
                <table style="width: 100%; table-layout: fixed; border-collapse: collapse;">
                    <tr>
                        <td style="width: 35%; vertical-align: top; padding-right: 5px;">

                            <div class="section-title">{{ in_array($tipo, ['Corona', 'Plato', 'Embudo', 'Cabeza de Soplo', 'Candado Obturador']) ? strtoupper($tipo) : 'FONDO' }}</div>
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th style="width:40%;">ITEM</th>
                                        <th style="width:30%;">DIBUJO (")</th>
                                        <th style="width:30%;">FISICO (")</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($itemsFondo as $key => $label)
                                        <tr>
                                            <td class="bg-row-label">{!! $label !!}</td>
                                            <td>{!! $fmt('medidas_fondo', $key, 'dibujo', $fondoActivo) !!}</td>
                                            <td>{!! $fmt('medidas_fondo', $key, 'fisico', $fondoActivo) !!}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <div class="section-title" style="margin-top: 5px;">OBTURADOR</div>
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th style="width:40%;">ITEM</th>
                                        <th style="width:30%;">DIBUJO (")</th>
                                        <th style="width:30%;">FISICO (")</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($itemsObturador as $key => $label)
                                        <tr>
                                            <td class="bg-row-label">{!! $label !!}</td>
                                            <td>{!! $fmt('medidas_obturador', $key, 'dibujo', $obturadorActivo) !!}</td>
                                            <td>{!! $fmt('medidas_obturador', $key, 'fisico', $obturadorActivo) !!}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                        </td>

                        <td style="width: 65%; vertical-align: top; padding-left: 5px;">
                            <div class="section-title">DIMENSIÓN DEL MODELO (MACHO Y HEMBRA)</div>
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th style="width:18%;">DIBUJO (")</th>
                                        <th style="width:38%;">ITEM</th>
                                        <th style="width:22%;">MACHO (")</th>
                                        <th style="width:22%;">HEMBRA (")</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($itemsModelo as $key => $label)
                                        <tr>
                                            <td>{!! $fmt('medidas_modelo', $key, 'dibujo', $modeloActivo) !!}</td>
                                            <td class="text-left"><strong>{{ $key }}</strong> — {{ $label }}</td>
                                            <td>{!! $fmt('medidas_modelo', $key, 'macho', $modeloActivo) !!}</td>
                                            <td>{!! $fmt('medidas_modelo', $key, 'hembra', $modeloActivo) !!}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </table>

                {{-- TABLAS DE PLANTILLA (100% de la columna derecha) --}}
                <div class="section-title">PLANTILLA</div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th rowspan="2" style="width:16%;">MEDIDA</th>
                            @foreach ($matrixCols as $main => $subs)
                                <th colspan="{{ count($subs) }}">{{ $main }}</th>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach ($matrixCols as $main => $subs)
                                @foreach ($subs as $sub)
                                    <th class="sub-header" style="width: 7%;">{{ $sub }}</th>
                                @endforeach
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="bg-row-label">Dibujo (")</td>
                            @foreach ($matrixCols as $main => $subs)
                                @foreach ($subs as $sub)
                                    <td>{!! $fmt('medidas_plantilla', "plantilla_{$sub}", 'dibujo', $plantillaActiva) !!}</td>
                                @endforeach
                            @endforeach
                        </tr>
                        <tr>
                            <td class="bg-row-label">Fisico (")</td>
                            @foreach ($matrixCols as $main => $subs)
                                @foreach ($subs as $sub)
                                    <td>{!! $fmt('medidas_plantilla', "plantilla_{$sub}", 'fisico', $plantillaActiva) !!}</td>
                                @endforeach
                            @endforeach
                        </tr>
                    </tbody>
                </table>

                <div class="section-title">TEMPLADERA DE MADERA</div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th rowspan="2" style="width:16%;">MEDIDA</th>
                            @foreach ($matrixCols as $main => $subs)
                                <th colspan="{{ count($subs) }}">{{ $main }}</th>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach ($matrixCols as $main => $subs)
                                @foreach ($subs as $sub)
                                    <th class="sub-header" style="width: 7%;">{{ $sub }}</th>
                                @endforeach
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="bg-row-label">Dibujo (")</td>
                            @foreach ($matrixCols as $main => $subs)
                                @foreach ($subs as $sub)
                                    <td>{!! $fmt('medidas_plantilla', "templadera_{$sub}", 'dibujo', $plantillaActiva) !!}</td>
                                @endforeach
                            @endforeach
                        </tr>
                        <tr>
                            <td class="bg-row-label">Fisico (")</td>
                            @foreach ($matrixCols as $main => $subs)
                                @foreach ($subs as $sub)
                                    <td>{!! $fmt('medidas_plantilla', "templadera_{$sub}", 'fisico', $plantillaActiva) !!}</td>
                                @endforeach
                            @endforeach
                        </tr>
                    </tbody>
                </table>

                {{-- OBSERVACIONES --}}
                <div class="section-title" style="margin-top: 5px;">OBSERVACIONES</div>
                <div class="obs-box" style="margin-bottom: 5px;">
                    @if (in_array($tipo, ['Fondo', 'Corona', 'Plato', 'Embudo', 'Cabeza de Soplo', 'Candado Obturador']))
                        <span class="obs-label">{{ $tipo }}:</span>
                        {!! nl2br(e($liberacion->observaciones_fondo ?: 'Ninguna.')) !!}
                    @elseif ($tipo === 'Obturador')
                        <span class="obs-label">Obturador:</span>
                        {!! nl2br(e($liberacion->observaciones_obturador ?: 'Ninguna.')) !!}
                    @elseif ($tipo === 'Molde' || $tipo === 'Bombillo')
                        <span class="obs-label">Modelo:</span>
                        {!! nl2br(e($liberacion->observaciones_modelo ?: 'Ninguna.')) !!} |
                        <span class="obs-label">Plantilla/Templadera:</span>
                        {!! nl2br(e($liberacion->observaciones_plantilla ?: 'Ninguna.')) !!}
                    @else
                        Ninguna.
                    @endif
                </div>

                <div class="section-title" style="margin-top: 5px; background-color: #9c0300;">MOTIVO DE RECHAZO</div>
                <div class="obs-box"
                    style="border: 1px solid #fca5a5; background-color: #fef2f2; color: #9c0300; min-height: 25px; margin-bottom: 5px;">
                    {!! nl2br(e($liberacion->motivo_rechazo ?: 'No especificado.')) !!}
                </div>

                {{-- SUB-TABLA INFERIOR: IMAGENES DE PLANTILLA --}}
                <table style="width: 100%; text-align: center; border-collapse: collapse; margin-top: 3px;">
                    <tr>
                        <td style="width: 50%; vertical-align: middle; padding-right: 10px;">
                            @if($toBase64('images/Liberación Calidad/Figura 4.jpg'))
                                <img src="{{ $toBase64('images/Liberación Calidad/Figura 4.jpg') }}"
                                    style="max-height: 150px; max-width: 100%;">
                            @endif
                        </td>
                        <td style="width: 50%; vertical-align: middle; padding-left: 10px;">
                            @if($toBase64('images/Liberación Calidad/Figura 5.jpg'))
                                <img src="{{ $toBase64('images/Liberación Calidad/Figura 5.jpg') }}"
                                    style="max-height: 150px; max-width: 100%;">
                            @endif
                        </td>
                    </tr>
                </table>

                {{-- FIRMAS DENTRO DE LA COLUMNA DERECHA --}}
                <table class="footer-table" style="margin-top: 20px;">
                    <tr>
                        <td>
                            <div class="signature-line"></div>
                            <div class="signature-name">Departamento de Almacén</div>
                            <div class="signature-role">Nombre y Firma</div>
                        </td>
                        <td>
                            <div class="signature-line"></div>
                            <div class="signature-name">Departamento de Calidad</div>
                            <div class="signature-role">Nombre y Firma</div>
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>

</body>

</html>
