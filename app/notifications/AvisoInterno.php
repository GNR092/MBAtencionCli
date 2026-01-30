<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class AvisoInterno extends Notification
{
    use Queueable;

    protected $asunto;
    protected $mensaje;

    public function __construct($asunto, $mensaje)
    {
        $this->asunto = $asunto;
        $this->mensaje = $mensaje;
    }

    // Define los canales: interno (database) y opcionalmente correo
    public function via($notifiable)
    {
        return property_exists($this, 'sendMail') && $this->sendMail
            ? ['database', 'mail']
            : ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'asunto' => $this->asunto,
            'mensaje' => $this->mensaje,
        ];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject($this->asunto)
                    ->line($this->mensaje)
                    ->line('Gracias por usar nuestro sistema.');
    }
}