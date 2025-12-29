@extends('layouts.appMenu')

@section('head')
    <title>Liberar Soldadura</title>
    @vite([
        'resources/css/trackingSoldadura/liberarSoldadura.css',
        'resources/js/TrackingSoldadura/liberarSoldadura.js'
    ])
@endsection

@section('background-body', 'background-image:url("' . asset('images/fondoLogin.jpg') . '")')

@section('content')
    <div class="wrapper">
        <img src="{{ asset('images/lg_saavedra.png') }}" class="lg-saavedra rounded-4" alt="Logo Saavedra" />

        <h2>Liberar Soldadura</h2>

        {{-- Mensajes del sistema --}}
        @include('layouts.partials.messages')

        <form method="POST" action="{{ route('soldadura.liberar.store') }}">
            @csrf

            {{-- Select de operadores generado por JS --}}
            <div class="mb-3 text-start operador-container">
                <label for="operador_id" class="form-label">Selecciona al operador</label>
            </div>

            {{-- Fecha de entrega --}}
            <div class="mb-3 text-start">
                <label for="fecha_entrega" class="form-label">Fecha de entrega al operador</label>
                <input type="date" id="fecha_entrega" name="fecha_entrega"
                    class="form-control @error('fecha_entrega') is-invalid @enderror" required>
                @error('fecha_entrega')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Select de soldaduras generado por JS --}}
            <div class="mb-3 text-start soldadura-container">
                <label for="soldadura_id" class="form-label">Selecciona el nombre y lote de la soldadura</label>
            </div>

            {{-- Cantidad --}}
            <div class="mb-3 text-start">
                <label for="cantidad" class="form-label">Cantidad entregada al operador (kg)</label>
                <input type="number" step="0.01" min="0" id="cantidad" name="cantidad"
                    class="form-control @error('cantidad') is-invalid @enderror" required>
                @error('cantidad')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="div-bttns d-flex justify-content-center">
                <button type="submit" id="btnGuardar" class="btn btn-primary" disabled>Guardar</button>
            </div>
        </form>
    </div>

    <script>
        // Pasar los datos al JS
        window.operadores = @json($operadores);
        window.soldaduras = @json($soldaduras);
    </script>
@endsection