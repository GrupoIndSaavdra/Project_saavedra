{{-- _modal_scar.blade.php — Formato SCAR de Modelos --}}

{{-- ── MODAL SCAR ── --}}
<div id="modalScar" class="alm-modal" role="dialog" aria-modal="true">
    <div class="alm-modal-content lib-modal-content" style="max-width: 780px;">

        {{-- CABECERA --}}
        <div class="alm-modal-header lib-modal-header lib-modal-header-rechazo" id="scar-modal-header">
            <div class="div-cerrar">
                <button type="button" class="btn-cerrar" onclick="cerrarModalScar()">
                    <img class="img-cerrar" src="{{ asset('images/cerrar.png') }}" alt="Cerrar">
                </button>
            </div>
            <div class="lib-header-top">
                <div class="lib-format-meta">
                    <span class="lib-meta-item"><strong>Codigo:</strong> F-CCL-SCAR</span>
                    <span class="lib-meta-sep">|</span>
                    <span class="lib-meta-item"><strong>Version:</strong> A</span>
                    <span class="lib-meta-sep">|</span>
                    <span class="lib-meta-item"><strong>Revision:</strong> 26 de mayo de 2026</span>
                </div>
                <h3 class="lib-modal-title-text" style="color: #ffffff;">
                    Formato SCAR de Modelos — Solicitud de Acción Correctiva
                </h3>
                <p id="scar-modal-subtitle" class="lib-modal-subtitle"></p>
            </div>
        </div>

        {{-- CUERPO --}}
        <div class="alm-modal-body lib-modal-body">
            <form id="formScar" autocomplete="off" enctype="multipart/form-data">
                <input type="hidden" id="scar-ot" name="ot">
                <input type="hidden" id="scar-tipo" name="tipo_modelo">
                <input type="hidden" id="scar-motivo" name="motivo_rechazo_heredado">

                {{-- DATOS PRINCIPALES --}}
                <div class="lib-format-datos" style="margin-bottom: 18px;">
                    <div class="lib-datos-grid" style="grid-template-columns: repeat(3, 1fr); gap: 12px;">
                        <div class="lib-dato-group">
                            <label class="lib-dato-label">Orden de Trabajo:</label>
                            <span id="scar-ot-display" class="lib-dato-value">—</span>
                        </div>
                        <div class="lib-dato-group">
                            <label class="lib-dato-label">Tipo de Modelo:</label>
                            <span id="scar-tipo-display" class="lib-dato-value">—</span>
                        </div>
                        <div class="lib-dato-group">
                            <label class="lib-dato-label">Fecha de Emisión:</label>
                            <span class="lib-dato-value">{{ now()->format('d/m/Y') }}</span>
                        </div>
                    </div>
                </div>

                {{-- DATOS COMPLEMENTARIOS --}}
                <div class="lib-section-block" style="margin-bottom: 16px; border-bottom: 1px solid #e2e8f0; padding-bottom: 16px;">
                    <h5 style="font-weight: 700; color: #334155; font-size: 1.05em; margin-bottom: 8px;">Datos del Solicitante</h5>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                        <div>
                            <label class="lib-dato-label" for="scar-cliente-empresa">Cliente / Empresa:</label>
                            <input type="text" id="scar-cliente-empresa" name="cliente_empresa" class="form-control" value="Industrial Saavedra">
                        </div>
                        <div>
                            <label class="lib-dato-label" for="scar-area-solicitante">Área Solicitante:</label>
                            <input type="text" id="scar-area-solicitante" name="area_solicitante" class="form-control" value="Calidad">
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div>
                            <label class="lib-dato-label" for="scar-nombre-solicitante">Nombre del Solicitante:</label>
                            <input type="text" id="scar-nombre-solicitante" name="nombre_solicitante" class="form-control" value="{{ Auth::user() ? Auth::user()->name : '' }}" placeholder="Inspector de Calidad">
                        </div>
                        <div>
                            <label class="lib-dato-label" for="scar-nombre-moldura">Nombre de la Moldura:</label>
                            <input type="text" id="scar-nombre-moldura" name="nombre_moldura" class="form-control" placeholder="Nombre de la moldura">
                        </div>
                    </div>
                </div>

                {{-- DESCRIPCIÓN DE LA NO CONFORMIDAD (pre-rellenado con motivo_rechazo) --}}
                <div class="lib-section-block" style="margin-bottom: 16px;">
                    <h5 style="font-weight: 700; color: #9c0300; font-size: 1.05em; margin-bottom: 6px;">
                        Descripción de la No Conformidad
                    </h5>
                    <p class="lib-section-hint" style="color: #9c0300; margin-bottom: 6px;">
                        Este campo se pre-rellena con el motivo de rechazo registrado en el formato F-CCL-LDM.
                    </p>
                    <textarea id="scar-descripcion" name="descripcion_no_conformidad"
                        class="form-control lib-textarea lib-textarea-danger" rows="4"
                        placeholder="Descripción del incumplimiento detectado..."></textarea>
                </div>

                {{-- PROVEEDOR --}}
                <div class="lib-section-block" style="margin-bottom: 16px;">
                    <h5 style="font-weight: 700; color: #334155; font-size: 1.05em; margin-bottom: 6px;">
                        Proveedor
                    </h5>
                    <input type="text" id="scar-proveedor" name="proveedor" class="form-control"
                        value="SS Metal Foundry, S. de R.L. de C.V." placeholder="Nombre del proveedor del modelo">
                </div>

                {{-- EVIDENCIA ADJUNTA --}}
                <div class="lib-section-block" style="margin-bottom: 16px; border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0; padding-top: 16px; padding-bottom: 16px;">
                    <h5 style="font-weight: 700; color: #334155; font-size: 1.05em; margin-bottom: 8px;">Evidencia Adjunta</h5>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <div>
                            <label style="display: flex; align-items: center; gap: 8px; font-weight: 500; cursor: pointer;">
                                <input type="checkbox" id="scar-evidencia-reporte" name="evidencia_reporte" value="1" checked onclick="return false;" style="width: 16px; height: 16px;">
                                <span>Reporte dimensional de Calidad (Obligatorio)</span>
                            </label>
                        </div>
                        <div>
                            <label style="display: flex; align-items: center; gap: 8px; font-weight: 500; cursor: pointer;">
                                <input type="checkbox" id="scar-evidencia-dibujos" name="evidencia_dibujos" value="1" style="width: 16px; height: 16px;">
                                <span>Dibujos autorizados</span>
                            </label>
                        </div>
                        <div>
                            <label style="display: flex; align-items: center; gap: 8px; font-weight: 500; cursor: pointer;">
                                <input type="checkbox" id="scar-evidencia-ayudas" name="evidencia_ayudas" value="1" style="width: 16px; height: 16px;">
                                <span>Ayudas visuales</span>
                            </label>
                        </div>
                        <div>
                            <label style="display: flex; align-items: center; gap: 8px; font-weight: 500; cursor: pointer;">
                                <input type="checkbox" id="scar-evidencia-fotos" name="evidencia_fotos" value="1" style="width: 16px; height: 16px;">
                                <span>Fotografías <span style="font-size:0.82em; color:#64748b;">(se adjuntarán al enviar alerta)</span></span>
                            </label>
                        </div>
                        <div style="grid-column: span 2;">
                            <label style="display: flex; align-items: center; gap: 8px; font-weight: 500; cursor: pointer;">
                                <input type="checkbox" id="scar-evidencia-otro" name="evidencia_otro" value="1" style="width: 16px; height: 16px;">
                                <span>Otro / PDFs adicionales <span style="font-size:0.82em; color:#64748b;">(se adjuntarán al enviar alerta)</span></span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- ACCIÓN CORRECTIVA INMEDIATA REQUERIDA --}}
                <div class="lib-section-block" style="margin-bottom: 16px;">
                    <h5 style="font-weight: 700; color: #334155; font-size: 1.05em; margin-bottom: 8px;">Acción Correctiva Inmediata Requerida</h5>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <label style="display: flex; align-items: center; gap: 8px; font-weight: 500; cursor: pointer;">
                            <input type="checkbox" id="scar-accion-regreso" name="accion_regreso" value="1" style="width: 16px; height: 16px;">
                            <span>Regreso del modelo al proveedor para su corrección</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; font-weight: 500; cursor: pointer;">
                            <input type="checkbox" id="scar-accion-fabricacion" name="accion_fabricacion" value="1" style="width: 16px; height: 16px;">
                            <span>Fabricación de un modelo nuevo</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; font-weight: 500; cursor: pointer;">
                            <input type="checkbox" id="scar-accion-otro" name="accion_otro" value="1" style="width: 16px; height: 16px;" onchange="document.getElementById('scar-accion-otro-text-group').style.display = this.checked ? 'block' : 'none'">
                            <span>Otro</span>
                        </label>
                        
                        <div id="scar-accion-otro-text-group" style="display: none; margin-top: 4px; padding-left: 24px;">
                            <input type="text" id="scar-accion-otro-texto" name="accion_otro_texto" class="form-control" placeholder="Escriba la acción correctiva inmediata requerida...">
                        </div>
                    </div>
                </div>

                {{-- CAUSA RAÍZ --}}
                <div class="lib-section-block" style="margin-bottom: 16px; border-top: 1px solid #e2e8f0; padding-top: 16px;">
                    <h5 style="font-weight: 700; color: #334155; font-size: 1.05em; margin-bottom: 6px;">
                        Causa Raíz del Defecto (a cargo del Proveedor)
                    </h5>
                    <textarea id="scar-causa-raiz" name="causa_raiz" class="form-control lib-textarea" rows="3"
                        placeholder="Indique la causa raíz identificada por el proveedor..."></textarea>
                </div>

                {{-- ACCIONES CORRECTIVAS --}}
                <div class="lib-section-block" style="margin-bottom: 16px;">
                    <h5 style="font-weight: 700; color: #334155; font-size: 1.05em; margin-bottom: 6px;">
                        Acciones Correctivas a Futuro
                    </h5>
                    <textarea id="scar-acciones" name="acciones_correctivas" class="form-control lib-textarea" rows="3"
                        placeholder="Indique las acciones correctivas planificadas para evitar recurrencia..."></textarea>
                </div>

                {{-- CÓDIGO DEL MODELO Y FECHA COMPROMISO (Vacio inicial) --}}
                <div class="lib-section-block" style="margin-bottom: 16px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div>
                            <label for="scar-fecha-compromiso"
                                style="font-weight: 700; color: #334155; font-size: 0.95em; display: block; margin-bottom: 4px;">
                                Fecha de Emisión / Compromiso de Correctivas:
                            </label>
                            <input type="date" id="scar-fecha-compromiso" name="fecha_compromiso" class="form-control">
                        </div>
                        <div>
                            <label for="scar-codigo-modelo"
                                style="font-weight: 700; color: #334155; font-size: 0.95em; display: block; margin-bottom: 4px;">
                                Código del Modelo:
                            </label>
                            <input type="text" id="scar-codigo-modelo" name="codigo_modelo" class="form-control"
                                placeholder="Código de referencia del modelo">
                        </div>
                    </div>
                </div>

                {{-- BOTONES --}}
                <div class="lib-actions" style="margin-top: 24px; display: flex; justify-content: center; gap: 12px;">
                    <button type="button" class="btn-lib-save" id="scar-btn-guardar" onclick="scarSubmit('guardar')">
                        <img src="{{ asset('images/Descarga.png') }}" alt="">
                        <span>Guardar y Descargar SCAR</span>
                    </button>
                </div>

            </form>
        </div>

    </div>
</div>