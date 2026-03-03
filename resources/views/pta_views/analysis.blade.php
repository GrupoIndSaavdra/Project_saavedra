@extends('layouts.appMenu')

@section('head')
    <title>Análisis Resultados Sold. PTA</title>
    @vite(['resources/css/pta_views/analysis.css'])
@endsection

@section('background-body', 'background-image:url("' . asset("images/fondoLogin.jpg") . '")')

@section('content')
    <div class="pta-analysis-wrapper">

        {{-- Header --}}
        <div class="pta-header">
            <h2>Análisis de Resultados Sold. PTA</h2>
            <p>Vista administrativa — Visualización y liberación de resultados por OT</p>
        </div>

        {{-- Alertas --}}
        @if (session('success'))
            <div class="pta-alert pta-alert-success">✅ {{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="pta-alert pta-alert-error">❌ {{ session('error') }}</div>
        @endif

        {{-- Filtro OT --}}
        <form method="GET" action="{{ route('pta.analysis') }}">
            <div class="pta-filter-card">
                <div>
                    <label for="ot-filter">Orden de Trabajo</label>
                    <div>
                        <select name="ot_id" id="ot-filter">
                            <option value="">— Seleccionar OT —</option>
                            @foreach ($otsConPTA as $otOpt)
                                <option value="{{ $otOpt->id }}" {{ $otSeleccionadaId == $otOpt->id ? 'selected' : '' }}>
                                    OT {{ $otOpt->id }}{{ $otOpt->moldura ? ' — ' . $otOpt->moldura->nombre : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn-pta-filter">Buscar</button>
            </div>
        </form>

        {{-- Tabla de resultados --}}
        @if ($otSeleccionadaId)
            @if ($piezasPTA->isEmpty())
                <div class="pta-results-table-wrap p-3">
                    <div class="pta-empty-state">
                        <p class="mt-2">No hay piezas terminadas en Soldadura PTA para la OT seleccionada.</p>
                    </div>
                </div>
            @else
                <div class="pta-results-table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Pieza</th>
                                <th>Pico Llenado</th>
                                <th>Pico Soldadura</th>
                                <th>Conexión Llenado</th>
                                <th>Conexión Soldadura</th>
                                <th>Perfilado Llenado</th>
                                <th>Perfilado Soldadura</th>
                                <th>Img. Pico</th>
                                <th>Img. Conexión</th>
                                <th>Img. Perfilado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($piezasPTA as $pieza)
                                @php $res = $resultados->get($pieza->id); @endphp
                                <tr>
                                    <td><strong>{{ $pieza->n_pieza }}</strong></td>

                                    {{-- Resultados --}}
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

                                    {{-- Imágenes --}}
                                    <td>
                                        @if ($res && $res->imagen_pico_soldadura)
                                            <button type="button" class="btn-img-thumb"
                                                onclick="openModal('{{ Storage::url($res->imagen_pico_soldadura) }}', 'Pico Soldadura - {{ $pieza->n_pieza }}')">
                                                🖼️ Ver
                                            </button>
                                        @else
                                            <span class="badge-empty">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($res && $res->imagen_conexion_soldadura)
                                            <button type="button" class="btn-img-thumb"
                                                onclick="openModal('{{ Storage::url($res->imagen_conexion_soldadura) }}', 'Conexión Soldadura - {{ $pieza->n_pieza }}')">
                                                🖼️ Ver
                                            </button>
                                        @else
                                            <span class="badge-empty">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($res && $res->imagen_perfilado_soldadura)
                                            <button type="button" class="btn-img-thumb"
                                                onclick="openModal('{{ Storage::url($res->imagen_perfilado_soldadura) }}', 'Perfilado Soldadura - {{ $pieza->n_pieza }}')">
                                                🖼️ Ver
                                            </button>
                                        @else
                                            <span class="badge-empty">—</span>
                                        @endif
                                    </td>

                                    {{-- Acciones: badge estado + botón Liberar + botón Rechazar --}}
                                    <td>
                                        <div class="acciones-cell">
                                            @if ($res)
                                                {{-- Badge de estado actual --}}
                                                @if ($res->liberado_por_admin)
                                                    <span class="badge-liberada">&#10003; Liberada &#10003;</span>
                                                    <div style="font-size:.72rem;color:#6c757d;margin-bottom:2px;">
                                                        {{ $res->liberador->nombre ?? 'Admin' }}<br>
                                                        {{ $res->fecha_liberacion?->format('d/m/Y H:i') }}
                                                    </div>
                                                @elseif ($res->rechazado_por_admin)
                                                    <span class="badge-rechazada">&#10007; Rechazada &#10007;</span>
                                                    <div style="font-size:.72rem;color:#6c757d;margin-bottom:2px;">
                                                        {{ $res->rechazador->nombre ?? 'Admin' }}<br>
                                                        {{ $res->fecha_rechazo?->format('d/m/Y H:i') }}
                                                    </div>
                                                @else
                                                    <span class="badge-no-liberada">Pendiente</span>
                                                @endif

                                                {{-- Botón LIBERAR: visible cuando NO está liberada --}}
                                                @if (!$res->liberado_por_admin)
                                                    <form method="POST" action="{{ route('pta.results.liberar', ['id' => $res->id]) }}"
                                                        onsubmit="return confirm('¿Confirmar liberación de pieza {{ $pieza->n_pieza }}?')">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden" name="liberar" value="1">
                                                        <button type="submit" class="btn-liberar-pta">&#10003; Liberar &#10003;</button>
                                                    </form>
                                                @endif

                                                {{-- Botón RECHAZAR: visible cuando NO está rechazada --}}
                                                @if (!$res->rechazado_por_admin)
                                                    <form method="POST" action="{{ route('pta.results.rechazar', ['id' => $res->id]) }}"
                                                        onsubmit="return confirm('¿Confirmar rechazo de pieza {{ $pieza->n_pieza }}?')">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden" name="rechazar" value="1">
                                                        <button type="submit" class="btn-rechazar-pta">&#10007; Rechazar &#10007;</button>
                                                    </form>
                                                @endif
                                            @else
                                                <span class="badge-empty" style="font-size:.78rem;">Sin datos</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        @else
            <div class="pta-results-table-wrap">
                <div class="pta-empty-state">
                    <p class="mt-2">Selecciona una Orden de Trabajo para ver sus resultados.</p>
                </div>
            </div>
        @endif

    </div>

    {{-- Modal de imagen ─────────────────────────────────────────────────────── --}}
    <div class="pta-modal-overlay" id="img-modal" onclick="closeModalOnBackdrop(event)">
        <div class="pta-modal-box">
            <div class="pta-modal-header">
                <h5 id="modal-title">Imagen</h5>
                <button class="btn-modal-close" onclick="closeModal()">✕</button>
            </div>
            <div class="pta-modal-body">
                <img id="modal-img" src="" alt="Imagen soldadura PTA">
            </div>
        </div>
    </div>

    <script>
        function openModal(src, title) {
            document.getElementById('modal-img').src = src;
            document.getElementById('modal-title').textContent = title;
            document.getElementById('img-modal').classList.add('show');
        }
        function closeModal() {
            document.getElementById('img-modal').classList.remove('show');
            document.getElementById('modal-img').src = '';
        }
        function closeModalOnBackdrop(e) {
            if (e.target === document.getElementById('img-modal')) closeModal();
        }
        // ESC cierra el modal
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeModal();
        });

        // Auto-dismiss de alertas (4 s + fade-out)
        document.querySelectorAll('.pta-alert').forEach((el) => {
            setTimeout(() => {
                el.classList.add('fade-out');
                el.addEventListener('transitionend', () => el.remove(), { once: true });
            }, 4000);
        });
    </script>
@endsection
