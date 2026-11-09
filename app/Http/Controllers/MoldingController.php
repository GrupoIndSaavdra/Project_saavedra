<?php

namespace App\Http\Controllers;

use App\Http\Requests\MoldingRequest;
use App\Models\FundicionHistory;
use App\Models\LiberacionModeloFundicion;
use App\Models\Moldura;
use App\Models\Orden_trabajo;
use App\Models\PreOrdenFundicion;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MoldingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function create()
    {
        return view('moldings_views.create_molding');
    }

    public function store(MoldingRequest $request)
    {
        $moldura = Moldura::create($request->all());

        return redirect()->to('createMolding')->with('success', 'Moldura registrada correctamente.');
    }

    public function edit()
    {
        $moldings = Moldura::all();

        return view('moldings_views.edit_molding', compact('moldings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'moldingId' => 'required|exists:molduras,id',
            'moldingName' => 'required|string|max:190',
        ]);

        $moldingId = (int) $request->input('moldingId');
        $newName = trim((string) $request->input('moldingName'));

        $molding = Moldura::query()->findOrFail($moldingId);
        $oldName = trim((string) $molding->nombre);

        $molding->nombre = $newName;
        $molding->save();

        $renamedCount = 0;
        if ($oldName !== '' && $oldName !== $newName) {
            $renamedCount = $this->renameMoldingFolders((int) $molding->id, $oldName, $newName);
        }

        $msg = 'Moldura actualizada correctamente.';
        if ($renamedCount > 0) {
            $msg .= " Se actualizaron {$renamedCount} carpetas asociadas en la documentación.";
        }

        return redirect()->to('editMolding')->with('success', $msg);
    }

    /**
     * Renombra y sincroniza automáticamente las carpetas de las OTs vinculadas a la moldura en todos los módulos.
     */
    public function renameMoldingFolders(int $moldingId, string $oldName, string $newName): int
    {
        $workOrders = Orden_trabajo::query()->where('id_moldura', $moldingId)->get();
        if ($workOrders->isEmpty()) {
            return 0;
        }

        $disk = Storage::disk('local');
        $renamedTotal = 0;

        $baseRoots = [
            'DOCUMENTACION_GIS/DIBUJOS_FUNDICION',
            'DOCUMENTACION_GIS/DIBUJOS_MAQUINADOS',
            'DOCUMENTACION_GIS/PROGRAMAS_MAQUINADOS',
            'DOCUMENTACION_GIS/ALMACEN_FUNDICION',
            'DOCUMENTACION_GIS/CALIDAD_FUNDICION',
            'DOCUMENTACION_GIS/AYUDAS_FUNDICION',
            'DOCUMENTACION_GIS/AYUDAS_MAQUINADOS',
            'DOCUMENTACION_GIS/MANUALES_PROCESOS',
            'DOCUMENTACION_GIS/INACTIVAS',
            'DOCUMENTACION_GIS',
            'DIBUJOS_GIS',
            'AYUDAS_GIS',
            'MANUALES_GIS',
            'CALIDAD_MAQUINADOS/dibujos',
            'CALIDAD_MAQUINADOS/ayudas',
        ];

        foreach ($workOrders as $ot) {
            $otId = (string) $ot->id;
            $newOtFolder = 'OT '.$otId.' - '.mb_strtoupper(preg_replace('/\s+/', ' ', trim($newName)), 'UTF-8');
            $otPattern = '/^OT[ _-]?'.preg_quote($otId, '/').'(?:[ _-].*)?$/iu';

            foreach ($baseRoots as $root) {
                if (! $disk->exists($root) && ! $disk->directoryExists($root)) {
                    continue;
                }

                // 1. Revisar subdirectorios directos de $root
                $dirs = $disk->directories($root);
                foreach ($dirs as $dirPath) {
                    $base = basename(str_replace('\\', '/', $dirPath));
                    if (strtoupper($base) === 'INACTIVAS') {
                        continue;
                    }

                    $matched = false;
                    if (preg_match($otPattern, $base)) {
                        $matched = true;
                    } elseif ($oldName !== '' && (strcasecmp($base, $oldName) === 0 || strcasecmp($base, 'OT '.$otId.' - '.$oldName) === 0)) {
                        $matched = true;
                    }

                    if ($matched) {
                        $targetPath = $root.'/'.$newOtFolder;
                        if ($base !== $newOtFolder) {
                            $this->moveOrMergeDirectory($dirPath, $targetPath);
                            $renamedTotal++;
                        }
                    }

                    // 2. Revisar un segundo nivel (ej: subcarpetas por clase o proceso)
                    $subDirs = $disk->directories($dirPath);
                    foreach ($subDirs as $subPath) {
                        $subBase = basename(str_replace('\\', '/', $subPath));
                        $subMatched = false;
                        if (preg_match($otPattern, $subBase)) {
                            $subMatched = true;
                        } elseif ($oldName !== '' && (strcasecmp($subBase, $oldName) === 0 || strcasecmp($subBase, 'OT '.$otId.' - '.$oldName) === 0)) {
                            $subMatched = true;
                        }

                        if ($subMatched) {
                            $subTargetPath = $dirPath.'/'.$newOtFolder;
                            if ($subBase !== $newOtFolder) {
                                $this->moveOrMergeDirectory($subPath, $subTargetPath);
                                $renamedTotal++;
                            }
                        }
                    }
                }
            }

            // Actualizar registros de base de datos que referencian el nombre de la OT
            try {
                FundicionHistory::query()
                    ->where('ot', 'LIKE', "OT {$otId} - %")
                    ->orWhere('ot', '=', "OT {$otId}")
                    ->update(['ot' => $newOtFolder]);

                LiberacionModeloFundicion::query()
                    ->where('ot', 'LIKE', "OT {$otId} - %")
                    ->orWhere('ot', '=', "OT {$otId}")
                    ->update(['ot' => $newOtFolder]);

                PreOrdenFundicion::query()
                    ->where('ot', 'LIKE', "OT {$otId} - %")
                    ->orWhere('ot', '=', "OT {$otId}")
                    ->update(['ot' => $newOtFolder]);
            } catch (\Throwable $e) {
                Log::warning("Error actualizando tablas de fundición para OT {$otId}: ".$e->getMessage());
            }
        }

        if ($renamedTotal > 0 && auth()->check()) {
            try {
                SystemLog::create([
                    'user_matricula' => auth()->user()->matricula ?? 'SISTEMA',
                    'action' => 'Renombrado de Moldura y Carpetas',
                    'details' => "Se renombró la moldura ID {$moldingId} de '{$oldName}' a '{$newName}'. Se actualizaron {$renamedTotal} carpetas documentales asociadas.",
                ]);
            } catch (\Throwable $e) {
                // Silencioso
            }
        }

        return $renamedTotal;
    }

    /**
     * Mueve o combina recursivamente una carpeta origen a una carpeta destino.
     */
    private function moveOrMergeDirectory(string $sourcePath, string $targetPath): void
    {
        $disk = Storage::disk('local');
        if (! $disk->exists($sourcePath)) {
            return;
        }

        if (! $disk->exists($targetPath)) {
            $disk->move($sourcePath, $targetPath);
            return;
        }

        // Si la carpeta destino ya existe, mover los archivos recursivamente
        foreach ($disk->allFiles($sourcePath) as $file) {
            $relPath = substr($file, strlen($sourcePath) + 1);
            $destination = $targetPath.'/'.$relPath;
            $destDir = dirname($destination);
            if (! $disk->exists($destDir)) {
                $disk->makeDirectory($destDir);
            }
            if (! $disk->exists($destination)) {
                $disk->move($file, $destination);
            } else {
                $disk->delete($file);
            }
        }

        $disk->deleteDirectory($sourcePath);
    }

    /**
     * @param  mixed  $moldingId
     */
    public function destroy($moldingId)
    {
        $workOrder = Orden_trabajo::query()->where('id_moldura', $moldingId)->first();
        if (! $workOrder) {
            $molding = Moldura::query()->find($moldingId);
            if ($molding) {
                $molding->delete();

                return redirect()->to('editMolding')->with('success', 'Moldura eliminada correctamente.');
            } else {
                return redirect()->to('editMolding')->with('error', 'Moldura no encontrada.');
            }
        } else {
            return redirect()->to('editMolding')->with('error', 'No se puede eliminar la moldura porque está asociada a una orden de trabajo.');
        }
    }
}
