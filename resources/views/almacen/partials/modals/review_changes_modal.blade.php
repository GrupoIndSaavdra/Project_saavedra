<style>
    .btn-action-cambios-row {
        display: flex;
        flex-direction: row;
        gap: 16px;
        width: 100%;
        margin-top: 1.8em;
        margin-bottom: 0.5em;
        align-items: center;
        justify-content: center;
    }

    .btn-action-cambios {
        flex: 1;
        width: 50%;
        min-width: 0;
        padding: 1.1em 1.5em !important;
        border-radius: 12px;
        font-weight: 800;
        font-size: 0.95em;
        border: 2px solid var(--gis-blue) !important;
        cursor: pointer;
        background: var(--gis-blue) !important;
        color: #ffffff !important;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
        transition: all 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        text-transform: uppercase;
        letter-spacing: 0.8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        white-space: nowrap;
        text-decoration: none;
    }

    .btn-action-reiniciar:hover {
        background: #fdf2f2 !important;
        border-color: #dc3545 !important;
        color: #dc3545 !important;
        transform: scale(1.025) translateY(-2px) !important;
        box-shadow: 0 8px 20px rgba(220, 53, 69, 0.25) !important;
    }

    .btn-action-reiniciar:active {
        transform: scale(0.98) translateY(0) !important;
    }

    .btn-action-reemplazar:hover {
        background: #e6f9ed !important;
        border-color: #28a745 !important;
        color: #28a745 !important;
        transform: scale(1.025) translateY(-2px) !important;
        box-shadow: 0 8px 20px rgba(40, 167, 69, 0.25) !important;
    }

    .btn-action-reemplazar:active {
        transform: scale(0.98) translateY(0) !important;
    }

    /* ── Estilos y Animaciones Hover/Active para Botones en Modals de Confirmación ── */
    .confirm-modal-actions button,
    .btn-confirm-cancel,
    #btn-ejecutar-resolver-cambios {
        transition: all 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
    }

    .btn-confirm-cancel:hover {
        background: #475569 !important;
        color: #ffffff !important;
        transform: scale(1.03) translateY(-2px) !important;
        box-shadow: 0 8px 20px rgba(100, 116, 139, 0.4) !important;
    }

    .btn-confirm-cancel:active {
        transform: scale(0.97) translateY(0) !important;
        box-shadow: 0 2px 6px rgba(100, 116, 139, 0.2) !important;
    }

    #btn-ejecutar-resolver-cambios:hover,
    .confirm-modal-actions button:not(.btn-confirm-cancel):hover {
        transform: scale(1.03) translateY(-2px) !important;
        filter: brightness(1.12) contrast(1.05) !important;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.35) !important;
    }

    #btn-ejecutar-resolver-cambios:active,
    .confirm-modal-actions button:not(.btn-confirm-cancel):active {
        transform: scale(0.97) translateY(0) !important;
        box-shadow: 0 3px 8px rgba(0, 0, 0, 0.2) !important;
    }

    .confirm-modal-header .btn-cerrar {
        transition: all 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
    }

    .confirm-modal-header .btn-cerrar:hover {
        transform: scale(1.18) rotate(90deg) !important;
        opacity: 0.9;
    }

    .confirm-modal-header .btn-cerrar:active {
        transform: scale(0.9) rotate(90deg) !important;
    }

    .confirm-portal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(0, 10, 25, 0.85);
        z-index: 10005;
        justify-content: center;
        align-items: center;
    }

    .confirm-portal.open,
    .confirm-portal:not([hidden]) {
        display: flex !important;
    }

    .confirm-modal {
        background: #ffffff;
        border-radius: 20px;
        overflow: hidden;
        animation: prodModalEntrance 0.3s cubic-bezier(0.23, 1, 0.32, 1) both;
    }

    @media (max-width: 640px) {
        .btn-action-cambios-row {
            flex-direction: column;
        }

        .btn-action-cambios {
            width: 100%;
        }
    }
</style>

<div id="modalRevisarCambios" class="alm-modal">
    <div class="alm-modal-content alm-max-width-800px">
        <div class="alm-modal-header">
            <div class="div-cerrar">
                <button type="button" class="btn-cerrar" onclick="cerrarModalRevisarCambios()">
                    <img class="img-cerrar" src="{{ asset('images/cerrar.png') }}">
                </button>
            </div>
            <h3>Cambios Pendientes en Dibujos de Fundición</h3>
            <p class="lib-modal-subtitle alm-color-bae6fd alm-font-size-0-9em alm-margin-top-4px alm-margin-bottom-0"
                style="text-align: center;">
                Administración registró actualizaciones en los dibujos de esta OT. Elige qué acción deseas realizar en
                Almacén:
            </p>
        </div>
        <div class="alm-modal-body">
            <div id="revisar-cambios-container" class="alm-display-flex alm-flex-direction-column alm-gap-15px">
                <!-- Contenido dinámico (Comparativa de dibujos viejos vs nuevos) -->
            </div>

            <div class="btn-action-cambios-row">
                {{-- Botón Rojo en Hover: Reiniciar Proceso Completo / Reiniciar Clase --}}
                <button type="button" id="btn-resolver-reiniciar" class="btn-action-cambios btn-action-reiniciar"
                    onclick="solicitarConfirmacionCambios('reiniciar_completo')">
                    <span id="text-btn-reiniciar">Reiniciar Proceso Completo</span>
                </button>

                {{-- Botón Verde en Hover: Reemplazar Dibujos --}}
                <button type="button" id="btn-resolver-mantener" class="btn-action-cambios btn-action-reemplazar"
                    onclick="solicitarConfirmacionCambios('mantener')">
                    <span>Reemplazar Dibujos</span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Emergente de Confirmación de Acción para Almacén --}}
<div id="modalConfirmarAccionCambios" class="confirm-portal" hidden>
    <div class="confirm-modal"
        style="max-width: 520px; border-radius: 20px; border: 4px solid var(--gis-blue); box-shadow: 0 15px 45px rgba(0,0,0,0.35);">
        <div class="confirm-modal-header"
            style="background: var(--gis-blue); padding: 1.2em 1.5em; display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #f0f0f0;">
            <h3 id="confirm-cambios-title"
                style="color: #ffffff; font-size: 1.2em; font-weight: 800; margin: 0; text-transform: uppercase; letter-spacing: 1px;">
                Confirmar Acción
            </h3>
            <button type="button" class="btn-cerrar" onclick="cerrarModalConfirmarAccionCambios()"
                style="background: none; border: none; cursor: pointer;">
                <img class="img-cerrar" src="{{ asset('images/cerrar.png') }}" style="width: 28px; height: 28px;">
            </button>
        </div>
        <div class="confirm-modal-body" style="padding: 1.8em; text-align: center; background: #fdfdfd;">
            <div id="confirm-cambios-icon-wrapper"
                style="width: 70px; height: 70px; margin: 0 auto 1em; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
            </div>
            <p id="confirm-cambios-message"
                style="margin: 0 0 1.5em 0; color: #334155; font-size: 1.05em; line-height: 1.5; font-weight: 600;">
            </p>
            <div class="confirm-modal-actions" style="display: flex; gap: 1em; justify-content: center;">
                <button type="button" class="btn-confirm-cancel" onclick="cerrarModalConfirmarAccionCambios()"
                    style="padding: 0.8em 1.8em; background: #64748b; color: white; border: none; border-radius: 50px; font-weight: 800; font-size: 0.9em; text-transform: uppercase; cursor: pointer; letter-spacing: 1px;">
                    Cancelar
                </button>
                <button type="button" id="btn-ejecutar-resolver-cambios"
                    style="padding: 0.8em 1.8em; border: none; border-radius: 50px; font-weight: 800; font-size: 0.9em; text-transform: uppercase; cursor: pointer; color: white; transition: all 0.3s ease; letter-spacing: 1px;">
                    Confirmar
                </button>
            </div>
        </div>
    </div>
</div>