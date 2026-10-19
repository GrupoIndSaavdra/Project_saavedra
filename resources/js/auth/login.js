/**
 * Login Module Scripts
 * Grupo Industrial Saavedra
 * Manejo de toggle de contraseña y carrusel ultra-ligero de Planta Tecámac
 */

document.addEventListener('DOMContentLoaded', () => {
    // ══════════════════════════════════════════════════════════════════════════
    // 1. Mostrar / Ocultar Contraseña
    // ══════════════════════════════════════════════════════════════════════════
    const toggleBtn = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('form3Example4');
    const eyeIcon = document.getElementById('eye-icon');

    if (toggleBtn && passwordInput && eyeIcon) {
        toggleBtn.addEventListener('click', () => {
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.innerHTML =
                    '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';
                toggleBtn.classList.add('active');
            } else {
                passwordInput.type = 'password';
                eyeIcon.innerHTML =
                    '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
                toggleBtn.classList.remove('active');
            }
        });
    }

    // ══════════════════════════════════════════════════════════════════════════
    // 2. Carrusel Ultra-Ligero Planta Tecámac (Rotación cada 30s)
    // ══════════════════════════════════════════════════════════════════════════
    const images = window.loginCarouselImages || [];
    if (!images || images.length <= 1) return;

    let currentIndex = typeof window.loginInitialIndex === 'number' ? window.loginInitialIndex : 0;
    let activeImgNum = 1;
    let timer = null;
    let isUserActive = true;
    let idleTimeout = null;

    const img1 = document.getElementById('loginCarouselImg1');
    const img2 = document.getElementById('loginCarouselImg2');
    if (!img1 || !img2) return;

    const ROTATION_INTERVAL = 30000; // 30 segundos
    const IDLE_LIMIT = 120000;       // Pausar si no hay actividad en 2 minutos

    function getNextRandomIndex() {
        if (images.length === 1) return 0;
        let next = Math.floor(Math.random() * images.length);
        while (next === currentIndex && images.length > 1) {
            next = Math.floor(Math.random() * images.length);
        }
        return next;
    }

    function crossfadeNext() {
        // Pausar si la pestaña está oculta, sin foco o el usuario está inactivo
        if (document.hidden || !document.hasFocus() || !isUserActive) {
            return;
        }

        const nextIndex = getNextRandomIndex();
        currentIndex = nextIndex;
        const nextSrc = images[nextIndex];

        const currentImg = activeImgNum === 1 ? img1 : img2;
        const nextImg = activeImgNum === 1 ? img2 : img1;

        // Carga bajo demanda únicamente cuando toca el cambio
        let preload = new Image();
        preload.onload = function () {
            if (document.hidden || !isUserActive) {
                preload = null;
                return;
            }
            nextImg.src = nextSrc;
            nextImg.classList.add('active');
            currentImg.classList.remove('active');
            currentImg.classList.add('fading-out');

            setTimeout(() => {
                currentImg.classList.remove('fading-out');
                currentImg.removeAttribute('src'); // Liberar memoria GPU/RAM
                preload = null;
            }, 1300);

            activeImgNum = activeImgNum === 1 ? 2 : 1;
        };
        preload.src = nextSrc;
    }

    function startTimer() {
        if (!timer && !document.hidden && document.hasFocus() && isUserActive) {
            timer = setInterval(crossfadeNext, ROTATION_INTERVAL);
        }
    }

    function stopTimer() {
        if (timer) {
            clearInterval(timer);
            timer = null;
        }
    }

    // A. Page Visibility API: detener al cambiar de pestaña o minimizar
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            stopTimer();
        } else {
            startTimer();
        }
    });

    // B. Window Focus / Blur
    window.addEventListener('blur', stopTimer);
    window.addEventListener('focus', startTimer);

    // C. Detección de inactividad
    function registerActivity() {
        isUserActive = true;
        startTimer();
        clearTimeout(idleTimeout);
        idleTimeout = setTimeout(() => {
            isUserActive = false;
            stopTimer();
        }, IDLE_LIMIT);
    }

    ['mousemove', 'mousedown', 'keydown', 'scroll', 'touchstart'].forEach((evt) => {
        window.addEventListener(evt, registerActivity, { passive: true });
    });

    // Iniciar monitoreo
    registerActivity();
});
