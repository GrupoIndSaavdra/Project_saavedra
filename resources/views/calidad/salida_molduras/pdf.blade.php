<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Salida de Molduras OT {{ $reporte->ot_id }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th, td {
            border: 1px solid #777;
            padding: 6px;
            text-align: center;
        }
        th {
            background-color: #033966;
            color: #fff;
            font-weight: bold;
        }
        .header-table td {
            border: none;
        }
        .info-table td {
            border: 1px solid #777;
            background-color: #f9f9f9;
        }
        .info-table th {
            background-color: #e0e0e0;
            color: #333;
            white-space: nowrap;
        }
        .page-break {
            page-break-after: always;
        }
        .checked {
            background-color: #e6f4ea;
            color: #16a34a;
            font-weight: bold;
        }
        .unchecked {
            background-color: #fdeaea;
            color: #d33;
        }
        .section-title {
            background-color: #033966;
            color: #fff;
            padding: 8px;
            font-weight: bold;
            margin-bottom: 5px;
            text-align: center;
            border-radius: 4px;
        }
    </style>
</head>
<body>

    <!-- Encabezado -->
    <table class="header-table">
        <tr>
            <td width="20%">
                <img src="{{ public_path('images/lg_saavedra.png') }}" width="120" height="auto" alt="Logo GIS">
            </td>
            <td width="55%" align="center">
                <h2 style="margin:0; font-size:16px;">Registro de Números de Trazabilidad por Moldura</h2>
            </td>
            <td width="25%" align="right">
                <table style="border-collapse: collapse; width: 100%; font-size: 10px;" border="1">
                    <tr><td style="background-color: #e0e0e0; font-weight: bold;">Código:</td><td>F PRO CPT</td></tr>
                    <tr><td style="background-color: #e0e0e0; font-weight: bold;">Versión:</td><td>6</td></tr>
                    <tr><td style="background-color: #e0e0e0; font-weight: bold;">Fecha Rev:</td><td>22/04/2026</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Info General -->
    <table class="info-table">
        <tr>
            <th>Fecha Inicio:</th>
            <td>{{ $reporte->fecha_inicio ? \Carbon\Carbon::parse($reporte->fecha_inicio)->format('d/m/Y') : now()->format('d/m/Y') }}</td>
            <th>Nombre de moldura:</th>
            <td>{{ $reporte->nombre_moldura }}</td>
            <th>OT:</th>
            <td>{{ $reporte->ot_id }}</td>
            <th>Clase:</th>
            <td>{{ $reporte->clase }}</td>
        </tr>
        <tr>
            <th>Cant. Pedido:</th>
            <td>{{ $reporte->cantidad_pedido }}</td>
            <th>Consignación:</th>
            <td>{{ $reporte->cantidad_consignacion }}</td>
            <th>Cliente:</th>
            <td>{{ $reporte->cliente ?? '—' }}</td>
            <th>Inspector:</th>
            <td>{{ $inspector->nombre ?? 'N/A' }}</td>
        </tr>
    </table>

    <div class="section-title">Registro de Piezas — Formato {{ ucfirst($reporte->formato) }}</div>
    
    @php 
        $itemsPerRow = ($reporte->formato === 'bombillos') ? 2 : 3; 
    @endphp
    <!-- Grid de Piezas -->
    <table>
        <thead>
            <tr>
                @for($i = 0; $i < $itemsPerRow; $i++)
                    <th width="5%">N°</th>
                    <th width="5%">✔</th>
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
                            if($numPieza > $totalPiezas) {
                                $cols = $reporte->formato === 'bombillos' ? 5 : 3;
                                for($blank=0; $blank<$cols; $blank++) { echo "<td></td>"; }
                                continue;
                            }
                            
                            $celda = $celdas[$numPieza] ?? null;
                            $isChecked = ($celda?->valor_simple === '1');
                            $desc = $celda?->descripcion ?? '';
                            $val90 = $celda?->valor_90 ?? '';
                            $valLp = $celda?->valor_lp ?? '';
                        @endphp
                        
                        <td class="{{ $isChecked ? 'checked' : 'unchecked' }}">{{ $numPieza }}</td>
                        <td class="{{ $isChecked ? 'checked' : 'unchecked' }}">{{ $isChecked ? '✔' : '✘' }}</td>
                        
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

    <div style="page-break-inside: avoid; margin-top: 15px;">
        <div class="section-title">Observaciones</div>
        <div style="border: 1px solid #777; padding: 15px; min-height: 70px; background-color: #fdfdfd;">
            {{ $reporte->observaciones ?? 'Sin observaciones.' }}
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
