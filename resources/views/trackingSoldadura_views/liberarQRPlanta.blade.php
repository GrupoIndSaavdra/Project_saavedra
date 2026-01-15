@extends('layouts.appMenu')

@section('head')
    <title>Liberar Soldadura a Operadores</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/trackingSoldadura_views/liberarQRPlanta.css'])
@endsection

@section('background-body', 'background-image:url("' . asset("images/fondoLogin.jpg") . '")')

@section('content')
    <div class="wrapper">
        <img src="{{ asset('images/lg_saavedra.png') }}" class="lg-saavedra rounded-4" alt="" />
        <h2>Liberar Soldadura a Operadores</h2>
        <p class="subtitle">Entrega de botes de soldadura a operadores de planta</p>

        {{-- Contador de botes disponibles --}}
        <div class="info-badge {{ ($botesEnPlanta ?? 0) > 0 ? 'badge-success' : 'badge-warning' }}">
            <span class="badge-text">Botes disponibles en planta: <strong>{{ $botesEnPlanta ?? 0 }}</strong></span>
        </div>

        @if(!isset($bote))
            @include('layouts.partials.messages')

            @if(($botesEnPlanta ?? 0) == 0)
                <div class="alert alert-warning">
                    <strong>⚠️ Sin botes disponibles ⚠️</strong>
                    <p>No hay botes de soldadura disponibles en planta para liberar. Primero debe registrar la recepción de botes.
                    </p>
                    <a href="{{ route('soldadura.recepcionPlanta') }}" class="btn btn-info btn-sm">
                        Ir a Recepción de Soldadura
                    </a>
                </div>
            @else
                <form action="{{ route('soldadura.liberarQRPlanta.escanear') }}" method="POST" id="qrForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Escanear QR del Bote</label>
                        <input type="text" name="qr_content" id="qr_content" class="form-control"
                            placeholder="Escanee el código QR con el lector..." autofocus required>
                        <small class="form-text text-muted">
                            Coloque el cursor en este campo y escanee el código QR con el dispositivo lector.
                        </small>
                    </div>

                    <div class="div-bttns">
                        <button type="submit" class="btn btn-primary">
                            Buscar Bote
                        </button>
                        <a href="{{ route('home') }}" class="btn btn-secondary">
                            Regresar
                        </a>
                    </div>
                </form>

                {{-- Lista de botes disponibles --}}
                @if(isset($botesDisponibles) && $botesDisponibles->count() > 0)
                    <div class="card mt-4">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0">Botes Disponibles para Liberar</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>Matrícula</th>
                                            <th>Soldadura</th>
                                            <th>Lote</th>
                                            <th>Peso</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($botesDisponibles as $boteDisp)
                                            <tr>
                                                <td><code>{{ $boteDisp->matricula }}</code></td>
                                                <td>{{ $boteDisp->nombre_soldadura }}</td>
                                                <td>{{ $boteDisp->numero_lote }}</td>
                                                <td>{{ $boteDisp->peso_kg }} kg</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
            @endif
        @else
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">Información del Bote a Liberar</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Matrícula del Bote</label>
                            <input type="text" class="form-control readonly-field" value="{{ $bote->matricula }}" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Tipo de Soldadura</label>
                            <input type="text" class="form-control readonly-field" value="{{ $bote->nombre_soldadura }}"
                                readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Número de Lote</label>
                            <input type="text" class="form-control readonly-field" value="{{ $bote->numero_lote }}" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Peso del Bote</label>
                            <input type="text" class="form-control readonly-field" value="{{ $bote->peso_kg }} kg" readonly>
                        </div>
                        {{-- NO se muestra el número de factura por seguridad --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Número de Bote</label>
                            <input type="text" class="form-control readonly-field" value="#{{ $bote->numero_bote }}" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Estado Actual</label>
                            <input type="text" class="form-control readonly-field estado-planta"
                                value="{{ strtoupper($bote->estado) }}" readonly>
                        </div>
                    </div>
                </div>
            </div>

            <form action="{{ route('soldadura.liberarQRPlanta.liberar') }}" method="POST">
                @csrf
                <input type="hidden" name="bote_id" value="{{ $bote->id }}">

                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Datos del Receptor (personal de planta)</h4>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <select name="operador_id" class="form-control" required>
                                <option value="">Seleccione el operador que recibirá el bote...</option>
                                @foreach($operadores as $operador)
                                    <option value="{{ $operador->id }}">
                                        {{ $operador->name }} ({{ $operador->matricula }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">
                                Solo soldadores de planta pueden recibir soldadura.
                            </small>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h4 class="mb-0">Datos del Remitente (almacén)</h4>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <select name="liberador_id" class="form-control" required>
                                <option value="">Seleccione quien entrega el bote...</option>
                                @foreach($almacenistas as $almacenista)
                                    <option value="{{ $almacenista->id }}">
                                        {{ $almacenista->name }} ({{ $almacenista->matricula }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">
                                Solo personal de almacén puede liberar soldadura.
                            </small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Observaciones (Opcional)</label>
                            <textarea name="observaciones" class="form-control" rows="2"
                                placeholder="Ingrese observaciones sobre la entrega..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="div-bttns">
                    <button type="submit" class="btn btn-success">
                        Liberar Bote al Operador
                    </button>
                    <a href="{{ route('soldadura.liberarQRPlanta') }}" class="btn btn-secondary">
                        Escanear Otro QR
                    </a>
                </div>
            </form>
        @endif
    </div>

    <script>
        // Auto-submit cuando el lector de QR termina de escanear (detecta Enter)
        document.getElementById('qr_content')?.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                if (this.value.trim() !== '') {
                    document.getElementById('qrForm').submit();
                }
            }
        });

        // Mantener el foco en el campo de escaneo
        document.getElementById('qr_content')?.focus();
    </script>
@endsection