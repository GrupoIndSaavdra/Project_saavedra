<?php

namespace App\Http\Controllers;

use App\Http\Requests\MoldingRequest;
use App\Models\CalidadMaquinadoDoc;
use App\Models\DibujoOtHistory;
use App\Models\FundicionHistory;
use App\Models\LiberacionModeloFundicion;
use App\Models\Moldura;
use App\Models\Orden_trabajo;
use App\Models\PreOrdenFundicion;
use App\Models\ScarModelo;
use App\Models\SystemLog;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\UnableToMoveFile;
use RuntimeException;

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

        [$renamedCount, $pending] = $this->renameMoldingFolders($moldingId, $oldName, $newName);

        $message = 'Moldura actualizada correctamente.';
        if ($renamedCount > 0) {
            $message .= " Se actualizaron {$renamedCount} carpetas asociadas en la documentación.";
        }

        $response = redirect()->to('editMolding')->with('success', $message);
        if ($pending !== []) {
            $response->with('warning', 'El nombre se guardó. Hay carpetas pendientes para '.implode(', ', $pending).'. Revisa el registro y vuelve a guardar el mismo nombre para reintentar.');
        }

        return $response;
    }

    private function renameMoldingFolders(int $moldingId, string $oldName, string $newName): array
    {
        $disk = Storage::disk('local');
        $renamedTotal = 0;
        $pending = [];
        $roots = [
            'DOCUMENTACION_GIS/DIBUJOS_FUNDICION' => 'fundicion',
            'FUNDICION_GIS' => 'fundicion',
            'DOCUMENTACION_GIS/ALMACEN_FUNDICION' => 'fundicion',
            'DOCUMENTACION_GIS/CALIDAD_FUNDICION' => 'fundicion',
            'DOCUMENTACION_GIS/DIBUJOS_MAQUINADOS' => 'dibujos',
            'DIBUJOS_GIS' => 'dibujos',
            'DOCUMENTACION_GIS/PROGRAMAS_MAQUINADOS' => 'cnc',
            'CALIDAD_MAQUINADOS/dibujos' => 'respaldo',
        ];

        $workOrderIds = Orden_trabajo::query()->where('id_moldura', '=', $moldingId, 'and')->pluck('id');
        foreach ($workOrderIds as $id) {
            $label = DibujosFundicionPdfController::normalizeOTName('OT '.$id.' - '.$newName);
            $pattern = '/^OT '.preg_quote($id, '/').'(?: - .+)?$/iu';
            $moved = [];

            try {
                if (preg_match('~[<>:"/|?*\x00-\x1f]~', $label) || str_contains($label, chr(92)) || str_ends_with($label, '.')) {
                    throw new RuntimeException('La etiqueta contiene caracteres inválidos para una carpeta documental.');
                }

                DB::transaction(function () use ($id, $label, $pattern, $roots, $disk, &$moved) {
                    Orden_trabajo::query()->lockForUpdate()->findOrFail($id);
                    $plan = [];
                    foreach ($roots as $root => $type) {
                        $folders = array_values(array_filter($disk->directories($root), function (string $folder) use ($pattern) {
                            $name = basename($folder);

                            return preg_match($pattern, $name) && ! preg_match('/_R\d+$/i', $name);
                        }));
                        if ($folders === []) {
                            continue;
                        }

                        $target = $root.'/'.$label;
                        if (count($folders) > 1) {
                            throw new RuntimeException("Varias carpetas para la misma OT en {$root}: ".implode(', ', $folders));
                        }

                        $source = $folders[0];
                        if ($source === $target) {
                            continue;
                        }

                        if ($disk->exists($target)) {
                            throw new RuntimeException("El destino ya existe: {$source} → {$target}.");
                        }

                        $plan[$source] = [$target, $type];
                    }

                    foreach ($plan as $source => [$target, $type]) {
                        $this->moveDocumentFolder($disk, $source, $target);
                        $moved[$source] = $target;

                        if ($disk->directoryExists($source) || ! $disk->directoryExists($target)) {
                            throw new RuntimeException("No se pudo verificar el movimiento: {$source} → {$target}.");
                        }

                        $this->updateDocumentReferences($source, $target, $type);
                    }
                });
                $renamedTotal += count($moved);
            } catch (\Throwable $error) {
                foreach (array_reverse($moved, true) as $source => $target) {
                    try {
                        $this->moveDocumentFolder($disk, $target, $source);
                    } catch (\Throwable $rollbackError) {
                        Log::critical('No se pudo devolver una carpeta documental a su ruta original.', [
                            'source' => $source,
                            'target' => $target,
                            'error' => $rollbackError->getMessage(),
                        ]);
                    }
                }

                $pending[] = "OT {$id}";
                Log::error('Sincronización de carpetas pendiente.', ['ot' => $id, 'error' => $error->getMessage()]);
            }
        }

        if ($renamedTotal > 0 && auth()->check()) {
            try {
                SystemLog::create([
                    'user_matricula' => auth()->user()->matricula ?? 'SISTEMA',
                    'action' => 'Renombrado de Moldura y Carpetas',
                    'details' => "Moldura ID {$moldingId}: '{$oldName}' → '{$newName}'. Se actualizaron {$renamedTotal} carpetas documentales asociadas.",
                ]);
            } catch (\Throwable $error) {
                Log::warning('No se pudo registrar el renombrado documental.', ['molding_id' => $moldingId, 'error' => $error->getMessage()]);
            }
        }

        return [$renamedTotal, $pending];
    }

    private function moveDocumentFolder(FilesystemAdapter $disk, string $source, string $target): void
    {
        if (! $disk->directoryExists($source) || $disk->exists($target)) {
            throw new RuntimeException("El origen no existe o el destino está ocupado: {$source} → {$target}.");
        }

        $warning = null;
        set_error_handler(static function (int $severity, string $message) use (&$warning): bool {
            $warning = $message;

            return false;
        }, E_WARNING);

        try {
            $disk->getDriver()->move($source, $target);
        } catch (UnableToMoveFile $error) {
            $reason = $warning ?? $error->getMessage();
            if (PHP_OS_FAMILY !== 'Windows' || ! $disk->directoryExists($source) || $disk->exists($target)) {
                throw new RuntimeException($reason, 0, $error);
            }
        } finally {
            restore_error_handler();
        }

        if (isset($reason)) {
            $this->copyAndRetireDocumentFolder($disk, $source, $target, $reason);
        }
    }

    private function copyAndRetireDocumentFolder(FilesystemAdapter $disk, string $source, string $target, string $reason): void
    {
        $driver = $disk->getDriver();
        $prefix = strlen($source) + 1;
        $directories = $disk->allDirectories($source);
        $files = $disk->allFiles($source);
        $copied = [];
        $removed = [];

        // Crear el destino exclusivamente, sin reutilizar una carpeta aparecida en paralelo.
        if (! @mkdir($disk->path($target))) {
            throw new RuntimeException("{$reason} No se pudo reservar el destino {$target}.");
        }

        try {
            foreach ($directories as $directory) {
                $driver->createDirectory($target.'/'.substr($directory, $prefix));
            }

            foreach ($files as $file) {
                $copy = $target.'/'.substr($file, $prefix);
                if ($disk->exists($copy)) {
                    throw new RuntimeException("Ya existe el destino {$copy}.");
                }
                $driver->copy($file, $copy);
                $copied[$file] = $copy;
            }

            $latestFiles = $disk->allFiles($source);
            $latestDirectories = $disk->allDirectories($source);
            if (array_diff($latestFiles, $files) || array_diff($files, $latestFiles)
                || array_diff($latestDirectories, $directories) || array_diff($directories, $latestDirectories)) {
                throw new RuntimeException('El contenido del origen cambió durante la copia.');
            }

            foreach ($copied as $file => $copy) {
                $hash = @hash_file('sha256', $disk->path($file));
                if ($hash === false || $hash !== @hash_file('sha256', $disk->path($copy))) {
                    throw new RuntimeException("No se pudo verificar la copia de {$file}.");
                }
            }

            foreach ($copied as $file => $copy) {
                $driver->delete($file);
                if ($disk->fileExists($file)) {
                    throw new RuntimeException("Windows no permitió retirar {$file}.");
                }
                $removed[$file] = $copy;
            }

            // rmdir solo elimina directorios vacíos, incluso si llegó un archivo nuevo.
            usort($directories, static fn (string $left, string $right) => strlen($right) <=> strlen($left));
            foreach ($directories as $directory) {
                if (! @rmdir($disk->path($directory))) {
                    throw new RuntimeException("Windows no permitió retirar {$directory}.");
                }
            }
            if (! @rmdir($disk->path($source))) {
                throw new RuntimeException("Windows no permitió retirar {$source}.");
            }
        } catch (\Throwable $error) {
            try {
                foreach ($directories as $directory) {
                    $driver->createDirectory($directory);
                }
                foreach ($removed as $file => $copy) {
                    if (! $disk->exists($file)) {
                        $driver->copy($copy, $file);
                    }
                    $hash = @hash_file('sha256', $disk->path($copy));
                    if ($hash === false || $hash !== @hash_file('sha256', $disk->path($file))) {
                        throw new RuntimeException("No se pudo verificar la restauración de {$file}.");
                    }
                }
            } catch (\Throwable $restoreError) {
                Log::critical('Se conservó la copia documental porque no se pudo restaurar el origen.', [
                    'source' => $source,
                    'target' => $target,
                    'error' => $restoreError->getMessage(),
                ]);

                throw new RuntimeException("{$reason} {$error->getMessage()} Se conservó la copia en {$target}.", 0, $error);
            }

            // Conservar las copias verificadas ante un fallo para revisión manual.
            throw new RuntimeException("{$reason} {$error->getMessage()} El origen se conservó y el destino quedó pendiente de revisión.", 0, $error);
        }
    }

    private function updateDocumentReferences(string $source, string $target, string $type): void
    {
        $old = basename($source);
        $new = basename($target);
        $models = match ($type) {
            'fundicion' => [FundicionHistory::class, LiberacionModeloFundicion::class, PreOrdenFundicion::class, ScarModelo::class],
            'dibujos' => [DibujoOtHistory::class],
            default => [],
        };

        foreach ($models as $model) {
            $model::query()->where('ot', '=', $old, 'and')->update(['ot' => $new]);
        }

        if ($type === 'respaldo') {
            $prefix = $source.'/';
            $docs = CalidadMaquinadoDoc::query()->dibujos()->where('ot', '=', $old, 'and')->get();
            foreach ($docs as $doc) {
                if (str_starts_with($doc->ruta_storage, $prefix)) {
                    $doc->update([
                        'ot' => $new,
                        'ruta_storage' => $target.'/'.substr($doc->ruta_storage, strlen($prefix)),
                    ]);
                }
            }
        }
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
