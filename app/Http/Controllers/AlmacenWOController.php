<?php

namespace App\Http\Controllers;

use App\Models\RemisionOt;
use App\Models\ParcialidadOt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AlmacenWOController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Sube un archivo de remisión (PDF/imagen) vinculado a una OT y Clase.
     */
    public function storeRemision(Request $request)
    {
        $request->validate([
            'id_ot'      => 'required|string',
            'id_clase'   => 'required|integer',
            'archivo'    => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'descripcion'=> 'nullable|string|max:255',
        ]);

        $file = $request->file('archivo');
        
        $otReal = \App\Models\Orden_trabajo::find($request->id_ot);
        $molding = $otReal ? \App\Models\Moldura::find($otReal->id_moldura) : null;
        $claseReal = \App\Models\Clase::find($request->id_clase);

        $otFolderName = 'OT ' . $request->id_ot;
        if ($molding && $molding->nombre) {
            $otFolderName .= ' - ' . $molding->nombre;
        }

        $claseFolderName = $claseReal ? $claseReal->nombre : 'Clase_' . $request->id_clase;

        $folder = 'DOCUMENTACION_GIS/REMISIONES/' . $otFolderName . '/' . $claseFolderName;
        $otNameClean = $molding && $molding->nombre ? $molding->nombre : '';
        $cleanOtName = str_replace(['/', '\\', ' ', ':', '*', '?', '"', '<', '>', '|'], '_', $otNameClean);
        $cleanClaseName = str_replace(['/', '\\', ' ', ':', '*', '?', '"', '<', '>', '|'], '_', $claseFolderName);
        $cleanOriginalName = str_replace(['/', '\\', ' ', ':', '*', '?', '"', '<', '>', '|'], '_', $file->getClientOriginalName());

        $filename = 'OT_' . $request->id_ot . '_' . $cleanClaseName . '_Remision_' . ($cleanOtName ? $cleanOtName . '_' : '') . $cleanOriginalName;
        $path = $file->storeAs($folder, $filename);

        RemisionOt::create([
            'id_ot'       => $request->id_ot,
            'id_clase'    => $request->id_clase,
            'filename'    => $filename,
            'path'        => $path,
            'descripcion' => $request->descripcion,
            'uploaded_by' => auth()->user()->matricula ?? auth()->user()->name,
        ]);

        return back()->with('success', 'Remisión subida correctamente.');
    }

    /**
     * Elimina un archivo de remisión.
     */
    public function destroyRemision($id)
    {
        $remision = RemisionOt::findOrFail($id);
        // No borramos la fila de la base de datos para no desvincular las parcialidades ya registradas.
        // Solo la ocultamos del pool de remisiones activas.
        $remision->update(['visible' => 0]);

        return back()->with('success', 'Remisión eliminada.');
    }

    /**
     * Sirve el archivo de remisión protegido.
     */
    public function serveRemision($id)
    {
        $remision = RemisionOt::findOrFail($id);

        if (!Storage::disk('local')->exists($remision->path)) {
            abort(404, 'Archivo no encontrado.');
        }

        $fullPath = storage_path('app/' . $remision->path);
        $mime = Storage::disk('local')->mimeType($remision->path);

        return response()->file($fullPath, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . $remision->filename . '"'
        ]);
    }

    /**
     * Registra una parcialidad (entrega parcial) vinculada a una OT y Clase.
     */
    public function storeParcialidad(Request $request)
    {
        $request->validate([
            'id_ot'           => 'required|string',
            'id_clase'        => 'required|integer',
            'archivo'         => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'cantidad'        => 'required|integer|min:1',
            'descripcion'     => 'nullable|string|max:255',
            'fecha_recepcion' => 'required|date',
        ]);

        $file = $request->file('archivo');

        $otReal = \App\Models\Orden_trabajo::find($request->id_ot);
        $molding = $otReal ? \App\Models\Moldura::find($otReal->id_moldura) : null;
        $claseReal = \App\Models\Clase::find($request->id_clase);
        if ($claseReal) {
            $currentSum = ParcialidadOt::where('id_clase', $request->id_clase)->sum('cantidad');
            if ($currentSum + $request->cantidad > $claseReal->piezas) {
                return back()->with('error', "No se pueden recibir más piezas de las que hay en Consignación ({$claseReal->piezas}). Actualmente recibidas: {$currentSum}.");
            }
        }

        $otFolderName = 'OT ' . $request->id_ot;
        if ($molding && $molding->nombre) {
            $otFolderName .= ' - ' . $molding->nombre;
        }

        $claseFolderName = $claseReal ? $claseReal->nombre : 'Clase_' . $request->id_clase;

        $folder = 'DOCUMENTACION_GIS/REMISIONES/' . $otFolderName . '/' . $claseFolderName;

        $otNameClean = $molding && $molding->nombre ? $molding->nombre : '';
        $cleanOtName = str_replace(['/', '\\', ' ', ':', '*', '?', '"', '<', '>', '|'], '_', $otNameClean);
        $cleanClaseName = str_replace(['/', '\\', ' ', ':', '*', '?', '"', '<', '>', '|'], '_', $claseFolderName);
        $cleanOriginalName = str_replace(['/', '\\', ' ', ':', '*', '?', '"', '<', '>', '|'], '_', $file->getClientOriginalName());

        $filename = 'OT_' . $request->id_ot . '_' . $cleanClaseName . '_Remision_' . ($cleanOtName ? $cleanOtName . '_' : '') . $cleanOriginalName;
        $path = $file->storeAs($folder, $filename, 'local');

        $remision = RemisionOt::create([
            'id_ot'       => $request->id_ot,
            'id_clase'    => $request->id_clase,
            'filename'    => $filename,
            'path'        => $path,
            'descripcion' => 'Adjunto a parcialidad',
            'uploaded_by' => auth()->user()->matricula ?? auth()->user()->name,
            'visible'     => 0,
        ]);

        ParcialidadOt::create([
            'id_ot'           => $request->id_ot,
            'id_clase'        => $request->id_clase,
            'id_remision'     => $remision->id,
            'cantidad'        => $request->cantidad,
            'descripcion'     => $request->descripcion,
            'fecha_recepcion' => $request->fecha_recepcion,
            'registrado_por'  => auth()->user()->matricula ?? auth()->user()->name,
        ]);

        return back()->with('success', 'Parcialidad y remisión registradas correctamente.');
    }

    public function destroyParcialidad(Request $request, $id)
    {
        $password = $request->input('password');
        $authorized = false;

        // Si el usuario firmado es admin (1) o master (3), está autorizado automáticamente
        if (auth()->check() && in_array(auth()->user()->perfil, [1, 3])) {
            $authorized = true;
        } elseif ($password) {
            // Verificar si el password corresponde a algún admin (1) o master (3)
            $users = \App\Models\User::whereIn('perfil', [1, 3])->get();
            foreach ($users as $user) {
                if (\Illuminate\Support\Facades\Hash::check($password, $user->contrasena)) {
                    $authorized = true;
                    break;
                }
            }
        }

        if (!$authorized) {
            return back()->with('error', 'Contraseña incorrecta. Solo administradores o personal master pueden autorizar la eliminación de parcialidades.');
        }

        $parcialidad = ParcialidadOt::findOrFail($id);
        $parcialidad->delete();

        return back()->with('success', 'Parcialidad eliminada correctamente.');
    }

    /**
     * Actualiza una parcialidad registrada.
     */
    public function updateParcialidad(Request $request, $id)
    {
        $request->validate([
            'cantidad'        => 'required|integer|min:1',
            'descripcion'     => 'nullable|string|max:255',
            'fecha_recepcion' => 'required|date',
            'archivo'         => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $parcialidad = ParcialidadOt::findOrFail($id);
        
        $claseReal = \App\Models\Clase::find($parcialidad->id_clase);
        if ($claseReal) {
            $currentSum = ParcialidadOt::where('id_clase', $parcialidad->id_clase)
                ->where('id', '!=', $id)
                ->sum('cantidad');
            if ($currentSum + $request->cantidad > $claseReal->piezas) {
                return response()->json([
                    'message' => "No se pueden recibir más piezas de las que hay en Consignación ({$claseReal->piezas}). Las otras parcialidades suman {$currentSum}."
                ], 422);
            }
        }

        $id_remision = $parcialidad->id_remision;

        if ($request->hasFile('archivo')) {
            $file = $request->file('archivo');
            
            $otReal = \App\Models\Orden_trabajo::find($parcialidad->id_ot);
            $molding = $otReal ? \App\Models\Moldura::find($otReal->id_moldura) : null;
            $claseReal = \App\Models\Clase::find($parcialidad->id_clase);

            $otFolderName = 'OT ' . $parcialidad->id_ot;
            if ($molding && $molding->nombre) {
                $otFolderName .= ' - ' . $molding->nombre;
            }

            $claseFolderName = $claseReal ? $claseReal->nombre : 'Clase_' . $parcialidad->id_clase;
            $folder = 'DOCUMENTACION_GIS/REMISIONES/' . $otFolderName . '/' . $claseFolderName;

            $otNameClean = $molding && $molding->nombre ? $molding->nombre : '';
            $cleanOtName = str_replace(['/', '\\', ' ', ':', '*', '?', '"', '<', '>', '|'], '_', $otNameClean);
            $cleanClaseName = str_replace(['/', '\\', ' ', ':', '*', '?', '"', '<', '>', '|'], '_', $claseFolderName);
            $cleanOriginalName = str_replace(['/', '\\', ' ', ':', '*', '?', '"', '<', '>', '|'], '_', $file->getClientOriginalName());

            $filename = 'OT_' . $parcialidad->id_ot . '_' . $cleanClaseName . '_Remision_' . ($cleanOtName ? $cleanOtName . '_' : '') . $cleanOriginalName;
            $path = $file->storeAs($folder, $filename, 'local');

            $remision = RemisionOt::create([
                'id_ot'       => $parcialidad->id_ot,
                'id_clase'    => $parcialidad->id_clase,
                'filename'    => $filename,
                'path'        => $path,
                'descripcion' => 'Reemplazo adjunto a parcialidad',
                'uploaded_by' => auth()->user()->matricula ?? auth()->user()->name,
                'visible'     => 0,
            ]);

            $id_remision = $remision->id;
        }

        $parcialidad->update([
            'cantidad'        => $request->cantidad,
            'descripcion'     => $request->descripcion,
            'fecha_recepcion' => $request->fecha_recepcion,
            'id_remision'     => $id_remision,
        ]);

        return back()->with('success', 'Parcialidad actualizada.');
    }
}
