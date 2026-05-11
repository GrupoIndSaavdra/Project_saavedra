<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pre-Orden de Modelos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            line-height: 1.6;
        }

        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .message-content {
            font-size: 16px;
            margin-bottom: 40px;
        }

        /* Firma styles */
        .firma {
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.4;
        }

        .name {
            color: #00008b;
            /* Azul fuerte */
            font-weight: bold;
            text-transform: uppercase;
        }

        .title {
            font-weight: bold;
            color: #000;
        }

        .link-text {
            color: #0000a9;
            /* Azul estilo link */
            text-decoration: none;
        }

        .phone {
            color: #000;
            /* Texto normal */
        }

        .mt-2 {
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="message-content">
            <p>Se ha generado una nueva <strong>Pre-Orden de fabricación de modelos</strong> para la OT: <strong>{{ $data['ot'] }}</strong>.</p>
            
            <p><strong>Detalles de la Pre-Orden:</strong></p>
            <ul>
                <li><strong>Folio:</strong> {{ $data['folio'] }}</li>
                <li><strong>Proveedor:</strong> {{ $data['proveedor'] }}</li>
                <li><strong>Fecha:</strong> {{ $data['fecha'] }}</li>
                <li><strong>Moldura:</strong> {{ $data['moldura'] }}</li>
            </ul>

            @if(!empty($data['observaciones']))
                <p><strong>Observaciones:</strong> {{ $data['observaciones'] }}</p>
            @endif

            <p>Favor de verificar el documento adjunto con la lista de clases y especificaciones técnicas para proceder con la fabricación.</p>
        </div>

        <div class="firma">
            <div class="name">{{ $userName ?? 'Departamento de Almacén' }}</div>
            <div class="title mt-2">ALMACÉN TECÁMAC - GRUPO INDUSTRIAL SAAVEDRA</div>

            <div class="mt-2">
                <span class="link-text">PROLONGACION INSURGENTES NO. 5 KM. 39</span><br>
                <span class="link-text">LOTE NO. 4 CARRETERA MEXICO – PACHUCA</span><br>
                <span class="link-text">TECAMAC, ESTADO DE MEXICO.</span><br>
                <span class="link-text">C.P. 55740</span>
            </div>
        </div>
    </div>
</body>

</html>
