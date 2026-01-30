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
        $adminIds = User::where('rol', 'administrador')->pluck('id');

        $messages = Message::where(function ($query) use ($userId, $adminIds) {
            $query->where('sender_id', $userId)
                  ->whereIn('receiver_id', $adminIds);
        })->orWhere(function ($query) use ($userId, $adminIds) {
            $query->whereIn('sender_id', $adminIds)
                  ->where('receiver_id', $userId);
        })
        ->orderBy('created_at', 'asc')
        ->get();

        // Mark messages from admins as read by the user
        Message::whereIn('sender_id', $adminIds)
               ->where('receiver_id', $userId)
               ->whereNull('read_at')
               ->update(['read_at' => now()]);

        return response()->json($messages);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $userId = Session::get('user')->id;
        $adminUsers = User::where('rol', 'administrador')->get();

        if ($adminUsers->isEmpty()) {
            return response()->json(['error' => 'No administrators found to send the message to.'], 404);
        }

        foreach ($adminUsers as $admin) {
            Message::create([
                'sender_id' => $userId,
                'receiver_id' => $admin->id,
                'message' => $request->input('message'),
            ]);
        }

        return response()->json(['status' => 'Message sent to all administrators.']);
    }
}
