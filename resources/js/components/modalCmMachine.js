/**
 * Componente interactivo para el modal de selección de máquinas (#modalCmMachine)
 * Proporciona filtrado express, búsqueda instantánea y tarjetas táctiles para ~50 máquinas
 * con distinción visual inmediata para máquinas pareadas/duales (1 y 2, 5 y 6, 25 y 26, 27 y 28).
 */

const PAIRED_MACHINE_INDICES = [1, 5, 25, 27];

/**
 * Genera el catálogo completo de máquinas respetando las máquinas pareadas oficiales.
 */
export function buildMachinesCatalog() {
    const list = [];
    for (let i = 1; i <= 45; i++) {
        if (PAIRED_MACHINE_INDICES.includes(i)) {
            // Opción Pareada / Dual
            list.push({
                value: `${i}_${i + 1}`,
                label: `Máquina ${i} y ${i + 1}`,
                shortLabel: `M${i} + M${i + 1}`,
                isPaired: true,
                numA: i,
                numB: i + 1,
                primaryNum: i,
            });
            // Opciones individuales
            list.push({
                value: `${i}`,
                label: `Máquina ${i}`,
                shortLabel: `M${i}`,
                isPaired: false,
                num: i,
                primaryNum: i,
            });
            list.push({
                value: `${i + 1}`,
                label: `Máquina ${i + 1}`,
                shortLabel: `M${i + 1}`,
                isPaired: false,
                num: i + 1,
                primaryNum: i + 1,
            });
            i++; // Se salta el siguiente porque ya se incluyó como pareja e individual
        } else {
            list.push({
                value: `${i}`,
                label: `Máquina ${i}`,
                shortLabel: `M${i}`,
                isPaired: false,
                num: i,
                primaryNum: i,
            });
        }
    }
    return list;
}

export const MACHINES_CATALOG = buildMachinesCatalog();

let activeTargetHiddenInput = null;
let activeDisplayTrigger = null;
let currentSelectedValue = null;
let currentFilterTab = "all";
let currentSearchQuery = "";
let isInitialized = false;
let occupiedMachinesList =
    typeof window !== "undefined" && Array.isArray(window.occupiedMachines)
        ? window.occupiedMachines
        : [];

/**
 * Determina si una máquina del catálogo está ocupada en el sistema según la lista activa.
 * Soporta validación cruzada: si una máquina pareada (ej: 1 y 2) está ocupada, bloquea 1, 2 y la dual;
 * y si una individual (ej: 1) está ocupada, bloquea la individual y la dual (1 y 2).
 */
export function getMachineOccupiedInfo(item, occupiedList = null) {
    const list =
        occupiedList ||
        (occupiedMachinesList && occupiedMachinesList.length > 0
            ? occupiedMachinesList
            : null) ||
        (typeof window !== "undefined" && Array.isArray(window.occupiedMachines)
            ? window.occupiedMachines
            : null);

    if (!item || !list || !Array.isArray(list) || list.length === 0) {
        return null;
    }

    const itemVal = String(item.value).trim().toLowerCase();

    for (const occ of occupiedList) {
        if (!occ) continue;

        // Soporta formatos de API ('machine'/'process') y de Modelo Eloquent ('maquina'/'proceso')
        const rawVal =
            occ.machine !== undefined && occ.machine !== null
                ? occ.machine
                : occ.maquina !== undefined && occ.maquina !== null
                    ? occ.maquina
                    : null;

        if (rawVal === undefined || rawVal === null) continue;

        const occVal = String(rawVal).trim().toLowerCase();
        const procName = occ.proceso || occ.process || "En uso";
        const opName = occ.operator || "";

        // 1. Coincidencia exacta (ej: '5' === '5' o '1_2' === '1_2')
        if (occVal === itemVal) {
            return {
                ...occ,
                maquina: occVal,
                machine: occVal,
                proceso: procName,
                operator: opName,
            };
        }

        // 2. Si este item es pareado (ej: 1_2), y alguna de sus dos máquinas está ocupada individualmente
        if (item.isPaired) {
            if (occVal === String(item.numA) || occVal === String(item.numB)) {
                return {
                    ...occ,
                    maquina: occVal,
                    machine: occVal,
                    proceso: procName,
                    operator: opName,
                };
            }
        }

        // 3. Si este item es individual (ej: 1 o 2), y la máquina dual correspondiente está ocupada (ej: 1_2)
        if (!item.isPaired && occVal.includes("_")) {
            const parts = occVal.split("_").map((p) => p.trim());
            if (parts.includes(String(item.num))) {
                return {
                    ...occ,
                    maquina: occVal,
                    machine: occVal,
                    proceso: procName,
                    operator: opName,
                };
            }
        }
    }

    return null;
}

/**
 * Formatea el valor de máquina (ej: '1_2' -> 'Máquina 1 y 2', '32' -> 'Máquina 32')
 */
export function formatDisplayMachine(val) {
    if (!val) return "";
    const found = MACHINES_CATALOG.find((m) => m.value === String(val).trim());
    if (found) return found.label;
    if (String(val).includes("_")) {
        return `Máquina ${String(val).replace("_", " y ")}`;
    }
    return `Máquina ${val}`;
}

export function getMachinePlaceholderHtml() {
    return `
        <div style="display: flex; align-items: center; justify-content: center; width: 100%; color: #64748b; white-space: nowrap; overflow: hidden;">
            <span style="font-weight: 800; font-size: 0.86rem; color: #64748b; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">Elegir máquina</span>
        </div>
    `;
}

export function getMachineTriggerHtml(value) {
    const displayText = formatDisplayMachine(value);
    return `
        <div style="display: flex; align-items: center; justify-content: center; width: 100%; color: #033966; white-space: nowrap; overflow: hidden;">
            <span style="font-weight: 800; font-size: 0.94rem; color: #033966; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">${displayText}</span>
        </div>
    `;
}

/**
 * Abre el modal de máquinas sincronizando con la selección actual.
 */
export function openModalMachine(
    hiddenInputEl,
    triggerDisplayEl,
    title = "Seleccionar Máquina de Trabajo",
) {
    initMachineModalEvents();
    activeTargetHiddenInput = hiddenInputEl;
    activeDisplayTrigger = triggerDisplayEl;

    const titleEl = document.getElementById("cm-machine-modal-title");
    if (titleEl) {
        titleEl.textContent = title;
    }

    currentSelectedValue =
        hiddenInputEl && hiddenInputEl.value
            ? String(hiddenInputEl.value).trim()
            : null;
    currentFilterTab = "all";
    currentSearchQuery = "";

    const searchInput = document.getElementById("cm-machine-search");
    if (searchInput) {
        searchInput.value = "";
    }
    const clearBtn = document.getElementById("cm-machine-search-clear");
    if (clearBtn) {
        clearBtn.style.display = "none";
    }

    // Resetear tabs a "all"
    const tabs = document.querySelectorAll(".cm-mach-tab");
    tabs.forEach((tab) => {
        if (tab.getAttribute("data-filter") === "all") {
            tab.classList.add("is-active");
        } else {
            tab.classList.remove("is-active");
        }
    });

    // Sincronizar catálogo de máquinas ocupadas inicial inyectado por Blade
    if (
        typeof window !== "undefined" &&
        Array.isArray(window.occupiedMachines)
    ) {
        occupiedMachinesList = window.occupiedMachines;
    }

    // Actualización en segundo plano desde el endpoint API
    if (typeof fetch !== "undefined") {
        fetch("/machinesOccupied/api")
            .then((res) => (res.ok ? res.json() : null))
            .then((data) => {
                if (Array.isArray(data)) {
                    occupiedMachinesList = data;
                    if (typeof window !== "undefined") {
                        window.occupiedMachines = data;
                    }
                    renderMachines();
                    updateSelectionUI();
                }
            })
            .catch(() => {
                // Conservar lista previa en caso de falla de red
            });
    }

    updateSelectionUI();
    renderMachines();

    const modal = document.getElementById("modalCmMachine");
    if (modal) {
        modal.style.display = "flex";
        setTimeout(() => {
            if (searchInput) searchInput.focus();
        }, 60);
    }
}

/**
 * Cierra el modal de máquinas
 */
export function closeModalMachine() {
    const modal = document.getElementById("modalCmMachine");
    if (modal) {
        modal.style.display = "none";
    }
}

/**
 * Confirma la selección actual en los inputs objetivos
 */
function confirmMachineSelection() {
    if (!currentSelectedValue) return;

    // Verificar si la máquina actual está ocupada antes de confirmar
    const selectedMach = MACHINES_CATALOG.find(
        (m) => m.value === currentSelectedValue,
    );
    if (
        selectedMach &&
        getMachineOccupiedInfo(selectedMach, occupiedMachinesList)
    ) {
        return;
    }

    if (activeTargetHiddenInput) {
        activeTargetHiddenInput.value = currentSelectedValue;
        activeTargetHiddenInput.dispatchEvent(
            new Event("input", { bubbles: true }),
        );
        activeTargetHiddenInput.dispatchEvent(
            new Event("change", { bubbles: true }),
        );
    }

    if (activeDisplayTrigger) {
        activeDisplayTrigger.innerHTML =
            getMachineTriggerHtml(currentSelectedValue);
        activeDisplayTrigger.classList.remove("is-empty");
        activeDisplayTrigger.dispatchEvent(
            new Event("input", { bubbles: true }),
        );
        activeDisplayTrigger.dispatchEvent(
            new Event("change", { bubbles: true }),
        );
    }

    closeModalMachine();
}

/**
 * Actualiza el indicador inferior y habilita/deshabilita el botón de confirmar
 */
function updateSelectionUI() {
    const previewText = document.getElementById("cm-machine-selected-text");
    const confirmBtn = document.getElementById("cm-machine-confirm-btn");

    if (currentSelectedValue) {
        const selectedMach = MACHINES_CATALOG.find(
            (m) => m.value === currentSelectedValue,
        );
        const occInfo = selectedMach
            ? getMachineOccupiedInfo(selectedMach, occupiedMachinesList)
            : null;
        const isOccupied = Boolean(occInfo);

        const label = formatDisplayMachine(currentSelectedValue);
        if (previewText) {
            previewText.textContent = isOccupied ? `${label} (OCUPADA)` : label;
        }

        if (confirmBtn) {
            if (isOccupied) {
                confirmBtn.disabled = true;
                confirmBtn.style.opacity = "0.45";
                confirmBtn.style.cursor = "not-allowed";
                confirmBtn.title = `Máquina ocupada en proceso: ${occInfo.proceso || "Producción"}`;
            } else {
                confirmBtn.disabled = false;
                confirmBtn.style.opacity = "1";
                confirmBtn.style.cursor = "pointer";
                confirmBtn.title = "Confirmar máquina seleccionada";
            }
        }
    } else {
        if (previewText) previewText.textContent = "Ninguna seleccionada";
        if (confirmBtn) {
            confirmBtn.disabled = true;
            confirmBtn.style.opacity = "0.5";
            confirmBtn.style.cursor = "not-allowed";
            confirmBtn.title = "Selecciona una máquina";
        }
    }
}

/**
 * Renderiza el grid de máquinas según la pestaña activa y el texto de búsqueda.
 */
function renderMachines() {
    const grid = document.getElementById("cm-machine-grid");
    const noResults = document.getElementById("cm-machine-no-results");
    if (!grid) return;

    grid.innerHTML = "";

    const query = currentSearchQuery.trim().toLowerCase();

    const filtered = MACHINES_CATALOG.filter((item) => {
        // Filtro por pestaña
        if (currentFilterTab === "paired" && !item.isPaired) return false;
        if (
            currentFilterTab === "range_1_15" &&
            (item.primaryNum < 1 || item.primaryNum > 15)
        )
            return false;
        if (
            currentFilterTab === "range_16_30" &&
            (item.primaryNum < 16 || item.primaryNum > 30)
        )
            return false;
        if (
            currentFilterTab === "range_31_45" &&
            (item.primaryNum < 31 || item.primaryNum > 45)
        )
            return false;

        // Filtro por búsqueda de texto
        if (query) {
            // Si el usuario escribe palabras como "doble", "dual", "y", "pareada"
            if (
                (query === "doble" ||
                    query === "dual" ||
                    query === "pareada" ||
                    query === "y") &&
                item.isPaired
            ) {
                return true;
            }
            const labelLower = item.label.toLowerCase();
            const valLower = item.value.toLowerCase();
            const shortLower = item.shortLabel.toLowerCase();

            // Coincidencia exacta de número o parcial
            if (
                labelLower.includes(query) ||
                valLower === query ||
                shortLower.includes(query)
            ) {
                return true;
            }
            // Si busca por solo dígitos (ej: "5")
            if (
                item.isPaired &&
                (String(item.numA) === query || String(item.numB) === query)
            ) {
                return true;
            }
            if (!item.isPaired && String(item.num) === query) {
                return true;
            }
            return false;
        }

        return true;
    });

    if (filtered.length === 0) {
        if (noResults) noResults.style.display = "block";
        return;
    }

    if (noResults) noResults.style.display = "none";

    filtered.forEach((mach) => {
        const card = document.createElement("div");
        const occInfo = getMachineOccupiedInfo(mach, occupiedMachinesList);
        const isOccupied = Boolean(occInfo);

        card.className = `cm-machine-card ${mach.isPaired ? "is-paired-card" : ""} ${isOccupied ? "is-occupied" : ""}`;
        card.setAttribute("data-value", mach.value);
        card.setAttribute("tabindex", isOccupied ? "-1" : "0");
        card.setAttribute("role", "button");

        if (isOccupied) {
            card.setAttribute("aria-disabled", "true");
            card.title = `Máquina Ocupada (${occInfo.proceso || "En uso"}) - No disponible`;
        }

        const isSelected = currentSelectedValue === mach.value;
        if (isSelected && !isOccupied) {
            card.classList.add("is-selected");
        }

        const baseUrl = (window.baseUrl || "").replace(/\/$/, "");
        const cncIcon = baseUrl ? `${baseUrl}/images/Color_Icons/Generales/ProgramsCNC.png` : "/images/Color_Icons/Generales/ProgramsCNC.png";
        const lockIcon = (isOcc) => baseUrl
            ? `${baseUrl}/images/${isOcc ? "Color_Icons/Generales/Bloq-Maqu-Icon.png" : "Color_Icons/Generales/Desbloc-Maq-Icon.png"}`
            : (isOcc ? "/images/Color_Icons/Generales/Bloq-Maqu-Icon.png" : "/images/Color_Icons/Generales/Desbloc-Maq-Icon.png");

        if (mach.isPaired) {
            card.innerHTML = `
                <div class="cm-mach-badge-paired">Dual</div>
                ${isOccupied ? `<div class="cm-mach-badge-occupied">Ocupada</div>` : `<div class="cm-mach-badge-available">Disponible</div>`}
                <div class="cm-mach-icon-wrap">
                    <img src="${cncIcon}" class="cm-mach-icon" alt="Máquina">
                </div>
                <div class="cm-mach-main-label">${mach.label}</div>
                <div class="cm-mach-sub-label">${isOccupied ? (occInfo.proceso ? `En: ${occInfo.proceso}` : "Ocupada") : `Opera ${mach.shortLabel}`}</div>
                <img src="${lockIcon(isOccupied)}"
                     class="cm-mach-status-icon ${isOccupied ? "is-locked" : "is-unlocked"}"
                     alt="${isOccupied ? "Máquina Bloqueada" : "Máquina Desbloqueada"}"
                     title="${isOccupied ? `Bloqueada (${occInfo.proceso || "Ocupada"})` : "Desbloqueada / Disponible"}">
                <div class="cm-mach-check-mark">&#10003;</div>
            `;
        } else {
            card.innerHTML = `
                ${isOccupied ? `<div class="cm-mach-badge-occupied">Ocupada</div>` : `<div class="cm-mach-badge-available">Disponible</div>`}
                <div class="cm-mach-icon-wrap">
                    <img src="${cncIcon}" class="cm-mach-icon" alt="Máquina">
                </div>
                <div class="cm-mach-number">${mach.num}</div>
                <div class="cm-mach-main-label">${mach.label}</div>
                ${isOccupied && occInfo.proceso ? `<div class="cm-mach-process-name">${occInfo.proceso}</div>` : ""}
                <img src="${lockIcon(isOccupied)}"
                     class="cm-mach-status-icon ${isOccupied ? "is-locked" : "is-unlocked"}"
                     alt="${isOccupied ? "Máquina Bloqueada" : "Máquina Desbloqueada"}"
                     title="${isOccupied ? `Bloqueada (${occInfo.proceso || "Ocupada"})` : "Desbloqueada / Disponible"}">
                <div class="cm-mach-check-mark">&#10003;</div>
            `;
        }

        // Selección al hacer click (bloqueado si está ocupada)
        card.addEventListener("click", () => {
            if (isOccupied) {
                // Notificación visual rápida de bloqueo
                card.classList.add("cm-card-shake");
                setTimeout(() => card.classList.remove("cm-card-shake"), 400);
                return;
            }
            currentSelectedValue = mach.value;
            // Remover selección previa
            grid.querySelectorAll(".cm-machine-card").forEach((c) =>
                c.classList.remove("is-selected"),
            );
            card.classList.add("is-selected");
            updateSelectionUI();
        });

        // Doble click confirma de inmediato si está disponible
        card.addEventListener("dblclick", () => {
            if (isOccupied) return;
            currentSelectedValue = mach.value;
            confirmMachineSelection();
        });

        // Enter en la tarjeta seleccionada confirma si está disponible
        card.addEventListener("keydown", (e) => {
            if (e.key === "Enter" || e.key === " ") {
                e.preventDefault();
                if (isOccupied) return;
                currentSelectedValue = mach.value;
                grid.querySelectorAll(".cm-machine-card").forEach((c) =>
                    c.classList.remove("is-selected"),
                );
                card.classList.add("is-selected");
                updateSelectionUI();
                confirmMachineSelection();
            }
        });

        grid.appendChild(card);
    });
}

/**
 * Inicializa los listeners una sola vez.
 */
function initMachineModalEvents() {
    if (isInitialized) return;
    isInitialized = true;

    const modal = document.getElementById("modalCmMachine");
    const closeBtn = document.getElementById("cm-machine-close-btn");
    const cancelBtn = document.getElementById("cm-machine-cancel-btn");
    const confirmBtn = document.getElementById("cm-machine-confirm-btn");
    const searchInput = document.getElementById("cm-machine-search");
    const clearBtn = document.getElementById("cm-machine-search-clear");
    const tabsContainer = document.getElementById("cm-machine-tabs");

    if (closeBtn) {
        closeBtn.addEventListener("click", closeModalMachine);
    }
    if (cancelBtn) {
        cancelBtn.addEventListener("click", closeModalMachine);
    }
    if (confirmBtn) {
        confirmBtn.addEventListener("click", confirmMachineSelection);
    }

    // Cerrar al dar click en el fondo oscuro
    if (modal) {
        modal.addEventListener("click", (e) => {
            if (e.target === modal) {
                closeModalMachine();
            }
        });
    }

    // Tecla Escape cierra el modal
    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") {
            const m = document.getElementById("modalCmMachine");
            if (m && m.style.display === "flex") {
                closeModalMachine();
            }
        }
    });

    // Pestañas de filtrado
    if (tabsContainer) {
        tabsContainer.addEventListener("click", (e) => {
            const tab = e.target.closest(".cm-mach-tab");
            if (!tab) return;
            const filter = tab.getAttribute("data-filter");
            if (!filter) return;

            currentFilterTab = filter;
            tabsContainer
                .querySelectorAll(".cm-mach-tab")
                .forEach((t) => t.classList.remove("is-active"));
            tab.classList.add("is-active");
            renderMachines();
        });
    }

    // Búsqueda en tiempo real
    if (searchInput) {
        searchInput.addEventListener("input", () => {
            currentSearchQuery = searchInput.value || "";
            if (clearBtn) {
                clearBtn.style.display = currentSearchQuery ? "flex" : "none";
            }
            renderMachines();
        });

        searchInput.addEventListener("keydown", (e) => {
            if (e.key === "Enter") {
                e.preventDefault();
                // Si hay una tarjeta visible seleccionada o la primera resultante
                const firstCard = document.querySelector(
                    "#cm-machine-grid .cm-machine-card",
                );
                if (firstCard) {
                    currentSelectedValue = firstCard.getAttribute("data-value");
                    confirmMachineSelection();
                }
            }
        });
    }

    if (clearBtn) {
        clearBtn.addEventListener("click", () => {
            if (searchInput) {
                searchInput.value = "";
                searchInput.focus();
            }
            currentSearchQuery = "";
            clearBtn.style.display = "none";
            renderMachines();
        });
    }
}
