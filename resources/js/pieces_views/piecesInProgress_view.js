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
                let headerSection = this.generateHeaderofWorkOrder( wOrderName, workOrder["molding"], className, classArray);
                let processesSection = document.createElement("div");
                processesSection.className = "processes-section";
            
                Object.values(classArray["processes"]).forEach((processesArray, indexProcess) => {
                    let processName = Object.keys(classArray["processes"])[indexProcess];
                    processesSection.appendChild(this.generateProcessSection(processesArray, processName, classArray["pieces"]));
                });
                section.appendChild(headerSection);
                section.appendChild(processesSection);
                body.appendChild(section);
            });
        });
    }
    generateHeaderofWorkOrder(wOrderName, moldingName, className, classArray) {
        let valueText = [
            [`OT: ${wOrderName}`, `Moldura: ${moldingName}`, `Clase: ${className}`],
            [
                `Pedido: ${this.getCompletedPieces(classArray)}/${classArray["pieces"]}`,
                `Fecha de inicio: ${classArray["startDate"]}`,
                `Fecha de término: ${classArray["endDate"]}`,
            ],
        ];
        let classText = [
            ["workOrder-text", "molding-text", "class-text"],
            ["pieces-text", "start-date-text", "end-date-text"],
        ];

        let header_section = document.createElement("divHeader");
        header_section.className = "header-section";

        for (let i = 0; i < valueText.length; i++) {
            let div = document.createElement("div");
            div.className = "title-div";
            for (let j = 0; j < valueText[i].length; j++) {
                let h3 = document.createElement("h3");
                h3.className = classText[i][j];
                h3.classList.add("text-header-class");
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
        let completedPieces = Object.values(classArray["processes"])[Object.keys(classArray["processes"]).length - 1][
            "pieces"
        ]["good"];
        return completedPieces;
    }
    generateProcessSection(processesArray, processName, order) {
        let processSection = document.createElement("div");
        processSection.className = "process-section";

        let processTitle = document.createElement("h3");
        processTitle.className = "process-title";
        processTitle.innerHTML = processName;
        processSection.appendChild(processTitle);
        //Crear barra de progreso
        let progressBar = document.createElement("div");
        progressBar.className = "progress-bar";

        let pieces = [processesArray["pieces"]["good"], processesArray["pieces"]["bad"]];
        for (let i = 0; i < pieces.length; i++) {
            let progressBar = document.createElement("div");
            progressBar.className = "progress-bar";

            let progress = document.createElement("div");
            progress.className = i == 0 ? "good-progress" : "bad-progress";
            progress.classList.add("progress");
            let percentage = (pieces[i] * 100) / order;
            progress.style.width = `${percentage}%`;

            percentage = percentage != 0 ? percentage.toFixed(1) : 0;
            let div = document.createElement("div");
            div.className = "progress-percentage";
            div.innerHTML = `${percentage}%`;

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
if(div_opacity){
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
