@if ($hasRechazadosGroup)
    @php
        $rawClasses = !empty($rechazados) ? $rechazados : (!empty($rechazadosRaw) ? $rechazadosRaw : []);
        $allFilesRech = array_merge($rechazadosDibujos ?? [], $rechazadosAyudas ?? [], $rechazadosOtros ?? []);
        
        if (empty($rawClasses)) {
            $extractedFromFiles = [];
            foreach ($allFilesRech as $f) {
                $fnClean = preg_replace('/[^a-z0-9]/', '', strtolower(basename($f['nombre'])));
                foreach ($targetReg->ayudas_config ?? [] as $ac) {
                    $acClean = preg_replace('/[^a-z0-9]/', '', strtolower($ac));
                    if (!empty($acClean) && str_contains($fnClean, $acClean)) {
                        $extractedFromFiles[] = $ac;
                    }
                }
            }
            $rawClasses = array_values(array_unique($extractedFromFiles));
        }

        $clasesRechazadasLista = [];
        foreach ($rawClasses as $rc) {
            $rcClean = preg_replace('/[^a-z0-9]/', '', strtolower($rc));
            $foundFull = false;
            foreach ($targetReg->ayudas_config ?? [] as $ac) {
                $acClean = preg_replace('/[^a-z0-9]/', '', strtolower($ac));
                if (!empty($acClean) && (str_contains($acClean, $rcClean) || str_contains($rcClean, $acClean))) {
                    $clasesRechazadasLista[] = $ac;
                    $foundFull = true;
                    break;
                }
            }
            if (!$foundFull) {
                $mappedFromFilename = $rc;
                foreach ($allFilesRech as $f) {
                    $fn = strtoupper(basename($f['nombre']));
                    $rcUpper = strtoupper($rc);
                    if (preg_match('/(\d+)_(' . preg_quote($rcUpper, '/') . ')/i', $fn, $matches)) {
                        $mappedFromFilename = $matches[1] . ' - ' . $matches[2];
                        break;
                    }
                }
                $clasesRechazadasLista[] = $mappedFromFilename;
            }
        }
        $clasesRechazadasLista = array_values(array_unique($clasesRechazadasLista));

        if (empty($clasesRechazadasLista)) {
            $clasesRechazadasLista = ['General'];
        }
    @endphp

    <div class="cal-subcontainer-rechazados"
        style="margin-bottom: 25px; padding: 18px; border-radius: 12px; background-color: #fef2f2; border: 2px solid #ef4444; box-shadow: 0 3px 10px rgba(239, 68, 68, 0.08);">
        @php
            $rechazadosCount = count($clasesRechazadasLista);
            $rechazadosText = '';
            if ($rechazadosCount == 1) {
                $rechazadosText = ' para la clase: ' . strtoupper($clasesRechazadasLista[0]);
            } elseif ($rechazadosCount > 1) {
                $rechazadosText = ' para las clases: ' . implode(', ', array_map('strtoupper', $clasesRechazadasLista));
            }
        @endphp
        <div
            style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1.5px solid #fecaca; padding-bottom: 8px; margin-bottom: 15px;">
            <h4
                style="margin: 0; color: #991b1b; font-size: 1.05rem; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                <img src="{{ asset('images/Rechazado.png') }}"
                    style="width: 22px; height: 22px; object-fit: contain; vertical-align: middle;"> Formato de Rechazo
                de
                Modelo (F_CCL_RDM)y SCAR (F_CCL_RDM_SCAR){{ $rechazadosText }}
            </h4>
            <span
                style="font-size: 0.75rem; font-weight: 700; background: #fee2e2; color: #991b1b; padding: 3px 10px; border-radius: 6px; border: 1px solid #fca5a5;">
                RECHAZADOS / DESVIACIONES
            </span>
        </div>

        @foreach ($clasesRechazadasLista as $claseRech)
            @php
                $cLow = strtolower($claseRech);

                $filtrarPorClase = function ($archivos) use ($cLow) {
                    if ($cLow === 'general') {
                        return $archivos;
                    }
                    $baseCLow = trim(preg_replace('/^\d+\s*-\s*/', '', $cLow));
                    $coreCLow = rtrim($baseCLow, 's');
                    return array_values(
                        array_filter($archivos, function ($a) use ($cLow, $baseCLow, $coreCLow) {
                            $n = strtolower(basename($a['nombre']));
                            $cl = strtolower($a['clase'] ?? '');
                            return $cl === $cLow ||
                                str_contains($cl, $cLow) ||
                                str_contains($n, $cLow) ||
                                str_contains($n, '_' . $cLow) ||
                                str_contains($n, '-' . $cLow) ||
                                str_contains($n, $baseCLow) ||
                                str_contains($n, '_' . $baseCLow) ||
                                str_contains($n, '-' . $baseCLow) ||
                                str_contains($n, $coreCLow) ||
                                str_contains($n, '_' . $coreCLow) ||
                                str_contains($n, '-' . $coreCLow);
                        }),
                    );
                };

                $calidadDibujosClass = $filtrarPorClase($rechazadosDibujos ?? []);
                $calidadAyudasClass = $filtrarPorClase($rechazadosAyudas ?? []);
                $otrosClass = $filtrarPorClase($rechazadosOtros ?? []);

                if (count($clasesRechazadasLista) === 1) {
                    if (empty($calidadDibujosClass)) {
                        $calidadDibujosClass = $rechazadosDibujos ?? [];
                    }
                    if (empty($calidadAyudasClass)) {
                        $calidadAyudasClass = $rechazadosAyudas ?? [];
                    }
                    if (empty($otrosClass)) {
                        $otrosClass = $rechazadosOtros ?? [];
                    }
                }
            @endphp

            <div class="cal-subcontainer-almacen"
                style="margin-bottom: 25px; padding: 18px; border-radius: 12px; background-color: #fef2f2; border: 2px solid #fca5a5; box-shadow: 0 3px 10px rgba(0, 0, 0, 0.03);">
                <div
                    style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1.5px solid #fca5a5; padding-bottom: 8px; margin-bottom: 15px;">
                    <h4
                        style="margin: 0; color: #b91c1c; font-size: 1.05rem; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                        <img src="{{ asset('images/Quality.png') }}"
                            style="width: 22px; height: 22px; object-fit: contain; filter: none;">
                        Proceso Activo — {{ strtoupper($claseRech) }}
                    </h4>
                    <span
                        style="font-size: 0.75rem; font-weight: 700; background: #fee2e2; color: #b91c1c; padding: 3px 10px; border-radius: 6px; border: 1px solid #fca5a5;">
                        RECHAZADO
                    </span>
                </div>

                @if (count($calidadDibujosClass) > 0)
                    <h5
                        style="margin-top: 10px; margin-bottom: 10px; color: #991b1b; font-weight: 700; font-size: 0.95rem;">
                        Dibujos Rechazados</h5>
                    <div class="alm-pdf-grid cal-background-color-fef2f2 cal-padding-15px cal-border-radius-8px cal-border-1px-solid-fecaca"
                        style="margin-bottom: 15px;">
                        @foreach ($calidadDibujosClass as $otroArchivo)
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
                                    <button
                                        class="btn-dibujos btn-dibujos-sm btn-ver cal-background-color-9c0300 cal-color-white"
                                        onclick="calidadVerPdf('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}')">{{ $isDwg ? 'Descargar' : 'Ver' }}</button>
                                    @if ($canDelete)
                                        <button
                                            class="btn-dibujos btn-dibujos-sm btn-eliminar cal-background-color-dc3545 cal-color-white"
                                            onclick="almacenEliminarOtroArchivo('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}', this, 'rechazado')">Eliminar</button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if (count($calidadAyudasClass) > 0)
                    <h5
                        style="margin-top: 15px; margin-bottom: 10px; color: #991b1b; font-weight: 700; font-size: 0.95rem;">
                        Ayudas Visuales Rechazadas</h5>
                    <div class="alm-pdf-grid cal-background-color-fef2f2 cal-padding-15px cal-border-radius-8px cal-border-1px-solid-fecaca"
                        style="margin-bottom: 15px;">
                        @foreach ($calidadAyudasClass as $otroArchivo)
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
                                    <button
                                        class="btn-dibujos btn-dibujos-sm btn-ver cal-background-color-9c0300 cal-color-white"
                                        onclick="calidadVerPdf('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}')">{{ $isDwg ? 'Descargar' : 'Ver' }}</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if (count($otrosClass) > 0)
                    <h5
                        style="margin-top: 15px; margin-bottom: 10px; color: #991b1b; font-weight: 700; font-size: 0.95rem;">
                        Formatos de Rechazo, SCAR y Evidencias</h5>
                    <div
                        class="alm-pdf-grid cal-background-color-fef2f2 cal-padding-15px cal-border-radius-8px cal-border-1px-solid-fecaca">
                        @foreach ($otrosClass as $otroArchivo)
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
                                    <button
                                        class="btn-dibujos btn-dibujos-sm btn-ver cal-background-color-9c0300 cal-color-white"
                                        onclick="calidadVerPdf('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}')">{{ $isDwg ? 'Descargar' : 'Ver' }}</button>
                                    @if ($canDelete)
                                        <button
                                            class="btn-dibujos btn-dibujos-sm btn-eliminar cal-background-color-dc3545 cal-color-white"
                                            onclick="almacenEliminarOtroArchivo('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}', this, '{{ $otroArchivo['origin'] ?? '' }}')">Eliminar</button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    </div>
@endif
