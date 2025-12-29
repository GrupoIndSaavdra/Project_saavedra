// Variables globales para manejar selects
let selects = {};

// Cambiar estilo según valor
function changeColorSelect(selectElement) {
    if (selectElement.value) {
        selectElement.style.backgroundColor = "#03396610";
        selectElement.style.color = "#000";
    } else {
        selectElement.style.backgroundColor = "#033966";
        selectElement.style.color = "#fff";
    }
}

// Crear select dinámico
function crearSelect(selectId, opciones, placeholder, tipo) {
    // Eliminar select previo si existe
    let oldSelect = document.getElementById(selectId);
    if (oldSelect) oldSelect.remove();

    let select = document.createElement("select");
    select.id = selectId;
    select.name = selectId;
    select.className = "form-control";

    // Primera opción
    let firstOption = document.createElement("option");
    firstOption.value = "";
    firstOption.text = placeholder;
    select.appendChild(firstOption);

    // Opciones dinámicas
    opciones.forEach(opcion => {
        let option = document.createElement("option");
        option.value = opcion.id;

        if (tipo === "operador") {
            // Para OPERADORES: Matrícula - Nombre Apellido
            const nombre = (opcion.nombre ?? '').trim();
            const paterno = (opcion.a_paterno ?? '').trim();
            const matricula = opcion.matricula ?? 'Sin matrícula';

            const nombreMostrar = nombre
                ? (paterno ? `${nombre} ${paterno}` : nombre)
                : (paterno || 'Sin nombre');

            option.text = `${matricula} - ${nombreMostrar}`;

        } else if (tipo === "soldadura") {
            // Para SOLDADURAS: Nombre Apellido - Lote
            const nombre = (opcion.nombre ?? '').trim();
            const paterno = (opcion.a_paterno ?? '').trim();
            const lote = opcion.lote ?? opcion.numero_lote ?? 'Sin lote'; // Ajusta el nombre del campo según tu estructura

            const nombreMostrar = nombre
                ? (paterno ? `${nombre} ${paterno}` : nombre)
                : (paterno || 'Operador sin nombre');

            option.text = `${nombreMostrar} - Lote: ${lote}`;
        }

        select.appendChild(option);
    });

    select.addEventListener("change", function () {
        changeColorSelect(select);
        actualizarBoton();
    });

    return select;
}

// Habilitar/deshabilitar botón
function actualizarBoton() {
    let btn = document.querySelector("#btnGuardar");
    let operador = document.getElementById("operador_id");
    let soldadura = document.getElementById("soldadura_id");
    let fecha = document.getElementById("fecha_entrega");
    let cantidad = document.getElementById("cantidad");

    if (operador && soldadura && fecha && cantidad &&
        operador.value && soldadura.value && fecha.value && cantidad.value) {
        btn.disabled = false;
    } else {
        btn.disabled = true;
    }
}

// Inicialización
document.addEventListener("DOMContentLoaded", function () {
    const operadoresData = window.operadores;
    const soldadurasData = window.soldaduras;

    const contOperador = document.querySelector(".operador-container");
    const contSoldadura = document.querySelector(".soldadura-container");

    // Crear selects
    const selectOperador = crearSelect("operador_id", operadoresData, "Seleccione un operador", "operador");
    selects["operador_id"] = selectOperador;
    contOperador.appendChild(selectOperador);

    const selectSoldadura = crearSelect("soldadura_id", soldadurasData, "Seleccione una soldadura", "soldadura");
    selects["soldadura_id"] = selectSoldadura;
    contSoldadura.appendChild(selectSoldadura);

    // Inputs adicionales
    const fechaInput = document.getElementById("fecha_entrega");
    const cantidadInput = document.getElementById("cantidad");

    if (fechaInput) fechaInput.addEventListener("input", actualizarBoton);
    if (cantidadInput) cantidadInput.addEventListener("input", actualizarBoton);

    // Botón inicial
    actualizarBoton();
});
