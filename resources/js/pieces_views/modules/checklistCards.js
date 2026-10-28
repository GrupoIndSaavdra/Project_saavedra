// ══════════════════════════════════════════════════════════
// MÓDULO DE TARJETAS REACTIVAS Y CHECKLISTS DE PROCESO
// (PTA, Fundición, Planeación, Térmico)
// ══════════════════════════════════════════════════════════

class PTACardComponent {
    /**
     * @param {string}  otId       - Identificador de la OT (ej. "OT-001")
     * @param {object}  initialData - { totalPTA, terminadas, liberadas } del servidor
     * @param {Element} container  - Elemento contenedor donde se monta la card
     * @param {object}  classArray - classArray de la OT (para calcular piezas malas)
     */
    constructor(otId, classId, initialData, container, classArray) {
        // — Raw data del montaje inicial
        const rawTotal = initialData.totalPTA || 0;
        const totalJuegos = rawTotal > 0 ? Math.round(rawTotal / 2) : 0;

        this.snapshot = totalJuegos > 0 ? Object.freeze({ total: totalJuegos }) : null;
        this.otId = otId;
        this.classId = classId;
        this.uniqueId = `${otId}-${classId}`;
        this.container = container;
        this.classArray = classArray;
        this._pollTimer = null;
        this._busy = false;
        this._mounted = false;   // true cuando el DOM ya está creado
        this.root = null;

        // Estado mutable
        this.current = initialData.terminadas || 0;
        this.liberadas = initialData.liberadas || 0;
        this.rechazadas = initialData.rechazadas || 0;
        this.sinLiberar = initialData.sinLiberar || 0;

        if (totalJuegos > 0) {
            // Regla 1: hay piezas → montar inmediatamente y arrancar polling
            this._mount();
        }
        // Siempre arrancar el polling (modo dormido si no hay piezas todavía)
        this._startPolling();
    }


    // ── Montaje del DOM (puede llamarse en diferido) ──────────────
    _mount() {
        if (this._mounted) return;
        this.root = this._render();
        this.container.appendChild(this.root);
        this._mounted = true;
    }

    // ── Construye el DOM inicial ──────────────────────────────
    _render() {
        const section = document.createElement("div");
        section.className = "process-section pta-process-card";
        section.style.cursor = "pointer";
        section.id = `pta-card-${this.uniqueId}`;
        section.title = "Ver resultados de Soldadura PTA";

        // Badge en la esquina superior izquierda (simétrico con 01, 02... de otros procesos)
        const numberBadge = document.createElement("span");
        numberBadge.className = "process-number-badge pta-number-badge";
        numberBadge.textContent = "PTA";
        section.appendChild(numberBadge);

        // Icono en la esquina superior derecha (posición absoluta para no estirar el flujo de la tarjeta)
        const ptaIcon = document.createElement("img");
        ptaIcon.className = "cotas-badge pta-badge-icon";
        const bUrl = window.baseUrl || (window.location.origin + '/');
        const bSlash = bUrl.endsWith('/') ? '' : '/';
        ptaIcon.src = `${bUrl}${bSlash}images/Color_Icons/Generales/Aprobado.png`;
        ptaIcon.title = "Resultados de Soldadura PTA (clic para ver detalle)";
        ptaIcon.style.position = "absolute";
        ptaIcon.style.top = "10px";
        ptaIcon.style.right = "10px";
        ptaIcon.style.width = "32px";
        ptaIcon.style.height = "32px";
        ptaIcon.style.zIndex = "5";
        section.appendChild(ptaIcon);

        const title = document.createElement("h3");
        title.className = "process-title";
        title.innerHTML = "Resultados Soldadura PTA";
        section.appendChild(title);

        // Subtítulo con total de piezas terminadas
        const labelTerm = document.createElement("label");
        labelTerm.className = "limit-label";
        labelTerm.id = `label-term-${this.uniqueId}`;
        labelTerm.style.fontSize = "12px";
        labelTerm.style.color = "#ffffff";
        labelTerm.textContent = "Terminadas: 0 juegos correctos";
        section.appendChild(labelTerm);

        // Barra 1: Juegos Liberados (Verde esmeralda de alto contraste)
        const barLib = this._buildBar(
            "bar-lib",
            "bar-lib-track",
            "fill-lib",
            "linear-gradient(90deg, #10b981 0%, #059669 100%)"
        );
        section.appendChild(barLib);

        // Barra 2: Juegos Sin Liberar / Pendientes (Azul GIS de alto contraste)
        const barSin = this._buildBar(
            "bar-sin",
            "bar-sin-track",
            "fill-sin",
            "linear-gradient(90deg, #38bdf8 0%, #0284c7 100%)"
        );
        section.appendChild(barSin);

        // Click: navegar a la vista de resultados
        section.addEventListener("click", () => {
            const base = window.ptaResultsBaseUrl || (window.baseUrl + "/admin/pta/results");
            window.location.href = `${base}/${this.otId}?clase_id=${this.classId}`;
        });

        // Actualizar valores iniciales (skip connection check, aún no está en DOM)
        this._updateBars(section, true);

        return section;
    }

    // ── Construye una barra de progreso de alto contraste ─────
    _buildBar(barId, trackClass, fillClass, fillGradient) {
        const wrap = document.createElement("div");
        wrap.className = `progress-bar pta-progress-bar ${trackClass}`;
        wrap.id = `${barId}-${this.uniqueId}`;

        const fill = document.createElement("div");
        fill.className = `pta-bar-fill ${fillClass} progress`;
        fill.style.background = fillGradient;
        fill.style.width = "0%";
        wrap.appendChild(fill);

        const info = document.createElement("div");
        info.className = "progress-percentage pta-progress-percentage";
        wrap.appendChild(info);

        return wrap;
    }

    // ── Actualiza solo los elementos internos (sin re-render) ─
    _updateBars(root, skipConnectionCheck) {
        root = root || this.root;
        if (!root) return;
        if (!skipConnectionCheck && !root.isConnected) return;

        const terminadas = this.current;
        const sinLiberar = Math.max(0, terminadas - this.liberadas - this.rechazadas);

        if (terminadas === 0 && this.rechazadas === 0) {
            root.classList.add("inactive-process");
        } else {
            root.classList.remove("inactive-process");
        }

        // Borde verde si el proceso está concluido (100% liberado de lo terminado)
        if (this.snapshot && this.snapshot.total > 0) {
            let ptaPercentage = (this.liberadas * 100) / this.snapshot.total;
            let hue = 30 + (Math.min(100, ptaPercentage) * 0.9);
            root.style.setProperty('--glow-hue', hue);

            if (ptaPercentage === 0) {
                root.title = "Proceso PTA aún no iniciado o sin piezas liberadas.";
            } else if (ptaPercentage < 50) {
                root.title = `Progreso bajo de liberación (${ptaPercentage.toFixed(1)}%).`;
            } else if (ptaPercentage < 100) {
                root.title = `Progreso estable de liberación (${ptaPercentage.toFixed(1)}%).`;
            } else {
                root.title = "¡Soldadura PTA completada y 100% liberada!";
            }

            if (ptaPercentage >= 100) {
                root.style.borderColor = '#4ade80';
                root.style.boxShadow = 'none';
            } else if (ptaPercentage > 0) {
                root.style.borderColor = `hsl(${hue}, 100%, 50%)`;
                root.style.boxShadow = 'none';
            } else {
                root.style.borderColor = '';
                root.style.boxShadow = '';
            }
        }

        // Subtítulo con desglose de terminadas
        const labelT = root.querySelector(`#label-term-${this.uniqueId}`);
        if (labelT) {
            if (this.rechazadas > 0) {
                labelT.textContent = `Terminadas: ${terminadas} juegos • ${this.rechazadas} rechazado${this.rechazadas !== 1 ? 's' : ''}`;
            } else {
                labelT.textContent = `Terminadas: ${terminadas} juego${terminadas !== 1 ? 's' : ''} correctos`;
            }
        }

        // Helper para actualizar barras
        const setBar = (barId, count, countText) => {
            const pct = terminadas > 0 ? Math.min(100, Math.round((count / terminadas) * 100)) : 0;
            const wrap = root.querySelector(`#${barId}-${this.uniqueId}`);
            if (wrap) {
                const fill = wrap.querySelector(".pta-bar-fill");
                if (fill) fill.style.width = `${pct}%`;
                const info = wrap.querySelector(".progress-percentage");
                if (info) info.textContent = `${pct}% • ${count}/${terminadas} ${countText}`;
            }
        };

        // Barra 1: Juegos Liberados (VERDE)
        setBar("bar-lib", this.liberadas, "liberados");

        // Barra 2: Juegos Sin Liberar (AZUL)
        setBar("bar-sin", sinLiberar, "sin liberar");
    }

    // ── Polling AJAX cada 10 s ───────────────────────────────
    _startPolling() {
        this._pollTimer = setInterval(() => this._poll(), 10_000);
    }

    async _poll() {
        // Guard: petición en vuelo
        if (this._busy) return;

        // Guard extra: si el contenedor original ya no está en el DOM, destruir el timer.
        if (this.container && !this.container.isConnected) {
            this._destroy();
            return;
        }

        // Guard extra: si el nodo ya fue montado, verificar que sigue conectado
        if (this._mounted && this.root && !this.root.isConnected) {
            this._destroy();
            return;
        }

        this._busy = true;

        try {
            const res = await fetch(`${window.baseUrl}/piecesInProgress/ptaCard/${this.otId}/${this.classId}`);
            if (!res.ok) return;

            const data = await res.json();
            if (data.error) return;
            const rawTotal = data.totalPTA || 0;
            const totalJuegos = rawTotal; // Trust the backend game count

            // ── Primera activación (modo dormido → con piezas) ────────────
            if (!this._mounted && totalJuegos > 0) {
                // Congelar el total en el primer tick con datos reales
                this.snapshot = Object.freeze({ total: totalJuegos });
                this.current = data.terminadas || 0;
                this.liberadas = data.liberadas || 0;
                this.rechazadas = data.rechazadas || 0;
                this.sinLiberar = data.sinLiberar || 0;
                this._mount();
                return;
            }


            if (!this._mounted) return; // aún sin piezas, seguir esperando

            // Regla 2: Actualizar el total si ha cambiado (ej. rechazos o nuevas piezas)
            const newTotalJuegos = data.totalPTA || 0;
            if (newTotalJuegos !== this.snapshot.total) {
                this.snapshot = Object.freeze({ total: newTotalJuegos });
            }

            const newTerminadas = data.terminadas || 0;
            const newLiberadas = data.liberadas || 0;
            const newRechazadas = data.rechazadas || 0;
            const newSinLiberar = data.sinLiberar || 0;

            // Detectar cambio real antes de tocar el DOM (evita re-renders innecesarios)
            if (newTerminadas === this.current && newLiberadas === this.liberadas && newRechazadas === this.rechazadas && newSinLiberar === this.sinLiberar) return;

            this.current = newTerminadas;
            this.liberadas = newLiberadas;
            this.rechazadas = newRechazadas;
            this.sinLiberar = newSinLiberar;

            // Regla 4: auto-desmontaje cuando el contador llega a 0
            if (this.current === 0) {
                this._destroy();
                return;
            }

            // Regla 3: actualizar barras de forma quirúrgica
            this._updateBars();
        } catch (_) {
            // Error de red — silencioso, el próximo tick reintenta
        } finally {
            this._busy = false;
        }
    }


    // ── Destrucción limpia ───────────────────────────────────
    _destroy() {
        clearInterval(this._pollTimer);
        this._pollTimer = null;
        if (this.root && this.root.isConnected) {
            this.root.remove();
        }
    }
}

// ══════════════════════════════════════════════════════════
// FundicionChecklistCard — Checklist reactivo del flujo de
// fundición (Modelo → Liberación → SCAR → Casting/Reproceso)
//
// Patrón idéntico a PTACardComponent:
//   - Carga inicial desde window.fundicionChecklist
//   - Polling AJAX cada 30s para refrescar sin reload
//   - Solo se instancia para perfiles 1 y 2 (guard en Dashboard)
//   - Auto-actualiza borde y colores según estado global
// ══════════════════════════════════════════════════════════
class FundicionChecklistCard {
    /**
     * @param {string}  otId        - Clave de la OT (ej. "6748" o "6748_R1")
     * @param {object}  initialData - { esReproceso: bool, pasos: { key: { label, estado } } }
     * @param {Element} container   - Div wrapper donde se monta la card
     * @param {string}  [instanceId] - Sufijo único para IDs del DOM (por defecto = otId).
     *                                  Usar el nombre de la clase cuando la OT tiene varias secciones.
     */
    constructor(otId, initialData, container, instanceId) {
        this.otId = otId;
        this.className = instanceId || '';
        this._instanceId = instanceId ? `${otId}-${instanceId}`.replace(/[^a-zA-Z0-9_-]/g, '_') : otId;
        this._data = initialData;
        this.container = container;
        this._pollTimer = null;
        this._mounted = false;
        this.root = null;

        this._mount();
        this._startPolling();
    }

    // ── Montar el DOM ─────────────────────────────────────
    _mount() {
        if (this._mounted) return;
        this.root = this._render();
        this.container.appendChild(this.root);
        this._mounted = true;
    }

    // ── Icono según estado ────────────────────────────────
    _getIconFor(estado) {
        const baseUrl = window.baseUrl || (window.location.origin + '/');
        const slash = baseUrl.endsWith('/') ? '' : '/';
        let imgName = '';
        switch (estado) {
            case 'Completado':
                imgName = 'Color_Icons/Generales/Aprobado.png';
                break;
            case 'En Espera':
                imgName = 'Color_Icons/Generales/Espera.png';
                break;
            case 'Revisando':
                imgName = 'Color_Icons/Generales/Revisando.png';
                break;
            case 'Incompleto':
            default:
                imgName = 'Color_Icons/Generales/Recibido.png';
                break;
        }
        return `${baseUrl}${slash}images/${imgName}`;
    }

    // ── Color del borde izquierdo según estado global ─────
    // Verde si todos completados, rojo si alguno rechazado, naranja si en progreso
    _getBorderColor(data) {
        const pasos = Object.values(data.pasos || {});
        if (pasos.some(p => (p.estado || '').toLowerCase() === 'rechazado')) return '#9D0402'; // corporate red
        if (pasos.length > 0 && pasos.every(p => {
            const st = (p.estado || '').toLowerCase();
            return st === 'completado' || st === 'proveedor' || st === 'aprobado' || st === 'aprobado_final' || st === 'no_aplica';
        })) return '#0C8201'; // corporate green
        return '#424141'; // pending - corporate gray
    }

    // ── Construir el DOM inicial ──────────────────────────
    _render() {
        const card = document.createElement('div');
        card.className = 'fundicion-checklist-card';
        card.id = `fundicion-checklist-${this._instanceId}`;

        // — Header —
        const header = document.createElement('div');
        header.className = 'checklist-header';

        const title = document.createElement('span');
        title.className = 'checklist-title';
        title.textContent = 'Levantamiento de OT';
        header.appendChild(title);

        const badge = document.createElement('span');
        badge.className = 'checklist-reproceso-badge';
        badge.id = `checklist-badge-${this._instanceId}`;
        badge.textContent = this._data.badgeText || 'Reproceso';
        badge.classList.toggle("hidden", !(this._data.isBadgeVisible));
        header.appendChild(badge);

        card.appendChild(header);

        // — Contenedor de pasos —
        const itemsContainer = document.createElement('div');
        itemsContainer.className = 'checklist-items';
        itemsContainer.id = `checklist-items-${this._instanceId}`;
        itemsContainer.hidden = true;
        card.appendChild(itemsContainer);

        card.classList.add('is-closed');

        // Toggle logic: make whole card clickable
        card.style.cursor = 'pointer';
        card.addEventListener('click', () => {
            if (itemsContainer.hidden) {
                itemsContainer.hidden = false;
                card.classList.remove('is-closed');
            } else {
                itemsContainer.hidden = true;
                card.classList.add('is-closed');
            }
        });

        // Render inicial de estados
        this._updateCard(card, this._data);

        return card;
    }

    // ── Actualiza borde + badge + pasos (sin re-render completo) ─
    _updateCard(card, data) {
        if (!card) return;

        // Contorno verde si completado, rojo si rechazado, sin glow
        let colorHex = this._getBorderColor(data);
        card.classList.remove('card-state-completado', 'card-state-incompleto', 'card-state-rechazado');
        if (colorHex === '#9D0402') { // Red
            card.classList.add('card-state-rechazado');
        } else if (colorHex === '#0C8201') { // Green
            card.classList.add('card-state-completado');
        } else {
            card.classList.add('card-state-incompleto');
        }

        const titleSpan = card.querySelector('.checklist-title');
        if (titleSpan) {
            if (colorHex === '#0C8201') {
                titleSpan.style.color = '#4ade80';
                titleSpan.style.textShadow = '0 0 10px rgba(74, 222, 128, 0.5)';
            } else if (colorHex === '#9D0402') {
                titleSpan.style.color = '#f87171';
                titleSpan.style.textShadow = '0 0 10px rgba(248, 113, 113, 0.5)';
            } else {
                titleSpan.style.color = '';
                titleSpan.style.textShadow = '';
            }
        }

        // Badge de reproceso
        const safeId = (typeof CSS !== 'undefined' && CSS.escape) ? CSS.escape(this._instanceId) : this._instanceId;
        const badge = card.querySelector(`#checklist-badge-${safeId}`);
        if (badge) {
            badge.classList.toggle("hidden", !(data.isBadgeVisible));
            if (data.badgeText) {
                badge.textContent = data.badgeText;
            }
        }

        // Reconstruir lista de pasos
        const container = card.querySelector(`#checklist-items-${safeId}`);
        if (!container) return;
        container.innerHTML = '';

        // Solo mostrar los pasos que no estén inactivos (para que vayan apareciendo uno a uno)
        const pasosEntries = Object.entries(data.pasos || {}).filter(([key, paso]) => paso.estado !== 'Incompleto');

        pasosEntries.forEach(([key, paso], idx) => {
            if (!paso) return;

            const safeEstado = paso.estado.toLowerCase().replace(/\s+/g, '_');
            const item = document.createElement('div');
            item.className = `checklist-item checklist-item--${safeEstado}`;
            if (paso.tooltip) {
                item.title = paso.tooltip;
                item.style.cursor = 'help';
            }

            // 1. Área del Ícono (Izquierda)
            const iconCol = document.createElement('div');
            iconCol.className = 'checklist-icon-col';

            const iconSpan = document.createElement('span');
            iconSpan.className = 'checklist-icon';

            const img = document.createElement('img');
            img.src = this._getIconFor(paso.estado);
            img.alt = safeEstado;
            img.className = 'checklist-state-icon';
            iconSpan.appendChild(img);

            iconCol.appendChild(iconSpan);

            // Conector vertical
            if (idx < pasosEntries.length - 1) {
                const line = document.createElement('div');
                line.className = 'checklist-connector-line';
                iconCol.appendChild(line);
            }

            // 2. Área de Descripción (Centro)
            const contentCol = document.createElement('div');
            contentCol.className = 'checklist-content-col';

            const labelParts = paso.label.split(': ');

            const labelDesc = document.createElement('span');
            labelDesc.className = 'checklist-label-desc';
            labelDesc.textContent = labelParts.length > 1 ? labelParts[1] : paso.label;

            contentCol.appendChild(labelDesc);

            if (labelParts.length > 1) {
                const labelDept = document.createElement('span');
                labelDept.className = 'checklist-label-dept';
                labelDept.textContent = labelParts[0];
                contentCol.appendChild(labelDept);
            }

            // 3. Área de Acción / Status (Derecha)
            const actionCol = document.createElement('div');
            actionCol.className = 'checklist-action-col';

            const statusBadge = document.createElement('span');
            statusBadge.className = `checklist-status-badge badge--${safeEstado.replace(/_/g, '-')}`;
            statusBadge.textContent = paso.estado.toUpperCase();

            actionCol.appendChild(statusBadge);

            item.appendChild(iconCol);
            item.appendChild(contentCol);
            item.appendChild(actionCol);

            // Add sub-pasos rendering if they exist
            const activeSubPasos = (paso.subPasos || []).filter(sp => sp.estado !== 'Incompleto');
            if (activeSubPasos.length > 0) {
                const subPasosContainer = document.createElement('div');
                subPasosContainer.className = 'checklist-subpasos-container';
                subPasosContainer.hidden = true;

                activeSubPasos.forEach((subPaso, spIdx) => {
                    const spEstado = subPaso.estado.toLowerCase().replace(/\s+/g, '_');
                    const spItem = document.createElement('div');
                    spItem.className = `checklist-subpaso-item checklist-subpaso-item--${spEstado}`;

                    const spIconCol = document.createElement('div');
                    spIconCol.className = 'checklist-subpaso-icon-col';

                    const spIcon = document.createElement('img');
                    spIcon.src = this._getIconFor(subPaso.estado);
                    spIcon.className = 'checklist-subpaso-icon';
                    spIconCol.appendChild(spIcon);

                    if (spIdx < activeSubPasos.length - 1) {
                        const spLine = document.createElement('div');
                        spLine.className = 'checklist-subpaso-connector';
                        spIconCol.appendChild(spLine);
                    }

                    const spContentCol = document.createElement('div');
                    spContentCol.className = 'checklist-subpaso-content-col';

                    const spLabel = document.createElement('span');
                    spLabel.className = 'checklist-subpaso-label';
                    spLabel.textContent = subPaso.label;
                    spContentCol.appendChild(spLabel);

                    if (subPaso.detalle) {
                        const spDetalle = document.createElement('span');
                        spDetalle.className = 'checklist-subpaso-detalle';
                        spDetalle.textContent = subPaso.detalle;
                        spContentCol.appendChild(spDetalle);
                    }

                    const spBadgeCol = document.createElement('div');
                    spBadgeCol.className = 'checklist-subpaso-badge-col';

                    const spStatusBadge = document.createElement('span');
                    spStatusBadge.className = `checklist-subpaso-badge badge--${spEstado.replace(/_/g, '-')}`;
                    spStatusBadge.textContent = subPaso.estado.toUpperCase();
                    spBadgeCol.appendChild(spStatusBadge);

                    spItem.appendChild(spIconCol);
                    spItem.appendChild(spContentCol);
                    spItem.appendChild(spBadgeCol);

                    subPasosContainer.appendChild(spItem);
                });

                // Add expand indicator icon to main item
                const expandIcon = document.createElement('span');
                expandIcon.className = 'checklist-expand-icon';
                expandIcon.innerHTML = '▼';
                actionCol.appendChild(expandIcon);

                item.style.cursor = 'pointer';
                item.addEventListener('click', (e) => {
                    e.stopPropagation(); // Avoid triggering the main card toggle

                    // Close other expanded items
                    const allExpandedItems = container.querySelectorAll('.checklist-item.is-expanded');
                    allExpandedItems.forEach(expandedItem => {
                        if (expandedItem !== item) {
                            expandedItem.classList.remove('is-expanded');
                            const relatedContainer = expandedItem.nextElementSibling;
                            if (relatedContainer && relatedContainer.classList.contains('checklist-subpasos-container')) {
                                relatedContainer.hidden = true;
                            }
                            const relatedIcon = expandedItem.querySelector('.checklist-expand-icon');
                            if (relatedIcon) {
                                relatedIcon.innerHTML = '▼';
                            }
                        }
                    });

                    if (subPasosContainer.classList.contains("hidden")) {
                        subPasosContainer.hidden = false;
                        item.classList.add('is-expanded');
                        expandIcon.innerHTML = '▲';
                    } else {
                        subPasosContainer.hidden = true;
                        item.classList.remove('is-expanded');
                        expandIcon.innerHTML = '▼';
                    }
                });

                container.appendChild(item);
                container.appendChild(subPasosContainer);
            } else {
                container.appendChild(item);
            }
        });
    }

    // ── Polling AJAX cada 30s ──────────────────────────────
    _startPolling() {
        // 30_000ms = 30 segundos; suficiente para un checklist de bajo cambio
        this._pollTimer = setInterval(() => this._poll(), 30_000);
    }

    async _poll() {
        // Guard: si el nodo fue desmontado, cancelar polling
        if (!this.root || !this.root.isConnected) {
            this._destroy();
            return;
        }
        try {
            const url = this.className ? `${window.fundicionChecklistUrl}/${this.otId}/${this.className}` : `${window.fundicionChecklistUrl}/${this.otId}`;
            const res = await fetch(url);
            if (!res.ok) return; // 404 si la OT dejó de tener flujo — se deja morir en siguiente tick

            const data = await res.json();
            if (data && !data.error) {
                this._data = data;
                this._updateCard(this.root, data);
            }
        } catch (_) {
            // Error de red — silencioso, el próximo tick reintenta
        }
    }

    // ── Destrucción limpia ─────────────────────────────────
    _destroy() {
        clearInterval(this._pollTimer);
        this._pollTimer = null;
        if (this.root && this.root.isConnected) {
            this.root.remove();
        }
    }
}

// ══════════════════════════════════════════════════════════
// PlaneacionChecklistCard — Checklist reactivo de Planeación
// por clase (Dibujos Maquinado, Cotas, Proceso).
// ══════════════════════════════════════════════════════════
class PlaneacionChecklistCard {
    constructor(otId, claseId, container) {
        this.otId = otId;
        this.claseId = claseId;
        this._safeClaseId = String(claseId).replace(/[^a-zA-Z0-9_-]/g, '_');
        this.container = container;
        this._pollTimer = null;
        this._mounted = false;
        this.root = null;

        this._mount();
        this._startPolling();
    }

    _mount() {
        if (this._mounted) return;
        this.root = this._render();
        this.container.appendChild(this.root);
        this._mounted = true;
    }

    _getIconFor(estado, label = '') {
        const baseUrl = window.baseUrl || (window.location.origin + '/');
        const slash = baseUrl.endsWith('/') ? '' : '/';
        let imgName = '';

        if (estado === 'completado') {
            imgName = 'Color_Icons/Generales/Aprobado.png';
        } else if (estado === 'no_aplica') {
            imgName = 'Color_Icons/Generales/icono_sin_cotas.png';
        } else {
            if (label.includes('Cotas de OT/Clase subidas')) {
                imgName = 'Color_Icons/Generales/icono_cotas.png';
            } else if (label.includes('Dibujos de maquinados subidos')) {
                imgName = 'Color_Icons/Generales/DrawsProduction.png';
            } else {
                switch (estado) {
                    case 'pendiente': imgName = 'Color_Icons/Generales/Espera.png'; break;
                    case 'rechazado': imgName = 'Color_Icons/Generales/Rechazado.png'; break;
                    case 'inactivo':
                    default: imgName = 'Color_Icons/Generales/Recibido.png'; break;
                }
            }
        }
        return `${baseUrl}${slash}images/${imgName}`;
    }

    _getBorderColor(pasos) {
        if (!pasos || Object.keys(pasos).length === 0) return '#424141';
        const vals = Object.values(pasos);
        if (vals.some(p => (p.estado || '').toLowerCase() === 'rechazado')) return '#9D0402';
        if (vals.every(p => {
            const st = (p.estado || '').toLowerCase();
            return st === 'completado' || st === 'no_aplica';
        })) return '#0C8201';
        return '#424141';
    }

    _render() {
        const card = document.createElement('div');
        card.className = 'fundicion-checklist-card';
        card.id = `planeacion-checklist-${this.otId}-${this._safeClaseId}`;

        // Contenedores base
        card.innerHTML = `
            <div class="checklist-header">
                <span class="checklist-title">Planeación</span>
            </div>
            <div class="checklist-items" id="planeacion-items-${this.otId}-${this._safeClaseId}" style="padding-top: 5px;">
                <div class="checklist-item checklist-item--pendiente" title="Pendiente">
                    <div class="checklist-icon-col">
                        <span class="checklist-icon"><img src="${this._getIconFor('pendiente')}" alt="pendiente" class="checklist-state-icon"></span>
                        <div class="checklist-connector-line"></div>
                    </div>
                    <div class="checklist-content-col">
                        <span class="checklist-label">Procesos de Producción</span>
                    </div>
                    <div class="checklist-action-col">
                        <span class="checklist-status-badge badge--pendiente">PENDIENTE</span>
                    </div>
                </div>
                <div class="checklist-item checklist-item--inactivo" title="Inactivo">
                    <div class="checklist-icon-col">
                        <span class="checklist-icon"><img src="${this._getIconFor('inactivo', 'Dibujos de maquinados subidos')}" alt="inactivo" class="checklist-state-icon"></span>
                        <div class="checklist-connector-line"></div>
                    </div>
                    <div class="checklist-content-col">
                        <span class="checklist-label">Dibujos de maquinados subidos</span>
                    </div>
                    <div class="checklist-action-col">
                        <span class="checklist-status-badge badge--inactivo">INACTIVO</span>
                    </div>
                </div>
                <div class="checklist-item checklist-item--inactivo" title="Inactivo">
                    <div class="checklist-icon-col">
                        <span class="checklist-icon"><img src="${this._getIconFor('inactivo', 'Cotas de OT/Clase subidas')}" alt="inactivo" class="checklist-state-icon"></span>
                    </div>
                    <div class="checklist-content-col">
                        <span class="checklist-label">Cotas de OT/Clase subidas (Admin)</span>
                    </div>
                    <div class="checklist-action-col">
                        <span class="checklist-status-badge badge--inactivo">INACTIVO</span>
                    </div>
                </div>
            </div>
        `;

        const itemsContainer = card.querySelector('.checklist-items');
        // Ocultar mediante atributo HTML (no clase CSS) para que el toggle sea consistente
        if (itemsContainer) itemsContainer.hidden = true;

        card.classList.add('is-closed');
        card.style.cursor = 'pointer';
        card.addEventListener('click', () => {
            if (itemsContainer.hidden) {
                itemsContainer.hidden = false;
                card.classList.remove('is-closed');
            } else {
                itemsContainer.hidden = true;
                card.classList.add('is-closed');
            }
        });

        return card;
    }

    _updateCard(data) {
        if (!this.root) return;
        const container = this.root.querySelector(`#planeacion-items-${this.otId}-${this._safeClaseId}`);
        if (!container) return;

        const pasosData = data[this.claseId];

        if (!pasosData) {
            container.innerHTML = `<div style="font-size: 13px; color: #94a3b8; text-align: center; padding: 10px;">Sin datos para esta clase</div>`;
            return;
        }

        let colorHex = this._getBorderColor(pasosData);
        this.root.classList.remove('card-state-completado', 'card-state-incompleto', 'card-state-rechazado');
        if (colorHex === '#9D0402') {
            this.root.style.borderColor = '';
            this.root.classList.add('card-state-rechazado');
        } else if (colorHex === '#0C8201') {
            this.root.style.borderColor = '';
            this.root.classList.add('card-state-completado');
        } else {
            this.root.style.borderColor = '';
            this.root.classList.add('card-state-incompleto');
        }

        const titleSpan = this.root.querySelector('.checklist-title');
        if (titleSpan) {
            if (colorHex === '#0C8201') {
                titleSpan.style.color = '#4ade80';
                titleSpan.style.textShadow = '0 0 10px rgba(74, 222, 128, 0.5)';
            } else if (colorHex === '#9D0402') {
                titleSpan.style.color = '#f87171';
                titleSpan.style.textShadow = '0 0 10px rgba(248, 113, 113, 0.5)';
            } else {
                titleSpan.style.color = '';
                titleSpan.style.textShadow = '';
            }
        }

        container.innerHTML = '';
        // Solo mostrar los pasos que no estén inactivos
        const pasosEntries = Object.entries(pasosData).filter(([key, paso]) => paso.estado !== 'Incompleto');

        pasosEntries.forEach(([key, paso], idx) => {
            if (!paso) return;

            const safeEstado = paso.estado.toLowerCase().replace(/\s+/g, '_');
            const item = document.createElement('div');
            item.className = `checklist-item checklist-item--${safeEstado}`;

            // 1. Área del Ícono (Izquierda)
            const iconCol = document.createElement('div');
            iconCol.className = 'checklist-icon-col';

            const iconSpan = document.createElement('span');
            iconSpan.className = 'checklist-icon';

            const img = document.createElement('img');
            img.src = this._getIconFor(paso.estado, paso.label);
            img.alt = safeEstado;
            img.className = 'checklist-state-icon';
            iconSpan.appendChild(img);

            iconCol.appendChild(iconSpan);

            // Conector vertical
            if (idx < pasosEntries.length - 1) {
                const line = document.createElement('div');
                line.className = 'checklist-connector-line';
                iconCol.appendChild(line);
            }

            // 2. Área de Descripción (Centro)
            const contentCol = document.createElement('div');
            contentCol.className = 'checklist-content-col';

            const label = document.createElement('span');
            label.className = 'checklist-label';
            label.textContent = paso.label;

            contentCol.appendChild(label);

            // 3. Área de Acción / Status (Derecha)
            const actionCol = document.createElement('div');
            actionCol.className = 'checklist-action-col';

            const actionBadge = document.createElement('span');
            actionBadge.className = `checklist-status-badge badge--${safeEstado.replace(/_/g, '-')}`;
            actionBadge.textContent = paso.estado.toUpperCase();

            actionCol.appendChild(actionBadge);

            item.appendChild(iconCol);
            item.appendChild(contentCol);
            item.appendChild(actionCol);
            container.appendChild(item);
        });
    }

    _startPolling() {
        this._poll();
        this._pollTimer = setInterval(() => this._poll(), 30_000);
    }

    async _poll() {
        if (!this.root || !this.root.isConnected) {
            this._destroy();
            return;
        }
        try {
            const res = await fetch(`${window.planeacionChecklistUrl}/${this.otId}`);
            if (!res.ok) return;
            const data = await res.json();
            if (data && !data.error) {
                this._updateCard(data);
            }
        } catch (_) { }
    }

    _destroy() {
        clearInterval(this._pollTimer);
        this._pollTimer = null;
        if (this.root && this.root.isConnected) {
            this.root.remove();
        }
    }
}

class TermicoChecklistCard {
    constructor(otId, className, initialData, container) {
        this.otId = otId;
        this.className = className;
        this.container = container;
        this.tPieces = initialData.pieces || 0;
        this.tTratadas = initialData.tratadas || 0;
        this._build();
        this._poll();
        this._pollTimer = setInterval(() => this._poll(), 30_000);
    }

    _build() {
        this.root = document.createElement('div');
        this.root.style.flex = '0.7 1 0%';
        this.root.style.minWidth = '250px';
        this.root.style.cursor = 'pointer';

        this._renderContent();

        this.container.appendChild(this.root);

        this.root.addEventListener('click', () => {
            const tItems = this.root.querySelector('.checklist-items');
            if (tItems) {
                if (tItems.hidden) {
                    tItems.hidden = false;
                    this.root.classList.remove('is-closed');
                } else {
                    tItems.hidden = true;
                    this.root.classList.add('is-closed');
                }
            }
        });
    }

    _renderContent() {
        const isTermicoComplete = (this.tPieces > 0 && this.tTratadas >= this.tPieces);

        const tItems = this.root.querySelector('.checklist-items');
        const isHidden = tItems ? tItems.hidden : true;
        const hiddenClass = isHidden ? 'hidden' : '';
        const isClosedClass = tItems ? (this.root.classList.contains('is-closed') ? 'is-closed' : '') : 'is-closed';

        this.root.className = isTermicoComplete
            ? `fundicion-checklist-card card-state-completado ${isClosedClass}`
            : `fundicion-checklist-card card-state-incompleto ${isClosedClass}`;

        const baseUrl = window.baseUrl || (window.location.origin + '/');
        const slash = baseUrl.endsWith('/') ? '' : '/';
        const iconUrl = isTermicoComplete ? `${baseUrl}${slash}images/Color_Icons/Generales/Aprobado.png` : `${baseUrl}${slash}images/Color_Icons/Generales/tratamiento_termico.png`;
        const badgeClass = isTermicoComplete ? 'badge--completado' : 'badge--pendiente';
        const badgeText = isTermicoComplete ? 'COMPLETADO' : 'PENDIENTE';
        const iconStateClass = isTermicoComplete ? 'checklist-item--completado' : 'checklist-item--pendiente';

        this.root.innerHTML = `
            <div class="checklist-header">
                <span class="checklist-title">Tratamiento Térmico</span>
            </div>
            <div class="checklist-items" style="padding-top: 15px;">
                <div class="checklist-item ${iconStateClass}" title="Piezas en tratamiento térmico" style="cursor: help;">
                    <div class="checklist-icon-col">
                        <span class="checklist-icon">
                            <img src="${iconUrl}" alt="${badgeText.toLowerCase()}" class="checklist-state-icon">
                        </span>
                    </div>
                    <div class="checklist-content-col">
                        <span class="checklist-label">Piezas en tratamiento: ${this.tTratadas} / ${this.tPieces}</span>
                    </div>
                    <div class="checklist-action-col">
                        <span class="checklist-status-badge ${badgeClass}">${badgeText}</span>
                    </div>
                </div>
            </div>
        `;

        // Restaurar el estado de visibilidad usando el atributo HTML (no la clase CSS)
        // para que el listener de click (que usa tItems.hidden) funcione correctamente.
        const newItems = this.root.querySelector('.checklist-items');
        if (newItems) newItems.hidden = isHidden;
    }

    async _poll() {
        if (!this.root || !this.root.isConnected) {
            this._destroy();
            return;
        }
        try {
            const res = await fetch(`${window.termicoChecklistUrl}/${this.otId}`);
            if (!res.ok) return;
            const data = await res.json();
            if (data && !data.error && data[this.className]) {
                const newData = data[this.className];
                if (this.tTratadas !== newData.tratadas || this.tPieces !== newData.pieces) {
                    this.tTratadas = newData.tratadas;
                    this.tPieces = newData.pieces;
                    this._renderContent();
                }
            }
        } catch (_) { }
    }

    _destroy() {
        clearInterval(this._pollTimer);
        this._pollTimer = null;
        if (this.root && this.root.isConnected) {
            this.root.remove();
        }
    }
}


export { PTACardComponent, FundicionChecklistCard, PlaneacionChecklistCard, TermicoChecklistCard };
