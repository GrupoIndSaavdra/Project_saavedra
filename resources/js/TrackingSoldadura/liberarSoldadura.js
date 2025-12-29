// Variables globales para manejar selects
let selects = {};

// Cambiar estilo según valor seleccionado
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
    const oldSelect = document.getElementById(selectId);
    if (oldSelect) oldSelect.remove();

    const select = document.createElement("select");
    select.id = selectId;
    select.name = selectId;
    select.className = "form-control";

    // Primera opción (placeholder)
    const firstOption = document.createElement("option");
    firstOption.value = "";
    firstOption.text = placeholder;
    select.appendChild(firstOption);

    // Opciones dinámicas
    opciones.forEach(opcion => {
        const option = document.createElement("option");
        option.value = opcion.id;

        if (tipo === "operador") {
            const nombre = (opcion.nombre ?? '').trim();
            const paterno = (opcion.a_paterno ?? '').trim();
            const matricula = opcion.matricula ?? 'Sin matrícula';
            const nombreMostrar = nombre ? (paterno ? `${nombre} ${paterno}` : nombre) : (paterno || 'Sin nombre');
            option.text = `${matricula} - ${nombreMostrar}`;
        } else if (tipo === "soldadura") {
            const nombre = (opcion.nombre ?? '').trim();
            const lote = opcion.lote ?? 'Sin lote';
            option.text = `Nombre: ${nombre} - Lote: ${lote}`;
        }

        select.appendChild(option);
    });

    select.addEventListener("change", () => {
        changeColorSelect(select);
        actualizarBoton();
    });

    return select;
}

// Habilitar/deshabilitar botón Guardar
function actualizarBoton() {
    const btn = document.querySelector("#btnGuardar");
    const operador = document.getElementById("operador_id");
    const soldadura = document.getElementById("soldadura_id");
    const fecha = document.getElementById("fecha_entrega");
    const cantidad = document.getElementById("cantidad");

    btn.disabled = !(operador?.value && soldadura?.value && fecha?.value && cantidad?.value);
}

// Inicialización al cargar la página
document.addEventListener("DOMContentLoaded", function () {
    const operadoresData = window.operadores || [];
    const soldadurasData = window.soldaduras || [];

    const contOperador = document.querySelector(".operador-container");
    const contSoldadura = document.querySelector(".soldadura-container");

    // Crear selects dinámicos
    const selectOperador = crearSelect("operador_id", operadoresData, "Seleccione un operador", "operador");
    selects["operador_id"] = selectOperador;
    contOperador.appendChild(selectOperador);

    const selectSoldadura = crearSelect("soldadura_id", soldadurasData, "Seleccione una soldadura", "soldadura");
    selects["soldadura_id"] = selectSoldadura;
    contSoldadura.appendChild(selectSoldadura);

    // Inputs adicionales
    const fechaInput = document.getElementById("fecha_entrega");
    const cantidadInput = document.getElementById("cantidad");

    fechaInput?.addEventListener("input", actualizarBoton);
    cantidadInput?.addEventListener("input", actualizarBoton);

    // Botón inicialmente deshabilitado
    actualizarBoton();
});
