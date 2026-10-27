{{-- Variables de entorno y rutas inyectadas para JS --}}
<script>
    window.workOrders = @json($workOrders);
    window.baseUrl = "{{ url('/') }}";
    window.edit = "{{ asset('images/Color_Icons/Generales/img-edit.png') }}";
    window.back = "{{ asset('images/Color_Icons/Generales/img-back.png') }}";
    window.imgError = "{{ asset('images/Color_Icons/Usuarios/personError.png') }}";
    window.imgNoPieces = "{{ asset('images/Color_Icons/Generales/ready.png') }}";
    window.imgNoPiecesPrevious = "{{ asset('images/Color_Icons/Generales/noPieces.png') }}";
    window.cerrarImgUrl = "{{ asset('images/Color_Icons/Generales/cerrar.png') }}";
    window.imgCerrar = "{{ asset('images/Color_Icons/Generales/cerrar.png') }}";
    window.imgEditPieces = "{{ asset('images/Color_Icons/Generales/editPieces-Icon.png') }}";
    window.imgQualityCheck = "{{ asset('images/Color_Icons/Generales/Quality.png') }}";
    window.imgDraws = "{{ asset('images/Color_Icons/Generales/DrawsProduction.png') }}";
    window.imgProgramsCNC = "{{ asset('images/Color_Icons/Generales/ProgramsCNC.png') }}";
    window.imgProgramsShadow = "{{ asset('images/Shadow_Icons/ProgramsCNC-Shadow.png') }}";
    window.imgTechDocs = "{{ asset('images/Color_Icons/Generales/manual.png') }}";
    window.imgNoCotas = "{{ asset('images/Color_Icons/Generales/icono_sin_cotas.png') }}";
    window.imgCancelar = "{{ asset('images/Light_Icons/Cancelar-Light.png') }}";
    window.imgResultado = "{{ asset('images/Light_Icons/Resultado-Light.png') }}";
    window.imgBloqueado = "{{ asset('images/Light_Icons/Bloqueado-Light.png') }}";
    window.imgMasZoom = "{{ asset('images/Light_Icons/Mas_Zoom-Light.png') }}";
    window.imgMenosZoom = "{{ asset('images/Light_Icons/Menos_Zoom-Light.png') }}";
    window.imgOt = "{{ asset('images/Light_Icons/OT-Light.png') }}";
    window.occupiedMachines = @json((new \App\Http\Controllers\MachinesController)->machinesOccupied());
</script>
@isset($arrayData)
    <script>
        window.arrayData = @json($arrayData);
        window.pieceToBeUsed = @json($pieceToBeUsed);
        @if (!empty($arrayData['ptaTableHtml']))
            window.ptaTableHtml = @json($arrayData['ptaTableHtml']);
        @else
            window.ptaTableHtml = null;
        @endif
        @if (!empty($arrayData['edit']) && $arrayData['edit'] == 2)
            document.documentElement.classList.add('is-edit-pieces-mode');
            if (document.body) {
                document.body.classList.add('is-edit-pieces-mode');
            } else {
                document.addEventListener('DOMContentLoaded', () => document.body.classList.add('is-edit-pieces-mode'));
            }
        @endif
        @if (!empty($arrayData['meta']) && (empty($arrayData['edit']) || $arrayData['edit'] != 1))
            try {
                var savedFocus = localStorage.getItem('gis_production_table_focus');
                var shouldFocus = savedFocus !== null ? (savedFocus === '1') : (window.innerHeight <= 768);
                if (shouldFocus) {
                    document.documentElement.classList.add('table-focus-mode');
                    if (document.body) {
                        document.body.classList.add('table-focus-mode');
                    } else {
                        document.addEventListener('DOMContentLoaded', function() {
                            document.body.classList.add('table-focus-mode');
                        });
                    }
                }
            } catch(e) {}
        @endif
    </script>
@endisset
