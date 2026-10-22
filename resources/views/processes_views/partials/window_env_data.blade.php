{{-- Variables de entorno y rutas inyectadas para JS --}}
<script>
    window.workOrders = @json($workOrders);
    window.baseUrl = "{{ url('/') }}";
    window.edit = "{{ asset('images/img-edit.png') }}";
    window.back = "{{ asset('images/img-back.png') }}";
    window.imgError = "{{ asset('images/personError.png') }}";
    window.imgNoPieces = "{{ asset('images/ready.png') }}";
    window.imgNoPiecesPrevious = "{{ asset('images/noPieces.png') }}";
    window.cerrarImgUrl = "{{ asset('images/cerrar.png') }}";
    window.imgCerrar = "{{ asset('images/cerrar.png') }}";
    window.imgEditPieces = "{{ asset('images/editPieces-Icon.png') }}";
    window.imgQualityCheck = "{{ asset('images/Quality.png') }}";
    window.imgDraws = "{{ asset('images/DrawsProduction.png') }}";
    window.imgProgramsCNC = "{{ asset('images/ProgramsCNC.png') }}";
    window.imgProgramsShadow = "{{ asset('images/ProgramsCNC-Shadow.png') }}";
    window.imgTechDocs = "{{ asset('images/manual.png') }}";
    window.imgNoCotas = "{{ asset('images/icono_sin_cotas.png') }}";
    window.imgCancelar = "{{ asset('images/Cancelar-Icon.png') }}";
    window.imgResultado = "{{ asset('images/Resultado-Icon.png') }}";
    window.imgBloqueado = "{{ asset('images/bloqueado.png') }}";
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
    </script>
@endisset
