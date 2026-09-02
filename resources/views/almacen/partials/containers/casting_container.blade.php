{{-- CONTENEDOR 2: PROCESO DE CASTING / MODELOS APROBADOS --}}
@if ($tieneAprobados)
    @php
        $castingPre = \App\Models\PreOrdenFundicion::where(function ($q) use ($reg, $targetReg) {
            $q->where('ot', '=', $reg->ot)->orWhere('ot', '=', $targetReg->ot);
        })
            ->where('pdf_filename', 'NOT LIKE', '%_Anterior_N%')
            ->where(function ($q) {
                $q->where('pdf_filename', 'LIKE', '%Casting%')
                    ->orWhere('pdf_filename', 'LIKE', '%F_ALM_PFC_%')
                    ->orWhere('pdf_filename', 'LIKE', '%PFC%');
            })->orderBy('id', 'desc')->first();

        $hasCastingPre = (bool) $castingPre || (count($almacenPreordenesCasting) > 0);

        $aprobadosPendientesCasting = [];
        $clasesAprobadasCubiertas = [];
        if (!empty($aprobados)) {
            $baseOtCleanCasting = preg_replace('/_R\d+$|_Anterior_\d+$/i', '', $targetReg->ot);
            $allCastingPres = \App\Models\PreOrdenFundicion::where(function ($q) use ($reg, $targetReg, $baseOtCleanCasting) {
                $q->where('ot', '=', $reg->ot)
                    ->orWhere('ot', '=', $targetReg->ot)
                    ->orWhere('ot', 'LIKE', $baseOtCleanCasting . '%');
            })
                ->where(function ($q) {
                    $q->where('pdf_filename', 'LIKE', '%Casting%')
                        ->orWhere('pdf_filename', 'LIKE', '%F_ALM_PFC_%')
                        ->orWhere('pdf_filename', 'LIKE', '%PFC%');
                })->get();

            $clasesPreordenCastingValidas = [];
            foreach ($allCastingPres as $cPo) {
                if ($cPo->is_sent != 1)
                    continue;
                $filasC = is_string($cPo->filas) ? json_decode($cPo->filas, true) : $cPo->filas;
                if (is_array($filasC)) {
                    foreach ($filasC as $fc) {
                        $cVal = strtolower($fc['clase'] ?? $fc['clase_nombre'] ?? $fc['tipo_modelo'] ?? $fc['nombre'] ?? '');
                        if (!empty($cVal)) {
                            $clasesPreordenCastingValidas[$cVal] = $cPo;
                        }
                    }
                }
                $fnLow = strtolower($cPo->pdf_filename ?? '');
                foreach ($aprobadosNorm as $apN) {
                    if ($apN !== '') {
                        $clasesPreordenCastingValidas[$apN] = $cPo;
                    }
                }
            }

            if (count($allCastingPres) === 0) {
                foreach ($almacenPreordenesCasting as $docCasting) {
                    $docNameLow = strtolower(basename($docCasting['nombre']));
                    if (str_contains($docNameLow, '_anterior_n'))
                        continue;
                    foreach ($aprobadosNorm as $apN) {
                        if ($apN !== '' && str_contains($docNameLow, $apN)) {
                            if (!isset($clasesPreordenCastingValidas[$apN])) {
                                $clasesPreordenCastingValidas[$apN] = true;
                            }
                        }
                    }
                }
            }

            foreach ($aprobados as $apClase) {
                $apLow = strtolower($apClase);
                $cubiertaC = false;
                $poAssoc = null;

                foreach ($clasesPreordenCastingValidas as $cKey => $poObj) {
                    if ($cKey === $apLow || str_contains($cKey, $apLow) || str_contains($apLow, $cKey)) {
                        $cubiertaC = true;
                        if (is_object($poObj)) {
                            $poAssoc = $poObj;
                        }
                        break;
                    }
                }

                if ($cubiertaC && $poAssoc && $poAssoc->is_sent != 1) {
                    $latestLdmClase = \App\Models\LiberacionModeloFundicion::where(function ($q) use ($reg, $targetReg, $baseOtCleanCasting) {
                        $q->where('ot', '=', $reg->ot)
                            ->orWhere('ot', '=', $targetReg->ot)
                            ->orWhere('ot', 'LIKE', $baseOtCleanCasting . '%');
                    })
                        ->where(function ($q) use ($apLow) {
                            $q->whereRaw("LOWER(tipo_modelo) = ?", [$apLow])
                                ->orWhereRaw("LOWER(tipo_modelo) LIKE ?", ['%' . $apLow . '%']);
                        })
                        ->orderBy('id', 'desc')
                        ->first();
                    if ($latestLdmClase && strtotime($latestLdmClase->created_at) > strtotime($poAssoc->updated_at ?: $poAssoc->created_at)) {
                        $cubiertaC = false;
                    }
                }

                if (!$cubiertaC) {
                    $aprobadosPendientesCasting[] = $apClase;
                } else {
                    $clasesAprobadasCubiertas[] = $apClase;
                }
            }
        }

        $clasesAccionCasting = !empty($aprobadosPendientesCasting) ? $aprobadosPendientesCasting : $aprobados;
        $castingEmailSent = ($castingPre && $castingPre->is_sent == 1 && count($aprobadosPendientesCasting) === 0);

        $tituloCasting = "Etapa: Proceso de Casting / Modelos Aprobados";
        if (count($aprobadosPendientesCasting) > 0 && count($clasesAprobadasCubiertas) > 0) {
            $tituloCasting .= " (Pendientes: " . implode(', ', array_map('ucfirst', $aprobadosPendientesCasting)) . " | Procesadas: " . implode(', ', array_map('ucfirst', $clasesAprobadasCubiertas)) . ")";
        } elseif (count($aprobadosPendientesCasting) > 0) {
            $tituloCasting .= " (Pendientes: " . implode(', ', array_map('ucfirst', $aprobadosPendientesCasting)) . ")";
        } elseif (count($clasesAprobadasCubiertas) > 0) {
            $tituloCasting .= " (" . implode(', ', array_map('ucfirst', $clasesAprobadasCubiertas)) . ")";
        }
    @endphp

    {{-- FUNCIONES AUXILIARES PARA FILTRAR ARCHIVOS POR CLASE --}}
    @php
        $filtrarArchivosCasting = function ($archivos, $clasesPermitidas) {
            $resultado = [];
            foreach ($archivos as $archivo) {
                $nombre = strtolower(basename($archivo['nombre']));
                $claseEnArchivo = strtolower($archivo['clase'] ?? '');

                $match = false;
                foreach ($clasesPermitidas as $cp) {
                    $cp = strtolower($cp);
                    if ($cp === '')
                        continue;

                    if (
                        $claseEnArchivo === $cp || strpos($claseEnArchivo, $cp) !== false ||
                        strpos($nombre, $cp) !== false ||
                        strpos($nombre, '_' . $cp . '_') !== false ||
                        strpos($nombre, '-' . $cp . '-') !== false
                    ) {
                        $match = true;
                        break;
                    }
                }

                if ($match) {
                    $resultado[] = $archivo;
                }
            }
            return $resultado;
        };

        // Filter arrays based on active (Pendientes) and processed (Cubiertas)
        $dibujosCastingPendientes = $filtrarArchivosCasting($dibujosCasting, $aprobadosPendientesCasting);
        $ayudasCastingPendientes = $filtrarArchivosCasting($ayudasCasting, $aprobadosPendientesCasting);

        $dibujosCastingProcesados = $filtrarArchivosCasting($dibujosCasting, $clasesAprobadasCubiertas);
        $ayudasCastingProcesadas = $filtrarArchivosCasting($ayudasCasting, $clasesAprobadasCubiertas);

        // For preordenes casting and other LDMs, there is already existing logic below filtering by $aprobadosNorm,
        // we'll apply our filter on top of those.
        $calidadAprobadosLdmCasting = array_values(array_filter($calidadAprobadosLdm, function ($doc) use ($aprobadosNorm, $rechazadosNorm) {
            $nameLow = strtolower(basename($doc['nombre']));
            if (!empty($rechazadosNorm)) {
                $mencionaRechazada = false;
                foreach ($rechazadosNorm as $rCl) {
                    if ($rCl !== '' && strpos($nameLow, $rCl) !== false) {
                        $mencionaRechazada = true;
                        break;
                    }
                }
                if ($mencionaRechazada) {
                    $mencionaAprobada = false;
                    foreach ($aprobadosNorm as $ap) {
                        if ($ap !== '' && strpos($nameLow, $ap) !== false) {
                            $mencionaAprobada = true;
                            break;
                        }
                    }
                    if (!$mencionaAprobada) {
                        return false;
                    }
                }
            }
            return true;
        }));

        $almacenPreordenesCastingBase = array_values(array_filter($almacenPreordenes, function ($doc) use ($aprobadosNorm) {
            $pathLow = strtolower($doc['nombre']);
            $nameLow = strtolower(basename($doc['nombre']));
            $isCastingDoc = (
                str_contains($pathLow, 'preorden_casting') ||
                str_contains($pathLow, 'casting') ||
                str_contains($nameLow, 'pfc') ||
                str_contains($nameLow, 'f_alm_pfc') ||
                str_contains($nameLow, 'efc') ||
                str_contains($nameLow, 'f_alm_efc')
            );
            if (!$isCastingDoc) {
                return false;
            }
            if (empty($aprobadosNorm)) {
                return true;
            }
            foreach ($aprobadosNorm as $ap) {
                if ($ap !== '' && strpos($nameLow, $ap) !== false) {
                    return true;
                }
            }
            return true;
        }));

        $ldmCastingPendientes = $filtrarArchivosCasting($calidadAprobadosLdmCasting, $aprobadosPendientesCasting);
        $preordenesCastingPendientes = $filtrarArchivosCasting($almacenPreordenesCastingBase, $aprobadosPendientesCasting);

        $ldmCastingProcesados = $filtrarArchivosCasting($calidadAprobadosLdmCasting, $clasesAprobadasCubiertas);
        $preordenesCastingProcesadas = $filtrarArchivosCasting($almacenPreordenesCastingBase, $clasesAprobadasCubiertas);

        // Fallbacks for files that couldn't be matched
        $idsDib = array_merge(array_column($dibujosCastingPendientes, 'nombre'), array_column($dibujosCastingProcesados, 'nombre'));
        foreach ($dibujosCasting as $a)
            if (!in_array($a['nombre'], $idsDib))
                $dibujosCastingPendientes[] = $a;

        $idsAyu = array_merge(array_column($ayudasCastingPendientes, 'nombre'), array_column($ayudasCastingProcesadas, 'nombre'));
        foreach ($ayudasCasting as $a)
            if (!in_array($a['nombre'], $idsAyu))
                $ayudasCastingPendientes[] = $a;

        $idsLdm = array_merge(array_column($ldmCastingPendientes, 'nombre'), array_column($ldmCastingProcesados, 'nombre'));
        foreach ($calidadAprobadosLdmCasting as $a)
            if (!in_array($a['nombre'], $idsLdm))
                $ldmCastingPendientes[] = $a;

        $idsPo = array_merge(array_column($preordenesCastingPendientes, 'nombre'), array_column($preordenesCastingProcesadas, 'nombre'));
        foreach ($almacenPreordenesCastingBase as $a)
            if (!in_array($a['nombre'], $idsPo))
                $preordenesCastingPendientes[] = $a;

        $clasesAprobadasParaCasting = !empty($aprobadosPendientesCasting) ? $aprobadosPendientesCasting : $aprobados;
        $hasLdmSubidoCasting = (bool) ($targetReg->casting_pdf_generated ?? false) || (bool) ($reg->casting_pdf_generated ?? false);
    @endphp

    <div class="alm-process-block"
        style="margin-bottom: 25px; padding: 20px; border-radius: 14px; background-color: #f0fdf4; border: 2px solid #16a34a; box-shadow: 0 4px 14px rgba(22, 163, 74, 0.08);">
        <div
            style="display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #16a34a; padding-bottom: 10px; margin-bottom: 15px;">
            <h3
                style="margin: 0; color: #16a34a; font-size: 1.15rem; font-weight: 700; display: flex; align-items: center; gap: 10px;">
                <img src="{{ asset('images/Aprobado.png') }}" style="width: 30px; height: 30px; object-fit: contain;">
                {{ $tituloCasting }}
            </h3>
            <span
                style="font-size: 0.8rem; font-weight: 700; background: #dcfce7; color: #15803d; padding: 4px 12px; border-radius: 6px; border: 1px solid #bbf7d0;">
                CASTING / APROBADOS
            </span>
        </div>

        {{-- ================================================================= --}}
        {{-- SECCIÓN ACTIVA (CLASES PENDIENTES DE CASTING) --}}
        {{-- ================================================================= --}}
        @if(count($aprobadosPendientesCasting) > 0 || (count($aprobadosPendientesCasting) == 0 && count($clasesAprobadasCubiertas) == 0))
            <div class="cal-subcontainer-almacen"
                style="margin-bottom: 25px; padding: 18px; border-radius: 12px; background-color: #f0fdf4; border: 2px solid #16a34a; box-shadow: 0 3px 10px rgba(22, 163, 74, 0.08);">
                <div
                    style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1.5px solid #bbf7d0; padding-bottom: 8px; margin-bottom: 15px;">
                    <h4
                        style="margin: 0; color: #15803d; font-size: 1.05rem; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                        <img src="{{ asset('images/almacen.png') }}" style="width: 22px; height: 22px; object-fit: contain;">
                        Procesos Activos ({{ implode(', ', array_map('ucfirst', $aprobadosPendientesCasting)) }})
                    </h4>
                </div>

                {{-- Dibujos de Casting --}}
                @if (count($dibujosCastingPendientes) > 0)
                    <h4 style="margin-top: 10px; margin-bottom: 10px; color: #15803d; font-weight: 700;">Dibujos de Fundición</h4>
                    <div class="alm-pdf-grid"
                        style="background-color: #dcfce7; border: 1px solid #bbf7d0; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                        @foreach ($dibujosCastingPendientes as $archivoInfo)
                            @php $isDwg = strtolower(pathinfo($archivoInfo['nombre'], PATHINFO_EXTENSION)) === 'dwg'; @endphp
                            <div class="dibujos-file-card"
                                style="animation-delay: {{ $loop->index * 0.05 }}s; border-left-color: #16a34a;">
                                <div class="file-icon-wrapper alm-cursor-pointer" title="{{ $isDwg ? 'Descargar DWG' : 'Abrir PDF' }}">
                                    <img src="{{ asset('images/' . ($isDwg ? 'dwg-shadow.png' : 'pdf-view-shadow.png')) }}"
                                        class="file-icon icon-default">
                                    <img src="{{ asset('images/' . ($isDwg ? 'dwg.png' : 'pdf-view.png')) }}"
                                        class="file-icon icon-hover">
                                </div>
                                <div class="file-name alm-cursor-pointer" title="{{ $isDwg ? 'Descargar DWG' : 'Abrir PDF' }}"
                                    onclick="almacenVerPdf('{{ $archivoInfo['ot'] }}', '{{ $archivoInfo['nombre'] }}', '{{ $archivoInfo['tipo'] }}')">
                                    {{ basename($archivoInfo['nombre']) }}
                                </div>
                                <div class="file-actions">
                                    <button class="btn-dibujos btn-dibujos-sm btn-ver alm-background-color-15803d alm-color-white"
                                        onclick="almacenVerPdf('{{ $archivoInfo['ot'] }}', '{{ $archivoInfo['nombre'] }}', '{{ $archivoInfo['tipo'] }}')">{{ $isDwg ? 'Descargar' : 'Ver' }}</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Ayudas Visuales de Casting --}}
                @if (count($ayudasCastingPendientes) > 0)
                    <h4 style="margin-top: 15px; margin-bottom: 10px; color: #15803d; font-weight: 700;">Ayudas Visuales</h4>
                    <div class="alm-pdf-grid"
                        style="background-color: #dcfce7; border: 1px solid #bbf7d0; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                        @foreach ($ayudasCastingPendientes as $archivoInfo)
                            @php $ayudaUrl = $archivoInfo['url'] ?? ''; @endphp
                            @php $isDwg = strtolower(pathinfo($archivoInfo['nombre'], PATHINFO_EXTENSION)) === 'dwg'; @endphp
                            <div class="dibujos-file-card card-ayuda"
                                style="animation-delay: {{ $loop->index * 0.05 }}s; border-left-color: #16a34a;">
                                <div class="file-icon-wrapper alm-cursor-pointer" title="{{ $isDwg ? 'Descargar DWG' : 'Abrir PDF' }}">
                                    <img src="{{ asset('images/' . ($isDwg ? 'dwg-shadow.png' : 'pdf-view-shadow.png')) }}"
                                        class="file-icon icon-default">
                                    <img src="{{ asset('images/' . ($isDwg ? 'dwg.png' : 'pdf-view.png')) }}"
                                        class="file-icon icon-hover">
                                </div>
                                <div class="file-name alm-cursor-pointer" title="{{ $isDwg ? 'Descargar DWG' : 'Abrir PDF' }}"
                                    onclick="almacenAbrirArchivo('{{ $ayudaUrl }}', '{{ $archivoInfo['ot'] }}', '{{ $archivoInfo['nombre'] }}', 'ayuda')">
                                    {{ basename($archivoInfo['nombre']) }}
                                </div>
                                <div class="file-actions">
                                    <button class="btn-dibujos btn-dibujos-sm btn-ver alm-background-color-15803d alm-color-white"
                                        onclick="almacenAbrirArchivo('{{ $ayudaUrl }}', '{{ $archivoInfo['ot'] }}', '{{ $archivoInfo['nombre'] }}', 'ayuda')">{{ $isDwg ? 'Descargar' : 'Ver' }}</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Documentos Aprobados LDM --}}
                @if (count($ldmCastingPendientes) > 0)
                    <h4 style="margin-top: 15px; margin-bottom: 10px; color: #155724; font-weight: 700;">Documentos Aprobados</h4>
                    <div class="alm-pdf-grid"
                        style="background-color: #dcfce7; border: 1px solid #bbf7d0; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                        @foreach ($ldmCastingPendientes as $otroArchivo)
                            @php
                                $canDelete = false;
                                $fileOwner = $otroArchivo['owner'] ?? '';
                                $fileNameLower = strtolower($otroArchivo['nombre']);
                                if (strpos($fileNameLower, 'f-ccl-ldm') !== false || strpos($fileNameLower, 'scar') !== false) {
                                    $fileOwner = 'calidad';
                                }
                                $userPerfil = Auth::user()->perfil;
                                $alertSent = false;
                                if ($fileOwner === 'almacen') {
                                    $alertSent = (bool) ($targetReg->pre_orden_email_sent || $targetReg->pre_orden_sent);
                                } elseif ($fileOwner === 'calidad') {
                                    $alertSent = in_array($targetReg->calidad_revision_status, ['calidad_aprobado', 'calidad_rechazado', 'calidad_mixto', 'calidad_parcial', 'casting_aprobado']);
                                }
                                if (!$alertSent) {
                                    if ($userPerfil == 1 || $userPerfil == 2 || $userPerfil == 3) {
                                        $canDelete = true;
                                    } elseif ($userPerfil == 5 && $fileOwner === 'almacen') {
                                        $canDelete = true;
                                    } elseif (($userPerfil == 4 || $userPerfil == 3) && $fileOwner === 'calidad') {
                                        $canDelete = true;
                                    }
                                }
                            @endphp
                            @php $isDwg = strtolower(pathinfo($otroArchivo['nombre'], PATHINFO_EXTENSION)) === 'dwg'; @endphp
                            <div class="dibujos-file-card card-otro"
                                style="animation-delay: {{ $loop->index * 0.05 }}s; border-left-color: #155724;">
                                <div class="file-icon-wrapper alm-cursor-pointer" title="{{ $isDwg ? 'Descargar DWG' : 'Abrir PDF' }}">
                                    <img src="{{ asset('images/' . ($isDwg ? 'dwg-shadow.png' : 'pdf-view-shadow.png')) }}"
                                        class="file-icon icon-default">
                                    <img src="{{ asset('images/' . ($isDwg ? 'dwg.png' : 'pdf-view.png')) }}"
                                        class="file-icon icon-hover">
                                </div>
                                <div class="file-name alm-cursor-pointer" title="{{ $isDwg ? 'Descargar DWG' : 'Abrir PDF' }}"
                                    onclick="almacenVerPdf('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}')">
                                    {{ basename($otroArchivo['nombre']) }}
                                </div>
                                <div class="file-actions alm-flex-gap-5">
                                    <button class="btn-dibujos btn-dibujos-sm btn-ver alm-background-color-155724 alm-color-white"
                                        onclick="almacenVerPdf('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}')">{{ $isDwg ? 'Descargar' : 'Ver' }}</button>
                                    @if ($canDelete)
                                        <button class="btn-dibujos btn-dibujos-sm btn-eliminar alm-bg-danger-white"
                                            onclick="almacenEliminarOtroArchivo('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}', this, '{{ $otroArchivo['origin'] ?? '' }}')">Eliminar</button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Pre-órdenes de Modelo --}}
                @if (count($preordenesCastingPendientes) > 0)
                    <h4 style="margin-top: 15px; margin-bottom: 10px; color: #15803d; font-weight: 700;">Pre-órdenes de Casting</h4>
                    <div class="alm-pdf-grid"
                        style="background-color: #dcfce7; border: 1px solid #bbf7d0; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                        @foreach ($preordenesCastingPendientes as $archivoInfo)
                            @php $isDwg = strtolower(pathinfo($archivoInfo['nombre'], PATHINFO_EXTENSION)) === 'dwg'; @endphp
                            <div class="dibujos-file-card"
                                style="animation-delay: {{ $loop->index * 0.05 }}s; border-left-color: #16a34a;">
                                <div class="file-icon-wrapper alm-cursor-pointer" title="{{ $isDwg ? 'Descargar DWG' : 'Abrir PDF' }}">
                                    <img src="{{ asset('images/' . ($isDwg ? 'dwg-shadow.png' : 'pdf-view-shadow.png')) }}"
                                        class="file-icon icon-default">
                                    <img src="{{ asset('images/' . ($isDwg ? 'dwg.png' : 'pdf-view.png')) }}"
                                        class="file-icon icon-hover">
                                </div>
                                <div class="file-name alm-cursor-pointer" title="{{ $isDwg ? 'Descargar DWG' : 'Abrir PDF' }}"
                                    onclick="almacenVerPdf('{{ $archivoInfo['ot'] }}', '{{ $archivoInfo['nombre'] }}', 'preorden')">
                                    {{ basename($archivoInfo['nombre']) }}
                                </div>
                                <div class="file-actions">
                                    <button class="btn-dibujos btn-dibujos-sm btn-ver alm-background-color-15803d alm-color-white"
                                        onclick="almacenVerPdf('{{ $archivoInfo['ot'] }}', '{{ $archivoInfo['nombre'] }}', 'preorden')">{{ $isDwg ? 'Descargar' : 'Ver' }}</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- TARJETA DE CONTROLES CASTING --}}
                <div class="lib-calidad-card" id="control-almacen-aprobados-{{ md5($reg->ot) }}" style="margin-top: 20px;">
                    <div
                        class="lib-calidad-card-header alm-background-linear-gradient-135deg-16a34a-15803d alm-border-bottom-2px-solid-rgba-22-163-74-0-5">
                        <img src="{{ asset('images/almacen.png') }}" alt="Almacén" class="alm-icon-lg">
                        <div class="alm-overflow-hidden">
                            <span class="lib-calidad-card-title alm-color-ffffff">Control de Modelos &mdash; Almacén
                                (Aprobados)</span>
                            <span
                                class="lib-calidad-card-ot alm-color-d1fae5">{{ preg_replace('/_\d{8}_\d{6}_.*/', '', $reg->ot) }}</span>
                        </div>
                    </div>
                    <div class="lib-calidad-card-body">
                        <div class="lib-calidad-action-row">
                            <h4 class="lib-calidad-card-prompt">
                                @if ($hasCastingPre)
                                    Pre-orden de casting generada
                                    {!! count($aprobados) > 0 ? 'para los modelos: <strong>' . e(implode(', ', array_map('ucfirst', $aprobados))) . '</strong>' : '' !!}.
                                    Puedes editar los datos o enviar la pre-orden por correo.
                                @elseif ($hasLdmSubidoCasting)
                                    Formatos LDM subidos
                                    {!! count($aprobados) > 0 ? 'para los modelos: <strong>' . e(implode(', ', array_map('ucfirst', $aprobados))) . '</strong>' : '' !!}.
                                    Procede a generar la Pre-Orden de Fabricación de Casting (PFC).
                                @else
                                    Modelos Aprobados por
                                    Calidad{!! !empty($aprobadosPendientesCasting) ? ': <strong>' . e(implode(', ', array_map('ucfirst', $aprobadosPendientesCasting))) . '</strong>' : (count($aprobados) > 0 ? ': <strong>' . e(implode(', ', array_map('ucfirst', $aprobados))) . '</strong>' : '') !!}.
                                    Procede a subir los formatos F-CCL-LDM firmados para iniciar el casting.
                                @endif
                            </h4>

                            <div class="lib-calidad-card-btns">
                                {{-- Paso 1: Subir LDMs (solo cuando aún no se han subido por Almacén para las clases aprobadas
                                y no hay pre-orden) --}}
                                <button class="btn-modelo btn-modelo-si"
                                    onclick="abrirModalGestionVeredicto('{{ $targetReg->ot }}', {{ json_encode($aprobadosPendientesCasting ?: $aprobados) }}, [])"
                                    title="Subir los formatos F-CCL-LDM firmados para iniciar el casting"
                                    style="{{ ($hasCastingPre || $hasLdmSubidoCasting) ? 'display: none;' : '' }}">
                                    <img src="{{ asset('images/Aprobado.png') }}" alt="Aprobado">
                                    <span>Procesar Aceptados
                                        ({{ implode(', ', array_map('ucfirst', $aprobadosPendientesCasting ?: $aprobados)) }})</span>
                                </button>

                                {{-- Paso 2: Generar Pre-Orden PFC (solo cuando los LDMs ya se subieron en Almacén y no hay
                                pre-orden) --}}
                                <button class="btn-modelo btn-modelo-casting"
                                    onclick="abrirModalPreOrdenCasting('{{ $targetReg->ot }}')"
                                    title="Generar la pre-orden de fabricación de Casting (PFC)"
                                    style="{{ ($hasCastingPre || !$hasLdmSubidoCasting) ? 'display: none;' : '' }}">
                                    <img src="{{ asset('images/pdf-view.png') }}" alt="Pre-Orden">
                                    <span>Generar Pre-Orden PFC</span>
                                </button>

                                {{-- Paso 3a: Editar pre-orden (cuando ya existe) --}}
                                <button class="btn-modelo btn-modelo-edit"
                                    onclick="abrirModalPreOrdenCasting('{{ $targetReg->ot }}')"
                                    title="Editar información de la preorden existente"
                                    style="{{ (!$hasCastingPre) ? 'display: none;' : '' }}">
                                    <img src="{{ asset('images/editar-informacion.png') }}" alt="Editar">
                                    <span>Editar PFC</span>
                                </button>

                                {{-- Paso 3b: Enviar correo (cuando ya existe pre-orden) --}}
                                <button class="btn-modelo btn-modelo-email"
                                    onclick="abrirModalEnviarPreOrden('{{ $targetReg->ot }}', 'casting', {{ json_encode($clasesAccionCasting) }})"
                                    title="Enviar pre-orden por correo electrónico"
                                    style="{{ (!$hasCastingPre) ? 'display: none;' : '' }}">
                                    <img src="{{ asset('images/enviando.png') }}" alt="Enviar">
                                    <span>Enviar Correo</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- ================================================================= --}}
        {{-- SECCIÓN INFORMATIVA (CLASES YA PROCESADAS EN CASTING) --}}
        {{-- ================================================================= --}}
        @if (count($clasesAprobadasCubiertas) > 0)
            <div class="cal-subcontainer-almacen"
                style="margin-bottom: 25px; padding: 18px; border-radius: 12px; background-color: #dcfce7; border: 2px solid #16a34a; box-shadow: 0 3px 10px rgba(22, 163, 74, 0.08);">
                <div
                    style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1.5px solid #bbf7d0; padding-bottom: 8px; margin-bottom: 15px;">
                    <h4
                        style="margin: 0; color: #15803d; font-size: 1.05rem; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                        <img src="{{ asset('images/Aprobado.png') }}" style="width: 22px; height: 22px; object-fit: contain;">
                        Clases Procesadas ({{ implode(', ', array_map('ucfirst', $clasesAprobadasCubiertas)) }})
                    </h4>
                    <span
                        style="font-size: 0.75rem; font-weight: 700; background: #e0f2fe; color: #0369a1; padding: 3px 10px; border-radius: 6px; border: 1px solid #7dd3fc;">
                        PRE-ORDEN ENVIADA A PROVEEDOR
                    </span>
                </div>

                {{-- Dibujos Procesados --}}
                @if (count($dibujosCastingProcesados) > 0)
                    <h4 style="margin-top: 10px; margin-bottom: 10px; color: #15803d; font-weight: 700;">Dibujos de Fundición</h4>
                    <div class="alm-pdf-grid"
                        style="background-color: #dcfce7; border: 1px solid #bbf7d0; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                        @foreach ($dibujosCastingProcesados as $archivoInfo)
                            @php $isDwg = strtolower(pathinfo($archivoInfo['nombre'], PATHINFO_EXTENSION)) === 'dwg'; @endphp
                            <div class="dibujos-file-card"
                                style="animation-delay: {{ $loop->index * 0.05 }}s; border-left-color: #15803d;">
                                <div class="file-icon-wrapper alm-cursor-pointer" title="{{ $isDwg ? 'Descargar DWG' : 'Abrir PDF' }}">
                                    <img src="{{ asset('images/' . ($isDwg ? 'dwg-shadow.png' : 'pdf-view-shadow.png')) }}"
                                        class="file-icon icon-default">
                                    <img src="{{ asset('images/' . ($isDwg ? 'dwg.png' : 'pdf-view.png')) }}"
                                        class="file-icon icon-hover">
                                </div>
                                <div class="file-name alm-cursor-pointer" title="{{ $isDwg ? 'Descargar DWG' : 'Abrir PDF' }}"
                                    onclick="almacenVerPdf('{{ $archivoInfo['ot'] }}', '{{ $archivoInfo['nombre'] }}', '{{ $archivoInfo['tipo'] }}')">
                                    {{ basename($archivoInfo['nombre']) }}
                                </div>
                                <div class="file-actions">
                                    <button class="btn-dibujos btn-dibujos-sm btn-ver alm-background-color-15803d alm-color-white"
                                        onclick="almacenVerPdf('{{ $archivoInfo['ot'] }}', '{{ $archivoInfo['nombre'] }}', '{{ $archivoInfo['tipo'] }}')">{{ $isDwg ? 'Descargar' : 'Ver' }}</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Ayudas Visuales Procesadas --}}
                @if (count($ayudasCastingProcesadas) > 0)
                    <h4 style="margin-top: 15px; margin-bottom: 10px; color: #15803d; font-weight: 700;">Ayudas Visuales</h4>
                    <div class="alm-pdf-grid"
                        style="background-color: #dcfce7; border: 1px solid #bbf7d0; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                        @foreach ($ayudasCastingProcesadas as $archivoInfo)
                            @php $ayudaUrl = $archivoInfo['url'] ?? ''; @endphp
                            @php $isDwg = strtolower(pathinfo($archivoInfo['nombre'], PATHINFO_EXTENSION)) === 'dwg'; @endphp
                            <div class="dibujos-file-card card-ayuda"
                                style="animation-delay: {{ $loop->index * 0.05 }}s; border-left-color: #15803d;">
                                <div class="file-icon-wrapper alm-cursor-pointer" title="{{ $isDwg ? 'Descargar DWG' : 'Abrir PDF' }}">
                                    <img src="{{ asset('images/' . ($isDwg ? 'dwg-shadow.png' : 'pdf-view-shadow.png')) }}"
                                        class="file-icon icon-default">
                                    <img src="{{ asset('images/' . ($isDwg ? 'dwg.png' : 'pdf-view.png')) }}"
                                        class="file-icon icon-hover">
                                </div>
                                <div class="file-name alm-cursor-pointer" title="{{ $isDwg ? 'Descargar DWG' : 'Abrir PDF' }}"
                                    onclick="almacenAbrirArchivo('{{ $ayudaUrl }}', '{{ $archivoInfo['ot'] }}', '{{ $archivoInfo['nombre'] }}', 'ayuda')">
                                    {{ basename($archivoInfo['nombre']) }}
                                </div>
                                <div class="file-actions">
                                    <button class="btn-dibujos btn-dibujos-sm btn-ver alm-background-color-15803d alm-color-white"
                                        onclick="almacenAbrirArchivo('{{ $ayudaUrl }}', '{{ $archivoInfo['ot'] }}', '{{ $archivoInfo['nombre'] }}', 'ayuda')">{{ $isDwg ? 'Descargar' : 'Ver' }}</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- LDM Procesados --}}
                @if (count($ldmCastingProcesados) > 0)
                    <h4 style="margin-top: 15px; margin-bottom: 10px; color: #15803d; font-weight: 700;">Documentos Aprobados</h4>
                    <div class="alm-pdf-grid"
                        style="background-color: #dcfce7; border: 1px solid #bbf7d0; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                        @foreach ($ldmCastingProcesados as $otroArchivo)
                            @php $isDwg = strtolower(pathinfo($otroArchivo['nombre'], PATHINFO_EXTENSION)) === 'dwg'; @endphp
                            <div class="dibujos-file-card card-otro"
                                style="animation-delay: {{ $loop->index * 0.05 }}s; border-left-color: #15803d;">
                                <div class="file-icon-wrapper alm-cursor-pointer" title="{{ $isDwg ? 'Descargar DWG' : 'Abrir PDF' }}">
                                    <img src="{{ asset('images/' . ($isDwg ? 'dwg-shadow.png' : 'pdf-view-shadow.png')) }}"
                                        class="file-icon icon-default">
                                    <img src="{{ asset('images/' . ($isDwg ? 'dwg.png' : 'pdf-view.png')) }}"
                                        class="file-icon icon-hover">
                                </div>
                                <div class="file-name alm-cursor-pointer" title="{{ $isDwg ? 'Descargar DWG' : 'Abrir PDF' }}"
                                    onclick="almacenVerPdf('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}')">
                                    {{ basename($otroArchivo['nombre']) }}
                                </div>
                                <div class="file-actions alm-flex-gap-5">
                                    <button class="btn-dibujos btn-dibujos-sm btn-ver alm-background-color-15803d alm-color-white"
                                        onclick="almacenVerPdf('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}')">{{ $isDwg ? 'Descargar' : 'Ver' }}</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Preordenes Procesadas --}}
                @if (count($preordenesCastingProcesadas) > 0)
                    <h4 style="margin-top: 15px; margin-bottom: 10px; color: #15803d; font-weight: 700;">Pre-órdenes de Casting</h4>
                    <div class="alm-pdf-grid"
                        style="background-color: #dcfce7; border: 1px solid #bbf7d0; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                        @foreach ($preordenesCastingProcesadas as $archivoInfo)
                            @php $isDwg = strtolower(pathinfo($archivoInfo['nombre'], PATHINFO_EXTENSION)) === 'dwg'; @endphp
                            <div class="dibujos-file-card"
                                style="animation-delay: {{ $loop->index * 0.05 }}s; border-left-color: #15803d;">
                                <div class="file-icon-wrapper alm-cursor-pointer" title="{{ $isDwg ? 'Descargar DWG' : 'Abrir PDF' }}">
                                    <img src="{{ asset('images/' . ($isDwg ? 'dwg-shadow.png' : 'pdf-view-shadow.png')) }}"
                                        class="file-icon icon-default">
                                    <img src="{{ asset('images/' . ($isDwg ? 'dwg.png' : 'pdf-view.png')) }}"
                                        class="file-icon icon-hover">
                                </div>
                                <div class="file-name alm-cursor-pointer" title="{{ $isDwg ? 'Descargar DWG' : 'Abrir PDF' }}"
                                    onclick="almacenVerPdf('{{ $archivoInfo['ot'] }}', '{{ $archivoInfo['nombre'] }}', 'preorden')">
                                    {{ basename($archivoInfo['nombre']) }}
                                </div>
                                <div class="file-actions">
                                    <button class="btn-dibujos btn-dibujos-sm btn-ver alm-background-color-15803d alm-color-white"
                                        onclick="almacenVerPdf('{{ $archivoInfo['ot'] }}', '{{ $archivoInfo['nombre'] }}', 'preorden')">{{ $isDwg ? 'Descargar' : 'Ver' }}</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="lib-calidad-card"
                    style="margin-top: 20px; border: 2px solid #16a34a; border-radius: 12px; overflow: hidden;">
                    <div class="lib-calidad-card-header"
                        style="background: #16a34a; display: flex; align-items: center; gap: 15px; padding: 12px 20px;">
                        <img src="{{ asset('images/Aprobado.png') }}" alt="Casting"
                            style="width: 38px; height: 38px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));">
                        <div class="alm-overflow-hidden alm-flex-1">
                            <span class="lib-calidad-card-title"
                                style="color: white; font-weight: 700; font-size: 1.1rem; display: block;">Información —
                                Casting</span>
                            <span class="lib-calidad-card-ot"
                                style="color: #dcfce7; font-size: 0.85rem;">{{ preg_replace('/_\\d{8}_\\d{6}_.*/', '', $reg->ot) }}</span>
                        </div>
                    </div>
                    <div class="lib-calidad-card-body" style="background: #f0fdf4; padding: 20px;">
                        <div class="lib-calidad-action-row" style="display: flex; align-items: center; gap: 20px;">
                            <img src="{{ asset('images/enviando.png') }}" style="width: 54px; height: 54px;">
                            <div>
                                <h4 class="lib-calidad-card-prompt"
                                    style="color: #15803d; margin-top: 0; margin-bottom: 8px; font-weight: 700; font-size: 1.1rem;">
                                    Clases Procesadas en Casting
                                </h4>
                                <p style="color: #166534; margin: 0; font-size: 0.95rem; font-weight: 500;">
                                    El proceso de pre-orden ha finalizado. El correo ha sido enviado al proveedor. Favor de
                                    esperar instrucciones.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    </div>
@endif
