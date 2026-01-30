<?php

namespace App\Jobs;

use App\Models\XmlBatch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\CfdiConfirmationMail;

class SendConfirmationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $batch;

    public function __construct(XmlBatch $batch)
    {
        $this->batch = $batch;
    }

    public function handle()
    {
        if ($this->batch->user_email) {
            Mail::to($this->batch->user_email)->send(new CfdiConfirmationMail($this->batch));
        }
    }
}