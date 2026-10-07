# Habilidad de No Pruebas (No Testing Rule)

> ** Directorio de Referencia:** `(Aplicable a todo el flujo de pruebas/verificación manual)`
> *Usa los archivos en este directorio como base o inspiración al crear/modificar funcionalidades relacionadas con esta skill.*


Esta regla define el alcance del agente IA en relación con las pruebas de interfaz de usuario y del navegador, así como las pruebas que sí están permitidas para el desarrollo.

---

## Límite estricto de pruebas en Navegador
* **El agente NO debe realizar pruebas en el navegador:** Queda totalmente prohibido iniciar subagentes de navegador (`browser_subagent`), interactuar con el DOM del navegador en tiempo real para validar flujos, o realizar capturas de pantalla para validar si el HTML/JS funciona de manera interactiva.
* **El desarrollador (USER) se encarga de las pruebas en vivo:** Toda la validación en el navegador web, clics, pruebas de usuario y aserciones visuales de interfaz las realiza manualmente el usuario.
* **Foco de desarrollo del agente:** El agente debe concentrarse en escribir código limpio, controladores correctos, vistas blade bien maquetadas, estilos pulidos y lógica de base de datos robusta.

---

## Pruebas de Backend Permitidas y Recomendadas
Aunque las pruebas en navegador están prohibidas para el agente, el agente **SÍ** puede y debe usar los mecanismos de verificación de backend de Laravel para asegurar la calidad del código:

1. **Validación de Sintaxis PHP:**
 Antes de considerar una tarea finalizada, puedes probar sintaxis de forma masiva:
 ```bash
 php -l app/Http/Controllers/EjemploController.php
 ```
2. **Pruebas Unitarias e Integración (PHPUnit):**
 Puedes crear y correr pruebas de HTTP o consola para endpoints críticos si el proyecto cuenta con infraestructura de pruebas:
 ```bash
 php artisan test
 ```
3. **Pruebas de Rutas e Integridad de Rutas:**
 Asegúrate de que las nuevas rutas no entren en conflicto con las existentes compilando o listando la caché de rutas:
 ```bash
 php artisan route:list
 ```
4. **Construcción y Compilación de Assets:**
 Prueba que los estilos y scripts compilen sin errores con Vite:
 ```bash
 npm run build
 ```

El agente debe asegurarse de dejar el código listo, compilado y sintácticamente impecable para que el usuario solo tenga que realizar pruebas en el navegador.

---

## Pruebas de Envío de Correos (Mailables y SMTP)
Para verificar la correcta integración con el servidor de correos (SMTP, Mailables y adjuntos PDF/imágenes), puedes utilizar el siguiente script desde Tinker sin necesidad de usar el navegador:

```php
// Crea un archivo temporal test_mails.php y ejecútalo con `php artisan tinker test_mails.php`
<?php
use Illuminate\Support\Facades\Mail;
use App\Mail\PtaReporteMail;
use App\Mail\DibujoFundicionAlertMail;
use App\Mail\LiberacionModeloMailable;
use App\Models\LiberacionModeloFundicion;

$email = 'correo_de_prueba@ejemplo.com';

try {
    // 1. PtaReporteMail
    $dummyPdf = storage_path('app/dummy.pdf');
    file_put_contents($dummyPdf, 'dummy content');
    Mail::to($email)->send(new PtaReporteMail("OT #999 - Prueba", "Clase Test", $dummyPdf));

    // 2. DibujoFundicionAlertMail
    Mail::to($email)->send(new DibujoFundicionAlertMail("OT #999 - Prueba", "Test File", ["clase 1"]));

    // 3. LiberacionModeloMailable
    $lib = new LiberacionModeloFundicion();
    $lib->motivo_rechazo = "Test de rechazo";
    $lib->tipo_modelo = "Molde";
    $lib->observaciones_modelo = "Obs modelo";
    $lib->user_nombre_calidad = "Test QA";
    Mail::to($email)->send(new LiberacionModeloMailable("OT #999 - Prueba", "rechazado", $lib, []));

    // 4. Raw Mail (Almacén pre-órdenes)
    Mail::send([], [], function ($message) use ($email) {
        $message->to($email)
            ->subject('Alerta Pre-Orden (Prueba)')
            ->html('<h1>Esto es una prueba de envío generada para Almacén.</h1>');
    });

    @unlink($dummyPdf);
    echo "ALL_EMAILS_SUCCESS\n";
} catch (\Exception $e) {
    echo "ERROR_SENDING: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine();
}
```
Este método permite diagnosticar inmediatamente si falta alguna variable en la plantilla de Blade o si existe un problema de autenticación SMTP.

---

## Pruebas de Controladores, Modelos y ViewModels sin Navegador
Para depurar y validar el comportamiento de Controladores o ViewModels complejos sin necesidad del navegador, puedes crear un script temporal en la raíz del proyecto y ejecutarlo con `php test_script.php`.

### 1. Probando un ViewModel
Puedes instanciar un ViewModel, pasarle un modelo Eloquent y hacer print de sus propiedades calculadas:

```php
// test_script.php
<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = \App\Models\User::first();
\Illuminate\Support\Facades\Auth::login($user); // Autenticación de prueba

$reg = \App\Models\FundicionHistory::where('ot', 'OT 9993 - SALIME 60 ML_BOMBILLO_R1')->first();
$vm = new \App\ViewModels\CalidadTableRowViewModel($reg, 'activa', 'Calidad');

echo "ACTIVE CLASSES:\n";
print_r($vm->activeClassesForOt);
echo "ARCHIVOS:\n";
print_r($vm->archivos);
```

### 2. Probando un Request a un Controlador
Puedes instanciar un Controller, simular una petición HTTP con un Request, y decodificar el JSON de respuesta:

```php
// test_getFiles.php
<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

Auth::loginUsingId(1); // Login como admin

$controller = new \App\Http\Controllers\CalidadFundicionController();
$request = new \Illuminate\Http\Request();
$request->merge(['ot' => 'OT 9993 - SALIME 60 ML_BOMBILLO_R1']);

$response = $controller->getFiles($request);
$data = json_decode($response->getContent(), true);

foreach ($data['archivos'] as $archivo) {
    echo $archivo['tipo'] . " -> " . $archivo['nombre'] . "\n";
}
```

### 3. Probando Lógica y Consultas (Modelos y Expresiones Regulares)
Puedes validar lógica de Regex o consultas de Eloquent encadenadas:

```php
// test_path.php
<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$ot = 'OT 9993 - SALIME 60 ML_BOMBILLO_R1';
$baseOt = preg_replace('/_(?:(?:candado\s+obturador|...)*_)?R\d+$/iu', '', $ot);

echo "Base OT: " . $baseOt . "\n";

$history = \App\Models\FundicionHistory::where('ot', '=', $ot, 'and')->first();
if (!$history) {
    $history = \App\Models\FundicionHistory::where('ot', '=', $baseOt, 'and')->first();
}
// etc...
```

---

## Verificación de Assets Frontend (Vite)

Antes de considerar un cambio CSS/JS como terminado, verifica que compila sin errores:

```powershell
# Verificar compilación de CSS y JS sin errores
npm run build 2>&1

# Si hay un SyntaxError en JS, Vite mostrará la línea exacta del problema
# Ver javascript_skill.md § 10 sobre escapes inválidos que rompen esbuild
```

> **Nota:** Si el build tiene errores de sintaxis JS (ej. `\\'` dentro de template literals), el servidor `npm run dev` seguirá funcionando en cache hasta que lo reinicies. **Siempre verifica con `npm run build`** antes de reportar que "funciona".

---

## Checklist de Verificación Final (Antes de Cerrar una Tarea)

- [ ] ¿Se verificó la sintaxis PHP de los controladores modificados? (`php -l archivo.php`)
- [ ] ¿Se verificó que las rutas no tienen conflictos? (`php artisan route:list`)
- [ ] ¿Se limpiaron las cachés? (`php artisan optimize:clear`)
- [ ] ¿Se verificó que Vite compila sin errores? (`npm run build`)
- [ ] ¿Se documentó en la skill correspondiente si se descubrió un nuevo patrón?


## Archivos de Prueba Temporales (Scratchpads)
Los scripts sueltos o archivos de prueba (ej. 	est_*.php, script.php, etc.) utilizados para depuración rápida de arrays, modelos o funciones (como 	est_diff.php o 	est_calidad_ayudas.php) **DEBEN ELIMINARSE** tan pronto como el problema esté resuelto o la tarea concluida. No dejes scripts basura en la raíz del proyecto. El historial del repositorio debe mantenerse limpio.
