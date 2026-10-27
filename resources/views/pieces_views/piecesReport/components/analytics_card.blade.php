@if ($hasCNominal ?? true)
    {{-- Panel de Análisis y Diagnóstico de Desviaciones para Calidad --}}
    <div class="cp-analytics-card" id="analyticsCard">
        <div class="analytics-header">
            <div class="analytics-title-group">
                <img src="{{ asset('images/Color_Icons/Generales/grafico-icon.png') }}" class="analytics-icon-img"
                    alt="Gráfica Calidad" />
                <span class="analytics-title">Diagnóstico de Desviaciones por Cota (Control de Calidad)</span>
            </div>
            <div class="analytics-header-actions">
                <span class="analytics-badge-status" id="generalStatusBadge">Auditando cotas...</span>
            </div>
        </div>

        <div class="analytics-body">
            {{-- Columna 1: KPIs y Nivel de Conformidad con Speedometer Badge --}}
            <div class="analytics-kpis-col">
                <div class="kpi-score-box">
                    <div class="kpi-circle-container" id="kpiGaugeContainer"
                        title="Porcentaje de conformidad dimensional">
                        <span class="kpi-percentage-number" id="kpiPercentVal">--%</span>
                        <span class="kpi-percentage-label">Conformidad</span>
                    </div>
                    <div class="kpi-details-grid">
                        {{-- Tarjeta Aprobado: Ícono centrado y arriba del texto, tamaño ampliado --}}
                        <div class="kpi-mini-card kpi-ok" title="Cotas dentro de tolerancia de maquinado">
                            <div class="kpi-card-vertical">
                                <img src="{{ asset('images/Color_Icons/Generales/Aprobado.png') }}" class="kpi-mini-img kpi-ok-img"
                                    alt="Aprobado" />
                                <span class="kpi-mini-lbl">Cotas OK:</span>
                                <span class="kpi-mini-num" id="kpiOkCount">0</span>
                            </div>
                        </div>
                        <div class="kpi-mini-card kpi-alert" title="Cotas que exceden la tolerancia aprobada">
                            <div class="kpi-card-left">
                                <img src="{{ asset('images/Color_Icons/Generales/Sospechosa.png') }}" class="kpi-mini-img"
                                    alt="Alerta" />
                                <span class="kpi-mini-lbl">Desviadas:</span>
                            </div>
                            <span class="kpi-mini-num" id="kpiFailCount">0</span>
                        </div>
                        <div class="kpi-mini-card kpi-total" title="Total de dimensiones medidas">
                            <div class="kpi-card-left">
                                <img src="{{ asset('images/Light_Icons/Resultado-Light.png') }}" class="kpi-mini-img"
                                    alt="Evaluadas" />
                                <span class="kpi-mini-lbl">Evaluadas:</span>
                            </div>
                            <span class="kpi-mini-num" id="kpiTotalCount">0</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Columna 2: Frecuencia de Desviaciones por Cota (Dónde fallaron) --}}
            <div class="analytics-chart-col">
                <div class="chart-header">
                    <div class="chart-header-title">
                        <img src="{{ asset('images/Color_Icons/Generales/grafico-icon.png') }}" class="chart-header-img"
                            alt="Gráfica Puntos Críticos" />
                        <span class="chart-subtitle">Puntos Críticos Detectados en Maquinado</span>
                    </div>
                    <span class="chart-hint">Dimensiones con mayor incidencia fuera de tolerancia</span>
                </div>
                <div class="chart-bars-list" id="chartBarsList">
                    <div class="chart-empty-state">
                        <img src="{{ asset('images/Color_Icons/Generales/grafico-icon.png') }}" class="empty-state-img"
                            alt="Analizando" />
                        <span>Analizando medidas registradas...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
