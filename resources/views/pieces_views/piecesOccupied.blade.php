@extends('layouts.appMenu')

@section('head')
<title>Piezas Ocupadas - Project Saavedra</title>
<link rel="icon" type="image/png" href="{{ asset('images/lg_saavedra.png') }}">
<meta name="csrf-token" content="{{ csrf_token() }}">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@vite(['resources/css/pieces_views/piecesOccupied.css', 'resources/js/pieces_views/piecesOccupied.js'])
@endsection

@section('background-body', 'background-image:url("' . asset("images/fondoLogin.jpg") . '")')

@section('content')
<div class="pieces-occupied-wrapper">
    <!-- Encabezado centrado con icono de la vista (sin lg_saavedra) -->
    <div class="report-header">
        <div class="title-row">
            <img src="{{ asset('images/Piezas_Ocupadas-icon.png') }}" class="header-view-icon" alt="Piezas Ocupadas" />
            <h1>Control de Piezas Ocupadas</h1>
        </div>
        <p class="subtitle">Monitoreo, liberación y reasignación de piezas retenidas o huérfanas en procesos de producción</p>
    </div>

    @include('layouts.partials.messages')

    <!-- Cinta de KPIs compacta (estilo cinta de estado) -->
    <div class="kpi-ribbon">
        <div class="kpi-chip kpi-chip-occupied">
            <img src="{{ asset('images/perspectiva-icon.png') }}" class="kpi-icon" alt="" />
            <span class="kpi-val" id="stat-total-occupied">0</span>
            <span class="kpi-txt">Piezas Ocupadas</span>
        </div>

        <div class="kpi-chip kpi-chip-orphan">
            <img src="{{ asset('images/Aviso.png') }}" class="kpi-icon" alt="" />
            <span class="kpi-val" id="stat-total-orphans">0</span>
            <span class="kpi-txt">Huérfanas (Sin máquina)</span>
        </div>

        <div class="kpi-chip kpi-chip-ots">
            <img src="{{ asset('images/documento.png') }}" class="kpi-icon" alt="" />
            <span class="kpi-val" id="stat-affected-ots">0</span>
            <span class="kpi-txt">OTs Involucradas</span>
        </div>
    </div>

    <!-- Barra de Filtros compacta horizontal (estilo Reporte de Piezas) -->
    <div class="filters">
        <div class="filter">
            <select id="select-ot">
                <option value="">Todas las OTs</option>
                @foreach ($workOrders as $wo)
                    <option value="{{ $wo->id }}">OT {{ $wo->id }} {{ $wo->moldura ? '— ' . $wo->moldura->nombre : '' }}</option>
                @endforeach
            </select>
            <label for="select-ot"><img src="{{ asset('images/folio-icon.png') }}" class="icon-label" alt="" /> ORDEN DE TRABAJO (OT)</label>
        </div>

        <div class="filter">
            <select id="select-clase">
                <option value="">Todas las Clases</option>
            </select>
            <label for="select-clase"><img src="{{ asset('images/icono_cotas.png') }}" class="icon-label" alt="" /> CLASE</label>
        </div>

        <div class="filter">
            <select id="select-proceso">
                <option value="">Todos los Procesos</option>
                @foreach ($processesList as $proc)
                    <option value="{{ $proc['key'] }}">{{ $proc['label'] }}</option>
                @endforeach
            </select>
            <label for="select-proceso"><img src="{{ asset('images/ProgramsProduction.png') }}" class="icon-label" alt="" /> PROCESO</label>
        </div>

        <div class="filter-actions-inline">
            <button type="button" id="btn-refresh" class="btn-refresh-gis" title="Recargar listado">
                <img src="{{ asset('images/actualizar.png') }}" class="icon-refresh" alt="" /> Actualizar
            </button>
        </div>
    </div>

    <!-- Barra de Pestañas: Piezas Ocupadas vs Historial de Acciones Administrativas -->
    <div class="view-tabs-bar">
        <button type="button" class="view-tab-btn active" id="tab-btn-pieces">
            <img src="{{ asset('images/perspectiva-icon.png') }}" class="tab-btn-icon" alt="" />
            <span>Piezas Ocupadas</span>
            <span class="tab-badge" id="tab-badge-pieces">0</span>
        </button>
        <button type="button" class="view-tab-btn" id="tab-btn-logs">
            <img src="{{ asset('images/documento.png') }}" class="tab-btn-icon" alt="" />
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
                        <img src="{{ asset('images/loading.gif') }}" class="loading-gif" alt="Cargando..." />
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
                        <img src="{{ asset('images/loading.gif') }}" class="loading-gif" alt="Cargando..." />
                        <p>Cargando historial de auditoría...</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Pie de tabla informativo -->
    <div class="table-footer-legend">
        <span id="footer-count-text">Cargando registros...</span>
        <span class="legend-note" id="footer-legend-note">Nota: Las filas sombreadas en tono rojizo corresponden a piezas <strong>huérfanas</strong> (su máquina o meta ya no está activa).</span>
    </div>
</div>

<!-- Modal para Modificar Pieza -->
<div class="modal-overlay" id="edit-piece-modal" style="display: none;">
    <div class="modal-card">
        <div class="modal-header">
            <h3>
                <span class="modal-header-icon-box">
                    <img src="{{ asset('images/edit.png') }}" alt="" />
                </span>
                Modificar Pieza Ocupada
            </h3>
            <button type="button" class="btn-close-modal" id="btn-close-modal" title="Cerrar ventana">&times;</button>
        </div>
        <form id="form-edit-piece">
            <input type="hidden" id="edit-proceso" name="proceso" />
            <input type="hidden" id="edit-piece-id" name="piece_id" />

            <div class="modal-body">
                <div class="info-badge-row">
                    <div class="info-pill pill-ot">
                        <img src="{{ asset('images/folio-icon.png') }}" class="pill-icon" alt="" />
                        <span class="pill-label">OT:</span>
                        <strong class="pill-val" id="badge-modal-ot">-</strong>
                    </div>
                    <div class="info-pill pill-clase">
                        <img src="{{ asset('images/icono_cotas.png') }}" class="pill-icon" alt="" />
                        <span class="pill-label">Clase:</span>
                        <strong class="pill-val" id="badge-modal-clase">-</strong>
                    </div>
                    <div class="info-pill pill-proceso">
                        <img src="{{ asset('images/ProgramsProduction.png') }}" class="pill-icon" alt="" />
                        <span class="pill-label">Proceso:</span>
                        <strong class="pill-val" id="badge-modal-proceso">-</strong>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <label for="edit-n-pieza">
                            <img src="{{ asset('images/perspectiva-icon.png') }}" class="input-icon-label" alt="" />
                            Número de Pieza
                        </label>
                        <div class="input-wrapper">
                            <input type="text" id="edit-n-pieza" name="n_pieza" class="modal-input" required autocomplete="off" />
                        </div>
                    </div>
                    <div class="form-col">
                        <label for="edit-n-juego">
                            <img src="{{ asset('images/icono_cotas.png') }}" class="input-icon-label" alt="" />
                            Número de Juego
                        </label>
                        <div class="input-wrapper">
                            <input type="text" id="edit-n-juego" name="n_juego" class="modal-input" required autocomplete="off" />
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <label for="edit-estado">
                            <img src="{{ asset('images/Aviso.png') }}" class="input-icon-label" alt="" />
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
                            <span class="helper-bullet">ℹ</span> Cambiar a <strong>0</strong> o <strong>2</strong> para desbloquear el proceso.
                        </small>
                    </div>
                    <div class="form-col">
                        <label for="edit-id-meta">
                            <img src="{{ asset('images/documento.png') }}" class="input-icon-label" alt="" />
                            ID de Meta Asignada
                        </label>
                        <div class="input-wrapper">
                            <input type="number" id="edit-id-meta" name="id_meta" class="modal-input" placeholder="Opcional / Dejar vacío" min="1" />
                        </div>
                        <small class="helper-text">
                            <span class="helper-bullet">ℹ</span> Meta de producción vinculada.
                        </small>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-gis btn-modal-cancel" id="btn-cancel-edit">Cancelar</button>
                <button type="button" class="btn-gis btn-modal-danger" id="btn-delete-piece" title="Eliminar registro huérfano por completo">
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
                    <img src="{{ asset('images/Liberar.png') }}" id="confirm-modal-icon" alt="" />
                </span>
                <span id="confirm-modal-title">¿Liberar pieza?</span>
            </h3>
            <button type="button" class="btn-close-modal" id="btn-close-confirm" title="Cerrar ventana">&times;</button>
        </div>
        <div class="modal-body modal-body-confirm">
            <div class="info-badge-row confirm-badge-row">
                <div class="info-pill pill-ot">
                    <img src="{{ asset('images/folio-icon.png') }}" class="pill-icon" alt="" />
                    <span class="pill-label">OT:</span>
                    <strong class="pill-val" id="confirm-pill-ot">-</strong>
                </div>
                <div class="info-pill pill-clase">
                    <img src="{{ asset('images/icono_cotas.png') }}" class="pill-icon" alt="" />
                    <span class="pill-label">Clase:</span>
                    <strong class="pill-val" id="confirm-pill-clase">-</strong>
                </div>
                <div class="info-pill pill-proceso">
                    <img src="{{ asset('images/ProgramsProduction.png') }}" class="pill-icon" alt="" />
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
    window.routes.freeUpPiece = "{{ route('piecesOccupied.freeUp') }}";
    window.routes.freeUpBatch = "{{ route('piecesOccupied.freeUpBatch') }}";
    window.routes.updatePiece = "{{ route('piecesOccupied.update') }}";
    window.routes.destroyPiece = "{{ route('piecesOccupied.destroy') }}";
    window.routes.getLogs = "{{ route('piecesOccupied.logs') }}";

    window.images = {
        liberar: "{{ asset('images/Liberar.png') }}",
        editar: "{{ asset('images/edit.png') }}",
        delete: "{{ asset('images/delete.png') }}",
        aviso: "{{ asset('images/Aviso.png') }}",
        ready: "{{ asset('images/ready.png') }}",
        loading: "{{ asset('images/loading.gif') }}"
    };
</script>
@endsection
