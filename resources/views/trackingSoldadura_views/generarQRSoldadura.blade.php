@extends('layouts.appMenu')

@section('head')
    <title>Generar QR Soldadura</title>

    @vite([
        'resources/css/trackingSoldadura/liberarSoldadura.css',
        'resources/js/TrackingSoldadura/generarQRSoldadura.js'
    ])
@endsection

@section('background-body', 'background-image:url("' . asset('images/fondoLogin.jpg') . '")')

@section('content')
    <div class="wrapper">
        <img src="{{ asset('images/lg_saavedra.png') }}" class="lg-saavedra rounded-4" alt="Logo Saavedra" />

        <h2>Generar QR Soldadura</h2>

        {{-- Mensajes del sistema --}}
        @include('layouts.partials.messages')

        <form method="POST" action="{{ route('soldadura.generarQRSoldadura.store') }}">
            @csrf

            {{-- Campos hidden --}}
            <input type="hidden" id="operador_id" name="operador_id" value="">
            <input type="hidden" id="soldadura_id" name="soldadura_id" value="">

            {{-- Operador --}}
            <div class="mb-3 text-start operador-container">
                <label for="operador_id_display" class="form-label">Selecciona al operador</label>
            </div>

            {{-- Fecha --}}
            <div class="mb-3 text-start">
                <label for="fecha_generacion" class="form-label">Fecha de generación del QR</label>
                <input type="date" id="fecha_generacion" name="fecha_generacion"
                    class="form-control @error('fecha_generacion') is-invalid @enderror" required>
                @error('fecha_generacion')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Soldadura --}}
            <div class="mb-3 text-start soldadura-container">
                <label for="soldadura_id" class="form-label">Selecciona el nombre y lote de la soldadura</label>
            </div>

            {{-- Cantidad entregada --}}
            <div class="mb-3 text-start">
                <label for="cantidad" class="form-label">Cantidad entregada al operador (kg)</label>
                <input type="number" step="0.01" min="0" id="cantidad" name="cantidad"
                    class="form-control @error('cantidad') is-invalid @enderror" required>
                @error('cantidad')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Botón --}}
            <div class="div-bttns d-flex gap-3 justify-content-center mt-4">
                <button type="submit" id="btnGenerar" class="btn btn-primary" disabled>Generar QRs</button>
            </div>
        </form>
    </div>

    {{-- Pasar datos de la BD al JS --}}
    <script>
        window.operadores = @json($operadores);
        window.soldaduras = @json($soldaduras);
        
        // Establecer fecha actual automáticamente
        document.addEventListener('DOMContentLoaded', function() {
            const fechaInput = document.getElementById('fecha_generacion');
            const today = new Date().toISOString().split('T')[0];
            fechaInput.value = today;
            
            // Manejar descarga automática si hay PDF
            @if(session('pdf_content') && session('pdf_filename'))
                const pdfContent = '{!! base64_encode(session('pdf_content')) !!}';
                const filename = '{{ session('pdf_filename') }}';
                
                const link = document.createElement('a');
                link.href = 'data:application/pdf;base64,' + pdfContent;
                link.download = filename;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            @endif
        });
    </script>
@endsection