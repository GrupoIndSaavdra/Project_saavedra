{{-- Botones de acción contextual en la cabecera (Header Izquierdo) --}}
@if (!empty($arrayData['edit']) && $arrayData['edit'] == 1)
    <a href="#" class="btn-cancel-report">
        <img src="{{ asset('images/Cancelar-Icon.png') }}" class="cancel-report-icon" alt="Cancelar">
        <span>Cancelar</span>
    </a>
@else
    <button class="btn-finishReport">
        <img src="{{ asset('images/Terminar-Repo-Icon.png') }}" class="finish-report-icon" alt="Terminar reporte">
        <span>Terminar reporte</span>
    </button>
@endif
