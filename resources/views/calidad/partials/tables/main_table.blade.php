@foreach ([
        'activa' => 'Documentos Activos (Dibujos y Ayudas)',
        'inactiva' => 'Documentos Inactivos (Histórico)',
    ] as $estado => $titulo)
    @php
        $registrosEstado = $registros->where('status', $estado);
    @endphp

    <div class="alm-table-card cal-margin-bottom-2em">
        <div class="alm-table-header"
            style="{{ $estado === 'inactiva' ? 'background: #6c757d; border-bottom: 2px solid #5a6268;' : '' }}">
            <h2>{{ $titulo }}</h2>
            <span class="alm-results-count">{{ $registrosEstado->count() }}
                resultado{{ $registrosEstado->count() !== 1 ? 's' : '' }}</span>
        </div>
        @if ($estado === 'activa')
            {{-- ── BARRA DE SINCRONIZACIÓN MANUAL (solo tabla Activa) ── --}}
            <div id="sync-bar-activa"
                class="cal-display-flex cal-align-items-center cal-justify-content-space-between cal-flex-wrap-wrap cal-gap-10px cal-padding-10px-20px cal-background-linear-gradient-135deg-f0f9ff-0-e0f2fe-100pct cal-border-bottom-1px-solid-bae6fd cal-font-size-0-85rem cal-color-0369a1 cal-font-family-Poppins-sans-serif">
                <span id="sync-status-calidad" class="cal-display-flex cal-align-items-center cal-gap-6px cal-font-weight-600">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="#0369a1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                        class="cal-flex-shrink-0">
                        <polyline points="23 4 23 10 17 10"></polyline>
                        <polyline points="1 20 1 14 7 14"></polyline>
                        <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15">
                        </path>
                    </svg>
                    <span id="sync-last-time-calidad">Sincronización automática activa</span>
                </span>
                <button id="btn-sync-manual-calidad" onclick="sincronizarDibujos(true)" title="Sincronizar archivos ahora"
                    class="cal-display-inline-flex cal-align-items-center cal-gap-7px cal-padding-7px-18px cal-background-linear-gradient-135deg-0369a1-0-0284c7-100pct cal-color-fff cal-border-none cal-border-radius-8px cal-font-weight-700 cal-font-size-0-82rem cal-font-family-Poppins-sans-serif cal-cursor-pointer cal-box-shadow-0-3px-10px-rgba-3-105-161-0-25 cal-transition-all-0-2s-ease cal-white-space-nowrap"
                    onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 5px 15px rgba(3,105,161,0.35)';"
                    onmouseout="this.style.transform=''; this.style.boxShadow='0 3px 10px rgba(3,105,161,0.25)';">
                    <svg id="sync-icon-calidad" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="23 4 23 10 17 10"></polyline>
                        <polyline points="1 20 1 14 7 14"></polyline>
                        <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15">
                        </path>
                    </svg>
                    Sincronizar ahora
                </button>
            </div>
        @endif
        @if ($registrosEstado->isEmpty())
            <div class="alm-empty">
                <div class="alm-empty-icon">
                    <img src="{{ asset('images/noPieces.png') }}" alt="Sin resultados" class="cal-width-64px cal-opacity-0-5" />
                </div>
                <p>
                    @if ($busquedaOt || $desde || $hasta)
                        No se encontraron registros de
                        {{ strtolower($titulo) }}
                        con los filtros aplicados.
                    @else
                        Aún no hay registros en la bandeja de
                        {{ strtolower($titulo) }}.
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
                                Orden de Trabajo
                            </th>
                            <th style="width:12%; {{ $estado === 'inactiva' ? 'background: #6c757d; border-color: #5a6268;' : '' }}"
                                class="d-text-center">
                                Estado
                            </th>
                            <th style="width:12%; {{ $estado === 'inactiva' ? 'background: #6c757d; border-color: #5a6268;' : '' }}"
                                class="d-text-center">
                                Modelo
                            </th>
                            <th style="width:18%; {{ $estado === 'inactiva' ? 'background: #6c757d; border-color: #5a6268;' : '' }}"
                                class="d-text-center">
                                Último envío
                            </th>
                            <th style="width:10%; {{ $estado === 'inactiva' ? 'background: #6c757d; border-color: #5a6268;' : '' }}"
                                class="d-text-center">
                                Archivos
                            </th>
                            <th style="width:16%; {{ $estado === 'inactiva' ? 'background: #6c757d; border-color: #5a6268;' : '' }}"
                                class="d-text-center">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody id="alm-tbody-{{ $estado }}">
                        @foreach ($registrosEstado as $reg)
                            @include('calidad.partials.tables.table_row', [
                                'reg' => $reg,
                                'estado' => $estado,
                                'titulo' => $titulo,
                                'deptName' => $deptName,
                                'loop' => $loop,
                                'busquedaOt' => $busquedaOt ?? '',
                                'desde' => $desde ?? '',
                                'hasta' => $hasta ?? ''
                            ])
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endforeach