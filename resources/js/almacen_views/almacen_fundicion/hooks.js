// ── HOOKS — Integración con el DOM existente ───────────────────────────────────
// ═══════════════════════════════════════════════════════════════════════════════

/**
 * INIT — Sincroniza el caché con el estado inicial servido por Blade.
 * Se ejecuta en DOMContentLoaded para leer las imágenes ya renderizadas.
 */
document.addEventListener("DOMContentLoaded", () => {
    if (window.ModeloStateMachine) {
        window.ModeloStateMachine.init();
    }
});

/**
 * HOOK 1 — _libActualizarBadgeEstado
 * Puente de compatibilidad con el callback del backend (accion='guardar').
 * pendiente → guardado | aprobado → aprobado | rechazado → rechazado
 */
(function _hookLibBadge() {
    window._libActualizarBadgeEstado = function (ot, nuevoEstado) {
        if (!window.ModeloStateMachine) return;
        const mapa = {
            pendiente: "guardado",
            guardado: "guardado",
            en_proceso: "guardado",
        };
        const estado = mapa[nuevoEstado] ?? nuevoEstado;
        const esTerminal = nuevoEstado === "aprobado" || nuevoEstado === "rechazado";
        if (esTerminal) {
            window.ModeloStateMachine._forzarTerminal(ot, estado);
        } else {
            window.ModeloStateMachine.transicion(ot, estado);
        }
    };
})();

/**
 * HOOK 2 — btn-toggle-files → revisando (Nivel 1)
 * Solo si el panel se está ABRIENDO y el nivel actual < 2.
 */
(function _hookToggleFiles() {
    document.addEventListener(
        "click",
        (e) => {
            if (!window.ModeloStateMachine) return;
            const btn = e.target.closest(".btn-toggle-files");
            if (!btn) return;
            const ot = btn.dataset.ot;
            if (!ot) return;
            if (window.ModeloStateMachine.getNivel(ot) >= 2) return;
            const panel = document.getElementById(btn.dataset.target);
            const estaAbierto = panel?.classList.contains("open");
            if (!estaAbierto) {
                window.ModeloStateMachine.onVerArchivos(ot);
            }
        },
        true,
    );
})();

/**
 * HOOK 3 — abrirModalLiberacion → editando (Nivel 1)
 * Solo si el nivel actual es < 2.
 */
(function _hookAbrirModal() {
    const _orig = window.abrirModalLiberacion;
    window.abrirModalLiberacion = function (ot, tipo) {
        if (window.ModeloStateMachine && window.ModeloStateMachine.getNivel(ot) < 2) {
            window.ModeloStateMachine.onAbrirDecision(ot);
        }
        if (typeof _orig === "function") {
            return _orig.call(this, ot, tipo);
        }
    };
})();

/**
 * HOOK 4 — Botones de #lib-actions (MutationObserver)
 *   lib-btn-guardar  → guardado   (Nivel 2, click inmediato)
 *   lib-btn-accion   → espera     (Nivel 2, correo enviado de forma optimista)
 *
 * El estado definitivo aprobado/rechazado llega por el evento 'modeloLiberado'.
 */
(function _hookLibActions() {
    const obs = new MutationObserver(() => {
        const btnGuardar = document.getElementById("lib-btn-guardar");
        if (btnGuardar && !btnGuardar.dataset.fsmHooked) {
            btnGuardar.dataset.fsmHooked = "1";
            btnGuardar.addEventListener(
                "click",
                () => {
                    const ot = document.getElementById("lib-ot")?.value;
                    if (ot && window.ModeloStateMachine) {
                        window.ModeloStateMachine.onGuardar(ot);
                    }
                },
                true,
            );
        }
        const btnAccion = document.getElementById("lib-btn-accion");
        if (btnAccion && !btnAccion.dataset.fsmHooked) {
            btnAccion.dataset.fsmHooked = "1";
        }
    });
    document.addEventListener("DOMContentLoaded", () => {
        const actionsEl = document.getElementById("lib-actions");
        if (actionsEl) obs.observe(actionsEl, { childList: true });
    });
})();

/**
 * HOOK 5 — URL.createObjectURL (detección de descarga de PDF)
 * Cuando la liberación genera un PDF blob, la prioridad avanza a "descargado".
 * Solo se activa si el modal de liberación (#lib-ot) está activo.
 */
(function _hookPdfDescarga() {
    const _origCreate = URL.createObjectURL;
    URL.createObjectURL = function (blob) {
        const url = _origCreate.call(URL, blob);
        try {
            const ot = document.getElementById("lib-ot")?.value;
            if (ot && blob?.type === "application/pdf" && window.ModeloStateMachine) {
                setTimeout(() => window.ModeloStateMachine.onDescargado(ot), 200);
            }
        } catch (_) {
            /* silenciar errores */
        }
        return url;
    };
})();

/**
 * HOOK 6 — confirmarModelo (Vista Almacén)
 * Cuando Almacén confirma el modelo físico → espera (Nivel 2).
 */
(function _hookConfirmarModelo() {
    const _origConfirmar = window.confirmarModelo;
    window.confirmarModelo = function (ot, id_hash) {
        if (typeof _origConfirmar === "function") {
            _origConfirmar(ot, id_hash);
        }
    };
})();

/**
 * HOOK 7 — evento 'modeloLiberado' (disparado por _libSubmit tras éxito del servidor)
 * Actualiza al estado terminal definitivo (aprobado/rechazado),
 * sobreescribiendo el "espera" provisional del HOOK 4.
 */
(function _hookModeloLiberado() {
    document.addEventListener("modeloLiberado", (e) => {
        if (!window.ModeloStateMachine) return;
        const { ot, accion } = e.detail ?? {};
        if (!ot || !accion) return;
        if (accion === "aprobar") window.ModeloStateMachine.onAprobado(ot);
        if (accion === "rechazar") window.ModeloStateMachine.onRechazado(ot);
    });
})();
