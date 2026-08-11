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
<link rel="icon" href="{{ url('images/lg_saavedra.png') }}?v=1">
@vite(['resources/css/wo_views/show_wo_almacen.css', 'resources/js/wo_views/show_wo_almacen.js'])
@endsection

@section('background-body', 'background-image:url("' . asset("images/fondoLogin.jpg") . '")')

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
                        <td>{{ $clase->nombre }}</td>
                        <td>{{ $clase->tamanio }}</td>
                        <td>{{ $clase->piezas }}</td>
                        <td>{{ $clase->pedido }}</td>
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
            Generar PDF
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

                <button type="button" id="btn-habilitar-edicion" class="btn-editar-clase-icon" title="Habilitar edición" class="btn-reset mx-auto d-block mt-1 text-center">
                    <img src="{{ asset('images/editar-informacion.png') }}" alt="Editar" class="icon-48">
                </button>

                <button type="submit" class="btn-guardar" id="btn-guardar-clase" hidden>Guardar cambios</button>
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

        {{-- ── CARD PARCIALIDADES ── --}}
        <div class="card-actividad" id="parcialidades-panel">
            <h3>
                Parcialidades Recibidas
            </h3>

            {{-- Placeholder --}}
            <div class="placeholder-msg" id="placeholder-parcialidad">
                Selecciona una clase para ver el historial de entregas parciales.
            </div>

            {{-- Aviso de bloqueo: se muestra hasta que exista al menos una remisión --}}
            <div class="placeholder-msg" id="aviso-sin-remision" class="alert-warning-custom" hidden>
                Debes subir al menos una remisión antes de poder registrar parcialidades.
            </div>

            {{-- Resumen (se actualiza por JS) --}}
            <div class="resumen-parcialidades" id="resumen-parcialidades" hidden>
                <div class="resumen-item">
                    <div class="resumen-valor val-recibido">0</div>
                    <div class="resumen-label">Pzas recibidas</div>
                </div>
                <div class="resumen-item">
                    <div class="resumen-valor val-pedido">0</div>
                    <div class="resumen-label">Pedido total</div>
                </div>
                <div class="resumen-item">
                    <div class="resumen-valor val-consignacion">0</div>
                    <div class="resumen-label">Consignación</div>
                </div>
                <div class="resumen-item">
                    <div class="resumen-valor val-pct">0%</div>
                    <div class="resumen-label">Avance</div>
                </div>
                <div class="flex-1 self-center px-0-5">
                    <div class="progress-track">
                        <div class="progress-bar-fill progress-fill w-0">
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

                <div class="field">
                    <label>Cantidad</label>
                    <input type="number" name="cantidad" id="parcialidad-cantidad" min="1" placeholder="Pzas" required class="form-control p-0-55-0-4 text-center">
                </div>
                <div class="field">
                    <label>Archivo (PDF / Imagen)</label>
                    <input type="file" name="archivo" id="parcialidad-archivo" accept=".pdf,.jpg,.jpeg,.png" required class="form-control btn-small">
                </div>
                <div class="field">
                    <label>Descripción</label>
                    <input type="text" name="descripcion" placeholder="Ej: 1ra entrega..."
                           maxlength="255" class="form-control">
                </div>
                <div class="field">
                    <label>Fecha recepción</label>
                    <input type="date" name="fecha_recepcion" id="parcialidad-fecha" required class="form-control"
                           value="{{ date('Y-m-d') }}">
                </div>
                <button type="submit" class="btn-subir" id="btn-registrar-parcialidad" disabled>Registrar</button>
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
                    <div class="tabla-parcialidades-wrap">
                        <p class="log-label">Log de Parcialidades</p>
                        <table class="tabla-parcialidades">
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
                                    <td>{{ $i + 1 }}</td>
                                    <td>
                                        <span class="view-fecha">{{ $p->fecha_recepcion->format('d/m/Y') }}</span>
                                        <input type="date" class="edit-fecha form-control" value="{{ $p->fecha_recepcion->format('Y-m-d') }}">
                                    </td>
                                    <td>
                                        <span class="view-cantidad badge-cantidad">{{ $p->cantidad }}</span>
                                        <input type="number" class="edit-cantidad form-control" min="1" value="{{ $p->cantidad }}">
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
                                                    $iconName = $isPdf ? 'pdf.png' : 'image-icon.png'; 
                                                @endphp
                                                <a href="{{ route('wo.remision.serve', $p->remision->id) }}" target="_blank" class="link-action-modal" title="{{ $p->remision->filename }}">
                                                    <img src="{{ asset('images/' . $iconName) }}" alt="{{ $isPdf ? 'PDF' : 'IMG' }}" class="icon-16" onerror="this.src='{{ asset('images/pdf.png') }}'">
                                                    <span class="text-truncate" style="max-width: 200px; display: inline-block; vertical-align: middle;">{{ $p->remision->filename }}</span>
                                                </a>
                                            @else
                                                <span class="text-muted-sm" title="Sin remisión vinculada">—</span>
                                            @endif
                                        </div>
                                        <input type="file" class="edit-archivo form-control" accept=".pdf,.jpg,.jpeg,.png">
                                    </td>
                                    <td>{{ $p->usuario ? ($p->usuario->nombre . ' ' . $p->usuario->a_paterno) : ($p->registrado_por ?? '—') }}</td>
                                    <td class="ws-nowrap">
                                        <!-- Botones estándar -->
                                        <button type="button" class="btn-editar-parcialidad btn-download">Editar</button>

                                        <form action="{{ route('wo.parcialidad.destroy', $p->id) }}"
                                              method="POST" class="form-eliminar-parcialidad d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="password" class="input-confirm-password" value="">
                                            <button type="submit" class="btn-eliminar-remision"
                                                    title="Eliminar">Eliminar</button>
                                        </form>

                                        <!-- Botones de guardar / cancelar (ocultos inicialmente) -->
                                        <button type="button" class="btn-guardar-parcialidad">Guardar</button>
                                        <button type="button" class="btn-cancelar-parcialidad">Cancelar</button>
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
        <div class="card-actividad" id="tratamiento-termico-panel">
            <h3>
                Tratamiento térmico
            </h3>
            <div class="placeholder-msg" id="placeholder-tratamiento">
                Selecciona una clase para ver el tratamiento térmico.
            </div>

            {{-- Resumen Tratamientos --}}
            <div class="resumen-tratamientos" id="resumen-tratamientos" hidden>
                <div class="resumen-item">
                    <div class="resumen-valor val-tratadas">0</div>
                    <div class="resumen-label">PZAS EN TT</div>
                </div>
                <div class="resumen-item">
                    <div class="resumen-valor val-pedido-tratamiento">0</div>
                    <div class="resumen-label">PZAS RECIBIDAS</div>
                </div>
                <div class="resumen-item">
                    <div class="resumen-valor val-consignacion-tratamiento">0</div>
                    <div class="resumen-label">Consignación</div>
                </div>
                <div class="resumen-item">
                    <div class="resumen-valor val-pct-tratamiento">0%</div>
                    <div class="resumen-label">Avance</div>
                </div>
                <div class="flex-1 self-center px-0-5">
                    <div class="progress-track">
                        <div class="progress-bar-fill-tratamiento progress-fill w-0">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Formulario nueva --}}
            <form action="{{ route('wo.tratamiento.store') }}" method="POST" enctype="multipart/form-data" class="form-tratamiento" id="form-tratamiento" hidden>
                @csrf
                <input type="hidden" name="id_ot" id="hidden-idOtTratamiento" value="">
                <input type="hidden" name="id_clase" id="hidden-idClaseTratamiento" value="">

                <div class="field">
                    <label>Cantidad</label>
                    <input type="number" name="cantidad" id="tratamiento-cantidad" min="1" placeholder="Pzas" required class="form-control p-0-55-0-4 text-center">
                </div>
                <div class="field">
                    <label>Archivo (PDF)</label>
                    <input type="file" name="archivo" id="tratamiento-archivo" accept=".pdf" required class="form-control btn-small">
                </div>
                <div class="field">
                    <label>Descripción</label>
                    <input type="text" name="descripcion" placeholder="Ej: Reporte de tratamiento..." maxlength="255" class="form-control">
                </div>
                <button type="submit" class="btn-subir" id="btn-registrar-tratamiento">Registrar</button>
            </form>

            {{-- Historial --}}
            <div id="historial-tratamiento-container">
                @foreach($classes ?? [] as $clase)
                @php /** @var \App\Models\Clase $clase */ @endphp
                <div class="grupo-tratamiento" data-id-clase="{{ $clase->id }}" hidden>
                    @if(isset($tratamientos[$clase->id]) && $tratamientos[$clase->id]->count() > 0)
                    <div class="lista-tratamientos">
                        <table class="tabla-parcialidades">
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
                                    <td>
                                        <span class="view-cantidad badge-cantidad">{{ $tratamiento->cantidad }}</span>
                                        <input type="number" class="edit-cantidad form-control" min="1" value="{{ $tratamiento->cantidad }}">
                                    </td>
                                    <td>
                                        <span class="view-descripcion">{{ $tratamiento->descripcion }}</span>
                                        <input type="text" class="edit-descripcion form-control" name="descripcion" value="{{ $tratamiento->descripcion }}">
                                    </td>
                                    <td>
                                        <div class="view-remision">
                                            <a href="{{ route('wo.tratamiento.download', $tratamiento->id) }}" target="_blank" class="link-action-modal">
                                                <img src="{{ asset('images/pdf.png') }}" alt="PDF" class="icon-16">
                                                <span>Ver PDF</span>
                                            </a>
                                        </div>
                                        <input type="file" class="edit-archivo form-control" accept=".pdf">
                                    </td>
                                    <td>{{ $tratamiento->registrado_por }}</td>
                                    <td class="ws-nowrap">
                                        <!-- Botones estándar -->
                                        <button type="button" class="btn-editar-tratamiento btn-download">Editar</button>

                                        <form action="{{ route('wo.tratamiento.destroy', $tratamiento->id) }}" method="POST" class="form-eliminar-tratamiento d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="password" class="input-confirm-password" value="">
                                            <button type="submit" class="btn-eliminar-remision" title="Eliminar">Eliminar</button>
                                        </form>

                                        <!-- Botones de guardar / cancelar (ocultos inicialmente) -->
                                        <button type="button" class="btn-guardar-tratamiento btn-guardar-parcialidad">Guardar</button>
                                        <button type="button" class="btn-cancelar-tratamiento btn-cancelar-parcialidad">Cancelar</button>
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
