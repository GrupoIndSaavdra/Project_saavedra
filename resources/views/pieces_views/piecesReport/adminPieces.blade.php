@extends('layouts.appMenu')

@section('head')
    <title>Reporte de piezas</title>
    <link rel="icon" href="{{ url('images/lg_saavedra.png') }}?v=1">
    @vite(['resources/css/pieces_views/piecesReport/adminPieces.css', 'resources/js/pieces_views/piecesReport/adminPieces.js'])
@endsection
<script>
    window.liberar = "{{ asset('images/Liberar.png') }}"
    window.rechazar = "{{ asset('images/Rechazar.png') }}"
    window.ojito = "{{ asset('images/ojito.png') }}"
    window.loading = "{{ asset('images/loading.gif') }}"
    window.baseUrl = "{{ url('/') }}";
</script>
@section('background-body', 'background-image:url("' . asset('images/fondoLogin.jpg') . '")')
<!--Body background Image-->
@section('content')

    @if (!isset($pieces) || count($pieces) == 0)
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

    <form action="{{ route('searchPieces') }}" method="post" class="form-search">
        @csrf
        <input type="hidden" name="profile" value="admin">
        <div class="report-header">
            <h1>Reporte de piezas</h1>
            <!-- IMAGEN DE PDF -->
            <button type="submit" name="action" value="pdf" class="btn-PDF">
                <img src="{{ asset('images/pdf.png') }}" alt="pdf" id="pdf" class="generar_pdf">
            </button>
        </div>
        <div class="filters"></div>

        @if (count($pieces) > 0)
            <div class="div-table">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Clase</th>
                            <th>Orden de trabajo</th>
                            <th>Juego</th>
                            <th>Nombre del operador</th>
                            <th>Máquina</th>
                            <th>Proceso</th>
                            <th>Errores</th>
                            <th>Observaciones</th>
                            <th class="adminp-w-100">Inicio</th>
                            <th>Término</th>
                            <th>Total Maquinado</th>
                            <th>Fecha de Maquinado</th>
                            <th>Fecha de Liberacion</th>
                            <th>Liberado por</th>
                            <th>Observaciones de Liberacion</th>
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
    <div class="colors-legend-container">
        <button type="button" class="colors-toggle-btn">
            <span class="toggle-text">Código de Colores</span>
        </button>
        <div class="colors-content">
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
                    <?php
    $colorsArray = ['Azul' => 'Liberado', 'Rojo' => 'Rechazado', 'Verde' => 'Buena sin liberacion/rechazo', 'Morado' => 'Mala sin liberacion/rechazo', 'Amarillo' => 'Incompleto'];
    $colorStyles = [
        'Azul' => 'background-color: #79BFED; color: black; font-weight: bold;',
        'Rojo' => 'background-color: #FF6B6B; color: black; font-weight: bold;',
        'Verde' => 'background-color: #90EE90; color: black; font-weight: bold;',
        'Morado' => 'background-color: #DDA0DD; color: black; font-weight: bold;',
        'Amarillo' => 'background-color: #FFD700; color: black; font-weight: bold;',
    ];
                            ?>
                    @foreach ($colorsArray as $key => $colorArray)
                        <tr>
                            <td style="{{ $colorStyles[$key] }}">{{ $key }}</td>
                            <td>{{ $colorArray }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <script>
        window.pieces = @json($pieces);
        window.infoPiezas = @json($infoPieces);
        window.piecesData = @json($piecesData);
        window.selectedItems = @json($selectedItems);
        window.filtersData = @json($filtersData);
    </script>
@endsection
