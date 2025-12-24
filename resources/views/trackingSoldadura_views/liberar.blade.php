@extends('layouts.appMenu')

@section('head')
<title>Liberar Soldadura</title>
@vite(['resources/css/trackingSoldadura/liberarSoldadura.css', 'resources/js/trackingSoldadura/LiberarSoldadura.js'])
@endsection

@section('background-body', 'background-image:url("' . asset("images/fondoLogin.jpg") . '")')

@section('content')
<div class="wrapper">
    <img src="{{ asset('images/lg_saavedra.png') }}" class="lg-saavedra rounded-4" alt="Logo Saavedra" />

    <h2>Liberar Soldadura</h2>

    <form method="POST" action="#">
        @csrf

        {{-- Seleccionar Operador --}}
        <div class="mb-3 text-start">
            <label for="operador_id" class="form-label">Selecciona Operador</label>
            <select id="operador_id"
                    name="operador_id"
                    class="form-select @error('operador_id') is-invalid @enderror"
                    required>
                <option value="">Seleccione un operador</option>

            </select>
            @error('operador_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Fecha de entrega --}}
        <div class="mb-3 text-start">
            <label for="fecha_entrega" class="form-label">Fecha de entrega al operador</label>
            <input type="date"
                   id="fecha_entrega"
                   name="fecha_entrega"
                   class="form-control @error('fecha_entrega') is-invalid @enderror"
                   required>
            @error('fecha_entrega')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Soldadura (nombre + lote) --}}
        <div class="mb-3 text-start">
            <label for="soldadura_id" class="form-label">Nombre y lote de la soldadura</label>
            <select id="soldadura_id"
                    name="soldadura_id"
                    class="form-select @error('soldadura_id') is-invalid @enderror"
                    required>
                <option value="">Seleccione una soldadura</option>

            </select>
            @error('soldadura_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Cantidad --}}
        <div class="mb-3 text-start">
            <label for="cantidad" class="form-label">Cantidad entregada al operador (kg)</label>
            <input type="number"
                   step="0.01"
                   min="0"
                   id="cantidad"
                   name="cantidad"
                   class="form-control @error('cantidad') is-invalid @enderror"
                   required>
            @error('cantidad')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="div-bttns d-flex justify-content-center">
            <button type="submit" class="btn btn-primary">
                Guardar
            </button>
        </div>
    </form>
</div>
@endsection
