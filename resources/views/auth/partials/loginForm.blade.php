{{-- Componente Formulario de Inicio de Sesión --}}
<div class="mb-5 col-lg-6 mb-lg-0">
    <div class="card cascading-right">
        <div class="p-5 text-center card-body shadow-5">
            <img src="{{ asset('images/logos/lg_saavedra.png') }}" class="lg-saavedra rounded-4" alt="GIS" />
            <h2 class="fw-bold login-title">Bienvenido</h2>
            <p class="login-subtitle">Ingresa tus credenciales para continuar</p>
            <span class="badge mb-3 px-3 py-1 rounded-pill" style="font-size: 0.75rem; color: #033966; background-color: rgba(3, 57, 102, 0.08); border: 1px solid rgba(3, 57, 102, 0.2);">Sistema GIS v2.0 • Server</span>
            <form action="{{ route('loginUser') }}" method="POST">
                @csrf
                @include('layouts.partials.messages')
                <div class="row">
                    <!-- Email / Matricula input -->
                    <div class="mb-3 col-md-12 form-outline">
                        <input type="text" id="form3Example3" class="form-control" maxlength="7" minlength="4"
                            name="matricula" placeholder="Ingresa tu Matrícula" required />
                        <label class="form-label visually-hidden" for="form3Example3">Matrícula</label>
                    </div>
                    <!-- Password input -->
                    <div class="mb-3 col-md-12 form-outline password-field-group">
                        <div class="input-eye-wrapper">
                            <input type="password" id="form3Example4" class="form-control" maxlength="12" minlength="8"
                                name="contrasena" placeholder="Ingresa tu Contraseña" required />
                            <button type="button" class="eye-toggle" id="togglePassword" title="Mostrar contraseña">
                                <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                    <circle cx="12" cy="12" r="3" />
                                </svg>
                            </button>
                        </div>
                        <label class="form-label visually-hidden" for="form3Example4">Contraseña</label>
                    </div>
                </div>
                <!-- Submit button -->
                <button type="submit" class="custom-btn">
                    Iniciar Sesión
                </button>
            </form>
        </div>
    </div>
</div>
