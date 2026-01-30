<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use App\Mail\AvisoMail;
use App\Models\User;
use App\Notifications\AvisoInterno;
use Illuminate\Notifications\DatabaseNotification;

class AvisoController extends Controller
{
    /**
     * Returns the count of unread notifications for the authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function unreadCount(Request $request)
    {
        $userSession = session('user');
        if (!$userSession) {
            return response()->json(['count' => 0], 401); // Unauthorized or not logged in
        }

        $user = User::find($userSession->id);

        if (!$user) {
            return response()->json(['count' => 0], 404); // User not found in DB
        }

        return response()->json(['count' => $user->unreadNotifications->count()]);
    }

    public function delete( $id){
        $user = session('user'); 
        if (!$user) {
            return redirect('/inicio-de-sesion');
        }

        $usuario = User::find($user->id);
        $notificacion = $usuario->notifications()->where('id', $id)->first();

        if ($notificacion) {
            $notificacion->delete();
        }

        return redirect()->back()->with('success', 'Notificación borrada.');


    }
    // Mostrar notificaciones internas del usuario
    public function index(Request $request)
    {
        $user = session('user'); 
        if (!$user) {
            return redirect('/inicio-de-sesion');
        }

        $usuario = User::find($user->id);
        $nuevas = $usuario->unreadNotifications;
        $antiguas = $usuario->readNotifications;

        $hasNotifications = $nuevas->count() > 0;

        if ($request->expectsJson()) {
            $html = view('notificaciones', compact('nuevas', 'antiguas','hasNotifications'))->render();
            return response()->json(['html' => $html]);
        }

        return view('notificaciones', compact('nuevas', 'antiguas','hasNotifications'));
    }

    // Marcar notificación como leída
    public function markAsRead(Request $request, $id) // Added Request $request
    {
        $user = session('user'); 
        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }
            return redirect('/inicio-de-sesion');
        }

        $usuario = User::find($user->id);
        $notificacion = $usuario->notifications()->where('id', $id)->first();

        if (!$notificacion) { // Added check for notification existence
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Notification not found'], 404);
            }
            return redirect()->back()->withErrors('Notificación no encontrada.');
        }

        if ($notificacion->read_at === null) { // Only mark as read if it's unread
            $notificacion->markAsRead();
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Notification marked as read']);
        }

        return redirect()->back()->with('success', 'Notificación marcada como leída.');
    }

    // Enviar aviso
    public function store(Request $request)
    {
        $request->validate([
            'usuario'     => 'nullable|string',
            'asunto'      => 'required|string|max:255',
            'mensaje'     => 'required|string',
            'canales'     => 'required|array',
            'proyect'     => 'nullable|array',
            'proyect.*'   => 'in:RESIDENT 1,RESIDENT 2,CAMPUS RECIDENCIA,TMZN 122,GRAND TEMOZON,MB RESORT MERIDA,Princess Village,Royal Square Plaza,RUM,Avenue Temozon,MB Resort Orlando,MB Wellness Resort,Aldea Borboleta I,Aldea Borboleta II,Aldea Borboleta III',
            'canales.*'   => 'in:interno,correo,whatsapp',
        ]);

        // 1) Resolver a qué usuarios enviar
        if ($request->boolean('todos')) {
            $usuarios = User::all();
            if ($usuarios->isEmpty()) {
                return back()->withErrors(['usuarios' => 'No hay usuarios para enviar.']);
            }
        } elseif ($request->filled('proyect')) {
            // Filtrar por proyecto (campo JSON)
            $usuarios = User::where(function ($q) use ($request) {
                foreach ($request->proyect as $p) {
                    $q->orWhereJsonContains('proyect', $p);
                }
            })->get();

            if ($usuarios->isEmpty()) {
                return back()->withErrors(['usuarios' => 'No se encontraron usuarios en el/los proyecto(s) seleccionado(s).']);
            }
        } else {
            // Buscar un usuario específico
            $input = $request->usuario;
            $usuario = null;

            if (is_numeric($input)) {
                $usuario = User::find($input);
            }

            if (!$usuario) {
                $usuario = User::where('name', 'like', "%{$input}%")
                               ->orWhere('email', $input)
                               ->first();
            }

            if (!$usuario) {
                return back()->withErrors(['usuario' => 'Usuario no encontrado (por ID, nombre o email).']);
            }

            $usuarios = collect([$usuario]);
        }

        // 2) Preparar contadores y configuración de WhatsApp
        $contadores = [
            'interno'            => 0,
            'correo'             => 0,
            'whatsapp'           => 0,
            'omitidos_whatsapp'  => 0,
            'omitidos_correo'    => 0,
        ];

        $waPhoneId  = env('WHATSAPP_PHONE_ID');
        $waToken    = env('WHATSAPP_ACCESS_TOKEN');
        $waUrl      = $waPhoneId ? "https://graph.facebook.com/v17.0/{$waPhoneId}/messages" : null;

        $usarInterno  = in_array('interno', $request->canales, true);
        $usarCorreo   = in_array('correo', $request->canales, true);
        $usarWhatsApp = in_array('whatsapp', $request->canales, true);

        // 3) Enviar por cada usuario
        foreach ($usuarios as $u) {
            // Interno
            if ($usarInterno) {
                try {
                    $u->notify(new AvisoInterno($request->asunto, $request->mensaje));
                    $contadores['interno']++;
                } catch (\Throwable $e) {
                    report($e);
                }
            }

            // Correo
            if ($usarCorreo) {
                try {
                    if (!empty($u->email)) {
                        Mail::to($u->email)->send(new AvisoMail($request->asunto, $request->mensaje));
                        $contadores['correo']++;
                    } else {
                        $contadores['omitidos_correo']++;
                    }
                } catch (\Throwable $e) {
                    report($e);
                }
            }

            // WhatsApp Business API
            if ($usarWhatsApp) {
                try {
                    if ($waUrl && $waToken && !empty($u->phone)) {
                        $payload = [
                            'messaging_product' => 'whatsapp',
                            'to' => $u->phone,
                            'type' => 'template',
                            'template' => [
                                'name' => 'aviso_atencion_inversionistas',
                                'language' => ['code' => 'es_MX'],
                                'components' => [
                                    [
                                        'type' => 'body',
                                        'parameters' => [
                                            ['type' => 'text', 'text' => $request->asunto],
                                            ['type' => 'text', 'text' => $request->mensaje]
                                        ]
                                    ]
                                ]
                            ]
                        ];

                        $resp = Http::withToken($waToken)
                                    ->acceptJson()
                                    ->post($waUrl, $payload);

                        if ($resp->successful()) {
                            $contadores['whatsapp']++;
                        } else {
                            report(new \Exception('WA error: '.$resp->status().' '.$resp->body()));
                            $contadores['omitidos_whatsapp']++;
                        }
                    } else {
                        $contadores['omitidos_whatsapp']++;
                    }
                } catch (\Throwable $e) {
                    report($e);
                    $contadores['omitidos_whatsapp']++;
                }
            }
        }

        // 4) Mensaje de resumen
        $resumen = [];
        if ($usarInterno)  { $resumen[] = "Interno: {$contadores['interno']} enviados"; }
        if ($usarCorreo)   { 
            $extra = $contadores['omitidos_correo'] ? " (omitidos sin email: {$contadores['omitidos_correo']})" : '';
            $resumen[] = "Correo: {$contadores['correo']} enviados{$extra}"; 
        }
        if ($usarWhatsApp) { 
            $extra = $contadores['omitidos_whatsapp'] ? " (errores/omitidos: {$contadores['omitidos_whatsapp']})" : '';
            $resumen[] = "WhatsApp: {$contadores['whatsapp']} enviados{$extra}";
        }

        return back()->with('success', 'Aviso enviado. '.implode(' | ', $resumen));
    }
}
