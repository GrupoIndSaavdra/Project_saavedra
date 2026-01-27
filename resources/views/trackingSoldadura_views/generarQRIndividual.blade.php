@extends('layouts.appMenu')

@section('head')
    <title>QRs Botes</title>
    @vite(['resources/css/trackingSoldadura_views/generarQRIndividual.css'])
@endsection

@section('background-body', 'background-image:url("' . asset("images/fondoLogin.jpg") . '")')

@section('content')
    <div class="wrapper">
        <img src="{{ asset('images/lg_saavedra.png') }}" class="lg-saavedra rounded-4" alt="" />
        <h2>QRs de Botes</h2>

        <form action="{{ route('soldadura.generarQRIndividual.store') }}" method="POST">
            @csrf
            @include('layouts.partials.messages')

            <div class="mb-3">
                <label class="form-label">Selecciona el Lote</label>
                <select name="lote_id" class="form-control" required>
                    <option value="">Seleccione un lote...</option>
                    @foreach($lotes as $lote)
                        <option value="{{ $lote->id }}">
                            {{ $lote->nombre }} - {{ $lote->lote }} - Factura: {{ $lote->numero_factura }}
                            ({{ $lote->peso_total_kg }}kg - {{ $lote->cantidadBotesEsperados() }} botes)
                        </option>
                    @endforeach
                </select>
            </div>

            @if($lotes->count() > 0)
                <div class="div-bttns">
                    <button type="submit" class="btn btn-primary">
                        Generar QRs Individuales
                    </button>
                    <a href="{{ route('home') }}" class="btn btn-secondary">
                        Regresar
                    </a>
                </div>
            @else
                <div class="alert alert-warning">
                    No hay lotes disponibles para procesar. Primero debe generar un QR por lote.
                </div>
                <div class="div-bttns">
                    <a href="{{ route('soldadura.generarQRLote') }}" class="btn btn-primary">
                        Generar QR por Lote
                    </a>
                    <a href="{{ route('home') }}" class="btn btn-secondary">
                        Regresar
                    </a>
                </div>
            @endif
        </form>
    </div>
@endsection