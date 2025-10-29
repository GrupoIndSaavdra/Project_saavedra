const newReportDiv = document.querySelector(".div-new-report");
if (newReportDiv) {
    newReportDiv.addEventListener("click", function () {
        window.location.href = window.reportRoute;
    });
    // Cuando el mouse entra en el área, inicia la animación
    newReportDiv.addEventListener("mouseenter", function () {
        // Agrega la clase que activa la animación
        newReportDiv.classList.add("active");
        newReportDiv.innerHTML = ""; // limpiar el contenido
        // Espera a que termine la animación (~100ms), y luego muestra el contenido
        setTimeout(() => {
            newReportDiv.innerHTML =
                '<h1 style="opacity:0; transform: translateX(30px); transition: all 0.4s ease; letter-spacing: 0.1em;">Nuevo reporte</h1>';

            // Activar entrada suave del texto
            setTimeout(() => {
                const h1 = newReportDiv.querySelector("h1");
                h1.style.opacity = "1";
                h1.style.transform = "translateX(0)";
            }, 50);
        }, 100);
    });

    // Cuando el mouse sale del área, revierte la animación
    newReportDiv.addEventListener("mouseleave", function () {
        newReportDiv.classList.remove("active");
        newReportDiv.innerHTML = ">"; // limpiar el contenido
    });
}

if (window.pieces_Released && window.pieces_Released.length > 0) {
    const divOpacity = document.createElement("div");
    divOpacity.classList.add("filter-opacity-home");

    let operacion = false;
    // Crear elementos
    const divTable = document.createElement("div");
    divTable.className = "div-table";
    const table = document.createElement("table");
    table.id = "table";

    const thead = document.createElement("thead");
    const tr = document.createElement("tr");

    // Agregar encabezados base
    const headers = ["N_juego", "Nombre del operador", "Máquina", "Proceso"];

    // Agregar columnas base
    headers.forEach((header, i) => {
        const th = document.createElement("th");
        th.textContent = header;
        // Aplicar estilos a columnas específicas
        if (header === "Nombre del operador" || header === "Proceso") {
            th.style.width = "500px";
        }
        tr.appendChild(th);
    });

    // Verificar si hay columna de "Operacion"
    for (let pieza of window.pieces_Released) {
        if (pieza[4] === "Operacion Equipo") {
            const thOperacion = document.createElement("th");
            thOperacion.textContent = "Operacion";
            tr.appendChild(thOperacion);
            operacion = true;
            break;
        }
    }

    // Agregar columnas finales
    const moreHeaders = [
        { name: "Errores", width: "300px" },
        "Fecha de Maquinado",
        "Fecha de Liberación",
        "Liberado/Rechazado por",
        "Liberar",
        "Rechazar",
        "Ver",
    ];

    moreHeaders.forEach((header) => {
        const th = document.createElement("th");
        if (typeof header === "object") {
            th.textContent = header.name;
            th.style.width = header.width;
        } else {
            th.textContent = header;
        }
        tr.appendChild(th);
    });

    // Estructurar tabla
    thead.appendChild(tr);
    table.appendChild(thead);
    divTable.appendChild(table);

    // Finalmente, agregar a tu documento
    divOpacity.appendChild(divTable); // O donde tú necesites insertarlo
    document.body.appendChild(divOpacity);
    crearTabla(window.pieces_Released, window.info_Pieces);
}

var operacion = false;

function crearTabla(piezas, infoPiezas) {
    //Crea la tabla de piezas trabajadas en la O.T
    // console.log(piezas);
    const table = document.querySelector(".table");
    const tbody = document.createElement("tbody");
    //Convertir el objeto a un array
    piezas = convertirObjectToArray(piezas);
    for (let i = 0; i < piezas.length; i++) {
        const tr = document.createElement("tr");
        for (let j = 0; j < piezas[i].length + 1; j++) {
            if (j >= 13) {
                break;
            }
            let td = document.createElement("td");
            switch (j) {
                case 6:
                    td.textContent = crearFecha(piezas[i][j]);
                    break;
                case 9:
                    td.textContent = piezas[i][13];
                    td.style.width = "600px";
                    break;
                case 10:
                    if (!piezas[i][5].includes("Incompleto") && piezas[i][9] != 1) {
                        td.appendChild(crearBotonLiberar(infoPiezas, i, piezas));
                        tr.appendChild(td);
                    }
                    break;
                case 11:
                    if (piezas[i][9] != 2) {
                        td.appendChild(crearBotonRechazar(infoPiezas, i));
                    }
                    break;
                case 12:
                    td.appendChild(crearBotonVer(infoPiezas, i, piezas[i][2]));
                    break;
                default:
                    if (piezas[i][j] != undefined) {
                        if (j == 0) {
                            td.textContent = piezas[i][j];
                            let tdClass = document.createElement("td");
                            tdClass.textContent = piezas[i][12];
                            tr.appendChild(tdClass);
                        } else {
                            td.textContent = piezas[i][j];
                        }
                    } else {
                        td.textContent = "";
                    }
                    break;
            }
            tr.appendChild(td);
            switch (piezas[i][9]) {
                case 0:
                    if (piezas[i][5].includes("Incompleto")) {
                        tr.style.backgroundColor = "#FFFF99";
                    } else if (piezas[i][5] == "Ninguno") {
                        tr.style.backgroundColor = "#ACF980A8";
                    } else {
                        tr.style.backgroundColor = "#E59CFF";
                    }
                    break;
                case 1:
                    tr.style.backgroundColor = "#79BFED";
                    break;
                case 2:
                    tr.style.backgroundColor = "#EC7063";
                    break;
            }
        }
        tbody.appendChild(tr);
    }
    table.appendChild(tbody);
}

function convertirObjectToArray(obj) {
    // console.log(obj);
    let array = [];
    for (let i = 0; i < obj.length; i++) {
        array.push(Object.values(obj[i]));
    }
    return array;
}

function crearFecha(fecha) {
    let cadena = "";
    if (fecha != "No liberado") {
        let array = fecha.split("T");
        cadena = array[0] + "\n " + array[1].slice(0, 8);
    } else {
        cadena = fecha;
    }
    return cadena;
}

function crearBotonLiberar(infoPiezas, i, piezas) {
    const a = document.createElement("a");
    a.className = "btn-liberar";

    let bool;
    if (infoPiezas[i][2] == "Ninguno" && piezas[i][piezas[i].length - 2] != 2) {
        bool = true;
    } else {
        bool = false;
    }
    let url = `${window.baseUrl}/piezasLiberar/${infoPiezas[i][0]}/${infoPiezas[i][1]}/${true}/${bool}/${null}`;
    a.href = url;

    const image = document.createElement("img");
    image.src = window.liberar;
    image.alt = "Liberar";
    image.className = "ver";
    a.appendChild(image);
    return a;
}
function crearBotonRechazar(infoPiezas, i) {
    const a = document.createElement("a");
    a.className = "btn-liberar";
    let url = `${window.baseUrl}/piezasLiberar/${infoPiezas[i][0]}/${infoPiezas[i][1]}/${false}/${false}/${null}`;
    a.href = url;

    const image = document.createElement("img");
    image.src = window.rechazar;
    image.alt = "Rechazar";
    image.className = "ver";
    a.appendChild(image);
    return a;
}
function crearBotonVer(infoPiezas, i, usuarios) {
    const a = document.createElement("a");
    a.className = "btn-pza";

    let nPiezas = [];
    for (let j = 0; j < infoPiezas[i][0].length; j++) {
        nPiezas.push(infoPiezas[i][0][j]);
    }
    //INFORMACIÓN DE LAS PIEZAS O PIEZA
    let url = `${window.baseUrl}/pieces/${nPiezas}/${infoPiezas[i][1]}/quality`;
    a.href = url;

    const image = document.createElement("img");
    image.src = window.ojito;
    image.alt = "Ver";
    image.className = "ver";
    a.appendChild(image);
    return a;
}
