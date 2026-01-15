@extends('layouts.appMenu')

@section('head')
    <title>Recepción de Soldadura en Planta</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/trackingSoldadura_views/recepcionPlanta.css'])
@endsection

@section('background-body', 'background-image:url("' . asset("images/fondoLogin.jpg") . '")')

@section('content')
    <div class="wrapper">
        <img src="{{ asset('images/lg_saavedra.png') }}" class="lg-saavedra rounded-4" alt="" />
        <h2>Recepción de Soldadura en Planta</h2>

        {{-- Contador de botes en planta --}}
        <div class="info-badge">
            <span class="badge-text">Botes actualmente en planta: <strong>{{ $botesEnPlanta ?? 0 }}</strong></span>
        </div>

        @if(!isset($bote))
            @include('layouts.partials.messages')

            <form action="{{ route('soldadura.recepcionPlanta.escanear') }}" method="POST" id="qrForm">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Escanear QR del Bote</label>
                    <input type="text" name="qr_content" id="qr_content" class="form-control"
                        placeholder="Escanee el código QR con el lector..." autofocus required>
                    <small class="form-text text-muted">
                        Coloque el cursor en este campo y escanee el código QR con el dispositivo.
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
        @else
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h4 class="mb-0"> Información del Bote a Recibir</h4>
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
                            <input type="text" class="form-control readonly-field estado-transito"
                                value="{{ strtoupper($bote->estado) }}" readonly>
                        </div>
                    </div>
                </div>
            </div>

            <form action="{{ route('soldadura.recepcionPlanta.confirmar') }}" method="POST">
                @csrf
                <input type="hidden" name="bote_id" value="{{ $bote->id }}">

                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Datos del Receptor (Almacén)</h4>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <select name="recibido_por" class="form-control" required>
                                <option value="">Seleccione quien recibe el bote...</option>
                                @foreach($almacenistas as $almacenista)
                                    <option value="{{ $almacenista->id }}">
                                        {{ $almacenista->name }} ({{ $almacenista->matricula }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">
                                Solo personal de almacén puede registrar la recepción.
                            </small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Observaciones (Opcional)</label>
                            <textarea name="observaciones" class="form-control" rows="2"
                                placeholder="Ingrese observaciones sobre el estado del bote..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="div-bttns">
                    <button type="submit" class="btn btn-success">
                        Confirmar Recepción
                    </button>
                    <a href="{{ route('soldadura.recepcionPlanta') }}" class="btn btn-secondary">
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