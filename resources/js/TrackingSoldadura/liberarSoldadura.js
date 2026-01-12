// ===============================
// Variables globales
// ===============================
let selects = {};
let html5QrCode = null;

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
    
    // Insertar donde están los mensajes del sistema
    const messagesContainer = document.querySelector('.wrapper h2').nextElementSibling;
    if (messagesContainer && messagesContainer.classList.contains('alert')) {
        messagesContainer.parentNode.insertBefore(alertDiv, messagesContainer.nextSibling);
    } else {
        const h2 = document.querySelector('.wrapper h2');
        h2.parentNode.insertBefore(alertDiv, h2.nextSibling);
    }
    
    // Agregar funcionalidad al botón de cerrar
    alertDiv.querySelector('.close-alert').addEventListener('click', function() {
        alertDiv.remove();
    });
    
    // Auto-remover después de 5 segundos
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
            option.value = `${nombre}|${lote}`; // Cambiar para enviar nombre|lote
            option.text = `${nombre} - Lote: ${lote} (${kilos} kg disponibles)`;
        }

        select.appendChild(option);
    });

    select.addEventListener("change", () => {
        // Actualizar campo hidden
        document.getElementById(selectId).value = select.value;
        changeColorSelect(select);
    });

    return select;
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
        const trimmedText = decodedText.trim();
        
        // Verificar si es un QR de ID numérico (QR individual generado)
        if (/^\d+$/.test(trimmedText)) {
            procesarQRIndividual(trimmedText);
            return;
        }
        
        // Si no es numérico, verificar si es QR de soldadura (2 líneas)
        const lines = decodedText.replace(/\r/g, "").split("\n");
        if (lines.length === 2) {
            procesarQRSoldadura(lines);
        } else {
            throw new Error("Formato de QR no reconocido");
        }

    } catch (e) {
        console.error("Error procesando QR:", e.message);
        mostrarAlertaTemporal(e.message, 'danger');
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
    
    // Solo completar el campo de soldadura con formato nombre|lote
    const soldaduraSelect = document.getElementById("soldadura_id_display");
    const valorSoldadura = `${nombre}|${lote}`;
    soldaduraSelect.value = valorSoldadura;
    document.getElementById("soldadura_id").value = valorSoldadura;
    soldaduraSelect.dispatchEvent(new Event('change'));
    
    changeColorSelect(soldaduraSelect);
    
    // Bloquear solo el campo de soldadura
    soldaduraSelect.disabled = true;

    if (html5QrCode) {
        html5QrCode.stop().then(() => html5QrCode.clear()).catch(() => {});
    }
    document.getElementById("qrModal").style.display = "none";

    // Mostrar alerta temporal de éxito
    mostrarAlertaTemporal(`Soldadura "${nombre}" - Lote "${lote}" seleccionada correctamente`, 'success');
}

// ===============================
// Procesar QR individual generado (solo ID)
// ===============================
function procesarQRIndividual(qrId) {
    // Mostrar estado de procesamiento
    document.getElementById("estado_qr").value = "PROCESANDO...";
    document.getElementById("estado_qr").style.backgroundColor = "#fff3cd";
    
    // Enviar QR ID al servidor para validación y procesamiento
    fetch('/soldadura/liberar/validar-qr', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ qr_content: qrId })
    })
    .then(response => {
        // Manejar tanto respuestas exitosas como errores
        if (response.ok) {
            return response.json();
        } else {
            // Para errores HTTP (422, 500, etc.), intentar parsear JSON
            return response.json().then(errorData => {
                throw new Error(errorData.message || 'Error del servidor');
            }).catch(() => {
                throw new Error(`Error HTTP ${response.status}: ${response.statusText}`);
            });
        }
    })
    .then(data => {
        if (data.success) {
            document.getElementById("estado_qr").value = "LIBERADO";
            document.getElementById("estado_qr").style.backgroundColor = "#d4edda";
            mostrarAlertaTemporal(data.message, 'success');
            // Limpiar campos después de 3 segundos
            setTimeout(() => {
                location.reload();
            }, 3000);
        } else {
            document.getElementById("estado_qr").value = "ERROR";
            document.getElementById("estado_qr").style.backgroundColor = "#f8d7da";
            mostrarAlertaTemporal(data.message || 'Error procesando QR', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById("estado_qr").value = "ERROR";
        document.getElementById("estado_qr").style.backgroundColor = "#f8d7da";
        mostrarAlertaTemporal(error.message || 'Error de conexión con el servidor', 'danger');
    })
    .finally(() => {
        if (html5QrCode && html5QrCode.getState() === Html5QrcodeScannerState.SCANNING) {
            html5QrCode.stop().then(() => html5QrCode.clear()).catch(() => {});
        }
        document.getElementById("qrModal").style.display = "none";
    });
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

    const selectOperador = crearSelect("operador_id", operadoresData, "Información del operador", "operador");
    const selectSoldadura = crearSelect("soldadura_id", soldadurasData, "Información de la soldadura", "soldadura");

    contOperador.appendChild(selectOperador);
    contSoldadura.appendChild(selectSoldadura);

    // Bloquear TODOS los campos - solo lectura
    selectOperador.disabled = true;
    selectSoldadura.disabled = true;
    document.getElementById("fecha_entrega").readOnly = true;
    document.getElementById("cantidad").readOnly = true;

    selects.operador = selectOperador;
    selects.soldadura = selectSoldadura;

    document.getElementById("btnEscanear")?.addEventListener("click", e => {
        e.preventDefault();
        iniciarEscaneo();
    });
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
        window.html5QrCode.stop().then(() => window.html5QrCode.clear()).catch(() => {});
    }
    document.body.style.overflow = "";
    document.getElementById("qrModal").style.display = "none";
};
