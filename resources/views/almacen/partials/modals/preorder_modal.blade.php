<div id="modalPreOrden" class="alm-modal" role="dialog" aria-modal="true">
    <div class="alm-modal-content alm-max-width-1800px alm-width-95vw alm-border-radius-20px alm-overflow-hidden alm-border-1-5px-solid-0a8504" style="box-shadow: 0 25px 60px rgba(10, 133, 4, 0.25);">
        <div class="alm-modal-header alm-background-linear-gradient-135deg-0a8504-064e03 alm-padding-2-2em-2-5em-1-5em alm-position-relative" style="background: linear-gradient(135deg, #0a8504 0%, #064e03 100%);">
            <div class="div-cerrar">
                <button type="button" class="btn-cerrar" onclick="cerrarModalPreOrden()">
                    <img class="img-cerrar" src="{{ asset('images/cerrar.png') }}" alt="Cerrar" style="width: 36px !important; height: 36px !important;">
                </button>
            </div>
            <h3 class="alm-font-size-2em alm-margin-0 alm-font-family-Poppins-sans-serif alm-font-weight-700 alm-color-fff" style="display: flex; align-items: center; justify-content: center; gap: 10px; letter-spacing: 0.5px; text-shadow: 0 2px 4px rgba(0,0,0,0.15);">
                <img src="{{ asset('images/perspectiva-icon.png') }}"
                    style="width: 38px; height: 38px; object-fit: contain;">
                Pre-Orden de Fabricación de Modelo (PFM)
            </h3>
        </div>
        <div class="alm-modal-body alm-padding-2-5em alm-background-fafafa alm-font-family-Poppins-sans-serif" style="background: #f8fafc; padding: 2em 2.5em;">

            <div id="po-page-1" class="po-page">
                <form id="formPreOrden">
                    <div class="form-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 18px; margin-bottom: 25px; background: #ffffff; padding: 20px 24px; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
                        <div class="form-group po-proveedor-group">
                            <label for="po-proveedor" style="font-weight: 700; color: #0f172a; font-size: 0.95em; margin-bottom: 8px; display: block;">Proveedor:</label>
                            <input type="text" id="po-proveedor" name="proveedor" class="form-control" style="width: 100%; height: 44px; padding: 8px 14px; border-radius: 10px; border: 1.5px solid #cbd5e1; font-family: 'Poppins', sans-serif; font-size: 0.95em; background: #f1f5f9; color: #334155; font-weight: 600;" value="SS Metal Foundry, S. de R. L. de C. V." readonly required>
                        </div>
                        <div class="form-group po-fecha-group">
                            <label for="po-fecha" style="font-weight: 700; color: #0f172a; font-size: 0.95em; margin-bottom: 8px; display: block;">Fecha <span class="alm-text-danger">*</span>:</label>
                            <input type="date" id="po-fecha" name="fecha" class="form-control" style="width: 100%; height: 44px; padding: 8px 14px; border-radius: 10px; border: 1.5px solid #0a8504; font-family: 'Poppins', sans-serif; font-size: 0.95em; background: #ffffff; color: #0f172a; box-shadow: 0 2px 4px rgba(10,133,4,0.05);" required
                                value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="form-group po-folio-group">
                            <label for="po-folio" style="font-weight: 700; color: #0f172a; font-size: 0.95em; margin-bottom: 8px; display: block;">Folio:</label>
                            <input type="text" id="po-folio" name="folio" class="form-control" style="width: 100%; height: 44px; padding: 8px 14px; border-radius: 10px; border: 1.5px solid #cbd5e1; font-family: 'Poppins', sans-serif; font-size: 0.95em; background: #f1f5f9; color: #0a8504; font-weight: 800;" readonly
                                value="PFM-{{ date('Y') }}-0000">
                        </div>
                        <div class="form-group po-moldura-group">
                            <label for="po-moldura" style="font-weight: 700; color: #0f172a; font-size: 0.95em; margin-bottom: 8px; display: block;">Moldura:</label>
                            <input type="text" id="po-moldura" name="moldura" class="form-control" style="width: 100%; height: 44px; padding: 8px 14px; border-radius: 10px; border: 1.5px solid #cbd5e1; font-family: 'Poppins', sans-serif; font-size: 0.95em; background: #f1f5f9; color: #334155;" readonly required>
                        </div>
                        <div class="form-group po-ot-group">
                            <label for="po-ot" style="font-weight: 700; color: #0f172a; font-size: 0.95em; margin-bottom: 8px; display: block;">Orden de Trabajo:</label>
                            <input type="text" id="po-ot" name="ot" class="form-control" style="width: 100%; height: 44px; padding: 8px 14px; border-radius: 10px; border: 1.5px solid #cbd5e1; font-family: 'Poppins', sans-serif; font-size: 0.95em; background: #f1f5f9; color: #334155;" readonly required>
                            <input type="hidden" id="po-ot-raw" name="ot_raw">
                        </div>
                    </div>

                    <div class="modal-table-container" style="overflow-x: auto; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 16px; padding: 0; box-shadow: 0 4px 16px rgba(0,0,0,0.04);">
                        <table class="modal-table" style="width: 100%; border-collapse: collapse; text-align: left;">
                            <thead>
                                <tr style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff; font-weight: 700; font-size: 0.88em; text-transform: uppercase; letter-spacing: 0.5px;">
                                    <th style="padding: 14px 12px; min-width: 120px;">Tipo de Modelo <span class="alm-text-danger">*</span>
                                    </th>
                                    <th style="padding: 14px 12px; min-width: 95px;">Impresiones <span class="alm-text-danger">*</span></th>
                                    <th style="padding: 14px 12px; min-width: 95px;">Cantidad <span class="alm-text-danger">*</span></th>
                                    <th style="padding: 14px 12px; min-width: 140px;">Descripción <span class="alm-text-danger">*</span></th>
                                    <th style="padding: 14px 12px; min-width: 130px;">Código de Modelo</th>
                                    <th style="padding: 14px 12px; min-width: 130px;">Fecha Entrega</th>
                                    <th style="padding: 14px 12px; text-align: center; min-width: 70px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="alm-tbody-preorden">

                            </tbody>
                        </table>
                        <div style="margin: 16px 0; text-align: center;">
                            <button type="button" id="btn-add-clase-po" onclick="agregarFilaPreOrden()"
                                style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 24px; background: #f0fdf4; border: 2px dashed #16a34a; border-radius: 30px; color: #16a34a; font-weight: 700; font-family: 'Poppins', sans-serif; font-size: 0.92em; cursor: pointer; transition: all 0.2s ease;">
                                <span>+ Añadir otra clase / modelo</span>
                            </button>
                        </div>
                    </div>

                    <div class="form-group" style="margin-top: 25px;">
                        <div id="po-observaciones-cycle-prefix"
                            class="alm-display-none alm-padding-8px-12px alm-background-color-fee2e2 alm-border-left-4px-solid-ef4444 alm-color-991b1b alm-font-weight-bold alm-margin-bottom-8px alm-border-radius-4px alm-font-family-Poppins-sans-serif">
                        </div>
                        <label for="po-observaciones" style="font-weight: 700; color: #0f172a; font-size: 0.95em; margin-bottom: 8px; display: block;">Observaciones:</label>
                        <textarea id="po-observaciones" name="observaciones" style="width: 100%; min-height: 80px; border-radius: 12px; padding: 14px; font-family: 'Poppins', sans-serif; font-size: 0.95em; border: 1.5px solid #cbd5e1; box-sizing: border-box;" placeholder="Escribe observaciones adicionales..."></textarea>
                    </div>

                    <style>
                        #btn-submit-preorden:disabled {
                            background: #94a3b8 !important;
                            box-shadow: none !important;
                            color: #f8fafc !important;
                        }
                        #btn-submit-preorden:not(:disabled):hover {
                            background: linear-gradient(135deg, #0f9d09 0%, #0a8504 100%) !important;
                            transform: translateY(-2px);
                        }
                    </style>
                    <div class="form-actions" style="margin-top: 35px; text-align: center;">
                        <button type="submit" class="btn-save-preorden" id="btn-submit-preorden" disabled style="font-size: 1.15em; padding: 16px 42px; border-radius: 14px; font-family: 'Poppins', sans-serif; font-weight: 700; background: linear-gradient(135deg, #0a8504 0%, #064e03 100%); border: none; color: #ffffff; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 8px 24px rgba(10, 133, 4, 0.35); height: auto; letter-spacing: 0.5px;">
                            <i class="fas fa-file-pdf" style="margin-right: 8px;"></i> Guardar y Descargar PFM
                        </button>
                    </div>
                </form>
            </div>


        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const form = document.getElementById("formPreOrden");
        if (!form) return;
        const btn = document.getElementById("btn-submit-preorden");
        const tbody = document.getElementById("alm-tbody-preorden");

        function checkFormValidity() {
            if (!btn) return;
            const hasRows = tbody && tbody.querySelectorAll("tr").length > 0;
            const isValid = form.checkValidity();

            if (isValid && hasRows) {
                btn.disabled = false;
                btn.style.cursor = "pointer";
            } else {
                btn.disabled = true;
                btn.style.cursor = "not-allowed";
            }
        }

        form.addEventListener("input", checkFormValidity);
        form.addEventListener("change", checkFormValidity);

        // Check periodically in case rows are added/removed dynamically
        setInterval(checkFormValidity, 500);
    });
</script>
