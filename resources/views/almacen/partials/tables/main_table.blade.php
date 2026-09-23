@foreach (['activa' => 'Documentos Activos (Dibujos y Ayudas)', 'inactiva' => 'Documentos Inactivos (Histórico)'] as $estado => $titulo)
    @php
        $registrosEstado = $registros->where('status', $estado);
    @endphp

    <div class="alm-table-card alm-margin-bottom-2em">
        <div class="alm-table-header"
            style="{{ $estado === 'inactiva' ? 'background: #6c757d; border-bottom: 2px solid #5a6268;' : '' }}">
            <h2>{{ $titulo }}</h2>
            <span class="alm-results-count">{{ $registrosEstado->count() }}
                resultado{{ $registrosEstado->count() !== 1 ? 's' : '' }}</span>
        </div>

        @if ($estado === 'activa')
            {{-- ── BARRA DE SINCRONIZACIÓN MANUAL (solo tabla Activa) ── --}}
            <div id="sync-bar-activa"
                class="alm-display-flex alm-align-items-center alm-justify-content-space-between alm-flex-wrap-wrap alm-gap-10px alm-padding-10px-20px alm-background-linear-gradient-135deg-f0f9ff-0-e0f2fe-100pct alm-border-bottom-1px-solid-bae6fd alm-font-size-0-85rem alm-color-0369a1 alm-font-family-Poppins-sans-serif">
                <span id="sync-status-almacen"
                    class="alm-display-flex alm-align-items-center alm-gap-6px alm-font-weight-600">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                        fill="none" stroke="#0369a1" stroke-width="2.5" stroke-linecap="round"
                        stroke-linejoin="round" class="alm-flex-shrink-0">
                        <polyline points="23 4 23 10 17 10"></polyline>
                        <polyline points="1 20 1 14 7 14"></polyline>
                        <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                    </svg>
                    <span id="sync-last-time-almacen">Sincronización automática activa</span>
                </span>
                <button id="btn-sync-manual-almacen" onclick="sincronizarDibujos(true)"
                    title="Sincronizar archivos ahora"
                    class="alm-display-inline-flex alm-align-items-center alm-gap-7px alm-padding-7px-18px alm-background-linear-gradient-135deg-0369a1-0-0284c7-100pct alm-color-fff alm-border-none alm-border-radius-8px alm-font-weight-700 alm-font-size-0-82rem alm-font-family-Poppins-sans-serif alm-cursor-pointer alm-box-shadow-0-3px-10px-rgba-3-105-161-0-25 alm-transition-all-0-2s-ease alm-white-space-nowrap"
                    onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 5px 15px rgba(3,105,161,0.35)';"
                    onmouseout="this.style.transform=''; this.style.boxShadow='0 3px 10px rgba(3,105,161,0.25)';">
                    <svg id="sync-icon-almacen" xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                        stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="23 4 23 10 17 10"></polyline>
                        <polyline points="1 20 1 14 7 14"></polyline>
                        <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                    </svg>
                    Sincronizar ahora
                </button>
            </div>
        @endif

        @if ($registrosEstado->isEmpty())
            <div class="alm-empty">
                <div class="alm-empty-icon">
                    <img src="{{ asset('images/noPieces.png') }}" alt="Sin resultados"
                        class="alm-width-64px alm-opacity-0-5">
                </div>
                <p>
                    @if ($busquedaOt || $desde || $hasta)
                        No se encontraron registros de {{ strtolower($titulo) }} con los filtros aplicados.
                    @else
                        Aún no hay registros en la bandeja de {{ strtolower($titulo) }}.
                    @endif
                </p>
            </div>
        @else
            <div class="alm-table-scroll">
                <table class="alm-table">
                    <thead>
                        <tr>
                            <th
                                style="width:30%; {{ $estado === 'inactiva' ? 'background: #6c757d; border-color: #5a6268;' : '' }}">
                                Orden de Trabajo</th>
                            <th style="width:12%; {{ $estado === 'inactiva' ? 'background: #6c757d; border-color: #5a6268;' : '' }}"
                                class="d-text-center">Estado</th>
                            <th style="width:12%; {{ $estado === 'inactiva' ? 'background: #6c757d; border-color: #5a6268;' : '' }}"
                                class="d-text-center">Modelo</th>
                            <th style="width:18%; {{ $estado === 'inactiva' ? 'background: #6c757d; border-color: #5a6268;' : '' }}"
                                class="d-text-center">Último envío</th>
                            <th style="width:10%; {{ $estado === 'inactiva' ? 'background: #6c757d; border-color: #5a6268;' : '' }}"
                                class="d-text-center">Archivos</th>
                            <th style="width:16%; {{ $estado === 'inactiva' ? 'background: #6c757d; border-color: #5a6268;' : '' }}"
                                class="d-text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="alm-tbody-{{ $estado }}">
                        @foreach ($registrosEstado as $reg)
                            @php
                                $vm = new \App\ViewModels\AlmacenTableRowViewModel($reg, $estado, $deptName);
                                $targetReg = $vm->targetReg;
                                $hasFilesOrControl = $vm->hasFilesOrControl;
                                $hasPendingChanges = $vm->hasPendingChanges;
                                $fsmState = $vm->fsmState;
                                $bgColor = $vm->bgColor ?? '#ffffff';
                                $textColor = $vm->textColor ?? '#000000';
                                $borderColor = $vm->borderColor ?? '#000000';
                                $icon = $vm->icon ?? 'default.png';
                                $label = $vm->label ?? 'Desconocido';
                                $tooltip = $vm->tooltip ?? '';
                                $count = $vm->count ?? 0;
                                extract(get_object_vars($vm));
                            @endphp

                            {{-- Fila principal --}}
                            <tr data-ot="{{ $reg->ot }}" data-estado-real="{{ $fsmState }}"
                                data-is-fully-processed="{{ $targetReg->isAlmacenFullyProcessed() ? 'true' : 'false' }}">
                                <td>
                                    <div class="alm-ot-label">{{ preg_replace('/_\d{8}_\d{6}_.*/', '', $reg->ot) }}
                                    </div>
                                    @if ($reg->status === 'inactiva')
                                        <div class="alm-inactiva-note">
                                            La carpeta fue eliminada por el administrador. Los PDFs de
                                            {{ $deptName }} se
                                            conservan.
                                        </div>
                                    @endif
                                </td>
                                <td class="d-text-center">
                                    <span class="badge-status badge-{{ $reg->status }}">
                                        {{ $reg->status }}
                                    </span>
                                </td>
                                <td class="d-text-center">
                                    <div id="status-modelo-{{ $reg->ot }}">
                                        <div
                                            class="status-modelo-container alm-display-inline-flex alm-flex-direction-column alm-align-items-center alm-gap-2px alm-padding-6px alm-border-radius-8px">
                                            <span class="badge-modelo-icon" title="{{ $tooltip }}"
                                                style="display: flex; align-items: center; justify-content: center; width: 52px; height: 52px; border-radius: 50%; background: {{ $bgColor }}; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); border: 2px solid {{ $borderColor }}; transition: all 0.2s ease;">
                                                <img src="{{ asset('images/' . $icon) }}" alt="{{ $label }}"
                                                    class="alm-width-34px alm-height-34px alm-object-fit-contain">
                                            </span>
                                            <span class="status-modelo-label"
                                                style="font-size: 11px; font-weight: 700; color: {{ $textColor }}; margin-top: 4px; text-transform: uppercase; white-space: nowrap;">
                                                {{ $label }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="alm-date d-text-center">
                                    {{ $reg->alert_sent_at ? $reg->alert_sent_at->format('d/m/Y H:i') : '—' }}
                                </td>
                                <td class="d-text-center">
                                    <span class="badge-pdf-count">{{ $count }}</span>
                                </td>
                                <td class="d-text-center">
                                    @if ($hasPendingChanges)
                                        <button class="btn-toggle-files"
                                            style="background: linear-gradient(135deg, #f97316, #ea580c); color: white; border: 1px solid #c2410c;"
                                            onclick="almacenRevisarCambios('{{ $reg->ot }}')">
                                            Revisar Cambios
                                        </button>
                                    @elseif ($hasFilesOrControl)
                                        <button class="btn-toggle-files"
                                            data-target="files-{{ $estado }}-{{ $loop->index }}"
                                            data-ot="{{ $reg->ot }}"
                                            id="toggle-btn-{{ $estado }}-{{ $loop->index }}"
                                            aria-expanded="false">
                                            Ver Archivos
                                        </button>
                                    @else
                                        <span class="d-text-subtle alm-font-size-0-85em">Sin archivos</span>
                                    @endif
                                </td>
                            </tr>

                            {{-- Fila desplegable de archivos --}}
                            @if ($hasFilesOrControl)
                                <tr class="alm-files-row" id="files-{{ $estado }}-{{ $loop->index }}">
                                    <td colspan="6">
                                        {{-- CONTENEDOR PRINCIPAL PROCESOS (CONTENEDOR 0) --}}
                                        <div class="alm-contenedor-principal-procesos"
                                            style="display: flex; flex-direction: column; gap: 25px; width: 100%; margin-top: 15px;">

                                            {{-- CONTENEDOR 1: FABRICACIÓN / RE-PROCESO DE MODELO --}}
                                            @include('almacen.partials.containers.fabrication_container')

                                            {{-- CONTENEDOR 2: PROCESO DE CASTING / MODELOS APROBADOS --}}
                                            @include('almacen.partials.containers.casting_container')

                                            {{-- CONTENEDOR 3: MODELOS RECHAZADOS --}}
                                            @include('almacen.partials.containers.rejected_container')
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endforeach
