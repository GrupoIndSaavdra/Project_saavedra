@php
/**
 * @var \Illuminate\Database\Eloquent\Collection|\App\Models\Clase[] $clases
 * @var \App\Models\Clase $clase
 * @var \App\Models\ParcialidadOt $p
 * @var \App\Models\TratamientoTermico $tratamiento
 */
@endphp
@extends('layouts.appMenu')

@section('head')
<title>Orden de Trabajo – Almacén</title>
<link rel="icon" type="image/x-icon" href="{{ asset('images/logos/favicon.ico') }}?v=3">
@vite(['resources/css/wo_views/show_wo_almacen.css', 'resources/js/wo_views/show_wo_almacen.js'])
@endsection

@section('background-body', 'background-image:url("' . asset("images/fondos/fondoLogin.jpg") . '")')

@section('content')

<div class="almacen-layout" id="almacen-layout-main" data-user-perfil="{{ auth()->user()->perfil }}">

    {{-- ═══════════════════════════════════════════════════
         PANEL IZQUIERDO — Información de la OT
    ═══════════════════════════════════════════════════ --}}
    <div class="panel-ot">
        <div class="navigation-header mb-1 d-flex d-justify-start">
            <a href="{{ route('manageWO', request('almacen_only') == 1 ? ['almacen_only' => 1] : []) }}" class="btn-regresar">
                ← Regresar a OTs
            </a>
        </div>

        <h3>Información de la Orden de Trabajo</h3>

        @include('layouts.partials.messages')

        {{-- Campos de OT y Moldura (solo lectura) --}}
        <div class="field-group">
            <div class="field">
                <label>Orden de Trabajo</label>
                <input type="text" value="{{ $workOrder->id }}" disabled>
            </div>
            <div class="field">
                <label>Moldura</label>
                <input type="text" value="{{ $molding->nombre ?? '—' }}" disabled>
            </div>
        </div>

        {{-- Tabla de clases --}}
        @if($classes && $classes->count() > 0)
        <div class="tabla-clases-wrap">
            <table class="tabla-clases">
                <thead>
                    <tr>
                        <th>Clase</th>
                        <th>Tamaño</th>
                        <th>Consignación</th>
                        <th>Pedido</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($classes as $clase)
                    <tr class="fila-clase"
                        data-id-clase="{{ $clase->id }}"
                        data-id-ot="{{ $workOrder->id }}"
                        data-nombre="{{ $clase->nombre }}"
                        data-tamanio="{{ $clase->tamanio }}"
                        data-pedido="{{ $clase->pedido }}"
                        data-piezas="{{ $clase->piezas }}"
                        data-composicion="{{ $clase->composicion_quimica }}"
                        data-soldadura="{{ $clase->tipo_soldadura }}">
                        <td class="td-clase-nombre">
                            <span class="clase-nombre-pill">{{ $clase->nombre }}</span>
                        </td>
                        <td>{{ $clase->tamanio }}</td>
                        <td><span class="badge-tabla-num badge-num-consignacion">{{ $clase->piezas }}</span></td>
                        <td><span class="badge-tabla-num badge-num-pedido">{{ $clase->pedido }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="empty-state">No hay clases registradas en esta OT.</div>
        @endif

        {{-- Botón PDF --}}
        <a href="{{ route('generatePDFWO', $workOrder->id) }}" class="btn-pdf" target="_blank">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
            <span>Generar PDF</span>
        </a>

        {{-- Detalle editable de la clase seleccionada (se muestra al hacer clic en la tabla) --}}
        <div class="clase-detail" id="clase-detail">
            <h4 class="mb-0-8">Editar clase seleccionada</h4>

            <div class="field full">
                <label>Clase / Tamaño</label>
                <input type="text" id="clase-nombre" disabled>
            </div>

            <form action="{{ route('saveClass') }}" method="POST" id="form-editar-clase">
                @csrf
                <input type="hidden" name="workOrder" value="{{ $workOrder->id }}">
                <input type="hidden" name="molding"   value="{{ $molding->id ?? '' }}">
                <input type="hidden" name="idClass"   id="hidden-idClase">
                <input type="hidden" name="class"     id="hidden-clase-nombre">
                <input type="hidden" name="size"      id="hidden-clase-tamanio">
                <input type="hidden" name="from_almacen" value="1">
                {{-- Fechas vacías: almacén no las edita --}}
                <input type="hidden" name="start_date" value="">
                <input type="hidden" name="start_time" value="">

                <div class="field-group">
                    <div class="field">
                        <label>Composición Química</label>
                        <input type="text" id="input-composicion" disabled>
                    </div>
                    <div class="field">
                        <label>Tipo de Soldadura</label>
                        <input type="text" id="input-soldadura" disabled>
                    </div>
                </div>

                <div class="field-group">
                    <div class="field">
                        <label>Pedido Total</label>
                        <input type="number" name="order" id="input-pedido" min="1" required disabled>
                    </div>
                    <div class="field">
                        <label>Piezas con Consignación</label>
                        <input type="number" name="pieces" id="input-piezas" min="0" required disabled>
                    </div>
                </div>

                <div class="edicion-clase-actions">
                    <button type="button" id="btn-habilitar-edicion" class="btn-habilitar-edicion-modern" title="Habilitar edición de pedido y piezas">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                        <span>Habilitar Edición de Clase</span>
                    </button>

                    <button type="submit" class="btn-guardar-clase-modern" id="btn-guardar-clase" hidden>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        <span>Guardar Cambios</span>
                    </button>
                </div>
            </form>
        </div>
    </div>


    {{-- ═══════════════════════════════════════════════════
         PANEL DERECHO — Remisiones + Parcialidades
    ═══════════════════════════════════════════════════ --}}
    <div class="panel-actividad">

        {{-- ── CARD REMISIONES (Ocultado para subidas nuevas, visible solo si hay historial antiguo) ── --}}
        @if(isset($remisiones) && $remisiones->count() > 0)
        <div class="card-actividad" id="remisiones-panel">
            <h3>
                Historial de Remisiones (Antiguas)
            </h3>

            {{-- Placeholder cuando no hay clase seleccionada --}}
            <div class="placeholder-msg" id="placeholder-remision">
                Selecciona una clase en la tabla para ver las remisiones anteriores.
            </div>

            {{-- Formulario de subida (único, rellenado por JS) --}}
            <form action="{{ route('wo.remision.store') }}" method="POST" enctype="multipart/form-data"
                  class="form-remision" id="form-remision" hidden>
                @csrf
                <input type="hidden" name="id_ot"    id="hidden-idOtRemision"    value="">
                <input type="hidden" name="id_clase" id="hidden-idClaseRemision"  value="">

                <div class="field">
                    <label>Remisión (PDF / Imagen)</label>
                    <input type="file" name="archivo" accept=".pdf,.jpg,.jpeg,.png" required
                           class="form-control btn-small">
                </div>
                <div class="field">
                    <label>Descripción (opcional)</label>
                    <input type="text" name="descripcion" placeholder="Ej: Remisión #1042"
                           maxlength="255" class="form-control">
                </div>
                <button type="submit" class="btn-subir">Subir</button>
            </form>

            {{-- Contenedor donde JS muestra las remisiones de la clase seleccionada --}}
            <div id="lista-remisiones-container">
                @foreach($classes ?? [] as $clase)
                @php /** @var \App\Models\Clase $clase */ @endphp
                <div class="grupo-remision" data-id-clase="{{ $clase->id }}" hidden>
                    @if(isset($remisiones[$clase->id]) && $remisiones[$clase->id]->count() > 0)
                    <div class="lista-remisiones">
                        @foreach($remisiones[$clase->id] as $rem)
                        @php /** @var \App\Models\RemisionOt $rem */ @endphp
                        <div class="item-remision" data-id="{{ $rem->id }}">
                            <span class="file-icon badge-blue">
                                {{ Str::endsWith(strtolower($rem->filename), '.pdf') ? 'PDF' : 'IMG' }}
                            </span>
                            <div class="file-info">
                                <div class="file-name">{{ $rem->filename }}</div>
                                <div class="file-meta">
                                    {{ $rem->descripcion ?? 'Sin descripción' }} &nbsp;·&nbsp;
                                    {{ $rem->usuario ? ($rem->usuario->nombre . ' ' . $rem->usuario->a_paterno) : ($rem->uploaded_by ?? '—') }} &nbsp;·&nbsp;
                                    {{ $rem->created_at->format('d/m/Y H:i') }}
                                </div>
                            </div>
                            <a href="{{ route('wo.remision.serve', $rem->id) }}"
                               class="btn-download" target="_blank">Descargar</a>
                            <form action="{{ route('wo.remision.destroy', $rem->id) }}" method="POST"
                                  class="form-eliminar-remision d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-eliminar-remision" title="Eliminar">Eliminar</button>
                            </form>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="empty-state">No hay remisiones para esta clase. Sube la primera arriba.</div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- ── CARD PARCIALIDADES (REMISIONES) ── --}}
        <div class="card-actividad card-seccion-remisiones" id="parcialidades-panel">
            <div class="card-header-styled">
                <h3>
                    <span class="card-header-icon-wrap icon-blue">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                    </span>
                    <span>Remisiones & Parcialidades Recibidas</span>
                </h3>
                <span class="badge-tipo badge-tipo-blue">Entradas Almacén</span>
            </div>

            {{-- Placeholder --}}
            <div class="placeholder-msg" id="placeholder-parcialidad">
                Selecciona una clase en la tabla para ver el historial de entregas parciales.
            </div>

            {{-- Aviso de bloqueo: se muestra hasta que exista al menos una remisión --}}
            <div class="placeholder-msg" id="aviso-sin-remision" class="alert-warning-custom" hidden>
                Debes subir al menos una remisión antes de poder registrar parcialidades.
            </div>

            {{-- Resumen (se actualiza por JS) --}}
            <div class="resumen-parcialidades resumen-parcialidades-blue" id="resumen-parcialidades">
                <div class="resumen-item item-blue">
                    <div class="resumen-valor val-recibido">0</div>
                    <div class="resumen-label">Pzas recibidas</div>
                </div>
                <div class="resumen-item item-blue">
                    <div class="resumen-valor val-pedido">0</div>
                    <div class="resumen-label">Pedido total</div>
                </div>
                <div class="resumen-item item-blue">
                    <div class="resumen-valor val-consignacion">0</div>
                    <div class="resumen-label">Consignación</div>
                </div>
                <div class="resumen-item item-blue">
                    <div class="resumen-valor val-pct">0%</div>
                    <div class="resumen-label">Avance</div>
                </div>
                <div class="flex-1 self-center px-0-5">
                    <div class="progress-track track-blue">
                        <div class="progress-bar-fill progress-fill fill-blue w-0">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Formulario nueva parcialidad (único, rellenado por JS) --}}
            <form action="{{ route('wo.parcialidad.store') }}" method="POST" enctype="multipart/form-data"
                  class="form-parcialidad" id="form-parcialidad" hidden>
                @csrf
                <input type="hidden" name="id_ot"    id="hidden-idOtParcialidad"    value="">
                <input type="hidden" name="id_clase" id="hidden-idClaseParcialidad"  value="">

                <div class="form-row-gis">
                    <div class="field field-qty">
                        <label>Cantidad</label>
                        <input type="number" name="cantidad" id="parcialidad-cantidad" min="0" placeholder="0" required class="form-control text-center">
                    </div>
                    <div class="field field-file">
                        <label>Archivo (PDF / Imagen)</label>
                        <input type="file" name="archivo" id="parcialidad-archivo" accept=".pdf,.jpg,.jpeg,.png" required class="form-control">
                    </div>
                    <div class="field field-date">
                        <label>Fecha recepción</label>
                        <input type="date" name="fecha_recepcion" id="parcialidad-fecha" required class="form-control"
                               value="{{ date('Y-m-d') }}">
                    </div>
                </div>

                <div class="form-row-gis mt-0-5">
                    <div class="field field-desc">
                        <label id="label-parcialidad-descripcion">Descripción</label>
                        <input type="text" name="descripcion" id="parcialidad-descripcion" placeholder="Ej: 1ra entrega, remisión #1042..."
                               maxlength="255" class="form-control">
                    </div>
                    <div class="field field-btn">
                        <button type="submit" class="btns btn-registrar-gis btn-registrar-blue" id="btn-registrar-parcialidad" disabled>
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                            <span>Registrar</span>
                        </button>
                    </div>
                </div>

                {{-- Alerta cuando la cantidad es 0 --}}
                <div id="alerta-cero-parcialidad" class="alerta-cero-box" style="display: none;">
                    <span class="alerta-cero-icon">⚠️</span>
                    <div class="alerta-cero-text">
                        <strong>Atención:</strong> Estás registrando una remisión con <strong>0 piezas</strong>. Es obligatorio indicar el motivo o justificación en la descripción.
                    </div>
                </div>
            </form>

            {{-- Historial agrupado por clase --}}
            <div id="historial-parcialidades-container">
                @foreach($classes ?? [] as $clase)
                @php /** @var \App\Models\Clase $clase */ @endphp
                {{-- data-tiene-remision: 1 si ya hay remisiones, 0 si no --}}
                <div class="grupo-parcialidad" data-id-clase="{{ $clase->id }}"
                     data-pedido="{{ $clase->pedido }}"
                     data-tiene-remision="{{ (isset($remisiones[$clase->id]) && $remisiones[$clase->id]->count() > 0) ? '1' : '0' }}" hidden>
                    @if(isset($parcialidades[$clase->id]) && $parcialidades[$clase->id]->count() > 0)
                    <div class="tabla-parcialidades-wrap tabla-wrap-blue">
                        <div class="log-header-bar">
                            <span class="log-label">Historial de Parcialidades Recibidas</span>
                            <span class="log-count-badge">{{ $parcialidades[$clase->id]->count() }} registro(s)</span>
                        </div>
                        <table class="tabla-parcialidades tabla-parcialidades-blue">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Fecha</th>
                                    <th>Cantidad</th>
                                    <th>Descripción</th>
                                    <th>Remisiones</th>
                                    <th>Registrado por</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($parcialidades[$clase->id] as $i => $p)
                                @php /** @var \App\Models\ParcialidadOt $p */ @endphp
                                <tr class="fila-parcialidad-item"
                                    data-id="{{ $p->id }}"
                                    data-cantidad="{{ $p->cantidad }}"
                                    data-descripcion="{{ $p->descripcion }}"
                                    data-fecha="{{ $p->fecha_recepcion->format('Y-m-d') }}"
                                    data-id-remision="{{ $p->id_remision }}"
                                    data-update-url="{{ route('wo.parcialidad.update', $p->id) }}">
                                    <td class="text-center font-bold text-muted-sm">{{ $i + 1 }}</td>
                                    <td>
                                        <span class="view-fecha">{{ $p->fecha_recepcion->format('d/m/Y') }}</span>
                                        <input type="date" class="edit-fecha form-control" value="{{ $p->fecha_recepcion->format('Y-m-d') }}">
                                    </td>
                                    <td class="text-center">
                                        <span class="view-cantidad badge-cantidad {{ $p->cantidad == 0 ? 'badge-cantidad-cero' : '' }}">{{ $p->cantidad }}</span>
                                        <input type="number" class="edit-cantidad form-control" min="0" value="{{ $p->cantidad }}">
                                    </td>
                                    <td>
                                        <span class="view-descripcion">{{ $p->descripcion ?? '—' }}</span>
                                        <input type="text" class="edit-descripcion form-control" value="{{ $p->descripcion }}">
                                    </td>
                                    <td>
                                        <div class="view-remision">
                                            @if($p->remision)
                                                @php
                                                    $isPdf = Str::endsWith(strtolower($p->remision->filename), '.pdf');
                                                    $iconName = $isPdf ? 'images/iconos/pdf.png' : 'images/iconos/IMG-Icon.png'; 
                                                @endphp
                                                <a href="{{ route('wo.remision.serve', $p->remision->id) }}" target="_blank" class="link-action-modal doc-pill-link" title="{{ $p->remision->filename }}">
                                                    <img src="{{ icon($iconName) }}" alt="{{ $isPdf ? 'PDF' : 'IMG' }}" class="icon-16" onerror="this.src='{{ icon('images/iconos/pdf.png') }}'">
                                                    <span class="text-truncate" style="max-width: 170px;">{{ $p->remision->filename }}</span>
                                                </a>
                                            @else
                                                <span class="text-muted-sm" title="Sin remisión vinculada">—</span>
                                            @endif
                                        </div>
                                        <input type="file" class="edit-archivo form-control" accept=".pdf,.jpg,.jpeg,.png">
                                    </td>
                                    <td>{{ $p->usuario ? ($p->usuario->nombre . ' ' . $p->usuario->a_paterno) : ($p->registrado_por ?? '—') }}</td>
                                    <td class="ws-nowrap col-acciones">
                                        <!-- Botones estándar -->
                                        <button type="button" class="btn-editar-parcialidad btn-action-edit btn-action-edit-blue" title="Editar entrega">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                            <span>Editar</span>
                                        </button>

                                        <form action="{{ route('wo.parcialidad.destroy', $p->id) }}"
                                              method="POST" class="form-eliminar-parcialidad d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="password" class="input-confirm-password" value="">
                                            <button type="submit" class="btn-eliminar-remision btn-action-delete"
                                                    title="Eliminar entrega">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                                <span>Eliminar</span>
                                            </button>
                                        </form>

                                        <!-- Botones de guardar / cancelar (ocultos inicialmente) -->
                                        <button type="button" class="btn-guardar-parcialidad btn-action-save" title="Guardar cambios">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                            <span>Guardar</span>
                                        </button>
                                        <button type="button" class="btn-cancelar-parcialidad btn-action-cancel" title="Cancelar edición">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                            <span>Cancelar</span>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="empty-state">No hay parcialidades registradas para esta clase.</div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        {{-- ── CARD TRATAMIENTO TÉRMICO ── --}}
        <div class="card-actividad card-seccion-tratamiento" id="tratamiento-termico-panel">
            <div class="card-header-styled">
                <h3>
                    <span class="card-header-icon-wrap icon-amber">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"></path>
                        </svg>
                    </span>
                    <span>Tratamiento Térmico</span>
                </h3>
                <span class="badge-tipo badge-tipo-amber">Proceso Térmico</span>
            </div>

            <div class="placeholder-msg placeholder-msg-amber" id="placeholder-tratamiento">
                Selecciona una clase en la tabla para ver el historial de tratamiento térmico.
            </div>

            {{-- Resumen Tratamientos --}}
            <div class="resumen-tratamientos resumen-tratamientos-amber" id="resumen-tratamientos" hidden>
                <div class="resumen-item item-amber">
                    <div class="resumen-valor val-tratadas">0</div>
                    <div class="resumen-label">Pzas en TT</div>
                </div>
                <div class="resumen-item item-amber">
                    <div class="resumen-valor val-pedido-tratamiento">0</div>
                    <div class="resumen-label">Pzas Recibidas</div>
                </div>
                <div class="resumen-item item-amber">
                    <div class="resumen-valor val-consignacion-tratamiento">0</div>
                    <div class="resumen-label">Consignación</div>
                </div>
                <div class="resumen-item item-amber">
                    <div class="resumen-valor val-pct-tratamiento">0%</div>
                    <div class="resumen-label">Avance</div>
                </div>
                <div class="flex-1 self-center px-0-5">
                    <div class="progress-track track-amber">
                        <div class="progress-bar-fill-tratamiento progress-fill fill-amber w-0">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Formulario nueva --}}
            <form action="{{ route('wo.tratamiento.store') }}" method="POST" enctype="multipart/form-data" class="form-tratamiento" id="form-tratamiento" hidden>
                @csrf
                <input type="hidden" name="id_ot" id="hidden-idOtTratamiento" value="">
                <input type="hidden" name="id_clase" id="hidden-idClaseTratamiento" value="">

                <div class="form-row-gis">
                    <div class="field field-qty">
                        <label>Cantidad</label>
                        <input type="number" name="cantidad" id="tratamiento-cantidad" min="0" placeholder="0" required class="form-control text-center input-theme-amber">
                    </div>
                    <div class="field field-file flex-1">
                        <label>Archivo (PDF)</label>
                        <input type="file" name="archivo" id="tratamiento-archivo" accept=".pdf" required class="form-control">
                    </div>
                </div>

                <div class="form-row-gis mt-0-5">
                    <div class="field field-desc flex-1">
                        <label id="label-tratamiento-descripcion">Descripción</label>
                        <input type="text" name="descripcion" id="tratamiento-descripcion" placeholder="Ej: Reporte de tratamiento térmico..." maxlength="255" class="form-control input-theme-amber">
                    </div>
                    <div class="field field-btn">
                        <button type="submit" class="btns btn-registrar-gis btn-registrar-amber" id="btn-registrar-tratamiento" disabled>
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"></path></svg>
                            <span>Registrar TT</span>
                        </button>
                    </div>
                </div>

                {{-- Alerta cuando la cantidad es 0 --}}
                <div id="alerta-cero-tratamiento" class="alerta-cero-box alerta-cero-amber" style="display: none;">
                    <span class="alerta-cero-icon">⚠️</span>
                    <div class="alerta-cero-text">
                        <strong>Atención:</strong> Estás registrando un proceso de tratamiento térmico con <strong>0 piezas</strong>. Es obligatorio indicar el motivo o justificación en la descripción.
                    </div>
                </div>
            </form>

            {{-- Historial --}}
            <div id="historial-tratamiento-container">
                @foreach($classes ?? [] as $clase)
                @php /** @var \App\Models\Clase $clase */ @endphp
                <div class="grupo-tratamiento" data-id-clase="{{ $clase->id }}" hidden>
                    @if(isset($tratamientos[$clase->id]) && $tratamientos[$clase->id]->count() > 0)
                    <div class="lista-tratamientos tabla-wrap-amber">
                        <div class="log-header-bar log-header-bar-amber">
                            <span class="log-label log-label-amber">Historial de Tratamiento Térmico</span>
                            <span class="log-count-badge log-count-amber">{{ $tratamientos[$clase->id]->count() }} registro(s)</span>
                        </div>
                        <table class="tabla-parcialidades tabla-tratamientos-amber">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Cantidad</th>
                                    <th>Descripción</th>
                                    <th>Documento</th>
                                    <th>Registrado por</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tratamientos[$clase->id] as $tratamiento)
                                @php /** @var \App\Models\TratamientoTermico $tratamiento */ @endphp
                                <tr class="fila-tratamiento-item"
                                    data-id="{{ $tratamiento->id }}"
                                    data-cantidad="{{ $tratamiento->cantidad }}"
                                    data-descripcion="{{ $tratamiento->descripcion }}"
                                    data-update-url="{{ route('wo.tratamiento.update', $tratamiento->id) }}">
                                    <td>{{ $tratamiento->created_at->format('d/m/Y') }}</td>
                                    <td class="text-center">
                                        <span class="view-cantidad badge-cantidad badge-cantidad-tt {{ $tratamiento->cantidad == 0 ? 'badge-cantidad-cero' : '' }}">{{ $tratamiento->cantidad }}</span>
                                        <input type="number" class="edit-cantidad form-control input-theme-amber" min="0" value="{{ $tratamiento->cantidad }}">
                                    </td>
                                    <td>
                                        <span class="view-descripcion">{{ $tratamiento->descripcion }}</span>
                                        <input type="text" class="edit-descripcion form-control input-theme-amber" name="descripcion" value="{{ $tratamiento->descripcion }}">
                                    </td>
                                    <td>
                                        <div class="view-remision">
                                            <a href="{{ route('wo.tratamiento.download', $tratamiento->id) }}" target="_blank" class="link-action-modal doc-pill-link doc-pill-amber">
                                                <img src="{{ asset('images/iconos/pdf.png') }}" alt="PDF" class="icon-16">
                                                <span>Ver PDF</span>
                                            </a>
                                        </div>
                                        <input type="file" class="edit-archivo form-control" accept=".pdf">
                                    </td>
                                    <td>{{ $tratamiento->registrado_por }}</td>
                                    <td class="ws-nowrap col-acciones">
                                        <!-- Botones estándar -->
                                        <button type="button" class="btn-editar-tratamiento btn-action-edit btn-action-edit-amber" title="Editar tratamiento">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                            <span>Editar</span>
                                        </button>

                                        <form action="{{ route('wo.tratamiento.destroy', $tratamiento->id) }}" method="POST" class="form-eliminar-tratamiento d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="password" class="input-confirm-password" value="">
                                            <button type="submit" class="btn-eliminar-remision btn-action-delete" title="Eliminar tratamiento">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                                <span>Eliminar</span>
                                            </button>
                                        </form>

                                        <!-- Botones de guardar / cancelar (ocultos inicialmente) -->
                                        <button type="button" class="btn-guardar-tratamiento btn-guardar-parcialidad btn-action-save" title="Guardar cambios">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                            <span>Guardar</span>
                                        </button>
                                        <button type="button" class="btn-cancelar-tratamiento btn-cancelar-parcialidad btn-action-cancel" title="Cancelar edición">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                            <span>Cancelar</span>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="empty-state">No hay tratamientos registrados para esta clase.</div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

    </div>{{-- /panel-actividad --}}
</div>{{-- /almacen-layout --}}

{{-- Formulario oculto global para actualizar parcialidades --}}
<form id="form-update-parcialidad" method="POST" hidden>
    @csrf
    @method('PUT')
    <input type="hidden" name="cantidad" id="update-cantidad">
    <input type="hidden" name="descripcion" id="update-descripcion">
    <input type="hidden" name="fecha_recepcion" id="update-fecha">
    <input type="hidden" name="id_remision" id="update-id-remision">
</form>

{{-- Formulario oculto global para actualizar tratamientos --}}
<form id="form-update-tratamiento" method="POST" hidden enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <input type="hidden" name="cantidad" id="update-tratamiento-cantidad">
    <input type="hidden" name="descripcion" id="update-tratamiento-descripcion">
    <input type="file" name="archivo" id="update-tratamiento-archivo">
</form>

{{-- Modal de Confirmación con Contraseña Encriptada --}}
<div id="modal-confirm-delete" class="modal-overlay" hidden>
    <div class="modal-content-box-v2">
        <h4 class="modal-title">Autorizar Eliminación</h4>
        <p class="modal-text">Ingresa la contraseña de un Administrador o Master para eliminar esta parcialidad:</p>
        <input type="password" id="modal-delete-password" class="form-control modal-input-v2" placeholder="Contraseña">
        <div class="modal-actions">
            <button type="button" id="btn-modal-delete-confirm" class="modal-btn-confirm">Confirmar</button>
            <button type="button" id="btn-modal-delete-cancel" class="modal-btn-cancel">Cancelar</button>
        </div>
    </div>
</div>

<script>
    window.classesDataUrl = "{{ url('/piecesInProgress/classesData') }}";
</script>
@endsection
