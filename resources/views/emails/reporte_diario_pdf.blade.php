<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte de Producción — {{ $fecha->format('d/m/Y') }}</title>
    <style>
        {!! file_get_contents(resource_path('css/reportes/pdf.css')) !!}
    </style>
</head>

<body>

    <div class="header">
        <p>Grupo Industrial Saavedra</p>
        <h1>Reporte General de Producción</h1>
        <div class="badge">{{ $fecha->translatedFormat('l, d \d\e F \d\e Y') }}</div>
    </div>

    @foreach ($reporte as $otId => $otData)
        <div class="ot-block">
            <div class="ot-header">
                {{ $otData['ot_label'] }}
            </div>

            @foreach ($otData['clases'] as $claseId => $claseData)
                <div class="clase-block">
                    <div class="clase-header">
                        {{ $claseData['clase_label'] }}
                    </div>

                    @foreach ($claseData['procesos'] as $proceso => $operadores)
                        <div class="proceso-block">
                            <div class="proceso-header">
                                PROCESO: {{ $proceso }}
                            </div>

                            @foreach ($operadores as $nombreOperador => $filas)
                                <div class="operador-section">
                                    <div class="operador-header">
                                        Operador: {{ $nombreOperador }} ({{ count($filas) }} registros)
                                    </div>

                                    <table>
                                        <thead>
                                            <tr>
                                                <th style="width: 20%;">N° Pieza</th>
                                                <th style="width: 30%;">Fecha / Hora</th>
                                                <th style="width: 50%;">Observaciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($filas as $fila)
                                                <tr class="{{ $fila['liberado'] ? 'liberado' : '' }}">
                                                    <td><strong>{{ $fila['n_piezas'] }}</strong></td>
                                                    <td>{{ $fila['hora'] }}</td>
                                                    <td>{{ $fila['observacion'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    @endforeach

    <div class="footer">
        Reporte generado automáticamente el {{ now()->format('d/m/Y H:i') }}
    </div>


</body>

</html>