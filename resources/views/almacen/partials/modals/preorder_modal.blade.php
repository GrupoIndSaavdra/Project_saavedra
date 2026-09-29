<div id="modalPreOrden" class="alm-modal">
    <div class="alm-modal-content">
        <div class="alm-modal-header">
            <div class="div-cerrar">
                <button type="button" class="btn-cerrar" onclick="cerrarModalPreOrden()">
                    <img class="img-cerrar" src="{{ asset('images/cerrar.png') }}">
                </button>
            </div>
            <h3
                style="margin-top: 0; margin-bottom: 5px; color: #ffffff; font-size: 1.3em; font-family: 'Poppins', sans-serif; font-weight: 700; display: flex; align-items: center; justify-content: center; gap: 10px;">
                <img src="{{ asset('images/perspectiva-icon.png') }}"
                    style="width: 38px; height: 38px; object-fit: contain;">
                Pre-Orden de Fabricación de Modelo (PFM)
            </h3>
        </div>
        <div class="alm-modal-body">

            <div id="po-page-1" class="po-page">
                <form id="formPreOrden">
                    <div class="form-grid">
                        <div class="form-group po-proveedor-group">
                            <label for="po-proveedor">Proveedor <span class="alm-text-danger">*</span>:</label>
                            <select id="po-proveedor" name="proveedor" class="form-control" required>
                                <option value="SS Metal Foundry, S. de R. L. de C. V." selected>SS Metal Foundry, S. de
                                    R. L. de C. V.</option>
                                <option value="Sociedad Cooperativa de Producción Jacarandas">Sociedad Cooperativa de
                                    Producción Jacarandas</option>
                            </select>
                        </div>
                        <div class="form-group po-fecha-group">
                            <label for="po-fecha">Fecha:</label>
                            <input type="date" id="po-fecha" name="fecha" class="form-control" required
                                value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="form-group po-folio-group">
                            <label for="po-folio">Folio:</label>
                            <input type="text" id="po-folio" name="folio" class="form-control" readonly
                                value="PFM-{{ date('Y') }}-0000">
                        </div>
                        <div class="form-group po-moldura-group">
                            <label for="po-moldura">Moldura:</label>
                            <input type="text" id="po-moldura" name="moldura" class="form-control" readonly required>
                        </div>
                        <div class="form-group po-ot-group">
                            <label for="po-ot">Orden de Trabajo:</label>
                            <input type="text" id="po-ot" name="ot" class="form-control" readonly required>
                            <input type="hidden" id="po-ot-raw" name="ot_raw">
                        </div>
                    </div>

                    <style>
                        #modalPreOrden .modal-table thead th {
                            background-color: #033966;
                            color: #ffffff;
                            padding: 10px 8px;
                        }
                    </style>
                    <div class="modal-table-container">
                        <table class="modal-table">
                            <thead>
                                <tr>
                                    <th class="alm-width-16pct">Tipo de Modelo <span class="alm-text-danger">*</span>
                                    </th>
                                    <th class="alm-w-12">Impresiones <span class="alm-text-danger">*</span></th>
                                    <th class="alm-w-12">Cantidad <span class="alm-text-danger">*</span></th>
                                    <th class="alm-width-22pct">Descripción <span class="alm-text-danger">*</span></th>
                                    <th class="alm-width-22pct">Código de Modelo</th>
                                    <th class="alm-w-12">Fecha Entrega</th>
                                    <th class="alm-width-6pct alm-text-align-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="alm-tbody-preorden">

                            </tbody>
                        </table>
                        <div class="alm-margin-top-10px alm-text-align-center">
                            <button type="button" id="btn-add-clase-po" onclick="agregarFilaPreOrden()"
                                class="btn-img-action alm-display-inline-block">
                                <img src="{{ asset('images/anadir.png') }}" alt="Añadir" class="alm-width-40px">
                            </button>
                        </div>
                    </div>

                    <div class="form-group alm-margin-top-20px">
                        <div id="po-observaciones-cycle-prefix"
                            class="alm-display-none alm-padding-8px-12px alm-background-color-fee2e2 alm-border-left-4px-solid-ef4444 alm-color-991b1b alm-font-weight-bold alm-margin-bottom-8px alm-border-radius-4px alm-font-family-Poppins-sans-serif">
                        </div>
                        <label for="po-observaciones">Observaciones:</label>
                        <textarea id="po-observaciones" name="observaciones" class="form-control" rows="3"></textarea>
                    </div>

                    <style>
                        #btn-submit-preorden {
                            transition: all 0.3s ease;
                            background: linear-gradient(135deg, rgb(10, 133, 4), rgb(6, 78, 3));
                            color: white;
                            border: none;
                        }

                        #btn-submit-preorden:disabled {
                            background: #94a3b8 !important;
                            /* Gris neutro oscuro */
                            box-shadow: none !important;
                            color: #f8fafc !important;
                        }
                    </style>
                    <div class="form-actions alm-margin-top-30px alm-text-align-center">
                        <button type="submit" class="btn-save-preorden" id="btn-submit-preorden" disabled>
                            Guardar y Descargar PFM
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
