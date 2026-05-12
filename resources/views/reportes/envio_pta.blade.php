@extends('layouts.appMenu')

@section('head')
    @vite(['resources/css/reportes/envio_pta.css'])
    <title>Envío de Reportes PTA</title>
    <link rel="icon" href="{{ url('images/lg_saavedra.png') }}?v=1">
@endsection

@section('background-body', 'background-image:url("' . asset('images/fondoLogin.jpg') . '")')

@section('content')
<div class="envio-pta-container">

    {{-- ── Header ──────────────────────────────────────────────── --}}
    <div class="envio-pta-header">
        <h1>📧 Envío de Reportes PTA</h1>
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
                                    style="display:none">
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

            {{-- Destinatario (informativo, fijo en código) --}}
            <div class="envio-pta-field">
                <label for="destinatario_display">Destinatario</label>
                <input type="text" id="destinatario_display" class="envio-pta-input"
                    value="{{ config('mail.pta_recipient', 'alemanpereznatali@gmail.com') }}"
                    disabled>
                <p class="envio-pta-hint">
                    El destinatario está configurado en el sistema. Contacta al administrador para modificarlo.
                </p>
            </div>

            <div class="envio-pta-actions">
                <button type="submit" class="envio-pta-btn envio-pta-btn-primary" id="btn-enviar">
                    📤 Enviar Reporte PTA
                </button>
                <a href="{{ route('home') }}" class="envio-pta-btn envio-pta-btn-secondary">
                    Cancelar
                </a>
            </div>
        </form>
    </div>

    {{-- ── Historial de envíos ──────────────────────────────────── --}}
    <div class="envio-pta-logs-card">
        <h2>📋 Historial de Envíos PTA</h2>

        @if($logs->isEmpty())
            <p class="envio-pta-no-logs">Sin registros de envíos de reportes PTA todavía.</p>
        @else
            <div class="envio-pta-table-wrap">
                <table class="envio-pta-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Fecha y Hora</th>
                            <th>OT</th>
                            <th>Clase</th>
                            <th>Destinatario</th>
                            <th>Estado</th>
                            <th>Matrícula</th>
                            <th>Enviado por</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                            <tr>
                                <td>{{ $log->id }}</td>
                                <td style="white-space:nowrap">
                                    {{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y') }}<br>
                                    <span style="font-size:11px;color:#64748b;">
                                        {{ \Carbon\Carbon::parse($log->created_at)->format('H:i:s') }}
                                    </span>
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
                                        <span style="color:#94a3b8;font-style:italic;">Sin datos</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
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
            infoText.textContent =
                `✅  OT #${otId} — ${moldura}   |   Clase: ${claseNom}`;
            infoBox.classList.add('visible');
        } else {
            infoBox.classList.remove('visible');
        }
    }

    selOt.addEventListener('change', function () { filtrarClases(this.value); });
    selClase.addEventListener('change', actualizarInfo);

    if (selOt.value) { filtrarClases(selOt.value); }

    // ── Prevenir doble envío ─────────────────────────────────────
    const form    = document.getElementById('form-envio-pta');
    const btnEnv  = document.getElementById('btn-enviar');

    form.addEventListener('submit', function () {
        btnEnv.disabled    = true;
        btnEnv.textContent = '⏳ Enviando...';
    });
});
</script>
@endsection
