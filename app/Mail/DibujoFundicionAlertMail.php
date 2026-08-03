<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
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
            view: 'emails.casting_drawing_alert',
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
        $dir = 'FUNDICION_GIS/' . $this->otName;

        if (Storage::disk('local')->exists($dir)) {
            $files = Storage::disk('local')->files($dir);
            
            foreach ($files as $file) {
                // Solo adjuntar PDFs
                if (strtolower(pathinfo($file, PATHINFO_EXTENSION)) === 'pdf') {
                    $attachments[] = Attachment::fromPath(storage_path('app/' . $file))
                        ->as(basename($file))
                        ->withMime('application/pdf');
                }
            }
        }

        return $attachments;
    }
}
