let wOrderArray = window.wOInProgress;
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
    //prettier-ignore
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

                    // Obtener datos de tiempo para este proceso si existen
                    let timeData = null;
                    if (classArray["time_data"] && classArray["time_data"][processName]) {
                        timeData = classArray["time_data"][processName];
                    }

                    processesSection.appendChild(this.generateProcessSection(processesArray, processName, limitPieces, classArray["pieces"], timeData));
                });
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
    generateProcessSection(processesArray, processName, limitPieces, pedido, timeData = null) {
        let processSection = document.createElement("div");
        processSection.className = "process-section";

        let processTitle = document.createElement("h3");
        processTitle.className = "process-title";
        processTitle.innerHTML = processName;
        processSection.appendChild(processTitle);

        let pieces = [processesArray["pieces"]["good"], processesArray["pieces"]["bad"]];
        for (let i = 0; i < pieces.length; i++) {
            //Crear barra de progreso
            let progressBar = document.createElement("div");
            progressBar.className = "progress-bar";
            progressBar.style.backgroundColor = i == 0 ? "#e1fcc6" : "#fcc6c6";

            let progress = document.createElement("div");
            progress.className = i == 0 ? "good-progress" : "bad-progress";
            progress.classList.add("progress");
            let piecesNoRegistered = pedido - limitPieces;
            let percentage = pieces[i] == 0 ? 0 : (pieces[i] * 100) / (limitPieces + piecesNoRegistered);

            progress.style.width = `${percentage}%`;

            percentage = percentage != 0 ? percentage.toFixed(1) : 0;
            let div = document.createElement("div");
            div.className = "progress-percentage";
            div.innerHTML = pieces[i] == 1 ? `${percentage}% ${pieces[i]} pieza` : `${percentage}% ${pieces[i]} piezas`;

            progressBar.appendChild(progress);
            progressBar.appendChild(div);
            processSection.appendChild(progressBar);
        }

        // ========== AGREGAR DATOS DE TIEMPO SI EXISTEN ==========
        if (timeData) {
            let timeDataSection = this.renderTimeData(timeData);
            processSection.appendChild(timeDataSection);
        }

        //Agregar evento al div de progreso para mostrar piezas malas
        processSection.addEventListener("click", () => {
            this.generateDivBadPieces(processName, processesArray["piecesBadData"]);
        });
        return processSection;
    }

    /**
     * Renderizar datos de tiempo de producción dentro de una tarjeta de proceso
     * @param {Object} timeData - Datos de tiempo del proceso
     * @returns {HTMLElement} Sección con datos de tiempo formateados
     */
    renderTimeData(timeData) {
        let timeSection = document.createElement("div");
        timeSection.className = "process-time-data";

        // Separador visual
        let separator = document.createElement("hr");
        separator.style.border = "none";
        separator.style.borderTop = "1px solid rgba(255, 255, 255, 0.2)";
        separator.style.margin = "10px 0";
        timeSection.appendChild(separator);

        // Hora inicio → fin (duración)
        if (timeData.hora_inicio && timeData.hora_fin) {
            let timelineRow = document.createElement("div");
            timelineRow.className = "time-info-row";
            timelineRow.innerHTML = `⏱ ${timeData.hora_inicio} → ${timeData.hora_fin} (${timeData.duracion_horas.toFixed(1)}h)`;
            timeSection.appendChild(timelineRow);
        }

        // Utilización con barra visual
        if (timeData.utilizacion !== undefined) {
            let utilizacionLabel = document.createElement("div");
            utilizacionLabel.className = "time-info-row";
            utilizacionLabel.innerHTML = `📊 Utilización: ${timeData.utilizacion}%`;
            timeSection.appendChild(utilizacionLabel);

            // Barra de utilización
            let utilizacionBarContainer = document.createElement("div");
            utilizacionBarContainer.className = "time-utilization-bar-container";
            utilizacionBarContainer.style.width = "100%";
            utilizacionBarContainer.style.height = "8px";
            utilizacionBarContainer.style.backgroundColor = "rgba(255, 255, 255, 0.2)";
            utilizacionBarContainer.style.borderRadius = "4px";
            utilizacionBarContainer.style.overflow = "hidden";
            utilizacionBarContainer.style.marginTop = "5px";

            let utilizacionBar = document.createElement("div");
            utilizacionBar.className = "time-utilization-bar";
            utilizacionBar.style.width = `${timeData.utilizacion}%`;
            utilizacionBar.style.height = "100%";
            utilizacionBar.style.backgroundColor = timeData.utilizacion >= 80 ? "#4CAF50" : timeData.utilizacion >= 50 ? "#FFC107" : "#FF5722";
            utilizacionBar.style.transition = "width 0.3s ease";

            utilizacionBarContainer.appendChild(utilizacionBar);
            timeSection.appendChild(utilizacionBarContainer);
        }

        // Tiempos muertos
        if (timeData.tiempo_muerto_horas !== undefined && timeData.tiempo_muerto_horas > 0) {
            let deadTimeRow = document.createElement("div");
            deadTimeRow.className = "time-info-row";
            deadTimeRow.innerHTML = `⏸ ${timeData.tiempo_muerto_horas.toFixed(1)}h muertos`;
            deadTimeRow.style.marginTop = "5px";
            timeSection.appendChild(deadTimeRow);
        }

        // Tasa de producción
        if (timeData.tasa_produccion && timeData.tasa_produccion !== 'N/A') {
            let rateRow = document.createElement("div");
            rateRow.className = "time-info-row";
            rateRow.innerHTML = `🚀 ${timeData.tasa_produccion}`;
            rateRow.style.marginTop = "5px";
            timeSection.appendChild(rateRow);
        }

        // Badge de cuello de botella
        if (timeData.es_cuello_botella) {
            let bottleneckBadge = document.createElement("div");
            bottleneckBadge.className = "bottleneck-indicator";
            bottleneckBadge.innerHTML = "⚠️ Cuello de botella";
            bottleneckBadge.style.marginTop = "8px";
            bottleneckBadge.style.padding = "4px 8px";
            bottleneckBadge.style.backgroundColor = "#FF5722";
            bottleneckBadge.style.color = "#fff";
            bottleneckBadge.style.borderRadius = "4px";
            bottleneckBadge.style.fontSize = "0.85em";
            bottleneckBadge.style.fontWeight = "bold";
            bottleneckBadge.style.textAlign = "center";
            timeSection.appendChild(bottleneckBadge);
        }

        return timeSection;
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

    // Auto-scroll desactivado para evitar comportamiento no deseado
    /*
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
    */
}
else {
    let body = document.querySelector("body");
    let noDataMessage = document.createElement("h2");
    noDataMessage.className = "no-data-message";
    noDataMessage.innerHTML = "No hay órdenes de trabajo en progreso.";
    body.appendChild(noDataMessage);
}
