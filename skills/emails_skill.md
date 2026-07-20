# Guía de Emails y Mailables (Emails Skill) — Project Saavedra

> ** Directorio de Referencia:** `app/Mail/ y resources/views/emails/`
> *Usa los archivos en este directorio como base o inspiración al crear/modificar funcionalidades de correo.*

Los correos en `Project_saavedra` se envían vía **Laravel Mailables** usando SMTP configurado en `.env`. Se usan principalmente para alertas operativas: pre-órdenes de fundición, notificaciones de SCARs y confirmaciones de procesos críticos.

---

## 1. Configuración SMTP (`.env`)

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=correo@empresa.com
MAIL_PASSWORD=tu_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=correo@empresa.com
MAIL_FROM_NAME="Sistema GIS Saavedra"
```

> Nunca hardcodees credenciales SMTP en el código. Siempre usa `config('mail.*')` o las variables de entorno.

---

## 2. Creación de un Mailable

```bash
php artisan make:mail AlertaPreOrdenFundicion --markdown=emails.alerta-pre-orden
```

Esto crea:
- `app/Mail/AlertaPreOrdenFundicion.php`
- `resources/views/emails/alerta-pre-orden.blade.php`

---

## 3. Estructura del Mailable (Laravel 9+)

```php
<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\FundicionHistory;

class AlertaPreOrdenFundicion extends Mailable
{
 use Queueable, SerializesModels;

 /**
 * Constructor — Pasar los datos que necesita el email
 */
 public function __construct(
 public readonly FundicionHistory $registro,
 public readonly string $otName,
 public readonly string $responsable,
 ) {}

 /**
 * Asunto del correo
 */
 public function envelope(): Envelope
 {
 return new Envelope(
 subject: " Pre-Orden de Fundición — OT: {$this->otName}",
 );
 }

 /**
 * Vista Blade del correo
 */
 public function content(): Content
 {
 return new Content(
 markdown: 'emails.alerta-pre-orden',
 with: [
 'otName' => $this->otName,
 'responsable' => $this->responsable,
 'registro' => $this->registro,
 ],
 );
 }
}
```

---

## 4. Envío de Correo en el Controlador

```php
use App\Mail\AlertaPreOrdenFundicion;
use Illuminate\Support\Facades\Mail;

public function enviarAlertaPreOrden(Request $request) {
 $registro = FundicionHistory::where('ot', $request->ot)->firstOrFail();

 try {
 // Enviar a un único destinatario
 Mail::to('almacen@saavedra.com')->send(
 new AlertaPreOrdenFundicion($registro, $request->ot, auth()->user()->nombre)
 );

 // Enviar a múltiples destinatarios
 Mail::to(['calidad@saavedra.com', 'gerencia@saavedra.com'])->send(
 new AlertaPreOrdenFundicion($registro, $request->ot, auth()->user()->nombre)
 );

 // Registrar en SystemLog que se envió el correo
 SystemLog::create([
 'user_id' => auth()->id(),
 'user_name' => auth()->user()->nombre,
 'accion' => 'envio_alerta_pre_orden',
 'descripcion' => "Envió alerta de pre-orden para OT '{$request->ot}'",
 'modulo' => 'Almacén Fundición',
 'ip' => $request->ip(),
 ]);

 // Marcar en el registro que el email fue enviado
 $registro->update(['pre_orden_email_sent' => true]);

 return response()->json(['success' => 'Alerta enviada correctamente.']);

 } catch (\Exception $e) {
 Log::error('Fallo al enviar alerta de pre-orden por correo.', [
 'ot' => $request->ot,
 'error' => $e->getMessage(),
 ]);
 return response()->json(['error' => 'No se pudo enviar el correo. Intente más tarde.'], 500);
 }
}
```

---

## 5. Plantilla Blade de Email (Markdown)

```blade
{{-- resources/views/emails/alerta-pre-orden.blade.php --}}
@component('mail::message')
# Pre-Orden de Fundición Generada

Se ha generado una nueva pre-orden de fundición en el sistema GIS Saavedra.

**OT:** {{ $otName }}
**Responsable:** {{ $responsable }}
**Fecha:** {{ now()->translatedFormat('l d \d\e F \d\e Y, h:i A') }}

@component('mail::table')
| Campo | Valor |
|:------|:------|
| OT | {{ $otName }} |
| Estado | {{ $registro->estado ?? 'Pendiente' }} |
| Generado por | {{ $responsable }} |
@endcomponent

Por favor revisa el sistema para continuar con el proceso de fundición.

@component('mail::button', ['url' => url('/almacen/fundicion'), 'color' => 'green'])
Ver en el Sistema
@endcomponent

Gracias,<br>
**Sistema GIS Saavedra**
@endcomponent
```

---

## 6. Plantilla de Email HTML Personalizada (Sin Markdown)

Si necesitas diseño personalizado sin usar el componente Markdown de Laravel:

```blade
{{-- resources/views/emails/custom.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
 <meta charset="UTF-8">
 <style>
 body { font-family: Arial, sans-serif; background: #f4f4f4; }
 .container { max-width: 600px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; }
 .header { background-color: #033966; color: #fff; padding: 20px; border-radius: 8px 8px 0 0; }
 .btn { display: inline-block; background-color: #0a8504; color: #fff; padding: 12px 24px;
 text-decoration: none; border-radius: 6px; font-weight: bold; }
 </style>
</head>
<body>
 <div class="container">
 <div class="header">
 <h2> Sistema GIS Saavedra — Notificación</h2>
 </div>
 <div style="padding: 20px;">
 <p>Hola,</p>
 <p>Se ha generado la pre-orden para la OT: <strong>{{ $otName }}</strong></p>
 <br>
 <a href="{{ url('/almacen/fundicion') }}" class="btn">Ver en el Sistema</a>
 </div>
 </div>
</body>
</html>
```

Para usar vista no-markdown en el Mailable:
```php
public function content(): Content {
 return new Content(view: 'emails.custom');
}
```

---

## 7. Envío en Cola (Queue) para Emails Pesados

Para evitar que el envío de email bloquee la respuesta al usuario (especialmente si se envían múltiples correos):

```php
// En el Mailable — agregar implements ShouldQueue
use Illuminate\Contracts\Queue\ShouldQueue;

class AlertaPreOrdenFundicion extends Mailable implements ShouldQueue
{
 use Queueable, SerializesModels;
 // ... resto igual
}

// En el controlador — se encola automáticamente
Mail::to('destino@ejemplo.com')->queue(new AlertaPreOrdenFundicion($registro, $ot, $nombre));
```

> Las colas requieren `php artisan queue:work` ejecutándose en el servidor. En desarrollo, usa `QUEUE_CONNECTION=sync` en `.env` para envíos síncronos.

---

## 8. Mailables del Proyecto (Referencia Real)

| Clase Mailable | Vista | Trigger |
|---|---|---|
| `AlertaPreOrdenFundicion` | `emails/alerta-pre-orden` | Al generar pre-orden en Almacén |
| *(Otros Mailables del proyecto)* | `emails/` | Revisar `app/Mail/` para lista actualizada |

---

## 9. Verificar Configuración de Mail en Desarrollo

```bash
# Verificar que la configuración de mail está cargada correctamente
php artisan tinker
# Dentro de tinker:
# config('mail.mailers.smtp')
# Mail::raw('Test email', fn($m) => $m->to('test@test.com')->subject('Test'))
```

Para desarrollo, usar **Mailtrap** o **Laravel Sail + Mailpit** para interceptar correos sin enviarlos realmente.
