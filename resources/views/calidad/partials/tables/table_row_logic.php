<?php
/** @var \App\Models\FundicionHistory $reg */
/** @var string $estado */
/** @var string $deptName */

$pendingChanges = is_string($reg->pending_almacen_changes) ? json_decode($reg->pending_almacen_changes, true) : ($reg->pending_almacen_changes ?? []);
$hasPendingChanges = !empty($pendingChanges);

$liberacionesReg = \App\Models\LiberacionModeloFundicion::where(
    'ot',
    $reg->ot,
)->get();
$hasAprobados = $liberacionesReg
    ->where('decision', 'aprobar')
    ->isNotEmpty();
$latestReproceso = null;
if ($reg->rechazos_procesados) {
    $latestReproceso = \App\Models\FundicionHistory::where(
        function ($q) use ($reg) {
            $q->where('ot', 'LIKE', $reg->ot . '_R%', 'and')
              ->where('ot', 'LIKE', $reg->ot . '_%_R%', 'or');
        },
        null,
        null,
        'and'
    )
        ->orderBy('id', 'desc')
        ->first();
}
// Corregimos la asignación para que la fila original NO actúe como si fuera el reproceso.
// Así mantenemos las clases independientes (Ej: original para Bombillo, R1 para Fondo).
$targetReg = $reg;
// ── RESOLVER TODOS LOS REGISTROS RELACIONADOS ──
$baseOtName = preg_replace('/_(?:(?:candado\s+obturador|cabeza\s+de\s+soplo|obturador|bombillo|embudo|corona|plato|molde|fondo|pistones|guías|guias)(?:_(?:candado\s+obturador|cabeza\s+de\s+soplo|obturador|bombillo|embudo|corona|plato|molde|fondo|pistones|guías|guias))*_)?R\d+$/iu', '', $reg->ot);
$relatedRecords = \App\Models\FundicionHistory::where('ot', '=', $baseOtName, 'or')
    ->where('ot', 'LIKE', $baseOtName . '_R%', 'or')
    ->where('ot', 'LIKE', $baseOtName . '_%_R%', 'or')
    ->get();
$allRelatedOtNames = $relatedRecords->pluck('ot')->toArray();
$allOtNames = $allRelatedOtNames;
// Obtener clases activas para filtrar archivos del historial
$activeClassesForOt = [];
$confSource =
    $targetReg->ayudas_config ?? ($reg->ayudas_config ?? null);
if (!empty($confSource)) {
    $configs = is_string($confSource)
        ? json_decode($confSource, true)
        : $confSource;
    if (is_array($configs)) {
        foreach ($configs as $val) {
            $val = strtolower($val);
            if (str_contains($val, 'opcional') && !str_contains($val, 'pistones') && !str_contains($val, 'guías') && !str_contains($val, 'guias')) {
                continue;
            }
            foreach (
                [
                    'candado obturador',
                    'cabeza de soplo',
                    'obturador',
                    'bombillo',
                    'embudo',
                    'corona',
                    'plato',
                    'molde',
                    'fondo',
                    'pistones',
                    'guías',
                    'guias',
                    'pistones',
                    'guías',
                    'guias',
                ]
                as $kc
            ) {
                if (strpos($val, $kc) !== false) {
                    $activeClassesForOt[] = $kc;
                    break;
                }
            }
        }
    }
}
if (empty($activeClassesForOt)) {
    $po = \App\Models\PreOrdenFundicion::where('ot', $reg->ot)->first();
    if ($po) {
        $filas = $po->filas;
        if (is_string($filas)) {
            $filas = json_decode($filas, true);
        }
        if (is_array($filas)) {
            foreach ($filas as $f) {
                $val = null;
                if (isset($f['clase'])) {
                    $val = strtolower($f['clase']);
                } elseif (isset($f['clase_nombre'])) {
                    $val = strtolower($f['clase_nombre']);
                } elseif (isset($f['tipo_modelo'])) {
                    $val = strtolower($f['tipo_modelo']);
                }
                if ($val) {
                    foreach (
                        [
                            'candado obturador',
                            'cabeza de soplo',
                            'obturador',
                            'bombillo',
                            'embudo',
                            'corona',
                            'plato',
                            'molde',
                            'fondo',
                            'pistones',
                            'guías',
                            'guias',
                            'pistones',
                            'guías',
                            'guias',
                        ]
                        as $kc
                    ) {
                        if (strpos($val, $kc) !== false) {
                            $activeClassesForOt[] = $kc;
                            break;
                        }
                    }
                }
            }
        }
    }
}
// Filtrar clases activas basándose en las decisiones de Calidad (solo si no se determinaron previamente)
if (empty($activeClassesForOt)) {
    $isReproceso = preg_match('/_R\d+$/i', $reg->ot);
    if ($isReproceso) {
        $classesInCurrentOtRaw = \App\Models\LiberacionModeloFundicion::where('ot', '=', $reg->ot)
            ->where('decision', '!=', 'pendiente')
            ->pluck('tipo_modelo')
            ->toArray();

        $parsedCurrent = [];
        foreach ($classesInCurrentOtRaw as $dc) {
            $parts = explode(',', strtolower($dc));
            foreach ($parts as $p) {
                $p = trim($p);
                if ($p !== '')
                    $parsedCurrent[] = $p;
            }
        }

        if (!empty($parsedCurrent)) {
            $activeClassesForOt = array_unique($parsedCurrent);
        } else {
            $prevOt = preg_replace_callback('/_R(\d+)$/i', function ($m) {
                $num = intval($m[1]) - 1;
                return $num > 0 ? '_R' . $num : '';
            }, $reg->ot);
            $rejectedPrevRaw = \App\Models\LiberacionModeloFundicion::where('ot', '=', $prevOt)
                ->where('decision', '=', 'rechazar')
                ->pluck('tipo_modelo')
                ->toArray();

            $parsedPrev = [];
            foreach ($rejectedPrevRaw as $dc) {
                $parts = explode(',', strtolower($dc));
                foreach ($parts as $p) {
                    $p = trim($p);
                    if ($p !== '')
                        $parsedPrev[] = $p;
                }
            }
            if (!empty($parsedPrev)) {
                $activeClassesForOt = array_unique($parsedPrev);
            }
        }
    } else {
        $hasLiberaciones = \App\Models\LiberacionModeloFundicion::where('ot', '=', $reg->ot)->exists();
        if ($hasLiberaciones) {
            $decidedClassesRaw = \App\Models\LiberacionModeloFundicion::where('ot', '=', $reg->ot)
                ->where('decision', '!=', 'pendiente')
                ->pluck('tipo_modelo')
                ->toArray();

            $parsedDecided = [];
            foreach ($decidedClassesRaw as $dc) {
                $parts = explode(',', strtolower($dc));
                foreach ($parts as $p) {
                    $p = trim($p);
                    if ($p !== '')
                        $parsedDecided[] = $p;
                }
            }

            if (!empty($parsedDecided)) {
                $activeClassesForOt = array_unique($parsedDecided);
            }
        }
    }
}

if (empty($activeClassesForOt)) {
    $activeClassesForOt = [
        'candado obturador',
        'cabeza de soplo',
        'obturador',
        'bombillo',
        'embudo',
        'corona',
        'plato',
        'molde',
        'fondo',
        'pistones',
        'guías',
        'guias',
    ];
}
$activeClassesForOt = array_values(array_unique($activeClassesForOt));
$rechazadosDibujos = [];
$rechazadosAyudas = [];
$rechazadosOtros = [];
$otParaRechazados = $reg->ot;
if (preg_match('/_R\d+$/i', $reg->ot)) {
    $otParaRechazados = preg_replace_callback(
        '/_R(\d+)$/i',
        function ($m) {
            $num = intval($m[1]) - 1;
            return $num > 0 ? '_R' . $num : '';
        },
        $reg->ot,
    );
}
$rejectedClassesForOt = \App\Models\LiberacionModeloFundicion::where(
    'ot',
    $otParaRechazados,
)
    ->where('decision', 'rechazar')
    ->pluck('tipo_modelo')
    ->map(fn($t) => mb_strtolower($t, 'UTF-8'))
    ->toArray();

$reprocesoTienePreOrden = false;
if (preg_match('/_R\d+$/i', $reg->ot) && !empty($rejectedClassesForOt)) {
    $reprocesoTienePreOrden = (
        $reg->pre_orden_sent
        || $reg->pre_orden_email_sent
        || \App\Models\PreOrdenFundicion::where('ot', $reg->ot)->exists()
    );
    if ($reprocesoTienePreOrden) {
        $rejectedClassesForOt = [];
    }
}
$archivos = [];
$dibujoBaseNames = [];
foreach ($relatedRecords as $relRec) {
    $relArchivos = is_array($relRec->almacen_archivos)
        ? $relRec->almacen_archivos
        : [];
    foreach ($relArchivos as $archivo) {
        $base = basename($archivo);
        $fileLower = strtolower($archivo);
        if (
            strpos($fileLower, 'ayudas_visuales') !== false ||
            strpos($fileLower, 'ayudas-visuales') !== false ||
            strpos($fileLower, 'preordenes') !== false
        ) {
            continue;
        }
        $knownClasses = [
            'candado obturador',
            'cabeza de soplo',
            'obturador',
            'bombillo',
            'embudo',
            'corona',
            'plato',
            'molde',
            'fondo',
            'pistones',
            'guías',
            'guias',
        ];
        $hasKnownClass = false;
        foreach ($knownClasses as $kc) {
            if (strpos($fileLower, $kc) !== false) {
                $hasKnownClass = true;
                break;
            }
        }
        if ($hasKnownClass) {
            // Los dibujos SIEMPRE se muestran, incluso si la clase fue rechazada.
            // Son documentos de referencia, no se ocultan ni mueven a rechazados.
            $matchesActive = false;
            foreach ($activeClassesForOt as $ac) {
                if (strpos($fileLower, $ac) !== false) {
                    $matchesActive = true;
                    break;
                }
            }
            if (!$matchesActive) {
                continue;
            }
        } else {
            if ($relRec->ot !== $reg->ot) {
                continue;
            }
        }
        if (!in_array($base, $dibujoBaseNames)) {
            $archivos[] = [
                'nombre' => $archivo,
                'ot' => $relRec->ot,
                'tipo' => 'dibujo',
            ];
            $dibujoBaseNames[] = $base;
        }
    }
}
$countDibujos = count($archivos);
$ayudasArchivos = [];
$otrosArchivos = [];
$baseNames = $dibujoBaseNames;
$normBaseNames = array_map(function ($b) {
    return strtolower(preg_replace('/[\s_]+/', '', $b));
}, $baseNames);
// --- NUEVO: Escanear ayudas visuales globales desde AYUDAS_FUNDICION ---
$ayudasGlobalesBase = 'DOCUMENTACION_GIS/AYUDAS_FUNDICION';
foreach ($activeClassesForOt as $activeClass) {
    $classNameProper = ucfirst(strtolower($activeClass));
    $candidateDirs = [
        $ayudasGlobalesBase . '/' . $classNameProper,
        $ayudasGlobalesBase . '/' . $classNameProper . '/Fundicion',
    ];
    foreach ($candidateDirs as $globalClassDir) {
        if (
            \Illuminate\Support\Facades\Storage::disk('local')->exists(
                $globalClassDir,
            )
        ) {
            $files = \Illuminate\Support\Facades\Storage::disk(
                'local',
            )->files($globalClassDir);
            foreach ($files as $f) {
                $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
                if ($ext === 'pdf') {
                    $base = basename($f);
                    $normBase = strtolower(preg_replace('/[\s_]+/', '', $base));
                    if (!in_array($normBase, $normBaseNames)) {
                        $ayudaData = [
                            'nombre' => $classNameProper . '/' . $base,
                            'url' => route('ayudas_fundicion.serve', [
                                'clase' => $classNameProper,
                                'archivo' => $base,
                            ]),
                            'tipo' => 'ayuda',
                            'ot' => $reg->ot,
                        ];

                        // Las ayudas globales SIEMPRE se muestran, sin importar si la clase fue rechazada.
                        // Son documentos de referencia permanentes.
                        $ayudasArchivos[] = $ayudaData;

                        $baseNames[] = $base;
                        $normBaseNames[] = $normBase;
                    }
                }
            }
        }
    }
}
$liberacionesPath = storage_path('app/public/liberaciones_pdf');
foreach ($allOtNames as $otName) {
    $otNameSanitized = trim(
        preg_replace(
            '/[\/\\\\]/',
            '',
            preg_replace('/\.\.+/', '', $otName),
        ),
    );
    // 1. Escanear ayudas visuales de Almacen (Legacy y Nueva Estructura)
    $ayudasDir =
        'DOCUMENTACION_GIS/ALMACEN_FUNDICION/' .
        $otNameSanitized .
        '/ayudas_visuales';
    $almacenRootScan =
        'DOCUMENTACION_GIS/ALMACEN_FUNDICION/' . $otNameSanitized;
    $scanDirs = [];
    if (
        \Illuminate\Support\Facades\Storage::disk('local')->exists(
            $ayudasDir,
        )
    ) {
        $scanDirs[] = [
            'path' => $ayudasDir,
            'base_dir' => $ayudasDir,
        ];
    }
    foreach (
        [
            'Candado obturador',
            'Cabeza de soplo',
            'Obturador',
            'Bombillo',
            'Embudo',
            'Corona',
            'Plato',
            'Molde',
            'Fondo',
            'Pistones',
            'Guías',
            'Guias',
        ]
        as $claseDir
    ) {
        $newAyDir =
            $almacenRootScan . '/' . $claseDir . '/Ayudas_Visuales';
        if (
            \Illuminate\Support\Facades\Storage::disk('local')->exists(
                $newAyDir,
            )
        ) {
            $scanDirs[] = [
                'path' => $newAyDir,
                'base_dir' => $almacenRootScan,
            ];
        }
        $legacyClaseAyDir = $ayudasDir . '/' . $claseDir;
        if (
            \Illuminate\Support\Facades\Storage::disk('local')->exists(
                $legacyClaseAyDir,
            )
        ) {
            $scanDirs[] = [
                'path' => $legacyClaseAyDir,
                'base_dir' => $ayudasDir,
            ];
        }
    }
    foreach ($scanDirs as $sInfo) {
        if (
            \Illuminate\Support\Facades\Storage::disk('local')->exists(
                $sInfo['path'],
            )
        ) {
            $files = \Illuminate\Support\Facades\Storage::disk(
                'local',
            )->allFiles($sInfo['path']);
            foreach ($files as $f) {
                $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
                $isPdf = $ext === 'pdf';
                $isImage = in_array($ext, [
                    'jpg',
                    'jpeg',
                    'png',
                    'gif',
                    'webp',
                ]);
                if (!$isPdf && !$isImage) {
                    continue;
                }
                $fNorm = str_replace('\\', '/', $f);
                $dirNorm = str_replace('\\', '/', $sInfo['base_dir']);
                $relativePath = ltrim(
                    str_replace($dirNorm, '', $fNorm),
                    '/',
                );
                $base = basename($relativePath);
                $fileLower = strtolower($relativePath);
                $knownClasses = [
                    'candado obturador',
                    'cabeza de soplo',
                    'obturador',
                    'bombillo',
                    'embudo',
                    'corona',
                    'plato',
                    'molde',
                    'fondo',
                    'pistones',
                    'guías',
                    'guias',
                    'pistones',
                    'guías',
                    'guias',
                ];
                $hasKnownClass = false;
                foreach ($knownClasses as $kc) {
                    if (strpos($fileLower, $kc) !== false) {
                        $hasKnownClass = true;
                        break;
                    }
                }
                if ($hasKnownClass) {
                    // Los dibujos/ayudas de clases rechazadas SIEMPRE se muestran.
                    // Solo verificar que la clase pertenece a las activas de esta OT.
                    $matchesActive = false;
                    foreach ($activeClassesForOt as $ac) {
                        if (strpos($fileLower, $ac) !== false) {
                            $matchesActive = true;
                            break;
                        }
                    }
                    if (!$matchesActive) {
                        continue;
                    }
                } else {
                    if ($otName !== $reg->ot) {
                        continue;
                    }
                }
                $normBase = strtolower(preg_replace('/[\s_]+/', '', $base));
                if (str_starts_with($relativePath, 'preordenes/')) {
                    if (!in_array($normBase, $normBaseNames)) {
                        $otrosArchivos[] = [
                            'nombre' => $relativePath,
                            'url' => route('calidad.fundicion.serve', [
                                'ot' => $otName,
                                'archivo' => $relativePath,
                                'tipo' => 'otro',
                            ]),
                            'tipo' => $isImage ? 'imagen' : 'otro',
                            'ot' => $otName,
                            'origin' => 'otro',
                            'owner' => 'almacen',
                        ];
                        $baseNames[] = $base;
                        $normBaseNames[] = $normBase;
                    }
                } elseif ($isPdf) {
                    if (!in_array($normBase, $normBaseNames)) {
                        $ayudasArchivos[] = [
                            'nombre' => $relativePath,
                            'url' => route('calidad.fundicion.serve', [
                                'ot' => $otName,
                                'archivo' => $relativePath,
                                'tipo' => 'ayuda',
                            ]),
                            'tipo' => 'ayuda',
                            'ot' => $otName,
                        ];
                        $baseNames[] = $base;
                        $normBaseNames[] = $normBase;
                    }
                }
            }
        }
    }
    // 2. Escanear ayudas visuales de Calidad
    $calidadDir =
        'DOCUMENTACION_GIS/CALIDAD_FUNDICION/' .
        $otNameSanitized .
        '/ayudas_visuales/preordenes';
    if (
        \Illuminate\Support\Facades\Storage::disk('local')->exists(
            $calidadDir,
        )
    ) {
        $files = \Illuminate\Support\Facades\Storage::disk(
            'local',
        )->allFiles($calidadDir);
        foreach ($files as $f) {
            $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
            $isPdf = $ext === 'pdf';
            $isImage = in_array($ext, [
                'jpg',
                'jpeg',
                'png',
                'gif',
                'webp',
            ]);
            if (!$isPdf && !$isImage) {
                continue;
            }
            $fNorm = str_replace('\\', '/', $f);
            $dirNorm = str_replace('\\', '/', $calidadDir);
            $relativePath = ltrim(
                str_replace($dirNorm, '', $fNorm),
                '/',
            );
            $base = basename($relativePath);
            $fileLower = strtolower($relativePath);
            $knownClasses = [
                'candado obturador',
                'cabeza de soplo',
                'obturador',
                'bombillo',
                'embudo',
                'corona',
                'plato',
                'molde',
                'fondo',
                'pistones',
                'guías',
                'guias',
            ];
            $hasKnownClass = false;
            foreach ($knownClasses as $kc) {
                if (strpos($fileLower, $kc) !== false) {
                    $hasKnownClass = true;
                    break;
                }
            }
            if ($hasKnownClass) {
                // Ayudas de preordenes SIEMPRE se muestran (son documentos de referencia).
                $matchesActive = false;
                foreach ($activeClassesForOt as $ac) {
                    if (strpos($fileLower, $ac) !== false) {
                        $matchesActive = true;
                        break;
                    }
                }
                if (!$matchesActive) {
                    // Si no coincide con activas, verificar con rechazadas (para que sigan visibles)
                    foreach ($rejectedClassesForOt as $rc) {
                        if (strpos($fileLower, $rc) !== false) {
                            $matchesActive = true;
                            break;
                        }
                    }
                }
                if (!$matchesActive) {
                    continue;
                }
            } else {
                if ($otName !== $reg->ot) {
                    continue;
                }
            }
            $normBase = strtolower(preg_replace('/[\s_]+/', '', $base));
            if (!in_array($normBase, $normBaseNames)) {
                $origin = 'otro';
                if (
                    strpos($relativePath, 'documentos_aprobados') !==
                    false
                ) {
                    $origin = 'aprobado';
                } elseif (
                    strpos($relativePath, 'documentos_rechazados') !==
                    false
                ) {
                    $origin = 'rechazado';
                }
                $relativePathWithPrefix = 'preordenes/' . $relativePath;
                $otrosArchivos[] = [
                    'nombre' => $relativePathWithPrefix,
                    'url' => route('calidad.fundicion.serve', [
                        'ot' => $otName,
                        'archivo' => $relativePathWithPrefix,
                        'tipo' => 'otro',
                        'origin' => $origin,
                    ]),
                    'tipo' => $isImage ? 'imagen' : 'otro',
                    'ot' => $otName,
                    'origin' => $origin,
                    'owner' => 'calidad',
                ];
                $baseNames[] = $base;
                $normBaseNames[] = $normBase;
            }
        }
    }
    // 2b. Escanear Documentos_Aprobados, Documentos_Rechazados, Preordenes y Escaneados (SOLO PARA LA OT ACTUAL DEL REGISTRO)
    if ($otName === $reg->ot) {
        $newDirs = [
        [
            'dir' =>
                'DOCUMENTACION_GIS/ALMACEN_FUNDICION/' .
                $otNameSanitized .
                '/Documentos_Aprobados',
            'origin' => 'aprobado',
            'prefix' => 'Documentos_Aprobados/',
            'owner' => 'almacen',
        ],
        [
            'dir' =>
                'DOCUMENTACION_GIS/ALMACEN_FUNDICION/' .
                $otNameSanitized .
                '/Documentos_Rechazados',
            'origin' => 'rechazado',
            'prefix' => 'Documentos_Rechazados/',
            'owner' => 'almacen',
        ],
        [
            'dir' =>
                'DOCUMENTACION_GIS/CALIDAD_FUNDICION/' .
                $otNameSanitized .
                '/Documentos_Aprobados',
            'origin' => 'aprobado',
            'prefix' => 'Documentos_Aprobados/',
            'owner' => 'calidad',
        ],
        [
            'dir' =>
                'DOCUMENTACION_GIS/CALIDAD_FUNDICION/' .
                $otNameSanitized .
                '/Documentos_Rechazados',
            'origin' => 'rechazado',
            'prefix' => 'Documentos_Rechazados/',
            'owner' => 'calidad',
        ],
    ];
    // --- NUEVO: ESCANEAR PREORDENES Y DOCUMENTOS POR CLASE ---
    foreach (['Candado obturador', 'Cabeza de soplo', 'Obturador', 'Bombillo', 'Embudo', 'Corona', 'Plato', 'Molde', 'Fondo', 'Pistones', 'Guías', 'Guias'] as $claseDir) {
        $cUnderscore = strtoupper(preg_replace('/[^a-zA-Z0-9_\-]/', '_', $claseDir));
        $cUpper = strtoupper($claseDir);
        $cTitle = $claseDir;

        $variants = array_unique([$cUnderscore, $cUpper, $cTitle]);

        foreach ($variants as $vDir) {
            // Preordenes (nueva estructura)
            $newDirs[] = [
                'dir' => 'DOCUMENTACION_GIS/ALMACEN_FUNDICION/' . $otNameSanitized . '/' . $vDir . '/Preordenes',
                'origin' => 'aprobado',
                'prefix' => $vDir . '/Preordenes/',
                'owner' => 'almacen'
            ];
            $newDirs[] = [
                'dir' => 'DOCUMENTACION_GIS/CALIDAD_FUNDICION/' . $otNameSanitized . '/' . $vDir . '/Preordenes',
                'origin' => 'aprobado',
                'prefix' => $vDir . '/Preordenes/',
                'owner' => 'calidad'
            ];
            // Preordenes (legacy Preordenes_Fundicion)
            $newDirs[] = [
                'dir' => 'DOCUMENTACION_GIS/ALMACEN_FUNDICION/' . $otNameSanitized . '/' . $vDir . '/Preordenes_Fundicion',
                'origin' => 'aprobado',
                'prefix' => $vDir . '/Preordenes_Fundicion/',
                'owner' => 'almacen'
            ];
            $newDirs[] = [
                'dir' => 'DOCUMENTACION_GIS/CALIDAD_FUNDICION/' . $otNameSanitized . '/' . $vDir . '/' . $vDir . '/Preordenes_Fundicion',
                'origin' => 'aprobado',
                'prefix' => $vDir . '/Preordenes_Fundicion/',
                'owner' => 'calidad'
            ];

            // Documentos Aprobados (nueva estructura)
            $newDirs[] = [
                'dir' => 'DOCUMENTACION_GIS/ALMACEN_FUNDICION/' . $otNameSanitized . '/' . $vDir . '/Documentos_Aprobados',
                'origin' => 'aprobado',
                'prefix' => $vDir . '/Documentos_Aprobados/',
                'owner' => 'almacen'
            ];
            $newDirs[] = [
                'dir' => 'DOCUMENTACION_GIS/CALIDAD_FUNDICION/' . $otNameSanitized . '/' . $vDir . '/Documentos_Aprobados',
                'origin' => 'aprobado',
                'prefix' => $vDir . '/Documentos_Aprobados/',
                'owner' => 'calidad'
            ];
            // Documentos Aprobados (legacy /Almacen y /Calidad)
            foreach (['Almacen', 'Calidad'] as $dept) {
                $newDirs[] = [
                    'dir' => 'DOCUMENTACION_GIS/ALMACEN_FUNDICION/' . $otNameSanitized . '/' . $vDir . '/Documentos_Aprobados/' . $dept,
                    'origin' => 'aprobado',
                    'prefix' => $vDir . '/Documentos_Aprobados/' . $dept . '/',
                    'owner' => 'almacen'
                ];
                $newDirs[] = [
                    'dir' => 'DOCUMENTACION_GIS/CALIDAD_FUNDICION/' . $otNameSanitized . '/' . $vDir . '/Documentos_Aprobados/' . $dept,
                    'origin' => 'aprobado',
                    'prefix' => $vDir . '/Documentos_Aprobados/' . $dept . '/',
                    'owner' => 'calidad'
                ];
            }

            // Documentos Rechazados (nueva estructura)
            $newDirs[] = [
                'dir' => 'DOCUMENTACION_GIS/ALMACEN_FUNDICION/' . $otNameSanitized . '/' . $vDir . '/Documentos_Rechazados',
                'origin' => 'rechazado',
                'prefix' => $vDir . '/Documentos_Rechazados/',
                'owner' => 'almacen'
            ];
            $newDirs[] = [
                'dir' => 'DOCUMENTACION_GIS/CALIDAD_FUNDICION/' . $otNameSanitized . '/' . $vDir . '/Documentos_Rechazados',
                'origin' => 'rechazado',
                'prefix' => $vDir . '/Documentos_Rechazados/',
                'owner' => 'calidad'
            ];
            // Documentos Rechazados (legacy)
            foreach (['Almacen', 'Calidad'] as $dept) {
                $newDirs[] = [
                    'dir' => 'DOCUMENTACION_GIS/ALMACEN_FUNDICION/' . $otNameSanitized . '/' . $vDir . '/Documentos_Rechazados/' . $dept,
                    'origin' => 'rechazado',
                    'prefix' => $vDir . '/Documentos_Rechazados/' . $dept . '/',
                    'owner' => 'almacen'
                ];
                $newDirs[] = [
                    'dir' => 'DOCUMENTACION_GIS/CALIDAD_FUNDICION/' . $otNameSanitized . '/' . $vDir . '/Documentos_Rechazados/' . $dept,
                    'origin' => 'rechazado',
                    'prefix' => $vDir . '/Documentos_Rechazados/' . $dept . '/',
                    'owner' => 'calidad'
                ];
            }

            // Documentos Escaneados (nueva estructura)
            foreach (['ESCANEADOS', 'Escaneados', 'escaneados', 'DOCUMENTOS_ESCANEADOS', 'Documentos_Escaneados', 'documentos_escaneados'] as $eSub) {
                $newDirs[] = [
                    'dir' => 'DOCUMENTACION_GIS/ALMACEN_FUNDICION/' . $otNameSanitized . '/' . $vDir . '/' . $eSub,
                    'origin' => 'aprobado',
                    'prefix' => $vDir . '/' . $eSub . '/',
                    'owner' => 'almacen'
                ];
                $newDirs[] = [
                    'dir' => 'DOCUMENTACION_GIS/CALIDAD_FUNDICION/' . $otNameSanitized . '/' . $vDir . '/' . $eSub,
                    'origin' => 'aprobado',
                    'prefix' => $vDir . '/' . $eSub . '/',
                    'owner' => 'calidad'
                ];
                foreach (['Almacen', 'Calidad'] as $dept) {
                    $newDirs[] = [
                        'dir' => 'DOCUMENTACION_GIS/ALMACEN_FUNDICION/' . $otNameSanitized . '/' . $vDir . '/' . $eSub . '/' . $dept,
                        'origin' => 'aprobado',
                        'prefix' => $vDir . '/' . $eSub . '/' . $dept . '/',
                        'owner' => 'almacen'
                    ];
                    $newDirs[] = [
                        'dir' => 'DOCUMENTACION_GIS/CALIDAD_FUNDICION/' . $otNameSanitized . '/' . $vDir . '/' . $vDir . '/' . $eSub . '/' . $dept,
                        'origin' => 'aprobado',
                        'prefix' => $vDir . '/' . $eSub . '/' . $dept . '/',
                        'owner' => 'calidad'
                    ];
                }
            }
        }
    }

    foreach ($newDirs as $dirInfo) {
        $targetDir = $dirInfo['dir'];
        $origin = $dirInfo['origin'];
        $prefix = $dirInfo['prefix'];
        if (
            \Illuminate\Support\Facades\Storage::disk('local')->exists(
                $targetDir,
            )
        ) {
            $files = \Illuminate\Support\Facades\Storage::disk(
                'local',
            )->allFiles($targetDir);
            foreach ($files as $f) {
                $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
                $isPdf = $ext === 'pdf';
                $isImage = in_array($ext, [
                    'jpg',
                    'jpeg',
                    'png',
                    'gif',
                    'webp',
                ]);
                if (!$isPdf && !$isImage) {
                    continue;
                }
                $fNorm = str_replace('\\', '/', $f);
                $dirNorm = str_replace('\\', '/', $targetDir);
                $relativePath = ltrim(
                    str_replace($dirNorm, '', $fNorm),
                    '/',
                );
                $base = basename($relativePath);
                $fileLower = strtolower($relativePath);
                $knownClasses = [
                    'candado obturador',
                    'cabeza de soplo',
                    'obturador',
                    'bombillo',
                    'embudo',
                    'corona',
                    'plato',
                    'molde',
                    'fondo',
                    'pistones',
                    'guías',
                    'guias',
                    'pistones',
                    'guías',
                    'guias',
                ];
                $fileClasses = [];
                foreach ($knownClasses as $kc) {
                    if (strpos($fileLower, $kc) !== false) {
                        $fileClasses[] = $kc;
                    }
                }
                if (!empty($fileClasses)) {
                    // Verificar que la clase del archivo pertenece a esta OT (activas o rechazadas)
                    $hasInactiveClass = false;
                    foreach ($fileClasses as $fc) {
                        if (!in_array($fc, $activeClassesForOt) && !in_array($fc, $rejectedClassesForOt)) {
                            $hasInactiveClass = true;
                            break;
                        }
                    }
                    if ($hasInactiveClass) {
                        continue;
                    }
                    // NO clasificar por clase rechazada aquí: el $origin viene del directorio escaneado.
                    // Un ConfirmacionModelo en Documentos_Aprobados/ SIEMPRE es aprobado, aunque
                    // mencione una clase rechazada en su nombre.
                } else {
                    if ($otName !== $reg->ot) {
                        continue;
                    }
                }
                $normBase = strtolower(preg_replace('/[\s_]+/', '', $base));
                if (!in_array($normBase, $normBaseNames)) {
                    $relativePathWithPrefix = $prefix . $relativePath;
                    $otrosArchivos[] = [
                        'nombre' => $relativePathWithPrefix,
                        'url' => route('calidad.fundicion.serve', [
                            'ot' => $otName,
                            'archivo' => $relativePathWithPrefix,
                            'tipo' => 'otro',
                            'origin' => $origin,
                        ]),
                        'tipo' => $isImage ? 'imagen' : 'otro',
                        'ot' => $otName,
                        'origin' => $origin,
                        'owner' => $dirInfo['owner'],
                    ];
                    $baseNames[] = $base;
                    $normBaseNames[] = $normBase;
                }
            }
        }
    }
    // 3. Buscar PDFs generados en public/liberaciones_pdf (LDM y SCAR)
    $otSanitizada = preg_replace('/[^\w\s\-]/', '', $otName);
    $otSanitizada = preg_replace('/[\s]+/', '_', trim($otSanitizada));
    if (file_exists($liberacionesPath)) {
        // Buscar LDM y RDM PDFs generados para ESTA OT en public/liberaciones_pdf
        $otLow = mb_strtolower($otSanitizada, 'UTF-8');
        $otNameLow = mb_strtolower($otName, 'UTF-8');
        $ldmFiles = array_merge(
            glob("{$liberacionesPath}/*{$otSanitizada}*.pdf") ?: [],
            glob("{$liberacionesPath}/F*CCL*{$otSanitizada}*.pdf") ?: []
        );
        foreach (array_unique($ldmFiles) as $f) {
            $base = basename($f);
            $fileLower = mb_strtolower($base, 'UTF-8');
            if (!str_contains($fileLower, $otLow) && !str_contains($fileLower, $otNameLow)) {
                continue;
            }
            $normBase = strtolower(preg_replace('/[\s_]+/', '', $base));
            if (!in_array($normBase, $normBaseNames)) {
                $isRechazado = strpos($fileLower, 'rdm') !== false || strpos($fileLower, 'rechazado') !== false;
                $origin = $isRechazado ? 'rechazado' : 'aprobado';
                $itemData = [
                    'nombre' => $base,
                    'url' => route('calidad.fundicion.serve', [
                        'ot' => $otName,
                        'archivo' => $base,
                        'tipo' => 'liberacion',
                        'origin' => $origin,
                    ]),
                    'tipo' => 'liberacion',
                    'ot' => $otName,
                    'origin' => $origin,
                    'owner' => 'calidad',
                    'tipo_origen' => 'digital',
                ];
                if ($isRechazado) {
                    $rechazadosOtros[] = $itemData;
                } else {
                    $otrosArchivos[] = $itemData;
                }
                $baseNames[] = $base;
                $normBaseNames[] = $normBase;
            }
        }
        // Buscar SCAR PDFs (digital y firmado)
        $scarPattern = "{$liberacionesPath}/F-CCL-SCAR_*_{$otSanitizada}*.pdf";
        $scarPattern2 = "{$liberacionesPath}/F-CCL-SCAR_{$otSanitizada}.pdf";
        $scarFiles = array_merge(
            glob($scarPattern) ?: [],
            glob($scarPattern2) ?: [],
        );
        foreach (array_unique($scarFiles) as $f) {
            $base = basename($f);
            $normBase = strtolower(preg_replace('/[\s_]+/', '', $base));
            if (!in_array($normBase, $normBaseNames)) {
                $itemData = [
                    'nombre' => $base,
                    'url' => route('calidad.fundicion.serve', [
                        'ot' => $otName,
                        'archivo' => $base,
                        'tipo' => 'liberacion',
                        'origin' => 'rechazado',
                    ]),
                    'tipo' => 'liberacion',
                    'ot' => $otName,
                    'origin' => 'rechazado',
                    'owner' => 'calidad',
                    'tipo_origen' => 'digital',
                ];
                $rechazadosOtros[] = $itemData;
                $baseNames[] = $base;
                $normBaseNames[] = $normBase;
            }
        }
    }
}
}
// Aplicar filtros de visibilidad según perfil de usuario
$userPerfil = Auth::user()->perfil;
if ($userPerfil != 1 && $userPerfil != 2 && $userPerfil != 3) {
    $filteredOtros = [];
    foreach ($otrosArchivos as $archivo) {
        $nameLow = strtolower($archivo['nombre']);
        $isPreorden =
            ((in_array($archivo['tipo'], ['otro', 'imagen']) ||
                str_starts_with($archivo['nombre'], 'preordenes/')) &&
                strpos($nameLow, 'ldm') === false &&
                strpos($nameLow, 'rdm') === false &&
                strpos($nameLow, 'scar') === false &&
                strpos($nameLow, 'confirmacion') === false &&
                strpos($nameLow, 'cfm') === false &&
                strpos($nameLow, 'liberacion') === false);
        if ($userPerfil == 4 || $userPerfil == 3) {
            // Calidad o Master
            if (!$isPreorden) {
                $filteredOtros[] = $archivo;
            } else {
                $fileHistory = $relatedRecords->firstWhere(
                    'ot',
                    $archivo['ot'],
                );
                $hasPoInDb = \App\Models\PreOrdenFundicion::where('ot', $archivo['ot'])->exists();
                if (
                    $fileHistory && (
                        $fileHistory->pre_orden_email_sent ||
                        $fileHistory->pre_orden_sent ||
                        $fileHistory->tiene_modelo ||
                        !empty($fileHistory->alert_sent_at) ||
                        $hasPoInDb
                    )
                ) {
                    $filteredOtros[] = $archivo;
                }
            }
        } elseif ($userPerfil == 5) {
            // Almacén
            // Almacén solo ve PDFs de Calidad si se envió la alerta (aprobado o scar alertado)
            if (
                $isPreorden ||
                strpos($nameLow, 'confirmacion') !== false
            ) {
                $filteredOtros[] = $archivo;
            } else {
                $fileHistory = $relatedRecords->firstWhere(
                    'ot',
                    $archivo['ot'],
                );
                $status = $fileHistory
                    ? $fileHistory->calidad_revision_status
                    : null;
                $calidadAlertaEnviada =
                    in_array($status, [
                        'calidad_aprobado',
                        'calidad_rechazado',
                        'calidad_mixto',
                        'calidad_parcial',
                        'casting_aprobado',
                    ]) ||
                    \App\Models\ScarModelo::where(
                        'ot',
                        '=',
                        $archivo['ot'],
                    )
                        ->where('estatus', '=', 'alertado')
                        ->exists();
                if ($calidadAlertaEnviada) {
                    $filteredOtros[] = $archivo;
                }
            }
        }
    }
    $otrosArchivos = $filteredOtros;
}
$archivosAprobados = [];
$archivosRechazados = [];
foreach ($otrosArchivos as $archivo) {
    $nameLow = strtolower($archivo['nombre']);
    $baseLow = strtolower(basename($archivo['nombre']));
    if (strpos($nameLow, 'documentos_rechazados') !== false) {
        $archivosRechazados[] = $archivo;
    } elseif (strpos($nameLow, 'documentos_aprobados') !== false) {
        $archivosAprobados[] = $archivo;
    } elseif (
        strpos($baseLow, 'pre-orden') !== false ||
        strpos($baseLow, 'preorden') !== false ||
        strpos($baseLow, 'confirmacion') !== false ||
        strpos($baseLow, 'escaneado') !== false ||
        strpos($baseLow, 'pfm') !== false ||
        strpos($baseLow, 'cfm') !== false ||
        strpos($baseLow, 'efm') !== false ||
        strpos($baseLow, 'pfc') !== false ||
        strpos($nameLow, 'preordenes/') !== false ||
        strpos($nameLow, 'preorden_casting') !== false ||
        strpos($nameLow, 'preorden_modelo') !== false ||
        strpos($nameLow, 'confirmacion_modelo') !== false ||
        strpos($nameLow, 'fdldm') !== false ||
        strpos($nameLow, 'f_ccl_ldm') !== false
    ) {
        $archivosAprobados[] = $archivo;
    } elseif (
        strpos($baseLow, 'rechazado') !== false ||
        strpos($baseLow, 'scar') !== false ||
        strpos($baseLow, 'rdm') !== false ||
        strpos($baseLow, 'fdrdm') !== false ||
        strpos($nameLow, 'f_ccl_rdm') !== false ||
        strpos($nameLow, 'f_ccl_scar') !== false
    ) {
        $archivosRechazados[] = $archivo;
    } else {
        $archivosAprobados[] = $archivo;
    }
}
$clasesActivas = collect($targetReg->ayudas_config ?? [])
    ->filter(fn($c) => !str_contains(strtolower($c), 'opcional') || str_contains(strtolower($c), 'pistones') || str_contains(strtolower($c), 'guías') || str_contains(strtolower($c), 'guias'))
    ->filter(function ($claseNombre) use ($targetReg) {
        $clLow = strtolower($claseNombre);
        $tipo = null;
        if (strpos($clLow, 'candado obturador') !== false) {
            $tipo = 'Candado obturador';
        } elseif (strpos($clLow, 'cabeza de soplo') !== false) {
            $tipo = 'Cabeza de soplo';
        } elseif (strpos($clLow, 'embudo') !== false) {
            $tipo = 'Embudo';
        } elseif (strpos($clLow, 'corona') !== false) {
            $tipo = 'Corona';
        } elseif (strpos($clLow, 'plato') !== false) {
            $tipo = 'Plato';
        } elseif (strpos($clLow, 'fondo') !== false) {
            $tipo = 'Fondo';
        } elseif (strpos($clLow, 'obturador') !== false) {
            $tipo = 'Obturador';
        } elseif (strpos($clLow, 'molde') !== false) {
            $tipo = 'Molde';
        } elseif (strpos($clLow, 'bombillo') !== false) {
            $tipo = 'Bombillo';
        } elseif (strpos($clLow, 'pistones') !== false) {
            $tipo = 'Pistones';
        } elseif (strpos($clLow, 'guías') !== false || strpos($clLow, 'guias') !== false) {
            $tipo = 'Guías';
        } elseif (strpos($clLow, 'pistones') !== false) {
            $tipo = 'Pistones';
        } elseif (strpos($clLow, 'guías') !== false || strpos($clLow, 'guias') !== false) {
            $tipo = 'Guías';
        }
        if ($tipo) {
            $baseOt = preg_replace('/_(?:(?:candado\s+obturador|cabeza\s+de\s+soplo|obturador|bombillo|embudo|corona|plato|molde|fondo|pistones|guías|guias)(?:_(?:candado\s+obturador|cabeza\s+de\s+soplo|obturador|bombillo|embudo|corona|plato|molde|fondo|pistones|guías|guias))*_)?R\d+$/iu', '', $targetReg->ot);
            $isAprobado = \App\Models\LiberacionModeloFundicion::where(
                function ($q) use ($baseOt) {
                    $q->where('ot', '=', $baseOt, 'and')
                      ->where('ot', 'LIKE', $baseOt . '_R%', 'or')
                      ->where('ot', 'LIKE', $baseOt . '_%_R%', 'or');
                },
                null,
                null,
                'and'
            )
                ->where('ot', '!=', $targetReg->ot, 'and')
                ->where('tipo_modelo', '=', $tipo, 'and')
                ->where('estado', '=', 'aprobado', 'and')
                ->exists();
            return !$isAprobado;
        }
        return true;
    })
    ->values()
    ->toArray();
$todosGuardados = true;
foreach ($clasesActivas as $clName) {
    $clLow = strtolower($clName);
    $tipo = null;
    if (strpos($clLow, 'candado obturador') !== false) {
        $tipo = 'Candado obturador';
    } elseif (strpos($clLow, 'cabeza de soplo') !== false) {
        $tipo = 'Cabeza de soplo';
    } elseif (strpos($clLow, 'embudo') !== false) {
        $tipo = 'Embudo';
    } elseif (strpos($clLow, 'corona') !== false) {
        $tipo = 'Corona';
    } elseif (strpos($clLow, 'plato') !== false) {
        $tipo = 'Plato';
    } elseif (strpos($clLow, 'fondo') !== false) {
        $tipo = 'Fondo';
    } elseif (strpos($clLow, 'obturador') !== false) {
        $tipo = 'Obturador';
    } elseif (strpos($clLow, 'molde') !== false) {
        $tipo = 'Molde';
    } elseif (strpos($clLow, 'bombillo') !== false) {
        $tipo = 'Bombillo';
    } elseif (strpos($clLow, 'pistones') !== false) {
        $tipo = 'Pistones';
    } elseif (strpos($clLow, 'guías') !== false || strpos($clLow, 'guias') !== false) {
        $tipo = 'Guías';
    } elseif (strpos($clLow, 'pistones') !== false) {
        $tipo = 'Pistones';
    } elseif (strpos($clLow, 'guías') !== false || strpos($clLow, 'guias') !== false) {
        $tipo = 'Guías';
    }
    if ($tipo) {
        $hasData = \App\Models\LiberacionModeloFundicion::where(
            'ot',
            '=',
            $targetReg->ot,
        )
            ->where('tipo_modelo', '=', $tipo)
            ->exists();
        if (!$hasData) {
            $todosGuardados = false;
            break;
        }
    }
}
if (empty($clasesActivas)) {
    $todosGuardados = false;
}
$countAprobados = count($archivosAprobados);
$countRechazados = count($archivosRechazados);
$countAyudas = count($ayudasArchivos);
$countOtros = count($otrosArchivos);
$isReprocesoBadge = (bool) preg_match('/_R\d+$/i', $reg->ot);
// En Calidad, todos los archivos base se obtienen de 4 arrays disjuntos:
// $archivos (Dibujos/), $ayudasArchivos (Ayudas/), $archivosAprobados (Otros_Documentos/),
// y $archivosRechazados (Documentos_Rechazados/).
// Al sumar estos 4, evitamos doble-conteo.
$count =
    count($archivos) +
    count($rechazadosDibujos) +
    count($ayudasArchivos) +
    count($rechazadosAyudas) +
    count($archivosAprobados) +
    count($archivosRechazados) +
    count($rechazadosOtros);
$hasFinalStatus = in_array($targetReg->calidad_revision_status, [
    'calidad_aprobado',
    'calidad_rechazado',
    'calidad_mixto',
    'casting_aprobado',
]);
$isQualityFinalized = $hasFinalStatus && $todosGuardados;
$showQualityCard =
    in_array(Auth::user()->perfil, ['1', '3', '4', 1, 3, 4]) &&
    $estado === 'activa' &&
    $targetReg->calidad_revision_status !== 'casting_aprobado';
$hasFilesOrControl = $count > 0 || $showQualityCard;
// ── CALCULAR APROBADOS Y RECHAZADOS DEL ÁšLTIMO VEREDICTO DE CADA CLASE ──
$liberacionesAll = \App\Models\LiberacionModeloFundicion::whereIn(
    'ot',
    $allRelatedOtNames,
)->get();
$latestLiberacionesByClass = [];
foreach ($liberacionesAll as $lib) {
    $tipo = $lib->tipo_modelo;
    $libOt = $lib->ot;
    preg_match('/_R(\d+)$/', $libOt, $matches);
    $suffixNum = isset($matches[1]) ? (int) $matches[1] : 0;
    if (
        !isset($latestLiberacionesByClass[$tipo]) ||
        $suffixNum > $latestLiberacionesByClass[$tipo]['suffix']
    ) {
        $latestLiberacionesByClass[$tipo] = [
            'lib' => $lib,
            'suffix' => $suffixNum,
        ];
    }
}
$aprobadosRaw = [];
$rechazadosRaw = [];
foreach ($latestLiberacionesByClass as $tipo => $data) {
    $lib = $data['lib'];
    if ($lib->decision === 'aprobar') {
        $aprobadosRaw[] = $tipo;
    } elseif ($lib->decision === 'rechazar') {
        $rechazadosRaw[] = $tipo;
    }
}
// Filtrar por clases activas en esta versión de la OT (desde la pre-orden de modelo)
// (Ya calculado al inicio en activeClassesForOt)
$aprobados = array_filter($aprobadosRaw, function ($clase) use ($activeClassesForOt) {
    return in_array(strtolower($clase), $activeClassesForOt);
});
$rechazados = array_filter($rechazadosRaw, function ($clase) use ($activeClassesForOt) {
    return in_array(strtolower($clase), $activeClassesForOt);
});
$aprobadosNorm = array_map('strtolower', $aprobados);
$clasesFabricacion = array_values(array_diff($activeClassesForOt, $aprobadosNorm));

$dibujosCasting = array_values(array_filter($archivos, function ($d) use ($aprobadosNorm) {
    $nameLow = strtolower($d['nombre']);
    foreach ($aprobadosNorm as $ap) {
        if ($ap !== '' && strpos($nameLow, $ap) !== false)
            return true;
    }
    return false;
}));

$dibujosModelo = array_values(array_filter($archivos, function ($d) use ($clasesFabricacion) {
    $nameLow = strtolower($d['nombre']);
    foreach ($clasesFabricacion as $cf) {
        if ($cf !== '' && strpos($nameLow, $cf) !== false)
            return true;
    }
    return false;
}));

$ayudasCasting = array_values(array_filter($ayudasArchivos, function ($a) use ($aprobadosNorm) {
    $nameLow = strtolower($a['nombre']);
    foreach ($aprobadosNorm as $ap) {
        if ($ap !== '' && strpos($nameLow, $ap) !== false)
            return true;
    }
    return false;
}));

$ayudasModelo = array_values(array_filter($ayudasArchivos, function ($a) use ($clasesFabricacion) {
    $nameLow = strtolower($a['nombre']);
    foreach ($clasesFabricacion as $cf) {
        if ($cf !== '' && strpos($nameLow, $cf) !== false)
            return true;
    }
    return false;
}));

$countDibujos = count($dibujosModelo);
$countAyudas = count($ayudasModelo);
$tieneFabricacion = count($clasesFabricacion) > 0;

// Sub-processing for files row
$rechazadosDibujos = [];
$rechazadosAyudas = [];
$rechazadosOtros = $rechazadosOtros ?? [];
foreach ($archivosRechazados as $rArchivo) {
    $nameLow = strtolower($rArchivo['nombre']);
    $ext = pathinfo($nameLow, PATHINFO_EXTENSION);
    $isImg = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    $rArchivo['ot'] = $rArchivo['ot'] ?? $targetReg->ot;
    $rArchivo['tipo'] = $rArchivo['tipo'] ?? ($isImg ? 'imagen' : 'otro');

    if (strpos($nameLow, 'scar') !== false || strpos($nameLow, 'f_ccl_scar') !== false || strpos($nameLow, 'f_ccl_rdm') !== false || strpos($nameLow, 'foto') !== false) {
        $rechazadosOtros[] = $rArchivo;
    } elseif (strpos($nameLow, 'ayudas_visuales') !== false || strpos($nameLow, 'ayudas-visuales') !== false || $isImg) {
        $rArchivo['tipo'] = $rArchivo['tipo'] === 'otro' ? 'ayuda' : $rArchivo['tipo'];
        $rechazadosAyudas[] = $rArchivo;
    } elseif (strpos($nameLow, 'dibujos') !== false || strpos($nameLow, 'dibujo') !== false) {
        $rechazadosDibujos[] = $rArchivo;
    } else {
        $rechazadosOtros[] = $rArchivo;
    }
}

// Documentos y Dibujos recibidos de Almacén (NUNCA se ocultan aunque 1 de 3 clases esté aprobada/rechazada)
$allValidDibujos = $archivos;
$allValidAyudas = $ayudasArchivos;

// Clasificar $archivosAprobados en Almacén (preórdenes/confirmaciones) vs Calidad (Formatos de Liberación Aprobados F-CCL-LDM)
$almacenAprobadosDocs = [];
$calidadAprobadosLdm = [];

foreach ($archivosAprobados as $doc) {
    $baseLow = strtolower(basename($doc['nombre']));
    if (strpos($baseLow, 'ldm') !== false || strpos($baseLow, 'f-ccl-ldm') !== false || strpos($baseLow, 'liberacion') !== false) {
        $calidadAprobadosLdm[] = $doc;
    } else {
        $almacenAprobadosDocs[] = $doc;
    }
}

$hasAlmacenGroup = (count($allValidDibujos) > 0 || count($allValidAyudas) > 0 || count($almacenAprobadosDocs) > 0);
$hasAprobadosGroup = (count($calidadAprobadosLdm) > 0);
$hasRechazadosGroup = (count($rechazadosDibujos) > 0 || count($rechazadosAyudas) > 0 || count($rechazadosOtros) > 0);