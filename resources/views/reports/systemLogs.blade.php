@extends('layouts.appMenu')

@section('head')
    <title>Reporte de Logs de Sistema</title>
    @vite(['resources/css/reports/systemLogs.css', 'resources/js/reports/systemLogs.js'])
@endsection
<script>
    window.baseUrl = "{{ url('/') }}";
    window.logsData = {!! json_encode($logsRender) !!};
    window.selectedItems = {!! json_encode($selectedItems) !!};
    window.filtrosDisponibles = {!! json_encode($filtrosDisponibles) !!};
    window.nextPageUrl = "{{ $nextPage ?? '' }}";
    window.hasMorePages = {{ $hasMore ? 'true' : 'false' }};
</script>
@section('background-body', 'background-image:url("' . asset('images/fondoLogin.jpg') . '")')
@section('content')

    @if (!isset($logsRender) || count($logsRender) == 0)
        <style>
            @media (max-width: 991.98px) {
                .container {
                    width: 100%;
                }

                .title_ot {
                    width: 100%;
                    text-align: center;
                    font-size: 1rem;
                }

                .icono-liberar,
                .icono-rechazar {
                    width: 20px;
                    height: 20px;
                }
            }
        </style>
    @endif

    <form action="{{ route('systemLogsReport') }}" method="get" class="form-search" id="filters-form">
        <h1>Auditoría y Logs del Sistema</h1>
        <div class="filters"></div>

        @if (count($logsRender) > 0)
            <button type="submit" name="generate_pdf" value="true" class="btn-PDF">
                <img src="{{ asset('images/pdf.png') }}" alt="pdf" id="pdf" class="generar_pdf">
            </button>
        @endif

        @if (count($logsRender) > 0)
            <div class="div-table">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Operador</th>
                            <th>Acción</th>
                            <th>Detalles</th>
                            <th>Orden de Trabajo</th>
                            <th>N# Juego</th>
                            <th>Hora de Inicio</th>
                            <th>Hora de Término</th>
                            <th>Tiempo Total</th>
                            <th>Clase</th>
                            <th>Proceso</th>
                            <th>Máquina</th>
                        </tr>
                    </thead>
                </table>
            </div>

            <div class="log-footer-container">
                <!-- Elementos centrados -->
                <div class="total-records-found">
                    Registros encontrados: <span id="total-found-count" style="color: #21618C;">{{ $totalFound }}</span> |
                    Mostrados: <span id="current-count">{{ count($logsRender) }}</span>
                </div>
                <div class="filter-explanation">
                    Nota: Los filtros se aplican de forma acumulativa (puedes combinar múltiples criterios).
                </div>

                <!-- Botón de Depuración Manual (Izquierda) -->
                @if(auth()->user()->perfil == 1)
                    <div id="manual-purge-container">
                        <button type="button" id="btn-manual-purge" class="btn-manual-purge-premium">
                            Depurar Logs
                        </button>
                    </div>
                @endif

                <!-- Botón de carga en la parte inferior derecha -->
                <div id="load-more-container" style="display: {{ $hasMore ? 'block' : 'none' }};">
                    <button type="button" id="btn-load-more" class="btn-load-more-premium">
                        Cargar más registros...
                    </button>
                </div>
            </div>
        @else
            <div class="letrero">
                <label class="advertence"> No hay logs registrados aún.</label>
            </div>
        @endif
    </form>

    <div class="colors">
        <table class="table-colors">
            <thead>
                <tr>
                    <th colspan="2">Tabla de colores</th>
                </tr>
                <tr>
                    <th>Color</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <!-- Familia Azul -->
            <tr class="level-toggle" data-level="azul" style="cursor: pointer;">
                <th colspan="2" style="background-color: #21618C; color: #ffffff;">Logs Azules (Acceso / Sesión) <span
                        class="arrow">▶</span></th>
            </tr>
            <tr class="level-row level-azul" style="display: none;">
                <td style="background-color: #3498DB; color: white;">Azul Normal</td>
                <td>Inicio de Sesión y Reporte</td>
            </tr>
            <tr class="level-row level-azul" style="display: none;">
                <td style="background-color: #21618C; color: white;">Azul Oscuro</td>
                <td>Login Inspector / Logout</td>
            </tr>



            <!-- Familia Verde -->
            <tr class="level-toggle" data-level="verde" style="cursor: pointer;">
                <th colspan="2" style="background-color: #186A3B; color: #ffffff;">Logs Verdes (Éxito / Producción)
                    <span class="arrow">▶</span>
                </th>
            </tr>
            <tr class="level-row level-verde" style="display: none;">
                <td style="background-color: #D5F5E3; color: black;">Verde Claro</td>
                <td>Liberación por Calidad (Correcto)</td>
            </tr>
            <tr class="level-row level-verde" style="display: none;">
                <td style="background-color: #27AE60; color: white;">Verde Normal</td>
                <td>Captura de Medida</td>
            </tr>
            <tr class="level-row level-verde" style="display: none;">
                <td style="background-color: #186A3B; color: white;">Verde Oscuro</td>
                <td>Término de Reporte</td>
            </tr>

            <!-- Familia Amarilla / Ocre -->
            <tr class="level-toggle" data-level="amarillo" style="cursor: pointer;">
                <th colspan="2" style="background-color: #9A7D0A; color: #ffffff;">Logs Amarillos (Auditoría /
                    Autorización)
                    <span class="arrow">▶</span>
                </th>
            </tr>
            <tr class="level-row level-amarillo" style="display: none;">
                <td style="background-color: #F1C40F; color: black;">Amarillo Normal</td>
                <td>Captura Sospechosa (Advertencia)</td>
            </tr>
            <tr class="level-row level-amarillo" style="display: none;">
                <td style="background-color: #9A7D0A; color: white;">Amarillo Oscuro</td>
                <td>Autorizaciones y Edición de Reporte</td>
            </tr>

            <!-- Familia Morada (Auditoría / Calidad / Dibujos) -->
            <tr class="level-toggle" data-level="morado" style="cursor: pointer;">
                <th colspan="2" style="background-color: #512E5F; color: #ffffff;">Auditoría de Calidad y Dibujos <span
                        class="arrow">▶</span></th>
            </tr>
            <tr class="level-row level-morado" style="display: none;">
                <td style="background-color: #8E44AD; color: white;">Morado Normal</td>
                <td>Edición de Juegos en Reporte</td>
            </tr>
            <tr class="level-row level-morado" style="display: none;">
                <td style="background-color: #512E5F; color: white;">Morado Oscuro</td>
                <td>Intento de Liberación de Calidad</td>
            </tr>

            <!-- Familia Roja -->
            <tr class="level-toggle" data-level="rojo" style="cursor: pointer;">
                <th colspan="2" style="background-color: #943126; color: #ffffff;">Logs Rojos (Fallas / Alertas) <span
                        class="arrow">▼</span></th>
            </tr>
            <tr class="level-row level-rojo" style="display: table-row;">
                <td style="background-color: #FADBD8; color: black;">Rojo Muy Claro</td>
                <td>Rechazo por Calidad (Error)</td>
            </tr>
            <tr class="level-row level-rojo" style="display: table-row;">
                <td style="background-color: #F5B7B1; color: black;">Rojo Claro</td>
                <td>Productividad y Advertencias de Login</td>
            </tr>
            <tr class="level-row level-rojo" style="display: table-row;">
                <td style="background-color: #E74C3C; color: white;">Rojo Normal</td>
                <td>Errores Técnicos de Sistema</td>
            </tr>
            <tr class="level-row level-rojo" style="display: table-row;">
                <td style="background-color: #943126; color: white;">Rojo Oscuro</td>
                <td>Problema Recurrente de Llenado (Crítico)</td>
            </tr>
            </tbody>
        </table>
    </div>
@endsection
