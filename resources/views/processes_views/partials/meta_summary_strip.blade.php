{{-- Barra de Resumen en Modo Foco en Tabla (Grupo Industrial Saavedra) --}}
<div class="meta-summary-strip" id="metaSummaryStrip">
    {{-- Fila 1: Contexto de Producción (OT, Proceso, Máquina, Operador) --}}
    <div class="summary-strip-row summary-strip-row-info">
        <div class="summary-chip summary-chip-ot" title="Orden de Trabajo">
            <img src="{{ asset('images/Light_Icons/OT-Light.png') }}" class="summary-chip-icon" alt="OT">
            <span class="summary-chip-label">OT:</span>
            <span class="summary-chip-value">{{ $arrayData['workOrder'] ?? '---' }}</span>
        </div>
        <div class="summary-chip summary-chip-process" title="Proceso y Clase">
            <img src="{{ asset('images/Light_Icons/Tornilleria-Light.png') }}" class="summary-chip-icon" alt="Proceso">
            <span class="summary-chip-label">PROCESO:</span>
            <span class="summary-chip-value">{{ $arrayData['process'] ?? '' }} ({{ $arrayData['class'] ?? '' }})</span>
        </div>
        <div class="summary-chip summary-chip-machine" title="Máquina Asignada">
            <img src="{{ asset('images/Light_Icons/Maquina-Light.png') }}" class="summary-chip-icon" alt="Máquina">
            <span class="summary-chip-label">MÁQ:</span>
            <span class="summary-chip-value">{{ $arrayData['machine'] ?? '---' }}</span>
        </div>
        <div class="summary-chip summary-chip-operator" title="Operador en Turno">
            <img src="{{ asset('images/Light_Icons/Operador-Light.png') }}" class="summary-chip-icon" alt="Operador">
            <span class="summary-chip-value">{{ auth()->user()->a_paterno . ' ' . auth()->user()->nombre }}</span>
        </div>
    </div>

    {{-- Fila 2: Métricas de Producción (Meta, Realizadas, Restantes) --}}
    <div class="summary-strip-row summary-strip-row-metrics">
        <div class="summary-chip summary-chip-meta" title="Meta de Producción">
            <img src="{{ asset('images/Light_Icons/Meta-Light.png') }}" class="summary-chip-icon" alt="Meta">
            <span class="summary-chip-label">META:</span>
            <span class="summary-chip-value">{{ $arrayData['meta']->meta ?? 0 }} pcs</span>
        </div>
        <div class="summary-chip summary-chip-result" title="Piezas Realizadas">
            <img src="{{ asset('images/Light_Icons/Resultado-Light.png') }}" class="summary-chip-icon" alt="Resultado">
            <span class="summary-chip-label">REALIZADAS:</span>
            <span class="summary-chip-value">{{ $arrayData['meta']->resultado ?? 0 }} pcs</span>
        </div>
        <div class="summary-chip summary-chip-remaining" title="Piezas Restantes">
            <img src="{{ asset('images/Light_Icons/Tornilleria-Light.png') }}" class="summary-chip-icon" alt="Restantes">
            <span class="summary-chip-label">RESTANTES:</span>
            <span class="summary-chip-value">{{ $arrayData['remainingPieces'] ?? 0 }} pcs</span>
        </div>
    </div>
</div>
