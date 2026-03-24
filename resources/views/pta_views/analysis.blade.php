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
            <p>Vista administrativa — Visualización de resultados por OT</p>
        </div>

        {{-- Alertas --}}
        @if (session('success'))
            <div class="pta-alert pta-alert-success">&#10003; {{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="pta-alert pta-alert-error">&#10007; {{ session('error') }}</div>
        @endif

        {{-- Filtro OT + Clase --}}
        <div class="pta-selectors-row">
            {{-- 1. Selector de OT --}}
            <div class="pta-ot-selector">
                <label for="ot-select">Orden de Trabajo (OT)</label>
                <select id="ot-select" onchange="changeAnalysisOT(this.value)">
                    <option value="">— Seleccionar OT —</option>
                    @foreach ($otsConPTA as $otOpt)
                        <option value="{{ $otOpt->id }}" {{ $otSeleccionadaId == $otOpt->id ? 'selected' : '' }}>
                            OT {{ $otOpt->id }}{{ $otOpt->moldura ? ' — ' . $otOpt->moldura->nombre : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- 2. Selector de Clase --}}
            <div class="pta-clase-selector">
                <label for="clase-select">Clase</label>
                <select id="clase-select" onchange="changeAnalysisClase(this.value)" {{ !$otSeleccionadaId ? 'disabled' : '' }}>
                    <option value="">— Seleccionar Clase —</option>
                    @if($otSeleccionadaId)
                        @php $otActual = $otsConPTA->firstWhere('id', $otSeleccionadaId); @endphp
                        @if($otActual)
                            @foreach ($otActual->clases as $claseOpt)
                                <option value="{{ $claseOpt->id }}" {{ $claseSeleccionadaId == $claseOpt->id ? 'selected' : '' }}>
                                    {{ $claseOpt->nombre }}
                                </option>
                            @endforeach
                        @endif
                    @endif
                </select>
            </div>

            {{-- 3. Botón Descargar PDF (Solo si hay OT y Clase) --}}
            @if($otSeleccionadaId && $claseSeleccionadaId)
                <div class="pta-pdf-action">
                    <button type="button" class="btn-pta-pdf-icon"
                            onclick="window.location.href='{{ route('pta.analysis.pdf', ['ot_id' => $otSeleccionadaId, 'clase_id' => $claseSeleccionadaId]) }}'"
                            title="Descargar reporte en PDF">
                        <img src="{{ asset('images/pdf.png') }}" alt="PDF">
                    </button>
                </div>
            @endif
        </div>

        <script>
            function changeAnalysisOT(otId) {
                const url = new URL(window.location.href);
                if (otId) {
                    url.searchParams.set('ot_id', otId);
                    url.searchParams.delete('clase_id'); // Reiniciar clase al cambiar OT
                } else {
                    url.searchParams.delete('ot_id');
                    url.searchParams.delete('clase_id');
                }
                window.location.href = url.toString();
            }

            function changeAnalysisClase(claseId) {
                const url = new URL(window.location.href);
                if (claseId) {
                    url.searchParams.set('clase_id', claseId);
                    window.location.href = url.toString();
                }
            }
        </script>

        {{-- Tabla de resultados --}}
        @if ($otSeleccionadaId)
            @if ($piezasPTA->isEmpty())
                <div class="pta-results-table-wrap p-3">
                    <div class="pta-empty-state">
                        <p class="mt-2">No hay piezas terminadas en Soldadura PTA para la OT seleccionada.</p>
                    </div>
                </div>
            @else
                {{-- Agrupar piezasPTA por prefijo numérico (juego) --}}
                @php
                    $juegosPTA = [];
                    foreach ($piezasPTA as $pieza) {
                        // Omitir piezas que Calidad ya liberó como RECHAZADAS (liberacion == 2)
                        // para que solo aparezcan en la sección de "Juegos Defectuosos" abajo.
                        if ($pieza->liberacion == 2) continue;

                        preg_match('/^(\d+)/', $pieza->n_pieza, $m);
                        $jNum = $m[1] ?? $pieza->n_pieza;
                        $juegosPTA[$jNum][$pieza->n_pieza] = $pieza;
                    }
                    ksort($juegosPTA, SORT_NUMERIC);
                @endphp

                <div class="pta-tech-section" style="margin-top:1.5rem;">
                    <div class="pta-header" style="margin-bottom:1.5rem;">
                        <h3 style="margin:0;">Resultados y Datos Técnicos — OT {{ $ot->id }} / {{ $claseSeleccionada->nombre }}</h3>
                        <p style="margin:0.2rem 0 0;opacity:.8;">Información detallada agrupada por juego</p>
                    </div>

                    @foreach ($juegosPTA as $jNum => $piezasDelJuegoObj)
                        @php
                            $piezasKeys    = array_keys($piezasDelJuegoObj);
                            $piezasLabel   = implode(' / ', $piezasKeys);

                            $todasLiberadas = true;
                            foreach ($piezasKeys as $_k) {
                                $_pieza = $piezasPTA->firstWhere('n_pieza', $_k);
                                if (!$_pieza) { $todasLiberadas = false; break; }
                                $_res = $resultados->get($_pieza->id);
                                if (!$_res || !$_res->liberado_por_admin) { $todasLiberadas = false; break; }
                            }

                            $piezasDelJuegoTecnicos = [];
                            if (isset($piezasGroup) && $piezasGroup->isNotEmpty()) {
                                foreach ($piezasKeys as $_k) {
                                    if ($piezasGroup->has($_k)) {
                                        $piezasDelJuegoTecnicos[$_k] = $piezasGroup->get($_k);
                                    }
                                }
                            }
                        @endphp

                        <div class="pta-juego-block" style="margin-bottom:2.5rem;">
                            {{-- Header del juego --}}
                            <div class="pta-juego-header">
                                <span class="pta-juego-titulo">Juego {{ $jNum }}</span>
                                <span class="pta-juego-piezas">{{ $piezasLabel }}</span>
                                @if ($todasLiberadas)
                                    <span class="badge-si" style="margin-left:auto;">✓ Juego Liberado</span>
                                @elseif (count($piezasDelJuegoObj) < 2)
                                    <span class="badge-na" style="margin-left:auto;">Incompleto</span>
                                @else
                                    <span class="badge-empty" style="margin-left:auto;">Pendiente</span>
                                @endif
                            </div>

                            {{-- 1. Tabla General de Resultados e Imágenes --}}
                            <div class="pta-results-table-wrap" style="border-radius:0; box-shadow:none; margin:0; border-bottom:2px solid #ddd;">
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
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($piezasDelJuegoObj as $pieza)
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
                                                            onclick="openModal('{{ asset($res->imagen_pico_soldadura) }}', 'Pico Soldadura - {{ $pieza->n_pieza }}')">
                                                            🖼️ Ver
                                                        </button>
                                                    @else
                                                        <span class="badge-empty">—</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($res && $res->imagen_conexion_soldadura)
                                                        <button type="button" class="btn-img-thumb"
                                                            onclick="openModal('{{ asset($res->imagen_conexion_soldadura) }}', 'Conexión Soldadura - {{ $pieza->n_pieza }}')">
                                                            🖼️ Ver
                                                        </button>
                                                    @else
                                                        <span class="badge-empty">—</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($res && $res->imagen_perfilado_soldadura)
                                                        <button type="button" class="btn-img-thumb"
                                                            onclick="openModal('{{ asset($res->imagen_perfilado_soldadura) }}', 'Perfilado Soldadura - {{ $pieza->n_pieza }}')">
                                                            🖼️ Ver
                                                        </button>
                                                    @else
                                                        <span class="badge-empty">—</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            {{-- 2. Tabla de Datos Técnicos --}}
                            @if (!empty($piezasDelJuegoTecnicos))
                                @include('processes_views.soldaduraPTA_table_partial', [
                                    'piezasGroup'        => collect($piezasDelJuegoTecnicos),
                                    'piezas'             => collect(),
                                    'modo'               => 'reporte',
                                    'piezasGroupActivas' => collect(),
                                ])
                            @else
                                <div style="padding:1.5rem; text-align:center; color:#888; font-size:0.95rem; background:#fbfbfb;">
                                    Sin datos técnicos de soldadura registrados para este juego.
                                </div>
                            @endif
                        </div>
                    @endforeach
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

    {{-- ── Datos técnicos de Soldadura PTA por juego ─────────────────────── --}}
    @if (isset($piezasGroup) && $piezasGroup->isNotEmpty())
        @php
            // Agrupar las piezas por su prefijo numérico (juego)
            // Ej: "2H" y "2M" → juego "2"
            $juegosTecnicos = [];
            foreach ($piezasGroup as $nPieza => $subFilas) {
                preg_match('/^(\d+)/', $nPieza, $m);
                $juegoNum = $m[1] ?? $nPieza;
                $juegosTecnicos[$juegoNum][$nPieza] = $subFilas;
            }
            ksort($juegosTecnicos, SORT_NUMERIC);

            // Filtrar para mostrar SOLO los juegos que salieron "mal" según el reporte del operador
            $juegosTecnicosMalos = [];
            foreach ($juegosTecnicos as $jNum => $piezasDelJuego) {
                $esMalo = false;

                // 1. Revisar si calidad liberó como Rechazado (liberacion == 2)
                foreach ($piezasDelJuego as $nPieza => $subFilas) {
                    $piezaEnPiezas = $piezasPTA->firstWhere('n_pieza', $nPieza);
                    if ($piezaEnPiezas && $piezaEnPiezas->liberacion == 2) {
                        $esMalo = true;
                        break;
                    }
                }

                if ($esMalo) {
                    $juegosTecnicosMalos[$jNum] = $piezasDelJuego;
                }
            }
        @endphp

        <div class="pta-tech-section" style="margin-top:2rem; padding: 0 1.5rem; margin-bottom: 2rem;">
            <div class="pta-header" style="margin-bottom:1.5rem;">
                <h3 style="margin:0;">Juegos Defectuosos — OT {{ $ot->id }} / {{ $claseSeleccionada->nombre }}</h3>
                <p style="margin:0.2rem 0 0;opacity:.8;">Detalles técnicos de los juegos que salieron mal (solo lectura)</p>
            </div>

            @if (empty($juegosTecnicosMalos))
                <div style="padding:1.5rem; text-align:center; color:#888; font-size:0.95rem; background:#fbfbfb; border-radius: 8px; border: 1px dashed #ccc;">
                    Ningún juego de esta OT presenta errores o defectos técnicos.
                </div>
            @else
                @foreach ($juegosTecnicosMalos as $jNum => $piezasDelJuego)
                    @php
                        $piezasKeys    = array_keys($piezasDelJuego);
                        $piezasLabel   = implode(' / ', $piezasKeys);

                        // Determinar si todas las piezas del juego están liberadas
                        $todasLiberadas = true;
                        foreach ($piezasKeys as $_k) {
                            $_pieza = $piezasPTA->firstWhere('n_pieza', $_k);
                            if (!$_pieza) { $todasLiberadas = false; break; }
                            $_res = $resultados->get($_pieza->id);
                            if (!$_res || !$_res->liberado_por_admin) { $todasLiberadas = false; break; }
                        }
                    @endphp

                    <div class="pta-juego-block" style="margin-bottom:2rem;">
                        {{-- Header del juego --}}
                        <div class="pta-juego-header">
                            <span class="pta-juego-titulo" style="color:#d32f2f;">Juego {{ $jNum }} (Con Defectos)</span>
                            <span class="pta-juego-piezas">{{ $piezasLabel }}</span>
                            @if ($todasLiberadas)
                                <span class="badge-si" style="margin-left:auto;">✓ Juego Liberado</span>
                            @elseif (count($piezasDelJuego) < 2)
                                <span class="badge-na" style="margin-left:auto;">Incompleto</span>
                            @else
                                <span class="badge-empty" style="margin-left:auto;">Pendiente</span>
                            @endif
                        </div>

                        {{-- Tabla con solo las piezas de este juego --}}
                        @include('processes_views.soldaduraPTA_table_partial', [
                            'piezasGroup' => collect($piezasDelJuego),
                            'piezas' => collect(),
                            'modo' => 'reporte',
                            'piezasGroupActivas' => collect(),
                        ])

                    </div>
                @endforeach
            @endif
        </div>
    @elseif (isset($ot) && $ot)
            <div style="margin-top:1.5rem;padding:1rem 1.5rem;background:#f7f7f7;border-radius:10px;color:#888;font-size:.9rem;text-align:center;">
                    No hay datos técnicos de soldadura registrados para esta OT.
                </div>
        @endif

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
