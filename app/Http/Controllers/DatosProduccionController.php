<?php

namespace App\Http\Controllers;

use App\Models\AcabadoBombilo;
use App\Models\AcabadoBombilo_pza;
use App\Models\AcabadoMolde;
use App\Models\AcabadoMolde_pza;
use App\Models\Asentado;
use App\Models\Asentado_pza;
use App\Models\BarrenoManiobra;
use App\Models\BarrenoManiobra_pza;
use App\Models\BarrenoProfundidad;
use App\Models\Cavidades;
use App\Models\Cavidades_pza;
use App\Models\Cepillado;
use App\Models\Clase;
use App\Models\Copiado;
use App\Models\Copiado_pza;
use App\Models\Desbaste_pza;
use App\Models\DesbasteExterior;
use App\Models\EmbudoCM;
use App\Models\EmbudoCM_pza;
use App\Models\Metas;
use App\Models\Moldura;
use App\Models\OffSet;
use App\Models\OffSet_pza;
use App\Models\Orden_trabajo;
use App\Models\Palomas;
use App\Models\Palomas_pza;
use App\Models\Pieza;
use App\Models\PrimeraOpeSoldadura;
use App\Models\PrimeraOpeSoldadura_pza;
use App\Models\Procesos;
use App\Models\PySOpeSoldadura;
use App\Models\PySOpeSoldadura_pza;
use App\Models\Pza_cepillado;
use App\Models\Rebajes;
use App\Models\Rebajes_pza;
use App\Models\Rectificado;
use App\Models\Rectificado_pza;
use App\Models\revCalificado;
use App\Models\revCalificado_pza;
use App\Models\RevLaterales;
use App\Models\RevLaterales_pza;
use App\Models\SegundaOpeSoldadura;
use App\Models\SegundaOpeSoldadura_pza;
use App\Models\Soldadura;
use App\Models\Soldadura_pza;
use App\Models\SoldaduraPTA;
use App\Models\SoldaduraPTA_pza;
use App\Models\User;
use DateTime;
use Illuminate\Http\Request;
use Mockery\Undefined;

class DatosProduccionController extends Controller
{
    protected $controladorPzas;
    public function __construct()
    {
        $this->controladorPzas = new PzasLiberadasController();
        $this->middleware('auth');
    }
    public function index($operadores = null, $filtros = null)
    {
        //Obtener todas las ordenes de trabajo y sus respectivas clases
        $datos = $this->obtenerDatos($this->obtenerOtArray());
        if ($operadores == null) {
            return view('users_views.productionData', compact('datos'));
        } else {
            return view('users_views.productionData', compact('datos', 'operadores', 'filtros'));
        }
    }
    public function show(Request $request)
    {
        //Obtener la ot conforme a su ID
        $ot = Orden_trabajo::find($request->ot);
        $moldura = Moldura::find($ot->id_moldura);

        //Obtener la clase conforme a su ID
        $clase = Clase::where("id_ot", $request->ot)->where("nombre", $request->clases)->first();

        //Obtener el nombre del operador
        $operador = User::where("matricula", $request->operadores)->first();
        $operador = $operador->nombre . " " . $operador->a_paterno . " " . $operador->a_materno;

        //Obtener las piezas conforme a la OT, la clase, el operador y el proceso
        $piezas = Pieza::where("id_ot", $request->ot)->where("id_clase", $clase->id)->where("id_operador", $request->operadores)->where("proceso", $request->procesos)->get();

        $operadores = $this->obtenerInformacionPiezas($piezas, $clase);

        //Guardar los datos buscados de los filtros en un arreglo
        $filtros = [
            "ot" => $request->ot,
            "moldura" => $moldura->nombre,
            "clase" => $request->clases,
            "pedido" => $clase->pedido,
            "operador" => $operador,
            "proceso" => $request->procesos
        ];
        return $this->index($operadores, $filtros);
    }
    public function obtenerOtArray()
    {
        $otArray = Orden_trabajo::all();
        return $otArray;
    }
    public function obtenerDatos($OTs)
    {
        $datos = [];
        foreach ($OTs as $ot) {
            //Asignar nombre de la moldura en el arreglo
            $this->insertarMoldura($ot, $datos[$ot->id]["moldura"]);

            //Agregar operadores de la orden de trabajo 
            $this->insertarDatosRestantes($ot, $datos[$ot->id]["operadores"]);
        }

        return $datos;
    }
    public function insertarMoldura($ot, &$array)
    {
        $moldura =  Moldura::find($ot->id_moldura);
        $array = $moldura->nombre;
    }
    public function insertarDatosRestantes($ot, &$arrayOperadores)
    {
        $arrayOperadores = [];
        $piezas = Pieza::where("id_ot", $ot->id)->get(); //Obtener las piezas en la que se ha trabajado la OT
        foreach ($piezas as $pieza) {
            //Verificar que el operador no se haya agregado previamente
            if (!array_key_exists($pieza->id_operador, $arrayOperadores)) {
                $arrayOperadores[$pieza->id_operador] = [];
                //Obtener el nombre del operador
                $operador = User::where("matricula", $pieza->id_operador)->first();
                $arrayOperadores[$pieza->id_operador]["nombre"] = $operador->nombre . " " . $operador->a_paterno . " " . $operador->a_materno;

                $arrayOperadores[$operador->matricula]["clases"] = [];

                //Obtener el nombre de la clase en la que ha trabajado el operador
                $clase = Clase::find($pieza->id_clase);
                //Verificar que la clase no se haya agregado previamente
                if (!array_key_exists($clase->nombre, $arrayOperadores[$operador->matricula]["clases"])) {
                    $arrayOperadores[$operador->matricula]["clases"][$clase->nombre]["pedido"] = $clase->pedido;
                    $arrayOperadores[$operador->matricula]["clases"][$clase->nombre]["procesos"] = $this->asignarProcesosOperador($clase->id, $operador->matricula);
                }
            }
        }
    }
    public function asignarProcesosOperador($idClase, $operador)
    {
        //Solo retorar las columnas que tengan un valor diferente a 0
        $procesosClase_Operador = Pieza::where("id_clase", $idClase)
            ->where("id_operador", $operador)
            ->distinct()
            ->pluck("proceso")
            ->toArray();
        return $procesosClase_Operador;
    }

    public function obtenerInformacionPiezas($piezas, $clase)
    {
        $operadores = [];
        foreach ($piezas as $pieza) {
            //Se otiene el nombre del operador
            $operadorName = User::where("matricula", $pieza->id_operador)->first();
            $operadorName = $operadorName->nombre . " " . $operadorName->a_paterno . " " . $operadorName->a_materno;

            //Se obtiene la fecha en la que se trabajo la pieza
            $fecha = $pieza->created_at;
            $fecha = $fecha->format("d/m/Y");
            if (!array_key_exists($operadorName, $operadores)) {
                $operadores[$operadorName] = [];
                $meta = $this->obtenerMeta($pieza, $clase->nombre);
                $operadores[$operadorName][$fecha] = ["Piezas buenas" => 0, "Piezas malas" => 0, "meta" => $meta, "Productividad" => 0];
            } else if (!array_key_exists($fecha, $operadores[$operadorName])) {
                $operadores[$operadorName][$fecha] = ["Piezas buenas" => 0, "Piezas malas" => 0];
                $meta = $this->obtenerMeta($pieza, $clase->nombre);
                $operadores[$operadorName][$fecha] = ["Piezas buenas" => 0, "Piezas malas" => 0, "meta" => $meta, "Productividad" => 0];
            }
            $processesAssembly = ["Barreno Maniobra", "Soldadura", "Soldadura PTA", "Rectificado", "Asentado", "Barreno Profundidad", "Palomas", "Rebajes", "Grabado"];
            $cantidad = in_array($pieza->proceso, $processesAssembly) ? 1 : 0.5;
            if ($pieza->error != "Ninguno") {
                if ($pieza->liberacion == 1) {
                    $operadores[$operadorName][$fecha]["Piezas buenas"] += $cantidad;
                } else {
                    $operadores[$operadorName][$fecha]["Piezas malas"] += $cantidad;
                }
            } else {
                if ($pieza->liberacion != 2) {
                    $operadores[$operadorName][$fecha]["Piezas buenas"] += $cantidad;
                } else {
                    $operadores[$operadorName][$fecha]["Piezas malas"] += $cantidad;
                }
            }
            if ($operadores[$operadorName][$fecha]["meta"] != 0) {
                $operadores[$operadorName][$fecha]["Productividad"] = ($operadores[$operadorName][$fecha]["Piezas buenas"] / $operadores[$operadorName][$fecha]["meta"]) * 100;
                $operadores[$operadorName][$fecha]["Productividad"] = round($operadores[$operadorName][$fecha]["Productividad"], 2);
            } else {
                $operadores[$operadorName][$fecha]["Productividad"] = 0;
            }
        }
        // Imprimir los datos del arreglo
        // foreach ($operadores as $operador => $fechas) {
        //     echo $operador . "<br>";
        //     foreach ($fechas as $fecha => $piezas) {
        //         echo $fecha . "<br>";
        //         echo "Piezas buenas: " . $piezas["Piezas buenas"] . "<br>";
        //         echo "Piezas malas: " . $piezas["Piezas malas"] . "<br>";
        //         echo "Meta: " . $piezas["meta"] . "<br>";
        //         echo "<br><br>";
        //     }
        // }
        // die();
        return $operadores;
    }
    public function renombrarProceso($proceso)
    {
        $procesoName = match ($proceso) {
            "Cepillado" => ["cepillado", null],
            "Desbaste Exterior" => ["desbaste", null],
            "Revision Laterales" => ["revLaterales", null],
            "Primera Operacion" => ["primeraOpeSoldadura", null],
            "Barreno Maniobra" => ["barrenoManiobra", null],
            "Segunda Operacion" => ["segundaOpeSoldadura", null],
            "Soldadura" => ["soldadura", null],
            "Soldadura PTA" => ["soldaduraPTA", null],
            "Rectificado" => ["rectificado", null],
            "Asentado" => ["asentado", null],
            "Calificado" => ["revCalificado", null],
            "Acabado Bombillo" => ["acabadoBombillo", null],
            "Acabado Molde" => ["acabadoMolde", null],
            "Cavidades" => "cavidades",
            "Barreno Profundidad" => ["barrenoProfundidad", null],
            "Copiado" => ["copiado", null],
            "Off Set" => ["offSet", null],
            "Palomas" => ["palomas", null],
            "Rebajes" => ["rebajes", null],
            "Grabado" => ["grabado", null],
            "Operacion Equipo_1" => ["pysOpeSoldadura", 1],
            "Operacion Equipo_2" => ["pysOpeSoldadura", 2],
            "embudoCm" => ["EmbudoCM", null],
        };
        return $procesoName;
    }
    public function obtenerMeta($pieza, $nameClass)
    {
        //Obtener el id_proceso que esta asociado a la pieza
        $processName = str_replace(" ", "_", $pieza->proceso);
        $idString = $processName . "_" . $nameClass . "_" . $pieza->id_ot;
        $meta = $this->get_idMeta($idString, $pieza);
        return $meta->meta;
    }

    public function get_idMeta($idString, $pieza)
    {
        switch ($pieza->proceso) {
            case "Cepillado":
                $id_proceso = Cepillado::where('id_proceso', $idString)->first();
                $piezaFounded = Pza_cepillado::where('id_proceso', $id_proceso->id)->where("n_pieza", $pieza->n_pieza)->first();
                break;
            case "Desbaste Exterior":
                $id_proceso = DesbasteExterior::where('id_proceso', $idString)->first();
                $piezaFounded = Desbaste_pza::where('id_proceso', $id_proceso->id)->where("n_pieza", $pieza->n_pieza)->first();
                break;
            case "Revision Laterales":
                $id_proceso = RevLaterales::where('id_proceso', $idString)->first();
                $piezaFounded = RevLaterales_pza::where('id_proceso', $id_proceso->id)->where("n_pieza", $pieza->n_pieza)->first();
                break;
            case "Primera Operacion":
                $id_proceso = PrimeraOpeSoldadura::where('id_proceso', $idString)->first();
                $piezaFounded = PrimeraOpeSoldadura_pza::where('id_proceso', $id_proceso->id)->where("n_pieza", $pieza->n_pieza)->first();
                break;
            case "Barreno Maniobra":
                $id_proceso = BarrenoManiobra::where('id_proceso', $idString)->first();
                $piezaFounded = BarrenoManiobra_pza::where('id_proceso', $id_proceso->id)->where("n_juego", $pieza->n_pieza)->first();
                break;
            case "Segunda Operacion":
                $id_proceso = SegundaOpeSoldadura::where('id_proceso', $idString)->first();
                $piezaFounded = SegundaOpeSoldadura_pza::where('id_proceso', $id_proceso->id)->where("n_pieza", $pieza->n_pieza)->first();
                break;
            case "Soldadura":
                $id_proceso = Soldadura::where('id_proceso', $idString)->first();
                $piezaFounded = Soldadura_pza::where('id_proceso', $id_proceso->id)->where("n_juego", $pieza->n_pieza)->first();
                break;
            case "Soldadura PTA":
                $id_proceso = SoldaduraPTA::where('id_proceso', $idString)->first();
                $piezaFounded = SoldaduraPTA_pza::where('id_proceso', $id_proceso->id)->where("n_juego", $pieza->n_pieza)->first();
                break;
            case "Rectificado":
                $id_proceso = Rectificado::where('id_proceso', $idString)->first();
                $piezaFounded = Rectificado_pza::where('id_proceso', $id_proceso->id)->where("n_juego", $pieza->n_pieza)->first();
                break;
            case "Asentado":
                $id_proceso = Asentado::where('id_proceso', $idString)->first();
                $piezaFounded = Asentado_pza::where('id_proceso', $id_proceso->id)->where("n_juego", $pieza->n_pieza)->first();
                break;
            case "Calificado":
                $id_proceso = revCalificado::where('id_proceso', $idString)->first();
                $piezaFounded = revCalificado_pza::where('id_proceso', $id_proceso->id)->where("n_pieza", $pieza->n_pieza)->first();
                break;
            case "Acabado Bombillo":
                $id_proceso = AcabadoBombilo::where('id_proceso', $idString)->first();
                $piezaFounded = AcabadoBombilo_pza::where('id_proceso', $id_proceso->id)->where("n_pieza", $pieza->n_pieza)->first();
                break;
            case "Acabado Molde":
                $id_proceso = AcabadoMolde::where('id_proceso', $idString)->first();
                $piezaFounded = AcabadoMolde_pza::where('id_proceso', $id_proceso->id)->where("n_pieza", $pieza->n_pieza)->first();
                break;
            case "Cavidades":
                $id_proceso = Cavidades::where('id_proceso', $idString)->first();
                $piezaFounded = Cavidades_pza::where('id_proceso', $id_proceso->id)->where("n_pieza", $pieza->n_pieza)->first();
                break;
            case "Barreno Profundidad":
                $id_proceso = BarrenoProfundidad::where('id_proceso', $idString)->first();
                $piezaFounded = BarrenoManiobra_pza::where('id_proceso', $id_proceso->id)->where("n_juego", $pieza->n_pieza)->first();
                break;
            case "Copiado":
                $id_proceso = Copiado::where('id_proceso', $idString)->first();
                $piezaFounded = Copiado_pza::where('id_proceso', $id_proceso->id)->where("n_pieza", $pieza->n_pieza)->first();
                break;
            case "Off Set":
                $id_proceso = OffSet::where('id_proceso', $idString)->first();
                $piezaFounded = OffSet_pza::where('id_proceso', $id_proceso->id)->where("n_pieza", $pieza->n_pieza)->first();
                break;
            case "Palomas":
                $id_proceso = Palomas::where('id_proceso', $idString)->first();
                $piezaFounded = Palomas_pza::where('id_proceso', $id_proceso->id)->where("n_juego", $pieza->n_pieza)->first();
                break;
            case "Rebajes":
                $id_proceso = Rebajes::where('id_proceso', $idString)->first();
                $piezaFounded = Rebajes_pza::where('id_proceso', $id_proceso->id)->where("n_juego", $pieza->n_pieza)->first();
                break;
            // case "Grabado":
            //     $id_proceso = Grabado::where('id_proceso', $idString)->first();
            //     break;
            case "Operacion Equipo_1":
            case "Operacion Equipo_2":
                $id_proceso = PySOpeSoldadura::where('id_proceso', $idString)->first();
                $piezaFounded = PySOpeSoldadura_pza::where('id_proceso', $id_proceso->id)->where("n_pieza", $pieza->n_pieza)->first();
                break;
            case "embudoCm":
                $id_proceso = EmbudoCM::where('id_proceso', $idString)->first();
                $piezaFounded = EmbudoCM_pza::where('id_proceso', $id_proceso->id)->where("n_pieza", $pieza->n_pieza)->first();
                break;
        }
        return $meta = Metas::find($piezaFounded->id_meta);
    }
}
