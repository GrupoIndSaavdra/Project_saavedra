@if ($hasRechazadosGroup)
    <div class="cal-subcontainer-rechazados"
        style="margin-bottom: 25px; padding: 18px; border-radius: 12px; background-color: #fef2f2; border: 2px solid #ef4444; box-shadow: 0 3px 10px rgba(239, 68, 68, 0.08);">
        <div
            style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1.5px solid #fecaca; padding-bottom: 8px; margin-bottom: 15px;">
            <h4
                style="margin: 0; color: #991b1b; font-size: 1.05rem; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                <img src="{{ asset('images/Rechazado.png') }}"
                    style="width: 22px; height: 22px; object-fit: contain; vertical-align: middle;"> Formato de Rechazo de
                Modelo y SCAR
            </h4>
            <span
                style="font-size: 0.75rem; font-weight: 700; background: #fee2e2; color: #991b1b; padding: 3px 10px; border-radius: 6px; border: 1px solid #fca5a5;">
                RECHAZADOS / DESVIACIONES
            </span>
        </div>

        @if (count($rechazadosDibujos) > 0)
            <h5 style="margin-top: 10px; margin-bottom: 10px; color: #991b1b; font-weight: 700; font-size: 0.95rem;">
                Dibujos Rechazados</h5>
            <div class="alm-pdf-grid cal-background-color-fef2f2 cal-padding-15px cal-border-radius-8px cal-border-1px-solid-fecaca"
                style="margin-bottom: 15px;">
                @foreach ($rechazadosDibujos as $otroArchivo)
                    @php
                        $canDelete = false;
                        $userPerfil = Auth::user()->perfil;
                        if (in_array($userPerfil, [1, 2, 3, 4, '1', '2', '3', '4'])) {
                            $canDelete = true;
                        }
                     @endphp
                    @php $isDwg = strtolower(pathinfo($otroArchivo['nombre'], PATHINFO_EXTENSION)) === 'dwg'; @endphp
                    <div class="dibujos-file-card card-otro"
                        style="animation-delay: {{ $loop->index * 0.05 }}s; border-left-color: #9c0300;">
                        <div class="file-icon-wrapper cal-cursor-pointer" title="Abrir Archivo">
                            <img src="{{ asset('images/' . ($isDwg ? 'dwg-shadow.png' : 'pdf-view-shadow.png')) }}"
                                class="file-icon icon-default" />
                            <img src="{{ asset('images/' . ($isDwg ? 'dwg.png' : 'pdf-view.png')) }}"
                                class="file-icon icon-hover" />
                        </div>
                        <div class="file-name cal-cursor-pointer"
                            onclick="calidadVerPdf('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}')">
                            {{ basename($otroArchivo['nombre']) }}
                        </div>
                        <div class="file-actions cal-display-flex cal-gap-5px">
                            <button class="btn-dibujos btn-dibujos-sm btn-ver cal-background-color-9c0300 cal-color-white"
                                onclick="calidadVerPdf('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}')">{{ $isDwg ? 'Descargar' : 'Ver' }}</button>
                            @if ($canDelete)
                                <button class="btn-dibujos btn-dibujos-sm btn-eliminar cal-background-color-dc3545 cal-color-white"
                                    onclick="almacenEliminarOtroArchivo('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}', this, 'rechazado')">Eliminar</button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if (count($rechazadosAyudas) > 0)
            <h5 style="margin-top: 15px; margin-bottom: 10px; color: #991b1b; font-weight: 700; font-size: 0.95rem;">
                Ayudas Visuales Rechazadas</h5>
            <div class="alm-pdf-grid cal-background-color-fef2f2 cal-padding-15px cal-border-radius-8px cal-border-1px-solid-fecaca"
                style="margin-bottom: 15px;">
                @foreach ($rechazadosAyudas as $otroArchivo)
                    @php $isDwg = strtolower(pathinfo($otroArchivo['nombre'], PATHINFO_EXTENSION)) === 'dwg'; @endphp
                    <div class="dibujos-file-card card-otro"
                        style="animation-delay: {{ $loop->index * 0.05 }}s; border-left-color: #9c0300;">
                        <div class="file-icon-wrapper cal-cursor-pointer">
                            <img src="{{ asset('images/' . ($isDwg ? 'dwg-shadow.png' : 'pdf-view-shadow.png')) }}"
                                class="file-icon icon-default" />
                            <img src="{{ asset('images/' . ($isDwg ? 'dwg.png' : 'pdf-view.png')) }}"
                                class="file-icon icon-hover" />
                        </div>
                        <div class="file-name cal-cursor-pointer"
                            onclick="calidadVerPdf('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}')">
                            {{ basename($otroArchivo['nombre']) }}
                        </div>
                        <div class="file-actions cal-display-flex cal-gap-5px">
                            <button class="btn-dibujos btn-dibujos-sm btn-ver cal-background-color-9c0300 cal-color-white"
                                onclick="calidadVerPdf('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}')">{{ $isDwg ? 'Descargar' : 'Ver' }}</button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if (count($rechazadosOtros) > 0)
            <h5 style="margin-top: 15px; margin-bottom: 10px; color: #991b1b; font-weight: 700; font-size: 0.95rem;">
                Formatos de Rechazo, SCAR y Evidencias</h5>
            <div
                class="alm-pdf-grid cal-background-color-fef2f2 cal-padding-15px cal-border-radius-8px cal-border-1px-solid-fecaca">
                @foreach ($rechazadosOtros as $otroArchivo)
                    @php
                        $canDelete = false;
                        $userPerfil = Auth::user()->perfil;
                        if (in_array($userPerfil, [1, 2, 3, 4, '1', '2', '3', '4'])) {
                            $canDelete = true;
                        }
                     @endphp
                    @php $isDwg = strtolower(pathinfo($otroArchivo['nombre'], PATHINFO_EXTENSION)) === 'dwg'; @endphp
                    <div class="dibujos-file-card card-otro"
                        style="animation-delay: {{ $loop->index * 0.05 }}s; border-left-color: #9c0300;">
                        <div class="file-icon-wrapper cal-cursor-pointer">
                            <img src="{{ asset('images/' . ($isDwg ? 'dwg-shadow.png' : 'pdf-view-shadow.png')) }}"
                                class="file-icon icon-default" />
                            <img src="{{ asset('images/' . ($isDwg ? 'dwg.png' : 'pdf-view.png')) }}"
                                class="file-icon icon-hover" />
                        </div>
                        <div class="file-name cal-cursor-pointer"
                            onclick="calidadVerPdf('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}')">
                            {{ basename($otroArchivo['nombre']) }}
                        </div>
                        <div class="file-actions cal-display-flex cal-gap-5px">
                            <button class="btn-dibujos btn-dibujos-sm btn-ver cal-background-color-9c0300 cal-color-white"
                                onclick="calidadVerPdf('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}')">{{ $isDwg ? 'Descargar' : 'Ver' }}</button>
                            @if ($canDelete)
                                <button class="btn-dibujos btn-dibujos-sm btn-eliminar cal-background-color-dc3545 cal-color-white"
                                    onclick="almacenEliminarOtroArchivo('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}', this, '{{ $otroArchivo['origin'] ?? '' }}')">Eliminar</button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endif