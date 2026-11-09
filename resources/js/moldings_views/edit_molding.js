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

// Variables de estado global
let currentSortDirection = null; // 'asc' | 'desc' | null
let searchQuery = "";
let tableBody = null;
let formElement = null;

function normalizeStr(str) {
    return (str || "")
        .toString()
        .toLowerCase()
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .trim();
}

function getProcessedMoldings() {
    let list = Array.isArray(moldings) ? [...moldings] : [];

    // 1. Filtrado por búsqueda
    if (searchQuery.trim() !== "") {
        const queryNorm = normalizeStr(searchQuery);
        list = list.filter((m) => normalizeStr(m.nombre).includes(queryNorm));
    }

    // 2. Ordenamiento alfabético/numérico si está activo
    if (currentSortDirection) {
        list.sort((a, b) => {
            const nameA = (a.nombre || "").trim();
            const nameB = (b.nombre || "").trim();
            const comp = nameA.localeCompare(nameB, "es", { sensitivity: "base", numeric: true });
            return currentSortDirection === "asc" ? comp : -comp;
        });
    }

    return list;
}

function renderRows(moldingsToDisplay) {
    if (!tableBody) return;
    tableBody.innerHTML = "";

    if (moldingsToDisplay.length > 0) {
        moldingsToDisplay.forEach((molding) => {
            let tr = document.createElement("tr");
            tr.classList.add("molding-table-row");
            tr.dataset.moldingId = molding.id;

            // Insertar el nombre de la moldura
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

            // Insertar las acciones
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
                    if (submitBtn && formElement) {
                        formElement.removeChild(submitBtn);
                    }

                    let nextElementSibling = editButton.nextElementSibling;
                    if (nextElementSibling && nextElementSibling.tagName === "BUTTON") {
                        nextElementSibling.hidden = false;
                    }

                    document.querySelectorAll(".molding-table-row").forEach((row) => {
                        row.classList.remove("disabled-row");
                    });
                } else {
                    if (!formElement.querySelector("button[type='submit']")) {
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
                        submit.classList.add("btn", "btn-block", "text-center", "my-3");
                        submit.textContent = "Guardar";
                        submit.type = "submit";
                        formElement.appendChild(submit);

                        setEditButtonState(editButton, "cancel");

                        let nextElementSibling = editButton.nextElementSibling;
                        if (nextElementSibling && nextElementSibling.tagName === "BUTTON") {
                            nextElementSibling.hidden = true;
                        }

                        document.querySelectorAll(".molding-table-row").forEach((row) => {
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
                    text: `Se eliminará permanentemente la moldura "${molding.nombre}" del catálogo.`,
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
            tableBody.appendChild(tr);
        });
    } else {
        let tr = document.createElement("tr");
        tr.classList.add("molding-table-row");
        let td = document.createElement("td");
        td.colSpan = 2;
        td.style.padding = "28px 16px";
        td.style.color = "#64748b";
        td.style.fontWeight = "500";
        td.style.fontStyle = "italic";
        td.textContent = searchQuery.trim() !== ""
            ? "No se encontraron molduras que coincidan con la búsqueda."
            : "No hay molduras disponibles";
        tr.appendChild(td);
        tableBody.appendChild(tr);
    }
}

function refreshTable() {
    // Si había una edición activa al filtrar o reordenar, limpiar el botón submit huérfano
    let submitBtn = document.querySelector("button[type='submit']");
    if (submitBtn && formElement) {
        submitBtn.remove();
    }
    let inputHidden = document.querySelector(".molding-id");
    if (inputHidden) {
        inputHidden.remove();
    }

    const processed = getProcessedMoldings();
    renderRows(processed);

    // Actualizar badge de conteo
    const countLabel = document.getElementById("molding-count-label");
    if (countLabel) {
        const total = Array.isArray(moldings) ? moldings.length : 0;
        if (searchQuery.trim() !== "") {
            countLabel.textContent = `${processed.length} de ${total}`;
        } else {
            countLabel.textContent = `${total}`;
        }
    }

    // Actualizar indicador visual de sort en el encabezado
    const sortIndicator = document.getElementById("sort-indicator");
    if (sortIndicator) {
        sortIndicator.classList.remove("asc", "desc");
        if (currentSortDirection === "asc") {
            sortIndicator.classList.add("asc");
        } else if (currentSortDirection === "desc") {
            sortIndicator.classList.add("desc");
        }
    }
}

function createTable(form) {
    const table = document.createElement("table");
    table.classList.add("molding-table");

    const thead = document.createElement("thead");
    thead.classList.add("molding-table-header");

    tableBody = document.createElement("tbody");
    tableBody.classList.add("molding-table-body");

    let tr = document.createElement("tr");

    // Columna 1: Nombre (ordenable con clic)
    let thName = document.createElement("th");
    thName.id = "th-sort-name";
    thName.classList.add("th-sortable");
    thName.title = "Clic para ordenar alfabéticamente (A-Z / Z-A)";
    thName.innerHTML = `
        <div class="th-sort-content">
            <span class="th-text">NOMBRE</span>
            <span class="sort-indicator" id="sort-indicator">
                <span class="arrow arrow-asc">▲</span>
                <span class="arrow arrow-desc">▼</span>
            </span>
        </div>
    `;

    thName.addEventListener("click", () => {
        if (!currentSortDirection || currentSortDirection === "desc") {
            currentSortDirection = "asc";
        } else {
            currentSortDirection = "desc";
        }
        refreshTable();
    });

    tr.appendChild(thName);

    // Columna 2: Acciones
    let thActions = document.createElement("th");
    thActions.textContent = "ACCIONES";
    thActions.style.width = "320px";
    tr.appendChild(thActions);

    thead.appendChild(tr);
    table.appendChild(thead);
    table.appendChild(tableBody);

    // Renderizado inicial
    renderRows(getProcessedMoldings());

    return table;
}

document.addEventListener("DOMContentLoaded", () => {
    formElement = document.querySelector("form");

    if (formElement) {
        let div_table_container = document.createElement("div");
        div_table_container.classList.add("table-container");
        div_table_container.appendChild(createTable(formElement));
        formElement.appendChild(div_table_container);
    }

    // Inicializar eventos de la barra de búsqueda
    const searchInput = document.getElementById("molding-search-input");
    const clearBtn = document.getElementById("molding-search-clear");

    if (searchInput) {
        searchInput.addEventListener("input", (e) => {
            searchQuery = e.target.value;
            if (clearBtn) {
                clearBtn.style.display = searchQuery.trim() !== "" ? "block" : "none";
            }
            refreshTable();
        });

        // Prevenir submit accidental del form al presionar Enter en el buscador
        searchInput.addEventListener("keydown", (e) => {
            if (e.key === "Enter") {
                e.preventDefault();
            }
        });
    }

    if (clearBtn) {
        clearBtn.addEventListener("click", () => {
            if (searchInput) {
                searchInput.value = "";
                searchQuery = "";
                clearBtn.style.display = "none";
                searchInput.focus();
                refreshTable();
            }
        });
    }
});
