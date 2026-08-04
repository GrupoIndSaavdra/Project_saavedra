<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\SerializesModels;

class DibujoFundicionAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $otName;
    public ?string $fileName;
    public array $ayudas;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $otName, ?string $fileName = null, array $ayudas = [])
    {
        $this->otName = $otName;
        $this->fileName = $fileName;
        $this->ayudas = $ayudas;
    }

    /**
     * Get the message envelope.
     *
     * @return Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Se han subido nuevos Dibujos de Fundición - ' . $this->otName,
        );
    }

    /**
     * Get the message content definition.
     *
     * @return Content
     */
    public function content()
    {
        return new Content(
            view: 'emails.dibujo_fundicion_alert',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        $attachments = [];
        $clasesAFiltrar = array_filter(array_map('trim', $this->ayudas));

        // Buscar directorio fuente en DIBUJOS_FUNDICION o FUNDICION_GIS
        $otPath = null;
        $bases = ['DOCUMENTACION_GIS/DIBUJOS_FUNDICION', 'FUNDICION_GIS'];

        foreach ($bases as $base) {
            if (Storage::disk('local')->exists($base . '/' . $this->otName)) {
                $otPath = $base . '/' . $this->otName;
                break;
            }
            // Búsqueda insensible a mayúsculas si falla la coincidencia exacta
            if (Storage::disk('local')->exists($base)) {
                $dirs = Storage::disk('local')->directories($base);
                foreach ($dirs as $d) {
                    if (strcasecmp(basename($d), $this->otName) === 0) {
                        $otPath = $d;
                        break 2;
                    }
                }
            }
        }

        if ($otPath && Storage::disk('local')->exists($otPath)) {
            $allFiles = Storage::disk('local')->allFiles($otPath);
            foreach ($allFiles as $file) {
                if (strtolower(pathinfo($file, PATHINFO_EXTENSION)) !== 'pdf') {
                    continue;
                }

                $relPath = str_replace(str_replace('\\', '/', $otPath) . '/', '', str_replace('\\', '/', $file));
                $parts = explode('/', $relPath, 2);
                $claseDelArchivo = count($parts) === 2 ? $parts[0] : '';

                // Si hay filtro de clases específicas enviadas desde Ingeniería, solo adjuntar archivos de esas clases
                if (!empty($clasesAFiltrar)) {
                    $matchesClass = false;
                    foreach ($clasesAFiltrar as $claseReq) {
                        if (
                            strcasecmp($claseDelArchivo, $claseReq) === 0 ||
                            str_contains(strtolower($relPath), strtolower($claseReq))
                        ) {
                            $matchesClass = true;
                            break;
                        }
                    }
                    if (!$matchesClass) {
                        continue;
                    }
                }

                $attachments[] = Attachment::fromPath(storage_path('app/' . $file))
                    ->as(basename($file))
                    ->withMime('application/pdf');
            }
        }

        return $attachments;
    }
}
