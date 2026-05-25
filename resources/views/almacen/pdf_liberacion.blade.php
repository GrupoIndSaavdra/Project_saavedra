<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Liberacion de Modelos - {{ $liberacion->ot }}</title>
    <style>
        /* === BASE === */
        @page {
            margin: 10px 15px;
            size: letter landscape;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 7.5px;
            line-height: 1.1;
            color: #222;
            margin: 0;
            padding: 0;
        }

        /* === ENCABEZADO === */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #000;
            margin-bottom: 5px;
        }

        .header-table td {
            padding: 2px 5px;
            border: 1px solid #000;
            vertical-align: middle;
        }

        .header-logo {
            width: 15%;
            text-align: center;
        }

        .header-logo img {
            max-height: 35px;
            max-width: 80px;
        }

        .header-title {
            width: 60%;
            text-align: center;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            color: #033966;
        }

        .header-meta {
            width: 25%;
            font-size: 7px;
            line-height: 1.3;
        }

        .header-checkboxes {
            font-size: 7px;
            text-align: center;
            font-weight: bold;
            padding: 3px !important;
        }

        /* === TITULOS DE SECCION === */
        .section-title {
            font-size: 8px;
            font-weight: bold;
            background-color: #033966;
            color: #fff;
            padding: 2px 4px;
            margin-top: 5px;
            margin-bottom: 3px;
            text-transform: uppercase;
        }

        .section-title-danger {
            background-color: #9c0300;
        }

        /* === TABLAS DE DATOS === */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
            table-layout: fixed;
        }

        .data-table th {
            background-color: #033966;
            color: #fff;
            border: 1px solid #000;
            padding: 2px 3px;
            text-align: center;
            font-size: 7px;
            font-weight: bold;
        }

        .data-table th.sub-header {
            background-color: #1a5e9e;
        }

        .data-table td {
            border: 1px solid #aaa;
            padding: 2px 3px;
            text-align: center;
            font-size: 7px;
        }

        .text-left {
            text-align: left !important;
        }

        .bg-row-label {
            background-color: #e8f2ff;
            font-weight: bold;
            color: #033966;
            text-align: left !important;
        }

        .val-na {
            color: #033966;
            /* Azul fuerte para resaltar */
            font-weight: bold;
            font-size: 7px;
        }

        /* === OBSERVACIONES === */
        .obs-box {
            border: 1px solid #aaa;
            padding: 4px 6px;
            min-height: 35px;
            font-size: 7.5px;
            line-height: 1.2;
            background: #fafcff;
            margin-bottom: 10px;
        }

        .obs-label {
            font-weight: bold;
            color: #033966;
        }

        /* === FIRMAS === */
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }

        .footer-table td {
            width: 33.33%;
            text-align: center;
            padding: 0 5px;
            vertical-align: bottom;
        }

        .signature-line {
            border-top: 1px solid #000;
            margin-top: 20px;
            margin-bottom: 2px;
        }

        .signature-name {
            font-weight: bold;
            font-size: 7px;
        }

        .signature-role {
            font-size: 6px;
            color: #555;
        }

        /* === IMAGENES DE REFERENCIA === */
        .ref-images-row {
            text-align: center;
            margin: 4px 0;
        }

        .ref-images-row img {
            max-height: 55px;
            max-width: 120px;
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

        // Helper Base64 a prueba de fallos
        $toBase64 = function ($path) {
            $fullPath = public_path($path);
            if (file_exists($fullPath)) {
                $type = pathinfo($fullPath, PATHINFO_EXTENSION);
                $data = file_get_contents($fullPath);
                return 'data:image/' . $type . ';base64,' . base64_encode($data);
            }
            return '';
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
    @endphp

    <table class="header-table">
        <tr>
            <td class="header-logo" rowspan="2">
                @php $logoBase64 = $toBase64('images/lg_saavedra.png'); @endphp
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" alt="Logo">
                @endif
            </td>
            <td class="header-title">FORMATO DE LIBERACION DE MODELOS</td>
            <td class="header-meta">Codigo: F-CCL-LDM<br>Version: B</td>
        </tr>
        <tr>
            <td class="header-checkboxes">
                MOLDURA: [ {{ $tipo == 'Molde' ? 'X' : ' ' }} ] &nbsp;&nbsp;
                FONDO: [ {{ $tipo == 'Fondo' ? 'X' : ' ' }} ] &nbsp;&nbsp;
                LIBERACIÓN DE BOMBILLO: [ {{ $tipo == 'Bombillo' ? 'X' : ' ' }} ] &nbsp;&nbsp;
                TIPO: O.T. <u>{{ $liberacion->ot }}</u>
            </td>
            <td class="header-meta">
                Fecha:
                {{ $liberacion->fecha_revision ? $liberacion->fecha_revision->format('d/m/Y') : now()->format('d/m/Y') }}<br>
                Estado: <strong>{{ strtoupper($liberacion->estado) }}</strong>
            </td>
        </tr>
    </table>

    {{-- MASTER TABLE PARA LAYOUT DE 2 COLUMNAS (28% IZQUIERDA / 72% DERECHA) --}}
    <table style="width: 100%; border-collapse: collapse; table-layout: fixed;">
        <tr>
            {{-- COLUMNA IZQUIERDA: BARRA LATERAL DE IMAGENES --}}
            <td style="width: 28%; vertical-align: top; padding-right: 5px; text-align: center;">
                @if($toBase64('images/Liberación Calidad/Figura 1.png'))
                    <img src="{{ $toBase64('images/Liberación Calidad/Molde 1.png') }}"
                        style="width: 85%; margin-bottom: 15px;">
                @endif
                @if($toBase64('images/Liberación Calidad/Figura 2.png'))
                    <img src="{{ $toBase64('images/Liberación Calidad/Figura 2.png') }}"
                        style="width: 85%; margin-bottom: 15px;">
                @endif
                @if($toBase64('images/Liberación Calidad/Figura 3.png'))
                    <img src="{{ $toBase64('images/Liberación Calidad/Figura 3.png') }}"
                        style="width: 85%; margin-bottom: 15px;">
                @endif
            </td>

            {{-- COLUMNA DERECHA: DATOS E IMAGENES SECUNDARIAS --}}
            <td style="width: 72%; vertical-align: top; padding-left: 5px;">

                {{-- SUB-TABLA SUPERIOR: FONDO/OBTURADOR (Izquierda 35%) y DIMENSION DEL MODELO (Derecha 65%) --}}
                <table style="width: 100%; table-layout: fixed; border-collapse: collapse;">
                    <tr>
                        <td style="width: 35%; vertical-align: top; padding-right: 5px;">

                            <div class="section-title">FONDO</div>
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
                <div class="obs-box" style="margin-bottom: 10px;">
                    @if ($tipo === 'Fondo')
                        <span class="obs-label">Fondo:</span>
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

                    @if ($liberacion->estado === 'rechazado')
                        <br><span class="obs-label" style="color:#9c0300;">Motivo de Rechazo:</span> <span
                            style="color:#9c0300;">{!! nl2br(e($liberacion->motivo_rechazo)) !!}</span>
                    @endif
                </div>

                {{-- SUB-TABLA INFERIOR: IMAGENES DE PLANTILLA --}}
                <table style="width: 100%; text-align: center; border-collapse: collapse; margin-top: 5px;">
                    <tr>
                        <td style="width: 50%; vertical-align: middle; padding-right: 10px;">
                            @if($toBase64('images/Liberación Calidad/Figura 4.png'))
                                <img src="{{ $toBase64('images/Liberación Calidad/Figura 4.png') }}"
                                    style="width: 100%; max-width: 320px;">
                            @endif
                        </td>
                        <td style="width: 50%; vertical-align: middle; padding-left: 10px;">
                            @if($toBase64('images/Liberación Calidad/Figura 5.png'))
                                <img src="{{ $toBase64('images/Liberación Calidad/Figura 5.png') }}"
                                    style="width: 100%; max-width: 300px;">
                            @endif
                        </td>
                    </tr>
                </table>

                {{-- FIRMAS DENTRO DE LA COLUMNA DERECHA --}}
                <table class="footer-table">
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
                        <td>
                            <div class="signature-line"></div>
                            <div class="signature-name">Gerente de Planta</div>
                            <div class="signature-role">Nombre y Firma</div>
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>

</body>

</html>
