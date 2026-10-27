@php
    $isInactive = !$user->estatus;
    $rolLabel = match((string)$user->perfil) {
        '1' => 'Administrador',
        '2' => 'Operador',
        '3' => 'Master',
        '4' => 'Calidad',
        '5' => 'Almacén',
        '6' => 'Supervisor',
        default => $user->perfil,
    };
    $rolIcon = match((string)$user->perfil) {
        '1' => 'images/Light_Icons/Administracion-Light.png',
        '2' => 'images/Light_Icons/Operador-Light.png',
        '3' => 'images/Light_Icons/Master-Light.png',
        '4' => 'images/Light_Icons/Calidad-Light.png',
        '5' => 'images/Light_Icons/Almacen-Light.png',
        '6' => 'images/Light_Icons/Supervision-Light.png',
        default => null,
    };
    $fullName = trim($user->name ?: ($user->nombre . ' ' . $user->a_paterno . ' ' . $user->a_materno));
@endphp

<div 
    class="user-card {{ $isInactive ? 'user-card-inactivo' : '' }}"
    data-planta="{{ $user->planta ?? '' }}"
    data-turno="{{ $user->turno ?? '' }}"
    data-status="{{ $user->estatus ? 'activo' : 'inactivo' }}"
    data-search="{{ strtolower($fullName . ' ' . $user->matricula . ' ' . ($user->puesto ?? '') . ' ' . ($user->area ?? '') . ' ' . ($user->planta ?? '')) }}"
>
    <div class="user-card-top">
        <div class="user-card-id-block">
            <span class="user-card-matricula">Matrícula: {{ $user->matricula }}</span>
            <span class="user-card-name">{{ $fullName }}</span>
        </div>
        <div>
            @if($isInactive)
                <span class="alert-falta-badge" title="Personal inactivo o vacante" style="display:inline-flex; align-items:center; gap:5px;">
                    <img src="{{ asset('images/Color_Icons/Generales/inactivar.png') }}" alt="Inactivo" style="width:13px; height:13px; object-fit:contain;">
                    FALTA / INACTIVO
                </span>
            @else
                <span class="badge-status-act" style="display:inline-flex; align-items:center; gap:5px;">
                    <img src="{{ asset('images/Color_Icons/Generales/activar.png') }}" alt="Activo" style="width:13px; height:13px; object-fit:contain;">
                    ACTIVO
                </span>
            @endif
        </div>
    </div>

    {{-- Puesto --}}
    <div class="user-card-puesto">
        {{ $user->puesto ?: 'Puesto sin definir' }}
    </div>

    @if($isInactive)
        <div class="alert-falta-note">
            ⚠️ Falta de personal en el puesto / Inactivo
        </div>
    @endif

    {{-- Meta: Planta, Turno, Área, Perfil --}}
    <div class="user-card-meta">
        @if(!empty($user->planta))
            <span class="user-card-tag user-card-tag-planta">
                📍 {{ $user->planta }}
            </span>
        @else
            <span class="user-card-tag" style="color:#94a3b8;">
                📍 Sin Planta
            </span>
        @endif

        @if(!empty($user->turno))
            <span class="user-card-tag user-card-tag-turno">
                ⏱ {{ $user->turno }}
            </span>
        @endif

        @if(!empty($user->area))
            <span class="user-card-tag">
                🏢 {{ $user->area }}
            </span>
        @endif

        <span class="user-card-tag" style="background:#e2e8f0; color:#475569; display:inline-flex; align-items:center; gap:5px;">
            @if($rolIcon)
                <img src="{{ icon($rolIcon) }}" alt="{{ $rolLabel }}" style="width:14px; height:14px; object-fit:contain;">
            @else
                👤
            @endif
            {{ $rolLabel }}
        </span>
    </div>
</div>
