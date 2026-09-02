@if ($hasAlmacenGroup)
    <div class="cal-subcontainer-almacen"
        style="margin-bottom: 25px; padding: 18px; border-radius: 12px; background-color: #f0f9ff; border: 2px solid #0284c7; box-shadow: 0 3px 10px rgba(2, 132, 199, 0.08);">
        <div
            style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1.5px solid #bae6fd; padding-bottom: 8px; margin-bottom: 15px;">
            <h4
                style="margin: 0; color: #0369a1; font-size: 1.05rem; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                <img src="{{ asset('images/almacen.png') }}"
                    style="width: 22px; height: 22px; object-fit: contain; vertical-align: middle;"> Documentos, Dibujos y
                Ayudas Visuales Aprobados por Almacén
            </h4>
            <span
                style="font-size: 0.75rem; font-weight: 700; background: #e0f2fe; color: #0369a1; padding: 3px 10px; border-radius: 6px; border: 1px solid #7dd3fc;">
                DOCUMENTOS ALMACÉN
            </span>
        </div>

        {{-- Dibujos de Fundición --}}
        @if (count($allValidDibujos) > 0)
            <h5 style="margin-top: 10px; margin-bottom: 10px; color: #0369a1; font-weight: 700; font-size: 0.95rem;">
                Dibujos de Fundición</h5>
            <div class="alm-pdf-grid cal-background-color-f0f9ff cal-padding-15px cal-border-radius-8px cal-border-1px-solid-bae6fd"
                style="margin-bottom: 15px;">
                @foreach ($allValidDibujos as $archivoInfo)
                    @php $isDwg = strtolower(pathinfo($archivoInfo['nombre'], PATHINFO_EXTENSION)) === 'dwg'; @endphp
                    <div class="dibujos-file-card" style="animation-delay: {{ $loop->index * 0.05 }}s; border-left-color: #0284c7;">
                        <div class="file-icon-wrapper cal-cursor-pointer" title="{{ $isDwg ? 'Descargar DWG' : 'Abrir PDF' }}">
                            <img src="{{ asset('images/' . ($isDwg ? 'dwg-shadow.png' : 'pdf-view-shadow.png')) }}"
                                class="file-icon icon-default" />
                            <img src="{{ asset('images/' . ($isDwg ? 'dwg.png' : 'pdf-view.png')) }}"
                                class="file-icon icon-hover" />
                        </div>
                        <div class="file-name cal-cursor-pointer" title="{{ $isDwg ? 'Descargar DWG' : 'Abrir PDF' }}"
                            onclick="calidadVerPdf('{{ $archivoInfo['ot'] }}', '{{ $archivoInfo['nombre'] }}', 'dibujo')">
                            {{ basename($archivoInfo['nombre']) }}
                        </div>
                        <div class="file-actions">
                            <button class="btn-dibujos btn-dibujos-sm btn-ver cal-background-color-0369a1 cal-color-white"
                                onclick="calidadVerPdf('{{ $archivoInfo['ot'] }}', '{{ $archivoInfo['nombre'] }}', 'dibujo')">{{ $isDwg ? 'Descargar' : 'Ver' }}</button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Ayudas Visuales de Fundición --}}
        @if (count($allValidAyudas) > 0)
            <h5 style="margin-top: 15px; margin-bottom: 10px; color: #0369a1; font-weight: 700; font-size: 0.95rem;">
                Ayudas Visuales de Fundición</h5>
            <div class="alm-pdf-grid cal-background-color-f0f9ff cal-padding-15px cal-border-radius-8px cal-border-1px-solid-bae6fd"
                style="margin-bottom: 15px;">
                @foreach ($allValidAyudas as $ayudaArchivo)
                    @php $isDwg = strtolower(pathinfo($ayudaArchivo['nombre'], PATHINFO_EXTENSION)) === 'dwg'; @endphp
                    <div class="dibujos-file-card card-ayuda"
                        style="animation-delay: {{ $loop->index * 0.05 }}s; border-left-color: #0284c7;">
                        <div class="file-icon-wrapper cal-cursor-pointer" title="{{ $isDwg ? 'Descargar DWG' : 'Abrir PDF' }}">
                            <img src="{{ asset('images/' . ($isDwg ? 'dwg-shadow.png' : 'pdf-view-shadow.png')) }}"
                                class="file-icon icon-default" />
                            <img src="{{ asset('images/' . ($isDwg ? 'dwg.png' : 'pdf-view.png')) }}"
                                class="file-icon icon-hover" />
                        </div>
                        <div class="file-name cal-cursor-pointer" title="{{ $isDwg ? 'Descargar DWG' : 'Abrir PDF' }}"
                            onclick="calidadVerPdf('{{ $ayudaArchivo['ot'] }}', '{{ $ayudaArchivo['nombre'] }}', '{{ $ayudaArchivo['tipo'] }}')">
                            {{ basename($ayudaArchivo['nombre']) }}
                        </div>
                        <div class="file-actions">
                            <button class="btn-dibujos btn-dibujos-sm btn-ver btn-ayuda-color"
                                onclick="calidadVerPdf('{{ $ayudaArchivo['ot'] }}', '{{ $ayudaArchivo['nombre'] }}', '{{ $ayudaArchivo['tipo'] }}')">{{ $isDwg ? 'Descargar' : 'Ver' }}</button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Documentos Aprobados de Almacén --}}
        @if (count($almacenAprobadosDocs) > 0)
            <h5 style="margin-top: 15px; margin-bottom: 10px; color: #0369a1; font-weight: 700; font-size: 0.95rem;">
                Documentos Aprobados de Almacén</h5>
            <div
                class="alm-pdf-grid cal-background-color-f0f9ff cal-padding-15px cal-border-radius-8px cal-border-1px-solid-bae6fd">
                @foreach ($almacenAprobadosDocs as $otroArchivo)
                    @php
                        $canDelete = false;
                        $fileOwner = $otroArchivo['owner'] ?? '';
                        $userPerfil = Auth::user()->perfil;
                        if (in_array($userPerfil, [1, 2, 3, '1', '2', '3'])) {
                            $canDelete = true;
                        } elseif ($userPerfil == 5 && $fileOwner === 'almacen') {
                            if (!$targetReg->pre_orden_email_sent && !$targetReg->pre_orden_sent) {
                                $canDelete = true;
                            }
                        }
                     @endphp
                    @php $isDwg = strtolower(pathinfo($otroArchivo['nombre'], PATHINFO_EXTENSION)) === 'dwg'; @endphp
                    <div class="dibujos-file-card card-otro"
                        style="animation-delay: {{ $loop->index * 0.05 }}s; border-left-color: #0284c7;">
                        <div class="file-icon-wrapper cal-cursor-pointer" title="{{ $isDwg ? 'Descargar DWG' : 'Abrir PDF' }}">
                            <img src="{{ asset('images/' . ($isDwg ? 'dwg-shadow.png' : 'pdf-view-shadow.png')) }}"
                                class="file-icon icon-default" />
                            <img src="{{ asset('images/' . ($isDwg ? 'dwg.png' : 'pdf-view.png')) }}"
                                class="file-icon icon-hover" />
                        </div>
                        <div class="file-name cal-cursor-pointer" title="{{ $isDwg ? 'Descargar DWG' : 'Abrir PDF' }}"
                            onclick="calidadVerPdf('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}')">
                            {{ basename($otroArchivo['nombre']) }}
                        </div>
                        <div class="file-actions cal-display-flex cal-gap-5px">
                            <button class="btn-dibujos btn-dibujos-sm btn-ver cal-background-color-0369a1 cal-color-white"
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