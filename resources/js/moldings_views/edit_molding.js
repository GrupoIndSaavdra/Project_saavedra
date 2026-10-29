function setEditButtonState(button, state) {
    const editIcon = window.imgEdit || (window.baseUrl ? `${window.baseUrl.replace(/\/$/, '')}/images/Color_Icons/Generales/DrawsProduction.png` : "/images/Color_Icons/Generales/DrawsProduction.png");
    const cancelIcon = window.imgCancel || (window.baseUrl ? `${window.baseUrl.replace(/\/$/, '')}/images/Light_Icons/Cancelar-Light.png` : "/images/Light_Icons/Cancelar-Light.png");

    if (state === "cancel") {
        button.dataset.state = "cancel";
        button.style.background = "#9C0303";
        button.innerHTML = `<img src="${cancelIcon}" class="btn-action-icon" alt="Cancelar"><span>Cancelar</span>`;
    } else {
        button.dataset.state = "edit";
        button.style.background = "";
        button.innerHTML = `<img src="${editIcon}" class="btn-action-icon" alt="Editar"><span>Editar</span>`;
    }
}

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
            th.style.width = "320px";
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
            setEditButtonState(editButton, "edit");

            editButton.onclick = (e) => {
                e.preventDefault();

                if (editButton.dataset.state === "cancel") {
                    setEditButtonState(editButton, "edit");
                    input.value = molding.nombre;
                    input.disabled = true;
                    let submitBtn = document.querySelector("button[type='submit']");
                    if (submitBtn) {
                        form.removeChild(submitBtn);
                    }

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

                        setEditButtonState(editButton, "cancel");

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
            const deleteIcon = window.imgDelete || (window.baseUrl ? `${window.baseUrl.replace(/\/$/, '')}/images/Color_Icons/Generales/delete.png` : "/images/Color_Icons/Generales/delete.png");
            deleteButton.innerHTML = `<img src="${deleteIcon}" class="btn-action-icon" alt="Eliminar"><span>Eliminar</span>`;

            deleteButton.onclick = async (e) => {
                e.preventDefault();
                const res = await (window.gisConfirm ? window.gisConfirm({
                    title: "¿Eliminar moldura?",
                    text: "Se eliminará permanentemente el registro de esta moldura del catálogo.",
                    confirmButtonText: "Eliminar",
                    confirmButtonColor: "#b30404"
                }) : Promise.resolve({ isConfirmed: true }));

                if (res.isConfirmed) {
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
