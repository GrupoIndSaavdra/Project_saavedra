// Habilitar/deshabilitar botón Guardar
function actualizarBoton() {
    const btn = document.querySelector("#btnGuardar");
    const fecha = document.getElementById("fecha_ingreso");
    const nombre = document.getElementById("nombre");
    const lote = document.getElementById("lote");
    const kilos = document.getElementById("kilos");

    btn.disabled = !(fecha?.value && nombre?.value && lote?.value && kilos?.value);
}

// Inicialización al cargar la página
document.addEventListener("DOMContentLoaded", function () {
    const fechaInput = document.getElementById("fecha_ingreso");
    const nombreInput = document.getElementById("nombre");
    const loteInput = document.getElementById("lote");
    const kilosInput = document.getElementById("kilos");

    [fechaInput, nombreInput, loteInput, kilosInput].forEach(input => {
        if (input) input.addEventListener("input", actualizarBoton);
    });

    // Botón inicialmente deshabilitado
    actualizarBoton();
});
