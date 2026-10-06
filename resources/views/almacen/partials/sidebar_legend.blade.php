<aside class="alm-sidebar">
    <div class="alm-margin-bottom-2em alm-background-rgba-255-255-255-0-95 alm-backdrop-filter-blur-10px alm-box-shadow-0-4px-15px-rgba-0-0-0-0-08 alm-position-relative alm-padding-1-6em" style="border: 5px solid #033966d4; border-radius: 12px;">
        <div class="alm-display-flex alm-align-items-center alm-gap-12px alm-margin-bottom-16px alm-border-bottom-2px-solid-e2e8f0 alm-padding-bottom-12px">
            <img src="{{ asset('images/Quality.png') }}" alt="Leyenda" class="alm-width-30px alm-height-30px alm-object-fit-contain">
            <h2 class="alm-margin-0 alm-font-size-1-3rem alm-color-0f172a alm-font-weight-700">Guía de Estados: Almacén</h2>
        </div>

        <h3 class="alm-font-size-0-92rem alm-color-475569 alm-font-weight-700 alm-margin-0-0-10px-0 alm-border-left-4px-solid-94a3b8 alm-padding-left-8px">
            Estados Comunes
        </h3>
        <div class="legend-grid-compact alm-display-flex alm-flex-wrap-wrap alm-justify-content-center alm-margin-bottom-10px" style="gap: 8px 4px;">
            <div class="legend-compact-item alm-width-calc-33-33-6pxpct alm-display-flex alm-flex-direction-column alm-align-items-center alm-padding-8px-2px alm-justify-content-center" title="Nueva OT recibida sin acciones registradas" style="--hover-border-color: #0ea5e9; margin-bottom: 12px;">
                <span class="alm-display-flex alm-align-items-center alm-justify-content-center alm-border-radius-50pct alm-flex-shrink-0 alm-position-relative" style="width: 68px; height: 68px; background-color: #f0f9ff; border: 2px solid #0ea5e9;">
                    <img src="{{ asset('images/Recibido.png') }}" alt="Nuevo" style="width: 40px; height: 40px; object-fit: contain;" />
                </span>
                <span style="font-size: 0.78rem; font-weight: 700; margin-top: 8px; text-align: center; line-height: 1.1; color: #0369a1; text-transform: uppercase;">Nuevo</span>
            </div>
            <div class="legend-compact-item alm-width-calc-33-33-6pxpct alm-display-flex alm-flex-direction-column alm-align-items-center alm-padding-8px-2px alm-justify-content-center" title="En espera de la otra área" style="--hover-border-color: #a3a3a3; margin-bottom: 12px;">
                <span class="alm-display-flex alm-align-items-center alm-justify-content-center alm-border-radius-50pct alm-flex-shrink-0 alm-position-relative" style="width: 68px; height: 68px; background-color: #fafafa; border: 2px solid #a3a3a3;">
                    <img src="{{ asset('images/Espera.png') }}" alt="En Espera" style="width: 40px; height: 40px; object-fit: contain;" />
                </span>
                <span style="font-size: 0.78rem; font-weight: 700; margin-top: 8px; text-align: center; line-height: 1.1; color: #525252; text-transform: uppercase;">En Espera</span>
            </div>
            <div class="legend-compact-item alm-width-calc-33-33-6pxpct alm-display-flex alm-flex-direction-column alm-align-items-center alm-padding-8px-2px alm-justify-content-center" title="Calidad o Almacén está realizando la revisión" style="--hover-border-color: #f59e0b; margin-bottom: 12px;">
                <span class="alm-display-flex alm-align-items-center alm-justify-content-center alm-border-radius-50pct alm-flex-shrink-0 alm-position-relative" style="width: 68px; height: 68px; background-color: #fffbeb; border: 2px solid #f59e0b;">
                    <img src="{{ asset('images/Revisando.png') }}" alt="En Revisión" style="width: 40px; height: 40px; object-fit: contain;" />
                </span>
                <span style="font-size: 0.78rem; font-weight: 700; margin-top: 8px; text-align: center; line-height: 1.1; color: #b45309; text-transform: uppercase;">En Revisión</span>
            </div>
        </div>

        <h3 class="alm-font-size-0-92rem alm-color-0f172a alm-font-weight-700 alm-margin-0-0-10px-0 alm-border-left-4px-solid-3b82f6 alm-padding-left-8px">
            Operación y Logística
        </h3>
        <div class="legend-grid-compact alm-display-flex alm-flex-wrap-wrap alm-justify-content-center alm-margin-bottom-10px" style="gap: 8px 4px;">
            <div class="legend-compact-item alm-width-calc-33-33-6pxpct alm-display-flex alm-flex-direction-column alm-align-items-center alm-padding-8px-2px alm-justify-content-center" title="Modelo físico disponible, pendiente de procesar" style="--hover-border-color: #14b8a6; margin-bottom: 12px;">
                <span class="alm-display-flex alm-align-items-center alm-justify-content-center alm-border-radius-50pct alm-flex-shrink-0 alm-position-relative" style="width: 68px; height: 68px; background-color: #f0fdfa; border: 2px solid #14b8a6;">
                    <img src="{{ asset('images/perspectiva-icon.png') }}" alt="Tengo Modelo" style="width: 40px; height: 40px; object-fit: contain;" />
                </span>
                <span style="font-size: 0.78rem; font-weight: 700; margin-top: 8px; text-align: center; line-height: 1.1; color: #0f766e; text-transform: uppercase;">Tengo Modelo</span>
            </div>
            <div class="legend-compact-item alm-width-calc-33-33-6pxpct alm-display-flex alm-flex-direction-column alm-align-items-center alm-padding-8px-2px alm-justify-content-center" title="Pre-orden generada, pendiente de firmar y enviar" style="--hover-border-color: #8b5cf6; margin-bottom: 12px;">
                <span class="alm-display-flex alm-align-items-center alm-justify-content-center alm-border-radius-50pct alm-flex-shrink-0 alm-position-relative" style="width: 68px; height: 68px; background-color: #f5f3ff; border: 2px solid #8b5cf6;">
                    <img src="{{ asset('images/PFM-icon.png') }}" alt="Pre-Orden de Fabricación de Modelo" style="width: 40px; height: 40px; object-fit: contain;" />
                </span>
                <span style="font-size: 0.78rem; font-weight: 700; margin-top: 8px; text-align: center; line-height: 1.1; color: #6d28d9; text-transform: uppercase;">Pre-Orden de Fabricación de Modelo</span>
            </div>
            <div class="legend-compact-item alm-width-calc-33-33-6pxpct alm-display-flex alm-flex-direction-column alm-align-items-center alm-padding-8px-2px alm-justify-content-center" title="Pre-orden generada, pendiente de firmar y enviar" style="--hover-border-color: #84cc16; margin-bottom: 12px;">
                <span class="alm-display-flex alm-align-items-center alm-justify-content-center alm-border-radius-50pct alm-flex-shrink-0 alm-position-relative" style="width: 68px; height: 68px; background-color: #f7fee7; border: 2px solid #84cc16;">
                    <img src="{{ asset('images/PFC-icon.png') }}" alt="Pre-Orden de Fabricación de Casting" style="width: 40px; height: 40px; object-fit: contain;" />
                </span>
                <span style="font-size: 0.78rem; font-weight: 700; margin-top: 8px; text-align: center; line-height: 1.1; color: #4d7c0f; text-transform: uppercase;">Pre-Orden de Fabricación de Casting</span>
            </div>
            <div class="legend-compact-item alm-width-calc-33-33-6pxpct alm-display-flex alm-flex-direction-column alm-align-items-center alm-padding-8px-2px alm-justify-content-center" title="Se generó formato, pendiente de firma/envío" style="--hover-border-color: #06b6d4; margin-bottom: 12px;">
                <span class="alm-display-flex alm-align-items-center alm-justify-content-center alm-border-radius-50pct alm-flex-shrink-0 alm-position-relative" style="width: 68px; height: 68px; background-color: #ecfeff; border: 2px solid #06b6d4;">
                    <img src="{{ asset('images/Escanear-icon.png') }}" alt="Por Escanear" style="width: 40px; height: 40px; object-fit: contain;" />
                </span>
                <span style="font-size: 0.78rem; font-weight: 700; margin-top: 8px; text-align: center; line-height: 1.1; color: #0e7490; text-transform: uppercase;">Por Escanear</span>
            </div>
            <div class="legend-compact-item alm-width-calc-33-33-6pxpct alm-display-flex alm-flex-direction-column alm-align-items-center alm-padding-8px-2px alm-justify-content-center" title="Proceso parcial, esperando las demás clases" style="--hover-border-color: #f97316; margin-bottom: 12px;">
                <span class="alm-display-flex alm-align-items-center alm-justify-content-center alm-border-radius-50pct alm-flex-shrink-0 alm-position-relative" style="width: 68px; height: 68px; background-color: #fff7ed; border: 2px solid #f97316;">
                    <img src="{{ asset('images/proceso_parcial-icon.png') }}" alt="Proceso Parcial" style="width: 40px; height: 40px; object-fit: contain;" />
                </span>
                <span style="font-size: 0.78rem; font-weight: 700; margin-top: 8px; text-align: center; line-height: 1.1; color: #c2410c; text-transform: uppercase;">Proceso Parcial</span>
            </div>
            <div class="legend-compact-item alm-width-calc-33-33-6pxpct alm-display-flex alm-flex-direction-column alm-align-items-center alm-padding-8px-2px alm-justify-content-center" title="Correo enviado, en espera de revisión por Calidad" style="--hover-border-color: #6366f1; margin-bottom: 12px;">
                <span class="alm-display-flex alm-align-items-center alm-justify-content-center alm-border-radius-50pct alm-flex-shrink-0 alm-position-relative" style="width: 68px; height: 68px; background-color: #eef2ff; border: 2px solid #6366f1;">
                    <img src="{{ asset('images/Quality.png') }}" alt="En Calidad" style="width: 40px; height: 40px; object-fit: contain;" />
                </span>
                <span style="font-size: 0.78rem; font-weight: 700; margin-top: 8px; text-align: center; line-height: 1.1; color: #4338ca; text-transform: uppercase;">En Calidad</span>
            </div>
            <div class="legend-compact-item alm-width-calc-33-33-6pxpct alm-display-flex alm-flex-direction-column alm-align-items-center alm-padding-8px-2px alm-justify-content-center" title="Pre-orden de casting enviada al proveedor, proceso finalizado" style="--hover-border-color: #9333ea; margin-bottom: 12px;">
                <span class="alm-display-flex alm-align-items-center alm-justify-content-center alm-border-radius-50pct alm-flex-shrink-0 alm-position-relative" style="width: 68px; height: 68px; background-color: #faf5ff; border: 2px solid #9333ea;">
                    <img src="{{ asset('images/Proveedor.png') }}" alt="Enviado a Proveedor" style="width: 40px; height: 40px; object-fit: contain;" />
                </span>
                <span style="font-size: 0.78rem; font-weight: 700; margin-top: 8px; text-align: center; line-height: 1.1; color: #7e22ce; text-transform: uppercase;">Enviado a Proveedor</span>
            </div>
            <div class="legend-compact-item alm-width-calc-33-33-6pxpct alm-display-flex alm-flex-direction-column alm-align-items-center alm-padding-8px-2px alm-justify-content-center" title="Retornado hacia un nuevo ciclo de modelo (Reproceso)" style="--hover-border-color: #ec4899; margin-bottom: 12px;">
                <span class="alm-display-flex alm-align-items-center alm-justify-content-center alm-border-radius-50pct alm-flex-shrink-0 alm-position-relative" style="width: 68px; height: 68px; background-color: #fdf2f8; border: 2px solid #ec4899;">
                    <img src="{{ asset('images/Reproceso.png') }}" alt="Reproceso" style="width: 40px; height: 40px; object-fit: contain;" />
                </span>
                <span style="font-size: 0.78rem; font-weight: 700; margin-top: 8px; text-align: center; line-height: 1.1; color: #be185d; text-transform: uppercase;">Reproceso</span>
            </div>
        </div>

        <h3 class="alm-font-size-0-92rem alm-color-0f172a alm-font-weight-700 alm-margin-0-0-10px-0 alm-border-left-4px-solid-f59e0b alm-padding-left-8px">
            Revisión y Dictamen de Calidad
        </h3>
        <div class="legend-grid-compact alm-display-flex alm-flex-wrap-wrap alm-justify-content-center alm-margin-bottom-10px" style="gap: 8px 4px;">
            <div class="legend-compact-item alm-width-calc-33-33-6pxpct alm-display-flex alm-flex-direction-column alm-align-items-center alm-padding-8px-2px alm-justify-content-center" title="OT aprobada y liberada por Calidad" style="--hover-border-color: #22c55e; margin-bottom: 12px;">
                <span class="alm-display-flex alm-align-items-center alm-justify-content-center alm-border-radius-50pct alm-flex-shrink-0 alm-position-relative" style="width: 68px; height: 68px; background-color: #f0fdf4; border: 2px solid #22c55e;">
                    <img src="{{ asset('images/Aprobado.png') }}" alt="Liberado" style="width: 40px; height: 40px; object-fit: contain;" />
                </span>
                <span style="font-size: 0.78rem; font-weight: 700; margin-top: 8px; text-align: center; line-height: 1.1; color: #15803d; text-transform: uppercase;">Liberado</span>
            </div>
            <div class="legend-compact-item alm-width-calc-33-33-6pxpct alm-display-flex alm-flex-direction-column alm-align-items-center alm-padding-8px-2px alm-justify-content-center" title="OT rechazada por Calidad" style="--hover-border-color: #ef4444; margin-bottom: 12px;">
                <span class="alm-display-flex alm-align-items-center alm-justify-content-center alm-border-radius-50pct alm-flex-shrink-0 alm-position-relative" style="width: 68px; height: 68px; background-color: #fef2f2; border: 2px solid #ef4444;">
                    <img src="{{ asset('images/Rechazado.png') }}" alt="Rechazado" style="width: 40px; height: 40px; object-fit: contain;" />
                </span>
                <span style="font-size: 0.78rem; font-weight: 700; margin-top: 8px; text-align: center; line-height: 1.1; color: #b91c1c; text-transform: uppercase;">Rechazado</span>
            </div>
            <div class="legend-compact-item alm-width-calc-33-33-6pxpct alm-display-flex alm-flex-direction-column alm-align-items-center alm-padding-8px-2px alm-justify-content-center" title="Liberación mixta por Calidad" style="--hover-border-color: #eab308; margin-bottom: 12px;">
                <span class="alm-display-flex alm-align-items-center alm-justify-content-center alm-border-radius-50pct alm-flex-shrink-0 alm-position-relative" style="width: 68px; height: 68px; background-color: #fefce8; border: 2px solid #eab308;">
                    <img src="{{ asset('images/Mixto.png') }}" alt="Mixto" style="width: 40px; height: 40px; object-fit: contain;" />
                </span>
                <span style="font-size: 0.78rem; font-weight: 700; margin-top: 8px; text-align: center; line-height: 1.1; color: #854d0e; text-transform: uppercase;">Mixto</span>
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
    <div id="legend-zoom-tooltip" class="alm-position-fixed alm-display-none alm-pointer-events-none alm-z-index-99999 alm-background-rgba-255-255-255-0-98 alm-backdrop-filter-blur-10px alm-border-radius-12px alm-box-shadow-0-10px-25px-rgba-0-0-0-0-15 alm-border-3px-solid-cbd5e1 alm-padding-16px alm-width-220px alm-min-height-180px alm-flex-direction-column alm-align-items-center alm-justify-content-center alm-box-sizing-border-box alm-transition-transform-0-15s-cubic-bezier-0-175-0-885-0-32-1-25-opacity-0-15s-ease alm-opacity-0 alm-transform-scale-0-9 alm-font-family-Poppins-sans-serif">
        <span id="legend-zoom-circle" class="alm-display-flex alm-align-items-center alm-justify-content-center alm-width-90px alm-height-90px alm-border-radius-50pct alm-box-shadow-0-4px-8px-rgba-0-0-0-0-06 alm-flex-shrink-0 alm-border-3px-solid-transparent">
            <img id="legend-zoom-img" src="" class="alm-width-55px alm-height-55px alm-object-fit-contain">
        </span>
        <span id="legend-zoom-label" class="alm-font-size-1-08rem alm-font-weight-800 alm-margin-top-10px alm-text-align-center alm-line-height-1-2"></span>
        <span id="legend-zoom-desc" class="alm-font-size-0-85rem alm-color-475569 alm-font-weight-500 alm-margin-top-8px alm-text-align-center alm-line-height-1-4"></span>
    </div>

</aside>
