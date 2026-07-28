@extends('layouts.appMenu')

@section('head')
    @vite(['resources/css/reports_views/pta_shipment.css'])
    <title>Envío de Reportes PTA</title>
    <link rel="icon" href="{{ url('images/lg_saavedra.png') }}?v=1">
@endsection

@section('background-body', 'background-image:url("' . asset('images/fondoLogin.jpg') . '")')

@section('content')
<div class="envio-pta-container">

    {{-- ── Header ──────────────────────────────────────────────── --}}
    <div class="envio-pta-header">
        <h1>Envío de Reportes PTA</h1>
        <p>Selecciona la OT y clase con registros de Soldadura PTA, y envía el reporte PDF por correo.</p>
    </div>

    {{-- ── Alertas de sesión ───────────────────────────────────── --}}
    @if(session('success'))
        <div class="envio-pta-alert envio-pta-alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="envio-pta-alert envio-pta-alert-error">{{ session('error') }}</div>
    @endif

    {{-- ── Formulario principal ────────────────────────────────── --}}
    <div class="envio-pta-card">
        <h2>Parámetros del Reporte</h2>

        <form action="{{ route('reportes.pta.enviar') }}" method="POST" id="form-envio-pta">
            @csrf

            <div class="envio-pta-row">
                {{-- Selector de OT --}}
                <div class="envio-pta-field">
                    <label for="ot_id">Orden de Trabajo</label>
                    <select name="ot_id" id="ot_id" class="envio-pta-select" required>
                        <option value="">— Selecciona una OT —</option>
                        @foreach($otsConPTA as $ot)
                            <option value="{{ $ot->id }}"
                                data-moldura="{{ $ot->moldura ? $ot->moldura->nombre : 'Sin moldura' }}"
                                {{ old('ot_id') == $ot->id ? 'selected' : '' }}>
                                OT #{{ $ot->id }} — {{ $ot->moldura ? $ot->moldura->nombre : 'Sin moldura' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Selector de Clase --}}
                <div class="envio-pta-field">
                    <label for="clase_id">Clase (con Soldadura PTA)</label>
                    <select name="clase_id" id="clase_id" class="envio-pta-select" required>
                        <option value="">— Primero selecciona una OT —</option>
                        @foreach($otsConPTA as $ot)
                            @foreach($ot->clases as $clase)
                                <option value="{{ $clase->id }}"
                                    data-ot="{{ $ot->id }}"
                                    data-nombre="{{ $clase->nombre }}"
                                    {{ (old('ot_id') == $ot->id && old('clase_id') == $clase->id) ? 'selected' : '' }}
                                    class="env-display-none">
                                    {{ $clase->nombre }}{{ $clase->tamanio ? ' (' . $clase->tamanio . ')' : '' }}
                                </option>
                            @endforeach
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Info dinámica OT/Clase seleccionada --}}
            <div class="envio-pta-ot-info" id="ot-info-box">
                <span id="ot-info-text"></span>
            </div>

            {{-- Destinatarios --}}
            <div class="envio-pta-field env-flex-100pct">
                <label>Destinatario fijo</label>

                {{-- Correo fijo/obligatorio --}}
                <div class="dest-fixed-row">
                    <span class="dest-fixed-badge">
                        {{ implode(', ', (array) \App\Http\Controllers\EnvioPtaController::DESTINATARIO) }}
                    </span>
                    <span class="dest-fixed-label">— siempre incluido</span>
                </div>

                {{-- Correos adicionales --}}
                <input type="text"
                    name="destinatarios_extra"
                    id="destinatarios_extra" class="envio-pta-input env-margin-top-8px"
                    placeholder="Correos adicionales separados por coma: otro@correo.com, otro2@correo.com"
                    value="{{ old('destinatarios_extra', '') }}">
                <p class="envio-pta-hint">
                    El reporte siempre se enviará al correo fijo. Puedes agregar más destinatarios separándolos por coma.
                </p>
            </div>

            <div class="envio-pta-actions">
                <button type="submit" class="envio-pta-btn envio-pta-btn-primary" id="btn-enviar">
                     Enviar Reporte PTA
                </button>
                <a href="{{ route('home') }}" class="envio-pta-btn envio-pta-btn-secondary">
                    Cancelar
                </a>
            </div>
        </form>
    </div>

    {{-- ── Historial de envíos ──────────────────────────────────── --}}
    <div class="envio-pta-logs-card">
        <h2> Historial de Envíos PTA</h2>

        @if($logs->isEmpty())
            <p class="envio-pta-no-logs">Sin registros de envíos de reportes PTA todavía.</p>
        @else
            {{-- ── Barra de filtros ─────────────────────────────── --}}
            <div class="logs-filtros">
                <div class="logs-filtro-item">
                    <label for="filtro-fecha-desde">Desde</label>
                    <input type="date" id="filtro-fecha-desde" class="envio-pta-input logs-filtro-input">
                </div>
                <div class="logs-filtro-item">
                    <label for="filtro-fecha-hasta">Hasta</label>
                    <input type="date" id="filtro-fecha-hasta" class="envio-pta-input logs-filtro-input">
                </div>
                <div class="logs-filtro-item logs-filtro-ot">
                    <label for="filtro-ot">Orden de Trabajo</label>
                    <select id="filtro-ot" class="envio-pta-select logs-filtro-input">
                        <option value="">— Todas las OTs —</option>
                        @foreach($logs->sortBy('ot_id')->unique('ot_id') as $log)
                            <option value="{{ $log->ot_id }}">
                                {{ $log->ot_nombre ?? 'OT #' . $log->ot_id }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="logs-filtro-item">
                    <label for="filtro-estado">Estado</label>
                    <select id="filtro-estado" class="envio-pta-select logs-filtro-input">
                        <option value="">— Todos —</option>
                        <option value="enviado">✓ Enviado</option>
                        <option value="error">✗ Error</option>
                    </select>
                </div>
                <div class="logs-filtro-acciones">
                    <button type="button" id="btn-limpiar-filtros" class="logs-btn-limpiar">
                        ✕ Limpiar filtros
                    </button>
                    <span class="logs-contador" id="logs-contador-texto">
                        {{ $logs->count() }} registro(s)
                    </span>
                </div>
            </div>

            {{-- ── Tabla con scroll ──────────────────────────────── --}}
            <div class="envio-pta-table-wrap">
                <table class="envio-pta-table" id="logs-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>OT</th>
                            <th>Clase</th>
                            <th>Destinatario</th>
                            <th>Estado</th>
                            <th>Matrícula</th>
                            <th>Enviado por</th>
                        </tr>
                    </thead>
                    <tbody id="logs-tbody">
                        @foreach($logs as $log)
                            <tr
                                data-fecha="{{ \Carbon\Carbon::parse($log->created_at)->format('Y-m-d') }}"
                                data-ot="{{ $log->ot_id }}"
                                data-estado="{{ $log->estado }}">
                                <td>{{ $log->id }}</td>
                                <td class="env-white-space-nowrap">
                                    {{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y') }}
                                </td>
                                <td class="env-white-space-nowrap env-font-size-12px env-color-64748b">
                                    {{ \Carbon\Carbon::parse($log->created_at)->format('H:i:s') }}
                                </td>
                                <td>{{ $log->ot_nombre ?? 'OT #' . $log->ot_id }}</td>
                                <td>{{ $log->clase_nombre ?? $log->clase_id }}</td>
                                <td>{{ $log->destinatario }}</td>
                                <td>
                                    @if($log->estado === 'enviado')
                                        <span class="badge-enviado">✓ Enviado</span>
                                    @else
                                        <span class="badge-error"
                                            title="{{ $log->mensaje_error ?? 'Error desconocido' }}">
                                            ✗ Error
                                        </span>
                                    @endif
                                </td>
                                <td class="td-matricula">
                                    {{ $log->enviado_por ?? '—' }}
                                </td>
                                <td>
                                    @if($log->usuario)
                                        {{ trim($log->usuario->nombre . ' ' . $log->usuario->a_paterno . ' ' . $log->usuario->a_materno) }}
                                    @else
                                        <span class="env-color-94a3b8 env-font-style-italic">Sin datos</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <p class="envio-pta-no-logs env-display-none">
                    Sin resultados para los filtros aplicados.
                </p>
            </div>
        @endif
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Auto-fade de alertas ─────────────────────────────────────
    document.querySelectorAll('.envio-pta-alert').forEach(function (el) {
        setTimeout(function () {
            el.classList.add('fade-out');
            setTimeout(function () { el.remove(); }, 1000);
        }, 5000);
    });

    // ── Selector OT → Clase en cascada ──────────────────────────
    const selOt    = document.getElementById('ot_id');
    const selClase = document.getElementById('clase_id');
    const infoBox  = document.getElementById('ot-info-box');
    const infoText = document.getElementById('ot-info-text');

    function filtrarClases(otId) {
        const opts = selClase.querySelectorAll('option[data-ot]');
        let first = null;
        opts.forEach(function (o) {
            if (o.dataset.ot === otId) {
                o.style.display = '';
                if (!first) first = o;
            } else {
                o.style.display = 'none';
                o.selected = false;
            }
        });
        selClase.querySelector('option[value=""]').textContent =
            otId ? '— Selecciona una clase —' : '— Primero selecciona una OT —';
        if (first) { first.selected = true; }
        else        { selClase.value = ''; }
        actualizarInfo();
    }

    function actualizarInfo() {
        const otId  = selOt.value;
        const otOpt = selOt.selectedOptions[0];
        const clOpt = selClase.selectedOptions[0];
        if (otId && clOpt && clOpt.value) {
            const moldura  = otOpt.dataset.moldura || '';
            const claseNom = clOpt.dataset.nombre  || clOpt.textContent.trim();
            infoText.textContent = `✅  OT #${otId} — ${moldura}   |   Clase: ${claseNom}`;
            infoBox.classList.add('visible');
        } else {
            infoBox.classList.remove('visible');
        }
    }

    if (selOt) {
        selOt.addEventListener('change', function () { filtrarClases(this.value); });
        selClase.addEventListener('change', actualizarInfo);
        if (selOt.value) { filtrarClases(selOt.value); }
    }

    // ── Prevenir doble envío ─────────────────────────────────────
    const form   = document.getElementById('form-envio-pta');
    const btnEnv = document.getElementById('btn-enviar');
    if (form) {
        form.addEventListener('submit', function () {
            btnEnv.disabled    = true;
            btnEnv.textContent = '⏳ Enviando...';
        });
    }

    // ════════════════════════════════════════════════════════════
    //  FILTROS DE LOGS
    // ════════════════════════════════════════════════════════════
    const fDesde   = document.getElementById('filtro-fecha-desde');
    const fHasta   = document.getElementById('filtro-fecha-hasta');
    const fOt      = document.getElementById('filtro-ot');
    const fEstado  = document.getElementById('filtro-estado');
    const btnLimp  = document.getElementById('btn-limpiar-filtros');
    const contador = document.getElementById('logs-contador-texto');
    const sinRes   = document.getElementById('logs-sin-resultados');
    const tbody    = document.getElementById('logs-tbody');

    if (!tbody) return; // No hay logs

    function aplicarFiltros() {
        const desde  = fDesde  ? fDesde.value  : '';
        const hasta  = fHasta  ? fHasta.value  : '';
        const otId   = fOt     ? fOt.value     : '';
        const estado = fEstado ? fEstado.value : '';

        const filas = tbody.querySelectorAll('tr');
        let visibles = 0;

        filas.forEach(function (tr) {
            const fecha  = tr.dataset.fecha  || '';
            const trOt   = tr.dataset.ot     || '';
            const trEst  = tr.dataset.estado || '';

            let visible = true;

            if (desde && fecha < desde)  visible = false;
            if (hasta && fecha > hasta)  visible = false;
            if (otId  && trOt !== otId)  visible = false;
            if (estado && trEst !== estado) visible = false;

            tr.style.display = visible ? '' : 'none';
            if (visible) visibles++;
        });

        // Contador
        if (contador) {
            contador.textContent = visibles + ' registro(s)';
        }

        // Mensaje sin resultados
        if (sinRes) {
            sinRes.style.display = visibles === 0 ? 'block' : 'none';
        }

        // Ocultar thead si no hay nada visible
        const table = document.getElementById('logs-table');
        if (table) {
            table.style.display = visibles === 0 ? 'none' : '';
        }
    }

    if (fDesde)  fDesde.addEventListener('input', aplicarFiltros);
    if (fHasta)  fHasta.addEventListener('input', aplicarFiltros);
    if (fOt)     fOt.addEventListener('change', aplicarFiltros);
    if (fEstado) fEstado.addEventListener('change', aplicarFiltros);

    if (btnLimp) {
        btnLimp.addEventListener('click', function () {
            if (fDesde)  fDesde.value  = '';
            if (fHasta)  fHasta.value  = '';
            if (fOt)     fOt.value     = '';
            if (fEstado) fEstado.value = '';
            aplicarFiltros();
        });
    }

    // Aplicar al cargar (por si hay old values)
    aplicarFiltros();
});
</script>
@endsection
