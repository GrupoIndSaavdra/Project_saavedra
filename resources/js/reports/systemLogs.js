document.addEventListener("DOMContentLoaded", () => {
    if (window.logsData && window.logsData.length > 0) {
        crearTabla(window.logsData);
    }
    createFilters();

    // Lógica para colapsar/expandir niveles en la tabla de colores
    document.querySelectorAll(".level-toggle").forEach(header => {
        header.addEventListener("click", () => {
            const level = header.getAttribute("data-level");
            const targetRows = document.querySelectorAll(`.level-row.level-${level}`);
            const targetArrow = header.querySelector(".arrow");
            const isExpanding = targetRows[0].style.display === "none";

            // Cerrar todos los niveles primero
            document.querySelectorAll(".level-row").forEach(row => {
                row.style.display = "none";
            });
            document.querySelectorAll(".arrow").forEach(arrow => {
                arrow.textContent = "▶";
            });

            // Si estábamos expandiendo, abrir solo el objetivo
            if (isExpanding) {
                targetRows.forEach(row => {
                    row.style.display = "table-row";
                });
                targetArrow.textContent = "▼";
            }
        });
    });

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
            btnLoadMore.style.opacity = '0.7';

            try {
                const response = await fetch(window.nextPageUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await response.json();

                if (data.logsData && data.logsData.length > 0) {
                    // Usar la función existente para renderizar los nuevos registros (Append)
                    appendLogsToTable(data.logsData);
                    
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
                        loadMoreContainer.style.display = 'none';
                    }
                }
            } catch (error) {
                console.error('Error al cargar más logs:', error);
                alert('No se pudieron cargar más registros. Intente de nuevo.');
            } finally {
                btnLoadMore.disabled = false;
                btnLoadMore.textContent = 'Cargar más registros...';
                btnLoadMore.style.opacity = '1';
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
        let div = document.createElement("div");
        div.className = "filter filter-" + key;

        if (key === "dateFrom" || key === "dateTo") {
            let input = document.createElement("input");
            input.type = "date";
            input.name = key;
            input.className = "input-filter";
            input.value = window.selectedItems[key] || "";
            input.addEventListener("change", () => document.getElementById("filters-form").submit());
            div.appendChild(input);
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
                    opt.style.fontWeight = "bold";
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

                    // VERDE
                    "Proceso Correcto": { color: "#ABEBC6" },
                    "Captura Medida": { color: "#27AE60", dark: true },
                    "Captura Medida / Reporte": { color: "#27AE60", dark: true },
                    "Terminar Reporte": { color: "#186A3B", dark: true },

                    // AMARILLO (AUDITORÍA / AUTORIZACIÓN)
                    "Consulta Documentación Técnica": { color: "#F9E79F" },
                    "Aviso de Sistema (Ventana)": { color: "#F9E79F" },
                    "Captura Sospechosa": { color: "#F1C40F" },
                    "Autorización de Edición": { color: "#9A7D0A", dark: true },
                    "Solicitud Edición de Reporte": { color: "#9A7D0A", dark: true },

                    // MORADO
                    "Consulta Dibujos Técnicos": { color: "#D7BDE2" },
                    "Solicitud Edición de Piezas": { color: "#8E44AD", dark: true },
                    "Intento de Liberación": { color: "#512E5F", dark: true },
                    "Liberación por Calidad": { color: "#D5F5E3" },
                    "Rechazo por Calidad": { color: "#FADBD8" },

                    // ROJO
                    "Exceso de Tiempo": { color: "#F5B7B1" },
                    "Exceso de Tiempo de Maquinado": { color: "#F5B7B1" },
                    "Inactividad en Formulario": { color: "#F5B7B1" },
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

                actionOrder.forEach(actionName => {
                    if (availableActions.includes(actionName) || actionName === "Captura Sospechosa" || actionName === "Captura Crítica") {
                        let opt = document.createElement("option");
                        opt.value = actionName;
                        opt.textContent = actionName;

                        const style = actionStyles[actionName];
                        if (style) {
                            opt.style.backgroundColor = style.color;
                            opt.style.color = style.dark ? "#FFFFFF" : "#000000";
                            opt.style.fontWeight = "bold";
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
        label.textContent = titles[key] + ": ";
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
    let btnClear = document.createElement("button");
    btnClear.id = "btnClearFilters";
    btnClear.textContent = "Limpiar Filtros";
    btnClear.className = "btns btn-clear-filters";
    btnClear.type = "button";

    // Función para verificar el estado de los filtros
    const updateClearButtonState = () => {
        const selects = document.querySelectorAll(".select-filter");
        const inputs = document.querySelectorAll(".input-filter");
        let hasActiveFilter = false;

        selects.forEach(s => { if (s.value !== "Todos") hasActiveFilter = true; });
        inputs.forEach(i => { if (i.value !== "") hasActiveFilter = true; });

        btnClear.disabled = !hasActiveFilter;
    };

    btnClear.addEventListener("click", () => {
        document.querySelectorAll(".select-filter").forEach(s => s.value = "Todos");
        document.querySelectorAll(".input-filter").forEach(i => i.value = "");
        updateNPiezaState();
        document.getElementById("filters-form").submit();
    });

    // Escuchar cambios en los filtros para actualizar el estado de los botones
    setTimeout(() => {
        document.querySelectorAll(".select-filter, .input-filter").forEach(el => {
            el.addEventListener("change", () => {
                updateNPiezaState();
                updateClearButtonState();
            });
        });
        updateNPiezaState();
        updateClearButtonState();
    }, 100);

    container.appendChild(btnClear);
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

    // Actualizar contador inicial si es carga base
    if (!append) {
        const currentCountSpan = document.getElementById('current-count');
        if (currentCountSpan) currentCountSpan.textContent = logs.length;
    }

    const actionColors = {
        // FAMILIA AZUL (Acceso / Sesión)
        "Carga de Formulario de Producción": "#AED6F1", // Azul Claro (Inicio Trabajo)
        "Inicio de Sesión": "#3498DB", // Azul Normal (Reporte)
        "Login Operador": "#3498DB",
        "Carga de Reporte": "#3498DB",
        "Inicio de Reporte": "#3498DB",
        "Nuevo reporte": "#3498DB",
        "Nueva Meta Creada": "#3498DB",
        "Ingreso a Meta Existente": "#3498DB",
        "Login Inspector Calidad": "#21618C", // Azul Oscuro (Audit)
        "Cierre de Sesión": "#21618C",
        "Logout": "#21618C",

        // FAMILIA GRIS (Neutral / Interfaz)
        "Navegación": "#A6ACAF", // Gris Claro (Ruido)
        "Carga de Interfaz": "#A6ACAF",
        "Selección de Pieza": "#A6ACAF",
        "Selección de OT": "#A6ACAF",
        "Selección de Clase": "#A6ACAF",
        "Selección de Proceso": "#A6ACAF",
        "Aceptación Alerta": "#7F8C8D", // Gris Normal (Pasivo)
        "Aviso de Sistema (Ventana)": "#7F8C8D",
        "Keep Alive": "#515A5A", // Gris Oscuro (Sistema/Fondo)
        "Sincronización Automática": "#515A5A",

        // FAMILIA VERDE (Éxito / Producción)
        "Proceso Correcto": "#ABEBC6", // Verde Claro
        "Segunda Pasada PTA": "#ABEBC6", // Verde Claro (Soldadura)
        "Captura Medida": "#27AE60", // Verde Normal
        "Captura de Medida": "#27AE60",
        "Captura Medida / Reporte": "#27AE60",
        "Guardado de Liberaciones": "#27AE60",
        "Terminar Reporte": "#186A3B", // Verde Oscuro
        "Término de Reporte": "#186A3B",

        // FAMILIA AMARILLA / OCRE (Auditoría / Autorización)
        "Avisos de Sistema": "#F9E79F", // Amarillo Claro (Avisos)
        "Documentación Técnica": "#F9E79F",
        "Consulta Documentación Técnica": "#F9E79F",
        "Captura Sospechosa": "#F1C40F", // Amarillo Normal (Advertencia)
        "Edición de Reporte": "#9A7D0A", // Amarillo Oscuro (Autorizaciones)
        "Autorización de Supervisor": "#9A7D0A",
        "Solicitud Edición de Reporte": "#9A7D0A",
        "Autorización de Edición": "#9A7D0A",

        // FAMILIA MORADA (Dibujos / Edición)
        "Visor de Planos": "#D7BDE2", // Morado Claro
        "Dibujos Técnicos": "#D7BDE2",
        "Consulta Dibujos Técnicos": "#D7BDE2",
        "Cambio de Catálogo": "#D7BDE2",
        "Edición de Piezas en Reporte": "#8E44AD", // Morado Normal
        "Solicitud Edición de Piezas": "#8E44AD",
        "Liberación por Calidad": "#D5F5E3", // Verde Claro (Éxito Calidad)
        "Rechazo por Calidad": "#FADBD8",   // Rojo Claro (Falla Calidad)
        "Intento de Liberación de Calidad": "#512E5F", // Morado Oscuro
        "Intento de Liberación": "#512E5F",

        // FAMILIA ROJA (Fallas / Alertas / Productividad)
        "Excedió el límite de inactividad": "#F5B7B1", // Rojo Claro (Productividad)
        "Inactividad en Formulario": "#F5B7B1",
        "Alerta de Productividad": "#F5B7B1",
        "Exceso de Tiempo": "#F5B7B1",
        "Exceso de Tiempo de Maquinado": "#F5B7B1",
        "Abandono de Liberación": "#F5B7B1",
        "Error Técnico de Sistema": "#E74C3C", // Rojo Normal (Errores)
        "Mensaje de Error": "#E74C3C",
        "Alerta de Error en Sistema": "#E74C3C",
        "Error Inspector Calidad": "#E74C3C",
        "Intento de Login Fallido": "#E74C3C",
        "Intento Fallido de Acceso": "#E74C3C",
        "Captura Crítica": "#943126", // Rojo Oscuro (Problema Recurrente)

        "Default": "#FFFFFF"
    };

    const CHUNK_SIZE = 500;
    let index = 0;
    const esc = (v) => v ? String(v).replace(/"/g, '&quot;').replace(/>/g, '&gt;').replace(/</g, '&lt;') : '';

    function renderNextChunk() {
        let chunkHtml = "";
        const limit = Math.min(index + CHUNK_SIZE, logs.length);

        for (; index < limit; index++) {
            let log = logs[index];

            // Lógica de color por Acción
            let color = actionColors[log.action] || actionColors["Default"];

            // Lógica de sospecha (Nivel 2 - Amarillo: Advertencia)
            if (log.action === "Captura Medida" && log.is_suspicious) {
                color = "#F1C40F"; // Amarillo Normal
            }

            // Lógica Crítica (Nivel 3 - Rojo Oscuro: Problema Recurrente)
            if (log.is_critical) {
                color = "#943126"; // Rojo Oscuro
            }

            // Determinar contraste (si el fondo es oscuro, texto blanco; si es claro, texto negro)
            const darkColors = ["#3498DB", "#21618C", "#27AE60", "#186A3B", "#E74C3C", "#943126", "#9A7D0A", "#8E44AD", "#6C3483", "#512E5F", "#A6ACAF", "#7F8C8D", "#515A5A"];

            // Lógica de Exceso de Tiempo (Prioridad para la acción "Exceso de Tiempo" o duraciones largas)
            if (log.action === "Exceso de Tiempo") {
                color = "#FADBD8"; // Rojo Claro siempre para esta acción
            } else if (log.tiempo_total && log.tiempo_total !== 'N/A' && log.tiempo_total !== '00:00:00') {
                const parts = log.tiempo_total.split(':');
                if (parseInt(parts[0]) >= 2) { // 2 horas o más
                    color = "#FADBD8"; // Rojo Claro
                }
            }

            const isDark = darkColors.includes(color.toUpperCase());
            const textColor = isDark ? "#FFFFFF" : "#000000";

            chunkHtml += `<tr style="background-color: ${color}; color: ${textColor}; font-weight: bold;">
                <td>${esc(log.date)}</td>
                <td>${esc(log.time)}</td>
                <td>${esc(log.operador)} - ${esc(log.operador_nombre)}</td>
                <td>${log.is_critical ? 'Captura Crítica' : esc(log.action)}</td>
                <td>
                    ${(() => {
                    let content = log.details || "";

                    // LIMPIEZA DE MENSAJES DE BLOQUEO: Quitar el prefijo técnico para el reporte visual
                    content = content.replace(/\[BLOQUEO\] Operador \d+ /i, '');

                    // REGLA DE ORO: Si el contenido ya trae HTML (de calidad), no lo escapamos
                    const hasHtml = content.includes('<span') || content.includes('<b>');
                    
                    if (!hasHtml) {
                        content = esc(content);
                        // Aplicar negritas a nombres en logs planos para mejor legibilidad
                        content = content.replace(/(El inspector de calidad|El operador) (.*?) (finalizó|registró|sincronizó|completó)/g, '$1 <b>$2</b> $3');
                    }

                    // Lógica de colores para Reporte de Calidad (Estandarización Legacy + New)
                    if (log.action === "Proceso Correcto" && (content.includes("finalizó la revisión:") || content.includes("realizó:"))) {
                        // Si es el formato legacy (sin HTML), aplicamos los colores aquí
                        if (!hasHtml) {
                            // Liberadas (Azul)
                            content = content.replace(/Liberadas: (\d+)( \[[^\]]+\])?/g,
                                '<span style="color:#2E86C1; font-weight:bold;">Liberadas: $1 <small>$2</small></span>');

                            // Rechazadas (Rojo)
                            content = content.replace(/Rechazadas: (\d+)( \[[^\]]+\])?/g,
                                '<span style="color:#C0392B; font-weight:bold; margin-left:12px;">Rechazadas: $1 <small>$2</small></span>');

                            // Incompletas (Amarillo Ocre)
                            content = content.replace(/Incompletas: (\d+)( \[[^\]]+\])?/g,
                                '<span style="color:#B7950B; font-weight:bold; margin-left:12px;">Incompletas: $1 <small>$2</small></span>');
                        }
                    }

                    // Resaltado para Liberaciones/Rechazos de Calidad
                    content = content.replace(/LIBERADA/g, '<b style="color:#1D8348;">LIBERADA</b>');
                    content = content.replace(/RECHAZADA/g, '<b style="color:#943126;">RECHAZADA</b>');

                    return content;
                })()}
                    ${(log.action === "Captura Sospechosa" || (log.action === "Captura Medida" && log.is_suspicious))
                    ? (log.is_critical
                        ? `<br><strong style="color: #ff0000; font-size:1.1em;"> ALERTA CRÍTICA: Problema recurrente de llenado. El operador está registrando piezas con tiempos insuficientes de forma sistemática. Favor de notificar de inmediato para tomar acciones (${log.diff_mins ?? 0} min)</strong>`
                        : `<br><strong style="color: #904d02;"> ALERTA: Tiempo insuficiente entre piezas diferentes (${log.diff_mins ?? 0} min)</strong>`
                    )
                    : ""
                }
                </td>
                <td>${esc(log.ot)}</td>
                <td>
                    ${(() => {
                    let rawPieza = log.n_juego ? String(log.n_juego).trim().toUpperCase() : "";
                    if (!rawPieza || rawPieza === "N/A") return "N/A";

                    // Si contiene comas (es una lista) o ya tiene letras al final, dejarlo casi tal cual
                    if (rawPieza.includes(',') || rawPieza.endsWith('H') || rawPieza.endsWith('M') || rawPieza.endsWith('J')) {
                        return rawPieza;
                    }

                    // Si es solo número solo, ponerle la J
                    let numero = rawPieza.replace(/[^0-9]/g, '');
                    return numero ? numero + "J" : rawPieza;
                })()}
                </td>
                <td>${esc(log.hora_inicio)}</td>
                <td>${esc(log.hora_termino)}</td>
                <td>${esc(log.tiempo_total)}</td>
                <td>${esc(log.clase)}</td>
                <td>${esc(log.proceso)}</td>
                <td>${esc(log.maquina)}</td>
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
