// ══════════════════════════════════════════════════════════
// MÓDULO NAVDOCK — Controles de Navegación, Buscador Dropdown y Scroll Snap
// ══════════════════════════════════════════════════════════

let cachedSections = [];

export function getCachedSections() {
    return cachedSections;
}

export function updateCachedSections() {
    const elements = document.querySelectorAll("section.section");
    cachedSections = Array.from(elements).map((sec, idx) => {
        const otId = sec.getAttribute("data-ot-id") || "";
        let molding = sec.getAttribute("data-molding") || "";
        if (!molding) {
            const workOrderEl = sec.querySelector(".workOrder-text");
            if (workOrderEl) {
                const textContent = workOrderEl.textContent || "";
                molding = textContent.replace(/^OT:\s*/i, '').replace(new RegExp(`^${otId}\\s*`, 'i'), '').trim();
            }
        }
        const label = molding ? `${otId} - ${molding}` : (otId ? `OT ${otId}` : '');
        return {
            element: sec,
            offsetTop: sec.offsetTop,
            otId: otId,
            molding: molding,
            label: label,
            index: idx
        };
    });

    renderOtDropdownItems();
    updateNavDockCounter();
}

/**
 * Renderiza los items del Dropdown Flotante GIS de Órdenes de Trabajo
 */
export function renderOtDropdownItems(filterText = "") {
    const listEl = document.getElementById("nav-ot-dropdown-list");
    const totalEl = document.getElementById("nav-ot-dropdown-total");
    if (!listEl) return;

    const seenOts = new Set();
    const uniqueSections = [];
    for (const sec of cachedSections) {
        if (sec.otId && !seenOts.has(sec.otId)) {
            seenOts.add(sec.otId);
            uniqueSections.push(sec);
        }
    }

    if (totalEl) totalEl.textContent = `${uniqueSections.length} OTs`;

    const cleanFilter = filterText.trim().toLowerCase();
    const filtered = uniqueSections.filter(sec => {
        if (!cleanFilter) return true;
        return String(sec.otId).toLowerCase().includes(cleanFilter) || 
               (sec.molding && sec.molding.toLowerCase().includes(cleanFilter)) ||
               (sec.label && sec.label.toLowerCase().includes(cleanFilter));
    });

    if (filtered.length === 0) {
        listEl.innerHTML = `<div class="nav-ot-dropdown-empty">No se encontraron OTs con "${filterText}"</div>`;
        return;
    }

    listEl.innerHTML = filtered.map(sec => `
        <div class="nav-ot-dropdown-item" data-ot="${sec.otId}" data-label="${sec.label}" title="${sec.label}">
            <span class="nav-ot-item-number">#${sec.otId}</span>
            <span class="nav-ot-item-molding">${sec.molding || 'Orden de Trabajo'}</span>
        </div>
    `).join("");

    listEl.querySelectorAll(".nav-ot-dropdown-item").forEach(item => {
        item.addEventListener("click", (e) => {
            e.stopPropagation();
            const otId = item.getAttribute("data-ot");
            const label = item.getAttribute("data-label");
            const input = document.getElementById("nav-ot-input");
            const clearBtn = document.getElementById("nav-ot-clear-btn");
            if (input) {
                input.value = label;
                if (clearBtn) clearBtn.style.display = "flex";
            }
            closeOtDropdown();
            window.jumpToOT(otId);
        });
    });
}

export function openOtDropdown(showAll = false) {
    const dropdown = document.getElementById("nav-ot-dropdown-menu");
    const searchBox = document.getElementById("nav-ot-search-box");
    const input = document.getElementById("nav-ot-input");
    if (!dropdown) return;

    renderOtDropdownItems(showAll ? "" : (input?.value || ""));
    dropdown.style.display = "flex";
    if (searchBox) searchBox.classList.add("dropdown-open");
}

export function closeOtDropdown() {
    const dropdown = document.getElementById("nav-ot-dropdown-menu");
    const searchBox = document.getElementById("nav-ot-search-box");
    if (!dropdown) return;
    dropdown.style.display = "none";
    if (searchBox) searchBox.classList.remove("dropdown-open");
}

export function toggleOtDropdown() {
    const dropdown = document.getElementById("nav-ot-dropdown-menu");
    if (!dropdown) return;
    if (dropdown.style.display === "none" || !dropdown.style.display) {
        openOtDropdown(true);
    } else {
        closeOtDropdown();
    }
}

/**
 * Control del Overlay Oficial con Filtro Azul de Carga (.div-opacity de global.css)
 * Se crea dinámicamente al iniciar y se remueve del DOM al terminar para evitar colisiones CSS.
 */
export function showBlueLoadingFilter(show = true) {
    const existing = document.getElementById("pip-loading-blue-filter");
    if (existing) {
        existing.remove();
    }
}

export function getCurrentlyVisibleSectionIndex() {
    if (!cachedSections || cachedSections.length === 0) return 0;
    let closestIndex = 0;
    let minDiff = Infinity;

    for (let i = 0; i < cachedSections.length; i++) {
        const rect = cachedSections[i].element.getBoundingClientRect();
        const diff = Math.abs(rect.top);
        if (diff < minDiff) {
            minDiff = diff;
            closestIndex = i;
        }
    }
    return closestIndex;
}

export function updateNavDockCounter() {
    const counterEl = document.getElementById("nav-ot-counter");
    const tabCounterEl = document.getElementById("tab-handle-counter");
    if (cachedSections.length === 0) return;

    const curIdx = getCurrentlyVisibleSectionIndex();
    const totalLoaded = cachedSections.length;
    const totalAll = window.paginationData?.totalWOs || totalLoaded;
    const remaining = totalAll > totalLoaded ? (totalAll - totalLoaded) : 0;
    const counterText = `OT ${curIdx + 1} / ${totalLoaded}${remaining > 0 ? ` (+${remaining})` : ''}`;

    if (counterEl) counterEl.textContent = counterText;
    if (tabCounterEl) tabCounterEl.textContent = counterText;

    // Sincronizar el input de la OT activa con la pantalla visible si el usuario no está escribiendo en él
    const currentSec = cachedSections[curIdx];
    const navOtInput = document.getElementById("nav-ot-input");
    const navOtClearBtn = document.getElementById("nav-ot-clear-btn");
    if (currentSec && navOtInput && document.activeElement !== navOtInput) {
        navOtInput.value = currentSec.label;
        if (navOtClearBtn) navOtClearBtn.style.display = "flex";
    }

    updateDockLoadMoreButton();
}

/**
 * Actualiza la apariencia y visibilidad del botón "Cargar más" en el dock inferior
 */
export function updateDockLoadMoreButton(isLoading = false) {
    const btnDock = document.getElementById("btn-dock-load-more");
    const divider = document.getElementById("dock-load-divider");
    if (!btnDock) return;

    const pagData = window.paginationData;
    const hasMore = pagData ? pagData.hasMore : false;
    const remaining = pagData ? pagData.remaining : 0;

    const textSpan = btnDock.querySelector(".dock-load-text");

    // Si ya no hay más OTs por cargar, ocultar por completo el botón y su divisor
    if (!hasMore) {
        btnDock.style.display = "none";
        if (divider) divider.style.display = "none";
        return;
    }

    btnDock.style.display = "inline-flex";
    if (divider) divider.style.display = "block";

    if (isLoading) {
        btnDock.disabled = true;
        btnDock.classList.add("loading");
        if (textSpan) textSpan.textContent = `Cargar más (${remaining})`;
    } else {
        btnDock.disabled = false;
        btnDock.classList.remove("loading");
        if (textSpan) textSpan.textContent = `Cargar más (${remaining})`;
    }
}

/**
 * Busca un elemento section en cachedSections por número de OT, nombre completo o coincidencia en texto
 */
export function findSectionForOt(cleanTarget) {
    if (!cleanTarget || cachedSections.length === 0) return null;

    let found = cachedSections.find(sec => String(sec.otId).trim().toLowerCase() === cleanTarget);
    if (found) return found.element;

    found = cachedSections.find(sec => String(sec.otId).trim().toLowerCase().includes(cleanTarget));
    if (found) return found.element;

    found = cachedSections.find(sec => sec.label && sec.label.toLowerCase().includes(cleanTarget));
    if (found) return found.element;

    for (const secInfo of cachedSections) {
        const headerTxt = secInfo.element.querySelector('.workOrder-text')?.textContent?.toLowerCase() || '';
        if (headerTxt.includes(cleanTarget)) {
            return secInfo.element;
        }
    }

    return null;
}

/**
 * Inicializa todos los eventos y controles del dock inferior
 */
export function initNavDock(onLoadMore) {
    const bUrl = window.baseUrl || (window.location.origin + '/');
    const bSlash = bUrl.endsWith('/') ? '' : '/';
    document.body.style.setProperty('background', `url('${bUrl}${bSlash}images/fondos/fondoLogin.jpg') no-repeat center center fixed`, 'important');
    document.body.style.setProperty('background-size', 'cover', 'important');
    document.body.style.setProperty('background-color', 'transparent', 'important');

    const dockWrapper = document.getElementById("bottom-nav-dock-wrapper");
    const btnToggleDock = document.getElementById("btn-toggle-nav-dock");
    const btnCollapseDock = document.getElementById("btn-collapse-nav-dock");

    const btnTop = document.getElementById("btn-scroll-top");
    const btnBottom = document.getElementById("btn-scroll-bottom");
    const btnPrev = document.getElementById("btn-scroll-prev");
    const btnNext = document.getElementById("btn-scroll-next");
    const navOtInput = document.getElementById("nav-ot-input");
    const navOtGoBtn = document.getElementById("nav-ot-go-btn");
    const btnDockLoadMore = document.getElementById("btn-dock-load-more");

    const tabHandleIcon = document.getElementById("tab-handle-icon");
    function setNavDockCollapsed(collapsed) {
        if (!dockWrapper) return;
        if (collapsed) {
            dockWrapper.classList.add("is-collapsed");
            if (tabHandleIcon) tabHandleIcon.textContent = "▲";
            localStorage.setItem("pip_nav_dock_collapsed", "true");
        } else {
            dockWrapper.classList.remove("is-collapsed");
            if (tabHandleIcon) tabHandleIcon.textContent = "▼";
            localStorage.setItem("pip_nav_dock_collapsed", "false");
        }
    }

    const isCollapsedSaved = localStorage.getItem("pip_nav_dock_collapsed") === "true";
    setNavDockCollapsed(isCollapsedSaved);

    if (btnToggleDock) {
        btnToggleDock.addEventListener("click", (e) => {
            e.preventDefault();
            e.stopPropagation();
            setNavDockCollapsed(!dockWrapper.classList.contains("is-collapsed"));
        });
    }

    if (btnCollapseDock) {
        btnCollapseDock.addEventListener("click", (e) => {
            e.preventDefault();
            e.stopPropagation();
            setNavDockCollapsed(true);
        });
    }

    let isUpdatingButtons = false;
    function updateScrollButtons() {
        if (isUpdatingButtons) return;
        isUpdatingButtons = true;
        requestAnimationFrame(() => {
            isUpdatingButtons = false;
            const scrollTop = window.scrollY || document.documentElement.scrollTop;
            const scrollHeight = document.documentElement.scrollHeight;
            const clientHeight = document.documentElement.clientHeight;

            const curIdx = getCurrentlyVisibleSectionIndex();
            const atTop = curIdx === 0 || scrollTop <= 15;
            const hasMorePages = window.paginationData ? window.paginationData.hasMore : false;
            const atBottom = curIdx >= cachedSections.length - 1 || (scrollTop + clientHeight >= scrollHeight - 20);

            if (btnTop) {
                btnTop.disabled = atTop;
                btnTop.style.opacity = atTop ? "0.3" : "1";
            }
            if (btnPrev) {
                btnPrev.disabled = atTop;
                btnPrev.style.opacity = atTop ? "0.3" : "1";
            }
            if (btnBottom) {
                // Si aún hay más OTs por cargar, mantener habilitado para continuar
                btnBottom.disabled = atBottom && !hasMorePages;
                btnBottom.style.opacity = (atBottom && !hasMorePages) ? "0.3" : "1";
            }
            if (btnNext) {
                // Si aún hay más OTs por cargar, mantener habilitado para avanzar fluidamente
                btnNext.disabled = atBottom && !hasMorePages;
                btnNext.style.opacity = (atBottom && !hasMorePages) ? "0.3" : "1";
            }
        });
    }

    if (btnTop) {
        btnTop.addEventListener("click", () => {
            if (cachedSections.length > 0) {
                cachedSections[0].element.scrollIntoView({ behavior: "smooth", block: "start" });
            } else {
                window.scrollTo({ top: 0, behavior: "smooth" });
            }
        });
    }

    if (btnBottom) {
        btnBottom.addEventListener("click", async () => {
            const hasMorePages = window.paginationData ? window.paginationData.hasMore : false;
            const curIdx = getCurrentlyVisibleSectionIndex();
            if (cachedSections.length > 0) {
                if (curIdx >= cachedSections.length - 1 && hasMorePages && typeof onLoadMore === 'function') {
                    await onLoadMore();
                    return;
                }
                cachedSections[cachedSections.length - 1].element.scrollIntoView({ behavior: "smooth", block: "start" });
            } else {
                window.scrollTo({ top: document.body.scrollHeight, behavior: "smooth" });
            }
        });
    }

    if (btnPrev) {
        btnPrev.addEventListener("click", () => {
            if (cachedSections.length === 0) return;
            const curIdx = getCurrentlyVisibleSectionIndex();
            const prevIdx = Math.max(0, curIdx - 1);
            cachedSections[prevIdx].element.scrollIntoView({ behavior: "smooth", block: "start" });
        });
    }

    if (btnNext) {
        btnNext.addEventListener("click", async () => {
            if (cachedSections.length === 0) return;
            const curIdx = getCurrentlyVisibleSectionIndex();
            const hasMorePages = window.paginationData ? window.paginationData.hasMore : false;
            if (curIdx >= cachedSections.length - 1 && hasMorePages && typeof onLoadMore === 'function') {
                await onLoadMore();
                return;
            }
            const nextIdx = Math.min(cachedSections.length - 1, curIdx + 1);
            cachedSections[nextIdx].element.scrollIntoView({ behavior: "smooth", block: "start" });
        });
    }

    window.jumpToOT = async function (targetOt) {
        if (!targetOt) {
            if (typeof window.gisToast === 'function') {
                window.gisToast("Ingresa el número de OT que deseas buscar", "info");
            }
            return;
        }

        const rawInput = String(targetOt).trim();
        const numMatch = rawInput.match(/\b\d+\b/);
        const cleanTarget = numMatch ? numMatch[0].toLowerCase() : rawInput.replace(/^ot[:\s\-#]*/i, '').trim().toLowerCase();

        if (!cleanTarget) {
            if (typeof window.gisToast === 'function') {
                window.gisToast("Ingresa un número de OT válido", "info");
            }
            return;
        }

        function scrollToSectionAndHighlight(secElement, otLabel) {
            secElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
            setTimeout(() => {
                secElement.classList.remove('ot-highlight-flash');
                void secElement.offsetWidth;
                secElement.classList.add('ot-highlight-flash');
            }, 300);

            if (typeof window.gisToast === 'function') {
                window.gisToast(`Navegando a la Orden de Trabajo ${otLabel}`, 'success');
            }
        }

        let foundSec = findSectionForOt(cleanTarget);
        if (foundSec) {
            scrollToSectionAndHighlight(foundSec, rawInput);
            return;
        }

        const pagData = window.paginationData;
        if (pagData && pagData.hasMore && typeof onLoadMore === 'function') {
            if (typeof window.gisToast === 'function') {
                window.gisToast(`Buscando OT ${rawInput} en las órdenes siguientes...`, 'info');
            }

            let foundAfterFetch = null;
            while (window.paginationData && window.paginationData.hasMore) {
                const data = await onLoadMore();
                if (!data) break;

                foundAfterFetch = findSectionForOt(cleanTarget);
                if (foundAfterFetch) {
                    scrollToSectionAndHighlight(foundAfterFetch, rawInput);
                    return;
                }
            }

            if (!foundAfterFetch && typeof window.gisToast === 'function') {
                window.gisToast(`No se encontró la OT ${rawInput} en las órdenes activas`, 'error');
            }
        } else if (typeof window.gisToast === 'function') {
            window.gisToast(`No se encontró la OT ${rawInput} en las órdenes activas`, 'error');
        }
    };

    const navOtClearBtn = document.getElementById("nav-ot-clear-btn");
    const navOtDropdownToggle = document.getElementById("nav-ot-dropdown-toggle");
    const searchBox = document.getElementById("nav-ot-search-box");

    if (navOtInput) {
        navOtInput.addEventListener("focus", () => {
            navOtInput.select();
            openOtDropdown(true);
        });
        navOtInput.addEventListener("click", (e) => {
            e.stopPropagation();
            navOtInput.select();
            openOtDropdown(true);
        });
        navOtInput.addEventListener("input", () => {
            const val = navOtInput.value;
            if (navOtClearBtn) {
                navOtClearBtn.style.display = val.length > 0 ? "flex" : "none";
            }
            renderOtDropdownItems(val);
            openOtDropdown(false);
        });
        navOtInput.addEventListener("keydown", (e) => {
            if (e.key === "Enter") {
                e.preventDefault();
                closeOtDropdown();
                window.jumpToOT(navOtInput.value);
            } else if (e.key === "Escape") {
                closeOtDropdown();
            }
        });
    }

    if (navOtClearBtn) {
        navOtClearBtn.addEventListener("click", (e) => {
            e.stopPropagation();
            if (navOtInput) {
                navOtInput.value = "";
                navOtClearBtn.style.display = "none";
                navOtInput.focus();
                renderOtDropdownItems("");
                openOtDropdown(true);
            }
        });
    }

    if (navOtDropdownToggle) {
        navOtDropdownToggle.addEventListener("click", (e) => {
            e.stopPropagation();
            toggleOtDropdown();
        });
    }

    if (navOtGoBtn && navOtInput) {
        navOtGoBtn.addEventListener("click", (e) => {
            e.stopPropagation();
            closeOtDropdown();
            window.jumpToOT(navOtInput.value);
        });
    }

    document.addEventListener("click", (e) => {
        if (searchBox && !searchBox.contains(e.target)) {
            closeOtDropdown();
        }
    });

    if (btnDockLoadMore && typeof onLoadMore === 'function') {
        btnDockLoadMore.addEventListener("click", async () => {
            await onLoadMore();
        });
    }

    const btnLoadMorePage = document.getElementById("btn-load-more-ot");
    if (btnLoadMorePage && typeof onLoadMore === 'function') {
        btnLoadMorePage.addEventListener("click", async () => {
            await onLoadMore();
        });
    }

    updateScrollButtons();
    updateNavDockCounter();

    window.addEventListener('scroll', () => {
        updateScrollButtons();
        updateNavDockCounter();
    }, { passive: true });

    window.addEventListener('resize', () => {
        updateCachedSections();
        updateScrollButtons();
        updateNavDockCounter();
    }, { passive: true });

    setTimeout(updateCachedSections, 250);
}
