<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class UserChatController extends Controller
{
    public function getMessages()
    {
        $userId = Session::get('user')->id;
        $adminIds = \App\Models\User::where('rol', 'administrador')->pluck('id')->toArray();

        $messages = \App\Models\Message::where(function ($query) use ($userId, $adminIds) {
            // Mensajes del usuario a cualquier administrador
            $query->where('sender_id', $userId)
                ->whereIn('receiver_id', $adminIds);
        })->orWhere(function ($query) use ($userId, $adminIds) {
            // Respuestas de cualquier administrador al usuario
            $query->whereIn('sender_id', $adminIds)
                ->where('receiver_id', $userId);
        })
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($messages);
    }

    public function sendMessage(Request $request)
    {
        try {
            $request->validate(['message' => 'required|string|max:2000']);

            $userId = Session::has('user') ? Session::get('user')->id : auth()->id();

            // Buscamos al primer administrador para satisfacer la llave foránea
            $firstAdmin = \App\Models\User::where('rol', 'administrador')->first();

            if (!$firstAdmin) {
                return response()->json(['error' => 'No hay administradores en el sistema'], 404);
            }

            $message = \App\Models\Message::create([
                'sender_id'   => $userId,
                'receiver_id' => $firstAdmin->id, // Usamos un ID real que existe en la tabla users
                'message'     => $request->input('message'),
            ]);

            return response()->json(['status' => 'Mensaje enviado', 'data' => $message]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error de base de datos',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }
}
