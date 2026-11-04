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
use Illuminate\Support\Facades\Cache;
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

    /**
     * Mapeo de columnas de la tabla procesos a claves de proceso en $processConfig.
     */
    protected static array $processColumnMap = [
        'cepillado' => 'Cepillado',
        'desbaste_exterior' => 'Desbaste Exterior',
        'revision_laterales' => 'Revision Laterales',
        'pOperacion' => 'Primera Operacion',
        'barreno_maniobra' => 'Barreno Maniobra',
        'sOperacion' => 'Segunda Operacion',
        'soldadura' => 'Soldadura',
        'soldaduraPTA' => 'Soldadura PTA',
        'rectificado' => 'Rectificado',
        'asentado' => 'Asentado',
        'calificado' => 'Calificado',
        'acabadoBombillo' => 'Acabado Bombillo',
        'acabadoMolde' => 'Acabado Molde',
        'barreno_profundidad' => 'Barreno Profundidad',
        'cavidades' => 'Cavidades',
        'copiado' => 'Copiado',
        'offSet' => 'Off Set',
        'palomas' => 'Palomas',
        'rebajes' => 'Rebajes',
        'operacionEquipo' => 'Operacion Equipo',
        'embudoCM' => 'Embudo CM',
        'primeraOperacionCabezaSoplo' => 'Primera Operacion Cabeza Soplo',
        'segundaOperacionCabezaSoplo' => 'Segunda Operacion Cabeza Soplo',
    ];

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Obtiene los IDs de OTs que tienen piezas ocupadas en cualquier proceso.
     */
    protected function getOccupiedOtIds(): array
    {
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
                            $parts = explode('_', (string) $parent->id_proceso);
                            $otCandidate = end($parts);
                            if (is_numeric($otCandidate)) {
                                $occupiedOtIds[] = (int) $otCandidate;
                            }
                        }
                    }
                }
            }
        }

        return array_values(array_unique(array_filter($occupiedOtIds)));
    }

    /** Cache en memoria de OTs activas para evitar escanear la tabla piezas múltiples veces */
    protected ?array $cachedActiveOtIds = null;

    /**
     * Limpia la caché de piezas ocupadas para sincronización inmediata tras modificaciones.
     */
    public function clearPiecesCache(): void
    {
        $this->cachedActiveOtIds = null;
        Cache::forget('pieces_occupied_initial_payload');
        Cache::forget('pieces_occupied_active_ot_ids');
        Cache::forget('pieces_occupied_show_catalogs');
    }

    /**
     * Obtiene los IDs de las OTs activas que tienen piezas en maquinado/producción actualmente
     * (idéntico a la lógica optimizada de Liberación de Piezas / Reporte de Piezas).
     */
    public function getActiveOtIds(): array
    {
        if ($this->cachedActiveOtIds !== null) {
            return $this->cachedActiveOtIds;
        }

        return $this->cachedActiveOtIds = Cache::remember('pieces_occupied_active_ot_ids', 600, function () {
            return Clase::query()
                ->where('finalizada', '=', 0, 'and')
                ->whereNotNull('id_ot', 'and')
                ->distinct()
                ->pluck('id_ot')
                ->filter()
                ->map(fn ($id) => (string) $id)
                ->unique()
                ->values()
                ->toArray();
        });
    }

    /**
     * Obtiene el catálogo de clases activas en el sistema para OTs que tengan piezas realizándose.
     */
    protected function getActiveClasses(?string $otId = null): array
    {
        $activeClassIds = Clase::query()
            ->where('finalizada', '=', 0, 'and')
            ->pluck('id')
            ->toArray();

        $activeOtIds = $this->getActiveOtIds();

        $query = Clase::query()
            ->distinct()
            ->whereNotNull('nombre', 'and')
            ->whereIn('id', $activeClassIds, 'and', false);

        if (! empty($otId)) {
            $query->where('id_ot', '=', $otId, 'and');
        } else {
            $query->whereIn('id_ot', $activeOtIds, 'and', false);
        }

        $dbClasses = $query->pluck('nombre')->toArray();
        $seen = [];
        $classesList = [];
        foreach ($dbClasses as $dbClass) {
            $clean = trim(preg_replace('/^\d+\s*-\s*/', '', (string) $dbClass));
            $lower = mb_strtolower($clean, 'UTF-8');
            if ($clean !== '' && ! isset($seen[$lower])) {
                $seen[$lower] = true;
                $classesList[] = mb_convert_case($clean, MB_CASE_TITLE, 'UTF-8');
            }
        }
        natcasesort($classesList);

        return array_values($classesList);
    }

    /**
     * Obtiene los procesos activos según el contexto (OT, Clase o general en el sistema).
     */
    protected function getActiveProcesses(?string $otId = null, ?string $claseId = null): array
    {
        $activeClassIds = Clase::query()
            ->where('finalizada', '=', 0, 'and')
            ->pluck('id')
            ->toArray();

        $activeOtIds = $this->getActiveOtIds();

        $claseQuery = Clase::query()
            ->whereIn('id', $activeClassIds, 'and', false);

        if (! empty($otId)) {
            $claseQuery->where('id_ot', '=', $otId, 'and');
            if (! empty($claseId)) {
                if (is_numeric($claseId)) {
                    $claseQuery->where('id', '=', (int) $claseId, 'and');
                } else {
                    $cleanName = trim(preg_replace('/^\d+\s*-\s*/', '', mb_strtolower($claseId, 'UTF-8')));
                    $claseQuery->where(function ($q) use ($claseId, $cleanName) {
                        $q->where('nombre', '=', $claseId, 'and')
                            ->orWhere('nombre', 'LIKE', "%{$cleanName}%");
                    });
                }
            }
        } else {
            $claseQuery->whereIn('id_ot', $activeOtIds, 'and', false);
        }

        $activeClassIds = $claseQuery->pluck('id')->toArray();

        $activeProcessKeys = [];
        if (! empty($activeClassIds)) {
            $procesosRows = \App\Models\Procesos::query()->whereIn('id_clase', $activeClassIds, 'and', false)->get();
            foreach ($procesosRows as $pRow) {
                foreach (self::$processColumnMap as $col => $procKey) {
                    if (! empty($pRow->{$col})) {
                        $activeProcessKeys[$procKey] = true;
                    }
                }
            }
        }

        $processesList = [];
        foreach (self::$processConfig as $key => $cfg) {
            if (isset($activeProcessKeys[$key])) {
                $processesList[] = [
                    'key' => $key,
                    'label' => $cfg['label'],
                ];
            }
        }

        if (empty($processesList)) {
            foreach (self::$processConfig as $key => $cfg) {
                $processesList[] = [
                    'key' => $key,
                    'label' => $cfg['label'],
                ];
            }
        }

        return $processesList;
    }

    /**
     * Obtiene los operadores activos en el sistema (perfil 2 = Operador Maquinado, estatus = 1)
     * e incluye cualquier operador con piezas ocupadas actualmente.
     */
    protected function getActiveOperators(array $occupiedPieces = []): array
    {
        $activeOperators = User::query()
            ->select(['matricula', 'nombre', 'a_paterno', 'a_materno'])
            ->where('perfil', '=', 2, 'and')
            ->where('estatus', '=', 1, 'and')
            ->get();

        $operatorsMap = [];
        foreach ($activeOperators as $u) {
            $fullName = trim("{$u->nombre} {$u->a_paterno} {$u->a_materno}");
            $operatorsMap[(string) $u->matricula] = [
                'matricula' => (string) $u->matricula,
                'nombre' => "{$fullName} (Mat: {$u->matricula})",
                'clean_name' => $fullName,
            ];
        }

        // Asegurar que operadores con piezas ocupadas retenidas actualmente estén presentes
        foreach ($occupiedPieces as $p) {
            $mat = $p['operador_matricula'] ?? null;
            $nom = trim((string) ($p['operador_nombre'] ?? ''));
            if ($mat && ! empty($nom) && $nom !== 'Desconocido' && ! isset($operatorsMap[(string) $mat])) {
                $operatorsMap[(string) $mat] = [
                    'matricula' => (string) $mat,
                    'nombre' => "{$nom} (Mat: {$mat})",
                    'clean_name' => $nom,
                ];
            }
        }

        uasort($operatorsMap, fn ($a, $b) => strcasecmp($a['clean_name'], $b['clean_name']));

        return array_values(array_map(fn ($op) => [
            'matricula' => $op['matricula'],
            'nombre' => $op['nombre'],
        ], $operatorsMap));
    }

    /**
     * Muestra la vista principal de piezas ocupadas con filtros activos exclusivamente.
     */
    public function show()
    {
        $targetOtIds = $this->getActiveOtIds();

        $cacheKey = 'pieces_occupied_show_catalogs_' . md5(implode(',', $targetOtIds));
        $data = Cache::remember($cacheKey, 600, function () use ($targetOtIds) {
            // Cargar SOLO OTs activas que tengan piezas realizándose en maquinado (con moldura)
            $workOrders = Orden_trabajo::query()
                ->select(['id', 'id_moldura', 'created_at'])
                ->with(['moldura:id,nombre'])
                ->whereIn('id', $targetOtIds, 'and', false)
                ->orderBy('id', 'asc')
                ->get();

            // Procesos y clases activas en el sistema para estas OTs
            $processesList = $this->getActiveProcesses(null, null);
            $classesList = $this->getActiveClasses();

            // Operadores activos en el sistema (perfil 2 y estatus 1)
            $operatorsList = $this->getActiveOperators([]);

            return compact('workOrders', 'processesList', 'classesList', 'operatorsList');
        });

        return view('pieces_views.piecesOccupied', $data);
    }

    /**
     * Obtiene las clases y procesos activos disponibles según la combinación de OT y Clase.
     */
    public function getClassesByOt(Request $request)
    {
        $otId = $request->query('ot');
        $claseId = $request->query('clase_id');

        if (! $otId) {
            // Si no se especifica OT, devolver las clases activas normalizadas en el sistema
            $classesList = $this->getActiveClasses();
            $classes = array_map(function ($cName) {
                return [
                    'id' => $cName,
                    'nombre' => $cName,
                    'piezas' => null,
                ];
            }, $classesList);
        } else {
            $classes = Clase::query()
                ->select(['id', 'id_ot', 'nombre', 'piezas', 'finalizada'])
                ->where('id_ot', '=', $otId, 'and')
                ->where('finalizada', '=', 0, 'and')
                ->orderBy('nombre')
                ->get();
        }

        // Obtener los procesos activos para este contexto de OT y Clase
        $processes = $this->getActiveProcesses($otId, $claseId);

        return response()->json([
            'classes' => $classes,
            'processes' => $processes,
        ]);
    }

    /**
     * Devuelve los números de pieza y operadores disponibles según la combinación de OT, Clase y Proceso.
     * Reduce dinámicamente las opciones sin requerir obligatoriamente los 3 campos.
     */
    public function getPiecesByContext(Request $request)
    {
        $targetOt = $request->input('ot');
        $targetClase = $request->input('clase_id');
        $targetProcess = $request->input('proceso');

        // Consultar piezas ocupadas para este contexto
        $contextPieces = $this->fetchOccupiedPiecesList($targetOt, $targetClase, $targetProcess);

        // Extraer números de pieza únicos
        $numbersMap = [];
        foreach ($contextPieces as $p) {
            foreach ([$p['n_pieza'], $p['n_juego']] as $str) {
                if ($str && preg_match('/\d+/', (string) $str, $matches)) {
                    $num = (int) $matches[0];
                    if ($num > 0) {
                        $numbersMap[$num] = true;
                    }
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

        // Extraer operadores únicos asociados al contexto de piezas ocupadas
        $operatorsMap = [];
        foreach ($contextPieces as $p) {
            $mat = $p['operador_matricula'] ?? null;
            $nom = trim((string) ($p['operador_nombre'] ?? ''));
            if ($mat && ! empty($nom) && $nom !== 'Desconocido') {
                $operatorsMap[(string) $mat] = [
                    'matricula' => (string) $mat,
                    'nombre' => "{$nom} (Mat: {$mat})",
                    'clean_name' => $nom,
                ];
            }
        }

        uasort($operatorsMap, function ($a, $b) {
            return strcasecmp($a['clean_name'], $b['clean_name']);
        });

        $operatorsList = array_values(array_map(function ($op) {
            return [
                'matricula' => $op['matricula'],
                'nombre' => $op['nombre'],
            ];
        }, $operatorsMap));

        return response()->json([
            'success' => true,
            'pieces' => $piecesList,
            'operators' => $operatorsList,
        ]);
    }

    /**
     * Consulta AJAX de piezas ocupadas filtradas por OT, Clase y Proceso.
     * Detecta piezas retenidas (estado 1), registros desvinculados sin máquina y juegos incompletos (mitad faltante).
     * Devuelve en una sola respuesta las piezas, KPIs y filtros dependientes para máxima fluidez.
     */
    public function getOccupiedPieces(Request $request)
    {
        $targetOt = $request->input('ot');
        $targetClaseId = $request->input('clase_id');
        $targetProcess = $request->input('proceso');
        $targetPieza = trim((string) $request->input('pieza', ''));
        $targetOperador = trim((string) $request->input('operador', ''));

        $isDefaultLoad = empty($targetOt) && empty($targetClaseId) && empty($targetProcess) && empty($targetPieza) && empty($targetOperador);

        if ($isDefaultLoad) {
            $cachedPayload = Cache::get('pieces_occupied_initial_payload');
            if ($cachedPayload !== null) {
                return response()->json($cachedPayload);
            }
        }

        // 1. Obtener las piezas ocupadas para el contexto de OT, Clase y Proceso
        $contextPieces = $this->fetchOccupiedPiecesList($targetOt, $targetClaseId, $targetProcess);

        // Operadores activos en el sistema (perfil 2 y estatus 1) + operadores con piezas ocupadas
        $availableOperators = $this->getActiveOperators($contextPieces);

        // Números de pieza: solo se activan y calculan si OT, Clase y Proceso están los 3 seleccionados
        $allThreeSelected = ! empty($targetOt) && ! empty($targetClaseId) && ! empty($targetProcess);
        $availablePieces = [];

        if ($allThreeSelected) {
            $numbersMap = [];
            foreach ($contextPieces as $p) {
                foreach ([$p['n_pieza'], $p['n_juego']] as $str) {
                    if ($str && preg_match('/\d+/', (string) $str, $matches)) {
                        $num = (int) $matches[0];
                        if ($num > 0) {
                            $numbersMap[$num] = true;
                        }
                    }
                }
            }
            $sortedNumbers = array_keys($numbersMap);
            sort($sortedNumbers, SORT_NUMERIC);
            $availablePieces = array_map(fn ($num) => [
                'id' => (string) $num,
                'label' => "Pieza #{$num}",
            ], $sortedNumbers);
        }

        // 2. Filtrar para la tabla por Pieza u Operador si fueron seleccionados
        $tablePieces = $contextPieces;

        if (! empty($targetPieza)) {
            preg_match('/\d+/', $targetPieza, $targetDigits);
            $targetNum = $targetDigits[0] ?? $targetPieza;

            $tablePieces = array_values(array_filter($tablePieces, function ($piece) use ($targetPieza, $targetNum) {
                $pNum = trim((string) ($piece['n_pieza'] ?? ''));
                $jNum = trim((string) ($piece['n_juego'] ?? ''));
                preg_match('/\d+/', $pNum, $pDigits);
                $pNumDigits = $pDigits[0] ?? '';
                preg_match('/\d+/', $jNum, $jDigits);
                $jNumDigits = $jDigits[0] ?? '';

                return ($pNumDigits !== '' && $pNumDigits === $targetNum) ||
                    ($jNumDigits !== '' && $jNumDigits === $targetNum) ||
                    stripos($pNum, $targetPieza) !== false ||
                    stripos($jNum, $targetPieza) !== false;
            }));
        }

        if (! empty($targetOperador)) {
            $tablePieces = array_values(array_filter($tablePieces, function ($piece) use ($targetOperador) {
                $mat = $piece['operador_matricula'] ?? null;
                $nom = $piece['operador_nombre'] ?? null;

                return ($mat && (string) $mat === (string) $targetOperador) ||
                    ($nom && stripos($nom, $targetOperador) !== false);
            }));
        }

        $totalOrphans = 0;
        $totalIncompletes = 0;
        $affectedOts = [];

        foreach ($tablePieces as $item) {
            if (! empty($item['is_orphan'])) {
                $totalOrphans++;
            }
            if (! empty($item['is_incomplete'])) {
                $totalIncompletes++;
            }
            if (! empty($item['id_ot']) && $item['id_ot'] !== 'N/A') {
                $affectedOts[$item['id_ot']] = true;
            }
        }

        $responseData = [
            'success' => true,
            'summary' => [
                'total_occupied' => count($tablePieces),
                'total_orphans' => $totalOrphans,
                'total_incompletes' => $totalIncompletes,
                'affected_ots' => count($affectedOts),
            ],
            'pieces' => $tablePieces,
            'available_operators' => $availableOperators,
            'available_pieces' => $availablePieces,
            'all_three_selected' => $allThreeSelected,
        ];

        if ($isDefaultLoad) {
            Cache::put('pieces_occupied_initial_payload', $responseData, 180);
        }

        return response()->json($responseData);
    }

    /**
     * Obtiene la lista completa de piezas ocupadas según los filtros especificados.
     */
    public function fetchOccupiedPiecesList($targetOt = null, $targetClaseId = null, $targetProcess = null, $targetPieza = null, $targetOperador = null): array
    {
        $processesToScan = self::$processConfig;
        if (! empty($targetProcess) && isset(self::$processConfig[$targetProcess])) {
            $processesToScan = [$targetProcess => self::$processConfig[$targetProcess]];
        }

        $occupiedPieces = [];
        $activeMachiningOtIds = $this->getActiveOtIds();
        $activeClassIds = Clase::query()
            ->where('finalizada', '=', 0, 'and')
            ->pluck('id')
            ->flip()
            ->toArray();

        // Pre-cargar catálogo de clases para mapeo rápido sin N+1
        $classesMap = Clase::query()->select(['id', 'nombre', 'id_ot', 'finalizada'])->get()->keyBy('id');
        // Pre-cargar usuarios para mapear matricula -> nombre de operador sin N+1
        $usersMap = User::query()->select(['matricula', 'nombre', 'a_paterno', 'a_materno'])->get()->keyBy('matricula');
        // Pre-cargar máquinas activas actualmente
        $activeMachineMetaIds = Maquinas::query()->pluck('id_meta')->filter()->toArray();

        // Normalización de Clase para comparación tolerante
        $targetClaseObj = (! empty($targetClaseId) && is_numeric($targetClaseId)) ? Clase::find((int) $targetClaseId) : null;
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

            if (! class_exists($pieceModel)) {
                continue;
            }

            // 1. Buscar piezas en estado 1 (Ocupada / Retenida)
            $query = $pieceModel::query()->where('estado', '=', 1, 'and');
            $rawPieces = $query->get();

            // 2. Para procesos con mitades, buscar además juegos incompletos (donde solo una mitad fue registrada o maquinada)
            $isTwoHalf = in_array($procKey, $twoHalfProcesses);
            $rawIncompletePieces = collect();

            if ($isTwoHalf) {
                $incompleteIds = $pieceModel::query()
                    ->select(DB::raw('MAX(id) as id'))
                    ->whereIn('estado', [1, 2], 'and')
                    ->whereNotNull('n_juego')
                    ->groupBy('id_proceso', 'n_juego')
                    ->havingRaw('COUNT(*) = 1')
                    ->pluck('id');

                if ($incompleteIds->isNotEmpty()) {
                    $candidates = $pieceModel::query()->whereIn('id', $incompleteIds, 'and', false)->get();
                    foreach ($candidates as $cand) {
                        $pName = (string) ($cand->n_pieza ?? '');
                        $lastChar = strtoupper(substr($pName, -1));
                        if ($lastChar === 'H' || $lastChar === 'M') {
                            $rawIncompletePieces->push($cand);
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
                if (! $ot && $parent && isset($parent->id_proceso)) {
                    $parts = explode('_', $parent->id_proceso);
                    $ot = end($parts);
                }

                // Filtrar por OT si fue especificada; si no, asegurar que pertenezca a las OTs activas en maquinado
                if (! empty($targetOt)) {
                    if ((string) $ot !== (string) $targetOt) {
                        continue;
                    }
                } else {
                    if (! $ot || ! in_array((string) $ot, $activeMachiningOtIds)) {
                        continue;
                    }
                }

                // Determinar Clase
                $claseId = $meta->id_clase ?? null;
                $claseNombre = 'N/A';

                if ($claseId) {
                    $claseObj = $classesMap->get($claseId);
                    $claseNombre = $claseObj ? $claseObj->nombre : "Clase #{$claseId}";
                } elseif ($parent && isset($parent->id_proceso)) {
                    $parts = explode('_', $parent->id_proceso);
                    if (count($parts) >= 2) {
                        $claseNombre = $parts[1];
                    }
                }

                // Descartar piezas pertenecientes a clases ya finalizadas
                if ($claseId && ! isset($activeClassIds[$claseId])) {
                    continue;
                }

                // Filtrar por Clase si fue especificada
                if (! $matchesTargetClass($claseId, $claseNombre)) {
                    continue;
                }

                // Filtrar por Pieza / Juego si fue especificada (engloba macho y hembra guiadas por el número)
                if (! empty($targetPieza)) {
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

                    if (! $matchesPiece) {
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
                if (! empty($targetOperador)) {
                    $matchesOp = false;
                    if ($operadorMatricula && (string) $operadorMatricula === (string) $targetOperador) {
                        $matchesOp = true;
                    } elseif ($operadorNombre && stripos($operadorNombre, $targetOperador) !== false) {
                        $matchesOp = true;
                    }
                    if (! $matchesOp) {
                        continue;
                    }
                }

                // Determinar si es incompleta o desvinculada (sin máquina activa)
                $isIncomplete = $rawIncompletePieces->contains('id', $piece->id);
                $isOrphan = false;

                if (! $isIncomplete) {
                    if (! $meta || ! in_array($meta->id, $activeMachineMetaIds)) {
                        $isOrphan = true;
                    }
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

        if (! empty($targetOt)) {
            $incompleteGeneralQuery->where('id_ot', '=', $targetOt, 'and');
        } else {
            $incompleteGeneralQuery->whereIn('id_ot', $activeMachiningOtIds, 'and', false);
        }

        if (! empty($activeClassIds)) {
            $incompleteGeneralQuery->whereIn('id_clase', array_keys($activeClassIds), 'and', false);
        }

        if (! empty($targetProcess)) {
            $incompleteGeneralQuery->where('proceso', '=', $targetProcess, 'and');
        }
        if (! empty($targetPieza)) {
            preg_match('/\d+/', $targetPieza, $targetDigits);
            $targetNum = $targetDigits[0] ?? $targetPieza;
            $incompleteGeneralQuery->where(function ($q) use ($targetPieza, $targetNum) {
                $q->where('n_pieza', '=', $targetPieza)
                    ->orWhere('n_pieza', 'LIKE', "{$targetNum}%")
                    ->orWhere('n_pieza', 'LIKE', "%{$targetPieza}%");
            });
        }
        if (! empty($targetOperador)) {
            $incompleteGeneralQuery->where('id_operador', '=', $targetOperador, 'and');
        }

        $generalIncompletes = $incompleteGeneralQuery->get();
        foreach ($generalIncompletes as $gp) {
            $claseObj = $gp->id_clase ? $classesMap->get($gp->id_clase) : null;
            $claseNombre = $claseObj ? $claseObj->nombre : 'N/A';

            if (! $matchesTargetClass($gp->id_clase, $claseNombre)) {
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

            if (! $alreadyIncluded) {
                $opUser = $gp->id_operador ? $usersMap->get($gp->id_operador) : null;
                $opName = $opUser ? "{$opUser->nombre} {$opUser->a_paterno}" : ($gp->id_operador ?: 'Desconocido');

                $occupiedPieces[] = [
                    'id' => $gp->id,
                    'proceso_key' => $procKey,
                    'proceso_label' => $procLabel,
                    'is_initial' => $isInitial,
                    'n_pieza' => $gp->n_pieza,
                    'n_juego' => rtrim($gp->n_pieza, 'HM').'J',
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

        return $occupiedPieces;
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

        if (! isset(self::$processConfig[$proceso])) {
            return response()->json(['success' => false, 'message' => 'Proceso no reconocido.'], 400);
        }

        $pieceModel = self::$processConfig[$proceso]['piece_model'];
        $isInitial = self::$processConfig[$proceso]['is_initial'];

        try {
            DB::beginTransaction();

            $piece = $pieceModel::query()->find($pieceId);
            $generalPiece = null;

            if (! $piece) {
                // Si no se encuentra en el modelo del proceso, verificar si es un ID de la tabla general 'piezas'
                $generalPiece = Pieza::query()->find($pieceId);
                if (! $generalPiece) {
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
                    if (! $ot && isset($parent->id_proceso)) {
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
                    $actionDetail = "Se liberó y eliminó el registro de la pieza '{$nPieza}' en el proceso '{$proceso}'".($ot && $ot !== 'N/A' ? " (OT: {$ot})" : '').' para restablecer la disponibilidad completa del juego.';
                } else {
                    // En otros procesos con estado 1, pasar a 0 para que otro operador pueda tomarlo
                    $piece->estado = 0;
                    $piece->id_meta = null;
                    $piece->save();
                    $actionDetail = "Se cambió el estado de la pieza '{$nPieza}' a 0 (desocupada) en el proceso '{$proceso}'".($ot && $ot !== 'N/A' ? " (OT: {$ot})" : '').'.';
                }

                // Limpiar también cualquier registro incompleto o pendiente en la tabla general 'piezas'
                if ($ot && $ot !== 'N/A') {
                    $numJuego = preg_replace('/[^0-9]/', '', (string) ($nJuego ?: $nPieza));
                    if (! empty($numJuego)) {
                        Pieza::query()
                            ->where('id_ot', '=', $ot, 'and')
                            ->where('proceso', '=', $proceso, 'and')
                            ->where(function ($q) use ($numJuego, $nPieza) {
                                $q->where('n_pieza', '=', $nPieza, 'and')
                                    ->orWhere('n_pieza', '=', $numJuego.'H', 'and')
                                    ->orWhere('n_pieza', '=', $numJuego.'M', 'and')
                                    ->orWhere('n_pieza', '=', $numJuego.'J', 'and');
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
                if (class_exists($pieceModel) && ! empty($numJuego)) {
                    $pieceModel::query()
                        ->where(function ($q) use ($numJuego, $nPieza) {
                            $q->where('n_pieza', '=', $nPieza, 'and')
                                ->orWhere('n_pieza', '=', $numJuego.'H', 'and')
                                ->orWhere('n_pieza', '=', $numJuego.'M', 'and')
                                ->orWhere('n_juego', '=', $numJuego.'J', 'and');
                        })
                        ->delete();
                }

                $generalPiece->delete();
                $actionDetail = "Se eliminó el registro de la pieza '{$nPieza}' en el proceso '{$proceso}' (OT: {$ot}).";
                $metaClaseId = $meta->id_clase ?? null;
            }

            SystemLog::create([
                'user_matricula' => Auth::check() ? Auth::user()->matricula : null,
                'action' => 'Liberación de Pieza Ocupada / Incompleta',
                'details' => $actionDetail,
                'ot' => ($ot && $ot !== 'N/A') ? $ot : null,
                'id_ot' => ($ot && $ot !== 'N/A') ? $ot : null,
                'id_clase' => $metaClaseId ?? null,
                'clase' => $claseNombre,
                'proceso' => $proceso,
                'n_pieza' => $nPieza,
                'maquina' => $meta->maquina ?? null,
            ]);

            DB::commit();
            $this->clearPiecesCache();

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

                if (! isset(self::$processConfig[$proceso])) {
                    continue;
                }

                $pieceModel = self::$processConfig[$proceso]['piece_model'];
                $isInitial = self::$processConfig[$proceso]['is_initial'];

                $piece = $pieceModel::query()->find($pieceId);
                if (! $piece) {
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
            $this->clearPiecesCache();

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

        if (! isset(self::$processConfig[$proceso])) {
            return response()->json(['success' => false, 'message' => 'Proceso no reconocido.'], 400);
        }

        $pieceModel = self::$processConfig[$proceso]['piece_model'];

        try {
            DB::beginTransaction();

            $piece = $pieceModel::query()->find($pieceId);
            if (! $piece) {
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
            if (! $ot && $parent && isset($parent->id_proceso)) {
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
                'details' => "El administrador modificó la pieza #{$pieceId} ({$piece->n_pieza}) del proceso {$proceso}.".($ot ? " OT: {$ot}." : '')." Estado anterior: {$oldEstado}, Nuevo estado: {$piece->estado}.",
                'ot' => $ot,
                'id_ot' => $ot,
                'clase' => $claseNombre,
                'proceso' => $proceso,
                'n_pieza' => $piece->n_pieza ?? null,
            ]);

            DB::commit();
            $this->clearPiecesCache();

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

        if (! isset(self::$processConfig[$proceso])) {
            return response()->json(['success' => false, 'message' => 'Proceso no reconocido.'], 400);
        }

        $pieceModel = self::$processConfig[$proceso]['piece_model'];

        try {
            DB::beginTransaction();

            $piece = $pieceModel::query()->find($pieceId);
            if (! $piece) {
                DB::rollBack();

                return response()->json(['success' => false, 'message' => 'Pieza no encontrada.'], 404);
            }

            $nPieza = $piece->n_pieza ?? $piece->n_juego ?? "#{$pieceId}";

            $meta = isset($piece->id_meta) && $piece->id_meta ? Metas::find($piece->id_meta) : null;
            $parentModel = self::$processConfig[$proceso]['parent_model'] ?? null;
            $parent = ($parentModel && isset($piece->id_proceso)) ? $parentModel::find($piece->id_proceso) : null;
            $ot = $meta->id_ot ?? $parent->id_ot ?? null;
            if (! $ot && $parent && isset($parent->id_proceso)) {
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
                'details' => "El administrador eliminó el registro de la pieza {$nPieza} en el proceso {$proceso}.".($ot ? " OT: {$ot}." : ''),
                'ot' => $ot,
                'id_ot' => $ot,
                'clase' => $claseNombre,
                'proceso' => $proceso,
                'n_pieza' => $nPieza,
            ]);

            DB::commit();
            $this->clearPiecesCache();

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
                ->where(function ($q) {
                    $q->whereIn('system_logs.action', [
                        'Liberación de Pieza Ocupada',
                        'Liberación de Pieza Ocupada / Incompleta',
                        'Liberación Masiva de Piezas',
                        'Modificación de Pieza Ocupada',
                        'Eliminación de Pieza Ocupada',
                    ])
                        ->orWhere('system_logs.action', 'LIKE', '%Pieza Ocupada%')
                        ->orWhere('system_logs.action', 'LIKE', '%Piezas Ocupadas%')
                        ->orWhere('system_logs.action', 'LIKE', '%Liberación Masiva%');
                });

            if ($request->filled('ot')) {
                $baseOt = preg_match('/^(\d+)/', $request->input('ot'), $matches) ? $matches[1] : $request->input('ot');
                $query->where(function ($q) use ($baseOt) {
                    $q->where('system_logs.ot', 'LIKE', $baseOt.'%')
                        ->orWhere('system_logs.id_ot', '=', $baseOt)
                        ->orWhere('system_logs.details', 'LIKE', "%OT: {$baseOt}%")
                        ->orWhere('system_logs.details', 'LIKE', "%(OT: {$baseOt})%");
                });
            }

            if ($request->filled('clase_id')) {
                $claseInput = $request->input('clase_id');
                $claseObj = is_numeric($claseInput) ? Clase::find((int) $claseInput) : null;
                $claseName = $claseObj ? $claseObj->nombre : (string) $claseInput;
                $cleanName = trim(preg_replace('/^\d+\s*-\s*/', '', mb_strtolower($claseName, 'UTF-8')));

                $query->where(function ($q) use ($claseName, $cleanName, $claseInput) {
                    $q->where('system_logs.clase', 'LIKE', "%{$cleanName}%")
                        ->orWhere('system_logs.clase', 'LIKE', "%{$claseName}%")
                        ->orWhere('system_logs.id_clase', '=', $claseInput);
                });
            }

            if ($request->filled('proceso')) {
                $query->where('system_logs.proceso', '=', $request->input('proceso'), 'and');
            }

            if ($request->filled('pieza')) {
                $p = $request->input('pieza');
                $query->where(function ($q) use ($p) {
                    $q->where('system_logs.n_pieza', 'LIKE', "%{$p}%")
                        ->orWhere('system_logs.details', 'LIKE', "%'{$p}%")
                        ->orWhere('system_logs.details', 'LIKE', "%pieza #{$p}%")
                        ->orWhere('system_logs.details', 'LIKE', "%pieza {$p}%");
                });
            }

            if ($request->filled('operador')) {
                $op = $request->input('operador');
                $query->where(function ($q) use ($op) {
                    $q->where('system_logs.user_matricula', '=', $op)
                        ->orWhere('users.nombre', 'LIKE', "%{$op}%")
                        ->orWhere('users.a_paterno', 'LIKE', "%{$op}%")
                        ->orWhere('system_logs.details', 'LIKE', "%{$op}%");
                });
            }

            $logs = $query->orderBy('system_logs.created_at', 'desc')
                ->limit(100)
                ->get()
                ->map(function ($log) {
                    $fullName = trim(($log->user_nombre ?? '').' '.($log->user_paterno ?? ''));
                    $details = (string) $log->details;

                    // Fallbacks para registros antiguos que no guardaron pieza/OT/clase
                    $pieza = $log->n_pieza;
                    if (! $pieza && preg_match("/pieza\\s+'?([^'\\s(]+)'?/u", $details, $m)) {
                        $pieza = $m[1];
                    }
                    $ot = $log->ot ?? $log->id_ot;
                    if (! $ot && preg_match('/OT:\\s*(\\d+)/', $details, $m)) {
                        $ot = $m[1];
                    }
                    $clase = $log->clase;
                    if (! $clase && preg_match('/Meta:\\s*(\\d+)/', $details, $m)) {
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
            Log::error('Error al obtener logs de piezas ocupadas: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'Error al cargar historial de auditoría.'], 500);
        }
    }
}
