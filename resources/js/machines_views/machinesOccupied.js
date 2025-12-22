function createTable(machines, form) {
    const table = document.createElement("table");
    table.classList.add("machines-table");

    const thead = document.createElement("thead");
    thead.classList.add("machines-table-header");

    const tbody = document.createElement("tbody");
    tbody.classList.add("machines-table-body");

    let tr = document.createElement("tr");

    // Crear el encabezado de la tabla
    let arrayHeader = ["Proceso", "Maquina", "Acciones"];
    arrayHeader.forEach((header, index) => {
        let th = document.createElement("th");
        th.textContent = header;
        if (index == 0) {
            th.style.width = "250px";
        }
        tr.appendChild(th);
    });
    thead.appendChild(tr);
    table.appendChild(thead);

    //Crear las filas de las maquinas
    console.log(machines);
    if (machines.length > 0) {
        machines.forEach((machine) => {
            let tr = document.createElement("tr");
            tr.classList.add("machine-table-row");

            for (let i = 0; i < 2; i++) {
                //Insertar el numero y proceso de la maquina
                let td = document.createElement("td");
                let input = document.createElement("input");
                input.type = "text";
                input.maxLength = 100;
                if(i == 0){
                    input.name = "process";
                    input.value = machine.process;
                } else {
                    let inputHidden = document.createElement("input");
                    inputHidden.type = "hidden";
                    inputHidden.name = "machine";
                    inputHidden.value = machine.machine;

                    input.value = machine.machine.replace("_", " y ");
                    form.appendChild(inputHidden);
                }
                input.readOnly = true;
                td.appendChild(input);
                tr.appendChild(td);
            }

            //Insertar las acciones
            let tdActions = document.createElement("td");
            let desoccupiedButton = document.createElement("button");
            desoccupiedButton.classList.add("btn", "btn-desoccupied");
            desoccupiedButton.textContent = "Desocupar";
            desoccupiedButton.onclick = (e) => {
                e.preventDefault();
                if (confirm("¿Estás seguro de que deseas desocupar la máquina?")) {
                    if (!document.querySelector(".idMachine")) {
                        let inputIdMachine = document.createElement("input");
                        inputIdMachine.type = "hidden";
                        inputIdMachine.name = "idMachine";
                        inputIdMachine.className = "idMachine";
                        inputIdMachine.value = machine.id;
                        form.appendChild(inputIdMachine);
                    }
                    //Redirigir a la ruta que ya tiene el formulario
                    form.submit();
                }
            };
            tdActions.appendChild(desoccupiedButton);
            tr.appendChild(tdActions);
            tbody.appendChild(tr);
        });
    } else {
        let tr = document.createElement("tr");
        tr.classList.add("molding-table-row");
        let td = document.createElement("td");
        td.colSpan = 3;
        td.textContent = "No hay maquinas ocupadas";
        tr.appendChild(td);
        tbody.appendChild(tr);
    }

    table.appendChild(tbody);
    return table;
}

document.addEventListener("DOMContentLoaded", () => {
    let form = document.querySelector("form");
    let div_table = document.createElement("div");
    div_table.classList.add("machines-table-container");
    div_table.appendChild(createTable(machines, form));
    form.appendChild(div_table);
});
