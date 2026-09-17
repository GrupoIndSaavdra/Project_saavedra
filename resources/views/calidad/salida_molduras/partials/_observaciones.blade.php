{{--
    Partial: _observaciones.blade.php
    Cuadro de observaciones generales del reporte.
    - Siempre visible (independiente del formato)
    - Máximo 250 caracteres
    - Autoguardado con debounce

    Variables requeridas:
        $reporte → SalidaMoldura
--}}
<div class="sm-obs-container">
    <div class="sm-obs-header">
        <h3 class="sm-obs-title">Observaciones:</h3>
        <span class="sm-obs-counter">
            <span id="obs-chars-used">{{ strlen($reporte->observaciones ?? '') }}</span>/250
        </span>
    </div>
    <textarea
        id="campo-observaciones"
        class="sm-obs-textarea"
        maxlength="250"
        rows="4"
        placeholder="Escribe aquí las observaciones generales del reporte..."
        data-reporte-id="{{ $reporte->id }}"
        {{ $reporte->enviado ? 'disabled' : '' }}
    >{{ $reporte->observaciones ?? '' }}</textarea>
    <div class="sm-obs-footer">
        <span class="sm-obs-status" id="obs-status"></span>
    </div>
</div>
