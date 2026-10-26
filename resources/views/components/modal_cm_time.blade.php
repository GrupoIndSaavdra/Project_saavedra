<!-- Modal de Selección de Turno y Horario Interactivo (GIS Corporate Style) -->
<div id="modalCmTime" class="alm-modal"
    style="padding: 15px; background: rgba(0, 0, 0, 0.65); -webkit-backdrop-filter: blur(8px); backdrop-filter: blur(8px); display: none; align-items: center; justify-content: center; z-index: 999999 !important; position: fixed; inset: 0;">
    <div class="cm-time-modal-card"
        style="background: #ffffff; padding: 30px 42px; border-radius: 26px; border: 3.5px solid var(--gis-blue, #033966); box-shadow: 0 28px 70px rgba(3, 57, 102, 0.48); width: 840px; max-width: 96vw; max-height: 94vh; overflow-y: auto; position: relative; box-sizing: border-box; font-family: 'Poppins', sans-serif;">
        
        <!-- Cabecera del modal -->
        <div
            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; border-bottom: 2.5px solid #e2e8f0; padding-bottom: 16px;">
            <h4
                style="margin: 0; color: var(--gis-blue, #033966); font-size: 1.45rem; font-family: 'Poppins', sans-serif; font-weight: 800; display: flex; align-items: center; gap: 14px;">
                <img src="{{ asset('images/iconos/Hora.png') }}" style="width: 42px; height: 42px; object-fit: contain;" alt="Hora">
                <span id="cm-time-modal-title">Turno y Horario de Producción</span>
            </h4>
            <button type="button" id="cm-time-close-btn"
                style="background: none; border: none; cursor: pointer; padding: 6px; display: flex; align-items: center; justify-content: center; border-radius: 50%; transition: transform 0.15s ease, background-color 0.15s ease;"
                onmouseover="this.style.transform='scale(1.15)'; this.style.backgroundColor='rgba(0,0,0,0.06)'"
                onmouseout="this.style.transform='scale(1)'; this.style.backgroundColor='transparent'"
                title="Cerrar">
                <img src="{{ asset('images/iconos/cerrar.png') }}" style="width: 38px; height: 38px; object-fit: contain;" alt="Cerrar">
            </button>
        </div>

        <!-- Banner de Jornada, Turno y Semana del Año (Sin emojis, iconos grandes) -->
        <div style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border: 2px solid #bfdbfe; border-radius: 16px; padding: 12px 20px; margin-bottom: 20px; display: grid; grid-template-columns: 1.2fr 1fr 1fr; gap: 14px; align-items: center; font-size: 0.92rem;">
            <!-- Jornada de Planta -->
            <div style="display: flex; align-items: center; gap: 10px;">
                <img src="{{ asset('images/iconos/Fecha-Icon.png') }}" style="width: 26px; height: 26px; object-fit: contain;" alt="Jornada">
                <div>
                    <span style="font-size: 0.76rem; font-weight: 700; color: #1e3a8a; text-transform: uppercase; display: block; line-height: 1.1;">Jornada de Planta</span>
                    <span id="cm-auto-day-badge" style="font-weight: 800; color: #1e40af; font-size: 0.95rem;">Lunes a Viernes</span>
                </div>
            </div>

            <!-- Turno Detectado -->
            <div style="display: flex; align-items: center; gap: 10px; border-left: 1.5px solid #bfdbfe; padding-left: 14px;">
                <img src="{{ asset('images/iconos/Hora.png') }}" style="width: 26px; height: 26px; object-fit: contain;" alt="Turno">
                <div>
                    <span style="font-size: 0.76rem; font-weight: 700; color: #1e3a8a; text-transform: uppercase; display: block; line-height: 1.1;">Turno del Sistema</span>
                    <span id="cm-auto-shift-badge" style="font-weight: 800; color: #0a8504; font-size: 0.95rem;">Mañana</span>
                </div>
            </div>

            <!-- Semana del Año -->
            <div style="display: flex; align-items: center; gap: 10px; border-left: 1.5px solid #bfdbfe; padding-left: 14px;">
                <img src="{{ asset('images/iconos/calendario.png') }}" style="width: 26px; height: 26px; object-fit: contain;" alt="Semana">
                <div>
                    <span style="font-size: 0.76rem; font-weight: 700; color: #1e3a8a; text-transform: uppercase; display: block; line-height: 1.1;">Semana de Trabajo</span>
                    <span id="cm-auto-week-badge" style="font-weight: 800; color: #b45309; font-size: 0.95rem;">Semana --</span>
                </div>
            </div>
        </div>

        <!-- Selección de Turnos de Planta -->
        <div style="margin-bottom: 20px;">
            <span style="font-size: 0.88rem; font-weight: 800; color: #033966; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 12px;">1. Selecciona o Confirma el Turno</span>
            <div id="cm-time-shifts-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px;">
                <!-- Turno Mañana -->
                <div class="cm-time-shift-card is-selected" id="shift-card-manana" data-shift="manana" style="padding: 16px 20px; min-height: 95px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <img src="{{ asset('images/iconos/Turno_Manana.png') }}" style="width: 42px; height: 42px; object-fit: contain;" alt="Mañana">
                        <span style="font-size: 1.18rem; font-weight: 800; color: #033966;">Mañana</span>
                    </div>
                    <span class="cm-shift-range" style="font-size: 0.95rem; font-weight: 700; color: #475569;">06:00 a 14:00</span>
                    <span class="cm-shift-badge-active" style="font-size: 0.78rem; font-weight: 800; color: #0a8504; text-transform: uppercase;">Activo</span>
                </div>

                <!-- Turno Mixto -->
                <div class="cm-time-shift-card" id="shift-card-mixto" data-shift="mixto" style="padding: 16px 20px; min-height: 95px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <img src="{{ asset('images/iconos/Turno_Mixto.png') }}" style="width: 42px; height: 42px; object-fit: contain;" alt="Mixto">
                        <span style="font-size: 1.18rem; font-weight: 800; color: #033966;">Mixto</span>
                    </div>
                    <span class="cm-shift-range" id="cm-mixto-range-text" style="font-size: 0.95rem; font-weight: 700; color: #475569;">13:45 a 21:45</span>
                    <span class="cm-shift-badge-active" style="display: none; font-size: 0.78rem; font-weight: 800; color: #0a8504; text-transform: uppercase;">Activo</span>
                </div>

                <!-- Turno Noche -->
                <div class="cm-time-shift-card" id="shift-card-noche" data-shift="noche" style="padding: 16px 20px; min-height: 95px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <img src="{{ asset('images/iconos/Turno_Noche.png') }}" style="width: 42px; height: 42px; object-fit: contain;" alt="Noche">
                        <span style="font-size: 1.18rem; font-weight: 800; color: #033966;">Noche</span>
                    </div>
                    <span class="cm-shift-range" id="cm-noche-range-text" style="font-size: 0.95rem; font-weight: 700; color: #475569;">22:00 a 06:00</span>
                    <span class="cm-shift-lock-label" style="display: none; font-size: 0.78rem; font-weight: 800; color: #dc2626;">
                        <img src="{{ asset('images/iconos/bloqueado.png') }}" style="width: 17px; height: 17px; vertical-align: middle; margin-right: 4px;" alt="Bloqueado">Bloqueado
                    </span>
                </div>
            </div>
        </div>

        <!-- Pestañas para Editar Hora de Inicio vs Hora de Término -->
        <div style="margin-bottom: 18px;">
            <span style="font-size: 0.88rem; font-weight: 800; color: #033966; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 12px;">2. Horas de Entrada y Salida (Ajuste fino o manual)</span>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <!-- Tab Hora de Inicio -->
                <button type="button" id="cm-tab-start" class="cm-time-target-tab is-active" title="Clic para ajustar hora de inicio" style="padding: 14px 22px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <img src="{{ asset('images/iconos/Hora.png') }}" style="width: 22px; height: 22px; object-fit: contain;" alt="Inicio">
                        <span style="font-size: 0.88rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em;">Hora de Inicio</span>
                    </div>
                    <span id="cm-tab-start-val" style="font-size: 1.6rem; font-weight: 800;">06:00</span>
                </button>
                <!-- Tab Hora de Término -->
                <button type="button" id="cm-tab-end" class="cm-time-target-tab" title="Clic para ajustar hora de término" style="padding: 14px 22px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <img src="{{ asset('images/iconos/Hora.png') }}" style="width: 22px; height: 22px; object-fit: contain;" alt="Término">
                        <span style="font-size: 0.88rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em;">Hora de Término</span>
                    </div>
                    <span id="cm-tab-end-val" style="font-size: 1.6rem; font-weight: 800;">14:00</span>
                </button>
            </div>
        </div>

        <!-- Reloj Digital Interactivo (Permite escribir directamente la hora) -->
        <div style="background: linear-gradient(135deg, #f0f7ff 0%, #e8f0fc 100%); border: 2px solid rgba(3, 57, 102, 0.22); border-radius: 20px; padding: 20px 28px; margin-bottom: 18px; box-shadow: inset 0 2px 6px rgba(0,0,0,0.04);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span id="cm-active-field-indicator" style="font-size: 0.90rem; font-weight: 800; color: #033966; text-transform: uppercase; letter-spacing: 0.04em;">Ajustando: Hora de Inicio</span>
                    <span style="font-size: 0.78rem; font-weight: 700; color: #0284c7; background: #e0f2fe; padding: 2px 8px; border-radius: 6px;">(Escribe o usa flechas)</span>
                </div>
                <!-- Switch 12H vs 24H -->
                <div class="cm-time-mode-switch" style="display: inline-flex; background: #e2e8f0; border-radius: 10px; padding: 4px;">
                    <button type="button" id="cm-time-mode-12" class="cm-time-mode-btn is-active" style="padding: 6px 16px; font-size: 0.90rem;">12H</button>
                    <button type="button" id="cm-time-mode-24" class="cm-time-mode-btn" style="padding: 6px 16px; font-size: 0.90rem;">24H</button>
                </div>
            </div>

            <div style="display: flex; align-items: center; justify-content: center; gap: 20px;">
                <!-- Columna Horas con Input Directo y Flechas -->
                <div style="display: flex; flex-direction: column; align-items: center; gap: 6px;">
                    <button type="button" id="cm-time-hour-up" class="cm-time-arrow-btn" title="Incrementar hora" style="width: 54px; height: 38px;">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"></polyline></svg>
                    </button>
                    <input type="text" id="cm-time-display-hour" maxlength="2" inputmode="numeric"
                        title="Haz clic para escribir la hora directamente"
                        style="font-size: 3.8rem; font-weight: 800; color: #033966; line-height: 1; width: 110px; height: 82px; text-align: center; border: 2.5px solid #cbd5e1; border-radius: 14px; background: #ffffff; box-shadow: 0 4px 10px rgba(0,0,0,0.06); font-variant-numeric: tabular-nums; outline: none; transition: border-color 0.2s;"
                        onfocus="this.select(); this.style.borderColor='#0284c7';" onblur="this.style.borderColor='#cbd5e1';"
                        value="06">
                    <button type="button" id="cm-time-hour-down" class="cm-time-arrow-btn" title="Disminuir hora" style="width: 54px; height: 38px;">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    </button>
                </div>

                <!-- Separador de dos puntos -->
                <div style="font-size: 3.8rem; font-weight: 800; color: #033966; line-height: 1; margin-bottom: 24px; -webkit-user-select: none; user-select: none;">
                    :
                </div>

                <!-- Columna Minutos con Input Directo y Flechas -->
                <div style="display: flex; flex-direction: column; align-items: center; gap: 6px;">
                    <button type="button" id="cm-time-min-up" class="cm-time-arrow-btn" title="Incrementar minutos" style="width: 54px; height: 38px;">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"></polyline></svg>
                    </button>
                    <input type="text" id="cm-time-display-min" maxlength="2" inputmode="numeric"
                        title="Haz clic para escribir los minutos directamente"
                        style="font-size: 3.8rem; font-weight: 800; color: #033966; line-height: 1; width: 110px; height: 82px; text-align: center; border: 2.5px solid #cbd5e1; border-radius: 14px; background: #ffffff; box-shadow: 0 4px 10px rgba(0,0,0,0.06); font-variant-numeric: tabular-nums; outline: none; transition: border-color 0.2s;"
                        onfocus="this.select(); this.style.borderColor='#0284c7';" onblur="this.style.borderColor='#cbd5e1';"
                        value="00">
                    <button type="button" id="cm-time-min-down" class="cm-time-arrow-btn" title="Disminuir minutos" style="width: 54px; height: 38px;">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    </button>
                </div>

                <!-- Selector AM / PM -->
                <div id="cm-time-ampm-container" style="display: flex; flex-direction: column; gap: 8px; justify-content: center; margin-left: 12px;">
                    <button type="button" id="cm-time-btn-am" class="cm-time-period-btn is-active" style="padding: 10px 22px; font-size: 1.05rem; font-weight: 800;">AM</button>
                    <button type="button" id="cm-time-btn-pm" class="cm-time-period-btn" style="padding: 10px 22px; font-size: 1.05rem; font-weight: 800;">PM</button>
                    <span id="cm-time-24h-badge" style="display: none; font-size: 0.85rem; font-weight: 800; color: #033966; background: #e0f2fe; padding: 6px 12px; border-radius: 8px;">24H</span>
                </div>
            </div>
        </div>

        <!-- Atajos de Minutos Rápidos -->
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 22px;">
            <button type="button" class="cm-time-chip" data-min="00" style="padding: 12px 0; font-size: 1.15rem;">:00</button>
            <button type="button" class="cm-time-chip" data-min="15" style="padding: 12px 0; font-size: 1.15rem;">:15</button>
            <button type="button" class="cm-time-chip" data-min="30" style="padding: 12px 0; font-size: 1.15rem;">:30</button>
            <button type="button" class="cm-time-chip" data-min="45" style="padding: 12px 0; font-size: 1.15rem;">:45</button>
        </div>

        <!-- Botones de Acción -->
        <div style="display: flex; justify-content: flex-end; align-items: center; gap: 14px; border-top: 2.5px solid #e2e8f0; padding-top: 18px;">
            <button type="button" id="cm-time-cancel-btn"
                style="background: #f1f5f9; color: #475569; border: 2px solid #cbd5e1; border-radius: 14px; font-weight: 700; font-size: 1.05rem; padding: 12px 26px; cursor: pointer; transition: all 0.2s;"
                onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">
                Cancelar
            </button>
            <button type="button" id="cm-time-confirm-btn"
                style="background: linear-gradient(135deg, var(--gis-green, #0a8504) 0%, #075e03 100%); color: #ffffff; border: none; border-radius: 14px; font-weight: 800; font-size: 1.08rem; padding: 13px 34px; cursor: pointer; box-shadow: 0 4px 16px rgba(10, 133, 4, 0.38); display: flex; align-items: center; gap: 10px; transition: all 0.2s;"
                onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 6px 20px rgba(10, 133, 4, 0.48)'"
                onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 16px rgba(10, 133, 4, 0.38)'">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                Confirmar Turno y Horario
            </button>
        </div>
    </div>
</div>
