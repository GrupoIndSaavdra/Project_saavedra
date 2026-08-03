@extends('layouts.appMenu')
@section('head')
<title>Registrar usuario</title>
<link rel="icon" href="{{ url('images/lg_saavedra.png') }}?v=1">
@vite(['resources/css/users_views/create_user.css'])
@endsection

@section('background-body', 'background-image:url("' . asset("images/fondoLogin.jpg") . '")')

@section('content')
<div class="container py-4">
    <div class="cascading-right">
        <img src="{{ asset('images/lg_saavedra.png') }}" class="lg-saavedra rounded-4" alt="" />
        <h2 class="sub-title">Registrar usuario</h2>
        <form action="{{route('storeUser')}}" method="post" class="form-container">
            @csrf
            @include('layouts.partials.messages')
            <div class="row">
                <div class="col-md-12 mb-2">
                    <div class="form-outline">
                        <input type="text" id="form3Example1" class="form-control" name="nombre" required />
                        <label class="form-label" for="form3Example1">Nombre (s)</label>
                    </div>
                </div>
                <div class="col-md-6 mb-2">
                    <div class="form-outline">
                        <input type="text" id="form3Example2" class="form-control" name="a_paterno" required />
                        <label class="form-label" for="form3Example2">Apellido Paterno</label>
                    </div>
                </div>
                <div class="col-md-6 mb-2">
                    <div class="form-outline">
                        <input type="text" id="form3Example3" class="form-control" name="a_materno" required />
                        <label class="form-label" for="form3Example3">Apellido Materno</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <!-- Email input -->
                <div class="col-md-6 form-outline mb-2">
                    <input type="text" id="form3Example3" class="form-control" maxlength="7" minlength="4" name="matricula" required />
                    <label class="form-label" for="form3Example3">Matrícula</label>
                </div>

                <!-- Password input -->
                <div class="col-md-6 form-outline mb-2">
                    <input type="password" id="form3Example4" class="form-control" maxlength="12" minlength="8" name="contrasena" required />
                    <label class="form-label" for="form3Example4">Contraseña</label>
                </div>
            </div>

            <div class="form-outline">
                <select class="form-select" id="floatingSelect" name="perfil">
                    <option value="1">Administrador</option>
                    <option value="2">Operador</option>
                    <option value="3">Maestro</option>
                    <option value="4">Calidad</option>
                    <option value="5">Almacen</option>
                </select>
                <label class="form-label">Tipo de usuario</label>
            </div>

            <!-- Submit button -->
            <button type="submit" class="custom-btn">
                Registrar
            </button>
        </form>
    </div>

    <img src="{{ asset('images/img-login.png') }}" class="img-saavedra" alt="" />
</div>
@endsection