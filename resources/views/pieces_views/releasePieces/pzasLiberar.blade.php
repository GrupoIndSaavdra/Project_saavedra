@extends('layouts.appMenu')

@section('head')
    <title>Liberación de piezas</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('images/logos/favicon.ico') }}?v=3">
    <script>
        window.liberar = "{{ asset('images/Color_Icons/Generales/Liberar.png') }}";
        window.rechazar = "{{ asset('images/Color_Icons/Generales/Rechazar.png') }}";
        window.ojito = "{{ asset('images/Color_Icons/Generales/ojito.png') }}";
        window.loading = "{{ asset('images/Color_Icons/Generales/loading.gif') }}";
        window.iconSinOrdenLight = "{{ asset('images/Light_Icons/Sin_Orden-Light.png') }}";
        window.iconOrdenAscLight = "{{ asset('images/Light_Icons/Orden_Asc-Light.png') }}";
        window.iconOrdenDescLight = "{{ asset('images/Light_Icons/Orden_Desc-Light.png') }}";
        window.iconSinOrdenShadow = "{{ asset('images/Shadow_Icons/Sin_Orden-Shadow.png') }}";
        window.iconOrdenAscShadow = "{{ asset('images/Shadow_Icons/Orden_Asc-Shadow.png') }}";
        window.iconOrdenDescShadow = "{{ asset('images/Shadow_Icons/Orden_Desc-Shadow.png') }}";
        window.back = "{{ asset('images/Color_Icons/Generales/img-back.png') }}";
        window.baseUrl = "{{ url('/') }}";
    </script>
    @vite(['resources/js/pieces_views/releasePieces/releasePieces.js', 'resources/css/pieces_views/releasePieces/releasePieces_view.css'])
@endsection

@section('background-body', 'background-image:url("' . asset('images/fondos/fondoLogin.jpg') . '")')
<!--Body background Image-->

@section('content')
    @if (!isset($pieces) || count($pieces) == 0)
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
    <form action="{{ route('piecesRelease') }}" method="post" class="form-search">
        @csrf
        <!-- FILTROS DE BÚSQUEDA Y RESULTADOS DE PIEZAS EN GENERAL. -->
        <div class="report-header">
            <!-- INDICADOR / BOTÓN DE ORDEN SUPERIOR IZQUIERDO -->
            <button type="button" class="header-order-badge" id="headerOrderBadge" title="Haz clic para restablecer el orden original">
                <div class="header-order-icon-wrapper">
                    <img src="{{ asset('images/Shadow_Icons/Sin_Orden-Shadow.png') }}" class="header-order-icon" id="headerOrderIcon" alt="Orden">
                </div>
                <span class="header-order-text" id="headerOrderText">Sin orden</span>
            </button>

            <div class="title-row">
                <img src="{{ asset('images/Color_Icons/Vistas/Liberacion_Pzas.png') }}" class="header-view-icon"
                    alt="Liberación de piezas" />
                <h1>Liberación de piezas</h1>
                <img src="{{ asset('images/Color_Icons/Vistas/Liberacion_Pzas.png') }}" class="header-view-icon"
                    alt="Liberación de piezas" />
            </div>
            <!-- IMAGEN DE PDF -->
            <button type="submit" name="action" value="pdf" class="btn-PDF" title="Descargar PDF">
                <div class="pdf-btn-wrapper">
                    <img src="{{ asset('images/Shadow_Icons/PDF_View-Shadow.png') }}" class="pdf-icon icon-shadow" alt="PDF">
                    <img src="{{ asset('images/Color_Icons/Generales/pdf-view.png') }}" class="pdf-icon icon-view generar_pdf" id="pdf"
                        alt="PDF">
                </div>
                <span class="pdf-btn-text">Descargar PDF</span>
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
                            <th>Fecha de Maquinado</th>
                            <th>Fecha de Liberacion</th>
                            <th>Liberado por</th>
                            <th>Observaciones de Liberacion</th>
                            <th>Liberar</th>
                            <th>Rechazar</th>
                            <th>Ver</th>
                        </tr>
                    </thead>
                </table>
            </div>
            <div class="total-records-found">
                <span class="records-completed-badge">
                    <span class="badge-status-dot"></span>
                    <span>Registros cargados: <strong>{{ number_format(count($pieces)) }}</strong></span>
                </span>
            </div>
        @else
            <div class="letrero">
                <label class="advertence"> No hay piezas trabajadas.</label>
            </div>
        @endif
    </form>
    <div class="colors-legend-container">
        <button type="button" class="colors-toggle-btn" title="Ver Código de Colores" aria-label="Código de Colores">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                stroke-linecap="round" stroke-linejoin="round" class="toggle-icon">
                <circle cx="13.5" cy="6.5" r=".5" fill="currentColor" />
                <circle cx="17.5" cy="10.5" r=".5" fill="currentColor" />
                <circle cx="8.5" cy="7.5" r=".5" fill="currentColor" />
                <circle cx="6.5" cy="12.5" r=".5" fill="currentColor" />
                <path
                    d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.563-2.512 5.563-5.563C22 6.5 17.5 2 12 2z" />
            </svg>
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
                    $colorsArray = [
                        'Azul' => ['label' => 'Liberado', 'chip' => 'chip-azul'],
                        'Rojo' => ['label' => 'Rechazado', 'chip' => 'chip-rojo'],
                        'Verde' => ['label' => 'Buena sin liberación / rechazo', 'chip' => 'chip-verde'],
                        'Morado' => ['label' => 'Mala sin liberación / rechazo', 'chip' => 'chip-morado'],
                        'Amarillo' => ['label' => 'Incompleto', 'chip' => 'chip-amarillo'],
                    ];
                    ?>
                    @foreach ($colorsArray as $key => $item)
                        <tr class="color-row">
                            <td class="color-chip {{ $item['chip'] }}">{{ $key }}</td>
                            <td class="color-desc">{{ $item['label'] }}</td>
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

    {{-- Modal de selección de fecha interactivo --}}
    @include('components.modal_cm_calendar')
@endsection
