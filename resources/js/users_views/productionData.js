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
                    let boton = document.getElementById("button");
                    if (boton.style.display != "none") {
                        boton.style.display = "none";
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
                                barColor = "#27ae60"; // Verde (Aceptable)
                            } else if (realPct >= 40) {
                                barColor = "#e67e22"; // Naranja (Medio)
                            } else {
                                barColor = "#e74c3c"; // Rojo (Bajo)
                            }
                            let container_progress =
                                document.createElement("div");
                            container_progress.className = "container-progress";
                            let progress_bar = document.createElement("div");
                            progress_bar.className = "progress-bar";
                            progress_bar.style.width = visualPct + "%";
                            progress_bar.style.backgroundColor = barColor;
                            progress_bar.innerHTML = visualPct + "%";

                            container_progress.appendChild(progress_bar);
                            td.appendChild(container_progress);
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
    selects = [];
//Crear select de OTs y agregarlo al div
box = document.querySelector(".ot");
selects["ot"] = insertarSelect("ot", datos);
box.appendChild(selects["ot"]);
//prettier-ignore
//Aplicar acciones cuando se seleccione una OT
selects["ot"].addEventListener("change", () => {
    //Habilitar o deshabilitar campos dependiendo del valor del select OT
    habilitar = (selects["ot"].value != 0) ? datos[selects["ot"].value]["operadores"] : null;
    aplicarAccionesToEvents(habilitar, ["operadores", "clases", "pedido", "procesos", "boton"]);

    //Crear el campo de moldura
    box = document.querySelector(".ot");
    let moldura = (habilitar != null) ? datos[selects["ot"].value]["moldura"] : selects["ot"].value;
    crearInputConValor(box, moldura, "moldura");

    //Aplicar acciones cuando se seleccione un operador
    selects["operadores"].addEventListener("change", () => {
        // console.log(datos[selects["ot"]]["operadores"]);
        habilitar = (selects["operadores"].value != 0) ? datos[selects["ot"].value]["operadores"][selects["operadores"].value]["clases"] : null;
        aplicarAccionesToEvents(habilitar, ["clases", "pedido", "procesos", "boton"]);

        //Aplicar acciones cuando se seleccione una clase
        selects["clases"].addEventListener("change", () => {
            // console.log(datos[selects["ot"]]["operadores"]);
            habilitar = (selects["clases"].value != 0) ? datos[selects["ot"].value]["operadores"][selects["operadores"].value]["clases"][selects["clases"].value]["procesos"] : null;
            aplicarAccionesToEvents(habilitar, ["procesos", "boton"]);

            //Crear el campo de pedido
            box = document.querySelector(".pedido");
            let pedido = (habilitar != null) ? datos[selects["ot"].value]["operadores"][selects["operadores"].value]["clases"][selects["clases"].value]["pedido"] : null;
            crearInputConValor(box, pedido, "pedido");

            selects["procesos"].addEventListener("change", () => {
                habilitar = selects["procesos"].value != 0 ? true : false;
                let boton = document.getElementById("button");
                if (habilitar) {
                    boton.style.display = "block";
                } else {
                    boton.style.display = "none";
                }
            });
        });
    });
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

if (window.filtros !== undefined) {
    let dashboard2 = document.querySelector(".dashboard2");
    let div_table = document.querySelector(".div-table");

    div_table.appendChild(crearTabla(datosOperadores));
    dashboard2.appendChild(crearLeyendaProductividad());
}
