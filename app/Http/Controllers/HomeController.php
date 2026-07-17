<?php

namespace App\Http\Controllers;

use App\Models\Clase;
use App\Models\Pieza;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /** @var \App\Http\Controllers\PzasLiberadasController */
    protected $releasedPiecesController;
    public function __construct()
    {
        $this->middleware('auth');
        $this->releasedPiecesController = new PzasLiberadasController();
    }
    public function index()
    {
        $perfil = auth()->user()->perfil;
 
        if ($perfil !== null) {
            $backgroundImage = "images/fondoadmin.png";
            $objectiveT = 'Nuestro objetivo es producir moldes de alta calidad para botellas de vidrio que cumplan con las especificaciones de los clientes y sean eficientes en términos de costos de producción.';
            $pieces_Released = [];
            $info_Pieces = [];
            switch (auth()->user()->perfil) {
                case 1:
                    $layout = "layouts.menu.appAdmin";
                    $welcomeT = 'Bienvenido a Administración';
                    break;
                case 2:
                    $layout = "layouts.menu.appProduction";
                    $backgroundImage = "images/operadores.png";
                    $welcomeT = 'Bienvenido a Producción';
                    break;
                case 3:
                    $layout = "layouts.menu.appMaster";
                    $backgroundImage = "images/fondo master.png";
                    $welcomeT = 'Bienvenido Master';
                    break;
                case 4:
                    $layout = "layouts.menu.appQuality";
                    $backgroundImage = "images/fondocalidad.png";
                    $welcomeT = 'Bienvenido a Control de calidad';
                    $objectiveT = 'En nuestro perfil de calidad, cada milímetro importa. Nos comprometemos a inspeccionar con precisión cada pieza, asegurando medidas exactas y calidad impecable. En la búsqueda constante de la excelencia, nos destacamos por nuestra meticulosidad y compromiso con la perfección.';
                    [$pieces_Released, $info_Pieces] = $this->releasedPiecesController->piecesToBeReleased();
                    break;
                case 5:
                    $layout = "layouts.menu.appWarehouse";
                    $backgroundImage = "images/fondoalmacen.png";
                    $welcomeT = 'Bienvenido a Almacen';
                    break;
            }

            // Obtener el listado de prioridades activo
            $workOrders = \App\Models\Orden_trabajo::query()
                ->whereHas('clases', function ($q) {
                    $q->where('finalizada', 0);
                })
                ->with([
                    'clases' => function ($q) {
                        $q->where('finalizada', 0)->select('id', 'id_ot', 'nombre');
                    },
                    'moldura' => function ($q) {
                        $q->select('id', 'nombre');
                    }
                ])
                ->orderByRaw('prioridad IS NULL ASC')
                ->orderBy('prioridad')
                ->orderBy('id')
                ->take(9)
                ->get(['id', 'id_moldura', 'prioridad']);

            $otPriorities = $workOrders->map(function ($ot, $index) {
                return [
                    'ot_id'    => $ot->id,
                    'moldura'  => $ot->moldura ? $ot->moldura->nombre : '—',
                    'clases'   => $ot->clases->pluck('nombre')->toArray(),
                    'prioridad' => $ot->prioridad ?? ($index + 1),
                ];
            })->values()->toArray();

            return view('home', compact('layout', 'backgroundImage', 'objectiveT', 'welcomeT', 'pieces_Released', 'info_Pieces', 'otPriorities'));
        }
    }
}
