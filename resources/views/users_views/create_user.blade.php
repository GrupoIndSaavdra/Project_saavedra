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
                        <option value="" disabled {{ old('planta') ? '' : 'selected' }}>Seleccione planta</option>
                        <option value="TECAMAC" {{ in_array(old('planta', 'TECAMAC'), ['TECAMAC', 'TECÁMAC'], true) ? 'selected' : '' }}>TECÁMAC</option>
                        <option value="CDMX" {{ old('planta') == 'CDMX' ? 'selected' : '' }}>CDMX</option>
                    </select>
                </div>

                {{-- 2. Tipo de Perfil (Rol) - Se desbloquea al seleccionar Planta --}}
                <div class="form-outline">
                    <label class="form-label" for="selectPerfil">
                        Tipo de Perfil (Rol) <span class="required-indicator">*</span>
                    </label>
                    <select class="form-select" id="selectPerfil" name="perfil" required disabled>
                        <option value="" disabled selected>Seleccione planta primero</option>
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
        // Catálogos cargados desde la base de datos
        const perfiles = @json($perfiles); // [{id, nombre, codigo_perfil}, ...]
        const puestos = @json($puestos);   // [{id, area_id, nombre, codigo_nivel}, ...]

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

            switch (perfilId) {
                case 1: // Master -> Dirección General (area 1)
                    return puestos.filter(p => parseInt(p.area_id, 10) === 1);

                case 2: // Administrador -> Administración (area 10)
                    return puestos.filter(p => parseInt(p.area_id, 10) === 10);

                case 3: // Supervisor -> Producción (9) en TECAMAC; Taller (6) o Acabados (7) en CDMX con nivel <= 2
                    if (currentPlanta === 'CDMX') {
                        return puestos.filter(p => [6, 7].includes(parseInt(p.area_id, 10)) && parseInt(p.codigo_nivel, 10) <= 2);
                    } else {
                        return puestos.filter(p => parseInt(p.area_id, 10) === 9 && parseInt(p.codigo_nivel, 10) <= 2);
                    }

                case 4: // Ingeniería -> Ingeniería (area 4)
                    return puestos.filter(p => parseInt(p.area_id, 10) === 4);

                case 5: // Diseño y programación -> Diseño y Programación (area 3)
                    return puestos.filter(p => parseInt(p.area_id, 10) === 3);

                case 6: // Ventas -> Ventas (area 2)
                    return puestos.filter(p => parseInt(p.area_id, 10) === 2);

                case 7: // Almacén -> Almacén (area 8)
                    return puestos.filter(p => parseInt(p.area_id, 10) === 8);

                case 8: // Calidad -> Calidad (area 5)
                    return puestos.filter(p => parseInt(p.area_id, 10) === 5);

                case 9: // Operador -> Producción (9) en TECAMAC; Taller (6) o Acabados (7) en CDMX con nivel >= 3
                    if (currentPlanta === 'CDMX') {
                        return puestos.filter(p => [6, 7].includes(parseInt(p.area_id, 10)) && parseInt(p.codigo_nivel, 10) >= 3);
                    } else {
                        return puestos.filter(p => parseInt(p.area_id, 10) === 9 && parseInt(p.codigo_nivel, 10) >= 3);
                    }

                case 10: // Sistemas -> Sistemas / TI (area 11)
                    return puestos.filter(p => parseInt(p.area_id, 10) === 11);

                default:
                    return [];
            }
        }

        /**
         * 1. Despliega los perfiles disponibles para la planta seleccionada.
         */
        function popularPerfiles(planta, preservePerfilId = null) {
            selectPerfil.innerHTML = '';

            const placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.disabled = true;
            placeholder.textContent = '— Seleccionar Perfil —';
            selectPerfil.appendChild(placeholder);

            perfiles.forEach(p => {
                const puestosDisponibles = getPuestosPorPerfil(p.id, planta);
                if (puestosDisponibles.length > 0) {
                    const opt = document.createElement('option');
                    opt.value = p.id;
                    opt.dataset.codigo = p.codigo_perfil;
                    opt.textContent = `${p.id} - ${p.nombre}`;
                    if (preservePerfilId && parseInt(preservePerfilId, 10) === parseInt(p.id, 10)) {
                        opt.selected = true;
                    }
                    selectPerfil.appendChild(opt);
                }
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
