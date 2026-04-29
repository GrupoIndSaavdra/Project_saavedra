@extends('layouts.appMenu')

@section('head')
<title>Recuperar Contraseña</title>
<link rel="icon" href="{{ url('images/lg_saavedra.png') }}?v=1">
@vite('resources/css/users_views/recoverPassword.css')
@endsection

@section('background-body', 'background-image:url("' . asset("images/fondoLogin.jpg") . '")')

@section('content')
<div class="wrapper">
    <img src="{{ asset('images/lg_saavedra.png') }}" class="lg-saavedra" alt="" />
    <h2>Recupera tu cuenta</h2>
    <form action="{{route('recover')}}" method="post" class="pt-3">
        @csrf
        @include('layouts.partials.messages')
        <div class="form-group py-2">
            <input name="matricula" type="text" placeholder="Ingresa tu matricula" required class="input-field">
            <input name="nueva_contraseña" type="password" placeholder="Ingresa tu nueva contraseña" required maxlength="12" min="8" class="input-field">
            <input name="nueva_contraseña_confirmation" type="password" placeholder="Confirma tu nueva contraseña" required maxlength="12" minlength="8" class="input-field">
        </div>
        <button class="btn btn-block text-center my-3">Restaurar contraseña</button>
    </form>
</div>
@endsection