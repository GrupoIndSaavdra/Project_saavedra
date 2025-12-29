@extends('layouts.appMenu')

@section('head')
    <title>Liberar Soldadura</title>
    @vite(['resources/css/trackingSoldadura/liberarSoldadura.css', 'resources/js/TrackingSoldadura/liberarSoldadura.js'])
@endsection

@section('background-body', 'background-image:url("' . asset('images/fondoLogin.jpg') . '")')

@section('content')
    <div class="wrapper">
        <img src="{{ asset('images/lg_saavedra.png') }}" class="lg-saavedra rounded-4" alt="Logo Saavedra" />

        <h2>Liberar Soldadura</h2>

        <form method="POST" action="{{ route('soldadura.liberar.store') }}">
            @csrf

            {{-- Contenedor para que JS genere el select de operadores --}}
            <div class="mb-3 text-start operador-container">
                <label for="fecha_entrega" class="form-label">Selecciona al operador</label>
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

            {{-- Contenedor para que JS genere el select de soldaduras --}}
            <div class="mb-3 text-start soldadura-container">
                <label for="fecha_entrega" class="form-label">Selecciona el nombre y lote de la soldadura</label>
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
        window.operadores = @json($operadores);
        window.soldaduras = @json($soldaduras);
    </script>
@endsection