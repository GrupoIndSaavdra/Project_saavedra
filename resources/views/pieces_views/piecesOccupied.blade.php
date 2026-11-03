@extends('layouts.appMenu')

@section('head')
    <title>Piezas Ocupadas - Project Saavedra</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('images/logos/favicon.ico') }}?v=3">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @vite(['resources/css/pieces_views/piecesOccupied.css', 'resources/js/pieces_views/piecesOccupied.js'])
@endsection

@section('background-body', 'background-image:url("' . asset('images/fondos/fondoLogin.jpg') . '")')

@section('content')
    <div class="pieces-occupied-wrapper">
        <!-- Encabezado centrado con icono de la vista al inicio y al final (sin lg_saavedra) -->
        <div class="report-header">
            <div class="title-row">
                <img src="{{ asset('images/Color_Icons/Vistas/Piezas_Ocupadas.png') }}" class="header-view-icon"
                    alt="Piezas Ocupadas" />
                <h1>Control de Piezas Ocupadas</h1>
                <img src="{{ asset('images/Color_Icons/Vistas/Piezas_Ocupadas.png') }}" class="header-view-icon"
                    alt="Piezas Ocupadas" />
            </div>
            <p class="subtitle">Monitoreo, liberación y reasignación de piezas retenidas, desvinculadas o incompletas en
                procesos de producción</p>
        </div>

        @include('layouts.partials.messages')

        <!-- Cinta de KPIs compacta (estilo cinta de estado) -->
        <div class="kpi-ribbon">
            <div class="kpi-chip kpi-chip-occupied">
                <img src="{{ asset('images/Color_Icons/Generales/perspectiva-icon.png') }}" class="kpi-icon"
                    alt="" />
                <span class="kpi-val" id="stat-total-occupied">0</span>
                <span class="kpi-txt">Piezas Ocupadas</span>
            </div>

            <div class="kpi-chip kpi-chip-orphan">
                <img src="{{ asset('images/Color_Icons/Generales/Desvincular_Icon.png') }}" class="kpi-icon"
                    alt="" />
                <span class="kpi-val" id="stat-total-orphans">0</span>
                <span class="kpi-txt">Desvinculadas (Sin máquina)</span>
            </div>

            <div class="kpi-chip kpi-chip-incomplete">
                <img src="{{ asset('images/Color_Icons/Generales/Incompleto_Icon.png') }}" class="kpi-icon"
                    alt="" />
                <span class="kpi-val" id="stat-total-incompletes">0</span>
                <span class="kpi-txt">Juegos Incompletos</span>
            </div>

            <div class="kpi-chip kpi-chip-ots">
                <img src="{{ asset('images/Color_Icons/Generales/folio-icon.png') }}" class="kpi-icon"
                    alt="OTs Involucradas" />
                <span class="kpi-val" id="stat-affected-ots">0</span>
                <span class="kpi-txt">OTs Involucradas</span>
            </div>

            <button type="button" id="btn-refresh" class="kpi-chip kpi-btn-refresh" title="Recargar listado de piezas">
                <img src="{{ asset('images/Color_Icons/Generales/actualizar.png') }}" class="kpi-icon" alt="" />
                <span class="kpi-txt-btn">Actualizar</span>
            </button>
        </div>

        <!-- Barra de Filtros compacta horizontal (estilo Reporte de Piezas) -->
        <div class="filters">
            <div class="filter">
                <select id="select-ot">
                    <option value="">Todas las OTs</option>
                    @foreach ($workOrders as $wo)
                        <option value="{{ $wo->id }}">OT {{ $wo->id }}
                            {{ $wo->moldura ? '— ' . $wo->moldura->nombre : '' }}</option>
                    @endforeach
                </select>
                <label for="select-ot"><img src="{{ asset('images/Light_Icons/Filtro_OT-Light.png') }}" class="icon-label"
                        alt="" /> ORDEN DE TRABAJO</label>
            </div>

            <div class="filter">
                <select id="select-clase">
                    <option value="">Todas las Clases</option>
                    @if (isset($classesList))
                        @foreach ($classesList as $cName)
                            <option value="{{ $cName }}">{{ $cName }}</option>
                        @endforeach
                    @endif
                </select>
                <label for="select-clase"><img src="{{ asset('images/Light_Icons/Filtro_Clase-Light.png') }}"
                        class="icon-label" alt="" /> CLASE</label>
            </div>

            <div class="filter">
                <select id="select-proceso">
                    <option value="">Todos los Procesos</option>
                    @foreach ($processesList as $proc)
                        <option value="{{ $proc['key'] }}">{{ $proc['label'] }}</option>
                    @endforeach
                </select>
                <label for="select-proceso"><img src="{{ asset('images/Light_Icons/Filtro_Proceso-Light.png') }}"
                        class="icon-label" alt="" /> PROCESO</label>
            </div>

            <div class="filter">
                <select id="select-pieza" disabled title="Selecciona primero OT, Clase y Proceso para activar este filtro">
                    <option value="">Todas las Piezas</option>
                </select>
                <label for="select-pieza"><img src="{{ asset('images/Light_Icons/Filtro_Pieza-Light.png') }}"
                        class="icon-label" alt="" /> NÚMERO DE PIEZA</label>
            </div>

            <div class="filter">
                <select id="select-operador" title="Filtrar por operador">
                    <option value="">Todos los Operadores</option>
                    @if (isset($operatorsList))
                        @foreach ($operatorsList as $op)
                            <option value="{{ $op['matricula'] }}">{{ $op['nombre'] }}</option>
                        @endforeach
                    @endif
                </select>
                <label for="select-operador"><img src="{{ asset('images/Light_Icons/Filtro_Operador-Light.png') }}"
                        class="icon-label" alt="" /> OPERADOR</label>
            </div>

            <div class="filter-actions-inline">
                <button type="button" id="btn-clear-filters" class="btns btn-clear-filters" disabled
                    title="Limpiar todos los filtros">
                    Limpiar Filtros
                </button>
            </div>
        </div>

        <!-- Barra de Pestañas: Piezas Ocupadas vs Historial de Acciones Administrativas -->
        <div class="view-tabs-bar">
            <button type="button" class="view-tab-btn active" id="tab-btn-pieces">
                <img src="{{ asset('images/Color_Icons/Generales/perspectiva-icon.png') }}" class="tab-btn-icon"
                    alt="" />
                <span>Piezas Ocupadas</span>
                <span class="tab-badge" id="tab-badge-pieces">0</span>
            </button>
            <button type="button" class="view-tab-btn" id="tab-btn-logs">
                <img src="{{ asset('images/Color_Icons/Generales/documento.png') }}" class="tab-btn-icon"
                    alt="" />
                <span>Historial de Acciones Administrativas</span>
                <span class="tab-badge" id="tab-badge-logs">0</span>
            </button>
        </div>

        <!-- Contenedor 1: Tabla de Piezas Ocupadas -->
        <div class="div-table" id="container-pieces-table">
            <table class="table-gis" id="table-occupied-pieces">
                <thead>
                    <tr>
                        <th>OT</th>
                        <th>Clase</th>
                        <th>Proceso</th>
                        <th>Pieza / Juego</th>
                        <th>Meta / Máquina</th>
                        <th>Operador</th>
                        <th>Fecha Asignada</th>
                        <th>Condición</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="pieces-table-body">
                    <tr>
                        <td colspan="9" class="empty-state">
                            <img src="{{ asset('images/Color_Icons/Generales/loading.gif') }}" class="loading-gif"
                                alt="Cargando..." />
                            <p>Cargando piezas ocupadas...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Contenedor 2: Tabla de Logs y Auditoría Administrativa -->
        <div class="div-table" id="container-logs-table" style="display: none;">
            <table class="table-gis" id="table-admin-logs">
                <thead>
                    <tr>
                        <th>Fecha / Hora</th>
                        <th>Administrador</th>
                        <th>Acción</th>
                        <th>OT</th>
                        <th>Clase</th>
                        <th>Proceso</th>
                        <th>Pieza</th>
                        <th>Detalles de la Acción</th>
                    </tr>
                </thead>
                <tbody id="logs-table-body">
                    <tr>
                        <td colspan="8" class="empty-state">
                            <img src="{{ asset('images/Color_Icons/Generales/loading.gif') }}" class="loading-gif"
                                alt="Cargando..." />
                            <p>Cargando historial de auditoría...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Paginación Unificada GIS (idéntica a Reporte de Piezas) -->
        <div class="total-records-found" id="pieces-pagination-container"></div>
    </div>

    <!-- Drawer Flotante de Código de Colores (lateral izquierdo) -->
    <div class="colors-legend-container" id="colors-legend-container">
        <button type="button" class="colors-toggle-btn" id="colors-toggle-btn" title="Ver Código de Colores"
            aria-label="Código de Colores">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="toggle-icon">
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
                        <th>Condición / Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="color-row">
                        <td class="color-chip chip-rojo">
                            <div class="chip-content-wrap">
                                <span class="chip-icon-box">
                                    <img src="{{ asset('images/Color_Icons/Generales/Desvincular_Icon.png') }}"
                                        class="chip-inline-icon" alt="" />
                                </span>
                                <span class="chip-label-txt">Rojo</span>
                            </div>
                        </td>
                        <td class="color-desc"><strong>Desvinculada (Máquina libre):</strong> La meta o máquina concluyó su
                            turno y no está activa. Registro retenido en el sistema.</td>
                    </tr>
                    <tr class="color-row">
                        <td class="color-chip chip-amarillo">
                            <div class="chip-content-wrap">
                                <span class="chip-icon-box">
                                    <img src="{{ asset('images/Color_Icons/Generales/Incompleto_Icon.png') }}"
                                        class="chip-inline-icon" alt="" />
                                </span>
                                <span class="chip-label-txt">Amarillo</span>
                            </div>
                        </td>
                        <td class="color-desc"><strong>Juego Incompleto:</strong> Mitad faltante (solo se registró H o M).
                            Bloquea el maquinado del juego.</td>
                    </tr>
                    <tr class="color-row">
                        <td class="color-chip chip-verde">
                            <div class="chip-content-wrap">
                                <span class="chip-icon-box">
                                    <img src="{{ asset('images/Color_Icons/Generales/ready.png') }}" class="chip-inline-icon"
                                        alt="" />
                                </span>
                                <span class="chip-label-txt">Verde</span>
                            </div>
                        </td>
                        <td class="color-desc"><strong>Máquina Activa (Vigente):</strong> Pieza asignada en proceso productivo
                            vigente con máquina en operación (acciones bloqueadas para proteger el maquinado).</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal para Modificar Pieza -->
    <div class="modal-overlay" id="edit-piece-modal" style="display: none;">
        <div class="modal-card">
            <div class="modal-header">
                <h3>
                    <span class="modal-header-icon-box">
                        <img src="{{ asset('images/Color_Icons/Generales/edit.png') }}" alt="" />
                    </span>
                    Modificar Pieza Ocupada
                </h3>
                <button type="button" class="btn-close-modal" id="btn-close-modal"
                    title="Cerrar ventana">&times;</button>
            </div>
            <form id="form-edit-piece">
                <input type="hidden" id="edit-proceso" name="proceso" />
                <input type="hidden" id="edit-piece-id" name="piece_id" />

                <div class="modal-body">
                    <div class="info-badge-row">
                        <div class="info-pill pill-ot">
                            <img src="{{ asset('images/Color_Icons/Generales/folio-icon.png') }}" class="pill-icon"
                                alt="" />
                            <span class="pill-label">OT:</span>
                            <strong class="pill-val" id="badge-modal-ot">-</strong>
                        </div>
                        <div class="info-pill pill-clase">
                            <img src="{{ asset('images/Color_Icons/Generales/icono_cotas.png') }}" class="pill-icon"
                                alt="" />
                            <span class="pill-label">Clase:</span>
                            <strong class="pill-val" id="badge-modal-clase">-</strong>
                        </div>
                        <div class="info-pill pill-proceso">
                            <img src="{{ asset('images/Color_Icons/Generales/ProgramsProduction.png') }}"
                                class="pill-icon" alt="" />
                            <span class="pill-label">Proceso:</span>
                            <strong class="pill-val" id="badge-modal-proceso">-</strong>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-col">
                            <label for="edit-n-pieza">
                                <img src="{{ asset('images/Color_Icons/Generales/perspectiva-icon.png') }}"
                                    class="input-icon-label" alt="" />
                                Número de Pieza
                            </label>
                            <div class="input-wrapper">
                                <input type="text" id="edit-n-pieza" name="n_pieza" class="modal-input" required
                                    autocomplete="off" />
                            </div>
                        </div>
                        <div class="form-col">
                            <label for="edit-n-juego">
                                <img src="{{ asset('images/Color_Icons/Generales/icono_cotas.png') }}"
                                    class="input-icon-label" alt="" />
                                Número de Juego
                            </label>
                            <div class="input-wrapper">
                                <input type="text" id="edit-n-juego" name="n_juego" class="modal-input" required
                                    autocomplete="off" />
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-col">
                            <label for="edit-estado">
                                <img src="{{ asset('images/Color_Icons/Generales/Aviso.png') }}" class="input-icon-label"
                                    alt="" />
                                Estado de la Pieza
                            </label>
                            <div class="input-wrapper">
                                <select id="edit-estado" name="estado" class="modal-input modal-select">
                                    <option value="0">0 — Disponible / Desocupada</option>
                                    <option value="1" selected>1 — Ocupada (En progreso)</option>
                                    <option value="2">2 — Maquinada / Completada</option>
                                </select>
                            </div>
                            <small class="helper-text">
                                <span class="helper-bullet">ℹ</span> Cambiar a <strong>0</strong> o <strong>2</strong> para
                                desbloquear el proceso.
                            </small>
                        </div>
                        <div class="form-col">
                            <label for="edit-id-meta">
                                <img src="{{ asset('images/Color_Icons/Generales/documento.png') }}"
                                    class="input-icon-label" alt="" />
                                ID de Meta Asignada
                            </label>
                            <div class="input-wrapper">
                                <input type="number" id="edit-id-meta" name="id_meta" class="modal-input"
                                    placeholder="Opcional / Dejar vacío" min="1" />
                            </div>
                            <small class="helper-text">
                                <span class="helper-bullet">ℹ</span> Meta de producción vinculada.
                            </small>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-gis btn-modal-cancel" id="btn-cancel-edit">Cancelar</button>
                    <button type="button" class="btn-gis btn-modal-danger" id="btn-delete-piece"
                        title="Eliminar registro huérfano por completo">
                        Eliminar Registro
                    </button>
                    <button type="submit" class="btn-gis btn-modal-save" id="btn-save-edit">
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Flotante para Confirmar Acciones (Liberar / Eliminar) - GIS Premium -->
    <div class="modal-overlay" id="confirm-action-modal" style="display: none;">
        <div class="modal-card modal-card-confirm">
            <div class="modal-header">
                <h3>
                    <span class="modal-header-icon-box" id="confirm-modal-icon-box">
                        <img src="{{ asset('images/Color_Icons/Generales/Liberar.png') }}" id="confirm-modal-icon"
                            alt="" />
                    </span>
                    <span id="confirm-modal-title">¿Liberar pieza?</span>
                </h3>
                <button type="button" class="btn-close-modal" id="btn-close-confirm"
                    title="Cerrar ventana">&times;</button>
            </div>
            <div class="modal-body modal-body-confirm">
                <div class="info-badge-row confirm-badge-row">
                    <div class="info-pill pill-ot">
                        <img src="{{ asset('images/Color_Icons/Generales/folio-icon.png') }}" class="pill-icon"
                            alt="" />
                        <span class="pill-label">OT:</span>
                        <strong class="pill-val" id="confirm-pill-ot">-</strong>
                    </div>
                    <div class="info-pill pill-clase">
                        <img src="{{ asset('images/Color_Icons/Generales/icono_cotas.png') }}" class="pill-icon"
                            alt="" />
                        <span class="pill-label">Clase:</span>
                        <strong class="pill-val" id="confirm-pill-clase">-</strong>
                    </div>
                    <div class="info-pill pill-proceso">
                        <img src="{{ asset('images/Color_Icons/Generales/ProgramsProduction.png') }}" class="pill-icon"
                            alt="" />
                        <span class="pill-label">Proceso:</span>
                        <strong class="pill-val" id="confirm-pill-proceso">-</strong>
                    </div>
                </div>

                <div class="confirm-message-box">
                    <p id="confirm-modal-text" class="confirm-text-desc">
                        ¿Estás seguro de liberar esta pieza?
                    </p>
                </div>
            </div>
            <div class="modal-footer modal-footer-confirm">
                <button type="button" class="btn-gis btn-modal-cancel" id="btn-cancel-confirm">Cancelar</button>
                <button type="button" class="btn-gis btn-modal-confirm-btn" id="btn-do-confirm">
                    Sí, liberar pieza
                </button>
            </div>
        </div>
    </div>

    <script>
        window.baseUrl = "{{ url('/') }}";
        window.routes = window.routes || {};
        window.routes.getOccupiedPieces = "{{ route('piecesOccupied.getPieces') }}";
        window.routes.getClasses = "{{ route('piecesOccupied.classes') }}";
        window.routes.getPiecesByContext = "{{ route('piecesOccupied.piecesByContext') }}";
        window.routes.freeUpPiece = "{{ route('piecesOccupied.freeUp') }}";
        window.routes.freeUpBatch = "{{ route('piecesOccupied.freeUpBatch') }}";
        window.routes.updatePiece = "{{ route('piecesOccupied.update') }}";
        window.routes.destroyPiece = "{{ route('piecesOccupied.destroy') }}";
        window.routes.getLogs = "{{ route('piecesOccupied.logs') }}";

        window.images = {
            liberar: "{{ asset('images/Color_Icons/Generales/Liberar.png') }}",
            editar: "{{ asset('images/Color_Icons/Generales/edit.png') }}",
            delete: "{{ asset('images/Color_Icons/Generales/delete.png') }}",
            aviso: "{{ asset('images/Color_Icons/Generales/Aviso.png') }}",
            ready: "{{ asset('images/Color_Icons/Generales/ready.png') }}",
            ready_shadow: "{{ asset('images/Shadow_Icons/ready-Shadow.png') }}",
            loading: "{{ asset('images/Color_Icons/Generales/loading.gif') }}",
            desvincular_color: "{{ asset('images/Color_Icons/Generales/Desvincular_Icon.png') }}",
            desvincular_shadow: "{{ asset('images/Shadow_Icons/Desvincular_Icon-Shadow.png') }}",
            incompleto_color: "{{ asset('images/Color_Icons/Generales/Incompleto_Icon.png') }}",
            incompleto_shadow: "{{ asset('images/Shadow_Icons/Incompleto_Icon-Shadow.png') }}",
            bloqueado_light: "{{ asset('images/Light_Icons/Bloqueado-Light.png') }}",
            desbloqueado_color: "{{ asset('images/Color_Icons/Generales/desbloqueado.png') }}",
            editar_light: "{{ asset('images/Light_Icons/Editar_Icon-Light.png') }}",
            editar_color: "{{ asset('images/Color_Icons/Generales/Editar_Icon.png') }}"
        };
    </script>
@endsection
