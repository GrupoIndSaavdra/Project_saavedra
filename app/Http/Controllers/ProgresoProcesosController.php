<?php

namespace App\Http\Controllers;

use App\Models\Cepillado;
use App\Models\Clase;
use App\Models\Desbaste_pza;
use App\Models\DesbasteExterior;
use App\Models\Moldura;
use App\Models\Orden_trabajo;
use App\Models\PrimeraOpeSoldadura;
use App\Models\PrimeraOpeSoldadura_pza;
use App\Models\Pza_cepillado;
use App\Models\RevLaterales;
use App\Models\RevLaterales_pza;
use App\Models\SegundaOpeSoldadura;
use App\Models\SegundaOpeSoldadura_pza;

class ProgresoProcesosController extends Controller
{
    public function show()
    {
        $ot = $this->almacenarDatos();
        if ($ot != 0) {
            return view('processesAdmin.verProcesos', ['ot' => $ot]);
        }

        return view('processesAdmin.verProcesos');
    }

    /**
     * @return array<int, array<int, mixed>>|int
     */
    public function almacenarDatos()
    {
        $clases = Clase::query()->where('finalizada', '=', 0, 'and')->get();
        if ($clases->isEmpty()) {
            return 0;
        }

        $ordenesMap = Orden_trabajo::all()->keyBy('id');
        $moldurasMap = Moldura::all()->keyBy('id');

        $ot = [];

        foreach ($clases as $clase) {
            $ordenT = $ordenesMap->get($clase->id_ot);
            $moldura = $ordenT ? $moldurasMap->get($ordenT->id_moldura) : null;
            $otId = $ordenT ? $ordenT->id : '';

            $fila = array_fill(0, 26, 0);
            $fila[0] = $ordenT ? $ordenT->id : null;
            $fila[1] = $moldura ? $moldura->nombre : '?';
            $fila[2] = $clase->nombre.' '.$clase->tamanio;

            // Cepillado
            $procesoCep = 'Cepillado_'.$clase->nombre.'_'.$otId;
            $cepillado = Cepillado::query()->where('id_proceso', $procesoCep)->first();
            if ($cepillado != null) {
                $countCep = Pza_cepillado::query()->where('estado', 2)->where('correcto', 1)->where('id_proceso', $cepillado->id)->count();
                $fila[3] = $countCep / 2;
            }

            // Desbaste Exterior
            $procesoDes = 'desbaste_'.$clase->nombre.'_'.$otId;
            $desbaste = DesbasteExterior::query()->where('id_proceso', $procesoDes)->first();
            if ($desbaste != null) {
                $pzas = Desbaste_pza::query()->where('estado', 2)->where('correcto', 1)->where('id_proceso', $desbaste->id)->get();
                $fila[4] = $this->contarJuegosCorrectos($pzas);
            }

            // Revisión Laterales
            $procesoRev = 'revLaterales_'.$clase->nombre.'_'.$otId;
            $revLaterales = RevLaterales::query()->where('id_proceso', $procesoRev)->first();
            if ($revLaterales != null) {
                $pzas = RevLaterales_pza::query()->where('estado', 2)->where('correcto', 1)->where('id_proceso', $revLaterales->id)->get();
                $fila[5] = $this->contarJuegosCorrectos($pzas);
            }

            // 1ª Operación Soldadura
            $proceso1Op = '1opeSoldadura_'.$clase->nombre.'_'.$otId;
            $primeraOpeSoldadura = PrimeraOpeSoldadura::query()->where('id_proceso', $proceso1Op)->first();
            if ($primeraOpeSoldadura != null) {
                $pzas = PrimeraOpeSoldadura_pza::query()->where('estado', 2)->where('correcto', 1)->where('id_proceso', $primeraOpeSoldadura->id)->get();
                $fila[6] = $this->contarJuegosCorrectos($pzas);
            }

            // 2ª Operación Soldadura
            $proceso2Op = '2opeSoldadura_'.$clase->nombre.'_'.$otId;
            $segundaOpeSoldadura = SegundaOpeSoldadura::query()->where('id_proceso', $proceso2Op)->first();
            if ($segundaOpeSoldadura != null) {
                $pzas = SegundaOpeSoldadura_pza::query()->where('estado', 2)->where('correcto', 1)->where('id_proceso', $segundaOpeSoldadura->id)->get();
                $fila[7] = $this->contarJuegosCorrectos($pzas);
            }

            $fila[25] = $clase->pedido;
            $ot[] = $fila;
        }

        return ! empty($ot) ? $ot : 0;
    }

    /**
     * Cuenta los juegos donde existen al menos dos piezas correctas.
     *
     * @param  \Illuminate\Support\Collection|null  $pzas
     */
    private function contarJuegosCorrectos($pzas): int
    {
        if (! $pzas || $pzas->isEmpty()) {
            return 0;
        }

        $juegosCount = [];
        foreach ($pzas as $pza) {
            if ($pza->correcto == 1 && ! empty($pza->n_juego)) {
                $juegosCount[$pza->n_juego] = ($juegosCount[$pza->n_juego] ?? 0) + 1;
            }
        }

        $correctas = 0;
        foreach ($juegosCount as $count) {
            if ($count >= 2) {
                $correctas++;
            }
        }

        return $correctas;
    }
}
