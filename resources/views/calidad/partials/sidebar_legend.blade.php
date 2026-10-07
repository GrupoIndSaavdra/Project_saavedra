<aside class="alm-sidebar">
    <div class="cal-margin-bottom-2em cal-background-rgba-255-255-255-0-95 cal-backdrop-filter-blur-10px cal-box-shadow-0-4px-15px-rgba-0-0-0-0-08 cal-position-relative cal-padding-1-6em" style="border: 5px solid #033966d4; border-radius: 12px;">
        <div class="cal-display-flex cal-align-items-center cal-gap-12px cal-margin-bottom-16px cal-border-bottom-2px-solid-e2e8f0 cal-padding-bottom-12px">
            <img src="{{ asset('images/Quality.png') }}" alt="Leyenda" class="cal-width-30px cal-height-30px cal-object-fit-contain">
            <h2 class="cal-margin-0 cal-font-size-1-3rem cal-color-0f172a cal-font-weight-700">Guía de Estados: Calidad</h2>
        </div>

        <h3 class="cal-font-size-0-92rem cal-color-475569 cal-font-weight-700 cal-margin-0-0-10px-0 cal-border-left-4px-solid-94a3b8 cal-padding-left-8px">
            Estados Comunes
        </h3>
        <div class="legend-grid-compact cal-display-flex cal-flex-wrap-wrap cal-justify-content-center cal-margin-bottom-10px" style="gap: 8px 4px;">
            <div class="legend-compact-item cal-width-calc-33-33-6pxpct cal-display-flex cal-flex-direction-column cal-align-items-center cal-padding-8px-2px cal-justify-content-center" title="Correo de notificación recibido, listo para revisión de Calidad" style="--hover-border-color: #64748b; margin-bottom: 12px;">
                <span class="cal-display-flex cal-align-items-center cal-justify-content-center cal-border-radius-50pct cal-flex-shrink-0 cal-position-relative" style="width: 68px; height: 68px; background-color: #f1f5f9; border: 2px solid #64748b;">
                    <img src="{{ asset('images/por_liberar_icon.png') }}" alt="Por Liberar" style="width: 40px; height: 40px; object-fit: contain;" />
                </span>
                <span style="font-size: 0.78rem; font-weight: 700; margin-top: 8px; text-align: center; line-height: 1.1; color: #334155; text-transform: uppercase;">Por Liberar</span>
            </div>
            <div class="legend-compact-item cal-width-calc-33-33-6pxpct cal-display-flex cal-flex-direction-column cal-align-items-center cal-padding-8px-2px cal-justify-content-center" title="En espera de la otra área" style="--hover-border-color: #a3a3a3; margin-bottom: 12px;">
                <span class="cal-display-flex cal-align-items-center cal-justify-content-center cal-border-radius-50pct cal-flex-shrink-0 cal-position-relative" style="width: 68px; height: 68px; background-color: #fafafa; border: 2px solid #a3a3a3;">
                    <img src="{{ asset('images/Espera.png') }}" alt="En Espera" style="width: 40px; height: 40px; object-fit: contain;" />
                </span>
                <span style="font-size: 0.78rem; font-weight: 700; margin-top: 8px; text-align: center; line-height: 1.1; color: #525252; text-transform: uppercase;">En Espera</span>
            </div>
            <div class="legend-compact-item cal-width-calc-33-33-6pxpct cal-display-flex cal-flex-direction-column cal-align-items-center cal-padding-8px-2px cal-justify-content-center" title="Calidad o Almacén está realizando la revisión" style="--hover-border-color: #f59e0b; margin-bottom: 12px;">
                <span class="cal-display-flex cal-align-items-center cal-justify-content-center cal-border-radius-50pct cal-flex-shrink-0 cal-position-relative" style="width: 68px; height: 68px; background-color: #fffbeb; border: 2px solid #f59e0b;">
                    <img src="{{ asset('images/Revisando.png') }}" alt="En Revisión" style="width: 40px; height: 40px; object-fit: contain;" />
                </span>
                <span style="font-size: 0.78rem; font-weight: 700; margin-top: 8px; text-align: center; line-height: 1.1; color: #b45309; text-transform: uppercase;">En Revisión</span>
            </div>
        </div>

        <h3 class="cal-font-size-0-92rem cal-color-0f172a cal-font-weight-700 cal-margin-0-0-10px-0 cal-border-left-4px-solid-3b82f6 cal-padding-left-8px">
            Operación y Logística
        </h3>
        <div class="legend-grid-compact cal-display-flex cal-flex-wrap-wrap cal-justify-content-center cal-margin-bottom-10px" style="gap: 8px 4px;">
            <div class="legend-compact-item cal-width-calc-33-33-6pxpct cal-display-flex cal-flex-direction-column cal-align-items-center cal-padding-8px-2px cal-justify-content-center" title="Modelo aprobado y liberado por Calidad (Formato LDM)" style="--hover-border-color: #10b981; margin-bottom: 12px;">
                <span class="cal-display-flex cal-align-items-center cal-justify-content-center cal-border-radius-50pct cal-flex-shrink-0 cal-position-relative" style="width: 68px; height: 68px; background-color: #ecfdf5; border: 2px solid #10b981;">
                    <img src="{{ asset('images/LDM-icon.png') }}" alt="Formato LDM" style="width: 40px; height: 40px; object-fit: contain;" />
                </span>
                <span style="font-size: 0.78rem; font-weight: 700; margin-top: 8px; text-align: center; line-height: 1.1; color: #047857; text-transform: uppercase;">Formato LDM</span>
            </div>
            <div class="legend-compact-item cal-width-calc-33-33-6pxpct cal-display-flex cal-flex-direction-column cal-align-items-center cal-padding-8px-2px cal-justify-content-center" title="Modelo rechazado por Calidad (Formato RDM/SCAR)" style="--hover-border-color: #f43f5e; margin-bottom: 12px;">
                <span class="cal-display-flex cal-align-items-center cal-justify-content-center cal-border-radius-50pct cal-flex-shrink-0 cal-position-relative" style="width: 68px; height: 68px; background-color: #fff1f2; border: 2px solid #f43f5e;">
                    <img src="{{ asset('images/RDM-SCAR-icon.png') }}" alt="Formato RDM y SCAR" style="width: 40px; height: 40px; object-fit: contain;" />
                </span>
                <span style="font-size: 0.78rem; font-weight: 700; margin-top: 8px; text-align: center; line-height: 1.1; color: #be123c; text-transform: uppercase;">Formato RDM y SCAR</span>
            </div>
            <div class="legend-compact-item cal-width-calc-33-33-6pxpct cal-display-flex cal-flex-direction-column cal-align-items-center cal-padding-8px-2px cal-justify-content-center" title="Formatos LDM y RDM/SCAR generados, pendiente de enviar la alerta" style="--hover-border-color: #ca8a04; margin-bottom: 12px;">
                <span class="cal-display-flex cal-align-items-center cal-justify-content-center cal-border-radius-50pct cal-flex-shrink-0 cal-position-relative" style="width: 68px; height: 68px; background-color: #fef9c3; border: 2px solid #ca8a04;">
                    <img src="{{ asset('images/Formatos_Mixtos_icon.png') }}" alt="Formatos Mixtos" style="width: 40px; height: 40px; object-fit: contain;" />
                </span>
                <span style="font-size: 0.78rem; font-weight: 700; margin-top: 8px; text-align: center; line-height: 1.1; color: #713f12; text-transform: uppercase;">Formatos Mixtos</span>
            </div>
            <div class="legend-compact-item cal-width-calc-33-33-6pxpct cal-display-flex cal-flex-direction-column cal-align-items-center cal-padding-8px-2px cal-justify-content-center" title="Dictamen enviado a Almacén" style="--hover-border-color: #3b82f6; margin-bottom: 12px;">
                <span class="cal-display-flex cal-align-items-center cal-justify-content-center cal-border-radius-50pct cal-flex-shrink-0 cal-position-relative" style="width: 68px; height: 68px; background-color: #eff6ff; border: 2px solid #3b82f6;">
                    <img src="{{ asset('images/almacen.png') }}" alt="En Almacén" style="width: 40px; height: 40px; object-fit: contain;" />
                </span>
                <span style="font-size: 0.78rem; font-weight: 700; margin-top: 8px; text-align: center; line-height: 1.1; color: #1d4ed8; text-transform: uppercase;">En Almacén</span>
            </div>
            <div class="legend-compact-item cal-width-calc-33-33-6pxpct cal-display-flex cal-flex-direction-column cal-align-items-center cal-padding-8px-2px cal-justify-content-center" title="Proceso parcial, esperando las demás clases" style="--hover-border-color: #f97316; margin-bottom: 12px;">
                <span class="cal-display-flex cal-align-items-center cal-justify-content-center cal-border-radius-50pct cal-flex-shrink-0 cal-position-relative" style="width: 68px; height: 68px; background-color: #fff7ed; border: 2px solid #f97316;">
                    <img src="{{ asset('images/proceso_parcial-icon.png') }}" alt="Proceso Parcial" style="width: 40px; height: 40px; object-fit: contain;" />
                </span>
                <span style="font-size: 0.78rem; font-weight: 700; margin-top: 8px; text-align: center; line-height: 1.1; color: #c2410c; text-transform: uppercase;">Proceso Parcial</span>
            </div>
        </div>

        <h3 class="cal-font-size-0-92rem cal-color-0f172a cal-font-weight-700 cal-margin-0-0-10px-0 cal-border-left-4px-solid-f59e0b cal-padding-left-8px">
            Revisión y Dictamen de Calidad
        </h3>
        <div class="legend-grid-compact cal-display-flex cal-flex-wrap-wrap cal-justify-content-center cal-margin-bottom-10px" style="gap: 8px 4px;">
            <div class="legend-compact-item cal-width-calc-33-33-6pxpct cal-display-flex cal-flex-direction-column cal-align-items-center cal-padding-8px-2px cal-justify-content-center" title="OT aprobada y liberada por Calidad" style="--hover-border-color: #22c55e; margin-bottom: 12px;">
                <span class="cal-display-flex cal-align-items-center cal-justify-content-center cal-border-radius-50pct cal-flex-shrink-0 cal-position-relative" style="width: 68px; height: 68px; background-color: #f0fdf4; border: 2px solid #22c55e;">
                    <img src="{{ asset('images/Aprobado.png') }}" alt="Liberado" style="width: 40px; height: 40px; object-fit: contain;" />
                </span>
                <span style="font-size: 0.78rem; font-weight: 700; margin-top: 8px; text-align: center; line-height: 1.1; color: #15803d; text-transform: uppercase;">Liberado</span>
            </div>
            <div class="legend-compact-item cal-width-calc-33-33-6pxpct cal-display-flex cal-flex-direction-column cal-align-items-center cal-padding-8px-2px cal-justify-content-center" title="OT rechazada por Calidad" style="--hover-border-color: #ef4444; margin-bottom: 12px;">
                <span class="cal-display-flex cal-align-items-center cal-justify-content-center cal-border-radius-50pct cal-flex-shrink-0 cal-position-relative" style="width: 68px; height: 68px; background-color: #fef2f2; border: 2px solid #ef4444;">
                    <img src="{{ asset('images/Rechazado.png') }}" alt="Rechazado" style="width: 40px; height: 40px; object-fit: contain;" />
                </span>
                <span style="font-size: 0.78rem; font-weight: 700; margin-top: 8px; text-align: center; line-height: 1.1; color: #b91c1c; text-transform: uppercase;">Rechazado</span>
            </div>
            <div class="legend-compact-item cal-width-calc-33-33-6pxpct cal-display-flex cal-flex-direction-column cal-align-items-center cal-padding-8px-2px cal-justify-content-center" title="Liberación mixta por Calidad" style="--hover-border-color: #d97706; margin-bottom: 12px;">
                <span class="cal-display-flex cal-align-items-center cal-justify-content-center cal-border-radius-50pct cal-flex-shrink-0 cal-position-relative" style="width: 68px; height: 68px; background-color: #fef3c7; border: 2px solid #d97706;">
                    <img src="{{ asset('images/Mixto.png') }}" alt="Mixto" style="width: 40px; height: 40px; object-fit: contain;" />
                </span>
                <span style="font-size: 0.78rem; font-weight: 700; margin-top: 8px; text-align: center; line-height: 1.1; color: #b45309; text-transform: uppercase;">Mixto</span>
            </div>
        </div>

    </div>
    <style>
        .legend-compact-item { 
            transition: all 0.2s ease-in-out !important; 
            cursor: help; 
            border: 2px solid transparent;
            border-radius: 12px;
        }
        .legend-compact-item:hover { 
            transform: scale(1.05) translateY(-2px); 
            border-color: var(--hover-border-color, #cbd5e1);
        }
    </style>

    <!-- Zoom Tooltip Flotante (Mercado Libre / Amazon Style) -->
    <div id="legend-zoom-tooltip" class="cal-position-fixed cal-display-none cal-pointer-events-none cal-z-index-99999 cal-background-rgba-255-255-255-0-98 cal-backdrop-filter-blur-10px cal-border-radius-12px cal-box-shadow-0-10px-25px-rgba-0-0-0-0-15 cal-border-3px-solid-cbd5e1 cal-padding-16px cal-width-220px cal-min-height-180px cal-flex-direction-column cal-align-items-center cal-justify-content-center cal-box-sizing-border-box cal-transition-transform-0-15s-cubic-bezier-0-175-0-885-0-32-1-25-opacity-0-15s-ease cal-opacity-0 cal-transform-scale-0-9 cal-font-family-Poppins-sans-serif">
        <span id="legend-zoom-circle" class="cal-display-flex cal-align-items-center cal-justify-content-center cal-width-90px cal-height-90px cal-border-radius-50pct cal-box-shadow-0-4px-8px-rgba-0-0-0-0-06 cal-flex-shrink-0 cal-border-3px-solid-transparent">
            <img id="legend-zoom-img" src="" class="cal-width-55px cal-height-55px cal-object-fit-contain">
        </span>
        <span id="legend-zoom-label" class="cal-font-size-1-08rem cal-font-weight-800 cal-margin-top-10px cal-text-align-center cal-line-height-1-2"></span>
        <span id="legend-zoom-desc" class="cal-font-size-0-85rem cal-color-475569 cal-font-weight-500 cal-margin-top-8px cal-text-align-center cal-line-height-1-4"></span>
    </div>

</aside>
