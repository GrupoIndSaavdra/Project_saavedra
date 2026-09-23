<?php

namespace App\ViewModels;

use App\Models\FundicionHistory;
use Illuminate\Support\Facades\Auth;

class AlmacenTableRowViewModel
{
    /** @var FundicionHistory */
    public $reg;
    /** @var string */
    public $estado;
    /** @var string */
    public $deptName;

    /** @var mixed */
    public $activeClassesForOt;
    /** @var mixed */
    public $allOtNames;
    /** @var mixed */
    public $allRelatedOtNames;
    /** @var mixed */
    public $allowFileCrossOt;
    /** @var mixed */
    public $almacenPreordenes;
    /** @var mixed */
    public $almacenPreordenesCasting;
    /** @var mixed */
    public $almacenPreordenesFab;
    /** @var mixed */
    public $almacenRootScan;
    /** @var mixed */
    public $aprobados;
    /** @var mixed */
    public $aprobadosNorm;
    /** @var mixed */
    public $aprobadosRaw;
    /** @var mixed */
    public $archivo;
    /** @var mixed */
    public $archivos;
    /** @var mixed */
    public $archivosAprobados;
    /** @var mixed */
    public $archivosFabAll;
    /** @var mixed */
    public $archivosRechazados;
    /** @var mixed */
    public $ayudasArchivos;
    /** @var mixed */
    public $ayudasBaseNames;
    /** @var mixed */
    public $ayudasCasting;
    /** @var mixed */
    public $ayudasDir;
    /** @var mixed */
    public $ayudasGlobalesBase;
    /** @var mixed */
    public $ayudasModelo;
    /** @var mixed */
    public $ayudasRechazadosOrig;
    /** @var mixed */
    public $baseLow;
    /** @var mixed */
    public $baseLower;
    /** @var mixed */
    public $baseName;
    /** @var mixed */
    public $baseNames;
    /** @var mixed */
    public $baseNamesRechAyudas;
    /** @var mixed */
    public $baseNamesRechOtros;
    /** @var mixed */
    public $baseOt;
    /** @var mixed */
    public $baseOtName;
    /** @var mixed */
    public $baseOtOfReg;
    /** @var mixed */
    public $bgColor;
    /** @var mixed */
    public $borderColor;
    /** @var mixed */
    public $calidadAlertaEnviada;
    /** @var mixed */
    public $calidadAprobadosLdm;
    /** @var mixed */
    public $calidadAprobadosLdmCasting;
    /** @var mixed */
    public $calidadDir;
    /** @var mixed */
    public $candidateDirs;
    /** @var mixed */
    public $castingEmailSent;
    /** @var mixed */
    public $claseVariants;
    /** @var mixed */
    public $clasesBaseList;
    /** @var mixed */
    public $clasesDb;
    /** @var mixed */
    public $clasesFabricacion;
    /** @var mixed */
    public $clasesFabricacionHeader;
    /** @var mixed */
    public $clasesRechazadas;
    /** @var mixed */
    public $classesInCurrentOtRaw;
    /** @var mixed */
    public $classesRaw;
    /** @var mixed */
    public $confSource;
    /** @var mixed */
    public $configs;
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
    public $countVisibleAprobados;
    /** @var mixed */
    public $countVisibleFabricacion;
    /** @var mixed */
    public $countVisibleRechazados;
    /** @var mixed */
    public $cubiertaFab;
    /** @var mixed */
    public $dibujoBaseNames;
    /** @var mixed */
    public $dibujosCasting;
    /** @var mixed */
    public $dibujosModelo;
    /** @var mixed */
    public $dibujosRechazadosOrig;
    /** @var mixed */
    public $esReproceso;
    /** @var mixed */
    public $estadoConfig;
    /** @var mixed */
    public $existId;
    /** @var mixed */
    public $existSuffix;
    /** @var mixed */
    public $extractedClass;
    /** @var mixed */
    public $extractedFab;
    /** @var mixed */
    public $filas;
    /** @var mixed */
    public $filasFab;
    /** @var mixed */
    public $fileHistory;
    /** @var mixed */
    public $fileOt;
    /** @var mixed */
    public $files;
    /** @var mixed */
    public $filteredOtros;
    /** @var mixed */
    public $foundClass;
    /** @var mixed */
    public $fsmState;
    /** @var mixed */
    public $hasAprobados;
    /** @var mixed */
    public $hasFilesOrControl;
    /** @var mixed */
    public $hasInactiveClass;
    /** @var mixed */
    public $hasKnownClass;
    /** @var mixed */
    public $hasPendingChanges;
    /** @var mixed */
    public $hasPreorden;
    /** @var mixed */
    public $hasRechazosRealLocal;
    /** @var mixed */
    public $hasVerdictosPendientes;
    /** @var mixed */
    public $hayRechazadosSinPreorden;
    /** @var mixed */
    public $icon;
    /** @var mixed */
    public $isCalidadAlerted;
    /** @var mixed */
    public $isCalidadAlertedLocal;
    /** @var mixed */
    public $isDwg;
    /** @var mixed */
    public $isFinalized;
    /** @var mixed */
    public $isImage;
    /** @var mixed */
    public $isImg;
    /** @var mixed */
    public $isMixedProcess;
    /** @var mixed */
    public $isNonDrawing;
    /** @var mixed */
    public $isNonDrawingFile;
    /** @var mixed */
    public $isPdf;
    /** @var mixed */
    public $isPreorden;
    /** @var mixed */
    public $isRechazado;
    /** @var mixed */
    public $isReproceso;
    /** @var mixed */
    public $isReprocesoOT;
    /** @var mixed */
    public $knownClasses;
    /** @var mixed */
    public $label;
    /** @var mixed */
    public $latestLiberacionesByClass;
    /** @var mixed */
    public $latestRechazo;
    /** @var mixed */
    public $latestReproceso;
    /** @var mixed */
    public $ldmFiles;
    /** @var mixed */
    public $legacyClaseAyDir;
    /** @var mixed */
    public $libOt;
    /** @var mixed */
    public $liberacionesAll;
    /** @var mixed */
    public $liberacionesPath;
    /** @var mixed */
    public $liberacionesReg;
    /** @var mixed */
    public $matched;
    /** @var mixed */
    public $matchesActive;
    /** @var mixed */
    public $matchesRejected;
    /** @var mixed */
    public $nameLow;
    /** @var mixed */
    public $newAyDir;
    /** @var mixed */
    public $newDirs;
    /** @var mixed */
    public $normAyudasBaseNames;
    /** @var mixed */
    public $normBase;
    /** @var mixed */
    public $normBaseNames;
    /** @var mixed */
    public $origin;
    /** @var mixed */
    public $otId;
    /** @var mixed */
    public $otLow;
    /** @var mixed */
    public $otName;
    /** @var mixed */
    public $otNameLow;
    /** @var mixed */
    public $otNameSanitized;
    /** @var mixed */
    public $otParaRechazados;
    /** @var mixed */
    public $otSanitizada;
    /** @var mixed */
    public $otrosArchivos;
    /** @var mixed */
    public $owner;
    /** @var mixed */
    public $p;
    /** @var mixed */
    public $parentOtSanitized;
    /** @var mixed */
    public $parsedClasses;
    /** @var mixed */
    public $parsedCurrent;
    /** @var mixed */
    public $parsedPrev;
    /** @var mixed */
    public $parts;
    /** @var mixed */
    public $pendingChanges;
    /** @var mixed */
    public $po;
    /** @var mixed */
    public $preOrdenesCandidates;
    /** @var mixed */
    public $preOrdenesEnviadasFab;
    /** @var mixed */
    public $prefix;
    /** @var mixed */
    public $preordenesSentClassesFab;
    /** @var mixed */
    public $prevOt;
    /** @var mixed */
    public $rArchivo;
    /** @var mixed */
    public $rechazados;
    /** @var mixed */
    public $rechazadosAyudas;
    /** @var mixed */
    public $rechazadosAyudasFilesystem;
    /** @var mixed */
    public $rechazadosDibujos;
    /** @var mixed */
    public $rechazadosNorm;
    /** @var mixed */
    public $rechazadosNormFab;
    /** @var mixed */
    public $rechazadosNormLocal;
    /** @var mixed */
    public $rechazadosOtros;
    /** @var mixed */
    public $rechazadosOtrosFilesystem;
    /** @var mixed */
    public $rechazadosRaw;
    /** @var mixed */
    public $rechazadosSinPreorden;
    /** @var mixed */
    public $rejectedPrevRaw;
    /** @var mixed */
    public $relArchivos;
    /** @var mixed */
    public $relatedRecords;
    /** @var mixed */
    public $relativePathWithPrefix;
    /** @var mixed */
    public $reprocesoTienePreOrden;
    /** @var mixed */
    public $scanDirs;
    /** @var mixed */
    public $scarFiles;
    /** @var mixed */
    public $scarPattern;
    /** @var mixed */
    public $scarPattern2;
    /** @var mixed */
    public $shouldReplace;
    /** @var mixed */
    public $showControlCard;
    /** @var mixed */
    public $status;
    /** @var mixed */
    public $subDirs;
    /** @var mixed */
    public $suffixNum;
    /** @var mixed */
    public $targetDir;
    /** @var mixed */
    public $targetReg;
    /** @var mixed */
    public $textColor;
    /** @var mixed */
    public $tieneAprobados;
    /** @var mixed */
    public $tieneArchivosFabricacion;
    /** @var mixed */
    public $tieneFabricacion;
    /** @var mixed */
    public $tieneRechazados;
    /** @var mixed */
    public $tipo;
    /** @var mixed */
    public $tooltip;
    /** @var mixed */
    public $userPerfil;

    public function __construct(FundicionHistory $reg, string $estado, string $deptName)
    {
        $this->reg = $reg;
        $this->estado = $estado;
        $this->deptName = $deptName;
        $this->process();
    }

    private function process(): void
    {
        $reg = $this->reg;
        $estado = $this->estado;
        $deptName = $this->deptName;

        /** @var FundicionHistory $reg */
        $liberacionesReg = \App\Models\LiberacionModeloFundicion::where('ot', $reg->ot)
            ->where('estado', '!=', 'pendiente')
            ->get();
        /** @var \Illuminate\Database\Eloquent\Collection<\App\Models\LiberacionModeloFundicion> $liberacionesReg */
        $hasAprobados = $liberacionesReg->where('decision', 'aprobar')->isNotEmpty();

        $latestReproceso = null;
        if ($reg->rechazos_procesados) {
            $latestReproceso = FundicionHistory::where(
                function ($q) use ($reg) {
                    $q->where('ot', 'LIKE', $reg->ot . '_R%', 'and')->where(
                        'ot',
                        'LIKE',
                        $reg->ot . '_%_R%',
                        'or',
                    );
                },
                null,
                null,
                'and',
            )
                ->orderBy('id', 'desc')
                ->first();
        }
        // Corregimos la asignación para que la fila original NO actúe como si fuera el reproceso.
        // Así mantenemos las clases independientes (Ej: original para Bombillo, R1 para Fondo).
        $targetReg = $reg;

        // ── RESOLVER TODOS LOS REGISTROS RELACIONADOS ──
        $baseOtName = preg_replace(
            '/_(?:(?:candado\s+obturador|cabeza\s+de\s+soplo|obturador|bombillo|embudo|corona|plato|molde|fondo|pistones|guías|guias)(?:_(?:candado\s+obturador|cabeza\s+de\s+soplo|obturador|bombillo|embudo|corona|plato|molde|fondo|pistones|guías|guias))*_)?R\d+$/iu',
            '',
            $reg->ot,
        );
        $relatedRecords = FundicionHistory::where('ot', '=', $baseOtName, 'or')
            ->where('ot', 'LIKE', $baseOtName . '_R%', 'or')
            ->where('ot', 'LIKE', $baseOtName . '_%_R%', 'or')
            ->get();
        $allRelatedOtNames = $relatedRecords->pluck('ot')->toArray();
        $allOtNames = $allRelatedOtNames;

        $isReprocesoOT = preg_match('/_R\d+$/i', $reg->ot);
        $baseOtOfReg = preg_replace(
            '/_(?:(?:candado\s+obturador|cabeza\s+de\s+soplo|obturador|bombillo|embudo|corona|plato|molde|fondo|pistones|guías|guias)(?:_(?:candado\s+obturador|cabeza\s+de\s+soplo|obturador|bombillo|embudo|corona|plato|molde|fondo|pistones|guías|guias))*_)?R\d+$/iu',
            '',
            $reg->ot,
        );
        preg_match('/_R(\d+)$/i', $reg->ot, $mReg);
        $sReg = isset($mReg[1]) ? (int) $mReg[1] : 0;

        $allowFileCrossOt = function ($fileOt) use ($reg, $isReprocesoOT, $baseOtOfReg, $sReg) {
            if ($fileOt === $reg->ot) {
                return true;
            }
            if (!$isReprocesoOT) {
                return false;
            }
            $baseOtOfFile = preg_replace(
                '/_(?:(?:candado\s+obturador|cabeza\s+de\s+soplo|obturador|bombillo|embudo|corona|plato|molde|fondo|pistones|guías|guias)(?:_(?:candado\s+obturador|cabeza\s+de\s+soplo|obturador|bombillo|embudo|corona|plato|molde|fondo|pistones|guías|guias))*_)?R\d+$/iu',
                '',
                $fileOt,
            );
            if ($baseOtOfFile !== $baseOtOfReg) {
                return false;
            }
            preg_match('/_R(\d+)$/i', $fileOt, $mFile);
            $sFile = isset($mFile[1]) ? (int) $mFile[1] : 0;
            return $sFile < $sReg;
        };

        // Obtener clases activas para filtrar archivos del historial
        $activeClassesForOt = [];
        $confSource = $targetReg->ayudas_config ?? ($reg->ayudas_config ?? null);
        if (!empty($confSource)) {
            $configs = is_string($confSource) ? json_decode($confSource, true) : $confSource;
            if (is_array($configs)) {
                foreach ($configs as $val) {
                    $val = strtolower($val);
                    if (str_contains($val, 'opcional')) {
                        continue;
                    }
                    $parts = explode(',', $val);
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
                        as $kc
                    ) {
                        foreach ($parts as $p) {
                            if (strpos($p, strtolower($kc)) !== false) {
                                $activeClassesForOt[] = $kc;
                            }
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
                            $parts = explode(',', $val);
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
                                as $kc
                            ) {
                                foreach ($parts as $p) {
                                    if (strpos($p, strtolower($kc)) !== false) {
                                        $activeClassesForOt[] = $kc;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        // Filtrar clases activas basándose en las decisiones de Calidad o liberaciones registradas
        /** @var FundicionHistory $reg */
        $isReproceso = preg_match('/_R\d+$/i', $reg->ot);
        if (empty($activeClassesForOt)) {
            if ($isReproceso) {
                $classesInCurrentOtRaw = \App\Models\LiberacionModeloFundicion::where(
                    'ot',
                    '=',
                    $reg->ot,
                )
                    ->whereNotNull('tipo_modelo')
                    ->pluck('tipo_modelo')
                    ->toArray();

                $parsedCurrent = [];
                foreach ($classesInCurrentOtRaw as $dc) {
                    $parts = explode(',', strtolower($dc));
                    foreach ($parts as $p) {
                        $p = trim($p);
                        if ($p !== '') {
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
                                as $kc
                            ) {
                                if (strpos($p, strtolower($kc)) !== false) {
                                    $parsedCurrent[] = $kc;
                                    break;
                                }
                            }
                        }
                    }
                }

                if (!empty($parsedCurrent)) {
                    $activeClassesForOt = array_unique($parsedCurrent);
                } else {
                    $baseOt = preg_replace(
                        '/_(?:(?:candado\s+obturador|cabeza\s+de\s+soplo|obturador|bombillo|embudo|corona|plato|molde|fondo|pistones|guías|guias)(?:_(?:candado\s+obturador|cabeza\s+de\s+soplo|obturador|bombillo|embudo|corona|plato|molde|fondo|pistones|guías|guias))*_)?R\d+$/iu',
                        '',
                        $reg->ot,
                    );
                    $latestRechazo = \App\Models\LiberacionModeloFundicion::where(
                        'ot',
                        'LIKE',
                        $baseOt . '%',
                        'and',
                    )
                        ->where('ot', '!=', $reg->ot, 'and')
                        ->where('decision', '=', 'rechazar', 'and')
                        ->orderBy('id', 'desc')
                        ->first();
                    $prevOt = $latestRechazo ? $latestRechazo->ot : $baseOt;
                    $rejectedPrevRaw = \App\Models\LiberacionModeloFundicion::where(
                        'ot',
                        '=',
                        $prevOt,
                    )
                        ->where('decision', '=', 'rechazar')
                        ->pluck('tipo_modelo')
                        ->toArray();

                    $parsedPrev = [];
                    foreach ($rejectedPrevRaw as $dc) {
                        $parts = explode(',', strtolower($dc));
                        foreach ($parts as $p) {
                            $p = trim($p);
                            if ($p !== '') {
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
                                    as $kc
                                ) {
                                    if (strpos($p, strtolower($kc)) !== false) {
                                        $parsedPrev[] = $kc;
                                        break;
                                    }
                                }
                            }
                        }
                    }
                    if (!empty($parsedPrev)) {
                        $activeClassesForOt = array_unique($parsedPrev);
                    }
                }
            } else {
                $classesRaw = \App\Models\LiberacionModeloFundicion::where('ot', '=', $reg->ot)
                    ->whereNotNull('tipo_modelo')
                    ->pluck('tipo_modelo')
                    ->toArray();

                $parsedClasses = [];
                foreach ($classesRaw as $dc) {
                    $parts = explode(',', strtolower($dc));
                    foreach ($parts as $p) {
                        $p = trim($p);
                        if ($p !== '') {
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
                                as $kc
                            ) {
                                if (strpos($p, strtolower($kc)) !== false) {
                                    $parsedClasses[] = $kc;
                                    break;
                                }
                            }
                        }
                    }
                }

                if (!empty($parsedClasses)) {
                    $activeClassesForOt = array_unique($parsedClasses);
                }
            }
        } else {
            $classesRaw = \App\Models\LiberacionModeloFundicion::where('ot', '=', $reg->ot)
                ->whereNotNull('tipo_modelo')
                ->pluck('tipo_modelo')
                ->toArray();
            foreach ($classesRaw as $dc) {
                $parts = explode(',', strtolower($dc));
                foreach ($parts as $p) {
                    $p = trim($p);
                    if ($p !== '') {
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
                            as $kc
                        ) {
                            if (
                                strpos($p, strtolower($kc)) !== false &&
                                !in_array($kc, $activeClassesForOt)
                            ) {
                                $activeClassesForOt[] = $kc;
                                break;
                            }
                        }
                    }
                }
            }
        }

        if (empty($activeClassesForOt)) {
            preg_match('/OT (\d+)/i', $reg->ot, $otMatches);
            if (!empty($otMatches[1])) {
                $otId = (int) $otMatches[1];
                $clasesDb = \App\Models\Clase::where('id_ot', $otId)
                    ->pluck('nombre')
                    ->toArray();
                if (!empty($clasesDb)) {
                    $parsedDb = [];
                    $otNameSanitized = str_replace(
                        [':', '/', '\\', '*', '?', '"', '<', '>', '|'],
                        '_',
                        $reg->ot,
                    );
                    foreach ($clasesDb as $cdb) {
                        $norm = \App\Models\Clase::normalizeClassName($cdb);
                        if ($norm) {
                            $cdbTrim = trim($cdb);
                            $hasFolder =
                                \Illuminate\Support\Facades\Storage::disk('local')->exists(
                                    'DOCUMENTACION_GIS/DIBUJOS_FUNDICION/' .
                                    $otNameSanitized .
                                    '/' .
                                    $cdbTrim,
                                ) ||
                                \Illuminate\Support\Facades\Storage::disk('local')->exists(
                                    'DOCUMENTACION_GIS/ALMACEN_FUNDICION/' .
                                    $otNameSanitized .
                                    '/' .
                                    $cdbTrim,
                                ) ||
                                \Illuminate\Support\Facades\Storage::disk('local')->exists(
                                    'DOCUMENTACION_GIS/CALIDAD_FUNDICION/' .
                                    $otNameSanitized .
                                    '/' .
                                    $cdbTrim,
                                );

                            if ($hasFolder) {
                                $parsedDb[] = strtolower($norm);
                            }
                        }
                    }
                    if (!empty($parsedDb)) {
                        $activeClassesForOt = array_unique($parsedDb);
                    }
                }
            }

            if (empty($activeClassesForOt)) {
                $activeClassesForOt = [
                    '1 - MOLDES',
                    '2 - BOMBILLO',
                    '3 - EMBUDO',
                    '4 - CORONA',
                    '5 - PLATO',
                    '6 - FONDO',
                    '7 - OBTURADOR',
                    '8 - CABEZA DE SOPLO',
                    '9 - CANDADO OBTURADOR',
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
                ];
            }
        }
        $activeClassesForOt = array_map(function ($c) {
            return \App\Services\FundicionPaths::normalizeClass($c);
        }, $activeClassesForOt);
        $activeClassesForOt = array_values(array_unique($activeClassesForOt));

        // Para reprocesos, las decisiones de rechazo están en la OT anterior (_R0, _R1, ...)
        // Para OTs base, están en la misma OT.
        $baseOt = preg_replace(
            '/_(?:(?:candado\s+obturador|cabeza\s+de\s+soplo|obturador|bombillo|embudo|corona|plato|molde|fondo|pistones|guías|guias)(?:_(?:candado\s+obturador|cabeza\s+de\s+soplo|obturador|bombillo|embudo|corona|plato|molde|fondo|pistones|guías|guias))*_)?R\d+$/iu',
            '',
            $reg->ot,
        );
        $latestRechazo = \App\Models\LiberacionModeloFundicion::where(
            'ot',
            'LIKE',
            $baseOt . '%',
            'and',
        )
            ->where('ot', '!=', $reg->ot, 'and')
            ->where('decision', '=', 'rechazar', 'and')
            ->orderBy('id', 'desc')
            ->first();
        $otParaRechazados = $latestRechazo ? $latestRechazo->ot : $reg->ot;
        $clasesRechazadas = \App\Models\LiberacionModeloFundicion::where(
            'ot',
            $otParaRechazados,
        )
            ->where('decision', 'rechazar')
            ->pluck('tipo_modelo')
            ->map(function ($modelo) {
                return strtolower(trim($modelo));
            })
            ->toArray();

        // Cuando esta OT ES un reproceso (_R1, _R2...) y tiene
        // pre-orden generada, los dibujos/ayudas de las clases
        // rechazadas ya estan siendo trabajadas nuevamente:
        // mostrarlas como aprobadas (limpiar clasesRechazadas).
        $reprocesoTienePreOrden = false;
        if (preg_match('/_R\d+$/i', $reg->ot) && !empty($clasesRechazadas)) {
            $reprocesoTienePreOrden =
                $reg->pre_orden_sent ||
                $reg->pre_orden_email_sent ||
                \App\Models\PreOrdenFundicion::where('ot', $reg->ot)->exists();
            if ($reprocesoTienePreOrden) {
                $clasesRechazadas = [];
            }
        }
        $rechazadosDibujos = [];
        $rechazadosAyudas = [];
        $rechazadosOtros = [];
        $archivos = [];
        $dibujoBaseNames = [];
        foreach ($relatedRecords as $relRec) {
            // No mezclar dibujos de OTs de reproceso (_R1, _R2...) en la OT base u otras OTs
            if ($relRec->ot !== $reg->ot && preg_match('/_R\d+$/i', $relRec->ot)) {
                continue;
            }
            if (
                preg_match('/_R\d+$/i', $reg->ot) &&
                $relRec->ot !== $reg->ot &&
                preg_match('/_R\d+$/i', $relRec->ot)
            ) {
                continue;
            }

            $relArchivos = is_array($relRec->almacen_archivos) ? $relRec->almacen_archivos : [];
            foreach ($relArchivos as $archivo) {
                $base = basename($archivo);
                $fileLower = strtolower($archivo);
                $baseLower = strtolower($base);

                $isNonDrawing =
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
                    strpos($baseLower, 'scar') !== false;

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
                ];
                $hasKnownClass = false;
                $foundClass = null;
                foreach ($knownClasses as $kc) {
                    if (strpos($fileLower, strtolower($kc)) !== false) {
                        $hasKnownClass = true;
                        $foundClass = strtolower($kc);
                        break;
                    }
                }
                if ($hasKnownClass) {
                    // Los dibujos SIEMPRE se muestran, aunque la clase esté rechazada.
                    // Son documentos de referencia permanentes.
                    $matchesActive = in_array(strtoupper($foundClass), $activeClassesForOt);
                    $matchesRejected = in_array($foundClass, $clasesRechazadas);
                    if (!$matchesActive && !$matchesRejected) {
                        continue;
                    }
                } else {
                    if (!$allowFileCrossOt($relRec->ot)) {
                        continue;
                    }
                }
                if (!in_array($base, $dibujoBaseNames)) {
                    $archivos[] = [
                        'nombre' => $archivo,
                        'ot' => $relRec->ot,
                        'tipo' => 'dibujo',
                        'origin' => 'dibujo',
                        'owner' => 'almacen',
                    ];
                    $dibujoBaseNames[] = $base;
                }
            }
        }
        $countDibujos = count($archivos);

        $ayudasArchivos = [];
        $otrosArchivos = [];
        $ayudasBaseNames = [];
        $normAyudasBaseNames = [];
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
                    \Illuminate\Support\Facades\Storage::disk('local')->exists($globalClassDir)
                ) {
                    $files = \Illuminate\Support\Facades\Storage::disk('local')->files(
                        $globalClassDir,
                    );
                    foreach ($files as $f) {
                        $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
                        if ($ext === 'pdf') {
                            $base = basename($f);
                            $fileLower = strtolower($f);
                            $baseLower = strtolower($base);
                            $isNonDrawingFile =
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
                                strpos($baseLower, 'scar') !== false;
                            if ($isNonDrawingFile) {
                                continue;
                            }
                            $normBase = strtolower(preg_replace('/[\s_]+/', '', $base));
                            if (!in_array($normBase, $normAyudasBaseNames)) {
                                $ayudaData = [
                                    'nombre' => $classNameProper . '/' . $base,
                                    'url' => route('ayudas_fundicion.serve', [
                                        'clase' => $classNameProper,
                                        'archivo' => $base,
                                    ]),
                                    'tipo' => 'ayuda',
                                    'ot' => $reg->ot,
                                ];

                                // Las ayudas globales SIEMPRE se muestran, aunque la clase esté rechazada.
                                // Son documentos de referencia permanentes.
                                $ayudasArchivos[] = $ayudaData;

                                $ayudasBaseNames[] = $base;
                                $normAyudasBaseNames[] = $normBase;
                            }
                        }
                    }
                }
            }
        }

        $liberacionesPath = storage_path('app/public/liberaciones_pdf');

        foreach ($allOtNames as $otName) {
            if ($otName !== $reg->ot && preg_match('/_R\d+$/i', $otName)) {
                continue;
            }
            if (
                preg_match('/_R\d+$/i', $reg->ot) &&
                $otName !== $reg->ot &&
                preg_match('/_R\d+$/i', $otName)
            ) {
                continue;
            }

            $otNameSanitized = trim(
                preg_replace('/[\/\\\\]/', '', preg_replace('/\.\.+/', '', $otName)),
            );

            // 1. Escanear ayudas visuales de Almacen (Legacy y Nueva Estructura)
            $ayudasDir =
                'DOCUMENTACION_GIS/ALMACEN_FUNDICION/' . $otNameSanitized . '/ayudas_visuales';
            $almacenRootScan = 'DOCUMENTACION_GIS/ALMACEN_FUNDICION/' . $otNameSanitized;

            $scanDirs = [];
            if (\Illuminate\Support\Facades\Storage::disk('local')->exists($ayudasDir)) {
                $scanDirs[] = [
                    'path' => $ayudasDir,
                    'base_dir' => $ayudasDir,
                ];
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
                $subDirs = [
                    $claseDir . '/Ayudas_Visuales',
                    strtoupper($claseDir) . '/AYUDAS_VISUALES_FUNDICION',
                    $claseDir . '/AYUDAS_VISUALES_FUNDICION',
                ];
                foreach ($subDirs as $subDir) {
                    $newAyDir = $almacenRootScan . '/' . $subDir;
                    if (\Illuminate\Support\Facades\Storage::disk('local')->exists($newAyDir)) {
                        $scanDirs[] = [
                            'path' => $newAyDir,
                            'base_dir' => $almacenRootScan,
                        ];
                    }
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
                    \Illuminate\Support\Facades\Storage::disk('local')->exists($sInfo['path'])
                ) {
                    $files = \Illuminate\Support\Facades\Storage::disk('local')->allFiles(
                        $sInfo['path'],
                    );
                    foreach ($files as $f) {
                        $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
                        $isPdf = $ext === 'pdf';
                        $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);

                        if (!$isPdf && !$isImage) {
                            continue;
                        }

                        $fNorm = str_replace('\\', '/', $f);
                        $dirNorm = str_replace('\\', '/', $sInfo['base_dir']);
                        $relativePath = ltrim(str_replace($dirNorm, '', $fNorm), '/');
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
                        ];
                        $hasKnownClass = false;
                        foreach ($knownClasses as $kc) {
                            if (strpos($fileLower, strtolower($kc)) !== false) {
                                $hasKnownClass = true;
                                break;
                            }
                        }
                        if ($hasKnownClass) {
                            // Las ayudas SIEMPRE se muestran aunque la clase esté rechazada.
                            $matchesActive = false;
                            foreach ($activeClassesForOt as $ac) {
                                if (strpos($fileLower, $ac) !== false) {
                                    $matchesActive = true;
                                    break;
                                }
                            }
                            // Incluir también archivos de clases rechazadas (siguen siendo referencia)
                            if (!$matchesActive) {
                                foreach ($clasesRechazadas as $rc) {
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
                            if (!$allowFileCrossOt($otName)) {
                                continue;
                            }
                        }

                        $normBase = strtolower(preg_replace('/[\s_]+/', '', $base));
                        if (str_starts_with($relativePath, 'preordenes/')) {
                            if (!in_array($normBase, $normBaseNames)) {
                                $otrosArchivos[] = [
                                    'nombre' => $relativePath,
                                    'url' => route('almacen.fundicion.serve', [
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
                            if (!in_array($normBase, $normAyudasBaseNames)) {
                                $ayudasArchivos[] = [
                                    'nombre' => $relativePath,
                                    'url' => route('almacen.fundicion.serve', [
                                        'ot' => $otName,
                                        'archivo' => $relativePath,
                                        'tipo' => 'ayuda',
                                    ]),
                                    'tipo' => 'ayuda',
                                    'ot' => $otName,
                                ];
                                $ayudasBaseNames[] = $base;
                                $normAyudasBaseNames[] = $normBase;
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
            if (\Illuminate\Support\Facades\Storage::disk('local')->exists($calidadDir)) {
                $files = \Illuminate\Support\Facades\Storage::disk('local')->allFiles(
                    $calidadDir,
                );
                foreach ($files as $f) {
                    $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
                    $isPdf = $ext === 'pdf';
                    $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);

                    if (!$isPdf && !$isImage) {
                        continue;
                    }

                    $fNorm = str_replace('\\', '/', $f);
                    $dirNorm = str_replace('\\', '/', $calidadDir);
                    $relativePath = ltrim(str_replace($dirNorm, '', $fNorm), '/');
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
                    ];
                    $hasKnownClass = false;
                    foreach ($knownClasses as $kc) {
                        if (strpos($fileLower, strtolower($kc)) !== false) {
                            $hasKnownClass = true;
                            break;
                        }
                    }
                    if ($hasKnownClass) {
                        // Ayudas de preordenes de Calidad SIEMPRE visibles (documentos de referencia).
                        $matchesActive = false;
                        foreach ($activeClassesForOt as $ac) {
                            if (strpos($fileLower, strtolower($ac)) !== false) {
                                $matchesActive = true;
                                break;
                            }
                        }
                        // Incluir clases rechazadas también
                        if (!$matchesActive) {
                            foreach ($clasesRechazadas as $rc) {
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
                        if (!$allowFileCrossOt($otName)) {
                            continue;
                        }
                    }

                    $normBase = strtolower(preg_replace('/[\s_]+/', '', $base));
                    if (!in_array($normBase, $normBaseNames)) {
                        $origin = 'otro';
                        if (strpos($relativePath, 'documentos_aprobados') !== false) {
                            $origin = 'aprobado';
                        } elseif (strpos($relativePath, 'documentos_rechazados') !== false) {
                            $origin = 'rechazado';
                        }
                        $relativePathWithPrefix = 'preordenes/' . $relativePath;

                        $otrosArchivos[] = [
                            'nombre' => $relativePathWithPrefix,
                            'url' => route('almacen.fundicion.serve', [
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
            $newDirs = [];
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
                            '/DOCUMENTOS_APROBADOS',
                        'origin' => 'aprobado',
                        'prefix' => 'DOCUMENTOS_APROBADOS/',
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
                            'DOCUMENTACION_GIS/ALMACEN_FUNDICION/' .
                            $otNameSanitized .
                            '/DOCUMENTOS_RECHAZADOS',
                        'origin' => 'rechazado',
                        'prefix' => 'DOCUMENTOS_RECHAZADOS/',
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
                            '/DOCUMENTOS_APROBADOS',
                        'origin' => 'aprobado',
                        'prefix' => 'DOCUMENTOS_APROBADOS/',
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
                    [
                        'dir' =>
                            'DOCUMENTACION_GIS/CALIDAD_FUNDICION/' .
                            $otNameSanitized .
                            '/DOCUMENTOS_RECHAZADOS',
                        'origin' => 'rechazado',
                        'prefix' => 'DOCUMENTOS_RECHAZADOS/',
                        'owner' => 'calidad',
                    ],
                ];

                // --- NUEVO: ESCANEAR RUTAS DE PREORDENES FALTANTES ---
                if ($otName === $reg->ot) {
                    $preOrdenesCandidates = [
                        'DOCUMENTACION_GIS/ALMACEN_FUNDICION/' .
                        $otNameSanitized .
                        '/preordenes',
                        'DOCUMENTACION_GIS/ALMACEN_FUNDICION/' .
                        $otNameSanitized .
                        '/PREORDENES',
                        'DOCUMENTACION_GIS/CALIDAD_FUNDICION/' .
                        $otNameSanitized .
                        '/preordenes',
                        'DOCUMENTACION_GIS/CALIDAD_FUNDICION/' .
                        $otNameSanitized .
                        '/PREORDENES',
                        'DOCUMENTACION_GIS/ALMACEN_FUNDICION/' .
                        $otNameSanitized .
                        '/Documentos_Aprobados/preordenes',
                        'DOCUMENTACION_GIS/ALMACEN_FUNDICION/' .
                        $otNameSanitized .
                        '/DOCUMENTOS_APROBADOS/PREORDENES',
                        'DOCUMENTACION_GIS/CALIDAD_FUNDICION/' .
                        $otNameSanitized .
                        '/Documentos_Aprobados/preordenes',
                        'DOCUMENTACION_GIS/CALIDAD_FUNDICION/' .
                        $otNameSanitized .
                        '/DOCUMENTOS_APROBADOS/PREORDENES',
                        'DOCUMENTACION_GIS/ALMACEN_FUNDICION/' .
                        $otNameSanitized .
                        '/ayudas_visuales/preordenes',
                        'DOCUMENTACION_GIS/ALMACEN_FUNDICION/' .
                        $otNameSanitized .
                        '/ayudas_visuales/preordenes/documentos_aprobados',
                        'DOCUMENTACION_GIS/CALIDAD_FUNDICION/' .
                        $otNameSanitized .
                        '/ayudas_visuales/preordenes/documentos_aprobados',
                        'DOCUMENTACION_GIS/ALMACEN_FUNDICION/' .
                        $otNameSanitized .
                        '/preordenes/documentos_aprobados',
                        'DOCUMENTACION_GIS/CALIDAD_FUNDICION/' .
                        $otNameSanitized .
                        '/preordenes/documentos_aprobados',
                    ];

                    foreach ($preOrdenesCandidates as $poDir) {
                        $owner =
                            strpos($poDir, 'ALMACEN_FUNDICION') !== false
                            ? 'almacen'
                            : 'calidad';
                        $newDirs[] = [
                            'dir' => $poDir,
                            'origin' => 'aprobado',
                            'prefix' => 'preordenes/',
                            'owner' => $owner,
                        ];
                    }
                }

                // --- NUEVO: ESCANEAR PREORDENES Y DOCUMENTOS POR CLASE (MAYÚSCULAS Y MINÚSCULAS) ---
                $clasesBaseList = [
                    '1 - MOLDES',
                    '2 - BOMBILLO',
                    '3 - EMBUDO',
                    '4 - CORONA',
                    '5 - PLATO',
                    '6 - FONDO',
                    '7 - OBTURADOR',
                    '8 - CABEZA DE SOPLO',
                    '9 - CANDADO OBTURADOR',
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
                ];
                foreach ($clasesBaseList as $claseDir) {
                    $claseVariants = array_values(
                        array_unique([
                            $claseDir,
                            strtoupper($claseDir),
                            ucfirst(strtolower($claseDir)),
                        ]),
                    );

                    foreach ($claseVariants as $cVariant) {
                        if ($otName === $reg->ot) {
                            // Preordenes
                            foreach (
                                [
                                    'PREORDENES',
                                    'Preordenes',
                                    'Preordenes_Fundicion',
                                    'preordenes',
                                ]
                                as $pSub
                            ) {
                                $newDirs[] = [
                                    'dir' =>
                                        'DOCUMENTACION_GIS/ALMACEN_FUNDICION/' .
                                        $otNameSanitized .
                                        '/' .
                                        $cVariant .
                                        '/' .
                                        $pSub,
                                    'origin' => 'aprobado',
                                    'prefix' => $cVariant . '/' . $pSub . '/',
                                    'owner' => 'almacen',
                                ];
                                $newDirs[] = [
                                    'dir' =>
                                        'DOCUMENTACION_GIS/CALIDAD_FUNDICION/' .
                                        $otNameSanitized .
                                        '/' .
                                        $cVariant .
                                        '/' .
                                        $pSub,
                                    'origin' => 'aprobado',
                                    'prefix' => $cVariant . '/' . $pSub . '/',
                                    'owner' => 'calidad',
                                ];
                            }

                            // Documentos Aprobados
                            foreach (
                                [
                                    'DOCUMENTOS_APROBADOS',
                                    'Documentos_Aprobados',
                                    'documentos_aprobados',
                                ]
                                as $dSub
                            ) {
                                $newDirs[] = [
                                    'dir' =>
                                        'DOCUMENTACION_GIS/ALMACEN_FUNDICION/' .
                                        $otNameSanitized .
                                        '/' .
                                        $cVariant .
                                        '/' .
                                        $dSub,
                                    'origin' => 'aprobado',
                                    'prefix' => $cVariant . '/' . $dSub . '/',
                                    'owner' => 'almacen',
                                ];
                                $newDirs[] = [
                                    'dir' =>
                                        'DOCUMENTACION_GIS/CALIDAD_FUNDICION/' .
                                        $otNameSanitized .
                                        '/' .
                                        $cVariant .
                                        '/' .
                                        $dSub,
                                    'origin' => 'aprobado',
                                    'prefix' => $cVariant . '/' . $dSub . '/',
                                    'owner' => 'calidad',
                                ];
                                foreach (
                                    ['Almacen', 'Calidad', 'ALMACEN', 'CALIDAD']
                                    as $dept
                                ) {
                                    $newDirs[] = [
                                        'dir' =>
                                            'DOCUMENTACION_GIS/ALMACEN_FUNDICION/' .
                                            $otNameSanitized .
                                            '/' .
                                            $cVariant .
                                            '/' .
                                            $dSub .
                                            '/' .
                                            $dept,
                                        'origin' => 'aprobado',
                                        'prefix' => $cVariant . '/' . $dSub . '/' . $dept . '/',
                                        'owner' => 'almacen',
                                    ];
                                    $newDirs[] = [
                                        'dir' =>
                                            'DOCUMENTACION_GIS/CALIDAD_FUNDICION/' .
                                            $otNameSanitized .
                                            '/' .
                                            $cVariant .
                                            '/' .
                                            $dSub .
                                            '/' .
                                            $dept,
                                        'origin' => 'aprobado',
                                        'prefix' => $cVariant . '/' . $dSub . '/' . $dept . '/',
                                        'owner' => 'calidad',
                                    ];
                                }
                            }

                            // Documentos Rechazados
                            foreach (
                                [
                                    'DOCUMENTOS_RECHAZADOS',
                                    'Documentos_Rechazados',
                                    'documentos_rechazados',
                                ]
                                as $rSub
                            ) {
                                $newDirs[] = [
                                    'dir' =>
                                        'DOCUMENTACION_GIS/ALMACEN_FUNDICION/' .
                                        $otNameSanitized .
                                        '/' .
                                        $cVariant .
                                        '/' .
                                        $rSub,
                                    'origin' => 'rechazado',
                                    'prefix' => $cVariant . '/' . $rSub . '/',
                                    'owner' => 'almacen',
                                ];
                                $newDirs[] = [
                                    'dir' =>
                                        'DOCUMENTACION_GIS/CALIDAD_FUNDICION/' .
                                        $otNameSanitized .
                                        '/' .
                                        $cVariant .
                                        '/' .
                                        $rSub,
                                    'origin' => 'rechazado',
                                    'prefix' => $cVariant . '/' . $rSub . '/',
                                    'owner' => 'calidad',
                                ];
                                foreach (
                                    ['Almacen', 'Calidad', 'ALMACEN', 'CALIDAD']
                                    as $dept
                                ) {
                                    $newDirs[] = [
                                        'dir' =>
                                            'DOCUMENTACION_GIS/ALMACEN_FUNDICION/' .
                                            $otNameSanitized .
                                            '/' .
                                            $cVariant .
                                            '/' .
                                            $rSub .
                                            '/' .
                                            $dept,
                                        'origin' => 'rechazado',
                                        'prefix' => $cVariant . '/' . $rSub . '/' . $dept . '/',
                                        'owner' => 'almacen',
                                    ];
                                    $newDirs[] = [
                                        'dir' =>
                                            'DOCUMENTACION_GIS/CALIDAD_FUNDICION/' .
                                            $otNameSanitized .
                                            '/' .
                                            $cVariant .
                                            '/' .
                                            $rSub .
                                            '/' .
                                            $dept,
                                        'origin' => 'rechazado',
                                        'prefix' => $cVariant . '/' . $rSub . '/' . $dept . '/',
                                        'owner' => 'calidad',
                                    ];
                                }
                            }

                            // Documentos Escaneados
                            foreach (
                                [
                                    'ESCANEADOS',
                                    'Escaneados',
                                    'escaneados',
                                    'DOCUMENTOS_ESCANEADOS',
                                    'Documentos_Escaneados',
                                    'documentos_escaneados',
                                ]
                                as $eSub
                            ) {
                                $newDirs[] = [
                                    'dir' =>
                                        'DOCUMENTACION_GIS/ALMACEN_FUNDICION/' .
                                        $otNameSanitized .
                                        '/' .
                                        $cVariant .
                                        '/' .
                                        $eSub,
                                    'origin' => 'aprobado',
                                    'prefix' => $cVariant . '/' . $eSub . '/',
                                    'owner' => 'almacen',
                                ];
                                $newDirs[] = [
                                    'dir' =>
                                        'DOCUMENTACION_GIS/CALIDAD_FUNDICION/' .
                                        $otNameSanitized .
                                        '/' .
                                        $cVariant .
                                        '/' .
                                        $eSub,
                                    'origin' => 'aprobado',
                                    'prefix' => $cVariant . '/' . $eSub . '/',
                                    'owner' => 'calidad',
                                ];
                                foreach (['Almacen', 'Calidad'] as $dept) {
                                    $newDirs[] = [
                                        'dir' =>
                                            'DOCUMENTACION_GIS/ALMACEN_FUNDICION/' .
                                            $otNameSanitized .
                                            '/' .
                                            $cVariant .
                                            '/' .
                                            $eSub .
                                            '/' .
                                            $dept,
                                        'origin' => 'aprobado',
                                        'prefix' => $cVariant . '/' . $eSub . '/' . $dept . '/',
                                        'owner' => 'almacen',
                                    ];
                                    $newDirs[] = [
                                        'dir' =>
                                            'DOCUMENTACION_GIS/CALIDAD_FUNDICION/' .
                                            $otNameSanitized .
                                            '/' .
                                            $cVariant .
                                            '/' .
                                            $cVariant .
                                            '/' .
                                            $eSub .
                                            '/' .
                                            $dept,
                                        'origin' => 'aprobado',
                                        'prefix' => $cVariant . '/' . $eSub . '/' . $dept . '/',
                                        'owner' => 'calidad',
                                    ];
                                }
                            }

                            // Formatos de Liberación (LDM firmados por Calidad)
                            foreach (
                                [
                                    'FORMATOS_LIBERACION',
                                    'Formatos_Liberacion',
                                    'formatos_liberacion',
                                ]
                                as $fSub
                            ) {
                                $newDirs[] = [
                                    'dir' =>
                                        'DOCUMENTACION_GIS/ALMACEN_FUNDICION/' .
                                        $otNameSanitized .
                                        '/' .
                                        $cVariant .
                                        '/' .
                                        $fSub,
                                    'origin' => 'aprobado',
                                    'prefix' => $cVariant . '/' . $fSub . '/',
                                    'owner' => 'almacen',
                                ];
                                $newDirs[] = [
                                    'dir' =>
                                        'DOCUMENTACION_GIS/CALIDAD_FUNDICION/' .
                                        $otNameSanitized .
                                        '/' .
                                        $cVariant .
                                        '/' .
                                        $fSub,
                                    'origin' => 'aprobado',
                                    'prefix' => $cVariant . '/' . $fSub . '/',
                                    'owner' => 'calidad',
                                ];
                            }
                        }

                        // --- FALLBACK PARA OTs DE REPROCESO: Buscar dibujos en carpeta de OT Padre Heredada ---
                        $parentOtSanitized = preg_replace(
                            '/_.*_R\d+$|_R\d+$/i',
                            '',
                            $otNameSanitized,
                        );
                        if ($parentOtSanitized && $parentOtSanitized !== $otNameSanitized) {
                            foreach (
                                [
                                    'DIBUJOS',
                                    'Dibujos',
                                    'dibujos',
                                    'DIBUJOS_FUNDICION',
                                    'Dibujos_Fundicion',
                                ]
                                as $dibSub
                            ) {
                                $newDirs[] = [
                                    'dir' =>
                                        'DOCUMENTACION_GIS/ALMACEN_FUNDICION/' .
                                        $parentOtSanitized .
                                        '/' .
                                        $cVariant .
                                        '/' .
                                        $dibSub,
                                    'origin' => 'dibujo',
                                    'prefix' => $cVariant . '/' . $dibSub . '/',
                                    'owner' => 'almacen',
                                ];
                                $newDirs[] = [
                                    'dir' =>
                                        'DOCUMENTACION_GIS/CALIDAD_FUNDICION/' .
                                        $parentOtSanitized .
                                        '/' .
                                        $cVariant .
                                        '/' .
                                        $dibSub,
                                    'origin' => 'dibujo',
                                    'prefix' => $cVariant . '/' . $dibSub . '/',
                                    'owner' => 'calidad',
                                ];
                            }
                        }
                    }
                }

                $knownClasses = $activeClassesForOt;
                foreach ($newDirs as $dirInfo) {
                    $targetDir = $dirInfo['dir'];
                    $origin = $dirInfo['origin'];
                    $prefix = $dirInfo['prefix'];

                    if (
                        \Illuminate\Support\Facades\Storage::disk('local')->exists($targetDir)
                    ) {
                        $files = \Illuminate\Support\Facades\Storage::disk('local')->allFiles(
                            $targetDir,
                        );
                        foreach ($files as $f) {
                            $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
                            $isPdf = $ext === 'pdf';
                            $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                            $isDwg = $ext === 'dwg';

                            if (!$isPdf && !$isImage && !$isDwg) {
                                continue;
                            }

                            $fNorm = str_replace('\\', '/', $f);
                            $dirNorm = str_replace('\\', '/', $targetDir);
                            $relativePath = ltrim(str_replace($dirNorm, '', $fNorm), '/');
                            $base = basename($relativePath);

                            $fileLower = strtolower($relativePath);
                            $fileClasses = [];
                            foreach ($knownClasses as $kc) {
                                if (strpos($fileLower, strtolower($kc)) !== false) {
                                    $fileClasses[] = strtolower($kc);
                                }
                            }
                            if (!empty($fileClasses)) {
                                // Verificar que la clase del archivo pertenece a esta OT (activas o rechazadas)
                                $hasInactiveClass = false;
                                foreach ($fileClasses as $fc) {
                                    if (
                                        !in_array(strtoupper($fc), $activeClassesForOt) &&
                                        !in_array($fc, $clasesRechazadas)
                                    ) {
                                        $hasInactiveClass = true;
                                        break;
                                    }
                                }
                                if ($hasInactiveClass) {
                                    continue;
                                }
                                // El origin viene del directorio ($dirInfo['origin']), NO de la clase del archivo.
                                // Un ConfirmacionModelo en Documentos_Aprobados/ es siempre aprobado.
                            } else {
                                if ($otName !== $reg->ot) {
                                    continue;
                                }
                            }

                            $normBase = strtolower(preg_replace('/[\s_]+/', '', $base));
                            if (
                                $origin === 'dibujo' ||
                                $isDwg ||
                                strpos(strtolower($targetDir), 'dibujo') !== false
                            ) {
                                if (!in_array($base, $dibujoBaseNames)) {
                                    $relativePathWithPrefix = $prefix . $relativePath;
                                    $archivos[] = [
                                        'nombre' => $relativePathWithPrefix,
                                        'url' => route('almacen.fundicion.serve', [
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
                                    $dibujoBaseNames[] = $base;
                                    $baseNames[] = $base;
                                    $normBaseNames[] = $normBase;
                                }
                            } elseif (!in_array($normBase, $normBaseNames)) {
                                $relativePathWithPrefix = $prefix . $relativePath;
                                $otrosArchivos[] = [
                                    'nombre' => $relativePathWithPrefix,
                                    'url' => route('almacen.fundicion.serve', [
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

                $otSanitizada = preg_replace('/[^\w\s\-]/', '', $otName);
                $otSanitizada = preg_replace('/[\s]+/', '_', trim($otSanitizada));

                if (file_exists($liberacionesPath) && $otName === $reg->ot) {
                    // Buscar LDM y RDM PDFs generados para ESTA OT en public/liberaciones_pdf
                    $otLow = mb_strtolower($otSanitizada, 'UTF-8');
                    $otNameLow = mb_strtolower($otName, 'UTF-8');
                    $ldmFiles = array_merge(
                        glob("{$liberacionesPath}/*{$otSanitizada}*.pdf") ?: [],
                        glob("{$liberacionesPath}/F*CCL*{$otSanitizada}*.pdf") ?: [],
                    );
                    foreach (array_unique($ldmFiles) as $f) {
                        $base = basename($f);
                        $fileLower = mb_strtolower($base, 'UTF-8');
                        if (
                            !str_contains($fileLower, $otLow) &&
                            !str_contains($fileLower, $otNameLow)
                        ) {
                            continue;
                        }

                        // FIX: Evitar que documentos de OTs de Reproceso (ej. _R1) se muestren en la OT original
                        $isReprocesoOT = (bool) preg_match('/_R\d+$/i', $otName);
                        if (!$isReprocesoOT && preg_match('/_R\d+\.pdf$/i', $base)) {
                            continue; // Es un documento de un reproceso, omitir en la OT original
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
                        ];
                        $hasKnownClass = false;
                        foreach ($knownClasses as $kc) {
                            if (strpos($fileLower, strtolower($kc)) !== false) {
                                $hasKnownClass = true;
                                break;
                            }
                        }
                        if ($hasKnownClass) {
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
                            if (!$allowFileCrossOt($otName)) {
                                continue;
                            }
                        }
                        $normBase = strtolower(preg_replace('/[\s_]+/', '', $base));
                        if (!in_array($normBase, $normBaseNames)) {
                            $isRechazado =
                                strpos($fileLower, 'rdm') !== false ||
                                strpos($fileLower, 'rechazado') !== false;
                            $origin = $isRechazado ? 'rechazado' : 'aprobado';
                            $otrosArchivos[] = [
                                'nombre' => $base,
                                'url' => route('almacen.fundicion.serve', [
                                    'ot' => $otName,
                                    'archivo' => $base,
                                    'tipo' => 'liberacion',
                                    'origin' => $origin,
                                ]),
                                'tipo' => 'liberacion',
                                'ot' => $otName,
                                'origin' => $origin,
                                'owner' => 'calidad',
                            ];
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
                        $fileLower = strtolower($base);

                        // FIX: Evitar que documentos de OTs de Reproceso (ej. _R1) se muestren en la OT original
                        $isReprocesoOT = (bool) preg_match('/_R\d+$/i', $otName);
                        if (!$isReprocesoOT && preg_match('/_R\d+\.pdf$/i', $base)) {
                            continue; // Es un documento de un reproceso, omitir en la OT original
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
                        ];
                        $hasKnownClass = false;
                        foreach ($knownClasses as $kc) {
                            if (strpos($fileLower, strtolower($kc)) !== false) {
                                $hasKnownClass = true;
                                break;
                            }
                        }
                        if ($hasKnownClass) {
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
                            if (!$allowFileCrossOt($otName)) {
                                continue;
                            }
                        }
                        $normBase = strtolower(preg_replace('/[\s_]+/', '', $base));
                        if (!in_array($normBase, $normBaseNames)) {
                            $rechazadosOtros[] = [
                                'nombre' => $base,
                                'url' => route('almacen.fundicion.serve', [
                                    'ot' => $otName,
                                    'archivo' => $base,
                                    'tipo' => 'liberacion',
                                    'origin' => 'rechazado',
                                ]),
                                'tipo' => 'liberacion',
                                'ot' => $otName,
                                'origin' => 'rechazado',
                                'owner' => 'calidad',
                            ];
                            $baseNames[] = $base;
                            $normBaseNames[] = $normBase;
                        }
                    }
                }
            }
        }
        // Aplicar filtros de visibilidad
        $userPerfil = Auth::user()->perfil;
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
                    strpos($nameLow, 'liberacion') === false) ||
                strpos($nameLow, 'escaneado') !== false;

            // Si el archivo es de Calidad y no es preorden ni confirmación, verificar que Calidad haya enviado efectivamente la alerta por correo
            if (
                $archivo['owner'] === 'calidad' &&
                !$isPreorden &&
                strpos($nameLow, 'confirmacion') === false
            ) {
                /** @var FundicionHistory|null $fileHistory */
                $fileHistory = $relatedRecords->firstWhere('ot', $archivo['ot']);
                $status =
                    $targetReg->calidad_revision_status ??
                    ($fileHistory ? $fileHistory->calidad_revision_status : null);
                $calidadAlertaEnviada =
                    in_array($status, [
                        'calidad_aprobado',
                        'calidad_rechazado',
                        'calidad_mixto',
                        'calidad_parcial',
                        'casting_aprobado',
                    ]) ||
                    \App\Models\LiberacionModeloFundicion::where(function ($q) use ($archivo, $targetReg, ) {
                        $q->where('ot', '=', $archivo['ot'])->orWhere(
                            'ot',
                            '=',
                            $targetReg->ot,
                        );
                    })
                        ->where('alerta_enviada', true)
                        ->exists() ||
                    \App\Models\ScarModelo::where(function ($q) use ($archivo, $targetReg) {
                        $q->where('ot', '=', $archivo['ot'])->orWhere(
                            'ot',
                            '=',
                            $targetReg->ot,
                        );
                    })
                        ->whereIn('estatus', ['alertado', 'cerrado'])
                        ->exists();
                if (!$calidadAlertaEnviada) {
                    continue; // Ocultar formatos F-CCL-LDM / SCAR en Almacén hasta que Calidad envíe la alerta
                }
            }

            if ($userPerfil != 1 && $userPerfil != 2) {
                if ($userPerfil == 4 || $userPerfil == 3) {
                    // Calidad o Master
                    // Calidad/Master solo ve preordenes si pre_orden_email_sent es true
                    if ($isPreorden) {
                        /** @var FundicionHistory|null $fileHistory */
                        $fileHistory = $relatedRecords->firstWhere('ot', $archivo['ot']);
                        $hasPreorden =
                            ($fileHistory && $fileHistory->pre_orden_email_sent) ||
                            !empty($targetReg->pre_orden_sent) ||
                            !empty($targetReg->pre_orden_email_sent) ||
                            \App\Models\PreOrdenFundicion::where('ot', $archivo['ot'])
                                ->orWhere('ot', $targetReg->ot)
                                ->exists();
                        if (
                            !$hasPreorden &&
                            strpos($nameLow, 'documentos_aprobados') === false
                        ) {
                            continue;
                        }
                    }
                } elseif ($userPerfil == 5) {
                    // Almacén
                    // Almacén ve preordenes y confirmaciones (calidad ya se filtró arriba)
                }
            }
            $filteredOtros[] = $archivo;
        }
        $otrosArchivos = $filteredOtros;

        $almacenPreordenes = [];
        $calidadAprobadosLdm = [];
        $archivosRechazados = [];
        foreach ($otrosArchivos as $archivo) {
            $nameLow = strtolower($archivo['nombre']);
            $baseLow = strtolower(basename($archivo['nombre']));
            if (
                strpos($nameLow, 'documentos_rechazados') !== false ||
                strpos($baseLow, 'rechazado') !== false ||
                strpos($baseLow, 'scar') !== false ||
                strpos($baseLow, 'rdm') !== false ||
                strpos($baseLow, 'fdrdm') !== false ||
                strpos($nameLow, 'f_ccl_rdm') !== false ||
                strpos($nameLow, 'f_ccl_scar') !== false
            ) {
                $archivosRechazados[] = $archivo;
            } elseif (
                strpos($nameLow, 'fdldm') !== false ||
                strpos($nameLow, 'f_ccl_ldm') !== false
            ) {
                $calidadAprobadosLdm[] = $archivo;
            } elseif (
                // CFM escaneado de ESCANEADOS cuando ya fue procesado por Calidad → va al container de Casting como referencia
                in_array($targetReg->calidad_revision_status, [
                    'calidad_aprobado', 'calidad_rechazado', 'calidad_mixto', 'calidad_parcial', 'casting_aprobado',
                ]) &&
                (strpos($nameLow, 'escaneados/') !== false || strpos($nameLow, '/escaneados/') !== false) &&
                (strpos($baseLow, 'cfm') !== false || strpos($baseLow, 'f_alm_cfm') !== false)
            ) {
                $calidadAprobadosLdm[] = $archivo;
            } elseif (
                strpos($nameLow, 'preorden_casting') !== false ||
                strpos($nameLow, 'preorden_modelo') !== false ||
                strpos($nameLow, 'confirmacion_modelo') !== false ||
                strpos($baseLow, 'pre-orden') !== false ||
                strpos($baseLow, 'preorden') !== false ||
                strpos($baseLow, 'confirmacion') !== false ||
                strpos($baseLow, 'escaneado') !== false ||
                strpos($baseLow, 'pfm') !== false ||
                strpos($baseLow, 'cfm') !== false ||
                strpos($baseLow, 'efm') !== false ||
                strpos($baseLow, 'pfc') !== false ||
                strpos($baseLow, 'efc') !== false ||
                strpos($nameLow, 'f_alm_efc') !== false ||
                strpos($nameLow, 'f_alm_cfm') !== false ||
                strpos($nameLow, 'preordenes/') !== false ||
                strpos($nameLow, 'escaneados/') !== false
            ) {
                $almacenPreordenes[] = $archivo;
            } else {
                $calidadAprobadosLdm[] = $archivo;
            }
        }
        $archivosAprobados = $almacenPreordenes;
        $countAprobados = count($calidadAprobadosLdm);
        $countRechazados = count($archivosRechazados);

        $countAyudas = count($ayudasArchivos);
        $countOtros = count($otrosArchivos);

        // ── CALCULAR APROBADOS Y RECHAZADOS DE CADA CLASE ──
        // (Calculado ANTES de showControlCard para poder usarlos en la lógica de visibilidad)
        $liberacionesAll = \App\Models\LiberacionModeloFundicion::where(
            'ot',
            $targetReg->ot,
        )->get();
        $latestLiberacionesByClass = [];
        foreach ($liberacionesAll as $lib) {
            $tipo = $lib->tipo_modelo;
            $libOt = $lib->ot;

            preg_match('/_R(\d+)$/', $libOt, $matches);
            $suffixNum = isset($matches[1]) ? (int) $matches[1] : 0;

            $shouldReplace = !isset($latestLiberacionesByClass[$tipo]);
            if (!$shouldReplace) {
                $existSuffix = $latestLiberacionesByClass[$tipo]['suffix'];
                $existId = $latestLiberacionesByClass[$tipo]['lib']->id;
                if (
                    $suffixNum > $existSuffix ||
                    ($suffixNum === $existSuffix && $lib->id > $existId)
                ) {
                    $shouldReplace = true;
                }
            }
            if ($shouldReplace) {
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
            if ($lib->alerta_enviada) {
                if (
                    $lib->decision === 'aprobar' ||
                    $lib->estado === 'aprobado' ||
                    $lib->estado === 'aprobada'
                ) {
                    $aprobadosRaw[] = $tipo;
                } elseif (
                    $lib->decision === 'rechazar' ||
                    $lib->estado === 'rechazado' ||
                    $lib->estado === 'rechazada'
                ) {
                    $rechazadosRaw[] = $tipo;
                }
            }
        }

        // Extraer clases de archivos aprobados en $calidadAprobadosLdm
        foreach ($calidadAprobadosLdm as $docAprob) {
            $baseName = basename($docAprob['nombre']);
            if (preg_match('/F_CCL_LDM_([^\.]+)/i', $baseName, $mMatches)) {
                $extractedClass = trim(str_replace(['_', '-'], ' ', $mMatches[1]));
                if (!empty($extractedClass)) {
                    $aprobadosRaw[] = $extractedClass;
                }
            }
            foreach ($activeClassesForOt as $ac) {
                if (!empty($ac) && strpos(strtolower($baseName), strtolower($ac)) !== false) {
                    $aprobadosRaw[] = $ac;
                }
            }
        }

        // Normalizar raw arrays
        $aprobadosRaw = array_map(function ($c) {
            return \App\Services\FundicionPaths::normalizeClass($c);
        }, $aprobadosRaw);
        $rechazadosRaw = array_map(function ($c) {
            return \App\Services\FundicionPaths::normalizeClass($c);
        }, $rechazadosRaw);

        // Normalizar y deduplicar aprobados y rechazados con respecto a activeClassesForOt
        $aprobados = [];
        foreach ($aprobadosRaw as $cRaw) {
            $cLow = strtolower(trim($cRaw));
            if (empty($cLow)) {
                continue;
            }
            $matched = false;
            foreach ($activeClassesForOt as $ac) {
                $acLow = strtolower(trim($ac));
                if (
                    $cLow === $acLow ||
                    strpos($cLow, $acLow) !== false ||
                    strpos($acLow, $cLow) !== false
                ) {
                    $aprobados[] = $ac;
                    $matched = true;
                    break;
                }
            }
            if (!$matched) {
                $aprobados[] = trim($cRaw);
            }
        }
        $aprobados = array_values(array_unique($aprobados));

        // Fix: Si la clase está rechazada en su estado más reciente, no debe estar en aprobados
        // aunque existan archivos aprobados viejos en disco de ciclos anteriores.
        $rechazadosRawNorm = array_map('strtolower', $rechazadosRaw);
        $aprobados = array_values(array_filter($aprobados, function ($c) use ($rechazadosRawNorm) {
            return !in_array(strtolower($c), $rechazadosRawNorm);
        }));

        $rechazados = array_values(
            array_unique(
                array_filter($rechazadosRaw, function ($clase) use ($activeClassesForOt) {
                    return in_array(
                        strtolower($clase),
                        array_map('strtolower', $activeClassesForOt),
                    );
                }),
            ),
        );

        // Fallback: si $tieneAprobados o count($calidadAprobadosLdm) > 0 y $aprobados está vacío
        $isCalidadAlertedLocal =
            in_array($reg->calidad_revision_status, [
                'calidad_aprobado',
                'calidad_rechazado',
                'calidad_mixto',
                'calidad_parcial',
                'casting_aprobado',
            ]) ||
            \App\Models\LiberacionModeloFundicion::where('ot', $reg->ot)
                ->where('alerta_enviada', true)
                ->exists();
        if (
            !$isReprocesoOT &&
            empty($aprobados) &&
            ($isCalidadAlertedLocal ||
                count($calidadAprobadosLdm) > 0 ||
                in_array($targetReg->calidad_revision_status, [
                    'calidad_aprobado',
                    'casting_aprobado',
                ]))
        ) {
            $rechazadosNormLocal = array_map('strtolower', $rechazados);
            $aprobados = array_values(
                array_filter($activeClassesForOt, function ($ac) use ($rechazadosNormLocal) {
                    return !in_array(strtolower($ac), $rechazadosNormLocal);
                }),
            );
        }
        // Clasificar dibujos y ayudas por etapa (Fabricación de Modelo vs Casting)
        $aprobadosNorm = array_map('strtolower', $aprobados);
        $rechazadosNorm = array_map('strtolower', $rechazados);
        $clasesFabricacion = array_values(
            array_diff(
                array_map('strtolower', $activeClassesForOt),
                array_merge($aprobadosNorm, $rechazadosNorm),
            ),
        );
        $isMixedProcess = count($aprobadosNorm) > 0 && count($clasesFabricacion) > 0;

        $dibujosCasting = array_values(
            array_filter($archivos, function ($d) use ($aprobadosNorm) {
                $nameLow = strtolower($d['nombre']);
                foreach ($aprobadosNorm as $ap) {
                    if ($ap !== '' && strpos($nameLow, $ap) !== false) {
                        return true;
                    }
                }
                return false;
            }),
        );

        $dibujosModelo = array_values(
            array_filter($archivos, function ($d) use ($clasesFabricacion) {
                $nameLow = strtolower($d['nombre']);
                foreach ($clasesFabricacion as $cf) {
                    if ($cf !== '' && strpos($nameLow, $cf) !== false) {
                        return true;
                    }
                }
                return false;
            }),
        );

        $ayudasCasting = array_values(
            array_filter($ayudasArchivos, function ($a) use ($aprobadosNorm) {
                $nameLow = strtolower($a['nombre']);
                foreach ($aprobadosNorm as $ap) {
                    if ($ap !== '' && strpos($nameLow, $ap) !== false) {
                        return true;
                    }
                }
                return false;
            }),
        );

        $ayudasModelo = array_values(
            array_filter($ayudasArchivos, function ($a) use ($clasesFabricacion) {
                $nameLow = strtolower($a['nombre']);
                foreach ($clasesFabricacion as $cf) {
                    if ($cf !== '' && strpos($nameLow, $cf) !== false) {
                        return true;
                    }
                }
                return false;
            }),
        );

        $dibujosRechazadosOrig = array_values(
            array_filter($archivos, function ($d) use ($rechazadosNorm) {
                $nameLow = strtolower($d['nombre']);
                foreach ($rechazadosNorm as $r) {
                    if ($r !== '' && strpos($nameLow, $r) !== false) {
                        return true;
                    }
                }
                return false;
            }),
        );

        $ayudasRechazadosOrig = array_values(
            array_filter($ayudasArchivos, function ($a) use ($rechazadosNorm) {
                $nameLow = strtolower($a['nombre']);
                foreach ($rechazadosNorm as $r) {
                    if ($r !== '' && strpos($nameLow, $r) !== false) {
                        return true;
                    }
                }
                return false;
            }),
        );

        // Clasificar rechazados:
        // Guardar primero los que vienen del escaneo de filesystem (acumulados arriba)
        // y limpiar los arrays para que la reclasificación empiece de cero.
        $rechazadosOtrosFilesystem = $rechazadosOtros ?? [];
        $rechazadosAyudasFilesystem = $rechazadosAyudas ?? [];
        $rechazadosDibujos = $rechazadosDibujos ?? [];
        // Reclasificar $archivosRechazados (los de la lógica de otrosArchivos)
        $rechazadosAyudas = [];
        $rechazadosOtros = [];
        foreach ($archivosRechazados as $rArchivo) {
            $nameLow = strtolower($rArchivo['nombre']);
            $ext = pathinfo($nameLow, PATHINFO_EXTENSION);
            $isImg = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);

            $rArchivo['ot'] = $rArchivo['ot'] ?? $reg->ot;
            $rArchivo['tipo'] = $rArchivo['tipo'] ?? ($isImg ? 'imagen' : 'otro');

            if (
                strpos($nameLow, 'scar') !== false ||
                strpos($nameLow, 'f_ccl_scar') !== false ||
                strpos($nameLow, 'f_ccl_rdm') !== false ||
                strpos($nameLow, 'foto') !== false
            ) {
                $rechazadosOtros[] = $rArchivo;
            } elseif (
                strpos($nameLow, 'ayudas_visuales') !== false ||
                strpos($nameLow, 'ayudas-visuales') !== false ||
                $isImg
            ) {
                if ($rArchivo['tipo'] === 'otro') {
                    $rArchivo['tipo'] = 'ayuda';
                }
                $rechazadosAyudas[] = $rArchivo;
            } elseif (
                strpos($nameLow, 'dibujos') !== false ||
                strpos($nameLow, 'dibujo') !== false
            ) {
                $rechazadosDibujos[] = $rArchivo;
            } else {
                $rechazadosOtros[] = $rArchivo;
            }
        }
        // Combinar los del filesystem con los reclasificados, deduplicando por nombre base
        $baseNamesRechOtros = array_map(fn($a) => basename($a['nombre']), $rechazadosOtros);
        foreach ($rechazadosOtrosFilesystem as $rFs) {
            if (!in_array(basename($rFs['nombre']), $baseNamesRechOtros)) {
                $rechazadosOtros[] = $rFs;
                $baseNamesRechOtros[] = basename($rFs['nombre']);
            }
        }
        $baseNamesRechAyudas = array_map(fn($a) => basename($a['nombre']), $rechazadosAyudas);
        foreach ($rechazadosAyudasFilesystem as $rFs) {
            if (!in_array(basename($rFs['nombre']), $baseNamesRechAyudas)) {
                $rechazadosAyudas[] = $rFs;
                $baseNamesRechAyudas[] = basename($rFs['nombre']);
            }
        }
        // ── CONTROL DE VISIBILIDAD DE LA CARD DE ALMACÉN Y CONTEO EXACTO DE TARJETAS ──
        $hasVerdictosPendientes = count($aprobados) > 0 || count($rechazados) > 0;
        $isFinalized =
            $targetReg->calidad_revision_status === 'casting_aprobado' &&
            !$hasVerdictosPendientes;
        if ($hasVerdictosPendientes) {
            $isFinalized = false;
        }
        $isCalidadAlerted =
            in_array($reg->calidad_revision_status, [
                'calidad_aprobado',
                'calidad_rechazado',
                'calidad_mixto',
                'calidad_parcial',
                'casting_aprobado',
            ]) ||
            \App\Models\LiberacionModeloFundicion::where('ot', $reg->ot)
                ->where('alerta_enviada', true)
                ->exists();

        $castingEmailSent = $reg->calidad_revision_status === 'casting_aprobado';

        $rechazadosSinPreorden = [];
        if ($isCalidadAlerted && count($rechazados) > 0 && !$reg->rechazos_procesados) {
            $rechazadosNormFab = array_map('strtolower', $rechazados);
            $preordenesSentClassesFab = [];
            $preOrdenesEnviadasFab = \App\Models\PreOrdenFundicion::where('ot', $targetReg->ot)
                ->where('is_sent', 1)
                ->get();
            foreach ($preOrdenesEnviadasFab as $poFab) {
                $filasFab = is_string($poFab->filas)
                    ? json_decode($poFab->filas, true)
                    : $poFab->filas;
                if (is_array($filasFab)) {
                    foreach ($filasFab as $fFab) {
                        if (!empty($fFab['clase'] ?? $fFab['clase_nombre'])) {
                            $preordenesSentClassesFab[] = strtolower(
                                $fFab['clase'] ?? $fFab['clase_nombre'],
                            );
                        }
                    }
                }
            }
            foreach ($rechazadosNormFab as $rClase) {
                $cubiertaFab = false;
                foreach ($preordenesSentClassesFab as $psc) {
                    if (strpos($psc, $rClase) !== false || strpos($rClase, $psc) !== false) {
                        $cubiertaFab = true;
                        break;
                    }
                }
                if (!$cubiertaFab) {
                    $rechazadosSinPreorden[] = $rClase;
                }
            }
        }
        $hayRechazadosSinPreorden = count($rechazadosSinPreorden) > 0;

        $aprobadosNorm = array_map('strtolower', $aprobados);
        $rechazadosNorm = array_map('strtolower', $rechazados);

        $almacenPreordenesFab = array_values(
            array_filter($almacenPreordenes, function ($doc) use ($clasesFabricacion, $isCalidadAlerted) {
                $pathLow = strtolower($doc['nombre']);
                $nameLow = strtolower(basename($doc['nombre']));

                // Nunca incluir documentos de casting ni LDM de calidad en la sección de fabricación
                if (
                    str_contains($pathLow, 'preorden_casting') ||
                    str_contains($pathLow, 'casting') ||
                    str_contains($pathLow, 'fdldm') ||
                    str_contains($nameLow, 'pfc') ||
                    str_contains($nameLow, 'f_alm_pfc') ||
                    str_contains($nameLow, 'efc') ||
                    str_contains($nameLow, 'f_alm_efc') ||
                    str_contains($nameLow, 'f_ccl_ldm') ||
                    str_contains($nameLow, 'fdldm')
                ) {
                    return false;
                }

                // Si Calidad ya respondió, excluir los escaneados (CFM ya firmado) —
                // son documentos históricos, no acciones pendientes de fabricación.
                $isEscaneado = str_contains($pathLow, 'escaneados/') ||
                    str_contains($nameLow, 'escaneado') ||
                    str_contains($nameLow, 'f_alm_cfm') ||
                    str_contains($nameLow, 'cfm');
                if ($isCalidadAlerted && $isEscaneado) {
                    return false;
                }

                if (empty($clasesFabricacion)) {
                    // Incluso sin clases definidas, escaneados ya procesados no deben aparecer
                    if ($isCalidadAlerted && $isEscaneado) {
                        return false;
                    }
                    return true;
                }
                if (
                    str_contains($nameLow, 'preorden') ||
                    str_contains($nameLow, 'pre-orden') ||
                    str_contains($nameLow, 'escaneado') ||
                    str_contains($pathLow, 'escaneados') ||
                    str_contains($nameLow, 'cfm') ||
                    str_contains($nameLow, 'f_alm_cfm') ||
                    str_contains($nameLow, 'pfm') ||
                    str_contains($nameLow, 'efm') ||
                    str_contains($pathLow, 'preorden_modelo') ||
                    str_contains($pathLow, 'confirmacion_modelo')
                ) {
                    return true;
                }
                foreach ($clasesFabricacion as $cf) {
                    if ($cf !== '' && strpos($nameLow, strtolower($cf)) !== false) {
                        return true;
                    }
                }
                return false;
            }),
        );

        $tieneArchivosFabricacion =
            count($dibujosModelo) > 0 ||
            count($ayudasModelo) > 0 ||
            count($almacenPreordenesFab) > 0;
        $tieneFabricacion =
            (!$isCalidadAlerted && !$castingEmailSent) ||
            $hayRechazadosSinPreorden ||
            $tieneArchivosFabricacion;

        $clasesFabricacionHeader = $clasesFabricacion;
        if (empty($clasesFabricacionHeader)) {
            $archivosFabAll = array_merge($almacenPreordenesFab, $dibujosModelo, $ayudasModelo);
            $extractedFab = [];
            foreach ($archivosFabAll as $af) {
                $bName = basename($af['nombre']);
                foreach ($activeClassesForOt as $ac) {
                    if (!empty($ac) && strpos(strtolower($bName), strtolower($ac)) !== false) {
                        $extractedFab[] = $ac;
                    }
                }
            }
            $clasesFabricacionHeader = array_values(array_unique($extractedFab));
            if (empty($clasesFabricacionHeader)) {
                $clasesFabricacionHeader = $activeClassesForOt;
            }
        }
        $aprobadosNorm = array_map('strtolower', $aprobados ?? []);
        $rechazadosNorm = array_map('strtolower', $rechazados ?? []);

        $calidadAprobadosLdmCasting = array_values(
            array_filter($calidadAprobadosLdm ?? [], function ($doc) use ($aprobadosNorm, $rechazadosNorm, ) {
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
            }),
        );

        $almacenPreordenesCasting = array_values(
            array_filter($almacenPreordenes ?? [], function ($doc) use ($aprobadosNorm) {
                $pathLow = strtolower($doc['nombre']);
                $nameLow = strtolower(basename($doc['nombre']));
                $isCastingDoc =
                    str_contains($pathLow, 'preorden_casting') ||
                    str_contains($pathLow, 'casting') ||
                    str_contains($nameLow, 'pfc') ||
                    str_contains($nameLow, 'f_alm_pfc') ||
                    str_contains($nameLow, 'efc') ||
                    str_contains($nameLow, 'f_alm_efc');
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
            }),
        );

        $tieneAprobados =
            count($aprobados ?? []) > 0 ||
            count($calidadAprobadosLdmCasting ?? []) > 0 ||
            count($dibujosCasting ?? []) > 0 ||
            count($ayudasCasting ?? []) > 0 ||
            count($almacenPreordenesCasting ?? []) > 0;
        $tieneRechazados =
            count($rechazados ?? []) > 0 ||
            count($rechazadosOtros ?? []) > 0 ||
            count($rechazadosDibujos ?? []) > 0 ||
            count($rechazadosAyudas ?? []) > 0 ||
            count($dibujosRechazadosOrig ?? []) > 0 ||
            count($ayudasRechazadosOrig ?? []) > 0;

        $countVisibleFabricacion = $tieneFabricacion
            ? count($dibujosModelo ?? []) +
            count($ayudasModelo ?? []) +
            count($almacenPreordenesFab ?? [])
            : 0;
        $countVisibleAprobados = $tieneAprobados
            ? count($dibujosCasting ?? []) +
            count($ayudasCasting ?? []) +
            count($calidadAprobadosLdmCasting ?? []) +
            count($almacenPreordenesCasting ?? [])
            : 0;
        $countVisibleRechazados = $tieneRechazados
            ? count($dibujosRechazadosOrig ?? []) +
            count($ayudasRechazadosOrig ?? []) +
            count($rechazadosDibujos ?? []) +
            count($rechazadosAyudas ?? []) +
            count($rechazadosOtros ?? [])
            : 0;

        $count = $countVisibleFabricacion + $countVisibleAprobados + $countVisibleRechazados;

        $hasRechazosRealLocal = count($rechazados) > 0 || $tieneRechazados;
        $esReproceso = (bool) preg_match('/_R\d+$/i', $targetReg->ot);
        $showControlCard =
            $estado === 'activa' &&
            !$isFinalized &&
            (!$isCalidadAlerted ||
                $hasRechazosRealLocal ||
                ($esReproceso && count($clasesFabricacion) > 0));
        $hasFilesOrControl = $count > 0 || $showControlCard;

        // DEBUG MARKER
        echo "<!-- DEBUG OT: {$reg->ot}, estado: {$estado}, isFinalized: " .
            ($isFinalized ? 'true' : 'false') .
            ', isCalidadAlerted: ' .
            ($isCalidadAlerted ? 'true' : 'false') .
            ', showControlCard: ' .
            ($showControlCard ? 'true' : 'false') .
            ' -->';

        $estadoConfig = \App\Services\FundicionStateService::resolverEstadoOT(
            $reg,
            $targetReg,
            $aprobados,
            $esReproceso,
        );

        $fsmState = $estadoConfig['fsmState'];
        $icon = $estadoConfig['icon'];
        $label = $estadoConfig['label'];
        $tooltip = $estadoConfig['tooltip'];
        $borderColor = $estadoConfig['borderColor'];
        $bgColor = $estadoConfig['bgColor'];
        $textColor = $estadoConfig['textColor'];


        $pendingChanges = is_string($reg->pending_almacen_changes)
            ? json_decode($reg->pending_almacen_changes, true)
            : $reg->pending_almacen_changes ?? [];
        $hasPendingChanges = !empty($pendingChanges);
        if (isset($activeClassesForOt))
            $this->activeClassesForOt = $activeClassesForOt;
        if (isset($allOtNames))
            $this->allOtNames = $allOtNames;
        if (isset($allRelatedOtNames))
            $this->allRelatedOtNames = $allRelatedOtNames;
        if (isset($allowFileCrossOt))
            $this->allowFileCrossOt = $allowFileCrossOt;
        if (isset($almacenPreordenes))
            $this->almacenPreordenes = $almacenPreordenes;
        if (isset($almacenPreordenesCasting))
            $this->almacenPreordenesCasting = $almacenPreordenesCasting;
        if (isset($almacenPreordenesFab))
            $this->almacenPreordenesFab = $almacenPreordenesFab;
        if (isset($almacenRootScan))
            $this->almacenRootScan = $almacenRootScan;
        if (isset($aprobados))
            $this->aprobados = $aprobados;
        if (isset($aprobadosNorm))
            $this->aprobadosNorm = $aprobadosNorm;
        if (isset($aprobadosRaw))
            $this->aprobadosRaw = $aprobadosRaw;
        if (isset($archivo))
            $this->archivo = $archivo;
        if (isset($archivos))
            $this->archivos = $archivos;
        if (isset($archivosAprobados))
            $this->archivosAprobados = $archivosAprobados;
        if (isset($archivosFabAll))
            $this->archivosFabAll = $archivosFabAll;
        if (isset($archivosRechazados))
            $this->archivosRechazados = $archivosRechazados;
        if (isset($ayudasArchivos))
            $this->ayudasArchivos = $ayudasArchivos;
        if (isset($ayudasBaseNames))
            $this->ayudasBaseNames = $ayudasBaseNames;
        if (isset($ayudasCasting))
            $this->ayudasCasting = $ayudasCasting;
        if (isset($ayudasDir))
            $this->ayudasDir = $ayudasDir;
        if (isset($ayudasGlobalesBase))
            $this->ayudasGlobalesBase = $ayudasGlobalesBase;
        if (isset($ayudasModelo))
            $this->ayudasModelo = $ayudasModelo;
        if (isset($ayudasRechazadosOrig))
            $this->ayudasRechazadosOrig = $ayudasRechazadosOrig;
        if (isset($baseLow))
            $this->baseLow = $baseLow;
        if (isset($baseLower))
            $this->baseLower = $baseLower;
        if (isset($baseName))
            $this->baseName = $baseName;
        if (isset($baseNames))
            $this->baseNames = $baseNames;
        if (isset($baseNamesRechAyudas))
            $this->baseNamesRechAyudas = $baseNamesRechAyudas;
        if (isset($baseNamesRechOtros))
            $this->baseNamesRechOtros = $baseNamesRechOtros;
        if (isset($baseOt))
            $this->baseOt = $baseOt;
        if (isset($baseOtName))
            $this->baseOtName = $baseOtName;
        if (isset($baseOtOfReg))
            $this->baseOtOfReg = $baseOtOfReg;
        if (isset($bgColor))
            $this->bgColor = $bgColor;
        if (isset($borderColor))
            $this->borderColor = $borderColor;
        if (isset($calidadAlertaEnviada))
            $this->calidadAlertaEnviada = $calidadAlertaEnviada;
        if (isset($calidadAprobadosLdm))
            $this->calidadAprobadosLdm = $calidadAprobadosLdm;
        if (isset($calidadAprobadosLdmCasting))
            $this->calidadAprobadosLdmCasting = $calidadAprobadosLdmCasting;
        if (isset($calidadDir))
            $this->calidadDir = $calidadDir;
        if (isset($candidateDirs))
            $this->candidateDirs = $candidateDirs;
        if (isset($castingEmailSent))
            $this->castingEmailSent = $castingEmailSent;
        if (isset($claseVariants))
            $this->claseVariants = $claseVariants;
        if (isset($clasesBaseList))
            $this->clasesBaseList = $clasesBaseList;
        if (isset($clasesDb))
            $this->clasesDb = $clasesDb;
        if (isset($clasesFabricacion))
            $this->clasesFabricacion = $clasesFabricacion;
        if (isset($clasesFabricacionHeader))
            $this->clasesFabricacionHeader = $clasesFabricacionHeader;
        if (isset($clasesRechazadas))
            $this->clasesRechazadas = $clasesRechazadas;
        if (isset($classesInCurrentOtRaw))
            $this->classesInCurrentOtRaw = $classesInCurrentOtRaw;
        if (isset($classesRaw))
            $this->classesRaw = $classesRaw;
        if (isset($confSource))
            $this->confSource = $confSource;
        if (isset($configs))
            $this->configs = $configs;
        if (isset($count))
            $this->count = $count;
        if (isset($countAprobados))
            $this->countAprobados = $countAprobados;
        if (isset($countAyudas))
            $this->countAyudas = $countAyudas;
        if (isset($countDibujos))
            $this->countDibujos = $countDibujos;
        if (isset($countOtros))
            $this->countOtros = $countOtros;
        if (isset($countRechazados))
            $this->countRechazados = $countRechazados;
        if (isset($countVisibleAprobados))
            $this->countVisibleAprobados = $countVisibleAprobados;
        if (isset($countVisibleFabricacion))
            $this->countVisibleFabricacion = $countVisibleFabricacion;
        if (isset($countVisibleRechazados))
            $this->countVisibleRechazados = $countVisibleRechazados;
        if (isset($cubiertaFab))
            $this->cubiertaFab = $cubiertaFab;
        if (isset($dibujoBaseNames))
            $this->dibujoBaseNames = $dibujoBaseNames;
        if (isset($dibujosCasting))
            $this->dibujosCasting = $dibujosCasting;
        if (isset($dibujosModelo))
            $this->dibujosModelo = $dibujosModelo;
        if (isset($dibujosRechazadosOrig))
            $this->dibujosRechazadosOrig = $dibujosRechazadosOrig;
        if (isset($esReproceso))
            $this->esReproceso = $esReproceso;
        if (isset($estadoConfig))
            $this->estadoConfig = $estadoConfig;
        if (isset($existId))
            $this->existId = $existId;
        if (isset($existSuffix))
            $this->existSuffix = $existSuffix;
        if (isset($extractedClass))
            $this->extractedClass = $extractedClass;
        if (isset($extractedFab))
            $this->extractedFab = $extractedFab;
        if (isset($filas))
            $this->filas = $filas;
        if (isset($filasFab))
            $this->filasFab = $filasFab;
        if (isset($fileHistory))
            $this->fileHistory = $fileHistory;
        if (isset($fileOt))
            $this->fileOt = $fileOt;
        if (isset($files))
            $this->files = $files;
        if (isset($filteredOtros))
            $this->filteredOtros = $filteredOtros;
        if (isset($foundClass))
            $this->foundClass = $foundClass;
        if (isset($fsmState))
            $this->fsmState = $fsmState;
        if (isset($hasAprobados))
            $this->hasAprobados = $hasAprobados;
        if (isset($hasFilesOrControl))
            $this->hasFilesOrControl = $hasFilesOrControl;
        if (isset($hasInactiveClass))
            $this->hasInactiveClass = $hasInactiveClass;
        if (isset($hasKnownClass))
            $this->hasKnownClass = $hasKnownClass;
        if (isset($hasPendingChanges))
            $this->hasPendingChanges = $hasPendingChanges;
        if (isset($hasPreorden))
            $this->hasPreorden = $hasPreorden;
        if (isset($hasRechazosRealLocal))
            $this->hasRechazosRealLocal = $hasRechazosRealLocal;
        if (isset($hasVerdictosPendientes))
            $this->hasVerdictosPendientes = $hasVerdictosPendientes;
        if (isset($hayRechazadosSinPreorden))
            $this->hayRechazadosSinPreorden = $hayRechazadosSinPreorden;
        if (isset($icon))
            $this->icon = $icon;
        if (isset($isCalidadAlerted))
            $this->isCalidadAlerted = $isCalidadAlerted;
        if (isset($isCalidadAlertedLocal))
            $this->isCalidadAlertedLocal = $isCalidadAlertedLocal;
        if (isset($isDwg))
            $this->isDwg = $isDwg;
        if (isset($isFinalized))
            $this->isFinalized = $isFinalized;
        if (isset($isImage))
            $this->isImage = $isImage;
        if (isset($isImg))
            $this->isImg = $isImg;
        if (isset($isMixedProcess))
            $this->isMixedProcess = $isMixedProcess;
        if (isset($isNonDrawing))
            $this->isNonDrawing = $isNonDrawing;
        if (isset($isNonDrawingFile))
            $this->isNonDrawingFile = $isNonDrawingFile;
        if (isset($isPdf))
            $this->isPdf = $isPdf;
        if (isset($isPreorden))
            $this->isPreorden = $isPreorden;
        if (isset($isRechazado))
            $this->isRechazado = $isRechazado;
        if (isset($isReproceso))
            $this->isReproceso = $isReproceso;
        if (isset($isReprocesoOT))
            $this->isReprocesoOT = $isReprocesoOT;
        if (isset($knownClasses))
            $this->knownClasses = $knownClasses;
        if (isset($label))
            $this->label = $label;
        if (isset($latestLiberacionesByClass))
            $this->latestLiberacionesByClass = $latestLiberacionesByClass;
        if (isset($latestRechazo))
            $this->latestRechazo = $latestRechazo;
        if (isset($latestReproceso))
            $this->latestReproceso = $latestReproceso;
        if (isset($ldmFiles))
            $this->ldmFiles = $ldmFiles;
        if (isset($legacyClaseAyDir))
            $this->legacyClaseAyDir = $legacyClaseAyDir;
        if (isset($libOt))
            $this->libOt = $libOt;
        if (isset($liberacionesAll))
            $this->liberacionesAll = $liberacionesAll;
        if (isset($liberacionesPath))
            $this->liberacionesPath = $liberacionesPath;
        if (isset($liberacionesReg))
            $this->liberacionesReg = $liberacionesReg;
        if (isset($matched))
            $this->matched = $matched;
        if (isset($matchesActive))
            $this->matchesActive = $matchesActive;
        if (isset($matchesRejected))
            $this->matchesRejected = $matchesRejected;
        if (isset($nameLow))
            $this->nameLow = $nameLow;
        if (isset($newAyDir))
            $this->newAyDir = $newAyDir;
        if (isset($newDirs))
            $this->newDirs = $newDirs;
        if (isset($normAyudasBaseNames))
            $this->normAyudasBaseNames = $normAyudasBaseNames;
        if (isset($normBase))
            $this->normBase = $normBase;
        if (isset($normBaseNames))
            $this->normBaseNames = $normBaseNames;
        if (isset($origin))
            $this->origin = $origin;
        if (isset($otId))
            $this->otId = $otId;
        if (isset($otLow))
            $this->otLow = $otLow;
        if (isset($otName))
            $this->otName = $otName;
        if (isset($otNameLow))
            $this->otNameLow = $otNameLow;
        if (isset($otNameSanitized))
            $this->otNameSanitized = $otNameSanitized;
        if (isset($otParaRechazados))
            $this->otParaRechazados = $otParaRechazados;
        if (isset($otSanitizada))
            $this->otSanitizada = $otSanitizada;
        if (isset($otrosArchivos))
            $this->otrosArchivos = $otrosArchivos;
        if (isset($owner))
            $this->owner = $owner;
        if (isset($p))
            $this->p = $p;
        if (isset($parentOtSanitized))
            $this->parentOtSanitized = $parentOtSanitized;
        if (isset($parsedClasses))
            $this->parsedClasses = $parsedClasses;
        if (isset($parsedCurrent))
            $this->parsedCurrent = $parsedCurrent;
        if (isset($parsedPrev))
            $this->parsedPrev = $parsedPrev;
        if (isset($parts))
            $this->parts = $parts;
        if (isset($pendingChanges))
            $this->pendingChanges = $pendingChanges;
        if (isset($po))
            $this->po = $po;
        if (isset($preOrdenesCandidates))
            $this->preOrdenesCandidates = $preOrdenesCandidates;
        if (isset($preOrdenesEnviadasFab))
            $this->preOrdenesEnviadasFab = $preOrdenesEnviadasFab;
        if (isset($prefix))
            $this->prefix = $prefix;
        if (isset($preordenesSentClassesFab))
            $this->preordenesSentClassesFab = $preordenesSentClassesFab;
        if (isset($prevOt))
            $this->prevOt = $prevOt;
        if (isset($rArchivo))
            $this->rArchivo = $rArchivo;
        if (isset($rechazados))
            $this->rechazados = $rechazados;
        if (isset($rechazadosAyudas))
            $this->rechazadosAyudas = $rechazadosAyudas;
        if (isset($rechazadosAyudasFilesystem))
            $this->rechazadosAyudasFilesystem = $rechazadosAyudasFilesystem;
        if (isset($rechazadosDibujos))
            $this->rechazadosDibujos = $rechazadosDibujos;
        if (isset($rechazadosNorm))
            $this->rechazadosNorm = $rechazadosNorm;
        if (isset($rechazadosNormFab))
            $this->rechazadosNormFab = $rechazadosNormFab;
        if (isset($rechazadosNormLocal))
            $this->rechazadosNormLocal = $rechazadosNormLocal;
        if (isset($rechazadosOtros))
            $this->rechazadosOtros = $rechazadosOtros;
        if (isset($rechazadosOtrosFilesystem))
            $this->rechazadosOtrosFilesystem = $rechazadosOtrosFilesystem;
        if (isset($rechazadosRaw))
            $this->rechazadosRaw = $rechazadosRaw;
        if (isset($rechazadosSinPreorden))
            $this->rechazadosSinPreorden = $rechazadosSinPreorden;
        if (isset($rejectedPrevRaw))
            $this->rejectedPrevRaw = $rejectedPrevRaw;
        if (isset($relArchivos))
            $this->relArchivos = $relArchivos;
        if (isset($relatedRecords))
            $this->relatedRecords = $relatedRecords;
        if (isset($relativePathWithPrefix))
            $this->relativePathWithPrefix = $relativePathWithPrefix;
        if (isset($reprocesoTienePreOrden))
            $this->reprocesoTienePreOrden = $reprocesoTienePreOrden;
        if (isset($scanDirs))
            $this->scanDirs = $scanDirs;
        if (isset($scarFiles))
            $this->scarFiles = $scarFiles;
        if (isset($scarPattern))
            $this->scarPattern = $scarPattern;
        if (isset($scarPattern2))
            $this->scarPattern2 = $scarPattern2;
        if (isset($shouldReplace))
            $this->shouldReplace = $shouldReplace;
        if (isset($showControlCard))
            $this->showControlCard = $showControlCard;
        if (isset($status))
            $this->status = $status;
        if (isset($subDirs))
            $this->subDirs = $subDirs;
        if (isset($suffixNum))
            $this->suffixNum = $suffixNum;
        if (isset($targetDir))
            $this->targetDir = $targetDir;
        if (isset($targetReg))
            $this->targetReg = $targetReg;
        if (isset($textColor))
            $this->textColor = $textColor;
        if (isset($tieneAprobados))
            $this->tieneAprobados = $tieneAprobados;
        if (isset($tieneArchivosFabricacion))
            $this->tieneArchivosFabricacion = $tieneArchivosFabricacion;
        if (isset($tieneFabricacion))
            $this->tieneFabricacion = $tieneFabricacion;
        if (isset($tieneRechazados))
            $this->tieneRechazados = $tieneRechazados;
        if (isset($tipo))
            $this->tipo = $tipo;
        if (isset($tooltip))
            $this->tooltip = $tooltip;
        if (isset($userPerfil))
            $this->userPerfil = $userPerfil;

    }
}
