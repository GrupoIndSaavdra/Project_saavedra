/**
 * salida_molduras.js
 * Módulo JavaScript para la vista "Salida de Molduras" — Calidad Fundición
 * Grupo Industrial Saavedra
 *
 * Responsabilidades:
 *  1. Autoguardado de celdas del grid (debounce 1500ms)
 *  2. Autoguardado de observaciones (debounce 2000ms)
 *  3. Actualización del encabezado (consignación + fecha)
 *  4. Selector dinámico OT → Clases (en la vista index)
 *  5. Panel de log de auditoría (carga bajo demanda)
 */

(function () {
    'use strict';

    // ─── CONSTANTES ──────────────────────────────────────────────────
    const DEBOUNCE_CELDA   = 1500; // ms de espera antes de guardar celda
    const DEBOUNCE_OBS     = 2000; // ms de espera antes de guardar observaciones
    const DEBOUNCE_HEADER  = 1000; // ms para guardar el encabezado

    // ─── HELPERS ─────────────────────────────────────────────────────

    /**
     * Función debounce: retrasa la ejecución hasta que pase el tiempo indicado
     * sin nuevas llamadas. Útil para no saturar el servidor con cada pulsación.
     */
    function debounce(fn, delay) {
        let timer;
        return function (...args) {
            clearTimeout(timer);
            timer = setTimeout(() => fn.apply(this, args), delay);
        };
    }

    /** CSRF token para las peticiones POST */
    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    }

    let saveStatusTimer = null;

    /**
     * Actualiza el indicador de guardado global (#save-indicator).
     * @param {'idle'|'dirty'|'saving'|'saved'|'error'} estado
     */
    function setSaveIndicator(estado) {
        const indicator = document.getElementById('save-indicator');
        const text      = document.getElementById('save-status-text');
        if (!indicator || !text) return;

        if (saveStatusTimer) {
            clearTimeout(saveStatusTimer);
            saveStatusTimer = null;
        }

        indicator.className = 'sm-save-indicator';

        const estados = {
            idle:   { cls: 'sm-save-indicator--idle',   txt: 'Listo' },
            dirty:  { cls: 'sm-save-indicator--saving', txt: 'Hay cambios sin guardar...' },
            saving: { cls: 'sm-save-indicator--saving', txt: 'Hay cambios sin guardar...' },
            saved:  { cls: 'sm-save-indicator--saved',  txt: 'Cambios guardados correctamente' },
            error:  { cls: 'sm-save-indicator--error',  txt: 'Error al guardar cambios' },
        };

        const cfg = estados[estado] ?? estados.idle;
        if (cfg.cls) indicator.classList.add(cfg.cls);
        text.textContent = cfg.txt;

        // Auto-volver a "Listo" después de 3.5s si fue exitoso
        if (estado === 'saved') {
            saveStatusTimer = setTimeout(() => setSaveIndicator('idle'), 3500);
        }
    }

    /**
     * Marca visualmente el input de la celda con el estado de guardado.
     * @param {HTMLInputElement} input
     * @param {'saving'|'saved'|'error'|null} estado
     */
    function setInputEstado(input, estado) {
        if (!input || input.type === 'checkbox') return;
        
        input.classList.remove('sm-celda__input--saving', 'sm-celda__input--saved', 'sm-celda__input--error');
        if (estado) {
            input.classList.add(`sm-celda__input--${estado}`);
        }
    }

    // ─── 1. AUTOGUARDADO DE CELDAS ───────────────────────────────────

    /**
     * Envía una petición AJAX para guardar una celda individual.
     * Registra automáticamente en el log de auditoría (lo hace el servidor).
     */
    async function guardarCelda(input) {
        const reporteId   = input.dataset.reporteId;
        const numeroPieza = input.dataset.num;
        const campo       = input.dataset.campo;
        
        let valor;
        if (input.type === 'checkbox') {
            valor = input.checked ? '1' : null;
        } else {
            valor = input.value.trim() || null;
        }

        if (!reporteId || !numeroPieza || !campo) return;

        setInputEstado(input, 'saving');
        setSaveIndicator('saving');

        try {
            const response = await fetch(window.routes['calidad.salida_molduras.autosave'], {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept':       'application/json',
                },
                body: JSON.stringify({
                    reporte_id:   parseInt(reporteId),
                    numero_pieza: parseInt(numeroPieza),
                    campo:        campo,
                    valor:        valor,
                }),
            });

            if (!response.ok) {
                const err = await response.json().catch(() => ({}));
                throw new Error(err.error || `Error HTTP ${response.status}`);
            }

            setInputEstado(input, 'saved');
            setSaveIndicator('saved');

            // Restaurar estilo normal después de 2.5s
            setTimeout(() => setInputEstado(input, null), 2500);

        } catch (error) {
            console.error('[SalidaMolduras][guardarCelda]', error);
            setInputEstado(input, 'error');
            setSaveIndicator('error');
        }
    }

    /** Versión con debounce del guardado de celda */
    const guardarCeldaDebounce = debounce(guardarCelda, DEBOUNCE_CELDA);

    /**
     * Inicializa los listeners de autoguardado en todos los inputs del grid.
     * Usa delegación de eventos en el contenedor para soportar inputs dinámicos.
     */
    function initAutoguardadoCeldas() {
        // Delegación sobre el body para capturar grids de ambos formatos (inputs de texto/número)
        document.addEventListener('input', function (e) {
            const input = e.target.closest('.sm-input-autosave');
            if (!input || input.type === 'checkbox') return;

            setSaveIndicator('dirty');
            setInputEstado(input, 'saving');
            guardarCeldaDebounce(input);
        });

        // Delegación para checkboxes
        document.addEventListener('change', function (e) {
            const input = e.target.closest('.sm-input-autosave');
            if (!input || input.type !== 'checkbox') return;

            setSaveIndicator('dirty');
            setInputEstado(input, 'saving');
            guardarCelda(input);

            // ── Lógica UI especial para los Checkboxes (Moldes / Bombillos) ──
            if (input.classList.contains('sm-checkbox-molde')) {
                const isChecked = input.checked;
                const num = input.dataset.num || input.getAttribute('data-num');
                const header = document.getElementById(`molde-num-${num}`);
                const input90 = document.getElementById(`pieza-${num}-90`);
                const inputLp = document.getElementById(`pieza-${num}-lp`);

                if (header) {
                    header.classList.remove('sm-badge-num--red', 'sm-badge-num--green');
                    header.classList.add(isChecked ? 'sm-badge-num--green' : 'sm-badge-num--red');
                }

                if (input90) input90.disabled = !isChecked;
                if (inputLp) inputLp.disabled = !isChecked;

                actualizarEstadoBotonesAccion();
            }
        });
    }

    // ─── 2. AUTOGUARDADO DE OBSERVACIONES ───────────────────────────

    async function guardarObservaciones(textarea) {
        const reporteId    = textarea.dataset.reporteId;
        const observaciones = textarea.value;

        const statusEl = document.getElementById('obs-status');

        if (statusEl) statusEl.textContent = 'Guardando...';
        setSaveIndicator('saving');

        try {
            const response = await fetch(window.routes['calidad.salida_molduras.observaciones'], {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept':       'application/json',
                },
                body: JSON.stringify({
                    reporte_id:    parseInt(reporteId),
                    observaciones: observaciones || null,
                }),
            });

            if (!response.ok) throw new Error(`Error HTTP ${response.status}`);

            if (statusEl) {
                statusEl.textContent = '✓ Observaciones guardadas';
                setTimeout(() => { statusEl.textContent = ''; }, 3000);
            }
            setSaveIndicator('saved');

        } catch (error) {
            console.error('[SalidaMolduras][guardarObservaciones]', error);
            if (statusEl) statusEl.textContent = '✗ Error al guardar';
            setSaveIndicator('error');
        }
    }

    const guardarObsDebounce = debounce(guardarObservaciones, DEBOUNCE_OBS);

    function initObservaciones() {
        const textarea = document.getElementById('campo-observaciones');
        if (!textarea) return;

        const counterEl = document.getElementById('obs-chars-used');

        // Contador de caracteres en tiempo real
        textarea.addEventListener('input', function () {
            if (counterEl) counterEl.textContent = this.value.length;
            guardarObsDebounce(this);
        });
    }

    // ─── 3. ACTUALIZACIÓN DE ENCABEZADO ─────────────────────────────

    async function guardarHeader(campo, valor) {
        const reporte = window.smReporte;
        if (!reporte) return;

        try {
            const body = { reporte_id: reporte.id };
            body[campo] = valor;

            const response = await fetch(window.routes['calidad.salida_molduras.updateHeader'], {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept':       'application/json',
                },
                body: JSON.stringify(body),
            });

            if (!response.ok) throw new Error(`Error HTTP ${response.status}`);

            const data = await response.json();

            // Si cambió la consignación, actualizar el display de total
            if (campo === 'cantidad_consignacion' && data.total_piezas !== undefined) {
                const totalEl      = document.getElementById('display-total-piezas');
                const consigEl     = document.getElementById('display-consignacion');
                if (totalEl) totalEl.textContent = data.total_piezas;
                if (consigEl) consigEl.textContent = valor;

                setSaveIndicator('saved');

                // Mostrar aviso de recarga si el total de piezas cambió
                if (data.total_piezas !== reporte.totalPiezas) {
                    mostrarAlerta(
                        'Consignación actualizada',
                        'El total de piezas cambió a ' + data.total_piezas + '. Recarga la página para ver el grid actualizado.',
                        'info'
                    );
                }
            } else {
                setSaveIndicator('saved');
            }

        } catch (error) {
            console.error('[SalidaMolduras][guardarHeader]', error);
            setSaveIndicator('error');
        }
    }

    const guardarHeaderDebounce = debounce(guardarHeader, DEBOUNCE_HEADER);

    function initHeader() {
        // Input de fecha de inicio
        const inputFecha = document.getElementById('campo-fecha-inicio');
        if (inputFecha) {
            inputFecha.addEventListener('change', function () {
                guardarHeaderDebounce('fecha_inicio', this.value);
            });
        }
    }

    // ─── 4. SELECTOR DINÁMICO OT → CLASES (vista index) ─────────────

    function initSelectorOT() {
        const selectOT    = document.getElementById('select-ot');
        const selectClase = document.getElementById('select-clase');
        const grupoClase  = document.getElementById('grupo-clase');
        const formAbrir   = document.getElementById('form-abrir-reporte');

        if (!selectOT || !selectClase) return;

        if (formAbrir) {
            formAbrir.addEventListener('submit', function (e) {
                e.preventDefault();
            });
        }

        const otClases = window.smOtClases ?? {};

        // Al seleccionar una OT, evaluar clases y redirigir o mostrar selector al lado
        selectOT.addEventListener('change', function () {
            const otId = this.value;
            selectClase.innerHTML = '<option value="">— Selecciona una clase —</option>';

            if (!otId) {
                if (grupoClase) grupoClase.style.display = 'none';
                return;
            }

            const clases = otClases[otId] ?? [];

            // Si solo hay una clase, auto-seleccionar y abrir directamente
            if (clases.length === 1) {
                const url = window.routes['calidad.salida_molduras.show']
                    .replace('%3Aot', encodeURIComponent(otId))
                    .replace(':ot', encodeURIComponent(otId))
                    .replace('%3Aclase', encodeURIComponent(clases[0]))
                    .replace(':clase', encodeURIComponent(clases[0]));
                window.location.href = url;
                return;
            }

            // Si hay múltiples clases, poblar y mostrar selector al lado
            clases.forEach(clase => {
                const opt = document.createElement('option');
                opt.value       = clase;
                opt.textContent = clase;
                selectClase.appendChild(opt);
            });

            if (grupoClase) {
                grupoClase.style.display = clases.length ? 'flex' : 'none';
            }
        });

        // Al seleccionar una clase, abrir el reporte inmediatamente
        selectClase.addEventListener('change', function () {
            const otId  = selectOT.value;
            const clase = this.value;

            if (otId && clase) {
                const url = window.routes['calidad.salida_molduras.show']
                    .replace('%3Aot', encodeURIComponent(otId))
                    .replace(':ot', encodeURIComponent(otId))
                    .replace('%3Aclase', encodeURIComponent(clase))
                    .replace(':clase', encodeURIComponent(clase));
                window.location.href = url;
            }
        });
    }

    // ─── 5. PANEL DE LOG DE AUDITORÍA Y FILTROS ──────────────────────

    function formatCampoLog(campo) {
        if (!campo) return '—';
        if (campo === 'observaciones') return '<span class="log-tag log-tag--header">Observaciones Generales</span>';
        if (campo === 'cantidad_consignacion') return '<span class="log-tag log-tag--header">Encabezado — Consignación</span>';
        if (campo === 'fecha_inicio') return '<span class="log-tag log-tag--header">Encabezado — Fecha Inicio</span>';
        if (campo === 'seleccion_ot_clase') return '<span class="log-tag log-tag--header">Acceso / Carga de Información</span>';

        const match = campo.match(/^pieza_(\d+)_(.+)$/);
        if (match) {
            const num = match[1];
            const tipo = match[2];
            let labelTipo = tipo;
            if (tipo === 'valor_simple') labelTipo = 'Liberación (Casilla ✔)';
            else if (tipo === 'valor_90') labelTipo = 'Cota 90°';
            else if (tipo === 'valor_lp') labelTipo = 'Cota LP';
            else if (tipo === 'descripcion') labelTipo = 'Motivo / Descripción';

            return `<span class="log-tag log-tag--pieza">Pieza N° <strong>${num}</strong> &mdash; ${labelTipo}</span>`;
        }

        return `<code>${escHtml(campo)}</code>`;
    }

    function formatValorLog(campo, valor) {
        if (valor === null || valor === undefined || valor === '' || valor === '—') {
            return '<span class="log-val-empty">— Vacío —</span>';
        }
        if (campo && campo.includes('valor_simple')) {
            if (valor === '1') {
                return '<span class="log-badge-val log-badge-val--ok">✔ Liberada</span>';
            } else {
                return '<span class="log-badge-val log-badge-val--no">✘ No liberada</span>';
            }
        }
        return escHtml(valor);
    }

    function initLogPanel() {
        const btnToggle  = document.getElementById('btn-toggle-log');
        const logBody    = document.getElementById('log-body');
        const logLoading = document.getElementById('log-loading');
        const logContent = document.getElementById('log-content');
        const logEmpty   = document.getElementById('log-empty');
        const logTbody   = document.getElementById('log-tbody');

        if (!btnToggle || !logBody) return;

        const reporteId = document.getElementById('reporte-id-log')?.value;
        let logCargado  = false;
        let allLogs     = [];

        function renderLogs(logsToRender) {
            if (!logTbody) return;
            logTbody.innerHTML = '';

            if (!logsToRender || logsToRender.length === 0) {
                if (logEmpty) {
                    logEmpty.classList.remove('hidden');
                    logEmpty.textContent = 'Sin ediciones registradas que coincidan con los filtros.';
                }
                return;
            }

            if (logEmpty) logEmpty.classList.add('hidden');

            logsToRender.forEach((log) => {
                const idxOriginal = allLogs.indexOf(log);
                const tr = document.createElement('tr');
                tr.className = 'fade-in';
                tr.innerHTML = `
                    <td style="text-align: center; font-weight: 700;">${allLogs.length - idxOriginal}</td>
                    <td><span class="log-tag log-tag--ot">OT ${escHtml(log.ot_id)} &mdash; ${escHtml(log.clase)}</span></td>
                    <td><span class="log-badge-accion">${escHtml(log.accion)}</span></td>
                    <td>${formatCampoLog(log.campo)}</td>
                    <td>${formatValorLog(log.campo, log.valor_anterior)}</td>
                    <td>${formatValorLog(log.campo, log.valor_nuevo)}</td>
                    <td><strong style="color: #334155;">${escHtml(log.usuario)}</strong></td>
                    <td style="text-align: center; font-size: 0.82rem; color: #555;">${escHtml(log.fecha)}</td>
                `;
                logTbody.appendChild(tr);
            });
        }

        function filterLogs() {
            const userVal   = document.getElementById('filter-log-usuario')?.value.toLowerCase() || '';
            const desdeVal  = document.getElementById('filter-log-desde')?.value || '';
            const hastaVal  = document.getElementById('filter-log-hasta')?.value || '';
            const searchVal = document.getElementById('filter-log-search')?.value.toLowerCase().trim() || '';

            const filtered = allLogs.filter(log => {
                if (userVal && (log.usuario || '').toLowerCase() !== userVal) {
                    return false;
                }
                const logDate = log.fecha_iso || '';
                if (desdeVal && logDate < desdeVal) return false;
                if (hastaVal && logDate > hastaVal) return false;

                if (searchVal) {
                    const haystack = [
                        log.ot_id,
                        log.clase,
                        log.accion,
                        log.campo,
                        log.valor_anterior,
                        log.valor_nuevo,
                        log.usuario,
                        log.fecha
                    ].join(' ').toLowerCase();
                    if (!haystack.includes(searchVal)) return false;
                }

                return true;
            });

            renderLogs(filtered);
        }

        function populateUsersDropdown(logs) {
            const userSelect = document.getElementById('filter-log-usuario');
            if (!userSelect) return;
            const currentSelected = userSelect.value;
            userSelect.innerHTML = '<option value="">Todos los usuarios</option>';

            const usersSet = new Set();
            logs.forEach(l => {
                if (l.usuario) usersSet.add(l.usuario);
            });

            Array.from(usersSet).sort().forEach(user => {
                const opt = document.createElement('option');
                opt.value = user.toLowerCase();
                opt.textContent = user;
                if (user.toLowerCase() === currentSelected) opt.selected = true;
                userSelect.appendChild(opt);
            });
        }

        // Listeners para la barra de filtros
        document.getElementById('filter-log-usuario')?.addEventListener('change', filterLogs);
        document.getElementById('filter-log-desde')?.addEventListener('change', filterLogs);
        document.getElementById('filter-log-hasta')?.addEventListener('change', filterLogs);
        document.getElementById('filter-log-search')?.addEventListener('input', filterLogs);
        document.getElementById('btn-reset-log-filter')?.addEventListener('click', function () {
            const uSelect = document.getElementById('filter-log-usuario');
            const dFrom   = document.getElementById('filter-log-desde');
            const dTo     = document.getElementById('filter-log-hasta');
            const sInput  = document.getElementById('filter-log-search');

            if (uSelect) uSelect.value = '';
            if (dFrom) dFrom.value = '';
            if (dTo) dTo.value = '';
            if (sInput) sInput.value = '';

            renderLogs(allLogs);
        });

        btnToggle.addEventListener('click', async function () {
            const isOpen = !logBody.classList.contains('hidden');

            if (isOpen) {
                logBody.classList.add('hidden');
                btnToggle.classList.remove('sm-log-toggle--open');
                btnToggle.setAttribute('aria-expanded', 'false');
                return;
            }

            logBody.classList.remove('hidden');
            btnToggle.classList.add('sm-log-toggle--open');
            btnToggle.setAttribute('aria-expanded', 'true');

            if (logCargado || !reporteId) return;

            logLoading?.classList.remove('hidden');
            logContent?.classList.add('hidden');

            try {
                const url = window.routes['calidad.salida_molduras.log']
                    .replace('%3Aid', reporteId)
                    .replace(':id', reporteId);

                const response = await fetch(url, {
                    headers: { 'Accept': 'application/json' }
                });

                if (!response.ok) throw new Error(`HTTP ${response.status}`);

                const data = await response.json();
                logCargado = true;
                allLogs = data.logs || [];

                logLoading?.classList.add('hidden');
                logContent?.classList.remove('hidden');

                populateUsersDropdown(allLogs);
                renderLogs(allLogs);

            } catch (error) {
                console.error('[SalidaMolduras][getLog]', error);
                logLoading?.classList.add('hidden');
                logContent?.classList.remove('hidden');
                if (logEmpty) {
                    logEmpty.textContent = '⚠️ Error al cargar el historial.';
                    logEmpty.classList.remove('hidden');
                }
            }
        });
    }

    // ─── HELPER: Escape HTML ─────────────────────────────────────────

    function escHtml(str) {
        if (str === null || str === undefined) return '—';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    // ─── FUNCIÓN GLOBAL: Alertas SweetAlert2 ─────────────────────────

    function mostrarAlerta(titulo, texto, icono) {
        if (typeof Swal === 'undefined') {
            alert(`${titulo}: ${texto}`);
            return;
        }
        Swal.fire({
            title:             titulo,
            text:              texto,
            icon:              icono,
            timer:             icono === 'success' ? 2500 : undefined,
            timerProgressBar:  icono === 'success',
            confirmButtonColor: icono === 'error' ? '#9c0303' : '#0a8504',
        });
    }

    // ─── 6. SELECTORES DE OT Y CLASE (ENCABEZADO) ────────────────────

    function initHeaderOTSelector() {
        const selectOt    = document.getElementById('header-select-ot');
        const selectClase = document.getElementById('header-select-clase');
        const container   = document.getElementById('header-clase-container');

        if (selectClase) {
            selectClase.addEventListener('change', function () {
                if (this.value) {
                    window.location.href = this.value;
                }
            });
        }

        if (selectOt) {
            selectOt.addEventListener('change', function () {
                const otId = this.value;
                if (!otId) return;

                const otClasesMap = window.smOtClases || {};
                const clases = otClasesMap[otId] || [];
                const routePattern = window.routes?.['calidad.salida_molduras.show'];

                if (!routePattern) return;

                function buildShowUrl(targetOt, targetClase) {
                    return routePattern
                        .replace('%3Aot', encodeURIComponent(targetOt))
                        .replace(':ot', encodeURIComponent(targetOt))
                        .replace('%3Aclase', encodeURIComponent(targetClase))
                        .replace(':clase', encodeURIComponent(targetClase));
                }

                if (clases.length === 1) {
                    window.location.href = buildShowUrl(otId, clases[0]);
                } else if (clases.length > 1) {
                    if (selectClase && container) {
                        selectClase.innerHTML = '';
                        clases.forEach(clase => {
                            const opt = document.createElement('option');
                            opt.value = buildShowUrl(otId, clase);
                            opt.textContent = clase;
                            selectClase.appendChild(opt);
                        });
                        container.style.display = 'inline-flex';
                        window.location.href = selectClase.options[0].value;
                    } else {
                        window.location.href = buildShowUrl(otId, clases[0]);
                    }
                }
            });
        }
    }

    // ─── 7. ACCIONES MASIVAS Y POR RANGO DE PIEZAS ──────────────────

    async function ejecutarUpdateMasivo(desde, hasta, valor) {
        const reporte = window.smReporte;
        if (!reporte || !reporte.id) return;

        const routeUrl = window.routes?.['calidad.salida_molduras.bulk'];
        if (!routeUrl) {
            console.error('[SalidaMolduras][ejecutarUpdateMasivo] Ruta de bulk update no configurada');
            return;
        }

        setSaveIndicator('saving');

        try {
            const response = await fetch(routeUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept':       'application/json',
                },
                body: JSON.stringify({
                    reporte_id: parseInt(reporte.id),
                    desde:      parseInt(desde),
                    hasta:      parseInt(hasta),
                    valor:      valor === '1' ? '1' : null,
                }),
            });

            if (!response.ok) {
                const errData = await response.json().catch(() => ({}));
                throw new Error(errData.error || `Error HTTP ${response.status}`);
            }

            const data = await response.json();

            // Limpiar los campos de rango automáticamente
            const desdeInput = document.getElementById('sm-range-desde');
            const hastaInput = document.getElementById('sm-range-hasta');
            if (desdeInput) desdeInput.value = '';
            if (hastaInput) hastaInput.value = '';

            // Actualizar la interfaz para cada pieza en el rango
            const d = Math.min(desde, hasta);
            const h = Math.max(desde, hasta);
            const isChecked = (valor === '1');

            for (let num = d; num <= h; num++) {
                const cb = document.querySelector(`.sm-checkbox-molde[data-num="${num}"]`);
                if (cb) {
                    cb.checked = isChecked;
                }

                const badge = document.getElementById(`molde-num-${num}`);
                if (badge) {
                    badge.classList.remove('sm-badge-num--red', 'sm-badge-num--green');
                    badge.classList.add(isChecked ? 'sm-badge-num--green' : 'sm-badge-num--red');
                }

                const input90 = document.getElementById(`pieza-${num}-90`);
                const inputLp = document.getElementById(`pieza-${num}-lp`);
                if (input90) input90.disabled = !isChecked;
                if (inputLp) inputLp.disabled = !isChecked;
                // La descripción (pieza-X-desc) permanece intacta y activa
            }

            setSaveIndicator('saved');
            actualizarEstadoBotonesAccion();

        } catch (error) {
            console.error('[SalidaMolduras][ejecutarUpdateMasivo]', error);
            setSaveIndicator('error');
        }
    }

    function actualizarEstadoBotonesAccion() {
        const checkboxes = document.querySelectorAll('.sm-checkbox-molde');
        if (!checkboxes || checkboxes.length === 0) return;

        const total = checkboxes.length;
        let checkedCount = 0;
        checkboxes.forEach(cb => {
            if (cb.checked) checkedCount++;
        });

        const btnMark   = document.getElementById('btn-apply-range-mark');
        const btnUnmark = document.getElementById('btn-apply-range-unmark');
        const btnAll    = document.getElementById('btn-select-all');
        const btnNone   = document.getElementById('btn-deselect-all');

        function setBtnState(btn, stateClass) {
            if (!btn) return;
            btn.classList.remove('sm-btn-active--green', 'sm-btn-active--red', 'sm-btn-inactive');
            btn.classList.add(stateClass);
        }

        if (checkedCount === total && total > 0) {
            // 100% Marcadas: "Seleccionar Todo" activo (Verde), "Desmarcar Todo" y Rango inactivos
            setBtnState(btnAll, 'sm-btn-active--green');
            setBtnState(btnNone, 'sm-btn-inactive');
            setBtnState(btnMark, 'sm-btn-inactive');
            setBtnState(btnUnmark, 'sm-btn-inactive');
        } else if (checkedCount === 0) {
            // 0% Marcadas: "Desmarcar Todo" activo (Rojo), "Seleccionar Todo" y Rango inactivos
            setBtnState(btnAll, 'sm-btn-inactive');
            setBtnState(btnNone, 'sm-btn-active--red');
            setBtnState(btnMark, 'sm-btn-inactive');
            setBtnState(btnUnmark, 'sm-btn-inactive');
        } else {
            // Selección parcial / manual: "Seleccionar Todo" y "Desmarcar Todo" inactivos, Rango activos (Verde / Rojo)
            setBtnState(btnAll, 'sm-btn-inactive');
            setBtnState(btnNone, 'sm-btn-inactive');
            setBtnState(btnMark, 'sm-btn-active--green');
            setBtnState(btnUnmark, 'sm-btn-active--red');
        }
    }

    function initBulkActions() {
        const btnMark   = document.getElementById('btn-apply-range-mark');
        const btnUnmark = document.getElementById('btn-apply-range-unmark');
        const btnAll    = document.getElementById('btn-select-all');
        const btnNone   = document.getElementById('btn-deselect-all');

        const totalPiezas = window.smReporte?.totalPiezas || 0;

        function validarYObtenerRango() {
            const desdeInput = document.getElementById('sm-range-desde');
            const hastaInput = document.getElementById('sm-range-hasta');

            const val1 = parseInt(desdeInput?.value);
            const val2 = parseInt(hastaInput?.value);

            if (isNaN(val1) || isNaN(val2) || val1 < 1 || val2 < 1) {
                if (isNaN(val1) && desdeInput) desdeInput.focus();
                else if (isNaN(val2) && hastaInput) hastaInput.focus();
                return null;
            }

            const desde = Math.min(val1, val2);
            const hasta = Math.max(val1, val2);

            if (desde > totalPiezas) {
                return null;
            }

            const hastaAjustado = Math.min(hasta, totalPiezas);
            return { desde, hasta: hastaAjustado };
        }

        if (btnMark) {
            btnMark.addEventListener('click', function () {
                const rango = validarYObtenerRango();
                if (rango) {
                    ejecutarUpdateMasivo(rango.desde, rango.hasta, '1');
                }
            });
        }

        if (btnUnmark) {
            btnUnmark.addEventListener('click', function () {
                const rango = validarYObtenerRango();
                if (rango) {
                    ejecutarUpdateMasivo(rango.desde, rango.hasta, '0');
                }
            });
        }

        if (btnAll) {
            btnAll.addEventListener('click', function () {
                if (totalPiezas <= 0) return;
                ejecutarUpdateMasivo(1, totalPiezas, '1');
            });
        }

        if (btnNone) {
            btnNone.addEventListener('click', function () {
                if (totalPiezas <= 0) return;
                ejecutarUpdateMasivo(1, totalPiezas, '0');
            });
        }

        // Evaluar estado inicial al cargar los botones
        actualizarEstadoBotonesAccion();
    }

    // ─── LÓGICA DE PDF, ENVÍO Y DESBLOQUEO ───────────────────────────

    window.openSendReportModal = function() {
        if (!window.smReporte || !window.routes['calidad.salida_molduras.send']) return;
        const btn = document.getElementById('btn-send-report');
        if (btn && btn.classList.contains('disabled')) return;

        let confirmBtn;

        Swal.fire({
            title: 'Alerta del Sistema',
            html: '<h3 style="color: #033966; font-weight: bold; margin-bottom: 10px;">¿Enviar Reporte a la Raza?</h3><p style="margin-bottom: 15px;">Se enviará el reporte por correo y se bloqueará para futuras ediciones.</p><p style="font-weight: bold; color: #d33;">Solo se puede enviar una vez. El botón se habilitará en <span id="swal-countdown">5</span> segundos.</p>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, confirmar',
            cancelButtonText: 'Cancelar',
            allowOutsideClick: false,
            customClass: {
                popup: 'sm-swal-popup',
                title: 'sm-swal-title',
                confirmButton: 'sm-swal-btn sm-swal-btn-confirm',
                cancelButton: 'sm-swal-btn sm-swal-btn-cancel'
            },
            buttonsStyling: false,
            didOpen: () => {
                confirmBtn = Swal.getConfirmButton();
                confirmBtn.disabled = true;
                
                let timeLeft = 5;
                const countdownEl = document.getElementById('swal-countdown');
                const timer = setInterval(() => {
                    timeLeft--;
                    if (countdownEl) countdownEl.textContent = timeLeft;
                    if (timeLeft <= 0) {
                        clearInterval(timer);
                        confirmBtn.disabled = false;
                        if (countdownEl) countdownEl.parentElement.innerHTML = '¡Puede enviar el reporte!';
                    }
                }, 1000);
            }
        }).then((result) => {
            if (result.isConfirmed) {
                enviarReporteBack();
            }
        });
    };

    async function enviarReporteBack() {
        Swal.fire({
            title: 'Alerta del Sistema',
            html: '<h3 style="color: #033966; font-weight: bold;">Enviando...</h3><p>Por favor espere, se está generando el PDF y enviando el correo.</p>',
            allowOutsideClick: false,
            customClass: {
                popup: 'sm-swal-popup',
                title: 'sm-swal-title'
            },
            didOpen: () => Swal.showLoading()
        });

        try {
            const res = await fetch(window.routes['calidad.salida_molduras.send'].replace(':id', window.smReporte.id), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.error || 'Error al enviar el reporte');

            Swal.fire({
                title: 'Alerta del Sistema',
                html: '<h3 style="color: #16a34a; font-weight: bold;">¡Enviado!</h3><p>El reporte se ha enviado correctamente.</p>',
                icon: 'success',
                customClass: {
                    popup: 'sm-swal-popup',
                    title: 'sm-swal-title',
                    confirmButton: 'sm-swal-btn sm-swal-btn-confirm'
                },
                buttonsStyling: false
            }).then(() => {
                window.location.reload();
            });
        } catch (error) {
            Swal.fire({
                title: 'Alerta del Sistema',
                html: `<h3 style="color: #d33; font-weight: bold;">Error</h3><p>${error.message}</p>`,
                icon: 'error',
                customClass: {
                    popup: 'sm-swal-popup',
                    title: 'sm-swal-title',
                    confirmButton: 'sm-swal-btn sm-swal-btn-cancel'
                },
                buttonsStyling: false
            });
        }
    }

    window.generateReportPdf = function() {
        if (!window.smReporte || !window.routes['calidad.salida_molduras.pdf']) return;
        
        Swal.fire({
            title: 'Alerta del Sistema',
            html: '<h3 style="color: #033966; font-weight: bold;">Generando PDF...</h3><p>Por favor espere mientras se descarga su documento.</p>',
            allowOutsideClick: false,
            customClass: { popup: 'sm-swal-popup', title: 'sm-swal-title' },
            didOpen: () => Swal.showLoading()
        });

        const url = window.routes['calidad.salida_molduras.pdf'].replace(':id', window.smReporte.id);
        
        // Crear enlace invisible para forzar descarga
        const a = document.createElement('a');
        a.href = url;
        a.target = '_blank';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);

        // Esperar unos segundos para permitir que el backend procese antes de recargar
        setTimeout(() => {
            Swal.close();
            window.location.reload();
        }, 4000);
    };

    window.openMasterUnlockModal = function() {
        if (!window.smReporte || !window.routes['calidad.salida_molduras.unlock']) return;

        Swal.fire({
            title: 'Alerta del Sistema',
            html: '<h3 style="color: #033966; font-weight: bold; margin-bottom: 10px;">Desbloqueo Master</h3>' + document.getElementById('template-master-unlock').innerHTML,
            showCancelButton: true,
            confirmButtonText: 'Desbloquear',
            cancelButtonText: 'Cancelar',
            customClass: {
                popup: 'sm-swal-popup',
                title: 'sm-swal-title',
                confirmButton: 'sm-swal-btn sm-swal-btn-confirm',
                cancelButton: 'sm-swal-btn sm-swal-btn-cancel'
            },
            buttonsStyling: false,
            preConfirm: () => {
                const pass = Swal.getPopup().querySelector('#swal-master-password').value;
                if (!pass) {
                    Swal.showValidationMessage('Debe ingresar la contraseña');
                }
                return { password: pass };
            }
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    Swal.fire({
                        title: 'Alerta del Sistema',
                        html: '<h3 style="color: #033966; font-weight: bold;">Verificando...</h3>',
                        allowOutsideClick: false,
                        customClass: { popup: 'sm-swal-popup', title: 'sm-swal-title' },
                        didOpen: () => Swal.showLoading()
                    });
                    const res = await fetch(window.routes['calidad.salida_molduras.unlock'].replace(':id', window.smReporte.id), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': getCsrfToken(),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ password: result.value.password })
                    });
                    const data = await res.json();
                    if (!res.ok) throw new Error(data.error || 'Contraseña incorrecta o error de servidor');

                    Swal.fire({
                        title: 'Alerta del Sistema',
                        html: '<h3 style="color: #16a34a; font-weight: bold;">¡Desbloqueado!</h3><p>El reporte se ha desbloqueado correctamente.</p>',
                        icon: 'success',
                        customClass: { popup: 'sm-swal-popup', title: 'sm-swal-title', confirmButton: 'sm-swal-btn sm-swal-btn-confirm' },
                        buttonsStyling: false
                    }).then(() => {
                        window.location.reload();
                    });
                } catch (error) {
                    Swal.fire({
                        title: 'Alerta del Sistema',
                        html: `<h3 style="color: #d33; font-weight: bold;">Error</h3><p>${error.message}</p>`,
                        icon: 'error',
                        customClass: { popup: 'sm-swal-popup', title: 'sm-swal-title', confirmButton: 'sm-swal-btn sm-swal-btn-cancel' },
                        buttonsStyling: false
                    });
                }
            }
        });
    };

    // ─── INICIALIZACIÓN PRINCIPAL ────────────────────────────────────

    document.addEventListener('DOMContentLoaded', function () {
        initAutoguardadoCeldas();  // Grid de piezas
        initObservaciones();       // Textarea observaciones
        initHeader();              // Campos del encabezado
        initSelectorOT();          // Selector OT → Clases (index)
        initLogPanel();            // Panel de historial
        initHeaderOTSelector();    // Selector de OT en encabezado
        initBulkActions();         // Acciones masivas por rango
    });

})();
