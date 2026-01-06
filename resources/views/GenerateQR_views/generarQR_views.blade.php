@extends('layouts.appMenu')

@section('head')
    <title>Generar QR</title>
    @vite([
        'resources/css/generateQR_views/generarQR.css',
        'resources/js/generateQR_views/generarQR.js'
    ])
@endsection

@section('background-body', 'background-image:url("' . asset("images/fondoLogin.jpg") . '")')

@section('content')
    <div class="wrapper">
        <img src="{{ asset('images/lg_saavedra.png') }}" class="lg-saavedra rounded-4" alt="Logo Saavedra" />

        <h2>Generar QR</h2>

        {{-- Alertas del sistema --}}
        @include('layouts.partials.messages')

        <form method="POST" action="{{ route('generarQR.store') }}">
            @csrf

            {{-- ID del operador --}}
            <div class="mb-3 text-start">
                <label for="id_operador" class="form-label">ID del operador:</label>
                <select id="id_operador" name="id_operador" class="form-control @error('id_operador') is-invalid @enderror" required>
                    <option value="">Selecciona un operador</option>
                    @foreach($operadores as $operador)
                        <option value="{{ $operador->id }}">{{ $operador->matricula }} - {{ $operador->nombre }} {{ $operador->a_paterno }}</option>
                    @endforeach
                </select>
                @error('id_operador')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- ID de la soldadura --}}
            <div class="mb-3 text-start">
                <label for="id_soldadura" class="form-label">ID de la soldadura:</label>
                <select id="id_soldadura" name="id_soldadura" class="form-control @error('id_soldadura') is-invalid @enderror" required>
                    <option value="">Selecciona una soldadura</option>
                    @foreach($soldaduras as $soldadura)
                        <option value="{{ $soldadura->id }}">{{ $soldadura->id }} - {{ $soldadura->nombre }} (Lote: {{ $soldadura->lote }})</option>
                    @endforeach
                </select>
                @error('id_soldadura')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Fecha de entrega --}}
            <div class="mb-3 text-start">
                <label for="fecha_entrega" class="form-label">Fecha de entrega:</label>
                <input type="date" id="fecha_entrega" name="fecha_entrega"
                    class="form-control @error('fecha_entrega') is-invalid @enderror" 
                    value="{{ date('Y-m-d') }}" required>
                @error('fecha_entrega')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Cantidad entregada --}}
            <div class="mb-3 text-start">
                <label for="cantidad_entregada" class="form-label">Cantidad entregada (kg):</label>
                <input type="number" step="0.01" id="cantidad_entregada" name="cantidad_entregada"
                    class="form-control @error('cantidad_entregada') is-invalid @enderror" 
                    placeholder="0.00" required>
                @error('cantidad_entregada')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="div-bttns d-flex justify-content-center gap-2">
                <button type="submit" id="btnGenerar" class="btn btn-primary">
                    Generar QR
                </button>
                <button type="button" id="btnLimpiar" class="btn btn-secondary" onclick="limpiarFormulario()">
                    Limpiar
                </button>
            </div>
        </form>

        {{-- Mostrar QR generado --}}
        @if(isset($qrCode))
            <div class="mt-4 text-center">
                <h4>Código QR Generado:</h4>
                <div class="qr-container">
                    {!! $qrCode !!}
                </div>
                <div class="mt-3 d-flex justify-content-center gap-2">
                    <a href="{{ route('generarQR.download') }}" class="btn btn-success">
                        <i class="fas fa-download"></i> Descargar QR
                    </a>
                    <button type="button" class="btn btn-info" onclick="copiarTextoQR()">
                        <i class="fas fa-copy"></i> Copiar Texto
                    </button>
                </div>
                <div class="mt-2">
                    <small class="text-muted">Texto del QR: "{{ $texto_qr }}"</small>
                </div>
            </div>
        @endif
    </div>
@endsection