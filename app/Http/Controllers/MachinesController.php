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

    public function machinesOccupied()
    {
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

        return $occupiedMachines;
    }

    public function getOccupiedApi()
    {
        return response()->json(array_values($this->machinesOccupied()));
    }

    public function freeUp(Request $request)
    {
        $idMachine = $request->input('idMachine') ?? $request->route('idMachine');
        if (! $idMachine) {
            return redirect()->route('machinesOccupied');
        }

        DB::beginTransaction();
        try {
            $machine = Maquinas::query()->find($idMachine);
            if (! $machine) {
                DB::rollBack();

                return redirect()->route('machinesOccupied')->with(['error' => 'La máquina ya no se encuentra ocupada.']);
            }

            // Guardar datos para el log antes de eliminar
            $machineName = (string) $machine->maquina;
            $machineProcess = (string) $machine->proceso;
            $metaId = $machine->id_meta;

            // Desocupar la máquina
            $machine->delete();

            // Desocupar piezas en la meta si es que estaban ocupadas
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

            return redirect()->route('machinesOccupied')->with(['success' => "La máquina {$machineName} ha sido desocupada correctamente."]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error al desocupar máquina en MachinesController@freeUp: ' . $e->getMessage(), [
                'machine_id' => $idMachine,
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('machinesOccupied')->with(['error' => 'Ocurrió un error al desocupar la máquina: ' . $e->getMessage()]);
        }
    }
}
