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
            $backgroundImage = "images/fondoadmin.jpg";
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
                    $backgroundImage = "images/OPE.png";
                    $welcomeT = 'Bienvenido a Producción';
                    break;
                case 3:
                    $layout = "layouts.menu.appMaster";
                    $welcomeT = 'Bienvenido Master';
                    break;
                case 4:
                    $layout = "layouts.menu.appQuality";
                    $backgroundImage = "images/calidad.png";
                    $welcomeT = 'Bienvenido a Control de calidad';
                    $objectiveT = 'En nuestro perfil de calidad, cada milímetro importa. Nos comprometemos a inspeccionar con precisión cada pieza, asegurando medidas exactas y calidad impecable. En la búsqueda constante de la excelencia, nos destacamos por nuestra meticulosidad y compromiso con la perfección.';
                    [$pieces_Released, $info_Pieces] = $this->releasedPiecesController->piecesToBeReleased();
                    break;
                case 5:
                    $layout = "layouts.menu.appWarehouse";
                    $welcomeT = 'Bienvenido a Almacen';
                    break;
            }
            return view('home', compact('layout', 'backgroundImage', 'objectiveT', 'welcomeT', 'pieces_Released', 'info_Pieces'));
        }
    }
}
