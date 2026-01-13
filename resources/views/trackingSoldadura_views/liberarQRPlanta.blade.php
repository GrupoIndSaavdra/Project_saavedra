@extends('layouts.appMenu')

@section('head')
    <title>Liberar QRs en Planta</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/trackingSoldadura_views/liberarQRPlanta.css'])
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
@endsection

@section('background-body', 'background-image:url("' . asset("images/fondoLogin.jpg") . '")')

@section('content')
    <div class="wrapper">
        <img src="{{ asset('images/lg_saavedra.png') }}" class="lg-saavedra rounded-4" alt="" />
        <h2>Liberar QRs en Planta</h2>

        @if(!isset($bote))
            @include('layouts.partials.messages')
            
            <div class="mb-3">
                <label class="form-label">Escanear QR del Bote</label>
                <div class="div-bttns">
                    <button type="button" id="btnEscanear" class="btn btn-primary">
                        Escanear QR
                    </button>
                </div>
            </div>

            <form action="{{ route('soldadura.liberarQRPlanta.escanear') }}" method="POST" id="qrForm">
                @csrf
                <input type="hidden" name="qr_content" id="qr_content">
            </form>

            <div class="div-bttns">
                <a href="{{ route('home') }}" class="btn btn-secondary">
                    Regresar
                </a>
            </div>
        @else
            <div class="alert-success">
                <h4>Bote Encontrado</h4>
                <p><strong>ID Único:</strong> {{ $bote->id_unico }}</p>
                <p><strong>Soldadura:</strong> {{ $bote->nombre }}</p>
                <p><strong>Lote:</strong> {{ $bote->lote }}</p>
                <p><strong>Peso:</strong> {{ $bote->peso }}kg</p>
                <p><strong>Factura:</strong> {{ $bote->numero_factura }}</p>
                <p><strong>Número de Bote:</strong> {{ $bote->numero_bote }}</p>
            </div>

            <form action="{{ route('soldadura.liberarQRPlanta.liberar') }}" method="POST">
                @csrf
                <input type="hidden" name="bote_id" value="{{ $bote->id }}">

                <div class="mb-3">
                    <label class="form-label">Seleccionar Operador</label>
                    <select name="id_operador" class="form-control" required>
                        <option value="">Seleccione un operador...</option>
                        @foreach($operadores as $operador)
                            <option value="{{ $operador->id }}">
                                {{ $operador->name }} ({{ $operador->matricula }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Liberado por (Almacén)</label>
                    <select name="id_liberador" class="form-control" required>
                        <option value="">Seleccione quien libera...</option>
                        @foreach($almacenistas as $almacenista)
                            <option value="{{ $almacenista->id }}">
                                {{ $almacenista->name }} ({{ $almacenista->matricula }})
                            </option>
                        @endforeach
                    </select>
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

    <!-- Modal QR -->
    <div id="qrModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.8);
                                justify-content:center; align-items:center; z-index:9999;">
        <div style="background:#fff; padding:1rem; border-radius:12px; width:90%; max-width:360px;">
            <div id="reader" style="width:100%"></div>
            <button type="button" class="btn btn-danger w-100 mt-3" onclick="cerrarQR()">
                Cerrar
            </button>
        </div>
    </div>

    <script>
        let html5QrcodeScanner;

        document.getElementById('btnEscanear').addEventListener('click', function() {
            document.getElementById('qrModal').style.display = 'flex';
            
            html5QrcodeScanner = new Html5QrcodeScanner(
                "reader", 
                { fps: 10, qrbox: 250 }
            );
            
            html5QrcodeScanner.render(onScanSuccess, onScanError);
        });

        function onScanSuccess(decodedText, decodedResult) {
            document.getElementById('qr_content').value = decodedText;
            cerrarQR();
            document.getElementById('qrForm').submit();
        }

        function onScanError(errorMessage) {
            // Silenciar errores de escaneo
        }

        function cerrarQR() {
            if (html5QrcodeScanner) {
                html5QrcodeScanner.clear();
            }
            document.getElementById('qrModal').style.display = 'none';
        }
    </script>
@endsection