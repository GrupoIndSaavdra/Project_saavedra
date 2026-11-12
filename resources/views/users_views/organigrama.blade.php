@extends('layouts.appMenu')

@section('background-body', 'background-image:url("' . asset("images/fondos/fondoLogin.jpg") . '")')

@section('content')
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/x-icon" href="{{ asset('images/logos/favicon.ico') }}?v=3">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organigrama — Grupo Industrial Saavedra</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/users_views/organigrama.css', 'resources/js/users_views/organigrama.js'])
</head>

@php
    // 1. Catálogo completo de usuarios para el organigrama
    $allUsers = $users;
    $assignedUsers = $users;

    // 2. Identificar Dirección General / Presidencia (Nivel 1 Raíz)
    $directores = $allUsers->filter(function($u) {
        $perfilId = (int) ($u->perfil_id ?? $u->perfil ?? 0);
        $puesto = strtoupper(trim($u->puesto ?: ($u->puestoInfo->nombre ?? '')));
        $area   = strtoupper(trim($u->area ?? ''));
        return $perfilId === 1 || in_array($puesto, ['DIRECTOR / MASTER', 'DIRECTOR', 'GERENTE', 'JEFE DE PLANTA']) || in_array($area, ['DIRECCIÓN GENERAL', 'DIRECCION GENERAL', 'GERENCIA']);
    });

    // Raíz principal (Abraham Saavedra ID 1 o primer directivo)
    $director = $directores->firstWhere('id', 1) ?? $directores->first();

    // Otros directores / Master corporativo (para que no desaparezcan y tengan su espacio en el organigrama)
    $otrosDirectores = $directores->filter(fn($u) => !$director || $u->id !== $director->id);

    // 3. Definición de Ramas Departamentales y Especialidades Operativas (Nivel 2)
    $branchDefinitions = [
        'ADMINISTRACION' => [
            'label' => 'ADMINISTRACIÓN',
            'match' => function($u) {
                $perfil = (int) ($u->perfil_id ?? $u->perfil ?? 0);
                $puesto = strtoupper(trim($u->puesto ?: ($u->puestoInfo->nombre ?? '')));
                $area   = strtoupper(trim($u->area ?? ''));
                return $perfil === 2 || str_contains($area, 'ADMIN') || str_contains($puesto, 'ADMIN');
            }
        ],
        'SISTEMAS' => [
            'label' => 'SISTEMAS / TI',
            'match' => function($u) {
                $perfil = (int) ($u->perfil_id ?? $u->perfil ?? 0);
                $puesto = strtoupper(trim($u->puesto ?: ($u->puestoInfo->nombre ?? '')));
                $area   = strtoupper(trim($u->area ?? ''));
                return $perfil === 10 || str_contains($area, 'SISTEMA') || str_contains($puesto, 'SISTEMA') || str_contains($puesto, 'SOFTWARE') || str_contains($puesto, 'TI');
            }
        ],
        'CALIDAD' => [
            'label' => 'CALIDAD',
            'match' => function($u) {
                $perfil = (int) ($u->perfil_id ?? $u->perfil ?? 0);
                $puesto = strtoupper(trim($u->puesto ?: ($u->puestoInfo->nombre ?? '')));
                $area   = strtoupper(trim($u->area ?? ''));
                return $perfil === 8 || str_contains($area, 'CALIDAD') || str_contains($puesto, 'CALIDAD') || str_contains($puesto, 'INSPECC') || str_contains($puesto, 'METROLOG');
            }
        ],
        'ALMACEN' => [
            'label' => 'ALMACÉN',
            'match' => function($u) {
                $perfil = (int) ($u->perfil_id ?? $u->perfil ?? 0);
                $puesto = strtoupper(trim($u->puesto ?: ($u->puestoInfo->nombre ?? '')));
                $area   = strtoupper(trim($u->area ?? ''));
                return $perfil === 7 || str_contains($area, 'ALMACEN') || str_contains($area, 'ALMACÉN') || str_contains($puesto, 'ALMACEN') || str_contains($puesto, 'ALMACÉN');
            }
        ],
        'SOLDADURA' => [
            'label' => 'SOLDADURA & PTA',
            'match' => function($u) {
                $puesto = strtoupper(trim($u->puesto ?: ($u->puestoInfo->nombre ?? '')));
                $area   = strtoupper(trim($u->area ?? ''));
                return str_contains($puesto, 'SOLDAD') || str_contains($puesto, 'PTA') || str_contains($area, 'SOLDAD');
            }
        ],
        'HERRAMENTISTAS' => [
            'label' => 'HERRAMENTISTAS',
            'match' => function($u) {
                $puesto = strtoupper(trim($u->puesto ?: ($u->puestoInfo->nombre ?? '')));
                $area   = strtoupper(trim($u->area ?? ''));
                return str_contains($puesto, 'HERRAMENT') || str_contains($area, 'HERRAMENT');
            }
        ],
        'DYP' => [
            'label' => 'DISEÑO Y PROGRAMACIÓN',
            'match' => function($u) {
                $perfil = (int) ($u->perfil_id ?? $u->perfil ?? 0);
                $puesto = strtoupper(trim($u->puesto ?: ($u->puestoInfo->nombre ?? '')));
                $area   = strtoupper(trim($u->area ?? ''));
                return $perfil === 5 || str_contains($area, 'DISEÑO') || str_contains($area, 'PROGRAMACI') || str_contains($puesto, 'DISEÑO') || str_contains($puesto, 'PROGRAMACI');
            }
        ],
        'INGENIERIA' => [
            'label' => 'INGENIERÍA',
            'match' => function($u) {
                $perfil = (int) ($u->perfil_id ?? $u->perfil ?? 0);
                $puesto = strtoupper(trim($u->puesto ?: ($u->puestoInfo->nombre ?? '')));
                $area   = strtoupper(trim($u->area ?? ''));
                return $perfil === 4 || str_contains($area, 'INGENIER') || str_contains($puesto, 'INGENIER');
            }
        ],
        'VENTAS' => [
            'label' => 'VENTAS',
            'match' => function($u) {
                $perfil = (int) ($u->perfil_id ?? $u->perfil ?? 0);
                $puesto = strtoupper(trim($u->puesto ?: ($u->puestoInfo->nombre ?? '')));
                $area   = strtoupper(trim($u->area ?? ''));
                return $perfil === 6 || str_contains($area, 'VENTA') || str_contains($puesto, 'VENTA');
            }
        ],
        'PRODUCCION' => [
            'label' => 'PRODUCCIÓN Y MAQUINADOS',
            'match' => function($u) {
                $perfil = (int) ($u->perfil_id ?? $u->perfil ?? 0);
                $puesto = strtoupper(trim($u->puesto ?: ($u->puestoInfo->nombre ?? '')));
                $area   = strtoupper(trim($u->area ?? ''));
                if (str_contains($puesto, 'SOLDAD') || str_contains($puesto, 'PTA') || str_contains($puesto, 'HERRAMENT')) return false;
                return in_array($perfil, [3, 9], true) || str_contains($area, 'PRODUCCI') || str_contains($area, 'MAQUINAD') || str_contains($area, 'TALLER') || str_contains($area, 'ACABADO') || str_contains($puesto, 'TORNO') || str_contains($puesto, 'CENTRO') || str_contains($puesto, 'AYUDANTE') || str_contains($puesto, 'OPERADOR') || str_contains($puesto, 'TURNO');
            }
        ],
    ];

    // Si existen directores o masters adicionales, agregamos la rama directiva
    if ($otrosDirectores->isNotEmpty()) {
        $branchDefinitions = ['DIRECCION' => [
            'label' => 'DIRECCIÓN Y ASESORÍA MASTER',
            'match' => fn($u) => $otrosDirectores->contains('id', $u->id)
        ]] + $branchDefinitions;
    }

    $assignedUserIds = [$director->id ?? 0];
    $areaData = [];

    foreach ($branchDefinitions as $branchKey => $def) {
        $usersInBranch = $allUsers->filter(function($u) use ($def, &$assignedUserIds) {
            if (in_array($u->id, $assignedUserIds, true)) return false;
            return ($def['match'])($u);
        });

        // Solo incluir ramas con colaboradores asignados
        if ($usersInBranch->isNotEmpty()) {
            foreach ($usersInBranch as $u) {
                $assignedUserIds[] = $u->id;
            }

            // Supervisor o Líder con prioridad jerárquica
            $supervisor = $usersInBranch->sortBy(function($u) {
                $puesto = strtoupper(trim($u->puesto ?: ($u->puestoInfo->nombre ?? '')));
                $perfilId = (int) ($u->perfil_id ?? $u->perfil ?? 0);
                $nivel = (int) ($u->puestoInfo->codigo_nivel ?? 9);

                if (str_contains($puesto, 'JEFE')) return 1;
                if (str_contains($puesto, 'SUPERVISOR') || $perfilId === 3) return 2;
                if (str_contains($puesto, 'ENCARGAD') || str_contains($puesto, 'LIDER')) return 3;
                if ($nivel <= 2) return 4;
                if (str_contains($puesto, 'AUXILIAR')) return 10;
                return 20 + $nivel;
            })->first();

            if (!$supervisor && $usersInBranch->isNotEmpty()) {
                $supervisor = $usersInBranch->first();
            }

            $team = $usersInBranch->filter(function($u) use ($supervisor) {
                return !$supervisor || $u->id !== $supervisor->id;
            });

            $areaData[$branchKey] = [
                'label' => $def['label'],
                'supervisor' => $supervisor,
                'team' => $team,
            ];
        }
    }

    $totalCount = $allUsers->count();
    $activeCount = $allUsers->where('estatus', 1)->count();
    $inactiveCount = $allUsers->where('estatus', 0)->count();
@endphp

<div class="org-master-wrapper">

    {{-- ── Encabezado Principal ── --}}
    <div class="org-top-header">
        <div class="org-brand">
            <div class="org-header-icon-circle">
                <img src="{{ file_exists(public_path('images/Color_Icons/Vistas/Organigrama.png')) ? asset('images/Color_Icons/Vistas/Organigrama.png') : asset('images/Color_Icons/Generales/organigrama.png') }}" alt="Organigrama" class="org-header-icon">
            </div>
            <div class="org-brand-titles">
                <h1>ORGANIGRAMA</h1>
                <span>Grupo Industrial Saavedra &bull; Estructura Organizacional y Jerárquica</span>
            </div>
        </div>

        <div class="org-header-controls">
            <button type="button" id="btn-export-pdf" class="btn-header-action btn-action-pdf" title="Descargar organigrama en PDF">
                <img src="{{ asset('images/Color_Icons/Generales/pdf-transparent.png') }}" alt="PDF" class="org-btn-icon">
                <span>Descargar PDF</span>
            </button>
            <a href="{{ route('users') }}" class="btn-header-action btn-action-table" title="Ver tabla de usuarios">
                <img src="{{ asset('images/Color_Icons/Usuarios/usuario.png') }}" alt="Tabla" class="org-btn-icon">
                <span>Tabla de Usuarios</span>
            </a>
            <a href="{{ route('createUser') }}" class="btn-header-action btn-action-create" title="Registrar nuevo usuario">
                <img src="{{ asset('images/Color_Icons/Generales/anadir.png') }}" alt="Registrar" class="org-btn-icon">
                <span>Registrar Personal</span>
            </a>
        </div>
    </div>

    {{-- ── Barra de KPIs Resumen ── --}}
    <div class="org-kpi-bar">
        <div class="org-kpi-pill pill-total">
            <div class="kpi-header-content">
                <div class="kpi-icon-circle kpi-circle-total">
                    <img src="{{ asset('images/Color_Icons/Usuarios/usuario.png') }}" alt="Personal Asignado" class="kpi-icon">
                </div>
                <span class="kpi-title">Personal Asignado</span>
            </div>
            <span class="kpi-num" id="kpi-total-val">{{ $totalCount }}</span>
        </div>
        <div class="org-kpi-pill pill-active">
            <div class="kpi-header-content">
                <div class="kpi-icon-circle kpi-circle-active">
                    <img src="{{ asset('images/Color_Icons/Generales/activar.png') }}" alt="Activos" class="kpi-icon">
                </div>
                <span class="kpi-title">Activos</span>
            </div>
            <span class="kpi-num" id="kpi-active-val">{{ $activeCount }}</span>
        </div>
        <div class="org-kpi-pill pill-inactive">
            <div class="kpi-header-content">
                <div class="kpi-icon-circle kpi-circle-inactive">
                    <img src="{{ asset('images/Color_Icons/Generales/inactivar.png') }}" alt="Inactivos" class="kpi-icon">
                </div>
                <span class="kpi-title">Inactivos / Faltantes</span>
            </div>
            <span class="kpi-num" id="kpi-inactive-val">{{ $inactiveCount }}</span>
        </div>
    </div>

    {{-- ── Barra de Herramientas, Filtros y Zoom ── --}}
    <div class="org-toolbar-bar">
        <div class="filter-group">
            <span class="filter-lbl">Planta:</span>
            <div class="btn-toggle-group">
                <button type="button" class="btn-toggle planta-filter-btn active" data-planta="todas">Todas</button>
                <button type="button" class="btn-toggle planta-filter-btn" data-planta="TECÁMAC">Tecámac</button>
                <button type="button" class="btn-toggle planta-filter-btn" data-planta="CDMX">CDMX</button>
            </div>
        </div>

        <div class="filter-group">
            <span class="filter-lbl">Turno:</span>
            <select id="org-turno-filter" class="select-filter-org">
                <option value="todos">Todos los turnos</option>
                <option value="MATUTINO">Matutino</option>
                <option value="VESPERTINO">Vespertino</option>
                <option value="NOCTURNO">Nocturno</option>
                <option value="MIXTO">Mixto</option>
            </select>
        </div>

        <div class="filter-group">
            <span class="filter-lbl">Estatus:</span>
            <select id="org-status-filter" class="select-filter-org">
                <option value="todos">Todos los estatus</option>
                <option value="activo">Solo Activos</option>
                <option value="inactivo">Solo Faltantes (Inactivos)</option>
            </select>
        </div>

        <div class="search-box-org">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
            </svg>
            <input type="text" id="org-search-input" placeholder="Buscar por nombre, puesto o matrícula…">
        </div>

        <div class="zoom-controls">
            <button type="button" class="btn-zoom btn-zoom-fit" id="btn-zoom-fit" title="Ajustar organigrama a la pantalla">
                <svg class="zoom-btn-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="9"></circle>
                    <circle cx="12" cy="12" r="4"></circle>
                    <line x1="12" y1="2" x2="12" y2="5"></line>
                    <line x1="12" y1="19" x2="12" y2="22"></line>
                    <line x1="2" y1="12" x2="5" y2="12"></line>
                    <line x1="19" y1="12" x2="22" y2="12"></line>
                </svg>
                <span>Ajustar</span>
            </button>
            <button type="button" class="btn-zoom" id="btn-zoom-out" title="Alejar (Zoom Out)">−</button>
            <button type="button" class="btn-zoom" id="btn-zoom-reset" title="Restablecer 100%" style="font-size: 0.75rem; width: auto; padding: 0 8px;">100%</button>
            <button type="button" class="btn-zoom" id="btn-zoom-in" title="Acercar (Zoom In)">+</button>
        </div>
    </div>

    {{-- ── LIENZO DEL ORGANIGRAMA EN ÁRBOL CONECTADO ── --}}
    <div class="org-canvas-container" id="org-canvas">
        {{-- Mensaje cuando no hay personal para el filtro seleccionado (ej: Planta CDMX) --}}
        <div id="org-empty-filter-msg" class="org-empty-filter-box" style="display: none;">
            <div class="empty-filter-icon">📍</div>
            <h3 class="empty-filter-title" id="empty-filter-title">No hay personal registrado en esta ubicación</h3>
            <p class="empty-filter-subtitle" id="empty-filter-details">Actualmente no se encuentran colaboradores asignados a los filtros seleccionados.</p>
            <button type="button" class="btn-reset-filters-org" id="btn-reset-empty-filters">Mostrar Todas las Plantas</button>
        </div>

        <div class="org-tree-root" id="org-tree-root">

            @if($assignedUsers->isEmpty())
                <div style="text-align: center; padding: 60px 20px; color: #64748b;">
                    <div style="font-size: 3rem; margin-bottom: 10px;">📋</div>
                    <h3 style="font-size: 1.3rem; font-weight: 800; color: var(--gis-blue);">No hay personal con puesto o área asignada todavía</h3>
                    <p style="font-size: 0.9rem; margin-top: 6px;">Edita los usuarios en la <a href="{{ route('users') }}" style="color: #0284c7; font-weight: 700;">Tabla de Usuarios</a> asignándoles su Área y Puesto para que aparezcan en el Organigrama.</p>
                </div>
            @else
                <div class="tree-branch">
                    <ul>
                        {{-- ── NODO RAÍZ: JEFE DE PLANTA / DIRECCIÓN GENERAL ── --}}
                        <li>
                            @if($director)
                                @include('users_views.partials.org_node', ['user' => $director, 'isDirector' => true, 'isSupervisor' => false])
                            @else
                                <div class="org-node org-node-director">
                                    <div class="org-avatar-wrapper">
                                        <div class="org-avatar-circle">
                                            <img src="{{ asset('images/Color_Icons/Usuarios/gerente.png') }}" alt="Gerente" class="org-avatar-img">
                                        </div>
                                    </div>
                                    <div class="org-name">DIRECCIÓN GENERAL</div>
                                    <div class="org-role">JEFATURA DE PLANTA</div>
                                </div>
                            @endif

                            {{-- ── RAMAS A CADA ÁREA ACTIVA (SUPERVISORES Y HERRAMENTISTAS EN NIVEL 2) ── --}}
                            @if(!empty($areaData))
                                <ul>
                                    @foreach($areaData as $areaKey => $area)
                                        <li>
                                            {{-- Titulo del Área / Rama --}}
                                            <div class="area-branch-title">{{ mb_strtoupper($area['label'], 'UTF-8') }}</div>

                                            {{-- NODO SUPERVISOR / HERRAMENTISTA LÍDER --}}
                                            @if($area['supervisor'])
                                                @include('users_views.partials.org_node', ['user' => $area['supervisor'], 'isDirector' => false, 'isSupervisor' => true, 'areaLabel' => $area['label']])
                                            @else
                                                <div class="org-node org-node-supervisor node-inactivo" data-search="{{ strtolower($area['label']) }}">
                                                    <div class="org-avatar-wrapper">
                                                        <div class="org-avatar-circle" style="border-color:#dc2626; background:#fee2e2;">
                                                            <span style="font-size:1.6rem;">⚠️</span>
                                                        </div>
                                                    </div>
                                                    <div class="org-name" style="color:#b91c1c;">FALTA SUPERVISOR</div>
                                                    <div class="org-role">ENCARGADO DE {{ mb_strtoupper($area['label'], 'UTF-8') }}</div>
                                                    <div class="badge-falta-alert">🔴 VACANTE</div>
                                                </div>
                                            @endif

                                            {{-- SUB-RAMAS DE EQUIPO (Torno CNC, Centro de Maquinados, Ayudante General, Becarios, etc.) --}}
                                            @if($area['team']->isNotEmpty())
                                                @php
                                                    $teamByPuesto = $area['team']->groupBy(function($u) {
                                                        return trim($u->puesto ?: ($u->puestoInfo->nombre ?? 'Operativo'));
                                                    })->sortBy(function($group, $puestoName) {
                                                        $p = strtoupper($puestoName);
                                                        if (str_contains($p, 'DIRECTOR') || str_contains($p, 'MASTER')) return 0;
                                                        if (str_contains($p, 'JEFE')) return 1;
                                                        if (str_contains($p, 'SUPERVISOR')) return 2;
                                                        if (str_contains($p, 'AUXILIAR')) return 3;
                                                        if (str_contains($p, 'TORNO')) return 4;
                                                        if (str_contains($p, 'CENTRO')) return 5;
                                                        if (str_contains($p, 'SOLDADOR') || str_contains($p, 'PTA')) return 6;
                                                        if (str_contains($p, 'HERRAMENT')) return 7;
                                                        if (str_contains($p, 'ACABADO')) return 8;
                                                        if (str_contains($p, 'INSPECC') || str_contains($p, 'CALIDAD')) return 9;
                                                        if (str_contains($p, 'ALMACEN')) return 10;
                                                        if (str_contains($p, 'AYUDANTE')) return 11;
                                                        if (str_contains($p, 'BECARI')) return 12;
                                                        return 20;
                                                    });
                                                @endphp
                                                <ul>
                                                    @foreach($teamByPuesto as $puestoName => $membersInPuesto)
                                                        <li>
                                                            <div class="sub-role-branch-title">{{ mb_strtoupper($puestoName, 'UTF-8') }}</div>
                                                            <div class="puesto-nodes-column {{ $membersInPuesto->count() >= 2 ? 'nodes-grid-2col' : '' }}">
                                                                @foreach($membersInPuesto as $member)
                                                                    @include('users_views.partials.org_node', ['user' => $member, 'isDirector' => false, 'isSupervisor' => false])
                                                                @endforeach
                                                            </div>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </li>
                    </ul>
                </div>
            @endif

        </div>
    </div>

</div>
@endsection
