const newReportDiv = document.querySelector(".div-new-report");
if (newReportDiv) {
    newReportDiv.addEventListener("click", function () {
        window.location.href = window.reportRoute;
    });
    // Cuando el mouse entra en el área, inicia la animación
    newReportDiv.addEventListener("mouseenter", function () {
        // Agrega la clase que activa la animación
        newReportDiv.classList.add("active");
        newReportDiv.innerHTML = ""; // limpiar el contenido
        // Espera a que termine la animación (~100ms), y luego muestra el contenido
        setTimeout(() => {
            newReportDiv.innerHTML =
                '<h1 style="opacity:0; transform: translateX(30px); transition: all 0.4s ease; letter-spacing: 0.1em;">Nuevo reporte</h1>';

            // Activar entrada suave del texto
            setTimeout(() => {
                const h1 = newReportDiv.querySelector("h1");
                h1.style.opacity = "1";
                h1.style.transform = "translateX(0)";
            }, 50);
        }, 100);
    });

    // Cuando el mouse sale del área, revierte la animación
    newReportDiv.addEventListener("mouseleave", function () {
        newReportDiv.classList.remove("active");
        newReportDiv.innerHTML = ">"; // limpiar el contenido
    });
}