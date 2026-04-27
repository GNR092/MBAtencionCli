<?php

namespace Canva\HBD\Mails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;

class HbdMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $nombre;

    public string $renderedHtml;

    public function __construct(string $nombre, string $renderedHtml)
    {
        $this->nombre = $nombre;
        $this->renderedHtml = $renderedHtml;
    }

    public function build(): self
    {
        $subjectTemplate = config('hbd_settings.subject_template', '¡Feliz cumpleaños, {nombre}!');
        $subject = str_replace('{nombre}', $this->nombre, $subjectTemplate);

        return $this->subject($subject)
            ->html($this->renderedHtml);
    }
}
