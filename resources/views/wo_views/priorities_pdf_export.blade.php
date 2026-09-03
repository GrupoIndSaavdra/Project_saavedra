<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 5mm;
        }
        body {
            font-family: 'Calibri', Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 9px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        tr, th, td {
            page-break-inside: avoid;
        }
        th, td {
            border: 1px solid #000;
            padding: 1px;
            text-align: center;
            vertical-align: middle;
            word-wrap: normal;
            word-break: normal;
            overflow-wrap: normal;
        }
        th {
            background-color: #033966;
            color: #ffffff;
            font-size: 7px;
            font-family: '3ds', Arial, sans-serif;
            letter-spacing: -0.1px;
        }
        td {
            font-size: 8px;
            font-family: 'Calibri', Arial, sans-serif;
        }
        .header-table {
            margin-bottom: 10px;
            border: none;
        }
        .header-table td {
            border: none;
            padding: 0;
        }
        tr {
            page-break-inside: avoid;
        }
        .subcell-row {
            padding: 2px;
            border-bottom: 1px solid #999;
        }
        .subcell-row:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    @php
    function safeDateParseDisplay($value) {
        if (empty($value)) return '';
        try {
            return \Carbon\Carbon::parse($value)->format('d/m/Y');
        } catch (\Exception $e) {
            return $value;
        }
    }

    function getPastelColorForWeek($weekString) {
        $premiumPastels = [
            '#d4f0f0', '#ffdfd3', '#e2f0cb', '#f3e5f5', '#ffebd2',
            '#d6e4ff', '#ffe5e5', '#e8f4d9', '#f0e6ff', '#fff2cc',
            '#d9f0f9', '#fbe4e4', '#e0f7fa', '#f5f5dc', '#e6e6fa',
            '#ffefd5', '#e0e8f5', '#f0fff0', '#fff0f5', '#fdf5e6'
        ];

        $weekNum = (int) preg_replace('/[^0-9]/', '', $weekString);
        if ($weekNum === 0) {
            $weekNum = crc32($weekString) % count($premiumPastels);
        }
        
        $index = $weekNum % count($premiumPastels);
        return $premiumPastels[$index];
    }
    @endphp

    <table class="header-table" style="width: 100%; border-collapse: collapse; margin-bottom: 0px;">
        <tr>
            <td style="width: 15%; border: 2px solid #000000; background-color: #ffffff; text-align: center; vertical-align: middle; padding: 4px;">
                <img src="{{ public_path('images/lg_saavedra.png') }}" width="90" height="auto">
            </td>
            <td style="width: 45%; border: 2px solid #000000; background-color: #033966; color: #ffffff; text-align: center; vertical-align: middle; padding: 4px;">
                <h1 style="font-family: 'Algerian', serif; font-size: 20px; letter-spacing: 4px; margin: 0;">PRIORIDADES</h1>
            </td>
            <td style="width: 20%; border: 2px solid #000000; background-color: #0A8504; color: #ffffff; text-align: center; vertical-align: middle; padding: 4px;">
                <h2 style="font-size: 13px; margin: 0; font-family: Arial, sans-serif;">
                    @if(!empty($startWeek) && !empty($endWeek) && $startWeek == $endWeek)
                        SEMANA {{ $startWeek }}
                    @elseif(!empty($startWeek) || !empty($endWeek))
                        SEMANAS {{ $startWeek ?? 1 }}-{{ $endWeek ?? 52 }}
                    @else
                        SEMANA {{ \Carbon\Carbon::now()->isoWeek() }}
                    @endif
                </h2>
                <p style="font-size: 9px; margin: 2px 0 0 0; font-family: Arial, sans-serif;">REPORTE GENERAL</p>
            </td>
            <td style="width: 20%; border: 2px solid #000000; background-color: #404040; color: #ffffff; text-align: center; vertical-align: middle; padding: 4px;">
                <h3 style="font-size: 11px; margin: 0; font-family: Arial, sans-serif;">
                    {{ \Carbon\Carbon::now()->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
                </h3>
            </td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th width="2%" rowspan="2">Nº</th>
                <th width="5%" rowspan="2">ORDEN DE<br>TRABAJO</th>
                <th width="8%" rowspan="2">FECHA Y ORDEN<br>DE COMPRA</th>
                <th width="7%" rowspan="2">CLIENTE</th>
                <th width="14%" rowspan="2">NOMBRE DEL PRODUCTO</th>
                <th width="5.5%" rowspan="2">CANTIDADES</th>
                <th width="8%" rowspan="2">PIEZAS</th>
                <th width="4.5%" rowspan="2">FORMA<br>GRABADOS<br>1,2,3,4</th>
                <th width="6%" rowspan="2">PROVEEDOR<br>DE MATERIAL</th>
                <th width="5.5%" rowspan="2">MATERIAL</th>
                <th width="6%" rowspan="2">FECHA<br>FUNDICION</th>
                <th width="6%" rowspan="2">FECHA<br>TECAMAC</th>
                <th width="4%" rowspan="2">Nº DE<br>SEMANA</th>
                <th width="6%" rowspan="2">FECHA<br>MEXICO</th>
                <th width="6%" rowspan="2" style="background-color: #ffeb3b; color: #000;">FECHA<br>PROMETIDA</th>
                <th width="6.5%" colspan="2">OBSERVACIONES</th>
            </tr>
            <tr>
                <th width="3.25%">PZAS</th>
                <th width="3.25%">CAV</th>
            </tr>
        </thead>
        <tbody>
            @php $rowCount = 1; @endphp
            @foreach($groupedWOs as $semana => $wos)
                @php $rowColor = getPastelColorForWeek($semana); @endphp
                @foreach($wos as $wo)
                    @php
                        $isInactive = ($wo->clases->where('finalizada', 0)->count() === 0);
                        $rowColor = $isInactive ? '#d6d6d6' : getPastelColorForWeek($semana);
                    @endphp
                    <tr style="background-color: {{ $rowColor }};">
                        <td><strong>{{ $rowCount++ }}</strong></td>
                        <td><strong>{{ $wo->id }}</strong></td>
                        <td>
                            @if($wo->fecha_compra)
                                {{ \Carbon\Carbon::parse($wo->fecha_compra)->format('d/m/Y') }}<br>
                            @endif
                            {{ $wo->orden_compra }}
                        </td>
                        <td>{{ $wo->cliente }}</td>
                        <td><strong>{{ $wo->moldura ? $wo->moldura->nombre : $wo->nombre_producto }}</strong></td>
                        <td style="padding: 0;">
                            @if($wo->clases->count() > 0)
                                @foreach($wo->clases as $cl)
                                    <div class="subcell-row">{{ $cl->pedido }}</div>
                                @endforeach
                            @else
                                <div class="subcell-row">{{ $wo->cantidad }}</div>
                            @endif
                        </td>
                        <td style="padding: 0; text-transform: uppercase;">
                            @if($wo->clases->count() > 0)
                                @foreach($wo->clases as $cl)
                                    <div class="subcell-row">{{ $cl->nombre }}</div>
                                @endforeach
                            @else
                                <div class="subcell-row">-</div>
                            @endif
                        </td>
                        <td>{{ $wo->forma_grabados }}</td>
                        <td>{{ $wo->proveedor_material }}</td>
                        <td style="padding: 0;">
                            @if($wo->clases->count() > 0)
                                @foreach($wo->clases as $cl)
                                    <div class="subcell-row">{{ $cl->material ?? '-' }}</div>
                                @endforeach
                            @else
                                <div class="subcell-row">{{ $wo->material }}</div>
                            @endif
                        </td>
                        <td>{{ safeDateParseDisplay($wo->fecha_entrega_fundicion) }}</td>
                        <td>{{ safeDateParseDisplay($wo->entrega_tecamac) }}</td>
                        <td><strong>{{ $wo->semana_entrega_cliente }}</strong></td>
                        <td>{{ safeDateParseDisplay($wo->fecha_real) }}</td>
                        <td><strong>{{ safeDateParseDisplay($wo->fecha_entrega_cliente) }}</strong></td>
                        <td colspan="2">{{ $wo->observaciones_prioridad }}</td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>

    <script type="text/php">
        if (isset($pdf)) {
            $x = $pdf->get_width() - 100;
            $y = $pdf->get_height() - 20;
            $pdf->page_text($x, $y, "Página {PAGE_NUM} de {PAGE_COUNT}", null, 8, array(0,0,0));
        }
    </script>
</body>
</html>
