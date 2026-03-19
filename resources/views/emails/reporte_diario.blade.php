<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Reporte de Producción — {{ $fecha->format('d/m/Y') }}</title>
    {{--
    Los clientes de correo no cargan CSS externos.
    Los estilos se inyectan directamente desde el archivo CSS del proyecto.
    --}}
    <style>
        {!! file_get_contents(resource_path('css/reportes/email.css')) !!}
    </style>
</head>

<body>

    <div class="header">
        <h1>Reporte General de Producción</h1>
        <p>Grupo Industrial Saavedra</p>
        <span class="badge">{{ $fecha->translatedFormat('l, d \d\e F \d\e Y') }}</span>

        <div style="margin-top: 20px;">
            <a href="{{ route('reportes.descargar_pdf', ['fecha' => $fecha->toDateString()]) }}" class="btn-pdf"
                style="background-color: #00b913; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;">
                Descargar PDF
            </a>
        </div>

    </div>


    {{-- CONTENIDO PRINCIPAL --}}
    @if(empty($reporte))
        <div class="sin-datos">
            <p>No se registró producción en este turno.</p>
        </div>
    @else

        {{-- NIVEL 1: OT --}}
        @foreach ($reporte as $otId => $otData)
            <div class="ot-block">

                <div class="ot-header">
                    {{ $otData['ot_label'] }}
                    <small>{{ count($otData['clases']) }} {{ count($otData['clases']) === 1 ? 'clase' : 'clases' }}</small>
                </div>

                {{-- NIVEL 2: Clase --}}
                @foreach ($otData['clases'] as $claseId => $claseData)
                    <div class="clase-block">

                        <div class="clase-header">
                            {{ $claseData['clase_label'] }}
                        </div>

                        {{-- NIVEL 3: Proceso --}}
                        @foreach ($claseData['procesos'] as $proceso => $operadores)
                            <div class="proceso-block">

                                <div class="proceso-header">
                                    {{ $proceso }}
                                </div>

                                {{-- NIVEL 4: Operador --}}
                                @foreach ($operadores as $nombreOperador => $filas)
                                    <div class="operador-section" style="margin-top: 15px;">
                                        <div class="operador-header"
                                            style="background: #f8f9fa; padding: 8px 12px; border-left: 4px solid #007bff; margin-bottom: 5px; font-weight: bold; color: #333;">
                                            Operador: {{ $nombreOperador }}
                                            <span style="float: right; font-weight: normal; font-size: 0.85em; color: #666;">
                                                {{ count($filas) }} {{ count($filas) === 1 ? 'registro' : 'registros' }}
                                            </span>
                                        </div>

                                        <table class="op-table">
                                            <thead>
                                                <tr>
                                                    <th style="width: 15%;">N° Pieza</th>
                                                    <th style="width: 20%;">Fecha / Hora</th>
                                                    <th style="width: 35%;">Obs. Operador</th>
                                                    <th style="width: 30%;">Obs. Calidad</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($filas as $fila)
                                                    {{-- Clase CSS "liberado" activa el fondo azul --}}
                                                    <tr class="{{ $fila['liberado'] ? 'liberado' : '' }}">
                                                        <td><strong>{{ $fila['n_piezas'] }}</strong></td>
                                                        <td>{{ $fila['hora'] }}</td>
                                                        <td>{{ $fila['obs_operador'] }}</td>
                                                        <td>{{ $fila['obs_calidad'] }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endforeach

                            </div>{{-- /proceso-block --}}
                        @endforeach

                    </div>{{-- /clase-block --}}
                @endforeach

            </div>{{-- /ot-block --}}
        @endforeach
    @endif

    <div class="footer">
        Reporte automático · {{ $fecha->format('d/m/Y') }} ·
        Este correo fue generado automáticamente, no responder.
    </div>

</body>

</html>
