<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Orden de Trabajo {{ $workOrder->id }} - Ficha Técnica</title>
    <style>
        @page {
            size: letter landscape;
            margin: 10mm 15mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333333;
            margin: 0;
            padding: 0;
            font-size: 11px;
            line-height: 1.4;
        }
        .header-container {
            border-bottom: 3px solid #033966;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }
        .header-title {
            font-size: 20px;
            color: #033966;
            font-weight: bold;
            margin: 0 0 4px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header-subtitle {
            font-size: 11px;
            color: #666666;
            margin: 0;
        }
        .info-table {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 5px 8px;
            vertical-align: middle;
            border: 1px solid #e2e8f0;
        }
        .info-label {
            font-weight: bold;
            color: #033966;
            width: 15%;
            font-size: 10px;
            text-transform: uppercase;
            background-color: #f8fafd;
        }
        .info-value {
            width: 35%;
            color: #333333;
            font-size: 11px;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.data-table th {
            background-color: #033966;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
            padding: 8px 6px;
            border: 1px solid #033966;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }
        table.data-table td {
            padding: 8px 6px;
            border: 1px solid #e2e8f0;
            font-size: 10px;
            color: #4a5568;
            text-align: center;
        }
        table.data-table tbody tr:nth-child(even) {
            background-color: #f8fafd;
        }
        .class-name {
            font-weight: bold;
            color: #033966;
            text-align: left;
            white-space: nowrap;
        }
        .composition-value {
            font-weight: 600;
            color: #1a202c;
            text-align: left;
            padding-left: 10px;
        }
        .nowrap-cell {
            white-space: nowrap;
        }
        .process-list {
            font-size: 9px;
            color: #4a5568;
            text-align: left;
            line-height: 1.3;
        }
        .no-records {
            padding: 20px;
            color: #718096;
            font-style: italic;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="header-container">
        <h1 class="header-title">Información de la Orden de Trabajo</h1>
        <p class="header-subtitle">Reporte de Ficha Técnica Generado de forma Automática</p>
    </div>

    <table class="info-table">
        <tr>
            <td class="info-label">Orden de Trabajo</td>
            <td class="info-value">#{{ $workOrder->id }}</td>
            <td class="info-label">Moldura</td>
            <td class="info-value" style="font-weight: bold; color: #033966;">{{ $molding->nombre }}</td>
        </tr>
        <tr>
            <td class="info-label">Fecha de Registro</td>
            <td class="info-value" colspan="3">{{ $workOrder->created_at }}</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="text-align: left;">Clase</th>
                <th>Tamaño / Sección</th>
                <th style="text-align: left;">Composición Química</th>
                <th>Piezas</th>
                <th>Pedido</th>
                <th>Fecha Inicio</th>
                <th>Fecha Término</th>
                <th style="text-align: left; width: 35%;">Procesos Autorizados</th>
            </tr>
        </thead>
        @if ($classes != null && count($classes) > 0)    
            <tbody>
                @foreach($classes as $class)
                <tr>
                    <td class="class-name">{{ $class->nombre }}</td>
                    <td>
                        @if($class->nombre == 'Obturador' && $class->seccion != null)
                            Sección {{ $class->seccion }}
                        @else
                            {{ $class->tamanio }}
                        @endif
                    </td>
                    <td class="composition-value">{{ $class->composicion_quimica ? str_replace('/', ' / ', $class->composicion_quimica) : '-' }}</td>
                    <td>{{ $class->piezas }}</td>
                    <td>{{ $class->pedido }}</td>
                    <td class="nowrap-cell">{{ $class->fecha_inicio }}</td>
                    <td class="nowrap-cell">{{ $class->fecha_termino ?? '-' }}</td>
                    <td class="process-list">
                        @if ($processes != null && isset($processes[$class->id]) && $processes[$class->id] != null)
                            {{ rtrim($processes[$class->id], ', ') }}
                        @else
                            <span style="color: #a0aec0; font-style: italic;">Sin procesos establecidos</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        @else
            <tbody>
                <tr>
                    <td colspan="8" class="no-records">No hay clases registradas en esta orden de trabajo.</td>
                </tr>
            </tbody>
        @endif
    </table>
</body>

</html>