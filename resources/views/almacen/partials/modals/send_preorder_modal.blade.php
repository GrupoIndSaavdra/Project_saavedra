<div id="modalEnviarPreOrden" class="alm-modal" role="dialog" aria-modal="true" style="padding: 0;">
    <div class="alm-modal-content alm-border-radius-20px alm-border-2-5px-solid-033966 alm-overflow-hidden"
        style="max-width: 1750px; width: 98vw; max-height: 97vh; height: 97vh; display: flex; flex-direction: column; margin: auto;">
        <div
            class="alm-modal-header alm-background-linear-gradient-135deg-033966-022340 alm-border-bottom-2px-solid-022340 alm-padding-0-9em-2-2em alm-position-relative">
            <div class="div-cerrar">
                <button type="button" class="btn-cerrar" onclick="cerrarModalEnviarPreOrden()">
                    <img class="img-cerrar" src="{{ asset('images/cerrar.png') }}"
                        style="width: 42px !important; height: 42px !important;">
                </button>
            </div>
            <div class="alm-display-flex alm-align-items-center" style="gap: 28px;">
                <img src="{{ asset('images/enviando.png') }}"
                    style="width: 52px !important; height: 52px !important; max-width: 52px !important; max-height: 52px !important; object-fit: contain; flex-shrink: 0;"
                    alt="">
                <div>
                    <h3
                        class="alm-color-fff alm-margin-0 alm-font-size-1-3em alm-font-weight-800 alm-font-family-Poppins-sans-serif">
                        Enviar Pre-Orden por Correo</h3>
                    <p id="env-po-modal-subtitle"
                        class="alm-color-rgba-255-255-255-0-9 alm-font-size-0-88em alm-margin-top-2px alm-font-weight-500 alm-font-family-Poppins-sans-serif alm-margin-bottom-0">
                    </p>
                </div>
            </div>
        </div>
        <div class="alm-modal-body alm-padding-1em-1-6em-1-2em-1-6em"
            style="flex: 1; display: flex; flex-direction: column; overflow: hidden; min-height: 0;">
            <form id="formEnviarPreOrden" enctype="multipart/form-data"
                style="display: flex; flex-direction: column; flex: 1; min-height: 0;"
                data-email-modelo="{{ env('EMAIL_PROVEEDOR_MODELOS', 'produccion@ssmetalf.mx,asistenteprod@ssmetalf.mx') }}"
                data-email-casting="{{ env('EMAIL_PRODUCCION_SS', 'produccion@ssmetalf.mx,laboratorio@ssmetalf.mx') }}"
                data-email-calidad="{{ env('EMAIL_CALIDAD', 'inspecciontec@grupoindsaavedra.com') }}"
                data-email-jacarandas="{{ env('EMAIL_PRODUCCION_JACARANDAS', 'ventas_jacarandas@prodigy.net.mx,requisicionestec@grupoindsaavedra.com') }}">
                <input type="hidden" id="env-ot" name="ot">
                <input type="hidden" id="env-tipo" name="tipo" value="modelo">

                <div
                    style="display: grid; grid-template-columns: minmax(360px, 1fr) minmax(600px, 1.55fr); gap: 18px; align-items: stretch; flex: 1; min-height: 0;">

                    <!-- Columna Izquierda: Información de Envío + Botón Verde de Selección -->
                    <div style="display: flex; flex-direction: column; gap: 12px; min-height: 0;">

                        <!-- Bloque 1: Formulario de Envío -->
                        <div
                            style="background: #fff; padding: 14px 16px; border-radius: 14px; border: 1px solid #e2e8f0; box-shadow: 0 4px 10px rgba(0,0,0,0.03);">
                            <h4
                                style="margin-top: 0; margin-bottom: 8px; color: #033966; font-size: 1em; border-bottom: 2px solid #033966; padding-bottom: 4px; font-weight: 700; font-family: 'Poppins', sans-serif; display: flex; align-items: center; gap: 8px;">
                                <img src="{{ asset('images/enviando.png') }}"
                                    style="width: 30px; height: 30px; object-fit: contain;"> Información del Envío
                            </h4>

                            <div style="display: grid; grid-template-columns: 1fr; gap: 10px;">
                                <div class="form-group" id="div-env-destinatario">
                                    <label for="env-destinatario"
                                        style="font-weight: 700; color: #334155; display: block; margin-bottom: 2px; font-size: 0.84em;">Enviar
                                        a Proveedor:</label>
                                    <input type="text" id="env-destinatario" name="destinatario" class="form-control"
                                        style="font-size: 0.84em; padding: 6px 10px; height: auto;" required>
                                </div>

                                <div class="form-group" id="div-env-destinatario-calidad">
                                    <label for="env-destinatario-calidad"
                                        style="font-weight: 700; color: #334155; display: block; margin-bottom: 2px; font-size: 0.84em;">Notificar
                                        a Calidad:</label>
                                    <input type="text" id="env-destinatario-calidad" name="destinatario_calidad"
                                        class="form-control" style="font-size: 0.84em; padding: 6px 10px; height: auto;"
                                        required>
                                </div>
                            </div>

                            <div class="form-group" style="margin-top: 6px;">
                                <label id="env-fecha-label"
                                    style="font-weight: 700; color: #334155; display: block; margin-bottom: 6px; font-size: 0.9em;">Fecha
                                    de Entrega acordada <span class="alm-color-dc2626">*</span>:</label>
                                <input type="hidden" id="env-fecha-entrega" name="fecha_entrega" required />
                                <button type="button"
                                    onclick="document.getElementById('modalEnvCalendar').style.display='flex'"
                                    style="display: flex; align-items: center; justify-content: center; gap: 10px; background: #fff8f1; border: 2px dashed #f97316; padding: 12px 16px; border-radius: 12px; width: 100%; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 6px rgba(249, 115, 22, 0.05);"
                                    onmouseover="this.style.background='#ffedd5'; this.style.borderColor='#ea580c';"
                                    onmouseout="this.style.background='#fff8f1'; this.style.borderColor='#f97316';">
                                    <img src="{{ asset('images/Fecha.png') }}"
                                        style="width: 26px; height: 26px; object-fit: contain;">
                                    <span id="env-fecha-seleccionada-btn"
                                        style="color: #c2410c; font-weight: 700; font-size: 1.05em; font-family: 'Poppins', sans-serif;">Seleccionar
                                        Fecha...</span>
                                </button>
                            </div>

                            <div class="form-group" style="margin-top: 6px; margin-bottom: 0;">
                                <label id="lbl-pending-preordenes"
                                    style="font-weight: 700; color: #1e293b; display: block; margin-bottom: 6px; font-size: 0.9em; display: flex; align-items: center; gap: 6px;">
                                    <img src="{{ asset('images/documento.png') }}" style="width: 16px; height: 16px;">
                                    Pre-órdenes pendientes por enviar:</label>
                                <div id="env-pending-preordenes-container"
                                    style="background: transparent; border: none; padding: 0; max-height: 300px; overflow-y: auto; overflow-x: hidden; display: flex; flex-direction: column; gap: 8px;">
                                </div>
                            </div>
                        </div>

                        <!-- Bloque 2 (VERDE): SOLO el Botón Dropzone de Selección -->
                        <div
                            style="background: #f0fdf4; border: 2px solid #16a34a; padding: 14px 16px; border-radius: 14px; box-shadow: 0 4px 10px rgba(22, 163, 74, 0.08);">
                            <h4
                                style="margin-top: 0; margin-bottom: 6px; color: #15803d; font-size: 0.96em; border-bottom: 1.5px solid #16a34a; padding-bottom: 4px; font-weight: 700; font-family: 'Poppins', sans-serif; display: flex; align-items: center; gap: 6px;">
                                <img src="{{ asset('images/anadir.png') }}"
                                    style="width: 32px; height: 32px; object-fit: contain;"> Subir Nuevos Archivos
                                Escaneados
                            </h4>

                            <p
                                style="margin-top: 0; margin-bottom: 12px; color: #166534; font-size: 0.84em; font-weight: 500;">
                                Formatos permitidos: <strong>Documentos PDF</strong> o <strong>Imágenes (JPG, PNG,
                                    JPEG)</strong>.
                            </p>

                            <div class="alm-border-radius-12px alm-margin-bottom-12px"
                                style="background-color: #ffffff; border: 1px solid #bbf7d0; overflow: hidden; box-shadow: 0 2px 4px rgba(22,163,74,0.05);">
                                <div
                                    style="background-color: #dcfce7; padding: 6px 14px; border-bottom: 1px solid #bbf7d0;">
                                    <strong
                                        style="color: #166534; font-size: 0.9em; display: flex; align-items: center; gap: 6px;">
                                        <img src="{{ asset('images/info-icon.png') }}"
                                            style="width: 30px; height: 30px; object-fit: contain;">
                                        Puntos a Revisar antes de Subir
                                    </strong>
                                </div>
                                <div
                                    style="padding: 10px 12px; display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; text-align: center;">
                                    <div
                                        style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; padding: 10px; background: #f0fdf4; border-radius: 8px; border: 1px solid #dcfce7;">
                                        <img src="{{ asset('images/folio-icon.png') }}"
                                            style="width: 40px; height: 40px; object-fit: contain;">
                                        <span
                                            style="color: #15803d; font-size: 0.85em; line-height: 1.3; font-weight: 500;"><strong>Folio
                                                Coincidente</strong><br>El #OT debe ser exacto</span>
                                    </div>
                                    <div
                                        style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; padding: 10px; background: #f0fdf4; border-radius: 8px; border: 1px solid #dcfce7;">
                                        <img src="{{ asset('images/claridad-icon.png') }}"
                                            style="width: 40px; height: 40px; object-fit: contain;">
                                        <span
                                            style="color: #15803d; font-size: 0.85em; line-height: 1.3; font-weight: 500;"><strong>Imagen
                                                Clara</strong><br>100% legible y nítida</span>
                                    </div>
                                    <div
                                        style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; padding: 10px; background: #f0fdf4; border-radius: 8px; border: 1px solid #dcfce7;">
                                        <img src="{{ asset('images/firma-icon.png') }}"
                                            style="width: 40px; height: 40px; object-fit: contain;">
                                        <span
                                            style="color: #15803d; font-size: 0.85em; line-height: 1.3; font-weight: 500;"><strong>Firmas
                                                / Sellos</strong><br>Doc. autorizado</span>
                                    </div>
                                </div>
                            </div>

                            <div class="custom-file-dropzone"
                                style="border: 2px dashed #16a34a; background: #ffffff; padding: 12px 14px; border-radius: 10px; text-align: center; cursor: pointer; position: relative;">
                                <input type="file" id="env-archivos-adicionales" name="archivos_adicionales[]"
                                    class="custom-file-input"
                                    style="position: absolute; top:0; left:0; width:100%; height:100%; opacity:0; cursor:pointer;"
                                    multiple>
                                <div class="dropzone-content"
                                    style="display: flex; align-items: center; justify-content: center; gap: 8px;">
                                    <img src="{{ asset('images/anadir.png') }}"
                                        style="width: 44px; height: 44px; object-fit: contain;">
                                    <span style="font-weight: 700; color: #15803d; font-size: 0.86em;">Arrastrar
                                        adicionales aquí (PDFs o imágenes)</span>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Columna Derecha: 2 Sub-contenedores bien diferenciados -->
                    <div
                        style="display: flex; flex-direction: column; gap: 14px; height: 100%; min-height: 0; box-sizing: border-box;">

                        <!-- Sub-contenedor 1 (AZUL ICE): Archivos y Dibujos de la OT Disponibles -->
                        <div
                            style="background: #f0f7ff; border: 2px solid #0284c7; padding: 14px 16px; border-radius: 14px; box-shadow: 0 4px 10px rgba(2, 132, 199, 0.08); flex: 1.45; display: flex; flex-direction: column; min-height: 0;">
                            <h4
                                style="margin-top: 0; margin-bottom: 6px; color: #0369a1; font-size: 1.02em; border-bottom: 2px solid #0284c7; padding-bottom: 4px; font-weight: 700; font-family: 'Poppins', sans-serif; display: flex; align-items: center; gap: 8px; flex-shrink: 0;">
                                <img src="{{ asset('images/galeria.png') }}"
                                    style="width: 30px; height: 30px; object-fit: contain;"> Archivos y Dibujos de la
                                OT Disponibles
                            </h4>

                            <div id="env-server-files-container"
                                style="background: #f0f7ff; border: 1px solid #bae6fd; border-radius: 10px; padding: 12px; flex: 1; min-height: 0; overflow-y: auto; display: flex; flex-direction: column; gap: 10px;">
                                <div
                                    class="alm-spinner alm-border-top-color-033966 alm-display-block alm-margin-10px-auto">
                                </div>
                            </div>
                        </div>

                        <!-- Sub-contenedor 2 (VERDE ESMERALDA): Nuevos Archivos Adjuntados (Coincide en color with left button) -->
                        <div
                            style="background: #f0fdf4; border: 2px solid #16a34a; padding: 14px 16px; border-radius: 14px; box-shadow: 0 4px 10px rgba(22, 163, 74, 0.08); flex: 1; display: flex; flex-direction: column; min-height: 0;">
                            <h4
                                style="margin-top: 0; margin-bottom: 6px; color: #15803d; font-size: 0.98em; border-bottom: 1.5px solid #16a34a; padding-bottom: 4px; font-weight: 700; font-family: 'Poppins', sans-serif; display: flex; align-items: center; gap: 6px; flex-shrink: 0;">
                                <img src="{{ asset('images/anadir.png') }}"
                                    style="width: 30px; height: 30px; object-fit: contain;"> Nuevos Archivos Adjuntados
                            </h4>

                            <div id="env-archivos-adicionales-list"
                                style="background: #f0fdf4; border: 1px solid #a7f3d0; border-radius: 10px; padding: 12px; flex: 1; min-height: 0; overflow-y: auto; display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-start;">
                            </div>
                        </div>

                    </div>

                </div>

                <div class="form-actions"
                    style="text-align: center; margin-top: 10px; padding-top: 8px; flex-shrink: 0;">
                    <button type="submit" id="btn-submit-envio" class="btn-save-preorden btn-hover-green-override"
                        disabled
                        style="background: linear-gradient(135deg, #033966, #022340); box-shadow: 0 4px 15px rgba(3, 57, 102, 0.35); padding: 11px 44px; border: none; border-radius: 10px; color: #fff; font-weight: 700; cursor: pointer; font-size: 1.05em; display: inline-flex; align-items: center; justify-content: center; gap: 8px;">
                        Enviar Correo con Adjuntos
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Mini Modal for Calendar -->
<div id="modalEnvCalendar" class="alm-modal"
    style="padding: 0; z-index: 999999 !important; background: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center;">
    <div
        style="background: #fff; padding: 24px 30px; border-radius: 16px; border: 2px solid #033966; box-shadow: 0 10px 30px rgba(0,0,0,0.3); width: 450px; position: relative;">
        <div
            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px;">
            <h4
                style="margin: 0; color: #033966; font-size: 1.15em; font-family: 'Poppins', sans-serif; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                <img src="{{ asset('images/Fecha.png') }}" style="width: 26px; height: 26px; object-fit: contain;">
                Seleccionar Fecha
            </h4>
            <button type="button" onclick="document.getElementById('modalEnvCalendar').style.display='none'"
                style="background: none; border: none; cursor: pointer; color: #ef4444; font-weight: bold; font-size: 1.6em; padding: 0; line-height: 1;">&times;</button>
        </div>
        <div id="env-inline-calendar" style="user-select: none;">
            <div
                style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; padding: 0 4px;">
                <button type="button" id="env-cal-prev"
                    style="border: none; background: #f1f5f9; border-radius: 8px; cursor: pointer; font-size: 1.3em; color: #475569; padding: 4px 14px; transition: 0.2s;"
                    onmouseover="this.style.background='#e2e8f0'"
                    onmouseout="this.style.background='#f1f5f9'">&#9664;</button>
                <span id="env-cal-month-year"
                    style="font-weight: 700; color: #0f172a; font-size: 1.2em; text-transform: capitalize;"></span>
                <button type="button" id="env-cal-next"
                    style="border: none; background: #f1f5f9; border-radius: 8px; cursor: pointer; font-size: 1.3em; color: #475569; padding: 4px 14px; transition: 0.2s;"
                    onmouseover="this.style.background='#e2e8f0'"
                    onmouseout="this.style.background='#f1f5f9'">&#9654;</button>
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
            <div id="env-cal-days"
                style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 6px; text-align: center;">
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const form = document.getElementById("formEnviarPreOrden");
        if (!form) return;
        const btn = document.getElementById("btn-submit-envio");

        // Marcar los campos que originalmente son requeridos
        const allRequired = form.querySelectorAll("input[required], select[required], textarea[required]");
        allRequired.forEach(input => input.setAttribute("data-was-required", "true"));

        function checkEnvioValidity() {
            if (!btn) return;

            let isValid = true;
            const checkInputs = form.querySelectorAll("[data-was-required='true']");

            checkInputs.forEach(input => {
                if (input.offsetParent === null) {
                    input.removeAttribute("required");
                } else {
                    input.setAttribute("required", "required");
                    if (!input.value.trim()) isValid = false;
                }
            });

            // Verificar que hay al menos una pre-orden seleccionada
            const pendingChecked = form.querySelectorAll('input[name="pre_orden_ids[]"]:checked').length > 0;
            if (!pendingChecked) isValid = false;

            // Verificar que el usuario haya subido obligatoriamente el archivo escaneado
            const adicionalesCount = window.adicionalesSelectedFiles ? window.adicionalesSelectedFiles.length :
                0;

            if (adicionalesCount === 0) isValid = false;

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

        form.addEventListener("input", checkEnvioValidity);
        form.addEventListener("change", checkEnvioValidity);

        // Check periodically in case files are loaded dynamically or checkmarks changed by JS
        setInterval(checkEnvioValidity, 500);

        // --- Inline Calendar Logic ---
        const monthNames = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto",
            "Septiembre", "Octubre", "Noviembre", "Diciembre"
        ];
        let envCalCurrentDate = new Date();
        const hiddenInputEnv = document.getElementById("env-fecha-entrega");

        window.envRenderCalendar = function(reset = false) {
            if (reset) {
                envCalCurrentDate = new Date();
                hiddenInputEnv.value = "";
                hiddenInputEnv.dispatchEvent(new Event('input', {
                    bubbles: true
                }));
            }
            const year = envCalCurrentDate.getFullYear();
            const month = envCalCurrentDate.getMonth();
            const selectedDate = hiddenInputEnv.value;

            document.getElementById("env-cal-month-year").textContent = `${monthNames[month]} ${year}`;

            const labelSelBtn = document.getElementById("env-fecha-seleccionada-btn");
            const btnContainer = labelSelBtn ? labelSelBtn.closest("button") : null;
            if (labelSelBtn && btnContainer) {
                if (selectedDate) {
                    const parts = selectedDate.split('-');
                    if (parts.length === 3) {
                        labelSelBtn.textContent = `Fecha Elegida: ${parts[2]}/${parts[1]}/${parts[0]}`;
                        labelSelBtn.style.color = '#15803d';
                        btnContainer.style.background = '#f0fdf4';
                        btnContainer.style.border = '2px solid #16a34a';
                        btnContainer.onmouseover = function() {
                            this.style.background = '#dcfce7';
                        };
                        btnContainer.onmouseout = function() {
                            this.style.background = '#f0fdf4';
                        };
                    } else {
                        labelSelBtn.textContent = "Seleccionar Fecha...";
                        labelSelBtn.style.color = '#c2410c';
                        btnContainer.style.background = '#fff8f1';
                        btnContainer.style.border = '2px dashed #f97316';
                        btnContainer.onmouseover = function() {
                            this.style.background = '#ffedd5';
                            this.style.borderColor = '#ea580c';
                        };
                        btnContainer.onmouseout = function() {
                            this.style.background = '#fff8f1';
                            this.style.borderColor = '#f97316';
                        };
                    }
                } else {
                    labelSelBtn.textContent = "Seleccionar Fecha...";
                    labelSelBtn.style.color = '#c2410c';
                    btnContainer.style.background = '#fff8f1';
                    btnContainer.style.border = '2px dashed #f97316';
                    btnContainer.onmouseover = function() {
                        this.style.background = '#ffedd5';
                        this.style.borderColor = '#ea580c';
                    };
                    btnContainer.onmouseout = function() {
                        this.style.background = '#fff8f1';
                        this.style.borderColor = '#f97316';
                    };
                }
            }

            const firstDay = new Date(year, month, 1).getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();

            const daysContainer = document.getElementById("env-cal-days");
            if (!daysContainer) return;
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
                    bg = "#033966";
                    color = "#fff";
                    fw = "700";
                    extraStyles = "border: 1px solid #033966; box-shadow: 0 2px 5px rgba(3, 57, 102, 0.4);";
                } else if (today.getFullYear() === year && today.getMonth() === month && today.getDate() ===
                    i) {
                    color = "#033966";
                    fw = "700";
                    extraStyles = "border: 1px solid #bae6fd; background: #e0f2fe;";
                }

                daysContainer.innerHTML += `
                    <div class="env-cal-day" data-date="${dStr}" style="padding: 8px 0; border-radius: 8px; cursor: pointer; background: ${bg}; color: ${color}; font-weight: ${fw}; font-size: 1.1em; transition: all 0.2s; ${extraStyles}"
                         onmouseover="if(this.dataset.date !== '${selectedDate}') this.style.background='#e2e8f0';"
                         onmouseout="if(this.dataset.date !== '${selectedDate}') this.style.background='${today.getFullYear() === year && today.getMonth() === month && today.getDate() === i ? '#e0f2fe' : 'transparent'}';">
                        ${i}
                    </div>
                `;
            }

            document.querySelectorAll(".env-cal-day").forEach(el => {
                el.addEventListener("click", function() {
                    hiddenInputEnv.value = this.dataset.date;
                    hiddenInputEnv.dispatchEvent(new Event('input', {
                        bubbles: true
                    }));
                    hiddenInputEnv.dispatchEvent(new Event('change', {
                        bubbles: true
                    }));
                    window.envRenderCalendar(false);
                    document.getElementById('modalEnvCalendar').style.display = 'none';
                });
            });
        };

        const prevBtn = document.getElementById("env-cal-prev");
        const nextBtn = document.getElementById("env-cal-next");
        if (prevBtn) {
            prevBtn.addEventListener("click", () => {
                envCalCurrentDate.setMonth(envCalCurrentDate.getMonth() - 1);
                window.envRenderCalendar(false);
            });
        }
        if (nextBtn) {
            nextBtn.addEventListener("click", () => {
                envCalCurrentDate.setMonth(envCalCurrentDate.getMonth() + 1);
                window.envRenderCalendar(false);
            });
        }

        // Initialize calendar
        window.envRenderCalendar(false);
    });
</script>
