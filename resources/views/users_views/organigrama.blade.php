@extends('layouts.appMenu')

@section('background-body', 'background-image:url("' . asset("images/fondoLogin.jpg") . '")')

@section('content')
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="{{ url('images/lg_saavedra.png') }}?v=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organigrama — Grupo Industrial Saavedra</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/users_views/organigrama.css', 'resources/js/users_views/organigrama.js'])
</head>

@php
    // Filtrar EXCLUSIVAMENTE personal que tiene asignado al menos área o puesto
    $assignedUsers = $users->filter(function($u) {
        $hasArea = !empty(trim($u->area ?? ''));
        $hasPuesto = !empty(trim($u->puesto ?? ''));
        return $hasArea || $hasPuesto;
    });

    // Identificar Director General / Jefe de Planta entre los asignados
    $director = $assignedUsers->first(function($u) {
        $area = strtoupper(trim($u->area ?? ''));
        $puesto = strtoupper(trim($u->puesto ?? ''));
        return in_array($area, ['JEFE PLANTA', 'JEFE DE PLANTA', 'GERENCIA', 'DIRECCIÓN', 'DIRECCION']) ||
               in_array($puesto, ['JEFE DE PLANTA', 'GERENTE', 'DIRECTOR', 'JEFE PLANTA']);
    });

    // Definición estándar de Áreas (Herramentistas al mismo nivel que Supervisores de Software/Programación)
    $areaDefinitions = [
        'PRODUCCIÓN' => [
            'aliases' => ['PRODUCCIÓN', 'PRODUCCION'],
            'exclude_puestos' => ['HERRAMENTISTA'], // Herramentista se eleva al nivel 2 independiente
            'label' => 'PRODUCCIÓN',
        ],
        'HERRAMENTISTAS' => [
            'aliases' => ['HERRAMENTISTA', 'HERRAMENTALES'],
            'puesto_match' => ['HERRAMENTISTA'],
            'label' => 'HERRAMENTISTAS',
            'is_specialist' => true,
        ],
        'PROGRAMACIÓN' => [
            'aliases' => ['PROGRAMACIÓN', 'PROGRAMACION'],
            'label' => 'PROGRAMACIÓN',
        ],
        'SOFTWARE' => [
            'aliases' => ['SOFTWARE', 'SISTEMAS'],
            'label' => 'SOFTWARE / SISTEMAS',
        ],
        'CALIDAD' => [
            'aliases' => ['CALIDAD', 'METROLOGIA'],
            'label' => 'CALIDAD',
        ],
        'SOLDADURA' => [
            'aliases' => ['SOLDADURA', 'SOLDADOR'],
            'label' => 'SOLDADURA',
        ],
        'ALMACÉN' => [
            'aliases' => ['ALMACÉN', 'ALMACEN'],
            'label' => 'ALMACÉN',
        ],
        'MANTENIMIENTO' => [
            'aliases' => ['MANTENIMIENTO'],
            'label' => 'MANTENIMIENTO',
        ],
        'ADMINISTRACIÓN' => [
            'aliases' => ['ADMINISTRACIÓN', 'ADMINISTRACION', 'ADMIN'],
            'label' => 'ADMINISTRACIÓN',
        ],
    ];

    // Clasificar usuarios por cada rama activa
    $areaData = [];
    foreach ($areaDefinitions as $areaKey => $def) {
        $usersInArea = $assignedUsers->filter(function($u) use ($def, $director) {
            if ($director && $u->id === $director->id) return false;
            $uArea = strtoupper(trim($u->area ?? ''));
            $uPuesto = strtoupper(trim($u->puesto ?? ''));

            // Verificar exclusión explícita (ej: herramentistas en producción)
            if (!empty($def['exclude_puestos'])) {
                foreach ($def['exclude_puestos'] as $exp) {
                    if (str_contains($uPuesto, $exp) || str_contains($uArea, $exp)) return false;
                }
            }

            // Coincidencia por puesto explícito (ej: HERRAMENTISTA)
            if (!empty($def['puesto_match'])) {
                foreach ($def['puesto_match'] as $pm) {
                    if (str_contains($uPuesto, $pm) || str_contains($uArea, $pm)) return true;
                }
            }

            // Coincidencia por área o por puesto (ej: SUPERVISOR DE PRODUCCIÓN con área SUPERVISOR)
            foreach ($def['aliases'] as $alias) {
                if (str_contains($uArea, $alias) || str_contains($uPuesto, $alias)) return true;
            }
            return false;
        });

        // Solo incluir el área en el organigrama si cuenta con personal asignado
        if ($usersInArea->isNotEmpty()) {
            if (!empty($def['is_specialist'])) {
                // Para Herramentistas: el primero toma la cabecera del nivel 2 y los demás como sub-equipo
                $supervisor = $usersInArea->first();
                $team = $usersInArea->skip(1);
            } else {
                // Supervisor del área: puesto que contenga SUPERVISOR / JEFE / ENCARGADO o área SUPERVISOR
                $supervisor = $usersInArea->first(function($u) {
                    $puesto = strtoupper(trim($u->puesto ?? ''));
                    $area = strtoupper(trim($u->area ?? ''));
                    return str_contains($puesto, 'SUPERVISOR') || str_contains($puesto, 'JEFE') || str_contains($puesto, 'ENCARGAD') || $area === 'SUPERVISOR';
                });

                // Equipo operativo bajo el supervisor
                $team = $usersInArea->filter(function($u) use ($supervisor) {
                    return !$supervisor || $u->id !== $supervisor->id;
                });
            }

            $areaData[$areaKey] = [
                'label' => $def['label'],
                'supervisor' => $supervisor,
                'team' => $team,
            ];
        }
    }

    $totalCount = $assignedUsers->count();
    $activeCount = $assignedUsers->where('estatus', 1)->count();
    $inactiveCount = $assignedUsers->where('estatus', 0)->count();
@endphp

<div class="org-master-wrapper">

    {{-- ── Encabezado Principal ── --}}
    <div class="org-top-header">
        <div class="org-brand">
            <img src="{{ asset('images/lg_saavedra.png') }}" alt="GIS Logo" class="org-brand-logo">
            <div class="org-brand-titles">
                <h1>ORGANIGRAMA</h1>
                <span>Grupo Industrial Saavedra &bull; Estructura Organizacional y Jerárquica</span>
            </div>
        </div>

        <div class="org-header-controls">
            <button type="button" id="btn-export-pdf" class="btn-header-action btn-action-pdf" title="Descargar organigrama en PDF">
                📄 Descargar PDF
            </button>
            <a href="{{ route('users') }}" class="btn-header-action btn-action-table" title="Ver tabla de usuarios">
                📋 Tabla de Usuarios
            </a>
            <a href="{{ route('createUser') }}" class="btn-header-action btn-action-create" title="Registrar nuevo usuario">
                ➕ Registrar Personal
            </a>
        </div>
    </div>

    {{-- ── Barra de KPIs Resumen ── --}}
    <div class="org-kpi-bar">
        <div class="org-kpi-pill pill-total">
            <span class="kpi-title">👥 Personal Asignado</span>
            <span class="kpi-num" id="kpi-total-val">{{ $totalCount }}</span>
        </div>
        <div class="org-kpi-pill pill-active">
            <span class="kpi-title">🟢 Activos</span>
            <span class="kpi-num" id="kpi-active-val">{{ $activeCount }}</span>
        </div>
        <div class="org-kpi-pill pill-inactive">
            <span class="kpi-title">🔴 Inactivos / Faltantes</span>
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
            <button type="button" class="btn-zoom" id="btn-zoom-fit" title="Ajustar organigrama a la pantalla" style="font-size: 0.76rem; width: auto; padding: 0 10px; gap: 4px; font-weight: 800;">
                🎯 Ajustar
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
                                            <img src="{{ asset('images/gerente.png') }}" alt="Gerente" class="org-avatar-img">
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
                                                        return trim($u->puesto ?: 'Operativo');
                                                    })->sortBy(function($group, $puestoName) {
                                                        $p = strtoupper($puestoName);
                                                        if (str_contains($p, 'TORNO')) return 1;
                                                        if (str_contains($p, 'CENTRO')) return 2;
                                                        if (str_contains($p, 'AYUDANTE')) return 3;
                                                        if (str_contains($p, 'HERRAMENTISTA')) return 4;
                                                        if (str_contains($p, 'SOLDADOR')) return 5;
                                                        if (str_contains($p, 'INSPECC')) return 6;
                                                        if (str_contains($p, 'ALMACEN')) return 7;
                                                        if (str_contains($p, 'MANTENIMIENTO')) return 8;
                                                        if (str_contains($p, 'BECARI')) return 9;
                                                        if (str_contains($p, 'CHOFER')) return 10;
                                                        if (str_contains($p, 'INTENDENCIA')) return 11;
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
