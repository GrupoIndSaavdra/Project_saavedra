<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Logs de Sistema</title>
</head>

<body>
    <style>
        * {
            font-family: Arial, Helvetica, sans-serif;
        }

        .title {
            font-size: .7em;
            text-align: center;
            font-weight: bold;
        }

        h2 {
            font-size: 1rem;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #032c00cc;
            background: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0);
            font-size: 10px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
        }

        th {
            background-color: #033966;
            color: #fff;
        }

        .principalData {
            width: 100%;
            margin-bottom: 2em;
        }

        .colors,
        .filters {
            display: inline-block;
            vertical-align: top;
            width: 49%;
        }

        .table-filters,
        .table-colors {
            width: 100%;
        }

        .table-logs {
            margin: 1em 0;
            width: 100%;
        }

        .level-header {
            background-color: #033966;
            color: #fff;
            font-weight: bold;
            text-align: left;
            padding-left: 10px;
            font-size: 9px;
        }
    </style>

    <div class="title">
        <h1>Auditoría y Logs del Sistema</h1>
        <p>Fecha de generación: {{ date('d-m-Y H:i:s') }}</p>
    </div>

    <div class="principalData">
        <div class="colors">
            <h2>Tabla de colores (Acciones)</h2>
            <table class="table-colors">
                <thead>
                    <tr>
                        <th>Acción</th>
                        <th>Nivel / Estado</th>
                    </tr>
                </thead>
                <tbody>
                        <?php
                // Detectar qué familias están presentes en los resultados actuales
                $activeFamilies = [
                    'azul' => false,
                    'verde' => false,
                    'amarillo' => false,
                    'morado' => false,
                    'rojo' => false
                ];

                foreach ($logsRender as $log) {
                    $action = $log['action'];
                    $isSuspicious = $log['is_suspicious'] ?? false;

                    // Azul
                    if (in_array($action, ['Inicio de Sesión', 'Nuevo reporte', 'Inicio de Reporte', 'Cierre de Sesión', 'Login Inspector Calidad', 'Carga de Formulario de Producción', 'Selección de Pieza', 'Selección de OT', 'Selección de Clase', 'Selección de Proceso', 'Nueva Meta Creada', 'Ingreso a Meta Existente'])) {
                        $activeFamilies['azul'] = true;
                    }
                    // Verde
                    if (in_array($action, ['Proceso Correcto', 'Captura Medida', 'Liberación por Calidad', 'Terminar Reporte']) && !$isSuspicious) {
                        $activeFamilies['verde'] = true;
                    }
                    // Morado
                    if (in_array($action, ['Consulta Dibujos Técnicos', 'Solicitud Edición de Piezas', 'Intento de Liberación'])) {
                        $activeFamilies['morado'] = true;
                    }
                    // Amarillo (Ayudas / Manuales)
                    if (in_array($action, ['Consulta Documentación Técnica', 'Captura Sospechosa', 'Solicitud Edición de Reporte']) || ($action === 'Captura Medida' && $isSuspicious)) {
                        $activeFamilies['amarillo'] = true;
                    }
                    // Rojo
                    if (in_array($action, ['Exceso de Tiempo', 'Mensaje de Error', 'Rechazo por Calidad', 'Alerta de Error en Sistema', 'Avisos de Sistema', 'Intento de Login Fallido'])) {
                        $activeFamilies['rojo'] = true;
                    }
                }
                            ?>

                    @if ($activeFamilies['azul'])
                        <tr style="background-color: #21618C; color: white; font-size: 10px;"><td colspan="2" style="text-align: center; padding: 2px;"><b>Logs Azules (Flujo / Info)</b></td></tr>
                        <tr style="background-color: #D6EAF8; font-size: 9px;"><td style="padding: 1px;">Claro</td><td style="padding: 1px;">Navegación / Cargas</td></tr>
                        <tr style="background-color: #3498DB; color: white; font-size: 9px;"><td style="padding: 1px;">Normal</td><td style="padding: 1px;">Inicios Sesión/Reporte</td></tr>
                        <tr style="background-color: #21618C; color: white; font-size: 9px;"><td style="padding: 1px;">Oscuro</td><td style="padding: 1px;">Login Inspector / Cierre</td></tr>
                    @endif
                    
                    @if ($activeFamilies['verde'])
                        <tr style="background-color: #186A3B; color: white; font-size: 10px;"><td colspan="2" style="text-align: center; padding: 2px;"><b>Logs Verdes (Éxito / Producción)</b></td></tr>
                        <tr style="background-color: #D5F5E3; font-size: 9px;"><td style="padding: 1px;">Claro</td><td style="padding: 1px;">Proceso Correcto</td></tr>
                        <tr style="background-color: #27AE60; color: white; font-size: 9px;"><td style="padding: 1px;">Normal</td><td style="padding: 1px;">Captura Medida</td></tr>
                        <tr style="background-color: #186A3B; color: white; font-size: 9px;"><td style="padding: 1px;">Oscuro</td><td style="padding: 1px;">Liberación / Término</td></tr>
                    @endif
                    
                    @if ($activeFamilies['amarillo'])
                        <tr style="background-color: #9A7D0A; color: white; font-size: 10px;"><td colspan="2" style="text-align: center; padding: 2px;"><b>Logs Amarillos (Auditoría / Avisos)</b></td></tr>
                        <tr style="background-color: #FCF3CF; font-size: 9px;"><td style="padding: 1px;">Claro</td><td style="padding: 1px;">Manuales / Ayudas Visuales</td></tr>
                        <tr style="background-color: #F1C40F; font-size: 9px;"><td style="padding: 1px;">Normal</td><td style="padding: 1px;">Captura Sospechosa</td></tr>
                        <tr style="background-color: #9A7D0A; color: white; font-size: 9px;"><td style="padding: 1px;">Oscuro</td><td style="padding: 1px;">Solicitud Edición</td></tr>
                    @endif
                    
                    @if ($activeFamilies['morado'])
                        <tr style="background-color: #512E5F; color: white; font-size: 10px;"><td colspan="2" style="text-align: center; padding: 2px;"><b>Logs Morados (Poder / Admin)</b></td></tr>
                        <tr style="background-color: #EBDEF0; font-size: 9px;"><td style="padding: 1px;">Claro</td><td style="padding: 1px;">Dibujos Técnicos (Planos)</td></tr>
                        <tr style="background-color: #8E44AD; color: white; font-size: 9px;"><td style="padding: 1px;">Normal</td><td style="padding: 1px;">Edición de Piezas</td></tr>
                        <tr style="background-color: #512E5F; color: white; font-size: 9px;"><td style="padding: 1px;">Oscuro</td><td style="padding: 1px;">Intento Liberación</td></tr>
                    @endif
                    
                    @if ($activeFamilies['rojo'])
                        <tr style="background-color: #943126; color: white; font-size: 10px;"><td colspan="2" style="text-align: center; padding: 2px;"><b>Logs Rojos (Fallas / Críticos)</b></td></tr>
                        <tr style="background-color: #FADBD8; font-size: 9px;"><td style="padding: 1px;">Claro</td><td style="padding: 1px;">Exceso de Tiempo / Avisos de Sistema</td></tr>
                        <tr style="background-color: #E74C3C; color: white; font-size: 9px;"><td style="padding: 1px;">Normal</td><td style="padding: 1px;">Errores Sistema</td></tr>
                        <tr style="background-color: #943126; color: white; font-size: 9px;"><td style="padding: 1px;">Oscuro</td><td style="padding: 1px;">Rechazo Calidad</td></tr>
                    @endif
                </tbody>
            </table>
        </div>

        <div class="filters">
            <h2>Filtros aplicados</h2>
            <?php $titles = [
    'ot' => 'OT',
    'clase' => 'Clase',
    'operador' => 'Operador',
    'maquina' => 'Máquina',
    'proceso' => 'Proceso',
    'audit_status' => 'Estado',
    'action' => 'Acción',
    'dateFrom' => 'Desde',
    'dateTo' => 'Hasta',
    'n_pieza' => 'N# Pieza',
];
            ?>
            <table class="table-filters">
                <thead>
                    <tr>
                        <th>Filtro</th>
                        <th>Valor</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($titles as $key => $title)
                        <tr>
                            <td>{{ $title }}</td>
                            <td>
                                {{ (isset($selectedItems[$key]) && $selectedItems[$key] !== '' && $selectedItems[$key] !== 'Todos') ? $selectedItems[$key] : 'Todos' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Fecha/Hora</th>
                <th>Operador</th>
                <th>Acción</th>
                <th>Descripción</th>
                <th>OT</th>
                <th>N_J</th>
                <th>Ini. Maq.</th>
                <th>Ter. Maq.</th>
                <th>T. Total</th>
                <th>Clase</th>
                <th>Proceso</th>
                <th>Máquina</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($logsRender as $log)
                        <?php
                $style = '';
                    if ($log['action'] === 'Captura Medida' && $log['is_suspicious']) {
                        $style = 'background-color: #F1C40F; color: #fff;';
                    } else {
                        switch ($log['action']) {
                             case 'Carga de Formulario de Producción':
                             case 'Selección de Pieza':
                             case 'Selección de OT':
                             case 'Selección de Clase':
                             case 'Selección de Proceso':
                                 $style = 'background-color: #D6EAF8; color: #000;';
                                 break;
                                 
                             case 'Inicio de Sesión':
                             case 'Nuevo reporte':
                             case 'Inicio de Reporte':
                             case 'Nueva Meta Creada':
                             case 'Ingreso a Meta Existente':
                                 $style = 'background-color: #3498DB; color: #fff;';
                                 break;
                                 
                             case 'Cierre de Sesión':
                             case 'Login Inspector Calidad':
                                 $style = 'background-color: #21618C; color: #fff;';
                                 break;
                                 
                             case 'Proceso Correcto':
                                 $style = 'background-color: #D5F5E3; color: #000;';
                                 break;
                                 
                             case 'Captura Medida':
                             case 'Captura Medida / Reporte':
                                 $style = 'background-color: #27AE60; color: #fff;';
                                 break;
                                 
                             case 'Liberación por Calidad':
                             case 'Terminar Reporte':
                                 $style = 'background-color: #186A3B; color: #fff;';
                                 break;
                                 
                             case 'Consulta Documentación Técnica':
                                 $style = 'background-color: #FCF3CF; color: #000;';
                                 break;
                                 
                             case 'Consulta Dibujos Técnicos':
                             case 'Cambio de Catálogo':
                                 $style = 'background-color: #EBDEF0; color: #000;';
                                 break;

                             case 'Captura Sospechosa':
                                 $style = 'background-color: #F1C40F; color: #fff;';
                                 break;
                                 
                             case 'Solicitud Edición de Reporte':
                                 $style = 'background-color: #9A7D0A; color: #fff;';
                                 break;
                                 
                             case 'Solicitud Edición de Piezas':
                                 $style = 'background-color: #8E44AD; color: #fff;';
                                 break;
                                 
                             case 'Intento de Liberación':
                                 $style = 'background-color: #512E5F; color: #fff;';
                                 break;

                             case 'Exceso de Tiempo':
                             case 'Avisos de Sistema':
                             case 'Intento de Login Fallido':
                                 $style = 'background-color: #FADBD8; color: #000;';
                                 break;
                                 
                             case 'Mensaje de Error':
                             case 'Alerta de Error en Sistema':
                                 $style = 'background-color: #E74C3C; color: #fff;';
                                 break;
                                 
                             case 'Rechazo por Calidad':
                                 $style = 'background-color: #943126; color: #fff;';
                                 break;
                         }
                    }

                    // Forzar Rojo Claro si hay exceso de tiempo (> 2 horas)
                    if ($log['tiempo_total'] !== 'N/A' && $log['tiempo_total'] !== '00:00:00') {
                        $tParts = explode(':', $log['tiempo_total']);
                        if (isset($tParts[0]) && (int)$tParts[0] >= 2) {
                            $style = 'background-color: #FADBD8; color: #000;';
                        }
                    }

                    // El rechazo tiene prioridad sobre el retraso
                    if ($log['action'] === 'Rechazo por Calidad') {
                        $style = 'background-color: #943126; color: #fff;';
                    }
                            ?>
                        <tr style="{{ $style }} font-weight: bold;">
                            <td>{{ $log['date'] }} {{ $log['time'] }}</td>
                            <td>{{ $log['operador'] }}</td>
                            <td>{{ $log['action'] }}</td>
                            <td style="text-align: left;">{{ $log['details'] }}</td>
                            <td>{{ $log['ot'] }}</td>
                            <td>{{ $log['n_juego'] }}</td>
                            <td>{{ $log['hora_inicio'] }}</td>
                            <td>{{ $log['hora_termino'] }}</td>
                            <td>{{ $log['tiempo_total'] }}</td>
                            <td>{{ $log['clase'] }}</td>
                            <td>{{ $log['proceso'] }}</td>
                            <td>{{ $log['maquina'] }}</td>
                        </tr>
            @endforeach
        </tbody>
    </table>

    <div style="text-align: right; font-size: 12px; font-weight: bold; margin-top: 10px;">
        Registros encontrados: {{ count($logsRender) }}
    </div>
</body>

</html>
