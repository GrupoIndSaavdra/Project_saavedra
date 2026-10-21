<!-- Modal Interactivo de Selección de Máquina (GIS Corporate Style) -->
<div id="modalCmMachine" class="alm-modal"
    style="padding: 15px; background: rgba(0, 0, 0, 0.65); -webkit-backdrop-filter: blur(8px); backdrop-filter: blur(8px); display: none; align-items: center; justify-content: center; z-index: 999999 !important; position: fixed; inset: 0;">
    <div class="cm-machine-modal-card"
        style="background: #ffffff; padding: 28px 36px; border-radius: 26px; border: 3.5px solid var(--gis-blue, #033966); box-shadow: 0 28px 70px rgba(3, 57, 102, 0.48); width: 1060px; max-width: 96vw; max-height: 94vh; display: flex; flex-direction: column; position: relative; box-sizing: border-box; font-family: 'Poppins', sans-serif;">

        <!-- Cabecera del modal -->
        <div
            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; border-bottom: 2.5px solid #e2e8f0; padding-bottom: 14px; flex-shrink: 0;">
            <h4
                style="margin: 0; color: var(--gis-blue, #033966); font-size: 1.52rem; font-family: 'Poppins', sans-serif; font-weight: 800; display: flex; align-items: center; gap: 14px;">
                <img src="{{ asset('images/ProgramsCNC.png') }}" style="width: 42px; height: 42px; object-fit: contain;" alt="Máquina">
                <span id="cm-machine-modal-title">Seleccionar Máquina de Trabajo</span>
            </h4>
            <button type="button" id="cm-machine-close-btn"
                style="background: none; border: none; cursor: pointer; padding: 6px; display: flex; align-items: center; justify-content: center; border-radius: 50%; transition: transform 0.15s ease, background-color 0.15s ease;"
                onmouseover="this.style.transform='scale(1.15)'; this.style.backgroundColor='rgba(0,0,0,0.06)'"
                onmouseout="this.style.transform='scale(1)'; this.style.backgroundColor='transparent'"
                title="Cerrar">
                <img src="{{ asset('images/cerrar.png') }}" style="width: 38px; height: 38px; object-fit: contain;" alt="Cerrar">
            </button>
        </div>

        <!-- Barra de Búsqueda Dinámica y Filtros Rápidos -->
        <div style="margin-bottom: 16px; flex-shrink: 0; display: flex; flex-direction: column; gap: 12px;">
            <div style="position: relative; display: flex; align-items: center;">
                <img src="{{ asset('images/ProgramsCNC.png') }}"
                    style="position: absolute; left: 16px; width: 24px; height: 24px; object-fit: contain; opacity: 0.8; pointer-events: none;" alt="Buscar">
                <input type="text" id="cm-machine-search"
                    placeholder="Escribe el número de máquina o nombre (ej: 32, 5, doble, 1 y 2)..."
                    autocomplete="off"
                    style="width: 100%; padding: 13px 46px 13px 52px; border: 2.5px solid #cbd5e1; border-radius: 14px; font-size: 1.05rem; font-weight: 700; color: #033966; outline: none; transition: all 0.2s; box-shadow: inset 0 2px 4px rgba(0,0,0,0.03);"
                    onfocus="this.style.borderColor='var(--gis-blue, #033966)'; this.style.boxShadow='0 0 0 3px rgba(3, 57, 102, 0.15)';"
                    onblur="this.style.borderColor='#cbd5e1'; this.style.boxShadow='inset 0 2px 4px rgba(0,0,0,0.03)';">
                <button type="button" id="cm-machine-search-clear"
                    style="display: none; position: absolute; right: 14px; background: #e2e8f0; border: none; border-radius: 50%; width: 26px; height: 26px; font-weight: bold; color: #475569; cursor: pointer; align-items: center; justify-content: center; font-size: 15px; line-height: 1;">
                    &times;
                </button>
            </div>

            <!-- Tabs de rango / categoría para filtrado express -->
            <div id="cm-machine-tabs" style="display: flex; gap: 10px; flex-wrap: wrap;">
                <button type="button" class="cm-mach-tab is-active" data-filter="all" style="font-size: 0.92rem; padding: 7px 16px; border-radius: 10px;">Todas (49)</button>
                <button type="button" class="cm-mach-tab is-paired" data-filter="paired" style="font-size: 0.92rem; padding: 7px 16px; border-radius: 10px;">
                    <img src="{{ asset('images/ProgramsCNC.png') }}" style="width: 20px; height: 20px; object-fit: contain; vertical-align: middle; margin-right: 6px;" alt="Dual">
                    Máquinas Duales (4)
                </button>
                <button type="button" class="cm-mach-tab" data-filter="range_1_15" style="font-size: 0.92rem; padding: 7px 16px; border-radius: 10px;">M-01 al M-15</button>
                <button type="button" class="cm-mach-tab" data-filter="range_16_30" style="font-size: 0.92rem; padding: 7px 16px; border-radius: 10px;">M-16 al M-30</button>
                <button type="button" class="cm-mach-tab" data-filter="range_31_45" style="font-size: 0.92rem; padding: 7px 16px; border-radius: 10px;">M-31 al M-45</button>
            </div>
        </div>

        <!-- Contenedor scrollable de Tarjetas de Máquina -->
        <div id="cm-machine-scroll-area"
            style="flex: 1; overflow-y: auto; padding: 10px 8px 16px 8px; max-height: 56vh; border: 2px solid #e2e8f0; border-radius: 18px; background: #f8fafc;">
            <div id="cm-machine-grid"
                style="display: grid; grid-template-columns: repeat(auto-fill, minmax(152px, 1fr)); gap: 14px;">
                <!-- Tarjetas renderizadas dinámicamente por modalCmMachine.js -->
            </div>
            <div id="cm-machine-no-results"
                style="display: none; text-align: center; padding: 45px 20px; color: #64748b;">
                <img src="{{ asset('images/personError.png') }}" style="width: 52px; height: 52px; opacity: 0.5; margin-bottom: 10px;" alt="Sin resultados">
                <p style="margin: 0; font-size: 1.05rem; font-weight: 700; color: #64748b; font-style: normal;">No se encontró ninguna máquina con ese criterio.</p>
                <span style="font-size: 0.90rem; color: #94a3b8;">Prueba buscando por número (ej: 5, 25) o por "doble".</span>
            </div>
        </div>

        <!-- Barra inferior de Selección y Confirmación -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 18px; border-top: 2.5px solid #e2e8f0; padding-top: 16px; flex-shrink: 0; flex-wrap: wrap; gap: 14px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <span style="font-size: 0.90rem; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.04em;">Selección actual:</span>
                <div id="cm-machine-preview-badge"
                    style="background: #e0f2fe; border: 1.5px solid #93c5fd; padding: 8px 20px; border-radius: 12px; font-weight: 800; font-size: 1.06rem; color: #033966; display: flex; align-items: center; gap: 10px;">
                    <img src="{{ asset('images/ProgramsCNC.png') }}" style="width: 22px; height: 22px; object-fit: contain;" alt="Máquina">
                    <span id="cm-machine-selected-text">Ninguna seleccionada</span>
                </div>
            </div>

            <div style="display: flex; gap: 12px; align-items: center;">
                <button type="button" id="cm-machine-cancel-btn"
                    style="background: #f1f5f9; color: #475569; border: 2px solid #cbd5e1; border-radius: 14px; font-weight: 700; font-size: 1rem; padding: 11px 24px; cursor: pointer; transition: all 0.2s;"
                    onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">
                    Cancelar
                </button>
                <button type="button" id="cm-machine-confirm-btn" disabled
                    style="background: linear-gradient(135deg, var(--gis-green, #0a8504) 0%, #075e03 100%); color: #ffffff; border: none; border-radius: 14px; font-weight: 800; font-size: 1.04rem; padding: 12px 28px; cursor: not-allowed; opacity: 0.5; box-shadow: 0 4px 14px rgba(10, 133, 4, 0.25); display: flex; align-items: center; gap: 8px; transition: all 0.2s;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    <span>Confirmar Máquina</span>
                </button>
            </div>
        </div>

    </div>
</div>

<script>
    window.occupiedMachines = @json((new \App\Http\Controllers\MachinesController)->machinesOccupied());
</script>
