    @php
        if (!isset($formatClase)) {
            $formatClase = function ($c) {
                $map = [
                    'molde' => '1 - MOLDES',
                    'moldes' => '1 - MOLDES',
                    '1 - moldes' => '1 - MOLDES',
                    'bombillo' => '2 - BOMBILLO',
                    '2 - bombillo' => '2 - BOMBILLO',
                    'embudo' => '3 - EMBUDO',
                    '3 - embudo' => '3 - EMBUDO',
                    'corona' => '4 - CORONA',
                    '4 - corona' => '4 - CORONA',
                    'plato' => '5 - PLATO',
                    '5 - plato' => '5 - PLATO',
                    'fondo' => '6 - FONDO',
                    '6 - fondo' => '6 - FONDO',
                    'obturador' => '7 - OBTURADOR',
                    '7 - obturador' => '7 - OBTURADOR',
                    'cabeza de soplo' => '8 - CABEZA DE SOPLO',
                    'cabeza' => '8 - CABEZA DE SOPLO',
                    '8 - cabeza de soplo' => '8 - CABEZA DE SOPLO',
                    'candado obturador' => '9 - CANDADO OBTURADOR',
                    'candado' => '9 - CANDADO OBTURADOR',
                    '9 - candado obturador' => '9 - CANDADO OBTURADOR',
                    'pistones' => 'Pistones',
                    'guias' => 'Guías',
                    'guías' => 'Guías'
                ];
                $cLower = strtolower(trim($c));
                return $map[$cLower] ?? ucfirst($c);
            };
        }
    @endphp

    @php
        if (!isset($formatClase)) {
            $formatClase = function ($c) {
                $map = [
                    'molde' => '1 - MOLDES',
                    'moldes' => '1 - MOLDES',
                    '1 - moldes' => '1 - MOLDES',
                    'bombillo' => '2 - BOMBILLO',
                    '2 - bombillo' => '2 - BOMBILLO',
                    'embudo' => '3 - EMBUDO',
                    '3 - embudo' => '3 - EMBUDO',
                    'corona' => '4 - CORONA',
                    '4 - corona' => '4 - CORONA',
                    'plato' => '5 - PLATO',
                    '5 - plato' => '5 - PLATO',
                    'fondo' => '6 - FONDO',
                    '6 - fondo' => '6 - FONDO',
                    'obturador' => '7 - OBTURADOR',
                    '7 - obturador' => '7 - OBTURADOR',
                    'cabeza de soplo' => '8 - CABEZA DE SOPLO',
                    'cabeza' => '8 - CABEZA DE SOPLO',
                    '8 - cabeza de soplo' => '8 - CABEZA DE SOPLO',
                    'candado obturador' => '9 - CANDADO OBTURADOR',
                    'candado' => '9 - CANDADO OBTURADOR',
                    '9 - candado obturador' => '9 - CANDADO OBTURADOR',
                    'pistones' => 'Pistones',
                    'guias' => 'Guías',
                    'guías' => 'Guías'
                ];
                $cLower = strtolower(trim($c));
                return $map[$cLower] ?? ucfirst($c);
            };
        }
    @endphp

{{-- CONTENEDOR 3: MODELOS RECHAZADOS --}}
@php
    $esReproceso = (bool) preg_match('/_R\d+$/i', $reg->ot);
@endphp
@if ($tieneRechazados)
    @php
        $baseOtCleanRep = preg_replace('/_.*_R\d+$|_R\d+$/i', '', $reg->ot);
        $allReprocesosOt = \App\Models\FundicionHistory::where(function($q) use ($baseOtCleanRep) {
            $q->where('ot', 'LIKE', $baseOtCleanRep . '_%_R%')
              ->orWhere('ot', 'LIKE', $baseOtCleanRep . '_R%');
        })->orderBy('id', 'desc')->get();

        $latestReproceso = $allReprocesosOt->first();
        
        // Define colors based on state
        $containerBg = '#fef2f2';
        $containerBorder = '#dc2626';
        $headerColor = '#dc2626';
        $badgeBg = '#fee2e2';
        $badgeColor = '#b91c1c';
        $badgeBorder = '#fca5a5';
        
        $subContainerBg = '#fef2f2';

        // Obtener lista de clases rechazadas únicas a separar
        $clasesRechazadasLista = !empty($rechazados) ? $rechazados : [];
        if (empty($clasesRechazadasLista)) {
            $extractedFromFiles = [];
            $allFilesRech = array_merge($dibujosRechazadosOrig, $ayudasRechazadosOrig, $rechazadosDibujos, $rechazadosAyudas, $rechazadosOtros);
            foreach ($allFilesRech as $f) {
                $fn = strtolower(basename($f['nombre']));
                foreach ($targetReg->ayudas_config ?? [] as $ac) {
                    $acLow = strtolower($ac);
                    if (!empty($acLow) && str_contains($fn, $acLow)) {
                        $extractedFromFiles[] = $ac;
                    }
                }
            }
            $clasesRechazadasLista = array_values(array_unique($extractedFromFiles));
        }
        if (empty($clasesRechazadasLista)) {
            $clasesRechazadasLista = ['General'];
        }
    @endphp

    <div class="alm-process-block" style="margin-bottom: 25px; padding: 20px; border-radius: 14px; background-color: {{ $containerBg }}; border: 2px solid {{ $containerBorder }}; box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04);">
        <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid {{ $containerBorder }}; padding-bottom: 10px; margin-bottom: 15px;">
            <h3 style="margin: 0; color: {{ $headerColor }}; font-size: 1.15rem; font-weight: 700; display: flex; align-items: center; gap: 10px;">
                <img src="{{ asset('images/Rechazado.png') }}" style="width: 30px; height: 30px; object-fit: contain;">
                Etapa: Modelos Rechazados ({{ implode(', ', array_map('ucfirst', $clasesRechazadasLista)) }})
            </h3>
            <span style="font-size: 0.8rem; font-weight: 700; background: {{ $badgeBg }}; color: {{ $badgeColor }}; padding: 4px 12px; border-radius: 6px; border: 1px solid {{ $badgeBorder }};">
                RECHAZADOS
            </span>
        </div>

        @foreach ($clasesRechazadasLista as $claseRech)
            @php
                $cLow = strtolower($claseRech);
                
                // Helper para filtrar por clase
                $filtrarPorClase = function($archivos) use ($cLow) {
                    if ($cLow === 'general') return $archivos;
                    return array_values(array_filter($archivos, function($a) use ($cLow) {
                        $n = strtolower(basename($a['nombre']));
                        $cl = strtolower($a['clase'] ?? '');
                        return ($cl === $cLow || str_contains($cl, $cLow) || str_contains($n, $cLow) || str_contains($n, '_' . $cLow) || str_contains($n, '-' . $cLow));
                    }));
                };

                $dibujosClass = $filtrarPorClase($dibujosRechazadosOrig);
                $ayudasClass = $filtrarPorClase($ayudasRechazadosOrig);
                $calidadDibujosClass = $filtrarPorClase($rechazadosDibujos);
                $calidadAyudasClass = $filtrarPorClase($rechazadosAyudas);
                $otrosClass = $filtrarPorClase($rechazadosOtros);

                // Si no hay archivos específicos para esta clase y solo hay una clase, asignar todos los que quedaron
                if (count($clasesRechazadasLista) === 1) {
                    if (empty($dibujosClass)) $dibujosClass = $dibujosRechazadosOrig;
                    if (empty($ayudasClass)) $ayudasClass = $ayudasRechazadosOrig;
                    if (empty($calidadDibujosClass)) $calidadDibujosClass = $rechazadosDibujos;
                    if (empty($calidadAyudasClass)) $calidadAyudasClass = $rechazadosAyudas;
                    if (empty($otrosClass)) $otrosClass = $rechazadosOtros;
                }

                // 1. Obtener la última liberación/rechazo de Calidad para esta clase
                $latestRechazoCalidad = \App\Models\LiberacionModeloFundicion::where(function($q) use ($reg, $targetReg) {
                    $q->where('ot', '=', $reg->ot)
                      ->orWhere('ot', '=', $targetReg->ot);
                })
                ->where(function($q) use ($cLow) {
                    $q->whereRaw("LOWER(tipo_modelo) = ?", [$cLow])
                      ->orWhereRaw("LOWER(tipo_modelo) LIKE ?", ['%' . $cLow . '%']);
                })
                ->where(function($q) {
                    $q->where('decision', '=', 'rechazar')
                      ->orWhere('decision', '=', 'rechazado');
                })
                ->orderBy('id', 'desc')
                ->first();

                // 2. Buscar la OT de reproceso para esta clase (creada después del registro original)
                $reprocesoClaseObj = $allReprocesosOt->filter(function($hist) use ($cLow, $targetReg, $latestRechazoCalidad) {
                    // Si tenemos la liberación de calidad, usamos su fecha como base mínima. Si no, usamos la fecha de la OT original.
                    $minTime = $latestRechazoCalidad ? strtotime($latestRechazoCalidad->created_at) : strtotime($targetReg->created_at);
                    
                    if (strtotime($hist->created_at) < $minTime) {
                        return false;
                    }
                    $otLow = strtolower($hist->ot);
                    // Comprobar si la clase está en el nombre de la OT
                    if (str_contains($otLow, '_' . str_replace(' ', '_', $cLow) . '_r') || 
                        str_contains($otLow, '_' . $cLow . '_r') || 
                        str_contains($otLow, '_' . $cLow . 'r')) {
                        return true;
                    }
                    // Comprobar ayudas_config
                    $cfg = is_array($hist->ayudas_config) ? $hist->ayudas_config : [];
                    foreach ($cfg as $c) {
                        if (strtolower(trim($c)) === $cLow) return true;
                    }
                    return false;
                })->sortByDesc('id')->first();


                $isClaseRechazoProcesada = ($reprocesoClaseObj !== null);
                $subContainerBorder = $isClaseRechazoProcesada ? '#fca5a5' : '#dc2626';
            @endphp

            <div class="cal-subcontainer-almacen" style="margin-bottom: 25px; padding: 18px; border-radius: 12px; background-color: {{ $subContainerBg }}; border: 2px solid {{ $subContainerBorder }}; box-shadow: 0 3px 10px rgba(0, 0, 0, 0.03);">
                <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1.5px solid #fca5a5; padding-bottom: 8px; margin-bottom: 15px;">
                    <h4 style="margin: 0; color: #b91c1c; font-size: 1.05rem; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                        <img src="{{ asset('images/almacen.png') }}" style="width: 22px; height: 22px; object-fit: contain; filter: {{ $isClaseRechazoProcesada ? 'grayscale(100%)' : 'none' }};">
                        {{ $isClaseRechazoProcesada ? 'Archivos Históricos de Rechazo — ' . ucfirst($claseRech) : 'Proceso Activo — ' . ucfirst($claseRech) }}
                    </h4>
                    <span style="font-size: 0.75rem; font-weight: 700; background: #fee2e2; color: #b91c1c; padding: 3px 10px; border-radius: 6px; border: 1px solid #fca5a5;">
                        {{ $isClaseRechazoProcesada ? 'REPROCESADO' : 'RECHAZADO' }}
                    </span>
                </div>

                @if ($isClaseRechazoProcesada)
                    <details class="alm-historico-details" style="margin-bottom: 20px;">
                        <summary style="cursor: pointer; color: #b91c1c; font-weight: 700; font-size: 0.95rem; user-select: none; padding: 10px 15px; background: #fee2e2; border-radius: 8px; border: 1px solid #fca5a5; display: inline-block; transition: all 0.2s ease;" onmouseover="this.style.background='#fecaca'" onmouseout="this.style.background='#fee2e2'">
                            <span style="display: flex; align-items: center; gap: 8px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                Mostrar Archivos Históricos del Rechazo
                            </span>
                        </summary>
                        <div style="margin-top: 15px; padding-left: 15px; border-left: 3px solid #fca5a5; animation: fadeIn 0.3s ease-in-out;">
                @endif

                {{-- Dibujos Originales Rechazados --}}
                @if (count($dibujosClass) > 0)
                    <h4 style="margin-top: 10px; margin-bottom: 10px; color: #b91c1c; font-weight: 700;">Dibujos de Fundición (Rechazados)</h4>
                    <div class="alm-pdf-grid" style="background-color: {{ $containerBg }}; border: 1px solid #fca5a5; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                        @foreach ($dibujosClass as $archivoInfo)
                            @php $isDwg = strtolower(pathinfo($archivoInfo['nombre'], PATHINFO_EXTENSION)) === 'dwg'; @endphp
<div class="dibujos-file-card card-otro" style="animation-delay: {{ $loop->index * 0.05 }}s; border-left-color: #dc2626;">
                                <div class="file-icon-wrapper alm-cursor-pointer" title="{{ $isDwg ? 'Descargar DWG' : 'Abrir PDF' }}">
                                    <img src="{{ asset('images/' . ($isDwg ? 'dwg-shadow.png' : 'pdf-view-shadow.png')) }}" class="file-icon icon-default">
                                    <img src="{{ asset('images/' . ($isDwg ? 'dwg.png' : 'pdf-view.png')) }}" class="file-icon icon-hover">
                                </div>
                                <div class="file-name alm-cursor-pointer" title="{{ $isDwg ? 'Descargar DWG' : 'Abrir PDF' }}" onclick="almacenVerPdf('{{ $archivoInfo['ot'] }}', '{{ $archivoInfo['nombre'] }}', '{{ $archivoInfo['tipo'] }}')">
                                    {{ basename($archivoInfo['nombre']) }}
                                </div>
                                <div class="file-actions alm-flex-gap-5">
                                    <button type="button" class="btn-dibujos btn-dibujos-sm btn-ver" style="background-color: #b91c1c; color: white;" onclick="almacenVerPdf('{{ $archivoInfo['ot'] }}', '{{ $archivoInfo['nombre'] }}', '{{ $archivoInfo['tipo'] }}')">{{ $isDwg ? 'Descargar' : 'Ver' }}</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Ayudas Visuales Originales Rechazadas --}}
                @if (count($ayudasClass) > 0)
                    <h4 style="margin-top: 15px; margin-bottom: 10px; color: #b91c1c; font-weight: 700;">Ayudas Visuales</h4>
                    <div class="alm-pdf-grid" style="background-color: {{ $containerBg }}; border: 1px solid #fca5a5; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                        @foreach ($ayudasClass as $archivoInfo)
                            @php $ayudaUrl = $archivoInfo['url'] ?? ''; @endphp
                            @php $isDwg = strtolower(pathinfo($archivoInfo['nombre'], PATHINFO_EXTENSION)) === 'dwg'; @endphp
<div class="dibujos-file-card card-ayuda" style="animation-delay: {{ $loop->index * 0.05 }}s; border-left-color: #dc2626;">
                                <div class="file-icon-wrapper alm-cursor-pointer" title="{{ $isDwg ? 'Descargar DWG' : 'Abrir PDF' }}">
                                    <img src="{{ asset('images/' . ($isDwg ? 'dwg-shadow.png' : 'pdf-view-shadow.png')) }}" class="file-icon icon-default">
                                    <img src="{{ asset('images/' . ($isDwg ? 'dwg.png' : 'pdf-view.png')) }}" class="file-icon icon-hover">
                                </div>
                                <div class="file-name alm-cursor-pointer" title="{{ $isDwg ? 'Descargar DWG' : 'Abrir PDF' }}" onclick="almacenAbrirArchivo('{{ $ayudaUrl }}', '{{ $archivoInfo['ot'] }}', '{{ $archivoInfo['nombre'] }}', 'ayuda')">
                                    {{ basename($archivoInfo['nombre']) }}
                                </div>
                                <div class="file-actions alm-flex-gap-5">
                                    <button type="button" class="btn-dibujos btn-dibujos-sm btn-ver" style="background-color: #b91c1c; color: white;" onclick="almacenAbrirArchivo('{{ $ayudaUrl }}', '{{ $archivoInfo['ot'] }}', '{{ $archivoInfo['nombre'] }}', 'ayuda')">{{ $isDwg ? 'Descargar' : 'Ver' }}</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Dibujos Rechazados (Calidad) --}}
                @if (count($calidadDibujosClass) > 0)
                    <h4 style="margin-top: 10px; margin-bottom: 10px; color: #b91c1c; font-weight: 700;">Documentos Adjuntos de Calidad (Dibujos)</h4>
                    <div class="alm-pdf-grid" style="background-color: {{ $containerBg }}; border: 1px solid #fca5a5; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                        @foreach ($calidadDibujosClass as $otroArchivo)
                            @php $isDwg = strtolower(pathinfo($otroArchivo['nombre'], PATHINFO_EXTENSION)) === 'dwg'; @endphp
<div class="dibujos-file-card card-otro" style="animation-delay: {{ $loop->index * 0.05 }}s; border-left-color: #dc2626;">
                                <div class="file-icon-wrapper alm-cursor-pointer" title="{{ $isDwg ? 'Descargar DWG' : 'Abrir PDF' }}">
                                    <img src="{{ asset('images/' . ($isDwg ? 'dwg-shadow.png' : 'pdf-view-shadow.png')) }}" class="file-icon icon-default">
                                    <img src="{{ asset('images/' . ($isDwg ? 'dwg.png' : 'pdf-view.png')) }}" class="file-icon icon-hover">
                                </div>
                                <div class="file-name alm-cursor-pointer" title="{{ $isDwg ? 'Descargar DWG' : 'Abrir PDF' }}" onclick="almacenVerPdf('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}')">
                                    {{ basename($otroArchivo['nombre']) }}
                                </div>
                                <div class="file-actions alm-flex-gap-5">
                                    <button type="button" class="btn-dibujos btn-dibujos-sm btn-ver" style="background-color: #b91c1c; color: white;" onclick="almacenVerPdf('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}')">{{ $isDwg ? 'Descargar' : 'Ver' }}</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Ayudas Visuales Rechazadas (Calidad) --}}
                @if (count($calidadAyudasClass) > 0)
                    <h4 style="margin-top: 15px; margin-bottom: 10px; color: #b91c1c; font-weight: 700;">Documentos Adjuntos de Calidad</h4>
                    <div class="alm-pdf-grid" style="background-color: {{ $containerBg }}; border: 1px solid #fca5a5; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                        @foreach ($calidadAyudasClass as $otroArchivo)
                            @php $isDwg = strtolower(pathinfo($otroArchivo['nombre'], PATHINFO_EXTENSION)) === 'dwg'; @endphp
<div class="dibujos-file-card card-ayuda" style="animation-delay: {{ $loop->index * 0.05 }}s; border-left-color: #dc2626;">
                                <div class="file-icon-wrapper alm-cursor-pointer" title="{{ $isDwg ? 'Descargar DWG' : 'Abrir PDF' }}">
                                    <img src="{{ asset('images/' . ($isDwg ? 'dwg-shadow.png' : 'pdf-view-shadow.png')) }}" class="file-icon icon-default">
                                    <img src="{{ asset('images/' . ($isDwg ? 'dwg.png' : 'pdf-view.png')) }}" class="file-icon icon-hover">
                                </div>
                                <div class="file-name alm-cursor-pointer" title="{{ $isDwg ? 'Descargar DWG' : 'Abrir PDF' }}" onclick="almacenVerPdf('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}')">
                                    {{ basename($otroArchivo['nombre']) }}
                                </div>
                                <div class="file-actions alm-flex-gap-5">
                                    <button type="button" class="btn-dibujos btn-dibujos-sm btn-ver" style="background-color: #b91c1c; color: white;" onclick="almacenVerPdf('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}')">{{ $isDwg ? 'Descargar' : 'Ver' }}</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Documentos de Rechazo / SCAR --}}
                @if (count($otrosClass) > 0)
                    <h4 style="margin-top: 15px; margin-bottom: 10px; color: #721c24; font-weight: 700;">Documentos de Rechazo</h4>
                    <div class="alm-pdf-grid" style="background-color: {{ $containerBg }}; border: 1px solid #fca5a5; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                        @foreach ($otrosClass as $otroArchivo)
                            @php $isDwg = strtolower(pathinfo($otroArchivo['nombre'], PATHINFO_EXTENSION)) === 'dwg'; @endphp
<div class="dibujos-file-card card-otro" style="animation-delay: {{ $loop->index * 0.05 }}s; border-left-color: #721c24;">
                                <div class="file-icon-wrapper alm-cursor-pointer" title="{{ $isDwg ? 'Descargar DWG' : 'Abrir PDF' }}">
                                    <img src="{{ asset('images/' . ($isDwg ? 'dwg-shadow.png' : 'pdf-view-shadow.png')) }}" class="file-icon icon-default">
                                    <img src="{{ asset('images/' . ($isDwg ? 'dwg.png' : 'pdf-view.png')) }}" class="file-icon icon-hover">
                                </div>
                                <div class="file-name alm-cursor-pointer" title="{{ $isDwg ? 'Descargar DWG' : 'Abrir PDF' }}" onclick="almacenVerPdf('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}')">
                                    {{ basename($otroArchivo['nombre']) }}
                                </div>
                                <div class="file-actions alm-flex-gap-5">
                                    <button type="button" class="btn-dibujos btn-dibujos-sm btn-ver" style="background-color: #b91c1c; color: white;" onclick="almacenVerPdf('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}')">{{ $isDwg ? 'Descargar' : 'Ver' }}</button>
                                    @php
                                        $canDeleteRechazado = false;
                                        $rUserPerfil = Auth::user()->perfil;
                                        $rAlertSent = in_array($targetReg->calidad_revision_status, ['calidad_aprobado', 'calidad_rechazado', 'calidad_mixto', 'calidad_parcial', 'casting_aprobado']);
                                        if (!$rAlertSent && ($rUserPerfil == 1 || $rUserPerfil == 2 || $rUserPerfil == 3 || $rUserPerfil == 4)) {
                                            $canDeleteRechazado = true;
                                        }
                                    @endphp
                                    @if ($canDeleteRechazado && !$isClaseRechazoProcesada)
                                        <button type="button" class="btn-dibujos btn-dibujos-sm btn-eliminar alm-bg-danger-white" onclick="almacenEliminarOtroArchivo('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}', this, '{{ $otroArchivo['origin'] ?? '' }}')">Eliminar</button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
                
                @if ($isClaseRechazoProcesada)
                        </div>
                    </details>
                @endif

                {{-- SECCIÓN INFORMATIVA O DE CONTROLES (DEPENDIENDO DEL ESTADO DE LA CLASE) --}}
                @if (true)
                    @if ($isClaseRechazoProcesada && strtolower($reprocesoClaseObj->ot) !== strtolower($reg->ot))
                        <div class="lib-calidad-card" style="margin-top: 20px; border: 2px solid #dc2626; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 14px rgba(220, 38, 38, 0.05);">
                            <div class="lib-calidad-card-header" style="background: #dc2626; display: flex; align-items: center; gap: 15px; padding: 12px 20px;">
                                <img src="{{ asset('images/Reproceso.png') }}" alt="Rechazados" style="width: 38px; height: 38px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));">
                                <div class="alm-overflow-hidden alm-flex-1">
                                    <span class="lib-calidad-card-title" style="color: white; font-weight: 700; font-size: 1.1rem; display: block;">Información &mdash; Rechazados ({{ ucfirst($claseRech) }})</span>
                                    <span class="lib-calidad-card-ot" style="color: #fecaca; font-size: 0.85rem;">{{ preg_replace('/_\d{8}_\d{6}_.*/', '', $reg->ot) }}</span>
                                </div>
                            </div>
                            <div class="lib-calidad-card-body" style="background: #fef2f2; padding: 20px;">
                                <div class="lib-calidad-action-row" style="display: flex; align-items: center; justify-content: space-between; gap: 20px; flex-wrap: wrap;">
                                    <div style="display: flex; align-items: center; gap: 20px;">
                                        <img src="{{ asset('images/Reproceso.png') }}" style="width: 54px; height: 54px; object-fit: contain;">
                                        <div>
                                            <h4 class="lib-calidad-card-prompt" style="color: #991b1b; margin-top: 0; margin-bottom: 8px; font-weight: 700; font-size: 1.1rem;">
                                                Rechazos Procesados Históricos
                                            </h4>
                                            <p style="color: #b91c1c; margin: 0; font-size: 0.95rem; font-weight: 500;">
                                                @if ($reprocesoClaseObj)
                                                    El reproceso se está trabajando en la nueva OT <strong>{{ preg_replace('/_\d{8}_\d{6}_.*/', '', $reprocesoClaseObj->ot) }}</strong>
                                                @else
                                                    Formatos de rechazo y SCAR subidos. Nueva pre-orden de modelo generada.
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    @if ($reprocesoClaseObj)
                                        <button class="btn-dibujos" 
                                            style="background: linear-gradient(135deg, #16a34a, #15803d); color: white; display: flex; align-items: center; gap: 6px; padding: 7px 14px; border-radius: 8px; border: none; cursor: pointer; font-weight: 700; font-size: 0.88rem; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 3px 10px rgba(22, 163, 74, 0.35);"
                                            onmouseover="this.style.transform='translateY(-2px) scale(1.02)'; this.style.boxShadow='0 5px 15px rgba(22, 163, 74, 0.45)';"
                                            onmouseout="this.style.transform='translateY(0) scale(1)'; this.style.boxShadow='0 3px 10px rgba(22, 163, 74, 0.35)';"
                                            onclick="const row = document.querySelector(`tr[data-ot='{{ $reprocesoClaseObj->ot }}']`); if(row) { row.scrollIntoView({behavior: 'smooth', block: 'center'}); row.animate([{ backgroundColor: '#86efac' }, { backgroundColor: 'transparent' }], { duration: 800, iterations: 3 }); } else { alert('La OT de reproceso se encuentra en otra página o filtro.'); }">
                                            Ir a <span style="color: #7dd3fc; font-weight: 800; text-shadow: 0 1px 2px rgba(0,0,0,0.3);">{{ preg_replace('/_\d{8}_\d{6}_.*/', '', $reprocesoClaseObj->ot) }}</span>
                                            <img src="{{ asset('images/redireccionar.png') }}" style="width: 16px; height: 16px; filter: brightness(0) invert(1);">
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="lib-calidad-card" id="control-almacen-rechazados-{{ md5($reg->ot . '_' . $claseRech) }}" style="margin-top: 20px;">
                            <div class="lib-calidad-card-header alm-background-linear-gradient-135deg-dc2626-b91c1c alm-border-bottom-2px-solid-rgba-220-38-38-0-5">
                                <img src="{{ asset('images/Reproceso.png') }}" alt="Reproceso" class="alm-icon-lg">
                                <div class="alm-overflow-hidden">
                                    <span class="lib-calidad-card-title alm-color-ffffff">Control de Modelos &mdash; Almacén (Rechazados)</span>
                                    <span class="lib-calidad-card-ot alm-color-fee2e2">{{ preg_replace('/_\d{8}_\d{6}_.*/', '', $reg->ot) }}</span>
                                </div>
                            </div>
                            <div class="lib-calidad-card-body">
                                <div class="lib-calidad-action-row">
                                    @php
                                        $hasRdmUploaded = false;
                                        $hasScarUploaded = false;
                                        foreach ($otrosClass as $f) {
                                            $n = strtolower(basename($f['nombre']));
                                            if (str_contains($n, 'rdm') || str_contains($n, 'rechazo')) $hasRdmUploaded = true;
                                            if (str_contains($n, 'scar')) $hasScarUploaded = true;
                                        }
                                        $rechazoProcesado = ($hasRdmUploaded && $hasScarUploaded);

                                        $reprocesoDirecto = null;
                                        if ($rechazoProcesado) {
                                            $baseOtCleanRep = preg_replace('/_.*_R\d+$|_R\d+$/i', '', $reg->ot);
                                            $allReps = \App\Models\FundicionHistory::where(function($q) use ($baseOtCleanRep) {
                                                $q->where('ot', 'LIKE', $baseOtCleanRep . '_%_R%')
                                                  ->orWhere('ot', 'LIKE', $baseOtCleanRep . '_R%');
                                            })->orderBy('id', 'desc')->get();
                                            foreach ($allReps as $rep) {
                                                $cfg = is_array($rep->ayudas_config) ? $rep->ayudas_config : [];
                                                
                                                // Normalize $cLow: e.g. "1 - moldes" -> "moldes"
                                                $baseCLow = trim(preg_replace('/^\d+\s*-\s*/', '', $cLow));
                                                $coreCLow = rtrim($baseCLow, 's'); // "molde"
                                                
                                                foreach ($cfg as $c) {
                                                    $cleanC = strtolower(trim($c));
                                                    $baseC = trim(preg_replace('/^\d+\s*-\s*/', '', $cleanC));
                                                    $coreC = rtrim($baseC, 's');
                                                    
                                                    if ($coreCLow === $coreC || str_contains($baseCLow, $coreC) || str_contains($baseC, $coreCLow)) {
                                                        $reprocesoDirecto = $rep;
                                                        break 2;
                                                    }
                                                }
                                                
                                                $otLow = strtolower($rep->ot);
                                                if (str_contains($otLow, '_' . $coreCLow) || str_contains($otLow, $coreCLow . '_r')) {
                                                    $reprocesoDirecto = $rep;
                                                    break;
                                                }
                                            }
                                            // Fallback: Si solo hay un reproceso, asumimos que es este
                                            if (!$reprocesoDirecto && $allReps->count() === 1) {
                                                $reprocesoDirecto = $allReps->first();
                                            }
                                        }
                                    @endphp

                                    @if ($rechazoProcesado)
                                        <div style="display: flex; flex-direction: column; gap: 12px; width: 100%;">
                                            <h4 class="lib-calidad-card-prompt" style="color: #047857; background: #ecfdf5; border-left: 4px solid #10b981; padding: 10px; border-radius: 4px; margin: 0;">
                                                <img src="{{ asset('images/Aprobado.png') }}" style="width: 18px; vertical-align: middle; margin-right: 6px;">
                                                Los formatos correspondientes de RDM y SCAR ya han sido subidos para <strong>{{ ucfirst($claseRech) }}</strong>.
                                            </h4>
                                            
                                            @if ($reprocesoDirecto)
                                                <div style="display: flex; align-items: center; justify-content: space-between; background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border: 2px solid #22c55e; border-radius: 12px; padding: 16px 20px; box-shadow: 0 8px 24px rgba(34, 197, 94, 0.15); transition: transform 0.3s ease; position: relative; overflow: hidden;"
                                                     onmouseover="this.style.transform='translateY(-1px)';" onmouseout="this.style.transform='translateY(0)';">
                                                    
                                                    <!-- Decorative background element -->
                                                    <div style="position: absolute; top: -15px; right: -15px; background: rgba(34, 197, 94, 0.1); width: 100px; height: 100px; border-radius: 50%; pointer-events: none;"></div>

                                                    <div style="display: flex; align-items: center; gap: 16px; position: relative; z-index: 1;">
                                                        <div style="background: white; padding: 10px; border-radius: 50%; box-shadow: 0 4px 12px rgba(0,0,0,0.06); display: flex; align-items: center; justify-content: center; border: 1px solid #bbf7d0;">
                                                            <img src="{{ asset('images/Reproceso.png') }}" style="width: 28px; height: 28px; filter: drop-shadow(0 2px 3px rgba(0,0,0,0.1));">
                                                        </div>
                                                        <div style="display: flex; flex-direction: column;">
                                                            <span style="color: #166534; font-weight: 800; font-size: 0.85em; text-transform: uppercase; letter-spacing: 0.8px; opacity: 0.85;">Reproceso Asignado</span>
                                                            <span style="color: #14532d; font-weight: 700; font-size: 1.15em; margin-top: 3px; font-family: 'Poppins', sans-serif; text-shadow: 0 1px 1px rgba(255,255,255,0.8);">
                                                                {{ preg_replace('/_\d{8}_\d{6}_.*/', '', $reprocesoDirecto->ot) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    
                                                    <button class="btn-modelo" 
                                                        style="position: relative; z-index: 1; background: linear-gradient(135deg, #22c55e 0%, #15803d 100%); border: none; padding: 10px 22px; border-radius: 8px; color: white; font-weight: 700; font-size: 0.95em; display: flex; align-items: center; gap: 10px; box-shadow: 0 6px 16px rgba(21, 128, 61, 0.35); animation: pulseGreenPremium 2.5s infinite cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);"
                                                        onmouseover="this.style.transform='translateY(-3px) scale(1.03)'; this.style.boxShadow='0 8px 20px rgba(21, 128, 61, 0.45)';"
                                                        onmouseout="this.style.transform='translateY(0) scale(1)'; this.style.boxShadow='0 6px 16px rgba(21, 128, 61, 0.35)';"
                                                        onclick="const row = document.querySelector(`tr[data-ot='{{ $reprocesoDirecto->ot }}']`); if(row) { row.scrollIntoView({behavior: 'smooth', block: 'center'}); row.animate([{ backgroundColor: '#bbf7d0' }, { backgroundColor: 'transparent' }], { duration: 800, iterations: 3 }); } else { alert('La OT de reproceso se encuentra en otra página o filtro.'); }">
                                                        <span style="text-shadow: 0 1px 2px rgba(0,0,0,0.2);">Ir a Nueva OT</span>
                                                        <img src="{{ asset('images/redireccionar.png') }}" style="width: 16px; height: 16px; filter: brightness(0) invert(1) drop-shadow(0 1px 2px rgba(0,0,0,0.3));">
                                                    </button>
                                                </div>
                                                <style>
                                                    @keyframes pulseGreenPremium {
                                                        0% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.6); }
                                                        50% { box-shadow: 0 0 0 14px rgba(34, 197, 94, 0); }
                                                        100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
                                                    }
                                                </style>
                                            @endif
                                        </div>
                                    @else
                                        <h4 class="lib-calidad-card-prompt">
                                            Modelo Rechazado por Calidad: <strong>{{ ucfirst($claseRech) }}</strong>. Procede a subir el Formato de Rechazo y el SCAR correspondiente.
                                        </h4>
                                        <div class="lib-calidad-card-btns">
                                            <button class="btn-modelo btn-modelo-no"
                                                onclick="abrirModalGestionVeredicto('{{ $reg->ot }}', [], [{{ json_encode($claseRech) }}])"
                                                class="alm-display-flex alm-background-color-b91c1c alm-color-white">
                                                <img src="{{ asset('images/Rechazado.png') }}" alt="No">
                                                <span>Procesar Rechazado ({{ ucfirst($claseRech) }})</span>
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                @endif
            </div>
        @endforeach
    </div>
@endif
