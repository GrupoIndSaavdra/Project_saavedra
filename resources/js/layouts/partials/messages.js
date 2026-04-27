/**
 * SISTEMA DE NOTIFICACIONES PREMIUM (TOASTPREMIUM) - GLOBAL
 * Captura alertas del servidor (.custom-alert) y las muestra con estilo Saavedra.
 */

document.addEventListener('DOMContentLoaded', function () {
    // 1. CAPTURADOR DE ALERTAS DEL SERVIDOR
    // Busca alertas estáticas generadas por messages.blade.php al cargar la página
    const backendAlerts = document.querySelectorAll(".custom-alert");
    backendAlerts.forEach(alert => {
        // Evitar procesar alertas que ya están en el contenedor premium (si esto se llamara varias veces)
        if (alert.closest('#toast-container-premium')) return;

        let type = "success";
        if (alert.classList.contains("alert-danger")) type = "error";
        else if (alert.classList.contains("alert-warning")) type = "warning";

        const msg = alert.innerText.trim();

        if (msg) {
            toastpremium(msg, type);
        }
        
        // La eliminamos del DOM
        alert.remove();
    });
});

/**
 * Función global para lanzar notificaciones premium
 * @param {string} message - Texto a mostrar
 * @param {string} type - 'success', 'error', 'warning'
 */
function toastpremium(message, type = "success") {
    let baseUrl = window.baseUrl || (window.location.origin + '/');
    if (!baseUrl.endsWith('/')) baseUrl += '/';
    
    // Contenedor global si no existe
    let container = document.getElementById('toast-container-premium');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container-premium';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    const alertTypeClass = type === 'error' ? 'alert-danger' : (type === 'warning' ? 'alert-warning' : 'alert-success');
    toast.className = `alert ${alertTypeClass} custom-alert-saavedra ${type}`;

    // Selección de imagen según el tipo
    let iconSrc = baseUrl + 'images/ready.png';
    if (type === 'error') iconSrc = baseUrl + 'images/error.png';
    if (type === 'warning') iconSrc = baseUrl + 'images/Aviso.png';

    toast.innerHTML = `
        <img src="${iconSrc}" class="alert-icon-small" alt="${type}">
        <div class="alert-content">
            ${message}
        </div>
        <button class="close-alert-x">&times;</button>
    `;

    container.appendChild(toast);

    // Botón cerrar manual
    const closeBtn = toast.querySelector('.close-alert-x');
    closeBtn.onclick = () => {
        toast.remove();
    };

    // Lógica de Registro en Bitácora (si existe la función logUserAction)
    if (typeof window.logUserAction === 'function') {
        if (type === 'error') {
            window.logUserAction("Alerta de Error en Sistema", `Se mostró mensaje al operador: ${message}`);
        } else if (type === 'warning') {
            window.logUserAction("Avisos de Sistema", `Se mostró aviso al operador: ${message}`);
        }
    }

    // Auto-cierre para success y warning (10 segundos)
    if (type !== 'error') {
        setTimeout(() => {
            if (toast.parentElement) {
                toast.remove();
            }
        }, 10000);
    }
}

/**
 * Función global para lanzar un modal de bloqueo del sistema (Aviso Premium)
 * @param {string} title - Título llamativo
 * @param {string} message - Mensaje detallado
 * @param {Object} options - { onConfirm, icon: 'Aviso.png' }
 */
window.showSystemModal = function(title, message, options = {}) {
    let baseUrl = window.baseUrl || (window.location.origin + '/');
    if (!baseUrl.endsWith('/')) baseUrl += '/';
    
    const onConfirm = options.onConfirm || null;
    const iconName = options.icon || 'Aviso.png';
    const type = options.type || 'info'; 
    const persist = options.persist || false;
    const startTime = options.startTime || new Date().toLocaleTimeString('it-IT');
    
    // Evitar duplicados
    if (document.querySelector('.productivity-lock-overlay')) return;

    // Si es persistente, guardamos en localStorage
    if (persist) {
        localStorage.setItem('pending_system_modal', JSON.stringify({
            title, 
            message, 
            iconName, 
            type, 
            startTime,
            productivityType: options.productivityType || null,
            lugar: options.lugar || 'Sistema'
        }));
    }

    const overlay = document.createElement('div');
    overlay.className = 'productivity-lock-overlay';
    overlay.id = 'productivity-lock-overlay';
    
    overlay.innerHTML = `
        <div class="productivity-premium-modal ${type}">
            <div class="lock-icon-container">
                <img src="${baseUrl}images/${iconName}" class="lock-icon" alt="Aviso">
            </div>
            <h2 class="lock-title">${title}</h2>
            <p class="lock-message">${message}</p>
            <div style="padding-bottom: 3em;">
                <button class="btn-lock-understood">Aceptar y Continuar</button>
            </div>
        </div>
    `;
    
    document.body.appendChild(overlay);
    
    const btn = overlay.querySelector('.btn-lock-understood');
    btn.onclick = () => {
        // Al confirmar, limpiamos la persistencia
        const stored = JSON.parse(localStorage.getItem('pending_system_modal') || '{}');
        localStorage.removeItem('pending_system_modal');
        overlay.remove();
        
        if (typeof onConfirm === 'function') {
            onConfirm(startTime);
        } else if (typeof window.handleProductivityUnlock === 'function') {
            // Usar los datos recuperados de la persistencia
            window.handleProductivityUnlock(title, startTime, stored.productivityType, stored.lugar);
        }
    };
};

// AUTO-RESTAURACIÓN DE MODALES PENDIENTES
document.addEventListener('DOMContentLoaded', function() {
    const pendingModal = localStorage.getItem('pending_system_modal');
    if (pendingModal) {
        try {
            const data = JSON.parse(pendingModal);
            setTimeout(() => {
                window.showSystemModal(data.title, data.message, {
                    icon: data.iconName,
                    type: data.type,
                    startTime: data.startTime,
                    productivityType: data.productivityType,
                    lugar: data.lugar,
                    persist: true
                });
            }, 500);
        } catch (e) {
            console.error("Error al restaurar modal:", e);
            localStorage.removeItem('pending_system_modal');
        }
    }
});

window.toastpremium = toastpremium;