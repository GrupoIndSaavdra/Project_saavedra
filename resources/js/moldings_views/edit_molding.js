function createTable(moldings, form) {
    const table = document.createElement("table");
    table.classList.add("molding-table");

    const thead = document.createElement("thead");
    thead.classList.add("molding-table-header");

    const tbody = document.createElement("tbody");
    tbody.classList.add("molding-table-body");

    let tr = document.createElement("tr");

    // Crear el encabezado de la tabla
    let arrayHeader = ["Nombre", "Acciones"];
    arrayHeader.forEach((header, index) => {
        let th = document.createElement("th");
        th.textContent = header;
        if (index == 1) {
            th.style.width = "250px";
        }
        tr.appendChild(th);
    });
    thead.appendChild(tr);
    table.appendChild(thead);

    //Crear las filas de las molduras
    if (moldings.length > 0) {
        moldings.forEach((molding) => {
            let tr = document.createElement("tr");
            tr.classList.add("molding-table-row");

            //Insertar el nombre de la moldura
            let tdName = document.createElement("td");
            tdName.classList.add("molding-name");
            let input = document.createElement("input");
            input.type = "text";
            input.maxLength = 100;
            input.name = "moldingName";
            input.value = molding.nombre;
            input.disabled = true;
            tdName.appendChild(input);
            tr.appendChild(tdName);

            //Insertar las acciones
            let tdActions = document.createElement("td");
            let editButton = document.createElement("button");
            editButton.classList.add("btn", "btn-edit");
            editButton.textContent = "Editar";
            editButton.onclick = (e) => {
                e.preventDefault();

                if (editButton.textContent === "Cancelar") {
                    editButton.style.background = "#033966";
                    input.value = molding.nombre;
                    input.disabled = true;
                    form.removeChild(
                        document.querySelector("button[type='submit']")
                    );
                    editButton.textContent = "Editar";

                    let nextElementSibling = editButton.nextElementSibling;
                    if (
                        nextElementSibling &&
                        nextElementSibling.tagName === "BUTTON"
                    ) {
                        nextElementSibling.hidden = false;
                    }

                    document
                        .querySelectorAll(".molding-table-row")
                        .forEach((row) => {
                            row.classList.remove("disabled-row");
                        });
                } else {
                    if (!form.querySelector("button[type='submit']")) {
                        let input = tdName.querySelector("input");
                        if (input.disabled) {
                            input.disabled = false;
                            input.focus();
                        }
                        let inputHidden = document.querySelector(".molding-id");
                        if (inputHidden) {
                            inputHidden.remove();
                        }
                        inputHidden = document.createElement("input");
                        inputHidden.type = "hidden";
                        inputHidden.name = "moldingId";
                        inputHidden.classList.add("molding-id");
                        inputHidden.value = molding.id;
                        tdName.appendChild(inputHidden);

                        let submit = document.createElement("button");
                        submit.classList.add(
                            "btn",
                            "btn-block",
                            "text-center",
                            "my-3"
                        );
                        submit.textContent = "Guardar";
                        submit.type = "submit";
                        form.appendChild(submit);

                        editButton.style.background = "#9C0303";
                        editButton.textContent = "Cancelar";

                        let nextElementSibling = editButton.nextElementSibling;
                        if (
                            nextElementSibling &&
                            nextElementSibling.tagName === "BUTTON"
                        ) {
                            nextElementSibling.hidden = true;
                        }

                        document
                            .querySelectorAll(".molding-table-row")
                            .forEach((row) => {
                                if (row !== tr) {
                                    row.classList.add("disabled-row");
                                }
                            });
                    }
                }
            };
            tdActions.appendChild(editButton);

            let deleteButton = document.createElement("button");
            deleteButton.classList.add("btn", "btn-delete");
            deleteButton.textContent = "Eliminar";

            deleteButton.onclick = (e) => {
                e.preventDefault();
                if (
                    confirm(
                        "¿Estás seguro de que deseas eliminar esta moldura?"
                    )
                ) {
                    let moldingId = molding.id;
                    window.location.href = window.baseUrl + `/deleteMolding/${moldingId}`;
                }
            };
            tdActions.appendChild(deleteButton);
            tr.appendChild(tdActions);
            tbody.appendChild(tr);
        });
    } else {
        let tr = document.createElement("tr");
        tr.classList.add("molding-table-row");
        let td = document.createElement("td");
        td.colSpan = 2;
        td.textContent = "No hay molduras disponibles";
        tr.appendChild(td);
        tbody.appendChild(tr);
    }

    table.appendChild(tbody);
    return table;
}

document.addEventListener("DOMContentLoaded", () => {
    let form = document.querySelector("form");
    let div_table_container = document.createElement("div");
    div_table_container.classList.add("table-container");
    div_table_container.appendChild(createTable(moldings, form));
    form.appendChild(div_table_container);
});
