/**
 * Componente interactivo para el modal de calendario de fecha (#modalCmCalendar)
 * Compatible con filtros dinámicos (systemLogs, adminPieces, releasePieces).
 */

const cmMonthNames = [
    "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
    "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
];

let cmCalCurrentDate = new Date();
let activeTargetInput = null;
let activeDisplayLabel = null;
let activeClearBtn = null;
let isInitialized = false;

/**
 * Valida si un string es una fecha válida en formato YYYY-MM-DD
 */
export function isValidDateStr(str) {
    if (!str || typeof str !== "string" || str === "Todos") return false;
    return /^\d{4}-\d{2}-\d{2}$/.test(str.trim());
}

/**
 * Formatea 'YYYY-MM-DD' a 'DD/MM/YYYY' para visualización amigable
 */
export function formatDisplayDate(dateStr) {
    if (!isValidDateStr(dateStr)) return "";
    const parts = dateStr.trim().split("-");
    if (parts.length === 3 && parts[0].length === 4) {
        return `${parts[2]}/${parts[1]}/${parts[0]}`;
    }
    return "";
}

/**
 * Abre el modal y sincroniza con la fecha actual del campo objetivo
 */
export function openModalCalendar(inputEl, displayLabelEl, clearBtnEl, title = "Seleccionar Fecha") {
    initCalendarModalEvents();
    activeTargetInput = inputEl;
    activeDisplayLabel = displayLabelEl;
    activeClearBtn = clearBtnEl;

    const titleEl = document.getElementById("cm-modal-title");
    if (titleEl) {
        titleEl.textContent = title;
    }

    if (inputEl && inputEl.value && isValidDateStr(inputEl.value)) {
        const parts = inputEl.value.split("-").map(Number);
        if (parts.length === 3 && !isNaN(parts[0]) && !isNaN(parts[1])) {
            cmCalCurrentDate = new Date(parts[0], parts[1] - 1, parts[2] || 1);
        } else {
            cmCalCurrentDate = new Date();
        }
    } else {
        cmCalCurrentDate = new Date();
    }

    renderCalendarDays();

    const modal = document.getElementById("modalCmCalendar");
    if (modal) {
        modal.style.display = "flex";
    }
}

/**
 * Cierra el modal de calendario
 */
export function closeModalCalendar() {
    const modal = document.getElementById("modalCmCalendar");
    if (modal) {
        modal.style.display = "none";
    }
}

/**
 * Inicializa los eventos del modal (anterior, siguiente, backdrop, escape)
 */
function initCalendarModalEvents() {
    if (isInitialized) return;
    const modal = document.getElementById("modalCmCalendar");
    const prevBtn = document.getElementById("cm-cal-prev");
    const nextBtn = document.getElementById("cm-cal-next");

    if (prevBtn) {
        prevBtn.addEventListener("click", (e) => {
            e.stopPropagation();
            cmCalCurrentDate.setMonth(cmCalCurrentDate.getMonth() - 1);
            renderCalendarDays();
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener("click", (e) => {
            e.stopPropagation();
            cmCalCurrentDate.setMonth(cmCalCurrentDate.getMonth() + 1);
            renderCalendarDays();
        });
    }

    if (modal) {
        modal.addEventListener("click", (e) => {
            if (e.target === modal) {
                closeModalCalendar();
            }
        });
    }

    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape" && modal && modal.style.display !== "none") {
            closeModalCalendar();
        }
    });

    isInitialized = true;
}

/**
 * Renderiza la cuadrícula de días según el mes y año de cmCalCurrentDate
 */
function renderCalendarDays() {
    const monthYearEl = document.getElementById("cm-cal-month-year");
    const daysContainer = document.getElementById("cm-cal-days");
    if (!daysContainer) return;

    const year = cmCalCurrentDate.getFullYear();
    const month = cmCalCurrentDate.getMonth();

    if (monthYearEl) {
        monthYearEl.textContent = `${cmMonthNames[month]} ${year}`;
    }

    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const today = new Date();
    const selectedDate = (activeTargetInput && isValidDateStr(activeTargetInput.value)) ? activeTargetInput.value.trim() : "";

    daysContainer.innerHTML = "";

    // Días vacíos al inicio de la semana
    for (let i = 0; i < firstDay; i++) {
        const emptyDiv = document.createElement("div");
        daysContainer.appendChild(emptyDiv);
    }

    // Días del mes
    for (let i = 1; i <= daysInMonth; i++) {
        const dStr = `${year}-${String(month + 1).padStart(2, "0")}-${String(i).padStart(2, "0")}`;

        let bg = "transparent";
        let color = "#334155";
        let fw = "500";
        let extraStyles = "border: 1px solid transparent;";

        if (selectedDate === dStr) {
            bg = "#0a8504";
            color = "#fff";
            fw = "700";
            extraStyles = "border: 1px solid #0a8504; box-shadow: 0 2px 5px rgba(10, 133, 4, 0.4);";
        } else if (
            today.getFullYear() === year &&
            today.getMonth() === month &&
            today.getDate() === i
        ) {
            color = "#0a8504";
            fw = "700";
            extraStyles = "border: 1px solid #bbf7d0; background: #dcfce7;";
        }

        const dayDiv = document.createElement("div");
        dayDiv.className = "cm-cal-day";
        dayDiv.dataset.date = dStr;
        dayDiv.textContent = i;
        dayDiv.style.cssText = `padding: 8px 0; border-radius: 8px; cursor: pointer; background: ${bg}; color: ${color}; font-weight: ${fw}; font-size: 1.1em; transition: all 0.2s; ${extraStyles}`;

        dayDiv.addEventListener("mouseover", function () {
            if (this.dataset.date !== selectedDate) {
                this.style.background = "#e2e8f0";
            }
        });

        dayDiv.addEventListener("mouseout", function () {
            if (this.dataset.date !== selectedDate) {
                const isToday = today.getFullYear() === year && today.getMonth() === month && today.getDate() === i;
                this.style.background = isToday ? "#dcfce7" : "transparent";
            }
        });

        dayDiv.addEventListener("click", function () {
            if (activeTargetInput) {
                activeTargetInput.value = this.dataset.date;
                if (activeDisplayLabel) {
                    activeDisplayLabel.textContent = formatDisplayDate(this.dataset.date);
                    activeDisplayLabel.style.color = "#0f172a";
                    activeDisplayLabel.style.fontWeight = "600";
                    activeDisplayLabel.style.flex = "0 0 68%";
                    activeDisplayLabel.style.width = "68%";
                    activeDisplayLabel.style.maxWidth = "68%";
                    activeDisplayLabel.classList.remove("is-empty");
                }
                if (activeClearBtn) {
                    activeClearBtn.style.display = "inline-flex";
                }
                activeTargetInput.dispatchEvent(new Event("input", { bubbles: true }));
                activeTargetInput.dispatchEvent(new Event("change", { bubbles: true }));
                if (typeof window.updateClearButtonState === "function") {
                    window.updateClearButtonState();
                }
            }
            closeModalCalendar();
        });

        daysContainer.appendChild(dayDiv);
    }
}

/**
 * Crea el elemento disparador (trigger) dentro del div .filter contenedor
 * @param {HTMLElement} containerDiv - El contenedor .filter
 * @param {string} fieldName - 'dateFrom' o 'dateTo'
 * @param {string} initialValue - Valor inicial YYYY-MM-DD (si es 'Todos' o inválido, queda vacío)
 * @param {string} titleLabel - Etiqueta descriptiva ('Desde', 'Hasta')
 * @param {function|null} onChangeCallback - Callback al cambiar fecha
 */
export function createDateFilterTrigger(containerDiv, fieldName, initialValue = "", titleLabel = "Fecha", onChangeCallback = null) {
    // Si viene "Todos" o algo que no es fecha YYYY-MM-DD, se inicializa completamente vacío sin filtros
    const validVal = isValidDateStr(initialValue) ? initialValue.trim() : "";
    const hasInitialDate = validVal !== "";

    const hiddenInput = document.createElement("input");
    hiddenInput.type = "hidden";
    hiddenInput.name = fieldName;
    hiddenInput.id = `input-filter-${fieldName}`;
    hiddenInput.className = "input-filter";
    hiddenInput.value = validVal;

    const btn = document.createElement("button");
    btn.type = "button";
    btn.className = "btn-date-trigger";
    btn.id = `btn-trigger-${fieldName}`;
    btn.setAttribute("aria-label", `Seleccionar fecha para ${titleLabel}`);
    btn.style.cssText = "display: flex !important; align-items: center !important; justify-content: space-between !important; text-align: left !important; width: 100% !important; min-width: 0 !important; max-width: 100% !important; box-sizing: border-box !important; padding: clamp(0.38rem, 0.9vh, 0.55rem) clamp(0.4rem, 0.8vw, 0.55rem) !important; gap: 4px !important; overflow: hidden !important;";

    // Texto de la fecha (se adapta al ancho sin empujar el contenedor)
    const labelSpan = document.createElement("span");
    labelSpan.className = "date-trigger-label" + (hasInitialDate ? "" : " is-empty");
    labelSpan.style.cssText = `font-family: inherit; font-size: inherit; font-weight: ${hasInitialDate ? '600' : '500'}; color: ${hasInitialDate ? '#0f172a' : '#64748b'}; letter-spacing: 0.01em; flex: 1 1 auto; min-width: 0; text-align: left; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;`;
    labelSpan.textContent = hasInitialDate ? formatDisplayDate(validVal) : "dd/mm/aaaa";

    // Botón de limpiar fecha (tamaño cómodo y fijo para no alterar el tamaño del filtro)
    const clearBtn = document.createElement("span");
    clearBtn.className = "btn-clear-single-date";
    clearBtn.title = "Limpiar fecha";
    clearBtn.innerHTML = `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round" style="pointer-events: none; display: block;"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>`;
    clearBtn.style.cssText = `display: ${hasInitialDate ? "inline-flex" : "none"}; align-items: center; justify-content: center; width: 26px; height: 24px; flex: 0 0 26px; border-radius: 6px; background: #f1f5f9; color: #475569; cursor: pointer; transition: all 0.18s ease; margin-left: auto; flex-shrink: 0; box-shadow: 0 1px 2px rgba(0,0,0,0.06);`;

    clearBtn.addEventListener("mouseover", () => {
        clearBtn.style.background = "#fee2e2";
        clearBtn.style.color = "#dc2626";
        clearBtn.style.transform = "scale(1.08)";
    });
    clearBtn.addEventListener("mouseout", () => {
        clearBtn.style.background = "#f1f5f9";
        clearBtn.style.color = "#475569";
        clearBtn.style.transform = "scale(1)";
    });

    clearBtn.addEventListener("click", (e) => {
        e.stopPropagation();
        hiddenInput.value = "";
        labelSpan.textContent = "dd/mm/aaaa";
        labelSpan.style.color = "#64748b";
        labelSpan.style.fontWeight = "500";
        labelSpan.classList.add("is-empty");
        clearBtn.style.display = "none";
        hiddenInput.dispatchEvent(new Event("input", { bubbles: true }));
        hiddenInput.dispatchEvent(new Event("change", { bubbles: true }));
        if (onChangeCallback) onChangeCallback("");
        if (typeof window.updateClearButtonState === "function") {
            window.updateClearButtonState();
        }
    });

    btn.appendChild(labelSpan);
    btn.appendChild(clearBtn);

    btn.addEventListener("click", () => {
        openModalCalendar(hiddenInput, labelSpan, clearBtn, `Seleccionar Fecha - ${titleLabel}`);
    });

    if (onChangeCallback) {
        hiddenInput.addEventListener("change", () => onChangeCallback(hiddenInput.value));
    }

    containerDiv.appendChild(hiddenInput);
    containerDiv.appendChild(btn);

    return { hiddenInput, btn, labelSpan, clearBtn };
}
