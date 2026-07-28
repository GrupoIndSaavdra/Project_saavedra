// ===============================
// Variables globales
// ===============================
let selects = {};

// ===============================
// Función para mostrar alertas temporales
// ===============================
function mostrarAlertaTemporal(mensaje, tipo = 'success') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${tipo} custom-alert`;
    alertDiv.innerHTML = `
        <button class="close-alert">&times;</button>
        ${mensaje}
    `;
    
    const h2 = document.querySelector('.wrapper h2');
    h2.parentNode.insertBefore(alertDiv, h2.nextSibling);
    
    alertDiv.querySelector('.close-alert').addEventListener('click', function() {
        alertDiv.remove();
    });
    
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

// ===============================
// Estilos select
// ===============================
function changeColorSelect(selectElement) {
    if (selectElement.value) {
        selectElement.style.backgroundColor = "#03396610";
        selectElement.style.color = "#000";
    } else {
        selectElement.style.backgroundColor = "#033966";
        selectElement.style.color = "#fff";
    }
}

// ===============================
// Crear select dinámico
// ===============================
function crearSelect(selectId, opciones, placeholder, tipo) {
    const displayId = selectId + '_display';
    const oldSelect = document.getElementById(displayId);
    if (oldSelect) oldSelect.remove();

    const select = document.createElement("select");
    select.id = displayId;
    select.name = displayId;
    select.className = "form-control";

    const firstOption = document.createElement("option");
    firstOption.value = "";
    firstOption.text = placeholder;
    select.appendChild(firstOption);

    opciones.forEach(opcion => {
        const option = document.createElement("option");
        option.value = opcion.id;

        if (tipo === "operador") {
            const nombre = (opcion.nombre ?? "").trim();
            const paterno = (opcion.a_paterno ?? "").trim();
            const matricula = opcion.matricula ?? "";
            const nombreMostrar = nombre ? (paterno ? `${nombre} ${paterno}` : nombre) : paterno;
            option.text = `${matricula} - ${nombreMostrar}`;
        }

        if (tipo === "soldadura") {
            const nombre = (opcion.nombre ?? "").trim();
            const lote = opcion.lote ?? "";
            const kilos = opcion.kilos_totales ?? opcion.kilos ?? 0;
            option.value = `${nombre}|${lote}`;
            option.text = `${nombre} - Lote: ${lote} (${kilos} kg disponibles)`;
        }

        select.appendChild(option);
    });

    select.addEventListener("change", () => {
        document.getElementById(selectId).value = select.value;
        changeColorSelect(select);
        actualizarBoton();
        calcularQRs();
    });

    return select;
}

// ===============================
// Calcular QRs necesarios
// ===============================
function calcularQRs() {
    const soldaduraSelect = document.getElementById("soldadura_id_display");
    const cantidadInput = document.getElementById("cantidad");
    
    if (!soldaduraSelect?.value || !cantidadInput?.value) {
        return;
    }
    
    const cantidad = parseFloat(cantidadInput.value);
    if (cantidad <= 0) {
        return;
    }
    
    // Obtener información de la soldadura seleccionada
    const selectedOption = soldaduraSelect.options[soldaduraSelect.selectedIndex];
    const optionText = selectedOption.text;
    
    // Extraer kilos disponibles del texto
    const match = optionText.match(/\((\d+(?:\.\d+)?) kg disponibles\)/);
    if (match) {
        const kilosDisponibles = parseFloat(match[1]);
        
        if (cantidad > kilosDisponibles) {
            cantidadInput.setCustomValidity(`Solo hay ${kilosDisponibles} kg disponibles`);
            mostrarAlertaTemporal(`Solo hay ${kilosDisponibles} kg disponibles`, 'warning');
        } else {
            cantidadInput.setCustomValidity('');
        }
    }
}

// ===============================
// Habilitar botón Generar
// ===============================
function actualizarBoton() {
    const btn = document.getElementById("btnGenerar");
    const operador = document.getElementById("operador_id");
    const soldadura = document.getElementById("soldadura_id");
    const fecha = document.getElementById("fecha_generacion");
    const cantidad = document.getElementById("cantidad");

    btn.disabled = !(operador?.value && soldadura?.value && fecha?.value && cantidad?.value);
}

// ===============================
// Inicialización
// ===============================
document.addEventListener("DOMContentLoaded", () => {
    const operadoresData = window.operadores || [];
    const soldadurasData = window.soldaduras || [];

    const contOperador = document.querySelector(".operador-container");
    const contSoldadura = document.querySelector(".soldadura-container");

    const selectOperador = crearSelect("operador_id", operadoresData, "Seleccione un operador", "operador");
    const selectSoldadura = crearSelect("soldadura_id", soldadurasData, "Seleccione una soldadura", "soldadura");

    contOperador.appendChild(selectOperador);
    contSoldadura.appendChild(selectSoldadura);

    selects.operador = selectOperador;
    selects.soldadura = selectSoldadura;

    // Event listeners
    document.getElementById("fecha_generacion")?.addEventListener("input", actualizarBoton);
    document.getElementById("cantidad")?.addEventListener("input", actualizarBoton);

    actualizarBoton();
});