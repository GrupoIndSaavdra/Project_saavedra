(() => {
    document.addEventListener('DOMContentLoaded', () => {
        const dialog = document.getElementById('priorityDocuments');
        if (!dialog) return;
        const title = dialog.querySelector('h2');
        const status = dialog.querySelector('[data-documents-status]');
        const sections = dialog.querySelector('[data-documents-sections]');
        const retry = dialog.querySelector('[data-retry-documents]');
        let controller;
        let activeButton;

        async function checkAvailability() {
            const buttons = [...document.querySelectorAll('[data-check-documents]')];
            const ids = [...new Set(buttons.map((button) => button.dataset.priorityDocuments))];
            for (let offset = 0; offset < ids.length; offset += 100) {
                const batch = ids.slice(offset, offset + 100);
                const batchButtons = buttons.filter((button) => batch.includes(button.dataset.priorityDocuments));
                try {
                    const url = new URL(window.routes.priorityDocumentsAvailability, window.location.origin);
                    batch.forEach((id) => url.searchParams.append('ots[]', id));
                    const response = await fetch(url, { headers: { Accept: 'application/json' } });
                    if (!response.ok) throw new Error('No se pudo comprobar la disponibilidad.');
                    const data = await response.json();
                    if (!data.success || !data.available) throw new Error('Respuesta de disponibilidad inválida.');
                    batchButtons.forEach((button) => {
                        const available = data.available[button.dataset.priorityDocuments] === true;
                        button.disabled = !available;
                        button.textContent = available ? 'Ver archivos' : 'Sin archivos';
                        button.title = available ? 'Consultar archivos y reportes de esta OT' : 'Esta OT no tiene archivos ni reportes disponibles';
                    });
                } catch (error) {
                    // Si falla la comprobación, permitir consultar y reintentar desde el modal.
                    batchButtons.forEach((button) => {
                        button.disabled = false;
                        button.title = 'No se pudo comprobar la disponibilidad. Pulsa para consultar.';
                    });
                    console.error('Disponibilidad de archivos de Prioridades:', error);
                } finally {
                    batchButtons.forEach((button) => button.removeAttribute('aria-busy'));
                }
            }
        }

        checkAvailability();

        function renderSection(section) {
            const details = document.createElement('details');
            details.setAttribute('name', 'priority-documents-sections');
            const summary = document.createElement('summary');
            summary.textContent = `${section.title} (${section.files.length})`;
            details.append(summary);
            if (section.error || !section.files.length) {
                const message = document.createElement('p');
                message.textContent = section.error || 'No hay archivos registrados para esta OT.';
                details.append(message);
                if (section.error) retry.hidden = false;
            }
            const grid = document.createElement('div');
            grid.className = 'priority-documents-grid';
            for (const file of section.files) {
                const card = document.createElement('article');
                card.className = 'dibujos-file-card priority-document-card';
                const extension = file.name.split('.').pop().toLowerCase();
                const kind = extension === 'dwg' ? 'dwg'
                    : extension === 'pdf' || file.detail.startsWith('PDF generado') ? 'pdf' : 'other';
                const icon = document.createElement('div');
                icon.className = 'file-icon-wrapper';
                window.priorityDocumentIcons[kind].forEach((src, index) => {
                    const image = document.createElement('img');
                    image.src = src;
                    image.alt = '';
                    image.className = `file-icon ${index ? 'icon-hover' : 'icon-default'}`;
                    icon.append(image);
                });
                const name = document.createElement('strong');
                name.className = 'file-name';
                name.textContent = file.name;
                name.title = file.name;
                const detail = document.createElement('small');
                detail.className = 'priority-document-detail';
                detail.textContent = `${file.class || 'General'} · ${file.detail}`;
                const actions = document.createElement('div');
                actions.className = 'file-actions';
                const action = document.createElement(file.available ? 'a' : 'span');
                action.textContent = file.available ? (kind === 'dwg' ? 'Descargar' : 'Ver') : 'Archivo no disponible';
                if (file.available) {
                    action.href = file.url;
                    action.target = '_blank';
                    action.rel = 'noopener noreferrer';
                    action.className = 'btn-dibujos-sm btn-ver';
                    action.setAttribute('aria-label', `${action.textContent}: ${file.name}`);
                } else {
                    action.className = 'status-neutral';
                }
                actions.append(action);
                card.append(icon, name, detail, actions);
                grid.append(card);
            }
            if (section.files.length) details.append(grid);
            sections.append(details);
        }

        async function load(button) {
            controller?.abort();
            const pending = new AbortController();
            controller = pending;
            activeButton = button;
            title.textContent = `Archivos · ${button.dataset.label}`;
            sections.replaceChildren();
            status.textContent = 'Consultando archivos…';
            retry.hidden = true;
            button.disabled = true;
            button.setAttribute('aria-busy', 'true');
            if (!dialog.open) dialog.showModal();
            try {
                const url = window.routes.priorityDocuments.replace('__OT__', encodeURIComponent(button.dataset.priorityDocuments));
                const response = await fetch(url, {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    signal: pending.signal,
                });
                if (!response.ok) {
                    throw new Error(response.status === 403 ? 'No tienes permiso para consultar estos archivos.'
                        : response.status === 401 || response.status === 419 ? 'Tu sesión expiró. Vuelve a iniciar sesión.'
                        : 'No se pudieron consultar los archivos. Vuelve a intentarlo.');
                }
                const data = await response.json();
                if (pending.signal.aborted) return;
                if (!data.success || !Array.isArray(data.sections)) throw new Error('La respuesta del servidor no es válida.');
                data.sections.forEach(renderSection);
                status.textContent = 'Selecciona una sección para ver sus archivos.';
            } catch (error) {
                if (error.name === 'AbortError') return;
                status.textContent = error.message || 'No se pudo conectar con el servidor.';
                retry.hidden = false;
            } finally {
                button.disabled = false;
                button.removeAttribute('aria-busy');
            }
        }

        document.addEventListener('click', (event) => {
            const button = event.target.closest('[data-priority-documents]');
            if (!button || button.disabled) return;
            event.stopPropagation();
            load(button);
        });
        retry.addEventListener('click', () => { if (activeButton) load(activeButton); });
        dialog.querySelector('[data-close-documents]').addEventListener('click', () => dialog.close());
        dialog.addEventListener('close', () => controller?.abort());
    });
})();
