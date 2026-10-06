<div id="flowchartModal" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(15, 23, 42, 0.6); z-index: 100000; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
    <div style="background: white; border-radius: 16px; width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto; padding: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); position: relative;">
        <!-- Botón de cerrar -->
        <button onclick="document.getElementById('flowchartModal').style.display='none'" style="position: absolute; top: 16px; right: 16px; background: #f1f5f9; border: none; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; font-weight: bold; color: #475569; display: flex; align-items: center; justify-content: center; transition: all 0.2s;" onmouseover="this.style.background='#e2e8f0'; this.style.color='#0f172a'" onmouseout="this.style.background='#f1f5f9'; this.style.color='#475569'">✕</button>

        <div class="legend-header" style="margin-bottom: 24px; border-bottom: 2px solid #e2e8f0; padding-bottom: 16px; display: flex; align-items: center; justify-content: center; gap: 12px;">
            <img src="{{ asset('images/Quality.png') }}" alt="Leyenda" style="width: 36px; height: 36px; object-fit: contain;">
            <h2 style="margin: 0; font-size: 1.4rem; color: #0f172a; font-weight: 800;">Diagrama de Flujo</h2>
        </div>

        <style>
            .fc-wrapper {
                display: flex;
                flex-direction: column;
                align-items: center;
                width: 100%;
                font-family: inherit;
                margin-top: 10px;
                padding-bottom: 20px;
            }
            .fc-node {
                display: flex;
                flex-direction: column;
                align-items: center;
                background: white;
                border-radius: 8px;
                padding: 10px;
                box-shadow: 0 4px 6px rgba(0,0,0,0.05);
                border: 2px solid #e2e8f0;
                width: 110px;
                text-align: center;
                z-index: 2;
                transition: transform 0.2s;
            }
            .fc-node:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 10px rgba(0,0,0,0.1);
            }
            .fc-node.dashed {
                border-style: dashed;
                background: #f8fafc;
            }
            .fc-icon-container {
                width: 44px;
                height: 44px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin-bottom: 6px;
            }
            .fc-icon {
                width: 26px;
                height: 26px;
                object-fit: contain;
            }
            .fc-text {
                font-size: 0.65rem;
                font-weight: 700;
                line-height: 1.15;
            }
            .fc-decision {
                border: 2px solid #f59e0b;
                background: #fffbeb;
                color: #b45309;
                padding: 10px;
                border-radius: 8px;
                font-size: 0.7rem;
                font-weight: 800;
                z-index: 2;
                text-align: center;
                transform: rotate(45deg);
                width: 60px;
                height: 60px;
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: 0 2px 5px rgba(245, 158, 11, 0.2);
            }
            .fc-decision span {
                transform: rotate(-45deg);
                display: block;
            }
            .fc-line-vertical {
                width: 2px;
                height: 25px;
                background: #cbd5e1;
            }
            .fc-line-dotted {
                width: 2px;
                height: 35px;
                border-left: 2px dashed #cbd5e1;
            }
            .fc-branches {
                display: flex;
                justify-content: center;
                width: 100%;
                position: relative;
                padding-top: 15px;
                align-items: stretch;
            }
            .fc-branches::before {
                content: '';
                position: absolute;
                top: 0;
                left: 25%;
                right: 25%;
                height: 2px;
                background: #cbd5e1;
            }
            .fc-branch {
                display: flex;
                flex-direction: column;
                align-items: center;
                width: 50%;
                position: relative;
            }
            .fc-branch::before {
                content: '';
                position: absolute;
                top: 0;
                left: 50%;
                width: 2px;
                height: 15px;
                background: #cbd5e1;
                transform: translateX(-50%);
            }
            .fc-branch-label {
                background: #f1f5f9;
                padding: 2px 8px;
                font-size: 0.65rem;
                font-weight: 800;
                border-radius: 10px;
                margin-top: 5px;
                margin-bottom: 15px;
                z-index: 2;
                color: #475569;
                border: 1px solid #cbd5e1;
            }
            .fc-merge {
                display: flex;
                justify-content: center;
                width: 100%;
                position: relative;
                height: 20px;
            }
            .fc-merge::before {
                content: '';
                position: absolute;
                bottom: 0;
                left: 25%;
                right: 25%;
                height: 2px;
                background: #cbd5e1;
            }
            
            /* Custom Scrollbar */
            .alm-sidebar::-webkit-scrollbar { width: 6px; }
            .alm-sidebar::-webkit-scrollbar-track { background: rgba(0,0,0,0.02); border-radius: 10px; }
            .alm-sidebar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
            .alm-sidebar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
            .legend-card-container {
                margin-bottom: 2em;
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(10px);
                border-radius: 12px;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
                padding: 1.2em;
            }
            .legend-header {
                display: flex; align-items: center; justify-content: center; gap: 12px;
                margin-bottom: 12px; border-bottom: 2px solid #e2e8f0; padding-bottom: 12px;
            }
            .legend-header-img { width: 30px; height: 30px; object-fit: contain; }
            .legend-header-title { margin: 0; font-size: 1.15rem; color: #0f172a; font-weight: 800; }
        </style>

        @if ($isAlmacen ?? false)
            <!-- ======================= -->
            <!-- DIAGRAMA ALMACÉN        -->
            <!-- ======================= -->
            <div class="fc-wrapper">
                <div class="fc-node">
                    <div class="fc-icon-container" style="background: #eff6ff; border: 2px solid #3b82f6;">
                        <img src="{{ asset('images/Recibido.png') }}" class="fc-icon" />
                    </div>
                    <div class="fc-text" style="color: #2563eb;">INICIO</div>
                </div>
                
                <div class="fc-line-vertical"></div>
                
                <div class="fc-decision"><span>¿Tiene<br>modelo?</span></div>
                
                <div class="fc-branches">
                    <!-- NO -->
                    <div class="fc-branch">
                        <div class="fc-branch-label">NO</div>
                        <div class="fc-node" style="border-color: #10b981;">
                            <div class="fc-icon-container" style="background: #ecfdf5;">
                                <img src="{{ asset('images/Escanear-icon.png') }}" class="fc-icon" />
                            </div>
                            <div class="fc-text" style="color: #059669;">Escaneo Modelo</div>
                        </div>
                        <div class="fc-line-vertical" style="flex-grow: 1;"></div>
                    </div>
                    
                    <!-- SÍ -->
                    <div class="fc-branch">
                        <div class="fc-branch-label">SÍ</div>
                        <div class="fc-node" style="border-color: #60a5fa;">
                            <div class="fc-icon-container" style="background: #eff6ff;">
                                <img src="{{ asset('images/PFM-icon.png') }}" class="fc-icon" />
                            </div>
                            <div class="fc-text" style="color: #2563eb;">Genera PFM</div>
                        </div>
                        <div class="fc-line-vertical"></div>
                        <div class="fc-node" style="border-color: #10b981;">
                            <div class="fc-icon-container" style="background: #ecfdf5;">
                                <img src="{{ asset('images/Escanear-icon.png') }}" class="fc-icon" />
                            </div>
                            <div class="fc-text" style="color: #059669;">Escanea PFM</div>
                        </div>
                        <div class="fc-line-vertical" style="flex-grow: 1;"></div>
                    </div>
                </div>
                
                <div class="fc-merge"></div>
                <div class="fc-line-vertical" style="position: relative;">
                    <!-- Return arrow target -->
                    <div style="position: absolute; left: -10px; top: -5px; color: #ec4899; font-weight: bold; font-size: 16px;">⤶</div>
                </div>
                
                <div class="fc-node" style="border-color: #818cf8;">
                    <div class="fc-icon-container" style="background: #e0e7ff;">
                        <img src="{{ asset('images/enviando.png') }}" class="fc-icon" />
                    </div>
                    <div class="fc-text" style="color: #4f46e5;">Envía correo<br>a Calidad</div>
                </div>
                
                <div class="fc-line-vertical"></div>
                
                <div class="fc-node dashed" style="border-color: #ef4444;">
                    <div class="fc-icon-container" style="background: #fef2f2;">
                        <img src="{{ asset('images/Espera.png') }}" class="fc-icon" />
                    </div>
                    <div class="fc-text" style="color: #dc2626;">Espera respuesta<br>de Calidad</div>
                </div>
                
                <div class="fc-line-dotted"></div>
                
                <div class="fc-node" style="border-color: #3b82f6;">
                    <div class="fc-icon-container" style="background: #eff6ff;">
                        <img src="{{ asset('images/Recibido.png') }}" class="fc-icon" />
                    </div>
                    <div class="fc-text" style="color: #2563eb;">Recibe LDM, RDM<br>o SCAR</div>
                </div>
                
                <div class="fc-line-vertical"></div>
                
                <div class="fc-decision"><span>¿Aprob. o<br>rechaz.?</span></div>
                
                <div class="fc-branches">
                    <!-- APROBADO -->
                    <div class="fc-branch">
                        <div class="fc-branch-label" style="color: #10b981; border-color: #10b981; background: #ecfdf5;">APROBADO</div>
                        <div class="fc-node" style="border-color: #10b981;">
                            <div class="fc-icon-container" style="background: #ecfdf5;"><img src="{{ asset('images/Escanear-icon.png') }}" class="fc-icon" /></div>
                            <div class="fc-text" style="color: #059669;">Escanea LDM</div>
                        </div>
                        <div class="fc-line-vertical"></div>
                        <div class="fc-node" style="border-color: #059669;">
                            <div class="fc-icon-container" style="background: #f0fdf4;"><img src="{{ asset('images/PFC-icon.png') }}" class="fc-icon" /></div>
                            <div class="fc-text" style="color: #15803d;">Genera PFC</div>
                        </div>
                        <div class="fc-line-vertical"></div>
                        <div class="fc-node" style="border-color: #10b981;">
                            <div class="fc-icon-container" style="background: #ecfdf5;"><img src="{{ asset('images/Escanear-icon.png') }}" class="fc-icon" /></div>
                            <div class="fc-text" style="color: #059669;">Escanea PFC</div>
                        </div>
                        <div class="fc-line-vertical"></div>
                        <div class="fc-node" style="border-color: #9333ea;">
                            <div class="fc-icon-container" style="background: #f3e8ff;"><img src="{{ asset('images/Proveedor.png') }}" class="fc-icon" /></div>
                            <div class="fc-text" style="color: #9333ea;">Notifica a<br>proveedor</div>
                        </div>
                        <div class="fc-line-vertical"></div>
                        <div class="fc-node" style="border-color: #3b82f6;">
                            <div class="fc-icon-container" style="background: #eff6ff;"><img src="{{ asset('images/Recibido.png') }}" class="fc-icon" /></div>
                            <div class="fc-text" style="color: #2563eb;">FIN</div>
                        </div>
                    </div>
                    
                    <!-- RECHAZADO -->
                    <div class="fc-branch">
                        <div class="fc-branch-label" style="color: #ef4444; border-color: #ef4444; background: #fef2f2;">RECHAZADO</div>
                        <div class="fc-node" style="border-color: #ef4444;">
                            <div class="fc-icon-container" style="background: #fef2f2;"><img src="{{ asset('images/Escanear-icon.png') }}" class="fc-icon" /></div>
                            <div class="fc-text" style="color: #b91c1c;">Escanea RDM<br>y SCAR</div>
                        </div>
                        <div class="fc-line-vertical"></div>
                        <div class="fc-node" style="border-color: #ec4899;">
                            <div class="fc-icon-container" style="background: #fdf2f8;"><img src="{{ asset('images/Reproceso.png') }}" class="fc-icon" /></div>
                            <div class="fc-text" style="color: #be185d;">Genera OT<br>Reproceso</div>
                        </div>
                        <div class="fc-line-vertical"></div>
                        <div class="fc-text" style="font-size:0.6rem; color:#ec4899; margin-top: 5px; font-weight:800;">⟲ Vuelve a PFM</div>
                    </div>
                </div>
            </div>

        @else
            <!-- ======================= -->
            <!-- DIAGRAMA CALIDAD        -->
            <!-- ======================= -->
            <div class="fc-wrapper">
                <div class="fc-node dashed" style="border-color: #ef4444;">
                    <div class="fc-icon-container" style="background: #fef2f2;">
                        <img src="{{ asset('images/Espera.png') }}" class="fc-icon" />
                    </div>
                    <div class="fc-text" style="color: #dc2626;">Espera correo<br>de Almacén</div>
                </div>
                
                <div class="fc-line-vertical"></div>
                
                <div class="fc-node" style="border-color: #3b82f6;">
                    <div class="fc-icon-container" style="background: #eff6ff;"><img src="{{ asset('images/Recibido.png') }}" class="fc-icon" /></div>
                    <div class="fc-text" style="color: #2563eb;">Recibe correo</div>
                </div>
                
                <div class="fc-line-vertical"></div>
                
                <div class="fc-node" style="border-color: #0ea5e9;">
                    <div class="fc-icon-container" style="background: #f0f9ff;"><img src="{{ asset('images/perspectiva-icon.png') }}" class="fc-icon" /></div>
                    <div class="fc-text" style="color: #0369a1;">Visualiza dibujos</div>
                </div>
                
                <div class="fc-line-vertical"></div>
                
                <div class="fc-node" style="border-color: #f59e0b;">
                    <div class="fc-icon-container" style="background: #fffbeb;"><img src="{{ asset('images/Revisando.png') }}" class="fc-icon" /></div>
                    <div class="fc-text" style="color: #b45309;">Registra medidas</div>
                </div>
                
                <div class="fc-line-vertical"></div>
                
                <div class="fc-decision"><span>¿Medidas<br>correctas?</span></div>
                
                <div class="fc-branches">
                    <!-- SÍ -->
                    <div class="fc-branch">
                        <div class="fc-branch-label" style="color: #10b981; border-color: #10b981; background: #ecfdf5;">SÍ</div>
                        <div class="fc-node dashed" style="border-color: #10b981;">
                            <div class="fc-icon-container" style="background: #ecfdf5;"><img src="{{ asset('images/LDM-icon.png') }}" class="fc-icon" /></div><div class="fc-text" style="color: #059669;">Genera Auto<br>LDM</div>
                        </div>
                        <div class="fc-line-vertical" style="flex-grow: 1;"></div>
                    </div>
                    
                    <!-- NO -->
                    <div class="fc-branch">
                        <div class="fc-branch-label" style="color: #ef4444; border-color: #ef4444; background: #fef2f2;">NO</div>
                        <div class="fc-node" style="border-color: #ef4444;">
                            <div class="fc-icon-container" style="background: #fef2f2;"><img src="{{ asset('images/manual.png') }}" class="fc-icon" /></div><div class="fc-text" style="color: #b91c1c;">Pregunta razón<br>rechazo</div>
                        </div>
                        <div class="fc-line-vertical"></div>
                        <div class="fc-node dashed" style="border-color: #ef4444;">
                            <div class="fc-icon-container" style="background: #fef2f2;"><img src="{{ asset('images/RDM-SCAR-icon.png') }}" class="fc-icon" /></div><div class="fc-text" style="color: #b91c1c;">Genera Auto<br>RDM</div>
                        </div>
                        <div class="fc-line-vertical"></div>
                        <div class="fc-node" style="border-color: #eab308;">
                            <div class="fc-icon-container" style="background: #fef9c3;"><img src="{{ asset('images/RDM-SCAR-icon.png') }}" class="fc-icon" /></div><div class="fc-text" style="color: #854d0e;">Abre formato SCAR</div>
                        </div>
                        <div class="fc-line-vertical"></div>
                        <div class="fc-node" style="border-color: #eab308;">
                            <div class="fc-icon-container" style="background: #fef9c3;"><img src="{{ asset('images/manual.png') }}" class="fc-icon" /></div><div class="fc-text" style="color: #854d0e;">Llena formato SCAR</div>
                        </div>
                        <div class="fc-line-vertical"></div>
                        <div class="fc-node dashed" style="border-color: #eab308;">
                            <div class="fc-icon-container" style="background: #fef9c3;"><img src="{{ asset('images/RDM-SCAR-icon.png') }}" class="fc-icon" /></div><div class="fc-text" style="color: #854d0e;">Genera Auto<br>SCAR</div>
                        </div>
                        <div class="fc-line-vertical" style="flex-grow: 1;"></div>
                    </div>
                </div>
                
                <div class="fc-merge"></div>
                <div class="fc-line-vertical"></div>
                
                <div class="fc-node" style="border-color: #818cf8;">
                    <div class="fc-icon-container" style="background: #e0e7ff;">
                        <img src="{{ asset('images/enviando.png') }}" class="fc-icon" />
                    </div>
                    <div class="fc-text" style="color: #4f46e5;">Envía formatos<br>a Almacén</div>
                </div>
            </div>
        @endif
    </div>
</div>

