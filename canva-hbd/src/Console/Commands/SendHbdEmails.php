<?php

namespace Canva\HBD\Console\Commands;

use Canva\HBD\Mails\HbdMail;
use Canva\HBD\Models\HbdSent;
use Canva\HBD\Models\HbdSetting;
use Canva\HBD\Models\HbdTemplate;
use Canva\HBD\Services\HbdTemplateRenderer;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendHbdEmails extends Command
{
    protected $signature = 'hbd:send {--dry-run : No envía realmente, solo muestra qué pasaría}';

    protected $description = 'Envía correos de cumpleaños a los usuarios que cumplen años';

    public function handle(): int
    {
        $settings = HbdSetting::getOrCreate();

        if (!$settings->is_active) {
            $this->info('El sistema de cumpleaños está desactivado.');
            return Command::SUCCESS;
        }

        if (!$settings->auto_send && !$this->option('dry-run')) {
            $this->info('El envío automático está desactivado. Usa --dry-run para ver qué pasaría.');
            return Command::SUCCESS;
        }

        $template = HbdTemplate::getActive();

        if (!$template) {
            $this->error('No hay plantilla activa configurada.');
            return Command::FAILURE;
        }

        $userClass = config('hbd.user_class', 'App\\Models\\User');
        $birthdayField = config('hbd.birthday_field', 'fecha_nacimiento');

        $targetDate = now()->addDays($settings->send_days_before);
        $month = $targetDate->month;
        $day = $targetDate->day;

        $allUsers = $userClass::whereNotNull($birthdayField)->get();

        $users = $allUsers->filter(function ($user) use ($birthdayField, $month, $day) {
            $birthday = $user->{$birthdayField};
            if (!$birthday) return false;
            $date = \Carbon\Carbon::parse($birthday);
            return $date->month === $month && $date->day === $day;
        })->values();

        if ($users->isEmpty()) {
            $this->info("No hay cumpleañeros para el {$targetDate->isoFormat('D [de] MMMM')}.");
            return Command::SUCCESS;
        }

        $renderer = new HbdTemplateRenderer();
        $sent = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($users as $user) {
            if (HbdSent::wasSentToday($user->id)) {
                $skipped++;
                if ($this->option('dry-run')) {
                    $this->line("  [SKIP] {$user->name} ({$user->email}) - ya enviado hoy");
                }
                continue;
            }

            if ($this->option('dry-run')) {
                $this->line("  [WOULD SEND] {$user->name} ({$user->email})");
                $sent++;
                continue;
            }

            try {
                $renderedHtml = $renderer->renderForUser($template, $user->name);

                Mail::to($user->email)->send(new HbdMail($user->name, $renderedHtml));

                HbdSent::create([
                    'user_id' => $user->id,
                    'hbd_template_id' => $template->id,
                    'sent_date' => now()->toDateString(),
                    'recipient_email' => $user->email,
                    'rendered_html' => $renderedHtml,
                ]);

                $sent++;
            } catch (\Throwable $e) {
                Log::error("HBD: Error enviando a {$user->email}: " . $e->getMessage());
                $failed++;
            }
        }

        $this->info("Resultado: {$sent} enviados, {$skipped} omitidos (ya enviados), {$failed} fallidos.");

        if ($this->option('dry-run')) {
            $this->info('(--dry-run: No se envió ningún correo)');
        }

        return Command::SUCCESS;
    }
}
