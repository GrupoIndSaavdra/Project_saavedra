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
