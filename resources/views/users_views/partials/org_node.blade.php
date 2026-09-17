@php
    $isInactive = !$user->estatus;
    $fullName = mb_strtoupper(trim($user->name ?: ($user->nombre . ' ' . $user->a_paterno . ' ' . $user->a_materno)), 'UTF-8');
    
    $rawPuesto = trim($user->puesto ?? '');
    if ($isDirector) {
        $puesto = $rawPuesto ?: 'JEFE DE PLANTA / DIRECCIÓN GENERAL';
    } elseif ($isSupervisor) {
        if (empty($rawPuesto) || strtoupper($rawPuesto) === 'SUPERVISOR') {
            $puesto = !empty($areaLabel) ? 'SUPERVISOR DE ' . $areaLabel : (!empty($user->area) ? 'SUPERVISOR DE ' . $user->area : 'SUPERVISOR DE ÁREA');
        } else {
            $puesto = $rawPuesto;
        }
    } else {
        $puesto = $rawPuesto ?: 'PUESTO OPERATIVO';
    }

    $puesto = mb_strtoupper($puesto, 'UTF-8');
    $nodeTypeClass = $isDirector ? 'org-node-director' : ($isSupervisor ? 'org-node-supervisor' : '');
    
    // Asignación de imagen según rol / puesto
    $puestoUpper = strtoupper($puesto);
    $areaUpper = strtoupper($user->area ?? '');
    $nameUpper = strtoupper($user->name ?: ($user->nombre . ' ' . $user->a_paterno . ' ' . $user->a_materno));
    
    $isFemale = false;
    $femaleKeywords = [
        'NATALI', 'NATALIA', 'LORENA', 'ALEJANDRA', 'MARIA', 'FERNANDA', 'LAURA', 'ANA', 'LIZ', 'LIZBETH', 
        'VALERIA', 'DANIELA', 'CAROLINA', 'GABRIELA', 'PAOLA', 'KAREN', 'JESSICA', 'ANDREA', 
        'DIANA', 'PATRICIA', 'CLAUDIA', 'MONICA', 'LILIANA', 'SUSANA', 'BRENDA', 'YADIRA', 
        'KARINA', 'GUADALUPE', 'LUPITA', 'ADRIANA', 'SILVIA', 'VERONICA', 'BEATRIZ', 'ESTEFANIA', 
        'FATIMA', 'ALMA', 'MARIANA', 'IVONNE', 'MIRIAM', 'VANESSA', 'XIMENA', 'DENISSE', 'SANDRA', 
        'ROSA', 'CARMEN', 'TERESA', 'GLORIA', 'MARTHA', 'LETICIA', 'ARACELI', 'SOCORRO', 'IRMA', 
        'CECILIA', 'ELIZABETH', 'ANGELICA', 'MAYRA', 'TANIA', 'LUCIA', 'VICTORIA', 'ROCIO', 'MONTSERRAT',
        'ISABEL', 'JACQUELINE', 'ABIGAIL', 'EVELYN', 'JOCELYN', 'ITZELL', 'ITZEL', 'BLANCA'
    ];
    foreach ($femaleKeywords as $fw) {
        if (str_contains($nameUpper, $fw)) {
            $isFemale = true;
            break;
        }
    }

    $avatarImg = null;
    $avatarAlt = '';

    if ($isDirector || str_contains($puestoUpper, 'GERENTE') || str_contains($puestoUpper, 'DIRECTOR') || str_contains($puestoUpper, 'JEFE DE PLANTA') || str_contains($puestoUpper, 'JEFE PLANTA') || str_contains($areaUpper, 'GERENCIA') || str_contains($areaUpper, 'JEFE PLANTA') || str_contains($areaUpper, 'DIRECCION')) {
        $avatarImg = asset('images/gerente.png');
        $avatarAlt = 'Gerente / Jefe de Planta';
    } elseif (str_contains($puestoUpper, 'SUPERVISORA') || str_contains($puestoUpper, 'ENCARGADA') || str_contains($puestoUpper, 'JEFA')) {
        $avatarImg = asset('images/supervisora.png');
        $avatarAlt = 'Supervisora';
    } elseif ($isSupervisor || str_contains($puestoUpper, 'SUPERVISOR') || str_contains($puestoUpper, 'JEFE') || str_contains($puestoUpper, 'ENCARGAD') || str_contains($puestoUpper, 'LIDER') || str_contains($puestoUpper, 'LÍDER') || $areaUpper === 'SUPERVISOR') {
        if ($isFemale) {
            $avatarImg = asset('images/supervisora.png');
            $avatarAlt = 'Supervisora';
        } else {
            $avatarImg = asset('images/supervisor.png');
            $avatarAlt = 'Supervisor';
        }
    } elseif (str_contains($puestoUpper, 'BECARI') || str_contains($puestoUpper, 'PRACTICANTE') || str_contains($puestoUpper, 'RESIDENTE') || str_contains($puestoUpper, 'SERVICIO SOCIAL') || str_contains($areaUpper, 'BECARI')) {
        $avatarImg = asset('images/becario.png');
        $avatarAlt = 'Becario';
    } elseif (str_contains($puestoUpper, 'AYUDANTE') || str_contains($puestoUpper, 'AUXILIAR') || str_contains($areaUpper, 'AYUDANTE')) {
        $avatarImg = asset('images/ayudante general.png');
        $avatarAlt = 'Ayudante General';
    } elseif (str_contains($puestoUpper, 'SOLDAD') || str_contains($areaUpper, 'SOLDAD')) {
        $avatarImg = asset('images/soldador.png');
        $avatarAlt = 'Soldador';
    } elseif (str_contains($puestoUpper, 'INSPECC') || str_contains($puestoUpper, 'CALIDAD') || str_contains($puestoUpper, 'METROLOG') || str_contains($areaUpper, 'CALIDAD')) {
        $avatarImg = asset('images/inspeccion.png');
        $avatarAlt = 'Inspección de Calidad';
    } elseif (str_contains($puestoUpper, 'CENTRO') || str_contains($puestoUpper, 'MAQUINAD')) {
        $avatarImg = asset('images/operador centro de maquinado .png');
        $avatarAlt = 'Operador Centro de Maquinados';
    } elseif (str_contains($puestoUpper, 'TORNO') || str_contains($puestoUpper, 'CNC') || str_contains($puestoUpper, 'OPERADOR') || str_contains($puestoUpper, 'HERRAMENTISTA')) {
        $avatarImg = asset('images/operador_cnc.png');
        $avatarAlt = 'Operador CNC';
    }
@endphp

<div 
    class="org-node {{ $nodeTypeClass }} {{ $isInactive ? 'node-inactivo' : '' }}"
    data-planta="{{ $user->planta ?? '' }}"
    data-turno="{{ $user->turno ?? '' }}"
    data-status="{{ $user->estatus ? 'activo' : 'inactivo' }}"
    data-search="{{ strtolower($fullName . ' ' . $user->matricula . ' ' . $puesto . ' ' . ($user->area ?? '') . ' ' . ($user->planta ?? '')) }}"
    title="{{ $fullName }} - {{ $puesto }} (Matrícula: {{ $user->matricula }})"
>
    {{-- Avatar / Imagen Asignada --}}
    <div class="org-avatar-wrapper">
        <div class="org-avatar-circle">
            @if($avatarImg)
                <img src="{{ $avatarImg }}" alt="{{ $avatarAlt }}" class="org-avatar-img" loading="lazy">
            @elseif(str_contains($puestoUpper, 'CALIDAD') || str_contains($puestoUpper, 'INSPECC'))
                {{-- Female / Dark Hair avatar for Calidad / Inspección --}}
                <svg class="org-avatar-svg" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="32" cy="32" r="30" fill="#ccfbf1" />
                    <path d="M20 22C20 12 25 10 32 10C39 10 44 12 44 22C44 32 40 38 40 38H24C24 38 20 32 20 22Z" fill="#0f172a" />
                    <circle cx="32" cy="26" r="10" fill="#fcd34d" />
                    <path d="M18 54C18 44 24 40 32 40C40 40 46 44 46 54V60H18V54Z" fill="#0d9488" />
                </svg>
            @elseif(str_contains($puestoUpper, 'HERRAMENTISTA'))
                {{-- Herramentista / Especialista --}}
                <svg class="org-avatar-svg" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="32" cy="32" r="30" fill="#ffedd5" />
                    <path d="M22 22C22 14 26 11 32 11C38 11 42 14 42 22C42 24 39 23 35 22C30 22 25 24 22 22Z" fill="#c2410c" />
                    <circle cx="32" cy="26" r="10" fill="#fde68a" />
                    <path d="M16 54C16 44 23 40 32 40C41 40 48 44 48 54V60H16V54Z" fill="#0f766e" />
                </svg>
            @else
                {{-- Operadores Pendientes (Torno CNC, Centro Maquinados, Soldador, etc.) --}}
                <svg class="org-avatar-svg" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="32" cy="32" r="30" fill="#f1f5f9" />
                    <path d="M22 22C22 14 26 12 32 12C38 12 42 14 42 22C42 24 38 23 35 22C30 22 25 24 22 22Z" fill="#334155" />
                    <circle cx="32" cy="26" r="10" fill="#fcd34d" />
                    <path d="M16 54C16 44 23 40 32 40C41 40 48 44 48 54V60H16V54Z" fill="#3b82f6" />
                </svg>
            @endif
        </div>
    </div>

    {{-- Nombre y Puesto --}}
    <div class="org-name">{{ $fullName }}</div>
    <div class="org-role">{{ $puesto }}</div>

    {{-- Tags / Meta --}}
    <div class="org-meta-tags">
        @if(!empty($user->planta))
            <span class="org-tag org-tag-planta">{{ $user->planta }}</span>
        @endif
        @if(!empty($user->turno))
            <span class="org-tag org-tag-turno">{{ $user->turno }}</span>
        @endif
        <span class="org-tag">#{{ $user->matricula }}</span>
    </div>

    {{-- Si está inactivo, badge de ALERTA EN ROJO --}}
    @if($isInactive)
        <div class="badge-falta-alert">
            🔴 FALTA / INACTIVO
        </div>
    @endif
</div>
