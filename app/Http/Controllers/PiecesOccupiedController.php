<?php

namespace App\Http\Controllers;

use App\Models\Clase;
use App\Models\Maquinas;
use App\Models\Metas;
use App\Models\Orden_trabajo;
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

        return view('pieces_views.piecesOccupied', compact('workOrders', 'processesList'));
    }

    /**
     * Obtiene las clases disponibles para una OT dada.
     */
    public function getClassesByOt(Request $request)
    {
        $otId = $request->query('ot');
        if (!$otId) {
            return response()->json(['classes' => []]);
        }

        $classes = Clase::query()
            ->select(['id', 'id_ot', 'nombre', 'piezas', 'finalizada'])
            ->where('id_ot', '=', $otId, 'and')
            ->orderBy('nombre')
            ->get();

        return response()->json(['classes' => $classes]);
    }

    /**
     * Consulta AJAX de piezas ocupadas filtradas por OT, Clase y Proceso.
     */
    public function getOccupiedPieces(Request $request)
    {
        $targetOt = $request->input('ot');
        $targetClaseId = $request->input('clase_id');
        $targetProcess = $request->input('proceso');

        $processesToScan = self::$processConfig;
        if (!empty($targetProcess) && isset(self::$processConfig[$targetProcess])) {
            $processesToScan = [$targetProcess => self::$processConfig[$targetProcess]];
        }

        $occupiedPieces = [];
        $totalOrphans = 0;
        $affectedOts = [];

        // Pre-cargar usuarios para mapear matricula -> nombre de operador sin N+1
        $usersMap = User::query()->select(['matricula', 'nombre', 'a_paterno', 'a_materno'])->get()->keyBy('matricula');
        // Pre-cargar máquinas activas actualmente
        $activeMachineMetaIds = Maquinas::query()->pluck('id_meta')->filter()->toArray();

        foreach ($processesToScan as $procKey => $cfg) {
            $pieceModel = $cfg['piece_model'];
            $parentModel = $cfg['parent_model'];

            if (!class_exists($pieceModel)) {
                continue;
            }

            // Buscar piezas en estado 1 (Ocupada)
            $query = $pieceModel::query()->where('estado', '=', 1, 'and');

            // Optimización de columnas comunes
            $rawPieces = $query->get();
            if ($rawPieces->isEmpty()) {
                continue;
            }

            // Extraer metas asociadas
            $metaIds = $rawPieces->pluck('id_meta')->filter()->unique()->toArray();
            $metasMap = empty($metaIds) ? collect() : Metas::query()->whereIn('id', $metaIds, 'and', false)->get()->keyBy('id');

            // Extraer procesos padres asociados
            $parentProcessIds = $rawPieces->pluck('id_proceso')->filter()->unique()->toArray();
            $parentsMap = empty($parentProcessIds) ? collect() : $parentModel::query()->whereIn('id', $parentProcessIds, 'and', false)->get()->keyBy('id');

            foreach ($rawPieces as $piece) {
                $meta = isset($piece->id_meta) ? $metasMap->get($piece->id_meta) : null;
                $parent = isset($piece->id_proceso) ? $parentsMap->get($piece->id_proceso) : null;

                // Determinar OT
                $ot = $meta->id_ot ?? $parent->id_ot ?? null;
                if (!$ot && $parent && isset($parent->id_proceso)) {
                    // Tratar de extraer OT del string del proceso (ej. Cepillado_Bombillo_6814)
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
                if (!empty($targetClaseId)) {
                    if ((string) $claseId !== (string) $targetClaseId && (string) $claseNombre !== (string) $targetClaseId) {
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

                // Identificar si es huérfana (la máquina ya fue liberada o la meta no existe)
                $isOrphan = false;
                if (!$meta || !in_array($meta->id, $activeMachineMetaIds)) {
                    $isOrphan = true;
                    $totalOrphans++;
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
                ];
            }
        }

        return response()->json([
            'success' => true,
            'summary' => [
                'total_occupied' => count($occupiedPieces),
                'total_orphans' => $totalOrphans,
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
            if (!$piece) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Pieza no encontrada.'], 404);
            }

            $nPieza = $piece->n_pieza ?? $piece->n_juego ?? "#{$pieceId}";
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

            // Si es proceso inicial (Cepillado o Candado), eliminar el registro desbloquea el juego de inmediato
            if ($isInitial) {
                $piece->delete();
                $actionDetail = "Se eliminó el registro de ocupación de la pieza '{$nPieza}' en el proceso inicial '{$proceso}'" . ($ot && $ot !== 'N/A' ? " (OT: {$ot})" : '') . " para desbloquear su maquinado.";
            } else {
                // En otros procesos, pasar a estado 0 o eliminar según si tiene datos medidos
                $piece->estado = 0;
                $piece->id_meta = null;
                $piece->save();
                $actionDetail = "Se cambió el estado de la pieza '{$nPieza}' a 0 (desocupada) en el proceso '{$proceso}'" . ($ot && $ot !== 'N/A' ? " (OT: {$ot})" : '') . ".";
            }

            SystemLog::create([
                'user_matricula' => Auth::check() ? Auth::user()->matricula : null,
                'action' => 'Liberación de Pieza Ocupada',
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
                'message' => "La pieza '{$nPieza}' fue liberada correctamente y ahora está disponible para maquinar.",
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
