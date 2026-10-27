/**
 * Componente interactivo para el modal de turno y horario (#modalCmTime)
 * Detecta automáticamente el día y turno según horarios de GIS, bloquea turnos inválidos
 * (como Turno Noche en Sábados) y envía startTime y endTime en formato 24H (HH:mm) para Laravel.
 */

let activeTargetStart = null;
let activeTargetEnd = null;
let activeDisplayLabel = null;

let startHour24 = 6;
let startMinute = 0;
let endHour24 = 14;
let endMinute = 0;

let activeTargetTab = "start"; // "start" (Inicio) o "end" (Término)
let timeFormatMode = "12";     // "12" o "24"
let activeDayMode = "lv";      // "lv" o "sab"
let selectedShiftId = "manana";
let isInitialized = false;

/**
 * Valida si un string es una hora válida en formato HH:mm o HH:mm:ss
 */
export function isValidTimeStr(str) {
    if (!str || typeof str !== "string") return false;
    return /^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/.test(str.trim());
}

/**
 * Calcula el número de semana ISO del año
 */
export function getWeekNumber(d = new Date()) {
    const target = new Date(d.valueOf());
    const dayNr = (d.getDay() + 6) % 7;
    target.setDate(target.getDate() - dayNr + 3);
    const firstThursday = target.valueOf();
    target.setMonth(0, 1);
    if (target.getDay() !== 4) {
        target.setMonth(0, 1 + ((4 - target.getDay()) + 7) % 7);
    }
    return 1 + Math.ceil((firstThursday - target) / 604800000);
}

/**
 * Formatea una hora en string a visualización amigable en 12h con AM/PM o 24h
 */
export function formatDisplayTime(timeStr, prefer12h = true) {
    if (!isValidTimeStr(timeStr)) return "";
    const parts = timeStr.trim().split(":");
    const h24 = parseInt(parts[0], 10);
    const m = parts[1].padStart(2, "0");

    if (prefer12h) {
        let h12 = h24 % 12;
        if (h12 === 0) h12 = 12;
        const period = h24 >= 12 ? "PM" : "AM";
        return `${String(h12).padStart(2, "0")}:${m} ${period}`;
    }

    return `${String(h24).padStart(2, "0")}:${m} hrs`;
}

/**
 * Detecta automáticamente el turno y día según la hora y fecha
 * Lunes a Viernes:
 *   - Mañana: 06:00 a 14:00
 *   - Mixto:   13:45 a 21:45
 *   - Noche:   22:00 a 06:00
 * Sábado:
 *   - Mañana: 06:00 a 14:00
 *   - Mixto:   13:45 a 18:45
 *   - Noche:   Bloqueado (No hay turno noche)
 */
export function getAutoDetectedShift(referenceDate = new Date()) {
    const dayOfWeek = referenceDate.getDay();
    const isSaturday = dayOfWeek === 6;
    const hours = referenceDate.getHours();
    const minutes = referenceDate.getMinutes();
    const currentTotalMinutes = hours * 60 + minutes;

    if (isSaturday) {
        if (currentTotalMinutes >= 825) { // 13:45 en adelante
            return {
                id: "mixto",
                name: "Turno Mixto (Sábado)",
                shortName: "Mixto",
                start: "13:45",
                end: "18:45",
                isSaturday: true,
                imageName: "Color_Icons/Generales/Turno_Mixto.png"
            };
        }
        return {
            id: "manana",
            name: "Turno Mañana (Sábado)",
            shortName: "Mañana",
            start: "06:00",
            end: "14:00",
            isSaturday: true,
            imageName: "Color_Icons/Generales/Turno_Manana.png"
        };
    }

    // Lunes a Viernes
    if (currentTotalMinutes >= 360 && currentTotalMinutes < 825) {
        // 06:00 a 13:44 -> Mañana
        return {
            id: "manana",
            name: "Turno Mañana",
            shortName: "Mañana",
            start: "06:00",
            end: "14:00",
            isSaturday: false,
            imageName: "Color_Icons/Generales/Turno_Manana.png"
        };
    } else if (currentTotalMinutes >= 825 && currentTotalMinutes < 1305) {
        // 13:45 a 21:44 -> Mixto
        return {
            id: "mixto",
            name: "Turno Mixto",
            shortName: "Mixto",
            start: "13:45",
            end: "21:45",
            isSaturday: false,
            imageName: "Color_Icons/Generales/Turno_Mixto.png"
        };
    } else {
        // 21:45 en adelante o antes de 06:00 -> Noche
        return {
            id: "noche",
            name: "Turno Noche",
            shortName: "Noche",
            start: "22:00",
            end: "06:00",
            isSaturday: false,
            imageName: "Color_Icons/Generales/Turno_Noche.png"
        };
    }
}

/**
 * Retorna una descripción completa del turno y rango de horas
 */
export function getShiftSummaryText(startTime, endTime) {
    if (!startTime || !endTime) return "Turno y Horario";
    const start = String(startTime).trim().substring(0, 5);
    const end = String(endTime).trim().substring(0, 5);

    if (start === "06:00" && end === "14:00") {
        return `Turno Mañana: 06:00 - 14:00 hrs`;
    } else if (start === "13:45" && (end === "21:45" || end === "18:45")) {
        const isSat = end === "18:45" ? " (Sábado)" : "";
        return `Turno Mixto${isSat}: ${start} - ${end} hrs`;
    } else if (start === "22:00" && end === "06:00") {
        return `Turno Noche: 22:00 - 06:00 hrs`;
    }
    return `Turno Especial: ${start} - ${end} hrs`;
}

/**
 * Retorna el marcado HTML con colores vivos para el input/disparador de Turno
 */
export function getShiftTriggerHtml(startTime, endTime) {
    const start = String(startTime || "06:00").trim().substring(0, 5);
    const end = String(endTime || "14:00").trim().substring(0, 5);

    let shiftName = "Turno";
    let iconName = "Color_Icons/Generales/Turno_Manana.png";
    let hoursColor = "#0a8504";
    let hoursBg = "#e8f5e9";
    let hoursBorder = "#a5d6a7";

    if (start === "06:00" && end === "14:00") {
        shiftName = "Turno Mañana";
        iconName = "Color_Icons/Generales/Turno_Manana.png";
        hoursColor = "#0a8504";
        hoursBg = "#e8f5e9";
        hoursBorder = "#a5d6a7";
    } else if (start === "13:45" && (end === "21:45" || end === "18:45")) {
        const isSat = end === "18:45";
        shiftName = isSat ? "Turno Mixto (Sáb)" : "Turno Mixto";
        iconName = "Color_Icons/Generales/Turno_Mixto.png";
        hoursColor = "#b45309";
        hoursBg = "#fef3c7";
        hoursBorder = "#fde68a";
    } else if (start === "22:00" && end === "06:00") {
        shiftName = "Turno Noche";
        iconName = "Color_Icons/Generales/Turno_Noche.png";
        hoursColor = "#4338ca";
        hoursBg = "#e0e7ff";
        hoursBorder = "#c7d2fe";
    } else {
        shiftName = "Turno Especial";
        iconName = "Color_Icons/Generales/Hora.png";
        hoursColor = "#033966";
        hoursBg = "#e0f2fe";
        hoursBorder = "#bae6fd";
    }

    return `
        <div style="display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%;">
            <span style="color: #033966; font-weight: 800; font-size: 0.94rem; letter-spacing: 0.01em;">${shiftName}:</span>
            <span style="color: ${hoursColor}; font-weight: 800; font-size: 0.88rem; background: ${hoursBg}; border: 1.5px solid ${hoursBorder}; padding: 2px 8px; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.06);">${start} – ${end} hrs</span>
        </div>
    `;
}

/**
 * Abre el modal unificado de Turno y Horario (sincroniza startTime y endTime a la vez)
 */
export function openModalShiftTime(startInputEl, endInputEl, displayLabelEl) {
    initShiftTimeModalEvents();
    activeTargetStart = startInputEl;
    activeTargetEnd = endInputEl;
    activeDisplayLabel = displayLabelEl;
    activeTargetTab = "start";

    // Detectar si la fecha del formulario o la fecha actual es sábado
    let refDate = new Date();
    const formDateInput = document.getElementById("date");
    if (formDateInput && formDateInput.value) {
        const parts = formDateInput.value.split("-").map(Number);
        if (parts.length === 3) {
            refDate = new Date(parts[0], parts[1] - 1, parts[2], refDate.getHours(), refDate.getMinutes());
        }
    }

    const autoShift = getAutoDetectedShift(refDate);
    activeDayMode = autoShift.isSaturday ? "sab" : "lv";

    // Actualizar badges informativos de detección automática
    const dayBadge = document.getElementById("cm-auto-day-badge");
    const shiftBadge = document.getElementById("cm-auto-shift-badge");
    const weekBadge = document.getElementById("cm-auto-week-badge");
    if (dayBadge) dayBadge.textContent = activeDayMode === "sab" ? "Sábado" : "Lunes a Viernes";
    if (shiftBadge) shiftBadge.textContent = autoShift.shortName;
    if (weekBadge) weekBadge.textContent = `Semana ${getWeekNumber(refDate)}`;

    // Si los inputs ya tienen valores válidos, sincronizar
    let hasExistingValues = false;
    if (startInputEl && isValidTimeStr(startInputEl.value)) {
        const parts = startInputEl.value.trim().split(":");
        startHour24 = parseInt(parts[0], 10);
        startMinute = parseInt(parts[1], 10);
        hasExistingValues = true;
    } else {
        const parts = autoShift.start.split(":");
        startHour24 = parseInt(parts[0], 10);
        startMinute = parseInt(parts[1], 10);
    }

    if (endInputEl && isValidTimeStr(endInputEl.value)) {
        const parts = endInputEl.value.trim().split(":");
        endHour24 = parseInt(parts[0], 10);
        endMinute = parseInt(parts[1], 10);
    } else {
        const parts = autoShift.end.split(":");
        endHour24 = parseInt(parts[0], 10);
        endMinute = parseInt(parts[1], 10);
    }

    if (hasExistingValues) {
        if (startHour24 === 6 && endHour24 === 14) {
            selectedShiftId = "manana";
        } else if (startHour24 === 13 && startMinute === 45) {
            selectedShiftId = "mixto";
        } else if (startHour24 === 22 && endHour24 === 6) {
            selectedShiftId = "noche";
        } else {
            selectedShiftId = autoShift.id;
        }
    } else {
        selectedShiftId = autoShift.id;
    }

    applyDayModeRules();
    highlightActiveShiftCard();
    renderTabsValues();
    renderClockForActiveTab();

    const modal = document.getElementById("modalCmTime");
    if (modal) {
        modal.style.display = "flex";
    }
}

/**
 * Cierra el modal de selección de hora
 */
export function closeModalTime() {
    const modal = document.getElementById("modalCmTime");
    if (modal) {
        modal.style.display = "none";
    }
}

/**
 * Aplica reglas estrictas de sábado (Mixto hasta 18:45, Noche BLOQUEADO)
 */
function applyDayModeRules() {
    const mixtoRange = document.getElementById("cm-mixto-range-text");
    const nocheCard = document.getElementById("shift-card-noche");
    const nocheRange = document.getElementById("cm-noche-range-text");
    const lockLabel = document.querySelector(".cm-shift-lock-label");

    if (activeDayMode === "sab") {
        if (mixtoRange) mixtoRange.textContent = "13:45 a 18:45";
        if (nocheCard) {
            nocheCard.classList.add("is-disabled");
            nocheCard.title = "Turno Noche bloqueado los sábados";
        }
        if (nocheRange) nocheRange.textContent = "No disponible sábados";
        if (lockLabel) lockLabel.style.display = "inline-block";

        if (selectedShiftId === "noche") {
            selectedShiftId = "manana";
            startHour24 = 6;
            startMinute = 0;
            endHour24 = 14;
            endMinute = 0;
        }
    } else {
        if (mixtoRange) mixtoRange.textContent = "13:45 a 21:45";
        if (nocheCard) {
            nocheCard.classList.remove("is-disabled");
            nocheCard.title = "Clic para seleccionar Turno Noche";
        }
        if (nocheRange) nocheRange.textContent = "22:00 a 06:00";
        if (lockLabel) lockLabel.style.display = "none";
    }
}

/**
 * Resalta la tarjeta de turno seleccionada
 */
function highlightActiveShiftCard() {
    document.querySelectorAll(".cm-time-shift-card").forEach((card) => {
        const shift = card.dataset.shift;
        const activeBadge = card.querySelector(".cm-shift-badge-active");

        if (shift === selectedShiftId) {
            card.classList.add("is-selected");
            if (activeBadge) activeBadge.style.display = "inline-block";
        } else {
            card.classList.remove("is-selected");
            if (activeBadge) activeBadge.style.display = "none";
        }
    });
}

/**
 * Actualiza los textos de las pestañas de inicio y término
 */
function renderTabsValues() {
    const startValEl = document.getElementById("cm-tab-start-val");
    const endValEl = document.getElementById("cm-tab-end-val");
    const tabStart = document.getElementById("cm-tab-start");
    const tabEnd = document.getElementById("cm-tab-end");
    const indicator = document.getElementById("cm-active-field-indicator");

    const startStr24 = `${String(startHour24).padStart(2, "0")}:${String(startMinute).padStart(2, "0")}`;
    const endStr24 = `${String(endHour24).padStart(2, "0")}:${String(endMinute).padStart(2, "0")}`;

    if (startValEl) {
        startValEl.textContent = timeFormatMode === "12" ? formatDisplayTime(startStr24, true) : `${startStr24} hrs`;
    }
    if (endValEl) {
        endValEl.textContent = timeFormatMode === "12" ? formatDisplayTime(endStr24, true) : `${endStr24} hrs`;
    }

    if (tabStart && tabEnd) {
        if (activeTargetTab === "start") {
            tabStart.classList.add("is-active");
            tabEnd.classList.remove("is-active");
            if (indicator) indicator.textContent = "Ajustando: Hora de Inicio";
        } else {
            tabEnd.classList.add("is-active");
            tabStart.classList.remove("is-active");
            if (indicator) indicator.textContent = "Ajustando: Hora de Término";
        }
    }
}

/**
 * Renderiza el reloj digital para la pestaña activa (inicio o término)
 */
function renderClockForActiveTab() {
    const curH = activeTargetTab === "start" ? startHour24 : endHour24;
    const curM = activeTargetTab === "start" ? startMinute : endMinute;

    const hourEl = document.getElementById("cm-time-display-hour");
    const minEl = document.getElementById("cm-time-display-min");
    const amBtn = document.getElementById("cm-time-btn-am");
    const pmBtn = document.getElementById("cm-time-btn-pm");
    const badge24 = document.getElementById("cm-time-24h-badge");

    if (timeFormatMode === "12") {
        let h12 = curH % 12;
        if (h12 === 0) h12 = 12;
        if (hourEl) hourEl.value = String(h12).padStart(2, "0");

        const isPM = curH >= 12;
        if (amBtn && pmBtn) {
            amBtn.style.display = "block";
            pmBtn.style.display = "block";
            if (isPM) {
                pmBtn.classList.add("is-active");
                amBtn.classList.remove("is-active");
            } else {
                amBtn.classList.add("is-active");
                pmBtn.classList.remove("is-active");
            }
        }
        if (badge24) badge24.style.display = "none";
    } else {
        if (hourEl) hourEl.value = String(curH).padStart(2, "0");
        if (amBtn && pmBtn) {
            amBtn.style.display = "none";
            pmBtn.style.display = "none";
        }
        if (badge24) badge24.style.display = "block";
    }

    if (minEl) {
        minEl.value = String(curM).padStart(2, "0");
    }

    // Actualizar chips de minutos activos
    const minuteFormatted = String(curM).padStart(2, "0");
    document.querySelectorAll(".cm-time-chip").forEach((chip) => {
        if (chip.dataset.min === minuteFormatted) {
            chip.classList.add("is-active");
        } else {
            chip.classList.remove("is-active");
        }
    });

    renderTabsValues();
}

/**
 * Modifica la hora o minuto del campo activo
 */
function updateActiveField(deltaHours = 0, deltaMinutes = 0, exactMinutes = null) {
    if (activeTargetTab === "start") {
        if (deltaHours !== 0) {
            startHour24 = (startHour24 + deltaHours + 24) % 24;
        }
        if (exactMinutes !== null) {
            startMinute = exactMinutes;
        } else if (deltaMinutes !== 0) {
            startMinute = (startMinute + deltaMinutes + 60) % 60;
        }
    } else {
        if (deltaHours !== 0) {
            endHour24 = (endHour24 + deltaHours + 24) % 24;
        }
        if (exactMinutes !== null) {
            endMinute = exactMinutes;
        } else if (deltaMinutes !== 0) {
            endMinute = (endMinute + deltaMinutes + 60) % 60;
        }
    }
    renderClockForActiveTab();
}

/**
 * Inicializa todos los eventos interactivos del modal
 */
function initShiftTimeModalEvents() {
    if (isInitialized) return;

    const modal = document.getElementById("modalCmTime");
    const closeBtn = document.getElementById("cm-time-close-btn");
    const cancelBtn = document.getElementById("cm-time-cancel-btn");
    const confirmBtn = document.getElementById("cm-time-confirm-btn");
    const tabStart = document.getElementById("cm-tab-start");
    const tabEnd = document.getElementById("cm-tab-end");
    const mode12Btn = document.getElementById("cm-time-mode-12");
    const mode24Btn = document.getElementById("cm-time-mode-24");
    const hourUp = document.getElementById("cm-time-hour-up");
    const hourDown = document.getElementById("cm-time-hour-down");
    const minUp = document.getElementById("cm-time-min-up");
    const minDown = document.getElementById("cm-time-min-down");
    const amBtn = document.getElementById("cm-time-btn-am");
    const pmBtn = document.getElementById("cm-time-btn-pm");

    // Pestañas Inicio / Término
    if (tabStart) {
        tabStart.addEventListener("click", (e) => {
            e.stopPropagation();
            activeTargetTab = "start";
            renderClockForActiveTab();
        });
    }

    if (tabEnd) {
        tabEnd.addEventListener("click", (e) => {
            e.stopPropagation();
            activeTargetTab = "end";
            renderClockForActiveTab();
        });
    }

    // Toggle 12H vs 24H
    if (mode12Btn) {
        mode12Btn.addEventListener("click", (e) => {
            e.stopPropagation();
            timeFormatMode = "12";
            mode12Btn.classList.add("is-active");
            mode24Btn?.classList.remove("is-active");
            renderClockForActiveTab();
        });
    }

    if (mode24Btn) {
        mode24Btn.addEventListener("click", (e) => {
            e.stopPropagation();
            timeFormatMode = "24";
            mode24Btn.classList.add("is-active");
            mode12Btn?.classList.remove("is-active");
            renderClockForActiveTab();
        });
    }

    // Flechas
    if (hourUp) {
        hourUp.addEventListener("click", (e) => {
            e.stopPropagation();
            updateActiveField(1, 0);
        });
    }

    if (hourDown) {
        hourDown.addEventListener("click", (e) => {
            e.stopPropagation();
            updateActiveField(-1, 0);
        });
    }

    if (minUp) {
        minUp.addEventListener("click", (e) => {
            e.stopPropagation();
            updateActiveField(0, 1);
        });
    }

    if (minDown) {
        minDown.addEventListener("click", (e) => {
            e.stopPropagation();
            updateActiveField(0, -1);
        });
    }

    // Escritura directa en dígitos del reloj
    const hourInput = document.getElementById("cm-time-display-hour");
    const minInput = document.getElementById("cm-time-display-min");

    if (hourInput) {
        const handleHourChange = () => {
            const targetTab = hourInput.dataset.targetTab || activeTargetTab;
            let val = parseInt(hourInput.value, 10);
            if (isNaN(val)) {
                renderClockForActiveTab();
                return;
            }
            if (timeFormatMode === "12") {
                if (val >= 13 && val <= 23) {
                    if (targetTab === "start") startHour24 = val;
                    else endHour24 = val;
                } else {
                    if (val < 1) val = 1;
                    if (val > 12) val = 12;
                    const isPM = (targetTab === "start" ? startHour24 : endHour24) >= 12;
                    let h24 = val % 12;
                    if (isPM) h24 += 12;
                    if (targetTab === "start") startHour24 = h24;
                    else endHour24 = h24;
                }
            } else {
                if (val < 0) val = 0;
                if (val > 23) val = 23;
                if (targetTab === "start") startHour24 = val;
                else endHour24 = val;
            }
            checkCustomShiftState();
            renderClockForActiveTab();
        };

        hourInput.addEventListener("focus", () => hourInput.select());
        hourInput.addEventListener("change", handleHourChange);
        hourInput.addEventListener("blur", handleHourChange);
        hourInput.addEventListener("keydown", (e) => {
            if (e.key === "Enter") {
                hourInput.blur();
                if (minInput) minInput.focus();
            }
        });
    }

    if (minInput) {
        const handleMinChange = () => {
            const targetTab = minInput.dataset.targetTab || activeTargetTab;
            let val = parseInt(minInput.value, 10);
            if (isNaN(val)) {
                renderClockForActiveTab();
                return;
            }
            if (val < 0) val = 0;
            if (val > 59) val = 59;
            if (targetTab === "start") startMinute = val;
            else endMinute = val;
            checkCustomShiftState();
            renderClockForActiveTab();
        };

        minInput.addEventListener("focus", () => minInput.select());
        minInput.addEventListener("change", handleMinChange);
        minInput.addEventListener("blur", handleMinChange);
        minInput.addEventListener("keydown", (e) => {
            if (e.key === "Enter") {
                minInput.blur();
            }
        });
    }

    // AM / PM
    if (amBtn) {
        amBtn.addEventListener("click", (e) => {
            e.stopPropagation();
            if (activeTargetTab === "start") {
                if (startHour24 >= 12) startHour24 -= 12;
            } else {
                if (endHour24 >= 12) endHour24 -= 12;
            }
            renderClockForActiveTab();
        });
    }

    if (pmBtn) {
        pmBtn.addEventListener("click", (e) => {
            e.stopPropagation();
            if (activeTargetTab === "start") {
                if (startHour24 < 12) startHour24 += 12;
            } else {
                if (endHour24 < 12) endHour24 += 12;
            }
            renderClockForActiveTab();
        });
    }

    // Chips de minutos (:00, :15, :30, :45)
    document.querySelectorAll(".cm-time-chip").forEach((chip) => {
        chip.addEventListener("click", (e) => {
            e.stopPropagation();
            const val = parseInt(chip.dataset.min, 10);
            if (!isNaN(val)) {
                updateActiveField(0, 0, val);
            }
        });
    });

    // Clic en tarjetas de turno
    const cardManana = document.getElementById("shift-card-manana");
    if (cardManana) {
        cardManana.addEventListener("click", (e) => {
            e.stopPropagation();
            selectedShiftId = "manana";
            startHour24 = 6;
            startMinute = 0;
            endHour24 = 14;
            endMinute = 0;
            highlightActiveShiftCard();
            renderTabsValues();
            renderClockForActiveTab();
        });
    }

    const cardMixto = document.getElementById("shift-card-mixto");
    if (cardMixto) {
        cardMixto.addEventListener("click", (e) => {
            e.stopPropagation();
            selectedShiftId = "mixto";
            startHour24 = 13;
            startMinute = 45;
            endHour24 = activeDayMode === "sab" ? 18 : 21;
            endMinute = 45;
            highlightActiveShiftCard();
            renderTabsValues();
            renderClockForActiveTab();
        });
    }

    const cardNoche = document.getElementById("shift-card-noche");
    if (cardNoche) {
        cardNoche.addEventListener("click", (e) => {
            e.stopPropagation();
            if (activeDayMode === "sab") return; // BLOQUEADO ESTRICTO EN SÁBADOS
            selectedShiftId = "noche";
            startHour24 = 22;
            startMinute = 0;
            endHour24 = 6;
            endMinute = 0;
            highlightActiveShiftCard();
            renderTabsValues();
            renderClockForActiveTab();
        });
    }

    // Confirmar Turno y Horario
    if (confirmBtn) {
        confirmBtn.addEventListener("click", (e) => {
            e.stopPropagation();

            // Bloqueo estricto: En sábado no se puede confirmar turno noche
            if (activeDayMode === "sab" && (selectedShiftId === "noche" || (startHour24 === 22 && endHour24 === 6))) {
                if (window.gisAlert) {
                    window.gisAlert({
                        title: "Seleccionar turno válido",
                        text: "Los turnos nocturnos no operan durante los días sábados en planta.",
                        confirmButtonColor: "#033966"
                    });
                } else if (window.Swal) {
                    Swal.fire({
                        title: "Seleccionar turno válido",
                        text: "Los turnos nocturnos no operan durante los días sábados en planta.",
                        confirmButtonColor: "#033966",
                        customClass: { popup: 'swal2-gis-popup' }
                    });
                } else if (window.toastpremium) {
                    window.toastpremium("Los sábados no está habilitado el Turno Noche en planta.", "warning");
                }
                return;
            }

            // Formatear SIEMPRE a HH:mm (24 horas) para Laravel
            const formattedStart24 = `${String(startHour24).padStart(2, "0")}:${String(startMinute).padStart(2, "0")}`;
            const formattedEnd24 = `${String(endHour24).padStart(2, "0")}:${String(endMinute).padStart(2, "0")}`;

            if (formattedStart24 === formattedEnd24) {
                if (window.gisAlert) {
                    window.gisAlert({
                        title: "Corregir intervalo de horario",
                        text: "La hora de inicio y de término deben ser distintas para registrar el turno.",
                        confirmButtonColor: "#033966"
                    });
                } else if (window.Swal) {
                    Swal.fire({
                        title: "Corregir intervalo de horario",
                        text: "La hora de inicio y de término deben ser distintas para registrar el turno.",
                        confirmButtonColor: "#033966",
                        customClass: { popup: 'swal2-gis-popup' }
                    });
                } else if (window.toastpremium) {
                    window.toastpremium("La hora de inicio y de término no pueden ser iguales.", "warning");
                }
                return;
            }

            if (activeTargetStart) {
                activeTargetStart.value = formattedStart24;
                activeTargetStart.dispatchEvent(new Event("input", { bubbles: true }));
                activeTargetStart.dispatchEvent(new Event("change", { bubbles: true }));
            }

            if (activeTargetEnd) {
                activeTargetEnd.value = formattedEnd24;
                activeTargetEnd.dispatchEvent(new Event("input", { bubbles: true }));
                activeTargetEnd.dispatchEvent(new Event("change", { bubbles: true }));
            }

            if (activeDisplayLabel) {
                let shiftName = "Turno";
                let iconName = "Color_Icons/Generales/Turno_Manana.png";
                let hoursColor = "#0a8504";
                let hoursBg = "#e8f5e9";
                let hoursBorder = "#a5d6a7";

                if (selectedShiftId === "manana" || (formattedStart24 === "06:00" && formattedEnd24 === "14:00")) {
                    shiftName = "Turno Mañana";
                    iconName = "Color_Icons/Generales/Turno_Manana.png";
                    hoursColor = "#0a8504";
                    hoursBg = "#e8f5e9";
                    hoursBorder = "#a5d6a7";
                } else if (selectedShiftId === "mixto" || formattedStart24 === "13:45") {
                    shiftName = activeDayMode === "sab" ? "Turno Mixto (Sábado)" : "Turno Mixto";
                    iconName = "Color_Icons/Generales/Turno_Mixto.png";
                    hoursColor = "#b45309";
                    hoursBg = "#fef3c7";
                    hoursBorder = "#fde68a";
                } else if (selectedShiftId === "noche" || formattedStart24 === "22:00") {
                    shiftName = "Turno Noche";
                    iconName = "Color_Icons/Generales/Turno_Noche.png";
                    hoursColor = "#4338ca";
                    hoursBg = "#e0e7ff";
                    hoursBorder = "#c7d2fe";
                } else {
                    shiftName = "Turno Especial";
                    iconName = "Color_Icons/Generales/Hora.png";
                    hoursColor = "#033966";
                    hoursBg = "#e0f2fe";
                    hoursBorder = "#bae6fd";
                }

                if (activeDisplayLabel.tagName === "INPUT") {
                    activeDisplayLabel.value = `${shiftName}: ${formattedStart24} - ${formattedEnd24} hrs`;
                } else {
                    activeDisplayLabel.innerHTML = getShiftTriggerHtml(formattedStart24, formattedEnd24);
                }
                activeDisplayLabel.classList.remove("is-empty");
                activeDisplayLabel.dispatchEvent(new Event("input", { bubbles: true }));
                activeDisplayLabel.dispatchEvent(new Event("change", { bubbles: true }));
            }

            closeModalTime();
        });
    }

    // Cancelar y Cerrar
    if (cancelBtn) cancelBtn.addEventListener("click", closeModalTime);
    if (closeBtn) closeBtn.addEventListener("click", closeModalTime);
    if (modal) {
        modal.addEventListener("click", (e) => {
            if (e.target === modal) closeModalTime();
        });
    }

    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape" && modal && modal.style.display !== "none") {
            closeModalTime();
        }
    });

    isInitialized = true;
}

/**
 * Fallback para abrir un selector de hora individual (si se requiere)
 */
export function openModalTime(inputEl, displayLabelEl = null, title = "Seleccionar Hora") {
    openModalShiftTime(inputEl, inputEl, displayLabelEl);
}
