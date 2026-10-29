@extends('layouts.appMenu')

@section('background-body', 'background-image:url("' . asset("images/fondos/fondoLogin.jpg") . '")')

@section('content')
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/x-icon" href="{{ asset('images/logos/favicon.ico') }}?v=3">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/viewUsers.css', 'resources/js/viewUsers.js'])
</head>

<div class="container1">

    {{-- ── Encabezado ─────────────────────────────────────────── --}}
    <div class="page-header">
        <div class="header-left-group">
            <img src="{{ asset('images/Color_Icons/Usuarios/usuario.png') }}" alt="Gestión de Usuarios" class="header-module-icon">
            <div class="header-title-content">
                <div class="header-title-row">
                    <h1>Gestión de Usuarios</h1>
                    <span class="record-count" id="record-count">{{ $totalUsers ?? $users->count() }} usuarios</span>
                </div>
                <span class="header-subtitle">Control de personal, asignación operativa y permisos de acceso</span>
            </div>
        </div>
        <div class="header-actions">
            <a href="{{ route('users.organigrama') }}" class="btn-header-nav btn-nav-organigrama" title="Ver organigrama jerárquico">
                <img src="{{ asset('images/Color_Icons/Generales/organigrama.png') }}" alt="Organigrama" class="btn-nav-icon">
                <span>Organigrama</span>
            </a>
            <a href="{{ route('createUser') }}" class="btn-header-nav btn-nav-registrar" title="Registrar nuevo usuario">
                <img src="{{ asset('images/Color_Icons/Generales/anadir.png') }}" alt="Registrar" class="btn-nav-icon">
                <span>Registrar Usuario</span>
            </a>
        </div>
    </div>

    {{-- ── Alertas ─────────────────────────────────────────────── --}}
    @if(session('success'))
        <div class="alert-success">
            ✔ {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert-error">
            ✖ {{ session('error') }}
        </div>
    @endif

    {{-- ── Barra de herramientas ───────────────────────────────── --}}
    <div class="toolbar">
        <div class="search-wrapper">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
            </svg>
            <input
                type="text"
                id="search-input"
                class="search-input"
                placeholder="Buscar por matrícula o nombre…"
                autocomplete="off"
            >
        </div>

        <select id="role-filter" class="role-select-toolbar">
            <option value="todos">Todos los roles</option>
            <option value="Administrador">Administrador</option>
            <option value="Supervisor">Supervisor</option>
            <option value="Operador">Operador</option>
            <option value="Master">Master</option>
            <option value="Calidad">Calidad</option>
            <option value="Almacén">Almacén</option>
        </select>

        <select id="status-filter" class="role-select-toolbar">
            <option value="todos">Todos los estatus</option>
            <option value="Activo">Activo</option>
            <option value="Inactivo">Inactivo</option>
        </select>

        <button id="btn-clear-filters" class="btn-clear-filters" title="Limpiar filtros">
            ✕ Limpiar filtros
        </button>
    </div>

    {{-- ── Tabla ───────────────────────────────────────────────── --}}
    <div class="table-wrapper">
        <table id="users-table">
            <thead>
                <tr>
                    <th>Rol</th>
                    <th>Matrícula</th>
                    <th>Usuario</th>
                    <th>Planta</th>
                    <th>Turno</th>
                    <th>Área</th>
                    <th>Puesto</th>
                    <th>Fecha de registro</th>
                    <th>Estatus</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="users-tbody">
                @forelse($users as $user)
                @php
                    $perfilStr = (string)$user->perfil;
                    $rolLabel = match($perfilStr) {
                        '1', 'Administrador'     => 'Administrador',
                        '2', 'Operador'          => 'Operador',
                        '3', 'Master', 'Maestro' => 'Master',
                        '4', 'Calidad'           => 'Calidad',
                        '5', 'Almacén', 'Almacen'=> 'Almacén',
                        '6', 'Supervisor'        => 'Supervisor',
                        default                  => $user->perfil,
                    };
                    $rolClass = match($rolLabel) {
                        'Administrador' => 'badge-admin',
                        'Operador'      => 'badge-operador',
                        'Master'        => 'badge-master',
                        'Calidad'       => 'badge-calidad',
                        'Almacén'       => 'badge-almacen',
                        'Supervisor'    => 'badge-supervisor',
                        default         => 'badge-default',
                    };
                    $rolIcon = match($rolLabel) {
                        'Administrador' => 'images/Light_Icons/Administracion-Light.png',
                        'Operador'      => 'images/Light_Icons/Operador-Light.png',
                        'Master'        => 'images/Light_Icons/Master-Light.png',
                        'Calidad'       => 'images/Light_Icons/Calidad-Light.png',
                        'Almacén'       => 'images/Light_Icons/Almacen-Light.png',
                        'Supervisor'    => 'images/Light_Icons/Supervision-Light.png',
                        default         => null,
                    };
                @endphp
                <tr id="user-row-{{ $user->id }}"
                    data-id="{{ $user->id }}"
                    data-rol="{{ $rolLabel }}"
                    data-status="{{ $user->estatus ? 'Activo' : 'Inactivo' }}"
                    data-search="{{ strtolower($user->matricula . ' ' . $user->name . ' ' . ($user->planta ?? '') . ' ' . ($user->turno ?? '') . ' ' . ($user->area ?? '') . ' ' . ($user->puesto ?? '')) }}">
                    {{-- Rol (Icono superior en círculo temático + Texto debajo) --}}
                    <td>
                        <div class="role-cell-container">
                            @if($rolIcon)
                                <div class="role-icon-circle {{ $rolClass }}" title="{{ $rolLabel }}">
                                    <img src="{{ icon($rolIcon) }}" alt="{{ $rolLabel }}" class="role-img-icon">
                                </div>
                            @endif
                            <span class="badge-rol {{ $rolClass }}">{{ $rolLabel }}</span>
                        </div>
                    </td>

                    {{-- Matrícula --}}
                    <td>
                        <span class="user-matricula use-font-size-9rem use-font-weight-600 use-color-1e293b">{{ $user->matricula }}</span>
                    </td>

                    {{-- Usuario --}}
                    <td>
                        <span class="user-name">{{ $user->name }}</span>
                    </td>

                    {{-- Planta --}}
                    <td>
                        <span class="badge-planta">{{ $user->planta ?: '—' }}</span>
                    </td>

                    {{-- Turno --}}
                    <td>
                        <span class="badge-turno">{{ $user->turno ?: '—' }}</span>
                    </td>

                    {{-- Área --}}
                    <td>
                        <span>{{ $user->area ?: '—' }}</span>
                    </td>

                    {{-- Puesto --}}
                    <td>
                        <span>{{ $user->puesto ?: '—' }}</span>
                    </td>

                    {{-- Fecha --}}
                    <td class="date-cell">
                        {{ $user->created_at ? $user->created_at->format('d/m/Y') : '—' }}
                    </td>

                    {{-- Estatus (Icono superior + Texto debajo) --}}
                    <td>
                        <div class="status-cell-container">
                            <img src="{{ asset($user->estatus ? 'images/Color_Icons/Generales/activar.png' : 'images/Color_Icons/Generales/inactivar.png') }}" alt="{{ $user->estatus ? 'Activo' : 'Inactivo' }}" class="status-img-icon">
                            <span class="badge-status {{ $user->estatus ? 'badge-activo' : 'badge-inactivo' }}">
                                {{ $user->estatus ? 'Activo' : 'Inactivo' }}
                            </span>
                        </div>
                    </td>

                    {{-- Acciones --}}
                    <td>
                        <div class="actions-cell">
                            {{-- Editar --}}
                            <button
                                type="button"
                                class="btn-action-icon btn-action-edit"
                                onclick="openEditModal({{ json_encode([
                                    'id' => $user->id,
                                    'matricula' => $user->matricula,
                                    'nombre' => $user->nombre,
                                    'a_paterno' => $user->a_paterno,
                                    'a_materno' => $user->a_materno,
                                    'perfil' => (string)$user->perfil,
                                    'planta' => $user->planta ?? '',
                                    'turno' => $user->turno ?? '',
                                    'area' => $user->area ?? '',
                                    'puesto' => $user->puesto ?? '',
                                ]) }})"
                                title="Editar usuario"
                            >
                                <img src="{{ asset('images/Color_Icons/Usuarios/editaruser.png') }}" alt="Editar" class="action-img-icon">
                            </button>

                            {{-- Activar / Inactivar --}}
                            <form action="{{ route($user->estatus ? 'baja_usuario' : 'alta_usuario', $user->id) }}" method="POST" class="use-display-inline" onsubmit="event.preventDefault(); toggleUserStatus({{ $user->id }}, this);">
                                @csrf
                                <button type="submit" class="btn-action-icon {{ $user->estatus ? 'btn-action-inactivate' : 'btn-action-activate' }}" title="{{ $user->estatus ? 'Inactivar usuario' : 'Activar usuario' }}">
                                    <img src="{{ asset($user->estatus ? 'images/Color_Icons/Generales/inactivar.png' : 'images/Color_Icons/Generales/activar.png') }}" alt="{{ $user->estatus ? 'Inactivar' : 'Activar' }}" class="action-img-icon">
                                </button>
                            </form>

                            {{-- Eliminar --}}
                            <form
                                action="{{ route('eliminar_usuario', $user->id) }}"
                                method="POST"
                                class="use-display-inline"
                                onsubmit="event.preventDefault(); deleteUser({{ $user->id }}, '{{ addslashes($user->name) }}', this);"
                            >
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-action-icon btn-action-delete" title="Eliminar usuario">
                                    <img src="{{ asset('images/Color_Icons/Generales/delete.png') }}" alt="Eliminar" class="action-img-icon">
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr class="empty-row">
                    <td colspan="10">No hay usuarios registrados.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ── Paginación Reactiva en Cliente ── --}}
    <nav class="gis-cursor-pagination users-client-pagination" id="users-pagination" aria-label="Navegación de usuarios">
        <button type="button" class="gis-pagination-btn" id="users-prev-btn" title="Página anterior">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="15 18 9 12 15 6"></polyline>
            </svg>
            <span>Anterior</span>
        </button>

        <div class="gis-pagination-info">
            <span class="gis-pagination-badge" id="users-page-info">
                Mostrando 1 - 25 de {{ $users->count() }} usuarios
            </span>
        </div>

        <button type="button" class="gis-pagination-btn" id="users-next-btn" title="Página siguiente">
            <span>Siguiente</span>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="9 18 15 12 9 6"></polyline>
            </svg>
        </button>
    </nav>

</div>

{{-- ── MODAL DE EDICIÓN DE USUARIO ── --}}
<div id="edit-user-modal" class="modal-overlay">
    <div class="modal-card">
        <div class="modal-header">
            <h2 id="modal-title" class="modal-title">EDITAR USUARIO</h2>
            <button type="button" class="modal-close-btn" onclick="closeEditModal()" title="Cerrar">&times;</button>
        </div>

        <form id="edit-user-form" action="" method="POST">
            @csrf
            
            {{-- 1. Información Personal --}}
            <div class="modal-section">
                <div class="modal-section-title">1. INFORMACIÓN PERSONAL</div>
                <div class="modal-grid-3">
                    <div class="modal-form-group">
                        <label class="modal-label" for="edit_nombre">Nombre(s) *</label>
                        <input type="text" id="edit_nombre" name="nombre" class="modal-input" required>
                    </div>
                    <div class="modal-form-group">
                        <label class="modal-label" for="edit_a_paterno">Apellido Paterno *</label>
                        <input type="text" id="edit_a_paterno" name="a_paterno" class="modal-input" required>
                    </div>
                    <div class="modal-form-group">
                        <label class="modal-label" for="edit_a_materno">Apellido Materno *</label>
                        <input type="text" id="edit_a_materno" name="a_materno" class="modal-input" required>
                    </div>
                </div>
            </div>

            {{-- 2. Credenciales y Perfil --}}
            <div class="modal-section">
                <div class="modal-section-title">2. CREDENCIALES Y PERFIL</div>
                <div class="modal-grid-2">
                    <div class="modal-form-group">
                        <label class="modal-label" for="edit_matricula">Matrícula (Solo lectura)</label>
                        <input type="text" id="edit_matricula" class="modal-input" disabled style="background:#e2e8f0; cursor:not-allowed;">
                    </div>
                    <div class="modal-form-group">
                        <label class="modal-label" for="edit_perfil">Tipo de Perfil (Rol) *</label>
                        <select id="edit_perfil" name="perfil" class="modal-select" required>
                            <option value="1">Administrador</option>
                            <option value="6">Supervisor</option>
                            <option value="2">Operador</option>
                            <option value="3">Master</option>
                            <option value="4">Calidad</option>
                            <option value="5">Almacén</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- 3. Ubicación y Asignación Operativa --}}
            <div class="modal-section">
                <div class="modal-section-title">3. UBICACIÓN Y ASIGNACIÓN OPERATIVA</div>
                <div class="modal-grid-4">
                    <div class="modal-form-group">
                        <label class="modal-label" for="edit_planta">Planta</label>
                        <select id="edit_planta" name="planta" class="modal-select">
                            <option value="">— Sin asignar —</option>
                            <option value="TECÁMAC">TECÁMAC</option>
                            <option value="CDMX">CDMX</option>
                        </select>
                    </div>
                    <div class="modal-form-group">
                        <label class="modal-label" for="edit_turno">Turno</label>
                        <select id="edit_turno" name="turno" class="modal-select">
                            <option value="">— Sin asignar —</option>
                            <option value="MIXTO">MIXTO</option>
                            <option value="MATUTINO">MATUTINO</option>
                            <option value="VESPERTINO">VESPERTINO</option>
                            <option value="NOCTURNO">NOCTURNO</option>
                        </select>
                    </div>
                    <div class="modal-form-group">
                        <label class="modal-label" for="edit_area">Área</label>
                        <select id="edit_area" name="area" class="modal-select">
                            <option value="">— Sin asignar —</option>
                            <option value="JEFE PLANTA">JEFE PLANTA</option>
                            <option value="SUPERVISOR">SUPERVISOR</option>
                            <option value="PRODUCCIÓN">PRODUCCIÓN</option>
                            <option value="SOLDADURA">SOLDADURA</option>
                            <option value="CALIDAD">CALIDAD</option>
                            <option value="MANTENIMIENTO">MANTENIMIENTO</option>
                            <option value="SOFTWARE">SOFTWARE</option>
                            <option value="ALMACÉN">ALMACÉN</option>
                            <option value="PROGRAMACIÓN">PROGRAMACIÓN</option>
                            <option value="ADMINISTRACIÓN">ADMINISTRACIÓN</option>
                        </select>
                    </div>
                    <div class="modal-form-group">
                        <label class="modal-label" for="edit_puesto">Puesto</label>
                        <select id="edit_puesto" name="puesto" class="modal-select">
                            <option value="">Seleccione área primero</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-modal-cancel" onclick="closeEditModal()">Cancelar</button>
                <button type="submit" class="btn-modal-save">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<script>
    // ── Matriz de Puestos por Área ───────────────────────────────
    const puestosPorAreaModal = {
        'JEFE PLANTA': [
            'JEFE DE PLANTA',
            'GERENTE DE PLANTA',
            'SUPERVISOR'
        ],
        'SUPERVISOR': [
            'SUPERVISOR DE PRODUCCIÓN',
            'SUPERVISOR DE CALIDAD',
            'SUPERVISOR DE SOLDADURA',
            'SUPERVISOR DE MANTENIMIENTO',
            'SUPERVISOR DE SOFTWARE',
            'SUPERVISOR DE PROGRAMACIÓN',
            'SUPERVISOR DE ALMACÉN',
            'SUPERVISOR DE ADMINISTRACIÓN',
            'SUPERVISOR GENERAL',
            'SUPERVISOR DE PLANTA'
        ],
        'PRODUCCIÓN': [
            'SUPERVISOR DE PRODUCCIÓN',
            'HERRAMENTISTA',
            'AYUDANTE GENERAL',
            'CENTRO DE MAQUINADOS',
            'OPERADOR TORNO CNC',
            'BECARIO',
            'SUPERVISOR'
        ],
        'SOLDADURA': [
            'SUPERVISOR DE SOLDADURA',
            'SOLDADOR',
            'SUPERVISOR'
        ],
        'CALIDAD': [
            'SUPERVISOR DE CALIDAD',
            'INSPECCIÓN',
            'SUPERVISOR'
        ],
        'MANTENIMIENTO': [
            'SUPERVISOR DE MANTENIMIENTO',
            'AUX MANTENIMIENTO',
            'SUPERVISOR'
        ],
        'SOFTWARE': [
            'SUPERVISOR DE SOFTWARE',
            'SUPERVISOR DE SISTEMAS',
            'BECARIO',
            'SUPERVISOR'
        ],
        'ALMACÉN': [
            'SUPERVISOR DE ALMACÉN',
            'ALMACÉN',
            'SUPERVISOR'
        ],
        'PROGRAMACIÓN': [
            'SUPERVISOR DE PROGRAMACIÓN',
            'BECARIO',
            'SUPERVISOR'
        ],
        'ADMINISTRACIÓN': [
            'SUPERVISOR DE ADMINISTRACIÓN',
            'BECARIO',
            'CHOFER',
            'INTENDENCIA',
            'SUPERVISOR'
        ]
    };

    const modalOverlay = document.getElementById('edit-user-modal');
    const editForm     = document.getElementById('edit-user-form');
    const modalTitle   = document.getElementById('modal-title');
    const editArea     = document.getElementById('edit_area');
    const editPuesto   = document.getElementById('edit_puesto');

    function actualizarPuestosModal(selectedArea, defaultPuesto = '') {
        editPuesto.innerHTML = '';

        if (!selectedArea || !puestosPorAreaModal[selectedArea]) {
            const opt = document.createElement('option');
            opt.value = '';
            opt.textContent = 'Seleccione área primero';
            editPuesto.appendChild(opt);
            return;
        }

        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = '— Seleccionar puesto —';
        if (!defaultPuesto) {
            placeholder.selected = true;
        }
        editPuesto.appendChild(placeholder);

        const puestos = puestosPorAreaModal[selectedArea];
        puestos.forEach(puesto => {
            const opt = document.createElement('option');
            opt.value = puesto;
            opt.textContent = puesto;
            if (defaultPuesto && defaultPuesto === puesto) {
                opt.selected = true;
            }
            editPuesto.appendChild(opt);
        });
    }

    editArea.addEventListener('change', function () {
        actualizarPuestosModal(this.value);
    });

    function openEditModal(userData) {
        editForm.action = '{{ url("/users") }}/' + userData.id;
        modalTitle.textContent = 'EDITAR USUARIO: ' + userData.matricula + ' — ' + userData.nombre;

        document.getElementById('edit_matricula').value = userData.matricula || '';
        document.getElementById('edit_nombre').value    = userData.nombre || '';
        document.getElementById('edit_a_paterno').value = userData.a_paterno || '';
        document.getElementById('edit_a_materno').value = userData.a_materno || '';
        document.getElementById('edit_perfil').value    = userData.perfil || '2';
        document.getElementById('edit_planta').value    = userData.planta || '';
        document.getElementById('edit_turno').value     = userData.turno || '';

        editArea.value = userData.area || '';
        actualizarPuestosModal(userData.area, userData.puesto || '');

        modalOverlay.classList.add('active');
    }

    function closeEditModal() {
        modalOverlay.classList.remove('active');
    }

    modalOverlay.addEventListener('click', function (e) {
        if (e.target === modalOverlay) {
            closeEditModal();
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modalOverlay.classList.contains('active')) {
            closeEditModal();
        }
    });

    // ── Helper para token CSRF ────────────────────────────────────────────────
    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            || document.querySelector('input[name="_token"]')?.value
            || '';
    }

    // ── Operación Asíncrona: Activar / Inactivar Usuario ───────────────────────
    async function toggleUserStatus(userId, formElement) {
        const btn = formElement.querySelector('button');
        if (!btn || btn.disabled) return;

        btn.disabled = true;
        btn.style.opacity = '0.5';

        try {
            const response = await fetch(formElement.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (data.success) {
                if (window.gisToast) window.gisToast(data.message, 'success');

                const row = document.getElementById(`user-row-${userId}`);
                if (row) {
                    const isActivo = data.estatus === 1;
                    row.dataset.status = isActivo ? 'Activo' : 'Inactivo';

                    // Actualizar celda de estatus
                    const statusCell = row.querySelector('.status-cell-container');
                    if (statusCell) {
                        const iconSrc = isActivo ? '{{ asset("images/Color_Icons/Generales/activar.png") }}' : '{{ asset("images/Color_Icons/Generales/inactivar.png") }}';
                        const badgeClass = isActivo ? 'badge-activo' : 'badge-inactivo';
                        const text = isActivo ? 'Activo' : 'Inactivo';
                        statusCell.innerHTML = `
                            <img src="${iconSrc}" alt="${text}" class="status-img-icon">
                            <span class="badge-status ${badgeClass}">${text}</span>
                        `;
                    }

                    // Actualizar botón de acción (alternar ruta, título, icono)
                    const newAction = isActivo ? '{{ url("/baja-usuario") }}/' + userId : '{{ url("/alta-usuario") }}/' + userId;
                    formElement.action = newAction;
                    const btnIcon = isActivo ? '{{ asset("images/Color_Icons/Generales/inactivar.png") }}' : '{{ asset("images/Color_Icons/Generales/activar.png") }}';
                    btn.className = `btn-action-icon ${isActivo ? 'btn-action-inactivate' : 'btn-action-activate'}`;
                    btn.title = isActivo ? 'Inactivar usuario' : 'Activar usuario';
                    btn.innerHTML = `<img src="${btnIcon}" alt="${isActivo ? 'Inactivar' : 'Activar'}" class="action-img-icon">`;
                }
            } else {
                if (window.gisToast) window.gisToast(data.message || 'Error al cambiar estatus', 'error');
            }
        } catch (err) {
            console.error('Error toggleUserStatus:', err);
            if (window.gisToast) window.gisToast('Error de red al actualizar estatus.', 'error');
        } finally {
            btn.disabled = false;
            btn.style.opacity = '';
        }
    }

    // ── Operación Asíncrona: Eliminar Usuario ──────────────────────────────────
    async function deleteUser(userId, userName, formElement) {
        const confirmed = await (window.gisConfirm ? window.gisConfirm({
            title: '¿Eliminar usuario?',
            text: `Se eliminará permanentemente la cuenta de ${userName} y se revocarán todos sus accesos al sistema.`,
            confirmButtonText: 'Eliminar',
            confirmButtonColor: '#b30404'
        }) : Promise.resolve({ isConfirmed: confirm(`¿Eliminar al usuario ${userName}?`) }));

        if (!confirmed || !confirmed.isConfirmed) return;

        const btn = formElement.querySelector('button');
        if (btn) { btn.disabled = true; btn.style.opacity = '0.5'; }

        try {
            const response = await fetch(formElement.action, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (data.success) {
                if (window.gisToast) window.gisToast(data.message, 'success');
                const row = document.getElementById(`user-row-${userId}`);
                if (row) {
                    row.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
                    row.style.opacity = '0';
                    row.style.transform = 'scale(0.96)';
                    setTimeout(() => {
                        const idxAll = allUserRows.indexOf(row);
                        if (idxAll > -1) allUserRows.splice(idxAll, 1);
                        const idxMatch = matchingUserRows.indexOf(row);
                        if (idxMatch > -1) matchingUserRows.splice(idxMatch, 1);
                        row.remove();
                        renderUsersPage();
                    }, 260);
                }
            } else {
                if (window.gisToast) window.gisToast(data.message || 'No se pudo eliminar el usuario', 'error');
            }
        } catch (err) {
            console.error('Error deleteUser:', err);
            if (window.gisToast) window.gisToast('Error de red al procesar la eliminación.', 'error');
        } finally {
            if (btn) { btn.disabled = false; btn.style.opacity = ''; }
        }
    }

    // ── Operación Asíncrona: Guardar Modificación en Modal ─────────────────────
    editForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        const submitBtn = editForm.querySelector('button[type="submit"]');
        if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = 'Guardando…'; }

        try {
            const formData = new FormData(editForm);
            const response = await fetch(editForm.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (data.success && data.user) {
                if (window.gisToast) window.gisToast(data.message, 'success');
                closeEditModal();

                const row = document.getElementById(`user-row-${data.user.id}`);
                if (row) {
                    // Actualizar Nombre
                    const nameSpan = row.querySelector('.user-name');
                    if (nameSpan) nameSpan.textContent = data.user.nombre_completo;

                    // Actualizar Rol
                    const roleContainer = row.querySelector('.role-cell-container');
                    const rolMap = {
                        '1': { label: 'Administrador', class: 'badge-admin', icon: '{{ icon("images/Light_Icons/Administracion-Light.png") }}' },
                        '2': { label: 'Operador', class: 'badge-operador', icon: '{{ icon("images/Light_Icons/Operador-Light.png") }}' },
                        '3': { label: 'Master', class: 'badge-master', icon: '{{ icon("images/Light_Icons/Master-Light.png") }}' },
                        '4': { label: 'Calidad', class: 'badge-calidad', icon: '{{ icon("images/Light_Icons/Calidad-Light.png") }}' },
                        '5': { label: 'Almacén', class: 'badge-almacen', icon: '{{ icon("images/Light_Icons/Almacen-Light.png") }}' },
                        '6': { label: 'Supervisor', class: 'badge-supervisor', icon: '{{ icon("images/Light_Icons/Supervision-Light.png") }}' }
                    };
                    const rInfo = rolMap[data.user.perfil] || { label: data.user.perfil, class: 'badge-default', icon: null };
                    row.dataset.rol = rInfo.label;
                    if (roleContainer) {
                        roleContainer.innerHTML = `
                            ${rInfo.icon ? `<div class="role-icon-circle ${rInfo.class}" title="${rInfo.label}"><img src="${rInfo.icon}" alt="${rInfo.label}" class="role-img-icon"></div>` : ''}
                            <span class="badge-rol ${rInfo.class}">${rInfo.label}</span>
                        `;
                    }

                    // Actualizar Celdas Planta, Turno, Área, Puesto
                    const cells = row.querySelectorAll('td');
                    if (cells[3]) cells[3].innerHTML = `<span class="badge-planta">${data.user.planta}</span>`;
                    if (cells[4]) cells[4].innerHTML = `<span class="badge-turno">${data.user.turno}</span>`;
                    if (cells[5]) cells[5].innerHTML = `<span>${data.user.area}</span>`;
                    if (cells[6]) cells[6].innerHTML = `<span>${data.user.puesto}</span>`;

                    // Actualizar búsqueda en memoria
                    row.dataset.search = (data.user.matricula + ' ' + data.user.nombre_completo + ' ' + data.user.planta + ' ' + data.user.turno + ' ' + data.user.area + ' ' + data.user.puesto).toLowerCase();

                    // Actualizar botón editar
                    const editBtn = row.querySelector('.btn-action-edit');
                    if (editBtn) {
                        editBtn.onclick = () => openEditModal(data.user);
                    }
                }
            } else {
                if (window.gisToast) window.gisToast(data.message || 'Error al actualizar usuario', 'error');
            }
        } catch (err) {
            console.error('Error editForm submit:', err);
            if (window.gisToast) window.gisToast('Error de red al actualizar usuario.', 'error');
        } finally {
            if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = 'Guardar Cambios'; }
        }
    });

    // ── Filtrado y Paginación en tiempo real (Optimizado O(1) sin Layout Reflow) ──
    const searchInput  = document.getElementById('search-input');
    const roleFilter   = document.getElementById('role-filter');
    const statusFilter = document.getElementById('status-filter');
    const tbody        = document.getElementById('users-tbody');
    const countBadge   = document.getElementById('record-count');
    const prevBtn      = document.getElementById('users-prev-btn');
    const nextBtn      = document.getElementById('users-next-btn');
    const pageInfo     = document.getElementById('users-page-info');

    const USERS_PER_PAGE = 25;
    let userCurrentPage  = 1;
    const allUserRows    = Array.from(tbody.querySelectorAll('tr[data-rol]'));
    let matchingUserRows = [...allUserRows];

    function renderUsersPage() {
        const totalMatches = matchingUserRows.length;
        const totalPages   = Math.max(1, Math.ceil(totalMatches / USERS_PER_PAGE));
        if (userCurrentPage > totalPages) userCurrentPage = totalPages;
        if (userCurrentPage < 1) userCurrentPage = 1;

        const startIdx = (userCurrentPage - 1) * USERS_PER_PAGE;
        const endIdx   = Math.min(startIdx + USERS_PER_PAGE, totalMatches);

        allUserRows.forEach(row => { row.style.display = 'none'; });

        for (let i = startIdx; i < endIdx; i++) {
            matchingUserRows[i].style.display = '';
        }

        countBadge.textContent = totalMatches + ' usuario' + (totalMatches !== 1 ? 's' : '');

        if (pageInfo) {
            pageInfo.textContent = totalMatches === 0
                ? '0 usuarios encontrados'
                : `Mostrando ${(startIdx + 1)} - ${endIdx} de ${totalMatches} usuarios (Página ${userCurrentPage} de ${totalPages})`;
        }

        if (prevBtn) {
            const isPrevDisabled = userCurrentPage <= 1;
            prevBtn.disabled = isPrevDisabled;
            prevBtn.classList.toggle('gis-pagination-disabled', isPrevDisabled);
        }

        if (nextBtn) {
            const isNextDisabled = userCurrentPage >= totalPages;
            nextBtn.disabled = isNextDisabled;
            nextBtn.classList.toggle('gis-pagination-disabled', isNextDisabled);
        }
    }

    function filterTable() {
        const query  = searchInput.value.toLowerCase().trim();
        const role   = roleFilter.value;
        const status = statusFilter.value;

        matchingUserRows = allUserRows.filter(row => {
            const text = row.dataset.search || '';
            const rowRol = row.dataset.rol;
            const rowSt  = row.dataset.status;

            const matchText   = !query  || text.includes(query);
            const matchRole   = role   === 'todos' || rowRol === role;
            const matchStatus = status === 'todos' || rowSt  === status;

            return matchText && matchRole && matchStatus;
        });

        userCurrentPage = 1;
        renderUsersPage();
    }

    let filterDebounceTimer;
    searchInput.addEventListener('input', function() {
        clearTimeout(filterDebounceTimer);
        filterDebounceTimer = setTimeout(filterTable, 120);
    });
    roleFilter.addEventListener('change',  filterTable);
    statusFilter.addEventListener('change', filterTable);

    prevBtn?.addEventListener('click', () => {
        if (userCurrentPage > 1) {
            userCurrentPage--;
            renderUsersPage();
        }
    });

    nextBtn?.addEventListener('click', () => {
        const totalPages = Math.max(1, Math.ceil(matchingUserRows.length / USERS_PER_PAGE));
        if (userCurrentPage < totalPages) {
            userCurrentPage++;
            renderUsersPage();
        }
    });

    // ── Limpiar filtros ──────────────────────────────────────────
    document.getElementById('btn-clear-filters').addEventListener('click', function () {
        searchInput.value   = '';
        roleFilter.value    = 'todos';
        statusFilter.value  = 'todos';
        filterTable();
    });

    // Renderizado inicial reactivo
    renderUsersPage();
</script>

@endsection
