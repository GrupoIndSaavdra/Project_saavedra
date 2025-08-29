<?php

namespace App\Http\Controllers;

class CepilladoController extends Controller
{
    protected $controladorPzasLiberadas;
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function storePiece($request, &$piece, $cNominal, $tolerance)
    {
        //Guardar los datos de la pieza
        $piece->fill($request->only([
            'radiof_mordaza',
            'radiof_mayor',
            'radiof_sufridera',
            'profuFinal_CFC',
            'profuFinal_mitadMB',
            'profuFinal_PCO',
            'acetato_MB',
            'ensamble',
            'distancia_barrenoAli',
            'profu_barrenoAliHembra',
            'profu_barrenoAliMacho',
            'altura_venaHembra',
            'altura_venaMacho',
            'ancho_vena',
            'laterales',
            'pin1',
            'pin2',
            'observaciones',
        ]));
        $piece->estado = 2;

        //Verificar si las medidas de la pieza estan correctas
        if ($this->compararDatosPieza($piece, $cNominal, $tolerance) == 0 && $request->error == 0) {
            $piece->error = 'Maquinado';
            $piece->correcto = 0;
        } else if (($this->compararDatosPieza($piece, $cNominal, $tolerance) == 0 && $request->error == 'Fundicion') || ($this->compararDatosPieza($piece, $cNominal, $tolerance) == 1 && $request->error == 'Fundicion')) {
            $piece->error = $request->error;
            $piece->correcto = 0;
        } else {
            $piece->error = 'Ninguno';
            $piece->correcto = 1;
        }
        $piece->save();
        return $piece;
    }


    public function compararDatosPieza($pieza, $cNominal, $tolerancia) //Función para comparar los datos de la pieza con los datos nominales y de tolerancia.
    {
        if ($pieza->radiof_mordaza > ($cNominal->radiof_mordaza + $tolerancia->radiof_mordaza1) || $pieza->radiof_mordaza < ($cNominal->radiof_mordaza - $tolerancia->radiof_mordaza2) || $pieza->radiof_mayor > ($cNominal->radiof_mayor + $tolerancia->radiof_mayor1) || $pieza->radiof_mayor < ($cNominal->radiof_mayor - $tolerancia->radiof_mayor2) || $pieza->radiof_sufridera > ($cNominal->radiof_sufridera + $tolerancia->radiof_sufridera1) || $pieza->radiof_sufridera < ($cNominal->radiof_sufridera - $tolerancia->radiof_sufridera2) || $pieza->profuFinal_CFC > ($cNominal->profuFinal_CFC + $tolerancia->profuFinal_CFC1) || $pieza->profuFinal_CFC < ($cNominal->profuFinal_CFC - $tolerancia->profuFinal_CFC2) || $pieza->profuFinal_mitadMB  > ($cNominal->profuFinal_mitadMB  + $tolerancia->profuFinal_mitadMB1) || $pieza->profuFinal_mitadMB < ($cNominal->profuFinal_mitadMB - $tolerancia->profuFinal_mitadMB2) || $pieza->profuFinal_PCO  > ($cNominal->profuFinal_PCO  + $tolerancia->profuFinal_PCO1) || $pieza->profuFinal_PCO < ($cNominal->profuFinal_PCO - $tolerancia->profuFinal_PCO2) || $pieza->acetato_MB == "Mal" || $pieza->ensamble > ($cNominal->ensamble + $tolerancia->ensamble1) || $pieza->ensamble < ($cNominal->ensamble - $tolerancia->ensamble2) || $pieza->distancia_barrenoAli > ($cNominal->distancia_barrenoAli + $tolerancia->distancia_barrenoAli1) || $pieza->distancia_barrenoAli < ($cNominal->distancia_barrenoAli - $tolerancia->distancia_barrenoAli2) || $pieza->profu_barrenoAliHembra > ($cNominal->profu_barrenoAliHembra + $tolerancia->profu_barrenoAliHembra1) || $pieza->profu_barrenoAliHembra < ($cNominal->profu_barrenoAliHembra - $tolerancia->profu_barrenoAliHembra2) || $pieza->profu_barrenoAliMacho > ($cNominal->profu_barrenoAliMacho + $tolerancia->profu_barrenoAliMacho1) || $pieza->profu_barrenoAliMacho < ($cNominal->profu_barrenoAliMacho - $tolerancia->profu_barrenoAliMacho2) || $pieza->altura_venaHembra > ($cNominal->altura_venaHembra + $tolerancia->altura_venaHembra1) || $pieza->altura_venaHembra < ($cNominal->altura_venaHembra - $tolerancia->altura_venaHembra2) || $pieza->altura_venaMacho > ($cNominal->altura_venaMacho + $tolerancia->altura_venaMacho1) || $pieza->altura_venaMacho < ($cNominal->altura_venaMacho - $tolerancia->altura_venaMacho2) || $pieza->ancho_vena > ($cNominal->ancho_vena + $tolerancia->ancho_vena1) || $pieza->ancho_vena < ($cNominal->ancho_vena - $tolerancia->ancho_vena2) || $pieza->laterales > ($cNominal->laterales + $tolerancia->laterales1) || $pieza->laterales < ($cNominal->laterales - $tolerancia->laterales2) || $pieza->pin1 > ($cNominal->pin1 + $tolerancia->pin1) || $pieza->pin1 < ($cNominal->pin1 - $tolerancia->pin1) || $pieza->pin2 > ($cNominal->pin2 + $tolerancia->pin2) || $pieza->pin2 < ($cNominal->pin2 - $tolerancia->pin2)) {
            return 0; //Si los datos de la pieza son diferentes a los nominales y de tolerancia, se retorna 0.
        } else {
            return 1; //Si los datos de la pieza son iguales a los nominales y de tolerancia, se retorna 1.
        }
    }
}
