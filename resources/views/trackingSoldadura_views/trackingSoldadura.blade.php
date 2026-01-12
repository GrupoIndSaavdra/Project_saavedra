@extends('layouts.appMenu')

@section('head')
    <title>Registro de soldadura</title>
    @vite(['resources/css/trackingSoldadura/trackingSoldadura.css', 'resources/js/TrackingSoldadura/registerSoldadura.js'])

@endsection

@section('background-body', 'background-image:url("' . asset("images/fondoLogin.jpg") . '")')

@section('content')
    <!--Formulario para entregar o registrar soldadura-->
    <div class="wrapper">
        <img src="{{ asset('images/lg_saavedra.png') }}" class="lg-saavedra rounded-4" alt="" />
        <h2>Selecciona tu proceso a seguir</h2>

        <form action="#" method="POST">
            @csrf
            @include('layouts.partials.messages')

            <input type="hidden" value="{{ auth()->user()->perfil }}" name="profile" />

            <!-- CAMPOS BÁSICOS -->
            <!-- <div class="mb-3">
                    <label class="form-label">Orden de trabajo</label>
                    <input type="text" name="orden_trabajo" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Pieza</label>
                    <input type="text" name="pieza" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Observaciones</label>
                    <textarea name="observaciones" class="form-control"></textarea>
                </div> -->

            <!-- BOTONES -->
            <div class="div-bttns">
                <button type="submit" name="accion" value="registrar" class="btn btn-primary">
                    Registrar y Generar QR por Lote
                </button>

                <button type="submit" name="accion" value="generar" class="btn btn-secondary">
                    Generar QR por Bote (5Kg)
                </button>

                <button type="submit" name="accion" value="liberar" class="btn btn-success">
                    Liberar QR de Botes
                </button>
            </div>
        </form>
    </div>
    <script>

    </script>
@endsection