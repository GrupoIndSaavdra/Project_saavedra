/**
 * SISTEMA GLOBAL DE PRODUCTIVIDAD Y AUDITORÍA
 * GRUPO INDUSTRIAL SAAVEDRA
 */

// --- 1. SISTEMA DE LOGGING GLOBAL ---
window.logUserAction = function(actionName, detailsStr = null, customData = {}) {
    if (!detailsStr) {
        const defaultDescriptions = {
            "Carga de Formulario de Producción": "El operador cargó la interfaz principal del sistema.",
            "Consulta Documentación Técnica": "El operador consultó los manuales de procesos o ayudas visuales.",
            "Consulta Dibujos Técnicos": "El operador consultó los planos o dibujos técnicos de la pieza.",
            "Login Inspector Calidad": "El operador inició el protocolo de autenticación de calidad.",
            "Error Inspector Calidad": "El sistema detectó un fallo en la autenticación de calidad del operador.",
            "Proceso Correcto": "El operador sincronizó los datos técnicos de la pieza con el reporte general.",
            "Mensaje de Error": "El sistema registró una excepción o error durante la actividad del operador.",
            "Inicio de Reporte": "El operador inició un nuevo reporte de producción.",
            "Exceso de Tiempo": "El operador reconoció y aceptó un exceso de tiempo en el proceso.",
            "Liberación por Calidad": "El operador solicitó y obtuvo la liberación de piezas por calidad.",
            "Terminar Reporte": "El operador finalizó oficialmente el reporte de producción.",
            "Inicio de Sesión": "El operador autenticó su matrícula y contraseña para acceder al sistema.",
            "Inactividad en Formulario": "El operador reconoció la alerta por falta de actividad en el formulario de registro.",
            "Exceso de Tiempo de Maquinado": "El operador reconoció que el tiempo de operación de la pieza superó el límite estándar permitido.",
            "Inicio de Reporte Pendiente": "El operador reconoció que es obligatorio iniciar un reporte para registrar su actividad."
        };
        detailsStr = defaultDescriptions[actionName] || "Acción registrada sin detalles adicionales.";
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");
    if (!csrfToken || !window.baseUrl) return;

    let nPieza = window.pieceToBeUsed ? (window.pieceToBeUsed.n_pieza || window.pieceToBeUsed.n_juego) : null;
    
    const nowTime = new Date().toLocaleTimeString('it-IT');
    
    // REGLA DE ORO: PRIORIDAD A DATOS MANUALES (PARA RANGOS PERSONALIZADOS)
    // Si no hay customData ni pieceStartTime, usamos la hora actual del sistema en lugar de la meta completa
    let h_inicio = customData.h_inicio || window.currentPieceStartTime || nowTime;
    let h_termino = customData.h_termino || nowTime;

    let payload = {
        action: actionName,
        details: detailsStr,
        ot: document.querySelector('.workOrder')?.value || (window.arrayData ? window.arrayData.workOrder : null),
        clase: document.querySelector('.class')?.value || (window.arrayData ? window.arrayData.class : null),
        proceso: document.querySelector('.process')?.value || (window.arrayData ? window.arrayData.process : null),
        maquina: document.querySelector('.machine')?.value || (window.arrayData ? window.arrayData.machine : null),
        n_pieza: nPieza,
        h_inicio: h_inicio,
        h_termino: h_termino,
        id_ot: (window.arrayData && window.arrayData.meta) ? window.arrayData.meta.id_ot : null,
        id_clase: (window.arrayData && window.arrayData.meta) ? window.arrayData.meta.id_clase : null,
        _token: csrfToken
    };

    fetch(window.baseUrl + "/system-logs", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": csrfToken
        },
        body: JSON.stringify(payload)
    }).catch(err => console.error("Error logging action:", err));
};

// --- 2. SISTEMA DE ALERTAS PREMIUM (TOASTS) ---
window.showToastAlert = function(message, type = "success") {
    let container = document.getElementById("bottom-right-toasts");
    if (!container) {
        container = document.createElement("div");
        container.id = "bottom-right-toasts";
        container.style.cssText = "position: fixed; bottom: 20px; right: 20px; z-index: 9999; display: flex; flex-direction: column; gap: 10px; pointer-events: none;";
        document.body.appendChild(container);
    }

    const toast = document.createElement("div");
    const alertClass = type === "error" ? "alert-danger" : "alert-success";
    toast.className = `alert ${alertClass} custom-alert`;
    toast.style.cssText = "margin: 0; pointer-events: auto; transform: translateX(120%); transition: transform 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55), opacity 0.4s ease; box-shadow: 0 10px 25px rgba(0,0,0,0.2);";
    
    toast.innerHTML = `<button class="close-alert">&times;</button>${message}`;

    toast.querySelector('.close-alert').onclick = () => {
        toast.style.transform = "translateX(120%)";
        toast.style.opacity = "0";
        setTimeout(() => toast.remove(), 400);
    };

    container.appendChild(toast);
    requestAnimationFrame(() => toast.style.transform = "translateX(0)");

    if (type === "success") {
        setTimeout(() => {
            if (toast.parentElement) {
                toast.style.transform = "translateX(120%)";
                toast.style.opacity = "0";
                setTimeout(() => toast.remove(), 400);
            }
        }, 6000);
    }
};

// --- 3. MONITOREO DE PRODUCTIVIDAD (SERVER-SIDE) ---
document.addEventListener("DOMContentLoaded", () => {
    const profileInput = document.getElementById("profile");
    if (profileInput) {
        console.log("Productivity Monitor: Perfil detectado =", profileInput.value);
        if (profileInput.value == "2") {
            initProductivityPulse();
        }
    } else {
        console.warn("Productivity Monitor: No se encontró el input #profile");
    }
});

function initProductivityPulse() {
    if (window.productivityPulseInterval) clearInterval(window.productivityPulseInterval);
    syncProductivityWithServer();
    window.productivityPulseInterval = setInterval(syncProductivityWithServer, 20000);
}

function syncProductivityWithServer() {
    let currentStatus = 'none';
    let standardMin = 0;

    const btnNewReport = document.querySelector('.div-new-report');
    const formGrid = document.querySelector('.form-principal-data');
    const isMachining = window.arrayData && window.arrayData.meta;

    if (isMachining) {
        currentStatus = 'machining';
        standardMin = parseFloat(window.arrayData.meta.t_estandar) || 60;
    } else if (formGrid) {
        currentStatus = 'form';
    } else if (btnNewReport) {
        currentStatus = 'welcome';
    }

    // --- PROTECCIÓN ANTI-BLIP ---
    // Si estamos en una página conocida de producción/home pero no detectamos nada, 
    // evitamos enviar 'none' para no confundir al servidor momentáneamente.
    const isOperatorPage = window.location.pathname.includes('/home') || 
                           window.location.pathname.includes('/processProduction');
    
    if (isOperatorPage && currentStatus === 'none') {
        return; // Ignorar este latido hasta que cargue la UI
    }

    // No re-enviar 'none' si ya estamos en 'none' localmente para ahorrar tráfico
    if (currentStatus === 'none' && window.lastProductivityStatus === 'none') return;
    window.lastProductivityStatus = currentStatus;

    // Verificación de seguridad: si no hay token CSRF, no podemos hacer ping
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    if (!csrfMeta) return;

    fetch(window.baseUrl + "/productivity/ping", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": csrfMeta.getAttribute("content")
        },
        body: JSON.stringify({ status: currentStatus, standard_min: standardMin })
    })
    .then(response => response.json())
    .then(data => {
        if (data.locked) {
            renderGlobalProductivityAlert(data.locked, data.standard_min || standardMin);
        }
    })
    .catch(err => console.error("Productivity Sync Error:", err));
}

function renderGlobalProductivityAlert(type, standardMinutes = 0) {
    if (document.getElementById('productivity-lock-overlay')) return;
    
    // Si ya hay algo en persistencia, no pisamos el startTime
    const pendingModal = localStorage.getItem('pending_system_modal');
    let startTime = new Date().toLocaleTimeString('it-IT');
    if (pendingModal) {
        try {
            startTime = JSON.parse(pendingModal).startTime;
        } catch (e) {}
    }

    // MAPEO DE LUGARES PARA EL LOG
    const locationMapping = {
        'inicio': 'Pantalla de Bienvenida',
        'formulario': 'Configuración de Formulario',
        'produccion': 'Registro de Medidas (Maquinado)'
    };
    const lugar = locationMapping[type] || 'Sistema';

    const configs = {
        'inicio': {
            icon: 'Aviso.png',
            title: 'Inicio de Reporte Pendiente',
            message: 'Inicie el nuevo reporte para continuar operando.',
            type: 'notice'
        },
        'formulario': {
            icon: 'Sospechosa.png',
            title: 'Inactividad en Formulario',
            message: 'Complete el registro para continuar.',
            type: 'warning'
        },
        'produccion': {
            icon: 'Critica.png',
            title: 'Exceso de Tiempo de Maquinado',
            message: `El tiempo estándar para esta operación:<br> <strong style="color: #ff0000; font-size: 1.4em; display: block; margin: 0.3em 0;">(${displayTime(standardMinutes)})</strong> ha excedido el límite de seguridad permitido.`,
            type: 'error'
        }
    };

    function displayTime(minsTotal) {
        let display = `${minsTotal} min`;
        if (minsTotal >= 60) {
            const hours = Math.floor(minsTotal / 60);
            const mins = minsTotal % 60;
            display = mins > 0 ? `${hours} h ${mins} min` : `${hours} ${hours === 1 ? 'hora' : 'horas'}`;
        }
        return display;
    }

    const config = configs[type] || configs['produccion'];
    const originalOverflow = document.body.style.overflow;
    document.body.style.overflow = 'hidden';

    window.showSystemModal(config.title, config.message, {
        icon: config.icon,
        type: config.type,
        persist: true,
        startTime: startTime,
        productivityType: type, // Identificador para handleProductivityUnlock
        lugar: lugar,           // Nombre legible del lugar
        onConfirm: (finalStartTime) => {
            window.handleProductivityUnlock(config.title, finalStartTime, type, lugar);
            document.body.style.overflow = originalOverflow;
        }
    });
}

/**
 * Función global para procesar el desbloqueo y logging
 */
window.handleProductivityUnlock = function(alertTitle, startTime, alertType = null, lugar = 'Sistema') {
    const h_termino = new Date().toLocaleTimeString('it-IT');
    
    // Calcular tiempo de respuesta (tiempo transcurrido desde que salió la alerta hasta que se aceptó)
    const [h1, m1, s1] = startTime.split(':').map(Number);
    const [h2, m2, s2] = h_termino.split(':').map(Number);
    const date1 = new Date(0,0,0, h1, m1, s1);
    const date2 = new Date(0,0,0, h2, m2, s2);
    let diffMs = date2 - date1;
    if (diffMs < 0) diffMs += 24 * 60 * 60 * 1000; // Por si cruza medianoche
    
    const totalSeconds = Math.floor(diffMs / 1000);
    let timeTaken = "";
    
    if (totalSeconds < 60) {
        timeTaken = `${totalSeconds} seg`;
    } else if (totalSeconds < 3600) {
        const mins = Math.floor(totalSeconds / 60);
        const secs = totalSeconds % 60;
        timeTaken = secs > 0 ? `${mins} min ${secs} seg` : `${mins} min`;
    } else {
        const hours = Math.floor(totalSeconds / 3600);
        const mins = Math.floor((totalSeconds % 3600) / 60);
        timeTaken = mins > 0 ? `${hours} h ${mins} min` : `${hours} h`;
    }

    let details = "";
    const label = alertType === 'produccion' ? 'Exceso de Tiempo de Maquinado' : 'Inactividad';
    details = `El operador reconoció y aceptó la alerta de ${label} en ${lugar} tras ${timeTaken}.`;
    
    // Registrar el log con el tiempo REAL (desde que salió la alerta por primera vez)
    window.logUserAction("Proceso Correcto", details, {
        h_inicio: startTime,
        h_termino: h_termino
    });

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");
    if (!csrfToken) return;

    fetch(window.baseUrl + "/productivity/unlock", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": csrfToken
        }
    }).catch(err => console.error("Error al desbloquear servidor:", err));
};
