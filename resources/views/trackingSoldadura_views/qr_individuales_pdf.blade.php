<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>QRs Individuales - {{ $lote->id_unico }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&display=swap');
        body { 
            font-family: 'Orbitron', monospace; 
            margin: 5px; 
            padding: 5px;
            font-size: 8px;
        }
        .header { 
            text-align: center; 
            margin-bottom: 10px; 
        }
        .company-name {
            font-size: 12px;
            font-weight: 900;
            color: #033966;
            margin-bottom: 3px;
        }
        .qr-title {
            font-size: 10px;
            font-weight: 700;
            color: #000;
            margin-bottom: 10px;
        }
        .qr-grid { 
            display: flex; 
            flex-wrap: wrap; 
            justify-content: space-around; 
        }
        .qr-item { 
            width: 45%; 
            margin: 5px 0; 
            border: 1px solid #333; 
            padding: 8px; 
            text-align: center; 
            page-break-inside: avoid;
            box-sizing: border-box;
        }
        .qr-item h4 {
            font-size: 9px;
            font-weight: 700;
            margin: 0 0 5px 0;
            color: #033966;
        }
        .qr-info { 
            font-size: 9px; 
            text-align: left; 
            margin-top: 5px;
        }
        .qr-info table { 
            width: 100%; 
            border-collapse: collapse;
        }
        .qr-info td { 
            padding: 1px; 
            border: none;
            font-weight: 400;
        }
        .qr-info td:first-child {
            font-weight: 700;
            width: 40%;
        }
        .page-break { 
            page-break-before: always; 
        }
        .footer {
            margin-top: 10px;
            font-size: 6px;
            color: #666;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">GRUPO INDUSTRIAL SAAVEDRA</div>
        <div class="qr-title">QR - BOTES INDIVIDUALES (5KG)</div>
        <p style="font-size: 8px; margin: 5px 0;">Lote: {{ $lote->id_unico }} | {{ $lote->nombre }} - {{ $lote->lote }} | Total: {{ count($qrCodes) }} botes</p>
    </div>

    <div class="qr-grid">
        @foreach($qrCodes as $index => $item)
            @if($index > 0 && $index % 4 == 0)
                <div class="page-break"></div>
            @endif
            
            <div class="qr-item">
                <h4>GIS - BOTE #{{ $item['bote']->numero_bote }}</h4>
                <div style="text-align: center; margin: 5px 0;">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&format=svg&data={{ urlencode($item['qrContent']) }}" 
                         alt="QR Bote {{ $item['bote']->numero_bote }}" style="width: 100px; height: 100px;">
                </div>
                
                <div class="qr-info">
                    <table>
                        <tr><td>ID:</td><td>{{ $item['bote']->id_unico }}</td></tr>
                        <tr><td>Soldadura:</td><td>{{ $item['bote']->nombre }}</td></tr>
                        <tr><td>Lote:</td><td>{{ $item['bote']->lote }}</td></tr>
                        <tr><td>Peso:</td><td>{{ $item['bote']->peso }}kg</td></tr>
                        <tr><td>Estado:</td><td>{{ strtoupper($item['bote']->estado) }}</td></tr>
                    </table>
                </div>
            </div>
        @endforeach
    </div>

    <div class="footer">
        <p>{{ now()->format('d/m/Y H:i:s') }} | Sistema Tracking Soldadura - Saavedra</p>
    </div>
</body>
</html>