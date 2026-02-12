<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Message;
use Illuminate\Support\Facades\Session;

class AdminChatController extends Controller
{
    public function showUserChatDirectory(Request $request)
    {
        $query = User::where('role', 'usuario');

        // Manejo de búsqueda
        if ($request->has('search') && $request->input('search') != '') {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('email', 'like', '%' . $searchTerm . '%');
            });
        }

        // Manejo de orden
        $sort = $request->input('sort', 'asc');
        if ($sort === 'recent') {
            $query->orderBy('created_at', 'desc');
        } elseif ($sort === 'desc') {
            $query->orderBy('name', 'desc');
        } else {
            $query->orderBy('name', 'asc');
        }

        $users = $query->get();

        return view('admin_user_chat_directory', [
            'users' => $users,
            'search' => $request->input('search'),
            'sort' => $sort
        ]);
    }

    public function getMessages($userId)
    {
        $currentAdminId = Session::get('user')->id;
        $allAdminIds = User::where('role', 'administrador')->pluck('id')->toArray();

        $messages = Message::with('sender:id,name,role')
            ->where(function ($query) use ($userId, $allAdminIds) {
                // CAMBIO CLAVE: Mensajes del usuario dirigidos a CUALQUIER admin (ID 0)
                // o a un administrador específico.
                $query->where('sender_id', $userId)
                    ->whereIn('receiver_id', array_merge([0], $allAdminIds));
            })->orWhere(function ($query) use ($userId, $allAdminIds) {
                // Mensajes de cualquier admin dirigidos a este usuario específico.
                $query->whereIn('sender_id', $allAdminIds)
                    ->where('receiver_id', $userId);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        // Marcamos como leído solo lo que entró al buzón general (0)
        // o lo que fue dirigido específicamente a este admin.
        Message::where('sender_id', $userId)
            ->whereIn('receiver_id', [0, $currentAdminId])
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json($messages);
    }

    public function sendMessage(Request $request, $userId)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $adminId = Session::get('user')->id;

        $message = Message::create([
            'sender_id' => $adminId,
            'receiver_id' => $userId, // La respuesta va directo al ID del usuario
            'message' => $request->input('message'),
        ]);

        return response()->json($message);
    }
}
