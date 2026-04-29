<?php

namespace App\Jobs;

use App\Mail\BirthdayGreetingMail;
use App\Models\BirthdayDelivery;
use App\Models\BirthdaySetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendBirthdayEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public int $deliveryId)
    {
    }

    public function backoff(): array
    {
        $settings = BirthdaySetting::first();
        $minutes = (int) ($settings?->retry_minutes ?? 10);

        return [$minutes * 60, $minutes * 60, $minutes * 60];
    }

    public function handle(): void
    {
        $delivery = BirthdayDelivery::with(['user', 'template'])->find($this->deliveryId);
        if (! $delivery || $delivery->status === 'sent') {
            return;
        }

        $settings = BirthdaySetting::first();
        $this->tries = (int) ($settings?->max_attempts ?? 3);

        $delivery->attempts = $delivery->attempts + 1;
        $delivery->save();

        Mail::to($delivery->user->email)->send(new BirthdayGreetingMail($delivery));

        $delivery->update([
            'status' => 'sent',
            'sent_at' => now(),
            'error_message' => null,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        $delivery = BirthdayDelivery::find($this->deliveryId);
        if (! $delivery) {
            return;
        }

        $settings = BirthdaySetting::first();
        $maxAttempts = (int) ($settings?->max_attempts ?? 3);
        $attempts = max($delivery->attempts, $this->attempts());

        $delivery->update([
            'status' => $attempts >= $maxAttempts ? 'failed' : 'pending',
            'error_message' => mb_substr($exception->getMessage(), 0, 2000),
        ]);
    }
}
