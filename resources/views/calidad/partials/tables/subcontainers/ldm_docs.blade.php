@if ($hasAprobadosGroup)
    <div class="cal-subcontainer-aprobados"
        style="margin-bottom: 25px; padding: 18px; border-radius: 12px; background-color: #f0fdf4; border: 2px solid #22c55e; box-shadow: 0 3px 10px rgba(34, 197, 94, 0.08);">
        @php
            $rawClasses = !empty($aprobados) ? $aprobados : (!empty($aprobadosRaw) ? $aprobadosRaw : []);
            if (empty($rawClasses)) {
                $extractedFromFiles = [];
                foreach ($calidadAprobadosLdm ?? [] as $f) {
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
            
            $clasesAprobadasLista = [];
            foreach ($rawClasses as $rc) {
                $rcClean = preg_replace('/[^a-z0-9]/', '', strtolower($rc));
                $foundFull = false;
                foreach ($targetReg->ayudas_config ?? [] as $ac) {
                    $acClean = preg_replace('/[^a-z0-9]/', '', strtolower($ac));
                    if (!empty($acClean) && (str_contains($acClean, $rcClean) || str_contains($rcClean, $acClean))) {
                        $clasesAprobadasLista[] = $ac;
                        $foundFull = true;
                        break;
                    }
                }
                if (!$foundFull) {
                    $mappedFromFilename = $rc;
                    foreach ($calidadAprobadosLdm ?? [] as $f) {
                        $fn = strtoupper(basename($f['nombre']));
                        $rcUpper = strtoupper($rc);
                        if (preg_match('/(\d+)_(' . preg_quote($rcUpper, '/') . ')/i', $fn, $matches)) {
                            $mappedFromFilename = $matches[1] . ' - ' . $matches[2];
                            break;
                        }
                    }
                    $clasesAprobadasLista[] = $mappedFromFilename;
                }
            }
            $clasesAprobadasLista = array_values(array_unique($clasesAprobadasLista));

            if (empty($clasesAprobadasLista)) {
                $clasesAprobadasLista = ['General'];
            }

            $aprobadosCount = count($clasesAprobadasLista);
            $aprobadosText = '';
            if ($aprobadosCount == 1) {
                $aprobadosText = ' para la clase: ' . strtoupper($clasesAprobadasLista[0]);
            } elseif ($aprobadosCount > 1) {
                $aprobadosText = ' para las clases: ' . implode(', ', array_map('strtoupper', $clasesAprobadasLista));
            }
        @endphp
        <div
            style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1.5px solid #bbf7d0; padding-bottom: 8px; margin-bottom: 15px;">
            <h4
                style="margin: 0; color: #15803d; font-size: 1.05rem; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                <img src="{{ asset('images/Aprobado.png') }}"
                    style="width: 22px; height: 22px; object-fit: contain; vertical-align: middle;"> Formato de
                Liberación
                de
                Modelo (F-CCL-LDM){{ $aprobadosText }}
            </h4>
            <span
                style="font-size: 0.75rem; font-weight: 700; background: #dcfce7; color: #15803d; padding: 3px 10px; border-radius: 6px; border: 1px solid #86efac;">
                EN ORDEN / APROBADOS
            </span>
        </div>

        @php
            $formatosGenerados = [];
            $formatosEscaneados = [];
            foreach ($calidadAprobadosLdm as $doc) {
                $baseLow = strtolower(basename($doc['nombre']));
                if (strpos($baseLow, 'f-ccl-ldm') !== false) {
                    $formatosGenerados[] = $doc;
                } else {
                    $formatosEscaneados[] = $doc;
                }
            }
            $ldmGroups = [
                ['title' => 'Formatos Generados (F-CCL-LDM)', 'docs' => $formatosGenerados],
                ['title' => 'Documentos Escaneados', 'docs' => $formatosEscaneados],
            ];
        @endphp

        @foreach ($ldmGroups as $group)
            @if (count($group['docs']) > 0)
                <h5 style="margin-top: 15px; margin-bottom: 10px; color: #15803d; font-weight: 700; font-size: 0.95rem;">
                    {{ $group['title'] }}
                </h5>
                <div
                    class="alm-pdf-grid cal-background-color-f0fdf4 cal-padding-15px cal-border-radius-8px cal-border-1px-solid-bbf7d0">
                    @foreach ($group['docs'] as $otroArchivo)
                        @php
                            $canDelete = false;
                            $userPerfil = Auth::user()->perfil;
                            if (in_array($userPerfil, [1, 2, 3, 4, '1', '2', '3', '4'])) {
                                $canDelete = true;
                            }
                        @endphp
                        @php $isDwg = strtolower(pathinfo($otroArchivo['nombre'], PATHINFO_EXTENSION)) === 'dwg'; @endphp
                        <div class="dibujos-file-card card-otro"
                            style="animation-delay: {{ $loop->index * 0.05 }}s; border-left-color: #155724;">
                            <div class="file-icon-wrapper cal-cursor-pointer"
                                title="{{ $isDwg ? 'Descargar DWG' : 'Abrir PDF' }}">
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
                                <button class="btn-dibujos btn-dibujos-sm btn-ver cal-background-color-155724 cal-color-white"
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
        @endforeach
    </div>
@endif
