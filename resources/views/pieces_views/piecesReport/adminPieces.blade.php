@extends('layouts.appMenu')

@section('head')
<title>Reporte de piezas</title>
@vite(['resources/css/pieces_views/piecesReport/adminPieces.css', 'resources/js/pieces_views/piecesReport/adminPieces.js'])
@endsection
<script>
    window.liberar = "{{ asset('images/Liberar.png') }}"
    window.rechazar = "{{ asset('images/Rechazar.png') }}"
    window.ojito = "{{ asset('images/ojito.png') }}"

    window.baseUrl = "{{ url('/') }}";
</script>
@section('background-body', 'background-image:url("' . asset("images/fondoLogin.jpg") . '")') <!--Body background Image-->
@section('content')

@if(!isset($pieces) || count($pieces) == 0)
<style>
    @media (max-width: 991.98px) {
        .container {
            width: 100%;
        }

        .title_ot {
            width: 100%;
            text-align: center;
            font-size: 1rem;
        }

        /* .generar_pdf {
            width: 10%;
            height: 10%;
        }

        form {
            overflow: hidden;
            width: 100%;
        } */

        .icono-liberar,
        .icono-rechazar {
            width: 20px;
            /* Ancho */
            height: 20px;
            /* Alto */
        }

    }
</style>
@endif

<form action="{{ route('searchPieces') }}" method="post">
    @csrf
    <input type="hidden" name="profile" value="admin">
    <!-- FILTROS DE BÚSQUEDA Y RESULTADOS DE PIEZAS EN GENERAL. -->
    <h1>Reporte de piezas</h1>
    <div class="filters"></div>
    <!-- IMAGEN DE PDF -->
    <button type="submit" name="action" value="pdf" class="btn-PDF">
        <img src="{{ asset('images/pdf.png')}}" alt="pdf" id="pdf" class="generar_pdf">
    </button>

    @if (count($pieces) > 0)
    <div class="div-table">
        <table class="table">
            <thead>
                <tr>
                    <th>Juego</th>
                    <th style="width: 500px;">Nombre del operador</th>
                    <th>Máquina</th>
                    <th style="width: 500px;">Proceso</th>
                    @foreach ($pieces as $piece)
                    @if ($piece[4] == "Operacion Equipo")
                    <th>Operacion</th>
                    <script>
                        operacion = true;
                    </script>
                    @break
                    @endif
                    @endforeach
                    <th style="width: 300px;">Errores</th>
                    <th>Fecha de Maquinado</th>
                    <th>Fecha de Liberacion</th>
                    <th>Liberado por</th>
                    <th>Ver</th>
                </tr>
            </thead>
        </table>
    </div>
    @else
    <div class="letrero">
        <label class="advertence"> No hay piezas trabajadas.</label>
    </div>
    @endif
</form>
<div class="colors">
    <table class="table-colors">
        <thead>
            <tr>
                <th colspan="2">Tabla de colores</th>
            </tr>
            <tr>
                <th>Color</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php $colorsArray = ["Azul" => "Liberado", "Rojo" => "Rechazado", "Verde" => "Buena sin liberacion/rechazo", "Morado" => "Mala sin liberacion/rechazo", "Amarillo" => "Incompleto"]; ?>
            @foreach ($colorsArray as $key => $colorArray)
            <tr>
                <td>{{$key}}</td>
                <td>{{$colorArray}}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<script>
    let pieces = @json($pieces);
    let infoPiezas = @json($infoPieces);
    window.piecesData = @json($piecesData);
    window.selectedItems = @json($selectedItems);
    window.filtersData = @json($filtersData);
</script>
@endsection