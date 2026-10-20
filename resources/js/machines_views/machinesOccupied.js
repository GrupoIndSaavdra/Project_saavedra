function createTable(machines, form) {
    const table = document.createElement("table");
    table.classList.add("machines-table");

    const thead = document.createElement("thead");
    thead.classList.add("machines-table-header");

    const tbody = document.createElement("tbody");
    tbody.classList.add("machines-table-body");

    let tr = document.createElement("tr");

    const iconSin = window.imgSinOrden || (window.baseUrl + "/images/Sin_Orden.png");
    const iconAsc = window.imgOrdenAsc || (window.baseUrl + "/images/Orden_Asc.png");
    const iconDesc = window.imgOrdenDesc || (window.baseUrl + "/images/Orden_Desc.png");

    // Nuevo orden de columnas con soporte de ordenamiento
    let arrayHeader = [
        { label: "Proceso", width: "22%", key: "process", sortable: true },
        { label: "Operador Asignado", width: "38%", key: "operator", sortable: true },
        { label: "Máquina", width: "20%", key: "machine", sortable: true },
        { label: "Acciones", width: "20%", key: "actions", sortable: false }
    ];

    let sortableHeaders = [];

    arrayHeader.forEach((header, colIndex) => {
        let th = document.createElement("th");
        if (header.width) {
            th.style.width = header.width;
        }

        if (header.sortable) {
            th.classList.add("sortable-th");
            th.dataset.order = "0"; // 0: Sin Orden, 1: Asc, 2: Desc
            th.dataset.colKey = header.key;
            th.title = `Clic para ordenar por ${header.label}`;

            let wrapper = document.createElement("div");
            wrapper.classList.add("th-sort-wrapper");

            let labelSpan = document.createElement("span");
            labelSpan.textContent = header.label;

            let sortIcon = document.createElement("img");
            sortIcon.src = iconSin;
            sortIcon.alt = "Ordenar";
            sortIcon.classList.add("sort-icon");

            wrapper.appendChild(labelSpan);
            wrapper.appendChild(sortIcon);
            th.appendChild(wrapper);

            sortableHeaders.push({ th, sortIcon, header });
        } else {
            th.textContent = header.label;
        }

        tr.appendChild(th);
    });
    thead.appendChild(tr);
    table.appendChild(thead);

    // Crear las filas de las máquinas
    let rows = [];
    if (machines && machines.length > 0) {
        const cncIconSrc = window.imgProgramsCNC || (window.baseUrl + "/images/ProgramsCNC.png");
        const operatorIconSrc = window.imgOperator || (window.baseUrl + "/images/Operador-Icon.png");
        const lockedIconSrc = window.imgLocked || (window.baseUrl + "/images/bloqueado.png");
        const unlockedIconSrc = window.imgUnlocked || (window.baseUrl + "/images/desbloqueado.png");

        machines.forEach((machine, rowIndex) => {
            let tr = document.createElement("tr");
            tr.classList.add("machine-table-row");
            tr.dataset.originalIndex = rowIndex;
            tr.dataset.process = (machine.process || "").trim();
            tr.dataset.operator = (machine.operator && machine.operator !== "Sin asignar" ? machine.operator : "ZZZZ").trim();
            tr.dataset.machine = (machine.machine || "").toString().trim();

            // 1. Columna Proceso (encerrado en una cápsula estilizada)
            let tdProcess = document.createElement("td");
            tdProcess.classList.add("td-process");
            let processCapsule = document.createElement("div");
            processCapsule.classList.add("process-capsule");
            let spanProcess = document.createElement("span");
            spanProcess.classList.add("process-name");
            spanProcess.textContent = machine.process || "---";
            processCapsule.appendChild(spanProcess);
            tdProcess.appendChild(processCapsule);
            tr.appendChild(tdProcess);

            // 2. Columna Operador Asignado (con Operador-Icon y diseño espacioso de dos líneas)
            let tdOperator = document.createElement("td");
            tdOperator.classList.add("td-operator");

            let operatorContainer = document.createElement("div");
            operatorContainer.classList.add("operator-cell-content");

            if (machine.operator && machine.operator !== "Sin asignar") {
                // Contenedor circular para el icono de operador
                let avatarBox = document.createElement("div");
                avatarBox.classList.add("operator-icon-box");

                let opImg = document.createElement("img");
                opImg.src = operatorIconSrc;
                opImg.alt = "Operador";
                opImg.classList.add("operator-icon-img");
                avatarBox.appendChild(opImg);

                // Columna de información: Nombre arriba y matrícula abajo
                let infoCol = document.createElement("div");
                infoCol.classList.add("operator-text-col");

                let opName = document.createElement("span");
                opName.classList.add("operator-name");
                opName.textContent = machine.operator;
                infoCol.appendChild(opName);

                if (machine.operator_matricula) {
                    let opMatricula = document.createElement("span");
                    opMatricula.classList.add("operator-matricula-badge");
                    opMatricula.textContent = `Mat. ${machine.operator_matricula}`;
                    infoCol.appendChild(opMatricula);
                }

                operatorContainer.appendChild(avatarBox);
                operatorContainer.appendChild(infoCol);
            } else {
                operatorContainer.classList.add("unassigned");
                let unassignedBadge = document.createElement("span");
                unassignedBadge.classList.add("unassigned-badge");
                unassignedBadge.textContent = "Sin operador asignado";
                operatorContainer.appendChild(unassignedBadge);
            }
            tdOperator.appendChild(operatorContainer);
            tr.appendChild(tdOperator);

            // 3. Columna Máquina (con icono CNC y píldora destacada)
            let tdMachine = document.createElement("td");
            tdMachine.classList.add("td-machine");

            let machinePill = document.createElement("div");
            machinePill.classList.add("machine-pill");

            let cncImg = document.createElement("img");
            cncImg.src = cncIconSrc;
            cncImg.alt = "CNC";
            cncImg.classList.add("cnc-machine-icon");

            let machineName = document.createElement("span");
            machineName.classList.add("machine-name");
            machineName.textContent = (machine.machine || "").toString().replace(/_/g, " y ");

            machinePill.appendChild(cncImg);
            machinePill.appendChild(machineName);
            tdMachine.appendChild(machinePill);
            tr.appendChild(tdMachine);

            // 4. Columna Acciones
            let tdActions = document.createElement("td");
            tdActions.classList.add("td-actions");
            let desoccupiedButton = document.createElement("button");
            desoccupiedButton.classList.add("btn", "btn-desoccupied");
            desoccupiedButton.type = "button";

            let iconWrapper = document.createElement("div");
            iconWrapper.classList.add("btn-lock-icon-wrapper");

            let lockedImg = document.createElement("img");
            lockedImg.src = lockedIconSrc;
            lockedImg.alt = "Bloqueado";
            lockedImg.classList.add("btn-icon-lock", "icon-locked");

            let unlockedImg = document.createElement("img");
            unlockedImg.src = unlockedIconSrc;
            unlockedImg.alt = "Desbloqueado";
            unlockedImg.classList.add("btn-icon-lock", "icon-unlocked");

            iconWrapper.appendChild(lockedImg);
            iconWrapper.appendChild(unlockedImg);

            let btnText = document.createElement("span");
            btnText.classList.add("btn-text");
            btnText.textContent = "Desocupar";

            desoccupiedButton.appendChild(iconWrapper);
            desoccupiedButton.appendChild(btnText);

            desoccupiedButton.onclick = (e) => {
                e.preventDefault();
                let machineLabel = (machine.machine || "").toString().replace(/_/g, " y ");
                let processLabel = machine.process || "---";
                let operatorLabel = machine.operator && machine.operator !== "Sin asignar" ? machine.operator : "Sin operador asignado";
                let matriculaLabel = machine.operator_matricula ? ` · Mat. ${machine.operator_matricula}` : "";

                const executeFreeUp = () => {
                    tr.classList.remove("machine-row-targeting");
                    document.querySelectorAll(".idMachine").forEach(el => el.remove());

                    let inputIdMachine = document.createElement("input");
                    inputIdMachine.type = "hidden";
                    inputIdMachine.name = "idMachine";
                    inputIdMachine.className = "idMachine";
                    inputIdMachine.value = machine.id;
                    form.appendChild(inputIdMachine);

                    form.submit();
                };

                tr.classList.add("machine-row-targeting");

                let confirmHtml = `¿Estás seguro de que deseas liberar la <strong style="color: #b30404;">Máquina ${machineLabel}</strong>?`;
                let plainConfirmText = `¿Estás seguro de que deseas liberar la Máquina ${machineLabel}?`;

                if (typeof Swal !== "undefined") {
                    Swal.fire({
                        title: `¿Desocupar <span style="color: #b30404;">Máquina ${machineLabel}</span>?`,
                        html: confirmHtml,
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#b30404", // Rojo Peligro Saavedra (skills/javascript_skill.md)
                        cancelButtonColor: "#030041",  // Azul Marino Saavedra (skills/javascript_skill.md)
                        confirmButtonText: "Sí, desocupar",
                        cancelButtonText: "Cancelar",
                        reverseButtons: true,
                        backdrop: "rgba(0, 10, 25, 0.85)"
                    }).then((result) => {
                        tr.classList.remove("machine-row-targeting");
                        if (result.isConfirmed) {
                            executeFreeUp();
                        }
                    });
                } else {
                    if (confirm(plainConfirmText)) {
                        executeFreeUp();
                    } else {
                        tr.classList.remove("machine-row-targeting");
                    }
                }
            };
            tdActions.appendChild(desoccupiedButton);
            tr.appendChild(tdActions);

            tbody.appendChild(tr);
            rows.push(tr);
        });

        // Función auxiliar para extraer número inicial en cadenas tipo "10", "1_2", etc.
        function extractFirstNumber(str) {
            let match = (str || "").toString().match(/\d+/);
            return match ? parseInt(match[0], 10) : 0;
        }

        // Lógica de ordenamiento interactivo
        sortableHeaders.forEach(({ th, sortIcon, header }) => {
            th.addEventListener("click", () => {
                // Resetear todos los demás encabezados a sin orden
                sortableHeaders.forEach((other) => {
                    if (other.th !== th) {
                        other.th.dataset.order = "0";
                        other.sortIcon.src = iconSin;
                    }
                });

                // Alternar estado: 0 (Sin Orden) -> 1 (Asc) -> 2 (Desc) -> 0
                let currentOrder = parseInt(th.dataset.order || "0", 10);
                let nextOrder = (currentOrder + 1) % 3;
                th.dataset.order = nextOrder.toString();

                if (nextOrder === 0) {
                    // Restaurar orden original
                    sortIcon.src = iconSin;
                    rows.sort((a, b) => parseInt(a.dataset.originalIndex, 10) - parseInt(b.dataset.originalIndex, 10));
                } else {
                    sortIcon.src = nextOrder === 1 ? iconAsc : iconDesc;
                    const key = header.key;

                    rows.sort((a, b) => {
                        let valA = a.dataset[key] || "";
                        let valB = b.dataset[key] || "";

                        if (key === "machine") {
                            // Orden numérico de menor a mayor / mayor a menor
                            let numA = extractFirstNumber(valA);
                            let numB = extractFirstNumber(valB);
                            if (numA !== numB) {
                                return nextOrder === 1 ? numA - numB : numB - numA;
                            }
                            return nextOrder === 1
                                ? valA.localeCompare(valB, undefined, { numeric: true, sensitivity: "base" })
                                : valB.localeCompare(valA, undefined, { numeric: true, sensitivity: "base" });
                        } else {
                            // Orden alfabético de A a Z / Z a A
                            return nextOrder === 1
                                ? valA.localeCompare(valB, "es", { sensitivity: "base" })
                                : valB.localeCompare(valA, "es", { sensitivity: "base" });
                        }
                    });
                }

                // Reordenar visualmente en el DOM
                rows.forEach((row) => tbody.appendChild(row));
            });
        });
    } else {
        let tr = document.createElement("tr");
        tr.classList.add("empty-table-row");
        let td = document.createElement("td");
        td.colSpan = 4;
        td.innerHTML = `
            <div class="empty-state">
                <span class="empty-text">No hay máquinas ocupadas actualmente</span>
            </div>
        `;
        tr.appendChild(td);
        tbody.appendChild(tr);
    }

    table.appendChild(tbody);
    return table;
}

document.addEventListener("DOMContentLoaded", () => {
    let form = document.querySelector("form");
    if (!form) return;
    let div_table = document.createElement("div");
    div_table.classList.add("machines-table-container");
    div_table.appendChild(createTable(machines, form));
    form.appendChild(div_table);
});
