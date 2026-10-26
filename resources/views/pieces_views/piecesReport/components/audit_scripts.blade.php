    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Motor de Auditoría y Diagnóstico de Calidad
            function runQualityAudit() {
                const table = document.querySelector('.scrollabe-table table');
                if (!table) return;

                const card = document.getElementById('analyticsCard');
                if (!card) {
                    // Si el proceso no lleva Cotas Nominales, no se computa analítica dimensional
                    const errorInputs = table.querySelectorAll('.input-error, input.cp-border-3px-solid-red, input[style*="red"]');
                    const hudCount = document.getElementById('hudErrorCount');
                    if (hudCount) {
                        hudCount.textContent = errorInputs.length;
                    }
                    return;
                }

                // 1. Obtener nombres de cotas desde los encabezados (fila 0)
                const headerRow = table.querySelector('tr:first-child');
                if (!headerRow) return;
                const headerCells = Array.from(headerRow.querySelectorAll('th, td'));
                const colNames = headerCells.map(c => c.textContent.trim());

                // 2. Identificar filas de piezas evaluadas (excluyendo cNominal y tolerancias)
                const allRows = Array.from(table.querySelectorAll('tr'));
                const pieceRows = allRows.filter((r, idx) => {
                    if (idx === 0) return false;
                    if (r.classList.contains('table-row-cNominals') || r.classList.contains(
                            'table-row-tolerancias')) return false;
                    const firstTd = r.querySelector('td:first-child');
                    if (!firstTd) return false;
                    const val = firstTd.textContent.trim().toLowerCase();
                    if (val === 'c.nominal' || val === 'tolerancias' || val.includes('c.nominal') || val
                        .includes('tolerancia')) return false;
                    return true;
                });

                let totalEvaluated = 0;
                let totalErrors = 0;
                const failuresByCota = {};

                pieceRows.forEach(row => {
                    const firstTd = row.querySelector('td:first-child');
                    const pieceId = firstTd ? (firstTd.textContent || firstTd.querySelector('input')
                        ?.value || '').trim() : 'Pza';
                    const cells = Array.from(row.querySelectorAll('td'));

                    cells.forEach((td, colIdx) => {
                        if (colIdx === 0) return; // Omitir columna de identificación de pieza
                        const cotaName = colNames[colIdx] || `Cota ${colIdx}`;
                        const cotaLower = cotaName.toLowerCase();
                        if (cotaLower.includes('error') || cotaLower.includes('observacion') ||
                            cotaLower.includes('observaciones')) {
                            return;
                        }

                        const inputs = Array.from(td.querySelectorAll('input'));
                        if (inputs.length === 0) {
                            const val = td.textContent.trim();
                            if (val !== '' && val !== '----') {
                                totalEvaluated++;
                                if (td.classList.contains('input-error') || td.style.borderColor ===
                                    'red') {
                                    totalErrors++;
                                    if (!failuresByCota[cotaName]) failuresByCota[cotaName] = {
                                        count: 0,
                                        pieces: []
                                    };
                                    failuresByCota[cotaName].count++;
                                    failuresByCota[cotaName].pieces.push(pieceId);
                                }
                            }
                        } else {
                            inputs.forEach(inp => {
                                const val = inp.value.trim();
                                if (val !== '' && val !== '----') {
                                    totalEvaluated++;
                                    const isError = inp.classList.contains('input-error') ||
                                        (inp.style && inp.style.cssText && inp.style.cssText
                                            .includes('red')) ||
                                        inp.classList.contains('cp-border-3px-solid-red');
                                    if (isError) {
                                        totalErrors++;
                                        if (!failuresByCota[cotaName]) failuresByCota[
                                            cotaName] = {
                                            count: 0,
                                            pieces: []
                                        };
                                        failuresByCota[cotaName].count++;
                                        failuresByCota[cotaName].pieces.push(pieceId);
                                    }
                                }
                            });
                        }
                    });
                });

                const totalOK = Math.max(0, totalEvaluated - totalErrors);
                const complianceRate = totalEvaluated > 0 ? ((totalOK / totalEvaluated) * 100).toFixed(1) : 100;

                // Actualizar contadores HUD y KPIs
                const kpiPercent = document.getElementById('kpiPercentVal');
                const kpiOk = document.getElementById('kpiOkCount');
                const kpiFail = document.getElementById('kpiFailCount');
                const kpiTotal = document.getElementById('kpiTotalCount');
                const hudCount = document.getElementById('hudErrorCount');
                const statusBadge = document.getElementById('generalStatusBadge');

                if (kpiPercent) kpiPercent.textContent = `${complianceRate}%`;
                if (kpiOk) kpiOk.textContent = totalOK;
                if (kpiFail) kpiFail.textContent = totalErrors;
                if (kpiTotal) kpiTotal.textContent = totalEvaluated;
                if (hudCount) hudCount.textContent = totalErrors;

                // Actualizar medidor visual de conformidad
                const kpiGauge = document.getElementById('kpiGaugeContainer');
                if (kpiGauge) {
                    kpiGauge.classList.remove('gauge-ok', 'gauge-warning', 'gauge-danger');
                    if (complianceRate >= 95) {
                        kpiGauge.classList.add('gauge-ok');
                    } else if (complianceRate >= 70) {
                        kpiGauge.classList.add('gauge-warning');
                    } else {
                        kpiGauge.classList.add('gauge-danger');
                    }
                }

                // Estado general
                if (statusBadge) {
                    if (totalErrors === 0) {
                        statusBadge.textContent = '100% Conforme — Sin Desviaciones';
                        statusBadge.className = 'analytics-badge-status badge-success';
                    } else if (complianceRate >= 80) {
                        statusBadge.textContent = `${totalErrors} Desviaciones — Rango Tolerable`;
                        statusBadge.className = 'analytics-badge-status badge-warning';
                    } else {
                        statusBadge.textContent = `${totalErrors} Cotas Fuera de Rango — Requiere Atención`;
                        statusBadge.className = 'analytics-badge-status badge-danger';
                    }
                }

                // Renderizar gráfico de frecuencia por cota
                const chartContainer = document.getElementById('chartBarsList');
                if (chartContainer) {
                    const sortedFailures = Object.entries(failuresByCota).sort((a, b) => b[1].count - a[1].count);
                    const totalPieces = pieceRows.length || 1;
                    const maxFailures = (sortedFailures.length > 0 && sortedFailures[0][1].count) ? sortedFailures[
                        0][1].count : 1;

                    if (sortedFailures.length === 0) {
                        chartContainer.innerHTML = `
                            <div class="chart-empty-state chart-all-good">
                                <img src="${window.iconosApp.aprobado}" class="empty-state-img" alt="Aprobado" />
                                <span>Todas las cotas registradas cumplen con las tolerancias aprobadas.</span>
                            </div>
                        `;
                    } else {
                        let html = '';
                        sortedFailures.forEach(([name, data], idx) => {
                            // Proporción relativa respecto a la cota con mayor desviación (para que la gráfica escale dinámicamente)
                            const barWidthPercent = Math.max(14, Math.round((data.count / maxFailures) *
                                100));

                            // Proporción del total de desviaciones en la orden
                            const defectShare = totalErrors > 0 ? Math.round((data.count / totalErrors) *
                                100) : 0;

                            // Impacto en lote de piezas
                            const uniquePieces = Array.from(new Set(data.pieces));
                            const pieceImpact = Math.round((uniquePieces.length / totalPieces) * 100);
                            const pieceBadges = uniquePieces.map(p =>
                                `<span class="cota-piece-chip" title="Pieza con desviación: ${p}">${p}</span>`
                            ).join('');

                            let severityClass = 'severity-critical';
                            let severityLabel = 'Crítico';
                            if (defectShare < 20 && data.count <= 1) {
                                severityClass = 'severity-low';
                                severityLabel = 'Leve';
                            } else if (defectShare < 40) {
                                severityClass = 'severity-medium';
                                severityLabel = 'Moderado';
                            }

                            html += `
                                <div class="chart-bar-item ${severityClass}">
                                    <div class="chart-bar-meta">
                                        <div class="chart-cota-left">
                                            <span class="chart-rank-badge">#${idx + 1}</span>
                                            <span class="chart-cota-name" title="${name}">${name}</span>
                                        </div>
                                        <div class="chart-cota-stats">
                                            <span class="stat-pill-incidencias">
                                                <strong>${data.count} falla(s)</strong> (${defectShare}% del total)
                                            </span>
                                            <span class="stat-pill-severity">${severityLabel}</span>
                                            <div class="stat-pieces-list">
                                                <span class="stat-pieces-label">Pzas:</span>
                                                ${pieceBadges}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="chart-bar-track">
                                        <div class="chart-bar-fill" style="width: ${barWidthPercent}%;">
                                            <span class="bar-shine"></span>
                                            <span class="chart-bar-count-badge">${data.count}</span>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                        chartContainer.innerHTML = html;
                    }
                }
            }

            // Gestión inteligente de retorno según el origen de apertura
            const btnRegresar = document.getElementById('btnRegresar');
            if (btnRegresar) {
                btnRegresar.addEventListener('click', function(e) {
                    const urlParams = new URLSearchParams(window.location.search);
                    const fromParam = urlParams.get('from') || urlParams.get('origin');

                    if (fromParam === 'home' || fromParam === 'inicio') {
                        e.preventDefault();
                        window.location.href = "{{ route('home') }}";
                        return;
                    }

                    if (document.referrer) {
                        try {
                            const ref = new URL(document.referrer);
                            if (ref.pathname.includes('releasePieces')) {
                                e.preventDefault();
                                window.location.href = "{{ route('showReleasePieces_view') }}";
                                return;
                            }
                            if (ref.pathname.endsWith('/home') || ref.pathname === '/' || ref.pathname ===
                                '') {
                                e.preventDefault();
                                window.location.href = "{{ route('home') }}";
                                return;
                            }
                        } catch (err) {}
                    }
                });
            }

            // Ejecutar la auditoría de calidad tras renderizar la tabla
            setTimeout(runQualityAudit, 200);
        });
    </script>
