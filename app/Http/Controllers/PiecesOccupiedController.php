<?php

namespace App\Http\Controllers;

use App\Models\Clase;
use App\Models\Maquinas;
use App\Models\Metas;
use App\Models\Orden_trabajo;
use App\Models\Pieza;
use App\Models\SystemLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PiecesOccupiedController extends Controller
{
    /**
     * Catálogo completo de procesos y sus modelos de piezas y proceso padre.
     */
    protected static array $processConfig = [
        'Cepillado' => [
            'piece_model' => \App\Models\Pza_cepillado::class,
            'parent_model' => \App\Models\Cepillado::class,
            'label' => 'Cepillado',
            'is_initial' => true,
        ],
        'Desbaste Exterior' => [
            'piece_model' => \App\Models\Desbaste_pza::class,
            'parent_model' => \App\Models\DesbasteExterior::class,
            'label' => 'Desbaste Exterior',
            'is_initial' => false,
        ],
        'Revision Laterales' => [
            'piece_model' => \App\Models\RevLaterales_pza::class,
            'parent_model' => \App\Models\RevLaterales::class,
            'label' => 'Revisión Laterales',
            'is_initial' => false,
        ],
        'Primera Operacion' => [
            'piece_model' => \App\Models\PrimeraOpeSoldadura_pza::class,
            'parent_model' => \App\Models\PrimeraOpeSoldadura::class,
            'label' => 'Primera Operación Soldadura',
            'is_initial' => false,
        ],
        'Barreno Maniobra' => [
            'piece_model' => \App\Models\BarrenoManiobra_pza::class,
            'parent_model' => \App\Models\BarrenoManiobra::class,
            'label' => 'Barreno Maniobra',
            'is_initial' => false,
        ],
        'Segunda Operacion' => [
            'piece_model' => \App\Models\SegundaOpeSoldadura_pza::class,
            'parent_model' => \App\Models\SegundaOpeSoldadura::class,
            'label' => 'Segunda Operación Soldadura',
            'is_initial' => false,
        ],
        'Rectificado' => [
            'piece_model' => \App\Models\Rectificado_pza::class,
            'parent_model' => \App\Models\Rectificado::class,
            'label' => 'Rectificado',
            'is_initial' => false,
        ],
        'Asentado' => [
            'piece_model' => \App\Models\Asentado_pza::class,
            'parent_model' => \App\Models\Asentado::class,
            'label' => 'Asentado',
            'is_initial' => false,
        ],
        'Calificado' => [
            'piece_model' => \App\Models\revCalificado_pza::class,
            'parent_model' => \App\Models\revCalificado::class,
            'label' => 'Calificado',
            'is_initial' => false,
        ],
        'Acabado Bombillo' => [
            'piece_model' => \App\Models\AcabadoBombilo_pza::class,
            'parent_model' => \App\Models\AcabadoBombilo::class,
            'label' => 'Acabado Bombillo',
            'is_initial' => false,
        ],
        'Acabado Molde' => [
            'piece_model' => \App\Models\AcabadoMolde_pza::class,
            'parent_model' => \App\Models\AcabadoMolde::class,
            'label' => 'Acabado Molde',
            'is_initial' => false,
        ],
        'Barreno Profundidad' => [
            'piece_model' => \App\Models\BarrenoProfundidad_pza::class,
            'parent_model' => \App\Models\BarrenoProfundidad::class,
            'label' => 'Barreno Profundidad',
            'is_initial' => false,
        ],
        'Cavidades' => [
            'piece_model' => \App\Models\Cavidades_pza::class,
            'parent_model' => \App\Models\Cavidades::class,
            'label' => 'Cavidades',
            'is_initial' => false,
        ],
        'Copiado' => [
            'piece_model' => \App\Models\Copiado_pza::class,
            'parent_model' => \App\Models\Copiado::class,
            'label' => 'Copiado',
            'is_initial' => false,
        ],
        'Off Set' => [
            'piece_model' => \App\Models\OffSet_pza::class,
            'parent_model' => \App\Models\OffSet::class,
            'label' => 'Off Set',
            'is_initial' => false,
        ],
        'Palomas' => [
            'piece_model' => \App\Models\Palomas_pza::class,
            'parent_model' => \App\Models\Palomas::class,
            'label' => 'Palomas',
            'is_initial' => false,
        ],
        'Rebajes' => [
            'piece_model' => \App\Models\Rebajes_pza::class,
            'parent_model' => \App\Models\Rebajes::class,
            'label' => 'Rebajes',
            'is_initial' => false,
        ],
        'Operacion Equipo' => [
            'piece_model' => \App\Models\PySOpeSoldadura_pza::class,
            'parent_model' => \App\Models\PySOpeSoldadura::class,
            'label' => 'Operación Equipo',
            'is_initial' => false,
        ],
        'Embudo CM' => [
            'piece_model' => \App\Models\EmbudoCM_pza::class,
            'parent_model' => \App\Models\EmbudoCM::class,
            'label' => 'Embudo CM',
            'is_initial' => false,
        ],
        'Soldadura' => [
            'piece_model' => \App\Models\Soldadura_pza::class,
            'parent_model' => \App\Models\Soldadura::class,
            'label' => 'Soldadura',
            'is_initial' => false,
        ],
        'Soldadura PTA' => [
            'piece_model' => \App\Models\SoldaduraPTA_pza::class,
            'parent_model' => \App\Models\SoldaduraPTA::class,
            'label' => 'Soldadura PTA',
            'is_initial' => false,
        ],
        'Primera Operacion Cabeza Soplo' => [
            'piece_model' => \App\Models\PrimeraOperacionCabezaSoplo_pza::class,
            'parent_model' => \App\Models\PrimeraOperacionCabezaSoplo::class,
            'label' => 'Primera Op. Cabeza Soplo',
            'is_initial' => false,
        ],
        'Segunda Operacion Cabeza Soplo' => [
            'piece_model' => \App\Models\SegundaOperacionCabezaSoplo_pza::class,
            'parent_model' => \App\Models\SegundaOperacionCabezaSoplo::class,
            'label' => 'Segunda Op. Cabeza Soplo',
            'is_initial' => false,
        ],
        'Candado Obturador' => [
            'piece_model' => \App\Models\CandadoObturador_pza::class,
            'parent_model' => \App\Models\CandadoObturador::class,
            'label' => 'Candado Obturador',
            'is_initial' => true,
        ],
    ];

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Muestra la vista principal de piezas ocupadas.
     */
    public function show()
    {
        // Obtener OTs con piezas ocupadas en cualquier proceso para asegurar su presencia en el filtro
        $occupiedOtIds = [];
        $activeMetas = Metas::query()->select(['id', 'id_ot'])->get()->keyBy('id');

        foreach (self::$processConfig as $cfg) {
            $pieceModel = $cfg['piece_model'];
            $parentModel = $cfg['parent_model'];
            if (class_exists($pieceModel)) {
                $occupiedPieces = $pieceModel::query()->where('estado', '=', 1, 'and')->get(['id_meta', 'id_proceso']);
                foreach ($occupiedPieces as $op) {
                    if ($op->id_meta && isset($activeMetas[$op->id_meta])) {
                        $occupiedOtIds[] = $activeMetas[$op->id_meta]->id_ot;
                    } elseif ($parentModel && $op->id_proceso) {
                        $parent = $parentModel::query()->find($op->id_proceso);
                        if ($parent && isset($parent->id_ot)) {
                            $occupiedOtIds[] = $parent->id_ot;
                        } elseif ($parent && isset($parent->id_proceso)) {
                            $parts = explode('_', $parent->id_proceso);
                            $otCandidate = end($parts);
                            if (is_numeric($otCandidate)) {
                                $occupiedOtIds[] = (int) $otCandidate;
                            }
                        }
                    }
                }
            }
        }
        $occupiedOtIds = array_unique(array_filter($occupiedOtIds));
        $recentOtIds = Orden_trabajo::query()->orderBy('id', 'desc')->limit(100)->pluck('id')->toArray();
        $targetOtIds = array_unique(array_merge($occupiedOtIds, $recentOtIds));

        // Cargar OTs para el selector inicial (con moldura)
        $workOrders = Orden_trabajo::query()
            ->select(['id', 'id_moldura', 'created_at'])
            ->with(['moldura:id,nombre'])
            ->whereIn('id', $targetOtIds, 'and', false)
            ->orderBy('id', 'desc')
            ->get();

        $processesList = array_map(function ($key, $cfg) {
            return [
                'key' => $key,
                'label' => $cfg['label'],
            ];
        }, array_keys(self::$processConfig), self::$processConfig);

        // Catálogo de clases reales registradas en BD (normalizadas sin prefijos ej. '3 - BOMBILLO' -> 'Bombillo')
        $dbClasses = Clase::query()->distinct()->whereNotNull('nombre', 'and')->pluck('nombre')->toArray();
        $seen = [];
        $classesList = [];
        foreach ($dbClasses as $dbClass) {
            $clean = trim(preg_replace('/^\d+\s*-\s*/', '', (string) $dbClass));
            $lower = mb_strtolower($clean, 'UTF-8');
            if ($clean !== '' && !isset($seen[$lower])) {
                $seen[$lower] = true;
                $classesList[] = mb_convert_case($clean, MB_CASE_TITLE, 'UTF-8');
            }
        }
        natcasesort($classesList);
        $classesList = array_values($classesList);

        // Operadores registrados en el sistema
        $operatorsList = User::query()
            ->select(['matricula', 'nombre', 'a_paterno'])
            ->whereNotNull('nombre')
            ->orderBy('nombre', 'asc')
            ->get()
            ->map(function ($u) {
                return [
                    'matricula' => $u->matricula,
                    'nombre' => trim("{$u->nombre} {$u->a_paterno}"),
                ];
            });

        return view('pieces_views.piecesOccupied', compact('workOrders', 'processesList', 'classesList', 'operatorsList'));
    }

    /**
     * Obtiene las clases disponibles para una OT dada.
     */
    public function getClassesByOt(Request $request)
    {
        $otId = $request->query('ot');
        if (!$otId) {
            // Si no se especifica OT, devolver las clases reales normalizadas
            $dbClasses = Clase::query()->distinct()->whereNotNull('nombre', 'and')->pluck('nombre')->toArray();
            $seen = [];
            $classes = [];
            foreach ($dbClasses as $dbClass) {
                $clean = trim(preg_replace('/^\d+\s*-\s*/', '', (string) $dbClass));
                $lower = mb_strtolower($clean, 'UTF-8');
                if ($clean !== '' && !isset($seen[$lower])) {
                    $seen[$lower] = true;
                    $titleName = mb_convert_case($clean, MB_CASE_TITLE, 'UTF-8');
                    $classes[] = [
                        'id' => $clean,
                        'nombre' => $titleName,
                        'piezas' => 0,
                    ];
                }
            }
            usort($classes, fn($a, $b) => strnatcasecmp($a['nombre'], $b['nombre']));
            return response()->json(['classes' => $classes]);
        }

        $classes = Clase::query()
            ->select(['id', 'id_ot', 'nombre', 'piezas', 'finalizada'])
            ->where('id_ot', '=', $otId, 'and')
            ->orderBy('nombre')
            ->get();

        return response()->json(['classes' => $classes]);
    }

    /**
     * Devuelve los números de pieza disponibles para una combinación de OT, Clase y Proceso.
     * Engloba mitades Macho y Hembra en un solo número identificador unificado.
     */
    public function getPiecesByContext(Request $request)
    {
        $targetOt = $request->input('ot');
        $targetClase = $request->input('clase_id');
        $targetProcess = $request->input('proceso');

        if (empty($targetOt) || empty($targetClase) || empty($targetProcess)) {
            return response()->json([
                'success' => true,
                'pieces' => [],
            ]);
        }

        // Resolver IDs de clase para la OT
        $claseIds = [];
        $targetClaseObj = is_numeric($targetClase) ? Clase::find((int) $targetClase) : null;
        $cleanTargetName = trim(preg_replace('/^\d+\s*-\s*/', '', mb_strtolower($targetClaseObj ? $targetClaseObj->nombre : (string) $targetClase, 'UTF-8')));

        $allOtClasses = Clase::query()->where('id_ot', '=', $targetOt, 'and')->get();
        foreach ($allOtClasses as $c) {
            $cleanC = trim(preg_replace('/^\d+\s*-\s*/', '', mb_strtolower($c->nombre, 'UTF-8')));
            if ($cleanC === $cleanTargetName || (string) $c->id === (string) $targetClase) {
                $claseIds[] = $c->id;
            }
        }
        if (empty($claseIds) && is_numeric($targetClase)) {
            $claseIds[] = (int) $targetClase;
        }

        $rawPieceStrings = [];

        // 1. Consultar tabla 'piezas'
        $piezasQuery = Pieza::query()
            ->where('id_ot', '=', $targetOt, 'and')
            ->where('proceso', '=', $targetProcess, 'and');
        if (!empty($claseIds)) {
            $piezasQuery->whereIn('id_clase', $claseIds, 'and', false);
        }
        $rawPieceStrings = array_merge($rawPieceStrings, $piezasQuery->pluck('n_pieza')->toArray());

        // 2. Consultar modelo del proceso si existe
        if (isset(self::$processConfig[$targetProcess])) {
            $cfg = self::$processConfig[$targetProcess];
            $pieceModel = $cfg['piece_model'];
            $parentModel = $cfg['parent_model'];

            if (class_exists($pieceModel)) {
                $metaQuery = Metas::query()->where('id_ot', '=', $targetOt, 'and');
                if (!empty($claseIds)) {
                    $metaQuery->whereIn('id_clase', $claseIds, 'and', false);
                }
                $metaIds = $metaQuery->pluck('id')->toArray();

                $procPiecesQuery = $pieceModel::query();
                if (!empty($metaIds)) {
                    $procPiecesQuery->whereIn('id_meta', $metaIds, 'and', false);
                } elseif (class_exists($parentModel)) {
                    $parentIds = $parentModel::query()->where('id_ot', '=', $targetOt, 'and')->pluck('id')->toArray();
                    if (!empty($parentIds)) {
                        $procPiecesQuery->whereIn('id_proceso', $parentIds, 'and', false);
                    }
                }

                $procPieces = $procPiecesQuery->get(['n_pieza', 'n_juego']);
                $rawPieceStrings = array_merge(
                    $rawPieceStrings,
                    $procPieces->pluck('n_pieza')->filter()->toArray(),
                    $procPieces->pluck('n_juego')->filter()->toArray()
                );
            }
        }

        // 3. Extraer únicamente los números base (englobando macho y hembra en uno solo)
        $numbersMap = [];
        foreach ($rawPieceStrings as $str) {
            $str = trim((string) $str);
            if (preg_match('/\d+/', $str, $matches)) {
                $num = (int) $matches[0];
                if ($num > 0) {
                    $numbersMap[$num] = true;
                }
            }
        }

        $sortedNumbers = array_keys($numbersMap);
        sort($sortedNumbers, SORT_NUMERIC);

        $piecesList = array_map(function ($num) {
            return [
                'id' => (string) $num,
                'label' => "Pieza #{$num}",
            ];
        }, $sortedNumbers);

        return response()->json([
            'success' => true,
            'pieces' => $piecesList,
        ]);
    }

    /**
     * Consulta AJAX de piezas ocupadas filtradas por OT, Clase y Proceso.
     * Detecta piezas retenidas (estado 1), registros desvinculados sin máquina y juegos incompletos (mitad faltante).
     */
    public function getOccupiedPieces(Request $request)
    {
        $targetOt = $request->input('ot');
        $targetClaseId = $request->input('clase_id');
        $targetProcess = $request->input('proceso');
        $targetPieza = trim((string) $request->input('pieza', ''));
        $targetOperador = trim((string) $request->input('operador', ''));

        $processesToScan = self::$processConfig;
        if (!empty($targetProcess) && isset(self::$processConfig[$targetProcess])) {
            $processesToScan = [$targetProcess => self::$processConfig[$targetProcess]];
        }

        $occupiedPieces = [];
        $totalOrphans = 0;
        $totalIncompletes = 0;
        $affectedOts = [];

        // Pre-cargar usuarios para mapear matricula -> nombre de operador sin N+1
        $usersMap = User::query()->select(['matricula', 'nombre', 'a_paterno', 'a_materno'])->get()->keyBy('matricula');
        // Pre-cargar máquinas activas actualmente
        $activeMachineMetaIds = Maquinas::query()->pluck('id_meta')->filter()->toArray();

        // Normalización de Clase para comparación tolerante
        $targetClaseObj = (!empty($targetClaseId) && is_numeric($targetClaseId)) ? Clase::find((int) $targetClaseId) : null;
        $targetClaseNombre = $targetClaseObj ? $targetClaseObj->nombre : (string) $targetClaseId;
        $cleanTargetClase = trim(preg_replace('/^\d+\s*-\s*/', '', mb_strtolower($targetClaseNombre, 'UTF-8')));

        $matchesTargetClass = function ($claseId, $claseNombre) use ($targetClaseId, $targetClaseObj, $targetClaseNombre, $cleanTargetClase) {
            if (empty($targetClaseId)) {
                return true;
            }
            if ($claseId && (string) $claseId === (string) $targetClaseId) {
                return true;
            }
            if ($targetClaseObj && $claseId && (int) $claseId === (int) $targetClaseObj->id) {
                return true;
            }
            $cleanNombre = trim(preg_replace('/^\d+\s*-\s*/', '', mb_strtolower((string) $claseNombre, 'UTF-8')));
            if ($cleanNombre !== '' && $cleanNombre === $cleanTargetClase) {
                return true;
            }
            if (strcasecmp((string) $claseNombre, (string) $targetClaseNombre) === 0) {
                return true;
            }
            return false;
        };

        // Procesos que manejan juegos con dos mitades (Hembra/Macho)
        $twoHalfProcesses = [
            'Cepillado',
            'Desbaste Exterior',
            'Revision Laterales',
            'Primera Operacion',
            'Segunda Operacion',
            'Calificado',
            'Acabado Bombillo',
            'Acabado Molde',
            'Soldadura PTA',
        ];

        foreach ($processesToScan as $procKey => $cfg) {
            $pieceModel = $cfg['piece_model'];
            $parentModel = $cfg['parent_model'];

            if (!class_exists($pieceModel)) {
                continue;
            }

            // 1. Buscar piezas en estado 1 (Ocupada / Retenida)
            $query = $pieceModel::query()->where('estado', '=', 1, 'and');
            $rawPieces = $query->get();

            // 2. Para procesos con mitades, buscar además juegos incompletos (donde solo una mitad fue registrada o maquinada)
            $isTwoHalf = in_array($procKey, $twoHalfProcesses);
            $rawIncompletePieces = collect();

            if ($isTwoHalf) {
                $allProcPieces = $pieceModel::query()->whereIn('estado', [1, 2], 'and')->get();
                if ($allProcPieces->isNotEmpty()) {
                    $byGame = $allProcPieces->groupBy(function ($item) {
                        return ($item->id_proceso ?? '0') . '_' . ($item->n_juego ?? $item->n_pieza ?? '0');
                    });

                    foreach ($byGame as $gameKey => $group) {
                        if ($group->count() === 1) {
                            $singlePiece = $group->first();
                            $pName = (string) ($singlePiece->n_pieza ?? '');
                            $lastChar = strtoupper(substr($pName, -1));
                            if ($lastChar === 'H' || $lastChar === 'M') {
                                $rawIncompletePieces->push($singlePiece);
                            }
                        }
                    }
                }
            }

            // Combinar piezas ocupadas e incompletas
            $combinedRaw = $rawPieces->concat($rawIncompletePieces)->unique('id');
            if ($combinedRaw->isEmpty()) {
                continue;
            }

            // Extraer metas asociadas
            $metaIds = $combinedRaw->pluck('id_meta')->filter()->unique()->toArray();
            $metasMap = empty($metaIds) ? collect() : Metas::query()->whereIn('id', $metaIds, 'and', false)->get()->keyBy('id');

            // Extraer procesos padres asociados
            $parentProcessIds = $combinedRaw->pluck('id_proceso')->filter()->unique()->toArray();
            $parentsMap = empty($parentProcessIds) ? collect() : $parentModel::query()->whereIn('id', $parentProcessIds, 'and', false)->get()->keyBy('id');

            foreach ($combinedRaw as $piece) {
                $meta = isset($piece->id_meta) ? $metasMap->get($piece->id_meta) : null;
                $parent = isset($piece->id_proceso) ? $parentsMap->get($piece->id_proceso) : null;

                // Determinar OT
                $ot = $meta->id_ot ?? $parent->id_ot ?? null;
                if (!$ot && $parent && isset($parent->id_proceso)) {
                    $parts = explode('_', $parent->id_proceso);
                    $ot = end($parts);
                }

                // Filtrar por OT si fue especificada
                if (!empty($targetOt) && (string) $ot !== (string) $targetOt) {
                    continue;
                }

                // Determinar Clase
                $claseId = $meta->id_clase ?? null;
                $claseNombre = 'N/A';

                if ($claseId) {
                    $claseObj = Clase::find($claseId);
                    $claseNombre = $claseObj ? $claseObj->nombre : "Clase #{$claseId}";
                } elseif ($parent && isset($parent->id_proceso)) {
                    $parts = explode('_', $parent->id_proceso);
                    if (count($parts) >= 2) {
                        $claseNombre = $parts[1];
                    }
                }

                // Filtrar por Clase si fue especificada
                if (!$matchesTargetClass($claseId, $claseNombre)) {
                    continue;
                }

                // Filtrar por Pieza / Juego si fue especificada (engloba macho y hembra guiadas por el número)
                if (!empty($targetPieza)) {
                    $pNum = trim((string) ($piece->n_pieza ?? ''));
                    $jNum = trim((string) ($piece->n_juego ?? ''));

                    preg_match('/\d+/', $targetPieza, $targetDigits);
                    $targetNum = $targetDigits[0] ?? $targetPieza;

                    preg_match('/\d+/', $pNum, $pDigits);
                    $pNumDigits = $pDigits[0] ?? '';

                    preg_match('/\d+/', $jNum, $jDigits);
                    $jNumDigits = $jDigits[0] ?? '';

                    $matchesPiece = ($pNumDigits !== '' && $pNumDigits === $targetNum) ||
                                    ($jNumDigits !== '' && $jNumDigits === $targetNum) ||
                                    stripos($pNum, $targetPieza) !== false ||
                                    stripos($jNum, $targetPieza) !== false;

                    if (!$matchesPiece) {
                        continue;
                    }
                }

                // Máquina y operador
                $maquina = $meta->maquina ?? 'Sin máquina';
                $operadorMatricula = $meta->id_usuario ?? null;
                $operadorNombre = 'Desconocido';
                if ($operadorMatricula && $user = $usersMap->get($operadorMatricula)) {
                    $operadorNombre = "{$user->nombre} {$user->a_paterno}";
                }

                // Filtrar por Operador si fue especificado
                if (!empty($targetOperador)) {
                    $matchesOp = false;
                    if ($operadorMatricula && (string) $operadorMatricula === (string) $targetOperador) {
                        $matchesOp = true;
                    } elseif ($operadorNombre && stripos($operadorNombre, $targetOperador) !== false) {
                        $matchesOp = true;
                    }
                    if (!$matchesOp) {
                        continue;
                    }
                }

                // Determinar si es incompleta o desvinculada (sin máquina activa)
                $isIncomplete = $rawIncompletePieces->contains('id', $piece->id);
                $isOrphan = false;

                if (!$isIncomplete) {
                    if (!$meta || !in_array($meta->id, $activeMachineMetaIds)) {
                        $isOrphan = true;
                        $totalOrphans++;
                    }
                } else {
                    $totalIncompletes++;
                }

                if ($ot) {
                    $affectedOts[$ot] = true;
                }

                $occupiedPieces[] = [
                    'id' => $piece->id,
                    'proceso_key' => $procKey,
                    'proceso_label' => $cfg['label'],
                    'is_initial' => $cfg['is_initial'],
                    'n_pieza' => $piece->n_pieza ?? $piece->n_juego ?? 'S/N',
                    'n_juego' => $piece->n_juego ?? 'S/N',
                    'estado' => (int) $piece->estado,
                    'id_ot' => $ot ?? 'N/A',
                    'id_clase' => $claseId,
                    'clase_nombre' => $claseNombre,
                    'id_meta' => $piece->id_meta ?? null,
                    'maquina' => $maquina,
                    'operador_matricula' => $operadorMatricula,
                    'operador_nombre' => $operadorNombre,
                    'fecha_meta' => $meta->fecha ?? null,
                    'hora_inicio' => $meta->h_inicio ?? null,
                    'hora_termino' => $meta->h_termino ?? null,
                    'created_at' => $piece->created_at ? $piece->created_at->format('d/m/Y H:i') : 'N/A',
                    'is_orphan' => $isOrphan,
                    'is_incomplete' => $isIncomplete,
                ];
            }
        }

        // 3. Respaldo adicional: Consultar piezas con error 'Incompleto' no liberadas en la tabla 'piezas'
        $incompleteGeneralQuery = Pieza::query()
            ->where('error', 'LIKE', '%Incompleto%')
            ->where('liberacion', '=', 0, 'and');

        if (!empty($targetOt)) {
            $incompleteGeneralQuery->where('id_ot', '=', $targetOt, 'and');
        }
        if (!empty($targetProcess)) {
            $incompleteGeneralQuery->where('proceso', '=', $targetProcess, 'and');
        }
        if (!empty($targetPieza)) {
            preg_match('/\d+/', $targetPieza, $targetDigits);
            $targetNum = $targetDigits[0] ?? $targetPieza;
            $incompleteGeneralQuery->where(function ($q) use ($targetPieza, $targetNum) {
                $q->where('n_pieza', '=', $targetPieza)
                    ->orWhere('n_pieza', 'LIKE', "{$targetNum}%")
                    ->orWhere('n_pieza', 'LIKE', "%{$targetPieza}%");
            });
        }
        if (!empty($targetOperador)) {
            $incompleteGeneralQuery->where('id_operador', '=', $targetOperador, 'and');
        }

        $generalIncompletes = $incompleteGeneralQuery->get();
        foreach ($generalIncompletes as $gp) {
            $claseObj = $gp->id_clase ? Clase::find($gp->id_clase) : null;
            $claseNombre = $claseObj ? $claseObj->nombre : 'N/A';

            if (!$matchesTargetClass($gp->id_clase, $claseNombre)) {
                continue;
            }

            $procKey = $gp->proceso;
            $procCfg = self::$processConfig[$procKey] ?? null;
            $procLabel = $procCfg ? $procCfg['label'] : $procKey;
            $isInitial = $procCfg ? $procCfg['is_initial'] : false;

            // Evitar duplicar si ya la capturamos arriba desde el modelo del proceso
            $alreadyIncluded = collect($occupiedPieces)->contains(function ($item) use ($gp, $procKey) {
                return (string) $item['id_ot'] === (string) $gp->id_ot &&
                    $item['proceso_key'] === $procKey &&
                    ($item['n_pieza'] === $gp->n_pieza || $item['n_juego'] === $gp->n_pieza);
            });

            if (!$alreadyIncluded) {
                $opUser = $gp->id_operador ? $usersMap->get($gp->id_operador) : null;
                $opName = $opUser ? "{$opUser->nombre} {$opUser->a_paterno}" : ($gp->id_operador ?: 'Desconocido');

                if ($gp->id_ot) {
                    $affectedOts[$gp->id_ot] = true;
                }
                $totalIncompletes++;

                $occupiedPieces[] = [
                    'id' => $gp->id,
                    'proceso_key' => $procKey,
                    'proceso_label' => $procLabel,
                    'is_initial' => $isInitial,
                    'n_pieza' => $gp->n_pieza,
                    'n_juego' => rtrim($gp->n_pieza, 'HM') . 'J',
                    'estado' => 1,
                    'id_ot' => $gp->id_ot,
                    'id_clase' => $gp->id_clase,
                    'clase_nombre' => $claseNombre,
                    'id_meta' => null,
                    'maquina' => $gp->maquina ?: 'Sin máquina',
                    'operador_matricula' => $gp->id_operador,
                    'operador_nombre' => $opName,
                    'fecha_meta' => null,
                    'hora_inicio' => null,
                    'hora_termino' => null,
                    'created_at' => $gp->created_at ? $gp->created_at->format('d/m/Y H:i') : 'N/A',
                    'is_orphan' => false,
                    'is_incomplete' => true,
                    'from_general' => true,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'summary' => [
                'total_occupied' => count($occupiedPieces),
                'total_orphans' => $totalOrphans,
                'total_incompletes' => $totalIncompletes,
                'affected_ots' => count($affectedOts),
            ],
            'pieces' => $occupiedPieces,
        ]);
    }

    /**
     * Libera / Desocupa una pieza específica para que vuelva a estar disponible.
     * En procesos iniciales (como Cepillado), elimina el registro huérfano para desbloquear el juego.
     */
    public function freeUp(Request $request)
    {
        $request->validate([
            'proceso' => 'required|string',
            'piece_id' => 'required|integer',
        ]);

        $proceso = $request->input('proceso');
        $pieceId = $request->input('piece_id');

        if (!isset(self::$processConfig[$proceso])) {
            return response()->json(['success' => false, 'message' => 'Proceso no reconocido.'], 400);
        }

        $pieceModel = self::$processConfig[$proceso]['piece_model'];
        $isInitial = self::$processConfig[$proceso]['is_initial'];

        try {
            DB::beginTransaction();

            $piece = $pieceModel::query()->find($pieceId);
            $generalPiece = null;

            if (!$piece) {
                // Si no se encuentra en el modelo del proceso, verificar si es un ID de la tabla general 'piezas'
                $generalPiece = Pieza::query()->find($pieceId);
                if (!$generalPiece) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => 'Pieza no encontrada.'], 404);
                }
            }

            if ($piece) {
                $nPieza = $piece->n_pieza ?? $piece->n_juego ?? "#{$pieceId}";
                $nJuego = $piece->n_juego ?? null;
                $metaId = $piece->id_meta ?? null;
                $meta = $metaId ? Metas::find($metaId) : null;
                $ot = $meta->id_ot ?? 'N/A';
                $parentModel = self::$processConfig[$proceso]['parent_model'] ?? null;
                $parent = ($parentModel && isset($piece->id_proceso)) ? $parentModel::find($piece->id_proceso) : null;

                if ($ot === 'N/A' && $parent) {
                    $ot = $parent->id_ot ?? null;
                    if (!$ot && isset($parent->id_proceso)) {
                        $parts = explode('_', $parent->id_proceso);
                        $ot = end($parts);
                    }
                }

                $claseNombre = null;
                if ($meta && $meta->id_clase) {
                    $claseObj = Clase::find($meta->id_clase);
                    $claseNombre = $claseObj ? $claseObj->nombre : null;
                } elseif ($parent && isset($parent->id_proceso)) {
                    $parts = explode('_', $parent->id_proceso);
                    if (count($parts) >= 2) {
                        $claseNombre = $parts[1];
                    }
                }

                // Si es proceso inicial (Cepillado o Candado) o tiene estado 2 (incompleta registrada):
                // Eliminar el registro desbloquea el juego de inmediato para volver a maquinar
                if ($isInitial || $piece->estado == 2) {
                    $piece->delete();
                    $actionDetail = "Se liberó y eliminó el registro de la pieza '{$nPieza}' en el proceso '{$proceso}'" . ($ot && $ot !== 'N/A' ? " (OT: {$ot})" : '') . " para restablecer la disponibilidad completa del juego.";
                } else {
                    // En otros procesos con estado 1, pasar a 0 para que otro operador pueda tomarlo
                    $piece->estado = 0;
                    $piece->id_meta = null;
                    $piece->save();
                    $actionDetail = "Se cambió el estado de la pieza '{$nPieza}' a 0 (desocupada) en el proceso '{$proceso}'" . ($ot && $ot !== 'N/A' ? " (OT: {$ot})" : '') . ".";
                }

                // Limpiar también cualquier registro incompleto o pendiente en la tabla general 'piezas'
                if ($ot && $ot !== 'N/A') {
                    $numJuego = preg_replace('/[^0-9]/', '', (string) ($nJuego ?: $nPieza));
                    if (!empty($numJuego)) {
                        Pieza::query()
                            ->where('id_ot', '=', $ot, 'and')
                            ->where('proceso', '=', $proceso, 'and')
                            ->where(function ($q) use ($numJuego, $nPieza) {
                                $q->where('n_pieza', '=', $nPieza, 'and')
                                    ->orWhere('n_pieza', '=', $numJuego . 'H', 'and')
                                    ->orWhere('n_pieza', '=', $numJuego . 'M', 'and')
                                    ->orWhere('n_pieza', '=', $numJuego . 'J', 'and');
                            })
                            ->where('liberacion', '=', 0, 'and')
                            ->delete();
                    }
                }
            } else {
                // Viene directamente de la tabla general 'piezas'
                $nPieza = $generalPiece->n_pieza;
                $ot = $generalPiece->id_ot;
                $claseObj = $generalPiece->id_clase ? Clase::find($generalPiece->id_clase) : null;
                $claseNombre = $claseObj ? $claseObj->nombre : null;
                $numJuego = preg_replace('/[^0-9]/', '', (string) $nPieza);

                // Buscar y eliminar en la tabla del proceso correspondiente si existe
                if (class_exists($pieceModel) && !empty($numJuego)) {
                    $pieceModel::query()
                        ->where(function ($q) use ($numJuego, $nPieza) {
                            $q->where('n_pieza', '=', $nPieza, 'and')
                                ->orWhere('n_pieza', '=', $numJuego . 'H', 'and')
                                ->orWhere('n_pieza', '=', $numJuego . 'M', 'and')
                                ->orWhere('n_juego', '=', $numJuego . 'J', 'and');
                        })
                        ->delete();
                }

                $generalPiece->delete();
                $actionDetail = "Se eliminó el registro de la pieza '{$nPieza}' en el proceso '{$proceso}' (OT: {$ot}).";
            }

            SystemLog::create([
                'user_matricula' => Auth::check() ? Auth::user()->matricula : null,
                'action' => 'Liberación de Pieza Ocupada / Incompleta',
                'details' => $actionDetail,
                'ot' => ($ot && $ot !== 'N/A') ? $ot : null,
                'id_ot' => ($ot && $ot !== 'N/A') ? $ot : null,
                'clase' => $claseNombre,
                'proceso' => $proceso,
                'n_pieza' => $nPieza,
                'maquina' => $meta->maquina ?? null,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "La pieza '{$nPieza}' fue liberada correctamente y el juego quedó disponible para maquinar.",
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error al liberar pieza ocupada.', [
                'proceso' => $proceso,
                'piece_id' => $pieceId,
                'error' => $e->getMessage(),
                'user' => Auth::check() ? Auth::user()->matricula : 'guest',
            ]);
            return response()->json(['success' => false, 'message' => 'Ocurrió un error interno al intentar liberar la pieza.'], 500);
        }
    }

    /**
     * Libera en lote múltiples piezas ocupadas.
     */
    public function freeUpBatch(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.proceso' => 'required|string',
            'items.*.piece_id' => 'required|integer',
        ]);

        $items = $request->input('items');
        $liberadas = 0;

        try {
            DB::beginTransaction();

            foreach ($items as $item) {
                $proceso = $item['proceso'];
                $pieceId = $item['piece_id'];

                if (!isset(self::$processConfig[$proceso])) {
                    continue;
                }

                $pieceModel = self::$processConfig[$proceso]['piece_model'];
                $isInitial = self::$processConfig[$proceso]['is_initial'];

                $piece = $pieceModel::query()->find($pieceId);
                if (!$piece) {
                    continue;
                }

                if ($isInitial) {
                    $piece->delete();
                } else {
                    $piece->estado = 0;
                    $piece->id_meta = null;
                    $piece->save();
                }
                $liberadas++;
            }

            SystemLog::create([
                'user_matricula' => Auth::check() ? Auth::user()->matricula : null,
                'action' => 'Liberación Masiva de Piezas',
                'details' => "El administrador liberó {$liberadas} piezas ocupadas en lote.",
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Se liberaron exitosamente {$liberadas} piezas.",
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error en liberación masiva de piezas.', [
                'error' => $e->getMessage(),
                'user' => Auth::check() ? Auth::user()->matricula : 'guest',
            ]);
            return response()->json(['success' => false, 'message' => 'Error interno al liberar piezas en lote.'], 500);
        }
    }

    /**
     * Modifica los datos de una pieza ocupada (n_pieza, n_juego, estado, id_meta).
     */
    public function update(Request $request)
    {
        $request->validate([
            'proceso' => 'required|string',
            'piece_id' => 'required|integer',
            'n_pieza' => 'nullable|string|max:50',
            'n_juego' => 'nullable|string|max:50',
            'estado' => 'required|integer|in:0,1,2',
            'id_meta' => 'nullable|integer',
        ]);

        $proceso = $request->input('proceso');
        $pieceId = $request->input('piece_id');

        if (!isset(self::$processConfig[$proceso])) {
            return response()->json(['success' => false, 'message' => 'Proceso no reconocido.'], 400);
        }

        $pieceModel = self::$processConfig[$proceso]['piece_model'];

        try {
            DB::beginTransaction();

            $piece = $pieceModel::query()->find($pieceId);
            if (!$piece) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Pieza no encontrada.'], 404);
            }

            $oldEstado = $piece->estado;
            if ($request->filled('n_pieza')) {
                $piece->n_pieza = $request->input('n_pieza');
            }
            if ($request->filled('n_juego')) {
                $piece->n_juego = $request->input('n_juego');
            }
            $piece->estado = (int) $request->input('estado');

            if ($request->has('id_meta')) {
                $piece->id_meta = $request->input('id_meta') ?: null;
            }

            $piece->save();

            $meta = $piece->id_meta ? Metas::find($piece->id_meta) : null;
            $parentModel = self::$processConfig[$proceso]['parent_model'] ?? null;
            $parent = ($parentModel && isset($piece->id_proceso)) ? $parentModel::find($piece->id_proceso) : null;
            $ot = $meta->id_ot ?? $parent->id_ot ?? null;
            if (!$ot && $parent && isset($parent->id_proceso)) {
                $parts = explode('_', $parent->id_proceso);
                $ot = end($parts);
            }
            $claseNombre = null;
            if ($meta && $meta->id_clase) {
                $claseObj = Clase::find($meta->id_clase);
                $claseNombre = $claseObj ? $claseObj->nombre : null;
            } elseif ($parent && isset($parent->id_proceso)) {
                $parts = explode('_', $parent->id_proceso);
                if (count($parts) >= 2) {
                    $claseNombre = $parts[1];
                }
            }

            SystemLog::create([
                'user_matricula' => Auth::check() ? Auth::user()->matricula : null,
                'action' => 'Modificación de Pieza Ocupada',
                'details' => "El administrador modificó la pieza #{$pieceId} ({$piece->n_pieza}) del proceso {$proceso}." . ($ot ? " OT: {$ot}." : '') . " Estado anterior: {$oldEstado}, Nuevo estado: {$piece->estado}.",
                'ot' => $ot,
                'id_ot' => $ot,
                'clase' => $claseNombre,
                'proceso' => $proceso,
                'n_pieza' => $piece->n_pieza ?? null,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pieza modificada correctamente.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error al actualizar pieza ocupada.', [
                'proceso' => $proceso,
                'piece_id' => $pieceId,
                'error' => $e->getMessage(),
                'user' => Auth::check() ? Auth::user()->matricula : 'guest',
            ]);
            return response()->json(['success' => false, 'message' => 'Error interno al actualizar la pieza.'], 500);
        }
    }

    /**
     * Elimina el registro huérfano de la pieza.
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'proceso' => 'required|string',
            'piece_id' => 'required|integer',
        ]);

        $proceso = $request->input('proceso');
        $pieceId = $request->input('piece_id');

        if (!isset(self::$processConfig[$proceso])) {
            return response()->json(['success' => false, 'message' => 'Proceso no reconocido.'], 400);
        }

        $pieceModel = self::$processConfig[$proceso]['piece_model'];

        try {
            DB::beginTransaction();

            $piece = $pieceModel::query()->find($pieceId);
            if (!$piece) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Pieza no encontrada.'], 404);
            }

            $nPieza = $piece->n_pieza ?? $piece->n_juego ?? "#{$pieceId}";

            $meta = isset($piece->id_meta) && $piece->id_meta ? Metas::find($piece->id_meta) : null;
            $parentModel = self::$processConfig[$proceso]['parent_model'] ?? null;
            $parent = ($parentModel && isset($piece->id_proceso)) ? $parentModel::find($piece->id_proceso) : null;
            $ot = $meta->id_ot ?? $parent->id_ot ?? null;
            if (!$ot && $parent && isset($parent->id_proceso)) {
                $parts = explode('_', $parent->id_proceso);
                $ot = end($parts);
            }
            $claseNombre = null;
            if ($meta && $meta->id_clase) {
                $claseObj = Clase::find($meta->id_clase);
                $claseNombre = $claseObj ? $claseObj->nombre : null;
            } elseif ($parent && isset($parent->id_proceso)) {
                $parts = explode('_', $parent->id_proceso);
                if (count($parts) >= 2) {
                    $claseNombre = $parts[1];
                }
            }

            $piece->delete();

            SystemLog::create([
                'user_matricula' => Auth::check() ? Auth::user()->matricula : null,
                'action' => 'Eliminación de Pieza Ocupada',
                'details' => "El administrador eliminó el registro de la pieza {$nPieza} en el proceso {$proceso}." . ($ot ? " OT: {$ot}." : ''),
                'ot' => $ot,
                'id_ot' => $ot,
                'clase' => $claseNombre,
                'proceso' => $proceso,
                'n_pieza' => $nPieza,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "La pieza '{$nPieza}' fue eliminada correctamente del registro de ocupadas.",
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error al eliminar pieza ocupada.', [
                'proceso' => $proceso,
                'piece_id' => $pieceId,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['success' => false, 'message' => 'Error interno al eliminar la pieza.'], 500);
        }
    }

    /**
     * Obtiene los registros de auditoría/logs de acciones administrativas en piezas ocupadas.
     */
    public function getLogs(Request $request)
    {
        try {
            $query = SystemLog::query()
                ->select([
                    'system_logs.*',
                    'users.nombre as user_nombre',
                    'users.a_paterno as user_paterno',
                ])
                ->leftJoin('users', 'system_logs.user_matricula', '=', 'users.matricula')
                ->whereIn('system_logs.action', [
                    'Liberación de Pieza Ocupada',
                    'Liberación Masiva de Piezas',
                    'Modificación de Pieza Ocupada',
                    'Eliminación de Pieza Ocupada',
                ]);

            if ($request->filled('ot')) {
                $baseOt = preg_match('/^(\d+)/', $request->input('ot'), $matches) ? $matches[1] : $request->input('ot');
                $query->where('system_logs.ot', 'LIKE', $baseOt . '%');
            }

            if ($request->filled('proceso')) {
                $query->where('system_logs.proceso', '=', $request->input('proceso'), 'and');
            }

            $logs = $query->orderBy('system_logs.created_at', 'desc')
                ->limit(100)
                ->get()
                ->map(function ($log) {
                    $fullName = trim(($log->user_nombre ?? '') . ' ' . ($log->user_paterno ?? ''));
                    $details = (string) $log->details;

                    // Fallbacks para registros antiguos que no guardaron pieza/OT/clase
                    $pieza = $log->n_pieza;
                    if (!$pieza && preg_match("/pieza\\s+'?([^'\\s(]+)'?/u", $details, $m)) {
                        $pieza = $m[1];
                    }
                    $ot = $log->ot ?? $log->id_ot;
                    if (!$ot && preg_match('/OT:\\s*(\\d+)/', $details, $m)) {
                        $ot = $m[1];
                    }
                    $clase = $log->clase;
                    if (!$clase && preg_match('/Meta:\\s*(\\d+)/', $details, $m)) {
                        $metaLog = Metas::find((int) $m[1]);
                        if ($metaLog && $metaLog->id_clase) {
                            $clase = Clase::find($metaLog->id_clase)?->nombre;
                        }
                    }

                    return [
                        'id' => $log->id,
                        'action' => $log->action,
                        'details' => $details,
                        'ot' => $ot ?: '—',
                        'clase' => $clase ?: '—',
                        'proceso' => $log->proceso ?? '—',
                        'n_pieza' => $pieza ?: '—',
                        'user_matricula' => $log->user_matricula ?? '—',
                        'user_nombre' => $fullName ?: ($log->user_matricula ?? 'Administrador'),
                        'created_at' => $log->created_at ? $log->created_at->format('d/m/Y H:i:s') : '—',
                        'time_ago' => $log->created_at ? $log->created_at->diffForHumans() : '',
                    ];
                });

            return response()->json([
                'success' => true,
                'logs' => $logs,
                'total' => $logs->count(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Error al obtener logs de piezas ocupadas: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al cargar historial de auditoría.'], 500);
        }
    }
}
