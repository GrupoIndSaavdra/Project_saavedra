<!doctype html>
<html lang="en">

<head>
    <title>Login</title>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" href="{{ url('images/lg_saavedra.png') }}?v=1">
    <link rel="icon" type="image/png" href="{{ asset('images/lg_saavedra.png') }}">

    <!-- Bootstrap CSS v5.2.1 -->
    <link rel="stylesheet" href="{{asset('bootstrap/css/bootstrap.min.css')}}">
    <!-- Link estilos de CSS -->
    @vite(['resources/css/auth/login.css', 'resources/js/layouts/partials/messages.js'])

    <script>
        window.baseUrl = "{{ url('/') }}";
    </script>
</head>

<body background="{{ asset('images/fondoLogin.jpg') }}">
    <!-- Section: Design Block -->
    <section class="text-center text-lg-start">
        <!-- Jumbotron -->
        <div class="container py-4">
            <div class="row g-0 align-items-center">
                <div class="col-lg-6 mb-lg-0 login-img-col">
                    <img src="{{ asset('images/img-login.png') }}" class="login-img" alt="Industrial Saavedra" />
                </div>

                <div class="col-lg-6 mb-5 mb-lg-0">
                    <div class="card cascading-right">
                        <div class="card-body p-5 shadow-5 text-center">
                            <img src="{{ asset('images/lg_saavedra.png') }}" class="lg-saavedra rounded-4" alt="" />
                            <h2 class="fw-bold mb-5">INICIAR SESIÓN</h2>
                            <form action="{{route('loginUser')}}" method="POST">
                                @csrf
                                @include('layouts.partials.messages')
                                <div class="row">
                                    <!-- Email input -->
                                    <div class="col-md-12 form-outline mb-4">
                                        <input type="text" id="form3Example3" class="form-control" maxlength="7" minlength="4" name="matricula" required />
                                        <label class="form-label" for="form3Example3">Matricula</label>
                                    </div>
                                    <!-- Password input -->
                                     <div class="col-md-12 form-outline mb-4 password-field-group">
                                         <div class="input-eye-wrapper">
                                             <input type="password" id="form3Example4" class="form-control" maxlength="12" minlength="8" name="contrasena" required />
                                             <button type="button" class="eye-toggle" id="togglePassword" title="Mostrar contraseña">
                                                 <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                     <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                                                 </svg>
                                             </button>
                                         </div>
                                         <label class="form-label" for="form3Example4">Contraseña</label>
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
            </div>
        </div>
        </div>
        <!-- Jumbotron -->
    </section>
    <!-- Section: Design Block -->

    <!-- Eye toggle script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var btn = document.getElementById('togglePassword');
            if (!btn) return;
            btn.addEventListener('click', function () {
                var input = document.getElementById('form3Example4');
                var icon = document.getElementById('eye-icon');
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';
                    btn.classList.add('active');
                } else {
                    input.type = 'password';
                    icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
                    btn.classList.remove('active');
                }
            });
        });
    </script>

    <!-- Bootstrap JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous">
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/js/bootstrap.min.js" integrity="sha384-7VPbUDkoPSGFnVtYi0QogXtr74QeVeeIs99Qfg5YCF+TidwNdjvaKZX19NZ/e6oz" crossorigin="anonymous">
    </script>
</body>

</html>