function createSelect(selectWO, arrayWo) {
    console.log(arrayWo);
    if (selectWO.value != "null") {
        let form = document.querySelector(".form");
        for (let i = 0; i < arrayWo.length; i++) {
            if (arrayWo[i][0] == selectWO.value) {
                //Eliminar select de clases si existe
                let label = document.querySelectorAll(".label-select")[1];
                let select_classes = document.querySelector(".select-classes");
                let button = document.getElementsByClassName("btn-search")[0];
                if (select_classes != null) {
                    label.remove();
                    select_classes.remove();
                    if(button != null) {
                        button.remove();
                    }
                }

                //Creacion de la etiqueta para el select de clases
                label = document.createElement("label");
                label.for = "classes";
                label.className = "label-select";
                label.innerHTML = "Clase";

                //Creacion del select de clases
                select_classes = document.createElement("select");
                select_classes.name = "class";
                select_classes.className = "select-classes select";

                //Creacion de las opciones para el select de clases
                arrayWo[i][1].forEach((classArray, index) => {
                    let option = document.createElement("option");
                    if (index == 0) {
                        let firstOption = document.createElement("option");
                        firstOption.value = "null";
                        firstOption.text = "Selecciona una opción";
                        select_classes.appendChild(firstOption);
                    }
                    option.value = classArray[0];
                    option.text = classArray[1];
                    select_classes.appendChild(option);
                });

                //Agregar evento al select de clases
                select_classes.addEventListener("change", function () {
                    changeColorSelect(select_classes, select_classes.value);
                    if (select_classes.value != "null") {
                        form.appendChild(button);
                    } else {
                        button.remove();
                    }
                });
                //Creacion del boton
                button = document.createElement("button");
                button.className = "btn-search";
                button.type = "submit";
                button.innerHTML = "Buscar máquinas";

                //Agregar elementos al div
                form.appendChild(select_classes);
                form.appendChild(label);
            }
        }
    } else {
        //Eliminar select de clases
        let label = document.querySelectorAll(".label-select")[1];
        let select_classes = document.querySelector(".select-classes");
        let button = document.getElementsByClassName("btn-search")[0];
        if (select_classes != null) {
            label.remove();
            select_classes.remove();
            button.remove();
        }
    }
}
function changeColorSelect(selectElement, value) {
    console.log(value);
    if (value != "null") {
        selectElement.style.backgroundColor = "#03396610";
        selectElement.style.color = "#000";
    }else{
        selectElement.style.backgroundColor = "#033966";
        selectElement.style.color = "#fff";
    }
}

let select_wo = document.querySelector(".select-workOrder");

select_wo.addEventListener("change", function () {
    changeColorSelect(select_wo, select_wo.value);
    createSelect(select_wo, dataWO);
});
