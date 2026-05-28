<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>SCAR - {{ $scar->no_scar ?? 'N/A' }} - {{ $scar->ot }}</title>
    <style>
        @page {
            margin: 12px 12px;
            size: letter portrait;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            line-height: 1.25;
            color: #000;
            margin: 0;
            padding: 0;
        }

        /* ── ESTRUCTURA TABULAR UNIFICADA ── */
        table.outer {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid #000;
            margin-top: -1.5px; /* colapsa bordes adyacentes */
        }
        
        table.outer:first-of-type {
            margin-top: 0;
        }

        table.outer td, table.outer th {
            border: 1px solid #000;
            padding: 4px 6px;
            vertical-align: middle;
        }

        /* Encabezado */
        .title-cell {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            color: #000;
            padding: 8px 4px;
        }

        .meta-header-bg {
            background-color: #7898ba;
            color: #fff;
            text-align: center;
            font-size: 9px;
            font-weight: bold;
            padding: 3px 2px;
        }

        .meta-value-cell {
            text-align: center;
            font-size: 9.5px;
            padding: 3px 2px;
        }

        /* Encabezados y Valores de Secciones */
        .field-header {
            background-color: #1e467a;
            color: #ffffff;
            font-weight: bold;
            font-size: 10px;
            padding: 3px 5px;
        }

        .field-value {
            font-size: 10.5px;
            padding: 4px 6px;
            min-height: 20px;
        }

        .tall-value {
            vertical-align: top;
            padding: 4px 6px;
        }

        /* Checkboxes Personalizados */
        .checkbox-container {
            display: inline-block;
            width: 10.5px;
            height: 10.5px;
            border: 1px solid #000;
            background-color: #fff;
            margin-right: 4px;
            vertical-align: middle;
            text-align: center;
            line-height: 8.5px;
            font-size: 9.5px;
            font-weight: bold;
        }

        .checkbox-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .checkbox-table td {
            border: none !important;
            padding: 2px 4px;
            font-size: 10px;
        }

        /* Firmas */
        .sig-line {
            border-top: 1px solid #000;
            margin-top: 24px;
            margin-bottom: 2px;
            width: 85%;
            margin-left: auto;
            margin-right: auto;
        }
    </style>
</head>
<body>

@php
    /* ── Helpers de datos ── */
    $otStr = preg_replace('/_\d{8}_\d{6}.*$/', '', $scar->ot ?? '');
    $otNum = '';
    $molduraName = '';
    if (preg_match('/^OT\s*(\d+)\s*-\s*(.*)$/i', $otStr, $m)) {
        $otNum = $m[1];
        $molduraName = trim($m[2]);
    } elseif (preg_match('/^OT\s*(\d+)$/i', $otStr, $m)) {
        $otNum = $m[1];
    } else {
        $otNum = $otStr;
    }

    $logoPath = public_path('images/lg_saavedra2.png'); // Logo Maquinados y Fusiones (izq)
    $logoG    = public_path('images/lg_saavedra.png');  // Logo Industrial Saavedra (der)

    $fechaEmision = '';
    $fechaCompromisoVal = '';
    $fechaCompromisoAccionesVal = '';
    $sigDateSuffix = '';

    // Si el SCAR está registrado
    if ($scar) {
        // La fecha de emisión y fecha compromiso de correctivas vienen del campo fecha_emision del formulario
        if (!empty($scar->fecha_emision)) {
            $fechaEmisionDate = \Carbon\Carbon::parse($scar->fecha_emision);
            $fechaEmision = $fechaEmisionDate->format('d/m/Y');
            
            $fechaCompromisoAccionesVal = $fechaEmision;
            $sigDateSuffix = ' — ' . $fechaEmision;
        }
        
        // La fecha compromiso de devolución viene de la fecha de compromiso de la alerta
        if (!empty($scar->fecha_compromiso)) {
            $fechaCompromisoVal = \Carbon\Carbon::parse($scar->fecha_compromiso)->format('d/m/Y');
        }
    }
@endphp

{{-- ════════════════════════════════════════════════════════
     ENCABEZADO PRINCIPAL (Logo | Formato | Logo G)
     ════════════════════════════════════════════════════════ --}}
<table class="outer">
    <tr>
        <!-- Col 0: Logo Maquinados -->
        <td style="width: 16%; text-align: center; padding: 4px;" rowspan="3">
            @if(file_exists($logoPath))
                <img src="{{ $logoPath }}" style="max-height: 48px; max-width: 90px; object-fit: contain;" alt="Logo Maquinados">
            @endif
        </td>
        <!-- Cols 1-6: Title Formato -->
        <td class="title-cell" colspan="6" style="width: 66%;">
            FORMATO SCAR DE MODELOS
        </td>
        <!-- Col 7: Logo Industrial Saavedra -->
        <td style="width: 18%; text-align: center; padding: 4px;" rowspan="3">
            @if(file_exists($logoG))
                <img src="{{ $logoG }}" style="max-height: 48px; max-width: 90px; object-fit: contain;" alt="Industrial Saavedra">
            @else
                <span style="font-weight:bold;font-size:9px;color:#1e467a;">INDUSTRIAL<br>SAAVEDRA</span>
            @endif
        </td>
    </tr>
    <tr>
        <td class="meta-header-bg" style="width: 11%;">Fecha de Elaboración</td>
        <td class="meta-header-bg" style="width: 11%;">Fecha de Revisión</td>
        <td class="meta-header-bg" style="width: 11%;">Fecha de Aprobación</td>
        <td class="meta-header-bg" style="width: 11%;">Nivel de Revisión</td>
        <td class="meta-header-bg" style="width: 11%;">Código</td>
        <td class="meta-header-bg" style="width: 11%;">Página</td>
    </tr>
    <tr>
        <td class="meta-value-cell">27/04/2026</td>
        <td class="meta-value-cell">25/05/2026</td>
        <td class="meta-value-cell"></td>
        <td class="meta-value-cell">1</td>
        <td class="meta-value-cell">F-SDM</td>
        <td class="meta-value-cell" style="font-weight: bold;">1 de 1</td>
    </tr>
</table>

{{-- ════════════════════════════════════════════════════════
     DATOS GENERALES (No. SCAR, Emisión, Proveedor)
     ════════════════════════════════════════════════════════ --}}
<table class="outer">
    <tr>
        <td class="field-header" style="width: 25%;">No. SCAR</td>
        <td class="field-header" style="width: 25%;">Fecha de emisión</td>
        <td class="field-header" style="width: 50%;">Proveedor</td>
    </tr>
    <tr>
        <td class="field-value" style="font-weight: bold;">{{ $scar->no_scar ?? 'N/A' }}</td>
        <td class="field-value">{{ $fechaEmision }}</td>
        <td class="field-value">{{ $scar->proveedor ?? 'SS Metal Foundry, S. de R.L. de C.V.' }}</td>
    </tr>
</table>

{{-- ════════════════════════════════════════════════════════
     DATOS DE CONTACTO (Cliente, Área, Solicitante)
     ════════════════════════════════════════════════════════ --}}
<table class="outer">
    <tr>
        <td class="field-header" style="width: 50%;">Cliente/Empresa</td>
        <td class="field-header" style="width: 25%;">Area solicitante</td>
        <td class="field-header" style="width: 25%;">Nombre del Solicitante</td>
    </tr>
    <tr>
        <td class="field-value">{{ $scar->cliente_empresa ?? 'Industrial Saavedra' }}</td>
        <td class="field-value">{{ $scar->area_solicitante ?? 'Calidad' }}</td>
        <td class="field-value">{{ $scar->nombre_solicitante ?? ($scar->inspector ?? ($scar->user_nombre_calidad ?? 'N/A')) }}</td>
    </tr>
</table>

{{-- ════════════════════════════════════════════════════════
     DATOS DE REFERENCIA (OT, Moldura, Código Modelo)
     ════════════════════════════════════════════════════════ --}}
<table class="outer">
    <tr>
        <td class="field-header" style="width: 25%;">OT</td>
        <td class="field-header" style="width: 50%;">Nombre de la moldura</td>
        <td class="field-header" style="width: 25%;">Código del modelo</td>
    </tr>
    <tr>
        <td class="field-value" style="font-weight: bold;">{{ $otNum }}</td>
        <td class="field-value">{{ $scar->nombre_moldura ?? ($molduraName ?: 'N/A') }}</td>
        <td class="field-value" style="font-weight: bold;">{{ $scar->codigo_modelo ?? 'N/A' }}</td>
    </tr>
</table>

{{-- ════════════════════════════════════════════════════════
     DESCRIPCIÓN DE LA NO CONFORMIDAD
     ════════════════════════════════════════════════════════ --}}
<table class="outer">
    <tr>
        <td class="field-header">Descripción de la No Conformidad</td>
    </tr>
    <tr>
        <td class="field-value tall-value" style="height: 90px;">
            {!! nl2br(e($scar->descripcion_no_conformidad ?? '')) !!}
        </td>
    </tr>
</table>

{{-- ════════════════════════════════════════════════════════
     EVIDENCIA ADJUNTA
     ════════════════════════════════════════════════════════ --}}
<table class="outer">
    <tr>
        <td class="field-header">Evidencia adjunta</td>
    </tr>
    <tr>
        <td class="field-value" style="padding: 5px 10px;">
            <table class="checkbox-table">
                <tr>
                    <td style="width: 50%;">
                        <span class="checkbox-container">{{ ($scar->evidencia_reporte ?? true) ? 'X' : '' }}</span> Reporte dimensional de Calidad
                    </td>
                    <td style="width: 50%;">
                        <span class="checkbox-container">{{ ($scar->evidencia_dibujos ?? false) ? 'X' : '' }}</span> Dibujos autorizados
                    </td>
                </tr>
                <tr>
                    <td>
                        <span class="checkbox-container">{{ ($scar->evidencia_fotos ?? false) ? 'X' : '' }}</span> Fotografías
                    </td>
                    <td>
                        <span class="checkbox-container">{{ ($scar->evidencia_ayudas ?? false) ? 'X' : '' }}</span> Ayudas visuales
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <span class="checkbox-container">{{ ($scar->evidencia_otro ?? false) ? 'X' : '' }}</span> Otro
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

{{-- ════════════════════════════════════════════════════════
     ACCIÓN CORRECTIVA INMEDIATA + FECHA COMPROMISO
     ════════════════════════════════════════════════════════ --}}
<table class="outer">
    <tr>
        <td class="field-header" style="width: 65%;">Acción correctiva inmediata requerida</td>
        <td class="field-header" style="width: 35%;">Fecha compromiso de devolución</td>
    </tr>
    <tr>
        <td class="field-value tall-value" style="height: 65px;">
            <table class="checkbox-table">
                <tr>
                    <td>
                        <span class="checkbox-container">{{ ($scar->accion_regreso ?? false) ? 'X' : '' }}</span> Regreso del modelo al proveedor para su corrección
                    </td>
                </tr>
                <tr>
                    <td>
                        <span class="checkbox-container">{{ ($scar->accion_fabricacion ?? false) ? 'X' : '' }}</span> Fabricación de un modelo nuevo
                    </td>
                </tr>
                <tr>
                    <td>
                        <span class="checkbox-container">{{ ($scar->accion_otro ?? false) ? 'X' : '' }}</span> Otro
                        @if(!empty($scar->accion_otro_texto))
                            : <span style="font-style: italic; font-weight: bold;">{{ $scar->accion_otro_texto }}</span>
                        @endif
                    </td>
                </tr>
            </table>
        </td>
        <td class="field-value" style="height: 65px; text-align: center; vertical-align: middle; font-size: 12px; font-weight: bold;">
            {{ $fechaCompromisoVal }}
        </td>
    </tr>
</table>

{{-- ════════════════════════════════════════════════════════
     CAUSA RAÍZ DEL DEFECTO
     ════════════════════════════════════════════════════════ --}}
<table class="outer">
    <tr>
        <td class="field-header">Causa raíz del defecto (Proveedor)</td>
    </tr>
    <tr>
        <td class="field-value tall-value" style="height: 80px;">
            {!! nl2br(e($scar->causa_raiz ?? '')) !!}
        </td>
    </tr>
</table>

{{-- ════════════════════════════════════════════════════════
     ACCIONES CORRECTIVAS A FUTURO + FIRMA + FECHA COMPROMISO
     ════════════════════════════════════════════════════════ --}}
<table class="outer">
    <tr>
        <td class="field-header" style="width: 58%;">Acciones correctivas a futuro</td>
        <td class="field-header" style="width: 21%;">Firma responsable</td>
        <td class="field-header" style="width: 21%;">Fecha compromiso de correctivas</td>
    </tr>
    <tr>
        <td class="field-value tall-value" style="height: 80px;">
            {!! nl2br(e($scar->acciones_correctivas ?? '')) !!}
        </td>
        <td class="field-value" style="height: 80px; vertical-align: bottom; padding: 2px;"></td>
        <td class="field-value" style="height: 80px; vertical-align: middle; text-align: center; font-size: 12px; font-weight: bold;">
            {{ $fechaCompromisoAccionesVal }}
        </td>
    </tr>
</table>

{{-- ════════════════════════════════════════════════════════
     FIRMAS FINALES
     ════════════════════════════════════════════════════════ --}}
<table class="outer">
    <tr>
        <td class="field-header" style="width: 33%;">Firma del solicitante</td>
        <td class="field-header" style="width: 34%;">Firma del proveedor</td>
        <td class="field-header" style="width: 33%;">Firma de Gerencia/Dirección</td>
    </tr>
    <tr>
        <td class="field-value" style="height: 75px;"></td>
        <td class="field-value" style="height: 75px;"></td>
        <td class="field-value" style="height: 75px;"></td>
    </tr>
</table>

</body>
</html>
