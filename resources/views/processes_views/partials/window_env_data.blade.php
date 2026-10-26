{{-- Variables de entorno y rutas inyectadas para JS --}}
<script>
    window.workOrders = @json($workOrders);
    window.baseUrl = "{{ url('/') }}";
    window.edit = "{{ asset('images/iconos/img-edit.png') }}";
    window.back = "{{ asset('images/iconos/img-back.png') }}";
    window.imgError = "{{ asset('images/usuarios/personError.png') }}";
    window.imgNoPieces = "{{ asset('images/iconos/ready.png') }}";
    window.imgNoPiecesPrevious = "{{ asset('images/iconos/noPieces.png') }}";
    window.cerrarImgUrl = "{{ asset('images/iconos/cerrar.png') }}";
    window.imgCerrar = "{{ asset('images/iconos/cerrar.png') }}";
    window.imgEditPieces = "{{ asset('images/iconos/editPieces-Icon.png') }}";
    window.imgQualityCheck = "{{ asset('images/iconos/Quality.png') }}";
    window.imgDraws = "{{ asset('images/iconos/DrawsProduction.png') }}";
    window.imgProgramsCNC = "{{ asset('images/iconos/ProgramsCNC.png') }}";
    window.imgProgramsShadow = "{{ asset('images/iconos/ProgramsCNC-Shadow.png') }}";
    window.imgTechDocs = "{{ asset('images/iconos/manual.png') }}";
    window.imgNoCotas = "{{ asset('images/iconos/icono_sin_cotas.png') }}";
    window.imgCancelar = "{{ asset('images/iconos/Cancelar-Icon.png') }}";
    window.imgResultado = "{{ asset('images/iconos/Resultado-Icon.png') }}";
    window.imgBloqueado = "{{ asset('images/iconos/bloqueado.png') }}";
    window.imgMasZoom = "{{ asset('images/iconos/mas_zoom.png') }}";
    window.imgMenosZoom = "{{ asset('images/iconos/menos_zoom.png') }}";
    window.imgOt = "{{ asset('images/iconos/OT-Icon.png') }}";
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
