@extends('layouts.appMenu')
@section('head')
<title>Registrar usuario | Grupo Industrial Saavedra</title>
<link rel="icon" type="image/x-icon" href="{{ asset('images/logos/favicon.ico') }}?v=3">
@vite(['resources/css/users_views/createUser.css'])
@endsection

@section('background-body', 'background-image:url("' . asset("images/fondos/fondoLogin.jpg") . '")')

@section('content')
<div class="create-user-container">

    {{-- Encabezado Principal --}}
    <div class="page-header">
        <div class="header-left">
            <img src="{{ asset('images/Color_Icons/Generales/anadir.png') }}" alt="Registrar Usuario" class="create-header-icon">
            <div class="header-title-group">
                <h1>REGISTRO DE USUARIO</h1>
                <span class="subtitle-text">Alta de personal y asignación operativa en planta</span>
            </div>
        </div>
        <a href="{{ route('users') }}" class="btn-volver">
            <img src="{{ asset('images/Color_Icons/Usuarios/usuario.png') }}" alt="Gestión de Usuarios" style="width:18px; height:18px; object-fit:contain; vertical-align:middle; margin-right:6px;">
            Gestión de Usuarios
        </a>
    </div>

    <form id="createUserForm" action="{{ route('storeUser') }}" method="post" class="form-container">
        @csrf
        @include('layouts.partials.messages')

        {{-- ── PASO 1: INFORMACIÓN PERSONAL ── --}}
        <div class="form-section">
            <div class="section-header">
                <h3 class="section-title">1. INFORMACIÓN PERSONAL</h3>
                <span class="section-badge">Datos Generales</span>
            </div>
            <div class="grid-row grid-cols-3">
                <div class="form-outline">
                    <label class="form-label" for="nombreInput">
                        Nombre (s) <span class="required-indicator">*</span>
                    </label>
                    <input type="text" id="nombreInput" class="form-control" name="nombre" value="{{ old('nombre') }}" placeholder="Nombre(s) del trabajador" required />
                </div>
                <div class="form-outline">
                    <label class="form-label" for="paternoInput">
                        Apellido Paterno <span class="required-indicator">*</span>
                    </label>
                    <input type="text" id="paternoInput" class="form-control" name="a_paterno" value="{{ old('a_paterno') }}" placeholder="Primer apellido" required />
                </div>
                <div class="form-outline">
                    <label class="form-label" for="maternoInput">
                        Apellido Materno <span class="required-indicator">*</span>
                    </label>
                    <input type="text" id="maternoInput" class="form-control" name="a_materno" value="{{ old('a_materno') }}" placeholder="Segundo apellido" required />
                </div>
            </div>
        </div>

        {{-- ── PASO 2: UBICACIÓN Y ASIGNACIÓN OPERATIVA (PLANTA → TIPO DE PERFIL → PUESTO) ── --}}
        <div class="form-section">
            <div class="section-header">
                <h3 class="section-title">2. UBICACIÓN Y ASIGNACIÓN OPERATIVA</h3>
                <span class="section-badge">Operación y Rol</span>
            </div>
            <div class="grid-row grid-cols-3">
                {{-- 1. Planta --}}
                <div class="form-outline">
                    <label class="form-label" for="selectPlanta">
                        Planta <span class="required-indicator">*</span>
                    </label>
                    <select class="form-select" id="selectPlanta" name="planta" required>
                        <option value="" disabled {{ old('planta') ? '' : '' }}>Seleccione planta</option>
                        <option value="TECAMAC" {{ old('planta', 'TECAMAC') === 'TECAMAC' || old('planta') === 'TECÁMAC' ? 'selected' : '' }}>TECÁMAC</option>
                        <option value="CDMX" {{ old('planta') === 'CDMX' ? 'selected' : '' }}>CDMX</option>
                    </select>
                </div>

                {{-- 2. Tipo de Perfil (Rol) --}}
                <div class="form-outline">
                    <label class="form-label" for="selectPerfil">
                        Tipo de Perfil (Rol) <span class="required-indicator">*</span>
                    </label>
                    <select class="form-select" id="selectPerfil" name="perfil" required>
                        <option value="" disabled {{ old('perfil', old('perfil_id')) ? '' : 'selected' }}>— Seleccionar Perfil —</option>
                        @php
                            $perfilesLista = (isset($perfiles) && count($perfiles) > 0) ? $perfiles : collect([
                                (object)['id' => 1, 'nombre' => 'Master', 'codigo_perfil' => '1'],
                                (object)['id' => 2, 'nombre' => 'Administrador', 'codigo_perfil' => '2'],
                                (object)['id' => 3, 'nombre' => 'Supervisor', 'codigo_perfil' => '3'],
                                (object)['id' => 4, 'nombre' => 'Ingeniería', 'codigo_perfil' => '4'],
                                (object)['id' => 5, 'nombre' => 'Diseño y programación', 'codigo_perfil' => '5'],
                                (object)['id' => 6, 'nombre' => 'Ventas', 'codigo_perfil' => '6'],
                                (object)['id' => 7, 'nombre' => 'Almacén', 'codigo_perfil' => '7'],
                                (object)['id' => 8, 'nombre' => 'Calidad', 'codigo_perfil' => '8'],
                                (object)['id' => 9, 'nombre' => 'Operador', 'codigo_perfil' => '9'],
                                (object)['id' => 10, 'nombre' => 'Sistemas', 'codigo_perfil' => '0'],
                            ]);
                        @endphp
                        @foreach($perfilesLista as $p)
                            <option value="{{ $p->id }}" data-codigo="{{ $p->codigo_perfil }}" {{ (string)old('perfil', old('perfil_id')) === (string)$p->id ? 'selected' : '' }}>
                                {{ $p->id }} - {{ $p->nombre }}
                            </option>
                        @endforeach
                    </select>
                    <input type="hidden" name="perfil_id" id="hiddenPerfilId" value="{{ old('perfil_id', old('perfil', '')) }}">
                </div>

                {{-- 3. Puesto - Se desbloquea al seleccionar Tipo de Perfil --}}
                <div class="form-outline">
                    <label class="form-label" for="selectPuesto">
                        Puesto <span class="required-indicator">*</span>
                    </label>
                    <select class="form-select" id="selectPuesto" name="puesto_id" required disabled>
                        <option value="" disabled selected>Seleccione perfil primero</option>
                    </select>
                    <input type="hidden" id="hiddenPuestoId" value="{{ old('puesto_id', '') }}">
                </div>

            </div>
        </div>

        {{-- ── PASO 3: CREDENCIALES Y PERFIL DE ACCESO ── --}}
        <div class="form-section">
            <div class="section-header">
                <h3 class="section-title">3. CREDENCIALES Y PERFIL DE ACCESO</h3>
                <span class="section-badge">Sistema</span>
            </div>
            <div class="grid-row grid-cols-3">
                {{-- Matrícula con Prefijo Dinámico --}}
                <div class="form-outline">
                    <label class="form-label" for="matriculaSufijo">
                        Matrícula <span class="required-indicator">*</span>
                    </label>
                    <div class="input-group-matricula">
                        <span class="matricula-prefix-badge" id="matriculaPrefixBadge">11-</span>
                        <input
                            type="text"
                            id="matriculaSufijo"
                            class="form-control matricula-input-suffix"
                            placeholder="Ej. 1273"
                            maxlength="6"
                            minlength="4"
                            autocomplete="off"
                            required
                        />
                    </div>
                    {{-- Matrícula completa que viaja en el POST --}}
                    <input type="hidden" name="matricula_prefijo" id="hiddenMatriculaPrefijo" value="11-">
                    <input type="hidden" name="matricula_sufijo" id="hiddenMatriculaSufijo" value="">
                    <input type="hidden" name="matricula" id="hiddenMatriculaCompleta" value="{{ old('matricula', '') }}">

                    <small class="input-helper-text" id="matriculaHelper">
                        7 a 9 caracteres totales (Prefijo de 2 dígitos + 4 a 6 dígitos del checador).
                    </small>
                </div>

                {{-- Contraseña --}}
                <div class="form-outline">
                    <label class="form-label" for="contrasenaInput">
                        Contraseña <span class="required-indicator">*</span>
                    </label>
                    <input type="password" id="contrasenaInput" class="form-control" maxlength="12" minlength="8" name="contrasena" placeholder="8 a 12 caracteres" required />
                    <small class="input-helper-text">De 8 a 12 caracteres alfanuméricos.</small>
                </div>

                {{-- Turno --}}
                <div class="form-outline">
                    <label class="form-label" for="selectTurno">
                        Turno <span class="required-indicator">*</span>
                    </label>
                    <select class="form-select" id="selectTurno" name="turno" required>
                        <option value="" disabled {{ old('turno') ? '' : 'selected' }}>Seleccione turno</option>
                        <option value="MIXTO" {{ old('turno') == 'MIXTO' ? 'selected' : '' }}>MIXTO</option>
                        <option value="MATUTINO" {{ old('turno', 'MATUTINO') == 'MATUTINO' ? 'selected' : '' }}>MATUTINO</option>
                        <option value="VESPERTINO" {{ old('turno') == 'VESPERTINO' ? 'selected' : '' }}>VESPERTINO</option>
                        <option value="NOCTURNO" {{ old('turno') == 'NOCTURNO' ? 'selected' : '' }}>NOCTURNO</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- ── BARRA DE ACCIONES ── --}}
        <div class="form-actions-bar">
            <span class="actions-note">* Todos los campos con asterisco son obligatorios para el registro</span>
            <div class="actions-buttons">
                <a href="{{ route('users') }}" class="btn-cancelar">
                    Cancelar
                </a>
                <button type="submit" class="btn-registrar">
                    <img src="{{ asset('images/Color_Icons/Generales/anadir.png') }}" alt="Guardar" class="btn-reg-icon">
                    <span>Guardar Usuario</span>
                </button>
            </div>
        </div>
    </form>
</div>

{{-- ── LOGICA DINAMICA DE ASIGNACION Y MATRICULA (CASCADA: PLANTA → ÁREA → PUESTO → PERFIL) ── --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Catálogos cargados desde la base de datos con respaldo estático oficial
        const perfilesDb = @json($perfiles); // [{id, nombre, codigo_perfil}, ...]
        const puestosDb = @json($puestos);   // [{id, area_id, nombre, codigo_nivel}, ...]

        const defaultPerfiles = [
            { id: 1, nombre: 'Master', codigo_perfil: '1' },
            { id: 2, nombre: 'Administrador', codigo_perfil: '2' },
            { id: 3, nombre: 'Supervisor', codigo_perfil: '3' },
            { id: 4, nombre: 'Ingeniería', codigo_perfil: '4' },
            { id: 5, nombre: 'Diseño y programación', codigo_perfil: '5' },
            { id: 6, nombre: 'Ventas', codigo_perfil: '6' },
            { id: 7, nombre: 'Almacén', codigo_perfil: '7' },
            { id: 8, nombre: 'Calidad', codigo_perfil: '8' },
            { id: 9, nombre: 'Operador', codigo_perfil: '9' },
            { id: 10, nombre: 'Sistemas', codigo_perfil: '0' }
        ];

        const defaultPuestos = [
            { id: 1, area_id: 1, nombre: 'Director / Master', codigo_nivel: '1' },
            { id: 2, area_id: 2, nombre: 'Jefe de Departamento', codigo_nivel: '1' },
            { id: 3, area_id: 2, nombre: 'Auxiliar', codigo_nivel: '2' },
            { id: 4, area_id: 2, nombre: 'Becario', codigo_nivel: '3' },
            { id: 5, area_id: 3, nombre: 'Jefe de Departamento', codigo_nivel: '1' },
            { id: 6, area_id: 3, nombre: 'Auxiliar', codigo_nivel: '2' },
            { id: 7, area_id: 3, nombre: 'Becario', codigo_nivel: '3' },
            { id: 8, area_id: 4, nombre: 'Jefe de Departamento', codigo_nivel: '1' },
            { id: 9, area_id: 4, nombre: 'Auxiliar', codigo_nivel: '2' },
            { id: 10, area_id: 4, nombre: 'Becario', codigo_nivel: '3' },
            { id: 11, area_id: 5, nombre: 'Jefe de Departamento', codigo_nivel: '1' },
            { id: 12, area_id: 5, nombre: 'Auxiliar', codigo_nivel: '2' },
            { id: 13, area_id: 5, nombre: 'Becario', codigo_nivel: '3' },
            { id: 14, area_id: 6, nombre: 'Jefe de Taller', codigo_nivel: '1' },
            { id: 15, area_id: 6, nombre: 'Auxiliar de Supervisión', codigo_nivel: '2' },
            { id: 16, area_id: 6, nombre: 'Becario', codigo_nivel: '3' },
            { id: 17, area_id: 6, nombre: 'Operador Torno CNC', codigo_nivel: '4' },
            { id: 18, area_id: 6, nombre: 'Operador Centro de Maquinados', codigo_nivel: '5' },
            { id: 19, area_id: 6, nombre: 'Ayudante General', codigo_nivel: '6' },
            { id: 20, area_id: 7, nombre: 'Jefe de Acabados', codigo_nivel: '1' },
            { id: 21, area_id: 7, nombre: 'Auxiliar de Supervisión', codigo_nivel: '2' },
            { id: 22, area_id: 7, nombre: 'Becario', codigo_nivel: '3' },
            { id: 23, area_id: 7, nombre: 'Operador de Acabados', codigo_nivel: '4' },
            { id: 24, area_id: 7, nombre: 'Ayudante General', codigo_nivel: '5' },
            { id: 25, area_id: 8, nombre: 'Jefe de Departamento', codigo_nivel: '1' },
            { id: 26, area_id: 8, nombre: 'Auxiliar', codigo_nivel: '2' },
            { id: 27, area_id: 8, nombre: 'Becario', codigo_nivel: '3' },
            { id: 28, area_id: 9, nombre: 'Jefe de Turno', codigo_nivel: '1' },
            { id: 29, area_id: 9, nombre: 'Auxiliar de Supervisión', codigo_nivel: '2' },
            { id: 30, area_id: 9, nombre: 'Becario', codigo_nivel: '3' },
            { id: 31, area_id: 9, nombre: 'Operador Torno CNC', codigo_nivel: '4' },
            { id: 32, area_id: 9, nombre: 'Soldador PTA', codigo_nivel: '5' },
            { id: 33, area_id: 9, nombre: 'Operador Centro de Maquinados', codigo_nivel: '6' },
            { id: 34, area_id: 9, nombre: 'Ayudante General', codigo_nivel: '7' },
            { id: 35, area_id: 10, nombre: 'Jefe de Departamento', codigo_nivel: '1' },
            { id: 36, area_id: 10, nombre: 'Auxiliar', codigo_nivel: '2' },
            { id: 37, area_id: 10, nombre: 'Becario', codigo_nivel: '3' },
            { id: 38, area_id: 11, nombre: 'Jefe de Sistemas', codigo_nivel: '1' },
            { id: 39, area_id: 11, nombre: 'Auxiliar de Sistemas', codigo_nivel: '2' },
            { id: 40, area_id: 11, nombre: 'Becario', codigo_nivel: '3' }
        ];

        const perfiles = (Array.isArray(perfilesDb) && perfilesDb.length > 0) ? perfilesDb : defaultPerfiles;
        const puestos = (Array.isArray(puestosDb) && puestosDb.length > 0) ? puestosDb : defaultPuestos;

        // Elementos DOM
        const form = document.getElementById('createUserForm');
        const selectPlanta = document.getElementById('selectPlanta');
        const selectPerfil = document.getElementById('selectPerfil');
        const selectPuesto = document.getElementById('selectPuesto');

        const hiddenPerfilId = document.getElementById('hiddenPerfilId');
        const hiddenAreaId = document.getElementById('hiddenAreaId') || { value: '' };
        const hiddenPuestoId = document.getElementById('hiddenPuestoId');

        const matriculaPrefixBadge = document.getElementById('matriculaPrefixBadge');
        const matriculaSufijo = document.getElementById('matriculaSufijo');
        const hiddenMatriculaPrefijo = document.getElementById('hiddenMatriculaPrefijo');
        const hiddenMatriculaSufijo = document.getElementById('hiddenMatriculaSufijo');
        const hiddenMatriculaCompleta = document.getElementById('hiddenMatriculaCompleta');
        const matriculaHelper = document.getElementById('matriculaHelper');

        // Valores old() de Laravel para preservar estado tras error de validación
        const oldData = {
            planta: @json(old('planta', '')),
            perfil: @json(old('perfil', '')),
            puesto_id: @json(old('puesto_id', '')),
            area_id: @json(old('area_id', '')),
            matricula: @json(old('matricula', ''))
        };

        if (oldData.matricula && oldData.matricula.includes('-')) {
            const parts = oldData.matricula.split('-');
            matriculaSufijo.value = parts.slice(1).join('-');
        }

        /**
         * Retorna los puestos disponibles según el perfil y la planta seleccionada.
         */
        function getPuestosPorPerfil(perfilId, planta) {
            perfilId = parseInt(perfilId, 10);
            const rawPlanta = (planta || selectPlanta.value || '').toUpperCase();
            const currentPlanta = rawPlanta.startsWith('TEC') ? 'TECAMAC' : (rawPlanta === 'CDMX' ? 'CDMX' : 'TECAMAC');

            let filtered = [];
            switch (perfilId) {
                case 1: // Master -> Dirección General (area 1)
                    filtered = puestos.filter(p => parseInt(p.area_id, 10) === 1);
                    break;
                case 2: // Administrador -> Administración (area 10)
                    filtered = puestos.filter(p => parseInt(p.area_id, 10) === 10);
                    break;
                case 3: // Supervisor -> Producción (9) en TECAMAC; Taller (6) o Acabados (7) en CDMX con nivel <= 2
                    if (currentPlanta === 'CDMX') {
                        filtered = puestos.filter(p => [6, 7].includes(parseInt(p.area_id, 10)) && parseInt(p.codigo_nivel, 10) <= 2);
                    } else {
                        filtered = puestos.filter(p => parseInt(p.area_id, 10) === 9 && parseInt(p.codigo_nivel, 10) <= 2);
                    }
                    break;
                case 4: // Ingeniería -> Ingeniería (area 4)
                    filtered = puestos.filter(p => parseInt(p.area_id, 10) === 4);
                    break;
                case 5: // Diseño y programación -> Diseño y Programación (area 3)
                    filtered = puestos.filter(p => parseInt(p.area_id, 10) === 3);
                    break;
                case 6: // Ventas -> Ventas (area 2)
                    filtered = puestos.filter(p => parseInt(p.area_id, 10) === 2);
                    break;
                case 7: // Almacén -> Almacén (area 8)
                    filtered = puestos.filter(p => parseInt(p.area_id, 10) === 8);
                    break;
                case 8: // Calidad -> Calidad (area 5)
                    filtered = puestos.filter(p => parseInt(p.area_id, 10) === 5);
                    break;
                case 9: // Operador -> Producción (9) en TECAMAC; Taller (6) o Acabados (7) en CDMX con nivel >= 3
                    if (currentPlanta === 'CDMX') {
                        filtered = puestos.filter(p => [6, 7].includes(parseInt(p.area_id, 10)) && parseInt(p.codigo_nivel, 10) >= 3);
                    } else {
                        filtered = puestos.filter(p => parseInt(p.area_id, 10) === 9 && parseInt(p.codigo_nivel, 10) >= 3);
                    }
                    break;
                case 10: // Sistemas -> Sistemas / TI (area 11)
                    filtered = puestos.filter(p => parseInt(p.area_id, 10) === 11);
                    break;
                default:
                    filtered = [];
                    break;
            }

            if (filtered.length === 0 && puestos.length > 0) {
                filtered = puestos.slice(0, 3);
            }

            return filtered;
        }

        /**
         * 1. Despliega los perfiles disponibles para la planta seleccionada.
         */
        function popularPerfiles(planta, preservePerfilId = null) {
            const currentSelected = preservePerfilId || selectPerfil.value || oldData.perfil;
            selectPerfil.innerHTML = '';

            const placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.disabled = true;
            placeholder.textContent = '— Seleccionar Perfil —';
            selectPerfil.appendChild(placeholder);

            perfiles.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.id;
                opt.dataset.codigo = p.codigo_perfil || String(p.id);
                opt.textContent = `${p.id} - ${p.nombre}`;
                if (currentSelected && parseInt(currentSelected, 10) === parseInt(p.id, 10)) {
                    opt.selected = true;
                }
                selectPerfil.appendChild(opt);
            });

            if (!selectPerfil.value) {
                placeholder.selected = true;
            }

            hiddenPerfilId.value = selectPerfil.value || '';
        }

        /**
         * 2. Despliega los puestos según el perfil seleccionado.
         */
        function actualizarPuestos(preservePuestoId = null) {
            selectPuesto.innerHTML = '';
            const perfilId = parseInt(selectPerfil.value, 10);

            if (!perfilId) {
                selectPuesto.disabled = true;
                const opt = document.createElement('option');
                opt.value = '';
                opt.disabled = true;
                opt.selected = true;
                opt.textContent = 'Seleccione perfil primero';
                selectPuesto.appendChild(opt);
                hiddenPuestoId.value = '';
                hiddenAreaId.value = '';
                recalcularMatricula();
                return;
            }

            const puestosDisponibles = getPuestosPorPerfil(perfilId, selectPlanta.value);

            if (puestosDisponibles.length === 0) {
                selectPuesto.disabled = true;
                const opt = document.createElement('option');
                opt.value = '';
                opt.disabled = true;
                opt.selected = true;
                opt.textContent = 'No hay puestos disponibles para este perfil';
                selectPuesto.appendChild(opt);
                hiddenPuestoId.value = '';
                hiddenAreaId.value = '';
                recalcularMatricula();
                return;
            }

            selectPuesto.disabled = false;

            let matched = false;
            puestosDisponibles.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.id;
                opt.dataset.nivel = p.codigo_nivel;
                opt.dataset.areaId = p.area_id;
                opt.textContent = p.nombre;
                if (preservePuestoId && parseInt(preservePuestoId, 10) === parseInt(p.id, 10)) {
                    opt.selected = true;
                    matched = true;
                }
                selectPuesto.appendChild(opt);
            });

            if (!matched && puestosDisponibles.length > 0) {
                selectPuesto.selectedIndex = 0;
            }

            const selectedOpt = selectPuesto.selectedOptions[0];
            if (selectedOpt && selectedOpt.dataset.areaId) {
                hiddenAreaId.value = selectedOpt.dataset.areaId;
                hiddenPuestoId.value = selectedOpt.value;
            }

            recalcularMatricula();
        }

        /**
         * 3. Recalcula la matrícula dinámicamente según Perfil y Puesto (Prefijo de 2 dígitos + guion).
         */
        function recalcularMatricula() {
            const perfilId = parseInt(selectPerfil.value, 10);
            const puestoId = parseInt(selectPuesto.value, 10);
            const puestoObj = puestos.find(p => p.id === puestoId);
            const areaId = puestoObj ? parseInt(puestoObj.area_id, 10) : parseInt(hiddenAreaId.value, 10);

            hiddenPerfilId.value = perfilId || '';
            hiddenAreaId.value = areaId || '';
            hiddenPuestoId.value = puestoId || '';

            if (!perfilId || !puestoId) {
                matriculaPrefixBadge.textContent = '--';
                hiddenMatriculaPrefijo.value = '';
                actualizarMatriculaCompleta();
                return;
            }

            const perfObj = perfiles.find(p => p.id === perfilId);
            const cPerfil = perfObj ? (perfObj.codigo_perfil ?? String(perfObj.id)) : '9';
            const cPuesto = puestoObj ? (puestoObj.codigo_nivel ?? '1') : '1';

            // Fórmula: Perfil (1 dígito) + Nivel de Puesto (1 dígito) + '-'
            const prefix = `${cPerfil}${cPuesto}-`;

            if (perfilId === 1) {
                matriculaSufijo.maxLength = 4;
                matriculaSufijo.minLength = 4;
                matriculaSufijo.placeholder = '4 dígitos (ej. 1234)';
                matriculaHelper.innerHTML = `<strong style="color:var(--gis-blue);">Exactamente 7 caracteres totales</strong>: 3 asignados automáticamente (<code>${prefix}</code>) + 4 del checador.`;
            } else {
                matriculaSufijo.maxLength = 6;
                matriculaSufijo.minLength = 4;
                matriculaSufijo.placeholder = '4 a 6 caracteres (ej. 1273)';
                matriculaHelper.innerHTML = `<strong>7 a 9 caracteres totales</strong> con prefijo <code>${prefix}</code> (4 a 6 dígitos complementarios).`;
            }

            matriculaPrefixBadge.textContent = prefix;
            hiddenMatriculaPrefijo.value = prefix;
            actualizarMatriculaCompleta();
        }

        function actualizarMatriculaCompleta() {
            let sufijo = matriculaSufijo.value.trim().toUpperCase().replace(/\s+/g, '');
            matriculaSufijo.value = sufijo;
            hiddenMatriculaSufijo.value = sufijo;

            const prefijo = hiddenMatriculaPrefijo.value || '994-';
            hiddenMatriculaCompleta.value = sufijo ? (prefijo + sufijo) : '';
        }

        // ── Eventos de Interacción ──
        selectPlanta.addEventListener('change', function () {
            if (!this.value) {
                selectPerfil.disabled = true;
                selectPerfil.innerHTML = '<option value="" disabled selected>Seleccione planta primero</option>';
                selectPuesto.disabled = true;
                selectPuesto.innerHTML = '<option value="" disabled selected>Seleccione perfil primero</option>';
                hiddenAreaId.value = '';
                hiddenPerfilId.value = '';
                hiddenPuestoId.value = '';
                recalcularMatricula();
                return;
            }

            selectPerfil.disabled = false;
            popularPerfiles(this.value, selectPerfil.value || oldData.perfil);
            actualizarPuestos(selectPuesto.value || oldData.puesto_id);
        });

        selectPerfil.addEventListener('change', function () {
            hiddenPerfilId.value = this.value;
            actualizarPuestos();
        });

        selectPuesto.addEventListener('change', function () {
            const selectedOpt = this.selectedOptions[0];
            if (selectedOpt && selectedOpt.dataset.areaId) {
                hiddenAreaId.value = selectedOpt.dataset.areaId;
            }
            hiddenPuestoId.value = this.value;
            recalcularMatricula();
        });

        matriculaSufijo.addEventListener('input', function () {
            actualizarMatriculaCompleta();
        });

        // Al enviar el formulario, desbloquear selects para que viajen en POST
        form.addEventListener('submit', function () {
            actualizarMatriculaCompleta();
            selectPlanta.disabled = false;
            selectPerfil.disabled = false;
            selectPuesto.disabled = false;
        });

        // ── Inicialización ──
        const initialPlanta = (selectPlanta.value || oldData.planta || '').toUpperCase();
        if (initialPlanta) {
            selectPlanta.value = initialPlanta.startsWith('TEC') ? 'TECAMAC' : (initialPlanta === 'CDMX' ? 'CDMX' : 'TECAMAC');
            selectPerfil.disabled = false;
            popularPerfiles(selectPlanta.value, oldData.perfil);
            if (selectPerfil.value) {
                selectPuesto.disabled = false;
                actualizarPuestos(oldData.puesto_id);
            }
        }
    });
</script>
@endsection
