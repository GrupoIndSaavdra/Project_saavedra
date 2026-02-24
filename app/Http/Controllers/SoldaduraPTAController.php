<?php

namespace App\Http\Controllers;

use App\Models\SoldaduraPTA_pza;

class SoldaduraPTAController extends Controller
{
    /**
     * Tipos de sub-fila, en orden de aparición en la tabla.
     * Cada pieza (M o H) genera exactamente 3 registros en soldaduraPTA_pza.
     */
    const TIPOS_MEDIDA = ['D_Conexion_pico', 'D_Conexion_obt', 'Perfilado'];

    /**
     * Mapa tipo_medida → campo de valor principal (la columna "Valor" de la tabla)
     */
    const CAMPO_VALOR = [
        'D_Conexion_pico' => 'd_conexion_pico',
        'D_Conexion_obt' => 'd_conexion_obt',
        'Perfilado' => 'perfilado',
    ];

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Guarda / actualiza las 3 sub-filas de una pieza individual (M o H).
     *
     * El $request lleva arrays con clave = ID del registro SoldaduraPTA_pza:
     *   - tipo_medida[id]
     *   - d_conexion_pico[id] | d_conexion_obt[id] | perfilado[id]
     *   - vl[id], tipo_preparacion[id]
     *   - precalentamiento[idPrecal]   (solo la fila D_Conexion_pico)
     *   - sold_inicial[id], sold_aplicada[id], sold_final[id]
     *   - corr_inicial[id], corr_aplicada[id], corr_final[id]
     *   - gas_argon[id], velocidad_calculada[id]
     *   - resultado[id], defecto_pta[id]
     *   - observaciones[idObs]          (solo la fila D_Conexion_pico)
     *
     * @param \Illuminate\Http\Request $request
     * @param int|null $index  — no se usa en PTA (legacy param), se mantiene para
     *                          compatibilidad con la firma que llama ProcessProductionController
     */
    public function storePiece($request, $index = null)
    {
        // Iteramos sobre todos los IDs recibidos en el campo piece_id[]
        if (!$request->has('piece_id')) {
            return;
        }

        foreach ($request->piece_id as $key => $pieceId) {
            if (!$pieceId) {
                continue;   // fila nueva sin ID — ignorar por ahora, se crean en selectAssembly
            }

            $piece = SoldaduraPTA_pza::find($pieceId, ['*']);
            if (!$piece) {
                continue;
            }

            $tipo = $request->tipo_medida[$key] ?? null;

            // ── Campos comunes a las 3 sub-filas ──
            $piece->tipo_medida = $tipo;
            $piece->vl = $request->vl[$key] ?? null;
            $piece->tipo_preparacion = $request->tipo_preparacion[$key] ?? null;
            $piece->sold_inicial = $request->sold_inicial[$key] ?? null;
            $piece->sold_aplicada = $request->sold_aplicada[$key] ?? null;
            $piece->sold_final = $request->sold_final[$key] ?? null;
            $piece->corr_inicial = $request->corr_inicial[$key] ?? null;
            $piece->corr_aplicada = $request->corr_aplicada[$key] ?? null;
            $piece->corr_final = $request->corr_final[$key] ?? null;
            $piece->gas_argon = $request->gas_argon[$key] ?? null;
            $piece->velocidad_calculada = $request->velocidad_calculada[$key] ?? null;
            $piece->resultado = $request->resultado[$key] ?? null;
            $piece->defecto_pta = $request->defecto_pta[$key] ?? 'Ninguno';

            // Mantener campo 'error' (legacy) sincronizado con defecto_pta
            $piece->error = $piece->defecto_pta;

            // ── Campo de valor principal según tipo_medida ──
            if ($tipo === 'D_Conexion_pico') {
                $piece->d_conexion_pico = $request->d_conexion_pico[$key] ?? null;
                // Precalentamiento — único por pieza, vive en D_Conexion_pico
                $piece->precalentamiento = $request->precalentamiento[$key] ?? null;
                // Observaciones — únicas por pieza, viven en D_Conexion_pico
                $piece->observaciones = $request->observaciones[$key] ?? null;
            } elseif ($tipo === 'D_Conexion_obt') {
                $piece->d_conexion_obt = $request->d_conexion_obt[$key] ?? null;
            } elseif ($tipo === 'Perfilado') {
                $piece->perfilado = $request->perfilado[$key] ?? null;
            }

            $piece->estado = 2;
            $piece->save();
        }
    }

    /**
     * comparePieceData — Soldadura PTA no tiene cotas nominales ni tolerancias.
     * Siempre retorna 1 (correcto) para que el sistema no marque errores automáticos.
     * La inspección se captura manualmente en los campos 'resultado' y 'defecto_pta'.
     */
    public function comparePieceData($piece, $cNominal, $tolerance): int
    {
        return 1;
    }

    /**
     * buildPiezasGroup — Construye la colección agrupada para pasar al partial Blade.
     *
     * Uso desde un controlador o vista:
     *   $piezasGroup = (new SoldaduraPTAController())->buildPiezasGroup($procesoDB->id);
     *   @include('processes_views.soldaduraPTA_table_partial', [
     *       'piezas'      => $piezasDB,
     *       'piezasGroup' => $piezasGroup,
     *       'modo'        => 'reporte',
     *   ])
     *
     * @param int $idProceso  — ID del registro en la tabla soldaduraPTA
     * @return \Illuminate\Support\Collection  agrupada por n_pieza
     */
    public function buildPiezasGroup(int $idProceso)
    {
        return SoldaduraPTA_pza::where('id_proceso', $idProceso)
            ->where('estado', 2)
            ->orderBy('n_pieza')
            ->orderByRaw("FIELD(tipo_medida, 'D_Conexion_pico', 'D_Conexion_obt', 'Perfilado')")
            ->get()
            ->groupBy('n_pieza');
    }
}
