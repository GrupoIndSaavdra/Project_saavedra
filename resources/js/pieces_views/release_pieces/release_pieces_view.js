function crearSelect(ot, arregloOT, div) {
    if (ot.value != "null") {
        for (let i = 0; i < arregloOT.length; i++) {
            if (arregloOT[i][0] == ot.value) {
                //Eliminar select de clases si existe
                let label = document.getElementsByClassName("label-select")[1];
                let select_clases = document.getElementById("clases");
                let button = document.querySelector(".btn-search");
                if (select_clases != null) {
                    label.remove();
                    select_clases.remove();
                    if (button != null) {
                        button.remove();
                    }
                }

                //Creacion de la etiqueta para el select de clases
                label = document.createElement("label");
                label.for = "clases";
                label.className = "label-select";
                label.innerHTML = "Clase";

                //Creacion del select de clases
                select_clases = document.createElement("select");
                select_clases.name = "class";
                select_clases.id = "clases";
                select_clases.className = "select-classes select";

                //Creacion de las opciones para el select de clases
                arregloOT[i][1].forEach((clase, index) => {
                    let option = document.createElement("option");
                    if (index == 0) {
                        let firstOption = document.createElement("option");
                        firstOption.value = "null";
                        firstOption.text = "Selecciona una opción";
                        select_clases.appendChild(firstOption);
                    }
                    option.value = clase[0];
                    option.text = clase[1];
                    select_clases.appendChild(option);
                });
                select_clases.addEventListener("change", function () {
                    changeColorSelect(select_clases, select_clases.value);
                    if (select_clases.value != "null") {
                        div.appendChild(button);
                    } else {
                        button.remove();
                    }
                });

                //Creacion del boton
                button = document.createElement("button");
                button.className = "btn-search";
                button.type = "submit";
                button.innerHTML = "Buscar";

                //Agregar elementos al div
                div.appendChild(select_clases);
                div.appendChild(label);
            }
        }
    } else {
        //Eliminar select de clases
        let label = document.getElementsByClassName("label-select")[1];
        let select_clases = document.getElementById("clases");
        let button = document.getElementsByClassName("btn-search")[0];
        if (select_clases != null) {
            label.remove();
            select_clases.remove();
            button.remove();
        }
    }
}
function changeColorSelect(selectElement, value) {
    console.log(value);
    if (value != "null") {
        selectElement.style.backgroundColor = "#03396610";
        selectElement.style.color = "#000";
    } else {
        selectElement.style.backgroundColor = "#033966";
        selectElement.style.color = "#fff";
    }
}

let select_ot = document.querySelector(".select-workOrder");
let container = document.querySelector(".select-container");
select_ot.addEventListener("change", function () {
    changeColorSelect(select_ot, select_ot.value);
    crearSelect(select_ot, window.arregloOT, container);
});
