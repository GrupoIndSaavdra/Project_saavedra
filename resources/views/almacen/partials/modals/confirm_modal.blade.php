<div id="modalConfirmarModelo" class="alm-modal" role="dialog" aria-modal="true" style="padding: 0;">
    <div class="alm-modal-content alm-border-radius-20px alm-border-2-5px-solid-0a8504 alm-overflow-hidden"
        style="max-width: 1750px; width: 98vw; max-height: 97vh; height: 97vh; display: flex; flex-direction: column; margin: auto;">
        <div class="alm-modal-header alm-background-linear-gradient-135deg-0a8504-064e03 alm-border-bottom-2px-solid-064e03 alm-position-relative"
            style="padding: 10px 28px;">
            <div class="div-cerrar">
                <button type="button" class="btn-cerrar" onclick="cerrarModalConfirmarModelo()">
                    <img class="img-cerrar" src="{{ asset('images/cerrar.png') }}" alt="Cerrar"
                        style="width: 32px !important; height: 32px !important;">
                </button>
            </div>
            <div class="alm-display-flex alm-align-items-center" style="gap: 12px;">
                <img src="{{ asset('images/Aprobado.png') }}"
                    style="width: 40px !important; height: 40px !important; max-width: 40px !important; max-height: 40px !important; object-fit: contain; flex-shrink: 0;"
                    alt="">
                <div>
                    <h3 class="alm-color-fff alm-margin-0 alm-font-weight-800 alm-font-family-Poppins-sans-serif"
                        style="font-size: 1.15em;">
                        Confirmar Disponibilidad del Modelo</h3>
                    <div id="confirmar-modelo-subtitle"
                        class="alm-color-rgba-255-255-255-0-9 alm-margin-top-2px alm-font-weight-500 alm-font-family-Poppins-sans-serif"
                        style="font-size: 0.85em;">
                        OT: -</div>
                </div>
            </div>
        </div>
        <div class="alm-modal-body alm-padding-1em-1-6em-1-2em-1-6em alm-background-fafafa alm-font-family-Poppins-sans-serif"
            style="flex: 1; display: flex; flex-direction: column; overflow: hidden; min-height: 0;">
            <form id="formConfirmarModelo" enctype="multipart/form-data"
                style="display: flex; flex-direction: column; flex: 1; min-height: 0;"
                data-email-modelo="{{ env('EMAIL_PROVEEDOR_MODELOS', 'produccion@ssmetalf.mx,asistenteprod@ssmetalf.mx') }}"
                data-email-calidad="{{ env('EMAIL_CALIDAD', 'inspecciontec@grupoindsaavedra.com') }}">
                <input type="hidden" id="cm-ot" name="ot">
                <input type="hidden" id="cm-id-hash" name="id_hash">


                <div
                    style="display: grid; grid-template-columns: minmax(360px, 1fr) minmax(600px, 1.55fr); gap: 18px; align-items: stretch; flex: 1; min-height: 0;">

                    <!-- Columna Izquierda: Datos del Formulario + Botón Verde de Selección -->
                    <div
                        style="display: flex; flex-direction: column; gap: 12px; min-height: 0; overflow-y: auto; padding-right: 6px;">

                        <!-- Bloque 1: Formulario Principal -->
                        <div
                            style="background: #fff; padding: 14px 16px; border-radius: 14px; border: 1px solid #e2e8f0; box-shadow: 0 4px 10px rgba(0,0,0,0.03); display: flex; flex-direction: column; flex: 1; flex-shrink: 0;">
                            <h4
                                style="margin-top: 0; margin-bottom: 8px; color: #0a8504; font-size: 1em; border-bottom: 2px solid #0a8504; padding-bottom: 4px; font-weight: 700; font-family: 'Poppins', sans-serif; display: flex; align-items: center; gap: 8px;">
                                <img src="{{ asset('images/copia-de-datos.png') }}"
                                    style="width: 30px; height: 30px; object-fit: contain;"> Datos de Confirmación
                            </h4>

                            <div style="display: grid; grid-template-columns: 1fr; gap: 10px;">
                                <div class="form-group" id="div-cm-destinatario-calidad">
                                    <label for="cm-destinatario-calidad"
                                        style="font-weight: 700; color: #334155; display: block; margin-bottom: 2px; font-size: 0.84em;">Notificar
                                        a Calidad:</label>
                                    <input type="text" id="cm-destinatario-calidad" name="destinatario_calidad"
                                        class="form-control" style="font-size: 0.84em; padding: 6px 10px; height: auto;"
                                        required>
                                </div>
                            </div>

                            <div class="form-group" style="margin-top: 6px;">
                                <label id="cm-fecha-label"
                                    style="font-weight: 700; color: #334155; display: block; margin-bottom: 6px; font-size: 0.9em;">Fecha
                                    de Envío <span class="alm-color-dc2626">*</span>:</label>
                                <input type="hidden" id="cm-fecha" name="fecha" required />
                                <button type="button"
                                    onclick="document.getElementById('modalCmCalendar').style.display='flex'"
                                    style="display: flex; align-items: center; justify-content: center; gap: 10px; background: #fff8f1; border: 2px dashed #f97316; padding: 12px 16px; border-radius: 12px; width: 100%; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 6px rgba(249, 115, 22, 0.05);"
                                    onmouseover="this.style.background='#ffedd5'; this.style.borderColor='#ea580c';"
                                    onmouseout="this.style.background='#fff8f1'; this.style.borderColor='#f97316';">
                                    <img src="{{ asset('images/Fecha.png') }}"
                                        style="width: 26px; height: 26px; object-fit: contain;">
                                    <span id="cm-fecha-seleccionada-btn"
                                        style="color: #c2410c; font-weight: 700; font-size: 1.05em; font-family: 'Poppins', sans-serif;">Seleccionar
                                        Fecha...</span>
                                </button>
                            </div>

                            <div class="form-group"
                                style="margin-top: 6px; margin-bottom: 0; display: flex; flex-direction: column; flex: 1; flex-shrink: 0;">
                                <label
                                    style="font-weight: 700; color: #334155; display: block; margin-bottom: 2px; font-size: 0.84em; flex-shrink: 0;">Clases
                                    Disponibles <span class="alm-text-dark-red">*</span>:</label>
                                <div id="cm-clases-container"
                                    style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px; display: flex; flex-wrap: wrap; gap: 8px; flex: 1; overflow-y: auto; align-content: flex-start;">
                                    <div
                                        class="alm-spinner alm-border-top-color-0284c7 alm-display-block alm-margin-5px-auto">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bloque 2 (VERDE): SOLO el Botón Dropzone para Seleccionar/Cargar -->
                        <div
                            style="background: #f0fdf4; border: 2px solid #16a34a; padding: 14px 16px; border-radius: 14px; box-shadow: 0 4px 10px rgba(22, 163, 74, 0.08);">
                            <h4
                                style="margin-top: 0; margin-bottom: 12px; color: #15803d; font-size: 0.96em; border-bottom: 1.5px solid #16a34a; padding-bottom: 4px; font-weight: 700; font-family: 'Poppins', sans-serif; display: flex; align-items: center; gap: 6px;">
                                <img src="{{ asset('images/anadir.png') }}"
                                    style="width: 32px; height: 32px; object-fit: contain;"> Subir Archivos Escaneados
                                para Confirmación de Modelo <span class="alm-text-dark-red">*</span>
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
                                    style="padding: 10px 12px; display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; text-align: center;">
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
                                    <div
                                        style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; padding: 10px; background: #f0fdf4; border-radius: 8px; border: 1px solid #dcfce7;">
                                        <img src="{{ asset('images/perspectiva-icon.png') }}"
                                            style="width: 40px; height: 40px; object-fit: contain;">
                                        <span
                                            style="color: #15803d; font-size: 0.85em; line-height: 1.3; font-weight: 500;"><strong>Fotos
                                                del Modelo</strong><br>Tomar desde varios ángulos</span>
                                    </div>
                                </div>
                            </div>

                            <div class="custom-file-dropzone"
                                style="border: 2px dashed #16a34a; background: #ffffff; padding: 12px 14px; border-radius: 10px; text-align: center; cursor: pointer; position: relative;">
                                <input type="file" id="cm-archivos" name="archivos[]" class="custom-file-input"
                                    style="position: absolute; top:0; left:0; width:100%; height:100%; opacity:0; cursor:pointer;"
                                    multiple>
                                <div class="dropzone-content"
                                    style="display: flex; align-items: center; justify-content: center; gap: 8px;">
                                    <img src="{{ asset('images/anadir.png') }}"
                                        style="width: 44px; height: 44px; object-fit: contain;">
                                    <span style="font-weight: 700; color: #15803d; font-size: 0.86em;">Haz clic o
                                        arrastra PDFs e imágenes aquí</span>
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
                                OT
                                Disponibles
                            </h4>

                            <div id="cm-server-files-container"
                                style="background: #f0f7ff; border: 1px solid #bae6fd; border-radius: 10px; padding: 12px; flex: 1; min-height: 0; overflow-y: auto; display: flex; flex-direction: column; gap: 10px;">
                                <div
                                    class="alm-spinner alm-border-top-color-0284c7 alm-display-block alm-margin-10px-auto">
                                </div>
                            </div>
                        </div>

                        <!-- Sub-contenedor 2 (VERDE ESMERALDA): Nuevos Archivos Adjuntados (Coincide en color con el botón de la izquierda) -->
                        <div
                            style="background: #f0fdf4; border: 2px solid #16a34a; padding: 14px 16px; border-radius: 14px; box-shadow: 0 4px 10px rgba(22, 163, 74, 0.08); flex: 1; display: flex; flex-direction: column; min-height: 0;">
                            <h4
                                style="margin-top: 0; margin-bottom: 6px; color: #15803d; font-size: 0.98em; border-bottom: 1.5px solid #16a34a; padding-bottom: 4px; font-weight: 700; font-family: 'Poppins', sans-serif; display: flex; align-items: center; gap: 6px; flex-shrink: 0;">
                                <img src="{{ asset('images/anadir.png') }}"
                                    style="width: 30px; height: 30px; object-fit: contain;"> Nuevos Archivos Adjuntados
                            </h4>

                            <div id="cm-archivos-list"
                                style="background: #f0fdf4; border: 1px solid #a7f3d0; border-radius: 10px; padding: 12px; flex: 1; min-height: 0; overflow-y: auto; display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-start;">
                            </div>
                        </div>

                    </div>

                </div>

                <div
                    style="text-align: center; margin-top: 8px; margin-bottom: 0; padding: 0; flex-shrink: 0; height: max-content;">
                    <button type="submit" id="btn-submit-confirmar-modelo" class="btn-save-preorden" disabled
                        style="background: linear-gradient(135deg, #0a8504, #064e03); box-shadow: 0 4px 15px rgba(10, 133, 4, 0.35); margin: 0; padding: 11px 44px; border: none; border-radius: 10px; color: #fff; font-weight: 700; cursor: pointer; font-size: 1.05em; display: inline-flex; align-items: center; justify-content: center; gap: 8px;">
                        Confirmar y Registrar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const form = document.getElementById("formConfirmarModelo");
        if (!form) return;
        const btn = document.getElementById("btn-submit-confirmar-modelo");

        function checkFormValidity() {
            if (!btn) return;
            const selectedClasses = form.querySelectorAll(".cm-clase-checkbox:checked").length;
            const hasDate = document.getElementById("cm-fecha")?.value;
            const filesCount = window.cmConfirmarSelectedFiles ? window.cmConfirmarSelectedFiles.length : 0;

            if (selectedClasses > 0 && filesCount > 0 && hasDate) {
                btn.disabled = false;
                btn.style.opacity = "1";
                btn.style.cursor = "pointer";
            } else {
                btn.disabled = true;
                btn.style.opacity = "0.6";
                btn.style.cursor = "not-allowed";
            }
        }

        form.addEventListener("input", checkFormValidity);
        form.addEventListener("change", checkFormValidity);

        // Patch the global function to trigger validation check
        const originalRender = window.renderCmConfirmarBadges;
        window.renderCmConfirmarBadges = function() {
            if (originalRender) originalRender();
            checkFormValidity();
        };

        // Initial check
        setInterval(checkFormValidity, 500);

        // --- Inline Calendar Logic for Confirm Modal ---
        const cmMonthNames = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto",
            "Septiembre", "Octubre", "Noviembre", "Diciembre"
        ];
        let cmCalCurrentDate = new Date();
        const hiddenInputCm = document.getElementById("cm-fecha");

        window.cmRenderCalendar = function(reset = false) {
            if (reset) {
                cmCalCurrentDate = new Date();
                if (hiddenInputCm) {
                    hiddenInputCm.value = "";
                    hiddenInputCm.dispatchEvent(new Event('input', { bubbles: true }));
                }
            }
            if (!hiddenInputCm) return;

            const year = cmCalCurrentDate.getFullYear();
            const month = cmCalCurrentDate.getMonth();
            const selectedDate = hiddenInputCm.value;

            const monthYearEl = document.getElementById("cm-cal-month-year");
            if (monthYearEl) {
                monthYearEl.textContent = `${cmMonthNames[month]} ${year}`;
            }

            const labelSelBtn = document.getElementById("cm-fecha-seleccionada-btn");
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

            const daysContainer = document.getElementById("cm-cal-days");
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
                    bg = "#0a8504";
                    color = "#fff";
                    fw = "700";
                    extraStyles = "border: 1px solid #0a8504; box-shadow: 0 2px 5px rgba(10, 133, 4, 0.4);";
                } else if (today.getFullYear() === year && today.getMonth() === month && today.getDate() ===
                    i) {
                    color = "#0a8504";
                    fw = "700";
                    extraStyles = "border: 1px solid #bbf7d0; background: #dcfce7;";
                }

                daysContainer.innerHTML += `
                    <div class="cm-cal-day" data-date="${dStr}" style="padding: 8px 0; border-radius: 8px; cursor: pointer; background: ${bg}; color: ${color}; font-weight: ${fw}; font-size: 1.1em; transition: all 0.2s; ${extraStyles}"
                         onmouseover="if(this.dataset.date !== '${selectedDate}') this.style.background='#e2e8f0';"
                         onmouseout="if(this.dataset.date !== '${selectedDate}') this.style.background='${today.getFullYear() === year && today.getMonth() === month && today.getDate() === i ? '#dcfce7' : 'transparent'}';">
                        ${i}
                    </div>
                `;
            }

            document.querySelectorAll(".cm-cal-day").forEach(el => {
                el.addEventListener("click", function() {
                    hiddenInputCm.value = this.dataset.date;
                    hiddenInputCm.dispatchEvent(new Event('input', { bubbles: true }));
                    hiddenInputCm.dispatchEvent(new Event('change', { bubbles: true }));
                    window.cmRenderCalendar(false);
                    document.getElementById('modalCmCalendar').style.display = 'none';
                });
            });
        };

        const cmPrevBtn = document.getElementById("cm-cal-prev");
        const cmNextBtn = document.getElementById("cm-cal-next");
        if (cmPrevBtn) {
            cmPrevBtn.addEventListener("click", () => {
                cmCalCurrentDate.setMonth(cmCalCurrentDate.getMonth() - 1);
                window.cmRenderCalendar(false);
            });
        }
        if (cmNextBtn) {
            cmNextBtn.addEventListener("click", () => {
                cmCalCurrentDate.setMonth(cmCalCurrentDate.getMonth() + 1);
                window.cmRenderCalendar(false);
            });
        }

        // Initialize calendar
        window.cmRenderCalendar(false);
    });
</script>

<!-- Mini Modal for Calendar Confirmar Modelo -->
<div id="modalCmCalendar" class="alm-modal"
    style="padding: 0; z-index: 999999 !important; background: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center;">
    <div
        style="background: #fff; padding: 24px 30px; border-radius: 16px; border: 2px solid #0a8504; box-shadow: 0 10px 30px rgba(0,0,0,0.3); width: 450px; position: relative;">
        <div
            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px;">
            <h4
                style="margin: 0; color: #0a8504; font-size: 1.15em; font-family: 'Poppins', sans-serif; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                <img src="{{ asset('images/Fecha.png') }}" style="width: 26px; height: 26px; object-fit: contain;">
                Seleccionar Fecha
            </h4>
            <button type="button" onclick="document.getElementById('modalCmCalendar').style.display='none'"
                style="background: none; border: none; cursor: pointer; color: #ef4444; font-weight: bold; font-size: 1.6em; padding: 0; line-height: 1;">&times;</button>
        </div>
        <div id="cm-inline-calendar" style="user-select: none;">
            <div
                style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; padding: 0 4px;">
                <button type="button" id="cm-cal-prev"
                    style="border: none; background: #f1f5f9; border-radius: 8px; cursor: pointer; font-size: 1.3em; color: #475569; padding: 4px 14px; transition: 0.2s;"
                    onmouseover="this.style.background='#e2e8f0'"
                    onmouseout="this.style.background='#f1f5f9'">&#9664;</button>
                <span id="cm-cal-month-year"
                    style="font-weight: 700; color: #0f172a; font-size: 1.2em; text-transform: capitalize;"></span>
                <button type="button" id="cm-cal-next"
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
            <div id="cm-cal-days"
                style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 6px; text-align: center;">
            </div>
        </div>
    </div>
</div>
