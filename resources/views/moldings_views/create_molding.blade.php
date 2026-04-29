@extends('layouts.appMenu')

@section('head')
<title>Registrar molduras</title>
@vite(['resources/css/moldings_views/create_molding.css'])
<link rel="icon" href="{{ url('images/lg_saavedra.png') }}?v=1">
@endsection

@section('background-body', 'background-image:url("' . asset("images/fondoLogin.jpg") . '")') <!--Body background Image-->
@section('content')
<div class="wrapper">
    <img src="{{ asset('images/lg_saavedra.png') }}" class="lg-saavedra rounded-4" alt="" />
    <h2>Registrar nuevas molduras</h2>
    <form action="{{ route('storeMolding') }}" method="post">
        @csrf
        @include('layouts.partials.messages')
        <input name="nombre" type="text" placeholder="Ingresa el nombre de la moldura" class="input-field" maxlength="50">
        <button class="btn btn-block text-center my-3">Guardar moldura</button>
    </form>
</div>
@endsection