<aside class="alm-sidebar">
    {{-- ── LEYENDA DE ESTADOS DE MODELO ───────────────────────── --}}
    <div class="alm-filters-card cal-margin-bottom-2em cal-background-rgba-255-255-255-0-95 cal-backdrop-filter-blur-10px cal-border-radius-12px cal-box-shadow-0-4px-15px-rgba-0-0-0-0-08 cal-position-relative cal-padding-1-6em">
        <div class="cal-display-flex cal-align-items-center cal-gap-12px cal-margin-bottom-16px cal-border-bottom-2px-solid-e2e8f0 cal-padding-bottom-12px">
            <img src="{{ asset('images/Quality.png') }}" alt="Leyenda" class="cal-width-30px cal-height-30px cal-object-fit-contain" />
            <h2 class="cal-margin-0 cal-font-size-1-3rem cal-color-0f172a cal-font-weight-700">
                Guía de Estados de Modelo
            </h2>
        </div>
        <h3 class="cal-font-size-0-92rem cal-color-475569 cal-font-weight-700 cal-margin-0-0-10px-0 cal-border-left-4px-solid-94a3b8 cal-padding-left-8px">
            Estados de Transición
        </h3>
        <div class="legend-grid-compact cal-display-flex cal-flex-wrap-wrap cal-justify-content-center cal-gap-8px cal-margin-bottom-20px">
            <div class="legend-compact-item cal-width-calc-33-33-6pxpct cal-display-flex cal-flex-direction-column cal-align-items-center cal-padding-10px-6px cal-background-f8fafc cal-border-1-5px-solid-e2e8f0 cal-border-radius-8px cal-min-height-102px cal-justify-content-center">
                <span class="cal-display-flex cal-background-f1f5f9 cal-border-2px-solid-cbd5e1 cal-align-items-center cal-justify-content-center cal-width-54px cal-height-54px cal-border-radius-50pct cal-box-shadow-0-2px-4px-rgba-0-0-0-0-04 cal-flex-shrink-0">
                    <img src="{{ asset('images/Recibido.png') }}" class="cal-width-28px cal-height-28px cal-object-fit-contain" />
                </span>
                <span class="cal-font-size-0-8rem cal-color-475569 cal-font-weight-700 cal-margin-top-7px cal-text-align-center cal-line-height-1-1">Nuevo</span>
            </div>
            <div class="legend-compact-item cal-width-calc-33-33-6pxpct cal-display-flex cal-flex-direction-column cal-align-items-center cal-padding-10px-6px cal-background-f8fafc cal-border-1-5px-solid-e2e8f0 cal-border-radius-8px cal-min-height-102px cal-justify-content-center">
                <span class="cal-display-flex cal-background-fffbeb cal-border-2px-solid-f59e0b cal-align-items-center cal-justify-content-center cal-width-54px cal-height-54px cal-border-radius-50pct cal-box-shadow-0-2px-4px-rgba-0-0-0-0-04 cal-flex-shrink-0">
                    <img src="{{ asset('images/Revisando.png') }}" class="cal-width-28px cal-height-28px cal-object-fit-contain" />
                </span>
                <span class="cal-font-size-0-8rem cal-color-b45309 cal-font-weight-700 cal-margin-top-7px cal-text-align-center cal-line-height-1-1">En Revisión</span>
            </div>
        </div>
        <h3 class="cal-font-size-0-92rem cal-color-0f172a cal-font-weight-700 cal-margin-0-0-10px-0 cal-border-left-4px-solid-3b82f6 cal-padding-left-8px">
            Estados Prioritarios
        </h3>
        <div class="legend-grid-compact cal-display-flex cal-flex-wrap-wrap cal-justify-content-center cal-gap-8px">
            <div class="legend-compact-item cal-width-calc-33-33-6pxpct cal-display-flex cal-flex-direction-column cal-align-items-center cal-padding-10px-6px cal-background-f8fafc cal-border-1-5px-solid-e2e8f0 cal-border-radius-8px cal-min-height-102px cal-justify-content-center">
                <span class="cal-display-flex cal-background-eff6ff cal-border-2px-solid-60a5fa cal-align-items-center cal-justify-content-center cal-width-54px cal-height-54px cal-border-radius-50pct cal-box-shadow-0-2px-4px-rgba-0-0-0-0-04 cal-flex-shrink-0">
                    <img src="{{ asset('images/pdf-view.png') }}" class="cal-width-28px cal-height-28px cal-object-fit-contain" />
                </span>
                <span class="cal-font-size-0-8rem cal-color-2563eb cal-font-weight-700 cal-margin-top-7px cal-text-align-center cal-line-height-1-1">Pre-Orden</span>
            </div>
            <div class="legend-compact-item cal-width-calc-33-33-6pxpct cal-display-flex cal-flex-direction-column cal-align-items-center cal-padding-10px-6px cal-background-f8fafc cal-border-1-5px-solid-e2e8f0 cal-border-radius-8px cal-min-height-102px cal-justify-content-center">
                <span class="cal-display-flex cal-background-f0f9ff cal-border-2px-solid-0ea5e9 cal-align-items-center cal-justify-content-center cal-width-54px cal-height-54px cal-border-radius-50pct cal-box-shadow-0-2px-4px-rgba-0-0-0-0-04 cal-flex-shrink-0">
                    <img src="{{ asset('images/Espera.png') }}" class="cal-width-28px cal-height-28px cal-object-fit-contain" />
                </span>
                <span class="cal-font-size-0-8rem cal-color-0369a1 cal-font-weight-700 cal-margin-top-7px cal-text-align-center cal-line-height-1-1">Tengo Modelo</span>
            </div>
            <div class="legend-compact-item cal-width-calc-33-33-6pxpct cal-display-flex cal-flex-direction-column cal-align-items-center cal-padding-10px-6px cal-background-f8fafc cal-border-1-5px-solid-e2e8f0 cal-border-radius-8px cal-min-height-102px cal-justify-content-center">
                <span class="cal-display-flex cal-background-ecfdf5 cal-border-2px-solid-10b981 cal-align-items-center cal-justify-content-center cal-width-54px cal-height-54px cal-border-radius-50pct cal-box-shadow-0-2px-4px-rgba-0-0-0-0-04 cal-flex-shrink-0">
                    <img src="{{ asset('images/Quality.png') }}" class="cal-width-28px cal-height-28px cal-object-fit-contain" />
                </span>
                <span class="cal-font-size-0-8rem cal-color-047857 cal-font-weight-700 cal-margin-top-7px cal-text-align-center cal-line-height-1-1">Aprobado</span>
            </div>
            <div class="legend-compact-item cal-width-calc-33-33-6pxpct cal-display-flex cal-flex-direction-column cal-align-items-center cal-padding-10px-6px cal-background-f8fafc cal-border-1-5px-solid-e2e8f0 cal-border-radius-8px cal-min-height-102px cal-justify-content-center">
                <span class="cal-display-flex cal-background-fef2f2 cal-border-2px-solid-ef4444 cal-align-items-center cal-justify-content-center cal-width-54px cal-height-54px cal-border-radius-50pct cal-box-shadow-0-2px-4px-rgba-0-0-0-0-04 cal-flex-shrink-0">
                    <img src="{{ asset('images/Quality.png') }}" class="cal-width-28px cal-height-28px cal-object-fit-contain" />
                </span>
                <span class="cal-font-size-0-8rem cal-color-b91c1c cal-font-weight-700 cal-margin-top-7px cal-text-align-center cal-line-height-1-1">Rechazado</span>
            </div>
            <div class="legend-compact-item cal-width-calc-33-33-6pxpct cal-display-flex cal-flex-direction-column cal-align-items-center cal-padding-10px-6px cal-background-f8fafc cal-border-1-5px-solid-e2e8f0 cal-border-radius-8px cal-min-height-102px cal-justify-content-center">
                <span class="cal-display-flex cal-background-fef9c3 cal-border-2px-solid-eab308 cal-align-items-center cal-justify-content-center cal-width-54px cal-height-54px cal-border-radius-50pct cal-box-shadow-0-2px-4px-rgba-0-0-0-0-04 cal-flex-shrink-0">
                    <img src="{{ asset('images/Quality.png') }}" class="cal-width-28px cal-height-28px cal-object-fit-contain" />
                </span>
                <span class="cal-font-size-0-8rem cal-color-854d0e cal-font-weight-700 cal-margin-top-7px cal-text-align-center cal-line-height-1-1">Mixto</span>
            </div>
            <div class="legend-compact-item cal-width-calc-33-33-6pxpct cal-display-flex cal-flex-direction-column cal-align-items-center cal-padding-10px-6px cal-background-f8fafc cal-border-1-5px-solid-e2e8f0 cal-border-radius-8px cal-min-height-102px cal-justify-content-center">
                <span class="cal-display-flex cal-background-f0fdf4 cal-border-2px-solid-059669 cal-align-items-center cal-justify-content-center cal-width-54px cal-height-54px cal-border-radius-50pct cal-box-shadow-0-2px-4px-rgba-0-0-0-0-04 cal-flex-shrink-0">
                    <img src="{{ asset('images/pdf-view.png') }}" class="cal-width-28px cal-height-28px cal-object-fit-contain" />
                </span>
                <span class="cal-font-size-0-8rem cal-color-15803d cal-font-weight-700 cal-margin-top-7px cal-text-align-center cal-line-height-1-1">Casting</span>
            </div>
            <div class="legend-compact-item cal-width-calc-33-33-6pxpct cal-display-flex cal-flex-direction-column cal-align-items-center cal-padding-10px-6px cal-background-f8fafc cal-border-1-5px-solid-e2e8f0 cal-border-radius-8px cal-min-height-102px cal-justify-content-center">
                <span class="cal-display-flex cal-background-fdf2f8 cal-border-2px-solid-ec4899 cal-align-items-center cal-justify-content-center cal-width-54px cal-height-54px cal-border-radius-50pct cal-box-shadow-0-2px-4px-rgba-0-0-0-0-04 cal-flex-shrink-0">
                    <img src="{{ asset('images/Reproceso.png') }}" class="cal-width-28px cal-height-28px cal-object-fit-contain" />
                </span>
                <span class="cal-font-size-0-8rem cal-color-be185d cal-font-weight-700 cal-margin-top-7px cal-text-align-center cal-line-height-1-1">Reproceso</span>
            </div>
            <div class="legend-compact-item cal-width-calc-33-33-6pxpct cal-display-flex cal-flex-direction-column cal-align-items-center cal-padding-10px-6px cal-background-f8fafc cal-border-1-5px-solid-e2e8f0 cal-border-radius-8px cal-min-height-102px cal-justify-content-center">
                <span class="cal-display-flex cal-background-f3e8ff cal-border-2px-solid-9333ea cal-align-items-center cal-justify-content-center cal-width-54px cal-height-54px cal-border-radius-50pct cal-box-shadow-0-2px-4px-rgba-0-0-0-0-04 cal-flex-shrink-0">
                    <img src="{{ asset('images/Proveedor.png') }}" class="cal-width-28px cal-height-28px cal-object-fit-contain" />
                </span>
                <span class="cal-font-size-0-8rem cal-color-9333ea cal-font-weight-700 cal-margin-top-7px cal-text-align-center cal-line-height-1-1">Enviado a Proveedor</span>
            </div>
            <div class="legend-compact-item cal-width-calc-33-33-6pxpct cal-display-flex cal-flex-direction-column cal-align-items-center cal-padding-10px-6px cal-background-f8fafc cal-border-1-5px-solid-e2e8f0 cal-border-radius-8px cal-min-height-102px cal-justify-content-center">
                <span class="cal-display-flex cal-background-ecfdf5 cal-border-2px-solid-10b981 cal-align-items-center cal-justify-content-center cal-width-54px cal-height-54px cal-border-radius-50pct cal-box-shadow-0-2px-4px-rgba-0-0-0-0-04 cal-flex-shrink-0">
                    <img src="{{ asset('images/Aprobado.png') }}" class="cal-width-28px cal-height-28px cal-object-fit-contain" />
                </span>
                <span class="cal-font-size-0-8rem cal-color-047857 cal-font-weight-700 cal-margin-top-7px cal-text-align-center cal-line-height-1-1">Aprobado Final</span>
            </div>
            <div class="legend-compact-item cal-width-calc-33-33-6pxpct cal-display-flex cal-flex-direction-column cal-align-items-center cal-padding-10px-6px cal-background-f8fafc cal-border-1-5px-solid-e2e8f0 cal-border-radius-8px cal-min-height-102px cal-justify-content-center">
                <span class="cal-display-flex cal-background-fef2f2 cal-border-2px-solid-dc2626 cal-align-items-center cal-justify-content-center cal-width-54px cal-height-54px cal-border-radius-50pct cal-box-shadow-0-2px-4px-rgba-0-0-0-0-04 cal-flex-shrink-0">
                    <img src="{{ asset('images/Rechazado.png') }}" class="cal-width-28px cal-height-28px cal-object-fit-contain" />
                </span>
                <span class="cal-font-size-0-8rem cal-color-b91c1c cal-font-weight-700 cal-margin-top-7px cal-text-align-center cal-line-height-1-1">Rechazado Final</span>
            </div>
        </div>
    </div>
    <style>
        .legend-compact-item {
            transition: all 0.2s ease-in-out !important;
            cursor: help;
        }

        .legend-compact-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
        }

        @keyframes almFadeIn {
            from {
                opacity: 0;
                transform: translateY(4px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
    <!-- Zoom Tooltip Flotante (Mercado Libre / Amazon Style) -->
    <div id="legend-zoom-tooltip" class="cal-position-fixed cal-display-none cal-pointer-events-none cal-z-index-99999 cal-background-rgba-255-255-255-0-98 cal-backdrop-filter-blur-10px cal-border-radius-12px cal-box-shadow-0-10px-25px-rgba-0-0-0-0-15 cal-border-3px-solid-cbd5e1 cal-padding-16px cal-width-170px cal-height-180px cal-flex-direction-column cal-align-items-center cal-justify-content-center cal-box-sizing-border-box cal-transition-transform-0-15s-cubic-bezier-0-175-0-885-0-32-1-25-opacity-0-15s-ease cal-opacity-0 cal-transform-scale-0-9 cal-font-family-quot">
        <span id="legend-zoom-circle" class="cal-display-flex cal-align-items-center cal-justify-content-center cal-width-90px cal-height-90px cal-border-radius-50pct cal-box-shadow-0-4px-8px-rgba-0-0-0-0-06 cal-flex-shrink-0 cal-border-3px-solid-transparent">
            <img id="legend-zoom-img" src="" class="cal-width-55px cal-height-55px cal-object-fit-contain" />
        </span>
        <span id="legend-zoom-label" class="cal-font-size-1-08rem cal-font-weight-800 cal-margin-top-10px cal-text-align-center cal-line-height-1-2"></span>
    </div>
    <script>
        function initLegendZoom() {
            const tooltip = document.getElementById("legend-zoom-tooltip");
            const zoomCircle = document.getElementById("legend-zoom-circle");
            const zoomImg = document.getElementById("legend-zoom-img");
            const zoomLabel = document.getElementById("legend-zoom-label");
            
            if (!tooltip) return;
            
            document.querySelectorAll(".legend-compact-item").forEach((item) => {
                item.addEventListener("mouseenter", (e) => {
                    const circle = item.querySelector("span");
                    const img = circle ? circle.querySelector("img") : null;
                    const label = item.querySelectorAll("span")[1];
                    
                    if (!circle || !img || !label) return;
                    
                    // Extract styles
                    const bgColor = circle.style.backgroundColor || window.getComputedStyle(circle).backgroundColor;
                    const borderColor = circle.style.borderColor || window.getComputedStyle(circle).borderColor;
                    const textColor = label.style.color || window.getComputedStyle(label).color;
                    const imgSrc = img.src;
                    const textContent = label.textContent;
                    
                    // Apply to tooltip
                    tooltip.style.borderColor = borderColor;
                    zoomCircle.style.backgroundColor = bgColor;
                    zoomCircle.style.borderColor = borderColor;
                    zoomCircle.style.borderStyle = "solid";
                    zoomCircle.style.borderWidth = "3px";
                    zoomImg.src = imgSrc;
                    zoomLabel.textContent = textContent;
                    zoomLabel.style.color = textColor;
                    tooltip.style.display = "flex";
                    
                    // Trigger animation frame for fade-in transition
                    requestAnimationFrame(() => {
                        tooltip.style.opacity = "1";
                        tooltip.style.transform = "scale(1.05)";
                    });
                });
                
                item.addEventListener("mousemove", (e) => {
                    const offsetX = 20;
                    const offsetY = 20;
                    let posX = e.clientX + offsetX;
                    let posY = e.clientY + offsetY;
                    
                    // Boundary checks
                    const tooltipWidth = 170;
                    const tooltipHeight = 180;
                    
                    if (posX + tooltipWidth > window.innerWidth - 10) {
                        posX = e.clientX - tooltipWidth - offsetX;
                    }
                    if (posY + tooltipHeight > window.innerHeight - 10) {
                        posY = e.clientY - tooltipHeight - offsetY;
                    }
                    
                    tooltip.style.left = `${posX}px`;
                    tooltip.style.top = `${posY}px`;
                });
                
                item.addEventListener("mouseleave", () => {
                    tooltip.style.opacity = "0";
                    tooltip.style.transform = "scale(0.95)";
                    // Hide after transition
                    setTimeout(() => {
                        if (tooltip.style.opacity === "0") {
                            tooltip.style.display = "none";
                        }
                    }, 100);
                });
            });
        }
        
        if (document.readyState !== "loading") {
            initLegendZoom();
        } else {
            document.addEventListener("DOMContentLoaded", initLegendZoom);
        }
    </script>
</aside>
