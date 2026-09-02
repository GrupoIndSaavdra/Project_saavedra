<div id="modalRevisarCambios" class="alm-modal">
    <div class="alm-modal-content alm-max-width-800px">
        <div class="alm-modal-header">
            <div class="div-cerrar">
                <button type="button" class="btn-cerrar" onclick="cerrarModalRevisarCambios()">
                    <img class="img-cerrar" src="{{ asset('images/cerrar.png') }}">
                </button>
            </div>
            <h3>Cambios Pendientes en Dibujos de Fundición</h3>
            <p class="lib-modal-subtitle alm-color-bae6fd alm-font-size-0-9em alm-margin-top-4px alm-margin-bottom-0">
                Se registraron cambios en Dibujos de Fundición. ¿Deseas reiniciar el proceso desde el inicio (borrando
                estados de Calidad actuales) o solo cambiar los dibujos viejos por los nuevos manteniendo el progreso de
                la OT?
            </p>
        </div>
        <div class="alm-modal-body">
            <div id="revisar-cambios-container" class="alm-display-flex alm-flex-direction-column alm-gap-15px">
                <!-- Contenido dinámico -->
            </div>
            <div class="alm-margin-top-20px alm-display-flex alm-gap-15px alm-justify-content-center">
                <button type="button" id="btn-resolver-reiniciar"
                    class="btn-save-preorden alm-background-color-b91c1c alm-box-shadow-0-4px-15px-rgba-220-38-38-0-3"
                    onclick="almacenResolverCambios('reiniciar')">
                    Reiniciar Proceso Completo
                </button>
                <button type="button" id="btn-resolver-mantener"
                    class="btn-save-preorden alm-background-linear-gradient-135deg-0a8504-064e03 alm-box-shadow-0-4px-15px-rgba-10-133-4-0-35"
                    onclick="almacenResolverCambios('mantener')">
                    Solo Reemplazar Archivos
                </button>
            </div>
        </div>
    </div>
</div>