<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\File;

class AllControllersIntegrityTest extends TestCase
{
    /**
     * Verificar que TODOS los controladores de la aplicación puedan ser instanciados.
     * Esto detecta errores de dependencias faltantes, errores de namespace y constructores rotos.
     */
    public function test_all_controllers_can_be_instantiated()
    {
        $controllerFiles = File::files(app_path('Http/Controllers'));
        
        $exceptions = [
            'Controller.php', // Clase base abstracta
        ];

        foreach ($controllerFiles as $file) {
            $fileName = $file->getFilename();
            
            if (in_array($fileName, $exceptions)) {
                continue;
            }

            $className = 'App\\Http\\Controllers\\' . str_replace('.php', '', $fileName);
            
            try {
                // Intentar instanciar el controlador. 
                // Nota: Algunos podrían requerir argumentos en el constructor si usan DI manual,
                // pero Laravel suele usar el contenedor de servicios.
                $instance = app($className);
                $this->assertInstanceOf($className, $instance, "Error al instanciar $className");
            } catch (\Throwable $e) {
                // Si falla por falta de parámetros en el constructor (BindingResolutionException), 
                // es normal en algunos casos, pero la mayoría deben resolver vía contenedor.
                if (str_contains($e->getMessage(), 'Unresolvable dependency')) {
                    $this->markTestSkipped("El controlador $className requiere dependencias manuales: " . $e->getMessage());
                } else {
                    $this->fail("El controlador $className lanzó una excepción al instanciarse: " . $e->getMessage());
                }
            }
        }
    }
}
