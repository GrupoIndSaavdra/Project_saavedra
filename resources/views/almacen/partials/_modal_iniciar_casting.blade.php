<div id="modalGestionVeredicto" class="alm-modal" role="dialog" aria-modal="true">
    <div class="alm-modal-content lib-modal-content"
        style="max-width: 1100px; width: 85vw; border-radius: 20px; overflow: hidden;">
        <div id="mgv-header" class="alm-modal-header lib-modal-header"
            style="background: linear-gradient(135deg, #16a34a, #15803d); padding: 2.5em 3em 2.2em; transition: background 0.3s ease;">
            <div class="div-cerrar">
                <button type="button" class="btn-cerrar" onclick="cerrarModalGestionVeredicto()">
                    <img class="img-cerrar" src="{{ asset('images/cerrar.png') }}" alt="Cerrar">
                </button>
            </div>
            <h3 style="font-size: 2.2em; margin: 0; font-family:'Poppins', sans-serif; font-weight: 700; color: #fff;"
                id="mgv-title">
                Finalización de Proceso de Modelo</h3>
            <p id="mgv-subtitle" class="lib-modal-subtitle"
                style="color: #e0e7ff; font-size: 1.15em; margin-top: 8px; margin-bottom: 0; font-family:'Poppins', sans-serif; font-weight: 500;">
            </p>

            {{-- Pestañas dinámicas --}}
            <div id="mgv-tabs-container"
                style="display: flex; gap: 10px; margin-top: 25px; border-bottom: 2px solid rgba(255,255,255,0.2); padding-bottom: 0;">
                <button type="button" id="tab-aprobados" class="mgv-tab active" onclick="switchMgvTab('aprobados')"
                    style="background: rgba(255,255,255,0.2); border: none; padding: 12px 25px; border-top-left-radius: 12px; border-top-right-radius: 12px; color: white; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 1.05em; cursor: pointer; transition: all 0.2s ease;">
                    <i class="fas fa-check-circle" style="margin-right: 6px;"></i> Modelos Aprobados (Casting)
                </button>
                <button type="button" id="tab-rechazados" class="mgv-tab" onclick="switchMgvTab('rechazados')"
                    style="background: rgba(255,255,255,0.05); border: none; padding: 12px 25px; border-top-left-radius: 12px; border-top-right-radius: 12px; color: rgba(255,255,255,0.7); font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 1.05em; cursor: pointer; transition: all 0.2s ease;">
                    <i class="fas fa-times-circle" style="margin-right: 6px;"></i> Modelos Rechazados
                </button>
            </div>
        </div>

        <div class="alm-modal-body lib-modal-body" style="padding: 3em 3.5em; background: #fff;">
            <input type="hidden" id="mgv-ot" name="ot">
            <input type="hidden" id="mgv-fecha" name="fecha_recepcion">

            {{-- ─────────────────────────────────────────────── --}}
            {{-- VISTA: APROBADOS (CASTING) --}}
            {{-- ─────────────────────────────────────────────── --}}
            <div id="mgv-view-aprobados" class="mgv-view">
                <form id="formMgvAprobados" enctype="multipart/form-data" novalidate autocomplete="off">
                    @csrf
                    <input type="hidden" name="ot" class="mgv-form-ot">
                    <input type="hidden" name="fecha_recepcion" class="mgv-form-fecha">

                    <p
                        style="margin-bottom: 20px; font-family:'Poppins', sans-serif; font-weight:500; line-height:1.6; color:#334155; font-size: 1.15em;">
                        Revisa los archivos disponibles de la OT, sube los formatos <strong
                            style="color:#15803d;">F-CCL-LDM</strong> firmados por cada modelo aprobado y genera la
                        Pre-Orden de Casting.
                    </p>

                    <div class="form-group" style="margin-bottom: 24px;">
                        <label
                            style="font-weight:700; color:#15803d; font-size:1.15em; margin-bottom:12px; display:block; font-family:'Poppins',sans-serif;">
                            Archivos de la OT disponibles (Aprobados):
                        </label>
                        <div id="mgv-aprobados-files"
                            style="background:#f0fdf4; border:1.8px solid #86efac; border-radius:14px; padding:20px; max-height:380px; overflow-y:auto; display:flex; flex-direction: column; gap: 20px;">
                            <div
                                style="text-align:center; color:#64748b; padding:12px; font-style:italic; font-size:0.95em; font-family:'Poppins',sans-serif;">
                                Cargando archivos...
                            </div>
                        </div>
                    </div>

                    <div class="form-group" style="margin-top: 20px; margin-bottom: 20px;">
                        <label
                            style="font-weight: 700; color: #15803d; display: block; margin-bottom: 10px; font-family:'Poppins',sans-serif; font-size:1.2em;">
                            Formatos F-CCL-LDM Firmados — Requeridos por Clase:
                        </label>
                        <div id="mgv-aprobados-inputs"
                            style="display:flex; flex-direction:column; gap:12px; margin-bottom: 15px;">
                        </div>
                    </div>

                    <div class="form-actions"
                        style="text-align: center; margin-top: 30px; display: flex; align-items: center; justify-content: center; gap: 15px; flex-wrap: wrap;">
                        <button type="submit" class="btn-save-preorden" id="btn-submit-aprobados"
                            style="font-size:1.15em; padding:14px 30px; border-radius:10px; font-family:'Poppins',sans-serif; font-weight: 700; height: auto; background: linear-gradient(135deg, #16a34a, #15803d); box-shadow: 0 4px 15px rgba(22,163,74,0.35); display: inline-flex; align-items: center; justify-content: center; gap: 8px; border: none; color: white; cursor: pointer; transition: all 0.2s ease;">
                            <span>Procesar Aceptados</span>
                        </button>
                        <button type="button" id="btn-ir-preorden-casting"
                            onclick="cerrarModalGestionVeredicto(); abrirModalPreOrdenCasting(document.getElementById('mgv-ot').value)"
                            title="Ir directamente a generar / editar la Pre-Orden de Casting"
                            class="alm-display-none cal-display-none"
                            style="font-size:1.15em; padding:14px 30px; border-radius:10px; font-family:'Poppins',sans-serif; font-weight: 700; height: auto; background: linear-gradient(135deg, #0369a1, #0284c7); box-shadow: 0 4px 15px rgba(3,105,161,0.3); align-items: center; justify-content: center; gap: 8px; border: none; color: white; cursor: pointer; transition: all 0.2s ease;">
                            <span>Generar / Ver Pre-Orden Casting</span>
                        </button>
                    </div>
                </form>
            </div>

            {{-- ─────────────────────────────────────────────── --}}
            {{-- VISTA: RECHAZADOS (PROCESAR) --}}
            {{-- ─────────────────────────────────────────────── --}}
            <div id="mgv-view-rechazados" class="mgv-view alm-display-none cal-display-none">
                <form id="formMgvRechazados" enctype="multipart/form-data" novalidate autocomplete="off">
                    @csrf
                    <input type="hidden" name="ot" class="mgv-form-ot">
                    <input type="hidden" name="fecha_recepcion" class="mgv-form-fecha">
                    <input type="hidden" name="clases_rechazadas" id="mgv-clases-rechazadas">

                    <p
                        style="margin-bottom: 20px; font-family:'Poppins', sans-serif; font-weight:500; line-height:1.6; color:#334155; font-size: 1.15em;">
                        Revisa los archivos disponibles, sube el <strong style="color:#b91c1c;">Formato de
                            Rechazo</strong> y el <strong style="color:#b91c1c;">SCAR</strong> por cada modelo
                        rechazado. Al finalizar, podrás generar la nueva Pre-Orden de Fabricación de Modelo.
                    </p>

                    <div class="form-group" style="margin-bottom: 24px;">
                        <label
                            style="font-weight:700; color:#b91c1c; font-size:1.15em; margin-bottom:12px; display:block; font-family:'Poppins',sans-serif;">
                            Archivos de la OT disponibles (Rechazados):
                        </label>
                        <div id="mgv-rechazados-files"
                            style="background:#fef2f2; border:1.8px solid #fca5a5; border-radius:14px; padding:20px; max-height:380px; overflow-y:auto; display:flex; flex-direction: column; gap: 20px;">
                            <div
                                style="text-align:center; color:#64748b; padding:12px; font-style:italic; font-size:0.95em; font-family:'Poppins',sans-serif;">
                                Cargando archivos...
                            </div>
                        </div>
                    </div>

                    <div class="form-group" style="margin-top: 20px; margin-bottom: 20px;">
                        <label
                            style="font-weight: 700; color: #b91c1c; display: block; margin-bottom: 10px; font-family:'Poppins',sans-serif; font-size:1.2em;">
                            Formatos de Rechazo y SCAR — Requeridos por Clase:
                        </label>
                        <div id="mgv-rechazados-inputs"
                            style="display:flex; flex-direction:column; gap:12px; margin-bottom: 15px;">
                        </div>
                    </div>

                    <div class="form-actions"
                        style="text-align: center; margin-top: 30px; display: flex; align-items: center; justify-content: center; gap: 15px; flex-wrap: wrap;">
                        <button type="submit" class="btn-save-preorden" id="btn-submit-rechazados"
                            style="font-size:1.15em; padding:14px 30px; border-radius:10px; font-family:'Poppins',sans-serif; font-weight: 700; height: auto; background: linear-gradient(135deg, #dc2626, #b91c1c); box-shadow: 0 4px 15px rgba(220,38,38,0.35); display: inline-flex; align-items: center; justify-content: center; gap: 8px; border: none; color: white; cursor: pointer; transition: all 0.2s ease;">
                            <span>Subir Formatos y Generar Pre-Orden de Modelo</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
