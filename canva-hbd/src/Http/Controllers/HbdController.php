<?php

namespace Canva\HBD\Http\Controllers;

use App\Http\Controllers\Controller;
use Canva\HBD\Mails\HbdMail;
use Canva\HBD\Models\HbdSent;
use Canva\HBD\Models\HbdSetting;
use Canva\HBD\Models\HbdTemplate;
use Canva\HBD\Services\HbdCanvasSaver;
use Canva\HBD\Services\HbdTemplateRenderer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class HbdController extends Controller
{
    public function index(): View
    {
        $userClass = config('hbd.user_class', 'App\\Models\\User');
        $birthdayField = config('hbd.birthday_field', 'fecha_nacimiento');

        $users = $userClass::where('role', 'usuario')
            ->whereNotNull($birthdayField)
            ->get()
            ->map(function ($user) use ($birthdayField) {
                $cumple = \Carbon\Carbon::parse($user->{$birthdayField})->setYear(now()->year);
                if ($cumple->lt(now()->startOfDay())) {
                    $cumple->addYear();
                }
                $user->dias_para_cumple = (int) now()->startOfDay()->diffInDays($cumple->startOfDay());
                $user->edad = (int) now()->year - (int) \Carbon\Carbon::parse($user->{$birthdayField})->year;
                $user->es_hoy = $cumple->isToday();
                $user->fecha_nacimiento = $user->{$birthdayField};
                return $user;
            })
            ->sortBy('dias_para_cumple');

        $settings = HbdSetting::getOrCreate();

        $esteMes = $users->filter(fn ($u) => \Carbon\Carbon::parse($u->fecha_nacimiento)->month === now()->month);
        $proximoMes = $users->filter(fn ($u) => \Carbon\Carbon::parse($u->fecha_nacimiento)->month === ((now()->month % 12) + 1));
        $restantes = $users->reject(fn ($u) => in_array(\Carbon\Carbon::parse($u->fecha_nacimiento)->month, [now()->month, (now()->month % 12) + 1]));

        $template = HbdTemplate::getActive();

        return view('hbd::hbd.index', compact(
            'users',
            'esteMes',
            'proximoMes',
            'restantes',
            'settings',
            'template'
        ));
    }

    public function settings(): View
    {
        $settings = HbdSetting::getOrCreate();
        $template = HbdTemplate::getActive();

        return view('hbd::hbd.settings', compact('settings', 'template'));
    }

    public function saveSettings(Request $request): JsonResponse
    {
        $settings = HbdSetting::getOrCreate();

        $validated = $request->validate([
            'auto_send' => 'boolean',
            'send_days_before' => 'integer|min:0|max:30',
            'send_hour' => 'string',
            'subject_template' => 'string|max:255',
            'is_active' => 'boolean',
        ]);

        $settings->fill($validated);
        $settings->save();

        return response()->json(['success' => true, 'message' => 'Configuración guardada.']);
    }

    public function canvas(): View
    {
        $template = HbdTemplate::getActive();

        if (!$template) {
            $template = HbdTemplate::create([
                'name' => 'Feliz Cumpleaños',
                'slug' => 'feliz-cumpleanos',
                'content' => HbdTemplate::getDefaultContent(),
                'is_active' => true,
            ]);
        }

        return view('hbd::hbd.canvas', compact('template'));
    }

    public function saveCanvas(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|array',
            'thumbnail' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $saver = new HbdCanvasSaver();
        $template = $saver->save($request->all(), $request->input('id'));

        return response()->json([
            'success' => true,
            'message' => 'Plantilla guardada.',
            'template' => [
                'id' => $template->id,
                'name' => $template->name,
                'is_active' => $template->is_active,
            ],
        ]);
    }

    public function canvasPreview(Request $request): View
    {
        $content = $request->input('content');
        $nombre = $request->input('nombre', 'Nombre del Cumpleañero');
        $device = $request->input('device', 'desktop');

        $sections = [];
        if ($content) {
            $decoded = is_string($content) ? json_decode($content, true) : $content;
            $sections = $decoded[$device] ?? $decoded['desktop'] ?? [];
        }

        return view('hbd::hbd.canvas-preview', compact('sections', 'nombre', 'device'));
    }

    public function enviar(Request $request, int $userId): JsonResponse
    {
        $userClass = config('hbd.user_class', 'App\\Models\\User');
        $user = $userClass::findOrFail($userId);

        $template = HbdTemplate::getActive();

        if (!$template) {
            return response()->json(['success' => false, 'message' => 'No hay plantilla activa.'], 422);
        }

        if (HbdSent::wasSentToday($user->id)) {
            return response()->json(['success' => false, 'message' => 'Ya se envió un correo hoy a este usuario.'], 422);
        }

        try {
            $renderer = new HbdTemplateRenderer();
            $renderedHtml = $renderer->renderForUser($template, $user->name);

            Mail::to($user->email)->send(new HbdMail($user->name, $renderedHtml));

            HbdSent::create([
                'user_id' => $user->id,
                'hbd_template_id' => $template->id,
                'sent_date' => now()->toDateString(),
                'recipient_email' => $user->email,
                'rendered_html' => $renderedHtml,
            ]);

            return response()->json(['success' => true, 'message' => "Correo enviado a {$user->name}."]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Error al enviar: ' . $e->getMessage()], 500);
        }
    }

    public function enviarTest(Request $request, int $userId): JsonResponse
    {
        $userClass = config('hbd.user_class', 'App\\Models\\User');
        $user = $userClass::findOrFail($userId);

        $template = HbdTemplate::getActive();

        if (!$template) {
            return response()->json(['success' => false, 'message' => 'No hay plantilla activa.'], 422);
        }

        try {
            $renderer = new HbdTemplateRenderer();
            $renderedHtml = $renderer->renderForUser($template, $user->name);

            Mail::to($user->email)->send(new HbdMail($user->name, $renderedHtml));

            return response()->json(['success' => true, 'message' => "Correo de prueba enviado a {$user->email}."]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Error al enviar: ' . $e->getMessage()], 500);
        }
    }

    public function uploadMedia(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|image|mimes:jpg,jpeg,png,gif,svg|max:5120',
        ]);

        try {
            $saver = new HbdCanvasSaver();
            $path = $saver->saveMedia($request->file('file'));

            return response()->json([
                'success' => true,
                'url' => asset("storage/{$path}"),
                'path' => $path,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function historial(Request $request): View
    {
        $sents = HbdSent::with(['user', 'template'])
            ->orderByDesc('sent_date')
            ->orderByDesc('created_at')
            ->paginate(30);

        return view('hbd::hbd.historial', compact('sents'));
    }
}
