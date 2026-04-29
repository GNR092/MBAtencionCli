<?php

namespace App\Http\Controllers;

use App\Jobs\SendBirthdayEmailJob;
use App\Models\BirthdayDelivery;
use App\Models\BirthdaySetting;
use App\Models\BirthdayTemplate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BirthdayCampaignController extends Controller
{
    public function templateEditor()
    {
        $template = BirthdayTemplate::where('is_active', true)->latest()->first() ?? BirthdayTemplate::latest()->first();

        return view('cumpleanios-template', compact('template'));
    }

    public function saveTemplate(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'default_message' => 'nullable|string',
            'zones_json' => 'required|json',
            'background' => 'nullable|image|max:4096',
            'remove_background' => 'nullable|boolean',
            'overlay_images' => 'nullable|array',
            'overlay_images.*' => 'image|max:4096',
            'canvas_width' => 'nullable|integer|min:400|max:2000',
            'canvas_height' => 'nullable|integer|min:300|max:1500',
        ]);

        $template = BirthdayTemplate::where('is_active', true)->latest()->first() ?? new BirthdayTemplate();
        $template->name = $data['name'];
        $template->default_message = $data['default_message'] ?? '';
        $template->zones_json = json_decode($data['zones_json'], true, 512, JSON_THROW_ON_ERROR);
        $template->canvas_width = $data['canvas_width'] ?? 960;
        $template->canvas_height = $data['canvas_height'] ?? 540;

        if ($request->boolean('remove_background') && $template->background_path) {
            Storage::disk('public')->delete($template->background_path);
            $template->background_path = null;
        }

        if ($request->hasFile('background')) {
            if ($template->background_path) {
                Storage::disk('public')->delete($template->background_path);
            }
            $template->background_path = $request->file('background')->store('birthday_templates', 'public');
        }

        $overlayImages = json_decode($request->input('overlay_images_json', '[]'), true, 512, JSON_THROW_ON_ERROR);
        $newOverlayImages = [];

        if ($request->hasFile('overlay_images')) {
            foreach ($request->file('overlay_images') as $idx => $file) {
                $path = $file->store('birthday_templates/overlays', 'public');
                $newOverlayImages[] = [
                    'path' => $path,
                    'x' => $overlayImages[$idx]['x'] ?? 0,
                    'y' => $overlayImages[$idx]['y'] ?? 0,
                    'width' => $overlayImages[$idx]['width'] ?? 200,
                    'height' => $overlayImages[$idx]['height'] ?? 200,
                    'rotation' => $overlayImages[$idx]['rotation'] ?? 0,
                    'scaleX' => $overlayImages[$idx]['scaleX'] ?? 1,
                    'scaleY' => $overlayImages[$idx]['scaleY'] ?? 1,
                    'originalFilename' => $file->getClientOriginalName(),
                ];
            }
        } else {
            $newOverlayImages = $overlayImages;
        }

        $template->overlay_images = $newOverlayImages;
        $template->is_active = true;
        $template->save();

        BirthdayTemplate::where('id', '!=', $template->id)->update(['is_active' => false]);

        return redirect()->route('cumpleanios.template')->with('success', 'Plantilla guardada correctamente.');
    }

    public function settings()
    {
        $settings = BirthdaySetting::firstOrCreate([], [
            'send_time' => '09:00:00',
            'timezone' => 'America/Mexico_City',
            'max_attempts' => 3,
            'retry_minutes' => 10,
        ]);

        return view('cumpleanios-settings', compact('settings'));
    }

    public function saveSettings(Request $request)
    {
        $data = $request->validate([
            'send_time' => 'required|date_format:H:i',
            'timezone' => 'required|string|max:100',
            'max_attempts' => 'required|integer|min:1|max:10',
            'retry_minutes' => 'required|integer|min:1|max:180',
        ]);

        $settings = BirthdaySetting::firstOrCreate([]);
        $settings->update($data);

        return redirect()->route('cumpleanios.settings')->with('success', 'Configuracion actualizada.');
    }

    public function deliveries()
    {
        $deliveries = BirthdayDelivery::with('user')
            ->latest()
            ->paginate(20);

        return view('cumpleanios-deliveries', compact('deliveries'));
    }

    public function retryDelivery(BirthdayDelivery $delivery)
    {
        if ($delivery->status !== 'failed') {
            return back()->with('error', 'Solo se pueden reintentar envios fallidos.');
        }

        $delivery->update([
            'status' => 'pending',
            'error_message' => null,
            'attempts' => 0,
        ]);

        SendBirthdayEmailJob::dispatch($delivery->id);

        return back()->with('success', 'Reintento enviado a cola.');
    }

    public function enqueueTodayBirthdays(): void
    {
        $template = BirthdayTemplate::where('is_active', true)->first();
        if (! $template) {
            return;
        }

        $today = now();
        $settings = BirthdaySetting::first();
        if (! $settings) {
            return;
        }

        $users = User::query()
            ->where('role', 'usuario')
            ->whereNotNull('fecha_nacimiento')
            ->whereNotNull('email')
            ->whereMonth('fecha_nacimiento', $today->month)
            ->whereDay('fecha_nacimiento', $today->day)
            ->get();

        foreach ($users as $user) {
            $delivery = BirthdayDelivery::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'birthday_date' => $today->toDateString(),
                ],
                [
                    'template_id' => $template->id,
                    'scheduled_for' => $today,
                    'status' => 'pending',
                ]
            );

            if ($delivery->wasRecentlyCreated || $delivery->status === 'failed') {
                SendBirthdayEmailJob::dispatch($delivery->id);
            }
        }
    }
}