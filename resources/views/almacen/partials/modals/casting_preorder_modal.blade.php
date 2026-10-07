<div id="modalPreOrdenCasting" class="alm-modal" role="dialog" aria-modal="true">
    <div class="alm-modal-content alm-max-width-1800px alm-width-95vw alm-border-radius-20px alm-overflow-hidden alm-border-1-5px-solid-0284c7" style="box-shadow: 0 25px 60px rgba(2, 132, 199, 0.25);">
        {{-- HEADER DEL MODAL --}}
        <div class="alm-modal-header alm-background-linear-gradient-135deg-0369a1-0284c7 alm-padding-2-2em-2-5em-1-5em alm-position-relative" style="background: linear-gradient(135deg, #0369a1 0%, #0284c7 100%);">
            <div class="div-cerrar">
                <button type="button" class="btn-cerrar" onclick="cerrarModalPreOrdenCasting()">
                    <img class="img-cerrar" src="{{ asset('images/cerrar.png') }}" alt="Cerrar" style="width: 36px !important; height: 36px !important;">
                </button>
            </div>
            <h3 class="alm-font-size-2em alm-margin-0 alm-font-family-Poppins-sans-serif alm-font-weight-700 alm-color-fff" style="letter-spacing: 0.5px; text-shadow: 0 2px 4px rgba(0,0,0,0.15);">
                Pre-Orden de Fabricación de Casting (4ALM-17)
            </h3>
            <p id="poc-modal-subtitle" class="lib-modal-subtitle alm-color-bae6fd alm-font-size-1-15em alm-margin-top-8px alm-margin-bottom-0 alm-font-family-Poppins-sans-serif alm-font-weight-500" style="color: #e0f2fe; text-shadow: 0 1px 2px rgba(0,0,0,0.1);">
            </p>

            {{-- NAVEGACIÓN DE PROVEEDORES / PESTAÑAS (Generadas dinámicamente) --}}
            <div id="poc-tabs-container" class="alm-display-flex alm-gap-10px alm-margin-top-25px alm-border-bottom-2px-solid-rgba-255-255-255-0-2 alm-padding-bottom-0 alm-align-items-center" style="border-bottom: 2px solid rgba(255,255,255,0.25); flex-wrap: wrap;">
                <!-- Tabs renderizados por JS -->
            </div>
        </div>

        {{-- CUERPO DEL MODAL --}}
        <div class="alm-modal-body alm-padding-2-5em alm-background-fafafa alm-font-family-Poppins-sans-serif" style="background: #f8fafc; padding: 2em 2.5em;">
            <form id="formPreOrdenCasting" autocomplete="off">
                @csrf
                
                <div id="poc-pages-container">
                    <!-- Páginas renderizadas por JS -->
                </div>

                {{-- BOTÓN DE GUARDAR GLOBAL --}}
                <div class="form-actions" style="margin-top: 35px; text-align: center;">
                    <button type="submit" id="btn-submit-poc" class="btn-save-preorden btn-hover-green-override" disabled style="font-size: 1.15em; padding: 16px 42px; border-radius: 14px; font-family: 'Poppins', sans-serif; font-weight: 700; background: linear-gradient(135deg, #0369a1 0%, #0284c7 100%); border: none; color: #ffffff; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 8px 24px rgba(3, 105, 161, 0.35); height: auto; letter-spacing: 0.5px;">
                        <i class="fas fa-file-pdf" style="margin-right: 8px;"></i> Guardar y Descargar Pre-Orden de Casting
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("formPreOrdenCasting");
    if(!form) return;
    const btn = document.getElementById("btn-submit-poc");
    
    function checkCastingFormValidity() {
        if(!btn) return;
        
        const checkInputs = form.querySelectorAll("input[required], select[required], textarea[required], [data-was-required='true']");
        let isValid = true;
        
        checkInputs.forEach(input => {
            if (!input.hasAttribute("data-was-required")) {
                input.setAttribute("data-was-required", "true");
            }
            if (input.offsetParent === null) {
                input.removeAttribute("required");
            } else {
                input.setAttribute("required", "required");
                if (!input.value || !String(input.value).trim()) isValid = false;
            }
        });
        
        if (window.pocState && window.pocState.pages) {
            Object.keys(window.pocState.pages).forEach(pageNumKey => {
                const tbody = document.getElementById(`alm-tbody-poc-p${pageNumKey}`);
                if (tbody) {
                    const hasRows = tbody.querySelectorAll("tr").length > 0;
                    if (!hasRows) isValid = false;
                }
            });
        }
        
        if (isValid) {
            btn.disabled = false;
            btn.style.opacity = "1";
            btn.style.cursor = "pointer";
        } else {
            btn.disabled = true;
            btn.style.opacity = "0.6";
            btn.style.cursor = "not-allowed";
        }
    }

    form.addEventListener("input", checkCastingFormValidity);
    form.addEventListener("change", checkCastingFormValidity);
    
    setInterval(checkCastingFormValidity, 500);
});
</script>
