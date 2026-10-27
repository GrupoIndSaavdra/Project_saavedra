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
 * Función global para lanzar notificaciones premium (Estándar SweetAlert2 GIS)
 * @param {string} message - Texto a mostrar
 * @param {string} type - 'success', 'error', 'warning', 'info'
 */
function toastpremium(message, type = "success") {
    // Normalizar tipo de alerta
    let swalIcon = type;
    if (type === 'danger') swalIcon = 'error';

    // Lógica de Registro en Bitácora (si existe la función logUserAction)
    if (typeof window.logUserAction === 'function') {
        if (swalIcon === 'error') {
            window.logUserAction("Alerta de Error en Sistema", `Se mostró mensaje al operador: ${message}`);
        } else if (swalIcon === 'warning') {
            window.logUserAction("Avisos de Sistema", `Se mostró aviso al operador: ${message}`);
        }
    }

    // Estándar 1: SweetAlert2 Toast Ultra-Fluido
    if (typeof Swal !== 'undefined') {
        Swal.mixin({
            toast: true,
            position: 'bottom-end',
            showConfirmButton: false,
            timer: 3200,
            timerProgressBar: true,
            showClass: {
                popup: 'swal2-toast-in',
            },
            hideClass: {
                popup: 'swal2-toast-out',
            },
        }).fire({
            icon: swalIcon,
            title: message,
        });
        return;
    }

    // Estándar 2: Fallback nativo Saavedra si SweetAlert2 no estuviera en memoria
    let baseUrl = window.baseUrl || (window.location.origin + window.location.pathname.split('/').slice(0, -1).join('/') + '/');
    if (!baseUrl.endsWith('/')) baseUrl += '/';
    if (baseUrl.includes('/login/')) baseUrl = baseUrl.replace('/login/', '/');

    let container = document.getElementById('toast-container-premium');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container-premium';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    const alertTypeClass = swalIcon === 'error' ? 'alert-danger' : (swalIcon === 'warning' ? 'alert-warning' : 'alert-success');
    toast.className = `alert ${alertTypeClass} custom-alert-saavedra ${swalIcon}`;

    let iconSrc = baseUrl + 'images/Color_Icons/Generales/ready.png';
    if (swalIcon === 'error') iconSrc = baseUrl + 'images/Color_Icons/Generales/error.png';
    if (swalIcon === 'warning') iconSrc = baseUrl + 'images/Color_Icons/Generales/Aviso.png';

    toast.innerHTML = `
        <img src="${iconSrc}" class="alert-icon-small" alt="${swalIcon}">
        <div class="alert-content">
            ${message}
        </div>
        <button class="close-alert-x">&times;</button>
    `;

    container.appendChild(toast);

    const closeBtn = toast.querySelector('.close-alert-x');
    if (closeBtn) closeBtn.onclick = () => toast.remove();

    if (swalIcon !== 'error') {
        setTimeout(() => {
            if (toast.parentElement) toast.remove();
        }, 5000);
    }
}

/**
 * Función global para lanzar un modal de bloqueo del sistema (Aviso Premium)
 * @param {string} title - Título llamativo
 * @param {string} message - Mensaje detallado
 * @param {Object} options - { onConfirm, icon: 'Color_Icons/Generales/Aviso.png' }
 */
window.showSystemModal = function(title, message, options = {}) {
    let baseUrl = window.baseUrl || (window.location.origin + '/');
    if (!baseUrl.endsWith('/')) baseUrl += '/';
    
    const onConfirm = options.onConfirm || null;
    const iconName = options.icon || 'Color_Icons/Generales/Aviso.png';
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
window.gisToast = toastpremium;
window.mostrarNotificacion = toastpremium;

// Interceptar alert nativo para que NUNCA aparezca el popup feo del navegador
window.alert = function (message) {
    if (typeof toastpremium === 'function') {
        toastpremium(String(message ?? ''), 'warning');
    } else {
        console.warn('[GIS Alert Interceptado]:', message);
    }
};

/**
 * Alerta Modal Estándar GIS (Reemplazo moderno de alert-NoCotas / alert nativo)
 * Soporta firma por objeto: gisAlert({ title, text, icon, confirmButtonText, confirmButtonColor })
 * o firma por parámetros: gisAlert(message, title, icon)
 */
window.gisAlert = function (arg1, arg2 = '', arg3 = 'info') {
    let title = 'Aviso';
    let text = '';
    let icon = 'info';
    let confirmButtonText = 'Entendido';
    let confirmButtonColor = '#033966';

    if (typeof arg1 === 'object' && arg1 !== null) {
        title = arg1.title || 'Aviso';
        text = arg1.text || '';
        icon = arg1.icon || 'info';
        confirmButtonText = arg1.confirmButtonText || 'Entendido';
        confirmButtonColor = arg1.confirmButtonColor || (icon === 'error' ? '#9c0303' : '#033966');
    } else {
        text = String(arg1 ?? '');
        title = arg2 || (arg3 === 'error' ? 'Error' : (arg3 === 'warning' ? 'Atención' : 'Aviso'));
        icon = arg3 || 'info';
        confirmButtonColor = icon === 'error' ? '#9c0303' : '#033966';
    }

    if (typeof Swal !== 'undefined') {
        return Swal.fire({
            title,
            text,
            icon,
            confirmButtonText,
            confirmButtonColor,
            backdrop: 'rgba(0, 10, 25, 0.85)',
            customClass: { popup: 'swal2-gis-popup' }
        });
    }

    // Fallback con ToastPremium o DOM modal Saavedra si SweetAlert no estuviera listo
    if (typeof toastpremium === 'function') {
        toastpremium(`${title}: ${text}`, icon === 'error' ? 'error' : (icon === 'warning' ? 'warning' : 'info'));
    }
    return Promise.resolve({ isConfirmed: true });
};

/**
 * Diálogo de Confirmación Estándar GIS (Reemplazo moderno de confirm() nativo)
 * Soporta firma por objeto: gisConfirm({ title, text, icon, confirmButtonText, confirmButtonColor, cancelButtonText, cancelButtonColor, reverseButtons })
 * o firma por texto: gisConfirm(message, title)
 */
window.gisConfirm = async function (arg1, arg2 = '¿Estás seguro?') {
    let title = '¿Estás seguro?';
    let text = '';
    let icon = 'warning';
    let confirmButtonText = 'Aceptar';
    let cancelButtonText = 'Cancelar';
    let confirmButtonColor = '#b30404'; // Rojo Peligro Saavedra por defecto
    let cancelButtonColor = '#030041';  // Azul Marino Saavedra
    let reverseButtons = true;          // Cancelar a la izquierda, Confirmar a la derecha
    let preConfirm = null;

    if (typeof arg1 === 'object' && arg1 !== null) {
        title = arg1.title || '¿Estás seguro?';
        text = arg1.text || '';
        icon = arg1.icon || 'warning';
        confirmButtonText = arg1.confirmButtonText || (icon === 'warning' || icon === 'error' ? 'Sí, continuar' : 'Aceptar');
        cancelButtonText = arg1.cancelButtonText || 'Cancelar';
        confirmButtonColor = arg1.confirmButtonColor || (icon === 'success' ? '#0a8504' : '#b30404');
        cancelButtonColor = arg1.cancelButtonColor || '#030041';
        reverseButtons = arg1.reverseButtons !== undefined ? arg1.reverseButtons : true;
        preConfirm = arg1.preConfirm || null;
    } else {
        text = String(arg1 ?? '');
        title = arg2 || '¿Estás seguro?';
        confirmButtonText = 'Sí, continuar';
    }

    if (typeof Swal !== 'undefined') {
        const options = {
            title,
            text,
            icon,
            showCancelButton: true,
            confirmButtonColor,
            cancelButtonColor,
            confirmButtonText,
            cancelButtonText,
            reverseButtons,
            backdrop: 'rgba(0, 10, 25, 0.85)',
            customClass: { popup: 'swal2-gis-popup' }
        };
        if (preConfirm) {
            options.showLoaderOnConfirm = true;
            options.preConfirm = preConfirm;
            options.allowOutsideClick = () => !Swal.isLoading();
        }
        return await Swal.fire(options);
    }

    // Fallback DOM Saavedra si SweetAlert2 estuviera temporalmente inaccesible (NUNCA confirm nativo)
    return new Promise((resolve) => {
        let baseUrl = window.baseUrl || (window.location.origin + '/');
        if (!baseUrl.endsWith('/')) baseUrl += '/';

        const overlay = document.createElement('div');
        overlay.className = 'productivity-lock-overlay';
        overlay.style.zIndex = '100050';
        overlay.innerHTML = `
            <div class="alm-modal-content" style="background: #ffffff; width: 95vw; max-width: 520px; border-radius: 20px; border: 4px solid #033966; box-shadow: 0 25px 60px rgba(0, 10, 25, 0.45); display: flex; flex-direction: column; overflow: hidden;">
                <div style="background: linear-gradient(135deg, #033966 0%, #0284c7 100%); padding: 1.5em; text-align: center;">
                    <h3 style="margin: 0; color: #fff; font-size: 1.4em; font-weight: 700; font-family: 'Poppins', sans-serif;">
                        ${title}
                    </h3>
                </div>
                <div style="background: #f8fafc; padding: 2em; font-family: 'Poppins', sans-serif; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 15px;">
                    <img src="${baseUrl}images/Color_Icons/Generales/Aviso.png" alt="Aviso" style="width: 70px; height: 70px; filter: drop-shadow(0 4px 6px rgba(3, 57, 102, 0.2));">
                    <p style="color: #475569; font-size: 1.1em; line-height: 1.5; margin: 0; font-weight: 500;">
                        ${text}
                    </p>
                    <div style="margin-top: 15px; display: flex; gap: 12px; justify-content: center; width: 100%;">
                        <button class="btn-gis-cancel" style="background: #030041; border-radius: 10px; font-weight: 700; font-size: 0.95rem; padding: 10px 24px; border: none; color: #fff; cursor: pointer;">
                            ${cancelButtonText}
                        </button>
                        <button class="btn-gis-confirm" style="background: ${confirmButtonColor}; border-radius: 10px; font-weight: 700; font-size: 0.95rem; padding: 10px 24px; border: none; color: #fff; cursor: pointer;">
                            ${confirmButtonText}
                        </button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(overlay);

        const btnCancel = overlay.querySelector('.btn-gis-cancel');
        const btnConfirm = overlay.querySelector('.btn-gis-confirm');

        btnCancel.onclick = () => {
            overlay.remove();
            resolve({ isConfirmed: false, isDismissed: true });
        };
        btnConfirm.onclick = async () => {
            if (preConfirm) {
                try {
                    const res = await preConfirm();
                    overlay.remove();
                    resolve({ isConfirmed: true, value: res });
                } catch (e) {
                    // preConfirm failed
                }
            } else {
                overlay.remove();
                resolve({ isConfirmed: true });
            }
        };
    });
};

window.confirmAsync = window.gisConfirm;