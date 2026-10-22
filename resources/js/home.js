const newReportDiv = document.querySelector(".div-new-report");
if (newReportDiv) {
    // Redirección directa al flujo de producción al hacer clic
    newReportDiv.addEventListener("click", function () {
        window.location.href = window.reportRoute;
    });

    // Control de apertura y cierre mediante clases sin manipular el DOM interno para fluidez 60 FPS
    newReportDiv.addEventListener("mouseenter", function () {
        newReportDiv.classList.add("active");
    });

    newReportDiv.addEventListener("mouseleave", function () {
        newReportDiv.classList.remove("active");
    });
}