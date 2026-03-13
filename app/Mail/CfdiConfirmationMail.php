<?php

namespace App\Mail;

use App\Models\XmlBatch;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CfdiConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $batch;

    public function __construct(XmlBatch $batch)
    {
        $this->batch = $batch;
    }

    public function build()
    {
        return $this->subject('Confirmación de recepción de CFDI')
            ->view('emails.cfdi-confirmation');
    }
}
