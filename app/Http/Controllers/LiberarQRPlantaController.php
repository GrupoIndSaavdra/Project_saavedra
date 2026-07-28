<?php

namespace App\Http\Controllers;

use App\Models\SoldaduraBote;
use App\Models\SoldaduraRecepcionPlanta;
use App\Models\SoldaduraLiberacion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LiberarQRPlantaController extends Controller
{
    /**
     * Parsea el contenido del QR que puede venir con caracteres especiales
     * del lector de códigos de barras/QR
     *
     * Ejemplo de entrada problemática: "¨[tipo[Ñ[bote[,[id[Ñ2,[matricula[Ñ[1401261534ALLTBS'002[,[lote?id[Ñ1,"
     * Formato esperado JSON: {"tipo":"bote","id":2,"matricula":"1401261534ALLTBS-002","lote_id":1}
     */
    /**
     * @param string $qrContent
     * @return array|null
     */
    private function parseQRContent($qrContent)
    {
        // Primero intentar parsear como JSON normal
        $qrData = json_decode($qrContent, true);
        if ($qrData && isset($qrData['tipo'])) {
            return $qrData;
        }

        // Si no es JSON válido, intentar limpiar caracteres especiales del lector
        // Mapeo de caracteres especiales a caracteres JSON
        $replacements = [
            '¨[' => '{',      // Apertura de objeto
            '[,' => ',',      // Coma
            '[Ñ' => ':',      // Dos puntos
            'Ñ[' => ':"',     // Dos puntos seguido de comilla
            '[Ñ[' => ':"',    // Variante
            "'" => '-',       // Apóstrofe a guión
            '[' => '"',       // Corchete a comilla
            '?' => '_',       // Signo de interrogación a guión bajo
            '¨' => '{',       // Diéresis a llave
        ];

        $cleaned = $qrContent;

        // Aplicar reemplazos en orden específico
        $cleaned = str_replace('¨[', '{', $cleaned);
        $cleaned = str_replace('[Ñ[', ':"', $cleaned);
        $cleaned = str_replace('[Ñ', ':', $cleaned);
        $cleaned = str_replace('Ñ[', ':"', $cleaned);
        $cleaned = str_replace('[,', '",', $cleaned);
        $cleaned = str_replace("'", '-', $cleaned);
        $cleaned = str_replace('?', '_', $cleaned);

        // Limpiar corchetes restantes que deberían ser comillas
        $cleaned = preg_replace('/\[([a-zA-Z_]+)\[/', '"$1":', $cleaned);
        $cleaned = preg_replace('/\[([0-9]+)/', '$1', $cleaned);
        $cleaned = str_replace('[', '"', $cleaned);

        // Asegurar que termine correctamente
        $cleaned = rtrim($cleaned, ',');
        if (substr($cleaned, -1) !== '}') {
            $cleaned .= '"}';
        }

        // Intentar parsear el JSON limpio
        $qrData = json_decode($cleaned, true);
        if ($qrData && isset($qrData['tipo'])) {
            return $qrData;
        }

        // Si aún no funciona, intentar extraer datos con regex
        $data = [];

        // Extraer tipo
        if (preg_match('/tipo[^a-z]*([a-z_]+)/i', $qrContent, $matches)) {
            $data['tipo'] = strtolower($matches[1]);
        }

        // Extraer id
        if (preg_match('/[^a-z]id[^0-9]*(\d+)/i', $qrContent, $matches)) {
            $data['id'] = (int) $matches[1];
        }

        // Extraer matricula
        if (preg_match('/matricula[^a-zA-Z0-9]*([a-zA-Z0-9\-\']+)/i', $qrContent, $matches)) {
            $data['matricula'] = str_replace("'", '-', $matches[1]);
        }

        // Extraer lote_id
        if (preg_match('/lote[_\?]?id[^0-9]*(\d+)/i', $qrContent, $matches)) {
            $data['lote_id'] = (int) $matches[1];
        }

        // Extraer numero_bote
        if (preg_match('/numero[_\?]?bote[^0-9]*(\d+)/i', $qrContent, $matches)) {
            $data['numero_bote'] = (int) $matches[1];
        }

        return !empty($data) ? $data : null;
    }

    // ==========================================
    // INTERFAZ 1: RECEPCIÓN EN PLANTA (ENTRADA)
    // ==========================================

    public function indexRecepcion()
    {
        $almacenistas = User::query()->where('perfil', '5')->get(); // Perfil 5 = Almacén
        $botesDisponibles = SoldaduraBote::query()->where('estado', 'en_planta')->get();
        $botesEnPlanta = $botesDisponibles->count();
        $currentUser = auth()->user(); // Usuario autenticado actual

        return view('welding_tracking_views.plant_reception', compact('almacenistas', 'botesEnPlanta', 'currentUser'));
    }

        /**
     * @param \Illuminate\Http\Request Request $request
     */
    public function escanearRecepcion(Request $request)
    {
        $request->validate([
            'qr_content' => 'required|string',
        ]);

        try {
            $qrData = $this->parseQRContent($request->qr_content);

            if (!$qrData || !isset($qrData['tipo']) || $qrData['tipo'] !== 'bote') {
                return back()->withErrors(['qr_content' => 'QR no válido o no es de un bote individual. Contenido recibido: ' . substr($request->qr_content, 0, 100)]);
            }

            $bote = SoldaduraBote::query()->with('lote')->find($qrData['id']);

            if (!$bote) {
                return back()->withErrors(['qr_content' => 'Bote no encontrado con ID: ' . $qrData['id']]);
            }

            // Verificar estado - solo se puede recibir si está en tránsito
            if ($bote->estado === 'en_planta') {
                return back()->withErrors(['qr_content' => 'Este bote ya fue recibido en planta']);
            }

            if ($bote->estado === 'liberado') {
                return back()->withErrors(['qr_content' => 'Este bote ya fue liberado a un operador']);
            }

            if ($bote->estado !== 'en_transito') {
                return back()->withErrors(['qr_content' => 'Este bote no está en tránsito. Estado actual: ' . $bote->estado]);
            }

            $almacenistas = User::query()->where('perfil', '5')->get();
            $botesEnPlanta = SoldaduraBote::query()->where('estado', 'en_planta')->count();
            $currentUser = auth()->user(); // Usuario autenticado actual

            return view('welding_tracking_views.plant_reception', compact('bote', 'almacenistas', 'botesEnPlanta', 'currentUser'));

        } catch (\Exception $e) {
            return back()->withErrors(['qr_content' => 'Error al procesar el QR: ' . $e->getMessage()]);
        }
    }

        /**
     * @param \Illuminate\Http\Request Request $request
     */
    public function confirmarRecepcion(Request $request)
    {
        $request->validate([
            'bote_id' => 'required|exists:soldadura_botes,id',
            'recibido_por' => 'required|exists:users,id',
            'observaciones' => 'nullable|string|max:500',
        ]);

        $bote = SoldaduraBote::findOrFail($request->bote_id);

        // Verificar que el bote esté en tránsito
        if ($bote->estado !== 'en_transito') {
            return back()->withErrors(['bote_id' => 'El bote debe estar en tránsito para ser recibido']);
        }

        $recibidor = User::findOrFail($request->recibido_por);

        DB::transaction(function () use ($bote, $recibidor, $request) {
            // Registrar recepción
            SoldaduraRecepcionPlanta::create([
                'bote_id' => $bote->id,
                'recibido_por' => $recibidor->id,
                'fecha_hora_recepcion' => now(),
                'observaciones' => $request->observaciones,
            ]);

            // Actualizar estado
            $bote->update(['estado' => 'en_planta']);
        });

        return redirect()->route('soldadura.recepcionPlanta')
            ->with('success', 'Bote recibido exitosamente en planta por ' . $recibidor->name);
    }

    // ==========================================
    // INTERFAZ 2: LIBERACIÓN A OPERADORES (SALIDA)
    // ==========================================

    public function index()
    {
        $usuarios = User::query()->whereIn('perfil', ['2', '5'], 'and', false)->get()->groupBy('perfil');
        $operadores = $usuarios->get('2', collect());
        $almacenistas = $usuarios->get('5', collect());

        $botesDisponibles = SoldaduraBote::query()->where('estado', 'en_planta')
            ->with('lote')
            ->get();
        $botesEnPlanta = $botesDisponibles->count();
        $currentUser = auth()->user(); // Usuario autenticado actual

        return view('welding_tracking_views.release_qr_plant', compact('operadores', 'almacenistas', 'botesEnPlanta', 'botesDisponibles', 'currentUser'));
    }

        /**
     * @param \Illuminate\Http\Request Request $request
     */
    public function escanear(Request $request)
    {
        $request->validate([
            'qr_content' => 'required|string',
        ]);

        try {
            $qrData = $this->parseQRContent($request->qr_content);

            if (!$qrData || !isset($qrData['tipo']) || $qrData['tipo'] !== 'bote') {
                return back()->withErrors(['qr_content' => 'QR no válido o no es de un bote individual. Contenido recibido: ' . substr($request->qr_content, 0, 100)]);
            }

            $bote = SoldaduraBote::query()->with('lote')->find($qrData['id']);

            if (!$bote) {
                return back()->withErrors(['qr_content' => 'Bote no encontrado con ID: ' . $qrData['id']]);
            }

            // Verificar estado
            if ($bote->estado === 'liberado') {
                return back()->withErrors(['qr_content' => 'Este bote ya fue liberado']);
            }

            // Si está en tránsito, primero debe ser recibido en planta
            if ($bote->estado === 'en_transito') {
                return back()->withErrors(['qr_content' => 'Este bote aún no ha sido recibido en planta. Primero debe registrar la recepción.']);
            }

            // Solo se puede liberar si está en planta
            if ($bote->estado !== 'en_planta') {
                return back()->withErrors(['qr_content' => 'Este bote no está disponible para liberación. Estado actual: ' . $bote->estado]);
            }

            $usuarios = User::query()->whereIn('perfil', ['2', '5'], 'and', false)->get()->groupBy('perfil');
            $operadores = $usuarios->get('2', collect());
            $almacenistas = $usuarios->get('5', collect());

            $botesDisponibles = SoldaduraBote::query()->where('estado', 'en_planta')
                ->with('lote')
                ->get();
            $botesEnPlanta = $botesDisponibles->count();
            $currentUser = auth()->user(); // Usuario autenticado actual

            return view('welding_tracking_views.release_qr_plant', compact('bote', 'operadores', 'almacenistas', 'botesEnPlanta', 'botesDisponibles', 'currentUser'));

        } catch (\Exception $e) {
            return back()->withErrors(['qr_content' => 'Error al procesar el QR: ' . $e->getMessage()]);
        }
    }

        /**
     * @param \Illuminate\Http\Request Request $request
     */
    public function liberar(Request $request)
    {
        $request->validate([
            'bote_id' => 'required|exists:soldadura_botes,id',
            'operador_id' => 'required|exists:users,id',
            'liberador_id' => 'required|exists:users,id',
            'observaciones' => 'nullable|string|max:500',
        ]);

        $bote = SoldaduraBote::findOrFail($request->bote_id);

        // Verificar que el bote esté en planta
        if ($bote->estado !== 'en_planta') {
            return back()->withErrors(['bote_id' => 'El bote debe estar en planta para ser liberado']);
        }

        $operador = User::findOrFail($request->operador_id);
        $liberador = User::findOrFail($request->liberador_id);

        $matriculaLiberacion = SoldaduraLiberacion::generarMatriculaLiberacion(
            $bote->matricula,
            $operador->matricula
        );

        DB::transaction(function () use ($bote, $operador, $liberador, $matriculaLiberacion, $request) {
            // Crear registro de liberación
            SoldaduraLiberacion::create([
                'bote_id' => $bote->id,
                'operador_id' => $operador->id,
                'liberado_por' => $liberador->id,
                'matricula_liberacion' => $matriculaLiberacion,
                'fecha_hora_liberacion' => now(),
                'observaciones' => $request->observaciones,
            ]);

            // Actualizar estado del bote
            $bote->update(['estado' => 'liberado']);
        });

        return redirect()->route('soldadura.liberarQRPlanta')
            ->with('success', 'Bote liberado exitosamente al operador ' . $operador->name);
    }
}
