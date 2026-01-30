<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AvisoMail extends Mailable
{
    use Queueable, SerializesModels;

    public $asunto;
    public $mensaje;

    public function __construct($asunto, $mensaje)
    {
        $this->asunto = $asunto;
        $this->mensaje = $mensaje;
    }

    public function build()
    {
        return $this->subject($this->asunto)
                    ->view('emails.aviso'); // Vista HTML simple
    }
}