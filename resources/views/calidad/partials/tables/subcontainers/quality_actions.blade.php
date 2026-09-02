{{-- ── ACCIONES DE CALIDAD / ESTADOS DE LIBERACION ── --}}
@if (in_array(Auth::user()->perfil, [1, 2, 3, 4, '1', '2', '3', '4']) && $estado === 'activa')
    @if ($targetReg->calidad_revision_status === 'casting_aprobado')
        {{-- Banner informativo de casting_aprobado --}}
        <div class="lib-calidad-card" style="background:#f3e8ff; border:2px solid #9333ea;">
            <div class="lib-calidad-card-header"
                style="background:linear-gradient(135deg,#9333ea,#7c3aed);border-bottom:2px solid rgba(147,51,234,0.4);">
                <img src="{{ asset('images/Proveedor.png') }}" alt="Proveedor"
                    class="cal-width-38px cal-height-38px cal-object-fit-contain cal-flex-shrink-0 cal-filter-brightness-0-invert-1">
                <div class="cal-overflow-hidden cal-flex-1">
                    <span class="lib-calidad-card-title cal-color-ffffff">Pre-Orden de Casting &mdash; Enviado al
                        Proveedor</span>
                    <span
                        class="lib-calidad-card-ot cal-color-rgba-255-255-255-0-9">{{ preg_replace('/_\d{8}_\d{6}_.*/', '', $targetReg->ot) }}</span>
                </div>
            </div>
            <div class="lib-calidad-card-body" style="padding:18px 22px;text-align:center;background:#faf5ff;">
                <img src="{{ asset('images/Proveedor.png') }}" alt="Proveedor"
                    style="width:52px;height:52px;margin-bottom:10px;">
                <h4 style="color:#7c3aed;font-size:1.05rem;font-weight:700;margin:0 0 8px 0;font-family:'Poppins',sans-serif;">
                    Proceso Finalizado &mdash; Enviado al Proveedor</h4>
                <p style="color:#4c1d95;font-size:0.9rem;margin:0;font-family:'Poppins',sans-serif;">La pre-orden de casting fue
                    generada y enviada al proveedor. No se requieren acciones adicionales de Calidad para esta OT.</p>
            </div>
        </div>
    @else
        <div class="lib-calidad-card">
            <div class="lib-calidad-card-header">
                <img src="{{ asset('images/Quality.png') }}" alt="Calidad"
                    class="cal-width-38px cal-height-38px cal-object-fit-contain cal-flex-shrink-0" />
                <div class="cal-overflow-hidden cal-flex-1">
                    <span class="lib-calidad-card-title">Acciones
                        de
                        Liberacion
                        &mdash;
                        Calidad</span>
                    <span class="lib-calidad-card-ot">{{ preg_replace('/_\d{8}_\d{6}_.*/', '', $targetReg->ot) }}</span>
                </div>
                @php
                    $hdClasesActivas = collect(
                        $targetReg->ayudas_config ?? [],
                    )
                        ->filter(
                            fn($c) => !str_contains(
                                strtolower($c),
                                'opcional',
                            ) || str_contains(strtolower($c), 'pistones') || str_contains(strtolower($c), 'guías') || str_contains(strtolower($c), 'guias'),
                        )
                        ->filter(function ($claseNombre) use ($targetReg, ) {
                            $clLow = strtolower($claseNombre);
                            $tipo = null;
                            if (
                                strpos(
                                    $clLow,
                                    'candado obturador',
                                ) !== false
                            ) {
                                $tipo = 'Candado obturador';
                            } elseif (
                                strpos(
                                    $clLow,
                                    'cabeza de soplo',
                                ) !== false
                            ) {
                                $tipo = 'Cabeza de soplo';
                            } elseif (
                                strpos($clLow, 'embudo') !== false
                            ) {
                                $tipo = 'Embudo';
                            } elseif (
                                strpos($clLow, 'corona') !== false
                            ) {
                                $tipo = 'Corona';
                            } elseif (
                                strpos($clLow, 'plato') !== false
                            ) {
                                $tipo = 'Plato';
                            } elseif (
                                strpos($clLow, 'fondo') !== false
                            ) {
                                $tipo = 'Fondo';
                            } elseif (
                                strpos($clLow, 'obturador') !==
                                false
                            ) {
                                $tipo = 'Obturador';
                            } elseif (
                                strpos($clLow, 'molde') !== false
                            ) {
                                $tipo = 'Molde';
                            } elseif (
                                strpos($clLow, 'bombillo') !== false
                            ) {
                                $tipo = 'Bombillo';
                            } elseif (
                                strpos($clLow, 'pistones') !== false
                            ) {
                                $tipo = 'Pistones';
                            } elseif (
                                strpos($clLow, 'guías') !== false || strpos($clLow, 'guias') !== false
                            ) {
                                $tipo = 'Guías';
                            } elseif (
                                strpos($clLow, 'pistones') !== false
                            ) {
                                $tipo = 'Pistones';
                            } elseif (
                                strpos($clLow, 'guías') !== false || strpos($clLow, 'guias') !== false
                            ) {
                                $tipo = 'Guías';
                            }
                            if ($tipo) {
                                $baseOt = preg_replace('/_(?:(?:candado\s+obturador|cabeza\s+de\s+soplo|obturador|bombillo|embudo|corona|plato|molde|fondo|pistones|guías|guias)(?:_(?:candado\s+obturador|cabeza\s+de\s+soplo|obturador|bombillo|embudo|corona|plato|molde|fondo|pistones|guías|guias))*_)?R\d+$/iu', '', $targetReg->ot);
                                $isAprob = \App\Models\LiberacionModeloFundicion::where(
                                    'ot',
                                    '!=',
                                    $targetReg->ot,
                                    'and'
                                )
                                    ->where(
                                        function ($q) use ($baseOt) {
                                            $q->where('ot', '=', $baseOt, 'and')
                                              ->where('ot', 'LIKE', $baseOt . '_R%', 'or')
                                              ->where('ot', 'LIKE', $baseOt . '_%_R%', 'or');
                                        },
                                        null,
                                        null,
                                        'and'
                                    )
                                    ->where(
                                        'tipo_modelo',
                                        '=',
                                        $tipo,
                                    )
                                    ->where(
                                        'decision',
                                        '=',
                                        'aprobar',
                                    )
                                    ->exists();
                                if ($isAprob) {
                                    return false;
                                }
                                return true;
                            }
                            return false;
                        })
                        ->values()
                        ->toArray();
                    $hdCont = 0;
                    foreach ($hdClasesActivas as $clName) {
                        $clLow = strtolower($clName);
                        $tipo = null;
                        if (
                            strpos($clLow, 'candado obturador') !==
                            false
                        ) {
                            $tipo = 'Candado obturador';
                        } elseif (
                            strpos($clLow, 'cabeza de soplo') !==
                            false
                        ) {
                            $tipo = 'Cabeza de soplo';
                        } elseif (
                            strpos($clLow, 'embudo') !== false
                        ) {
                            $tipo = 'Embudo';
                        } elseif (
                            strpos($clLow, 'corona') !== false
                        ) {
                            $tipo = 'Corona';
                        } elseif (
                            strpos($clLow, 'plato') !== false
                        ) {
                            $tipo = 'Plato';
                        } elseif (
                            strpos($clLow, 'fondo') !== false
                        ) {
                            $tipo = 'Fondo';
                        } elseif (
                            strpos($clLow, 'obturador') !== false
                        ) {
                            $tipo = 'Obturador';
                        } elseif (
                            strpos($clLow, 'molde') !== false
                        ) {
                            $tipo = 'Molde';
                        } elseif (
                            strpos($clLow, 'bombillo') !== false
                        ) {
                            $tipo = 'Bombillo';
                        } elseif (
                            strpos($clLow, 'pistones') !== false
                        ) {
                            $tipo = 'Pistones';
                        } elseif (
                            strpos($clLow, 'guías') !== false || strpos($clLow, 'guias') !== false
                        ) {
                            $tipo = 'Guías';
                        } elseif (
                            strpos($clLow, 'pistones') !== false
                        ) {
                            $tipo = 'Pistones';
                        } elseif (
                            strpos($clLow, 'guías') !== false || strpos($clLow, 'guias') !== false
                        ) {
                            $tipo = 'Guías';
                        }
                        if ($tipo) {
                            if (
                                \App\Models\LiberacionModeloFundicion::where(
                                    'ot',
                                    '=',
                                    $targetReg->ot,
                                )
                                    ->where(
                                        'tipo_modelo',
                                        '=',
                                        $tipo,
                                    )
                                    ->where(function ($q) {
                                        $q->whereNotNull('user_id_calidad')
                                            ->orWhereNotNull('decision');
                                    })
                                    ->exists()
                            ) {
                                $hdCont++;
                            }
                        }
                    }
                @endphp
                <div
                    class="cal-flex-shrink-0 cal-display-flex cal-flex-direction-column cal-align-items-center cal-gap-2px cal-padding-left-10px cal-border-left-1px-solid-e2e8f0 cal-margin-left-auto">
                    <span
                        style="font-size:1.1em; font-weight:800; color: {{ $hdCont == count($hdClasesActivas) && count($hdClasesActivas) > 0 ? '#15803d' : '#033966' }};">
                        {{ $hdCont }}/{{ count($hdClasesActivas) }}
                    </span>
                    <span
                        class="cal-font-size-0-65em cal-font-weight-700 cal-color-64748b cal-letter-spacing-0-5px cal-text-transform-uppercase">Clases</span>
                    @if ($hdCont == count($hdClasesActivas) && count($hdClasesActivas) > 0)
                        <img src="{{ asset('images/ready.png') }}" class="cal-width-14px cal-height-14px cal-margin-top-2px"
                            alt="Listo" />
                    @endif
                </div>
            </div>
            <div class="lib-calidad-card-body">
                @if (in_array($targetReg->calidad_revision_status, ['aprobado', 'calidad_aprobado']))
                    <div
                        class="lib-estado-badge lib-estado-aprobado cal-padding-12px-16px cal-width-100pct cal-box-sizing-border-box cal-display-flex cal-align-items-center cal-gap-8px">
                        <img src="{{ asset('images/Aprobado.png') }}" alt=""
                            class="cal-width-18px cal-height-18px cal-object-fit-contain cal-flex-shrink-0" />
                        <span>Liberación aprobada. Puedes revisar las clases procesadas o
                            modificar su estado si es necesario.</span>
                    </div>
                @elseif (in_array($targetReg->calidad_revision_status, ['mixto', 'calidad_mixto', 'calidad_parcial']))
                    <div
                        class="lib-estado-badge lib-estado-guardado cal-padding-12px-16px cal-width-100pct cal-box-sizing-border-box cal-display-flex cal-align-items-center cal-gap-8px">
                        <img src="{{ asset('images/Guardado.png') }}" alt=""
                            class="cal-width-18px cal-height-18px cal-object-fit-contain cal-flex-shrink-0" />
                        <span>Liberación parcial / mixta procesada. Puedes revisar las
                            clases pendientes o reiniciadas y generar un nuevo envío para la
                            liberación final.</span>
                    </div>
                @elseif (in_array($targetReg->calidad_revision_status, ['rechazado', 'calidad_rechazado']))
                    <div
                        class="lib-estado-badge lib-estado-rechazado cal-padding-12px-16px cal-width-100pct cal-box-sizing-border-box cal-display-flex cal-align-items-center cal-gap-8px">
                        <img src="{{ asset('images/Rechazado.png') }}" alt=""
                            class="cal-width-18px cal-height-18px cal-object-fit-contain cal-flex-shrink-0" />
                        <span>Liberación rechazada anteriormente. Puedes revisar las clases
                            y volver a emitir un nuevo proceso de liberación.</span>
                    </div>
                @elseif (is_null($targetReg->calidad_revision_status))
                    <div
                        class="lib-estado-badge lib-estado-info cal-width-100pct cal-box-sizing-border-box cal-display-flex cal-align-items-center cal-gap-8px">
                        Modelo disponible para iniciar el proceso de liberación.
                    </div>
                @elseif (in_array($targetReg->calidad_revision_status, ['pendiente', 'calidad_pendiente']))
                    <div
                        class="lib-estado-badge lib-estado-guardado cal-width-100pct cal-box-sizing-border-box cal-display-flex cal-align-items-center cal-gap-8px">
                        <img src="{{ asset('images/Guardado.png') }}" alt=""
                            class="cal-width-18px cal-height-18px cal-object-fit-contain cal-flex-shrink-0" />
                        Datos capturados como borrador.
                    </div>
                @endif
                @php
                    $borradorPendiente = \App\Models\LiberacionModeloFundicion::where(
                        'ot',
                        $targetReg->ot,
                    )
                        ->where('estado', 'pendiente')
                        ->first();
                    $scarModelo = \App\Models\ScarModelo::where(
                        'ot',
                        $targetReg->ot,
                    )->first();
                    $reqFotos =
                        $scarModelo &&
                        ($scarModelo->evidencia_fotos ||
                            $scarModelo->evidencia_otro);
                    $clasesActivas = collect(
                        $targetReg->ayudas_config ?? [],
                    )
                        ->filter(
                            fn($c) => !str_contains(
                                strtolower($c),
                                'opcional',
                            ) || str_contains(strtolower($c), 'pistones') || str_contains(strtolower($c), 'guías') || str_contains(strtolower($c), 'guias'),
                        )
                        ->filter(function ($claseNombre) use ($targetReg, ) {
                            $clLow = strtolower($claseNombre);
                            $tipo = null;
                            if (
                                strpos(
                                    $clLow,
                                    'candado obturador',
                                ) !== false
                            ) {
                                $tipo = 'Candado obturador';
                            } elseif (
                                strpos(
                                    $clLow,
                                    'cabeza de soplo',
                                ) !== false
                            ) {
                                $tipo = 'Cabeza de soplo';
                            } elseif (
                                strpos($clLow, 'embudo') !== false
                            ) {
                                $tipo = 'Embudo';
                            } elseif (
                                strpos($clLow, 'corona') !== false
                            ) {
                                $tipo = 'Corona';
                            } elseif (
                                strpos($clLow, 'plato') !== false
                            ) {
                                $tipo = 'Plato';
                            } elseif (
                                strpos($clLow, 'fondo') !== false
                            ) {
                                $tipo = 'Fondo';
                            } elseif (
                                strpos($clLow, 'obturador') !==
                                false
                            ) {
                                $tipo = 'Obturador';
                            } elseif (
                                strpos($clLow, 'molde') !== false
                            ) {
                                $tipo = 'Molde';
                            } elseif (
                                strpos($clLow, 'bombillo') !== false
                            ) {
                                $tipo = 'Bombillo';
                            } elseif (
                                strpos($clLow, 'pistones') !== false
                            ) {
                                $tipo = 'Pistones';
                            } elseif (
                                strpos($clLow, 'guías') !== false || strpos($clLow, 'guias') !== false
                            ) {
                                $tipo = 'Guías';
                            } elseif (
                                strpos($clLow, 'pistones') !== false
                            ) {
                                $tipo = 'Pistones';
                            } elseif (
                                strpos($clLow, 'guías') !== false || strpos($clLow, 'guias') !== false
                            ) {
                                $tipo = 'Guías';
                            }
                            if ($tipo) {
                                $baseOt = preg_replace('/_(?:(?:candado\s+obturador|cabeza\s+de\s+soplo|obturador|bombillo|embudo|corona|plato|molde|fondo|pistones|guías|guias)(?:_(?:candado\s+obturador|cabeza\s+de\s+soplo|obturador|bombillo|embudo|corona|plato|molde|fondo|pistones|guías|guias))*_)?R\d+$/iu', '', $targetReg->ot);
                                $isAprobado = \App\Models\LiberacionModeloFundicion::where(
                                    'ot',
                                    '!=',
                                    $targetReg->ot,
                                    'and'
                                )
                                    ->where(
                                        function ($q) use ($baseOt) {
                                            $q->where('ot', '=', $baseOt, 'and')
                                              ->where('ot', 'LIKE', $baseOt . '_R%', 'or')
                                              ->where('ot', 'LIKE', $baseOt . '_%_R%', 'or');
                                        },
                                        null,
                                        null,
                                        'and'
                                    )
                                    ->where(
                                        'tipo_modelo',
                                        '=',
                                        $tipo,
                                    )
                                    ->where(
                                        'decision',
                                        '=',
                                        'aprobar',
                                    )
                                    ->exists();
                                if ($isAprobado) {
                                    return false;
                                }
                                return true;
                            }
                            return false;
                        })
                        ->values()
                        ->toArray();
                    // Determinar si todas las clases activas tienen datos guardados (como borrador pendiente)
                    $todosGuardados = true;
                    $contClasesConDatos = 0;
                    foreach ($clasesActivas as $clName) {
                        $clLow = strtolower($clName);
                        $tipo = null;
                        if (
                            strpos($clLow, 'candado obturador') !==
                            false
                        ) {
                            $tipo = 'Candado obturador';
                        } elseif (
                            strpos($clLow, 'cabeza de soplo') !==
                            false
                        ) {
                            $tipo = 'Cabeza de soplo';
                        } elseif (
                            strpos($clLow, 'embudo') !== false
                        ) {
                            $tipo = 'Embudo';
                        } elseif (
                            strpos($clLow, 'corona') !== false
                        ) {
                            $tipo = 'Corona';
                        } elseif (
                            strpos($clLow, 'plato') !== false
                        ) {
                            $tipo = 'Plato';
                        } elseif (
                            strpos($clLow, 'fondo') !== false
                        ) {
                            $tipo = 'Fondo';
                        } elseif (
                            strpos($clLow, 'obturador') !== false
                        ) {
                            $tipo = 'Obturador';
                        } elseif (
                            strpos($clLow, 'molde') !== false
                        ) {
                            $tipo = 'Molde';
                        } elseif (
                            strpos($clLow, 'bombillo') !== false
                        ) {
                            $tipo = 'Bombillo';
                        } elseif (
                            strpos($clLow, 'pistones') !== false
                        ) {
                            $tipo = 'Pistones';
                        } elseif (
                            strpos($clLow, 'guías') !== false || strpos($clLow, 'guias') !== false
                        ) {
                            $tipo = 'Guías';
                        } elseif (
                            strpos($clLow, 'pistones') !== false
                        ) {
                            $tipo = 'Pistones';
                        } elseif (
                            strpos($clLow, 'guías') !== false || strpos($clLow, 'guias') !== false
                        ) {
                            $tipo = 'Guías';
                        }
                        if ($tipo) {
                            $hasData = \App\Models\LiberacionModeloFundicion::where(
                                'ot',
                                '=',
                                $targetReg->ot,
                            )
                                ->where('tipo_modelo', '=', $tipo)
                                ->where(function ($q) {
                                    $q->whereNotNull('user_id_calidad')
                                        ->orWhereNotNull('decision');
                                })
                                ->exists();
                            if (!$hasData) {
                                $todosGuardados = false;
                            } else {
                                $contClasesConDatos++;
                            }
                        }
                    }
                    if (empty($clasesActivas)) {
                        $todosGuardados = false;
                    }
                    // Determinar si hay al menos una clase con decisión de rechazo pendiente de enviar o con formato SCAR pendiente
                    $clasesAlertadasArr = \App\Models\LiberacionModeloFundicion::where('ot', '=', $targetReg->ot)
                        ->where('alerta_enviada', '=', 1)
                        ->pluck('tipo_modelo')
                        ->map(fn($item) => strtolower(trim($item)))
                        ->toArray();

                    $scarsOTAll = \App\Models\ScarModelo::where('ot', '=', $targetReg->ot)->get();
                    $scarsOT = $scarsOTAll->filter(function ($sc) use ($clasesAlertadasArr) {
                        if (strtolower((string) $sc->estatus) === 'alertado') {
                            return false;
                        }
                        if ($sc->tipo_modelo && in_array(strtolower(trim($sc->tipo_modelo)), $clasesAlertadasArr)) {
                            return false;
                        }
                        return true;
                    });
                    $hasScar = $scarsOT->isNotEmpty();
                    $scarTipos = $scarsOT->pluck('tipo_modelo')->filter()->map(fn($t) => strtolower(trim($t)))->toArray();

                    $hasRechazoBorrador = \App\Models\LiberacionModeloFundicion::where(
                        'ot',
                        '=',
                        $targetReg->ot,
                    )
                        ->where('decision', '=', 'rechazar')
                        ->where('alerta_enviada', '=', 0)
                        ->exists() || $hasScar;

                    $hasAprobadoBorrador = \App\Models\LiberacionModeloFundicion::where(
                        'ot',
                        '=',
                        $targetReg->ot,
                    )
                        ->where('decision', '=', 'aprobar')
                        ->where('alerta_enviada', '=', 0)
                        ->exists();

                    $borradorRechazado = \App\Models\LiberacionModeloFundicion::where(
                        'ot',
                        '=',
                        $targetReg->ot,
                    )
                        ->where('decision', '=', 'rechazar')
                        ->where('alerta_enviada', '=', 0)
                        ->first();

                    $tiposGuardados = \App\Models\LiberacionModeloFundicion::where(
                        'ot',
                        '=',
                        $targetReg->ot,
                    )
                        ->where('alerta_enviada', '=', 0)
                        ->get(['tipo_modelo', 'decision']);

                    $tiposAprobadosArr = [];
                    $tiposRechazadosArr = [];

                    foreach ($tiposGuardados as $tg) {
                        if (!$tg->tipo_modelo) continue;
                        $tLow = strtolower(trim($tg->tipo_modelo));
                        if ($tg->decision === 'aprobar') {
                            $tiposAprobadosArr[] = $tg->tipo_modelo;
                        } elseif ($tg->decision === 'rechazar' || in_array($tLow, $scarTipos)) {
                            $tiposRechazadosArr[] = $tg->tipo_modelo;
                        }
                    }

                    foreach ($scarsOT as $sc) {
                        if ($sc->tipo_modelo && !in_array($sc->tipo_modelo, $tiposRechazadosArr)) {
                            $tiposRechazadosArr[] = $sc->tipo_modelo;
                        }
                    }

                    $tiposAprobadosArr = array_values(array_unique($tiposAprobadosArr));
                    $tiposRechazadosArr = array_values(array_unique($tiposRechazadosArr));

                    if (!empty($tiposAprobadosArr) && !empty($tiposRechazadosArr)) {
                        $decisionGlobal = 'mixto';
                    } elseif (!empty($tiposRechazadosArr) || $hasRechazoBorrador) {
                        $decisionGlobal = 'rechazar';
                    } else {
                        $decisionGlobal = 'aprobar';
                    }

                    // Fallback de seguridad: si ambos arreglos están vacíos pero hay clases pendientes de alertar
                    if (empty($tiposAprobadosArr) && empty($tiposRechazadosArr) && !empty($clasesActivas)) {
                        foreach ($clasesActivas as $cAct) {
                            $cLow = strtolower(trim($cAct));
                            if (!in_array($cLow, $clasesAlertadasArr)) {
                                if ($decisionGlobal === 'rechazar') {
                                    $tiposRechazadosArr[] = $cAct;
                                } else {
                                    $tiposAprobadosArr[] = $cAct;
                                }
                            }
                        }
                    }

                    $tiposAprobadosJson = json_encode(array_values(array_unique($tiposAprobadosArr)));
                    $tiposRechazadosJson = json_encode(array_values(array_unique($tiposRechazadosArr)));

                    // Verificar si todas las clases activas ya fueron alertadas (proceso enviado a la siguiente etapa)
                    $clasesAlertadas = \App\Models\LiberacionModeloFundicion::where(
                        'ot',
                        '=',
                        $targetReg->ot,
                    )
                        ->where('alerta_enviada', '=', 1)
                        ->pluck('tipo_modelo')
                        ->map(fn($item) => strtolower(trim($item)))
                        ->toArray();
                    $clasesPendientesAlertar = array_filter(
                        $clasesActivas,
                        function ($c) use ($clasesAlertadas) {
                            $clLow = strtolower($c);
                            $tipo = null;
                            if (
                                strpos(
                                    $clLow,
                                    'candado obturador',
                                ) !== false
                            ) {
                                $tipo = 'candado obturador';
                            } elseif (
                                strpos(
                                    $clLow,
                                    'cabeza de soplo',
                                ) !== false
                            ) {
                                $tipo = 'cabeza de soplo';
                            } elseif (
                                strpos($clLow, 'embudo') !== false
                            ) {
                                $tipo = 'embudo';
                            } elseif (
                                strpos($clLow, 'corona') !== false
                            ) {
                                $tipo = 'corona';
                            } elseif (
                                strpos($clLow, 'plato') !== false
                            ) {
                                $tipo = 'plato';
                            } elseif (
                                strpos($clLow, 'fondo') !== false
                            ) {
                                $tipo = 'fondo';
                            } elseif (
                                strpos($clLow, 'obturador') !==
                                false
                            ) {
                                $tipo = 'obturador';
                            } elseif (
                                strpos($clLow, 'molde') !== false
                            ) {
                                $tipo = 'molde';
                            } elseif (
                                strpos($clLow, 'bombillo') !== false
                            ) {
                                $tipo = 'bombillo';
                            } elseif (
                                strpos($clLow, 'pistones') !== false
                            ) {
                                $tipo = 'pistones';
                            } elseif (
                                strpos($clLow, 'guías') !== false || strpos($clLow, 'guias') !== false
                            ) {
                                $tipo = 'guías';
                            } elseif (
                                strpos($clLow, 'pistones') !== false
                            ) {
                                $tipo = 'pistones';
                            } elseif (
                                strpos($clLow, 'guías') !== false || strpos($clLow, 'guias') !== false
                            ) {
                                $tipo = 'guías';
                            }
                            return $tipo &&
                                !in_array($tipo, $clasesAlertadas);
                        },
                    );
                    $etapaFinalizada =
                        empty($clasesPendientesAlertar) &&
                        (!empty($clasesActivas) || $hasFinalStatus) &&
                        !$hasRechazoBorrador &&
                        !$hasAprobadoBorrador;
                @endphp
                <div class="lib-calidad-card-body" style="padding: 18px 22px;">
                    @if ($etapaFinalizada)
                        @php
                            $alertaEnviadaEtapa = empty($clasesPendientesAlertar);
                        @endphp
                        <div class="lib-calidad-finalizado-banner"
                            style="background: #f0f9ff; border: 2px solid #0284c7; border-radius: 12px; padding: 20px; display: flex; align-items: center; justify-content: space-between; gap: 20px; width: 100%; box-shadow: 0 4px 10px rgba(2, 132, 199, 0.08);">
                            <div style="display: flex; align-items: center; gap: 20px;">
                                <img src="{{ asset('images/enviando.png') }}" style="width: 54px; height: 54px; flex-shrink: 0;"
                                    alt="Enviado">
                                <div>
                                    <h4 class="lib-calidad-card-prompt"
                                        style="color: #0369a1; margin-top: 0; margin-bottom: 8px; font-weight: 700; font-size: 1.1rem; font-family: 'Poppins', sans-serif;">
                                        Proceso de Liberación Finalizado
                                    </h4>
                                    <p
                                        style="color: #0c4a6e; margin: 0; font-size: 0.95rem; font-weight: 500; font-family: 'Poppins', sans-serif;">
                                        Las clases actuales han sido procesadas correctamente y están en espera de la siguiente
                                        etapa o nuevas clases.
                                    </p>
                                </div>
                            </div>
                            @if ($contClasesConDatos > 0)
                                <div class="lib-calidad-card-btns" style="flex-shrink: 0;">
                                    @if ($alertaEnviadaEtapa)
                                        <button class="btn-calidad-action btn-calidad-email cal-background-color-059669 cal-color-white"
                                            disabled
                                            style="pointer-events: none; opacity: 0.85; cursor: not-allowed; background-color: #059669 !important; border: 2px solid #047857 !important;"
                                            title="El correo de alerta ya ha sido enviado para estas clases">
                                            <img src="{{ asset('images/enviando.png') }}" alt="" style="filter: none !important;" />
                                            <span>Correo Enviado</span>
                                        </button>
                                    @elseif ($hasRechazoBorrador)
                                        @if (!$scarModelo)
                                            <button class="btn-calidad-action btn-calidad-borrador"
                                                onclick="abrirModalScar('{{ $targetReg->ot }}', '{{ $borradorRechazado->tipo_modelo }}', '{{ $borradorRechazado->motivo_rechazo }}')"
                                                title="Generar el formato de acción correctiva SCAR">
                                                <img src="{{ asset('images/pdf.png') }}" alt="" />
                                                <span>Generar Formato SCAR</span>
                                            </button>
                                        @else
                                            <button class="btn-calidad-action btn-calidad-email cal-background-color-dc2626 cal-color-white"
                                                onclick="abrirModalFinalizarCalidad('{{ $targetReg->ot }}', '{{ $decisionGlobal }}', {{ $tiposAprobadosJson }}, {{ $tiposRechazadosJson }})"
                                                title="Enviar alerta de calidad y notificar por correo">
                                                <img src="{{ asset('images/enviando.png') }}" alt="" style="filter: none !important;" />
                                                <span>Enviar Alerta</span>
                                            </button>
                                        @endif
                                    @else
                                        <button class="btn-calidad-action btn-calidad-iniciar"
                                            onclick="abrirModalFinalizarCalidad('{{ $targetReg->ot }}', '{{ $decisionGlobal }}', {{ $tiposAprobadosJson }}, {{ $tiposRechazadosJson }})"
                                            title="Enviar alerta de calidad y notificar por correo">
                                            <img src="{{ asset('images/enviando.png') }}" alt="" style="filter: none !important;" />
                                            <span>Enviar Alerta</span>
                                        </button>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @elseif ($targetReg->tiene_modelo)
                        <div class="lib-calidad-finalizado-banner"
                            style="background: #f0f9ff; border: 2px solid #0284c7; border-radius: 12px; padding: 20px; display: flex; align-items: center; justify-content: space-between; gap: 20px; width: 100%; box-shadow: 0 4px 10px rgba(2, 132, 199, 0.08);">
                            <div style="display: flex; align-items: center; gap: 20px;">
                                <img src="{{ asset('images/Espera.png') }}" style="width: 54px; height: 54px; flex-shrink: 0;"
                                    alt="Casting">
                                <div>
                                    <h4 class="lib-calidad-card-prompt"
                                        style="color: #0369a1; margin-top: 0; margin-bottom: 8px; font-weight: 700; font-size: 1.1rem; font-family: 'Poppins', sans-serif;">
                                        Proceso de Casting Iniciado
                                    </h4>
                                    <p
                                        style="color: #0c4a6e; margin: 0; font-size: 0.95rem; font-weight: 500; font-family: 'Poppins', sans-serif;">
                                        El modelo se encuentra en proceso de casting por parte de Almacén. No se requiere acción
                                        adicional de Calidad en esta etapa.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="lib-calidad-action-row"
                            style="display: flex; align-items: center; justify-content: space-between; gap: 20px; width: 100%;">
                            @php
                                $almacenEnvioAlerta = (bool) (
                                    !empty($targetReg->pre_orden_sent) ||
                                    !empty($targetReg->pre_orden_email_sent) ||
                                    !empty($targetReg->alert_sent_at) ||
                                    \App\Models\PreOrdenFundicion::where('ot', $targetReg->ot)->exists() ||
                                    $contClasesConDatos > 0
                                );
                            @endphp
                            <h4 class="lib-calidad-card-prompt"
                                style="margin: 0; font-size: 0.98em; font-weight: 600; color: #1e293b; font-family: 'Poppins', sans-serif;">
                                @if (!$almacenEnvioAlerta)
                                    En espera de que Almacén notifique el envío de clases por
                                    correo.
                                @elseif ($todosGuardados)
                                    @if ($hasRechazoBorrador)
                                        Borrador de rechazo guardado para esta OT. ¿Qué deseas
                                        hacer?
                                    @else
                                        Borrador de aprobación guardado para esta OT. ¿Qué deseas
                                        hacer?
                                    @endif
                                @elseif ($contClasesConDatos > 0)
                                    Proceso de liberación en curso (capturados:
                                    {{ $contClasesConDatos }} de {{ count($clasesActivas) }}).
                                @elseif (in_array($targetReg->calidad_revision_status, ['rechazado', 'calidad_rechazado']))
                                    El modelo fue rechazado antes. ¿Quieres revisarlo de nuevo?
                                @else
                                    ¿Qué deseas hacer con este modelo? ¿Lo apruebas o lo
                                    rechazas?
                                @endif
                            </h4>
                            <div class="lib-calidad-card-btns"
                                style="display: flex; align-items: center; gap: 10px; flex-shrink: 0;">
                                @if ($todosGuardados)
                                    <button class="btn-calidad-action btn-calidad-edit"
                                        onclick="abrirModalLiberacionUnificado('{{ $targetReg->ot }}', {{ json_encode($clasesActivas) }}, {{ json_encode($targetReg->ayudas_config ?? []) }})"
                                        title="Editar borrador del formato de liberación F-CCL-LDM">
                                        <img src="{{ asset('images/editar-informacion.png') }}" alt="" />
                                        <span>Editar Información</span>
                                    </button>
                                @else
                                    @php
                                        $btnDisabled = empty($clasesActivas) || !$almacenEnvioAlerta;
                                        $btnTitle = !$almacenEnvioAlerta
                                            ? 'En espera de que Almacén envíe el correo de notificación con las clases a Calidad'
                                            : (empty($clasesActivas)
                                                ? 'No hay clases enviadas por Almacén para revisar'
                                                : ($contClasesConDatos > 0
                                                    ? 'Continuar con el proceso de liberación'
                                                    : 'Iniciar el proceso de liberación'));
                                    @endphp
                                    <button
                                        class="btn-calidad-action btn-calidad-edit @if ($btnDisabled) cal-opacity-0-55 cal-cursor-not-allowed @endif"
                                        title="{{ $btnTitle }}" @if ($btnDisabled) disabled style="pointer-events: none;" @else
                                            onclick="abrirModalLiberacionUnificado('{{ $targetReg->ot }}', {{ json_encode($clasesActivas) }}, {{ json_encode($targetReg->ayudas_config ?? []) }})"
                                        @endif>
                                        <img src="{{ asset('images/Liberar.png') }}" alt="" />
                                        <span>{{ $contClasesConDatos > 0 ? 'Continuar con el proceso de liberación' : 'Empezar con el proceso de liberación' }}</span>
                                    </button>
                                @endif
                                @if ($contClasesConDatos > 0)
                                    @if (empty($clasesPendientesAlertar))
                                        <button class="btn-calidad-action btn-calidad-email cal-background-color-059669 cal-color-white"
                                            disabled
                                            style="pointer-events: none; opacity: 0.85; cursor: not-allowed; background-color: #059669 !important; border: 2px solid #047857 !important;"
                                            title="El correo de alerta ya ha sido enviado para estas clases">
                                            <img src="{{ asset('images/enviando.png') }}" alt="" style="filter: none !important;" />
                                            <span>Correo Enviado</span>
                                        </button>
                                    @elseif ($hasRechazoBorrador)
                                        @if (!$scarModelo)
                                            <button class="btn-calidad-action btn-calidad-borrador"
                                                onclick="abrirModalScar('{{ $targetReg->ot }}', '{{ $borradorRechazado->tipo_modelo }}', '{{ $borradorRechazado->motivo_rechazo }}')"
                                                title="Generar el formato de acción correctiva SCAR">
                                                <img src="{{ asset('images/pdf.png') }}" alt="" />
                                                <span>Generar Formato SCAR</span>
                                            </button>
                                        @else
                                            <button class="btn-calidad-action btn-calidad-email cal-background-color-dc2626 cal-color-white"
                                                onclick="abrirModalFinalizarCalidad('{{ $targetReg->ot }}', '{{ $decisionGlobal }}', {{ $tiposAprobadosJson }}, {{ $tiposRechazadosJson }})"
                                                title="Enviar alerta de calidad y notificar por correo">
                                                <img src="{{ asset('images/enviando.png') }}" alt="" />
                                                <span>Enviar Alerta</span>
                                            </button>
                                        @endif
                                    @else
                                        <button class="btn-calidad-action btn-calidad-iniciar"
                                            onclick="abrirModalFinalizarCalidad('{{ $targetReg->ot }}', '{{ $decisionGlobal }}', {{ $tiposAprobadosJson }}, {{ $tiposRechazadosJson }})"
                                            title="Enviar alerta de calidad y notificar por correo">
                                            <img src="{{ asset('images/enviando.png') }}" alt="" />
                                            <span>Enviar Alerta</span>
                                        </button>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            @if ($decisionGlobal === 'ninguno' && $targetReg->calidad_revision_status !== 'pendiente')
                <div class="lib-calidad-card" id="control-calidad-enviados-{{ md5($targetReg->ot) }}" class="cal-margin-top-15px">
                    <div
                        class="lib-calidad-card-header cal-background-linear-gradient-135deg-059669-047857 cal-border-bottom-2px-solid-rgba-5-150-105-0-5">
                        <img src="{{ asset('images/Quality.png') }}" alt="Calidad"
                            class="cal-width-38px cal-height-38px cal-object-fit-contain cal-flex-shrink-0 cal-filter-brightness-0-invert-1">
                        <div class="cal-overflow-hidden cal-flex-1">
                            <span class="lib-calidad-card-title cal-color-ffffff">Alertas
                                Enviadas &mdash; Calidad</span>
                            <span
                                class="lib-calidad-card-ot cal-color-rgba-255-255-255-0-9">{{ preg_replace('/_\d{8}_\d{6}_.*/', '', $targetReg->ot) }}</span>
                        </div>
                    </div>
                    <div
                        class="lib-calidad-card-body cal-background-color-f0fdf4 cal-border-1px-solid-bbf7d0 cal-border-top-none cal-padding-15px cal-text-align-center">
                        <img src="{{ asset('images/enviando.png') }}" alt="Enviado"
                            class="cal-width-48px cal-height-48px cal-margin-bottom-10px">
                        <h4 class="cal-color-065f46 cal-font-size-1-1em cal-margin-0-0-5px-0 cal-font-weight-600">
                            Alertas de Liberación Completadas</h4>
                        <p class="cal-color-064e3b cal-font-size-0-9em cal-margin-0">
                            Las
                            notificaciones de liberación
                            correspondientes a este modelo ya han
                            sido procesadas y enviadas exitosamente al
                            almacén.</p>
                    </div>
                </div>
            @endif
        </div>
    @endif
@endif
@if (in_array(Auth::user()->perfil, [1, 2, 3, 4, '1', '2', '3', '4']) && $isQualityFinalized)
    @php
        $libStatusClean = str_replace(
            'calidad_',
            '',
            $targetReg->calidad_revision_status,
        );
    @endphp
    @if (in_array($targetReg->calidad_revision_status, ['aprobado', 'calidad_aprobado', 'calidad_parcial', 'rechazado', 'calidad_rechazado', 'mixto', 'calidad_mixto']))
        @php
            $liberacionesAll = \App\Models\LiberacionModeloFundicion::where(
                'ot',
                $targetReg->ot,
            )->get();
            $aprobadosAll = $liberacionesAll
                ->where('decision', 'aprobar')
                ->pluck('tipo_modelo')
                ->toArray();
            $rechazadosAll = $liberacionesAll
                ->where('decision', 'rechazar')
                ->pluck('tipo_modelo')
                ->toArray();
            $liberacionesPend = $liberacionesAll->where(
                'alerta_enviada',
                false,
            );
            $aprobadosPend = $liberacionesPend
                ->where('decision', 'aprobar')
                ->pluck('tipo_modelo')
                ->toArray();
            $rechazadosPend = $liberacionesPend
                ->where('decision', 'rechazar')
                ->pluck('tipo_modelo')
                ->toArray();
            if (
                count($aprobadosPend) > 0 &&
                count($rechazadosPend) > 0
            ) {
                $decisionFinal = 'mixto';
            } elseif (count($aprobadosPend) > 0) {
                $decisionFinal = 'aprobar';
            } elseif (count($rechazadosPend) > 0) {
                $decisionFinal = 'rechazar';
            } else {
                $decisionFinal = 'ninguno';
            }
            $tiposAprobadosJson = json_encode(
                array_values($aprobadosPend),
            );
            $tiposRechazadosJson = json_encode(
                array_values($rechazadosPend),
            );
        @endphp
    @endif
@endif




</div>