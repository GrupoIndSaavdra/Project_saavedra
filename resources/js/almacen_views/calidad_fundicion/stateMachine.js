// ── MÁQUINA DE ESTADOS VISUAL — Estado del Modelo  (v4 — FSM Completa) ─────────
// ═══════════════════════════════════════════════════════════════════════════════
/**
 * ModeloStateMachine (v4)
 * ─────────────────────────────────────────────────────────────────────────────
 * FSM de 8 estados exactos en 3 niveles jerárquicos.
 *
 * REGLA DE ORO: Una vez alcanzado un nivel, los estados de nivel inferior
 * son ignorados. La transición solo avanza, nunca retrocede.
 *
 * ┌───────┬────────────┬──────────────┬──────────────────────────────────────┐
 * │ NIVEL │ Estado     │ Imagen       │ Disparador                           │
 * ├───────┼────────────┼──────────────┼──────────────────────────────────────┤
 * │   1   │ recibido   │ Recibido.png │ Alerta inicial del servidor          │
 * │   1   │ revisando  │ Revisando.png│ Clic en "Ver Archivos"               │
 * │   1   │ editando   │ Editando.png │ Clic en "Aprobar/Rechazar Lib."      │
 * ├───────┼────────────┼──────────────┼──────────────────────────────────────┤
 * │   2   │ guardado   │ Guardado.png │ Clic en "Guardar"                    │
 * │   2   │ descargado │ Descarga.png │ PDF generado y descargado            │
 * │   2   │ espera     │ documento.png│ Correo enviado / Dpto. confirmó      │
 * ├───────┼────────────┼──────────────┼──────────────────────────────────────┤
 * │   3   │ aprobado   │ Aprobado.png │ Liberación aprobada (servidor)       │
 * │   3   │ rechazado  │ Rechazado.png│ Liberación rechazada (servidor)      │
 * └───────┴────────────┴──────────────┴──────────────────────────────────────┘
 */
const ModeloStateMachine = (() => {
    function _baseUrl() {
        let b = window.baseUrl || window.location.origin + "/";
        return b.endsWith("/") ? b : b + "/";
    }
    // ── Registro de estados ───────────────────────────────────────────────────
                const ESTADOS = {
        // Almacen States
        recibido: { img: "Recibido.png", label: "Nuevo", title: "Nueva OT recibida sin acciones registradas", borderColor: "#0ea5e9", bgColor: "#f0f9ff", textColor: "#0369a1", nivel: 1, prio: 1 },
        espera: { img: "Espera.png", label: "En Espera", title: "En espera de la otra área", borderColor: "#a3a3a3", bgColor: "#fafafa", textColor: "#525252", nivel: 1, prio: 2 },
        revisando: { img: "Revisando.png", label: "En Revisión", title: "Calidad o Almacén está realizando la revisión", borderColor: "#f59e0b", bgColor: "#fffbeb", textColor: "#b45309", nivel: 2, prio: 5 },
        tiene_modelo: { img: "perspectiva-icon.png", label: "Tengo Modelo", title: "Modelo físico disponible, pendiente de procesar", borderColor: "#14b8a6", bgColor: "#f0fdfa", textColor: "#0f766e", nivel: 3, prio: 4 },
        pre_orden: { img: "PFM-icon.png", label: "Pre-Orden de Fabricación de Modelo", title: "Pre-orden generada, pendiente de firmar y enviar", borderColor: "#8b5cf6", bgColor: "#f5f3ff", textColor: "#6d28d9", nivel: 2, prio: 3 },
        casting: { img: "PFC-icon.png", label: "Pre-Orden de Fabricación de Casting", title: "Pre-orden generada, pendiente de firmar y enviar", borderColor: "#84cc16", bgColor: "#f7fee7", textColor: "#4d7c0f", nivel: 2, prio: 3 },
        por_escanear: { img: "Escanear-icon.png", label: "Por Escanear", title: "Se generó formato, pendiente de firma/envío", borderColor: "#06b6d4", bgColor: "#ecfeff", textColor: "#0e7490", nivel: 2, prio: 3 },
        proceso_parcial: { img: "proceso_parcial-icon.png", label: "Proceso Parcial", title: "Proceso parcial, esperando las demás clases", borderColor: "#ea580c", bgColor: "#fff7ed", textColor: "#c2410c", nivel: 2, prio: 4.5 },
        correo_enviado: { img: "Quality.png", label: "En Calidad", title: "Correo enviado, en espera de revisión por Calidad", borderColor: "#6366f1", bgColor: "#eef2ff", textColor: "#4338ca", nivel: 2, prio: 3 },
        enviado_proveedor: { img: "Proveedor.png", label: "Enviado a Proveedor", title: "Pre-orden de casting enviada al proveedor, proceso finalizado", borderColor: "#9333ea", bgColor: "#faf5ff", textColor: "#7e22ce", nivel: 3, prio: 100 },
        reproceso: { img: "Reproceso.png", label: "Reproceso", title: "Retornado hacia un nuevo ciclo de modelo (Reproceso)", borderColor: "#ec4899", bgColor: "#fdf2f8", textColor: "#be185d", nivel: 1, prio: 1 },
        liberado_almacen: { img: "Aprobado.png", label: "Liberado", title: "OT aprobada y liberada por Calidad", borderColor: "#22c55e", bgColor: "#f0fdf4", textColor: "#15803d", nivel: 3, prio: 99 },
        rechazado_almacen: { img: "Rechazado.png", label: "Rechazado", title: "OT rechazada por Calidad", borderColor: "#ef4444", bgColor: "#fef2f2", textColor: "#b91c1c", nivel: 3, prio: 99 },
        mixto_almacen: { img: "Mixto.png", label: "Mixto", title: "Liberación mixta por Calidad", borderColor: "#eab308", bgColor: "#fefce8", textColor: "#854d0e", nivel: 3, prio: 99 },

        // Calidad States
        por_liberar: { img: "por_liberar_icon.png", label: "Por Liberar", title: "Correo de notificación recibido, listo para revisión de Calidad", borderColor: "#64748b", bgColor: "#f1f5f9", textColor: "#334155", nivel: 2, prio: 3 },
        ldm_generado: { img: "LDM-icon.png", label: "Formato LDM", title: "Modelo aprobado y liberado por Calidad (Formato LDM)", borderColor: "#10b981", bgColor: "#ecfdf5", textColor: "#047857", nivel: 2, prio: 6 },
        rdm_generado: { img: "RDM-SCAR-icon.png", label: "Formato RDM y SCAR", title: "Modelo rechazado por Calidad (Formato RDM/SCAR)", borderColor: "#f43f5e", bgColor: "#fff1f2", textColor: "#be123c", nivel: 2, prio: 6 },
        formatos_mixtos: { img: "Formatos_Mixtos_icon.png", label: "Formatos Mixtos", title: "Formatos LDM y RDM/SCAR generados, pendiente de enviar la alerta", borderColor: "#ca8a04", bgColor: "#fef9c3", textColor: "#713f12", nivel: 2, prio: 6 },
        en_almacen: { img: "almacen.png", label: "En Almacén", title: "Dictamen enviado a Almacén", borderColor: "#3b82f6", bgColor: "#eff6ff", textColor: "#1d4ed8", nivel: 3, prio: 4 },
        aprobado: { img: "Aprobado.png", label: "Liberado", title: "OT aprobada y liberada por Calidad", borderColor: "#22c55e", bgColor: "#f0fdf4", textColor: "#15803d", nivel: 3, prio: 99 },
        rechazado: { img: "Rechazado.png", label: "Rechazado", title: "OT rechazada por Calidad", borderColor: "#ef4444", bgColor: "#fef2f2", textColor: "#b91c1c", nivel: 3, prio: 99 },
        mixto: { img: "Mixto.png", label: "Mixto", title: "Liberación mixta por Calidad", borderColor: "#eab308", bgColor: "#fefce8", textColor: "#854d0e", nivel: 3, prio: 99 },
    };
    /** Mapa alias → estado canónico para la caché interna */
        const _CANONICAL = {
        editando: "revisando",
        guardado: "revisando",
        descargado: "revisando",
        pendiente: "revisando",
        en_proceso: "revisando",
        enviando: "correo_enviado",
        documento: "tiene_modelo",
        enviado_proveedor: "casting_aprobado",
    };
    /** Caché: ot → estado canónico actual */
    const _cache = {};
    // ── Aplicar estado al DOM ─────────────────────────────────────────────────
    function _render(ot, estado, cfg) {
        const el = document.getElementById(`status-modelo-${ot}`);
        if (!el) {
            console.warn(
                `[FSM] Contenedor no encontrado: #status-modelo-${ot}`,
            );
            return;
        }

        let labelStr = cfg.label;
        let titleStr = cfg.title;
        if (/_R\d+$/i.test(ot) && estado !== 'reproceso' && estado !== 'rechazos_procesados_rechazado' && estado !== 'rechazos_procesados_aprobado') {
            labelStr += ' (Reproceso)';
            titleStr += ' (Reproceso)';
        }

        const src = _baseUrl() + "images/" + cfg.img;
        el.innerHTML = `
<div class="status-modelo-container" style="display: inline-flex; flex-direction: column; align-items: center; gap: 2px; padding: 6px; border-radius: 8px;">
<span class="badge-modelo-icon" title="${titleStr}" style="display: flex; align-items: center; justify-content: center; width: 52px; height: 52px; border-radius: 50%; background: ${cfg.bgColor}; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); border: 2px solid ${cfg.borderColor}; transition: all 0.2s ease;">
<img src="${src}" alt="${labelStr}" style="width: 34px; height: 34px; object-fit: contain;">
</span>
<span class="status-modelo-label" style="font-size: 11px; font-weight: 700; color: ${cfg.textColor}; margin-top: 4px; text-transform: uppercase; white-space: nowrap;">
${labelStr}
</span>
</div>
`;
        console.info(`[FSM] "${ot}": → ${estado} (nivel ${cfg.nivel})`);
    }
    // ── Transición normal (respeta jerarquía) ─────────────────────────────────
    function transicion(ot, estado) {
        const canonical = _CANONICAL[estado] ?? estado;
        const cfg = ESTADOS[canonical];
        if (!cfg) {
            console.warn(
                `[FSM] Estado desconocido: "${estado}" (canonical: "${canonical}")`,
            );
            return false;
        }
        const actual = _cache[ot];
        const cfgActual = actual ? ESTADOS[actual] : null;
        // Regla 1 — Terminales son permanentes
        if (cfgActual?.nivel === 3) {
            console.info(
                `[FSM] "${ot}": BLOQUEADO — terminal "${actual}" (→ ${estado})`,
            );
            return false;
        }
        // Regla 2 — No retroceder prioridad
        if (cfg.prio <= (cfgActual?.prio ?? 0)) {
            console.info(
                `[FSM] "${ot}": BLOQUEADO — retroceso (${actual}[${cfgActual?.prio}] → ${estado}[${cfg.prio}])`,
            );
            return false;
        }
        _cache[ot] = canonical;
        _render(ot, canonical, cfg);
        return true;
    }
    // ── Forzar terminal (solo desde servidor) ─────────────────────────────────
    function _forzarTerminal(ot, estado) {
        const canonical = _CANONICAL[estado] ?? estado;
        const cfg = ESTADOS[canonical];
        if (!cfg || cfg.nivel !== 3) {
            console.warn(`[FSM] _forzarTerminal: "${estado}" no es terminal`);
            return false;
        }
        _cache[ot] = canonical;
        _render(ot, canonical, cfg);
        console.info(`[FSM] "${ot}": TERMINAL FORZADO → ${estado} ★`);
        return true;
    }
    // ── Sincronización desde DOM ──────────────────────────────────────────────
    function init() {
        document.querySelectorAll('[id^="status-modelo-"]').forEach((el) => {
            const ot = el.id.replace("status-modelo-", "");
            const labelEl = el.querySelector(".status-modelo-label");
            if (labelEl && !_cache[ot]) {
                const txt = labelEl.textContent.trim().toUpperCase();
                const imgEl = el.querySelector("img");
                const imgSrc = imgEl ? imgEl.src.toUpperCase() : "";
                // Etiquetas reales emitidas por FundicionStateService (en mayúsculas)
                const LABEL_A_ESTADO = {
                    "NUEVO": "recibido",
                    "EN ESPERA": "espera",
                    "POR ESCANEAR": "por_escanear",
                    "TENGO MODELO": "tiene_modelo",
                    "EN CALIDAD": "correo_enviado",
                    "POR LIBERAR": "por_liberar",
                    "PROCESO PARCIAL": "proceso_parcial",
                    "EN REVISIÓN": "revisando",
                    "APROBADO": "aprobado",
                    "RECHAZADO": "rechazado",
                    "MIXTO": "mixto",
                    "FORMATOS MIXTOS": "formatos_mixtos",
                    "REPROCESO": "reproceso",
                    "ENVIADO A PROVEEDOR": "casting_aprobado",
                };
                const estado = LABEL_A_ESTADO[txt] ?? "recibido";
                _cache[ot] = estado;
                console.info(`[FSM] init: "${ot}" → ${estado}`);
            }
        });
    }
    function getEstado(ot) {
        return _cache[ot] ?? null;
    }
    function getNivel(ot) {
        return ESTADOS[_cache[ot]]?.nivel ?? 0;
    }
    function onAlertaEnviada(ot) {
        transicion(ot, "recibido");
    }
    function onVerArchivos(ot) {
        transicion(ot, "revisando");
    }
    function onAbrirDecision(ot) {
        transicion(ot, "editando");
    }
    function onGuardar(ot) {
        transicion(ot, "guardado");
    }
    function onDescargado(ot) {
        transicion(ot, "descargado");
    }
    function onCorreoEnviado(ot) {
        transicion(ot, "correo_enviado");
    }
    function onConfirmarModelo(ot) {
        transicion(ot, "tiene_modelo");
    }
    function onEnEspera(ot) {
        transicion(ot, "tiene_modelo");
    }
    function onAprobado(ot) {
        _forzarTerminal(ot, "aprobado");
    }
    function onRechazado(ot) {
        _forzarTerminal(ot, "rechazado");
    }
    return {
        transicion,
        _forzarTerminal,
        init,
        getEstado,
        getNivel,
        onAlertaEnviada,
        onVerArchivos,
        onAbrirDecision,
        onGuardar,
        onDescargado,
        onCorreoEnviado,
        onConfirmarModelo,
        onEnEspera,
        onAprobado,
        onRechazado,
        ESTADOS,
    };
})();
window.ModeloStateMachine = ModeloStateMachine;
// ═══════════════════════════════════════════════════════════════════════════════


// Expose to window for global access
window.ModeloStateMachine = ModeloStateMachine;
