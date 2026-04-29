<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Route;

class RoutesIntegrityTest extends TestCase
{
    /**
     * Verificar que todas las rutas registradas apunten a controladores y métodos que EXISTEN.
     */
    public function test_all_routes_have_valid_controllers_and_methods()
    {
        $routes = Route::getRoutes();
        $failures = [];

        foreach ($routes as $route) {
            $action = $route->getAction();

            if (isset($action['controller'])) {
                $controllerAction = $action['controller'];
                
                // Ignorar rutas de vendor si las hay
                if (str_contains($controllerAction, 'Laravel\\')) {
                    continue;
                }

                if (str_contains($controllerAction, '@')) {
                    [$controller, $method] = explode('@', $controllerAction);
                } else {
                    // Controladores invocables (magic __invoke)
                    $controller = $controllerAction;
                    $method = '__invoke';
                }

                if (!class_exists($controller)) {
                    $failures[] = "Ruta [{$route->uri()}] apunta a clase inexistente: $controller";
                    continue;
                }

                if (!method_exists($controller, $method)) {
                    $failures[] = "Ruta [{$route->uri()}] apunta a método inexistente: $controller@$method";
                }
            }
        }

        if (!empty($failures)) {
            $this->fail("Se encontraron rutas rotas:\n" . implode("\n", $failures));
        }
        
        $this->assertTrue(true);
    }
}
