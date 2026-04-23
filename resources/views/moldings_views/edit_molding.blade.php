@extends('layouts.appMenu')

@section('head')
<title>Editar molduras</title>
<link rel="icon" href="{{ url('images/lg_saavedra.png') }}?v=1">
@vite(['resources/css/moldings_views/edit_molding.css', 'resources/js/moldings_views/edit_molding.js'])
<script>
    window.baseUrl = "{{ url('/') }}";
</script>
@endsection

@section('background-body', 'background-image:url("' . asset("images/fondoLogin.jpg") . '")') <!--Body background Image-->
@section('content')
<div class="wrapper">
    <img src="{{ asset('images/lg_saavedra.png') }}" class="lg-saavedra rounded-4" alt="" />
    <h2>Editar molduras</h2>
    <form action="{{ route('updateMolding') }}" method="post">
        @csrf
        @include('layouts.partials.messages')

        <!--La tabla y el boton se crea en JavaScript-->
    </form>
</div>
<script>
    const moldings = @json($moldings);
</script>
@endsection