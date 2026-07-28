function actualizarBoton() {
    const btn = document.getElementById("btnGuardar");
    const fechaEl = document.getElementById("fecha_ingreso");
    const nombreEl = document.getElementById("nombre");
    const loteEl = document.getElementById("lote");
    const kilosEl = document.getElementById("kilos");

    const fecha = fechaEl?.value || '';
    const nombre = nombreEl?.value || '';
    const lote = loteEl?.value || '';
    const kilos = kilosEl?.value || '';

    if (btn) {
        btn.disabled = !(fecha && nombre && lote && kilos);
    }
}

document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll("input").forEach(input => {
        input.addEventListener("input", actualizarBoton);
    });
    actualizarBoton();
});
