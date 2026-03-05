@extends('layouts.appMenu')

@section('head')
    <title>Resultados Sold. PTA — OT {{ $ot->id }}</title>
    @vite(['resources/css/pta_views/results.css'])
@endsection

@section('background-body', 'background-image:url("' . asset("images/fondoLogin.jpg") . '")')

@section('content')
    <div class="pta-wrapper">

        {{-- Header --}}
        <div class="pta-header">
            <div>
                <h2>Resultados de Soldadura PTA</h2>
                <p>Orden de Trabajo (OT):
                    <strong>{{ $ot->id }}{{ $ot->moldura ? ' — ' . $ot->moldura->nombre : '' }}</strong> — Registro y
                    edición de resultados por pieza
                </p>
            </div>
        </div>

        {{-- Alertas --}}
        @if (session('success'))
            <div class="pta-alert pta-alert-success">✔ {{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="pta-alert pta-alert-error">X {{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="pta-alert pta-alert-error">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Selectores en la misma línea --}}
        <div class="pta-selectors-row">
            {{-- Selector de OT --}}
            <div class="pta-ot-selector">
                <label for="ot-select">OT</label>
                <select id="ot-select" onchange="changeOT(this.value)">
                    <option value="">— Cambiar OT —</option>
                    @foreach ($otsConPTA as $otOpt)
                        <option value="{{ $otOpt->id }}" {{ $ot->id == $otOpt->id ? 'selected' : '' }}>
                            OT {{ $otOpt->id }}{{ $otOpt->moldura ? ' — ' . $otOpt->moldura->nombre : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Selector de pieza --}}
            <div class="pta-piece-selector">
                <label for="piece-select">Pieza</label>
                <select id="piece-select" onchange="changePiece(this.value)">
                    @foreach ($piezas as $pieza)
                        @php $tieneRes = isset($todosResultados[$pieza->id]); @endphp
                        <option value="{{ $pieza->id }}" {{ $pieza->id == $piezaSeleccionada->id ? 'selected' : '' }}>
                            {{ $pieza->n_pieza }} — {{ $tieneRes ? 'Guardado' : 'Pendiente' }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Formulario de resultados --}}
        <form action="{{ route('pta.results.store', ['ot_id' => $ot->id]) }}" method="POST" enctype="multipart/form-data"
            id="pta-form">
            @csrf

            <input type="hidden" name="pieza_id" value="{{ $piezaSeleccionada->id }}">
            <input type="hidden" name="n_pieza" value="{{ $piezaSeleccionada->n_pieza }}">

            <div class="pta-form-card">
                <div class="pta-form-card-header">
                    Resultados Técnicos — Pieza: <strong>{{ $piezaSeleccionada->n_pieza }}</strong>
                    @if(isset($todosResultados[$piezaSeleccionada->id]))
                        <span style="font-size:.8rem;font-weight:400;opacity:.9;"> (editando resultado existente)</span>
                    @endif
                </div>
                <div class="pta-form-card-body">
                    <div class="pta-grid">

                        {{-- Resultado Pico Llenado --}}
                        <div class="pta-field">
                            <label for="res_pico_llenado">Resultado Pico Llenado</label>
                            <select name="resultado_pico_llenado" id="res_pico_llenado" required>
                                <option value="">— Seleccionar —</option>
                                @foreach (['Si', 'No', 'No Aplica'] as $opt)
                                    <option value="{{ $opt }}" {{ old('resultado_pico_llenado', $resultado->resultado_pico_llenado ?? '') == $opt ? 'selected' : '' }}>
                                        {{ $opt }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Resultado Pico Soldadura --}}
                        <div class="pta-field">
                            <label for="res_pico_sold">Resultado Pico Soldadura</label>
                            <select name="resultado_pico_soldadura" id="res_pico_sold" required>
                                <option value="">— Seleccionar —</option>
                                @foreach (['Si', 'No', 'No Aplica'] as $opt)
                                    <option value="{{ $opt }}" {{ old('resultado_pico_soldadura', $resultado->resultado_pico_soldadura ?? '') == $opt ? 'selected' : '' }}>
                                        {{ $opt }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Evidencia Pico Soldadura --}}
                        <div class="pta-field">
                            <label>Evidencia Pico Soldadura</label>
                            <div class="pta-img-wrapper" style="margin-top: 0;">
                                @php $imgPs = $resultado->imagen_pico_soldadura ?? null; @endphp
                                <label class="pta-img-btn-label" for="img_pico_sold">Subir imagen</label>
                                <input type="file" id="img_pico_sold" name="imagen_pico_soldadura" accept="image/*"
                                    class="d-none"
                                    onchange="updateFileLabel(this, 'status_pico_sold', 'preview_pico_sold')">
                                <div id="status_pico_sold" class="pta-img-status {{ $imgPs ? 'filled' : 'empty' }}">
                                    {{ $imgPs ? '✔ - ' . basename($imgPs) : 'Vacío' }}
                                </div>
                                <div
                                    style="margin-top: 10px; display: {{ $imgPs ? 'flex' : 'none' }}; justify-content: center; align-items: center;">
                                    <img id="preview_pico_sold" src="{{ $imgPs ? asset($imgPs) : '#' }}" alt="Vista Previa"
                                        style="max-width: 100%; max-height: 250px; border-radius: 6px; border: 1px solid #dce8f5; object-fit: contain;">
                                </div>
                            </div>
                        </div>

                        {{-- Resultado Conexion Llenado --}}
                        <div class="pta-field">
                            <label for="res_con_llenado">Resultado Conexion Llenado</label>
                            <select name="resultado_conexion_llenado" id="res_con_llenado" required>
                                <option value="">— Seleccionar —</option>
                                @foreach (['Si', 'No', 'No Aplica'] as $opt)
                                    <option value="{{ $opt }}" {{ old('resultado_conexion_llenado', $resultado->resultado_conexion_llenado ?? '') == $opt ? 'selected' : '' }}>
                                        {{ $opt }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Resultado Conexion Soldadura --}}
                        <div class="pta-field">
                            <label for="res_con_sold">Resultado Conexion Soldadura</label>
                            <select name="resultado_conexion_soldadura" id="res_con_sold" required>
                                <option value="">— Seleccionar —</option>
                                @foreach (['Si', 'No', 'No Aplica'] as $opt)
                                    <option value="{{ $opt }}" {{ old('resultado_conexion_soldadura', $resultado->resultado_conexion_soldadura ?? '') == $opt ? 'selected' : '' }}>
                                        {{ $opt }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Evidencia Conexion Soldadura --}}
                        <div class="pta-field">
                            <label>Evidencia Conexion Soldadura</label>
                            <div class="pta-img-wrapper" style="margin-top: 0;">
                                @php $imgCs = $resultado->imagen_conexion_soldadura ?? null; @endphp
                                <label class="pta-img-btn-label" for="img_con_sold">Subir imagen</label>
                                <input type="file" id="img_con_sold" name="imagen_conexion_soldadura" accept="image/*"
                                    class="d-none" onchange="updateFileLabel(this, 'status_con_sold', 'preview_con_sold')">
                                <div id="status_con_sold" class="pta-img-status {{ $imgCs ? 'filled' : 'empty' }}">
                                    {{ $imgCs ? '✔ - ' . basename($imgCs) : 'Vacío' }}
                                </div>
                                <div
                                    style="margin-top: 10px; display: {{ $imgCs ? 'flex' : 'none' }}; justify-content: center; align-items: center;">
                                    <img id="preview_con_sold" src="{{ $imgCs ? asset($imgCs) : '#' }}" alt="Vista Previa"
                                        style="max-width: 100%; max-height: 250px; border-radius: 6px; border: 1px solid #dce8f5; object-fit: contain;">
                                </div>
                            </div>
                        </div>

                        {{-- Resultado Perfilado Llenado --}}
                        <div class="pta-field">
                            <label for="res_perf_llenado">Resultado Perfilado Llenado</label>
                            <select name="resultado_perfilado_llenado" id="res_perf_llenado" required>
                                <option value="">— Seleccionar —</option>
                                @foreach (['Si', 'No', 'No Aplica'] as $opt)
                                    <option value="{{ $opt }}" {{ old('resultado_perfilado_llenado', $resultado->resultado_perfilado_llenado ?? '') == $opt ? 'selected' : '' }}>
                                        {{ $opt }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Resultado Perfilado Soldadura --}}
                        <div class="pta-field">
                            <label for="res_perf_sold">Resultado Perfilado Soldadura</label>
                            <select name="resultado_perfilado_soldadura" id="res_perf_sold" required>
                                <option value="">— Seleccionar —</option>
                                @foreach (['Si', 'No', 'No Aplica'] as $opt)
                                    <option value="{{ $opt }}" {{ old('resultado_perfilado_soldadura', $resultado->resultado_perfilado_soldadura ?? '') == $opt ? 'selected' : '' }}>
                                        {{ $opt }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Evidencia Perfilado Soldadura --}}
                        <div class="pta-field">
                            <label>Evidencia Perfilado Soldadura</label>
                            <div class="pta-img-wrapper" style="margin-top: 0;">
                                @php $imgPs2 = $resultado->imagen_perfilado_soldadura ?? null; @endphp
                                <label class="pta-img-btn-label" for="img_perf_sold">Subir imagen</label>
                                <input type="file" id="img_perf_sold" name="imagen_perfilado_soldadura" accept="image/*"
                                    class="d-none"
                                    onchange="updateFileLabel(this, 'status_perf_sold', 'preview_perf_sold')">
                                <div id="status_perf_sold" class="pta-img-status {{ $imgPs2 ? 'filled' : 'empty' }}">
                                    {{ $imgPs2 ? '✔ - ' . basename($imgPs2) : 'Vacío' }}
                                </div>
                                <div
                                    style="margin-top: 10px; display: {{ $imgPs2 ? 'flex' : 'none' }}; justify-content: center; align-items: center;">
                                    <img id="preview_perf_sold" src="{{ $imgPs2 ? asset($imgPs2) : '#' }}"
                                        alt="Vista Previa"
                                        style="max-width: 100%; max-height: 250px; border-radius: 6px; border: 1px solid #dce8f5; object-fit: contain;">
                                </div>
                            </div>
                        </div>

                    </div>{{-- /pta-grid --}}
                </div>{{-- /card-body --}}
            </div>{{-- /pta-form-card --}}

            <div class="pta-actions">
                <button type="submit" class="btn-pta-save" id="btn-guardar">
                    Guardar Resultados
                </button>
                <button type="button" onclick="window.location.href='{{ route('showPiecesInProgress') }}'"
                    class="btn-pta-back">← Regresar</button>
            </div>

        </form>

        {{-- ---------------- Tabla resumen de todas las piezas ---------------- --}}
        <div class="pta-overview-wrap">
            <h4>Resumen de Piezas — OT {{ $ot->id }}</h4>
            <table>
                <thead>
                    <tr>
                        <th>Pieza</th>
                        <th>Pico Llen.</th>
                        <th>Pico Sold.</th>
                        <th>Conex. Llen.</th>
                        <th>Conex. Sold.</th>
                        <th>Perf. Llen.</th>
                        <th>Perf. Sold.</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($piezas as $pieza)
                        @php $res = $todosResultados->get($pieza->id); @endphp
                        <tr class="{{ $pieza->id == $piezaSeleccionada->id ? 'row-selected' : '' }}">
                            <td><strong>{{ $pieza->n_pieza }}</strong></td>

                            @php
                                $campos = [
                                    $res->resultado_pico_llenado ?? null,
                                    $res->resultado_pico_soldadura ?? null,
                                    $res->resultado_conexion_llenado ?? null,
                                    $res->resultado_conexion_soldadura ?? null,
                                    $res->resultado_perfilado_llenado ?? null,
                                    $res->resultado_perfilado_soldadura ?? null,
                                ];
                            @endphp
                            @foreach ($campos as $campo)
                                <td>
                                    @if ($campo === 'Si')
                                        <span class="badge-si">&#10003;</span>
                                    @elseif ($campo === 'No')
                                        <span class="badge-no">&#10007;</span>
                                    @elseif ($campo === 'No Aplica')
                                        <span class="badge-na">N/A</span>
                                    @else
                                        <span class="badge-empty">—</span>
                                    @endif
                                </td>
                            @endforeach

                            <td>
                                @if ($res && $res->liberado_por_admin)
                                    <span class="badge-si">✓ Liberada</span>
                                @elseif ($res)
                                    <span class="badge-na">Guardada</span>
                                @else
                                    <span class="badge-empty">Pendiente</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>

    <script>
        function updateFileLabel(input, statusId, previewId) {
            const el = document.getElementById(statusId);
            const previewImg = previewId ? document.getElementById(previewId) : null;
            const previewContainer = previewImg ? previewImg.parentElement : null;

            if (input.files && input.files.length > 0) {
                const file = input.files[0];
                el.textContent = '✔ - ' + file.name;
                el.className = 'pta-img-status filled';

                if (previewImg && previewContainer) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        previewImg.src = e.target.result;
                        previewContainer.style.display = 'flex';
                    }
                    reader.readAsDataURL(file);
                }
            } else {
                el.textContent = 'Vacío';
                el.className = 'pta-img-status empty';

                if (previewImg && previewContainer) {
                    previewImg.src = '#';
                    previewContainer.style.display = 'none';
                }
            }
        }

        function changePiece(piezaId) {
            const url = new URL(window.location.href);
            url.searchParams.set('pieza_id', piezaId);
            window.location.href = url.toString();
        }

        function changeOT(otId) {
            if (otId) window.location.href = `{{ url('admin/pta/results') }}/${otId}`;
        }

        // Auto-dismiss alertas (4 s)
        document.querySelectorAll('.pta-alert').forEach((el) => {
            setTimeout(() => {
                el.classList.add('fade-out');
                el.addEventListener('transitionend', () => el.remove(), { once: true });
            }, 4000);
        });
    </script>
@endsection
