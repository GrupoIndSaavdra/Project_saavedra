@extends('layouts.appMenu')

@section('head')
    <title>Liberar Soldadura</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @vite([
        'resources/css/trackingSoldadura/liberarSoldadura.css',
        'resources/js/TrackingSoldadura/liberarSoldadura.js'
    ])

    {{-- Librería para escaneo QR desde CDN --}}
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
@endsection

@section('background-body', 'background-image:url("' . asset('images/fondoLogin.jpg') . '")')

@section('content')
    <div class="wrapper">
        <img src="{{ asset('images/lg_saavedra.png') }}" class="lg-saavedra rounded-4" alt="Logo Saavedra" />

        <h2>Liberar Soldadura - Solo Escaneo</h2>

        {{-- Mensajes del sistema --}}
        @include('layouts.partials.messages')

        <form method="POST" action="{{ route('soldadura.liberar.store') }}">
            @csrf

            {{-- Campos hidden para asegurar que se envíen los valores --}}
            <input type="hidden" id="operador_id" name="operador_id" value="">
            <input type="hidden" id="soldadura_id" name="soldadura_id" value="">

            {{-- Operador --}}
            <div class="mb-3 text-start operador-container">
                <label for="operador_id_display" class="form-label">Información del operador (solo lectura)</label>
            </div>

            {{-- Fecha --}}
            <div class="mb-3 text-start">
                <label for="fecha_entrega" class="form-label">Fecha de entrega (solo lectura)</label>
                <input type="date" id="fecha_entrega" name="fecha_entrega"
                    class="form-control" readonly>
            </div>

            {{-- Soldadura --}}
            <div class="mb-3 text-start soldadura-container">
                <label for="soldadura_id" class="form-label">Información de la soldadura (solo lectura)</label>
            </div>

            {{-- Cantidad --}}
            <div class="mb-3 text-start">
                <label for="cantidad" class="form-label">Cantidad (solo lectura)</label>
                <input type="number" step="0.01" min="0" id="cantidad" name="cantidad"
                    class="form-control" readonly>
            </div>

            {{-- Estado del QR --}}
            <div class="mb-3 text-start">
                <label for="estado_qr" class="form-label">Estado del QR</label>
                <input type="text" id="estado_qr" name="estado_qr"
                    class="form-control" readonly placeholder="Escanea un QR para ver su estado">
            </div>

            {{-- Botón --}}
            <div class="div-bttns d-flex gap-3 justify-content-center mt-4">
                <button type="button" id="btnEscanear" class="btn btn-primary">Escanear QR para Liberar</button>
            </div>
        </form>
    </div>

    {{-- Modal QR --}}
    <div id="qrModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.8);
                                justify-content:center; align-items:center; z-index:9999;">
        <div style="background:#fff; padding:1rem; border-radius:12px; width:90%; max-width:360px;">
            <div id="reader" style="width:100%"></div>

            <button type="button" class="btn btn-danger w-100 mt-3" onclick="cerrarQR()">
                Cerrar
            </button>
        </div>
    </div>

    {{-- Pasar datos de la BD al JS --}}
    <script>
        window.operadores = @json($operadores);
        window.soldaduras = @json($soldaduras);
        
        // Establecer fecha actual automáticamente
        document.addEventListener('DOMContentLoaded', function() {
            const fechaInput = document.getElementById('fecha_entrega');
            const today = new Date().toISOString().split('T')[0];
            fechaInput.value = today;
        });
    </script>
@endsection