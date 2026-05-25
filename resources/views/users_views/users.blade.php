@extends('layouts.appMenu')

@section('background-body', 'background-image:url("' . asset("images/fondoLogin.jpg") . '")')

@section('content')
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="{{ url('images/lg_saavedra.png') }}?v=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/viewUsers.css', 'resources/js/viewUsers.js'])
</head>

<div class="container1">

    {{-- ── Encabezado ─────────────────────────────────────────── --}}
    <div class="page-header">
        <h1>Gestión de Usuarios</h1>
        <span class="record-count" id="record-count">{{ $users->count() }} usuarios</span>
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
                    <th>Fecha de registro</th>
                    <th>Estatus</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="users-tbody">
                @forelse($users as $user)
                @php
                    $rolLabel = match((string)$user->perfil) {
                        '1' => 'Administrador',
                        '2' => 'Operador',
                        '3' => 'Master',
                        '4' => 'Calidad',
                        '5' => 'Almacén',
                        default => $user->perfil,
                    };
                    $rolClass = match((string)$user->perfil) {
                        '1' => 'badge-admin',
                        '2' => 'badge-operador',
                        '3' => 'badge-master',
                        '4' => 'badge-calidad',
                        '5' => 'badge-almacen',
                        default => 'badge-default',
                    };
                @endphp
                <tr data-rol="{{ $rolLabel }}" data-status="{{ $user->estatus ? 'Activo' : 'Inactivo' }}">
                    {{-- Rol --}}
                    <td><span class="badge-rol {{ $rolClass }}">{{ $rolLabel }}</span></td>

                    {{-- Matrícula + Nombre --}}
                    <td>
                        <span class="user-matricula" style="font-size:.9rem;font-weight:600;color:#1e293b;">{{ $user->matricula }}</span>
                    </td>
                    <td>
                        <span class="user-name">{{ $user->name }}</span>
                    </td>

                    {{-- Fecha --}}
                    <td class="date-cell">
                        {{ $user->created_at ? $user->created_at->format('d/m/Y') : '—' }}
                    </td>

                    {{-- Estatus --}}
                    <td>
                        <span class="badge-status {{ $user->estatus ? 'badge-activo' : 'badge-inactivo' }}">
                            {{ $user->estatus ? 'Activo' : 'Inactivo' }}
                        </span>
                    </td>

                    {{-- Acciones --}}
                    <td>
                        <div class="actions-cell">
                            {{-- Activar / Inactivar --}}
                            <form action="{{ route($user->estatus ? 'baja_usuario' : 'alta_usuario', $user->id) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn-action {{ $user->estatus ? 'btn-inactivate' : 'btn-activate' }}">
                                    {{ $user->estatus ? '⏸ Inactivar' : '▶ Activar' }}
                                </button>
                            </form>

                            {{-- Eliminar --}}
                            <form
                                action="{{ route('eliminar_usuario', $user->id) }}"
                                method="POST"
                                style="display:inline;"
                                onsubmit="return confirm('¿Seguro que deseas eliminar al usuario {{ addslashes($user->name) }}? Esta acción no se puede deshacer.');"
                            >
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-action btn-delete">
                                    🗑 Eliminar
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr class="empty-row">
                    <td colspan="6">No hay usuarios registrados.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

<script>
    // ── Filtrado en tiempo real ──────────────────────────────────
    const searchInput  = document.getElementById('search-input');
    const roleFilter   = document.getElementById('role-filter');
    const statusFilter = document.getElementById('status-filter');
    const tbody        = document.getElementById('users-tbody');
    const countBadge   = document.getElementById('record-count');

    function filterTable() {
        const query  = searchInput.value.toLowerCase().trim();
        const role   = roleFilter.value;
        const status = statusFilter.value;

        let visible = 0;

        tbody.querySelectorAll('tr[data-rol]').forEach(row => {
            const text   = row.innerText.toLowerCase();
            const rowRol = row.dataset.rol;
            const rowSt  = row.dataset.status;

            const matchText   = !query  || text.includes(query);
            const matchRole   = role   === 'todos' || rowRol === role;
            const matchStatus = status === 'todos' || rowSt  === status;

            const show = matchText && matchRole && matchStatus;
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        countBadge.textContent = visible + ' usuario' + (visible !== 1 ? 's' : '');
    }

    searchInput.addEventListener('input',  filterTable);
    roleFilter.addEventListener('change',  filterTable);
    statusFilter.addEventListener('change', filterTable);

    // ── Limpiar filtros ──────────────────────────────────────────
    document.getElementById('btn-clear-filters').addEventListener('click', function () {
        searchInput.value   = '';
        roleFilter.value    = 'todos';
        statusFilter.value  = 'todos';
        filterTable();
    });
</script>

@endsection
