<?php

namespace App\Mail;

use App\Models\BirthdayDelivery;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BirthdayGreetingMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public BirthdayDelivery $delivery)
    {
    }

    public function build()
    {
        return $this->subject('Feliz cumpleanos, '.$this->delivery->user->name)
            ->view('emails.birthday-greeting');
    }
}
