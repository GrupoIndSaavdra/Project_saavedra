/**
 * Componente interactivo para el modal de calendario de fecha (#modalCmCalendar)
 * Compatible con filtros dinámicos (systemLogs, adminPieces, releasePieces),
 * formularios maestros (create_master_wo, show_wo_almacen) y cualquier input type="date".
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
 * Valida si un string es una fecha válida en formato YYYY-MM-DD o DD/MM/YYYY
 */
export function isValidDateStr(str) {
    if (!str || typeof str !== "string" || str === "Todos") return false;
    const trimmed = str.trim();
    return /^\d{4}-\d{2}-\d{2}$/.test(trimmed) || /^\d{2}\/\d{2}\/\d{4}$/.test(trimmed);
}

/**
 * Parsea un string YYYY-MM-DD o DD/MM/YYYY a objeto Date seguro
 */
export function parseDateString(str) {
    if (!str || typeof str !== "string") return null;
    const trimmed = str.trim();
    if (/^\d{4}-\d{2}-\d{2}$/.test(trimmed)) {
        const parts = trimmed.split("-").map(Number);
        if (parts.length === 3 && !isNaN(parts[0]) && !isNaN(parts[1])) {
            return new Date(parts[0], parts[1] - 1, parts[2] || 1);
        }
    }
    if (/^\d{2}\/\d{2}\/\d{4}$/.test(trimmed)) {
        const parts = trimmed.split("/").map(Number);
        if (parts.length === 3 && !isNaN(parts[0]) && !isNaN(parts[1])) {
            return new Date(parts[2], parts[1] - 1, parts[0] || 1);
        }
    }
    return null;
}

/**
 * Formatea 'YYYY-MM-DD' a 'DD/MM/YYYY' para visualización amigable
 */
export function formatDisplayDate(dateStr) {
    if (!dateStr || typeof dateStr !== "string") return "";
    const trimmed = dateStr.trim();
    if (/^\d{2}\/\d{2}\/\d{4}$/.test(trimmed)) return trimmed;
    if (/^\d{4}-\d{2}-\d{2}$/.test(trimmed)) {
        const parts = trimmed.split("-");
        if (parts.length === 3 && parts[0].length === 4) {
            return `${parts[2]}/${parts[1]}/${parts[0]}`;
        }
    }
    return "";
}

/**
 * Formatea 'DD/MM/YYYY' o 'YYYY-MM-DD' a 'YYYY-MM-DD' (ISO)
 */
export function formatIsoDate(dateStr) {
    if (!dateStr || typeof dateStr !== "string") return "";
    const trimmed = dateStr.trim();
    if (/^\d{4}-\d{2}-\d{2}$/.test(trimmed)) return trimmed;
    if (/^\d{2}\/\d{2}\/\d{4}$/.test(trimmed)) {
        const parts = trimmed.split("/");
        if (parts.length === 3) {
            return `${parts[2]}-${parts[1].padStart(2, "0")}-${parts[0].padStart(2, "0")}`;
        }
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

    let parsed = null;
    if (inputEl && inputEl.value) {
        parsed = parseDateString(inputEl.value);
    }
    if (!parsed && displayLabelEl) {
        const lblVal = (displayLabelEl.tagName === "INPUT" ? displayLabelEl.value : displayLabelEl.textContent) || "";
        parsed = parseDateString(lblVal);
    }

    if (parsed && !isNaN(parsed.getTime())) {
        cmCalCurrentDate = parsed;
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
    const prevYearBtn = document.getElementById("cm-cal-prev-year");
    const nextYearBtn = document.getElementById("cm-cal-next-year");

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

    if (prevYearBtn) {
        prevYearBtn.addEventListener("click", (e) => {
            e.stopPropagation();
            cmCalCurrentDate.setFullYear(cmCalCurrentDate.getFullYear() - 1);
            renderCalendarDays();
        });
    }

    if (nextYearBtn) {
        nextYearBtn.addEventListener("click", (e) => {
            e.stopPropagation();
            cmCalCurrentDate.setFullYear(cmCalCurrentDate.getFullYear() + 1);
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

    let selectedIso = "";
    if (activeTargetInput && activeTargetInput.value) {
        selectedIso = formatIsoDate(activeTargetInput.value);
    } else if (activeDisplayLabel) {
        const val = (activeDisplayLabel.tagName === "INPUT" ? activeDisplayLabel.value : activeDisplayLabel.textContent) || "";
        selectedIso = formatIsoDate(val);
    }

    const minDateStr = (activeTargetInput && activeTargetInput.min) ? activeTargetInput.min.trim() : "";
    const maxDateStr = (activeTargetInput && activeTargetInput.max) ? activeTargetInput.max.trim() : "";

    daysContainer.innerHTML = "";

    // Días vacíos al inicio de la semana
    for (let i = 0; i < firstDay; i++) {
        const emptyDiv = document.createElement("div");
        daysContainer.appendChild(emptyDiv);
    }

    // Días del mes
    for (let i = 1; i <= daysInMonth; i++) {
        const dStr = `${year}-${String(month + 1).padStart(2, "0")}-${String(i).padStart(2, "0")}`;

        const isOutOfMin = minDateStr && dStr < minDateStr;
        const isOutOfMax = maxDateStr && dStr > maxDateStr;
        const isDisabled = isOutOfMin || isOutOfMax;

        let bg = "transparent";
        let color = isDisabled ? "#94a3b8" : "#334155";
        let fw = "500";
        let cursor = isDisabled ? "not-allowed" : "pointer";
        let opacity = isDisabled ? "0.38" : "1";
        let extraStyles = "border: 1px solid transparent;";

        if (selectedIso === dStr) {
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
        dayDiv.className = "cm-cal-day" + (isDisabled ? " disabled" : "");
        dayDiv.dataset.date = dStr;
        dayDiv.textContent = i;
        dayDiv.style.cssText = `padding: 8px 0; border-radius: 8px; cursor: ${cursor}; opacity: ${opacity}; background: ${bg}; color: ${color}; font-weight: ${fw}; font-size: 1.1em; transition: all 0.2s; ${extraStyles}`;

        if (!isDisabled) {
            dayDiv.addEventListener("mouseover", function () {
                if (this.dataset.date !== selectedIso) {
                    this.style.background = "#e2e8f0";
                }
            });

            dayDiv.addEventListener("mouseout", function () {
                if (this.dataset.date !== selectedIso) {
                    const isToday = today.getFullYear() === year && today.getMonth() === month && today.getDate() === i;
                    this.style.background = isToday ? "#dcfce7" : "transparent";
                }
            });

            dayDiv.addEventListener("click", function () {
                if (activeTargetInput) {
                    activeTargetInput.value = this.dataset.date;
                    if (activeDisplayLabel) {
                        const formatted = formatDisplayDate(this.dataset.date);
                        if (activeDisplayLabel.tagName === "INPUT") {
                            activeDisplayLabel.value = formatted;
                        } else {
                            activeDisplayLabel.textContent = formatted;
                        }
                        activeDisplayLabel.style.color = "#0f172a";
                        activeDisplayLabel.style.fontWeight = "600";
                        if (activeClearBtn && activeDisplayLabel.classList.contains("date-trigger-label")) {
                            activeDisplayLabel.style.flex = "0 0 68%";
                            activeDisplayLabel.style.width = "68%";
                            activeDisplayLabel.style.maxWidth = "68%";
                        }
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
        }

        daysContainer.appendChild(dayDiv);
    }
}

/**
 * Crea el elemento disparador (trigger) dentro del div .filter contenedor
 * Mantiene compatibilidad total con adminPieces, releasePieces y systemLogs.
 */
export function createDateFilterTrigger(containerDiv, fieldName, initialValue = "", titleLabel = "Fecha", onChangeCallback = null) {
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

    const labelSpan = document.createElement("span");
    labelSpan.className = "date-trigger-label" + (hasInitialDate ? "" : " is-empty");
    labelSpan.style.cssText = `font-family: inherit; font-size: inherit; font-weight: ${hasInitialDate ? '600' : '500'}; color: ${hasInitialDate ? '#0f172a' : '#64748b'}; letter-spacing: 0.01em; flex: 1 1 auto; min-width: 0; text-align: left; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;`;
    labelSpan.textContent = hasInitialDate ? formatDisplayDate(validVal) : "dd/mm/aaaa";

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

/**
 * Transforma un elemento input de tipo fecha en un componente interactivo GIS
 * con el modal corporativo (#modalCmCalendar).
 */
export function enhanceDateInput(inputEl) {
    if (!inputEl || inputEl.dataset.gisEnhanced === "true") return;
    if (inputEl.dataset.noGisDatepicker !== undefined) return;

    // Si está explícitamente oculto (celdas de priorities, etc.), no envolver
    if (inputEl.style.opacity === "0" || inputEl.style.visibility === "hidden") {
        return;
    }

    inputEl.dataset.gisEnhanced = "true";

    // Extraer título contextual (del label, placeholder, title o data-title)
    let modalTitle = inputEl.dataset.title || "";
    if (!modalTitle && inputEl.id) {
        const label = document.querySelector(`label[for="${inputEl.id}"]`);
        if (label) {
            let lblText = label.textContent.replace(/[\*\:\#]/g, "").trim();
            lblText = lblText.replace(/^\d+[\.\-\s]+/, "").trim();
            if (lblText) {
                modalTitle = `Seleccionar ${lblText}`;
            }
        }
    }
    if (!modalTitle && inputEl.placeholder) {
        modalTitle = `Seleccionar ${inputEl.placeholder}`;
    }
    if (!modalTitle) {
        modalTitle = "Seleccionar Fecha";
    }

    const initialVal = inputEl.value ? inputEl.value.trim() : "";
    const wasRequired = inputEl.required;
    const isDisabled = inputEl.disabled;
    const isReadOnly = inputEl.readOnly;

    // Convertir el input original en hidden para mantener su name, id y valor funcional
    inputEl.type = "hidden";
    inputEl.required = false;

    // Crear contenedor wrapper
    const wrapper = document.createElement("div");
    wrapper.className = "gis-datepicker-wrapper";

    // Visible display input
    const displayInput = document.createElement("input");
    displayInput.type = "text";
    displayInput.readOnly = true;
    displayInput.tabIndex = isDisabled ? -1 : 0;
    displayInput.className = (inputEl.className ? inputEl.className + " " : "") + "gis-datepicker-display";
    if (inputEl.id) {
        displayInput.id = `display_${inputEl.id}`;
    }
    displayInput.placeholder = inputEl.placeholder || "dd/mm/aaaa";
    displayInput.value = formatDisplayDate(initialVal);
    displayInput.required = wasRequired;
    displayInput.disabled = isDisabled;
    displayInput.title = inputEl.title || "Haz clic para seleccionar la fecha";

    if (!initialVal) {
        displayInput.classList.add("is-empty");
    }

    // Botón / Icono de Calendario
    const iconBtn = document.createElement("button");
    iconBtn.type = "button";
    iconBtn.tabIndex = -1;
    iconBtn.className = "gis-datepicker-btn";
    iconBtn.title = "Abrir calendario";
    iconBtn.setAttribute("aria-label", modalTitle);

    const iconImg = document.createElement("img");
    const fechaIconUrl = (typeof window.getIcon === "function") 
        ? window.getIcon("fecha") 
        : ((window.baseUrl || "") + "/images/Color_Icons/Generales/Fecha.png");
    iconImg.src = fechaIconUrl;
    iconImg.alt = "Fecha";
    iconBtn.appendChild(iconImg);

    // Eventos de apertura de calendario
    const triggerOpen = (e) => {
        if (displayInput.disabled || isReadOnly) return;
        if (e) e.preventDefault();
        openModalCalendar(inputEl, displayInput, null, modalTitle);
    };

    displayInput.addEventListener("click", triggerOpen);
    iconBtn.addEventListener("click", triggerOpen);
    displayInput.addEventListener("keydown", (e) => {
        if (e.key === "Enter" || e.key === " " || e.key === "ArrowDown") {
            triggerOpen(e);
        } else if (e.key === "Backspace" || e.key === "Delete") {
            if (!wasRequired) {
                e.preventDefault();
                inputEl.value = "";
                displayInput.value = "";
                displayInput.classList.add("is-empty");
                inputEl.dispatchEvent(new Event("input", { bubbles: true }));
                inputEl.dispatchEvent(new Event("change", { bubbles: true }));
            }
        }
    });

    // Si el usuario hace clic en el <label> asociado, enfocar el displayInput
    if (inputEl.id) {
        const label = document.querySelector(`label[for="${inputEl.id}"]`);
        if (label) {
            label.addEventListener("click", (e) => {
                e.preventDefault();
                triggerOpen(e);
            });
        }
    }

    // Interceptar el setter de value en inputEl para sincronizar cambios programáticos
    const originalDescriptor = Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, "value");
    if (originalDescriptor) {
        Object.defineProperty(inputEl, "value", {
            get() {
                return originalDescriptor.get.call(this);
            },
            set(val) {
                originalDescriptor.set.call(this, val);
                const formatted = formatDisplayDate(val);
                displayInput.value = formatted;
                if (val && String(val).trim()) {
                    displayInput.classList.remove("is-empty");
                } else {
                    displayInput.classList.add("is-empty");
                }
            },
            configurable: true
        });
    }

    inputEl.addEventListener("input", () => {
        displayInput.value = formatDisplayDate(inputEl.value);
    });
    inputEl.addEventListener("change", () => {
        displayInput.value = formatDisplayDate(inputEl.value);
    });

    // Insertar en el DOM
    if (inputEl.parentNode) {
        inputEl.parentNode.insertBefore(wrapper, inputEl);
        wrapper.appendChild(inputEl);
        wrapper.appendChild(displayInput);
        wrapper.appendChild(iconBtn);
    }
}

/**
 * Escanea y transforma automáticamente todos los inputs de fecha en el contenedor dado.
 */
export function initAllGisDatePickers(root = document) {
    if (!root || !root.querySelectorAll) return;
    const candidates = root.querySelectorAll('input[type="date"], input.gis-datepicker, input[data-gis-datepicker]');
    candidates.forEach((el) => {
        enhanceDateInput(el);
    });
}

/**
 * Observador de mutaciones para transformar dinámicamente inputs creados por JS o modales
 */
let isObserverActive = false;
export function setupDatePickersObserver() {
    if (isObserverActive || typeof MutationObserver === "undefined") return;
    const observer = new MutationObserver((mutations) => {
        for (const mut of mutations) {
            if (mut.addedNodes && mut.addedNodes.length > 0) {
                for (const node of mut.addedNodes) {
                    if (node.nodeType === 1) { // ELEMENT_NODE
                        if (node.matches && node.matches('input[type="date"], input.gis-datepicker, input[data-gis-datepicker]')) {
                            enhanceDateInput(node);
                        } else if (node.querySelectorAll) {
                            initAllGisDatePickers(node);
                        }
                    }
                }
            }
        }
    });

    if (document.body) {
        observer.observe(document.body, { childList: true, subtree: true });
        isObserverActive = true;
    } else {
        document.addEventListener("DOMContentLoaded", () => {
            if (!isObserverActive && document.body) {
                observer.observe(document.body, { childList: true, subtree: true });
                isObserverActive = true;
            }
        });
    }
}

// Inicialización automática
if (typeof document !== "undefined") {
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", () => {
            initAllGisDatePickers();
            setupDatePickersObserver();
        });
    } else {
        initAllGisDatePickers();
        setupDatePickersObserver();
    }
}

// Exponer en window para acceso global
if (typeof window !== "undefined") {
    window.openModalCalendar = openModalCalendar;
    window.closeModalCalendar = closeModalCalendar;
    window.isValidDateStr = isValidDateStr;
    window.formatDisplayDate = formatDisplayDate;
    window.formatIsoDate = formatIsoDate;
    window.enhanceDateInput = enhanceDateInput;
    window.initAllGisDatePickers = initAllGisDatePickers;
}
