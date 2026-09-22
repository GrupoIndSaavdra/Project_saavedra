{{--
    Partial: _log_panel.blade.php
    Panel colapsable con el historial de auditoría de ediciones del reporte.
    Carga el log bajo demanda (AJAX) para no saturar la carga inicial de la página.

    Variables requeridas:
        $reporte → SalidaMoldura
--}}
<div class="ri-log-panel" id="log-panel">

    <button class="ri-log-toggle" id="btn-toggle-log" aria-expanded="false">
        <span class="ri-log-toggle__text">Historial de Ediciones y Auditoría</span>
        <span class="ri-log-toggle__arrow" id="log-arrow">&#9660;</span>
    </button>

    <div class="ri-log-body hidden" id="log-body">

        <div class="ri-log-loading hidden" id="log-loading">
            <div class="spinner"></div>
            <span>Cargando historial de ediciones...</span>
        </div>

        <div class="ri-log-content hidden" id="log-content">
            {{-- Barra de Filtros --}}
            <div class="ri-log-filters">
                <div class="ri-log-filter-group">
                    <label for="filter-log-usuario">Usuario:</label>
                    <select id="filter-log-usuario" class="ri-log-filter-input">
                        <option value="">Todos los usuarios</option>
                    </select>
                </div>

                <div class="ri-log-filter-group">
                    <label for="filter-log-desde">Desde:</label>
                    <input type="date" id="filter-log-desde" class="ri-log-filter-input">
                </div>

                <div class="ri-log-filter-group">
                    <label for="filter-log-hasta">Hasta:</label>
                    <input type="date" id="filter-log-hasta" class="ri-log-filter-input">
                </div>

                <div class="ri-log-filter-group ri-log-filter-group--search">
                    <label for="filter-log-search">Búsqueda rápida:</label>
                    <input type="text" id="filter-log-search" class="ri-log-filter-input" placeholder="Buscar por pieza, acción o detalle...">
                </div>

                <div class="ri-log-filter-group ri-log-filter-group--btn">
                    <label>&nbsp;</label>
                    <button type="button" id="btn-reset-log-filter" class="ri-btn-bulk ri-btn-bulk--none" title="Limpiar filtros">
                        Limpiar
                    </button>
                </div>
            </div>

            {{-- Tabla Scrolleable al 100% de Ancho --}}
            <div class="ri-log-scroll-wrapper">
                <table class="ri-log-table-full" id="tabla-log">
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

            <p class="ri-log-empty hidden" id="log-empty">
                Sin ediciones registradas que coincidan con los filtros.
            </p>
        </div>

    </div>

</div>

{{-- Guardar ID del reporte para el JS --}}
<input type="hidden" id="reporte-id-log" value="{{ $reporte->id }}">
