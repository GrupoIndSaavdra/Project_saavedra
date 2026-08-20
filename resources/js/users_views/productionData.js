//********FUNCTIONS TO APPLY IN THE DASHBOARD***********

const aplicarAccionesToEvents = (habilitar, campos) => {
    if (habilitar != null) {
        let box = document.querySelector(`.${campos[0]}`);
        let elemento = des_habilitarCampo(box, campos[0], habilitar);
        box.appendChild(elemento);
        habilitar = null;
        campos.shift();
        aplicarAccionesToEvents(habilitar, campos);
    } else {
        campos.forEach((campo) => {
            let box = document.querySelector(`.${campo}`);
            switch (campo) {
                case "pedido":
                    let pedido =
                        habilitar != null
                            ? datos[selects["ot"].value]["operadores"][
                            selects["operadores"].value
                            ]["clases"][selects["clases"].value]["pedido"]
                            : null;
                    crearInputConValor(box, pedido, "pedido");
                    break;
                case "boton":
                    boton = document.getElementById("button");
                    if (boton && !boton.classList.contains("pro-display-none")) {
                        boton.classList.add("pro-display-none");
                    }
                    break;
                default:
                    let elemento = des_habilitarCampo(box, campo, habilitar);
                    box.appendChild(elemento);
                    break;
            }
        });
    }

    //Verificar si esta funcion si sirve
};
const des_habilitarCampo = (box, campo, habilitar) => {
    //Declaracion de variables
    let elemento, newElement;

    if (habilitar != null) {
        elemento = document.getElementById(`${campo}-input`);
        if (elemento === null) {
            elemento = document.getElementById(`${campo}-select`);
        }
        newElement = insertarSelect(campo, habilitar);
        selects[campo] = newElement;
        box.classList.remove("box--disabled");
        box.classList.add("box--enabled");
    } else {
        elemento = document.getElementById(`${campo}-select`);
        if (elemento === null) {
            elemento = document.getElementById(`${campo}-input`);
        }
        newElement = insertarInput(campo);
        box.classList.remove("box--enabled");
        box.classList.add("box--disabled");
    }
    if (elemento != null) {
        elemento.remove(); //Eliminar elemento si existe
    }
    return newElement;
};

const insertarSelect = (campo, arrayOpciones) => {
    //Creacion del select
    let select = document.createElement("select");
    select.id = `${campo}-select`;
    select.className = "filtros";
    select.name = campo;

    //Insertar las opciones en el select
    let firstOption, text, value;
    let band = false;
    for (let opcion in arrayOpciones) {
        switch (campo) {
            case "ot":
                firstOption = "Selecciona una OT";
                value = opcion;
                text = arrayOpciones[opcion]["nombre"] || opcion;
                break;
            case "operadores":
                firstOption = "Selecciona un Operador";
                value = opcion;
                text = arrayOpciones[opcion]["nombre"];
                break;
            case "clases":
                firstOption = "Selecciona una clase";
                value = opcion;
                text = opcion;
                break;
            case "procesos":
                firstOption = "Selecciona un proceso";
                value = arrayOpciones[opcion];
                text = arrayOpciones[opcion];
                break;
        }
        if (band == false) {
            select.appendChild(insertarOpcion(0, firstOption)); //Insertar la primera opcion del select
            band = true;
        }
        select.appendChild(insertarOpcion(value, text));
    }
    if (!band) {
        select.appendChild(insertarOpcion(0, "Sin opciones disponibles")); //Insertar la primera opcion del select
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
    //Insertar input deshabilitado
    let inputDisabled = document.createElement("input");
    inputDisabled.id = `${campo}-input`;
    inputDisabled.className = "filtros";
    inputDisabled.type = "text";
    inputDisabled.disabled = true;

    return inputDisabled;
};

/**
 * Insertar un valor y texto en un elemento
 * @param {input} elemento Elemento en el que se insertará el valor
 * @param {string} valor Valor que tendrá el elemento
 */
const crearInputConValor = (box, valor, campo) => {
    //Si ya existe el input eliminarlo
    let input = document.getElementById(`${campo}-input`);
    if (input != null) {
        input.remove();
    }

    //Insertar input solamente si el valor es diferente a 0
    if (valor != 0) {
        //Creación del nuevo input
        input = document.createElement("input");
        input.id = `${campo}-input`;
        input.className = "filtros";
        input.value = valor;
        input.disabled = true;
        box.appendChild(input);
    }
};

//********FUNCTIONS TO APPLY IN THE TABLE***********
const crearTabla = (datos) => {
    let table = document.createElement("table");
    table.className = "data-table";

    for (let i = 0; i < 2; i++) {
        let tr_head = document.createElement("tr");

        //Crear el encabezado de la tabla
        if (i == 0) {
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
            //Insertar informacion del operador
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

var datos = window.datos; //Datos de las ordenes de trabajo

let habilitar,
    box,
    selects = [],
    boton,
    isInitializing = true;
//Crear select de OTs y agregarlo al div
box = document.querySelector(".ot");
selects["ot"] = insertarSelect("ot", datos);
box.appendChild(selects["ot"]);
boton = document.getElementById("button");

// Escuchar evento de envío para realizar consulta AJAX
let form = document.querySelector(".search-form");
if (form) {
    form.addEventListener("submit", (e) => {
        e.preventDefault();

        let otSelect = document.getElementById("ot-select");
        let operadorSelect = document.getElementById("operadores-select");
        let clasesSelect = document.getElementById("clases-select");
        let procesosSelect = document.getElementById("procesos-select");

        let otValue = otSelect ? otSelect.value : "0";
        let operadorValue = operadorSelect ? operadorSelect.value : "0";
        let claseValue = clasesSelect ? clasesSelect.value : "0";
        let procesoValue = procesosSelect ? procesosSelect.value : "0";

        if (otValue == "0" || operadorValue == "0" || claseValue == "0" || procesoValue == "0") {
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
                    statusText.innerHTML = `Consulta completada. Mostrando rendimiento para la OT <strong>${data.filtros.ot}</strong>, proceso <strong>${data.filtros.proceso}</strong>.`;
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

// Aplicar acciones cuando cambie cualquier selector en el dashboard usando delegación de eventos
document.querySelector(".dashboard").addEventListener("change", (e) => {
    let target = e.target;
    if (target.id === "ot-select") {
        let otValue = target.value;
        if (otValue != 0) {
            // Habilitar el campo de operadores si hay OT seleccionada
            let habilitarOperadores = datos[otValue]["operadores"];
            let boxOperadores = document.querySelector(".operadores");
            let operadoresSelect = des_habilitarCampo(boxOperadores, "operadores", habilitarOperadores);
            boxOperadores.appendChild(operadoresSelect);
            selects["operadores"] = operadoresSelect;

            // Poblar el select de CLASES con TODAS las clases de la OT (no solo las del operador)
            if (datos[otValue]["clases_ot"]) {
                let boxClases = document.querySelector(".clases");
                let prevClasesSelect = document.getElementById("clases-select");
                let prevClasesInput  = document.getElementById("clases-input");
                if (prevClasesSelect) prevClasesSelect.remove();
                if (prevClasesInput)  prevClasesInput.remove();

                let clasesSelect = insertarSelect("clases", datos[otValue]["clases_ot"]);
                selects["clases"] = clasesSelect;
                boxClases.classList.remove("box--disabled");
                boxClases.classList.add("box--enabled");
                boxClases.appendChild(clasesSelect);
            }


        } else {
            // Deshabilitar operadores y clases si no hay OT seleccionada
            let boxOperadores = document.querySelector(".operadores");
            let inputOperadores = des_habilitarCampo(boxOperadores, "operadores", null);
            boxOperadores.appendChild(inputOperadores);

            let boxClases = document.querySelector(".clases");
            let inputClases = des_habilitarCampo(boxClases, "clases", null);
            boxClases.appendChild(inputClases);


        }

        // Limpiar procesos, pedido y botón
        let boxProcesos = document.querySelector(".procesos");
        let inputProcesos = des_habilitarCampo(boxProcesos, "procesos", null);
        boxProcesos.appendChild(inputProcesos);

        let boxPedido = document.querySelector(".pedido");
        crearInputConValor(boxPedido, null, "pedido");
    }

    else if (target.id === "operadores-select") {
        updateProcesos();
    }

    else if (target.id === "clases-select") {
        let claseValue = target.value;
        let otSelect = document.getElementById("ot-select");
        let otValue = otSelect ? otSelect.value : 0;

        // Mostrar el pedido de la clase seleccionada
        let boxPedido = document.querySelector(".pedido");
        let pedido = (claseValue != 0 && otValue != 0 && datos[otValue]["clases_ot"] && datos[otValue]["clases_ot"][claseValue])
            ? datos[otValue]["clases_ot"][claseValue]["pedido"]
            : null;
        crearInputConValor(boxPedido, pedido, "pedido");

        updateProcesos();
    }

    checkFormValidity();
});

function updateProcesos() {
    let otSelect = document.getElementById("ot-select");
    let operadorSelect = document.getElementById("operadores-select");
    let clasesSelect = document.getElementById("clases-select");

    let otValue = otSelect ? otSelect.value : 0;
    let operadorValue = operadorSelect ? operadorSelect.value : 0;
    let claseValue = clasesSelect ? clasesSelect.value : 0;

    let procesosClase = null;
    if (otValue != 0 && operadorValue != 0 && claseValue != 0) {
        let operadorData = datos[otValue]["operadores"][operadorValue];
        if (operadorData && operadorData["clases"] && operadorData["clases"][claseValue]) {
            procesosClase = operadorData["clases"][claseValue]["procesos"];
        }
    }

    let boxProcesos = document.querySelector(".procesos");
    if (procesosClase && procesosClase.length > 0) {
        let selectProcesos = des_habilitarCampo(boxProcesos, "procesos", procesosClase);
        boxProcesos.appendChild(selectProcesos);
        selects["procesos"] = selectProcesos;
    } else {
        let inputProcesos = des_habilitarCampo(boxProcesos, "procesos", null);
        boxProcesos.appendChild(inputProcesos);
    }

    checkFormValidity();
}

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

if (window.filtros !== undefined) {
    let dashboard2 = document.querySelector(".dashboard2");
    let div_table = document.querySelector(".div-table");

    div_table.appendChild(crearTabla(datosOperadores));
    dashboard2.appendChild(crearLeyendaProductividad());

    // Restablecer filtros seleccionados previamente tras el reload del submit
    let otSelect = document.getElementById("ot-select");
    if (otSelect && window.filtros.ot) {
        otSelect.value = window.filtros.ot;
        
        // Habilitar operadores
        let otValue = window.filtros.ot;
        let habilitarOperadores = datos[otValue]["operadores"];
        let boxOperadores = document.querySelector(".operadores");
        let operadoresSelect = des_habilitarCampo(boxOperadores, "operadores", habilitarOperadores);
        boxOperadores.appendChild(operadoresSelect);
        selects["operadores"] = operadoresSelect;
        
        // Seleccionar operador
        if (window.filtros.operador_matricula) {
            operadoresSelect.value = window.filtros.operador_matricula;
        } else {
            for (let opVal in habilitarOperadores) {
                if (habilitarOperadores[opVal].nombre === window.filtros.operador) {
                    operadoresSelect.value = opVal;
                    break;
                }
            }
        }

        // Habilitar clases
        if (datos[otValue]["clases_ot"]) {
            let boxClases = document.querySelector(".clases");
            let prevClasesSelect = document.getElementById("clases-select");
            let prevClasesInput  = document.getElementById("clases-input");
            if (prevClasesSelect) prevClasesSelect.remove();
            if (prevClasesInput)  prevClasesInput.remove();

            let clasesSelect = insertarSelect("clases", datos[otValue]["clases_ot"]);
            selects["clases"] = clasesSelect;
            boxClases.classList.remove("box--disabled");
            boxClases.classList.add("box--enabled");
            boxClases.appendChild(clasesSelect);
            
            if (window.filtros.clase) {
                clasesSelect.value = window.filtros.clase;
            }
        }

        // Habilitar procesos
        let operadorValue = operadoresSelect.value;
        let claseValue = window.filtros.clase;
        let procesosClase = null;
        if (otValue != 0 && operadorValue != 0 && claseValue != 0) {
            let operadorData = datos[otValue]["operadores"][operadorValue];
            if (operadorData && operadorData["clases"] && operadorData["clases"][claseValue]) {
                procesosClase = operadorData["clases"][claseValue]["procesos"];
            }
        }

        let boxProcesos = document.querySelector(".procesos");
        if (procesosClase && procesosClase.length > 0) {
            let selectProcesos = des_habilitarCampo(boxProcesos, "procesos", procesosClase);
            boxProcesos.appendChild(selectProcesos);
            selects["procesos"] = selectProcesos;
            if (window.filtros.proceso) {
                selectProcesos.value = window.filtros.proceso;
            }
        }

        // Mostrar pedido
        let boxPedido = document.querySelector(".pedido");
        let pedido = (claseValue != 0 && otValue != 0 && datos[otValue]["clases_ot"] && datos[otValue]["clases_ot"][claseValue])
            ? datos[otValue]["clases_ot"][claseValue]["pedido"]
            : null;
        crearInputConValor(boxPedido, pedido, "pedido");



        checkFormValidity();
    }
}
isInitializing = false;

function checkFormValidity() {
    let otSelect = document.getElementById("ot-select");
    let operadorSelect = document.getElementById("operadores-select");
    let clasesSelect = document.getElementById("clases-select");
    let procesosSelect = document.getElementById("procesos-select");
    let pedidoInput = document.getElementById("pedido-input");

    let otValue = otSelect ? otSelect.value : "0";
    let operadorValue = operadorSelect ? operadorSelect.value : "0";
    let claseValue = clasesSelect ? clasesSelect.value : "0";
    let procesoValue = procesosSelect ? procesosSelect.value : "0";

    let isValid = otValue != "0" && operadorValue != "0" && claseValue != "0" && procesoValue != "0";

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
        // Contar parámetros faltantes de los 5 elementos visuales del dashboard
        let missing = 0;
        if (!otSelect || otValue == "0") missing++;
        if (!operadorSelect || operadorValue == "0") missing++;
        if (!clasesSelect || claseValue == "0") missing++;
        if (!pedidoInput || !pedidoInput.value || pedidoInput.value == "0" || pedidoInput.value == "—") missing++;
        if (!procesosSelect || procesoValue == "0") missing++;

        missing = Math.min(Math.max(missing, 1), 5);

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
