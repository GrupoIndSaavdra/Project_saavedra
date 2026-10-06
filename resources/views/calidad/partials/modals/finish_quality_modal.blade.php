<div id="modalFinalizarCalidad" class="alm-modal" role="dialog" aria-modal="true" style="padding: 0;">

    <div id="finalizar-calidad-modal-content" class="alm-modal-content cal-border-radius-20px cal-overflow-hidden"
        style="max-width: 1530px; width: 97vw; max-height: 96vh; height: 95vh; display: flex; flex-direction: column; margin: auto;">

        <div id="finalizar-calidad-header"
            class="alm-modal-header cal-border-top-left-radius-18px cal-border-top-right-radius-18px cal-position-relative"
            style="padding: 0.9em 2.2em;">
            <div class="div-cerrar">
                <button type="button" class="btn-cerrar" onclick="cerrarModalFinalizarCalidad()">
                    <img src="{{ asset('images/cerrar.png') }}" alt="Cerrar" class="img-cerrar"
                        style="width: 42px !important; height: 42px !important;" />
                </button>
            </div>
            <div style="display: flex; align-items: center; gap: 28px;">
                <img src="{{ asset('images/enviando.png') }}"
                    style="width: 52px !important; height: 52px !important; max-width: 52px !important; max-height: 52px !important; object-fit: contain; flex-shrink: 0;"
                    alt="">
                <div>
                    <h3 id="finalizar-calidad-title"
                        class="cal-font-size-1-35em cal-margin-0 cal-font-family-quot cal-font-weight-700 cal-color-fff cal-line-height-1-3">
                        Finalizar Proceso de Calidad
                    </h3>
                    <p id="finalizar-calidad-subtitle"
                        class="lib-modal-subtitle cal-color-ffffff cal-font-size-0-88em cal-margin-top-2px cal-margin-bottom-0 cal-font-family-quot cal-font-weight-500 cal-opacity-0-9">
                    </p>
                </div>
            </div>
        </div>

        <div class="alm-modal-body cal-padding-1em-1-6em-1-2em-1-6em cal-background-fafafa cal-font-family-quot"
            style="flex: 1; display: flex; flex-direction: column; overflow: hidden; min-height: 0;">

            <form id="formFinalizarCalidad" enctype="multipart/form-data"
                style="display: flex; flex-direction: column; flex: 1; min-height: 0;"
                data-email-almacen="{{ env('EMAIL_ALMACEN', 'almacentec@grupoindsaavedra.com') }}"
                data-email-calidad="{{ env('EMAIL_CALIDAD', 'inspecciontec@grupoindsaavedra.com') }}">
                @csrf
                <input type="hidden" id="fc-ot" name="ot" />
                <input type="hidden" id="fc-decision" name="decision" />
                <input type="hidden" id="fc-tipo-modelo" name="tipo_modelo" />
                <input type="hidden" id="fc-tipos-aprobados" name="tipos_aprobados" />
                <input type="hidden" id="fc-tipos-rechazados" name="tipos_rechazados" />

                <div
                    style="display: grid; grid-template-columns: minmax(360px, 450px) 1fr; gap: 18px; align-items: stretch; flex: 1; min-height: 0;">

                    <!-- Columna Izquierda: Datos de Liberación -->
                    <div
                        style="display: flex; flex-direction: column; gap: 6px; min-height: 0; overflow: visible; padding-right: 6px;">

                        <div
                            style="background: #fff; padding: 10px 14px; border-radius: 14px; border: 1px solid #e2e8f0; box-shadow: 0 4px 10px rgba(0,0,0,0.03); display: flex; flex-direction: column; justify-content: flex-start; box-sizing: border-box; flex: none; flex-shrink: 0;">
                            <h4 id="fc-left-header"
                                style="margin-top: 0; margin-bottom: 8px; color: #0284c7; font-size: 1.1em; border-bottom: 2px solid #0284c7; padding-bottom: 4px; font-weight: 700; font-family: 'Poppins', sans-serif; display: flex; align-items: center; gap: 8px;">
                                <img id="fc-left-icon" src="{{ asset('images/Quality.png') }}"
                                    style="width: 38px; height: 38px; object-fit: contain;"> <span
                                    id="fc-left-title">Datos de Liberación</span>
                            </h4>

                            <div class="form-group cal-margin-bottom-10px">
                                <label for="fc-destinatario"
                                    style="font-weight: 700; color: #334155; display: block; margin-bottom: 2px; font-size: 0.95em;">Notificar
                                    a Almacén:</label>
                                <input type="text" id="fc-destinatario" name="destinatario" class="form-control"
                                    style="font-size: 0.95em; padding: 8px 12px; height: auto;" required />
                                <span style="font-size: 0.75em; color: #64748b; margin-top: 2px; display: block;">Separa
                                    correos con comas.</span>
                            </div>

                            <div class="form-group cal-margin-bottom-10px">
                                <label for="fc-destinatario-calidad"
                                    style="font-weight: 700; color: #334155; display: block; margin-bottom: 2px; font-size: 0.95em;">Notificar
                                    a Calidad:</label>
                                <input type="text" id="fc-destinatario-calidad" name="destinatario_calidad"
                                    class="form-control" style="font-size: 0.95em; padding: 8px 12px; height: auto;"
                                    required />
                            </div>

                            <div class="form-group cal-margin-bottom-10px">
                                <label id="fc-fecha-label"
                                    style="font-weight: 700; color: #334155; display: block; margin-bottom: 2px; font-size: 0.95em;">Fecha
                                    de Finalización <span class="cal-color-dc2626">*</span>: <span id="fc-fecha-seleccionada" style="color: #0284c7; margin-left: 8px;"></span></label>
                                <input type="hidden" id="fc-fecha" name="fecha" required />
                                <div id="fc-inline-calendar" style="border: 1px solid #cbd5e1; border-radius: 10px; padding: 8px; background: #f8fafc; user-select: none;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                        <button type="button" id="fc-cal-prev" style="border: none; background: transparent; cursor: pointer; font-size: 1.2em; color: #64748b; padding: 0 5px;">&#9664;</button>
                                        <span id="fc-cal-month-year" style="font-weight: 700; color: #0f172a; font-size: 0.95em; text-transform: capitalize;"></span>
                                        <button type="button" id="fc-cal-next" style="border: none; background: transparent; cursor: pointer; font-size: 1.2em; color: #64748b; padding: 0 5px;">&#9654;</button>
                                    </div>
                                    <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 4px; text-align: center; font-size: 0.75em; font-weight: 700; color: #94a3b8; margin-bottom: 4px;">
                                        <div>Do</div><div>Lu</div><div>Ma</div><div>Mi</div><div>Ju</div><div>Vi</div><div>Sa</div>
                                    </div>
                                    <div id="fc-cal-days" style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 4px; text-align: center;">
                                    </div>
                                </div>
                            </div>

                            <div style="background: #fff8f1; border-left: 5px solid #f97316; border-radius: 8px; padding: 10px 14px; margin-top: 8px; display: flex; align-items: center; gap: 15px; box-shadow: inset 0 0 8px rgba(249, 115, 22, 0.03);">
                                <img src="{{ asset('images/Fecha.png') }}" style="width: 40px; height: 40px; object-fit: contain; flex-shrink: 0;" alt="Fecha">
                                <div style="font-family:'Poppins', sans-serif; font-weight: 500; color: #9a3412; font-size: 1.05em; line-height: 1.4;">
                                    <strong>Solo necesitas colocar la Fecha de Finalización</strong>. Los correos de notificación hacia el personal correspondiente se adjuntarán y enviarán de forma automática.
                                </div>
                            </div>
                        </div>

                        <div id="fc-prompt-text" style="flex: 1; min-height: 0; overflow-y: auto; padding-right: 4px;"></div>

                    </div>

                    <!-- Columna Derecha (AZUL ICE): Documentos en Servidor -->
                    <div
                        style="background: #f0f7ff; border: 2px solid #0284c7; padding: 14px 16px; border-radius: 14px; box-shadow: 0 4px 10px rgba(2, 132, 199, 0.08); display: flex; flex-direction: column; height: 100%; min-height: 0; box-sizing: border-box;">
                        <h4 id="fc-right-header"
                            style="margin-top: 0; margin-bottom: 6px; color: #0369a1; font-size: 1.1em; border-bottom: 2px solid #0284c7; padding-bottom: 4px; font-weight: 700; font-family: 'Poppins', sans-serif; display: flex; align-items: center; gap: 8px; flex-shrink: 0;">
                            <img id="fc-right-icon" src="{{ asset('images/galeria.png') }}"
                                style="width: 38px; height: 38px; object-fit: contain;"> <span
                                id="fc-right-title">Archivos de Liberación Disponibles</span>
                        </h4>

                        <div style="flex: 1; display: flex; flex-direction: column; min-height: 0;">
                            <div id="fc-server-files-container"
                                style="background: #f0f7ff; border: 1px solid #bae6fd; border-radius: 10px; padding: 12px; flex: 1; min-height: 0; overflow-y: auto; display: flex; flex-direction: column; gap: 10px;">
                                <div
                                    class="alm-spinner cal-border-top-color-0284c7 cal-display-block cal-margin-10px-auto">
                                </div>
                                <span class="cal-text-align-center cal-color-64748b">Cargando archivos de la
                                    OT...</span>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="cal-text-align-center"
                    style="margin: 0 !important; padding: 0 !important; border-top: none; flex-shrink: 0; display: flex; justify-content: center; align-items: center; height: fit-content !important; min-height: 0 !important;">
                    <button type="submit" id="btn-submit-finalizar-calidad" disabled
                        class="btn-save-preorden cal-font-size-1em cal-padding-10px-36px cal-border-radius-10px cal-font-weight-700"
                        style="display: inline-flex; align-items: center; justify-content: center; gap: 8px;">
                        Finalizar y Enviar Correo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const form = document.getElementById("formFinalizarCalidad");
        if (!form) return;
        const btn = document.getElementById("btn-submit-finalizar-calidad");

        // Marcar los campos que originalmente son requeridos
        const allRequired = form.querySelectorAll("input[required], select[required], textarea[required]");
        allRequired.forEach(input => input.setAttribute("data-was-required", "true"));

        function checkFinalizarValidity() {
            if (!btn) return;

            let isValid = true;
            const checkInputs = form.querySelectorAll("[data-was-required='true']");

            checkInputs.forEach(input => {
                if (input.offsetParent === null) {
                    input.removeAttribute("required"); // Evitar error nativo de HTML5 en campos ocultos
                } else {
                    input.setAttribute("required", "required");
                    if (!input.value.trim()) isValid = false;
                }
            });

            if (isValid) {
                btn.disabled = false;
                btn.style.opacity = "1";
                btn.style.cursor = "pointer";
            } else {
                btn.disabled = true;
                btn.style.opacity = "0.6";
                btn.style.cursor = "not-allowed";
            }
        }

        form.addEventListener("input", checkFinalizarValidity);
        form.addEventListener("change", checkFinalizarValidity);

        setInterval(checkFinalizarValidity, 500);

        // --- Inline Calendar Logic ---
        const monthNames = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
        let calCurrentDate = new Date();
        const hiddenInput = document.getElementById("fc-fecha");

        window.fcRenderCalendar = function(reset = false) {
            if (reset) {
                calCurrentDate = new Date();
                hiddenInput.value = "";
                hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
            }
            const year = calCurrentDate.getFullYear();
            const month = calCurrentDate.getMonth();
            const selectedDate = hiddenInput.value;
            
            document.getElementById("fc-cal-month-year").textContent = `${monthNames[month]} ${year}`;

            const labelSel = document.getElementById("fc-fecha-seleccionada");
            if (labelSel) {
                if (selectedDate) {
                    const parts = selectedDate.split('-');
                    if (parts.length === 3) {
                        labelSel.textContent = `${parts[2]}/${parts[1]}/${parts[0]}`;
                    } else {
                        labelSel.textContent = "";
                    }
                } else {
                    labelSel.textContent = "";
                }
            }
            
            const firstDay = new Date(year, month, 1).getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            
            const daysContainer = document.getElementById("fc-cal-days");
            daysContainer.innerHTML = "";
            
            for (let i = 0; i < firstDay; i++) {
                daysContainer.innerHTML += `<div></div>`;
            }
            
            const today = new Date();
            
            for (let i = 1; i <= daysInMonth; i++) {
                const dStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(i).padStart(2, '0')}`;
                
                let bg = "transparent";
                let color = "#334155";
                let fw = "500";
                let extraStyles = "border: 1px solid transparent;";
                
                if (selectedDate === dStr) {
                    bg = "#0284c7";
                    color = "#fff";
                    fw = "700";
                    extraStyles = "border: 1px solid #0284c7; box-shadow: 0 2px 5px rgba(2, 132, 199, 0.4);";
                } else if (today.getFullYear() === year && today.getMonth() === month && today.getDate() === i) {
                    color = "#0284c7";
                    fw = "700";
                    extraStyles = "border: 1px solid #bae6fd; background: #e0f2fe;";
                }
                
                daysContainer.innerHTML += `
                    <div class="fc-cal-day" data-date="${dStr}" style="padding: 4px 0; border-radius: 6px; cursor: pointer; background: ${bg}; color: ${color}; font-weight: ${fw}; font-size: 0.85em; transition: all 0.2s; ${extraStyles}" 
                         onmouseover="if(this.dataset.date !== '${selectedDate}') this.style.background='#e2e8f0';" 
                         onmouseout="if(this.dataset.date !== '${selectedDate}') this.style.background='${today.getFullYear() === year && today.getMonth() === month && today.getDate() === i ? '#e0f2fe' : 'transparent'}';">
                        ${i}
                    </div>
                `;
            }
            
            document.querySelectorAll(".fc-cal-day").forEach(el => {
                el.addEventListener("click", function() {
                    hiddenInput.value = this.dataset.date;
                    hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
                    hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
                    window.fcRenderCalendar(false);
                });
            });
        };

        document.getElementById("fc-cal-prev").addEventListener("click", () => {
            calCurrentDate.setMonth(calCurrentDate.getMonth() - 1);
            window.fcRenderCalendar(false);
        });
        document.getElementById("fc-cal-next").addEventListener("click", () => {
            calCurrentDate.setMonth(calCurrentDate.getMonth() + 1);
            window.fcRenderCalendar(false);
        });

        // Initialize calendar
        window.fcRenderCalendar(false);
    });
</script>
