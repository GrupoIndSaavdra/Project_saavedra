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
    window.isAdminOnly = {{ request('admin_only') == 1 ? 'true' : 'false' }};
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
        <div class="report-header">
            @php
                $logTitle = request('admin_only') == 1 ? 'Logs de administradores' : 'Auditoría y Logs del Sistema';
                $logSubtitle =
                    request('admin_only') == 1
                        ? 'Trazabilidad y registro de acciones administrativas'
                        : 'Historial de eventos, cambios y trazabilidad general del sistema';
            @endphp
            <div class="title-row">
                <img src="{{ asset('images/Iconos_Vistas/Logs_Produccion_Administradores.png') }}" class="header-view-icon"
                    alt="{{ $logTitle }}" />
                <h1>{{ $logTitle }}</h1>
                <img src="{{ asset('images/Iconos_Vistas/Logs_Produccion_Administradores.png') }}" class="header-view-icon"
                    alt="{{ $logTitle }}" />
            </div>
            <p class="subtitle">{{ $logSubtitle }}</p>
            @if (count($logsRender) > 0)
                <button type="submit" name="generate_pdf" value="true" class="btn-PDF" title="Generar PDF">
                    <div class="pdf-btn-wrapper">
                        <img src="{{ asset('images/pdf-view-shadow.png') }}" class="pdf-icon icon-shadow" alt="PDF">
                        <img src="{{ asset('images/pdf-view.png') }}" class="pdf-icon icon-view generar_pdf" id="pdf"
                            alt="PDF">
                    </div>
                </button>
            @endif
        </div>
        @if (request('admin_only') == 1)
            <input type="hidden" name="admin_only" value="1">
        @endif
        <div class="filters"></div>

        @if (count($logsRender) > 0)
            <div class="div-table">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th class="col-admin-label">Operador</th>
                            <th>Acción</th>
                            <th>Detalles</th>
                            <th class="col-operador-only">Orden de Trabajo</th>
                            <th class="col-operador-only">N# Juego</th>
                            <th class="col-operador-only">Hora de Inicio</th>
                            <th class="col-operador-only">Hora de Término</th>
                            <th class="col-operador-only">Tiempo Total</th>
                            <th class="col-operador-only">Clase</th>
                            <th class="col-operador-only">Proceso</th>
                            <th class="col-operador-only">Máquina</th>
                        </tr>
                    </thead>
                </table>
            </div>

            <div class="log-footer-container">
                <!-- Elementos centrados -->
                <div class="total-records-found">
                    Registros encontrados: <span id="total-found-count" class="sys-color-21618C">{{ $totalFound }}</span>
                    |
                    Mostrados: <span id="current-count">{{ count($logsRender) }}</span>
                </div>
                <div class="filter-explanation">
                    Nota: Los filtros se aplican de forma acumulativa (puedes combinar múltiples criterios).
                </div>

                <!-- Botón de Depuración Manual (Izquierda) -->
                @if (in_array(auth()->user()->perfil, [1, 3, 6]))
                    <div id="manual-purge-container">
                        <button type="button" id="btn-manual-purge" class="btn-manual-purge-premium">
                            <span id="purge-btn-icon">🗑</span>
                            <span id="purge-btn-text">Depurar Logs</span>
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
                <img src="{{ asset('images/Revisando.png') }}" alt="Sin logs" class="letrero-icon">
                <label class="advertence">No hay logs registrados aún.</label>
            </div>
        @endif
    </form>

    <div class="colors-legend-container">
        <button type="button" class="colors-toggle-btn" title="Ver Código de Colores" aria-label="Código de Colores">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                stroke-linecap="round" stroke-linejoin="round" class="toggle-icon">
                <circle cx="13.5" cy="6.5" r=".5" fill="currentColor" />
                <circle cx="17.5" cy="10.5" r=".5" fill="currentColor" />
                <circle cx="8.5" cy="7.5" r=".5" fill="currentColor" />
                <circle cx="6.5" cy="12.5" r=".5" fill="currentColor" />
                <path
                    d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.563-2.512 5.563-5.563C22 6.5 17.5 2 12 2z" />
            </svg>
            <span class="toggle-text">Código de Colores</span>
        </button>
        <div class="colors-content">
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
                <tbody>
                    <!-- Familia Azul -->
                    <tr class="level-toggle sys-cursor-pointer" data-level="azul">
                        <th colspan="2" class="sys-background-color-21618C sys-color-ffffff">Logs Azules (Acceso /
                            Sesión) <span class="arrow">▶</span></th>
                    </tr>
                    <tr class="level-row level-azul sys-display-none">
                        <td class="sys-background-color-3498DB sys-color-white">Azul Normal</td>
                        <td>Inicio de Sesión, Reporte y Desbloqueo de PC</td>
                    </tr>
                    <tr class="level-row level-azul sys-display-none">
                        <td class="sys-background-color-21618C sys-color-white">Azul Oscuro</td>
                        <td>Login Inspector / Logout</td>
                    </tr>

                    <!-- Familia Verde -->
                    <tr class="level-toggle sys-cursor-pointer" data-level="verde">
                        <th colspan="2" class="sys-background-color-186A3B sys-color-ffffff">Logs Verdes (Éxito /
                            Producción)
                            <span class="arrow">▶</span>
                        </th>
                    </tr>
                    <tr class="level-row level-verde sys-display-none">
                        <td class="sys-background-color-D5F5E3 sys-color-black">Verde Claro</td>
                        <td>Liberación por Calidad (Correcto)</td>
                    </tr>
                    <tr class="level-row level-verde sys-display-none">
                        <td class="sys-background-color-27AE60 sys-color-white">Verde Normal</td>
                        <td>Captura de Medida, Cargo de OT y Clase</td>
                    </tr>
                    <tr class="level-row level-verde sys-display-none">
                        <td class="sys-background-color-186A3B sys-color-white">Verde Oscuro</td>
                        <td>Término de Reporte y Cotas Nominales</td>
                    </tr>

                    <!-- Familia Amarilla / Ocre -->
                    <tr class="level-toggle sys-cursor-pointer" data-level="amarillo">
                        <th colspan="2" class="sys-background-color-9A7D0A sys-color-ffffff">Logs Amarillos (Auditoría
                            / Avisos)
                            <span class="arrow">▶</span>
                        </th>
                    </tr>
                    <tr class="level-row level-amarillo sys-display-none">
                        <td class="sys-background-color-F1C40F sys-color-black">Amarillo Brillante</td>
                        <td>Captura Sospechosa (Advertencia)</td>
                    </tr>
                    <tr class="level-row level-amarillo sys-display-none">
                        <td class="sys-background-color-B7950B sys-color-white">Amarillo Mostaza</td>
                        <td>Avisos de Sistema y Documentación Técnica</td>
                    </tr>
                    <tr class="level-row level-amarillo sys-display-none">
                        <td class="sys-background-color-9A7D0A sys-color-white">Amarillo Ocre</td>
                        <td>Autorizaciones, Edición y Modificación de OT</td>
                    </tr>

                    <!-- Familia Morada (Auditoría / Calidad / Dibujos) -->
                    <tr class="level-toggle sys-cursor-pointer" data-level="morado">
                        <th colspan="2" class="sys-background-color-512E5F sys-color-ffffff">Auditoría de Calidad y
                            Dibujos
                            <span class="arrow">▶</span>
                        </th>
                    </tr>
                    <tr class="level-row level-morado sys-display-none">
                        <td class="sys-background-color-D7BDE2 sys-color-black">Morado Claro</td>
                        <td>Consulta de Dibujos y Reporte de OT</td>
                    </tr>
                    <tr class="level-row level-morado sys-display-none">
                        <td class="sys-background-color-8E44AD sys-color-white">Morado Normal</td>
                        <td>Edición de Juegos en Reporte</td>
                    </tr>
                    <tr class="level-row level-morado sys-display-none">
                        <td class="sys-background-color-512E5F sys-color-white">Morado Oscuro</td>
                        <td>Intento de Liberación de Calidad</td>
                    </tr>

                    <!-- Familia Roja -->
                    <tr class="level-toggle sys-cursor-pointer" data-level="rojo">
                        <th colspan="2" class="sys-background-color-943126 sys-color-ffffff">Logs Rojos (Fallas /
                            Alertas) <span class="arrow">▶</span></th>
                    </tr>
                    <tr class="level-row level-rojo sys-display-none">
                        <td class="sys-background-color-FADBD8 sys-color-black">Rojo Muy Claro</td>
                        <td>Rechazo por Calidad (Error)</td>
                    </tr>
                    <tr class="level-row level-rojo sys-display-none">
                        <td class="sys-background-color-F5B7B1 sys-color-black">Rojo Claro</td>
                        <td>Productividad y Advertencias de Login</td>
                    </tr>
                    <tr class="level-row level-rojo sys-display-none">
                        <td class="sys-background-color-E74C3C sys-color-white">Rojo Normal</td>
                        <td>Errores Técnicos de Sistema</td>
                    </tr>
                    <tr class="level-row level-rojo sys-display-none">
                        <td class="sys-background-color-943126 sys-color-white">Rojo Oscuro</td>
                        <td>Problema Recurrente de Llenado (Crítico)</td>
                    </tr>
                </tbody>
            </table>

            {{-- Tabla de colores exclusiva para modo Administrador --}}
            <table class="table-colors table-colors-admin sys-display-none">
                <thead>
                    <tr>
                        <th colspan="2">Tabla de colores — Administrador</th>
                    </tr>
                    <tr>
                        <th>Color</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Familia Azul: Sesión -->
                    <tr class="level-toggle sys-cursor-pointer" data-level="admin-azul">
                        <th colspan="2" class="sys-background-color-21618C sys-color-fff">Acceso / Sesión <span
                                class="arrow">▶</span></th>
                    </tr>
                    <tr class="level-row level-admin-azul sys-display-none">
                        <td class="sys-background-color-3498DB sys-color-white">Azul Normal</td>
                        <td>Inicio de Sesión</td>
                    </tr>
                    <tr class="level-row level-admin-azul sys-display-none">
                        <td class="sys-background-color-21618C sys-color-white">Azul Oscuro</td>
                        <td>Cierre de Sesión</td>
                    </tr>

                    <!-- Familia Verde: OT y Producción -->
                    <tr class="level-toggle sys-cursor-pointer" data-level="admin-verde">
                        <th colspan="2" class="sys-background-color-186A3B sys-color-fff">Gestión de OT y Producción
                            <span class="arrow">▶</span>
                        </th>
                    </tr>
                    <tr class="level-row level-admin-verde sys-display-none">
                        <td class="sys-background-color-27AE60 sys-color-white">Verde Normal</td>
                        <td>Cargo de OT, Cargo de Clase de OT</td>
                    </tr>
                    <tr class="level-row level-admin-verde sys-display-none">
                        <td class="sys-background-color-186A3B sys-color-white">Verde Oscuro</td>
                        <td>Cargo / Modificación de Cotas Nominales</td>
                    </tr>
                    <tr class="level-row level-admin-verde sys-display-none">
                        <td class="sys-background-color-1A8C5F sys-color-white">Verde Medio</td>
                        <td>Desocupación de Máquina</td>
                    </tr>

                    <!-- Familia Naranja: Dibujos -->
                    <tr class="level-toggle sys-cursor-pointer" data-level="admin-naranja">
                        <th colspan="2" class="sys-background-color-CA6F1E sys-color-fff">Gestión de Dibujos <span
                                class="arrow">▶</span></th>
                    </tr>
                    <tr class="level-row level-admin-naranja sys-display-none">
                        <td class="sys-background-color-E67E22 sys-color-white">Naranja Normal</td>
                        <td>Subida de Dibujo / Dibujo Fundición</td>
                    </tr>
                    <tr class="level-row level-admin-naranja sys-display-none">
                        <td class="sys-background-color-F39C12 sys-color-white">Naranja Claro</td>
                        <td>Reemplazo de Dibujo / Dibujo Fundición</td>
                    </tr>
                    <tr class="level-row level-admin-naranja sys-display-none">
                        <td class="sys-background-color-CA6F1E sys-color-white">Naranja Oscuro</td>
                        <td>Eliminación de Dibujo / Dibujo Fundición</td>
                    </tr>

                    <!-- Familia Cian: Manuales -->
                    <tr class="level-toggle sys-cursor-pointer" data-level="admin-manuales">
                        <th colspan="2" class="sys-background-color-2E86C1 sys-color-fff">Gestión de Manuales <span
                                class="arrow">▶</span></th>
                    </tr>
                    <tr class="level-row level-admin-manuales sys-display-none">
                        <td class="sys-background-color-5DADE2 sys-color-black">Cian Claro</td>
                        <td>Subida de Manual</td>
                    </tr>
                    <tr class="level-row level-admin-manuales sys-display-none">
                        <td class="sys-background-color-2874A6 sys-color-white">Cian Medio</td>
                        <td>Reemplazo de Manual</td>
                    </tr>
                    <tr class="level-row level-admin-manuales sys-display-none">
                        <td class="sys-background-color-2E86C1 sys-color-white">Cian Intenso</td>
                        <td>Eliminación de Manual</td>
                    </tr>

                    <!-- Familia Morada: Ayudas Visuales -->
                    <tr class="level-toggle sys-cursor-pointer" data-level="admin-ayudas">
                        <th colspan="2" class="sys-background-color-6C3483 sys-color-fff">Gestión de Ayudas Visuales
                            <span class="arrow">▶</span>
                        </th>
                    </tr>
                    <tr class="level-row level-admin-ayudas sys-display-none">
                        <td class="sys-background-color-A569BD sys-color-white">Morado Claro</td>
                        <td>Subida de Ayuda Visual</td>
                    </tr>
                    <tr class="level-row level-admin-ayudas sys-display-none">
                        <td class="sys-background-color-6C3483 sys-color-white">Morado Oscuro</td>
                        <td>Reemplazo de Ayuda Visual</td>
                    </tr>
                    <tr class="level-row level-admin-ayudas sys-display-none">
                        <td class="sys-background-color-7D3C98 sys-color-white">Morado Normal</td>
                        <td>Eliminación de Ayuda Visual</td>
                    </tr>

                    <!-- Carpetas -->
                    <tr class="level-toggle sys-cursor-pointer" data-level="admin-carpetas">
                        <th colspan="2" class="sys-background-color-1E8449 sys-color-fff">Gestión de Carpetas <span
                                class="arrow">▶</span></th>
                    </tr>
                    <tr class="level-row level-admin-carpetas sys-display-none">
                        <td class="sys-background-color-82E0AA sys-color-black">Verde Menta</td>
                        <td>Creación de Carpeta</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Overlay de progreso de depuración (fuera de la tabla y del formulario para abarcar toda la pantalla) --}}
    @if (in_array(auth()->user()->perfil, [1, 3, 6]))
        <div id="purge-progress-overlay" class="gis-lock-overlay" style="display:none;">
            <div class="gis-premium-modal" style="border-color: #033966;">
                {{-- Spinner GIS --}}
                <div class="lock-icon-container">
                    <div class="purge-progress-spinner"></div>
                </div>

                <h2 class="lock-title" style="color:#033966;">Depuración en Progreso</h2>
                <p class="lock-message">
                    Por favor espera. <strong>No cierres ni recargues esta página.</strong>
                </p>

                {{-- Barra de progreso --}}
                <div style="padding: 0 2rem 0.6rem;">
                    <div class="purge-progress-bar-track">
                        <div id="purge-progress-bar" class="purge-progress-bar-fill"></div>
                    </div>
                    <p id="purge-progress-status" class="purge-progress-status">Conectando con el servidor...</p>
                </div>

                <div style="padding-bottom: 2.2rem; padding-top: 0.4rem;">
                    <span style="font-size:0.78rem; color:#94a3b8; font-style:italic;">
                        Este proceso puede tardar varios minutos con grandes volúmenes de datos.
                    </span>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal de selección de fecha interactivo --}}
    @include('components.modal_cm_calendar')
@endsection
