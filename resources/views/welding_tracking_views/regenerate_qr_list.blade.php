@extends('layouts.appMenu')

@section('head')
    <title>Regenerar QRs - Lista de Lotes</title>
    <link rel="icon" href="{{ url('images/lg_saavedra.png') }}?v=1">
    @vite(['resources/css/welding_tracking_views/regenerate_qr.css'])
@endsection

@section('background-body', 'background-image:url("' . asset("images/fondoLogin.jpg") . '")')

@section('content')
    <div class="wrapper-lista">
        <div class="header-lista">
            <div class="header-info">
                <h2>Regenerar QRs de Soldadura</h2>
                <p class="subtitulo">Selecciona un lote para descargar sus QRs</p>
            </div>
            <a href="{{ route('soldadura.regenerarQR.cerrar') }}" class="btn btn-danger btn-cerrar">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z" />
                    <path
                        d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z" />
                </svg>
                Cerrar Sesión
            </a>
        </div>

        @include('layouts.partials.messages')

        @if($lotes->isEmpty())
            <div class="alert-vacio">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z" />
                    <path
                        d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533L8.93 6.588zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0z" />
                </svg>
                <p>No hay lotes de soldadura registrados en el sistema.</p>
            </div>
        @else
            <div class="tabla-container">
                <table class="tabla-lotes">
                    <thead>
                        <tr>
                            <th>ID bote</th>
                            <th>Soldadura</th>
                            <th>Lote</th>
                            <th>Peso (lote)</th>
                            <th>Factura</th>
                            <th>Fecha Ingreso</th>
                            <th>Botes Generados</th>
                            <th>Descargar QRs</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lotes as $lote)
                            <tr>
                                <td class="matricula">{{ $lote->matricula }}</td>
                                <td>{{ $lote->nombre }}</td>
                                <td>{{ $lote->lote }}</td>
                                <td>{{ number_format($lote->peso_total_kg, 2) }} kg</td>
                                <td>{{ $lote->numero_factura }}</td>
                                <td>{{ $lote->fecha_ingreso->format('d/m/Y') }}</td>
                                <td>
                                    @if($lote->botes->count() > 0)
                                        <span class="badge badge-success">{{ $lote->botes->count() }} botes</span>
                                    @else
                                        <span class="badge badge-warning">Sin botes</span>
                                    @endif
                                </td>
                                <td class="acciones">
                                    <div class="btn-acciones">
                                        <a href="{{ route('soldadura.regenerarQRLote.descargar', $lote->id) }}"
                                            class="btn btn-qr-lote" title="Descargar QR del Lote">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                                viewBox="0 0 16 16">
                                                <path
                                                    d="M0 .5A.5.5 0 0 1 .5 0h3a.5.5 0 0 1 0 1H1v2.5a.5.5 0 0 1-1 0v-3Zm12 0a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0V1h-2.5a.5.5 0 0 1-.5-.5ZM.5 12a.5.5 0 0 1 .5.5V15h2.5a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5v-3a.5.5 0 0 1 .5-.5Zm15 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1 0-1H15v-2.5a.5.5 0 0 1 .5-.5ZM4 4h1v1H4V4Z" />
                                                <path d="M7 2H2v5h5V2ZM3 3h3v3H3V3Zm2 8H4v1h1v-1Z" />
                                                <path d="M7 9H2v5h5V9Zm-4 1h3v3H3v-3Zm8-6h1v1h-1V4Z" />
                                                <path
                                                    d="M9 2h5v5H9V2Zm1 1v3h3V3h-3ZM8 8v2h1v1H8v1h2v-2h1v2h1v-1h1v-1h-3V8H8Zm2 2H9V9h1v1Zm4 2h-1v1h-2v1h3v-2Zm-4 2v-1H8v1h2Z" />
                                                <path d="M12 9h2V8h-2v1Z" />
                                            </svg>
                                            QR Lote
                                        </a>

                                        @if($lote->botes->count() > 0)
                                            <a href="{{ route('soldadura.regenerarQRIndividuales.descargar', $lote->id) }}"
                                                class="btn btn-qr-individuales" title="Descargar QRs de todos los botes">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                                    viewBox="0 0 16 16">
                                                    <path
                                                        d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z" />
                                                    <path
                                                        d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z" />
                                                </svg>
                                                QRs Botes ({{ $lote->botes->count() }})
                                            </a>
                                        @else
                                            <span class="btn btn-disabled" title="No hay botes generados">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                                    viewBox="0 0 16 16">
                                                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z" />
                                                    <path
                                                        d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z" />
                                                </svg>
                                                Sin botes
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <!-- <div class="footer-lista">
            <a href="{{ route('home') }}" class="btn btn-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path fill-rule="evenodd"
                        d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z" />
                </svg>
                Volver al Inicio
            </a>
        </div> -->
    </div>
@endsection