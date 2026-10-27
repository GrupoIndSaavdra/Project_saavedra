        {{-- Barra de Contexto HUD (Chips, Métricas de Piezas, Buscador en vivo y Leyenda) --}}
        <div class="cp-hud-strip">
            <div class="cp-hud-chips">
                <div class="hud-chip">
                    <img src="{{ asset('images/Color_Icons/Generales/por_liberar_icon.png') }}" class="hud-chip-icon" alt="OT" />
                    <span class="hud-chip-label">OT:</span>
                    <span class="hud-chip-val">#{{ $ot }}</span>
                </div>
                <div class="hud-chip">
                    <img src="{{ asset('images/Color_Icons/Generales/proceso_parcial-icon.png') }}" class="hud-chip-icon"
                        alt="Proceso" />
                    <span class="hud-chip-label">PROCESO:</span>
                    <span class="hud-chip-val">{{ $process }}</span>
                </div>
                <div class="hud-chip">
                    <img src="{{ asset('images/Color_Icons/Generales/DrawsProduction.png') }}" class="hud-chip-icon" alt="Clase" />
                    <span class="hud-chip-label">CLASE:</span>
                    <span class="hud-chip-val">{{ $clase }}</span>
                </div>
                <div class="hud-chip">
                    <img src="{{ asset('images/Color_Icons/Vistas/Reporte_Pzas.png') }}" class="hud-chip-icon" alt="Total Piezas" />
                    <span class="hud-chip-label">TOTAL PIEZAS:</span>
                    <span class="hud-chip-val"
                        id="hudTotalPiezas">{{ is_array($piecesInfo) ? count($piecesInfo) : (is_countable($piecesInfo) ? count($piecesInfo) : 1) }}</span>
                </div>
                <div class="hud-chip hud-chip-error" id="hudErrorChip">
                    <img src="{{ asset('images/Color_Icons/Generales/Sospechosa.png') }}" class="hud-chip-icon" alt="Alerta" />
                    <span class="hud-chip-label">FUERA DE TOLERANCIA:</span>
                    <span class="hud-chip-val" id="hudErrorCount">0</span>
                </div>
            </div>

            <div class="cp-hud-tools">
                <div class="cp-legend">
                    @if ($hasCNominal ?? false)
                        <span class="legend-pill nominal"><span class="dot"></span> C.Nominal / Tol</span>
                    @endif
                    <span class="legend-pill alert"><span class="dot"></span> Fuera de Tolerancia</span>
                </div>
            </div>
        </div>
