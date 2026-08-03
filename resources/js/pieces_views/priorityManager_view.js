// ══════════════════════════════════════════════════════════════════
// priorityManager_view.js
//
// Panel de Gestión de Prioridades de Órdenes de Trabajo (Auto-guardado).
// ══════════════════════════════════════════════════════════════════

document.addEventListener('DOMContentLoaded', () => {

    // ── Referencias DOM ──────────────────────────────────────────
    const list        = document.getElementById('pm-list');
    const saveBtn     = document.getElementById('pm-save-btn'); // Funciona como indicador de estado
    const countLabel  = document.getElementById('pm-count-label');
    const toast       = document.getElementById('pm-toast');

    // ── Estado ───────────────────────────────────────────────────
    let items = Array.isArray(window.otPriorities) ? [...window.otPriorities] : [];
    let dragSrcIndex = null;
    let isSaving = false;

    // ── Render inicial ───────────────────────────────────────────
    renderList();

    // ══════════════════════════════════════════════════════════════
    // RENDER
    // ══════════════════════════════════════════════════════════════

    function renderList() {
        list.innerHTML = '';

        if (items.length === 0) {
            renderEmptyState();
            countLabel.textContent = '';
            updateStatusIndicator('empty');
            return;
        }

        const total = items.length;
        countLabel.innerHTML = `Hay <strong>${total}</strong> orden${total !== 1 ? 'es' : ''} de trabajo en progreso.`;

        items.forEach((ot, index) => {
            const li = buildCard(ot, index);
            list.appendChild(li);
        });
    }

    function buildCard(ot, index) {
        const li = document.createElement('li');
        li.className = 'pm-card';
        li.dataset.otId  = ot.ot_id;
        li.dataset.index = index;
        li.draggable = true;

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

        // — Handle de arrastre —
        const handle = document.createElement('div');
        handle.className = 'pm-drag-handle';
        handle.setAttribute('aria-hidden', 'true');
        handle.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2.5"
                 stroke-linecap="round" stroke-linejoin="round">
                <circle cx="9" cy="5" r="1" fill="currentColor" stroke="none"/>
                <circle cx="9" cy="12" r="1" fill="currentColor" stroke="none"/>
                <circle cx="9" cy="19" r="1" fill="currentColor" stroke="none"/>
                <circle cx="15" cy="5" r="1" fill="currentColor" stroke="none"/>
                <circle cx="15" cy="12" r="1" fill="currentColor" stroke="none"/>
                <circle cx="15" cy="19" r="1" fill="currentColor" stroke="none"/>
            </svg>
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
        li.appendChild(handle);

        // ── Eventos de Drag-and-Drop ─────────────────────────────
        li.addEventListener('dragstart', onDragStart);
        li.addEventListener('dragover',  onDragOver);
        li.addEventListener('dragenter', onDragEnter);
        li.addEventListener('dragleave', onDragLeave);
        li.addEventListener('drop',      onDrop);
        li.addEventListener('dragend',   onDragEnd);

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
            <p>Cuando existan clases activas en órdenes de trabajo, aparecerán aquí para poder ordenarlas por prioridad.</p>
        `;
        list.appendChild(empty);
    }

    // ══════════════════════════════════════════════════════════════
    // DRAG-AND-DROP — Lógica nativa HTML5
    // ══════════════════════════════════════════════════════════════

    function onDragStart(e) {
        dragSrcIndex = parseInt(e.currentTarget.dataset.index, 10);
        e.currentTarget.classList.add('is-dragging');

        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', dragSrcIndex);
    }

    function onDragOver(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
    }

    function onDragEnter(e) {
        e.preventDefault();
        const card = e.currentTarget;
        if (parseInt(card.dataset.index, 10) !== dragSrcIndex) {
            card.classList.add('drag-over');
        }
    }

    function onDragLeave(e) {
        e.currentTarget.classList.remove('drag-over');
    }

    function onDrop(e) {
        e.preventDefault();
        e.stopPropagation();

        const targetIndex = parseInt(e.currentTarget.dataset.index, 10);
        e.currentTarget.classList.remove('drag-over');

        if (dragSrcIndex === null || dragSrcIndex === targetIndex) return;

        // Reordenar el array en memoria
        const moved = items.splice(dragSrcIndex, 1)[0];
        items.splice(targetIndex, 0, moved);

        // Re-renderizar lista
        renderList();

        // Guardar automáticamente
        autoSavePriorities();
    }

    function onDragEnd(e) {
        document.querySelectorAll('.pm-card').forEach(card => {
            card.classList.remove('is-dragging', 'drag-over');
        });
        dragSrcIndex = null;
    }

    // ══════════════════════════════════════════════════════════════
    // AUTO-GUARDAR
    // ══════════════════════════════════════════════════════════════

    async function autoSavePriorities() {
        if (isSaving) return;
        isSaving = true;

        updateStatusIndicator('saving');

        const priorities = items.map((ot, index) => ({
            ot_id:    ot.ot_id,
            prioridad: index + 1,
        }));

        try {
            const res = await fetch(window.savePrioritiesUrl, {
                method: 'POST',
                headers: {
                    'Content-Type':     'application/json',
                    'Accept':           'application/json',
                    'X-CSRF-TOKEN':     window.csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ priorities }),
            });

            const data = await res.json();

            if (res.ok && data.success) {
                items = items.map((ot, index) => ({
                    ...ot,
                    prioridad: index + 1,
                }));
                updateStatusIndicator('saved');
                showToast('✓ Prioridades guardadas automáticamente.', 'success');
            } else {
                updateStatusIndicator('error');
                showToast('✗ No se pudieron guardar las prioridades.', 'error');
            }
        } catch (err) {
            console.error('[PriorityManager] Error de red:', err);
            updateStatusIndicator('error');
            showToast('✗ Error de conexión. Cambios no guardados.', 'error');
        } finally {
            isSaving = false;
        }
    }

    function updateStatusIndicator(state) {
        if (!saveBtn) return;

        const spinner = saveBtn.querySelector('.pm-spinner');
        const textSpan = saveBtn.querySelector('.pm-btn-text');

        if (state === 'saving') {
            saveBtn.className = 'pm-save-btn pm-status-saving';
            if (spinner) spinner.classList.remove("hidden");
            if (textSpan) textSpan.textContent = 'Guardando...';
        } else if (state === 'saved') {
            saveBtn.className = 'pm-save-btn pm-status-saved';
            if (spinner) spinner.classList.add("hidden");
            if (textSpan) textSpan.textContent = '✓ Guardado';
        } else if (state === 'error') {
            saveBtn.className = 'pm-save-btn pm-status-error';
            if (spinner) spinner.classList.add("hidden");
            if (textSpan) textSpan.textContent = '⚠ Error al guardar';
        } else {
            saveBtn.className = 'pm-save-btn';
            if (spinner) spinner.classList.add("hidden");
            if (textSpan) textSpan.textContent = 'Autoguardado Activo';
        }
    }

    // ══════════════════════════════════════════════════════════════
    // TOAST
    // ══════════════════════════════════════════════════════════════

    let toastTimer = null;

    function showToast(message, type = 'success') {
        if (toastTimer) {
            clearTimeout(toastTimer);
            toast.classList.remove('is-visible');
        }

        toast.textContent = message;
        toast.className = `pm-toast pm-toast--${type}`;

        void toast.offsetWidth;
        toast.classList.add('is-visible');

        toastTimer = setTimeout(() => {
            toast.classList.remove('is-visible');
            toastTimer = null;
        }, 3000);
    }

});
