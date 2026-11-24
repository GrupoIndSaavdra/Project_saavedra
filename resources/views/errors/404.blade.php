<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Página No Encontrada | Grupo Industrial Saavedra</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('images/logos/favicon.ico') }}?v=3">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --gis-blue: #033966;
            --gis-green: #0a8504;
            --gis-red: #9c0303;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #021a30 0%, #082d4d 50%, #031525 100%);
            color: #ffffff;
            padding: 20px;
        }
        .error-card {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1.5px solid rgba(255, 255, 255, 0.2);
            border-radius: 24px;
            max-width: 600px;
            width: 100%;
            padding: 40px 32px;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6);
            animation: fadeIn 0.4s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .logo-img {
            max-width: 160px;
            height: auto;
            margin-bottom: 24px;
            filter: drop-shadow(0 4px 10px rgba(0,0,0,0.3));
        }
        .error-code {
            font-size: 4rem;
            font-weight: 800;
            line-height: 1;
            background: linear-gradient(135deg, #60a5fa 0%, #38bdf8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 12px;
        }
        .error-title {
            font-size: 1.4rem;
            font-weight: 600;
            color: #f1f5f9;
            margin-bottom: 12px;
        }
        .error-desc {
            font-size: 0.95rem;
            color: #cbd5e1;
            line-height: 1.6;
            margin-bottom: 32px;
        }
        .actions-group {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            justify-content: center;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 24px;
            border-radius: 12px;
            font-size: 0.95rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
        }
        .btn-primary {
            background-color: var(--gis-green);
            color: #ffffff;
            box-shadow: 0 4px 14px rgba(10, 133, 4, 0.35);
        }
        .btn-primary:hover {
            background-color: #0c9e05;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(10, 133, 4, 0.45);
        }
        .btn-secondary {
            background-color: rgba(255, 255, 255, 0.15);
            color: #f8fafc;
            border: 1px solid rgba(255, 255, 255, 0.25);
        }
        .btn-secondary:hover {
            background-color: rgba(255, 255, 255, 0.25);
            transform: translateY(-2px);
        }
        .footer-note {
            margin-top: 28px;
            font-size: 0.8rem;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <img src="{{ asset('images/logos/Logo_GIS.png') }}" alt="Grupo Industrial Saavedra" class="logo-img" onerror="this.style.display='none'">
        <div class="error-code">404</div>
        <h1 class="error-title">Página No Encontrada</h1>
        <p class="error-desc">
            La ruta o recurso al que intentas acceder no existe o fue reubicado.
        </p>
        <div class="actions-group">
            <button onclick="window.history.back()" class="btn btn-secondary">Regresar</button>
            <a href="{{ url('/') }}" class="btn btn-primary">Ir al Menú Principal</a>
        </div>
        <p class="footer-note">Project Saavedra &bull; Sistema de Control de Producción y Calidad</p>
    </div>
</body>
</html>

