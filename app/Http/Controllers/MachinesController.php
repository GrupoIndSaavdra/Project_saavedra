<?php

namespace App\Http\Controllers;

use App\Models\Maquinas;
use App\Models\Metas;
use App\Models\SystemLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MachinesController extends Controller
{
    /** @var \App\Http\Controllers\ProcessProductionController */
    protected $processProductionController;

    public function __construct()
    {
        $this->middleware('auth');
        $this->processProductionController = new ProcessProductionController();
    }

    public function show()
    {
        $machines = $this->machinesOccupied();

        return view('machines_views.machinesOccupied', compact('machines'));
    }

    /**
     * Limpia automáticamente máquinas ocupadas que correspondan a turnos/días vencidos
     * o registros huérfanos sin meta válida en base de datos.
     */
    public static function cleanupExpiredMachines(): void
    {
        try {
            $today = date('Y-m-d');
            $yesterday = date('Y-m-d', strtotime('-1 day'));
            $nowTime = date('H:i:s');

            $allMachines = Maquinas::all();
            if ($allMachines->isEmpty()) {
                return;
            }

            $metaIds = $allMachines->pluck('id_meta')->filter()->unique();
            $metas = Metas::whereIn('id', $metaIds)->get()->keyBy('id');

            $idsToDelete = [];

            foreach ($allMachines as $mach) {
                // 1. Huérfano sin id_meta o cuya meta ya no existe en la base de datos
                if (empty($mach->id_meta) || ! $metas->has($mach->id_meta)) {
                    $idsToDelete[] = $mach->id;
                    continue;
                }

                $meta = $metas->get($mach->id_meta);
                $metaDate = (string) $meta->fecha;

                // 2. Si la meta es de antes de ayer, está 100% vencida
                if ($metaDate < $yesterday) {
                    $idsToDelete[] = $mach->id;
                    continue;
                }

                // 3. Si la meta es de ayer:
                if ($metaDate === $yesterday) {
                    // Verificar si es turno nocturno que cruza medianoche (h_inicio > h_termino)
                    $isNightShift = $meta->h_inicio && $meta->h_termino && ($meta->h_inicio > $meta->h_termino);
                    if (! $isNightShift) {
                        // Turno diurno normal de ayer: vencido
                        $idsToDelete[] = $mach->id;
                    } else {
                        // Turno nocturno: vence 2 horas después de h_termino en el día de hoy
                        $graceEnd = date('H:i:s', strtotime($meta->h_termino . ' +2 hours'));
                        if ($nowTime > $graceEnd && $nowTime > '08:00:00') {
                            $idsToDelete[] = $mach->id;
                        }
                    }
                }
            }

            if (! empty($idsToDelete)) {
                Maquinas::query()->whereIn('id', $idsToDelete)->delete();
                Log::info('[AUTO-CLEANUP MAQUINAS] Se liberaron '.count($idsToDelete).' máquinas ocupadas de turnos vencidos.');
            }
        } catch (\Throwable $e) {
            Log::error('[AUTO-CLEANUP MAQUINAS] Error limpiando máquinas vencidas: '.$e->getMessage());
        }
    }

    public function machinesOccupied()
    {
        // Limpiar automáticamente máquinas de turnos pasados antes de listar
        self::cleanupExpiredMachines();

        $machines = Maquinas::all();
        $occupiedMachines = [];

        // Obtener las metas relacionadas para identificar al operador asignado
        $metaIds = $machines->pluck('id_meta')->filter()->unique();
        $metas = Metas::whereIn('id', $metaIds)->get()->keyBy('id');

        // Obtener los usuarios correspondientes por id_usuario (matricula)
        $matriculas = $metas->pluck('id_usuario')->filter()->unique();
        $users = User::whereIn('matricula', $matriculas)->get()->keyBy('matricula');

        foreach ($machines as $key => $machine) {
            $meta = $metas->get($machine->id_meta);
            $operatorName = 'Sin asignar';
            $operatorMatricula = '';

            if ($meta && ! empty($meta->id_usuario)) {
                $user = $users->get($meta->id_usuario);
                if ($user) {
                    $fullName = trim("{$user->nombre} {$user->a_paterno} {$user->a_materno}");
                    $operatorName = $fullName !== '' ? $fullName : trim("{$user->nombre} {$user->a_paterno}");
                    $operatorMatricula = (string) $user->matricula;
                } else {
                    $operatorName = "Matrícula {$meta->id_usuario}";
                    $operatorMatricula = (string) $meta->id_usuario;
                }
            }

            $occupiedMachines[$key] = [
                'id' => $machine->id,
                'process' => $machine->proceso,
                'machine' => (string) $machine->maquina,
                'proceso' => $machine->proceso,
                'maquina' => (string) $machine->maquina,
                'operator' => $operatorName,
                'operator_matricula' => $operatorMatricula,
                'id_meta' => $machine->id_meta,
            ];
        }

        return array_values($occupiedMachines);
    }

    public function getOccupiedApi()
    {
        return response()->json($this->machinesOccupied());
    }

    public function freeUp(Request $request)
    {
        $idMachine = $request->input('idMachine') ?? $request->route('idMachine');
        $machineNameInput = $request->input('machine') ?? $request->input('maquina');

        if (! $idMachine && ! $machineNameInput) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'No se especificó la máquina a desocupar.'], 400);
            }

            return redirect()->route('machinesOccupied');
        }

        DB::beginTransaction();
        try {
            $machine = null;
            if ($idMachine) {
                $machine = Maquinas::query()->find($idMachine);
            }
            if (! $machine && $machineNameInput) {
                $machine = Maquinas::query()->where('maquina', (string) $machineNameInput)->first();
            }

            if (! $machine) {
                DB::rollBack();

                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'La máquina ya no se encuentra ocupada.']);
                }

                return redirect()->route('machinesOccupied')->with(['error' => 'La máquina ya no se encuentra ocupada.']);
            }

            // Guardar datos para el log antes de eliminar
            $machineName = (string) $machine->maquina;
            $machineProcess = (string) $machine->proceso;
            $metaId = $machine->id_meta;

            // Desocupar la máquina y TODOS los registros duplicados de esta misma máquina
            Maquinas::query()->where('maquina', $machineName)->delete();
            $normalizedName = str_replace(' y ', '_', $machineName);
            $spacedName = str_replace('_', ' y ', $machineName);
            Maquinas::query()->whereIn('maquina', [$normalizedName, $spacedName])->delete();

            // Si es pareada (ej: 1_2 o 1 y 2), desocupar también registros individuales
            $pairedMap = [
                '1_2' => ['1', '2'], '1 y 2' => ['1', '2'],
                '5_6' => ['5', '6'], '5 y 6' => ['5', '6'],
                '25_26' => ['25', '26'], '25 y 26' => ['25', '26'],
                '27_28' => ['27', '28'], '27 y 28' => ['27', '28'],
            ];

            if (isset($pairedMap[$machineName])) {
                Maquinas::query()->whereIn('maquina', $pairedMap[$machineName])->delete();
            }
            if (isset($pairedMap[$normalizedName])) {
                Maquinas::query()->whereIn('maquina', $pairedMap[$normalizedName])->delete();
            }

            // Si es una máquina individual que pertenece a un par (ej: 1 o 2), liberar también la dual '1_2'
            $individualToPairMap = [
                '1' => ['1_2', '1 y 2'], '2' => ['1_2', '1 y 2'],
                '5' => ['5_6', '5 y 6'], '6' => ['5_6', '5 y 6'],
                '25' => ['25_26', '25 y 26'], '26' => ['25_26', '25 y 26'],
                '27' => ['27_28', '27 y 28'], '28' => ['27_28', '27 y 28'],
            ];
            if (isset($individualToPairMap[$machineName])) {
                Maquinas::query()->whereIn('maquina', (array) $individualToPairMap[$machineName])->delete();
            }

            // Desocupar piezas en la meta si es que estaban ocupadas
            try {
                $meta = $metaId ? Metas::query()->find($metaId) : null;
                if ($meta && ! empty($meta->proceso)) {
                    $processToUse = str_contains((string) $meta->proceso, 'Operacion Equipo') ? 'Operacion Equipo' : $meta->proceso;
                    $modelProcessPieces = $this->processProductionController->get_ModelProcessPieces($processToUse);

                    if ($modelProcessPieces && class_exists($modelProcessPieces)) {
                        $occupiedPieces = $modelProcessPieces::query()->where('id_meta', $meta->id)->where('estado', 1)->get();
                        if ($occupiedPieces->count() > 0) {
                            $processesAssemblies = ['Barreno Maniobra', 'Soldadura', 'Soldadura PTA', 'Rectificado', 'Asentado', 'Barreno Profundidad', 'Palomas', 'Rebajes', 'Grabado', 'Operacion Equipo', 'Embudo CM'];
                            if (in_array($processToUse, $processesAssemblies) || str_contains((string) $processToUse, 'Operacion Equipo')) {
                                foreach ($occupiedPieces as $piece) { // Si es un juego marcar como desocupado
                                    $piece->delete();
                                }
                            } else {
                                if ($occupiedPieces->count() < 2) { // Si es una mitad marcar como desocupada
                                    foreach ($occupiedPieces as $piece) {
                                        $piece->estado = 0;
                                        $piece->save();
                                    }
                                } else {
                                    foreach ($occupiedPieces as $piece) { // Si son dos mitades eliminarlas
                                        $piece->delete();
                                    }
                                }
                            }
                        }
                    }
                }
            } catch (\Throwable $pieceEx) {
                Log::warning("Aviso no fatal al desocupar piezas de meta {$metaId} en máquina {$machineName}: " . $pieceEx->getMessage());
            }

            // Registrar auditoría de desocupación
            SystemLog::create([
                'user_matricula' => Auth::check() ? Auth::user()->matricula : null,
                'action' => 'Desocupación de Máquina',
                'details' => "El usuario desocupó la máquina '{$machineName}' del proceso '{$machineProcess}'" . ($metaId ? " (Meta ID: {$metaId})." : "."),
                'maquina' => $machineName,
                'proceso' => $machineProcess,
                'id_ot' => $meta->id_ot ?? null,
                'ot' => $meta->id_ot ?? null,
            ]);

            DB::commit();

            $displayMachineName = str_replace('_', ' y ', $machineName);
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "La máquina {$displayMachineName} ha sido desocupada correctamente.",
                    'machine' => $machineName,
                ]);
            }

            return redirect()->route('machinesOccupied')->with(['success' => "La máquina {$displayMachineName} ha sido desocupada correctamente."]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error al desocupar máquina en MachinesController@freeUp: ' . $e->getMessage(), [
                'machine_id' => $idMachine,
                'trace' => $e->getTraceAsString(),
            ]);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Ocurrió un error al desocupar la máquina: ' . $e->getMessage()], 500);
            }

            return redirect()->route('machinesOccupied')->with(['error' => 'Ocurrió un error al desocupar la máquina: ' . $e->getMessage()]);
        }
    }
}
