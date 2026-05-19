<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page {
            size: A4 landscape;
            margin: 1.5cm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            color: #1a1a1a;
            line-height: 1.2;
        }

        * { box-sizing: border-box; }

        table { border-collapse: collapse; width: 100%; }

        /* ── ENCABEZADO: LOGOS Y TÍTULO ─── */
        .tbl-header { margin-bottom: 5px; }
        .tbl-header td { border: none; padding: 5px; vertical-align: middle; }
        .logo-cell { width: 150px; text-align: center; }
        .logo-cell img { max-height: 50px; max-width: 140px; }
        .title-cell {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            color: #005194;
            text-transform: uppercase;
        }

        /* ── BLOQUE CONTROL DE CALIDAD ─── */
        .tbl-quality { margin-bottom: 10px; }
        .tbl-quality td {
            border: 1px solid #333;
            padding: 3px 4px;
            text-align: center;
            font-size: 8.5px;
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
        .tbl-general { margin-bottom: 8px; }
        .tbl-general td { border: 1px solid #aaa; padding: 5px 10px; }
        .gen-label {
            background-color: #f0f0f0;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
            width: 12%;
        }
        .gen-value { font-weight: bold; font-size: 11px; }
        .gen-value-folio { font-weight: bold; color: #c0392b; font-size: 12px; }

        /* ── TABLA PRINCIPAL ─── */
        .tbl-main th {
            background-color: #005194;
            color: #ffffff;
            padding: 6px 3px;
            font-size: 9px;
            font-weight: bold;
            text-align: center;
            border: 1px solid #005194;
            text-transform: uppercase;
        }
        .tbl-main td {
            border: 1px solid #cccccc;
            padding: 6px 5px;
            font-size: 10px;
            text-align: center;
            vertical-align: middle;
        }
        .desc-cell { text-align: left; padding-left: 10px; font-weight: bold; }
        .code-cell {
            font-family: 'Courier New', Courier, monospace;
            font-weight: bold;
            color: #005194;
            font-size: 9px;
        }
        .row-alt { background-color: #f5f8fd; }
        .date-rowspan {
            font-weight: bold;
            font-size: 11px;
            color: #005194;
            background-color: #eef4fc;
        }

        /* ── OBSERVACIONES ─── */
        .tbl-obs { margin-top: 8px; }
        .tbl-obs td { border: 1px solid #aaa; padding: 5px 8px; font-size: 9.5px; }
        .obs-label {
            background-color: #f0f0f0;
            font-weight: bold;
            font-size: 8.5px;
            text-transform: uppercase;
            width: 12%;
            color: #333;
        }

        /* ── FIRMAS ─── */
        .tbl-firmas { margin-top: 25px; }
        .tbl-firmas td {
            width: 33.33%;
            text-align: center;
            padding: 0 20px;
            border: none;
            vertical-align: bottom;
        }
        .firma-espacio { height: 35px; }
        .firma-linea {
            border-top: 1.5px solid #222;
            width: 80%;
            margin: 0 auto 4px auto;
        }
        .firma-rol {
            font-weight: bold;
            font-size: 9.5px;
            text-transform: uppercase;
            color: #005194;
        }
        .firma-sub { font-size: 8.5px; color: #555; margin-top: 2px; }

        /* ── FOOTER ─── */
        .footer {
            position: fixed;
            bottom: -1cm;
            right: 0;
            width: 100%;
            text-align: right;
            font-size: 7.5px;
            color: #999;
            border-top: 0.5px solid #ddd;
            padding-top: 2px;
        }
    </style>
</head>
<body>

{{-- BLOQUE 1: LOGOS Y TÍTULO --}}
<table class="tbl-header">
    <tr>
        <td class="logo-cell">
            <img src="{{ public_path('images/lg_saavedra2.png') }}" alt="Logo GIS">
        </td>
        <td class="title-cell">
            Pre-Orden y Salida de Modelo
        </td>
        <td class="logo-cell">
            <img src="{{ public_path('images/lg_saavedra.png') }}" alt="GIS">
        </td>
    </tr>
</table>

{{-- BLOQUE 2: CONTROL DE CALIDAD (valores estáticos 4ALM-16) --}}
<table class="tbl-quality">
    <tr>
        <td class="quality-label">Fecha de Elaboración</td>
        <td class="quality-value">23-DIC-25</td>
        <td class="quality-label">Fecha de Revisión</td>
        <td class="quality-value">26-DIC-25</td>
        <td class="quality-label">Fecha de Aprobación</td>
        <td class="quality-value">26-DIC-25</td>
        <td class="quality-label">Código</td>
        <td class="quality-value">4ALM-16</td>
        <td class="quality-label">Nivel de Revisión</td>
        <td class="quality-value">0</td>
    </tr>
</table>

{{-- BLOQUE 3: DATOS GENERALES --}}
<table class="tbl-general">
    <tr>
        <td class="gen-label">Proveedor</td>
        <td class="gen-value" style="width: 25%;">{{ $data['proveedor'] }}</td>
        <td class="gen-label">Fecha</td>
        <td class="gen-value" style="width: 14%;">{{ \Carbon\Carbon::parse($data['fecha_creacion'])->format('d/m/Y') }}</td>
        <td class="gen-label">Folio</td>
        <td class="gen-value-folio" style="width: 14%;">{{ $data['folio'] }}</td>
    </tr>
    <tr>
        <td class="gen-label">Moldura</td>
        <td class="gen-value" colspan="3">{{ $data['moldura'] }}</td>
        <td class="gen-label">Orden de Trabajo</td>
        <td class="gen-value">{{ preg_replace('/[^0-9]/', '', $data['ot']) ?: $data['ot'] }}</td>
    </tr>
</table>

{{-- BLOQUE 4: TABLA PRINCIPAL --}}
<table class="tbl-main">
    <thead>
        <tr>
            <th style="width: 14%;">Tipo de Modelo</th>
            <th style="width: 10%;">Impresiones</th>
            <th style="width: 10%;">Cantidad</th>
            <th style="width: 28%;">Descripción</th>
            <th style="width: 22%;">Código de Modelo</th>
            <th style="width: 16%;">Fecha de Entrega</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data['filas'] as $index => $fila)
            <tr class="{{ $index % 2 == 1 ? 'row-alt' : '' }}">
                <td>{{ $fila['tipo_modelo'] }}</td>
                <td>{{ $fila['impresiones'] }}</td>
                <td>{{ $fila['cantidad'] }}</td>
                {{-- clase_nombre ya trae el prefijo correcto: "Templadera [Clase]" o "Modelo [Clase]" --}}
                <td class="desc-cell">{{ $fila['clase_nombre'] }}</td>
                <td class="code-cell">{{ $fila['codigo_modelo'] }}</td>
                @if($index === 0)
                    <td class="date-rowspan" rowspan="{{ count($data['filas']) }}" style="text-align:center; vertical-align:middle;">
                        {{ !empty($data['fecha_entrega']) ? \Carbon\Carbon::parse($data['fecha_entrega'])->format('d/m/Y') : 'Llenado manual' }}
                    </td>
                @endif
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
            <div class="firma-rol">Elaboró</div>
            <div class="firma-sub">{{ $user->nombre }} {{ $user->a_paterno }} {{ $user->a_materno }}</div>
        </td>
        <td>
            <div class="firma-espacio"></div>
            <div class="firma-linea"></div>
            <div class="firma-rol">Revisó</div>
            <div class="firma-sub">(Nombre y Firma)</div>
        </td>
        <td>
            <div class="firma-espacio"></div>
            <div class="firma-linea"></div>
            <div class="firma-rol">Recibió</div>
            <div class="firma-sub">(Nombre y Firma)</div>
        </td>
    </tr>
</table>

<div class="footer">
    Grupo Industrial Saavedra &nbsp;|&nbsp; Formato 4ALM-16 &nbsp;|&nbsp; Generado el {{ date('d/m/Y H:i') }} &nbsp;|&nbsp; Copia Controlada
</div>

</body>
</html>
