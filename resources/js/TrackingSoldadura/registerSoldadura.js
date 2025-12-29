function actualizarBoton() {
    const btn = document.getElementById("btnGuardar");
    const fecha = document.getElementById("fecha_ingreso").value;
    const nombre = document.getElementById("nombre").value;
    const lote = document.getElementById("lote").value;
    const kilos = document.getElementById("kilos").value;

    btn.disabled = !(fecha && nombre && lote && kilos);
}

document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll("input").forEach(input => {
        input.addEventListener("input", actualizarBoton);
    });
    actualizarBoton();
});
