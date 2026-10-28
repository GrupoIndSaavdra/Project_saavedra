/**
 * cursorPagination.js
 * 
 * Controlador universal de navegación y carga dinámica por Cursor
 * (Keyset Pagination) para Project Saavedra.
 */

document.addEventListener('DOMContentLoaded', () => {
    initCursorPagination();
});

function initCursorPagination() {
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('.gis-pagination-loadmore');
        if (!btn || btn.disabled) return;

        e.preventDefault();

        const nextUrl = btn.dataset.nextUrl;
        const targetSelector = btn.dataset.target;
        if (!nextUrl || !targetSelector) return;

        const targetEl = document.querySelector(targetSelector);
        if (!targetEl) {
            console.warn('[cursorPagination] Elemento objetivo no encontrado:', targetSelector);
            return;
        }

        const textSpan = btn.querySelector('.gis-loadmore-text');
        const spinner = btn.querySelector('.gis-loadmore-spinner');

        // Estado de carga
        btn.disabled = true;
        btn.setAttribute('aria-busy', 'true');
        if (textSpan) textSpan.textContent = 'Cargando registros...';
        if (spinner) spinner.style.display = 'inline-block';

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        try {
            const response = await fetch(nextUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'text/html, application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`Error en el servidor (${response.status})`);
            }

            const contentType = response.headers.get('content-type') || '';

            if (contentType.includes('application/json')) {
                const data = await response.json();
                if (data.html) {
                    insertNodesWithAnimation(targetEl, data.html);
                }
                if (data.next_url) {
                    btn.dataset.nextUrl = data.next_url;
                } else {
                    btn.closest('.gis-cursor-loadmore-wrap')?.remove();
                }
            } else {
                // Si retorna HTML de la página completa, parseamos el contenido del target
                const htmlText = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(htmlText, 'text/html');

                const incomingTarget = doc.querySelector(targetSelector);
                if (incomingTarget) {
                    insertNodesWithAnimation(targetEl, incomingTarget.innerHTML);
                }

                const incomingBtn = doc.querySelector('.gis-pagination-loadmore');
                if (incomingBtn && incomingBtn.dataset.nextUrl) {
                    btn.dataset.nextUrl = incomingBtn.dataset.nextUrl;
                } else {
                    btn.closest('.gis-cursor-loadmore-wrap')?.remove();
                }
            }
        } catch (error) {
            console.error('[cursorPagination] Error al cargar siguientes registros:', error);
            if (typeof window.gisToast === 'function') {
                window.gisToast('No se pudieron cargar más registros. Intente nuevamente.', 'error');
            } else {
                alert('No se pudieron cargar más registros.');
            }
        } finally {
            if (btn && btn.isConnected) {
                btn.disabled = false;
                btn.removeAttribute('aria-busy');
                if (textSpan) textSpan.textContent = 'Cargar más registros';
                if (spinner) spinner.style.display = 'none';
            }
        }
    });
}

/**
 * Inserta nodos HTML con una suave transición visual.
 *
 * @param {HTMLElement} container
 * @param {string} html
 */
function insertNodesWithAnimation(container, html) {
    const temp = document.createElement(container.tagName.toLowerCase() === 'tbody' ? 'tbody' : 'div');
    temp.innerHTML = html;

    const children = Array.from(temp.children);
    children.forEach((child) => {
        child.classList.add('gis-cursor-item-enter');
        container.appendChild(child);
        requestAnimationFrame(() => {
            child.classList.remove('gis-cursor-item-enter');
        });
    });
}
