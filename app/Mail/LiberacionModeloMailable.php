<?php

namespace App\Mail;

use App\Models\LiberacionModeloFundicion;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LiberacionModeloMailable extends Mailable
{
    use Queueable, SerializesModels;

    public string $ot;
    public string $estado;          // 'aprobado' | 'rechazado'
    public ?string $motivoRechazo;
    public ?string $userCalidad;
    public ?string $observaciones;
    public array $emailAttachments;

    /**
     * @param string                        $ot
     * @param string                        $estado         'aprobado' o 'rechazado'
     * @param LiberacionModeloFundicion|null $liberacion
     */
    public function __construct(
        string $ot,
        string $estado,
        ?LiberacionModeloFundicion $liberacion = null,
        array $attachments = []
    ) {
        $this->ot            = $ot;
        $this->estado        = $estado;
        $this->motivoRechazo = $liberacion?->motivo_rechazo;
        $this->observaciones = $liberacion ? (match ($liberacion->tipo_modelo) {
            'Molde', 'Bombillo' => trim(($liberacion->observaciones_modelo ?? '') . ' ' . ($liberacion->observaciones_plantilla ?? '')),
            'Fondo' => $liberacion->observaciones_fondo,
            'Obturador' => $liberacion->observaciones_obturador,
            default => null,
        }) : null;
        $this->userCalidad   = $liberacion?->user_nombre_calidad;
        $this->emailAttachments = $attachments;
    }

    public function envelope(): Envelope
    {
        $accion = $this->estado === 'aprobado' ? 'APROBADA' : 'RECHAZADA';
        return new Envelope(
            subject: "Liberacion de Modelo [{$accion}] - {$this->ot}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.model_release',
        );
    }

    public function attachments(): array
    {
        $atts = [];
        foreach ($this->emailAttachments as $att) {
            $atts[] = \Illuminate\Mail\Mailables\Attachment::fromPath($att['path'])
                        ->as($att['name'])
                        ->withMime($att['mime']);
        }
        return $atts;
    }
}
