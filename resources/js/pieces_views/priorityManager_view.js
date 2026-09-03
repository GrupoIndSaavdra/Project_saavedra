// ══════════════════════════════════════════════════════════════════
// priorityManager_view.js
//
// Panel de Consulta de Prioridades de Órdenes de Trabajo (Vista de lectura).
// ══════════════════════════════════════════════════════════════════

document.addEventListener('DOMContentLoaded', () => {

    // ── Referencias DOM ──────────────────────────────────────────
    const list       = document.getElementById('pm-list');
    const countLabel = document.getElementById('pm-count-label');

    // ── Estado ───────────────────────────────────────────────────
    let items = Array.isArray(window.otPriorities) ? [...window.otPriorities] : [];

    // ── Render inicial ───────────────────────────────────────────
    renderList();

    // ══════════════════════════════════════════════════════════════
    // RENDER
    // ══════════════════════════════════════════════════════════════

    function renderList() {
        list.innerHTML = '';

        if (items.length === 0) {
            renderEmptyState();
            if (countLabel) countLabel.textContent = '';
            return;
        }

        const total = items.length;
        if (countLabel) {
            countLabel.innerHTML = `Hay <strong>${total}</strong> orden${total !== 1 ? 'es' : ''} de trabajo en progreso.`;
        }

        items.forEach((ot, index) => {
            const li = buildCard(ot, index, total);
            list.appendChild(li);
        });
    }

    function buildCard(ot, index, total) {
        const li = document.createElement('li');
        li.className = 'pm-card';
        li.dataset.otId  = ot.ot_id;
        li.dataset.index = index;
        li.draggable = false;
        li.style.cursor = 'default';

        const prioridad = index + 1;

        // — Badge de prioridad —
        const badge = document.createElement('div');
        badge.className = 'pm-priority-badge';
        badge.setAttribute('aria-label', `Prioridad ${prioridad}`);

        let imgPath = "";
        if (prioridad === 1) imgPath = `${window.baseUrl}/images/uno.png`;
        else if (prioridad === 2) imgPath = `${window.baseUrl}/images/dos.png`;
        else if (prioridad === 3) imgPath = `${window.baseUrl}/images/tres.png`;
        else if (prioridad === 4) imgPath = `${window.baseUrl}/images/cuatro.png`;
        else if (prioridad === 5) imgPath = `${window.baseUrl}/images/cinco.png`;
        else {
            imgPath = `${window.baseUrl}/images/plata.png`;
        }

        badge.innerHTML = `
            <span class="pm-priority-num">${prioridad}</span>
            <img src="${imgPath}" alt="Prioridad ${prioridad}" class="pm-priority-img">
        `;

        // — Cuerpo de la tarjeta —
        const body = document.createElement('div');
        body.className = 'pm-card-body';

        const top = document.createElement('div');
        top.className = 'pm-card-top';

        const otIdEl = document.createElement('span');
        otIdEl.className = 'pm-ot-id';
        otIdEl.textContent = `OT ${ot.ot_id}`;

        const molduraEl = document.createElement('span');
        molduraEl.className = 'pm-moldura-name';
        molduraEl.textContent = ot.moldura || '—';
        molduraEl.title = ot.moldura || '—';

        top.appendChild(otIdEl);
        top.appendChild(molduraEl);

        const clasesEl = document.createElement('div');
        clasesEl.className = 'pm-clases-list';

        const clases = ot.clases || [];
        if (clases.length > 0) {
            clases.forEach(clase => {
                const chip = document.createElement('span');
                chip.className = 'pm-clase-chip';
                chip.textContent = clase;
                clasesEl.appendChild(chip);
            });
        } else {
            const noClase = document.createElement('span');
            noClase.className = 'pm-clase-chip';
            noClase.style.opacity = '0.5';
            noClase.textContent = 'Sin clases activas';
            clasesEl.appendChild(noClase);
        }

        body.appendChild(top);
        body.appendChild(clasesEl);

        li.appendChild(badge);
        li.appendChild(body);

        return li;
    }

    function renderEmptyState() {
        const empty = document.createElement('div');
        empty.className = 'pm-empty';
        empty.innerHTML = `
            <svg class="pm-empty-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/>
                <rect x="9" y="3" width="6" height="4" rx="1"/>
                <line x1="9" y1="12" x2="15" y2="12"/>
                <line x1="9" y1="16" x2="15" y2="16"/>
            </svg>
            <h3>No hay órdenes de trabajo en progreso</h3>
            <p>Cuando existan clases activas en órdenes de trabajo, aparecerán aquí ordenadas por prioridad.</p>
        `;
        list.appendChild(empty);
    }

});
