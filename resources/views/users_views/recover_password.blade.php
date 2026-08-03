@extends('layouts.appMenu')

@section('head')
<title>Recuperar Contraseña</title>
<link rel="icon" href="{{ url('images/lg_saavedra.png') }}?v=1">
@vite('resources/css/users_views/recover_password.css')
@endsection

@section('background-body', 'background-image:url("' . asset("images/fondoLogin.jpg") . '")')

@section('content')
<div class="container py-4">
    <div class="cascading-right">
        <img src="{{ asset('images/lg_saavedra.png') }}" class="lg-saavedra rounded-4" alt="" />
        <h2 class="sub-title">Recuperar contraseña</h2>
        <form action="{{route('recover')}}" method="post" class="form-container">
            @csrf
            @include('layouts.partials.messages')
            <div class="row">
                <div class="col-md-12 mb-2">
                    <div class="form-outline">
                        <input type="text" id="formMatricula" class="form-control" name="matricula" required />
                        <label class="form-label" for="formMatricula">Matrícula</label>
                    </div>
                </div>
                <div class="col-md-6 mb-2">
                    <div class="form-outline">
                        <div class="password-wrapper">
                            <input type="password" id="formPassword" class="form-control" name="nueva_contraseña" required minlength="8" maxlength="12" />
                            <span class="password-toggle-icon" onclick="togglePasswordVisibility('formPassword', this)">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16">
                                  <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/>
                                  <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/>
                                </svg>
                            </span>
                        </div>
                        <label class="form-label" for="formPassword">Nueva Contraseña</label>
                    </div>
                </div>
                <div class="col-md-6 mb-2">
                    <div class="form-outline">
                        <div class="password-wrapper">
                            <input type="password" id="formPasswordConfirmation" class="form-control" name="nueva_contraseña_confirmation" required minlength="8" maxlength="12" />
                            <span class="password-toggle-icon" onclick="togglePasswordVisibility('formPasswordConfirmation', this)">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16">
                                  <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/>
                                  <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/>
                                </svg>
                            </span>
                        </div>
                        <label class="form-label" for="formPasswordConfirmation">Confirmar Contraseña</label>
                    </div>
                </div>
            </div>

            <!-- Submit button -->
            <button type="submit" class="custom-btn">
                Restaurar contraseña
            </button>
        </form>
    </div>

    <img src="{{ asset('images/img-login.png') }}" class="img-saavedra" alt="" />
</div>

<script>
    function togglePasswordVisibility(inputId, toggleEl) {
        const input = document.getElementById(inputId);
        if (!input) return;
        
        if (input.type === 'password') {
            input.type = 'text';
            toggleEl.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-eye-slash" viewBox="0 0 16 16">
                  <path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a8.8 8.8 0 0 0-2.79.444l.746.893C6.883 3.654 7.425 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.154.22-.381.536-.684.896z"/>
                  <path d="M8.586 5.694A5 5 0 0 1 11.5 8.586l-2.914-2.914zm3.002 6.004L1.17 2.489a.5.5 0 0 0-.708.708l.747.747C1.16 4.195 0 8 0 8s3 5.5 8 5.5a8.74 8.74 0 0 0 5.258-1.743l.866.866a.5.5 0 0 0 .708-.708L11.588 11.7zM7.92 12.492A13 13 0 0 1 1.172 8c.196-.281.472-.605.802-.94l1.528 1.528A2.5 2.5 0 0 0 6.5 10.5c.345 0 .672-.07.97-.196l.45.45c-.347.168-.732.257-1.144.257z"/>
                  <path d="M13.657 2.235A2.23 2.23 0 1 1 9.9 5.98l.54-.54a1.23 1.23 0 1 0 1.674-1.674l.543-.543z"/>
                  <path d="M5.5 8a2.5 2.5 0 1 1 5 0 2.5 2.5 0 0 1-5 0"/>
                </svg>
            `;
        } else {
            input.type = 'password';
            toggleEl.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16">
                  <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/>
                  <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/>
                </svg>
            `;
        }
    }
</script>
@endsection