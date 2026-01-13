<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\LiberacionSoldadura;
use App\Models\RegistroSoldadura;
use App\Models\QRGeneradoSoldadura;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LiberarSoldaduraController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Mostrar formulario
     */
    public function create()
    {
        $operadores = User::where('perfil', 2)->get();

        $soldaduras = RegistroSoldadura::where('kilos', '>', 0)
            ->selectRaw('nombre, lote, SUM(kilos) as kilos_totales, MIN(id) as id')
            ->groupBy('nombre', 'lote')
            ->having('kilos_totales', '>', 0)
            ->get();

        return view('trackingSoldadura_views.liberar', compact('operadores', 'soldaduras'));
    }

    /**
     * Validar QR escaneado por ID
     */
    public function validarQR($qrId)
    {
        if (!is_numeric($qrId) || $qrId <= 0) {
            throw new \Exception('ID de QR inválido');
        }

        $qrGenerado = QRGeneradoSoldadura::find($qrId);

        if (!$qrGenerado) {
            throw new \Exception('QR no existe en el sistema');
        }

        if ($qrGenerado->estado === QRGeneradoSoldadura::ESTADO_CANCELADO) {
            throw new \Exception('QR cancelado por vencimiento');
        }

        if ($qrGenerado->estado === QRGeneradoSoldadura::ESTADO_LIBERADO) {
            throw new \Exception('QR ya fue liberado anteriormente');
        }

        $estadoActual = $qrGenerado->estado;
        if ($estadoActual !== QRGeneradoSoldadura::ESTADO_GENERADO && !empty($estadoActual)) {
            throw new \Exception('QR en estado inválido: ' . ($estadoActual ?? 'null'));
        }

        $fechaGeneracion = Carbon::parse($qrGenerado->fecha_generacion);
        $fechaLimite = $fechaGeneracion->copy()->addDays(2);
        if (now()->gt($fechaLimite)) {
            $qrGenerado->update(['estado' => QRGeneradoSoldadura::ESTADO_CANCELADO]);
            throw new \Exception('QR vencido y cancelado automáticamente');
        }

        return $qrGenerado;
    }

    /**
     * Procesar liberación desde QR
     */
    public function liberarDesdeQR(Request $request)
    {
        try {
            $request->validate([
                'qr_content' => 'required|numeric|min:1',
            ]);

            $qrId = (int) $request->qr_content;

            Log::info('Iniciando liberación QR', [
                'qr_id' => $qrId,
                'user_id' => auth()->id(),
                'timestamp' => now()->toISOString()
            ]);

            $qrGenerado = $this->validarQR($qrId);

            Log::info('QR validado', [
                'qr_id' => $qrGenerado->id,
                'estado_actual' => $qrGenerado->estado
            ]);

            $inventarioDisponible = RegistroSoldadura::where('nombre', $qrGenerado->nombre)
                ->where('lote', $qrGenerado->lote)
                ->where('kilos', '>', 0)
                ->sum('kilos');

            Log::info('Inventario verificado', [
                'disponible' => $inventarioDisponible,
                'requerido' => $qrGenerado->kilos
            ]);

            if ($inventarioDisponible < $qrGenerado->kilos) {
                Log::warning('Inventario insuficiente', [
                    'qr_id' => $qrGenerado->id,
                    'disponible' => $inventarioDisponible,
                    'requerido' => $qrGenerado->kilos
                ]);
                return response()->json([
                    'success' => false,
                    'message' => "Inventario insuficiente. Disponible: {$inventarioDisponible} kg, Requerido: {$qrGenerado->kilos} kg"
                ], 200);
            }

            $liberacion = null;
            DB::transaction(function () use ($qrGenerado, &$liberacion) {
                $liberacion = LiberacionSoldadura::create([
                    'id_operador' => $qrGenerado->id_operador,
                    'fecha_entrega' => now()->toDateString(),
                    'nombre' => $qrGenerado->nombre,
                    'lote' => $qrGenerado->lote,
                    'cantidad' => $qrGenerado->kilos,
                    'qr_generado_id' => $qrGenerado->id,
                    'estado' => 'liberado'
                ]);

                $registros = RegistroSoldadura::where('nombre', $qrGenerado->nombre)
                    ->where('lote', $qrGenerado->lote)
                    ->where('kilos', '>', 0)
                    ->orderBy('id')
                    ->get();

                $cantidadRestante = $qrGenerado->kilos;
                foreach ($registros as $registro) {
                    if ($cantidadRestante <= 0)
                        break;
                    $descontar = min($cantidadRestante, $registro->kilos);
                    $registro->kilos -= $descontar;
                    $registro->save();
                    $cantidadRestante -= $descontar;
                }

                $qrGenerado->update(['estado' => QRGeneradoSoldadura::ESTADO_LIBERADO]);
            });

            Log::info('QR liberado exitosamente', ['liberacion_id' => $liberacion?->id ?? 'N/A']);

            return response()->json([
                'success' => true,
                'message' => "QR liberado correctamente: {$qrGenerado->kilos} kg de {$qrGenerado->nombre} - Lote: {$qrGenerado->lote}"
            ]);

        } catch (\Exception $e) {
            Log::error('Error liberando QR', [
                'qr_id' => $request->qr_content ?? 'N/A',
                'message' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Guardar liberación manual
     */
    public function store(Request $request)
    {
        $request->validate([
            'operador_id' => 'required|exists:users,id',
            'fecha_entrega' => 'required|date',
            'soldadura_id' => 'required',
            'cantidad' => 'required|numeric|min:0.01',
        ]);

        $soldaduraInfo = explode('|', $request->soldadura_id);
        if (count($soldaduraInfo) !== 2) {
            return redirect()->back()
                ->withErrors(['soldadura_id' => 'Formato inválido'])
                ->withInput();
        }
        [$nombre, $lote] = $soldaduraInfo;

        $registros = RegistroSoldadura::where('nombre', $nombre)
            ->where('lote', $lote)
            ->where('kilos', '>', 0)
            ->orderBy('id')
            ->get();

        $kilosTotales = $registros->sum('kilos');

        if ($kilosTotales <= 0) {
            return redirect()->back()
                ->withErrors(['cantidad' => 'No hay soldadura disponible para este lote.'])
                ->withInput();
        }

        if ($request->cantidad > $kilosTotales) {
            return redirect()->back()
                ->withErrors(['cantidad' => "Solo hay {$kilosTotales} kg disponibles. No se pueden liberar {$request->cantidad} kg."])
                ->withInput();
        }

        DB::transaction(function () use ($registros, $request, $nombre, $lote, &$liberacion) {
            $liberacion = LiberacionSoldadura::create([
                'id_operador' => $request->operador_id,
                'fecha_entrega' => $request->fecha_entrega,
                'nombre' => $nombre,
                'lote' => $lote,
                'cantidad' => $request->cantidad,
            ]);

            $cantidadRestante = $request->cantidad;
            foreach ($registros as $registro) {
                if ($cantidadRestante <= 0)
                    break;
                $descontar = min($cantidadRestante, $registro->kilos);
                $registro->kilos -= $descontar;
                $registro->save();
                $cantidadRestante -= $descontar;
            }
        });

        $kilosRestantes = RegistroSoldadura::where('nombre', $nombre)
            ->where('lote', $lote)
            ->sum('kilos');

        $mensaje = 'Soldadura liberada correctamente.';
        if ($kilosRestantes <= 0) {
            $mensaje .= ' ATENCIÓN: Se agotó el inventario de este lote.';
        } elseif ($kilosRestantes <= 5) {
            $mensaje .= " ADVERTENCIA: Solo quedan {$kilosRestantes} kg de este lote.";
        }

        return redirect()
            ->route('soldadura.liberar')
            ->with('success', $mensaje);
    }
}