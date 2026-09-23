<?php

namespace App\ViewModels;

use App\Models\FundicionHistory;

class CalidadTableRowViewModel
{
    /** @var mixed */
    public $activeClassesForOt;
    /** @var mixed */
    public $allOtNames;
    /** @var mixed */
    public $allRelatedOtNames;
    /** @var mixed */
    public $allValidAyudas;
    /** @var mixed */
    public $allValidDibujos;
    /** @var mixed */
    public $almacenAprobadosDocs;
    /** @var mixed */
    public $aprobados;
    /** @var mixed */
    public $aprobadosNorm;
    /** @var mixed */
    public $aprobadosRaw;
    /** @var mixed */
    public $archivos;
    /** @var mixed */
    public $archivosAprobados;
    /** @var mixed */
    public $archivosRechazados;
    /** @var mixed */
    public $ayudasArchivos;
    /** @var mixed */
    public $ayudasCasting;
    /** @var mixed */
    public $ayudasGlobalesBase;
    /** @var mixed */
    public $ayudasModelo;
    /** @var mixed */
    public $baseNames;
    /** @var mixed */
    public $baseOtName;
    /** @var mixed */
    public $calidadAprobadosLdm;
    /** @var mixed */
    public $clasesActivas;
    /** @var mixed */
    public $clasesFabricacion;
    /** @var mixed */
    public $confSource;
    /** @var mixed */
    public $count;
    /** @var mixed */
    public $countAprobados;
    /** @var mixed */
    public $countAyudas;
    /** @var mixed */
    public $countDibujos;
    /** @var mixed */
    public $countOtros;
    /** @var mixed */
    public $countRechazados;
    /** @var mixed */
    public $dibujoBaseNames;
    /** @var mixed */
    public $dibujosCasting;
    /** @var mixed */
    public $dibujosModelo;
    /** @var mixed */
    public $hasAlmacenGroup;
    /** @var mixed */
    public $hasAprobados;
    /** @var mixed */
    public $hasAprobadosGroup;
    /** @var mixed */
    public $hasFilesOrControl;
    /** @var mixed */
    public $hasFinalStatus;
    /** @var mixed */
    public $hasPendingChanges;
    /** @var mixed */
    public $hasRechazadosGroup;
    /** @var mixed */
    public $isQualityFinalized;
    /** @var mixed */
    public $isReprocesoBadge;
    /** @var mixed */
    public $latestLiberacionesByClass;
    /** @var mixed */
    public $latestReproceso;
    /** @var mixed */
    public $liberacionesAll;
    /** @var mixed */
    public $liberacionesPath;
    /** @var mixed */
    public $liberacionesReg;
    /** @var mixed */
    public $normBaseNames;
    /** @var mixed */
    public $otParaRechazados;
    /** @var mixed */
    public $otrosArchivos;
    /** @var mixed */
    public $pendingChanges;
    /** @var mixed */
    public $rechazados;
    /** @var mixed */
    public $rechazadosAyudas;
    /** @var mixed */
    public $rechazadosDibujos;
    /** @var mixed */
    public $rechazadosOtros;
    /** @var mixed */
    public $rechazadosRaw;
    /** @var mixed */
    public $rejectedClassesForOt;
    /** @var mixed */
    public $relatedRecords;
    /** @var mixed */
    public $reprocesoTienePreOrden;
    /** @var mixed */
    public $showQualityCard;
    /** @var mixed */
    public $targetReg;
    /** @var mixed */
    public $tieneFabricacion;
    /** @var mixed */
    public $todosGuardados;
    /** @var mixed */
    public $userPerfil;
    /** @var mixed */
    public $reg;
    /** @var mixed */
    public $estado;
    /** @var mixed */
    public $deptName;

    public function __construct(FundicionHistory $reg, string $estado, string $deptName)
    {
        $this->reg = $reg;
        $this->estado = $estado;
        $this->deptName = $deptName;
        $this->process();
    }

    private function process(): void
    {
        $this->pendingChanges = is_string($this->reg->pending_almacen_changes) ? json_decode($this->reg->pending_almacen_changes, true) : ($this->reg->pending_almacen_changes ?? []);
        $this->hasPendingChanges = !empty($this->pendingChanges);

        $this->liberacionesReg = \App\Models\LiberacionModeloFundicion::where(
            'ot',
            $this->reg->ot,
        )->get();
        $this->hasAprobados = $this->liberacionesReg
            ->where('decision', 'aprobar')
            ->isNotEmpty();
        $this->latestReproceso = null;
        if ($this->reg->rechazos_procesados) {
            $this->latestReproceso = FundicionHistory::where(
                function ($q)  {
                    $q->where('ot', 'LIKE', $this->reg->ot . '_R%', 'and')
                        ->where('ot', 'LIKE', $this->reg->ot . '_%_R%', 'or');
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
        $this->targetReg = $this->reg;
        // ── RESOLVER TODOS LOS REGISTROS RELACIONADOS ──
        $this->baseOtName = preg_replace('/_(?:(?:candado\s+obturador|cabeza\s+de\s+soplo|obturador|bombillo|embudo|corona|plato|molde|fondo|pistones|guías|guias)(?:_(?:candado\s+obturador|cabeza\s+de\s+soplo|obturador|bombillo|embudo|corona|plato|molde|fondo|pistones|guías|guias))*_)?R\d+$/iu', '', $this->reg->ot);
        $this->relatedRecords = FundicionHistory::where('ot', '=', $this->baseOtName, 'or')
            ->where('ot', 'LIKE', $this->baseOtName . '_R%', 'or')
            ->where('ot', 'LIKE', $this->baseOtName . '_%_R%', 'or')
            ->get();
        $this->allRelatedOtNames = $this->relatedRecords->pluck('ot')->toArray();
        $this->allOtNames = $this->allRelatedOtNames;
        // Obtener clases activas para filtrar archivos del historial
        $this->activeClassesForOt = [];
        $this->confSource =
            $this->targetReg->ayudas_config ?? ($this->reg->ayudas_config ?? null);
        if (!empty($this->confSource)) {
            $configs = is_string($this->confSource)
                ? json_decode($this->confSource, true)
                : $this->confSource;
            if (is_array($configs)) {
                foreach ($configs as $val) {
                    $val = strtolower($val);
                    if (str_contains($val, 'opcional') && !str_contains($val, 'pistones') && !str_contains($val, 'guías') && !str_contains($val, 'guias')) {
                        continue;
                    }
                    foreach (
                        [
                                    '1 - MOLDES',
                                    '2 - BOMBILLO',
                                    '3 - EMBUDO',
                                    '4 - CORONA',
                                    '5 - PLATO',
                                    '6 - FONDO',
                                    '7 - OBTURADOR',
                                    '8 - CABEZA DE SOPLO',
                                    '9 - CANDADO OBTURADOR',
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
                                ]
                        as $kc
                    ) {
                        if (strpos($val, $kc) !== false) {
                            $this->activeClassesForOt[] = $kc;
                            break;
                        }
                    }
                }
            }
        }
        if (empty($this->activeClassesForOt)) {
            $po = \App\Models\PreOrdenFundicion::where('ot', $this->reg->ot)->first();
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
                                    '1 - MOLDES',
                                    '2 - BOMBILLO',
                                    '3 - EMBUDO',
                                    '4 - CORONA',
                                    '5 - PLATO',
                                    '6 - FONDO',
                                    '7 - OBTURADOR',
                                    '8 - CABEZA DE SOPLO',
                                    '9 - CANDADO OBTURADOR',
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
                                ]
                                as $kc
                            ) {
                                if (strpos($val, $kc) !== false) {
                                    $this->activeClassesForOt[] = $kc;
                                    break;
                                }
                            }
                        }
                    }
                }
            }
        }
        // Filtrar clases activas basándose en las decisiones de Calidad o liberaciones registradas
        if (empty($this->activeClassesForOt)) {
            $isReproceso = preg_match('/_R\d+$/i', $this->reg->ot);
            if ($isReproceso) {
                $classesInCurrentOtRaw = \App\Models\LiberacionModeloFundicion::where('ot', '=', $this->reg->ot)
                    ->whereNotNull('tipo_modelo')
                    ->pluck('tipo_modelo')
                    ->toArray();

                $parsedCurrent = [];
                foreach ($classesInCurrentOtRaw as $dc) {
                    $parts = explode(',', strtolower($dc));
                    foreach ($parts as $p) {
                        $p = trim($p);
                        if ($p !== '') {
                            foreach ([
                                    '1 - MOLDES',
                                    '2 - BOMBILLO',
                                    '3 - EMBUDO',
                                    '4 - CORONA',
                                    '5 - PLATO',
                                    '6 - FONDO',
                                    '7 - OBTURADOR',
                                    '8 - CABEZA DE SOPLO',
                                    '9 - CANDADO OBTURADOR',
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
                                ] as $kc) {
                                if (strpos($p, $kc) !== false) {
                                    $parsedCurrent[] = $kc;
                                    break;
                                }
                            }
                        }
                    }
                }

                if (!empty($parsedCurrent)) {
                    $this->activeClassesForOt = array_unique($parsedCurrent);
                } else {
                    $prevOt = preg_replace_callback('/_R(\d+)$/i', function ($m) {
                        $num = intval($m[1]) - 1;
                        return $num > 0 ? '_R' . $num : '';
                    }, $this->reg->ot);
                    $rejectedPrevRaw = \App\Models\LiberacionModeloFundicion::where('ot', '=', $prevOt)
                        ->where('decision', '=', 'rechazar')
                        ->pluck('tipo_modelo')
                        ->toArray();

                    $parsedPrev = [];
                    foreach ($rejectedPrevRaw as $dc) {
                        $parts = explode(',', strtolower($dc));
                        foreach ($parts as $p) {
                            $p = trim($p);
                            if ($p !== '') {
                                foreach ([
                                    '1 - MOLDES',
                                    '2 - BOMBILLO',
                                    '3 - EMBUDO',
                                    '4 - CORONA',
                                    '5 - PLATO',
                                    '6 - FONDO',
                                    '7 - OBTURADOR',
                                    '8 - CABEZA DE SOPLO',
                                    '9 - CANDADO OBTURADOR',
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
                                ] as $kc) {
                                    if (strpos($p, $kc) !== false) {
                                        $parsedPrev[] = $kc;
                                        break;
                                    }
                                }
                            }
                        }
                    }
                    if (!empty($parsedPrev)) {
                        $this->activeClassesForOt = array_unique($parsedPrev);
                    }
                }
            } else {
                $classesRaw = \App\Models\LiberacionModeloFundicion::where('ot', '=', $this->reg->ot)
                    ->whereNotNull('tipo_modelo')
                    ->pluck('tipo_modelo')
                    ->toArray();

                $parsedClasses = [];
                foreach ($classesRaw as $dc) {
                    $parts = explode(',', strtolower($dc));
                    foreach ($parts as $p) {
                        $p = trim($p);
                        if ($p !== '') {
                            foreach ([
                                    '1 - MOLDES',
                                    '2 - BOMBILLO',
                                    '3 - EMBUDO',
                                    '4 - CORONA',
                                    '5 - PLATO',
                                    '6 - FONDO',
                                    '7 - OBTURADOR',
                                    '8 - CABEZA DE SOPLO',
                                    '9 - CANDADO OBTURADOR',
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
                                ] as $kc) {
                                if (strpos($p, $kc) !== false) {
                                    $parsedClasses[] = $kc;
                                    break;
                                }
                            }
                        }
                    }
                }

                if (!empty($parsedClasses)) {
                    $this->activeClassesForOt = array_unique($parsedClasses);
                }
            }
        } else {
            // Si ya teníamos clases en ayudas_config o preorden, asegurar también incluir cualquier tipo registrado en LiberacionModeloFundicion
            $classesRaw = \App\Models\LiberacionModeloFundicion::where('ot', '=', $this->reg->ot)
                ->whereNotNull('tipo_modelo')
                ->pluck('tipo_modelo')
                ->toArray();
            foreach ($classesRaw as $dc) {
                $parts = explode(',', strtolower($dc));
                foreach ($parts as $p) {
                    $p = trim($p);
                    if ($p !== '') {
                        foreach ([
                                    '1 - MOLDES',
                                    '2 - BOMBILLO',
                                    '3 - EMBUDO',
                                    '4 - CORONA',
                                    '5 - PLATO',
                                    '6 - FONDO',
                                    '7 - OBTURADOR',
                                    '8 - CABEZA DE SOPLO',
                                    '9 - CANDADO OBTURADOR',
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
                                ] as $kc) {
                            if (strpos($p, $kc) !== false && !in_array($kc, $this->activeClassesForOt)) {
                                $this->activeClassesForOt[] = $kc;
                                break;
                            }
                        }
                    }
                }
            }
        }

        if (empty($this->activeClassesForOt)) {
            preg_match('/OT (\d+)/i', $this->reg->ot, $otMatches);
            if (!empty($otMatches[1])) {
                $otId = (int) $otMatches[1];
                $clasesDb = \App\Models\Clase::where('id_ot', $otId)->pluck('nombre')->toArray();
                if (!empty($clasesDb)) {
                    $parsedDb = [];
                    $otNameSanitized = str_replace([':', '/', '\\', '*', '?', '"', '<', '>', '|'], '_', $this->reg->ot);
                    foreach ($clasesDb as $cdb) {
                        $norm = \App\Models\Clase::normalizeClassName($cdb);
                        if ($norm) {
                            $cdbTrim = trim($cdb);
                            $hasFolder = \Illuminate\Support\Facades\Storage::disk('local')->exists('DOCUMENTACION_GIS/DIBUJOS_FUNDICION/' . $otNameSanitized . '/' . $cdbTrim) ||
                                \Illuminate\Support\Facades\Storage::disk('local')->exists('DOCUMENTACION_GIS/ALMACEN_FUNDICION/' . $otNameSanitized . '/' . $cdbTrim) ||
                                \Illuminate\Support\Facades\Storage::disk('local')->exists('DOCUMENTACION_GIS/CALIDAD_FUNDICION/' . $otNameSanitized . '/' . $cdbTrim);

                            if ($hasFolder) {
                                $parsedDb[] = strtolower($norm);
                            }
                        }
                    }
                    if (!empty($parsedDb)) {
                        $this->activeClassesForOt = array_unique($parsedDb);
                    }
                }
            }

            if (empty($this->activeClassesForOt)) {
                $this->activeClassesForOt = [
                                    '1 - MOLDES',
                                    '2 - BOMBILLO',
                                    '3 - EMBUDO',
                                    '4 - CORONA',
                                    '5 - PLATO',
                                    '6 - FONDO',
                                    '7 - OBTURADOR',
                                    '8 - CABEZA DE SOPLO',
                                    '9 - CANDADO OBTURADOR',
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
        }
        $this->activeClassesForOt = array_values(array_unique($this->activeClassesForOt));
        $this->rechazadosDibujos = [];
        $this->rechazadosAyudas = [];
        $this->rechazadosOtros = [];
        $this->otParaRechazados = $this->reg->ot;
        if (preg_match('/_R\d+$/i', $this->reg->ot)) {
            $this->otParaRechazados = preg_replace_callback(
                '/_R(\d+)$/i',
                function ($m) {
                    $num = intval($m[1]) - 1;
                    return $num > 0 ? '_R' . $num : '';
                },
                $this->reg->ot,
            );
        }
        $this->rejectedClassesForOt = \App\Models\LiberacionModeloFundicion::where(
            'ot',
            $this->otParaRechazados,
        )
            ->where('decision', 'rechazar')
            ->pluck('tipo_modelo')
            ->map(fn($t) => mb_strtolower($t, 'UTF-8'))
            ->toArray();

        $this->reprocesoTienePreOrden = false;
        if (preg_match('/_R\d+$/i', $this->reg->ot) && !empty($this->rejectedClassesForOt)) {
            $this->reprocesoTienePreOrden = (
                $this->reg->pre_orden_sent
                || $this->reg->pre_orden_email_sent
                || \App\Models\PreOrdenFundicion::where('ot', $this->reg->ot)->exists()
            );
            if ($this->reprocesoTienePreOrden) {
                $this->rejectedClassesForOt = [];
            }
        }
        $this->archivos = [];
        $this->dibujoBaseNames = [];
        foreach ($this->relatedRecords as $relRec) {
            $relArchivos = is_array($relRec->almacen_archivos)
                ? $relRec->almacen_archivos
                : [];
            foreach ($relArchivos as $archivo) {
                $base = basename($archivo);
                $fileLower = strtolower($archivo);
                $baseLower = strtolower($base);

                $isNonDrawing = (
                    strpos($fileLower, 'ayudas_visuales') !== false ||
                    strpos($fileLower, 'ayudas-visuales') !== false ||
                    strpos($fileLower, 'preordenes') !== false ||
                    strpos($fileLower, 'preorden') !== false ||
                    strpos($fileLower, 'escaneados') !== false ||
                    strpos($fileLower, 'documentos_aprobados') !== false ||
                    strpos($fileLower, 'documentos_rechazados') !== false ||
                    strpos($baseLower, 'f_alm_') !== false ||
                    strpos($baseLower, 'f_ccl_') !== false ||
                    strpos($baseLower, 'cfm') !== false ||
                    strpos($baseLower, 'efm') !== false ||
                    strpos($baseLower, 'pfm') !== false ||
                    strpos($baseLower, 'pfc') !== false ||
                    strpos($baseLower, 'efc') !== false ||
                    strpos($baseLower, 'ldm') !== false ||
                    strpos($baseLower, 'rdm') !== false ||
                    strpos($baseLower, 'scar') !== false
                );

                if ($isNonDrawing) {
                    continue;
                }
                $knownClasses = [
                                    '1 - MOLDES',
                                    '2 - BOMBILLO',
                                    '3 - EMBUDO',
                                    '4 - CORONA',
                                    '5 - PLATO',
                                    '6 - FONDO',
                                    '7 - OBTURADOR',
                                    '8 - CABEZA DE SOPLO',
                                    '9 - CANDADO OBTURADOR',
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
                    foreach ($this->activeClassesForOt as $ac) {
                        if (strpos($fileLower, $ac) !== false) {
                            $matchesActive = true;
                            break;
                        }
                    }
                    if (!$matchesActive) {
                        continue;
                    }
                } else {
                    if ($relRec->ot !== $this->reg->ot) {
                        continue;
                    }
                }
                if (!in_array($base, $this->dibujoBaseNames)) {
                    $this->archivos[] = [
                        'nombre' => $archivo,
                        'ot' => $relRec->ot,
                        'tipo' => 'dibujo',
                    ];
                    $this->dibujoBaseNames[] = $base;
                }
            }
        }
        $this->countDibujos = count($this->archivos);
        $this->ayudasArchivos = [];
        $this->otrosArchivos = [];
        $this->baseNames = $this->dibujoBaseNames;
        $this->normBaseNames = array_map(function ($b) {
            return strtolower(preg_replace('/[\s_]+/', '', $b));
        }, $this->baseNames);
        // --- NUEVO: Escanear ayudas visuales globales desde AYUDAS_FUNDICION ---
        $this->ayudasGlobalesBase = 'DOCUMENTACION_GIS/AYUDAS_FUNDICION';
        foreach ($this->activeClassesForOt as $activeClass) {
            $classNameProper = ucfirst(strtolower($activeClass));
            $candidateDirs = [
                $this->ayudasGlobalesBase . '/' . $classNameProper,
                $this->ayudasGlobalesBase . '/' . $classNameProper . '/Fundicion',
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
                            if (!in_array($normBase, $this->normBaseNames)) {
                                $ayudaData = [
                                    'nombre' => $classNameProper . '/' . $base,
                                    'url' => route('ayudas_fundicion.serve', [
                                        'clase' => $classNameProper,
                                        'archivo' => $base,
                                    ]),
                                    'tipo' => 'ayuda',
                                    'ot' => $this->reg->ot,
                                ];

                                // Las ayudas globales SIEMPRE se muestran, sin importar si la clase fue rechazada.
                                // Son documentos de referencia permanentes.
                                $this->ayudasArchivos[] = $ayudaData;

                                $this->baseNames[] = $base;
                                $this->normBaseNames[] = $normBase;
                            }
                        }
                    }
                }
            }
        }
        $this->liberacionesPath = storage_path('app/public/liberaciones_pdf');
        foreach ($this->allOtNames as $otName) {
            $otNameSanitized = trim(
                preg_replace(
                    '/[\/\\\\]/',
                    '',
                    preg_replace('/\.\.+/', '', $otName),
                ),
            );
            // 1. Escanear ayudas visuales de Calidad (Legacy y Nueva Estructura)
            $ayudasDir =
                'DOCUMENTACION_GIS/CALIDAD_FUNDICION/' .
                $otNameSanitized .
                '/' . \App\Services\FundicionPaths::LEGACY_AYUDAS;
            $calidadRootScan =
                'DOCUMENTACION_GIS/CALIDAD_FUNDICION/' . $otNameSanitized;
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
                    '1 - MOLDES',
                    '2 - BOMBILLO',
                    '3 - EMBUDO',
                    '4 - CORONA',
                    '5 - PLATO',
                    '6 - FONDO',
                    '7 - OBTURADOR',
                    '8 - CABEZA DE SOPLO',
                    '9 - CANDADO OBTURADOR'
                ]
                as $claseDir
            ) {
                $newAyDir =
                    $calidadRootScan . '/' . $claseDir . '/' . \App\Services\FundicionPaths::AYUDAS_VISUALES;
                if (
                    \Illuminate\Support\Facades\Storage::disk('local')->exists(
                        $newAyDir,
                    )
                ) {
                    $scanDirs[] = [
                        'path' => $newAyDir,
                        'base_dir' => $calidadRootScan,
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
                                    '1 - MOLDES',
                                    '2 - BOMBILLO',
                                    '3 - EMBUDO',
                                    '4 - CORONA',
                                    '5 - PLATO',
                                    '6 - FONDO',
                                    '7 - OBTURADOR',
                                    '8 - CABEZA DE SOPLO',
                                    '9 - CANDADO OBTURADOR',
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
                            // Los dibujos/ayudas de clases rechazadas SIEMPRE se muestran.
                            // Solo verificar que la clase pertenece a las activas de esta OT.
                            $matchesActive = false;
                            foreach ($this->activeClassesForOt as $ac) {
                                if (strpos($fileLower, $ac) !== false) {
                                    $matchesActive = true;
                                    break;
                                }
                            }
                            if (!$matchesActive) {
                                continue;
                            }
                        } else {
                            if ($otName !== $this->reg->ot) {
                                continue;
                            }
                        }
                        $normBase = strtolower(preg_replace('/[\s_]+/', '', $base));
                        if (str_starts_with($relativePath, 'preordenes/')) {
                            if (!in_array($normBase, $this->normBaseNames)) {
                                $this->otrosArchivos[] = [
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
                                $this->baseNames[] = $base;
                                $this->normBaseNames[] = $normBase;
                            }
                        } elseif ($isPdf) {
                            if (!in_array($normBase, $this->normBaseNames)) {
                                $this->ayudasArchivos[] = [
                                    'nombre' => $relativePath,
                                    'url' => route('calidad.fundicion.serve', [
                                        'ot' => $otName,
                                        'archivo' => $relativePath,
                                        'tipo' => 'ayuda',
                                    ]),
                                    'tipo' => 'ayuda',
                                    'ot' => $otName,
                                ];
                                $this->baseNames[] = $base;
                                $this->normBaseNames[] = $normBase;
                            }
                        }
                    }
                }
            }
            // 2. Escanear ayudas visuales de Calidad (Legacy preordenes)
            $calidadDir =
                'DOCUMENTACION_GIS/CALIDAD_FUNDICION/' .
                $otNameSanitized .
                '/' . \App\Services\FundicionPaths::LEGACY_PREORDENES;
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
                                    '1 - MOLDES',
                                    '2 - BOMBILLO',
                                    '3 - EMBUDO',
                                    '4 - CORONA',
                                    '5 - PLATO',
                                    '6 - FONDO',
                                    '7 - OBTURADOR',
                                    '8 - CABEZA DE SOPLO',
                                    '9 - CANDADO OBTURADOR',
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
                        foreach ($this->activeClassesForOt as $ac) {
                            if (strpos($fileLower, $ac) !== false) {
                                $matchesActive = true;
                                break;
                            }
                        }
                        if (!$matchesActive) {
                            // Si no coincide con activas, verificar con rechazadas (para que sigan visibles)
                            foreach ($this->rejectedClassesForOt as $rc) {
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
                        if ($otName !== $this->reg->ot) {
                            continue;
                        }
                    }
                    $normBase = strtolower(preg_replace('/[\s_]+/', '', $base));
                    if (!in_array($normBase, $this->normBaseNames)) {
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
                        $this->otrosArchivos[] = [
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
                        $this->baseNames[] = $base;
                        $this->normBaseNames[] = $normBase;
                    }
                }
            }
            // 2b. Escanear Documentos_Aprobados, Documentos_Rechazados, Preordenes y Escaneados (SOLO PARA LA OT ACTUAL DEL REGISTRO)
            if ($otName === $this->reg->ot) {
                $newDirs = [
                    [
                        'dir' =>
                            'DOCUMENTACION_GIS/CALIDAD_FUNDICION/' .
                            $otNameSanitized .
                            '/' . \App\Services\FundicionPaths::DOCUMENTOS_APROBADOS,
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
                foreach (['Candado obturador', 'Cabeza de soplo', 'Obturador', 'Bombillo', 'Embudo', 'Corona', 'Plato', 'Molde', 'Fondo', 'Pistones', 'Guías', 'Guias', '1 - MOLDES', '2 - BOMBILLO', '3 - EMBUDO', '4 - CORONA', '5 - PLATO', '6 - FONDO', '7 - OBTURADOR', '8 - CABEZA DE SOPLO', '9 - CANDADO OBTURADOR'] as $claseDir) {
                    $cUnderscore = strtoupper(preg_replace('/[^a-zA-Z0-9_\-]/', '_', $claseDir));
                    $cUpper = strtoupper($claseDir);
                    $cTitle = $claseDir;

                    $variants = array_unique([$cUnderscore, $cUpper, $cTitle]);

                    foreach ($variants as $vDir) {
                        // Dibujos / DWG (nueva estructura y legacy)
                        foreach (['DIBUJOS', 'Dibujos', 'dibujos', 'DIBUJOS_FUNDICION', 'Dibujos_Fundicion'] as $dibSub) {
                            $newDirs[] = [
                                'dir' => 'DOCUMENTACION_GIS/ALMACEN_FUNDICION/' . $otNameSanitized . '/' . $vDir . '/' . $dibSub,
                                'origin' => 'dibujo',
                                'prefix' => $vDir . '/' . $dibSub . '/',
                                'owner' => 'almacen'
                            ];
                            $newDirs[] = [
                                'dir' => 'DOCUMENTACION_GIS/CALIDAD_FUNDICION/' . $otNameSanitized . '/' . $vDir . '/' . $dibSub,
                                'origin' => 'dibujo',
                                'prefix' => $vDir . '/' . $dibSub . '/',
                                'owner' => 'calidad'
                            ];
                        }

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

                        // --- FALLBACK PARA OTs DE REPROCESO: Buscar dibujos en carpeta de OT Padre Heredada ---
                        $parentOtSanitized = preg_replace('/_.*_R\d+$|_R\d+$/i', '', $otNameSanitized);
                        if ($parentOtSanitized && $parentOtSanitized !== $otNameSanitized) {
                            foreach (['DIBUJOS', 'Dibujos', 'dibujos', 'DIBUJOS_FUNDICION', 'Dibujos_Fundicion'] as $dibSub) {
                                $newDirs[] = [
                                    'dir' => 'DOCUMENTACION_GIS/ALMACEN_FUNDICION/' . $parentOtSanitized . '/' . $vDir . '/' . $dibSub,
                                    'origin' => 'dibujo',
                                    'prefix' => $vDir . '/' . $dibSub . '/',
                                    'owner' => 'almacen'
                                ];
                                $newDirs[] = [
                                    'dir' => 'DOCUMENTACION_GIS/CALIDAD_FUNDICION/' . $parentOtSanitized . '/' . $vDir . '/' . $dibSub,
                                    'origin' => 'dibujo',
                                    'prefix' => $vDir . '/' . $dibSub . '/',
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
                            $isDwg = $ext === 'dwg';
                            if (!$isPdf && !$isImage && !$isDwg) {
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
                                    '1 - MOLDES',
                                    '2 - BOMBILLO',
                                    '3 - EMBUDO',
                                    '4 - CORONA',
                                    '5 - PLATO',
                                    '6 - FONDO',
                                    '7 - OBTURADOR',
                                    '8 - CABEZA DE SOPLO',
                                    '9 - CANDADO OBTURADOR',
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
                                    if (!in_array($fc, $this->activeClassesForOt) && !in_array($fc, $this->rejectedClassesForOt)) {
                                        $hasInactiveClass = true;
                                        break;
                                    }
                                }
                                if ($hasInactiveClass) {
                                    continue;
                                }
                            } else {
                                if ($otName !== $this->reg->ot) {
                                    continue;
                                }
                            }
                            $baseLower = strtolower($base);
                            $isNonDrawingFile = (
                                strpos($fileLower, 'ayudas_visuales') !== false ||
                                strpos($fileLower, 'ayudas-visuales') !== false ||
                                strpos($fileLower, 'preordenes') !== false ||
                                strpos($fileLower, 'preorden') !== false ||
                                strpos($fileLower, 'escaneados') !== false ||
                                strpos($fileLower, 'documentos_aprobados') !== false ||
                                strpos($fileLower, 'documentos_rechazados') !== false ||
                                strpos($baseLower, 'f_alm_') !== false ||
                                strpos($baseLower, 'f_ccl_') !== false ||
                                strpos($baseLower, 'cfm') !== false ||
                                strpos($baseLower, 'efm') !== false ||
                                strpos($baseLower, 'pfm') !== false ||
                                strpos($baseLower, 'pfc') !== false ||
                                strpos($baseLower, 'efc') !== false ||
                                strpos($baseLower, 'ldm') !== false ||
                                strpos($baseLower, 'rdm') !== false ||
                                strpos($baseLower, 'scar') !== false
                            );

                            $normBase = strtolower(preg_replace('/[\s_]+/', '', $base));
                            if (($origin === 'dibujo' || $isDwg || strpos(strtolower($targetDir), 'dibujo') !== false) && !$isNonDrawingFile) {
                                if (!in_array($base, $this->dibujoBaseNames)) {
                                    $relativePathWithPrefix = $prefix . $relativePath;
                                    $this->archivos[] = [
                                        'nombre' => $relativePathWithPrefix,
                                        'url' => route('calidad.fundicion.serve', [
                                            'ot' => $otName,
                                            'archivo' => $relativePathWithPrefix,
                                            'tipo' => 'dibujo',
                                            'origin' => 'dibujo',
                                        ]),
                                        'tipo' => 'dibujo',
                                        'ot' => $otName,
                                        'origin' => 'dibujo',
                                        'owner' => $dirInfo['owner'],
                                    ];
                                    $this->dibujoBaseNames[] = $base;
                                    $this->baseNames[] = $base;
                                    $this->normBaseNames[] = $normBase;
                                }
                            } elseif (!in_array($normBase, $this->normBaseNames)) {
                                $relativePathWithPrefix = $prefix . $relativePath;
                                $this->otrosArchivos[] = [
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
                                $this->baseNames[] = $base;
                                $this->normBaseNames[] = $normBase;
                            }
                        }
                    }
                }
                // 3. Buscar PDFs generados en public/liberaciones_pdf (LDM y SCAR)
                $otSanitizada = preg_replace('/[^\w\s\-]/', '', $otName);
                $otSanitizada = preg_replace('/[\s]+/', '_', trim($otSanitizada));
                if (file_exists($this->liberacionesPath)) {
                    // Buscar LDM y RDM PDFs generados para ESTA OT en public/liberaciones_pdf
                    $otLow = mb_strtolower($otSanitizada, 'UTF-8');
                    $otNameLow = mb_strtolower($otName, 'UTF-8');
                    $ldmFiles = array_merge(
                        glob("{$this->liberacionesPath}/*{$otSanitizada}*.pdf") ?: [],
                        glob("{$this->liberacionesPath}/F*CCL*{$otSanitizada}*.pdf") ?: []
                    );
                    foreach (array_unique($ldmFiles) as $f) {
                        $base = basename($f);
                        $fileLower = mb_strtolower($base, 'UTF-8');
                        if (!str_contains($fileLower, $otLow) && !str_contains($fileLower, $otNameLow)) {
                            continue;
                        }
                        $normBase = strtolower(preg_replace('/[\s_]+/', '', $base));
                        if (!in_array($normBase, $this->normBaseNames)) {
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
                                $this->rechazadosOtros[] = $itemData;
                            } else {
                                $this->otrosArchivos[] = $itemData;
                            }
                            $this->baseNames[] = $base;
                            $this->normBaseNames[] = $normBase;
                        }
                    }
                    // Buscar SCAR PDFs (digital y firmado)
                    $scarPattern = "{$this->liberacionesPath}/F-CCL-SCAR_*_{$otSanitizada}*.pdf";
                    $scarPattern2 = "{$this->liberacionesPath}/F-CCL-SCAR_{$otSanitizada}.pdf";
                    $scarFiles = array_merge(
                        glob($scarPattern) ?: [],
                        glob($scarPattern2) ?: [],
                    );
                    foreach (array_unique($scarFiles) as $f) {
                        $base = basename($f);
                        $normBase = strtolower(preg_replace('/[\s_]+/', '', $base));
                        if (!in_array($normBase, $this->normBaseNames)) {
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
                            $this->rechazadosOtros[] = $itemData;
                            $this->baseNames[] = $base;
                            $this->normBaseNames[] = $normBase;
                        }
                    }
                }
            }
        }
        // Aplicar filtros de visibilidad según perfil de usuario
        $this->userPerfil = \Auth::user()->perfil;
        if ($this->userPerfil != 1 && $this->userPerfil != 2 && $this->userPerfil != 3) {
            $filteredOtros = [];
            foreach ($this->otrosArchivos as $archivo) {
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
                if ($this->userPerfil == 4 || $this->userPerfil == 3) {
                    // Calidad o Master
                    if (!$isPreorden) {
                        $filteredOtros[] = $archivo;
                    } else {
                        $fileHistory = $this->relatedRecords->firstWhere(
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
                } elseif ($this->userPerfil == 5) {
                    // Almacén
                    // Almacén solo ve PDFs de Calidad si se envió la alerta (aprobado o scar alertado)
                    if (
                        $isPreorden ||
                        strpos($nameLow, 'confirmacion') !== false
                    ) {
                        $filteredOtros[] = $archivo;
                    } else {
                        $fileHistory = $this->relatedRecords->firstWhere(
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
            $this->otrosArchivos = $filteredOtros;
        }
        $this->archivosAprobados = [];
        $this->archivosRechazados = [];
        foreach ($this->otrosArchivos as $archivo) {
            $nameLow = strtolower($archivo['nombre']);
            $baseLow = strtolower(basename($archivo['nombre']));
            if (strpos($nameLow, 'documentos_rechazados') !== false) {
                $this->archivosRechazados[] = $archivo;
            } elseif (strpos($nameLow, 'documentos_aprobados') !== false) {
                $this->archivosAprobados[] = $archivo;
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
                $this->archivosAprobados[] = $archivo;
            } elseif (
                strpos($baseLow, 'rechazado') !== false ||
                strpos($baseLow, 'scar') !== false ||
                strpos($baseLow, 'rdm') !== false ||
                strpos($baseLow, 'fdrdm') !== false ||
                strpos($nameLow, 'f_ccl_rdm') !== false ||
                strpos($nameLow, 'f_ccl_scar') !== false
            ) {
                $this->archivosRechazados[] = $archivo;
            } else {
                $this->archivosAprobados[] = $archivo;
            }
        }
        $this->clasesActivas = collect($this->targetReg->ayudas_config ?? [])
            ->filter(fn($c) => !str_contains(strtolower($c), 'opcional') || str_contains(strtolower($c), 'pistones') || str_contains(strtolower($c), 'guías') || str_contains(strtolower($c), 'guias'))
            ->filter(function ($claseNombre)  {
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
                    $baseOt = preg_replace('/_(?:(?:candado\s+obturador|cabeza\s+de\s+soplo|obturador|bombillo|embudo|corona|plato|molde|fondo|pistones|guías|guias)(?:_(?:candado\s+obturador|cabeza\s+de\s+soplo|obturador|bombillo|embudo|corona|plato|molde|fondo|pistones|guías|guias))*_)?R\d+$/iu', '', $this->targetReg->ot);
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
                        ->where('ot', '!=', $this->targetReg->ot, 'and')
                        ->where('tipo_modelo', '=', $tipo, 'and')
                        ->where('estado', '=', 'aprobado', 'and')
                        ->exists();
                    return !$isAprobado;
                }
                return true;
            })
            ->values()
            ->toArray();
        $this->todosGuardados = true;
        foreach ($this->clasesActivas as $clName) {
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
                    $this->targetReg->ot,
                )
                    ->where('tipo_modelo', '=', $tipo)
                    ->exists();
                if (!$hasData) {
                    $this->todosGuardados = false;
                    break;
                }
            }
        }
        if (empty($this->clasesActivas)) {
            $this->todosGuardados = false;
        }
        $this->countAprobados = count($this->archivosAprobados);
        $this->countRechazados = count($this->archivosRechazados);
        $this->countAyudas = count($this->ayudasArchivos);
        $this->countOtros = count($this->otrosArchivos);
        $this->isReprocesoBadge = (bool) preg_match('/_R\d+$/i', $this->reg->ot);
        // En Calidad, todos los archivos base se obtienen de 4 arrays disjuntos:
        // $this->archivos (Dibujos/), $this->ayudasArchivos (Ayudas/), $this->archivosAprobados (Otros_Documentos/),
        // y $this->archivosRechazados (Documentos_Rechazados/).
        // Al sumar estos 4, evitamos doble-conteo.
        $this->count =
            count($this->archivos) +
            count($this->rechazadosDibujos) +
            count($this->ayudasArchivos) +
            count($this->rechazadosAyudas) +
            count($this->archivosAprobados) +
            count($this->archivosRechazados) +
            count($this->rechazadosOtros);
        $this->hasFinalStatus = in_array($this->targetReg->calidad_revision_status, [
            'calidad_aprobado',
            'calidad_rechazado',
            'calidad_mixto',
            'casting_aprobado',
        ]);
        $this->isQualityFinalized = $this->hasFinalStatus && $this->todosGuardados;
        $this->showQualityCard =
            in_array(\Auth::user()->perfil, ['1', '3', '4', 1, 3, 4]) &&
            $this->estado === 'activa' &&
            $this->targetReg->calidad_revision_status !== 'casting_aprobado';
        $this->hasFilesOrControl = $this->count > 0 || $this->showQualityCard;
        // ── CALCULAR APROBADOS Y RECHAZADOS DEL ÁšLTIMO VEREDICTO DE CADA CLASE ──
        $this->liberacionesAll = \App\Models\LiberacionModeloFundicion::whereIn(
            'ot',
            $this->allRelatedOtNames,
        )->get();
        $this->latestLiberacionesByClass = [];
        foreach ($this->liberacionesAll as $lib) {
            $tipo = $lib->tipo_modelo;
            $libOt = $lib->ot;
            preg_match('/_R(\d+)$/', $libOt, $matches);
            $suffixNum = isset($matches[1]) ? (int) $matches[1] : 0;
            if (
                !isset($this->latestLiberacionesByClass[$tipo]) ||
                $suffixNum > $this->latestLiberacionesByClass[$tipo]['suffix']
            ) {
                $this->latestLiberacionesByClass[$tipo] = [
                    'lib' => $lib,
                    'suffix' => $suffixNum,
                ];
            }
        }
        $this->aprobadosRaw = [];
        $this->rechazadosRaw = [];
        foreach ($this->latestLiberacionesByClass as $tipo => $data) {
            $lib = $data['lib'];
            if ($lib->decision === 'aprobar') {
                $this->aprobadosRaw[] = $tipo;
            } elseif ($lib->decision === 'rechazar') {
                $this->rechazadosRaw[] = $tipo;
            }
        }
        // Filtrar por clases activas en esta versión de la OT (desde la pre-orden de modelo)
        // (Ya calculado al inicio en activeClassesForOt)
        $this->aprobados = array_filter($this->aprobadosRaw, function ($clase)  {
            return in_array(strtolower($clase), $this->activeClassesForOt);
        });
        $this->rechazados = array_filter($this->rechazadosRaw, function ($clase)  {
            return in_array(strtolower($clase), $this->activeClassesForOt);
        });
        $this->aprobadosNorm = array_map('strtolower', $this->aprobados);
        $this->clasesFabricacion = array_values(array_diff($this->activeClassesForOt, $this->aprobadosNorm));

        $this->dibujosCasting = array_values(array_filter($this->archivos, function ($d)  {
            $nameLow = strtolower($d['nombre']);
            foreach ($this->aprobadosNorm as $ap) {
                if ($ap !== '' && strpos($nameLow, $ap) !== false)
                    return true;
            }
            return false;
        }));

        $this->dibujosModelo = array_values(array_filter($this->archivos, function ($d)  {
            $nameLow = strtolower($d['nombre']);
            foreach ($this->clasesFabricacion as $cf) {
                if ($cf !== '' && strpos($nameLow, $cf) !== false)
                    return true;
            }
            return false;
        }));

        $this->ayudasCasting = array_values(array_filter($this->ayudasArchivos, function ($a)  {
            $nameLow = strtolower($a['nombre']);
            foreach ($this->aprobadosNorm as $ap) {
                if ($ap !== '' && strpos($nameLow, $ap) !== false)
                    return true;
            }
            return false;
        }));

        $this->ayudasModelo = array_values(array_filter($this->ayudasArchivos, function ($a)  {
            $nameLow = strtolower($a['nombre']);
            foreach ($this->clasesFabricacion as $cf) {
                if ($cf !== '' && strpos($nameLow, $cf) !== false)
                    return true;
            }
            return false;
        }));

        $this->countDibujos = count($this->dibujosModelo);
        $this->countAyudas = count($this->ayudasModelo);
        $this->tieneFabricacion = count($this->clasesFabricacion) > 0;

        // Sub-processing for files row
        $this->rechazadosDibujos = [];
        $this->rechazadosAyudas = [];
        $this->rechazadosOtros = $this->rechazadosOtros ?? [];
        foreach ($this->archivosRechazados as $rArchivo) {
            $nameLow = strtolower($rArchivo['nombre']);
            $ext = pathinfo($nameLow, PATHINFO_EXTENSION);
            $isImg = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
            $rArchivo['ot'] = $rArchivo['ot'] ?? $this->targetReg->ot;
            $rArchivo['tipo'] = $rArchivo['tipo'] ?? ($isImg ? 'imagen' : 'otro');

            if (strpos($nameLow, 'scar') !== false || strpos($nameLow, 'f_ccl_scar') !== false || strpos($nameLow, 'f_ccl_rdm') !== false || strpos($nameLow, 'foto') !== false) {
                $this->rechazadosOtros[] = $rArchivo;
            } elseif (strpos($nameLow, 'ayudas_visuales') !== false || strpos($nameLow, 'ayudas-visuales') !== false || $isImg) {
                $rArchivo['tipo'] = $rArchivo['tipo'] === 'otro' ? 'ayuda' : $rArchivo['tipo'];
                $this->rechazadosAyudas[] = $rArchivo;
            } elseif (strpos($nameLow, 'dibujos') !== false || strpos($nameLow, 'dibujo') !== false) {
                $this->rechazadosDibujos[] = $rArchivo;
            } else {
                $this->rechazadosOtros[] = $rArchivo;
            }
        }

        // Documentos y Dibujos recibidos de Almacén (NUNCA se ocultan aunque 1 de 3 clases esté aprobada/rechazada)
        $this->allValidDibujos = $this->archivos;
        $this->allValidAyudas = $this->ayudasArchivos;

        // Clasificar $this->archivosAprobados en Almacén (preórdenes/confirmaciones) vs Calidad (Formatos de Liberación Aprobados F-CCL-LDM)
        $this->almacenAprobadosDocs = [];
        $this->calidadAprobadosLdm = [];

        foreach ($this->archivosAprobados as $doc) {
            $baseLow = strtolower(basename($doc['nombre']));
            if (strpos($baseLow, 'ldm') !== false || strpos($baseLow, 'f-ccl-ldm') !== false || strpos($baseLow, 'liberacion') !== false) {
                $this->calidadAprobadosLdm[] = $doc;
            } else {
                $this->almacenAprobadosDocs[] = $doc;
            }
        }

        $this->hasAlmacenGroup = (count($this->allValidDibujos) > 0 || count($this->allValidAyudas) > 0 || count($this->almacenAprobadosDocs) > 0);
        $this->hasAprobadosGroup = (count($this->calidadAprobadosLdm) > 0);
        $this->hasRechazadosGroup = (count($this->rechazadosDibujos) > 0 || count($this->rechazadosAyudas) > 0 || count($this->rechazadosOtros) > 0);

        
    }

    public function toArray(): array
    {
        $vars = get_object_vars($this);
        unset($vars['reg'], $vars['estado'], $vars['deptName']);
        return $vars;
    }
}
