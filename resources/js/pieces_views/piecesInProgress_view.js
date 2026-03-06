let wOrderArray = window.wOInProgress;

// ══════════════════════════════════════════════════════════
// PTACardComponent — componente reactivo de la card PTA
//
// Estrategia de snapshot:
//   El total inicial se congela con Object.freeze() en el
//   constructor, garantizando que nunca pueda aumentar aunque
//   el servidor devuelva un número mayor en futuras consultas.
//
// Anti-loops:
//   El setInterval solo actualiza el DOM si el elemento raíz
//   sigue montado (via `this.root.isConnected`). Al detectar
//   count === 0 se cancela el intervalo y se elimina el nodo.
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
        this.current = totalJuegos > 0 ? this._calcTerminadas(initialData, totalJuegos) : 0;
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

    // ── Calcula juegos terminados correctamente ───────────────
    _calcTerminadas(data, totalJuegos) {
        const liberadas = data.liberadas || 0;
        // Re-usar la misma lógica del Dashboard para calcular terminadas
        let terminadas = totalJuegos;
        if (this.classArray) {
            const soldKey = Object.keys(this.classArray["processes"]).find(k => k.includes("Soldadura PTA"));
            if (soldKey) {
                const badData = this.classArray["processes"][soldKey]["piecesBadData"] || [];
                const ptaBadJuegos = new Set(
                    badData.filter(p => p["process"] === "Soldadura PTA").map(p => p["setNumber"])
                ).size;
                terminadas = Math.max(0, totalJuegos - ptaBadJuegos);
            }
        }
        return terminadas;
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
        section.className = "process-section";
        section.style.cursor = "pointer";
        section.id = `pta-card-${this.uniqueId}`;
        section.title = "Ver resultados de Soldadura PTA";

        const title = document.createElement("h3");
        title.className = "process-title";
        title.innerHTML = "Resultados Soldadura PTA";
        section.appendChild(title);

        // Etiqueta juegos terminados correctamente (solo texto, sin barra)
        const label1 = this._buildLabel("label-term");
        section.appendChild(label1);

        // Label + Barra — Sin liberar / mixto (AZUL)
        const bar2 = this._buildBar("bar-sin", "#dbeafe", "linear-gradient(to right, #90bff3, #043885)");
        section.appendChild(bar2);

        // Label + Barra — Liberados por admin (VERDE)
        const bar4 = this._buildBar("bar-lib", "#e1fcc6", "linear-gradient(to right, #9ff390, #0a8504)");
        section.appendChild(bar4);

        // Label + Barra apilada — Juegos Totales (azul = sin liberar | verde = liberados)
        const labelTot = this._buildLabel("label-tot");
        section.appendChild(labelTot);
        const barTot = this._buildStackedBar("bar-tot");
        section.appendChild(barTot);

        // Click: navegar a la vista de resultados
        // (se registra ANTES de _updateBars para que un posible error
        //  en la actualización de barras no bloquee la navegación)
        section.addEventListener("click", () => {
            const base = window.ptaResultsBaseUrl || (window.baseUrl + "/admin/pta/results");
            window.location.href = `${base}/${this.otId}?clase_id=${this.classId}`;
        });

        // Actualizar valores iniciales (skip connection check, aún no está en DOM)
        this._updateBars(section, true);

        return section;
    }

    // ── Construye un label encima de la barra ─────────────────
    _buildLabel(labelId) {
        const label = document.createElement("div");
        label.className = "pta-bar-label";
        label.id = `${labelId}-${this.uniqueId}`;
        label.style.cssText = "font-size:12px;color:#fff;text-align:left;margin-top:4px;margin-bottom:2px;font-weight:600;";
        return label;
    }

    // ── Construye una barra vacía con IDs predecibles ────────
    // bgColor: color de fondo del contenedor; fillGradient: gradiente del fill
    _buildBar(barId, bgColor = "#dbeafe", fillGradient = "linear-gradient(to right, #1d415e, #0060ae)") {
        const wrap = document.createElement("div");
        wrap.className = "progress-bar";
        wrap.style.backgroundColor = bgColor;
        wrap.id = `${barId}-${this.uniqueId}`;

        const fill = document.createElement("div");
        fill.className = `pta-bar-fill ${barId}-fill progress`;
        fill.style.background = fillGradient;
        fill.style.width = "0%";
        wrap.appendChild(fill);

        const info = document.createElement("div");
        info.className = "progress-percentage";
        wrap.appendChild(info);

        return wrap;
    }

    // ── Construye la barra apilada de Juegos Totales ──────────
    // Siempre al 100%. Segmento izquierdo (violeta) = liberados,
    // segmento derecho (naranja) = sin liberar. Conforme avanzan liberados
    // el violeta crece y el naranja se reduce.
    _buildStackedBar(barId) {
        const wrap = document.createElement("div");
        wrap.className = "progress-bar";
        wrap.style.backgroundColor = "#e4d9f7"; // fondo suave si nada liberado
        wrap.style.position = "relative";
        wrap.style.overflow = "hidden";
        wrap.id = `${barId}-${this.uniqueId}`;

        // Segmento VIOLETA: juegos liberados (crece desde la izquierda)
        const fillLib = document.createElement("div");
        fillLib.className = "pta-bar-fill progress";
        fillLib.id = `${barId}-fill-lib-${this.uniqueId}`;
        fillLib.style.cssText = "background: linear-gradient(to right, #c084fc, #7c3aed); width:0%; position:absolute; left:0; top:0; height:100%; transition: width .5s ease;";
        wrap.appendChild(fillLib);

        // Segmento NARANJA: juegos sin liberar (lo que queda a la derecha)
        const fillSin = document.createElement("div");
        fillSin.className = "pta-bar-fill progress";
        fillSin.id = `${barId}-fill-sin-${this.uniqueId}`;
        fillSin.style.cssText = "background: linear-gradient(to right, #ffb347, #e65c00); width:100%; position:absolute; right:0; top:0; height:100%; transition: width .5s ease;";
        wrap.appendChild(fillSin);

        const info = document.createElement("div");
        info.className = "progress-percentage";
        info.style.position = "relative";
        info.style.zIndex = "1";
        wrap.appendChild(info);

        return wrap;
    }

    // ── Actualiza solo los elementos internos (sin re-render) ─
    _updateBars(root, skipConnectionCheck) {
        root = root || this.root;
        if (!root) return;
        if (!skipConnectionCheck && !root.isConnected) return;

        const terminadas = this.current;

        // sinLiberar se calcula localmente: juegos terminados que aún no están liberados.
        // Esto evita el bug donde el servidor devuelve 0 porque auto-libera al guardar.
        const sinLiberar = Math.max(0, terminadas - this.liberadas - this.rechazadas);

        // Etiqueta terminadas (texto informativo, sin barra)
        const labelT = root.querySelector(`#label-term-${this.uniqueId}`);
        if (labelT) labelT.textContent =
            `Terminadas: ${terminadas} juego${terminadas !== 1 ? 's' : ''} correctos`;

        // —— helper interno —————————————————————————————————
        const setBar = (barId, labelId, count, labelText, countText) => {
            const pct = terminadas > 0 ? Math.min(100, Math.round((count / terminadas) * 100)) : 0;
            const wrap = root.querySelector(`#${barId}-${this.uniqueId}`);
            if (wrap) {
                wrap.querySelector(".pta-bar-fill")?.style.setProperty("width", `${pct}%`);
                const info = wrap.querySelector(".progress-percentage");
                if (info) info.textContent = `${pct}% ${count}/${terminadas} ${countText}`;
            }
            const lbl = root.querySelector(`#${labelId}-${this.uniqueId}`);
            if (lbl) lbl.textContent = labelText;
        };

        // Barra sin liberar (AZUL) — calculado localmente
        setBar("bar-sin", "label-sin", sinLiberar,
            `Sin liberar: ${sinLiberar}/${terminadas}`,
            "juegos sin liberar"
        );

        // Barra liberados (VERDE)
        setBar("bar-lib", "label-lib", this.liberadas,
            `Liberados: ${this.liberadas}/${terminadas}`,
            "juegos liberados"
        );

        // ── Barra apilada: Juegos Totales (siempre 100% llena) ──────────
        // Violeta (liberados) crece desde la izquierda.
        // Naranja (sin liberar) ocupa el resto hacia la derecha.
        const pctLib = terminadas > 0 ? Math.min(100, Math.round((this.liberadas / terminadas) * 100)) : 0;
        const pctSin = 100 - pctLib; // naranja = lo que queda

        const barTot = root.querySelector(`#bar-tot-${this.uniqueId}`);
        const lblTot = root.querySelector(`#label-tot-${this.uniqueId}`);
        if (barTot) {
            const fLib = barTot.querySelector(`#bar-tot-fill-lib-${this.uniqueId}`);
            const fSin = barTot.querySelector(`#bar-tot-fill-sin-${this.uniqueId}`);
            if (fLib) fLib.style.width = `${pctLib}%`;
            if (fSin) fSin.style.width = `${pctSin}%`;
            const info = barTot.querySelector(".progress-percentage");
            if (info) info.textContent =
                `${this.liberadas} liberados + ${sinLiberar} pendientes = ${terminadas} total`;
        }
        if (lblTot) lblTot.textContent =
            `Juegos Totales: ${this.liberadas}/${terminadas} liberados`;
    }

    // ── Polling AJAX cada 10 s ───────────────────────────────
    _startPolling() {
        this._pollTimer = setInterval(() => this._poll(), 10_000);
    }

    async _poll() {
        // Guard: petición en vuelo
        if (this._busy) return;
        // Guard extra: si el nodo ya fue montado, verificar que sigue conectado
        if (this._mounted && this.root && !this.root.isConnected) return;
        this._busy = true;

        try {
            const res = await fetch(`${window.baseUrl}/piecesInProgress/ptaCard/${this.otId}/${this.classId}`);
            if (!res.ok) return;

            const data = await res.json();
            const rawTotal = data.totalPTA || 0;
            const totalJuegos = rawTotal > 0 ? Math.round(rawTotal / 2) : 0;

            // ── Primera activación (modo dormido → con piezas) ────────────
            if (!this._mounted && totalJuegos > 0) {
                // Congelar el total en el primer tick con datos reales
                this.snapshot = Object.freeze({ total: totalJuegos });
                this.current = this._calcTerminadasFromData(data, totalJuegos);
                this.liberadas = data.liberadas || 0;
                this.rechazadas = data.rechazadas || 0;
                this.sinLiberar = data.sinLiberar || 0;
                this._mount();
                return;
            }

            if (!this._mounted) return; // aún sin piezas, seguir esperando

            // Regla 2: el total NUNCA aumenta — comparamos con el snapshot
            const newTerminadas = Math.min(
                this.snapshot.total,
                this._calcTerminadasFromData(data, totalJuegos)
            );
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

    // Helper para calcular terminadas a partir de datos crudos del AJAX
    _calcTerminadasFromData(data, totalJuegos) {
        // En el AJAX ya tenemos `terminadas` aunque aquí preferimos recalcular
        // desde badData si está disponible; si no, confiamos en el valor del servidor.
        return this.classArray
            ? this._calcTerminadas(data, totalJuegos)
            : Math.max(0, totalJuegos - (data.bad || 0));
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

class Dashboard {
    constructor(wOrderArray) {
        this.wOrderArray = wOrderArray;
    }
    //Función para general carrusel
    generateSection($workOrder, $class, $processes) {
        let divHeader = document.createElement("div");
        divHeader.className = "header";
        let workOrderDiv = document.createElement("div");
        workOrderDiv.className = "work-order";
        let h2 = document.createElement("h2");
        h2.className = "work-order-title text-header";
        h2.innerHTML = "Orden de trabajo: " + $workOrder;
        let moldingLabel = document.createElement("label");
        moldingLabel.className = "molding-label text-header";
        label.innerHTML = "Moldura: " + $workOrder["moldura"];
        let classLabel = document.createElement("label");
        classLabel.className = "class-label text-header";
        classLabel.innerHTML = "Clase: " + $class;

        //Insertar los elementos en el div
        workOrderDiv.appendChild(h2);
        workOrderDiv.appendChild(moldingLabel);
        workOrderDiv.appendChild(classLabel);
        divHeader.appendChild(workOrderDiv);
        section.appendChild(divHeader);
        return section;
    }
    createSections() {
        let body = document.querySelector("body");
        Object.values(this.wOrderArray).forEach((workOrder, indexWo) => {
            let wOrderName = Object.keys(this.wOrderArray)[indexWo];
            Object.values(workOrder["classes"]).forEach((classArray, indexClass) => {
                let section = document.createElement("section");
                section.className = "section";
                let className = Object.keys(workOrder["classes"])[indexClass];
                let headerSection = this.generateHeaderofWorkOrder(wOrderName, workOrder["molding"], className, classArray);
                let processesSection = document.createElement("div");
                processesSection.className = "processes-section";

                Object.values(classArray["processes"]).forEach((processesArray, indexProcess) => {
                    let processName = Object.keys(classArray["processes"])[indexProcess]
                    let previousProcess = classArray["processes"][Object.keys(classArray["processes"])[indexProcess - 1]];
                    let limitPieces = previousProcess ? previousProcess["pieces"]["good"] : classArray["pieces"];

                    processesSection.appendChild(this.generateProcessSection(processesArray, processName, limitPieces, classArray["pieces"]));
                });

                // ── Card PTA: instanciar SIEMPRE si la OT tiene Soldadura PTA ─
                // Si aún no hay piezas (ptaData=null), el componente arrancará en modo
                // dormido y se montará solo en cuanto el polling detecte la primera pieza.
                const processKeys = Object.keys(classArray["processes"]);
                if (processKeys.some(k => k.includes("Soldadura PTA"))) {
                    let userProfile = document.getElementById("profile");
                    if (userProfile && (userProfile.value == "1" || userProfile.value == "2")) {
                        const classId = classArray["id"];
                        const ptaData = (window.ptaCardsData && window.ptaCardsData[wOrderName] && window.ptaCardsData[wOrderName][classId])
                            ? window.ptaCardsData[wOrderName][classId]
                            : { totalPTA: 0, liberadas: 0 };
                        this.generatePTASection(ptaData, wOrderName, classId, classArray, processesSection);
                    }
                }

                section.appendChild(headerSection);
                section.appendChild(processesSection);
                body.appendChild(section);
            });
        });
    }
    generateHeaderofWorkOrder(wOrderName, moldingName, className, classArray) {
        let valueText = [
            [
                `${wOrderName} ${moldingName}`,
                `${className}`,
                `Fecha de inicio: ${classArray["startDate"]}`,
                `Fecha de término: ${classArray["endDate"]}`,
            ],
            [
                `Pedido: ${classArray["order"]}`,
                `Pedido mas consignación: ${classArray["pieces"]}`,
                `Piezas completadas: ${this.getCompletedPieces(classArray)}`,
            ],
        ];
        let classText = [
            ["workOrder-text", "class-text", "start-date-text", "end-date-text"],
            ["order-text", "pieces-text", "completed-pieces-text"],
        ];

        let header_section = document.createElement("divHeader");
        header_section.className = "header-section";

        for (let i = 0; i < valueText.length; i++) {
            let div = document.createElement("div");
            div.className = "title-div";
            for (let j = 0; j < valueText[i].length; j++) {
                let h3 = document.createElement("h3");
                h3.className = classText[i][j];
                h3.innerHTML = valueText[i][j];

                div.appendChild(h3);
            }
            header_section.appendChild(div);
        }

        let a = document.createElement("a");
        a.href = `/finishOrder/${wOrderName}/${className}`;
        a.className = "finish-order";
        a.innerHTML = "Finalizar pedido";
        a.addEventListener("click", (e) => {
            e.preventDefault();
            if (confirm("¿Estás seguro de que deseas finalizar esta orden de trabajo?")) {
                let url = `${window.baseUrl}/finishOrder/${wOrderName}/${className}`;
                window.location.href = url;
            }
        });
        header_section.appendChild(a);

        return header_section;
    }

    getCompletedPieces(classArray) {
        //Obtener las piezas del ultimo proceso de la clase
        if (!classArray["processes"] || Object.keys(classArray["processes"]).length === 0) {
            return 0;
        }
        let completedPieces;
        let lastProcess = Object.keys(classArray["processes"])[Object.keys(classArray["processes"]).length - 1];
        if (lastProcess == "Soldadura PTA" || lastProcess == "Soldadura") {
            // Si ultimo proceso es Soldadura o Soldadura PTA
            let otherProcess = lastProcess == "Soldadura PTA" ? "Soldadura" : "Soldadura PTA";
            // Si se incluyen los dos procesos en la clase, sumar las piezas buenas
            if (Object.keys(classArray["processes"]).includes(otherProcess)) {
                completedPieces =
                    classArray["processes"][lastProcess]["pieces"]["good"] +
                    classArray["processes"][otherProcess]["pieces"]["good"];
            } else {
                completedPieces = classArray["processes"][lastProcess]["pieces"]["good"];
            }
        } else {
            completedPieces = Object.values(classArray["processes"])[Object.keys(classArray["processes"]).length - 1][
                "pieces"
            ]["good"];
        }
        return completedPieces;
    }
    generateProcessSection(processesArray, processName, limitPieces, pedido) {
        let processSection = document.createElement("div");
        processSection.className = "process-section";

        let processTitle = document.createElement("h3");
        processTitle.className = "process-title";
        processTitle.innerHTML = processName;
        processSection.appendChild(processTitle);

        let limitLabel = document.createElement("label");
        limitLabel.className = "limit-label";
        limitLabel.style.fontSize = "12px";
        limitLabel.style.color = "#fff";
        limitLabel.innerHTML = `Total disponible: ${limitPieces - processesArray["pieces"]["bad"]}`;
        processSection.appendChild(limitLabel);

        let pieces = [processesArray["pieces"]["good"], processesArray["pieces"]["bad"]];
        for (let i = 0; i < pieces.length; i++) {
            //Crear barra de progreso
            let progressBar = document.createElement("div");
            progressBar.className = "progress-bar";
            progressBar.style.backgroundColor = i == 0 ? "#e1fcc6" : "#fcc6c6";

            let progress = document.createElement("div");
            progress.className = i == 0 ? "good-progress" : "bad-progress";
            progress.classList.add("progress");
            let percentage = pieces[i] == 0 ? 0 : (pieces[i] * 100) / limitPieces;

            progress.style.width = `${percentage}%`;

            percentage = percentage != 0 ? percentage.toFixed(1) : 0;
            let div = document.createElement("div");
            div.className = "progress-percentage";
            div.innerHTML = pieces[i] == 1 ? `${percentage}% ${pieces[i]} pieza` : `${percentage}% ${pieces[i]} piezas`;

            progressBar.appendChild(progress);
            progressBar.appendChild(div);
            processSection.appendChild(progressBar);
        }

        //Agregar evento al div de progreso
        processSection.addEventListener("click", () => {
            this.generateDivBadPieces(processName, processesArray["piecesBadData"]);
        });
        return processSection;
    }

    // ── Card especial para Resultados de Soldadura PTA ──────────────────────
    generatePTASection(ptaData, otId, classId, classArray, container) {
        // Monta el componente reactivo directamente en el container dado.
        // El componente gestiona su propio ciclo de vida.
        new PTACardComponent(otId, classId, ptaData, container, classArray);
    }
    generateDivBadPieces(processName, badPieces) {
        //Creacion del div de opacidad de fondo
        let div = document.createElement("div");
        div.className = "opacity-div";

        //Creacion del div en donde se mostrara la tabla de las piezas malas
        let modal = document.createElement("div");
        modal.className = "modal";

        //Creacion del titulo del proceso al que se da click
        let modalTitle = document.createElement("h2");
        modalTitle.className = "modal-title";
        modalTitle.innerHTML = `Proceso: ${processName}`;
        modal.appendChild(modalTitle);

        //Creacion del boton de cerrar el modal
        let modalClose = document.createElement("button");
        modalClose.className = "modal-close";

        let imageClose = document.createElement("img");
        imageClose.className = "img-close";
        imageClose.src = window.cerrarImgUrl;
        modalClose.appendChild(imageClose);

        modalClose.addEventListener("click", function () {
            document.body.removeChild(div);
            document.body.style.overflow = "auto";
        });
        modal.appendChild(modalClose);

        //Creacion de la tabla de las piezas malas
        let table = this.createTableBadPieces(badPieces, processName);
        modal.appendChild(table);

        div.addEventListener("click", function (e) {
            if (e.target === div) {
                document.body.removeChild(div);
                document.body.style.overflow = "auto";
            }
        });
        div.appendChild(modal);
        document.body.appendChild(div);
        document.body.style.overflow = "hidden";
    }
    createTableBadPieces(badPieces, processName) {
        let table = document.createElement("table");
        table.className = "bad-pieces-table";
        let thead = document.createElement("thead");
        let headerRow = document.createElement("tr");
        let headers =
            processName == "Operacion Equipo"
                ? ["Pieza", "Numero de juego", "Operador", "Proceso", "Operacion", "Error"]
                : ["Pieza", "Numero de juego", "Operador", "Proceso", "Error"];

        //Insertar encabezados de la tabla
        headers.forEach((header) => {
            let th = document.createElement("th");
            th.innerHTML = header;
            th.style.width = headers.length / 100 + "%"; // Ajustar el ancho de las columnas
            headerRow.appendChild(th);
        });

        //Insertar los datos de cada una de las piezas malas
        //prettier-ignore
        let tbody = document.createElement("tbody");
        if (Object.keys(badPieces).length > 0) {
            // Para Soldadura PTA, agrupar por número de juego (setNumber)
            if (processName.includes("Soldadura PTA")) {
                let setGroups = {};
                Object.values(badPieces).forEach((piece) => {
                    let key = piece["setNumber"];
                    if (!setGroups[key]) {
                        setGroups[key] = {
                            pieces: [],
                            setNumber: piece["setNumber"],
                            operator: piece["operator"],
                            process: piece["process"],
                            error: piece["error"],
                        };
                    }
                    setGroups[key].pieces.push(piece["piece"]);
                });
                Object.values(setGroups).forEach((group) => {
                    let row = document.createElement("tr");
                    let pieceData = [
                        group.pieces.join(", "),
                        group.setNumber,
                        group.operator,
                        group.process,
                        group.error,
                    ];
                    pieceData.forEach((data) => {
                        let td = document.createElement("td");
                        td.innerHTML = data;
                        row.appendChild(td);
                    });
                    tbody.appendChild(row);
                });
            } else {
                Object.values(badPieces).forEach((piece) => {
                    let row = document.createElement("tr");
                    let pieceData =
                        processName == "Operacion Equipo"
                            ? [
                                piece["piece"],
                                piece["setNumber"],
                                piece["operator"],
                                piece["process"],
                                piece["operation"],
                                piece["error"],
                            ]
                            : [piece["piece"], piece["setNumber"], piece["operator"], piece["process"], piece["error"]];
                    pieceData.forEach((data) => {
                        let td = document.createElement("td");
                        td.innerHTML = data;
                        row.appendChild(td);
                    });
                    tbody.appendChild(row);
                });
            }
        } else {
            let row = document.createElement("tr");
            let td = document.createElement("td");
            td.colSpan = headers.length;
            td.classList.add("no-bad-pieces");
            td.innerHTML = "No hay piezas malas registradas para este proceso.";
            row.appendChild(td);
            tbody.appendChild(row);
        }
        thead.appendChild(headerRow);
        table.appendChild(thead);
        table.appendChild(tbody);
        return table;
    }
}



let div_opacity = document.querySelector(".div-opacity");
if (div_opacity) {
    document.querySelector(".btn-cerrar").addEventListener("click", () => {
        let div_padre = document.querySelector(".div-opacity");
        div_padre.remove();
    });
    div_opacity.addEventListener("click", () => {
        let div_padre = document.querySelector(".div-opacity");
        div_padre.remove();
    });
}

if (Object.keys(wOrderArray).length > 0) {
    let dashboard = new Dashboard(wOrderArray);
    dashboard.createSections();



    const secciones = document.querySelectorAll("section");
    let scrollTimeout = null;

    function getClosestSection() {
        let closest = null;
        let minDist = Infinity;
        const scrollY = window.scrollY;

        secciones.forEach((sec) => {
            const dist = Math.abs(sec.offsetTop - scrollY);
            if (dist < minDist) {
                minDist = dist;
                closest = sec;
            }
        });

        return closest;
    }

    window.addEventListener("scroll", () => {
        if (scrollTimeout) {
            clearTimeout(scrollTimeout);
        }

        // Espera 200ms tras dejar de hacer scroll
        scrollTimeout = setTimeout(() => {
            const destino = getClosestSection();
            if (destino) {
                destino.scrollIntoView({ behavior: "smooth" });
            }
        }, 200);
    });
} else {
    let body = document.querySelector("body");
    let noDataMessage = document.createElement("h2");
    noDataMessage.className = "no-data-message";
    noDataMessage.innerHTML = "No hay órdenes de trabajo en progreso.";
    body.appendChild(noDataMessage);

}
