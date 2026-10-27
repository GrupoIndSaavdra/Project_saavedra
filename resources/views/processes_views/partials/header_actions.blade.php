{{-- Botones de acción contextual en la cabecera (Header Izquierdo) --}}
<div class="header-actions-group">
    @if (!empty($arrayData['edit']) && $arrayData['edit'] == 1)
        <a href="#" class="btn-cancel-report">
            <img src="{{ asset('images/Light_Icons/Cancelar-Light.png') }}" class="cancel-report-icon" alt="Cancelar">
            <span>Cancelar</span>
        </a>
    @else
        <button class="btn-finishReport">
            <img src="{{ asset('images/Light_Icons/Terminar_Repo-Light.png') }}" class="finish-report-icon" alt="Terminar reporte">
            <span>Terminar reporte</span>
        </button>
    @endif

    @if (!empty($arrayData['meta']) && (empty($arrayData['edit']) || $arrayData['edit'] != 1))
        <button type="button" class="btn-toggle-focus" id="btnToggleFocus" style="display: none;" title="Más Zoom (Ampliar tabla de captura)">
            <img src="{{ asset('images/Light_Icons/Mas_Zoom-Light.png') }}" class="btn-zoom-icon" id="btnZoomIcon" alt="Más Zoom">
            <span class="btn-focus-label">Más Zoom</span>
        </button>
    @endif
</div>

