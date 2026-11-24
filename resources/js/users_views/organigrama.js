document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("org-search-input");
    const plantaBtns = document.querySelectorAll(".planta-filter-btn");
    const turnoSelect = document.getElementById("org-turno-filter");
    const statusSelect = document.getElementById("org-status-filter");
    const userCards = document.querySelectorAll(".org-node");

    const canvas = document.getElementById("org-canvas");
    const treeRoot = document.getElementById("org-tree-root");
    const emptyFilterMsg = document.getElementById("org-empty-filter-msg");
    const emptyTitleEl = document.getElementById("empty-filter-title");
    const emptyDetailsEl = document.getElementById("empty-filter-details");
    const btnResetEmpty = document.getElementById("btn-reset-empty-filters");

    const btnZoomIn = document.getElementById("btn-zoom-in");
    const btnZoomOut = document.getElementById("btn-zoom-out");
    const btnZoomReset = document.getElementById("btn-zoom-reset");
    const btnZoomFit = document.getElementById("btn-zoom-fit");
    const btnExportPdf = document.getElementById("btn-export-pdf");

    const masterWrapper = document.querySelector(".org-master-wrapper");
    const btnExpandAll = document.getElementById("btn-org-expand-all");
    const btnSummary = document.getElementById("btn-org-summary");
    const btnDensity = document.getElementById("btn-org-density");

    // Referencias cacheadas del DOM (evitan querySelectorAll repetitivos)
    const treeBranchEl = treeRoot ? treeRoot.querySelector(".tree-branch") : null;
    const treeUlEl = treeBranchEl ? treeBranchEl.querySelector(":scope > ul") : null;
    const areaBranches = Array.from(document.querySelectorAll("li.org-area-branch.has-children"));
    const roleGroups = Array.from(document.querySelectorAll("li.org-role-group"));
    const branchTitles = Array.from(document.querySelectorAll(".area-branch-title, .sub-role-branch-title"));
    const areaTitleEls = Array.from(document.querySelectorAll(".area-branch-title"));
    const subRoleCols = Array.from(document.querySelectorAll(".puesto-nodes-column"));
    const kpiTotalEl = document.getElementById("kpi-total-val");
    const kpiActiveEl = document.getElementById("kpi-active-val");
    const kpiInactiveEl = document.getElementById("kpi-inactive-val");

    let currentPlanta = "todas";
    let currentTurno = "todos";
    let currentStatus = "todos";
    let searchQuery = "";
    let currentZoom = 1;
    let hadActiveFilter = false;
    let searchDebounceTimer = null;
    let fitRafId = null;
    let resizeDebounceTimer = null;

    // ── Despliegue / Contracción de Áreas y Puestos ──────────
    function syncAriaStates() {
        areaBranches.forEach((li) => {
            const trigger = li.querySelector(":scope > .area-branch-title");
            if (trigger) {
                trigger.setAttribute("aria-expanded", li.classList.contains("is-area-collapsed") ? "false" : "true");
            }
        });
        roleGroups.forEach((li) => {
            const trigger = li.querySelector(":scope > .sub-role-branch-title");
            if (trigger) {
                trigger.setAttribute("aria-expanded", li.classList.contains("is-group-collapsed") ? "false" : "true");
            }
        });

        const allExpanded = roleGroups.every((g) => !g.classList.contains("is-group-collapsed"))
            && areaBranches.every((a) => !a.classList.contains("is-area-collapsed"));
        const isSummary = roleGroups.length > 0
            && roleGroups.every((g) => g.classList.contains("is-group-collapsed"))
            && areaBranches.every((a) => !a.classList.contains("is-area-collapsed"));

        if (btnExpandAll) btnExpandAll.classList.toggle("active", allExpanded);
        if (btnSummary) btnSummary.classList.toggle("active", isSummary);
    }

    function setCollapsed(collapseGroups, collapseAreas) {
        roleGroups.forEach((li) => {
            li.classList.toggle("is-group-collapsed", collapseGroups);
        });
        areaBranches.forEach((li) => {
            li.classList.toggle("is-area-collapsed", collapseAreas);
        });
        syncAriaStates();
    }

    function updateBranchCounts() {
        branchTitles.forEach((titleEl) => {
            const li = titleEl.closest("li");
            if (!li) return;
            const scope = li.classList.contains("org-role-group")
                ? (li.querySelector(":scope > .puesto-nodes-column") || li)
                : li;
            const visibleNodes = scope.querySelectorAll(".org-node:not(.node-filtered-out)");
            const inactiveNodes = scope.querySelectorAll(".org-node.node-inactivo:not(.node-filtered-out)");

            const totalBadge = titleEl.querySelector(".org-count-total");
            const warnBadge = titleEl.querySelector(".org-count-warn");
            if (totalBadge) {
                totalBadge.textContent = visibleNodes.length;
            }
            if (warnBadge) {
                warnBadge.hidden = inactiveNodes.length === 0;
                warnBadge.textContent = inactiveNodes.length > 0 ? `⚠ ${inactiveNodes.length}` : "";
            }
        });
    }

    // Delegación de eventos para clic y teclado sobre títulos de área y puesto
    if (treeRoot) {
        treeRoot.addEventListener("click", (e) => {
            const trigger = e.target.closest(".org-collapsible-trigger");
            if (!trigger) return;
            const li = trigger.closest("li");
            if (!li) return;

            if (trigger.classList.contains("sub-role-branch-title")) {
                li.classList.toggle("is-group-collapsed");
            } else if (trigger.classList.contains("area-branch-title")) {
                li.classList.toggle("is-area-collapsed");
            }

            syncAriaStates();
            fitToScreen();
        });

        treeRoot.addEventListener("keydown", (e) => {
            if ((e.key === "Enter" || e.key === " ") && e.target.classList.contains("org-collapsible-trigger")) {
                e.preventDefault();
                e.target.click();
            }
        });
    }

    if (btnExpandAll) {
        btnExpandAll.addEventListener("click", () => {
            setCollapsed(false, false);
            fitToScreen();
        });
    }

    if (btnSummary) {
        btnSummary.addEventListener("click", () => {
            setCollapsed(true, false);
            fitToScreen();
        });
    }

    if (btnDensity && masterWrapper) {
        btnDensity.addEventListener("click", () => {
            const isCompact = masterWrapper.classList.toggle("org-compact-mode");
            btnDensity.classList.toggle("active", isCompact);
            btnDensity.textContent = isCompact ? "Vista detallada" : "Vista compacta";
            fitToScreen();
        });
    }

    // ── Filtros ──────────────────────────────────────────────
    function filterUsers() {
        let visibleCount = 0;
        let activeVisible = 0;
        let inactiveVisible = 0;

        const trimmedQuery = searchQuery.trim().toLowerCase();
        const hasFilter = currentPlanta !== "todas"
            || currentTurno !== "todos"
            || currentStatus !== "todos"
            || trimmedQuery !== "";

        userCards.forEach((card) => {
            const cardPlanta = (card.dataset.planta || "").trim().toUpperCase();
            const cardTurno = (card.dataset.turno || "").trim().toUpperCase();
            const cardStatus = (card.dataset.status || "").trim().toLowerCase();
            const cardText = (card.dataset.search || "").toLowerCase();

            // Planta Filter
            let matchPlanta = true;
            if (currentPlanta !== "todas") {
                const targetPlanta = currentPlanta.trim().toUpperCase();
                matchPlanta = cardPlanta === targetPlanta;
            }

            // Turno Filter
            let matchTurno = true;
            if (currentTurno !== "todos") {
                matchTurno = cardTurno === currentTurno.trim().toUpperCase();
            }

            // Status Filter
            let matchStatus = true;
            if (currentStatus === "activo") {
                matchStatus = cardStatus === "activo";
            } else if (currentStatus === "inactivo") {
                matchStatus = cardStatus === "inactivo";
            }

            // Search Query
            let matchSearch = true;
            if (trimmedQuery !== "") {
                matchSearch = cardText.includes(trimmedQuery);
            }

            const isMatch = matchPlanta && matchTurno && matchStatus && matchSearch;

            if (isMatch) {
                card.style.display = "flex";
                card.style.opacity = "1";
                card.style.filter = "none";
                card.classList.remove("node-filtered-out");
                card.classList.toggle("node-found", trimmedQuery !== "");
                visibleCount++;
                if (cardStatus === "activo") activeVisible++;
                else inactiveVisible++;
            } else {
                card.style.display = "none";
                card.classList.add("node-filtered-out");
                card.classList.remove("node-found");
            }
        });

        // Ocultar sub-ramas de puestos si no tienen miembros visibles
        subRoleCols.forEach((col) => {
            const parentLi = col.closest("li");
            if (!parentLi) return;
            const visibleChildren = col.querySelectorAll(".org-node:not(.node-filtered-out)");
            parentLi.style.display = visibleChildren.length > 0 ? "" : "none";
        });

        // Ocultar ramas de área completas si ni supervisor ni equipo están visibles
        areaTitleEls.forEach((titleEl) => {
            const areaLi = titleEl.closest("li");
            if (!areaLi) return;
            const visibleNodesInArea = areaLi.querySelectorAll(".org-node:not(.node-filtered-out)");
            areaLi.style.display = visibleNodesInArea.length > 0 ? "" : "none";
        });

        // Auto-expandir cuando hay búsqueda/filtros activos y restaurar resumen al limpiar
        if (hasFilter) {
            setCollapsed(false, false);
        } else if (hadActiveFilter) {
            setCollapsed(true, false);
        }
        hadActiveFilter = hasFilter;

        // Mostrar u ocultar mensaje de estado vacío (ej: CDMX sin gente)
        if (visibleCount === 0) {
            if (treeRoot) treeRoot.style.display = "none";
            if (emptyFilterMsg) {
                emptyFilterMsg.style.display = "block";
                
                if (currentPlanta !== "todas") {
                    if (emptyTitleEl) emptyTitleEl.textContent = `No hay personal registrado en Planta ${currentPlanta}`;
                    if (emptyDetailsEl) emptyDetailsEl.textContent = `Actualmente no se encuentran colaboradores asignados a la ubicación "${currentPlanta}".`;
                } else if (trimmedQuery !== "") {
                    if (emptyTitleEl) emptyTitleEl.textContent = `Sin resultados para "${searchQuery}"`;
                    if (emptyDetailsEl) emptyDetailsEl.textContent = "Intenta con otro término de búsqueda o limpia los filtros.";
                } else {
                    if (emptyTitleEl) emptyTitleEl.textContent = "No hay personal que coincida con los filtros";
                    if (emptyDetailsEl) emptyDetailsEl.textContent = "No se encontraron colaboradores para la combinación de filtros aplicada.";
                }
            }
        } else {
            if (treeRoot) treeRoot.style.display = "";
            if (emptyFilterMsg) emptyFilterMsg.style.display = "none";
        }

        // Actualizar contadores KPI y contadores en píldoras de área/puesto
        if (kpiTotalEl) kpiTotalEl.textContent = visibleCount;
        if (kpiActiveEl) kpiActiveEl.textContent = activeVisible;
        if (kpiInactiveEl) kpiInactiveEl.textContent = inactiveVisible;

        updateBranchCounts();
        if (visibleCount > 0) {
            fitToScreen();
            if (trimmedQuery !== "") {
                const firstFound = document.querySelector(".org-node.node-found");
                if (firstFound) {
                    setTimeout(() => {
                        firstFound.scrollIntoView({ behavior: "smooth", block: "center", inline: "center" });
                    }, 90);
                }
            }
        }
    }

    // Planta Buttons
    plantaBtns.forEach((btn) => {
        btn.addEventListener("click", function () {
            plantaBtns.forEach((b) => b.classList.remove("active"));
            this.classList.add("active");
            currentPlanta = this.dataset.planta || "todas";
            filterUsers();
        });
    });

    // Turno Select
    if (turnoSelect) {
        turnoSelect.addEventListener("change", function () {
            currentTurno = this.value;
            filterUsers();
        });
    }

    // Status Select
    if (statusSelect) {
        statusSelect.addEventListener("change", function () {
            currentStatus = this.value;
            filterUsers();
        });
    }

    // Search Input (con debounce suave para evitar recálculos excesivos)
    if (searchInput) {
        searchInput.addEventListener("input", function () {
            searchQuery = this.value;
            if (searchDebounceTimer) clearTimeout(searchDebounceTimer);
            searchDebounceTimer = setTimeout(filterUsers, 220);
        });
    }

    // Botón para restablecer filtros desde el mensaje vacío
    if (btnResetEmpty) {
        btnResetEmpty.addEventListener("click", function () {
            // Restablecer planta a 'todas'
            plantaBtns.forEach((b) => {
                if (b.dataset.planta === "todas") b.classList.add("active");
                else b.classList.remove("active");
            });
            currentPlanta = "todas";

            // Restablecer turno y estatus
            if (turnoSelect) {
                turnoSelect.value = "todos";
                currentTurno = "todos";
            }
            if (statusSelect) {
                statusSelect.value = "todos";
                currentStatus = "todos";
            }

            // Restablecer búsqueda
            if (searchInput) {
                searchInput.value = "";
                searchQuery = "";
            }

            filterUsers();
        });
    }

    // ── Zoom, Pan y Ajuste Inteligente en Canvas ──────────────
    function setZoom(scale, keepCenter = true) {
        currentZoom = Math.min(Math.max(0.35, scale), 2.0);

        // Guardar centro relativo actual si keepCenter es true
        let relativeCenterX = 0.5;
        let relativeCenterY = 0.5;
        if (canvas && keepCenter) {
            relativeCenterX = (canvas.scrollLeft + canvas.clientWidth / 2) / (canvas.scrollWidth || 1);
            relativeCenterY = (canvas.scrollTop + canvas.clientHeight / 2) / (canvas.scrollHeight || 1);
        }

        if (treeRoot) {
            // CSS zoom reflows scrollbars naturally without negative-space clipping
            treeRoot.style.zoom = currentZoom;
            // Fallback para navegadores que requieran transform
            if (treeRoot.style.zoom === undefined || treeRoot.style.zoom === "") {
                treeRoot.style.transform = `scale(${currentZoom})`;
                treeRoot.style.transformOrigin = "top center";
            }
        }

        if (btnZoomReset) {
            btnZoomReset.textContent = `${Math.round(currentZoom * 100)}%`;
        }

        // Mantener posición visual centrada tras el cambio de zoom
        if (canvas && keepCenter) {
            requestAnimationFrame(() => {
                canvas.scrollLeft = (relativeCenterX * canvas.scrollWidth) - (canvas.clientWidth / 2);
                canvas.scrollTop = (relativeCenterY * canvas.scrollHeight) - (canvas.clientHeight / 2);
            });
        }
    }

    function fitToScreen() {
        if (!canvas || !treeRoot) return;
        if (fitRafId) cancelAnimationFrame(fitRafId);

        fitRafId = requestAnimationFrame(() => {
            fitRafId = null;
            const availableWidth = canvas.clientWidth - 80;
            const measuredEl = treeUlEl || treeBranchEl || treeRoot;
            // offsetWidth sobre el <ul> raíz devuelve el ancho natural sin escalar y sin inflarse por min-width:100%
            const naturalWidth = measuredEl.offsetWidth || measuredEl.scrollWidth || 1400;
            const optimalZoom = Math.min(Math.max(0.45, availableWidth / naturalWidth), 1.0);

            setZoom(optimalZoom, false);

            requestAnimationFrame(() => {
                canvas.scrollLeft = Math.max(0, (canvas.scrollWidth - canvas.clientWidth) / 2);
                canvas.scrollTop = 0;
            });
        });
    }

    if (btnZoomIn) {
        btnZoomIn.addEventListener("click", () => setZoom(currentZoom + 0.1));
    }
    if (btnZoomOut) {
        btnZoomOut.addEventListener("click", () => setZoom(currentZoom - 0.1));
    }
    if (btnZoomReset) {
        btnZoomReset.addEventListener("click", () => {
            setZoom(1);
            setTimeout(() => {
                if (canvas) canvas.scrollLeft = (canvas.scrollWidth - canvas.clientWidth) / 2;
            }, 50);
        });
    }
    if (btnZoomFit) {
        btnZoomFit.addEventListener("click", fitToScreen);
    }
    async function exportToPdf() {
        if (!treeRoot || !btnExportPdf) return;

        const originalBtnText = btnExportPdf.innerHTML;
        btnExportPdf.innerHTML = "⏳ Generando PDF...";
        btnExportPdf.disabled = true;

        // Guardar estado actual de vista
        const previousZoom = currentZoom;
        const previousScrollLeft = canvas ? canvas.scrollLeft : 0;
        const previousScrollTop = canvas ? canvas.scrollTop : 0;

        try {
            // Contenedor temporal aislado para renderizado fiel en alta resolución
            const exportContainer = document.createElement("div");
            exportContainer.style.position = "absolute";
            exportContainer.style.left = "-99999px";
            exportContainer.style.top = "0";
            exportContainer.style.background = "#ffffff";
            exportContainer.style.padding = "30px 40px 40px";
            exportContainer.style.width = "max-content";
            exportContainer.style.boxSizing = "border-box";
            exportContainer.style.fontFamily = "'Poppins', Arial, sans-serif";

            // Encabezado corporativo membretado
            const headerOriginal = document.querySelector(".org-top-header");
            if (headerOriginal) {
                const headerClone = headerOriginal.cloneNode(true);
                const controlsInClone = headerClone.querySelector(".org-header-controls");
                if (controlsInClone) controlsInClone.remove();
                headerClone.style.borderBottom = "4px solid #033966";
                headerClone.style.paddingBottom = "15px";
                headerClone.style.marginBottom = "25px";
                exportContainer.appendChild(headerClone);
            }

            // Clon exacto del árbol del organigrama
            const treeClone = treeRoot.cloneNode(true);
            treeClone.style.zoom = "1";
            treeClone.style.transform = "none";
            treeClone.style.display = "block";
            exportContainer.appendChild(treeClone);

            document.body.appendChild(exportContainer);

            // Carga dinámica de librerías PDF bajo demanda
            const [{ default: html2canvas }, { jsPDF }] = await Promise.all([
                import("html2canvas"),
                import("jspdf"),
            ]);

            // Esperar que el navegador renderice nodos e imágenes
            await new Promise((resolve) => setTimeout(resolve, 350));

            const renderedCanvas = await html2canvas(exportContainer, {
                scale: 2, // Calidad HD 2x
                useCORS: true,
                allowTaint: true,
                backgroundColor: "#ffffff",
                logging: false,
            });

            document.body.removeChild(exportContainer);

            const imgData = renderedCanvas.toDataURL("image/jpeg", 0.95);
            const imgWidth = renderedCanvas.width;
            const imgHeight = renderedCanvas.height;

            // Generar PDF a medida de una sola página en horizontal
            const pdf = new jsPDF({
                orientation: imgWidth > imgHeight ? "landscape" : "portrait",
                unit: "px",
                format: [imgWidth / 2, imgHeight / 2],
            });

            pdf.addImage(imgData, "JPEG", 0, 0, imgWidth / 2, imgHeight / 2);
            pdf.save("Organigrama_Grupo_Industrial_Saavedra.pdf");
        } catch (error) {
            console.error("Error al exportar PDF:", error);
            if (window.toastpremium) {
                window.toastpremium("No se pudo generar el archivo directo. Abriendo diálogo de impresión...", "warning");
            }
            window.print();
        } finally {
            // Restaurar zoom y posición del usuario
            setZoom(previousZoom, false);
            if (canvas) {
                canvas.scrollLeft = previousScrollLeft;
                canvas.scrollTop = previousScrollTop;
            }
            btnExportPdf.innerHTML = originalBtnText;
            btnExportPdf.disabled = false;
        }
    }

    if (btnExportPdf) {
        btnExportPdf.addEventListener("click", exportToPdf);
    }

    // Drag to Pan Canvas optimizado con requestAnimationFrame (60 FPS)
    if (canvas) {
        let isDown = false;
        let startX = 0;
        let startY = 0;
        let startScrollLeft = 0;
        let startScrollTop = 0;
        let pendingScrollLeft = 0;
        let pendingScrollTop = 0;
        let dragRafId = null;
        let wheelRafId = null;
        let pendingZoomDelta = 0;

        function applyPendingPan() {
            dragRafId = null;
            canvas.scrollLeft = pendingScrollLeft;
            canvas.scrollTop = pendingScrollTop;
        }

        function stopDragging() {
            if (!isDown) return;
            isDown = false;
            canvas.classList.remove("is-dragging");
            if (dragRafId) {
                cancelAnimationFrame(dragRafId);
                dragRafId = null;
            }
        }

        canvas.addEventListener("mousedown", (e) => {
            if (
                e.button !== 0 ||
                e.target.closest(".org-node") ||
                e.target.closest(".org-collapsible-trigger") ||
                e.target.closest(".org-empty-filter-box") ||
                e.target.closest("button") ||
                e.target.closest("input") ||
                e.target.closest("select")
            ) return;
            isDown = true;
            canvas.classList.add("is-dragging");
            startX = e.clientX;
            startY = e.clientY;
            startScrollLeft = canvas.scrollLeft;
            startScrollTop = canvas.scrollTop;
        });

        window.addEventListener("mouseup", stopDragging);
        window.addEventListener("blur", stopDragging);

        canvas.addEventListener("mousemove", (e) => {
            if (!isDown) return;
            e.preventDefault();
            pendingScrollLeft = startScrollLeft - (e.clientX - startX) * 1.35;
            pendingScrollTop = startScrollTop - (e.clientY - startY) * 1.35;
            if (!dragRafId) {
                dragRafId = requestAnimationFrame(applyPendingPan);
            }
        });

        // Zoom con rueda del ratón (Ctrl + Scroll) agrupado por frame
        canvas.addEventListener("wheel", (e) => {
            if (!e.ctrlKey) return;
            e.preventDefault();
            pendingZoomDelta += e.deltaY < 0 ? 0.08 : -0.08;
            if (!wheelRafId) {
                wheelRafId = requestAnimationFrame(() => {
                    wheelRafId = null;
                    const delta = pendingZoomDelta;
                    pendingZoomDelta = 0;
                    setZoom(currentZoom + delta);
                });
            }
        }, { passive: false });
    }

    // Estado inicial en Resumen (puestos contraídos, áreas visibles con contadores)
    setCollapsed(true, false);
    updateBranchCounts();
    setTimeout(fitToScreen, 100);
    window.addEventListener("resize", () => {
        if (resizeDebounceTimer) clearTimeout(resizeDebounceTimer);
        resizeDebounceTimer = setTimeout(fitToScreen, 150);
    });
});

