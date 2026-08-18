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
    // 1. OBTENER BASE URL (Prioriza window.baseUrl definida en Blade)
    let baseUrl = window.baseUrl || (window.location.origin + window.location.pathname.split('/').slice(0, -1).join('/') + '/');
    if (!baseUrl.endsWith('/')) baseUrl += '/';
    
    // Si baseUrl parece terminar en el nombre del archivo (ej. login), subir un nivel
    if (baseUrl.includes('/login/')) baseUrl = baseUrl.replace('/login/', '/');
    
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
        <div class="alm-modal-content" style="background: #ffffff; width: 95vw; max-width: 600px; border-radius: 20px; border: 4px solid #0284c7; box-shadow: 0 25px 60px rgba(2, 132, 199, 0.25); display: flex; flex-direction: column; overflow: hidden; position: relative;">
            <div class="alm-modal-header" style="background: linear-gradient(135deg, #0369a1 0%, #0284c7 100%); padding: 2em 2.5em 1.5em; position: relative; text-align: center;">
                <h3 style="margin: 0; color: #fff; font-size: 1.8em; font-weight: 700; letter-spacing: 0.5px; text-shadow: 0 2px 4px rgba(0,0,0,0.15); font-family: 'Poppins', sans-serif;">
                    ${title}
                </h3>
            </div>
            <div class="alm-modal-body" style="background: #f8fafc; padding: 2.5em; font-family: 'Poppins', sans-serif; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 20px;">
                <div class="lock-icon-container" style="margin: 0;">
                    <img src="${baseUrl}images/${iconName}" class="lock-icon" alt="Aviso" style="width: 100px; height: 100px; filter: drop-shadow(0 4px 6px rgba(3, 57, 102, 0.2));">
                </div>
                <p class="lock-message" style="color: #475569; font-size: 1.25em; line-height: 1.6; margin: 0; font-weight: 500; padding: 0 1rem; text-align: center;">
                    ${message}
                </p>
                <div style="margin-top: 15px; width: 100%;">
                    <button class="btn-lock-understood" style="background: linear-gradient(135deg, #0369a1 0%, #0284c7 100%); border-radius: 50px; font-weight: 800; font-size: 1.1em; text-transform: uppercase; letter-spacing: 1.5px; padding: 16px 45px; border: none; color: #fff; cursor: pointer; transition: all 0.2s ease; box-shadow: 0 8px 24px rgba(3, 105, 161, 0.35); width: 100%; max-width: 320px;" onmouseover="this.style.filter='brightness(1.1)'; this.style.transform='translateY(-2px)';" onmouseout="this.style.filter='none'; this.style.transform='none';">
                        Aceptar y Continuar
                    </button>
                </div>
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