<!-- Encabezado Estilo Excel Prioridades Desactivadas -->
<div class="excel-header">
    <div class="header-logo">
        <img src="{{ asset('images/logos/lg_saavedra.png') }}" alt="Grupo Industrial Saavedra" />
    </div>
    <div class="header-title" style="display: flex; align-items: center; justify-content: center; gap: 12px;">
        @if (file_exists(public_path('images/Color_Icons/Vistas/Prioridades.png')))
            <img src="{{ asset('images/Color_Icons/Vistas/Prioridades.png') }}" alt="Prioridades"
                style="height: 38px; width: auto; object-fit: contain; filter: grayscale(100%);">
        @endif
        <h1 style="color: #dc2626;">PRIORIDADES DESACTIVADAS</h1>
    </div>
    <div class="header-week">
        @if (!empty($startWeek) && !empty($endWeek) && $startWeek == $endWeek)
            <h2>SEMANA {{ $startWeek }}</h2>
        @elseif(!empty($startWeek) || !empty($endWeek))
            <h2>SEMANAS {{ $startWeek ?? 1 }}-{{ $endWeek ?? 52 }}</h2>
        @else
            <h2>SEMANA {{ \Carbon\Carbon::now()->isoWeek() }}</h2>
        @endif
        <p>HISTORIAL DE OTs INACTIVAS</p>
    </div>
    <div class="header-date">
        <h3>{{ \Carbon\Carbon::now()->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}</h3>
    </div>
</div>
