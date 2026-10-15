@extends('layouts.appMenu')

@section('head')
<title>Recuperar Contraseña | GIS</title>
<link rel="icon" type="image/png" href="{{ asset('images/lg_saavedra.png') }}">
@vite(['resources/css/users_views/recoverPassword.css', 'resources/js/layouts/partials/messages.js'])
@endsection

@section('background-body', 'background-image:url("' . asset("images/fondoLogin.jpg") . '")')

@section('content')
<div class="recover-viewport">
    <div class="recover-card">
        
        {{-- Cabecera con Logo e Icono de Candado --}}
        <div class="recover-header">
            <a href="{{ route('home') }}" class="logo-link" title="Volver al inicio">
                <img src="{{ asset('images/lg_saavedra.png') }}" class="recover-logo" alt="Grupo Industrial Saavedra" />
            </a>

            <div class="security-badge-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    <circle cx="12" cy="16" r="1"></circle>
                </svg>
            </div>

            <h1 class="recover-heading">Restablecer Contraseña</h1>
            <p class="recover-subheading">Ingresa tu matrícula institucional y define tu nueva clave de acceso.</p>
        </div>

        {{-- Formulario --}}
        <form action="{{ route('recover') }}" method="post" class="recover-form-body">
            @csrf
            @include('layouts.partials.messages')

            {{-- Campo: Matrícula --}}
            <div class="input-group-custom">
                <label for="formMatricula" class="field-label">Matrícula</label>
                <div class="field-wrapper">
                    <span class="field-prefix-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </span>
                    <input 
                        type="text" 
                        id="formMatricula" 
                        name="matricula" 
                        class="form-input" 
                        placeholder="Ej. 1321124015" 
                        autocomplete="username"
                        required 
                    />
                </div>
            </div>

            {{-- Campo: Nueva Contraseña --}}
            <div class="input-group-custom">
                <label for="formPassword" class="field-label">Nueva Contraseña</label>
                <div class="field-wrapper">
                    <span class="field-prefix-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                    </span>
                    <input 
                        type="password" 
                        id="formPassword" 
                        name="nueva_contraseña" 
                        class="form-input has-toggle" 
                        placeholder="Mínimo 8 caracteres" 
                        minlength="8" 
                        maxlength="12" 
                        required 
                    />
                    <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('formPassword', this)" title="Ver / Ocultar contraseña">
                        <svg class="eye-open" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                </div>
                <span class="field-hint">Entre 8 y 12 caracteres</span>
            </div>

            {{-- Campo: Confirmar Contraseña --}}
            <div class="input-group-custom">
                <label for="formPasswordConfirmation" class="field-label">Confirmar Contraseña</label>
                <div class="field-wrapper">
                    <span class="field-prefix-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                    </span>
                    <input 
                        type="password" 
                        id="formPasswordConfirmation" 
                        name="nueva_contraseña_confirmation" 
                        class="form-input has-toggle" 
                        placeholder="Repite la contraseña" 
                        minlength="8" 
                        maxlength="12" 
                        required 
                    />
                    <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('formPasswordConfirmation', this)" title="Ver / Ocultar contraseña">
                        <svg class="eye-open" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Botón de Acción --}}
            <button type="submit" class="submit-action-btn">
                <span>Actualizar Contraseña</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                </svg>
            </button>

            {{-- Enlaces secundarios --}}
            <div class="recover-footer">
                @auth
                    <a href="{{ route('home') }}" class="back-link">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                        Volver al Menú Principal
                    </a>
                @else
                    <a href="{{ route('login') }}" class="back-link">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                        Regresar a Iniciar Sesión
                    </a>
                @endauth
            </div>
        </form>

    </div>
</div>

<script>
    function togglePasswordVisibility(inputId, btnEl) {
        const input = document.getElementById(inputId);
        if (!input) return;

        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';

        if (isPassword) {
            btnEl.classList.add('active');
            btnEl.innerHTML = `
                <svg class="eye-closed" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                    <line x1="1" y1="1" x2="23" y2="23"></line>
                </svg>
            `;
        } else {
            btnEl.classList.remove('active');
            btnEl.innerHTML = `
                <svg class="eye-open" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                </svg>
            `;
        }
    }
</script>
@endsection