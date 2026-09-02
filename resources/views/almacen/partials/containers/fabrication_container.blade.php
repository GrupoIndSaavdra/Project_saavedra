{{-- CONTENEDOR 1: FABRICACIÓN / RE-PROCESO DE MODELO --}}
@if ($tieneFabricacion)
    @php
        $esReproceso = (bool) preg_match('/_R\d+$/i', $targetReg->ot);
        $previousOtForRechazo = $targetReg->ot;
        if ($esReproceso) {
            preg_match('/_R(\d+)$/i', $targetReg->ot, $matches);
            $rNum = (int) ($matches[1] ?? 1);
            if ($rNum > 1) {
                $previousOtForRechazo = preg_replace('/_R\d+$/i', '_R' . ($rNum - 1), $targetReg->ot);
            } else {
                $previousOtForRechazo = preg_replace('/_.*_R\d+$|_R\d+$/i', '', $targetReg->ot);
            }
        }
        $rechazadosRaw = \App\Models\LiberacionModeloFundicion::where('ot', $previousOtForRechazo)
            ->where('decision', 'rechazar')
            ->pluck('tipo_modelo')
            ->unique()
            ->filter(fn($v) => !empty($v))
            ->values()
            ->toArray();

        $rechazadosClases = [];
        foreach ($rechazadosRaw as $r) {
            foreach (explode(',', $r) as $c) {
                if (!empty(trim($c))) {
                    $rechazadosClases[] = trim($c);
                }
            }
        }
        $rechazadosClases = array_unique($rechazadosClases);

        $hasRechazosReal = (count($rechazadosClases) > 0 || $tieneRechazados);
        $esReinicioParcial = $isCalidadAlerted && count($clasesFabricacion) > 0 && !$esReproceso && $hasRechazosReal;

        if ($esReinicioParcial) {
            $otClasesActivas = array_map('strtolower', $clasesFabricacion);
        } elseif ($esReproceso) {
            $otClasesActivas = array_map('strtolower', $rechazadosClases);
        } else {
            $otClasesActivas = !empty($clasesFabricacion)
                ? array_map('strtolower', $clasesFabricacion)
                : (is_array($reg->ayudas_config) ? array_map('strtolower', $reg->ayudas_config) : []);
        }

        $preOrdenesFabExistentes = \App\Models\PreOrdenFundicion::where('ot', $targetReg->ot)->get()->filter(function ($po) use ($clasesFabricacion) {
            if ($po->pdf_filename && (str_contains($po->pdf_filename, '_Anterior_N') || str_contains($po->pdf_filename, 'Casting') || str_contains($po->pdf_filename, 'F_ALM_PFC_') || str_contains($po->pdf_filename, 'PFC')))
                return false;
            $filas = is_string($po->filas) ? json_decode($po->filas, true) : $po->filas;
            if (!is_array($filas))
                return false;
            foreach ($filas as $f) {
                $c = strtolower($f['clase'] ?? $f['clase_nombre'] ?? $f['tipo_modelo'] ?? $f['nombre'] ?? '');
                foreach ($clasesFabricacion as $cf) {
                    if ($c !== '' && ($c === strtolower($cf) || strpos($c, strtolower($cf)) !== false || strpos(strtolower($cf), $c) !== false))
                        return true;
                }
            }
            return false;
        });

        $tieneFisicoFab = \App\Models\LiberacionModeloFundicion::where('ot', $targetReg->ot)
            ->where('tipo_origen', 'con_modelo')
            ->get()
            ->filter(function ($lib) use ($clasesFabricacion) {
                $tm = strtolower($lib->tipo_modelo ?? '');
                foreach ($clasesFabricacion as $cf) {
                    if ($tm !== '' && ($tm === strtolower($cf) || strpos($tm, strtolower($cf)) !== false || strpos(strtolower($cf), $tm) !== false))
                        return true;
                }
            })->count() > 0;

        $tienePreOrdenFab = $preOrdenesFabExistentes->count() > 0;
        $poPendienteEnvioFab = $preOrdenesFabExistentes->where('is_sent', 0)->first();

        // Para OTs de reproceso, la pre-orden pendiente puede ser directo de la OT de reproceso
        if ($esReproceso && !$poPendienteEnvioFab) {
            $poPendienteEnvioFab = \App\Models\PreOrdenFundicion::where('ot', $targetReg->ot)
                ->where('is_sent', 0)
                ->where(function ($q) {
                    $q->where('pdf_filename', 'NOT LIKE', '%Casting%')
                      ->where('pdf_filename', 'NOT LIKE', '%F_ALM_PFC_%')
                      ->where('pdf_filename', 'NOT LIKE', '%PFC%');
                })
                ->orderBy('id', 'desc')
                ->first();
            if ($poPendienteEnvioFab) {
                $tienePreOrdenFab = true;
            }
        }

        $tienePreOrden = $tienePreOrdenFab || $tieneFisicoFab;

        $clasesProcesadas = [];
        $preOrdenesEnviadas = \App\Models\PreOrdenFundicion::where('ot', $targetReg->ot)
            ->where('is_sent', 1)
            ->where('pdf_filename', 'NOT LIKE', '%_Anterior_N%')
            ->get();
        foreach ($preOrdenesEnviadas as $po) {
            $filas = is_string($po->filas) ? json_decode($po->filas, true) : $po->filas;
            if (is_array($filas)) {
                foreach ($filas as $f) {
                    $cVal = strtolower($f['clase'] ?? $f['clase_nombre'] ?? $f['tipo_modelo'] ?? $f['nombre'] ?? '');
                    if (!empty($cVal)) {
                        $inFab = false;
                        foreach ($clasesFabricacion as $cf) {
                            if (!empty($cf) && (strtolower($cf) === $cVal || strpos($cVal, strtolower($cf)) !== false || strpos(strtolower($cf), $cVal) !== false)) {
                                $inFab = true;
                                break;
                            }
                        }
                        if ($inFab) {
                            $clasesProcesadas[] = $cVal;
                        }
                    }
                }
            }
        }

        $liberacionesFisicas = \App\Models\LiberacionModeloFundicion::where('ot', $targetReg->ot)
            ->where('tipo_origen', 'con_modelo')
            ->whereNotNull('tipo_modelo')
            ->where('tipo_modelo', '!=', '')
            ->pluck('tipo_modelo')->toArray();
        foreach ($liberacionesFisicas as $lf) {
            if (!empty($lf)) {
                foreach (explode(',', $lf) as $c) {
                    $cTrim = strtolower(trim($c));
                    $inFab = false;
                    foreach ($clasesFabricacion as $cf) {
                        if (!empty($cf) && (strtolower($cf) === $cTrim || strpos($cTrim, strtolower($cf)) !== false || strpos(strtolower($cf), $cTrim) !== false)) {
                            $inFab = true;
                            break;
                        }
                    }
                    if ($inFab) {
                        $clasesProcesadas[] = $cTrim;
                    }
                }
            }
        }
        $clasesProcesadas = array_values(array_unique(array_filter($clasesProcesadas, fn($v) => $v !== '')));

        $clasesActivasCubiertas = [];
        $clasesActivasFaltantes = [];
        foreach ($otClasesActivas as $clActiva) {
            $cubierta = false;
            foreach ($clasesProcesadas as $cp) {
                if ($cp === '' || $clActiva === '')
                    continue;
                if (strpos($cp, strtolower($clActiva)) !== false || strpos(strtolower($clActiva), $cp) !== false) {
                    $cubierta = true;
                    break;
                }
            }
            if ($cubierta) {
                $clasesActivasCubiertas[] = $clActiva;
            } else {
                $clasesActivasFaltantes[] = $clActiva;
            }
        }

        $todasClasesProcesadas = count($otClasesActivas) > 0 && count($clasesActivasFaltantes) === 0;
        $algunaClaseProcesada = count($clasesActivasCubiertas) > 0;

        $controlDisabled = ((count($clasesFabricacion) > 0 || $esReinicioParcial)) ? '' : (($targetReg->tiene_modelo || $targetReg->pre_orden_sent || $targetReg->pre_orden_email_sent) ? 'opacity: 0.5; pointer-events: none;' : '');
        $hideControlCard = (count($clasesFabricacion) > 0 || $esReinicioParcial) ? '' : ((($tieneAprobados || $tieneRechazados) && !$esReproceso) ? 'display: none;' : ((count($clasesFabricacion) === 0 && !$esReproceso && !$targetReg->tiene_modelo && !$targetReg->pre_orden_sent && !$targetReg->pre_orden_email_sent) ? 'display: none;' : ''));
        $hideTengoModelo = ($todasClasesProcesadas || $poPendienteEnvioFab !== null) ? 'display: none;' : '';
        $hideGenerarFormato = ($todasClasesProcesadas || $poPendienteEnvioFab !== null || $esReproceso) ? 'display: none;' : '';
        $hideReprocesoPreOrden = ($esReproceso && !$tienePreOrdenFab) ? '' : 'display: none;';
        $hideEditPreOrden = ($tienePreOrdenFab && $poPendienteEnvioFab !== null) ? '' : 'display: none;';

        $clasesFisicamenteConfirmadas = [];
        $liberacionesFisicasObj = \App\Models\LiberacionModeloFundicion::where('ot', $targetReg->ot)
            ->where('tipo_origen', 'con_modelo')
            ->whereNotNull('tipo_modelo')
            ->where('tipo_modelo', '!=', '')
            ->get();
        foreach ($liberacionesFisicasObj as $lib) {
            $clasesArray = explode(',', $lib->tipo_modelo);
            foreach ($clasesArray as $c) {
                $clasesFisicamenteConfirmadas[] = strtolower(trim($c));
            }
        }
        $clasesFisicamenteConfirmadas = array_unique($clasesFisicamenteConfirmadas);

        $clasesFaltantesFisico = [];
        foreach ($otClasesActivas as $clActiva) {
            $cubierta = false;
            foreach ($clasesFisicamenteConfirmadas as $cp) {
                if ($cp === '' || $clActiva === '')
                    continue;
                if (strpos($cp, strtolower($clActiva)) !== false || strpos(strtolower($clActiva), $cp) !== false) {
                    $cubierta = true;
                    break;
                }
            }
            if (!$cubierta) {
                $clasesFaltantesFisico[] = $clActiva;
            }
        }

        $poPendienteEnvio = \App\Models\PreOrdenFundicion::where('ot', $targetReg->ot)
            ->where('is_sent', 0)
            ->where(function ($q) {
                $q->where('pdf_filename', 'NOT LIKE', '%Casting%')
                    ->where('pdf_filename', 'NOT LIKE', '%F_ALM_PFC_%')
                    ->where('pdf_filename', 'NOT LIKE', '%PFC%');
            })
            ->orderBy('id', 'desc')
            ->first();
        $clasesParaEnvio = [];
        if ($poPendienteEnvio) {
            $filas = is_string($poPendienteEnvio->filas) ? json_decode($poPendienteEnvio->filas, true) : $poPendienteEnvio->filas;
            if (is_array($filas)) {
                foreach ($filas as $f) {
                    $cVal = strtolower($f['clase'] ?? $f['clase_nombre'] ?? $f['tipo_modelo'] ?? $f['nombre'] ?? '');
                    if (!empty($cVal)) {
                        $clasesParaEnvio[] = $cVal;
                    }
                }
            }
        }

        if ($tieneFisicoFab && empty($clasesParaEnvio)) {
            $clasesParaEnvio = array_values(array_map('strtolower', $clasesFisicamenteConfirmadas));
        }

        $clasesYaProcesadasJson = json_encode(array_values($clasesActivasCubiertas));
        $clasesActivasFaltantesJson = json_encode(array_values($clasesActivasFaltantes));
        $todasClasesActivasJson = json_encode(array_values($otClasesActivas));
        $clasesActivasNoEnviadasJson = json_encode(array_values($clasesActivasFaltantes));
        $clasesFaltantesFisicoJson = json_encode(array_values($clasesFaltantesFisico));
        $clasesParaEnvioJson = json_encode(array_values(array_unique($clasesParaEnvio)));

        $todasClasesEnviadas = $todasClasesProcesadas && ($poPendienteEnvioFab === null);
        $isFullySubmitted = $todasClasesEnviadas;
        $hideAllBtns = $isFullySubmitted ? 'display: none;' : '';
        $hideSendEmail = ($poPendienteEnvioFab !== null) ? '' : 'display: none;';
        $calidadYaRespondio = ($reg->casting_pdf_generated || in_array($reg->calidad_revision_status, ['casting_aprobado']) || (($tieneAprobados || $tieneRechazados) && !$esReproceso && !$esReinicioParcial));
        $ocultarCardEnModelo = count($clasesFabricacion) > 0 ? false : ($calidadYaRespondio || (($tieneAprobados || $tieneRechazados) && (!$esReproceso || count($clasesFabricacion) === 0 || !$hasRechazosReal)));
    @endphp

    {{-- FUNCIONES AUXILIARES PARA FILTRAR ARCHIVOS POR CLASE --}}
    @php
        $filtrarArchivos = function ($archivos, $clasesPermitidas) {
            $resultado = [];
            foreach ($archivos as $archivo) {
                // If the file doesn't have a specific class assigned, or matches, we check
                $nombre = strtolower($archivo['nombre']);
                $claseEnArchivo = strtolower($archivo['clase'] ?? '');

                $match = false;
                foreach ($clasesPermitidas as $cp) {
                    $cp = strtolower($cp);
                    if ($cp === '')
                        continue;

                    // Match by direct class attribute or filename
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

                // If it's an old file structure and we can't determine class, include it only in active if there's no processing done yet
                if ($match) {
                    $resultado[] = $archivo;
                }
            }
            return $resultado;
        };

        $dibujosPendientes = $filtrarArchivos($dibujosModelo, $clasesActivasFaltantes);
        $ayudasPendientes = $filtrarArchivos($ayudasModelo, $clasesActivasFaltantes);
        $preordenesPendientes = $filtrarArchivos($almacenPreordenesFab, $clasesActivasFaltantes);

        $dibujosProcesados = $filtrarArchivos($dibujosModelo, $clasesActivasCubiertas);
        $ayudasProcesadas = $filtrarArchivos($ayudasModelo, $clasesActivasCubiertas);
        $preordenesProcesadas = $filtrarArchivos($almacenPreordenesFab, $clasesActivasCubiertas);

        // Si hay archivos que no hicieron match con nada (quizás por el nombre), los ponemos en pendientes por seguridad
        $idsAsignados = array_merge(
            array_column($dibujosPendientes, 'nombre'),
            array_column($dibujosProcesados, 'nombre')
        );
        foreach ($dibujosModelo as $a) {
            if (!in_array($a['nombre'], $idsAsignados))
                $dibujosPendientes[] = $a;
        }

        $idsAsignadosAyu = array_merge(
            array_column($ayudasPendientes, 'nombre'),
            array_column($ayudasProcesadas, 'nombre')
        );
        foreach ($ayudasModelo as $a) {
            if (!in_array($a['nombre'], $idsAsignadosAyu))
                $ayudasPendientes[] = $a;
        }

        $idsAsignadosPo = array_merge(
            array_column($preordenesPendientes, 'nombre'),
            array_column($preordenesProcesadas, 'nombre')
        );
        foreach ($almacenPreordenesFab as $a) {
            if (!in_array($a['nombre'], $idsAsignadosPo))
                $preordenesPendientes[] = $a;
        }
    @endphp

    <div class="alm-process-block"
        style="margin-bottom: 25px; padding: 20px; border-radius: 14px; background-color: #f0f9ff; border: 2px solid #0284c7; box-shadow: 0 4px 14px rgba(2, 132, 199, 0.08);">
        <div
            style="display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #0284c7; padding-bottom: 10px; margin-bottom: 15px;">
            <h3
                style="margin: 0; color: #0284c7; font-size: 1.15rem; font-weight: 700; display: flex; align-items: center; gap: 10px;">
                <img src="{{ asset('images/almacen.png') }}" style="width: 30px; height: 30px; object-fit: contain;">
                @if ($hayRechazadosSinPreorden)
                    Documentos de Almacén
                    {{ count($rechazadosSinPreorden) > 0 ? '(' . implode(', ', array_map('ucfirst', $rechazadosSinPreorden)) . ')' : '' }}
                @else
                    Etapa: Fabricación / Re-Proceso de Modelo
                    {{ count($clasesFabricacionHeader) > 0 ? '(' . implode(', ', array_map('ucfirst', $clasesFabricacionHeader)) . ')' : '' }}
                @endif
            </h3>
            <span
                style="font-size: 0.8rem; font-weight: 700; background: #e0f2fe; color: #0369a1; padding: 4px 12px; border-radius: 6px; border: 1px solid #bae6fd;">
                {{ $hayRechazadosSinPreorden ? 'DOCUMENTOS ALMACÉN' : 'FABRICACIÓN / MODELO' }}
            </span>
        </div>

        {{-- ================================================================= --}}
        {{-- SECCIÓN ACTIVA (CLASES PENDIENTES DE PROCESAR) --}}
        {{-- ================================================================= --}}
        @if(count($clasesActivasFaltantes) > 0 || (count($clasesActivasFaltantes) == 0 && count($clasesActivasCubiertas) == 0))
            <div class="cal-subcontainer-almacen"
                style="margin-bottom: 25px; padding: 18px; border-radius: 12px; background-color: #f0f9ff; border: 2px solid #0ea5e9; box-shadow: 0 3px 10px rgba(14, 165, 233, 0.08);">
                <div
                    style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1.5px solid #bae6fd; padding-bottom: 8px; margin-bottom: 15px;">
                    <h4
                        style="margin: 0; color: #0369a1; font-size: 1.05rem; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                        <img src="{{ asset('images/almacen.png') }}" style="width: 22px; height: 22px; object-fit: contain;">
                        Procesos Activos ({{ implode(', ', array_map('ucfirst', $clasesActivasFaltantes)) }})
                    </h4>
                </div>

                {{-- Dibujos de Modelo Pendientes --}}
                @if (count($dibujosPendientes) > 0)
                    <h4 style="margin-top: 10px; margin-bottom: 10px; color: #005194; font-weight: 700;">Dibujos / DWG de Fundición
                    </h4>
                    <div class="alm-pdf-grid"
                        style="background-color: #e0f2fe; border: 1px solid #bae6fd; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                        @foreach ($dibujosPendientes as $archivoInfo)
                            @php $isDwg = strtolower(pathinfo($archivoInfo['nombre'], PATHINFO_EXTENSION)) === 'dwg'; @endphp
                            <div class="dibujos-file-card"
                                style="animation-delay: {{ $loop->index * 0.05 }}s; border-left-color: #0284c7;">
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
                                    <button class="btn-dibujos btn-dibujos-sm btn-ver"
                                        onclick="almacenVerPdf('{{ $archivoInfo['ot'] }}', '{{ $archivoInfo['nombre'] }}', '{{ $archivoInfo['tipo'] }}')">{{ $isDwg ? 'Descargar' : 'Ver' }}</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Ayudas Visuales Pendientes --}}
                @if (count($ayudasPendientes) > 0)
                    <h4 style="margin-top: 15px; margin-bottom: 10px; color: #9c0300; font-weight: 700;">Ayudas Visuales de
                        Fundición</h4>
                    <div class="alm-pdf-grid"
                        style="background-color: #e0f2fe; border: 1px solid #bae6fd; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                        @foreach ($ayudasPendientes as $archivoInfo)
                            @php
                                $ayudaUrl = route('ayudas_fundicion.serve', [
                                    'clase' => $archivoInfo['clase'] ?? '',
                                    'archivo' => basename($archivoInfo['nombre'])
                                ]);
                            @endphp
                            @php $isDwg = strtolower(pathinfo($archivoInfo['nombre'], PATHINFO_EXTENSION)) === 'dwg'; @endphp
                            <div class="dibujos-file-card card-ayuda"
                                style="animation-delay: {{ $loop->index * 0.05 }}s; border-left-color: #0284c7;">
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
                                    <button class="btn-dibujos btn-dibujos-sm btn-ver alm-background-color-0284c7 alm-color-white"
                                        onclick="almacenAbrirArchivo('{{ $ayudaUrl }}', '{{ $archivoInfo['ot'] }}', '{{ $archivoInfo['nombre'] }}', 'ayuda')">{{ $isDwg ? 'Descargar' : 'Ver' }}</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Documentos Pendientes --}}
                @if (count($preordenesPendientes) > 0)
                    <h4 style="margin-top: 15px; margin-bottom: 10px; color: #0284c7; font-weight: 700;">Pre-órdenes /
                        Confirmaciones de Modelo</h4>
                    <div class="alm-pdf-grid"
                        style="background-color: #e0f2fe; border: 1px solid #bae6fd; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                        @foreach ($preordenesPendientes as $archivoInfo)
                            @php $isDwg = strtolower(pathinfo($archivoInfo['nombre'], PATHINFO_EXTENSION)) === 'dwg'; @endphp
                            <div class="dibujos-file-card"
                                style="animation-delay: {{ $loop->index * 0.05 }}s; border-left-color: #0284c7;">
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
                                    <button class="btn-dibujos btn-dibujos-sm btn-ver alm-background-color-0284c7 alm-color-white"
                                        onclick="almacenVerPdf('{{ $archivoInfo['ot'] }}', '{{ $archivoInfo['nombre'] }}', 'preorden')">{{ $isDwg ? 'Descargar' : 'Ver' }}</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- TARJETA DE CONTROLES (SOLO SI HAY PENDIENTES Y showControlCard) --}}
                @if ($showControlCard && !$ocultarCardEnModelo)
                    @php
                        $enviadas = ($reg && is_array($reg->clases_enviadas)) ? $reg->clases_enviadas : [];
                        // Normalizar: si el array es indexado numéricamente (legacy), usamos el valor como nombre de clase
                        $clasesAlertadas = [];
                        foreach ($enviadas as $envClass => $hash) {
                            $claseNombre = is_numeric($envClass) ? $hash : $envClass;
                            $clasesAlertadas[] = str_replace([' ', '_', 'í', 'gú'], ['', '', 'i', 'gu'], strtolower(trim($claseNombre)));
                        }

                        // Fallback: si la OT ya tiene alert_sent_at, consideramos que Ingeniería ya notificó
                        $alertYaEnviada = $reg && !empty($reg->alert_sent_at);

                        $hasAlertedClass = empty($otClasesActivas) ? true : $alertYaEnviada;
                        if (!$hasAlertedClass) {
                            foreach ($otClasesActivas as $clActiva) {
                                $c2Norm = str_replace([' ', '_', 'í', 'gú'], ['', '', 'i', 'gu'], strtolower(trim($clActiva)));
                                if (in_array($c2Norm, $clasesAlertadas)) {
                                    $hasAlertedClass = true;
                                    break;
                                }
                            }
                        }
                    @endphp
                    <div class="lib-calidad-card" id="control-modelo-{{ md5($reg->ot) }}"
                        style="margin-top: 20px; {{ trim($controlDisabled . ' ' . $hideControlCard) }} @if(!$hasAlertedClass) pointer-events: none; opacity: 0.6; @endif">
                        <div class="lib-calidad-card-header">
                            <img src="{{ ($esReproceso || $esReinicioParcial) ? asset('images/Reproceso.png') : asset('images/almacen.png') }}"
                                alt="Almacén" class="alm-icon-lg">
                            <div class="alm-overflow-hidden alm-flex-1">
                                <span class="lib-calidad-card-title">Control de Modelos &mdash; Almacén</span>
                                <span class="lib-calidad-card-ot">{{ preg_replace('/_\d{8}_\d{6}_.*/', '', $reg->ot) }}</span>
                            </div>
                            @if (count($otClasesActivas) > 0)
                                <div
                                    class="alm-flex-shrink-0 alm-display-flex alm-flex-direction-column alm-align-items-center alm-gap-2px">
                                    <span
                                        style="font-size:1.1em; font-weight:800; color:{{ $todasClasesProcesadas ? '#15803d' : ($algunaClaseProcesada ? '#0369a1' : '#ffffff') }};">
                                        {{ count($clasesActivasCubiertas) }}/{{ count($otClasesActivas) }}
                                    </span>
                                    <span
                                        class="alm-font-size-0-65em alm-font-weight-600 alm-color-rgba-255-255-255-0-75 alm-letter-spacing-0-5px alm-text-transform-uppercase">clases</span>
                                </div>
                            @endif
                        </div>
                        <div class="lib-calidad-card-body">
                            <div class="lib-calidad-action-row">
                                <h4 class="lib-calidad-card-prompt">
                                    @if (!$hasAlertedClass)
                                        <span class="alm-color-dc2626 alm-font-weight-700" style="color: #dc2626; font-weight: 700;">
                                            Bloqueado: Esperando a que Ingeniería (Dibujos de Fundición) envíe la notificación a
                                            Almacén.
                                        </span>
                                    @elseif ($algunaClaseProcesada && !$todasClasesProcesadas)
                                        <span class="alm-color-0369a1 alm-font-weight-600">
                                            Proceso parcial ({{ count($clasesActivasCubiertas) }}/{{ count($otClasesActivas) }} clases
                                            enviadas). Puedes generar o enviar las pre-órdenes restantes.
                                        </span>
                                    @elseif($targetReg->pre_orden_sent && !$targetReg->pre_orden_email_sent && !$esReinicioParcial)
                                        Pre-orden de fabricación de modelo generada y guardada. Pendiente de enviar al proveedor.
                                    @elseif($targetReg->pre_orden_email_sent && !$esReinicioParcial)
                                        Pre-orden enviada por correo al proveedor. Esperando entrega de modelo físico para revisión de
                                        Calidad.
                                    @elseif($targetReg->tiene_modelo && !$esReinicioParcial && !$esReproceso)
                                        Modelo físico disponible en Almacén, en espera de revisión por Calidad.
                                    @elseif($esReproceso)
                                        Modelos Retornados a Reproceso. Procede a generar la Pre-Orden de Fabricación de Modelo.
                                    @else
                                        Formatos LDM subidos. ¿Cuentas con el modelo en físico para la etapa de
                                        <strong>Fabricación</strong>?
                                    @endif
                                </h4>
                                <div class="lib-calidad-card-btns" style="{{ $hideAllBtns }}">
                                    @if((!$targetReg->tiene_modelo || $esReinicioParcial))
                                        <button class="btn-modelo btn-modelo-si"
                                            onclick="abrirModalConfirmarModelo('{{ $targetReg->ot }}', '{{ md5($reg->ot) }}', {{ $clasesActivasFaltantesJson }}, {{ $todasClasesActivasJson }})"
                                            title="Confirmar que se tiene el modelo físico en almacén" style="{{ $hideTengoModelo }}">
                                            <img src="{{ asset('images/Espera.png') }}" alt="Si">
                                            <span>Sí, tengo modelo</span>
                                        </button>
                                    @endif

                                    <button class="btn-modelo btn-modelo-no"
                                        onclick="abrirModalPreOrden('{{ $targetReg->ot }}', {{ $clasesYaProcesadasJson }})"
                                        title="No cuento con él, generar formato PDF"
                                        style="{{ $tienePreOrdenFab && !$esReinicioParcial ? 'display: none;' : $hideGenerarFormato }}">
                                        <img src="{{ asset('images/pdf-view.png') }}" alt="PDF">
                                        <span>No, generar formato</span>
                                    </button>

                                    <button class="btn-modelo btn-modelo-no"
                                        onclick="abrirModalPreOrden('{{ $targetReg->ot }}', {{ $clasesYaProcesadasJson }})"
                                        title="Generar / editar la pre-orden de fabricación de modelo"
                                        style="{{ $hideReprocesoPreOrden }}">
                                        <img src="{{ asset('images/pdf-view.png') }}" alt="Pre-Orden">
                                        <span>Pre-Orden Modelo</span>
                                    </button>

                                    <button class="btn-modelo btn-modelo-edit"
                                        onclick="abrirModalPreOrden('{{ $targetReg->ot }}', {{ $clasesYaProcesadasJson }})"
                                        title="Editar información de la preorden existente" style="{{ $hideEditPreOrden }}">
                                        <img src="{{ asset('images/editar-informacion.png') }}" alt="Editar">
                                        <span>Editar Pre-orden</span>
                                    </button>

                                    <button class="btn-modelo btn-modelo-email"
                                        onclick="abrirModalEnviarPreOrden('{{ $targetReg->ot }}', 'modelo', {{ $clasesParaEnvioJson }})"
                                        title="{{ $esReproceso ? 'Enviar alerta a Calidad para iniciar revisión de re-proceso' : 'Enviar pre-orden por correo electrónico' }}"
                                        style="{{ $hideSendEmail }}">
                                        <img src="{{ asset('images/enviando.png') }}" alt="Enviar">
                                        <span>{{ $esReproceso ? 'Enviar Alerta' : 'Enviar Correo' }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        {{-- ================================================================= --}}
        {{-- SECCIÓN INFORMATIVA (CLASES YA PROCESADAS EN FABRICACIÓN) --}}
        {{-- ================================================================= --}}
        @if (count($clasesActivasCubiertas) > 0)
            <div class="cal-subcontainer-almacen"
                style="margin-bottom: 25px; padding: 18px; border-radius: 12px; background-color: #e0f2fe; border: 2px solid #0284c7; box-shadow: 0 3px 10px rgba(2, 132, 199, 0.08);">
                <div
                    style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1.5px solid #bae6fd; padding-bottom: 8px; margin-bottom: 15px;">
                    <h4
                        style="margin: 0; color: #0369a1; font-size: 1.05rem; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                        <img src="{{ asset('images/Aprobado.png') }}" style="width: 22px; height: 22px; object-fit: contain;">
                        Clases Procesadas ({{ implode(', ', array_map('ucfirst', $clasesActivasCubiertas)) }})
                    </h4>
                    <span
                        style="font-size: 0.75rem; font-weight: 700; background: #dcfce7; color: #0369a1; padding: 3px 10px; border-radius: 6px; border: 1px solid #86efac;">
                        ENVIADO A CALIDAD
                    </span>
                </div>

                {{-- Dibujos Procesados --}}
                @if (count($dibujosProcesados) > 0)
                    <h4 style="margin-top: 10px; margin-bottom: 10px; color: #0369a1; font-weight: 700;">Dibujos / DWG de Fundición
                    </h4>
                    <div class="alm-pdf-grid"
                        style="background-color: #e0f2fe; border: 1px solid #bae6fd; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                        @foreach ($dibujosProcesados as $archivoInfo)
                            @php $isDwg = strtolower(pathinfo($archivoInfo['nombre'], PATHINFO_EXTENSION)) === 'dwg'; @endphp
                            <div class="dibujos-file-card"
                                style="animation-delay: {{ $loop->index * 0.05 }}s; border-left-color: #0284c7;">
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
                                    <button class="btn-dibujos btn-dibujos-sm btn-ver alm-background-color-0284c7 alm-color-white"
                                        onclick="almacenVerPdf('{{ $archivoInfo['ot'] }}', '{{ $archivoInfo['nombre'] }}', '{{ $archivoInfo['tipo'] }}')">{{ $isDwg ? 'Descargar' : 'Ver' }}</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Ayudas Visuales Procesadas --}}
                @if (count($ayudasProcesadas) > 0)
                    <h4 style="margin-top: 15px; margin-bottom: 10px; color: #0369a1; font-weight: 700;">Ayudas Visuales de
                        Fundición</h4>
                    <div class="alm-pdf-grid"
                        style="background-color: #e0f2fe; border: 1px solid #bae6fd; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                        @foreach ($ayudasProcesadas as $archivoInfo)
                            @php
                                $ayudaUrl = route('ayudas_fundicion.serve', [
                                    'clase' => $archivoInfo['clase'] ?? '',
                                    'archivo' => basename($archivoInfo['nombre'])
                                ]);
                            @endphp
                            @php $isDwg = strtolower(pathinfo($archivoInfo['nombre'], PATHINFO_EXTENSION)) === 'dwg'; @endphp
                            <div class="dibujos-file-card card-ayuda"
                                style="animation-delay: {{ $loop->index * 0.05 }}s; border-left-color: #0284c7;">
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
                                    <button class="btn-dibujos btn-dibujos-sm btn-ver alm-background-color-0284c7 alm-color-white"
                                        onclick="almacenAbrirArchivo('{{ $ayudaUrl }}', '{{ $archivoInfo['ot'] }}', '{{ $archivoInfo['nombre'] }}', 'ayuda')">{{ $isDwg ? 'Descargar' : 'Ver' }}</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Documentos Procesados --}}
                @if (count($preordenesProcesadas) > 0)
                    <h4 style="margin-top: 15px; margin-bottom: 10px; color: #0369a1; font-weight: 700;">Pre-órdenes/Confirmaciones
                        de Modelo</h4>
                    <div class="alm-pdf-grid"
                        style="background-color: #e0f2fe; border: 1px solid #bae6fd; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                        @foreach ($preordenesProcesadas as $archivoInfo)
                            @php $isDwg = strtolower(pathinfo($archivoInfo['nombre'], PATHINFO_EXTENSION)) === 'dwg'; @endphp
                            <div class="dibujos-file-card"
                                style="animation-delay: {{ $loop->index * 0.05 }}s; border-left-color: #0284c7;">
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
                                    <button class="btn-dibujos btn-dibujos-sm btn-ver alm-background-color-0284c7 alm-color-white"
                                        onclick="almacenVerPdf('{{ $archivoInfo['ot'] }}', '{{ $archivoInfo['nombre'] }}', 'preorden')">{{ $isDwg ? 'Descargar' : 'Ver' }}</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="lib-calidad-card"
                    style="margin-top: 20px; border: 2px solid #0284c7; border-radius: 12px; overflow: hidden;">
                    <div class="lib-calidad-card-header"
                        style="background: #0284c7; display: flex; align-items: center; gap: 15px; padding: 12px 20px;">
                        <img src="{{ asset('images/almacen.png') }}" alt="Almacén"
                            style="width: 38px; height: 38px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));">
                        <div class="alm-overflow-hidden alm-flex-1">
                            <span class="lib-calidad-card-title"
                                style="color: white; font-weight: 700; font-size: 1.1rem; display: block;">Información — Almacén
                                (Fabricación)</span>
                            <span class="lib-calidad-card-ot"
                                style="color: #bae6fd; font-size: 0.85rem;">{{ preg_replace('/_\d{8}_\d{6}_.*/', '', $reg->ot) }}</span>
                        </div>
                    </div>
                    <div class="lib-calidad-card-body" style="background: #f0f9ff; padding: 20px;">
                        <div class="lib-calidad-action-row" style="display: flex; align-items: center; gap: 20px;">
                            <img src="{{ asset('images/enviando.png') }}" style="width: 54px; height: 54px;">
                            <div>
                                <h4 class="lib-calidad-card-prompt"
                                    style="color: #0369a1; margin-top: 0; margin-bottom: 8px; font-weight: 700; font-size: 1.1rem;">
                                    Clases Procesadas por Almacén
                                </h4>
                                <p style="color: #0c4a6e; margin: 0; font-size: 0.95rem; font-weight: 500;">
                                    El proceso ahora le pertenece a Calidad. Por favor, espera instrucciones para las clases
                                    enviadas.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    </div>
@endif