{{-- CONTENEDOR 3: MODELOS RECHAZADOS --}}
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
                $latestRechazoCalidad = \App\Models\LiberacionModeloFundicion::where(function($q) use ($reg, $targetReg, $baseOtCleanRep) {
                    $q->where('ot', '=', $reg->ot)
                      ->orWhere('ot', '=', $targetReg->ot)
                      ->orWhere('ot', 'LIKE', $baseOtCleanRep . '%');
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

                // 2. Buscar la OT de reproceso creada DESPUÉS del rechazo de Calidad para esta clase
                $reprocesoClaseObj = null;
                if ($latestRechazoCalidad) {
                    $reprocesoClaseObj = $allReprocesosOt->filter(function($hist) use ($cLow, $latestRechazoCalidad) {
                        if (strtotime($hist->created_at) < strtotime($latestRechazoCalidad->created_at)) {
                            return false;
                        }
                        $otLow = strtolower($hist->ot);
                        if (str_contains($otLow, '_' . $cLow . '_r') || str_contains($otLow, '_' . $cLow . 'r')) return true;
                        $cfg = is_array($hist->ayudas_config) ? $hist->ayudas_config : [];
                        foreach ($cfg as $c) {
                            if (strtolower(trim($c)) === $cLow) return true;
                        }
                        return false;
                    })->sortByDesc('id')->first();
                }

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

                {{-- Dibujos Originales Rechazados --}}
                @if (count($dibujosClass) > 0)
                    <h4 style="margin-top: 10px; margin-bottom: 10px; color: #b91c1c; font-weight: 700;">Dibujos de Fundición (Rechazados)</h4>
                    <div class="alm-pdf-grid" style="background-color: {{ $containerBg }}; border: 1px solid #fca5a5; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                        @foreach ($dibujosClass as $archivoInfo)
                            <div class="dibujos-file-card card-otro" style="animation-delay: {{ $loop->index * 0.05 }}s; border-left-color: #dc2626;">
                                <div class="file-icon-wrapper alm-cursor-pointer" title="Abrir PDF">
                                    <img src="{{ asset('images/pdf-view-shadow.png') }}" class="file-icon icon-default">
                                    <img src="{{ asset('images/pdf-view.png') }}" class="file-icon icon-hover">
                                </div>
                                <div class="file-name alm-cursor-pointer" title="Abrir PDF" onclick="almacenVerPdf('{{ $archivoInfo['ot'] }}', '{{ $archivoInfo['nombre'] }}', '{{ $archivoInfo['tipo'] }}')">
                                    {{ basename($archivoInfo['nombre']) }}
                                </div>
                                <div class="file-actions alm-flex-gap-5">
                                    <button class="btn-dibujos btn-dibujos-sm btn-ver" style="background-color: #b91c1c; color: white;" onclick="almacenVerPdf('{{ $archivoInfo['ot'] }}', '{{ $archivoInfo['nombre'] }}', '{{ $archivoInfo['tipo'] }}')">Ver</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Ayudas Visuales Originales Rechazadas --}}
                @if (count($ayudasClass) > 0)
                    <h4 style="margin-top: 15px; margin-bottom: 10px; color: #b91c1c; font-weight: 700;">Ayudas Visuales (Rechazadas)</h4>
                    <div class="alm-pdf-grid" style="background-color: {{ $containerBg }}; border: 1px solid #fca5a5; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                        @foreach ($ayudasClass as $archivoInfo)
                            @php $ayudaUrl = $archivoInfo['url'] ?? ''; @endphp
                            <div class="dibujos-file-card card-ayuda" style="animation-delay: {{ $loop->index * 0.05 }}s; border-left-color: #dc2626;">
                                <div class="file-icon-wrapper alm-cursor-pointer" title="Abrir PDF">
                                    <img src="{{ asset('images/pdf-view-shadow.png') }}" class="file-icon icon-default">
                                    <img src="{{ asset('images/pdf-view.png') }}" class="file-icon icon-hover">
                                </div>
                                <div class="file-name alm-cursor-pointer" title="Abrir PDF" onclick="almacenAbrirArchivo('{{ $ayudaUrl }}', '{{ $archivoInfo['ot'] }}', '{{ $archivoInfo['nombre'] }}', 'ayuda')">
                                    {{ basename($archivoInfo['nombre']) }}
                                </div>
                                <div class="file-actions alm-flex-gap-5">
                                    <button class="btn-dibujos btn-dibujos-sm btn-ver" style="background-color: #b91c1c; color: white;" onclick="almacenAbrirArchivo('{{ $ayudaUrl }}', '{{ $archivoInfo['ot'] }}', '{{ $archivoInfo['nombre'] }}', 'ayuda')">Ver</button>
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
                            <div class="dibujos-file-card card-otro" style="animation-delay: {{ $loop->index * 0.05 }}s; border-left-color: #dc2626;">
                                <div class="file-icon-wrapper alm-cursor-pointer" title="Abrir PDF">
                                    <img src="{{ asset('images/pdf-view-shadow.png') }}" class="file-icon icon-default">
                                    <img src="{{ asset('images/pdf-view.png') }}" class="file-icon icon-hover">
                                </div>
                                <div class="file-name alm-cursor-pointer" title="Abrir PDF" onclick="almacenVerPdf('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}')">
                                    {{ basename($otroArchivo['nombre']) }}
                                </div>
                                <div class="file-actions alm-flex-gap-5">
                                    <button class="btn-dibujos btn-dibujos-sm btn-ver" style="background-color: #b91c1c; color: white;" onclick="almacenVerPdf('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}')">Ver</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Ayudas Visuales Rechazadas (Calidad) --}}
                @if (count($calidadAyudasClass) > 0)
                    <h4 style="margin-top: 15px; margin-bottom: 10px; color: #b91c1c; font-weight: 700;">Documentos Adjuntos de Calidad (Ayudas Visuales)</h4>
                    <div class="alm-pdf-grid" style="background-color: {{ $containerBg }}; border: 1px solid #fca5a5; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                        @foreach ($calidadAyudasClass as $otroArchivo)
                            <div class="dibujos-file-card card-ayuda" style="animation-delay: {{ $loop->index * 0.05 }}s; border-left-color: #dc2626;">
                                <div class="file-icon-wrapper alm-cursor-pointer" title="Abrir PDF">
                                    <img src="{{ asset('images/pdf-view-shadow.png') }}" class="file-icon icon-default">
                                    <img src="{{ asset('images/pdf-view.png') }}" class="file-icon icon-hover">
                                </div>
                                <div class="file-name alm-cursor-pointer" title="Abrir PDF" onclick="almacenVerPdf('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}')">
                                    {{ basename($otroArchivo['nombre']) }}
                                </div>
                                <div class="file-actions alm-flex-gap-5">
                                    <button class="btn-dibujos btn-dibujos-sm btn-ver" style="background-color: #b91c1c; color: white;" onclick="almacenVerPdf('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}')">Ver</button>
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
                            <div class="dibujos-file-card card-otro" style="animation-delay: {{ $loop->index * 0.05 }}s; border-left-color: #721c24;">
                                <div class="file-icon-wrapper alm-cursor-pointer" title="Abrir PDF">
                                    <img src="{{ asset('images/pdf-view-shadow.png') }}" class="file-icon icon-default">
                                    <img src="{{ asset('images/pdf-view.png') }}" class="file-icon icon-hover">
                                </div>
                                <div class="file-name alm-cursor-pointer" title="Abrir PDF" onclick="almacenVerPdf('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}')">
                                    {{ basename($otroArchivo['nombre']) }}
                                </div>
                                <div class="file-actions alm-flex-gap-5">
                                    <button class="btn-dibujos btn-dibujos-sm btn-ver" style="background-color: #b91c1c; color: white;" onclick="almacenVerPdf('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}')">Ver</button>
                                    @php
                                        $canDeleteRechazado = false;
                                        $rUserPerfil = Auth::user()->perfil;
                                        $rAlertSent = in_array($targetReg->calidad_revision_status, ['calidad_aprobado', 'calidad_rechazado', 'calidad_mixto', 'calidad_parcial', 'casting_aprobado']);
                                        if (!$rAlertSent && ($rUserPerfil == 1 || $rUserPerfil == 2 || $rUserPerfil == 3 || $rUserPerfil == 4)) {
                                            $canDeleteRechazado = true;
                                        }
                                    @endphp
                                    @if ($canDeleteRechazado && !$isClaseRechazoProcesada)
                                        <button class="btn-dibujos btn-dibujos-sm btn-eliminar alm-bg-danger-white" onclick="almacenEliminarOtroArchivo('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}', this, '{{ $otroArchivo['origin'] ?? '' }}')">Eliminar</button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
                
                {{-- SECCIÓN INFORMATIVA O DE CONTROLES (DEPENDIENDO DEL ESTADO DE LA CLASE) --}}
                @if ($isClaseRechazoProcesada)
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
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
@endif
