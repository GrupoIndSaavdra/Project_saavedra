//********FUNCTIONS TO APPLY IN THE DASHBOARD***********

var datos = window.datos || {}; // Datos de las ordenes de trabajo

let habilitar,
    box,
    selects = {},
    boton,
    isInitializing = true;

const des_habilitarCampo = (box, campo, habilitar) => {
    let elemento, newElement;

    if (habilitar != null) {
        elemento = document.getElementById(`${campo}-input`) || document.getElementById(`${campo}-select`);
        newElement = insertarSelect(campo, habilitar);
        selects[campo] = newElement;
        box.classList.remove("box--disabled");
        box.classList.add("box--enabled");
    } else {
        elemento = document.getElementById(`${campo}-select`) || document.getElementById(`${campo}-input`);
        newElement = insertarInput(campo);
        selects[campo] = null;
        box.classList.remove("box--enabled");
        box.classList.add("box--disabled");
    }

    if (elemento != null) {
        elemento.remove();
    }
    box.appendChild(newElement);
    return newElement;
};

const insertarSelect = (campo, arrayOpciones) => {
    let select = document.createElement("select");
    select.id = `${campo}-select`;
    select.className = "filtros";
    select.name = campo;

    let firstOption = "";
    switch (campo) {
        case "ot":
            firstOption = "Selecciona una OT";
            break;
        case "clases":
            firstOption = "Selecciona una clase";
            break;
        case "procesos":
            firstOption = "Selecciona un proceso";
            break;
        case "operadores":
            firstOption = "Selecciona un Operador";
            break;
    }

    select.appendChild(insertarOpcion(0, firstOption));

    let count = 0;
    if (Array.isArray(arrayOpciones)) {
        arrayOpciones.forEach((opcion) => {
            select.appendChild(insertarOpcion(opcion, opcion));
            count++;
        });
    } else if (typeof arrayOpciones === "object" && arrayOpciones !== null) {
        for (let opcion in arrayOpciones) {
            let value = opcion;
            let text = opcion;

            switch (campo) {
                case "ot":
                    text = arrayOpciones[opcion]?.nombre || opcion;
                    break;
                case "clases":
                    text = opcion;
                    break;
                case "procesos":
                    text = opcion;
                    break;
                case "operadores":
                    value = arrayOpciones[opcion]?.matricula || opcion;
                    text = arrayOpciones[opcion]?.nombre || opcion;
                    break;
            }

            select.appendChild(insertarOpcion(value, text));
            count++;
        }
    }

    if (count === 0) {
        select.innerHTML = "";
        select.appendChild(insertarOpcion(0, "Sin opciones activas"));
    }

    return select;
};

const insertarOpcion = (value, text) => {
    let option = document.createElement("option");
    option.value = value;
    option.text = text;
    return option;
};

const insertarInput = (campo) => {
    let inputDisabled = document.createElement("input");
    inputDisabled.id = `${campo}-input`;
    inputDisabled.className = "filtros";
    inputDisabled.type = "text";
    inputDisabled.disabled = true;

    switch (campo) {
        case "clases":
            inputDisabled.placeholder = "Esperando OT...";
            break;
        case "procesos":
            inputDisabled.placeholder = "Esperando Clase...";
            break;
        case "operadores":
            inputDisabled.placeholder = "Esperando Proceso...";
            break;
        case "pedido":
            inputDisabled.placeholder = "—";
            break;
        default:
            inputDisabled.placeholder = "Esperando selección previa...";
    }

    return inputDisabled;
};

/**
 * Insertar un valor y texto en un elemento
 * @param {HTMLElement} box Contenedor del elemento
 * @param {string|number|null} valor Valor que tendrá el elemento
 * @param {string} campo Nombre del campo
 */
const crearInputConValor = (box, valor, campo) => {
    let input = document.getElementById(`${campo}-input`) || document.getElementById(`${campo}-select`);
    if (input != null) {
        input.remove();
    }

    let newInput = document.createElement("input");
    newInput.id = `${campo}-input`;
    newInput.className = "filtros";
    newInput.disabled = true;

    if (valor != null && valor !== "" && valor != 0) {
        newInput.value = valor;
        box.classList.remove("box--disabled");
        box.classList.add("box--enabled");
    } else {
        newInput.value = "";
        newInput.placeholder = "—";
        box.classList.remove("box--enabled");
        box.classList.add("box--disabled");
    }

    box.appendChild(newInput);
};

//********FUNCTIONS TO APPLY IN THE TABLE***********
const crearTabla = (datos) => {
    let table = document.createElement("table");
    table.className = "data-table";

    for (let i = 0; i < 2; i++) {
        let tr_head = document.createElement("tr");

        if (i === 0) {
            let encabezado = [
                "Fecha",
                "Piezas buenas",
                "Piezas malas",
                "Meta",
                "Productividad",
            ];
            let thead = document.createElement("thead");
            for (let j = 0; j < 5; j++) {
                let th = document.createElement("th");
                th.innerHTML = encabezado[j];
                tr_head.appendChild(th);
            }
            thead.appendChild(tr_head);
            table.appendChild(thead);
        } else {
            let tbody = document.createElement("tbody");
            for (let operador in datos) {
                for (let fecha in datos[operador]) {
                    let tr_body = document.createElement("tr");

                    let td_fecha = document.createElement("td");
                    td_fecha.innerHTML = fecha;
                    tr_body.appendChild(td_fecha);

                    for (let piezasinfo in datos[operador][fecha]) {
                        let td = document.createElement("td");
                        if (piezasinfo != "Productividad") {
                            td.innerHTML = datos[operador][fecha][piezasinfo];
                        } else {
                            let realPct = datos[operador][fecha][piezasinfo];
                            let visualPct = Math.min(Math.max(realPct, 0), 100);
                            let barColor;
                            if (realPct >= 150) {
                                barColor = "#9b59b6"; // Platino/Morado brillante (Excelencia)
                            } else if (realPct >= 100) {
                                barColor = "#f1c40f"; // Dorado (Esfuerzo destacado)
                            } else if (realPct >= 75) {
                                barColor = "#0a8504"; // Verde GIS (Aceptable)
                            } else if (realPct >= 40) {
                                barColor = "#e67e22"; // Naranja (Medio)
                            } else {
                                barColor = "#9c0303"; // Rojo GIS (Bajo)
                            }
                            let wrapper = document.createElement("div");
                            wrapper.className = "productividad-wrapper";

                            let spanText = document.createElement("span");
                            spanText.className = "productividad-text";
                            spanText.innerText = realPct + "%";

                            let container_progress = document.createElement("div");
                            container_progress.className = "container-progress";

                            let progress_bar = document.createElement("div");
                            progress_bar.className = "progress-bar";
                            progress_bar.style.width = visualPct + "%";
                            progress_bar.style.backgroundColor = barColor;

                            container_progress.appendChild(progress_bar);
                            wrapper.appendChild(spanText);
                            wrapper.appendChild(container_progress);
                            td.appendChild(wrapper);
                        }
                        tr_body.appendChild(td);
                    }
                    tbody.appendChild(tr_body);
                }
            }
            table.appendChild(tbody);
        }
    }
    return table;
};

// Crear select de OTs y agregarlo al box .ot
box = document.querySelector(".ot");
if (box) {
    let existingOt = document.getElementById("ot-select");
    if (existingOt) existingOt.remove();
    selects["ot"] = insertarSelect("ot", datos);
    box.appendChild(selects["ot"]);
}
boton = document.getElementById("button");

// Escuchar evento de envío para realizar consulta AJAX
let form = document.querySelector(".search-form");
if (form) {
    form.addEventListener("submit", (e) => {
        e.preventDefault();

        let otSelect = document.getElementById("ot-select");
        let clasesSelect = document.getElementById("clases-select");
        let procesosSelect = document.getElementById("procesos-select");
        let operadorSelect = document.getElementById("operadores-select");

        let otValue = otSelect ? otSelect.value : "0";
        let claseValue = clasesSelect ? clasesSelect.value : "0";
        let procesoValue = procesosSelect ? procesosSelect.value : "0";
        let operadorValue = operadorSelect ? operadorSelect.value : "0";

        if (otValue == "0" || claseValue == "0" || procesoValue == "0" || operadorValue == "0") {
            return;
        }

        let dashboard2 = document.querySelector(".dashboard2");
        let div_table = document.querySelector(".div-table");

        // Mostrar indicador de carga en el banner
        let loadingStatus = document.getElementById("loading-status");
        let statusText = document.getElementById("status-text");
        let statusSpinner = document.querySelector(".status-spinner");

        if (loadingStatus) {
            loadingStatus.classList.remove("status-success", "status-error");
        }
        if (statusText) {
            statusText.innerText = "Realizando consulta de rendimiento en tiempo real, por favor espere...";
        }
        if (statusSpinner) {
            statusSpinner.style.display = "block";
        }

        if (dashboard2) {
            dashboard2.style.display = "grid";
        }

        if (div_table) {
            div_table.innerHTML = `
                <div class="loading-spinner-container">
                    <div class="gis-spinner"></div>
                    <p>Cargando información de productividad...</p>
                </div>
            `;
        }

        // Remover leyenda anterior si existe
        let oldLegend = document.querySelector(".leyenda-productividad");
        if (oldLegend) oldLegend.remove();

        const formData = new FormData(form);

        fetch(window.location.pathname + "?ajax=1", {
            method: "POST",
            body: formData,
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || ""
            }
        })
        .then(response => {
            const contentType = response.headers.get("content-type");
            if (!contentType || !contentType.includes("application/json")) {
                return response.text().then(text => {
                    console.error("Respuesta no es JSON:", text);
                    throw new Error("Sesión expirada o error en el servidor. Recarga la página por favor.");
                });
            }
            if (!response.ok) {
                throw new Error("Error en el servidor");
            }
            return response.json();
        })
        .then(data => {
            if (statusSpinner) {
                statusSpinner.style.display = "none";
            }

            if (data.success) {
                if (loadingStatus) {
                    loadingStatus.classList.add("status-success");
                }
                if (statusText) {
                    statusText.innerHTML = `Consulta completada. Mostrando rendimiento para la OT <strong>${data.filtros.ot}</strong>, clase <strong>${data.filtros.clase}</strong>, proceso <strong>${data.filtros.proceso}</strong>.`;
                }

                // Actualizar etiquetas de detalles
                document.getElementById("detail-ot").innerText = data.filtros.ot + " - " + data.filtros.moldura;
                document.getElementById("detail-clase").innerText = data.filtros.clase + " (" + data.filtros.pedido + " pzas)";
                document.getElementById("detail-proceso").innerText = data.filtros.proceso;
                document.getElementById("detail-operador").innerText = data.filtros.operador;

                // Limpiar spinner y renderizar tabla y leyenda nuevas
                div_table.innerHTML = "";
                div_table.appendChild(crearTabla(data.operadores));
                dashboard2.appendChild(crearLeyendaProductividad());
            } else {
                if (loadingStatus) {
                    loadingStatus.classList.add("status-error");
                }
                if (statusText) {
                    statusText.innerText = `Error: ${data.error}`;
                }
                div_table.innerHTML = `<div class="alert-error-container" style="margin: 20px auto; max-width: 90%;">${data.error}</div>`;
            }
        })
        .catch(err => {
            if (loadingStatus) {
                loadingStatus.classList.add("status-error");
            }
            if (statusSpinner) {
                statusSpinner.style.display = "none";
            }
            if (statusText) {
                statusText.innerText = "Error al conectar con el servidor. Por favor, inténtelo de nuevo.";
            }
            div_table.innerHTML = `<div class="alert-error-container" style="margin: 20px auto; max-width: 90%;">Error al conectar con el servidor. Por favor, inténtelo de nuevo.</div>`;
            console.error(err);
        });
    });
}

// Cascada de filtros: Orden de Trabajo -> Clase -> Proceso -> Operador (y Pedido con Clase)
document.querySelector(".dashboard").addEventListener("change", (e) => {
    let target = e.target;

    // 1. Cambio en ORDEN DE TRABAJO
    if (target.id === "ot-select") {
        let otValue = target.value;
        let boxClases = document.querySelector(".clases");
        let boxProcesos = document.querySelector(".procesos");
        let boxOperadores = document.querySelector(".operadores");
        let boxPedido = document.querySelector(".pedido");

        if (otValue != 0 && datos[otValue]) {
            // Habilitar sólo las clases activas de esa OT
            let clasesActivas = datos[otValue]["clases"] || {};
            des_habilitarCampo(boxClases, "clases", clasesActivas);
        } else {
            // Deshabilitar clases si no hay OT seleccionada
            des_habilitarCampo(boxClases, "clases", null);
        }

        // Resetear procesos, operadores y pedido
        des_habilitarCampo(boxProcesos, "procesos", null);
        des_habilitarCampo(boxOperadores, "operadores", null);
        crearInputConValor(boxPedido, null, "pedido");
    }

    // 2. Cambio en CLASE
    else if (target.id === "clases-select") {
        let claseValue = target.value;
        let otSelect = document.getElementById("ot-select");
        let otValue = otSelect ? otSelect.value : 0;

        let boxPedido = document.querySelector(".pedido");
        let boxProcesos = document.querySelector(".procesos");
        let boxOperadores = document.querySelector(".operadores");

        if (claseValue != 0 && otValue != 0 && datos[otValue]?.["clases"]?.[claseValue]) {
            let claseData = datos[otValue]["clases"][claseValue];

            // Mostrar pedido activo de la clase seleccionada
            crearInputConValor(boxPedido, claseData.pedido, "pedido");

            // Habilitar sólo los procesos activos de esa OT y Clase
            let procesosActivos = claseData.procesos || {};
            des_habilitarCampo(boxProcesos, "procesos", procesosActivos);
        } else {
            crearInputConValor(boxPedido, null, "pedido");
            des_habilitarCampo(boxProcesos, "procesos", null);
        }

        // Resetear operadores
        des_habilitarCampo(boxOperadores, "operadores", null);
    }

    // 3. Cambio en PROCESO
    else if (target.id === "procesos-select") {
        let procesoValue = target.value;
        let clasesSelect = document.getElementById("clases-select");
        let otSelect = document.getElementById("ot-select");

        let claseValue = clasesSelect ? clasesSelect.value : 0;
        let otValue = otSelect ? otSelect.value : 0;

        let boxOperadores = document.querySelector(".operadores");

        if (procesoValue != 0 && claseValue != 0 && otValue != 0) {
            // Habilitar sólo los operadores activos que trabajaron ese proceso para esa clase en esa OT
            let operadoresActivos = datos[otValue]?.["clases"]?.[claseValue]?.["procesos"]?.[procesoValue]?.operadores || {};
            des_habilitarCampo(boxOperadores, "operadores", operadoresActivos);
        } else {
            des_habilitarCampo(boxOperadores, "operadores", null);
        }
    }

    // 4. Cambio en OPERADOR
    else if (target.id === "operadores-select") {
        // Nada más que validar
    }

    checkFormValidity();
});

const crearLeyendaProductividad = () => {
    let leyenda = document.createElement("div");
    leyenda.className = "leyenda-productividad";

    let titulo = document.createElement("p");
    titulo.className = "leyenda-titulo";
    titulo.innerHTML = "<strong>Leyenda de productividad:</strong>";
    leyenda.appendChild(titulo);

    const rangos = [
        { colorClass: "leyenda-color--bajo", rango: "0% - 39%", label: "Bajo" },
        { colorClass: "leyenda-color--medio", rango: "40% - 74%", label: "Medio" },
        { colorClass: "leyenda-color--aceptable", rango: "75% - 100%", label: "Aceptable / Meta" },
        { colorClass: "leyenda-color--destacado", rango: "100% - 149%", label: "Esfuerzo destacado" },
        { colorClass: "leyenda-color--excelencia", rango: "+ 150%", label: "Excelencia" },
    ];

    let tabla = document.createElement("table");
    tabla.className = "leyenda-tabla";

    rangos.forEach(item => {
        let fila = document.createElement("tr");

        let tdColor = document.createElement("td");
        tdColor.className = "leyenda-color " + item.colorClass;
        fila.appendChild(tdColor);

        let tdRango = document.createElement("td");
        tdRango.className = "leyenda-rango";
        tdRango.innerHTML = `<strong>${item.rango}</strong>`;
        fila.appendChild(tdRango);

        let tdLabel = document.createElement("td");
        tdLabel.className = "leyenda-label";
        tdLabel.innerText = item.label;
        fila.appendChild(tdLabel);

        tabla.appendChild(fila);
    });

    leyenda.appendChild(tabla);
    return leyenda;
};

// Restablecer filtros si existen en la carga de la página
if (window.filtros !== undefined) {
    let dashboard2 = document.querySelector(".dashboard2");
    let div_table = document.querySelector(".div-table");

    if (window.datosOperadores) {
        div_table.appendChild(crearTabla(datosOperadores));
        dashboard2.appendChild(crearLeyendaProductividad());
    }

    let otSelect = document.getElementById("ot-select");
    if (otSelect && window.filtros.ot) {
        otSelect.value = window.filtros.ot;
        let otValue = window.filtros.ot;

        if (datos[otValue]) {
            // 1. Restaurar clases
            let clasesActivas = datos[otValue]["clases"] || {};
            let boxClases = document.querySelector(".clases");
            let clasesSelect = des_habilitarCampo(boxClases, "clases", clasesActivas);
            if (window.filtros.clase) {
                clasesSelect.value = window.filtros.clase;
            }

            // 2. Restaurar pedido
            let claseValue = window.filtros.clase;
            let boxPedido = document.querySelector(".pedido");
            let pedido = datos[otValue]?.["clases"]?.[claseValue]?.pedido || window.filtros.pedido;
            crearInputConValor(boxPedido, pedido, "pedido");

            // 3. Restaurar procesos
            let boxProcesos = document.querySelector(".procesos");
            let procesosActivos = datos[otValue]?.["clases"]?.[claseValue]?.procesos || {};
            let procesosSelect = des_habilitarCampo(boxProcesos, "procesos", procesosActivos);
            if (window.filtros.proceso) {
                procesosSelect.value = window.filtros.proceso;
            }

            // 4. Restaurar operadores
            let procesoValue = window.filtros.proceso;
            let boxOperadores = document.querySelector(".operadores");
            let operadoresActivos = datos[otValue]?.["clases"]?.[claseValue]?.["procesos"]?.[procesoValue]?.operadores || {};
            let operadoresSelect = des_habilitarCampo(boxOperadores, "operadores", operadoresActivos);

            if (window.filtros.operador_matricula) {
                operadoresSelect.value = window.filtros.operador_matricula;
            } else if (window.filtros.operador) {
                for (let opMat in operadoresActivos) {
                    if (operadoresActivos[opMat].nombre === window.filtros.operador) {
                        operadoresSelect.value = opMat;
                        break;
                    }
                }
            }
        }

        checkFormValidity();
    }
}
isInitializing = false;

function checkFormValidity() {
    let otSelect = document.getElementById("ot-select");
    let clasesSelect = document.getElementById("clases-select");
    let procesosSelect = document.getElementById("procesos-select");
    let operadorSelect = document.getElementById("operadores-select");

    let otValue = otSelect ? otSelect.value : "0";
    let claseValue = clasesSelect ? clasesSelect.value : "0";
    let procesoValue = procesosSelect ? procesosSelect.value : "0";
    let operadorValue = operadorSelect ? operadorSelect.value : "0";

    let isValid = otValue != "0" && claseValue != "0" && procesoValue != "0" && operadorValue != "0";

    if (boton) {
        if (isValid) {
            boton.removeAttribute("disabled");
        } else {
            boton.setAttribute("disabled", "true");
        }
    }

    // Actualizar el estado del banner de información
    let statusText = document.getElementById("status-text");
    let statusSpinner = document.querySelector(".status-spinner");
    let loadingStatus = document.getElementById("loading-status");

    if (loadingStatus && !isValid) {
        // Contar parámetros faltantes de los 4 filtros seleccionables
        let missing = 0;
        if (!otSelect || otValue == "0") missing++;
        if (!clasesSelect || claseValue == "0") missing++;
        if (!procesosSelect || procesoValue == "0") missing++;
        if (!operadorSelect || operadorValue == "0") missing++;

        missing = Math.min(Math.max(missing, 1), 4);

        if (statusText) {
            statusText.innerText = `Falta seleccionar ${missing} parámetro${missing > 1 ? 's' : ''} para realizar la consulta automática.`;
        }
        if (statusSpinner) {
            statusSpinner.style.display = "none";
        }
        loadingStatus.classList.remove("status-success", "status-error");
    }

    // Auto-submit si el formulario es válido y no estamos en fase de carga inicial
    if (isValid && !isInitializing) {
        let form = document.querySelector(".search-form");
        if (form) {
            if (typeof form.requestSubmit === "function") {
                form.requestSubmit();
            } else {
                form.dispatchEvent(new Event("submit", { cancelable: true }));
            }
        }
    }
}
