<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ url('images/lg_saavedra.png') }}?v=1">
    <title>Alerta: Dibujo de Fundición</title>
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

        .header {
            text-align: left;
            margin-bottom: 20px;
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
        }

        .title {
            font-weight: bold;
            color: #000;
        }

        .link-text {
            color: #0000a9;
            /* Azul estilo link */
            text-decoration: none;
            /* Se quito el subrayado por solicitud del usuario */
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
            <p>Se suben dibujos de fundicion de la OT: <strong>{{ $otName }}</strong>, verificada y validada. Favor de
                verificar en el Software en el apartado de <strong>Dibujos de Fundicion</strong>.</p>

            @if (!empty($ayudas))
                <p><strong>Ayudas Visuales vinculadas:</strong> {{ implode(', ', $ayudas) }}.</p>
            @endif

            @if ($fileName)
                <p>Archivo subido: {{ $fileName }}</p>
            @endif
        </div>

        <div class="firma">
            <div class="name">DEPARTAMENTO DE PROGRAMACIÓN Y DISEÑO, PLANTA TECÁMAC</div>

            <div class="mt-2">
                <span class="link-text">PROLONGACION INSURGENTES NO. 5 KM. 39</span><br>
                <span class="link-text">LOTE NO. 4 CARRETERA MEXICO – PACHUCA</span><br>
                <span class="link-text">TECAMAC, ESTADO DE MEXICO.</span><br>
                <span class="link-text">C.P. 55740</span>, <span class="phone">TEL. 56 44653134</span>
            </div>
        </div>
    </div>
</body>

</html>
