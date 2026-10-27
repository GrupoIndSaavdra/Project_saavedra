<!-- Modal de Selección de Fecha con Calendario Interactivo -->
<div id="modalCmCalendar" class="alm-modal"
    style="padding: 0px; background: rgba(0, 0, 0, 0.5); display: none; align-items: center; justify-content: center; z-index: 999999 !important; position: fixed; inset: 0;">
    <div
        style="background: #fff; padding: 24px 30px; border-radius: 16px; border: 2px solid #0a8504; box-shadow: 0 10px 30px rgba(0,0,0,0.3); width: 450px; max-width: 95vw; position: relative; box-sizing: border-box;">
        <div
            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px;">
            <h4
                style="margin: 0; color: #0a8504; font-size: 1.15em; font-family: 'Poppins', sans-serif; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                <img src="{{ asset('images/Light_Icons/Fecha-Light.png') }}" style="width: 26px; height: 26px; object-fit: contain;" alt="Fecha">
                <span id="cm-modal-title">Seleccionar Fecha</span>
            </h4>
            <button type="button" onclick="document.getElementById('modalCmCalendar').style.display='none'"
                style="background: none; border: none; cursor: pointer; padding: 6px; display: flex; align-items: center; justify-content: center; border-radius: 50%; transition: transform 0.15s ease, background-color 0.15s ease;"
                onmouseover="this.style.transform='scale(1.15)'; this.style.backgroundColor='rgba(0,0,0,0.06)'"
                onmouseout="this.style.transform='scale(1)'; this.style.backgroundColor='transparent'"
                title="Cerrar">
                <img src="{{ asset('images/Color_Icons/Generales/cerrar.png') }}" style="width: 38px; height: 38px; object-fit: contain;" alt="Cerrar">
            </button>
        </div>
        <div id="cm-inline-calendar" style="user-select: none;">
            <div
                style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; padding: 0 4px;">
                <button type="button" id="cm-cal-prev"
                    style="border: none; background: #f1f5f9; border-radius: 8px; cursor: pointer; font-size: 1.3em; color: #475569; padding: 4px 14px; transition: 0.2s;"
                    onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">&#9664;</button>
                <span id="cm-cal-month-year"
                    style="font-weight: 700; color: #0f172a; font-size: 1.2em; text-transform: capitalize;"></span>
                <button type="button" id="cm-cal-next"
                    style="border: none; background: #f1f5f9; border-radius: 8px; cursor: pointer; font-size: 1.3em; color: #475569; padding: 4px 14px; transition: 0.2s;"
                    onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">&#9654;</button>
            </div>
            <div
                style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 6px; text-align: center; font-size: 1em; font-weight: 700; color: #64748b; margin-bottom: 8px;">
                <div>Do</div>
                <div>Lu</div>
                <div>Ma</div>
                <div>Mi</div>
                <div>Ju</div>
                <div>Vi</div>
                <div>Sa</div>
            </div>
            <div id="cm-cal-days"
                style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 6px; text-align: center;"></div>
        </div>
    </div>
</div>
