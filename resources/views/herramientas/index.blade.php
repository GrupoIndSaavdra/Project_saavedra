@extends('layouts.appMenu')

@section('head')
    <title>Herramientas Tecamac | GIS</title>
    <meta name="description"
        content="Catálogo de Herramientas y Tornillería Tecamac: descripción, inserto, condiciones de corte, imágenes y stock.">
    @vite([
        'resources/css/herramientas_views/herramientas_tecamac.css',
        'resources/js/herramientas_views/herramientas_tecamac.js',
    ])
@endsection

@section('background-body', 'background-image:url("' . asset('images/fondoLogin.jpg') . '")')

@php
/** Paleta pastel por proceso (soft, legible) */
$procesoPaleta = [
    'Cepillado'              => ['bg' => '#e8f5e9', 'text' => '#2e7d32', 'border' => '#a5d6a7'],
    'Desbaste Exterior'      => ['bg' => '#e3f2fd', 'text' => '#1565c0', 'border' => '#90caf9'],
    'Revisión Laterales'     => ['bg' => '#fce4ec', 'text' => '#880e4f', 'border' => '#f48fb1'],
    '1ª Operación'           => ['bg' => '#fff3e0', 'text' => '#e65100', 'border' => '#ffcc80'],
    'Barreno Maniobra'       => ['bg' => '#f3e5f5', 'text' => '#6a1b9a', 'border' => '#ce93d8'],
    '2ª Operación'           => ['bg' => '#e0f7fa', 'text' => '#006064', 'border' => '#80deea'],
    'Soldadura'              => ['bg' => '#fafafa', 'text' => '#37474f', 'border' => '#b0bec5'],
    'Soldadura PTA'          => ['bg' => '#f1f8e9', 'text' => '#33691e', 'border' => '#aed581'],
    'Rectificado'            => ['bg' => '#e8eaf6', 'text' => '#283593', 'border' => '#9fa8da'],
    'Asentado'               => ['bg' => '#fbe9e7', 'text' => '#bf360c', 'border' => '#ffab91'],
    'Calificado'             => ['bg' => '#e0f2f1', 'text' => '#004d40', 'border' => '#80cbc4'],
    'Acabado Bombillo'       => ['bg' => '#fff8e1', 'text' => '#ff6f00', 'border' => '#ffe082'],
    'Acabado Molde'          => ['bg' => '#ede7f6', 'text' => '#4527a0', 'border' => '#b39ddb'],
    'Barreno Profundidad'    => ['bg' => '#e8f5e9', 'text' => '#1b5e20', 'border' => '#81c784'],
    'Cavidades'              => ['bg' => '#e3f2fd', 'text' => '#0d47a1', 'border' => '#64b5f6'],
    'Copiado'                => ['bg' => '#fce4ec', 'text' => '#ad1457', 'border' => '#f06292'],
    'OffSet'                 => ['bg' => '#fff9c4', 'text' => '#827717', 'border' => '#fff176'],
    'Palomas'                => ['bg' => '#f9fbe7', 'text' => '#558b2f', 'border' => '#c5e1a5'],
    'Rebajes'                => ['bg' => '#fdf6e3', 'text' => '#8d6e63', 'border' => '#d7ccc8'],
    'Grabado'                => ['bg' => '#e8eaf6', 'text' => '#1a237e', 'border' => '#7986cb'],
    'Operación Equipo'       => ['bg' => '#f3e5f5', 'text' => '#7b1fa2', 'border' => '#ba68c8'],
    'Embudo CM'              => ['bg' => '#e0f7fa', 'text' => '#00838f', 'border' => '#4dd0e1'],
    '1ª Op. Cabeza Soplo'    => ['bg' => '#fbe9e7', 'text' => '#d84315', 'border' => '#ff8a65'],
    '2ª Op. Cabeza Soplo'    => ['bg' => '#e8f5e9', 'text' => '#388e3c', 'border' => '#66bb6a'],
];

function paletaProceso($proceso, &$paleta) {
    return $paleta[$proceso] ?? ['bg' => '#f5f5f5', 'text' => '#555', 'border' => '#ccc'];
}
@endphp

@section('content')

<div class="ht-wrapper">

    {{-- ── HEADER ──────────────────────────────────────────────────────── --}}
    <div class="ht-header">
        <div class="ht-header-icon">
            <img src="{{ asset('images/herramientas.png') }}" alt="Herramientas" style="width:85px;">
        </div>
        <div class="ht-header-text">
            <h1>Herramientas Tecamac</h1>
            <p>Catálogo de herramientas de corte y tornillería — Planta Tecamac.</p>
        </div>
        @if($esCrud)
            <span class="ht-crud-badge">✎ Edición completa</span>
        @endif
    </div>

    {{-- ── STATS ───────────────────────────────────────────────────────── --}}
    <div class="ht-stats">
        <a href="{{ route('herramientas.tecamac.index', ['modo' => 'activas']) }}"
           class="ht-stat-card stat-activas {{ $modo === 'activas' ? 'stat-activo' : '' }}">
            <div class="ht-stat-icon">🔧</div>
            <div>
                <div class="ht-stat-value">{{ $totalActivas }}</div>
                <div class="ht-stat-label">Herramientas Activas</div>
                <div class="{{ $modo === 'activas' ? 'stat-modo-badge' : 'stat-click-hint' }}">
                    {{ $modo === 'activas' ? 'Mostrando ahora' : 'Clic para ver' }}
                </div>
            </div>
        </a>

        <a href="{{ route('herramientas.tecamac.index', ['modo' => 'stock_bajo']) }}"
           class="ht-stat-card stat-stock {{ $modo === 'stock_bajo' ? 'stat-activo' : '' }}">
            <div class="ht-stat-icon">{{ $totalStockBajo > 0 ? '⚠️' : '✅' }}</div>
            <div>
                <div class="ht-stat-value {{ $totalStockBajo > 0 ? 'valor-rojo' : '' }}">{{ $totalStockBajo }}</div>
                <div class="ht-stat-label">Con Stock Bajo
                    @if($totalStockBajo > 0)<span class="stock-bajo-tip">cantidad &lt; mínimo</span>@endif
                </div>
                <div class="{{ $modo === 'stock_bajo' ? 'stat-modo-badge warning' : 'stat-click-hint' }}">
                    {{ $modo === 'stock_bajo' ? 'Mostrando ahora' : 'Clic para ver' }}
                </div>
            </div>
        </a>

        <a href="{{ route('herramientas.tecamac.index', ['modo' => 'inactivas']) }}"
           class="ht-stat-card stat-inactivas {{ $modo === 'inactivas' ? 'stat-activo' : '' }}">
            <div class="ht-stat-icon">📦</div>
            <div>
                <div class="ht-stat-value">{{ $totalInactivas }}</div>
                <div class="ht-stat-label">Inactivas</div>
                <div class="{{ $modo === 'inactivas' ? 'stat-modo-badge' : 'stat-click-hint' }}">
                    {{ $modo === 'inactivas' ? 'Mostrando ahora' : 'Clic para ver' }}
                </div>
            </div>
        </a>
    </div>

    {{-- ── TOOLBAR ─────────────────────────────────────────────────────── --}}
    <div class="ht-toolbar">
        {{-- Búsqueda por nombre --}}
        <div class="ht-search-wrap">
            <span class="ht-search-icon">🔍</span>
            <input type="text" id="ht-search" class="ht-search-input"
                   placeholder="Buscar nombre, descripción…"
                   value="{{ $busqueda }}" autocomplete="off">
        </div>

        {{-- Filtro por nombre de herramienta --}}
        <div class="ht-search-wrap">
            <span class="ht-search-icon">🏷</span>
            <input type="text" id="ht-filter-nombre" class="ht-search-input"
                   placeholder="Filtrar por nombre herramienta…"
                   value="" autocomplete="off">
        </div>



        @if($esAlta && $modo === 'activas')
            <button id="ht-btn-nuevo" class="ht-btn-nuevo">＋ Nueva Herramienta</button>
        @endif
    </div>

    {{-- ── BANNER CONTEXTUAL ───────────────────────────────────────────── --}}
    @if($modo === 'stock_bajo')
        <div class="ht-banner banner-warning">⚠️ Mostrando herramientas cuya <strong>cantidad es menor al mínimo</strong>.</div>
    @elseif($modo === 'inactivas')
        <div class="ht-banner banner-info">📦 Herramientas dadas de baja.
            @if($esCrud) Usa <strong>Reactivar</strong> para devolverlas al catálogo. @endif
        </div>
    @endif

    {{-- ── TABLA ───────────────────────────────────────────────────────── --}}
    <div class="ht-table-card">
        <div class="ht-table-header">
            <h2>
                @if($modo === 'stock_bajo') ⚠️ Stock Bajo
                @elseif($modo === 'inactivas') 📦 Inactivas
                @else 🔧 Catálogo — Herramientas Tecamac
                @endif

            </h2>
            <span class="ht-results-count" id="ht-count">
                {{ $herramientas->count() }} resultado{{ $herramientas->count() !== 1 ? 's' : '' }}
            </span>
        </div>

        @if($herramientas->isEmpty())
            <div class="ht-empty">
                <div class="ht-empty-icon">
                    @if($modo === 'stock_bajo') ✅ @elseif($modo === 'inactivas') 📦 @else 🔧 @endif
                </div>
                <p>
                    @if($filtroProceso) No hay herramientas para el proceso <strong>{{ $filtroProceso }}</strong>.
                    @elseif($modo === 'stock_bajo') No hay herramientas con stock bajo.
                    @elseif($modo === 'inactivas') No hay herramientas inactivas.
                    @else No hay herramientas registradas.
                        @if($esAlta) Usa <strong>Nueva Herramienta</strong>. @endif
                    @endif
                </p>
            </div>
        @else
            <div class="ht-table-scroll">
                <table class="ht-table">
                    <thead>
                        <tr class="ht-thead-group">
                            <th colspan="5" class="th-group-herramientas">⚙️ HERRAMIENTAS</th>
                            <th colspan="3" class="th-group-condiciones">🔴 CONDICIONES DE CORTE PULG.</th>
                            <th colspan="1" class="th-group-tornilleria">🔩 TORNILLERÍA</th>
                            <th colspan="1" class="th-group-fisica">🖼️ IMAGEN FÍSICA</th>
                            <th colspan="2" class="th-group-stock">📊 STOCK</th>
                            @if($esCrud)<th rowspan="2" class="th-acciones">ACCIONES</th>@endif
                        </tr>
                        <tr class="ht-thead-cols">
                            <th>NOMBRE<br>HERRAMIENTA</th>
                            <th>INSERTO</th>
                            <th>CANTIDAD</th>
                            <th>FOTOS<br>HERRAMIENTA</th>
                            <th>FOTOS<br>ACCESORIO</th>
                            <th class="th-sub-condicion">PROF.<br>CORTE</th>
                            <th class="th-sub-condicion">RPM</th>
                            <th class="th-sub-condicion">AVANCES</th>
                            <th class="th-sub-tornilleria">FOTOS<br>TORNILLERÍA</th>
                            <th class="th-sub-fisica">IMAGEN<br>FÍSICA</th>
                            <th class="th-minmax">MÍN.</th>
                            <th class="th-minmax">MÁX.</th>
                        </tr>
                    </thead>
                    <tbody id="ht-tbody">
                        @foreach($herramientas as $h)
                            @php
                                $stockBajoFila   = $h->minimo !== null && $h->cantidad_portaherramientas < $h->minimo;
                                $imgsHerramienta = $h->imagenesPorTipo('herramienta');
                                $imgsAccesorio   = $h->imagenesPorTipo('accesorio');
                                $imgsTornilleria = $h->imagenesPorTipo('tornilleria');
                                $imgsFisica      = $h->imagenesPorTipo('imagen_fisica');
                                $procesos        = is_array($h->proceso) ? $h->proceso : [];
                                $imgData = $h->imagenes->map(fn($i) => [
                                    'id'     => $i->id,
                                    'tipo'   => $i->tipo,
                                    'nombre' => $i->nombre,
                                    'url'    => asset($i->ruta),
                                ])->values()->toJson(JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
                            @endphp
                            <tr class="ht-row {{ $stockBajoFila ? 'fila-stock-bajo' : '' }}"
                                data-id="{{ $h->id }}"
                                data-search="{{ strtolower(implode(' ', $procesos) . ' ' . $h->nombre_herramienta . ' ' . $h->descripcion_herramienta . ' ' . $h->descripcion_inserto) }}"
                                data-nombre-herramienta="{{ $h->nombre_herramienta }}"
                                data-proceso='{{ json_encode($procesos) }}'
                                data-desc="{{ $h->descripcion_herramienta }}"
                                data-inserto="{{ $h->descripcion_inserto }}"
                                data-cantidad="{{ $h->cantidad_portaherramientas }}"
                                data-profundidad="{{ $h->profundidad_corte }}"
                                data-rpm="{{ $h->rpm }}"
                                data-avances="{{ $h->avances }}"
                                data-minimo="{{ $h->minimo }}"
                                data-maximo="{{ $h->maximo }}"
                                data-imgs='{{ $imgData }}'>

                                {{-- NOMBRE HERRAMIENTA --}}
                                <td class="td-desc">
                                    @if($h->nombre_herramienta)
                                        <div class="ht-nombre-label">{{ $h->nombre_herramienta }}</div>
                                    @endif
                                    @if($h->descripcion_herramienta)
                                        <div class="ht-desc-label">{{ $h->descripcion_herramienta }}</div>
                                    @endif
                                    @if(!$h->activo)
                                        <span class="badge-inactiva">Inactiva</span>
                                    @endif
                                </td>

                                <td class="d-center">{{ $h->descripcion_inserto ?? '—' }}</td>

                                <td class="d-center">
                                    @if($stockBajoFila)
                                        <span class="ht-cantidad-badge ht-stock-low" title="Mín: {{ $h->minimo }}">
                                            {{ $h->cantidad_portaherramientas }}<span class="stock-low-arrow">▼</span>
                                        </span>
                                    @else
                                        <span class="ht-cantidad-badge">{{ $h->cantidad_portaherramientas }}</span>
                                    @endif
                                </td>

                                <td class="d-center">@include('herramientas._mini_gallery', ['imgs' => $imgsHerramienta])</td>

                                <td class="d-center">@include('herramientas._mini_gallery', ['imgs' => $imgsAccesorio])</td>

                                {{-- CONDICIONES DE CORTE --}}
                                <td class="d-center">{{ $h->profundidad_corte !== null ? number_format($h->profundidad_corte, 3) : '—' }}</td>
                                <td class="d-center">{{ $h->rpm !== null ? number_format($h->rpm) : '—' }}</td>
                                <td class="d-center">{{ $h->avances ?? '—' }}</td>

                                {{-- TORNILLERÍA --}}
                                <td class="d-center">@include('herramientas._mini_gallery', ['imgs' => $imgsTornilleria])</td>

                                {{-- IMAGEN FÍSICA --}}
                                <td class="d-center">@include('herramientas._mini_gallery', ['imgs' => $imgsFisica])</td>

                                <td class="d-center">
                                    @if($h->minimo !== null)
                                        <span class="ht-minmax-val {{ $stockBajoFila ? 'minimo-alerta' : '' }}">{{ $h->minimo }}</span>
                                    @else <span class="ht-na">—</span> @endif
                                </td>
                                <td class="d-center">
                                    @if($h->maximo !== null)
                                        <span class="ht-minmax-val">{{ $h->maximo }}</span>
                                    @else <span class="ht-na">—</span> @endif
                                </td>

                                @if($esCrud)
                                <td class="d-center">
                                    <div class="ht-actions">
                                        @if($h->activo)
                                            <button class="ht-btn-accion ht-btn-edit"
                                                    onclick="htAbrirEditar({{ $h->id }})" title="Editar">✎</button>
                                            <button class="ht-btn-accion ht-btn-delete"
                                                    onclick="htEliminar({{ $h->id }}, '{{ addslashes($h->descripcion_herramienta) }}')" title="Dar de baja">✕</button>
                                        @else
                                            <button class="ht-btn-accion ht-btn-reactivar"
                                                    onclick="htReactivar({{ $h->id }}, '{{ addslashes($h->descripcion_herramienta) }}')" title="Reactivar">↺ Reactivar</button>
                                        @endif
                                    </div>
                                </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="ht-empty" id="ht-no-results" style="display:none;">
                    <div class="ht-empty-icon">🔍</div>
                    <p>Ninguna herramienta coincide con la búsqueda.</p>
                </div>
            </div>
        @endif
    </div>
</div>{{-- /.ht-wrapper --}}

{{-- ════════════════════════════════════════════════
     MODAL CRUD — Admin / Almacén
════════════════════════════════════════════════ --}}
@if($esCrud)
<div class="ht-modal-overlay" id="ht-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="ht-modal-title">
    <div class="ht-modal">
        <div class="ht-modal-header">
            <div>
                <h3 id="ht-modal-title">Nueva Herramienta</h3>
                <p class="ht-modal-subtitle" id="ht-modal-subtitle">Complete los datos de la herramienta</p>
            </div>
            <button class="ht-modal-close" id="ht-modal-close" type="button">✕</button>
        </div>
        <div class="ht-modal-body">
            <form id="ht-form" enctype="multipart/form-data" novalidate>
                @csrf


                {{-- ═══════════════════════════════
                     SECCIÓN: HERRAMIENTA
                ═══════════════════════════════ --}}
                <div class="ht-modal-section">
                    <div class="ht-modal-section-header" style="background:linear-gradient(135deg,#033966,#0d5ca8);">
                        <span>⚙️ Herramienta</span>
                    </div>
                    <div class="ht-modal-section-body">
                        <div class="ht-form-grid">
                            <div class="ht-form-field">
                                <label for="ht-f-nombre">Nombre de la Herramienta</label>
                                <input type="text" id="ht-f-nombre" name="nombre_herramienta"
                                       placeholder="Ej. PORTAINSERTO IZQUIERDO" maxlength="255">
                            </div>

                            <div class="ht-form-field">
                                <label for="ht-f-inserto">Descripción de Inserto</label>
                                <input type="text" id="ht-f-inserto" name="descripcion_inserto"
                                       placeholder="Ej. WNMG080412" maxlength="255">
                            </div>
                            <div class="ht-form-field">
                                <label for="ht-f-cantidad">Cantidad Planta Tecamac <span class="req">*</span></label>
                                <input type="number" id="ht-f-cantidad" name="cantidad_portaherramientas"
                                       min="0" value="0" required>
                            </div>
                        </div>
                        <div class="ht-img-section">
                            <div class="ht-img-section-header herramienta-color">
                                📷 Foto de inserto
                                <button type="button" class="ht-btn-add-img" onclick="htAgregarFoto('herramienta')">+ Agregar</button>
                            </div>
                            <div class="ht-img-list" id="ht-imgs-herramienta"></div>
                        </div>
                    </div>
                </div>

                {{-- ═══════════════════════════════
                     SECCIÓN: ACCESORIO
                ═══════════════════════════════ --}}
                <div class="ht-modal-section">
                    <div class="ht-modal-section-header" style="background:linear-gradient(135deg,#4a148c,#6a1b9a);">
                        <span>🔩 Accesorio de Herramienta</span>
                    </div>
                    <div class="ht-modal-section-body">
                        <div class="ht-img-section">
                            <div class="ht-img-section-header accesorio-color">
                                📷 Fotos de Accesorio
                                <button type="button" class="ht-btn-add-img" onclick="htAgregarFoto('accesorio')">+ Agregar</button>
                            </div>
                            <div class="ht-img-list" id="ht-imgs-accesorio"></div>
                        </div>
                    </div>
                </div>

                {{-- ═══════════════════════════════
                     SECCIÓN: TORNILLERÍA
                ═══════════════════════════════ --}}
                <div class="ht-modal-section">
                    <div class="ht-modal-section-header" style="background:linear-gradient(135deg,#7a5200,#a06a00);">
                        <span>🔩 Tornillería</span>
                    </div>
                    <div class="ht-modal-section-body">
                        <div class="ht-img-section">
                            <div class="ht-img-section-header tornilleria-color">
                                📷 Fotos de Tornillería
                                <button type="button" class="ht-btn-add-img" onclick="htAgregarFoto('tornilleria')">+ Agregar</button>
                            </div>
                            <div class="ht-img-list" id="ht-imgs-tornilleria"></div>
                        </div>
                    </div>
                </div>

                {{-- ═══════════════════════════════
                     SECCIÓN: IMAGEN FÍSICA
                ═══════════════════════════════ --}}
                <div class="ht-modal-section">
                    <div class="ht-modal-section-header" style="background:linear-gradient(135deg,#1b5e20,#2e7d32);">
                        <span>🖼️ Imagen Física</span>
                    </div>
                    <div class="ht-modal-section-body">
                        <div class="ht-img-section">
                            <div class="ht-img-section-header imagen-fisica-color">
                                📷 Fotos de Imagen Física
                                <button type="button" class="ht-btn-add-img" onclick="htAgregarFoto('imagen_fisica')">+ Agregar</button>
                            </div>
                            <div class="ht-img-list" id="ht-imgs-imagen_fisica"></div>
                        </div>
                    </div>
                </div>

                {{-- ═══════════════════════════════
                     SECCIÓN: CONDICIONES DE CORTE
                ═══════════════════════════════ --}}
                <div class="ht-modal-section">
                    <div class="ht-modal-section-header" style="background:linear-gradient(135deg,#9c0300,#c62828);">
                        <span>🔴 Condiciones de Corte (PULG.)</span>
                    </div>
                    <div class="ht-modal-section-body">
                        <div class="ht-form-grid">
                            <div class="ht-form-field">
                                <label for="ht-f-profundidad">Profundidad de Corte</label>
                                <input type="number" id="ht-f-profundidad" name="profundidad_corte"
                                       step="0.0001" min="0" placeholder="Ej. 0.0600">
                            </div>
                            <div class="ht-form-field">
                                <label for="ht-f-rpm">RPM</label>
                                <input type="number" id="ht-f-rpm" name="rpm" min="0" placeholder="Ej. 600">
                            </div>
                            <div class="ht-form-field full-width">
                                <label for="ht-f-avances">Avances</label>
                                <input type="text" id="ht-f-avances" name="avances"
                                       placeholder="Ej. 0.012 AVANCE/MIN" maxlength="100">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ═══════════════════════════════
                     SECCIÓN: STOCK
                ═══════════════════════════════ --}}
                <div class="ht-modal-section">
                    <div class="ht-modal-section-header" style="background:linear-gradient(135deg,#027a3a,#02903f);">
                        <span>📊 Control de Stock</span>
                    </div>
                    <div class="ht-modal-section-body">
                        <div class="ht-form-grid">
                            <div class="ht-form-field">
                                <label for="ht-f-minimo">Mínimo en Stock</label>
                                <input type="number" id="ht-f-minimo" name="minimo" min="0" placeholder="Ej. 5">
                            </div>
                            <div class="ht-form-field">
                                <label for="ht-f-maximo">Máximo en Stock</label>
                                <input type="number" id="ht-f-maximo" name="maximo" min="0" placeholder="Ej. 30">
                            </div>
                        </div>
                    </div>
                </div>

            </form>
        </div>
        <div class="ht-modal-footer">
            <button class="ht-btn-cancel" id="ht-btn-cancel" type="button">Cancelar</button>
            <button class="ht-btn-save"   id="ht-btn-save"   type="button">💾 Guardar</button>
        </div>
    </div>
</div>

{{-- Confirmación --}}
<div class="ht-confirm-overlay" id="ht-confirm-overlay">
    <div class="ht-confirm-box">
        <h4 id="ht-confirm-titulo">¿Confirmar acción?</h4>
        <p id="ht-confirm-desc"></p>
        <div class="ht-confirm-actions">
            <button class="ht-btn-accion ht-btn-edit" id="ht-confirm-no">Cancelar</button>
            <button class="ht-btn-accion ht-btn-delete" id="ht-confirm-si">Confirmar</button>
        </div>
    </div>
</div>
@endif

{{-- Lightbox --}}
<div class="ht-lightbox-overlay" id="ht-lightbox-overlay">
    <button class="ht-lightbox-close" id="ht-lightbox-close" type="button">✕</button>
    <img id="ht-lightbox-img" class="ht-lightbox-img" src="" alt="Imagen ampliada">
    <p id="ht-lightbox-caption" style="color:#fff;margin-top:0.8em;font-size:0.9em;text-align:center;"></p>
</div>

<script>
    window.htRoutes = {
        store    : "{{ route('herramientas.tecamac.store') }}",
        update   : "{{ url('herramientas/tecamac') }}/{id}",
        destroy  : "{{ url('herramientas/tecamac') }}/{id}",
        reactivar: "{{ url('herramientas/tecamac') }}/{id}/reactivar",
    };
    window.htModo   = "{{ $modo }}";
    window.htEsCrud = {{ $esCrud ? 'true' : 'false' }};
    window.htEsAlta = {{ $esAlta ? 'true' : 'false' }};
    // Paleta de colores por proceso (para preview en modal)
    window.htProcesoPaleta = @json($procesoPaleta);
</script>

@endsection
