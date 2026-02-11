// selectClass.addEventListener('change', function () {
//     inicializarVariables();
// });
document.addEventListener("DOMContentLoaded", function () {
    if (workOrders.length !== 0){
        insertSelects(workOrders);
        let selectClass = document.querySelector(".class");
    }else {
        let div = document.querySelector(".search");
        let label = document.createElement("label");
        label.className = "form-label";
        label.textContent = "Aun no hay ordenes de trabajo registradas.";
        div.appendChild(label);
    }
});

function insertSelects(array) {
    let div = document.querySelector(".search");
    let arrayItems = createSelect(
        Object.keys(array),
        "workOrder",
        "Orden de trabajo"
    );
    let selectWO = arrayItems[0];
    selectWO.addEventListener("change", function () {
        removeSelect("class");
        if (selectWO.value != 0) {
            selectWO.style.backgroundColor = "#03396610";
            selectWO.style.color = "#000000";
            arrayItems = createSelect(
                Object.keys(array[selectWO.value]),
                "class",
                "Clase"
            );
            let selectClass = arrayItems[0];
            selectClass.addEventListener("change", function () {
                eliminarTabla(
                    document.getElementById("tabla"),
                    document.getElementById("btnTabla")
                );
                if (selectClass.value != 0) {
                    selectClass.style.backgroundColor = "#03396610";
                    selectClass.style.color = "#000000";
                } else {
                    selectClass.style.backgroundColor = "#033966";
                    selectClass.style.color = "#ffffff";
                }
                inicializarVariables(selectClass);
            });
            div.appendChild(selectClass);
            div.appendChild(arrayItems[1]);
        } else {
            selectWO.style.backgroundColor = "#033966";
            selectWO.style.color = "#ffffff";
            eliminarTabla(
                document.getElementById("tabla"),
                document.getElementById("btnTabla")
            );
        }
    });
    div.appendChild(selectWO);
    div.appendChild(arrayItems[1]);
}
function removeSelect(name) {
    let selectFounded = document.querySelector(`.${name}`);
    if (selectFounded != null) {
        selectFounded.remove();
        let labelFounded = document.querySelector(`label[for='${name}']`);
        labelFounded.remove();
    }
}
function createSelect(array, name, labelText) {
    let select = document.createElement("select");
    select.className = `form-select ${name}`;
    select.name = name;
    select.id = name;
    array.forEach(function (item, number) {
        if (number === 0) {
            let option = document.createElement("option");
            option.value = 0;
            option.textContent = "Selecciona una opción";
            select.appendChild(option);
        }
        let option = document.createElement("option");
        option.value = item;
        option.textContent = item;
        select.appendChild(option);
    });

    let label = document.createElement("label");
    label.className = "form-label";
    label.textContent = labelText;
    label.setAttribute("for", name);
    return [select, label];
}

//prettier-ignore
function inicializarVariables(select) {
    let form = document.querySelector(".form");
    let tabla = document.getElementById("tabla");
    let boton = document.getElementById("btnTabla");
    let procesos = [];
    let procesosDB = [];
    let encabezados = ["Proceso", "Tiempo (minutos)"];
    if (select.value != 0) {
        switch (select.value) {
            case "Bombillo":
            procesos = ["Cepillado","Desbaste-exterior","Laterales","1ra-Operación","Barreno-Maniobra","2da-Operación","Soldadura","Soldadura-PTA","Rectificado","Asentado","Calificado","Acabado-Bombillo","Barreno-Profundidad","Cavidades","Copiado","OffSet","Palomas","Rebajes"];

            procesosDB = ["cepillado","desbaste","revLaterales","primeraOpeSoldadura","barrenoManiobra","segundaOpeSoldadura","soldadura","soldaduraPTA","rectificado","asentado","revCalificado","acabadoBombillo","barrenoProfundidad","cavidades","copiado","offset","palomas","rebajes"];
                break;
            case "Molde":
            procesos = ["Cepillado","Desbaste-exterior","Laterales","1ra-Operación","Barreno-Maniobra","2da-Operación","Soldadura","Soldadura-PTA","Rectificado","Asentado","Calificado","Acabado-Molde","Barreno-Profundidad","Cavidades","Copiado","OffSet","Palomas","Rebajes"];
            procesosDB = ["cepillado","desbaste","revLaterales","primeraOpeSoldadura","barrenoManiobra","segundaOpeSoldadura","soldadura","soldaduraPTA","rectificado","asentado","revCalificado","acabadoMolde","barrenoProfundidad","cavidades","copiado","offset","palomas","rebajes"];
                break;
            case "Obturador":
            case "Fondo":
                procesos = ["Operacion-Equipo", "Soldadura", "Soldadura-PTA"];
                procesosDB = ["operacionEquipo", "soldadura", "soldaduraPTA"];
                break;
            case "Corona":
                procesos = ["Cepillado", "Desbaste-exterior"];
                procesosDB = ["cepillado", "desbaste"];
                break;
            case "Plato":
                procesos = ["Operacion-Equipo", "Barreno-Profundidad"];
                procesosDB = ["operacionEquipo", "barrenoProfundidad"];
                break;
            case "Embudo":
                procesos = ["Operacion-Equipo", "Embudo-CM"];
                procesosDB = ["operacionEquipo", "embudoCM"];
                break;
            default:
                break;
        }
        crearTabla( procesos, procesosDB, encabezados, tabla, boton, workOrders[document.querySelector(".workOrder").value][select.value], select.value, form);
    } else {
        eliminarTabla(tabla, boton);
    }
}
//prettier-ignore
function crearTabla( procesos, procesosDB, encabezados, tabla, boton, workOrders, clase, form) {
    eliminarTabla(tabla, boton);
    let div_table = document.createElement("div");
    div_table.className = "div-table";

    let btn = document.createElement("input");
    btn.type = "submit";
    btn.textContent = "Guardar cambios";
    btn.id = "btnTabla";
    let table = document.createElement("table");
    table.id = "tabla";
    table.classList.add("table");

    //Crear encabezado
    let encabezadosT = document.createElement("tr");
    for (let encabezado of encabezados) {
        let th = document.createElement("th");

        //Cambiar guiones por espacios
        th.textContent = encabezado;
        encabezadosT.appendChild(th);
    }
    table.appendChild(encabezadosT);

    //Crear filas
    if(procesos.length > 0) {
        for (let proceso in procesos) {
            let contador = 1;
            let fila = document.createElement("tr");
            for (let i = 0; i < encabezados.length; i++) {
                let columna = document.createElement("td");
                columna.style.padding = "3px";
                if (i === 0) {
                    let title = procesos[proceso].replace(/-/g, " ");
                    columna.textContent = title;
                } else {
                    let input = document.createElement("input");
                    input.type = "number";
                    input.className = "celdas";
    
                    //Mandar la seleccion del select a la ruta para identificar la clase
                    input.name = `${procesosDB[proceso]}`;
                    let valorInput = 0;
                    if (workOrders != null) {
                        valorInput = recorrerArrayTiempos(
                            workOrders,
                            procesosDB[proceso]
                        );
                    }
                    input.value = valorInput;
                    columna.appendChild(input);
                    contador++;
                }
                fila.appendChild(columna);
            }
            table.appendChild(fila);
        }
        div_table.appendChild(table);
        div_table.appendChild(btn);
    }else{
        let label = document.createElement("label");
        label.innerHTML = "No hay tiempos de produccion definidos para esta clase.";
        div_table.appendChild(label);
    }
    form.appendChild(div_table);
    setTimeout(() => {
        div_table.classList.add("show");
    }, 100);
}

function eliminarTabla(tabla, boton) {
    let div = document.querySelector(".div-table");
    let label = document.querySelector(".div-table label");

    if (tabla != undefined || label != undefined) {
        if (label != undefined) {
            label.remove();
        } else if (tabla != undefined) {
            tabla.remove();
            boton.remove();
        }
        div.classList.remove("show");
        setTimeout(() => {
            div.remove();
        }, 100);
        let div_search = document.getElementsByClassName("search")[0];
        div_search.style.margin = "auto";
    }
}

function recorrerArrayTiempos(array, proceso) {
    return array?.[proceso]?.["tiempo"] ?? 0;
}
