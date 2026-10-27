@extends('layouts.appMenu')

@section('head')
    <title>QR por Lote</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('images/logos/favicon.ico') }}?v=3">
    @vite(['resources/css/trackingSoldadura_views/generarQRLote.css'])
@endsection

@section('background-body', 'background-image:url("' . asset("images/fondos/fondoLogin.jpg") . '")')

@section('content')
    <div class="wrapper">
        <div class="report-header">
            <div class="title-row">
                <img src="{{ asset('images/Color_Icons/Vistas/Generar_QR_Lote.png') }}" class="header-view-icon" alt="Generar QR por Lote" />
                <h1>Generación de QR por Lote</h1>
                <img src="{{ asset('images/Color_Icons/Vistas/Generar_QR_Lote.png') }}" class="header-view-icon" alt="Generar QR por Lote" />
            </div>
            <p class="subtitle">Creación y registro de código QR para lotes de soldadura</p>
        </div>

        <form action="{{ route('soldadura.generarQRLote.store') }}" method="POST">
            @csrf
            @include('layouts.partials.messages')

            <div class="mb-3">
                <label class="form-label">Nombre de la Soldadura</label>
                <input type="text" name="nombre" class="form-control" value="{{ old('nombre') }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Lote</label>
                <input type="text" name="lote" class="form-control" value="{{ old('lote') }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Peso Total (kg)</label>
                <input type="number" step="0.01" min="5" name="peso_total" class="form-control"
                    value="{{ old('peso_total') }}" required>
                <small class="form-text text-muted">Mínimo 5 kg para generar al menos un bote</small>
            </div>

            <div class="mb-3">
                <label class="form-label">Número de Factura</label>
                <input type="text" name="numero_factura" class="form-control" value="{{ old('numero_factura') }}" required>
            </div>

            <div class="div-bttns">
                <button type="submit" class="btn btn-primary">
                    Generar QR del Lote
                </button>
                <a href="{{ route('home') }}" class="btn btn-secondary">
                    Regresar
                </a>
            </div>
        </form>
    </div>
@endsection