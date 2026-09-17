{{--
    Partial: _log_panel.blade.php
    Panel colapsable con el historial de auditoría de ediciones del reporte.
    Carga el log bajo demanda (AJAX) para no saturar la carga inicial de la página.

    Variables requeridas:
        $reporte → SalidaMoldura
--}}
<div class="sm-log-panel" id="log-panel">

    <button class="sm-log-toggle" id="btn-toggle-log" aria-expanded="false">
        <span class="sm-log-toggle__text">Historial de Ediciones y Auditoría</span>
        <span class="sm-log-toggle__arrow" id="log-arrow">&#9660;</span>
    </button>

    <div class="sm-log-body hidden" id="log-body">

        <div class="sm-log-loading hidden" id="log-loading">
            <div class="spinner"></div>
            <span>Cargando historial de ediciones...</span>
        </div>

        <div class="sm-log-content hidden" id="log-content">
            {{-- Barra de Filtros --}}
            <div class="sm-log-filters">
                <div class="sm-log-filter-group">
                    <label for="filter-log-usuario">Usuario:</label>
                    <select id="filter-log-usuario" class="sm-log-filter-input">
                        <option value="">Todos los usuarios</option>
                    </select>
                </div>

                <div class="sm-log-filter-group">
                    <label for="filter-log-desde">Desde:</label>
                    <input type="date" id="filter-log-desde" class="sm-log-filter-input">
                </div>

                <div class="sm-log-filter-group">
                    <label for="filter-log-hasta">Hasta:</label>
                    <input type="date" id="filter-log-hasta" class="sm-log-filter-input">
                </div>

                <div class="sm-log-filter-group sm-log-filter-group--search">
                    <label for="filter-log-search">Búsqueda rápida:</label>
                    <input type="text" id="filter-log-search" class="sm-log-filter-input" placeholder="Buscar por pieza, acción o detalle...">
                </div>

                <div class="sm-log-filter-group sm-log-filter-group--btn">
                    <label>&nbsp;</label>
                    <button type="button" id="btn-reset-log-filter" class="sm-btn-bulk sm-btn-bulk--none" title="Limpiar filtros">
                        Limpiar
                    </button>
                </div>
            </div>

            {{-- Tabla Scrolleable al 100% de Ancho --}}
            <div class="sm-log-scroll-wrapper">
                <table class="sm-log-table-full" id="tabla-log">
                    <thead>
                        <tr>
                            <th style="width: 4%; text-align: center;">#</th>
                            <th style="width: 14%;">OT / Clase</th>
                            <th style="width: 12%;">Acción</th>
                            <th style="width: 24%;">Elemento / Campo Modificado</th>
                            <th style="width: 15%;">Valor Anterior</th>
                            <th style="width: 15%;">Valor Nuevo</th>
                            <th style="width: 8%;">Usuario</th>
                            <th style="width: 8%; text-align: center;">Fecha y Hora</th>
                        </tr>
                    </thead>
                    <tbody id="log-tbody">
                        {{-- Llenado dinámicamente por JS --}}
                    </tbody>
                </table>
            </div>

            <p class="sm-log-empty hidden" id="log-empty">
                Sin ediciones registradas que coincidan con los filtros.
            </p>
        </div>

    </div>

</div>

{{-- Guardar ID del reporte para el JS --}}
<input type="hidden" id="reporte-id-log" value="{{ $reporte->id }}">
