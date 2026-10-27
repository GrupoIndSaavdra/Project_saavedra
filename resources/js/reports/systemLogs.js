import { createDateFilterTrigger } from '../components/modalCmCalendar.js';

document.addEventListener("DOMContentLoaded", () => {
    if (window.logsData && window.logsData.length > 0) {
        crearTabla(window.logsData);
    }
    createFilters();

    // Adaptar tabla al modo admin
    if (window.isAdminOnly) {
        // Renombrar encabezado Operador -> Administrador
        const labelTh = document.querySelector('.col-admin-label');
        if (labelTh) labelTh.textContent = 'Administrador';
        // Inyectar CSS para ocultar columnas de operador
        const style = document.createElement('style');
        style.textContent = '.col-operador-only { display: none !important; } .col-operador-only-td { display: none !important; }';
        document.head.appendChild(style);
    }

    // Lógica para colapsar/expandir niveles en la tabla de colores
    document.querySelectorAll(".level-toggle").forEach(header => {
        header.addEventListener("click", () => {
            const level = header.getAttribute("data-level");
            const targetRows = document.querySelectorAll(`.level-row.level-${level}`);
            const targetArrow = header.querySelector(".arrow");
            const isExpanding = targetRows[0].classList.contains("sys-display-none");

            // Cerrar todos los niveles de la misma tabla primero
            const parentTable = header.closest('table');
            if (parentTable) {
                parentTable.querySelectorAll(".level-row").forEach(row => {
                    row.classList.add("sys-display-none");
                });
                parentTable.querySelectorAll(".arrow").forEach(arrow => {
                    arrow.textContent = "▶";
                });
            }

            // Si estábamos expandiendo, abrir solo el objetivo
            if (isExpanding) {
                targetRows.forEach(row => {
                    row.classList.remove("sys-display-none");
                });
                targetArrow.textContent = "▼";
            }
        });
    });

    // Alternar tabla de colores según modo admin
    if (window.isAdminOnly) {
        const tableNormal = document.querySelector('.table-colors:not(.table-colors-admin)');
        const tableAdmin  = document.querySelector('.table-colors-admin');
        if (tableNormal) tableNormal.classList.add("sys-display-none");
        if (tableAdmin)  tableAdmin.classList.remove('sys-display-none');
    }

    // ---------------------------------------------------------
    // LÓGICA DE CARGA DINÁMICA (PAGINACIÓN AJAX)
    // ---------------------------------------------------------
    const btnLoadMore = document.getElementById('btn-load-more');
    const loadMoreContainer = document.getElementById('load-more-container');
    const currentCountSpan = document.getElementById('current-count');

    if (btnLoadMore) {
        btnLoadMore.addEventListener('click', async () => {
            if (!window.nextPageUrl) return;

            btnLoadMore.disabled = true;
            btnLoadMore.textContent = 'Cargando registros...';
            btnLoadMore.classList.add('opacity-70');

            try {
                const response = await fetch(window.nextPageUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await response.json();

                if (data.logsData && data.logsData.length > 0) {
                    // Usar la función existente para renderizar los nuevos registros (Append)
                    crearTabla(data.logsData, true);

                    // Actualizar estado de paginación
                    window.nextPageUrl = data.next_page;
                    window.hasMorePages = data.has_more;

                    // Actualizar contadores visuales
                    const totalFoundSpan = document.getElementById('total-found-count');
                    const currentCountSpan = document.getElementById('current-count');

                    if (totalFoundSpan && data.total_found !== undefined) {
                        totalFoundSpan.textContent = data.total_found;
                    }

                    const currentRows = document.querySelectorAll('.table tbody tr').length;
                    if (currentCountSpan) {
                        currentCountSpan.textContent = currentRows;
                    }

                    // Mostrar/Ocultar botón según disponibilidad
                    if (!window.hasMorePages || !window.nextPageUrl) {
                        loadMoreContainer.classList.add("sys-display-none");
                    }
                }
            } catch (error) {
                console.error('Error al cargar más logs:', error);
                if (window.toastpremium) {
                    window.toastpremium('No se pudieron cargar más registros. Intente de nuevo.', 'error');
                }
            } finally {
                btnLoadMore.disabled = false;
                btnLoadMore.textContent = 'Cargar más registros...';
                btnLoadMore.classList.remove('opacity-70');
            }
        });
    }

    // ---------------------------------------------------------
    // LÓGICA DE DEPURACIÓN MANUAL (PURGE) — con progreso y bloqueo
    // ---------------------------------------------------------
    const btnPurge   = document.getElementById('btn-manual-purge');
    const overlay    = document.getElementById('purge-progress-overlay');
    const progressBar = document.getElementById('purge-progress-bar');
    const statusText  = document.getElementById('purge-progress-status');

    // Guard para prevenir doble clic o múltiples peticiones simultáneas
    let isPurging = false;

    // Mensajes de progreso que se muestran de forma animada mientras el servidor trabaja
    const progressSteps = [
        { pct: 5,  msg: 'Conectando con el servidor...' },
        { pct: 12, msg: 'Verificando registros en la base de datos...' },
        { pct: 20, msg: 'Cargando catálogo de operadores...' },
        { pct: 30, msg: 'Identificando grupos por semana...' },
        { pct: 42, msg: 'Generando respaldos en PDF — Administradores...' },
        { pct: 55, msg: 'Generando respaldos en PDF — Producción...' },
        { pct: 68, msg: 'Eliminando registros respaldados de la BD...' },
        { pct: 80, msg: 'Guardando archivos en LOGS_GIS...' },
        { pct: 90, msg: 'Optimizando tabla de base de datos...' },
        { pct: 95, msg: 'Finalizando proceso...' },
    ];

    function setProgress(pct, msg) {
        if (progressBar) progressBar.style.transform = `scaleX(${pct / 100})`;
        if (statusText)  statusText.textContent  = msg;
    }

    function showOverlay() {
        if (overlay) overlay.style.display = 'flex';
        setProgress(5, 'Conectando con el servidor...');
    }

    function hideOverlay() {
        if (overlay) overlay.style.display = 'none';
        setProgress(0, '');
    }

    // Simula progreso visual mientras el servidor trabaja (el servidor no emite eventos SSE)
    // El avance se detiene en 95% y solo llega a 100% cuando el servidor responde.
    let progressInterval = null;
    function startFakeProgress() {
        let stepIndex = 0;
        progressInterval = setInterval(() => {
            if (stepIndex < progressSteps.length) {
                const step = progressSteps[stepIndex];
                setProgress(step.pct, step.msg);
                stepIndex++;
            } else {
                clearInterval(progressInterval);
            }
        }, 2200); // avanzar un paso cada ~2.2 segundos
    }

    function stopFakeProgress() {
        if (progressInterval) {
            clearInterval(progressInterval);
            progressInterval = null;
        }
    }

    if (btnPurge) {
        btnPurge.addEventListener('click', async () => {
            // Bloquear si ya hay una depuración en curso
            if (isPurging) return;

            const resConfirm = await (window.gisConfirm ? window.gisConfirm({
                title: '¿Depurar bitácora del sistema?',
                text: 'Se generarán respaldos PDF organizados por semana y se eliminarán permanentemente los registros de la base de datos.',
                confirmButtonText: 'Depurar',
                confirmButtonColor: '#b30404'
            }) : Promise.resolve({ isConfirmed: true }));

            if (!resConfirm.isConfirmed) return;

            // ── Iniciar proceso ──────────────────────────────────────────────
            isPurging = true;
            btnPurge.disabled = true;
            btnPurge.classList.add('purge-btn-loading');
            document.getElementById('purge-btn-text').textContent = 'En proceso...';

            showOverlay();
            startFakeProgress();

            let baseUrl = window.baseUrl || (window.location.origin + '/');
            if (!baseUrl.endsWith('/')) baseUrl += '/';
            const purgeUrl = baseUrl + 'system-logs/purge';

            try {
                const response = await fetch(purgeUrl, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    }
                });

                stopFakeProgress();
                const data = await response.json();

                if (data.success) {
                    setProgress(100, '✓ Depuración completada exitosamente.');
                    // Pequeña pausa para que el usuario vea el 100%
                    await new Promise(r => setTimeout(r, 1200));
                    hideOverlay();
                    if (window.gisAlert) {
                        await window.gisAlert({
                            title: 'Éxito',
                            text: data.message,
                            icon: 'success',
                            confirmButtonColor: '#0a8504'
                        });
                    } else if (window.toastpremium) {
                        window.toastpremium(data.message, 'success');
                    }
                    window.location.reload();
                } else {
                    setProgress(100, '✗ El servidor reportó un error.');
                    await new Promise(r => setTimeout(r, 800));
                    hideOverlay();
                    if (window.gisAlert) {
                        await window.gisAlert({
                            title: 'Error',
                            text: data.message,
                            icon: 'error',
                            confirmButtonColor: '#b30404'
                        });
                    } else if (window.toastpremium) {
                        window.toastpremium(data.message, 'error');
                    }
                }

            } catch (error) {
                stopFakeProgress();
                console.error('[GIS] Error al depurar logs:', error);
                setProgress(100, '✗ Error de conexión.');
                await new Promise(r => setTimeout(r, 800));
                hideOverlay();
                if (window.gisAlert) {
                    await window.gisAlert({
                        title: 'Error',
                        text: 'Ocurrió un error técnico al intentar depurar los logs. Revisa la consola para más detalles.',
                        icon: 'error',
                        confirmButtonColor: '#b30404'
                    });
                } else if (window.toastpremium) {
                    window.toastpremium('Ocurrió un error técnico al intentar depurar los logs.', 'error');
                }

            } finally {
                // ── Restaurar botón siempre, pase lo que pase ────────────────
                isPurging = false;
                btnPurge.disabled = false;
                btnPurge.classList.remove('purge-btn-loading');
                document.getElementById('purge-btn-text').textContent = 'Depurar Logs';
            }
        });
    }
});

/**
 * Función para añadir logs al final de la tabla sin borrar lo anterior
 */
function appendLogsToTable(newLogs) {
    // Reutilizar la lógica de renderizado por trozos (chunks) para no congelar el navegador
    crearTabla(newLogs, true);
}

function createFilters() {
    // Filtros que solo aplican a logs de operadores (no admin)
    const operatorOnlyFilters = ['ot', 'clase', 'maquina', 'proceso', 'audit_status', 'n_pieza'];

    let titles = {
        ot: "Orden de trabajo",
        clase: "Clase",
        operador: "Operador",
        maquina: "Maquina",
        proceso: "Proceso",
        audit_status: "Estado",
        action: "Acción",
        dateFrom: "Desde",
        dateTo: "Hasta",
        n_pieza: "N# Juego",
    };

    let container = document.querySelector(".filters");
    if (!container) return;
    container.innerHTML = ""; // Limpiar antes de crear

    Object.keys(titles).forEach((key) => {
        // En modo admin, omitir filtros exclusivos de operadores
        if (window.isAdminOnly && operatorOnlyFilters.includes(key)) return;

        let div = document.createElement("div");
        div.className = "filter filter-" + key;

        if (key === "dateFrom" || key === "dateTo") {
            createDateFilterTrigger(
                div,
                key,
                window.selectedItems[key] || "",
                titles[key] || "Fecha",
                () => document.getElementById("filters-form").submit()
            );
        } else {
            let select = document.createElement("select");
            select.name = key;
            select.className = "select-filter";
            if (key === "n_pieza") select.id = "n_juego_filter";

            let defaultOpt = document.createElement("option");
            defaultOpt.value = "Todos";
            defaultOpt.textContent = "Todos";
            select.appendChild(defaultOpt);

            if (key === "audit_status") {
                const statusOpts = [
                    { val: "Válidos", color: "#58D68D" },
                    { val: "Sospechosos", color: "#F1C40F" },
                    { val: "Críticos", color: "#943126" }
                ];
                statusOpts.forEach(item => {
                    let opt = document.createElement("option");
                    opt.value = item.val;
                    opt.textContent = item.val;
                    opt.style.backgroundColor = item.color;
                    opt.style.color = (item.color === "#943126" || item.color === "#21618C") ? "#FFFFFF" : "#000000";
                    opt.classList.add('font-bold');
                    if (window.selectedItems[key] === item.val) opt.selected = true;
                    select.appendChild(opt);
                });
            } else if (key === "action") {
                const actionStyles = {
                    // AZUL
                    "Carga de Formulario de Producción": { color: "#AED6F1" },
                    "Selección de Pieza": { color: "#AED6F1" },
                    "Selección de OT": { color: "#AED6F1" },
                    "Selección de Clase": { color: "#AED6F1" },
                    "Selección de Proceso": { color: "#AED6F1" },
                    "Abandono de Liberación": { color: "#D7BDE2" },
                    "Inicio de Sesión": { color: "#3498DB", dark: true },
                    "Nuevo reporte": { color: "#3498DB", dark: true },
                    "Inicio de Reporte": { color: "#3498DB", dark: true },
                    "Nueva Meta Creada": { color: "#3498DB", dark: true },
                    "Ingreso a Meta Existente": { color: "#3498DB", dark: true },
                    "Login Inspector Calidad": { color: "#21618C", dark: true },
                    "Cierre de Sesión": { color: "#21618C", dark: true },
                    "Desbloqueo de PC": { color: "#3498DB", dark: true },

                    // VERDE
                    "Proceso Correcto": { color: "#ABEBC6" },
                    "Captura Medida": { color: "#27AE60", dark: true },
                    "Captura Medida / Reporte": { color: "#27AE60", dark: true },
                    "Cargo de OT": { color: "#27AE60", dark: true },
                    "Cargo de Clase de OT": { color: "#27AE60", dark: true },
                    "Terminar Reporte": { color: "#186A3B", dark: true },
                    "Cargo/Modificación Cotas Nominales": { color: "#186A3B", dark: true },
                    "Desocupación de Máquina": { color: "#1A8C5F", dark: true },

                    // AMARILLO (AUDITORÍA / AUTORIZACIÓN)
                    "Consulta Documentación Técnica": { color: "#F9E79F" },
                    "Aviso de Sistema (Ventana)": { color: "#F9E79F" },
                    "Captura Sospechosa": { color: "#F1C40F" },
                    "Autorización de Edición": { color: "#9A7D0A", dark: true },
                    "Solicitud Edición de Reporte": { color: "#9A7D0A", dark: true },
                    "Modificación de OT": { color: "#9A7D0A", dark: true },

                    // NARANJA (DOCUMENTACIÓN - DIBUJOS / MANUALES / AYUDAS)
                    "Subida de Dibujo": { color: "#E67E22", dark: true },
                    "Eliminación de Dibujo": { color: "#CA6F1E", dark: true },
                    "Reemplazo de Dibujo": { color: "#F39C12", dark: true },
                    "Subida de Dibujo Fundición": { color: "#E67E22", dark: true },
                    "Eliminación de Dibujo Fundición": { color: "#CA6F1E", dark: true },
                    "Reemplazo de Dibujo Fundición": { color: "#F39C12", dark: true },
                    "Subida de Manual": { color: "#5DADE2", dark: true },
                    "Eliminación de Manual": { color: "#2E86C1", dark: true },
                    "Reemplazo de Manual": { color: "#2874A6", dark: true },
                    "Subida de Ayuda Visual": { color: "#A569BD", dark: true },
                    "Eliminación de Ayuda Visual": { color: "#7D3C98", dark: true },
                    "Reemplazo de Ayuda Visual": { color: "#6C3483", dark: true },
                    "Creación de Carpeta": { color: "#82E0AA", dark: false },

                    // MORADO
                    "Consulta Dibujos Técnicos": { color: "#D7BDE2" },
                    "Consulta Reporte de OT": { color: "#D7BDE2" },
                    "Solicitud Edición de Piezas": { color: "#8E44AD", dark: true },
                    "Intento de Liberación": { color: "#512E5F", dark: true },
                    "Liberación por Calidad": { color: "#D5F5E3" },
                    "Rechazo por Calidad": { color: "#FADBD8" },

                    // ROJO
                    "Exceso de Tiempo": { color: "#F5B7B1" },
                    "Exceso de Tiempo de Maquinado": { color: "#F5B7B1" },
                    "Inactividad en Formulario": { color: "#F5B7B1" },
                    "Inactividad en Bienvenida": { color: "#F5B7B1" },
                    "Alerta de Productividad": { color: "#F5B7B1" },
                    "Avisos de Sistema": { color: "#F5B7B1" },
                    "Alerta de Error en Sistema": { color: "#F5B7B1" },
                    "Error Inspector Calidad": { color: "#F5B7B1" },
                    "Intento de Login Fallido": { color: "#F5B7B1" },
                    "Mensaje de Error": { color: "#E74C3C", dark: true },
                    "Captura Crítica": { color: "#943126", dark: true },
                };

                const actionOrder = Object.keys(actionStyles);
                const availableActions = window.filtrosDisponibles[key] || [];

                // Acciones que NO aplican al reporte de administrador
                const adminExcludedActions = [
                    "Captura Sospechosa", "Autorización de Edición", "Captura Crítica",
                    "Carga de Formulario de Producción", "Selección de Pieza", "Selección de OT",
                    "Selección de Clase", "Selección de Proceso", "Abandono de Liberación",
                    "Nuevo reporte", "Inicio de Reporte", "Nueva Meta Creada", "Ingreso a Meta Existente",
                    "Login Inspector Calidad", "Desbloqueo de PC", "Proceso Correcto",
                    "Captura Medida", "Captura Medida / Reporte", "Terminar Reporte",
                    "Consulta Documentación Técnica", "Aviso de Sistema (Ventana)",
                    "Solicitud Edición de Reporte", "Consulta Dibujos Técnicos",
                    "Consulta Reporte de OT", "Solicitud Edición de Piezas",
                    "Intento de Liberación", "Liberación por Calidad", "Rechazo por Calidad",
                    "Exceso de Tiempo", "Exceso de Tiempo de Maquinado", "Inactividad en Formulario",
                    "Inactividad en Bienvenida", "Alerta de Productividad", "Avisos de Sistema",
                    "Alerta de Error en Sistema", "Error Inspector Calidad", "Intento de Login Fallido",
                    "Mensaje de Error"
                ];

                actionOrder.forEach(actionName => {
                    // En modo admin, omitir acciones de operadores
                    if (window.isAdminOnly && adminExcludedActions.includes(actionName)) return;

                    if (availableActions.includes(actionName) || (!window.isAdminOnly && (actionName === "Captura Sospechosa" || actionName === "Captura Crítica"))) {
                        let opt = document.createElement("option");
                        opt.value = actionName;
                        opt.textContent = actionName;

                        const style = actionStyles[actionName];
                        if (style) {
                            opt.style.backgroundColor = style.color;
                            opt.style.color = style.dark ? "#FFFFFF" : "#000000";
                            opt.classList.add('font-bold');
                        }

                        if (window.selectedItems[key] && window.selectedItems[key] == actionName) {
                            opt.selected = true;
                        }
                        select.appendChild(opt);
                    }
                });

                availableActions.forEach(actionName => {
                    if (!actionOrder.includes(actionName)) {
                        let opt = document.createElement("option");
                        opt.value = actionName;
                        opt.textContent = actionName;
                        if (window.selectedItems[key] && window.selectedItems[key] == actionName) {
                            opt.selected = true;
                        }
                        select.appendChild(opt);
                    }
                });
            } else {
                let filterData = window.filtrosDisponibles[key];
                if (filterData) {
                    filterData.forEach(item => {
                        let opt = document.createElement("option");
                        if (key === "operador") {
                            opt.value = item.matricula;
                            opt.textContent = `${item.matricula} - ${item.nombre} ${item.a_paterno}`;
                        } else {
                            opt.value = item;
                            opt.textContent = (key === "maquina") ? item.replace("_", " y ") : item;
                        }
                        if (window.selectedItems[key] && window.selectedItems[key] == opt.value) {
                            opt.selected = true;
                        }
                        select.appendChild(opt);
                    });
                }
            }

            select.addEventListener("change", () => document.getElementById("filters-form").submit());
            div.appendChild(select);
        }

        let label = document.createElement("label");
        // En modo admin, el filtro de operador se llama "Administrador"
        label.textContent = (key === 'operador' && window.isAdminOnly ? 'Administrador' : titles[key]) + ": ";
        div.appendChild(label);

        container.appendChild(div);
    });

    // Lógica para habilitar/deshabilitar N# Pieza
    const updateNPiezaState = () => {
        let otSel = document.querySelector('[name="ot"]');
        let claseSel = document.querySelector('[name="clase"]');
        let nPiezaSel = document.getElementById("n_juego_filter");

        if (otSel && claseSel && nPiezaSel) {
            const isBlocked = (otSel.value === "Todos" || claseSel.value === "Todos");
            nPiezaSel.disabled = isBlocked;
            if (isBlocked) nPiezaSel.value = "Todos";
        }
    };

    // Botón Limpiar Filtros
    let divClear = document.createElement("div");
    divClear.className = "filter filter-btn-wrapper";

    let btnClear = document.createElement("button");
    btnClear.id = "btnClearFilters";
    btnClear.textContent = "Limpiar Filtros";
    btnClear.className = "btns btn-clear-filters";
    btnClear.type = "button";
    divClear.appendChild(btnClear);

    // Función para verificar el estado de los filtros
    const updateClearButtonState = () => {
        const selects = document.querySelectorAll(".select-filter");
        const inputs = document.querySelectorAll(".input-filter");
        let hasActiveFilter = false;

        selects.forEach(s => { if (s.value && s.value !== "Todos" && s.value !== "") hasActiveFilter = true; });
        inputs.forEach(i => { if (i.value && i.value !== "" && i.value !== "Todos") hasActiveFilter = true; });

        btnClear.disabled = !hasActiveFilter;
    };
    window.updateClearButtonState = updateClearButtonState;

    btnClear.addEventListener("click", () => {
        document.querySelectorAll(".select-filter").forEach(s => s.value = "Todos");
        document.querySelectorAll(".input-filter").forEach(i => i.value = "");
        document.querySelectorAll(".date-trigger-label").forEach(l => {
            l.textContent = "dd/mm/aaaa";
            l.style.color = "#64748b";
            l.style.fontWeight = "500";
            l.style.flex = "1 1 auto";
            l.style.width = "100%";
            l.style.maxWidth = "100%";
            l.classList.add("is-empty");
        });
        document.querySelectorAll(".btn-clear-single-date").forEach(b => {
            b.style.display = "none";
        });
        updateNPiezaState();
        updateClearButtonState();
        document.getElementById("filters-form").submit();
    });

    // Estado inicial inmediato
    updateClearButtonState();

    // Escuchar cambios en los filtros para actualizar el estado de los botones
    setTimeout(() => {
        document.querySelectorAll(".select-filter, .input-filter").forEach(el => {
            el.addEventListener("change", () => {
                updateNPiezaState();
                updateClearButtonState();
            });
            el.addEventListener("input", () => {
                updateNPiezaState();
                updateClearButtonState();
            });
        });
        updateNPiezaState();
        updateClearButtonState();
    }, 100);

    container.appendChild(divClear);
}

let currentRenderId = null;

function crearTabla(logs, append = false) {
    if (currentRenderId && !append) {
        cancelAnimationFrame(currentRenderId);
    }

    // Tracker para detectar piezas que completan un juego (M + H)
    const piezasTracker = new Set();

    const table = document.querySelector(".table");
    let tbody = table.querySelector("tbody");
    if (!tbody) {
        tbody = document.createElement("tbody");
        table.appendChild(tbody);
    } else if (!append) {
        tbody.innerHTML = "";
    }

    // Función para asignar color vivo u oscuro según palabras clave en la descripción
    function getColorFromDescription(desc, action) {
        const text = ((action || '') + ' ' + (desc || '')).toLowerCase();
        
        // Críticos / Fallas graves / Bloqueos
        if (text.includes('crític') || text.includes('recurrente') || text.includes('fallid') || text.includes('bloqueo')) {
            return "#943126"; // Rojo Oscuro
        }
        // Errores / Alertas / Inactividad / Exceso / Rechazos
        if (text.includes('error') || text.includes('falla') || text.includes('inactivid') || text.includes('exceso') || text.includes('rechaz') || text.includes('alerta')) {
            return "#E74C3C"; // Rojo Brillante
        }
        // Advertencias / Sospechas / Tolerancia
        if (text.includes('sospech') || text.includes('advertencia') || text.includes('tolerancia')) {
            return "#F1C40F"; // Amarillo Brillante
        }
        // Autorizaciones / Supervisor / Edición / Modificación
        if (text.includes('autoriz') || text.includes('supervisor') || text.includes('modific') || text.includes('edici')) {
            return "#9A7D0A"; // Ocre / Ámbar Oscuro
        }
        // Término / Cotas nominales
        if (text.includes('terminar') || text.includes('término') || text.includes('cota nominal')) {
            return "#145A32"; // Verde Oscuro
        }
        // Liberación / Medida / Producción / Éxito
        if (text.includes('libera') || text.includes('medida') || text.includes('producci') || text.includes('éxito') || text.includes('segunda pasada') || text.includes('cargo de')) {
            return "#27AE60"; // Verde Brillante
        }
        // Desocupación de máquina
        if (text.includes('desocup') || text.includes('máquina')) {
            return "#1A8C5F"; // Verde Medio
        }
        // Logout / Cierre
        if (text.includes('logout') || text.includes('cierre') || text.includes('salida')) {
            return "#154360"; // Azul Marino Oscuro
        }
        // Login / Acceso / Inicio / Desbloqueo
        if (text.includes('login') || text.includes('inicio') || text.includes('desbloqueo') || text.includes('sesi') || text.includes('ingreso')) {
            return "#2980B9"; // Azul Brillante
        }
        // Planos / Dibujos
        if (text.includes('plano') || text.includes('dibujo')) {
            return "#E67E22"; // Naranja Brillante
        }
        // Manuales
        if (text.includes('manual')) {
            return "#2874A6"; // Azul Cian
        }
        // Ayudas Visuales
        if (text.includes('ayuda visual') || text.includes('ayuda')) {
            return "#7D3C98"; // Morado Medio
        }
        // Carpetas
        if (text.includes('carpeta')) {
            return "#229954"; // Verde Esmeralda
        }
        // Navegación / Sistema
        if (text.includes('navega') || text.includes('interfaz') || text.includes('sistema')) {
            return "#334155"; // Pizarra Oscuro
        }
        // Default vivo/oscuro
        return "#1B4F72"; // Azul Oscuro GIS
    }

    const actionColors = {
        // FAMILIA AZUL (Acceso / Sesión) - Vivos y Oscuros
        "Carga de Formulario de Producción": "#1B4F72", // Azul Oscuro
        "Inicio de Sesión": "#2980B9", // Azul Brillante
        "Login Operador": "#2980B9",
        "Carga de Reporte": "#2980B9",
        "Inicio de Reporte": "#2980B9",
        "Nuevo reporte": "#2980B9",
        "Nueva Meta Creada": "#2980B9",
        "Ingreso a Meta Existente": "#2980B9",
        "Login Inspector Calidad": "#154360", // Azul Marino Oscuro
        "Cierre de Sesión": "#154360",
        "Logout": "#154360",
        "Desbloqueo de PC": "#2980B9",

        // FAMILIA GRIS / PIZARRA (Neutral / Interfaz) - Tonos oscuros elegantes
        "Navegación": "#334155", // Pizarra Oscuro
        "Carga de Interfaz": "#334155",
        "Selección de Pieza": "#334155",
        "Selección de OT": "#334155",
        "Selección de Clase": "#334155",
        "Selección de Proceso": "#334155",
        "Aceptación Alerta": "#475569",
        "Aviso de Sistema (Ventana)": "#475569",
        "Keep Alive": "#1E293B",
        "Sincronización Automática": "#1E293B",

        // FAMILIA VERDE (Éxito / Producción) - Verdes Vivos y Oscuros
        "Proceso Correcto": "#27AE60", // Verde Brillante
        "Segunda Pasada PTA": "#1E8449", // Verde Brillante Intenso
        "Captura Medida": "#27AE60",
        "Captura de Medida": "#27AE60",
        "Captura Medida / Reporte": "#27AE60",
        "Guardado de Liberaciones": "#27AE60",
        "Cargo de OT": "#27AE60",
        "Cargo de Clase de OT": "#27AE60",
        "Liberación por Calidad": "#27AE60",
        "Terminar Reporte": "#145A32", // Verde Oscuro Bosque
        "Término de Reporte": "#145A32",
        "Cargo/Modificación Cotas Nominales": "#145A32",
        "Desocupación de Máquina": "#1A8C5F", // Verde Medio
        "Creación de Carpeta": "#229954", // Verde Esmeralda

        // FAMILIA AMARILLA / OCRE (Auditoría / Autorización)
        "Avisos de Sistema": "#B7950B", // Ámbar Oscuro
        "Documentación Técnica": "#B7950B",
        "Consulta Documentación Técnica": "#B7950B",
        "Captura Sospechosa": "#F1C40F", // Amarillo Brillante
        "Edición de Reporte": "#9A7D0A", // Ocre Oscuro
        "Autorización de Supervisor": "#9A7D0A",
        "Solicitud Edición de Reporte": "#9A7D0A",
        "Autorización de Edición": "#9A7D0A",
        "Modificación de OT": "#9A7D0A",

        // FAMILIA MORADA (Dibujos / Calidad / Auditoría)
        "Visor de Planos": "#8E44AD", // Morado Brillante
        "Dibujos Técnicos": "#8E44AD",
        "Consulta Dibujos Técnicos": "#8E44AD",
        "Consulta Reporte de OT": "#8E44AD",
        "Cambio de Catálogo": "#8E44AD",
        "Edición de Piezas en Reporte": "#8E44AD",
        "Solicitud Edición de Piezas": "#8E44AD",
        "Abandono de Liberación": "#8E44AD",
        "Intento de Liberación de Calidad": "#512E5F", // Morado Oscuro
        "Intento de Liberación": "#512E5F",
        "Subida de Ayuda Visual": "#7D3C98", // Morado Medio
        "Eliminación de Ayuda Visual": "#7D3C98",
        "Reemplazo de Ayuda Visual": "#6C3483",

        // FAMILIA ROJA (Fallas / Alertas / Productividad) - Rojos Vivos y Oscuros
        "Excedió el límite de inactividad": "#C0392B", // Rojo Alerta
        "Inactividad en Formulario": "#C0392B",
        "Inactividad en Bienvenida": "#C0392B",
        "Inicio de Reporte Pendiente": "#C0392B",
        "Alerta de Productividad": "#C0392B",
        "Exceso de Tiempo": "#C0392B",
        "Exceso de Tiempo de Maquinado": "#C0392B",
        "Error Técnico de Sistema": "#E74C3C", // Rojo Brillante
        "Mensaje de Error": "#E74C3C",
        "Alerta de Error en Sistema": "#E74C3C",
        "Error Inspector Calidad": "#E74C3C",
        "Rechazo por Calidad": "#E74C3C",
        "Intento de Login Fallido": "#943126", // Rojo Oscuro
        "Intento Fallido de Acceso": "#943126",
        "Captura Crítica": "#943126",

        // FAMILIA NARANJA (Documentación - Dibujos)
        "Subida de Dibujo": "#E67E22", // Naranja Brillante
        "Eliminación de Dibujo": "#CA6F1E", // Naranja Oscuro
        "Reemplazo de Dibujo": "#F39C12", // Ámbar Brillante
        "Subida de Dibujo Fundición": "#E67E22",
        "Eliminación de Dibujo Fundición": "#CA6F1E",
        "Reemplazo de Dibujo Fundición": "#F39C12",

        // FAMILIA AZUL MANUALES (Manuales)
        "Subida de Manual": "#2874A6",
        "Eliminación de Manual": "#1F618D",
        "Reemplazo de Manual": "#2471A3",

        "Default": "#1B4F72"
    };

    const CHUNK_SIZE = 500;
    let index = 0;
    const esc = (v) => v ? String(v).replace(/"/g, '&quot;').replace(/>/g, '&gt;').replace(/</g, '&lt;') : '';

    function renderNextChunk() {
        let chunkHtml = "";
        const limit = Math.min(index + CHUNK_SIZE, logs.length);

        for (; index < limit; index++) {
            let log = logs[index];

            // Lógica de color por Acción o Descripción
            let color = actionColors[log.action];
            if (!color || color === "#FFFFFF") {
                color = getColorFromDescription(log.details, log.action);
            }

            // Lógica de sospecha (Nivel 2 - Amarillo: Advertencia)
            if (log.action === "Captura Medida" && log.is_suspicious) {
                color = "#F1C40F"; // Amarillo Brillante
            }

            // Lógica Crítica (Nivel 3 - Rojo Oscuro: Problema Recurrente)
            if (log.is_critical) {
                color = "#943126"; // Rojo Oscuro
            }

            // Lógica de Exceso de Tiempo (Prioridad para la acción "Exceso de Tiempo" o duraciones largas)
            if (log.action === "Exceso de Tiempo") {
                color = "#C0392B"; // Rojo Alerta
            } else if (log.tiempo_total && log.tiempo_total !== 'N/A' && log.tiempo_total !== '00:00:00') {
                const parts = log.tiempo_total.split(':');
                if (parseInt(parts[0]) >= 2) { // 2 horas o más
                    color = "#C0392B";
                }
            }

            // Determinar contraste: Fondos brillantes como amarillo llevan texto oscuro; fondos vivos u oscuros llevan blanco
            const isLightBg = ["#F1C40F", "#F39C12", "#2ECC71"].includes(color.toUpperCase());
            const isDark = !isLightBg;
            const textColor = isDark ? "#FFFFFF" : "#0f172a";

            // LÓGICA MANUAL DE ALTO CONTRASTE (Colores curados para cada fondo)
            const getManualHLColor = (hex) => {
                const bg = hex.toUpperCase();
                const map = {
                    // FONDOS CLAROS (Colores oscuros para destacar)
                    "#D5F5E3": "#922B21",
                    "#F5B7B1": "#922B21",
                    "#FADBD8": "#922B21",
                    "#F9E79F": "#922B21",
                    "#D4E6F1": "#1B4F72",
                    "#FCF3CF": "#9A7D0A",
                    "#F1C40F": "#922B21",
                    "#AED6F1": "#1B4F72",
                    "#ABEBC6": "#145A32",
                    "#82E0AA": "#145A32",
                    "#F39C12": "#78281F",
                    "#FAD7A0": "#78281F",
                    "#F8C471": "#78281F",
                    "#FDC89A": "#78281F",

                    // FONDOS OSCUROS (Colores brillantes para destacar)
                    "#186A3B": "#F1C40F",
                    "#21618C": "#F1C40F",
                    "#512E5F": "#F1C40F",
                    "#943126": "#FAD7A0",
                    "#E74C3C": "#F1C40F",
                    "#27AE60": "#F1C40F",
                    "#3498DB": "#F1C40F",
                    "#8E44AD": "#F4D03F",
                    "#6C3483": "#F4D03F",
                    "#7D3C98": "#F4D03F",
                    "#9A7D0A": "#F9E79F",
                    "#E67E22": "#F9E79F",
                    "#CA6F1E": "#F9E79F",
                    "#2E86C1": "#F9E79F",
                    "#2874A6": "#F9E79F",
                    "#1A8C5F": "#F9E79F",
                    "#515A5A": "#F9E79F",
                    "#7F8C8D": "#FFFFFF",
                    "#A6ACAF": "#000000"
                };
                return map[bg] || (isDark ? "#F1C40F" : "#1B4F72");
            };

            const hlColor = getManualHLColor(color);
            const userHL = isDark ? "#FFFFFF" : "#0B5345";

            // Lógica de visualización de detalles (formateo de HTML y limpieza)
            let detailsHtml = (() => {
                let content = log.details || "";

                // 1. Limpieza de rastro de depuración y alertas repetitivas
                content = content.replace(/\[BLOQUEO\] Operador \d+ /i, '');
                content = content.replace(/ALERTA: Tiempo insuficiente entre (piezas|juegos) diferentes \(\d+ min\)/gi, '');
                content = content.replace(/ALERTA CRÍTICA: Problema recurrente de llenado. Reincidencia detectada. \(\d+ min\)/gi, '');

                // Detectar si el backend ya envió HTML para evitar doble escape
                const hasHtml = content.includes('<span') || content.includes('<b>');
                if (!hasHtml) {
                    content = esc(content);
                }

                // 2. Acciones de Productividad y Bloqueos (Prioridad)
                content = content.replace(/(reconoció y aceptó la alerta de|Inactividad en|Exceso de Tiempo en)\s+([^.]+?)(?=\.|\stras\s| por | durante | desde |$)/gi, `$1 <b style="color:${hlColor}; font-weight:700;">$2</b>`);

                // 3. Resaltar nombres de usuarios
                const nameRegex = /(El inspector de calidad|El operador|El inspector|El usuario|El supervisor|El administrador)\s+(?:<b>)?([^.<]+?)(?:<\/b>)?\s+(finalizó|registró|sincronizó|completó|inició|seleccionó|autorizó|solicitó|canceló|abrió|realizó|cerró|ingresó)/gi;
                content = content.replace(nameRegex, `$1 <strong style="color:${userHL}; font-weight:700;">$2</strong> $3`);

                // 4. Resaltado de metadatos técnicos (OT, Clase, Máquina, Piezas)
                content = content.replace(/\bOT\b:?\s*(\d+)/gi, `<b style="color:${hlColor}; font-weight:800;">OT $1</b>`);
                content = content.replace(/\bClase\b:?\s*([^,)\n<]+)/gi, `<b style="color:${hlColor}; font-weight:800;">Clase $1</b>`);
                content = content.replace(/\bMaquina\b:?\s*([^,)\n<]+)/gi, `<b style="color:${hlColor}; font-weight:800;">Máquina $1</b>`);
                content = content.replace(/\b(juego|pieza)\b:?\s*(\d+[a-zA-Z]?)/gi, `<b style="color:${hlColor}; font-weight:800;">$1 $2</b>`);

                // 5. Resaltado de Mensajes de Sistema y Acciones Genéricas
                content = content.replace(/(mensaje al operador|Autenticación correcta|Autenticación fallida|interfaz de|formulario de|acceso a|reporte de|sistema de|meta|contraseña de|solicitud de|registro de|edición de):?\s*([^.<]+?)(?=\.|$)/gi, (match, p1, p2) => {
                    return `${p1} <b style="color:${hlColor}; font-weight:700;">${p2}</b>`;
                });

                // Resaltar palabras clave de estado/lugar en descripciones genéricas (NUNCA amarillo fluorescente)
                content = content.replace(/(?<![">])\b(el sistema|la meta|el reporte|su turno|la máquina|el formulario|reporte de producción|acceso a edición)\b/gi, `<b style="color:${hlColor}; font-weight:700;">$1</b>`);

                // 6. Lógica específica para Reportes de Producción (Proceso Correcto)
                const greenHL = isDark ? '#6ee7b7' : '#0e6238';
                const redHL = isDark ? '#fca5a5' : '#922b21';
                const yellowHL = isDark ? '#fde047' : '#8a6d0b';

                if (log.action === "Proceso Correcto" && (content.includes("finalizó la revisión:") || content.includes("realizó:"))) {
                    if (!hasHtml) {
                        content = content.replace(/Liberadas: (\d+)( \[[^\]]+\])?/g, `<span style="color:${greenHL}; font-weight:700;">Liberadas: $1 <small>$2</small></span>`);
                        content = content.replace(/Rechazadas: (\d+)( \[[^\]]+\])?/g, `<span style="color:${redHL}; font-weight:700; margin-left:12px;">Rechazadas: $1 <small>$2</small></span>`);
                        content = content.replace(/Incompletas: (\d+)( \[[^\]]+\])?/g, `<span style="color:${yellowHL}; font-weight:700; margin-left:12px;">Incompletas: $1 <small>$2</small></span>`);
                    }
                }

                // 7. Resaltado de estados de Calidad en los detalles narrativos
                content = content.replace(/LIBERAD[AO]/gi, (match) => `<b style="color:${greenHL}; font-weight:800;">${match}</b>`);
                content = content.replace(/RECHAZAD[AO]/gi, (match) => `<b style="color:${redHL}; font-weight:800;">${match}</b>`);

                // Resaltar frases de resultados de calidad
                content = content.replace(/(liberó el juego|liberaron los juegos)\s*(\[.*?\])/gi, `$1 <b style="color:${greenHL}; font-weight:700;">$2</b>`);
                content = content.replace(/(rechazó el juego|fue rechazado el juego|fueron rechazados los juegos)\s*(\[.*?\])/gi, `$1 <b style="color:${redHL}; font-weight:700;">$2</b>`);
                content = content.replace(/(incompletos los juegos|registrados como incompletas)\s*(\[.*?\])?/gi, `$1 <b style="color:${yellowHL}; font-weight:700;">$2</b>`);

                // 8. Resaltar conectores de lugar/proceso genéricos
                content = content.replace(/(?<![">])\ben\s+([^.<]+?)(?=\.|\stras\s| por | durante | desde |$)/gi, `en <b style="color:${hlColor}; font-weight:700;">$1</b>`);

                // 9. Resaltar Duraciones y Tiempos (tras X min Y seg)
                content = content.replace(/(tras|durante|desde)\s+(\d+\s*(?:seg|min|hr|h|m|s)[^.<]*)(?=\.|$)/gi, `$1 <b style="color:${hlColor}; font-weight:700;">$2</b>`);

                content = content.replace(/Obs: (.*)$/g, `Obs: <b style="color:${isDark ? '#e9d5ff' : '#512e5f'}; font-weight:700;">$1</b>`);

                return content;
            })();

            // LÓGICA DE ALERTAS DE AUDITORÍA
            let auditAlert = "";
            const criticalColor = isDark ? '#fca5a5' : '#78281f';
            const warningColor = isDark ? '#fde047' : '#922b21';
            const alertBg = isDark ? 'rgba(0,0,0,0.35)' : 'rgba(255,255,255,0.6)';

            if (log.is_critical) {
                auditAlert = `<br><strong style="color: ${criticalColor}; background: ${alertBg}; padding: 2px 8px; border-radius: 4px; font-size:1.02em; display: inline-block; margin-top: 5px;">ALERTA CRÍTICA: Problema recurrente de llenado. Reincidencia detectada. (${log.diff_mins ?? 0} min)</strong>`;
            } else if (log.action === "Captura Sospechosa" || (log.action === "Captura Medida" && log.is_suspicious)) {
                auditAlert = `<br><strong style="color: ${warningColor}; background: ${alertBg}; padding: 2px 8px; border-radius: 4px; display: inline-block; margin-top: 5px;">ALERTA: Tiempo insuficiente entre juegos diferentes (${log.diff_mins ?? 0} min)</strong>`;
            }

            // Lógica de visualización de N# Juego
            let piezaDisplay = (() => {
                let rawPieza = log.n_juego ? String(log.n_juego).trim().toUpperCase() : "";
                if (!rawPieza || rawPieza === "N/A") return "N/A";
                if (rawPieza.includes(',') || rawPieza.endsWith('H') || rawPieza.endsWith('M') || rawPieza.endsWith('J')) return rawPieza;
                let numero = rawPieza.replace(/[^0-9]/g, '');
                return numero ? numero + "J" : rawPieza;
            })();

            chunkHtml += `
            <tr style="background-color: ${color}; color: ${textColor}; font-weight: 600;"
                data-action="${esc(log.action)}">
                <td>${esc(log.date)}</td>
                <td>${esc(log.time)}</td>
                <td>${esc(log.operador)} - ${esc(log.operador_nombre)}</td>
                <td>${log.is_critical ? 'Captura Crítica' : (log.action === 'Inicio de Reporte Pendiente' ? 'Inactividad en Bienvenida' : esc(log.action))}</td>
                <td>${detailsHtml}${auditAlert}</td>
                ${window.isAdminOnly ? '' : `
                <td class="col-operador-only-td">${esc(log.ot)}</td>
                <td class="col-operador-only-td">${piezaDisplay}</td>
                <td class="col-operador-only-td">${esc(log.hora_inicio)}</td>
                <td class="col-operador-only-td">${esc(log.hora_termino)}</td>
                <td class="col-operador-only-td">${esc(log.tiempo_total)}</td>
                <td class="col-operador-only-td">${esc(log.clase)}</td>
                <td class="col-operador-only-td">${esc(log.proceso)}</td>
                <td class="col-operador-only-td">${esc(log.maquina)}</td>
                `}
            </tr>`;

        }

        tbody.innerHTML += chunkHtml;

        if (index < logs.length) {
            currentRenderId = requestAnimationFrame(renderNextChunk);
        } else {
            currentRenderId = null;
        }
    }

    renderNextChunk();
}
