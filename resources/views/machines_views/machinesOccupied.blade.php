@extends('layouts.appMenu')

@section('head')
<title>Maquinas Ocupadas</title>
@vite(['resources/css/machines_views/machinesOccupied.css', 'resources/js/machines_views/machinesOccupied.js'])
@endsection

@section('background-body', 'background-image:url("' . asset("images/fondoLogin.jpg") . '")') <!--Body background Image-->
@section('content')
<div class="wrapper">
    <img src="{{ asset('images/lg_saavedra.png') }}" class="lg-saavedra rounded-4" alt="" />
    <h2>Maquinas Ocupadas</h2>
    <form action="{{ route('freeUp') }}" method="post">
        @csrf
        @include('layouts.partials.messages')

        <!--La tabla y el boton se crea en JavaScript-->
    </form>
</div>
<script>
    const machines = @json($machines);
    window.baseUrl = "{{ url('/') }}";
</script>
@endsection