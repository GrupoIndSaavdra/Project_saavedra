// ===============================
// Variables globales
// ===============================
let selects = {};
let html5QrCode = null;

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
            const kilos = opcion.kilos ?? 0;
            option.text = `${nombre} - Lote: ${lote} (${kilos} kg disponibles)`;
        }

        select.appendChild(option);
    });

    select.addEventListener("change", () => {
        // Actualizar campo hidden
        document.getElementById(selectId).value = select.value;
        changeColorSelect(select);
        actualizarBoton();
    });

    return select;
}

// ===============================
// Habilitar botón Guardar
// ===============================
function actualizarBoton() {
    const btn = document.getElementById("btnGuardar");
    const operador = document.getElementById("operador_id");
    const soldadura = document.getElementById("soldadura_id");
    const fecha = document.getElementById("fecha_entrega");
    const cantidad = document.getElementById("cantidad");

    btn.disabled = !(operador?.value && soldadura?.value && fecha?.value && cantidad?.value);
}

// ===============================
// Bloquear campos tras escaneo
// ===============================
function bloquearCampos() {
    document.getElementById("operador_id_display").disabled = true;
    document.getElementById("soldadura_id_display").disabled = true;
    document.getElementById("cantidad").readOnly = true;
    document.getElementById("fecha_entrega").readOnly = true;
}

// ===============================
// QR – lectura exitosa (detecta tipo de QR)
// ===============================
function onScanSuccess(decodedText) {
    try {
        const lines = decodedText.replace(/\r/g, "").split("\n");
        
        if (lines.length === 2) {
            // QR de soldadura registrada (nombre y lote)
            procesarQRSoldadura(lines);
        } else if (lines.length === 4) {
            // QR de liberación completo (operador, soldadura, fecha, cantidad)
            procesarQRLiberacion(lines);
        } else {
            throw new Error("Formato de QR no reconocido");
        }

    } catch (e) {
        alert("Error procesando QR: " + e.message);
    }
}

// ===============================
// Procesar QR de soldadura (solo nombre y lote)
// ===============================
function procesarQRSoldadura(lines) {
    const nombre = lines[0].trim();
    const lote = lines[1].trim();

    if (!window.soldaduras || window.soldaduras.length === 0) {
        throw new Error('No hay soldaduras disponibles');
    }

    // Buscar soldadura por nombre y lote
    const soldaduraEncontrada = window.soldaduras.find(sol => 
        sol.nombre.trim() === nombre && sol.lote.trim() === lote
    );
    
    if (!soldaduraEncontrada) {
        throw new Error(`Soldadura "${nombre}" con lote "${lote}" no encontrada`);
    }
    
    // Solo completar el campo de soldadura
    const soldaduraSelect = document.getElementById("soldadura_id_display");
    soldaduraSelect.value = soldaduraEncontrada.id;
    document.getElementById("soldadura_id").value = soldaduraEncontrada.id;
    soldaduraSelect.dispatchEvent(new Event('change'));
    
    changeColorSelect(soldaduraSelect);
    
    // Bloquear solo el campo de soldadura
    soldaduraSelect.disabled = true;
    
    actualizarBoton();

    if (html5QrCode) {
        html5QrCode.stop().then(() => html5QrCode.clear());
    }
    document.getElementById("qrModal").style.display = "none";

    alert(`Soldadura "${nombre}" - Lote "${lote}" seleccionada correctamente`);
}

// ===============================
// Procesar QR de liberación completo
// ===============================
function procesarQRLiberacion(lines) {
    const operadorId = lines[0].trim();
    const soldaduraId = lines[1].trim();
    const fecha = lines[2].trim();
    const cantidad = lines[3].trim();

    // Verificar que los datos estén disponibles
    if (!window.operadores || window.operadores.length === 0) {
        throw new Error('No hay operadores disponibles');
    }
    if (!window.soldaduras || window.soldaduras.length === 0) {
        throw new Error('No hay soldaduras disponibles');
    }

    // Seleccionar operador y soldadura por ID
    const operadorSelect = document.getElementById("operador_id_display");
    const operadorEncontrado = window.operadores.find(op => op.id == operadorId);
    
    if (!operadorEncontrado) {
        throw new Error(`Operador con ID ${operadorId} no encontrado`);
    }
    
    operadorSelect.value = operadorId;
    document.getElementById("operador_id").value = operadorId;
    operadorSelect.dispatchEvent(new Event('change'));

    const soldaduraSelect = document.getElementById("soldadura_id_display");
    const soldaduraEncontrada = window.soldaduras.find(sol => sol.id == soldaduraId);
    
    if (!soldaduraEncontrada) {
        throw new Error(`Soldadura con ID ${soldaduraId} no encontrada`);
    }
    
    soldaduraSelect.value = soldaduraId;
    document.getElementById("soldadura_id").value = soldaduraId;
    soldaduraSelect.dispatchEvent(new Event('change'));

    document.getElementById("fecha_entrega").value = fecha;
    document.getElementById("cantidad").value = cantidad;

    changeColorSelect(operadorSelect);
    changeColorSelect(soldaduraSelect);

    bloquearCampos();
    actualizarBoton();

    if (html5QrCode) {
        html5QrCode.stop().then(() => html5QrCode.clear());
    }
    document.getElementById("qrModal").style.display = "none";

    alert('QR de liberación procesado correctamente');
}

// ===============================
// Iniciar escáner
// ===============================
function iniciarEscaneo() {
    const qrModal = document.getElementById("qrModal");
    qrModal.style.display = "flex";

    html5QrCode = new Html5Qrcode("reader");
    html5QrCode.start(
        { facingMode: "environment" },
        { fps: 10, qrbox: 250 },
        onScanSuccess
    ).catch(err => alert("Error al iniciar la cámara: " + err));
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

    document.getElementById("fecha_entrega")?.addEventListener("input", actualizarBoton);
    document.getElementById("cantidad")?.addEventListener("input", actualizarBoton);

    document.getElementById("btnEscanear")?.addEventListener("click", e => {
        e.preventDefault();
        iniciarEscaneo();
    });

    actualizarBoton();
});

// ===============================
// Control modal QR
// ===============================
function abrirQR() {
    document.body.style.overflow = "hidden";
    document.getElementById("qrModal").style.display = "flex";
}

window.cerrarQR = function () {
    if (window.html5QrCode) {
        window.html5QrCode.stop().then(() => window.html5QrCode.clear());
    }
    document.body.style.overflow = "";
    document.getElementById("qrModal").style.display = "none";
};
