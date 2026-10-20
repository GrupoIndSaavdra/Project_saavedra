@extends('layouts.appMenu')

@section('head')
    <title>Regenerar QRs</title>
    <link rel="icon" href="{{ url('images/lg_saavedra.png') }}?v=1">
    @vite(['resources/css/trackingSoldadura_views/regenerarQR.css'])
@endsection

@section('background-body', 'background-image:url("' . asset("images/fondoLogin.jpg") . '")')

@section('content')
    <div class="wrapper-verificacion">
        <div class="verificacion-card">
            <div class="report-header">
                <div class="title-row">
                    <img src="{{ asset('images/Iconos_Vistas/Regenerar_QR.png') }}" class="header-view-icon" alt="Regenerar QRs" />
                    <h1>Acceso a Regenerar QRs</h1>
                    <img src="{{ asset('images/Iconos_Vistas/Regenerar_QR.png') }}" class="header-view-icon" alt="Regenerar QRs" />
                </div>
                <p class="subtitle">Sección restringida para regeneración y descarga de códigos QR</p>
            </div>

            @include('layouts.partials.messages')

            <form action="{{ route('soldadura.regenerarQR.verificar') }}" method="POST" class="form-verificacion">
                @csrf
                <div class="input-group">
                    <label for="password_admin">Contraseña de Administrador</label>
                    <input type="password" name="password_admin" id="password_admin" class="form-control"
                        placeholder="Password Admin" required autofocus>
                </div>

                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                            viewBox="0 0 16 16">
                            <path
                                d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z" />
                        </svg>
                        Verificar y Acceder
                    </button>
                    <a href="{{ route('home') }}" class="btn btn-secondary">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection