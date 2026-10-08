{{-- ────────────────────────────────────────────────────────────────
     MODAL: Confirmar Reinicio de Proceso (Rojo)
     ID: modalConfirmarReinicio
     ──────────────────────────────────────────────────────────────── --}}
<style>
    .btn-cancelar-hover:hover {
        background: #ef4444 !important;
        color: #ffffff !important;
        border-color: #ef4444 !important;
        transform: scale(1.05);
    }

    .btn-confirmar-reinicio {
        background: #033966 !important;
    }

    .btn-confirmar-reinicio:not(:disabled):hover {
        background: #991b1b !important;
        transform: scale(1.05);
    }

    .btn-confirmar-reemplazo {
        background: #033966 !important;
    }

    .btn-confirmar-reemplazo:not(:disabled):hover {
        background: #16a34a !important;
        transform: scale(1.05);
    }
</style>

<div id="modalConfirmarReinicio" class="alm-modal" role="dialog" aria-modal="true" style="padding: 0; z-index: 10010;">
    <div class="alm-modal-content alm-border-radius-20px alm-overflow-hidden"
        style="max-width: 1500px; width: 95vw; max-height: 92vh; min-height: 60vh; display: flex; flex-direction: column; margin: auto; border: 2.5px solid #dc2626; box-shadow: 0 20px 60px rgba(220,38,38,0.25);">

        {{-- CABECERA ROJA --}}
        <div class="alm-modal-header alm-position-relative"
            style="background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%); border-bottom: 2px solid #7f1d1d; padding: 10px 28px;">
            <div class="div-cerrar">
                <button type="button" class="btn-cerrar" onclick="cerrarModalReinicio()">
                    <img class="img-cerrar" src="{{ asset('images/cerrar.png') }}" alt="Cerrar"
                        style="width: 32px !important; height: 32px !important;">
                </button>
            </div>
            <div class="alm-display-flex alm-align-items-center" style="gap: 16px; margin-top: 5px;">
                <img src="{{ asset('images/reiniciar.png') }}"
                    style="width: 55px !important; height: 55px !important; object-fit: contain; flex-shrink: 0;"
                    alt="">
                <div>
                    <h3 class="alm-color-fff alm-margin-0 alm-font-weight-800 alm-font-family-Poppins-sans-serif"
                        style="font-size: 1.35em;">Confirmar Reinicio de Proceso</h3>
                    <p id="reinicio-subtitle"
                        class="alm-color-rgba-255-255-255-0-9 alm-margin-top-2px alm-font-weight-500 alm-font-family-Poppins-sans-serif"
                        style="font-size: 0.95em; margin: 0;">ATENCIÓN: Esta acción es irreversible</p>
                </div>
            </div>
        </div>

        {{-- CUERPO --}}
        <div class="alm-modal-body alm-background-fafafa alm-font-family-Poppins-sans-serif"
            style="flex: 1; overflow-y: auto; padding: 24px; display: flex; flex-direction: column;">

            <div
                style="display: grid; grid-template-columns: minmax(250px, 1fr) minmax(380px, 1.5fr); gap: 24px; align-items: stretch; flex: 1;">

                {{-- Columna izquierda: Advertencia --}}
                <div
                    style="background: #fff; padding: 24px; border-radius: 14px; border: 1.5px solid #fecaca; box-shadow: 0 4px 14px rgba(220,38,38,0.07); display: flex; flex-direction: column;">
                    <div style="text-align: center; margin-bottom: 18px;">
                        <div
                            style="width: 185px; height: 185px; margin: 0 auto; background: #fee2e2; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(220,38,38,0.2);">
                            <img src="{{ asset('images/reiniciar.png') }}"
                                style="width: 130px; height: 130px; object-fit: contain;">
                        </div>
                    </div>
                    <h4
                        style="color: #b91c1c; font-weight: 800; font-size: 1.2em; text-align: center; margin: 0 0 12px 0; font-family: 'Poppins', sans-serif;">
                        ¡Acción Destructiva!
                    </h4>
                    <p
                        style="color: #b91c1c; font-size: 1.05em; text-align: left; margin: 0; font-weight: 600; line-height: 1.6; font-family: 'Poppins', sans-serif;">
                        Se <strong style="color: #ff0000; font-weight: 900; font-size: 1.15em; text-transform: uppercase; text-shadow: 0px 0px 1px rgba(255,0,0,0.5);">eliminarán permanentemente</strong> los avances, formularios y documentos de Almacén
                        para las clases seleccionadas. El proceso volverá a su etapa inicial. Esta acción <strong style="color: #ff0000; font-weight: 900; font-size: 1.15em; text-decoration: underline; text-transform: uppercase; text-shadow: 0px 0px 1px rgba(255,0,0,0.5);">no se
                            puede deshacer</strong>.
                    </p>
                    <div
                        style="margin-top: auto; padding: 12px 16px; background: #fff1f2; border-radius: 8px; border: 1px solid #fecdd3;">
                        <p
                            style="margin: 0; font-size: 0.9em; color: #be123c; font-weight: 600; display: flex; align-items: flex-start; gap: 8px; font-family: 'Poppins', sans-serif;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                stroke-linejoin="round" style="flex-shrink: 0; margin-top: 1px;">
                                <path
                                    d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                                <line x1="12" y1="9" x2="12" y2="13" />
                                <line x1="12" y1="17" x2="12.01" y2="17" />
                            </svg>
                            El botón de confirmación se habilitará después de un breve conteo para evitar errores.
                        </p>
                    </div>
                </div>

                {{-- Columna derecha: Selección de clases + botones --}}
                <div
                    style="background: #fff; padding: 20px; border-radius: 14px; border: 1.5px solid #e2e8f0; box-shadow: 0 4px 14px rgba(0,0,0,0.04); display: flex; flex-direction: column;">
                    <h4
                        style="margin: 0 0 4px 0; color: #334155; font-size: 1em; font-weight: 700; font-family: 'Poppins', sans-serif;">
                        Selecciona las clases a reiniciar:
                    </h4>
                    <p style="margin: 0 0 12px 0; font-size: 0.85em; color: #64748b; font-family: 'Poppins', sans-serif; border-bottom: 2px solid #fca5a5; padding-bottom: 8px;">
                        * Las clases resaltadas en <strong style="color: #dc2626;">rojo</strong> ya están seleccionadas.
                    </p>

                    <div id="reinicio-classes-container"
                        style="min-height: 120px; max-height: 400px; overflow-y: auto; display: flex; flex-wrap: wrap; gap: 10px; align-content: flex-start; padding: 4px 0;">
                        {{-- Pills de clases inyectadas por JS --}}
                    </div>

                    <p id="reinicio-no-classes-msg"
                        style="display: none; color: #94a3b8; font-size: 0.88em; text-align: center; margin: 10px 0; font-family: 'Poppins', sans-serif;">
                        No hay clases disponibles.
                    </p>

                    <div
                        style="margin-top: auto; padding-top: 20px; display: flex; justify-content: center; gap: 12px; flex-wrap: wrap;">
                        <button type="button" onclick="cerrarModalReinicio()" class="btn-cancelar-hover"
                            style="padding: 10px 22px; background: #f1f5f9; color: #475569; border: 1.5px solid #cbd5e1; border-radius: 10px; font-weight: 700; font-size: 0.88em; cursor: pointer; font-family: 'Poppins', sans-serif; transition: all 0.2s;">
                            Cancelar
                        </button>
                        <button type="button" id="btn-ejecutar-reinicio" class="btn-confirmar-reinicio" disabled
                            style="padding: 10px 22px; color: white; border: none; border-radius: 10px; font-weight: 800; font-size: 0.88em; cursor: not-allowed; font-family: 'Poppins', sans-serif; opacity: 0.5; transition: all 0.3s;">
                            Sí, Reiniciar (5s)
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>


{{-- ────────────────────────────────────────────────────────────────
     MODAL: Confirmar Reemplazo de Dibujos (Verde)
     ID: modalConfirmarReemplazo
     ──────────────────────────────────────────────────────────────── --}}
<div id="modalConfirmarReemplazo" class="alm-modal" role="dialog" aria-modal="true"
    style="padding: 0; z-index: 10010;">
    <div class="alm-modal-content alm-border-radius-20px alm-overflow-hidden"
        style="max-width: 1500px; width: 95vw; max-height: 92vh; min-height: 60vh; display: flex; flex-direction: column; margin: auto; border: 2.5px solid #16a34a; box-shadow: 0 20px 60px rgba(22,163,74,0.2);">

        {{-- CABECERA VERDE --}}
        <div class="alm-modal-header alm-position-relative"
            style="background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); border-bottom: 2px solid #14532d; padding: 10px 28px;">
            <div class="div-cerrar">
                <button type="button" class="btn-cerrar" onclick="cerrarModalReemplazo()">
                    <img class="img-cerrar" src="{{ asset('images/cerrar.png') }}" alt="Cerrar"
                        style="width: 32px !important; height: 32px !important;">
                </button>
            </div>
            <div class="alm-display-flex alm-align-items-center" style="gap: 16px; margin-top: 5px;">
                <img src="{{ asset('images/reemplazar.png') }}"
                    style="width: 55px !important; height: 55px !important; object-fit: contain; flex-shrink: 0;"
                    alt="">
                <div>
                    <h3 class="alm-color-fff alm-margin-0 alm-font-weight-800 alm-font-family-Poppins-sans-serif"
                        style="font-size: 1.35em;">Confirmar Reemplazo de Dibujos</h3>
                    <p id="reemplazo-subtitle"
                        class="alm-color-rgba-255-255-255-0-9 alm-margin-top-2px alm-font-weight-500 alm-font-family-Poppins-sans-serif"
                        style="font-size: 0.95em; margin: 0;">Actualización segura — El progreso se conserva</p>
                </div>
            </div>
        </div>

        {{-- CUERPO --}}
        <div class="alm-modal-body alm-background-fafafa alm-font-family-Poppins-sans-serif"
            style="flex: 1; overflow-y: auto; padding: 24px; display: flex; flex-direction: column;">

            <div
                style="display: grid; grid-template-columns: minmax(250px, 1fr) minmax(380px, 1.5fr); gap: 24px; align-items: stretch; flex: 1;">

                {{-- Columna izquierda: Info positiva --}}
                <div
                    style="background: #fff; padding: 24px; border-radius: 14px; border: 1.5px solid #bbf7d0; box-shadow: 0 4px 14px rgba(22,163,74,0.07); display: flex; flex-direction: column;">
                    <div style="text-align: center; margin-bottom: 18px;">
                        <div
                            style="width: 185px; height: 185px; margin: 0 auto; background: #dcfce7; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(22,163,74,0.2);">
                            <img src="{{ asset('images/reemplazar.png') }}"
                                style="width: 130px; height: 130px; object-fit: contain;">
                        </div>
                    </div>
                    <h4
                        style="color: #15803d; font-weight: 800; font-size: 1.2em; text-align: center; margin: 0 0 12px 0; font-family: 'Poppins', sans-serif;">
                        Actualización Segura
                    </h4>
                    <p
                        style="color: #15803d; font-size: 1.05em; text-align: left; margin: 0; font-weight: 600; line-height: 1.6; font-family: 'Poppins', sans-serif;">
                        Se reemplazarán los dibujos obsoletos por las nuevas versiones registradas en el área de diseño.
                        <br>
                        <strong style="color: #059669; font-weight: 900; font-size: 1.15em; text-shadow: 0px 0px 1px rgba(5,150,105,0.3);">¡Todo el progreso, formularios y registros actuales se conservarán intactos!</strong>
                    </p>
                    <div
                        style="margin-top: auto; padding: 12px 16px; background: #f0fdf4; border-radius: 8px; border: 1px solid #bbf7d0;">
                        <p
                            style="margin: 0; font-size: 0.9em; color: #166534; font-weight: 600; display: flex; align-items: flex-start; gap: 8px; font-family: 'Poppins', sans-serif;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                                stroke-linecap="round" stroke-linejoin="round"
                                style="flex-shrink: 0; margin-top: 1px;">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                                <polyline points="22 4 12 14.01 9 11.01" />
                            </svg>
                            Esta acción es segura y no elimina información existente del proceso.
                        </p>
                    </div>
                </div>

                {{-- Columna derecha: Selección de clases + botones --}}
                <div
                    style="background: #fff; padding: 20px; border-radius: 14px; border: 1.5px solid #e2e8f0; box-shadow: 0 4px 14px rgba(0,0,0,0.04); display: flex; flex-direction: column;">
                    <h4
                        style="margin: 0 0 4px 0; color: #334155; font-size: 1em; font-weight: 700; font-family: 'Poppins', sans-serif;">
                        Selecciona las clases a actualizar:
                    </h4>
                    <p style="margin: 0 0 12px 0; font-size: 0.85em; color: #64748b; font-family: 'Poppins', sans-serif; border-bottom: 2px solid #86efac; padding-bottom: 8px;">
                        * Las clases resaltadas en <strong style="color: #16a34a;">verde</strong> ya están seleccionadas.
                    </p>

                    <div id="reemplazo-classes-container"
                        style="min-height: 120px; max-height: 400px; overflow-y: auto; display: flex; flex-wrap: wrap; gap: 10px; align-content: flex-start; padding: 4px 0;">
                        {{-- Pills de clases inyectadas por JS --}}
                    </div>

                    <p id="reemplazo-no-classes-msg"
                        style="display: none; color: #94a3b8; font-size: 0.88em; text-align: center; margin: 10px 0; font-family: 'Poppins', sans-serif;">
                        No hay clases disponibles.
                    </p>

                    <div
                        style="margin-top: auto; padding-top: 20px; display: flex; justify-content: center; gap: 12px; flex-wrap: wrap;">
                        <button type="button" onclick="cerrarModalReemplazo()" class="btn-cancelar-hover"
                            style="padding: 10px 22px; background: #f1f5f9; color: #475569; border: 1.5px solid #cbd5e1; border-radius: 10px; font-weight: 700; font-size: 0.88em; cursor: pointer; font-family: 'Poppins', sans-serif; transition: all 0.2s;">
                            Cancelar
                        </button>
                        <button type="button" id="btn-ejecutar-reemplazo" class="btn-confirmar-reemplazo" disabled
                            style="padding: 10px 22px; color: white; border: none; border-radius: 10px; font-weight: 800; font-size: 0.88em; cursor: not-allowed; font-family: 'Poppins', sans-serif; opacity: 0.5; transition: all 0.3s;">
                            Sí, Reemplazar (3s)
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
