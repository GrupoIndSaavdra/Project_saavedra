// ── TOGGLE FILAS DE ARCHIVOS ──────────────────────────────────────────────────
function initToggleFiles() {
    document.addEventListener("click", (e) => {
        const btn = e.target.closest(".btn-toggle-files");
        if (!btn) return;
        const targetId = btn.dataset.target;
        const filesRow = document.getElementById(targetId);
        if (!filesRow) return;
        const isOpen = filesRow.classList.contains("open");
        // Cerrar todos los demás antes de abrir el nuevo (Comportamiento de Acordeón)
        if (!isOpen) {
            document.querySelectorAll(".alm-files-row.open").forEach((row) => {
                row.classList.remove("open");
            });
            document
                .querySelectorAll(".btn-toggle-files.active")
                .forEach((b) => {
                    b.classList.remove("active");
                    b.setAttribute("aria-expanded", "false");
                    b.innerHTML = "Ver PDFs";
                });
        }
        if (isOpen) {
            filesRow.classList.remove("open");
            btn.classList.remove("active");
            btn.setAttribute("aria-expanded", "false");
            btn.innerHTML = "Ver PDFs";
        } else {
            filesRow.classList.add("open");
            btn.classList.add("active");
            btn.setAttribute("aria-expanded", "true");
            btn.innerHTML = "Ocultar";
        }
    });
}


// Expose to window for global access
window.initToggleFiles = initToggleFiles;
