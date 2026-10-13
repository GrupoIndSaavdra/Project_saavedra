<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\TratamientoTermico;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class TratamientoTermicoController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'id_clase' => 'required|exists:clases,id',
            'archivo' => 'required|mimes:pdf|max:10240',
            'descripcion' => 'required_if:cantidad,0|nullable|string|max:255',
            'cantidad' => 'required|integer|min:0',
            'id_ot' => 'required'
        ], [
            'descripcion.required_if' => 'Es obligatorio ingresar una descripción del motivo cuando se registra un tratamiento térmico con 0 piezas.',
        ]);

        $path = $request->file('archivo')->store('tratamientos_termicos', 'public');

        TratamientoTermico::create([
            'id_clase' => $request->id_clase,
            'archivo' => $path,
            'descripcion' => $request->descripcion,
            'cantidad' => $request->cantidad,
            'registrado_por' => auth()->user() ? auth()->user()->nombre . ' ' . auth()->user()->a_paterno : 'Sistema'
        ]);

        return back()->with('success', 'Tratamiento térmico registrado correctamente.');
    }

    public function download(int|string $id)
    {
        $tratamiento = TratamientoTermico::findOrFail($id);
        $path = storage_path('app/public/' . $tratamiento->archivo);
        if (!file_exists($path)) {
            abort(404);
        }
        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"'
        ]);
    }

    public function destroy(Request $request, int|string $id)
    {
        $password = $request->input('password');
        $authorized = false;

        // Si el usuario firmado es admin (1), master (3) o supervisor (6), está autorizado automáticamente
        if (auth()->check() && in_array(auth()->user()->perfil, [1, 3, 6])) {
            $authorized = true;
        } elseif ($password) {
            // Verificar si el password corresponde a algún admin (1), master (3) o supervisor (6)
            $users = User::whereIn('perfil', [1, 3, 6], 'and', false)->get();
            foreach ($users as $user) {
                if (Hash::check($password, $user->contrasena)) {
                    $authorized = true;
                    break;
                }
            }
        }

        if (!$authorized) {
            return back()->with('error', 'Contraseña incorrecta. Solo administradores o personal master pueden autorizar la eliminación de tratamientos.');
        }

        $tratamiento = TratamientoTermico::findOrFail($id);
        
        // Delete file
        if (Storage::disk('public')->exists($tratamiento->archivo)) {
            Storage::disk('public')->delete($tratamiento->archivo);
        }
        
        $tratamiento->delete();
        
        return back()->with('success', 'Tratamiento térmico eliminado.');
    }

    public function update(Request $request, int|string $id)
    {
        $request->validate([
            'descripcion' => 'required_if:cantidad,0|nullable|string|max:255',
            'cantidad'    => 'required|integer|min:0',
            'archivo'     => 'nullable|file|mimes:pdf|max:10240',
        ], [
            'descripcion.required_if' => 'Es obligatorio ingresar una descripción del motivo cuando se registra un tratamiento térmico con 0 piezas.',
        ]);

        $tratamiento = TratamientoTermico::findOrFail($id);
        $archivoPath = $tratamiento->archivo;

        if ($request->hasFile('archivo')) {
            // Delete old file from public disk
            if (Storage::disk('public')->exists($tratamiento->archivo)) {
                Storage::disk('public')->delete($tratamiento->archivo);
            }
            // Store new file
            $archivoPath = $request->file('archivo')->store('tratamientos_termicos', 'public');
        }

        $tratamiento->update([
            'descripcion' => $request->descripcion,
            'cantidad'    => $request->cantidad,
            'archivo'     => $archivoPath,
        ]);

        return back()->with('success', 'Tratamiento térmico actualizado correctamente.');
    }
}

