<?php

namespace App\Http\Controllers;

use App\Models\SoldaduraBoteIndividual;
use App\Models\SoldaduraLiberacion;
use App\Models\User;
use Illuminate\Http\Request;

class LiberarQRPlantaController extends Controller
{
    public function index()
    {
        $operadores = User::where('perfil', '2')->get();
        $almacenistas = User::where('perfil', '5')->get();
        return view('trackingSoldadura_views.liberarQRPlanta', compact('operadores', 'almacenistas'));
    }

    public function escanear(Request $request)
    {
        $request->validate([
            'qr_content' => 'required|string',
        ]);

        try {
            $qrData = json_decode($request->qr_content, true);
            
            if (!$qrData || $qrData['tipo'] !== 'bote_individual') {
                return back()->withErrors(['qr_content' => 'QR no válido o no es de un bote individual']);
            }

            $bote = SoldaduraBoteIndividual::where('id_unico', $qrData['id_unico'])->first();
            
            if (!$bote) {
                return back()->withErrors(['qr_content' => 'Bote no encontrado']);
            }

            if ($bote->estado !== 'en_camino') {
                return back()->withErrors(['qr_content' => 'Este bote ya no está en camino']);
            }

            // Actualizar estado del bote
            $bote->update(['estado' => 'en_planta']);

            $operadores = User::where('perfil', '2')->get();
            $almacenistas = User::where('perfil', '5')->get();
            
            return view('trackingSoldadura_views.liberarQRPlanta', compact('bote', 'operadores', 'almacenistas'));
            
        } catch (\Exception $e) {
            return back()->withErrors(['qr_content' => 'Error al procesar el QR']);
        }
    }

    public function liberar(Request $request)
    {
        $request->validate([
            'bote_id' => 'required|exists:soldadura_botes_individuales,id',
            'id_operador' => 'required|exists:users,id',
            'id_liberador' => 'required|exists:users,id',
        ]);

        $bote = SoldaduraBoteIndividual::findOrFail($request->bote_id);
        $operador = User::findOrFail($request->id_operador);
        $liberador = User::findOrFail($request->id_liberador);

        $idUnicoLiberacion = SoldaduraLiberacion::generarIdUnico($bote->id_unico, $operador->matricula);

        $liberacion = SoldaduraLiberacion::create([
            'id_unico' => $idUnicoLiberacion,
            'bote_id' => $bote->id,
            'id_operador' => $operador->id,
            'id_liberador' => $liberador->id,
            'fecha_liberacion' => now()->toDateString(),
            'nombre' => $bote->nombre,
            'lote' => $bote->lote,
            'peso' => $bote->peso,
            'numero_factura' => $bote->numero_factura,
        ]);

        $bote->update(['estado' => 'liberado']);

        return redirect()->route('soldadura.liberarQRPlanta')
            ->with('success', 'Bote liberado exitosamente al operador ' . $operador->name);
    }
}