@extends('layouts.appMenu')

@section('head')
    <title>Regenerar QRs</title>
    @vite(['resources/css/trackingSoldadura_views/regenerarQR.css'])
@endsection

@section('background-body', 'background-image:url("' . asset("images/fondoLogin.jpg") . '")')

@section('content')
    <div class="wrapper-verificacion">
        <div class="verificacion-card">
            <div class="icon-lock">
                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" viewBox="0 0 16 16">
                    <path
                        d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2zM5 8h6a1 1 0 0 1 1 1v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V9a1 1 0 0 1 1-1z" />
                </svg>
            </div>

            <h2>Acceso Restringido</h2>
            <p class="descripcion">
                Esta sección permite regenerar QRs de soldadura perdidos o eliminados.
            </p>

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