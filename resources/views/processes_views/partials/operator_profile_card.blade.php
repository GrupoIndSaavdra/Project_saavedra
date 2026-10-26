{{-- Panel de Datos del Operador y Código del Formato --}}
<div class="div-table-code">
    <div class="panel-section-header">
        <img src="{{ asset('images/usuarios/Operador-Icon.png') }}" class="panel-header-icon" alt="Operador">
        <span>Datos del Operador</span>
        <img src="{{ asset('images/usuarios/Operador-Icon.png') }}" class="panel-header-icon" alt="Operador">
    </div>

    <div class="operator-profile-card">
        <div class="operator-avatar-wrapper">
            <img src="{{ asset('images/usuarios/Operador-Profile-Icon.png') }}" alt="Operador" class="operator-avatar-img">
        </div>
        <div class="operator-info-content">
            <div class="operator-meta-badges">
                <span class="operator-id-badge">
                    <img src="{{ asset('images/usuarios/Operador-Icon.png') }}" alt="Matrícula">
                    <span>Matrícula: {{ auth()->user()->matricula }}</span>
                </span>
                <span class="operator-status-badge">
                    <span class="status-pulse-dot"></span>
                    <span>Activo</span>
                </span>
            </div>
            <div class="operator-fullname-box">
                <span class="operator-name-text">{{ auth()->user()->a_paterno . ' ' . auth()->user()->a_materno . ' ' . auth()->user()->nombre }}</span>
            </div>
        </div>
        {{-- Input oculto para compatibilidad con telemetría de processProduction.js --}}
        <div class="operator-name" style="display: none;">
            <input type="hidden" value="{{ auth()->user()->matricula . ' - ' . auth()->user()->a_paterno . ' ' . auth()->user()->a_materno . ' ' . auth()->user()->nombre }}">
        </div>
    </div>

    {{-- El botón de documentación técnica se inyecta vía JS en insertProductionActions --}}

    <table class="table-code">
        <tr>
            <th>
                <div class="table-code-th-content">
                    <img src="{{ asset('images/iconos/Codigo-Icon.png') }}" class="table-code-icon" alt="Código">
                    <span>Código</span>
                </div>
            </th>
            <th>F- PRO - CPT</th>
        </tr>
        <tr>
            <th>
                <div class="table-code-th-content">
                    <img src="{{ asset('images/iconos/Version-Icon.png') }}" class="table-code-icon" alt="Versión">
                    <span>Versión</span>
                </div>
            </th>
            <th>05</th>
        </tr>
        <tr>
            <th>
                <div class="table-code-th-content">
                    <img src="{{ asset('images/iconos/Revision-Icon.png') }}" class="table-code-icon" alt="Revisión">
                    <span>Revisión</span>
                </div>
            </th>
            <th>23 - Agosto - 23</th>
        </tr>
    </table>
</div>
