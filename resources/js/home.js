const newReportDiv = document.querySelector(".div-new-report");
if (newReportDiv) {
    newReportDiv.addEventListener("click", function () {
        // Redirección directa sin log de telemetría
        window.location.href = window.reportRoute;
    });
    // Cuando el mouse entra en el área
    newReportDiv.addEventListener("mouseenter", function () {
        newReportDiv.classList.add("active");
        newReportDiv.innerHTML = '<h1 style="letter-spacing: 0.1em;">Nuevo reporte</h1>';
    });

    // Cuando el mouse sale del área
    newReportDiv.addEventListener("mouseleave", function () {
        newReportDiv.classList.remove("active");
        newReportDiv.innerHTML = ">"; 
    });
}