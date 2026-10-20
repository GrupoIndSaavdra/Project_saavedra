<?php

namespace App\Http\Controllers;

use App\Models\Maquinas;
use App\Models\Metas;
use App\Models\SystemLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $occupiedMachines = array();

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

            if ($meta && !empty($meta->id_usuario)) {
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

            $occupiedMachines[$key] = array(
                'id' => $machine->id,
                'process' => $machine->proceso,
                'machine' => $machine->maquina,
                'operator' => $operatorName,
                'operator_matricula' => $operatorMatricula,
                'id_meta' => $machine->id_meta,
            );
        }
        return $occupiedMachines;
    }
        /**
     * @param Request $request
     */
    public function freeUp(Request $request)
    {
        $machine = Maquinas::query()->find($request->idMachine);
        if ($machine) {
            // Guardar datos para el log antes de eliminar
            $machineName   = $machine->maquina;
            $machineProcess = $machine->proceso;
            $metaId        = $machine->id_meta;

            // Desocupar la maquina
            $machine->delete();
            // Desocupar piezas en la meta si es que estaban ocupadas
            $meta = Metas::query()->find($metaId);
            $modelProcessPieces = $this->processProductionController->get_ModelProcessPieces($meta->proceso);
            $occupiedPieces = $modelProcessPieces::query()->where('id_meta', $meta->id)->where('estado', 1)->get();
            if (count($occupiedPieces) > 0) {
                $processesAssemblies = ["Barreno Maniobra", "Soldadura", "Soldadura PTA", "Rectificado", "Asentado", "Barreno Profundidad", "Palomas", "Rebajes", "Grabado", "Operacion Equipo", "Embudo CM"];
                if (in_array($meta->proceso, $processesAssemblies)) {
                    foreach ($occupiedPieces as $piece) { // Si es un juego marcar como desocupado
                        $piece->delete();
                    }
                } else {
                    if (count($occupiedPieces) < 2) { // Si es una mitad marcar como desocupada
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

            // Registrar auditoría de desocupación
            SystemLog::create([
                'user_matricula' => Auth::check() ? Auth::user()->matricula : null,
                'action'         => 'Desocupación de Máquina',
                'details'        => "El administrador desocupó la máquina '{$machineName}' del proceso '{$machineProcess}' (Meta ID: {$metaId}).",
                'maquina'        => $machineName,
                'proceso'        => $machineProcess,
                'id_ot'          => $meta->id_ot ?? null,
                'ot'             => $meta->id_ot ?? null,
            ]);

            return redirect()->route('machinesOccupied')->with(['success' => 'La máquina ha sido desocupada correctamente.']);
        }
        return redirect()->route('machinesOccupied')->with(['error' => 'La máquina no está ocupada.']);
    }
}
