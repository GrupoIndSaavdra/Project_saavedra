<?php

namespace App\Http\Controllers;

use App\Models\SoldaduraLote;
use App\Models\SoldaduraBote;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Barryvdh\DomPDF\Facade\Pdf;

class RegenerarQRController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Muestra la vista de verificación de contraseña
     */
    public function index()
    {
        return view('welding_tracking_views.regenerate_qr_verify');
    }

    /**
     * Valida la contraseña de administrador y muestra la vista de lotes
     */
    public function verificarAcceso(Request $request)
    {
        $request->validate([
            'password_admin' => 'required|string',
        ]);

        if (!$this->validatePasswordAdmin($request->password_admin)) {
            return back()->with('error', 'Contraseña de administrador incorrecta. Solo administradores pueden acceder a esta sección.');
        }

        // Guardar en sesión que el usuario está verificado
        session(['regenerar_qr_verificado' => true]);

        return redirect()->route('soldadura.regenerarQR.lista');
    }

    /**
     * Muestra la lista de lotes (solo si está verificado)
     */
    public function listaLotes()
    {
        if (!session('regenerar_qr_verificado')) {
            return redirect()->route('soldadura.regenerarQR')->with('error', 'Debes verificar tu contraseña de administrador primero.');
        }

        $lotes = SoldaduraLote::query()->with('botes')
            ->orderBy('fecha_ingreso', 'desc')
            ->get();

        return view('welding_tracking_views.regenerate_qr_list', compact('lotes'));
    }

    /**
     * Valida la contraseña de administrador
     * Usa la misma lógica que ProcessProductionController
     * @param string $passwordEntered
     */
    public function validatePasswordAdmin($passwordEntered)
    {
        if ($passwordEntered) {
            $users = User::all();
            foreach ($users as $user) {
                if ($user->perfil == 1 || $user->perfil == 4) { // Verificar si el usuario es admin o superadmin
                    if (Hash::check($passwordEntered, $user->contrasena)) {
                        return true; // Contraseña correcta
                    }
                }
            }
        }
        return false; // No se proporcionó contraseña o es incorrecta
    }

    /**
     * Regenera el QR del lote
     * @param int|string $loteId
     */
    public function regenerarQRLote($loteId)
    {
        if (!session('regenerar_qr_verificado')) {
            return redirect()->route('soldadura.regenerarQR')->with('error', 'Sesión expirada. Verifica tu contraseña nuevamente.');
        }

        $lote = SoldaduraLote::findOrFail($loteId);

        return $this->generarPDFLote($lote);
    }

    /**
     * Regenera los QRs individuales de un lote
     * @param int|string $loteId
     */
    public function regenerarQRIndividuales($loteId)
    {
        if (!session('regenerar_qr_verificado')) {
            return redirect()->route('soldadura.regenerarQR')->with('error', 'Sesión expirada. Verifica tu contraseña nuevamente.');
        }

        $lote = SoldaduraLote::findOrFail($loteId);
        $botes = SoldaduraBote::query()->where('lote_id', $lote->id)->orderBy('numero_bote')->get();

        if ($botes->isEmpty()) {
            return back()->with('error', 'Este lote no tiene botes individuales generados.');
        }

        return $this->generarPDFIndividuales($botes, $lote);
    }

    /**
     * Cierra la sesión de verificación
     */
    public function cerrarSesion()
    {
        session()->forget('regenerar_qr_verificado');
        return redirect()->route('soldadura.regenerarQR')->with('success', 'Sesión cerrada correctamente.');
    }

    /**
     * Genera el PDF del QR de lote
     * @param \App\Models\SoldaduraLote $lote
     */
    private function generarPDFLote($lote)
    {
        $qrContent = json_encode([
            'tipo' => 'lote',
            'id' => $lote->id,
            'matricula' => $lote->matricula,
            'nombre' => $lote->nombre,
            'lote' => $lote->lote,
            'peso_total_kg' => $lote->peso_total_kg,
            'numero_factura' => $lote->numero_factura,
        ]);

        $pdf = Pdf::loadView('welding_tracking_views.qr_batch_pdf', compact('lote', 'qrContent'));

        return $pdf->download('QR_Lote_' . $lote->matricula . '_REGENERADO.pdf');
    }

    /**
     * Genera el PDF de los QRs individuales
     * @param \Illuminate\Support\Collection $botes
     * @param \App\Models\SoldaduraLote $lote
     */
    private function generarPDFIndividuales($botes, $lote)
    {
        $qrCodes = [];

        foreach ($botes as $bote) {
            $qrContent = json_encode([
                'tipo' => 'bote',
                'id' => $bote->id,
                'matricula' => $bote->matricula,
                'lote_id' => $bote->lote_id,
                'numero_bote' => $bote->numero_bote,
            ]);

            $qrCodes[] = [
                'bote' => $bote,
                'qrContent' => $qrContent
            ];
        }

        $pdf = Pdf::loadView('welding_tracking_views.qr_individual_pdf', compact('qrCodes', 'lote'));

        return $pdf->download('QR_Botes_' . $lote->matricula . '_REGENERADO.pdf');
    }
}