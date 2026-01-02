@extends('layouts.appMenu')

@section('head')
    <title>Registrar Soldadura</title>
    @vite([
        'resources/css/trackingSoldadura/registerSoldadura.css',
        'resources/js/TrackingSoldadura/registerSoldadura.js'
    ])
@endsection

@section('background-body', 'background-image:url("' . asset("images/fondoLogin.jpg") . '")')

@section('content')
    <div class="wrapper">
        <img src="{{ asset('images/lg_saavedra.png') }}" class="lg-saavedra rounded-4" alt="Logo Saavedra" />

        <h2>Registrar Soldadura</h2>

        {{-- Alertas del sistema --}}
        @include('layouts.partials.messages')

        <form method="POST" action="{{ route('storeRegistroSoldadura') }}">
            @csrf

            {{-- Fecha de ingreso --}}
            <div class="mb-3 text-start">
                <label for="fecha_ingreso" class="form-label">Fecha de ingreso:</label>
                <input type="date" id="fecha_ingreso" name="fecha_ingreso"
                    class="form-control @error('fecha_ingreso') is-invalid @enderror" required>
                @error('fecha_ingreso')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Nombre de la soldadura --}}
            <div class="mb-3 text-start">
                <label for="nombre" class="form-label">Nombre de la soldadura:</label>
                <input type="text" id="nombre" name="nombre" class="form-control @error('nombre') is-invalid @enderror"
                    required>
                @error('nombre')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Lote --}}
            <div class="mb-3 text-start">
                <label for="lote" class="form-label">Lote de la soldadura:</label>
                <input type="text" id="lote" name="lote" class="form-control @error('lote') is-invalid @enderror" required>
                @error('lote')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Kilos --}}
            <div class="mb-3 text-start">
                <label for="kilos" class="form-label">Kilos ingresados:</label>
                <input type="number" step="0.01" id="kilos" name="kilos"
                    class="form-control @error('kilos') is-invalid @enderror" required>
                @error('kilos')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="div-bttns d-flex justify-content-center">
                <button type="submit" id="btnGuardar" class="btn btn-primary" disabled>
                    Guardar
                </button>
            </div>
        </form>
    </div>
@endsection