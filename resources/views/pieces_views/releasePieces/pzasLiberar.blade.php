@extends('layouts.appMenu')

@section('head')
<title>Liberación de piezas</title>
<script>
    window.liberar = "{{ asset('images/Liberar.png') }}"
    window.rechazar = "{{ asset('images/Rechazar.png') }}"
    window.ojito = "{{ asset('images/ojito.png') }}"
    window.loading = "{{ asset('images/loading.gif') }}"
    window.baseUrl = "{{ url('/') }}";
</script>
@vite(['resources/js/pieces_views/releasePieces/releasePieces.js', 'resources/css/pieces_views/piecesReport/adminPieces.css'])
@endsection

@section('background-body', 'background-image:url("' . asset("images/fondoLogin.jpg") . '")') <!--Body background Image-->

@section('content')
@if(!isset($pieces) || count($pieces) == 0)
<style>
    @media screen and (max-width: 600px) {
        .container {
            width: 100%;
        }

        form {
            overflow: hidden;
            width: 80%;
        }

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
<div class="container">
    <form action="{{ route('piecesRelease') }}" method="post">
        @csrf
        <!-- FILTROS DE BÚSQUEDA Y RESULTADOS DE PIEZAS EN GENERAL. -->
        <h1>Liberación de piezas</h1>
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
                        <th>Clase</th>
                        <th>Orden de trabajo</th>
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
                        <th>Observaciones</th>
                        <th>Liberar</th>
                        <th>Rechazar</th>
                        <th>Ver</th>
                    </tr>
                </thead>
            </table>
        </div>
        <div class="total-records-found">Registros encontrados: {{ count($pieces) }}</div>
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
</div>
<script>
    window.pieces = @json($pieces);
    window.infoPieces = @json($infoPieces);
    window.piecesData = @json($piecesData);
    window.selectedItems = @json($selectedItems);
    window.filtersData = @json($filtersData);
</script>
@endsection