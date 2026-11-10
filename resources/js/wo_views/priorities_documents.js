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

        function renderSection(section) {
            const details = document.createElement('details');
            const summary = document.createElement('summary');
            summary.textContent = `${section.title} (${section.files.length})`;
            details.append(summary);
            if (section.error || !section.files.length) {
                const message = document.createElement('p');
                message.textContent = section.error || 'No hay archivos registrados para esta OT.';
                details.append(message);
                if (section.error) retry.hidden = false;
            }
            for (const file of section.files) {
                const row = document.createElement('div');
                row.className = 'priority-document-row';
                const description = document.createElement('div');
                const name = document.createElement('strong');
                name.textContent = file.name;
                const detail = document.createElement('small');
                detail.textContent = `${file.class || 'General'} · ${file.detail}`;
                description.append(name, detail);
                const action = document.createElement(file.available ? 'a' : 'span');
                action.textContent = file.available ? 'Abrir' : 'Archivo no disponible';
                if (file.available) {
                    action.href = file.url;
                    action.target = '_blank';
                    action.rel = 'noopener noreferrer';
                    action.className = 'btns blue';
                }
                row.append(description, action);
                details.append(row);
            }
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
