import html2canvas from "html2canvas";
import { jsPDF } from "jspdf";

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

    let currentPlanta = "todas";
    let currentTurno = "todos";
    let currentStatus = "todos";
    let searchQuery = "";
    let currentZoom = 1;

    // ── Filtros ──────────────────────────────────────────────
    function filterUsers() {
        let visibleCount = 0;
        let activeVisible = 0;
        let inactiveVisible = 0;

        userCards.forEach((card) => {
            const cardPlanta = (card.dataset.planta || "").trim().toUpperCase();
            const cardTurno = (card.dataset.turno || "").trim().toUpperCase();
            const cardStatus = (card.dataset.status || "").trim().toLowerCase();
            const cardText = (card.dataset.search || "").toLowerCase();

            // Planta Filter
            let matchPlanta = true;
            if (currentPlanta !== "todas") {
                const targetPlanta = currentPlanta.trim().toUpperCase();
                // Si la tarjeta no tiene planta o es diferente a la seleccionada
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
            if (searchQuery.trim() !== "") {
                matchSearch = cardText.includes(searchQuery.trim().toLowerCase());
            }

            const isMatch = matchPlanta && matchTurno && matchStatus && matchSearch;

            if (isMatch) {
                card.style.display = "flex";
                card.style.opacity = "1";
                card.style.filter = "none";
                card.classList.remove("node-filtered-out");
                visibleCount++;
                if (cardStatus === "activo") activeVisible++;
                else inactiveVisible++;
            } else {
                card.style.display = "none";
                card.classList.add("node-filtered-out");
            }
        });

        // Ocultar sub-ramas de puestos si no tienen miembros visibles
        const subRoleLis = document.querySelectorAll(".puesto-nodes-column");
        subRoleLis.forEach((col) => {
            const parentLi = col.closest("li");
            if (!parentLi) return;
            const visibleChildren = col.querySelectorAll(".org-node:not(.node-filtered-out)");
            if (visibleChildren.length > 0) {
                parentLi.style.display = "";
            } else {
                parentLi.style.display = "none";
            }
        });

        // Ocultar ramas de área completas si ni supervisor ni equipo están visibles
        const areaTitles = document.querySelectorAll(".area-branch-title");
        areaTitles.forEach((titleEl) => {
            const areaLi = titleEl.closest("li");
            if (!areaLi) return;
            const visibleNodesInArea = areaLi.querySelectorAll(".org-node:not(.node-filtered-out)");
            if (visibleNodesInArea.length > 0) {
                areaLi.style.display = "";
            } else {
                areaLi.style.display = "none";
            }
        });

        // Mostrar u ocultar mensaje de estado vacío (ej: CDMX sin gente)
        if (visibleCount === 0) {
            if (treeRoot) treeRoot.style.display = "none";
            if (emptyFilterMsg) {
                emptyFilterMsg.style.display = "block";
                
                // Personalizar mensaje según el filtro
                if (currentPlanta !== "todas") {
                    if (emptyTitleEl) emptyTitleEl.textContent = `No hay personal registrado en Planta ${currentPlanta}`;
                    if (emptyDetailsEl) emptyDetailsEl.textContent = `Actualmente no se encuentran colaboradores asignados a la ubicación "${currentPlanta}".`;
                } else if (searchQuery.trim() !== "") {
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

        // Actualizar contadores
        const totalEl = document.getElementById("kpi-total-val");
        const activeEl = document.getElementById("kpi-active-val");
        const inactiveEl = document.getElementById("kpi-inactive-val");

        if (totalEl) totalEl.textContent = visibleCount;
        if (activeEl) activeEl.textContent = activeVisible;
        if (inactiveEl) inactiveEl.textContent = inactiveVisible;
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

    // Search Input
    if (searchInput) {
        searchInput.addEventListener("input", function () {
            searchQuery = this.value;
            filterUsers();
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
        
        // Medir ancho disponible
        const availableWidth = canvas.clientWidth - 80;
        
        // Reset zoom momentáneo para cálculo exacto
        treeRoot.style.zoom = 1;
        
        requestAnimationFrame(() => {
            const contentWidth = treeRoot.scrollWidth || treeRoot.offsetWidth || 1400;
            let optimalZoom = availableWidth / contentWidth;
            
            // Límite de escala óptima entre 45% y 100%
            optimalZoom = Math.min(Math.max(0.45, optimalZoom), 1.0);
            
            setZoom(optimalZoom, false);
            
            setTimeout(() => {
                canvas.scrollLeft = (canvas.scrollWidth - canvas.clientWidth) / 2;
                canvas.scrollTop = 0;
            }, 50);
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
            alert("No se pudo generar el archivo directo. Abriendo diálogo de impresión...");
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

    // Drag to Pan Canvas
    if (canvas) {
        let isDown = false;
        let startX, startY, scrollLeft, scrollTop;

        canvas.addEventListener("mousedown", (e) => {
            if (e.target.closest(".org-node") || e.target.closest(".org-empty-filter-box") || e.target.closest("button") || e.target.closest("input") || e.target.closest("select")) return;
            isDown = true;
            canvas.style.cursor = "grabbing";
            startX = e.pageX - canvas.offsetLeft;
            startY = e.pageY - canvas.offsetTop;
            scrollLeft = canvas.scrollLeft;
            scrollTop = canvas.scrollTop;
        });

        window.addEventListener("mouseup", () => {
            if (isDown) {
                isDown = false;
                canvas.style.cursor = "grab";
            }
        });

        canvas.addEventListener("mousemove", (e) => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - canvas.offsetLeft;
            const y = e.pageY - canvas.offsetTop;
            const walkX = (x - startX) * 1.5;
            const walkY = (y - startY) * 1.5;
            canvas.scrollLeft = scrollLeft - walkX;
            canvas.scrollTop = scrollTop - walkY;
        });

        // Zoom con rueda del ratón (Ctrl + Scroll)
        canvas.addEventListener("wheel", (e) => {
            if (e.ctrlKey) {
                e.preventDefault();
                const delta = e.deltaY < 0 ? 0.08 : -0.08;
                setZoom(currentZoom + delta);
            }
        }, { passive: false });

        // Centrar y ajustar scroll al cargar
        setTimeout(() => {
            canvas.scrollLeft = (canvas.scrollWidth - canvas.clientWidth) / 2;
        }, 150);
    }
});

