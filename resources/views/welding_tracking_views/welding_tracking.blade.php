@extends('layouts.appMenu')

@section('head')
    <title>Tracking de Soldadura</title>
    <link rel="icon" href="{{ url('images/lg_saavedra.png') }}?v=1">
    @vite(['resources/css/welding_tracking/trackingSoldadura.css', 'resources/js/welding_tracking/register_welding.js'])
@endsection

@section('background-body', 'background-image:url("' . asset("images/fondoLogin.jpg") . '")')

@section('content')
    <div class="wrapper">
        <img src="{{ asset('images/lg_saavedra.png') }}" class="lg-saavedra rounded-4" alt="" />
        <h2>Selecciona tu proceso a seguir</h2>

        <form action="{{ route('trackingSoldadura.store') }}" method="POST">
            @csrf
            @include('layouts.partials.messages')

            <input type="hidden" value="{{ auth()->user()->perfil }}" name="profile" />

            <div class="div-bttns">
                <button type="submit" name="accion" value="generar_lote" class="btn btn-primary">
                    Generar QR por Lote
                </button>

                <button type="submit" name="accion" value="generar_individual" class="btn btn-secondary">
                    Generar QRs Individuales (5Kg)
                </button>

                <button type="submit" name="accion" value="liberar_planta" class="btn btn-success">
                    Liberar QRs en Planta
                </button>
            </div>
        </form>
    </div>
@endsection