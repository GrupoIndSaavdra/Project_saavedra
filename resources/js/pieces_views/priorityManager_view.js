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
    let savePending = false;

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
            const li = buildCard(ot, index, total);
            list.appendChild(li);
        });
    }

    function buildCard(ot, index, total) {
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

        // — Contenedor de Acciones Rápidas (Posición / Subir / Bajar / Arrastrar) —
        const actions = document.createElement('div');
        actions.className = 'pm-card-actions';

        // 1. Selector rápido de posición directa
        const posPicker = document.createElement('div');
        posPicker.className = 'pm-pos-picker';
        posPicker.title = 'Mover directamente a una posición';

        const posLabel = document.createElement('span');
        posLabel.className = 'pm-pos-label';
        posLabel.textContent = 'Posición:';

        const posSelect = document.createElement('select');
        posSelect.className = 'pm-pos-select';
        posSelect.setAttribute('aria-label', `Posición de OT ${ot.ot_id}`);

        for (let p = 1; p <= total; p++) {
            const opt = document.createElement('option');
            opt.value = p - 1; // 0-indexed destination
            opt.text = `#${p}`;
            if (p === prioridad) {
                opt.selected = true;
            }
            posSelect.appendChild(opt);
        }

        posSelect.addEventListener('change', (e) => {
            const targetPos = parseInt(e.target.value, 10);
            moveItem(index, targetPos);
        });

        posPicker.appendChild(posLabel);
        posPicker.appendChild(posSelect);

        // 2. Botones de Mover Arriba / Abajo
        const btnsGroup = document.createElement('div');
        btnsGroup.className = 'pm-btns-move-group';

        const btnUp = document.createElement('button');
        btnUp.type = 'button';
        btnUp.className = 'pm-btn-move';
        btnUp.innerHTML = '▲';
        btnUp.title = 'Subir 1 posición';
        btnUp.disabled = (index === 0);
        btnUp.addEventListener('click', (e) => {
            e.stopPropagation();
            moveItem(index, index - 1);
        });

        const btnDown = document.createElement('button');
        btnDown.type = 'button';
        btnDown.className = 'pm-btn-move';
        btnDown.innerHTML = '▼';
        btnDown.title = 'Bajar 1 posición';
        btnDown.disabled = (index === total - 1);
        btnDown.addEventListener('click', (e) => {
            e.stopPropagation();
            moveItem(index, index + 1);
        });

        btnsGroup.appendChild(btnUp);
        btnsGroup.appendChild(btnDown);

        // 3. Handle de arrastre lateral
        const handle = document.createElement('div');
        handle.className = 'pm-drag-handle';
        handle.setAttribute('role', 'button');
        handle.setAttribute('tabindex', '0');
        handle.title = 'Sujeta y arrastra aquí para mover la OT libremente';
        handle.innerHTML = `
            <span class="pm-drag-handle-text">Arrastrar</span>
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2.5"
                 stroke-linecap="round" stroke-linejoin="round">
                <circle cx="9" cy="5" r="1.5" fill="currentColor" stroke="none"/>
                <circle cx="9" cy="12" r="1.5" fill="currentColor" stroke="none"/>
                <circle cx="9" cy="19" r="1.5" fill="currentColor" stroke="none"/>
                <circle cx="15" cy="5" r="1.5" fill="currentColor" stroke="none"/>
                <circle cx="15" cy="12" r="1.5" fill="currentColor" stroke="none"/>
                <circle cx="15" cy="19" r="1.5" fill="currentColor" stroke="none"/>
            </svg>
        `;

        // Activar arrastre al interactuar con el mango lateral
        handle.addEventListener('mousedown', () => {
            li.draggable = true;
        });
        handle.addEventListener('touchstart', () => {
            li.draggable = true;
        }, { passive: true });

        actions.appendChild(posPicker);
        actions.appendChild(btnsGroup);
        actions.appendChild(handle);

        li.appendChild(badge);
        li.appendChild(body);
        li.appendChild(actions);

        // ── Eventos de Drag-and-Drop ─────────────────────────────
        li.addEventListener('dragstart', onDragStart);
        li.addEventListener('dragover',  onDragOver);
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
    // MOVER ITEMS (Reordenar y Autoguardar)
    // ══════════════════════════════════════════════════════════════

    function moveItem(fromIndex, toIndex) {
        if (fromIndex < 0 || fromIndex >= items.length) return;
        if (toIndex < 0 || toIndex >= items.length) return;
        if (fromIndex === toIndex) return;

        const [moved] = items.splice(fromIndex, 1);
        items.splice(toIndex, 0, moved);

        renderList();
        autoSavePriorities();
    }

    // ══════════════════════════════════════════════════════════════
    // DRAG-AND-DROP — Lógica fluida con indicador superior / inferior
    // ══════════════════════════════════════════════════════════════

    function onDragStart(e) {
        const card = e.currentTarget;
        dragSrcIndex = parseInt(card.dataset.index, 10);
        card.classList.add('is-dragging');

        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', String(dragSrcIndex));
    }

    function onDragOver(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';

        const card = e.currentTarget;
        const targetIndex = parseInt(card.dataset.index, 10);

        if (targetIndex === dragSrcIndex) {
            card.classList.remove('drag-over-top', 'drag-over-bottom');
            return;
        }

        const rect = card.getBoundingClientRect();
        const isBottom = e.clientY > (rect.top + rect.height / 2);

        if (isBottom) {
            card.classList.remove('drag-over-top');
            card.classList.add('drag-over-bottom');
        } else {
            card.classList.remove('drag-over-bottom');
            card.classList.add('drag-over-top');
        }
    }

    function onDragLeave(e) {
        const card = e.currentTarget;
        const rect = card.getBoundingClientRect();

        // Verificar si el cursor realmente salió de los límites de la tarjeta
        if (
            e.clientX < rect.left ||
            e.clientX >= rect.right ||
            e.clientY < rect.top ||
            e.clientY >= rect.bottom
        ) {
            card.classList.remove('drag-over-top', 'drag-over-bottom');
        }
    }

    function onDrop(e) {
        e.preventDefault();
        e.stopPropagation();

        const card = e.currentTarget;
        const targetIndex = parseInt(card.dataset.index, 10);
        card.classList.remove('drag-over-top', 'drag-over-bottom');

        if (dragSrcIndex === null || dragSrcIndex === targetIndex) return;

        const rect = card.getBoundingClientRect();
        const isBottom = e.clientY > (rect.top + rect.height / 2);

        // Extraer elemento movido
        const [movedItem] = items.splice(dragSrcIndex, 1);

        // Calcular índice exacto de inserción
        let insertIndex = targetIndex;
        if (dragSrcIndex < targetIndex) {
            insertIndex = isBottom ? targetIndex : targetIndex - 1;
        } else {
            insertIndex = isBottom ? targetIndex + 1 : targetIndex;
        }

        insertIndex = Math.max(0, Math.min(insertIndex, items.length));
        items.splice(insertIndex, 0, movedItem);

        dragSrcIndex = null;
        renderList();
        autoSavePriorities();
    }

    function onDragEnd(e) {
        document.querySelectorAll('.pm-card').forEach(card => {
            card.classList.remove('is-dragging', 'drag-over-top', 'drag-over-bottom');
        });
        dragSrcIndex = null;
    }

    // ══════════════════════════════════════════════════════════════
    // AUTO-GUARDAR
    // ══════════════════════════════════════════════════════════════

    async function autoSavePriorities() {
        if (isSaving) {
            savePending = true;
            return;
        }
        isSaving = true;
        savePending = false;

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
            if (savePending) {
                autoSavePriorities();
            }
        }
    }

    function updateStatusIndicator(state) {
        if (!saveBtn) return;

        const spinner = saveBtn.querySelector('.pm-spinner');
        const textSpan = saveBtn.querySelector('.pm-btn-text');

        if (state === 'saving') {
            saveBtn.className = 'pm-save-btn pm-status-saving';
            if (spinner) spinner.hidden = false;
            if (textSpan) textSpan.textContent = 'Guardando...';
        } else if (state === 'saved') {
            saveBtn.className = 'pm-save-btn pm-status-saved';
            if (spinner) spinner.hidden = true;
            if (textSpan) textSpan.textContent = '✓ Guardado';
        } else if (state === 'error') {
            saveBtn.className = 'pm-save-btn pm-status-error';
            if (spinner) spinner.hidden = true;
            if (textSpan) textSpan.textContent = '⚠ Error al guardar';
        } else {
            saveBtn.className = 'pm-save-btn';
            if (spinner) spinner.hidden = true;
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
