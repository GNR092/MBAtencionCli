<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Message; // Import the Message model
use Illuminate\Support\Facades\Auth; // Import the Auth facade
use Illuminate\Support\Facades\Session; // Add this line

class AdminChatController extends Controller
{
    public function showUserChatDirectory(Request $request)
    {
        $query = User::where('rol', 'usuario');

        // Handle Search
        if ($request->has('search') && $request->input('search') != '') {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('email', 'like', '%' . $searchTerm . '%');
            });
        }

        // Handle Sort
        $sort = $request->input('sort', 'asc'); // Default to 'asc'
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
        $allAdminIds = User::where('rol', 'administrador')->pluck('id');

        $messages = Message::with('sender:id,name,rol')
            ->where(function ($query) use ($userId, $allAdminIds) {
                // Messages from the user to any admin
                $query->where('sender_id', $userId)
                      ->whereIn('receiver_id', $allAdminIds);
            })->orWhere(function ($query) use ($userId, $allAdminIds) {
                // Messages from any admin to the user
                $query->whereIn('sender_id', $allAdminIds)
                      ->where('receiver_id', $userId);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark messages from the user as read by the current admin.
        // Note: This only marks it for the viewing admin. The concept of "read" is now per-admin.
        Message::where('sender_id', $userId)
               ->where('receiver_id', $currentAdminId)
               ->whereNull('read_at')
               ->update(['read_at' => now()]);

        return response()->json($messages);
    }

    public function sendMessage(Request $request, $userId)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $adminId = Session::get('user')->id; // Current authenticated admin's ID

        $message = Message::create([
            'sender_id' => $adminId,
            'receiver_id' => $userId,
            'message' => $request->input('message'),
        ]);

        return response()->json($message);
    }
}
